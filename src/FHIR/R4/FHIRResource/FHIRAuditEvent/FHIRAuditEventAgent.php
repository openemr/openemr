<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRAuditEvent;

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
 * A record of an event made for purposes of maintaining a security log. Typical uses include detection of intrusion attempts and monitoring for inappropriate usage.
 */
class FHIRAuditEventAgent extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Specification of the participation type the user plays when performing the event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The security role that the user was acting under, that come from local codes defined by the access control security system (e.g. RBAC, ABAC) used in the local context.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $role = [];

    /**
     * Reference to who this agent is that was involved in the event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $who = null;

    /**
     * Alternative agent Identifier. For a human, this should be a user identifier text string from authentication system. This identifier would be one known to a common authentication system (e.g. single sign-on), if available.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $altId = null;

    /**
     * Human-meaningful name for the agent.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * Indicator that the user is or is not the requestor, or initiator, for the event being audited.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $requestor = null;

    /**
     * Where the event occurred.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $location = null;

    /**
     * The policy or plan that authorized the activity being recorded. Typically, a single activity may have multiple applicable policies, such as patient consent, guarantor funding, etc. The policy would also indicate the security token used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri[]
     */
    public $policy = [];

    /**
     * Type of media involved. Used when the event is about exporting/importing onto media.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $media = null;

    /**
     * Logical network location for application activity, if the activity has a network location.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRAuditEvent\FHIRAuditEventNetwork
     */
    public $network = null;

    /**
     * The reason (purpose of use), specific to this agent, that was used during the event being recorded.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $purposeOfUse = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'AuditEvent.Agent';

    /**
     * Specification of the participation type the user plays when performing the event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Specification of the participation type the user plays when performing the event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The security role that the user was acting under, that come from local codes defined by the access control security system (e.g. RBAC, ABAC) used in the local context.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * The security role that the user was acting under, that come from local codes defined by the access control security system (e.g. RBAC, ABAC) used in the local context.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $role
     * @return $this
     */
    public function addRole($role)
    {
        $this->role[] = $role;
        return $this;
    }

    /**
     * Reference to who this agent is that was involved in the event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * Reference to who this agent is that was involved in the event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $who
     * @return $this
     */
    public function setWho($who)
    {
        $this->who = $who;
        return $this;
    }

    /**
     * Alternative agent Identifier. For a human, this should be a user identifier text string from authentication system. This identifier would be one known to a common authentication system (e.g. single sign-on), if available.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getAltId()
    {
        return $this->altId;
    }

    /**
     * Alternative agent Identifier. For a human, this should be a user identifier text string from authentication system. This identifier would be one known to a common authentication system (e.g. single sign-on), if available.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $altId
     * @return $this
     */
    public function setAltId($altId)
    {
        $this->altId = $altId;
        return $this;
    }

    /**
     * Human-meaningful name for the agent.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Human-meaningful name for the agent.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Indicator that the user is or is not the requestor, or initiator, for the event being audited.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getRequestor()
    {
        return $this->requestor;
    }

    /**
     * Indicator that the user is or is not the requestor, or initiator, for the event being audited.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $requestor
     * @return $this
     */
    public function setRequestor($requestor)
    {
        $this->requestor = $requestor;
        return $this;
    }

    /**
     * Where the event occurred.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Where the event occurred.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * The policy or plan that authorized the activity being recorded. Typically, a single activity may have multiple applicable policies, such as patient consent, guarantor funding, etc. The policy would also indicate the security token used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri[]
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * The policy or plan that authorized the activity being recorded. Typically, a single activity may have multiple applicable policies, such as patient consent, guarantor funding, etc. The policy would also indicate the security token used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $policy
     * @return $this
     */
    public function addPolicy($policy)
    {
        $this->policy[] = $policy;
        return $this;
    }

    /**
     * Type of media involved. Used when the event is about exporting/importing onto media.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Type of media involved. Used when the event is about exporting/importing onto media.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $media
     * @return $this
     */
    public function setMedia($media)
    {
        $this->media = $media;
        return $this;
    }

    /**
     * Logical network location for application activity, if the activity has a network location.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRAuditEvent\FHIRAuditEventNetwork
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Logical network location for application activity, if the activity has a network location.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRAuditEvent\FHIRAuditEventNetwork $network
     * @return $this
     */
    public function setNetwork($network)
    {
        $this->network = $network;
        return $this;
    }

    /**
     * The reason (purpose of use), specific to this agent, that was used during the event being recorded.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getPurposeOfUse()
    {
        return $this->purposeOfUse;
    }

    /**
     * The reason (purpose of use), specific to this agent, that was used during the event being recorded.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $purposeOfUse
     * @return $this
     */
    public function addPurposeOfUse($purposeOfUse)
    {
        $this->purposeOfUse[] = $purposeOfUse;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['role'])) {
                if (is_array($data['role'])) {
                    foreach ($data['role'] as $d) {
                        $this->addRole($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"role" must be array of objects or null, ' . gettype($data['role']) . ' seen.');
                }
            }
            if (isset($data['who'])) {
                $this->setWho($data['who']);
            }
            if (isset($data['altId'])) {
                $this->setAltId($data['altId']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['requestor'])) {
                $this->setRequestor($data['requestor']);
            }
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['policy'])) {
                if (is_array($data['policy'])) {
                    foreach ($data['policy'] as $d) {
                        $this->addPolicy($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"policy" must be array of objects or null, ' . gettype($data['policy']) . ' seen.');
                }
            }
            if (isset($data['media'])) {
                $this->setMedia($data['media']);
            }
            if (isset($data['network'])) {
                $this->setNetwork($data['network']);
            }
            if (isset($data['purposeOfUse'])) {
                if (is_array($data['purposeOfUse'])) {
                    foreach ($data['purposeOfUse'] as $d) {
                        $this->addPurposeOfUse($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"purposeOfUse" must be array of objects or null, ' . gettype($data['purposeOfUse']) . ' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->role)) {
            $json['role'] = [];
            foreach ($this->role as $role) {
                $json['role'][] = $role;
            }
        }
        if (isset($this->who)) {
            $json['who'] = $this->who;
        }
        if (isset($this->altId)) {
            $json['altId'] = $this->altId;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->requestor)) {
            $json['requestor'] = $this->requestor;
        }
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (0 < count($this->policy)) {
            $json['policy'] = [];
            foreach ($this->policy as $policy) {
                $json['policy'][] = $policy;
            }
        }
        if (isset($this->media)) {
            $json['media'] = $this->media;
        }
        if (isset($this->network)) {
            $json['network'] = $this->network;
        }
        if (0 < count($this->purposeOfUse)) {
            $json['purposeOfUse'] = [];
            foreach ($this->purposeOfUse as $purposeOfUse) {
                $json['purposeOfUse'][] = $purposeOfUse;
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
            $sxe = new \SimpleXMLElement('<AuditEventAgent xmlns="http://hl7.org/fhir"></AuditEventAgent>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->role)) {
            foreach ($this->role as $role) {
                $role->xmlSerialize(true, $sxe->addChild('role'));
            }
        }
        if (isset($this->who)) {
            $this->who->xmlSerialize(true, $sxe->addChild('who'));
        }
        if (isset($this->altId)) {
            $this->altId->xmlSerialize(true, $sxe->addChild('altId'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->requestor)) {
            $this->requestor->xmlSerialize(true, $sxe->addChild('requestor'));
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (0 < count($this->policy)) {
            foreach ($this->policy as $policy) {
                $policy->xmlSerialize(true, $sxe->addChild('policy'));
            }
        }
        if (isset($this->media)) {
            $this->media->xmlSerialize(true, $sxe->addChild('media'));
        }
        if (isset($this->network)) {
            $this->network->xmlSerialize(true, $sxe->addChild('network'));
        }
        if (0 < count($this->purposeOfUse)) {
            foreach ($this->purposeOfUse as $purposeOfUse) {
                $purposeOfUse->xmlSerialize(true, $sxe->addChild('purposeOfUse'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
