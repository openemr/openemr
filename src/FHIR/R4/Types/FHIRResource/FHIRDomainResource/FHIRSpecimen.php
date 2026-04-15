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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSpecimenStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenCollection;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenContainer;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenProcessing;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * A sample to be used for analysis.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSpecimen extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SPECIMEN;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_ACCESSION_IDENTIFIER = 'accessionIdentifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_TYPE = 'type';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_RECEIVED_TIME = 'receivedTime';
    public const FIELD_RECEIVED_TIME_EXT = '_receivedTime';
    public const FIELD_PARENT = 'parent';
    public const FIELD_REQUEST = 'request';
    public const FIELD_COLLECTION = 'collection';
    public const FIELD_PROCESSING = 'processing';
    public const FIELD_CONTAINER = 'container';
    public const FIELD_CONDITION = 'condition';
    public const FIELD_NOTE = 'note';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RECEIVED_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Id for specimen.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identifier assigned by the lab when accessioning specimen(s). This is not
     * necessarily the same as the specimen identifier, depending on local lab
     * procedures.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $accessionIdentifier;
    /**
     * Codes providing the status/availability of a specimen.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The availability of the specimen.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenStatus
     */
    #[FHIRSpecimenStatus]
    protected FHIRSpecimenStatus $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of material that forms the specimen.
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
     * Where the specimen came from. This may be from patient(s), from a location
     * (e.g., the source of an environmental sample), or a sampling of a substance or a
     * device.
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
     * Time when specimen was received for processing or testing.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $receivedTime;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the parent (source) specimen which is used when the specimen was
     * either derived from or a component of another specimen.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $parent;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details concerning a service request that required a specimen to be collected.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $request;
    /**
     * A sample to be used for analysis.
     *
     * Details concerning the specimen collection.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenCollection
     */
    #[FHIRSpecimenCollection]
    protected FHIRSpecimenCollection $collection;
    /**
     * A sample to be used for analysis.
     *
     * Details concerning processing and processing steps for the specimen.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenProcessing>
     */
    #[FHIRSpecimenProcessing]
    protected array $processing;
    /**
     * A sample to be used for analysis.
     *
     * The container holding the specimen. The recursive nature of containers; i.e.
     * blood in tube in tray in rack is not addressed here.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenContainer>
     */
    #[FHIRSpecimenContainer]
    protected array $container;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A mode or state of being that describes the nature of the specimen.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $condition;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * To communicate any details or issues about the specimen or during the specimen
     * collection. (for example: broken vial, sent with patient, frozen).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;

    /* constructor.php:61 */
    /**
     * FHIRSpecimen Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $accessionIdentifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSpecimenStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $receivedTime
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $parent
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $request
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenCollection $collection
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenProcessing> $processing
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenContainer> $container
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $condition
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
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
                                null|FHIRIdentifier $accessionIdentifier = null,
                                null|string|FHIRSpecimenStatusList|FHIRSpecimenStatus $status = null,
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRReference $subject = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $receivedTime = null,
                                null|iterable $parent = null,
                                null|iterable $request = null,
                                null|FHIRSpecimenCollection $collection = null,
                                null|iterable $processing = null,
                                null|iterable $container = null,
                                null|iterable $condition = null,
                                null|iterable $note = null,
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
        if (null !== $accessionIdentifier) {
            $this->setAccessionIdentifier($accessionIdentifier);
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
        if (null !== $receivedTime) {
            $this->setReceivedTime($receivedTime);
        }
        if (null !== $parent) {
            $this->setParent(...$parent);
        }
        if (null !== $request) {
            $this->setRequest(...$request);
        }
        if (null !== $collection) {
            $this->setCollection($collection);
        }
        if (null !== $processing) {
            $this->setProcessing(...$processing);
        }
        if (null !== $container) {
            $this->setContainer(...$container);
        }
        if (null !== $condition) {
            $this->setCondition(...$condition);
        }
        if (null !== $note) {
            $this->setNote(...$note);
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
     * Id for specimen.
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
     * Id for specimen.
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
     * Id for specimen.
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
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identifier assigned by the lab when accessioning specimen(s). This is not
     * necessarily the same as the specimen identifier, depending on local lab
     * procedures.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getAccessionIdentifier(): null|FHIRIdentifier
    {
        return $this->accessionIdentifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identifier assigned by the lab when accessioning specimen(s). This is not
     * necessarily the same as the specimen identifier, depending on local lab
     * procedures.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $accessionIdentifier
     * @return static
     */
    public function setAccessionIdentifier(null|FHIRIdentifier $accessionIdentifier): self
    {
        if (null === $accessionIdentifier) {
            unset($this->accessionIdentifier);
            return $this;
        }
        $this->accessionIdentifier = $accessionIdentifier;
        return $this;
    }

    /**
     * Codes providing the status/availability of a specimen.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The availability of the specimen.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenStatus
     */
    public function getStatus(): null|FHIRSpecimenStatus
    {
        return $this->status ?? null;
    }

    /**
     * Codes providing the status/availability of a specimen.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The availability of the specimen.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSpecimenStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRSpecimenStatusList|FHIRSpecimenStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRSpecimenStatus)) {
            $status = new FHIRSpecimenStatus(value: $status);
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
     * The kind of material that forms the specimen.
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
     * The kind of material that forms the specimen.
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
     * Where the specimen came from. This may be from patient(s), from a location
     * (e.g., the source of an environmental sample), or a sampling of a substance or a
     * device.
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
     * Where the specimen came from. This may be from patient(s), from a location
     * (e.g., the source of an environmental sample), or a sampling of a substance or a
     * device.
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
     * Time when specimen was received for processing or testing.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getReceivedTime(): null|FHIRDateTime
    {
        return $this->receivedTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time when specimen was received for processing or testing.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $receivedTime
     * @return static
     */
    public function setReceivedTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $receivedTime): self
    {
        if (null === $receivedTime) {
            unset($this->receivedTime);
            return $this;
        }
        if (!($receivedTime instanceof FHIRDateTime)) {
            $receivedTime = new FHIRDateTime(value: $receivedTime);
        }
        $this->receivedTime = $receivedTime;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the parent (source) specimen which is used when the specimen was
     * either derived from or a component of another specimen.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getParent(): array
    {
        return $this->parent ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getParentIterator(): iterable
    {
        if (!isset($this->parent)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->parent);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the parent (source) specimen which is used when the specimen was
     * either derived from or a component of another specimen.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $parent
     * @return static
     */
    public function addParent(FHIRReference $parent): self
    {
        if (!isset($this->parent)) {
            $this->parent = [];
        }
        $this->parent[] = $parent;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the parent (source) specimen which is used when the specimen was
     * either derived from or a component of another specimen.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$parent
     * @return static
     */
    public function setParent(FHIRReference ...$parent): self
    {
        if ([] === $parent) {
            unset($this->parent);
            return $this;
        }
        $this->parent = $parent;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details concerning a service request that required a specimen to be collected.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRequest(): array
    {
        return $this->request ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRequestIterator(): iterable
    {
        if (!isset($this->request)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->request);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details concerning a service request that required a specimen to be collected.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $request
     * @return static
     */
    public function addRequest(FHIRReference $request): self
    {
        if (!isset($this->request)) {
            $this->request = [];
        }
        $this->request[] = $request;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details concerning a service request that required a specimen to be collected.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$request
     * @return static
     */
    public function setRequest(FHIRReference ...$request): self
    {
        if ([] === $request) {
            unset($this->request);
            return $this;
        }
        $this->request = $request;
        return $this;
    }

    /**
     * A sample to be used for analysis.
     *
     * Details concerning the specimen collection.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenCollection
     */
    public function getCollection(): null|FHIRSpecimenCollection
    {
        return $this->collection ?? null;
    }

    /**
     * A sample to be used for analysis.
     *
     * Details concerning the specimen collection.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenCollection $collection
     * @return static
     */
    public function setCollection(null|FHIRSpecimenCollection $collection): self
    {
        if (null === $collection) {
            unset($this->collection);
            return $this;
        }
        $this->collection = $collection;
        return $this;
    }

    /**
     * A sample to be used for analysis.
     *
     * Details concerning processing and processing steps for the specimen.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenProcessing>
     */
    public function getProcessing(): array
    {
        return $this->processing ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenProcessing>
     */
    public function getProcessingIterator(): iterable
    {
        if (!isset($this->processing)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->processing);
    }

    /**
     * A sample to be used for analysis.
     *
     * Details concerning processing and processing steps for the specimen.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenProcessing $processing
     * @return static
     */
    public function addProcessing(FHIRSpecimenProcessing $processing): self
    {
        if (!isset($this->processing)) {
            $this->processing = [];
        }
        $this->processing[] = $processing;
        return $this;
    }

    /**
     * A sample to be used for analysis.
     *
     * Details concerning processing and processing steps for the specimen.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenProcessing ...$processing
     * @return static
     */
    public function setProcessing(FHIRSpecimenProcessing ...$processing): self
    {
        if ([] === $processing) {
            unset($this->processing);
            return $this;
        }
        $this->processing = $processing;
        return $this;
    }

    /**
     * A sample to be used for analysis.
     *
     * The container holding the specimen. The recursive nature of containers; i.e.
     * blood in tube in tray in rack is not addressed here.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenContainer>
     */
    public function getContainer(): array
    {
        return $this->container ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenContainer>
     */
    public function getContainerIterator(): iterable
    {
        if (!isset($this->container)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->container);
    }

    /**
     * A sample to be used for analysis.
     *
     * The container holding the specimen. The recursive nature of containers; i.e.
     * blood in tube in tray in rack is not addressed here.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenContainer $container
     * @return static
     */
    public function addContainer(FHIRSpecimenContainer $container): self
    {
        if (!isset($this->container)) {
            $this->container = [];
        }
        $this->container[] = $container;
        return $this;
    }

    /**
     * A sample to be used for analysis.
     *
     * The container holding the specimen. The recursive nature of containers; i.e.
     * blood in tube in tray in rack is not addressed here.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimen\FHIRSpecimenContainer ...$container
     * @return static
     */
    public function setContainer(FHIRSpecimenContainer ...$container): self
    {
        if ([] === $container) {
            unset($this->container);
            return $this;
        }
        $this->container = $container;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A mode or state of being that describes the nature of the specimen.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCondition(): array
    {
        return $this->condition ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getConditionIterator(): iterable
    {
        if (!isset($this->condition)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->condition);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A mode or state of being that describes the nature of the specimen.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $condition
     * @return static
     */
    public function addCondition(FHIRCodeableConcept $condition): self
    {
        if (!isset($this->condition)) {
            $this->condition = [];
        }
        $this->condition[] = $condition;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A mode or state of being that describes the nature of the specimen.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$condition
     * @return static
     */
    public function setCondition(FHIRCodeableConcept ...$condition): self
    {
        if ([] === $condition) {
            unset($this->condition);
            return $this;
        }
        $this->condition = $condition;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * To communicate any details or issues about the specimen or during the specimen
     * collection. (for example: broken vial, sent with patient, frozen).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNote(): array
    {
        return $this->note ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNoteIterator(): iterable
    {
        if (!isset($this->note)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->note);
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * To communicate any details or issues about the specimen or during the specimen
     * collection. (for example: broken vial, sent with patient, frozen).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation $note
     * @return static
     */
    public function addNote(FHIRAnnotation $note): self
    {
        if (!isset($this->note)) {
            $this->note = [];
        }
        $this->note[] = $note;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * To communicate any details or issues about the specimen or during the specimen
     * collection. (for example: broken vial, sent with patient, frozen).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation ...$note
     * @return static
     */
    public function setNote(FHIRAnnotation ...$note): self
    {
        if ([] === $note) {
            unset($this->note);
            return $this;
        }
        $this->note = $note;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSpecimen $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSpecimen
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSpecimen)) {
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
            } else if (self::FIELD_ACCESSION_IDENTIFIER === $cen) {
                $type->setAccessionIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRSpecimenStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECEIVED_TIME === $cen) {
                $type->setReceivedTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARENT === $cen) {
                $type->addParent(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST === $cen) {
                $type->addRequest(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COLLECTION === $cen) {
                $type->setCollection(FHIRSpecimenCollection::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROCESSING === $cen) {
                $type->addProcessing(FHIRSpecimenProcessing::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINER === $cen) {
                $type->addContainer(FHIRSpecimenContainer::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONDITION === $cen) {
                $type->addCondition(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_RECEIVED_TIME])) {
            if (isset($type->receivedTime)) {
                $type->receivedTime->setValue((string)$attributes[self::FIELD_RECEIVED_TIME]);
            } else {
                $type->setReceivedTime((string)$attributes[self::FIELD_RECEIVED_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RECEIVED_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('Specimen', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->receivedTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RECEIVED_TIME]) {
            $xw->writeAttribute(self::FIELD_RECEIVED_TIME, $this->receivedTime->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->accessionIdentifier)) {
            $xw->startElement(self::FIELD_ACCESSION_IDENTIFIER);
            $this->accessionIdentifier->xmlSerialize($xw, $config);
            $xw->endElement();
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
        if (isset($this->receivedTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RECEIVED_TIME]
                || $this->receivedTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RECEIVED_TIME);
            $this->receivedTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RECEIVED_TIME]);
            $xw->endElement();
        }
        if (isset($this->parent)) {
            foreach ($this->parent as $v) {
                $xw->startElement(self::FIELD_PARENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->request)) {
            foreach ($this->request as $v) {
                $xw->startElement(self::FIELD_REQUEST);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->collection)) {
            $xw->startElement(self::FIELD_COLLECTION);
            $this->collection->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->processing)) {
            foreach ($this->processing as $v) {
                $xw->startElement(self::FIELD_PROCESSING);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->container)) {
            foreach ($this->container as $v) {
                $xw->startElement(self::FIELD_CONTAINER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->condition)) {
            foreach ($this->condition as $v) {
                $xw->startElement(self::FIELD_CONDITION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->note)) {
            foreach ($this->note as $v) {
                $xw->startElement(self::FIELD_NOTE);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSpecimen $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSpecimen
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
        } else if (!($type instanceof FHIRSpecimen)) {
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
        if (isset($decoded->accessionIdentifier) || property_exists($decoded, self::FIELD_ACCESSION_IDENTIFIER)) {
            if (is_array($decoded->accessionIdentifier)) {
                $type->setAccessionIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->accessionIdentifier), $config));
            } else {
                $type->setAccessionIdentifier(FHIRIdentifier::jsonUnserialize($decoded->accessionIdentifier, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRSpecimenStatus::jsonUnserialize($v, $config));
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
        if (isset($decoded->receivedTime)
            || isset($decoded->_receivedTime)
            || property_exists($decoded, self::FIELD_RECEIVED_TIME)
            || property_exists($decoded, self::FIELD_RECEIVED_TIME_EXT)) {
            $v = $decoded->_receivedTime ?? new \stdClass();
            $v->value = $decoded->receivedTime ?? null;
            $type->setReceivedTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->parent) || property_exists($decoded, self::FIELD_PARENT)) {
            if (is_object($decoded->parent)) {
                $vals = [$decoded->parent];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PARENT, true);
            } else {
                $vals = $decoded->parent;
            }
            foreach($vals as $v) {
                $type->addParent(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->request) || property_exists($decoded, self::FIELD_REQUEST)) {
            if (is_object($decoded->request)) {
                $vals = [$decoded->request];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REQUEST, true);
            } else {
                $vals = $decoded->request;
            }
            foreach($vals as $v) {
                $type->addRequest(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->collection) || property_exists($decoded, self::FIELD_COLLECTION)) {
            if (is_array($decoded->collection)) {
                $type->setCollection(FHIRSpecimenCollection::jsonUnserialize(reset($decoded->collection), $config));
            } else {
                $type->setCollection(FHIRSpecimenCollection::jsonUnserialize($decoded->collection, $config));
            }
        }
        if (isset($decoded->processing) || property_exists($decoded, self::FIELD_PROCESSING)) {
            if (is_object($decoded->processing)) {
                $vals = [$decoded->processing];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROCESSING, true);
            } else {
                $vals = $decoded->processing;
            }
            foreach($vals as $v) {
                $type->addProcessing(FHIRSpecimenProcessing::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->container) || property_exists($decoded, self::FIELD_CONTAINER)) {
            if (is_object($decoded->container)) {
                $vals = [$decoded->container];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTAINER, true);
            } else {
                $vals = $decoded->container;
            }
            foreach($vals as $v) {
                $type->addContainer(FHIRSpecimenContainer::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->condition) || property_exists($decoded, self::FIELD_CONDITION)) {
            if (is_object($decoded->condition)) {
                $vals = [$decoded->condition];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONDITION, true);
            } else {
                $vals = $decoded->condition;
            }
            foreach($vals as $v) {
                $type->addCondition(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->note) || property_exists($decoded, self::FIELD_NOTE)) {
            if (is_object($decoded->note)) {
                $vals = [$decoded->note];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NOTE, true);
            } else {
                $vals = $decoded->note;
            }
            foreach($vals as $v) {
                $type->addNote(FHIRAnnotation::jsonUnserialize($v, $config));
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
        if (isset($this->accessionIdentifier)) {
            $out->accessionIdentifier = $this->accessionIdentifier;
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
        if (isset($this->receivedTime)) {
            if (null !== ($val = $this->receivedTime->getValue())) {
                $out->receivedTime = $val;
            }
            if ($this->receivedTime->_nonValueFieldDefined()) {
                $ext = $this->receivedTime->jsonSerialize();
                unset($ext->value);
                $out->_receivedTime = $ext;
            }
        }
        if (isset($this->parent) && [] !== $this->parent) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PARENT) && 1 === count($this->parent)) {
                $out->parent = $this->parent[0];
            } else {
                $out->parent = $this->parent;
            }
        }
        if (isset($this->request) && [] !== $this->request) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REQUEST) && 1 === count($this->request)) {
                $out->request = $this->request[0];
            } else {
                $out->request = $this->request;
            }
        }
        if (isset($this->collection)) {
            $out->collection = $this->collection;
        }
        if (isset($this->processing) && [] !== $this->processing) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROCESSING) && 1 === count($this->processing)) {
                $out->processing = $this->processing[0];
            } else {
                $out->processing = $this->processing;
            }
        }
        if (isset($this->container) && [] !== $this->container) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTAINER) && 1 === count($this->container)) {
                $out->container = $this->container[0];
            } else {
                $out->container = $this->container;
            }
        }
        if (isset($this->condition) && [] !== $this->condition) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONDITION) && 1 === count($this->condition)) {
                $out->condition = $this->condition[0];
            } else {
                $out->condition = $this->condition;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
