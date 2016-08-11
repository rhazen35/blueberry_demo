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
        protected $allowedRequests = ['buildRelations'];

        /**
         * IOXMLEAInheritance constructor.
         * @param $type
         * @param $modelId
         */
        public function __construct($type, $modelId )
        {
            $this->type    = $type;
            $this->modelId = $modelId;
        }

        /**
         * @return array
         */
        public function request()
        {
            if( !in_array( $this->type, $this->allowedRequests ) ):
                return("Instantiation aborted! request type no allowed.");
            else:
                switch( $this->type ):
                    case"buildRelations":
                        return( $this->buildRelationsStructure() );
                        break;
                endswitch;
            endif;
        }

        /**
         * @param $action
         * @return array|bool|mixed
         */
        private function extractModelDataFromFile($action )
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
            /**
             * Return the right result based on the action.
             */
            switch( $action ):
                case"elements":
                    return( $elements = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses() );
                    break;
                case"elementNames":
                    $elements = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses();
                    $params['elements'] = $elements;
                    return( $elementNames = ( new IOXMLEAScreenFactory( "extractElementNames", $modelId = null ) )->request( $params ) );
                    break;
                case"connectors":
                    return( $connectors = ( new IOXMLEAModelParser( $xmlFile ) )->parseConnectors() );
                    break;
                default:
                    return( false );
                    break;
            endswitch;
        }

        /**
         * @return array
         *
         * Determine the relations for each element.
         * Specify the name, idref and isAbstract.
         * Specify the relation kind(parent, child or both), sub or super type or both, and super type's name.
         *
         */
        private function buildRelations()
        {
            /**
             * Collect all data and start building relations.
             */
            $elements              = $this->extractModelDataFromFile( "elements" );
            $elementNames          = $this->extractModelDataFromFile( "elementNames" );
            $connectors            = $this->extractModelDataFromFile( "connectors" );
            $totalElementsNames    = count( $elementNames );
            $totalConnectors       = count( $connectors['connectors'] );

            $relationArray = array();
            if( $totalElementsNames > 0 ):
                /**
                 * Loop trough each element.
                 */
                for( $i = 0; $i < $totalElementsNames; $i++ ):
                    /**
                     * Add name, id, and isAbstract.
                     */
                    $elementType     = $elements[$elementNames[$i]]['type'];
                    $elementName     = $elements[$elementNames[$i]]['name'];
                    $elementID       = $elements[$elementNames[$i]]['idref'];
                    $elementAbstract = $elements[$elementNames[$i]]['abstract'];
                    $elementRoot     = $elements[$elementNames[$i]]['root'];
                    /**
                     * Get the links and split them up
                     * target, source
                     */
                    $elementLinks             = ( !empty( $elements[$elementNames[$i]]['links'] ) ? $elements[$elementNames[$i]]['links'] : "" );
                    $totalAggregationLinks    = ( !empty( $elementLinks['aggregation'] ) ?  count( $elementLinks['aggregation'] ) : "" );
                    $totalGeneralizationLinks = ( !empty( $elementLinks['generalization'] ) ?  count( $elementLinks['generalization'] ) : "" );

                    $relationArray[$elementNames[$i]]['type']      = $elementType;
                    $relationArray[$elementNames[$i]]['name']      = $elementName;
                    $relationArray[$elementNames[$i]]['id']        = $elementID;
                    $relationArray[$elementNames[$i]]['abstract']  = $elementAbstract;
                    $relationArray[$elementNames[$i]]['root']      = $elementRoot;

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
                                    $parent = $element['name'];
                                endif;
                                /**
                                 * Multiplicity
                                 */
                                for( $l = 0; $l < $totalConnectors; $l++ ):
                                    if( $elementID === $connectors['connectors']['connector'.($l+1)]['source']['idref'] ):
                                        $parent = ( !empty( $parent ) ? $parent : "" );
                                        if( $connectors['connectors']['connector'.($l+1)]['target']['name'] === $parent ):
                                            $relationArray[$elementNames[$i]]['multiplicity'] = $connectors['connectors']['connector'.($l+1)]['labels']['multiplicity_source'];
                                        endif;
                                        break;
                                    endif;
                                endfor;

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

        /**
         * @return array
         */
        private function buildRelationsStructure()
        {
            /**
             * Get the relations and the element names array
             */
            $relations         = $this->buildRelations();
            $elementNames      = $this->extractModelDataFromFile( "elementNames" );

            $totalElementNames = count( $elementNames );

            for( $i = 0; $i < $totalElementNames; $i++ ):
                foreach( $relations as $relation ):
                    /**
                     * Specify the aggregations and generalizations and count them.
                     */
                    $aggregations         = ( !empty( $relation['aggregations'] ) ? $relation['aggregations'] : "" );
                    $generalizations      = ( !empty( $relation['generalizations'] ) ? $relation['generalizations'] : "" );
                    $totalAggregations    = count( $aggregations );
                    $totalGeneralizations = count( $generalizations );
                    /**
                     * Aggregation check.
                     */
                    for( $j = 0; $j < $totalAggregations; $j++ ):
                        if( !empty( $aggregations['aggregation'.($j+1)] ) ):
                            if( $relation['name'] === $aggregations['aggregation'.($j+1)]['child'] ):
                                $relations[$relation['name']]['isChild'] = true;
                            elseif( $relation['name'] === $aggregations['aggregation'.($j+1)]['parent'] ):
                                $relations[$relation['name']]['isParent'] = true;
                            endif;
                        endif;
                    endfor;
                    /**
                     * Generalizations check
                     */
                    for( $j = 0; $j < $totalGeneralizations; $j++ ):
                        if( !empty( $generalizations['generalization'.($j+1)] ) ):
                            if( $relation['name'] === $generalizations['generalization'.($j+1)]['sub_type'] ):
                                $relations[$relation['name']]['isSubType'] = true;
                                $relations[$relation['name']]['super_type'] = $generalizations['generalization'.($j+1)]['super_type'];
                            elseif( $relation['name'] === $generalizations['generalization'.($j+1)]['super_type'] ):
                                $relations[$relation['name']]['isSuperType'] = true;
                            endif;
                        endif;
                    endfor;

                endforeach;
            endfor;
            /**
             * Return the relations
             */
            return( $relations );
        }

    }

endif;