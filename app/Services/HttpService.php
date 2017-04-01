<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use GuzzleHttp\Client;

class HttpService
{
    /**
     * Get response of AAD Graph API
     *
     * @param string $requestType The HTTP method to use, e.g. "GET" or "POST"
     * @param string $token The access token
     * @param string $url The url to send the HTTP request to
     *
     * @return mixed Response of the HTTP request
     */
    public static function getHttpResponse($requestType, $token, $url)
    {
        $client = new Client();
        $authHeader = [];
        if ($token) {
            $authHeader = HttpService::getAuthHeader($token);
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
    private static function getAuthHeader($token)
    {
        return [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ];
    }
}