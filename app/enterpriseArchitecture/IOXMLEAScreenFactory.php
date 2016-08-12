<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 11-Aug-16
 * Time: 12:04
 */

namespace app\enterpriseArchitecture;


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
    public function request($params )
    {
        switch( $this->type ):
            case"extractElementNames":
                return( $this->extractElementNames( $params ) );
                break;
            case"extractAndOrderElements":
                return( $this->extractAndOrderElements() );
                break;
            case"buildScreenArray":
                return( $this->buildScreenArray() );
                break;
        endswitch;
    }

    /**
     * @param $params
     * @return array
     */
    private function extractElementNames($params )
    {
        $parsedElements = $params['elements'];
        $elementNames = array();

        foreach( $parsedElements as $parsedElement ):
            /**
             * Get all the classes names
             */
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
    public function extractAndOrderElements()
    {
        $modelData          = ( new IOXMLEAModel( $this->xmlModelId ) )->getModel();
        $relations          = ( new IOXMLEAInheritance( "buildRelations", $this->xmlModelId ) )->request();
        $orderedElements    = array();
        $highestOrder       = 0;

        if (!empty($modelData)):

            if (!empty($modelData['hash'])):

                $xmlFile        = 'web/files/xml_models_tmp/' . $modelData['hash'] . '.' . $modelData['ext'];
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
                    $element          = ( isset( $parsedElements[$elementName] ) && $parsedElements[$elementName]['type'] === "uml:Class" ? $parsedElements[$elementName] : "" );
                    $idref            = ( isset( $element['idref'] ) ? $element['idref'] : "" );
                    $root             = ( isset( $element['root'] ) ? $element['root'] : false );
                    $abstract         = ( isset( $element['abstract'] ) ? $element['abstract'] : false );
                    $name             = ( isset( $element['name'] ) ? $element['name'] : "" );
                    $tags             = ( isset( $element['tags'] ) ? $element['tags'] : false );
                    $order            = ( isset( $tags['QR-PrintOrder']['order'] ) ? $tags['QR-PrintOrder']['order'] : "noPrint");

                    $elementDocumentation  = ( isset( $element['documentation'] ) ? $element['documentation'] : "" );
                    $elementAttributes     = ( isset( $element['attributes'] ) ? $element['attributes'] : false );
                    $elementOperations     = ( isset( $element['operations'] ) ? $element['operations'] : "" );
                    /**
                     * Only create element array if there is an order.
                     */
                    if( $name === $relationName ):
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
                         * Select the sub types for each super type.
                         *
                         */
                        if( !empty( $superTypes ) ):
                            for( $j = 0; $j < $totalSuperTypes; $j++ ):
                                /**
                                 * Select sub types for current element
                                 */
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

                usort( $orderedElements, array( $this,'sortByPrintOrder' ) );

                return( $orderedElements );

            endif;

        endif;
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

    private function extractAndOrderSuperTypes( $superTypes, $orderedElements )
    {
        $returnData = array();
        if ( !empty( $superTypes ) ):
            foreach( $superTypes as $superType => $subTypes ):
               // $returnData[$superType]['name'] = $superType;

                $totalSubTypes = count( $subTypes );
                if( $totalSubTypes > 0 ):
                    for( $i = 0; $i < $totalSubTypes; $i++ ):
                        $subType = $subTypes['sub'.($i+1)];
                        //$returnData[$superType]['subType'.($i+1)] = $subType;

                        foreach( $orderedElements as $orderedElement ):
                            if( $subType === $orderedElement['name']):
                                $returnData['sub'] = $orderedElement['name'];
                            endif;
                            break;
                        endforeach;

                    /**
                     * TODO: If sub is super, repeat
                     */
                    endfor;
                endif;
            endforeach;

            return( $orderedElements );
        else:
            return( false );
        endif;
    }

    private function buildScreenArray()
    {
        /**
         * Build the screen array which contains all data related to the view.
         */
        $orderedElements = $this->extractAndOrderElements();

        $screenArray = array();
        foreach( $orderedElements as $orderedElement ):
            /**
             * Collect all data
             */
            $superTypes = ( !empty( $orderedElement['super_types'] ) ? $orderedElement['super_types'] : "" );
            if( !empty( $superTypes ) ):
                $extractedAndOrderedSuperTypes = $this->extractAndOrderSuperTypes( $superTypes, $orderedElements );
            endif;
        endforeach;

        return( $extractedAndOrderedSuperTypes );
    }
}