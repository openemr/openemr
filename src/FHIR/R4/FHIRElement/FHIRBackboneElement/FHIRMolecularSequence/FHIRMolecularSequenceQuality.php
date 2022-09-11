<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: September 10th, 2022 20:42+0000
 * 
 * PHPFHIR Copyright:
 * 
 * Copyright 2016-2022 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQualityType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * Raw data describing a biological sequence.
 *
 * Class FHIRMolecularSequenceQuality
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence
 */
class FHIRMolecularSequenceQuality extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY;
    const FIELD_TYPE = 'type';
    const FIELD_TYPE_EXT = '_type';
    const FIELD_STANDARD_SEQUENCE = 'standardSequence';
    const FIELD_START = 'start';
    const FIELD_START_EXT = '_start';
    const FIELD_END = 'end';
    const FIELD_END_EXT = '_end';
    const FIELD_SCORE = 'score';
    const FIELD_METHOD = 'method';
    const FIELD_TRUTH_TP = 'truthTP';
    const FIELD_TRUTH_TP_EXT = '_truthTP';
    const FIELD_QUERY_TP = 'queryTP';
    const FIELD_QUERY_TP_EXT = '_queryTP';
    const FIELD_TRUTH_FN = 'truthFN';
    const FIELD_TRUTH_FN_EXT = '_truthFN';
    const FIELD_QUERY_FP = 'queryFP';
    const FIELD_QUERY_FP_EXT = '_queryFP';
    const FIELD_GT_FP = 'gtFP';
    const FIELD_GT_FP_EXT = '_gtFP';
    const FIELD_PRECISION = 'precision';
    const FIELD_PRECISION_EXT = '_precision';
    const FIELD_RECALL = 'recall';
    const FIELD_RECALL_EXT = '_recall';
    const FIELD_F_SCORE = 'fScore';
    const FIELD_F_SCORE_EXT = '_fScore';
    const FIELD_ROC = 'roc';

    /** @var string */
    private $_xmlns = '';

    /**
     * Type for quality report.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * INDEL / SNP / Undefined variant.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQualityType
     */
    protected $type = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Gold standard sequence used for comparing against.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $standardSequence = null;

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the sequence. If the coordinate system is either 0-based or
     * 1-based, then start position is inclusive.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    protected $start = null;

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the sequence. If the coordinate system is 0-based then end is
     * exclusive and does not include the last position. If the coordinate system is
     * 1-base, then end is inclusive and includes the last position.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    protected $end = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    protected $score = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Which method is used to get sequence quality.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $method = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $truthTP = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $queryTP = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $truthFN = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $queryFP = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $gtFP = null;

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $precision = null;

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $recall = null;

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall /
     * (precision + recall).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $fScore = null;

    /**
     * Raw data describing a biological sequence.
     *
     * Receiver Operator Characteristic (ROC) Curve to give sensitivity/specificity
     * tradeoff.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc
     */
    protected $roc = null;

    /**
     * Validation map for fields in type MolecularSequence.Quality
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRMolecularSequenceQuality Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRMolecularSequenceQuality::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_TYPE]) || isset($data[self::FIELD_TYPE_EXT])) {
            $value = isset($data[self::FIELD_TYPE]) ? $data[self::FIELD_TYPE] : null;
            $ext = (isset($data[self::FIELD_TYPE_EXT]) && is_array($data[self::FIELD_TYPE_EXT])) ? $ext = $data[self::FIELD_TYPE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRQualityType) {
                    $this->setType($value);
                } else if (is_array($value)) {
                    $this->setType(new FHIRQualityType(array_merge($ext, $value)));
                } else {
                    $this->setType(new FHIRQualityType([FHIRQualityType::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setType(new FHIRQualityType($ext));
            }
        }
        if (isset($data[self::FIELD_STANDARD_SEQUENCE])) {
            if ($data[self::FIELD_STANDARD_SEQUENCE] instanceof FHIRCodeableConcept) {
                $this->setStandardSequence($data[self::FIELD_STANDARD_SEQUENCE]);
            } else {
                $this->setStandardSequence(new FHIRCodeableConcept($data[self::FIELD_STANDARD_SEQUENCE]));
            }
        }
        if (isset($data[self::FIELD_START]) || isset($data[self::FIELD_START_EXT])) {
            $value = isset($data[self::FIELD_START]) ? $data[self::FIELD_START] : null;
            $ext = (isset($data[self::FIELD_START_EXT]) && is_array($data[self::FIELD_START_EXT])) ? $ext = $data[self::FIELD_START_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRInteger) {
                    $this->setStart($value);
                } else if (is_array($value)) {
                    $this->setStart(new FHIRInteger(array_merge($ext, $value)));
                } else {
                    $this->setStart(new FHIRInteger([FHIRInteger::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setStart(new FHIRInteger($ext));
            }
        }
        if (isset($data[self::FIELD_END]) || isset($data[self::FIELD_END_EXT])) {
            $value = isset($data[self::FIELD_END]) ? $data[self::FIELD_END] : null;
            $ext = (isset($data[self::FIELD_END_EXT]) && is_array($data[self::FIELD_END_EXT])) ? $ext = $data[self::FIELD_END_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRInteger) {
                    $this->setEnd($value);
                } else if (is_array($value)) {
                    $this->setEnd(new FHIRInteger(array_merge($ext, $value)));
                } else {
                    $this->setEnd(new FHIRInteger([FHIRInteger::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setEnd(new FHIRInteger($ext));
            }
        }
        if (isset($data[self::FIELD_SCORE])) {
            if ($data[self::FIELD_SCORE] instanceof FHIRQuantity) {
                $this->setScore($data[self::FIELD_SCORE]);
            } else {
                $this->setScore(new FHIRQuantity($data[self::FIELD_SCORE]));
            }
        }
        if (isset($data[self::FIELD_METHOD])) {
            if ($data[self::FIELD_METHOD] instanceof FHIRCodeableConcept) {
                $this->setMethod($data[self::FIELD_METHOD]);
            } else {
                $this->setMethod(new FHIRCodeableConcept($data[self::FIELD_METHOD]));
            }
        }
        if (isset($data[self::FIELD_TRUTH_TP]) || isset($data[self::FIELD_TRUTH_TP_EXT])) {
            $value = isset($data[self::FIELD_TRUTH_TP]) ? $data[self::FIELD_TRUTH_TP] : null;
            $ext = (isset($data[self::FIELD_TRUTH_TP_EXT]) && is_array($data[self::FIELD_TRUTH_TP_EXT])) ? $ext = $data[self::FIELD_TRUTH_TP_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setTruthTP($value);
                } else if (is_array($value)) {
                    $this->setTruthTP(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setTruthTP(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setTruthTP(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_QUERY_TP]) || isset($data[self::FIELD_QUERY_TP_EXT])) {
            $value = isset($data[self::FIELD_QUERY_TP]) ? $data[self::FIELD_QUERY_TP] : null;
            $ext = (isset($data[self::FIELD_QUERY_TP_EXT]) && is_array($data[self::FIELD_QUERY_TP_EXT])) ? $ext = $data[self::FIELD_QUERY_TP_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setQueryTP($value);
                } else if (is_array($value)) {
                    $this->setQueryTP(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setQueryTP(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setQueryTP(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_TRUTH_FN]) || isset($data[self::FIELD_TRUTH_FN_EXT])) {
            $value = isset($data[self::FIELD_TRUTH_FN]) ? $data[self::FIELD_TRUTH_FN] : null;
            $ext = (isset($data[self::FIELD_TRUTH_FN_EXT]) && is_array($data[self::FIELD_TRUTH_FN_EXT])) ? $ext = $data[self::FIELD_TRUTH_FN_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setTruthFN($value);
                } else if (is_array($value)) {
                    $this->setTruthFN(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setTruthFN(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setTruthFN(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_QUERY_FP]) || isset($data[self::FIELD_QUERY_FP_EXT])) {
            $value = isset($data[self::FIELD_QUERY_FP]) ? $data[self::FIELD_QUERY_FP] : null;
            $ext = (isset($data[self::FIELD_QUERY_FP_EXT]) && is_array($data[self::FIELD_QUERY_FP_EXT])) ? $ext = $data[self::FIELD_QUERY_FP_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setQueryFP($value);
                } else if (is_array($value)) {
                    $this->setQueryFP(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setQueryFP(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setQueryFP(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_GT_FP]) || isset($data[self::FIELD_GT_FP_EXT])) {
            $value = isset($data[self::FIELD_GT_FP]) ? $data[self::FIELD_GT_FP] : null;
            $ext = (isset($data[self::FIELD_GT_FP_EXT]) && is_array($data[self::FIELD_GT_FP_EXT])) ? $ext = $data[self::FIELD_GT_FP_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setGtFP($value);
                } else if (is_array($value)) {
                    $this->setGtFP(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setGtFP(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setGtFP(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_PRECISION]) || isset($data[self::FIELD_PRECISION_EXT])) {
            $value = isset($data[self::FIELD_PRECISION]) ? $data[self::FIELD_PRECISION] : null;
            $ext = (isset($data[self::FIELD_PRECISION_EXT]) && is_array($data[self::FIELD_PRECISION_EXT])) ? $ext = $data[self::FIELD_PRECISION_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setPrecision($value);
                } else if (is_array($value)) {
                    $this->setPrecision(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setPrecision(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setPrecision(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_RECALL]) || isset($data[self::FIELD_RECALL_EXT])) {
            $value = isset($data[self::FIELD_RECALL]) ? $data[self::FIELD_RECALL] : null;
            $ext = (isset($data[self::FIELD_RECALL_EXT]) && is_array($data[self::FIELD_RECALL_EXT])) ? $ext = $data[self::FIELD_RECALL_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setRecall($value);
                } else if (is_array($value)) {
                    $this->setRecall(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setRecall(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setRecall(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_F_SCORE]) || isset($data[self::FIELD_F_SCORE_EXT])) {
            $value = isset($data[self::FIELD_F_SCORE]) ? $data[self::FIELD_F_SCORE] : null;
            $ext = (isset($data[self::FIELD_F_SCORE_EXT]) && is_array($data[self::FIELD_F_SCORE_EXT])) ? $ext = $data[self::FIELD_F_SCORE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setFScore($value);
                } else if (is_array($value)) {
                    $this->setFScore(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setFScore(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setFScore(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_ROC])) {
            if ($data[self::FIELD_ROC] instanceof FHIRMolecularSequenceRoc) {
                $this->setRoc($data[self::FIELD_ROC]);
            } else {
                $this->setRoc(new FHIRMolecularSequenceRoc($data[self::FIELD_ROC]));
            }
        }
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if ('' !==  $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<MolecularSequenceQuality{$xmlns}></MolecularSequenceQuality>";
    }

    /**
     * Type for quality report.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * INDEL / SNP / Undefined variant.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQualityType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type for quality report.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * INDEL / SNP / Undefined variant.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQualityType $type
     * @return static
     */
    public function setType(FHIRQualityType $type = null)
    {
        $this->_trackValueSet($this->type, $type);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStandardSequence()
    {
        return $this->standardSequence;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Gold standard sequence used for comparing against.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $standardSequence
     * @return static
     */
    public function setStandardSequence(FHIRCodeableConcept $standardSequence = null)
    {
        $this->_trackValueSet($this->standardSequence, $standardSequence);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the sequence. If the coordinate system is either 0-based or
     * 1-based, then start position is inclusive.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $start
     * @return static
     */
    public function setStart($start = null)
    {
        if (null !== $start && !($start instanceof FHIRInteger)) {
            $start = new FHIRInteger($start);
        }
        $this->_trackValueSet($this->start, $start);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getEnd()
    {
        return $this->end;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $end
     * @return static
     */
    public function setEnd($end = null)
    {
        if (null !== $end && !($end instanceof FHIRInteger)) {
            $end = new FHIRInteger($end);
        }
        $this->_trackValueSet($this->end, $end);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getScore()
    {
        return $this->score;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $score
     * @return static
     */
    public function setScore(FHIRQuantity $score = null)
    {
        $this->_trackValueSet($this->score, $score);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Which method is used to get sequence quality.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $method
     * @return static
     */
    public function setMethod(FHIRCodeableConcept $method = null)
    {
        $this->_trackValueSet($this->method, $method);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getTruthTP()
    {
        return $this->truthTP;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $truthTP
     * @return static
     */
    public function setTruthTP($truthTP = null)
    {
        if (null !== $truthTP && !($truthTP instanceof FHIRDecimal)) {
            $truthTP = new FHIRDecimal($truthTP);
        }
        $this->_trackValueSet($this->truthTP, $truthTP);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getQueryTP()
    {
        return $this->queryTP;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $queryTP
     * @return static
     */
    public function setQueryTP($queryTP = null)
    {
        if (null !== $queryTP && !($queryTP instanceof FHIRDecimal)) {
            $queryTP = new FHIRDecimal($queryTP);
        }
        $this->_trackValueSet($this->queryTP, $queryTP);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getTruthFN()
    {
        return $this->truthFN;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $truthFN
     * @return static
     */
    public function setTruthFN($truthFN = null)
    {
        if (null !== $truthFN && !($truthFN instanceof FHIRDecimal)) {
            $truthFN = new FHIRDecimal($truthFN);
        }
        $this->_trackValueSet($this->truthFN, $truthFN);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getQueryFP()
    {
        return $this->queryFP;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $queryFP
     * @return static
     */
    public function setQueryFP($queryFP = null)
    {
        if (null !== $queryFP && !($queryFP instanceof FHIRDecimal)) {
            $queryFP = new FHIRDecimal($queryFP);
        }
        $this->_trackValueSet($this->queryFP, $queryFP);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getGtFP()
    {
        return $this->gtFP;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $gtFP
     * @return static
     */
    public function setGtFP($gtFP = null)
    {
        if (null !== $gtFP && !($gtFP instanceof FHIRDecimal)) {
            $gtFP = new FHIRDecimal($gtFP);
        }
        $this->_trackValueSet($this->gtFP, $gtFP);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $precision
     * @return static
     */
    public function setPrecision($precision = null)
    {
        if (null !== $precision && !($precision instanceof FHIRDecimal)) {
            $precision = new FHIRDecimal($precision);
        }
        $this->_trackValueSet($this->precision, $precision);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getRecall()
    {
        return $this->recall;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $recall
     * @return static
     */
    public function setRecall($recall = null)
    {
        if (null !== $recall && !($recall instanceof FHIRDecimal)) {
            $recall = new FHIRDecimal($recall);
        }
        $this->_trackValueSet($this->recall, $recall);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getFScore()
    {
        return $this->fScore;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $fScore
     * @return static
     */
    public function setFScore($fScore = null)
    {
        if (null !== $fScore && !($fScore instanceof FHIRDecimal)) {
            $fScore = new FHIRDecimal($fScore);
        }
        $this->_trackValueSet($this->fScore, $fScore);
        $this->fScore = $fScore;
        return $this;
    }

    /**
     * Raw data describing a biological sequence.
     *
     * Receiver Operator Characteristic (ROC) Curve to give sensitivity/specificity
     * tradeoff.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc
     */
    public function getRoc()
    {
        return $this->roc;
    }

    /**
     * Raw data describing a biological sequence.
     *
     * Receiver Operator Characteristic (ROC) Curve to give sensitivity/specificity
     * tradeoff.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceRoc $roc
     * @return static
     */
    public function setRoc(FHIRMolecularSequenceRoc $roc = null)
    {
        $this->_trackValueSet($this->roc, $roc);
        $this->roc = $roc;
        return $this;
    }

    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = parent::_getValidationErrors();
        $validationRules = $this->_getValidationRules();
        if (null !== ($v = $this->getType())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TYPE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStandardSequence())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STANDARD_SEQUENCE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStart())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_START] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getEnd())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_END] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getScore())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_SCORE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getMethod())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_METHOD] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getTruthTP())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TRUTH_TP] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getQueryTP())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_QUERY_TP] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getTruthFN())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TRUTH_FN] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getQueryFP())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_QUERY_FP] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getGtFP())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_GT_FP] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getPrecision())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PRECISION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getRecall())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_RECALL] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getFScore())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_F_SCORE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getRoc())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ROC] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_TYPE])) {
            $v = $this->getType();
            foreach($validationRules[self::FIELD_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TYPE])) {
                        $errs[self::FIELD_TYPE] = [];
                    }
                    $errs[self::FIELD_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STANDARD_SEQUENCE])) {
            $v = $this->getStandardSequence();
            foreach($validationRules[self::FIELD_STANDARD_SEQUENCE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_STANDARD_SEQUENCE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STANDARD_SEQUENCE])) {
                        $errs[self::FIELD_STANDARD_SEQUENCE] = [];
                    }
                    $errs[self::FIELD_STANDARD_SEQUENCE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_START])) {
            $v = $this->getStart();
            foreach($validationRules[self::FIELD_START] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_START, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_START])) {
                        $errs[self::FIELD_START] = [];
                    }
                    $errs[self::FIELD_START][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_END])) {
            $v = $this->getEnd();
            foreach($validationRules[self::FIELD_END] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_END, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_END])) {
                        $errs[self::FIELD_END] = [];
                    }
                    $errs[self::FIELD_END][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SCORE])) {
            $v = $this->getScore();
            foreach($validationRules[self::FIELD_SCORE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_SCORE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SCORE])) {
                        $errs[self::FIELD_SCORE] = [];
                    }
                    $errs[self::FIELD_SCORE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_METHOD])) {
            $v = $this->getMethod();
            foreach($validationRules[self::FIELD_METHOD] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_METHOD, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_METHOD])) {
                        $errs[self::FIELD_METHOD] = [];
                    }
                    $errs[self::FIELD_METHOD][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TRUTH_TP])) {
            $v = $this->getTruthTP();
            foreach($validationRules[self::FIELD_TRUTH_TP] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_TRUTH_TP, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TRUTH_TP])) {
                        $errs[self::FIELD_TRUTH_TP] = [];
                    }
                    $errs[self::FIELD_TRUTH_TP][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_QUERY_TP])) {
            $v = $this->getQueryTP();
            foreach($validationRules[self::FIELD_QUERY_TP] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_QUERY_TP, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_QUERY_TP])) {
                        $errs[self::FIELD_QUERY_TP] = [];
                    }
                    $errs[self::FIELD_QUERY_TP][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TRUTH_FN])) {
            $v = $this->getTruthFN();
            foreach($validationRules[self::FIELD_TRUTH_FN] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_TRUTH_FN, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TRUTH_FN])) {
                        $errs[self::FIELD_TRUTH_FN] = [];
                    }
                    $errs[self::FIELD_TRUTH_FN][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_QUERY_FP])) {
            $v = $this->getQueryFP();
            foreach($validationRules[self::FIELD_QUERY_FP] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_QUERY_FP, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_QUERY_FP])) {
                        $errs[self::FIELD_QUERY_FP] = [];
                    }
                    $errs[self::FIELD_QUERY_FP][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_GT_FP])) {
            $v = $this->getGtFP();
            foreach($validationRules[self::FIELD_GT_FP] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_GT_FP, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_GT_FP])) {
                        $errs[self::FIELD_GT_FP] = [];
                    }
                    $errs[self::FIELD_GT_FP][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PRECISION])) {
            $v = $this->getPrecision();
            foreach($validationRules[self::FIELD_PRECISION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_PRECISION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PRECISION])) {
                        $errs[self::FIELD_PRECISION] = [];
                    }
                    $errs[self::FIELD_PRECISION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_RECALL])) {
            $v = $this->getRecall();
            foreach($validationRules[self::FIELD_RECALL] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_RECALL, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_RECALL])) {
                        $errs[self::FIELD_RECALL] = [];
                    }
                    $errs[self::FIELD_RECALL][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_F_SCORE])) {
            $v = $this->getFScore();
            foreach($validationRules[self::FIELD_F_SCORE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_F_SCORE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_F_SCORE])) {
                        $errs[self::FIELD_F_SCORE] = [];
                    }
                    $errs[self::FIELD_F_SCORE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ROC])) {
            $v = $this->getRoc();
            foreach($validationRules[self::FIELD_ROC] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_QUALITY, self::FIELD_ROC, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ROC])) {
                        $errs[self::FIELD_ROC] = [];
                    }
                    $errs[self::FIELD_ROC][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MODIFIER_EXTENSION])) {
            $v = $this->getModifierExtension();
            foreach($validationRules[self::FIELD_MODIFIER_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_BACKBONE_ELEMENT, self::FIELD_MODIFIER_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MODIFIER_EXTENSION])) {
                        $errs[self::FIELD_MODIFIER_EXTENSION] = [];
                    }
                    $errs[self::FIELD_MODIFIER_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceQuality $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceQuality
     */
    public static function xmlUnserialize($element = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            return null;
        }
        if (is_string($element)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($element, $libxmlOpts);
            if (false === $dom) {
                throw new \DomainException(sprintf('FHIRMolecularSequenceQuality::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRMolecularSequenceQuality::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRMolecularSequenceQuality(null);
        } elseif (!is_object($type) || !($type instanceof FHIRMolecularSequenceQuality)) {
            throw new \RuntimeException(sprintf(
                'FHIRMolecularSequenceQuality::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceQuality or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_TYPE === $n->nodeName) {
                $type->setType(FHIRQualityType::xmlUnserialize($n));
            } elseif (self::FIELD_STANDARD_SEQUENCE === $n->nodeName) {
                $type->setStandardSequence(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_START === $n->nodeName) {
                $type->setStart(FHIRInteger::xmlUnserialize($n));
            } elseif (self::FIELD_END === $n->nodeName) {
                $type->setEnd(FHIRInteger::xmlUnserialize($n));
            } elseif (self::FIELD_SCORE === $n->nodeName) {
                $type->setScore(FHIRQuantity::xmlUnserialize($n));
            } elseif (self::FIELD_METHOD === $n->nodeName) {
                $type->setMethod(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_TRUTH_TP === $n->nodeName) {
                $type->setTruthTP(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_QUERY_TP === $n->nodeName) {
                $type->setQueryTP(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_TRUTH_FN === $n->nodeName) {
                $type->setTruthFN(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_QUERY_FP === $n->nodeName) {
                $type->setQueryFP(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_GT_FP === $n->nodeName) {
                $type->setGtFP(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_PRECISION === $n->nodeName) {
                $type->setPrecision(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_RECALL === $n->nodeName) {
                $type->setRecall(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_F_SCORE === $n->nodeName) {
                $type->setFScore(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_ROC === $n->nodeName) {
                $type->setRoc(FHIRMolecularSequenceRoc::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_START);
        if (null !== $n) {
            $pt = $type->getStart();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setStart($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_END);
        if (null !== $n) {
            $pt = $type->getEnd();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setEnd($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_TRUTH_TP);
        if (null !== $n) {
            $pt = $type->getTruthTP();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setTruthTP($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_QUERY_TP);
        if (null !== $n) {
            $pt = $type->getQueryTP();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setQueryTP($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_TRUTH_FN);
        if (null !== $n) {
            $pt = $type->getTruthFN();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setTruthFN($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_QUERY_FP);
        if (null !== $n) {
            $pt = $type->getQueryFP();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setQueryFP($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_GT_FP);
        if (null !== $n) {
            $pt = $type->getGtFP();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setGtFP($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_PRECISION);
        if (null !== $n) {
            $pt = $type->getPrecision();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setPrecision($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_RECALL);
        if (null !== $n) {
            $pt = $type->getRecall();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setRecall($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_F_SCORE);
        if (null !== $n) {
            $pt = $type->getFScore();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setFScore($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ID);
        if (null !== $n) {
            $pt = $type->getId();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setId($n->nodeValue);
            }
        }
        return $type;
    }

    /**
     * @param null|\DOMElement $element
     * @param null|int $libxmlOpts
     * @return \DOMElement
     */
    public function xmlSerialize(\DOMElement $element = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            $dom = new \DOMDocument();
            $dom->loadXML($this->_getFHIRXMLElementDefinition(), $libxmlOpts);
            $element = $dom->documentElement;
        } elseif (null === $element->namespaceURI && '' !== ($xmlns = $this->_getFHIRXMLNamespace())) {
            $element->setAttribute('xmlns', $xmlns);
        }
        parent::xmlSerialize($element);
        if (null !== ($v = $this->getType())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TYPE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStandardSequence())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STANDARD_SEQUENCE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStart())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_START);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getEnd())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_END);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getScore())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_SCORE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getMethod())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_METHOD);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getTruthTP())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TRUTH_TP);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getQueryTP())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_QUERY_TP);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getTruthFN())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TRUTH_FN);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getQueryFP())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_QUERY_FP);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getGtFP())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_GT_FP);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getPrecision())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PRECISION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getRecall())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_RECALL);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getFScore())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_F_SCORE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getRoc())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ROC);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if (null !== ($v = $this->getType())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_TYPE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRQualityType::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_TYPE_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getStandardSequence())) {
            $a[self::FIELD_STANDARD_SEQUENCE] = $v;
        }
        if (null !== ($v = $this->getStart())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_START] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRInteger::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_START_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getEnd())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_END] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRInteger::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_END_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getScore())) {
            $a[self::FIELD_SCORE] = $v;
        }
        if (null !== ($v = $this->getMethod())) {
            $a[self::FIELD_METHOD] = $v;
        }
        if (null !== ($v = $this->getTruthTP())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_TRUTH_TP] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_TRUTH_TP_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getQueryTP())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_QUERY_TP] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_QUERY_TP_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getTruthFN())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_TRUTH_FN] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_TRUTH_FN_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getQueryFP())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_QUERY_FP] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_QUERY_FP_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getGtFP())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_GT_FP] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_GT_FP_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getPrecision())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_PRECISION] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_PRECISION_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getRecall())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_RECALL] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_RECALL_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getFScore())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_F_SCORE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_F_SCORE_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getRoc())) {
            $a[self::FIELD_ROC] = $v;
        }
        return $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}