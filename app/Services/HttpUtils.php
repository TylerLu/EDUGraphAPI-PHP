<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use GuzzleHttp\Client;

class HttpUtils
{
    /**
     * Get response of AAD Graph API
     *
     * @param string $token The access token
     * @param string $url The url to send the HTTP request to
     *
     * @return mixed Response of the HTTP request
     */
    public static function getHttpResponse($token, $url)
    {
        return self::getResponse('get',$token,$url);
    }

    public static function postHttpResponse($token, $url)
    {
        return self::getResponse('post',$token,$url);
    }

    public static function getHttpResponseJson($token, $url)
    {
        $result = self::getHttpResponse($token,$url)->getBody();
        return json_decode($result) ;
    }

    public static function deleteHttpResponse($token, $url)
    {
        return self::getResponse('DELETE',$token,$url);
    }

    private static  function getResponse($requestType, $token, $url)
    {
        $client = new Client();
        $authHeader = [];
        if ($token) {
            $authHeader = HttpUtils::getAuthHeader($token);
        }
        return $client->request($requestType, $url, $authHeader);
    }



    /**
     * Get authorization header for http request
     *
     * @param string $token The access token
     *
     * @return array The authorization header for http request
     */
    public static function getAuthHeader($token)
    {
        return [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ];
    }
}