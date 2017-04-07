<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use Microsoft\Graph\Connect\Constants;

class AuthenticationHelper
{
    /**
     * Create a new provider for login O365 and get token.
     * @param string $redirectUri
     * @return \League\OAuth2\Client\Provider\GenericProvider
     */
    public  function GetProvider($redirectUri)
    {

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => env(Constants::CLIENT_ID),
            'clientSecret' => env(Constants::CLIENT_SECRET),
            'redirectUri' => $redirectUri,
            'urlAuthorize' => Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT,
            'urlAccessToken' => Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT,
            'urlResourceOwnerDetails' => ''
        ]);
        return $provider;
    }
}

