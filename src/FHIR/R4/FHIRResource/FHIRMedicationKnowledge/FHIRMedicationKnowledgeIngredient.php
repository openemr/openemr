<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge;

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
 * Information about a medication that is used to support knowledge.
 */
class FHIRMedicationKnowledgeIngredient extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $itemCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $itemReference = null;

    /**
     * Indication of whether this ingredient affects the therapeutic action of the drug.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $isActive = null;

    /**
     * Specifies how many (or how much) of the items there are in this Medication.  For example, 250 mg per tablet.  This is expressed as a ratio where the numerator is 250mg and the denominator is 1 tablet.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public $strength = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationKnowledge.Ingredient';

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getItemCodeableConcept()
    {
        return $this->itemCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $itemCodeableConcept
     * @return $this
     */
    public function setItemCodeableConcept($itemCodeableConcept)
    {
        $this->itemCodeableConcept = $itemCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getItemReference()
    {
        return $this->itemReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $itemReference
     * @return $this
     */
    public function setItemReference($itemReference)
    {
        $this->itemReference = $itemReference;
        return $this;
    }

    /**
     * Indication of whether this ingredient affects the therapeutic action of the drug.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Indication of whether this ingredient affects the therapeutic action of the drug.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Specifies how many (or how much) of the items there are in this Medication.  For example, 250 mg per tablet.  This is expressed as a ratio where the numerator is 250mg and the denominator is 1 tablet.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getStrength()
    {
        return $this->strength;
    }

    /**
     * Specifies how many (or how much) of the items there are in this Medication.  For example, 250 mg per tablet.  This is expressed as a ratio where the numerator is 250mg and the denominator is 1 tablet.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $strength
     * @return $this
     */
    public function setStrength($strength)
    {
        $this->strength = $strength;
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
            if (isset($data['itemCodeableConcept'])) {
                $this->setItemCodeableConcept($data['itemCodeableConcept']);
            }
            if (isset($data['itemReference'])) {
                $this->setItemReference($data['itemReference']);
            }
            if (isset($data['isActive'])) {
                $this->setIsActive($data['isActive']);
            }
            if (isset($data['strength'])) {
                $this->setStrength($data['strength']);
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
        if (isset($this->itemCodeableConcept)) {
            $json['itemCodeableConcept'] = $this->itemCodeableConcept;
        }
        if (isset($this->itemReference)) {
            $json['itemReference'] = $this->itemReference;
        }
        if (isset($this->isActive)) {
            $json['isActive'] = $this->isActive;
        }
        if (isset($this->strength)) {
            $json['strength'] = $this->strength;
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
            $sxe = new \SimpleXMLElement('<MedicationKnowledgeIngredient xmlns="http://hl7.org/fhir"></MedicationKnowledgeIngredient>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->itemCodeableConcept)) {
            $this->itemCodeableConcept->xmlSerialize(true, $sxe->addChild('itemCodeableConcept'));
        }
        if (isset($this->itemReference)) {
            $this->itemReference->xmlSerialize(true, $sxe->addChild('itemReference'));
        }
        if (isset($this->isActive)) {
            $this->isActive->xmlSerialize(true, $sxe->addChild('isActive'));
        }
        if (isset($this->strength)) {
            $this->strength->xmlSerialize(true, $sxe->addChild('strength'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
