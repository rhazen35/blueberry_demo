<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 25-Jul-16
 * Time: 14:25
 */

namespace app\enterpriseArchitecture;

use app\model\Service;

if( !class_exists( "IOXMLEAModel" ) ):

    class IOXMLEAModel
    {
        protected $modelId;
        protected $database = "blueberry";

        public function __construct( $modelId )
        {
            $this->modelId = $modelId;
        }

        public function getModel()
        {

            $sql        = "CALL proc_getModel(?)";
            $data       = array("id" => $this->modelId);
            $format     = array('i');

            $type       = "read";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            $returnArray = "";
            if( !empty( $returnData ) ):

                foreach( $returnData as $data ):

                    $returnArray = array( 'user_id' => $data['user_id'],
                                          'hash' => $data['hash'],
                                          'date' => $data['date'],
                                          'time' => $data['time']
                                        );

                endforeach;

                return( $returnArray );

            else:

                return( false );

            endif;

        }

        public function getModelIdByHash()
        {

            $sql        = "CALL proc_getModelIdByHash(?)";
            $data       = array("hash" => $this->modelId);
            $format     = array('s');

            $type       = "read";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            $returnArray = "";
            if( !empty( $returnData ) ):

                foreach( $returnData as $data ):

                    $returnArray = array( 'model_id' => $data['id']);

                endforeach;

                return( $returnArray );

            else:

                return( false );

            endif;

        }

        public function getModelArray()
        {
            $sql        = "CALL proc_getModelArray(?)";
            $data       = array("model_id" => $this->modelId);
            $format     = array('i');

            $type       = "read";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return($returnData);
        }


    }

endif;