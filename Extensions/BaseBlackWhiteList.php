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

use TechG\Bundle\SfBaseprjBundle\Extensions\MainKernel;
use TechG\Bundle\SfBaseprjBundle\Extensions\BaseEntity;

class BaseBlackWhiteList extends BaseEntity
{
    
    const LIST_TYPE_BLACK = 'BLACK';
    const LIST_TYPE_WHITE = 'WHITE';
    const DATA_TYPE_IP = 'IP';
    const DATA_TYPE_HOST = 'HOST';
    const DATA_TYPE_GEO = 'GEO';
    
    public function __construct()
    {
        parent::__construct();
    }   

// **********************************************************************************************************    
//  STATIC METHOD
// **********************************************************************************************************    
    
    
    public static function getDataFromKernel($tgKernel, $type, $options)
    {
        switch ($type) {
            case self::DATA_TYPE_HOST: 
                                                return $tgKernel->host; 
                                                break;
            case self::DATA_TYPE_GEO: 
                                                if (!is_null($tgKernel->userGeoPosition)) {
                                                    $field = $options['geo_field'];
                                                    return (array_key_exists($field, $tgKernel->userGeoPosition)) ? $tgKernel->userGeoPosition[$field] : null; 
                                                }
                                                break;
            case self::DATA_TYPE_IP: 
            default: 
                                                return $tgKernel->clientIp; 
                                                break;
        }
        
        return null;
        
    }
    
    
    public static function isInList($em, $list, $_data, $type)
    {
        return $em->getRepository("TechGSfBaseprjBundle:BlackWhiteList")->isInList($list, $_data, $type);
    }
    
    

    /**
    * Controlla se l'entity è del tipo giusto
    * 
    * @param mixed $item
    */
    public static function isValidEntity($item)
    {
        return (\TechG\Bundle\SfBaseprjBundle\Extensions\BaseEntity::isValidEntity($item) && $item instanceof \TechG\Bundle\SfBaseprjBundle\Entity\BlackWhiteList);
    } 
 
}

