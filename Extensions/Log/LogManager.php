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
use Symfony\Component\DependencyInjection\ContainerInterface;

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

use TechG\Bundle\SfBaseprjBundle\Event\TechGKernelInitEvent;

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
                              self::LEVEL_APP => array( 'label' => 'app',
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
    const TYPE_TWIG_RUNTIME_EXCEPTION = 151;    
    const TYPE_GENERIC_404_EXCEPTION = 101;    
    const TYPE_GENERIC_EXCEPTION = 100;    
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
                              self::TYPE_GENERIC_404_EXCEPTION => array( 'defaultLevel' => self::LEVEL_WARNING,
                                                                    'title' => 'PAGE_NOT_FOUND',
                                                                    'label' => 'err404'                                                            
                                                         ),
                              self::TYPE_TWIG_RUNTIME_EXCEPTION => array( 'defaultLevel' => self::LEVEL_ERROR,
                                                                    'title' => 'TWIG_EXCEPTION',
                                                                    'label' => 'twig'                                                            
                                                         ),
                              self::TYPE_GENERIC_EXCEPTION => array( 'defaultLevel' => self::LEVEL_ERROR,
                                                                    'title' => 'GENERIC_EXCEPTION',
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
                                                            'title' => 'APP_LOG',
                                                            'label' => 'small information'                                                            
                                                         ),
                              self::TYPE_GENERIC_INFO => array( 'defaultLevel' => self::LEVEL_INFO,
                                                                'title' => 'GENERIC_INFO',
                                                                'label' => 'info'                                                            
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
    private $requestSaved;
    private $persistQueue;
    
    private $loggedRequest;
    private $loggedSession;
    

// ********************************************************************************************************       
// METODI DI CONFIGURAZIONE E INIZIALIZZAZIONE       
// ********************************************************************************************************    

    public function __construct(ContainerInterface $container, SettingManager $settingManager)
    {
        parent::__construct($container, $settingManager);
        
        $this->requestSaved = null;
        $this->sessionSaved = null;
        $this->persistQueue = array();        
        $this->loggedRequest = null;
        $this->loggedSession = null;

        // Recupero le configurazioni del modulo
        $this->maxLogLevel = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_LOG_LEVEL);
        $this->enableQueue = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_ENABLE_QUEUE);
        $this->saveSession = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_SESSION);
        $this->saveRequest = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_REQUEST);
        $this->keepAlive = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SAVE_LAST_ACTIVITY);
        $this->skipPattern = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SKIP_PATTERN);
        
        // Controllo se devo disabilitare i log per un pattern
        if ($this->skipPattern != '') {
            if (preg_match($this->skipPattern, $container->get('request')->getRequestUri())) {
                $this->enabled = false;
            }    
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
        
        if (!is_null($this->loggedRequest)){
            if ($level === self::LEVEL_WARNING) {
                $this->loggedRequest['info']['request_warning'] = true;    
            }
            if ($level === self::LEVEL_ERROR) {
                $this->loggedRequest['info']['request_error'] = true;    
            }            
            // Aggiungo un contatore dei tipi di log agganciati alla request
            if (array_key_exists($level, $this->loggedRequest['info']['request']['typecount'])) {
                $this->loggedRequest['info']['request']['typecount'][$level] += 1;
            } else {
                $this->loggedRequest['info']['request']['typecount'][$level] = 1;    
            }
        }
        
        $log = $this->getRawLog($type, $level, $short, $long, $info, $taskId, $parentId, $user);
        $this->saveLog($log);   
    }
    
    /**
    * Crea Un Log di tipo Info
    * 
    * @param mixed $type
    * @param mixed $level
    * @param mixed $short
    * @param mixed $long
    * @param mixed $info
    * @param mixed $taskId
    * @param mixed $parentId
    */
    public function addAppLog($short = '', $long = '', $info = null)
    {   
        return $this->addRawLog(self::TYPE_GENERIC_APP, self::LEVEL_APP, $short, $long, $info);
    }    


//******************************************
    

// ********************************************************************************************************       
// GESTORI EVENTI       
// ******************************************************************************************************** 

    /**
    * Reagisce all'evento di inizializzazione del kernel
    * 
    * @param TechGKernelInitEvent $event
    */
    public function onTechGKernelInit(TechGKernelInitEvent $event)
    {         
        // Preparo subito il log della request (nel caso poi lo integro)
        $this->loggedRequest = $this->getNewLogRequest();   
        $this->loggedSession = $this->getLoggedSession();            
    }

    public function onKernelRequest(GetResponseEvent $event) 
    {
        if (!$this->requestSaved) {
            // Aggiungo rotta e controller ( e locale corretto)
            $this->updateRequest();
        }       
    }

    public function onKernelResponse(FilterResponseEvent $event) 
    {        
        $this->logResponse($event->getResponse());
    }

    public function onKernelException(GetResponseForExceptionEvent $event) 
    {        
        $this->logException($event->getException());
    }
        
    // Chiude i log
    public function onKernelTerminate(PostResponseEvent $event)
    {

        $this->updateAndPersistSession($this->loggedSession);
        
        $this->flushQueue();
        $this->em->flush();
        
    }    
    

// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************     

    // Logga una eccezione
    private function logException(\Exception $exception)
    {
        // Forzo il salvataggio della request in caso di eccezione
        // TODO: non dovrebbe piu essere necessario, se c'è qualcosa in coda lui la request la salva
        $this->saveRequest = true;

        $type = self::TYPE_GENERIC_EXCEPTION;
        $level = self::getDefaultLevel($type);        
            
        $classe = get_class($exception);
        
        switch($classe){
            case 'Symfony\Component\HttpKernel\Exception\NotFoundHttpException':
                                                                                    $type = self::TYPE_GENERIC_404_EXCEPTION;
                                                                                    $level = self::getDefaultLevel($type);                                                                                    
                                                                                    
                                                                                    break;
            case 'Twig_Error_Runtime':
                                        $type = self::TYPE_TWIG_RUNTIME_EXCEPTION;
                                        $level = self::getDefaultLevel($type);
                                        break;
        }
            
            
        // Aggiungo il log dell'eccezione        
        $info = self::getLogInfoByException($exception);
        $this->addRawLog($type, $level, '', '', $info);
                
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
            $this->addToQueue($this->loggedRequest);
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
    private function getLoggedSession()
    {   
        $loggedSession = $this->loggedSession;
         
        if (is_null($loggedSession))                 
            $loggedSession = $this->em->getRepository("TechGSfBaseprjBundle:LogSession")->find($this->tgKernel->cookieId);
        
        if (is_null($loggedSession))
            $loggedSession = $this->getNewLogSessionObj(); 
        
        return $loggedSession;
    }

    // aggiorna la sessione nel db
    private function updateAndPersistSession($session)
    {
               
        if ($this->keepAlive) {
            $session->setLastActivity(new \DateTime());
            $session->setSessionId($this->session->getId());
            $userInfo = $session->getInfoUser();
            $userInfo['ip'] = $this->tgKernel->getMasterRequest('ip');
            $userInfo['last_uri'] = $this->tgKernel->getMasterRequest('requestUri');
            if (!$userInfo['auth'] && is_object($this->tgKernel->getUser()) && in_array('ROLE_USER',$this->tgKernel->getUser()->getRoles())) {
                $userInfo['auth'] = true;
                $userInfo['userInfo'] = $this->serializer->serialize($this->tgKernel->getUser(), 'json');                
            }
            
            $session->setInfoUser($userInfo);
        }

        $this->em->persist($session);             
        return true;

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
        $userInfo['auth'] = (is_object($this->tgKernel->getUser()) && in_array('ROLE_USER',$this->tgKernel->getUser()->getRoles())) ? true : false;
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


    // ritorna un loggedRequest da salvare
    private function getNewLogRequest()
    {        
        if (!$this->isEnabled()) return false;

        $info['request'] = $this->tgKernel->getMasterRequest();
        $info['request']['typecount'] = array();
        
        return $this->getRawLog(self::TYPE_SAVE_REQUEST, self::LEVEL_SYSTEM, '', '', $info);
        
    }  

    // aggiorna la request
    private function updateRequest()
    {
        $this->loggedRequest['info']['request'] = array_merge($this->loggedRequest['info']['request'], $this->tgKernel->getMasterRequest());
    }    

    
    // Logga una response
    private function logResponse(Response $response)
    {
        // se è presente una request
        if (!$this->loggedRequest) return false;
                
        // Aggiungo il log della response        
        $this->loggedRequest['info']['response'] = $this->serializer->serialize($response, 'json'); 

    }    
    
// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  


    public function linkNoCookieLog($tempCookieId, $cookieId)  
    {
        return $this->em->getRepository("TechGSfBaseprjBundle:Log")->linkLogToNewCookieId($tempCookieId, $cookieId);
    }
    

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