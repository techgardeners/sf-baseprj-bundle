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
    }       


}
