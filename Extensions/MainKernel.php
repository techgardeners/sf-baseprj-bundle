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
    
    public  $requestInfo = null;                        // Contiene il baseUri dell' applicazione
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

        $this->requestInfo = $this->populateRequestInfo($request);
        
        // Setto varie impostazioni base
        $this->clientIp = $request->getClientIp();
        $this->host = $request->getHttpHost();
        $this->uri = $request->getRequestUri(); // salvo l'uri della pagina
        $this->baseUri = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $this->userBrowserInfo = $this->getBrowser();  // get information for User Browser
        
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
    
    private function addRawLog($type = null, $level = null, $short = '', $long = '', $info = null, $taskId = null, $parentId = null )
    {   
        if (is_object($this->getLogManager())) {
            $this->getLogManager()->addRawLog($type, $level, $short, $long, $info, $taskId, $parentId);    
        }  
    }     
    
    
    
    // metodi di utilità generale
    
    public static function print_rh($arg, $print_pre = true)
    {
        
        if ($print_pre) { echo "<pre>"; }

        print_r($arg);

        if ($print_pre) { echo "</pre>"; }

        return 0;
    }
    

    // Metodo per la generazione di uno slug
    
    public static function slugify($title)
    {
    
        $title = strip_tags($title);
        
        $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
        
        $title = str_replace('%', '', $title);
        
        $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
    
        $title = self::remove_accents($title);
        if (self::seems_utf8($title)) {
            if (function_exists('mb_strtolower')) {
                $title = mb_strtolower($title, 'UTF-8');
            }
            //$title = $this->utf8_uri_encode($title);
        }
    
        $title = strtolower($title);
        $title = preg_replace('/&.+?;/', '', $title); 
        $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
        $title = preg_replace('/\s+/', '-', $title);
        $title = preg_replace('|-+|', '-', $title);
        $title = trim($title, '-');
    
        return $title;
    }
    
    
    public static function getBrowser($u_agent = null) 
    { 
        $u_agent = ($u_agent) ? $u_agent : @$_SERVER['HTTP_USER_AGENT']; 
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";
        $ub= "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }
        
        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
        { 
            $bname = 'Internet Explorer'; 
            $ub = "MSIE"; 
        } 
        elseif(preg_match('/Firefox/i',$u_agent)) 
        { 
            $bname = 'Mozilla Firefox'; 
            $ub = "Firefox"; 
        } 
        elseif(preg_match('/Chrome/i',$u_agent)) 
        { 
            $bname = 'Google Chrome'; 
            $ub = "Chrome"; 
        } 
        elseif(preg_match('/Safari/i',$u_agent)) 
        { 
            $bname = 'Apple Safari'; 
            $ub = "Safari"; 
        } 
        elseif(preg_match('/Opera/i',$u_agent)) 
        { 
            $bname = 'Opera'; 
            $ub = "Opera"; 
        } 
        elseif(preg_match('/Netscape/i',$u_agent)) 
        { 
            $bname = 'Netscape'; 
            $ub = "Netscape"; 
        } 
        
        // finally get the correct version number
        $known = array('Version', $ub, 'other');  
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#'; 
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }
        
        // see how many we have
        $i = count($matches['browser']);  
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= @$matches['version'][0];
            }
            else {
                $version= @$matches['version'][1];
            }
        }
        else {
            $version= @$matches['version'][0];
        }
        
        // check if we have a number
        if ($version==null || $version=="") {$version="?";}
        
        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    } 
    
/* ---------------------------------------------- */
/*              METODI PRIVATI                    */
/* ---------------------------------------------- */

    private static function populateRequestInfo(Request $request)
    {
        $requestInfo = array();
        
        $requestInfo['uri'] = $request->getUri();
        $requestInfo['host'] = $request->getHost();
        $requestInfo['queryString'] = $request->getQueryString();
        $requestInfo['format'] = $request->getRequestFormat();
        $requestInfo['route'] = $request->get('_route');
        
        return $requestInfo;
    }


    private static function seems_utf8($Str) 
    { 
        for ($i=0; $i<strlen($Str); $i++) {
            if (ord($Str[$i]) < 0x80) continue; 
            elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1;
            elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2;
            elseif ((ord($Str[$i]) & 0xF8) == 0xF0) $n=3; 
            elseif ((ord($Str[$i]) & 0xFC) == 0xF8) $n=4; 
            elseif ((ord($Str[$i]) & 0xFE) == 0xFC) $n=5; 
            else return false;
            for ($j=0; $j<$n; $j++) { 
                if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80))
                return false;
            }
        }
        return true;
    }
    
    private static function utf8_uri_encode( $utf8_string )
    {
      $unicode = '';        
      $values = array();
      $num_octets = 1;
            
      for ($i = 0; $i < strlen( $utf8_string ); $i++ ) {
    
        $value = ord( $utf8_string[ $i ] );
                
        if ( $value < 128 ) {
          $unicode .= chr($value);
        } else {
          if ( count( $values ) == 0 ) $num_octets = ( $value < 224 ) ? 2 : 3;
                    
          $values[] = $value;
          
          if ( count( $values ) == $num_octets ) {
        if ($num_octets == 3) {
          $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
        } else {
          $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
        }
    
        $values = array();
        $num_octets = 1;
          }
        }
      }
    
      return $unicode;    
    }
    
    private static function remove_accents($string)
    {
        if (self::seems_utf8($string)) {
            $chars = array(
            // Latin-1 
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Latin A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(134) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(196).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(196).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(196).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(196).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(196).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(196).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(196).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(196).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(196).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
            // Euro
            chr(226).chr(130).chr(172) => 'E');
            
            $string = strtr($string, $chars);
        } else {
            
            $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
                .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
                .chr(195).chr(197).chr(199).chr(200).chr(201).chr(202)
                .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
                .chr(211).chr(212).chr(213).chr(216).chr(217).chr(218)
                .chr(219).chr(221).chr(224).chr(225).chr(226).chr(227)
                .chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
                .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
                .chr(244).chr(245).chr(248).chr(249).chr(250).chr(251)
                .chr(253).chr(255);
    
            $chars['out'] = "EfSZszYcYuAAAAACEEEEIIIINOOOOOUUUYaaaaaceeeeiiiinooooouuuyy";
    
            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254), chr(196), chr(220), chr(214), chr(228), chr(252), chr(246));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th', 'Ae', 'Ue', 'Oe', 'ae', 'ue', 'oe');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }
    
        return $string;
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