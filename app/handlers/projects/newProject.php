<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 16:40
 */

use app\lib\Project;

/**
 * Check if the name and description are set and valid
 */
$name        = ( $_POST['name'] ? $_POST['name'] : "" );
$description = ( $_POST['description'] ? $_POST['description'] : "" );

if( !empty( $name ) && !empty( $description ) ):
    /**
     * TODO: Validation
     */

    /**
     * Check if the project already exists
     */
    $params         = array( "name" => $name );
    $projectExists  = ( new Project( "checkProjectExists" ) )->request( $params );

    if( $projectExists === false):
        $params       = array( "name" => $name, "description" => $description );
        $lastInsertId = ( new Project( "newProject" ) )->request( $params );

        $_SESSION['projectId'] = $lastInsertId;
        header("Location: index.php?projects");
        exit();
    else:
        header("Location: index.php?projectExists");
        exit();
    endif;
else:
    header("Location: index.php?newProjectEmptyFields");
    exit();
endif;