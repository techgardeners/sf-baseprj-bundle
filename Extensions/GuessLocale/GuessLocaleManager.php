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

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class GuessLocaleManager extends BaseModule
{    
    const MODULE_NAME = 'guesslocale';
    const CONF_SAVE_SESSION = 'savesession';
    const CONF_ONLY_FIRST_REQUEST = 'onlyfirstrequest';
    
    const SESSION_VARS_SAVED_SESSION = 'tgsfbaseprj/bundle/guesslocale/saved/session';
    const SESSION_VARS_PREF_SAVE = 'tgsfbaseprj/bundle/guesslocale/pref/language';

    private $saveSession;
    private $onlyFirstRecord;
    private $enabledLanguage;
    
    private $localeSaved;
    
    
    public function hydrateConfinguration(MainKernel $tgKernel)
    {         
        $this->saveSession = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_SESSION);           
        $this->onlyFirstRecord = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_ONLY_FIRST_REQUEST);           
    } 
    
    public function init()
    { 
        $this->enabledLanguage = $this->getEnabledLanguage();
        $this->localeSaved = null;       
    }        
 
    public function getEnabledLanguage()
    {
        if (!$this->isEnabled()) return false;
        
        // Check if in session
        if ($this->saveSession && $this->session->has(self::SESSION_VARS_PREF_SAVE)) {
        
            $prefLang = $this->session->get(self::SESSION_VARS_PREF_SAVE);    
        
        } else {
        
            $prefLang = $this->em->getRepository("TechGSfBaseprjBundle:Language")->getEnabledLanguage();
            
            if ($this->saveSession){
                $this->session->set(self::SESSION_VARS_PREF_SAVE, $prefLang);    
            }             
        }
        
        return $prefLang;    
    }

    
    public function guessLocale(Request $request)
    {
           
        $locale = null;
        
        if ($this->isEnabled() && !$this->isSetLocaleOnUrl($request->getRequestUri())) {
            
            $this->addDebugLap('Start guess language'); 
            
            
            // Check if in session
            if ($this->onlyFirstRecord && $this->session->has(self::SESSION_VARS_SAVED_SESSION)) {
            
                $locale = $this->session->get(self::SESSION_VARS_SAVED_SESSION);    
            
            } else {
            
                // get right Language by Browser Preferred Language(ONLY xx_XX format)
                foreach ($request->getLanguages() as $lang) {
                    if (preg_match('%^[a-z]{2}_[A-Z]{2}$%', $lang) && is_null($locale)) {
                        
                        foreach ($this->enabledLanguage as $language) {
                            if ($lang == $language['locale'] && is_null($locale)){
                                $locale = $lang;
                            }
                        }
                            
                    }    
                }
                
                // if no result get right Language by Browser Preferred Language(ONLY xx format)
                if (is_null($locale)) {
                    foreach ($request->getLanguages() as $lang) {
                        if (preg_match('%^[a-zA-Z]{2}$%', $lang) && is_null($locale)) {
                            
                            foreach ($this->enabledLanguage as $language) {
                                if ((strtolower($lang) == $language['ISO639'] || strtoupper($lang) == $language['ISO3166']) && is_null($locale)){
                                    $locale = $lang;
                                }
                            }
                        }   
                    }                
                }
                
                // if no result and GeoInfo is enabled try to guess by contry code
                if (is_null($locale) && $this->tgKernel->getGeocoderManager()->isEnabled()) {

                   // todo: implement method
                   
                }  
            
                if ($this->onlyFirstRecord){
                    $this->session->set(self::SESSION_VARS_SAVED_SESSION, $locale);    
                }         
                                 
            }              
            
            $this->addDebugLap('End guess language'); 
                      
        }

        $this->localeSaved = $locale;
        
        return $this->localeSaved;            
    }
    
 
    
// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  

    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        self::setSingleConf(self::CONF_SAVE_SESSION, $config, $container);        
        self::setSingleConf(self::CONF_ONLY_FIRST_REQUEST, $config, $container);        
    } 
    
    public static function isSetLocaleOnUrl($uri)
    {
        return preg_match('%^/[a-z]{2}[-_][A-Za-z]{2}%', $uri);    
    }              
        
}