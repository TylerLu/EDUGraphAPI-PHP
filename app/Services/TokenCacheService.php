<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use App\Model\TokenCache;
use Exception;
use Microsoft\Graph\Connect\Constants;

class TokenCacheService
{
    public function UpdateOrInsertCache($userId, $refreshToken, $accessToken)
    {
        $tokenCache = TokenCache::where('UserId', $userId)->first();
        if ($tokenCache) {
            $tokenCache->refreshToken = $refreshToken;
            $tokenCache->accessTokens = $accessToken;
            $tokenCache->save();
        } else {
            $tokenCache = new TokenCache();
            $tokenCache->refreshToken = $refreshToken;
            $tokenCache->accessTokens = $accessToken;
            $tokenCache->UserId = $userId;
            $tokenCache->save();
        }

    }

    /**
     * Return token of Microsoft. This token will be used on schools related page.
     * @param $userId
     * @return array|string
     */
    public function GetMicrosoftToken($userId)
    {
        return $this->getToken($userId, Constants::RESOURCE_ID);
    }

    /**
     * Get AAD token. This token will be used on admin related page.
     * @param $userId
     * @return array|string
     */
    public function GetAADToken($userId)
    {
        return $this->getToken($userId, Constants::AADGraph);
    }


    /**
     * Get token from DB first. If a token is expired, get a new one from O365 using refresh token and then update DB. If refresh token is expired, let user to relogin the site.
     * @param $userId
     * @param $resource
     * @return array|string
     */
    private function getToken($userId, $resource)
    {
        $tokenCache = TokenCache::where('UserId', $userId)->first();

        if ($tokenCache) {
            //1. Check if token is expired. If expired, get a new token with refresh token.
            $token = $tokenCache->accessTokens;
            $array = array();
            $array = json_decode($token, true);
            $expired = $array[$resource]['expiresOn'];

            $date1 = gmdate($expired);
            $date2 = gmdate(date("Y-m-d h:i:s"));

            if (!$expired || (strtotime($date1) < strtotime($date2))) {
                return $this->RefreshToken($userId, $tokenCache->refreshToken, $resource);
            } else
                return $array[$resource]['value'];
        } else {
            header('Location: ' . '/o365loginrequired');
            exit();
        }

    }

    /**
     * Get a new token with refresh token when a token is expired.
     * @param $userId
     * @param $refreshToken
     * @param $resource
     * @param bool $returnExpires
     * @return array|string
     */
    public function RefreshToken($userId, $refreshToken, $resource, $returnExpires = false)
    {
        $aq=$_SERVER['APP_URL'];
        try {
            $provider = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId' => env(Constants::CLIENT_ID),
                'clientSecret' => env(Constants::CLIENT_SECRET),
                'redirectUri' => $_SERVER['APP_URL'].'/oauth.php',
                'urlAuthorize' => Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT,
                'urlAccessToken' => Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT,
                'urlResourceOwnerDetails' => ''
            ]);
            $aadGraphTokenResult = '';
            $aadTokenExpires = '';

            $microsoftTokenResult = '';
            $microsoftTokenExpires = '';

            $newRefreshToken = $refreshToken;
            if ($resource === Constants::RESOURCE_ID) {
                $microsoftToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $refreshToken,
                    'resource' => Constants::RESOURCE_ID
                ]);
                $ts = $microsoftToken->getExpires();
                $date = new \DateTime("@$ts");
                $microsoftTokenExpires = $date->format('Y-m-d H:i:s');
                $microsoftTokenResult = $microsoftToken->getToken();
                $newRefreshToken = $microsoftToken->getRefreshToken();
            } else {
                $aadGraphToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $refreshToken,
                    'resource' => Constants::AADGraph
                ]);
                $ts = $aadGraphToken->getExpires();
                $date = new \DateTime("@$ts");
                $aadTokenExpires = $date->format('Y-m-d H:i:s');
                $aadGraphTokenResult = $aadGraphToken->getToken();
                $newRefreshToken = $aadGraphToken->getRefreshToken();
            }
            $tokenCache = TokenCache::where('UserId', $userId)->first();
            if ($tokenCache) {
                $token = $tokenCache->accessTokens;
                $array = array();
                $array = json_decode($token, true);
                if ($resource === Constants::RESOURCE_ID) {
                    $aadTokenExpires = $array[Constants::AADGraph]['expiresOn'];
                    $aadGraphTokenResult = $array[Constants::AADGraph]['value'];
                } else {
                    $microsoftTokenExpires = $array[Constants::RESOURCE_ID]['expiresOn'];
                    $microsoftTokenResult = $array[Constants::RESOURCE_ID]['value'];
                }
            }
            $format = '{"%s":{"expiresOn":"%s","value":"%s"},"%s":{"expiresOn":"%s","value":"%s"}}';
            $tokensArray = sprintf($format, Constants::AADGraph, $aadTokenExpires, $aadGraphTokenResult, Constants::RESOURCE_ID, $microsoftTokenExpires, $microsoftTokenResult);
            $this->UpdateOrInsertCache($userId, $newRefreshToken, $tokensArray);
            if ($resource === Constants::RESOURCE_ID) {
                if ($returnExpires)
                    return ["token" => $microsoftTokenResult, "expires" => $microsoftTokenExpires];
                return $microsoftTokenResult;
            } else {
                if ($returnExpires)
                    return ["token" => $aadGraphTokenResult, "expires" => $aadTokenExpires];
                return $aadGraphTokenResult;
            }
        } catch (Exception $e) {
            header('Location: ' . '/o365loginrequired');
            exit();
        }

    }
}