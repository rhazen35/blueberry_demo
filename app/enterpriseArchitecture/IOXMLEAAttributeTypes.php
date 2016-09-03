<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 06-Aug-16
 * Time: 22:38
 */

namespace app\enterpriseArchitecture;

use app\core\Library;

if( !class_exists( "IOXMLEAAttributeTypes" ) ):

    class IOXMLEAAttributeTypes
    {

        protected $attributeType;
        protected $model;

        public function __construct( $model, $attributeType )
        {
            $this->model = $model;
            $this->attributeType = $attributeType;
        }

        public function fieldType()
        {

            $modelData      = ( new IOXMLEAModel( $this->model ) )->getModel();
            $modelHash      = ( !empty( $modelData['hash'] ) ? $modelData['hash'] : "" );
            $modelExt       = ( !empty( $modelData['ext'] ) ? $modelData['ext'] : "" );

            if( !empty( $modelHash ) && !empty( $modelExt ) ):
                $xmlFile        = Library::path( $_SERVER['DOCUMENT_ROOT'] . '/web/files/xml_models/' . $modelHash . '.' . $modelExt );
                $parsedClasses  = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses();
            endif;

            if( !empty( $parsedClasses ) ):
                foreach( $parsedClasses as $parsedClass ):
                    if( $parsedClass['name'] === $this->attributeType ):
                        $parsedClassType = str_replace( "uml:", "", $parsedClass['type'] );
                        return($parsedClassType);
                        break;
                    endif;
                endforeach;
                return ($parsedClasses);
            endif;

        }
    }

endif;