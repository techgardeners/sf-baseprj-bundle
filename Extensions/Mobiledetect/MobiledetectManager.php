<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\Mobiledetect;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class MobiledetectManager extends BaseModule
{    
    const MODULE_NAME = 'mobiledetect';
    
    public $mobileDetector;


    public function __construct(SettingManager $settingManager, $container)
    {
        parent::__construct($settingManager);
       
        if ($this->isEnabled()) {
            $this->mobileDetector  = $container->get('mobile_detect.mobile_detector');
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
            
            if (!$this->geocoder) {
                // inizialize geocoder ( https://github.com/willdurand/Geocoder )
                $this->geocoder = new \TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeocoderEx();
            }

            $this->geocoder->registerProviders(array(new \TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeoPluginExProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter()),));
            
            $userGeoPosition = $this->geocoder->using('geo_plugin')->geocode($clientIp, true);           
            $userGeoPosition->setProvider('geo_plugin');
            $userGeoPosition->setDataOrigin('ip');            
            
            return $userGeoPosition;
        }        
    }
    
    
    
    
}