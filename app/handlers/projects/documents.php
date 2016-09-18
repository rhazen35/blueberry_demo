<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 11-Sep-16
 * Time: 21:55
 */

use app\lib\ProjectDocuments;
use app\core\Library;

if( $_SERVER['REQUEST_METHOD'] === "POST" ):

    $projectId  = ( !empty( $_POST['projectId'] ) ? $_POST['projectId'] : "" );
    $name       = ( !empty( $_POST['name'] ) ? $_POST['name'] : "" );
    $group      = ( !empty( $_POST['group'] ) ? $_POST['group'] : "" );
    $groupName  = ( !empty( $_POST['groupName'] ) ? $_POST['groupName'] : "" );
    $document   = ( !empty( $_POST['document'] ) ? $_POST['document'] : "" );

    $params           = array("project_id" => $projectId);
    $documents        = ( new ProjectDocuments( "getDocuments" ) )->request( $params );
    $documents_groups = ( new ProjectDocuments( "getDocumentsGroups" ) )->request( $params );

    $group = ( !empty( $groupName ) ? $groupName : $group );
    if( !empty( $projectId ) && !empty( $_FILES ) ):

        if( isset( $_FILES['document'] ) && ( $_FILES['document']['error'] === UPLOAD_ERR_OK ) ):

            $file               = $_FILES['document']['tmp_name'];
            $fileName           = $_FILES['document']['name'];
            $document           = $_FILES['document']['tmp_name'];
            $path_parts         = pathinfo($_FILES["document"]["name"]);
            $extension          = $path_parts['extension'];
            $newFile            = sha1_file($file);
            $directory          = ( !empty( $group ) ? strtolower( $group ) : "" );
            $path               = Library::path( APPLICATION_ROOT . "/web/files/project_documents/" . $projectId . "/" . $group . "/" );

            $params['group'] = $group;
            $params['name']  = $name;
            $params['hash']  = $newFile;
            $params['ext']   = $extension;

            $documentExists = false;
            if( !empty( $documents ) ):
                foreach( $documents as $document ):
                    if( $newFile === $document['hash'] ):
                        $documentExists = true;
                    endif;
                endforeach;
            endif;

            $allowedExtensions = array("doc", "docx", "xls", "xlsx", "txt", "xml");
            if( in_array( $extension, $allowedExtensions ) ):
                if ( !file_exists( $path ) ):
                    $oldmask = umask(0);
                    mkdir ($path, 0744);
                    ( new ProjectDocuments( "newProjectDocumentGroup" ) )->request( $params );
                endif;
                if( $documentExists === false ):
                    ( new ProjectDocuments( "newProjectDocument" ) )->request( $params );
                    move_uploaded_file( $_FILES['document']['tmp_name'], sprintf( APPLICATION_ROOT.'/web/files/project_documents/'.$projectId.'/'.$group.'/%s.%s', sha1_file( $_FILES['document']['tmp_name'] ), $extension ) );
                    header("Location: " . APPLICATION_HOME . "?projectDocuments");
                    exit();
                else:
                    echo "Document exists already!";
                endif;
            else:
                echo "Extension not allowed!";
            endif;

        endif;

    endif;
endif;


