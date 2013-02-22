<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\GPanel;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class GPanelManager extends BaseModule
{    
    const MODULE_NAME = 'gpanel';    

    
    const CONF_SECURED = 'secured';
    const CONF_SECURED_ROLE = 'securedrole';
    
    
    private $isSecured;
    private $securedRole;
    
// ********************************************************************************************************       
// METODI DI CONFIGURAZIONE E INIZIALIZZAZIONE       
// ********************************************************************************************************

    public function __construct(ContainerInterface $container, SettingManager $settingManager)
    {
        parent::__construct($container, $settingManager);
        
        $this->isSecured = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SECURED);              
        $this->securedRole = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SECURED_ROLE);       
                                
    }        
    
// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************     
    
    
// ********************************************************************************************************       
// METODI PUBBLICI       
// ********************************************************************************************************  
   
   public function getAccessRole()
   {
       return $this->securedRole;
   } 

   public function isSecured()
   {
       return $this->isSecured;
   } 

// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  

    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        self::setSingleConf(self::CONF_SECURED, $config, $container);        
        self::setSingleConf(self::CONF_SECURED_ROLE, $config, $container);        
        
    }    

    
}