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
 * Details and position information for a physical place where services are provided and resources and participants may be stored, found, contained, or accommodated.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRLocation extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Unique code or number identifying the location to its users.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The status property covers the general availability of the resource, not the current value which may be covered by the operationStatus, or by a schedule/slots if they are configured for the location.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRLocationStatus
     */
    public $status = null;

    /**
     * The operational status covers operation values most relevant to beds (but can also apply to rooms/units/chairs/etc. such as an isolation unit/dialysis chair). This typically covers concepts such as contamination, housekeeping, and other activities like maintenance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $operationalStatus = null;

    /**
     * Name of the location as used by humans. Does not need to be unique.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A list of alternate names that the location is known as, or was known as, in the past.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $alias = [];

    /**
     * Description of the Location, which helps in finding or referencing the place.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Indicates whether a resource instance represents a specific location or a class of locations.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRLocationMode
     */
    public $mode = null;

    /**
     * Indicates the type of function performed at the location.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $type = [];

    /**
     * The contact details of communication devices available at the location. This can include phone numbers, fax numbers, mobile numbers, email addresses and web sites.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public $telecom = [];

    /**
     * Physical location.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress
     */
    public $address = null;

    /**
     * Physical form of the location, e.g. building, room, vehicle, road.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $physicalType = null;

    /**
     * The absolute geographic location of the Location, expressed using the WGS84 datum (This is the same co-ordinate system used in KML).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRLocation\FHIRLocationPosition
     */
    public $position = null;

    /**
     * The organization responsible for the provisioning and upkeep of the location.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $managingOrganization = null;

    /**
     * Another Location of which this Location is physically a part of.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $partOf = null;

    /**
     * What days/times during a week is this location usually open.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRLocation\FHIRLocationHoursOfOperation[]
     */
    public $hoursOfOperation = [];

    /**
     * A description of when the locations opening ours are different to normal, e.g. public holiday availability. Succinctly describing all possible exceptions to normal site availability as detailed in the opening hours Times.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $availabilityExceptions = null;

    /**
     * Technical endpoints providing access to services operated for the location.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $endpoint = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Location';

    /**
     * Unique code or number identifying the location to its users.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique code or number identifying the location to its users.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The status property covers the general availability of the resource, not the current value which may be covered by the operationStatus, or by a schedule/slots if they are configured for the location.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRLocationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status property covers the general availability of the resource, not the current value which may be covered by the operationStatus, or by a schedule/slots if they are configured for the location.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRLocationStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The operational status covers operation values most relevant to beds (but can also apply to rooms/units/chairs/etc. such as an isolation unit/dialysis chair). This typically covers concepts such as contamination, housekeeping, and other activities like maintenance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getOperationalStatus()
    {
        return $this->operationalStatus;
    }

    /**
     * The operational status covers operation values most relevant to beds (but can also apply to rooms/units/chairs/etc. such as an isolation unit/dialysis chair). This typically covers concepts such as contamination, housekeeping, and other activities like maintenance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $operationalStatus
     * @return $this
     */
    public function setOperationalStatus($operationalStatus)
    {
        $this->operationalStatus = $operationalStatus;
        return $this;
    }

    /**
     * Name of the location as used by humans. Does not need to be unique.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Name of the location as used by humans. Does not need to be unique.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A list of alternate names that the location is known as, or was known as, in the past.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * A list of alternate names that the location is known as, or was known as, in the past.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $alias
     * @return $this
     */
    public function addAlias($alias)
    {
        $this->alias[] = $alias;
        return $this;
    }

    /**
     * Description of the Location, which helps in finding or referencing the place.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Description of the Location, which helps in finding or referencing the place.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Indicates whether a resource instance represents a specific location or a class of locations.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRLocationMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Indicates whether a resource instance represents a specific location or a class of locations.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRLocationMode $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Indicates the type of function performed at the location.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Indicates the type of function performed at the location.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * The contact details of communication devices available at the location. This can include phone numbers, fax numbers, mobile numbers, email addresses and web sites.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public function getTelecom()
    {
        return $this->telecom;
    }

    /**
     * The contact details of communication devices available at the location. This can include phone numbers, fax numbers, mobile numbers, email addresses and web sites.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint $telecom
     * @return $this
     */
    public function addTelecom($telecom)
    {
        $this->telecom[] = $telecom;
        return $this;
    }

    /**
     * Physical location.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Physical location.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Physical form of the location, e.g. building, room, vehicle, road.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getPhysicalType()
    {
        return $this->physicalType;
    }

    /**
     * Physical form of the location, e.g. building, room, vehicle, road.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $physicalType
     * @return $this
     */
    public function setPhysicalType($physicalType)
    {
        $this->physicalType = $physicalType;
        return $this;
    }

    /**
     * The absolute geographic location of the Location, expressed using the WGS84 datum (This is the same co-ordinate system used in KML).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRLocation\FHIRLocationPosition
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * The absolute geographic location of the Location, expressed using the WGS84 datum (This is the same co-ordinate system used in KML).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRLocation\FHIRLocationPosition $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * The organization responsible for the provisioning and upkeep of the location.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getManagingOrganization()
    {
        return $this->managingOrganization;
    }

    /**
     * The organization responsible for the provisioning and upkeep of the location.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $managingOrganization
     * @return $this
     */
    public function setManagingOrganization($managingOrganization)
    {
        $this->managingOrganization = $managingOrganization;
        return $this;
    }

    /**
     * Another Location of which this Location is physically a part of.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * Another Location of which this Location is physically a part of.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $partOf
     * @return $this
     */
    public function setPartOf($partOf)
    {
        $this->partOf = $partOf;
        return $this;
    }

    /**
     * What days/times during a week is this location usually open.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRLocation\FHIRLocationHoursOfOperation[]
     */
    public function getHoursOfOperation()
    {
        return $this->hoursOfOperation;
    }

    /**
     * What days/times during a week is this location usually open.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRLocation\FHIRLocationHoursOfOperation $hoursOfOperation
     * @return $this
     */
    public function addHoursOfOperation($hoursOfOperation)
    {
        $this->hoursOfOperation[] = $hoursOfOperation;
        return $this;
    }

    /**
     * A description of when the locations opening ours are different to normal, e.g. public holiday availability. Succinctly describing all possible exceptions to normal site availability as detailed in the opening hours Times.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getAvailabilityExceptions()
    {
        return $this->availabilityExceptions;
    }

    /**
     * A description of when the locations opening ours are different to normal, e.g. public holiday availability. Succinctly describing all possible exceptions to normal site availability as detailed in the opening hours Times.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $availabilityExceptions
     * @return $this
     */
    public function setAvailabilityExceptions($availabilityExceptions)
    {
        $this->availabilityExceptions = $availabilityExceptions;
        return $this;
    }

    /**
     * Technical endpoints providing access to services operated for the location.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Technical endpoints providing access to services operated for the location.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $endpoint
     * @return $this
     */
    public function addEndpoint($endpoint)
    {
        $this->endpoint[] = $endpoint;
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
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['operationalStatus'])) {
                $this->setOperationalStatus($data['operationalStatus']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['alias'])) {
                if (is_array($data['alias'])) {
                    foreach ($data['alias'] as $d) {
                        $this->addAlias($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"alias" must be array of objects or null, ' . gettype($data['alias']) . ' seen.');
                }
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['mode'])) {
                $this->setMode($data['mode']);
            }
            if (isset($data['type'])) {
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, ' . gettype($data['type']) . ' seen.');
                }
            }
            if (isset($data['telecom'])) {
                if (is_array($data['telecom'])) {
                    foreach ($data['telecom'] as $d) {
                        $this->addTelecom($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"telecom" must be array of objects or null, ' . gettype($data['telecom']) . ' seen.');
                }
            }
            if (isset($data['address'])) {
                $this->setAddress($data['address']);
            }
            if (isset($data['physicalType'])) {
                $this->setPhysicalType($data['physicalType']);
            }
            if (isset($data['position'])) {
                $this->setPosition($data['position']);
            }
            if (isset($data['managingOrganization'])) {
                $this->setManagingOrganization($data['managingOrganization']);
            }
            if (isset($data['partOf'])) {
                $this->setPartOf($data['partOf']);
            }
            if (isset($data['hoursOfOperation'])) {
                if (is_array($data['hoursOfOperation'])) {
                    foreach ($data['hoursOfOperation'] as $d) {
                        $this->addHoursOfOperation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"hoursOfOperation" must be array of objects or null, ' . gettype($data['hoursOfOperation']) . ' seen.');
                }
            }
            if (isset($data['availabilityExceptions'])) {
                $this->setAvailabilityExceptions($data['availabilityExceptions']);
            }
            if (isset($data['endpoint'])) {
                if (is_array($data['endpoint'])) {
                    foreach ($data['endpoint'] as $d) {
                        $this->addEndpoint($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"endpoint" must be array of objects or null, ' . gettype($data['endpoint']) . ' seen.');
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->operationalStatus)) {
            $json['operationalStatus'] = $this->operationalStatus;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (0 < count($this->alias)) {
            $json['alias'] = [];
            foreach ($this->alias as $alias) {
                $json['alias'][] = $alias;
            }
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->mode)) {
            $json['mode'] = $this->mode;
        }
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
        }
        if (0 < count($this->telecom)) {
            $json['telecom'] = [];
            foreach ($this->telecom as $telecom) {
                $json['telecom'][] = $telecom;
            }
        }
        if (isset($this->address)) {
            $json['address'] = $this->address;
        }
        if (isset($this->physicalType)) {
            $json['physicalType'] = $this->physicalType;
        }
        if (isset($this->position)) {
            $json['position'] = $this->position;
        }
        if (isset($this->managingOrganization)) {
            $json['managingOrganization'] = $this->managingOrganization;
        }
        if (isset($this->partOf)) {
            $json['partOf'] = $this->partOf;
        }
        if (0 < count($this->hoursOfOperation)) {
            $json['hoursOfOperation'] = [];
            foreach ($this->hoursOfOperation as $hoursOfOperation) {
                $json['hoursOfOperation'][] = $hoursOfOperation;
            }
        }
        if (isset($this->availabilityExceptions)) {
            $json['availabilityExceptions'] = $this->availabilityExceptions;
        }
        if (0 < count($this->endpoint)) {
            $json['endpoint'] = [];
            foreach ($this->endpoint as $endpoint) {
                $json['endpoint'][] = $endpoint;
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
            $sxe = new \SimpleXMLElement('<Location xmlns="http://hl7.org/fhir"></Location>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->operationalStatus)) {
            $this->operationalStatus->xmlSerialize(true, $sxe->addChild('operationalStatus'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (0 < count($this->alias)) {
            foreach ($this->alias as $alias) {
                $alias->xmlSerialize(true, $sxe->addChild('alias'));
            }
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->mode)) {
            $this->mode->xmlSerialize(true, $sxe->addChild('mode'));
        }
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (0 < count($this->telecom)) {
            foreach ($this->telecom as $telecom) {
                $telecom->xmlSerialize(true, $sxe->addChild('telecom'));
            }
        }
        if (isset($this->address)) {
            $this->address->xmlSerialize(true, $sxe->addChild('address'));
        }
        if (isset($this->physicalType)) {
            $this->physicalType->xmlSerialize(true, $sxe->addChild('physicalType'));
        }
        if (isset($this->position)) {
            $this->position->xmlSerialize(true, $sxe->addChild('position'));
        }
        if (isset($this->managingOrganization)) {
            $this->managingOrganization->xmlSerialize(true, $sxe->addChild('managingOrganization'));
        }
        if (isset($this->partOf)) {
            $this->partOf->xmlSerialize(true, $sxe->addChild('partOf'));
        }
        if (0 < count($this->hoursOfOperation)) {
            foreach ($this->hoursOfOperation as $hoursOfOperation) {
                $hoursOfOperation->xmlSerialize(true, $sxe->addChild('hoursOfOperation'));
            }
        }
        if (isset($this->availabilityExceptions)) {
            $this->availabilityExceptions->xmlSerialize(true, $sxe->addChild('availabilityExceptions'));
        }
        if (0 < count($this->endpoint)) {
            foreach ($this->endpoint as $endpoint) {
                $endpoint->xmlSerialize(true, $sxe->addChild('endpoint'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
