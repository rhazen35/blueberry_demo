<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 11-Aug-16
 * Time: 12:04
 */

namespace app\enterpriseArchitecture;

/**
 * Class IOXMLEAScreenFactory
 * @package app\enterpriseArchitecture
 *
 * The screen factory uses to following classes:
 *
 * - IOXMLEAModel --> get basic model information
 * - IOXMLEAModelParser --> parse the xml into a php array
 * - IOXMLEAInheritance --> create relations for each xml element
 * - IOXMLEAAttributeTypes --> returns the attribute data type (a.k.a field type)
 * - IOXMLEAEnumerations --> returns a list of all attribute enumerations
 *
 * ##### [EXTRACT AND ORDER] #####
 *
 * - The extract and order elements function will gather all needed data for screen processing.
 * - Data without a print order will be marked as noPrint.
 * - Extracted data will be ordered by the given print order.
 * - Attributes and operations will also be ordered per element.
 * - Parent/Super type attributes will be displayed before child/sub type attributes.
 *
 * TODO: Order element result data by last item added.
 * TODO: Operations, inheritance, extract and order, handling/display of operations.
 * TODO: Form validation: which fields are required, what is the maximum multiplicity.
 * TODO: Handle constants, arrays and lists, display constant in a grayed/unchangeable field.
 *
 * ##### [SCREEN FACTORY] #####
 *
 * - Each model starts with an intro, created by the build element intro function.
 *  [note:] Each element that is a root is the intro.
 * - Elements that are of type uml:class and have a print will be created by the build element function.
 * - The build element function also creates the forms, which are handled based on multiplicity.
 * - The build super element function builds a super element sub form.
 *
 * ** FORMS **
 *      - When the multiplicity is equal to 1 a basic (normal) form will be build.
 *      - When the multiplicity is equal to 1..* or 0..* a basic (normal) form and advanced form(s) will be build.
 *      - Basic forms will contain input data if the multiplicity is equal to 1 and if any is available.
 *      - Basic forms will NOT contain input data if the multiplicity is equal to 1..* or 0..* and if any is available.
 *      - Advanced forms always contain available input data and can be edited/deleted.
 *
 * ** SUPER FORMS **
 *      - When a class is a supertype and has sub types, a super form will be displayed.
 *      - Super forms only allow for sub type selection.
 *      - Each selected sub type will be loaded as a form. (normal form)
 *      - Results of all sub types will be displayed on their super type page, sorted by sub type.
 */

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
     *
     * Request handles the public access and controls function calls.
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
                return( $this->buildElement() );
                break;
            case"buildSuperElement":
                return( $this->buildSuperElement() );
                break;
        endswitch;
    }
    /**
     * @param $params
     * @return array
     *
     * Extract all element names, element names are used to target specific elements in the parsed php array.
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
     *
     * Sorts any array that contains a printOrder, sorts compares naturally.
     */
    private function sortByPrintOrder( $a, $b )
    {
        if( !empty( $a['printOrder'] ) && !empty( $b['printOrder'] ) ):
            return( strnatcmp( $a['printOrder'], $b['printOrder'] ) );
        endif;
    }
    /**
     * @return array
     *
     * Extract and order elements function.
     *
     * - Get basic model data.
     * - Build relations for each element.
     * - Get the parsed classes array, which contains all xml elements data.
     * - Extract element names and start extract and order of elements.
     *
     * - Get the matching target connector and add the super type if available.
     * - Add all super and sub types if available.
     * - Extract and order attributes, super types first, if avilable.
     * - Extract and order operations, super types first, if available.
     */
    private function extractAndOrderElements()
    {
        $modelData          = ( new IOXMLEAModel( $this->xmlModelId ) )->getModel();
        $relations          = ( new IOXMLEAInheritance( "buildRelations", $this->xmlModelId ) )->request();
        $orderedElements    = array();
        $highestOrder       = 0;

        if (!empty($modelData)):
            if (!empty($modelData['hash'])):
                /**
                 * Get the xml file, pass it to the model parser and extract element names.
                 */
                $xmlFile        = 'web/files/xml_models_tmp/' . $modelData['hash'] . '.' . $modelData['ext'];
                $parsedElements = ( new IOXMLEAModelParser($xmlFile))->parseXMLClasses();
                $params         = array( "elements" => $parsedElements );
                $elementNames   = $this->extractElementNames ( $params );
                /**
                 * Start extraction.
                 */
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
                    $element          = ( isset( $parsedElements[$elementName] ) && $parsedElements[$elementName]['type'] === "uml:Class" ? $parsedElements[$elementName] : "" );
                    $idref            = ( isset( $element['idref'] ) ? $element['idref'] : "" );
                    $root             = ( isset( $element['root'] ) ? $element['root'] : false );
                    $abstract         = ( isset( $element['abstract'] ) ? $element['abstract'] : false );
                    $name             = ( isset( $element['name'] ) ? $element['name'] : "" );
                    $tags             = ( isset( $element['tags'] ) ? $element['tags'] : false );
                    $order            = ( isset( $tags['QR-PrintOrder']['order'] ) ? $tags['QR-PrintOrder']['order'] : "noPrint");
                    $highestOrder     = ( $order !== "noPrint" ? $highestOrder + 1 : $highestOrder );
                    /**
                     * Element documentation, attributes, operations, target(collected by the matching connector, if available)
                     */
                    $elementDocumentation  = ( isset( $element['documentation'] ) ? $element['documentation'] : "" );
                    $elementAttributes     = ( isset( $element['attributes'] ) ? $element['attributes'] : false );
                    $elementOperations     = ( isset( $element['operations'] ) ? $element['operations'] : "" );
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
                         * Get element multiplicity, set to 1 if none is provided.
                         */
                        $orderedElements[$i]['multiplicity']  = $relationMultiplicity;
                        $orderedElements[$i]['header_txt']  = $relationMultiHead;
                        /**
                         * Get the parent, child, super type and sub type
                         */
                        $orderedElements[$i]['isParent']             = $relationIsParent;
                        $orderedElements[$i]['isChild']              = $relationIsChild;
                        $orderedElements[$i]['isSuperType']          = $relationIsSuperType;
                        $orderedElements[$i]['isSubType']            = $relationIsSubType;
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
                            $labels         = ( isset( $target['labels'] ) ? $target['labels'] : "" );

                            $orderedElements[$i]['supertype']['idref']              = $idref;
                            $orderedElements[$i]['supertype']['order']              = $order;
                            $orderedElements[$i]['supertype']['documentation']      = $documentation;
                            $orderedElements[$i]['supertype']['attributes']         = $attributes;
                            $orderedElements[$i]['supertype']['attributes']['tags'] = $attributesTags;
                            $orderedElements[$i]['supertype']['operations']         = $operations;
                            $orderedElements[$i]['supertype']['labels']             = $labels;
                        endif;
                        /**
                         * Select the sub types for each super type.
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
                         * Add form details
                         */
                        $orderedElements[$i]['formDetails'] = array();
                        /**
                         * Element documentation
                         */
                        $orderedElements[$i]['formDetails']['elementDocumentation']     = $elementDocumentation;
                        /**
                         * Element Attributes
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

                $i++;
                endforeach;
            endif;
        endif;
        /**
         * Sort all elements by print order, add the highest order and return the extracted and ordered  elements.
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
        $xmlFile            =  'web/files/xml_models_tmp/' . $modelData['hash'] . '.' . $modelData['ext'];
        $parsedConnectors   = ( new IOXMLEAModelParser( $xmlFile) )->parseConnectors();
        $totalConnectors    = count( $parsedConnectors['connectors'] );

        for( $j = 0; $j < $totalConnectors; $j++ ):
            if( $idref === $parsedConnectors['connectors']['connector'.($j+1)]['source']['idref'] ):
                if( $parsedConnectors['connectors']['connector'.($j+1)]['properties']['ea_type'] === "Generalization" ):

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

                    if( $type === "source" ):
                        return( $sourceArray );
                    elseif( $type === "target" ):
                        return( $targetArray );
                    endif;


                    break;

                endif;
            endif;
        endfor;

    }

    private function extractAndOrderOperations( $operations )
    {
        $operationsArray = array();
        $totalOperations = count( $operations );

        for( $i = 0; $i < $totalOperations; $i++ ):
            $operationName          = ( !empty( $operations['operation'.($i+1)]['name'] ) ? $operations['operation'.($i+1)]['name'] : "" );
            $operationDocumentation = ( !empty( $operations['operation'.($i+1)]['documentation'] ) ? $operations['operation'.($i+1)]['documentation'] : "" );
            $operationTags          = ( !empty( $operations['operation'.($i+1)]['tags'] ) ? $operations['operation'.($i+1)]['tags'] : "" );
            $totalTags              = count( $operationTags );

            $operationsArray[$operationName]['name']          = $operationName;
            $operationsArray[$operationName]['documentation'] = $operationDocumentation;

            for( $j = 0; $j < $totalTags; $j++ ):
                if( !empty( $operations['operation'.($i+1)]['tags'][$j] ) ):

                    $operationTagName    = ( !empty( $operations['operation'.($i+1)]['tags'][$j]['name'] ) ? $operations['operation'.($i+1)]['tags'][$j]['name'] : "" );
                    $operationTagCell    = ( !empty( $operations['operation'.($i+1)]['tags'][$j]['cell'] ) ? $operations['operation'.($i+1)]['tags'][$j]['cell'] : "" );
                    $operationTagOrder   = ( $operations['operation'.($i+1)]['tags'][$j]['name'] === "QR-PrintOrder" ? $operationTagCell : "" );
                    $operationTagFile    = ( !empty( $operations['operation'.($i+1)]['tags'][$j]['file'] ) ? $operations['operation'.($i+1)]['tags'][$j]['file'] : "" );
                    $operationTagTab     = ( !empty( $operations['operation'.($i+1)]['tags'][$j]['tab'] ) ? $operations['operation'.($i+1)]['tags'][$j]['tab'] : "" );

                    $operationsArray[$operationName]['printOrder'] = $operationTagOrder;

                    if( $operationTagName !== "QR-PrintOrder" ):

                        $operationsArray[$operationName][$operationTagName]['name']       = $operationTagName;

                        $operationsArray[$operationName][$operationTagName]['file']       = $operationTagFile;
                        $operationsArray[$operationName][$operationTagName]['tab']        = $operationTagTab;
                        $operationsArray[$operationName][$operationTagName]['cell']       = $operationTagCell;

                    endif;

                endif;

            endfor;
        endfor;

        usort( $operationsArray, array( $this,'sortByPrintOrder' ) );

        return( $operationsArray );

    }

    private function extractAndOrderAttributes( $attributes )
    {
        $attributesArray = array();

        foreach( $attributes as $attribute => $array ):
            $attributesArray[$attribute]['name'] = $attribute;
            $attributeDocumentation = $array['documentation'];
            $attributeDataType      = $array['data_type'];
            $attributeInitialValue  = $array['initialValue'];

            $attributesArray[$attribute]['data_type']     = $attributeDataType;
            $attributesArray[$attribute]['initialValue']  = $attributeInitialValue;
            $attributesArray[$attribute]['documentation'] = $attributeDocumentation;

            $tags      = ( !empty( $array['tags'] ) ? $array['tags'] : "" );
            $totalTags = count( $tags );

            if( $totalTags > 0 ):
                for( $i = 0; $i < $totalTags; $i++ ):
                    $tagName = ( !empty( $tags[$i]['name'] ) ? $tags[$i]['name'] : "" );
                    if( $tagName === "QR-PrintOrder" ):
                        $attributesArray[$attribute]['printOrder'] = $tags[$i]['cell'];
                    else:
                        $attributesArray[$attribute]['file']    = ( !empty( $tags[$i]['file'] ) ? $tags[$i]['file'] : "" );
                        $attributesArray[$attribute]['tab']     = ( !empty( $tags[$i]['tab'] ) ? $tags[$i]['tab'] : "" );
                        $attributesArray[$attribute]['cell']    = ( !empty( $tags[$i]['cell'] ) ? $tags[$i]['cell'] : "" );
                    endif;
                endfor;
            endif;

        endforeach;

        usort( $attributesArray, array( $this,'sortByPrintOrder' ) );

        return( $attributesArray );
    }

    /**
     * @return string
     */
    private function buildElementIntro()
    {
        $element     = $this->xmlModelId;
        $elementName = ( !empty( $element['name'] ) ? $element['name'] : "" );
        $title       = $elementName;
        $intro       = ( isset( $element['formDetails']['elementDocumentation'] ) ? $element['formDetails']['elementDocumentation'] : "" );

        $html = '<div class="element">';

        if( !empty( $element['formDetails'] ) ):

            $formOperations = $element['formDetails']['elementOperations'][$elementName];

            $html .= '<div class="elementIntro-title">'. $title .'</div>';
            $html .= '<div class="elementIntro-txt"><p>'. $intro .'<p></div>';

            $totalFormOperations = count( $formOperations );

            for( $i = 0; $i < $totalFormOperations; $i++ ):
                $html .= '<div class="elementIntro-subTitle">'. $formOperations[$i]['name'] .'</div>';
                $html .= '<div class="elementIntro-subIntro"><p>'. $formOperations[$i]['documentation'] .'</p></div>';
            endfor;

            $html .= '<div class="elementIntro-next"><a href="' . APPLICATION_HOME . '?model&page=' . ( ($element['printOrder'] + 1) ) . '" class="button">Next</a></div>';

        endif;

        $html .= '</div>';

        return( $html );

    }

    /**
     * @return string
     */
    private function buildElement()
    {
        $element         = $this->xmlModelId;
        $modelId         = ( isset( $element['model_id'] ) ? $element['model_id'] : "" );
        $parsedElements  = ( new IOXMLEAScreenFactory( "extractAndOrderElements", $modelId ) )->request( $params = null );
        $elementName     = ( isset( $element['name'] ) ? $element['name'] : "" );
        $title           = ( !empty( $element['header_txt'] ) ? $element['header_txt'] : ( !empty( $element['name'] ) ? $element['name'] : "" ) );
        $documentation   = ( isset( $element['formDetails']['elementDocumentation'] ) ? $element['formDetails']['elementDocumentation'] : "" );
        $multiplicity    = ( isset( $element['multiplicity'] ) ? $element['multiplicity'] : "" );
        $hasSuperTypes   = ( isset( $element['super_types'] ) ? $element['super_types'] : "" );

        $params['element_name'] = $elementName;
        $params['elements']     = $parsedElements;
        $params['multiplicity'] = $multiplicity;

        if( !empty( $hasSuperTypes ) ):
            $data = array();
            foreach( $hasSuperTypes[$elementName] as $hasSuperType ):
                $params['element_name'] = $hasSuperType;
                $params['elements']     = $parsedElements;
                $params['multiplicity'] = $multiplicity;
                $results[$hasSuperType] = ( new XMLDBController( "read" ) )->request( $params );

                $totalResults = count($results[$hasSuperType]);
                for( $i = 0; $i < $totalResults; $i++ ):
                    if( !empty(  $results[$hasSuperType][$i]['id'] ) ):
                        $results[$hasSuperType][$i]['name'] = $hasSuperType;
                        $data[$hasSuperType] =  $results[$hasSuperType];
                    endif;
                endfor;

            endforeach;
        else:
            $params['element_name'] = $elementName;
            $params['elements']     = $parsedElements;
            $params['multiplicity'] = $multiplicity;
            $data = ( new XMLDBController( "read" ) )->request( $params );
        endif;

        $html  = '<div class="element">';
        $html .= '<div class="element-title">'. $title .'</div>';
        $html .= '<div class="element-documentation"><p>'. $documentation .'</p></div>';

        if( !empty( $hasSuperTypes ) ):
            $html .= $this->buildSuperForm( $element, $multiplicity );

            if( $multiplicity === "1..*" || $multiplicity === "0..*" || $multiplicity === "" ):
                if( !empty( $data ) ):
                    $html .= '<br><br><div class="element-submitted"><p>Previously submitted '. $elementName . '</p></div>';
                    foreach( $hasSuperTypes[$elementName] as $hasSuperType ):
                        if( !empty( $data[$hasSuperType] ) ):
                            $totalResults = count( $data[$hasSuperType] );
                            $subTypeName = $hasSuperType;
                            $html .= '<br><br><div class="element-subTitle"><p>' . $subTypeName . '</p></div>';
                            for( $i = 0; $i < $totalResults; $i++ ):
                                foreach( $parsedElements as $parsedElement ):
                                    if( $subTypeName === $parsedElement['name'] && $data[$hasSuperType][$i]['name'] === $hasSuperType ):
                                        $html .= $this->buildForm( $parsedElement, $data[$hasSuperType][$i], $multiplicity, "advanced" );
                                    break;
                                    endif;
                                endforeach;
                            endfor;
                        endif;
                    endforeach;
                endif;
            endif;
        else:
            $html .= $this->buildForm( $element, $data, $multiplicity,  "normal" );

            if( $multiplicity === "1..*" || $multiplicity === "0..*" || $multiplicity === "" ):
                if( !empty( $data ) ):
                    $html .= '<br><br><div class="element-submitted"><p>Previously submitted ' . $elementName . '</p></div>';
                    foreach( $data as $result ):
                        $html .= $this->buildForm( $element, $result, $multiplicity, "advanced" );
                    endforeach;
                endif;
            endif;
        endif;

        $html .= '</div>';

        return( $html );
    }

    /**
     * @return string
     */
    private function buildSuperElement()
    {
        $element         = $this->xmlModelId;
        $modelId         = ( isset( $element['model_id'] ) ? $element['model_id'] : "" );
        $parsedElements  = ( new IOXMLEAScreenFactory( "extractAndOrderElements", $modelId ) )->request( $params = null );
        $elementName     = ( isset( $element['name'] ) ? $element['name'] : "" );
        $title           = ( !empty( $element['header_txt'] ) ? $element['header_txt'] : ( !empty( $element['name'] ) ? $element['name'] : "" ) );
        $documentation   = ( isset( $element['formDetails']['elementDocumentation'] ) ? $element['formDetails']['elementDocumentation'] : "" );
        $multiplicity    = ( isset( $element['multiplicity'] ) ? $element['multiplicity'] : "" );

        $params['element_name'] = $elementName;
        $params['elements']     = $parsedElements;
        $params['multiplicity'] = $multiplicity;
        $data = ( new XMLDBController( "read" ) )->request( $params );

        $html = '<div class="element-title">'. $title .'</div>';
        $html .= '<div class="element-documentation"><p>'. $documentation .'</p></div>';

        $html .= $this->buildForm( $element, $data, $multiplicity,  "normal" );

        return( $html );
    }

    /**
     * @param $element
     * @param $result
     * @param $multiplicity
     * @param $type
     * @return string
     */
    private function buildForm ( $element, $result, $multiplicity, $type )
    {
        /**
         * Collect all data for form building.
         */
        $parsedElements = $this->extractAndOrderElements();
        $elementName    = ( isset( $element['name'] ) ? $element['name'] : "" );
        $target         = ( isset( $element['supertype'] ) ? $element['supertype'] : "" );
        $targetFields   = ( isset( $target['attributes'] ) ? $target['attributes'] : "" );
        $fields         = ( isset( $element['formDetails']['elementAttributes'][$elementName] ) ? $element['formDetails']['elementAttributes'][$elementName] : "" );

        $elementPrintOrder = ( $element['printOrder'] !== "noPrint" ? $element['printOrder'] : ( isset( $_GET['page'] ) ? $_GET['page'] : "" ) );
        /**
         * Set standard hidden input fields
         */
        $hiddenInputData = '<input type="hidden" name="elementName" value="' . $elementName . '">';
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
         * - Set data equal to result if the multiplicity is 1.
         * - Set data equal to an array with key 0 and value result when the multiplicity is other then 1.
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
         * Start the normal form.
         *
         * ############################################################
         *
         * - The normal form does not contain any data and has normal buttons depending on multiplicity.
         *
         * [FIELDS]
         * - The normal form has fields created from the element attributes.
         * - Attributes inherited from their parent/super type will be displayed first.
         * - Attributes specific to the current element (child/sub type) will be displayed after.
         * - Submit buttons will be displayed according to the elements multiplicity.
         * - Set the input name(attribute name), the hover information(attribute documentation), the placeholder(attribute initial value).
         *
         * ############################################################
         *
         * [DATA TYPES]
         * - Set the data type and build input type accordingly:
         *  - input type txt = PrimitiveType and DataType
         *  - select         = Enumeration
         */
        $form = '<div class="element-form">';

        switch( $type ):
            case"normal":
                $form .= '<div class="element-form-intro">Fill in the form below to add another ' . $elementName . '</div>';
                break;
            case"advanced":
                $form .= '<div class="element-form-intro">Edit or delete this ' . $elementName . '</div>';
                break;
        endswitch;

        $form .= '<form action="' . APPLICATION_HOME . '" method="post">';
        /**
         * Parent/super type fields
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
                     * Start of the input box, i.e. field name, input field based on data type, placeholder based on attribute documentation and data type format.
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
                                    $form .= '<input type="text" name="' . $inputName . '" value="' . $inputValue . '" placeholder="' . $inputPlaceholder . '">';
                                    break;
                                case"Enumeration":
                                    $params['model_id'] = $element['model_id'];
                                    $enumerations       = ( new IOXMLEAEnumerations( "getEnumerations", $inputDataType ) )->request( $params );
                                    $formattedInputName = strtolower( str_replace( " ", "_", $inputName ) );
                                    $inputValue         = ( $multiplicity === "" ? "" : ( isset( $data[0][$formattedInputName] ) ? $data[0][$formattedInputName] : "" ) );
                                    $form .= '<select name="' . $inputName . '">';
                                    if( !empty( $inputValue ) ):
                                        $form .= '<option name="enum" value="' . $inputValue . '">' . $inputValue . '</option>';
                                    endif;
                                    $form .= '<option name="enum" value="">Choose ' . $inputName . '</option>';
                                    foreach( $enumerations as $enumeration ):
                                        $form .= '<option name="enum" value="' . $enumeration['input_name'] . '">' . $enumeration['input_name'] . '</option>';
                                    endforeach;
                                    $form .= '</select>';
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
                    /**
                     * End of the input box.
                     */
                    $form .= '</div>';
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

                    $form .= '<div class="element-input-box">';

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

                                    $form .= '<select name="' . $inputName . '">';

                                    if( !empty( $inputValue ) ):
                                        $form .= '<option name="enum" value="' . $inputValue . '">' . $inputValue . '</option>';
                                    endif;

                                    $form .= '<option name="enum" value="">Choose ' . $inputName . '</option>';

                                    foreach( $enumerations as $enumeration ):
                                        $form .= '<option name="enum" value="' . $enumeration['input_name'] . '">' . $enumeration['input_name'] . '</option>';
                                    endforeach;

                                    $form .= '</select>';
                                    break;
                            endswitch;
                        endif;
                    endif;

                    if( !empty( $inputInfo ) ):
                        $form .= '<div class="element-input-hoverImg"><img src="images/icons/info_icon_blue.png"></div>';
                        $form .= '<div class="element-input-hover">' . $inputInfo . '</div>';
                    endif;

                    $form .= '</div>';

                endif;
            endforeach;
        endif;

        $form .= '<div class="element-input-box-submit">';

        if( $type === "normal" ):

            if( $type === "normal" && $multiplicity === "1..*" || $multiplicity === "0..*" ):

                $form .= '<div class="element-input-submit">';
                $form .= '<a href="' . APPLICATION_HOME . '?model&page=' . ( $elementPrintOrder + 1 ) . '" class="button">next</a>';
                $form .= '</div>';

                if( $element['printOrder'] > 1 ):
                    $form .= '<div class="element-input-submit">';
                    $form .= '<a href="' . APPLICATION_HOME . '?model&page=' . ( $elementPrintOrder - 1 ) . '" class="button">previous</a>';
                    $form .= '</div>';
                endif;
            endif;

            $form .= $hiddenInputData;

            if( $type === "normal" && $multiplicity === "1..*" || $multiplicity === "0..*" || $multiplicity === "" ):
                $form .= '<div class="element-input-submit">';
                $form .= '<input type="hidden" name="action" value="create">';
                $form .= '<input type="submit" name="submit" value="add" class="button">';
                $form .= '</div>';

                $form .= '<div class="element-input-info">';
                if( $multiplicity === "" ):
                    $form .= 'Press add to add ' . $elementName . '.';
                else:
                    $form .= 'Press add to add ' . $elementName . ', press next to continue.';
                endif;
                $form .= '</div>';
            else:
                $form .= '<div class="element-input-submit">';
                $form .= '<input type="hidden" name="action" value="create">';
                $form .= '<input type="submit" name="submit" value="next" class="button">';
                $form .= '</div>';

                if( $element['printOrder'] > 1 ):
                    $form .= '<div class="element-input-submit">';
                    $form .= '<a href="' . APPLICATION_HOME . '?model&page=' . ( $elementPrintOrder - 1 ) . '" class="button">previous</a>';
                    $form .= '</div>';
                endif;

                $form .= '<div class="element-input-info">';
                if( !empty( $data ) ):
                    $form .= 'Press next to update ' . $elementName . ' and continue.';
                else:
                    $form .= 'Press next to add ' . $elementName . ' and continue.';
                endif;
                $form .= '</div>';
            endif;

            $form .= '</form>';
            $form .= '</div>';

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

                $form .= '<div class="element-input-info">';
                $form .= 'Press delete to remove ' . $elementName . ', press edit to change this ' . $elementName . '.';
                $form .= '</div>';
                $form .= '</form>';

                $form .= '</div>';
            endif;
        endif;

        $form .= '</div>';

        return( $form );

    }

    private function buildSuperForm( $element, $multiplicity )
    {
        $elementName  = ( isset( $element['name'] ) ? $element['name'] : "" );
        $superTypes   = ( isset( $element['super_types'] ) ? $element['super_types'] : "" );

        $form = '<div class="element-form">';

        $form .= '<div class="element-form-intro">Choose and add ' . $elementName . '</div>';

        $form .= '<form action="' . APPLICATION_HOME . '" method="post">';

        /**
         * Super types drop down
         */
        if( !empty( $superTypes ) ):
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
        $form .= '<a href="' . APPLICATION_HOME . '?model&page=' . ( $element['printOrder'] + 1 ) . '" class="button">next</a>';
        $form .= '</div>';

        if( $element['printOrder'] > 1 ):
            $form .= '<div class="element-input-submit">';
            $form .= '<a href="' . APPLICATION_HOME . '?model&page=' . ( $element['printOrder'] - 1 ) . '" class="button">previous</a>';
            $form .= '</div>';
        endif;

        $form .= '<div class="element-input-submit">';
        $form .= '<input type="hidden" name="elementName" value="' . $elementName . '">';
        $form .= '<input type="hidden" name="modelId" value="' . $element['model_id'] . '">';
        $form .= '<input type="hidden" name="elementOrder" value="' . ($element['printOrder'] + 1) . '">';
        $form .= '<input type="hidden" name="path" value="screenFactory">';
        $form .= '<input type="hidden" name="attr" value="elementControl">';
        $form .= '<input type="hidden" name="action" value="addForm">';
        $form .= '<input type="submit" name="submit" value="add" class="button">';
        $form .= '</div>';


        $form .= '<div class="element-input-info">';
        $form .= 'Press add to add ' . $elementName . ', press next to continue.';
        $form .= '</div>';

        $form .= '</div>';

        $form .= '</form>';

        $form .= '</div>';

        if( !empty( $_GET['addForm'] ) ):
            $className = $_GET['addForm'];
            $modelId = $element['model_id'];
            $parsedElements  = ( new IOXMLEAScreenFactory( "extractAndOrderElements", $modelId ) )->request( $params = null );
            foreach($parsedElements as $parsedElement):
                if( $parsedElement['name'] === $className ):
                    $params['element_name'] = $className;
                    $params['elements']     = $parsedElements;
                    $params['multiplicity'] = $multiplicity;
                    $form .= ( new IOXMLEAScreenFactory( "buildSuperElement", $parsedElement ) )->request( $params );
                endif;
            endforeach;
        endif;

        return( $form );

    }
}