<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport;

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
 * The MeasureReport resource contains the results of the calculation of a measure; and optionally a reference to the resources involved in that calculation.
 */
class FHIRMeasureReportStratum extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The value for this stratum, expressed as a CodeableConcept. When defining stratifiers on complex values, the value must be rendered such that the value for each stratum within the stratifier is unique.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $value = null;

    /**
     * A stratifier component value.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport\FHIRMeasureReportComponent[]
     */
    public $component = [];

    /**
     * The populations that make up the stratum, one for each type of population appropriate to the measure.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport\FHIRMeasureReportPopulation1[]
     */
    public $population = [];

    /**
     * The measure score for this stratum, calculated as appropriate for the measure type and scoring method, and based on only the members of this stratum.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $measureScore = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MeasureReport.Stratum';

    /**
     * The value for this stratum, expressed as a CodeableConcept. When defining stratifiers on complex values, the value must be rendered such that the value for each stratum within the stratifier is unique.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The value for this stratum, expressed as a CodeableConcept. When defining stratifiers on complex values, the value must be rendered such that the value for each stratum within the stratifier is unique.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * A stratifier component value.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport\FHIRMeasureReportComponent[]
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * A stratifier component value.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport\FHIRMeasureReportComponent $component
     * @return $this
     */
    public function addComponent($component)
    {
        $this->component[] = $component;
        return $this;
    }

    /**
     * The populations that make up the stratum, one for each type of population appropriate to the measure.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport\FHIRMeasureReportPopulation1[]
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * The populations that make up the stratum, one for each type of population appropriate to the measure.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasureReport\FHIRMeasureReportPopulation1 $population
     * @return $this
     */
    public function addPopulation($population)
    {
        $this->population[] = $population;
        return $this;
    }

    /**
     * The measure score for this stratum, calculated as appropriate for the measure type and scoring method, and based on only the members of this stratum.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getMeasureScore()
    {
        return $this->measureScore;
    }

    /**
     * The measure score for this stratum, calculated as appropriate for the measure type and scoring method, and based on only the members of this stratum.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $measureScore
     * @return $this
     */
    public function setMeasureScore($measureScore)
    {
        $this->measureScore = $measureScore;
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
            if (isset($data['value'])) {
                $this->setValue($data['value']);
            }
            if (isset($data['component'])) {
                if (is_array($data['component'])) {
                    foreach ($data['component'] as $d) {
                        $this->addComponent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"component" must be array of objects or null, ' . gettype($data['component']) . ' seen.');
                }
            }
            if (isset($data['population'])) {
                if (is_array($data['population'])) {
                    foreach ($data['population'] as $d) {
                        $this->addPopulation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"population" must be array of objects or null, ' . gettype($data['population']) . ' seen.');
                }
            }
            if (isset($data['measureScore'])) {
                $this->setMeasureScore($data['measureScore']);
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
        return (string)$this->getValue();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        if (isset($this->value)) {
            $json['value'] = $this->value;
        }
        if (0 < count($this->component)) {
            $json['component'] = [];
            foreach ($this->component as $component) {
                $json['component'][] = $component;
            }
        }
        if (0 < count($this->population)) {
            $json['population'] = [];
            foreach ($this->population as $population) {
                $json['population'][] = $population;
            }
        }
        if (isset($this->measureScore)) {
            $json['measureScore'] = $this->measureScore;
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
            $sxe = new \SimpleXMLElement('<MeasureReportStratum xmlns="http://hl7.org/fhir"></MeasureReportStratum>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->value)) {
            $this->value->xmlSerialize(true, $sxe->addChild('value'));
        }
        if (0 < count($this->component)) {
            foreach ($this->component as $component) {
                $component->xmlSerialize(true, $sxe->addChild('component'));
            }
        }
        if (0 < count($this->population)) {
            foreach ($this->population as $population) {
                $population->xmlSerialize(true, $sxe->addChild('population'));
            }
        }
        if (isset($this->measureScore)) {
            $this->measureScore->xmlSerialize(true, $sxe->addChild('measureScore'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
