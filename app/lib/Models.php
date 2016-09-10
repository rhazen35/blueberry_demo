<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 03-Aug-16
 * Time: 10:54
 */

namespace app\lib;

use app\model\Service;

class Models
{

    protected $type;
    protected $database = "blueberry";

    public function __construct( $type )
    {
        $this->type = $type;
    }

    public function request( $params )
    {
        switch( $this->type ):
            case"countModels":
                return( $this->countModels() );
                break;
            case"getAllModelsByUser":
                return( $this->getAllModelsByUser() );
                break;
            case"getProjectNameByModelId":
                return( $this->getProjectNameByModelId( $params ) );
                break;
            case"deleteModel":
                ( $this->deleteModel( $params ) );
                break;
        endswitch;
    }

    private function countModels()
    {
        $sql         = "CALL proc_countModels()";
        $data        = array();
        $format      = array();
        $type        = "read";
        $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        $count = 0;
        foreach( $returnData[0] as $key => $value ):
            $count = $value;
        endforeach;

        return( $count );
    }

    private function getAllModelsByUser()
    {
        $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
        $sql        = "CALL proc_getAllModelsByUser(?)";
        $data       = array( "user_id" => $userId );
        $format     = array("i");
        $type       = "read";

        $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        return( $returnData );
    }

    private function getProjectNameByModelId( $params )
    {
        $modelId    = !empty( $params['model_id'] ) ?$params['model_id'] : "";
        $sql        = "CALL proc_getProjectNameByModelId(?)";
        $data       = array( "model_id" => $modelId );
        $format     = array("i");
        $type       = "read";

        $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        return( $returnData );
    }

    private function deleteModel( $params )
    {
        $modelId    = !empty( $params['model_id'] ) ?$params['model_id'] : "";
        $sql        = "CALL proc_deleteModel(?)";
        $data       = array( "model_id" => $modelId );
        $format     = array("i");
        $type       = "delete";

        ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
    }

}