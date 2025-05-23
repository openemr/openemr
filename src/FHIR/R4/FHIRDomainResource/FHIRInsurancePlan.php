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
 * Details of a Health Insurance product/plan provided by an organization.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRInsurancePlan extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Business identifiers assigned to this health insurance product which remain constant as the resource is updated and propagates from server to server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The current state of the health insurance product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public $status = null;

    /**
     * The kind of health insurance product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $type = [];

    /**
     * Official name of the health insurance product as designated by the owner.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A list of alternate names that the product is known as, or was known as in the past.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $alias = [];

    /**
     * The period of time that the health insurance product is available.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * The entity that is providing  the health insurance product and underwriting the risk.  This is typically an insurance carriers, other third-party payers, or health plan sponsors comonly referred to as 'payers'.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $ownedBy = null;

    /**
     * An organization which administer other services such as underwriting, customer service and/or claims processing on behalf of the health insurance product owner.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $administeredBy = null;

    /**
     * The geographic region in which a health insurance product's benefits apply.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $coverageArea = [];

    /**
     * The contact for the health insurance product for a certain purpose.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanContact[]
     */
    public $contact = [];

    /**
     * The technical endpoints providing access to services operated for the health insurance product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $endpoint = [];

    /**
     * Reference to the network included in the health insurance product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $network = [];

    /**
     * Details about the coverage offered by the insurance product.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanCoverage[]
     */
    public $coverage = [];

    /**
     * Details about an insurance plan.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanPlan[]
     */
    public $plan = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'InsurancePlan';

    /**
     * Business identifiers assigned to this health insurance product which remain constant as the resource is updated and propagates from server to server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifiers assigned to this health insurance product which remain constant as the resource is updated and propagates from server to server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The current state of the health insurance product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The current state of the health insurance product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The kind of health insurance product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The kind of health insurance product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * Official name of the health insurance product as designated by the owner.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Official name of the health insurance product as designated by the owner.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A list of alternate names that the product is known as, or was known as in the past.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * A list of alternate names that the product is known as, or was known as in the past.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $alias
     * @return $this
     */
    public function addAlias($alias)
    {
        $this->alias[] = $alias;
        return $this;
    }

    /**
     * The period of time that the health insurance product is available.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The period of time that the health insurance product is available.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * The entity that is providing  the health insurance product and underwriting the risk.  This is typically an insurance carriers, other third-party payers, or health plan sponsors comonly referred to as 'payers'.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getOwnedBy()
    {
        return $this->ownedBy;
    }

    /**
     * The entity that is providing  the health insurance product and underwriting the risk.  This is typically an insurance carriers, other third-party payers, or health plan sponsors comonly referred to as 'payers'.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $ownedBy
     * @return $this
     */
    public function setOwnedBy($ownedBy)
    {
        $this->ownedBy = $ownedBy;
        return $this;
    }

    /**
     * An organization which administer other services such as underwriting, customer service and/or claims processing on behalf of the health insurance product owner.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAdministeredBy()
    {
        return $this->administeredBy;
    }

    /**
     * An organization which administer other services such as underwriting, customer service and/or claims processing on behalf of the health insurance product owner.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $administeredBy
     * @return $this
     */
    public function setAdministeredBy($administeredBy)
    {
        $this->administeredBy = $administeredBy;
        return $this;
    }

    /**
     * The geographic region in which a health insurance product's benefits apply.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getCoverageArea()
    {
        return $this->coverageArea;
    }

    /**
     * The geographic region in which a health insurance product's benefits apply.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $coverageArea
     * @return $this
     */
    public function addCoverageArea($coverageArea)
    {
        $this->coverageArea[] = $coverageArea;
        return $this;
    }

    /**
     * The contact for the health insurance product for a certain purpose.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanContact[]
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * The contact for the health insurance product for a certain purpose.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanContact $contact
     * @return $this
     */
    public function addContact($contact)
    {
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * The technical endpoints providing access to services operated for the health insurance product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * The technical endpoints providing access to services operated for the health insurance product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $endpoint
     * @return $this
     */
    public function addEndpoint($endpoint)
    {
        $this->endpoint[] = $endpoint;
        return $this;
    }

    /**
     * Reference to the network included in the health insurance product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Reference to the network included in the health insurance product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $network
     * @return $this
     */
    public function addNetwork($network)
    {
        $this->network[] = $network;
        return $this;
    }

    /**
     * Details about the coverage offered by the insurance product.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanCoverage[]
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * Details about the coverage offered by the insurance product.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanCoverage $coverage
     * @return $this
     */
    public function addCoverage($coverage)
    {
        $this->coverage[] = $coverage;
        return $this;
    }

    /**
     * Details about an insurance plan.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanPlan[]
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Details about an insurance plan.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanPlan $plan
     * @return $this
     */
    public function addPlan($plan)
    {
        $this->plan[] = $plan;
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
            if (isset($data['type'])) {
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, ' . gettype($data['type']) . ' seen.');
                }
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
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['ownedBy'])) {
                $this->setOwnedBy($data['ownedBy']);
            }
            if (isset($data['administeredBy'])) {
                $this->setAdministeredBy($data['administeredBy']);
            }
            if (isset($data['coverageArea'])) {
                if (is_array($data['coverageArea'])) {
                    foreach ($data['coverageArea'] as $d) {
                        $this->addCoverageArea($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"coverageArea" must be array of objects or null, ' . gettype($data['coverageArea']) . ' seen.');
                }
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
            if (isset($data['endpoint'])) {
                if (is_array($data['endpoint'])) {
                    foreach ($data['endpoint'] as $d) {
                        $this->addEndpoint($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"endpoint" must be array of objects or null, ' . gettype($data['endpoint']) . ' seen.');
                }
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
            if (isset($data['coverage'])) {
                if (is_array($data['coverage'])) {
                    foreach ($data['coverage'] as $d) {
                        $this->addCoverage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"coverage" must be array of objects or null, ' . gettype($data['coverage']) . ' seen.');
                }
            }
            if (isset($data['plan'])) {
                if (is_array($data['plan'])) {
                    foreach ($data['plan'] as $d) {
                        $this->addPlan($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"plan" must be array of objects or null, ' . gettype($data['plan']) . ' seen.');
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
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
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
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->ownedBy)) {
            $json['ownedBy'] = $this->ownedBy;
        }
        if (isset($this->administeredBy)) {
            $json['administeredBy'] = $this->administeredBy;
        }
        if (0 < count($this->coverageArea)) {
            $json['coverageArea'] = [];
            foreach ($this->coverageArea as $coverageArea) {
                $json['coverageArea'][] = $coverageArea;
            }
        }
        if (0 < count($this->contact)) {
            $json['contact'] = [];
            foreach ($this->contact as $contact) {
                $json['contact'][] = $contact;
            }
        }
        if (0 < count($this->endpoint)) {
            $json['endpoint'] = [];
            foreach ($this->endpoint as $endpoint) {
                $json['endpoint'][] = $endpoint;
            }
        }
        if (0 < count($this->network)) {
            $json['network'] = [];
            foreach ($this->network as $network) {
                $json['network'][] = $network;
            }
        }
        if (0 < count($this->coverage)) {
            $json['coverage'] = [];
            foreach ($this->coverage as $coverage) {
                $json['coverage'][] = $coverage;
            }
        }
        if (0 < count($this->plan)) {
            $json['plan'] = [];
            foreach ($this->plan as $plan) {
                $json['plan'][] = $plan;
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
            $sxe = new \SimpleXMLElement('<InsurancePlan xmlns="http://hl7.org/fhir"></InsurancePlan>');
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
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (0 < count($this->alias)) {
            foreach ($this->alias as $alias) {
                $alias->xmlSerialize(true, $sxe->addChild('alias'));
            }
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->ownedBy)) {
            $this->ownedBy->xmlSerialize(true, $sxe->addChild('ownedBy'));
        }
        if (isset($this->administeredBy)) {
            $this->administeredBy->xmlSerialize(true, $sxe->addChild('administeredBy'));
        }
        if (0 < count($this->coverageArea)) {
            foreach ($this->coverageArea as $coverageArea) {
                $coverageArea->xmlSerialize(true, $sxe->addChild('coverageArea'));
            }
        }
        if (0 < count($this->contact)) {
            foreach ($this->contact as $contact) {
                $contact->xmlSerialize(true, $sxe->addChild('contact'));
            }
        }
        if (0 < count($this->endpoint)) {
            foreach ($this->endpoint as $endpoint) {
                $endpoint->xmlSerialize(true, $sxe->addChild('endpoint'));
            }
        }
        if (0 < count($this->network)) {
            foreach ($this->network as $network) {
                $network->xmlSerialize(true, $sxe->addChild('network'));
            }
        }
        if (0 < count($this->coverage)) {
            foreach ($this->coverage as $coverage) {
                $coverage->xmlSerialize(true, $sxe->addChild('coverage'));
            }
        }
        if (0 < count($this->plan)) {
            foreach ($this->plan as $plan) {
                $plan->xmlSerialize(true, $sxe->addChild('plan'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
