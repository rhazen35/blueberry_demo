<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 16:40
 */

use app\lib\Project;

$name        = ( $_POST['name'] ? $_POST['name'] : "" );
$description = ( $_POST['description'] ? $_POST['description'] : "" );

if( !empty( $name ) && !empty( $description ) ):
    $params       = array( "name" => $name, "description" => $description );
    $lastInsertId = ( new Project( "newProject" ) )->request( $params );

    $_SESSION['projectId'] = $lastInsertId;

    header("Location: index.php?projects");
    exit();
else:
    header("Location: index.php?newProjectFailed");
    exit();
endif;