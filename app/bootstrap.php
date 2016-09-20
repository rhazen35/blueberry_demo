<?php

session_start();

use app\core\Library as Lib;
use app\core\Configuration;
use app\core\Application;

require_once( "lib" . DIRECTORY_SEPARATOR . 'definitions.php' );
require_once( "core" . DIRECTORY_SEPARATOR . 'Configuration.php' );
require_once( 'core' . DIRECTORY_SEPARATOR . 'Library.php' );

define('APPLICATION_PATH', realpath( Lib::path(__DIR__ . '/../') ) . DIRECTORY_SEPARATOR);

require_once( APPLICATION_PATH . Lib::path( 'app/core/autoloader.php' ) );

( new Configuration() )->initSet();
( new Application() )->launch();