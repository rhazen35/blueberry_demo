<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 14-8-2016
 * Time: 17:05
 */

namespace app\enterpriseArchitecture;

use app\model\Service;
/**
 * Class XMLDBController
 * @package app\enterpriseArchitecture
 */
class XMLDBController
{
    protected $type;
    /**
     * XMLDBController constructor.
     * @param $type
     */
    public function __construct( $type )
    {
        $this->type = $type;
    }
    /**
     * @param $params
     * @return bool|\mysqli_result
     */
    public function request( $params )
    {
        switch( $this->type ):
            case"create":
                return( $this->dbControl( $params ) );
                break;
            case"read":
                return( $this->dbControl( $params ) );
                break;
            case"update":
            case"delete":
                return( $this->dbControl( $params ) );
                break;
        endswitch;
    }
    /**
     * DB Control
     *
     * - Builds CRUD queries with bind parameters.
     * - Table and column names are based on the parsed xml model. (attributes)
     * - Super type columns first, then sub types. (same as table creation on xml model upload)
     *
     * @param $params
     * @return bool|\mysqli_result
     */
    private function dbControl( $params )
    {
        /**
         * Collect data
         */
        $elementName    = $params['element_name'];
        $parsedElements = $params['elements'];
        $multiplicity   = $params['multiplicity'];
        $resultId       = ( !empty( $params['result_id'] ) ? $params['result_id'] : "" );
        $userId         = ( isset( $_SESSION['userId'] ) ? $_SESSION['userId'] : "" );
        $tableName      = strtolower( str_replace( " ", "_", $elementName ) );
        $database       = "";
        $date           = date( "Y-m-d" );
        $time           = date( "H:i:s" );
        /**
         * Create query
         */
        $sql       = "INSERT INTO " . $tableName. " VALUES(?,?";
        $data      = array("id" => "", "user_id" => $userId);
        $format    = array();
        $format[]  = "i";
        $format[]  = "i";
        /**
         * Read query
         */
        $checkSql = "SELECT id, user_id, ";
        $checkData     = array();
        $checkFormat   = array();
        /**
         * Update query
         */
        $updateSql = "UPDATE " . $tableName . " SET ";
        $updateData     = array();
        $updateFormat   = array();
        /**
         * Delete query
         */
        $deleteSql    = "DELETE FROM " . $tableName . " WHERE id = ?";
        $deleteData   = array("id" => $resultId);
        $deleteFormat = array("i");
        /**
         * Build column names and compare them with the data
         */
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
                    $totalSuperAttributes   = count( $superAttributes );
                    /**
                     * Super type attributes
                     */
                    if( !empty( $superAttributes ) ):
                        $countAttributes = 1;
                        foreach( $superAttributes as $attribute => $array ):
                            if( !empty( $array ) ):
                                $countAttributes++;
                                $attributeName  = ( isset( $array['input_name'] ) ? str_replace( " ", "_", $array['input_name'] ) : "" );
                                $attributeValue = ( isset( $_POST[$attributeName] ) ? $_POST[$attributeName] : "" );
                                $attributeName = strtolower( $attributeName );
                                /**
                                 * Create query
                                 */
                                $data[$attributeName] = $attributeValue;
                                $format[] = "s";
                                $sql .= ",?";
                                /**
                                 * Read query
                                 */
                                $checkSql .= ( $countAttributes < $totalSuperAttributes ? $attributeName.", " : ( !empty( $elementAttributes ) ? $attributeName.", " : $attributeName."" ) );
                                /**
                                 * Update
                                 */
                                $updateSql .= ( $countAttributes < $totalSuperAttributes ? $attributeName." = ?, " : ( !empty( $elementAttributes ) ? $attributeName." = ?," : $attributeName." = ?" ) );
                                $updateData[$attributeName] = $attributeValue;
                                $updateFormat[]             = "s";
                            endif;
                        endforeach;
                    endif;
                    /**
                     * Sub type attributes
                     */
                    if( !empty( $elementAttributes ) ):
                        for( $i = 0; $i < $totalElementAttributes; $i++ ):
                            $attributeName  = ( isset( $elementAttributes[$i]['name'] ) ? str_replace( " ", "_", $elementAttributes[$i]['name'] ) : "" );
                            $attributeValue = ( isset( $_POST[$attributeName] ) ? $_POST[$attributeName] : "" );
                            $attributeName  = strtolower( $attributeName );
                            /**
                             * Create query
                             */
                            $data[$attributeName] = $attributeValue;
                            $format[]             = "s";
                            $sql                 .= ",?";
                            /**
                             * Read query
                             */
                            $checkSql .= ( ($i+1) < $totalElementAttributes ? $attributeName.", " : $attributeName."" );
                            /**
                             * Update query
                             */
                            $updateSql                 .= ( ($i+1) < $totalElementAttributes ? $attributeName." = ?, " : $attributeName." = ?"  );
                            $updateData[$attributeName] = $attributeValue;
                            $updateFormat[]             = "s";
                        endfor;
                    endif;
                    break;
                endif;
            endforeach;
        endif;
        /**
         * Create query
         */
        $data["date"] = $date;
        $data["time"] = $time;
        $format[]     = "s";
        $format[]     = "s";
        $sql         .= ",?,?)";
        /**
         * Read query
         */
        $checkSql            .= " FROM " . $tableName . " WHERE user_id = ? ORDER BY date,time DESC ";
        $checkData["user_id"] = $userId;
        $checkFormat[]        = "i";
        /**
         * Update query
         */
        $updateSql            .= " WHERE user_id = ?";
        $updateData["user_id"] = $userId;
        $updateFormat[]        = "i";
        if( !empty( $resultId ) ):
            $updateSql .= " AND id = ?";
            $updateData["id"]      = $resultId;
            $updateFormat[]        = "i";
        endif;
        /**
         * Execute read query if type is other then delete.
         */
        if( $this->type !== "delete" ):
            $type = "read";
            $selectArray = ( new Service( $type, $database ) )->dbAction( $checkSql, $checkData, $checkFormat );
        endif;
        /**
         * Execute one of the CRUD types if requested.
         */
        switch( $this->type ):
            case"create":
                if( empty( $selectArray ) || $multiplicity === "1..*" || $multiplicity === "0..*" || $multiplicity === "" ):
                    $type = "create";
                    ( new Service( $type, $database ) )->dbAction( $sql, $data, $format );
                    return("created");
                else:
                    $type = "update";
                    ( new Service( $type, $database ) )->dbAction( $updateSql, $updateData, $updateFormat );
                    return("updated");
                endif;
                break;
            case"read":
                return( !empty( $selectArray ) ? $selectArray : false );
                break;
            case"update":
                $type = "update";
                ( new Service( $type, $database ) )->dbAction( $updateSql, $updateData, $updateFormat );
                return("updated");
                break;
            case"delete":
                $type = "delete";
                ( new Service( $type, $database ) )->dbAction( $deleteSql, $deleteData, $deleteFormat );
                return("delete");
                break;
        endswitch;
    }
}