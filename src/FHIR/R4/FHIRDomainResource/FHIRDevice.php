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
 * A type of a manufactured item that is used in the provision of healthcare without being substantially changed through that activity. The device may be a medical or non-medical device.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRDevice extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Unique instance identifiers assigned to a device by manufacturers other organizations or owners.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The reference to the definition for the device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $definition = null;

    /**
     * Unique device identifier (UDI) assigned to device label or package.  Note that the Device may include multiple udiCarriers as it either may include just the udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it could have been sold.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceUdiCarrier[]
     */
    public $udiCarrier = [];

    /**
     * Status of the Device availability.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRDeviceStatus
     */
    public $status = null;

    /**
     * Reason for the dtatus of the Device availability.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $statusReason = [];

    /**
     * The distinct identification string as required by regulation for a human cell, tissue, or cellular and tissue-based product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $distinctIdentifier = null;

    /**
     * A name of the manufacturer.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $manufacturer = null;

    /**
     * The date and time when the device was manufactured.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $manufactureDate = null;

    /**
     * The date and time beyond which this device is no longer valid or should not be used (if applicable).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $expirationDate = null;

    /**
     * Lot number assigned by the manufacturer.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $lotNumber = null;

    /**
     * The serial number assigned by the organization when the device was manufactured.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $serialNumber = null;

    /**
     * This represents the manufacturer's name of the device as provided by the device, from a UDI label, or by a person describing the Device.  This typically would be used when a person provides the name(s) or when the device represents one of the names available from DeviceDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceDeviceName[]
     */
    public $deviceName = [];

    /**
     * The model number for the device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $modelNumber = null;

    /**
     * The part number of the device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $partNumber = null;

    /**
     * The kind or type of device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The capabilities supported on a  device, the standards to which the device conforms for a particular purpose, and used for the communication.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceSpecialization[]
     */
    public $specialization = [];

    /**
     * The actual design of the device or software version running on the device.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceVersion[]
     */
    public $version = [];

    /**
     * The actual configuration settings of a device as it actually operates, e.g., regulation status, time properties.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceProperty[]
     */
    public $property = [];

    /**
     * Patient information, If the device is affixed to a person.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $patient = null;

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
     * The place where the device can be found.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $location = null;

    /**
     * A network address on which the device may be contacted directly.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * Descriptive information, usage information or implantation information that is not captured in an existing element.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * Provides additional safety characteristics about a medical device.  For example devices containing latex.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $safety = [];

    /**
     * The parent device.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $parent = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Device';

    /**
     * Unique instance identifiers assigned to a device by manufacturers other organizations or owners.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique instance identifiers assigned to a device by manufacturers other organizations or owners.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The reference to the definition for the device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * The reference to the definition for the device.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $definition
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
        return $this;
    }

    /**
     * Unique device identifier (UDI) assigned to device label or package.  Note that the Device may include multiple udiCarriers as it either may include just the udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it could have been sold.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceUdiCarrier[]
     */
    public function getUdiCarrier()
    {
        return $this->udiCarrier;
    }

    /**
     * Unique device identifier (UDI) assigned to device label or package.  Note that the Device may include multiple udiCarriers as it either may include just the udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it could have been sold.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceUdiCarrier $udiCarrier
     * @return $this
     */
    public function addUdiCarrier($udiCarrier)
    {
        $this->udiCarrier[] = $udiCarrier;
        return $this;
    }

    /**
     * Status of the Device availability.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRDeviceStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Status of the Device availability.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRDeviceStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Reason for the dtatus of the Device availability.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getStatusReason()
    {
        return $this->statusReason;
    }

    /**
     * Reason for the dtatus of the Device availability.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $statusReason
     * @return $this
     */
    public function addStatusReason($statusReason)
    {
        $this->statusReason[] = $statusReason;
        return $this;
    }

    /**
     * The distinct identification string as required by regulation for a human cell, tissue, or cellular and tissue-based product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDistinctIdentifier()
    {
        return $this->distinctIdentifier;
    }

    /**
     * The distinct identification string as required by regulation for a human cell, tissue, or cellular and tissue-based product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $distinctIdentifier
     * @return $this
     */
    public function setDistinctIdentifier($distinctIdentifier)
    {
        $this->distinctIdentifier = $distinctIdentifier;
        return $this;
    }

    /**
     * A name of the manufacturer.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * A name of the manufacturer.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $manufacturer
     * @return $this
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * The date and time when the device was manufactured.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getManufactureDate()
    {
        return $this->manufactureDate;
    }

    /**
     * The date and time when the device was manufactured.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $manufactureDate
     * @return $this
     */
    public function setManufactureDate($manufactureDate)
    {
        $this->manufactureDate = $manufactureDate;
        return $this;
    }

    /**
     * The date and time beyond which this device is no longer valid or should not be used (if applicable).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * The date and time beyond which this device is no longer valid or should not be used (if applicable).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $expirationDate
     * @return $this
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    /**
     * Lot number assigned by the manufacturer.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getLotNumber()
    {
        return $this->lotNumber;
    }

    /**
     * Lot number assigned by the manufacturer.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $lotNumber
     * @return $this
     */
    public function setLotNumber($lotNumber)
    {
        $this->lotNumber = $lotNumber;
        return $this;
    }

    /**
     * The serial number assigned by the organization when the device was manufactured.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * The serial number assigned by the organization when the device was manufactured.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $serialNumber
     * @return $this
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;
        return $this;
    }

    /**
     * This represents the manufacturer's name of the device as provided by the device, from a UDI label, or by a person describing the Device.  This typically would be used when a person provides the name(s) or when the device represents one of the names available from DeviceDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceDeviceName[]
     */
    public function getDeviceName()
    {
        return $this->deviceName;
    }

    /**
     * This represents the manufacturer's name of the device as provided by the device, from a UDI label, or by a person describing the Device.  This typically would be used when a person provides the name(s) or when the device represents one of the names available from DeviceDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceDeviceName $deviceName
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
     * The part number of the device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPartNumber()
    {
        return $this->partNumber;
    }

    /**
     * The part number of the device.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $partNumber
     * @return $this
     */
    public function setPartNumber($partNumber)
    {
        $this->partNumber = $partNumber;
        return $this;
    }

    /**
     * The kind or type of device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The kind or type of device.
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
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceSpecialization[]
     */
    public function getSpecialization()
    {
        return $this->specialization;
    }

    /**
     * The capabilities supported on a  device, the standards to which the device conforms for a particular purpose, and used for the communication.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceSpecialization $specialization
     * @return $this
     */
    public function addSpecialization($specialization)
    {
        $this->specialization[] = $specialization;
        return $this;
    }

    /**
     * The actual design of the device or software version running on the device.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceVersion[]
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The actual design of the device or software version running on the device.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceVersion $version
     * @return $this
     */
    public function addVersion($version)
    {
        $this->version[] = $version;
        return $this;
    }

    /**
     * The actual configuration settings of a device as it actually operates, e.g., regulation status, time properties.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceProperty[]
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * The actual configuration settings of a device as it actually operates, e.g., regulation status, time properties.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDevice\FHIRDeviceProperty $property
     * @return $this
     */
    public function addProperty($property)
    {
        $this->property[] = $property;
        return $this;
    }

    /**
     * Patient information, If the device is affixed to a person.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * Patient information, If the device is affixed to a person.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
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
     * The place where the device can be found.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The place where the device can be found.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
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
     * Provides additional safety characteristics about a medical device.  For example devices containing latex.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSafety()
    {
        return $this->safety;
    }

    /**
     * Provides additional safety characteristics about a medical device.  For example devices containing latex.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $safety
     * @return $this
     */
    public function addSafety($safety)
    {
        $this->safety[] = $safety;
        return $this;
    }

    /**
     * The parent device.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * The parent device.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
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
            if (isset($data['definition'])) {
                $this->setDefinition($data['definition']);
            }
            if (isset($data['udiCarrier'])) {
                if (is_array($data['udiCarrier'])) {
                    foreach ($data['udiCarrier'] as $d) {
                        $this->addUdiCarrier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"udiCarrier" must be array of objects or null, ' . gettype($data['udiCarrier']) . ' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['statusReason'])) {
                if (is_array($data['statusReason'])) {
                    foreach ($data['statusReason'] as $d) {
                        $this->addStatusReason($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"statusReason" must be array of objects or null, ' . gettype($data['statusReason']) . ' seen.');
                }
            }
            if (isset($data['distinctIdentifier'])) {
                $this->setDistinctIdentifier($data['distinctIdentifier']);
            }
            if (isset($data['manufacturer'])) {
                $this->setManufacturer($data['manufacturer']);
            }
            if (isset($data['manufactureDate'])) {
                $this->setManufactureDate($data['manufactureDate']);
            }
            if (isset($data['expirationDate'])) {
                $this->setExpirationDate($data['expirationDate']);
            }
            if (isset($data['lotNumber'])) {
                $this->setLotNumber($data['lotNumber']);
            }
            if (isset($data['serialNumber'])) {
                $this->setSerialNumber($data['serialNumber']);
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
            if (isset($data['partNumber'])) {
                $this->setPartNumber($data['partNumber']);
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
            if (isset($data['property'])) {
                if (is_array($data['property'])) {
                    foreach ($data['property'] as $d) {
                        $this->addProperty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"property" must be array of objects or null, ' . gettype($data['property']) . ' seen.');
                }
            }
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
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
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
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
            if (isset($data['safety'])) {
                if (is_array($data['safety'])) {
                    foreach ($data['safety'] as $d) {
                        $this->addSafety($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"safety" must be array of objects or null, ' . gettype($data['safety']) . ' seen.');
                }
            }
            if (isset($data['parent'])) {
                $this->setParent($data['parent']);
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
        if (isset($this->definition)) {
            $json['definition'] = $this->definition;
        }
        if (0 < count($this->udiCarrier)) {
            $json['udiCarrier'] = [];
            foreach ($this->udiCarrier as $udiCarrier) {
                $json['udiCarrier'][] = $udiCarrier;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (0 < count($this->statusReason)) {
            $json['statusReason'] = [];
            foreach ($this->statusReason as $statusReason) {
                $json['statusReason'][] = $statusReason;
            }
        }
        if (isset($this->distinctIdentifier)) {
            $json['distinctIdentifier'] = $this->distinctIdentifier;
        }
        if (isset($this->manufacturer)) {
            $json['manufacturer'] = $this->manufacturer;
        }
        if (isset($this->manufactureDate)) {
            $json['manufactureDate'] = $this->manufactureDate;
        }
        if (isset($this->expirationDate)) {
            $json['expirationDate'] = $this->expirationDate;
        }
        if (isset($this->lotNumber)) {
            $json['lotNumber'] = $this->lotNumber;
        }
        if (isset($this->serialNumber)) {
            $json['serialNumber'] = $this->serialNumber;
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
        if (isset($this->partNumber)) {
            $json['partNumber'] = $this->partNumber;
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
        if (0 < count($this->property)) {
            $json['property'] = [];
            foreach ($this->property as $property) {
                $json['property'][] = $property;
            }
        }
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
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
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
        }
        if (0 < count($this->safety)) {
            $json['safety'] = [];
            foreach ($this->safety as $safety) {
                $json['safety'][] = $safety;
            }
        }
        if (isset($this->parent)) {
            $json['parent'] = $this->parent;
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
            $sxe = new \SimpleXMLElement('<Device xmlns="http://hl7.org/fhir"></Device>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->definition)) {
            $this->definition->xmlSerialize(true, $sxe->addChild('definition'));
        }
        if (0 < count($this->udiCarrier)) {
            foreach ($this->udiCarrier as $udiCarrier) {
                $udiCarrier->xmlSerialize(true, $sxe->addChild('udiCarrier'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (0 < count($this->statusReason)) {
            foreach ($this->statusReason as $statusReason) {
                $statusReason->xmlSerialize(true, $sxe->addChild('statusReason'));
            }
        }
        if (isset($this->distinctIdentifier)) {
            $this->distinctIdentifier->xmlSerialize(true, $sxe->addChild('distinctIdentifier'));
        }
        if (isset($this->manufacturer)) {
            $this->manufacturer->xmlSerialize(true, $sxe->addChild('manufacturer'));
        }
        if (isset($this->manufactureDate)) {
            $this->manufactureDate->xmlSerialize(true, $sxe->addChild('manufactureDate'));
        }
        if (isset($this->expirationDate)) {
            $this->expirationDate->xmlSerialize(true, $sxe->addChild('expirationDate'));
        }
        if (isset($this->lotNumber)) {
            $this->lotNumber->xmlSerialize(true, $sxe->addChild('lotNumber'));
        }
        if (isset($this->serialNumber)) {
            $this->serialNumber->xmlSerialize(true, $sxe->addChild('serialNumber'));
        }
        if (0 < count($this->deviceName)) {
            foreach ($this->deviceName as $deviceName) {
                $deviceName->xmlSerialize(true, $sxe->addChild('deviceName'));
            }
        }
        if (isset($this->modelNumber)) {
            $this->modelNumber->xmlSerialize(true, $sxe->addChild('modelNumber'));
        }
        if (isset($this->partNumber)) {
            $this->partNumber->xmlSerialize(true, $sxe->addChild('partNumber'));
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
        if (0 < count($this->property)) {
            foreach ($this->property as $property) {
                $property->xmlSerialize(true, $sxe->addChild('property'));
            }
        }
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (isset($this->owner)) {
            $this->owner->xmlSerialize(true, $sxe->addChild('owner'));
        }
        if (0 < count($this->contact)) {
            foreach ($this->contact as $contact) {
                $contact->xmlSerialize(true, $sxe->addChild('contact'));
            }
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if (0 < count($this->safety)) {
            foreach ($this->safety as $safety) {
                $safety->xmlSerialize(true, $sxe->addChild('safety'));
            }
        }
        if (isset($this->parent)) {
            $this->parent->xmlSerialize(true, $sxe->addChild('parent'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
