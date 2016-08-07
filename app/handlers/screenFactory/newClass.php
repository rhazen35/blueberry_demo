<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 07-Aug-16
 * Time: 16:05
 */

use app\enterpriseArchitecture\IOXMLEAModelParser;
use app\enterpriseArchitecture\IOXMLEAModel;

$elementOrder = ( isset( $_POST['elementOrder'] ) ? $_POST['elementOrder'] : "" );
echo $elementName  = ( isset( $_POST['elementName'] ) ? $_POST['elementName'] : "" );
echo $modelId      = ( isset( $_POST['modelId'] ) ? $_POST['modelId'] : "" );

$model        = ( new IOXMLEAModel( $modelId ) )->getModel();
$modelHash    = ( isset( $model['hash'] ) ? $model['hash'] : "" );
$modelExt     = ( isset( $model['ext'] ) ? $model['ext'] : "" );

if( !empty( $modelHash ) && !empty( $modelExt ) ):

    $xmlFile       = 'web/files/xml_models_tmp/' . $modelHash . '.' . $modelExt;
    $parsedClasses = ( new IOXMLEAModelParser( $xmlFile ) )->parseXMLClasses();

    foreach( $parsedClasses as $parsedClass ):
        if( $parsedClass['name'] === $elementName ):
            var_dump($parsedClass);
            break;
        endif;
    endforeach;

endif;




//header( "Location: " . APPLICATION_HOME . "?model&page=" .$elementOrder );
//exit();