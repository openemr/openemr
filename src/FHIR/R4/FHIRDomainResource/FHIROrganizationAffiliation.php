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
 * Defines an affiliation/assotiation/relationship between 2 distinct oganizations, that is not a part-of relationship/sub-division relationship.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIROrganizationAffiliation extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Business identifiers that are specific to this role.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Whether this organization affiliation record is in active use.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $active = null;

    /**
     * The period during which the participatingOrganization is affiliated with the primary organization.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * Organization where the role is available (primary organization/has members).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $organization = null;

    /**
     * The Participating Organization provides/performs the role(s) defined by the code to the Primary Organization (e.g. providing services or is a member of).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $participatingOrganization = null;

    /**
     * Health insurance provider network in which the participatingOrganization provides the role's services (if defined) at the indicated locations (if defined).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $network = [];

    /**
     * Definition of the role the participatingOrganization plays in the association.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $code = [];

    /**
     * Specific specialty of the participatingOrganization in the context of the role.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $specialty = [];

    /**
     * The location(s) at which the role occurs.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $location = [];

    /**
     * Healthcare services provided through the role.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $healthcareService = [];

    /**
     * Contact details at the participatingOrganization relevant to this Affiliation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public $telecom = [];

    /**
     * Technical endpoints providing access to services operated for this role.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $endpoint = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'OrganizationAffiliation';

    /**
     * Business identifiers that are specific to this role.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifiers that are specific to this role.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Whether this organization affiliation record is in active use.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Whether this organization affiliation record is in active use.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * The period during which the participatingOrganization is affiliated with the primary organization.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The period during which the participatingOrganization is affiliated with the primary organization.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * Organization where the role is available (primary organization/has members).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Organization where the role is available (primary organization/has members).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $organization
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * The Participating Organization provides/performs the role(s) defined by the code to the Primary Organization (e.g. providing services or is a member of).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getParticipatingOrganization()
    {
        return $this->participatingOrganization;
    }

    /**
     * The Participating Organization provides/performs the role(s) defined by the code to the Primary Organization (e.g. providing services or is a member of).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $participatingOrganization
     * @return $this
     */
    public function setParticipatingOrganization($participatingOrganization)
    {
        $this->participatingOrganization = $participatingOrganization;
        return $this;
    }

    /**
     * Health insurance provider network in which the participatingOrganization provides the role's services (if defined) at the indicated locations (if defined).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Health insurance provider network in which the participatingOrganization provides the role's services (if defined) at the indicated locations (if defined).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $network
     * @return $this
     */
    public function addNetwork($network)
    {
        $this->network[] = $network;
        return $this;
    }

    /**
     * Definition of the role the participatingOrganization plays in the association.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Definition of the role the participatingOrganization plays in the association.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function addCode($code)
    {
        $this->code[] = $code;
        return $this;
    }

    /**
     * Specific specialty of the participatingOrganization in the context of the role.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }

    /**
     * Specific specialty of the participatingOrganization in the context of the role.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $specialty
     * @return $this
     */
    public function addSpecialty($specialty)
    {
        $this->specialty[] = $specialty;
        return $this;
    }

    /**
     * The location(s) at which the role occurs.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The location(s) at which the role occurs.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function addLocation($location)
    {
        $this->location[] = $location;
        return $this;
    }

    /**
     * Healthcare services provided through the role.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getHealthcareService()
    {
        return $this->healthcareService;
    }

    /**
     * Healthcare services provided through the role.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $healthcareService
     * @return $this
     */
    public function addHealthcareService($healthcareService)
    {
        $this->healthcareService[] = $healthcareService;
        return $this;
    }

    /**
     * Contact details at the participatingOrganization relevant to this Affiliation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public function getTelecom()
    {
        return $this->telecom;
    }

    /**
     * Contact details at the participatingOrganization relevant to this Affiliation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint $telecom
     * @return $this
     */
    public function addTelecom($telecom)
    {
        $this->telecom[] = $telecom;
        return $this;
    }

    /**
     * Technical endpoints providing access to services operated for this role.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Technical endpoints providing access to services operated for this role.
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
            if (isset($data['active'])) {
                $this->setActive($data['active']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['organization'])) {
                $this->setOrganization($data['organization']);
            }
            if (isset($data['participatingOrganization'])) {
                $this->setParticipatingOrganization($data['participatingOrganization']);
            }
            if (isset($data['network'])) {
                if (is_array($data['network'])) {
                    foreach ($data['network'] as $d) {
                        $this->addNetwork($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"network" must be array of objects or null, ' . gettype($data['network']) . ' seen.');
                }
            }
            if (isset($data['code'])) {
                if (is_array($data['code'])) {
                    foreach ($data['code'] as $d) {
                        $this->addCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"code" must be array of objects or null, ' . gettype($data['code']) . ' seen.');
                }
            }
            if (isset($data['specialty'])) {
                if (is_array($data['specialty'])) {
                    foreach ($data['specialty'] as $d) {
                        $this->addSpecialty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specialty" must be array of objects or null, ' . gettype($data['specialty']) . ' seen.');
                }
            }
            if (isset($data['location'])) {
                if (is_array($data['location'])) {
                    foreach ($data['location'] as $d) {
                        $this->addLocation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"location" must be array of objects or null, ' . gettype($data['location']) . ' seen.');
                }
            }
            if (isset($data['healthcareService'])) {
                if (is_array($data['healthcareService'])) {
                    foreach ($data['healthcareService'] as $d) {
                        $this->addHealthcareService($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"healthcareService" must be array of objects or null, ' . gettype($data['healthcareService']) . ' seen.');
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
        if (isset($this->active)) {
            $json['active'] = $this->active;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->organization)) {
            $json['organization'] = $this->organization;
        }
        if (isset($this->participatingOrganization)) {
            $json['participatingOrganization'] = $this->participatingOrganization;
        }
        if (0 < count($this->network)) {
            $json['network'] = [];
            foreach ($this->network as $network) {
                $json['network'][] = $network;
            }
        }
        if (0 < count($this->code)) {
            $json['code'] = [];
            foreach ($this->code as $code) {
                $json['code'][] = $code;
            }
        }
        if (0 < count($this->specialty)) {
            $json['specialty'] = [];
            foreach ($this->specialty as $specialty) {
                $json['specialty'][] = $specialty;
            }
        }
        if (0 < count($this->location)) {
            $json['location'] = [];
            foreach ($this->location as $location) {
                $json['location'][] = $location;
            }
        }
        if (0 < count($this->healthcareService)) {
            $json['healthcareService'] = [];
            foreach ($this->healthcareService as $healthcareService) {
                $json['healthcareService'][] = $healthcareService;
            }
        }
        if (0 < count($this->telecom)) {
            $json['telecom'] = [];
            foreach ($this->telecom as $telecom) {
                $json['telecom'][] = $telecom;
            }
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
            $sxe = new \SimpleXMLElement('<OrganizationAffiliation xmlns="http://hl7.org/fhir"></OrganizationAffiliation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->active)) {
            $this->active->xmlSerialize(true, $sxe->addChild('active'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->organization)) {
            $this->organization->xmlSerialize(true, $sxe->addChild('organization'));
        }
        if (isset($this->participatingOrganization)) {
            $this->participatingOrganization->xmlSerialize(true, $sxe->addChild('participatingOrganization'));
        }
        if (0 < count($this->network)) {
            foreach ($this->network as $network) {
                $network->xmlSerialize(true, $sxe->addChild('network'));
            }
        }
        if (0 < count($this->code)) {
            foreach ($this->code as $code) {
                $code->xmlSerialize(true, $sxe->addChild('code'));
            }
        }
        if (0 < count($this->specialty)) {
            foreach ($this->specialty as $specialty) {
                $specialty->xmlSerialize(true, $sxe->addChild('specialty'));
            }
        }
        if (0 < count($this->location)) {
            foreach ($this->location as $location) {
                $location->xmlSerialize(true, $sxe->addChild('location'));
            }
        }
        if (0 < count($this->healthcareService)) {
            foreach ($this->healthcareService as $healthcareService) {
                $healthcareService->xmlSerialize(true, $sxe->addChild('healthcareService'));
            }
        }
        if (0 < count($this->telecom)) {
            foreach ($this->telecom as $telecom) {
                $telecom->xmlSerialize(true, $sxe->addChild('telecom'));
            }
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
