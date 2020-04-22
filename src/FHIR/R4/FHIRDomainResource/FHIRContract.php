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
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a policy or agreement.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRContract extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Unique identifier for this Contract or a derivative that references a Source Contract.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Canonical identifier for this contract, represented as a URI (globally unique).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * An edition identifier used for business purposes to label business significant variants.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * The status of the resource instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContractResourceStatusCodes
     */
    public $status = null;

    /**
     * Legal states of the formation of a legal instrument, which is a formally executed written document that can be formally attributed to its author, records and formally expresses a legally enforceable act, process, or contractual duty, obligation, or right, and therefore evidences that act, process, or agreement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $legalState = null;

    /**
     * The URL pointing to a FHIR-defined Contract Definition that is adhered to in whole or part by this Contract.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $instantiatesCanonical = null;

    /**
     * The URL pointing to an externally maintained definition that is adhered to in whole or in part by this Contract.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $instantiatesUri = null;

    /**
     * The minimal content derived from the basal information source at a specific stage in its lifecycle.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $contentDerivative = null;

    /**
     * When this  Contract was issued.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $issued = null;

    /**
     * Relevant time or time-period when this Contract is applicable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $applies = null;

    /**
     * Event resulting in discontinuation or termination of this Contract instance by one or more parties to the contract.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $expirationType = null;

    /**
     * The target entity impacted by or of interest to parties to the agreement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $subject = [];

    /**
     * A formally or informally recognized grouping of people, principals, organizations, or jurisdictions formed for the purpose of achieving some form of collective action such as the promulgation, administration and enforcement of contracts and policies.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $authority = [];

    /**
     * Recognized governance framework or system operating with a circumscribed scope in accordance with specified principles, policies, processes or procedures for managing rights, actions, or behaviors of parties or principals relative to resources.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $domain = [];

    /**
     * Sites in which the contract is complied with,  exercised, or in force.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $site = [];

    /**
     * A natural language name identifying this Contract definition, derivative, or instance in any legal state. Provides additional information about its content. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A short, descriptive, user-friendly title for this Contract definition, derivative, or instance in any legal state.t giving additional information about its content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * An explanatory or alternate user-friendly title for this Contract definition, derivative, or instance in any legal state.t giving additional information about its content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $subtitle = null;

    /**
     * Alternative representation of the title for this Contract definition, derivative, or instance in any legal state., e.g., a domain specific contract number related to legislation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $alias = [];

    /**
     * The individual or organization that authored the Contract definition, derivative, or instance in any legal state.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $author = null;

    /**
     * A selector of legal concerns for this Contract definition, derivative, or instance in any legal state.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $scope = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $topicCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $topicReference = null;

    /**
     * A high-level category for the legal instrument, whether constructed as a Contract definition, derivative, or instance in any legal state.  Provides additional information about its content within the context of the Contract's scope to distinguish the kinds of systems that would be interested in the contract.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Sub-category for the Contract that distinguishes the kinds of systems that would be interested in the Contract within the context of the Contract's scope.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $subType = [];

    /**
     * Precusory content developed with a focus and intent of supporting the formation a Contract instance, which may be associated with and transformable into a Contract.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractContentDefinition
     */
    public $contentDefinition = null;

    /**
     * One or more Contract Provisions, which may be related and conveyed as a group, and may contain nested groups.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractTerm[]
     */
    public $term = [];

    /**
     * Information that may be needed by/relevant to the performer in their execution of this term action.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $supportingInfo = [];

    /**
     * Links to Provenance records for past versions of this Contract definition, derivative, or instance, which identify key state transitions or updates that are likely to be relevant to a user looking at the current version of the Contract.  The Provence.entity indicates the target that was changed in the update. http://build.fhir.org/provenance-definitions.html#Provenance.entity.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $relevantHistory = [];

    /**
     * Parties with legal standing in the Contract, including the principal parties, the grantor(s) and grantee(s), which are any person or organization bound by the contract, and any ancillary parties, which facilitate the execution of the contract such as a notary or witness.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractSigner[]
     */
    public $signer = [];

    /**
     * The "patient friendly language" versionof the Contract in whole or in parts. "Patient friendly language" means the representation of the Contract and Contract Provisions in a manner that is readily accessible and understandable by a layperson in accordance with best practices for communication styles that ensure that those agreeing to or signing the Contract understand the roles, actions, obligations, responsibilities, and implication of the agreement.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractFriendly[]
     */
    public $friendly = [];

    /**
     * List of Legal expressions or representations of this Contract.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractLegal[]
     */
    public $legal = [];

    /**
     * List of Computable Policy Rule Language Representations of this Contract.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractRule[]
     */
    public $rule = [];

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public $legallyBindingAttachment = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $legallyBindingReference = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Contract';

    /**
     * Unique identifier for this Contract or a derivative that references a Source Contract.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique identifier for this Contract or a derivative that references a Source Contract.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Canonical identifier for this contract, represented as a URI (globally unique).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Canonical identifier for this contract, represented as a URI (globally unique).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * An edition identifier used for business purposes to label business significant variants.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * An edition identifier used for business purposes to label business significant variants.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * The status of the resource instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContractResourceStatusCodes
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of the resource instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContractResourceStatusCodes $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Legal states of the formation of a legal instrument, which is a formally executed written document that can be formally attributed to its author, records and formally expresses a legally enforceable act, process, or contractual duty, obligation, or right, and therefore evidences that act, process, or agreement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getLegalState()
    {
        return $this->legalState;
    }

    /**
     * Legal states of the formation of a legal instrument, which is a formally executed written document that can be formally attributed to its author, records and formally expresses a legally enforceable act, process, or contractual duty, obligation, or right, and therefore evidences that act, process, or agreement.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $legalState
     * @return $this
     */
    public function setLegalState($legalState)
    {
        $this->legalState = $legalState;
        return $this;
    }

    /**
     * The URL pointing to a FHIR-defined Contract Definition that is adhered to in whole or part by this Contract.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getInstantiatesCanonical()
    {
        return $this->instantiatesCanonical;
    }

    /**
     * The URL pointing to a FHIR-defined Contract Definition that is adhered to in whole or part by this Contract.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $instantiatesCanonical
     * @return $this
     */
    public function setInstantiatesCanonical($instantiatesCanonical)
    {
        $this->instantiatesCanonical = $instantiatesCanonical;
        return $this;
    }

    /**
     * The URL pointing to an externally maintained definition that is adhered to in whole or in part by this Contract.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getInstantiatesUri()
    {
        return $this->instantiatesUri;
    }

    /**
     * The URL pointing to an externally maintained definition that is adhered to in whole or in part by this Contract.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $instantiatesUri
     * @return $this
     */
    public function setInstantiatesUri($instantiatesUri)
    {
        $this->instantiatesUri = $instantiatesUri;
        return $this;
    }

    /**
     * The minimal content derived from the basal information source at a specific stage in its lifecycle.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getContentDerivative()
    {
        return $this->contentDerivative;
    }

    /**
     * The minimal content derived from the basal information source at a specific stage in its lifecycle.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $contentDerivative
     * @return $this
     */
    public function setContentDerivative($contentDerivative)
    {
        $this->contentDerivative = $contentDerivative;
        return $this;
    }

    /**
     * When this  Contract was issued.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * When this  Contract was issued.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $issued
     * @return $this
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;
        return $this;
    }

    /**
     * Relevant time or time-period when this Contract is applicable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getApplies()
    {
        return $this->applies;
    }

    /**
     * Relevant time or time-period when this Contract is applicable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $applies
     * @return $this
     */
    public function setApplies($applies)
    {
        $this->applies = $applies;
        return $this;
    }

    /**
     * Event resulting in discontinuation or termination of this Contract instance by one or more parties to the contract.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getExpirationType()
    {
        return $this->expirationType;
    }

    /**
     * Event resulting in discontinuation or termination of this Contract instance by one or more parties to the contract.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $expirationType
     * @return $this
     */
    public function setExpirationType($expirationType)
    {
        $this->expirationType = $expirationType;
        return $this;
    }

    /**
     * The target entity impacted by or of interest to parties to the agreement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The target entity impacted by or of interest to parties to the agreement.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function addSubject($subject)
    {
        $this->subject[] = $subject;
        return $this;
    }

    /**
     * A formally or informally recognized grouping of people, principals, organizations, or jurisdictions formed for the purpose of achieving some form of collective action such as the promulgation, administration and enforcement of contracts and policies.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * A formally or informally recognized grouping of people, principals, organizations, or jurisdictions formed for the purpose of achieving some form of collective action such as the promulgation, administration and enforcement of contracts and policies.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $authority
     * @return $this
     */
    public function addAuthority($authority)
    {
        $this->authority[] = $authority;
        return $this;
    }

    /**
     * Recognized governance framework or system operating with a circumscribed scope in accordance with specified principles, policies, processes or procedures for managing rights, actions, or behaviors of parties or principals relative to resources.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Recognized governance framework or system operating with a circumscribed scope in accordance with specified principles, policies, processes or procedures for managing rights, actions, or behaviors of parties or principals relative to resources.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $domain
     * @return $this
     */
    public function addDomain($domain)
    {
        $this->domain[] = $domain;
        return $this;
    }

    /**
     * Sites in which the contract is complied with,  exercised, or in force.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Sites in which the contract is complied with,  exercised, or in force.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $site
     * @return $this
     */
    public function addSite($site)
    {
        $this->site[] = $site;
        return $this;
    }

    /**
     * A natural language name identifying this Contract definition, derivative, or instance in any legal state. Provides additional information about its content. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A natural language name identifying this Contract definition, derivative, or instance in any legal state. Provides additional information about its content. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A short, descriptive, user-friendly title for this Contract definition, derivative, or instance in any legal state.t giving additional information about its content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * A short, descriptive, user-friendly title for this Contract definition, derivative, or instance in any legal state.t giving additional information about its content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * An explanatory or alternate user-friendly title for this Contract definition, derivative, or instance in any legal state.t giving additional information about its content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * An explanatory or alternate user-friendly title for this Contract definition, derivative, or instance in any legal state.t giving additional information about its content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $subtitle
     * @return $this
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
     * Alternative representation of the title for this Contract definition, derivative, or instance in any legal state., e.g., a domain specific contract number related to legislation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Alternative representation of the title for this Contract definition, derivative, or instance in any legal state., e.g., a domain specific contract number related to legislation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $alias
     * @return $this
     */
    public function addAlias($alias)
    {
        $this->alias[] = $alias;
        return $this;
    }

    /**
     * The individual or organization that authored the Contract definition, derivative, or instance in any legal state.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * The individual or organization that authored the Contract definition, derivative, or instance in any legal state.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $author
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * A selector of legal concerns for this Contract definition, derivative, or instance in any legal state.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * A selector of legal concerns for this Contract definition, derivative, or instance in any legal state.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getTopicCodeableConcept()
    {
        return $this->topicCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $topicCodeableConcept
     * @return $this
     */
    public function setTopicCodeableConcept($topicCodeableConcept)
    {
        $this->topicCodeableConcept = $topicCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getTopicReference()
    {
        return $this->topicReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $topicReference
     * @return $this
     */
    public function setTopicReference($topicReference)
    {
        $this->topicReference = $topicReference;
        return $this;
    }

    /**
     * A high-level category for the legal instrument, whether constructed as a Contract definition, derivative, or instance in any legal state.  Provides additional information about its content within the context of the Contract's scope to distinguish the kinds of systems that would be interested in the contract.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A high-level category for the legal instrument, whether constructed as a Contract definition, derivative, or instance in any legal state.  Provides additional information about its content within the context of the Contract's scope to distinguish the kinds of systems that would be interested in the contract.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Sub-category for the Contract that distinguishes the kinds of systems that would be interested in the Contract within the context of the Contract's scope.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * Sub-category for the Contract that distinguishes the kinds of systems that would be interested in the Contract within the context of the Contract's scope.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $subType
     * @return $this
     */
    public function addSubType($subType)
    {
        $this->subType[] = $subType;
        return $this;
    }

    /**
     * Precusory content developed with a focus and intent of supporting the formation a Contract instance, which may be associated with and transformable into a Contract.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractContentDefinition
     */
    public function getContentDefinition()
    {
        return $this->contentDefinition;
    }

    /**
     * Precusory content developed with a focus and intent of supporting the formation a Contract instance, which may be associated with and transformable into a Contract.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractContentDefinition $contentDefinition
     * @return $this
     */
    public function setContentDefinition($contentDefinition)
    {
        $this->contentDefinition = $contentDefinition;
        return $this;
    }

    /**
     * One or more Contract Provisions, which may be related and conveyed as a group, and may contain nested groups.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractTerm[]
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * One or more Contract Provisions, which may be related and conveyed as a group, and may contain nested groups.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractTerm $term
     * @return $this
     */
    public function addTerm($term)
    {
        $this->term[] = $term;
        return $this;
    }

    /**
     * Information that may be needed by/relevant to the performer in their execution of this term action.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSupportingInfo()
    {
        return $this->supportingInfo;
    }

    /**
     * Information that may be needed by/relevant to the performer in their execution of this term action.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $supportingInfo
     * @return $this
     */
    public function addSupportingInfo($supportingInfo)
    {
        $this->supportingInfo[] = $supportingInfo;
        return $this;
    }

    /**
     * Links to Provenance records for past versions of this Contract definition, derivative, or instance, which identify key state transitions or updates that are likely to be relevant to a user looking at the current version of the Contract.  The Provence.entity indicates the target that was changed in the update. http://build.fhir.org/provenance-definitions.html#Provenance.entity.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getRelevantHistory()
    {
        return $this->relevantHistory;
    }

    /**
     * Links to Provenance records for past versions of this Contract definition, derivative, or instance, which identify key state transitions or updates that are likely to be relevant to a user looking at the current version of the Contract.  The Provence.entity indicates the target that was changed in the update. http://build.fhir.org/provenance-definitions.html#Provenance.entity.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $relevantHistory
     * @return $this
     */
    public function addRelevantHistory($relevantHistory)
    {
        $this->relevantHistory[] = $relevantHistory;
        return $this;
    }

    /**
     * Parties with legal standing in the Contract, including the principal parties, the grantor(s) and grantee(s), which are any person or organization bound by the contract, and any ancillary parties, which facilitate the execution of the contract such as a notary or witness.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractSigner[]
     */
    public function getSigner()
    {
        return $this->signer;
    }

    /**
     * Parties with legal standing in the Contract, including the principal parties, the grantor(s) and grantee(s), which are any person or organization bound by the contract, and any ancillary parties, which facilitate the execution of the contract such as a notary or witness.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractSigner $signer
     * @return $this
     */
    public function addSigner($signer)
    {
        $this->signer[] = $signer;
        return $this;
    }

    /**
     * The "patient friendly language" versionof the Contract in whole or in parts. "Patient friendly language" means the representation of the Contract and Contract Provisions in a manner that is readily accessible and understandable by a layperson in accordance with best practices for communication styles that ensure that those agreeing to or signing the Contract understand the roles, actions, obligations, responsibilities, and implication of the agreement.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractFriendly[]
     */
    public function getFriendly()
    {
        return $this->friendly;
    }

    /**
     * The "patient friendly language" versionof the Contract in whole or in parts. "Patient friendly language" means the representation of the Contract and Contract Provisions in a manner that is readily accessible and understandable by a layperson in accordance with best practices for communication styles that ensure that those agreeing to or signing the Contract understand the roles, actions, obligations, responsibilities, and implication of the agreement.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractFriendly $friendly
     * @return $this
     */
    public function addFriendly($friendly)
    {
        $this->friendly[] = $friendly;
        return $this;
    }

    /**
     * List of Legal expressions or representations of this Contract.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractLegal[]
     */
    public function getLegal()
    {
        return $this->legal;
    }

    /**
     * List of Legal expressions or representations of this Contract.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractLegal $legal
     * @return $this
     */
    public function addLegal($legal)
    {
        $this->legal[] = $legal;
        return $this;
    }

    /**
     * List of Computable Policy Rule Language Representations of this Contract.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractRule[]
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * List of Computable Policy Rule Language Representations of this Contract.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractRule $rule
     * @return $this
     */
    public function addRule($rule)
    {
        $this->rule[] = $rule;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public function getLegallyBindingAttachment()
    {
        return $this->legallyBindingAttachment;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment $legallyBindingAttachment
     * @return $this
     */
    public function setLegallyBindingAttachment($legallyBindingAttachment)
    {
        $this->legallyBindingAttachment = $legallyBindingAttachment;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getLegallyBindingReference()
    {
        return $this->legallyBindingReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $legallyBindingReference
     * @return $this
     */
    public function setLegallyBindingReference($legallyBindingReference)
    {
        $this->legallyBindingReference = $legallyBindingReference;
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
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
            }
            if (isset($data['version'])) {
                $this->setVersion($data['version']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['legalState'])) {
                $this->setLegalState($data['legalState']);
            }
            if (isset($data['instantiatesCanonical'])) {
                $this->setInstantiatesCanonical($data['instantiatesCanonical']);
            }
            if (isset($data['instantiatesUri'])) {
                $this->setInstantiatesUri($data['instantiatesUri']);
            }
            if (isset($data['contentDerivative'])) {
                $this->setContentDerivative($data['contentDerivative']);
            }
            if (isset($data['issued'])) {
                $this->setIssued($data['issued']);
            }
            if (isset($data['applies'])) {
                $this->setApplies($data['applies']);
            }
            if (isset($data['expirationType'])) {
                $this->setExpirationType($data['expirationType']);
            }
            if (isset($data['subject'])) {
                if (is_array($data['subject'])) {
                    foreach ($data['subject'] as $d) {
                        $this->addSubject($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subject" must be array of objects or null, ' . gettype($data['subject']) . ' seen.');
                }
            }
            if (isset($data['authority'])) {
                if (is_array($data['authority'])) {
                    foreach ($data['authority'] as $d) {
                        $this->addAuthority($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"authority" must be array of objects or null, ' . gettype($data['authority']) . ' seen.');
                }
            }
            if (isset($data['domain'])) {
                if (is_array($data['domain'])) {
                    foreach ($data['domain'] as $d) {
                        $this->addDomain($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"domain" must be array of objects or null, ' . gettype($data['domain']) . ' seen.');
                }
            }
            if (isset($data['site'])) {
                if (is_array($data['site'])) {
                    foreach ($data['site'] as $d) {
                        $this->addSite($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"site" must be array of objects or null, ' . gettype($data['site']) . ' seen.');
                }
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['title'])) {
                $this->setTitle($data['title']);
            }
            if (isset($data['subtitle'])) {
                $this->setSubtitle($data['subtitle']);
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
            if (isset($data['author'])) {
                $this->setAuthor($data['author']);
            }
            if (isset($data['scope'])) {
                $this->setScope($data['scope']);
            }
            if (isset($data['topicCodeableConcept'])) {
                $this->setTopicCodeableConcept($data['topicCodeableConcept']);
            }
            if (isset($data['topicReference'])) {
                $this->setTopicReference($data['topicReference']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['subType'])) {
                if (is_array($data['subType'])) {
                    foreach ($data['subType'] as $d) {
                        $this->addSubType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subType" must be array of objects or null, ' . gettype($data['subType']) . ' seen.');
                }
            }
            if (isset($data['contentDefinition'])) {
                $this->setContentDefinition($data['contentDefinition']);
            }
            if (isset($data['term'])) {
                if (is_array($data['term'])) {
                    foreach ($data['term'] as $d) {
                        $this->addTerm($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"term" must be array of objects or null, ' . gettype($data['term']) . ' seen.');
                }
            }
            if (isset($data['supportingInfo'])) {
                if (is_array($data['supportingInfo'])) {
                    foreach ($data['supportingInfo'] as $d) {
                        $this->addSupportingInfo($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportingInfo" must be array of objects or null, ' . gettype($data['supportingInfo']) . ' seen.');
                }
            }
            if (isset($data['relevantHistory'])) {
                if (is_array($data['relevantHistory'])) {
                    foreach ($data['relevantHistory'] as $d) {
                        $this->addRelevantHistory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"relevantHistory" must be array of objects or null, ' . gettype($data['relevantHistory']) . ' seen.');
                }
            }
            if (isset($data['signer'])) {
                if (is_array($data['signer'])) {
                    foreach ($data['signer'] as $d) {
                        $this->addSigner($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"signer" must be array of objects or null, ' . gettype($data['signer']) . ' seen.');
                }
            }
            if (isset($data['friendly'])) {
                if (is_array($data['friendly'])) {
                    foreach ($data['friendly'] as $d) {
                        $this->addFriendly($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"friendly" must be array of objects or null, ' . gettype($data['friendly']) . ' seen.');
                }
            }
            if (isset($data['legal'])) {
                if (is_array($data['legal'])) {
                    foreach ($data['legal'] as $d) {
                        $this->addLegal($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"legal" must be array of objects or null, ' . gettype($data['legal']) . ' seen.');
                }
            }
            if (isset($data['rule'])) {
                if (is_array($data['rule'])) {
                    foreach ($data['rule'] as $d) {
                        $this->addRule($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"rule" must be array of objects or null, ' . gettype($data['rule']) . ' seen.');
                }
            }
            if (isset($data['legallyBindingAttachment'])) {
                $this->setLegallyBindingAttachment($data['legallyBindingAttachment']);
            }
            if (isset($data['legallyBindingReference'])) {
                $this->setLegallyBindingReference($data['legallyBindingReference']);
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
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (isset($this->version)) {
            $json['version'] = $this->version;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->legalState)) {
            $json['legalState'] = $this->legalState;
        }
        if (isset($this->instantiatesCanonical)) {
            $json['instantiatesCanonical'] = $this->instantiatesCanonical;
        }
        if (isset($this->instantiatesUri)) {
            $json['instantiatesUri'] = $this->instantiatesUri;
        }
        if (isset($this->contentDerivative)) {
            $json['contentDerivative'] = $this->contentDerivative;
        }
        if (isset($this->issued)) {
            $json['issued'] = $this->issued;
        }
        if (isset($this->applies)) {
            $json['applies'] = $this->applies;
        }
        if (isset($this->expirationType)) {
            $json['expirationType'] = $this->expirationType;
        }
        if (0 < count($this->subject)) {
            $json['subject'] = [];
            foreach ($this->subject as $subject) {
                $json['subject'][] = $subject;
            }
        }
        if (0 < count($this->authority)) {
            $json['authority'] = [];
            foreach ($this->authority as $authority) {
                $json['authority'][] = $authority;
            }
        }
        if (0 < count($this->domain)) {
            $json['domain'] = [];
            foreach ($this->domain as $domain) {
                $json['domain'][] = $domain;
            }
        }
        if (0 < count($this->site)) {
            $json['site'] = [];
            foreach ($this->site as $site) {
                $json['site'][] = $site;
            }
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->title)) {
            $json['title'] = $this->title;
        }
        if (isset($this->subtitle)) {
            $json['subtitle'] = $this->subtitle;
        }
        if (0 < count($this->alias)) {
            $json['alias'] = [];
            foreach ($this->alias as $alias) {
                $json['alias'][] = $alias;
            }
        }
        if (isset($this->author)) {
            $json['author'] = $this->author;
        }
        if (isset($this->scope)) {
            $json['scope'] = $this->scope;
        }
        if (isset($this->topicCodeableConcept)) {
            $json['topicCodeableConcept'] = $this->topicCodeableConcept;
        }
        if (isset($this->topicReference)) {
            $json['topicReference'] = $this->topicReference;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->subType)) {
            $json['subType'] = [];
            foreach ($this->subType as $subType) {
                $json['subType'][] = $subType;
            }
        }
        if (isset($this->contentDefinition)) {
            $json['contentDefinition'] = $this->contentDefinition;
        }
        if (0 < count($this->term)) {
            $json['term'] = [];
            foreach ($this->term as $term) {
                $json['term'][] = $term;
            }
        }
        if (0 < count($this->supportingInfo)) {
            $json['supportingInfo'] = [];
            foreach ($this->supportingInfo as $supportingInfo) {
                $json['supportingInfo'][] = $supportingInfo;
            }
        }
        if (0 < count($this->relevantHistory)) {
            $json['relevantHistory'] = [];
            foreach ($this->relevantHistory as $relevantHistory) {
                $json['relevantHistory'][] = $relevantHistory;
            }
        }
        if (0 < count($this->signer)) {
            $json['signer'] = [];
            foreach ($this->signer as $signer) {
                $json['signer'][] = $signer;
            }
        }
        if (0 < count($this->friendly)) {
            $json['friendly'] = [];
            foreach ($this->friendly as $friendly) {
                $json['friendly'][] = $friendly;
            }
        }
        if (0 < count($this->legal)) {
            $json['legal'] = [];
            foreach ($this->legal as $legal) {
                $json['legal'][] = $legal;
            }
        }
        if (0 < count($this->rule)) {
            $json['rule'] = [];
            foreach ($this->rule as $rule) {
                $json['rule'][] = $rule;
            }
        }
        if (isset($this->legallyBindingAttachment)) {
            $json['legallyBindingAttachment'] = $this->legallyBindingAttachment;
        }
        if (isset($this->legallyBindingReference)) {
            $json['legallyBindingReference'] = $this->legallyBindingReference;
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
            $sxe = new \SimpleXMLElement('<Contract xmlns="http://hl7.org/fhir"></Contract>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (isset($this->version)) {
            $this->version->xmlSerialize(true, $sxe->addChild('version'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->legalState)) {
            $this->legalState->xmlSerialize(true, $sxe->addChild('legalState'));
        }
        if (isset($this->instantiatesCanonical)) {
            $this->instantiatesCanonical->xmlSerialize(true, $sxe->addChild('instantiatesCanonical'));
        }
        if (isset($this->instantiatesUri)) {
            $this->instantiatesUri->xmlSerialize(true, $sxe->addChild('instantiatesUri'));
        }
        if (isset($this->contentDerivative)) {
            $this->contentDerivative->xmlSerialize(true, $sxe->addChild('contentDerivative'));
        }
        if (isset($this->issued)) {
            $this->issued->xmlSerialize(true, $sxe->addChild('issued'));
        }
        if (isset($this->applies)) {
            $this->applies->xmlSerialize(true, $sxe->addChild('applies'));
        }
        if (isset($this->expirationType)) {
            $this->expirationType->xmlSerialize(true, $sxe->addChild('expirationType'));
        }
        if (0 < count($this->subject)) {
            foreach ($this->subject as $subject) {
                $subject->xmlSerialize(true, $sxe->addChild('subject'));
            }
        }
        if (0 < count($this->authority)) {
            foreach ($this->authority as $authority) {
                $authority->xmlSerialize(true, $sxe->addChild('authority'));
            }
        }
        if (0 < count($this->domain)) {
            foreach ($this->domain as $domain) {
                $domain->xmlSerialize(true, $sxe->addChild('domain'));
            }
        }
        if (0 < count($this->site)) {
            foreach ($this->site as $site) {
                $site->xmlSerialize(true, $sxe->addChild('site'));
            }
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->title)) {
            $this->title->xmlSerialize(true, $sxe->addChild('title'));
        }
        if (isset($this->subtitle)) {
            $this->subtitle->xmlSerialize(true, $sxe->addChild('subtitle'));
        }
        if (0 < count($this->alias)) {
            foreach ($this->alias as $alias) {
                $alias->xmlSerialize(true, $sxe->addChild('alias'));
            }
        }
        if (isset($this->author)) {
            $this->author->xmlSerialize(true, $sxe->addChild('author'));
        }
        if (isset($this->scope)) {
            $this->scope->xmlSerialize(true, $sxe->addChild('scope'));
        }
        if (isset($this->topicCodeableConcept)) {
            $this->topicCodeableConcept->xmlSerialize(true, $sxe->addChild('topicCodeableConcept'));
        }
        if (isset($this->topicReference)) {
            $this->topicReference->xmlSerialize(true, $sxe->addChild('topicReference'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->subType)) {
            foreach ($this->subType as $subType) {
                $subType->xmlSerialize(true, $sxe->addChild('subType'));
            }
        }
        if (isset($this->contentDefinition)) {
            $this->contentDefinition->xmlSerialize(true, $sxe->addChild('contentDefinition'));
        }
        if (0 < count($this->term)) {
            foreach ($this->term as $term) {
                $term->xmlSerialize(true, $sxe->addChild('term'));
            }
        }
        if (0 < count($this->supportingInfo)) {
            foreach ($this->supportingInfo as $supportingInfo) {
                $supportingInfo->xmlSerialize(true, $sxe->addChild('supportingInfo'));
            }
        }
        if (0 < count($this->relevantHistory)) {
            foreach ($this->relevantHistory as $relevantHistory) {
                $relevantHistory->xmlSerialize(true, $sxe->addChild('relevantHistory'));
            }
        }
        if (0 < count($this->signer)) {
            foreach ($this->signer as $signer) {
                $signer->xmlSerialize(true, $sxe->addChild('signer'));
            }
        }
        if (0 < count($this->friendly)) {
            foreach ($this->friendly as $friendly) {
                $friendly->xmlSerialize(true, $sxe->addChild('friendly'));
            }
        }
        if (0 < count($this->legal)) {
            foreach ($this->legal as $legal) {
                $legal->xmlSerialize(true, $sxe->addChild('legal'));
            }
        }
        if (0 < count($this->rule)) {
            foreach ($this->rule as $rule) {
                $rule->xmlSerialize(true, $sxe->addChild('rule'));
            }
        }
        if (isset($this->legallyBindingAttachment)) {
            $this->legallyBindingAttachment->xmlSerialize(true, $sxe->addChild('legallyBindingAttachment'));
        }
        if (isset($this->legallyBindingReference)) {
            $this->legallyBindingReference->xmlSerialize(true, $sxe->addChild('legallyBindingReference'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
