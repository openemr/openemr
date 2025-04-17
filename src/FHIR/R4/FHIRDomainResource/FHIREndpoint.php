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
 * The technical details of an endpoint that can be used for electronic services, such as for web services providing XDS.b or a REST endpoint for another FHIR server. This may include any security context information.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIREndpoint extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifier for the organization that is used to identify the endpoint across multiple disparate systems.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * active | suspended | error | off | test.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIREndpointStatus
     */
    public $status = null;

    /**
     * A coded value that represents the technical details of the usage of this endpoint, such as what WSDLs should be used in what way. (e.g. XDS.b/DICOM/cds-hook).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $connectionType = null;

    /**
     * A friendly name that this endpoint can be referred to with.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * The organization that manages this endpoint (even if technically another organization is hosting this in the cloud, it is the organization associated with the data).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $managingOrganization = null;

    /**
     * Contact details for a human to contact about the subscription. The primary use of this for system administrator troubleshooting.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public $contact = [];

    /**
     * The interval during which the endpoint is expected to be operational.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * The payload type describes the acceptable content that can be communicated on the endpoint.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $payloadType = [];

    /**
     * The mime type to send the payload in - e.g. application/fhir+xml, application/fhir+json. If the mime type is not specified, then the sender could send any content (including no content depending on the connectionType).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public $payloadMimeType = [];

    /**
     * The uri that describes the actual end-point to connect to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl
     */
    public $address = null;

    /**
     * Additional headers / information to send as part of the notification.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $header = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Endpoint';

    /**
     * Identifier for the organization that is used to identify the endpoint across multiple disparate systems.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier for the organization that is used to identify the endpoint across multiple disparate systems.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * active | suspended | error | off | test.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIREndpointStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * active | suspended | error | off | test.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIREndpointStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A coded value that represents the technical details of the usage of this endpoint, such as what WSDLs should be used in what way. (e.g. XDS.b/DICOM/cds-hook).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getConnectionType()
    {
        return $this->connectionType;
    }

    /**
     * A coded value that represents the technical details of the usage of this endpoint, such as what WSDLs should be used in what way. (e.g. XDS.b/DICOM/cds-hook).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $connectionType
     * @return $this
     */
    public function setConnectionType($connectionType)
    {
        $this->connectionType = $connectionType;
        return $this;
    }

    /**
     * A friendly name that this endpoint can be referred to with.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A friendly name that this endpoint can be referred to with.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The organization that manages this endpoint (even if technically another organization is hosting this in the cloud, it is the organization associated with the data).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getManagingOrganization()
    {
        return $this->managingOrganization;
    }

    /**
     * The organization that manages this endpoint (even if technically another organization is hosting this in the cloud, it is the organization associated with the data).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $managingOrganization
     * @return $this
     */
    public function setManagingOrganization($managingOrganization)
    {
        $this->managingOrganization = $managingOrganization;
        return $this;
    }

    /**
     * Contact details for a human to contact about the subscription. The primary use of this for system administrator troubleshooting.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Contact details for a human to contact about the subscription. The primary use of this for system administrator troubleshooting.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint $contact
     * @return $this
     */
    public function addContact($contact)
    {
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * The interval during which the endpoint is expected to be operational.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The interval during which the endpoint is expected to be operational.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * The payload type describes the acceptable content that can be communicated on the endpoint.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getPayloadType()
    {
        return $this->payloadType;
    }

    /**
     * The payload type describes the acceptable content that can be communicated on the endpoint.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $payloadType
     * @return $this
     */
    public function addPayloadType($payloadType)
    {
        $this->payloadType[] = $payloadType;
        return $this;
    }

    /**
     * The mime type to send the payload in - e.g. application/fhir+xml, application/fhir+json. If the mime type is not specified, then the sender could send any content (including no content depending on the connectionType).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public function getPayloadMimeType()
    {
        return $this->payloadMimeType;
    }

    /**
     * The mime type to send the payload in - e.g. application/fhir+xml, application/fhir+json. If the mime type is not specified, then the sender could send any content (including no content depending on the connectionType).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $payloadMimeType
     * @return $this
     */
    public function addPayloadMimeType($payloadMimeType)
    {
        $this->payloadMimeType[] = $payloadMimeType;
        return $this;
    }

    /**
     * The uri that describes the actual end-point to connect to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * The uri that describes the actual end-point to connect to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Additional headers / information to send as part of the notification.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Additional headers / information to send as part of the notification.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $header
     * @return $this
     */
    public function addHeader($header)
    {
        $this->header[] = $header;
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
            if (isset($data['connectionType'])) {
                $this->setConnectionType($data['connectionType']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['managingOrganization'])) {
                $this->setManagingOrganization($data['managingOrganization']);
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
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['payloadType'])) {
                if (is_array($data['payloadType'])) {
                    foreach ($data['payloadType'] as $d) {
                        $this->addPayloadType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"payloadType" must be array of objects or null, ' . gettype($data['payloadType']) . ' seen.');
                }
            }
            if (isset($data['payloadMimeType'])) {
                if (is_array($data['payloadMimeType'])) {
                    foreach ($data['payloadMimeType'] as $d) {
                        $this->addPayloadMimeType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"payloadMimeType" must be array of objects or null, ' . gettype($data['payloadMimeType']) . ' seen.');
                }
            }
            if (isset($data['address'])) {
                $this->setAddress($data['address']);
            }
            if (isset($data['header'])) {
                if (is_array($data['header'])) {
                    foreach ($data['header'] as $d) {
                        $this->addHeader($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"header" must be array of objects or null, ' . gettype($data['header']) . ' seen.');
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->connectionType)) {
            $json['connectionType'] = $this->connectionType;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->managingOrganization)) {
            $json['managingOrganization'] = $this->managingOrganization;
        }
        if (0 < count($this->contact)) {
            $json['contact'] = [];
            foreach ($this->contact as $contact) {
                $json['contact'][] = $contact;
            }
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (0 < count($this->payloadType)) {
            $json['payloadType'] = [];
            foreach ($this->payloadType as $payloadType) {
                $json['payloadType'][] = $payloadType;
            }
        }
        if (0 < count($this->payloadMimeType)) {
            $json['payloadMimeType'] = [];
            foreach ($this->payloadMimeType as $payloadMimeType) {
                $json['payloadMimeType'][] = $payloadMimeType;
            }
        }
        if (isset($this->address)) {
            $json['address'] = $this->address;
        }
        if (0 < count($this->header)) {
            $json['header'] = [];
            foreach ($this->header as $header) {
                $json['header'][] = $header;
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
            $sxe = new \SimpleXMLElement('<Endpoint xmlns="http://hl7.org/fhir"></Endpoint>');
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
        if (isset($this->connectionType)) {
            $this->connectionType->xmlSerialize(true, $sxe->addChild('connectionType'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->managingOrganization)) {
            $this->managingOrganization->xmlSerialize(true, $sxe->addChild('managingOrganization'));
        }
        if (0 < count($this->contact)) {
            foreach ($this->contact as $contact) {
                $contact->xmlSerialize(true, $sxe->addChild('contact'));
            }
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (0 < count($this->payloadType)) {
            foreach ($this->payloadType as $payloadType) {
                $payloadType->xmlSerialize(true, $sxe->addChild('payloadType'));
            }
        }
        if (0 < count($this->payloadMimeType)) {
            foreach ($this->payloadMimeType as $payloadMimeType) {
                $payloadMimeType->xmlSerialize(true, $sxe->addChild('payloadMimeType'));
            }
        }
        if (isset($this->address)) {
            $this->address->xmlSerialize(true, $sxe->addChild('address'));
        }
        if (0 < count($this->header)) {
            foreach ($this->header as $header) {
                $header->xmlSerialize(true, $sxe->addChild('header'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
