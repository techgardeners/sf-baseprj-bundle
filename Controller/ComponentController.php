<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ivory\GoogleMapBundle\Model\MapTypeId;
use Ivory\GoogleMapBundle\Model\Overlays\Animation;

use TechG\Bundle\SfBaseprjBundle\Extensions\Log\LogManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\UtilityManager;

class ComponentController extends Controller
{  
    
    public function renderSessionAction($session_id)
    {
    
        $em = $this->getDoctrine()->getEntityManager();
        $sessione = $em->getRepository("TechGSfBaseprjBundle:LogSession")->find($session_id);        
        $userInfo = $sessione->getInfoUser();
        $userInfo['userInfo'] = json_decode($userInfo['userInfo'], true);
        $geoInfo = $sessione->getInfoGeo();
        
        $startSession = UtilityManager::time_ago($sessione->getLogDate());
        $lastActivity = UtilityManager::time_ago($sessione->getLastActivity());
        
        $map = $this->getMap($geoInfo, $sessione);

        
        // prendo i log collegati alla sessione
        
        $requests = $em->getRepository("TechGSfBaseprjBundle:Log")->getRequestIdsBySessionId($session_id, 10); 
        
        
        return $this->render('TechGSfBaseprjBundle:Component:render_session.html.twig', array('sessione' => $sessione,
                                                                                              'start' => $startSession,
                                                                                              'lastActivity' => $lastActivity,
                                                                                              'userInfo' => $userInfo,
                                                                                              'geoInfo' => $geoInfo,
                                                                                              'map' => $map,
                                                                                              'requests' => $requests,
                                                                                                ));
    }    


    public function renderRequestAction($request_id)
    {
    
        $request = null;
        $requestDate = null;
        $response = null;
        $reqStatus = 'ok';
        $warning = false;
        $reqIcon = 'page';
        $queryString = '';
        $returnCode = 0;
        
        $em = $this->getDoctrine()->getEntityManager();
        $logs = $em->getRepository("TechGSfBaseprjBundle:Log")->getLogsByRequestId($request_id);        
        $serializer =  \JMS\Serializer\SerializerBuilder::create()->build();
        
        
        foreach($logs as $k=>$log){

            $logs[$k]['info'] = json_decode($logs[$k]['info'], true);

            if ($log['log_type'] == LogManager::TYPE_SAVE_REQUEST) {
                    
                    $request = $logs[$k]['info']['request'];                                                
                    $request['queryString'] = ($request['queryString'] != '') ? '?'.$request['queryString'] : '';
                    $request['onlyBaseUri'] = str_replace($request['queryString'], '', $request['requestUri']);
                    $request['data'] = UtilityManager::time_ago(\DateTime::createFromFormat('Y-m-d H:i:s', $log['log_date']));
                    
                if (array_key_exists('response', $logs[$k]['info'])) {
                    
                    $response = json_decode($logs[$k]['info']['response'], true);
                    $returnCode = $response['status_code'];
                }     
                    
                unset($logs[$k]);    
            } else {
                
               $logs[$k]['gPanel']['log_class'] = LogManager::$logLevels[$logs[$k]['log_level']]['label'];
               $logs[$k]['gPanel']['title'] = LogManager::$logTypes[$logs[$k]['log_type']]['title'];
               $logs[$k]['gPanel']['icon_class'] = LogManager::$logTypes[$logs[$k]['log_type']]['label'];
                
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
                        if ($request['warning']) {
                            $reqStatus = 'warning';
                            $reqIcon = 'page_white_error';    
                        }
                        if ($request['error']) {
                            $reqStatus = 'error';    
                            $reqIcon = 'http_status_server_error';    
                        }
                        break; 
        }
       
        /*
        echo "<pre>";
        print_r($request);
        echo "</pre>";
        */
        
        return $this->render('TechGSfBaseprjBundle:Component:render_request.html.twig', array('request' => $request,
                                                                                              'response' => $response,
                                                                                              'logs' => $logs,
                                                                                              'reqStatus' => $reqStatus,
                                                                                              'reqIcon' => $reqIcon,
                                                                                                ));
    }       



    public function getMap($geoInfo, $sessione)
    {
        $map = $this->get('ivory_google_map.map');

        if (!(is_array($geoInfo) && array_key_exists('latitude', $geoInfo))) return null;
        
        
        // Configure your map options
        $map->setPrefixJavascriptVariable('map_'.$sessione->getId().'_');
        $map->setHtmlContainerId('map_canvas_'.$sessione->getId());

        $map->setAsync(false);

        $map->setAutoZoom(false);

        $map->setCenter($geoInfo['latitude'], $geoInfo['longitude'], true);
        $map->setMapOption('zoom', 4);


        $map->setMapOption('mapTypeId', MapTypeId::ROADMAP);

        $map->setMapOption('disableDefaultUI', true);
        $map->setMapOption('disableDoubleClickZoom', false);

        $map->setStylesheetOptions(array(
            'width' => '75px',
            'height' => '75px'
        ));
        
        
        // MARKER
        
        // Requests the ivory google map marker service
        $marker = $this->get('ivory_google_map.marker');

        // Configure your marker options
        $marker->setPrefixJavascriptVariable('marker_'.$sessione->getId());
        $marker->setPosition($geoInfo['latitude'], $geoInfo['longitude'], true);
        $marker->setAnimation(Animation::DROP);

        $marker->setOptions(array(
            'clickable' => false,
            'flat' => true
        ));        
        
        $map->addMarker($marker);
        
        return $map;        
    }


    
// ********************************************************
// GLOBAL FRAGMENTS
// ********************************************************

    public function navbarAction($route)
    {
        
        return $this->render('TechGSfBaseprjBundle:Component:navbar.html.twig', array('route' => $route));
    }

    public function headerDrawerAction($route)
    {
        return $this->render('TechGSfBaseprjBundle:Component:header_drawer.html.twig', array('route' => $route));
    }

    public function footerAction()
    {
        return $this->render('TechGSfBaseprjBundle:Component:footer.html.twig', array());
    }
}
