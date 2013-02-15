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

use TechG\Bundle\SfBaseprjBundle\Entity\Base\BaseLog as BaseEntity;
                  
/**
 * Log
 *
 * @ORM\Table(name="log")
 * @ORM\Entity(repositoryClass="TechG\Bundle\SfBaseprjBundle\Repository\LogRepository")
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
     * @ORM\Column(name="desc_short", type="string", length=255, nullable=true)
     */
    private $descShort;

    /**
     * @var string
     *
     * @ORM\Column(name="desc_long", type="text", nullable=true)
     */
    private $descLong;

    /**
     * @var $user
     *
     * @ORM\Column(name="user_id", type="bigint", nullable=true)
     * 
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="log_level", type="string", length=255, nullable=true)
     */
    private $logLevel;

    /**
     * @var string
     *
     * @ORM\Column(name="log_type", type="string", length=255, nullable=true)
     */
    private $logType;
    
    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="string", length=255, nullable=true)
     */
    private $sessionId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="task_id", type="string", length=255, nullable=true)
     */
    private $taskId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="parent_id", type="bigint", nullable=true)
     */
    private $parentId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="request_id", type="string", length=255, nullable=true)
     */
    private $requestId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="info", type="text", nullable=true)
     */
    private $info;
    
    
    
    
    public function __construct()
    {
        parent::__construct();
    }       


}
