<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use Microsoft\Graph\Connect\Constants;

class MapUtils
{
    /**
     * Get the longitude and latitude of a location.
     *
     * @param $state The state
     * @param $city The city
     * @param $address The address
     *
     * @return mixed longitude and latitude of the place
     */
    public static function getLatitudeAndLongitude($state, $city, $address)
    {
        $bingMapKey = env(Constants::BINGMAPKEY,'');
        if(!$bingMapKey){
            return null;
        }
        if ($state || $city || $address) {
            $url = sprintf("http://dev.virtualearth.net/REST/v1/Locations/US/%s/%s/%s?output=json&key=%s", $state, $city, $address, env(Constants::BINGMAPKEY,''));
            $result = HttpUtils::getHttpResponse( null, $url);
            $json = json_decode($result->getBody());
            return $json->resourceSets[0]->resources[0]->point->coordinates;
        }
        return null;
    }
}