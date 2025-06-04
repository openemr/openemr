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
 * The characteristics, operational status and capabilities of a medical-related component of a medical device.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRDeviceDefinition extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Unique instance identifiers assigned to a device by the software, manufacturers, other organizations or owners. For example: handle ID.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Unique device identifier (UDI) assigned to device label or package.  Note that the Device may include multiple udiCarriers as it either may include just the udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it could have been sold.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier[]
     */
    public $udiDeviceIdentifier = [];

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $manufacturerString = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $manufacturerReference = null;

    /**
     * A name given to the device to identify it.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName[]
     */
    public $deviceName = [];

    /**
     * The model number for the device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $modelNumber = null;

    /**
     * What kind of device or device system this is.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The capabilities supported on a  device, the standards to which the device conforms for a particular purpose, and used for the communication.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization[]
     */
    public $specialization = [];

    /**
     * The available versions of the device, e.g., software versions.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $version = [];

    /**
     * Safety characteristics of the device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $safety = [];

    /**
     * Shelf Life and storage information.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRProductShelfLife[]
     */
    public $shelfLifeStorage = [];

    /**
     * Dimensions, color etc.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRProdCharacteristic
     */
    public $physicalCharacteristics = null;

    /**
     * Language code for the human-readable text strings produced by the device (all supported).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $languageCode = [];

    /**
     * Device capabilities.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability[]
     */
    public $capability = [];

    /**
     * The actual configuration settings of a device as it actually operates, e.g., regulation status, time properties.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty[]
     */
    public $property = [];

    /**
     * An organization that is responsible for the provision and ongoing maintenance of the device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $owner = null;

    /**
     * Contact details for an organization or a particular human that is responsible for the device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public $contact = [];

    /**
     * A network address on which the device may be contacted directly.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * Access to on-line information about the device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $onlineInformation = null;

    /**
     * Descriptive information, usage information or implantation information that is not captured in an existing element.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * The quantity of the device present in the packaging (e.g. the number of devices present in a pack, or the number of devices in the same package of the medicinal product).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * The parent device it can be part of.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $parentDevice = null;

    /**
     * A substance used to create the material(s) of which the device is made.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial[]
     */
    public $material = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'DeviceDefinition';

    /**
     * Unique instance identifiers assigned to a device by the software, manufacturers, other organizations or owners. For example: handle ID.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique instance identifiers assigned to a device by the software, manufacturers, other organizations or owners. For example: handle ID.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Unique device identifier (UDI) assigned to device label or package.  Note that the Device may include multiple udiCarriers as it either may include just the udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it could have been sold.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier[]
     */
    public function getUdiDeviceIdentifier()
    {
        return $this->udiDeviceIdentifier;
    }

    /**
     * Unique device identifier (UDI) assigned to device label or package.  Note that the Device may include multiple udiCarriers as it either may include just the udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it could have been sold.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier $udiDeviceIdentifier
     * @return $this
     */
    public function addUdiDeviceIdentifier($udiDeviceIdentifier)
    {
        $this->udiDeviceIdentifier[] = $udiDeviceIdentifier;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getManufacturerString()
    {
        return $this->manufacturerString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $manufacturerString
     * @return $this
     */
    public function setManufacturerString($manufacturerString)
    {
        $this->manufacturerString = $manufacturerString;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getManufacturerReference()
    {
        return $this->manufacturerReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $manufacturerReference
     * @return $this
     */
    public function setManufacturerReference($manufacturerReference)
    {
        $this->manufacturerReference = $manufacturerReference;
        return $this;
    }

    /**
     * A name given to the device to identify it.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName[]
     */
    public function getDeviceName()
    {
        return $this->deviceName;
    }

    /**
     * A name given to the device to identify it.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName $deviceName
     * @return $this
     */
    public function addDeviceName($deviceName)
    {
        $this->deviceName[] = $deviceName;
        return $this;
    }

    /**
     * The model number for the device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getModelNumber()
    {
        return $this->modelNumber;
    }

    /**
     * The model number for the device.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $modelNumber
     * @return $this
     */
    public function setModelNumber($modelNumber)
    {
        $this->modelNumber = $modelNumber;
        return $this;
    }

    /**
     * What kind of device or device system this is.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * What kind of device or device system this is.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The capabilities supported on a  device, the standards to which the device conforms for a particular purpose, and used for the communication.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization[]
     */
    public function getSpecialization()
    {
        return $this->specialization;
    }

    /**
     * The capabilities supported on a  device, the standards to which the device conforms for a particular purpose, and used for the communication.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization $specialization
     * @return $this
     */
    public function addSpecialization($specialization)
    {
        $this->specialization[] = $specialization;
        return $this;
    }

    /**
     * The available versions of the device, e.g., software versions.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The available versions of the device, e.g., software versions.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $version
     * @return $this
     */
    public function addVersion($version)
    {
        $this->version[] = $version;
        return $this;
    }

    /**
     * Safety characteristics of the device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSafety()
    {
        return $this->safety;
    }

    /**
     * Safety characteristics of the device.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $safety
     * @return $this
     */
    public function addSafety($safety)
    {
        $this->safety[] = $safety;
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
     * Language code for the human-readable text strings produced by the device (all supported).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * Language code for the human-readable text strings produced by the device (all supported).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $languageCode
     * @return $this
     */
    public function addLanguageCode($languageCode)
    {
        $this->languageCode[] = $languageCode;
        return $this;
    }

    /**
     * Device capabilities.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability[]
     */
    public function getCapability()
    {
        return $this->capability;
    }

    /**
     * Device capabilities.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability $capability
     * @return $this
     */
    public function addCapability($capability)
    {
        $this->capability[] = $capability;
        return $this;
    }

    /**
     * The actual configuration settings of a device as it actually operates, e.g., regulation status, time properties.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty[]
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * The actual configuration settings of a device as it actually operates, e.g., regulation status, time properties.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty $property
     * @return $this
     */
    public function addProperty($property)
    {
        $this->property[] = $property;
        return $this;
    }

    /**
     * An organization that is responsible for the provision and ongoing maintenance of the device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * An organization that is responsible for the provision and ongoing maintenance of the device.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $owner
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Contact details for an organization or a particular human that is responsible for the device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Contact details for an organization or a particular human that is responsible for the device.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint $contact
     * @return $this
     */
    public function addContact($contact)
    {
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * A network address on which the device may be contacted directly.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * A network address on which the device may be contacted directly.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Access to on-line information about the device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getOnlineInformation()
    {
        return $this->onlineInformation;
    }

    /**
     * Access to on-line information about the device.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $onlineInformation
     * @return $this
     */
    public function setOnlineInformation($onlineInformation)
    {
        $this->onlineInformation = $onlineInformation;
        return $this;
    }

    /**
     * Descriptive information, usage information or implantation information that is not captured in an existing element.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Descriptive information, usage information or implantation information that is not captured in an existing element.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * The quantity of the device present in the packaging (e.g. the number of devices present in a pack, or the number of devices in the same package of the medicinal product).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The quantity of the device present in the packaging (e.g. the number of devices present in a pack, or the number of devices in the same package of the medicinal product).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * The parent device it can be part of.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getParentDevice()
    {
        return $this->parentDevice;
    }

    /**
     * The parent device it can be part of.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $parentDevice
     * @return $this
     */
    public function setParentDevice($parentDevice)
    {
        $this->parentDevice = $parentDevice;
        return $this;
    }

    /**
     * A substance used to create the material(s) of which the device is made.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial[]
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * A substance used to create the material(s) of which the device is made.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial $material
     * @return $this
     */
    public function addMaterial($material)
    {
        $this->material[] = $material;
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
            if (isset($data['udiDeviceIdentifier'])) {
                if (is_array($data['udiDeviceIdentifier'])) {
                    foreach ($data['udiDeviceIdentifier'] as $d) {
                        $this->addUdiDeviceIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"udiDeviceIdentifier" must be array of objects or null, ' . gettype($data['udiDeviceIdentifier']) . ' seen.');
                }
            }
            if (isset($data['manufacturerString'])) {
                $this->setManufacturerString($data['manufacturerString']);
            }
            if (isset($data['manufacturerReference'])) {
                $this->setManufacturerReference($data['manufacturerReference']);
            }
            if (isset($data['deviceName'])) {
                if (is_array($data['deviceName'])) {
                    foreach ($data['deviceName'] as $d) {
                        $this->addDeviceName($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"deviceName" must be array of objects or null, ' . gettype($data['deviceName']) . ' seen.');
                }
            }
            if (isset($data['modelNumber'])) {
                $this->setModelNumber($data['modelNumber']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['specialization'])) {
                if (is_array($data['specialization'])) {
                    foreach ($data['specialization'] as $d) {
                        $this->addSpecialization($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specialization" must be array of objects or null, ' . gettype($data['specialization']) . ' seen.');
                }
            }
            if (isset($data['version'])) {
                if (is_array($data['version'])) {
                    foreach ($data['version'] as $d) {
                        $this->addVersion($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"version" must be array of objects or null, ' . gettype($data['version']) . ' seen.');
                }
            }
            if (isset($data['safety'])) {
                if (is_array($data['safety'])) {
                    foreach ($data['safety'] as $d) {
                        $this->addSafety($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"safety" must be array of objects or null, ' . gettype($data['safety']) . ' seen.');
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
            if (isset($data['physicalCharacteristics'])) {
                $this->setPhysicalCharacteristics($data['physicalCharacteristics']);
            }
            if (isset($data['languageCode'])) {
                if (is_array($data['languageCode'])) {
                    foreach ($data['languageCode'] as $d) {
                        $this->addLanguageCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"languageCode" must be array of objects or null, ' . gettype($data['languageCode']) . ' seen.');
                }
            }
            if (isset($data['capability'])) {
                if (is_array($data['capability'])) {
                    foreach ($data['capability'] as $d) {
                        $this->addCapability($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"capability" must be array of objects or null, ' . gettype($data['capability']) . ' seen.');
                }
            }
            if (isset($data['property'])) {
                if (is_array($data['property'])) {
                    foreach ($data['property'] as $d) {
                        $this->addProperty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"property" must be array of objects or null, ' . gettype($data['property']) . ' seen.');
                }
            }
            if (isset($data['owner'])) {
                $this->setOwner($data['owner']);
            }
            if (isset($data['contact'])) {
                if (is_array($data['contact'])) {
                    foreach ($data['contact'] as $d) {
                        $this->addContact($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contact" must be array of objects or null, ' . gettype($data['contact']) . ' seen.');
                }
            }
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
            }
            if (isset($data['onlineInformation'])) {
                $this->setOnlineInformation($data['onlineInformation']);
            }
            if (isset($data['note'])) {
                if (is_array($data['note'])) {
                    foreach ($data['note'] as $d) {
                        $this->addNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"note" must be array of objects or null, ' . gettype($data['note']) . ' seen.');
                }
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['parentDevice'])) {
                $this->setParentDevice($data['parentDevice']);
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (0 < count($this->udiDeviceIdentifier)) {
            $json['udiDeviceIdentifier'] = [];
            foreach ($this->udiDeviceIdentifier as $udiDeviceIdentifier) {
                $json['udiDeviceIdentifier'][] = $udiDeviceIdentifier;
            }
        }
        if (isset($this->manufacturerString)) {
            $json['manufacturerString'] = $this->manufacturerString;
        }
        if (isset($this->manufacturerReference)) {
            $json['manufacturerReference'] = $this->manufacturerReference;
        }
        if (0 < count($this->deviceName)) {
            $json['deviceName'] = [];
            foreach ($this->deviceName as $deviceName) {
                $json['deviceName'][] = $deviceName;
            }
        }
        if (isset($this->modelNumber)) {
            $json['modelNumber'] = $this->modelNumber;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->specialization)) {
            $json['specialization'] = [];
            foreach ($this->specialization as $specialization) {
                $json['specialization'][] = $specialization;
            }
        }
        if (0 < count($this->version)) {
            $json['version'] = [];
            foreach ($this->version as $version) {
                $json['version'][] = $version;
            }
        }
        if (0 < count($this->safety)) {
            $json['safety'] = [];
            foreach ($this->safety as $safety) {
                $json['safety'][] = $safety;
            }
        }
        if (0 < count($this->shelfLifeStorage)) {
            $json['shelfLifeStorage'] = [];
            foreach ($this->shelfLifeStorage as $shelfLifeStorage) {
                $json['shelfLifeStorage'][] = $shelfLifeStorage;
            }
        }
        if (isset($this->physicalCharacteristics)) {
            $json['physicalCharacteristics'] = $this->physicalCharacteristics;
        }
        if (0 < count($this->languageCode)) {
            $json['languageCode'] = [];
            foreach ($this->languageCode as $languageCode) {
                $json['languageCode'][] = $languageCode;
            }
        }
        if (0 < count($this->capability)) {
            $json['capability'] = [];
            foreach ($this->capability as $capability) {
                $json['capability'][] = $capability;
            }
        }
        if (0 < count($this->property)) {
            $json['property'] = [];
            foreach ($this->property as $property) {
                $json['property'][] = $property;
            }
        }
        if (isset($this->owner)) {
            $json['owner'] = $this->owner;
        }
        if (0 < count($this->contact)) {
            $json['contact'] = [];
            foreach ($this->contact as $contact) {
                $json['contact'][] = $contact;
            }
        }
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (isset($this->onlineInformation)) {
            $json['onlineInformation'] = $this->onlineInformation;
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->parentDevice)) {
            $json['parentDevice'] = $this->parentDevice;
        }
        if (0 < count($this->material)) {
            $json['material'] = [];
            foreach ($this->material as $material) {
                $json['material'][] = $material;
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
            $sxe = new \SimpleXMLElement('<DeviceDefinition xmlns="http://hl7.org/fhir"></DeviceDefinition>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (0 < count($this->udiDeviceIdentifier)) {
            foreach ($this->udiDeviceIdentifier as $udiDeviceIdentifier) {
                $udiDeviceIdentifier->xmlSerialize(true, $sxe->addChild('udiDeviceIdentifier'));
            }
        }
        if (isset($this->manufacturerString)) {
            $this->manufacturerString->xmlSerialize(true, $sxe->addChild('manufacturerString'));
        }
        if (isset($this->manufacturerReference)) {
            $this->manufacturerReference->xmlSerialize(true, $sxe->addChild('manufacturerReference'));
        }
        if (0 < count($this->deviceName)) {
            foreach ($this->deviceName as $deviceName) {
                $deviceName->xmlSerialize(true, $sxe->addChild('deviceName'));
            }
        }
        if (isset($this->modelNumber)) {
            $this->modelNumber->xmlSerialize(true, $sxe->addChild('modelNumber'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->specialization)) {
            foreach ($this->specialization as $specialization) {
                $specialization->xmlSerialize(true, $sxe->addChild('specialization'));
            }
        }
        if (0 < count($this->version)) {
            foreach ($this->version as $version) {
                $version->xmlSerialize(true, $sxe->addChild('version'));
            }
        }
        if (0 < count($this->safety)) {
            foreach ($this->safety as $safety) {
                $safety->xmlSerialize(true, $sxe->addChild('safety'));
            }
        }
        if (0 < count($this->shelfLifeStorage)) {
            foreach ($this->shelfLifeStorage as $shelfLifeStorage) {
                $shelfLifeStorage->xmlSerialize(true, $sxe->addChild('shelfLifeStorage'));
            }
        }
        if (isset($this->physicalCharacteristics)) {
            $this->physicalCharacteristics->xmlSerialize(true, $sxe->addChild('physicalCharacteristics'));
        }
        if (0 < count($this->languageCode)) {
            foreach ($this->languageCode as $languageCode) {
                $languageCode->xmlSerialize(true, $sxe->addChild('languageCode'));
            }
        }
        if (0 < count($this->capability)) {
            foreach ($this->capability as $capability) {
                $capability->xmlSerialize(true, $sxe->addChild('capability'));
            }
        }
        if (0 < count($this->property)) {
            foreach ($this->property as $property) {
                $property->xmlSerialize(true, $sxe->addChild('property'));
            }
        }
        if (isset($this->owner)) {
            $this->owner->xmlSerialize(true, $sxe->addChild('owner'));
        }
        if (0 < count($this->contact)) {
            foreach ($this->contact as $contact) {
                $contact->xmlSerialize(true, $sxe->addChild('contact'));
            }
        }
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (isset($this->onlineInformation)) {
            $this->onlineInformation->xmlSerialize(true, $sxe->addChild('onlineInformation'));
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->parentDevice)) {
            $this->parentDevice->xmlSerialize(true, $sxe->addChild('parentDevice'));
        }
        if (0 < count($this->material)) {
            foreach ($this->material as $material) {
                $material->xmlSerialize(true, $sxe->addChild('material'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
