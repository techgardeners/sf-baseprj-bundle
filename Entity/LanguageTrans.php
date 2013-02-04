<?php
/*
 * This file is part of the E-menu project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\SfBaseprjBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use TechG\SfBaseprjBundle\Extensions\BaseEntity as BaseEntity;

/**
 * LanguageTrans
 *
 * @ORM\Table(name="language_trans")
 * @ORM\Entity
 */
class LanguageTrans extends BaseEntity
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
     * @ORM\Column(name="name_language", type="string", length=255, nullable=false)
     */
    private $nameLanguage;

    /**
     * @var string
     *
     * @ORM\Column(name="name_country", type="string", length=255, nullable=false)
     */
    private $nameCountry;

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
     * @var \Language
     *
     * @ORM\ManyToOne(targetEntity="Language")
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
