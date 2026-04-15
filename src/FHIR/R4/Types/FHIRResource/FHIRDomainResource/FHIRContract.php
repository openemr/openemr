<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
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
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\ValueXMLLocationEnum;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLWriter;
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContractResourceStatusCodesList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourceStatusCodes;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
 * policy or agreement.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRContract extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONTRACT;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_URL = 'url';
    public const FIELD_URL_EXT = '_url';
    public const FIELD_VERSION = 'version';
    public const FIELD_VERSION_EXT = '_version';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_LEGAL_STATE = 'legalState';
    public const FIELD_INSTANTIATES_CANONICAL = 'instantiatesCanonical';
    public const FIELD_INSTANTIATES_URI = 'instantiatesUri';
    public const FIELD_INSTANTIATES_URI_EXT = '_instantiatesUri';
    public const FIELD_CONTENT_DERIVATIVE = 'contentDerivative';
    public const FIELD_ISSUED = 'issued';
    public const FIELD_ISSUED_EXT = '_issued';
    public const FIELD_APPLIES = 'applies';
    public const FIELD_EXPIRATION_TYPE = 'expirationType';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_AUTHORITY = 'authority';
    public const FIELD_DOMAIN = 'domain';
    public const FIELD_SITE = 'site';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_TITLE = 'title';
    public const FIELD_TITLE_EXT = '_title';
    public const FIELD_SUBTITLE = 'subtitle';
    public const FIELD_SUBTITLE_EXT = '_subtitle';
    public const FIELD_ALIAS = 'alias';
    public const FIELD_ALIAS_EXT = '_alias';
    public const FIELD_AUTHOR = 'author';
    public const FIELD_SCOPE = 'scope';
    public const FIELD_TOPIC_CODEABLE_CONCEPT = 'topicCodeableConcept';
    public const FIELD_TOPIC_REFERENCE = 'topicReference';
    public const FIELD_TYPE = 'type';
    public const FIELD_SUB_TYPE = 'subType';
    public const FIELD_CONTENT_DEFINITION = 'contentDefinition';
    public const FIELD_TERM = 'term';
    public const FIELD_SUPPORTING_INFO = 'supportingInfo';
    public const FIELD_RELEVANT_HISTORY = 'relevantHistory';
    public const FIELD_SIGNER = 'signer';
    public const FIELD_FRIENDLY = 'friendly';
    public const FIELD_LEGAL = 'legal';
    public const FIELD_RULE = 'rule';
    public const FIELD_LEGALLY_BINDING_ATTACHMENT = 'legallyBindingAttachment';
    public const FIELD_LEGALLY_BINDING_REFERENCE = 'legallyBindingReference';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VERSION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_INSTANTIATES_URI => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ISSUED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TITLE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SUBTITLE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for this Contract or a derivative that references a Source
     * Contract.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Canonical identifier for this contract, represented as a URI (globally unique).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $url;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An edition identifier used for business purposes to label business significant
     * variants.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $version;
    /**
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourceStatusCodes
     */
    #[FHIRContractResourceStatusCodes]
    protected FHIRContractResourceStatusCodes $status;
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $legalState;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined Contract Definition that is adhered to in
     * whole or part by this Contract.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $instantiatesCanonical;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an externally maintained definition that is adhered to in
     * whole or in part by this Contract.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $instantiatesUri;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimal content derived from the basal information source at a specific
     * stage in its lifecycle.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $contentDerivative;
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $issued;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Relevant time or time-period when this Contract is applicable.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $applies;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Event resulting in discontinuation or termination of this Contract instance by
     * one or more parties to the contract.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $expirationType;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The target entity impacted by or of interest to parties to the agreement.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $subject;
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
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $authority;
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
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $domain;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sites in which the contract is complied with, exercised, or in force.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $site;
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $name;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short, descriptive, user-friendly title for this Contract definition,
     * derivative, or instance in any legal state.t giving additional information about
     * its content.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $title;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An explanatory or alternate user-friendly title for this Contract definition,
     * derivative, or instance in any legal state.t giving additional information about
     * its content.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $subtitle;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Alternative representation of the title for this Contract definition,
     * derivative, or instance in any legal state., e.g., a domain specific contract
     * number related to legislation.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $alias;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization that authored the Contract definition,
     * derivative, or instance in any legal state.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $author;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A selector of legal concerns for this Contract definition, derivative, or
     * instance in any legal state.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $scope;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives. (choose any one of topic*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $topicCodeableConcept;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives. (choose any one of topic*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $topicReference;
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sub-category for the Contract that distinguishes the kinds of systems that would
     * be interested in the Contract within the context of the Contract's scope.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $subType;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Precusory content developed with a focus and intent of supporting the formation
     * a Contract instance, which may be associated with and transformable into a
     * Contract.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition
     */
    #[FHIRContractContentDefinition]
    protected FHIRContractContentDefinition $contentDefinition;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * One or more Contract Provisions, which may be related and conveyed as a group,
     * and may contain nested groups.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm>
     */
    #[FHIRContractTerm]
    protected array $term;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information that may be needed by/relevant to the performer in their execution
     * of this term action.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $supportingInfo;
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
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $relevantHistory;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Parties with legal standing in the Contract, including the principal parties,
     * the grantor(s) and grantee(s), which are any person or organization bound by the
     * contract, and any ancillary parties, which facilitate the execution of the
     * contract such as a notary or witness.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner>
     */
    #[FHIRContractSigner]
    protected array $signer;
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
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly>
     */
    #[FHIRContractFriendly]
    protected array $friendly;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Legal expressions or representations of this Contract.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal>
     */
    #[FHIRContractLegal]
    protected array $legal;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Computable Policy Rule Language Representations of this Contract.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule>
     */
    #[FHIRContractRule]
    protected array $rule;
    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract. (choose any one of legallyBinding*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    #[FHIRAttachment]
    protected FHIRAttachment $legallyBindingAttachment;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract. (choose any one of legallyBinding*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $legallyBindingReference;

    /* constructor.php:61 */
    /**
     * FHIRContract Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $url
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $version
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContractResourceStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourceStatusCodes $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $legalState
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $instantiatesCanonical
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $instantiatesUri
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $contentDerivative
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $issued
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $applies
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $expirationType
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $subject
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $authority
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $domain
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $site
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $title
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $subtitle
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $alias
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $author
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $scope
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $topicCodeableConcept
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $topicReference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $subType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition $contentDefinition
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm> $term
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $supportingInfo
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $relevantHistory
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner> $signer
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly> $friendly
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal> $legal
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule> $rule
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $legallyBindingAttachment
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $legallyBindingReference
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRNarrative $text = null,
                                null|iterable $contained = null,
                                null|iterable $extension = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $identifier = null,
                                null|string|FHIRUriPrimitive|FHIRUri $url = null,
                                null|string|FHIRStringPrimitive|FHIRString $version = null,
                                null|string|FHIRContractResourceStatusCodesList|FHIRContractResourceStatusCodes $status = null,
                                null|FHIRCodeableConcept $legalState = null,
                                null|FHIRReference $instantiatesCanonical = null,
                                null|string|FHIRUriPrimitive|FHIRUri $instantiatesUri = null,
                                null|FHIRCodeableConcept $contentDerivative = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $issued = null,
                                null|FHIRPeriod $applies = null,
                                null|FHIRCodeableConcept $expirationType = null,
                                null|iterable $subject = null,
                                null|iterable $authority = null,
                                null|iterable $domain = null,
                                null|iterable $site = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|string|FHIRStringPrimitive|FHIRString $title = null,
                                null|string|FHIRStringPrimitive|FHIRString $subtitle = null,
                                null|iterable $alias = null,
                                null|FHIRReference $author = null,
                                null|FHIRCodeableConcept $scope = null,
                                null|FHIRCodeableConcept $topicCodeableConcept = null,
                                null|FHIRReference $topicReference = null,
                                null|FHIRCodeableConcept $type = null,
                                null|iterable $subType = null,
                                null|FHIRContractContentDefinition $contentDefinition = null,
                                null|iterable $term = null,
                                null|iterable $supportingInfo = null,
                                null|iterable $relevantHistory = null,
                                null|iterable $signer = null,
                                null|iterable $friendly = null,
                                null|iterable $legal = null,
                                null|iterable $rule = null,
                                null|FHIRAttachment $legallyBindingAttachment = null,
                                null|FHIRReference $legallyBindingReference = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            text: $text,
                            contained: $contained,
                            extension: $extension,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $identifier) {
            $this->setIdentifier(...$identifier);
        }
        if (null !== $url) {
            $this->setUrl($url);
        }
        if (null !== $version) {
            $this->setVersion($version);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $legalState) {
            $this->setLegalState($legalState);
        }
        if (null !== $instantiatesCanonical) {
            $this->setInstantiatesCanonical($instantiatesCanonical);
        }
        if (null !== $instantiatesUri) {
            $this->setInstantiatesUri($instantiatesUri);
        }
        if (null !== $contentDerivative) {
            $this->setContentDerivative($contentDerivative);
        }
        if (null !== $issued) {
            $this->setIssued($issued);
        }
        if (null !== $applies) {
            $this->setApplies($applies);
        }
        if (null !== $expirationType) {
            $this->setExpirationType($expirationType);
        }
        if (null !== $subject) {
            $this->setSubject(...$subject);
        }
        if (null !== $authority) {
            $this->setAuthority(...$authority);
        }
        if (null !== $domain) {
            $this->setDomain(...$domain);
        }
        if (null !== $site) {
            $this->setSite(...$site);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $title) {
            $this->setTitle($title);
        }
        if (null !== $subtitle) {
            $this->setSubtitle($subtitle);
        }
        if (null !== $alias) {
            $this->setAlias(...$alias);
        }
        if (null !== $author) {
            $this->setAuthor($author);
        }
        if (null !== $scope) {
            $this->setScope($scope);
        }
        if (null !== $topicCodeableConcept) {
            $this->setTopicCodeableConcept($topicCodeableConcept);
        }
        if (null !== $topicReference) {
            $this->setTopicReference($topicReference);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $subType) {
            $this->setSubType(...$subType);
        }
        if (null !== $contentDefinition) {
            $this->setContentDefinition($contentDefinition);
        }
        if (null !== $term) {
            $this->setTerm(...$term);
        }
        if (null !== $supportingInfo) {
            $this->setSupportingInfo(...$supportingInfo);
        }
        if (null !== $relevantHistory) {
            $this->setRelevantHistory(...$relevantHistory);
        }
        if (null !== $signer) {
            $this->setSigner(...$signer);
        }
        if (null !== $friendly) {
            $this->setFriendly(...$friendly);
        }
        if (null !== $legal) {
            $this->setLegal(...$legal);
        }
        if (null !== $rule) {
            $this->setRule(...$rule);
        }
        if (null !== $legallyBindingAttachment) {
            $this->setLegallyBindingAttachment($legallyBindingAttachment);
        }
        if (null !== $legallyBindingReference) {
            $this->setLegallyBindingReference($legallyBindingReference);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for this Contract or a derivative that references a Source
     * Contract.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifier(): array
    {
        return $this->identifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifierIterator(): iterable
    {
        if (!isset($this->identifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->identifier);
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function addIdentifier(FHIRIdentifier $identifier): self
    {
        if (!isset($this->identifier)) {
            $this->identifier = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier ...$identifier
     * @return static
     */
    public function setIdentifier(FHIRIdentifier ...$identifier): self
    {
        if ([] === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Canonical identifier for this contract, represented as a URI (globally unique).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getUrl(): null|FHIRUri
    {
        return $this->url ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Canonical identifier for this contract, represented as a URI (globally unique).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $url
     * @return static
     */
    public function setUrl(null|string|FHIRUriPrimitive|FHIRUri $url): self
    {
        if (null === $url) {
            unset($this->url);
            return $this;
        }
        if (!($url instanceof FHIRUri)) {
            $url = new FHIRUri(value: $url);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getVersion(): null|FHIRString
    {
        return $this->version ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An edition identifier used for business purposes to label business significant
     * variants.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $version
     * @return static
     */
    public function setVersion(null|string|FHIRStringPrimitive|FHIRString $version): self
    {
        if (null === $version) {
            unset($this->version);
            return $this;
        }
        if (!($version instanceof FHIRString)) {
            $version = new FHIRString(value: $version);
        }
        $this->version = $version;
        return $this;
    }

    /**
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourceStatusCodes
     */
    public function getStatus(): null|FHIRContractResourceStatusCodes
    {
        return $this->status ?? null;
    }

    /**
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContractResourceStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourceStatusCodes $status
     * @return static
     */
    public function setStatus(null|string|FHIRContractResourceStatusCodesList|FHIRContractResourceStatusCodes $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRContractResourceStatusCodes)) {
            $status = new FHIRContractResourceStatusCodes(value: $status);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getLegalState(): null|FHIRCodeableConcept
    {
        return $this->legalState ?? null;
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $legalState
     * @return static
     */
    public function setLegalState(null|FHIRCodeableConcept $legalState): self
    {
        if (null === $legalState) {
            unset($this->legalState);
            return $this;
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getInstantiatesCanonical(): null|FHIRReference
    {
        return $this->instantiatesCanonical ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined Contract Definition that is adhered to in
     * whole or part by this Contract.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $instantiatesCanonical
     * @return static
     */
    public function setInstantiatesCanonical(null|FHIRReference $instantiatesCanonical): self
    {
        if (null === $instantiatesCanonical) {
            unset($this->instantiatesCanonical);
            return $this;
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getInstantiatesUri(): null|FHIRUri
    {
        return $this->instantiatesUri ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an externally maintained definition that is adhered to in
     * whole or in part by this Contract.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $instantiatesUri
     * @return static
     */
    public function setInstantiatesUri(null|string|FHIRUriPrimitive|FHIRUri $instantiatesUri): self
    {
        if (null === $instantiatesUri) {
            unset($this->instantiatesUri);
            return $this;
        }
        if (!($instantiatesUri instanceof FHIRUri)) {
            $instantiatesUri = new FHIRUri(value: $instantiatesUri);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getContentDerivative(): null|FHIRCodeableConcept
    {
        return $this->contentDerivative ?? null;
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $contentDerivative
     * @return static
     */
    public function setContentDerivative(null|FHIRCodeableConcept $contentDerivative): self
    {
        if (null === $contentDerivative) {
            unset($this->contentDerivative);
            return $this;
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getIssued(): null|FHIRDateTime
    {
        return $this->issued ?? null;
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
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $issued
     * @return static
     */
    public function setIssued(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $issued): self
    {
        if (null === $issued) {
            unset($this->issued);
            return $this;
        }
        if (!($issued instanceof FHIRDateTime)) {
            $issued = new FHIRDateTime(value: $issued);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getApplies(): null|FHIRPeriod
    {
        return $this->applies ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Relevant time or time-period when this Contract is applicable.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $applies
     * @return static
     */
    public function setApplies(null|FHIRPeriod $applies): self
    {
        if (null === $applies) {
            unset($this->applies);
            return $this;
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getExpirationType(): null|FHIRCodeableConcept
    {
        return $this->expirationType ?? null;
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $expirationType
     * @return static
     */
    public function setExpirationType(null|FHIRCodeableConcept $expirationType): self
    {
        if (null === $expirationType) {
            unset($this->expirationType);
            return $this;
        }
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSubject(): array
    {
        return $this->subject ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSubjectIterator(): iterable
    {
        if (!isset($this->subject)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->subject);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The target entity impacted by or of interest to parties to the agreement.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @return static
     */
    public function addSubject(FHIRReference $subject): self
    {
        if (!isset($this->subject)) {
            $this->subject = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$subject
     * @return static
     */
    public function setSubject(FHIRReference ...$subject): self
    {
        if ([] === $subject) {
            unset($this->subject);
            return $this;
        }
        $this->subject = $subject;
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAuthority(): array
    {
        return $this->authority ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAuthorityIterator(): iterable
    {
        if (!isset($this->authority)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->authority);
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $authority
     * @return static
     */
    public function addAuthority(FHIRReference $authority): self
    {
        if (!isset($this->authority)) {
            $this->authority = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$authority
     * @return static
     */
    public function setAuthority(FHIRReference ...$authority): self
    {
        if ([] === $authority) {
            unset($this->authority);
            return $this;
        }
        $this->authority = $authority;
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getDomain(): array
    {
        return $this->domain ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getDomainIterator(): iterable
    {
        if (!isset($this->domain)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->domain);
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $domain
     * @return static
     */
    public function addDomain(FHIRReference $domain): self
    {
        if (!isset($this->domain)) {
            $this->domain = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$domain
     * @return static
     */
    public function setDomain(FHIRReference ...$domain): self
    {
        if ([] === $domain) {
            unset($this->domain);
            return $this;
        }
        $this->domain = $domain;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sites in which the contract is complied with, exercised, or in force.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSite(): array
    {
        return $this->site ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSiteIterator(): iterable
    {
        if (!isset($this->site)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->site);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Sites in which the contract is complied with, exercised, or in force.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $site
     * @return static
     */
    public function addSite(FHIRReference $site): self
    {
        if (!isset($this->site)) {
            $this->site = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$site
     * @return static
     */
    public function setSite(FHIRReference ...$site): self
    {
        if ([] === $site) {
            unset($this->site);
            return $this;
        }
        $this->site = $site;
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getName(): null|FHIRString
    {
        return $this->name ?? null;
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
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @return static
     */
    public function setName(null|string|FHIRStringPrimitive|FHIRString $name): self
    {
        if (null === $name) {
            unset($this->name);
            return $this;
        }
        if (!($name instanceof FHIRString)) {
            $name = new FHIRString(value: $name);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getTitle(): null|FHIRString
    {
        return $this->title ?? null;
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
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $title
     * @return static
     */
    public function setTitle(null|string|FHIRStringPrimitive|FHIRString $title): self
    {
        if (null === $title) {
            unset($this->title);
            return $this;
        }
        if (!($title instanceof FHIRString)) {
            $title = new FHIRString(value: $title);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getSubtitle(): null|FHIRString
    {
        return $this->subtitle ?? null;
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
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $subtitle
     * @return static
     */
    public function setSubtitle(null|string|FHIRStringPrimitive|FHIRString $subtitle): self
    {
        if (null === $subtitle) {
            unset($this->subtitle);
            return $this;
        }
        if (!($subtitle instanceof FHIRString)) {
            $subtitle = new FHIRString(value: $subtitle);
        }
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getAlias(): array
    {
        return $this->alias ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getAliasIterator(): iterable
    {
        if (!isset($this->alias)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->alias);
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
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $alias
     * @return static
     */
    public function addAlias(string|FHIRStringPrimitive|FHIRString $alias): self
    {
        if (!($alias instanceof FHIRString)) {
            $alias = new FHIRString(value: $alias);
        }
        if (!isset($this->alias)) {
            $this->alias = [];
        }
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
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$alias
     * @return static
     */
    public function setAlias(string|FHIRStringPrimitive|FHIRString ...$alias): self
    {
        if ([] === $alias) {
            unset($this->alias);
            return $this;
        }
        $this->alias = [];
        foreach($alias as $v) {
            if ($v instanceof FHIRString) {
                $this->alias[] = $v;
            } else {
                $this->alias[] = new FHIRString(value: $v);
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getAuthor(): null|FHIRReference
    {
        return $this->author ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization that authored the Contract definition,
     * derivative, or instance in any legal state.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $author
     * @return static
     */
    public function setAuthor(null|FHIRReference $author): self
    {
        if (null === $author) {
            unset($this->author);
            return $this;
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getScope(): null|FHIRCodeableConcept
    {
        return $this->scope ?? null;
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $scope
     * @return static
     */
    public function setScope(null|FHIRCodeableConcept $scope): self
    {
        if (null === $scope) {
            unset($this->scope);
            return $this;
        }
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
     * contractual objectives. (choose any one of topic*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getTopicCodeableConcept(): null|FHIRCodeableConcept
    {
        return $this->topicCodeableConcept ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives. (choose any one of topic*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $topicCodeableConcept
     * @return static
     */
    public function setTopicCodeableConcept(null|FHIRCodeableConcept $topicCodeableConcept): self
    {
        if (null === $topicCodeableConcept) {
            unset($this->topicCodeableConcept);
            return $this;
        }
        $this->topicCodeableConcept = $topicCodeableConcept;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives. (choose any one of topic*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getTopicReference(): null|FHIRReference
    {
        return $this->topicReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Narrows the range of legal concerns to focus on the achievement of specific
     * contractual objectives. (choose any one of topic*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $topicReference
     * @return static
     */
    public function setTopicReference(null|FHIRReference $topicReference): self
    {
        if (null === $topicReference) {
            unset($this->topicReference);
            return $this;
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getType(): null|FHIRCodeableConcept
    {
        return $this->type ?? null;
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function setType(null|FHIRCodeableConcept $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSubType(): array
    {
        return $this->subType ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSubTypeIterator(): iterable
    {
        if (!isset($this->subType)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->subType);
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $subType
     * @return static
     */
    public function addSubType(FHIRCodeableConcept $subType): self
    {
        if (!isset($this->subType)) {
            $this->subType = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$subType
     * @return static
     */
    public function setSubType(FHIRCodeableConcept ...$subType): self
    {
        if ([] === $subType) {
            unset($this->subType);
            return $this;
        }
        $this->subType = $subType;
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition
     */
    public function getContentDefinition(): null|FHIRContractContentDefinition
    {
        return $this->contentDefinition ?? null;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Precusory content developed with a focus and intent of supporting the formation
     * a Contract instance, which may be associated with and transformable into a
     * Contract.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition $contentDefinition
     * @return static
     */
    public function setContentDefinition(null|FHIRContractContentDefinition $contentDefinition): self
    {
        if (null === $contentDefinition) {
            unset($this->contentDefinition);
            return $this;
        }
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm>
     */
    public function getTerm(): array
    {
        return $this->term ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm>
     */
    public function getTermIterator(): iterable
    {
        if (!isset($this->term)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->term);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * One or more Contract Provisions, which may be related and conveyed as a group,
     * and may contain nested groups.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm $term
     * @return static
     */
    public function addTerm(FHIRContractTerm $term): self
    {
        if (!isset($this->term)) {
            $this->term = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm ...$term
     * @return static
     */
    public function setTerm(FHIRContractTerm ...$term): self
    {
        if ([] === $term) {
            unset($this->term);
            return $this;
        }
        $this->term = $term;
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSupportingInfo(): array
    {
        return $this->supportingInfo ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSupportingInfoIterator(): iterable
    {
        if (!isset($this->supportingInfo)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->supportingInfo);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information that may be needed by/relevant to the performer in their execution
     * of this term action.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $supportingInfo
     * @return static
     */
    public function addSupportingInfo(FHIRReference $supportingInfo): self
    {
        if (!isset($this->supportingInfo)) {
            $this->supportingInfo = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$supportingInfo
     * @return static
     */
    public function setSupportingInfo(FHIRReference ...$supportingInfo): self
    {
        if ([] === $supportingInfo) {
            unset($this->supportingInfo);
            return $this;
        }
        $this->supportingInfo = $supportingInfo;
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRelevantHistory(): array
    {
        return $this->relevantHistory ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRelevantHistoryIterator(): iterable
    {
        if (!isset($this->relevantHistory)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->relevantHistory);
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $relevantHistory
     * @return static
     */
    public function addRelevantHistory(FHIRReference $relevantHistory): self
    {
        if (!isset($this->relevantHistory)) {
            $this->relevantHistory = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$relevantHistory
     * @return static
     */
    public function setRelevantHistory(FHIRReference ...$relevantHistory): self
    {
        if ([] === $relevantHistory) {
            unset($this->relevantHistory);
            return $this;
        }
        $this->relevantHistory = $relevantHistory;
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner>
     */
    public function getSigner(): array
    {
        return $this->signer ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner>
     */
    public function getSignerIterator(): iterable
    {
        if (!isset($this->signer)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->signer);
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner $signer
     * @return static
     */
    public function addSigner(FHIRContractSigner $signer): self
    {
        if (!isset($this->signer)) {
            $this->signer = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSigner ...$signer
     * @return static
     */
    public function setSigner(FHIRContractSigner ...$signer): self
    {
        if ([] === $signer) {
            unset($this->signer);
            return $this;
        }
        $this->signer = $signer;
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
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly>
     */
    public function getFriendly(): array
    {
        return $this->friendly ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly>
     */
    public function getFriendlyIterator(): iterable
    {
        if (!isset($this->friendly)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->friendly);
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly $friendly
     * @return static
     */
    public function addFriendly(FHIRContractFriendly $friendly): self
    {
        if (!isset($this->friendly)) {
            $this->friendly = [];
        }
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
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractFriendly ...$friendly
     * @return static
     */
    public function setFriendly(FHIRContractFriendly ...$friendly): self
    {
        if ([] === $friendly) {
            unset($this->friendly);
            return $this;
        }
        $this->friendly = $friendly;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Legal expressions or representations of this Contract.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal>
     */
    public function getLegal(): array
    {
        return $this->legal ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal>
     */
    public function getLegalIterator(): iterable
    {
        if (!isset($this->legal)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->legal);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Legal expressions or representations of this Contract.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal $legal
     * @return static
     */
    public function addLegal(FHIRContractLegal $legal): self
    {
        if (!isset($this->legal)) {
            $this->legal = [];
        }
        $this->legal[] = $legal;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Legal expressions or representations of this Contract.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractLegal ...$legal
     * @return static
     */
    public function setLegal(FHIRContractLegal ...$legal): self
    {
        if ([] === $legal) {
            unset($this->legal);
            return $this;
        }
        $this->legal = $legal;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Computable Policy Rule Language Representations of this Contract.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule>
     */
    public function getRule(): array
    {
        return $this->rule ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule>
     */
    public function getRuleIterator(): iterable
    {
        if (!isset($this->rule)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->rule);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Computable Policy Rule Language Representations of this Contract.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule $rule
     * @return static
     */
    public function addRule(FHIRContractRule $rule): self
    {
        if (!isset($this->rule)) {
            $this->rule = [];
        }
        $this->rule[] = $rule;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * List of Computable Policy Rule Language Representations of this Contract.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractRule ...$rule
     * @return static
     */
    public function setRule(FHIRContractRule ...$rule): self
    {
        if ([] === $rule) {
            unset($this->rule);
            return $this;
        }
        $this->rule = $rule;
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
     * Contract. (choose any one of legallyBinding*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    public function getLegallyBindingAttachment(): null|FHIRAttachment
    {
        return $this->legallyBindingAttachment ?? null;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract. (choose any one of legallyBinding*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $legallyBindingAttachment
     * @return static
     */
    public function setLegallyBindingAttachment(null|FHIRAttachment $legallyBindingAttachment): self
    {
        if (null === $legallyBindingAttachment) {
            unset($this->legallyBindingAttachment);
            return $this;
        }
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
     * Contract. (choose any one of legallyBinding*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getLegallyBindingReference(): null|FHIRReference
    {
        return $this->legallyBindingReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Legally binding Contract: This is the signed and legally recognized
     * representation of the Contract, which is considered the "source of truth" and
     * which would be the basis for legal action related to enforcement of this
     * Contract. (choose any one of legallyBinding*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $legallyBindingReference
     * @return static
     */
    public function setLegallyBindingReference(null|FHIRReference $legallyBindingReference): self
    {
        if (null === $legallyBindingReference) {
            unset($this->legallyBindingReference);
            return $this;
        }
        $this->legallyBindingReference = $legallyBindingReference;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRContract $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRContract
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRContract)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRNarrative::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINED === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->addContained($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_URL === $cen) {
                $type->setUrl(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VERSION === $cen) {
                $type->setVersion(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRContractResourceStatusCodes::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LEGAL_STATE === $cen) {
                $type->setLegalState(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSTANTIATES_CANONICAL === $cen) {
                $type->setInstantiatesCanonical(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSTANTIATES_URI === $cen) {
                $type->setInstantiatesUri(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTENT_DERIVATIVE === $cen) {
                $type->setContentDerivative(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ISSUED === $cen) {
                $type->setIssued(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_APPLIES === $cen) {
                $type->setApplies(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXPIRATION_TYPE === $cen) {
                $type->setExpirationType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->addSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHORITY === $cen) {
                $type->addAuthority(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOMAIN === $cen) {
                $type->addDomain(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SITE === $cen) {
                $type->addSite(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TITLE === $cen) {
                $type->setTitle(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBTITLE === $cen) {
                $type->setSubtitle(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ALIAS === $cen) {
                $type->addAlias(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHOR === $cen) {
                $type->setAuthor(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SCOPE === $cen) {
                $type->setScope(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TOPIC_CODEABLE_CONCEPT === $cen) {
                $type->setTopicCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TOPIC_REFERENCE === $cen) {
                $type->setTopicReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUB_TYPE === $cen) {
                $type->addSubType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTENT_DEFINITION === $cen) {
                $type->setContentDefinition(FHIRContractContentDefinition::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TERM === $cen) {
                $type->addTerm(FHIRContractTerm::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUPPORTING_INFO === $cen) {
                $type->addSupportingInfo(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELEVANT_HISTORY === $cen) {
                $type->addRelevantHistory(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SIGNER === $cen) {
                $type->addSigner(FHIRContractSigner::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FRIENDLY === $cen) {
                $type->addFriendly(FHIRContractFriendly::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LEGAL === $cen) {
                $type->addLegal(FHIRContractLegal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RULE === $cen) {
                $type->addRule(FHIRContractRule::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LEGALLY_BINDING_ATTACHMENT === $cen) {
                $type->setLegallyBindingAttachment(FHIRAttachment::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LEGALLY_BINDING_REFERENCE === $cen) {
                $type->setLegallyBindingReference(FHIRReference::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_URL])) {
            if (isset($type->url)) {
                $type->url->setValue((string)$attributes[self::FIELD_URL]);
            } else {
                $type->setUrl((string)$attributes[self::FIELD_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VERSION])) {
            if (isset($type->version)) {
                $type->version->setValue((string)$attributes[self::FIELD_VERSION]);
            } else {
                $type->setVersion((string)$attributes[self::FIELD_VERSION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VERSION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_INSTANTIATES_URI])) {
            if (isset($type->instantiatesUri)) {
                $type->instantiatesUri->setValue((string)$attributes[self::FIELD_INSTANTIATES_URI]);
            } else {
                $type->setInstantiatesUri((string)$attributes[self::FIELD_INSTANTIATES_URI]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_INSTANTIATES_URI, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ISSUED])) {
            if (isset($type->issued)) {
                $type->issued->setValue((string)$attributes[self::FIELD_ISSUED]);
            } else {
                $type->setIssued((string)$attributes[self::FIELD_ISSUED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ISSUED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TITLE])) {
            if (isset($type->title)) {
                $type->title->setValue((string)$attributes[self::FIELD_TITLE]);
            } else {
                $type->setTitle((string)$attributes[self::FIELD_TITLE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TITLE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SUBTITLE])) {
            if (isset($type->subtitle)) {
                $type->subtitle->setValue((string)$attributes[self::FIELD_SUBTITLE]);
            } else {
                $type->setSubtitle((string)$attributes[self::FIELD_SUBTITLE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SUBTITLE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('Contract', $this->_getSourceXMLNS());
        }
        if (isset($this->url) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_URL]) {
            $xw->writeAttribute(self::FIELD_URL, $this->url->_getValueAsString());
        }
        if (isset($this->version) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VERSION]) {
            $xw->writeAttribute(self::FIELD_VERSION, $this->version->_getValueAsString());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->instantiatesUri) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_INSTANTIATES_URI]) {
            $xw->writeAttribute(self::FIELD_INSTANTIATES_URI, $this->instantiatesUri->_getValueAsString());
        }
        if (isset($this->issued) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ISSUED]) {
            $xw->writeAttribute(self::FIELD_ISSUED, $this->issued->_getValueAsString());
        }
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->title) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TITLE]) {
            $xw->writeAttribute(self::FIELD_TITLE, $this->title->_getValueAsString());
        }
        if (isset($this->subtitle) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SUBTITLE]) {
            $xw->writeAttribute(self::FIELD_SUBTITLE, $this->subtitle->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->url)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_URL]
                || $this->url->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_URL);
            $this->url->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_URL]);
            $xw->endElement();
        }
        if (isset($this->version)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VERSION]
                || $this->version->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VERSION);
            $this->version->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VERSION]);
            $xw->endElement();
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->legalState)) {
            $xw->startElement(self::FIELD_LEGAL_STATE);
            $this->legalState->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->instantiatesCanonical)) {
            $xw->startElement(self::FIELD_INSTANTIATES_CANONICAL);
            $this->instantiatesCanonical->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->instantiatesUri)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_INSTANTIATES_URI]
                || $this->instantiatesUri->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_INSTANTIATES_URI);
            $this->instantiatesUri->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_INSTANTIATES_URI]);
            $xw->endElement();
        }
        if (isset($this->contentDerivative)) {
            $xw->startElement(self::FIELD_CONTENT_DERIVATIVE);
            $this->contentDerivative->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->issued)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ISSUED]
                || $this->issued->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ISSUED);
            $this->issued->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ISSUED]);
            $xw->endElement();
        }
        if (isset($this->applies)) {
            $xw->startElement(self::FIELD_APPLIES);
            $this->applies->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->expirationType)) {
            $xw->startElement(self::FIELD_EXPIRATION_TYPE);
            $this->expirationType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subject)) {
            foreach ($this->subject as $v) {
                $xw->startElement(self::FIELD_SUBJECT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->authority)) {
            foreach ($this->authority as $v) {
                $xw->startElement(self::FIELD_AUTHORITY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->domain)) {
            foreach ($this->domain as $v) {
                $xw->startElement(self::FIELD_DOMAIN);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->site)) {
            foreach ($this->site as $v) {
                $xw->startElement(self::FIELD_SITE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->title)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TITLE]
                || $this->title->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TITLE);
            $this->title->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TITLE]);
            $xw->endElement();
        }
        if (isset($this->subtitle)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SUBTITLE]
                || $this->subtitle->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SUBTITLE);
            $this->subtitle->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SUBTITLE]);
            $xw->endElement();
        }
        if (isset($this->alias) && [] !== $this->alias) {
            foreach($this->alias as $v) {
                $xw->startElement(self::FIELD_ALIAS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->author)) {
            $xw->startElement(self::FIELD_AUTHOR);
            $this->author->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->scope)) {
            $xw->startElement(self::FIELD_SCOPE);
            $this->scope->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->topicCodeableConcept)) {
            $xw->startElement(self::FIELD_TOPIC_CODEABLE_CONCEPT);
            $this->topicCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->topicReference)) {
            $xw->startElement(self::FIELD_TOPIC_REFERENCE);
            $this->topicReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subType)) {
            foreach ($this->subType as $v) {
                $xw->startElement(self::FIELD_SUB_TYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->contentDefinition)) {
            $xw->startElement(self::FIELD_CONTENT_DEFINITION);
            $this->contentDefinition->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->term)) {
            foreach ($this->term as $v) {
                $xw->startElement(self::FIELD_TERM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->supportingInfo)) {
            foreach ($this->supportingInfo as $v) {
                $xw->startElement(self::FIELD_SUPPORTING_INFO);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->relevantHistory)) {
            foreach ($this->relevantHistory as $v) {
                $xw->startElement(self::FIELD_RELEVANT_HISTORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->signer)) {
            foreach ($this->signer as $v) {
                $xw->startElement(self::FIELD_SIGNER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->friendly)) {
            foreach ($this->friendly as $v) {
                $xw->startElement(self::FIELD_FRIENDLY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->legal)) {
            foreach ($this->legal as $v) {
                $xw->startElement(self::FIELD_LEGAL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->rule)) {
            foreach ($this->rule as $v) {
                $xw->startElement(self::FIELD_RULE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->legallyBindingAttachment)) {
            $xw->startElement(self::FIELD_LEGALLY_BINDING_ATTACHMENT);
            $this->legallyBindingAttachment->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->legallyBindingReference)) {
            $xw->startElement(self::FIELD_LEGALLY_BINDING_REFERENCE);
            $this->legallyBindingReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRContract $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRContract
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            if (isset($decoded->resourceType) && $decoded->resourceType !== static::FHIR_TYPE_NAME) {
                throw new \DomainException(sprintf(
                    '%s::jsonUnserialize - Cannot unmarshal data for resource type "%s" into this type.',
                    ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                    $decoded->resourceType,
                ));
            }
            $type = new static();
        } else if (!($type instanceof FHIRContract)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_object($decoded->identifier)) {
                $vals = [$decoded->identifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER, true);
            } else {
                $vals = $decoded->identifier;
            }
            foreach($vals as $v) {
                $type->addIdentifier(FHIRIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->url)
            || isset($decoded->_url)
            || property_exists($decoded, self::FIELD_URL)
            || property_exists($decoded, self::FIELD_URL_EXT)) {
            $v = $decoded->_url ?? new \stdClass();
            $v->value = $decoded->url ?? null;
            $type->setUrl(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->version)
            || isset($decoded->_version)
            || property_exists($decoded, self::FIELD_VERSION)
            || property_exists($decoded, self::FIELD_VERSION_EXT)) {
            $v = $decoded->_version ?? new \stdClass();
            $v->value = $decoded->version ?? null;
            $type->setVersion(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRContractResourceStatusCodes::jsonUnserialize($v, $config));
        }
        if (isset($decoded->legalState) || property_exists($decoded, self::FIELD_LEGAL_STATE)) {
            if (is_array($decoded->legalState)) {
                $type->setLegalState(FHIRCodeableConcept::jsonUnserialize(reset($decoded->legalState), $config));
            } else {
                $type->setLegalState(FHIRCodeableConcept::jsonUnserialize($decoded->legalState, $config));
            }
        }
        if (isset($decoded->instantiatesCanonical) || property_exists($decoded, self::FIELD_INSTANTIATES_CANONICAL)) {
            if (is_array($decoded->instantiatesCanonical)) {
                $type->setInstantiatesCanonical(FHIRReference::jsonUnserialize(reset($decoded->instantiatesCanonical), $config));
            } else {
                $type->setInstantiatesCanonical(FHIRReference::jsonUnserialize($decoded->instantiatesCanonical, $config));
            }
        }
        if (isset($decoded->instantiatesUri)
            || isset($decoded->_instantiatesUri)
            || property_exists($decoded, self::FIELD_INSTANTIATES_URI)
            || property_exists($decoded, self::FIELD_INSTANTIATES_URI_EXT)) {
            $v = $decoded->_instantiatesUri ?? new \stdClass();
            $v->value = $decoded->instantiatesUri ?? null;
            $type->setInstantiatesUri(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->contentDerivative) || property_exists($decoded, self::FIELD_CONTENT_DERIVATIVE)) {
            if (is_array($decoded->contentDerivative)) {
                $type->setContentDerivative(FHIRCodeableConcept::jsonUnserialize(reset($decoded->contentDerivative), $config));
            } else {
                $type->setContentDerivative(FHIRCodeableConcept::jsonUnserialize($decoded->contentDerivative, $config));
            }
        }
        if (isset($decoded->issued)
            || isset($decoded->_issued)
            || property_exists($decoded, self::FIELD_ISSUED)
            || property_exists($decoded, self::FIELD_ISSUED_EXT)) {
            $v = $decoded->_issued ?? new \stdClass();
            $v->value = $decoded->issued ?? null;
            $type->setIssued(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->applies) || property_exists($decoded, self::FIELD_APPLIES)) {
            if (is_array($decoded->applies)) {
                $type->setApplies(FHIRPeriod::jsonUnserialize(reset($decoded->applies), $config));
            } else {
                $type->setApplies(FHIRPeriod::jsonUnserialize($decoded->applies, $config));
            }
        }
        if (isset($decoded->expirationType) || property_exists($decoded, self::FIELD_EXPIRATION_TYPE)) {
            if (is_array($decoded->expirationType)) {
                $type->setExpirationType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->expirationType), $config));
            } else {
                $type->setExpirationType(FHIRCodeableConcept::jsonUnserialize($decoded->expirationType, $config));
            }
        }
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_object($decoded->subject)) {
                $vals = [$decoded->subject];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUBJECT, true);
            } else {
                $vals = $decoded->subject;
            }
            foreach($vals as $v) {
                $type->addSubject(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->authority) || property_exists($decoded, self::FIELD_AUTHORITY)) {
            if (is_object($decoded->authority)) {
                $vals = [$decoded->authority];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_AUTHORITY, true);
            } else {
                $vals = $decoded->authority;
            }
            foreach($vals as $v) {
                $type->addAuthority(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->domain) || property_exists($decoded, self::FIELD_DOMAIN)) {
            if (is_object($decoded->domain)) {
                $vals = [$decoded->domain];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DOMAIN, true);
            } else {
                $vals = $decoded->domain;
            }
            foreach($vals as $v) {
                $type->addDomain(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->site) || property_exists($decoded, self::FIELD_SITE)) {
            if (is_object($decoded->site)) {
                $vals = [$decoded->site];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SITE, true);
            } else {
                $vals = $decoded->site;
            }
            foreach($vals as $v) {
                $type->addSite(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->title)
            || isset($decoded->_title)
            || property_exists($decoded, self::FIELD_TITLE)
            || property_exists($decoded, self::FIELD_TITLE_EXT)) {
            $v = $decoded->_title ?? new \stdClass();
            $v->value = $decoded->title ?? null;
            $type->setTitle(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->subtitle)
            || isset($decoded->_subtitle)
            || property_exists($decoded, self::FIELD_SUBTITLE)
            || property_exists($decoded, self::FIELD_SUBTITLE_EXT)) {
            $v = $decoded->_subtitle ?? new \stdClass();
            $v->value = $decoded->subtitle ?? null;
            $type->setSubtitle(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->alias)
            || isset($decoded->_alias)
            || property_exists($decoded, self::FIELD_ALIAS)
            || property_exists($decoded, self::FIELD_ALIAS_EXT)) {
            $vals = (array)($decoded->alias ?? []);
            $exts = (array)($decoded->_alias ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addAlias(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->author) || property_exists($decoded, self::FIELD_AUTHOR)) {
            if (is_array($decoded->author)) {
                $type->setAuthor(FHIRReference::jsonUnserialize(reset($decoded->author), $config));
            } else {
                $type->setAuthor(FHIRReference::jsonUnserialize($decoded->author, $config));
            }
        }
        if (isset($decoded->scope) || property_exists($decoded, self::FIELD_SCOPE)) {
            if (is_array($decoded->scope)) {
                $type->setScope(FHIRCodeableConcept::jsonUnserialize(reset($decoded->scope), $config));
            } else {
                $type->setScope(FHIRCodeableConcept::jsonUnserialize($decoded->scope, $config));
            }
        }
        if (isset($decoded->topicCodeableConcept) || property_exists($decoded, self::FIELD_TOPIC_CODEABLE_CONCEPT)) {
            if (is_array($decoded->topicCodeableConcept)) {
                $type->setTopicCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->topicCodeableConcept), $config));
            } else {
                $type->setTopicCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->topicCodeableConcept, $config));
            }
        }
        if (isset($decoded->topicReference) || property_exists($decoded, self::FIELD_TOPIC_REFERENCE)) {
            if (is_array($decoded->topicReference)) {
                $type->setTopicReference(FHIRReference::jsonUnserialize(reset($decoded->topicReference), $config));
            } else {
                $type->setTopicReference(FHIRReference::jsonUnserialize($decoded->topicReference, $config));
            }
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->subType) || property_exists($decoded, self::FIELD_SUB_TYPE)) {
            if (is_object($decoded->subType)) {
                $vals = [$decoded->subType];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUB_TYPE, true);
            } else {
                $vals = $decoded->subType;
            }
            foreach($vals as $v) {
                $type->addSubType(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->contentDefinition) || property_exists($decoded, self::FIELD_CONTENT_DEFINITION)) {
            if (is_array($decoded->contentDefinition)) {
                $type->setContentDefinition(FHIRContractContentDefinition::jsonUnserialize(reset($decoded->contentDefinition), $config));
            } else {
                $type->setContentDefinition(FHIRContractContentDefinition::jsonUnserialize($decoded->contentDefinition, $config));
            }
        }
        if (isset($decoded->term) || property_exists($decoded, self::FIELD_TERM)) {
            if (is_object($decoded->term)) {
                $vals = [$decoded->term];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TERM, true);
            } else {
                $vals = $decoded->term;
            }
            foreach($vals as $v) {
                $type->addTerm(FHIRContractTerm::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->supportingInfo) || property_exists($decoded, self::FIELD_SUPPORTING_INFO)) {
            if (is_object($decoded->supportingInfo)) {
                $vals = [$decoded->supportingInfo];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFO, true);
            } else {
                $vals = $decoded->supportingInfo;
            }
            foreach($vals as $v) {
                $type->addSupportingInfo(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->relevantHistory) || property_exists($decoded, self::FIELD_RELEVANT_HISTORY)) {
            if (is_object($decoded->relevantHistory)) {
                $vals = [$decoded->relevantHistory];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RELEVANT_HISTORY, true);
            } else {
                $vals = $decoded->relevantHistory;
            }
            foreach($vals as $v) {
                $type->addRelevantHistory(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->signer) || property_exists($decoded, self::FIELD_SIGNER)) {
            if (is_object($decoded->signer)) {
                $vals = [$decoded->signer];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SIGNER, true);
            } else {
                $vals = $decoded->signer;
            }
            foreach($vals as $v) {
                $type->addSigner(FHIRContractSigner::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->friendly) || property_exists($decoded, self::FIELD_FRIENDLY)) {
            if (is_object($decoded->friendly)) {
                $vals = [$decoded->friendly];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_FRIENDLY, true);
            } else {
                $vals = $decoded->friendly;
            }
            foreach($vals as $v) {
                $type->addFriendly(FHIRContractFriendly::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->legal) || property_exists($decoded, self::FIELD_LEGAL)) {
            if (is_object($decoded->legal)) {
                $vals = [$decoded->legal];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_LEGAL, true);
            } else {
                $vals = $decoded->legal;
            }
            foreach($vals as $v) {
                $type->addLegal(FHIRContractLegal::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->rule) || property_exists($decoded, self::FIELD_RULE)) {
            if (is_object($decoded->rule)) {
                $vals = [$decoded->rule];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RULE, true);
            } else {
                $vals = $decoded->rule;
            }
            foreach($vals as $v) {
                $type->addRule(FHIRContractRule::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->legallyBindingAttachment) || property_exists($decoded, self::FIELD_LEGALLY_BINDING_ATTACHMENT)) {
            if (is_array($decoded->legallyBindingAttachment)) {
                $type->setLegallyBindingAttachment(FHIRAttachment::jsonUnserialize(reset($decoded->legallyBindingAttachment), $config));
            } else {
                $type->setLegallyBindingAttachment(FHIRAttachment::jsonUnserialize($decoded->legallyBindingAttachment, $config));
            }
        }
        if (isset($decoded->legallyBindingReference) || property_exists($decoded, self::FIELD_LEGALLY_BINDING_REFERENCE)) {
            if (is_array($decoded->legallyBindingReference)) {
                $type->setLegallyBindingReference(FHIRReference::jsonUnserialize(reset($decoded->legallyBindingReference), $config));
            } else {
                $type->setLegallyBindingReference(FHIRReference::jsonUnserialize($decoded->legallyBindingReference, $config));
            }
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->identifier) && [] !== $this->identifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER) && 1 === count($this->identifier)) {
                $out->identifier = $this->identifier[0];
            } else {
                $out->identifier = $this->identifier;
            }
        }
        if (isset($this->url)) {
            if (null !== ($val = $this->url->getValue())) {
                $out->url = $val;
            }
            if ($this->url->_nonValueFieldDefined()) {
                $ext = $this->url->jsonSerialize();
                unset($ext->value);
                $out->_url = $ext;
            }
        }
        if (isset($this->version)) {
            if (null !== ($val = $this->version->getValue())) {
                $out->version = $val;
            }
            if ($this->version->_nonValueFieldDefined()) {
                $ext = $this->version->jsonSerialize();
                unset($ext->value);
                $out->_version = $ext;
            }
        }
        if (isset($this->status)) {
            if (null !== ($val = $this->status->getValue())) {
                $out->status = $val;
            }
            if ($this->status->_nonValueFieldDefined()) {
                $ext = $this->status->jsonSerialize();
                unset($ext->value);
                $out->_status = $ext;
            }
        }
        if (isset($this->legalState)) {
            $out->legalState = $this->legalState;
        }
        if (isset($this->instantiatesCanonical)) {
            $out->instantiatesCanonical = $this->instantiatesCanonical;
        }
        if (isset($this->instantiatesUri)) {
            if (null !== ($val = $this->instantiatesUri->getValue())) {
                $out->instantiatesUri = $val;
            }
            if ($this->instantiatesUri->_nonValueFieldDefined()) {
                $ext = $this->instantiatesUri->jsonSerialize();
                unset($ext->value);
                $out->_instantiatesUri = $ext;
            }
        }
        if (isset($this->contentDerivative)) {
            $out->contentDerivative = $this->contentDerivative;
        }
        if (isset($this->issued)) {
            if (null !== ($val = $this->issued->getValue())) {
                $out->issued = $val;
            }
            if ($this->issued->_nonValueFieldDefined()) {
                $ext = $this->issued->jsonSerialize();
                unset($ext->value);
                $out->_issued = $ext;
            }
        }
        if (isset($this->applies)) {
            $out->applies = $this->applies;
        }
        if (isset($this->expirationType)) {
            $out->expirationType = $this->expirationType;
        }
        if (isset($this->subject) && [] !== $this->subject) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUBJECT) && 1 === count($this->subject)) {
                $out->subject = $this->subject[0];
            } else {
                $out->subject = $this->subject;
            }
        }
        if (isset($this->authority) && [] !== $this->authority) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_AUTHORITY) && 1 === count($this->authority)) {
                $out->authority = $this->authority[0];
            } else {
                $out->authority = $this->authority;
            }
        }
        if (isset($this->domain) && [] !== $this->domain) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DOMAIN) && 1 === count($this->domain)) {
                $out->domain = $this->domain[0];
            } else {
                $out->domain = $this->domain;
            }
        }
        if (isset($this->site) && [] !== $this->site) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SITE) && 1 === count($this->site)) {
                $out->site = $this->site[0];
            } else {
                $out->site = $this->site;
            }
        }
        if (isset($this->name)) {
            if (null !== ($val = $this->name->getValue())) {
                $out->name = $val;
            }
            if ($this->name->_nonValueFieldDefined()) {
                $ext = $this->name->jsonSerialize();
                unset($ext->value);
                $out->_name = $ext;
            }
        }
        if (isset($this->title)) {
            if (null !== ($val = $this->title->getValue())) {
                $out->title = $val;
            }
            if ($this->title->_nonValueFieldDefined()) {
                $ext = $this->title->jsonSerialize();
                unset($ext->value);
                $out->_title = $ext;
            }
        }
        if (isset($this->subtitle)) {
            if (null !== ($val = $this->subtitle->getValue())) {
                $out->subtitle = $val;
            }
            if ($this->subtitle->_nonValueFieldDefined()) {
                $ext = $this->subtitle->jsonSerialize();
                unset($ext->value);
                $out->_subtitle = $ext;
            }
        }
        if (isset($this->alias) && [] !== $this->alias) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->alias as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->alias = $vals;
            }
            if ($hasExts) {
                $out->_alias = $exts;
            }
        }
        if (isset($this->author)) {
            $out->author = $this->author;
        }
        if (isset($this->scope)) {
            $out->scope = $this->scope;
        }
        if (isset($this->topicCodeableConcept)) {
            $out->topicCodeableConcept = $this->topicCodeableConcept;
        }
        if (isset($this->topicReference)) {
            $out->topicReference = $this->topicReference;
        }
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->subType) && [] !== $this->subType) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUB_TYPE) && 1 === count($this->subType)) {
                $out->subType = $this->subType[0];
            } else {
                $out->subType = $this->subType;
            }
        }
        if (isset($this->contentDefinition)) {
            $out->contentDefinition = $this->contentDefinition;
        }
        if (isset($this->term) && [] !== $this->term) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TERM) && 1 === count($this->term)) {
                $out->term = $this->term[0];
            } else {
                $out->term = $this->term;
            }
        }
        if (isset($this->supportingInfo) && [] !== $this->supportingInfo) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFO) && 1 === count($this->supportingInfo)) {
                $out->supportingInfo = $this->supportingInfo[0];
            } else {
                $out->supportingInfo = $this->supportingInfo;
            }
        }
        if (isset($this->relevantHistory) && [] !== $this->relevantHistory) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELEVANT_HISTORY) && 1 === count($this->relevantHistory)) {
                $out->relevantHistory = $this->relevantHistory[0];
            } else {
                $out->relevantHistory = $this->relevantHistory;
            }
        }
        if (isset($this->signer) && [] !== $this->signer) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SIGNER) && 1 === count($this->signer)) {
                $out->signer = $this->signer[0];
            } else {
                $out->signer = $this->signer;
            }
        }
        if (isset($this->friendly) && [] !== $this->friendly) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_FRIENDLY) && 1 === count($this->friendly)) {
                $out->friendly = $this->friendly[0];
            } else {
                $out->friendly = $this->friendly;
            }
        }
        if (isset($this->legal) && [] !== $this->legal) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_LEGAL) && 1 === count($this->legal)) {
                $out->legal = $this->legal[0];
            } else {
                $out->legal = $this->legal;
            }
        }
        if (isset($this->rule) && [] !== $this->rule) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RULE) && 1 === count($this->rule)) {
                $out->rule = $this->rule[0];
            } else {
                $out->rule = $this->rule;
            }
        }
        if (isset($this->legallyBindingAttachment)) {
            $out->legallyBindingAttachment = $this->legallyBindingAttachment;
        }
        if (isset($this->legallyBindingReference)) {
            $out->legallyBindingReference = $this->legallyBindingReference;
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
