<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIREffectEvidenceSynthesis;

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
 * The EffectEvidenceSynthesis resource describes the difference in an outcome between exposures states in a population where the effect estimate is derived from a combination of research studies.
 */
class FHIREffectEvidenceSynthesisEffectEstimate extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Human-readable summary of effect estimate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Examples include relative risk and mean difference.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Used to define variant exposure states such as low-risk state.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $variantState = null;

    /**
     * The point estimate of the effect estimate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $value = null;

    /**
     * Specifies the UCUM unit for the outcome.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $unitOfMeasure = null;

    /**
     * A description of the precision of the estimate for the effect.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisPrecisionEstimate[]
     */
    public $precisionEstimate = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'EffectEvidenceSynthesis.EffectEstimate';

    /**
     * Human-readable summary of effect estimate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Human-readable summary of effect estimate.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Examples include relative risk and mean difference.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Examples include relative risk and mean difference.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Used to define variant exposure states such as low-risk state.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getVariantState()
    {
        return $this->variantState;
    }

    /**
     * Used to define variant exposure states such as low-risk state.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $variantState
     * @return $this
     */
    public function setVariantState($variantState)
    {
        $this->variantState = $variantState;
        return $this;
    }

    /**
     * The point estimate of the effect estimate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The point estimate of the effect estimate.
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
     * A description of the precision of the estimate for the effect.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisPrecisionEstimate[]
     */
    public function getPrecisionEstimate()
    {
        return $this->precisionEstimate;
    }

    /**
     * A description of the precision of the estimate for the effect.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisPrecisionEstimate $precisionEstimate
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
            if (isset($data['variantState'])) {
                $this->setVariantState($data['variantState']);
            }
            if (isset($data['value'])) {
                $this->setValue($data['value']);
            }
            if (isset($data['unitOfMeasure'])) {
                $this->setUnitOfMeasure($data['unitOfMeasure']);
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
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->variantState)) {
            $json['variantState'] = $this->variantState;
        }
        if (isset($this->value)) {
            $json['value'] = $this->value;
        }
        if (isset($this->unitOfMeasure)) {
            $json['unitOfMeasure'] = $this->unitOfMeasure;
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
            $sxe = new \SimpleXMLElement('<EffectEvidenceSynthesisEffectEstimate xmlns="http://hl7.org/fhir"></EffectEvidenceSynthesisEffectEstimate>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->variantState)) {
            $this->variantState->xmlSerialize(true, $sxe->addChild('variantState'));
        }
        if (isset($this->value)) {
            $this->value->xmlSerialize(true, $sxe->addChild('value'));
        }
        if (isset($this->unitOfMeasure)) {
            $this->unitOfMeasure->xmlSerialize(true, $sxe->addChild('unitOfMeasure'));
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
