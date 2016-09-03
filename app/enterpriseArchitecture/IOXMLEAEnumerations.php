<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 07-Aug-16
 * Time: 14:43
 */

namespace app\enterpriseArchitecture;

if( !class_exists( "IOXMLEAEnumerations" ) ):

    class IOXMLEAEnumerations
    {

        protected $type;
        protected $enumeration;

        /**
         * IOXMLEAEnumerations constructor.
         * @param $type
         * @param $enumeration
         */
        public function __construct($type, $enumeration )
        {
            $this->type         = $type;
            $this->enumeration  = $enumeration;
        }

        /**
         * @param $params
         * @return bool
         */
        public function request($params )
        {
            switch( $this->type ):
                case"getEnumerations":
                    return( $this->getEnumerations( $params ) );
                    break;
            endswitch;
        }

        /**
         * @param $params
         * @return bool
         */
        private function getEnumerations($params )
        {
            $model      = ( new IOXMLEAModel( $params['model_id'] ) )->getModel();
            $modelHash  = ( !empty( $model['hash'] ) ? $model['hash'] : "" );
            $modelExt   = ( !empty( $model['ext'] ) ? $model['ext'] : "" );

            if( !empty( $modelHash ) && !empty( $modelExt ) ):
                $xmlFile        = 'web/files/xml_models/' . $modelHash . '.' . $modelExt;
                $parsedClasses  = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses();

                foreach( $parsedClasses as $parsedClass ):

                    $classType = ( isset( $parsedClass['type'] ) ? $parsedClass['type'] : "" );
                    $className = ( isset( $parsedClass['name'] ) ? $parsedClass['name'] : "" );

                    if( !empty( $classType ) && $classType === "uml:Enumeration" && $className === $this->enumeration ):
                        $selectOptions = $parsedClass['attributes'];
                        return($selectOptions);
                    endif;

                endforeach;

            else:
                return( false );
            endif;
        }

    }

endif;