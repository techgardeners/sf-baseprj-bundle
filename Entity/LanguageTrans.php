<?php
/*
 * This file is part of the SfBaseprjBundle project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Entity;


use TechG\Bundle\SfBaseprjBundle\Entity\Base\BaseEntity as BaseEntity;

/**
 * LanguageTrans
 */
class LanguageTrans extends BaseEntity
{
    /**
     * @var integer  
     */
    private $id;

    /**
     * @var string      
     */
    private $nameLanguage;

    /**
     * @var string    
     */
    private $nameCountry;

    /**
     * @var string       
     */
    private $descShort;

    /**
     * @var string      
     */
    private $descLong;

    /**
     * @var \Language   
     */
    private $item;

    /**
     * @var \Language  
     */
    private $language;


    public function __construct()
    {
        parent::__construct();
    }       

}
