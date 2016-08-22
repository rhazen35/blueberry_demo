<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-7-2016
 * Time: 14:47
 */

namespace app\enterpriseArchitecture;

if( !class_exists( "IOExcelFactory" ) ):

    class IOExcelFactory
    {

        public function write( $params )
        {

            $userId = ( !empty( $_SESSION['user_excel_id'] ) ? $_SESSION['user_excel_id'] : "" );

            // Set the filename and indentify the type with IOFactory->identify
            $fileName = 'files/' . $userId . '.xlsx';
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