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
use TechG\Bundle\SfBaseprjBundle\Extensions\GPanel\GPanelManager;

class GPanelController extends Controller
{
    public function indexAction()
    {        
        // instanzio il kernel principale
        $tgKernel = $this->get("techg.kernel");
        $gpanelManager = $this->get('techg.gpanel');

        if (!$gpanelManager->isEnabled()) {
            return $this->errorAction();
        }

        if ($gpanelManager->isSecured() && !(is_object($tgKernel->getUser()) && in_array($gpanelManager->getAccessRole(),$tgKernel->getUser()->getRoles()))) {
            return $this->errorAction();
        }
        
        return $this->render('TechGSfBaseprjBundle:GPanel:index.html.twig', array());
    }

    public function infoAction()
    {
        
        // instanzio il kernel principale
        $tgKernel = $this->get("techg.kernel");
        
        return $this->render('TechGSfBaseprjBundle:GPanel:info.html.twig', array());
    }

    public function liveAction()
    {
        
        // instanzio il kernel principale
        $em = $this->getDoctrine()->getEntityManager();
        $tgKernel = $this->get("techg.kernel");
        $gPanelManager = $this->get("techg.gpanel");        
        
        
        $sessionIds = $em->getRepository("TechGSfBaseprjBundle:LogSession")->getActiveSession(900);
        
        $sessions = array();
        foreach ($sessionIds as $item){
            $session_id = $item['id'];
            
            $sessions[$session_id] =  $gPanelManager->renderSession($session_id, false);
            $requests = $em->getRepository("TechGSfBaseprjBundle:Log")
                              ->getRequestIdsBySessionId($session_id, 15); 
            
            $webSessions = $em->getRepository("TechGSfBaseprjBundle:Log")
                                 ->getWebSessionBySessionId($session_id, 3); 
            
            $sessions[$session_id]['requests'] = array();
            foreach ($requests as $reqItem){
                $req_id = $reqItem['id'];
                
                $sessions[$session_id]['requests'][$req_id] = $gPanelManager->renderRequest($req_id);
            }            
            
            $sessions[$session_id]['ws'] = array();            
            foreach ($webSessions as $ws){
                $ws_id = $ws['id'];
                
                $sessions[$session_id]['ws'][$ws_id] = $gPanelManager->renderWebSession($ws);
            }            
            
        }        
        
        
        return $this->render('TechGSfBaseprjBundle:GPanel:live.html.twig', array('sessions' => $sessions));
    }

    public function sessionsAction()
    {
        
        // instanzio il kernel principale
        $em = $this->getDoctrine()->getEntityManager();
        $tgKernel = $this->get("techg.kernel");
        $gPanelManager = $this->get("techg.gpanel");
        
        $sessionIds = $em->getRepository("TechGSfBaseprjBundle:LogSession")->getSessions(20);
        
        $sessions = array();
        foreach ($sessionIds as $item){
            $sessions[] =  $gPanelManager->renderSession($item['id'], false);
        }
        
        
        return $this->render('TechGSfBaseprjBundle:GPanel:sessions.html.twig', array('sessions' => $sessions));
    }

    public function errorAction()
    {
        
        return $this->render('TechGSfBaseprjBundle:GPanel:error.html.twig', array());
    } 
    
    
// *******************************************************************************************
}
