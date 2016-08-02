<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 18:57
 */

namespace app\lib;

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

    /**
     * @param $get
     */
    public function getRequest( $get )
    {
        switch( $get ):
            /**
             * Register
             */
            case (isset($get["register"])):
                $args = array("register", "login");
                ( new ViewController( ...$args ) )->request();
                break;
            /**
             * Projects
             */
            case (isset($get["projects"])):
                $args = array("projects", "projects");
                ( new ViewController( ...$args ) )->request();
                break;
            case (isset($get["newProject"])):
                $args = array("newProject", "projects");
                ( new ViewController( ...$args ) )->request();
                break;
            /**
             * Models
             */
            case (isset($get["newModel"])):
                $args = array("newModel", "models");
                ( new ViewController( ...$args ) )->request();
                break;
            case (isset($_GET["xmlEAValidatorReport"])):
                $args = array("validationReport", "models");
                ( new ViewController( ...$args ) )->request();
                break;
        endswitch;
    }

}