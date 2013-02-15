<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\Mobiledetect;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;

class MobiledetectManager extends BaseModule
{    
    const MODULE_NAME = 'mobiledetect';
    
    public $mobileDetector;


    public function hydrateConfinguration(MainKernel $tgKernel)
    { 
                       
    } 
    
    public function init()
    {
        if ($this->isEnabled()) {
            $this->mobileDetector  = $this->tgKernel->getContainer()->get('mobile_detect.mobile_detector');
        }        
    }        
 

// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  

    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
    }
    
    
}