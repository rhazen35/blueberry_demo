<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 17:22
 */

use app\core\Library;
use app\lib\Project;
use app\enterpriseArchitecture\IOXMLEAModel;
use app\enterpriseArchitecture\IOEAExcelCalculator;
use app\enterpriseArchitecture\XMLEATableFactory;

$projectId   = ( !empty( $_POST['projectId'] ) ? $_POST['projectId'] : "" );
$deleteCheck = ( isset( $_POST['deleteCheck'] ) ? $_POST['deleteCheck'] : "" );

/**
 * Check if a project id is available and if the deletion is accepted
 */
if( !empty( $projectId ) && $deleteCheck === "accepted" ):

    $params         = array( "project_id" => $projectId );
    $modelId        = ( new Project( "getModelIdByProjectId" ) )->request( $params );
    $calculatorId   = ( new Project( "getCalculatorIdByProjectId" ) )->request( $params );
    $modelId        = ( isset( $modelId['model_id'] ) ? $modelId['model_id'] : "" );
    $calculatorId   = ( isset( $calculatorId['calculator_id'] ) ? $calculatorId['calculator_id'] : "" );

    /**
     * Check if there is a model id and delete the model file, database and table(s)
     */
    if( !empty( $modelId ) ):
        $model               = ( new IOXMLEAModel( $modelId ))->getModel();
        $modelName           = ( isset( $model['name'] ) ? $model['name'] : "" );
        $modelHash           = ( isset( $model['hash'] ) ? $model['hash'] : "" );
        $modelExtension      = ( isset( $model['ext'] ) ? $model['ext'] : "" );

        /**
         * Delete the model database and it's table(s)
         */
        $dbName                 = strtolower( str_replace( " ", "_", $modelName ) );
        $params['name']         = $dbName;
        $params['model_id']     = $modelId;

        ( new XMLEATableFactory( "delete" ) )->request( $params );

        if( !empty( $modelHash ) && !empty( $modelExtension ) ):
            $path = Library::path($_SERVER['DOCUMENT_ROOT'] . '/web/files/xml_models_tmp/' . $modelHash . '.' . $modelExtension);
            if( file_exists( $path ) ): unlink( $path ); endif;
        endif;

    endif;

    /**
     * Check if there is a calculator id and delete the file
     */
    if( !empty( $calculatorId ) ):
        $params['calculator_id'] = $calculatorId;
        $calculator              = ( new IOEAExcelCalculator( "getCalculator" ))->request( $params );
        $calculatorHash          = ( isset( $calculator['hash'] ) ? $calculator['hash'] : "" );
        $calculatorExtension     = ( isset( $calculator['ext'] ) ? $calculator['ext'] : "" );

        if( !empty( $calculatorHash ) && !empty( $calculatorExtension ) ):
            $path = Library::path($_SERVER['DOCUMENT_ROOT'] . '/web/files/excel_calculators_tmp/' . $calculatorHash . '.' . $calculatorExtension);
            if( file_exists( $path ) ): unlink( $path ); endif;
        endif;

    endif;
    ( new Project( "deleteProject" ) )->request( $params );
    header("Location: index.php?projects&deleted");
    exit();

/**
 * Redirect if the deletion check is declined
 */
elseif( $deleteCheck === "declined" ):
    unset($_SESSION['delProjectId']);
    header("Location: index.php?project_settings=" . $projectId . "");
    exit();
/**
 * Redirect and ask for permission to the delete the project
 */
else:
    $_SESSION['delProjectId'] = $projectId;
    header("Location: index.php?project_settings=" . $projectId . "&deleteProjectAccept");
    exit();
endif;