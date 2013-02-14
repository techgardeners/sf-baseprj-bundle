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

class BlackListManager extends BaseModule
{    
    const MODULE_NAME = 'blacklist';
    


 
    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        parent::setConfiguration($config, $container);

    }
    
    public function executeFilter()
    {

 /*   public $modules = array(
       self::MODULE_NAME_BLACK_LIST => array('enabled' => false,
                                             'types' => array(BlackWhiteList::DATA_TYPE_GEO),                                   // possible value = ip|host|geo
                                             'geo_field' => 'countryCode',                                                                 // campo delle info geo su cui controllare
                                            ), 
       self::MODULE_NAME_WHITE_LIST => array('enabled' => false,
                                             'types' => array(),
                                             'geo_field' => '',                                                                 // campo delle info geo su cui controllare                                             
                                            ),    
    );    */


        /* 
        if ($this->isModuleEnabled(self::MODULE_NAME_BLACK_LIST)) {

            foreach ($this->modules[self::MODULE_NAME_BLACK_LIST]['types'] as $type) {

                $_data = BlackWhiteList::getDataFromKernel($this, $type, $this->modules[self::MODULE_NAME_BLACK_LIST]);
                
                if (!is_null($_data)) {
                    $_inList = BlackWhiteList::isInList($this->em, BlackWhiteList::LIST_TYPE_BLACK, $_data, $type);
                
                    if ($_inList) {
                        //header("location: http://www.tin.it");
                        //echo "in lista";
                        //exit();
                    }             
                }
            }
            
        }
        */       
    }    
    
}