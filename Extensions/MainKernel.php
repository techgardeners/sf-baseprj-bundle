<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Locale\Locale;

use Doctrine\ORM\EntityManager;

use TechG\Bundle\SfBaseprjBundle\Extensions\UtilityManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Log\LogManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Debug\DebugManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeocoderManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Mobiledetect\MobiledetectManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\GuessLocale\GuessLocaleManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\BlackWhiteList\BlackListManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\BlackWhiteList\WhiteListManager;



class MainKernel
{

    const BUNDLE_VERSION = '0.1.0-beta';
    const KERNEL_VERSION = '0.1.0-beta';
  
    private $container = null;
    private $em = null;
    private $router = null;
    private $session = null;
    private $isInit = false;                            // Indica se la classe è stata inizializzata

    public  $settingManager = null;
    
    public  $baseUri = null;                            // Contiene il baseUri dell' applicazione
    public  $uri = null;                                // Contiene l'uri della pagina richiamata
    public  $host = null;                               // Contiene l'host della pagina richiamata
    public  $clientIp = null;                           // Hold user ip
    public  $userGeoPosition = null;                    // Hold user geoInfo
    public  $userBrowserInfo = null;                    // Hold user browser info
    public  $requestLocale = null;                      // Hold locale setted
    public  $guessedLocale = null;                      // Hold locale guessed
    
    public  $requestId = null;                          

    public $modules = array();
    
    
    /**
    * Costruttore del metodo a cui viene passato l'intero contenitore dei servizi da cui recuperare request e routing
    * 
    * @param ContainerInterface $container
    * @return MainKernel
    */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getEntityManager();
        $this->settingManager = new SettingManager();
        
        // Innesto i vari moduli
        $this->modules[DebugManager::MODULE_NAME] = new DebugManager();
        $this->modules[LogManager::MODULE_NAME] = new LogManager();
        $this->modules[GeocoderManager::MODULE_NAME] = new GeocoderManager();
        $this->modules[MobiledetectManager::MODULE_NAME] = new MobiledetectManager();
        $this->modules[GuessLocaleManager::MODULE_NAME] = new GuessLocaleManager();
        $this->modules[BlackListManager::MODULE_NAME] = new BlackListManager();
        $this->modules[WhiteListManager::MODULE_NAME] = new WhiteListManager();             
        
    }

    /**
    *  Inizializza l'oggetto
    *  viene richiamato dall'evento Invictus\CmsBundle\Listener
    * 
    * @param \Symfony\Component\HttpFoundation\Request $request
    */
    public function init(Request $request)
    {
        $this->router = $this->container->get('router'); // Instanzio l'oggetto per la gestione delle rotte    
        $this->session = $this->container->get('session');
        $this->requestId = uniqid(rand(), true);
        
        // Setto varie impostazioni base
        $this->clientIp = $request->getClientIp();
        $this->host = $request->getHttpHost();
        $this->uri = $request->getRequestUri(); // salvo l'uri della pagina
        $this->baseUri = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $this->userBrowserInfo = UtilityManager::getBrowser();  // get information for User Browser
        
        // Inizializzo il Manager dei settaggi che a suo volta a cascata innietta le configurazioni a tutti i moduli 
        $this->settingManager->init($this);
        foreach($this->modules as $nameModule => $moduleObj) {
            $moduleObj->initModule();
        }

        $this->userGeoPosition = $this->getGeocoderManager()->getGeoInfoByIp($this->clientIp);
        
        // If Locale is not set on Uri guessed right Locale
        if (null !== $this->guessedLocale = $this->getGuessLocaleManager()->guessLocale($request)) {
            $request->setLocale($this->guessedLocale);    
        }            

        $this->requestLocale = $request->getLocale();
        
        // Qui implemento la white e la black list
        $this->getBlackListManager()->executeFilter();
        $this->getWhiteListManager()->executeFilter();
                
        $this->getLogManager()->logSession();
        
        // ************************************************************************************************
        // Qui ho finito completamente il lavoro del kernel a livello request  per la richiesta principale
        // ************************************************************************************************
        
        // A questo punto sono inizilizzato
        $this->isInit = true;

        // a questo punto ho tutti i moduli pronti        
        $this->addDebugLap('End init kernel');        

        // *********************************************************************
        // Da qui si potrebbe definire PostInit
        // *********************************************************************                
     }    

     
    public function initSubRequest(Request $request)
    {
        
        if ($this->isInit()){
            // nelle richieste secondarie risetto il locale della request a quello scelto        
            $request->setLocale($this->requestLocale);            
        }

    }
     
    public function elaborateException(\Exception $exception) 
    {
        $logM = $this->getLogManager();
        $logM->logException($exception);
        
    }
     
    public function elaborateController(FilterControllerEvent $event) 
    {
        
    }
     
    public function elaborateView(GetResponseForControllerResultEvent $event) 
    {
        
    }
     
    public function elaborateResponse(FilterResponseEvent $event) 
    {
        $logM = $this->getLogManager();       
        $logM->logResponse($event->getResponse());        
        
    }
     
    public function elaborateTerminate($event) 
    {
        $logM = $this->getLogManager();
        $logM->shutdown($event);
        
    }
     
     
/* ---------------------------------------------- */
/*              METODI PUBBLICI                   */
/* ---------------------------------------------- */


    public function addDebugLap($string, $ts = null)
    {
        if (is_object($this->getDebugManager())) {
            $this->getDebugManager()->addLap($string, $ts);    
        }
        
    }
    
    public function addRawLog($type = null, $level = null, $short = '', $long = '', $info = null, $taskId = null, $parentId = null, $user = null )
    {   
        if (is_object($this->getLogManager())) {
            $this->getLogManager()->addRawLog($type, $level, $short, $long, $info, $taskId, $parentId, $user = null);    
        }  
    }     
    
    

/* ---------------------------------------------- */
/*              METODI PRIVATI                    */
/* ---------------------------------------------- */


 
/* ---------------------------------------------- */
/*              GETTER / SETTER                   */
/* ---------------------------------------------- */

    public function isInit()
    {
        return $this->isInit;
    }    

    public function getEntityManager()
    {
        return $this->em;
    }    

    public function getContainer()
    {
        return $this->container;
    }    

    public function getContainerElement($name)
    {
        return $this->container->get($name);
    }    
    
    
    public function getUser()
    {
        $securityContext = $this->getContainerElement('security.context');
        $token = $securityContext->getToken();
        return (is_object($token)) ? $token->getUser() : null;
    }    
    
    public function getRequest()
    {
        return $this->getContainerElement('request');
    }    
    
    
    public function getSession()
    {
        return $this->session;
    }    

    public function getRouter()
    {
        return $this->router;
    }        
     
    public function isLocaleinUrl()
    {
        return $this->getGuessLocaleManager()->isSetLocaleOnUrl($this->uri);
    }        
     
    public function getKernelVersion()
    {
        return $this::KERNEL_VERSION;
    }        
     
    public function getBundleVersion()
    {
        return $this::BUNDLE_VERSION;
    }                

// *************************************    

    public function getModules()
    {
           return $this->modules;
    }
    
    public function getModule($name)
    {
           return $this->modules[$name];
    }
     
    public function isModuleEnable($name)
    {
        return $this->getModule($name)->isEnabled();
    }
    
    
    public function getDebugManager()
    {
        return $this->getModule(DebugManager::MODULE_NAME);    
    }
    
    public function getGeocoderManager()
    {
        return $this->getModule(GeocoderManager::MODULE_NAME);    
    }
    
    public function getGuessLocaleManager()
    {
        return $this->getModule(GuessLocaleManager::MODULE_NAME);    
    }
    
    public function getMobiledetectManager()
    {
        return $this->getModule(MobiledetectManager::MODULE_NAME);    
    }
    
    public function getLogManager()
    {
        return $this->getModule(LogManager::MODULE_NAME);    
    }
    
    public function getBlackListManager()
    {
        return $this->getModule(BlackListManager::MODULE_NAME);    
    }
    
    public function getWhiteListManager()
    {
        return $this->getModule(WhiteListManager::MODULE_NAME);    
    }

    public function isDebugEnabled()
    {
        return $this->getDebugManager()->isEnabled();
    }        
     
    public function isGeoEnabled()
    {
        return $this->getGeocoderManager()->isEnabled();
    }        
     
    public function isGuessLocaleEnabled()
    {
        return $this->getGuessLocaleManager()->isEnabled();
    }        
     
    public function isMobileDetectEnabled()
    {
        return $this->getMobiledetectManager()->isEnabled();
    }        
     
    public function isLogEnabled()
    {
        return $this->getLogManager()->isEnabled();
    }        
     
    public function isBlackListEnabled()
    {
        return $this->getBlackListManager()->isEnabled();
    }        
     
    public function isWhiteListEnabled()
    {
        return $this->getWhiteListManager()->isEnabled();
    }        
     
        
}