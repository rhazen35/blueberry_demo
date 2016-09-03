<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 16-Aug-16
 * Time: 22:59
 */

namespace app\api\ea;

use app\enterpriseArchitecture\IOXMLEAInheritance;
use app\enterpriseArchitecture\IOXMLEAModel;
use app\enterpriseArchitecture\IOXMLEAModelParser;
use app\enterpriseArchitecture\IOXMLEAScreenFactory;
use app\enterpriseArchitecture\XMLDBController;
use app\model\Service;

class EAApi
{
    protected $type;
    protected $dbName = "blueberry";

    public function __construct( $type )
    {
        $this->type = $type;
    }
    /**
     * @param $params
     * @return array|bool|\mysqli_result
     */
    public function request($params )
    {
        switch( $this->type ):
            case"get_all_models":
                return( $this->get_all_models( $params ) );
                break;
            case"get_all_models_detailed":
                return( $this->get_all_models_detailed( $params ) );
                break;
            case"get_all_models_detailed_extracted_and_ordered":
                return( $this->get_all_models_detailed_extracted_and_ordered( $params ) );
                break;
            case"get_all_models_elements_relations":
                return( $this->get_all_models_elements_relations( $params ) );
                break;
            case"get_all_models_elements_extracted_and_ordered_attributes":
                return( $this->get_all_models_elements_extracted_and_ordered_attributes( $params ) );
                break;
            case"get_all_models_database_data":
                return( $this->get_all_models_database_data( $params ) );
                break;
            case"get_all_models_data_with_excel_tags":
                return( $this->get_all_models_data_with_excel_tags( $params ) );
                break;
            case"get_model_data_with_destination":
                return( $this->get_model_data_with_destination( $params ) );
                break;
        endswitch;
    }

    private function get_all_models( $params )
    {
        $dbName      = ( !empty( $params['dbName'] ) ? $params['dbName'] : $this->dbName );
        $sql         = "CALL proc_ea_api_get_all_models()";
        $data        = array();
        $format      = array();
        $type        = "read";
        $returnData  = ( new Service( $type, $dbName ) )->dbAction( $sql, $data, $format );
        return( $returnData );
    }

    private function get_all_models_detailed( $params )
    {
        $returnArray = array();
        $models = $this->get_all_models( $params );
        if( !empty( $models ) ):
            foreach( $models as $model ):
                $modelId = ( !empty( $model['id'] ) ? $model['id'] : "" );
                $model   = ( new IOXMLEAModel( $modelId ) )->getModel();
                if( !empty( $model ) ):
                    $modelName = $model['name'];
                    $modelHash = $model['hash'];
                    $modelExt  = $model['ext'];
                    if( !empty( $modelHash ) && !empty( $modelExt ) ):
                        $xmlFile = "./web/files/xml_models/" . $modelHash . "." . $modelExt;
                        $parsedElements = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses();
                        $returnArray[$modelName] = $parsedElements;
                    endif;
                endif;
            endforeach;
            return( $returnArray );
        endif;
    }

    private function get_all_models_detailed_extracted_and_ordered( $params )
    {
        $returnArray = array();
        $models = $this->get_all_models( $params );
        if( !empty( $models ) ):
            foreach( $models as $model ):
                $modelName                   = $model['name'];
                $modelId                     = ( !empty( $model['id'] ) ? $model['id'] : "" );
                $extractedAndOrderedElements = ( new IOXMLEAScreenFactory( "extractAndOrderElements", $modelId ) )->request( $params = null );
                $returnArray[$modelName]     = $extractedAndOrderedElements;
            endforeach;
            return( $returnArray );
        endif;
    }

    private function get_all_models_elements_relations( $params )
    {
        $returnArray = array();
        $models = $this->get_all_models( $params );
        if( !empty( $models ) ):
            foreach( $models as $model ):
                $modelName                   = $model['name'];
                $modelId                     = ( !empty( $model['id'] ) ? $model['id'] : "" );
                $elementsRelations           = ( new IOXMLEAInheritance( "buildRelations", $modelId ))->request();
                $returnArray[$modelName]     = $elementsRelations;
            endforeach;
            return( $returnArray );
        endif;
    }

    private function get_all_models_elements_extracted_and_ordered_attributes( $params )
    {
        $extractedAndOrderedAttributes = array();
        $extractedAndOrderedModels = $this->get_all_models_detailed_extracted_and_ordered( $params );

        if( !empty( $extractedAndOrderedModels ) ):
            $a = 0;
            foreach( $extractedAndOrderedModels as $model => $extractedAndOrderedElements ):
                if( !empty( $extractedAndOrderedElements[$a] ) ):
                    $modelName = $model;
                    foreach( $extractedAndOrderedElements as $extractedAndOrderedElement ):
                        $elementName        = ( isset( $extractedAndOrderedElement['name'] ) ? $extractedAndOrderedElement['name'] : "" );
                        $target             = ( isset( $extractedAndOrderedElement['supertype'] ) ? $extractedAndOrderedElement['supertype'] : "" );
                        $targetFields       = ( isset( $target['attributes'] ) ? $target['attributes'] : "" );
                        $fields             = ( isset( $extractedAndOrderedElement['formDetails']['elementAttributes'][$elementName] ) ? $extractedAndOrderedElement['formDetails']['elementAttributes'][$elementName] : "" );

                        $i = 0;
                        /**
                         * Super type attributes
                         */
                        if( !empty( $targetFields ) ):
                            $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i] = array();
                            foreach( $targetFields as $targetField ):
                                if( !empty( $targetField ) ):
                                    $name      = ( isset( $targetField['input_name'] ) ? $targetField['input_name'] : "" );
                                    $dataType  = ( isset( $targetField['data_type'] ) ? $targetField['data_type'] : "" );
                                    $tags      = ( isset( $targetField['tags'] ) ? $targetField['tags'] : "" );
                                    if( !empty( $name ) ):
                                        $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['name']      = $name;
                                        $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['data_type'] = $dataType;
                                    endif;
                                    $totalTags = count( $tags );
                                    if( !empty( $tags ) && $totalTags > 0 ):
                                        for( $j = 0; $j < $totalTags; $j++ ):
                                            $file      = ( isset( $tags[$j]['file'] ) ? $tags[$j]['file'] : "" );
                                            $tab       = ( isset( $tags[$j]['tab'] ) ? $tags[$j]['tab'] : "" );
                                            $cell      = ( isset( $tags[$j]['cell'] ) ? $tags[$j]['cell'] : "" );
                                            $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['file'] = $file;
                                            $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['tab']  = $tab;
                                            $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['cell'] = $cell;
                                        endfor;
                                    endif;
                                    $i++;
                                endif;
                            endforeach;
                         endif;
                        /**
                         * Element attributes
                         */
                        if( !empty( $fields ) ):
                            foreach( $fields as $field ):
                                if( !empty( $field ) ):
                                    $name      = ( isset( $field['name'] ) ? $field['name'] : "" );
                                    $dataType  = ( isset( $field['data_type'] ) ? $field['data_type'] : "" );
                                    if( !empty( $name ) ):
                                        $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['name']      = $name;
                                        $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['data_type'] = $dataType;
                                    endif;

                                    $file      = ( isset( $field['file'] ) ? $field['file'] : "" );
                                    $tab       = ( isset( $field['tab'] ) ? $field['tab'] : "" );
                                    $cell      = ( isset( $field['cell'] ) ? $field['cell'] : "" );
                                    $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['file'] = $file;
                                    $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['tab']  = $tab;
                                    $extractedAndOrderedAttributes[$modelName][$elementName]['attributes'][$i]['cell'] = $cell;
                                endif;
                                $i++;
                            endforeach;
                        endif;
                    endforeach;
                endif;
                $a++;
            endforeach;
        endif;

        return( $extractedAndOrderedAttributes );
    }

    private function get_all_models_database_data( $params )
    {
        $modelsDatabaseData = array();
        $models = $this->get_all_models_detailed_extracted_and_ordered( $params );

        if( !empty( $models ) ):
            foreach( $models as $model ):
                $totalElements = count( $model );
                if( !empty( $model ) && $totalElements > 0 ):
                    for( $i = 0; $i < $totalElements; $i++ ):
                        if( !empty( $model[$i] )):
                            if( $model[$i]['isRoot'] !== "true" ):
                                $modelId = $model[$i]['model_id'];
                                $modelName = ( new IOXMLEAModel( $modelId ) )->getModelNameById();
                                $elementName = ( !empty( $model[$i]['name'] ) ? $model[$i]['name'] : "" );
                                if( !empty( $modelName['name'] ) ):
                                    $params['elements'] = $model;
                                    $params['element_name'] = $model[$i]['name'];
                                    $params['multiplicity'] = $model[$i]['multiplicity'];
                                    $data = ( new XMLDBController( "read" ) )->request( $params );
                                    if( !empty( $data ) ):
                                        $modelsDatabaseData[$modelName['name']][$elementName]['attributeValues'] = $data;
                                    endif;
                                endif;
                            endif;
                        endif;
                    endfor;
                endif;
            endforeach;
        endif;
        return($modelsDatabaseData);
    }

//    private function get_all_models_data_with_excel_tags( $params )
//    {
//        $orderedAttributes  = $this->get_all_models_elements_extracted_and_ordered_attributes( $params );
//        $modelsDatabaseData = $this->get_all_models_database_data( $params );
//
//        foreach($orderedAttributes as $model => $elements ):
//            foreach( $elements as $elementName => $element ):
//                if( !empty( $modelsDatabaseData[ $model ][ $elementName ] ) ):
//                    foreach( $modelsDatabaseData[ $model ][ $elementName ] as $data_attributes ):
//                        if( !empty( $data_attributes ) ):
//                            foreach( $element['attributes'] as $attributes ):
//                                foreach( $attributes as $attribute ):
//                                    $i = 0;
//                                    foreach( $data_attributes as $data_attribute_name => $data ):
//                                        if( strtolower( str_replace( "_", " ", $attribute ) ) === $data_attribute_name ):
//                                            $orderedAttributes[$model][$elementName]['attributes'][$i]['value'] = $data;
//                                        endif;
//                                        $i++;
//                                    endforeach;
//                                endforeach;
//                            endforeach;
//                        endif;
//                    endforeach;
//                endif;
//            endforeach;
//        endforeach;
//
//        return($orderedAttributes);
//    }

    private function get_all_models_data_with_excel_tags( $params )
    {
        $orderedAttributes  = $this->get_all_models_elements_extracted_and_ordered_attributes( $params );
        $modelsDatabaseData = $this->get_all_models_database_data( $params );

        $dataWithDestination = array();

        if( !empty( $modelsDatabaseData ) ):
            foreach($modelsDatabaseData as $model => $data):
                foreach( $data as $dataElementName => $dataElement ):
                    $i = 0;
                    foreach( $orderedAttributes as $modelName => $orderedElement ):
                        if( $model === $modelName && !empty( $dataElement['attributeValues'] ) ):
                            foreach( $dataElement['attributeValues'] as $dataAttributes ):
                                if( !empty( $dataAttributes ) ):
                                    $j = 0;
                                    foreach( $dataAttributes as $dataAttributeKey => $dataAttributeValue ):
                                        if( $dataAttributeKey !== "id" && $dataAttributeKey !== "user_id" ):
                                            if( !empty( $orderedElement[$dataElementName]['attributes'] ) ):
                                                foreach( $orderedElement[$dataElementName]['attributes'] as $orderedAttribute ):
                                                    if( $dataAttributeKey === strtolower( str_replace( " ", "_", $orderedAttribute['name'] ) ) ):
                                                        $tab = ( !empty( $orderedAttribute['tab'] ) ? $orderedAttribute['tab'] : "" );
                                                        $dataWithDestination[$model][$tab][$dataElementName][$i][$j]['file']  = ( !empty( $orderedAttribute['file'] ) ? $orderedAttribute['file'] : "" );
                                                        $dataWithDestination[$model][$tab][$dataElementName][$i][$j]['tab']   = $tab;
                                                        $dataWithDestination[$model][$tab][$dataElementName][$i][$j]['cell']  = ( !empty( $orderedAttribute['cell'] ) ? $orderedAttribute['cell'] : "" );
                                                        $dataWithDestination[$model][$tab][$dataElementName][$i][$j]['value'] = $dataAttributeValue;
                                                    endif;
                                                endforeach;
                                            endif;
                                            $j++;
                                        endif;
                                    endforeach;
                                endif;
                                $i++;
                            endforeach;
                        endif;
                    endforeach;
                endforeach;
            endforeach;
        endif;

        return( $dataWithDestination );
    }

    private function get_model_data_with_destination( $params )
    {
        $modelName  = ( !empty( $params['modelName'] ) ? $params['modelName'] : "" );
        $modelsData = $this->get_all_models_data_with_excel_tags( $params );
        foreach( $modelsData as $model => $modelData ):
            if( $model === $modelName ):
               return($modelData);
                break;
            endif;
        endforeach;

    }
}