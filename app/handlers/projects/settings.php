<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 10-9-2016
 * Time: 19:41
 */

use app\lib\ProjectSettings;

$projectId = ( !empty( $_POST['projectId'] ) ? $_POST['projectId'] : "" );

if( !empty( $projectId ) ):

    if( $_SERVER['REQUEST_METHOD'] === "POST" ):
        $name  = ( !empty( $_POST['name'] ) ? $_POST['name'] : "" );
        $descr = ( !empty( $_POST['descr'] ) ? $_POST['descr'] : "" );
        $type  = ( !empty( $_POST['type'] ) ? $_POST['type'] : "" );

        if( !empty( $name ) && !empty( $descr ) && !empty( $type ) ):
            $params = array(
                            "project_id" => $projectId,
                            "name" => $name,
                            "descr" => $descr,
                            "type" => $type
                            );
            ( new ProjectSettings( "updateSettings" ) )->request( $params );
            header("Location: " . APPLICATION_HOME . "?project_settings=" . $projectId .  "&edited");
            exit();
        endif;
    endif;

header("Location: " . APPLICATION_HOME . "?project_settings=" . $projectId .  "");
exit();

else:
    header("Location: " . APPLICATION_HOME . "?project_settings=" . $projectId .  "&noProject");
    exit();
endif;

