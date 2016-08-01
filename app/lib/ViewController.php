<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 18:57
 */

namespace app\lib;

use app\core\Library as Lib;

class ViewController
{

    protected $page;
    protected $directory;
    protected $root = "web";

    public function __construct( $page, $directory )
    {
        $this->page      = $page;
        $this->directory = $directory;
    }


    public function request()
    {
        include $this->root . DIRECTORY_SEPARATOR . $this->directory . DIRECTORY_SEPARATOR . $this->page . ".phtml";
    }

}