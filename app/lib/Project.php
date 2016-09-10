<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 28-7-2016
 * Time: 15:46
 */

namespace app\lib;

use app\model\Service;

if( !class_exists( "Project" ) ):

    class Project
    {

        protected $type;
        protected $database = "blueberry";

        public function __construct( $type )
        {
            $this->type = $type;
        }

        public function request( $params )
        {
            switch( $this->type ):
                case"checkProjectExists":
                    return( $this->checkProjectExists( $params ) );
                case"newProject":
                    return( $this->newProject( $params ) );
                    break;
                case"getProjectById":
                    return( $this->getProjectById( $params ) );
                    break;
                case"getProjectByModelId":
                    return( $this->getProjectByModelId( $params ) );
                    break;
                case"getProjectByCalculatorId":
                    return( $this->getProjectByCalculatorId( $params ) );
                    break;
                case"saveModelJoinTable":
                    $this->saveModelJoinTable( $params );
                    break;
                case"saveCalculatorJoinTable":
                    $this->saveCalculatorJoinTable( $params );
                    break;
                    break;
                case"getAllProjectsByUser":
                    return( $this->getAllProjectsByUser() );
                    break;
                case"getAllProjectsModelsByUser":
                    return( $this->getAllProjectsModelsByUser() );
                    break;
                case"getAllProjectsCalculatorsByUser":
                    return( $this->getAllProjectsCalculatorsByUser() );
                    break;
                case"getModelIdByProjectId":
                    return( $this->getModelIdByProjectId( $params ) );
                    break;
                case"getCalculatorIdByProjectId":
                    return( $this->getCalculatorIdByProjectId( $params ) );
                    break;
                case"deleteProject":
                    $this->deleteProject( $params );
                    break;
                case"countProjects":
                    return( $this->countProjects() );
                    break;
            endswitch;
        }

        private function checkProjectExists( $params )
        {
            $sql         = "CALL proc_checkProjectExists(?)";
            $data        = array("name" => $params['name']);
            $format      = array("s");
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            foreach( $returnData as $returnDat ):
                $id      = $returnDat['id'];
            endforeach;

            return( empty( $id ) ? false : true );
        }

        private function newProject( $params )
        {
            $date       = date( "Y-m-d" );
            $time       = date( "H:i:s" );
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $id         = $output = "";
            $status     = "validation";
            $sql        = "CALL proc_newProject(?,?,?,?,?,?,?,?)";
            $data       = array(
                "id"            => $id,
                "user_id"       => $userId,
                'name'          => $params['name'],
                'description'   => $params['description'],
                'status'        => $status,
                "date"          => $date,
                "time"          => $time,
                "output"        => $output
            );
            $format     = array("iisssssi");
            $type       = "createWithOutput";

            $lastInsertId = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $lastInsertId );
        }

        private function getProjectById( $params )
        {
            $sql         = "CALL proc_getProjectById(?)";
            $data        = array("project_id" => $params['project_id']);
            $format      = array("i");
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
            $returnArray = array();

            foreach( $returnData as $returnDat ):
                $returnArray['name']        = $returnDat['name'];
                $returnArray['description'] = $returnDat['description'];
                $returnArray['status']      = $returnDat['status'];
                $returnArray['date']        = $returnDat['date'];
                $returnArray['time']        = $returnDat['time'];
            endforeach;

            return( $returnArray );
        }

        private function getProjectByModelId( $params )
        {
            $sql         = "CALL proc_getProjectByModelId(?)";
            $data        = array("model_id" => $params['model_id']);
            $format      = array("i");
            $type        = "read";

            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
            $returnArray = array();

            foreach( $returnData as $returnDat ):
                $returnArray['id']          = $returnDat['id'];
                $returnArray['name']        = $returnDat['name'];
                $returnArray['description'] = $returnDat['description'];
                $returnArray['status']      = $returnDat['status'];
                $returnArray['date']        = $returnDat['date'];
                $returnArray['time']        = $returnDat['time'];
            endforeach;

            return( $returnArray );
        }

        private function getProjectByCalculatorId( $params )
        {
            $sql         = "CALL proc_getProjectByCalculatorId(?)";
            $data        = array("calculator_id" => $params['calculator_id']);
            $format      = array("i");
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
            $returnArray = array();

            foreach( $returnData as $returnDat ):
                $returnArray['id'] = $returnDat['id'];
                $returnArray['name'] = $returnDat['name'];
                $returnArray['description'] = $returnDat['description'];
                $returnArray['status'] = $returnDat['status'];
                $returnArray['date'] = $returnDat['date'];
                $returnArray['time'] = $returnDat['time'];
            endforeach;

            return( $returnArray );
        }

        private function saveModelJoinTable( $params )
        {
            $userId      = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $projectId   = !empty( $_SESSION['project_id'] ) ? $_SESSION['project_id'] : "";
            $id          = "";
            $sql         = "CALL proc_newProjectModel(?,?,?,?)";
            $data        = array(
                "id"            => $id,
                "user_id"       => $userId,
                'project_id'    => $projectId,
                'model_id'      => $params['model_id']
            );
            $format     = array("iiii");
            $type       = "create";

            ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
        }

        private function saveCalculatorJoinTable( $params )
        {
            $userId      = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $projectId   = !empty( $_SESSION['project_id'] ) ? $_SESSION['project_id'] : "";
            $id          = "";
            $sql         = "CALL proc_newProjectCalculator(?,?,?,?)";
            $data        = array(
                "id"            => $id,
                "user_id"       => $userId,
                'project_id'    => $projectId,
                'calculator'      => $params['calculator_id']
            );
            $format     = array("iiii");
            $type       = "create";

            ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
        }

        private function getAllProjectsByUser()
        {
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $sql        = "CALL proc_getAllProjectsByUser(?)";
            $data       = array( "user_id" => $userId );
            $format     = array("i");
            $type       = "read";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $returnData );
        }

        private function getAllProjectsModelsByUser()
        {
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $sql        = "CALL proc_getAllProjectsModelsByUser(?)";
            $data       = array( "user_id" => $userId );
            $format     = array("i");
            $type       = "read";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $returnData );
        }

        private function getAllProjectsCalculatorsByUser()
        {
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $sql        = "CALL proc_getAllProjectsCalculatorsByUser(?)";
            $data       = array( "user_id" => $userId );
            $format     = array("i");
            $type       = "read";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $returnData );
        }

        private function getModelIdByProjectId( $params )
        {
            $sql         = "CALL proc_getModelIdByProjectId(?)";
            $data        = array("id" => $params['project_id']);
            $format      = array("i");
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
            $returnArray = array();

            foreach( $returnData as $returnDat ):
                $returnArray['model_id'] = $returnDat['model_id'];
            endforeach;

            return( $returnArray );
        }

        private function getCalculatorIdByProjectId( $params )
        {
            $sql         = "CALL proc_getCalculatorIdByProjectId(?)";
            $data        = array("id" => $params['project_id']);
            $format      = array("i");
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
            $returnArray = array();

            foreach( $returnData as $returnDat ):
                $returnArray['calculator_id'] = $returnDat['calculator_id'];
            endforeach;

            return( $returnArray );
        }

        private function deleteProject( $params )
        {
            $projectId    = ( isset( $params['project_id'] ) ? $params['project_id'] : "" );
            $modelId      = ( isset( $params['model_id'] ) ? $params['model_id'] : "" );
            $calculatorId = ( isset( $params['calculator_id'] ) ? $params['calculator_id'] : "" );
            $sql          = "CALL proc_deleteProject(?,?,?)";
            $data         = array("project_id" => $projectId, "model_id" => $modelId, "calculator_id" => $calculatorId);
            $format       = array("iii");
            $type         = "delete";

            ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
        }

        private function countProjects()
        {
            $sql         = "CALL proc_countProjects()";
            $data        = array();
            $format      = array();
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            $count = 0;
            foreach( $returnData[0] as $key => $value ):
                $count = $value;
            endforeach;

            return( $count );
        }

    }

endif;