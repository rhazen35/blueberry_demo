<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 16-Aug-16
 * Time: 22:59
 */

namespace app\api\ea;

use app\model\Service;

class EAApi
{
    protected $type;
    protected $dbName = "blueberry";

    public function __construct( $type )
    {
        $this->type = $type;
    }

    /**
     * @param $params
     * @return bool|\mysqli_result
     */
    public function request( $params )
    {
        switch( $this->type ):
            case"get_all_models":
                return( $this->get_all_models( $params ) );
                break;
        endswitch;
    }

    private function get_all_models( $params )
    {
        $dbName      = ( !empty( $params['model'] ) ? $params['model'] : $this->dbName );
        $sql         = "CALL proc_ea_api_get_all_models()";
        $data        = array();
        $format      = array();
        $type        = "read";
        $returnData  = ( new Service( $type, $dbName ) )->dbAction( $sql, $data, $format );
        return( $returnData );
    }

    private function get_all_models_detailed()
    {
        $models = $this->get_all_models( $params = null );
        foreach( $models as $model ):

        endforeach;
    }
}