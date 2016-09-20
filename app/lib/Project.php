<?php

namespace app\lib;

use app\model\Service;

if( !class_exists( "Project" ) ):

    class Project
    {
        protected $type;
        protected $database = "blueberry";
        /**
         * Project constructor.
         * @param $type
         */
        public function __construct( $type )
        {
            $this->type = $type;
        }
        /**
         * @param $params
         * @return int|string
         */
        public function request( $params )
        {
            switch( $this->type ):
                case"checkProjectExists":
                    return( $this->checkProjectExists( $params ) );
                case"newProject":
                    return( $this->newProject( $params ) );
                    break;
                case"newProjectSettings":
                    $this->newProjectSettings( $params );
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
                case"getProjectsSettings":
                    return( $this->getProjectsSettings( $params ) );
                    break;
                case"saveModelJoinTable":
                    $this->saveModelJoinTable( $params );
                    break;
                case"saveCalculatorJoinTable":
                    $this->saveCalculatorJoinTable( $params );
                    break;
                case"getAllProjects":
                    return( $this->getAllProjects() );
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
                case"countProjects":
                    return( $this->countProjects() );
                    break;
                case"getLastAddedProject":
                    return( $this->getLastAddedProject() );
                    break;
                case"getLastAddedProjectByUser":
                    return( $this->getLastAddedProjectByUser() );
                    break;
                case"deleteProject":
                    $this->deleteProject( $params );
                    break;
            endswitch;
        }
        /**
         * @param $params
         * @return bool
         */
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
        /**
         * @param $params
         * @return bool|\mysqli_result
         */
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
            $format       = array("iisssssi");
            $type         = "createWithOutput";
            $lastInsertId = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $lastInsertId );
        }
        /**
         * @param $params
         */
        private function newProjectSettings($params )
        {
            $date       = date( "Y-m-d" );
            $time       = date( "H:i:s" );
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $id         = "";
            $type       = ( !empty( $params['type'] ) ? $params['type'] : "" );
            $projectId  = ( !empty( $params['projectId'] ) ? $params['projectId'] : "" );
            $sql        = "CALL proc_newProjectSettings(?,?,?,?,?,?)";
            $data       = array(
                "id"            => $id,
                "user_id"       => $userId,
                "project_id"    => $projectId,
                'type'          => $type,
                "date"          => $date,
                "time"          => $time
            );
            $format     = array("iiisss");
            $type       = "create";
            ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
        }
        /**
         * @param $params
         * @return array
         */
        private function getProjectById($params )
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
        /**
         * @param $params
         * @return array
         */
        private function getProjectByModelId($params )
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
        /**
         * @param $params
         * @return array
         */
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
        /**
         * @param $params
         * @return array
         */
        private function getProjectsSettings($params )
        {
            $sql         = "CALL proc_getProjectSettings(?)";
            $data        = array("project_id" => $params['project_id']);
            $format      = array("i");
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
            $returnArray = array();

            foreach( $returnData as $returnDat ):
                $returnArray['id']          = $returnDat['id'];
                $returnArray['user_id']     = $returnDat['user_id'];
                $returnArray['project_id']  = $returnDat['project_id'];
                $returnArray['type']        = $returnDat['type'];
                $returnArray['date']        = $returnDat['date'];
                $returnArray['time']        = $returnDat['time'];
            endforeach;

            return( $returnArray );
        }
        /**
         * @param $params
         */
        private function saveModelJoinTable($params )
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
        /**
         * @param $params
         */
        private function saveCalculatorJoinTable($params )
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
        /**
         * @return bool|\mysqli_result
         */
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
        /**
         * @return bool|\mysqli_result
         */
        private function getAllProjects()
        {
            $sql        = "CALL proc_getAllProjects()";
            $data       = array();
            $format     = array();
            $type       = "read";
            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
            return( $returnData );
        }
        /**
         * @return bool|\mysqli_result
         */
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
        /**
         * @return bool|\mysqli_result
         */
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
        /**
         * @param $params
         * @return array
         */
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
        /**
         * @param $params
         * @return array
         */
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

        /**
         * @param $params
         */
        private function deleteProject($params )
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
        /**
         * @return int
         */
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
        /**
         * @return string
         */
        private function getLastAddedProject()
        {
            $sql         = "CALL proc_getLastAddedProject()";
            $data        = array();
            $format      = array();
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            $project = "";
            foreach( $returnData[0] as $key => $value ):
                $project = $value;
            endforeach;

            return( $project );
        }
        /**
         * @return string
         */
        private function getLastAddedProjectByUser()
        {
            $userId      = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $sql         = "CALL proc_getLastAddedProjectByUser(?)";
            $data        = array("user_id" => $userId);
            $format      = array("i");
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            $project = "";
            foreach( $returnData[0] as $key => $value ):
                $project = $value;
            endforeach;

            return( $project );
        }

    }

endif;