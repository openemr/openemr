<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition;

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
 * The characteristics, operational status and capabilities of a medical-related component of a medical device.
 */
class FHIRDeviceDefinitionProperty extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Code that specifies the property DeviceDefinitionPropetyCode (Extensible).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Property value as a quantity.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity[]
     */
    public $valueQuantity = [];

    /**
     * Property value as a code, e.g., NTP4 (synced to NTP).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $valueCode = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'DeviceDefinition.Property';

    /**
     * Code that specifies the property DeviceDefinitionPropetyCode (Extensible).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Code that specifies the property DeviceDefinitionPropetyCode (Extensible).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Property value as a quantity.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity[]
     */
    public function getValueQuantity()
    {
        return $this->valueQuantity;
    }

    /**
     * Property value as a quantity.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $valueQuantity
     * @return $this
     */
    public function addValueQuantity($valueQuantity)
    {
        $this->valueQuantity[] = $valueQuantity;
        return $this;
    }

    /**
     * Property value as a code, e.g., NTP4 (synced to NTP).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getValueCode()
    {
        return $this->valueCode;
    }

    /**
     * Property value as a code, e.g., NTP4 (synced to NTP).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $valueCode
     * @return $this
     */
    public function addValueCode($valueCode)
    {
        $this->valueCode[] = $valueCode;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['valueQuantity'])) {
                if (is_array($data['valueQuantity'])) {
                    foreach ($data['valueQuantity'] as $d) {
                        $this->addValueQuantity($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"valueQuantity" must be array of objects or null, ' . gettype($data['valueQuantity']) . ' seen.');
                }
            }
            if (isset($data['valueCode'])) {
                if (is_array($data['valueCode'])) {
                    foreach ($data['valueCode'] as $d) {
                        $this->addValueCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"valueCode" must be array of objects or null, ' . gettype($data['valueCode']) . ' seen.');
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
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->valueQuantity)) {
            $json['valueQuantity'] = [];
            foreach ($this->valueQuantity as $valueQuantity) {
                $json['valueQuantity'][] = $valueQuantity;
            }
        }
        if (0 < count($this->valueCode)) {
            $json['valueCode'] = [];
            foreach ($this->valueCode as $valueCode) {
                $json['valueCode'][] = $valueCode;
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
            $sxe = new \SimpleXMLElement('<DeviceDefinitionProperty xmlns="http://hl7.org/fhir"></DeviceDefinitionProperty>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->valueQuantity)) {
            foreach ($this->valueQuantity as $valueQuantity) {
                $valueQuantity->xmlSerialize(true, $sxe->addChild('valueQuantity'));
            }
        }
        if (0 < count($this->valueCode)) {
            foreach ($this->valueCode as $valueCode) {
                $valueCode->xmlSerialize(true, $sxe->addChild('valueCode'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
