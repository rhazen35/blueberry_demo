<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 11:10
 */

session_start();

use app\core\Library as Lib;
use app\core\Application;

error_reporting(E_ALL);

ini_set('display_errors', 1);
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

mb_internal_encoding('UTF-8');

require_once "lib" . DIRECTORY_SEPARATOR . 'definitions.php';
require_once('core' . DIRECTORY_SEPARATOR . 'Library.php');

define('APPLICATION_PATH', realpath( Lib::path(__DIR__ . '/../') ) . DIRECTORY_SEPARATOR);

require_once( APPLICATION_PATH . Lib::path( 'app/core/autoloader.php' ) );

( new Application() )->launch();