<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 25-Jul-16
 * Time: 21:09
 */

namespace app\enterpriseArchitecture;

class IOXMLEAScreenFactoryOld
{

    protected $xmlModelId;

    /**
     * IOXMLEAScreenFactory constructor.
     * @param $xmlModelId
     */
    public function __construct($xmlModelId )
    {
        $this->xmlModelId = $xmlModelId;
    }

    /**
     * @param $parsedElements
     * @return array
     */
    public function extractElementNames( $parsedElements )
    {
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

    private function sortElements( $a, $b )
    {
        if( !empty( $a['order']['order'] ) && !empty( $b['order']['order'] ) ):
            return( strnatcmp( $a['order']['order'], $b['order']['order'] ) );
        endif;
    }

    /**
     * @return array|bool
     */
    public function extractAndOrderElements()
    {
        $modelData = ( new IOXMLEAModel( $this->xmlModelId ) )->getModel();

        if( !empty( $modelData ) ):

            if( !empty( $modelData['hash'] ) ):

                $xmlFile          = 'web/files/xml_models_tmp/' . $modelData['hash'] . '.' . $modelData['ext'];
                $parsedElements   = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses();
                $elementNames     = $this->extractElementNames( $parsedElements );
                $orderedElements  = array();
                $highestOrder     = 0;

                $i = 0;
                foreach( $elementNames as $elementName ):

                    $class          = ( isset( $parsedElements[$elementName] ) && $parsedElements[$elementName]['type'] === "uml:Class" ? $parsedElements[$elementName] : "" );
                    $idref          = ( isset( $class['idref'] ) ? $class['idref'] : "" );
                    $root           = ( isset( $class['Root'] ) ? $class['Root'] : false );
                    $abstract       = ( isset( $class['abstract'] ) ? $class['abstract'] : false );
                    $name           = ( isset( $class['name'] ) ? $class['name'] : "" );
                    $tags           = ( isset( $class['tags'] ) ? $class['tags'] : false );
                    $order          = ( isset( $tags['QR-PrintOrder'] ) ? $tags['QR-PrintOrder'] : "");
                    $documentation  = ( isset( $class['documentation'] ) ? $class['documentation'] : "" );
                    $attributes     = ( isset( $class['attributes'] ) ? $class['attributes'] : false );
                    $operations     = ( isset( $class['operations'] ) ? $class['operations'] : "" );

                    $source         = $this->getMatchingConnector( $idref, "source" );
                    $target         = $this->getMatchingConnector( $idref, "target" );

                    if( !empty( $order ) ):
                        $orderedElements[$i]['model_id']      = $this->xmlModelId;
                        $orderedElements[$i]['idref']         = $idref;
                        $orderedElements[$i]['name']          = $name;
                        $orderedElements[$i]['root']          = $root;
                        $orderedElements[$i]['abstract']      = $abstract;
                        $orderedElements[$i]['order']         = $order;
                        $orderedElements[$i]['documentation'] = $documentation;
                        $orderedElements[$i]['attributes']    = $attributes;
                        $orderedElements[$i]['operations']    = $operations;

                        $highestOrder =  $order;

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

                        $i++;

                    endif;

                endforeach;

                usort( $orderedElements, array( $this,'sortElements' ) );

                $orderedElements['highestOrder'] = $highestOrder;
                return( $orderedElements );

            else:
                return( false );
            endif;

        else:
            return( false );
        endif;

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
                    $sourceName              = $parsedConnectors['connectors']['connector'.($j+1)]['source']['model']['name'];
                    $sourceModelType         = $parsedConnectors['connectors']['connector'.($j+1)]['source']['model']['type'];
                    $sourceModelEALocalId    = $parsedConnectors['connectors']['connector'.($j+1)]['source']['model']['ea_localid'];
                    $sourceModeMultiplicity  = $parsedConnectors['connectors']['connector'.($j+1)]['source']['model']['multiplicity'];
                    $sourceModelAggregation  = $parsedConnectors['connectors']['connector'.($j+1)]['source']['model']['aggregation'];
                    $sourceArray             = array(
                        "id"            => $source,
                        "name"          => $sourceName,
                        "type"          => $sourceModelType,
                        "ea_localId"    => $sourceModelEALocalId,
                        "multiplicity"  => $sourceModeMultiplicity,
                        "aggregation"   => $sourceModelAggregation
                    );

                    $target                  = $parsedConnectors['connectors']['connector'.($j+1)]['target']['idref'];
                    $targetName              = $parsedConnectors['connectors']['connector'.($j+1)]['target']['model']['name'];
                    $targetModelType         = $parsedConnectors['connectors']['connector'.($j+1)]['target']['model']['type'];
                    $targetModelEALocalId    = $parsedConnectors['connectors']['connector'.($j+1)]['target']['model']['ea_localid'];
                    $targetModeMultiplicity  = $parsedConnectors['connectors']['connector'.($j+1)]['target']['model']['multiplicity'];
                    $targetModelAggregation  = $parsedConnectors['connectors']['connector'.($j+1)]['target']['model']['aggregation'];
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


    /**
     * @param $operations
     * @return array
     */
    private function extractAndOrderOperations($operations )
    {
        $operationsArray = array();
        $totalOperations = count( $operations );

        for( $i = 0; $i < $totalOperations; $i++ ):
            $operationName          = $operations['operation'.($i+1)]['name'];
            $operationDocumentation = $operations['operation'.($i+1)]['documentation'];

            $operationsArray[$i]['name']          = $operationName;
            $operationsArray[$i]['documentation'] = $operationDocumentation;
        endfor;

        return( $operationsArray );

    }

    /**
     * @return string
     */
    public function createIntro()
    {
        $class   = $this->xmlModelId;
        $title   = ( isset( $class['name'] ) ? $class['name'] : "" );
        $intro   = ( isset( $class['documentation'] ) ? $class['documentation'] : "" );

        $element = '<div class="element">';

        if( !empty( $class['operations'] ) ):

            $operations        = $class['operations'];
            $orderedOperations = $this->extractAndOrderOperations( $operations );

            $element .= '<div class="elementIntro-title">'. $title .'</div>';
            $element .= '<div class="elementIntro-txt"><p>'. $intro .'<p></div>';

            foreach( $orderedOperations as $orderedOperation ):
                $element .= '<div class="elementIntro-subTitle">'. $orderedOperation['name'] .'</div>';
                $element .= '<div class="elementIntro-subIntro"><p>'. $orderedOperation['documentation'] .'</p></div>';
            endforeach;

            $element .= '<div class="elementIntro-next"><a href="' . APPLICATION_HOME . '?model&page=' . ( $class['order']['order'] ) . '" class="button">Next</a></div>';

        endif;

        $element .= '</div>';

        return( $element );

    }

    /**
     * @return string
     */
    public function createElement()
    {
        $class           = $this->xmlModelId;
        $title           = $class['name'];
        $documentation   = $class['documentation'];

        $element  = '<div class="element">';
            $element .= '<div class="element-title">'. $title .'</div>';
            $element .= '<div class="element-documentation"><p>'. $documentation .'</p></div>';
            $element .= $this->createForm( $class );
        $element .= '</div>';

        return( $element );
    }

    /**
     * @param $class
     * @return string
     */
    private function createForm ( $class )
    {
        $elementName  = ( isset( $class['name'] ) ? $class['name'] : "" );
        $target       = ( isset( $class['supertype'] ) ? $class['supertype'] : "" );
        $targetFields = ( isset( $target['attributes'] ) ? $target['attributes'] : "" );
        $fields       = ( isset( $class['attributes'] ) ? $class['attributes'] : "" );

        $form = '<form action="' . APPLICATION_HOME . '" method="post" class="element-form">';

        if( !empty( $targetFields ) ):
            foreach( $targetFields as $targetField ):
                if( !empty( $targetField ) ):

                    $inputName        = ( isset( $targetField['input_name'] ) ? $targetField['input_name'] : "" );
                    $inputInfo        = ( isset( $targetField['documentation'] ) ? $targetField['documentation'] : "" );
                    $inputPlaceholder = ( isset( $targetField['initialValue'] ) ? $targetField['initialValue'] : "" );
                    $inputDataType    = ( isset( $targetField['data_type'] ) ? $targetField['data_type'] : "" );
                    $inputFieldType   = ( new IOXMLEAAttributeTypes( $class['model_id'], $inputDataType ) )->fieldType();

                    $form .= '<div class="element-input-box">';

                        if( !empty( $inputName ) ):
                            $form .= '<div class="element-input-name">' . $inputName . '</div>';
                            if( !empty( $inputFieldType ) ):
                                switch( $inputFieldType ):
                                    case"PrimitiveType":
                                    case"DataType":
                                        $form .= '<input type="text" name="' . $inputName . '" value="" placeholder="' . $inputPlaceholder . '">';
                                        break;
                                    case"Enumeration":

                                        $params['model_id'] = $class['model_id'];
                                        $enumerations       = ( new IOXMLEAEnumerations( "getEnumerations", $inputDataType ) )->request( $params );

                                        $form .= '<select name="' . $inputName . '">';

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

        if( !empty( $fields ) ):
            foreach( $fields as $field ):
                if( !empty( $field ) ):
                    $inputName        = ( isset( $field['input_name'] ) ? $field['input_name'] : "" );
                    $inputInfo        = ( isset( $field['documentation'] ) ? $field['documentation'] : "" );
                    $inputPlaceholder = ( isset( $field['initialValue'] ) ? $field['initialValue'] : "" );
                    $inputDataType    = ( isset( $field['data_type'] ) ? $field['data_type'] : "" );
                    $inputFieldType   = ( new IOXMLEAAttributeTypes( $class['model_id'], $inputDataType ) )->fieldType();

                    $form .= '<div class="element-input-box">';

                    if( !empty( $inputName ) ):
                        $form .= '<div class="element-input-name">' . $inputName . '</div>';
                        if( !empty( $inputFieldType ) ):
                            switch( $inputFieldType ):
                                case"PrimitiveType":
                                case"DataType":
                                    $form .= '<input type="text" name="' . $inputName . '" value="" placeholder="' . $inputPlaceholder . '">';
                                    break;
                                case"Enumeration":
                                    $params['model_id'] = $class['model_id'];
                                    $enumerations       = ( new IOXMLEAEnumerations( "getEnumerations", $inputDataType ) )->request( $params );

                                    $form .= '<select name="' . $inputName . '">';

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

        $form .= '<div class="element-input-box">';
            $form .= '<div class="element-input-submit">';
            /**
             * TODO: Get the highest order to determine the max element to display previous button
             * Display previous button if the class order is bigger then one
             */
            if( $class['order']['order'] > 1 ):
                $form .= '<a href="' . APPLICATION_HOME . '?model&page=' . ( $class['order']['order'] - 2 ) . '" class="button">previous</a>';
            endif;
            $form .= '<input type="hidden" name="elementName" value="' . $elementName . '">';
            $form .= '<input type="hidden" name="modelId" value="' . $class['model_id'] . '">';
            $form .= '<input type="hidden" name="elementOrder" value="' . $class['order']['order'] . '">';
            $form .= '<input type="hidden" name="path" value="screenFactory">';
            $form .= '<input type="hidden" name="attr" value="newClass">';
            $form .= '<input type="submit" name="submit" value="next" class="button">';
            $form .= '</div>';
        $form .= '</div>';

         $form .= '</form>';

        return( $form );

    }

}
