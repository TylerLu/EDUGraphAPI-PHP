<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;


class Submission extends ParsableObject
{
    public $id;
    public $status;
    public $submittedDateTime;
    public $submittedBy;
    public $resourcesFolder;
    public $resourcesFolderUrl;
    public $resources;


    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "id" => "id",
                "status" => "status",
                "submittedDateTime" => "submittedDateTime",
                "submittedBy" => "submittedBy",
                "resourcesFolder"=>"resourcesFolder",
                "resourcesFolderUrl"=>"resourcesFolderUrl"
            ]);

    }
}

