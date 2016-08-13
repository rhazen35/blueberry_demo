<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 07-Aug-16
 * Time: 16:05
 */

use app\enterpriseArchitecture\IOXMLEAScreenFactory;
use app\model\Service;

$superElement   = ( isset( $_POST['superForm'] ) ? $_POST['superForm'] : "" );
$elementOrder   = ( isset( $_POST['elementOrder'] ) ? $_POST['elementOrder'] : "" );
$elementName    = ( isset( $_POST['elementName'] ) ? $_POST['elementName'] : "" );
$modelId        = ( isset( $_POST['modelId'] ) ? $_POST['modelId'] : "" );
$multiplicity   = ( isset( $_POST['multiplicity'] ) ? $_POST['multiplicity'] : "" );
$parsedElements = ( new IOXMLEAScreenFactory( "extractAndOrderElements", $modelId ) )->request( $params = null );

if( $superElement === "true" ):
    echo 'supershizzle here';

else:

        $userId    = ( isset( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
        $tableName = strtolower( $elementName );
        $database  = "";
        $date      = date( "Y-m-d" );
        $time      = date( "H:i:s" );

        $sql       = "INSERT INTO " . $tableName. " VALUES(?,?";

        $data      = array("id" => "", "user_id" => $userId);
        $format    = array();
        $format[]  = "i";
        $format[]  = "i";

        $checkSql = "SELECT id, user_id, ";

        $checkData     = array();
        $checkFormat   = array();

        $updateSql = "UPDATE " . $tableName . " SET ";

        $updateData     = array();
        $updateFormat   = array();

        if( !empty( $parsedElements ) ):

            foreach( $parsedElements as $parsedElement ):
                if( $parsedElement['isRoot'] === "true" ):
                    $database = $parsedElement['name'];
                    $database = strtolower( str_replace( " ", "_", $database ) );
                endif;

                if( $parsedElement['name'] === $elementName ):

                    if( !empty( $parsedElement['formDetails']['elementAttributes'][$elementName] ) ):
                        $attributes      = $parsedElement['formDetails']['elementAttributes'][$elementName];
                        $totalAttributes = count($attributes);
                        for( $i = 0; $i < $totalAttributes; $i++ ):
                            $attributeName  = ( isset( $attributes[$i]['name'] ) ? str_replace( " ", "_", $attributes[$i]['name'] ) : "" );
                            $attributeValue = ( isset( $_POST[$attributeName] ) ? $_POST[$attributeName] : "" );

                            $attributeName        = strtolower( $attributeName );
                            $data[$attributeName] = $attributeValue;
                            $format[]             = "s";
                            $sql                 .= ",?";

                            $checkSql .= ( ($i+1) < $totalAttributes ? $attributeName.", " : $attributeName."" );

                            $updateSql .= ( ($i+1) < $totalAttributes ? $attributeName." = ?, " : $attributeName." = ?" );
                            $updateData[$attributeName] = $attributeValue;
                            $updateFormat[]             = "s";

                        endfor;
                        break;

                    else:
                        if( !empty( $parsedElement['supertype']['attributes'] ) ):

                            $attributes = $parsedElement['supertype']['attributes'];
                            $totalAttributes = count( $attributes );

                            $countAttributes = 1;
                            foreach( $attributes as $attribute => $array ):
                                if( !empty( $array ) ):
                                    $countAttributes++;
                                    $attributeName  = ( isset( $array['input_name'] ) ? str_replace( " ", "_", $array['input_name'] ) : "" );
                                    $attributeValue = ( isset( $_POST[$attributeName] ) ? $_POST[$attributeName] : "" );

                                    $attributeName = strtolower( $attributeName );
                                    $data[$attributeName] = $attributeValue;
                                    $format[] = "s";
                                    $sql .= ",?";

                                    $checkSql .= ( $countAttributes < $totalAttributes ? $attributeName.", " : $attributeName."" );
                                    $checkData[$attributeName] = $attributeValue;
                                    $checkFormat[]             = "s";

                                endif;
                            endforeach;

                            break;
                        endif;

                    endif;

                endif;

            endforeach;

        endif;

        $data["date"] = $date;
        $data["time"] = $time;
        $format[]     = "s";
        $format[]     = "s";
        $sql         .= ",?,?)";

        $checkSql            .= " FROM " . $tableName . " WHERE user_id = ? ";
        $checkData["user_id"] = $userId;
        $checkFormat[]        = "i";

        $updateSql            .= " WHERE user_id = ? ";
        $updateData["user_id"] = $userId;
        $updateFormat[]        = "i";

        $type = "read";

        $selectArray = ( new Service( $type, $database ) )->dbAction( $checkSql, $checkData, $checkFormat );

        if( !empty( $selectArray ) ):
            foreach( $selectArray as $select ):

            endforeach;
        endif;

        $type = "create";

        ( new Service( $type, $database ) )->dbAction( $sql, $data, $format );

        if( $multiplicity === "1..*" ):
            header( "Location: " . APPLICATION_HOME . "?model&page=" . ( $elementOrder - 1 ) ."&add" );
            exit();
        else:
            header( "Location: " . APPLICATION_HOME . "?model&page=" .$elementOrder );
            exit();
        endif;



endif;