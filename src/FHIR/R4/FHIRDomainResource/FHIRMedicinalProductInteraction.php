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
 * The interactions of the medicinal product with other medicinal products, or other forms of interactions.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicinalProductInteraction extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * The medication for which this is a described interaction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $subject = [];

    /**
     * The interaction described.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * The specific medication, food or laboratory test that interacts.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductInteraction\FHIRMedicinalProductInteractionInteractant[]
     */
    public $interactant = [];

    /**
     * The type of the interaction e.g. drug-drug interaction, drug-food interaction, drug-lab test interaction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The effect of the interaction, for example "reduced gastric absorption of primary medication".
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $effect = null;

    /**
     * The incidence of the interaction, e.g. theoretical, observed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $incidence = null;

    /**
     * Actions for managing the interaction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $management = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductInteraction';

    /**
     * The medication for which this is a described interaction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The medication for which this is a described interaction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function addSubject($subject)
    {
        $this->subject[] = $subject;
        return $this;
    }

    /**
     * The interaction described.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * The interaction described.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The specific medication, food or laboratory test that interacts.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductInteraction\FHIRMedicinalProductInteractionInteractant[]
     */
    public function getInteractant()
    {
        return $this->interactant;
    }

    /**
     * The specific medication, food or laboratory test that interacts.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductInteraction\FHIRMedicinalProductInteractionInteractant $interactant
     * @return $this
     */
    public function addInteractant($interactant)
    {
        $this->interactant[] = $interactant;
        return $this;
    }

    /**
     * The type of the interaction e.g. drug-drug interaction, drug-food interaction, drug-lab test interaction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of the interaction e.g. drug-drug interaction, drug-food interaction, drug-lab test interaction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The effect of the interaction, for example "reduced gastric absorption of primary medication".
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getEffect()
    {
        return $this->effect;
    }

    /**
     * The effect of the interaction, for example "reduced gastric absorption of primary medication".
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $effect
     * @return $this
     */
    public function setEffect($effect)
    {
        $this->effect = $effect;
        return $this;
    }

    /**
     * The incidence of the interaction, e.g. theoretical, observed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getIncidence()
    {
        return $this->incidence;
    }

    /**
     * The incidence of the interaction, e.g. theoretical, observed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $incidence
     * @return $this
     */
    public function setIncidence($incidence)
    {
        $this->incidence = $incidence;
        return $this;
    }

    /**
     * Actions for managing the interaction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getManagement()
    {
        return $this->management;
    }

    /**
     * Actions for managing the interaction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $management
     * @return $this
     */
    public function setManagement($management)
    {
        $this->management = $management;
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
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['interactant'])) {
                if (is_array($data['interactant'])) {
                    foreach ($data['interactant'] as $d) {
                        $this->addInteractant($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"interactant" must be array of objects or null, ' . gettype($data['interactant']) . ' seen.');
                }
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['effect'])) {
                $this->setEffect($data['effect']);
            }
            if (isset($data['incidence'])) {
                $this->setIncidence($data['incidence']);
            }
            if (isset($data['management'])) {
                $this->setManagement($data['management']);
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
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->subject)) {
            $json['subject'] = [];
            foreach ($this->subject as $subject) {
                $json['subject'][] = $subject;
            }
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->interactant)) {
            $json['interactant'] = [];
            foreach ($this->interactant as $interactant) {
                $json['interactant'][] = $interactant;
            }
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->effect)) {
            $json['effect'] = $this->effect;
        }
        if (isset($this->incidence)) {
            $json['incidence'] = $this->incidence;
        }
        if (isset($this->management)) {
            $json['management'] = $this->management;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductInteraction xmlns="http://hl7.org/fhir"></MedicinalProductInteraction>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->subject)) {
            foreach ($this->subject as $subject) {
                $subject->xmlSerialize(true, $sxe->addChild('subject'));
            }
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->interactant)) {
            foreach ($this->interactant as $interactant) {
                $interactant->xmlSerialize(true, $sxe->addChild('interactant'));
            }
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->effect)) {
            $this->effect->xmlSerialize(true, $sxe->addChild('effect'));
        }
        if (isset($this->incidence)) {
            $this->incidence->xmlSerialize(true, $sxe->addChild('incidence'));
        }
        if (isset($this->management)) {
            $this->management->xmlSerialize(true, $sxe->addChild('management'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
