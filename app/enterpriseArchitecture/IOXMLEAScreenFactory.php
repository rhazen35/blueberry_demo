<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 25-Jul-16
 * Time: 21:09
 */

namespace app\enterpriseArchitecture;

use app\enterpriseArchitecture\IOXMLEAModel;
use app\enterpriseArchitecture\IOXMLModelParser;
use app\enterpriseArchitecture\IOXMLEAAttributeTypes;

class IOXMLEAScreenFactory
{

    protected $xmlModelId;

    public function __construct( $xmlModelId )
    {
        $this->xmlModelId = $xmlModelId;
    }

    private function extractElementNames( $parsedElements )
    {
        $elementNames = array();

        foreach( $parsedElements as $parsedElement ):

            /**
             * Get all classes names
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

    public function extractAndOrderElements()
    {

        $modelData = ( new IOXMLEAModel( $this->xmlModelId ) )->getModel();

        if( !empty( $modelData ) ):

            if( !empty( $modelData['hash'] ) ):
                $xmlFile          = 'web/files/xml_models_tmp/' . $modelData['hash'] . '.' . $modelData['ext'];
                $parsedElements   = ( new IOXMLModelParser( $xmlFile ) )->parseXMLClasses();

                $elementNames = $this->extractElementNames( $parsedElements );
                $orderedElements = array();

                $i = 0;
                foreach( $elementNames as $elementName ):

                    $class          = ( isset( $parsedElements[$elementName] ) && $parsedElements[$elementName]['type'] === "uml:Class" ? $parsedElements[$elementName] : "" );
                    $fields         = ( isset( $class['attributes'] ) ? $class['attributes'] : false );
                    $root           = ( isset( $class['Root'] ) ? $class['Root'] : false );
                    $name           = ( isset( $class['name'] ) ? $class['name'] : "" );
                    $tags           = ( isset( $class['tags'] ) ? $class['tags'] : false );
                    $order          = ( isset( $tags['QR-PrintOrder'] ) ? $tags['QR-PrintOrder'] : "");
                    $documentation  = ( isset( $class['documentation'] ) ? $class['documentation'] : "" );
                    $idref          = ( isset( $class['idref'] ) ? $class['idref'] : "" );
                    $operations     = ( isset( $class['operations'] ) ? $class['operations'] : "" );
                    $target         = $this->getMatchingConnector( $idref );

                    if( !empty( $order ) ):

                        $orderedElements[$i]['model_id']      = $this->xmlModelId;
                        $orderedElements[$i]['name']          = $name;
                        $orderedElements[$i]['idref']         = $idref;
                        $orderedElements[$i]['order']         = $order;
                        $orderedElements[$i]['root']          = $root;
                        $orderedElements[$i]['documentation'] = $documentation;
                        $orderedElements[$i]['attributes']    = $fields;
                        $orderedElements[$i]['operations']    = $operations;

                        if( !empty( $target ) ):

                            $orderedElements[$i]['supertype']           = array();
                            $orderedElements[$i]['supertype']['id']     = $target['id'];
                            $orderedElements[$i]['supertype']['name']   = $target['name'];

                            $targetClass = $parsedElements[$target['name']];

                            $tags           = ( isset( $targetClass['tags'] ) ? $targetClass['tags'] : false );
                            $order          = ( isset( $tags['QR-PrintOrder'] ) ? $tags['QR-PrintOrder'] : "");
                            $documentation  = ( isset( $targetClass['documentation'] ) ? $targetClass['documentation'] : "" );
                            $idref          = ( isset( $targetClass['idref'] ) ? $targetClass['idref'] : "" );
                            $operations     = ( isset( $targetClass['operations'] ) ? $targetClass['operations'] : "" );
                            $fields         = ( isset( $targetClass['attributes'] ) ? $targetClass['attributes'] : false );
                            $fieldTags      = ( isset( $targetClass['attributes']['tags'] ) ? $targetClass['attributes']['tags'] : false );

                            $orderedElements[$i]['supertype']['idref']              = $idref;
                            $orderedElements[$i]['supertype']['order']              = $order;
                            $orderedElements[$i]['supertype']['documentation']      = $documentation;
                            $orderedElements[$i]['supertype']['attributes']         = $fields;
                            $orderedElements[$i]['supertype']['attributes']['tags'] = $fieldTags;
                            $orderedElements[$i]['supertype']['operations']         = $operations;


                        endif;

                        $i++;

                    endif;

                endforeach;

                usort( $orderedElements, array( $this,'sortElements' ) );

                return( $orderedElements );

            else:

                return( false );

            endif;

        else:

            return( false );

        endif;

    }

    private function getMatchingConnector( $idref )
    {

        $modelData = ( new IOXMLEAModel( $this->xmlModelId ) )->getModel();

        $parsedConnectors = ( new IOXMLModelParser( 'web/files/xml_models_tmp/'.$modelData['hash'].'.xml' ) )->parseConnectors();
        $totalConnectors  = count( $parsedConnectors['connectors'] );

        for( $j = 0; $j < $totalConnectors; $j++ ):

            if( $idref === $parsedConnectors['connectors']['connector'.($j+1)]['source']['idref'] ):

                if( $parsedConnectors['connectors']['connector'.($j+1)]['properties']['ea_type'] === "Generalization" ):

                    $target = $parsedConnectors['connectors']['connector'.($j+1)]['target']['idref'];
                    $targetName = $parsedConnectors['connectors']['connector'.($j+1)]['target']['model']['name'];

                    $targetArray = array( "id" => $target, "name" => $targetName );
                    return( $targetArray );
                    break;

                endif;

            endif;

        endfor;

    }


    private function extractAndOrderOperations( $operations )
    {

        /**
         * Order the operations
         */
        $operationsArray = array();
        $totalOperations = count( $operations );

        for( $i = 0; $i < $totalOperations; $i++ ):

            $operationName          = $operations['operation'.($i+1)]['name'];
            $operationDocumentation = $operations['operation'.($i+1)]['documentation'];

            $operationsArray[$i]['name'] = $operationName;
            $operationsArray[$i]['documentation'] = $operationDocumentation;

        endfor;

        return( $operationsArray );

    }

    public function createIntro()
    {

        $class   = $this->xmlModelId;
        $title   = ( isset( $class['name'] ) ? $class['name'] : "" );
        $intro   = ( isset( $class['documentation'] ) ? $class['documentation'] : "" );

        $element = '<div class="element">';

        /**
         * Order the operations
         */
        if( !empty( $class['operations'] ) ):

            $operations = $class['operations'];
            $orderedOperations = $this->extractAndOrderOperations( $operations );

            $element .= '<div class="elementIntro-title">'. $title .'</div>';
            $element .= '<div class="elementIntro-txt"><p>'. $intro .'<p></div>';

            foreach( $orderedOperations as $orderedOperation ):

                $element .= '<div class="elementIntro-subTitle">'. $orderedOperation['name'] .'</div>';
                $element .= '<div class="elementIntro-subIntro"><p>'. $orderedOperation['documentation'] .'</p></div>';

            endforeach;

            $element .= '<div class="elementIntro-next"><a href="" class="button">Next</a></div>';

        endif;

        $element .= $class['order']['order'];
        $element .= '</div>';

        return( $element );

    }

    public function createElement()
    {

        $class           = $this->xmlModelId;
        $title           = $class['name'];
        $documentation   = $class['documentation'];

        $element  = '<div class="element">';
            $element .= '<div class="element-title">'. $title .'</div>';
            $element .= '<div class="element-documentation"><p>'. $documentation .'</p></div>';
            $element .= $this->createForm( $class );

        $element .= $class['order']['order'];

        $element .= '</div>';

        return( $element );
    }

    private function createForm( $class )
    {

        $target       = ( isset( $class['supertype'] ) ? $class['supertype'] : "" );
        $targetFields = ( isset( $target['attributes'] ) ? $target['attributes'] : "" );
        $fields       = ( isset( $class['attributes'] ) ? $class['attributes'] : "" );

        $form = '<form action="" method="post" class="element-form">';

        if( !empty( $targetFields ) ):
            foreach($targetFields as $targetField):
                if( !empty( $targetField ) ):

                    $inputName        = ( isset( $targetField['input_name'] ) ? $targetField['input_name'] : "" );
                    $inputInfo        = ( isset( $targetField['documentation'] ) ? $targetField['documentation'] : "" );
                    $inputPlaceholder = ( isset( $targetField['initialValue'] ) ? $targetField['initialValue'] : "" );
                    $inputDataType    = ( isset( $targetField['data_type'] ) ? $targetField['data_type'] : "" );
                    $inputFieldType   = ( new IOXMLEAAttributeTypes( $class['model_id'], $inputDataType ) )->fieldType();
                    //$form .= var_dump($inputFieldType);

                    $form .= '<div class="element-input-box">';
                        if( !empty( $inputName ) ):
                            $form .= '<div class="element-input-name">' . $inputName . '</div>';
                            $form .= '<input type="text" name="' . $inputName . '" value="" placeholder="' . $inputPlaceholder . '">';
                        endif;
                        if( !empty( $inputInfo ) ):
                            $form .= '<div class="element-input-hoverImg"><img src="images/icons/info_icon_blue.png"></div>';
                            $form .= '<div class="element-input-hover">' . $inputInfo . '<br>'.$inputDataType.'</div>';
                        endif;
                    $form .= '</div>';

                endif;
            endforeach;
        endif;

        if( !empty( $fields ) ):
            foreach($fields as $field):
                if( !empty( $field ) ):
                    $inputName        = ( isset( $field['input_name'] ) ? $field['input_name'] : "" );
                    $inputInfo        = ( isset( $field['documentation'] ) ? $field['documentation'] : "" );
                    $inputPlaceholder = ( isset( $field['initialValue'] ) ? $field['initialValue'] : "" );
                    $inputDataType    = ( isset( $field['data_type'] ) ? $field['data_type'] : "" );

                    $form .= '<div class="element-input-box">';
                    if( !empty( $inputName ) ):
                        $form .= '<div class="element-input-name">' . $inputName . '</div>';
                        $form .= '<input type="text" name="' . $inputName . '" value="" placeholder="' . $inputPlaceholder . '">';
                    endif;
                    if( !empty( $inputInfo ) ):
                        $form .= '<div class="element-input-hoverImg"><img src="images/icons/info_icon_blue.png"></div>';
                        $form .= '<div class="element-input-hover">' . $inputInfo . '<br>'.$inputDataType.'</div>';
                    endif;
                    $form .= '</div>';

                endif;
            endforeach;
        endif;

        $form .= '<div class="element-input-box">';
            $form .= '<div class="element-input-submit">';
            $form .= '<input type="hidden" name="elementOrder" value="' . $class['order']['order'] . '">';

            /**
             * TODO: Get the highest order to determine the max element to display previous button
             * Display previous button if the class order is bigger then one
             */
            if( $class['order']['order'] > 1 ):
                $form .= '<a href="" class="button">previous</a>';
            endif;
            $form .= '<input type="submit" name="submit" value="next" class="button">';
            $form .= '</div>';
        $form .= '</div>';

         $form .= '</form>';

        return( $form );

    }

}
