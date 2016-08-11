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

    /**
     * @param $params
     * @return array|void
     */
    public function request($params )
    {
        switch( $this->type ):
            case"extractElementNames":
                return( $this->extractElementNames( $params ) );
                break;
            case"extractAndOrderElements":
                return( $this->extractAndOrderElements() );
                break;
            case"buildScreenArray":
                return( $this->buildScreenArray() );
                break;
        endswitch;
    }

    /**
     * @param $params
     * @return array
     */
    private function extractElementNames($params )
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

    /**
     * @param $a
     * @param $b
     * @return int
     */
    private function sortElements($a, $b )
    {
        if( !empty( $a['printOrder'] ) && !empty( $b['printOrder'] ) ):
            return( strnatcmp( $a['printOrder'], $b['printOrder'] ) );
        endif;
    }

    /**
     * @return array
     */
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
                    /**
                     * Relation data collection
                     */
                    $elementRelations     = ( !empty( $relations[$elementName] ) ? $relations[$elementName] : "" );
                    $relationName         = ( !empty( $relations[$elementName]['name'] ) ? $relations[$elementName]['name'] : "" );
                    $relationIsParent     = ( !empty( $relations[$elementName]['isParent'] ) ? $relations[$elementName]['isParent'] : "" );
                    $relationIsChild      = ( !empty( $relations[$elementName]['isChild'] ) ? $relations[$elementName]['isChild'] : "" );
                    $relationIsSuperType  = ( !empty( $relations[$elementName]['isSuperType'] ) ? $relations[$elementName]['isSuperType'] : "" );
                    $relationIsSubType    = ( !empty( $relations[$elementName]['isSubType'] ) ? $relations[$elementName]['isSubType'] : "" );
                    $relationMultiplicity = ( !empty( $relations[$elementName]['multiplicity'] ) ? $relations[$elementName]['multiplicity'] : "" );
                    /**
                     * Super and sub types
                     */
                    $superTypes           = ( !empty(  $relations[$elementName]['super_types'] ) ?  $relations[$elementName]['super_types'] : "" );
                    $subTypes             = ( !empty(  $relations[$elementName]['sub_types'] ) ?  $relations[$elementName]['sub_types'] : "" );
                    $totalSuperTypes      = ( count( $superTypes ) );
                    $totalSubTypes        = ( count( $subTypes ) );
                    /**
                     * Element data collection
                     */
                    $element          = ( isset( $parsedElements[$elementName] ) && $parsedElements[$elementName]['type'] === "uml:Class" ? $parsedElements[$elementName] : "" );
                    $idref            = ( isset( $element['idref'] ) ? $element['idref'] : "" );
                    $root             = ( isset( $element['root'] ) ? $element['root'] : false );
                    $abstract         = ( isset( $element['abstract'] ) ? $element['abstract'] : false );
                    $name             = ( isset( $element['name'] ) ? $element['name'] : "" );
                    $tags             = ( isset( $element['tags'] ) ? $element['tags'] : false );
                    $order            = ( isset( $tags['QR-PrintOrder']['order'] ) ? $tags['QR-PrintOrder']['order'] : "");
                    /**
                     * Only create element array if there is an order.
                     */
                    if( !empty( $order ) ):

                        if( $name === $relationName ):
                            $orderedElements[$i]['elementName']          = $name;
                            $orderedElements[$i]['idref']                = $idref;
                            $orderedElements[$i]['printOrder']           = $order;
                            $orderedElements[$i]['isRoot']               = $root;
                            $orderedElements[$i]['isAbstract']           = $abstract;
                            /**
                             * Get element multiplicity, set to 1 if none is provided.
                             */
                            $orderedElements[$i]['elementMultiplicity']  = $relationMultiplicity;
                            /**
                             * Get the parent, child, super type and sub type
                             */
                            $orderedElements[$i]['isParent']             = $relationIsParent;
                            $orderedElements[$i]['isChild']              = $relationIsChild;
                            $orderedElements[$i]['isSuperType']          = $relationIsSuperType;
                            $orderedElements[$i]['isSubType']            = $relationIsSubType;

                            /**
                             * Select the sub types for each super type.
                             *
                             */
                            if( !empty( $superTypes ) ):
                                for( $j = 0; $j < $totalSuperTypes; $j++ ):
                                    /**
                                     * Select sub types for current element
                                     */
                                    if( !empty( $subTypes ) ):
                                        for( $k = 0; $k < $totalSubTypes; $k++ ):
                                            $orderedElements[$i]['super_types'][$superTypes['super_type'.($j+1)]]['sub'.($k+1)] = ( !empty( $subTypes['sub_type'.($k+1)] ) ? $subTypes['sub_type'.($k+1)] : "" );
                                        endfor;
                                    endif;
                                endfor;
                            endif;

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

    private function extractAndOrderSuperTypes( $superTypes )
    {
        $extractedandOrderedSuperTypes = array();
        if ( !empty( $superTypes ) ):
            foreach( $superTypes as $superType => $value ):

            endforeach;
        endif;
    }

    private function buildScreenArray()
    {
        /**
         * Build the screen array which contains all data related to the view.
         */
        $orderedElements = $this->extractAndOrderElements();

        $screenArray = array();
        foreach( $orderedElements as $orderedElement ):
            /**
             * Collect all data
             */
            $superTypes = ( !empty( $orderedElement['super_types'] ) ? $orderedElement['super_types'] : "" );
            if( !empty( $superTypes ) ):
                $extractedAndOrderedSuperTypes = $this->extractAndOrderSuperTypes( $superTypes );
            endif;
        endforeach;

        return( $orderedElements );
    }
}