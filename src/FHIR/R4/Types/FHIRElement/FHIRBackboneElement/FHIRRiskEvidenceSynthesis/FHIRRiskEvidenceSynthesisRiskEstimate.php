<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The RiskEvidenceSynthesis resource describes the likelihood of an outcome in a
 * population plus exposure state where the risk estimate is derived from a
 * combination of research studies.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRRiskEvidenceSynthesisRiskEstimate extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_RISK_EVIDENCE_SYNTHESIS_DOT_RISK_ESTIMATE;

    /* class_default.php:56 */
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_TYPE = 'type';
    public const FIELD_VALUE = 'value';
    public const FIELD_VALUE_EXT = '_value';
    public const FIELD_UNIT_OF_MEASURE = 'unitOfMeasure';
    public const FIELD_DENOMINATOR_COUNT = 'denominatorCount';
    public const FIELD_DENOMINATOR_COUNT_EXT = '_denominatorCount';
    public const FIELD_NUMERATOR_COUNT = 'numeratorCount';
    public const FIELD_NUMERATOR_COUNT_EXT = '_numeratorCount';
    public const FIELD_PRECISION_ESTIMATE = 'precisionEstimate';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DENOMINATOR_COUNT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NUMERATOR_COUNT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Human-readable summary of risk estimate.
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
     * Examples include proportion and mean.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The point estimate of the risk estimate.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $value;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the UCUM unit for the outcome.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $unitOfMeasure;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The sample size for the group that was measured for this risk estimate.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $denominatorCount;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of group members with the outcome of interest.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $numeratorCount;
    /**
     * The RiskEvidenceSynthesis resource describes the likelihood of an outcome in a
     * population plus exposure state where the risk estimate is derived from a
     * combination of research studies.
     *
     * A description of the precision of the estimate for the effect.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisPrecisionEstimate>
     */
    #[FHIRRiskEvidenceSynthesisPrecisionEstimate]
    protected array $precisionEstimate;

    /* constructor.php:61 */
    /**
     * FHIRRiskEvidenceSynthesisRiskEstimate Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $value
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $unitOfMeasure
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $denominatorCount
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $numeratorCount
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisPrecisionEstimate> $precisionEstimate
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|FHIRCodeableConcept $type = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $value = null,
                                null|FHIRCodeableConcept $unitOfMeasure = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $denominatorCount = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $numeratorCount = null,
                                null|iterable $precisionEstimate = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $value) {
            $this->setValue($value);
        }
        if (null !== $unitOfMeasure) {
            $this->setUnitOfMeasure($unitOfMeasure);
        }
        if (null !== $denominatorCount) {
            $this->setDenominatorCount($denominatorCount);
        }
        if (null !== $numeratorCount) {
            $this->setNumeratorCount($numeratorCount);
        }
        if (null !== $precisionEstimate) {
            $this->setPrecisionEstimate(...$precisionEstimate);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Human-readable summary of risk estimate.
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
     * Human-readable summary of risk estimate.
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
     * Examples include proportion and mean.
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
     * Examples include proportion and mean.
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
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The point estimate of the risk estimate.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getValue(): null|FHIRDecimal
    {
        return $this->value ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The point estimate of the risk estimate.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $value
     * @return static
     */
    public function setValue(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $value): self
    {
        if (null === $value) {
            unset($this->value);
            return $this;
        }
        if (!($value instanceof FHIRDecimal)) {
            $value = new FHIRDecimal(value: $value);
        }
        $this->value = $value;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the UCUM unit for the outcome.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getUnitOfMeasure(): null|FHIRCodeableConcept
    {
        return $this->unitOfMeasure ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the UCUM unit for the outcome.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $unitOfMeasure
     * @return static
     */
    public function setUnitOfMeasure(null|FHIRCodeableConcept $unitOfMeasure): self
    {
        if (null === $unitOfMeasure) {
            unset($this->unitOfMeasure);
            return $this;
        }
        $this->unitOfMeasure = $unitOfMeasure;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The sample size for the group that was measured for this risk estimate.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getDenominatorCount(): null|FHIRInteger
    {
        return $this->denominatorCount ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The sample size for the group that was measured for this risk estimate.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $denominatorCount
     * @return static
     */
    public function setDenominatorCount(null|string|float|FHIRIntegerPrimitive|FHIRInteger $denominatorCount): self
    {
        if (null === $denominatorCount) {
            unset($this->denominatorCount);
            return $this;
        }
        if (!($denominatorCount instanceof FHIRInteger)) {
            $denominatorCount = new FHIRInteger(value: $denominatorCount);
        }
        $this->denominatorCount = $denominatorCount;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of group members with the outcome of interest.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getNumeratorCount(): null|FHIRInteger
    {
        return $this->numeratorCount ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of group members with the outcome of interest.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $numeratorCount
     * @return static
     */
    public function setNumeratorCount(null|string|float|FHIRIntegerPrimitive|FHIRInteger $numeratorCount): self
    {
        if (null === $numeratorCount) {
            unset($this->numeratorCount);
            return $this;
        }
        if (!($numeratorCount instanceof FHIRInteger)) {
            $numeratorCount = new FHIRInteger(value: $numeratorCount);
        }
        $this->numeratorCount = $numeratorCount;
        return $this;
    }

    /**
     * The RiskEvidenceSynthesis resource describes the likelihood of an outcome in a
     * population plus exposure state where the risk estimate is derived from a
     * combination of research studies.
     *
     * A description of the precision of the estimate for the effect.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisPrecisionEstimate>
     */
    public function getPrecisionEstimate(): array
    {
        return $this->precisionEstimate ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisPrecisionEstimate>
     */
    public function getPrecisionEstimateIterator(): iterable
    {
        if (!isset($this->precisionEstimate)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->precisionEstimate);
    }

    /**
     * The RiskEvidenceSynthesis resource describes the likelihood of an outcome in a
     * population plus exposure state where the risk estimate is derived from a
     * combination of research studies.
     *
     * A description of the precision of the estimate for the effect.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisPrecisionEstimate $precisionEstimate
     * @return static
     */
    public function addPrecisionEstimate(FHIRRiskEvidenceSynthesisPrecisionEstimate $precisionEstimate): self
    {
        if (!isset($this->precisionEstimate)) {
            $this->precisionEstimate = [];
        }
        $this->precisionEstimate[] = $precisionEstimate;
        return $this;
    }

    /**
     * The RiskEvidenceSynthesis resource describes the likelihood of an outcome in a
     * population plus exposure state where the risk estimate is derived from a
     * combination of research studies.
     *
     * A description of the precision of the estimate for the effect.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisPrecisionEstimate ...$precisionEstimate
     * @return static
     */
    public function setPrecisionEstimate(FHIRRiskEvidenceSynthesisPrecisionEstimate ...$precisionEstimate): self
    {
        if ([] === $precisionEstimate) {
            unset($this->precisionEstimate);
            return $this;
        }
        $this->precisionEstimate = $precisionEstimate;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisRiskEstimate $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisRiskEstimate
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRRiskEvidenceSynthesisRiskEstimate)) {
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
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE === $cen) {
                $type->setValue(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_UNIT_OF_MEASURE === $cen) {
                $type->setUnitOfMeasure(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DENOMINATOR_COUNT === $cen) {
                $type->setDenominatorCount(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NUMERATOR_COUNT === $cen) {
                $type->setNumeratorCount(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRECISION_ESTIMATE === $cen) {
                $type->addPrecisionEstimate(FHIRRiskEvidenceSynthesisPrecisionEstimate::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE])) {
            if (isset($type->value)) {
                $type->value->setValue((string)$attributes[self::FIELD_VALUE]);
            } else {
                $type->setValue((string)$attributes[self::FIELD_VALUE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DENOMINATOR_COUNT])) {
            if (isset($type->denominatorCount)) {
                $type->denominatorCount->setValue((string)$attributes[self::FIELD_DENOMINATOR_COUNT]);
            } else {
                $type->setDenominatorCount((string)$attributes[self::FIELD_DENOMINATOR_COUNT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DENOMINATOR_COUNT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NUMERATOR_COUNT])) {
            if (isset($type->numeratorCount)) {
                $type->numeratorCount->setValue((string)$attributes[self::FIELD_NUMERATOR_COUNT]);
            } else {
                $type->setNumeratorCount((string)$attributes[self::FIELD_NUMERATOR_COUNT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NUMERATOR_COUNT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->value) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE]) {
            $xw->writeAttribute(self::FIELD_VALUE, $this->value->_getValueAsString());
        }
        if (isset($this->denominatorCount) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DENOMINATOR_COUNT]) {
            $xw->writeAttribute(self::FIELD_DENOMINATOR_COUNT, $this->denominatorCount->_getValueAsString());
        }
        if (isset($this->numeratorCount) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NUMERATOR_COUNT]) {
            $xw->writeAttribute(self::FIELD_NUMERATOR_COUNT, $this->numeratorCount->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->value)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE]
                || $this->value->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE);
            $this->value->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE]);
            $xw->endElement();
        }
        if (isset($this->unitOfMeasure)) {
            $xw->startElement(self::FIELD_UNIT_OF_MEASURE);
            $this->unitOfMeasure->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->denominatorCount)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DENOMINATOR_COUNT]
                || $this->denominatorCount->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DENOMINATOR_COUNT);
            $this->denominatorCount->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DENOMINATOR_COUNT]);
            $xw->endElement();
        }
        if (isset($this->numeratorCount)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NUMERATOR_COUNT]
                || $this->numeratorCount->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NUMERATOR_COUNT);
            $this->numeratorCount->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NUMERATOR_COUNT]);
            $xw->endElement();
        }
        if (isset($this->precisionEstimate)) {
            foreach ($this->precisionEstimate as $v) {
                $xw->startElement(self::FIELD_PRECISION_ESTIMATE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisRiskEstimate $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskEvidenceSynthesis\FHIRRiskEvidenceSynthesisRiskEstimate
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
        } else if (!($type instanceof FHIRRiskEvidenceSynthesisRiskEstimate)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->value)
            || isset($decoded->_value)
            || property_exists($decoded, self::FIELD_VALUE)
            || property_exists($decoded, self::FIELD_VALUE_EXT)) {
            $v = $decoded->_value ?? new \stdClass();
            $v->value = $decoded->value ?? null;
            $type->setValue(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->unitOfMeasure) || property_exists($decoded, self::FIELD_UNIT_OF_MEASURE)) {
            if (is_array($decoded->unitOfMeasure)) {
                $type->setUnitOfMeasure(FHIRCodeableConcept::jsonUnserialize(reset($decoded->unitOfMeasure), $config));
            } else {
                $type->setUnitOfMeasure(FHIRCodeableConcept::jsonUnserialize($decoded->unitOfMeasure, $config));
            }
        }
        if (isset($decoded->denominatorCount)
            || isset($decoded->_denominatorCount)
            || property_exists($decoded, self::FIELD_DENOMINATOR_COUNT)
            || property_exists($decoded, self::FIELD_DENOMINATOR_COUNT_EXT)) {
            $v = $decoded->_denominatorCount ?? new \stdClass();
            $v->value = $decoded->denominatorCount ?? null;
            $type->setDenominatorCount(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->numeratorCount)
            || isset($decoded->_numeratorCount)
            || property_exists($decoded, self::FIELD_NUMERATOR_COUNT)
            || property_exists($decoded, self::FIELD_NUMERATOR_COUNT_EXT)) {
            $v = $decoded->_numeratorCount ?? new \stdClass();
            $v->value = $decoded->numeratorCount ?? null;
            $type->setNumeratorCount(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->precisionEstimate) || property_exists($decoded, self::FIELD_PRECISION_ESTIMATE)) {
            if (is_object($decoded->precisionEstimate)) {
                $vals = [$decoded->precisionEstimate];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PRECISION_ESTIMATE, true);
            } else {
                $vals = $decoded->precisionEstimate;
            }
            foreach($vals as $v) {
                $type->addPrecisionEstimate(FHIRRiskEvidenceSynthesisPrecisionEstimate::jsonUnserialize($v, $config));
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
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->value)) {
            if (null !== ($val = $this->value->getValue())) {
                $out->value = $val;
            }
            if ($this->value->_nonValueFieldDefined()) {
                $ext = $this->value->jsonSerialize();
                unset($ext->value);
                $out->_value = $ext;
            }
        }
        if (isset($this->unitOfMeasure)) {
            $out->unitOfMeasure = $this->unitOfMeasure;
        }
        if (isset($this->denominatorCount)) {
            if (null !== ($val = $this->denominatorCount->getValue())) {
                $out->denominatorCount = $val;
            }
            if ($this->denominatorCount->_nonValueFieldDefined()) {
                $ext = $this->denominatorCount->jsonSerialize();
                unset($ext->value);
                $out->_denominatorCount = $ext;
            }
        }
        if (isset($this->numeratorCount)) {
            if (null !== ($val = $this->numeratorCount->getValue())) {
                $out->numeratorCount = $val;
            }
            if ($this->numeratorCount->_nonValueFieldDefined()) {
                $ext = $this->numeratorCount->jsonSerialize();
                unset($ext->value);
                $out->_numeratorCount = $ext;
            }
        }
        if (isset($this->precisionEstimate) && [] !== $this->precisionEstimate) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PRECISION_ESTIMATE) && 1 === count($this->precisionEstimate)) {
                $out->precisionEstimate = $this->precisionEstimate[0];
            } else {
                $out->precisionEstimate = $this->precisionEstimate;
            }
        }
        return $out;
    }
}
