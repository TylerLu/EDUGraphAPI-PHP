<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */
use Illuminate\Database\Capsule\Manager as Capsule;


$capsule = new Capsule;
$capsule->addConnection([
     "driver" => "mysql",
    "host" =>getenv("DB_HOST"),
    "database" => "edu",
    "username" => getenv("DB_USERNAME"),
    "password" =>getenv("DB_PASSWORD")

]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
