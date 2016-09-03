<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 18:44
 */

use app\lib\Project;
use app\enterpriseArchitecture\IOXMLExcelUser;

/**
 * Handle the model request.
 *
 * - Send to upload when no model is linked to the project, otherwise send to the preview.
 * - Copies the calculator base file if it does not exists already.
 */

$projectId              = ( isset( $_POST['projectId'] ) ? $_POST['projectId'] : "" );
$userId                 = ( isset( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
$params                 = array("project_id" => $projectId);
$modelId                = ( new Project( "getModelIdByProjectId" ) )->request( $params );
$_SESSION['project_id'] = $projectId;

/**
 * - Check if there is a user specific calculator file.
 * - Insert a new user excel hash if there isn't any
 */
var_dump($userExcelHash = ( new IOXMLExcelUser( "getUserExcelHash" ) )->request( $params = null ));

if( empty( $userExcelHash ) ):
    $userExcelHash  = sha1( $userId );
    $params['hash'] = $userExcelHash;
    var_dump( ( new IOXMLExcelUser( "newUserExcelHash" ) )->request( $params ) );
endif;

if( isset( $modelId['model_id'] ) ):
    $_SESSION['xmlModelId'] = $modelId['model_id'];
    header("Location: index.php?model");
    exit();
else:
    header("Location: index.php?newModel");
    exit();
endif;
