<?php

namespace app\lib;

use app\model\Service;

if( !class_exists( "Calculator" ) ):

    class Calculator
    {
        protected $type;
        protected $database = "blueberry";
        /**
         * Calculator constructor.
         * @param $type
         */
        public function __construct($type )
        {
            $this->type = $type;
        }
        /**
         * @param $params
         * @return array|bool|int|\mysqli_result
         */
        public function request($params )
        {
            switch( $this->type ):
                case"countCalculators":
                    return( $this->countCalculators() );
                    break;
                case"getCalculatorIdByProjectId":
                    return( $this->getCalculatorIdByProjectId( $params ) );
                    break;
                case "getAllCalculatorsByUser":
                    return( $this->getAllCalculatorsByUser( $params ) );
                    break;
                case"deleteCalculator":
                    $this->deleteCalculator( $params );
                    break;
            endswitch;
        }
        /**
         * @return bool|\mysqli_result
         */
        private function getAllCalculatorsByUser()
        {
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $sql        = "CALL proc_getAllCalculatorsByUser(?)";
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
        private function deleteCalculator($params )
        {
            $calculatorId = !empty( $params['calculator_id'] ) ?$params['calculator_id'] : "";
            $sql          = "CALL proc_deleteCalculator(?)";
            $data         = array( "calculator_id" => $calculatorId );
            $format       = array("i");
            $type         = "delete";
            ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
        }
        /**
         * @return int
         */
        private function countCalculators()
        {
            $sql         = "CALL proc_countCalculators()";
            $data        = array();
            $format      = array();
            $type        = "read";
            $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
            $count       = 0;
            foreach( $returnData[0] as $key => $value ):
                $count = $value;
            endforeach;
            return( $count );
        }

    }

endif;