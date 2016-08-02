<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 15:23
 */

use app\lib\Login;
use app\lib\Register;

$email = ( !empty( $_POST['email'] ) ? $_POST['email'] : "" );
$password = ( !empty( $_POST['password'] ) ? $_POST['password'] : "" );
$passwordRepeat = ( !empty( $_POST['passwordRepeat'] ) ? $_POST['passwordRepeat'] : "" );

if( !empty( $email ) && !empty( $password ) && !empty( $passwordRepeat ) ):

    $results = (new Register( $email, $password ))->checkEmailExists();

    $id = "";
    foreach($results as $result):
        $id = $result[0];
    endforeach;

    if( $id === NULL ):
        ( new Register( $email, $password ) )->register();
        header("Location: index.php");
        exit();
    else:
        header("Location: index.php?registerFailed");
        exit();
    endif;

else:
    header("Location: index.php?registerFailed");
endif;