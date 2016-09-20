<?php

namespace app\lib;

use app\model\Service;
use app\core\Library as Lib;

if( !class_exists( "Register" ) ):

    class Register
    {
        protected $type;
        protected $database = "blueberry";
        /**
         * RegisterUser constructor.
         * @param $type
         */
        public function __construct( $type )
        {
            $this->type = $type;
        }
        /**
         * @param $params
         * @return mixed
         */
        public function request($params )
        {
            switch( $this->type ):
                case"register":
                    $this->register( $params );
                    break;
                case"checkEmailExists":
                    return( $this->checkEmailExists( $params ) );
                    break;
            endswitch;
        }
        /**
         * @param $params
         */
        private function register( $params )
        {
            $emptyspace = '';
            $date       = date('Y-m-d H:i:s');
            $type       = 'verifyEmail';
            $hash       = password_hash( $params['password'], PASSWORD_BCRYPT );
            $email_code = Lib::randomPassword();
            $sql        = "CALL proc_newUser(?,?,?,?,?,?)";
            $data       = array("id" => $emptyspace, "email" => $params['email'], "email_code" => $email_code, "password" => $hash, "type" => $type, "timestamp" => $date);
            $format     = array('isssss');
            $type       = "create";
            ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
        }
        /**
         * @param $params
         * @return mixed
         */
        private function checkEmailExists( $params )
        {
            $sql        = "SELECT f_checkEmailExists(?)";
            $data       = array("email" => $params['email']);
            $format     = array('s');
            $type       = "read";
            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):
                return( $returnData );
            else:
                return( false );
            endif;
        }

    }

endif;