<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-8-2016
 * Time: 21:36
 */

$type    = ( isset( $_POST['type'] ) ? $_POST['type'] : "" );
$display = ( isset( $_POST['display'] ) ? $_POST['display'] : "" );
$modelId = ( isset( $_POST['model_id'] ) ? $_POST['model_id'] : "" );

$_SESSION['xmlModelId'] = $modelId;

switch( $type ):

    case"severe":
        $_SESSION['xmlModelErrorSevere']  = $display;
        break;
    case"error":
        $_SESSION['xmlModelErrorError']   = $display;
        break;
    case"warning":
        $_SESSION['xmlModelErrorWarning'] = $display;
        break;
    case"info":
        $_SESSION['xmlModelErrorInfo']    = $display;
        break;
    case"valid":
        $_SESSION['xmlModelErrorValid']   = $display;
        break;

endswitch;

header("Location: ".APPLICATION_HOME."?xmlEAValidatorReport");
exit();
