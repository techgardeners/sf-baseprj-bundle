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

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

class DebugManager extends BaseModule
{    
    const MODULE_NAME = 'debug';    

    private $lapArr = array();
    private $startLapTs = 0;
    private $lastLapTs = 0;

// ********************************************************************************************************       
// METODI DI CONFIGURAZIONE E INIZIALIZZAZIONE       
// ********************************************************************************************************

    public function __construct()
    {
        $this->lastLapTs = $this->startLapTs = microtime();
    }
    
    public function hydrateConfinguration(MainKernel $tgKernel)
    {                 
    } 
    
    public function init()
    {
        // se è attivo il debug cancello i dati delle configurazioni in sessione
        if ($this->isEnabled()) {
            $this->settingManager->clearSession();
        }        
    }          

    
// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************     
    
    

    private function convertMsToTime($mc)
    {
        list($microSec, $timeStamp) = explode(" ", $mc);
        return date('H:i:', $timeStamp) . (date('s', $timeStamp) + $microSec);    
    }
    
    private function calcDiff($mc_start, $mc_end)
    {
        list($S_microSec, $S_timeStamp) = explode(" ", $mc_start);
        list($E_microSec, $E_timeStamp) = explode(" ", $mc_end);
        
        $sec = $E_timeStamp - $S_timeStamp;
        $mill = $E_microSec - $S_microSec;
        
        return round(0 + $sec + $mill, 5);
        
    }    

// ********************************************************************************************************       
// METODI PUBBLICI       
// ********************************************************************************************************  
    
    public function addLap($info, $microsec = null)
    {
        if (!$this->isEnabled()) return self::returnNoEnable();
        
        $microsec = (!is_null($microsec)) ? $microsec : microtime();
        
        $this->lapArr[$microsec] = array('info' => $info,
                                         'text' => $this->convertMsToTime($microsec),
                                         'startDiff' => $this->calcDiff($this->startLapTs, $microsec),
                                         'lastDiff' => $this->calcDiff($this->lastLapTs, $microsec),
                                         );
        $this->lastLapTs = $microsec;  
    } 
    
    public function getLapArr()
    {
        return $this->lapArr;
    }      


// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  

    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
    }    

    
}