<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 07-Aug-16
 * Time: 16:05
 */

use app\enterpriseArchitecture\IOXMLEAScreenFactory;
use app\enterpriseArchitecture\XMLDBController;

$action         = ( isset( $_POST['action'] ) ? $_POST['action'] : "" );
$superElement   = ( isset( $_POST['superForm'] ) ? $_POST['superForm'] : "" );
$elementOrder   = ( isset( $_POST['elementOrder'] ) ? $_POST['elementOrder'] : "" );
$elementName    = ( isset( $_POST['elementName'] ) ? $_POST['elementName'] : "" );
$modelId        = ( isset( $_POST['modelId'] ) ? $_POST['modelId'] : "" );
$multiplicity   = ( isset( $_POST['multiplicity'] ) ? $_POST['multiplicity'] : "" );
$resultId       = ( isset( $_POST['resultId'] ) ? $_POST['resultId'] : "" );

$parsedElements = ( new IOXMLEAScreenFactory( "extractAndOrderElements", $modelId ) )->request( $params = null );

if( $superElement === "true" ):
    echo 'supershizzle here';

else:

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

    if( !empty( $action ) ):
        $params['element_name'] = $elementName;
        $params['elements']     = $parsedElements;
        $params['multiplicity'] = $multiplicity;

        switch( $action ):
            case"create":
                ( new XMLDBController( "create" ) )->request( $params );
                switch( $multiplicity ):
                    case"1":
                        header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder ) . "&created" );
                        exit();
                        break;
                    case"1..*":
                        header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder - 1 ) . "&created" );
                        exit();
                        break;
                endswitch;
                break;
            case"delete":
                if( !empty( $resultId ) ):
                    $params['result_id'] = $resultId;
                    ( new XMLDBController( "delete" ) )->request( $params );
                    header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder - 1 ) . "&deleted" );
                    exit();
                endif;
                break;
        endswitch;

    endif;

endif;