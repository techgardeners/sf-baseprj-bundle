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
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use TechG\AppFw\BaseBundle\Extensions\BaseEntity;

class BaseSetting extends BaseEntity
{
    
    public function __construct()
    {
        parent::__construct();
    }   

// **********************************************************************************************************    
//  STATIC METHOD
// **********************************************************************************************************    
    

    /**
    * Controlla se l'entity è del tipo giusto
    * 
    * @param mixed $item
    */
    public static function isValidEntity($item)
    {
        return (\TechG\AppFw\BaseBundle\Extensions\BaseEntity::isValidEntity($item) && $item instanceof \TechG\AppFw\BaseBundle\Entity\Setting);
    } 
 
}

