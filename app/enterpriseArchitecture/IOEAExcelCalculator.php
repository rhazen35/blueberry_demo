<?php

namespace app\enterpriseArchitecture;

use app\model\Service;

if( !class_exists( "IOEAExcelCalculator" ) ):

    class IOEAExcelCalculator
    {
        protected $type;
        protected $database = "blueberry";
        /**
         * IOEAExcelCalculator constructor.
         * @param $type
         */
        public function __construct( $type )
        {
            $this->type = $type;
        }
        /**
         * @param $params
         * @return bool
         */
        public function request($params )
        {
            switch( $this->type ):
                case"getCalculator":
                    return( $this->getCalculator( $params ) );
                    break;
                case"getCalculatorIdByProjectId":
                    return( $this->getCalculatorIdByProjectId( $params ) );
                    break;
            endswitch;
        }
        /**
         * @param $params
         * @return bool
         */
        private function getCalculator( $params )
        {
            $sql          = "CALL proc_getCalculator(?)";
            $data         = array("id" => $params['calculator_id']);
            $format       = array('i');
            $type         = "read";
            $returnData   = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):
                return( $returnData[0] );
            else:
                return( false );
            endif;
        }
        /**
         * @param $params
         * @return bool
         */
        private function getCalculatorIdByProjectId( $params )
        {
            $sql           = "CALL proc_getCalculatorIdByProjectId(?)";
            $data          = array("project_id" => $params['project_id']);
            $format        = array('i');
            $type          = "read";
            $returnData    = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):
                $calculatorId = $returnData[0];
                return( $calculatorId );
            else:
                return( false );
            endif;
        }


    }

endif;