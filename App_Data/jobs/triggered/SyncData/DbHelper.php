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


//    public function execute($sql) {
//        $result=mysqli_query($this->connection,$sql) or die(mysqli_error($this->connection));
//        return $result;
//    }

//    public function close()
//    {
//        mysqli_close($this->connection);
//    }

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
        $record = new DataSyncRecord();

        $sql ='select * from datasyncrecords where Query= "'.$this->usersQuery.'" and TenantId ="'.$org->tenantId.'"' ;
        $result = $this->execute($sql);
        while ($row = $result->fetch_assoc())
        {
            $record->deltaLink=$row['DeltaLink'];
            $record->tenantId=$row['TenantId'];
            $record->query=$row['Query'];
            $record->id=$row['id'];
            break;
        }
        if($result->num_rows==0){
            error_log("First time executing differential query; all items will return.");
            $url  = "https://graph.microsoft.com/v1.0/".$this->usersQuery."/delta?$select=jobTitle,department,mobilePhone";
            $sqlInsert = "INSERT INTO datasyncrecords (TenantId, Query, DeltaLink)   VALUES ".
                "('$org->tenantId','$this->usersQuery','$url')";
            $this->execute($sqlInsert);
            $record->deltaLink=$url;
            $record->tenantId=$org->tenantId;
            $record->query=$this->usersQuery;
            $record->id=0;
        }

        return $record;
    }

    public function updateUser($user)
    {
        $sql = 'select id from users where o365UserId="'.$user->id .'"';

        $result = $this->execute($sql);
        if($result->num_rows==0) {
            error_log("Skipping updating user ".$user->id." who does not exist in the local database.");
            return;
        }

        if($user->isRemoved){
            $sql = 'delete from users where o365UserId="'.$user->id .'"';
            $this->execute($sql);
        }
        else
        {
            if(isset($user->jobTitle) || isset($user->mobilePhone) || isset($user->department))
            {
                $sql = 'update users set JobTitle = "'.$user->jobTitle.'",  Department = "'.$user->department.'" , MobilePhone = "'.$user->mobilePhone.'" where o365UserId="'.$user->id .'"';
                $this->execute($sql);
            }
        }

        return;

    }
}