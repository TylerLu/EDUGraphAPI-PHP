
<?php
require  'vendor/autoload.php';
require('MSGraphHelper.php');
require('DbHelper.php');


$helper = new MSGraphHelper();

$clientId =getenv("CLIENT_ID");

$dbHelper = new DBHelper();
$msGraphHelper = new MSGraphHelper();
$organizations = $dbHelper->getOrganizations();

foreach ($organizations as $org)
{
    $dataSyncRecord = $dbHelper->getOrCreateDataSyncRecord($org);
    $users = $msGraphHelper->queryUsers($dataSyncRecord->deltaLink,$org->tenantId,$clientId);
    foreach ($users as $user) {
        $dbHelper->updateUser($user);
    }
}

$dbHelper->close();



