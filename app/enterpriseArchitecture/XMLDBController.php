<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 14-8-2016
 * Time: 17:05
 */

namespace app\enterpriseArchitecture;

use app\model\Service;

class XMLDBController
{
    protected $type;

    public function __construct( $type )
    {
        $this->type = $type;
    }

    public function request( $params )
    {
        switch( $this->type ):
            case"create":
                $this->dbControl( $params );
                break;
            case"read":
                return( $this->dbControl( $params ) );
                break;
            case"update":
            case"delete":
                $this->dbControl( $params );
                break;
        endswitch;
    }

    private function dbControl( $params )
    {
        $elementName    = $params['element_name'];
        $parsedElements = $params['elements'];
        $multiplicity   = $params['multiplicity'];
        $resultId       = ( !empty( $params['result_id'] ) ? $params['result_id'] : "" );

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

        $deleteSql    = "DELETE FROM " . $tableName . " WHERE id = ?";
        $deleteData   = array("id" => $resultId);
        $deleteFormat = array("i");

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

                                    $updateSql .= ( $countAttributes < $totalAttributes ? $attributeName." = ?, " : $attributeName." = ?" );
                                    $updateData[$attributeName] = $attributeValue;
                                    $updateFormat[]             = "s";

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

        if( $this->type === "create" || $this->type === "update" ):
            if( empty( $selectArray ) || $multiplicity === "1..*" ):
                $type = "create";
                ( new Service( $type, $database ) )->dbAction( $sql, $data, $format );
            else:
                $type = "update";
                ( new Service( $type, $database ) )->dbAction( $updateSql, $updateData, $updateFormat );
            endif;
        else:
            if( $this->type === "read" ):
                return( $selectArray );
            else:
                if( $this->type === "delete" ):
                    $type = "delete";
                    ( new Service( $type, $database ) )->dbAction( $deleteSql, $deleteData, $deleteFormat );
                endif;
            endif;
        endif;
    }

}