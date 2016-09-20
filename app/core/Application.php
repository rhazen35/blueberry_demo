<?php

namespace app\core;

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

        private function loadApp()
        {
            return( "web/common/default.phtml" );
        }

        private function loadHandler()
        {
            $prefix = "app". DIRECTORY_SEPARATOR ."handlers". DIRECTORY_SEPARATOR;
            $path   = (isset($_POST['path']) ? $_POST['path'] : "");
            $attr   = (isset($_POST['attr']) ? $_POST['attr'] : "");
            $ext    = ".php";

            return( $prefix.$path.DIRECTORY_SEPARATOR.$attr.$ext );
        }
    }

endif;