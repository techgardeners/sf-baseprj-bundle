<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Tests\Extensions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use TechG\Bundle\SfBaseprjBundle\Tests\Extensions\MainKernelTest;

class BaseControllerTestCase extends WebTestCase
{
   
    protected $rootUri = '/';
    

    // ******************************************************************************    
    // ******************************************************************************    
    // ******************************************************************************    
    // ******************************************************************************   

    /**
    * Ritorna un crawler già testato
    * 
    * @param mixed $url
    * @param mixed $method
    * @param mixed $client
    * @param mixed $enableKernelTest
    */
    public function getCrawler($url, $method = 'GET', $client = null, $enableKernelTest = true)
    {
        $mainKernelTest = new MainKernelTest();
        
        return $crawler = $mainKernelTest->getCrawler($url, $method, $client, $enableKernelTest);
    }


}
