<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 11:10
 */

session_start();

// Set proper internal encoding
mb_internal_encoding('UTF-8');

// Include definitions
require_once "lib" . DIRECTORY_SEPARATOR . 'definitions.php';

// Include application library: A utility class with public static functions for use throughout the entire application
require_once('core' . DIRECTORY_SEPARATOR . 'Library.php');

use app\core\Library as Lib;

// Define constant for cross-platform absolute navigation on server:
define('APPLICATION_PATH', realpath( Lib::path(__DIR__ . '/../') ) . DIRECTORY_SEPARATOR);

// Require Auto-loader:
require_once( APPLICATION_PATH . Lib::path('app/core/autoloader.php') );

// All set! Init Application!
use app\core\Application;
( new Application() )->applicator();