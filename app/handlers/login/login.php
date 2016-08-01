<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 11:44
 */

use app\core\Login;

if( $_SERVER['REQUEST_METHOD'] === 'POST' ):

    $email      = (isset($_POST['email']) ? $_POST['email'] : "");
    $email      = trim($email);
    $password   = (isset($_POST['password']) ? $_POST['password'] : "");

    if( empty( $email ) && empty( $password ) ):
        /**
         * TODO: Validation
         */
    else:
        $results = ( new Login( $email, " " ) )->getUserPass();

        foreach( $results as $result ):
            $returnPass = $result[0];
        endforeach;

        $verify = !empty( $returnPass ) ? password_verify( $password, $returnPass ) : "";

        if( $verify ):

            $results = ( new Login( $email, " " ) )->loginId();

            foreach( $results as $result ):

                $userId = $result[0];

            endforeach;

            $_SESSION['login']  = true;
            $_SESSION['userId'] = isset($userId) ? $userId : "";

            ( new Login( $email, " " ) )->login();

            header( "Location: index.php" );

        else:
            echo "Incorrect username/password combination";
        endif;
    endif;
endif;