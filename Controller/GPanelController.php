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
        
        $session = $em->getRepository("TechGSfBaseprjBundle:LogSession")->getActiveSession();
        
        return $this->render('TechGSfBaseprjBundle:GPanel:live.html.twig', array('sessioni' => $session));
    }

    public function errorAction()
    {
        
        return $this->render('TechGSfBaseprjBundle:GPanel:error.html.twig', array());
    }
}
