<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use App\Config\SiteConstants;
use Microsoft\Graph\Connect\Constants;

class AdminService
{
    public function getAuthorizationUrl($state,$redirectUrl)
    {
        $provider = (new AuthenticationHelper())->GetProvider($redirectUrl);

        return $provider->getAuthorizationUrl([
            'response_type' => 'code',
            'resource' => Constants::AADGraph,
            'state' => $state,
            'prompt' => SiteConstants::AdminConsent
        ]);
    }

    public function getMsGraphToken($redirectUrl,$code)
    {
        $provider = (new AuthenticationHelper())->GetProvider($redirectUrl);
        return $provider->getAccessToken('authorization_code', [
            'code' => $code,
            'resource' => Constants::RESOURCE_ID
        ]);
    }

    public function unconsent($tenantId, $token)
    {
        $client = new \GuzzleHttp\Client();
        $url = Constants::AADGraph . '/' . $tenantId . '/servicePrincipals/?api-version=1.6&$filter=appId%20eq%20\'' . env(Constants::CLIENT_ID) . '\'';
        $result = $client->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        $app = json_decode($result->getBody())->value;
        $appId = $app[0]->objectId;
        $url = Constants::AADGraph . '/' . $tenantId . '/servicePrincipals/' . $appId . '?api-version=1.6';
        $result = $client->request('DELETE', $url, [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        (new OrganizationsService)->SetTenantConsentResult($tenantId, false);
    }
}