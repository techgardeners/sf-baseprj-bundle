<?php
/*
 * This file is part of the App Framework project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\AppFw\BaseBundle\Extensions\Geocode;

use Geocoder\Geocoder as BaseClass;

class GeocoderEx extends BaseClass
{

    private $rawData = null;
               
    /**
     * {@inheritDoc}
     */
    public function geocode($value)
    {
        if (empty($value)) {
            // let's save a request
            return $this->returnResult(array());
        }

        $data   = $this->getProvider()->getGeocodedData(trim($value));
        $result = $this->returnResult($data);
        
        $this->rawData = isset($data['rawdata']) ? $data['rawdata'] : null;
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
     */    
    public function getRawData() {
        
        return $this->rawData;    
    }
}
