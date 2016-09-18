<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 14-8-2016
 * Time: 17:05
 */

/**
 * DB Control
 *
 * Handles the database actions called by handlers/elementControl.php
 *
 * - Builds CRUD queries with bind parameters.
 * - Table and column names are based on the parsed xml model. (attributes)
 * - Super type columns first, then sub types. (same as table creation on xml model upload)
 * - Create will turn into an update if there already is data present in the database.
 * - A return message will be added to the page redirect when the action has been executed.
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

    public function request( $params )
    {
        switch( $this->type ):
            case"create":
                return( $this->dbControl( $params ) );
                break;
            case"read":
                return( $this->dbControl( $params ) );
                break;
            case"readSuperType":
                $returnData = array();
                $superType  = ( !empty( $params['element']['super_types'][$params['element_name']] ) ? $params['element']['super_types'][$params['element_name']] : array() );
                if( !empty( $superType ) ):
                    foreach( $superType as $subType ):
                        $params['element_name'] = $subType;
                        $returnData[$subType] = ( $this->dbControl( $params ) );
                    endforeach;
                endif;
                return( $returnData );
                break;
            case"readAttrOnly":
                return( $this->dbControl( $params ) );
                break;
            case"update":
            case"delete":
                return( $this->dbControl( $params ) );
                break;
        endswitch;
    }
    /**
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
        $dbConnection   = "";
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
         * Attributes only read query
         */
        $attrOnlySql    = "SELECT ";
        $attrOnlyWhere  = " WHERE ";
        $attrOnlyData   = array();
        $attrOnlyFormat = array();
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
                /**
                 * Only continue if the database exists.
                 */
                $dbSql        = "CALL proc_checkDatabaseExists(?)";
                $dbData       = array( "db_name" => $database );
                $dbFormat     = array("s");
                $dbConnection = ( new Service( "read", "blueberry" ) )->dbAction( $dbSql, $dbData, $dbFormat );
                if( !empty($dbConnection) && $parsedElement['name'] === $elementName ):
                    $elementAttributes      = ( !empty( $parsedElement['formDetails']['elementAttributes'][$elementName] ) ? $parsedElement['formDetails']['elementAttributes'][$elementName] : array()  );
                    $totalElementAttributes = count($elementAttributes);
                    $superAttributes        = ( !empty( $parsedElement['supertype']['attributes'] ) ? $parsedElement['supertype']['attributes'] : array() );
                    $totalSuperAttributes   = count( $superAttributes );
                    /**
                     * Super type attributes
                     */
                    if( !empty( $superAttributes ) ):
                        $countAttributes = 1;
                        foreach( $superAttributes as $attribute => $array ):
                            if( !empty( $array ) ):
                                $countAttributes++;
                                $attributeName  = ( !empty( $array['input_name'] ) ? str_replace( " ", "_", $array['input_name'] ) : "" );
                                $attributeValue = ( !empty( $_POST[$attributeName] ) ? $_POST[$attributeName] : "" );
                                $attributeName  = strtolower( $attributeName );
                                /**
                                 * Create query
                                 */
                                $data[$attributeName] = $attributeValue;
                                $format[] = "s";
                                $sql .= ",?";
                                /**
                                 * Read query
                                 */
                                $checkSql        .= ( $countAttributes < $totalSuperAttributes ? $attributeName.", " : ( !empty( $elementAttributes ) ? $attributeName.", " : $attributeName."" ) );
                                $attrOnlySql     .= ( $countAttributes < $totalSuperAttributes ? $attributeName.", " : ( !empty( $elementAttributes ) ? $attributeName.", " : $attributeName."" ) );
                                $attrOnlyWhere   .= ( $countAttributes < $totalSuperAttributes ? $attributeName." = ? AND " : ( !empty( $elementAttributes ) ? $attributeName." = ? AND " : $attributeName." = ?" ) );
                                $attrOnlyData[$attributeName] = $attributeValue;
                                $attrOnlyFormat[] = "s";
                                /**
                                 * Update
                                 */
                                $updateSql                 .= ( $countAttributes < $totalSuperAttributes ? $attributeName." = ?, " : ( !empty( $elementAttributes ) ? $attributeName." = ?," : $attributeName." = ?" ) );
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
                            $checkSql        .= ( ($i+1) < $totalElementAttributes ? $attributeName.", " : $attributeName."" );
                            $attrOnlySql     .= ( ($i+1) < $totalElementAttributes ? $attributeName.", " : $attributeName."" );
                            $attrOnlyWhere   .= ( ($i+1) < $totalElementAttributes ? $attributeName." = ? AND " : $attributeName." = ?"  );
                            $attrOnlyData[$attributeName] = $attributeValue;
                            $attrOnlyFormat[] = "s";
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
         * Attributes only read query
         */
        $attrOnlySql            .= " FROM " . $tableName . $attrOnlyWhere . " AND user_id = ? ";
        $attrOnlyData["user_id"] = $userId;
        $attrOnlyFormat[]        = "i";
        /**
         * Update query
         */
        $updateSql            .= " WHERE user_id = ?";
        $updateData["user_id"] = $userId;
        $updateFormat[]        = "i";
        if( !empty( $resultId ) ):
            $updateSql        .= " AND id = ?";
            $updateData["id"]  = $resultId;
            $updateFormat[]    = "i";
        endif;
        /**
         * Execute read query if type is other then delete and the database exists.
         */
        if( $this->type !== "delete" && !empty( $dbConnection ) ):
            $type        = "read";
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
            case"readSuperType":
                return( !empty( $selectArray ) ? $selectArray : false );
                break;
            case"readAttrOnly":
                $type = "read";
                $attrOnly = ( new Service( $type, $database ) )->dbAction( $attrOnlySql, $attrOnlyData, $attrOnlyFormat );
                return( $attrOnly );
                break;
            case"update":
                $type = "update";
                ( new Service( $type, $database ) )->dbAction( $updateSql, $updateData, $updateFormat );
                return("updated");
                break;
            case"delete":
                $type = "delete";
                ( new Service( $type, $database ) )->dbAction( $deleteSql, $deleteData, $deleteFormat );
                return("deleted");
                break;
        endswitch;
    }
}