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

use TechG\Bundle\SfBaseprjBundle\Entity\Base\BaseSetting as BaseEntity;

/**
 * Settings
 */
class Setting extends BaseEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string     
     */
    private $key;

    /**
     * @var string        
     */
    private $value;

    /**
     * @var $user            
     * 
     */
    private $user;

    public function __construct()
    {
        parent::__construct();
    }       


}
