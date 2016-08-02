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

    /**
     * Launches the application
     *
     * Checks for post method first.
     */
    public function applicator()
    {
        if( $_SERVER['REQUEST_METHOD'] === "POST"):
           require $this->handlePost();
        else:
            require $this->loadApp();
        endif;
    }

    private function loadApp()
    {
        return "web/common/default.phtml";
    }

    private function handlePost()
    {
        $prefix = "app". DIRECTORY_SEPARATOR ."handlers". DIRECTORY_SEPARATOR;
        $path   = (isset($_POST['path']) ? $_POST['path'] : "");
        $attr   = (isset($_POST['attr']) ? $_POST['attr'] : "");
        $ext    = ".php";

        return $prefix.$path.DIRECTORY_SEPARATOR.$attr.$ext;
    }
}