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
            /**
             * Determine the relations for each class.
             * Specify the relation kind
             */
            $model      = ( new IOXMLEAModel( $this->modelId ) )->getModel();
            $modelHash  = ( !empty( $model['hash'] ) ? $model['hash'] : "" );
            $modelExt   = ( !empty( $model['ext'] ) ? $model['ext'] : "" );

            if( !empty( $modelHash ) && !empty( $modelExt ) ):

                /**
                 * Get the parsed classes, element names and connectors.
                 */
                $xmlFile            = Library::path("web/files/xml_models_tmp/" . $modelHash . '.' . $modelExt);
                $parsedClasses      = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses();
                $elementNames       = ( new IOXMLEAScreenFactory( "" ) )->extractElementNames( $parsedClasses );
                $parsedConnectors   = ( new IOXMLEAModelParser( $xmlFile ) )->parseConnectors();

                $totalParsedClasses    = count( $parsedClasses );
                $totalClassesNames     = count( $elementNames );
                $totalParsedConnectors = count( $parsedConnectors['connectors'] );

                $relationArray = array();

                if( $totalClassesNames > 0 ):

                    for( $i = 0; $i < $totalClassesNames; $i++ ):

                        $className       = $parsedClasses[$elementNames[$i]]['name'];
                        $classID         = $parsedClasses[$elementNames[$i]]['idref'];
                        $classAbstract   = $parsedClasses[$elementNames[$i]]['abstract'];

                        $classLinks               = ( !empty( $parsedClasses[$elementNames[$i]]['links'] ) ? $parsedClasses[$elementNames[$i]]['links'] : "" );
                        $totalAggregationLinks    = ( !empty( $classLinks['aggregation'] ) ?  count( $classLinks['aggregation'] ) : "" );
                        $totalGeneralizationLinks = ( !empty( $classLinks['generalization'] ) ?  count( $classLinks['generalization'] ) : "" );

                        $relationArray[$i]['name']      = $className;
                        $relationArray[$i]['id']        = $classID;

                        for( $j = 0; $j < $totalAggregationLinks; $j++ ):
                            if( !empty( $classLinks['aggregation'] ) && !empty( $classLinks['aggregation']['link'.($j+1)] ) ):
                                $relationArray[$i]['aggregation'.($j+1)] = $classLinks['aggregation']['link'.($j+1)];
                                foreach( $parsedClasses as $parsedClass ):
                                    if( $parsedClass['idref'] === $classLinks['aggregation']['link'.($j+1)]['source'] ):
                                        $relationArray[$i]['aggregation'.($j+1)]['parent'] = $parsedClass['name'];
                                    endif;
                                    if( $parsedClass['idref'] === $classLinks['aggregation']['link'.($j+1)]['target'] ):
                                        $relationArray[$i]['aggregation'.($j+1)]['child'] = $parsedClass['name'];
                                    endif;
                                endforeach;
                            endif;
                        endfor;

                        for( $k = 0; $k < $totalGeneralizationLinks; $k++ ):
                            if( !empty( $classLinks['generalization'] ) && !empty( $classLinks['generalization']['link'.($k+1)] ) ):
                                $relationArray[$i]['generalization'.($k+1)] = $classLinks['generalization']['link'.($k+1)];
                                foreach( $parsedClasses as $parsedClass ):
                                    if( $parsedClass['idref'] === $classLinks['generalization']['link'.($k+1)]['source'] ):
                                        $relationArray[$i]['generalization'.($k+1)]['super_type'] = $parsedClass['name'];
                                    endif;
                                    if( $parsedClass['idref'] === $classLinks['generalization']['link'.($k+1)]['target'] ):
                                        $relationArray[$i]['generalization'.($k+1)]['sub_type'] = $parsedClass['name'];
                                    endif;
                                endforeach;
                            endif;
                        endfor;

                    endfor;

                endif;

                return( $relationArray );
            endif;
        }


    }

endif;