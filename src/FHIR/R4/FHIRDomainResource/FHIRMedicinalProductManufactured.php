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
 * The manufactured item as contained in the packaged medicinal product.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicinalProductManufactured extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Dose form as manufactured and before any transformation into the pharmaceutical product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $manufacturedDoseForm = null;

    /**
     * The “real world” units in which the quantity of the manufactured item is described.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $unitOfPresentation = null;

    /**
     * The quantity or "count number" of the manufactured item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * Manufacturer of the item (Note that this should be named "manufacturer" but it currently causes technical issues).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $manufacturer = [];

    /**
     * Ingredient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $ingredient = [];

    /**
     * Dimensions, color etc.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRProdCharacteristic
     */
    public $physicalCharacteristics = null;

    /**
     * Other codeable characteristics.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $otherCharacteristics = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductManufactured';

    /**
     * Dose form as manufactured and before any transformation into the pharmaceutical product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getManufacturedDoseForm()
    {
        return $this->manufacturedDoseForm;
    }

    /**
     * Dose form as manufactured and before any transformation into the pharmaceutical product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $manufacturedDoseForm
     * @return $this
     */
    public function setManufacturedDoseForm($manufacturedDoseForm)
    {
        $this->manufacturedDoseForm = $manufacturedDoseForm;
        return $this;
    }

    /**
     * The “real world” units in which the quantity of the manufactured item is described.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getUnitOfPresentation()
    {
        return $this->unitOfPresentation;
    }

    /**
     * The “real world” units in which the quantity of the manufactured item is described.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $unitOfPresentation
     * @return $this
     */
    public function setUnitOfPresentation($unitOfPresentation)
    {
        $this->unitOfPresentation = $unitOfPresentation;
        return $this;
    }

    /**
     * The quantity or "count number" of the manufactured item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The quantity or "count number" of the manufactured item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Manufacturer of the item (Note that this should be named "manufacturer" but it currently causes technical issues).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Manufacturer of the item (Note that this should be named "manufacturer" but it currently causes technical issues).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $manufacturer
     * @return $this
     */
    public function addManufacturer($manufacturer)
    {
        $this->manufacturer[] = $manufacturer;
        return $this;
    }

    /**
     * Ingredient.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getIngredient()
    {
        return $this->ingredient;
    }

    /**
     * Ingredient.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $ingredient
     * @return $this
     */
    public function addIngredient($ingredient)
    {
        $this->ingredient[] = $ingredient;
        return $this;
    }

    /**
     * Dimensions, color etc.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRProdCharacteristic
     */
    public function getPhysicalCharacteristics()
    {
        return $this->physicalCharacteristics;
    }

    /**
     * Dimensions, color etc.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRProdCharacteristic $physicalCharacteristics
     * @return $this
     */
    public function setPhysicalCharacteristics($physicalCharacteristics)
    {
        $this->physicalCharacteristics = $physicalCharacteristics;
        return $this;
    }

    /**
     * Other codeable characteristics.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getOtherCharacteristics()
    {
        return $this->otherCharacteristics;
    }

    /**
     * Other codeable characteristics.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $otherCharacteristics
     * @return $this
     */
    public function addOtherCharacteristics($otherCharacteristics)
    {
        $this->otherCharacteristics[] = $otherCharacteristics;
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
            if (isset($data['manufacturedDoseForm'])) {
                $this->setManufacturedDoseForm($data['manufacturedDoseForm']);
            }
            if (isset($data['unitOfPresentation'])) {
                $this->setUnitOfPresentation($data['unitOfPresentation']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
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
            if (isset($data['ingredient'])) {
                if (is_array($data['ingredient'])) {
                    foreach ($data['ingredient'] as $d) {
                        $this->addIngredient($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"ingredient" must be array of objects or null, ' . gettype($data['ingredient']) . ' seen.');
                }
            }
            if (isset($data['physicalCharacteristics'])) {
                $this->setPhysicalCharacteristics($data['physicalCharacteristics']);
            }
            if (isset($data['otherCharacteristics'])) {
                if (is_array($data['otherCharacteristics'])) {
                    foreach ($data['otherCharacteristics'] as $d) {
                        $this->addOtherCharacteristics($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"otherCharacteristics" must be array of objects or null, ' . gettype($data['otherCharacteristics']) . ' seen.');
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
        if (isset($this->manufacturedDoseForm)) {
            $json['manufacturedDoseForm'] = $this->manufacturedDoseForm;
        }
        if (isset($this->unitOfPresentation)) {
            $json['unitOfPresentation'] = $this->unitOfPresentation;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (0 < count($this->manufacturer)) {
            $json['manufacturer'] = [];
            foreach ($this->manufacturer as $manufacturer) {
                $json['manufacturer'][] = $manufacturer;
            }
        }
        if (0 < count($this->ingredient)) {
            $json['ingredient'] = [];
            foreach ($this->ingredient as $ingredient) {
                $json['ingredient'][] = $ingredient;
            }
        }
        if (isset($this->physicalCharacteristics)) {
            $json['physicalCharacteristics'] = $this->physicalCharacteristics;
        }
        if (0 < count($this->otherCharacteristics)) {
            $json['otherCharacteristics'] = [];
            foreach ($this->otherCharacteristics as $otherCharacteristics) {
                $json['otherCharacteristics'][] = $otherCharacteristics;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductManufactured xmlns="http://hl7.org/fhir"></MedicinalProductManufactured>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->manufacturedDoseForm)) {
            $this->manufacturedDoseForm->xmlSerialize(true, $sxe->addChild('manufacturedDoseForm'));
        }
        if (isset($this->unitOfPresentation)) {
            $this->unitOfPresentation->xmlSerialize(true, $sxe->addChild('unitOfPresentation'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (0 < count($this->manufacturer)) {
            foreach ($this->manufacturer as $manufacturer) {
                $manufacturer->xmlSerialize(true, $sxe->addChild('manufacturer'));
            }
        }
        if (0 < count($this->ingredient)) {
            foreach ($this->ingredient as $ingredient) {
                $ingredient->xmlSerialize(true, $sxe->addChild('ingredient'));
            }
        }
        if (isset($this->physicalCharacteristics)) {
            $this->physicalCharacteristics->xmlSerialize(true, $sxe->addChild('physicalCharacteristics'));
        }
        if (0 < count($this->otherCharacteristics)) {
            foreach ($this->otherCharacteristics as $otherCharacteristics) {
                $otherCharacteristics->xmlSerialize(true, $sxe->addChild('otherCharacteristics'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
