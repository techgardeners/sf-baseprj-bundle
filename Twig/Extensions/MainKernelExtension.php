<?php
/*
 * This file is part of the App Framework project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace TechG\AppFw\BaseBundle\Twig\Extensions;

use TechG\AppFw\BaseBundle\Extensions\MainKernel;

class MainKernelExtension extends \Twig_Extension
{
    protected $mainKernel;

    function __construct(MainKernel $mainKernel) {
        $this->mainKernel = $mainKernel;
    }

    public function getGlobals() {
        return array(
            'mainKernel' => $this->mainKernel,
        );
    }

    public function getName()
    {
        return 'mainKernel';
    }

}