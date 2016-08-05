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
    protected $calculatorId;
    protected $database = "blueberry";

    public function __construct( $calculatorId )
    {
        $this->calculatorId = $calculatorId;
    }

    public function getCalculator()
    {
        $sql        = "CALL proc_getCalculator(?)";
        $data       = array("id" => $this->calculatorId);
        $format     = array('i');
        $type       = "read";

        $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        $returnArray = "";
        if( !empty( $returnData ) ):
            foreach( $returnData as $data ):
                $returnArray = array(
                    'user_id' => $data['user_id'],
                    'hash'    => $data['hash'],
                    'ext'     => $data['ext'],
                    'date'    => $data['date'],
                    'time'    => $data['time']
                );
            endforeach;
            return( $returnArray );
        else:
            return( false );
        endif;
    }

    public function getCalculatorIdByHash()
    {
        $sql        = "CALL proc_getModelIdByHash(?)";
        $data       = array("hash" => $this->calculatorId);
        $format     = array('s');
        $type       = "read";

        $returnData = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        $returnArray = "";
        if( !empty( $returnData ) ):
            foreach( $returnData as $data ):
                $returnArray = array( 'calculator_id' => $data['id']);
            endforeach;
            return( $returnArray );
        else:
            return( false );
        endif;

    }
}