<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-8-2016
 * Time: 15:10
 */

namespace app\lib;

use app\model\Service;

class Calculator
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
            case"getCalculatorIdByProjectId":
                return( $this->getCalculatorIdByProjectId( $params ) );
                break;
            case "getAllCalculatorsByUser":
                return( $this->getAllCalculatorsByUser( $params ) );
                break;
            case"deleteCalculator":
                return( $this->deleteCalculator( $params ) );
                break;
        endswitch;
    }

    private function getAllCalculatorsByUser()
    {
        $userId     = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
        $sql        = "CALL proc_getAllCalculatorsByUser(?)";
        $data       = array( "user_id" => $userId );
        $format     = array("i");

        $type       = "read";

        $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        return( $returnData );
    }

    private function getCalculatorIdByProjectId( $params )
    {
        $sql         = "CALL proc_getCalculatorIdByProjectId(?)";
        $data        = array("id" => $params['project_id']);
        $format      = array("i");
        $type        = "read";
        $returnData  = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
        $returnArray = array();

        foreach( $returnData as $returnDat ):
            $returnArray['calculator_id'] = $returnDat['calculator_id'];
        endforeach;

        return( $returnArray );
    }

    private function deleteCalculator( $params )
    {
        $calculatorId = !empty( $params['calculator_id'] ) ?$params['calculator_id'] : "";
        $sql          = "CALL proc_deleteCalculator(?)";
        $data         = array( "calculator_id" => $calculatorId );
        $format       = array("i");
        $type         = "delete";

        ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );
    }

}