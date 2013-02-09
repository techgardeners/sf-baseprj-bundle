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
    protected $client = null;
    

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
    public function getCrawler($url, $method = 'GET', $enableKernelTest = true)
    {
        $mainKernelTest = new MainKernelTest();
        
        return $crawler = $mainKernelTest->getCrawler($url, $method, $this->getClient(), $enableKernelTest);
    }

    /**
    * Ritorna il kernel techG
    *     
    */
    public function getKernel()
    {
        return static::$kernel->getContainer()->get(MainKernelTest::KERNEL_NAME);
    }
    
    /**
    * Ritorna un client
    *     
    */
    public function getClient($renew = false)
    {
        $this->client = (!is_null($this->client) && !$renew) ? $this->client : static::createClient(array(), array('HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
                                                            ));
        return $this->client;
    } 
    
    
    public function checkIfRedirect($url, $method = 'GET')
    {        
        $client = $this->getClient();
        
        // Effettuo la chiamata
        $crawler = $client->request($method,$url);
        
        // deve essere un redirect alla login
        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertTrue($client->getResponse()->isRedirect(), "L'url: $url non è un redirect");
        
    }  
    
     

}