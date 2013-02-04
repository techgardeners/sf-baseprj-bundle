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
 * AttachmentTrans
 *
 * @ORM\Table(name="attachment_trans")
 * @ORM\Entity
 */
class AttachmentTrans extends BaseEntity
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

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
     * @var string
     *
     * @ORM\Column(name="html_alt", type="string", length=255, nullable=true)
     */
    private $htmlAlt;

    /**
     * @var string
     *
     * @ORM\Column(name="html_title", type="string", length=255, nullable=true)
     */
    private $htmlTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="js_code", type="text", nullable=true)
     */
    private $jsCode;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = false;

    /**
     * @var \Attachment
     *
     * @ORM\ManyToOne(targetEntity="Attachment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     * })
     */
    private $item;

    /**
     * @var \Language
     *
     * @ORM\ManyToOne(targetEntity="Language")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="language_id", referencedColumnName="id")
     * })
     */
    private $language;


    public function __construct()
    {
        parent::__construct();
    }       

}
