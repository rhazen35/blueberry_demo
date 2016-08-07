<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 14-7-2016
 * Time: 01:52
 */

namespace app\enterpriseArchitecture;

if( !class_exists( "IOXMLEAModelParser" ) ):

    class IOXMLEAModelParser
    {

        protected $xmlFile;

        /**
         * IOXMLModelParser constructor.
         * @param $xmlFile
         */
        public function __construct( $xmlFile )
        {
            $this->xmlFile = $xmlFile; 
        }

        /**
         * @return array
         */
        public function parseModelExtensionInfo()
        {

            /**
             * Set xml path, xml object and pass it trough the controller to get a specific node
             */
            $xmiPath             = "//xmi:Extension"; // Path for extender information
            $xmlObject           = ( new XMLController( $this->xmlFile, "fileToSimpleXmlObject", "" ) )->request(); // Convert model to simple xml object
            $extensionAttributes = ( new XMLController( $xmlObject, "getNode", $xmiPath ) )->request(); // Get the extension attributes

            $xmiVersionPath      = "//xmi:XMI"; // Path for extender information
            $xmiVersions         = ( new XMLController( $xmlObject, "getNode", $xmiVersionPath ) )->request(); // Get the extension attributes

            $modelExtensionInfo = array();

            foreach ( $xmiVersions as $xmiVersion ):

                $namespaces   = $xmiVersion->getNameSpaces( true );
                $xmiNamespace = $namespaces['xmi'];
                $version      = (string) $xmiVersion->attributes( $xmiNamespace )->version;

                $modelExtensionInfo['model']['xmi_version'] = $version;

            endforeach;

            /**
             * Collect the model's extension information
             */

            foreach ( $extensionAttributes as $extensionAttribute ):

                /** Get the node attribute */
                $extender   = ( new XMLController( $extensionAttribute, "getNodeAttribute", "extender" ) )->request();
                $extenderID = ( new XMLController( $extensionAttribute, "getNodeAttribute", "extenderID" ) )->request();

                /** Add the extender and extender id to the model array */
                $modelExtensionInfo['model']['extender_info']['extender']   = $extender;
                $modelExtensionInfo['model']['extender_info']['extenderID'] = $extenderID;

            endforeach;

            /** Return the model extension info array */
            return( $modelExtensionInfo );

        }

        /**
         * @return array
         */
        public function parseXMLClasses()
        {

            /**
             * Prepare the model
             */
            $model      = $this->xmlFile;
            $xmlObject  = ( new XMLController( $model, "fileToSimpleXmlObject", "" ) )->request(); // Convert model to simple xml object

            /**
             * Define the elements path and get the elements
             */
            $elementPath    = "//xmi:Extension/elements/element"; // Path for elements in the extension
            $elements       = ( new XMLController( $xmlObject, "getNode", $elementPath ) )->request(); // Get the elements

            /**
             * Collect the required model information
             *
             * - Required data is specified in the model interface supplied by the modeller.
             * - Required data has standards which are expressed in the QuaRatio XML Standards(QXS) for Sparx Enterprice UML Modelling.
             */
            if( !empty( $elements ) ):

                $uniqueElements               = array();
                $classArray['duplicateNames'] = 0;

                foreach ( $elements as $element ):

                    /** @var  $namespaces */
                    $namespaces   = $element->getNameSpaces( true );
                    $xmiNamespace = $namespaces['xmi'];

                    /** Class name */
                    $className = ( new XMLController( $element, "getNodeAttribute", "name" ) )->request();


                    if( !in_array( $className, $uniqueElements ) ):

                        $uniqueElements[] = ( in_array( $className, $uniqueElements ) ? "" : $className );
                        $classArray[$className]['name'] = $className;

                        $project   = $element->children()->project;
                        $modified  = ( new XMLController( $project, "getNodeAttribute", "modified" ) )->request();
                        $classArray[$className]['modified'] = $modified;

                        /**
                         * Get the element properties to access the isRoot value
                         */
                        $properties = $element->children()->properties;

                        foreach ( $properties as $property ):

                            /**
                             * Get the isRoot, this class will be shown first.
                             * The root also defines the base which the template generator will request
                             */
                            $isRoot = ( new XMLController( $property, "getNodeAttribute", "isRoot" ) )->request();

                            /**
                             * Detailed class information
                             */

                            if ($isRoot === "true"):
                                $classArray[$className]['Root'] = "true";
                            else:
                                $classArray[$className]['Root'] = "false";
                            endif;

                            /**
                             * Get the package attribute value and add to the model node array
                             */
                            $modelNode = $element->children()->model;
                            $classArray[$className]['package']  = ( new XMLController( $modelNode, "getNodeAttribute", "package" ) )->request();
                            $classArray[$className]['package2'] = ( new XMLController( $modelNode, "getNodeAttribute", "package2" ) )->request();

                            /** @var  $abstract */
                            $abstract = ( new XmlController( $property, "getNodeAttribute", "isAbstract" ) )->request();

                            /**
                             * Get the namespace attributes from the class
                             *
                             * @var  $type
                             * @var  $idref
                             */
                            $type         = (string) $element->attributes( $xmiNamespace )->type;
                            $idref        = (string) $element->attributes( $xmiNamespace )->idref;

                            /** Namespaced type and idref to class array*/
                            $classArray[$className]['type']  = $type;
                            $classArray[$className]['idref'] = $idref;

                            /**
                             * Non-namespaced attributes
                             * Scope, documentation and abstract to class array
                             */
                            $classArray[$className]['scope']         = ( new XMLController( $element, "getNodeAttribute", "scope" ) )->request();
                            $classArray[$className]['documentation'] = ( new XMLController( $property, "getNodeAttribute", "documentation" ) )->request();
                            $classArray[$className]['abstract']      = $abstract;

                            /** Tags for class order */
                            $tags = $element->tags->tag;
                            $totalTags = count($tags);

                            if( !empty( $totalTags ) ):

                                for($l = 0; $l < $totalTags; $l++):

                                    $tagName = (string) $element->tags->tag[$l]->attributes()->name;
                                    $order   = (string) $element->tags->tag[$l]->attributes()->value;

                                    if( !empty( $order ) ):

                                        $classArray[$className]['tags'][$tagName]['order']      = trim( $order );
                                        $classArray[$className]['tags'][$tagName]['className']   = trim( $className );


                                    endif;

                                endfor;

                            endif;

                        endforeach;

                        /**
                         * Get the operations of the class.
                         *
                         * Operations are calculator calculations.
                         * The input data will be added to match the collected excel cells
                         *
                         * @var  $operations
                         * @var  $totalOperations
                         */
                        $operations      = $element->children()->operations;
                        $totalOperations = count( $operations->operation );

                        /**
                         * Create an array with the operation name and add it to the operations array
                         */

                        if( !empty( $operations ) ):

                            for ($i = 0; $i < $totalOperations; $i++):

                                if( $operations->operation[$i]->attributes() ):

                                    /** @var  $operationName */
                                    $operationName  = (string) $operations->operation[$i]->attributes()->name;

                                    /** Define the operations array and add the operation name */
                                    $classArray[$className]['operations']['operation'.($i+1)] = array();
                                    $classArray[$className]['operations']['operation'.($i+1)]['name'] = $operationName;

                                    /**
                                     * idref, position, type, abstract, documentation
                                     */
                                    $idref          = (string) $operations->operation[$i]->attributes( $xmiNamespace )->idref;
                                    $position = $type = $abstract = $documentation = "";

                                    if( $operations->operation[$i]->properties ):
                                        $position   = (string) $operations->operation[$i]->properties->attributes()->position;
                                    endif;

                                    if( $operations->operation[$i]->type ):
                                        $type       = (string) $operations->operation[$i]->type->attributes()->type;
                                        $abstract   = (string) $operations->operation[$i]->type->attributes()->isAbstract;
                                    endif;

                                    if( $operations->operation[$i]->documentation ):
                                        $documentation  = (string) $operations->operation[$i]->documentation->attributes()->value;
                                    endif;

                                    /**
                                     * Add idref, position, type, abstract and documentation to the operations array.
                                     */
                                    $classArray[$className]['operations']['operation'.($i+1)]['className']      = $className;
                                    $classArray[$className]['operations']['operation'.($i+1)]['idref']          = $idref;
                                    $classArray[$className]['operations']['operation'.($i+1)]['position']       = $position;
                                    $classArray[$className]['operations']['operation'.($i+1)]['type']           = $type;
                                    $classArray[$className]['operations']['operation'.($i+1)]['abstract']       = $abstract;
                                    $classArray[$className]['operations']['operation'.($i+1)]['documentation']  = $documentation;

                                    /**
                                     * Operation tags for file, tab and cell
                                     */
                                    $tags = $operations->operation[$i]->tags->children();
                                    $totalTags = count($tags);

                                    if( !empty( $tags ) && $tags !== 0 ):

                                        for($l = 0; $l < $totalTags; $l++):

                                            if( !empty( $tags ) ):
                                                $tagName  = (string) $tags->tag[$l]->attributes()->name;
                                                $tagValue = (string) $tags->tag[$l]->attributes()->value;
                                            endif;

                                            if( !empty( $tagValue ) ):
                                                list($cell, $tab, $file) = array_pad(explode(",", $tagValue, 3),3, null);

                                                $classArray[$className]['operations']['operation'.($i+1)]['tags'][$l]['operation'] = trim($operationName);
                                                $classArray[$className]['operations']['operation'.($i+1)]['tags'][$l]['name'] = trim($tagName);
                                                $classArray[$className]['operations']['operation'.($i+1)]['tags'][$l]['file'] = trim($file);
                                                $classArray[$className]['operations']['operation'.($i+1)]['tags'][$l]['tab']  = trim($tab);
                                                $classArray[$className]['operations']['operation'.($i+1)]['tags'][$l]['cell'] = trim($cell);

                                            endif;

                                        endfor;

                                    endif;

                                endif;

                            endfor;

                        endif;

                        /**
                         * Input fields and enumeration/data type fields
                         *
                         * - Data types of fields can be found under their own class in the xml model
                         */
                        $attributes      = $element->children()->attributes;
                        $totalAttributes = count( $attributes->attribute );

                        /** Loop trough the attributes of the class */
                        for ($i = 0; $i < $totalAttributes; $i++):

                            /** Input field names /enumeration/data type names */
                            $inputName = (string) $attributes->attribute[$i]->attributes()->name;
                            $classArray[$className]['attributes'][$inputName] = array();

                            /** Input field input name */
                            $classArray[$className]['attributes'][$inputName]['input_name'] = $inputName;

                            /** Input class input name */
                            $classArray[$className]['attributes'][$inputName]['class_name'] = $className;

                            /** Input field documentation */
                            $inputFieldDocumentation = (string) $attributes->attribute[$i]->documentation->attributes()->value;
                            $classArray[$className]['attributes'][$inputName]['documentation'] = $inputFieldDocumentation;

                            /** Input field data type */
                            $inputFieldDataType = (string) $attributes->attribute[$i]->properties->attributes()->type;
                            $classArray[$className]['attributes'][$inputName]['data_type'] = $inputFieldDataType;

                            /** Input field initial value */
                            $inputInitialValue = (string) $attributes->attribute[$i]->initial->attributes()->body;
                            $classArray[$className]['attributes'][$inputName]['initialValue'] = $inputInitialValue;

                            /**
                             * Tags for excel address
                             */
                            $tags = $attributes->attribute[$i]->tags->children();
                            $totalTags = count( $tags );

                            if( !empty( $tags ) && $tags !== 0 ):

                                for( $j = 0; $j < $totalTags; $j++ ):

                                    if( !empty( $tags ) ):
                                        $tagName  = (string) $tags->tag[$j]->attributes()->name;
                                        $tagValue = (string) $tags->tag[$j]->attributes()->value;
                                    endif;

                                    if( !empty( $tagValue ) ):
                                        list($cell, $tab, $file) = array_pad(explode(",", $tagValue, 3),3, null);

                                        $classArray[$className]['attributes'][$inputName]['tags'][$j]['attribute'] = $inputName;
                                        $classArray[$className]['attributes'][$inputName]['tags'][$j]['name'] = $tagName;
                                        $classArray[$className]['attributes'][$inputName]['tags'][$j]['file'] = $file;
                                        $classArray[$className]['attributes'][$inputName]['tags'][$j]['tab'] = $tab;
                                        $classArray[$className]['attributes'][$inputName]['tags'][$j]['cell'] = $cell;

                                    endif;

                                endfor;

                            endif;

                        endfor;

                        $links      = $element->children()->links;
                        $totalLinks = count( $links->Aggregation );

                        $classArray[$className]['links'] = array();

                        for($i = 0; $i < $totalLinks; $i++):

                            $id     = (string) $links->Aggregation[$i]->attributes($xmiNamespace)->id;
                            $end    = (string) $links->Aggregation[$i]->attributes()->end;
                            $start  = (string) $links->Aggregation[$i]->attributes()->start;

                            $classArray[$className]['links']['link'.($i+1)]['id']      = $id;
                            $classArray[$className]['links']['link'.($i+1)]['end']     = $end;
                            $classArray[$className]['links']['link'.($i+1)]['start']   = $start;

                        endfor;

                    else:

                        $classArray['duplicateNames'] += 1;

                    endif;

                endforeach;

            endif;

            /**
             * Return the class array
             */

            $classArray = ( !empty( $classArray ) ? $classArray : array() );

            return( $classArray );

        }

        /**
         * @return mixed
         */
        public function parseConnectors()
        {

            $model      = $this->xmlFile;
            $xmlObject  = ( new XMLController( $model, "fileToSimpleXmlObject", "" ) )->request(); // Convert model to simple xml object

            /**
             * Define the connectors path and get the connectors
             */
            $connectorPath    = "//xmi:Extension/connectors/connector"; // Path for connectors in the extension
            $connectors       = ( new XMLController( $xmlObject, "getNode", $connectorPath ) )->request(); // Get the elements

            $connectorArray['connectors'] = array();

            $i = 0;
            foreach($connectors as $connector):

                /** @var  $namespaces */
                $namespaces   = $connector->getNameSpaces( true );
                $xmiNamespace = $namespaces['xmi'];

                /** Add the connector idref to the connectors array */
                $idref = (string) $connector->attributes( $xmiNamespace )->idref;
                $connectorArray['connectors']['connector'.($i+1)]['idref'] = $idref;

                /**
                 * Get the labels of the connector
                 */
                $labels             = $connector->children()->labels;
                $multiplicityStart  = ( !empty( $labels->attributes()->rb ) ? (string) $labels->attributes()->rb : "" );
                $multiplicityEnd    = ( !empty( $labels->attributes()->lb ) ? (string) $labels->attributes()->lb : "" );
                $multiplicityHead   = ( !empty( $labels->attributes()->mt ) ? (string) $labels->attributes()->mt : "" );

                $connectorArray['connectors']['connector'.($i+1)]['labels']['multiplicity_start'] = $multiplicityStart;
                $connectorArray['connectors']['connector'.($i+1)]['labels']['multiplicity_end']   = $multiplicityEnd;
                $connectorArray['connectors']['connector'.($i+1)]['labels']['multiplicity_head']  = $multiplicityHead;

                /**
                 * Get the connector source and add it as an array to the connectors array
                 */
                $source = $connector->children()->source;
                $connectorArray['connectors']['connector'.($i+1)]['source'] = array();

                /** Add the source idref to the source array */
                $idref = (string) $source->attributes( $xmiNamespace )->idref;
                $connectorArray['connectors']['connector'.($i+1)]['source']['idref'] = $idref;

                $modelName      = (string) $source->children()->model->attributes()->name;
                $modelType      = (string) $source->children()->model->attributes()->type;
                $modelEALocalId = (string) $source->children()->model->attributes()->ea_localid;
                $multiplicity   = (string) $source->children()->type->attributes()->multiplicity;
                $aggregation    = (string) $source->children()->type->attributes()->aggregation;

                $connectorArray['connectors']['connector'.($i+1)]['source']['model']['name']         = $modelName;
                $connectorArray['connectors']['connector'.($i+1)]['source']['model']['type']         = $modelType;
                $connectorArray['connectors']['connector'.($i+1)]['source']['model']['ea_localid']   = $modelEALocalId;
                $connectorArray['connectors']['connector'.($i+1)]['source']['model']['multiplicity'] = $multiplicity;
                $connectorArray['connectors']['connector'.($i+1)]['source']['model']['aggregation']  = $aggregation;

                /**
                 * Get the connector target and add it as an array to the connectors array
                 */
                $target = $connector->children()->target;
                $connectorArray['connectors']['connector'.($i+1)]['target'] = array();

                /** Add the target idref to the target array */
                $idref = (string) $target->attributes( $xmiNamespace )->idref;
                $connectorArray['connectors']['connector'.($i+1)]['target']['idref'] = $idref;

                $modelName      = (string) $target->children()->model->attributes()->name;
                $modelType      = (string) $target->children()->model->attributes()->type;
                $modelEALocalId = (string) $target->children()->model->attributes()->ea_localid;
                $multiplicity   = (string) $target->children()->type->attributes()->multiplicity;

                $connectorArray['connectors']['connector'.($i+1)]['target']['model']['name']         = $modelName;
                $connectorArray['connectors']['connector'.($i+1)]['target']['model']['type']         = $modelType;
                $connectorArray['connectors']['connector'.($i+1)]['target']['model']['ea_localid']   = $modelEALocalId;
                $connectorArray['connectors']['connector'.($i+1)]['target']['model']['multiplicity'] = $multiplicity;

                /**
                 * Get the connector target and add it as an array to the connectors array
                 */
                $properties = $connector->children()->properties;
                $connectorArray['connectors']['connector'.($i+1)]['properties'] = array();

                $ea_type     = (string) $properties->attributes()->ea_type;
                $direction   = (string) $properties->attributes()->direction;

                $connectorArray['connectors']['connector'.($i+1)]['properties']['ea_type'] = $ea_type;
                $connectorArray['connectors']['connector'.($i+1)]['properties']['direction'] = $direction;

                $i++;

            endforeach;

            return( $connectorArray );

        }
    }

endif;

?>