<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;

class SectionUser extends ParsableObject
{
    public $id;
    public $accountEnabled;
    public $displayName;
    public $givenName;
    public $surname;
    public $userPrincipalName;
    public $userType;
    public $primaryRole;

    public $schoolId;
    public $mail;

    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "mail" => "mail",
                "id" => "id",
                "accountEnabled" => "accountEnabled",
                "displayName" => "displayName",
                "givenName" => "givenName",
                "surname" => "surname",
                "userPrincipalName" => "userPrincipalName",
                "userType"=>"userType",
                "primaryRole"=>"primaryRole",
                "schoolId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId",
            ]);
    }


}