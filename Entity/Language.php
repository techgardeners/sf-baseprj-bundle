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

use TechG\Bundle\SfBaseprjBundle\Entity\Base\BaseEntity as BaseEntity;

/**
 * Language     
 */
class Language extends BaseEntity
{
    /**
     * @var integer

     */
    private $id;

    /**
     * @var string

     */
    private $label;

    /**
     * @var string

     */
    private $iso639;

    /**
     * @var string

     */
    private $iso3166;

    /**
     * @var string

     */
    private $locale;

    /**
     * @var boolean

     */
    private $enabled = false;


    public function __construct()
    {
        parent::__construct();
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
     * Set label
     *
     * @param string $label
     * @return Language
     */
    public function setLabel($label)
    {
        $this->label = $label;
    
        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set iso639
     *
     * @param string $iso639
     * @return Language
     */
    public function setIso639($iso639)
    {
        $this->iso639 = $iso639;
    
        return $this;
    }

    /**
     * Get iso639
     *
     * @return string 
     */
    public function getIso639()
    {
        return $this->iso639;
    }

    /**
     * Set iso3166
     *
     * @param string $iso3166
     * @return Language
     */
    public function setIso3166($iso3166)
    {
        $this->iso3166 = $iso3166;
    
        return $this;
    }

    /**
     * Get iso3166
     *
     * @return string 
     */
    public function getIso3166()
    {
        return $this->iso3166;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return Language
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    
        return $this;
    }

    /**
     * Get locale
     *
     * @return string 
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Language
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }
}