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

use TechG\Bundle\SfBaseprjBundle\EventListener\ModuleListener as BaseListener;

class LogListener extends BaseListener
{            
         
    /**
    * Reagisce all'evento di inizializzazione del kernel
    * 
    * @param TechGKernelInitEvent $event
    */
    public function onTechGKernelInit(TechGKernelInitEvent $event)
    {         
        // Preparo subito il log della request (nel caso poi lo integro)
        $this->loggedRequest = $this->getNewLogRequest();   
        $this->loggedSession = $this->getLoggedSession();            
    }

    public function onKernelRequest(GetResponseEvent $event) 
    {
        if (!$this->requestSaved) {
            // Aggiungo rotta e controller ( e locale corretto)
            $this->updateRequest();
        }       
    }

    public function onKernelResponse(FilterResponseEvent $event) 
    {        
        $this->logResponse($event->getResponse());
    }

    public function onKernelException(GetResponseForExceptionEvent $event) 
    {        
        $this->logException($event->getException());
    }
        
    // Chiude i log
    public function onKernelTerminate(PostResponseEvent $event)
    {

        $this->updateAndPersistSession($this->loggedSession);
        
        $this->flushQueue();
        $this->em->flush();
        
    }    

}