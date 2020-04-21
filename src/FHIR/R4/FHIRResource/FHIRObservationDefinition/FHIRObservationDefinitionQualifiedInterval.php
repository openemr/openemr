<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRObservationDefinition;

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
 * Set of definitional characteristics for a kind of observation or measurement produced or consumed by an orderable health care service.
 */
class FHIRObservationDefinitionQualifiedInterval extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The category of interval of values for continuous or ordinal observations conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationRangeCategory
     */
    public $category = null;

    /**
     * The low and high values determining the interval. There may be only one of the two.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $range = null;

    /**
     * Codes to indicate the health context the range applies to. For example, the normal or therapeutic range.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $context = null;

    /**
     * Codes to indicate the target population this reference range applies to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $appliesTo = [];

    /**
     * Sex of the population the range applies to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender
     */
    public $gender = null;

    /**
     * The age at which this reference range is applicable. This is a neonatal age (e.g. number of weeks at term) if the meaning says so.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $age = null;

    /**
     * The gestational age to which this reference range is applicable, in the context of pregnancy.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $gestationalAge = null;

    /**
     * Text based condition for which the reference range is valid.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $condition = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ObservationDefinition.QualifiedInterval';

    /**
     * The category of interval of values for continuous or ordinal observations conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationRangeCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * The category of interval of values for continuous or ordinal observations conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationRangeCategory $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * The low and high values determining the interval. There may be only one of the two.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * The low and high values determining the interval. There may be only one of the two.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $range
     * @return $this
     */
    public function setRange($range)
    {
        $this->range = $range;
        return $this;
    }

    /**
     * Codes to indicate the health context the range applies to. For example, the normal or therapeutic range.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Codes to indicate the health context the range applies to. For example, the normal or therapeutic range.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Codes to indicate the target population this reference range applies to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getAppliesTo()
    {
        return $this->appliesTo;
    }

    /**
     * Codes to indicate the target population this reference range applies to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $appliesTo
     * @return $this
     */
    public function addAppliesTo($appliesTo)
    {
        $this->appliesTo[] = $appliesTo;
        return $this;
    }

    /**
     * Sex of the population the range applies to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Sex of the population the range applies to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender $gender
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * The age at which this reference range is applicable. This is a neonatal age (e.g. number of weeks at term) if the meaning says so.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * The age at which this reference range is applicable. This is a neonatal age (e.g. number of weeks at term) if the meaning says so.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $age
     * @return $this
     */
    public function setAge($age)
    {
        $this->age = $age;
        return $this;
    }

    /**
     * The gestational age to which this reference range is applicable, in the context of pregnancy.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getGestationalAge()
    {
        return $this->gestationalAge;
    }

    /**
     * The gestational age to which this reference range is applicable, in the context of pregnancy.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $gestationalAge
     * @return $this
     */
    public function setGestationalAge($gestationalAge)
    {
        $this->gestationalAge = $gestationalAge;
        return $this;
    }

    /**
     * Text based condition for which the reference range is valid.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Text based condition for which the reference range is valid.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $condition
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
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
            if (isset($data['category'])) {
                $this->setCategory($data['category']);
            }
            if (isset($data['range'])) {
                $this->setRange($data['range']);
            }
            if (isset($data['context'])) {
                $this->setContext($data['context']);
            }
            if (isset($data['appliesTo'])) {
                if (is_array($data['appliesTo'])) {
                    foreach ($data['appliesTo'] as $d) {
                        $this->addAppliesTo($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"appliesTo" must be array of objects or null, ' . gettype($data['appliesTo']) . ' seen.');
                }
            }
            if (isset($data['gender'])) {
                $this->setGender($data['gender']);
            }
            if (isset($data['age'])) {
                $this->setAge($data['age']);
            }
            if (isset($data['gestationalAge'])) {
                $this->setGestationalAge($data['gestationalAge']);
            }
            if (isset($data['condition'])) {
                $this->setCondition($data['condition']);
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
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        if (isset($this->range)) {
            $json['range'] = $this->range;
        }
        if (isset($this->context)) {
            $json['context'] = $this->context;
        }
        if (0 < count($this->appliesTo)) {
            $json['appliesTo'] = [];
            foreach ($this->appliesTo as $appliesTo) {
                $json['appliesTo'][] = $appliesTo;
            }
        }
        if (isset($this->gender)) {
            $json['gender'] = $this->gender;
        }
        if (isset($this->age)) {
            $json['age'] = $this->age;
        }
        if (isset($this->gestationalAge)) {
            $json['gestationalAge'] = $this->gestationalAge;
        }
        if (isset($this->condition)) {
            $json['condition'] = $this->condition;
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
            $sxe = new \SimpleXMLElement('<ObservationDefinitionQualifiedInterval xmlns="http://hl7.org/fhir"></ObservationDefinitionQualifiedInterval>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->category)) {
            $this->category->xmlSerialize(true, $sxe->addChild('category'));
        }
        if (isset($this->range)) {
            $this->range->xmlSerialize(true, $sxe->addChild('range'));
        }
        if (isset($this->context)) {
            $this->context->xmlSerialize(true, $sxe->addChild('context'));
        }
        if (0 < count($this->appliesTo)) {
            foreach ($this->appliesTo as $appliesTo) {
                $appliesTo->xmlSerialize(true, $sxe->addChild('appliesTo'));
            }
        }
        if (isset($this->gender)) {
            $this->gender->xmlSerialize(true, $sxe->addChild('gender'));
        }
        if (isset($this->age)) {
            $this->age->xmlSerialize(true, $sxe->addChild('age'));
        }
        if (isset($this->gestationalAge)) {
            $this->gestationalAge->xmlSerialize(true, $sxe->addChild('gestationalAge'));
        }
        if (isset($this->condition)) {
            $this->condition->xmlSerialize(true, $sxe->addChild('condition'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
