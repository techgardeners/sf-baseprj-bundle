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
        
        $gpanelManager = $this->get('techg.gpanel');
        
        if (!$gpanelManager->isEnabled()) {
            return $this->errorAction();
        }

        if ($gpanelManager->isSecured() && !(is_object($tgKernel->getUser()) && in_array($gpanelManager->getAccessRole(),$tgKernel->getUser()->getRoles()))) {
            return $this->errorAction();
        }
        
        $session = $em->getRepository("TechGSfBaseprjBundle:LogSession")->getActiveSession();
        
        return $this->render('TechGSfBaseprjBundle:GPanel:live.html.twig', array('sessioni' => $session));
    }

    public function errorAction()
    {
        
        return $this->render('TechGSfBaseprjBundle:GPanel:error.html.twig', array());
    }
}
