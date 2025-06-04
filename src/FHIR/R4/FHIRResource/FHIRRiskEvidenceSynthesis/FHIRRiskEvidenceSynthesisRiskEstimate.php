<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRRiskEvidenceSynthesis;

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
 * The RiskEvidenceSynthesis resource describes the likelihood of an outcome in a population plus exposure state where the risk estimate is derived from a combination of research studies.
 */
class FHIRRiskEvidenceSynthesisRiskEstimate extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Human-readable summary of risk estimate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Examples include proportion and mean.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The point estimate of the risk estimate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $value = null;

    /**
     * Specifies the UCUM unit for the outcome.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $unitOfMeasure = null;

    /**
     * The sample size for the group that was measured for this risk estimate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $denominatorCount = null;

    /**
     * The number of group members with the outcome of interest.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $numeratorCount = null;

    /**
     * A description of the precision of the estimate for the effect.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisPrecisionEstimate[]
     */
    public $precisionEstimate = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'RiskEvidenceSynthesis.RiskEstimate';

    /**
     * Human-readable summary of risk estimate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Human-readable summary of risk estimate.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Examples include proportion and mean.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Examples include proportion and mean.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The point estimate of the risk estimate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The point estimate of the risk estimate.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Specifies the UCUM unit for the outcome.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getUnitOfMeasure()
    {
        return $this->unitOfMeasure;
    }

    /**
     * Specifies the UCUM unit for the outcome.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $unitOfMeasure
     * @return $this
     */
    public function setUnitOfMeasure($unitOfMeasure)
    {
        $this->unitOfMeasure = $unitOfMeasure;
        return $this;
    }

    /**
     * The sample size for the group that was measured for this risk estimate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getDenominatorCount()
    {
        return $this->denominatorCount;
    }

    /**
     * The sample size for the group that was measured for this risk estimate.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $denominatorCount
     * @return $this
     */
    public function setDenominatorCount($denominatorCount)
    {
        $this->denominatorCount = $denominatorCount;
        return $this;
    }

    /**
     * The number of group members with the outcome of interest.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getNumeratorCount()
    {
        return $this->numeratorCount;
    }

    /**
     * The number of group members with the outcome of interest.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $numeratorCount
     * @return $this
     */
    public function setNumeratorCount($numeratorCount)
    {
        $this->numeratorCount = $numeratorCount;
        return $this;
    }

    /**
     * A description of the precision of the estimate for the effect.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisPrecisionEstimate[]
     */
    public function getPrecisionEstimate()
    {
        return $this->precisionEstimate;
    }

    /**
     * A description of the precision of the estimate for the effect.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisPrecisionEstimate $precisionEstimate
     * @return $this
     */
    public function addPrecisionEstimate($precisionEstimate)
    {
        $this->precisionEstimate[] = $precisionEstimate;
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
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['value'])) {
                $this->setValue($data['value']);
            }
            if (isset($data['unitOfMeasure'])) {
                $this->setUnitOfMeasure($data['unitOfMeasure']);
            }
            if (isset($data['denominatorCount'])) {
                $this->setDenominatorCount($data['denominatorCount']);
            }
            if (isset($data['numeratorCount'])) {
                $this->setNumeratorCount($data['numeratorCount']);
            }
            if (isset($data['precisionEstimate'])) {
                if (is_array($data['precisionEstimate'])) {
                    foreach ($data['precisionEstimate'] as $d) {
                        $this->addPrecisionEstimate($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"precisionEstimate" must be array of objects or null, ' . gettype($data['precisionEstimate']) . ' seen.');
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
        return (string)$this->getValue();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->value)) {
            $json['value'] = $this->value;
        }
        if (isset($this->unitOfMeasure)) {
            $json['unitOfMeasure'] = $this->unitOfMeasure;
        }
        if (isset($this->denominatorCount)) {
            $json['denominatorCount'] = $this->denominatorCount;
        }
        if (isset($this->numeratorCount)) {
            $json['numeratorCount'] = $this->numeratorCount;
        }
        if (0 < count($this->precisionEstimate)) {
            $json['precisionEstimate'] = [];
            foreach ($this->precisionEstimate as $precisionEstimate) {
                $json['precisionEstimate'][] = $precisionEstimate;
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
            $sxe = new \SimpleXMLElement('<RiskEvidenceSynthesisRiskEstimate xmlns="http://hl7.org/fhir"></RiskEvidenceSynthesisRiskEstimate>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->value)) {
            $this->value->xmlSerialize(true, $sxe->addChild('value'));
        }
        if (isset($this->unitOfMeasure)) {
            $this->unitOfMeasure->xmlSerialize(true, $sxe->addChild('unitOfMeasure'));
        }
        if (isset($this->denominatorCount)) {
            $this->denominatorCount->xmlSerialize(true, $sxe->addChild('denominatorCount'));
        }
        if (isset($this->numeratorCount)) {
            $this->numeratorCount->xmlSerialize(true, $sxe->addChild('numeratorCount'));
        }
        if (0 < count($this->precisionEstimate)) {
            foreach ($this->precisionEstimate as $precisionEstimate) {
                $precisionEstimate->xmlSerialize(true, $sxe->addChild('precisionEstimate'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
