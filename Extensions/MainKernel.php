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

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Symfony\Component\Locale\Locale;

class MainKernel
{

    private $container = null;
    private $em = null;
    private $router = null;
    public  $geocoder = null;
    public  $mobileDetector = null;

    private $isInit = false;                // Indica se la classe è stata inizializzata
    public  $collectUserGeoInfo = false;    // Indica se il geodecoding è abilitato o no
    public  $guessLocale = false;           // Indica se il locale deve essere individuato automaticamente o no (necessita tabella in db)
    
    public  $baseUri = null;                // Contiene il baseUri dell' applicazione
    public  $uri = null;                    // Contiene l'uri della pagina richiamata
    public  $userGeoInfo = null;            // Hold user geoInfo
    public  $userBrowserInfo = null;        // Hold user geoInfo

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

        $this->collectUserGeoInfo = $this->container->getParameter('tech_g_sf_baseprj.collectUserGeoInfo');
        $this->guessLocale = $this->container->getParameter('tech_g_sf_baseprj.guessLocale');
        
        // inizialize geocoder ( https://github.com/willdurand/Geocoder )
        $this->geocoder = new \TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeocoderEx();
        $this->geocoder ->registerProviders(array(new \TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeoPluginExProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter()),));        
        
        $this->mobileDetector  = $this->container->get('mobile_detect.mobile_detector');
        
    }

    /**
    *  Inizializza l'oggetto
    *  viene richiamato dall'evento Invictus\CmsBundle\Listener
    * 
    * @param \Symfony\Component\HttpFoundation\Request $request
    */
    public function init(Request $request)
    {
        
        $this->uri = $request->getRequestUri(); // salvo l'uri della pagina
        $this->router = $this->container->get('router'); // Instanzio l'oggetto per la gestione delle rotte    
        
        // Set the baseUri
        $this->baseUri = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

        // get information for User Browser
        $this->userBrowserInfo = $this->getBrowser();
       
        if ($this->collectUserGeoInfo) {
            
            $clientIp = $request->getClientIp();
            
            if (in_array($clientIp, array('127.0.0.1', 'fe80::1', '::1'))) {
                $clientIp = '201.218.96.138';    
            }
            
            $this->userGeoInfo = $this->geocoder->using('geo_plugin')->geocode($clientIp);           
                
        }
        
        // If Locale is not set on Uri guessed right Locale
        if ($this->guessLocale && !$this->isSetLocaleOnUrl($this->uri)) {
            
            if (null !== $guessedLocale = $this->guessLocale($request)) {
                $request->setLocale($guessedLocale->getLocale());    
            }            
        }

        
        // A questo punto sono inizilizzato
        $this->isInit = true;
        
     }    

/* ---------------------------------------------- */
/*              METODI PUBBLICI                   */
/* ---------------------------------------------- */
    

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
        $u_agent = ($u_agent) ? $u_agent : $_SERVER['HTTP_USER_AGENT']; 
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

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
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
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

    private function isSetLocaleOnUrl($uri)
    {
        return preg_match('%^/[a-z]{2}[-_][A-Za-z]{2}%', $uri);    
    }
    
    private function guessLocale(Request $request)
    {
           
        $langObj = null;
        
        // get right Language by Browser Preferred Language(ONLY xx_XX format)
        foreach ($request->getLanguages() as $lang) {
            if (preg_match('%^[a-z]{2}_[A-Z]{2}$%', $lang) && is_null($langObj)) {
                
                if ($_obj = $this->em->getRepository("TechGSfBaseprjBundle:Language")->findOneBy(array('locale' => $lang, 'enabled' => true))){
                    $langObj = $_obj;
                }
                    
            }    
        }
        
        // if no result get right Language by Browser Preferred Language(ONLY xx format)
        if (is_null($langObj)) {
            foreach ($request->getLanguages() as $lang) {
                if (preg_match('%^[a-z]{2}$%', $lang) && is_null($langObj)) {
                    
                    if (is_null($langObj) && $_obj = $this->em->getRepository("TechGSfBaseprjBundle:Language")->findOneBy(array('iso639' => $lang, 'enabled' => true))){
                        $langObj = $_obj;
                    }
                        
                }   
            }                
        }
        
        // if no result and GeoInfo is enabled try to guess by contry code
        if (is_null($langObj) && $this->collectUserGeoInfo) {

           // todo: implement method
           
        }
        
        return $langObj;            
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

    public function getRouter()
    {
        return $this->router;
    }        
    
    /**
    * DIRECTORY  PATH
    * 
    */
    public static function getUploadBaseRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../../web';
    }

    public static function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return self::getUploadBaseRootDir().self::getUploadDir();
    }

    public static function getUploadTmpRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return self::getUploadBaseRootDir().self::getUploadTmpDir();
    }

    public static function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return '/uploads';
    }    
    
    public static function getUploadTmpDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return self::getUploadDir().'/tmp';
    }    
    
    
    /**
    * Return a configurations value (for twig use)
    * 
    * @param mixed $confConst
    */
    public static function getConfConst($confConst)
    {
        eval('$value = \Invictus\CmsBundle\Extensions\CMSConfigurations::'.$confConst.';');
        return $value;
    }    
        
}