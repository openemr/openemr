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
 * An ingredient of a manufactured item or pharmaceutical product.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicinalProductIngredient extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * The identifier(s) of this Ingredient that are assigned by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * Ingredient role e.g. Active ingredient, excipient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $role = null;

    /**
     * If the ingredient is a known or suspected allergen.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $allergenicIndicator = null;

    /**
     * Manufacturer of this Ingredient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $manufacturer = [];

    /**
     * A specified substance that comprises this ingredient.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance[]
     */
    public $specifiedSubstance = [];

    /**
     * The ingredient substance.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSubstance
     */
    public $substance = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductIngredient';

    /**
     * The identifier(s) of this Ingredient that are assigned by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The identifier(s) of this Ingredient that are assigned by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Ingredient role e.g. Active ingredient, excipient.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Ingredient role e.g. Active ingredient, excipient.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * If the ingredient is a known or suspected allergen.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getAllergenicIndicator()
    {
        return $this->allergenicIndicator;
    }

    /**
     * If the ingredient is a known or suspected allergen.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $allergenicIndicator
     * @return $this
     */
    public function setAllergenicIndicator($allergenicIndicator)
    {
        $this->allergenicIndicator = $allergenicIndicator;
        return $this;
    }

    /**
     * Manufacturer of this Ingredient.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Manufacturer of this Ingredient.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $manufacturer
     * @return $this
     */
    public function addManufacturer($manufacturer)
    {
        $this->manufacturer[] = $manufacturer;
        return $this;
    }

    /**
     * A specified substance that comprises this ingredient.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance[]
     */
    public function getSpecifiedSubstance()
    {
        return $this->specifiedSubstance;
    }

    /**
     * A specified substance that comprises this ingredient.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSpecifiedSubstance $specifiedSubstance
     * @return $this
     */
    public function addSpecifiedSubstance($specifiedSubstance)
    {
        $this->specifiedSubstance[] = $specifiedSubstance;
        return $this;
    }

    /**
     * The ingredient substance.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSubstance
     */
    public function getSubstance()
    {
        return $this->substance;
    }

    /**
     * The ingredient substance.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientSubstance $substance
     * @return $this
     */
    public function setSubstance($substance)
    {
        $this->substance = $substance;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['role'])) {
                $this->setRole($data['role']);
            }
            if (isset($data['allergenicIndicator'])) {
                $this->setAllergenicIndicator($data['allergenicIndicator']);
            }
            if (isset($data['manufacturer'])) {
                if (is_array($data['manufacturer'])) {
                    foreach ($data['manufacturer'] as $d) {
                        $this->addManufacturer($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"manufacturer" must be array of objects or null, ' . gettype($data['manufacturer']) . ' seen.');
                }
            }
            if (isset($data['specifiedSubstance'])) {
                if (is_array($data['specifiedSubstance'])) {
                    foreach ($data['specifiedSubstance'] as $d) {
                        $this->addSpecifiedSubstance($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specifiedSubstance" must be array of objects or null, ' . gettype($data['specifiedSubstance']) . ' seen.');
                }
            }
            if (isset($data['substance'])) {
                $this->setSubstance($data['substance']);
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->role)) {
            $json['role'] = $this->role;
        }
        if (isset($this->allergenicIndicator)) {
            $json['allergenicIndicator'] = $this->allergenicIndicator;
        }
        if (0 < count($this->manufacturer)) {
            $json['manufacturer'] = [];
            foreach ($this->manufacturer as $manufacturer) {
                $json['manufacturer'][] = $manufacturer;
            }
        }
        if (0 < count($this->specifiedSubstance)) {
            $json['specifiedSubstance'] = [];
            foreach ($this->specifiedSubstance as $specifiedSubstance) {
                $json['specifiedSubstance'][] = $specifiedSubstance;
            }
        }
        if (isset($this->substance)) {
            $json['substance'] = $this->substance;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductIngredient xmlns="http://hl7.org/fhir"></MedicinalProductIngredient>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->role)) {
            $this->role->xmlSerialize(true, $sxe->addChild('role'));
        }
        if (isset($this->allergenicIndicator)) {
            $this->allergenicIndicator->xmlSerialize(true, $sxe->addChild('allergenicIndicator'));
        }
        if (0 < count($this->manufacturer)) {
            foreach ($this->manufacturer as $manufacturer) {
                $manufacturer->xmlSerialize(true, $sxe->addChild('manufacturer'));
            }
        }
        if (0 < count($this->specifiedSubstance)) {
            foreach ($this->specifiedSubstance as $specifiedSubstance) {
                $specifiedSubstance->xmlSerialize(true, $sxe->addChild('specifiedSubstance'));
            }
        }
        if (isset($this->substance)) {
            $this->substance->xmlSerialize(true, $sxe->addChild('substance'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
