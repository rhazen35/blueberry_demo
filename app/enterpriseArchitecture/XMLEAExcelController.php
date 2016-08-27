<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 27-Aug-16
 * Time: 18:32
 */

namespace app\enterpriseArchitecture;

use app\api\ea\EAApi;
use app\enterpriseArchitecture\IOExcelFactory;


class XMLEAExcelController
{
    protected $type;

    public function __construct( $type )
    {
        $this->type = $type;
    }

    public function request( $params )
    {
        switch( $this->type ):
            case"getResults":
                return( $this->getResults( $params ) );
                break;
        endswitch;
    }

    private function getResults( $params )
    {
        $projectName         = ( isset( $params['project'] ) ? $params['project'] : "" );
        $params['project']   = $projectName;
        $excel               = ( new IOExcelFactory( "dataToFile" ) )->request( $params );

        return($excel);
    }
}