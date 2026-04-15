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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRMessageheaderResponseRequestList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRMessageSignificanceCategoryList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionFocus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageheaderResponseRequest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageSignificanceCategory;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Defines the characteristics of a message that can be shared between systems,
 * including the type of event that initiates the message, the content to be
 * transmitted and what response(s), if any, are permitted.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMessageDefinition extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MESSAGE_DEFINITION;

    /* class_default.php:56 */
    public const FIELD_URL = 'url';
    public const FIELD_URL_EXT = '_url';
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_VERSION = 'version';
    public const FIELD_VERSION_EXT = '_version';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_TITLE = 'title';
    public const FIELD_TITLE_EXT = '_title';
    public const FIELD_REPLACES = 'replaces';
    public const FIELD_REPLACES_EXT = '_replaces';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_EXPERIMENTAL = 'experimental';
    public const FIELD_EXPERIMENTAL_EXT = '_experimental';
    public const FIELD_DATE = 'date';
    public const FIELD_DATE_EXT = '_date';
    public const FIELD_PUBLISHER = 'publisher';
    public const FIELD_PUBLISHER_EXT = '_publisher';
    public const FIELD_CONTACT = 'contact';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_USE_CONTEXT = 'useContext';
    public const FIELD_JURISDICTION = 'jurisdiction';
    public const FIELD_PURPOSE = 'purpose';
    public const FIELD_PURPOSE_EXT = '_purpose';
    public const FIELD_COPYRIGHT = 'copyright';
    public const FIELD_COPYRIGHT_EXT = '_copyright';
    public const FIELD_BASE = 'base';
    public const FIELD_BASE_EXT = '_base';
    public const FIELD_PARENT = 'parent';
    public const FIELD_PARENT_EXT = '_parent';
    public const FIELD_EVENT_CODING = 'eventCoding';
    public const FIELD_EVENT_URI = 'eventUri';
    public const FIELD_EVENT_URI_EXT = '_eventUri';
    public const FIELD_CATEGORY = 'category';
    public const FIELD_CATEGORY_EXT = '_category';
    public const FIELD_FOCUS = 'focus';
    public const FIELD_RESPONSE_REQUIRED = 'responseRequired';
    public const FIELD_RESPONSE_REQUIRED_EXT = '_responseRequired';
    public const FIELD_ALLOWED_RESPONSE = 'allowedResponse';
    public const FIELD_GRAPH = 'graph';
    public const FIELD_GRAPH_EXT = '_graph';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_DATE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VERSION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TITLE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_EXPERIMENTAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PUBLISHER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PURPOSE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COPYRIGHT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_BASE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_EVENT_URI => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CATEGORY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RESPONSE_REQUIRED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The business identifier that is used to reference the MessageDefinition and *is*
     * expected to be consistent from server to server.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $url;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A formal identifier that is used to identify this message definition when it is
     * represented in other formats, or referenced in a specification, model, design or
     * an instance.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identifier that is used to identify this version of the message definition
     * when it is referenced in a specification, model, design or instance. This is an
     * arbitrary value managed by the message definition author and is not expected to
     * be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a
     * managed version is not available. There is also no expectation that versions can
     * be placed in a lexicographical sequence.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $version;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A natural language name identifying the message definition. This name should be
     * usable as an identifier for the module by machine processing applications such
     * as code generation.
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
     * A short, descriptive, user-friendly title for the message definition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $title;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A MessageDefinition that is superseded by this definition.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    #[FHIRCanonical]
    protected array $replaces;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of this message definition. Enables tracking the life-cycle of the
     * content.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus
     */
    #[FHIRPublicationStatus]
    protected FHIRPublicationStatus $status;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A Boolean value to indicate that this message definition is authored for testing
     * purposes (or education/evaluation/marketing) and is not intended to be used for
     * genuine usage.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $experimental;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date (and optionally time) when the message definition was published. The
     * date must change when the business version changes and it must change if the
     * status code changes. In addition, it should change when the substantive content
     * of the message definition changes.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $date;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the organization or individual that published the message
     * definition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $publisher;
    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details to assist a user in finding and communicating with the
     * publisher.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail>
     */
    #[FHIRContactDetail]
    protected array $contact;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A free text natural language description of the message definition from a
     * consumer's perspective.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $description;
    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The content was developed with a focus and intent of supporting the contexts
     * that are listed. These contexts may be general categories (gender, age, ...) or
     * may be references to specific programs (insurance plans, studies, ...) and may
     * be used to assist with indexing and searching for appropriate message definition
     * instances.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext>
     */
    #[FHIRUsageContext]
    protected array $useContext;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A legal or geographic region in which the message definition is intended to be
     * used.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $jurisdiction;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Explanation of why this message definition is needed and why it has been
     * designed as it has.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $purpose;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A copyright statement relating to the message definition and/or its contents.
     * Copyright statements are generally legal restrictions on the use and publishing
     * of the message definition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $copyright;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The MessageDefinition that is the basis for the contents of this resource.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $base;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Identifies a protocol or workflow that this MessageDefinition represents a step
     * in.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    #[FHIRCanonical]
    protected array $parent;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Event code or link to the EventDefinition. (choose any one of event*, but only
     * one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    #[FHIRCoding]
    protected FHIRCoding $eventCoding;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Event code or link to the EventDefinition. (choose any one of event*, but only
     * one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $eventUri;
    /**
     * The impact of the content of a message.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The impact of the content of the message.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageSignificanceCategory
     */
    #[FHIRMessageSignificanceCategory]
    protected FHIRMessageSignificanceCategory $category;
    /**
     * Defines the characteristics of a message that can be shared between systems,
     * including the type of event that initiates the message, the content to be
     * transmitted and what response(s), if any, are permitted.
     *
     * Identifies the resource (or resources) that are being addressed by the event.
     * For example, the Encounter for an admit message or two Account records for a
     * merge.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionFocus>
     */
    #[FHIRMessageDefinitionFocus]
    protected array $focus;
    /**
     * HL7-defined table of codes which identify conditions under which acknowledgments
     * are required to be returned in response to a message.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Declare at a message definition level whether a response is required or only
     * upon error or success, or never.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageheaderResponseRequest
     */
    #[FHIRMessageheaderResponseRequest]
    protected FHIRMessageheaderResponseRequest $responseRequired;
    /**
     * Defines the characteristics of a message that can be shared between systems,
     * including the type of event that initiates the message, the content to be
     * transmitted and what response(s), if any, are permitted.
     *
     * Indicates what types of messages may be sent as an application-level response to
     * this message.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse>
     */
    #[FHIRMessageDefinitionAllowedResponse]
    protected array $allowedResponse;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Canonical reference to a GraphDefinition. If a URL is provided, it is the
     * canonical reference to a [[[GraphDefinition]]] that it controls what resources
     * are to be added to the bundle when building the document. The GraphDefinition
     * can also specify profiles that apply to the various resources.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    #[FHIRCanonical]
    protected array $graph;

    /* constructor.php:61 */
    /**
     * FHIRMessageDefinition Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $url
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $version
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $title
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $replaces
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus $status
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $experimental
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $date
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $publisher
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail> $contact
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $description
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext> $useContext
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $jurisdiction
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $purpose
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $copyright
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $base
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $parent
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $eventCoding
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $eventUri
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRMessageSignificanceCategoryList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageSignificanceCategory $category
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionFocus> $focus
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRMessageheaderResponseRequestList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageheaderResponseRequest $responseRequired
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse> $allowedResponse
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $graph
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
                                null|string|FHIRUriPrimitive|FHIRUri $url = null,
                                null|iterable $identifier = null,
                                null|string|FHIRStringPrimitive|FHIRString $version = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|string|FHIRStringPrimitive|FHIRString $title = null,
                                null|iterable $replaces = null,
                                null|string|FHIRPublicationStatusList|FHIRPublicationStatus $status = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $experimental = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $date = null,
                                null|string|FHIRStringPrimitive|FHIRString $publisher = null,
                                null|iterable $contact = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $description = null,
                                null|iterable $useContext = null,
                                null|iterable $jurisdiction = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $purpose = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $copyright = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $base = null,
                                null|iterable $parent = null,
                                null|FHIRCoding $eventCoding = null,
                                null|string|FHIRUriPrimitive|FHIRUri $eventUri = null,
                                null|string|FHIRMessageSignificanceCategoryList|FHIRMessageSignificanceCategory $category = null,
                                null|iterable $focus = null,
                                null|string|FHIRMessageheaderResponseRequestList|FHIRMessageheaderResponseRequest $responseRequired = null,
                                null|iterable $allowedResponse = null,
                                null|iterable $graph = null,
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
        if (null !== $url) {
            $this->setUrl($url);
        }
        if (null !== $identifier) {
            $this->setIdentifier(...$identifier);
        }
        if (null !== $version) {
            $this->setVersion($version);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $title) {
            $this->setTitle($title);
        }
        if (null !== $replaces) {
            $this->setReplaces(...$replaces);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $experimental) {
            $this->setExperimental($experimental);
        }
        if (null !== $date) {
            $this->setDate($date);
        }
        if (null !== $publisher) {
            $this->setPublisher($publisher);
        }
        if (null !== $contact) {
            $this->setContact(...$contact);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $useContext) {
            $this->setUseContext(...$useContext);
        }
        if (null !== $jurisdiction) {
            $this->setJurisdiction(...$jurisdiction);
        }
        if (null !== $purpose) {
            $this->setPurpose($purpose);
        }
        if (null !== $copyright) {
            $this->setCopyright($copyright);
        }
        if (null !== $base) {
            $this->setBase($base);
        }
        if (null !== $parent) {
            $this->setParent(...$parent);
        }
        if (null !== $eventCoding) {
            $this->setEventCoding($eventCoding);
        }
        if (null !== $eventUri) {
            $this->setEventUri($eventUri);
        }
        if (null !== $category) {
            $this->setCategory($category);
        }
        if (null !== $focus) {
            $this->setFocus(...$focus);
        }
        if (null !== $responseRequired) {
            $this->setResponseRequired($responseRequired);
        }
        if (null !== $allowedResponse) {
            $this->setAllowedResponse(...$allowedResponse);
        }
        if (null !== $graph) {
            $this->setGraph(...$graph);
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
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The business identifier that is used to reference the MessageDefinition and *is*
     * expected to be consistent from server to server.
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
     * The business identifier that is used to reference the MessageDefinition and *is*
     * expected to be consistent from server to server.
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
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A formal identifier that is used to identify this message definition when it is
     * represented in other formats, or referenced in a specification, model, design or
     * an instance.
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
     * A formal identifier that is used to identify this message definition when it is
     * represented in other formats, or referenced in a specification, model, design or
     * an instance.
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
     * A formal identifier that is used to identify this message definition when it is
     * represented in other formats, or referenced in a specification, model, design or
     * an instance.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identifier that is used to identify this version of the message definition
     * when it is referenced in a specification, model, design or instance. This is an
     * arbitrary value managed by the message definition author and is not expected to
     * be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a
     * managed version is not available. There is also no expectation that versions can
     * be placed in a lexicographical sequence.
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
     * The identifier that is used to identify this version of the message definition
     * when it is referenced in a specification, model, design or instance. This is an
     * arbitrary value managed by the message definition author and is not expected to
     * be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a
     * managed version is not available. There is also no expectation that versions can
     * be placed in a lexicographical sequence.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A natural language name identifying the message definition. This name should be
     * usable as an identifier for the module by machine processing applications such
     * as code generation.
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
     * A natural language name identifying the message definition. This name should be
     * usable as an identifier for the module by machine processing applications such
     * as code generation.
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
     * A short, descriptive, user-friendly title for the message definition.
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
     * A short, descriptive, user-friendly title for the message definition.
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
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A MessageDefinition that is superseded by this definition.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getReplaces(): array
    {
        return $this->replaces ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getReplacesIterator(): iterable
    {
        if (!isset($this->replaces)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->replaces);
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A MessageDefinition that is superseded by this definition.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $replaces
     * @return static
     */
    public function addReplaces(string|FHIRCanonicalPrimitive|FHIRCanonical $replaces): self
    {
        if (!($replaces instanceof FHIRCanonical)) {
            $replaces = new FHIRCanonical(value: $replaces);
        }
        if (!isset($this->replaces)) {
            $this->replaces = [];
        }
        $this->replaces[] = $replaces;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A MessageDefinition that is superseded by this definition.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical ...$replaces
     * @return static
     */
    public function setReplaces(string|FHIRCanonicalPrimitive|FHIRCanonical ...$replaces): self
    {
        if ([] === $replaces) {
            unset($this->replaces);
            return $this;
        }
        $this->replaces = [];
        foreach($replaces as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->replaces[] = $v;
            } else {
                $this->replaces[] = new FHIRCanonical(value: $v);
            }
        }
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of this message definition. Enables tracking the life-cycle of the
     * content.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus
     */
    public function getStatus(): null|FHIRPublicationStatus
    {
        return $this->status ?? null;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of this message definition. Enables tracking the life-cycle of the
     * content.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRPublicationStatusList|FHIRPublicationStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRPublicationStatus)) {
            $status = new FHIRPublicationStatus(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A Boolean value to indicate that this message definition is authored for testing
     * purposes (or education/evaluation/marketing) and is not intended to be used for
     * genuine usage.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getExperimental(): null|FHIRBoolean
    {
        return $this->experimental ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A Boolean value to indicate that this message definition is authored for testing
     * purposes (or education/evaluation/marketing) and is not intended to be used for
     * genuine usage.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $experimental
     * @return static
     */
    public function setExperimental(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $experimental): self
    {
        if (null === $experimental) {
            unset($this->experimental);
            return $this;
        }
        if (!($experimental instanceof FHIRBoolean)) {
            $experimental = new FHIRBoolean(value: $experimental);
        }
        $this->experimental = $experimental;
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
     * The date (and optionally time) when the message definition was published. The
     * date must change when the business version changes and it must change if the
     * status code changes. In addition, it should change when the substantive content
     * of the message definition changes.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getDate(): null|FHIRDateTime
    {
        return $this->date ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date (and optionally time) when the message definition was published. The
     * date must change when the business version changes and it must change if the
     * status code changes. In addition, it should change when the substantive content
     * of the message definition changes.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $date
     * @return static
     */
    public function setDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $date): self
    {
        if (null === $date) {
            unset($this->date);
            return $this;
        }
        if (!($date instanceof FHIRDateTime)) {
            $date = new FHIRDateTime(value: $date);
        }
        $this->date = $date;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the organization or individual that published the message
     * definition.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getPublisher(): null|FHIRString
    {
        return $this->publisher ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the organization or individual that published the message
     * definition.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $publisher
     * @return static
     */
    public function setPublisher(null|string|FHIRStringPrimitive|FHIRString $publisher): self
    {
        if (null === $publisher) {
            unset($this->publisher);
            return $this;
        }
        if (!($publisher instanceof FHIRString)) {
            $publisher = new FHIRString(value: $publisher);
        }
        $this->publisher = $publisher;
        return $this;
    }

    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details to assist a user in finding and communicating with the
     * publisher.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail>
     */
    public function getContact(): array
    {
        return $this->contact ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail>
     */
    public function getContactIterator(): iterable
    {
        if (!isset($this->contact)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->contact);
    }

    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details to assist a user in finding and communicating with the
     * publisher.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail $contact
     * @return static
     */
    public function addContact(FHIRContactDetail $contact): self
    {
        if (!isset($this->contact)) {
            $this->contact = [];
        }
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details to assist a user in finding and communicating with the
     * publisher.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail ...$contact
     * @return static
     */
    public function setContact(FHIRContactDetail ...$contact): self
    {
        if ([] === $contact) {
            unset($this->contact);
            return $this;
        }
        $this->contact = $contact;
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
     * A free text natural language description of the message definition from a
     * consumer's perspective.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getDescription(): null|FHIRMarkdown
    {
        return $this->description ?? null;
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
     * A free text natural language description of the message definition from a
     * consumer's perspective.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $description
     * @return static
     */
    public function setDescription(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRMarkdown)) {
            $description = new FHIRMarkdown(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The content was developed with a focus and intent of supporting the contexts
     * that are listed. These contexts may be general categories (gender, age, ...) or
     * may be references to specific programs (insurance plans, studies, ...) and may
     * be used to assist with indexing and searching for appropriate message definition
     * instances.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext>
     */
    public function getUseContext(): array
    {
        return $this->useContext ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext>
     */
    public function getUseContextIterator(): iterable
    {
        if (!isset($this->useContext)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->useContext);
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The content was developed with a focus and intent of supporting the contexts
     * that are listed. These contexts may be general categories (gender, age, ...) or
     * may be references to specific programs (insurance plans, studies, ...) and may
     * be used to assist with indexing and searching for appropriate message definition
     * instances.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext $useContext
     * @return static
     */
    public function addUseContext(FHIRUsageContext $useContext): self
    {
        if (!isset($this->useContext)) {
            $this->useContext = [];
        }
        $this->useContext[] = $useContext;
        return $this;
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The content was developed with a focus and intent of supporting the contexts
     * that are listed. These contexts may be general categories (gender, age, ...) or
     * may be references to specific programs (insurance plans, studies, ...) and may
     * be used to assist with indexing and searching for appropriate message definition
     * instances.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext ...$useContext
     * @return static
     */
    public function setUseContext(FHIRUsageContext ...$useContext): self
    {
        if ([] === $useContext) {
            unset($this->useContext);
            return $this;
        }
        $this->useContext = $useContext;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A legal or geographic region in which the message definition is intended to be
     * used.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getJurisdiction(): array
    {
        return $this->jurisdiction ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getJurisdictionIterator(): iterable
    {
        if (!isset($this->jurisdiction)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->jurisdiction);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A legal or geographic region in which the message definition is intended to be
     * used.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $jurisdiction
     * @return static
     */
    public function addJurisdiction(FHIRCodeableConcept $jurisdiction): self
    {
        if (!isset($this->jurisdiction)) {
            $this->jurisdiction = [];
        }
        $this->jurisdiction[] = $jurisdiction;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A legal or geographic region in which the message definition is intended to be
     * used.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$jurisdiction
     * @return static
     */
    public function setJurisdiction(FHIRCodeableConcept ...$jurisdiction): self
    {
        if ([] === $jurisdiction) {
            unset($this->jurisdiction);
            return $this;
        }
        $this->jurisdiction = $jurisdiction;
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
     * Explanation of why this message definition is needed and why it has been
     * designed as it has.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getPurpose(): null|FHIRMarkdown
    {
        return $this->purpose ?? null;
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
     * Explanation of why this message definition is needed and why it has been
     * designed as it has.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $purpose
     * @return static
     */
    public function setPurpose(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $purpose): self
    {
        if (null === $purpose) {
            unset($this->purpose);
            return $this;
        }
        if (!($purpose instanceof FHIRMarkdown)) {
            $purpose = new FHIRMarkdown(value: $purpose);
        }
        $this->purpose = $purpose;
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
     * A copyright statement relating to the message definition and/or its contents.
     * Copyright statements are generally legal restrictions on the use and publishing
     * of the message definition.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getCopyright(): null|FHIRMarkdown
    {
        return $this->copyright ?? null;
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
     * A copyright statement relating to the message definition and/or its contents.
     * Copyright statements are generally legal restrictions on the use and publishing
     * of the message definition.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $copyright
     * @return static
     */
    public function setCopyright(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $copyright): self
    {
        if (null === $copyright) {
            unset($this->copyright);
            return $this;
        }
        if (!($copyright instanceof FHIRMarkdown)) {
            $copyright = new FHIRMarkdown(value: $copyright);
        }
        $this->copyright = $copyright;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The MessageDefinition that is the basis for the contents of this resource.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getBase(): null|FHIRCanonical
    {
        return $this->base ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The MessageDefinition that is the basis for the contents of this resource.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $base
     * @return static
     */
    public function setBase(null|string|FHIRCanonicalPrimitive|FHIRCanonical $base): self
    {
        if (null === $base) {
            unset($this->base);
            return $this;
        }
        if (!($base instanceof FHIRCanonical)) {
            $base = new FHIRCanonical(value: $base);
        }
        $this->base = $base;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Identifies a protocol or workflow that this MessageDefinition represents a step
     * in.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getParent(): array
    {
        return $this->parent ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getParentIterator(): iterable
    {
        if (!isset($this->parent)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->parent);
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Identifies a protocol or workflow that this MessageDefinition represents a step
     * in.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $parent
     * @return static
     */
    public function addParent(string|FHIRCanonicalPrimitive|FHIRCanonical $parent): self
    {
        if (!($parent instanceof FHIRCanonical)) {
            $parent = new FHIRCanonical(value: $parent);
        }
        if (!isset($this->parent)) {
            $this->parent = [];
        }
        $this->parent[] = $parent;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Identifies a protocol or workflow that this MessageDefinition represents a step
     * in.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical ...$parent
     * @return static
     */
    public function setParent(string|FHIRCanonicalPrimitive|FHIRCanonical ...$parent): self
    {
        if ([] === $parent) {
            unset($this->parent);
            return $this;
        }
        $this->parent = [];
        foreach($parent as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->parent[] = $v;
            } else {
                $this->parent[] = new FHIRCanonical(value: $v);
            }
        }
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Event code or link to the EventDefinition. (choose any one of event*, but only
     * one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    public function getEventCoding(): null|FHIRCoding
    {
        return $this->eventCoding ?? null;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Event code or link to the EventDefinition. (choose any one of event*, but only
     * one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $eventCoding
     * @return static
     */
    public function setEventCoding(null|FHIRCoding $eventCoding): self
    {
        if (null === $eventCoding) {
            unset($this->eventCoding);
            return $this;
        }
        $this->eventCoding = $eventCoding;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Event code or link to the EventDefinition. (choose any one of event*, but only
     * one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getEventUri(): null|FHIRUri
    {
        return $this->eventUri ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Event code or link to the EventDefinition. (choose any one of event*, but only
     * one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $eventUri
     * @return static
     */
    public function setEventUri(null|string|FHIRUriPrimitive|FHIRUri $eventUri): self
    {
        if (null === $eventUri) {
            unset($this->eventUri);
            return $this;
        }
        if (!($eventUri instanceof FHIRUri)) {
            $eventUri = new FHIRUri(value: $eventUri);
        }
        $this->eventUri = $eventUri;
        return $this;
    }

    /**
     * The impact of the content of a message.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The impact of the content of the message.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageSignificanceCategory
     */
    public function getCategory(): null|FHIRMessageSignificanceCategory
    {
        return $this->category ?? null;
    }

    /**
     * The impact of the content of a message.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The impact of the content of the message.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRMessageSignificanceCategoryList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageSignificanceCategory $category
     * @return static
     */
    public function setCategory(null|string|FHIRMessageSignificanceCategoryList|FHIRMessageSignificanceCategory $category): self
    {
        if (null === $category) {
            unset($this->category);
            return $this;
        }
        if (!($category instanceof FHIRMessageSignificanceCategory)) {
            $category = new FHIRMessageSignificanceCategory(value: $category);
        }
        $this->category = $category;
        return $this;
    }

    /**
     * Defines the characteristics of a message that can be shared between systems,
     * including the type of event that initiates the message, the content to be
     * transmitted and what response(s), if any, are permitted.
     *
     * Identifies the resource (or resources) that are being addressed by the event.
     * For example, the Encounter for an admit message or two Account records for a
     * merge.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionFocus>
     */
    public function getFocus(): array
    {
        return $this->focus ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionFocus>
     */
    public function getFocusIterator(): iterable
    {
        if (!isset($this->focus)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->focus);
    }

    /**
     * Defines the characteristics of a message that can be shared between systems,
     * including the type of event that initiates the message, the content to be
     * transmitted and what response(s), if any, are permitted.
     *
     * Identifies the resource (or resources) that are being addressed by the event.
     * For example, the Encounter for an admit message or two Account records for a
     * merge.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionFocus $focus
     * @return static
     */
    public function addFocus(FHIRMessageDefinitionFocus $focus): self
    {
        if (!isset($this->focus)) {
            $this->focus = [];
        }
        $this->focus[] = $focus;
        return $this;
    }

    /**
     * Defines the characteristics of a message that can be shared between systems,
     * including the type of event that initiates the message, the content to be
     * transmitted and what response(s), if any, are permitted.
     *
     * Identifies the resource (or resources) that are being addressed by the event.
     * For example, the Encounter for an admit message or two Account records for a
     * merge.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionFocus ...$focus
     * @return static
     */
    public function setFocus(FHIRMessageDefinitionFocus ...$focus): self
    {
        if ([] === $focus) {
            unset($this->focus);
            return $this;
        }
        $this->focus = $focus;
        return $this;
    }

    /**
     * HL7-defined table of codes which identify conditions under which acknowledgments
     * are required to be returned in response to a message.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Declare at a message definition level whether a response is required or only
     * upon error or success, or never.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageheaderResponseRequest
     */
    public function getResponseRequired(): null|FHIRMessageheaderResponseRequest
    {
        return $this->responseRequired ?? null;
    }

    /**
     * HL7-defined table of codes which identify conditions under which acknowledgments
     * are required to be returned in response to a message.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Declare at a message definition level whether a response is required or only
     * upon error or success, or never.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRMessageheaderResponseRequestList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMessageheaderResponseRequest $responseRequired
     * @return static
     */
    public function setResponseRequired(null|string|FHIRMessageheaderResponseRequestList|FHIRMessageheaderResponseRequest $responseRequired): self
    {
        if (null === $responseRequired) {
            unset($this->responseRequired);
            return $this;
        }
        if (!($responseRequired instanceof FHIRMessageheaderResponseRequest)) {
            $responseRequired = new FHIRMessageheaderResponseRequest(value: $responseRequired);
        }
        $this->responseRequired = $responseRequired;
        return $this;
    }

    /**
     * Defines the characteristics of a message that can be shared between systems,
     * including the type of event that initiates the message, the content to be
     * transmitted and what response(s), if any, are permitted.
     *
     * Indicates what types of messages may be sent as an application-level response to
     * this message.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse>
     */
    public function getAllowedResponse(): array
    {
        return $this->allowedResponse ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse>
     */
    public function getAllowedResponseIterator(): iterable
    {
        if (!isset($this->allowedResponse)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->allowedResponse);
    }

    /**
     * Defines the characteristics of a message that can be shared between systems,
     * including the type of event that initiates the message, the content to be
     * transmitted and what response(s), if any, are permitted.
     *
     * Indicates what types of messages may be sent as an application-level response to
     * this message.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse $allowedResponse
     * @return static
     */
    public function addAllowedResponse(FHIRMessageDefinitionAllowedResponse $allowedResponse): self
    {
        if (!isset($this->allowedResponse)) {
            $this->allowedResponse = [];
        }
        $this->allowedResponse[] = $allowedResponse;
        return $this;
    }

    /**
     * Defines the characteristics of a message that can be shared between systems,
     * including the type of event that initiates the message, the content to be
     * transmitted and what response(s), if any, are permitted.
     *
     * Indicates what types of messages may be sent as an application-level response to
     * this message.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMessageDefinition\FHIRMessageDefinitionAllowedResponse ...$allowedResponse
     * @return static
     */
    public function setAllowedResponse(FHIRMessageDefinitionAllowedResponse ...$allowedResponse): self
    {
        if ([] === $allowedResponse) {
            unset($this->allowedResponse);
            return $this;
        }
        $this->allowedResponse = $allowedResponse;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Canonical reference to a GraphDefinition. If a URL is provided, it is the
     * canonical reference to a [[[GraphDefinition]]] that it controls what resources
     * are to be added to the bundle when building the document. The GraphDefinition
     * can also specify profiles that apply to the various resources.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getGraph(): array
    {
        return $this->graph ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getGraphIterator(): iterable
    {
        if (!isset($this->graph)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->graph);
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Canonical reference to a GraphDefinition. If a URL is provided, it is the
     * canonical reference to a [[[GraphDefinition]]] that it controls what resources
     * are to be added to the bundle when building the document. The GraphDefinition
     * can also specify profiles that apply to the various resources.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $graph
     * @return static
     */
    public function addGraph(string|FHIRCanonicalPrimitive|FHIRCanonical $graph): self
    {
        if (!($graph instanceof FHIRCanonical)) {
            $graph = new FHIRCanonical(value: $graph);
        }
        if (!isset($this->graph)) {
            $this->graph = [];
        }
        $this->graph[] = $graph;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Canonical reference to a GraphDefinition. If a URL is provided, it is the
     * canonical reference to a [[[GraphDefinition]]] that it controls what resources
     * are to be added to the bundle when building the document. The GraphDefinition
     * can also specify profiles that apply to the various resources.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical ...$graph
     * @return static
     */
    public function setGraph(string|FHIRCanonicalPrimitive|FHIRCanonical ...$graph): self
    {
        if ([] === $graph) {
            unset($this->graph);
            return $this;
        }
        $this->graph = [];
        foreach($graph as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->graph[] = $v;
            } else {
                $this->graph[] = new FHIRCanonical(value: $v);
            }
        }
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMessageDefinition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMessageDefinition
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMessageDefinition)) {
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
            } else if (self::FIELD_URL === $cen) {
                $type->setUrl(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VERSION === $cen) {
                $type->setVersion(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TITLE === $cen) {
                $type->setTitle(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REPLACES === $cen) {
                $type->addReplaces(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRPublicationStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXPERIMENTAL === $cen) {
                $type->setExperimental(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATE === $cen) {
                $type->setDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PUBLISHER === $cen) {
                $type->setPublisher(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTACT === $cen) {
                $type->addContact(FHIRContactDetail::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_USE_CONTEXT === $cen) {
                $type->addUseContext(FHIRUsageContext::xmlUnserialize($ce, $config));
            } else if (self::FIELD_JURISDICTION === $cen) {
                $type->addJurisdiction(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PURPOSE === $cen) {
                $type->setPurpose(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COPYRIGHT === $cen) {
                $type->setCopyright(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BASE === $cen) {
                $type->setBase(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARENT === $cen) {
                $type->addParent(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EVENT_CODING === $cen) {
                $type->setEventCoding(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EVENT_URI === $cen) {
                $type->setEventUri(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->setCategory(FHIRMessageSignificanceCategory::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FOCUS === $cen) {
                $type->addFocus(FHIRMessageDefinitionFocus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESPONSE_REQUIRED === $cen) {
                $type->setResponseRequired(FHIRMessageheaderResponseRequest::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ALLOWED_RESPONSE === $cen) {
                $type->addAllowedResponse(FHIRMessageDefinitionAllowedResponse::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GRAPH === $cen) {
                $type->addGraph(FHIRCanonical::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EXPERIMENTAL])) {
            if (isset($type->experimental)) {
                $type->experimental->setValue((string)$attributes[self::FIELD_EXPERIMENTAL]);
            } else {
                $type->setExperimental((string)$attributes[self::FIELD_EXPERIMENTAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EXPERIMENTAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DATE])) {
            if (isset($type->date)) {
                $type->date->setValue((string)$attributes[self::FIELD_DATE]);
            } else {
                $type->setDate((string)$attributes[self::FIELD_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PUBLISHER])) {
            if (isset($type->publisher)) {
                $type->publisher->setValue((string)$attributes[self::FIELD_PUBLISHER]);
            } else {
                $type->setPublisher((string)$attributes[self::FIELD_PUBLISHER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PUBLISHER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PURPOSE])) {
            if (isset($type->purpose)) {
                $type->purpose->setValue((string)$attributes[self::FIELD_PURPOSE]);
            } else {
                $type->setPurpose((string)$attributes[self::FIELD_PURPOSE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PURPOSE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COPYRIGHT])) {
            if (isset($type->copyright)) {
                $type->copyright->setValue((string)$attributes[self::FIELD_COPYRIGHT]);
            } else {
                $type->setCopyright((string)$attributes[self::FIELD_COPYRIGHT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COPYRIGHT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_BASE])) {
            if (isset($type->base)) {
                $type->base->setValue((string)$attributes[self::FIELD_BASE]);
            } else {
                $type->setBase((string)$attributes[self::FIELD_BASE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_BASE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EVENT_URI])) {
            if (isset($type->eventUri)) {
                $type->eventUri->setValue((string)$attributes[self::FIELD_EVENT_URI]);
            } else {
                $type->setEventUri((string)$attributes[self::FIELD_EVENT_URI]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EVENT_URI, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CATEGORY])) {
            if (isset($type->category)) {
                $type->category->setValue((string)$attributes[self::FIELD_CATEGORY]);
            } else {
                $type->setCategory((string)$attributes[self::FIELD_CATEGORY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CATEGORY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RESPONSE_REQUIRED])) {
            if (isset($type->responseRequired)) {
                $type->responseRequired->setValue((string)$attributes[self::FIELD_RESPONSE_REQUIRED]);
            } else {
                $type->setResponseRequired((string)$attributes[self::FIELD_RESPONSE_REQUIRED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RESPONSE_REQUIRED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('MessageDefinition', $this->_getSourceXMLNS());
        }
        if (isset($this->url) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_URL]) {
            $xw->writeAttribute(self::FIELD_URL, $this->url->_getValueAsString());
        }
        if (isset($this->version) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VERSION]) {
            $xw->writeAttribute(self::FIELD_VERSION, $this->version->_getValueAsString());
        }
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->title) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TITLE]) {
            $xw->writeAttribute(self::FIELD_TITLE, $this->title->_getValueAsString());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->experimental) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EXPERIMENTAL]) {
            $xw->writeAttribute(self::FIELD_EXPERIMENTAL, $this->experimental->_getValueAsString());
        }
        if (isset($this->date) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DATE]) {
            $xw->writeAttribute(self::FIELD_DATE, $this->date->_getValueAsString());
        }
        if (isset($this->publisher) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PUBLISHER]) {
            $xw->writeAttribute(self::FIELD_PUBLISHER, $this->publisher->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->purpose) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PURPOSE]) {
            $xw->writeAttribute(self::FIELD_PURPOSE, $this->purpose->_getValueAsString());
        }
        if (isset($this->copyright) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COPYRIGHT]) {
            $xw->writeAttribute(self::FIELD_COPYRIGHT, $this->copyright->_getValueAsString());
        }
        if (isset($this->base) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_BASE]) {
            $xw->writeAttribute(self::FIELD_BASE, $this->base->_getValueAsString());
        }
        if (isset($this->eventUri) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EVENT_URI]) {
            $xw->writeAttribute(self::FIELD_EVENT_URI, $this->eventUri->_getValueAsString());
        }
        if (isset($this->category) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CATEGORY]) {
            $xw->writeAttribute(self::FIELD_CATEGORY, $this->category->_getValueAsString());
        }
        if (isset($this->responseRequired) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RESPONSE_REQUIRED]) {
            $xw->writeAttribute(self::FIELD_RESPONSE_REQUIRED, $this->responseRequired->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->url)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_URL]
                || $this->url->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_URL);
            $this->url->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_URL]);
            $xw->endElement();
        }
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->version)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VERSION]
                || $this->version->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VERSION);
            $this->version->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VERSION]);
            $xw->endElement();
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
        if (isset($this->replaces) && [] !== $this->replaces) {
            foreach($this->replaces as $v) {
                $xw->startElement(self::FIELD_REPLACES);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->experimental)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EXPERIMENTAL]
                || $this->experimental->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EXPERIMENTAL);
            $this->experimental->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EXPERIMENTAL]);
            $xw->endElement();
        }
        if (isset($this->date)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DATE]
                || $this->date->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DATE);
            $this->date->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DATE]);
            $xw->endElement();
        }
        if (isset($this->publisher)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PUBLISHER]
                || $this->publisher->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PUBLISHER);
            $this->publisher->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PUBLISHER]);
            $xw->endElement();
        }
        if (isset($this->contact)) {
            foreach ($this->contact as $v) {
                $xw->startElement(self::FIELD_CONTACT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->useContext)) {
            foreach ($this->useContext as $v) {
                $xw->startElement(self::FIELD_USE_CONTEXT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->jurisdiction)) {
            foreach ($this->jurisdiction as $v) {
                $xw->startElement(self::FIELD_JURISDICTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->purpose)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PURPOSE]
                || $this->purpose->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PURPOSE);
            $this->purpose->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PURPOSE]);
            $xw->endElement();
        }
        if (isset($this->copyright)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COPYRIGHT]
                || $this->copyright->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COPYRIGHT);
            $this->copyright->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COPYRIGHT]);
            $xw->endElement();
        }
        if (isset($this->base)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_BASE]
                || $this->base->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_BASE);
            $this->base->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_BASE]);
            $xw->endElement();
        }
        if (isset($this->parent) && [] !== $this->parent) {
            foreach($this->parent as $v) {
                $xw->startElement(self::FIELD_PARENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->eventCoding)) {
            $xw->startElement(self::FIELD_EVENT_CODING);
            $this->eventCoding->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->eventUri)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EVENT_URI]
                || $this->eventUri->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EVENT_URI);
            $this->eventUri->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EVENT_URI]);
            $xw->endElement();
        }
        if (isset($this->category)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CATEGORY]
                || $this->category->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CATEGORY);
            $this->category->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CATEGORY]);
            $xw->endElement();
        }
        if (isset($this->focus)) {
            foreach ($this->focus as $v) {
                $xw->startElement(self::FIELD_FOCUS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->responseRequired)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RESPONSE_REQUIRED]
                || $this->responseRequired->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RESPONSE_REQUIRED);
            $this->responseRequired->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RESPONSE_REQUIRED]);
            $xw->endElement();
        }
        if (isset($this->allowedResponse)) {
            foreach ($this->allowedResponse as $v) {
                $xw->startElement(self::FIELD_ALLOWED_RESPONSE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->graph) && [] !== $this->graph) {
            foreach($this->graph as $v) {
                $xw->startElement(self::FIELD_GRAPH);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMessageDefinition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMessageDefinition
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
        } else if (!($type instanceof FHIRMessageDefinition)) {
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
        if (isset($decoded->url)
            || isset($decoded->_url)
            || property_exists($decoded, self::FIELD_URL)
            || property_exists($decoded, self::FIELD_URL_EXT)) {
            $v = $decoded->_url ?? new \stdClass();
            $v->value = $decoded->url ?? null;
            $type->setUrl(FHIRUri::jsonUnserialize($v, $config));
        }
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
        if (isset($decoded->version)
            || isset($decoded->_version)
            || property_exists($decoded, self::FIELD_VERSION)
            || property_exists($decoded, self::FIELD_VERSION_EXT)) {
            $v = $decoded->_version ?? new \stdClass();
            $v->value = $decoded->version ?? null;
            $type->setVersion(FHIRString::jsonUnserialize($v, $config));
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
        if (isset($decoded->replaces)
            || isset($decoded->_replaces)
            || property_exists($decoded, self::FIELD_REPLACES)
            || property_exists($decoded, self::FIELD_REPLACES_EXT)) {
            $vals = (array)($decoded->replaces ?? []);
            $exts = (array)($decoded->_replaces ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addReplaces(FHIRCanonical::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRPublicationStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->experimental)
            || isset($decoded->_experimental)
            || property_exists($decoded, self::FIELD_EXPERIMENTAL)
            || property_exists($decoded, self::FIELD_EXPERIMENTAL_EXT)) {
            $v = $decoded->_experimental ?? new \stdClass();
            $v->value = $decoded->experimental ?? null;
            $type->setExperimental(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->date)
            || isset($decoded->_date)
            || property_exists($decoded, self::FIELD_DATE)
            || property_exists($decoded, self::FIELD_DATE_EXT)) {
            $v = $decoded->_date ?? new \stdClass();
            $v->value = $decoded->date ?? null;
            $type->setDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->publisher)
            || isset($decoded->_publisher)
            || property_exists($decoded, self::FIELD_PUBLISHER)
            || property_exists($decoded, self::FIELD_PUBLISHER_EXT)) {
            $v = $decoded->_publisher ?? new \stdClass();
            $v->value = $decoded->publisher ?? null;
            $type->setPublisher(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->contact) || property_exists($decoded, self::FIELD_CONTACT)) {
            if (is_object($decoded->contact)) {
                $vals = [$decoded->contact];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTACT, true);
            } else {
                $vals = $decoded->contact;
            }
            foreach($vals as $v) {
                $type->addContact(FHIRContactDetail::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->useContext) || property_exists($decoded, self::FIELD_USE_CONTEXT)) {
            if (is_object($decoded->useContext)) {
                $vals = [$decoded->useContext];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_USE_CONTEXT, true);
            } else {
                $vals = $decoded->useContext;
            }
            foreach($vals as $v) {
                $type->addUseContext(FHIRUsageContext::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->jurisdiction) || property_exists($decoded, self::FIELD_JURISDICTION)) {
            if (is_object($decoded->jurisdiction)) {
                $vals = [$decoded->jurisdiction];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_JURISDICTION, true);
            } else {
                $vals = $decoded->jurisdiction;
            }
            foreach($vals as $v) {
                $type->addJurisdiction(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->purpose)
            || isset($decoded->_purpose)
            || property_exists($decoded, self::FIELD_PURPOSE)
            || property_exists($decoded, self::FIELD_PURPOSE_EXT)) {
            $v = $decoded->_purpose ?? new \stdClass();
            $v->value = $decoded->purpose ?? null;
            $type->setPurpose(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->copyright)
            || isset($decoded->_copyright)
            || property_exists($decoded, self::FIELD_COPYRIGHT)
            || property_exists($decoded, self::FIELD_COPYRIGHT_EXT)) {
            $v = $decoded->_copyright ?? new \stdClass();
            $v->value = $decoded->copyright ?? null;
            $type->setCopyright(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->base)
            || isset($decoded->_base)
            || property_exists($decoded, self::FIELD_BASE)
            || property_exists($decoded, self::FIELD_BASE_EXT)) {
            $v = $decoded->_base ?? new \stdClass();
            $v->value = $decoded->base ?? null;
            $type->setBase(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->parent)
            || isset($decoded->_parent)
            || property_exists($decoded, self::FIELD_PARENT)
            || property_exists($decoded, self::FIELD_PARENT_EXT)) {
            $vals = (array)($decoded->parent ?? []);
            $exts = (array)($decoded->_parent ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addParent(FHIRCanonical::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->eventCoding) || property_exists($decoded, self::FIELD_EVENT_CODING)) {
            if (is_array($decoded->eventCoding)) {
                $type->setEventCoding(FHIRCoding::jsonUnserialize(reset($decoded->eventCoding), $config));
            } else {
                $type->setEventCoding(FHIRCoding::jsonUnserialize($decoded->eventCoding, $config));
            }
        }
        if (isset($decoded->eventUri)
            || isset($decoded->_eventUri)
            || property_exists($decoded, self::FIELD_EVENT_URI)
            || property_exists($decoded, self::FIELD_EVENT_URI_EXT)) {
            $v = $decoded->_eventUri ?? new \stdClass();
            $v->value = $decoded->eventUri ?? null;
            $type->setEventUri(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->category)
            || isset($decoded->_category)
            || property_exists($decoded, self::FIELD_CATEGORY)
            || property_exists($decoded, self::FIELD_CATEGORY_EXT)) {
            $v = $decoded->_category ?? new \stdClass();
            $v->value = $decoded->category ?? null;
            $type->setCategory(FHIRMessageSignificanceCategory::jsonUnserialize($v, $config));
        }
        if (isset($decoded->focus) || property_exists($decoded, self::FIELD_FOCUS)) {
            if (is_object($decoded->focus)) {
                $vals = [$decoded->focus];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_FOCUS, true);
            } else {
                $vals = $decoded->focus;
            }
            foreach($vals as $v) {
                $type->addFocus(FHIRMessageDefinitionFocus::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->responseRequired)
            || isset($decoded->_responseRequired)
            || property_exists($decoded, self::FIELD_RESPONSE_REQUIRED)
            || property_exists($decoded, self::FIELD_RESPONSE_REQUIRED_EXT)) {
            $v = $decoded->_responseRequired ?? new \stdClass();
            $v->value = $decoded->responseRequired ?? null;
            $type->setResponseRequired(FHIRMessageheaderResponseRequest::jsonUnserialize($v, $config));
        }
        if (isset($decoded->allowedResponse) || property_exists($decoded, self::FIELD_ALLOWED_RESPONSE)) {
            if (is_object($decoded->allowedResponse)) {
                $vals = [$decoded->allowedResponse];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ALLOWED_RESPONSE, true);
            } else {
                $vals = $decoded->allowedResponse;
            }
            foreach($vals as $v) {
                $type->addAllowedResponse(FHIRMessageDefinitionAllowedResponse::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->graph)
            || isset($decoded->_graph)
            || property_exists($decoded, self::FIELD_GRAPH)
            || property_exists($decoded, self::FIELD_GRAPH_EXT)) {
            $vals = (array)($decoded->graph ?? []);
            $exts = (array)($decoded->_graph ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addGraph(FHIRCanonical::jsonUnserialize($v, $config));
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
        if (isset($this->identifier) && [] !== $this->identifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER) && 1 === count($this->identifier)) {
                $out->identifier = $this->identifier[0];
            } else {
                $out->identifier = $this->identifier;
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
        if (isset($this->replaces) && [] !== $this->replaces) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->replaces as $v) {
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
                $out->replaces = $vals;
            }
            if ($hasExts) {
                $out->_replaces = $exts;
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
        if (isset($this->experimental)) {
            if (null !== ($val = $this->experimental->getValue())) {
                $out->experimental = $val;
            }
            if ($this->experimental->_nonValueFieldDefined()) {
                $ext = $this->experimental->jsonSerialize();
                unset($ext->value);
                $out->_experimental = $ext;
            }
        }
        if (isset($this->date)) {
            if (null !== ($val = $this->date->getValue())) {
                $out->date = $val;
            }
            if ($this->date->_nonValueFieldDefined()) {
                $ext = $this->date->jsonSerialize();
                unset($ext->value);
                $out->_date = $ext;
            }
        }
        if (isset($this->publisher)) {
            if (null !== ($val = $this->publisher->getValue())) {
                $out->publisher = $val;
            }
            if ($this->publisher->_nonValueFieldDefined()) {
                $ext = $this->publisher->jsonSerialize();
                unset($ext->value);
                $out->_publisher = $ext;
            }
        }
        if (isset($this->contact) && [] !== $this->contact) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTACT) && 1 === count($this->contact)) {
                $out->contact = $this->contact[0];
            } else {
                $out->contact = $this->contact;
            }
        }
        if (isset($this->description)) {
            if (null !== ($val = $this->description->getValue())) {
                $out->description = $val;
            }
            if ($this->description->_nonValueFieldDefined()) {
                $ext = $this->description->jsonSerialize();
                unset($ext->value);
                $out->_description = $ext;
            }
        }
        if (isset($this->useContext) && [] !== $this->useContext) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_USE_CONTEXT) && 1 === count($this->useContext)) {
                $out->useContext = $this->useContext[0];
            } else {
                $out->useContext = $this->useContext;
            }
        }
        if (isset($this->jurisdiction) && [] !== $this->jurisdiction) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_JURISDICTION) && 1 === count($this->jurisdiction)) {
                $out->jurisdiction = $this->jurisdiction[0];
            } else {
                $out->jurisdiction = $this->jurisdiction;
            }
        }
        if (isset($this->purpose)) {
            if (null !== ($val = $this->purpose->getValue())) {
                $out->purpose = $val;
            }
            if ($this->purpose->_nonValueFieldDefined()) {
                $ext = $this->purpose->jsonSerialize();
                unset($ext->value);
                $out->_purpose = $ext;
            }
        }
        if (isset($this->copyright)) {
            if (null !== ($val = $this->copyright->getValue())) {
                $out->copyright = $val;
            }
            if ($this->copyright->_nonValueFieldDefined()) {
                $ext = $this->copyright->jsonSerialize();
                unset($ext->value);
                $out->_copyright = $ext;
            }
        }
        if (isset($this->base)) {
            if (null !== ($val = $this->base->getValue())) {
                $out->base = $val;
            }
            if ($this->base->_nonValueFieldDefined()) {
                $ext = $this->base->jsonSerialize();
                unset($ext->value);
                $out->_base = $ext;
            }
        }
        if (isset($this->parent) && [] !== $this->parent) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->parent as $v) {
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
                $out->parent = $vals;
            }
            if ($hasExts) {
                $out->_parent = $exts;
            }
        }
        if (isset($this->eventCoding)) {
            $out->eventCoding = $this->eventCoding;
        }
        if (isset($this->eventUri)) {
            if (null !== ($val = $this->eventUri->getValue())) {
                $out->eventUri = $val;
            }
            if ($this->eventUri->_nonValueFieldDefined()) {
                $ext = $this->eventUri->jsonSerialize();
                unset($ext->value);
                $out->_eventUri = $ext;
            }
        }
        if (isset($this->category)) {
            if (null !== ($val = $this->category->getValue())) {
                $out->category = $val;
            }
            if ($this->category->_nonValueFieldDefined()) {
                $ext = $this->category->jsonSerialize();
                unset($ext->value);
                $out->_category = $ext;
            }
        }
        if (isset($this->focus) && [] !== $this->focus) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_FOCUS) && 1 === count($this->focus)) {
                $out->focus = $this->focus[0];
            } else {
                $out->focus = $this->focus;
            }
        }
        if (isset($this->responseRequired)) {
            if (null !== ($val = $this->responseRequired->getValue())) {
                $out->responseRequired = $val;
            }
            if ($this->responseRequired->_nonValueFieldDefined()) {
                $ext = $this->responseRequired->jsonSerialize();
                unset($ext->value);
                $out->_responseRequired = $ext;
            }
        }
        if (isset($this->allowedResponse) && [] !== $this->allowedResponse) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ALLOWED_RESPONSE) && 1 === count($this->allowedResponse)) {
                $out->allowedResponse = $this->allowedResponse[0];
            } else {
                $out->allowedResponse = $this->allowedResponse;
            }
        }
        if (isset($this->graph) && [] !== $this->graph) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->graph as $v) {
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
                $out->graph = $vals;
            }
            if ($hasExts) {
                $out->_graph = $exts;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
