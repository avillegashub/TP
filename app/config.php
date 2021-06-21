<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();
$capsule->addConnection( [
'driver' => 'mysql',
'host' => 'remotemysql.com',
'database' => 'kS9HROZ3zi',
'username' => 'kS9HROZ3zi',
'password' => 'i1evzdwayM',
'charset'   => 'utf8',
'collation' => 'utf8_unicode_ci',
'prefix'    => '',
]);

$capsule->bootEloquent();

?>