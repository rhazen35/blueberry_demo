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
                    return( $this->buildRelationsStructure() );
                    break;
            endswitch;
        }

        private function buildRelationsStructure()
        {
            $relations         = $this->buildRelations();
            $elementNames      = $this->extractModelDataFromFile( "elementNames" );

            $totalElementNames = count( $elementNames );

            for( $i = 0; $i < $totalElementNames; $i++ ):
                foreach( $relations as $relation ):
                    $aggregations         = ( !empty( $relation['aggregations'] ) ? $relation['aggregations'] : "" );
                    $generalizations      = ( !empty( $relation['generalizations'] ) ? $relation['generalizations'] : "" );
                    $totalAggregations    = count( $aggregations );
                    $totalGeneralizations = count( $generalizations );

                    for( $j = 0; $j < $totalAggregations; $j++ ):
                        if( !empty( $aggregations['aggregation'.($j+1)] ) ):
                            if( $relation['name'] === $aggregations['aggregation'.($j+1)]['child'] ):
                                $relations[$relation['name']]['isChild'] = true;
                            elseif( $relation['name'] === $aggregations['aggregation'.($j+1)]['parent'] ):
                                $relations[$relation['name']]['isParent'] = true;
                            endif;
                        endif;
                    endfor;

                    for( $j = 0; $j < $totalGeneralizations; $j++ ):
                        if( !empty( $generalizations['generalization'.($j+1)] ) ):
                            if( $relation['name'] === $generalizations['generalization'.($j+1)]['sub_type'] ):
                                $relations[$relation['name']]['isSubtype'] = true;
                                $relations[$relation['name']]['super_type'] = $generalizations['generalization'.($j+1)]['super_type'];
                            elseif( $relation['name'] === $generalizations['generalization'.($j+1)]['super_type'] ):
                                $relations[$relation['name']]['isSuperType'] = true;
                            endif;
                        endif;
                    endfor;

                endforeach;
            endfor;

            return( $relations );
        }

        private function extractModelDataFromFile( $action )
        {

            $model      = ( new IOXMLEAModel( $this->modelId ) )->getModel();
            $modelHash  = ( !empty( $model['hash'] ) ? $model['hash'] : "" );
            $modelExt   = ( !empty( $model['ext'] ) ? $model['ext'] : "" );

            if( !empty( $modelHash ) && !empty( $modelExt ) ):

                /**
                 * Get the parsed classes.
                 */
                $xmlFile = Library::path("web/files/xml_models_tmp/" . $modelHash . '.' . $modelExt);

            else:
                return( false );
            endif;

            switch( $action ):
                case"elements":
                    return( $elements = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses() );
                    break;
                case"elementNames":
                    $elements = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses();
                    return( $elementNames = ( new IOXMLEAScreenFactory( "" ) )->extractElementNames( $elements ) );
                    break;
                case"connectors":
                    return( $connectors = ( new IOXMLEAModelParser( $xmlFile ) )->parseConnectors() );
                    break;
                default:
                    return( false );
                    break;
            endswitch;


        }

        private function buildRelations()
        {
            /**
             * Determine the relations for each class.
             * Specify the relation kind.
             *
             * Collect all data and start building relations.
             */
            $elements              = $this->extractModelDataFromFile( "elements" );
            $elementNames          = $this->extractModelDataFromFile( "elementNames" );
            $connectors            = $this->extractModelDataFromFile( "connectors" );
            $totalElementsNames    = count( $elementNames );

            $relationArray = array();
            if( $totalElementsNames > 0 ):
                /**
                 * Loop trough each element.
                 */
                for( $i = 0; $i < $totalElementsNames; $i++ ):
                    /**
                     * Add name, id, and isAbstract.
                     */
                    $elementName     = $elements[$elementNames[$i]]['name'];
                    $elementID       = $elements[$elementNames[$i]]['idref'];
                    $elementAbstract = $elements[$elementNames[$i]]['abstract'];
                    /**
                     * Get the links and split them up
                     * target, source
                     */
                    $elementLinks             = ( !empty( $elements[$elementNames[$i]]['links'] ) ? $elements[$elementNames[$i]]['links'] : "" );
                    $totalAggregationLinks    = ( !empty( $elementLinks['aggregation'] ) ?  count( $elementLinks['aggregation'] ) : "" );
                    $totalGeneralizationLinks = ( !empty( $elementLinks['generalization'] ) ?  count( $elementLinks['generalization'] ) : "" );

                    $relationArray[$elementNames[$i]]['name']      = $elementName;
                    $relationArray[$elementNames[$i]]['id']        = $elementID;
                    $relationArray[$elementNames[$i]]['abstract']  = $elementAbstract;
                    /**
                     * Aggregation
                     * Add the parent and child
                     */
                    for( $j = 0; $j < $totalAggregationLinks; $j++ ):
                        if( !empty( $elementLinks['aggregation'] ) && !empty( $elementLinks['aggregation']['link'.($j+1)] ) ):
                            $relationArray[$elementNames[$i]]['aggregations']['aggregation'.($j+1)] = $elementLinks['aggregation']['link'.($j+1)];
                            foreach( $elements as $element ):
                                if( $element['idref'] === $elementLinks['aggregation']['link'.($j+1)]['source'] ):
                                    $relationArray[$elementNames[$i]]['aggregations']['aggregation'.($j+1)]['child'] = $element['name'];
                                endif;
                                if( $element['idref'] === $elementLinks['aggregation']['link'.($j+1)]['target'] ):
                                    $relationArray[$elementNames[$i]]['aggregations']['aggregation'.($j+1)]['parent'] = $element['name'];
                                endif;
                            endforeach;
                        endif;
                    endfor;
                    /**
                     * Generalization
                     * Add the super type and sup type
                     */
                    for( $k = 0; $k < $totalGeneralizationLinks; $k++ ):
                        if( !empty( $elementLinks['generalization'] ) && !empty( $elementLinks['generalization']['link'.($k+1)] ) ):
                            $relationArray[$elementNames[$i]]['generalizations']['generalization'.($k+1)] = $elementLinks['generalization']['link'.($k+1)];
                            foreach( $elements as $element ):
                                $elementName = $element['name'];
                                if( $element['idref'] === $elementLinks['generalization']['link'.($k+1)]['source'] ):
                                    $relationArray[$elementNames[$i]]['generalizations']['generalization'.($k+1)]['sub_type'] = $elementName;
                                endif;
                                if( $element['idref'] === $elementLinks['generalization']['link'.($k+1)]['target'] ):
                                    $relationArray[$elementNames[$i]]['generalizations']['generalization'.($k+1)]['super_type'] = $elementName;
                                endif;
                            endforeach;
                        endif;
                    endfor;

                endfor;

            endif;

            return( $relationArray );
        }


    }

endif;