<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimenDefinition;

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
 * A kind of specimen with associated set of requirements.
 */
class FHIRSpecimenDefinitionHandling extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * It qualifies the interval of temperature, which characterizes an occurrence of handling. Conditions that are not related to temperature may be handled in the instruction element.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $temperatureQualifier = null;

    /**
     * The temperature interval for this set of handling instructions.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $temperatureRange = null;

    /**
     * The maximum time interval of preservation of the specimen with these conditions.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $maxDuration = null;

    /**
     * Additional textual instructions for the preservation or transport of the specimen. For instance, 'Protect from light exposure'.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $instruction = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SpecimenDefinition.Handling';

    /**
     * It qualifies the interval of temperature, which characterizes an occurrence of handling. Conditions that are not related to temperature may be handled in the instruction element.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getTemperatureQualifier()
    {
        return $this->temperatureQualifier;
    }

    /**
     * It qualifies the interval of temperature, which characterizes an occurrence of handling. Conditions that are not related to temperature may be handled in the instruction element.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $temperatureQualifier
     * @return $this
     */
    public function setTemperatureQualifier($temperatureQualifier)
    {
        $this->temperatureQualifier = $temperatureQualifier;
        return $this;
    }

    /**
     * The temperature interval for this set of handling instructions.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getTemperatureRange()
    {
        return $this->temperatureRange;
    }

    /**
     * The temperature interval for this set of handling instructions.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $temperatureRange
     * @return $this
     */
    public function setTemperatureRange($temperatureRange)
    {
        $this->temperatureRange = $temperatureRange;
        return $this;
    }

    /**
     * The maximum time interval of preservation of the specimen with these conditions.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getMaxDuration()
    {
        return $this->maxDuration;
    }

    /**
     * The maximum time interval of preservation of the specimen with these conditions.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $maxDuration
     * @return $this
     */
    public function setMaxDuration($maxDuration)
    {
        $this->maxDuration = $maxDuration;
        return $this;
    }

    /**
     * Additional textual instructions for the preservation or transport of the specimen. For instance, 'Protect from light exposure'.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * Additional textual instructions for the preservation or transport of the specimen. For instance, 'Protect from light exposure'.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $instruction
     * @return $this
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
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
            if (isset($data['temperatureQualifier'])) {
                $this->setTemperatureQualifier($data['temperatureQualifier']);
            }
            if (isset($data['temperatureRange'])) {
                $this->setTemperatureRange($data['temperatureRange']);
            }
            if (isset($data['maxDuration'])) {
                $this->setMaxDuration($data['maxDuration']);
            }
            if (isset($data['instruction'])) {
                $this->setInstruction($data['instruction']);
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
        if (isset($this->temperatureQualifier)) {
            $json['temperatureQualifier'] = $this->temperatureQualifier;
        }
        if (isset($this->temperatureRange)) {
            $json['temperatureRange'] = $this->temperatureRange;
        }
        if (isset($this->maxDuration)) {
            $json['maxDuration'] = $this->maxDuration;
        }
        if (isset($this->instruction)) {
            $json['instruction'] = $this->instruction;
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
            $sxe = new \SimpleXMLElement('<SpecimenDefinitionHandling xmlns="http://hl7.org/fhir"></SpecimenDefinitionHandling>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->temperatureQualifier)) {
            $this->temperatureQualifier->xmlSerialize(true, $sxe->addChild('temperatureQualifier'));
        }
        if (isset($this->temperatureRange)) {
            $this->temperatureRange->xmlSerialize(true, $sxe->addChild('temperatureRange'));
        }
        if (isset($this->maxDuration)) {
            $this->maxDuration->xmlSerialize(true, $sxe->addChild('maxDuration'));
        }
        if (isset($this->instruction)) {
            $this->instruction->xmlSerialize(true, $sxe->addChild('instruction'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
