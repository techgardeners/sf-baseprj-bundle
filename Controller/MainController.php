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

class MainController extends Controller
{
    public function infoAction()
    {
        
        // instanzio il kernel principale
        $tgKernel = $this->get("techg.kernel");
        
        $userInfo = $tgKernel;
        
        return $this->render('TechGSfBaseprjBundle:Main:info.html.twig', array());
    }

    public function errorAction()
    {
        
        return $this->render('TechGSfBaseprjBundle:Main:error.html.twig', array());
    }
}
