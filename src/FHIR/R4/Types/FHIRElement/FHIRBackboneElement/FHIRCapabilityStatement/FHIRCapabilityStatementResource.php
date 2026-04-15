<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement;

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
use OpenEMR\FHIR\Types\ElementTypeInterface;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRConditionalDeleteStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRConditionalReadStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRReferenceHandlingPolicyList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRResourceVersionPolicyList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalDeleteStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalReadStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReferenceHandlingPolicy;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResourceVersionPolicy;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
 * Server for a particular version of FHIR that may be used as a statement of
 * actual server functionality or a statement of required or desired server
 * implementation.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCapabilityStatementResource extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CAPABILITY_STATEMENT_DOT_RESOURCE;

    /* class_default.php:56 */
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_EXT = '_type';
    public const FIELD_PROFILE = 'profile';
    public const FIELD_PROFILE_EXT = '_profile';
    public const FIELD_SUPPORTED_PROFILE = 'supportedProfile';
    public const FIELD_SUPPORTED_PROFILE_EXT = '_supportedProfile';
    public const FIELD_DOCUMENTATION = 'documentation';
    public const FIELD_DOCUMENTATION_EXT = '_documentation';
    public const FIELD_INTERACTION = 'interaction';
    public const FIELD_VERSIONING = 'versioning';
    public const FIELD_VERSIONING_EXT = '_versioning';
    public const FIELD_READ_HISTORY = 'readHistory';
    public const FIELD_READ_HISTORY_EXT = '_readHistory';
    public const FIELD_UPDATE_CREATE = 'updateCreate';
    public const FIELD_UPDATE_CREATE_EXT = '_updateCreate';
    public const FIELD_CONDITIONAL_CREATE = 'conditionalCreate';
    public const FIELD_CONDITIONAL_CREATE_EXT = '_conditionalCreate';
    public const FIELD_CONDITIONAL_READ = 'conditionalRead';
    public const FIELD_CONDITIONAL_READ_EXT = '_conditionalRead';
    public const FIELD_CONDITIONAL_UPDATE = 'conditionalUpdate';
    public const FIELD_CONDITIONAL_UPDATE_EXT = '_conditionalUpdate';
    public const FIELD_CONDITIONAL_DELETE = 'conditionalDelete';
    public const FIELD_CONDITIONAL_DELETE_EXT = '_conditionalDelete';
    public const FIELD_REFERENCE_POLICY = 'referencePolicy';
    public const FIELD_REFERENCE_POLICY_EXT = '_referencePolicy';
    public const FIELD_SEARCH_INCLUDE = 'searchInclude';
    public const FIELD_SEARCH_INCLUDE_EXT = '_searchInclude';
    public const FIELD_SEARCH_REV_INCLUDE = 'searchRevInclude';
    public const FIELD_SEARCH_REV_INCLUDE_EXT = '_searchRevInclude';
    public const FIELD_SEARCH_PARAM = 'searchParam';
    public const FIELD_OPERATION = 'operation';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_TYPE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PROFILE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DOCUMENTATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VERSIONING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_READ_HISTORY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_UPDATE_CREATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CONDITIONAL_CREATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CONDITIONAL_READ => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CONDITIONAL_UPDATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CONDITIONAL_DELETE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A type of resource exposed via the restful interface.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $type;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A specification of the profile that describes the solution's overall support for
     * the resource, including any constraints on cardinality, bindings, lengths or
     * other limitations. See further discussion in [Using
     * Profiles](profiling.html#profile-uses).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $profile;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A list of profiles that represent different use cases supported by the system.
     * For a server, "supported by the system" means the system hosts/produces a set of
     * resources that are conformant to a particular profile, and allows clients that
     * use its services to search using this profile and to find appropriate data. For
     * a client, it means the system will search by this profile and process data
     * according to the guidance implicit in the profile. See further discussion in
     * [Using Profiles](profiling.html#profile-uses).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    #[FHIRCanonical]
    protected array $supportedProfile;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Additional information about the resource type used by the system.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $documentation;
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Identifies a restful operation supported by the solution.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction>
     */
    #[FHIRCapabilityStatementInteraction]
    protected array $interaction;
    /**
     * How the system supports versioning for a resource.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This field is set to no-version to specify that the system does not support
     * (server) or use (client) versioning for this resource type. If this has some
     * other value, the server must at least correctly track and populate the versionId
     * meta-property on resources. If the value is 'versioned-update', then the server
     * supports all the versioning features, including using e-tags for version
     * integrity in the API.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResourceVersionPolicy
     */
    #[FHIRResourceVersionPolicy]
    protected FHIRResourceVersionPolicy $versioning;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag for whether the server is able to return past versions as part of the
     * vRead operation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $readHistory;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag to indicate that the server allows or needs to allow the client to create
     * new identities on the server (that is, the client PUTs to a location where there
     * is no existing resource). Allowing this operation means that the server allows
     * the client to create new identities on the server.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $updateCreate;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag that indicates that the server supports conditional create.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $conditionalCreate;
    /**
     * A code that indicates how the server supports conditional read.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code that indicates how the server supports conditional read.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalReadStatus
     */
    #[FHIRConditionalReadStatus]
    protected FHIRConditionalReadStatus $conditionalRead;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag that indicates that the server supports conditional update.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $conditionalUpdate;
    /**
     * A code that indicates how the server supports conditional delete.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code that indicates how the server supports conditional delete.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalDeleteStatus
     */
    #[FHIRConditionalDeleteStatus]
    protected FHIRConditionalDeleteStatus $conditionalDelete;
    /**
     * A set of flags that defines how references are supported.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A set of flags that defines how references are supported.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReferenceHandlingPolicy>
     */
    #[FHIRReferenceHandlingPolicy]
    protected array $referencePolicy;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of _include values supported by the server.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $searchInclude;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of _revinclude (reverse include) values supported by the server.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $searchRevInclude;
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Search parameters for implementations to support and/or make use of - either
     * references to ones defined in the specification, or additional ones defined
     * for/by the implementation.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam>
     */
    #[FHIRCapabilityStatementSearchParam]
    protected array $searchParam;
    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Definition of an operation or a named query together with its parameters and
     * their meaning and type. Consult the definition of the operation for details
     * about how to invoke the operation, and the parameters.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation>
     */
    #[FHIRCapabilityStatementOperation]
    protected array $operation;

    /* constructor.php:61 */
    /**
     * FHIRCapabilityStatementResource Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $type
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $profile
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $supportedProfile
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $documentation
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction> $interaction
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRResourceVersionPolicyList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResourceVersionPolicy $versioning
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $readHistory
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $updateCreate
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $conditionalCreate
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRConditionalReadStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalReadStatus $conditionalRead
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $conditionalUpdate
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRConditionalDeleteStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalDeleteStatus $conditionalDelete
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRReferenceHandlingPolicyList>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReferenceHandlingPolicy> $referencePolicy
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $searchInclude
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $searchRevInclude
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam> $searchParam
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation> $operation
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRCodePrimitive|FHIRCode $type = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $profile = null,
                                null|iterable $supportedProfile = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $documentation = null,
                                null|iterable $interaction = null,
                                null|string|FHIRResourceVersionPolicyList|FHIRResourceVersionPolicy $versioning = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $readHistory = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $updateCreate = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $conditionalCreate = null,
                                null|string|FHIRConditionalReadStatusList|FHIRConditionalReadStatus $conditionalRead = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $conditionalUpdate = null,
                                null|string|FHIRConditionalDeleteStatusList|FHIRConditionalDeleteStatus $conditionalDelete = null,
                                null|iterable $referencePolicy = null,
                                null|iterable $searchInclude = null,
                                null|iterable $searchRevInclude = null,
                                null|iterable $searchParam = null,
                                null|iterable $operation = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $profile) {
            $this->setProfile($profile);
        }
        if (null !== $supportedProfile) {
            $this->setSupportedProfile(...$supportedProfile);
        }
        if (null !== $documentation) {
            $this->setDocumentation($documentation);
        }
        if (null !== $interaction) {
            $this->setInteraction(...$interaction);
        }
        if (null !== $versioning) {
            $this->setVersioning($versioning);
        }
        if (null !== $readHistory) {
            $this->setReadHistory($readHistory);
        }
        if (null !== $updateCreate) {
            $this->setUpdateCreate($updateCreate);
        }
        if (null !== $conditionalCreate) {
            $this->setConditionalCreate($conditionalCreate);
        }
        if (null !== $conditionalRead) {
            $this->setConditionalRead($conditionalRead);
        }
        if (null !== $conditionalUpdate) {
            $this->setConditionalUpdate($conditionalUpdate);
        }
        if (null !== $conditionalDelete) {
            $this->setConditionalDelete($conditionalDelete);
        }
        if (null !== $referencePolicy) {
            $this->setReferencePolicy(...$referencePolicy);
        }
        if (null !== $searchInclude) {
            $this->setSearchInclude(...$searchInclude);
        }
        if (null !== $searchRevInclude) {
            $this->setSearchRevInclude(...$searchRevInclude);
        }
        if (null !== $searchParam) {
            $this->setSearchParam(...$searchParam);
        }
        if (null !== $operation) {
            $this->setOperation(...$operation);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A type of resource exposed via the restful interface.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getType(): null|FHIRCode
    {
        return $this->type ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A type of resource exposed via the restful interface.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $type
     * @return static
     */
    public function setType(null|string|FHIRCodePrimitive|FHIRCode $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        if (!($type instanceof FHIRCode)) {
            $type = new FHIRCode(value: $type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A specification of the profile that describes the solution's overall support for
     * the resource, including any constraints on cardinality, bindings, lengths or
     * other limitations. See further discussion in [Using
     * Profiles](profiling.html#profile-uses).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getProfile(): null|FHIRCanonical
    {
        return $this->profile ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A specification of the profile that describes the solution's overall support for
     * the resource, including any constraints on cardinality, bindings, lengths or
     * other limitations. See further discussion in [Using
     * Profiles](profiling.html#profile-uses).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $profile
     * @return static
     */
    public function setProfile(null|string|FHIRCanonicalPrimitive|FHIRCanonical $profile): self
    {
        if (null === $profile) {
            unset($this->profile);
            return $this;
        }
        if (!($profile instanceof FHIRCanonical)) {
            $profile = new FHIRCanonical(value: $profile);
        }
        $this->profile = $profile;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A list of profiles that represent different use cases supported by the system.
     * For a server, "supported by the system" means the system hosts/produces a set of
     * resources that are conformant to a particular profile, and allows clients that
     * use its services to search using this profile and to find appropriate data. For
     * a client, it means the system will search by this profile and process data
     * according to the guidance implicit in the profile. See further discussion in
     * [Using Profiles](profiling.html#profile-uses).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getSupportedProfile(): array
    {
        return $this->supportedProfile ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getSupportedProfileIterator(): iterable
    {
        if (!isset($this->supportedProfile)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->supportedProfile);
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A list of profiles that represent different use cases supported by the system.
     * For a server, "supported by the system" means the system hosts/produces a set of
     * resources that are conformant to a particular profile, and allows clients that
     * use its services to search using this profile and to find appropriate data. For
     * a client, it means the system will search by this profile and process data
     * according to the guidance implicit in the profile. See further discussion in
     * [Using Profiles](profiling.html#profile-uses).
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $supportedProfile
     * @return static
     */
    public function addSupportedProfile(string|FHIRCanonicalPrimitive|FHIRCanonical $supportedProfile): self
    {
        if (!($supportedProfile instanceof FHIRCanonical)) {
            $supportedProfile = new FHIRCanonical(value: $supportedProfile);
        }
        if (!isset($this->supportedProfile)) {
            $this->supportedProfile = [];
        }
        $this->supportedProfile[] = $supportedProfile;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A list of profiles that represent different use cases supported by the system.
     * For a server, "supported by the system" means the system hosts/produces a set of
     * resources that are conformant to a particular profile, and allows clients that
     * use its services to search using this profile and to find appropriate data. For
     * a client, it means the system will search by this profile and process data
     * according to the guidance implicit in the profile. See further discussion in
     * [Using Profiles](profiling.html#profile-uses).
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical ...$supportedProfile
     * @return static
     */
    public function setSupportedProfile(string|FHIRCanonicalPrimitive|FHIRCanonical ...$supportedProfile): self
    {
        if ([] === $supportedProfile) {
            unset($this->supportedProfile);
            return $this;
        }
        $this->supportedProfile = [];
        foreach($supportedProfile as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->supportedProfile[] = $v;
            } else {
                $this->supportedProfile[] = new FHIRCanonical(value: $v);
            }
        }
        return $this;
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Additional information about the resource type used by the system.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getDocumentation(): null|FHIRMarkdown
    {
        return $this->documentation ?? null;
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Additional information about the resource type used by the system.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $documentation
     * @return static
     */
    public function setDocumentation(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $documentation): self
    {
        if (null === $documentation) {
            unset($this->documentation);
            return $this;
        }
        if (!($documentation instanceof FHIRMarkdown)) {
            $documentation = new FHIRMarkdown(value: $documentation);
        }
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Identifies a restful operation supported by the solution.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction>
     */
    public function getInteraction(): array
    {
        return $this->interaction ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction>
     */
    public function getInteractionIterator(): iterable
    {
        if (!isset($this->interaction)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->interaction);
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Identifies a restful operation supported by the solution.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction $interaction
     * @return static
     */
    public function addInteraction(FHIRCapabilityStatementInteraction $interaction): self
    {
        if (!isset($this->interaction)) {
            $this->interaction = [];
        }
        $this->interaction[] = $interaction;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Identifies a restful operation supported by the solution.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction ...$interaction
     * @return static
     */
    public function setInteraction(FHIRCapabilityStatementInteraction ...$interaction): self
    {
        if ([] === $interaction) {
            unset($this->interaction);
            return $this;
        }
        $this->interaction = $interaction;
        return $this;
    }

    /**
     * How the system supports versioning for a resource.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This field is set to no-version to specify that the system does not support
     * (server) or use (client) versioning for this resource type. If this has some
     * other value, the server must at least correctly track and populate the versionId
     * meta-property on resources. If the value is 'versioned-update', then the server
     * supports all the versioning features, including using e-tags for version
     * integrity in the API.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResourceVersionPolicy
     */
    public function getVersioning(): null|FHIRResourceVersionPolicy
    {
        return $this->versioning ?? null;
    }

    /**
     * How the system supports versioning for a resource.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This field is set to no-version to specify that the system does not support
     * (server) or use (client) versioning for this resource type. If this has some
     * other value, the server must at least correctly track and populate the versionId
     * meta-property on resources. If the value is 'versioned-update', then the server
     * supports all the versioning features, including using e-tags for version
     * integrity in the API.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRResourceVersionPolicyList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResourceVersionPolicy $versioning
     * @return static
     */
    public function setVersioning(null|string|FHIRResourceVersionPolicyList|FHIRResourceVersionPolicy $versioning): self
    {
        if (null === $versioning) {
            unset($this->versioning);
            return $this;
        }
        if (!($versioning instanceof FHIRResourceVersionPolicy)) {
            $versioning = new FHIRResourceVersionPolicy(value: $versioning);
        }
        $this->versioning = $versioning;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag for whether the server is able to return past versions as part of the
     * vRead operation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getReadHistory(): null|FHIRBoolean
    {
        return $this->readHistory ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag for whether the server is able to return past versions as part of the
     * vRead operation.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $readHistory
     * @return static
     */
    public function setReadHistory(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $readHistory): self
    {
        if (null === $readHistory) {
            unset($this->readHistory);
            return $this;
        }
        if (!($readHistory instanceof FHIRBoolean)) {
            $readHistory = new FHIRBoolean(value: $readHistory);
        }
        $this->readHistory = $readHistory;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag to indicate that the server allows or needs to allow the client to create
     * new identities on the server (that is, the client PUTs to a location where there
     * is no existing resource). Allowing this operation means that the server allows
     * the client to create new identities on the server.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getUpdateCreate(): null|FHIRBoolean
    {
        return $this->updateCreate ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag to indicate that the server allows or needs to allow the client to create
     * new identities on the server (that is, the client PUTs to a location where there
     * is no existing resource). Allowing this operation means that the server allows
     * the client to create new identities on the server.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $updateCreate
     * @return static
     */
    public function setUpdateCreate(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $updateCreate): self
    {
        if (null === $updateCreate) {
            unset($this->updateCreate);
            return $this;
        }
        if (!($updateCreate instanceof FHIRBoolean)) {
            $updateCreate = new FHIRBoolean(value: $updateCreate);
        }
        $this->updateCreate = $updateCreate;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag that indicates that the server supports conditional create.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getConditionalCreate(): null|FHIRBoolean
    {
        return $this->conditionalCreate ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag that indicates that the server supports conditional create.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $conditionalCreate
     * @return static
     */
    public function setConditionalCreate(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $conditionalCreate): self
    {
        if (null === $conditionalCreate) {
            unset($this->conditionalCreate);
            return $this;
        }
        if (!($conditionalCreate instanceof FHIRBoolean)) {
            $conditionalCreate = new FHIRBoolean(value: $conditionalCreate);
        }
        $this->conditionalCreate = $conditionalCreate;
        return $this;
    }

    /**
     * A code that indicates how the server supports conditional read.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code that indicates how the server supports conditional read.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalReadStatus
     */
    public function getConditionalRead(): null|FHIRConditionalReadStatus
    {
        return $this->conditionalRead ?? null;
    }

    /**
     * A code that indicates how the server supports conditional read.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code that indicates how the server supports conditional read.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRConditionalReadStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalReadStatus $conditionalRead
     * @return static
     */
    public function setConditionalRead(null|string|FHIRConditionalReadStatusList|FHIRConditionalReadStatus $conditionalRead): self
    {
        if (null === $conditionalRead) {
            unset($this->conditionalRead);
            return $this;
        }
        if (!($conditionalRead instanceof FHIRConditionalReadStatus)) {
            $conditionalRead = new FHIRConditionalReadStatus(value: $conditionalRead);
        }
        $this->conditionalRead = $conditionalRead;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag that indicates that the server supports conditional update.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getConditionalUpdate(): null|FHIRBoolean
    {
        return $this->conditionalUpdate ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag that indicates that the server supports conditional update.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $conditionalUpdate
     * @return static
     */
    public function setConditionalUpdate(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $conditionalUpdate): self
    {
        if (null === $conditionalUpdate) {
            unset($this->conditionalUpdate);
            return $this;
        }
        if (!($conditionalUpdate instanceof FHIRBoolean)) {
            $conditionalUpdate = new FHIRBoolean(value: $conditionalUpdate);
        }
        $this->conditionalUpdate = $conditionalUpdate;
        return $this;
    }

    /**
     * A code that indicates how the server supports conditional delete.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code that indicates how the server supports conditional delete.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalDeleteStatus
     */
    public function getConditionalDelete(): null|FHIRConditionalDeleteStatus
    {
        return $this->conditionalDelete ?? null;
    }

    /**
     * A code that indicates how the server supports conditional delete.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code that indicates how the server supports conditional delete.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRConditionalDeleteStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConditionalDeleteStatus $conditionalDelete
     * @return static
     */
    public function setConditionalDelete(null|string|FHIRConditionalDeleteStatusList|FHIRConditionalDeleteStatus $conditionalDelete): self
    {
        if (null === $conditionalDelete) {
            unset($this->conditionalDelete);
            return $this;
        }
        if (!($conditionalDelete instanceof FHIRConditionalDeleteStatus)) {
            $conditionalDelete = new FHIRConditionalDeleteStatus(value: $conditionalDelete);
        }
        $this->conditionalDelete = $conditionalDelete;
        return $this;
    }

    /**
     * A set of flags that defines how references are supported.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A set of flags that defines how references are supported.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReferenceHandlingPolicy>
     */
    public function getReferencePolicy(): array
    {
        return $this->referencePolicy ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReferenceHandlingPolicy>
     */
    public function getReferencePolicyIterator(): iterable
    {
        if (!isset($this->referencePolicy)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->referencePolicy);
    }

    /**
     * A set of flags that defines how references are supported.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A set of flags that defines how references are supported.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRReferenceHandlingPolicyList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReferenceHandlingPolicy $referencePolicy
     * @return static
     */
    public function addReferencePolicy(string|FHIRReferenceHandlingPolicyList|FHIRReferenceHandlingPolicy $referencePolicy): self
    {
        if (!($referencePolicy instanceof FHIRReferenceHandlingPolicy)) {
            $referencePolicy = new FHIRReferenceHandlingPolicy(value: $referencePolicy);
        }
        if (!isset($this->referencePolicy)) {
            $this->referencePolicy = [];
        }
        $this->referencePolicy[] = $referencePolicy;
        return $this;
    }

    /**
     * A set of flags that defines how references are supported.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A set of flags that defines how references are supported.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRReferenceHandlingPolicyList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReferenceHandlingPolicy ...$referencePolicy
     * @return static
     */
    public function setReferencePolicy(string|FHIRReferenceHandlingPolicyList|FHIRReferenceHandlingPolicy ...$referencePolicy): self
    {
        if ([] === $referencePolicy) {
            unset($this->referencePolicy);
            return $this;
        }
        $this->referencePolicy = [];
        foreach($referencePolicy as $v) {
            if ($v instanceof FHIRReferenceHandlingPolicy) {
                $this->referencePolicy[] = $v;
            } else {
                $this->referencePolicy[] = new FHIRReferenceHandlingPolicy(value: $v);
            }
        }
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of _include values supported by the server.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getSearchInclude(): array
    {
        return $this->searchInclude ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getSearchIncludeIterator(): iterable
    {
        if (!isset($this->searchInclude)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->searchInclude);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of _include values supported by the server.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $searchInclude
     * @return static
     */
    public function addSearchInclude(string|FHIRStringPrimitive|FHIRString $searchInclude): self
    {
        if (!($searchInclude instanceof FHIRString)) {
            $searchInclude = new FHIRString(value: $searchInclude);
        }
        if (!isset($this->searchInclude)) {
            $this->searchInclude = [];
        }
        $this->searchInclude[] = $searchInclude;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of _include values supported by the server.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$searchInclude
     * @return static
     */
    public function setSearchInclude(string|FHIRStringPrimitive|FHIRString ...$searchInclude): self
    {
        if ([] === $searchInclude) {
            unset($this->searchInclude);
            return $this;
        }
        $this->searchInclude = [];
        foreach($searchInclude as $v) {
            if ($v instanceof FHIRString) {
                $this->searchInclude[] = $v;
            } else {
                $this->searchInclude[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of _revinclude (reverse include) values supported by the server.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getSearchRevInclude(): array
    {
        return $this->searchRevInclude ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getSearchRevIncludeIterator(): iterable
    {
        if (!isset($this->searchRevInclude)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->searchRevInclude);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of _revinclude (reverse include) values supported by the server.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $searchRevInclude
     * @return static
     */
    public function addSearchRevInclude(string|FHIRStringPrimitive|FHIRString $searchRevInclude): self
    {
        if (!($searchRevInclude instanceof FHIRString)) {
            $searchRevInclude = new FHIRString(value: $searchRevInclude);
        }
        if (!isset($this->searchRevInclude)) {
            $this->searchRevInclude = [];
        }
        $this->searchRevInclude[] = $searchRevInclude;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of _revinclude (reverse include) values supported by the server.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$searchRevInclude
     * @return static
     */
    public function setSearchRevInclude(string|FHIRStringPrimitive|FHIRString ...$searchRevInclude): self
    {
        if ([] === $searchRevInclude) {
            unset($this->searchRevInclude);
            return $this;
        }
        $this->searchRevInclude = [];
        foreach($searchRevInclude as $v) {
            if ($v instanceof FHIRString) {
                $this->searchRevInclude[] = $v;
            } else {
                $this->searchRevInclude[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Search parameters for implementations to support and/or make use of - either
     * references to ones defined in the specification, or additional ones defined
     * for/by the implementation.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam>
     */
    public function getSearchParam(): array
    {
        return $this->searchParam ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam>
     */
    public function getSearchParamIterator(): iterable
    {
        if (!isset($this->searchParam)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->searchParam);
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Search parameters for implementations to support and/or make use of - either
     * references to ones defined in the specification, or additional ones defined
     * for/by the implementation.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam $searchParam
     * @return static
     */
    public function addSearchParam(FHIRCapabilityStatementSearchParam $searchParam): self
    {
        if (!isset($this->searchParam)) {
            $this->searchParam = [];
        }
        $this->searchParam[] = $searchParam;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Search parameters for implementations to support and/or make use of - either
     * references to ones defined in the specification, or additional ones defined
     * for/by the implementation.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam ...$searchParam
     * @return static
     */
    public function setSearchParam(FHIRCapabilityStatementSearchParam ...$searchParam): self
    {
        if ([] === $searchParam) {
            unset($this->searchParam);
            return $this;
        }
        $this->searchParam = $searchParam;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Definition of an operation or a named query together with its parameters and
     * their meaning and type. Consult the definition of the operation for details
     * about how to invoke the operation, and the parameters.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation>
     */
    public function getOperation(): array
    {
        return $this->operation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation>
     */
    public function getOperationIterator(): iterable
    {
        if (!isset($this->operation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->operation);
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Definition of an operation or a named query together with its parameters and
     * their meaning and type. Consult the definition of the operation for details
     * about how to invoke the operation, and the parameters.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation $operation
     * @return static
     */
    public function addOperation(FHIRCapabilityStatementOperation $operation): self
    {
        if (!isset($this->operation)) {
            $this->operation = [];
        }
        $this->operation[] = $operation;
        return $this;
    }

    /**
     * A Capability Statement documents a set of capabilities (behaviors) of a FHIR
     * Server for a particular version of FHIR that may be used as a statement of
     * actual server functionality or a statement of required or desired server
     * implementation.
     *
     * Definition of an operation or a named query together with its parameters and
     * their meaning and type. Consult the definition of the operation for details
     * about how to invoke the operation, and the parameters.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementOperation ...$operation
     * @return static
     */
    public function setOperation(FHIRCapabilityStatementOperation ...$operation): self
    {
        if ([] === $operation) {
            unset($this->operation);
            return $this;
        }
        $this->operation = $operation;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCapabilityStatementResource)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ID === $cen) {
                $va = $ce->attributes()[FHIRStringPrimitive::FIELD_VALUE] ?? null;
                if (null !== $va) {
                    $type->setId((string)$va);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_ATTRIBUTE);
                } else {
                    $type->setId((string)$ce);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_VALUE);
                }
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROFILE === $cen) {
                $type->setProfile(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUPPORTED_PROFILE === $cen) {
                $type->addSupportedProfile(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOCUMENTATION === $cen) {
                $type->setDocumentation(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INTERACTION === $cen) {
                $type->addInteraction(FHIRCapabilityStatementInteraction::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VERSIONING === $cen) {
                $type->setVersioning(FHIRResourceVersionPolicy::xmlUnserialize($ce, $config));
            } else if (self::FIELD_READ_HISTORY === $cen) {
                $type->setReadHistory(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_UPDATE_CREATE === $cen) {
                $type->setUpdateCreate(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONDITIONAL_CREATE === $cen) {
                $type->setConditionalCreate(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONDITIONAL_READ === $cen) {
                $type->setConditionalRead(FHIRConditionalReadStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONDITIONAL_UPDATE === $cen) {
                $type->setConditionalUpdate(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONDITIONAL_DELETE === $cen) {
                $type->setConditionalDelete(FHIRConditionalDeleteStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE_POLICY === $cen) {
                $type->addReferencePolicy(FHIRReferenceHandlingPolicy::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEARCH_INCLUDE === $cen) {
                $type->addSearchInclude(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEARCH_REV_INCLUDE === $cen) {
                $type->addSearchRevInclude(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEARCH_PARAM === $cen) {
                $type->addSearchParam(FHIRCapabilityStatementSearchParam::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OPERATION === $cen) {
                $type->addOperation(FHIRCapabilityStatementOperation::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TYPE])) {
            if (isset($type->type)) {
                $type->type->setValue((string)$attributes[self::FIELD_TYPE]);
            } else {
                $type->setType((string)$attributes[self::FIELD_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PROFILE])) {
            if (isset($type->profile)) {
                $type->profile->setValue((string)$attributes[self::FIELD_PROFILE]);
            } else {
                $type->setProfile((string)$attributes[self::FIELD_PROFILE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PROFILE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DOCUMENTATION])) {
            if (isset($type->documentation)) {
                $type->documentation->setValue((string)$attributes[self::FIELD_DOCUMENTATION]);
            } else {
                $type->setDocumentation((string)$attributes[self::FIELD_DOCUMENTATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DOCUMENTATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VERSIONING])) {
            if (isset($type->versioning)) {
                $type->versioning->setValue((string)$attributes[self::FIELD_VERSIONING]);
            } else {
                $type->setVersioning((string)$attributes[self::FIELD_VERSIONING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VERSIONING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_READ_HISTORY])) {
            if (isset($type->readHistory)) {
                $type->readHistory->setValue((string)$attributes[self::FIELD_READ_HISTORY]);
            } else {
                $type->setReadHistory((string)$attributes[self::FIELD_READ_HISTORY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_READ_HISTORY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_UPDATE_CREATE])) {
            if (isset($type->updateCreate)) {
                $type->updateCreate->setValue((string)$attributes[self::FIELD_UPDATE_CREATE]);
            } else {
                $type->setUpdateCreate((string)$attributes[self::FIELD_UPDATE_CREATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_UPDATE_CREATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONDITIONAL_CREATE])) {
            if (isset($type->conditionalCreate)) {
                $type->conditionalCreate->setValue((string)$attributes[self::FIELD_CONDITIONAL_CREATE]);
            } else {
                $type->setConditionalCreate((string)$attributes[self::FIELD_CONDITIONAL_CREATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONDITIONAL_CREATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONDITIONAL_READ])) {
            if (isset($type->conditionalRead)) {
                $type->conditionalRead->setValue((string)$attributes[self::FIELD_CONDITIONAL_READ]);
            } else {
                $type->setConditionalRead((string)$attributes[self::FIELD_CONDITIONAL_READ]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONDITIONAL_READ, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONDITIONAL_UPDATE])) {
            if (isset($type->conditionalUpdate)) {
                $type->conditionalUpdate->setValue((string)$attributes[self::FIELD_CONDITIONAL_UPDATE]);
            } else {
                $type->setConditionalUpdate((string)$attributes[self::FIELD_CONDITIONAL_UPDATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONDITIONAL_UPDATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONDITIONAL_DELETE])) {
            if (isset($type->conditionalDelete)) {
                $type->conditionalDelete->setValue((string)$attributes[self::FIELD_CONDITIONAL_DELETE]);
            } else {
                $type->setConditionalDelete((string)$attributes[self::FIELD_CONDITIONAL_DELETE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONDITIONAL_DELETE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param \OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param \OpenEMR\FHIR\Encoding\SerializeConfig $config
     */
    public function xmlSerialize(XMLWriter $xw,
                                 SerializeConfig $config): void
    {
        if (isset($this->type) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TYPE]) {
            $xw->writeAttribute(self::FIELD_TYPE, $this->type->_getValueAsString());
        }
        if (isset($this->profile) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PROFILE]) {
            $xw->writeAttribute(self::FIELD_PROFILE, $this->profile->_getValueAsString());
        }
        if (isset($this->documentation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]) {
            $xw->writeAttribute(self::FIELD_DOCUMENTATION, $this->documentation->_getValueAsString());
        }
        if (isset($this->versioning) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VERSIONING]) {
            $xw->writeAttribute(self::FIELD_VERSIONING, $this->versioning->_getValueAsString());
        }
        if (isset($this->readHistory) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_READ_HISTORY]) {
            $xw->writeAttribute(self::FIELD_READ_HISTORY, $this->readHistory->_getValueAsString());
        }
        if (isset($this->updateCreate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_UPDATE_CREATE]) {
            $xw->writeAttribute(self::FIELD_UPDATE_CREATE, $this->updateCreate->_getValueAsString());
        }
        if (isset($this->conditionalCreate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONDITIONAL_CREATE]) {
            $xw->writeAttribute(self::FIELD_CONDITIONAL_CREATE, $this->conditionalCreate->_getValueAsString());
        }
        if (isset($this->conditionalRead) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONDITIONAL_READ]) {
            $xw->writeAttribute(self::FIELD_CONDITIONAL_READ, $this->conditionalRead->_getValueAsString());
        }
        if (isset($this->conditionalUpdate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONDITIONAL_UPDATE]) {
            $xw->writeAttribute(self::FIELD_CONDITIONAL_UPDATE, $this->conditionalUpdate->_getValueAsString());
        }
        if (isset($this->conditionalDelete) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONDITIONAL_DELETE]) {
            $xw->writeAttribute(self::FIELD_CONDITIONAL_DELETE, $this->conditionalDelete->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->type)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TYPE]
                || $this->type->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TYPE]);
            $xw->endElement();
        }
        if (isset($this->profile)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PROFILE]
                || $this->profile->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PROFILE);
            $this->profile->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PROFILE]);
            $xw->endElement();
        }
        if (isset($this->supportedProfile) && [] !== $this->supportedProfile) {
            foreach($this->supportedProfile as $v) {
                $xw->startElement(self::FIELD_SUPPORTED_PROFILE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->documentation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]
                || $this->documentation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DOCUMENTATION);
            $this->documentation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]);
            $xw->endElement();
        }
        if (isset($this->interaction)) {
            foreach ($this->interaction as $v) {
                $xw->startElement(self::FIELD_INTERACTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->versioning)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VERSIONING]
                || $this->versioning->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VERSIONING);
            $this->versioning->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VERSIONING]);
            $xw->endElement();
        }
        if (isset($this->readHistory)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_READ_HISTORY]
                || $this->readHistory->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_READ_HISTORY);
            $this->readHistory->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_READ_HISTORY]);
            $xw->endElement();
        }
        if (isset($this->updateCreate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_UPDATE_CREATE]
                || $this->updateCreate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_UPDATE_CREATE);
            $this->updateCreate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_UPDATE_CREATE]);
            $xw->endElement();
        }
        if (isset($this->conditionalCreate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONDITIONAL_CREATE]
                || $this->conditionalCreate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONDITIONAL_CREATE);
            $this->conditionalCreate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONDITIONAL_CREATE]);
            $xw->endElement();
        }
        if (isset($this->conditionalRead)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONDITIONAL_READ]
                || $this->conditionalRead->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONDITIONAL_READ);
            $this->conditionalRead->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONDITIONAL_READ]);
            $xw->endElement();
        }
        if (isset($this->conditionalUpdate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONDITIONAL_UPDATE]
                || $this->conditionalUpdate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONDITIONAL_UPDATE);
            $this->conditionalUpdate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONDITIONAL_UPDATE]);
            $xw->endElement();
        }
        if (isset($this->conditionalDelete)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONDITIONAL_DELETE]
                || $this->conditionalDelete->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONDITIONAL_DELETE);
            $this->conditionalDelete->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONDITIONAL_DELETE]);
            $xw->endElement();
        }
        if (isset($this->referencePolicy) && [] !== $this->referencePolicy) {
            foreach($this->referencePolicy as $v) {
                $xw->startElement(self::FIELD_REFERENCE_POLICY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->searchInclude) && [] !== $this->searchInclude) {
            foreach($this->searchInclude as $v) {
                $xw->startElement(self::FIELD_SEARCH_INCLUDE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->searchRevInclude) && [] !== $this->searchRevInclude) {
            foreach($this->searchRevInclude as $v) {
                $xw->startElement(self::FIELD_SEARCH_REV_INCLUDE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->searchParam)) {
            foreach ($this->searchParam as $v) {
                $xw->startElement(self::FIELD_SEARCH_PARAM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->operation)) {
            foreach ($this->operation as $v) {
                $xw->startElement(self::FIELD_OPERATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCapabilityStatement\FHIRCapabilityStatementResource
     * @throws \Exception
     */
    public static function jsonUnserialize(\stdClass $decoded,
                                           UnserializeConfig $config,
                                           null|ElementTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIRCapabilityStatementResource)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->type)
            || isset($decoded->_type)
            || property_exists($decoded, self::FIELD_TYPE)
            || property_exists($decoded, self::FIELD_TYPE_EXT)) {
            $v = $decoded->_type ?? new \stdClass();
            $v->value = $decoded->type ?? null;
            $type->setType(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->profile)
            || isset($decoded->_profile)
            || property_exists($decoded, self::FIELD_PROFILE)
            || property_exists($decoded, self::FIELD_PROFILE_EXT)) {
            $v = $decoded->_profile ?? new \stdClass();
            $v->value = $decoded->profile ?? null;
            $type->setProfile(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->supportedProfile)
            || isset($decoded->_supportedProfile)
            || property_exists($decoded, self::FIELD_SUPPORTED_PROFILE)
            || property_exists($decoded, self::FIELD_SUPPORTED_PROFILE_EXT)) {
            $vals = (array)($decoded->supportedProfile ?? []);
            $exts = (array)($decoded->_supportedProfile ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addSupportedProfile(FHIRCanonical::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->documentation)
            || isset($decoded->_documentation)
            || property_exists($decoded, self::FIELD_DOCUMENTATION)
            || property_exists($decoded, self::FIELD_DOCUMENTATION_EXT)) {
            $v = $decoded->_documentation ?? new \stdClass();
            $v->value = $decoded->documentation ?? null;
            $type->setDocumentation(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->interaction) || property_exists($decoded, self::FIELD_INTERACTION)) {
            if (is_object($decoded->interaction)) {
                $vals = [$decoded->interaction];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INTERACTION, true);
            } else {
                $vals = $decoded->interaction;
            }
            foreach($vals as $v) {
                $type->addInteraction(FHIRCapabilityStatementInteraction::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->versioning)
            || isset($decoded->_versioning)
            || property_exists($decoded, self::FIELD_VERSIONING)
            || property_exists($decoded, self::FIELD_VERSIONING_EXT)) {
            $v = $decoded->_versioning ?? new \stdClass();
            $v->value = $decoded->versioning ?? null;
            $type->setVersioning(FHIRResourceVersionPolicy::jsonUnserialize($v, $config));
        }
        if (isset($decoded->readHistory)
            || isset($decoded->_readHistory)
            || property_exists($decoded, self::FIELD_READ_HISTORY)
            || property_exists($decoded, self::FIELD_READ_HISTORY_EXT)) {
            $v = $decoded->_readHistory ?? new \stdClass();
            $v->value = $decoded->readHistory ?? null;
            $type->setReadHistory(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->updateCreate)
            || isset($decoded->_updateCreate)
            || property_exists($decoded, self::FIELD_UPDATE_CREATE)
            || property_exists($decoded, self::FIELD_UPDATE_CREATE_EXT)) {
            $v = $decoded->_updateCreate ?? new \stdClass();
            $v->value = $decoded->updateCreate ?? null;
            $type->setUpdateCreate(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->conditionalCreate)
            || isset($decoded->_conditionalCreate)
            || property_exists($decoded, self::FIELD_CONDITIONAL_CREATE)
            || property_exists($decoded, self::FIELD_CONDITIONAL_CREATE_EXT)) {
            $v = $decoded->_conditionalCreate ?? new \stdClass();
            $v->value = $decoded->conditionalCreate ?? null;
            $type->setConditionalCreate(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->conditionalRead)
            || isset($decoded->_conditionalRead)
            || property_exists($decoded, self::FIELD_CONDITIONAL_READ)
            || property_exists($decoded, self::FIELD_CONDITIONAL_READ_EXT)) {
            $v = $decoded->_conditionalRead ?? new \stdClass();
            $v->value = $decoded->conditionalRead ?? null;
            $type->setConditionalRead(FHIRConditionalReadStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->conditionalUpdate)
            || isset($decoded->_conditionalUpdate)
            || property_exists($decoded, self::FIELD_CONDITIONAL_UPDATE)
            || property_exists($decoded, self::FIELD_CONDITIONAL_UPDATE_EXT)) {
            $v = $decoded->_conditionalUpdate ?? new \stdClass();
            $v->value = $decoded->conditionalUpdate ?? null;
            $type->setConditionalUpdate(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->conditionalDelete)
            || isset($decoded->_conditionalDelete)
            || property_exists($decoded, self::FIELD_CONDITIONAL_DELETE)
            || property_exists($decoded, self::FIELD_CONDITIONAL_DELETE_EXT)) {
            $v = $decoded->_conditionalDelete ?? new \stdClass();
            $v->value = $decoded->conditionalDelete ?? null;
            $type->setConditionalDelete(FHIRConditionalDeleteStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->referencePolicy)
            || isset($decoded->_referencePolicy)
            || property_exists($decoded, self::FIELD_REFERENCE_POLICY)
            || property_exists($decoded, self::FIELD_REFERENCE_POLICY_EXT)) {
            $vals = (array)($decoded->referencePolicy ?? []);
            $exts = (array)($decoded->_referencePolicy ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addReferencePolicy(FHIRReferenceHandlingPolicy::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->searchInclude)
            || isset($decoded->_searchInclude)
            || property_exists($decoded, self::FIELD_SEARCH_INCLUDE)
            || property_exists($decoded, self::FIELD_SEARCH_INCLUDE_EXT)) {
            $vals = (array)($decoded->searchInclude ?? []);
            $exts = (array)($decoded->_searchInclude ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addSearchInclude(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->searchRevInclude)
            || isset($decoded->_searchRevInclude)
            || property_exists($decoded, self::FIELD_SEARCH_REV_INCLUDE)
            || property_exists($decoded, self::FIELD_SEARCH_REV_INCLUDE_EXT)) {
            $vals = (array)($decoded->searchRevInclude ?? []);
            $exts = (array)($decoded->_searchRevInclude ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addSearchRevInclude(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->searchParam) || property_exists($decoded, self::FIELD_SEARCH_PARAM)) {
            if (is_object($decoded->searchParam)) {
                $vals = [$decoded->searchParam];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SEARCH_PARAM, true);
            } else {
                $vals = $decoded->searchParam;
            }
            foreach($vals as $v) {
                $type->addSearchParam(FHIRCapabilityStatementSearchParam::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->operation) || property_exists($decoded, self::FIELD_OPERATION)) {
            if (is_object($decoded->operation)) {
                $vals = [$decoded->operation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_OPERATION, true);
            } else {
                $vals = $decoded->operation;
            }
            foreach($vals as $v) {
                $type->addOperation(FHIRCapabilityStatementOperation::jsonUnserialize($v, $config));
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
        if (isset($this->type)) {
            if (null !== ($val = $this->type->getValue())) {
                $out->type = $val;
            }
            if ($this->type->_nonValueFieldDefined()) {
                $ext = $this->type->jsonSerialize();
                unset($ext->value);
                $out->_type = $ext;
            }
        }
        if (isset($this->profile)) {
            if (null !== ($val = $this->profile->getValue())) {
                $out->profile = $val;
            }
            if ($this->profile->_nonValueFieldDefined()) {
                $ext = $this->profile->jsonSerialize();
                unset($ext->value);
                $out->_profile = $ext;
            }
        }
        if (isset($this->supportedProfile) && [] !== $this->supportedProfile) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->supportedProfile as $v) {
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
                $out->supportedProfile = $vals;
            }
            if ($hasExts) {
                $out->_supportedProfile = $exts;
            }
        }
        if (isset($this->documentation)) {
            if (null !== ($val = $this->documentation->getValue())) {
                $out->documentation = $val;
            }
            if ($this->documentation->_nonValueFieldDefined()) {
                $ext = $this->documentation->jsonSerialize();
                unset($ext->value);
                $out->_documentation = $ext;
            }
        }
        if (isset($this->interaction) && [] !== $this->interaction) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INTERACTION) && 1 === count($this->interaction)) {
                $out->interaction = $this->interaction[0];
            } else {
                $out->interaction = $this->interaction;
            }
        }
        if (isset($this->versioning)) {
            if (null !== ($val = $this->versioning->getValue())) {
                $out->versioning = $val;
            }
            if ($this->versioning->_nonValueFieldDefined()) {
                $ext = $this->versioning->jsonSerialize();
                unset($ext->value);
                $out->_versioning = $ext;
            }
        }
        if (isset($this->readHistory)) {
            if (null !== ($val = $this->readHistory->getValue())) {
                $out->readHistory = $val;
            }
            if ($this->readHistory->_nonValueFieldDefined()) {
                $ext = $this->readHistory->jsonSerialize();
                unset($ext->value);
                $out->_readHistory = $ext;
            }
        }
        if (isset($this->updateCreate)) {
            if (null !== ($val = $this->updateCreate->getValue())) {
                $out->updateCreate = $val;
            }
            if ($this->updateCreate->_nonValueFieldDefined()) {
                $ext = $this->updateCreate->jsonSerialize();
                unset($ext->value);
                $out->_updateCreate = $ext;
            }
        }
        if (isset($this->conditionalCreate)) {
            if (null !== ($val = $this->conditionalCreate->getValue())) {
                $out->conditionalCreate = $val;
            }
            if ($this->conditionalCreate->_nonValueFieldDefined()) {
                $ext = $this->conditionalCreate->jsonSerialize();
                unset($ext->value);
                $out->_conditionalCreate = $ext;
            }
        }
        if (isset($this->conditionalRead)) {
            if (null !== ($val = $this->conditionalRead->getValue())) {
                $out->conditionalRead = $val;
            }
            if ($this->conditionalRead->_nonValueFieldDefined()) {
                $ext = $this->conditionalRead->jsonSerialize();
                unset($ext->value);
                $out->_conditionalRead = $ext;
            }
        }
        if (isset($this->conditionalUpdate)) {
            if (null !== ($val = $this->conditionalUpdate->getValue())) {
                $out->conditionalUpdate = $val;
            }
            if ($this->conditionalUpdate->_nonValueFieldDefined()) {
                $ext = $this->conditionalUpdate->jsonSerialize();
                unset($ext->value);
                $out->_conditionalUpdate = $ext;
            }
        }
        if (isset($this->conditionalDelete)) {
            if (null !== ($val = $this->conditionalDelete->getValue())) {
                $out->conditionalDelete = $val;
            }
            if ($this->conditionalDelete->_nonValueFieldDefined()) {
                $ext = $this->conditionalDelete->jsonSerialize();
                unset($ext->value);
                $out->_conditionalDelete = $ext;
            }
        }
        if (isset($this->referencePolicy) && [] !== $this->referencePolicy) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->referencePolicy as $v) {
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
                $out->referencePolicy = $vals;
            }
            if ($hasExts) {
                $out->_referencePolicy = $exts;
            }
        }
        if (isset($this->searchInclude) && [] !== $this->searchInclude) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->searchInclude as $v) {
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
                $out->searchInclude = $vals;
            }
            if ($hasExts) {
                $out->_searchInclude = $exts;
            }
        }
        if (isset($this->searchRevInclude) && [] !== $this->searchRevInclude) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->searchRevInclude as $v) {
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
                $out->searchRevInclude = $vals;
            }
            if ($hasExts) {
                $out->_searchRevInclude = $exts;
            }
        }
        if (isset($this->searchParam) && [] !== $this->searchParam) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SEARCH_PARAM) && 1 === count($this->searchParam)) {
                $out->searchParam = $this->searchParam[0];
            } else {
                $out->searchParam = $this->searchParam;
            }
        }
        if (isset($this->operation) && [] !== $this->operation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_OPERATION) && 1 === count($this->operation)) {
                $out->operation = $this->operation[0];
            } else {
                $out->operation = $this->operation;
            }
        }
        return $out;
    }
}
