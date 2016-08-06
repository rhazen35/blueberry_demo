<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 5-8-2016
 * Time: 15:54
 */

use app\enterpriseArchitecture\IOEAExcelCalculator;
use app\lib\Calculator;

$changeCheck  = ( isset( $_POST['changeCheck'] ) ? $_POST['changeCheck'] : "" );
$projectId    = ( isset( $_POST['projectId'] ) ? $_POST['projectId'] : "" );
$calculatorId = ( isset( $_POST['calculatorId'] ) ? $_POST['calculatorId'] : "" );

/**
 * Check if the calculator id is available and if the deletion check is accepeted
 */
if( !empty( $calculatorId ) && $changeCheck === "accepted" ):
    $calculator              = ( new IOEAExcelCalculator( $calculatorId ))->getCalculator();
    $calculatorHash          = ( isset( $calculator['hash'] ) ? $calculator['hash'] : "" );
    $calculatorExtension     = ( isset( $calculator['ext'] ) ? $calculator['ext'] : "" );
    $_SESSION['project_id']  = ( !empty( $projectId ) ? $projectId : "" );

    $params['calculator_id'] = $calculatorId;

    /**
     * Check if the calculator hash and extension are available and delete the file
     */
    if( !empty( $calculatorHash ) && !empty( $calculatorExtension ) ):
        unlink( $_SERVER['DOCUMENT_ROOT'] . '/web/files/excel_calculators_tmp/' . $calculatorHash . "." . $calculatorExtension);
    endif;

    /**
     * Delete the calculator
     */
    ( new Calculator( "deleteCalculator" ) )->request( $params );
    header("Location: index.php?newCalculator");
    exit();

/**
 * Redirect if calculator deletion has been declined
 */
elseif( $changeCheck === "declined" ):
    unset($_SESSION['calculator_id']);
    header("Location: index.php?calculators");
    exit();
/**
 * Redirect and ask for permission to delete the calculator
 */
else:
    $_SESSION['calculator_id'] = $calculatorId;
    header("Location: index.php?changeCalculatorAccept");
    exit();
endif;