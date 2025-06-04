<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities;

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
 *   Generated on Thu, Dec 27, 2018 22:37+1100 for FHIR v4.0.0
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;

/**
 * A TerminologyCapabilities resource documents a set of capabilities (behaviors) of a FHIR Terminology Server that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRTerminologyCapabilitiesVersion extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * For version-less code systems, there should be a single version with no identifier.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $code = null;

    /**
     * If this is the default version for this code system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $isDefault = null;

    /**
     * If the compositional grammar defined by the code system is supported.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $compositional = null;

    /**
     * Language Displays supported.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public $language = [];

    /**
     * Filter Properties supported.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesFilter[]
     */
    public $filter = [];

    /**
     * Properties supported for $lookup.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public $property = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'TerminologyCapabilities.Version';

    /**
     * For version-less code systems, there should be a single version with no identifier.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * For version-less code systems, there should be a single version with no identifier.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * If this is the default version for this code system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * If this is the default version for this code system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $isDefault
     * @return $this
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * If the compositional grammar defined by the code system is supported.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getCompositional()
    {
        return $this->compositional;
    }

    /**
     * If the compositional grammar defined by the code system is supported.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $compositional
     * @return $this
     */
    public function setCompositional($compositional)
    {
        $this->compositional = $compositional;
        return $this;
    }

    /**
     * Language Displays supported.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Language Displays supported.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $language
     * @return $this
     */
    public function addLanguage($language)
    {
        $this->language[] = $language;
        return $this;
    }

    /**
     * Filter Properties supported.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesFilter[]
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Filter Properties supported.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesFilter $filter
     * @return $this
     */
    public function addFilter($filter)
    {
        $this->filter[] = $filter;
        return $this;
    }

    /**
     * Properties supported for $lookup.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Properties supported for $lookup.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $property
     * @return $this
     */
    public function addProperty($property)
    {
        $this->property[] = $property;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @param mixed $data
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['isDefault'])) {
                $this->setIsDefault($data['isDefault']);
            }
            if (isset($data['compositional'])) {
                $this->setCompositional($data['compositional']);
            }
            if (isset($data['language'])) {
                if (is_array($data['language'])) {
                    foreach ($data['language'] as $d) {
                        $this->addLanguage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"language" must be array of objects or null, ' . gettype($data['language']) . ' seen.');
                }
            }
            if (isset($data['filter'])) {
                if (is_array($data['filter'])) {
                    foreach ($data['filter'] as $d) {
                        $this->addFilter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"filter" must be array of objects or null, ' . gettype($data['filter']) . ' seen.');
                }
            }
            if (isset($data['property'])) {
                if (is_array($data['property'])) {
                    foreach ($data['property'] as $d) {
                        $this->addProperty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"property" must be array of objects or null, ' . gettype($data['property']) . ' seen.');
                }
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
        }
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->isDefault)) {
            $json['isDefault'] = $this->isDefault;
        }
        if (isset($this->compositional)) {
            $json['compositional'] = $this->compositional;
        }
        if (0 < count($this->language)) {
            $json['language'] = [];
            foreach ($this->language as $language) {
                $json['language'][] = $language;
            }
        }
        if (0 < count($this->filter)) {
            $json['filter'] = [];
            foreach ($this->filter as $filter) {
                $json['filter'][] = $filter;
            }
        }
        if (0 < count($this->property)) {
            $json['property'] = [];
            foreach ($this->property as $property) {
                $json['property'][] = $property;
            }
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<TerminologyCapabilitiesVersion xmlns="http://hl7.org/fhir"></TerminologyCapabilitiesVersion>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->isDefault)) {
            $this->isDefault->xmlSerialize(true, $sxe->addChild('isDefault'));
        }
        if (isset($this->compositional)) {
            $this->compositional->xmlSerialize(true, $sxe->addChild('compositional'));
        }
        if (0 < count($this->language)) {
            foreach ($this->language as $language) {
                $language->xmlSerialize(true, $sxe->addChild('language'));
            }
        }
        if (0 < count($this->filter)) {
            foreach ($this->filter as $filter) {
                $filter->xmlSerialize(true, $sxe->addChild('filter'));
            }
        }
        if (0 < count($this->property)) {
            foreach ($this->property as $property) {
                $property->xmlSerialize(true, $sxe->addChild('property'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
