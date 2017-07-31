<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use App\Config\SiteConstants;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Connect\Constants;

class AdminService
{
    private $aadGraphService;
    private $tokenCacheService;


    public function __construct()
    {
        $this->aadGraphService = new AADGraphService();
        $this->tokenCacheService = new TokenCacheService();

    }

    /**
     * Get consent URL.
     * An administrator should be prompted to consent on behalf of all users in their organization.
     */
    public function getConsentUrl($state, $redirectUrl)
    {
        $provider = (new AuthenticationHelper())->getProvider($redirectUrl);
        return $provider->getAuthorizationUrl([
            'response_type' => 'code',
            'resource' => Constants::AADGraph,
            'state' => $state,
            'prompt' => SiteConstants::AdminConsent
        ]);
    }

    /**
     * Cancel consent.
     */
    public function unconsent($tenantId, $token)
    {
        $url = Constants::AADGraph . '/' . $tenantId . '/servicePrincipals/?api-version=1.6&$filter=appId%20eq%20\'' . env(Constants::CLIENT_ID) . '\'';
        $app = HttpUtils::getHttpResponseJson($token,$url)->value;
        $appId = $app[0]->objectId;
        $url = Constants::AADGraph . '/' . $tenantId . '/servicePrincipals/' . $appId . '?api-version=1.6';
        HttpUtils::deleteHttpResponse($token,$url);
        (new OrganizationsService)->setTenantConsentResult($tenantId, false);
    }

    /**
     * Enable users of current tenant to access the app.
     * This action will add AppRoleAssignment of this app for each user in the tenancy.
     */
    public function enableUsersAccess()
    {
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $token = $this->tokenCacheService->getMSGraphToken($o365UserId);
        $tenantId = $this->aadGraphService->getTenantIdByUserId($o365UserId,$token);
        $token = $this->tokenCacheService->getAADToken($o365UserId);
        $url = Constants::AADGraph . '/' . $tenantId . '/servicePrincipals/?api-version=1.6&$filter=appId%20eq%20\'' . env(Constants::CLIENT_ID) . '\'';
        $client = new \GuzzleHttp\Client();
        $app = null;
        $authHeader = HttpUtils::getAuthHeader($token);
        try {
            $result = $client->request('GET', $url, $authHeader);
            $app = json_decode($result->getBody())->value;
            $servicePrincipalId = $app[0]->objectId;
            $servicePrincipalName = $app[0]->appDisplayName;
        } catch (\Exception $e) {
            return back()->with('msg', SiteConstants::NoPrincipalError);
        }

        try {
            $this->addAppRoleAssignmentForUsers($authHeader, null, $tenantId, $servicePrincipalId, $servicePrincipalName);

        } catch (\Exception $e) {
            return back()->with('msg', SiteConstants::EnableUserAccessFailed);
        }
        $count = '0';
        if (isset($_SESSION[SiteConstants::Session_EnabledUserCount]))
            $count = (int)$_SESSION[SiteConstants::Session_EnabledUserCount];
        $message = 'There are no users in your tenant.';
        if ($count > 0)
            $message = 'User access was successfully enabled for ' . $count . ' users.';
        $_SESSION[SiteConstants::Session_EnabledUserCount] = null;
        header('Location: ' . '/admin?successMsg=' . $message);
        exit();
    }

    public function getMSGraphToken($redirectUrl, $code)
    {
        $provider = (new AuthenticationHelper())->getProvider($redirectUrl);
        return $provider->getAccessToken('authorization_code', [
            'code' => $code,
            'resource' => Constants::MSGraph
        ]);
    }

    private function addAppRoleAssignmentForUsers($authHeader, $nextLink, $tenantId, $servicePrincipalId, $servicePrincipalName)
    {

        $url = Constants::AADGraph . '/' . $tenantId . '/users?api-version=1.6&$expand=appRoleAssignments';
        if ($nextLink) {
            $url = $url . "&" . $this->getSkipToken($nextLink);
        }
        $client = new \GuzzleHttp\Client();
        $result = $client->request('GET', $url, $authHeader);
        $response = json_decode($result->getBody());
        $users = $response->value;

        $this->addAppRoleAssignment($authHeader, $users, $servicePrincipalId, $servicePrincipalName, $tenantId);
        if (!isset($_SESSION[SiteConstants::Session_EnabledUserCount]))
            $_SESSION[SiteConstants::Session_EnabledUserCount] = count($users);
        else {
            $count = (int)$_SESSION[SiteConstants::Session_EnabledUserCount];
            $count += count($users);
            $_SESSION[SiteConstants::Session_EnabledUserCount] = $count;
        }
        if (isset(get_object_vars($response)['odata.nextLink']))
            $nextLink = get_object_vars($response)['odata.nextLink'];
        else {
            $nextLink = null;
        }
        if ($nextLink) {
            $this->addAppRoleAssignmentForUsers($authHeader, $nextLink, $tenantId, $servicePrincipalId, $servicePrincipalName);
        }

    }

    private function addAppRoleAssignment($authHeader, $users, $servicePrincipalId, $servicePrincipalName, $tenantId)
    {
        $count = count($users);
        $client = new \GuzzleHttp\Client();

        foreach($users as $user){
            $roleAssignment = $user->appRoleAssignments;
            foreach ($roleAssignment as $role) {
                if ($role->resourceId == $servicePrincipalId) {
                    return;
                }
            }
            if (!isset($roleAssignments['odata.nextLink'])) {
                $this->doAddRole($authHeader, $user, $servicePrincipalId, $servicePrincipalName, $tenantId);
            } else {
                $url = Constants::AADGraph . '/' . $tenantId . '/users/' . $user->objectId . '/appRoleAssignments?api-version=1.6&$filter=resourceId%20eq%20guid\'' . $servicePrincipalId . '\'';
                $result = $client->request('GET', $url, $authHeader);
                $response = json_decode($result->getBody());
                if (!$response->value) {
                    $this->doAddRole($authHeader, $user, $servicePrincipalId, $servicePrincipalName, $tenantId);
                }
            }
        }
    }

    private function doAddRole($authHeader, $user, $servicePrincipalId, $servicePrincipalName, $tenantId)
    {

        $client = new \GuzzleHttp\Client();
        $body = [
            'odata.type' => 'Microsoft.DirectoryServices.AppRoleAssignment',
            'principalDisplayName' => $user->displayName,
            'principalId' => $user->objectId,
            'principalType' => 'User',
            'resourceId' => $servicePrincipalId,
            'resourceDisplayName' => $servicePrincipalName
        ];

        $authHeader['body'] = json_encode($body);

        $url = Constants::AADGraph . '/' . $tenantId . '/users/' . $user->objectId . '/appRoleAssignments?api-version=1.6';
        $res = $client->request('POST', $url, $authHeader);

    }

    private function getSkipToken($nextLink)
    {
        $pattern = '/\$skiptoken=[^&]+/';
        preg_match($pattern, $nextLink, $match);
        if (count($match) == 0)
            return '';
        return $match[0];
    }
}