<?php

namespace OpenEMR\FHIR\R4;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: September 10th, 2022 20:42+0000
 * 
 * PHPFHIR Copyright:
 * 
 * Copyright 2016-2022 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 * 
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 * 
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 * 
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 * 
 * 
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 * 
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 * 
 */

/**
 * Raw type used in special cases
 *
 * Class FHIRRaw
 * @package \OpenEMR\FHIR\R4
 */
class FHIRRaw implements PHPFHIRTypeInterface
{
    use PHPFHIRValidationAssertionsTrait;
    use PHPFHIRChangeTrackingTrait;

    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_RAW;
    const TO_STRING_FUNC = '__toString';

    /** @var string */
    private $_data = null;
    /** @var string */
    private $_elementName = null;
    /** @var string */
    private $_xmlns = '';

    /** @var array */
    private static $_validationRules = [];

    /**
     * raw Constructor
     * @param null|string|int|float|bool|object $data
     */
    public function __construct($data = null)
    {
        $this->_setData($data);
    }

    /**
     * The name of the FHIR element this raw type represents
     *
     * @param string $elementName
     * @return \OpenEMR\FHIR\R4\FHIRRaw
     */
    public function _setElementName($elementName)
    {
        $this->_elementName = $elementName;
        return $this;
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLNamespace()
    {
        return $this->_xmlns;
    }

    /**
     * @param null|string $xmlNamespace
     * @return static
     */
    public function _setFHIRXMLNamespace($xmlNamespace)
    {
        $this->_xmlns = trim((string)$xmlNamespace);
        return $this;
    }


    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if ('' !==  $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<raw{$xmlns}></raw>";
    }

    /**
     * @return null|string|integer|float|boolean|object
     */
    public function _getData()
    {
        return $this->_data;
    }

    /**
     * @param mixed $data
     * @return \OpenEMR\FHIR\R4\FHIRRaw
     */
    public function _setData($data)
    {
        if (null === $data) {
            $this->_data = null;
            return $this;
        }
        if (is_scalar($data) || (is_object($data) && (method_exists($data, self::TO_STRING_FUNC) || $data instanceof \DOMNode || $data instanceof \DOMText))) {
            $this->_data = $data;
            return $this;
        }
        throw new \InvalidArgumentException(sprintf(
            '$data must be one of: null, string, integer, double, boolean, or object implementing "__toString", saw "%s"',
            gettype($data)
        ));
    }


    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = [];
        $validationRules = $this->_getValidationRules();
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRRaw $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRRaw
     */
    public static function xmlUnserialize($element = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            return null;
        }
        if (is_string($element)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($element, $libxmlOpts);
            if (false === $dom) {
                throw new \DomainException(sprintf('FHIRRaw::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRRaw::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRRaw(null);
        } elseif (!is_object($type) || !($type instanceof FHIRRaw)) {
            throw new \RuntimeException(sprintf(
                'FHIRRaw::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRRaw or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        $dom = new \DOMDocument();
        $dom->loadXML($element->ownerDocument->saveXML($element), $libxmlOpts | LIBXML_NOXMLDECL);
        $type->_setData($dom->documentElement);
        return $type;
    }

     /**
     * @param \DOMElement|string|null $element
     * @param null|int $libxmlOpts
     * @return \DOMElement
     */
    public function xmlSerialize(\DOMElement $element = null, $libxmlOpts = 591872)
    {
        $data = $this->_getData();
        $xmlns = $this->_getFHIRXMLNamespace();
        if (null === $element) {
            $dom = new \DOMDocument();
            if (!empty($xmlns)) {
                $xmlns = " xmlns=\"{$xmlns}\"";
            }
            if (null === $data) {
                $dom->loadXML("<raw{$xmlns}></raw", $libxmlOpts);
                return $dom->documentElement;
            }
            if (is_scalar($data) || (is_object($data) && !($data instanceof \DOMNode) && !($data instanceof \DOMText))) {
                if (is_bool($data)) {
                    $strval = $data ? 'true' : 'false';
                } else {
                    $strval = (string)$data;
                }
                $dom->loadXML("<raw{$xmlns}>{$strval}</raw", $libxmlOpts);
                return $dom->documentElement;
            }
            return $dom->documentElement;
        }

        if (!empty($xmlns)) {
            $element->setAttribute('xmlns', $xmlns);
        }

        if ($data instanceof \DOMElement) {
            if ($data->hasAttributes()) {
                for ($i = 0; $i < $data->attributes->length; $i++) {
                    $attr = $data->attributes->item($i);
                    $element->setAttribute($attr->nodeName, $attr->nodeValue);
                }
            }
            if ($data->hasChildNodes()) {
                for ($i = 0; $i < $data->childNodes->length; $i++) {
                    $n = $data->childNodes->item($i);
                    $n = $element->ownerDocument->importNode($n, true);
                    $element->appendChild($n);
                }
            }
        }

        return $element;
    }

    /**
     * @return null|string|integer|float|boolean|object
     */
    public function jsonSerialize()
    {
        return $this->_getData();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->_getData());
    }

}