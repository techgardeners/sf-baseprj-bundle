<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use TechG\Bundle\SfBaseprjBundle\Tests\Extensions\MainKernelTest;
use TechG\Bundle\SfBaseprjBundle\Tests\Extensions\BaseControllerTestCase as BaseTestCaseClass;

class MainControllerTest extends BaseTestCaseClass
{
       
    /**
    * testa che il MainKernel si inizializzi bene
    */
    public function testInit()
    {        
        // Effettuo la chiamata
        $crawler = $this->getCrawler($this->rootUri);       

        
    }
}
