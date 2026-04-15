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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRBiologicallyDerivedProductCategoryList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRBiologicallyDerivedProductStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductCollection;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductManipulation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductCategory;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * A material substance originating from a biological entity intended to be
 * transplanted or infused into another (possibly the same) biological entity.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRBiologicallyDerivedProduct extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_BIOLOGICALLY_DERIVED_PRODUCT;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_PRODUCT_CATEGORY = 'productCategory';
    public const FIELD_PRODUCT_CATEGORY_EXT = '_productCategory';
    public const FIELD_PRODUCT_CODE = 'productCode';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_REQUEST = 'request';
    public const FIELD_QUANTITY = 'quantity';
    public const FIELD_QUANTITY_EXT = '_quantity';
    public const FIELD_PARENT = 'parent';
    public const FIELD_COLLECTION = 'collection';
    public const FIELD_PROCESSING = 'processing';
    public const FIELD_MANIPULATION = 'manipulation';
    public const FIELD_STORAGE = 'storage';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_PRODUCT_CATEGORY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_QUANTITY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This records identifiers associated with this biologically derived product
     * instance that are defined by business processes and/or used to refer to it when
     * a direct URL reference to the resource itself is not appropriate (e.g. in CDA
     * documents, or in written / printed documentation).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * Biologically Derived Product Category.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Broad category of this product.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductCategory
     */
    #[FHIRBiologicallyDerivedProductCategory]
    protected FHIRBiologicallyDerivedProductCategory $productCategory;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that identifies the kind of this biologically derived product (SNOMED
     * Ctcode).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $productCode;
    /**
     * Biologically Derived Product Status.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the product is currently available.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductStatus
     */
    #[FHIRBiologicallyDerivedProductStatus]
    protected FHIRBiologicallyDerivedProductStatus $status;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Procedure request to obtain this biologically derived product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $request;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Number of discrete units within this product.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $quantity;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Parent product (if any).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $parent;
    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * How this product was collected.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductCollection
     */
    #[FHIRBiologicallyDerivedProductCollection]
    protected FHIRBiologicallyDerivedProductCollection $collection;
    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Any processing of the product during collection that does not change the
     * fundamental nature of the product. For example adding anti-coagulants during the
     * collection of Peripheral Blood Stem Cells.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing>
     */
    #[FHIRBiologicallyDerivedProductProcessing]
    protected array $processing;
    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Any manipulation of product post-collection that is intended to alter the
     * product. For example a buffy-coat enrichment or CD8 reduction of Peripheral
     * Blood Stem Cells to make it more suitable for infusion.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductManipulation
     */
    #[FHIRBiologicallyDerivedProductManipulation]
    protected FHIRBiologicallyDerivedProductManipulation $manipulation;
    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Product storage.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage>
     */
    #[FHIRBiologicallyDerivedProductStorage]
    protected array $storage;

    /* constructor.php:61 */
    /**
     * FHIRBiologicallyDerivedProduct Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRBiologicallyDerivedProductCategoryList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductCategory $productCategory
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $productCode
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRBiologicallyDerivedProductStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductStatus $status
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $request
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $quantity
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $parent
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductCollection $collection
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing> $processing
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductManipulation $manipulation
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage> $storage
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
                                null|string|FHIRBiologicallyDerivedProductCategoryList|FHIRBiologicallyDerivedProductCategory $productCategory = null,
                                null|FHIRCodeableConcept $productCode = null,
                                null|string|FHIRBiologicallyDerivedProductStatusList|FHIRBiologicallyDerivedProductStatus $status = null,
                                null|iterable $request = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $quantity = null,
                                null|iterable $parent = null,
                                null|FHIRBiologicallyDerivedProductCollection $collection = null,
                                null|iterable $processing = null,
                                null|FHIRBiologicallyDerivedProductManipulation $manipulation = null,
                                null|iterable $storage = null,
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
        if (null !== $productCategory) {
            $this->setProductCategory($productCategory);
        }
        if (null !== $productCode) {
            $this->setProductCode($productCode);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $request) {
            $this->setRequest(...$request);
        }
        if (null !== $quantity) {
            $this->setQuantity($quantity);
        }
        if (null !== $parent) {
            $this->setParent(...$parent);
        }
        if (null !== $collection) {
            $this->setCollection($collection);
        }
        if (null !== $processing) {
            $this->setProcessing(...$processing);
        }
        if (null !== $manipulation) {
            $this->setManipulation($manipulation);
        }
        if (null !== $storage) {
            $this->setStorage(...$storage);
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
     * This records identifiers associated with this biologically derived product
     * instance that are defined by business processes and/or used to refer to it when
     * a direct URL reference to the resource itself is not appropriate (e.g. in CDA
     * documents, or in written / printed documentation).
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
     * This records identifiers associated with this biologically derived product
     * instance that are defined by business processes and/or used to refer to it when
     * a direct URL reference to the resource itself is not appropriate (e.g. in CDA
     * documents, or in written / printed documentation).
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
     * This records identifiers associated with this biologically derived product
     * instance that are defined by business processes and/or used to refer to it when
     * a direct URL reference to the resource itself is not appropriate (e.g. in CDA
     * documents, or in written / printed documentation).
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
     * Biologically Derived Product Category.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Broad category of this product.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductCategory
     */
    public function getProductCategory(): null|FHIRBiologicallyDerivedProductCategory
    {
        return $this->productCategory ?? null;
    }

    /**
     * Biologically Derived Product Category.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Broad category of this product.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRBiologicallyDerivedProductCategoryList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductCategory $productCategory
     * @return static
     */
    public function setProductCategory(null|string|FHIRBiologicallyDerivedProductCategoryList|FHIRBiologicallyDerivedProductCategory $productCategory): self
    {
        if (null === $productCategory) {
            unset($this->productCategory);
            return $this;
        }
        if (!($productCategory instanceof FHIRBiologicallyDerivedProductCategory)) {
            $productCategory = new FHIRBiologicallyDerivedProductCategory(value: $productCategory);
        }
        $this->productCategory = $productCategory;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that identifies the kind of this biologically derived product (SNOMED
     * Ctcode).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getProductCode(): null|FHIRCodeableConcept
    {
        return $this->productCode ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that identifies the kind of this biologically derived product (SNOMED
     * Ctcode).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $productCode
     * @return static
     */
    public function setProductCode(null|FHIRCodeableConcept $productCode): self
    {
        if (null === $productCode) {
            unset($this->productCode);
            return $this;
        }
        $this->productCode = $productCode;
        return $this;
    }

    /**
     * Biologically Derived Product Status.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the product is currently available.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductStatus
     */
    public function getStatus(): null|FHIRBiologicallyDerivedProductStatus
    {
        return $this->status ?? null;
    }

    /**
     * Biologically Derived Product Status.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the product is currently available.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRBiologicallyDerivedProductStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBiologicallyDerivedProductStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRBiologicallyDerivedProductStatusList|FHIRBiologicallyDerivedProductStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRBiologicallyDerivedProductStatus)) {
            $status = new FHIRBiologicallyDerivedProductStatus(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Procedure request to obtain this biologically derived product.
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
     * Procedure request to obtain this biologically derived product.
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
     * Procedure request to obtain this biologically derived product.
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
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Number of discrete units within this product.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getQuantity(): null|FHIRInteger
    {
        return $this->quantity ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Number of discrete units within this product.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $quantity
     * @return static
     */
    public function setQuantity(null|string|float|FHIRIntegerPrimitive|FHIRInteger $quantity): self
    {
        if (null === $quantity) {
            unset($this->quantity);
            return $this;
        }
        if (!($quantity instanceof FHIRInteger)) {
            $quantity = new FHIRInteger(value: $quantity);
        }
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Parent product (if any).
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
     * Parent product (if any).
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
     * Parent product (if any).
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
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * How this product was collected.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductCollection
     */
    public function getCollection(): null|FHIRBiologicallyDerivedProductCollection
    {
        return $this->collection ?? null;
    }

    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * How this product was collected.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductCollection $collection
     * @return static
     */
    public function setCollection(null|FHIRBiologicallyDerivedProductCollection $collection): self
    {
        if (null === $collection) {
            unset($this->collection);
            return $this;
        }
        $this->collection = $collection;
        return $this;
    }

    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Any processing of the product during collection that does not change the
     * fundamental nature of the product. For example adding anti-coagulants during the
     * collection of Peripheral Blood Stem Cells.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing>
     */
    public function getProcessing(): array
    {
        return $this->processing ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing>
     */
    public function getProcessingIterator(): iterable
    {
        if (!isset($this->processing)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->processing);
    }

    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Any processing of the product during collection that does not change the
     * fundamental nature of the product. For example adding anti-coagulants during the
     * collection of Peripheral Blood Stem Cells.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing $processing
     * @return static
     */
    public function addProcessing(FHIRBiologicallyDerivedProductProcessing $processing): self
    {
        if (!isset($this->processing)) {
            $this->processing = [];
        }
        $this->processing[] = $processing;
        return $this;
    }

    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Any processing of the product during collection that does not change the
     * fundamental nature of the product. For example adding anti-coagulants during the
     * collection of Peripheral Blood Stem Cells.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductProcessing ...$processing
     * @return static
     */
    public function setProcessing(FHIRBiologicallyDerivedProductProcessing ...$processing): self
    {
        if ([] === $processing) {
            unset($this->processing);
            return $this;
        }
        $this->processing = $processing;
        return $this;
    }

    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Any manipulation of product post-collection that is intended to alter the
     * product. For example a buffy-coat enrichment or CD8 reduction of Peripheral
     * Blood Stem Cells to make it more suitable for infusion.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductManipulation
     */
    public function getManipulation(): null|FHIRBiologicallyDerivedProductManipulation
    {
        return $this->manipulation ?? null;
    }

    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Any manipulation of product post-collection that is intended to alter the
     * product. For example a buffy-coat enrichment or CD8 reduction of Peripheral
     * Blood Stem Cells to make it more suitable for infusion.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductManipulation $manipulation
     * @return static
     */
    public function setManipulation(null|FHIRBiologicallyDerivedProductManipulation $manipulation): self
    {
        if (null === $manipulation) {
            unset($this->manipulation);
            return $this;
        }
        $this->manipulation = $manipulation;
        return $this;
    }

    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Product storage.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage>
     */
    public function getStorage(): array
    {
        return $this->storage ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage>
     */
    public function getStorageIterator(): iterable
    {
        if (!isset($this->storage)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->storage);
    }

    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Product storage.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage $storage
     * @return static
     */
    public function addStorage(FHIRBiologicallyDerivedProductStorage $storage): self
    {
        if (!isset($this->storage)) {
            $this->storage = [];
        }
        $this->storage[] = $storage;
        return $this;
    }

    /**
     * A material substance originating from a biological entity intended to be
     * transplanted or infused into another (possibly the same) biological entity.
     *
     * Product storage.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBiologicallyDerivedProduct\FHIRBiologicallyDerivedProductStorage ...$storage
     * @return static
     */
    public function setStorage(FHIRBiologicallyDerivedProductStorage ...$storage): self
    {
        if ([] === $storage) {
            unset($this->storage);
            return $this;
        }
        $this->storage = $storage;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBiologicallyDerivedProduct $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBiologicallyDerivedProduct
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRBiologicallyDerivedProduct)) {
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
            } else if (self::FIELD_PRODUCT_CATEGORY === $cen) {
                $type->setProductCategory(FHIRBiologicallyDerivedProductCategory::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRODUCT_CODE === $cen) {
                $type->setProductCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRBiologicallyDerivedProductStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST === $cen) {
                $type->addRequest(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_QUANTITY === $cen) {
                $type->setQuantity(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARENT === $cen) {
                $type->addParent(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COLLECTION === $cen) {
                $type->setCollection(FHIRBiologicallyDerivedProductCollection::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROCESSING === $cen) {
                $type->addProcessing(FHIRBiologicallyDerivedProductProcessing::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANIPULATION === $cen) {
                $type->setManipulation(FHIRBiologicallyDerivedProductManipulation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STORAGE === $cen) {
                $type->addStorage(FHIRBiologicallyDerivedProductStorage::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_PRODUCT_CATEGORY])) {
            if (isset($type->productCategory)) {
                $type->productCategory->setValue((string)$attributes[self::FIELD_PRODUCT_CATEGORY]);
            } else {
                $type->setProductCategory((string)$attributes[self::FIELD_PRODUCT_CATEGORY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PRODUCT_CATEGORY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_QUANTITY])) {
            if (isset($type->quantity)) {
                $type->quantity->setValue((string)$attributes[self::FIELD_QUANTITY]);
            } else {
                $type->setQuantity((string)$attributes[self::FIELD_QUANTITY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_QUANTITY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('BiologicallyDerivedProduct', $this->_getSourceXMLNS());
        }
        if (isset($this->productCategory) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PRODUCT_CATEGORY]) {
            $xw->writeAttribute(self::FIELD_PRODUCT_CATEGORY, $this->productCategory->_getValueAsString());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->quantity) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_QUANTITY]) {
            $xw->writeAttribute(self::FIELD_QUANTITY, $this->quantity->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->productCategory)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PRODUCT_CATEGORY]
                || $this->productCategory->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PRODUCT_CATEGORY);
            $this->productCategory->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PRODUCT_CATEGORY]);
            $xw->endElement();
        }
        if (isset($this->productCode)) {
            $xw->startElement(self::FIELD_PRODUCT_CODE);
            $this->productCode->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->request)) {
            foreach ($this->request as $v) {
                $xw->startElement(self::FIELD_REQUEST);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->quantity)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_QUANTITY]
                || $this->quantity->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_QUANTITY);
            $this->quantity->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_QUANTITY]);
            $xw->endElement();
        }
        if (isset($this->parent)) {
            foreach ($this->parent as $v) {
                $xw->startElement(self::FIELD_PARENT);
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
        if (isset($this->manipulation)) {
            $xw->startElement(self::FIELD_MANIPULATION);
            $this->manipulation->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->storage)) {
            foreach ($this->storage as $v) {
                $xw->startElement(self::FIELD_STORAGE);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBiologicallyDerivedProduct $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRBiologicallyDerivedProduct
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
        } else if (!($type instanceof FHIRBiologicallyDerivedProduct)) {
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
        if (isset($decoded->productCategory)
            || isset($decoded->_productCategory)
            || property_exists($decoded, self::FIELD_PRODUCT_CATEGORY)
            || property_exists($decoded, self::FIELD_PRODUCT_CATEGORY_EXT)) {
            $v = $decoded->_productCategory ?? new \stdClass();
            $v->value = $decoded->productCategory ?? null;
            $type->setProductCategory(FHIRBiologicallyDerivedProductCategory::jsonUnserialize($v, $config));
        }
        if (isset($decoded->productCode) || property_exists($decoded, self::FIELD_PRODUCT_CODE)) {
            if (is_array($decoded->productCode)) {
                $type->setProductCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->productCode), $config));
            } else {
                $type->setProductCode(FHIRCodeableConcept::jsonUnserialize($decoded->productCode, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRBiologicallyDerivedProductStatus::jsonUnserialize($v, $config));
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
        if (isset($decoded->quantity)
            || isset($decoded->_quantity)
            || property_exists($decoded, self::FIELD_QUANTITY)
            || property_exists($decoded, self::FIELD_QUANTITY_EXT)) {
            $v = $decoded->_quantity ?? new \stdClass();
            $v->value = $decoded->quantity ?? null;
            $type->setQuantity(FHIRInteger::jsonUnserialize($v, $config));
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
        if (isset($decoded->collection) || property_exists($decoded, self::FIELD_COLLECTION)) {
            if (is_array($decoded->collection)) {
                $type->setCollection(FHIRBiologicallyDerivedProductCollection::jsonUnserialize(reset($decoded->collection), $config));
            } else {
                $type->setCollection(FHIRBiologicallyDerivedProductCollection::jsonUnserialize($decoded->collection, $config));
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
                $type->addProcessing(FHIRBiologicallyDerivedProductProcessing::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->manipulation) || property_exists($decoded, self::FIELD_MANIPULATION)) {
            if (is_array($decoded->manipulation)) {
                $type->setManipulation(FHIRBiologicallyDerivedProductManipulation::jsonUnserialize(reset($decoded->manipulation), $config));
            } else {
                $type->setManipulation(FHIRBiologicallyDerivedProductManipulation::jsonUnserialize($decoded->manipulation, $config));
            }
        }
        if (isset($decoded->storage) || property_exists($decoded, self::FIELD_STORAGE)) {
            if (is_object($decoded->storage)) {
                $vals = [$decoded->storage];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_STORAGE, true);
            } else {
                $vals = $decoded->storage;
            }
            foreach($vals as $v) {
                $type->addStorage(FHIRBiologicallyDerivedProductStorage::jsonUnserialize($v, $config));
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
        if (isset($this->productCategory)) {
            if (null !== ($val = $this->productCategory->getValue())) {
                $out->productCategory = $val;
            }
            if ($this->productCategory->_nonValueFieldDefined()) {
                $ext = $this->productCategory->jsonSerialize();
                unset($ext->value);
                $out->_productCategory = $ext;
            }
        }
        if (isset($this->productCode)) {
            $out->productCode = $this->productCode;
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
        if (isset($this->request) && [] !== $this->request) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REQUEST) && 1 === count($this->request)) {
                $out->request = $this->request[0];
            } else {
                $out->request = $this->request;
            }
        }
        if (isset($this->quantity)) {
            if (null !== ($val = $this->quantity->getValue())) {
                $out->quantity = $val;
            }
            if ($this->quantity->_nonValueFieldDefined()) {
                $ext = $this->quantity->jsonSerialize();
                unset($ext->value);
                $out->_quantity = $ext;
            }
        }
        if (isset($this->parent) && [] !== $this->parent) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PARENT) && 1 === count($this->parent)) {
                $out->parent = $this->parent[0];
            } else {
                $out->parent = $this->parent;
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
        if (isset($this->manipulation)) {
            $out->manipulation = $this->manipulation;
        }
        if (isset($this->storage) && [] !== $this->storage) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_STORAGE) && 1 === count($this->storage)) {
                $out->storage = $this->storage[0];
            } else {
                $out->storage = $this->storage;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
