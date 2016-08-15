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
        $tableName = strtolower( str_replace( " ", "_", $elementName ) );
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

                    $elementAttributes      = ( isset( $parsedElement['formDetails']['elementAttributes'][$elementName] ) ? $parsedElement['formDetails']['elementAttributes'][$elementName] : ""  );
                    $totalElementAttributes = count($elementAttributes);
                    $superAttributes        = ( isset( $parsedElement['supertype']['attributes'] ) ? $parsedElement['supertype']['attributes'] : "" );
                    $totalSuperAttributes  = count( $superAttributes );

                    if( !empty( $superAttributes ) ):
                        $countAttributes = 1;
                        foreach( $superAttributes as $attribute => $array ):
                            if( !empty( $array ) ):
                                $countAttributes++;
                                $attributeName  = ( isset( $array['input_name'] ) ? str_replace( " ", "_", $array['input_name'] ) : "" );
                                $attributeValue = ( isset( $_POST[$attributeName] ) ? $_POST[$attributeName] : "" );

                                $attributeName = strtolower( $attributeName );
                                $data[$attributeName] = $attributeValue;
                                $format[] = "s";
                                $sql .= ",?";

                                $checkSql .= ( $countAttributes < $totalSuperAttributes ? $attributeName.", " : ( !empty( $elementAttributes ) ? $attributeName.", " : $attributeName."" ) );

                                $updateSql .= ( $countAttributes < $totalSuperAttributes ? $attributeName." = ?, " : ( !empty( $elementAttributes ) ? $attributeName." = ?," : $attributeName." = ?" ) );
                                $updateData[$attributeName] = $attributeValue;
                                $updateFormat[]             = "s";

                            endif;
                        endforeach;

                    endif;

                    if( !empty( $elementAttributes ) ):
                        for( $i = 0; $i < $totalElementAttributes; $i++ ):
                            $attributeName  = ( isset( $elementAttributes[$i]['name'] ) ? str_replace( " ", "_", $elementAttributes[$i]['name'] ) : "" );
                            $attributeValue = ( isset( $_POST[$attributeName] ) ? $_POST[$attributeName] : "" );

                            $attributeName        = strtolower( $attributeName );
                            $data[$attributeName] = $attributeValue;
                            $format[]             = "s";
                            $sql                 .= ",?";

                            $checkSql .= ( ($i+1) < $totalElementAttributes ? $attributeName.", " : $attributeName."" );

                            $updateSql .= ( ($i+1) < $totalElementAttributes ? $attributeName." = ?, " : $attributeName." = ?"  );
                            $updateData[$attributeName] = $attributeValue;
                            $updateFormat[]             = "s";

                        endfor;

                    endif;

                    break;
                endif;

            endforeach;

        endif;

        $data["date"] = $date;
        $data["time"] = $time;
        $format[]     = "s";
        $format[]     = "s";
        $sql         .= ",?,?)";

        $checkSql            .= " FROM " . $tableName . " WHERE user_id = ? ORDER BY date,time DESC ";
        $checkData["user_id"] = $userId;
        $checkFormat[]        = "i";

        $updateSql            .= " WHERE user_id = ?";
        $updateData["user_id"] = $userId;
        $updateFormat[]        = "i";

        if( !empty( $resultId ) ):
            $updateSql .= " AND id = ?";
            $updateData["id"]      = $resultId;
            $updateFormat[]        = "i";
        endif;

        if( $this->type !== "delete" ):
            $type = "read";
            $selectArray = ( new Service( $type, $database ) )->dbAction( $checkSql, $checkData, $checkFormat );
        endif;

        switch( $this->type ):

            case"create":
                if( empty( $selectArray ) || $multiplicity === "1..*" || $multiplicity === "0..*" || $multiplicity === "" ):
                    $type = "create";
                    ( new Service( $type, $database ) )->dbAction( $sql, $data, $format );
                else:
                    $type = "update";
                    ( new Service( $type, $database ) )->dbAction( $updateSql, $updateData, $updateFormat );
                endif;
                break;
            case"read":
                return( !empty( $selectArray ) ? $selectArray : false );
                break;
            case"update":
                $type = "update";
                ( new Service( $type, $database ) )->dbAction( $updateSql, $updateData, $updateFormat );
                break;
            case"delete":
                $type = "delete";
                ( new Service( $type, $database ) )->dbAction( $deleteSql, $deleteData, $deleteFormat );
                break;

        endswitch;
    }

}