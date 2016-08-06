<?php

namespace application\config;

/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 9-6-2016
 * Time: 16:09
 */

function dbCredentials()
{
    $dbArray = array(
        "dbhost" => '172.21.159.197',
        "dbuser" => 'dbm',
        "dbpass" => 'em2XSp8AowGwC1t2',
        "dbname" => 'quaratio'
    );

    return( $dbArray );
}

function allowedKeys()
{
    $allowedKeys = array(
        'global',
        'request',
        'login',
        'pagination',
        'systemLogs',
        'excel',
        'xmlModel'
    );

    return( $allowedKeys );
}
?>