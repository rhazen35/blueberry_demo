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
                case"readCellRange":
                    return( $this->readCellRange( $params ) );
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
            $rangeData                     = $this->readDataSetCells($dataWithDestination);
            return( $rangeData );
        }

        private function getCellRange( $params )
        {

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

        private function readDataSetCells( $data )
        {
            $returnArray = array();
            $returnData  = array();
            $fileName    = $this->getUserFile();

            $fileType    = Excel_Factory::identify( $fileName );
            $objReader   = Excel_Factory::createReader( $fileType );
            $objPHPExcel = $objReader->load( $fileName );

            foreach( $data as $sheet => $set ):
                $totalSets = count( $set );

                for( $i = 0; $i < $totalSets; $i++ ):
                    if( !empty( $set[$i] ) ):

                        $objPHPExcel->setActiveSheetIndexByName( trim( $set[$i]['tab'] ) );
                        $returnData[] = $objPHPExcel->getActiveSheet()->rangeToArray( $set[$i]['cell'] );
                    endif;
                endfor;
                //$returnData = $objPHPExcel->getActiveSheet()->getCell( $cell )->getCalculatedValue();

            endforeach;

            if( !empty( $returnData ) ):
                return( $returnData );
            else:
                return( false );
            endif;
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
                        $excelTypeFile      = ( isset( $element['tags']['QR-Excel subtypes']['file'] ) ? $element['tags']['QR-Excel subtypes']['file'] : "" );
                        $excelTypeTab       = ( isset( $element['tags']['QR-Excel subtypes']['tab'] ) ? $element['tags']['QR-Excel subtypes']['tab'] : "" );
                        $excelTypeCell      = ( isset( $element['tags']['QR-Excel subtypes']['cell'] ) ? $element['tags']['QR-Excel subtypes']['cell'] : "" );

                        $i = 0;

                        $extractedAndOrderedAttributes[$i]['excelTypeFile'] = $excelTypeFile;
                        $extractedAndOrderedAttributes[$i]['excelTypeTab']  = $excelTypeTab;
                        $extractedAndOrderedAttributes[$i]['excelTypeCell'] = $excelTypeCell;
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
    }

endif;