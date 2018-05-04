<?php

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

    public $tenantId ;

    public $name ;

    public $created ;

    public $isAdminConsented ;

    public $issuer = "https://sts.windows.net/";
}