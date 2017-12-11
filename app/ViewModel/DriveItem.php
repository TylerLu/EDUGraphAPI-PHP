<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2017/12/11
 * Time: 16:23
 */

namespace App\ViewModel;


class DriveItem extends ParsableObject
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