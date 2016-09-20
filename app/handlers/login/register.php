<?php

use app\lib\Register;

$email          = ( !empty( $_POST['email'] ) ? $_POST['email'] : "" );
$password       = ( !empty( $_POST['password'] ) ? $_POST['password'] : "" );
$passwordRepeat = ( !empty( $_POST['passwordRepeat'] ) ? $_POST['passwordRepeat'] : "" );
$params         = array("email" => $email, "password" => $password);

if( !empty( $email ) && !empty( $password ) && !empty( $passwordRepeat ) ):

    $results = ( new Register( "checkEmailExists" ) )->request( $params );
    $id      = "";

    foreach($results as $result):
        $id = $result[0];
    endforeach;

    if( $id === NULL ):
        ( new Register( "register" ) )->request( $params );
        header("Location: index.php");
        exit();
    else:
        header("Location: index.php?registerFailed");
        exit();
    endif;

else:
    header("Location: index.php?registerFailed");
endif;