<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 18:44
 */

use app\lib\Project;

/**
 * Handle the model request.
 * Send to upload when no model is linked to the project, otherwise send to the preview.
 */

$projectId              = ( isset( $_POST['projectId'] ) ? $_POST['projectId'] : "" );

$_SESSION['project_id'] = $projectId;
$params                 = array("project_id" => $projectId);
$modelId                = ( new Project( "getModelIdByProjectId" ) )->request( $params );

if( isset( $modelId['model_id'] ) ):
    $_SESSION['xmlModelId'] = $modelId['model_id'];
    header("Location: index.php?model");
    exit();
else:
    header("Location: index.php?newModel");
    exit();
endif;