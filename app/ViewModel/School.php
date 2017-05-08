<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;

class School extends ParsableObject
{
    public $schoolId;
    public $objectId;
    public $objectType;
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
    public $city;
    public $address;
    public $anchorId;
    public $stateId;
    public $latitude;
    public $longitude;
    public $isMySchool;
    public $educationObjectType;

    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "schoolId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId",
                "objectId" => "objectId",
                "objectType" => "objectType",
                "displayName" => "displayName",
                "principalName" => "extension_fe2174665583431c953114ff7268b7b3_Education_SchoolPrincipalName",
                "description" => "description",
                "email" => "extension_fe2174665583431c953114ff7268b7b3_Education_SchoolPrincipalEmail",
                "highestGrade" => "extension_fe2174665583431c953114ff7268b7b3_Education_HighestGrade",
                "lowestGrade" => "extension_fe2174665583431c953114ff7268b7b3_Education_LowestGrade",
                "schoolNumber" => "extension_fe2174665583431c953114ff7268b7b3_Education_SchoolNumber",
                "phone" => "extension_fe2174665583431c953114ff7268b7b3_Education_Phone",
                "zip" => "extension_fe2174665583431c953114ff7268b7b3_Education_Zip",
                "state" => "extension_fe2174665583431c953114ff7268b7b3_Education_State",
                "city" => "extension_fe2174665583431c953114ff7268b7b3_Education_City",
                "address" => "extension_fe2174665583431c953114ff7268b7b3_Education_Address",
                "anchorId" => "extension_fe2174665583431c953114ff7268b7b3_Education_AnchorId",
                "stateId" => "extension_fe2174665583431c953114ff7268b7b3_Education_StateId",
                "educationObjectType" => "extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType"
            ]);
    }

    /**
     * Get the compound address consist of city, state, zip
     *
     * @return string The compound address
     */
    function getCompoundAddress()
    {
        if (strlen($this->city) === 0 && strlen($this->state) === 0 && strlen($this->zip) === 0) {
            return "";
        }
        $city = "";
        if (strlen($this->city) > 0) {
            $city = $this->city . ", ";
        }
        return $city . $this->state . " " . $this->zip;
    }


}