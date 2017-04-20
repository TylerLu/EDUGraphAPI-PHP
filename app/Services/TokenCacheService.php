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

    /**
     * $accessTokenArray:
     * [
     *    Constants::AADGraph => [
     *      "expiresOn" => '',
     *      "value" => ''
     *    ],
     *    Constants::RESOURCE_ID => [
     *      "expiresOn" => '',
     *      "value" => ''
     *     ]
     *    ...
     * ]
     */
    public function cacheToken($userId, $refreshToken, $accessTokenArray)
    {
        $accessTokenArray = \GuzzleHttp\json_encode($accessTokenArray);
        $tokenCache = TokenCache::where('UserId', $userId)->first();
        if ($tokenCache) {
            $tokenCache->refreshToken = $refreshToken;
            $tokenCache->accessTokens = $accessTokenArray;
            $tokenCache->save();
        } else {
            $tokenCache = new TokenCache();
            $tokenCache->refreshToken = $refreshToken;
            $tokenCache->accessTokens = $accessTokenArray;
            $tokenCache->UserId = $userId;
            $tokenCache->save();
        }
    }

    /**
     * Get MS Graph token.
     * Token will be refreshed automatically.
     * @param $userId
     * @return array|string
     */
    public function GetMSGraphToken($userId)
    {
        return $this->getToken($userId, Constants::RESOURCE_ID);
    }

    /**
     * Get AAD graph token.
     * Token will be refreshed automatically.
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
        if (!$tokenCache) {
            header('Location: ' . '/o365loginrequired');
            exit();
        }

        $tokens = $tokenCache->accessTokens;
        if (!$tokens) {
            return $this->RefreshToken($userId, $tokenCache->refreshToken, $resource);
        }

        $array = json_decode($tokens, true);
        if (!array_key_exists($resource, $array)) {
            return $this->RefreshToken($userId, $tokenCache->refreshToken, $resource);
        }

        $expired = $array[$resource]['expiresOn'];
        $date1 = gmdate($expired);
        $date2 = gmdate(date("Y-m-d h:i:s"));
        if (!$expired || (strtotime($date1) < strtotime($date2))) {
            return $this->RefreshToken($userId, $tokenCache->refreshToken, $resource);
        }
        return $array[$resource]['value'];
    }

    /**
     * Get a new token with refresh token when a token is expired.
     * @param $userId
     * @param $refreshToken
     * @param $resource
     * @param bool $returnExpires
     * @return array|string
     */
    private function RefreshToken($userId, $refreshToken, $resource, $returnExpires = false)
    {
        try {
            $provider = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId' => env(Constants::CLIENT_ID),
                'clientSecret' => env(Constants::CLIENT_SECRET),
                'redirectUri' => 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://' . $_SERVER['HTTP_HOST'] . '/oauth.php',
                'urlAuthorize' => Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT,
                'urlAccessToken' => Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT,
                'urlResourceOwnerDetails' => ''
            ]);

            $newToken = $this->getRefreshedToken($provider, $resource, $refreshToken);
            $newRefreshToken = $newToken['refreshToken'];

            $jsonArray =[
                $resource => [
                    "expiresOn" => $newToken['expiresOn'],
                    "value" => $newToken['value']
                ]
            ] ;
            $tokenCache = TokenCache::where('UserId', $userId)->first();
            if ($tokenCache) {
                $tokens = \GuzzleHttp\json_decode($tokenCache->accessTokens, true);
                if (array_key_exists($resource, $tokens)) {
                    $tokens[$resource] = [
                        "expiresOn" => $newToken['expiresOn'],
                        "value" => $newToken['value']
                    ];
                }
                else{
                    $tokens[$resource]=[
                        "expiresOn" => $newToken['expiresOn'],
                        "value" => $newToken['value']
                    ];
                }
                $jsonArray = $tokens;
            }

            $this->cacheToken($userId, $newRefreshToken, $jsonArray);

            if ($returnExpires)
                return ["token" => $newToken['value'], "expires" => $newToken['expiresOn']];
            return $newToken['value'];

        } catch (Exception $e) {
            header('Location: ' . '/o365loginrequired');
            exit();
        }

    }


    private function getRefreshedToken($provider, $resource, $refreshToken)
    {
        $token = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken,
            'resource' => $resource
        ]);
        $ts = $token->getExpires();
        $date = new \DateTime("@$ts");
        $msGraphTokenExpires = $date->format('Y-m-d H:i:s');
        $msGraphTokenResult = $token->getToken();
        $newRefreshToken = $token->getRefreshToken();
        return [
            'refreshToken' => $newRefreshToken,
            'value' => $msGraphTokenResult,
            'expiresOn' => $msGraphTokenExpires
        ];
    }

}