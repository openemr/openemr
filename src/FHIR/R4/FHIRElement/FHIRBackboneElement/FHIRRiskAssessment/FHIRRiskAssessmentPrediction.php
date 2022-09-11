<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment;

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
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRange;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * An assessment of the likely outcome(s) for a patient or other subject as well as
 * the likelihood of each outcome.
 *
 * Class FHIRRiskAssessmentPrediction
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment
 */
class FHIRRiskAssessmentPrediction extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION;
    const FIELD_OUTCOME = 'outcome';
    const FIELD_PROBABILITY_DECIMAL = 'probabilityDecimal';
    const FIELD_PROBABILITY_DECIMAL_EXT = '_probabilityDecimal';
    const FIELD_PROBABILITY_RANGE = 'probabilityRange';
    const FIELD_QUALITATIVE_RISK = 'qualitativeRisk';
    const FIELD_RELATIVE_RISK = 'relativeRisk';
    const FIELD_RELATIVE_RISK_EXT = '_relativeRisk';
    const FIELD_WHEN_PERIOD = 'whenPeriod';
    const FIELD_WHEN_RANGE = 'whenRange';
    const FIELD_RATIONALE = 'rationale';
    const FIELD_RATIONALE_EXT = '_rationale';

    /** @var string */
    private $_xmlns = '';

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * One of the potential outcomes for the patient (e.g. remission, death, a
     * particular condition).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $outcome = null;

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $probabilityDecimal = null;

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    protected $probabilityRange = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe), expressed as a
     * qualitative value (e.g. low, medium, or high).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $qualitativeRisk = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    protected $relativeRisk = null;

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    protected $whenPeriod = null;

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    protected $whenRange = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional information explaining the basis for the prediction.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $rationale = null;

    /**
     * Validation map for fields in type RiskAssessment.Prediction
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRRiskAssessmentPrediction Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRRiskAssessmentPrediction::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_OUTCOME])) {
            if ($data[self::FIELD_OUTCOME] instanceof FHIRCodeableConcept) {
                $this->setOutcome($data[self::FIELD_OUTCOME]);
            } else {
                $this->setOutcome(new FHIRCodeableConcept($data[self::FIELD_OUTCOME]));
            }
        }
        if (isset($data[self::FIELD_PROBABILITY_DECIMAL]) || isset($data[self::FIELD_PROBABILITY_DECIMAL_EXT])) {
            $value = isset($data[self::FIELD_PROBABILITY_DECIMAL]) ? $data[self::FIELD_PROBABILITY_DECIMAL] : null;
            $ext = (isset($data[self::FIELD_PROBABILITY_DECIMAL_EXT]) && is_array($data[self::FIELD_PROBABILITY_DECIMAL_EXT])) ? $ext = $data[self::FIELD_PROBABILITY_DECIMAL_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setProbabilityDecimal($value);
                } else if (is_array($value)) {
                    $this->setProbabilityDecimal(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setProbabilityDecimal(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setProbabilityDecimal(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_PROBABILITY_RANGE])) {
            if ($data[self::FIELD_PROBABILITY_RANGE] instanceof FHIRRange) {
                $this->setProbabilityRange($data[self::FIELD_PROBABILITY_RANGE]);
            } else {
                $this->setProbabilityRange(new FHIRRange($data[self::FIELD_PROBABILITY_RANGE]));
            }
        }
        if (isset($data[self::FIELD_QUALITATIVE_RISK])) {
            if ($data[self::FIELD_QUALITATIVE_RISK] instanceof FHIRCodeableConcept) {
                $this->setQualitativeRisk($data[self::FIELD_QUALITATIVE_RISK]);
            } else {
                $this->setQualitativeRisk(new FHIRCodeableConcept($data[self::FIELD_QUALITATIVE_RISK]));
            }
        }
        if (isset($data[self::FIELD_RELATIVE_RISK]) || isset($data[self::FIELD_RELATIVE_RISK_EXT])) {
            $value = isset($data[self::FIELD_RELATIVE_RISK]) ? $data[self::FIELD_RELATIVE_RISK] : null;
            $ext = (isset($data[self::FIELD_RELATIVE_RISK_EXT]) && is_array($data[self::FIELD_RELATIVE_RISK_EXT])) ? $ext = $data[self::FIELD_RELATIVE_RISK_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDecimal) {
                    $this->setRelativeRisk($value);
                } else if (is_array($value)) {
                    $this->setRelativeRisk(new FHIRDecimal(array_merge($ext, $value)));
                } else {
                    $this->setRelativeRisk(new FHIRDecimal([FHIRDecimal::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setRelativeRisk(new FHIRDecimal($ext));
            }
        }
        if (isset($data[self::FIELD_WHEN_PERIOD])) {
            if ($data[self::FIELD_WHEN_PERIOD] instanceof FHIRPeriod) {
                $this->setWhenPeriod($data[self::FIELD_WHEN_PERIOD]);
            } else {
                $this->setWhenPeriod(new FHIRPeriod($data[self::FIELD_WHEN_PERIOD]));
            }
        }
        if (isset($data[self::FIELD_WHEN_RANGE])) {
            if ($data[self::FIELD_WHEN_RANGE] instanceof FHIRRange) {
                $this->setWhenRange($data[self::FIELD_WHEN_RANGE]);
            } else {
                $this->setWhenRange(new FHIRRange($data[self::FIELD_WHEN_RANGE]));
            }
        }
        if (isset($data[self::FIELD_RATIONALE]) || isset($data[self::FIELD_RATIONALE_EXT])) {
            $value = isset($data[self::FIELD_RATIONALE]) ? $data[self::FIELD_RATIONALE] : null;
            $ext = (isset($data[self::FIELD_RATIONALE_EXT]) && is_array($data[self::FIELD_RATIONALE_EXT])) ? $ext = $data[self::FIELD_RATIONALE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setRationale($value);
                } else if (is_array($value)) {
                    $this->setRationale(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setRationale(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setRationale(new FHIRString($ext));
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
        return "<RiskAssessmentPrediction{$xmlns}></RiskAssessmentPrediction>";
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getOutcome()
    {
        return $this->outcome;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $outcome
     * @return static
     */
    public function setOutcome(FHIRCodeableConcept $outcome = null)
    {
        $this->_trackValueSet($this->outcome, $outcome);
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getProbabilityDecimal()
    {
        return $this->probabilityDecimal;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $probabilityDecimal
     * @return static
     */
    public function setProbabilityDecimal($probabilityDecimal = null)
    {
        if (null !== $probabilityDecimal && !($probabilityDecimal instanceof FHIRDecimal)) {
            $probabilityDecimal = new FHIRDecimal($probabilityDecimal);
        }
        $this->_trackValueSet($this->probabilityDecimal, $probabilityDecimal);
        $this->probabilityDecimal = $probabilityDecimal;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getProbabilityRange()
    {
        return $this->probabilityRange;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates how likely the outcome is (in the specified timeframe).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRange $probabilityRange
     * @return static
     */
    public function setProbabilityRange(FHIRRange $probabilityRange = null)
    {
        $this->_trackValueSet($this->probabilityRange, $probabilityRange);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getQualitativeRisk()
    {
        return $this->qualitativeRisk;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $qualitativeRisk
     * @return static
     */
    public function setQualitativeRisk(FHIRCodeableConcept $qualitativeRisk = null)
    {
        $this->_trackValueSet($this->qualitativeRisk, $qualitativeRisk);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getRelativeRisk()
    {
        return $this->relativeRisk;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $relativeRisk
     * @return static
     */
    public function setRelativeRisk($relativeRisk = null)
    {
        if (null !== $relativeRisk && !($relativeRisk instanceof FHIRDecimal)) {
            $relativeRisk = new FHIRDecimal($relativeRisk);
        }
        $this->_trackValueSet($this->relativeRisk, $relativeRisk);
        $this->relativeRisk = $relativeRisk;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getWhenPeriod()
    {
        return $this->whenPeriod;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $whenPeriod
     * @return static
     */
    public function setWhenPeriod(FHIRPeriod $whenPeriod = null)
    {
        $this->_trackValueSet($this->whenPeriod, $whenPeriod);
        $this->whenPeriod = $whenPeriod;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getWhenRange()
    {
        return $this->whenRange;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the period of time or age range of the subject to which the specified
     * probability applies.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRange $whenRange
     * @return static
     */
    public function setWhenRange(FHIRRange $whenRange = null)
    {
        $this->_trackValueSet($this->whenRange, $whenRange);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getRationale()
    {
        return $this->rationale;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional information explaining the basis for the prediction.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $rationale
     * @return static
     */
    public function setRationale($rationale = null)
    {
        if (null !== $rationale && !($rationale instanceof FHIRString)) {
            $rationale = new FHIRString($rationale);
        }
        $this->_trackValueSet($this->rationale, $rationale);
        $this->rationale = $rationale;
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
        if (null !== ($v = $this->getOutcome())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_OUTCOME] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getProbabilityDecimal())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PROBABILITY_DECIMAL] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getProbabilityRange())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PROBABILITY_RANGE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getQualitativeRisk())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_QUALITATIVE_RISK] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getRelativeRisk())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_RELATIVE_RISK] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getWhenPeriod())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_WHEN_PERIOD] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getWhenRange())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_WHEN_RANGE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getRationale())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_RATIONALE] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_OUTCOME])) {
            $v = $this->getOutcome();
            foreach($validationRules[self::FIELD_OUTCOME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION, self::FIELD_OUTCOME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_OUTCOME])) {
                        $errs[self::FIELD_OUTCOME] = [];
                    }
                    $errs[self::FIELD_OUTCOME][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PROBABILITY_DECIMAL])) {
            $v = $this->getProbabilityDecimal();
            foreach($validationRules[self::FIELD_PROBABILITY_DECIMAL] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION, self::FIELD_PROBABILITY_DECIMAL, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PROBABILITY_DECIMAL])) {
                        $errs[self::FIELD_PROBABILITY_DECIMAL] = [];
                    }
                    $errs[self::FIELD_PROBABILITY_DECIMAL][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PROBABILITY_RANGE])) {
            $v = $this->getProbabilityRange();
            foreach($validationRules[self::FIELD_PROBABILITY_RANGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION, self::FIELD_PROBABILITY_RANGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PROBABILITY_RANGE])) {
                        $errs[self::FIELD_PROBABILITY_RANGE] = [];
                    }
                    $errs[self::FIELD_PROBABILITY_RANGE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_QUALITATIVE_RISK])) {
            $v = $this->getQualitativeRisk();
            foreach($validationRules[self::FIELD_QUALITATIVE_RISK] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION, self::FIELD_QUALITATIVE_RISK, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_QUALITATIVE_RISK])) {
                        $errs[self::FIELD_QUALITATIVE_RISK] = [];
                    }
                    $errs[self::FIELD_QUALITATIVE_RISK][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_RELATIVE_RISK])) {
            $v = $this->getRelativeRisk();
            foreach($validationRules[self::FIELD_RELATIVE_RISK] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION, self::FIELD_RELATIVE_RISK, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_RELATIVE_RISK])) {
                        $errs[self::FIELD_RELATIVE_RISK] = [];
                    }
                    $errs[self::FIELD_RELATIVE_RISK][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_WHEN_PERIOD])) {
            $v = $this->getWhenPeriod();
            foreach($validationRules[self::FIELD_WHEN_PERIOD] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION, self::FIELD_WHEN_PERIOD, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_WHEN_PERIOD])) {
                        $errs[self::FIELD_WHEN_PERIOD] = [];
                    }
                    $errs[self::FIELD_WHEN_PERIOD][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_WHEN_RANGE])) {
            $v = $this->getWhenRange();
            foreach($validationRules[self::FIELD_WHEN_RANGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION, self::FIELD_WHEN_RANGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_WHEN_RANGE])) {
                        $errs[self::FIELD_WHEN_RANGE] = [];
                    }
                    $errs[self::FIELD_WHEN_RANGE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_RATIONALE])) {
            $v = $this->getRationale();
            foreach($validationRules[self::FIELD_RATIONALE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RISK_ASSESSMENT_DOT_PREDICTION, self::FIELD_RATIONALE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_RATIONALE])) {
                        $errs[self::FIELD_RATIONALE] = [];
                    }
                    $errs[self::FIELD_RATIONALE][$rule] = $err;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment\FHIRRiskAssessmentPrediction $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment\FHIRRiskAssessmentPrediction
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
                throw new \DomainException(sprintf('FHIRRiskAssessmentPrediction::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRRiskAssessmentPrediction::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRRiskAssessmentPrediction(null);
        } elseif (!is_object($type) || !($type instanceof FHIRRiskAssessmentPrediction)) {
            throw new \RuntimeException(sprintf(
                'FHIRRiskAssessmentPrediction::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRRiskAssessment\FHIRRiskAssessmentPrediction or null, %s seen.',
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
            if (self::FIELD_OUTCOME === $n->nodeName) {
                $type->setOutcome(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_PROBABILITY_DECIMAL === $n->nodeName) {
                $type->setProbabilityDecimal(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_PROBABILITY_RANGE === $n->nodeName) {
                $type->setProbabilityRange(FHIRRange::xmlUnserialize($n));
            } elseif (self::FIELD_QUALITATIVE_RISK === $n->nodeName) {
                $type->setQualitativeRisk(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_RELATIVE_RISK === $n->nodeName) {
                $type->setRelativeRisk(FHIRDecimal::xmlUnserialize($n));
            } elseif (self::FIELD_WHEN_PERIOD === $n->nodeName) {
                $type->setWhenPeriod(FHIRPeriod::xmlUnserialize($n));
            } elseif (self::FIELD_WHEN_RANGE === $n->nodeName) {
                $type->setWhenRange(FHIRRange::xmlUnserialize($n));
            } elseif (self::FIELD_RATIONALE === $n->nodeName) {
                $type->setRationale(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_PROBABILITY_DECIMAL);
        if (null !== $n) {
            $pt = $type->getProbabilityDecimal();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setProbabilityDecimal($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_RELATIVE_RISK);
        if (null !== $n) {
            $pt = $type->getRelativeRisk();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setRelativeRisk($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_RATIONALE);
        if (null !== $n) {
            $pt = $type->getRationale();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setRationale($n->nodeValue);
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
        if (null !== ($v = $this->getOutcome())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_OUTCOME);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getProbabilityDecimal())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PROBABILITY_DECIMAL);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getProbabilityRange())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PROBABILITY_RANGE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getQualitativeRisk())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_QUALITATIVE_RISK);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getRelativeRisk())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_RELATIVE_RISK);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getWhenPeriod())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_WHEN_PERIOD);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getWhenRange())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_WHEN_RANGE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getRationale())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_RATIONALE);
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
        if (null !== ($v = $this->getOutcome())) {
            $a[self::FIELD_OUTCOME] = $v;
        }
        if (null !== ($v = $this->getProbabilityDecimal())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_PROBABILITY_DECIMAL] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_PROBABILITY_DECIMAL_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getProbabilityRange())) {
            $a[self::FIELD_PROBABILITY_RANGE] = $v;
        }
        if (null !== ($v = $this->getQualitativeRisk())) {
            $a[self::FIELD_QUALITATIVE_RISK] = $v;
        }
        if (null !== ($v = $this->getRelativeRisk())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_RELATIVE_RISK] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDecimal::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_RELATIVE_RISK_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getWhenPeriod())) {
            $a[self::FIELD_WHEN_PERIOD] = $v;
        }
        if (null !== ($v = $this->getWhenRange())) {
            $a[self::FIELD_WHEN_RANGE] = $v;
        }
        if (null !== ($v = $this->getRationale())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_RATIONALE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_RATIONALE_EXT] = $ext;
            }
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