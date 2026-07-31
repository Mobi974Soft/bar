<?php
$remoteDb = [
        'host' => '143.14.22.158',
        'dbname' => "mobipos_test_staging",
        'user' => "bUwspaXWewlxBalK",
        'pass' => "eX3Q8m3MxCwo9DEF"
    ];

    $pdoRemote = new PDO("mysql:host={$remoteDb['host']};dbname={$remoteDb['dbname']};charset=utf8", $remoteDb['user'], $remoteDb['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);