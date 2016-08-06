<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 06-Aug-16
 * Time: 22:38
 */

namespace app\enterpriseArchitecture;

use app\enterpriseArchitecture\IOXMLEAModel;
use app\enterpriseArchitecture\IOXMLModelParser;
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
                $xmlFile        = Library::path( $_SERVER['DOCUMENT_ROOT'] . '/web/files/xml_models_tmp/' . $modelHash . '.' . $modelExt );
                $parsedClasses  = ( new IOXMLModelParser( $xmlFile ) )->parseXMLClasses();
            endif;

            foreach( $parsedClasses as $parsedClass ):

                if( $parsedClass['name'] === $this->attributeType ):
                    return($parsedClass['type']);
                    break;
                endif;

            endforeach;

            return ($parsedClasses);

        }
    }

endif;