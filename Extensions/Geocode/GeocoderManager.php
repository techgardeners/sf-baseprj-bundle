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

use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;
use TechG\Bundle\SfBaseprjBundle\Entity\GeoPosition;

class GeocoderManager extends BaseModule
{    
    const MODULE_NAME = 'geodecode';
    
    // Nome della variabile in sessione contenente la cache della configurazione
    const SESSION_VARS_CACHE = 'tgsfbaseprj/bundle/geocode/cache';

    private $userGeoPositionCache;
    public $geocoder;

    public function __construct(MainKernel $tgKernel)
    {
        parent::__construct($tgKernel);
       
        if ($this->isEnabled()) {
            // inizialize geocoder ( https://github.com/willdurand/Geocoder )
            $this->geocoder = new \TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeocoderEx();
            $this->geocoder->registerProviders(array(new \TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeoPluginExProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter()),));            
        }       
        
    }    
 
    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        parent::setConfiguration($config, $container);

    }
    
    public function getGeoInfoByIp($clientIp)
    {
        if ($this->isEnabled()) {

            $this->addDebugLap('Start geo decoding');            

            // Geoposition Object
            $geoPositionObj = new GeoPosition();         
            
            // TODO:
            // Da mettere in userGeoPositionCache e farli caricare dal from array (anche l'id)
            $geoPositionObj->setIpAddr($clientIp);
            $geoPositionObj->setProvider('geo_plugin');
            $geoPositionObj->setDataOrigin('ip');
            
            if (in_array($clientIp, array('127.0.0.1', 'fe80::1', '::1'))) {
                $clientIp = '190.218.72.14';    
            }
            
            // Check if in session
            if ($this->tgKernel->getSession()->has(self::SESSION_VARS_CACHE)) {
            
                $this->userGeoPositionCache = $this->tgKernel->getSession()->get(self::SESSION_VARS_CACHE);    
            
            } else {
               
                $this->userGeoPositionCache = $this->geocoder->using('geo_plugin')->geocode($clientIp, true);           
                $this->tgKernel->getSession()->set(self::SESSION_VARS_CACHE, $this->userGeoPositionCache);                 
            }
            
            $geoPositionObj->fromArray($this->userGeoPositionCache);
      
            
            $this->addDebugLap('End geo decoding'); 
            
            return $geoPositionObj;
        }        
    }
    
    
    
    
}