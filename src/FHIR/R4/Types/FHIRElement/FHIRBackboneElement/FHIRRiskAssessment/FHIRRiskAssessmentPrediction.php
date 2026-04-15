<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * An assessment of the likely outcome(s) for a patient or other subject as well as
 * the likelihood of each outcome.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRRiskAssessmentPrediction extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION;

    /* class_default.php:56 */
    public const FIELD_OUTCOME = 'outcome';
    public const FIELD_PROBABILITY_DECIMAL = 'probabilityDecimal';
    public const FIELD_PROBABILITY_DECIMAL_EXT = '_probabilityDecimal';
    public const FIELD_PROBABILITY_RANGE = 'probabilityRange';
    public const FIELD_QUALITATIVE_RISK = 'qualitativeRisk';
    public const FIELD_RELATIVE_RISK = 'relativeRisk';
    public const FIELD_RELATIVE_RISK_EXT = '_relativeRisk';
    public const FIELD_WHEN_PERIOD = 'whenPeriod';
    public const FIELD_WHEN_RANGE = 'whenRange';
    public const FIELD_RATIONALE = 'rationale';
    public const FIELD_RATIONALE_EXT = '_rationale';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_PROBABILITY_DECIMAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RELATIVE_RISK => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RATIONALE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * One of the potential outcomes for the patient (e.g. remission, death, a
     * particular condition).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $outcome;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe). (choose any
     * one of probability*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $probabilityDecimal;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe). (choose any
     * one of probability*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $probabilityRange;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe), expressed as a
     * qualitative value (e.g. low, medium, or high).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $qualitativeRisk;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the risk for this particular subject (with their specific
     * characteristics) divided by the risk of the population in general. (Numbers
     * greater than 1 = higher risk than the population, numbers less than 1 = lower
     * risk.).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $relativeRisk;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies. (choose any one of when*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $whenPeriod;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies. (choose any one of when*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $whenRange;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional information explaining the basis for the prediction.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $rationale;

    /* constructor.php:61 */
    /**
     * FHIRRiskAssessmentPrediction Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $outcome
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $probabilityDecimal
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $probabilityRange
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $qualitativeRisk
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $relativeRisk
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $whenPeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $whenRange
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $rationale
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $outcome = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $probabilityDecimal = null,
                                null|FHIRRange $probabilityRange = null,
                                null|FHIRCodeableConcept $qualitativeRisk = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $relativeRisk = null,
                                null|FHIRPeriod $whenPeriod = null,
                                null|FHIRRange $whenRange = null,
                                null|string|FHIRStringPrimitive|FHIRString $rationale = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $outcome) {
            $this->setOutcome($outcome);
        }
        if (null !== $probabilityDecimal) {
            $this->setProbabilityDecimal($probabilityDecimal);
        }
        if (null !== $probabilityRange) {
            $this->setProbabilityRange($probabilityRange);
        }
        if (null !== $qualitativeRisk) {
            $this->setQualitativeRisk($qualitativeRisk);
        }
        if (null !== $relativeRisk) {
            $this->setRelativeRisk($relativeRisk);
        }
        if (null !== $whenPeriod) {
            $this->setWhenPeriod($whenPeriod);
        }
        if (null !== $whenRange) {
            $this->setWhenRange($whenRange);
        }
        if (null !== $rationale) {
            $this->setRationale($rationale);
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
     * One of the potential outcomes for the patient (e.g. remission, death, a
     * particular condition).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getOutcome(): null|FHIRCodeableConcept
    {
        return $this->outcome ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * One of the potential outcomes for the patient (e.g. remission, death, a
     * particular condition).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $outcome
     * @return static
     */
    public function setOutcome(null|FHIRCodeableConcept $outcome): self
    {
        if (null === $outcome) {
            unset($this->outcome);
            return $this;
        }
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe). (choose any
     * one of probability*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getProbabilityDecimal(): null|FHIRDecimal
    {
        return $this->probabilityDecimal ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe). (choose any
     * one of probability*, but only one)
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $probabilityDecimal
     * @return static
     */
    public function setProbabilityDecimal(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $probabilityDecimal): self
    {
        if (null === $probabilityDecimal) {
            unset($this->probabilityDecimal);
            return $this;
        }
        if (!($probabilityDecimal instanceof FHIRDecimal)) {
            $probabilityDecimal = new FHIRDecimal(value: $probabilityDecimal);
        }
        $this->probabilityDecimal = $probabilityDecimal;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe). (choose any
     * one of probability*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getProbabilityRange(): null|FHIRRange
    {
        return $this->probabilityRange ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe). (choose any
     * one of probability*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $probabilityRange
     * @return static
     */
    public function setProbabilityRange(null|FHIRRange $probabilityRange): self
    {
        if (null === $probabilityRange) {
            unset($this->probabilityRange);
            return $this;
        }
        $this->probabilityRange = $probabilityRange;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe), expressed as a
     * qualitative value (e.g. low, medium, or high).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getQualitativeRisk(): null|FHIRCodeableConcept
    {
        return $this->qualitativeRisk ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe), expressed as a
     * qualitative value (e.g. low, medium, or high).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $qualitativeRisk
     * @return static
     */
    public function setQualitativeRisk(null|FHIRCodeableConcept $qualitativeRisk): self
    {
        if (null === $qualitativeRisk) {
            unset($this->qualitativeRisk);
            return $this;
        }
        $this->qualitativeRisk = $qualitativeRisk;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the risk for this particular subject (with their specific
     * characteristics) divided by the risk of the population in general. (Numbers
     * greater than 1 = higher risk than the population, numbers less than 1 = lower
     * risk.).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getRelativeRisk(): null|FHIRDecimal
    {
        return $this->relativeRisk ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the risk for this particular subject (with their specific
     * characteristics) divided by the risk of the population in general. (Numbers
     * greater than 1 = higher risk than the population, numbers less than 1 = lower
     * risk.).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $relativeRisk
     * @return static
     */
    public function setRelativeRisk(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $relativeRisk): self
    {
        if (null === $relativeRisk) {
            unset($this->relativeRisk);
            return $this;
        }
        if (!($relativeRisk instanceof FHIRDecimal)) {
            $relativeRisk = new FHIRDecimal(value: $relativeRisk);
        }
        $this->relativeRisk = $relativeRisk;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies. (choose any one of when*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getWhenPeriod(): null|FHIRPeriod
    {
        return $this->whenPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies. (choose any one of when*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $whenPeriod
     * @return static
     */
    public function setWhenPeriod(null|FHIRPeriod $whenPeriod): self
    {
        if (null === $whenPeriod) {
            unset($this->whenPeriod);
            return $this;
        }
        $this->whenPeriod = $whenPeriod;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies. (choose any one of when*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getWhenRange(): null|FHIRRange
    {
        return $this->whenRange ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies. (choose any one of when*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $whenRange
     * @return static
     */
    public function setWhenRange(null|FHIRRange $whenRange): self
    {
        if (null === $whenRange) {
            unset($this->whenRange);
            return $this;
        }
        $this->whenRange = $whenRange;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional information explaining the basis for the prediction.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getRationale(): null|FHIRString
    {
        return $this->rationale ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional information explaining the basis for the prediction.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $rationale
     * @return static
     */
    public function setRationale(null|string|FHIRStringPrimitive|FHIRString $rationale): self
    {
        if (null === $rationale) {
            unset($this->rationale);
            return $this;
        }
        if (!($rationale instanceof FHIRString)) {
            $rationale = new FHIRString(value: $rationale);
        }
        $this->rationale = $rationale;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment\FHIRRiskAssessmentPrediction $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment\FHIRRiskAssessmentPrediction
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRRiskAssessmentPrediction)) {
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
            } else if (self::FIELD_OUTCOME === $cen) {
                $type->setOutcome(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROBABILITY_DECIMAL === $cen) {
                $type->setProbabilityDecimal(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROBABILITY_RANGE === $cen) {
                $type->setProbabilityRange(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_QUALITATIVE_RISK === $cen) {
                $type->setQualitativeRisk(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATIVE_RISK === $cen) {
                $type->setRelativeRisk(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_WHEN_PERIOD === $cen) {
                $type->setWhenPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_WHEN_RANGE === $cen) {
                $type->setWhenRange(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RATIONALE === $cen) {
                $type->setRationale(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PROBABILITY_DECIMAL])) {
            if (isset($type->probabilityDecimal)) {
                $type->probabilityDecimal->setValue((string)$attributes[self::FIELD_PROBABILITY_DECIMAL]);
            } else {
                $type->setProbabilityDecimal((string)$attributes[self::FIELD_PROBABILITY_DECIMAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PROBABILITY_DECIMAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RELATIVE_RISK])) {
            if (isset($type->relativeRisk)) {
                $type->relativeRisk->setValue((string)$attributes[self::FIELD_RELATIVE_RISK]);
            } else {
                $type->setRelativeRisk((string)$attributes[self::FIELD_RELATIVE_RISK]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RELATIVE_RISK, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RATIONALE])) {
            if (isset($type->rationale)) {
                $type->rationale->setValue((string)$attributes[self::FIELD_RATIONALE]);
            } else {
                $type->setRationale((string)$attributes[self::FIELD_RATIONALE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RATIONALE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->probabilityDecimal) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PROBABILITY_DECIMAL]) {
            $xw->writeAttribute(self::FIELD_PROBABILITY_DECIMAL, $this->probabilityDecimal->_getValueAsString());
        }
        if (isset($this->relativeRisk) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RELATIVE_RISK]) {
            $xw->writeAttribute(self::FIELD_RELATIVE_RISK, $this->relativeRisk->_getValueAsString());
        }
        if (isset($this->rationale) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RATIONALE]) {
            $xw->writeAttribute(self::FIELD_RATIONALE, $this->rationale->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->outcome)) {
            $xw->startElement(self::FIELD_OUTCOME);
            $this->outcome->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->probabilityDecimal)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PROBABILITY_DECIMAL]
                || $this->probabilityDecimal->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PROBABILITY_DECIMAL);
            $this->probabilityDecimal->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PROBABILITY_DECIMAL]);
            $xw->endElement();
        }
        if (isset($this->probabilityRange)) {
            $xw->startElement(self::FIELD_PROBABILITY_RANGE);
            $this->probabilityRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->qualitativeRisk)) {
            $xw->startElement(self::FIELD_QUALITATIVE_RISK);
            $this->qualitativeRisk->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->relativeRisk)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RELATIVE_RISK]
                || $this->relativeRisk->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RELATIVE_RISK);
            $this->relativeRisk->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RELATIVE_RISK]);
            $xw->endElement();
        }
        if (isset($this->whenPeriod)) {
            $xw->startElement(self::FIELD_WHEN_PERIOD);
            $this->whenPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->whenRange)) {
            $xw->startElement(self::FIELD_WHEN_RANGE);
            $this->whenRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->rationale)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RATIONALE]
                || $this->rationale->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RATIONALE);
            $this->rationale->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RATIONALE]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment\FHIRRiskAssessmentPrediction $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment\FHIRRiskAssessmentPrediction
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
        } else if (!($type instanceof FHIRRiskAssessmentPrediction)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->outcome) || property_exists($decoded, self::FIELD_OUTCOME)) {
            if (is_array($decoded->outcome)) {
                $type->setOutcome(FHIRCodeableConcept::jsonUnserialize(reset($decoded->outcome), $config));
            } else {
                $type->setOutcome(FHIRCodeableConcept::jsonUnserialize($decoded->outcome, $config));
            }
        }
        if (isset($decoded->probabilityDecimal)
            || isset($decoded->_probabilityDecimal)
            || property_exists($decoded, self::FIELD_PROBABILITY_DECIMAL)
            || property_exists($decoded, self::FIELD_PROBABILITY_DECIMAL_EXT)) {
            $v = $decoded->_probabilityDecimal ?? new \stdClass();
            $v->value = $decoded->probabilityDecimal ?? null;
            $type->setProbabilityDecimal(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->probabilityRange) || property_exists($decoded, self::FIELD_PROBABILITY_RANGE)) {
            if (is_array($decoded->probabilityRange)) {
                $type->setProbabilityRange(FHIRRange::jsonUnserialize(reset($decoded->probabilityRange), $config));
            } else {
                $type->setProbabilityRange(FHIRRange::jsonUnserialize($decoded->probabilityRange, $config));
            }
        }
        if (isset($decoded->qualitativeRisk) || property_exists($decoded, self::FIELD_QUALITATIVE_RISK)) {
            if (is_array($decoded->qualitativeRisk)) {
                $type->setQualitativeRisk(FHIRCodeableConcept::jsonUnserialize(reset($decoded->qualitativeRisk), $config));
            } else {
                $type->setQualitativeRisk(FHIRCodeableConcept::jsonUnserialize($decoded->qualitativeRisk, $config));
            }
        }
        if (isset($decoded->relativeRisk)
            || isset($decoded->_relativeRisk)
            || property_exists($decoded, self::FIELD_RELATIVE_RISK)
            || property_exists($decoded, self::FIELD_RELATIVE_RISK_EXT)) {
            $v = $decoded->_relativeRisk ?? new \stdClass();
            $v->value = $decoded->relativeRisk ?? null;
            $type->setRelativeRisk(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->whenPeriod) || property_exists($decoded, self::FIELD_WHEN_PERIOD)) {
            if (is_array($decoded->whenPeriod)) {
                $type->setWhenPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->whenPeriod), $config));
            } else {
                $type->setWhenPeriod(FHIRPeriod::jsonUnserialize($decoded->whenPeriod, $config));
            }
        }
        if (isset($decoded->whenRange) || property_exists($decoded, self::FIELD_WHEN_RANGE)) {
            if (is_array($decoded->whenRange)) {
                $type->setWhenRange(FHIRRange::jsonUnserialize(reset($decoded->whenRange), $config));
            } else {
                $type->setWhenRange(FHIRRange::jsonUnserialize($decoded->whenRange, $config));
            }
        }
        if (isset($decoded->rationale)
            || isset($decoded->_rationale)
            || property_exists($decoded, self::FIELD_RATIONALE)
            || property_exists($decoded, self::FIELD_RATIONALE_EXT)) {
            $v = $decoded->_rationale ?? new \stdClass();
            $v->value = $decoded->rationale ?? null;
            $type->setRationale(FHIRString::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->outcome)) {
            $out->outcome = $this->outcome;
        }
        if (isset($this->probabilityDecimal)) {
            if (null !== ($val = $this->probabilityDecimal->getValue())) {
                $out->probabilityDecimal = $val;
            }
            if ($this->probabilityDecimal->_nonValueFieldDefined()) {
                $ext = $this->probabilityDecimal->jsonSerialize();
                unset($ext->value);
                $out->_probabilityDecimal = $ext;
            }
        }
        if (isset($this->probabilityRange)) {
            $out->probabilityRange = $this->probabilityRange;
        }
        if (isset($this->qualitativeRisk)) {
            $out->qualitativeRisk = $this->qualitativeRisk;
        }
        if (isset($this->relativeRisk)) {
            if (null !== ($val = $this->relativeRisk->getValue())) {
                $out->relativeRisk = $val;
            }
            if ($this->relativeRisk->_nonValueFieldDefined()) {
                $ext = $this->relativeRisk->jsonSerialize();
                unset($ext->value);
                $out->_relativeRisk = $ext;
            }
        }
        if (isset($this->whenPeriod)) {
            $out->whenPeriod = $this->whenPeriod;
        }
        if (isset($this->whenRange)) {
            $out->whenRange = $this->whenRange;
        }
        if (isset($this->rationale)) {
            if (null !== ($val = $this->rationale->getValue())) {
                $out->rationale = $val;
            }
            if ($this->rationale->_nonValueFieldDefined()) {
                $ext = $this->rationale->jsonSerialize();
                unset($ext->value);
                $out->_rationale = $ext;
            }
        }
        return $out;
    }
}
