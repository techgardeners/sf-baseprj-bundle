<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\Debug;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class DebugManager extends BaseModule
{    
    const MODULE_NAME = 'debug';    


    public function __construct(SettingManager $settingManager)
    {
        parent::__construct($settingManager);
        
        // se è attivo il debug cancello i dati delle configurazioni in sessione
        if ($this->isEnabled()) {
            $settingManager->clearSession();
        }
        
    }
 
 
    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        parent::setConfiguration($config, $container);

    }    
    
}