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

/**
 * Check if a project has been posted.
 * If a projects has been posted, but is empty, redirect back to upload.
 */
if( isset( $_POST['project'] ) ):
    if(!empty( $_POST['project'] )):
        $_SESSION['project_id'] = $_POST['project'];
    else:
        header("Location: index.php?calculatorUploadNoProject");
        exit();
    endif;
endif;

echo $_SESSION['userId'];

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
         * TODO: Validate the file.
         */

        /**
         * Check if the extensions are valid
         */
        $allowedExtensions = array("xls", "xlsx");
        if( in_array( $extension, $allowedExtensions ) ):

            /**
             * Check if the calculator already exists
             */
            $returnData = ( new IOExcelCalculatorUpload( "matchHash", $newFile, $uploadedAt ) )->request( $params = null );
            if( !empty( $returnData ) ):
                $matchHash = $returnData[0];
            else:
                $matchHash = "";
            endif;

            if( !hash_equals( $newFile, $matchHash ) ):

                /**
                 * Save the calculator in the database and in the files/excel_calculator_tmp directory
                 */
                $params = array( "extension" => $extension );
                $lastInsertedID = ( new IOExcelCalculatorUpload( "saveCalculator", $newFile, $uploadedAt ) )->request( $params );

                /**
                 * Store the project id, calculator id, and user id in the projects_calculators join table
                 */
                $params = array( "calculator_id" => $lastInsertedID );
                ( new Project( "saveCalculatorJoinTable" ) )->request( $params );

                /**
                 * Hash and save the file
                 */
                move_uploaded_file( $_FILES['excelFile']['tmp_name'], sprintf(APPLICATION_ROOT.'/web/files/excel_calculators_tmp/%s.%s', sha1_file($_FILES['excelFile']['tmp_name']), $extension)) ;
                $_SESSION['calculatorId'] = ( isset( $lastInsertedID ) ? $lastInsertedID : "" );

            else:
                $returnData = ( new IOEAExcelCalculator( $matchHash ) )->getCalculatorIdByHash();
                $_SESSION['calculatorId'] = ( !empty( $returnData['calculator_id'] ) ? $returnData['calculator_id'] : "" );

                header("Location: index.php?calculatorExists");
                exit();

            endif;
            header("Location: index.php?calculators");
            exit();
        else:
            header("Location: index.php?calculatorUploadInvalidFileExtension");
            exit();
        endif;

    else:
        header("Location: index.php?calculatorUploadNoFile");
        exit();
    endif;

else:
    header("Location: index.php?calculatorUploadNoFile");
    exit();
endif;