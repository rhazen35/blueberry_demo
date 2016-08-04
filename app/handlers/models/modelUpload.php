<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 19:28
 */

use app\enterpriseArchitecture\IOXMLModelUpload;
use app\enterpriseArchitecture\IOXMLEAModel;
use app\lib\Project;
use app\core\Library;

$validationStartTime = microtime(true);

if( isset($_FILES) && !empty( $_FILES ) ):

    if( isset( $_FILES['xmlFile'] ) && ( $_FILES['xmlFile']['error'] === UPLOAD_ERR_OK ) ):

        $file               = $_FILES['xmlFile']['tmp_name'];
        $fileName           = $_FILES['xmlFile']['name'];
        $uploadedAt         = date( "Y-m-d H:i:s" );
        $xmlFile            = $_FILES['xmlFile']['tmp_name'];
        $path_parts         = pathinfo($_FILES["xmlFile"]["name"]);
        $extension          = $path_parts['extension'];
        $newFile            = sha1_file($file);
        /**
         * Check if the model already exists
         */
        $returnData = ( new IOXMLModelUpload( "matchHash", $newFile, $uploadedAt ) )->request( $params = null );
        if( !empty( $returnData ) ):
            $matchHash = $returnData[0];
        else:
            $matchHash = "";
        endif;
        /**
         * Pass the xml file with the new model command and the timestamp
         * XML will be validated and a report is returned
         */
        $report = ( new IOXMLModelUpload( "validateModel", $xmlFile, $uploadedAt ) )->request( $params = null );
        /**
         * Add the original file name to the report array
         */
        $report['originalFileName'] = $fileName;

        if( !hash_equals( $newFile, $matchHash ) ):

            $report['file_exists'] = false;
            /**
             * Save the model in the database and in the files/xml_models_tmp directory
             */
            $name           = ( isset( $report['trueRootClassName'] ) ? $report['trueRootClassName'] : "" );
            $valid          = ( $report['validation']['valid'] === true ? "yes" : "no" );
            $params         = array( "name" => $name, "valid" => $valid );
            $lastInsertedID = ( new IOXMLModelUpload( "saveModel", $newFile, $uploadedAt ) )->request( $params );
            /**
             * Store the project id, model id, and user id in the projects_models join table
             */
            $params = array( "model_id" => $lastInsertedID );
            ( new Project( "saveModelJoinTable" ) )->request( $params );
            /**
             * Hash and save the file
             */
            move_uploaded_file(
                $_FILES['xmlFile']['tmp_name'],
                sprintf(APPLICATION_ROOT.'/web/files/xml_models_tmp/%s.%s',
                    sha1_file($_FILES['xmlFile']['tmp_name']),
                    $extension
                )) ;

            $_SESSION['xmlModelId'] = ( isset( $lastInsertedID ) ? $lastInsertedID : "" );

        else:
            $report['file_exists']  = true;
            $returnData             = ( new IOXMLEAModel( $matchHash ) )->getModelIdByHash();
            $_SESSION['xmlModelId'] = ( !empty( $returnData['model_id'] ) ? $returnData['model_id'] : "" );
        endif;


        $validationEndTime              = Library::microtimeFormat( $validationStartTime );
        $report['validationDuration']   = $validationEndTime;
        $_SESSION['xmlValidatorReport'] = serialize( $report );

        header("Location: index.php?xmlEAValidatorReport");
        exit();

    else:
        header("Location: index.php?modelUploadFailed");
        exit();

    endif;

else:
    header("Location: index.php?modelUploadFailed");
    exit();
endif;