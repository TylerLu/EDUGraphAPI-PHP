<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;


class Assignment extends ParsableObject
{
    public $id;
    public $allowLateSubmissions;
    public $allowStudentsToAddResourcesToSubmission;
    public $assignDateTime;
    public $assignedDateTime;
    public $classId;
    public $displayName;
    public $dueDateTime;
    public $status;
    public $resources;


    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "id" => "id",
                "allowLateSubmissions" => "allowLateSubmissions",
                "allowStudentsToAddResourcesToSubmission" => "allowStudentsToAddResourcesToSubmission",
                "assignDateTime" => "assignDateTime",
                "assignedDateTime"=>"assignedDateTime",
                "classId"=>"classId",
                "displayName"=>"displayName",
                "dueDateTime"=>"dueDateTime",
                "status"=>"status",
                "resources"=>"resources"
            ]);

    }
}

