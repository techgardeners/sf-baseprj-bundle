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

class MainKernelTest extends WebTestCase
{
    const KERNEL_NAME = 'techg.kernel';
    
    private $rootUri = '/';
    
    /**
    * testa che il MainKernel si inizializzi bene
    */
    public function testInit()
    {        
        // Effettuo la chiamata
        //$crawler = $this->getCrawler($this->rootUri);       
    }    
    
// ******************************************************************************    
// ******************************************************************************    
// ******************************************************************************    
// ******************************************************************************    


    public function getCrawler($url, $method = 'GET', $client = null, $enableKernelTest = true)
    {
        $client = (is_null($client)) ? $this->getClient() : $client;
        
        // Effettuo i test pre inizializzazione
        if ($enableKernelTest){ $this->checkPreInit(); }

        
        // Effettuo la chiamata
        $crawler = $client->request($method, $url); 
        if($client->getResponse()->isRedirect()){
            $crawler = $client->followRedirect(true);    
        }
                
        // Effettuo i test post inizializzazione
        if ($enableKernelTest){ $this->checkPostInit(); }
        
        return $crawler;
    }


    /**
    * Ritorna il kernel techG
    *     
    */
    public function getKernel()
    {
        return static::$kernel->getContainer()->get(self::KERNEL_NAME);
    }
    
    /**
    * Ritorna un client
    *     
    */
    public function getClient()
    {
        return static::createClient(array(), array(
            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
        ));
    }
    
    
    /**
    * Effettua i test pre inizializzazione

    */
    public function checkPreInit()
    {
        $tgKernel = $this->getKernel();
        
        // Controllo che il kernel esista
        $this->assertNotNull($tgKernel, 'Il kernel non sembra essere nel container');
        
        // Controllo che i moduli siano correttamente inzializzati
        $this->checkInitModuli($tgKernel::MODULE_NAME_GEO, $tgKernel->geocoder);
        $this->checkInitModuli($tgKernel::MODULE_NAME_MOBILE_DECT, $tgKernel->mobileDetector);
        
        // Prima della reguest non è inizializzato
        $this->assertFalse($tgKernel->isInit(), 'Il kernel non deve essere inzializzato prima della richiesta');         
    }
    
    /**
    * Effettua i test post inizializzazione

    */
    public function checkPostInit()
    {
        $tgKernel = $this->getKernel();
        
        // Dopo la request, si deve essere inizializzato
        $this->assertTrue($tgKernel->isInit(), 'Il kernel deve essere inzializzato dopo una richiesta');
        
        $this->assertNotNull($tgKernel->uri, "La proprietà uri non sembra correttamente inizializzata");
        $this->assertNotNull($tgKernel->host, "La proprietà host non sembra correttamente inizializzata");
        $this->assertNotNull($tgKernel->baseUri, "La proprietà baseUri non sembra correttamente inizializzata");
        $this->assertNotNull($tgKernel->clientIp, "La proprietà clientIp non sembra correttamente inizializzata");
        
        $this->checkBrowserInfo();

        
               
    }
    
    /**
    * Controlla che l'array browserInfo sia correttamente inizializzato
    * 
    */
    public function checkBrowserInfo()
    {
        $tgKernel = $this->getKernel();
        
        $this->assertNotNull($tgKernel->userBrowserInfo, "La proprietà userBrowserInfo non sembra correttamente inizializzata");
        $this->assertTrue(is_array($tgKernel->userBrowserInfo), "La proprietà userBrowserInfo non sembra essere un array");
        $this->assertTrue(array_key_exists('userAgent', $tgKernel->userBrowserInfo), "La proprietà userAgent di userBrowserInfo non sembra correttamente inizializzata");
        $this->assertTrue(array_key_exists('name', $tgKernel->userBrowserInfo), "La proprietà name di userBrowserInfo non sembra correttamente inizializzata");
        $this->assertTrue(array_key_exists('version', $tgKernel->userBrowserInfo), "La proprietà version di userBrowserInfo non sembra correttamente inizializzata");
        $this->assertTrue(array_key_exists('platform', $tgKernel->userBrowserInfo), "La proprietà platform di userBrowserInfo non sembra correttamente inizializzata");
        $this->assertTrue(array_key_exists('pattern', $tgKernel->userBrowserInfo), "La proprietà pattern di userBrowserInfo non sembra correttamente inizializzata");
        
    }
    
    
    /**
    * Testa che i moduli siano inizializzati o meno
    * 
    * @param mixed $name
    * @param mixed $obj
    */
    public function checkInitModuli($name, $obj)
    {
        $tgKernel = $this->getKernel();
        
        if ($tgKernel->isModuleEnabled($name)) {
            $this->assertNotNull($obj, "Il modulo $name è attivato ma non è stato correttamente inizializzato");
        } else {
            $this->assertNull($obj, "Il modulo $name non è attivato ma è stato inizializzato");   
        }          
    }
    
    
}
