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
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

use Symfony\Component\HttpFoundation\Cookie;
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
use TechG\Bundle\SfBaseprjBundle\Extensions\Locale\LocaleManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\BlackWhiteList\BlackListManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\BlackWhiteList\WhiteListManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\GPanel\GPanelManager;
use TechG\Bundle\SfBaseprjBundle\Event\TechGKernelInitEvent;

use TechG\Bundle\SfBaseprjBundle\EventListener\TechGKernelListener;



class MainKernel
{

    const BUNDLE_VERSION = '0.1.0-beta';
    const KERNEL_VERSION = '0.1.0-beta';
  
    private $container = null;
    private $session = null;
    
    private $isInit = false;                            // Indica se la classe è stata inizializzata    
    private $saveCookie = false;                         
    
    private $masterRequest;
    private $cookieObj;

    private  $settingManager = null;
    private  $logManager = null;
    
    public  $userGeoPosition = null;                    // Hold user geoInfo
    public  $userBrowserInfo = null;                    // Hold user browser info
    public  $guessedLocale = null;                      // Hold locale guessed
    
    public  $requestId = null;                          
    public  $cookieId = null;                          
    public  $tempCookieId = null;                          

    public $modules = array();
    
    
    /**
    * Costruttore del servizio
    * 
    * @param ContainerInterface $container
    * @return MainKernel
    */
    public function __construct(ContainerInterface $container, SettingManager $settingManager)
    {
        $this->container = $container;
        $this->settingManager = $settingManager;
        $this->logManager = $container->get('techg.log');
        
        $this->session = $container->get('session');
        
        $this->requestId = uniqid(rand(), true);
        $this->userBrowserInfo = UtilityManager::getBrowser();        
        
        // Inizializzo la sessione
        $this->session->start();
        
        $request = $container->get('request');

        // Gestisco il salvataggio nei cookie dell' Id di sessione
        if ($request->cookies->has('tgSessionId')) {
            $this->cookieId = $request->cookies->get('tgSessionId');
        } else {
            
            if ($request->cookies->has('tgSessionFirstId')) {
                $this->cookieId = uniqid(null, true);
                $this->tempCookieId = $request->cookies->get('tgSessionFirstId');
                $this->cookieObj = new Cookie('tgSessionId', $this->cookieId, time() + 3600 * 24 * 7);
            } else {
                $this->cookieId = 'NO-COOKIE-SUPPORT';
                $tempId = $this->session->getId();
                $this->cookieObj = new Cookie('tgSessionFirstId', $tempId, time() + 3600 * 24 * 1);                   
            }
            
            $this->saveCookie = true;
        }

        // Mappo la master request nell'array apposito
        $this->masterRequest = $this->mapRequest($request, true);
        

        // Qua dovrei lanciare un evento che carichi i moduli attivi nel kernel        
        // a quel punto sapendo quali sono attivi, posso ragionare di conseguenza        
        $this->userGeoPosition = $container->get('techg.geocoder')->getGeoInfoByIp($this->getMasterRequest('ip'));
        $this->guessedLocale = $this->masterRequest['guessedLocale'];        

        // ************************************************************************************************
        // Qui ho finito completamente il l'inizializzazione del kernel
        // ************************************************************************************************        
        
        $this->isInit = true;

        // *********************************************************************
        // Da qui si potrebbe definire PostInit
        // Faccio partire l'evento che indica ai moduli che il kernel è inizializzato
        // ********************************************************************* 
        $container->get('event_dispatcher')->dispatch(TechGKernelInitEvent::onKernelInit, new TechGKernelInitEvent($this));
    }   
    
//**************************************************************************************    
// SYMFONY EVENTS HANDLER   
//**************************************************************************************     

    public function onRequest(GetResponseEvent $event) 
    {
        // Setto il locale (se è stato guessato)               
        if (null !== $this->guessedLocale) {
            $event->getRequest()->setLocale($this->masterRequest['guessedLocale']);
        }
        
        // Aggiungo i settaggi del secondo giro
        $this->masterRequest = array_merge($this->masterRequest, $this->mapRequest($event->getRequest()));
    }
     
    public function onException(GetResponseForExceptionEvent $event) 
    {       
    }
     
    public function onController(FilterControllerEvent $event) 
    {      
    }
     
    public function onView(GetResponseForControllerResultEvent $event) 
    {
        
    }
     
    public function onResponse(FilterResponseEvent $event) 
    {
        if ($this->saveCookie) {
            $event->getResponse()->headers->setCookie($this->cookieObj);
        }                     
    }
     
    public function onTerminate(PostResponseEvent $event) 
    {     
        // Se ho un cookieIdTemporaneo devo agganciare i log dispersi
        if (null != $this->tempCookieId) {
            $linkedRecord = $this->getLogManager()->linkNoCookieLog($this->tempCookieId, $this->cookieId);
        }          
    }
     

//***************** SECONDARY REQUEST ***********************************
    
    public function initSubRequest(GetResponseEvent $event)
    {        
        if ($this->isInit()){
            // nelle richieste secondarie risetto il locale della request a quello scelto
            // Non metto direttamente il guessed, perchè potrebbe essere settato nella url
            if (null !== $guessedLocale = $this->getLocaleManager()->guessLocale($event->getRequest())) {
                $event->getRequest()->setLocale($guessedLocale);
            }                        
        }
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
/*              METODI STATICI                   */
/* ---------------------------------------------- */

    public static function skipSubRequest($event)
    {              
        $request = $event->getRequest();
        
        // Viene richiamato SOLO nelle richieste principali
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType() || self::skipOtherRequest($event)) {
            return true;
        }
        
        return false;         
    }
    
    public static function skipOtherRequest($event)
    {              
        $request = $event->getRequest();
        
        // Viene richiamato SOLO nelle richieste principali
        if (preg_match('/^\/_(wdt|assetic|profiler|configurator)/', $request->getRequestUri())) {
            return true;
        }
        
        return false;         
    } 

 
    

/* ---------------------------------------------- */
/*              METODI PRIVATI                    */
/* ---------------------------------------------- */

    private function mapRequest(Request $request, $full = false)
    {
        $mapRequest = array();
        
        if ($full){
            $mapRequest['ip'] = $request->getClientIp();
            $mapRequest['method'] = $request->getMethod();
            $mapRequest['host'] = $request->getHttpHost();
            $mapRequest['port'] = $request->getPort();
            $mapRequest['scheme'] = $request->getScheme();
            $mapRequest['uri'] = $request->getUri();
            $mapRequest['requestUri'] = $request->getRequestUri();
            $mapRequest['queryString'] = $request->getQueryString();
            $mapRequest['isSecure'] = $request->isSecure();
            $mapRequest['content'] = $request->getContent();
            $mapRequest['preferredLanguage'] = $request->getPreferredLanguage();
            $mapRequest['languages'] = $request->getLanguages();
            $mapRequest['charsets'] = $request->getCharsets();
            $mapRequest['acceptableContentTypes'] = $request->getAcceptableContentTypes();
            $mapRequest['isXmlHttpRequest'] = $request->isXmlHttpRequest();
            $mapRequest['to_string'] = $request->__toString();
            $mapRequest['server'] = $request->server;
            $mapRequest['headers'] = $request->headers;
            $mapRequest['cookies'] = $request->cookies;
            $mapRequest['oriLocale'] = $request->getLocale();
            $mapRequest['guessedLocale'] = $this->getLocaleManager()->getGuessedLocale();            
        }
        
        $mapRequest['locale'] = $request->getLocale();
        $mapRequest['_route'] = $request->get('_route');
        $mapRequest['_controller'] = $request->get('_controller');
        $mapRequest['_route_params'] = $request->get('_route_params');
        
        
        return $mapRequest;        
    }

/* ---------------------------------------------- */
/*              GETTER / SETTER                   */
/* ---------------------------------------------- */

    public function isInit()
    {
        return $this->isInit;
    }    

    public function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }    

    public function getContainer()
    {
        return $this->container;
    }    

    public function getContainerElement($name)
    {
        return $this->container->get($name);
    }    
    
    public function getMasterRequest($item = null)
    {
        return (is_null($item)) ? $this->masterRequest : $this->masterRequest[$item];
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
      
     
    public function isLocaleinUrl()
    {
        return $this->getLocaleManager()->isSetLocaleOnUrl($this->uri);
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

   
    public function getDebugManager()
    {
        return $this->container->get('techg.debug');     
    }
    
    public function getGeocoderManager()
    {
        return $this->container->get('techg.geocoder');     
    }
    
    public function getLocaleManager()
    {
        return $this->container->get('techg.locale');    
    }
    
    public function getMobiledetectManager()
    {
        return $this->getModule(MobiledetectManager::MODULE_NAME);    
    }
    
    public function getLogManager()
    {
        return $this->logManager;    
    }
   
    public function isDebugEnabled()
    {
        return $this->getDebugManager()->isEnabled();
    }        
     
    public function isGeoEnabled()
    {
        return $this->getGeocoderManager()->isEnabled();
    }        
     
    public function isLocaleEnabled()
    {
        return $this->getLocaleManager()->isEnabled();
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