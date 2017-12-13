<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;


class ResourcesFolder extends ParsableObject
{
    public $odataId;
    public $resourceFolderURL;

    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "odataId" => "odataid",
                "resourceFolderURL" => "value"
            ]);

    }
}