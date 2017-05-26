<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;

class Student extends SectionUser
{
    public $studentId;

    public function __construct()
    {
        parent::__construct();
        $this->addPropertyMappings(["studentId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_StudentId"]);
    }

    /**
     * Get the user id
     *
     * @return string The user id
     */
    public function getUserId()
    {
        return $this->studentId;
    }


}