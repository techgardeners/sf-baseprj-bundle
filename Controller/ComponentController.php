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
use Ivory\GoogleMap\MapTypeId;
use Ivory\GoogleMap\Overlays\Animation;

use TechG\Bundle\SfBaseprjBundle\Extensions\Log\LogManager;
use TechG\Bundle\SfBaseprjBundle\Extensions\UtilityManager;

class ComponentController extends Controller
{  
    
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
