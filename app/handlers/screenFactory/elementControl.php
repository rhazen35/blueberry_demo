<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 07-Aug-16
 * Time: 16:05
 */

use app\enterpriseArchitecture\IOXMLEAScreenFactory;
use app\enterpriseArchitecture\XMLDBController;
use app\enterpriseArchitecture\IOElementExcelFactory;

/**
 * Handle form based on the posted action and the posted multiplicity.
 *
 * - Actions are CRUD and will be handled according to their multiplicity.
 * - Delete actions will be handled without multiplicity and target one specific row.
 *
 * - If the multiplicity is at least 1 and 1, the user will be redirected forward, one page.
 * - If the multiplicity is at least 1 or 0 and more, the user will be redirected to the page he/she/it came from
 */

$action         = ( isset( $_POST['action'] ) ? $_POST['action'] : "" );
$elementOrder   = ( isset( $_POST['elementOrder'] ) ? $_POST['elementOrder'] : "" );
$elementName    = ( isset( $_POST['elementName'] ) ? $_POST['elementName'] : "" );
$modelId        = ( isset( $_POST['modelId'] ) ? $_POST['modelId'] : "" );
$multiplicity   = ( isset( $_POST['multiplicity'] ) ? $_POST['multiplicity'] : "" );
$resultId       = ( isset( $_POST['resultId'] ) ? $_POST['resultId'] : "" );
$subElement     = ( isset( $_POST['subElement'] ) ? $_POST['subElement'] : "" );

$parsedElements = ( new IOXMLEAScreenFactory( "extractAndOrderElements", $modelId ) )->request( $params = null );

if( !empty( $action ) ):
    $params['element_name'] = $elementName;
    $params['elements']     = $parsedElements;
    $params['multiplicity'] = $multiplicity;

    switch( $action ):
        case"create":
            $returnMessage     = ( new XMLDBController( "create" ) )->request( $params );
            $returnMessage     = ( !empty( $returnMessage ) ? $returnMessage : "" );
            $elementData       = ( new XMLDBController( "read" ) )->request( $params );
            $projectName       = ( !empty( $_SESSION['project'] ) ? $_SESSION['project'] : "" );
            $params['project'] = $projectName;
            $params['data']    = $elementData;
            $excel             = ( new IOElementExcelFactory( "dataToFile" ) )->request( $params );
            $operations        = ( new IOElementExcelFactory( "getOperations" ) )->request( $params );

            switch( $multiplicity ):
                case"1":
                    header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder ) . "&" . $returnMessage );
                    exit();
                    break;
                case"1..*":
                case"0..*":
                case"":
                    header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder - 1 ) . "&".$returnMessage );
                    exit();
                    break;
            endswitch;
            break;
        case"edit":
            if( !empty( $resultId ) ):
                $params['result_id'] = $resultId;
                $returnMessage = ( new XMLDBController( "update" ) )->request( $params );
                //header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder - 1 ) . "&".$returnMessage );
                //exit();
            endif;
            break;
        case"delete":
            if( !empty( $resultId ) ):
                $params['result_id'] = $resultId;
                $returnMessage = ( new XMLDBController( "delete" ) )->request( $params );
                header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder - 1 ) . "&".$returnMessage );
                exit();
            endif;
            break;
        case"addForm":
            if( !empty( $subElement ) ):
                header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder - 1 ) . "&addForm=" . rawurlencode( $subElement ) );
                exit();
            endif;
            break;
    endswitch;

endif;