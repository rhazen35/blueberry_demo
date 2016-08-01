<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 20:48
 */

namespace app\core;


class Configuration
{

    public static function dbCredentials()
    {
        $dbArray = array(
            "dbhost" => '127.0.0.1',
            "dbuser" => 'ruben35',
            "dbpass" => 'Ruben1986Hazenbosch35',
            "dbname" => 'blueberry'
        );

        return( $dbArray );
    }


}