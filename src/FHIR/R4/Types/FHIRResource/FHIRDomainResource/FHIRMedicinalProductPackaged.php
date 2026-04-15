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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedBatchIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
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
 * A medicinal product in a container or package.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicinalProductPackaged extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICINAL_PRODUCT_PACKAGED;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_LEGAL_STATUS_OF_SUPPLY = 'legalStatusOfSupply';
    public const FIELD_MARKETING_STATUS = 'marketingStatus';
    public const FIELD_MARKETING_AUTHORIZATION = 'marketingAuthorization';
    public const FIELD_MANUFACTURER = 'manufacturer';
    public const FIELD_BATCH_IDENTIFIER = 'batchIdentifier';
    public const FIELD_PACKAGE_ITEM = 'packageItem';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_PACKAGE_ITEM => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The product with this is a pack for.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $subject;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Textual description.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The legal status of supply of the medicinal product as classified by the
     * regulator.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $legalStatusOfSupply;
    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing information.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus>
     */
    #[FHIRMarketingStatus]
    protected array $marketingStatus;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Package Item.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $marketingAuthorization;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Package Item.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $manufacturer;
    /**
     * A medicinal product in a container or package.
     *
     * Batch numbering.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedBatchIdentifier>
     */
    #[FHIRMedicinalProductPackagedBatchIdentifier]
    protected array $batchIdentifier;
    /**
     * A medicinal product in a container or package.
     *
     * A packaging item, as a contained for medicine, possibly with other packaging
     * items within.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem>
     */
    #[FHIRMedicinalProductPackagedPackageItem]
    protected array $packageItem;

    /* constructor.php:61 */
    /**
     * FHIRMedicinalProductPackaged Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $subject
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $legalStatusOfSupply
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus> $marketingStatus
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $marketingAuthorization
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $manufacturer
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedBatchIdentifier> $batchIdentifier
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem> $packageItem
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
                                null|iterable $subject = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|FHIRCodeableConcept $legalStatusOfSupply = null,
                                null|iterable $marketingStatus = null,
                                null|FHIRReference $marketingAuthorization = null,
                                null|iterable $manufacturer = null,
                                null|iterable $batchIdentifier = null,
                                null|iterable $packageItem = null,
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
        if (null !== $subject) {
            $this->setSubject(...$subject);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $legalStatusOfSupply) {
            $this->setLegalStatusOfSupply($legalStatusOfSupply);
        }
        if (null !== $marketingStatus) {
            $this->setMarketingStatus(...$marketingStatus);
        }
        if (null !== $marketingAuthorization) {
            $this->setMarketingAuthorization($marketingAuthorization);
        }
        if (null !== $manufacturer) {
            $this->setManufacturer(...$manufacturer);
        }
        if (null !== $batchIdentifier) {
            $this->setBatchIdentifier(...$batchIdentifier);
        }
        if (null !== $packageItem) {
            $this->setPackageItem(...$packageItem);
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
     * Unique identifier.
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
     * Unique identifier.
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
     * Unique identifier.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The product with this is a pack for.
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
     * The product with this is a pack for.
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
     * The product with this is a pack for.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Textual description.
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
     * Textual description.
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
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The legal status of supply of the medicinal product as classified by the
     * regulator.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getLegalStatusOfSupply(): null|FHIRCodeableConcept
    {
        return $this->legalStatusOfSupply ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The legal status of supply of the medicinal product as classified by the
     * regulator.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $legalStatusOfSupply
     * @return static
     */
    public function setLegalStatusOfSupply(null|FHIRCodeableConcept $legalStatusOfSupply): self
    {
        if (null === $legalStatusOfSupply) {
            unset($this->legalStatusOfSupply);
            return $this;
        }
        $this->legalStatusOfSupply = $legalStatusOfSupply;
        return $this;
    }

    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing information.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus>
     */
    public function getMarketingStatus(): array
    {
        return $this->marketingStatus ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus>
     */
    public function getMarketingStatusIterator(): iterable
    {
        if (!isset($this->marketingStatus)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->marketingStatus);
    }

    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing information.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus $marketingStatus
     * @return static
     */
    public function addMarketingStatus(FHIRMarketingStatus $marketingStatus): self
    {
        if (!isset($this->marketingStatus)) {
            $this->marketingStatus = [];
        }
        $this->marketingStatus[] = $marketingStatus;
        return $this;
    }

    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing information.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus ...$marketingStatus
     * @return static
     */
    public function setMarketingStatus(FHIRMarketingStatus ...$marketingStatus): self
    {
        if ([] === $marketingStatus) {
            unset($this->marketingStatus);
            return $this;
        }
        $this->marketingStatus = $marketingStatus;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Package Item.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getMarketingAuthorization(): null|FHIRReference
    {
        return $this->marketingAuthorization ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Package Item.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $marketingAuthorization
     * @return static
     */
    public function setMarketingAuthorization(null|FHIRReference $marketingAuthorization): self
    {
        if (null === $marketingAuthorization) {
            unset($this->marketingAuthorization);
            return $this;
        }
        $this->marketingAuthorization = $marketingAuthorization;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Package Item.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getManufacturer(): array
    {
        return $this->manufacturer ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getManufacturerIterator(): iterable
    {
        if (!isset($this->manufacturer)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->manufacturer);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Package Item.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $manufacturer
     * @return static
     */
    public function addManufacturer(FHIRReference $manufacturer): self
    {
        if (!isset($this->manufacturer)) {
            $this->manufacturer = [];
        }
        $this->manufacturer[] = $manufacturer;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Manufacturer of this Package Item.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$manufacturer
     * @return static
     */
    public function setManufacturer(FHIRReference ...$manufacturer): self
    {
        if ([] === $manufacturer) {
            unset($this->manufacturer);
            return $this;
        }
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * A medicinal product in a container or package.
     *
     * Batch numbering.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedBatchIdentifier>
     */
    public function getBatchIdentifier(): array
    {
        return $this->batchIdentifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedBatchIdentifier>
     */
    public function getBatchIdentifierIterator(): iterable
    {
        if (!isset($this->batchIdentifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->batchIdentifier);
    }

    /**
     * A medicinal product in a container or package.
     *
     * Batch numbering.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedBatchIdentifier $batchIdentifier
     * @return static
     */
    public function addBatchIdentifier(FHIRMedicinalProductPackagedBatchIdentifier $batchIdentifier): self
    {
        if (!isset($this->batchIdentifier)) {
            $this->batchIdentifier = [];
        }
        $this->batchIdentifier[] = $batchIdentifier;
        return $this;
    }

    /**
     * A medicinal product in a container or package.
     *
     * Batch numbering.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedBatchIdentifier ...$batchIdentifier
     * @return static
     */
    public function setBatchIdentifier(FHIRMedicinalProductPackagedBatchIdentifier ...$batchIdentifier): self
    {
        if ([] === $batchIdentifier) {
            unset($this->batchIdentifier);
            return $this;
        }
        $this->batchIdentifier = $batchIdentifier;
        return $this;
    }

    /**
     * A medicinal product in a container or package.
     *
     * A packaging item, as a contained for medicine, possibly with other packaging
     * items within.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem>
     */
    public function getPackageItem(): array
    {
        return $this->packageItem ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem>
     */
    public function getPackageItemIterator(): iterable
    {
        if (!isset($this->packageItem)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->packageItem);
    }

    /**
     * A medicinal product in a container or package.
     *
     * A packaging item, as a contained for medicine, possibly with other packaging
     * items within.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem $packageItem
     * @return static
     */
    public function addPackageItem(FHIRMedicinalProductPackagedPackageItem $packageItem): self
    {
        if (!isset($this->packageItem)) {
            $this->packageItem = [];
        }
        $this->packageItem[] = $packageItem;
        return $this;
    }

    /**
     * A medicinal product in a container or package.
     *
     * A packaging item, as a contained for medicine, possibly with other packaging
     * items within.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPackaged\FHIRMedicinalProductPackagedPackageItem ...$packageItem
     * @return static
     */
    public function setPackageItem(FHIRMedicinalProductPackagedPackageItem ...$packageItem): self
    {
        if ([] === $packageItem) {
            unset($this->packageItem);
            return $this;
        }
        $this->packageItem = $packageItem;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductPackaged $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductPackaged
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicinalProductPackaged)) {
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
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->addSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LEGAL_STATUS_OF_SUPPLY === $cen) {
                $type->setLegalStatusOfSupply(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MARKETING_STATUS === $cen) {
                $type->addMarketingStatus(FHIRMarketingStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MARKETING_AUTHORIZATION === $cen) {
                $type->setMarketingAuthorization(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANUFACTURER === $cen) {
                $type->addManufacturer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BATCH_IDENTIFIER === $cen) {
                $type->addBatchIdentifier(FHIRMedicinalProductPackagedBatchIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PACKAGE_ITEM === $cen) {
                $type->addPackageItem(FHIRMedicinalProductPackagedPackageItem::xmlUnserialize($ce, $config));
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
            $xw->openRootNode('MedicinalProductPackaged', $this->_getSourceXMLNS());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->subject)) {
            foreach ($this->subject as $v) {
                $xw->startElement(self::FIELD_SUBJECT);
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
        if (isset($this->legalStatusOfSupply)) {
            $xw->startElement(self::FIELD_LEGAL_STATUS_OF_SUPPLY);
            $this->legalStatusOfSupply->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->marketingStatus)) {
            foreach ($this->marketingStatus as $v) {
                $xw->startElement(self::FIELD_MARKETING_STATUS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->marketingAuthorization)) {
            $xw->startElement(self::FIELD_MARKETING_AUTHORIZATION);
            $this->marketingAuthorization->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->manufacturer)) {
            foreach ($this->manufacturer as $v) {
                $xw->startElement(self::FIELD_MANUFACTURER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->batchIdentifier)) {
            foreach ($this->batchIdentifier as $v) {
                $xw->startElement(self::FIELD_BATCH_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->packageItem)) {
            foreach ($this->packageItem as $v) {
                $xw->startElement(self::FIELD_PACKAGE_ITEM);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductPackaged $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductPackaged
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
        } else if (!($type instanceof FHIRMedicinalProductPackaged)) {
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
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->legalStatusOfSupply) || property_exists($decoded, self::FIELD_LEGAL_STATUS_OF_SUPPLY)) {
            if (is_array($decoded->legalStatusOfSupply)) {
                $type->setLegalStatusOfSupply(FHIRCodeableConcept::jsonUnserialize(reset($decoded->legalStatusOfSupply), $config));
            } else {
                $type->setLegalStatusOfSupply(FHIRCodeableConcept::jsonUnserialize($decoded->legalStatusOfSupply, $config));
            }
        }
        if (isset($decoded->marketingStatus) || property_exists($decoded, self::FIELD_MARKETING_STATUS)) {
            if (is_object($decoded->marketingStatus)) {
                $vals = [$decoded->marketingStatus];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MARKETING_STATUS, true);
            } else {
                $vals = $decoded->marketingStatus;
            }
            foreach($vals as $v) {
                $type->addMarketingStatus(FHIRMarketingStatus::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->marketingAuthorization) || property_exists($decoded, self::FIELD_MARKETING_AUTHORIZATION)) {
            if (is_array($decoded->marketingAuthorization)) {
                $type->setMarketingAuthorization(FHIRReference::jsonUnserialize(reset($decoded->marketingAuthorization), $config));
            } else {
                $type->setMarketingAuthorization(FHIRReference::jsonUnserialize($decoded->marketingAuthorization, $config));
            }
        }
        if (isset($decoded->manufacturer) || property_exists($decoded, self::FIELD_MANUFACTURER)) {
            if (is_object($decoded->manufacturer)) {
                $vals = [$decoded->manufacturer];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MANUFACTURER, true);
            } else {
                $vals = $decoded->manufacturer;
            }
            foreach($vals as $v) {
                $type->addManufacturer(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->batchIdentifier) || property_exists($decoded, self::FIELD_BATCH_IDENTIFIER)) {
            if (is_object($decoded->batchIdentifier)) {
                $vals = [$decoded->batchIdentifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_BATCH_IDENTIFIER, true);
            } else {
                $vals = $decoded->batchIdentifier;
            }
            foreach($vals as $v) {
                $type->addBatchIdentifier(FHIRMedicinalProductPackagedBatchIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->packageItem) || property_exists($decoded, self::FIELD_PACKAGE_ITEM)) {
            if (is_object($decoded->packageItem)) {
                $vals = [$decoded->packageItem];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PACKAGE_ITEM, true);
            } else {
                $vals = $decoded->packageItem;
            }
            foreach($vals as $v) {
                $type->addPackageItem(FHIRMedicinalProductPackagedPackageItem::jsonUnserialize($v, $config));
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
        if (isset($this->subject) && [] !== $this->subject) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUBJECT) && 1 === count($this->subject)) {
                $out->subject = $this->subject[0];
            } else {
                $out->subject = $this->subject;
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
        if (isset($this->legalStatusOfSupply)) {
            $out->legalStatusOfSupply = $this->legalStatusOfSupply;
        }
        if (isset($this->marketingStatus) && [] !== $this->marketingStatus) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MARKETING_STATUS) && 1 === count($this->marketingStatus)) {
                $out->marketingStatus = $this->marketingStatus[0];
            } else {
                $out->marketingStatus = $this->marketingStatus;
            }
        }
        if (isset($this->marketingAuthorization)) {
            $out->marketingAuthorization = $this->marketingAuthorization;
        }
        if (isset($this->manufacturer) && [] !== $this->manufacturer) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MANUFACTURER) && 1 === count($this->manufacturer)) {
                $out->manufacturer = $this->manufacturer[0];
            } else {
                $out->manufacturer = $this->manufacturer;
            }
        }
        if (isset($this->batchIdentifier) && [] !== $this->batchIdentifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_BATCH_IDENTIFIER) && 1 === count($this->batchIdentifier)) {
                $out->batchIdentifier = $this->batchIdentifier[0];
            } else {
                $out->batchIdentifier = $this->batchIdentifier;
            }
        }
        if (isset($this->packageItem) && [] !== $this->packageItem) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PACKAGE_ITEM) && 1 === count($this->packageItem)) {
                $out->packageItem = $this->packageItem[0];
            } else {
                $out->packageItem = $this->packageItem;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
