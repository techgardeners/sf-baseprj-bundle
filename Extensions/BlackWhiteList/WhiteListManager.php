<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\BlackWhiteList;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class WhiteListManager extends BaseModule
{    
    const MODULE_NAME = 'whitelist';
    
 
    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        parent::setConfiguration($config, $container);

    }
    
    
    public function executeFilter()
    {
        /* 
        if ($this->isModuleEnabled(self::MODULE_NAME_WHITE_LIST)) {
        
            foreach ($this->modules[self::MODULE_NAME_WHITE_LIST]['types'] as $type) {

                $_data = BlackWhiteList::getDataFromKernel($this, $type, $this->modules[self::MODULE_NAME_WHITE_LIST]);
                
                if (!is_null($_data)) {
                    $_inList = BlackWhiteList::isInList($this->em, BlackWhiteList::LIST_TYPE_WHITE, $_data, $type);
                    
                    if (!$_inList) {
                        //header("location: http://www.tin.it");
                        //exit();
                    }                
                }
            }
            
        } 
        */       
    }
    
    
    
}