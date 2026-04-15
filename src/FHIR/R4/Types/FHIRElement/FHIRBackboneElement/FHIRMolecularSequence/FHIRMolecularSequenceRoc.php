<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Raw data describing a biological sequence.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMolecularSequenceRoc extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_ROC;

    /* class_default.php:56 */
    public const FIELD_SCORE = 'score';
    public const FIELD_SCORE_EXT = '_score';
    public const FIELD_NUM_TP = 'numTP';
    public const FIELD_NUM_TP_EXT = '_numTP';
    public const FIELD_NUM_FP = 'numFP';
    public const FIELD_NUM_FP_EXT = '_numFP';
    public const FIELD_NUM_FN = 'numFN';
    public const FIELD_NUM_FN_EXT = '_numFN';
    public const FIELD_PRECISION = 'precision';
    public const FIELD_PRECISION_EXT = '_precision';
    public const FIELD_SENSITIVITY = 'sensitivity';
    public const FIELD_SENSITIVITY_EXT = '_sensitivity';
    public const FIELD_F_MEASURE = 'fMeasure';
    public const FIELD_F_MEASURE_EXT = '_fMeasure';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Invidual data point representing the GQ (genotype quality) score threshold.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    #[FHIRInteger]
    protected array $score;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of true positives if the GQ score threshold was set to "score" field
     * value.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    #[FHIRInteger]
    protected array $numTP;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false positives if the GQ score threshold was set to "score" field
     * value.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    #[FHIRInteger]
    protected array $numFP;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false negatives if the GQ score threshold was set to "score" field
     * value.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    #[FHIRInteger]
    protected array $numFN;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated precision if the GQ score threshold was set to "score" field value.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal>
     */
    #[FHIRDecimal]
    protected array $precision;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated sensitivity if the GQ score threshold was set to "score" field value.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal>
     */
    #[FHIRDecimal]
    protected array $sensitivity;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated fScore if the GQ score threshold was set to "score" field value.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal>
     */
    #[FHIRDecimal]
    protected array $fMeasure;

    /* constructor.php:61 */
    /**
     * FHIRMolecularSequenceRoc Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<string>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger> $score
     * @param null|iterable<string>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger> $numTP
     * @param null|iterable<string>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger> $numFP
     * @param null|iterable<string>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger> $numFN
     * @param null|iterable<string>|iterable<int>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal> $precision
     * @param null|iterable<string>|iterable<int>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal> $sensitivity
     * @param null|iterable<string>|iterable<int>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal> $fMeasure
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $score = null,
                                null|iterable $numTP = null,
                                null|iterable $numFP = null,
                                null|iterable $numFN = null,
                                null|iterable $precision = null,
                                null|iterable $sensitivity = null,
                                null|iterable $fMeasure = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $score) {
            $this->setScore(...$score);
        }
        if (null !== $numTP) {
            $this->setNumTP(...$numTP);
        }
        if (null !== $numFP) {
            $this->setNumFP(...$numFP);
        }
        if (null !== $numFN) {
            $this->setNumFN(...$numFN);
        }
        if (null !== $precision) {
            $this->setPrecision(...$precision);
        }
        if (null !== $sensitivity) {
            $this->setSensitivity(...$sensitivity);
        }
        if (null !== $fMeasure) {
            $this->setFMeasure(...$fMeasure);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Invidual data point representing the GQ (genotype quality) score threshold.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getScore(): array
    {
        return $this->score ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getScoreIterator(): iterable
    {
        if (!isset($this->score)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->score);
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Invidual data point representing the GQ (genotype quality) score threshold.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $score
     * @return static
     */
    public function addScore(string|float|FHIRIntegerPrimitive|FHIRInteger $score): self
    {
        if (!($score instanceof FHIRInteger)) {
            $score = new FHIRInteger(value: $score);
        }
        if (!isset($this->score)) {
            $this->score = [];
        }
        $this->score[] = $score;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Invidual data point representing the GQ (genotype quality) score threshold.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger ...$score
     * @return static
     */
    public function setScore(string|float|FHIRIntegerPrimitive|FHIRInteger ...$score): self
    {
        if ([] === $score) {
            unset($this->score);
            return $this;
        }
        $this->score = [];
        foreach($score as $v) {
            if ($v instanceof FHIRInteger) {
                $this->score[] = $v;
            } else {
                $this->score[] = new FHIRInteger(value: $v);
            }
        }
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of true positives if the GQ score threshold was set to "score" field
     * value.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getNumTP(): array
    {
        return $this->numTP ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getNumTPIterator(): iterable
    {
        if (!isset($this->numTP)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->numTP);
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of true positives if the GQ score threshold was set to "score" field
     * value.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $numTP
     * @return static
     */
    public function addNumTP(string|float|FHIRIntegerPrimitive|FHIRInteger $numTP): self
    {
        if (!($numTP instanceof FHIRInteger)) {
            $numTP = new FHIRInteger(value: $numTP);
        }
        if (!isset($this->numTP)) {
            $this->numTP = [];
        }
        $this->numTP[] = $numTP;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of true positives if the GQ score threshold was set to "score" field
     * value.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger ...$numTP
     * @return static
     */
    public function setNumTP(string|float|FHIRIntegerPrimitive|FHIRInteger ...$numTP): self
    {
        if ([] === $numTP) {
            unset($this->numTP);
            return $this;
        }
        $this->numTP = [];
        foreach($numTP as $v) {
            if ($v instanceof FHIRInteger) {
                $this->numTP[] = $v;
            } else {
                $this->numTP[] = new FHIRInteger(value: $v);
            }
        }
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false positives if the GQ score threshold was set to "score" field
     * value.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getNumFP(): array
    {
        return $this->numFP ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getNumFPIterator(): iterable
    {
        if (!isset($this->numFP)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->numFP);
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false positives if the GQ score threshold was set to "score" field
     * value.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $numFP
     * @return static
     */
    public function addNumFP(string|float|FHIRIntegerPrimitive|FHIRInteger $numFP): self
    {
        if (!($numFP instanceof FHIRInteger)) {
            $numFP = new FHIRInteger(value: $numFP);
        }
        if (!isset($this->numFP)) {
            $this->numFP = [];
        }
        $this->numFP[] = $numFP;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false positives if the GQ score threshold was set to "score" field
     * value.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger ...$numFP
     * @return static
     */
    public function setNumFP(string|float|FHIRIntegerPrimitive|FHIRInteger ...$numFP): self
    {
        if ([] === $numFP) {
            unset($this->numFP);
            return $this;
        }
        $this->numFP = [];
        foreach($numFP as $v) {
            if ($v instanceof FHIRInteger) {
                $this->numFP[] = $v;
            } else {
                $this->numFP[] = new FHIRInteger(value: $v);
            }
        }
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false negatives if the GQ score threshold was set to "score" field
     * value.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getNumFN(): array
    {
        return $this->numFN ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger>
     */
    public function getNumFNIterator(): iterable
    {
        if (!isset($this->numFN)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->numFN);
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false negatives if the GQ score threshold was set to "score" field
     * value.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $numFN
     * @return static
     */
    public function addNumFN(string|float|FHIRIntegerPrimitive|FHIRInteger $numFN): self
    {
        if (!($numFN instanceof FHIRInteger)) {
            $numFN = new FHIRInteger(value: $numFN);
        }
        if (!isset($this->numFN)) {
            $this->numFN = [];
        }
        $this->numFN[] = $numFN;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false negatives if the GQ score threshold was set to "score" field
     * value.
     *
     * @param string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger ...$numFN
     * @return static
     */
    public function setNumFN(string|float|FHIRIntegerPrimitive|FHIRInteger ...$numFN): self
    {
        if ([] === $numFN) {
            unset($this->numFN);
            return $this;
        }
        $this->numFN = [];
        foreach($numFN as $v) {
            if ($v instanceof FHIRInteger) {
                $this->numFN[] = $v;
            } else {
                $this->numFN[] = new FHIRInteger(value: $v);
            }
        }
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated precision if the GQ score threshold was set to "score" field value.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal>
     */
    public function getPrecision(): array
    {
        return $this->precision ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal>
     */
    public function getPrecisionIterator(): iterable
    {
        if (!isset($this->precision)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->precision);
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated precision if the GQ score threshold was set to "score" field value.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $precision
     * @return static
     */
    public function addPrecision(string|int|float|FHIRDecimalPrimitive|FHIRDecimal $precision): self
    {
        if (!($precision instanceof FHIRDecimal)) {
            $precision = new FHIRDecimal(value: $precision);
        }
        if (!isset($this->precision)) {
            $this->precision = [];
        }
        $this->precision[] = $precision;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated precision if the GQ score threshold was set to "score" field value.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal ...$precision
     * @return static
     */
    public function setPrecision(string|int|float|FHIRDecimalPrimitive|FHIRDecimal ...$precision): self
    {
        if ([] === $precision) {
            unset($this->precision);
            return $this;
        }
        $this->precision = [];
        foreach($precision as $v) {
            if ($v instanceof FHIRDecimal) {
                $this->precision[] = $v;
            } else {
                $this->precision[] = new FHIRDecimal(value: $v);
            }
        }
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated sensitivity if the GQ score threshold was set to "score" field value.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal>
     */
    public function getSensitivity(): array
    {
        return $this->sensitivity ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal>
     */
    public function getSensitivityIterator(): iterable
    {
        if (!isset($this->sensitivity)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->sensitivity);
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated sensitivity if the GQ score threshold was set to "score" field value.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $sensitivity
     * @return static
     */
    public function addSensitivity(string|int|float|FHIRDecimalPrimitive|FHIRDecimal $sensitivity): self
    {
        if (!($sensitivity instanceof FHIRDecimal)) {
            $sensitivity = new FHIRDecimal(value: $sensitivity);
        }
        if (!isset($this->sensitivity)) {
            $this->sensitivity = [];
        }
        $this->sensitivity[] = $sensitivity;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated sensitivity if the GQ score threshold was set to "score" field value.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal ...$sensitivity
     * @return static
     */
    public function setSensitivity(string|int|float|FHIRDecimalPrimitive|FHIRDecimal ...$sensitivity): self
    {
        if ([] === $sensitivity) {
            unset($this->sensitivity);
            return $this;
        }
        $this->sensitivity = [];
        foreach($sensitivity as $v) {
            if ($v instanceof FHIRDecimal) {
                $this->sensitivity[] = $v;
            } else {
                $this->sensitivity[] = new FHIRDecimal(value: $v);
            }
        }
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated fScore if the GQ score threshold was set to "score" field value.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal>
     */
    public function getFMeasure(): array
    {
        return $this->fMeasure ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal>
     */
    public function getFMeasureIterator(): iterable
    {
        if (!isset($this->fMeasure)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->fMeasure);
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated fScore if the GQ score threshold was set to "score" field value.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $fMeasure
     * @return static
     */
    public function addFMeasure(string|int|float|FHIRDecimalPrimitive|FHIRDecimal $fMeasure): self
    {
        if (!($fMeasure instanceof FHIRDecimal)) {
            $fMeasure = new FHIRDecimal(value: $fMeasure);
        }
        if (!isset($this->fMeasure)) {
            $this->fMeasure = [];
        }
        $this->fMeasure[] = $fMeasure;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Calculated fScore if the GQ score threshold was set to "score" field value.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal ...$fMeasure
     * @return static
     */
    public function setFMeasure(string|int|float|FHIRDecimalPrimitive|FHIRDecimal ...$fMeasure): self
    {
        if ([] === $fMeasure) {
            unset($this->fMeasure);
            return $this;
        }
        $this->fMeasure = [];
        foreach($fMeasure as $v) {
            if ($v instanceof FHIRDecimal) {
                $this->fMeasure[] = $v;
            } else {
                $this->fMeasure[] = new FHIRDecimal(value: $v);
            }
        }
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMolecularSequenceRoc)) {
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
            } else if (self::FIELD_SCORE === $cen) {
                $type->addScore(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NUM_TP === $cen) {
                $type->addNumTP(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NUM_FP === $cen) {
                $type->addNumFP(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NUM_FN === $cen) {
                $type->addNumFN(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRECISION === $cen) {
                $type->addPrecision(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SENSITIVITY === $cen) {
                $type->addSensitivity(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_F_MEASURE === $cen) {
                $type->addFMeasure(FHIRDecimal::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        parent::xmlSerialize($xw, $config);
        if (isset($this->score) && [] !== $this->score) {
            foreach($this->score as $v) {
                $xw->startElement(self::FIELD_SCORE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->numTP) && [] !== $this->numTP) {
            foreach($this->numTP as $v) {
                $xw->startElement(self::FIELD_NUM_TP);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->numFP) && [] !== $this->numFP) {
            foreach($this->numFP as $v) {
                $xw->startElement(self::FIELD_NUM_FP);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->numFN) && [] !== $this->numFN) {
            foreach($this->numFN as $v) {
                $xw->startElement(self::FIELD_NUM_FN);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->precision) && [] !== $this->precision) {
            foreach($this->precision as $v) {
                $xw->startElement(self::FIELD_PRECISION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->sensitivity) && [] !== $this->sensitivity) {
            foreach($this->sensitivity as $v) {
                $xw->startElement(self::FIELD_SENSITIVITY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->fMeasure) && [] !== $this->fMeasure) {
            foreach($this->fMeasure as $v) {
                $xw->startElement(self::FIELD_F_MEASURE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc
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
        } else if (!($type instanceof FHIRMolecularSequenceRoc)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->score)
            || isset($decoded->_score)
            || property_exists($decoded, self::FIELD_SCORE)
            || property_exists($decoded, self::FIELD_SCORE_EXT)) {
            $vals = (array)($decoded->score ?? []);
            $exts = (array)($decoded->_score ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addScore(FHIRInteger::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->numTP)
            || isset($decoded->_numTP)
            || property_exists($decoded, self::FIELD_NUM_TP)
            || property_exists($decoded, self::FIELD_NUM_TP_EXT)) {
            $vals = (array)($decoded->numTP ?? []);
            $exts = (array)($decoded->_numTP ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addNumTP(FHIRInteger::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->numFP)
            || isset($decoded->_numFP)
            || property_exists($decoded, self::FIELD_NUM_FP)
            || property_exists($decoded, self::FIELD_NUM_FP_EXT)) {
            $vals = (array)($decoded->numFP ?? []);
            $exts = (array)($decoded->_numFP ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addNumFP(FHIRInteger::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->numFN)
            || isset($decoded->_numFN)
            || property_exists($decoded, self::FIELD_NUM_FN)
            || property_exists($decoded, self::FIELD_NUM_FN_EXT)) {
            $vals = (array)($decoded->numFN ?? []);
            $exts = (array)($decoded->_numFN ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addNumFN(FHIRInteger::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->precision)
            || isset($decoded->_precision)
            || property_exists($decoded, self::FIELD_PRECISION)
            || property_exists($decoded, self::FIELD_PRECISION_EXT)) {
            $vals = (array)($decoded->precision ?? []);
            $exts = (array)($decoded->_precision ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addPrecision(FHIRDecimal::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->sensitivity)
            || isset($decoded->_sensitivity)
            || property_exists($decoded, self::FIELD_SENSITIVITY)
            || property_exists($decoded, self::FIELD_SENSITIVITY_EXT)) {
            $vals = (array)($decoded->sensitivity ?? []);
            $exts = (array)($decoded->_sensitivity ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addSensitivity(FHIRDecimal::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->fMeasure)
            || isset($decoded->_fMeasure)
            || property_exists($decoded, self::FIELD_F_MEASURE)
            || property_exists($decoded, self::FIELD_F_MEASURE_EXT)) {
            $vals = (array)($decoded->fMeasure ?? []);
            $exts = (array)($decoded->_fMeasure ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addFMeasure(FHIRDecimal::jsonUnserialize($v, $config));
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
        if (isset($this->score) && [] !== $this->score) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->score as $v) {
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
                $out->score = $vals;
            }
            if ($hasExts) {
                $out->_score = $exts;
            }
        }
        if (isset($this->numTP) && [] !== $this->numTP) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->numTP as $v) {
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
                $out->numTP = $vals;
            }
            if ($hasExts) {
                $out->_numTP = $exts;
            }
        }
        if (isset($this->numFP) && [] !== $this->numFP) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->numFP as $v) {
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
                $out->numFP = $vals;
            }
            if ($hasExts) {
                $out->_numFP = $exts;
            }
        }
        if (isset($this->numFN) && [] !== $this->numFN) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->numFN as $v) {
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
                $out->numFN = $vals;
            }
            if ($hasExts) {
                $out->_numFN = $exts;
            }
        }
        if (isset($this->precision) && [] !== $this->precision) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->precision as $v) {
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
                $out->precision = $vals;
            }
            if ($hasExts) {
                $out->_precision = $exts;
            }
        }
        if (isset($this->sensitivity) && [] !== $this->sensitivity) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->sensitivity as $v) {
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
                $out->sensitivity = $vals;
            }
            if ($hasExts) {
                $out->_sensitivity = $exts;
            }
        }
        if (isset($this->fMeasure) && [] !== $this->fMeasure) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->fMeasure as $v) {
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
                $out->fMeasure = $vals;
            }
            if ($hasExts) {
                $out->_fMeasure = $exts;
            }
        }
        return $out;
    }
}
