<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;

use App\Config\EduConstants;

class Section extends ParsableObject
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->addPropertyMappings(
            [
                "objectId" => "objectId",
                "objectType" => "objectType",
                "educationObjectType" => "extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType",
                "displayName" => "displayName",
                "email" => "mail",
                "securityEnabled" => "securityEnabled",
                "mailNickname" => "mailNickname",
                "period" => "extension_fe2174665583431c953114ff7268b7b3_Education_Period",
                "courseNumber" => "extension_fe2174665583431c953114ff7268b7b3_Education_CourseNumber",
                "courseDescription" => "extension_fe2174665583431c953114ff7268b7b3_Education_CourseDescription",
                "courseName" => "extension_fe2174665583431c953114ff7268b7b3_Education_CourseName",
                "courseId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_CourseId",
                "termEndDate" => "extension_fe2174665583431c953114ff7268b7b3_Education_TermEndDate",
                "termStartDate" => "extension_fe2174665583431c953114ff7268b7b3_Education_TermStartDate",
                "termName" => "extension_fe2174665583431c953114ff7268b7b3_Education_TermName",
                "termId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_TermId",
                "sectionNumber" => "extension_fe2174665583431c953114ff7268b7b3_Education_SectionNumber",
                "sectionName" => "extension_fe2174665583431c953114ff7268b7b3_Education_SectionName",
                "sectionId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SectionId",
                "schoolId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId",
                "syncSource" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource",
                "anchorId" => "extension_fe2174665583431c953114ff7268b7b3_Education_AnchorId",
                "educationStatus" => "extension_fe2174665583431c953114ff7268b7b3_Education_Status",
                "members" => "members"
            ]);
        $this->addArrayElementTypes(["members" => SectionUser::class]);
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
        return collect($this->members)->where("educationObjectType", "=", EduConstants::StudentObjectType)->all();
    }

    /**
     * Get teacher members
     *
     * @return array The teacher members
     */
    public function getTeachers()
    {
        return collect($this->members)->where("educationObjectType", "=", EduConstants::TeacherObjectType)->all();
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

    public $objectId;
    public $objectType;
    public $educationObjectType;
    public $displayName;
    public $email;
    public $securityEnabled;
    public $mailNickname;
    public $period;
    public $courseNumber;
    public $courseDescription;
    public $courseName;
    public $courseId;
    public $termEndDate;
    public $termStartDate;
    public $termName;
    public $termId;
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
}