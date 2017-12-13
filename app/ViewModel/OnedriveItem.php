<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

use App\ViewModel\ParsableObject;

/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2017/12/12
 * Time: 11:01
 */

class OnedriveItem extends ParsableObject
{
    public $id;
    public $parentReference;
    public $name;

    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "id" => "id",
                "parentReference" => "parentReference",
                "name" => "name"
            ]);

    }
}