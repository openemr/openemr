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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDocumentReferenceStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentManifest\FHIRDocumentManifestRelated;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDocumentReferenceStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
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
 * A collection of documents compiled for a purpose together with metadata that
 * applies to the collection.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRDocumentManifest extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_DOCUMENT_MANIFEST;

    /* class_default.php:56 */
    public const FIELD_MASTER_IDENTIFIER = 'masterIdentifier';
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_TYPE = 'type';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_CREATED = 'created';
    public const FIELD_CREATED_EXT = '_created';
    public const FIELD_AUTHOR = 'author';
    public const FIELD_RECIPIENT = 'recipient';
    public const FIELD_SOURCE = 'source';
    public const FIELD_SOURCE_EXT = '_source';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_CONTENT = 'content';
    public const FIELD_RELATED = 'related';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_CONTENT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CREATED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SOURCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A single identifier that uniquely identifies this manifest. Principally used to
     * refer to the manifest in non-FHIR contexts.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $masterIdentifier;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Other identifiers associated with the document manifest, including version
     * independent identifiers.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of this document manifest.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDocumentReferenceStatus
     */
    #[FHIRDocumentReferenceStatus]
    protected FHIRDocumentReferenceStatus $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The code specifying the type of clinical activity that resulted in placing the
     * associated content into the DocumentManifest.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Who or what the set of documents is about. The documents can be about a person,
     * (patient or healthcare practitioner), a device (i.e. machine) or even a group of
     * subjects (such as a document about a herd of farm animals, or a set of patients
     * that share a common exposure). If the documents cross more than one subject,
     * then more than one subject is allowed here (unusual use case).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $subject;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the document manifest was created for submission to the server (not
     * necessarily the same thing as the actual resource last modified time, since it
     * may be modified, replicated, etc.).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $created;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies who is the author of the manifest. Manifest author is not necessarly
     * the author of the references included.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $author;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A patient, practitioner, or organization for which this set of documents is
     * intended.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $recipient;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies the source system, application, or software that produced the
     * document manifest.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $source;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Human-readable description of the source document. This is sometimes known as
     * the "title".
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The list of Resources that consist of the parts of this manifest.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $content;
    /**
     * A collection of documents compiled for a purpose together with metadata that
     * applies to the collection.
     *
     * Related identifiers or resources associated with the DocumentManifest.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentManifest\FHIRDocumentManifestRelated>
     */
    #[FHIRDocumentManifestRelated]
    protected array $related;

    /* constructor.php:61 */
    /**
     * FHIRDocumentManifest Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $masterIdentifier
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDocumentReferenceStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDocumentReferenceStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $created
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $author
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $recipient
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $source
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $content
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentManifest\FHIRDocumentManifestRelated> $related
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
                                null|FHIRIdentifier $masterIdentifier = null,
                                null|iterable $identifier = null,
                                null|string|FHIRDocumentReferenceStatusList|FHIRDocumentReferenceStatus $status = null,
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRReference $subject = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $created = null,
                                null|iterable $author = null,
                                null|iterable $recipient = null,
                                null|string|FHIRUriPrimitive|FHIRUri $source = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|iterable $content = null,
                                null|iterable $related = null,
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
        if (null !== $masterIdentifier) {
            $this->setMasterIdentifier($masterIdentifier);
        }
        if (null !== $identifier) {
            $this->setIdentifier(...$identifier);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $created) {
            $this->setCreated($created);
        }
        if (null !== $author) {
            $this->setAuthor(...$author);
        }
        if (null !== $recipient) {
            $this->setRecipient(...$recipient);
        }
        if (null !== $source) {
            $this->setSource($source);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $content) {
            $this->setContent(...$content);
        }
        if (null !== $related) {
            $this->setRelated(...$related);
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
     * A single identifier that uniquely identifies this manifest. Principally used to
     * refer to the manifest in non-FHIR contexts.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getMasterIdentifier(): null|FHIRIdentifier
    {
        return $this->masterIdentifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A single identifier that uniquely identifies this manifest. Principally used to
     * refer to the manifest in non-FHIR contexts.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $masterIdentifier
     * @return static
     */
    public function setMasterIdentifier(null|FHIRIdentifier $masterIdentifier): self
    {
        if (null === $masterIdentifier) {
            unset($this->masterIdentifier);
            return $this;
        }
        $this->masterIdentifier = $masterIdentifier;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Other identifiers associated with the document manifest, including version
     * independent identifiers.
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
     * Other identifiers associated with the document manifest, including version
     * independent identifiers.
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
     * Other identifiers associated with the document manifest, including version
     * independent identifiers.
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
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of this document manifest.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDocumentReferenceStatus
     */
    public function getStatus(): null|FHIRDocumentReferenceStatus
    {
        return $this->status ?? null;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of this document manifest.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDocumentReferenceStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDocumentReferenceStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRDocumentReferenceStatusList|FHIRDocumentReferenceStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRDocumentReferenceStatus)) {
            $status = new FHIRDocumentReferenceStatus(value: $status);
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
     * The code specifying the type of clinical activity that resulted in placing the
     * associated content into the DocumentManifest.
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
     * The code specifying the type of clinical activity that resulted in placing the
     * associated content into the DocumentManifest.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Who or what the set of documents is about. The documents can be about a person,
     * (patient or healthcare practitioner), a device (i.e. machine) or even a group of
     * subjects (such as a document about a herd of farm animals, or a set of patients
     * that share a common exposure). If the documents cross more than one subject,
     * then more than one subject is allowed here (unusual use case).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSubject(): null|FHIRReference
    {
        return $this->subject ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Who or what the set of documents is about. The documents can be about a person,
     * (patient or healthcare practitioner), a device (i.e. machine) or even a group of
     * subjects (such as a document about a herd of farm animals, or a set of patients
     * that share a common exposure). If the documents cross more than one subject,
     * then more than one subject is allowed here (unusual use case).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @return static
     */
    public function setSubject(null|FHIRReference $subject): self
    {
        if (null === $subject) {
            unset($this->subject);
            return $this;
        }
        $this->subject = $subject;
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
     * When the document manifest was created for submission to the server (not
     * necessarily the same thing as the actual resource last modified time, since it
     * may be modified, replicated, etc.).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getCreated(): null|FHIRDateTime
    {
        return $this->created ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the document manifest was created for submission to the server (not
     * necessarily the same thing as the actual resource last modified time, since it
     * may be modified, replicated, etc.).
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $created
     * @return static
     */
    public function setCreated(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $created): self
    {
        if (null === $created) {
            unset($this->created);
            return $this;
        }
        if (!($created instanceof FHIRDateTime)) {
            $created = new FHIRDateTime(value: $created);
        }
        $this->created = $created;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies who is the author of the manifest. Manifest author is not necessarly
     * the author of the references included.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAuthor(): array
    {
        return $this->author ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAuthorIterator(): iterable
    {
        if (!isset($this->author)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->author);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies who is the author of the manifest. Manifest author is not necessarly
     * the author of the references included.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $author
     * @return static
     */
    public function addAuthor(FHIRReference $author): self
    {
        if (!isset($this->author)) {
            $this->author = [];
        }
        $this->author[] = $author;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies who is the author of the manifest. Manifest author is not necessarly
     * the author of the references included.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$author
     * @return static
     */
    public function setAuthor(FHIRReference ...$author): self
    {
        if ([] === $author) {
            unset($this->author);
            return $this;
        }
        $this->author = $author;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A patient, practitioner, or organization for which this set of documents is
     * intended.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRecipient(): array
    {
        return $this->recipient ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRecipientIterator(): iterable
    {
        if (!isset($this->recipient)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->recipient);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A patient, practitioner, or organization for which this set of documents is
     * intended.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $recipient
     * @return static
     */
    public function addRecipient(FHIRReference $recipient): self
    {
        if (!isset($this->recipient)) {
            $this->recipient = [];
        }
        $this->recipient[] = $recipient;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A patient, practitioner, or organization for which this set of documents is
     * intended.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$recipient
     * @return static
     */
    public function setRecipient(FHIRReference ...$recipient): self
    {
        if ([] === $recipient) {
            unset($this->recipient);
            return $this;
        }
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies the source system, application, or software that produced the
     * document manifest.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getSource(): null|FHIRUri
    {
        return $this->source ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies the source system, application, or software that produced the
     * document manifest.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $source
     * @return static
     */
    public function setSource(null|string|FHIRUriPrimitive|FHIRUri $source): self
    {
        if (null === $source) {
            unset($this->source);
            return $this;
        }
        if (!($source instanceof FHIRUri)) {
            $source = new FHIRUri(value: $source);
        }
        $this->source = $source;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Human-readable description of the source document. This is sometimes known as
     * the "title".
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDescription(): null|FHIRString
    {
        return $this->description ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Human-readable description of the source document. This is sometimes known as
     * the "title".
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @return static
     */
    public function setDescription(null|string|FHIRStringPrimitive|FHIRString $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRString)) {
            $description = new FHIRString(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The list of Resources that consist of the parts of this manifest.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getContent(): array
    {
        return $this->content ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getContentIterator(): iterable
    {
        if (!isset($this->content)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->content);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The list of Resources that consist of the parts of this manifest.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $content
     * @return static
     */
    public function addContent(FHIRReference $content): self
    {
        if (!isset($this->content)) {
            $this->content = [];
        }
        $this->content[] = $content;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The list of Resources that consist of the parts of this manifest.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$content
     * @return static
     */
    public function setContent(FHIRReference ...$content): self
    {
        if ([] === $content) {
            unset($this->content);
            return $this;
        }
        $this->content = $content;
        return $this;
    }

    /**
     * A collection of documents compiled for a purpose together with metadata that
     * applies to the collection.
     *
     * Related identifiers or resources associated with the DocumentManifest.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentManifest\FHIRDocumentManifestRelated>
     */
    public function getRelated(): array
    {
        return $this->related ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentManifest\FHIRDocumentManifestRelated>
     */
    public function getRelatedIterator(): iterable
    {
        if (!isset($this->related)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->related);
    }

    /**
     * A collection of documents compiled for a purpose together with metadata that
     * applies to the collection.
     *
     * Related identifiers or resources associated with the DocumentManifest.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentManifest\FHIRDocumentManifestRelated $related
     * @return static
     */
    public function addRelated(FHIRDocumentManifestRelated $related): self
    {
        if (!isset($this->related)) {
            $this->related = [];
        }
        $this->related[] = $related;
        return $this;
    }

    /**
     * A collection of documents compiled for a purpose together with metadata that
     * applies to the collection.
     *
     * Related identifiers or resources associated with the DocumentManifest.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentManifest\FHIRDocumentManifestRelated ...$related
     * @return static
     */
    public function setRelated(FHIRDocumentManifestRelated ...$related): self
    {
        if ([] === $related) {
            unset($this->related);
            return $this;
        }
        $this->related = $related;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDocumentManifest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDocumentManifest
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRDocumentManifest)) {
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
            } else if (self::FIELD_MASTER_IDENTIFIER === $cen) {
                $type->setMasterIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRDocumentReferenceStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CREATED === $cen) {
                $type->setCreated(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHOR === $cen) {
                $type->addAuthor(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECIPIENT === $cen) {
                $type->addRecipient(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE === $cen) {
                $type->setSource(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTENT === $cen) {
                $type->addContent(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATED === $cen) {
                $type->addRelated(FHIRDocumentManifestRelated::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CREATED])) {
            if (isset($type->created)) {
                $type->created->setValue((string)$attributes[self::FIELD_CREATED]);
            } else {
                $type->setCreated((string)$attributes[self::FIELD_CREATED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CREATED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SOURCE])) {
            if (isset($type->source)) {
                $type->source->setValue((string)$attributes[self::FIELD_SOURCE]);
            } else {
                $type->setSource((string)$attributes[self::FIELD_SOURCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SOURCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('DocumentManifest', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->created) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CREATED]) {
            $xw->writeAttribute(self::FIELD_CREATED, $this->created->_getValueAsString());
        }
        if (isset($this->source) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SOURCE]) {
            $xw->writeAttribute(self::FIELD_SOURCE, $this->source->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->masterIdentifier)) {
            $xw->startElement(self::FIELD_MASTER_IDENTIFIER);
            $this->masterIdentifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
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
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subject)) {
            $xw->startElement(self::FIELD_SUBJECT);
            $this->subject->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->created)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CREATED]
                || $this->created->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CREATED);
            $this->created->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CREATED]);
            $xw->endElement();
        }
        if (isset($this->author)) {
            foreach ($this->author as $v) {
                $xw->startElement(self::FIELD_AUTHOR);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->recipient)) {
            foreach ($this->recipient as $v) {
                $xw->startElement(self::FIELD_RECIPIENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->source)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SOURCE]
                || $this->source->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SOURCE);
            $this->source->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SOURCE]);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->content)) {
            foreach ($this->content as $v) {
                $xw->startElement(self::FIELD_CONTENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->related)) {
            foreach ($this->related as $v) {
                $xw->startElement(self::FIELD_RELATED);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDocumentManifest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDocumentManifest
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
        } else if (!($type instanceof FHIRDocumentManifest)) {
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
        if (isset($decoded->masterIdentifier) || property_exists($decoded, self::FIELD_MASTER_IDENTIFIER)) {
            if (is_array($decoded->masterIdentifier)) {
                $type->setMasterIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->masterIdentifier), $config));
            } else {
                $type->setMasterIdentifier(FHIRIdentifier::jsonUnserialize($decoded->masterIdentifier, $config));
            }
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
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRDocumentReferenceStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_array($decoded->subject)) {
                $type->setSubject(FHIRReference::jsonUnserialize(reset($decoded->subject), $config));
            } else {
                $type->setSubject(FHIRReference::jsonUnserialize($decoded->subject, $config));
            }
        }
        if (isset($decoded->created)
            || isset($decoded->_created)
            || property_exists($decoded, self::FIELD_CREATED)
            || property_exists($decoded, self::FIELD_CREATED_EXT)) {
            $v = $decoded->_created ?? new \stdClass();
            $v->value = $decoded->created ?? null;
            $type->setCreated(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->author) || property_exists($decoded, self::FIELD_AUTHOR)) {
            if (is_object($decoded->author)) {
                $vals = [$decoded->author];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_AUTHOR, true);
            } else {
                $vals = $decoded->author;
            }
            foreach($vals as $v) {
                $type->addAuthor(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->recipient) || property_exists($decoded, self::FIELD_RECIPIENT)) {
            if (is_object($decoded->recipient)) {
                $vals = [$decoded->recipient];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RECIPIENT, true);
            } else {
                $vals = $decoded->recipient;
            }
            foreach($vals as $v) {
                $type->addRecipient(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->source)
            || isset($decoded->_source)
            || property_exists($decoded, self::FIELD_SOURCE)
            || property_exists($decoded, self::FIELD_SOURCE_EXT)) {
            $v = $decoded->_source ?? new \stdClass();
            $v->value = $decoded->source ?? null;
            $type->setSource(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->content) || property_exists($decoded, self::FIELD_CONTENT)) {
            if (is_object($decoded->content)) {
                $vals = [$decoded->content];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTENT, true);
            } else {
                $vals = $decoded->content;
            }
            foreach($vals as $v) {
                $type->addContent(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->related) || property_exists($decoded, self::FIELD_RELATED)) {
            if (is_object($decoded->related)) {
                $vals = [$decoded->related];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RELATED, true);
            } else {
                $vals = $decoded->related;
            }
            foreach($vals as $v) {
                $type->addRelated(FHIRDocumentManifestRelated::jsonUnserialize($v, $config));
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
        if (isset($this->masterIdentifier)) {
            $out->masterIdentifier = $this->masterIdentifier;
        }
        if (isset($this->identifier) && [] !== $this->identifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER) && 1 === count($this->identifier)) {
                $out->identifier = $this->identifier[0];
            } else {
                $out->identifier = $this->identifier;
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
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->subject)) {
            $out->subject = $this->subject;
        }
        if (isset($this->created)) {
            if (null !== ($val = $this->created->getValue())) {
                $out->created = $val;
            }
            if ($this->created->_nonValueFieldDefined()) {
                $ext = $this->created->jsonSerialize();
                unset($ext->value);
                $out->_created = $ext;
            }
        }
        if (isset($this->author) && [] !== $this->author) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_AUTHOR) && 1 === count($this->author)) {
                $out->author = $this->author[0];
            } else {
                $out->author = $this->author;
            }
        }
        if (isset($this->recipient) && [] !== $this->recipient) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RECIPIENT) && 1 === count($this->recipient)) {
                $out->recipient = $this->recipient[0];
            } else {
                $out->recipient = $this->recipient;
            }
        }
        if (isset($this->source)) {
            if (null !== ($val = $this->source->getValue())) {
                $out->source = $val;
            }
            if ($this->source->_nonValueFieldDefined()) {
                $ext = $this->source->jsonSerialize();
                unset($ext->value);
                $out->_source = $ext;
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
        if (isset($this->content) && [] !== $this->content) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTENT) && 1 === count($this->content)) {
                $out->content = $this->content[0];
            } else {
                $out->content = $this->content;
            }
        }
        if (isset($this->related) && [] !== $this->related) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELATED) && 1 === count($this->related)) {
                $out->related = $this->related[0];
            } else {
                $out->related = $this->related;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
