<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 17-Jul-16
 * Time: 19:07
 */

namespace app\enterpriseArchitecture;

use app\model\Service;
use app\enterpriseArchitecture\IOXMLModelParser;
use app\enterpriseArchitecture\IOXMLEAValidator;

if( !class_exists( "IOXMLModelUpload" ) ):

    class IOXMLModelUpload
    {

        protected $type;
        protected $xmlFile;
        protected $uploadedAt;
        protected $database = "blueberry";

        public function __construct( $type, $xmlFile, $uploadedAt )
        {
            $this->type         = $type;
            $this->xmlFile      = $xmlFile;
            $this->uploadedAt   = $uploadedAt;
        }

        public function request( $params )
        {
            switch( $this->type ):
                case"validateModel":
                    return( $this->validateModel() );
                    break;
                case"saveModel":
                    return( $this->saveModel( $params ) );
                    break;
                case"matchHash":
                    return( $this->matchHash() );
                    break;
                case"getModelArray":
                    return( $this->getModelArray() );
                    break;
            endswitch;
        }

        private function validateModel()
        {
            $validateXMLFile = ( new IOXMLEAValidator( $this->xmlFile ) )->validate();
            return( $validateXMLFile );
        }

        private function matchHash()
        {
            $sql        = "CALL proc_getMatchingModelHash(?)";
            $data       = array("hash" => $this->xmlFile);
            $format     = array('s');
            $type       = "read";

            $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            if( !empty( $returnData ) ):
                foreach( $returnData as $returnDat ):
                    return( $returnDat );
                endforeach;
            else:
                return( false );
            endif;
        }

        private function saveModel( $params )
        {
            $datetime    = new \DateTime( $this->uploadedAt );
            $upload_date = $datetime->format('Y-m-d');
            $upload_time = $datetime->format('H:i:s');
            $userId      = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $id          = $output = "";

            $sql        = "CALL proc_newModel(?,?,?,?,?,?,?,?,?)";
            $data       = array(
                                "id"            => $id,
                                "user_id"       => $userId,
                                "name"          => $params['name'],
                                "hash"          => $this->xmlFile,
                                "ext"           => $params['extension'],
                                "valid"         => $params['valid'],
                                "date"          => $upload_date,
                                "time"          => $upload_time,
                                "output"        => $output
                                );
            $format     = array("iissssssi");
            $type       = "createWithOutput";

            $lastInsertedId = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $lastInsertedId );
        }

        private function getModelArray()
        {
            $extensionInfo  = ( new IOXMLModelParser( $this->xmlFile ) )->parseModelExtensionInfo();
            $elements       = ( new IOXMLModelParser( $this->xmlFile ) )->parseXMLClasses();
            $connectors     = ( new IOXMLModelParser( $this->xmlFile ) )->parseConnectors();

            $modelArray     = array_merge( $extensionInfo, $elements, $connectors );
            return($modelArray);
        }

    }

endif;