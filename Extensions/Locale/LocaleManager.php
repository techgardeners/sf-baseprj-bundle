<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\Locale;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;  

use TechG\Bundle\SfBaseprjBundle\Event\TechGKernelInitEvent;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class LocaleManager extends BaseModule
{    
    const MODULE_NAME = 'locale';
    const CONF_SAVE_SESSION = 'savesession';
    const CONF_ONLY_FIRST_REQUEST = 'onlyfirstrequest';
    
    const SESSION_VARS_SAVED_SESSION = 'tgsfbaseprj/bundle/guesslocale/saved/session';
    const SESSION_VARS_PREF_SAVE = 'tgsfbaseprj/bundle/guesslocale/pref/language';

    private $saveSession;
    private $onlyFirstRequest;
    
    private $enabledLanguage;
    
    private $oriLocale;
    private $guessedLocale;
    
// ********************************************************************************************************       
// METODI DI CONFIGURAZIONE E INIZIALIZZAZIONE       
// ********************************************************************************************************

    public function __construct(ContainerInterface $container, SettingManager $settingManager)
    {
        parent::__construct($container, $settingManager);
        
        $this->saveSession = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_SESSION);           
        $this->onlyFirstRequest = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_ONLY_FIRST_REQUEST); 
        
        $this->enabledLanguage = null;
        $this->localeSaved = null;
        
        if ($this->isEnabled()) {
            $this->enabledLanguage = $this->getEnabledLanguage();
            
            $request = $container->get('request');
            $this->oriLocale = $request->getLocale();
            $this->guessedLocale = $this->retriveGuessedLocale($request);
        }      
                                
    }       


// ********************************************************************************************************       
// METODI PUBBLICI       
// ********************************************************************************************************      

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

    
    public function getGuessedLocale()
    {
        return $this->guessedLocale;    
    }


    public function guessLocale($request)
    {
        $locale = null;
        
        $uri = $request->getRequestUri();
        $requestLanguages = $request->getLanguages();
        
        if ($this->isEnabled() && !$this->isSetLocaleOnUrl($uri)) {
            
            // get right Language by Browser Preferred Language(ONLY xx_XX format)
            foreach ($requestLanguages as $lang) {
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
                foreach ($requestLanguages as $lang) {
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
            //if (is_null($locale) && $this->tgKernel->getGeocoderManager()->isEnabled()) {

               // todo: implement method
               
            //}
        }
        
        return $locale;                 
    } 
    
// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************  

    private function retriveGuessedLocale($request)
    {           
        $locale = null;
        
        // Check if in session
        if ($this->onlyFirstRequest && $this->session->has(self::SESSION_VARS_SAVED_SESSION)) {
        
            $locale = $this->session->get(self::SESSION_VARS_SAVED_SESSION);    
        
        } else {
        
            $locale = $this->guessLocale($request);
            $this->session->set(self::SESSION_VARS_SAVED_SESSION, $locale);    
        }
        
        return $locale;            
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