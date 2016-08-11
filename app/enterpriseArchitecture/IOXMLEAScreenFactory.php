<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 11-Aug-16
 * Time: 12:04
 */

namespace app\enterpriseArchitecture;


class IOXMLEAScreenFactory
{
    protected $xmlModelId;

    /**
     * IOXMLEAScreenFactory constructor.
     * @param $xmlModelId
     * @param $type
     */
    public function __construct( $type, $xmlModelId )
    {
        $this->type       = $type;
        $this->xmlModelId = $xmlModelId;
    }

    public function request( $params )
    {
        switch( $this->type ):
            case"extractElementNames":
                return( $this->extractElementNames( $params ) );
                break;
            case"extractAndOrderElements":
                return( $this->extractAndOrderElements() );
                break;
        endswitch;
    }

    private function extractElementNames( $params )
    {
        $parsedElements = $params['elements'];
        $elementNames = array();

        foreach( $parsedElements as $parsedElement ):
            /**
             * Get all the classes names
             */
            if( !empty( $parsedElement['name'] ) ):
                $elementNames[] = $parsedElement['name'];
            endif;
        endforeach;

        return($elementNames);
    }

    private function sortElements( $a, $b )
    {
        if( !empty( $a['order'] ) && !empty( $b['order'] ) ):
            return( strnatcmp( $a['order'], $b['order'] ) );
        endif;
    }

    public function extractAndOrderElements()
    {
        $modelData          = (new IOXMLEAModel( $this->xmlModelId ) )->getModel();
        $relations          = ( new IOXMLEAInheritance( "buildRelations", $this->xmlModelId ) )->request();
        $totalRelations     = ( count( $relations ) );
        $orderedElements    = array();
        $highestOrder       = 0;

        if (!empty($modelData)):

            if (!empty($modelData['hash'])):

                $xmlFile        = 'web/files/xml_models_tmp/' . $modelData['hash'] . '.' . $modelData['ext'];
                $parsedElements = (new IOXMLEAModelParser($xmlFile))->parseXMLClasses();
                $params         = array( "elements" => $parsedElements );
                $elementNames   = $this->extractElementNames ( $params );

                $i = 0;
                foreach( $elementNames as $elementName ):

                    $element        = ( isset( $parsedElements[$elementName] ) && $parsedElements[$elementName]['type'] === "uml:Class" ? $parsedElements[$elementName] : "" );
                    $idref          = ( isset( $element['idref'] ) ? $element['idref'] : "" );
                    $root           = ( isset( $element['root'] ) ? $element['root'] : false );
                    $abstract       = ( isset( $element['abstract'] ) ? $element['abstract'] : false );
                    $name           = ( isset( $element['name'] ) ? $element['name'] : "" );
                    $tags           = ( isset( $element['tags'] ) ? $element['tags'] : false );
                    $order          = ( isset( $tags['QR-PrintOrder']['order'] ) ? $tags['QR-PrintOrder']['order'] : "");
                    $documentation  = ( isset( $element['documentation'] ) ? $element['documentation'] : "" );
                    $attributes     = ( isset( $element['attributes'] ) ? $element['attributes'] : false );
                    $operations     = ( isset( $element['operations'] ) ? $element['operations'] : "" );

                    if( !empty( $order ) ):

                        if( $name === $relations[$elementName]['name'] ):
                            $orderedElements[$i]['name']    = $name;
                            $orderedElements[$i]['order']   = $order;
                            $orderedElements[$i]['root']    = $root;
                            if( !empty( $relations['isSuperType'] ) ):
                                $orderElements[$i]['isSuperType'] = true;
                            endif;
                            $orderedElements[$i]['documentation']    = $documentation;
                        endif;

                    $highestOrder = $order;

                    endif;

                    $i++;
                endforeach;

                usort( $orderedElements, array( $this,'sortElements' ) );

                $orderedElements['highestOrder'] = $highestOrder;

                return( $orderedElements );

            endif;

        endif;
    }
}