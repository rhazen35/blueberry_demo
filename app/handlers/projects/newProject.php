<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 16:40
 */

use app\lib\Project;
use app\core\Library;

/**
 * Check if the name and description are set and valid
 */
$name        = ( $_POST['name'] ? $_POST['name'] : "" );
$description = ( $_POST['description'] ? $_POST['description'] : "" );
$type        = ( $_POST['type'] ? $_POST['type'] : "" );

if( !empty( $name ) && !empty( $description ) && !empty( $type ) ):
    /**
     * TODO: Validation
     */

    /**
     * Check if the project already exists
     */
    $params         = array( "name" => $name );
    $projectExists  = ( new Project( "checkProjectExists" ) )->request( $params );

    if( $projectExists === false ):
        $params                 = array( "name" => $name, "description" => $description, "type" => $type );
        $lastInsertId           = ( new Project( "newProject" ) )->request( $params );
        $params['projectId']    = $lastInsertId;
        $_SESSION['projectId']  = $lastInsertId;

        ( new Project( "newProjectSettings" ) )->request( $params );
        /**
         * Create a directory in projects_documents for the projects documents
         */
        $path = Library::path( APPLICATION_ROOT . '/web/files/project_documents/' . $lastInsertId );
        if(!file_exists($path)):
            mkdir($path, 0777, true);
        endif;
        /**
         * Redirect to projects
         */
        header("Location: index.php?projects&new&project=" . $lastInsertId . "");
        exit();
    else:
        header("Location: index.php?projectExists");
        exit();
    endif;
else:
    header("Location: index.php?newProjectEmptyFields");
    exit();
endif;