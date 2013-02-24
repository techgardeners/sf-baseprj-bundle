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

use TechG\Bundle\SfBaseprjBundle\Entity\Base\BaseBlackWhiteList as BaseEntity;
                  
/**
 * Log

 */
class BlackWhiteList extends BaseEntity
{
    /**
     * @var integer

     */
    private $id;

    /**
     * @var string

     */
    private $data;

    /**
     * @var string

     */
    private $type;

    /**
     * @var string

     */
    private $list;

    /**
     * @var string

     */
    private $origin;

    /**
     * @var boolean

     */
    private $enabled = false;
    
    public function __construct()
    {
        parent::__construct();
    }       


}
