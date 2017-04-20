<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use App\Config\O365ProductLicenses;
use App\Config\Roles;
use App\Config\SiteConstants;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Connect\Constants;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class AADGraphService
{

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function GetCurrentUserRoles($userId, $token)
    {
        $graph = new Graph();
        $graph->setAccessToken($token);
        $licenses = $graph->createRequest("get", "/me/assignedLicenses")
            ->setReturnType(Model\AssignedLicense::class)
            ->execute();
        $roles = array();
        if ($this->IsUserAdmin($userId))
            array_push($roles, Roles::Admin);
        if (self::IsUserStudent($licenses))
            array_push($roles, Roles::Student);
        if (self::IsUserTeacher($licenses))
            array_push($roles, Roles::Faculty);
        return $roles;

    }

    public function GetTenantByUserId($userId, $token)
    {
        return $this->GetTenantByToken($token);
    }

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

    public function GetTenantIdByUserId($userId, $token)
    {
        $tenant = $this->GetTenantByUserId($userId, $token);
        return $this->GetTenantId($tenant);
    }

    public static function IsUserStudent($licenses)
    {
        while ($license = each($licenses)) {
            if (is_array($license['value']))
                return false;
            if ($license['value']->getSkuId() === O365ProductLicenses::Student || $license['value']->getSkuId() === O365ProductLicenses::StudentPro) {
                return true;
            }
        }
        return false;
    }

    public static function IsUserTeacher($licenses)
    {
        while ($license = each($licenses)) {
            if (is_array($license['value']))
                return false;
            if ($license['value']->getSkuId() === O365ProductLicenses::Faculty || $license['value']->getSkuId() === O365ProductLicenses::FacultyPro) {
                return true;
            }
        }
        return false;
    }

    private function IsUserAdmin($userId)
    {
        $tenantId = null;
        if (Auth::user()) {
            $tenantId = Auth::user()->tenantId;
        } else {
            $token = (new TokenCacheService)->GetAADToken($userId);
            $tenantId = $this->GetTenantIdByUserId($userId, $token);
        }
        $token = (new TokenCacheService)->GetAADToken($userId);
        $adminRoles = $this->GetDirectoryAdminRole($tenantId, $token);
        $adminMembers = $this->GetAdminDirectoryMembers($tenantId, $adminRoles['value']->objectId, $token);
        $isAdmin = false;
        while ($member = each($adminMembers)) {
            if (stripos($member['value']->url, $userId) != false) {
                $isAdmin = true;
            }
        }
        return $isAdmin;
    }

    private function GetDirectoryAdminRole($tenantId, $token)
    {
        $url = Constants::AADGraph . '/' . $tenantId . '/directoryRoles?api-version=1.6';
        $roles = HttpUtils::getHttpResponseJson($token, $url)->value;
        while ($role = each($roles)) {
            if ($role['value']->displayName === SiteConstants::AADCompanyAdminRoleName) {
                return $role;
            }
        }
    }

    private function GetAdminDirectoryMembers($tenantId, $roleId, $token)
    {
        $url = Constants::AADGraph . '/' . $tenantId . '/directoryRoles/' . $roleId . '/$links/members?api-version=1.6';
        return HttpUtils::getHttpResponseJson($token, $url)->value;
    }
}