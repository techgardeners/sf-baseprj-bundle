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

use TechG\Bundle\SfBaseprjBundle\Entity\Base\BaseGeoPosition as BaseEntity;

/**
 * GeoPosition
 *
 * @ORM\Table(name="geo_position")
 * @ORM\Entity
 */
class GeoPosition extends BaseEntity
{
    
    // hold the bound informations
    private $bounds = array();

    // hold the raw data informations
    private $rawData= array();

    
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
     * @ORM\Column(name="latitude", type="string", length=50, nullable=false)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=50, nullable=false)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="bound_south", type="string", length=50, nullable=false)
     */
    private $bound_south;

    /**
     * @var string
     *
     * @ORM\Column(name="bound_north", type="string", length=50, nullable=false)
     */
    private $bound_north;

    /**
     * @var string
     *
     * @ORM\Column(name="bound_west", type="string", length=50, nullable=false)
     */
    private $bound_west;

    /**
     * @var string
     *
     * @ORM\Column(name="bound_east", type="string", length=50, nullable=false)
     */
    private $bound_east;

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
     * @ORM\Column(name="street_number", type="string", length=50, nullable=false)
     */
    private $streetNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="street_name", type="string", length=255, nullable=false)
     */
    private $streetName;

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
     * @ORM\Column(name="region_code", type="string", length=255, nullable=true)
     */
    private $regionCode;    
    
    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=255, nullable=true)
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city_district", type="string", length=255, nullable=true)
     */
    private $cityDistrict;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=255, nullable=true)
     */
    private $timezone;


    /**
     * @var string
     *
     * @ORM\Column(name="ip_addr", type="string", length=255, nullable=true)
     */
    private $ipAddr;

    /**
     * @var string
     *
     * @ORM\Column(name="provider", type="string", length=255, nullable=true)
     */
    private $provider;

    /**
     * @var string
     *
     * @ORM\Column(name="data_origin", type="string", length=255, nullable=true)
     */
    private $dataOrigin;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="geo_date", type="datetime", nullable=false)
     */
    private $geoDate;    


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


// **********************************************************************************************************    
//  PUBLIC METHOD
// **********************************************************************************************************    

    /**
     * {@inheritDoc}
     */
    public function fromArray(array $data = array())
    {
        if (isset($data['latitude'])) {
            $this->latitude = (double) $data['latitude'];
        }

        if (isset($data['longitude'])) {
            $this->longitude = (double) $data['longitude'];
        }

        if (isset($data['bounds']) && is_array($data['bounds'])) {
            $this->setBounds($data['bounds']);
        }

        if (isset($data['streetNumber'])) {
            $this->streetNumber = (string) $data['streetNumber'];
        }

        if (isset($data['streetName'])) {
            $this->streetName = $this->formatString($data['streetName']);
        }

        if (isset($data['city'])) {
            $this->city = $this->formatString($data['city']);
        }

        if (isset($data['zipcode'])) {
            $this->zipcode = (string) $data['zipcode'];
        }

        if (isset($data['cityDistrict'])) {
            $this->cityDistrict = $this->formatString($data['cityDistrict']);
        }

        if (isset($data['county'])) {
            $this->country = $this->formatString($data['county']);
        }

        if (isset($data['countyCode'])) {
            $this->countryCode = $this->upperize($data['countyCode']);
        }

        if (isset($data['country'])) {
            $this->country = $this->formatString($data['country']);
        }

        if (isset($data['countryCode'])) {
            $this->countryCode = $this->upperize($data['countryCode']);
        }

        if (isset($data['region'])) {
            $this->region = $this->formatString($data['region']);
        }

        if (isset($data['regionCode'])) {
            $this->regionCode = $this->upperize($data['regionCode']);
        }

        if (isset($data['timezone'])) {
            $this->timezone = (string) $data['timezone'];
        }
        
        // in base al provider ci sono parametri che geocoded non mette nell'oggetto.
        if (isset($data['rawdata'])) {
            $this->rawData = $data['rawdata'];
        } 
                
        $this->setGeoDate(new \DateTime());
        
    }

    /**
     * {@inheritDoc}
     */
    public function toArray($retRawData = false)
    {
        $dataArray = array(
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'bounds'        => $this->bounds,
            'streetNumber'  => $this->streetNumber,
            'streetName'    => $this->streetName,
            'zipcode'       => $this->zipcode,
            'city'          => $this->city,
            'cityDistrict'  => $this->cityDistrict,
            'country'        => $this->country,
            'countryCode'    => $this->countryCode,
            'region'        => $this->region,
            'regionCode'    => $this->regionCode,
            'country'       => $this->country,
            'countryCode'   => $this->countryCode,
            'timezone'      => $this->timezone,
            'geoip'      => $this->ipAddr,
        );
        
        if ($retRawData) { $dataArray['rawdata'] = $this->rawData; }

        return $dataArray;
    }    

    public function getLogInfo()
    {                                
        $dataArray = $this->toArray();
        $dataArray['provider'] = $this->getProvider();
        $dataArray['origin'] = $this->getDataOrigin();
        $dataArray['geodata'] = $this->getGeoDate();
        
        return $dataArray;
    }    


    public function setBounds(array $data = array())
    {
        $this->bounds = array(
            'south' => (double) $data['south'],
            'west'  => (double) $data['west'],
            'north' => (double) $data['north'],
            'east'  => (double) $data['east']
        );
        
        $this->bound_south = $this->bounds['south'];
        $this->bound_north = $this->bounds['north'];
        $this->bound_west = $this->bounds['west'];
        $this->bound_east = $this->bounds['east'];        
    }

    public function getBounds()
    {
        $this->bounds = array(
            'south' => (double) $this->bound_south,
            'west'  => (double) $this->bound_west,
            'north' => (double) $this->bound_north,
            'east'  => (double) $this->bound_east
        );

        return $this->bounds;       
    }    
    
    
// **********************************************************************************************************    
//  GET SET METHOD
// **********************************************************************************************************  
  

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
     * Set latitude
     *
     * @param string $latitude
     * @return GeoPosition
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    
        return $this;
    }

    /**
     * Get latitude
     *
     * @return string 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return GeoPosition
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    
        return $this;
    }

    /**
     * Get longitude
     *
     * @return string 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set bound_south
     *
     * @param string $boundSouth
     * @return GeoPosition
     */
    public function setBoundSouth($boundSouth)
    {
        $this->bound_south = $boundSouth;
    
        return $this;
    }

    /**
     * Get bound_south
     *
     * @return string 
     */
    public function getBoundSouth()
    {
        return $this->bound_south;
    }

    /**
     * Set bound_north
     *
     * @param string $boundNorth
     * @return GeoPosition
     */
    public function setBoundNorth($boundNorth)
    {
        $this->bound_north = $boundNorth;
    
        return $this;
    }

    /**
     * Get bound_north
     *
     * @return string 
     */
    public function getBoundNorth()
    {
        return $this->bound_north;
    }

    /**
     * Set bound_west
     *
     * @param string $boundWest
     * @return GeoPosition
     */
    public function setBoundWest($boundWest)
    {
        $this->bound_west = $boundWest;
    
        return $this;
    }

    /**
     * Get bound_west
     *
     * @return string 
     */
    public function getBoundWest()
    {
        return $this->bound_west;
    }

    /**
     * Set bound_east
     *
     * @param string $boundEast
     * @return GeoPosition
     */
    public function setBoundEast($boundEast)
    {
        $this->bound_east = $boundEast;
    
        return $this;
    }

    /**
     * Get bound_east
     *
     * @return string 
     */
    public function getBoundEast()
    {
        return $this->bound_east;
    }

    /**
     * Set addressLineFirst
     *
     * @param string $addressLineFirst
     * @return GeoPosition
     */
    public function setAddressLineFirst($addressLineFirst)
    {
        $this->addressLineFirst = $addressLineFirst;
    
        return $this;
    }

    /**
     * Get addressLineFirst
     *
     * @return string 
     */
    public function getAddressLineFirst()
    {
        return $this->addressLineFirst;
    }

    /**
     * Set addressLineSecond
     *
     * @param string $addressLineSecond
     * @return GeoPosition
     */
    public function setAddressLineSecond($addressLineSecond)
    {
        $this->addressLineSecond = $addressLineSecond;
    
        return $this;
    }

    /**
     * Get addressLineSecond
     *
     * @return string 
     */
    public function getAddressLineSecond()
    {
        return $this->addressLineSecond;
    }

    /**
     * Set streetNumber
     *
     * @param string $streetNumber
     * @return GeoPosition
     */
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;
    
        return $this;
    }

    /**
     * Get streetNumber
     *
     * @return string 
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * Set streetName
     *
     * @param string $streetName
     * @return GeoPosition
     */
    public function setStreetName($streetName)
    {
        $this->streetName = $streetName;
    
        return $this;
    }

    /**
     * Get streetName
     *
     * @return string 
     */
    public function getStreetName()
    {
        return $this->streetName;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return GeoPosition
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    
        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string 
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return GeoPosition
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return GeoPosition
     */
    public function setState($state)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return GeoPosition
     */
    public function setRegion($region)
    {
        $this->region = $region;
    
        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set regionCode
     *
     * @param string $regionCode
     * @return GeoPosition
     */
    public function setRegionCode($regionCode)
    {
        $this->regionCode = $regionCode;
    
        return $this;
    }

    /**
     * Get regionCode
     *
     * @return string 
     */
    public function getRegionCode()
    {
        return $this->regionCode;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return GeoPosition
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     * @return GeoPosition
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    
        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set cityDistrict
     *
     * @param string $cityDistrict
     * @return GeoPosition
     */
    public function setCityDistrict($cityDistrict)
    {
        $this->cityDistrict = $cityDistrict;
    
        return $this;
    }

    /**
     * Get cityDistrict
     *
     * @return string 
     */
    public function getCityDistrict()
    {
        return $this->cityDistrict;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return GeoPosition
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    
        return $this;
    }

    /**
     * Get timezone
     *
     * @return string 
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set ipAddr
     *
     * @param string $ipAddr
     * @return GeoPosition
     */
    public function setIpAddr($ipAddr)
    {
        $this->ipAddr = $ipAddr;
    
        return $this;
    }

    /**
     * Get ipAddr
     *
     * @return string 
     */
    public function getIpAddr()
    {
        return $this->ipAddr;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return GeoPosition
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

    /**
     * Set provider
     *
     * @param string $provider
     * @return GeoPosition
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    
        return $this;
    }

    /**
     * Get provider
     *
     * @return string 
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set dataOrigin
     *
     * @param string $dataOrigin
     * @return GeoPosition
     */
    public function setDataOrigin($dataOrigin)
    {
        $this->dataOrigin = $dataOrigin;
    
        return $this;
    }

    /**
     * Get dataOrigin
     *
     * @return string 
     */
    public function getDataOrigin()
    {
        return $this->dataOrigin;
    }

    /**
     * Set geoDate
     *
     * @param \DateTime $geoDate
     * @return GeoPosition
     */
    public function setGeoDate($geoDate)
    {
        $this->geoDate = $geoDate;
    
        return $this;
    }

    /**
     * Get geoDate
     *
     * @return \DateTime 
     */
    public function getGeoDate()
    {
        return $this->geoDate;
    }
}