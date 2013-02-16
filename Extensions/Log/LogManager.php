<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\Log;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

use TechG\Bundle\SfBaseprjBundle\Entity\Log;
use TechG\Bundle\SfBaseprjBundle\Entity\LogSession;

class LogManager extends BaseModule
{
    const MODULE_NAME = 'log';
    
    const CONF_LOG_LEVEL = 'level';
    const CONF_SAVE_SESSION = 'savesession';
    const CONF_SAVE_LAST_ACTIVITY = 'keepalive';
    const CONF_SAVE_REQUEST = 'saverequest';

    
    const SESSION_VARS_SAVED_SESSION = 'tgsfbaseprj/bundle/log/saved/session';
    const SESSION_VARS_SAVED_REQUEST = 'tgsfbaseprj/bundle/log/saved/request';
    
// ******************************************
// LIVELLI DI LOG
// ******************************************

    const LEVEL_ERROR = 100;
    const LEVEL_WARNING = 200;
    const LEVEL_APP = 300;
    const LEVEL_LOG = 400;
    const LEVEL_INFO = 500;
    const LEVEL_DEBUG = 600;
    const LEVEL_VERBOSE = 700;
    const LEVEL_INSANE = 800;

// ******************************************
// TIPI DI LOG
// ******************************************
              
    const TYPE_GENERIC = 100;    
    const TYPE_GENERIC_SQL = 200;
    const TYPE_GENERIC_TASK = 300;
    const TYPE_GENERIC_APP = 300;
    const TYPE_GENERIC_ERROR = 300;
    const TYPE_GENERIC_WARNING = 300;
    const TYPE_GENERIC_INFO = 300;
    const TYPE_GENERIC_DEBUG = 300;
    const TYPE_GENERIC_VERBOSE = 300;
    const TYPE_GENERIC_INSANE = 300;

    static $logTypes = array(
                              self::TYPE_GENERIC => array( 'defaultLevel' => self::TYPE_GENERIC_INFO,                                                            
                                                         ),
                              self::TYPE_GENERIC_SQL => array( 'defaultLevel' => self::LEVEL_DEBUG,                                                            
                                                         ),
                              self::TYPE_GENERIC_TASK => array( 'defaultLevel' => self::LEVEL_DEBUG,                                                            
                                                         ),
                            );
    
    
    private $logLevel;
    private $saveSession;
    private $saveRequest;
    private $keepAlive;
    
    private $sessionSaved;
    

// ********************************************************************************************************       
// METODI DI CONFIGURAZIONE E INIZIALIZZAZIONE       
// ********************************************************************************************************    
    
    public function hydrateConfinguration(MainKernel $tgKernel)
    {            
    
        $this->logLevel = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_LOG_LEVEL);
        $this->saveSession = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_SESSION);
        $this->saveRequest = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_REQUEST);
        $this->keepAlive = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_LAST_ACTIVITY);
         
    } 
    
    public function init()
    {   
        
        $this->sessionSaved = null;
        $this->logRequest();
        
    }        


// ********************************************************************************************************       
// METODI PUBBLICI       
// ******************************************************************************************************** 
 
    private function addRawLog($type = null, $level = null, $short = '', $long = '', $info = null, $taskId = null, $parentId = null )
    {
        $newLog = new Log();
        $newLog->setSessionId($this->session->getId());
        $newLog->setRequestId($this->tgKernel->requestId);
        $newLog->setLogLevel($level);
        $newLog->setLogType($type);
        $newLog->setParentId($parentId);
        $newLog->setTaskId($taskId);
        $newLog->setUser(null);
        $newLog->setInfo($info);

        // salvo il log
        $this->em->persist($newLog);
        $this->em->flush();

    } 
    
    // Salva la sessione nel db
    public function logSession()
    {
       
        if (!$this->saveSession) return false;

        // Check if just in session
        if ($this->session->has(self::SESSION_VARS_SAVED_SESSION) &&
            $this->session->get(self::SESSION_VARS_SAVED_SESSION) === $this->session->getId()){
              
              if (!$this->keepAlive) return false;
              
              $session = $this->getSessionSaved();
              $session->setLastActivity(new \DateTime());
              
              $this->em->persist($session);
              $this->em->flush();
              
              return true;
                
            } 
        
        //save the session record
        $newLogSession = new LogSession();
        $newLogSession->setId($this->session->getId());

        // Collect User info
        $userInfo = array();
        $userInfo['ip'] = $this->tgKernel->clientIp;
        $userInfo['browserInfo'] = $this->tgKernel->userBrowserInfo;
        $newLogSession->setInfoUser( json_encode($userInfo) );
        
        // Collect Geo info
        $newLogSession->setInfoGeo( json_encode($this->tgKernel->userGeoPosition->getLogInfo()) );         
        
        // persisto nel db
        $this->em->persist($newLogSession);
        $this->em->flush();
        
        $this->session->set(self::SESSION_VARS_SAVED_SESSION, $this->session->getId());
        $this->sessionSaved = $newLogSession;
    }
    
    
    public function logRequest()
    {
        // se i permessi lo consentono salvo la request
        if (!$this->saveRequest) return false;
        
        $info = $this->tgKernel->requestInfo;        
        $this->addRawLog(self::TYPE_GENERIC_APP, self::LEVEL_APP, '', '', $info);
        
    }
    

// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************     

    // Ritorna l'oggetto di sessione salvato, se non lo trova nell'oggetto lo carica dal db
    private function getSessionSaved()
    {        
        if (is_null($this->sessionSaved))                 
            $this->sessionSaved = $this->em->getRepository("TechGSfBaseprjBundle:LogSession")->find($this->session->getId());
        
        return $this->sessionSaved;
    }
 

// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  

    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        
        self::setSingleConf(self::CONF_LOG_LEVEL, $config, $container);
        self::setSingleConf(self::CONF_SAVE_SESSION, $config, $container);
        self::setSingleConf(self::CONF_SAVE_REQUEST, $config, $container);
        self::setSingleConf(self::CONF_SAVE_LAST_ACTIVITY, $config, $container);
        
    }
        
        
}