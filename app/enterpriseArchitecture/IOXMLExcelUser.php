<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 03-Sep-16
 * Time: 22:37
 */

namespace app\enterpriseArchitecture;

use app\model\Service;

if( !class_exists( "IOXMLExcelUser" ) ):

    class IOXMLExcelUser
    {
        protected $type;
        /**
         * @param $type
         */
        public function __construct( $type )
        {
            $this->type = $type;
        }
        /**
         * @param $params
         * @return bool|\mysqli_result
         */
        public function request( $params )
        {
            switch( $this->type ):
                case"getUserExcelHash":
                    return( $this->getUserExcelHash() );
                    break;
                case"newUserExcelHash":
                    $this->newUserExcelHash( $params );
                    break;
                case"getUserExcel":
                    return( $this->getUserExcel() );
                    break;
            endswitch;
        }
        /**
         * @return bool|\mysqli_result
         */
        private function getUserExcelHash()
        {
            $userId = ( !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );

            $sql    = "CALL proc_getUserExcelHash(?)";
            $data   = array("user_id" => $userId);
            $format = array("i");
            $type   = "read";

            $userExcelHash = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );

            if( !empty( $userExcelHash ) ):
                return( $userExcelHash[0] );
            else:
                return( false );
            endif;
        }

        private function newUserExcelHash( $params )
        {
            $userId     = ( !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
            $hash       = ( !empty( $params['hash'] ) ? $params['hash'] : "" );
            $ext        = ( !empty( $params['ext'] ) ? $params['ext'] : "" );
            $date       = date("Y-m-d");
            $time       = date("H:i:s");
            $emptyspace = "";

            $sql    = "CALL proc_newUserExcelHash(?,?,?,?,?,?)";
            $data   = array("id" => $emptyspace, "user_id" => $userId, "hash" => $hash, "ext" => $ext, "date" => $date, "time" => $time);
            $format = array("iissss");
            $type   = "create";

            ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );
        }

        private function getUserExcel()
        {
            $userId = ( !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );

            $sql    = "CALL proc_getUserExcel(?)";
            $data   = array("user_id" => $userId);
            $format = array("i");
            $type   = "read";

            $userExcel = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );

            if( !empty( $userExcel ) ):
                return( $userExcel[0] );
            else:
                return( false );
            endif;
        }

    }

endif;