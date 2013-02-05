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

use TechG\Bundle\SfBaseprjBundle\Extensions\BaseGeoPosition as BaseEntity;

/**
 * GeoPosition
 *
 * @ORM\Table(name="geo_position")
 * @ORM\Entity
 */
class GeoPosition extends BaseEntity
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
     * @ORM\Column(name="geo_lat", type="string", length=50, nullable=false)
     */
    private $geoLat;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_long", type="string", length=50, nullable=false)
     */
    private $geoLong;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line_first", type="string", length=255, nullable=true)
     */
    private $addressLineFirst;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line_second", type="string", length=255, nullable=true)
     */
    private $addressLineSecond;

    /**
     * @var string
     *
     * @ORM\Column(name="address_number", type="string", length=50, nullable=false)
     */
    private $addressNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=255, nullable=true)
     */
    private $zipcode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = false;

    public function __construct()
    {
        parent::__construct();
    }       


}
