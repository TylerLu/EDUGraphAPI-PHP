<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;

use App\Config\EduConstants;

class Section extends ParsableObject
{
    public $id;
    public $courseDescription;
    public $displayName;
    public $mailNickname;
    public $educationObjectType;
    public $externalName;
    public $classCode;
    public $externalId;
    public $externalSource;
    public $term;
    public $termEndDate;
    public $termStartDate;
    public $termName;
    public $termId;

    public $email;
    public $securityEnabled;
    public $period;
    public $courseNumber;
    public $courseName;
    public $courseId;
    public $sectionNumber;
    public $sectionName;
    public $sectionId;
    public $schoolId;
    public $syncSource;
    public $anchorId;
    public $educationStatus;
    public $isMySection;
    public $combinedCourseNumber;
    public $members;
    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "id" => "id",
                "courseDescription" => "description",
                "displayName" => "displayName",
                "educationObjectType" => "extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType",
                "mailNickname"=>"mailNickname",
                "externalName"=>"externalName",
                "classCode"=>"classCode",
                "externalId"=>"externalId",
                "externalSource"=>"externalSource",
                "term"=>"term",
                "members"=>"members"
            ]);
        $this->addArrayElementTypes(["members" => SectionUser::class]);
        $this->termEndDate = $this->term["endDate"];
        $this->termStartDate = $this->term["startDate"];
        $this->termName = $this->term["displayName"];
        $this->termId = $this->term["externalId"];
    }

    /**
     * Parse json data to the object
     *
     * @param string $json The json data
     *
     * @return void
     */
    public function parse($json)
    {
        parent::parse($json);
        $this->combinedCourseNumber = $this->getCombinedCourseNumber();
    }

    /**
     * Get student members
     *
     * @return array The student members
     */
    public function getStudents()
    {
        return collect($this->members)->where("primaryRole", "=", strtolower( EduConstants::StudentObjectType))->all();
    }

    /**
     * Get teacher members
     *
     * @return array The teacher members
     */
    public function getTeachers()
    {
        return collect($this->members)->where("primaryRole", "=", strtolower(EduConstants::TeacherObjectType))->all();
    }

    /**
     * Get the combination of course name and course number
     *
     * @return string The combination of course name and course number
     */
    public function getCombinedCourseNumber()
    {
        return strtoupper(substr($this->courseName, 0, 3)) . $this->getCourseNumber();
    }

    /**
     * Get the digits in course number
     *
     * @return string The digits in course number
     */
    private function getCourseNumber()
    {
        $match = [];
        preg_match('/\d+/', $this->courseNumber, $match);
        return count($match) === 0 ? '' : $match[0];
    }


}