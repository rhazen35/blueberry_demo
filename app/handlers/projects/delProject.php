<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 17:22
 */

use app\core\Library;
use app\lib\Project;
use app\enterpriseArchitecture\IOXMLEAModel;
use app\enterpriseArchitecture\IOEAExcelCalculator;

$projectId   = ( !empty( $_POST['projectId'] ) ? $_POST['projectId'] : "" );
$deleteCheck = ( isset( $_POST['deleteCheck'] ) ? $_POST['deleteCheck'] : "" );

if( !empty( $projectId ) && $deleteCheck === "accepted" ):

    $params         = array( "project_id" => $projectId );
    $modelId        = ( new Project( "getModelIdByProjectId" ) )->request( $params );
    $calculatorId   = ( new Project( "getCalculatorIdByProjectId" ) )->request( $params );
    $modelId        = ( isset( $modelId['model_id'] ) ? $modelId['model_id'] : "" );
    $calculatorId   = ( isset( $calculatorId['calculator_id'] ) ? $calculatorId['calculator_id'] : "" );

    if( !empty( $modelId ) ):
        $model               = ( new IOXMLEAModel( $modelId ))->getModel();
        $calculator          = ( new IOEAExcelCalculator( $calculatorId ))->getCalculator();
        $modelHash           = $model['hash'];
        $calculatorHash      = $calculator['hash'];
        $modelExtension      = $model['ext'];
        $calculatorExtension = $calculator['ext'];

        $params['model_id']      = $modelId;
        $params['calculator_id'] = $calculatorId;

        unlink( Library::path($_SERVER['DOCUMENT_ROOT'] . '/web/files/xml_models_tmp/' . $modelHash . '.' . $modelExtension));
        unlink( Library::path($_SERVER['DOCUMENT_ROOT'] . '/web/files/excel_calculators_tmp/' . $calculatorHash . '.' . $calculatorExtension));

    endif;

    ( new Project( "deleteProject" ) )->request( $params );
    header("Location: index.php?projects");

elseif( $deleteCheck === "declined" ):
    unset($_SESSION['delProjectId']);
    header("Location: index.php?projects");
else:
    $_SESSION['delProjectId'] = $projectId;
    header("Location: index.php?deleteProjectAccept");
endif;

exit();