<?php

namespace app\model\data;

use app\database\Database;

/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 9-6-2016
 * Time: 15:37
 */

if(!class_exists( "Create" )):

    class Create
    {

        protected $sql;

        /**
         * DbCreate constructor.
         * @param $sql
         * @param $database
         */
        public function __construct( $sql, $database )
        {
            $this->sql      = $sql;
            $this->database = $database;
        }

        /**
         * @param $data
         * @param $format
         */
        public function  dbInsert( $data, $format )

        {
            $mysqli     = ( new Database( $this->database ) )->dbConnect();
            $stmt       = $mysqli->prepare( $this->sql );

            if( !empty( $format ) && !empty( $data ) ):
                $format = implode( '', $format );
                $format = str_replace( '%', '', $format );
                
                array_unshift( $data, $format );
                call_user_func_array( array( $stmt, 'bind_param' ), ( new Database( $this->database ) )->referenceValues( $data ) );
            endif;

            $stmt->execute();
            $stmt->close();
            $mysqli->close();
        }

        /**
         * @param $data
         * @param $format
         * @return string
         */
        public function dbInsertOut($data, $format )

        {
            $mysqli     = ( new Database( $this->database ) )->dbConnect();
            $stmt       = $mysqli->prepare( $this->sql );

            if( !empty( $format ) && !empty( $data ) ):
                $format = implode( '', $format );
                $format = str_replace( '%', '', $format );

                array_unshift( $data, $format );
                call_user_func_array( array( $stmt, 'bind_param' ), ( new Database( $this->database ) )->referenceValues( $data ) );
            endif;

            $stmt->execute();
            $lastInsertedId = "";

            if( $stmt->bind_result( $id ) ):
                while( $row = $stmt->fetch() ):
                    $lastInsertedId = $id;
                endwhile;
            endif;

            $stmt->close();
            $mysqli->close();

            return( $lastInsertedId );
        }

        public function dbCreateDatabase()
        {
            $mysqli     = ( new Database( "" ) )->checkDbConnection();
            $stmt       = $mysqli->prepare( $this->sql );

            if( !empty( $format ) && !empty( $data ) ):
                $format = implode( '', $format );
                $format = str_replace( '%', '', $format );

                array_unshift( $data, $format );
                call_user_func_array( array( $stmt, 'bind_param' ), ( new Database(  ) )->referenceValues( $data ) );
            endif;

            $stmt->execute();
            $stmt->close();
            $mysqli->close();
        }
    }

endif;

?>