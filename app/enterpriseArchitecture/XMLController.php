<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 12-Jul-16
 * Time: 20:07
 */

namespace app\enterpriseArchitecture;

use app\enterpriseArchitecture\IOXMLParser;


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
                return( ( new IOXMLParser( $this->model ) )->fileToSimpleXmlObject() );
                break;
            case"getNode":
                return( ( new iOXMLParser( $this->model ) )->getNode( $this->path ) );
                break;
            case"getNodeAttribute":
                return( ( new iOXMLParser( $this->model ) )->getNodeAttribute( $this->path ) );
                break;
            default:
                break;

        endswitch;

    }
}