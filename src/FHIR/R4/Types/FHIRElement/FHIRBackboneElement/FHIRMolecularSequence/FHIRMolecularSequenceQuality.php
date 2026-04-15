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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRQualityTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQualityType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Raw data describing a biological sequence.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMolecularSequenceQuality extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY;

    /* class_default.php:56 */
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_EXT = '_type';
    public const FIELD_STANDARD_SEQUENCE = 'standardSequence';
    public const FIELD_START = 'start';
    public const FIELD_START_EXT = '_start';
    public const FIELD_END = 'end';
    public const FIELD_END_EXT = '_end';
    public const FIELD_SCORE = 'score';
    public const FIELD_METHOD = 'method';
    public const FIELD_TRUTH_TP = 'truthTP';
    public const FIELD_TRUTH_TP_EXT = '_truthTP';
    public const FIELD_QUERY_TP = 'queryTP';
    public const FIELD_QUERY_TP_EXT = '_queryTP';
    public const FIELD_TRUTH_FN = 'truthFN';
    public const FIELD_TRUTH_FN_EXT = '_truthFN';
    public const FIELD_QUERY_FP = 'queryFP';
    public const FIELD_QUERY_FP_EXT = '_queryFP';
    public const FIELD_GT_FP = 'gtFP';
    public const FIELD_GT_FP_EXT = '_gtFP';
    public const FIELD_PRECISION = 'precision';
    public const FIELD_PRECISION_EXT = '_precision';
    public const FIELD_RECALL = 'recall';
    public const FIELD_RECALL_EXT = '_recall';
    public const FIELD_F_SCORE = 'fScore';
    public const FIELD_F_SCORE_EXT = '_fScore';
    public const FIELD_ROC = 'roc';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_TYPE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_START => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_END => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TRUTH_TP => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_QUERY_TP => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TRUTH_FN => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_QUERY_FP => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_GT_FP => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PRECISION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RECALL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_F_SCORE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Type for quality report.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * INDEL / SNP / Undefined variant.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQualityType
     */
    #[FHIRQualityType]
    protected FHIRQualityType $type;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Gold standard sequence used for comparing against.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $standardSequence;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the sequence. If the coordinate system is either 0-based or
     * 1-based, then start position is inclusive.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $start;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the sequence. If the coordinate system is 0-based then end is
     * exclusive and does not include the last position. If the coordinate system is
     * 1-base, then end is inclusive and includes the last position.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $end;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The score of an experimentally derived feature such as a p-value
     * ([SO:0001685](http://www.sequenceontology.org/browser/current_svn/term/SO:0001685)).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $score;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Which method is used to get sequence quality.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $method;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * True positives, from the perspective of the truth data, i.e. the number of sites
     * in the Truth Call Set for which there are paths through the Query Call Set that
     * are consistent with all of the alleles at this site, and for which there is an
     * accurate genotype call for the event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $truthTP;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * True positives, from the perspective of the query data, i.e. the number of sites
     * in the Query Call Set for which there are paths through the Truth Call Set that
     * are consistent with all of the alleles at this site, and for which there is an
     * accurate genotype call for the event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $queryTP;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * False negatives, i.e. the number of sites in the Truth Call Set for which there
     * is no path through the Query Call Set that is consistent with all of the alleles
     * at this site, or sites for which there is an inaccurate genotype call for the
     * event. Sites with correct variant but incorrect genotype are counted here.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $truthFN;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * False positives, i.e. the number of sites in the Query Call Set for which there
     * is no path through the Truth Call Set that is consistent with this site. Sites
     * with correct variant but incorrect genotype are counted here.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $queryFP;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false positives where the non-REF alleles in the Truth and Query
     * Call Sets match (i.e. cases where the truth is 1/1 and the query is 0/1 or
     * similar).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $gtFP;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $precision;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $recall;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall /
     * (precision + recall).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $fScore;
    /**
     * Raw data describing a biological sequence.
     *
     * Receiver Operator Characteristic (ROC) Curve to give sensitivity/specificity
     * tradeoff.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc
     */
    #[FHIRMolecularSequenceRoc]
    protected FHIRMolecularSequenceRoc $roc;

    /* constructor.php:61 */
    /**
     * FHIRMolecularSequenceQuality Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRQualityTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQualityType $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $standardSequence
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $start
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $end
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $score
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $method
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $truthTP
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $queryTP
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $truthFN
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $queryFP
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $gtFP
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $precision
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $recall
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $fScore
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc $roc
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRQualityTypeList|FHIRQualityType $type = null,
                                null|FHIRCodeableConcept $standardSequence = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $start = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $end = null,
                                null|FHIRQuantity $score = null,
                                null|FHIRCodeableConcept $method = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $truthTP = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $queryTP = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $truthFN = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $queryFP = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $gtFP = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $precision = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $recall = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $fScore = null,
                                null|FHIRMolecularSequenceRoc $roc = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $standardSequence) {
            $this->setStandardSequence($standardSequence);
        }
        if (null !== $start) {
            $this->setStart($start);
        }
        if (null !== $end) {
            $this->setEnd($end);
        }
        if (null !== $score) {
            $this->setScore($score);
        }
        if (null !== $method) {
            $this->setMethod($method);
        }
        if (null !== $truthTP) {
            $this->setTruthTP($truthTP);
        }
        if (null !== $queryTP) {
            $this->setQueryTP($queryTP);
        }
        if (null !== $truthFN) {
            $this->setTruthFN($truthFN);
        }
        if (null !== $queryFP) {
            $this->setQueryFP($queryFP);
        }
        if (null !== $gtFP) {
            $this->setGtFP($gtFP);
        }
        if (null !== $precision) {
            $this->setPrecision($precision);
        }
        if (null !== $recall) {
            $this->setRecall($recall);
        }
        if (null !== $fScore) {
            $this->setFScore($fScore);
        }
        if (null !== $roc) {
            $this->setRoc($roc);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Type for quality report.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * INDEL / SNP / Undefined variant.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQualityType
     */
    public function getType(): null|FHIRQualityType
    {
        return $this->type ?? null;
    }

    /**
     * Type for quality report.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * INDEL / SNP / Undefined variant.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRQualityTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQualityType $type
     * @return static
     */
    public function setType(null|string|FHIRQualityTypeList|FHIRQualityType $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        if (!($type instanceof FHIRQualityType)) {
            $type = new FHIRQualityType(value: $type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Gold standard sequence used for comparing against.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getStandardSequence(): null|FHIRCodeableConcept
    {
        return $this->standardSequence ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Gold standard sequence used for comparing against.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $standardSequence
     * @return static
     */
    public function setStandardSequence(null|FHIRCodeableConcept $standardSequence): self
    {
        if (null === $standardSequence) {
            unset($this->standardSequence);
            return $this;
        }
        $this->standardSequence = $standardSequence;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the sequence. If the coordinate system is either 0-based or
     * 1-based, then start position is inclusive.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getStart(): null|FHIRInteger
    {
        return $this->start ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the sequence. If the coordinate system is either 0-based or
     * 1-based, then start position is inclusive.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $start
     * @return static
     */
    public function setStart(null|string|float|FHIRIntegerPrimitive|FHIRInteger $start): self
    {
        if (null === $start) {
            unset($this->start);
            return $this;
        }
        if (!($start instanceof FHIRInteger)) {
            $start = new FHIRInteger(value: $start);
        }
        $this->start = $start;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the sequence. If the coordinate system is 0-based then end is
     * exclusive and does not include the last position. If the coordinate system is
     * 1-base, then end is inclusive and includes the last position.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getEnd(): null|FHIRInteger
    {
        return $this->end ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the sequence. If the coordinate system is 0-based then end is
     * exclusive and does not include the last position. If the coordinate system is
     * 1-base, then end is inclusive and includes the last position.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $end
     * @return static
     */
    public function setEnd(null|string|float|FHIRIntegerPrimitive|FHIRInteger $end): self
    {
        if (null === $end) {
            unset($this->end);
            return $this;
        }
        if (!($end instanceof FHIRInteger)) {
            $end = new FHIRInteger(value: $end);
        }
        $this->end = $end;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The score of an experimentally derived feature such as a p-value
     * ([SO:0001685](http://www.sequenceontology.org/browser/current_svn/term/SO:0001685)).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getScore(): null|FHIRQuantity
    {
        return $this->score ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The score of an experimentally derived feature such as a p-value
     * ([SO:0001685](http://www.sequenceontology.org/browser/current_svn/term/SO:0001685)).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $score
     * @return static
     */
    public function setScore(null|FHIRQuantity $score): self
    {
        if (null === $score) {
            unset($this->score);
            return $this;
        }
        $this->score = $score;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Which method is used to get sequence quality.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getMethod(): null|FHIRCodeableConcept
    {
        return $this->method ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Which method is used to get sequence quality.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $method
     * @return static
     */
    public function setMethod(null|FHIRCodeableConcept $method): self
    {
        if (null === $method) {
            unset($this->method);
            return $this;
        }
        $this->method = $method;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * True positives, from the perspective of the truth data, i.e. the number of sites
     * in the Truth Call Set for which there are paths through the Query Call Set that
     * are consistent with all of the alleles at this site, and for which there is an
     * accurate genotype call for the event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getTruthTP(): null|FHIRDecimal
    {
        return $this->truthTP ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * True positives, from the perspective of the truth data, i.e. the number of sites
     * in the Truth Call Set for which there are paths through the Query Call Set that
     * are consistent with all of the alleles at this site, and for which there is an
     * accurate genotype call for the event.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $truthTP
     * @return static
     */
    public function setTruthTP(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $truthTP): self
    {
        if (null === $truthTP) {
            unset($this->truthTP);
            return $this;
        }
        if (!($truthTP instanceof FHIRDecimal)) {
            $truthTP = new FHIRDecimal(value: $truthTP);
        }
        $this->truthTP = $truthTP;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * True positives, from the perspective of the query data, i.e. the number of sites
     * in the Query Call Set for which there are paths through the Truth Call Set that
     * are consistent with all of the alleles at this site, and for which there is an
     * accurate genotype call for the event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getQueryTP(): null|FHIRDecimal
    {
        return $this->queryTP ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * True positives, from the perspective of the query data, i.e. the number of sites
     * in the Query Call Set for which there are paths through the Truth Call Set that
     * are consistent with all of the alleles at this site, and for which there is an
     * accurate genotype call for the event.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $queryTP
     * @return static
     */
    public function setQueryTP(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $queryTP): self
    {
        if (null === $queryTP) {
            unset($this->queryTP);
            return $this;
        }
        if (!($queryTP instanceof FHIRDecimal)) {
            $queryTP = new FHIRDecimal(value: $queryTP);
        }
        $this->queryTP = $queryTP;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * False negatives, i.e. the number of sites in the Truth Call Set for which there
     * is no path through the Query Call Set that is consistent with all of the alleles
     * at this site, or sites for which there is an inaccurate genotype call for the
     * event. Sites with correct variant but incorrect genotype are counted here.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getTruthFN(): null|FHIRDecimal
    {
        return $this->truthFN ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * False negatives, i.e. the number of sites in the Truth Call Set for which there
     * is no path through the Query Call Set that is consistent with all of the alleles
     * at this site, or sites for which there is an inaccurate genotype call for the
     * event. Sites with correct variant but incorrect genotype are counted here.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $truthFN
     * @return static
     */
    public function setTruthFN(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $truthFN): self
    {
        if (null === $truthFN) {
            unset($this->truthFN);
            return $this;
        }
        if (!($truthFN instanceof FHIRDecimal)) {
            $truthFN = new FHIRDecimal(value: $truthFN);
        }
        $this->truthFN = $truthFN;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * False positives, i.e. the number of sites in the Query Call Set for which there
     * is no path through the Truth Call Set that is consistent with this site. Sites
     * with correct variant but incorrect genotype are counted here.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getQueryFP(): null|FHIRDecimal
    {
        return $this->queryFP ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * False positives, i.e. the number of sites in the Query Call Set for which there
     * is no path through the Truth Call Set that is consistent with this site. Sites
     * with correct variant but incorrect genotype are counted here.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $queryFP
     * @return static
     */
    public function setQueryFP(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $queryFP): self
    {
        if (null === $queryFP) {
            unset($this->queryFP);
            return $this;
        }
        if (!($queryFP instanceof FHIRDecimal)) {
            $queryFP = new FHIRDecimal(value: $queryFP);
        }
        $this->queryFP = $queryFP;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false positives where the non-REF alleles in the Truth and Query
     * Call Sets match (i.e. cases where the truth is 1/1 and the query is 0/1 or
     * similar).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getGtFP(): null|FHIRDecimal
    {
        return $this->gtFP ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The number of false positives where the non-REF alleles in the Truth and Query
     * Call Sets match (i.e. cases where the truth is 1/1 and the query is 0/1 or
     * similar).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $gtFP
     * @return static
     */
    public function setGtFP(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $gtFP): self
    {
        if (null === $gtFP) {
            unset($this->gtFP);
            return $this;
        }
        if (!($gtFP instanceof FHIRDecimal)) {
            $gtFP = new FHIRDecimal(value: $gtFP);
        }
        $this->gtFP = $gtFP;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getPrecision(): null|FHIRDecimal
    {
        return $this->precision ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $precision
     * @return static
     */
    public function setPrecision(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $precision): self
    {
        if (null === $precision) {
            unset($this->precision);
            return $this;
        }
        if (!($precision instanceof FHIRDecimal)) {
            $precision = new FHIRDecimal(value: $precision);
        }
        $this->precision = $precision;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getRecall(): null|FHIRDecimal
    {
        return $this->recall ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $recall
     * @return static
     */
    public function setRecall(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $recall): self
    {
        if (null === $recall) {
            unset($this->recall);
            return $this;
        }
        if (!($recall instanceof FHIRDecimal)) {
            $recall = new FHIRDecimal(value: $recall);
        }
        $this->recall = $recall;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall /
     * (precision + recall).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getFScore(): null|FHIRDecimal
    {
        return $this->fScore ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall /
     * (precision + recall).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $fScore
     * @return static
     */
    public function setFScore(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $fScore): self
    {
        if (null === $fScore) {
            unset($this->fScore);
            return $this;
        }
        if (!($fScore instanceof FHIRDecimal)) {
            $fScore = new FHIRDecimal(value: $fScore);
        }
        $this->fScore = $fScore;
        return $this;
    }

    /**
     * Raw data describing a biological sequence.
     *
     * Receiver Operator Characteristic (ROC) Curve to give sensitivity/specificity
     * tradeoff.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc
     */
    public function getRoc(): null|FHIRMolecularSequenceRoc
    {
        return $this->roc ?? null;
    }

    /**
     * Raw data describing a biological sequence.
     *
     * Receiver Operator Characteristic (ROC) Curve to give sensitivity/specificity
     * tradeoff.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc $roc
     * @return static
     */
    public function setRoc(null|FHIRMolecularSequenceRoc $roc): self
    {
        if (null === $roc) {
            unset($this->roc);
            return $this;
        }
        $this->roc = $roc;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceQuality $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceQuality
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMolecularSequenceQuality)) {
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
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRQualityType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STANDARD_SEQUENCE === $cen) {
                $type->setStandardSequence(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_START === $cen) {
                $type->setStart(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_END === $cen) {
                $type->setEnd(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SCORE === $cen) {
                $type->setScore(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_METHOD === $cen) {
                $type->setMethod(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TRUTH_TP === $cen) {
                $type->setTruthTP(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_QUERY_TP === $cen) {
                $type->setQueryTP(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TRUTH_FN === $cen) {
                $type->setTruthFN(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_QUERY_FP === $cen) {
                $type->setQueryFP(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GT_FP === $cen) {
                $type->setGtFP(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRECISION === $cen) {
                $type->setPrecision(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECALL === $cen) {
                $type->setRecall(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_F_SCORE === $cen) {
                $type->setFScore(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ROC === $cen) {
                $type->setRoc(FHIRMolecularSequenceRoc::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TYPE])) {
            if (isset($type->type)) {
                $type->type->setValue((string)$attributes[self::FIELD_TYPE]);
            } else {
                $type->setType((string)$attributes[self::FIELD_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_START])) {
            if (isset($type->start)) {
                $type->start->setValue((string)$attributes[self::FIELD_START]);
            } else {
                $type->setStart((string)$attributes[self::FIELD_START]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_START, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_END])) {
            if (isset($type->end)) {
                $type->end->setValue((string)$attributes[self::FIELD_END]);
            } else {
                $type->setEnd((string)$attributes[self::FIELD_END]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_END, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TRUTH_TP])) {
            if (isset($type->truthTP)) {
                $type->truthTP->setValue((string)$attributes[self::FIELD_TRUTH_TP]);
            } else {
                $type->setTruthTP((string)$attributes[self::FIELD_TRUTH_TP]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TRUTH_TP, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_QUERY_TP])) {
            if (isset($type->queryTP)) {
                $type->queryTP->setValue((string)$attributes[self::FIELD_QUERY_TP]);
            } else {
                $type->setQueryTP((string)$attributes[self::FIELD_QUERY_TP]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_QUERY_TP, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TRUTH_FN])) {
            if (isset($type->truthFN)) {
                $type->truthFN->setValue((string)$attributes[self::FIELD_TRUTH_FN]);
            } else {
                $type->setTruthFN((string)$attributes[self::FIELD_TRUTH_FN]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TRUTH_FN, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_QUERY_FP])) {
            if (isset($type->queryFP)) {
                $type->queryFP->setValue((string)$attributes[self::FIELD_QUERY_FP]);
            } else {
                $type->setQueryFP((string)$attributes[self::FIELD_QUERY_FP]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_QUERY_FP, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_GT_FP])) {
            if (isset($type->gtFP)) {
                $type->gtFP->setValue((string)$attributes[self::FIELD_GT_FP]);
            } else {
                $type->setGtFP((string)$attributes[self::FIELD_GT_FP]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_GT_FP, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PRECISION])) {
            if (isset($type->precision)) {
                $type->precision->setValue((string)$attributes[self::FIELD_PRECISION]);
            } else {
                $type->setPrecision((string)$attributes[self::FIELD_PRECISION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PRECISION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RECALL])) {
            if (isset($type->recall)) {
                $type->recall->setValue((string)$attributes[self::FIELD_RECALL]);
            } else {
                $type->setRecall((string)$attributes[self::FIELD_RECALL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RECALL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_F_SCORE])) {
            if (isset($type->fScore)) {
                $type->fScore->setValue((string)$attributes[self::FIELD_F_SCORE]);
            } else {
                $type->setFScore((string)$attributes[self::FIELD_F_SCORE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_F_SCORE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->type) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TYPE]) {
            $xw->writeAttribute(self::FIELD_TYPE, $this->type->_getValueAsString());
        }
        if (isset($this->start) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_START]) {
            $xw->writeAttribute(self::FIELD_START, $this->start->_getValueAsString());
        }
        if (isset($this->end) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_END]) {
            $xw->writeAttribute(self::FIELD_END, $this->end->_getValueAsString());
        }
        if (isset($this->truthTP) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TRUTH_TP]) {
            $xw->writeAttribute(self::FIELD_TRUTH_TP, $this->truthTP->_getValueAsString());
        }
        if (isset($this->queryTP) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_QUERY_TP]) {
            $xw->writeAttribute(self::FIELD_QUERY_TP, $this->queryTP->_getValueAsString());
        }
        if (isset($this->truthFN) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TRUTH_FN]) {
            $xw->writeAttribute(self::FIELD_TRUTH_FN, $this->truthFN->_getValueAsString());
        }
        if (isset($this->queryFP) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_QUERY_FP]) {
            $xw->writeAttribute(self::FIELD_QUERY_FP, $this->queryFP->_getValueAsString());
        }
        if (isset($this->gtFP) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_GT_FP]) {
            $xw->writeAttribute(self::FIELD_GT_FP, $this->gtFP->_getValueAsString());
        }
        if (isset($this->precision) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PRECISION]) {
            $xw->writeAttribute(self::FIELD_PRECISION, $this->precision->_getValueAsString());
        }
        if (isset($this->recall) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RECALL]) {
            $xw->writeAttribute(self::FIELD_RECALL, $this->recall->_getValueAsString());
        }
        if (isset($this->fScore) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_F_SCORE]) {
            $xw->writeAttribute(self::FIELD_F_SCORE, $this->fScore->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->type)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TYPE]
                || $this->type->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TYPE]);
            $xw->endElement();
        }
        if (isset($this->standardSequence)) {
            $xw->startElement(self::FIELD_STANDARD_SEQUENCE);
            $this->standardSequence->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->start)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_START]
                || $this->start->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_START);
            $this->start->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_START]);
            $xw->endElement();
        }
        if (isset($this->end)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_END]
                || $this->end->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_END);
            $this->end->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_END]);
            $xw->endElement();
        }
        if (isset($this->score)) {
            $xw->startElement(self::FIELD_SCORE);
            $this->score->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->method)) {
            $xw->startElement(self::FIELD_METHOD);
            $this->method->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->truthTP)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TRUTH_TP]
                || $this->truthTP->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TRUTH_TP);
            $this->truthTP->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TRUTH_TP]);
            $xw->endElement();
        }
        if (isset($this->queryTP)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_QUERY_TP]
                || $this->queryTP->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_QUERY_TP);
            $this->queryTP->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_QUERY_TP]);
            $xw->endElement();
        }
        if (isset($this->truthFN)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TRUTH_FN]
                || $this->truthFN->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TRUTH_FN);
            $this->truthFN->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TRUTH_FN]);
            $xw->endElement();
        }
        if (isset($this->queryFP)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_QUERY_FP]
                || $this->queryFP->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_QUERY_FP);
            $this->queryFP->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_QUERY_FP]);
            $xw->endElement();
        }
        if (isset($this->gtFP)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_GT_FP]
                || $this->gtFP->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_GT_FP);
            $this->gtFP->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_GT_FP]);
            $xw->endElement();
        }
        if (isset($this->precision)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PRECISION]
                || $this->precision->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PRECISION);
            $this->precision->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PRECISION]);
            $xw->endElement();
        }
        if (isset($this->recall)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RECALL]
                || $this->recall->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RECALL);
            $this->recall->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RECALL]);
            $xw->endElement();
        }
        if (isset($this->fScore)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_F_SCORE]
                || $this->fScore->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_F_SCORE);
            $this->fScore->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_F_SCORE]);
            $xw->endElement();
        }
        if (isset($this->roc)) {
            $xw->startElement(self::FIELD_ROC);
            $this->roc->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceQuality $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceQuality
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
        } else if (!($type instanceof FHIRMolecularSequenceQuality)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->type)
            || isset($decoded->_type)
            || property_exists($decoded, self::FIELD_TYPE)
            || property_exists($decoded, self::FIELD_TYPE_EXT)) {
            $v = $decoded->_type ?? new \stdClass();
            $v->value = $decoded->type ?? null;
            $type->setType(FHIRQualityType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->standardSequence) || property_exists($decoded, self::FIELD_STANDARD_SEQUENCE)) {
            if (is_array($decoded->standardSequence)) {
                $type->setStandardSequence(FHIRCodeableConcept::jsonUnserialize(reset($decoded->standardSequence), $config));
            } else {
                $type->setStandardSequence(FHIRCodeableConcept::jsonUnserialize($decoded->standardSequence, $config));
            }
        }
        if (isset($decoded->start)
            || isset($decoded->_start)
            || property_exists($decoded, self::FIELD_START)
            || property_exists($decoded, self::FIELD_START_EXT)) {
            $v = $decoded->_start ?? new \stdClass();
            $v->value = $decoded->start ?? null;
            $type->setStart(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->end)
            || isset($decoded->_end)
            || property_exists($decoded, self::FIELD_END)
            || property_exists($decoded, self::FIELD_END_EXT)) {
            $v = $decoded->_end ?? new \stdClass();
            $v->value = $decoded->end ?? null;
            $type->setEnd(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->score) || property_exists($decoded, self::FIELD_SCORE)) {
            if (is_array($decoded->score)) {
                $type->setScore(FHIRQuantity::jsonUnserialize(reset($decoded->score), $config));
            } else {
                $type->setScore(FHIRQuantity::jsonUnserialize($decoded->score, $config));
            }
        }
        if (isset($decoded->method) || property_exists($decoded, self::FIELD_METHOD)) {
            if (is_array($decoded->method)) {
                $type->setMethod(FHIRCodeableConcept::jsonUnserialize(reset($decoded->method), $config));
            } else {
                $type->setMethod(FHIRCodeableConcept::jsonUnserialize($decoded->method, $config));
            }
        }
        if (isset($decoded->truthTP)
            || isset($decoded->_truthTP)
            || property_exists($decoded, self::FIELD_TRUTH_TP)
            || property_exists($decoded, self::FIELD_TRUTH_TP_EXT)) {
            $v = $decoded->_truthTP ?? new \stdClass();
            $v->value = $decoded->truthTP ?? null;
            $type->setTruthTP(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->queryTP)
            || isset($decoded->_queryTP)
            || property_exists($decoded, self::FIELD_QUERY_TP)
            || property_exists($decoded, self::FIELD_QUERY_TP_EXT)) {
            $v = $decoded->_queryTP ?? new \stdClass();
            $v->value = $decoded->queryTP ?? null;
            $type->setQueryTP(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->truthFN)
            || isset($decoded->_truthFN)
            || property_exists($decoded, self::FIELD_TRUTH_FN)
            || property_exists($decoded, self::FIELD_TRUTH_FN_EXT)) {
            $v = $decoded->_truthFN ?? new \stdClass();
            $v->value = $decoded->truthFN ?? null;
            $type->setTruthFN(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->queryFP)
            || isset($decoded->_queryFP)
            || property_exists($decoded, self::FIELD_QUERY_FP)
            || property_exists($decoded, self::FIELD_QUERY_FP_EXT)) {
            $v = $decoded->_queryFP ?? new \stdClass();
            $v->value = $decoded->queryFP ?? null;
            $type->setQueryFP(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->gtFP)
            || isset($decoded->_gtFP)
            || property_exists($decoded, self::FIELD_GT_FP)
            || property_exists($decoded, self::FIELD_GT_FP_EXT)) {
            $v = $decoded->_gtFP ?? new \stdClass();
            $v->value = $decoded->gtFP ?? null;
            $type->setGtFP(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->precision)
            || isset($decoded->_precision)
            || property_exists($decoded, self::FIELD_PRECISION)
            || property_exists($decoded, self::FIELD_PRECISION_EXT)) {
            $v = $decoded->_precision ?? new \stdClass();
            $v->value = $decoded->precision ?? null;
            $type->setPrecision(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->recall)
            || isset($decoded->_recall)
            || property_exists($decoded, self::FIELD_RECALL)
            || property_exists($decoded, self::FIELD_RECALL_EXT)) {
            $v = $decoded->_recall ?? new \stdClass();
            $v->value = $decoded->recall ?? null;
            $type->setRecall(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->fScore)
            || isset($decoded->_fScore)
            || property_exists($decoded, self::FIELD_F_SCORE)
            || property_exists($decoded, self::FIELD_F_SCORE_EXT)) {
            $v = $decoded->_fScore ?? new \stdClass();
            $v->value = $decoded->fScore ?? null;
            $type->setFScore(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->roc) || property_exists($decoded, self::FIELD_ROC)) {
            if (is_array($decoded->roc)) {
                $type->setRoc(FHIRMolecularSequenceRoc::jsonUnserialize(reset($decoded->roc), $config));
            } else {
                $type->setRoc(FHIRMolecularSequenceRoc::jsonUnserialize($decoded->roc, $config));
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
        if (isset($this->standardSequence)) {
            $out->standardSequence = $this->standardSequence;
        }
        if (isset($this->start)) {
            if (null !== ($val = $this->start->getValue())) {
                $out->start = $val;
            }
            if ($this->start->_nonValueFieldDefined()) {
                $ext = $this->start->jsonSerialize();
                unset($ext->value);
                $out->_start = $ext;
            }
        }
        if (isset($this->end)) {
            if (null !== ($val = $this->end->getValue())) {
                $out->end = $val;
            }
            if ($this->end->_nonValueFieldDefined()) {
                $ext = $this->end->jsonSerialize();
                unset($ext->value);
                $out->_end = $ext;
            }
        }
        if (isset($this->score)) {
            $out->score = $this->score;
        }
        if (isset($this->method)) {
            $out->method = $this->method;
        }
        if (isset($this->truthTP)) {
            if (null !== ($val = $this->truthTP->getValue())) {
                $out->truthTP = $val;
            }
            if ($this->truthTP->_nonValueFieldDefined()) {
                $ext = $this->truthTP->jsonSerialize();
                unset($ext->value);
                $out->_truthTP = $ext;
            }
        }
        if (isset($this->queryTP)) {
            if (null !== ($val = $this->queryTP->getValue())) {
                $out->queryTP = $val;
            }
            if ($this->queryTP->_nonValueFieldDefined()) {
                $ext = $this->queryTP->jsonSerialize();
                unset($ext->value);
                $out->_queryTP = $ext;
            }
        }
        if (isset($this->truthFN)) {
            if (null !== ($val = $this->truthFN->getValue())) {
                $out->truthFN = $val;
            }
            if ($this->truthFN->_nonValueFieldDefined()) {
                $ext = $this->truthFN->jsonSerialize();
                unset($ext->value);
                $out->_truthFN = $ext;
            }
        }
        if (isset($this->queryFP)) {
            if (null !== ($val = $this->queryFP->getValue())) {
                $out->queryFP = $val;
            }
            if ($this->queryFP->_nonValueFieldDefined()) {
                $ext = $this->queryFP->jsonSerialize();
                unset($ext->value);
                $out->_queryFP = $ext;
            }
        }
        if (isset($this->gtFP)) {
            if (null !== ($val = $this->gtFP->getValue())) {
                $out->gtFP = $val;
            }
            if ($this->gtFP->_nonValueFieldDefined()) {
                $ext = $this->gtFP->jsonSerialize();
                unset($ext->value);
                $out->_gtFP = $ext;
            }
        }
        if (isset($this->precision)) {
            if (null !== ($val = $this->precision->getValue())) {
                $out->precision = $val;
            }
            if ($this->precision->_nonValueFieldDefined()) {
                $ext = $this->precision->jsonSerialize();
                unset($ext->value);
                $out->_precision = $ext;
            }
        }
        if (isset($this->recall)) {
            if (null !== ($val = $this->recall->getValue())) {
                $out->recall = $val;
            }
            if ($this->recall->_nonValueFieldDefined()) {
                $ext = $this->recall->jsonSerialize();
                unset($ext->value);
                $out->_recall = $ext;
            }
        }
        if (isset($this->fScore)) {
            if (null !== ($val = $this->fScore->getValue())) {
                $out->fScore = $val;
            }
            if ($this->fScore->_nonValueFieldDefined()) {
                $ext = $this->fScore->jsonSerialize();
                unset($ext->value);
                $out->_fScore = $ext;
            }
        }
        if (isset($this->roc)) {
            $out->roc = $this->roc;
        }
        return $out;
    }
}
