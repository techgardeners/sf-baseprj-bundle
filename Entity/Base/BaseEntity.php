<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Entity\Base;

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
    
    /**
     * Format a string data.
     *
     * @param string $str A string.
     *
     * @return string
     */
    public static function formatString($str)
    {
        if (extension_loaded('mbstring')) {
            $str = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
        } else {
            $str = $this->lowerize($str);
            $str = ucwords($str);
        }

        $str = str_replace('-', '- ', $str);
        $str = str_replace('- ', '-', $str);

        return $str;
    }

    /**
     * Make a string lowercase.
     *
     * @param string $str A string.
     *
     * @return string
     */
    public static function lowerize($str)
    {
        return extension_loaded('mbstring') ? mb_strtolower($str, 'UTF-8') : strtolower($str);
    }

    /**
     * Make a string uppercase.
     *
     * @param string $str A string.
     *
     * @return string
     */
    public static function upperize($str)
    {
        return extension_loaded('mbstring') ? mb_strtoupper($str, 'UTF-8') : strtoupper($str);
    }       
    
}
