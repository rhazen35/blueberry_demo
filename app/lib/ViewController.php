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
            case (isset($get["newProject"])
                || isset($get['newProjectEmptyFields'])
                || isset($get['projectExists'])):
                $args = array("newProject", "projects");
                ( new ViewController( ...$args ) )->request();
                break;
            case (isset($get["project_settings"])
                || isset($get['deleteProjectAccept'])):
                $args = array("settings", "projects");
                ( new ViewController( ...$args ) )->request();
                break;
            /**
             * Models
             */
            case (isset($get["newModel"])
                || isset($get['modelUploadNoFile'])
                || isset($get['modelUploadNoProject'])
                || isset($get['modelUploadNameExists'])):
                $args = array("newModel", "models");
                ( new ViewController( ...$args ) )->request();
                break;
            case (isset($get["models"])
                || isset($get["changeModelAccept"])):
                $args = array("models", "models");
                ( new ViewController( ...$args ) )->request();
                break;
            case (isset($get["model"])):
                $args = array("model", "models");
                ( new ViewController( ...$args ) )->request();
                break;
            case (isset($get["xmlEAValidatorReport"])):
                $args = array("validationReport", "models");
                ( new ViewController( ...$args ) )->request();
                break;
            case (isset($get["projectResults"])):
                $args = array("projectResults", "projects");
                ( new ViewController( ...$args ) )->request();
                break;
            /**
             * Calculators
             */
            case (isset($_GET["newCalculator"])
                || isset($_GET['calculatorUploadInvalidFileExtension'])
                || isset($_GET['calculatorUploadNoFile'])
                || isset($_GET['calculatorUploadNoProject'])
                || isset($_GET['calculatorExists'])):
                $args = array("newCalculator", "calculators");
                ( new ViewController( ...$args ) )->request();
                break;
            case (isset($_GET["calculators"])
                || isset($_GET['changeCalculatorAccept'])):
                $args = array("calculators", "calculators");
                ( new ViewController( ...$args ) )->request();
                break;
        endswitch;
    }

}