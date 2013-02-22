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
 * @ORM\Table(name="log", indexes={@ORM\Index(name="search_idx_1", columns={"cookie_id"}),
 *                                 @ORM\Index(name="search_idx_2", columns={"session_id"}),  
 *                                 @ORM\Index(name="search_idx_3", columns={"task_id"}),  
 *                                 @ORM\Index(name="search_idx_4", columns={"request_id"}),  
 *                                 @ORM\Index(name="search_idx_5", columns={"parent_id"}),  
 *                                 @ORM\Index(name="search_idx_6", columns={"cookie_id", "request_id"})  
 * })
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
     * @ORM\Column(name="cookie_id", type="string", length=255, nullable=true)
     */
    private $cookieId;
    
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
        
        $this->setLogDate(new \DateTime());
        
    }       



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set logDate
     *
     * @param \DateTime $logDate
     * @return Log
     */
    public function setLogDate($logDate)
    {
        $this->logDate = $logDate;
    
        return $this;
    }

    /**
     * Get logDate
     *
     * @return \DateTime 
     */
    public function getLogDate()
    {
        return $this->logDate;
    }

    /**
     * Set descShort
     *
     * @param string $descShort
     * @return Log
     */
    public function setDescShort($descShort)
    {
        $this->descShort = $descShort;
    
        return $this;
    }

    /**
     * Get descShort
     *
     * @return string 
     */
    public function getDescShort()
    {
        return $this->descShort;
    }

    /**
     * Set descLong
     *
     * @param string $descLong
     * @return Log
     */
    public function setDescLong($descLong)
    {
        $this->descLong = $descLong;
    
        return $this;
    }

    /**
     * Get descLong
     *
     * @return string 
     */
    public function getDescLong()
    {
        return $this->descLong;
    }

    /**
     * Set user
     *
     * @param integer $user
     * @return Log
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return integer 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set logLevel
     *
     * @param string $logLevel
     * @return Log
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    
        return $this;
    }

    /**
     * Get logLevel
     *
     * @return string 
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * Set logType
     *
     * @param string $logType
     * @return Log
     */
    public function setLogType($logType)
    {
        $this->logType = $logType;
    
        return $this;
    }

    /**
     * Get logType
     *
     * @return string 
     */
    public function getLogType()
    {
        return $this->logType;
    }

    /**
     * Set sessionId
     *
     * @param string $sessionId
     * @return Log
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    
        return $this;
    }

    /**
     * Get sessionId
     *
     * @return string 
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set cookieId
     *
     * @param string $cookieId
     * @return Log
     */
    public function setCookieId($cookieId)
    {
        $this->cookieId = $cookieId;
    
        return $this;
    }

    /**
     * Get cookieId
     *
     * @return string 
     */
    public function getCookieId()
    {
        return $this->cookieId;
    }

    /**
     * Set taskId
     *
     * @param string $taskId
     * @return Log
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
    
        return $this;
    }

    /**
     * Get taskId
     *
     * @return string 
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     * @return Log
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    
        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer 
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set requestId
     *
     * @param string $requestId
     * @return Log
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    
        return $this;
    }

    /**
     * Get requestId
     *
     * @return string 
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Set info
     *
     * @param string $info
     * @return Log
     */
    public function setInfo($info)
    {
        $this->info = json_encode($info);
    
        return $this;
    }

    /**
     * Get info
     *
     * @return string 
     */
    public function getInfo($array = true)
    {
        return json_decode($this->info, $array);
    }
}