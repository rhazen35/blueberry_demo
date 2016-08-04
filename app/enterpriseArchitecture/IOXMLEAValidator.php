<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 17-Jul-16
 * Time: 19:39
 */

namespace app\enterpriseArchitecture;

use app\enterpriseArchitecture\IOXMLParser;
use app\enterpriseArchitecture\IOXMLModelParser;
use app\enterpriseArchitecture\IOXMLPrimitiveTypes;


if( !class_exists( "IOXMLEAValidator" ) ):

    class IOXMLEAValidator
    {

        protected $xmlFile;
        protected $matchExcelFormat = '/^[A-Za-z]+[0-9]+(\:)?(?(1)[A-Za-z]+[0-9]+|[0-9]?)$/';

        /**
         * IOXMLEAValidator constructor.
         * @param $xmlFile
         */
        public function __construct($xmlFile )
        {
            $this->xmlFile = $xmlFile;
        }

        /**
         * @return array
         */
        public function validate()
        {

            $report = array();

            $severe     = 0;
            $error      = 0;
            $warning    = 0;
            $valid      = 0;
            $info       = 0;
            $type       = "";

            /**
             * Check if the file is a xml
             */
            $isXML = ( new IOXMLParser( $this->xmlFile ) )->isXML();

            if( $isXML === true ):
                $type = "valid";
                $valid += 1;
            elseif( $isXML === false ):
                $type  = "severe";
                $severe += 1;
            endif;

            $value    = ( $isXML === true ? 1 : 0 );
            $s_valid  = ( $isXML === true ? true : false );
            $message  = ( $isXML === true ? "File is an XML" : "File is not an XML" );
            $s_info   = "The uploaded file is not an xml file. Allowed extensions: .xml";

            $xmlArray = $this->generateArray( "isXML", "XML file", $type, $value, $s_valid, $message, $s_info );
            $report = array_merge($report, $xmlArray);

            /**
             * Only continue validation when the file is an XML
             */
            if( $isXML === true ):

                $parsedExtensionInfo = ( new IOXMLModelParser( $this->xmlFile ) )->parseModelExtensionInfo();
                $extension           = $parsedExtensionInfo['model']['extender_info']['extender'];
                $extensionVersion    = $parsedExtensionInfo['model']['extender_info']['extenderID'];
                $xmi_version         = $parsedExtensionInfo['model']['xmi_version'];

                /**
                 * Check for the xmi version and add it to the report array
                 */
                if( !empty( $xmi_version ) && $xmi_version !== "2.1" ):
                    $type  = "info";
                    $info += 1;
                elseif( empty( $xmi_version ) ):
                    $type  = "error";
                    $error += 1;
                else:
                    $type  = "valid";
                    $valid += 1;
                endif;

                $value     = ( !empty( $xmi_version ) ? $xmi_version : "empty" );
                $s_valid   = ( !empty( $xmi_version ) && $xmi_version === "2.1" ? true : false );
                $message   = ( !empty( $xmi_version ) ? ( $xmi_version === "2.1" ? "Version found" : "Version found but other then 2.1" ) : "No version found" );
                $s_info    = "Couldn't find the XMI version which is crucial for reproducibility.";

                $xmlVersionArray = $this->generateArray( "xmiVersion", "XMI version", $type, $value, $s_valid, $message, $s_info );
                $report = array_merge($report, $xmlVersionArray);

                /**
                 * Check for the extension and add it to the report array
                 */
                if( !empty( $extension ) && $extension !== "Enterprise Architect" ):
                    $type  = "info";
                    $info += 1;
                elseif( empty( $extension ) ):
                    $type  = "error";
                    $error += 1;
                else:
                    $type  = "valid";
                    $valid += 1;
                endif;

                $value     = $extension;
                $s_valid   = ( !empty( $extension ) && $extension === "Enterprise Architect" ? true : false );
                $message   = ( !empty( $extension ) ? ( $extension === "Enterprise Architect" ? "Extension found" : "Extension found but other then Enterprise Architect" ) : "No extension found" );
                $s_info    = "Couldn't find the EA extension which is crucial for reproducibility.";

                $xmlExtensionArray = $this->generateArray( "extension", "EA extension", $type, $value, $s_valid, $message, $s_info );
                $report = array_merge($report, $xmlExtensionArray);

                /**
                 * Check for the extension version and add it to the report array
                 */
                if( !empty( $extensionVersion ) && $extensionVersion !== "6.5" ):
                    $type  = "info";
                    $info += 1;
                elseif( empty( $extensionVersion ) ):
                    $type  = "error";
                    $error += 1;
                else:
                    $type  = "valid";
                    $valid += 1;
                endif;

                $value     = $extensionVersion;
                $s_valid   = ( !empty( $extensionVersion ) && $extensionVersion === "6.5" ? true : false );
                $message   = ( !empty( $extensionVersion ) ? ( $extensionVersion === "6.5" ? "Version found" : "Version found but other then 6.5" ) : "No version found" );
                $s_info    = "Couldn't find the EA extension version which is crucial for reproducibility.";

                $extensionVersionArray = $this->generateArray( "extensionVersion", "EA extension version", $type, $value, $s_valid, $message, $s_info );
                $report = array_merge($report, $extensionVersionArray);

                /**
                 * Parse the classes and count them
                 * Check if at least one class can be found
                 */
                $parsedClasses    = ( new IOXMLModelParser( $this->xmlFile ) )->parseXMLClasses();
                $parsedConnectors = ( new IOXMLModelParser( $this->xmlFile ) )->parseConnectors();

                $totalConnectors  = count( $parsedConnectors['connectors'] );
                $totalClasses     = count( $parsedClasses );

                $name      = ( $totalClasses === 1 ? "Class" : "Classes" );

                if( $totalClasses > 0 ):
                    $type  = "valid";
                    $valid += 1;
                elseif( $totalClasses === 0 ):
                    $type  = "severe";
                    $severe += 1;
                endif;

                $value      = $totalClasses;
                $s_valid    = ( $totalClasses !== 0 ? true : false );
                $message    = ( $totalClasses !== 0 ? $totalClasses.' classes found.' : "No classes found.");
                $s_info     = "There is no model without classes.";

                $totalClassesArray = $this->generateArray( "totalClasses", $name, $type, $value, $s_valid, $message, $s_info );
                $report = array_merge($report, $totalClassesArray);

                if( $parsedClasses['duplicateNames'] > 0 ):

                    $name     = ( $parsedClasses['duplicateNames'] === 1 ? "Duplicate class" : "Duplicate classes" );
                    $type     = "severe";
                    $value    = $parsedClasses['duplicateNames'];
                    $s_valid  = false;
                    $message  = ( $parsedClasses['duplicateNames'] === 1 ? $parsedClasses['duplicateNames'].' class found.' : $parsedClasses['duplicateNames']." classes found.");
                    $s_info   = "Duplicate classes are ignored and the model can not be processed.";

                    $duplicateClassesArray = $this->generateArray( "duplicateClasses", $name, $type, $value, $s_valid, $message, $s_info );
                    $report = array_merge($report, $duplicateClassesArray);

                    $severe += 1;

                endif;

                /**
                 * Collect data from the parsed classes
                 */
                $roots            = array();
                $trueRoots        = array();
                $modifiedDates    = array();
                $operations       = array();
                $classNames       = array();
                $classTags        = array();

                if( !empty( $parsedClasses ) ):

                    foreach( $parsedClasses as $parsedClass ):

                        /**
                         * Get all classes names
                         */
                        if( !empty( $parsedClass['name'] ) ):
                            $classNames[] = $parsedClass['name'];
                        endif;

                        /**
                         * Get all the roots out of the classes array
                         */
                        if( !empty( $parsedClass['Root'] ) ):
                            $roots[] = $parsedClass['Root'];
                            if( $parsedClass['Root'] === "true" ):
                                $trueRootClassName = $parsedClass['name'];
                                $report['trueRootClassName'] = $trueRootClassName;
                            endif;
                        endif;

                        /**
                         * Get all the modified dates out of the classes array
                         */
                        if( !empty( $parsedClass['modified'] ) ):
                            $modifiedDates[] = $parsedClass['modified'];
                        endif;

                        /**
                         * Get all the operations out of the classes array
                         */
                        if( !empty( $parsedClass['operations'] ) ):
                            $operations[] = $parsedClass['operations'];
                        endif;

                        /**
                         * Get all tags of the class
                         */
                        if( !empty( $parsedClass['tags'] ) ):
                            $classTags[] = $parsedClass['tags'];
                        endif;

                    endforeach;

                    foreach( $parsedClasses as $parsedClass ):

                        /**
                         * Check if a model name is available in the package
                         */
                        if( $parsedClass['type'] === "uml:Package" ):

                            if( empty( $parsedClass['package'] ) ):

                                if( empty( $parsedClass['name'] ) ):

                                    $name      = "Model name";
                                    $type      = "severe";
                                    $value     = "empty";
                                    $s_valid   = false;
                                    $message   = "No model name found.";
                                    $s_info    = "The model name could not be found, probably because an xml was exported from the wrong package.";

                                    $packageArray = $this->generateArray( "package", $name, $type, $value, $s_valid, $message, $s_info );
                                    $report = array_merge($report, $packageArray);

                                    $severe += 1;

                                endif;

                            endif;

                        endif;

                        /**
                         * Check if the class has a connector
                         */
                        if( $parsedClass['type'] === "uml:Class" ):

                            $connectors = 0;
                            for( $i = 0; $i < $totalConnectors; $i++ ):
                                if( $parsedClass['idref'] === $parsedConnectors['connectors']['connector'.($i+1)]['source']['idref']
                                    || $parsedClass['idref'] === $parsedConnectors['connectors']['connector'.($i+1)]['target']['idref']
                                ):
                                    $connectors += 1;
                                endif;
                            endfor;

                            if( $connectors === 0 ):

                                $name      = "Class connector missing";
                                $type      = "warning";
                                $value     = "empty";
                                $s_valid   = false;
                                $message   = "No class connector found.";
                                $s_info    = "The class connector could not be found. <strong>Class:</strong> ".$parsedClass['name'];

                                $connectorArray = $this->generateArray( "connectors", $name, $type, $value, $s_valid, $message, $s_info );
                                $report = array_merge($report, $connectorArray);

                                $warning += 1;

                            endif;

                        endif;

                    endforeach;

                    /**
                     * Check if any duplicate class orders are present
                     */
                    $classOrderArray            = array();
                    $uniqueClassOrders          = array();
                    $duplicateClassOrders       = 0;
                    $duplicateClassOrderNames   = array();
                    $totalClassTags             = count( $classTags );

                    if( !empty( $classTags ) ):
                        for( $i = 0; $i < $totalClassTags; $i++ ):
                            if(isset($classTags[$i]['QR-PrintOrder'])):
                            $order                  = $classTags[$i]['QR-PrintOrder']['order'];
                            $name                   = $classTags[$i]['QR-PrintOrder']['className'];
                            $classOrderArray[]      = $order;
                            if( !in_array( $order, $uniqueClassOrders ) ):
                                $uniqueClassOrders['order'] = $order;
                                $uniqueClassOrders['name']  = $name;
                            else:
                                $duplicateClassOrders += 1;
                                $duplicateClassOrderNames[] = $name;
                            endif;
                            endif;
                        endfor;
                    endif;


                    if( count( $classOrderArray ) !== count( array_unique( $classOrderArray ) ) ):

                        $name      = ( $duplicateClassOrders === 1 ? "Duplicate class order" : "Duplicate class orders" );
                        $type      = "severe";
                        $value     = $duplicateClassOrders;
                        $s_valid   = ( $duplicateClassOrders !== 0 && $duplicateClassOrders === 1 ? true : false );
                        $message   = $duplicateClassOrders." found.";

                        $j = 1;
                        foreach( $duplicateClassOrderNames as $classOrderName ):
                            $message .= " ".$classOrderName;
                            if( $j < $duplicateClassOrders ):
                                if( $j === ( $duplicateClassOrders - 1 ) || $j === 2 ):
                                    $message .= " and ";
                                endif;
                            else:
                                $message .= ".";
                            endif;
                            $j++;
                        endforeach;

                        $s_info          = "Duplicate class orders have been found and the model can not be processed.";
                        $duplicateClassOrderArray = $this->generateArray( "classOrder", $name, $type, $value, $s_valid, $message, $s_info );
                        $report = array_merge($report, $duplicateClassOrderArray);

                        $severe += 1;
                    endif;

                    /**
                     * Get all true roots and add them to the true roots array
                     */
                    $totalRoots = count( $roots );

                    if( $totalRoots !== 0 ):
                        for( $i = 0; $i < $totalRoots; $i++ ):
                            if( $roots[$i] === "true" ):
                                $trueRoots[] = $i;
                            endif;
                        endfor;
                    endif;

                    /**
                     * Count all true roots and determine if there is only one, also add it to the report array
                     */
                    $totalTrueRoots = count( $trueRoots );

                    $name      = ( $totalTrueRoots === 1 ? "Root" : "Roots" );

                    if( $totalTrueRoots === 1 ):
                        $type  = "valid";
                        $valid += 1;
                    elseif( $totalTrueRoots === 0 || $totalTrueRoots > 1 ):
                        $type  = "severe";
                        $severe += 1;
                    endif;

                    $value     = $totalTrueRoots;
                    $s_valid   = ( $totalTrueRoots !== 0 && $totalTrueRoots === 1 ? true : false );
                    $message   = ( $totalTrueRoots !== 0 && $totalTrueRoots === 1 ? $totalTrueRoots.' root found' : ( $totalTrueRoots > 1 ? $totalTrueRoots.' roots found' : 'No roots found' ) );
                    $s_info    = "A root is needed to define the starting point, only one root is allowed.";

                    $totalRootsArray = $this->generateArray( "totalRoots", $name, $type, $value, $s_valid, $message, $s_info );
                    $report = array_merge($report, $totalRootsArray);

                    /**
                     * Get the last modified date and add it to the report array
                     */
                    $maxDate           = max(array_map('strtotime', $modifiedDates));
                    $maxDate           = date('Y-m-d H:i:s', $maxDate);
                    $modifiedClassName = "";

                    foreach( $parsedClasses as $parsedClass):
                        if( isset( $parsedClass['modified'] ) && $parsedClass['modified'] === $maxDate ):
                            $modifiedClassName = $parsedClass['name'];
                            break;
                        endif;
                    endforeach;

                    $name      = ( "Last modified class" );

                    if( !empty( $modifiedDates ) ):
                        $type = "valid";
                        $valid += 1;
                    else:
                        $type  = "warning";
                        $warning += 1;
                    endif;


                    $value     = $maxDate;
                    $s_valid   = true;
                    $message   = ( !empty( $modifiedClassName ) ? "<strong>Class: </strong> ".$modifiedClassName : " No class name specified. " );
                    $s_info    = "";

                    $lastModifiedClassArray = $this->generateArray( "lastModified", $name, $type, $value, $s_valid, $message, $s_info );
                    $report = array_merge($report, $lastModifiedClassArray);

                    /**
                     * Get the fields from the parsed classes and validate them.
                     */
                    if( !empty( $parsedClasses ) ):

                        $i = 0;
                        $j = 0;
                        foreach( $parsedClasses as $parsedClass ):

                            if( !empty( $parsedClass['attributes'] ) ):

                                foreach( $parsedClass['attributes'] as $attribute ):

                                    /**
                                     * Validate the initial value by matching it with the data type.
                                     */
                                    $validInitialValue = ( new IOXMLPrimitiveTypes( $attribute['data_type'], $attribute['initialValue'] ) )->validate();

                                    if( isset( $attribute['documentation'] ) && $attribute['documentation'] === ""  ):

                                        $name      = "Attribute documentation.";
                                        $type      = "warning";
                                        $value     = "empty";
                                        $s_valid   = false;
                                        $message   = "There is no documentation specified with this attribute. <strong>Class:</strong> ".$attribute['class_name']." <strong>Attribute:</strong> ".$attribute['input_name'];
                                        $s_info    = "The documentation is missing and used to provide user information.";

                                        $attributeDocumentation[$i] = array();
                                        $attributeDocumentation[$i] = $this->generateArray( "AttributeDocumentation".$i, $name, $type, $value, $s_valid, $message, $s_info );
                                        $report = array_merge($report, $attributeDocumentation[$i]);

                                        $warning += 1;
                                        $i++;

                                    endif;

                                    if( $validInitialValue === false ):

                                        $name      = "Initial value data type";
                                        $type      = "warning";
                                        $value     = $attribute['initialValue'];
                                        $s_valid     = false;
                                        $message   = "Invalid data type. Allowed type: ".$attribute['data_type']." <strong>Class:</strong> ".$attribute['class_name']." <strong>Attribute:</strong> ".$attribute['input_name'];
                                        $s_info      = "The initial value doesn't match the data type.";

                                        $initialValueArray[$j] = array();
                                        $initialValueArray[$j] = $this->generateArray( "initialValueType".$j, $name, $type, $value, $s_valid, $message, $s_info );
                                        $report = array_merge($report, $initialValueArray[$j]);

                                        $warning += 1;
                                        $j++;

                                    endif;

                                endforeach;

                            endif;

                        endforeach;

                    endif;

                    /**
                     * Get all operations from the parsed classes and validate them
                     */
                    $operationsArray = array();
                    $tagsOrderArray = array();

                    if( !empty( $operations ) ):

                        $i = 0;
                        foreach( $operations as $operation ):
                            $operationsArray[$i] = $operation;
                            $i++;
                        endforeach;

                        $j = 0;
                        $k = 0;
                        $l = 0;
                        $m = 0;
                        $n = 0;
                        $o = 0;
                        $p = 0;
                        $q = 0;
                        $r = 0;
                        $s = 0;
                        $t = 0;
                        foreach($operationsArray as $operations):
                            foreach( $operations as $operation ):
                                /**
                                 * Add all operations without documentation to the parse report
                                 */
                                if( $operation['documentation'] === "" ):
                                    $name      = "Operation documentation";
                                    $type      = "warning";
                                    $value     = "empty";
                                    $s_valid   = false;
                                    $message   = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Documentation";
                                    $s_info    = "The documentation is missing and used to provide user information.";

                                    $operationDocumentationArray[$j] = array();
                                    $operationDocumentationArray[$j] = $this->generateArray( "OperationDocumentation".$j, $name, $type, $value, $s_valid, $message, $s_info );
                                    $report = array_merge($report, $operationDocumentationArray[$j]);

                                    $warning += 1;
                                    $j++;
                                endif;

                                if( !empty( $operation['tags'] ) ):

                                    $tagsArray      = array();
                                    foreach($operation['tags'] as $tag):
                                        if( !empty( $tag['name'] ) && $tag['name'] === "QR-PrintOrder" ):
                                            /**
                                             * Add print order name to the tags array
                                             */
                                            $tagsArray[] = "QR-PrintOrder";
                                            /**
                                             * Add print order number to tags order array
                                             */
                                            if( !in_array( $tag['cell'], $tagsOrderArray ) ):
                                                $tagsOrderArray[] = $tag['cell'];
                                            else:
                                                $name     = "Duplicate operation order";
                                                $type     = "warning";
                                                $value    = "empty";
                                                $s_valid  = false;
                                                $message  = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Tags->cell";
                                                $s_info   = "A duplicate operation order has been found.";

                                                $duplicateOperationOrderArray[$q] = array();
                                                $duplicateOperationOrderArray[$q] = $this->generateArray( "duplicateOperationOrder".$q, $name, $type, $value, $s_valid, $message, $s_info );
                                                $report = array_merge($report, $duplicateOperationOrderArray[$q]);

                                                $warning += 1;
                                                $q++;
                                            endif;
                                        endif;
                                        /**
                                         * Check if an operation has an order and excel file specified
                                         */
                                        if( empty( $tag['name'] ) ):
                                            $name      = "Operation order has no print order and no excel file";
                                            $type      = "warning";
                                            $value     = "empty";
                                            $s_valid   = false;
                                            $message   = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Tags->cell";
                                            $s_info    = "Operation has no print order and no excel file.";

                                            $operationNoOrderAndExcelTagArray[$t] = array();
                                            $operationNoOrderAndExcelTagArray[$t] = $this->generateArray( "OperationNoOrderAndExcelTag".$t, $name, $type, $value, $s_valid, $message, $s_info );
                                            $report = array_merge($report, $operationNoOrderAndExcelTagArray[$t]);

                                            $warning += 1;
                                            $t++;

                                        endif;
                                        /**
                                         * Check if an operation has a file specified
                                         */
                                        if( empty( $tag['file'] ) && $tag['name'] !== "QR-PrintOrder" ):
                                            $name      = "Operation has no file";
                                            $type      = "warning";
                                            $value     = "empty";
                                            $s_valid   = false;
                                            $message   = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Tags->file";
                                            $s_info    = "Without a specified file, input and output can not be handled properly.";

                                            $operationFileSpecifiedArray[$k] = array();
                                            $operationFileSpecifiedArray[$k] = $this->generateArray( "OperationFileSpecified".$k, $name, $type, $value, $s_valid, $message, $s_info );
                                            $report = array_merge($report, $operationFileSpecifiedArray[$k]);

                                            $warning += 1;
                                            $k++;
                                        endif;
                                        /**
                                         * Check if an operation has a tab specified
                                         */
                                        if( empty( $tag['tab'] ) && $tag['name'] !== "QR-PrintOrder" ):
                                            $name      = "No operation tab specified";
                                            $type      = "error";
                                            $value     = "empty";
                                            $s_valid   = false;
                                            $message   = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Tags->tab";
                                            $s_info    = "No tab/sheet has been specified";

                                            $operationTabSpecifiedArray[$l] = array();
                                            $operationTabSpecifiedArray[$l] = $this->generateArray( "OperationTabSpecified".$l, $name, $type, $value, $s_valid, $message, $s_info );
                                            $report = array_merge($report, $operationTabSpecifiedArray[$l]);

                                            $error += 1;
                                            $l++;

                                        endif;
                                        /**
                                         * Check if an operation has a cell specified
                                         */
                                        if( empty( $tag['cell'] ) ):
                                            $name      = "Operation cell";
                                            $type      = "warning";
                                            $value     = "empty";
                                            $s_valid   = false;
                                            $message   = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Tags->cell";
                                            $s_info    = ( $tag['name'] === "QR-PrintOrder" ? "Without an order operations can not be handled properly." : "Without a cell input and output can not be handled properly." );

                                            $operationCellSpecifiedArray[$m] = array();
                                            $operationCellSpecifiedArray[$m] = $this->generateArray( "OperationCellSpecified".$m, $name, $type, $value, $s_valid, $message, $s_info );
                                            $report = array_merge($report, $operationCellSpecifiedArray[$m]);

                                            $warning += 1;
                                            $m++;
                                        /**
                                         * Check if the operation cell has the right format
                                         */
                                        elseif( !empty( $tag['cell'] && $tag['name'] !== "QR-PrintOrder" ) ):

                                            $excelCell = str_replace( $tag['cell'], " ", "" );

                                            if( !preg_match( $this->matchExcelFormat , $excelCell) ):

                                                $name     = "Operation cell format";
                                                $type     = "error";
                                                $value    = "invalid";
                                                $s_valid  = false;
                                                $message  = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Tags->cell format";
                                                $s_info   = "The format of the specified cell is incorrect. Please us the following formats: A1 or A1:A2";

                                                $operationCellFormatArray[$n] = array();
                                                $operationCellFormatArray[$n] = $this->generateArray( "OperationCellFormat".$n, $name, $type, $value, $s_valid, $message, $s_info );
                                                $report = array_merge($report, $operationCellFormatArray[$n]);

                                                $error += 1;
                                                $n++;

                                            endif;

                                        endif;
                                        /**
                                         * Check if operation order is numeric
                                         */
                                        if( $tag['name'] === "QR-PrintOrder" && !empty( $tag['cell'] && !is_numeric( $tag['cell'] ) ) ):
                                            $name      = "Operation order tag not numeric";
                                            $type      = "error";
                                            $value     = "not numeric";
                                            $s_valid   = false;
                                            $message   = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Tags->file";
                                            $s_info    = "The operation order tag is not numeric. Example: 1 or 1.1 or 11.1";

                                            $operationOrderNotNumericArray[$s] = array();
                                            $operationOrderNotNumericArray[$s] = $this->generateArray( "operationOrderNotNumeric".$s, $name, $type, $value, $s_valid, $message, $s_info );
                                            $report = array_merge($report, $operationOrderNotNumericArray[$s]);

                                            $error += 1;
                                            $s++;

                                        endif;

                                    endforeach;
                                    /**
                                     * Check if a print order is specified
                                     */
                                    if( !in_array( "QR-PrintOrder", $tagsArray ) ):
                                        $name      = "No operation print order";
                                        $type      = "info";
                                        $value     = "empty";
                                        $s_valid   = false;
                                        $message   = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Tags->file";
                                        $s_info    = "Operation print order is not specified.";

                                        $operationHasNoOrderArray[$p] = array();
                                        $operationHasNoOrderArray[$p] = $this->generateArray( "operationHasNoPrintOrder".$p, $name, $type, $value, $s_valid, $message, $s_info );
                                        $report = array_merge($report, $operationHasNoOrderArray[$p]);

                                        $info += 1;
                                        $p++;
                                    /**
                                     * Check if there are multiple occurrences of the print order in this tag.
                                     */
                                    elseif( count( $tagsArray ) !== count( array_unique( $tagsArray ) ) ):
                                        $name      = "Multiple operation print order tags";
                                        $type      = "error";
                                        $value     = "multiple";
                                        $s_valid   = false;
                                        $message   = "<strong>Class: </strong>".$operation['className']." <strong>Operation:</strong> ".$operation['name']. " <strong>Attribute:</strong> Tags->file";
                                        $s_info    = "Multiple operation print order tags have been found.";

                                        $operationMultiplePrintOrderTagsArray[$r] = array();
                                        $operationMultiplePrintOrderTagsArray[$r] = $this->generateArray( "operationMultiplePrintOrderTags".$r, $name, $type, $value, $s_valid, $message, $s_info );
                                        $report = array_merge($report, $operationMultiplePrintOrderTagsArray[$r]);

                                        $error += 1;
                                        $r++;
                                    endif;
                                else:

                                    $name      = "Operation tags";
                                    $type      = "info";
                                    $value     = "empty";
                                    $s_valid     = false;
                                    $message   = "No operations tags found. <strong>Class:</strong> ".$operation['className'];
                                    $s_info      = "Operation tags are needed to handle input and output.";

                                    $operationNoTagsArray[$o] = array();
                                    $operationNoTagsArray[$o] = $this->generateArray( "operationNoTags".$o, $name, $type, $value, $s_valid, $message, $s_info );
                                    $report = array_merge($report, $operationNoTagsArray[$o]);

                                    $info += 1;
                                    $o++;

                                endif;

                            endforeach;

                        endforeach;

                    endif;

                endif;

            endif;

            /**
             * Check if all necessary items have been validated and conclude the total validation, also add it to the report array.
             */
            $totalErrorTypesArray['totalErrorTypes']             = array();
            $totalErrorTypesArray['totalErrorTypes']['severe']   = $severe;
            $totalErrorTypesArray['totalErrorTypes']['error']    = $error;
            $totalErrorTypesArray['totalErrorTypes']['warning']  = $warning;
            $totalErrorTypesArray['totalErrorTypes']['info']     = $info;
            $totalErrorTypesArray['totalErrorTypes']['valid']    = $valid;

            $report = array_merge($report, $totalErrorTypesArray);

            $validationArray['validation']              = array();
            $validationArray['validation']['name']      = "Validation";
            $validationArray['validation']['type']      = "severe";

            if( !empty( $severe )
                || !empty( $error )
            ):
                $validationValue = false;
                $validationValid = false;
                $validationMessage = "Invalid XML";
            else:

                $validationValue = true;
                $validationValid = true;
                $validationMessage = "Valid XML";

            endif;

            $validationArray['validation']['value']     = $validationValue;
            $validationArray['validation']['valid']     = $validationValid;
            $validationArray['validation']['message']   = $validationMessage;

            $report = array_merge($report, $validationArray);

            return( $report );

        }

        private function generateArray( $arrayName, $subject, $type, $value, $valid, $message, $info )
        {

            $parseReport[$arrayName]              = array();
            $parseReport[$arrayName]['name']      = $subject;
            $parseReport[$arrayName]['type']      = $type;
            $parseReport[$arrayName]['value']     = $value;
            $parseReport[$arrayName]['valid']     = $valid;
            $parseReport[$arrayName]['message']   = $message;
            $parseReport[$arrayName]['info']      = $info;

            return( $parseReport );

        }

    }

endif;