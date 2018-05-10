<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
use Illuminate\Database\Capsule\Manager as Capsule;


$capsule = new Capsule;
$capsule->addConnection([
    "driver" => "mysql",
    "host" =>"edugraphapiphp111.mysql.database.azure.com",
    "database" => "edu",
    "username" => "azureuser@edugraphapiphp111",
    "password" => "P@ssw0rd"

//     "driver" => "mysql",
//    "host" =>getenv("DB_HOST"),
//    "database" => "edu",
//    "username" => getenv("DB_USERNAME"),
//    "password" =>getenv("DB_PASSWORD")

]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
