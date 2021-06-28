<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();
$capsule->addConnection( [
'driver' => 'mysql',
'host' => 'remotemysql.com',
'database' => 'vTNr71rR92',
'username' => 'vTNr71rR92',
'password' => '57zQf1JyNx',
'charset'   => 'utf8',
'collation' => 'utf8_unicode_ci',
'prefix'    => '',
]);

$capsule->bootEloquent();

?>