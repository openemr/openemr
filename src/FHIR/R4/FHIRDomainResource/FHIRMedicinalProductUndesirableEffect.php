<?php

namespace OpenEMR\FHIR\R4\FHIRDomainResource;

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

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/**
 * Describe the undesirable effects of the medicinal product.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicinalProductUndesirableEffect extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * The medication for which this is an indication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $subject = [];

    /**
     * The symptom, condition or undesirable effect.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $symptomConditionEffect = null;

    /**
     * Classification of the effect.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $classification = null;

    /**
     * The frequency of occurrence of the effect.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $frequencyOfOccurrence = null;

    /**
     * The population group to which this applies.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRPopulation[]
     */
    public $population = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductUndesirableEffect';

    /**
     * The medication for which this is an indication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The medication for which this is an indication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function addSubject($subject)
    {
        $this->subject[] = $subject;
        return $this;
    }

    /**
     * The symptom, condition or undesirable effect.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSymptomConditionEffect()
    {
        return $this->symptomConditionEffect;
    }

    /**
     * The symptom, condition or undesirable effect.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $symptomConditionEffect
     * @return $this
     */
    public function setSymptomConditionEffect($symptomConditionEffect)
    {
        $this->symptomConditionEffect = $symptomConditionEffect;
        return $this;
    }

    /**
     * Classification of the effect.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * Classification of the effect.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $classification
     * @return $this
     */
    public function setClassification($classification)
    {
        $this->classification = $classification;
        return $this;
    }

    /**
     * The frequency of occurrence of the effect.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getFrequencyOfOccurrence()
    {
        return $this->frequencyOfOccurrence;
    }

    /**
     * The frequency of occurrence of the effect.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $frequencyOfOccurrence
     * @return $this
     */
    public function setFrequencyOfOccurrence($frequencyOfOccurrence)
    {
        $this->frequencyOfOccurrence = $frequencyOfOccurrence;
        return $this;
    }

    /**
     * The population group to which this applies.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRPopulation[]
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * The population group to which this applies.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRPopulation $population
     * @return $this
     */
    public function addPopulation($population)
    {
        $this->population[] = $population;
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
            if (isset($data['subject'])) {
                if (is_array($data['subject'])) {
                    foreach ($data['subject'] as $d) {
                        $this->addSubject($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subject" must be array of objects or null, ' . gettype($data['subject']) . ' seen.');
                }
            }
            if (isset($data['symptomConditionEffect'])) {
                $this->setSymptomConditionEffect($data['symptomConditionEffect']);
            }
            if (isset($data['classification'])) {
                $this->setClassification($data['classification']);
            }
            if (isset($data['frequencyOfOccurrence'])) {
                $this->setFrequencyOfOccurrence($data['frequencyOfOccurrence']);
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
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->subject)) {
            $json['subject'] = [];
            foreach ($this->subject as $subject) {
                $json['subject'][] = $subject;
            }
        }
        if (isset($this->symptomConditionEffect)) {
            $json['symptomConditionEffect'] = $this->symptomConditionEffect;
        }
        if (isset($this->classification)) {
            $json['classification'] = $this->classification;
        }
        if (isset($this->frequencyOfOccurrence)) {
            $json['frequencyOfOccurrence'] = $this->frequencyOfOccurrence;
        }
        if (0 < count($this->population)) {
            $json['population'] = [];
            foreach ($this->population as $population) {
                $json['population'][] = $population;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductUndesirableEffect xmlns="http://hl7.org/fhir"></MedicinalProductUndesirableEffect>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->subject)) {
            foreach ($this->subject as $subject) {
                $subject->xmlSerialize(true, $sxe->addChild('subject'));
            }
        }
        if (isset($this->symptomConditionEffect)) {
            $this->symptomConditionEffect->xmlSerialize(true, $sxe->addChild('symptomConditionEffect'));
        }
        if (isset($this->classification)) {
            $this->classification->xmlSerialize(true, $sxe->addChild('classification'));
        }
        if (isset($this->frequencyOfOccurrence)) {
            $this->frequencyOfOccurrence->xmlSerialize(true, $sxe->addChild('frequencyOfOccurrence'));
        }
        if (0 < count($this->population)) {
            foreach ($this->population as $population) {
                $population->xmlSerialize(true, $sxe->addChild('population'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
