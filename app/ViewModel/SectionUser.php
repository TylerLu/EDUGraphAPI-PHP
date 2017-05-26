<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;

class SectionUser extends ParsableObject
{
    public $mail;
    public $educationObjectType;
    public $displayName;
    public $educationGrade;
    public $schoolId;
    public $o365UserId;
    public $position;
    public $favoriteColor;
    public $userRole;

    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "mail" => "mail",
                "educationObjectType" => "extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType",
                "displayName" => "displayName",
                "educationGrade" => "extension_fe2174665583431c953114ff7268b7b3_Education_Grade",
                "schoolId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId",
                "o365UserId" => "objectId"
            ]);
    }


}