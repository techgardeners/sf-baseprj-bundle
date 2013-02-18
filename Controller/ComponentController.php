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

class ComponentController extends Controller
{

    // DISPLAYS COMMENT POST TIME AS "1 year, 1 week ago" or "5 minutes, 7 seconds ago", etc...
    function time_ago($date,$granularity=2) {
        
        if (!is_object($date)) return '';
        
        $retval = '';
        $now = new \DateTime();        
        $now = strtotime($now->format('m/d/Y H:i:s'));
        $date = strtotime($date->format('m/d/Y H:i:s'));
        $difference = $now - $date;
        $periods = array('decade' => 315360000,
            'year' => 31536000,
            'month' => 2628000,
            'week' => 604800, 
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1);

        foreach ($periods as $key => $value) {
            if ($difference >= $value) {
                $time = floor($difference/$value);
                $difference %= $value;
                $retval .= ($retval ? ' ' : '').$time.' ';
                $retval .= (($time > 1) ? $key.'s' : $key);
                $granularity--;
            }
            if ($granularity == '0') { break; }
        }
        return ($retval != '') ? $retval.' ago' : ' < 1 second ago' ;      
    }    
    
    
    public function renderSessionAction($session_id)
    {
    
        $em = $this->getDoctrine()->getEntityManager();
        $sessione = $em->getRepository("TechGSfBaseprjBundle:LogSession")->find($session_id);        
        $userInfo = $sessione->getInfoUser();
        $userInfo['userInfo'] = json_decode($userInfo['userInfo'], true);
        $geoInfo = $sessione->getInfoGeo();
        
        $startSession = $this->time_ago($sessione->getLogDate());
        $lastActivity = $this->time_ago($sessione->getLastActivity());
        
        $map = $this->getMap($geoInfo, $sessione);

        
        // prendo i log collegati alla sessione
        
        $requests = $em->getRepository("TechGSfBaseprjBundle:Log")->getRequestIdsBySessionId($session_id, 3); 
        
        
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
        $response = null;
        
        $em = $this->getDoctrine()->getEntityManager();
        $logs = $em->getRepository("TechGSfBaseprjBundle:Log")->getLogsByRequestId($request_id);        
        
        foreach($logs as $k=>$log){

            $logs[$k]['info'] = json_decode($logs[$k]['info'], true);

            if ($log['log_type'] == LogManager::TYPE_SAVE_REQUEST) {
                $request = $logs[$k];
                unset($logs[$k]);    
            }   
            if ($log['log_type'] == LogManager::TYPE_SAVE_RESPONSE) {
                $response = $logs[$k];
                unset($logs[$k]);    
            }   
        }
        
        return $this->render('TechGSfBaseprjBundle:Component:render_request.html.twig', array('request' => $request,
                                                                                              'response' => $response,
                                                                                              'logs' => $logs,
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
