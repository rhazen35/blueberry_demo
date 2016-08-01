<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 17:44
 */

namespace app\core;

use app\core;
use app\lib;

class Application
{
    public function __construct()
    {

    }

    public function applicate(){

        include( "web/common/default.phtml" );

    }
}