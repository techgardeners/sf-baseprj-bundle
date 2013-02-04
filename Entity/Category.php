<?php
/*
 * This file is part of the E-menu project
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
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity
 */
class Category extends BaseEntity
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
     * @var integer
     *
     * @ORM\Column(name="element_id", type="bigint", nullable=true)
     */
    private $elementId;

    /**
     * @var integer
     *
     * @ORM\Column(name="element_type", type="smallint", nullable=true)
     */
    private $elementType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = false;

    /**
     * @var \Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $parent;


    public function __construct()
    {
        parent::__construct();
    }       

}
