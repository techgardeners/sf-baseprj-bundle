<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\Setting;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\Debug\DebugManager;

class SettingManager
{
    
    /**
    * Constanti per la configurazione
    */
    const PREFIX_BUNDLE = 'tgsfbaseprj';
    
    /**
    * Costanti per abilitare / diabilitare un modulo
    */
    const SUFFIX_ENABLE = 'enable';
    const SUFFIX_DISABLE = 'disable';
    
    
    /**
    * Settaggi 
    */
    
    
    // Nome della variabile in sessione contenente la cache della configurazione
    const SESSION_VARS_CACHE = 'tgsfbaseprj/bundle/settings/cache';
    
    private $session = null;
    private $em = null;
    
    private $settingCache = null;
    private $gParameterBag = null; 
    
// ********************************************************************************************************       
// METODI DI CONFIGURAZIONE E INIZIALIZZAZIONE       
// ********************************************************************************************************

    public function init(MainKernel $tgKernel)
    {
        $this->session = $tgKernel->getSession();
        $this->em = $tgKernel->getEntityManager();
        $this->gParameterBag = $tgKernel->getContainer()->getParameterBag();
        
        // Se trova i settaggi in sessione li carica
        if ($this->session->has(self::SESSION_VARS_CACHE)) {
            $this->settingCache = $this->session->get(self::SESSION_VARS_CACHE);    
        }
        
        if(is_null($this->settingCache)) {
            $this->loadSettingFromDb();
        }
            
        foreach($tgKernel->getModules() as $nameModule => $moduleObj) {
            $moduleObj->hydrateModuleConfinguration($tgKernel);
        }
                
    }

// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************     
    
    /**
    * Carica i settaggi dal db
    * 
    */
    private function loadSettingFromDb()
    {
        $this->settingCache = $this->em->getRepository('TechGSfBaseprjBundle:Setting')->loadAll();
        $this->session->set(self::SESSION_VARS_CACHE, $this->settingCache);            
    }


// ********************************************************************************************************       
// METODI PUBBLICI       
// ********************************************************************************************************  
    
    public function clearSession()
    {
        $this->session->remove(self::SESSION_VARS_CACHE);    
    }
    
    
    public function getGlobalSetting($key, $default = null)
    {
        
        if (is_array($this->settingCache) && 
                array_key_exists('global', $this->settingCache) && is_array($this->settingCache['global']) &&
                array_key_exists($key, $this->settingCache['global']) ) {
            
            return $this->settingCache['global'][$key];
                
        } else {
            
            return $this->getFileConfigSetting($key, $default);
        }
        
    }
               
    
    public function getUserSetting($key, $user_id, $default = null)
    {
        return (is_array($this->settingCache) && 
                array_key_exists('users', $this->settingCache) && is_array($this->settingCache['users']) &&
                array_key_exists($user_id, $this->settingCache['users']) && is_array($this->settingCache['users'][$user_id]) && 
                array_key_exists($key, $this->settingCache['users'][$user_id]) ) ? $this->settingCache['users'][$user_id][$key] : $default;    
    }
    
    
    /**
    * Ritorna una configurazione cercandola solo nei file di configurazione
    * 
    * @param mixed $key
    * @param mixed $default
    * @return Boolean
    */
    public function getFileConfigSetting($key, $default = null)
    {
        return ($this->gParameterBag->has(SettingManager::PREFIX_BUNDLE.'.'.$key)) ? $this->gParameterBag->get(SettingManager::PREFIX_BUNDLE.'.'.$key) : $default;   
    }
    
    
// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************      
    
    /**
    * Setta una configurazione nel container
    * 
    * @param mixed $key
    * @param mixed $value
    * @param mixed $container
    */
    public static function setGlobalSetting($key, $value, $container)
    {
        $container->setParameter(SettingManager::PREFIX_BUNDLE.'.'.$key, $value);    
    }
    
}