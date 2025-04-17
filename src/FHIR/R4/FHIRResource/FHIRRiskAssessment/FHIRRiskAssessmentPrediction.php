<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRRiskAssessment;

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
 * An assessment of the likely outcome(s) for a patient or other subject as well as the likelihood of each outcome.
 */
class FHIRRiskAssessmentPrediction extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * One of the potential outcomes for the patient (e.g. remission, death,  a particular condition).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $outcome = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $probabilityDecimal = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $probabilityRange = null;

    /**
     * Indicates how likely the outcome is (in the specified timeframe), expressed as a qualitative value (e.g. low, medium, or high).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $qualitativeRisk = null;

    /**
     * Indicates the risk for this particular subject (with their specific characteristics) divided by the risk of the population in general.  (Numbers greater than 1 = higher risk than the population, numbers less than 1 = lower risk.).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $relativeRisk = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $whenPeriod = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $whenRange = null;

    /**
     * Additional information explaining the basis for the prediction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $rationale = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'RiskAssessment.Prediction';

    /**
     * One of the potential outcomes for the patient (e.g. remission, death,  a particular condition).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * One of the potential outcomes for the patient (e.g. remission, death,  a particular condition).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getProbabilityDecimal()
    {
        return $this->probabilityDecimal;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $probabilityDecimal
     * @return $this
     */
    public function setProbabilityDecimal($probabilityDecimal)
    {
        $this->probabilityDecimal = $probabilityDecimal;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getProbabilityRange()
    {
        return $this->probabilityRange;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $probabilityRange
     * @return $this
     */
    public function setProbabilityRange($probabilityRange)
    {
        $this->probabilityRange = $probabilityRange;
        return $this;
    }

    /**
     * Indicates how likely the outcome is (in the specified timeframe), expressed as a qualitative value (e.g. low, medium, or high).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getQualitativeRisk()
    {
        return $this->qualitativeRisk;
    }

    /**
     * Indicates how likely the outcome is (in the specified timeframe), expressed as a qualitative value (e.g. low, medium, or high).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $qualitativeRisk
     * @return $this
     */
    public function setQualitativeRisk($qualitativeRisk)
    {
        $this->qualitativeRisk = $qualitativeRisk;
        return $this;
    }

    /**
     * Indicates the risk for this particular subject (with their specific characteristics) divided by the risk of the population in general.  (Numbers greater than 1 = higher risk than the population, numbers less than 1 = lower risk.).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getRelativeRisk()
    {
        return $this->relativeRisk;
    }

    /**
     * Indicates the risk for this particular subject (with their specific characteristics) divided by the risk of the population in general.  (Numbers greater than 1 = higher risk than the population, numbers less than 1 = lower risk.).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $relativeRisk
     * @return $this
     */
    public function setRelativeRisk($relativeRisk)
    {
        $this->relativeRisk = $relativeRisk;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getWhenPeriod()
    {
        return $this->whenPeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $whenPeriod
     * @return $this
     */
    public function setWhenPeriod($whenPeriod)
    {
        $this->whenPeriod = $whenPeriod;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getWhenRange()
    {
        return $this->whenRange;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $whenRange
     * @return $this
     */
    public function setWhenRange($whenRange)
    {
        $this->whenRange = $whenRange;
        return $this;
    }

    /**
     * Additional information explaining the basis for the prediction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getRationale()
    {
        return $this->rationale;
    }

    /**
     * Additional information explaining the basis for the prediction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $rationale
     * @return $this
     */
    public function setRationale($rationale)
    {
        $this->rationale = $rationale;
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
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
            }
            if (isset($data['probabilityDecimal'])) {
                $this->setProbabilityDecimal($data['probabilityDecimal']);
            }
            if (isset($data['probabilityRange'])) {
                $this->setProbabilityRange($data['probabilityRange']);
            }
            if (isset($data['qualitativeRisk'])) {
                $this->setQualitativeRisk($data['qualitativeRisk']);
            }
            if (isset($data['relativeRisk'])) {
                $this->setRelativeRisk($data['relativeRisk']);
            }
            if (isset($data['whenPeriod'])) {
                $this->setWhenPeriod($data['whenPeriod']);
            }
            if (isset($data['whenRange'])) {
                $this->setWhenRange($data['whenRange']);
            }
            if (isset($data['rationale'])) {
                $this->setRationale($data['rationale']);
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
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
        }
        if (isset($this->probabilityDecimal)) {
            $json['probabilityDecimal'] = $this->probabilityDecimal;
        }
        if (isset($this->probabilityRange)) {
            $json['probabilityRange'] = $this->probabilityRange;
        }
        if (isset($this->qualitativeRisk)) {
            $json['qualitativeRisk'] = $this->qualitativeRisk;
        }
        if (isset($this->relativeRisk)) {
            $json['relativeRisk'] = $this->relativeRisk;
        }
        if (isset($this->whenPeriod)) {
            $json['whenPeriod'] = $this->whenPeriod;
        }
        if (isset($this->whenRange)) {
            $json['whenRange'] = $this->whenRange;
        }
        if (isset($this->rationale)) {
            $json['rationale'] = $this->rationale;
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
            $sxe = new \SimpleXMLElement('<RiskAssessmentPrediction xmlns="http://hl7.org/fhir"></RiskAssessmentPrediction>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if (isset($this->probabilityDecimal)) {
            $this->probabilityDecimal->xmlSerialize(true, $sxe->addChild('probabilityDecimal'));
        }
        if (isset($this->probabilityRange)) {
            $this->probabilityRange->xmlSerialize(true, $sxe->addChild('probabilityRange'));
        }
        if (isset($this->qualitativeRisk)) {
            $this->qualitativeRisk->xmlSerialize(true, $sxe->addChild('qualitativeRisk'));
        }
        if (isset($this->relativeRisk)) {
            $this->relativeRisk->xmlSerialize(true, $sxe->addChild('relativeRisk'));
        }
        if (isset($this->whenPeriod)) {
            $this->whenPeriod->xmlSerialize(true, $sxe->addChild('whenPeriod'));
        }
        if (isset($this->whenRange)) {
            $this->whenRange->xmlSerialize(true, $sxe->addChild('whenRange'));
        }
        if (isset($this->rationale)) {
            $this->rationale->xmlSerialize(true, $sxe->addChild('rationale'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
