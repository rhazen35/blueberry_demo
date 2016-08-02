<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 21-Jul-16
 * Time: 23:14
 */

namespace app\enterpriseArchitecture;

if( !class_exists( "IOXMLPrimitiveTypes" ) ):

    class IOXMLPrimitiveTypes
    {

        protected $dataType;
        protected $value;

        public function __construct( $dataType, $value )
        {
            $this->dataType = $dataType;
            $this->value    = $value;
        }

        public function validate()
        {

            switch( $this->dataType ):

                case"String":
                    return( is_string( (string) $this->value ) );
                    break;
                case"Geslachten":

                    break;

            endswitch;

        }

    }

endif;