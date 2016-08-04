<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 4-8-2016
 * Time: 16:19
 */

namespace app\enterpriseArchitecture;

use app\model\Service;

class IOExcelCalculatorUpload
{
    protected $type;
    protected $calculatorFile;
    protected $uploadedAt;
    protected $database = "blueberry";

    public function __construct( $type, $calculatorFile, $uploadedAt )
    {
        $this->type           = $type;
        $this->calculatorFile = $calculatorFile;
        $this->uploadedAt     = $uploadedAt;
    }

    public function request()
    {
        switch( $this->type ):
            case"saveCalculator":
                return( $this->saveCalculator() );
                break;
            case"matchHash":
                return( $this->matchHash() );
                break;
        endswitch;
    }

    private function matchHash()
    {
        $sql        = "CALL proc_getMatchingCalculatorHash(?)";
        $data       = array("hash" => $this->calculatorFile);
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

    private function saveCalculator()
    {
        $datetime    = new \DateTime( $this->uploadedAt );
        $upload_date = $datetime->format('Y-m-d');
        $upload_time = $datetime->format('H:i:s');
        $userId      = !empty( $_SESSION['userId'] ) ? $_SESSION['userId'] : "";
        $id          = $output = "";

        $sql        = "CALL proc_newCalculator(?,?,?,?,?,?)";
        $data       = array(
            "id"            => $id,
            "user_id"       => $userId,
            "hash"          => $this->calculatorFile,
            "date"          => $upload_date,
            "time"          => $upload_time,
            "output"        => $output
        );
        $format     = array("iisssi");
        $type       = "createWithOutput";

        $lastInsertedId = ( new Service( $type, $this->database ) )->dbAction( $sql, $data, $format );

        return( $lastInsertedId );
    }

}