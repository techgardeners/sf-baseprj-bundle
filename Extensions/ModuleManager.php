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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\ORM\EntityManager;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class ModuleManager
{    
    const MODULE_NAME = '';
    
    const ERR_MODULE_NOT_ACTIVE = -100;
    
    // 
    protected $enabled;
    protected $configured;    
    protected $init;    

    // 
    protected $settingManager;    
    protected $session;
    protected $em;
    protected $tgKernel;    
    protected $serializer;    
 
    
    public function __construct()
    {
        $this->enabled = false;    
        $this->configured = false;      
        $this->init = false;      
    }
    
    // Effettua il settaggio della configurazione ed l'init minima necessaria (enabled fondamentalmente)
    public function hydrateModuleConfinguration(MainKernel $tgKernel)
    {
        $c = get_called_class();
        
        $this->tgKernel = $tgKernel;
        $this->settingManager = $this->tgKernel->settingManager;
        $this->session = $this->tgKernel->getSession();
        $this->em = $this->tgKernel->getEntityManager();
        $this->serializer =  \JMS\Serializer\SerializerBuilder::create()->build();

        $this->enabled = $this->settingManager->getGlobalSetting($c::MODULE_NAME.'.'.SettingManager::SUFFIX_ENABLE);
        
        $c::hydrateConfinguration($this->tgKernel);
        
        $this->configured = true;
    }    
    
    // (funzione sovrascritta dal figlio)
    public function hydrateConfinguration(MainKernel $tgKernel)
    {       
    } 
    
    // Inizializza il modulo
    public function initModule()
    {   
        
        $c = get_called_class();
        
        if ($this->isEnabled()) {
            $this->addDebugLap('Init module '.$c::MODULE_NAME);            
        }        
        
        $c::init();
        
        $this->init = true;        
    }
    
    // Inizializza il modulo (funzione sovrascritta dal figlio)   
    public function init()
    {
    }
       
       
// ********************************************************************************************************       
// METODI PUBBLICI       
// ********************************************************************************************************         
    
    public function isEnabled()
    {
        return $this->enabled;    
    }

    public function isConfigured()
    {
        return $this->configured;    
    }

    public function isInit()
    {
        return $this->init();    
    }

    public function addDebugLap($string, $ts = null)
    {
        $this->tgKernel->addDebugLap($string, $ts);    
    }    
       
// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************       
       
    // Setta le configurazioni per il modulo in oggetto
    public static function setModuleConfiguration(array $config, ContainerBuilder $container)
    {   
        $c = get_called_class();
        
        $isEnabled = (array_key_exists($c::MODULE_NAME, $config) && array_key_exists(SettingManager::SUFFIX_ENABLE, $config[$c::MODULE_NAME])) ? $config[$c::MODULE_NAME][SettingManager::SUFFIX_ENABLE] : false;
             
        // Setta il valore dell' enabled
        SettingManager::setGlobalSetting($c::MODULE_NAME.'.'.SettingManager::SUFFIX_ENABLE, $isEnabled, $container);        

        $c::setConfiguration($config, $container);
        
    }

    // Setta le configurazioni per il modulo in oggetto (funzione sovrascritta dal figlio)
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {        
    }
    

    public static function setSingleConf($name, array $config, ContainerBuilder $container)
    {
        $c = get_called_class();
        $moduleName = $c::MODULE_NAME;
        
        $configuration = (array_key_exists($moduleName, $config) && array_key_exists($name, $config[$moduleName])) ? $config[$moduleName][$name] : false;
        SettingManager::setGlobalSetting($moduleName.'.'.$name, $configuration, $container);        
    }
    
    protected static function returnNoEnable()
    {
        return self::ERR_MODULE_NOT_ACTIVE;
    }
    
    
}