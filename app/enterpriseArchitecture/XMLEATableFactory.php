<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 07-Aug-16
 * Time: 16:55
 */

namespace app\enterpriseArchitecture;

use app\model\Service;

if( !class_exists( "XMLEATableFactory" ) ):

    class XMLEATableFactory
    {
        protected $type;

        /**
         * XMLEATableFactory constructor.
         * @param $type
         */
        public function __construct( $type )
        {
            $this->type = $type;
        }

        /**
         * @param $params
         */
        public function request( $params )
        {
            switch( $this->type ):
                case"create":
                    $this->create( $params );
                    break;
                case"delete":
                    $this->delete( $params );
                    break;
            endswitch;
        }

        /**
         * @param $params
         */
        private function create( $params )
        {
            $dbName           = $this->createDatabase( $params );
            $params['dbName'] = $dbName;

            $this->createTables( $params );
        }

        /**
         * @param $params
         */
        private function delete( $params )
        {
            $dbInfo = $this->getModelDatabaseInfo( $params );
            foreach( $dbInfo as $db ):

                $type = ( isset( $db['type'] ) ? $db['type'] : "" );
                $name = ( isset( $db['name'] ) ? $db['name'] : "" );

                if( $type === "database" ):
                    $params['dbName'] = $name;
                elseif( $type === "table" && !empty( $name ) ):
                    $params['tableName'] = $name;
                    $this->dropTable( $params );
                endif;

            endforeach;

            $this->dropDatabase( $params );
        }

        /**
         * @param $params
         * @return string
         */
        private function createDatabase($params )
        {
            $dbName     = $params['model_name'];
            $dbName     = strtolower( str_replace( " ", "_", $dbName ) );

            $sql        = "CREATE DATABASE IF NOT EXISTS " . $dbName;
            $data       = array();
            $format     = array();
            $type       = "createDatabase";

            ( new Service( $type, "" ) )->dbAction( $sql, $data, $format );

            $params['type'] = "database";
            $params['name'] = $dbName;

            $this->newDbType( $params );

            return( $dbName );

        }


        /**
         * @param $params
         */
        private function createTables($params )
        {
            $elements      = ( new IOXMLEAScreenFactory( $params['model_id'] ) )->extractAndOrderElements();
            $totalElements = count( $elements );

            if( !empty( $elements ) ):

                $data     = array();
                $format   = array();
                $type     = "create";
                $database = ( isset( $params['dbName'] ) ? $params['dbName'] : "" );
                for( $i = 0; $i < $totalElements; $i++ ):

                    if( !empty( $elements[$i] ) ):

                        if( $elements[$i]['root'] !== 'true' ):

                            $tableName      = ( isset( $elements[$i]['name'] ) ? $elements[$i]['name'] : "" );
                            $tableName      = strtolower( str_replace( " ", "_", $tableName ) );
                            $params['type'] = "table";
                            $params['name'] = $tableName;

                            $columnsSuper   = ( isset( $elements[$i]['supertype']['attributes'] ) ? $elements[$i]['supertype']['attributes'] : "" );
                            $columns        = ( isset( $elements[$i]['attributes'] ) ? $elements[$i]['attributes'] : "" );

                            /**
                             * * * START OF TABLE * * *
                             */
                            $sql  = "CREATE TABLE IF NOT EXISTS " . $tableName ." ( ";
                            $sql .= " id INT(11) NOT NULL AUTO_INCREMENT, ";
                            $sql .= " user_id INT(11) NOT NULL, ";

                            if( !empty( $columnsSuper ) ):
                                foreach( $columnsSuper as $columnSuper ):
                                    $columnName = ( isset( $columnSuper['input_name'] ) ? $columnSuper['input_name'] : "" );
                                    if( !empty( $columnName ) ):
                                        $columnName = strtolower( str_replace( " ", "_", $columnName ) );
                                        $sql .= " " . $columnName . " VARCHAR(150) NOT NULL, ";
                                    endif;
                                endforeach;
                            endif;

                            if( !empty( $columns ) ):
                                foreach( $columns as $column ):
                                    $columnName = ( isset( $column['input_name'] ) ? $column['input_name'] : "" );
                                    if( !empty( $columnName ) ):
                                        $columnName = strtolower( str_replace( " ", "_", $columnName ) );
                                        $sql .= " " . $columnName . " VARCHAR(150) NOT NULL, ";
                                    endif;
                                endforeach;
                            endif;

                            $sql .= " date DATE NOT NULL, ";
                            $sql .= " time TIME NOT NULL, ";
                            $sql .= " PRIMARY KEY(id) ";
                            $sql .= " ) ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
                            /**
                             * * * END OF TABLE * * *
                             */
                            $this->newDbType( $params );
                            ( new Service( $type, $database ) )->dbAction( $sql, $data, $format );

                        endif;

                    endif;

                endfor;

            endif;
        }

        /**
         * @param $params
         * @return bool|\mysqli_result
         */
        private function getModelDatabaseInfo($params )
        {
            $modelId    = ( isset( $params['model_id'] ) ? $params['model_id'] : "" );

            if( !empty( $modelId ) ):

                $sql        = "CALL proc_getModelDatabaseInfo(?)";
                $data       = array("model_id" => $modelId,);
                $format     = array("i");
                $type       = "read";

                $returnData = ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );

                return( $returnData );

            endif;
        }

        /**
         * @param $params
         */
        private function newDbType( $params )
        {
            $date       = date("Y-m-d");
            $time       = date("H:i:s");
            $id         = "";

            $sql        = "CALL proc_newModelDbType(?,?,?,?,?,?)";
            $data       = array(
                            "id" => $id,
                            "model_id" => $params['model_id'],
                            "type" => $params['type'],
                            "name" => $params['name'],
                            "date" => $date,
                            "time" => $time
                        );
            $format     = array("iissss");
            $type       = "create";

            ( new Service( $type, "blueberry" ) )->dbAction( $sql, $data, $format );
        }

        private function dropTable( $params )
        {
            if( !empty( $params['tableName'] ) ):
                $sql        = "DROP TABLE IF EXISTS " . $params['tableName'];
                $data       = array();
                $format     = array();
                $type       = "delete";

                ( new Service( $type, "" ) )->dbAction( $sql, $data, $format );
            endif;
        }

        /**
         * @param $params
         */
        private function dropDatabase( $params )
        {
            if( !empty( $params['dbName'] ) ):
                $sql        = "DROP DATABASE " . $params['dbName'];
                $data       = array();
                $format     = array();
                $type       = "deleteDatabase";

                ( new Service( $type, "" ) )->dbAction( $sql, $data, $format );
            endif;
        }

    }

endif;