<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\Geocode;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Ivory\GoogleMap\MapTypeId;
use Ivory\GoogleMap\Overlays\Animation;

use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Log\LogManager;
use TechG\Bundle\SfBaseprjBundle\Entity\GeoPosition;

class GeocoderManager extends BaseModule
{    
    const MODULE_NAME = 'geodecode';
    const CONF_SAVE_SESSION = 'savesession';    
    
    // Nome della variabile in sessione contenente la cache della configurazione
    const SESSION_VARS_CACHE = 'tgsfbaseprj/bundle/geocode/cache';

    private $saveSession;  
    private $userGeoPositionCache;
    
    public $geocoder; 


// ********************************************************************************************************       
// METODI DI CONFIGURAZIONE E INIZIALIZZAZIONE       
// ********************************************************************************************************

    public function __construct(ContainerInterface $container, SettingManager $settingManager)
    {
        parent::__construct($container, $settingManager);
        
        $this->saveSession = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_SESSION);
        
        if ($this->isEnabled()) {
            // inizialize geocoder ( https://github.com/willdurand/Geocoder )
            $this->geocoder = new \TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeocoderEx();
            $this->geocoder->registerProviders(array(new \TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeoPluginExProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter()),));

        }        
                                
    }           

// ********************************************************************************************************       
// METODI PUBBLICI       
// ********************************************************************************************************  
    
    public function getGeoInfoByIp($clientIp)
    {
        if ($this->isEnabled()) {

            $this->addDebugLap('Start geo decoding');            
          
            if (in_array($clientIp, array('127.0.0.1', 'fe80::1', '::1'))) {
                
                $debugIps = array('8.8.8.8', '213.92.16.171', '190.218.72.14', '201.218.92.155', '194.1.160.38', '149.5.47.131', '95.227.185.133', '213.215.155.212');
                
                $clientIp = $debugIps[rand(0, count($debugIps)-1)];
            }
            
            // Check if in session
            if ($this->saveSession && $this->session->has(self::SESSION_VARS_CACHE)) {
            
                $this->userGeoPositionCache = $this->session->get(self::SESSION_VARS_CACHE);    
            
            } else {

                try{
                    $result = $this->geocoder->using('geo_plugin')->geocode($clientIp);    
                }catch(\Exception $e){
                    $this->logGeoError($e);
                    $result = null;    
                }
                
                $this->userGeoPositionCache = $result;           
                
                if ($this->saveSession) {
                    $this->session->set(self::SESSION_VARS_CACHE, $this->userGeoPositionCache);                    
                }
                
                if (is_null($result)) {
                    return null;                
                }                                 
            }
            

            // Geoposition Object
            $geoPositionObj = new GeoPosition();         
            
            // TODO:
            // Da mettere in userGeoPositionCache e farli caricare dal from array (anche l'id)
            $geo = ($this->userGeoPositionCache) ? $this->userGeoPositionCache : array();
            $geoPositionObj->fromArray($this->userGeoPositionCache);
            $geoPositionObj->setIpAddr($clientIp);
            $geoPositionObj->setProvider('geo_plugin');
            $geoPositionObj->setDataOrigin('ip');      
            
            $this->addDebugLap('End geo decoding'); 
            
            return $geoPositionObj;
        }        
    }
    


// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************  

    private function logGeoError(\Exception $exception)
    {
        $info = LogManager::getLogInfoByException($exception);
        return $this->addRawLog(LogManager::TYPE_GEO_ERROR, LogManager::LEVEL_WARNING, '', '', $info);        
    }

// ********************************************************************************************************       
// METODI TWIG       
// ********************************************************************************************************   
    
    public function getMap($geoInfo)
    {
        $map = $this->tgKernel->getContainer()->get('ivory_google_map.map');

        if (!(is_array($geoInfo) && array_key_exists('latitude', $geoInfo))) return null;
        
        $mapUniqueId = uniqid('idx');
        
        // Configure your map options
        $map->setPrefixJavascriptVariable('map_'.$mapUniqueId.'_');
        $map->setHtmlContainerId('map_canvas_'.$mapUniqueId);

        $map->setAsync(false);

        $map->setAutoZoom(false);

        $map->setCenter($geoInfo['latitude'], $geoInfo['longitude'], true);
        $map->setMapOption('zoom', 4);


        $map->setMapOption('mapTypeId', MapTypeId::ROADMAP);

        $map->setMapOption('disableDefaultUI', true);
        $map->setMapOption('disableDoubleClickZoom', false);

        $map->setStylesheetOptions(array(
            'width' => '75px',
            'height' => '75px'
        ));
        
        
        // MARKER
        
        // Requests the ivory google map marker service
        $marker = $this->tgKernel->getContainer()->get('ivory_google_map.marker');

        // Configure your marker options
        $marker->setPrefixJavascriptVariable('marker_'.$mapUniqueId);
        $marker->setPosition($geoInfo['latitude'], $geoInfo['longitude'], true);
        $marker->setAnimation(Animation::DROP);

        $marker->setOptions(array(
            'clickable' => false,
            'flat' => true
        ));        
        
        $map->addMarker($marker);
        
        return $map;        
    }    
    
    
// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  

    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        self::setSingleConf(self::CONF_SAVE_SESSION, $config, $container);        
    }    
    
    
}