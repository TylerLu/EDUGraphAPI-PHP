<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
class DataSyncRecord
{
    public $id;
    public $tenantId ;
    public $query ;
    public $deltaLink;
    public $updated;
}

class Organization
{
    public $id ;
    public $name ;
    public $tenantId ;
    public $isAdminConsented ;

}

class User
{
    public $id;
    public $jobTitle;
    public $department;
    public $mobilePhone;
    public $isRemoved;
}