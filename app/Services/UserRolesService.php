<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;
use App\Model\UserRoles;

class UserRolesService
{
    public function createOrUpdateUserRoles($roles, $userId){
        UserRoles::where('UserId',  $userId)->delete();
        while ($role = each($roles)) {
            $userRole = new   UserRoles();
            $userRole->name = $role['value'];
            $userRole->UserId = $userId;
            $userRole->save();
        }
    }

    public function isUserAdmin($userId){
       $role = UserRoles::where('UserId',  $userId)->first();
       if($role)
           return true;
        return false;
    }

    public function getUserRole($userId){
        $role = UserRoles::where('UserId',  $userId)->first();
        if($role)
            return $role->name;
        return '';
    }
}