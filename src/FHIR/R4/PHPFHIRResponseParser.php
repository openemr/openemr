<?php

namespace OpenEMR\FHIR\R4;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: June 14th, 2019
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

class PHPFHIRResponseParser
{
    /**
     * If response is XML, these arguments will be passed into the \SimpleXMLElement constructor
     * @see http://php.net/manual/en/libxml.constants.php for a list of options.
     * @var int
     */
    public static $sxeArgs = null;

    /** @var PHPFHIRParserMap */
    private $_parserMap;

    /**
     * Constructor
     *
     * @param bool $registerAutoloader
     */
    public function __construct($registerAutoloader = true)
    {
        if (null === self::$sxeArgs) {
            self::$sxeArgs = LIBXML_COMPACT | LIBXML_NSCLEAN;
        }

        if ($registerAutoloader) {
            self::_registerAutoloader();
        }

        $this->_parserMap = new PHPFHIRParserMap();
    }

    /**
     * @param string $input
     * @return object Root object type depends on initial query type and parameters
     */
    public function parse($input)
    {
        if (!is_string($input)) {
            throw $this->_createNonStringArgumentException($input);
        }

        switch (substr($input, 0, 1)) {
            case '<':
                return $this->_parseXML($input);

            case '{':
                return $this->_parseJson($input);

            default:
                throw new \RuntimeException(sprintf(
                    '%s::parse - Unable to determine response type, expected JSON or XML.',
                    get_class($this)
                ));
        }
    }

    /**
     * @param string $input
     * @return object
     */
    private function _parseJson($input)
    {
        $decoded = json_decode($input, true);

        $lastError = json_last_error();
        if (JSON_ERROR_NONE === $lastError) {
            return $this->_parseJsonObject($decoded, $decoded['resourceType']);
        }

        throw new \DomainException(sprintf(
            '%s::parse - Error encountered while decoding json input.  Error code: %s',
            get_class($this),
            $lastError
        ));
    }

    /**
     * @param string $input
     * @return object
     */
    private function _parseXML($input)
    {
        libxml_use_internal_errors(true);
        $sxe = new \SimpleXMLElement($input, self::$sxeArgs);
        $error = libxml_get_last_error();
        libxml_use_internal_errors(false);

        if ($sxe instanceof \SimpleXMLElement) {
            return $this->_parseXMLNode($sxe, $sxe->getName());
        }

        throw new \RuntimeException(sprintf(
            'Unable to parse response: "%s"',
            ($error ? $error->message : 'Unknown Error')
        ));
    }

    /**
     * @param array $jsonEntry
     * @param string $fhirElementName
     * @return mixed
     */
    private function _parseJsonObject($jsonEntry, $fhirElementName)
    {
        if ('html' === $fhirElementName) {
            return $jsonEntry;
        }

        if (false !== strpos($fhirElementName, '-primitive') || false !== strpos($fhirElementName, '-list')) {
            return $jsonEntry;
        }

        $map = $this->_tryGetMapEntry($fhirElementName);

        $fullClassName = $map['fullClassName'];
        $properties = $map['properties'];

        $object = new $fullClassName();

        // This indicates we are at a primitive value...
        if (is_scalar($jsonEntry)) {
            if (isset($properties['value'])) {
                $propertyMap = $properties['value'];
                $setter = $propertyMap['setter'];
                $element = $propertyMap['element'];

                if (sprintf('%s-primitive', $fhirElementName) === $element || sprintf('%s-list', $fhirElementName) === $element) {
                    $object->$setter($jsonEntry);
                } else {
                    $this->_triggerPropertyNotFoundError($fhirElementName, 'value');
                }
            } else {
                $this->_triggerPropertyNotFoundError($fhirElementName, 'value');
            }
        } elseif (isset($jsonEntry['resourceType']) && $jsonEntry['resourceType'] !== $fhirElementName) { // TODO:
            // This is probably very not ok...
            $propertyMap = $properties[$jsonEntry['resourceType']];
            $setter = $propertyMap['setter'];
            $element = $propertyMap['element'];
            $object->$setter($this->_parseJsonObject($jsonEntry, $element));
        } else {
            foreach ($jsonEntry as $k => $v) {
                switch ($k) {
                    case 'resourceType':
                    case 'fhir_comments':
                        continue 2;
                }

                if (!isset($properties[$k])) {
                    $this->_triggerPropertyNotFoundError($fhirElementName, $k);
                    continue;
                }

                $propertyMap = $properties[$k];
                $setter = $propertyMap['setter'];
                $element = $propertyMap['element'];

                if (is_array($v)) {
                    $firstKey = key($v);

                    if (is_string($firstKey)) {
                        $object->$setter($this->_parseJsonObject($v, $element));
                    } else {
                        foreach ($v as $child) {
                            $object->$setter($this->_parseJsonObject($child, $element));
                        }
                    }
                } else {
                    $object->$setter($this->_parseJsonObject($v, $element));
                }
            }
        }

        return $object;
    }

    /**
     * @param \SimpleXMLElement $element
     * @param string $fhirElementName
     * @return mixed
     */
    private function _parseXMLNode(\SimpleXMLElement $element, $fhirElementName)
    {
        if ('html' === $fhirElementName) {
            return $element->saveXML();
        }

        if (false !== strpos($fhirElementName, '-primitive') || false !== strpos($fhirElementName, '-list')) {
            return (string)$element;
        }

        $map = $this->_tryGetMapEntry($fhirElementName);

        $fullClassName = $map['fullClassName'];
        $properties = $map['properties'];

        $object = new $fullClassName();

        /** @var \SimpleXMLElement $attribute */
        foreach ($element->attributes() as $attribute) {
            $childName = $attribute->getName();
            if (!isset($properties[$childName])) {
                $this->_triggerPropertyNotFoundError($fhirElementName, $childName);
                continue;
            }

            $propertyMap = $properties[$childName];
            $setter = $propertyMap['setter'];

            $object->$setter((string)$attribute);
        }

        /** @var \SimpleXMLElement $childElement */
        foreach ($element->children() as $childElement) {
            $childName = $childElement->getName();
            if (!isset($properties[$childName])) {
                $this->_triggerPropertyNotFoundError($fhirElementName, $childName);
                continue;
            }

            $propertyMap = $properties[$childName];
            $setter = $propertyMap['setter'];
            $element = $propertyMap['element'];

            $object->$setter($this->_parseXMLNode($childElement, $element));
        }

        return $object;
    }

    /**
     * @param string $fhirElementName
     * @return array
     */
    private function _tryGetMapEntry($fhirElementName)
    {
        if (!isset($this->_parserMap[$fhirElementName])) {
            throw new \RuntimeException(sprintf(
                'Element map does not contain entry for "%s".  This indicates either malformed response or bug in class generation.',
                $fhirElementName
            ));
        }

        return $this->_parserMap[$fhirElementName];
    }

    /**
     * @param string $fhirElementName
     * @param string $propertyName
     * @return bool
     */
    private function _triggerPropertyNotFoundError($fhirElementName, $propertyName)
    {
        return trigger_error(sprintf(
            'Could not find mapped property called "%s" on object "%s".  This could indicate malformed response or bug in class generator.',
            $propertyName,
            $fhirElementName
        ));
    }

    /**
     * @param mixed $input
     * @return \InvalidArgumentException
     */
    private function _createNonStringArgumentException($input)
    {
        return new \InvalidArgumentException(sprintf(
            '%s::parse - Argument 1 expected to be string, %s seen.',
            get_called_class(),
            gettype($input)
        ));
    }

    private static function _registerAutoloader()
    {
        $autoloaderClass = __NAMESPACE__ . '\PHPFHIRAutoloader';
        $autoloaderClassFile = __DIR__ . '/PHPFHIRAutoloader.php';

        if (!class_exists($autoloaderClass, false)) {
            if (!file_exists($autoloaderClassFile)) {
                throw new \RuntimeException(sprintf(
                    'PHPFHIRAutoloader class is not defined and was not found at expected location "%s".',
                    $autoloaderClassFile
                ));
            }

            require $autoloaderClassFile;
        }

        $autoloaderClass::register();
    }
}
