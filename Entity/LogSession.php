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
 * @ORM\Table(name="log_session")
 * @ORM\Entity(repositoryClass="TechG\Bundle\SfBaseprjBundle\Repository\LogSessionRepository")
 */
class LogSession extends BaseEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="string", nullable=false)
     * @ORM\Id
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
     * @ORM\Column(name="info_user", type="text", nullable=true)
     */
    private $infoUser;

    /**
     * @var string
     *
     * @ORM\Column(name="info_geo", type="text", nullable=true)
     */
    private $infoGeo;

    /**
     * @var string
     *
     * @ORM\Column(name="info", type="text", nullable=true)
     */
    private $info;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_activity", type="datetime", nullable=true)
     */
    private $lastActivity;

    
    public function __construct()
    {
        parent::__construct();
        
        $this->setLogDate(new \DateTime());        
        $this->lastActivity(new \DateTime());        
    }       



    /**
     * Set id
     *
     * @param string $id
     * @return LogSession
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set logDate
     *
     * @param \DateTime $logDate
     * @return LogSession
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
     * Set infoUser
     *
     * @param string $infoUser
     * @return LogSession
     */
    public function setInfoUser($infoUser)
    {
        $this->infoUser = json_encode($infoUser);
    
        return $this;
    }

    /**
     * Get infoUser
     *
     * @return string 
     */
    public function getInfoUser($array = true)
    {
        return json_decode($this->infoUser, $array);
    }

    /**
     * Set infoGeo
     *
     * @param string $infoGeo
     * @return LogSession
     */
    public function setInfoGeo($infoGeo, $encode = true)
    {
        $this->infoGeo = ($encode) ? json_encode($infoGeo) : $infoGeo;
    
        return $this;
    }

    /**
     * Get infoGeo
     *
     * @return string 
     */
    public function getInfoGeo($array = true)
    {
        return json_decode($this->infoGeo, $array);
    }

    /**
     * Set info
     *
     * @param string $info
     * @return LogSession
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

    /**
     * Set lastActivity
     *
     * @param \DateTime $lastActivity
     * @return LogSession
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;
    
        return $this;
    }

    /**
     * Get lastActivity
     *
     * @return \DateTime 
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }
}