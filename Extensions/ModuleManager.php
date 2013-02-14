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

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class ModuleManager
{    
    const MODULE_NAME = '';
    
    const ERR_MODULE_NOT_ACTIVE = -100;
    
    protected $isEnabled;    
    protected $settingManager;    
    protected $tgKernel;    
 
    
    public function __construct(MainKernel $tgKernel)
    {
        $c = get_called_class();
        
        $this->tgKernel = $tgKernel;
        $this->settingManager = $tgKernel->settingManager;
        $this->isEnabled = $this->settingManager->getGlobalSetting($c::MODULE_NAME.'.'.SettingManager::SUFFIX_ENABLE);

        if ($this->isEnabled()) {
            $this->addDebugLap('Init module '.$c::MODULE_NAME);            
        }
        
    }
 
    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {   
        $c = get_called_class();
        
        $isEnabled = (array_key_exists($c::MODULE_NAME, $config) && array_key_exists(SettingManager::SUFFIX_ENABLE, $config[$c::MODULE_NAME])) ? $config[$c::MODULE_NAME][SettingManager::SUFFIX_ENABLE] : false;
        
             
        // Setta il valore dell' enabled
        SettingManager::setGlobalSetting($c::MODULE_NAME.'.'.SettingManager::SUFFIX_ENABLE, $isEnabled, $container);        

    }
    
    
    public function isEnabled()
    {
        return $this->isEnabled;    
    }
    
    
    protected static function returnNoEnable()
    {
        return self::ERR_MODULE_NOT_ACTIVE;
    }
    
    public function addDebugLap($string, $ts = null)
    {
        $this->tgKernel->addDebugLap($string, $ts);    
    }    
       
    
}