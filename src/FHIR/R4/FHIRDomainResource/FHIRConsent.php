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
 * A record of a healthcare consumer’s  choices, which permits or denies identified recipient(s) or recipient role(s) to perform one or more actions within a given policy context, for specific purposes and periods of time.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRConsent extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Unique identifier for this copy of the Consent Statement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Indicates the current state of this consent.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRConsentState
     */
    public $status = null;

    /**
     * A selector of the type of consent being presented: ADR, Privacy, Treatment, Research.  This list is now extensible.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $scope = null;

    /**
     * A classification of the type of consents found in the statement. This element supports indexing and retrieval of consent statements.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $category = [];

    /**
     * The patient/healthcare consumer to whom this consent applies.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * When this  Consent was issued / created / indexed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $dateTime = null;

    /**
     * Either the Grantor, which is the entity responsible for granting the rights listed in a Consent Directive or the Grantee, which is the entity responsible for complying with the Consent Directive, including any obligations or limitations on authorizations and enforcement of prohibitions.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $performer = [];

    /**
     * The organization that manages the consent, and the framework within which it is executed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $organization = [];

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public $sourceAttachment = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $sourceReference = null;

    /**
     * The references to the policies that are included in this consent scope. Policies may be organizational, but are often defined jurisdictionally, or in law.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentPolicy[]
     */
    public $policy = [];

    /**
     * A reference to the specific base computable regulation or policy.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $policyRule = null;

    /**
     * Whether a treatment instruction (e.g. artificial respiration yes or no) was verified with the patient, his/her family or another authorized person.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentVerification[]
     */
    public $verification = [];

    /**
     * An exception to the base policy of this consent. An exception can be an addition or removal of access permissions.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentProvision
     */
    public $provision = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Consent';

    /**
     * Unique identifier for this copy of the Consent Statement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique identifier for this copy of the Consent Statement.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Indicates the current state of this consent.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRConsentState
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Indicates the current state of this consent.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRConsentState $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A selector of the type of consent being presented: ADR, Privacy, Treatment, Research.  This list is now extensible.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * A selector of the type of consent being presented: ADR, Privacy, Treatment, Research.  This list is now extensible.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * A classification of the type of consents found in the statement. This element supports indexing and retrieval of consent statements.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * A classification of the type of consents found in the statement. This element supports indexing and retrieval of consent statements.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->category[] = $category;
        return $this;
    }

    /**
     * The patient/healthcare consumer to whom this consent applies.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * The patient/healthcare consumer to whom this consent applies.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * When this  Consent was issued / created / indexed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * When this  Consent was issued / created / indexed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $dateTime
     * @return $this
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    /**
     * Either the Grantor, which is the entity responsible for granting the rights listed in a Consent Directive or the Grantee, which is the entity responsible for complying with the Consent Directive, including any obligations or limitations on authorizations and enforcement of prohibitions.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * Either the Grantor, which is the entity responsible for granting the rights listed in a Consent Directive or the Grantee, which is the entity responsible for complying with the Consent Directive, including any obligations or limitations on authorizations and enforcement of prohibitions.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $performer
     * @return $this
     */
    public function addPerformer($performer)
    {
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * The organization that manages the consent, and the framework within which it is executed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * The organization that manages the consent, and the framework within which it is executed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $organization
     * @return $this
     */
    public function addOrganization($organization)
    {
        $this->organization[] = $organization;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public function getSourceAttachment()
    {
        return $this->sourceAttachment;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment $sourceAttachment
     * @return $this
     */
    public function setSourceAttachment($sourceAttachment)
    {
        $this->sourceAttachment = $sourceAttachment;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSourceReference()
    {
        return $this->sourceReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $sourceReference
     * @return $this
     */
    public function setSourceReference($sourceReference)
    {
        $this->sourceReference = $sourceReference;
        return $this;
    }

    /**
     * The references to the policies that are included in this consent scope. Policies may be organizational, but are often defined jurisdictionally, or in law.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentPolicy[]
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * The references to the policies that are included in this consent scope. Policies may be organizational, but are often defined jurisdictionally, or in law.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentPolicy $policy
     * @return $this
     */
    public function addPolicy($policy)
    {
        $this->policy[] = $policy;
        return $this;
    }

    /**
     * A reference to the specific base computable regulation or policy.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getPolicyRule()
    {
        return $this->policyRule;
    }

    /**
     * A reference to the specific base computable regulation or policy.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $policyRule
     * @return $this
     */
    public function setPolicyRule($policyRule)
    {
        $this->policyRule = $policyRule;
        return $this;
    }

    /**
     * Whether a treatment instruction (e.g. artificial respiration yes or no) was verified with the patient, his/her family or another authorized person.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentVerification[]
     */
    public function getVerification()
    {
        return $this->verification;
    }

    /**
     * Whether a treatment instruction (e.g. artificial respiration yes or no) was verified with the patient, his/her family or another authorized person.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentVerification $verification
     * @return $this
     */
    public function addVerification($verification)
    {
        $this->verification[] = $verification;
        return $this;
    }

    /**
     * An exception to the base policy of this consent. An exception can be an addition or removal of access permissions.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentProvision
     */
    public function getProvision()
    {
        return $this->provision;
    }

    /**
     * An exception to the base policy of this consent. An exception can be an addition or removal of access permissions.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentProvision $provision
     * @return $this
     */
    public function setProvision($provision)
    {
        $this->provision = $provision;
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
            if (isset($data['scope'])) {
                $this->setScope($data['scope']);
            }
            if (isset($data['category'])) {
                if (is_array($data['category'])) {
                    foreach ($data['category'] as $d) {
                        $this->addCategory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"category" must be array of objects or null, ' . gettype($data['category']) . ' seen.');
                }
            }
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
            }
            if (isset($data['dateTime'])) {
                $this->setDateTime($data['dateTime']);
            }
            if (isset($data['performer'])) {
                if (is_array($data['performer'])) {
                    foreach ($data['performer'] as $d) {
                        $this->addPerformer($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"performer" must be array of objects or null, ' . gettype($data['performer']) . ' seen.');
                }
            }
            if (isset($data['organization'])) {
                if (is_array($data['organization'])) {
                    foreach ($data['organization'] as $d) {
                        $this->addOrganization($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"organization" must be array of objects or null, ' . gettype($data['organization']) . ' seen.');
                }
            }
            if (isset($data['sourceAttachment'])) {
                $this->setSourceAttachment($data['sourceAttachment']);
            }
            if (isset($data['sourceReference'])) {
                $this->setSourceReference($data['sourceReference']);
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
            if (isset($data['policyRule'])) {
                $this->setPolicyRule($data['policyRule']);
            }
            if (isset($data['verification'])) {
                if (is_array($data['verification'])) {
                    foreach ($data['verification'] as $d) {
                        $this->addVerification($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"verification" must be array of objects or null, ' . gettype($data['verification']) . ' seen.');
                }
            }
            if (isset($data['provision'])) {
                $this->setProvision($data['provision']);
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
        if (isset($this->scope)) {
            $json['scope'] = $this->scope;
        }
        if (0 < count($this->category)) {
            $json['category'] = [];
            foreach ($this->category as $category) {
                $json['category'][] = $category;
            }
        }
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
        }
        if (isset($this->dateTime)) {
            $json['dateTime'] = $this->dateTime;
        }
        if (0 < count($this->performer)) {
            $json['performer'] = [];
            foreach ($this->performer as $performer) {
                $json['performer'][] = $performer;
            }
        }
        if (0 < count($this->organization)) {
            $json['organization'] = [];
            foreach ($this->organization as $organization) {
                $json['organization'][] = $organization;
            }
        }
        if (isset($this->sourceAttachment)) {
            $json['sourceAttachment'] = $this->sourceAttachment;
        }
        if (isset($this->sourceReference)) {
            $json['sourceReference'] = $this->sourceReference;
        }
        if (0 < count($this->policy)) {
            $json['policy'] = [];
            foreach ($this->policy as $policy) {
                $json['policy'][] = $policy;
            }
        }
        if (isset($this->policyRule)) {
            $json['policyRule'] = $this->policyRule;
        }
        if (0 < count($this->verification)) {
            $json['verification'] = [];
            foreach ($this->verification as $verification) {
                $json['verification'][] = $verification;
            }
        }
        if (isset($this->provision)) {
            $json['provision'] = $this->provision;
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
            $sxe = new \SimpleXMLElement('<Consent xmlns="http://hl7.org/fhir"></Consent>');
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
        if (isset($this->scope)) {
            $this->scope->xmlSerialize(true, $sxe->addChild('scope'));
        }
        if (0 < count($this->category)) {
            foreach ($this->category as $category) {
                $category->xmlSerialize(true, $sxe->addChild('category'));
            }
        }
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (isset($this->dateTime)) {
            $this->dateTime->xmlSerialize(true, $sxe->addChild('dateTime'));
        }
        if (0 < count($this->performer)) {
            foreach ($this->performer as $performer) {
                $performer->xmlSerialize(true, $sxe->addChild('performer'));
            }
        }
        if (0 < count($this->organization)) {
            foreach ($this->organization as $organization) {
                $organization->xmlSerialize(true, $sxe->addChild('organization'));
            }
        }
        if (isset($this->sourceAttachment)) {
            $this->sourceAttachment->xmlSerialize(true, $sxe->addChild('sourceAttachment'));
        }
        if (isset($this->sourceReference)) {
            $this->sourceReference->xmlSerialize(true, $sxe->addChild('sourceReference'));
        }
        if (0 < count($this->policy)) {
            foreach ($this->policy as $policy) {
                $policy->xmlSerialize(true, $sxe->addChild('policy'));
            }
        }
        if (isset($this->policyRule)) {
            $this->policyRule->xmlSerialize(true, $sxe->addChild('policyRule'));
        }
        if (0 < count($this->verification)) {
            foreach ($this->verification as $verification) {
                $verification->xmlSerialize(true, $sxe->addChild('verification'));
            }
        }
        if (isset($this->provision)) {
            $this->provision->xmlSerialize(true, $sxe->addChild('provision'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
