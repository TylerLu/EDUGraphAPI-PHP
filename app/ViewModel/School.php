<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;

class School extends ParsableObject
{

    public $id;
    public $displayName;
    public $principalName;
    public $description;
    public $email;
    public $highestGrade;
    public $lowestGrade;
    public $schoolNumber;
    public $phone;
    public $zip;
    public $state;
    public $street;
    public $city;
    public $address;
    public $countryOrRegion;
    public $stateId;
    public $isMySchool;
    public $educationObjectType;
    public $physicalAddress;


    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "id" =>"id",
                "displayName" => "displayName",
                "principalName" => "principalName",
                "description" => "description",
                "email" => "principalEmail",
                "highestGrade" => "highestGrade",
                "lowestGrade" => "lowestGrade",
                "schoolNumber" => "schoolNumber",
                "physicalAddress"=>"address"
            ]);

    }

    /**
     * Get the compound address consist of city, state, zip
     *
     * @return string The compound address
     */
    function getCompoundAddress()
    {
        $this->city = $this->physicalAddress["city"];
        $this->street = $this->physicalAddress["street"];
        $this->zip = $this->physicalAddress["postalCode"];
        $this->countryOrRegion = $this->physicalAddress["countryOrRegion"];
        $this->state = $this->physicalAddress["state"];

        if (strlen($this->city) === 0 && strlen($this->state) === 0 && strlen($this->zip) === 0) {
            return "";
        }
        $city = "";
        if (strlen($this->city) > 0) {
            $city = $this->city . ", ";
        }
        return $this->street ."<br/>". $city . $this->state . " " . $this->zip;

    }


}