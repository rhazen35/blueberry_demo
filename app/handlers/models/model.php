<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 2-8-2016
 * Time: 18:44
 */

use app\lib\Project;
use app\core\Library;
use app\enterpriseArchitecture\IOXMLExcelUser;
use app\enterpriseArchitecture\IOEAExcelCalculator;

/**
 * Handle the model request.
 *
 * - Send to upload when no model is linked to the project, otherwise send to the preview.
 * - Copies the calculator base file if it does not exists already.
 * - New file name consists of a hash based on the user id, extensions stays unchanged.
 */
$projectId              = ( isset( $_POST['projectId'] ) ? $_POST['projectId'] : "" );
$userId                 = ( isset( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
$params                 = array("project_id" => $projectId);
$modelId                = ( new Project( "getModelIdByProjectId" ) )->request( $params );
$userExcelHash          = ( new IOXMLExcelUser( "getUserExcelHash" ) )->request( $params );
$getCalculatorId        = ( new IOEAExcelCalculator( "getCalculatorIdByProjectId" ) )->request( $params );
$calculatorId           = ( !empty( $getCalculatorId ) ? $getCalculatorId['calculator_id'] : "" );


$_SESSION['project_id'] = $projectId;

if( empty( $userExcelHash ) && !empty( $calculatorId ) ):
    $userExcelHash           = sha1( $userId );
    $params['hash']          = $userExcelHash;
    $params['calculator_id'] = $calculatorId;
    $calculator              = ( new IOEAExcelCalculator( "getCalculator" ) )->request( $params );
    $params['ext']           = ( isset( $calculator['ext'] ) ? $calculator['ext'] : "" );

    if(!empty( $calculator )):
        $baseFile   = Library::path( APPLICATION_PATH . "web/files/excel_calculators/" . $calculator['hash'] . "." . $calculator['ext'] );
    else:
        $baseFile    = "";
    endif;

    if( !empty( $baseFile ) ):
        $newFile = Library::path( APPLICATION_PATH . "web/files/excel_calculators_tmp/" . $userExcelHash . "." . $calculator['ext'] );
        if( copy( $baseFile, $newFile ) ):
            ( ( new IOXMLExcelUser( "newUserExcelHash" ) )->request( $params ) );
        endif;
    else:
        echo 'Error with the base file (excel)';
    endif;

endif;

if( isset( $modelId['model_id'] ) ):
    $_SESSION['xmlModelId'] = $modelId['model_id'];
    header("Location: index.php?model");
    exit();
else:
    header("Location: index.php?newModel");
    exit();
endif;
