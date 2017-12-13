<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2017/12/11
 * Time: 10:42
 */

namespace App\ViewModel;


class EducationAssignmentResource extends ParsableObject
{
    public $id;
    public $distributeForStudentWork;
    public $resources; //For submission resources
    public $resource;//For assignment resources
    public $resourcesFolderUrl;

    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "id" => "id",
                "distributeForStudentWork" => "distributeForStudentWork",
                "resources" => "resources",
                "resource" => "resource",
                "resourcesFolderUrl"=>"resourcesFolderUrl"
            ]);

    }
}