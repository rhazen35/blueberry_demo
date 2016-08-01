<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 22:24
 */

session_start();

unset( $_SESSION['userId'] );
unset( $_SESSION['login'] );

header( "Location: ..". DIRECTORY_SEPARATOR ."index.php" );