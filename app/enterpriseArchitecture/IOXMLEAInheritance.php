<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 09-Aug-16
 * Time: 10:48
 */

namespace app\enterpriseArchitecture;

use app\core\Library;

if( !class_exists( "IOXMLEAInheritance" ) ):

    class IOXMLEAInheritance
    {
        protected $type;
        protected $modelId;

        public function __construct( $type, $modelId )
        {
            $this->type    = $type;
            $this->modelId = $modelId;
        }

        public function request()
        {
            switch( $this->type ):
                case"buildRelations":
                    return( $this->buildRelations() );
                    break;
            endswitch;
        }

        private function buildRelations()
        {
            $model      = ( new IOXMLEAModel( $this->modelId ) )->getModel();
            $modelHash  = ( !empty( $model['hash'] ) ? $model['hash'] : "" );
            $modelExt   = ( !empty( $model['ext'] ) ? $model['ext'] : "" );

            if( !empty( $modelHash ) && !empty( $modelExt ) ):
                $xmlFile            = Library::path("web/files/xml_models_tmp/" . $modelHash . '.' . $modelExt);
                $parsedClasses      = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses();
                $parsedConnectors   = ( new IOXMLEAModelParser( $xmlFile ) )->parseConnectors();

                $totalParsedClasses    = count( $parsedClasses );
                $totalParsedConnectors = count( $parsedConnectors );

                $relationArray = array();

                if( $totalParsedClasses > 0 ):

                    for( $i = 0; $i < $totalParsedClasses; $i++ ):

                        $classID = $parsedClasses[$i]['idref'];

                        $relationArray['id'] = $classID;

                    endfor;

                endif;

                return( $relationArray );
            endif;
        }


    }

endif;