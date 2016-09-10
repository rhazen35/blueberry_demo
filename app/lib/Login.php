<?php
/*
 * Login class:
 *
 * - Checks if a user is logged in.
 * - Gets the user pass with a given email.
 * - Gets the users login id.
 * 
 */
namespace app\lib;

use app\model\Service;

if( !class_exists( 'Login' ) ):

    class Login
    {
        protected $email;
        protected $password;

        /**
         * Login constructor.
         * @param $email
         * @param $password
         */

        public function __construct( $email, $password )
        {
            $this->email    = $email;
            $this->password = $password;
        }

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
        public function checkLoginExists()

        {

            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";

            $sql        = "CALL proc_checkLoginExists(?)";
            $data       = array("user_id" => $userId);
            $format     = array("i");

            $type       = "read";

            $returnData = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );

            return( $returnData );

        }

        public function createLogin()
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

        public function getLastLogin()
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

        public function getPreviousLogin()
        {

            $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";

            $sql        = "CALL proc_getPreviousLogin(?)";
            $data       = array("user_id" => $userId);
            $format     = array("i");

            $type       = "read";

            $returnData = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );

            return( $returnData[0]['previous'] );
        }

        public function updateLogin()
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

        public function isLoggedIn()
        {
            if( !isset( $_SESSION['login'] ) && empty( $_SESSION['login'] ) && empty( $_SESSION['userId'] ) ):
                return false;
            else:
                return true;
            endif;
        }

        /**
         * @return mixed
         */

        public function getUserPass()
        {

            $sql        = "CALL proc_getUserPass(?)";
            $data       = array("email" => $this->email);
            $format     = array('s');

            $type       = "read";

            $returnData = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):
               foreach( $returnData as $returnDat ):
                    return($returnDat);
               endforeach;
            else:
                return( false );
            endif;

        }

        /**
         * @return mixed
         */

        public function loginId()
        {

            $sql        = "CALL proc_getUserId(?)";
            $data       = array("email" => $this->email);
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

        public function getUsernameByEmailById( $userId )
        {

            $sql        = "CALL proc_getUserEmailById(?)";
            $data       = array("id" => $userId);
            $format     = array('s');

            $type       = "read";

            $returnData = ( new Service( $type, "litening" ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):

                foreach($returnData as $returnDat):
                    $prefix = substr($returnDat[0], 0, strrpos($returnDat[0], '@'));
                endforeach;

                return($prefix);

            else:

                return(false);

            endif;

        }

    }

endif;

?>