<?php
/*
 * This file is part of the App Framework project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\AppFw\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use TechG\AppFw\BaseBundle\Extensions\BaseEntity as BaseEntity;

/**
 * LogType
 *
 * @ORM\Table(name="log_type")
 * @ORM\Entity
 */
class LogType extends BaseEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;


    public function __construct()
    {
        parent::__construct();
    }       


}
