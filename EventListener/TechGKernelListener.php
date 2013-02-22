<?php
/*
 * This file is part of the SfBaseprjBundle project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace TechG\Bundle\SfBaseprjBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Session;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;

class TechGKernelListener implements EventSubscriberInterface
{
    
    protected $mainKernel;    
    
    static public function getSubscribedEvents()
    {
        return array(
            'kernel.request' => array(
                array('onRequestEvent', 0),
            ),
            'kernel.controller' => array(
                array('onKernelController', 0),
            ),
            'kernel.exception' => array(
                array('onKernelException', 0),
            ),
            'kernel.view' => array(
                array('onKernelView', 0),
            ),
            'kernel.response' => array(
                array('onKernelResponse', 0),
            ),
            'kernel.terminate' => array(
                array('onKernelTerminate', 0),
            ),
        );
    }

    public function __construct(MainKernel $mainKernel)
    {
        $this->mainKernel = $mainKernel;
    }

    public function onPreRequestEvent(GetResponseEvent $event)
    {
               
        if (self::skipOtherRequest($event)) return true;
        
        // Nelle richieste secondarie devo inizializzarlo uguale ma solo alcune cose
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            $this->mainKernel->initSubRequest($event);
            return true;
        }

        $this->mainKernel->init($event);
  
    }
  
    public function onRequestEvent(GetResponseEvent $event)
    {
        
        if (MainKernel::skipOtherRequest($event)) return true;
        
        // Nelle richieste secondarie devo inizializzarlo uguale ma solo alcune cose (tipo il locale per esempio)
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            $this->mainKernel->initSubRequest($event);
            return true;
        }
        
        $this->mainKernel->onRequest($event);  
    }
  
    public function onKernelController(FilterControllerEvent $event)
    {
        
        if (MainKernel::skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;
       
        
        $this->mainKernel->onController($event);

    }      

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        
        if (MainKernel::skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;
           
        $this->mainKernel->onException($event);
        
    }  

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {

        if (MainKernel::skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;       
        
        $this->mainKernel->onView($event);

    }    
    
    public function onKernelResponse(FilterResponseEvent $event)
    {

        if (MainKernel::skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;       
        
        $this->mainKernel->onResponse($event);

    }
    
    public function onKernelTerminate(PostResponseEvent $event)
    {

        if (!$this->mainKernel->isInit()) return;       
        
        $this->mainKernel->onTerminate($event);

    }
    
    
}
