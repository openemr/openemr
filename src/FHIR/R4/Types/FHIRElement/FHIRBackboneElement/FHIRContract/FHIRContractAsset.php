<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract;

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
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
 * policy or agreement.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRContractAsset extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONTRACT_DOT_ASSET;

    /* class_default.php:56 */
    public const FIELD_SCOPE = 'scope';
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_REFERENCE = 'typeReference';
    public const FIELD_SUBTYPE = 'subtype';
    public const FIELD_RELATIONSHIP = 'relationship';
    public const FIELD_CONTEXT = 'context';
    public const FIELD_CONDITION = 'condition';
    public const FIELD_CONDITION_EXT = '_condition';
    public const FIELD_PERIOD_TYPE = 'periodType';
    public const FIELD_PERIOD = 'period';
    public const FIELD_USE_PERIOD = 'usePeriod';
    public const FIELD_TEXT = 'text';
    public const FIELD_TEXT_EXT = '_text';
    public const FIELD_LINK_ID = 'linkId';
    public const FIELD_LINK_ID_EXT = '_linkId';
    public const FIELD_ANSWER = 'answer';
    public const FIELD_SECURITY_LABEL_NUMBER = 'securityLabelNumber';
    public const FIELD_SECURITY_LABEL_NUMBER_EXT = '_securityLabelNumber';
    public const FIELD_VALUED_ITEM = 'valuedItem';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_CONDITION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TEXT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Differentiates the kind of the asset .
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $scope;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Target entity type about which the term may be concerned.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $type;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Associated entities.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $typeReference;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be a subtype or part of an offered asset.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $subtype;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the applicability of the term to an asset resource instance, and
     * instances it refers to orinstances that refer to it, and/or are owned by the
     * offeree.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    #[FHIRCoding]
    protected FHIRCoding $relationship;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Circumstance of the asset.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContext>
     */
    #[FHIRContractContext]
    protected array $context;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Description of the quality and completeness of the asset that imay be a factor
     * in its valuation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $condition;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of Asset availability for use or ownership.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $periodType;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Asset relevant contractual time period.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    #[FHIRPeriod]
    protected array $period;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Time period of asset use.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    #[FHIRPeriod]
    protected array $usePeriod;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Clause or question text (Prose Object) concerning the asset in a linked form,
     * such as a QuestionnaireResponse used in the formation of the contract.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $text;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Id [identifier??] of the clause or question text about the asset in the
     * referenced form or QuestionnaireResponse.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $linkId;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Response to assets.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAnswer>
     */
    #[FHIRContractAnswer]
    protected array $answer;
    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Security labels that protects the asset.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt>
     */
    #[FHIRUnsignedInt]
    protected array $securityLabelNumber;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Contract Valued Item List.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractValuedItem>
     */
    #[FHIRContractValuedItem]
    protected array $valuedItem;

    /* constructor.php:61 */
    /**
     * FHIRContractAsset Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $scope
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $type
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $typeReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $subtype
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $relationship
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContext> $context
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $condition
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $periodType
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod> $period
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod> $usePeriod
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $text
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $linkId
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAnswer> $answer
     * @param null|iterable<string>|iterable<int>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt> $securityLabelNumber
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractValuedItem> $valuedItem
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $scope = null,
                                null|iterable $type = null,
                                null|iterable $typeReference = null,
                                null|iterable $subtype = null,
                                null|FHIRCoding $relationship = null,
                                null|iterable $context = null,
                                null|string|FHIRStringPrimitive|FHIRString $condition = null,
                                null|iterable $periodType = null,
                                null|iterable $period = null,
                                null|iterable $usePeriod = null,
                                null|string|FHIRStringPrimitive|FHIRString $text = null,
                                null|iterable $linkId = null,
                                null|iterable $answer = null,
                                null|iterable $securityLabelNumber = null,
                                null|iterable $valuedItem = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $scope) {
            $this->setScope($scope);
        }
        if (null !== $type) {
            $this->setType(...$type);
        }
        if (null !== $typeReference) {
            $this->setTypeReference(...$typeReference);
        }
        if (null !== $subtype) {
            $this->setSubtype(...$subtype);
        }
        if (null !== $relationship) {
            $this->setRelationship($relationship);
        }
        if (null !== $context) {
            $this->setContext(...$context);
        }
        if (null !== $condition) {
            $this->setCondition($condition);
        }
        if (null !== $periodType) {
            $this->setPeriodType(...$periodType);
        }
        if (null !== $period) {
            $this->setPeriod(...$period);
        }
        if (null !== $usePeriod) {
            $this->setUsePeriod(...$usePeriod);
        }
        if (null !== $text) {
            $this->setText($text);
        }
        if (null !== $linkId) {
            $this->setLinkId(...$linkId);
        }
        if (null !== $answer) {
            $this->setAnswer(...$answer);
        }
        if (null !== $securityLabelNumber) {
            $this->setSecurityLabelNumber(...$securityLabelNumber);
        }
        if (null !== $valuedItem) {
            $this->setValuedItem(...$valuedItem);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Differentiates the kind of the asset .
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getScope(): null|FHIRCodeableConcept
    {
        return $this->scope ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Differentiates the kind of the asset .
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $scope
     * @return static
     */
    public function setScope(null|FHIRCodeableConcept $scope): self
    {
        if (null === $scope) {
            unset($this->scope);
            return $this;
        }
        $this->scope = $scope;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Target entity type about which the term may be concerned.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getType(): array
    {
        return $this->type ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getTypeIterator(): iterable
    {
        if (!isset($this->type)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->type);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Target entity type about which the term may be concerned.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function addType(FHIRCodeableConcept $type): self
    {
        if (!isset($this->type)) {
            $this->type = [];
        }
        $this->type[] = $type;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Target entity type about which the term may be concerned.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$type
     * @return static
     */
    public function setType(FHIRCodeableConcept ...$type): self
    {
        if ([] === $type) {
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
     * Associated entities.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getTypeReference(): array
    {
        return $this->typeReference ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getTypeReferenceIterator(): iterable
    {
        if (!isset($this->typeReference)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->typeReference);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Associated entities.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $typeReference
     * @return static
     */
    public function addTypeReference(FHIRReference $typeReference): self
    {
        if (!isset($this->typeReference)) {
            $this->typeReference = [];
        }
        $this->typeReference[] = $typeReference;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Associated entities.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$typeReference
     * @return static
     */
    public function setTypeReference(FHIRReference ...$typeReference): self
    {
        if ([] === $typeReference) {
            unset($this->typeReference);
            return $this;
        }
        $this->typeReference = $typeReference;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be a subtype or part of an offered asset.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSubtype(): array
    {
        return $this->subtype ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSubtypeIterator(): iterable
    {
        if (!isset($this->subtype)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->subtype);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be a subtype or part of an offered asset.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $subtype
     * @return static
     */
    public function addSubtype(FHIRCodeableConcept $subtype): self
    {
        if (!isset($this->subtype)) {
            $this->subtype = [];
        }
        $this->subtype[] = $subtype;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be a subtype or part of an offered asset.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$subtype
     * @return static
     */
    public function setSubtype(FHIRCodeableConcept ...$subtype): self
    {
        if ([] === $subtype) {
            unset($this->subtype);
            return $this;
        }
        $this->subtype = $subtype;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the applicability of the term to an asset resource instance, and
     * instances it refers to orinstances that refer to it, and/or are owned by the
     * offeree.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    public function getRelationship(): null|FHIRCoding
    {
        return $this->relationship ?? null;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the applicability of the term to an asset resource instance, and
     * instances it refers to orinstances that refer to it, and/or are owned by the
     * offeree.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $relationship
     * @return static
     */
    public function setRelationship(null|FHIRCoding $relationship): self
    {
        if (null === $relationship) {
            unset($this->relationship);
            return $this;
        }
        $this->relationship = $relationship;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Circumstance of the asset.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContext>
     */
    public function getContext(): array
    {
        return $this->context ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContext>
     */
    public function getContextIterator(): iterable
    {
        if (!isset($this->context)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->context);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Circumstance of the asset.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContext $context
     * @return static
     */
    public function addContext(FHIRContractContext $context): self
    {
        if (!isset($this->context)) {
            $this->context = [];
        }
        $this->context[] = $context;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Circumstance of the asset.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContext ...$context
     * @return static
     */
    public function setContext(FHIRContractContext ...$context): self
    {
        if ([] === $context) {
            unset($this->context);
            return $this;
        }
        $this->context = $context;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Description of the quality and completeness of the asset that imay be a factor
     * in its valuation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getCondition(): null|FHIRString
    {
        return $this->condition ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Description of the quality and completeness of the asset that imay be a factor
     * in its valuation.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $condition
     * @return static
     */
    public function setCondition(null|string|FHIRStringPrimitive|FHIRString $condition): self
    {
        if (null === $condition) {
            unset($this->condition);
            return $this;
        }
        if (!($condition instanceof FHIRString)) {
            $condition = new FHIRString(value: $condition);
        }
        $this->condition = $condition;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of Asset availability for use or ownership.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPeriodType(): array
    {
        return $this->periodType ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPeriodTypeIterator(): iterable
    {
        if (!isset($this->periodType)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->periodType);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of Asset availability for use or ownership.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $periodType
     * @return static
     */
    public function addPeriodType(FHIRCodeableConcept $periodType): self
    {
        if (!isset($this->periodType)) {
            $this->periodType = [];
        }
        $this->periodType[] = $periodType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of Asset availability for use or ownership.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$periodType
     * @return static
     */
    public function setPeriodType(FHIRCodeableConcept ...$periodType): self
    {
        if ([] === $periodType) {
            unset($this->periodType);
            return $this;
        }
        $this->periodType = $periodType;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Asset relevant contractual time period.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    public function getPeriod(): array
    {
        return $this->period ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    public function getPeriodIterator(): iterable
    {
        if (!isset($this->period)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->period);
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Asset relevant contractual time period.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @return static
     */
    public function addPeriod(FHIRPeriod $period): self
    {
        if (!isset($this->period)) {
            $this->period = [];
        }
        $this->period[] = $period;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Asset relevant contractual time period.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod ...$period
     * @return static
     */
    public function setPeriod(FHIRPeriod ...$period): self
    {
        if ([] === $period) {
            unset($this->period);
            return $this;
        }
        $this->period = $period;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Time period of asset use.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    public function getUsePeriod(): array
    {
        return $this->usePeriod ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    public function getUsePeriodIterator(): iterable
    {
        if (!isset($this->usePeriod)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->usePeriod);
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Time period of asset use.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $usePeriod
     * @return static
     */
    public function addUsePeriod(FHIRPeriod $usePeriod): self
    {
        if (!isset($this->usePeriod)) {
            $this->usePeriod = [];
        }
        $this->usePeriod[] = $usePeriod;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Time period of asset use.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod ...$usePeriod
     * @return static
     */
    public function setUsePeriod(FHIRPeriod ...$usePeriod): self
    {
        if ([] === $usePeriod) {
            unset($this->usePeriod);
            return $this;
        }
        $this->usePeriod = $usePeriod;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Clause or question text (Prose Object) concerning the asset in a linked form,
     * such as a QuestionnaireResponse used in the formation of the contract.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getText(): null|FHIRString
    {
        return $this->text ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Clause or question text (Prose Object) concerning the asset in a linked form,
     * such as a QuestionnaireResponse used in the formation of the contract.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $text
     * @return static
     */
    public function setText(null|string|FHIRStringPrimitive|FHIRString $text): self
    {
        if (null === $text) {
            unset($this->text);
            return $this;
        }
        if (!($text instanceof FHIRString)) {
            $text = new FHIRString(value: $text);
        }
        $this->text = $text;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Id [identifier??] of the clause or question text about the asset in the
     * referenced form or QuestionnaireResponse.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getLinkId(): array
    {
        return $this->linkId ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getLinkIdIterator(): iterable
    {
        if (!isset($this->linkId)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->linkId);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Id [identifier??] of the clause or question text about the asset in the
     * referenced form or QuestionnaireResponse.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $linkId
     * @return static
     */
    public function addLinkId(string|FHIRStringPrimitive|FHIRString $linkId): self
    {
        if (!($linkId instanceof FHIRString)) {
            $linkId = new FHIRString(value: $linkId);
        }
        if (!isset($this->linkId)) {
            $this->linkId = [];
        }
        $this->linkId[] = $linkId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Id [identifier??] of the clause or question text about the asset in the
     * referenced form or QuestionnaireResponse.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$linkId
     * @return static
     */
    public function setLinkId(string|FHIRStringPrimitive|FHIRString ...$linkId): self
    {
        if ([] === $linkId) {
            unset($this->linkId);
            return $this;
        }
        $this->linkId = [];
        foreach($linkId as $v) {
            if ($v instanceof FHIRString) {
                $this->linkId[] = $v;
            } else {
                $this->linkId[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Response to assets.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAnswer>
     */
    public function getAnswer(): array
    {
        return $this->answer ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAnswer>
     */
    public function getAnswerIterator(): iterable
    {
        if (!isset($this->answer)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->answer);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Response to assets.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAnswer $answer
     * @return static
     */
    public function addAnswer(FHIRContractAnswer $answer): self
    {
        if (!isset($this->answer)) {
            $this->answer = [];
        }
        $this->answer[] = $answer;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Response to assets.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAnswer ...$answer
     * @return static
     */
    public function setAnswer(FHIRContractAnswer ...$answer): self
    {
        if ([] === $answer) {
            unset($this->answer);
            return $this;
        }
        $this->answer = $answer;
        return $this;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Security labels that protects the asset.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt>
     */
    public function getSecurityLabelNumber(): array
    {
        return $this->securityLabelNumber ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt>
     */
    public function getSecurityLabelNumberIterator(): iterable
    {
        if (!isset($this->securityLabelNumber)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->securityLabelNumber);
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Security labels that protects the asset.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $securityLabelNumber
     * @return static
     */
    public function addSecurityLabelNumber(string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $securityLabelNumber): self
    {
        if (!($securityLabelNumber instanceof FHIRUnsignedInt)) {
            $securityLabelNumber = new FHIRUnsignedInt(value: $securityLabelNumber);
        }
        if (!isset($this->securityLabelNumber)) {
            $this->securityLabelNumber = [];
        }
        $this->securityLabelNumber[] = $securityLabelNumber;
        return $this;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Security labels that protects the asset.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt ...$securityLabelNumber
     * @return static
     */
    public function setSecurityLabelNumber(string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt ...$securityLabelNumber): self
    {
        if ([] === $securityLabelNumber) {
            unset($this->securityLabelNumber);
            return $this;
        }
        $this->securityLabelNumber = [];
        foreach($securityLabelNumber as $v) {
            if ($v instanceof FHIRUnsignedInt) {
                $this->securityLabelNumber[] = $v;
            } else {
                $this->securityLabelNumber[] = new FHIRUnsignedInt(value: $v);
            }
        }
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Contract Valued Item List.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractValuedItem>
     */
    public function getValuedItem(): array
    {
        return $this->valuedItem ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractValuedItem>
     */
    public function getValuedItemIterator(): iterable
    {
        if (!isset($this->valuedItem)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->valuedItem);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Contract Valued Item List.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractValuedItem $valuedItem
     * @return static
     */
    public function addValuedItem(FHIRContractValuedItem $valuedItem): self
    {
        if (!isset($this->valuedItem)) {
            $this->valuedItem = [];
        }
        $this->valuedItem[] = $valuedItem;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Contract Valued Item List.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractValuedItem ...$valuedItem
     * @return static
     */
    public function setValuedItem(FHIRContractValuedItem ...$valuedItem): self
    {
        if ([] === $valuedItem) {
            unset($this->valuedItem);
            return $this;
        }
        $this->valuedItem = $valuedItem;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRContractAsset)) {
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
            } else if (self::FIELD_SCOPE === $cen) {
                $type->setScope(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->addType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE_REFERENCE === $cen) {
                $type->addTypeReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBTYPE === $cen) {
                $type->addSubtype(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATIONSHIP === $cen) {
                $type->setRelationship(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTEXT === $cen) {
                $type->addContext(FHIRContractContext::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONDITION === $cen) {
                $type->setCondition(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD_TYPE === $cen) {
                $type->addPeriodType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD === $cen) {
                $type->addPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_USE_PERIOD === $cen) {
                $type->addUsePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LINK_ID === $cen) {
                $type->addLinkId(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ANSWER === $cen) {
                $type->addAnswer(FHIRContractAnswer::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SECURITY_LABEL_NUMBER === $cen) {
                $type->addSecurityLabelNumber(FHIRUnsignedInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUED_ITEM === $cen) {
                $type->addValuedItem(FHIRContractValuedItem::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONDITION])) {
            if (isset($type->condition)) {
                $type->condition->setValue((string)$attributes[self::FIELD_CONDITION]);
            } else {
                $type->setCondition((string)$attributes[self::FIELD_CONDITION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONDITION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TEXT])) {
            if (isset($type->text)) {
                $type->text->setValue((string)$attributes[self::FIELD_TEXT]);
            } else {
                $type->setText((string)$attributes[self::FIELD_TEXT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TEXT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->condition) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONDITION]) {
            $xw->writeAttribute(self::FIELD_CONDITION, $this->condition->_getValueAsString());
        }
        if (isset($this->text) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TEXT]) {
            $xw->writeAttribute(self::FIELD_TEXT, $this->text->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->scope)) {
            $xw->startElement(self::FIELD_SCOPE);
            $this->scope->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)) {
            foreach ($this->type as $v) {
                $xw->startElement(self::FIELD_TYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->typeReference)) {
            foreach ($this->typeReference as $v) {
                $xw->startElement(self::FIELD_TYPE_REFERENCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->subtype)) {
            foreach ($this->subtype as $v) {
                $xw->startElement(self::FIELD_SUBTYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->relationship)) {
            $xw->startElement(self::FIELD_RELATIONSHIP);
            $this->relationship->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->context)) {
            foreach ($this->context as $v) {
                $xw->startElement(self::FIELD_CONTEXT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->condition)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONDITION]
                || $this->condition->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONDITION);
            $this->condition->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONDITION]);
            $xw->endElement();
        }
        if (isset($this->periodType)) {
            foreach ($this->periodType as $v) {
                $xw->startElement(self::FIELD_PERIOD_TYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->period)) {
            foreach ($this->period as $v) {
                $xw->startElement(self::FIELD_PERIOD);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->usePeriod)) {
            foreach ($this->usePeriod as $v) {
                $xw->startElement(self::FIELD_USE_PERIOD);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->text)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TEXT]
                || $this->text->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TEXT);
            $this->text->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TEXT]);
            $xw->endElement();
        }
        if (isset($this->linkId) && [] !== $this->linkId) {
            foreach($this->linkId as $v) {
                $xw->startElement(self::FIELD_LINK_ID);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->answer)) {
            foreach ($this->answer as $v) {
                $xw->startElement(self::FIELD_ANSWER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->securityLabelNumber) && [] !== $this->securityLabelNumber) {
            foreach($this->securityLabelNumber as $v) {
                $xw->startElement(self::FIELD_SECURITY_LABEL_NUMBER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->valuedItem)) {
            foreach ($this->valuedItem as $v) {
                $xw->startElement(self::FIELD_VALUED_ITEM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset
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
        } else if (!($type instanceof FHIRContractAsset)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->scope) || property_exists($decoded, self::FIELD_SCOPE)) {
            if (is_array($decoded->scope)) {
                $type->setScope(FHIRCodeableConcept::jsonUnserialize(reset($decoded->scope), $config));
            } else {
                $type->setScope(FHIRCodeableConcept::jsonUnserialize($decoded->scope, $config));
            }
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_object($decoded->type)) {
                $vals = [$decoded->type];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TYPE, true);
            } else {
                $vals = $decoded->type;
            }
            foreach($vals as $v) {
                $type->addType(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->typeReference) || property_exists($decoded, self::FIELD_TYPE_REFERENCE)) {
            if (is_object($decoded->typeReference)) {
                $vals = [$decoded->typeReference];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TYPE_REFERENCE, true);
            } else {
                $vals = $decoded->typeReference;
            }
            foreach($vals as $v) {
                $type->addTypeReference(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->subtype) || property_exists($decoded, self::FIELD_SUBTYPE)) {
            if (is_object($decoded->subtype)) {
                $vals = [$decoded->subtype];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUBTYPE, true);
            } else {
                $vals = $decoded->subtype;
            }
            foreach($vals as $v) {
                $type->addSubtype(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->relationship) || property_exists($decoded, self::FIELD_RELATIONSHIP)) {
            if (is_array($decoded->relationship)) {
                $type->setRelationship(FHIRCoding::jsonUnserialize(reset($decoded->relationship), $config));
            } else {
                $type->setRelationship(FHIRCoding::jsonUnserialize($decoded->relationship, $config));
            }
        }
        if (isset($decoded->context) || property_exists($decoded, self::FIELD_CONTEXT)) {
            if (is_object($decoded->context)) {
                $vals = [$decoded->context];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTEXT, true);
            } else {
                $vals = $decoded->context;
            }
            foreach($vals as $v) {
                $type->addContext(FHIRContractContext::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->condition)
            || isset($decoded->_condition)
            || property_exists($decoded, self::FIELD_CONDITION)
            || property_exists($decoded, self::FIELD_CONDITION_EXT)) {
            $v = $decoded->_condition ?? new \stdClass();
            $v->value = $decoded->condition ?? null;
            $type->setCondition(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->periodType) || property_exists($decoded, self::FIELD_PERIOD_TYPE)) {
            if (is_object($decoded->periodType)) {
                $vals = [$decoded->periodType];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PERIOD_TYPE, true);
            } else {
                $vals = $decoded->periodType;
            }
            foreach($vals as $v) {
                $type->addPeriodType(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->period) || property_exists($decoded, self::FIELD_PERIOD)) {
            if (is_object($decoded->period)) {
                $vals = [$decoded->period];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PERIOD, true);
            } else {
                $vals = $decoded->period;
            }
            foreach($vals as $v) {
                $type->addPeriod(FHIRPeriod::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->usePeriod) || property_exists($decoded, self::FIELD_USE_PERIOD)) {
            if (is_object($decoded->usePeriod)) {
                $vals = [$decoded->usePeriod];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_USE_PERIOD, true);
            } else {
                $vals = $decoded->usePeriod;
            }
            foreach($vals as $v) {
                $type->addUsePeriod(FHIRPeriod::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->text)
            || isset($decoded->_text)
            || property_exists($decoded, self::FIELD_TEXT)
            || property_exists($decoded, self::FIELD_TEXT_EXT)) {
            $v = $decoded->_text ?? new \stdClass();
            $v->value = $decoded->text ?? null;
            $type->setText(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->linkId)
            || isset($decoded->_linkId)
            || property_exists($decoded, self::FIELD_LINK_ID)
            || property_exists($decoded, self::FIELD_LINK_ID_EXT)) {
            $vals = (array)($decoded->linkId ?? []);
            $exts = (array)($decoded->_linkId ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addLinkId(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->answer) || property_exists($decoded, self::FIELD_ANSWER)) {
            if (is_object($decoded->answer)) {
                $vals = [$decoded->answer];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ANSWER, true);
            } else {
                $vals = $decoded->answer;
            }
            foreach($vals as $v) {
                $type->addAnswer(FHIRContractAnswer::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->securityLabelNumber)
            || isset($decoded->_securityLabelNumber)
            || property_exists($decoded, self::FIELD_SECURITY_LABEL_NUMBER)
            || property_exists($decoded, self::FIELD_SECURITY_LABEL_NUMBER_EXT)) {
            $vals = (array)($decoded->securityLabelNumber ?? []);
            $exts = (array)($decoded->_securityLabelNumber ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addSecurityLabelNumber(FHIRUnsignedInt::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->valuedItem) || property_exists($decoded, self::FIELD_VALUED_ITEM)) {
            if (is_object($decoded->valuedItem)) {
                $vals = [$decoded->valuedItem];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_VALUED_ITEM, true);
            } else {
                $vals = $decoded->valuedItem;
            }
            foreach($vals as $v) {
                $type->addValuedItem(FHIRContractValuedItem::jsonUnserialize($v, $config));
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
        if (isset($this->scope)) {
            $out->scope = $this->scope;
        }
        if (isset($this->type) && [] !== $this->type) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TYPE) && 1 === count($this->type)) {
                $out->type = $this->type[0];
            } else {
                $out->type = $this->type;
            }
        }
        if (isset($this->typeReference) && [] !== $this->typeReference) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TYPE_REFERENCE) && 1 === count($this->typeReference)) {
                $out->typeReference = $this->typeReference[0];
            } else {
                $out->typeReference = $this->typeReference;
            }
        }
        if (isset($this->subtype) && [] !== $this->subtype) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUBTYPE) && 1 === count($this->subtype)) {
                $out->subtype = $this->subtype[0];
            } else {
                $out->subtype = $this->subtype;
            }
        }
        if (isset($this->relationship)) {
            $out->relationship = $this->relationship;
        }
        if (isset($this->context) && [] !== $this->context) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTEXT) && 1 === count($this->context)) {
                $out->context = $this->context[0];
            } else {
                $out->context = $this->context;
            }
        }
        if (isset($this->condition)) {
            if (null !== ($val = $this->condition->getValue())) {
                $out->condition = $val;
            }
            if ($this->condition->_nonValueFieldDefined()) {
                $ext = $this->condition->jsonSerialize();
                unset($ext->value);
                $out->_condition = $ext;
            }
        }
        if (isset($this->periodType) && [] !== $this->periodType) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PERIOD_TYPE) && 1 === count($this->periodType)) {
                $out->periodType = $this->periodType[0];
            } else {
                $out->periodType = $this->periodType;
            }
        }
        if (isset($this->period) && [] !== $this->period) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PERIOD) && 1 === count($this->period)) {
                $out->period = $this->period[0];
            } else {
                $out->period = $this->period;
            }
        }
        if (isset($this->usePeriod) && [] !== $this->usePeriod) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_USE_PERIOD) && 1 === count($this->usePeriod)) {
                $out->usePeriod = $this->usePeriod[0];
            } else {
                $out->usePeriod = $this->usePeriod;
            }
        }
        if (isset($this->text)) {
            if (null !== ($val = $this->text->getValue())) {
                $out->text = $val;
            }
            if ($this->text->_nonValueFieldDefined()) {
                $ext = $this->text->jsonSerialize();
                unset($ext->value);
                $out->_text = $ext;
            }
        }
        if (isset($this->linkId) && [] !== $this->linkId) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->linkId as $v) {
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
                $out->linkId = $vals;
            }
            if ($hasExts) {
                $out->_linkId = $exts;
            }
        }
        if (isset($this->answer) && [] !== $this->answer) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ANSWER) && 1 === count($this->answer)) {
                $out->answer = $this->answer[0];
            } else {
                $out->answer = $this->answer;
            }
        }
        if (isset($this->securityLabelNumber) && [] !== $this->securityLabelNumber) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->securityLabelNumber as $v) {
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
                $out->securityLabelNumber = $vals;
            }
            if ($hasExts) {
                $out->_securityLabelNumber = $exts;
            }
        }
        if (isset($this->valuedItem) && [] !== $this->valuedItem) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_VALUED_ITEM) && 1 === count($this->valuedItem)) {
                $out->valuedItem = $this->valuedItem[0];
            } else {
                $out->valuedItem = $this->valuedItem;
            }
        }
        return $out;
    }
}
