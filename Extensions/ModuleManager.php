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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use TechG\Bundle\SfBaseprjBundle\Event\TechGKernelInitEvent;

class ModuleManager
{    
    const MODULE_NAME = '';
    
    const ERR_MODULE_NOT_ACTIVE = -100;
    
    // 
    protected $enabled;
    protected $init;    

    // 
    protected $tgKernel;    
    protected $settingManager;    
    protected $session;
    protected $em;   
    protected $serializer;    
 
        
    public function __construct(ContainerInterface $container, SettingManager $settingManager)
    {
        $this->enabled = false;    
        $this->configured = false;      
        $this->init = false;
        
        $this->settingManager = $settingManager;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->session = $container->get('session');
        $this->serializer =  \JMS\Serializer\SerializerBuilder::create()->build();
        
        // Called Class related
        $c = get_called_class();
        $this->enabled = $this->settingManager->getGlobalSetting($c::MODULE_NAME.'.'.SettingManager::SUFFIX_ENABLE);

        if ($this->isEnabled()) {
            $this->addDebugLap('Init module '.$c::MODULE_NAME);            
        }  
        
        $this->init = true;        
    }    
     
    
    
//**************************************************************************************    
// TECHG KERNEL EVENTS HANDLER   
//**************************************************************************************   
    
    // Sovrascritta dal figlio
    public function onTechGKernelInit(TechGKernelInitEvent $event)
    {         
    }    
    
//**************************************************************************************    
// SYMFONY EVENTS HANDLER   
//**************************************************************************************      
    
    public function onKernelRequest(GetResponseEvent $event)
    {  
    }   

    public function onKernelController(FilterControllerEvent $event)
    {
    }       
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
    }     

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
    }    

    public function onKernelResponse(FilterResponseEvent $event)
    {
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
    }
    
  
// ********************************************************************************************************       
// METODI PUBBLICI       
// ********************************************************************************************************         
    
    public function setTgKernel($tgKernel)
    {
        $this->tgKernel = $tgKernel;
    }
    
    public function isEnabled()
    {
        return $this->enabled;    
    }

    public function isInit()
    {
        return $this->init();    
    }

    public function addDebugLap($string, $ts = null)
    {
        //$this->tgKernel->addDebugLap($string, $ts);    
    }    
       
    public function addRawLog($type = null, $level = null, $short = '', $long = '', $info = null, $taskId = null, $parentId = null, $user = null )
    {   
        //$this->tgKernel->addRawLog($type, $level, $short, $long, $info, $taskId, $parentId, $user);   
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