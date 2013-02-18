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

use Symfony\Component\HttpFoundation\Response;

use TechG\Bundle\SfBaseprjBundle\Entity\Log;
use TechG\Bundle\SfBaseprjBundle\Entity\LogSession;

class LogManager extends BaseModule
{
    const MODULE_NAME = 'log';
    
    const CONF_LOG_LEVEL = 'level';
    const CONF_ENABLE_QUEUE = 'queue';
    const CONF_SAVE_SESSION = 'savesession';
    const CONF_SAVE_LAST_ACTIVITY = 'keepalive';
    const CONF_SAVE_REQUEST = 'saverequest';

    
    const SESSION_VARS_SAVED_SESSION = 'tgsfbaseprj/bundle/log/saved/session';
    const SESSION_VARS_SAVED_REQUEST = 'tgsfbaseprj/bundle/log/saved/request';
    
// ******************************************
// LIVELLI DI LOG
// ******************************************

    const LEVEL_SYSTEM = 0;
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
              
    const TYPE_SAVE_REQUEST = 1;    
    const TYPE_SAVE_RESPONSE = 2;    
    const TYPE_GENERIC = 100;    
    const TYPE_GENERIC_EXEPTION = 100;    
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
                              self::TYPE_SAVE_REQUEST => array( 'defaultLevel' => self::LEVEL_SYSTEM,                                                            
                                                         ),
                              self::TYPE_SAVE_RESPONSE => array( 'defaultLevel' => self::LEVEL_SYSTEM,                                                            
                                                         ),
                              self::TYPE_GENERIC_EXEPTION => array( 'defaultLevel' => self::LEVEL_ERROR,                                                            
                                                         ),
                              self::TYPE_GENERIC => array( 'defaultLevel' => self::LEVEL_INFO,                                                            
                                                         ),
                              self::TYPE_GENERIC_SQL => array( 'defaultLevel' => self::LEVEL_DEBUG,                                                            
                                                         ),
                              self::TYPE_GENERIC_TASK => array( 'defaultLevel' => self::LEVEL_DEBUG,                                                            
                                                         ),
                            );
    
    
    private $maxLogLevel;
    private $saveSession;
    private $saveRequest;
    private $keepAlive;
    private $enableQueue;
    
    private $sessionSaved;
    private $requestSaved;
    private $persistQueue;
    

// ********************************************************************************************************       
// METODI DI CONFIGURAZIONE E INIZIALIZZAZIONE       
// ********************************************************************************************************    
    
    public function hydrateConfinguration(MainKernel $tgKernel)
    {            
    
        $this->maxLogLevel = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_LOG_LEVEL);
        $this->enableQueue = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_ENABLE_QUEUE);
        $this->saveSession = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_SESSION);
        $this->saveRequest = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_REQUEST);
        $this->keepAlive = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_LAST_ACTIVITY);
         
    } 
    
    public function init()
    {   
        
        $this->requestSaved = null;
        $this->sessionSaved = null;
        $this->persistQueue = array();
        $this->logRequest();
        
    }        


// ********************************************************************************************************       
// METODI PUBBLICI       
// ******************************************************************************************************** 
 
    /**
    * Crea un log e lo salva
    * 
    * @param mixed $type
    * @param mixed $level
    * @param mixed $short
    * @param mixed $long
    * @param mixed $info
    * @param mixed $taskId
    * @param mixed $parentId
    */
    private function addRawLog($type = null, $level = null, $short = '', $long = '', $info = null, $taskId = null, $parentId = null )
    {   
        $log = $this->getRawLog($type, $level, $short, $long, $info, $taskId, $parentId);
        $this->saveLog($log);   
    }

    /**
    * Ritorna un oggetto Log compilato pronto per il salvataggio
    * 
    * @param mixed $type
    * @param mixed $level
    * @param mixed $short
    * @param mixed $long
    * @param mixed $info
    * @param mixed $taskId
    * @param mixed $parentId
    * @return Log
    */
    private function getRawLog($type = null, $level = null, $short = '', $long = '', $info = null, $taskId = null, $parentId = null )
    {
        if (!$this->isLoggable($level)) return null;
        
        $newLog = new Log();
        $newLog->setSessionId($this->session->getId());
        $newLog->setRequestId($this->tgKernel->requestId);
        $newLog->setLogLevel($level);
        $newLog->setLogType($type);
        $newLog->setParentId($parentId);
        $newLog->setTaskId($taskId);
        $newLog->setUser(null);
        $newLog->setInfo($info);

        return $newLog;

    } 
    
    private function saveLog($log, $forceImmediate = false)
    {              
        return (!$this->enableQueue || $forceImmediate) ?  $this->persisteLog($log) : $this->addToQueue($log);       
    }
    
    private function persisteLog($log, $flush = true)
    {
        if (is_null($log)) return false;
        
        $this->em->persist($log);
        if($flush) { $this->em->flush(); }
        
    }

    private function addToQueue($newLog)
    {
        if (is_null($newLog)) return false;
        
        // aggiungo il log alla coda dei log da salvare
        $this->persistQueue[] = $newLog;
    }    
    
    private function flushQueue()
    {
        if (!(count($this->persistQueue) > 0)) return false;
        
        foreach ($this->persistQueue as $idx=>$log) {
            $this->persisteLog($log, false);
            unset($this->persistQueue[$idx]);
        }
        
        $this->em->flush();
        return true;
    }    
    
    
    private function isLoggable($level)
    {
        return ($this->isEnabled() && $this->maxLogLevel >= $level);    
    }

//******************************************
    


    // Salva la sessione nel db
    public function logSession()
    {
       
        if (!$this->isEnabled() || !$this->saveSession) return false;

        // Check if just in session
        if ($this->session->has(self::SESSION_VARS_SAVED_SESSION) &&
            $this->session->get(self::SESSION_VARS_SAVED_SESSION) === $this->session->getId()){
              
              if (!$this->keepAlive) return false;
              
              $session = $this->getLogSessionSaved();
              $session->setLastActivity(new \DateTime());
              
              $this->em->persist($session);
              $this->em->flush();
              
              return true;
                
            } 
        
        // a volte è capitato che ricarica una sessione con lo stesso id;
        // quindi provo a ricaricarlo comunque dal db
        $newLogSession = $this->getLogSessionSaved();

        // persisto nel db
        $this->em->persist($newLogSession);
        $this->em->flush();
        
        $this->session->set(self::SESSION_VARS_SAVED_SESSION, $this->session->getId());
        $this->sessionSaved = $newLogSession;
    }


    // ************ EVENTS CONTROLLER *******************

    // Logga una request
    public function logRequest($forceSave = false)
    {
        
        if (!$this->isEnabled()) return false;
        
        // se i permessi lo consentono salvo la request
        if (!$this->saveRequest && !$forceSave) return false;
        
        if ($this->requestSaved) return false;
        
        
        $info = $this->tgKernel->requestInfo;        
        $this->addRawLog(self::TYPE_SAVE_REQUEST, self::LEVEL_SYSTEM, '', '', $info);

        $this->requestSaved = true;
        
    }
    
    // Logga una eccezione
    public function logException(\Exception $exception)
    {
        // Se avviene prima del salvataggio, la salvo, se è già salvata salta da solo
        $this->logRequest(true);
        
        // Aggiungo il log dell'eccezione        
        $info = array();        
        $info['code'] = $exception->getCode();        
        $info['file'] = $exception->getFile();        
        $info['line'] = $exception->getLine();        
        $info['message'] = $exception->getMessage();        
        $info['trace'] = $exception->getTrace();        
        
        $this->addRawLog(self::TYPE_GENERIC_EXEPTION, self::LEVEL_WARNING, '', '', $info);
                
    }
    
    // Logga una response
    public function logResponse(Response $response)
    {
        // se i permessi lo consentono salvo la request
        //if (!$this->saveRequest) return false;
        if (!$this->requestSaved) return false;

        // Aggiungo il log della response        
        $info = array();        
        $info['statusCode'] = $response->getStatusCode();
        $info['charset'] = $response->getCharset();
        $info['headers'] = $response->headers->all();
        
        $this->addRawLog(self::TYPE_SAVE_RESPONSE, self::LEVEL_SYSTEM, '', '', $info); 

    }
    
    // Chiude i log
    public function shutdown($event)
    {
        $this->flushQueue();
    }
    

// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************     

    // Ritorna l'oggetto di sessione salvato, se non lo trova nell'oggetto lo carica dal db
    private function getLogSessionSaved()
    {        
        if (is_null($this->sessionSaved))                 
            $this->sessionSaved = $this->em->getRepository("TechGSfBaseprjBundle:LogSession")->find($this->session->getId());
        
        if (is_null($this->sessionSaved))
            $this->sessionSaved = $this->getNewLogSessionObj(); 
        
        return $this->sessionSaved;
    }
 
     // Ritorna un nuovo oggetto di sessione pronto per essere salvato
    private function getNewLogSessionObj()
    {        
        $newLogSession = new LogSession();
        //save the session record
        $newLogSession->setId($this->session->getId());

        // Collect User info
        // Devo ricavare l'utente e se è loggato
        $user = $this->tgKernel->getUser();
        
        $userInfo = array();
        $userInfo['userInfo'] = $this->serializer->serialize($user, 'json');
        $userInfo['ip'] = $this->tgKernel->clientIp;
        $userInfo['browserInfo'] = $this->tgKernel->userBrowserInfo;

        $newLogSession->setInfoUser( $userInfo );
        
        // Collect Geo info
        $newLogSession->setInfoGeo( $this->serializer->serialize($this->tgKernel->userGeoPosition, 'json'), false);
        
        return $newLogSession; 
    }
 

// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  

    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        
        self::setSingleConf(self::CONF_LOG_LEVEL, $config, $container);
        self::setSingleConf(self::CONF_ENABLE_QUEUE, $config, $container);
        self::setSingleConf(self::CONF_SAVE_SESSION, $config, $container);
        self::setSingleConf(self::CONF_SAVE_REQUEST, $config, $container);
        self::setSingleConf(self::CONF_SAVE_LAST_ACTIVITY, $config, $container);
        
    }
        
        
}