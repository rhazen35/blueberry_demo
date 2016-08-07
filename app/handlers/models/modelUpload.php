<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 19:28
 */

use app\enterpriseArchitecture\IOXMLEAModelUpload;
use app\enterpriseArchitecture\IOXMLEAModel;
use app\enterpriseArchitecture\XMLEATableFactory;
use app\lib\Project;
use app\core\Library;

/**
 * Check if a project has been posted.
 * If a projects has been posted, but is empty, redirect back to upload.
 */
if( isset( $_POST['project'] ) ):
    if(!empty( $_POST['project'] )):
        $_SESSION['project_id'] = $_POST['project'];
    else:
        header("Location: index.php?modelUploadNoProject");
        exit();
    endif;
endif;

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
         * TODO: Validate the file.
         */

        /**
         * Check if the extensions are valid
         */
        $allowedExtensions = array("xml");
        if( in_array( $extension, $allowedExtensions ) ):

            /**
             * Check if the model already exists
             */
            $returnData = ( new IOXMLEAModelUpload( "matchHash", $newFile, $uploadedAt ) )->request( $params = null );
            if( !empty( $returnData ) ):
                $matchHash = $returnData[0];
            else:
                $matchHash = "";
            endif;
            /**
             * Pass the xml file with the new model command and the timestamp
             * XML will be validated and a report is returned
             */
            $report = ( new IOXMLEAModelUpload( "validateModel", $xmlFile, $uploadedAt ) )->request( $params = null );

            $validationEndTime              = Library::microtimeFormat( $validationStartTime );
            $report['validationDuration']   = $validationEndTime;
            $_SESSION['xmlValidatorReport'] = serialize( $report );
            /**
             * Add the original file name to the report array
             */
            $report['originalFileName'] = $fileName;

            if( !hash_equals( $newFile, $matchHash ) ):

                $report['file_exists'] = false;
                /**
                 * Save the model in the database and in the files/xml_models_tmp directory
                 */
                $name                   = ( isset( $report['trueRootClassName'] ) ? $report['trueRootClassName'] : "" );
                $nameExists             = ( new IOXMLEAModel( $name ) )->checkModelNameExists();
                if( $nameExists === false ):

                    $valid                  = ( $report['validation']['valid'] === true ? "yes" : "no" );
                    $params                 = array( "name" => $name, "valid" => $valid, "extension" => $extension );
                    $lastInsertedID         = ( new IOXMLEAModelUpload( "saveModel", $newFile, $uploadedAt ) )->request( $params );
                    $_SESSION['xmlModelId'] = ( isset( $lastInsertedID ) ? $lastInsertedID : "" );
                    /**
                     * Store the project id, model id, and user id in the projects_models join table
                     */
                    $params = array( "model_id" => $lastInsertedID );
                    ( new Project( "saveModelJoinTable" ) )->request( $params );
                    /**
                     * Hash and save the file
                     */
                    move_uploaded_file( $_FILES['xmlFile']['tmp_name'], sprintf( APPLICATION_ROOT.'/web/files/xml_models_tmp/%s.%s', sha1_file( $_FILES['xmlFile']['tmp_name'] ), $extension ) );
                    /**
                     * Create tables for the elements with the type of uml:Class
                     */
                    if( !empty( $name ) ):
                        $params = array( "model_id" => $lastInsertedID, "model_name" => $name );
                        ( new XMLEATableFactory( "create" ) )->request( $params );
                    endif;

                else:
                    header("Location: index.php?modelUploadNameExists");
                    exit();
                endif;

            else:
                $report['file_exists']  = true;
                $returnData             = ( new IOXMLEAModel( $matchHash ) )->getModelIdByHash();
                $_SESSION['xmlModelId'] = ( !empty( $returnData['model_id'] ) ? $returnData['model_id'] : "" );
            endif;

            header("Location: index.php?xmlEAValidatorReport");
            exit();

        else:
            header("Location: index.php?modelUploadInvalidFileExtension");
            exit();
        endif;

    else:
        header("Location: index.php?modelUploadNoFile");
    endif;

else:
    header("Location: index.php?modelUploadNoFile");
    exit();
endif;