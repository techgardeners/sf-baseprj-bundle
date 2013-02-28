<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\GPanel;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\ModuleManager as BaseModule;
use TechG\Bundle\SfBaseprjBundle\Extensions\Geocode\GeocoderManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\Log\LogManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\UtilityManager;

class GPanelManager extends BaseModule
{    
    const MODULE_NAME = 'gpanel';    

    
    const CONF_SECURED = 'secured';
    const CONF_SECURED_ROLE = 'securedrole';
    
    
    private $isSecured;
    private $securedRole;
    
// ********************************************************************************************************       
// METODI DI CONFIGURAZIONE E INIZIALIZZAZIONE       
// ********************************************************************************************************

    public function __construct(ContainerInterface $container, SettingManager $settingManager)
    {
        parent::__construct($container, $settingManager);
        
        $this->isSecured = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SECURED);              
        $this->securedRole = $this->settingManager->getGlobalSetting(self::MODULE_NAME.'.'.self::CONF_SECURED_ROLE);                                
    }        
    
// ********************************************************************************************************       
// METODI PRIVATI       
// ********************************************************************************************************     
    
    
// ********************************************************************************************************       
// METODI PUBBLICI       
// ********************************************************************************************************  
   
   public function getAccessRole()
   {
       return $this->securedRole;
   } 

   public function isSecured()
   {
       return $this->isSecured;
   } 

   
// ********************************************************************************************************       
// METODI TWIG       
// ********************************************************************************************************     
    
    // renderizza una sessione utente (con mappa)
    public function renderSession($session_id, $active_map = true, $include_request = false)
    {
    
        $sessione = $this->em->getRepository("TechGSfBaseprjBundle:LogSession")->find($session_id);        
        $userInfo = $sessione->getInfoUser();
        $userInfo['userInfo'] = json_decode($userInfo['userInfo'], true);
        $geoInfo = $sessione->getInfoGeo();
        
        $startSession = UtilityManager::time_ago($sessione->getLogDate());
        $lastActivity = UtilityManager::time_ago($sessione->getLastActivity());

        $map = $this->tgKernel->getGeoManager()
                              ->getMap($geoInfo);
        
        
        $returnValue = array();
        $returnValue['obj'] = $sessione;
        $returnValue['map'] = $map;
        $returnValue['requests'] = array();
        $returnValue['html'] = $this->tgKernel
                                        ->getTemplatingEngine()
                                            ->render('TechGSfBaseprjBundle:GPanel:Component/render_session.html.twig', 
                                                array('sessione' => $sessione,
                                                      'start' => $startSession,
                                                      'lastActivity' => $lastActivity,
                                                      'active_map' => $active_map,
                                                      'userInfo' => $userInfo,
                                                      'geoInfo' => $geoInfo,
                                                      'map' => $map,
                                                        ));
                                                        
                                                        
        if ($include_request) {
            
        }                                                        
                                                        
                                                        
                
        return $returnValue;
    }    

    
    // renderizza un Log
    public function renderWebSession($wsArr)
    {

        $logLevel = $this->em->getRepository("TechGSfBaseprjBundle:Log")
                                        ->getLogLevelByWebSessionId($wsArr['id']); 
        
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', $wsArr['startd']);
        $stop =  \DateTime::createFromFormat('Y-m-d H:i:s', $wsArr['stopd']);

        $duration = UtilityManager::time_difference_readable($start, $stop);
        $finish = UtilityManager::time_ago($stop);
        
        $levelStruct = array();
        
        foreach (LogManager::$logLevels as $level=>$info) {
            $levelStruct[$level]['tot'] = 0;
            $levelStruct[$level]['label'] = $info['label'];
        }
        
        
        foreach ($logLevel as $level) {
            $levelStruct[$level['log_level']]['tot'] += $level['tot'];
        }
        
        $returnValue = array();
        $returnValue['arr'] = $wsArr;
        $returnValue['html'] = $this->tgKernel
                                        ->getTemplatingEngine()
                                            ->render('TechGSfBaseprjBundle:GPanel:Component/session/render_websession.html.twig', 
                                                array('duration' => $duration,
                                                      'finish' => $finish,
                                                      'levelStruct' => $levelStruct,
                                                        ));
                                           
                
        return $returnValue;    
    }    
   
    
    
    
    // renderizza una request
    public function renderRequest($request_id)
    {
    
        $serializer =  \JMS\Serializer\SerializerBuilder::create()->build();

        $request = null;
        $response = null;

        $reqStatus = 'ok';
        $warning = false;
        $reqIcon = 'page';
        $returnCode = 0;
        
        $requestLogs = $this->em->getRepository("TechGSfBaseprjBundle:Log")->getLogsByRequestId($request_id);        
        
        // Setto la request e la response
        foreach($requestLogs as $k=>$log){

            // decodifico gli info
            $log['info'] = json_decode($requestLogs[$k]['info'], true);

            // Se è la request la tolgo dall'array e la tratto a parte
            if ($log['log_type'] == LogManager::TYPE_SAVE_REQUEST) {
                    
                $request = $log['info']['request'];                                                
                $request['queryString'] = ($request['queryString'] != '') ? '?'.$request['queryString'] : '';
                $request['onlyBaseUri'] = str_replace($request['queryString'], '', $request['requestUri']);
                $request['data'] = UtilityManager::time_ago(\DateTime::createFromFormat('Y-m-d H:i:s', $log['log_date']));
                
                // Se esiste la response, prendo anche quella
                if (array_key_exists('response', $log['info'])) {                    
                    $response = json_decode($log['info']['response'], true);
                    $returnCode = $response['status_code'];
                }     
                    
                // infine la tolgo dall'array dei log validi
                unset($requestLogs[$k]);
            
            } else {
                
               $requestLogs[$k] = $this->renderLog($log);
               
            }   
        }
        
        
        switch ($returnCode){
            
            case '500':
            case '501':
                        $reqStatus = 'error';
                        $reqIcon = 'http_status_server_error';
                        break;
            case '404':
                        $reqStatus = 'nofound';
                        $reqIcon = 'page_white_error';
                        break;
            case 0:
                        $reqStatus = 'warning';
                        $reqIcon = 'page_white_error';
                        break;
            case 302:
                        $reqStatus = 'forward';
                        $reqIcon = 'arrow_up';
                        break;
            default:
                        if (array_key_exists(LogManager::LEVEL_WARNING, $request['typecount'])) {
                            $reqStatus = 'warning';
                            $reqIcon = 'page_white_error';    
                        }
                        if (array_key_exists(LogManager::LEVEL_ERROR, $request['typecount'])) {
                            $reqStatus = 'error';    
                            $reqIcon = 'http_status_server_error';    
                        }
                        break; 
        }
       
        
        $returnValue = array();
        $returnValue['obj'] = $request;
        $returnValue['html'] = $this->tgKernel
                                        ->getTemplatingEngine()
                                            ->render('TechGSfBaseprjBundle:GPanel:Component/render_request.html.twig', 
                                                array('request' => $request,
                                                      'response' => $response,
                                                      'logs' => $requestLogs,
                                                      'reqStatus' => $reqStatus,
                                                      'reqIcon' => $reqIcon,
                                                        ));
                                           
                
        return $returnValue;
    }  

    
    // renderizza un Log
    public function renderLog($log)
    {

        $log['gPanel']['log_class'] = LogManager::$logLevels[$log['log_level']]['label'];
        $log['gPanel']['title'] = LogManager::$logTypes[$log['log_type']]['title'];
        $log['gPanel']['icon_class'] = LogManager::$logTypes[$log['log_type']]['label'];

        $returnValue = array();
        $returnValue['obj'] = $log;
        $returnValue['html'] = $this->tgKernel
                                        ->getTemplatingEngine()
                                            ->render('TechGSfBaseprjBundle:GPanel:Component/log/log_generic.html.twig', 
                                                array('log' => $log,
                                                        ));
                                           
                
        return $returnValue;    
    }    
   
// ********************************************************************************************************       
// METODI STATICI       
// ********************************************************************************************************  

    // Setta le configurazioni per il modulo in oggetto
    public static function setConfiguration(array $config, ContainerBuilder $container)
    {
        self::setSingleConf(self::CONF_SECURED, $config, $container);        
        self::setSingleConf(self::CONF_SECURED_ROLE, $config, $container);        
        
    }    

    
}