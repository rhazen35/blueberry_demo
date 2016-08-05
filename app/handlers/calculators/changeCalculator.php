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

if( !empty( $calculatorId ) && $changeCheck === "accepted" ):
    $calculator             = ( new IOEAExcelCalculator( $calculatorId ))->getCalculator();
    $calculatorHash         = $calculator['hash'];
    $calculatorExtension    = $calculator['ext'];
    $params['calculator_id']     = $calculatorId;
    $_SESSION['project_id'] = ( !empty( $projectId ) ? $projectId : "" );

    unlink( $_SERVER['DOCUMENT_ROOT'] . '/web/files/excel_calculators_tmp/' . $calculatorHash . "." . $calculatorExtension);

    ( new Calculator( "deleteCalculator" ) )->request( $params );
    header("Location: index.php?newCalculator");
elseif( $changeCheck === "declined" ):
    unset($_SESSION['calculator_id']);
    header("Location: index.php?calculators");
else:
    $_SESSION['calculator_id'] = $calculatorId;
    header("Location: index.php?changeCalculatorAccept");
endif;

exit();