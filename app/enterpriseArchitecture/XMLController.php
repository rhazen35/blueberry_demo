<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 12-Jul-16
 * Time: 20:07
 */

namespace app\enterpriseArchitecture;

class XMLController
{

    protected $model;

    public function __construct( $model, $type, $path )
    {
        $this->model = $model;
        $this->type  = $type;
        $this->path  = $path;
    }

    public function request()
    {

        switch( $this->type ):

            case"fileToSimpleXmlObject":
                return( ( new IOXMLEAParser( $this->model ) )->fileToSimpleXmlObject() );
                break;
            case"getNode":
                return( ( new iOXMLEAParser( $this->model ) )->getNode( $this->path ) );
                break;
            case"getNodeAttribute":
                return( ( new iOXMLEAParser( $this->model ) )->getNodeAttribute( $this->path ) );
                break;
            default:
                break;

        endswitch;

    }
}