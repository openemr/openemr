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
 * A pharmaceutical product described in terms of its composition and dose form.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicinalProductPharmaceutical extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * An identifier for the pharmaceutical medicinal product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The administrable dose form, after necessary reconstitution.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $administrableDoseForm = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $unitOfPresentation = null;

    /**
     * Ingredient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $ingredient = [];

    /**
     * Accompanying device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $device = [];

    /**
     * Characteristics e.g. a products onset of action.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalCharacteristics[]
     */
    public $characteristics = [];

    /**
     * The path by which the pharmaceutical product is taken into or makes contact with the body.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalRouteOfAdministration[]
     */
    public $routeOfAdministration = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductPharmaceutical';

    /**
     * An identifier for the pharmaceutical medicinal product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * An identifier for the pharmaceutical medicinal product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The administrable dose form, after necessary reconstitution.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getAdministrableDoseForm()
    {
        return $this->administrableDoseForm;
    }

    /**
     * The administrable dose form, after necessary reconstitution.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $administrableDoseForm
     * @return $this
     */
    public function setAdministrableDoseForm($administrableDoseForm)
    {
        $this->administrableDoseForm = $administrableDoseForm;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getUnitOfPresentation()
    {
        return $this->unitOfPresentation;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $unitOfPresentation
     * @return $this
     */
    public function setUnitOfPresentation($unitOfPresentation)
    {
        $this->unitOfPresentation = $unitOfPresentation;
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
     * Accompanying device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Accompanying device.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $device
     * @return $this
     */
    public function addDevice($device)
    {
        $this->device[] = $device;
        return $this;
    }

    /**
     * Characteristics e.g. a products onset of action.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalCharacteristics[]
     */
    public function getCharacteristics()
    {
        return $this->characteristics;
    }

    /**
     * Characteristics e.g. a products onset of action.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalCharacteristics $characteristics
     * @return $this
     */
    public function addCharacteristics($characteristics)
    {
        $this->characteristics[] = $characteristics;
        return $this;
    }

    /**
     * The path by which the pharmaceutical product is taken into or makes contact with the body.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalRouteOfAdministration[]
     */
    public function getRouteOfAdministration()
    {
        return $this->routeOfAdministration;
    }

    /**
     * The path by which the pharmaceutical product is taken into or makes contact with the body.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalRouteOfAdministration $routeOfAdministration
     * @return $this
     */
    public function addRouteOfAdministration($routeOfAdministration)
    {
        $this->routeOfAdministration[] = $routeOfAdministration;
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
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, ' . gettype($data['identifier']) . ' seen.');
                }
            }
            if (isset($data['administrableDoseForm'])) {
                $this->setAdministrableDoseForm($data['administrableDoseForm']);
            }
            if (isset($data['unitOfPresentation'])) {
                $this->setUnitOfPresentation($data['unitOfPresentation']);
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
            if (isset($data['device'])) {
                if (is_array($data['device'])) {
                    foreach ($data['device'] as $d) {
                        $this->addDevice($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"device" must be array of objects or null, ' . gettype($data['device']) . ' seen.');
                }
            }
            if (isset($data['characteristics'])) {
                if (is_array($data['characteristics'])) {
                    foreach ($data['characteristics'] as $d) {
                        $this->addCharacteristics($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"characteristics" must be array of objects or null, ' . gettype($data['characteristics']) . ' seen.');
                }
            }
            if (isset($data['routeOfAdministration'])) {
                if (is_array($data['routeOfAdministration'])) {
                    foreach ($data['routeOfAdministration'] as $d) {
                        $this->addRouteOfAdministration($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"routeOfAdministration" must be array of objects or null, ' . gettype($data['routeOfAdministration']) . ' seen.');
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
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->administrableDoseForm)) {
            $json['administrableDoseForm'] = $this->administrableDoseForm;
        }
        if (isset($this->unitOfPresentation)) {
            $json['unitOfPresentation'] = $this->unitOfPresentation;
        }
        if (0 < count($this->ingredient)) {
            $json['ingredient'] = [];
            foreach ($this->ingredient as $ingredient) {
                $json['ingredient'][] = $ingredient;
            }
        }
        if (0 < count($this->device)) {
            $json['device'] = [];
            foreach ($this->device as $device) {
                $json['device'][] = $device;
            }
        }
        if (0 < count($this->characteristics)) {
            $json['characteristics'] = [];
            foreach ($this->characteristics as $characteristics) {
                $json['characteristics'][] = $characteristics;
            }
        }
        if (0 < count($this->routeOfAdministration)) {
            $json['routeOfAdministration'] = [];
            foreach ($this->routeOfAdministration as $routeOfAdministration) {
                $json['routeOfAdministration'][] = $routeOfAdministration;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductPharmaceutical xmlns="http://hl7.org/fhir"></MedicinalProductPharmaceutical>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->administrableDoseForm)) {
            $this->administrableDoseForm->xmlSerialize(true, $sxe->addChild('administrableDoseForm'));
        }
        if (isset($this->unitOfPresentation)) {
            $this->unitOfPresentation->xmlSerialize(true, $sxe->addChild('unitOfPresentation'));
        }
        if (0 < count($this->ingredient)) {
            foreach ($this->ingredient as $ingredient) {
                $ingredient->xmlSerialize(true, $sxe->addChild('ingredient'));
            }
        }
        if (0 < count($this->device)) {
            foreach ($this->device as $device) {
                $device->xmlSerialize(true, $sxe->addChild('device'));
            }
        }
        if (0 < count($this->characteristics)) {
            foreach ($this->characteristics as $characteristics) {
                $characteristics->xmlSerialize(true, $sxe->addChild('characteristics'));
            }
        }
        if (0 < count($this->routeOfAdministration)) {
            foreach ($this->routeOfAdministration as $routeOfAdministration) {
                $routeOfAdministration->xmlSerialize(true, $sxe->addChild('routeOfAdministration'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
