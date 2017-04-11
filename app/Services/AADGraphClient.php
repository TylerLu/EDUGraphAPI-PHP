<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use App\Config\O365ProductLicenses;
use App\Config\Roles;
use App\Config\SiteConstants;
use GuzzleHttp\Client;
use Microsoft\Graph\Connect\Constants;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class AADGraphClient
{

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Get current user and roles from AAD. Update user roles to database.
     */
    public function GetCurrentUserAndUpdateUserRoles($userId)
    {
        $token = (new TokenCacheService)->GetMicrosoftToken($userId);
        if ($token) {
            $graph = new Graph();
            $graph->setAccessToken($token);
            $me = $graph->createRequest("get", "/me")
                ->setReturnType(Model\User::class)
                ->execute();
            $licenses = $graph->createRequest("get", "/me/assignedLicenses")
                ->setReturnType(Model\AssignedLicense::class)
                ->execute();
            $roles = array();
            if ($this->IsUserAdmin($userId))
                array_push($roles, Roles::Admin);
            if ($this->IsUserStudent($licenses))
                array_push($roles, Roles::Student);
            if ($this->IsUserTeacher($licenses))
                array_push($roles, Roles::Faculty);
            (new UserRolesService)->CreateOrUpdateUserRoles($roles, $userId);

        }

    }

    /**
     * Get tenant by o365 user id.
     * @param $userId
     * @return mixed|null
     */
    public function GetTenantByUserId($userId)
    {
        $token = (new TokenCacheService)->GetMicrosoftToken($userId);
        return $this->GetTenantByToken($token);
    }

    /**
     * Get tenant directly if token exists.
     * @param $token
     * @return mixed|null
     */
    public function GetTenantByToken($token)
    {
        if ($token) {
            $graph = new Graph();
            $graph->setAccessToken($token);
            $org = $graph->createRequest("get", "/organization")
                ->setReturnType(Model\Organization::class)
                ->execute();
            return $org;
        }
        return null;
    }

    public function GetTenantId($tenant)
    {
        $array = json_decode(json_encode($tenant[0]));
        return $array->id;
    }

    public function GetTenantIdByUserId($userId)
    {
        $tenant = $this->GetTenantByUserId($userId);
        return $this->GetTenantId($tenant);
    }


    private function IsUserAdmin($userId)
    {
        $tenantId = '';
        if (isset($_SESSION[SiteConstants::Session_TenantId])) {
            $tenantId = $_SESSION[SiteConstants::Session_TenantId];
        } else {
            $tenantId = $this->GetTenantIdByUserId($userId);
        }
        $token = (new TokenCacheService)->GetAADToken($userId);
        $adminRoles = $this->GetDirectoryAdminRole($userId, $tenantId, $token);
        $adminMembers = $this->GetAdminDirectoryMembers($tenantId, $adminRoles['value']->objectId, $token);
        $isAdmin = false;
        while ($member = each($adminMembers)) {
            if (stripos($member['value']->url, $userId) != false) {
                $isAdmin = true;
            }
        }
        return $isAdmin;
    }

    private function GetDirectoryAdminRole($userId, $tenantId, $token)
    {

        $url = Constants::AADGraph . '/' . $tenantId . '/directoryRoles?api-version=1.6';
        $client = new Client();

        $result = $client->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        $roles = json_decode($result->getBody())->value;
        while ($role = each($roles)) {
            if ($role['value']->displayName === SiteConstants::AADCompanyAdminRoleName) {
                return $role;
            }
        }
    }

    private function GetAdminDirectoryMembers($tenantId, $roleId, $token)
    {
        $url = Constants::AADGraph . '/' . $tenantId . '/directoryRoles/' . $roleId . '/$links/members?api-version=1.6';
        $client = new Client();

        $result = $client->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        return $members = json_decode($result->getBody())->value;
    }

    public function IsUserStudent($licenses)
    {
        while ($license = each($licenses)) {
            if ($license['value']->getSkuId() === O365ProductLicenses::Student || $license['value']->getSkuId() === O365ProductLicenses::StudentPro) {
                return true;
            }
        }
        return false;
    }

    public function IsUserTeacher($licenses)
    {
        while ($license = each($licenses)) {
            if ($license['value']->getSkuId() === O365ProductLicenses::Faculty || $license['value']->getSkuId() === O365ProductLicenses::FacultyPro) {
                return true;
            }
        }
        return false;
    }

}