<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 19:28
 */

use app\enterpriseArchitecture\IOExcelCalculatorUpload;
use app\enterpriseArchitecture\IOEAExcelCalculator;
use app\lib\Project;

if( isset($_FILES) && !empty( $_FILES ) ):

    if( isset( $_FILES['excelFile'] ) && ( $_FILES['excelFile']['error'] === UPLOAD_ERR_OK ) ):

        $file               = $_FILES['excelFile']['tmp_name'];
        $fileName           = $_FILES['excelFile']['name'];
        $uploadedAt         = date( "Y-m-d H:i:s" );
        $xmlFile            = $_FILES['excelFile']['tmp_name'];
        $path_parts         = pathinfo($_FILES["excelFile"]["name"]);
        $extension          = $path_parts['extension'];
        $newFile            = sha1_file($file);

        /**
         * Check if the calculator already exists
         */
        $returnData = ( new IOExcelCalculatorUpload( "matchHash", $newFile, $uploadedAt ) )->request();
        if( !empty( $returnData ) ):
            $matchHash = $returnData[0];
        else:
            $matchHash = "";
        endif;

        if( !hash_equals( $newFile, $matchHash ) ):

            /**
             * Save the calculator in the database and in the files/excel_calculator_tmp directory
             */
            $lastInsertedID = ( new IOExcelCalculatorUpload( "saveCalculator", $newFile, $uploadedAt ) )->request();

            /**
             * Store the project id, calculator id, and user id in the projects_calculators join table
             */
            $params = array( "calculator_id" => $lastInsertedID );
            ( new Project( "saveCalculatorJoinTable" ) )->request( $params );

            /**
             * Hash and save the file
             */
            move_uploaded_file(
                $_FILES['excelFile']['tmp_name'],
                sprintf(APPLICATION_ROOT.'/web/files/excel_calculators_tmp/%s.%s',
                    sha1_file($_FILES['excelFile']['tmp_name']),
                    $extension
                )) ;

            $_SESSION['calculatorId'] = ( isset( $lastInsertedID ) ? $lastInsertedID : "" );

        else:
            $returnData = ( new IOEAExcelCalculator( $matchHash ) )->getCalculatorIdByHash();
            $_SESSION['calculatorId'] = ( !empty( $returnData['calculator_id'] ) ? $returnData['calculator_id'] : "" );

        endif;


        header("Location: index.php?calculators");
        exit();

    else:
        header("Location: index.php?calculatorUploadFailed");
        exit();

    endif;

else:
    header("Location: index.php?modelUploadFailed");
    exit();
endif;