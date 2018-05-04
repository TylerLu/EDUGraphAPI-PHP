<?php
require_once ('model.php');

class DBHelper {
    public $connection=null;
    public $databaseName = null;
    public $username=null;
    public $password=null;
    public $hostName=null;
    public $usersQuery = "users";

    public function __construct() {
//        $this->databaseName = getenv('DB_DATABASE');
//        $this->username=getenv("DB_USERNAME");
//        $this->password=getenv("DB_PASSWORD");
//        $this->hostName=getenv("DB_HOST");
        $this->databaseName = 'eduphp';
        $this->username='azureuser@edugraphapipython';
        $this->password='Beat@Apple';
        $this->hostName='edugraphapipython.mysql.database.azure.com';


        $this->connection=mysqli_connect($this->hostName,$this->username,$this->password,$this->databaseName);
        if (!$this->connection) {
            mysqli_error($this->connection);
        }
        mysqli_query($this->connection, "set names utf8") or die(mysqli_error($this->connection));
    }


    public function execute($sql) {
        $result=mysqli_query($this->connection,$sql) or die(mysqli_error($this->connection));
        return $result;
    }

    public function syncCore()
    {

    }
    public function getOrganizations()
    {
        $sql ='select * from organizations where isAdminConsented = 1 ';
        $result = $this->execute($sql);
        $organization = new Organization();
        while ($row = $result->fetch_assoc())
        {
            $record->deltaLink=$row['DeltaLink'];
            $record->tenantId=$row['TenantId'];
            $record->query=$row['Query'];
            $record->id=$row['id'];
            break;
        }
    }

    public function getOrCreateDataSyncRecord($tenantId )
    {
        $record = new DataSyncRecord();

        $sql ='select * from datasyncrecords where Query= "'.$this->usersQuery.'" and TenantId ="'.$tenantId.'"' ;
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
            $url  = "https://graph.microsoft.com/beta/".$this->usersQuery."/delta";
            $sqlInsert = "INSERT INTO datasyncrecords (TenantId, Query, DeltaLink)   VALUES ".
                "('$tenantId','$this->usersQuery','$url')";
            $this->execute($sqlInsert);
            $record->deltaLink=$url;
            $record->tenantId=$tenantId;
            $record->query=$this->usersQuery;
            $record->id=0;
        }
        return $result;
    }

}