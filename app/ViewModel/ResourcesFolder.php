<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2017/12/11
 * Time: 12:17
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