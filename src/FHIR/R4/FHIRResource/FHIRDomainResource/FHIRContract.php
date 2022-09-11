<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: September 10th, 2022 20:42+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2022 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

use OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContractResourceStatusCodes;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeMap;

/**
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
 * policy or agreement.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 *
 * Class FHIRContract
 * @package \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource
 */
class FHIRContract extends FHIRDomainResource implements PHPFHIRContainedTypeInterface
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_CONTRACT;
    const FIELD_IDENTIFIER = 'identifier';
    const FIELD_URL = 'url';
    const FIELD_URL_EXT = '_url';
    const FIELD_VERSION = 'version';
    const FIELD_VERSION_EXT = '_version';
    const FIELD_STATUS = 'status';
    const FIELD_STATUS_EXT = '_status';
    const FIELD_LEGAL_STATE = 'legalState';
    const FIELD_INSTANTIATES_CANONICAL = 'instantiatesCanonical';
    const FIELD_INSTANTIATES_URI = 'instantiatesUri';
    const FIELD_INSTANTIATES_URI_EXT = '_instantiatesUri';
    const FIELD_CONTENT_DERIVATIVE = 'contentDerivative';
    const FIELD_ISSUED = 'issued';
    const FIELD_ISSUED_EXT = '_issued';
    const FIELD_APPLIES = 'applies';
    const FIELD_EXPIRATION_TYPE = 'expirationType';
    const FIELD_SUBJECT = 'subject';
    const FIELD_AUTHORITY = 'authority';
    const FIELD_DOMAIN = 'domain';
    const FIELD_SITE = 'site';
    const FIELD_NAME = 'name';
    const FIELD_NAME_EXT = '_name';
    const FIELD_TITLE = 'title';
    const FIELD_TITLE_EXT = '_title';
    const FIELD_SUBTITLE = 'subtitle';
    const FIELD_SUBTITLE_EXT = '_subtitle';
    const FIELD_ALIAS = 'alias';
    const FIELD_ALIAS_EXT = '_alias';
    const FIELD_AUTHOR = 'author';
    const FIELD_SCOPE = 'scope';
    const FIELD_TOPIC_CODEABLE_CONCEPT = 'topicCodeableConcept';
    const FIELD_TOPIC_REFERENCE = 'topicReference';
    const FIELD_TYPE = 'type';
    const FIELD_SUB_TYPE = 'subType';
    const FIELD_CONTENT_DEFINITION = 'contentDefinition';
    const FIELD_TERM = 'term';
    const FIELD_SUPPORTING_INFO = 'supportingInfo';
    const FIELD_RELEVANT_HISTORY = 'relevantHistory';
    const FIELD_SIGNER = 'signer';
    const FIELD_FRIENDLY = 'friendly';
    const FIELD_LEGAL = 'legal';
    const FIELD_RULE = 'rule';
    const FIELD_LEGALLY_BINDING_ATTACHMENT = 'legallyBindingAttachment';
    const FIELD_LEGALLY_BINDING_REFERENCE = 'legallyBindingReference';

    /** @var string */
    private $_xmlns = '';

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for this Contract or a derivative that references a Source
     * Contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    protected $identifier = [];

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Canonical identifier for this contract, represented as a URI (globally unique).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    protected $url = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An edition identifier used for business purposes to label business significant
     * variants.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $version = null;

    /**
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRContractResourceStatusCodes
     */
    protected $status = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legal states of the formation of a legal instrument, which is a formally
     * executed written document that can be formally attributed to its author, records
     * and formally expresses a legally enforceable act, process, or contractual duty,
     * obligation, or right, and therefore evidences that act, process, or agreement.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $legalState = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined Contract Definition that is adhered to in
     * whole or part by this Contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $instantiatesCanonical = null;

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an externally maintained definition that is adhered to in
     * whole or in part by this Contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    protected $instantiatesUri = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimal content derived from the basal information source at a specific
     * stage in its lifecycle.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $contentDerivative = null;

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When this Contract was issued.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    protected $issued = null;

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Relevant time or time-period when this Contract is applicable.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    protected $applies = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Event resulting in discontinuation or termination of this Contract instance by
     * one or more parties to the contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $expirationType = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The target entity impacted by or of interest to parties to the agreement.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    protected $subject = [];

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A formally or informally recognized grouping of people, principals,
     * organizations, or jurisdictions formed for the purpose of achieving some form of
     * collective action such as the promulgation, administration and enforcement of
     * contracts and policies.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    protected $authority = [];

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Recognized governance framework or system operating with a circumscribed scope
     * in accordance with specified principles, policies, processes or procedures for
     * managing rights, actions, or behaviors of parties or principals relative to
     * resources.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    protected $domain = [];

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sites in which the contract is complied with, exercised, or in force.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    protected $site = [];

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A natural language name identifying this Contract definition, derivative, or
     * instance in any legal state. Provides additional information about its content.
     * This name should be usable as an identifier for the module by machine processing
     * applications such as code generation.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $name = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short, descriptive, user-friendly title for this Contract definition,
     * derivative, or instance in any legal state.t giving additional information about
     * its content.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $title = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An explanatory or alternate user-friendly title for this Contract definition,
     * derivative, or instance in any legal state.t giving additional information about
     * its content.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $subtitle = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Alternative representation of the title for this Contract definition,
     * derivative, or instance in any legal state., e.g., a domain specific contract
     * number related to legislation.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    protected $alias = [];

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization that authored the Contract definition,
     * derivative, or instance in any legal state.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $author = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A selector of legal concerns for this Contract definition, derivative, or
     * instance in any legal state.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $scope = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $topicCodeableConcept = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $topicReference = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A high-level category for the legal instrument, whether constructed as a
     * Contract definition, derivative, or instance in any legal state. Provides
     * additional information about its content within the context of the Contract's
     * scope to distinguish the kinds of systems that would be interested in the
     * contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $type = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sub-category for the Contract that distinguishes the kinds of systems that would
     * be interested in the Contract within the context of the Contract's scope.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    protected $subType = [];

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Precusory content developed with a focus and intent of supporting the formation
     * a Contract instance, which may be associated with and transformable into a
     * Contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition
     */
    protected $contentDefinition = null;

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * One or more Contract Provisions, which may be related and conveyed as a group,
     * and may contain nested groups.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm[]
     */
    protected $term = [];

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information that may be needed by/relevant to the performer in their execution
     * of this term action.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    protected $supportingInfo = [];

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Links to Provenance records for past versions of this Contract definition,
     * derivative, or instance, which identify key state transitions or updates that
     * are likely to be relevant to a user looking at the current version of the
     * Contract. The Provence.entity indicates the target that was changed in the
     * update. http://build.fhir.org/provenance-definitions.html#Provenance.entity.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    protected $relevantHistory = [];

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Parties with legal standing in the Contract, including the principal parties,
     * the grantor(s) and grantee(s), which are any person or organization bound by the
     * contract, and any ancillary parties, which facilitate the execution of the
     * contract such as a notary or witness.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner[]
     */
    protected $signer = [];

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * The "patient friendly language" versionof the Contract in whole or in parts.
     * "Patient friendly language" means the representation of the Contract and
     * Contract Provisions in a manner that is readily accessible and understandable by
     * a layperson in accordance with best practices for communication styles that
     * ensure that those agreeing to or signing the Contract understand the roles,
     * actions, obligations, responsibilities, and implication of the agreement.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly[]
     */
    protected $friendly = [];

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Legal expressions or representations of this Contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal[]
     */
    protected $legal = [];

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Computable Policy Rule Language Representations of this Contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule[]
     */
    protected $rule = [];

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    protected $legallyBindingAttachment = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $legallyBindingReference = null;

    /**
     * Validation map for fields in type Contract
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRContract Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRContract::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_IDENTIFIER])) {
            if (is_array($data[self::FIELD_IDENTIFIER])) {
                foreach ($data[self::FIELD_IDENTIFIER] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRIdentifier) {
                        $this->addIdentifier($v);
                    } else {
                        $this->addIdentifier(new FHIRIdentifier($v));
                    }
                }
            } elseif ($data[self::FIELD_IDENTIFIER] instanceof FHIRIdentifier) {
                $this->addIdentifier($data[self::FIELD_IDENTIFIER]);
            } else {
                $this->addIdentifier(new FHIRIdentifier($data[self::FIELD_IDENTIFIER]));
            }
        }
        if (isset($data[self::FIELD_URL]) || isset($data[self::FIELD_URL_EXT])) {
            $value = isset($data[self::FIELD_URL]) ? $data[self::FIELD_URL] : null;
            $ext = (isset($data[self::FIELD_URL_EXT]) && is_array($data[self::FIELD_URL_EXT])) ? $ext = $data[self::FIELD_URL_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRUri) {
                    $this->setUrl($value);
                } else if (is_array($value)) {
                    $this->setUrl(new FHIRUri(array_merge($ext, $value)));
                } else {
                    $this->setUrl(new FHIRUri([FHIRUri::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setUrl(new FHIRUri($ext));
            }
        }
        if (isset($data[self::FIELD_VERSION]) || isset($data[self::FIELD_VERSION_EXT])) {
            $value = isset($data[self::FIELD_VERSION]) ? $data[self::FIELD_VERSION] : null;
            $ext = (isset($data[self::FIELD_VERSION_EXT]) && is_array($data[self::FIELD_VERSION_EXT])) ? $ext = $data[self::FIELD_VERSION_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setVersion($value);
                } else if (is_array($value)) {
                    $this->setVersion(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setVersion(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setVersion(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_STATUS]) || isset($data[self::FIELD_STATUS_EXT])) {
            $value = isset($data[self::FIELD_STATUS]) ? $data[self::FIELD_STATUS] : null;
            $ext = (isset($data[self::FIELD_STATUS_EXT]) && is_array($data[self::FIELD_STATUS_EXT])) ? $ext = $data[self::FIELD_STATUS_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRContractResourceStatusCodes) {
                    $this->setStatus($value);
                } else if (is_array($value)) {
                    $this->setStatus(new FHIRContractResourceStatusCodes(array_merge($ext, $value)));
                } else {
                    $this->setStatus(new FHIRContractResourceStatusCodes([FHIRContractResourceStatusCodes::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setStatus(new FHIRContractResourceStatusCodes($ext));
            }
        }
        if (isset($data[self::FIELD_LEGAL_STATE])) {
            if ($data[self::FIELD_LEGAL_STATE] instanceof FHIRCodeableConcept) {
                $this->setLegalState($data[self::FIELD_LEGAL_STATE]);
            } else {
                $this->setLegalState(new FHIRCodeableConcept($data[self::FIELD_LEGAL_STATE]));
            }
        }
        if (isset($data[self::FIELD_INSTANTIATES_CANONICAL])) {
            if ($data[self::FIELD_INSTANTIATES_CANONICAL] instanceof FHIRReference) {
                $this->setInstantiatesCanonical($data[self::FIELD_INSTANTIATES_CANONICAL]);
            } else {
                $this->setInstantiatesCanonical(new FHIRReference($data[self::FIELD_INSTANTIATES_CANONICAL]));
            }
        }
        if (isset($data[self::FIELD_INSTANTIATES_URI]) || isset($data[self::FIELD_INSTANTIATES_URI_EXT])) {
            $value = isset($data[self::FIELD_INSTANTIATES_URI]) ? $data[self::FIELD_INSTANTIATES_URI] : null;
            $ext = (isset($data[self::FIELD_INSTANTIATES_URI_EXT]) && is_array($data[self::FIELD_INSTANTIATES_URI_EXT])) ? $ext = $data[self::FIELD_INSTANTIATES_URI_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRUri) {
                    $this->setInstantiatesUri($value);
                } else if (is_array($value)) {
                    $this->setInstantiatesUri(new FHIRUri(array_merge($ext, $value)));
                } else {
                    $this->setInstantiatesUri(new FHIRUri([FHIRUri::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setInstantiatesUri(new FHIRUri($ext));
            }
        }
        if (isset($data[self::FIELD_CONTENT_DERIVATIVE])) {
            if ($data[self::FIELD_CONTENT_DERIVATIVE] instanceof FHIRCodeableConcept) {
                $this->setContentDerivative($data[self::FIELD_CONTENT_DERIVATIVE]);
            } else {
                $this->setContentDerivative(new FHIRCodeableConcept($data[self::FIELD_CONTENT_DERIVATIVE]));
            }
        }
        if (isset($data[self::FIELD_ISSUED]) || isset($data[self::FIELD_ISSUED_EXT])) {
            $value = isset($data[self::FIELD_ISSUED]) ? $data[self::FIELD_ISSUED] : null;
            $ext = (isset($data[self::FIELD_ISSUED_EXT]) && is_array($data[self::FIELD_ISSUED_EXT])) ? $ext = $data[self::FIELD_ISSUED_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDateTime) {
                    $this->setIssued($value);
                } else if (is_array($value)) {
                    $this->setIssued(new FHIRDateTime(array_merge($ext, $value)));
                } else {
                    $this->setIssued(new FHIRDateTime([FHIRDateTime::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setIssued(new FHIRDateTime($ext));
            }
        }
        if (isset($data[self::FIELD_APPLIES])) {
            if ($data[self::FIELD_APPLIES] instanceof FHIRPeriod) {
                $this->setApplies($data[self::FIELD_APPLIES]);
            } else {
                $this->setApplies(new FHIRPeriod($data[self::FIELD_APPLIES]));
            }
        }
        if (isset($data[self::FIELD_EXPIRATION_TYPE])) {
            if ($data[self::FIELD_EXPIRATION_TYPE] instanceof FHIRCodeableConcept) {
                $this->setExpirationType($data[self::FIELD_EXPIRATION_TYPE]);
            } else {
                $this->setExpirationType(new FHIRCodeableConcept($data[self::FIELD_EXPIRATION_TYPE]));
            }
        }
        if (isset($data[self::FIELD_SUBJECT])) {
            if (is_array($data[self::FIELD_SUBJECT])) {
                foreach ($data[self::FIELD_SUBJECT] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRReference) {
                        $this->addSubject($v);
                    } else {
                        $this->addSubject(new FHIRReference($v));
                    }
                }
            } elseif ($data[self::FIELD_SUBJECT] instanceof FHIRReference) {
                $this->addSubject($data[self::FIELD_SUBJECT]);
            } else {
                $this->addSubject(new FHIRReference($data[self::FIELD_SUBJECT]));
            }
        }
        if (isset($data[self::FIELD_AUTHORITY])) {
            if (is_array($data[self::FIELD_AUTHORITY])) {
                foreach ($data[self::FIELD_AUTHORITY] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRReference) {
                        $this->addAuthority($v);
                    } else {
                        $this->addAuthority(new FHIRReference($v));
                    }
                }
            } elseif ($data[self::FIELD_AUTHORITY] instanceof FHIRReference) {
                $this->addAuthority($data[self::FIELD_AUTHORITY]);
            } else {
                $this->addAuthority(new FHIRReference($data[self::FIELD_AUTHORITY]));
            }
        }
        if (isset($data[self::FIELD_DOMAIN])) {
            if (is_array($data[self::FIELD_DOMAIN])) {
                foreach ($data[self::FIELD_DOMAIN] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRReference) {
                        $this->addDomain($v);
                    } else {
                        $this->addDomain(new FHIRReference($v));
                    }
                }
            } elseif ($data[self::FIELD_DOMAIN] instanceof FHIRReference) {
                $this->addDomain($data[self::FIELD_DOMAIN]);
            } else {
                $this->addDomain(new FHIRReference($data[self::FIELD_DOMAIN]));
            }
        }
        if (isset($data[self::FIELD_SITE])) {
            if (is_array($data[self::FIELD_SITE])) {
                foreach ($data[self::FIELD_SITE] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRReference) {
                        $this->addSite($v);
                    } else {
                        $this->addSite(new FHIRReference($v));
                    }
                }
            } elseif ($data[self::FIELD_SITE] instanceof FHIRReference) {
                $this->addSite($data[self::FIELD_SITE]);
            } else {
                $this->addSite(new FHIRReference($data[self::FIELD_SITE]));
            }
        }
        if (isset($data[self::FIELD_NAME]) || isset($data[self::FIELD_NAME_EXT])) {
            $value = isset($data[self::FIELD_NAME]) ? $data[self::FIELD_NAME] : null;
            $ext = (isset($data[self::FIELD_NAME_EXT]) && is_array($data[self::FIELD_NAME_EXT])) ? $ext = $data[self::FIELD_NAME_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setName($value);
                } else if (is_array($value)) {
                    $this->setName(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setName(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setName(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_TITLE]) || isset($data[self::FIELD_TITLE_EXT])) {
            $value = isset($data[self::FIELD_TITLE]) ? $data[self::FIELD_TITLE] : null;
            $ext = (isset($data[self::FIELD_TITLE_EXT]) && is_array($data[self::FIELD_TITLE_EXT])) ? $ext = $data[self::FIELD_TITLE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setTitle($value);
                } else if (is_array($value)) {
                    $this->setTitle(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setTitle(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setTitle(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_SUBTITLE]) || isset($data[self::FIELD_SUBTITLE_EXT])) {
            $value = isset($data[self::FIELD_SUBTITLE]) ? $data[self::FIELD_SUBTITLE] : null;
            $ext = (isset($data[self::FIELD_SUBTITLE_EXT]) && is_array($data[self::FIELD_SUBTITLE_EXT])) ? $ext = $data[self::FIELD_SUBTITLE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setSubtitle($value);
                } else if (is_array($value)) {
                    $this->setSubtitle(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setSubtitle(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setSubtitle(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_ALIAS]) || isset($data[self::FIELD_ALIAS_EXT])) {
            $value = isset($data[self::FIELD_ALIAS]) ? $data[self::FIELD_ALIAS] : null;
            $ext = (isset($data[self::FIELD_ALIAS_EXT]) && is_array($data[self::FIELD_ALIAS_EXT])) ? $ext = $data[self::FIELD_ALIAS_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->addAlias($value);
                } else if (is_array($value)) {
                    foreach ($value as $i => $v) {
                        if ($v instanceof FHIRString) {
                            $this->addAlias($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addAlias(new FHIRString(array_merge($v, $iext)));
                            } else {
                                $this->addAlias(new FHIRString([FHIRString::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addAlias(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->addAlias(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach ($ext as $iext) {
                    $this->addAlias(new FHIRString($iext));
                }
            }
        }
        if (isset($data[self::FIELD_AUTHOR])) {
            if ($data[self::FIELD_AUTHOR] instanceof FHIRReference) {
                $this->setAuthor($data[self::FIELD_AUTHOR]);
            } else {
                $this->setAuthor(new FHIRReference($data[self::FIELD_AUTHOR]));
            }
        }
        if (isset($data[self::FIELD_SCOPE])) {
            if ($data[self::FIELD_SCOPE] instanceof FHIRCodeableConcept) {
                $this->setScope($data[self::FIELD_SCOPE]);
            } else {
                $this->setScope(new FHIRCodeableConcept($data[self::FIELD_SCOPE]));
            }
        }
        if (isset($data[self::FIELD_TOPIC_CODEABLE_CONCEPT])) {
            if ($data[self::FIELD_TOPIC_CODEABLE_CONCEPT] instanceof FHIRCodeableConcept) {
                $this->setTopicCodeableConcept($data[self::FIELD_TOPIC_CODEABLE_CONCEPT]);
            } else {
                $this->setTopicCodeableConcept(new FHIRCodeableConcept($data[self::FIELD_TOPIC_CODEABLE_CONCEPT]));
            }
        }
        if (isset($data[self::FIELD_TOPIC_REFERENCE])) {
            if ($data[self::FIELD_TOPIC_REFERENCE] instanceof FHIRReference) {
                $this->setTopicReference($data[self::FIELD_TOPIC_REFERENCE]);
            } else {
                $this->setTopicReference(new FHIRReference($data[self::FIELD_TOPIC_REFERENCE]));
            }
        }
        if (isset($data[self::FIELD_TYPE])) {
            if ($data[self::FIELD_TYPE] instanceof FHIRCodeableConcept) {
                $this->setType($data[self::FIELD_TYPE]);
            } else {
                $this->setType(new FHIRCodeableConcept($data[self::FIELD_TYPE]));
            }
        }
        if (isset($data[self::FIELD_SUB_TYPE])) {
            if (is_array($data[self::FIELD_SUB_TYPE])) {
                foreach ($data[self::FIELD_SUB_TYPE] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRCodeableConcept) {
                        $this->addSubType($v);
                    } else {
                        $this->addSubType(new FHIRCodeableConcept($v));
                    }
                }
            } elseif ($data[self::FIELD_SUB_TYPE] instanceof FHIRCodeableConcept) {
                $this->addSubType($data[self::FIELD_SUB_TYPE]);
            } else {
                $this->addSubType(new FHIRCodeableConcept($data[self::FIELD_SUB_TYPE]));
            }
        }
        if (isset($data[self::FIELD_CONTENT_DEFINITION])) {
            if ($data[self::FIELD_CONTENT_DEFINITION] instanceof FHIRContractContentDefinition) {
                $this->setContentDefinition($data[self::FIELD_CONTENT_DEFINITION]);
            } else {
                $this->setContentDefinition(new FHIRContractContentDefinition($data[self::FIELD_CONTENT_DEFINITION]));
            }
        }
        if (isset($data[self::FIELD_TERM])) {
            if (is_array($data[self::FIELD_TERM])) {
                foreach ($data[self::FIELD_TERM] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRContractTerm) {
                        $this->addTerm($v);
                    } else {
                        $this->addTerm(new FHIRContractTerm($v));
                    }
                }
            } elseif ($data[self::FIELD_TERM] instanceof FHIRContractTerm) {
                $this->addTerm($data[self::FIELD_TERM]);
            } else {
                $this->addTerm(new FHIRContractTerm($data[self::FIELD_TERM]));
            }
        }
        if (isset($data[self::FIELD_SUPPORTING_INFO])) {
            if (is_array($data[self::FIELD_SUPPORTING_INFO])) {
                foreach ($data[self::FIELD_SUPPORTING_INFO] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRReference) {
                        $this->addSupportingInfo($v);
                    } else {
                        $this->addSupportingInfo(new FHIRReference($v));
                    }
                }
            } elseif ($data[self::FIELD_SUPPORTING_INFO] instanceof FHIRReference) {
                $this->addSupportingInfo($data[self::FIELD_SUPPORTING_INFO]);
            } else {
                $this->addSupportingInfo(new FHIRReference($data[self::FIELD_SUPPORTING_INFO]));
            }
        }
        if (isset($data[self::FIELD_RELEVANT_HISTORY])) {
            if (is_array($data[self::FIELD_RELEVANT_HISTORY])) {
                foreach ($data[self::FIELD_RELEVANT_HISTORY] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRReference) {
                        $this->addRelevantHistory($v);
                    } else {
                        $this->addRelevantHistory(new FHIRReference($v));
                    }
                }
            } elseif ($data[self::FIELD_RELEVANT_HISTORY] instanceof FHIRReference) {
                $this->addRelevantHistory($data[self::FIELD_RELEVANT_HISTORY]);
            } else {
                $this->addRelevantHistory(new FHIRReference($data[self::FIELD_RELEVANT_HISTORY]));
            }
        }
        if (isset($data[self::FIELD_SIGNER])) {
            if (is_array($data[self::FIELD_SIGNER])) {
                foreach ($data[self::FIELD_SIGNER] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRContractSigner) {
                        $this->addSigner($v);
                    } else {
                        $this->addSigner(new FHIRContractSigner($v));
                    }
                }
            } elseif ($data[self::FIELD_SIGNER] instanceof FHIRContractSigner) {
                $this->addSigner($data[self::FIELD_SIGNER]);
            } else {
                $this->addSigner(new FHIRContractSigner($data[self::FIELD_SIGNER]));
            }
        }
        if (isset($data[self::FIELD_FRIENDLY])) {
            if (is_array($data[self::FIELD_FRIENDLY])) {
                foreach ($data[self::FIELD_FRIENDLY] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRContractFriendly) {
                        $this->addFriendly($v);
                    } else {
                        $this->addFriendly(new FHIRContractFriendly($v));
                    }
                }
            } elseif ($data[self::FIELD_FRIENDLY] instanceof FHIRContractFriendly) {
                $this->addFriendly($data[self::FIELD_FRIENDLY]);
            } else {
                $this->addFriendly(new FHIRContractFriendly($data[self::FIELD_FRIENDLY]));
            }
        }
        if (isset($data[self::FIELD_LEGAL])) {
            if (is_array($data[self::FIELD_LEGAL])) {
                foreach ($data[self::FIELD_LEGAL] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRContractLegal) {
                        $this->addLegal($v);
                    } else {
                        $this->addLegal(new FHIRContractLegal($v));
                    }
                }
            } elseif ($data[self::FIELD_LEGAL] instanceof FHIRContractLegal) {
                $this->addLegal($data[self::FIELD_LEGAL]);
            } else {
                $this->addLegal(new FHIRContractLegal($data[self::FIELD_LEGAL]));
            }
        }
        if (isset($data[self::FIELD_RULE])) {
            if (is_array($data[self::FIELD_RULE])) {
                foreach ($data[self::FIELD_RULE] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRContractRule) {
                        $this->addRule($v);
                    } else {
                        $this->addRule(new FHIRContractRule($v));
                    }
                }
            } elseif ($data[self::FIELD_RULE] instanceof FHIRContractRule) {
                $this->addRule($data[self::FIELD_RULE]);
            } else {
                $this->addRule(new FHIRContractRule($data[self::FIELD_RULE]));
            }
        }
        if (isset($data[self::FIELD_LEGALLY_BINDING_ATTACHMENT])) {
            if ($data[self::FIELD_LEGALLY_BINDING_ATTACHMENT] instanceof FHIRAttachment) {
                $this->setLegallyBindingAttachment($data[self::FIELD_LEGALLY_BINDING_ATTACHMENT]);
            } else {
                $this->setLegallyBindingAttachment(new FHIRAttachment($data[self::FIELD_LEGALLY_BINDING_ATTACHMENT]));
            }
        }
        if (isset($data[self::FIELD_LEGALLY_BINDING_REFERENCE])) {
            if ($data[self::FIELD_LEGALLY_BINDING_REFERENCE] instanceof FHIRReference) {
                $this->setLegallyBindingReference($data[self::FIELD_LEGALLY_BINDING_REFERENCE]);
            } else {
                $this->setLegallyBindingReference(new FHIRReference($data[self::FIELD_LEGALLY_BINDING_REFERENCE]));
            }
        }
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if ('' !==  $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<Contract{$xmlns}></Contract>";
    }
    /**
     * @return string
     */
    public function _getResourceType()
    {
        return static::FHIR_TYPE_NAME;
    }


    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for this Contract or a derivative that references a Source
     * Contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for this Contract or a derivative that references a Source
     * Contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function addIdentifier(FHIRIdentifier $identifier = null)
    {
        $this->_trackValueAdded();
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for this Contract or a derivative that references a Source
     * Contract.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[] $identifier
     * @return static
     */
    public function setIdentifier(array $identifier = [])
    {
        if ([] !== $this->identifier) {
            $this->_trackValuesRemoved(count($this->identifier));
            $this->identifier = [];
        }
        if ([] === $identifier) {
            return $this;
        }
        foreach ($identifier as $v) {
            if ($v instanceof FHIRIdentifier) {
                $this->addIdentifier($v);
            } else {
                $this->addIdentifier(new FHIRIdentifier($v));
            }
        }
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Canonical identifier for this contract, represented as a URI (globally unique).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Canonical identifier for this contract, represented as a URI (globally unique).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri $url
     * @return static
     */
    public function setUrl($url = null)
    {
        if (null !== $url && !($url instanceof FHIRUri)) {
            $url = new FHIRUri($url);
        }
        $this->_trackValueSet($this->url, $url);
        $this->url = $url;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An edition identifier used for business purposes to label business significant
     * variants.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An edition identifier used for business purposes to label business significant
     * variants.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $version
     * @return static
     */
    public function setVersion($version = null)
    {
        if (null !== $version && !($version instanceof FHIRString)) {
            $version = new FHIRString($version);
        }
        $this->_trackValueSet($this->version, $version);
        $this->version = $version;
        return $this;
    }

    /**
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRContractResourceStatusCodes
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRContractResourceStatusCodes $status
     * @return static
     */
    public function setStatus(FHIRContractResourceStatusCodes $status = null)
    {
        $this->_trackValueSet($this->status, $status);
        $this->status = $status;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legal states of the formation of a legal instrument, which is a formally
     * executed written document that can be formally attributed to its author, records
     * and formally expresses a legally enforceable act, process, or contractual duty,
     * obligation, or right, and therefore evidences that act, process, or agreement.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getLegalState()
    {
        return $this->legalState;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legal states of the formation of a legal instrument, which is a formally
     * executed written document that can be formally attributed to its author, records
     * and formally expresses a legally enforceable act, process, or contractual duty,
     * obligation, or right, and therefore evidences that act, process, or agreement.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $legalState
     * @return static
     */
    public function setLegalState(FHIRCodeableConcept $legalState = null)
    {
        $this->_trackValueSet($this->legalState, $legalState);
        $this->legalState = $legalState;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined Contract Definition that is adhered to in
     * whole or part by this Contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getInstantiatesCanonical()
    {
        return $this->instantiatesCanonical;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined Contract Definition that is adhered to in
     * whole or part by this Contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $instantiatesCanonical
     * @return static
     */
    public function setInstantiatesCanonical(FHIRReference $instantiatesCanonical = null)
    {
        $this->_trackValueSet($this->instantiatesCanonical, $instantiatesCanonical);
        $this->instantiatesCanonical = $instantiatesCanonical;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an externally maintained definition that is adhered to in
     * whole or in part by this Contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getInstantiatesUri()
    {
        return $this->instantiatesUri;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an externally maintained definition that is adhered to in
     * whole or in part by this Contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri $instantiatesUri
     * @return static
     */
    public function setInstantiatesUri($instantiatesUri = null)
    {
        if (null !== $instantiatesUri && !($instantiatesUri instanceof FHIRUri)) {
            $instantiatesUri = new FHIRUri($instantiatesUri);
        }
        $this->_trackValueSet($this->instantiatesUri, $instantiatesUri);
        $this->instantiatesUri = $instantiatesUri;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimal content derived from the basal information source at a specific
     * stage in its lifecycle.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getContentDerivative()
    {
        return $this->contentDerivative;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimal content derived from the basal information source at a specific
     * stage in its lifecycle.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $contentDerivative
     * @return static
     */
    public function setContentDerivative(FHIRCodeableConcept $contentDerivative = null)
    {
        $this->_trackValueSet($this->contentDerivative, $contentDerivative);
        $this->contentDerivative = $contentDerivative;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When this Contract was issued.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When this Contract was issued.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $issued
     * @return static
     */
    public function setIssued($issued = null)
    {
        if (null !== $issued && !($issued instanceof FHIRDateTime)) {
            $issued = new FHIRDateTime($issued);
        }
        $this->_trackValueSet($this->issued, $issued);
        $this->issued = $issued;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Relevant time or time-period when this Contract is applicable.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getApplies()
    {
        return $this->applies;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Relevant time or time-period when this Contract is applicable.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $applies
     * @return static
     */
    public function setApplies(FHIRPeriod $applies = null)
    {
        $this->_trackValueSet($this->applies, $applies);
        $this->applies = $applies;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Event resulting in discontinuation or termination of this Contract instance by
     * one or more parties to the contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getExpirationType()
    {
        return $this->expirationType;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Event resulting in discontinuation or termination of this Contract instance by
     * one or more parties to the contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $expirationType
     * @return static
     */
    public function setExpirationType(FHIRCodeableConcept $expirationType = null)
    {
        $this->_trackValueSet($this->expirationType, $expirationType);
        $this->expirationType = $expirationType;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The target entity impacted by or of interest to parties to the agreement.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The target entity impacted by or of interest to parties to the agreement.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return static
     */
    public function addSubject(FHIRReference $subject = null)
    {
        $this->_trackValueAdded();
        $this->subject[] = $subject;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The target entity impacted by or of interest to parties to the agreement.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[] $subject
     * @return static
     */
    public function setSubject(array $subject = [])
    {
        if ([] !== $this->subject) {
            $this->_trackValuesRemoved(count($this->subject));
            $this->subject = [];
        }
        if ([] === $subject) {
            return $this;
        }
        foreach ($subject as $v) {
            if ($v instanceof FHIRReference) {
                $this->addSubject($v);
            } else {
                $this->addSubject(new FHIRReference($v));
            }
        }
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A formally or informally recognized grouping of people, principals,
     * organizations, or jurisdictions formed for the purpose of achieving some form of
     * collective action such as the promulgation, administration and enforcement of
     * contracts and policies.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A formally or informally recognized grouping of people, principals,
     * organizations, or jurisdictions formed for the purpose of achieving some form of
     * collective action such as the promulgation, administration and enforcement of
     * contracts and policies.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $authority
     * @return static
     */
    public function addAuthority(FHIRReference $authority = null)
    {
        $this->_trackValueAdded();
        $this->authority[] = $authority;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A formally or informally recognized grouping of people, principals,
     * organizations, or jurisdictions formed for the purpose of achieving some form of
     * collective action such as the promulgation, administration and enforcement of
     * contracts and policies.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[] $authority
     * @return static
     */
    public function setAuthority(array $authority = [])
    {
        if ([] !== $this->authority) {
            $this->_trackValuesRemoved(count($this->authority));
            $this->authority = [];
        }
        if ([] === $authority) {
            return $this;
        }
        foreach ($authority as $v) {
            if ($v instanceof FHIRReference) {
                $this->addAuthority($v);
            } else {
                $this->addAuthority(new FHIRReference($v));
            }
        }
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Recognized governance framework or system operating with a circumscribed scope
     * in accordance with specified principles, policies, processes or procedures for
     * managing rights, actions, or behaviors of parties or principals relative to
     * resources.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Recognized governance framework or system operating with a circumscribed scope
     * in accordance with specified principles, policies, processes or procedures for
     * managing rights, actions, or behaviors of parties or principals relative to
     * resources.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $domain
     * @return static
     */
    public function addDomain(FHIRReference $domain = null)
    {
        $this->_trackValueAdded();
        $this->domain[] = $domain;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Recognized governance framework or system operating with a circumscribed scope
     * in accordance with specified principles, policies, processes or procedures for
     * managing rights, actions, or behaviors of parties or principals relative to
     * resources.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[] $domain
     * @return static
     */
    public function setDomain(array $domain = [])
    {
        if ([] !== $this->domain) {
            $this->_trackValuesRemoved(count($this->domain));
            $this->domain = [];
        }
        if ([] === $domain) {
            return $this;
        }
        foreach ($domain as $v) {
            if ($v instanceof FHIRReference) {
                $this->addDomain($v);
            } else {
                $this->addDomain(new FHIRReference($v));
            }
        }
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sites in which the contract is complied with, exercised, or in force.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sites in which the contract is complied with, exercised, or in force.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $site
     * @return static
     */
    public function addSite(FHIRReference $site = null)
    {
        $this->_trackValueAdded();
        $this->site[] = $site;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sites in which the contract is complied with, exercised, or in force.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[] $site
     * @return static
     */
    public function setSite(array $site = [])
    {
        if ([] !== $this->site) {
            $this->_trackValuesRemoved(count($this->site));
            $this->site = [];
        }
        if ([] === $site) {
            return $this;
        }
        foreach ($site as $v) {
            if ($v instanceof FHIRReference) {
                $this->addSite($v);
            } else {
                $this->addSite(new FHIRReference($v));
            }
        }
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A natural language name identifying this Contract definition, derivative, or
     * instance in any legal state. Provides additional information about its content.
     * This name should be usable as an identifier for the module by machine processing
     * applications such as code generation.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A natural language name identifying this Contract definition, derivative, or
     * instance in any legal state. Provides additional information about its content.
     * This name should be usable as an identifier for the module by machine processing
     * applications such as code generation.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return static
     */
    public function setName($name = null)
    {
        if (null !== $name && !($name instanceof FHIRString)) {
            $name = new FHIRString($name);
        }
        $this->_trackValueSet($this->name, $name);
        $this->name = $name;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short, descriptive, user-friendly title for this Contract definition,
     * derivative, or instance in any legal state.t giving additional information about
     * its content.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short, descriptive, user-friendly title for this Contract definition,
     * derivative, or instance in any legal state.t giving additional information about
     * its content.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return static
     */
    public function setTitle($title = null)
    {
        if (null !== $title && !($title instanceof FHIRString)) {
            $title = new FHIRString($title);
        }
        $this->_trackValueSet($this->title, $title);
        $this->title = $title;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An explanatory or alternate user-friendly title for this Contract definition,
     * derivative, or instance in any legal state.t giving additional information about
     * its content.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An explanatory or alternate user-friendly title for this Contract definition,
     * derivative, or instance in any legal state.t giving additional information about
     * its content.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $subtitle
     * @return static
     */
    public function setSubtitle($subtitle = null)
    {
        if (null !== $subtitle && !($subtitle instanceof FHIRString)) {
            $subtitle = new FHIRString($subtitle);
        }
        $this->_trackValueSet($this->subtitle, $subtitle);
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Alternative representation of the title for this Contract definition,
     * derivative, or instance in any legal state., e.g., a domain specific contract
     * number related to legislation.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Alternative representation of the title for this Contract definition,
     * derivative, or instance in any legal state., e.g., a domain specific contract
     * number related to legislation.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $alias
     * @return static
     */
    public function addAlias($alias = null)
    {
        if (null !== $alias && !($alias instanceof FHIRString)) {
            $alias = new FHIRString($alias);
        }
        $this->_trackValueAdded();
        $this->alias[] = $alias;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Alternative representation of the title for this Contract definition,
     * derivative, or instance in any legal state., e.g., a domain specific contract
     * number related to legislation.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString[] $alias
     * @return static
     */
    public function setAlias(array $alias = [])
    {
        if ([] !== $this->alias) {
            $this->_trackValuesRemoved(count($this->alias));
            $this->alias = [];
        }
        if ([] === $alias) {
            return $this;
        }
        foreach ($alias as $v) {
            if ($v instanceof FHIRString) {
                $this->addAlias($v);
            } else {
                $this->addAlias(new FHIRString($v));
            }
        }
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization that authored the Contract definition,
     * derivative, or instance in any legal state.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization that authored the Contract definition,
     * derivative, or instance in any legal state.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $author
     * @return static
     */
    public function setAuthor(FHIRReference $author = null)
    {
        $this->_trackValueSet($this->author, $author);
        $this->author = $author;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A selector of legal concerns for this Contract definition, derivative, or
     * instance in any legal state.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A selector of legal concerns for this Contract definition, derivative, or
     * instance in any legal state.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $scope
     * @return static
     */
    public function setScope(FHIRCodeableConcept $scope = null)
    {
        $this->_trackValueSet($this->scope, $scope);
        $this->scope = $scope;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getTopicCodeableConcept()
    {
        return $this->topicCodeableConcept;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $topicCodeableConcept
     * @return static
     */
    public function setTopicCodeableConcept(FHIRCodeableConcept $topicCodeableConcept = null)
    {
        $this->_trackValueSet($this->topicCodeableConcept, $topicCodeableConcept);
        $this->topicCodeableConcept = $topicCodeableConcept;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getTopicReference()
    {
        return $this->topicReference;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $topicReference
     * @return static
     */
    public function setTopicReference(FHIRReference $topicReference = null)
    {
        $this->_trackValueSet($this->topicReference, $topicReference);
        $this->topicReference = $topicReference;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A high-level category for the legal instrument, whether constructed as a
     * Contract definition, derivative, or instance in any legal state. Provides
     * additional information about its content within the context of the Contract's
     * scope to distinguish the kinds of systems that would be interested in the
     * contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A high-level category for the legal instrument, whether constructed as a
     * Contract definition, derivative, or instance in any legal state. Provides
     * additional information about its content within the context of the Contract's
     * scope to distinguish the kinds of systems that would be interested in the
     * contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function setType(FHIRCodeableConcept $type = null)
    {
        $this->_trackValueSet($this->type, $type);
        $this->type = $type;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sub-category for the Contract that distinguishes the kinds of systems that would
     * be interested in the Contract within the context of the Contract's scope.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sub-category for the Contract that distinguishes the kinds of systems that would
     * be interested in the Contract within the context of the Contract's scope.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $subType
     * @return static
     */
    public function addSubType(FHIRCodeableConcept $subType = null)
    {
        $this->_trackValueAdded();
        $this->subType[] = $subType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sub-category for the Contract that distinguishes the kinds of systems that would
     * be interested in the Contract within the context of the Contract's scope.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[] $subType
     * @return static
     */
    public function setSubType(array $subType = [])
    {
        if ([] !== $this->subType) {
            $this->_trackValuesRemoved(count($this->subType));
            $this->subType = [];
        }
        if ([] === $subType) {
            return $this;
        }
        foreach ($subType as $v) {
            if ($v instanceof FHIRCodeableConcept) {
                $this->addSubType($v);
            } else {
                $this->addSubType(new FHIRCodeableConcept($v));
            }
        }
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Precusory content developed with a focus and intent of supporting the formation
     * a Contract instance, which may be associated with and transformable into a
     * Contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition
     */
    public function getContentDefinition()
    {
        return $this->contentDefinition;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Precusory content developed with a focus and intent of supporting the formation
     * a Contract instance, which may be associated with and transformable into a
     * Contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition $contentDefinition
     * @return static
     */
    public function setContentDefinition(FHIRContractContentDefinition $contentDefinition = null)
    {
        $this->_trackValueSet($this->contentDefinition, $contentDefinition);
        $this->contentDefinition = $contentDefinition;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * One or more Contract Provisions, which may be related and conveyed as a group,
     * and may contain nested groups.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm[]
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * One or more Contract Provisions, which may be related and conveyed as a group,
     * and may contain nested groups.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm $term
     * @return static
     */
    public function addTerm(FHIRContractTerm $term = null)
    {
        $this->_trackValueAdded();
        $this->term[] = $term;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * One or more Contract Provisions, which may be related and conveyed as a group,
     * and may contain nested groups.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm[] $term
     * @return static
     */
    public function setTerm(array $term = [])
    {
        if ([] !== $this->term) {
            $this->_trackValuesRemoved(count($this->term));
            $this->term = [];
        }
        if ([] === $term) {
            return $this;
        }
        foreach ($term as $v) {
            if ($v instanceof FHIRContractTerm) {
                $this->addTerm($v);
            } else {
                $this->addTerm(new FHIRContractTerm($v));
            }
        }
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information that may be needed by/relevant to the performer in their execution
     * of this term action.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSupportingInfo()
    {
        return $this->supportingInfo;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information that may be needed by/relevant to the performer in their execution
     * of this term action.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $supportingInfo
     * @return static
     */
    public function addSupportingInfo(FHIRReference $supportingInfo = null)
    {
        $this->_trackValueAdded();
        $this->supportingInfo[] = $supportingInfo;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information that may be needed by/relevant to the performer in their execution
     * of this term action.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[] $supportingInfo
     * @return static
     */
    public function setSupportingInfo(array $supportingInfo = [])
    {
        if ([] !== $this->supportingInfo) {
            $this->_trackValuesRemoved(count($this->supportingInfo));
            $this->supportingInfo = [];
        }
        if ([] === $supportingInfo) {
            return $this;
        }
        foreach ($supportingInfo as $v) {
            if ($v instanceof FHIRReference) {
                $this->addSupportingInfo($v);
            } else {
                $this->addSupportingInfo(new FHIRReference($v));
            }
        }
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Links to Provenance records for past versions of this Contract definition,
     * derivative, or instance, which identify key state transitions or updates that
     * are likely to be relevant to a user looking at the current version of the
     * Contract. The Provence.entity indicates the target that was changed in the
     * update. http://build.fhir.org/provenance-definitions.html#Provenance.entity.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getRelevantHistory()
    {
        return $this->relevantHistory;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Links to Provenance records for past versions of this Contract definition,
     * derivative, or instance, which identify key state transitions or updates that
     * are likely to be relevant to a user looking at the current version of the
     * Contract. The Provence.entity indicates the target that was changed in the
     * update. http://build.fhir.org/provenance-definitions.html#Provenance.entity.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $relevantHistory
     * @return static
     */
    public function addRelevantHistory(FHIRReference $relevantHistory = null)
    {
        $this->_trackValueAdded();
        $this->relevantHistory[] = $relevantHistory;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Links to Provenance records for past versions of this Contract definition,
     * derivative, or instance, which identify key state transitions or updates that
     * are likely to be relevant to a user looking at the current version of the
     * Contract. The Provence.entity indicates the target that was changed in the
     * update. http://build.fhir.org/provenance-definitions.html#Provenance.entity.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[] $relevantHistory
     * @return static
     */
    public function setRelevantHistory(array $relevantHistory = [])
    {
        if ([] !== $this->relevantHistory) {
            $this->_trackValuesRemoved(count($this->relevantHistory));
            $this->relevantHistory = [];
        }
        if ([] === $relevantHistory) {
            return $this;
        }
        foreach ($relevantHistory as $v) {
            if ($v instanceof FHIRReference) {
                $this->addRelevantHistory($v);
            } else {
                $this->addRelevantHistory(new FHIRReference($v));
            }
        }
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Parties with legal standing in the Contract, including the principal parties,
     * the grantor(s) and grantee(s), which are any person or organization bound by the
     * contract, and any ancillary parties, which facilitate the execution of the
     * contract such as a notary or witness.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner[]
     */
    public function getSigner()
    {
        return $this->signer;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Parties with legal standing in the Contract, including the principal parties,
     * the grantor(s) and grantee(s), which are any person or organization bound by the
     * contract, and any ancillary parties, which facilitate the execution of the
     * contract such as a notary or witness.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner $signer
     * @return static
     */
    public function addSigner(FHIRContractSigner $signer = null)
    {
        $this->_trackValueAdded();
        $this->signer[] = $signer;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Parties with legal standing in the Contract, including the principal parties,
     * the grantor(s) and grantee(s), which are any person or organization bound by the
     * contract, and any ancillary parties, which facilitate the execution of the
     * contract such as a notary or witness.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner[] $signer
     * @return static
     */
    public function setSigner(array $signer = [])
    {
        if ([] !== $this->signer) {
            $this->_trackValuesRemoved(count($this->signer));
            $this->signer = [];
        }
        if ([] === $signer) {
            return $this;
        }
        foreach ($signer as $v) {
            if ($v instanceof FHIRContractSigner) {
                $this->addSigner($v);
            } else {
                $this->addSigner(new FHIRContractSigner($v));
            }
        }
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * The "patient friendly language" versionof the Contract in whole or in parts.
     * "Patient friendly language" means the representation of the Contract and
     * Contract Provisions in a manner that is readily accessible and understandable by
     * a layperson in accordance with best practices for communication styles that
     * ensure that those agreeing to or signing the Contract understand the roles,
     * actions, obligations, responsibilities, and implication of the agreement.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly[]
     */
    public function getFriendly()
    {
        return $this->friendly;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * The "patient friendly language" versionof the Contract in whole or in parts.
     * "Patient friendly language" means the representation of the Contract and
     * Contract Provisions in a manner that is readily accessible and understandable by
     * a layperson in accordance with best practices for communication styles that
     * ensure that those agreeing to or signing the Contract understand the roles,
     * actions, obligations, responsibilities, and implication of the agreement.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly $friendly
     * @return static
     */
    public function addFriendly(FHIRContractFriendly $friendly = null)
    {
        $this->_trackValueAdded();
        $this->friendly[] = $friendly;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * The "patient friendly language" versionof the Contract in whole or in parts.
     * "Patient friendly language" means the representation of the Contract and
     * Contract Provisions in a manner that is readily accessible and understandable by
     * a layperson in accordance with best practices for communication styles that
     * ensure that those agreeing to or signing the Contract understand the roles,
     * actions, obligations, responsibilities, and implication of the agreement.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly[] $friendly
     * @return static
     */
    public function setFriendly(array $friendly = [])
    {
        if ([] !== $this->friendly) {
            $this->_trackValuesRemoved(count($this->friendly));
            $this->friendly = [];
        }
        if ([] === $friendly) {
            return $this;
        }
        foreach ($friendly as $v) {
            if ($v instanceof FHIRContractFriendly) {
                $this->addFriendly($v);
            } else {
                $this->addFriendly(new FHIRContractFriendly($v));
            }
        }
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Legal expressions or representations of this Contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal[]
     */
    public function getLegal()
    {
        return $this->legal;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Legal expressions or representations of this Contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal $legal
     * @return static
     */
    public function addLegal(FHIRContractLegal $legal = null)
    {
        $this->_trackValueAdded();
        $this->legal[] = $legal;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Legal expressions or representations of this Contract.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal[] $legal
     * @return static
     */
    public function setLegal(array $legal = [])
    {
        if ([] !== $this->legal) {
            $this->_trackValuesRemoved(count($this->legal));
            $this->legal = [];
        }
        if ([] === $legal) {
            return $this;
        }
        foreach ($legal as $v) {
            if ($v instanceof FHIRContractLegal) {
                $this->addLegal($v);
            } else {
                $this->addLegal(new FHIRContractLegal($v));
            }
        }
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Computable Policy Rule Language Representations of this Contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule[]
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Computable Policy Rule Language Representations of this Contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule $rule
     * @return static
     */
    public function addRule(FHIRContractRule $rule = null)
    {
        $this->_trackValueAdded();
        $this->rule[] = $rule;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Computable Policy Rule Language Representations of this Contract.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule[] $rule
     * @return static
     */
    public function setRule(array $rule = [])
    {
        if ([] !== $this->rule) {
            $this->_trackValuesRemoved(count($this->rule));
            $this->rule = [];
        }
        if ([] === $rule) {
            return $this;
        }
        foreach ($rule as $v) {
            if ($v instanceof FHIRContractRule) {
                $this->addRule($v);
            } else {
                $this->addRule(new FHIRContractRule($v));
            }
        }
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public function getLegallyBindingAttachment()
    {
        return $this->legallyBindingAttachment;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment $legallyBindingAttachment
     * @return static
     */
    public function setLegallyBindingAttachment(FHIRAttachment $legallyBindingAttachment = null)
    {
        $this->_trackValueSet($this->legallyBindingAttachment, $legallyBindingAttachment);
        $this->legallyBindingAttachment = $legallyBindingAttachment;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getLegallyBindingReference()
    {
        return $this->legallyBindingReference;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $legallyBindingReference
     * @return static
     */
    public function setLegallyBindingReference(FHIRReference $legallyBindingReference = null)
    {
        $this->_trackValueSet($this->legallyBindingReference, $legallyBindingReference);
        $this->legallyBindingReference = $legallyBindingReference;
        return $this;
    }

    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = parent::_getValidationErrors();
        $validationRules = $this->_getValidationRules();
        if ([] !== ($vs = $this->getIdentifier())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_IDENTIFIER, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getUrl())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_URL] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getVersion())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_VERSION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStatus())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STATUS] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getLegalState())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_LEGAL_STATE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getInstantiatesCanonical())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_INSTANTIATES_CANONICAL] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getInstantiatesUri())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_INSTANTIATES_URI] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getContentDerivative())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CONTENT_DERIVATIVE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getIssued())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ISSUED] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getApplies())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_APPLIES] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getExpirationType())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_EXPIRATION_TYPE] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getSubject())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_SUBJECT, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getAuthority())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_AUTHORITY, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getDomain())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_DOMAIN, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getSite())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_SITE, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getName())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_NAME] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getTitle())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TITLE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getSubtitle())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_SUBTITLE] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getAlias())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_ALIAS, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getAuthor())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_AUTHOR] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getScope())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_SCOPE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getTopicCodeableConcept())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TOPIC_CODEABLE_CONCEPT] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getTopicReference())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TOPIC_REFERENCE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getType())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TYPE] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getSubType())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_SUB_TYPE, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getContentDefinition())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CONTENT_DEFINITION] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getTerm())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_TERM, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getSupportingInfo())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_SUPPORTING_INFO, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getRelevantHistory())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_RELEVANT_HISTORY, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getSigner())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_SIGNER, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getFriendly())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_FRIENDLY, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getLegal())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_LEGAL, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getRule())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_RULE, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getLegallyBindingAttachment())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_LEGALLY_BINDING_ATTACHMENT] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getLegallyBindingReference())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_LEGALLY_BINDING_REFERENCE] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_IDENTIFIER])) {
            $v = $this->getIdentifier();
            foreach ($validationRules[self::FIELD_IDENTIFIER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_IDENTIFIER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_IDENTIFIER])) {
                        $errs[self::FIELD_IDENTIFIER] = [];
                    }
                    $errs[self::FIELD_IDENTIFIER][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_URL])) {
            $v = $this->getUrl();
            foreach ($validationRules[self::FIELD_URL] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_URL, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_URL])) {
                        $errs[self::FIELD_URL] = [];
                    }
                    $errs[self::FIELD_URL][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_VERSION])) {
            $v = $this->getVersion();
            foreach ($validationRules[self::FIELD_VERSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_VERSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_VERSION])) {
                        $errs[self::FIELD_VERSION] = [];
                    }
                    $errs[self::FIELD_VERSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STATUS])) {
            $v = $this->getStatus();
            foreach ($validationRules[self::FIELD_STATUS] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_STATUS, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STATUS])) {
                        $errs[self::FIELD_STATUS] = [];
                    }
                    $errs[self::FIELD_STATUS][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LEGAL_STATE])) {
            $v = $this->getLegalState();
            foreach ($validationRules[self::FIELD_LEGAL_STATE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_LEGAL_STATE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LEGAL_STATE])) {
                        $errs[self::FIELD_LEGAL_STATE] = [];
                    }
                    $errs[self::FIELD_LEGAL_STATE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_INSTANTIATES_CANONICAL])) {
            $v = $this->getInstantiatesCanonical();
            foreach ($validationRules[self::FIELD_INSTANTIATES_CANONICAL] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_INSTANTIATES_CANONICAL, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_INSTANTIATES_CANONICAL])) {
                        $errs[self::FIELD_INSTANTIATES_CANONICAL] = [];
                    }
                    $errs[self::FIELD_INSTANTIATES_CANONICAL][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_INSTANTIATES_URI])) {
            $v = $this->getInstantiatesUri();
            foreach ($validationRules[self::FIELD_INSTANTIATES_URI] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_INSTANTIATES_URI, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_INSTANTIATES_URI])) {
                        $errs[self::FIELD_INSTANTIATES_URI] = [];
                    }
                    $errs[self::FIELD_INSTANTIATES_URI][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTENT_DERIVATIVE])) {
            $v = $this->getContentDerivative();
            foreach ($validationRules[self::FIELD_CONTENT_DERIVATIVE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_CONTENT_DERIVATIVE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTENT_DERIVATIVE])) {
                        $errs[self::FIELD_CONTENT_DERIVATIVE] = [];
                    }
                    $errs[self::FIELD_CONTENT_DERIVATIVE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ISSUED])) {
            $v = $this->getIssued();
            foreach ($validationRules[self::FIELD_ISSUED] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_ISSUED, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ISSUED])) {
                        $errs[self::FIELD_ISSUED] = [];
                    }
                    $errs[self::FIELD_ISSUED][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_APPLIES])) {
            $v = $this->getApplies();
            foreach ($validationRules[self::FIELD_APPLIES] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_APPLIES, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_APPLIES])) {
                        $errs[self::FIELD_APPLIES] = [];
                    }
                    $errs[self::FIELD_APPLIES][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXPIRATION_TYPE])) {
            $v = $this->getExpirationType();
            foreach ($validationRules[self::FIELD_EXPIRATION_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_EXPIRATION_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXPIRATION_TYPE])) {
                        $errs[self::FIELD_EXPIRATION_TYPE] = [];
                    }
                    $errs[self::FIELD_EXPIRATION_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SUBJECT])) {
            $v = $this->getSubject();
            foreach ($validationRules[self::FIELD_SUBJECT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_SUBJECT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SUBJECT])) {
                        $errs[self::FIELD_SUBJECT] = [];
                    }
                    $errs[self::FIELD_SUBJECT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_AUTHORITY])) {
            $v = $this->getAuthority();
            foreach ($validationRules[self::FIELD_AUTHORITY] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_AUTHORITY, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_AUTHORITY])) {
                        $errs[self::FIELD_AUTHORITY] = [];
                    }
                    $errs[self::FIELD_AUTHORITY][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DOMAIN])) {
            $v = $this->getDomain();
            foreach ($validationRules[self::FIELD_DOMAIN] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_DOMAIN, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DOMAIN])) {
                        $errs[self::FIELD_DOMAIN] = [];
                    }
                    $errs[self::FIELD_DOMAIN][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SITE])) {
            $v = $this->getSite();
            foreach ($validationRules[self::FIELD_SITE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_SITE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SITE])) {
                        $errs[self::FIELD_SITE] = [];
                    }
                    $errs[self::FIELD_SITE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_NAME])) {
            $v = $this->getName();
            foreach ($validationRules[self::FIELD_NAME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_NAME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_NAME])) {
                        $errs[self::FIELD_NAME] = [];
                    }
                    $errs[self::FIELD_NAME][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TITLE])) {
            $v = $this->getTitle();
            foreach ($validationRules[self::FIELD_TITLE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_TITLE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TITLE])) {
                        $errs[self::FIELD_TITLE] = [];
                    }
                    $errs[self::FIELD_TITLE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SUBTITLE])) {
            $v = $this->getSubtitle();
            foreach ($validationRules[self::FIELD_SUBTITLE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_SUBTITLE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SUBTITLE])) {
                        $errs[self::FIELD_SUBTITLE] = [];
                    }
                    $errs[self::FIELD_SUBTITLE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ALIAS])) {
            $v = $this->getAlias();
            foreach ($validationRules[self::FIELD_ALIAS] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_ALIAS, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ALIAS])) {
                        $errs[self::FIELD_ALIAS] = [];
                    }
                    $errs[self::FIELD_ALIAS][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_AUTHOR])) {
            $v = $this->getAuthor();
            foreach ($validationRules[self::FIELD_AUTHOR] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_AUTHOR, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_AUTHOR])) {
                        $errs[self::FIELD_AUTHOR] = [];
                    }
                    $errs[self::FIELD_AUTHOR][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SCOPE])) {
            $v = $this->getScope();
            foreach ($validationRules[self::FIELD_SCOPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_SCOPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SCOPE])) {
                        $errs[self::FIELD_SCOPE] = [];
                    }
                    $errs[self::FIELD_SCOPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TOPIC_CODEABLE_CONCEPT])) {
            $v = $this->getTopicCodeableConcept();
            foreach ($validationRules[self::FIELD_TOPIC_CODEABLE_CONCEPT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_TOPIC_CODEABLE_CONCEPT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TOPIC_CODEABLE_CONCEPT])) {
                        $errs[self::FIELD_TOPIC_CODEABLE_CONCEPT] = [];
                    }
                    $errs[self::FIELD_TOPIC_CODEABLE_CONCEPT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TOPIC_REFERENCE])) {
            $v = $this->getTopicReference();
            foreach ($validationRules[self::FIELD_TOPIC_REFERENCE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_TOPIC_REFERENCE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TOPIC_REFERENCE])) {
                        $errs[self::FIELD_TOPIC_REFERENCE] = [];
                    }
                    $errs[self::FIELD_TOPIC_REFERENCE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TYPE])) {
            $v = $this->getType();
            foreach ($validationRules[self::FIELD_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TYPE])) {
                        $errs[self::FIELD_TYPE] = [];
                    }
                    $errs[self::FIELD_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SUB_TYPE])) {
            $v = $this->getSubType();
            foreach ($validationRules[self::FIELD_SUB_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_SUB_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SUB_TYPE])) {
                        $errs[self::FIELD_SUB_TYPE] = [];
                    }
                    $errs[self::FIELD_SUB_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTENT_DEFINITION])) {
            $v = $this->getContentDefinition();
            foreach ($validationRules[self::FIELD_CONTENT_DEFINITION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_CONTENT_DEFINITION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTENT_DEFINITION])) {
                        $errs[self::FIELD_CONTENT_DEFINITION] = [];
                    }
                    $errs[self::FIELD_CONTENT_DEFINITION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TERM])) {
            $v = $this->getTerm();
            foreach ($validationRules[self::FIELD_TERM] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_TERM, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TERM])) {
                        $errs[self::FIELD_TERM] = [];
                    }
                    $errs[self::FIELD_TERM][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SUPPORTING_INFO])) {
            $v = $this->getSupportingInfo();
            foreach ($validationRules[self::FIELD_SUPPORTING_INFO] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_SUPPORTING_INFO, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SUPPORTING_INFO])) {
                        $errs[self::FIELD_SUPPORTING_INFO] = [];
                    }
                    $errs[self::FIELD_SUPPORTING_INFO][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_RELEVANT_HISTORY])) {
            $v = $this->getRelevantHistory();
            foreach ($validationRules[self::FIELD_RELEVANT_HISTORY] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_RELEVANT_HISTORY, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_RELEVANT_HISTORY])) {
                        $errs[self::FIELD_RELEVANT_HISTORY] = [];
                    }
                    $errs[self::FIELD_RELEVANT_HISTORY][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SIGNER])) {
            $v = $this->getSigner();
            foreach ($validationRules[self::FIELD_SIGNER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_SIGNER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SIGNER])) {
                        $errs[self::FIELD_SIGNER] = [];
                    }
                    $errs[self::FIELD_SIGNER][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_FRIENDLY])) {
            $v = $this->getFriendly();
            foreach ($validationRules[self::FIELD_FRIENDLY] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_FRIENDLY, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_FRIENDLY])) {
                        $errs[self::FIELD_FRIENDLY] = [];
                    }
                    $errs[self::FIELD_FRIENDLY][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LEGAL])) {
            $v = $this->getLegal();
            foreach ($validationRules[self::FIELD_LEGAL] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_LEGAL, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LEGAL])) {
                        $errs[self::FIELD_LEGAL] = [];
                    }
                    $errs[self::FIELD_LEGAL][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_RULE])) {
            $v = $this->getRule();
            foreach ($validationRules[self::FIELD_RULE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_RULE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_RULE])) {
                        $errs[self::FIELD_RULE] = [];
                    }
                    $errs[self::FIELD_RULE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LEGALLY_BINDING_ATTACHMENT])) {
            $v = $this->getLegallyBindingAttachment();
            foreach ($validationRules[self::FIELD_LEGALLY_BINDING_ATTACHMENT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_LEGALLY_BINDING_ATTACHMENT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LEGALLY_BINDING_ATTACHMENT])) {
                        $errs[self::FIELD_LEGALLY_BINDING_ATTACHMENT] = [];
                    }
                    $errs[self::FIELD_LEGALLY_BINDING_ATTACHMENT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LEGALLY_BINDING_REFERENCE])) {
            $v = $this->getLegallyBindingReference();
            foreach ($validationRules[self::FIELD_LEGALLY_BINDING_REFERENCE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_CONTRACT, self::FIELD_LEGALLY_BINDING_REFERENCE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LEGALLY_BINDING_REFERENCE])) {
                        $errs[self::FIELD_LEGALLY_BINDING_REFERENCE] = [];
                    }
                    $errs[self::FIELD_LEGALLY_BINDING_REFERENCE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TEXT])) {
            $v = $this->getText();
            foreach ($validationRules[self::FIELD_TEXT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_TEXT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TEXT])) {
                        $errs[self::FIELD_TEXT] = [];
                    }
                    $errs[self::FIELD_TEXT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTAINED])) {
            $v = $this->getContained();
            foreach ($validationRules[self::FIELD_CONTAINED] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_CONTAINED, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTAINED])) {
                        $errs[self::FIELD_CONTAINED] = [];
                    }
                    $errs[self::FIELD_CONTAINED][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach ($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MODIFIER_EXTENSION])) {
            $v = $this->getModifierExtension();
            foreach ($validationRules[self::FIELD_MODIFIER_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_MODIFIER_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MODIFIER_EXTENSION])) {
                        $errs[self::FIELD_MODIFIER_EXTENSION] = [];
                    }
                    $errs[self::FIELD_MODIFIER_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach ($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_META])) {
            $v = $this->getMeta();
            foreach ($validationRules[self::FIELD_META] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_META, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_META])) {
                        $errs[self::FIELD_META] = [];
                    }
                    $errs[self::FIELD_META][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_IMPLICIT_RULES])) {
            $v = $this->getImplicitRules();
            foreach ($validationRules[self::FIELD_IMPLICIT_RULES] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_IMPLICIT_RULES, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_IMPLICIT_RULES])) {
                        $errs[self::FIELD_IMPLICIT_RULES] = [];
                    }
                    $errs[self::FIELD_IMPLICIT_RULES][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LANGUAGE])) {
            $v = $this->getLanguage();
            foreach ($validationRules[self::FIELD_LANGUAGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_LANGUAGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LANGUAGE])) {
                        $errs[self::FIELD_LANGUAGE] = [];
                    }
                    $errs[self::FIELD_LANGUAGE][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRContract $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRContract
     */
    public static function xmlUnserialize($element = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            return null;
        }
        if (is_string($element)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($element, $libxmlOpts);
            if (false === $dom) {
                throw new \DomainException(sprintf('FHIRContract::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function (\libXMLError $err) {
                    return $err->message;
                }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRContract::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRContract(null);
        } elseif (!is_object($type) || !($type instanceof FHIRContract)) {
            throw new \RuntimeException(sprintf(
                'FHIRContract::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRContract or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for ($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_IDENTIFIER === $n->nodeName) {
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($n));
            } elseif (self::FIELD_URL === $n->nodeName) {
                $type->setUrl(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_VERSION === $n->nodeName) {
                $type->setVersion(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_STATUS === $n->nodeName) {
                $type->setStatus(FHIRContractResourceStatusCodes::xmlUnserialize($n));
            } elseif (self::FIELD_LEGAL_STATE === $n->nodeName) {
                $type->setLegalState(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_INSTANTIATES_CANONICAL === $n->nodeName) {
                $type->setInstantiatesCanonical(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_INSTANTIATES_URI === $n->nodeName) {
                $type->setInstantiatesUri(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_CONTENT_DERIVATIVE === $n->nodeName) {
                $type->setContentDerivative(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_ISSUED === $n->nodeName) {
                $type->setIssued(FHIRDateTime::xmlUnserialize($n));
            } elseif (self::FIELD_APPLIES === $n->nodeName) {
                $type->setApplies(FHIRPeriod::xmlUnserialize($n));
            } elseif (self::FIELD_EXPIRATION_TYPE === $n->nodeName) {
                $type->setExpirationType(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_SUBJECT === $n->nodeName) {
                $type->addSubject(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_AUTHORITY === $n->nodeName) {
                $type->addAuthority(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_DOMAIN === $n->nodeName) {
                $type->addDomain(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_SITE === $n->nodeName) {
                $type->addSite(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_NAME === $n->nodeName) {
                $type->setName(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_TITLE === $n->nodeName) {
                $type->setTitle(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_SUBTITLE === $n->nodeName) {
                $type->setSubtitle(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_ALIAS === $n->nodeName) {
                $type->addAlias(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_AUTHOR === $n->nodeName) {
                $type->setAuthor(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_SCOPE === $n->nodeName) {
                $type->setScope(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_TOPIC_CODEABLE_CONCEPT === $n->nodeName) {
                $type->setTopicCodeableConcept(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_TOPIC_REFERENCE === $n->nodeName) {
                $type->setTopicReference(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_TYPE === $n->nodeName) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_SUB_TYPE === $n->nodeName) {
                $type->addSubType(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_CONTENT_DEFINITION === $n->nodeName) {
                $type->setContentDefinition(FHIRContractContentDefinition::xmlUnserialize($n));
            } elseif (self::FIELD_TERM === $n->nodeName) {
                $type->addTerm(FHIRContractTerm::xmlUnserialize($n));
            } elseif (self::FIELD_SUPPORTING_INFO === $n->nodeName) {
                $type->addSupportingInfo(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_RELEVANT_HISTORY === $n->nodeName) {
                $type->addRelevantHistory(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_SIGNER === $n->nodeName) {
                $type->addSigner(FHIRContractSigner::xmlUnserialize($n));
            } elseif (self::FIELD_FRIENDLY === $n->nodeName) {
                $type->addFriendly(FHIRContractFriendly::xmlUnserialize($n));
            } elseif (self::FIELD_LEGAL === $n->nodeName) {
                $type->addLegal(FHIRContractLegal::xmlUnserialize($n));
            } elseif (self::FIELD_RULE === $n->nodeName) {
                $type->addRule(FHIRContractRule::xmlUnserialize($n));
            } elseif (self::FIELD_LEGALLY_BINDING_ATTACHMENT === $n->nodeName) {
                $type->setLegallyBindingAttachment(FHIRAttachment::xmlUnserialize($n));
            } elseif (self::FIELD_LEGALLY_BINDING_REFERENCE === $n->nodeName) {
                $type->setLegallyBindingReference(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_TEXT === $n->nodeName) {
                $type->setText(FHIRNarrative::xmlUnserialize($n));
            } elseif (self::FIELD_CONTAINED === $n->nodeName) {
                for ($ni = 0; $ni < $n->childNodes->length; $ni++) {
                    $nn = $n->childNodes->item($ni);
                    if ($nn instanceof \DOMElement) {
                        $type->addContained(PHPFHIRTypeMap::getContainedTypeFromXML($nn));
                    }
                }
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRId::xmlUnserialize($n));
            } elseif (self::FIELD_META === $n->nodeName) {
                $type->setMeta(FHIRMeta::xmlUnserialize($n));
            } elseif (self::FIELD_IMPLICIT_RULES === $n->nodeName) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_LANGUAGE === $n->nodeName) {
                $type->setLanguage(FHIRCode::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_URL);
        if (null !== $n) {
            $pt = $type->getUrl();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setUrl($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_VERSION);
        if (null !== $n) {
            $pt = $type->getVersion();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setVersion($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_INSTANTIATES_URI);
        if (null !== $n) {
            $pt = $type->getInstantiatesUri();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setInstantiatesUri($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ISSUED);
        if (null !== $n) {
            $pt = $type->getIssued();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setIssued($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_NAME);
        if (null !== $n) {
            $pt = $type->getName();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setName($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_TITLE);
        if (null !== $n) {
            $pt = $type->getTitle();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setTitle($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_SUBTITLE);
        if (null !== $n) {
            $pt = $type->getSubtitle();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setSubtitle($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ALIAS);
        if (null !== $n) {
            $pt = $type->getAlias();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->addAlias($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ID);
        if (null !== $n) {
            $pt = $type->getId();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setId($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_IMPLICIT_RULES);
        if (null !== $n) {
            $pt = $type->getImplicitRules();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setImplicitRules($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_LANGUAGE);
        if (null !== $n) {
            $pt = $type->getLanguage();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setLanguage($n->nodeValue);
            }
        }
        return $type;
    }

    /**
     * @param null|\DOMElement $element
     * @param null|int $libxmlOpts
     * @return \DOMElement
     */
    public function xmlSerialize(\DOMElement $element = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            $dom = new \DOMDocument();
            $dom->loadXML($this->_getFHIRXMLElementDefinition(), $libxmlOpts);
            $element = $dom->documentElement;
        } elseif (null === $element->namespaceURI && '' !== ($xmlns = $this->_getFHIRXMLNamespace())) {
            $element->setAttribute('xmlns', $xmlns);
        }
        parent::xmlSerialize($element);
        if ([] !== ($vs = $this->getIdentifier())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_IDENTIFIER);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getUrl())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_URL);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getVersion())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_VERSION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStatus())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STATUS);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getLegalState())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_LEGAL_STATE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getInstantiatesCanonical())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_INSTANTIATES_CANONICAL);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getInstantiatesUri())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_INSTANTIATES_URI);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getContentDerivative())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CONTENT_DERIVATIVE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getIssued())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ISSUED);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getApplies())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_APPLIES);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getExpirationType())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_EXPIRATION_TYPE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getSubject())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_SUBJECT);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getAuthority())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_AUTHORITY);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getDomain())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_DOMAIN);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getSite())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_SITE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getName())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_NAME);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getTitle())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TITLE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getSubtitle())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_SUBTITLE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getAlias())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_ALIAS);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getAuthor())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_AUTHOR);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getScope())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_SCOPE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getTopicCodeableConcept())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TOPIC_CODEABLE_CONCEPT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getTopicReference())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TOPIC_REFERENCE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getType())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TYPE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getSubType())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_SUB_TYPE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getContentDefinition())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CONTENT_DEFINITION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getTerm())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_TERM);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getSupportingInfo())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_SUPPORTING_INFO);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getRelevantHistory())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_RELEVANT_HISTORY);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getSigner())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_SIGNER);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getFriendly())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_FRIENDLY);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getLegal())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_LEGAL);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getRule())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_RULE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getLegallyBindingAttachment())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_LEGALLY_BINDING_ATTACHMENT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getLegallyBindingReference())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_LEGALLY_BINDING_REFERENCE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if ([] !== ($vs = $this->getIdentifier())) {
            $a[self::FIELD_IDENTIFIER] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_IDENTIFIER][] = $v;
            }
        }
        if (null !== ($v = $this->getUrl())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_URL] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRUri::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_URL_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getVersion())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_VERSION] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_VERSION_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getStatus())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_STATUS] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRContractResourceStatusCodes::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_STATUS_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getLegalState())) {
            $a[self::FIELD_LEGAL_STATE] = $v;
        }
        if (null !== ($v = $this->getInstantiatesCanonical())) {
            $a[self::FIELD_INSTANTIATES_CANONICAL] = $v;
        }
        if (null !== ($v = $this->getInstantiatesUri())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_INSTANTIATES_URI] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRUri::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_INSTANTIATES_URI_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getContentDerivative())) {
            $a[self::FIELD_CONTENT_DERIVATIVE] = $v;
        }
        if (null !== ($v = $this->getIssued())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_ISSUED] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDateTime::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_ISSUED_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getApplies())) {
            $a[self::FIELD_APPLIES] = $v;
        }
        if (null !== ($v = $this->getExpirationType())) {
            $a[self::FIELD_EXPIRATION_TYPE] = $v;
        }
        if ([] !== ($vs = $this->getSubject())) {
            $a[self::FIELD_SUBJECT] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_SUBJECT][] = $v;
            }
        }
        if ([] !== ($vs = $this->getAuthority())) {
            $a[self::FIELD_AUTHORITY] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_AUTHORITY][] = $v;
            }
        }
        if ([] !== ($vs = $this->getDomain())) {
            $a[self::FIELD_DOMAIN] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_DOMAIN][] = $v;
            }
        }
        if ([] !== ($vs = $this->getSite())) {
            $a[self::FIELD_SITE] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_SITE][] = $v;
            }
        }
        if (null !== ($v = $this->getName())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_NAME] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_NAME_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getTitle())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_TITLE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_TITLE_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getSubtitle())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_SUBTITLE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_SUBTITLE_EXT] = $ext;
            }
        }
        if ([] !== ($vs = $this->getAlias())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRString::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_ALIAS] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_ALIAS_EXT] = $exts;
            }
        }
        if (null !== ($v = $this->getAuthor())) {
            $a[self::FIELD_AUTHOR] = $v;
        }
        if (null !== ($v = $this->getScope())) {
            $a[self::FIELD_SCOPE] = $v;
        }
        if (null !== ($v = $this->getTopicCodeableConcept())) {
            $a[self::FIELD_TOPIC_CODEABLE_CONCEPT] = $v;
        }
        if (null !== ($v = $this->getTopicReference())) {
            $a[self::FIELD_TOPIC_REFERENCE] = $v;
        }
        if (null !== ($v = $this->getType())) {
            $a[self::FIELD_TYPE] = $v;
        }
        if ([] !== ($vs = $this->getSubType())) {
            $a[self::FIELD_SUB_TYPE] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_SUB_TYPE][] = $v;
            }
        }
        if (null !== ($v = $this->getContentDefinition())) {
            $a[self::FIELD_CONTENT_DEFINITION] = $v;
        }
        if ([] !== ($vs = $this->getTerm())) {
            $a[self::FIELD_TERM] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_TERM][] = $v;
            }
        }
        if ([] !== ($vs = $this->getSupportingInfo())) {
            $a[self::FIELD_SUPPORTING_INFO] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_SUPPORTING_INFO][] = $v;
            }
        }
        if ([] !== ($vs = $this->getRelevantHistory())) {
            $a[self::FIELD_RELEVANT_HISTORY] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_RELEVANT_HISTORY][] = $v;
            }
        }
        if ([] !== ($vs = $this->getSigner())) {
            $a[self::FIELD_SIGNER] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_SIGNER][] = $v;
            }
        }
        if ([] !== ($vs = $this->getFriendly())) {
            $a[self::FIELD_FRIENDLY] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_FRIENDLY][] = $v;
            }
        }
        if ([] !== ($vs = $this->getLegal())) {
            $a[self::FIELD_LEGAL] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_LEGAL][] = $v;
            }
        }
        if ([] !== ($vs = $this->getRule())) {
            $a[self::FIELD_RULE] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_RULE][] = $v;
            }
        }
        if (null !== ($v = $this->getLegallyBindingAttachment())) {
            $a[self::FIELD_LEGALLY_BINDING_ATTACHMENT] = $v;
        }
        if (null !== ($v = $this->getLegallyBindingReference())) {
            $a[self::FIELD_LEGALLY_BINDING_REFERENCE] = $v;
        }
        return [PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE => $this->_getResourceType()] + $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}
