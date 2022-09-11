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
 * Class PHPFHIRResponseParser
 * @package \OpenEMR\FHIR\R4
 */
class PHPFHIRResponseParser
{
    /** @var \OpenEMR\FHIR\R4\PHPFHIRResponseParserConfig $config */
    private $config;

    /**
     * PHPFHIRResponseParser Constructor
     * @param \OpenEMR\FHIR\R4\PHPFHIRResponseParserConfig $config
     */
    public function __construct(PHPFHIRResponseParserConfig $config = null)
    {
        if (null === $config) {
            $config = new PHPFHIRResponseParserConfig;
        }
        $this->config = $config;
    }

    /**
     * @return \OpenEMR\FHIR\R4\PHPFHIRResponseParserConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array|string|\SimpleXMLElement $input
     * @return \OpenEMR\FHIR\R4\PHPFHIRTypeInterface|null
     */
    public function parse($input)
    {
        $inputType = gettype($input);
        if ('NULL' === $inputType) {
            return null;
        } elseif ('string' === $inputType) {
            return $this->parseStringInput($input);
        } elseif ('array' === $inputType) {
            return $this->parseArrayInput($input);
        } elseif ('object' === $inputType) {
            return $this->parseObjectInput($input);
        } else {
            throw new \InvalidArgumentException(sprintf(
                '%s::parse - $input must be XML or JSON encoded string, array, or an object of type \\DOMElement or \\SimpleXMLElement, %s seen.',
                get_class($this),
                $inputType
            ));
        }
    }

    /**
     * @param array $input
     * @return \OpenEMR\FHIR\R4\PHPFHIRTypeInterface|null
     */
    protected function parseArrayInput(array $input)
    {
        if ([] === $input) {
            return null;
        }
        if (isset($input[PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE])) {
            $className = PHPFHIRTypeMap::getTypeClass($input[PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE]);
            if (null === $className) {
                throw new \UnexpectedValueException(sprintf(
                    'Provided input has "%s" value of "%s", but it does not map to any known type.  Other keys: ["%s"]',
                    PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE,
                    $input[PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE],
                    implode('","', array_keys($input))
                ));
            }
            return new $className($input);
        }
        throw new \DomainException(sprintf(
            'Unable to determine FHIR Type from provided array: missing "%s" key.  Available keys: ["%s"]',
            PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE,
            implode('","', array_keys($input))
        ));
    }

    /**
     * @param \SimpleXMLElement $input
     * @return \OpenEMR\FHIR\R4\PHPFHIRTypeInterface|null
     */
    protected function parseObjectSimpleXMLElementInput(\SimpleXMLElement $input)
    {
        return self::parseObjectDOMDocumentInput(dom_import_simplexml($input));
    }

    /**
     * @param \DOMDocument $input
     * @return \OpenEMR\FHIR\R4\PHPFHIRTypeInterface|null
     */
    protected function parseObjectDOMDocumentInput(\DOMDocument $input)
    {
        $elementName = $input->documentElement->nodeName;
        $className = PHPFHIRTypeMap::getTypeClass($elementName);
         if (null === $className) {
            throw new \UnexpectedValueException(sprintf(
                'Unable to locate class for root XML element "%s". Input seen: %s',
                $elementName,
                $this->getPrintableStringInput($input->saveXML())
            ));
        }
        return $className::xmlUnserialize($input->documentElement);
    }

    /**
     * @param object $input
     * @return \OpenEMR\FHIR\R4\PHPFHIRTypeInterface|null
     */
    protected function parseObjectInput($input)
    {
        if ($input instanceof PHPFHIRTypeInterface) {
            return $input;
        } elseif ($input instanceof \SimpleXMLElement) {
            return $this->parseObjectSimpleXMLElementInput($input);
        } elseif ($input instanceof \DOMDocument) {
            return $this->parseObjectDOMDocumentInput($input);
        }
        throw new \UnexpectedValueException(sprintf(
            'Unable parse provided input object of type "%s"',
            get_class($input)
        ));
        // TODO: implement \stdClass handling?
    }

    /**
     * @param string $input
     * @param null|int $libxmlOpts
     * @return \OpenEMR\FHIR\R4\PHPFHIRTypeInterface|null
     */
    protected function parseStringXMLInput($input, $libxmlOpts = 591872)
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($input, $libxmlOpts);
        $err = libxml_get_last_error();
        libxml_use_internal_errors(false);
        if ($dom instanceof \DOMDocument) {
            return $this->parseObjectDOMDocumentInput($dom);
        }
        throw new \DomainException(sprintf(
            'Unable to parse provided input as XML.  Error: %s; Input: %s',
            $err ? $err->message : 'Unknown',
            $this->getPrintableStringInput($input)
        ));
    }

    /**
     * @param string $input
     * @return \OpenEMR\FHIR\R4\PHPFHIRTypeInterface|null
     */
    protected function parseStringJSONInput($input)
    {
        $decoded = json_decode($input, true);
        $err = json_last_error();
        if (JSON_ERROR_NONE !== $err) {
            if (function_exists('php_last_error_msg')) {
                $reason = json_last_error_msg();
            } else {
                $reason = $err;
            }
            throw new \DomainException(sprintf(
                'Unable to parse provided input as JSON.  Error: %s; Input: %s',
                $reason,
               $this->getPrintableStringInput($input)
            ));
        }

        return $this->parseArrayInput($decoded);
    }

    /**
     * @param string $input
     * @return \OpenEMR\FHIR\R4\PHPFHIRTypeInterface|null
     */
    protected function parseStringInput($input)
    {
        $input = trim($input);
        if ('' === $input) {
            return null;
        }
        $chr = $input[0];
        if ('<' === $chr) {
            return $this->parseStringXMLInput($input);
        } elseif ('{' === $chr) {
            return $this->parseStringJSONInput($input);
        } else {
            throw new \UnexpectedValueException(sprintf(
                'Input string must be either XML or JSON encoded object.  Provided: %s',
               $this->getPrintableStringInput($input)
            ));
        }
    }

    /**
     * @param string $input
     * @return string
     */
    protected function getPrintableStringInput($input)
    {
        return strlen($input) > 100 ? substr($input, 0, 100) . '[...]' : $input;
    }
}
