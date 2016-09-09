<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 05-Sep-16
 * Time: 13:10
 */

namespace app\enterpriseArchitecture;

if( !class_exists( "IOElementExcelFactory" ) ):

    class IOElementExcelFactory
    {
        protected $type;

        public function __construct($type)
        {
            $this->type = $type;
        }

        public function request( $params )
        {
            switch( $this->type ):
                case"dataToFile":
                    return( $this->dataToFile( $params ) );
                    break;
                case"getOperations":
                    return( $this->getOperations( $params ) );
                    break;
            endswitch;
        }

        private function dataToFile( $params )
        {
            $data                          = ( isset( $params['data'] ) ? $params['data'] : "" );
            $extractedAndOrderedAttributes = $this->extractedAndOrderedAttributes( $params );
            $dataWithDestination           = $this->dataWithDestination( $extractedAndOrderedAttributes, $data, $params );
            $dataToCell                    = $this->dataToCell( $dataWithDestination, $params );

            $excel = "";
            if( !empty( $dataToCell ) ):
                $params['data'] = $dataToCell;
                $userExcel  = ( new IOXMLExcelUser( "getUserExcel" ) )->request( $params );
                if( !empty( $userExcel ) ):
                    $params['excelHash'] = $userExcel['hash'];
                    $params['excelExt']  = $userExcel['ext'];
                    $excel = ( new IOExcelFactory( "write" ) )->request( $params );
                endif;
            endif;

            return( $excel );
        }

        private function dataWithDestination( $attributes, $data, $params )
        {
            $elementName         = ( !empty( $params['element_name'] ) ? $params['element_name'] : "" );
            $dataWithDestination = array();

            if( !empty( $attributes ) && !empty( $data[0] ) ):
                $i = 0;
                foreach( $data[0] as $key => $value ):
                    if( $key !== "id" && $key !== "user_id" ):
                        foreach( $attributes as $attribute ):
                            if( $key === strtolower( str_replace( " ", "_", $attribute['name'] ) ) ):
                                $tab  = ( isset( $attribute['tab'] ) ? $attribute['tab'] : "" );
                                $file = ( isset( $attribute['file'] ) ? $attribute['file'] : "" );
                                $cell = ( isset( $attribute['cell'] ) ? $attribute['cell'] : "" );

                                $dataWithDestination[$tab][$i]['name']  = $key;
                                $dataWithDestination[$tab][$i]['file']  = $file;
                                $dataWithDestination[$tab][$i]['tab']   = $tab;
                                $dataWithDestination[$tab][$i]['cell']  = $cell;
                                $dataWithDestination[$tab][$i]['value'] = $value;
                                $dataWithDestination[$tab][$i]['type']  = $elementName;

                                $i++;
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endif;

            return($dataWithDestination);
        }

        private function extractedAndOrderedAttributes( $params )
        {
            if( !empty( $params['elements'] ) && !empty( $params['element_name'] ) ):

                $extractedAndOrderedAttributes = array();
                foreach( $params['elements'] as $element ):
                    if( $params['element_name'] === $element['name'] ):
                        $elementName        = $params['element_name'];
                        $target             = ( isset( $element['supertype'] ) ? $element['supertype'] : "" );
                        $targetFields       = ( isset( $target['attributes'] ) ? $target['attributes'] : array() );
                        $fields             = ( isset( $element['formDetails']['elementAttributes'][$elementName] ) ? $element['formDetails']['elementAttributes'][$elementName] : "" );

                        $i = 0;
                        /**
                         * Super type attributes
                         */
                        if( !empty( $targetFields ) ):
                            $extractedAndOrderedAttributes[$i] = array();
                            foreach( $targetFields as $targetField ):
                                if( !empty( $targetField ) ):
                                    $name      = ( isset( $targetField['input_name'] ) ? $targetField['input_name'] : "" );
                                    $dataType  = ( isset( $targetField['data_type'] ) ? $targetField['data_type'] : "" );
                                    $tags      = ( isset( $targetField['tags'] ) ? $targetField['tags'] : "" );
                                    if( !empty( $name ) ):
                                        $extractedAndOrderedAttributes[$i]['name']      = $name;
                                        $extractedAndOrderedAttributes[$i]['data_type'] = $dataType;
                                    endif;
                                    $totalTags = count( $tags );
                                    if( !empty( $tags ) && $totalTags > 0 ):
                                        for( $j = 0; $j < $totalTags; $j++ ):
                                            $file      = ( isset( $tags[$j]['file'] ) ? $tags[$j]['file'] : "" );
                                            $tab       = ( isset( $tags[$j]['tab'] ) ? $tags[$j]['tab'] : "" );
                                            $cell      = ( isset( $tags[$j]['cell'] ) ? $tags[$j]['cell'] : "" );
                                            $extractedAndOrderedAttributes[$i]['file'] = $file;
                                            $extractedAndOrderedAttributes[$i]['tab']  = $tab;
                                            $extractedAndOrderedAttributes[$i]['cell'] = $cell;
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
                                        $extractedAndOrderedAttributes[$i]['name']      = $name;
                                        $extractedAndOrderedAttributes[$i]['data_type'] = $dataType;
                                    endif;

                                    $file      = ( isset( $field['file'] ) ? $field['file'] : "" );
                                    $tab       = ( isset( $field['tab'] ) ? $field['tab'] : "" );
                                    $cell      = ( isset( $field['cell'] ) ? $field['cell'] : "" );
                                    $extractedAndOrderedAttributes[$i]['file'] = $file;
                                    $extractedAndOrderedAttributes[$i]['tab']  = $tab;
                                    $extractedAndOrderedAttributes[$i]['cell'] = $cell;
                                endif;
                                $i++;
                            endforeach;
                        endif;
                    endif;
                endforeach;

            return($extractedAndOrderedAttributes);
            endif;
        }

        private function dataToCell( $data, $params )
        {
            $elementName = ( !empty( $params['element_name'] ) ? $params['element_name'] : "" );
            $ranges      = $this->getCellRangesByTab( $data );

            $dataWithCell = array();

            $k = 0;
            foreach( $data as $sheetName => $sheet ):
                $i = 0;
                $sheetName = ( !empty( $sheetName ) ? trim($sheetName) : "" );
                foreach( $sheet as $element ):

                    $file     = ( isset( $element['file'] ) ? trim( $element['file'] ) : "" );
                    $tab      = ( isset( $element['tab'] ) ? trim( $element['tab'] ) : "" );
                    $cell     = ( isset( $element['cell'] ) ? trim( $element['cell'] ) : "" );
                    $startStr = ( isset( $ranges[$sheetName][$cell]['start_str'] ) ? $ranges[$sheetName][$cell]['start_str'] : "" );
                    $startNum = ( isset( $ranges[$sheetName][$cell]['start_num'] ) ? $ranges[$sheetName][$cell]['start_num'] : "" );

                    if( !empty( $file ) && !empty( $tab ) && !empty( $cell ) ):
                        $dataWithCell[$sheetName][$k]['type']   = $elementName;
                        $dataWithCell[$sheetName][$k]['file']   = $file;
                        $dataWithCell[$sheetName][$k]['tab']    = $tab;
                        $dataWithCell[$sheetName][$k]['cell']   = $startStr . ( (int)$startNum + $i );
                        $dataWithCell[$sheetName][$k]['value']  = $element['value'];
                        $k++;
                    endif;

                endforeach;
            endforeach;

            return( $dataWithCell );
        }

        private function getCellRangesByTab( $data )
        {
            $tabs        = $this->extractTabsOrCells( "tabs", $data );
            $cells       = $this->extractTabsOrCells( "cells", $data );
            $uniqueCells = array_unique( $cells );

            $range = array();
            $i = 0;
            foreach( $tabs as $tab ):
                foreach( $uniqueCells as $uniqueCell ):
                    $parts = explode(':', $uniqueCell);
                    $start = ( isset( $parts[0] ) ? $parts[0] : "" );
                    $end   = ( isset( $parts[1] ) ? $parts[1] : "" );
                    $tab   = ( trim( $tab ) );

                    $range[$tab][$uniqueCell]['start_str'] = preg_replace("/[^a-zA-Z]+/", "", $start);
                    $range[$tab][$uniqueCell]['start_num'] = ( preg_match_all( '/\d+/', $start, $matches ) ? $matches[0][0] : "" );
                    $range[$tab][$uniqueCell]['end_str']   = preg_replace("/[^a-zA-Z]+/", "", $end);
                    $range[$tab][$uniqueCell]['end_num']   = ( preg_match_all( '/\d+/', $end, $matches ) ? $matches[0][0] : "" );
                    $i++;
                endforeach;
            endforeach;

            return( $range );
        }

        private function extractTabsOrCells( $type, $data )
        {
            $returnData = array();
            if( !empty( $data ) ):
                foreach( $data as  $sheetName => $sheet ):
                    foreach( $sheet as $element ):
                        if( !empty( $element ) ):
                            if( !empty( $element ) ):
                                if( $type === "cells" ):
                                    if( !empty( $element['cell'] ) ):
                                        $returnData[] = $element['cell'];
                                    endif;
                                else:
                                    if( $type === "tabs" ):
                                        if( !empty( $element['tab'] ) ):
                                            $returnData[] = $element['tab'];
                                        endif;
                                    endif;
                                endif;
                            endif;
                        endif;
                    endforeach;
                endforeach;
            endif;

            return($returnData);
        }

        private function getOperations( $params )
        {
            $elements    = ( !empty( $params['elements'] ) ? $params['elements'] : array() );
            $elementName = ( !empty( $params['element_name'] ) ? $params['element_name'] : "" );

            $operationArray = array();
            foreach( $elements as $element ):
                if( $elementName === $element['name'] ):

                    $operations = ( !empty( $element['formDetails']['elementOperations'][$elementName] ) ? $element['formDetails']['elementOperations'][$elementName] : "" );

                    if( !empty( $operations ) ):
                        $i = 0;
                        foreach( $operations as $operation ):

                            $operationArray[$i]['name']          = $operation['name'];
                            $operationArray[$i]['documentation'] = $operation['documentation'];
                            $operationArray[$i]['printOrder']    = $operation['printOrder'];

                            $file  = $operation['QR-Excel output']['file'];
                            $tab   = $operation['QR-Excel output']['tab'];
                            $cell  = $operation['QR-Excel output']['cell'];

                            $userExcel  = ( new IOXMLExcelUser( "getUserExcel" ) )->request( $params );
                            if( !empty( $userExcel ) ):
                                $params['excelHash']         = $userExcel['hash'];
                                $params['excelExt']          = $userExcel['ext'];
                                $params['file']              = $file;
                                $params['tab']               = $tab;
                                $params['cell']              = $cell;
                                $excel                       = ( new IOExcelFactory( "readCell" ) )->request( $params );
                                $operationArray[$i]['value'] = ( !empty( $excel ) ? $excel : "" );
                            endif;
                            $i++;
                        endforeach;
                    endif;
                endif;
            endforeach;

            return( $operationArray );
        }

    }

endif;