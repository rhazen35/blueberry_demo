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

            $this->createDatabaseStructure( $params );
        }
        /**
         * @param $params
         */
        private function delete( $params )
        {
            $dbName =  $params['name'];
            $params['dbName'] = $dbName;
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
            return( $dbName );
        }
        /**
         * @param $params
         */
        private function createDatabaseStructure($params )
        {
            /**
             * Database structure building
             * Create tables for all extracted and ordered elements.
             */
            $queries       = array();
            $elements      = ( new IOXMLEAScreenFactory( "extractAndOrderElements", $params['model_id'] ) )->request( $params );
            $totalElements = count( $elements );
            if( !empty( $elements ) ):
                $data      = array();
                $format    = array();
                $database  = ( isset( $params['dbName'] ) ? $params['dbName'] : "" );
                for( $i = 0; $i < $totalElements; $i++ ):
                    if( !empty( $elements[$i] ) ):
                        if( $elements[$i]['isRoot'] !== 'true' ):
                            $elementName            = ( isset( $elements[$i]['name'] ) ? $elements[$i]['name'] : "" );
                            $tableName              = ( isset( $elements[$i]['name'] ) ? $elements[$i]['name'] : "" );
                            $tableName              = strtolower( str_replace( " ", "_", $tableName ) );
                            $columnsSuper           = ( isset( $elements[$i]['supertype']['attributes'] ) ? $elements[$i]['supertype']['attributes'] : "" );
                            $columns                = array();
                            $elementAttributes      = ( !empty( $elements[$i]['formDetails']['elementAttributes'][$elementName] ) ? $elements[$i]['formDetails']['elementAttributes'][$elementName] : "" );
                            $totalElementAttributes = count( $elementAttributes );
                            if( !empty( $elementAttributes ) ):
                                for( $j = 0; $j < $totalElementAttributes; $j++ ):
                                    $columns[] = $elementAttributes[$j]['name'];
                                endfor;
                            endif;
                            /**
                             * * * START OF TABLE * * *
                             */
                            $sql  = " CREATE TABLE IF NOT EXISTS " . $tableName ." ( ";
                            $sql .= " id INT(11) NOT NULL AUTO_INCREMENT, ";
                            $sql .= " user_id INT(11) NOT NULL, ";
                            /**
                             * Super type columns first
                             */
                            if( !empty( $columnsSuper ) ):
                                $countColumnsSuper = 0;
                                foreach( $columnsSuper as $columnSuper ):
                                    $countColumnsSuper++;
                                    $columnName = ( isset( $columnSuper['input_name'] ) ? $columnSuper['input_name'] : "" );
                                    if( !empty( $columnName ) ):
                                        $columnName = strtolower( str_replace( " ", "_", $columnName ) );
                                        $sql .= " " . $columnName . " VARCHAR(150) NOT NULL, ";
                                    endif;
                                endforeach;
                            endif;
                            /**
                             * Sub type columns
                             */
                            if( !empty( $columns ) ):
                                $countColumns = 0;
                                foreach( $columns as $column ):
                                    $columnName = strtolower( str_replace( " ", "_", $column ) );
                                    $countColumns++;
                                    $sql .= " " . $columnName . " VARCHAR(150) NOT NULL, ";
                                endforeach;
                            endif;
                            $sql .= " date DATE NOT NULL, ";
                            $sql .= " time TIME NOT NULL, ";
                            $sql .= " PRIMARY KEY(id) ";
                            $sql .= " ) ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
                            /**
                             * * * END OF TABLE * * *
                             */
                            $queries[] = $sql;
                        endif;
                    endif;
                endfor;
                $type = "create";
                foreach($queries as $query):
                    ( new Service( $type, $database ) )->dbAction( $query, $data, $format );
                endforeach;
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