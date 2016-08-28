<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-7-2016
 * Time: 14:47
 */

namespace app\enterpriseArchitecture;

use app\api\ea\EAApi;

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
                case"dataToFile":
                    return( $this->dataToFile( $params ) );
                    break;
            endswitch;
        }

        private function dataToFile( $params )
        {
            $dataWithCell = $this->dataToCell( $params );
            $params['data'] = $dataWithCell;
            $this->write( $params );
            return($dataWithCell);
        }

        private function dataToCell( $params )
        {
            $projectName         = $params['project'];
            $type                = "get_model_data_with_destination";
            $params['dbName']    = "blueberry";
            $params['modelName'] = $projectName;
            $data                = ( new EAApi( $type ) )->request( $params );

            $ranges = $this->getCellRangesByTab( $data );
            $dataWithCell = array();

            if( !empty( $ranges ) ):
                $k = 0;
                foreach( $data as $itemName => $items ):
                    $totalItems = count( $items );
                    for( $i = 0; $i < $totalItems; $i++ ):
                        $totalValues = count( $items[$i] );
                        for( $j = 0; $j < $totalValues; $j++ ):
                            $cell  = ( isset( $items[$i][$j]['cell'] ) ? $items[$i][$j]['cell'] : "" );
                            $tab   = ( isset( $items[$i][$j]['tab'] ) ? $items[$i][$j]['tab'] : "" );
                            $file  = ( isset( $items[$i][$j]['file'] ) ? $items[$i][$j]['file'] : "" );
                            $value = ( isset( $items[$i][$j]['value'] ) ? $items[$i][$j]['value'] : "" );
                            if( !empty( $cell ) ):
                                $dataWithCell[$k]['cell']         = $ranges[$tab][$cell]['start_str'].$ranges[$tab][$cell]['start_num'];
                                $dataWithCell[$k]['tab']          = $tab;
                                $dataWithCell[$k]['file']         = $file;
                                $dataWithCell[$k]['value']        = $value;
                                $ranges[$tab][$cell]['start_num'] = (string) ( (int) $ranges[$tab][$cell]['start_num'] + 1 );
                                $k++;
                            endif;
                        endfor;
                    endfor;
                endforeach;
            endif;

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
                foreach( $data as $elements ):
                    if( !empty( $elements ) ):
                        foreach( $elements as $element ):
                            if( !empty( $element ) ):
                                foreach( $element as $array ):
                                    if( $type === "cells" ):
                                        if( !empty( $array['cell'] ) ):
                                            $returnData[] = $array['cell'];
                                        endif;
                                    else:
                                        if( $type === "tabs" ):
                                            if( !empty( $array['tab'] ) ):
                                                $returnData[] = $array['tab'];
                                            endif;
                                        endif;
                                    endif;
                                endforeach;
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endif;

            return($returnData);
        }

        public function write( $params )
        {
            $data   = $params['data'];
            $userId = ( !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );

            // Set the filename and indentify the type with IOFactory->identify
            $fileName = APPLICATION_PATH . Library::path('web/files/xml_storage/simpel rekenmodel.xlsx');
            $fileType = Excel_Factory::identify($fileName);

            // Read the file
            $objReader = Excel_Factory::createReader($fileType);
            $objPHPExcel = $objReader->load($fileName);

            foreach( $data as $item ):
                $sheet = ( !empty( $item['tab'] ) ? str_replace( " ", "", $item['tab'] ) : ""  );

                $objPHPExcel->setActiveSheetIndexByName($sheet)->setCellValue($item['cell'], $item['value']);

                // Write the file
                $objWriter = Excel_Factory::createWriter($objPHPExcel, 'Excel2007');

                // Set calculate formula's false when using formulas to prevent PHPExcel from executing them.
                $objWriter->setPreCalculateFormulas(false);
            endforeach;

            // Save the file
            if( $objWriter->save( $fileName ) ):
                return( true );
            else:
                return( false );
            endif;
        }

        public function read( $params )
        {
            $returnData = array();

            $userId = ( !empty( $_SESSION['user_excel_id'] ) ? $_SESSION['user_excel_id'] : "" );

            // Set the filename and indentify the type with IOFactory->identify
            $fileName = 'files/' . $userId . '.xlsx';
            $fileType = \PHPExcel_IOFactory::identify( $fileName );

            // Read the file
            $objReader = \PHPExcel_IOFactory::createReader( $fileType );
            $objPHPExcel = $objReader->load( $fileName );

            // Read the file
            $objPHPExcel->setActiveSheetIndex(0);
            
            foreach($params as $key => $value):
                $returnData[$key] = $objPHPExcel->getActiveSheet()->getCell( $value )->getCalculatedValue();
            endforeach;

            return( $returnData );
        }

    }

endif;