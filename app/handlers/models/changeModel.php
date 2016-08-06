<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 5-8-2016
 * Time: 11:55
 */

use app\enterpriseArchitecture\IOXMLEAModel;
use app\lib\Models;

$changeCheck = ( isset( $_POST['changeCheck'] ) ? $_POST['changeCheck'] : "" );
$projectId   = ( isset( $_POST['projectId'] ) ? $_POST['projectId'] : "" );
$modelId     = ( isset( $_POST['modelId'] ) ? $_POST['modelId'] : "" );

/**
 * Check if a model id is available and if deletion check has been accepeted
 */
if( !empty( $modelId ) && $changeCheck === "accepted" ):

    $model                  = ( new IOXMLEAModel( $modelId ))->getModel();
    $modelHash              = ( isset( $model['hash'] ) ? $model['hash'] : "" );
    $modelExtension         = ( isset( $model['ext'] ) ? $model['ext'] : "" );
    $_SESSION['project_id'] = ( !empty( $projectId ) ? $projectId : "" );

    $params['model_id']     = $modelId;
    /**
     * Check if the model hash and extension are available and delete the file
     */
    if( !empty( $modelHash ) && !empty( $modelExtension ) ):
        unlink( $_SERVER['DOCUMENT_ROOT'] . '/web/files/xml_models_tmp/' . $modelHash . "." . $modelExtension);
    endif;

    /**
     * Delete the model
     */
    ( new Models( "deleteModel" ) )->request( $params );
    header("Location: index.php?newModel");
    exit();

/**
 * Redirect if the deletion is declined
 */
elseif( $changeCheck === "declined" ):
    unset($_SESSION['model_id']);
    header("Location: index.php?models");
    exit();
/**
 * Redirect and ask for permission to delete the model
 */
else:
    $_SESSION['model_id'] = $modelId;
    header("Location: index.php?changeModelAccept");
    exit();
endif;

