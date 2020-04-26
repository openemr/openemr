<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPackaged;

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
 * A medicinal product in a container or package.
 */
class FHIRMedicinalProductPackagedPackageItem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Including possibly Data Carrier Identifier.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The physical type of the container of the medicine.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The quantity of this package in the medicinal product, at the current level of packaging. The outermost is always 1.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * Material type of the package item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $material = [];

    /**
     * A possible alternate material for the packaging.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $alternateMaterial = [];

    /**
     * A device accompanying a medicinal product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $device = [];

    /**
     * The manufactured item as contained in the packaged medicinal product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $manufacturedItem = [];

    /**
     * Allows containers within containers.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem[]
     */
    public $packageItem = [];

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
     * Shelf Life and storage information.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRProductShelfLife[]
     */
    public $shelfLifeStorage = [];

    /**
     * Manufacturer of this Package Item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $manufacturer = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductPackaged.PackageItem';

    /**
     * Including possibly Data Carrier Identifier.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Including possibly Data Carrier Identifier.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The physical type of the container of the medicine.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The physical type of the container of the medicine.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The quantity of this package in the medicinal product, at the current level of packaging. The outermost is always 1.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The quantity of this package in the medicinal product, at the current level of packaging. The outermost is always 1.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Material type of the package item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Material type of the package item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $material
     * @return $this
     */
    public function addMaterial($material)
    {
        $this->material[] = $material;
        return $this;
    }

    /**
     * A possible alternate material for the packaging.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getAlternateMaterial()
    {
        return $this->alternateMaterial;
    }

    /**
     * A possible alternate material for the packaging.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $alternateMaterial
     * @return $this
     */
    public function addAlternateMaterial($alternateMaterial)
    {
        $this->alternateMaterial[] = $alternateMaterial;
        return $this;
    }

    /**
     * A device accompanying a medicinal product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * A device accompanying a medicinal product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $device
     * @return $this
     */
    public function addDevice($device)
    {
        $this->device[] = $device;
        return $this;
    }

    /**
     * The manufactured item as contained in the packaged medicinal product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getManufacturedItem()
    {
        return $this->manufacturedItem;
    }

    /**
     * The manufactured item as contained in the packaged medicinal product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $manufacturedItem
     * @return $this
     */
    public function addManufacturedItem($manufacturedItem)
    {
        $this->manufacturedItem[] = $manufacturedItem;
        return $this;
    }

    /**
     * Allows containers within containers.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem[]
     */
    public function getPackageItem()
    {
        return $this->packageItem;
    }

    /**
     * Allows containers within containers.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem $packageItem
     * @return $this
     */
    public function addPackageItem($packageItem)
    {
        $this->packageItem[] = $packageItem;
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
     * Shelf Life and storage information.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRProductShelfLife[]
     */
    public function getShelfLifeStorage()
    {
        return $this->shelfLifeStorage;
    }

    /**
     * Shelf Life and storage information.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRProductShelfLife $shelfLifeStorage
     * @return $this
     */
    public function addShelfLifeStorage($shelfLifeStorage)
    {
        $this->shelfLifeStorage[] = $shelfLifeStorage;
        return $this;
    }

    /**
     * Manufacturer of this Package Item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Manufacturer of this Package Item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $manufacturer
     * @return $this
     */
    public function addManufacturer($manufacturer)
    {
        $this->manufacturer[] = $manufacturer;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['material'])) {
                if (is_array($data['material'])) {
                    foreach ($data['material'] as $d) {
                        $this->addMaterial($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"material" must be array of objects or null, ' . gettype($data['material']) . ' seen.');
                }
            }
            if (isset($data['alternateMaterial'])) {
                if (is_array($data['alternateMaterial'])) {
                    foreach ($data['alternateMaterial'] as $d) {
                        $this->addAlternateMaterial($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"alternateMaterial" must be array of objects or null, ' . gettype($data['alternateMaterial']) . ' seen.');
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
            if (isset($data['manufacturedItem'])) {
                if (is_array($data['manufacturedItem'])) {
                    foreach ($data['manufacturedItem'] as $d) {
                        $this->addManufacturedItem($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"manufacturedItem" must be array of objects or null, ' . gettype($data['manufacturedItem']) . ' seen.');
                }
            }
            if (isset($data['packageItem'])) {
                if (is_array($data['packageItem'])) {
                    foreach ($data['packageItem'] as $d) {
                        $this->addPackageItem($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"packageItem" must be array of objects or null, ' . gettype($data['packageItem']) . ' seen.');
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
            if (isset($data['shelfLifeStorage'])) {
                if (is_array($data['shelfLifeStorage'])) {
                    foreach ($data['shelfLifeStorage'] as $d) {
                        $this->addShelfLifeStorage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"shelfLifeStorage" must be array of objects or null, ' . gettype($data['shelfLifeStorage']) . ' seen.');
                }
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (0 < count($this->material)) {
            $json['material'] = [];
            foreach ($this->material as $material) {
                $json['material'][] = $material;
            }
        }
        if (0 < count($this->alternateMaterial)) {
            $json['alternateMaterial'] = [];
            foreach ($this->alternateMaterial as $alternateMaterial) {
                $json['alternateMaterial'][] = $alternateMaterial;
            }
        }
        if (0 < count($this->device)) {
            $json['device'] = [];
            foreach ($this->device as $device) {
                $json['device'][] = $device;
            }
        }
        if (0 < count($this->manufacturedItem)) {
            $json['manufacturedItem'] = [];
            foreach ($this->manufacturedItem as $manufacturedItem) {
                $json['manufacturedItem'][] = $manufacturedItem;
            }
        }
        if (0 < count($this->packageItem)) {
            $json['packageItem'] = [];
            foreach ($this->packageItem as $packageItem) {
                $json['packageItem'][] = $packageItem;
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
        if (0 < count($this->shelfLifeStorage)) {
            $json['shelfLifeStorage'] = [];
            foreach ($this->shelfLifeStorage as $shelfLifeStorage) {
                $json['shelfLifeStorage'][] = $shelfLifeStorage;
            }
        }
        if (0 < count($this->manufacturer)) {
            $json['manufacturer'] = [];
            foreach ($this->manufacturer as $manufacturer) {
                $json['manufacturer'][] = $manufacturer;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductPackagedPackageItem xmlns="http://hl7.org/fhir"></MedicinalProductPackagedPackageItem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (0 < count($this->material)) {
            foreach ($this->material as $material) {
                $material->xmlSerialize(true, $sxe->addChild('material'));
            }
        }
        if (0 < count($this->alternateMaterial)) {
            foreach ($this->alternateMaterial as $alternateMaterial) {
                $alternateMaterial->xmlSerialize(true, $sxe->addChild('alternateMaterial'));
            }
        }
        if (0 < count($this->device)) {
            foreach ($this->device as $device) {
                $device->xmlSerialize(true, $sxe->addChild('device'));
            }
        }
        if (0 < count($this->manufacturedItem)) {
            foreach ($this->manufacturedItem as $manufacturedItem) {
                $manufacturedItem->xmlSerialize(true, $sxe->addChild('manufacturedItem'));
            }
        }
        if (0 < count($this->packageItem)) {
            foreach ($this->packageItem as $packageItem) {
                $packageItem->xmlSerialize(true, $sxe->addChild('packageItem'));
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
        if (0 < count($this->shelfLifeStorage)) {
            foreach ($this->shelfLifeStorage as $shelfLifeStorage) {
                $shelfLifeStorage->xmlSerialize(true, $sxe->addChild('shelfLifeStorage'));
            }
        }
        if (0 < count($this->manufacturer)) {
            foreach ($this->manufacturer as $manufacturer) {
                $manufacturer->xmlSerialize(true, $sxe->addChild('manufacturer'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
