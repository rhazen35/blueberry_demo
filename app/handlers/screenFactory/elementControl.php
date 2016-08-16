<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 07-Aug-16
 * Time: 16:05
 */

use app\enterpriseArchitecture\IOXMLEAScreenFactory;
use app\enterpriseArchitecture\XMLDBController;

/**
 * Handle form based on the action and the multiplicity.
 *
 * - Actions are CRUD and will be handled according to their multiplicity.
 * - Delete actions will be handled without multiplicity and target one result.
 *
 * - If the multiplicity is at least 1, the user will be redirected forward, one page.
 * - If the multiplicity is at least 1 and or more, the user will be redirected to the page he/she/it came from.
 * - If the multiplicity is 0 or more, the user will be redirected to the page he/she/it came from.
 *
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
            $returnMessage = ( new XMLDBController( "create" ) )->request( $params );
            $returnMessage = ( !empty( $returnMessage ) ? $returnMessage : "" );
            switch( $multiplicity ):
                case"1":
                    header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder ) . "&".$returnMessage );
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
                ( new XMLDBController( "update" ) )->request( $params );
                header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder - 1 ) . "&edited" );
                exit();
            endif;
            break;
        case"delete":
            if( !empty( $resultId ) ):
                $params['result_id'] = $resultId;
                ( new XMLDBController( "delete" ) )->request( $params );
                header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder - 1 ) . "&deleted" );
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