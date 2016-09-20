<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 13-Sep-16
 * Time: 10:14
 */

namespace app\enterpriseArchitecture;

/**
 * PHP Excel Library
 */
require_once( $_SERVER['DOCUMENT_ROOT'].'/app/PHPExcel/Classes/PHPExcel.php');

use app\core\Library;
use PHPExcel_IOFactory as Excel_Factory;

if( !class_exists( "IOExcelFactory" ) ):

    class IOExcelFactory
    {
        protected $type;
        /**
         * IOExcelFactory constructor.
         * @param $type
         */
        public function __construct($type )
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
                    return( $this->getDataSetCells( $params ) );
                    break;
                case"getOperations":
                    return( $this->getOperations( $params ) );
                    break;
            endswitch;
        }

        private function getUserFile()
        {
            $userExcel  = ( new IOXMLExcelUser( "getUserExcel" ) )->request( $params = null );
            $params     = array();
            if( !empty( $userExcel ) ):
                $excelHash      = $userExcel['hash'];
                $excelExt       = $userExcel['ext'];
                $fileName       = APPLICATION_PATH . Library::path('web/files/excel_calculators_tmp/' . $excelHash . '.' . $excelExt);
                $params['hash'] = $excelHash;
                $params['ext']  = $excelExt;
                $params['file'] = $fileName;
            endif;
            return( $params );
        }

        private function dataToFile( $params )
        {
            $elementName                   = $params['element_name'];
            $extractedAndOrderedAttributes = $this->extractedAndOrderedAttributes( $params );
            $dataWithDestination           = $this->dataWithDestination( $extractedAndOrderedAttributes, $params['data'], $params );
            $dataSetCells                  = $this->getDataSetCells( $dataWithDestination );
            $elementMultiplicity           = $this->getElementMultiplicity( $params );
            $elementTypeOccurrences        = $this->getElementOccurrences( $dataSetCells, $elementName );
            $isAllowed                     = $this->matchOccurrencesWithMultiplicity( $elementMultiplicity, $elementTypeOccurrences );

            if( $isAllowed === true ):

                $emptyRow                      = $this->getEmptyRow( $dataSetCells );
                $maxRow                        = $this->getMaxRowFromDataSet( $dataSetCells );

                if( $emptyRow !== ( $maxRow + 1 ) && $emptyRow !== 0 ):
                    $rowWithData                   = $this->populateEmptyRow( $dataWithDestination, $emptyRow );

                    if( !empty( $rowWithData ) ):
                        return( $this->dataToExcel( $rowWithData ) );
                    endif;
                else:
                    echo "Max excel row reached! (Based on range)";
                endif;

            endif;
        }


        private function dataToExcel( $data )
        {
            $fileInfo       = $this->getUserFile();
            $userId         = ( !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
            $userExcelHash  = sha1( $userId );
            $excelHash      = ( isset( $fileInfo['hash'] ) ?  $fileInfo['hash'] : "" );
            $excelExt       = ( isset( $fileInfo['ext'] ) ?  $fileInfo['ext'] : "" );
            $fileName       = ( isset( $fileInfo['file'] ) ?  $fileInfo['file'] : "" );

            /**
             * Check if the excel hash matches the user excel hash
             */
            if( $userExcelHash === $excelHash && !empty( $excelExt )):

                $fileType    = Excel_Factory::identify( $fileName );
                $objReader   = Excel_Factory::createReader( $fileType );
                $objPHPExcel = $objReader->load( $fileName );

                $sheet   = ( !empty( $data['fileInfo']['sheet'] ) ? $data['fileInfo']['sheet'] : "" );

                $row     = ( !empty( $data['fileInfo']['row'] ) ? $data['fileInfo']['row'] : "" );
                $columns = ( !empty( $data['columns'] ) ? $data['columns'] : array() );

                foreach( $columns as $column => $value ):
                    $cell = $column.$row;
                    $objPHPExcel->setActiveSheetIndexByName( $sheet )->setCellValue( $cell, $value );
                endforeach;

                $objWriter = Excel_Factory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->setPreCalculateFormulas(false);

                return( empty( $objWriter->save( $fileName ) ) ? true : false );


            endif;
        }

        private function matchOccurrencesWithMultiplicity( $elementMultiplicity, $elementTypeOccurrences )
        {
            switch( $elementTypeOccurrences ):
                case"0":
                    return( true );
                    break;
                case"1":
                    $multiplicity = "1";
                    $allowed      = ( $multiplicity === $elementMultiplicity && $multiplicity === (string) $elementTypeOccurrences ? false : true );
                    return( $allowed );
                    break;
                case ( $elementTypeOccurrences > 1 ):
                    return( true );
                    break;
            endswitch;
        }

        private function getElementMultiplicity( $params )
        {
            $elementName = ( !empty( $params['element_name'] ) ? $params['element_name'] : "" );

            $multiplicity = "";
            foreach( $params['elements'] as $element  ):
                if( $elementName === $element['name'] ):
                    $multiplicity = ( !empty( $element['multiplicity'] ) ? $element['multiplicity'] : "" );
                    break;
                endif;
            endforeach;

            return( $multiplicity );
        }

        /**
         * @param $data
         * @return array|bool
         *
         * Reads the entire data set specified by excel ranges and specific to the element and sheet.
         */
        private function getDataSetCells( $data )
        {
            $dataSetCells  = array();
            $fileInfo    = $this->getUserFile();
            $fileName    = ( !empty( $fileInfo['file'] ) ? $fileInfo['file'] : "" );
            $fileType    = Excel_Factory::identify( $fileName );
            $objReader   = Excel_Factory::createReader( $fileType );
            $objPHPExcel = $objReader->load( $fileName );

            foreach( $data as $sheet => $set ):
                /**
                 * - Get the excel type tab and cell.
                 * - Prepare if for reading
                 */
                if( !empty( $set[0]['excelTypeCell'] ) && !empty( $set[0]['excelTypeTab'] ) ):
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
                        $dataSetCells[$k][$typeStartStr] = $objPHPExcel->getActiveSheet()->getCell( $cell )->getCalculatedValue();
                    endfor;

                endif;

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
                            $dataSetCells[$j][$startStr] = $objPHPExcel->getActiveSheet()->getCell( $cell )->getCalculatedValue();
                        endfor;

                    endif;
                endfor;

            endforeach;

            if( !empty( $dataSetCells ) ):
                ksort( $dataSetCells );
                return( $dataSetCells );
            else:
                return( false );
            endif;
        }

        private function getElementOccurrences( $dataSetCells, $elementName )
        {
            $occurrences = 0;
            if( !empty( $dataSetCells ) ):
                foreach( $dataSetCells as $dataCells ):
                    foreach( $dataCells as $dataCell ):
                        if( $dataCell === $elementName ):
                            $occurrences++;
                        endif;
                    endforeach;
                endforeach;
            endif;

            return( $occurrences );
        }

        private function getMaxRowFromDataSet( $dataSet )
        {
            $rows   = array();
            $maxRow = 0;
            if( !empty( $dataSet ) ):
                foreach( $dataSet as $row => $array ):
                    $rows[] = $row;
                endforeach;

                $maxRow = max( $rows );
            endif;

            return( $maxRow );
        }

        private function populateEmptyRow( $data, $rowNumber )
        {
            $populated = array();

            foreach( $data as $sheet => $array ):
                foreach( $array as $item ):
                    $sheetName   = ( !empty( $item['tab'] ) ? str_replace( " ", "", $item['tab'] ) : ""  );
                    $typeValue   = ( !empty( $item['type'] ) ? $item['type'] : ""  );
                    $cell        = ( !empty( $item['cell'] ) ? $item['cell'] : ""  );
                    $typeCell    = ( !empty( $item['excelTypeCell'] ) ? $item['excelTypeCell'] : ""  );
                    $value       = ( !empty( $item['value'] ) ? $item['value'] : ""  );

                    $populated['fileInfo']['sheet'] = $sheetName;
                    $populated['fileInfo']['row']   = $rowNumber;

                    $range       = explode( ":", $typeCell );
                    $rangeStart  = $range[0];
                    $startStr    = strtoupper( preg_replace( "/[^a-zA-Z]+/", "", $rangeStart ) );

                    if( !empty( $startStr ) && !empty( $typeValue ) ):
                        $populated['columns'][$startStr] = $typeValue;
                    endif;

                    $range       = explode( ":", $cell );
                    $rangeStart  = $range[0];
                    $startStr    = strtoupper( preg_replace( "/[^a-zA-Z]+/", "", $rangeStart ) );

                    if( !empty( $startStr ) && !empty( $value ) ):
                        $populated['columns'][$startStr] = $value;
                    endif;

                endforeach;
            endforeach;

            if( !empty( $populated['columns'] ) ):
                ksort( $populated['columns'] );
            endif;

            return( $populated );
        }

        private function getEmptyRow( $dataSetCells )
        {
            $emptyRow = 0;
            if( !empty( $dataSetCells ) ):

                foreach( $dataSetCells as $cell => $row ):
                    $isEmpty = array_filter($row);
                    if( empty( $isEmpty ) ):
                        $emptyRow = $cell;
                        break;
                    endif;
                endforeach;
            endif;

            return( $emptyRow );
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

        private function readCell( $params )
        {
            $userId = ( !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
            $userExcelHash  = sha1( $userId );
            $excelHash      = ( isset( $params['excelHash'] ) ?  $params['excelHash'] : "" );
            $excelExt       = ( isset( $params['excelExt'] ) ?  $params['excelExt'] : "" );

            if( $userExcelHash === $excelHash && !empty( $excelExt ) ):

                $tab  = ( !empty( $params['tab'] ) ? $params['tab'] : "" );
                $cell = ( !empty( $params['cell'] ) ? $params['cell'] : "" );

                $parts = explode( ":", $cell );
                $cell  = ( !empty( $parts[0] ) ? strtoupper( $parts[0] ) : "" );

                $fileName = APPLICATION_PATH . Library::path('web/files/excel_calculators_tmp/' . $excelHash . '.' . $excelExt);
                $fileType = Excel_Factory::identify($fileName);

                $objReader = Excel_Factory::createReader( $fileType );
                $objReader->setReadDataOnly(true);
                $objPHPExcel = $objReader->load( $fileName );

                $objPHPExcel->setActiveSheetIndexByName( $tab );
                $returnData = $objPHPExcel->getActiveSheet()->getCell( $cell )->getCalculatedValue();

            endif;

            if( !empty( $returnData ) ):
                return( $returnData );
            else:
                return( false );
            endif;
        }

        private function getOperations( $params )
        {
            $elements    = ( !empty( $params['elements'] ) ? $params['elements'] : array() );
            $elementName = ( !empty( $params['element_name'] ) ? $params['element_name'] : "" );

            $operationArray = array();
            foreach( $elements as $element ):
                if( $elementName === $element['name'] ):

                    $operations = ( !empty( $element['formDetails']['elementOperations'][$elementName] ) ? $element['formDetails']['elementOperations'][$elementName] : array() );

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
                                $excel                       = $this->readCell( $params );
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