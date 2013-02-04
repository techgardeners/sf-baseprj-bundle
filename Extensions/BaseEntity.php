<?php
/*
 * This file is part of the App Framework project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\AppFw\BaseBundle\Extensions;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class BaseEntity
{
               
    public function __construct()
    {        
    }
    
// ***********************************************************************************************************    
// ***********************************************************************************************************    
// ***********************************************************************************************************    
// ***********************************************************************************************************   
  
    /**
    * Ritorna se l'entità è valida
    * 
    * @param mixed $item
    */
    public static function isValidEntity($item)
    {
        return ($item->getId() > 0);
    }    
    
}
