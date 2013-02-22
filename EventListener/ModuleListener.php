<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;


use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\Setting\SettingManager;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use TechG\Bundle\SfBaseprjBundle\Event\TechGKernelInitEvent;

class ModuleListener implements EventSubscriberInterface
{            
    
    protected $moduloManager;
         
    public function __construct($moduleManager)
    { 
        $this->moduloManager = $moduleManager;         
    }    
     
       
// ********************************************************************************************************       
// GESTORI EVENTI       
// ********************************************************************************************************         
    static public function getSubscribedEvents()
    {
        return array(
            TechGKernelInitEvent::onKernelInit => array(
                array('onTechGKernelInitEvent', 0)
            ),
            'kernel.request' => array(
                array('onKernelRequestEvent', 0),
            ),
            'kernel.controller' => array(
                array('onKernelControllerEvent', 0),
            ),
            'kernel.exception' => array(
                array('onKernelExceptionEvent', 0),
            ),
            'kernel.view' => array(
                array('onKernelViewEvent', 0),
            ),
            'kernel.response' => array(
                array('onKernelResponseEvent', 0),
            ),
            'kernel.terminate' => array(
                array('onKernelTerminateEvent', 0),
            ),

        );
    }
    
    
//**************************************************************************************    
// TECHG KERNEL EVENTS HANDLER   
//**************************************************************************************    

    public function onTechGKernelInitEvent(TechGKernelInitEvent $event)
    {                 
        if (!$this->moduloManager->isEnabled()) return;

        $this->moduloManager->setTgKernel($event->getTgKernel());
        $this->moduloManager->onTechGKernelInit($event);   
    }
    
//**************************************************************************************    
// SYMFONY EVENTS HANDLER   
//**************************************************************************************    
    
    public function onKernelRequestEvent(GetResponseEvent $event)
    {   
        if (MainKernel::skipSubRequest($event) || 
            !$this->moduloManager->isEnabled()) return;
        
        $this->moduloManager->onKernelRequest($event); 
    }    
  
    public function onKernelControllerEvent(FilterControllerEvent $event)
    {
        if (MainKernel::skipSubRequest($event) || 
            !$this->moduloManager->isEnabled()) return;
                    
        $this->moduloManager->onKernelController($event);         
    }      

    public function onKernelExceptionEvent(GetResponseForExceptionEvent $event)
    {
        if (!$this->moduloManager->isEnabled()) return;
                    
        $this->moduloManager->onKernelException($event);           
    }  
    
    public function onKernelViewEvent(GetResponseForControllerResultEvent $event)
    {
        if (MainKernel::skipSubRequest($event) || 
            !$this->moduloManager->isEnabled()) return;
                    
        $this->moduloManager->onKernelView($event);         
    }    
    
    public function onKernelResponseEvent(FilterResponseEvent $event)
    {
        if (MainKernel::skipSubRequest($event) || 
            !$this->moduloManager->isEnabled()) return;
                    
        $this->moduloManager->onKernelResponse($event);         
    }
    
    public function onKernelTerminateEvent(PostResponseEvent $event)
    {
        if (MainKernel::skipOtherRequest($event) || !$this->moduloManager->isEnabled()) return;
                    
        $this->moduloManager->onKernelTerminate($event);
    }

}