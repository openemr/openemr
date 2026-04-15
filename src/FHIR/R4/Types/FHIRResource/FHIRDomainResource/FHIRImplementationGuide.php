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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFHIRVersionList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSPDXLicenseList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDependsOn;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideGlobal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideManifest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSPDXLicense;
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
 * A set of rules of how a particular interoperability or standards problem is
 * solved - typically through the use of FHIR resources. This resource is used to
 * gather all the parts of an implementation guide into a logical whole and to
 * publish a computable definition of all the parts.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRImplementationGuide extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_IMPLEMENTATION_GUIDE;

    /* class_default.php:56 */
    public const FIELD_URL = 'url';
    public const FIELD_URL_EXT = '_url';
    public const FIELD_VERSION = 'version';
    public const FIELD_VERSION_EXT = '_version';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_TITLE = 'title';
    public const FIELD_TITLE_EXT = '_title';
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
    public const FIELD_COPYRIGHT = 'copyright';
    public const FIELD_COPYRIGHT_EXT = '_copyright';
    public const FIELD_PACKAGE_ID = 'packageId';
    public const FIELD_PACKAGE_ID_EXT = '_packageId';
    public const FIELD_LICENSE = 'license';
    public const FIELD_LICENSE_EXT = '_license';
    public const FIELD_FHIR_VERSION = 'fhirVersion';
    public const FIELD_FHIR_VERSION_EXT = '_fhirVersion';
    public const FIELD_DEPENDS_ON = 'dependsOn';
    public const FIELD_GLOBAL = 'global';
    public const FIELD_DEFINITION = 'definition';
    public const FIELD_MANIFEST = 'manifest';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_URL => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_NAME => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_PACKAGE_ID => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_FHIR_VERSION => [
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
        self::FIELD_COPYRIGHT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PACKAGE_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LICENSE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An absolute URI that is used to identify this implementation guide when it is
     * referenced in a specification, model, design or an instance; also called its
     * canonical identifier. This SHOULD be globally unique and SHOULD be a literal
     * address at which at which an authoritative instance of this implementation guide
     * is (or will be) published. This URL can be the target of a canonical reference.
     * It SHALL remain the same when the implementation guide is stored on different
     * servers.
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
     * The identifier that is used to identify this version of the implementation guide
     * when it is referenced in a specification, model, design or instance. This is an
     * arbitrary value managed by the implementation guide author and is not expected
     * to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a
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
     * A natural language name identifying the implementation guide. This name should
     * be usable as an identifier for the module by machine processing applications
     * such as code generation.
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
     * A short, descriptive, user-friendly title for the implementation guide.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $title;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of this implementation guide. Enables tracking the life-cycle of the
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
     * A Boolean value to indicate that this implementation guide is authored for
     * testing purposes (or education/evaluation/marketing) and is not intended to be
     * used for genuine usage.
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
     * The date (and optionally time) when the implementation guide was published. The
     * date must change when the business version changes and it must change if the
     * status code changes. In addition, it should change when the substantive content
     * of the implementation guide changes.
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
     * The name of the organization or individual that published the implementation
     * guide.
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
     * A free text natural language description of the implementation guide from a
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
     * be used to assist with indexing and searching for appropriate implementation
     * guide instances.
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
     * A legal or geographic region in which the implementation guide is intended to be
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
     * A copyright statement relating to the implementation guide and/or its contents.
     * Copyright statements are generally legal restrictions on the use and publishing
     * of the implementation guide.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $copyright;
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The NPM package name for this Implementation Guide, used in the NPM package
     * distribution, which is the primary mechanism by which FHIR based tooling manages
     * IG dependencies. This value must be globally unique, and should be assigned with
     * care.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $packageId;
    /**
     * The license that applies to an Implementation Guide (using an SPDX license
     * Identifiers, or 'not-open-source'). The binding is required but new SPDX license
     * Identifiers are allowed to be used (https://spdx.org/licenses/).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The license that applies to this Implementation Guide, using an SPDX license
     * code, or 'not-open-source'.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSPDXLicense
     */
    #[FHIRSPDXLicense]
    protected FHIRSPDXLicense $license;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version(s) of the FHIR specification that this ImplementationGuide targets -
     * e.g. describes how to use. The value of this element is the formal version of
     * the specification, without the revision number, e.g.
     * [publication].[major].[minor], which is 4.0.1. for this version.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion>
     */
    #[FHIRFHIRVersion]
    protected array $fhirVersion;
    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Another implementation guide that this implementation depends on. Typically, an
     * implementation guide uses value sets, profiles etc.defined in other
     * implementation guides.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDependsOn>
     */
    #[FHIRImplementationGuideDependsOn]
    protected array $dependsOn;
    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * A set of profiles that all resources covered by this implementation guide must
     * conform to.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideGlobal>
     */
    #[FHIRImplementationGuideGlobal]
    protected array $global;
    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * The information needed by an IG publisher tool to publish the whole
     * implementation guide.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDefinition
     */
    #[FHIRImplementationGuideDefinition]
    protected FHIRImplementationGuideDefinition $definition;
    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Information about an assembled implementation guide, created by the publication
     * tooling.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideManifest
     */
    #[FHIRImplementationGuideManifest]
    protected FHIRImplementationGuideManifest $manifest;

    /* constructor.php:61 */
    /**
     * FHIRImplementationGuide Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $url
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $version
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $title
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus $status
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $experimental
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $date
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $publisher
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail> $contact
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $description
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext> $useContext
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $jurisdiction
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $copyright
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $packageId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSPDXLicenseList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSPDXLicense $license
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFHIRVersionList>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion> $fhirVersion
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDependsOn> $dependsOn
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideGlobal> $global
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDefinition $definition
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideManifest $manifest
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
                                null|string|FHIRStringPrimitive|FHIRString $version = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|string|FHIRStringPrimitive|FHIRString $title = null,
                                null|string|FHIRPublicationStatusList|FHIRPublicationStatus $status = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $experimental = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $date = null,
                                null|string|FHIRStringPrimitive|FHIRString $publisher = null,
                                null|iterable $contact = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $description = null,
                                null|iterable $useContext = null,
                                null|iterable $jurisdiction = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $copyright = null,
                                null|string|FHIRIdPrimitive|FHIRId $packageId = null,
                                null|string|FHIRSPDXLicenseList|FHIRSPDXLicense $license = null,
                                null|iterable $fhirVersion = null,
                                null|iterable $dependsOn = null,
                                null|iterable $global = null,
                                null|FHIRImplementationGuideDefinition $definition = null,
                                null|FHIRImplementationGuideManifest $manifest = null,
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
        if (null !== $version) {
            $this->setVersion($version);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $title) {
            $this->setTitle($title);
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
        if (null !== $copyright) {
            $this->setCopyright($copyright);
        }
        if (null !== $packageId) {
            $this->setPackageId($packageId);
        }
        if (null !== $license) {
            $this->setLicense($license);
        }
        if (null !== $fhirVersion) {
            $this->setFhirVersion(...$fhirVersion);
        }
        if (null !== $dependsOn) {
            $this->setDependsOn(...$dependsOn);
        }
        if (null !== $global) {
            $this->setGlobal(...$global);
        }
        if (null !== $definition) {
            $this->setDefinition($definition);
        }
        if (null !== $manifest) {
            $this->setManifest($manifest);
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
     * An absolute URI that is used to identify this implementation guide when it is
     * referenced in a specification, model, design or an instance; also called its
     * canonical identifier. This SHOULD be globally unique and SHOULD be a literal
     * address at which at which an authoritative instance of this implementation guide
     * is (or will be) published. This URL can be the target of a canonical reference.
     * It SHALL remain the same when the implementation guide is stored on different
     * servers.
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
     * An absolute URI that is used to identify this implementation guide when it is
     * referenced in a specification, model, design or an instance; also called its
     * canonical identifier. This SHOULD be globally unique and SHOULD be a literal
     * address at which at which an authoritative instance of this implementation guide
     * is (or will be) published. This URL can be the target of a canonical reference.
     * It SHALL remain the same when the implementation guide is stored on different
     * servers.
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
     * The identifier that is used to identify this version of the implementation guide
     * when it is referenced in a specification, model, design or instance. This is an
     * arbitrary value managed by the implementation guide author and is not expected
     * to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a
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
     * The identifier that is used to identify this version of the implementation guide
     * when it is referenced in a specification, model, design or instance. This is an
     * arbitrary value managed by the implementation guide author and is not expected
     * to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a
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
     * A natural language name identifying the implementation guide. This name should
     * be usable as an identifier for the module by machine processing applications
     * such as code generation.
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
     * A natural language name identifying the implementation guide. This name should
     * be usable as an identifier for the module by machine processing applications
     * such as code generation.
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
     * A short, descriptive, user-friendly title for the implementation guide.
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
     * A short, descriptive, user-friendly title for the implementation guide.
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
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of this implementation guide. Enables tracking the life-cycle of the
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
     * The status of this implementation guide. Enables tracking the life-cycle of the
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
     * A Boolean value to indicate that this implementation guide is authored for
     * testing purposes (or education/evaluation/marketing) and is not intended to be
     * used for genuine usage.
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
     * A Boolean value to indicate that this implementation guide is authored for
     * testing purposes (or education/evaluation/marketing) and is not intended to be
     * used for genuine usage.
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
     * The date (and optionally time) when the implementation guide was published. The
     * date must change when the business version changes and it must change if the
     * status code changes. In addition, it should change when the substantive content
     * of the implementation guide changes.
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
     * The date (and optionally time) when the implementation guide was published. The
     * date must change when the business version changes and it must change if the
     * status code changes. In addition, it should change when the substantive content
     * of the implementation guide changes.
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
     * The name of the organization or individual that published the implementation
     * guide.
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
     * The name of the organization or individual that published the implementation
     * guide.
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
     * A free text natural language description of the implementation guide from a
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
     * A free text natural language description of the implementation guide from a
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
     * be used to assist with indexing and searching for appropriate implementation
     * guide instances.
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
     * be used to assist with indexing and searching for appropriate implementation
     * guide instances.
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
     * be used to assist with indexing and searching for appropriate implementation
     * guide instances.
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
     * A legal or geographic region in which the implementation guide is intended to be
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
     * A legal or geographic region in which the implementation guide is intended to be
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
     * A legal or geographic region in which the implementation guide is intended to be
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
     * A copyright statement relating to the implementation guide and/or its contents.
     * Copyright statements are generally legal restrictions on the use and publishing
     * of the implementation guide.
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
     * A copyright statement relating to the implementation guide and/or its contents.
     * Copyright statements are generally legal restrictions on the use and publishing
     * of the implementation guide.
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
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The NPM package name for this Implementation Guide, used in the NPM package
     * distribution, which is the primary mechanism by which FHIR based tooling manages
     * IG dependencies. This value must be globally unique, and should be assigned with
     * care.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getPackageId(): null|FHIRId
    {
        return $this->packageId ?? null;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The NPM package name for this Implementation Guide, used in the NPM package
     * distribution, which is the primary mechanism by which FHIR based tooling manages
     * IG dependencies. This value must be globally unique, and should be assigned with
     * care.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $packageId
     * @return static
     */
    public function setPackageId(null|string|FHIRIdPrimitive|FHIRId $packageId): self
    {
        if (null === $packageId) {
            unset($this->packageId);
            return $this;
        }
        if (!($packageId instanceof FHIRId)) {
            $packageId = new FHIRId(value: $packageId);
        }
        $this->packageId = $packageId;
        return $this;
    }

    /**
     * The license that applies to an Implementation Guide (using an SPDX license
     * Identifiers, or 'not-open-source'). The binding is required but new SPDX license
     * Identifiers are allowed to be used (https://spdx.org/licenses/).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The license that applies to this Implementation Guide, using an SPDX license
     * code, or 'not-open-source'.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSPDXLicense
     */
    public function getLicense(): null|FHIRSPDXLicense
    {
        return $this->license ?? null;
    }

    /**
     * The license that applies to an Implementation Guide (using an SPDX license
     * Identifiers, or 'not-open-source'). The binding is required but new SPDX license
     * Identifiers are allowed to be used (https://spdx.org/licenses/).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The license that applies to this Implementation Guide, using an SPDX license
     * code, or 'not-open-source'.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSPDXLicenseList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSPDXLicense $license
     * @return static
     */
    public function setLicense(null|string|FHIRSPDXLicenseList|FHIRSPDXLicense $license): self
    {
        if (null === $license) {
            unset($this->license);
            return $this;
        }
        if (!($license instanceof FHIRSPDXLicense)) {
            $license = new FHIRSPDXLicense(value: $license);
        }
        $this->license = $license;
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version(s) of the FHIR specification that this ImplementationGuide targets -
     * e.g. describes how to use. The value of this element is the formal version of
     * the specification, without the revision number, e.g.
     * [publication].[major].[minor], which is 4.0.1. for this version.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion>
     */
    public function getFhirVersion(): array
    {
        return $this->fhirVersion ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion>
     */
    public function getFhirVersionIterator(): iterable
    {
        if (!isset($this->fhirVersion)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->fhirVersion);
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version(s) of the FHIR specification that this ImplementationGuide targets -
     * e.g. describes how to use. The value of this element is the formal version of
     * the specification, without the revision number, e.g.
     * [publication].[major].[minor], which is 4.0.1. for this version.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFHIRVersionList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion $fhirVersion
     * @return static
     */
    public function addFhirVersion(string|FHIRFHIRVersionList|FHIRFHIRVersion $fhirVersion): self
    {
        if (!($fhirVersion instanceof FHIRFHIRVersion)) {
            $fhirVersion = new FHIRFHIRVersion(value: $fhirVersion);
        }
        if (!isset($this->fhirVersion)) {
            $this->fhirVersion = [];
        }
        $this->fhirVersion[] = $fhirVersion;
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version(s) of the FHIR specification that this ImplementationGuide targets -
     * e.g. describes how to use. The value of this element is the formal version of
     * the specification, without the revision number, e.g.
     * [publication].[major].[minor], which is 4.0.1. for this version.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFHIRVersionList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion ...$fhirVersion
     * @return static
     */
    public function setFhirVersion(string|FHIRFHIRVersionList|FHIRFHIRVersion ...$fhirVersion): self
    {
        if ([] === $fhirVersion) {
            unset($this->fhirVersion);
            return $this;
        }
        $this->fhirVersion = [];
        foreach($fhirVersion as $v) {
            if ($v instanceof FHIRFHIRVersion) {
                $this->fhirVersion[] = $v;
            } else {
                $this->fhirVersion[] = new FHIRFHIRVersion(value: $v);
            }
        }
        return $this;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Another implementation guide that this implementation depends on. Typically, an
     * implementation guide uses value sets, profiles etc.defined in other
     * implementation guides.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDependsOn>
     */
    public function getDependsOn(): array
    {
        return $this->dependsOn ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDependsOn>
     */
    public function getDependsOnIterator(): iterable
    {
        if (!isset($this->dependsOn)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->dependsOn);
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Another implementation guide that this implementation depends on. Typically, an
     * implementation guide uses value sets, profiles etc.defined in other
     * implementation guides.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDependsOn $dependsOn
     * @return static
     */
    public function addDependsOn(FHIRImplementationGuideDependsOn $dependsOn): self
    {
        if (!isset($this->dependsOn)) {
            $this->dependsOn = [];
        }
        $this->dependsOn[] = $dependsOn;
        return $this;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Another implementation guide that this implementation depends on. Typically, an
     * implementation guide uses value sets, profiles etc.defined in other
     * implementation guides.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDependsOn ...$dependsOn
     * @return static
     */
    public function setDependsOn(FHIRImplementationGuideDependsOn ...$dependsOn): self
    {
        if ([] === $dependsOn) {
            unset($this->dependsOn);
            return $this;
        }
        $this->dependsOn = $dependsOn;
        return $this;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * A set of profiles that all resources covered by this implementation guide must
     * conform to.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideGlobal>
     */
    public function getGlobal(): array
    {
        return $this->global ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideGlobal>
     */
    public function getGlobalIterator(): iterable
    {
        if (!isset($this->global)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->global);
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * A set of profiles that all resources covered by this implementation guide must
     * conform to.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideGlobal $global
     * @return static
     */
    public function addGlobal(FHIRImplementationGuideGlobal $global): self
    {
        if (!isset($this->global)) {
            $this->global = [];
        }
        $this->global[] = $global;
        return $this;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * A set of profiles that all resources covered by this implementation guide must
     * conform to.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideGlobal ...$global
     * @return static
     */
    public function setGlobal(FHIRImplementationGuideGlobal ...$global): self
    {
        if ([] === $global) {
            unset($this->global);
            return $this;
        }
        $this->global = $global;
        return $this;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * The information needed by an IG publisher tool to publish the whole
     * implementation guide.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDefinition
     */
    public function getDefinition(): null|FHIRImplementationGuideDefinition
    {
        return $this->definition ?? null;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * The information needed by an IG publisher tool to publish the whole
     * implementation guide.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideDefinition $definition
     * @return static
     */
    public function setDefinition(null|FHIRImplementationGuideDefinition $definition): self
    {
        if (null === $definition) {
            unset($this->definition);
            return $this;
        }
        $this->definition = $definition;
        return $this;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Information about an assembled implementation guide, created by the publication
     * tooling.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideManifest
     */
    public function getManifest(): null|FHIRImplementationGuideManifest
    {
        return $this->manifest ?? null;
    }

    /**
     * A set of rules of how a particular interoperability or standards problem is
     * solved - typically through the use of FHIR resources. This resource is used to
     * gather all the parts of an implementation guide into a logical whole and to
     * publish a computable definition of all the parts.
     *
     * Information about an assembled implementation guide, created by the publication
     * tooling.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideManifest $manifest
     * @return static
     */
    public function setManifest(null|FHIRImplementationGuideManifest $manifest): self
    {
        if (null === $manifest) {
            unset($this->manifest);
            return $this;
        }
        $this->manifest = $manifest;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImplementationGuide $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImplementationGuide
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRImplementationGuide)) {
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
            } else if (self::FIELD_VERSION === $cen) {
                $type->setVersion(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TITLE === $cen) {
                $type->setTitle(FHIRString::xmlUnserialize($ce, $config));
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
            } else if (self::FIELD_COPYRIGHT === $cen) {
                $type->setCopyright(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PACKAGE_ID === $cen) {
                $type->setPackageId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LICENSE === $cen) {
                $type->setLicense(FHIRSPDXLicense::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FHIR_VERSION === $cen) {
                $type->addFhirVersion(FHIRFHIRVersion::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEPENDS_ON === $cen) {
                $type->addDependsOn(FHIRImplementationGuideDependsOn::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GLOBAL === $cen) {
                $type->addGlobal(FHIRImplementationGuideGlobal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION === $cen) {
                $type->setDefinition(FHIRImplementationGuideDefinition::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANIFEST === $cen) {
                $type->setManifest(FHIRImplementationGuideManifest::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_COPYRIGHT])) {
            if (isset($type->copyright)) {
                $type->copyright->setValue((string)$attributes[self::FIELD_COPYRIGHT]);
            } else {
                $type->setCopyright((string)$attributes[self::FIELD_COPYRIGHT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COPYRIGHT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PACKAGE_ID])) {
            if (isset($type->packageId)) {
                $type->packageId->setValue((string)$attributes[self::FIELD_PACKAGE_ID]);
            } else {
                $type->setPackageId((string)$attributes[self::FIELD_PACKAGE_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PACKAGE_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LICENSE])) {
            if (isset($type->license)) {
                $type->license->setValue((string)$attributes[self::FIELD_LICENSE]);
            } else {
                $type->setLicense((string)$attributes[self::FIELD_LICENSE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LICENSE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('ImplementationGuide', $this->_getSourceXMLNS());
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
        if (isset($this->copyright) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COPYRIGHT]) {
            $xw->writeAttribute(self::FIELD_COPYRIGHT, $this->copyright->_getValueAsString());
        }
        if (isset($this->packageId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PACKAGE_ID]) {
            $xw->writeAttribute(self::FIELD_PACKAGE_ID, $this->packageId->_getValueAsString());
        }
        if (isset($this->license) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LICENSE]) {
            $xw->writeAttribute(self::FIELD_LICENSE, $this->license->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
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
        if (isset($this->copyright)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COPYRIGHT]
                || $this->copyright->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COPYRIGHT);
            $this->copyright->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COPYRIGHT]);
            $xw->endElement();
        }
        if (isset($this->packageId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PACKAGE_ID]
                || $this->packageId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PACKAGE_ID);
            $this->packageId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PACKAGE_ID]);
            $xw->endElement();
        }
        if (isset($this->license)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LICENSE]
                || $this->license->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LICENSE);
            $this->license->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LICENSE]);
            $xw->endElement();
        }
        if (isset($this->fhirVersion) && [] !== $this->fhirVersion) {
            foreach($this->fhirVersion as $v) {
                $xw->startElement(self::FIELD_FHIR_VERSION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->dependsOn)) {
            foreach ($this->dependsOn as $v) {
                $xw->startElement(self::FIELD_DEPENDS_ON);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->global)) {
            foreach ($this->global as $v) {
                $xw->startElement(self::FIELD_GLOBAL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->definition)) {
            $xw->startElement(self::FIELD_DEFINITION);
            $this->definition->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->manifest)) {
            $xw->startElement(self::FIELD_MANIFEST);
            $this->manifest->xmlSerialize($xw, $config);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImplementationGuide $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImplementationGuide
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
        } else if (!($type instanceof FHIRImplementationGuide)) {
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
        if (isset($decoded->copyright)
            || isset($decoded->_copyright)
            || property_exists($decoded, self::FIELD_COPYRIGHT)
            || property_exists($decoded, self::FIELD_COPYRIGHT_EXT)) {
            $v = $decoded->_copyright ?? new \stdClass();
            $v->value = $decoded->copyright ?? null;
            $type->setCopyright(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->packageId)
            || isset($decoded->_packageId)
            || property_exists($decoded, self::FIELD_PACKAGE_ID)
            || property_exists($decoded, self::FIELD_PACKAGE_ID_EXT)) {
            $v = $decoded->_packageId ?? new \stdClass();
            $v->value = $decoded->packageId ?? null;
            $type->setPackageId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->license)
            || isset($decoded->_license)
            || property_exists($decoded, self::FIELD_LICENSE)
            || property_exists($decoded, self::FIELD_LICENSE_EXT)) {
            $v = $decoded->_license ?? new \stdClass();
            $v->value = $decoded->license ?? null;
            $type->setLicense(FHIRSPDXLicense::jsonUnserialize($v, $config));
        }
        if (isset($decoded->fhirVersion)
            || isset($decoded->_fhirVersion)
            || property_exists($decoded, self::FIELD_FHIR_VERSION)
            || property_exists($decoded, self::FIELD_FHIR_VERSION_EXT)) {
            $vals = (array)($decoded->fhirVersion ?? []);
            $exts = (array)($decoded->_fhirVersion ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addFhirVersion(FHIRFHIRVersion::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->dependsOn) || property_exists($decoded, self::FIELD_DEPENDS_ON)) {
            if (is_object($decoded->dependsOn)) {
                $vals = [$decoded->dependsOn];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DEPENDS_ON, true);
            } else {
                $vals = $decoded->dependsOn;
            }
            foreach($vals as $v) {
                $type->addDependsOn(FHIRImplementationGuideDependsOn::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->global) || property_exists($decoded, self::FIELD_GLOBAL)) {
            if (is_object($decoded->global)) {
                $vals = [$decoded->global];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_GLOBAL, true);
            } else {
                $vals = $decoded->global;
            }
            foreach($vals as $v) {
                $type->addGlobal(FHIRImplementationGuideGlobal::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->definition) || property_exists($decoded, self::FIELD_DEFINITION)) {
            if (is_array($decoded->definition)) {
                $type->setDefinition(FHIRImplementationGuideDefinition::jsonUnserialize(reset($decoded->definition), $config));
            } else {
                $type->setDefinition(FHIRImplementationGuideDefinition::jsonUnserialize($decoded->definition, $config));
            }
        }
        if (isset($decoded->manifest) || property_exists($decoded, self::FIELD_MANIFEST)) {
            if (is_array($decoded->manifest)) {
                $type->setManifest(FHIRImplementationGuideManifest::jsonUnserialize(reset($decoded->manifest), $config));
            } else {
                $type->setManifest(FHIRImplementationGuideManifest::jsonUnserialize($decoded->manifest, $config));
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
        if (isset($this->packageId)) {
            if (null !== ($val = $this->packageId->getValue())) {
                $out->packageId = $val;
            }
            if ($this->packageId->_nonValueFieldDefined()) {
                $ext = $this->packageId->jsonSerialize();
                unset($ext->value);
                $out->_packageId = $ext;
            }
        }
        if (isset($this->license)) {
            if (null !== ($val = $this->license->getValue())) {
                $out->license = $val;
            }
            if ($this->license->_nonValueFieldDefined()) {
                $ext = $this->license->jsonSerialize();
                unset($ext->value);
                $out->_license = $ext;
            }
        }
        if (isset($this->fhirVersion) && [] !== $this->fhirVersion) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->fhirVersion as $v) {
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
                $out->fhirVersion = $vals;
            }
            if ($hasExts) {
                $out->_fhirVersion = $exts;
            }
        }
        if (isset($this->dependsOn) && [] !== $this->dependsOn) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DEPENDS_ON) && 1 === count($this->dependsOn)) {
                $out->dependsOn = $this->dependsOn[0];
            } else {
                $out->dependsOn = $this->dependsOn;
            }
        }
        if (isset($this->global) && [] !== $this->global) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_GLOBAL) && 1 === count($this->global)) {
                $out->global = $this->global[0];
            } else {
                $out->global = $this->global;
            }
        }
        if (isset($this->definition)) {
            $out->definition = $this->definition;
        }
        if (isset($this->manifest)) {
            $out->manifest = $this->manifest;
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
