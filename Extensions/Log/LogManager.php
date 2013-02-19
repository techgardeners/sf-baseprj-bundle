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

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

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
    const CONF_SKIP_PATTERN = 'skippattern';

    
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

    static $logLevels = array(
                              self::LEVEL_SYSTEM => array( 'label' => 'system',
                                                           ),
                              self::LEVEL_ERROR => array( 'label' => 'error',
                                                           ),
                              self::LEVEL_WARNING => array( 'label' => 'warning',
                                                           ),
                              self::LEVEL_LOG => array( 'label' => 'log',
                                                           ),
                              self::LEVEL_INFO => array( 'label' => 'info',
                                                           ),
                              self::LEVEL_DEBUG => array( 'label' => 'debug',
                                                           ),
                              self::LEVEL_VERBOSE => array( 'label' => 'verbose',
                                                           ),
                              self::LEVEL_INSANE => array( 'label' => 'insane',
                                                           ),
                            );    
    
// ******************************************
// TIPI DI LOG
// ******************************************
              
    const TYPE_SAVE_REQUEST = 1;
    const TYPE_SYSTEM = 0;    
    const TYPE_GENERIC = 1000;    
    const TYPE_GENERIC_EXEPTION = 100;    
    const TYPE_GENERIC_SQL = 200;
    const TYPE_GENERIC_TASK = 300;
    const TYPE_GENERIC_APP = 400;
    const TYPE_GENERIC_ERROR = 500;
    const TYPE_GENERIC_WARNING = 600;
    const TYPE_GENERIC_INFO = 700;
    const TYPE_GENERIC_DEBUG = 800;
    const TYPE_GENERIC_VERBOSE = 900;
    const TYPE_GENERIC_INSANE = 1000;
    
    const TYPE_GEO_ERROR = 50;

    static $logTypes = array(
                              self::TYPE_GEO_ERROR => array( 'defaultLevel' => self::TYPE_GENERIC_WARNING,
                                                             'title' => 'Geo decoding error',
                                                             'label' => 'geoerror'
                                                           ),
                              self::TYPE_SYSTEM => array( 'defaultLevel' => self::LEVEL_SYSTEM,
                                                          'title' => 'SYS',
                                                          'label' => ''
                                                         ),
                              self::TYPE_SAVE_REQUEST => array( 'defaultLevel' => self::LEVEL_SYSTEM,
                                                                'title' => 'REQUEST',
                                                                'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_EXEPTION => array( 'defaultLevel' => self::LEVEL_ERROR,
                                                                    'title' => 'GENERIC_EXEPTION',
                                                                    'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC => array( 'defaultLevel' => self::LEVEL_INSANE,
                                                            'title' => 'GENERIC',
                                                            'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_SQL => array( 'defaultLevel' => self::LEVEL_DEBUG,
                                                                'title' => 'GENERIC_SQL',
                                                                'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_TASK => array( 'defaultLevel' => self::LEVEL_DEBUG,
                                                                  'title' => 'GENERIC_TASK',
                                                                  'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_APP => array( 'defaultLevel' => self::LEVEL_APP,
                                                            'title' => 'GENERIC_APP',
                                                            'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_INFO => array( 'defaultLevel' => self::LEVEL_INFO,
                                                                'title' => 'GENERIC_INFO',
                                                                'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_ERROR => array( 'defaultLevel' => self::LEVEL_ERROR,
                                                            'title' => 'GENERIC_ERROR',
                                                            'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_WARNING => array( 'defaultLevel' => self::LEVEL_WARNING,
                                                            'title' => 'GENERIC_WARNING',
                                                            'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_DEBUG => array( 'defaultLevel' => self::LEVEL_DEBUG,
                                                            'title' => 'GENERIC_DEBUG',
                                                            'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_VERBOSE => array( 'defaultLevel' => self::LEVEL_VERBOSE,
                                                            'title' => 'GENERIC_VERBOSE',
                                                            'label' => ''                                                            
                                                         ),
                              self::TYPE_GENERIC_INSANE => array( 'defaultLevel' => self::LEVEL_INSANE,
                                                            'title' => 'GENERIC_INSANE',
                                                            'label' => ''                                                            
                                                         ),
                            );
    
    
    private $maxLogLevel;
    private $saveSession;
    private $saveRequest;
    private $keepAlive;
    private $enableQueue;
    private $skipPattern;
    
    private $sessionSaved;
    private $requestSaved;
    private $persistQueue;
    
    private $logRequest;
    

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
        $this->skipPattern = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SKIP_PATTERN);
         
    } 
    
    public function init()
    {   
        
        $this->requestSaved = null;
        $this->sessionSaved = null;
        $this->persistQueue = array();        
        $this->logRequest = null;

        // Controllo se devo disabilitare i log per un pattern
        if ($this->skipPattern != '') {
            if (preg_match($this->skipPattern, $this->tgKernel->getMasterRequest('requestUri'))) {
                $this->enabled = false;
            }    
        }
        
        if ($this->isEnabled()) {
            // Preparo subito il log della request (nel caso poi lo integro)
            $this->logRequest = $this->getNewLogRequest();   
        }          
        
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
    public function addRawLog($type = null, $level = null, $short = '', $long = '', $info = null, $taskId = null, $parentId = null, $user = null)
    {   
        
        if (!is_null($this->logRequest)){
            if ($level === self::LEVEL_WARNING) {
                $this->logRequest['info']['request_warning'] = true;    
            }
            if ($level === self::LEVEL_ERROR) {
                $this->logRequest['info']['request_error'] = true;    
            }            
        }

        
        $log = $this->getRawLog($type, $level, $short, $long, $info, $taskId, $parentId, $user);
        $this->saveLog($log);   
    }


//******************************************
    

    // persiste la sessione nel db
    public function persistSession()
    {
       
        if (!$this->isEnabled()) return false;
        
        $session = $this->getLogSessionSaved();
        
        if ($this->keepAlive) {
            $session->setLastActivity(new \DateTime());
            $userInfo = $session->getInfoUser();
            $userInfo['ip'] = $this->tgKernel->getMasterRequest('ip');
            $userInfo['last_uri'] = $this->tgKernel->getMasterRequest('requestUri');
            if (!$userInfo['auth'] && is_object($this->tgKernel->getUser()) && $this->tgKernel->getUser()->hasRole('ROLE_USER')) {
                $userInfo['auth'] = true;
                $userInfo['userInfo'] = $this->serializer->serialize($this->tgKernel->getUser(), 'json');                
            }
            
            $session->setInfoUser($userInfo);
        }

        $this->em->persist($session);
              
        return true;

    }

    // ************ EVENTS CONTROLLER *******************

    public function onRequest(GetResponseEvent $event) 
    {
        if ($this->isEnabled() && !$this->requestSaved) {
            // Aggiungo i settaggi del secondo giro
            $this->persistSession();
            $this->logRequest['info']['request'] = array_merge($this->logRequest['info']['request'], $this->tgKernel->getMasterRequest());   
        }        
    }

    public function onResponse(FilterResponseEvent $event) 
    {        
        $this->logResponse($event->getResponse());
    }

    public function onException(GetResponseForExceptionEvent $event) 
    {        
        $this->persistSession();
        $this->logException($event->getException());
    }

        
    // Logga una eccezione
    public function logException(\Exception $exception)
    {
        // Forzo il salvataggio della request in caso di eccezione
        // TODO: non dovrebbe piu essere necessario, se c'è qualcosa in coda lui la request la salva
        $this->saveRequest = true;

        
        // Aggiungo il log dell'eccezione        
        $info = self::getLogInfoByException($exception);
        $this->addRawLog(self::TYPE_GENERIC_EXEPTION, self::getDefaultLevel(self::TYPE_GENERIC_EXEPTION), '', '', $info);
                
    }
    
    // Logga una response
    public function logResponse(Response $response)
    {
        // se è presente una request
        if (!$this->logRequest) return false;
                
        // Aggiungo il log della response        
        $this->logRequest['info']['response'] = $this->serializer->serialize($response, 'json'); 

    }

    // Chiude i log
    public function onTerminate($event)
    {
        
        $this->flushQueue();
        $this->em->flush();
        
        // Se ho un cookieIdTemporaneo devo agganciare i log dispersi
        if (null != $this->tgKernel->tempCookieId) {
            $res = $this->em->getRepository("TechGSfBaseprjBundle:Log")->linkLogToNewCookieId($this->tgKernel->tempCookieId, $this->tgKernel->cookieId);           
        }
        
    }    
    

// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************     


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
    private function getRawLog($type = null, $level = null, $short = '', $long = '', $info = null, $taskId = null, $parentId = null, $user = null )
    {
        if (!$this->isLoggable($level)) return null;
        
        $newLog = array();
        $newLog['cookieId'] = $this->tgKernel->cookieId;
        $newLog['sessionId'] = $this->session->getId();
        $newLog['requestId'] = $this->tgKernel->requestId;
        $newLog['logLevel'] = $level;
        $newLog['logType'] = $type;
        $newLog['parentId'] = $parentId;
        $newLog['taskId'] = $taskId;
        $newLog['user'] = $user;
        $newLog['info'] = $info;

        return $newLog;

    } 
    
    private function saveLog($log, $forceImmediate = false)
    {              
        return (!$this->enableQueue || $forceImmediate) ?  $this->persisteLog($log) : $this->addToQueue($log);       
    }
    
    private function persisteLog($log, $flush = true)
    {
        if (is_null($log)) return false;

        $newLog = new Log();
        $newLog->setCookieId($log['cookieId']);
        $newLog->setSessionId($log['sessionId']);
        $newLog->setRequestId($log['requestId']);
        $newLog->setLogLevel($log['logLevel']);
        $newLog->setLogType($log['logType']);
        $newLog->setParentId($log['parentId']);
        $newLog->setTaskId($log['taskId']);
        $newLog->setUser($log['user']);
        $newLog->setInfo($log['info']);

        $this->em->persist($newLog);
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
        // Se non c'è nulla da salvare esco subito
        if (!(count($this->persistQueue) > 0) && !($this->saveRequest && !$this->requestSaved)) return false;

        // salvo se c'è qualcosa in coda o se devo salvare la request
        if (!$this->requestSaved) {
            $this->addToQueue($this->logRequest);
            $this->requestSaved = true;    
        } 
        
        foreach ($this->persistQueue as $idx=>$log) {
            $this->persisteLog($log, false);
            unset($this->persistQueue[$idx]);
        }
        
        // $this->em->flush();
                
        return true;
    }    
        
    private function isLoggable($level)
    {
        return ($this->isEnabled() && $this->maxLogLevel >= $level);    
    }

    // Ritorna l'oggetto di sessione salvato, se non lo trova nell'oggetto lo crea nuovo
    private function getLogSessionSaved()
    {        
        if (is_null($this->sessionSaved))                 
            $this->sessionSaved = $this->em->getRepository("TechGSfBaseprjBundle:LogSession")->find($this->tgKernel->cookieId);
        
        if (is_null($this->sessionSaved))
            $this->sessionSaved = $this->getNewLogSessionObj(); 
        
        return $this->sessionSaved;
    }
 
     // Ritorna un nuovo oggetto di sessione pronto per essere salvato
    private function getNewLogSessionObj()
    {        
        $newLogSession = new LogSession();
        //save the session record
        $newLogSession->setId($this->tgKernel->cookieId);
        $newLogSession->setSessionId($this->session->getId());

        // Collect User info
        $userInfo = array();
        $userInfo['last_uri'] = $this->tgKernel->getMasterRequest('requestUri');
        $userInfo['auth'] = (!is_object($user) || !($user instanceof UserInterface)) ? false : $this->tgKernel->getUser()->hasRole('ROLE_USER');
        $userInfo['userInfo'] = $this->serializer->serialize($this->tgKernel->getUser(), 'json');
        $userInfo['ip'] = $this->tgKernel->getMasterRequest('ip');
        $userInfo['locale'] = $this->tgKernel->getMasterRequest('locale');
        $userInfo['guessedLocale'] = $this->tgKernel->getMasterRequest('guessedLocale');
        $userInfo['browserInfo'] = $this->tgKernel->userBrowserInfo;
        $userInfo['mobileInfo'] = null;

        $newLogSession->setInfoUser( $userInfo );
        
        // Collect Geo info
        $newLogSession->setInfoGeo( $this->serializer->serialize($this->tgKernel->userGeoPosition, 'json'), false);
        
        return $newLogSession; 
    }


    // ritorna un logRequest da salvare
    private function getNewLogRequest()
    {        
        if (!$this->isEnabled()) return false;

        $info['request'] = $this->tgKernel->getMasterRequest();
        $info['request']['warning'] = false;
        $info['request']['error'] = false;
        
        return $this->getRawLog(self::TYPE_SAVE_REQUEST, self::LEVEL_SYSTEM, '', '', $info);
        
    } 

// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  


    // Ritorna una array di info di un eccezione
    public static function getLogInfoByException(\Exception $exception)
    {
        // Aggiungo il log dell'eccezione        
        $info = array();        
        $info['code'] = $exception->getCode();        
        $info['file'] = $exception->getFile();        
        $info['line'] = $exception->getLine();        
        $info['message'] = $exception->getMessage();        
        $info['trace'] = $exception->getTrace();        
        
        return $info;
                
    }

    // Ritorna il livello di default in base al tipo di log
    public static function getDefaultLevel($logType)
    {
        return (array_key_exists($logType, self::$logTypes) && array_key_exists('defaultLevel', self::$logTypes[$logType])) ? self::$logTypes[$logType]['defaultLevel'] : self::LEVEL_LOG;        
    }

    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        
        self::setSingleConf(self::CONF_LOG_LEVEL, $config, $container);
        self::setSingleConf(self::CONF_ENABLE_QUEUE, $config, $container);
        self::setSingleConf(self::CONF_SAVE_SESSION, $config, $container);
        self::setSingleConf(self::CONF_SAVE_REQUEST, $config, $container);
        self::setSingleConf(self::CONF_SAVE_LAST_ACTIVITY, $config, $container);
        self::setSingleConf(self::CONF_SKIP_PATTERN, $config, $container);
        
    }
        
        
}