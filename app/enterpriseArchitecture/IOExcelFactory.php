<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 13-Sep-16
 * Time: 10:14
 */

namespace app\enterpriseArchitecture;

require_once( $_SERVER['DOCUMENT_ROOT'].'/app/PHPExcel/Classes/PHPExcel.php');

use app\core\Library;
use PHPExcel_IOFactory as Excel_Factory;

if( !class_exists( "IOExcelFactory" ) ):

    class IOExcelFactory
    {
        protected $type;

        public function __construct( $type )
        {
            $this->type = $type;
        }

        public function request( $params )
        {
            switch( $this->type ):
                case"getUserFile":
                    return( $this->getUserFile() );
                    break;
                case"dataToFile":
                    return( $this->dataToFile( $params ) );
                    break;
                case"getAllDataSetCells":
                    return( $this->getAllDataSetCells( $params ) );
                    break;
            endswitch;
        }

        private function getUserFile()
        {
            $userExcel  = ( new IOXMLExcelUser( "getUserExcel" ) )->request( $params = null );
            if( !empty( $userExcel ) ):
                $excelHash = $userExcel['hash'];
                $excelExt  = $userExcel['ext'];
                $fileName  = APPLICATION_PATH . Library::path('web/files/excel_calculators_tmp/' . $excelHash . '.' . $excelExt);
                return( $fileName );
            endif;
        }

        private function dataToFile( $params )
        {
            $extractedAndOrderedAttributes = $this->extractedAndOrderedAttributes( $params );
            $dataWithDestination           = $this->dataWithDestination( $extractedAndOrderedAttributes, $params['data'], $params );
            $dataSetCells                  = $this->getAllDataSetCells( $dataWithDestination );
            $emptyDataSetCells             = $this->getEmptyDataSetCells( $dataSetCells );
            return( $emptyDataSetCells );
        }

        private function write( $params )
        {
            $data           = $params['data'];
            $userId         = ( !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
            $userExcelHash  = sha1( $userId );
            $excelHash      = ( isset( $params['excelHash'] ) ?  $params['excelHash'] : "" );
            $excelExt       = ( isset( $params['excelExt'] ) ?  $params['excelExt'] : "" );
            $fileName       = $this->getUserFile( $params );

            /**
             * Check if the excel hash matches the user excel hash
             */
            if( $userExcelHash === $excelHash && !empty( $excelExt )):

                $fileType = Excel_Factory::identify( $fileName );

                $objReader = Excel_Factory::createReader( $fileType );
                $objPHPExcel = $objReader->load( $fileName );

                foreach( $data as $sheetName => $sheet ):
                    foreach( $sheet as $item ):
                        $sheet = ( !empty( $item['tab'] ) ? str_replace( " ", "", $item['tab'] ) : ""  );
                        $objPHPExcel->setActiveSheetIndexByName($sheet)->setCellValue($item['cell'], $item['value']);
                    endforeach;
                endforeach;

                $objWriter = Excel_Factory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->setPreCalculateFormulas(false);

                return( empty( $objWriter->save( $fileName ) ) ? true : false );

            endif;
        }

        /**
         * @param $data
         * @return array|bool
         *
         * Reads the entire data set specified by excel ranges and specific to the element and sheet.
         */
        private function getAllDataSetCells( $data )
        {
            $returnData  = array();
            $fileName    = $this->getUserFile();
            $fileType    = Excel_Factory::identify( $fileName );
            $objReader   = Excel_Factory::createReader( $fileType );
            $objPHPExcel = $objReader->load( $fileName );

            foreach( $data as $sheet => $set ):
                /**
                 * - Get the excel type tab and cell.
                 * - Prepare if for reading
                 */
                $excelType       = $set[0]['excelTypeCell'];
                $excelTab        = $set[0]['excelTypeTab'];
                $typeRange       = explode( ":", $excelType );
                $typeStart       = $typeRange[0];
                $typeEnd         = $typeRange[1];
                $typeStartStr    = strtoupper( preg_replace( "/[^a-zA-Z]+/", "", $typeStart ) );
                $typeEndStr      = strtoupper( preg_replace( "/[^A-Z]+/", "", $typeEnd ) );
                $typeStartNumber = (int) filter_var($typeStart, FILTER_SANITIZE_NUMBER_INT);
                $typeEndNumber   = (int) filter_var($typeEnd, FILTER_SANITIZE_NUMBER_INT);

                for( $k = $typeStartNumber; $k <= $typeEndNumber; $k++ ):
                    $cell = $typeStartStr . $k;
                    $objPHPExcel->setActiveSheetIndexByName( $excelTab );
                    $returnData[$typeStartStr][$k] = $objPHPExcel->getActiveSheet()->getCell( $cell )->getCalculatedValue();
                endfor;

                /**
                 * Prepare each data set for reading
                 */
                $totalSets = count( $set );
                for( $i = 0; $i < $totalSets; $i++ ):

                    if( !empty( $set[$i] ) && !empty( $set[$i]['tab'] ) && !empty( $set[$i]['cell'] ) ):
                        $tab         = trim( $set[$i]['tab'] );
                        $range       = explode( ":", $set[$i]['cell'] );
                        $rangeStart  = $range[0];
                        $rangeEnd    = $range[1];
                        $startStr    = strtoupper( preg_replace( "/[^a-zA-Z]+/", "", $rangeStart ) );
                        $EndStr      = strtoupper( preg_replace( "/[^A-Z]+/", "", $rangeEnd ) );
                        $startNumber = (int) filter_var($rangeStart, FILTER_SANITIZE_NUMBER_INT);
                        $endNumber   = (int) filter_var($rangeEnd, FILTER_SANITIZE_NUMBER_INT);

                        for( $j = $startNumber; $j <= $endNumber; $j++ ):
                            $cell = $startStr . $j;
                            $objPHPExcel->setActiveSheetIndexByName( $tab );
                            $returnData[$startStr][$j] = $objPHPExcel->getActiveSheet()->getCell( $cell )->getCalculatedValue();
                        endfor;

                    endif;
                endfor;

            endforeach;

            if( !empty( $returnData ) ):
                ksort($returnData);
                return( $returnData );
            else:
                return( false );
            endif;
        }

        private function getEmptyDataSetCells( $dataSetCells )
        {
            $returnData = array();
            $maxCells   = array();
            foreach( $dataSetCells as $cellString => $cellNumbers ):
                $maxCells[$cellString] = max(array_keys($cellNumbers));
            endforeach;

            foreach( $maxCells as $cellString => $maxNumber ):
                for( $i = 0; $i <= $maxCells[$cellString]; $i-- ):
                    echo $i;
                endfor;
            endforeach;

            return( $maxCells );
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
                        $fields             = ( isset( $element['formDetails']['elementAttributes'][$elementName] ) ? $element['formDetails']['elementAttributes'][$elementName] : array() );
                        $excelTypeFile      = ( isset( $element['supertype']['excelTypeLocation']['file'] ) ? $element['supertype']['excelTypeLocation']['file'] : "" );
                        $excelTypeTab       = ( isset( $element['supertype']['excelTypeLocation']['tab'] ) ? $element['supertype']['excelTypeLocation']['tab'] : "" );
                        $excelTypeCell      = ( isset( $element['supertype']['excelTypeLocation']['cell'] ) ? $element['supertype']['excelTypeLocation']['cell'] : "" );

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

                                            $file = ( isset( $tags[$j]['file'] ) ? $tags[$j]['file'] : "" );
                                            $tab  = ( isset( $tags[$j]['tab'] ) ? $tags[$j]['tab'] : "" );
                                            $cell = ( isset( $tags[$j]['cell'] ) ? $tags[$j]['cell'] : "" );

                                            $extractedAndOrderedAttributes[$i]['file'] = $file;
                                            $extractedAndOrderedAttributes[$i]['tab']  = $tab;
                                            $extractedAndOrderedAttributes[$i]['cell'] = $cell;

                                            /**
                                             * Type
                                             */
                                            $extractedAndOrderedAttributes[$i]['excelTypeFile'] = $excelTypeFile;
                                            $extractedAndOrderedAttributes[$i]['excelTypeTab']  = $excelTypeTab;
                                            $extractedAndOrderedAttributes[$i]['excelTypeCell'] = $excelTypeCell;
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

                                    $file = ( isset( $field['file'] ) ? $field['file'] : "" );
                                    $tab  = ( isset( $field['tab'] ) ? $field['tab'] : "" );
                                    $cell = ( isset( $field['cell'] ) ? $field['cell'] : "" );

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

        private function dataWithDestination( $attributes, $data, $params )
        {
            $elementName         = ( !empty( $params['element_name'] ) ? $params['element_name'] : "" );
            $dataWithDestination = array();

            if( !empty( $attributes ) && !empty( $data[0] ) ):
                $i = 0;
                foreach( $data[0] as $key => $value ):
                    if( $key !== "id" && $key !== "user_id" ):
                        foreach( $attributes as $attribute ):
                            if( $key === strtolower( str_replace( " ", "_", trim( $attribute['name'] ) ) ) ):
                                $tab  = ( isset( $attribute['tab'] ) ? trim( $attribute['tab'] ) : "" );
                                $file = ( isset( $attribute['file'] ) ? trim( $attribute['file'] ) : "" );
                                $cell = ( isset( $attribute['cell'] ) ? trim( $attribute['cell'] ) : "" );

                                $excelFile  = ( isset( $attribute['excelTypeFile'] ) ? trim( $attribute['excelTypeFile'] ) : "" );
                                $excelTab = ( isset( $attribute['excelTypeTab'] ) ? trim( $attribute['excelTypeTab'] ) : "" );
                                $excelCell = ( isset( $attribute['excelTypeCell'] ) ? trim( $attribute['excelTypeCell'] ) : "" );

                                $dataWithDestination[$tab][$i]['name']  = $key;
                                $dataWithDestination[$tab][$i]['file']  = $file;
                                $dataWithDestination[$tab][$i]['tab']   = $tab;
                                $dataWithDestination[$tab][$i]['cell']  = $cell;
                                $dataWithDestination[$tab][$i]['value'] = $value;
                                $dataWithDestination[$tab][$i]['type']  = $elementName;

                                $dataWithDestination[$tab][$i]['excelTypeFile']  = $excelFile;
                                $dataWithDestination[$tab][$i]['excelTypeTab']   = $excelTab;
                                $dataWithDestination[$tab][$i]['excelTypeCell']  = $excelCell;

                                $i++;
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endif;

            return($dataWithDestination);
        }
    }

endif;