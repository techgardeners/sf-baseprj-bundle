<?php
/*
 * This file is part of the App Framework project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\AppFw\BaseBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Session;

use TechG\AppFw\BaseBundle\Extensions\MainKernel;

class AppOnRequestListener
{
    protected $mainKernel;

    public function __construct(MainKernel $mainKernel)
    {
        $this->mainKernel = $mainKernel;
    }

  
  public function onRequestEvent(Event $event)
  {

    $request = $event->getRequest();
          
    // Viene richiamato SOLO nelle richieste principali
    if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType() || preg_match('/^_(wdt|assetic|profiler|configurator)/', $request->get('_route'))) {
        return;
    }
    
    // Richiamo l'init del kernel per inniettargli la request
    // (per problemi di scope dei servizi http://symfony.com/it/doc/current/cookbook/service_container/scopes.html)
    $this->mainKernel->init($request);  
  }
}