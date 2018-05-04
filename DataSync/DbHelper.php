<?php
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

    public function getOrganizations()
    {
        $sql ='select * from organizations where isAdminConsented = 1 ';
        return $this->execute($sql);
    }

    public function getOrCreateDataSyncRecord($tenantId )
    {

        $sql ='select * from datasyncrecords where Query= "'.$this->usersQuery.'" & TenantId ="'.$tenantId.'"' ;

        $result = $this->execute($sql);

        var_dump($result);

        if(count($result)==0){
            $url  = "https://graph.microsoft.com/beta/".$this->usersQuery."/delta";
            $sql  = "insert into datasyncrecords(TenantId, Query, DeltaLink) values ('.$tenantId.', '.$this->usersQuery.','.$url.')";
            $result = $this->execute($sql);
        }

        return $result;
    }

}