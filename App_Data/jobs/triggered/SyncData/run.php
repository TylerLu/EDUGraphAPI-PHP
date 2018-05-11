<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
require  ('/home/site/wwwroot/vendor/autoload.php');
require ('bootstrap.php');
require('MSGraphHelper.php');
require('DbHelper.php');

$clientId =getenv("CLIENT_ID");


$dbHelper = new DBHelper();
$msGraphHelper = new MSGraphHelper();
$organizations = $dbHelper->getOrganizations();

foreach ($organizations as $org)
{
     $dataSyncRecord = $dbHelper->getOrCreateDataSyncRecord($org);
     $users = $msGraphHelper->queryUsers($dataSyncRecord->DeltaLink,$org->tenantId,$clientId);
    foreach ($users as $user) {
        $dbHelper->updateUser($user);
    }
}

error_log('Succeed!');




