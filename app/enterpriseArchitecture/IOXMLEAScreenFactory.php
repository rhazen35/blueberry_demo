<?php

namespace app\enterpriseArchitecture;

if( !class_exists( "IOXMLEAScreenFactory" ) ):

    class IOXMLEAScreenFactory
    {
        protected $xmlModelId;
        /**
         * IOXMLEAScreenFactory constructor.
         * @param $xmlModelId
         * @param $type
         */
        public function __construct( $type, $xmlModelId )
        {
            $this->type       = $type;
            $this->xmlModelId = $xmlModelId;
        }
        /**
         * @param $params
         * @return array|void
         */
        public function request( $params )
        {
            switch( $this->type ):
                case"extractElementNames":
                    return( $this->extractElementNames( $params ) );
                    break;
                case"extractAndOrderElements":
                    return( $this->extractAndOrderElements() );
                    break;
                case"buildElementIntro":
                    return( $this->buildElementIntro() );
                    break;
                case"buildElement":
                    return( $this->buildElement( $params ) );
                    break;
                case"buildSuperElement":
                    return( $this->buildSuperElement( $params ) );
                    break;
                case"buildOperations":
                    return( $this->buildOperations( $params ) );
                    break;
                case"showElementView":
                    return( $this->showElementView( $params ) );
                    break;
            endswitch;
        }
        /**
         * @param $params
         * @return array
         */
        private function extractElementNames( $params )
        {
            $parsedElements = $params['elements'];
            $elementNames   = array();
            foreach( $parsedElements as $parsedElement ):
                if( !empty( $parsedElement['name'] ) ):
                    $elementNames[] = $parsedElement['name'];
                endif;
            endforeach;
            return($elementNames);
        }
        /**
         * @param $a
         * @param $b
         * @return int
         */
        private function sortByPrintOrder( $a, $b )
        {
            if( !empty( $a['printOrder'] ) && !empty( $b['printOrder'] ) ):
                return( strnatcmp( $a['printOrder'], $b['printOrder'] ) );
            endif;
        }
        /**
         * @return array
         */
        private function extractAndOrderElements()
        {
            $modelData          = ( new IOXMLEAModel( $this->xmlModelId ) )->getModel();
            $relations          = ( new IOXMLEAInheritance( "buildRelations", $this->xmlModelId ) )->request();
            $orderedElements    = array();
            $highestOrder       = 0;

            if (!empty($modelData)):
                if (!empty($modelData['hash'])):

                    $xmlFile        = 'web/files/xml_models/' . $modelData['hash'] . '.' . $modelData['ext'];
                    $parsedElements = ( new IOXMLEAModelParser($xmlFile))->parseXMLClasses();
                    $params         = array( "elements" => $parsedElements );
                    $elementNames   = $this->extractElementNames ( $params );

                    $i = 0;
                    foreach( $elementNames as $elementName ):
                        /**
                         * Relation data collection
                         */
                        $relationName         = ( !empty( $relations[$elementName]['name'] ) ? $relations[$elementName]['name'] : "" );
                        $relationIsParent     = ( !empty( $relations[$elementName]['isParent'] ) ? $relations[$elementName]['isParent'] : "" );
                        $relationIsChild      = ( !empty( $relations[$elementName]['isChild'] ) ? $relations[$elementName]['isChild'] : "" );
                        $relationIsSuperType  = ( !empty( $relations[$elementName]['isSuperType'] ) ? $relations[$elementName]['isSuperType'] : "" );
                        $relationIsSubType    = ( !empty( $relations[$elementName]['isSubType'] ) ? $relations[$elementName]['isSubType'] : "" );
                        $relationMultiplicity = ( !empty( $relations[$elementName]['multiplicity'] ) ? $relations[$elementName]['multiplicity'] : "" );
                        $relationMultiHead    = ( !empty( $relations[$elementName]['multiplicity_head'] ) ? $relations[$elementName]['multiplicity_head'] : "" );
                        /**
                         * Super and sub types
                         */
                        $superTypes           = ( !empty(  $relations[$elementName]['super_types'] ) ?  $relations[$elementName]['super_types'] : "" );
                        $subTypes             = ( !empty(  $relations[$elementName]['sub_types'] ) ?  $relations[$elementName]['sub_types'] : "" );
                        $totalSuperTypes      = ( count( $superTypes ) );
                        $totalSubTypes        = ( count( $subTypes ) );
                        /**
                         * Element data collection
                         */
                        $element              = ( isset( $parsedElements[$elementName] ) && $parsedElements[$elementName]['type'] === "uml:Class" ? $parsedElements[$elementName] : "" );
                        $idref                = ( isset( $element['idref'] ) ? $element['idref'] : "" );
                        $root                 = ( isset( $element['root'] ) ? $element['root'] : false );
                        $abstract             = ( isset( $element['abstract'] ) ? $element['abstract'] : false );
                        $name                 = ( isset( $element['name'] ) ? $element['name'] : "" );
                        $tags                 = ( isset( $element['tags'] ) ? $element['tags'] : false );
                        $order                = ( isset( $tags['QR-PrintOrder']['order'] ) ? $tags['QR-PrintOrder']['order'] : "noPrint");
                        $excelTypes           = ( isset( $tags['QR-Excel subtypes'] ) ? $tags['QR-Excel subtypes'] : "");
                        $highestOrder         = ( $order !== "noPrint" ? $highestOrder + 1 : $highestOrder );
                        /**
                         * Element documentation, attributes, operations, target(collected by the matching connector, if available)
                         */
                        $elementDocumentation = ( isset( $element['documentation'] ) ? $element['documentation'] : "" );
                        $elementAttributes    = ( isset( $element['attributes'] ) ? $element['attributes'] : false );
                        $elementOperations    = ( isset( $element['operations'] ) ? $element['operations'] : "" );
                        /**
                         * Element target i.e. parent/super type
                         */
                        $target = $this->getMatchingConnector( $idref, "target" );
                        /**
                         * Only create element array if the element has a relation.
                         */
                        if( $name === $relationName ):
                            $orderedElements[$i]['model_id']             = $this->xmlModelId;
                            $orderedElements[$i]['name']                 = $name;
                            $orderedElements[$i]['idref']                = $idref;
                            $orderedElements[$i]['printOrder']           = $order;
                            $orderedElements[$i]['isRoot']               = $root;
                            $orderedElements[$i]['isAbstract']           = $abstract;
                            /**
                             * Get element multiplicity.
                             */
                            $orderedElements[$i]['multiplicity']         = $relationMultiplicity;
                            $orderedElements[$i]['header_txt']           = $relationMultiHead;
                            /**
                             * Get the parent, child, super type and sub type
                             */
                            $orderedElements[$i]['isParent']             = $relationIsParent;
                            $orderedElements[$i]['isChild']              = $relationIsChild;
                            $orderedElements[$i]['isSuperType']          = $relationIsSuperType;
                            $orderedElements[$i]['isSubType']            = $relationIsSubType;
                            $orderedElements[$i]['excelTypeLocation']    = $excelTypes;
                            /**
                             * Add the super type if the target is available
                             */
                            if( !empty( $target ) ):
                                $orderedElements[$i]['supertype']                   = array();
                                $orderedElements[$i]['supertype']['id']             = $target['id'];
                                $orderedElements[$i]['supertype']['type']           = $target['type'];
                                $orderedElements[$i]['supertype']['name']           = $target['name'];
                                $orderedElements[$i]['supertype']['ea_localId']     = $target['ea_localId'];
                                $orderedElements[$i]['supertype']['multiplicity']   = $target['multiplicity'];
                                $orderedElements[$i]['supertype']['aggregation']    = $target['aggregation'];

                                $targetClass    = $parsedElements[$target['name']];
                                $idref          = ( isset( $targetClass['idref'] ) ? $targetClass['idref'] : "" );
                                $tags           = ( isset( $targetClass['tags'] ) ? $targetClass['tags'] : false );
                                $documentation  = ( isset( $targetClass['documentation'] ) ? $targetClass['documentation'] : "" );
                                $order          = ( isset( $tags['QR-PrintOrder'] ) ? $tags['QR-PrintOrder'] : "");
                                $attributes     = ( isset( $targetClass['attributes'] ) ? $targetClass['attributes'] : false );
                                $attributesTags = ( isset( $targetClass['attributes']['tags'] ) ? $targetClass['attributes']['tags'] : false );
                                $operations     = ( isset( $targetClass['operations'] ) ? $targetClass['operations'] : "" );
                                $labels         = ( isset( $targetClass['labels'] ) ? $targetClass['labels'] : "" );
                                $excelTypes     = ( isset( $targetClass['tags']['QR-Excel subtypes'] ) ? $targetClass['tags']['QR-Excel subtypes'] : "" );

                                $orderedElements[$i]['supertype']['idref']              = $idref;
                                $orderedElements[$i]['supertype']['order']              = $order;
                                $orderedElements[$i]['supertype']['documentation']      = $documentation;
                                $orderedElements[$i]['supertype']['excelTypeLocation']  = $excelTypes;
                                $orderedElements[$i]['supertype']['attributes']         = $attributes;
                                $orderedElements[$i]['supertype']['attributes']['tags'] = $attributesTags;
                                $orderedElements[$i]['supertype']['operations']         = $operations;
                                $orderedElements[$i]['supertype']['labels']             = $labels;
                                $orderedElements[$i]['supertype']['excelTypes']         = $excelTypes;
                            endif;
                            /**
                             * Select the sub types for each super type and add each super/sub combination to the array.
                             */
                            if( !empty( $superTypes ) ):
                                for( $j = 0; $j < $totalSuperTypes; $j++ ):
                                    if( !empty( $subTypes ) ):
                                        for( $k = 0; $k < $totalSubTypes; $k++ ):
                                            $orderedElements[$i]['super_types'][$superTypes['super_type'.($j+1)]]['sub'.($k+1)] = ( !empty( $subTypes['sub_type'.($k+1)] ) ? $subTypes['sub_type'.($k+1)] : "" );
                                        endfor;
                                    endif;
                                endfor;
                            endif;
                            /**
                             * Add form details.
                             */
                            $orderedElements[$i]['formDetails'] = array();
                            /**
                             * Element documentation.
                             */
                            $orderedElements[$i]['formDetails']['elementDocumentation'] = $elementDocumentation;
                            /**
                             * Element Attributes.
                             */
                            if( !empty( $elementAttributes ) ):
                                $orderedElements[$i]['formDetails']['elementAttributes'][$name] = ( !empty( $elementAttributes ) ? $this->extractAndOrderAttributes( $elementAttributes ) : "" );
                            endif;
                            /**
                             * Extract and order the super types, super attributes and super operations
                             */
                            if( !empty( $orderedElements[$i]['super_types'] ) ):
                                $superTypes = $orderedElements[$i]['super_types'];
                                foreach( $superTypes as $superType => $subTypes ):
                                    foreach( $subTypes as $subType ):
                                        foreach( $parsedElements as $parsedElement ):
                                            if( $parsedElement['name'] === $subType ):
                                                $subAttributes = ( !empty( $parsedElement['attributes'] ) ? $parsedElement['attributes'] : "" );
                                                $subOperations = ( !empty( $parsedElement['operations'] ) ? $parsedElement['operations'] : "" );
                                                if( !empty( $subAttributes ) ):
                                                    $orderedElements[$i]['formDetails']['elementAttributes'][$subType] = ( !empty( $subAttributes ) ? $this->extractAndOrderAttributes( $subAttributes ) : "" );
                                                endif;
                                                if( !empty( $subOperations ) ):
                                                    $orderedElements[$i]['formDetails']['elementOperations'][$subType] = ( !empty( $subOperations ) ? $this->extractAndOrderOperations( $subOperations ) : "" );
                                                endif;
                                            endif;
                                        endforeach;
                                    endforeach;
                                endforeach;
                            endif;
                            /**
                             * Element Operations
                             */
                            if( !empty( $elementOperations ) ):
                                $orderedElements[$i]['formDetails']['elementOperations'][$name] = ( !empty( $elementOperations ) ? $this->extractAndOrderOperations( $elementOperations ) : "" );
                            endif;
                        endif;
                        /**
                         * Increment looping trough the elements.
                         */
                        $i++;
                    endforeach;
                endif;
            endif;
            /**
             * Sort all elements by print order, add the highest order and return the extracted and ordered elements.
             */
            usort( $orderedElements, array( $this,'sortByPrintOrder' ) );
            $orderedElements['highest_order'] = $highestOrder;
            return( $orderedElements );
        }
        /**
         * @param $idref
         * @return array
         */
        private function getMatchingConnector( $idref, $type )
        {
            $modelData          = ( new IOXMLEAModel( $this->xmlModelId ) )->getModel();
            $xmlFile            =  'web/files/xml_models/' . $modelData['hash'] . '.' . $modelData['ext'];
            $parsedConnectors   = ( new IOXMLEAModelParser( $xmlFile ) )->parseConnectors();
            $totalConnectors    = count( $parsedConnectors['connectors'] );

            if( $totalConnectors > 0 ):
                for( $j = 0; $j < $totalConnectors; $j++ ):
                    if( $idref === $parsedConnectors['connectors']['connector'.($j+1)]['source']['idref'] ):
                        if( $parsedConnectors['connectors']['connector'.($j+1)]['properties']['ea_type'] === "Generalization" ):
                            /**
                             * Extract the source.
                             */
                            $source                  = $parsedConnectors['connectors']['connector'.($j+1)]['source']['idref'];
                            $sourceName              = $parsedConnectors['connectors']['connector'.($j+1)]['source']['name'];
                            $sourceModelType         = $parsedConnectors['connectors']['connector'.($j+1)]['source']['type'];
                            $sourceModelEALocalId    = $parsedConnectors['connectors']['connector'.($j+1)]['source']['ea_localid'];
                            $sourceModeMultiplicity  = $parsedConnectors['connectors']['connector'.($j+1)]['source']['multiplicity'];
                            $sourceModelAggregation  = $parsedConnectors['connectors']['connector'.($j+1)]['source']['aggregation'];
                            $sourceArray             = array(
                                "id"            => $source,
                                "name"          => $sourceName,
                                "type"          => $sourceModelType,
                                "ea_localId"    => $sourceModelEALocalId,
                                "multiplicity"  => $sourceModeMultiplicity,
                                "aggregation"   => $sourceModelAggregation
                            );
                            /**
                             * Extract the target
                             */
                            $target                  = $parsedConnectors['connectors']['connector'.($j+1)]['target']['idref'];
                            $targetName              = $parsedConnectors['connectors']['connector'.($j+1)]['target']['name'];
                            $targetModelType         = $parsedConnectors['connectors']['connector'.($j+1)]['target']['type'];
                            $targetModelEALocalId    = $parsedConnectors['connectors']['connector'.($j+1)]['target']['ea_localid'];
                            $targetModeMultiplicity  = $parsedConnectors['connectors']['connector'.($j+1)]['target']['multiplicity'];
                            $targetModelAggregation  = $parsedConnectors['connectors']['connector'.($j+1)]['target']['aggregation'];
                            $labels                  = $parsedConnectors['connectors']['connector'.($j+1)]['labels'];
                            $targetArray             = array(
                                "id"            => $target,
                                "name"          => $targetName,
                                "type"          => $targetModelType,
                                "ea_localId"    => $targetModelEALocalId,
                                "multiplicity"  => $targetModeMultiplicity,
                                "aggregation"   => $targetModelAggregation,
                                "labels"        => $labels
                            );
                            /**
                             * Return array based on type.
                             */
                            if( $type === "source" ):
                                return( $sourceArray );
                            elseif( $type === "target" ):
                                return( $targetArray );
                            endif;
                            /**
                             * Break the loop if the idref matched and the ea_type equals Generalization.
                             */
                            break;
                        endif;
                    endif;
                endfor;
            endif;
        }
        /**
         * @param $operations
         * @return array
         */
        private function extractAndOrderOperations( $operations )
        {
            $operationsArray = array();
            $totalOperations = count( $operations );
            if( $totalOperations > 0 ):
                for( $i = 0; $i < $totalOperations; $i++ ):
                    $operationName          = ( !empty( $operations['operation'.($i+1)]['name'] ) ? $operations['operation'.($i+1)]['name'] : "" );
                    $operationDocumentation = ( !empty( $operations['operation'.($i+1)]['documentation'] ) ? $operations['operation'.($i+1)]['documentation'] : "" );
                    $operationTags          = ( !empty( $operations['operation'.($i+1)]['tags'] ) ? $operations['operation'.($i+1)]['tags'] : "" );
                    $totalTags              = count( $operationTags );
                    /**
                     * Operation name and documentation
                     */
                    $operationsArray[$operationName]['name']          = $operationName;
                    $operationsArray[$operationName]['documentation'] = $operationDocumentation;
                    /**
                     * Extract the operation tags
                     */
                    for( $j = 0; $j < $totalTags; $j++ ):
                        if( !empty( $operations['operation'.($i+1)]['tags'][$j] ) ):
                            $operationTagName    = ( !empty( $operations['operation'.($i+1)]['tags'][$j]['name'] ) ? $operations['operation'.($i+1)]['tags'][$j]['name'] : "" );
                            $operationTagCell    = ( !empty( $operations['operation'.($i+1)]['tags'][$j]['cell'] ) ? $operations['operation'.($i+1)]['tags'][$j]['cell'] : "" );
                            $operationTagOrder   = ( $operations['operation'.($i+1)]['tags'][$j]['name'] === "QR-PrintOrder" ? $operationTagCell : "" );
                            $operationTagFile    = ( !empty( $operations['operation'.($i+1)]['tags'][$j]['file'] ) ? $operations['operation'.($i+1)]['tags'][$j]['file'] : "" );
                            $operationTagTab     = ( !empty( $operations['operation'.($i+1)]['tags'][$j]['tab'] ) ? $operations['operation'.($i+1)]['tags'][$j]['tab'] : "" );

                            $operationsArray[$operationName]['printOrder'] = ( !empty( $operationTagOrder ) ? $operationTagOrder : "" );
                            /**
                             * Only add name, file, tab and cell if the operation is other then QR-PrintOrder.
                             */
                            if( $operationTagName !== "QR-PrintOrder" ):
                                $operationsArray[$operationName][$operationTagName]['name'] = $operationTagName;
                                $operationsArray[$operationName][$operationTagName]['file'] = $operationTagFile;
                                $operationsArray[$operationName][$operationTagName]['tab']  = $operationTagTab;
                                $operationsArray[$operationName][$operationTagName]['cell'] = $operationTagCell;
                            endif;
                        endif;
                    endfor;
                endfor;
                usort( $operationsArray, array( $this,'sortByPrintOrder' ) );
            endif;
            return( $operationsArray );
        }
        /**
         * @param $attributes
         * @return array
         */
        private function extractAndOrderAttributes($attributes )
        {
            $attributesArray = array();
            if( !empty( $attributes ) ):
                foreach( $attributes as $attribute => $array ):
                    $attributesArray[$attribute]['name'] = $attribute;
                    $attributeDocumentation = $array['documentation'];
                    $attributeDataType      = $array['data_type'];
                    $attributeInitialValue  = $array['initialValue'];
                    /**
                     * Add data type, initial value and documentation.
                     */
                    $attributesArray[$attribute]['data_type']     = $attributeDataType;
                    $attributesArray[$attribute]['initialValue']  = $attributeInitialValue;
                    $attributesArray[$attribute]['documentation'] = $attributeDocumentation;
                    /**
                     * Add the attribute tags
                     */
                    $tags      = ( !empty( $array['tags'] ) ? $array['tags'] : "" );
                    $totalTags = count( $tags );
                    if( $totalTags > 0 ):
                        for( $i = 0; $i < $totalTags; $i++ ):
                            $tagName = ( !empty( $tags[$i]['name'] ) ? $tags[$i]['name'] : "" );
                            if( $tagName === "QR-PrintOrder" ):
                                $attributesArray[$attribute]['printOrder'] = $tags[$i]['cell'];
                            else:
                                $attributesArray[$attribute]['file'] = ( !empty( $tags[$i]['file'] ) ? $tags[$i]['file'] : "" );
                                $attributesArray[$attribute]['tab']  = ( !empty( $tags[$i]['tab'] ) ? $tags[$i]['tab'] : "" );
                                $attributesArray[$attribute]['cell'] = ( !empty( $tags[$i]['cell'] ) ? $tags[$i]['cell'] : "" );
                            endif;
                        endfor;
                    endif;
                endforeach;
                usort( $attributesArray, array( $this,'sortByPrintOrder' ) );
            endif;
            return( $attributesArray );
        }
        /**
         * #####################################################################
         * ##              START OF ELEMENT AND FORM BUILD                    ##
         * #####################################################################
         *
         * @return string
         */
        private function buildElementIntro()
        {
            $element     = $this->xmlModelId;
            $elementName = ( !empty( $element['name'] ) ? $element['name'] : "" );
            $title       = $elementName;
            $intro       = ( isset( $element['formDetails']['elementDocumentation'] ) ? $element['formDetails']['elementDocumentation'] : "" );
            $html        = '<div class="elementIntro">';
            if( !empty( $element['formDetails'] ) ):
                $formOperations      = $element['formDetails']['elementOperations'][$elementName];
                $html               .= '<div class="elementIntro-title">'. $title .'</div>';
                $html               .= '<div class="elementIntro-txt"><p>'. $intro .'<p></div>';
                $totalFormOperations = count( $formOperations );
                if( $totalFormOperations > 0 ):
                    for( $i = 0; $i < $totalFormOperations; $i++ ):
                        $html .= '<div class="elementIntro-subTitle">'. $formOperations[$i]['name'] .'</div>';
                        $html .= '<div class="elementIntro-subIntro"><p>'. $formOperations[$i]['documentation'] .'</p></div>';
                    endfor;
                    $html .= '<div class="elementIntro-next"><a href="' . APPLICATION_HOME . '?model&page=' . ( $element['printOrder'] + 1 ) . '" class="button">Next</a></div>';
                endif;
            endif;
            $html .= '</div>';
            return( $html );
        }
        /**
         * @param $params
         * @return string
         */
        private function buildElement( $params )
        {
            $element                = $this->xmlModelId;
            $parsedElements         = $params['elements'];
            $elementName            = ( isset( $element['name'] ) ? $element['name'] : "" );
            $title                  = ( !empty( $element['header_txt'] ) ? $element['header_txt'] : ( !empty( $element['name'] ) ? $element['name'] : "" ) );
            $documentation          = ( isset( $element['formDetails']['elementDocumentation'] ) ? $element['formDetails']['elementDocumentation'] : "" );
            $multiplicity           = ( isset( $element['multiplicity'] ) ? $element['multiplicity'] : "" );
            $hasSuperTypes          = ( isset( $element['super_types'] ) ? $element['super_types'] : "" );
            $params['element_name'] = $elementName;
            $params['elements']     = $parsedElements;
            $params['multiplicity'] = $multiplicity;
            /**
             * Check if an element has super types, if so get their sub types results.
             */
            if( !empty( $hasSuperTypes ) ):
                $data = array();
                if( !empty( $hasSuperTypes[$elementName] ) ):
                    foreach( $hasSuperTypes[$elementName] as $hasSuperType ):
                        $params['element_name'] = $hasSuperType;
                        $params['elements']     = $parsedElements;
                        $params['multiplicity'] = $multiplicity;
                        $results[$hasSuperType] = ( new XMLDBController( "read" ) )->request( $params );
                        $totalResults           = count($results[$hasSuperType]);
                        if( $totalResults > 0 ):
                            for( $i = 0; $i < $totalResults; $i++ ):
                                if( !empty(  $results[$hasSuperType][$i]['id'] ) ):
                                    $results[$hasSuperType][$i]['name'] = $hasSuperType;
                                    $data[$hasSuperType]                =  $results[$hasSuperType];
                                endif;
                            endfor;
                        endif;
                    endforeach;
                endif;
            /**
             * Get the results when an element does not have super type(s)
             */
            else:
                $params['element_name'] = $elementName;
                $params['elements']     = $parsedElements;
                $params['multiplicity'] = $multiplicity;
                $data                   = ( new XMLDBController( "read" ) )->request( $params );
            endif;
            $html  = '<div class="element">';
            $html .= '<div class="element-title">'. $title .'</div>';
            $html .= '<div class="element-documentation"><p>'. $documentation .'</p></div>';

            if( !empty( $hasSuperTypes ) ):
                if( $multiplicity === "1..*" || $multiplicity === "0..*" || $multiplicity === "" ):
                    $params['element']      = $element;
                    $params['elements']     = $parsedElements;
                    $params['multiplicity'] = $multiplicity;
                    $html .= $this->buildSuperForm( $params );
                    if( !empty( $data ) ):
                        $html .= '<div class="element-submitted"></div>';
                        if( !empty( $hasSuperTypes[$elementName] ) ):
                            foreach( $hasSuperTypes[$elementName] as $hasSuperType ):
                                if( !empty( $data[$hasSuperType] ) ):
                                    $totalResults = count( $data[$hasSuperType] );
                                    $subTypeName  = $hasSuperType;
                                    $html        .= '<br><br><div class="element-subTitle"><p>' . $subTypeName . '</p></div>';
                                    for( $i = 0; $i < $totalResults; $i++ ):
                                        foreach( $parsedElements as $parsedElement ):
                                            if( $subTypeName === $parsedElement['name'] && $data[$hasSuperType][$i]['name'] === $hasSuperType ):
                                                $params['element']      = $parsedElement;
                                                $params['elements']     = $parsedElements;
                                                $params['result']       = $data[$hasSuperType][$i];
                                                $params['multiplicity'] = $multiplicity;
                                                $params['type']         = "advanced";
                                                $html .= $this->buildForm( $params );
                                            break;
                                            endif;
                                        endforeach;
                                    endfor;
                                endif;
                            endforeach;
                        endif;
                    endif;
                endif;
            /**
             * Create a normal form if the element does not have super types
             */
            else:
                $params['element']      = $element;
                $params['elements']     = $parsedElements;
                $params['result']       = $data;
                $params['multiplicity'] = $multiplicity;
                $params['type']         = "normal";
                $html .= $this->buildForm( $params );
                if( !empty( $data ) ):
                    if( $multiplicity === "1..*" || $multiplicity === "0..*" || $multiplicity === "" ):
                        $html .= '<div class="element-submitted"></div>';
                        foreach( $data as $result ):
                            $params['result']       = $result;
                            $params['type']         = "advanced";
                            $html .= $this->buildForm( $params );
                        endforeach;
                    endif;
                endif;
            endif;
            $html .= '</div>';
            return( $html );
        }
        /**
         * @param $params
         * @return string
         */
        private function buildSuperElement( $params )
        {
            $element         = $this->xmlModelId;
            $parsedElements  = $params['elements'];
            $elementName     = ( isset( $element['name'] ) ? $element['name'] : "" );
            $title           = ( !empty( $element['header_txt'] ) ? $element['header_txt'] : ( !empty( $element['name'] ) ? $element['name'] : "" ) );
            $documentation   = ( isset( $element['formDetails']['elementDocumentation'] ) ? $element['formDetails']['elementDocumentation'] : "" );
            $multiplicity    = ( isset( $element['multiplicity'] ) ? $element['multiplicity'] : "" );

            $params['element_name'] = $elementName;
            $params['elements']     = $parsedElements;
            $params['multiplicity'] = $multiplicity;

            $data  = ( new XMLDBController( "read" ) )->request( $params );

            $html  = '<div class="element-title">'. $title .'</div>';
            $html .= '<div class="element-documentation"><p>'. $documentation .'</p></div>';

            $params['element']      = $element;
            $params['elements']     = $parsedElements;
            $params['result']       = $data;
            $params['multiplicity'] = $multiplicity;
            $params['type']         = "normal";

            $html .= $this->buildForm( $params);
            return( $html );
        }
        /**
         * Build a normal(standard) form.
         * @param $params
         * @return string
         */
        private function buildForm ( $params )
        {
            $parsedElements     = $params['elements'];
            $element            = $params['element'];
            $result             = $params['result'];
            $multiplicity       = $params['multiplicity'];
            $type               = $params['type'];
            $elementName        = ( isset( $element['name'] ) ? $element['name'] : "" );
            $target             = ( isset( $element['supertype'] ) ? $element['supertype'] : "" );
            $targetFields       = ( isset( $target['attributes'] ) ? $target['attributes'] : array() );
            $fields             = ( isset( $element['formDetails']['elementAttributes'][$elementName] ) ? $element['formDetails']['elementAttributes'][$elementName] : array() );
            $elementPrintOrder  = ( $element['printOrder'] !== "noPrint" ? $element['printOrder'] : ( isset( $_GET['page'] ) ? $_GET['page'] : "" ) );
            $elementsuperType   = ( !empty( $element['super_type'] ) ? $element['super_type'] : "" );

            $hiddenInputData  = '<input type="hidden" name="elementName" value="' . $elementName . '">';
            $hiddenInputData .= '<input type="hidden" name="modelId" value="' . $element['model_id'] . '">';
            $hiddenInputData .= '<input type="hidden" name="elementOrder" value="' . ( $elementPrintOrder + 1 ) . '">';
            $hiddenInputData .= '<input type="hidden" name="path" value="screenFactory">';
            $hiddenInputData .= '<input type="hidden" name="attr" value="elementControl">';
            $hiddenInputData .= '<input type="hidden" name="multiplicity" value="' . $multiplicity . '">';
            /**
             * Set params for data collection
             */
            $params['element_name'] = $elementName;
            $params['elements']     = $parsedElements;
            $params['multiplicity'] = $multiplicity;
            /**
             * - Set data equal to result if the multiplicity is 1 or empty.
             * - Set data equal to an array with key 0 and value result when the multiplicity is other then 1 or empty.
             * - An advanced form will be build for each result, the 0 key makes sure only 1 and only the first result is targeted.
             */
            if( $multiplicity === "1" || $multiplicity === "" ):
                $data = $result;
            else:
                $data = array("0" => $result);
            endif;
            /**
             * Get the id from the result. (for edit and delete)
             */
            $resultId = ( isset( $data[0]['id'] ) ? $data[0]['id'] : "" );
            /**
             * Start form building
             */
            $form = '<div class="element-form">';
            $form .= '<form action="' . APPLICATION_HOME . '" method="post">';
            /**
             * Parent/super type fields
             *
             * - Build the input fields, target first (parent/super type) then the element fields.
             * - Field type is based on data type.
             * - Placeholder is based on attribute documentation.
             */
            if( !empty( $targetFields ) ):
                foreach( $targetFields as $targetField ):
                    if( !empty( $targetField ) ):
                        $inputName        = ( isset( $targetField['input_name'] ) ? $targetField['input_name'] : "" );
                        $inputInfo        = ( isset( $targetField['documentation'] ) ? $targetField['documentation'] : "" );
                        $inputPlaceholder = ( isset( $targetField['initialValue'] ) ? $targetField['initialValue'] : "" );
                        $inputDataType    = ( isset( $targetField['data_type'] ) ? $targetField['data_type'] : "" );
                        $inputFieldType   = ( new IOXMLEAAttributeTypes( $element['model_id'], $inputDataType ) )->fieldType();
                        /**
                         * Display each input field with input name, based on data type
                         */
                        $form .= '<div class="element-input-box">';
                        if( !empty( $inputName ) ):
                            $form .= '<div class="element-input-name">' . $inputName . '</div>';
                            if( !empty( $inputFieldType ) ):
                                switch( $inputFieldType ):
                                    case"PrimitiveType":
                                    case"DataType":
                                        $formattedInputName = strtolower( str_replace( " ", "_", $inputName ) );
                                        $inputValue         = ( $multiplicity === "" ? "" : ( isset( $data[0][$formattedInputName] ) ? $data[0][$formattedInputName] : "" ) );
                                        $form              .= '<input type="text" name="' . $inputName . '" value="' . $inputValue . '" placeholder="' . $inputPlaceholder . '">';
                                        break;
                                    case"Enumeration":
                                        $params['model_id'] = $element['model_id'];
                                        $enumerations       = ( new IOXMLEAEnumerations( "getEnumerations", $inputDataType ) )->request( $params );
                                        $formattedInputName = strtolower( str_replace( " ", "_", $inputName ) );
                                        $inputValue         = ( $multiplicity === "" ? "" : ( isset( $data[0][$formattedInputName] ) ? $data[0][$formattedInputName] : "" ) );
                                        if( !empty( $enumerations ) && is_array( $enumerations ) ):
                                            $form              .= '<select name="' . $inputName . '">';
                                            /**
                                             * Show input data if available
                                             */
                                            if( !empty( $inputValue ) ):
                                                $form          .= '<option name="enum" value="' . $inputValue . '">' . $inputValue . '</option>';
                                            endif;
                                            /**
                                             * Populate the select box with the enumerations
                                             */
                                            $form              .= '<option name="enum" value="">Choose ' . $inputName . '</option>';
                                            foreach( $enumerations as $enumeration ):
                                                $form          .= '<option name="enum" value="' . $enumeration['input_name'] . '">' . $enumeration['input_name'] . '</option>';
                                            endforeach;
                                            $form              .= '</select>';
                                        endif;
                                        break;
                                endswitch;
                            endif;
                        endif;
                        /**
                         * Display hover information if available (attribute documentation)
                         */
                        if( !empty( $inputInfo ) ):
                            $form .= '<div class="element-input-hoverImg"><img src="images/icons/info_icon_blue.png"></div>';
                            $form .= '<div class="element-input-hover">' . $inputInfo . '</div>';
                        endif;
                        $form     .= '</div>';
                    endif;
                endforeach;
            endif;
            /**
             * Child/sub type fields
             */
            if( !empty( $fields ) ):
                foreach( $fields as $field ):
                    if( !empty( $field ) ):
                        $inputName        = ( isset( $field['name'] ) ? $field['name'] : "" );
                        $inputInfo        = ( isset( $field['documentation'] ) ? $field['documentation'] : "" );
                        $inputPlaceholder = ( isset( $field['initialValue'] ) ? $field['initialValue'] : "" );
                        $inputDataType    = ( isset( $field['data_type'] ) ? $field['data_type'] : "" );
                        $inputFieldType   = ( new IOXMLEAAttributeTypes( $element['model_id'], $inputDataType ) )->fieldType();
                        $form            .= '<div class="element-input-box">';
                        if( !empty( $inputName ) ):
                            $form .= '<div class="element-input-name">' . $inputName . '</div>';
                            if( !empty( $inputFieldType ) ):
                                switch( $inputFieldType ):
                                    case"PrimitiveType":
                                    case"DataType":
                                        $formattedInputName = strtolower( str_replace( " ", "_", $inputName ) );
                                        $inputValue         = ( $multiplicity === "" ? "" : ( isset( $data[0][$formattedInputName] ) ? $data[0][$formattedInputName] : "" ) );
                                        $form .= '<input type="text" name="' . $inputName . '" value="' . $inputValue . '" placeholder="' . $inputPlaceholder . '">';
                                        break;
                                    case"Enumeration":
                                        $params['model_id'] = $element['model_id'];
                                        $enumerations       = ( new IOXMLEAEnumerations( "getEnumerations", $inputDataType ) )->request( $params );
                                        $formattedInputName = strtolower( str_replace( " ", "_", $inputName ) );
                                        $inputValue         = ( $multiplicity === "" ? "" : ( isset( $data[0][$formattedInputName] ) ? $data[0][$formattedInputName] : "" ) );
                                        if( !empty( $enumerations ) && is_array( $enumerations ) ):
                                            $form              .= '<select name="' . $inputName . '">';
                                            if( !empty( $inputValue ) ):
                                                $form          .= '<option name="enum" value="' . $inputValue . '">' . $inputValue . '</option>';
                                            endif;
                                            $form              .= '<option name="enum" value="">Choose ' . $inputName . '</option>';
                                            foreach( $enumerations as $enumeration ):
                                                $form          .= '<option name="enum" value="' . $enumeration['input_name'] . '">' . $enumeration['input_name'] . '</option>';
                                            endforeach;
                                            $form              .= '</select>';
                                        endif;
                                        break;
                                endswitch;
                            endif;
                        endif;
                        /**
                         * Display hover information if available (attribute documentation)
                         */
                        if( !empty( $inputInfo ) ):
                            $form .= '<div class="element-input-hoverImg"><img src="images/icons/info_icon_blue.png"></div>';
                            $form .= '<div class="element-input-hover">' . $inputInfo . '</div>';
                        endif;
                        $form     .= '</div>';
                    endif;
                endforeach;
            endif;

            $form             .= '<div class="element-input-box-submit">';
            if( $type === "normal" ):

                $form     .= $hiddenInputData;

                $form     .= '<div class="element-input-submit">';
                $form     .= '<a href="' . APPLICATION_HOME . '?model&page=' . ( $elementPrintOrder + 1 ) . '" class="button">next</a>';
                $form     .= '</div>';

                if( $element['printOrder'] > 1 ):
                    $form .= '<div class="element-input-submit">';
                    $form .= '<a href="' . APPLICATION_HOME . '?model&page=' . ( $elementPrintOrder - 1 ) . '" class="button">previous</a>';
                    $form .= '</div>';
                endif;

                $form     .= '<div class="element-input-submit">';
                $form     .= '<input type="hidden" name="action" value="create">';
                $form     .= '<input type="submit" name="submit" value="add" class="button">';
                $form     .= '</div>';
                $form     .= '</form>';
                $form     .= '</div>';
            else:

                if( $type === "advanced" ):

                    $form .= '<div class="element-input-submit">';
                    $form .= '<form action="' . APPLICATION_HOME . '" method="post">';
                    $form .= $hiddenInputData;
                    $form .= '<input type="hidden" name="resultId" value="' . $resultId . '">';
                    $form .= '<input type="hidden" name="action" value="edit">';
                    $form .= '<input type="submit" name="submit" value="edit" class="button">';
                    $form .= '</form>';
                    $form .= '</div>';

                    $form .= '<div class="element-input-submit">';
                    $form .= '<form action="' . APPLICATION_HOME . '" method="post">';
                    $form .= $hiddenInputData;
                    $form .= '<input type="hidden" name="resultId" value="' . $resultId . '">';
                    $form .= '<input type="hidden" name="action" value="delete">';
                    $form .= '<input type="submit" name="submit" value="delete" class="button">';
                    $form .= '</div>';

                    $form .= '</form>';
                    $form .= '</div>';

                endif;
            endif;

            $form         .= '</div>';

            return( $form );
        }

        /**
         * @param $params
         * @return string
         */
        private function buildSuperForm( $params )
        {
            $element        = $params['element'];
            $multiplicity   = $params['multiplicity'];
            $elementName    = ( isset($element['name']) ? $element['name'] : "" );
            $superTypes     = ( isset($element['super_types']) ? $element['super_types'] : array() );

            $form  = '<div class="element-form">';
            $form .= '<form action="' . APPLICATION_HOME . '" method="post">';
            /**
             * Super types drop down
             */
            if ( !empty( $superTypes ) ):

                foreach( $superTypes as $superTypeName => $subTypes ):
                    $form .= '<div class="element-input-box">';
                    $form .= '<div class="element-input-name">' . $superTypeName . '</div>';
                    $form .= '<select name="subElement">';
                    $form .= '<option name="" value="">Choose ' . $elementName . '</option>';

                    foreach( $subTypes as $subType ):
                        $form .= '<option name=" ' . $subType . '">' . $subType . '</option>';
                    endforeach;

                    $form .= '</select>';
                    $form .= '</div>';
                endforeach;
            endif;
            $form .= '<div class="element-input-box">';
            /**
             * Display previous button if the class order is bigger then one
             */
            $form .= '<div class="element-input-submit">';
            $form .= '<a href="' . APPLICATION_HOME . '?model&page=' . ($element['printOrder'] + 1) . '" class="button">next</a>';
            $form .= '</div>';
            if( $element['printOrder'] > 1 ):
                $form .= '<div class="element-input-submit">';
                $form .= '<a href="' . APPLICATION_HOME . '?model&page=' . ($element['printOrder'] - 1) . '" class="button">previous</a>';
                $form .= '</div>';
            endif;
            /**
             * Hidden input fields
             */
            $form .= '<div class="element-input-submit">';
            $form .= '<input type="hidden" name="elementName" value="' . $elementName . '">';
            $form .= '<input type="hidden" name="modelId" value="' . $element['model_id'] . '">';
            $form .= '<input type="hidden" name="elementOrder" value="' . ($element['printOrder'] + 1) . '">';
            $form .= '<input type="hidden" name="path" value="screenFactory">';
            $form .= '<input type="hidden" name="attr" value="elementControl">';
            $form .= '<input type="hidden" name="action" value="addForm">';
            $form .= '<input type="submit" name="submit" value="add" class="button">';
            $form .= '</div>';
            $form .= '</div>';
            $form .= '</form>';
            $form .= '</div>';
            /**
             * Check if a form should be added, if so build a super element.
             */
            if (!empty($_GET['addForm'])):
                $className              = $_GET['addForm'];
                $parsedElements         = $params['elements'];
                $params['element_name'] = $className;
                $params['elements']     = $parsedElements;
                $params['multiplicity'] = $multiplicity;

                foreach ($parsedElements as $parsedElement):
                    if ($parsedElement['name'] === $className):
                        $form .= (new IOXMLEAScreenFactory("buildSuperElement", $parsedElement))->request($params);
                        break;
                    endif;
                endforeach;
            endif;

            return ($form);
        }
        /**
         * @param $params
         * @return bool|string
         */
        private function buildOperations($params )
        {
            if( !empty( $params['operations'] ) ):

                $operations       = $params['operations'];
                $getOperationData = ( new IOExcelFactory( "getOperations" ) )->request( $params );
                $operationData    = ( !empty( $getOperationData ) ? $getOperationData : array() );
                usort( $operations, array( $this,'sortByPrintOrder' ) );

                $operationsElement  = '';
                $operationsElement .= '<div class="elementOperations">';

                $i = 0;
                $operationsElement .= '<div class="element-form">';
                $operationsElement .= '<div class="elementOperations-title">Berekende resultaten</div>';

                foreach( $operations as $operation ):
                    $operationsElement .= '<div class="elementOperation-documentation">' . $operation['documentation'] . '</div>';
                    $operationsElement .= '<div class="element-input-box">';
                    $operationsElement .= '<div class="element-input-name">' . $operation['name'] . '</div>';

                    if( !empty( $operationData[$i]['name'] ) && !empty( $operationData[$i]['value'] ) ):
                        if( $operation['name'] === $operationData[$i]['name'] ):
                            $operationsElement .= '<input type="text" name="" value="' . $operationData[$i]['value'] . '"">';
                        endif;
                    endif;
                    $operationsElement .= '</div>';
                    $i++;
                endforeach;

                $operationsElement .= '</div>';
                $operationsElement .= '</div>';

                return( $operationsElement );
            else:
                return( false );
            endif;
        }

        private function showElementView( $params )
        {
            $elements     = $this->extractAndOrderElements();
            $elementView  = '<div class="element-view">';

            foreach( $elements as $element ):

                if( $element['printOrder'] !== "noPrint" ):
                    $highlighted = ( $element['name'] === $params['element_name'] ? " highlighted" : "" );
                    $elementView .= '<div class="element-view-item ' . $highlighted . '">';
                    $elementView .= $element['name'];
                    $elementView .= '</div>';
                endif;

            endforeach;

            $elementView .= '</div>';

            return( $elementView );
        }
    }

endif;