<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions;

use Doctrine\ORM\EntityRepository;

class BaseEntityRepository extends EntityRepository
{
    
    
    
    
// ***************** DA VERIFICARE ***********************    
// ***************** DA VERIFICARE ***********************    
// ***************** DA VERIFICARE ***********************    
    
    
    /**
    * Ritorna una porzione di sql per la generazione del limit
    * 
    * @param mixed $nresult
    * @param mixed $firstRecord
    */
    
    public function addLimit($nresult, $firstRecord = 0)
    {
        $sql = "
            LIMIT $firstRecord , $nresult 
        ";
        
        return $sql;
    }
    
    
    public function elaborateResult($result, $limit, $defaultValue, $returnObj, $returnOneElementAsArray, $repository = null, $idName = 'id')
    {
        
        $repository = ($repository == null) ? $this : $repository;
        
        
        if (!(count($result) > 0)) {
            
            return $defaultValue;    
        }        

        if (!$returnOneElementAsArray && ($limit == 1 || count($result) == 1)) {
            
            return ($returnObj) ? $repository->find($result[0][$idName]) : $result[0];        
        }               

        if ($returnObj) {
            
            foreach ($result as &$item) {
                $item = $repository->find($item[$idName]);    
            }    
        }
        
        return $result;
                
    }    
    
    
    
}
