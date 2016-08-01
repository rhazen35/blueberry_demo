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

    public function login()
    {
        include "views/_template/login.phtml";
    }

    public function header()
    {
        include "views/_template/header.phtml";
    }

    public function content()
    {

    }

    public function footer()
    {
        include "views/_template/footer.phtml";
    }

    public function pageScripts( array $pageScripts = array() )
    {

        foreach( $pageScripts as $pageScript ):

            require_once APPLICATION_PATH . Lib::path( "views/pageScripts/" . $pageScript . ".phtml" );

        endforeach;

    }

}