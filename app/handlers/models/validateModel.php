<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-8-2016
 * Time: 20:19
 */

use app\enterpriseArchitecture\IOXMLModelUpload;
use app\enterpriseArchitecture\IOXMLEAModel;
use app\core\Library;

$validationStartTime = microtime(true);

$modelId                = ( isset( $_POST['modelId'] ) ? $_POST['modelId'] : "" );
$_SESSION['xmlModelId'] = $modelId;
$model                  = ( new IOXMLEAModel( $modelId ) )->getModel();
$modelHash              = ( isset( $model['hash'] ) ? $model['hash'] : "" );

if( !empty( $modelHash ) ):

    echo $xmlFile = 'web/files/xml_models_tmp/'.$modelHash.'.xml';
    $report  = ( new IOXMLModelUpload( "validateModel", $xmlFile, $uploadedAt = null ) )->request( $params = null );

    $validationEndTime              = Library::microtimeFormat( $validationStartTime );
    $report['validationDuration']   = $validationEndTime;
    $_SESSION['xmlValidatorReport'] = serialize( $report );

    header("Location: index.php?xmlEAValidatorReport");
    exit();

endif;