<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 11:44
 */

use app\lib\Login;

if( $_SERVER['REQUEST_METHOD'] === 'POST' ):

    $email      = (isset($_POST['email']) ? $_POST['email'] : "");
    $email      = trim($email);
    $password   = (isset($_POST['password']) ? $_POST['password'] : "");

    if( empty( $email ) && empty( $password ) ):
        /**
         * TODO: Validation
         */
        header("Location: index.php?loginFailed");
        exit();
    else:
        $returnPass = ( new Login( $email, " " ) )->getUserPass();

        $verify = !empty( $returnPass['password'] ) ? password_verify( $password, $returnPass['password'] ) : "";

        if( $verify ):

            $results = ( new Login( $email, " " ) )->loginId();
            $userId  = ( !empty( $results['id'] ) ? $results['id'] : "" );

            $_SESSION['login']  = true;
            $_SESSION['userId'] = isset($userId) ? $userId : "";

            ( new Login( $email, " " ) )->login();

            header("Location: index.php");
            exit();

        else:
            header("Location: index.php?loginFailed");
            exit();
        endif;
    endif;
endif;

?>