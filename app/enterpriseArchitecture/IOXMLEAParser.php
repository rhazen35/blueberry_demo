<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 5-7-2016
 * Time: 23:45
 *
 * IOXMLParser reads a XML file and returns
 *
 */

namespace app\enterpriseArchitecture;

if( !empty( "IOXMLEAParser" ) ):

    class IOXMLEAParser
    {

        protected $xml;

        /**
         * IOXMLParser constructor.
         * @param $xmlFile
         */

        public function __construct( $xmlFile )
        {
            $this->xmlFile = $xmlFile;
        }


        /**
         * @return string
         */
        public function isXML()
        {
            /**
             * Check if the file is a XML file
             */
            $xml = @simplexml_load_file( $this->xmlFile );

            if ( $xml === false ):
                return( false );
            else:
                return( true );
            endif;

        }

        public function fileToSimpleXmlObject()
        {

            /**
             * Check if the file is a XML file
             */
            return( simplexml_load_file( $this->xmlFile ) );

        }

        public function getNode( $path )
        {

            // Get the data with the specified path
            $data = $this->xmlFile->xpath( $path );

            return( $data );

        }

        public function getNodeAttribute( $attribute )
        {
            if( isset( $this->xmlFile[$attribute] ) ):
                return (string) $this->xmlFile[$attribute];
            endif;
        }

    }

endif;