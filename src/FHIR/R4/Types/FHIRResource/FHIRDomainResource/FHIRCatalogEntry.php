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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Catalog entries are wrappers that contextualize items included in a catalog.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCatalogEntry extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CATALOG_ENTRY;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_TYPE = 'type';
    public const FIELD_ORDERABLE = 'orderable';
    public const FIELD_ORDERABLE_EXT = '_orderable';
    public const FIELD_REFERENCED_ITEM = 'referencedItem';
    public const FIELD_ADDITIONAL_IDENTIFIER = 'additionalIdentifier';
    public const FIELD_CLASSIFICATION = 'classification';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_VALIDITY_PERIOD = 'validityPeriod';
    public const FIELD_VALID_TO = 'validTo';
    public const FIELD_VALID_TO_EXT = '_validTo';
    public const FIELD_LAST_UPDATED = 'lastUpdated';
    public const FIELD_LAST_UPDATED_EXT = '_lastUpdated';
    public const FIELD_ADDITIONAL_CHARACTERISTIC = 'additionalCharacteristic';
    public const FIELD_ADDITIONAL_CLASSIFICATION = 'additionalClassification';
    public const FIELD_RELATED_ENTRY = 'relatedEntry';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_ORDERABLE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_REFERENCED_ITEM => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ORDERABLE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALID_TO => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LAST_UPDATED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used in supporting different identifiers for the same product, e.g. manufacturer
     * code and retailer code.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of item - medication, device, service, protocol or other.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the entry represents an orderable item.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $orderable;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The item in a catalog or definition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $referencedItem;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used in supporting related concepts, e.g. NDC to RxNorm.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $additionalIdentifier;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Classes of devices, or ATC for medication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $classification;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Used to support catalog exchange even for unsupported products, e.g. getting
     * list of medications even if not prescribable.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus
     */
    #[FHIRPublicationStatus]
    protected FHIRPublicationStatus $status;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The time period in which this catalog entry is expected to be active.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $validityPeriod;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date until which this catalog entry is expected to be active.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $validTo;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Typically date of issue is different from the beginning of the validity. This
     * can be used to see when an item was last updated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $lastUpdated;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used for examplefor Out of Formulary, or any specifics.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $additionalCharacteristic;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * User for example for ATC classification, or.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $additionalClassification;
    /**
     * Catalog entries are wrappers that contextualize items included in a catalog.
     *
     * Used for example, to point to a substance, or to a device used to administer a
     * medication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry>
     */
    #[FHIRCatalogEntryRelatedEntry]
    protected array $relatedEntry;

    /* constructor.php:61 */
    /**
     * FHIRCatalogEntry Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $orderable
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $referencedItem
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $additionalIdentifier
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $classification
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $validityPeriod
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $validTo
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $lastUpdated
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $additionalCharacteristic
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $additionalClassification
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry> $relatedEntry
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
                                null|FHIRCodeableConcept $type = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $orderable = null,
                                null|FHIRReference $referencedItem = null,
                                null|iterable $additionalIdentifier = null,
                                null|iterable $classification = null,
                                null|string|FHIRPublicationStatusList|FHIRPublicationStatus $status = null,
                                null|FHIRPeriod $validityPeriod = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $validTo = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $lastUpdated = null,
                                null|iterable $additionalCharacteristic = null,
                                null|iterable $additionalClassification = null,
                                null|iterable $relatedEntry = null,
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
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $orderable) {
            $this->setOrderable($orderable);
        }
        if (null !== $referencedItem) {
            $this->setReferencedItem($referencedItem);
        }
        if (null !== $additionalIdentifier) {
            $this->setAdditionalIdentifier(...$additionalIdentifier);
        }
        if (null !== $classification) {
            $this->setClassification(...$classification);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $validityPeriod) {
            $this->setValidityPeriod($validityPeriod);
        }
        if (null !== $validTo) {
            $this->setValidTo($validTo);
        }
        if (null !== $lastUpdated) {
            $this->setLastUpdated($lastUpdated);
        }
        if (null !== $additionalCharacteristic) {
            $this->setAdditionalCharacteristic(...$additionalCharacteristic);
        }
        if (null !== $additionalClassification) {
            $this->setAdditionalClassification(...$additionalClassification);
        }
        if (null !== $relatedEntry) {
            $this->setRelatedEntry(...$relatedEntry);
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
     * Used in supporting different identifiers for the same product, e.g. manufacturer
     * code and retailer code.
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
     * Used in supporting different identifiers for the same product, e.g. manufacturer
     * code and retailer code.
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
     * Used in supporting different identifiers for the same product, e.g. manufacturer
     * code and retailer code.
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
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of item - medication, device, service, protocol or other.
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
     * The type of item - medication, device, service, protocol or other.
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
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the entry represents an orderable item.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getOrderable(): null|FHIRBoolean
    {
        return $this->orderable ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the entry represents an orderable item.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $orderable
     * @return static
     */
    public function setOrderable(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $orderable): self
    {
        if (null === $orderable) {
            unset($this->orderable);
            return $this;
        }
        if (!($orderable instanceof FHIRBoolean)) {
            $orderable = new FHIRBoolean(value: $orderable);
        }
        $this->orderable = $orderable;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The item in a catalog or definition.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getReferencedItem(): null|FHIRReference
    {
        return $this->referencedItem ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The item in a catalog or definition.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $referencedItem
     * @return static
     */
    public function setReferencedItem(null|FHIRReference $referencedItem): self
    {
        if (null === $referencedItem) {
            unset($this->referencedItem);
            return $this;
        }
        $this->referencedItem = $referencedItem;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used in supporting related concepts, e.g. NDC to RxNorm.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getAdditionalIdentifier(): array
    {
        return $this->additionalIdentifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getAdditionalIdentifierIterator(): iterable
    {
        if (!isset($this->additionalIdentifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->additionalIdentifier);
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used in supporting related concepts, e.g. NDC to RxNorm.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $additionalIdentifier
     * @return static
     */
    public function addAdditionalIdentifier(FHIRIdentifier $additionalIdentifier): self
    {
        if (!isset($this->additionalIdentifier)) {
            $this->additionalIdentifier = [];
        }
        $this->additionalIdentifier[] = $additionalIdentifier;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used in supporting related concepts, e.g. NDC to RxNorm.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier ...$additionalIdentifier
     * @return static
     */
    public function setAdditionalIdentifier(FHIRIdentifier ...$additionalIdentifier): self
    {
        if ([] === $additionalIdentifier) {
            unset($this->additionalIdentifier);
            return $this;
        }
        $this->additionalIdentifier = $additionalIdentifier;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Classes of devices, or ATC for medication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getClassification(): array
    {
        return $this->classification ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getClassificationIterator(): iterable
    {
        if (!isset($this->classification)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->classification);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Classes of devices, or ATC for medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $classification
     * @return static
     */
    public function addClassification(FHIRCodeableConcept $classification): self
    {
        if (!isset($this->classification)) {
            $this->classification = [];
        }
        $this->classification[] = $classification;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Classes of devices, or ATC for medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$classification
     * @return static
     */
    public function setClassification(FHIRCodeableConcept ...$classification): self
    {
        if ([] === $classification) {
            unset($this->classification);
            return $this;
        }
        $this->classification = $classification;
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Used to support catalog exchange even for unsupported products, e.g. getting
     * list of medications even if not prescribable.
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
     * Used to support catalog exchange even for unsupported products, e.g. getting
     * list of medications even if not prescribable.
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
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The time period in which this catalog entry is expected to be active.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getValidityPeriod(): null|FHIRPeriod
    {
        return $this->validityPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The time period in which this catalog entry is expected to be active.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $validityPeriod
     * @return static
     */
    public function setValidityPeriod(null|FHIRPeriod $validityPeriod): self
    {
        if (null === $validityPeriod) {
            unset($this->validityPeriod);
            return $this;
        }
        $this->validityPeriod = $validityPeriod;
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
     * The date until which this catalog entry is expected to be active.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getValidTo(): null|FHIRDateTime
    {
        return $this->validTo ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date until which this catalog entry is expected to be active.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $validTo
     * @return static
     */
    public function setValidTo(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $validTo): self
    {
        if (null === $validTo) {
            unset($this->validTo);
            return $this;
        }
        if (!($validTo instanceof FHIRDateTime)) {
            $validTo = new FHIRDateTime(value: $validTo);
        }
        $this->validTo = $validTo;
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
     * Typically date of issue is different from the beginning of the validity. This
     * can be used to see when an item was last updated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getLastUpdated(): null|FHIRDateTime
    {
        return $this->lastUpdated ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Typically date of issue is different from the beginning of the validity. This
     * can be used to see when an item was last updated.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $lastUpdated
     * @return static
     */
    public function setLastUpdated(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $lastUpdated): self
    {
        if (null === $lastUpdated) {
            unset($this->lastUpdated);
            return $this;
        }
        if (!($lastUpdated instanceof FHIRDateTime)) {
            $lastUpdated = new FHIRDateTime(value: $lastUpdated);
        }
        $this->lastUpdated = $lastUpdated;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used for examplefor Out of Formulary, or any specifics.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getAdditionalCharacteristic(): array
    {
        return $this->additionalCharacteristic ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getAdditionalCharacteristicIterator(): iterable
    {
        if (!isset($this->additionalCharacteristic)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->additionalCharacteristic);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used for examplefor Out of Formulary, or any specifics.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $additionalCharacteristic
     * @return static
     */
    public function addAdditionalCharacteristic(FHIRCodeableConcept $additionalCharacteristic): self
    {
        if (!isset($this->additionalCharacteristic)) {
            $this->additionalCharacteristic = [];
        }
        $this->additionalCharacteristic[] = $additionalCharacteristic;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Used for examplefor Out of Formulary, or any specifics.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$additionalCharacteristic
     * @return static
     */
    public function setAdditionalCharacteristic(FHIRCodeableConcept ...$additionalCharacteristic): self
    {
        if ([] === $additionalCharacteristic) {
            unset($this->additionalCharacteristic);
            return $this;
        }
        $this->additionalCharacteristic = $additionalCharacteristic;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * User for example for ATC classification, or.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getAdditionalClassification(): array
    {
        return $this->additionalClassification ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getAdditionalClassificationIterator(): iterable
    {
        if (!isset($this->additionalClassification)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->additionalClassification);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * User for example for ATC classification, or.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $additionalClassification
     * @return static
     */
    public function addAdditionalClassification(FHIRCodeableConcept $additionalClassification): self
    {
        if (!isset($this->additionalClassification)) {
            $this->additionalClassification = [];
        }
        $this->additionalClassification[] = $additionalClassification;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * User for example for ATC classification, or.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$additionalClassification
     * @return static
     */
    public function setAdditionalClassification(FHIRCodeableConcept ...$additionalClassification): self
    {
        if ([] === $additionalClassification) {
            unset($this->additionalClassification);
            return $this;
        }
        $this->additionalClassification = $additionalClassification;
        return $this;
    }

    /**
     * Catalog entries are wrappers that contextualize items included in a catalog.
     *
     * Used for example, to point to a substance, or to a device used to administer a
     * medication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry>
     */
    public function getRelatedEntry(): array
    {
        return $this->relatedEntry ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry>
     */
    public function getRelatedEntryIterator(): iterable
    {
        if (!isset($this->relatedEntry)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->relatedEntry);
    }

    /**
     * Catalog entries are wrappers that contextualize items included in a catalog.
     *
     * Used for example, to point to a substance, or to a device used to administer a
     * medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry $relatedEntry
     * @return static
     */
    public function addRelatedEntry(FHIRCatalogEntryRelatedEntry $relatedEntry): self
    {
        if (!isset($this->relatedEntry)) {
            $this->relatedEntry = [];
        }
        $this->relatedEntry[] = $relatedEntry;
        return $this;
    }

    /**
     * Catalog entries are wrappers that contextualize items included in a catalog.
     *
     * Used for example, to point to a substance, or to a device used to administer a
     * medication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry ...$relatedEntry
     * @return static
     */
    public function setRelatedEntry(FHIRCatalogEntryRelatedEntry ...$relatedEntry): self
    {
        if ([] === $relatedEntry) {
            unset($this->relatedEntry);
            return $this;
        }
        $this->relatedEntry = $relatedEntry;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCatalogEntry $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCatalogEntry
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCatalogEntry)) {
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
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORDERABLE === $cen) {
                $type->setOrderable(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCED_ITEM === $cen) {
                $type->setReferencedItem(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADDITIONAL_IDENTIFIER === $cen) {
                $type->addAdditionalIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLASSIFICATION === $cen) {
                $type->addClassification(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRPublicationStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDITY_PERIOD === $cen) {
                $type->setValidityPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALID_TO === $cen) {
                $type->setValidTo(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LAST_UPDATED === $cen) {
                $type->setLastUpdated(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADDITIONAL_CHARACTERISTIC === $cen) {
                $type->addAdditionalCharacteristic(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADDITIONAL_CLASSIFICATION === $cen) {
                $type->addAdditionalClassification(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATED_ENTRY === $cen) {
                $type->addRelatedEntry(FHIRCatalogEntryRelatedEntry::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_ORDERABLE])) {
            if (isset($type->orderable)) {
                $type->orderable->setValue((string)$attributes[self::FIELD_ORDERABLE]);
            } else {
                $type->setOrderable((string)$attributes[self::FIELD_ORDERABLE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ORDERABLE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALID_TO])) {
            if (isset($type->validTo)) {
                $type->validTo->setValue((string)$attributes[self::FIELD_VALID_TO]);
            } else {
                $type->setValidTo((string)$attributes[self::FIELD_VALID_TO]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALID_TO, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LAST_UPDATED])) {
            if (isset($type->lastUpdated)) {
                $type->lastUpdated->setValue((string)$attributes[self::FIELD_LAST_UPDATED]);
            } else {
                $type->setLastUpdated((string)$attributes[self::FIELD_LAST_UPDATED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LAST_UPDATED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('CatalogEntry', $this->_getSourceXMLNS());
        }
        if (isset($this->orderable) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ORDERABLE]) {
            $xw->writeAttribute(self::FIELD_ORDERABLE, $this->orderable->_getValueAsString());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->validTo) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALID_TO]) {
            $xw->writeAttribute(self::FIELD_VALID_TO, $this->validTo->_getValueAsString());
        }
        if (isset($this->lastUpdated) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LAST_UPDATED]) {
            $xw->writeAttribute(self::FIELD_LAST_UPDATED, $this->lastUpdated->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->orderable)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ORDERABLE]
                || $this->orderable->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ORDERABLE);
            $this->orderable->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ORDERABLE]);
            $xw->endElement();
        }
        if (isset($this->referencedItem)) {
            $xw->startElement(self::FIELD_REFERENCED_ITEM);
            $this->referencedItem->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->additionalIdentifier)) {
            foreach ($this->additionalIdentifier as $v) {
                $xw->startElement(self::FIELD_ADDITIONAL_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->classification)) {
            foreach ($this->classification as $v) {
                $xw->startElement(self::FIELD_CLASSIFICATION);
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
        if (isset($this->validityPeriod)) {
            $xw->startElement(self::FIELD_VALIDITY_PERIOD);
            $this->validityPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->validTo)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALID_TO]
                || $this->validTo->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALID_TO);
            $this->validTo->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALID_TO]);
            $xw->endElement();
        }
        if (isset($this->lastUpdated)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LAST_UPDATED]
                || $this->lastUpdated->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LAST_UPDATED);
            $this->lastUpdated->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LAST_UPDATED]);
            $xw->endElement();
        }
        if (isset($this->additionalCharacteristic)) {
            foreach ($this->additionalCharacteristic as $v) {
                $xw->startElement(self::FIELD_ADDITIONAL_CHARACTERISTIC);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->additionalClassification)) {
            foreach ($this->additionalClassification as $v) {
                $xw->startElement(self::FIELD_ADDITIONAL_CLASSIFICATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->relatedEntry)) {
            foreach ($this->relatedEntry as $v) {
                $xw->startElement(self::FIELD_RELATED_ENTRY);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCatalogEntry $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCatalogEntry
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
        } else if (!($type instanceof FHIRCatalogEntry)) {
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
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->orderable)
            || isset($decoded->_orderable)
            || property_exists($decoded, self::FIELD_ORDERABLE)
            || property_exists($decoded, self::FIELD_ORDERABLE_EXT)) {
            $v = $decoded->_orderable ?? new \stdClass();
            $v->value = $decoded->orderable ?? null;
            $type->setOrderable(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->referencedItem) || property_exists($decoded, self::FIELD_REFERENCED_ITEM)) {
            if (is_array($decoded->referencedItem)) {
                $type->setReferencedItem(FHIRReference::jsonUnserialize(reset($decoded->referencedItem), $config));
            } else {
                $type->setReferencedItem(FHIRReference::jsonUnserialize($decoded->referencedItem, $config));
            }
        }
        if (isset($decoded->additionalIdentifier) || property_exists($decoded, self::FIELD_ADDITIONAL_IDENTIFIER)) {
            if (is_object($decoded->additionalIdentifier)) {
                $vals = [$decoded->additionalIdentifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADDITIONAL_IDENTIFIER, true);
            } else {
                $vals = $decoded->additionalIdentifier;
            }
            foreach($vals as $v) {
                $type->addAdditionalIdentifier(FHIRIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->classification) || property_exists($decoded, self::FIELD_CLASSIFICATION)) {
            if (is_object($decoded->classification)) {
                $vals = [$decoded->classification];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CLASSIFICATION, true);
            } else {
                $vals = $decoded->classification;
            }
            foreach($vals as $v) {
                $type->addClassification(FHIRCodeableConcept::jsonUnserialize($v, $config));
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
        if (isset($decoded->validityPeriod) || property_exists($decoded, self::FIELD_VALIDITY_PERIOD)) {
            if (is_array($decoded->validityPeriod)) {
                $type->setValidityPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->validityPeriod), $config));
            } else {
                $type->setValidityPeriod(FHIRPeriod::jsonUnserialize($decoded->validityPeriod, $config));
            }
        }
        if (isset($decoded->validTo)
            || isset($decoded->_validTo)
            || property_exists($decoded, self::FIELD_VALID_TO)
            || property_exists($decoded, self::FIELD_VALID_TO_EXT)) {
            $v = $decoded->_validTo ?? new \stdClass();
            $v->value = $decoded->validTo ?? null;
            $type->setValidTo(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->lastUpdated)
            || isset($decoded->_lastUpdated)
            || property_exists($decoded, self::FIELD_LAST_UPDATED)
            || property_exists($decoded, self::FIELD_LAST_UPDATED_EXT)) {
            $v = $decoded->_lastUpdated ?? new \stdClass();
            $v->value = $decoded->lastUpdated ?? null;
            $type->setLastUpdated(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->additionalCharacteristic) || property_exists($decoded, self::FIELD_ADDITIONAL_CHARACTERISTIC)) {
            if (is_object($decoded->additionalCharacteristic)) {
                $vals = [$decoded->additionalCharacteristic];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADDITIONAL_CHARACTERISTIC, true);
            } else {
                $vals = $decoded->additionalCharacteristic;
            }
            foreach($vals as $v) {
                $type->addAdditionalCharacteristic(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->additionalClassification) || property_exists($decoded, self::FIELD_ADDITIONAL_CLASSIFICATION)) {
            if (is_object($decoded->additionalClassification)) {
                $vals = [$decoded->additionalClassification];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADDITIONAL_CLASSIFICATION, true);
            } else {
                $vals = $decoded->additionalClassification;
            }
            foreach($vals as $v) {
                $type->addAdditionalClassification(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->relatedEntry) || property_exists($decoded, self::FIELD_RELATED_ENTRY)) {
            if (is_object($decoded->relatedEntry)) {
                $vals = [$decoded->relatedEntry];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RELATED_ENTRY, true);
            } else {
                $vals = $decoded->relatedEntry;
            }
            foreach($vals as $v) {
                $type->addRelatedEntry(FHIRCatalogEntryRelatedEntry::jsonUnserialize($v, $config));
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
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->orderable)) {
            if (null !== ($val = $this->orderable->getValue())) {
                $out->orderable = $val;
            }
            if ($this->orderable->_nonValueFieldDefined()) {
                $ext = $this->orderable->jsonSerialize();
                unset($ext->value);
                $out->_orderable = $ext;
            }
        }
        if (isset($this->referencedItem)) {
            $out->referencedItem = $this->referencedItem;
        }
        if (isset($this->additionalIdentifier) && [] !== $this->additionalIdentifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADDITIONAL_IDENTIFIER) && 1 === count($this->additionalIdentifier)) {
                $out->additionalIdentifier = $this->additionalIdentifier[0];
            } else {
                $out->additionalIdentifier = $this->additionalIdentifier;
            }
        }
        if (isset($this->classification) && [] !== $this->classification) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CLASSIFICATION) && 1 === count($this->classification)) {
                $out->classification = $this->classification[0];
            } else {
                $out->classification = $this->classification;
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
        if (isset($this->validityPeriod)) {
            $out->validityPeriod = $this->validityPeriod;
        }
        if (isset($this->validTo)) {
            if (null !== ($val = $this->validTo->getValue())) {
                $out->validTo = $val;
            }
            if ($this->validTo->_nonValueFieldDefined()) {
                $ext = $this->validTo->jsonSerialize();
                unset($ext->value);
                $out->_validTo = $ext;
            }
        }
        if (isset($this->lastUpdated)) {
            if (null !== ($val = $this->lastUpdated->getValue())) {
                $out->lastUpdated = $val;
            }
            if ($this->lastUpdated->_nonValueFieldDefined()) {
                $ext = $this->lastUpdated->jsonSerialize();
                unset($ext->value);
                $out->_lastUpdated = $ext;
            }
        }
        if (isset($this->additionalCharacteristic) && [] !== $this->additionalCharacteristic) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADDITIONAL_CHARACTERISTIC) && 1 === count($this->additionalCharacteristic)) {
                $out->additionalCharacteristic = $this->additionalCharacteristic[0];
            } else {
                $out->additionalCharacteristic = $this->additionalCharacteristic;
            }
        }
        if (isset($this->additionalClassification) && [] !== $this->additionalClassification) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADDITIONAL_CLASSIFICATION) && 1 === count($this->additionalClassification)) {
                $out->additionalClassification = $this->additionalClassification[0];
            } else {
                $out->additionalClassification = $this->additionalClassification;
            }
        }
        if (isset($this->relatedEntry) && [] !== $this->relatedEntry) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELATED_ENTRY) && 1 === count($this->relatedEntry)) {
                $out->relatedEntry = $this->relatedEntry[0];
            } else {
                $out->relatedEntry = $this->relatedEntry;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
