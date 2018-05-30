<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
require('/home/site/wwwroot/vendor/autoload.php');
require('bootstrap.php');
require('MSGraphHelper.php');
require('DbHelper.php');

$clientId =getenv("CLIENT_ID");

$dbHelper = new DBHelper();
$msGraphHelper = new MSGraphHelper();
$organizations = $dbHelper->getOrganizations();

foreach ($organizations as $org)
{
     $dataSyncRecord = $dbHelper->getOrCreateDataSyncRecord($org);
     $results = $msGraphHelper->queryUsers($dataSyncRecord->DeltaLink,$org->tenantId,$clientId);
     $users = $results[0];
     $deltaLink = $results[1];
     foreach ($users as $user) {
          $dbHelper->updateUser($user);
     }
     $dbHelper->updateDatasyncRecorderDeltaLink($org,$deltaLink);
}

error_log('Succeed!');
