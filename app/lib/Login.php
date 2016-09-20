<?php

namespace app\lib;

use app\model\Service;

if( !class_exists( 'Login' ) ):

    class Login
    {
        protected $email;
        protected $password;
        /**
         * Login constructor.
         * @param $type
         */
        public function __construct($type )
        {
            $this->type = $type;
        }

        public function request( $params )
        {
            switch( $this->type ):
                case"getUserPass":
                    return( $this->getUserPass( $params ) );
                    break;
                case"login":
                    $this->login();
                    break;
                case"getLoginId":
                    return( $this->getLoginId( $params ) );
                    break;
                case"isLoggedIn":
                    return( $this->isLoggedIn() );
                    break;
                case"getLastLogin":
                    return( $this->getLastLogin() );
                    break;
                case"checkLoginExists":
                    return( $this->checkLoginExists() );
                    break;
                case"getPreviousLogin":
                    return( $this->getPreviousLogin() );
                    break;
                case"getUsernameByEmailById":
                    return( $this->getUsernameByEmailById() );
                    break;
            endswitch;
        }
        /**
         * Check if the login exists and create or update accordingly
         * @param $params;
         */
        public function login()
        {
            $loginExists = $this->checkLoginExists();
            if( empty( $loginExists ) ):
                $this->createLogin();
            else:
                $this->updateLogin();
            endif;
        }
        /**
         * @return bool|\mysqli_result
         */
        private function checkLoginExists()
        {
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $sql        = "CALL proc_checkLoginExists(?)";
            $data       = array("user_id" => $userId);
            $format     = array("i");
            $type       = "read";
            $returnData = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );
            return( $returnData );
        }

        private function createLogin()
        {
            $date       = date("Y-m-d H:i:s");
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $emptyspace = "";
            $sql        = "CALL proc_newLogin(?,?,?,?,?,?)";
            $data       = array("id"        => $emptyspace,
                                "user_id"   => $userId,
                                "previous"  => $emptyspace,
                                "current"   => $date,
                                "first"     => $date,
                                "count"     => 1);
            $format     = array("iisssi");
            $type       = "create";
            ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );
        }
        /**
         * @return bool|\mysqli_result
         */
        private function getLastLogin()
        {
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $sql        = "CALL proc_getLastLogin(?)";
            $data       = array("user_id" => $userId);
            $format     = array("i");
            $type       = "read";
            $returnData = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );
            return( $returnData[0]['current'] );
        }
        /**
         * @return bool|\mysqli_result
         */
        private function getPreviousLogin()
        {
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $sql        = "CALL proc_getPreviousLogin(?)";
            $data       = array("user_id" => $userId);
            $format     = array("i");
            $type       = "read";
            $returnData = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );
            return( $returnData[0]['previous'] );
        }

        private function updateLogin()
        {
            $date       = date("Y-m-d H:i:s");
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $lastLogin  = $this->getLastLogin();

            foreach( $lastLogin as $last ):
                $lastLogin = $last['current'];
            endforeach;

            $sql        = "CALL proc_updateLogin(?,?,?)";
            $data       = array("user_id" => $userId, "previous" => $lastLogin, "current" => $date);
            $format     = array("iss");
            $type       = "update";
            ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );
        }
        /**
         * @return bool
         */
        private function isLoggedIn()
        {
            if( !isset( $_SESSION['login'] ) && empty( $_SESSION['login'] ) && empty( $_SESSION['userId'] ) ):
                return false;
            else:
                return true;
            endif;
        }
        /**
         * @param $params
         * @return bool
         */
        private function getUserPass( $params )
        {
            $sql        = "CALL proc_getUserPass(?)";
            $data       = array("email" => $params['email']);
            $format     = array('s');
            $type       = "read";
            $returnData = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):
               foreach( $returnData as $returnDat ):
                    return( $returnDat );
               endforeach;
            endif;
            return( false );
        }
        /**
         * @param $params
         * @return bool
         */
        private function getLoginId( $params )
        {
            $sql        = "CALL proc_getUserId(?)";
            $data       = array("email" => $params['email']);
            $format     = array('s');
            $type       = "read";
            $returnData = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):
                foreach( $returnData as $returnDat ):
                    return( $returnDat );
                endforeach;
            else:
                return( false );
            endif;
        }
        /**
         * @return bool|string
         */
        private function getUsernameByEmailById()
        {
            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $sql        = "CALL proc_getUserEmailById(?)";
            $data       = array("id" => $userId);
            $format     = array('s');
            $type       = "read";
            $returnData = ( new Service( $type, "litening" ) )->dbAction( $sql, $data, $format );

            $prefix = "";
            if( !empty( $returnData ) ):
                foreach($returnData as $returnDat):
                    $prefix = substr($returnDat[0], 0, strrpos($returnDat[0], '@'));
                endforeach;
                return( $prefix );
            else:
                return(false);
            endif;
        }

    }

endif;

?>