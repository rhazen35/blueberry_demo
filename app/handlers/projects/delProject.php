<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 17:22
 */

use app\lib\Project;
use app\enterpriseArchitecture\IOXMLEAModel;

$projectId   = ( !empty( $_POST['projectId'] ) ? $_POST['projectId'] : "" );

if( empty( $projectId ) ):

    header("Location: index.php?delProjectFailed");
    exit();

else:

    $params    = array( "project_id" => $projectId );
    $modelId   = ( new Project( "getModelIdByProjectId" ) )->request( $params );

    if( !empty( $modelId ) ):
        $model     = ( new IOXMLEAModel( $modelId['model_id'] ))->getModel();
        $modelHash = $model['hash'];

        $params['model_id'] = $modelId['model_id'];

        unlink( $_SERVER['DOCUMENT_ROOT'].'/web/files/xml_models_tmp/'.$modelHash.'.xml');

    endif;

    ( new Project( "deleteProject" ) )->request( $params );
    header("Location: index.php?projects");
    exit();

endif;