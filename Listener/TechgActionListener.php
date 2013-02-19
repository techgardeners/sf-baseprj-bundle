<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Listener;

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

class TechgActionListener
{
    protected $mainKernel;

    public function __construct(MainKernel $mainKernel)
    {
        $this->mainKernel = $mainKernel;
    }

    private function skipSubRequest($event)
    {              
        $request = $event->getRequest();
        
        // Viene richiamato SOLO nelle richieste principali
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType() || $this->skipOtherRequest($request)) {
            return true;
        }
        
        return false;         
    }
    
    private function skipOtherRequest($request)
    {              
        
        // Viene richiamato SOLO nelle richieste principali
        if (preg_match('/^\/_(wdt|assetic|profiler|configurator)/', $request->getRequestUri())) {
            return true;
        }
        
        return false;         
    }
    
  
    public function onInitEvent(GetResponseEvent $event)
    {
        
        $request = $event->getRequest();
        
        if ($this->skipOtherRequest($request)) return true;
        
        // Nelle richieste secondarie devo inizializzarlo uguale ma solo alcune cose
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            $this->mainKernel->initSubRequest($event);
            return true;
        }
        
        
        $this->mainKernel->init($event);
        
          
    }
  
    public function onRequestEvent(GetResponseEvent $event)
    {
        
        if ($this->skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;
        
        // Richiamo l'init del kernel per inniettargli la request
        // (per problemi di scope dei servizi http://symfony.com/it/doc/current/cookbook/service_container/scopes.html)
        $this->mainKernel->onRequest($event);  
    }
  
    public function onKernelController(FilterControllerEvent $event)
    {
        
        if ($this->skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;
       
        
        $this->mainKernel->onController($event);

    }      

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        
        if ($this->skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;

        $exception = $event->getException();            
        $this->mainKernel->onException($exception);
        
    }  

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {

        if ($this->skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;       
        
        $this->mainKernel->onView($event);

    }    
    
    public function onKernelResponse(FilterResponseEvent $event)
    {

        if ($this->skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;       
        
        $this->mainKernel->onResponse($event);

    }
    
    public function onKernelTerminate(PostResponseEvent $event)
    {

        if (!$this->mainKernel->isInit()) return;       
        
        $this->mainKernel->onTerminate($event);

    }         
  
}