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

$projectId = ( isset( $_POST['projectId'] ) ? $_POST['projectId'] : "" );
$modelId   = ( isset( $_POST['modelId'] ) ? $_POST['modelId'] : "" );

if( !empty( $modelId ) && $changeCheck === "accepted" ):
    $model                  = ( new IOXMLEAModel( $modelId ))->getModel();
    $modelHash              = $model['hash'];
    $modelExtension         = $model['ext'];
    $params['model_id']     = $modelId;
    $_SESSION['project_id'] = ( !empty( $projectId ) ? $projectId : "" );

    unlink( $_SERVER['DOCUMENT_ROOT'] . '/web/files/xml_models_tmp/' . $modelHash . "." . $modelExtension);

    ( new Models( "deleteModel" ) )->request( $params );
    header("Location: index.php?newModel");
elseif( $changeCheck === "declined" ):
    unset($_SESSION['model_id']);
    header("Location: index.php?models");
else:
    $_SESSION['model_id'] = $modelId;
    header("Location: index.php?changeModelAccept");
endif;

exit();

