<?php
/*
 * This file is part of the Base Project Bundle
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Extensions\Geocode;

use Geocoder\Geocoder as BaseClass;
use TechG\Bundle\SfBaseprjBundle\Entity\GeoPosition;

class GeocoderEx extends BaseClass
{
               
    /**
     * Returns object of type GeoPosition with data treated by the provider.
     *
     * @param string $address An address (IP or street).
     *
     * @throws NoResultException           If the address could not be resolved
     * @throws InvalidCredentialsException If the credentials are invalid
     * @throws UnsupportedException        If IPv4, IPv6 or street is not supported
     *
     * @return array
     */
    public function geocode($address)
    {
        
        // Geoposition Object
        $geoInfoObj = new GeoPosition();         
        $geoInfoObj->setIpAddr($address);
        
        if (in_array($address, array('127.0.0.1', 'fe80::1', '::1'))) {
            $address = '190.218.72.14';    
        }
        
        // se è vuoto l'ip non faccio la richiesta
        if (!empty($address)) {
            $geoInfoObj->fromArray($this->getProvider()->getGeocodedData(trim($address)));
        }
        
        return $geoInfoObj;
    }
    
    
}
