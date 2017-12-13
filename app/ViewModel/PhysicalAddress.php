<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;


class PhysicalAddress extends ParsableObject
{
    public $postOfficeBox;
    public $street;
    public $state;
    public $city;
    public $countryOrRegion;
    public $postalCode;

    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "postOfficeBox" =>"postOfficeBox",
                "street" => "street",
                "state" => "state",
                "city" => "city",
                "countryOrRegion" => "countryOrRegion",
                "postalCode" => "postalCode"
            ]);

    }
}