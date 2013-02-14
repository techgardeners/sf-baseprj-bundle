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
    
    private $settingCache = null;
    private $session = null;
    private $em = null;
    private $gParameterBag = null;

    public function __construct(ContainerInterface $container)
    {   
        $this->session = $container->get('session');
        $this->em = $container->get('doctrine')->getEntityManager();
        $this->gParameterBag = $container->getParameterBag();
        
        // Se trova i settaggi in sessione li carica
        if ($this->session->has(self::SESSION_VARS_CACHE)) {
            $this->settingCache = $this->session->get(self::SESSION_VARS_CACHE);    
        }
        
        if(is_null($this->settingCache)) {
            $this->loadSettingFromDb();
        }
        
    } 
    
    /**
    * Carica i settaggi dal db
    * 
    */
    public function loadSettingFromDb()
    {
        $this->settingCache = $this->em->getRepository('TechGSfBaseprjBundle:Setting')->loadAll();
        $this->session->set(self::SESSION_VARS_CACHE, $this->settingCache);            
    }
    
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
    
    
    
    /**
    *  STATIC METHOD
    */
    
    
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