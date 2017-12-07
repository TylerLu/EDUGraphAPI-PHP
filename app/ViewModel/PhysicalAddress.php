<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2017/12/7
 * Time: 9:29
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