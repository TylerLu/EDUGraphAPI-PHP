<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
namespace SyncData;
use Illuminate\Database\Eloquent\Model;
class DataSyncRecord extends Model
{
    protected $table='datasyncrecords';
    protected $fillable = [
        'tenantId', 'query', 'deltaLink','updated'
    ];
}

class Organization extends Model
{
    protected $table='organizations';
    protected $fillable = [
        'name', 'tenantId', 'isAdminConsented'
    ];
}

class User extends Model
{
    protected $table='users';
    protected $fillable = [
        'jobTitle', 'department', 'mobilePhone','isRemoved'
    ];
}