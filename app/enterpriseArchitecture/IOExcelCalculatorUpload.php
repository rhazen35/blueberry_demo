<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-8-2016
 * Time: 16:19
 */

namespace app\enterpriseArchitecture;

use app\model\Service;

if( !class_exists( "IOExcelCalculatorUpload" ) ):

    class IOExcelCalculatorUpload
    {
        protected $type;
        protected $calculatorFile;
        protected $uploadedAt;
        protected $database = "blueberry";
        /**
         * IOExcelCalculatorUpload constructor.
         * @param $type
         * @param $calculatorFile
         * @param $uploadedAt
         */
        public function __construct($type, $calculatorFile, $uploadedAt )
        {
            $this->type           = $type;
            $this->calculatorFile = $calculatorFile;
            $this->uploadedAt     = $uploadedAt;
        }
        /**
         * @param $params
         * @return bool|\mysqli_result
         */
        public function request($params )
        {
            switch( $this->type ):
                case"saveCalculator":
                    return( $this->saveCalculator( $params ) );
                    break;
                case"matchHash":
                    return( $this->matchHash() );
                    break;
            endswitch;
        }
        /**
         * @return bool
         */
        private function matchHash()
        {
            $sql        = "CALL proc_getMatchingCalculatorHash(?)";
            $data       = array("hash" => $this->calculatorFile);
            $format     = array('s');
            $type       = "read";
            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):
                foreach( $returnData as $returnDat ):
                    return( $returnDat );
                endforeach;
            endif;
            return( false );
        }
        /**
         * @param $params
         * @return bool|\mysqli_result
         */
        private function saveCalculator( $params )
        {
            $datetime    = new \DateTime( $this->uploadedAt );
            $upload_date = $datetime->format('Y-m-d');
            $upload_time = $datetime->format('H:i:s');
            $userId      = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $id          = $output = "";
            $sql         = "CALL proc_newCalculator(?,?,?,?,?,?,?)";
            $data        = array(
                "id"            => $id,
                "user_id"       => $userId,
                "hash"          => $this->calculatorFile,
                "ext"           => $params['extension'],
                "date"          => $upload_date,
                "time"          => $upload_time,
                "output"        => $output
            );
            $format         = array("iissssi");
            $type           = "createWithOutput";
            $lastInsertedId = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $lastInsertedId );
        }

    }

endif;