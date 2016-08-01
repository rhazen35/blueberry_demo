<?php

namespace app\model\data;

use app\database\Database;

/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 12-6-2016
 * Time: 20:51
 */

if(!class_exists( "Update" )):

    class Update
    {
        protected $sql;

        /**
         * DbUpdate constructor.
         * @param $sql
         * @param $database
         */

        public function __construct( $sql, $database )
        {
            $this->sql = $sql;
            $this->database = $database;
        }

        /**
         * @param $data
         * @param $format
         */

        public function  dbUpdate( $data, $format )

        {
            $mysqli     = (new Database( $this->database ))->dbConnect();
            $stmt       = $mysqli->prepare( $this->sql );

            if(!empty( $format ) && !empty( $data )):

                $format = implode( '', $format );
                $format = str_replace( '%', '', $format );

                array_unshift( $data, $format );
                call_user_func_array( array( $stmt, 'bind_param' ), (new Database( $this->database ))->referenceValues( $data ) );

            endif;

            $stmt->execute();
            $stmt->close();

            $mysqli->close();
        }
    }

endif;