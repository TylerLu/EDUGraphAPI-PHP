<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
require_once ('model.php');

class DBHelper {
    public $usersQuery = "users";

    public function __construct() {
    }

    public function getOrganizations()
    {
        $orgs = SyncData\Organization::where('isAdminConsented', 1)->get();
        if(count($orgs)==0)
        {
            error_log("No consented organization found. This sync was canceled.");
        }
       return $orgs;
    }

    public function getOrCreateDataSyncRecord($org )    
    {
        error_log("Starting to sync users for the ".$org->name." organization.");

        $dataSyncRecord =  SyncData\DataSyncRecord::where("Query",$this->usersQuery)->where("TenantId",$org->tenantId)->first();

        if(!isset($dataSyncRecord))
        {
            error_log('First time executing differential query; all items will return.');
            $url  = 'https://graph.microsoft.com/v1.0/'.$this->usersQuery.'/delta?$select=jobTitle,department,mobilePhone';
            $dataSyncRecord = SyncData\DataSyncRecord::create(['tenantId' => $org->tenantId, 'query'=>$this->usersQuery,'deltaLink'=>$url]);
            $dataSyncRecord->DeltaLink = $url;
        }
        return $dataSyncRecord;
    }
    
    public function updateDatasyncRecorderDeltaLink($org, $deltaLink )
    {       
        $dataSyncRecord =  SyncData\DataSyncRecord::where("TenantId",$org->tenantId)->first();
        if(isset($dataSyncRecord))
        {
            $dataSyncRecord->deltaLink = $deltaLink;
            $dataSyncRecord->save();
        }
    }

    public function updateUser($user)
    {
        $result = SyncData\User::where('o365UserId',$user->id)->first();
        if(!isset($result))
        {
            error_log("Skipping updating user ".$user->id." who does not exist in the local database.");
             return;
        }

        if($user->isRemoved) {
            SyncData\User::destroy($result->id);
        }
        else{
            if(isset($user->jobTitle) || isset($user->mobilePhone) || isset($user->department)) {
                $result->jobTitle = $user->jobTitle;
                $result->mobilePhone = $user->mobilePhone;
                $result->department = $user->department;
                $result->save();
                error_log('Update information for user: ' . $user->id);
           }
        }
        return;
    }
}
