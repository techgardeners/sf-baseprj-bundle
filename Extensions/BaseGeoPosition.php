<?php
/*
 * This file is part of the Base Framework project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use TechG\Bundle\SfBaseprjBundle\Extensions\BaseEntity;

class BaseGeoPosition extends BaseEntity
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
        return (\TechG\Bundle\SfBaseprjBundle\Extensions\BaseEntity::isValidEntity($item) && $item instanceof \TechG\Bundle\SfBaseprjBundle\Entity\GeoPosition);
    } 
 
}

