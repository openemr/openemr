<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRBundleTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBundleType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;

/**
 * A container for a collection of resources.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRBundle extends FHIRResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_BUNDLE;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_EXT = '_type';
    public const FIELD_TIMESTAMP = 'timestamp';
    public const FIELD_TIMESTAMP_EXT = '_timestamp';
    public const FIELD_TOTAL = 'total';
    public const FIELD_TOTAL_EXT = '_total';
    public const FIELD_LINK = 'link';
    public const FIELD_ENTRY = 'entry';
    public const FIELD_SIGNATURE = 'signature';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_TYPE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TIMESTAMP => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TOTAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A persistent identifier for the bundle that won't change as a bundle is copied
     * from server to server.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $identifier;
    /**
     * Indicates the purpose of a bundle - how it is intended to be used.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the purpose of this bundle - how it is intended to be used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBundleType
     */
    #[FHIRBundleType]
    protected FHIRBundleType $type;
    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date/time that the bundle was assembled - i.e. when the resources were
     * placed in the bundle.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    #[FHIRInstant]
    protected FHIRInstant $timestamp;
    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If a set of search matches, this is the total number of entries of type 'match'
     * across all pages in the search. It does not include search.mode = 'include' or
     * 'outcome' entries and it does not provide a count of the number of entries in
     * the Bundle.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    #[FHIRUnsignedInt]
    protected FHIRUnsignedInt $total;
    /**
     * A container for a collection of resources.
     *
     * A series of links that provide context to this bundle.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink>
     */
    #[FHIRBundleLink]
    protected array $link;
    /**
     * A container for a collection of resources.
     *
     * An entry in a bundle resource - will either contain a resource or information
     * about a resource (transactions and history only).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry>
     */
    #[FHIRBundleEntry]
    protected array $entry;
    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Digital Signature - base64 encoded. XML-DSig or a JWT.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    #[FHIRSignature]
    protected FHIRSignature $signature;

    /* constructor.php:61 */
    /**
     * FHIRBundle Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRBundleTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBundleType $type
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $timestamp
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $total
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink> $link
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry> $entry
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $signature
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRIdentifier $identifier = null,
                                null|string|FHIRBundleTypeList|FHIRBundleType $type = null,
                                null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $timestamp = null,
                                null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $total = null,
                                null|iterable $link = null,
                                null|iterable $entry = null,
                                null|FHIRSignature $signature = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            fhirComments: $fhirComments);
        if (null !== $identifier) {
            $this->setIdentifier($identifier);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $timestamp) {
            $this->setTimestamp($timestamp);
        }
        if (null !== $total) {
            $this->setTotal($total);
        }
        if (null !== $link) {
            $this->setLink(...$link);
        }
        if (null !== $entry) {
            $this->setEntry(...$entry);
        }
        if (null !== $signature) {
            $this->setSignature($signature);
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
     * A persistent identifier for the bundle that won't change as a bundle is copied
     * from server to server.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier(): null|FHIRIdentifier
    {
        return $this->identifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A persistent identifier for the bundle that won't change as a bundle is copied
     * from server to server.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function setIdentifier(null|FHIRIdentifier $identifier): self
    {
        if (null === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Indicates the purpose of a bundle - how it is intended to be used.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the purpose of this bundle - how it is intended to be used.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBundleType
     */
    public function getType(): null|FHIRBundleType
    {
        return $this->type ?? null;
    }

    /**
     * Indicates the purpose of a bundle - how it is intended to be used.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the purpose of this bundle - how it is intended to be used.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRBundleTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBundleType $type
     * @return static
     */
    public function setType(null|string|FHIRBundleTypeList|FHIRBundleType $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        if (!($type instanceof FHIRBundleType)) {
            $type = new FHIRBundleType(value: $type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date/time that the bundle was assembled - i.e. when the resources were
     * placed in the bundle.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    public function getTimestamp(): null|FHIRInstant
    {
        return $this->timestamp ?? null;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date/time that the bundle was assembled - i.e. when the resources were
     * placed in the bundle.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $timestamp
     * @return static
     */
    public function setTimestamp(null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $timestamp): self
    {
        if (null === $timestamp) {
            unset($this->timestamp);
            return $this;
        }
        if (!($timestamp instanceof FHIRInstant)) {
            $timestamp = new FHIRInstant(value: $timestamp);
        }
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If a set of search matches, this is the total number of entries of type 'match'
     * across all pages in the search. It does not include search.mode = 'include' or
     * 'outcome' entries and it does not provide a count of the number of entries in
     * the Bundle.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    public function getTotal(): null|FHIRUnsignedInt
    {
        return $this->total ?? null;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If a set of search matches, this is the total number of entries of type 'match'
     * across all pages in the search. It does not include search.mode = 'include' or
     * 'outcome' entries and it does not provide a count of the number of entries in
     * the Bundle.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $total
     * @return static
     */
    public function setTotal(null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $total): self
    {
        if (null === $total) {
            unset($this->total);
            return $this;
        }
        if (!($total instanceof FHIRUnsignedInt)) {
            $total = new FHIRUnsignedInt(value: $total);
        }
        $this->total = $total;
        return $this;
    }

    /**
     * A container for a collection of resources.
     *
     * A series of links that provide context to this bundle.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink>
     */
    public function getLink(): array
    {
        return $this->link ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink>
     */
    public function getLinkIterator(): iterable
    {
        if (!isset($this->link)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->link);
    }

    /**
     * A container for a collection of resources.
     *
     * A series of links that provide context to this bundle.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink $link
     * @return static
     */
    public function addLink(FHIRBundleLink $link): self
    {
        if (!isset($this->link)) {
            $this->link = [];
        }
        $this->link[] = $link;
        return $this;
    }

    /**
     * A container for a collection of resources.
     *
     * A series of links that provide context to this bundle.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink ...$link
     * @return static
     */
    public function setLink(FHIRBundleLink ...$link): self
    {
        if ([] === $link) {
            unset($this->link);
            return $this;
        }
        $this->link = $link;
        return $this;
    }

    /**
     * A container for a collection of resources.
     *
     * An entry in a bundle resource - will either contain a resource or information
     * about a resource (transactions and history only).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry>
     */
    public function getEntry(): array
    {
        return $this->entry ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry>
     */
    public function getEntryIterator(): iterable
    {
        if (!isset($this->entry)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->entry);
    }

    /**
     * A container for a collection of resources.
     *
     * An entry in a bundle resource - will either contain a resource or information
     * about a resource (transactions and history only).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry $entry
     * @return static
     */
    public function addEntry(FHIRBundleEntry $entry): self
    {
        if (!isset($this->entry)) {
            $this->entry = [];
        }
        $this->entry[] = $entry;
        return $this;
    }

    /**
     * A container for a collection of resources.
     *
     * An entry in a bundle resource - will either contain a resource or information
     * about a resource (transactions and history only).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry ...$entry
     * @return static
     */
    public function setEntry(FHIRBundleEntry ...$entry): self
    {
        if ([] === $entry) {
            unset($this->entry);
            return $this;
        }
        $this->entry = $entry;
        return $this;
    }

    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Digital Signature - base64 encoded. XML-DSig or a JWT.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    public function getSignature(): null|FHIRSignature
    {
        return $this->signature ?? null;
    }

    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Digital Signature - base64 encoded. XML-DSig or a JWT.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $signature
     * @return static
     */
    public function setSignature(null|FHIRSignature $signature): self
    {
        if (null === $signature) {
            unset($this->signature);
            return $this;
        }
        $this->signature = $signature;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBundle $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBundle
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRBundle)) {
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
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->setIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRBundleType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIMESTAMP === $cen) {
                $type->setTimestamp(FHIRInstant::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TOTAL === $cen) {
                $type->setTotal(FHIRUnsignedInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LINK === $cen) {
                $type->addLink(FHIRBundleLink::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENTRY === $cen) {
                $type->addEntry(FHIRBundleEntry::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SIGNATURE === $cen) {
                $type->setSignature(FHIRSignature::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_TYPE])) {
            if (isset($type->type)) {
                $type->type->setValue((string)$attributes[self::FIELD_TYPE]);
            } else {
                $type->setType((string)$attributes[self::FIELD_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TIMESTAMP])) {
            if (isset($type->timestamp)) {
                $type->timestamp->setValue((string)$attributes[self::FIELD_TIMESTAMP]);
            } else {
                $type->setTimestamp((string)$attributes[self::FIELD_TIMESTAMP]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TIMESTAMP, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TOTAL])) {
            if (isset($type->total)) {
                $type->total->setValue((string)$attributes[self::FIELD_TOTAL]);
            } else {
                $type->setTotal((string)$attributes[self::FIELD_TOTAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TOTAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('Bundle', $this->_getSourceXMLNS());
        }
        if (isset($this->type) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TYPE]) {
            $xw->writeAttribute(self::FIELD_TYPE, $this->type->_getValueAsString());
        }
        if (isset($this->timestamp) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TIMESTAMP]) {
            $xw->writeAttribute(self::FIELD_TIMESTAMP, $this->timestamp->_getValueAsString());
        }
        if (isset($this->total) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TOTAL]) {
            $xw->writeAttribute(self::FIELD_TOTAL, $this->total->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            $xw->startElement(self::FIELD_IDENTIFIER);
            $this->identifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TYPE]
                || $this->type->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TYPE]);
            $xw->endElement();
        }
        if (isset($this->timestamp)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TIMESTAMP]
                || $this->timestamp->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TIMESTAMP);
            $this->timestamp->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TIMESTAMP]);
            $xw->endElement();
        }
        if (isset($this->total)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TOTAL]
                || $this->total->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TOTAL);
            $this->total->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TOTAL]);
            $xw->endElement();
        }
        if (isset($this->link)) {
            foreach ($this->link as $v) {
                $xw->startElement(self::FIELD_LINK);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->entry)) {
            foreach ($this->entry as $v) {
                $xw->startElement(self::FIELD_ENTRY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->signature)) {
            $xw->startElement(self::FIELD_SIGNATURE);
            $this->signature->xmlSerialize($xw, $config);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBundle $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBundle
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
        } else if (!($type instanceof FHIRBundle)) {
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
            if (is_array($decoded->identifier)) {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->identifier), $config));
            } else {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize($decoded->identifier, $config));
            }
        }
        if (isset($decoded->type)
            || isset($decoded->_type)
            || property_exists($decoded, self::FIELD_TYPE)
            || property_exists($decoded, self::FIELD_TYPE_EXT)) {
            $v = $decoded->_type ?? new \stdClass();
            $v->value = $decoded->type ?? null;
            $type->setType(FHIRBundleType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->timestamp)
            || isset($decoded->_timestamp)
            || property_exists($decoded, self::FIELD_TIMESTAMP)
            || property_exists($decoded, self::FIELD_TIMESTAMP_EXT)) {
            $v = $decoded->_timestamp ?? new \stdClass();
            $v->value = $decoded->timestamp ?? null;
            $type->setTimestamp(FHIRInstant::jsonUnserialize($v, $config));
        }
        if (isset($decoded->total)
            || isset($decoded->_total)
            || property_exists($decoded, self::FIELD_TOTAL)
            || property_exists($decoded, self::FIELD_TOTAL_EXT)) {
            $v = $decoded->_total ?? new \stdClass();
            $v->value = $decoded->total ?? null;
            $type->setTotal(FHIRUnsignedInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->link) || property_exists($decoded, self::FIELD_LINK)) {
            if (is_object($decoded->link)) {
                $vals = [$decoded->link];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_LINK, true);
            } else {
                $vals = $decoded->link;
            }
            foreach($vals as $v) {
                $type->addLink(FHIRBundleLink::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->entry) || property_exists($decoded, self::FIELD_ENTRY)) {
            if (is_object($decoded->entry)) {
                $vals = [$decoded->entry];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ENTRY, true);
            } else {
                $vals = $decoded->entry;
            }
            foreach($vals as $v) {
                $type->addEntry(FHIRBundleEntry::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->signature) || property_exists($decoded, self::FIELD_SIGNATURE)) {
            if (is_array($decoded->signature)) {
                $type->setSignature(FHIRSignature::jsonUnserialize(reset($decoded->signature), $config));
            } else {
                $type->setSignature(FHIRSignature::jsonUnserialize($decoded->signature, $config));
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
        if (isset($this->identifier)) {
            $out->identifier = $this->identifier;
        }
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
        if (isset($this->timestamp)) {
            if (null !== ($val = $this->timestamp->getValue())) {
                $out->timestamp = $val;
            }
            if ($this->timestamp->_nonValueFieldDefined()) {
                $ext = $this->timestamp->jsonSerialize();
                unset($ext->value);
                $out->_timestamp = $ext;
            }
        }
        if (isset($this->total)) {
            if (null !== ($val = $this->total->getValue())) {
                $out->total = $val;
            }
            if ($this->total->_nonValueFieldDefined()) {
                $ext = $this->total->jsonSerialize();
                unset($ext->value);
                $out->_total = $ext;
            }
        }
        if (isset($this->link) && [] !== $this->link) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_LINK) && 1 === count($this->link)) {
                $out->link = $this->link[0];
            } else {
                $out->link = $this->link;
            }
        }
        if (isset($this->entry) && [] !== $this->entry) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ENTRY) && 1 === count($this->entry)) {
                $out->entry = $this->entry[0];
            } else {
                $out->entry = $this->entry;
            }
        }
        if (isset($this->signature)) {
            $out->signature = $this->signature;
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
