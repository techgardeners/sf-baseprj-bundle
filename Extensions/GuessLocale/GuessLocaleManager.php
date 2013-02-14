<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\GuessLocale;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class GuessLocaleManager extends BaseModule
{    
    const MODULE_NAME = 'guesslocale';
 
    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        parent::setConfiguration($config, $container);

    }
    
    public function guessLocale(Request $request, $em, $geocoderManager)
    {
           
        $langObj = null;
        
        if ($this->isEnabled() && !$this->isSetLocaleOnUrl($request->getRequestUri())) {
            
            $this->addDebugLap('Start guess language'); 
            
            // get right Language by Browser Preferred Language(ONLY xx_XX format)
            foreach ($request->getLanguages() as $lang) {
                if (preg_match('%^[a-z]{2}_[A-Z]{2}$%', $lang) && is_null($langObj)) {
                    
                    if ($_obj = $em->getRepository("TechGSfBaseprjBundle:Language")->findOneBy(array('locale' => $lang, 'enabled' => true))){
                        $langObj = $_obj;
                    }
                        
                }    
            }
            
            // if no result get right Language by Browser Preferred Language(ONLY xx format)
            if (is_null($langObj)) {
                foreach ($request->getLanguages() as $lang) {
                    if (preg_match('%^[a-z]{2}$%', $lang) && is_null($langObj)) {
                        
                        if (is_null($langObj) && $_obj = $em->getRepository("TechGSfBaseprjBundle:Language")->findOneBy(array('iso639' => $lang, 'enabled' => true))){
                            $langObj = $_obj;
                        }
                            
                    }   
                }                
            }
            
            // if no result and GeoInfo is enabled try to guess by contry code
            if (is_null($langObj) && $geocoderManager->isEnabled()) {

               // todo: implement method
               
            }  
            
            
            $this->addDebugLap('End guess language'); 
                      
        }

        return $langObj;            
    }
    
    public function isSetLocaleOnUrl($uri)
    {
        return preg_match('%^/[a-z]{2}[-_][A-Za-z]{2}%', $uri);    
    }        
        
}