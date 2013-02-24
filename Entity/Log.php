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


use TechG\Bundle\SfBaseprjBundle\Entity\Base\BaseLog as BaseEntity;
                  
/**
 * Log      
 */
class Log extends BaseEntity
{
    /**
     * @var integer   
     */
    private $id;

    /**
     * @var \DateTime     
     */
    private $logDate;

    /**
     * @var string   
     */
    private $descShort;

    /**
     * @var string    
     */
    private $descLong;

    /**
     * @var $user   
     * 
     */
    private $user;

    /**
     * @var string   
     */
    private $logLevel;

    /**
     * @var string   
     */
    private $logType;
    
    /**
     * @var string       
     */
    private $cookieId;
    
    /**
     * @var string    
     */
    private $sessionId;
    
    /**
     * @var string   
     */
    private $taskId;
    
    /**
     * @var string     
     */
    private $parentId;
    
    /**
     * @var string     
     */
    private $requestId;
    
    /**
     * @var string    
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