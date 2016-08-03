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

        public function request()
        {
            switch( $this->type ):
                case"newModel":
                    return( $this->newModel() );
                    break;
                case"saveModel":
                    return( $this->saveModel() );
                    break;
                case"matchHash":
                    return( $this->matchHash() );
                    break;
                case"getModelArray":
                    return( $this->getModelArray() );
                    break;
                case"saveModelArray":
                    $this->saveModelArray();
                    break;
            endswitch;
        }

        private function newModel()
        {
            $validateXMLFile    = ( new IOXMLEAValidator( $this->xmlFile ) )->validate();
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

        private function saveModel()
        {
            $datetime    = new \DateTime( $this->uploadedAt );
            $upload_date = $datetime->format('Y-m-d');
            $upload_time = $datetime->format('H:i:s');
            $userId      = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
            $id          = $output = "";

            $sql        = "CALL proc_newModel(?,?,?,?,?,?)";
            $data       = array(
                                "id"            => $id,
                                "user_id"       => $userId,
                                "hash"          => $this->xmlFile,
                                "date"          => $upload_date,
                                "time"          => $upload_time,
                                "output"        => $output
                                );
            $format     = array("iisssi");
            $type       = "createWithOutput";

            $lastInsertedId = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

            return( $lastInsertedId );
        }

        private function getModelArray()
        {
            $extensionInfo  = ( new IOXMLModelParser( $this->xmlFile ) )->parseXMLClasses();
            $elements       = ( new IOXMLModelParser( $this->xmlFile ) )->parseModelExtensionInfo();
            $connectors     = ( new IOXMLModelParser( $this->xmlFile ) )->parseConnectors();

            $modelArray     = array_merge( $extensionInfo, $elements, $connectors );
            return($modelArray);
        }

        private function saveModelArray()
        {
            $date        = date('Y-m-d');
            $time        = date('H:i:s');
            $id          = "";

            $sql         = "CALL proc_saveModelArray(?,?,?,?,?)";
            $data        = array(
                "id"            => $id,
                "model_id"      => $this->uploadedAt,
                "array"         => serialize( $this->xmlFile ),
                "date"          => $date,
                "time"          => $time
            );
            $format      = array("iisss");
            $type        = "create";

            ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
        }

    }

endif;