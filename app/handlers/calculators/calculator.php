<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-8-2016
 * Time: 15:09
 */

use app\lib\Calculator;

$projectId              = ( isset( $_POST['projectId'] ) ? $_POST['projectId'] : "" );
$_SESSION['project_id'] = $projectId;
$params                 = array("project_id" => $projectId);
$calculatorId           = ( new Calculator( "getCalculatorIdByProjectId" ) )->request( $params );

if( isset( $calculatorId['calculator_id'] ) ):
    $_SESSION['calculator_id'] = $modelId['calculator_id'];
    header("Location: index.php?calculator");
    exit();
else:
    header("Location: index.php?newCalculator");
    exit();
endif;