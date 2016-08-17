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
     * @return bool|\mysqli_result
     */
    public function request( $params )
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
        endswitch;
    }

    private function get_all_models( $params )
    {
        $dbName      = ( !empty( $params['model'] ) ? $params['model'] : $this->dbName );
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
                        $xmlFile = "./web/files/xml_models_tmp/" . $modelHash . "." . $modelExt;
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
        $extractedAndOrderedModel = $this->get_all_models_detailed_extracted_and_ordered( $params );

        $a = 0;
        foreach( $extractedAndOrderedModel as $extractedAndOrderedElements ):
            $modelName = $extractedAndOrderedElements[$a]['name'];
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
                        endif;
                        $i++;
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
                            $tags      = ( isset( $field['tags'] ) ? $field['tags'] : "" );
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
                        endif;
                        $i++;
                    endforeach;
                endif;
            endforeach;
            $a++;
        endforeach;

        return( $extractedAndOrderedAttributes );
    }

    private function get_all_models_database_data( $params )
    {
        $modelsDatabaseData = array();
        $models = $this->get_all_models_detailed_extracted_and_ordered( $params );

        foreach( $models as $model ):
            $totalElements = count( $model );
            if( !empty( $model ) && $totalElements > 0 ):
                for( $i = 0; $i < $totalElements; $i++ ):
                    if( !empty( $model[$i] )):
                        if( $model[$i]['isRoot'] !== "true" ):
                            $modelId = $model[$i]['model_id'];
                            $modelName = ( new IOXMLEAModel( $modelId ) )->getModelNameById();
                            $elementName = ( !empty( $model[$i]['name'] ) ? $model[$i]['name'] : "" );
                            if( !empty( $modelName ) ):
                                $params['elements'] = $model;
                                $params['element_name'] = $model[$i]['name'];
                                $params['multiplicity'] = $model[$i]['multiplicity'];
                                $data = ( new XMLDBController( "read" ) )->request( $params );
                                if( !empty( $data ) ):
                                    $modelsDatabaseData[$modelName['name']][$i]['element_name'] = $elementName;
                                    $modelsDatabaseData[$modelName['name']][$i]['element_data']['table'] = strtolower( $elementName );
                                    $modelsDatabaseData[$modelName['name']][$i]['element_data']['columns'] = $data[0];
                                endif;
                            endif;
                        endif;
                    endif;
                endfor;
            endif;
        endforeach;
        return($modelsDatabaseData);
    }

    private function get_all_models_data_with_excel_tags( $params )
    {
        $orderedAttributes  = $this->get_all_models_elements_extracted_and_ordered_attributes( $params );
        $modelsDatabaseData = $this->get_all_models_database_data( $params );

        return($modelsDatabaseData);
    }
}