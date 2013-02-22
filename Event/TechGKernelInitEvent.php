<?php
/*
 * This file is part of the SfBaseprjBundle project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace TechG\Bundle\SfBaseprjBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;

class TechGKernelInitEvent extends Event
{
    const onKernelInit = 'techg.kernel.init';    
    
    protected $tgKernel;

    public function __construct(MainKernel $tgKernel)
    {
        $this->tgKernel = $tgKernel;
    }

    public function getTgKernel()
    {
        return $this->tgKernel;
    }    
}