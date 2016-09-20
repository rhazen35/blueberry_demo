<?php

namespace app\core;

if( !class_exists( "Configuration" ) ):

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

        public static function initSet()
        {
            error_reporting(E_ALL);

            ini_set('display_errors', 1);
            ini_set('xdebug.var_display_max_depth', -1);
            ini_set('xdebug.var_display_max_children', 256);
            ini_set('xdebug.var_display_max_data', 1024);

            mb_internal_encoding('UTF-8');
        }

    }

endif;