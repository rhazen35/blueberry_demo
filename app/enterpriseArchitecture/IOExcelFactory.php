<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-7-2016
 * Time: 14:47
 */

namespace app\enterpriseArchitecture;

use app\api\ea\EAApi;

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
            $projectName         = $params['project'];
            $type                = "get_model_data_with_destination";
            $params['dbName']    = "blueberry";
            $params['modelName'] = $projectName;
            $data                = ( new EAApi( $type ) )->request( $params );

            $ranges = $this->getCellRanges( $data );

            if( !empty( $ranges ) ):
                foreach( $data as $items ):
                    $totalItems = count( $items );
                    for( $i = 0; $i < $totalItems; $i++ ):
                        $totalValues = count( $items[$i] );
                        for( $j = 0; $j < $totalValues; $j++ ):
                            $cell  = ( isset( $items[$i][$j]['cell'] ) ?  $items[$i][$j]['cell'] : "" );
                            $value = ( isset( $items[$i][$j]['value'] ) ? $items[$i][$j]['value'] : "" );

                            if( !empty( $cell ) ):
                                //$parts = explode(":", $cell);
                                if( !empty($ranges[$cell]) ):

                                endif;
                            endif;

                        endfor;
                    endfor;
                endforeach;
            endif;

            return( $ranges );
        }

        private function getCellRanges( $data )
        {
            $cells = $this->extractCells( $data );
            $uniqueCells = array_unique( $cells );

            $range = array();
            $i = 0;
            foreach( $uniqueCells as $uniqueCell ):
                $parts = explode(':', $uniqueCell);
                $start = ( isset( $parts[0] ) ? $parts[0] : "" );
                $end   = ( isset( $parts[1] ) ? $parts[1] : "" );

                $range[$uniqueCell]['start_str'] = preg_replace("/[^a-zA-Z]+/", "", $start);
                $range[$uniqueCell]['start_num'] = ( preg_match_all( '/\d+/', $start, $matches ) ? $matches[0][0] : "" );
                $range[$uniqueCell]['end_str']   = preg_replace("/[^a-zA-Z]+/", "", $end);
                $range[$uniqueCell]['end_num']   = ( preg_match_all( '/\d+/', $end, $matches ) ? $matches[0][0] : "" );
                $i++;
            endforeach;

            return( $range );
        }

        private function extractCells( $data )
        {
            $cells = array();
            if( !empty( $data ) ):
                foreach( $data as $elements ):
                    if( !empty( $elements ) ):
                        foreach( $elements as $element ):
                            if( !empty( $element ) ):
                                foreach( $element as $array ):
                                    if( !empty( $array['cell'] ) ):
                                        $cells[] = $array['cell'];
                                    endif;
                                endforeach;
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endif;

            return($cells);
        }

        public function write( $params )
        {

            $userId = ( !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );

            // Set the filename and indentify the type with IOFactory->identify
            $fileName = 'files/xml_storage/simpel rekenmodel.xlsx';
            $fileType = \PHPExcel_IOFactory::identify($fileName);

            // Read the file
            $objReader = \PHPExcel_IOFactory::createReader($fileType);
            $objPHPExcel = $objReader->load($fileName);

            // Change the file
            $objPHPExcel->setActiveSheetIndex(0);

                foreach($params as $key => $value):
                    $objPHPExcel->getActiveSheet()->setCellValue($key, $value);
                endforeach;

            // Write the file
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            // Set calculate formula's false when using formulas to prevent PHPExcel from executing them.
            $objWriter->setPreCalculateFormulas(false);

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