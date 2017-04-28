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

    public function getCurrentUserRoles($userId, $token)
    {
        $graph = new Graph();
        $graph->setAccessToken($token);
        $licenses = $graph->createRequest("get", "/me/assignedLicenses")
            ->setReturnType(Model\AssignedLicense::class)
            ->execute();
        $roles = array();
        if ($this->isUserAdmin($userId))
            array_push($roles, Roles::Admin);
        if (self::isUserStudent($licenses))
            array_push($roles, Roles::Student);
        if (self::isUserTeacher($licenses))
            array_push($roles, Roles::Faculty);
        return $roles;

    }

    public function getTenantByUserId($userId, $token)
    {
        return $this->getTenantByToken($token);
    }

    public function getTenantByToken($token)
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

    public function getTenantId($tenant)
    {
        $array = json_decode(json_encode($tenant[0]));
        return $array->id;
    }

    public function getTenantIdByUserId($userId, $token)
    {
        $tenant = $this->getTenantByUserId($userId, $token);
        return $this->getTenantId($tenant);
    }

    public static function isUserStudent($licenses)
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

    public static function isUserTeacher($licenses)
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

    private function isUserAdmin($userId)
    {
        $tenantId = null;
        if (Auth::user()) {
            $tenantId = Auth::user()->tenantId;
        } else {
            $token = (new TokenCacheService)->getAADToken($userId);
            $tenantId = $this->getTenantIdByUserId($userId, $token);
        }
        $token = (new TokenCacheService)->getAADToken($userId);
        $adminRoles = $this->getDirectoryAdminRole($tenantId, $token);
        $adminMembers = $this->getAdminDirectoryMembers($tenantId, $adminRoles['value']->objectId, $token);
        $isAdmin = false;
        while ($member = each($adminMembers)) {
            if (stripos($member['value']->url, $userId) != false) {
                $isAdmin = true;
            }
        }
        return $isAdmin;
    }

    private function getDirectoryAdminRole($tenantId, $token)
    {
        $url = Constants::AADGraph . '/' . $tenantId . '/directoryRoles?api-version=1.6';
        $roles = HttpUtils::getHttpResponseJson($token, $url)->value;
        while ($role = each($roles)) {
            if ($role['value']->displayName === SiteConstants::AADCompanyAdminRoleName) {
                return $role;
            }
        }
    }

    private function getAdminDirectoryMembers($tenantId, $roleId, $token)
    {
        $url = Constants::AADGraph . '/' . $tenantId . '/directoryRoles/' . $roleId . '/$links/members?api-version=1.6';
        return HttpUtils::getHttpResponseJson($token, $url)->value;
    }
}