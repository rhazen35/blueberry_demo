<?php

namespace app\core;

use app\core\Library as Lib;

if( !class_exists( "Application" ) ):

    class Application
    {

        public function launch()
        {
            if( $_SERVER['REQUEST_METHOD'] === "POST"):
               require( $this->loadHandler() );
            else:
                require( $this->loadApp() );
            endif;
        }
        /**
         * @return string
         */
        private function loadApp()
        {
            return( "web/common/default.phtml" );
        }
        /**
         * @return string
         */
        private function loadHandler()
        {
            $prefix = "app". DIRECTORY_SEPARATOR ."handlers". DIRECTORY_SEPARATOR;
            $path   = (isset($_POST['path']) ? $_POST['path'] : "");
            $attr   = (isset($_POST['attr']) ? $_POST['attr'] : "");
            $ext    = ".php";

            return( Lib::path( $prefix.$path.DIRECTORY_SEPARATOR.$attr.$ext ) );
        }
    }

endif;