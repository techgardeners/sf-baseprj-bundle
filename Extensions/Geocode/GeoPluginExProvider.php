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

use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;

use Geocoder\Provider\GeoPluginProvider as BaseClass;

class GeoPluginExProvider extends BaseClass
{

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content || '' === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content, true);

        if (!is_array($json) || !count($json)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        if (!array_key_exists('geoplugin_status', $json) || (200 !== $json['geoplugin_status'])) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $data = array_filter($json);

        return array_merge($this->getDefaults(), array(
            'city'        => isset($data['geoplugin_city']) ? $data['geoplugin_city'] : null,
            'country'     => isset($data['geoplugin_countryName']) ? $data['geoplugin_countryName'] : null,
            'countryCode' => isset($data['geoplugin_countryCode']) ? $data['geoplugin_countryCode'] : null,
            'region'      => isset($data['geoplugin_regionName']) ? $data['geoplugin_regionName'] : null,
            'regionCode'  => isset($data['geoplugin_regionCode']) ? $data['geoplugin_regionCode'] : null,
            'latitude'    => isset($data['geoplugin_latitude']) ? $data['geoplugin_latitude'] : null,
            'longitude'   => isset($data['geoplugin_longitude']) ? $data['geoplugin_longitude'] : null,
            'rawdata'   => $data,
        ));
    }
}
