<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 15:29
 */

namespace app\lib;

use app\model\Service;

class Register
{
    protected $email;
    protected $password;
    protected $database = "blueberry";

    /**
     * RegisterUser constructor.
     * @param $email
     * @param $password
     */

    public function __construct( $email, $password )
    {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Register a new user
     */

    public function register()
    {
        $emptyspace = '';
        $date       = date('Y-m-d H:i:s');
        $type       = 'verifyEmail';
        $hash       = password_hash( $this->password, PASSWORD_BCRYPT );
        $email_code = $this->randomPassword();

        $sql        = "CALL proc_newUser(?,?,?,?,?,?)";
        $data       = array("id" => $emptyspace, "email" => $this->email, "email_code" => $email_code, "password" => $hash, "type" => $type, "timestamp" => $date);
        $format     = array('isssss');

        $type       = "create";

        ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

    }

    /**
     * @return mixed
     */

    public function checkEmailExists()
    {

        $sql        = "SELECT f_checkEmailExists(?)";
        $data       = array("email" => $this->email);
        $format     = array('s');

        $type       = "read";

        $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        if( !empty( $returnData ) ):
            return( $returnData );
        else:
            return( false );
        endif;
    }

    /**
     * @return string
     */

    private function randomPassword()
    {

        $alphabet    = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass        = array();
        $alphaLength = strlen( $alphabet ) - 1;

        for ($i = 0; $i < 8; $i++) {
            $n = rand( 0, $alphaLength );
            $pass[] = $alphabet[$n];
        }

        return( implode( $pass ) );
    }
}