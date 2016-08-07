<?php

namespace app\model;

use app\core;
use app\database\Database;
use app\model\data\Create as DbCreate;
use app\model\data\Read as DbRead;
use app\model\data\Update as DbUpdate;
use app\model\data\Delete as DbDelete;

/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 9-6-2016
 * Time: 15:25
 */

if(!class_exists( "Service" )):

    class Service
    {
        protected $type;

        /**
         * Service constructor.
         * @param $type
         * @param $database
         */
        
        public function __construct( $type, $database )
        {
            $this->type = $type;
            $this->database = $database;
        }

        /**
         * @param $sql
         * @param $data
         * @param $format
         * @return bool|\mysqli_result
         */

        public function dbAction( $sql, $data, $format )
        {
            if ( !in_array( $this->type, ['create', 'createWithOutput', 'createDatabase', 'read', 'update', 'delete', 'deleteDatabase'] ) ):

                echo 'Error: An invalid $type was passed to Service::dbAction()! Instantiation aborted!';
                return( false );

            else:

                switch($this->type):

                    case"create":
                        ( new DbCreate( $sql, $this->database ) )->dbInsert( $data, $format );
                        break;
                    case"createWithOutput":
                        $lastInsertedID = ( new DbCreate( $sql, $this->database ) )->dbInsertOut( $data, $format );
                        return( $lastInsertedID );
                        break;
                    case"createDatabase":
                        ( new DbCreate( $sql, $this->database ) )->dbCreateDatabase( $data, $format );
                        break;
                    case"read":
                        $returnData = ( new DbRead( $sql, $this->database ) )->dbSelect( $data, $format );
                        return($returnData);
                        break;
                    case"update":
                        ( new DbUpdate( $sql, $this->database ) )->dbUpdate( $data, $format );
                        break;
                    case"delete":
                        ( new DbDelete( $sql, $this->database ) )->dbDelete( $data, $format );
                        break;
                    case"deleteDatabase":
                        ( new DbDelete( $sql, $this->database ) )->dbDeleteDatabase( $data, $format );
                        break;
                endswitch;

            endif;
        }

        /**
         * @return bool
         */

        public function checkDbServerConnection()
        {
            $mysqli     = (new Database( $this->database ))->checkDbConnection();

            if( $mysqli ):

                return( true );
            else:
                return( false );

            endif;
        }

        /**
         * @return bool
         */

        public function checkDbConnection()
        {
            $mysqli     = (new Database( $this->database ))->dbConnect();

            if( $mysqli ):

                return( true );
            else:
                return( false );

            endif;
        }
    }

endif;
