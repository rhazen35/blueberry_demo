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

        public function __construct( $type )
        {
            $this->type = $type;
        }

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

        private function create( $params )
        {
            $this->createDatabase( $params );
        }

        private function delete( $params )
        {
            $this->dropDatabase( $params );
        }

        private function createDatabase( $params )
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

        }

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

        private function dropDatabase( $params )
        {
            $sql        = "DROP DATABASE " . $params['name'] . ";";
            $data       = array();
            $format     = array();
            $type       = "deleteDatabase";

            ( new Service( $type, "" ) )->dbAction( $sql, $data, $format );
        }


    }

endif;