<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 12-Sep-16
 * Time: 9:19
 */

namespace app\lib;

use app\model\Service;

if( !class_exists( "ProjectDocuments" ) ):

    class ProjectDocuments
    {
        protected $type;
        protected $database;

        public function __construct( $type )
        {
            $this->type     = $type;
            $this->database = "blueberry";
        }

        public function request( $params )
        {
            switch( $this->type ):
                case"getDocuments":
                    return( $this->getDocuments( $params ) );
                    break;
                case"getDocumentsGroups":
                    return( $this->getDocumentsGroups( $params ) );
                    break;
                case"newProjectDocument":
                    return( $this->newProjectDocument( $params ) );
                break;
                case"newProjectDocumentGroup":
                    $this->newProjectDocumentGroup( $params );
                    break;
            endswitch;
        }

        private function getDocuments( $params )
        {
            $projectId = ( !empty( $params['project_id'] ) ? $params['project_id'] : "" );

            $sql    = "CALL proc_getProjectDocuments(?)";
            $data   = array("project_id" => $projectId);
            $format = array("i");
            $type   = "read";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $returnData  );
        }

        private function getDocumentsGroups( $params )
        {
            $projectId = ( !empty( $params['project_id'] ) ? $params['project_id'] : "" );

            $sql    = "CALL proc_getProjectDocumentsGroups(?)";
            $data   = array("project_id" => $projectId);
            $format = array("i");
            $type   = "read";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):
                return( $returnData );
            else:
                return( false );
            endif;
        }

        private function newProjectDocumentGroup( $params )
        {
            $userId    = ( isset( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
            $projectId = ( !empty( $params['project_id'] ) ? $params['project_id'] : "" );
            $group     = ( !empty( $params['group'] ) ? $params['group'] : "" );
            $date      = date( "Y-m-d" );
            $time      = date( "H:i:s" );
            $id        = "";

            $sql    = "CALL proc_newProjectDocumentGroup(?,?,?,?,?,?)";
            $data   = array(
                            "id"         => $id,
                            "user_id"    => $userId,
                            "project_id" => $projectId,
                            "group"      => $group,
                            "date"       => $date,
                            "time"       => $time
                            );
            $format = array("iiisss");
            $type   = "create";

            ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
        }

        private function newProjectDocument( $params )
        {
            $userId    = ( isset( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
            $projectId = ( !empty( $params['project_id'] ) ? $params['project_id'] : "" );
            $group     = ( !empty( $params['group'] ) ? $params['group'] : "" );
            $fileName  = ( !empty( $params['name'] ) ? $params['name'] : "" );
            $hash      = ( !empty( $params['hash'] ) ? $params['hash'] : "" );
            $ext       = ( !empty( $params['ext'] ) ? $params['ext'] : "" );
            $date      = date( "Y-m-d" );
            $time      = date( "H:i:s" );
            $id        = $output = "";

            $sql    = "CALL proc_newProjectDocument(?,?,?,?,?,?,?,?,?,?)";
            $data   = array(
                            "id"         => $id,
                            "user_id"    => $userId,
                            "project_id" => $projectId,
                            "hash"       => $hash,
                            "name"       => $fileName,
                            "group"      => $group,
                            "ext"        => $ext,
                            "date"       => $date,
                            "time"       => $time,
                            "output"     => $output
                            );
            $format = array("iiissssssi");
            $type   = "createWithOutput";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $returnData );
        }
    }

endif;