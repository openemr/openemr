<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A provider issued list of professional services and products which have been
 * provided, or are to be provided, to a patient which is sent to an insurer for
 * reimbursement.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRClaimInsurance extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CLAIM_DOT_INSURANCE;

    /* class_default.php:56 */
    public const FIELD_SEQUENCE = 'sequence';
    public const FIELD_SEQUENCE_EXT = '_sequence';
    public const FIELD_FOCAL = 'focal';
    public const FIELD_FOCAL_EXT = '_focal';
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_COVERAGE = 'coverage';
    public const FIELD_BUSINESS_ARRANGEMENT = 'businessArrangement';
    public const FIELD_BUSINESS_ARRANGEMENT_EXT = '_businessArrangement';
    public const FIELD_PRE_AUTH_REF = 'preAuthRef';
    public const FIELD_PRE_AUTH_REF_EXT = '_preAuthRef';
    public const FIELD_CLAIM_RESPONSE = 'claimResponse';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_SEQUENCE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_FOCAL => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_COVERAGE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SEQUENCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_FOCAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_BUSINESS_ARRANGEMENT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A number to uniquely identify insurance entries and provide a sequence of
     * coverages to convey coordination of benefit order.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $sequence;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag to indicate that this Coverage is to be used for adjudication of this
     * claim when set to true.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $focal;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The business identifier to be used when the claim is sent for adjudication
     * against this insurance policy.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $identifier;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the insurance card level information contained in the Coverage
     * resource. The coverage issuing insurer will use these details to locate the
     * patient's actual coverage within the insurer's information system.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $coverage;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A business agreement number established between the provider and the insurer for
     * special business processing purposes.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $businessArrangement;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference numbers previously provided by the insurer to the provider to be
     * quoted on subsequent claims containing services or products related to the prior
     * authorization.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $preAuthRef;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The result of the adjudication of the line items for the Coverage specified in
     * this insurance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $claimResponse;

    /* constructor.php:61 */
    /**
     * FHIRClaimInsurance Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $sequence
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $focal
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $coverage
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $businessArrangement
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $preAuthRef
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $claimResponse
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $sequence = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $focal = null,
                                null|FHIRIdentifier $identifier = null,
                                null|FHIRReference $coverage = null,
                                null|string|FHIRStringPrimitive|FHIRString $businessArrangement = null,
                                null|iterable $preAuthRef = null,
                                null|FHIRReference $claimResponse = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $sequence) {
            $this->setSequence($sequence);
        }
        if (null !== $focal) {
            $this->setFocal($focal);
        }
        if (null !== $identifier) {
            $this->setIdentifier($identifier);
        }
        if (null !== $coverage) {
            $this->setCoverage($coverage);
        }
        if (null !== $businessArrangement) {
            $this->setBusinessArrangement($businessArrangement);
        }
        if (null !== $preAuthRef) {
            $this->setPreAuthRef(...$preAuthRef);
        }
        if (null !== $claimResponse) {
            $this->setClaimResponse($claimResponse);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A number to uniquely identify insurance entries and provide a sequence of
     * coverages to convey coordination of benefit order.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getSequence(): null|FHIRPositiveInt
    {
        return $this->sequence ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A number to uniquely identify insurance entries and provide a sequence of
     * coverages to convey coordination of benefit order.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $sequence
     * @return static
     */
    public function setSequence(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $sequence): self
    {
        if (null === $sequence) {
            unset($this->sequence);
            return $this;
        }
        if (!($sequence instanceof FHIRPositiveInt)) {
            $sequence = new FHIRPositiveInt(value: $sequence);
        }
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag to indicate that this Coverage is to be used for adjudication of this
     * claim when set to true.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getFocal(): null|FHIRBoolean
    {
        return $this->focal ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A flag to indicate that this Coverage is to be used for adjudication of this
     * claim when set to true.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $focal
     * @return static
     */
    public function setFocal(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $focal): self
    {
        if (null === $focal) {
            unset($this->focal);
            return $this;
        }
        if (!($focal instanceof FHIRBoolean)) {
            $focal = new FHIRBoolean(value: $focal);
        }
        $this->focal = $focal;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The business identifier to be used when the claim is sent for adjudication
     * against this insurance policy.
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
     * The business identifier to be used when the claim is sent for adjudication
     * against this insurance policy.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the insurance card level information contained in the Coverage
     * resource. The coverage issuing insurer will use these details to locate the
     * patient's actual coverage within the insurer's information system.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getCoverage(): null|FHIRReference
    {
        return $this->coverage ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the insurance card level information contained in the Coverage
     * resource. The coverage issuing insurer will use these details to locate the
     * patient's actual coverage within the insurer's information system.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $coverage
     * @return static
     */
    public function setCoverage(null|FHIRReference $coverage): self
    {
        if (null === $coverage) {
            unset($this->coverage);
            return $this;
        }
        $this->coverage = $coverage;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A business agreement number established between the provider and the insurer for
     * special business processing purposes.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getBusinessArrangement(): null|FHIRString
    {
        return $this->businessArrangement ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A business agreement number established between the provider and the insurer for
     * special business processing purposes.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $businessArrangement
     * @return static
     */
    public function setBusinessArrangement(null|string|FHIRStringPrimitive|FHIRString $businessArrangement): self
    {
        if (null === $businessArrangement) {
            unset($this->businessArrangement);
            return $this;
        }
        if (!($businessArrangement instanceof FHIRString)) {
            $businessArrangement = new FHIRString(value: $businessArrangement);
        }
        $this->businessArrangement = $businessArrangement;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference numbers previously provided by the insurer to the provider to be
     * quoted on subsequent claims containing services or products related to the prior
     * authorization.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getPreAuthRef(): array
    {
        return $this->preAuthRef ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getPreAuthRefIterator(): iterable
    {
        if (!isset($this->preAuthRef)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->preAuthRef);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference numbers previously provided by the insurer to the provider to be
     * quoted on subsequent claims containing services or products related to the prior
     * authorization.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $preAuthRef
     * @return static
     */
    public function addPreAuthRef(string|FHIRStringPrimitive|FHIRString $preAuthRef): self
    {
        if (!($preAuthRef instanceof FHIRString)) {
            $preAuthRef = new FHIRString(value: $preAuthRef);
        }
        if (!isset($this->preAuthRef)) {
            $this->preAuthRef = [];
        }
        $this->preAuthRef[] = $preAuthRef;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference numbers previously provided by the insurer to the provider to be
     * quoted on subsequent claims containing services or products related to the prior
     * authorization.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$preAuthRef
     * @return static
     */
    public function setPreAuthRef(string|FHIRStringPrimitive|FHIRString ...$preAuthRef): self
    {
        if ([] === $preAuthRef) {
            unset($this->preAuthRef);
            return $this;
        }
        $this->preAuthRef = [];
        foreach($preAuthRef as $v) {
            if ($v instanceof FHIRString) {
                $this->preAuthRef[] = $v;
            } else {
                $this->preAuthRef[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The result of the adjudication of the line items for the Coverage specified in
     * this insurance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getClaimResponse(): null|FHIRReference
    {
        return $this->claimResponse ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The result of the adjudication of the line items for the Coverage specified in
     * this insurance.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $claimResponse
     * @return static
     */
    public function setClaimResponse(null|FHIRReference $claimResponse): self
    {
        if (null === $claimResponse) {
            unset($this->claimResponse);
            return $this;
        }
        $this->claimResponse = $claimResponse;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim\FHIRClaimInsurance $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim\FHIRClaimInsurance
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRClaimInsurance)) {
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
            } else if (self::FIELD_SEQUENCE === $cen) {
                $type->setSequence(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FOCAL === $cen) {
                $type->setFocal(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->setIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COVERAGE === $cen) {
                $type->setCoverage(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BUSINESS_ARRANGEMENT === $cen) {
                $type->setBusinessArrangement(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRE_AUTH_REF === $cen) {
                $type->addPreAuthRef(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLAIM_RESPONSE === $cen) {
                $type->setClaimResponse(FHIRReference::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SEQUENCE])) {
            if (isset($type->sequence)) {
                $type->sequence->setValue((string)$attributes[self::FIELD_SEQUENCE]);
            } else {
                $type->setSequence((string)$attributes[self::FIELD_SEQUENCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SEQUENCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_FOCAL])) {
            if (isset($type->focal)) {
                $type->focal->setValue((string)$attributes[self::FIELD_FOCAL]);
            } else {
                $type->setFocal((string)$attributes[self::FIELD_FOCAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_FOCAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_BUSINESS_ARRANGEMENT])) {
            if (isset($type->businessArrangement)) {
                $type->businessArrangement->setValue((string)$attributes[self::FIELD_BUSINESS_ARRANGEMENT]);
            } else {
                $type->setBusinessArrangement((string)$attributes[self::FIELD_BUSINESS_ARRANGEMENT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_BUSINESS_ARRANGEMENT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->sequence) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SEQUENCE]) {
            $xw->writeAttribute(self::FIELD_SEQUENCE, $this->sequence->_getValueAsString());
        }
        if (isset($this->focal) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_FOCAL]) {
            $xw->writeAttribute(self::FIELD_FOCAL, $this->focal->_getValueAsString());
        }
        if (isset($this->businessArrangement) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_BUSINESS_ARRANGEMENT]) {
            $xw->writeAttribute(self::FIELD_BUSINESS_ARRANGEMENT, $this->businessArrangement->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->sequence)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SEQUENCE]
                || $this->sequence->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SEQUENCE);
            $this->sequence->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SEQUENCE]);
            $xw->endElement();
        }
        if (isset($this->focal)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_FOCAL]
                || $this->focal->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_FOCAL);
            $this->focal->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_FOCAL]);
            $xw->endElement();
        }
        if (isset($this->identifier)) {
            $xw->startElement(self::FIELD_IDENTIFIER);
            $this->identifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->coverage)) {
            $xw->startElement(self::FIELD_COVERAGE);
            $this->coverage->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->businessArrangement)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_BUSINESS_ARRANGEMENT]
                || $this->businessArrangement->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_BUSINESS_ARRANGEMENT);
            $this->businessArrangement->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_BUSINESS_ARRANGEMENT]);
            $xw->endElement();
        }
        if (isset($this->preAuthRef) && [] !== $this->preAuthRef) {
            foreach($this->preAuthRef as $v) {
                $xw->startElement(self::FIELD_PRE_AUTH_REF);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->claimResponse)) {
            $xw->startElement(self::FIELD_CLAIM_RESPONSE);
            $this->claimResponse->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim\FHIRClaimInsurance $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim\FHIRClaimInsurance
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
        } else if (!($type instanceof FHIRClaimInsurance)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->sequence)
            || isset($decoded->_sequence)
            || property_exists($decoded, self::FIELD_SEQUENCE)
            || property_exists($decoded, self::FIELD_SEQUENCE_EXT)) {
            $v = $decoded->_sequence ?? new \stdClass();
            $v->value = $decoded->sequence ?? null;
            $type->setSequence(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->focal)
            || isset($decoded->_focal)
            || property_exists($decoded, self::FIELD_FOCAL)
            || property_exists($decoded, self::FIELD_FOCAL_EXT)) {
            $v = $decoded->_focal ?? new \stdClass();
            $v->value = $decoded->focal ?? null;
            $type->setFocal(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_array($decoded->identifier)) {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->identifier), $config));
            } else {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize($decoded->identifier, $config));
            }
        }
        if (isset($decoded->coverage) || property_exists($decoded, self::FIELD_COVERAGE)) {
            if (is_array($decoded->coverage)) {
                $type->setCoverage(FHIRReference::jsonUnserialize(reset($decoded->coverage), $config));
            } else {
                $type->setCoverage(FHIRReference::jsonUnserialize($decoded->coverage, $config));
            }
        }
        if (isset($decoded->businessArrangement)
            || isset($decoded->_businessArrangement)
            || property_exists($decoded, self::FIELD_BUSINESS_ARRANGEMENT)
            || property_exists($decoded, self::FIELD_BUSINESS_ARRANGEMENT_EXT)) {
            $v = $decoded->_businessArrangement ?? new \stdClass();
            $v->value = $decoded->businessArrangement ?? null;
            $type->setBusinessArrangement(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->preAuthRef)
            || isset($decoded->_preAuthRef)
            || property_exists($decoded, self::FIELD_PRE_AUTH_REF)
            || property_exists($decoded, self::FIELD_PRE_AUTH_REF_EXT)) {
            $vals = (array)($decoded->preAuthRef ?? []);
            $exts = (array)($decoded->_preAuthRef ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addPreAuthRef(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->claimResponse) || property_exists($decoded, self::FIELD_CLAIM_RESPONSE)) {
            if (is_array($decoded->claimResponse)) {
                $type->setClaimResponse(FHIRReference::jsonUnserialize(reset($decoded->claimResponse), $config));
            } else {
                $type->setClaimResponse(FHIRReference::jsonUnserialize($decoded->claimResponse, $config));
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
        if (isset($this->sequence)) {
            if (null !== ($val = $this->sequence->getValue())) {
                $out->sequence = $val;
            }
            if ($this->sequence->_nonValueFieldDefined()) {
                $ext = $this->sequence->jsonSerialize();
                unset($ext->value);
                $out->_sequence = $ext;
            }
        }
        if (isset($this->focal)) {
            if (null !== ($val = $this->focal->getValue())) {
                $out->focal = $val;
            }
            if ($this->focal->_nonValueFieldDefined()) {
                $ext = $this->focal->jsonSerialize();
                unset($ext->value);
                $out->_focal = $ext;
            }
        }
        if (isset($this->identifier)) {
            $out->identifier = $this->identifier;
        }
        if (isset($this->coverage)) {
            $out->coverage = $this->coverage;
        }
        if (isset($this->businessArrangement)) {
            if (null !== ($val = $this->businessArrangement->getValue())) {
                $out->businessArrangement = $val;
            }
            if ($this->businessArrangement->_nonValueFieldDefined()) {
                $ext = $this->businessArrangement->jsonSerialize();
                unset($ext->value);
                $out->_businessArrangement = $ext;
            }
        }
        if (isset($this->preAuthRef) && [] !== $this->preAuthRef) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->preAuthRef as $v) {
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
                $out->preAuthRef = $vals;
            }
            if ($hasExts) {
                $out->_preAuthRef = $exts;
            }
        }
        if (isset($this->claimResponse)) {
            $out->claimResponse = $this->claimResponse;
        }
        return $out;
    }
}
