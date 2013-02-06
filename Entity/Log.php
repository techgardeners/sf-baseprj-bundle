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

use Doctrine\ORM\Mapping as ORM;

use TechG\Bundle\SfBaseprjBundle\Extensions\BaseGeoPosition as BaseEntity;
                  
/**
 * Log
 *
 * @ORM\Table(name="log")
 * @ORM\Entity
 */
class Log extends BaseEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="log_date", type="datetime", nullable=false)
     */
    private $logDate;

    /**
     * @var string
     *
     * @ORM\Column(name="desc_short", type="string", length=255, nullable=false)
     */
    private $descShort;

    /**
     * @var string
     *
     * @ORM\Column(name="desc_long", type="text", nullable=false)
     */
    private $descLong;

    /**
     * @var $user
     *
     * @ORM\Column(name="user_id", type="bigint", nullable=false)
     * 
     */
    private $user;

    /**
     * @var \LogLevel
     *
     * @ORM\ManyToOne(targetEntity="LogLevel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="log_level_id", referencedColumnName="id")
     * })
     */
    private $logLevel;

    /**
     * @var \LogType
     *
     * @ORM\ManyToOne(targetEntity="LogType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="log_type_id", referencedColumnName="id")
     * })
     */
    private $logType;
    
    public function __construct()
    {
        parent::__construct();
    }       


}