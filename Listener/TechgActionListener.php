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
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType() || preg_match('/^_(wdt|assetic|profiler|configurator)/', $request->get('_route'))) {
            return true;
        }
        
        return false;         
    }
    
  
    public function onRequestEvent(GetResponseEvent $event)
    {


        if ($this->skipSubRequest($event)) return;

        $request = $event->getRequest();
        // Richiamo l'init del kernel per inniettargli la request
        // (per problemi di scope dei servizi http://symfony.com/it/doc/current/cookbook/service_container/scopes.html)
        $this->mainKernel->init($request);  
    }
  
    public function onKernelController(FilterControllerEvent $event)
    {
        
        if ($this->skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;
       
        
        $this->mainKernel->elaborateController($event);

    }      

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        
        if ($this->skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;

        $exception = $event->getException();            
        $this->mainKernel->elaborateException($exception);
        
    }  

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {

        if ($this->skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;       
        
        $this->mainKernel->elaborateView($event);

    }    
    
    public function onKernelResponse(FilterResponseEvent $event)
    {

        if ($this->skipSubRequest($event) || 
            !$this->mainKernel->isInit()) return;       
        
        $this->mainKernel->elaborateResponse($event);

    }
    
    public function onKernelTerminate(PostResponseEvent $event)
    {

        if (!$this->mainKernel->isInit()) return;       
        
        $this->mainKernel->elaborateTerminate($event);

    }         
  
}