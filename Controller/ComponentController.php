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
        
        $map = $this->get('ivory_google_map.map');
        
        return $this->render('TechGSfBaseprjBundle:Component:render_session.html.twig', array('sessione' => $sessione,
                                                                                              'start' => $startSession,
                                                                                              'lastActivity' => $lastActivity,
                                                                                              'userInfo' => $userInfo,
                                                                                              'geoInfo' => $geoInfo,
                                                                                              'map' => $map,
                                                                                                ));
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
