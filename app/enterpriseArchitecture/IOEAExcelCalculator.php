<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-8-2016
 * Time: 16:30
 */

namespace app\enterpriseArchitecture;

use app\model\Service;

class IOEAExcelCalculator
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
            case"getCalculator":
                return( $this->getCalculator( $params ) );
                break;
        endswitch;
    }

    private function getCalculator( $params )
    {
        $calculatorId = $this->getCalculatorIdByProjectId( $params );
        $sql          = "CALL proc_getCalculator(?)";
        $data         = array("id" => $calculatorId['calculator_id']);
        $format       = array('i');
        $type         = "read";
        $returnData   = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        if( !empty( $returnData ) ):
            return( $returnData[0] );
        else:
            return( false );
        endif;
    }

    private function getCalculatorIdByProjectId( $params )
    {
        $sql        = "CALL proc_getCalculatorIdByProjectId(?)";
        $data       = array("project_id" => $params['project_id']);
        $format     = array('i');
        $type       = "read";

        $returnData    = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        if( !empty( $returnData ) ):
            $calculatorId = $returnData[0];
            return( $calculatorId );
        else:
            return( false );
        endif;
    }


}