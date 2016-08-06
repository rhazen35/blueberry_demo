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
     * Check if there is a model id and delete the model file
     */
    if( !empty( $modelId ) ):
        $model               = ( new IOXMLEAModel( $modelId ))->getModel();
        $modelHash           = ( isset( $model['hash'] ) ? $model['hash'] : "" );
        $modelExtension      = ( isset( $model['ext'] ) ? $model['ext'] : "" );

        $params['model_id']  = $modelId;

        if( !empty( $modelHash ) && !empty( $modelExtension ) ):
            unlink( Library::path($_SERVER['DOCUMENT_ROOT'] . '/web/files/xml_models_tmp/' . $modelHash . '.' . $modelExtension));
        endif;

    endif;

    /**
     * Check if there is a calculator id and delete the file
     */
    if( !empty( $calculatorId ) ):
        $calculator          = ( new IOEAExcelCalculator( $calculatorId ))->getCalculator();
        $calculatorHash      = ( isset( $calculator['hash'] ) ? $calculator['hash'] : "" );
        $calculatorExtension = ( isset( $calculator['ext'] ) ? $calculator['ext'] : "" );

        $params['calculator_id'] = $calculatorId;

        if( !empty( $calculatorHash ) && !empty( $calculatorExtension ) ):
            unlink( Library::path($_SERVER['DOCUMENT_ROOT'] . '/web/files/excel_calculators_tmp/' . $calculatorHash . '.' . $calculatorExtension));
        endif;

    endif;

    ( new Project( "deleteProject" ) )->request( $params );
    header("Location: index.php?projects");
    exit();

/**
 * Redirect if the deletion check is declined
 */
elseif( $deleteCheck === "declined" ):
    unset($_SESSION['delProjectId']);
    header("Location: index.php?projects");
    exit();
/**
 * Redirect and ask for permission to the delete the project
 */
else:
    $_SESSION['delProjectId'] = $projectId;
    header("Location: index.php?deleteProjectAccept");
    exit();
endif;