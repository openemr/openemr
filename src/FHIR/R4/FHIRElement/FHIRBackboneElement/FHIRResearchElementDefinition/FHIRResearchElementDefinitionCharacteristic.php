<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition;

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
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRTiming;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExpression;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRGroupMeasure;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * The ResearchElementDefinition resource describes a "PICO" element that knowledge
 * (evidence, assertion, recommendation) is about.
 *
 * Class FHIRResearchElementDefinitionCharacteristic
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition
 */
class FHIRResearchElementDefinitionCharacteristic extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC;
    const FIELD_DEFINITION_CODEABLE_CONCEPT = 'definitionCodeableConcept';
    const FIELD_DEFINITION_CANONICAL = 'definitionCanonical';
    const FIELD_DEFINITION_CANONICAL_EXT = '_definitionCanonical';
    const FIELD_DEFINITION_EXPRESSION = 'definitionExpression';
    const FIELD_DEFINITION_DATA_REQUIREMENT = 'definitionDataRequirement';
    const FIELD_USAGE_CONTEXT = 'usageContext';
    const FIELD_EXCLUDE = 'exclude';
    const FIELD_EXCLUDE_EXT = '_exclude';
    const FIELD_UNIT_OF_MEASURE = 'unitOfMeasure';
    const FIELD_STUDY_EFFECTIVE_DESCRIPTION = 'studyEffectiveDescription';
    const FIELD_STUDY_EFFECTIVE_DESCRIPTION_EXT = '_studyEffectiveDescription';
    const FIELD_STUDY_EFFECTIVE_DATE_TIME = 'studyEffectiveDateTime';
    const FIELD_STUDY_EFFECTIVE_DATE_TIME_EXT = '_studyEffectiveDateTime';
    const FIELD_STUDY_EFFECTIVE_PERIOD = 'studyEffectivePeriod';
    const FIELD_STUDY_EFFECTIVE_DURATION = 'studyEffectiveDuration';
    const FIELD_STUDY_EFFECTIVE_TIMING = 'studyEffectiveTiming';
    const FIELD_STUDY_EFFECTIVE_TIME_FROM_START = 'studyEffectiveTimeFromStart';
    const FIELD_STUDY_EFFECTIVE_GROUP_MEASURE = 'studyEffectiveGroupMeasure';
    const FIELD_STUDY_EFFECTIVE_GROUP_MEASURE_EXT = '_studyEffectiveGroupMeasure';
    const FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION = 'participantEffectiveDescription';
    const FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION_EXT = '_participantEffectiveDescription';
    const FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME = 'participantEffectiveDateTime';
    const FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME_EXT = '_participantEffectiveDateTime';
    const FIELD_PARTICIPANT_EFFECTIVE_PERIOD = 'participantEffectivePeriod';
    const FIELD_PARTICIPANT_EFFECTIVE_DURATION = 'participantEffectiveDuration';
    const FIELD_PARTICIPANT_EFFECTIVE_TIMING = 'participantEffectiveTiming';
    const FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START = 'participantEffectiveTimeFromStart';
    const FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE = 'participantEffectiveGroupMeasure';
    const FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE_EXT = '_participantEffectiveGroupMeasure';

    /** @var string */
    private $_xmlns = '';

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $definitionCodeableConcept = null;

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    protected $definitionCanonical = null;

    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRExpression
     */
    protected $definitionExpression = null;

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement
     */
    protected $definitionDataRequirement = null;

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Use UsageContext to define the members of the population, such as Age Ranges,
     * Genders, Settings.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    protected $usageContext = [];

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When true, members with this characteristic are excluded from the element.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    protected $exclude = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the UCUM unit for the outcome.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $unitOfMeasure = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $studyEffectiveDescription = null;

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    protected $studyEffectiveDateTime = null;

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    protected $studyEffectivePeriod = null;

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    protected $studyEffectiveDuration = null;

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    protected $studyEffectiveTiming = null;

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the study initiation.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    protected $studyEffectiveTimeFromStart = null;

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRGroupMeasure
     */
    protected $studyEffectiveGroupMeasure = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $participantEffectiveDescription = null;

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    protected $participantEffectiveDateTime = null;

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    protected $participantEffectivePeriod = null;

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    protected $participantEffectiveDuration = null;

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    protected $participantEffectiveTiming = null;

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the participant's study entry.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    protected $participantEffectiveTimeFromStart = null;

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRGroupMeasure
     */
    protected $participantEffectiveGroupMeasure = null;

    /**
     * Validation map for fields in type ResearchElementDefinition.Characteristic
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRResearchElementDefinitionCharacteristic Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRResearchElementDefinitionCharacteristic::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_DEFINITION_CODEABLE_CONCEPT])) {
            if ($data[self::FIELD_DEFINITION_CODEABLE_CONCEPT] instanceof FHIRCodeableConcept) {
                $this->setDefinitionCodeableConcept($data[self::FIELD_DEFINITION_CODEABLE_CONCEPT]);
            } else {
                $this->setDefinitionCodeableConcept(new FHIRCodeableConcept($data[self::FIELD_DEFINITION_CODEABLE_CONCEPT]));
            }
        }
        if (isset($data[self::FIELD_DEFINITION_CANONICAL]) || isset($data[self::FIELD_DEFINITION_CANONICAL_EXT])) {
            $value = isset($data[self::FIELD_DEFINITION_CANONICAL]) ? $data[self::FIELD_DEFINITION_CANONICAL] : null;
            $ext = (isset($data[self::FIELD_DEFINITION_CANONICAL_EXT]) && is_array($data[self::FIELD_DEFINITION_CANONICAL_EXT])) ? $ext = $data[self::FIELD_DEFINITION_CANONICAL_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRCanonical) {
                    $this->setDefinitionCanonical($value);
                } else if (is_array($value)) {
                    $this->setDefinitionCanonical(new FHIRCanonical(array_merge($ext, $value)));
                } else {
                    $this->setDefinitionCanonical(new FHIRCanonical([FHIRCanonical::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setDefinitionCanonical(new FHIRCanonical($ext));
            }
        }
        if (isset($data[self::FIELD_DEFINITION_EXPRESSION])) {
            if ($data[self::FIELD_DEFINITION_EXPRESSION] instanceof FHIRExpression) {
                $this->setDefinitionExpression($data[self::FIELD_DEFINITION_EXPRESSION]);
            } else {
                $this->setDefinitionExpression(new FHIRExpression($data[self::FIELD_DEFINITION_EXPRESSION]));
            }
        }
        if (isset($data[self::FIELD_DEFINITION_DATA_REQUIREMENT])) {
            if ($data[self::FIELD_DEFINITION_DATA_REQUIREMENT] instanceof FHIRDataRequirement) {
                $this->setDefinitionDataRequirement($data[self::FIELD_DEFINITION_DATA_REQUIREMENT]);
            } else {
                $this->setDefinitionDataRequirement(new FHIRDataRequirement($data[self::FIELD_DEFINITION_DATA_REQUIREMENT]));
            }
        }
        if (isset($data[self::FIELD_USAGE_CONTEXT])) {
            if (is_array($data[self::FIELD_USAGE_CONTEXT])) {
                foreach($data[self::FIELD_USAGE_CONTEXT] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRUsageContext) {
                        $this->addUsageContext($v);
                    } else {
                        $this->addUsageContext(new FHIRUsageContext($v));
                    }
                }
            } elseif ($data[self::FIELD_USAGE_CONTEXT] instanceof FHIRUsageContext) {
                $this->addUsageContext($data[self::FIELD_USAGE_CONTEXT]);
            } else {
                $this->addUsageContext(new FHIRUsageContext($data[self::FIELD_USAGE_CONTEXT]));
            }
        }
        if (isset($data[self::FIELD_EXCLUDE]) || isset($data[self::FIELD_EXCLUDE_EXT])) {
            $value = isset($data[self::FIELD_EXCLUDE]) ? $data[self::FIELD_EXCLUDE] : null;
            $ext = (isset($data[self::FIELD_EXCLUDE_EXT]) && is_array($data[self::FIELD_EXCLUDE_EXT])) ? $ext = $data[self::FIELD_EXCLUDE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRBoolean) {
                    $this->setExclude($value);
                } else if (is_array($value)) {
                    $this->setExclude(new FHIRBoolean(array_merge($ext, $value)));
                } else {
                    $this->setExclude(new FHIRBoolean([FHIRBoolean::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setExclude(new FHIRBoolean($ext));
            }
        }
        if (isset($data[self::FIELD_UNIT_OF_MEASURE])) {
            if ($data[self::FIELD_UNIT_OF_MEASURE] instanceof FHIRCodeableConcept) {
                $this->setUnitOfMeasure($data[self::FIELD_UNIT_OF_MEASURE]);
            } else {
                $this->setUnitOfMeasure(new FHIRCodeableConcept($data[self::FIELD_UNIT_OF_MEASURE]));
            }
        }
        if (isset($data[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION]) || isset($data[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION_EXT])) {
            $value = isset($data[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION]) ? $data[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION] : null;
            $ext = (isset($data[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION_EXT]) && is_array($data[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION_EXT])) ? $ext = $data[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setStudyEffectiveDescription($value);
                } else if (is_array($value)) {
                    $this->setStudyEffectiveDescription(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setStudyEffectiveDescription(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setStudyEffectiveDescription(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_STUDY_EFFECTIVE_DATE_TIME]) || isset($data[self::FIELD_STUDY_EFFECTIVE_DATE_TIME_EXT])) {
            $value = isset($data[self::FIELD_STUDY_EFFECTIVE_DATE_TIME]) ? $data[self::FIELD_STUDY_EFFECTIVE_DATE_TIME] : null;
            $ext = (isset($data[self::FIELD_STUDY_EFFECTIVE_DATE_TIME_EXT]) && is_array($data[self::FIELD_STUDY_EFFECTIVE_DATE_TIME_EXT])) ? $ext = $data[self::FIELD_STUDY_EFFECTIVE_DATE_TIME_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDateTime) {
                    $this->setStudyEffectiveDateTime($value);
                } else if (is_array($value)) {
                    $this->setStudyEffectiveDateTime(new FHIRDateTime(array_merge($ext, $value)));
                } else {
                    $this->setStudyEffectiveDateTime(new FHIRDateTime([FHIRDateTime::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setStudyEffectiveDateTime(new FHIRDateTime($ext));
            }
        }
        if (isset($data[self::FIELD_STUDY_EFFECTIVE_PERIOD])) {
            if ($data[self::FIELD_STUDY_EFFECTIVE_PERIOD] instanceof FHIRPeriod) {
                $this->setStudyEffectivePeriod($data[self::FIELD_STUDY_EFFECTIVE_PERIOD]);
            } else {
                $this->setStudyEffectivePeriod(new FHIRPeriod($data[self::FIELD_STUDY_EFFECTIVE_PERIOD]));
            }
        }
        if (isset($data[self::FIELD_STUDY_EFFECTIVE_DURATION])) {
            if ($data[self::FIELD_STUDY_EFFECTIVE_DURATION] instanceof FHIRDuration) {
                $this->setStudyEffectiveDuration($data[self::FIELD_STUDY_EFFECTIVE_DURATION]);
            } else {
                $this->setStudyEffectiveDuration(new FHIRDuration($data[self::FIELD_STUDY_EFFECTIVE_DURATION]));
            }
        }
        if (isset($data[self::FIELD_STUDY_EFFECTIVE_TIMING])) {
            if ($data[self::FIELD_STUDY_EFFECTIVE_TIMING] instanceof FHIRTiming) {
                $this->setStudyEffectiveTiming($data[self::FIELD_STUDY_EFFECTIVE_TIMING]);
            } else {
                $this->setStudyEffectiveTiming(new FHIRTiming($data[self::FIELD_STUDY_EFFECTIVE_TIMING]));
            }
        }
        if (isset($data[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START])) {
            if ($data[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START] instanceof FHIRDuration) {
                $this->setStudyEffectiveTimeFromStart($data[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START]);
            } else {
                $this->setStudyEffectiveTimeFromStart(new FHIRDuration($data[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START]));
            }
        }
        if (isset($data[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE]) || isset($data[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE_EXT])) {
            $value = isset($data[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE]) ? $data[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE] : null;
            $ext = (isset($data[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE_EXT]) && is_array($data[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE_EXT])) ? $ext = $data[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRGroupMeasure) {
                    $this->setStudyEffectiveGroupMeasure($value);
                } else if (is_array($value)) {
                    $this->setStudyEffectiveGroupMeasure(new FHIRGroupMeasure(array_merge($ext, $value)));
                } else {
                    $this->setStudyEffectiveGroupMeasure(new FHIRGroupMeasure([FHIRGroupMeasure::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setStudyEffectiveGroupMeasure(new FHIRGroupMeasure($ext));
            }
        }
        if (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION]) || isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION_EXT])) {
            $value = isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION]) ? $data[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION] : null;
            $ext = (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION_EXT]) && is_array($data[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION_EXT])) ? $ext = $data[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setParticipantEffectiveDescription($value);
                } else if (is_array($value)) {
                    $this->setParticipantEffectiveDescription(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setParticipantEffectiveDescription(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setParticipantEffectiveDescription(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]) || isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME_EXT])) {
            $value = isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]) ? $data[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME] : null;
            $ext = (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME_EXT]) && is_array($data[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME_EXT])) ? $ext = $data[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDateTime) {
                    $this->setParticipantEffectiveDateTime($value);
                } else if (is_array($value)) {
                    $this->setParticipantEffectiveDateTime(new FHIRDateTime(array_merge($ext, $value)));
                } else {
                    $this->setParticipantEffectiveDateTime(new FHIRDateTime([FHIRDateTime::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setParticipantEffectiveDateTime(new FHIRDateTime($ext));
            }
        }
        if (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD])) {
            if ($data[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD] instanceof FHIRPeriod) {
                $this->setParticipantEffectivePeriod($data[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD]);
            } else {
                $this->setParticipantEffectivePeriod(new FHIRPeriod($data[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD]));
            }
        }
        if (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION])) {
            if ($data[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION] instanceof FHIRDuration) {
                $this->setParticipantEffectiveDuration($data[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION]);
            } else {
                $this->setParticipantEffectiveDuration(new FHIRDuration($data[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION]));
            }
        }
        if (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING])) {
            if ($data[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING] instanceof FHIRTiming) {
                $this->setParticipantEffectiveTiming($data[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING]);
            } else {
                $this->setParticipantEffectiveTiming(new FHIRTiming($data[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING]));
            }
        }
        if (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START])) {
            if ($data[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START] instanceof FHIRDuration) {
                $this->setParticipantEffectiveTimeFromStart($data[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START]);
            } else {
                $this->setParticipantEffectiveTimeFromStart(new FHIRDuration($data[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START]));
            }
        }
        if (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE]) || isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE_EXT])) {
            $value = isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE]) ? $data[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE] : null;
            $ext = (isset($data[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE_EXT]) && is_array($data[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE_EXT])) ? $ext = $data[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRGroupMeasure) {
                    $this->setParticipantEffectiveGroupMeasure($value);
                } else if (is_array($value)) {
                    $this->setParticipantEffectiveGroupMeasure(new FHIRGroupMeasure(array_merge($ext, $value)));
                } else {
                    $this->setParticipantEffectiveGroupMeasure(new FHIRGroupMeasure([FHIRGroupMeasure::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setParticipantEffectiveGroupMeasure(new FHIRGroupMeasure($ext));
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
        return "<ResearchElementDefinitionCharacteristic{$xmlns}></ResearchElementDefinitionCharacteristic>";
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDefinitionCodeableConcept()
    {
        return $this->definitionCodeableConcept;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $definitionCodeableConcept
     * @return static
     */
    public function setDefinitionCodeableConcept(FHIRCodeableConcept $definitionCodeableConcept = null)
    {
        $this->_trackValueSet($this->definitionCodeableConcept, $definitionCodeableConcept);
        $this->definitionCodeableConcept = $definitionCodeableConcept;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getDefinitionCanonical()
    {
        return $this->definitionCanonical;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $definitionCanonical
     * @return static
     */
    public function setDefinitionCanonical($definitionCanonical = null)
    {
        if (null !== $definitionCanonical && !($definitionCanonical instanceof FHIRCanonical)) {
            $definitionCanonical = new FHIRCanonical($definitionCanonical);
        }
        $this->_trackValueSet($this->definitionCanonical, $definitionCanonical);
        $this->definitionCanonical = $definitionCanonical;
        return $this;
    }

    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRExpression
     */
    public function getDefinitionExpression()
    {
        return $this->definitionExpression;
    }

    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRExpression $definitionExpression
     * @return static
     */
    public function setDefinitionExpression(FHIRExpression $definitionExpression = null)
    {
        $this->_trackValueSet($this->definitionExpression, $definitionExpression);
        $this->definitionExpression = $definitionExpression;
        return $this;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement
     */
    public function getDefinitionDataRequirement()
    {
        return $this->definitionDataRequirement;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement $definitionDataRequirement
     * @return static
     */
    public function setDefinitionDataRequirement(FHIRDataRequirement $definitionDataRequirement = null)
    {
        $this->_trackValueSet($this->definitionDataRequirement, $definitionDataRequirement);
        $this->definitionDataRequirement = $definitionDataRequirement;
        return $this;
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Use UsageContext to define the members of the population, such as Age Ranges,
     * Genders, Settings.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    public function getUsageContext()
    {
        return $this->usageContext;
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Use UsageContext to define the members of the population, such as Age Ranges,
     * Genders, Settings.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext $usageContext
     * @return static
     */
    public function addUsageContext(FHIRUsageContext $usageContext = null)
    {
        $this->_trackValueAdded();
        $this->usageContext[] = $usageContext;
        return $this;
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Use UsageContext to define the members of the population, such as Age Ranges,
     * Genders, Settings.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[] $usageContext
     * @return static
     */
    public function setUsageContext(array $usageContext = [])
    {
        if ([] !== $this->usageContext) {
            $this->_trackValuesRemoved(count($this->usageContext));
            $this->usageContext = [];
        }
        if ([] === $usageContext) {
            return $this;
        }
        foreach($usageContext as $v) {
            if ($v instanceof FHIRUsageContext) {
                $this->addUsageContext($v);
            } else {
                $this->addUsageContext(new FHIRUsageContext($v));
            }
        }
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When true, members with this characteristic are excluded from the element.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When true, members with this characteristic are excluded from the element.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $exclude
     * @return static
     */
    public function setExclude($exclude = null)
    {
        if (null !== $exclude && !($exclude instanceof FHIRBoolean)) {
            $exclude = new FHIRBoolean($exclude);
        }
        $this->_trackValueSet($this->exclude, $exclude);
        $this->exclude = $exclude;
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getUnitOfMeasure()
    {
        return $this->unitOfMeasure;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the UCUM unit for the outcome.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $unitOfMeasure
     * @return static
     */
    public function setUnitOfMeasure(FHIRCodeableConcept $unitOfMeasure = null)
    {
        $this->_trackValueSet($this->unitOfMeasure, $unitOfMeasure);
        $this->unitOfMeasure = $unitOfMeasure;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getStudyEffectiveDescription()
    {
        return $this->studyEffectiveDescription;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $studyEffectiveDescription
     * @return static
     */
    public function setStudyEffectiveDescription($studyEffectiveDescription = null)
    {
        if (null !== $studyEffectiveDescription && !($studyEffectiveDescription instanceof FHIRString)) {
            $studyEffectiveDescription = new FHIRString($studyEffectiveDescription);
        }
        $this->_trackValueSet($this->studyEffectiveDescription, $studyEffectiveDescription);
        $this->studyEffectiveDescription = $studyEffectiveDescription;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getStudyEffectiveDateTime()
    {
        return $this->studyEffectiveDateTime;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $studyEffectiveDateTime
     * @return static
     */
    public function setStudyEffectiveDateTime($studyEffectiveDateTime = null)
    {
        if (null !== $studyEffectiveDateTime && !($studyEffectiveDateTime instanceof FHIRDateTime)) {
            $studyEffectiveDateTime = new FHIRDateTime($studyEffectiveDateTime);
        }
        $this->_trackValueSet($this->studyEffectiveDateTime, $studyEffectiveDateTime);
        $this->studyEffectiveDateTime = $studyEffectiveDateTime;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getStudyEffectivePeriod()
    {
        return $this->studyEffectivePeriod;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $studyEffectivePeriod
     * @return static
     */
    public function setStudyEffectivePeriod(FHIRPeriod $studyEffectivePeriod = null)
    {
        $this->_trackValueSet($this->studyEffectivePeriod, $studyEffectivePeriod);
        $this->studyEffectivePeriod = $studyEffectivePeriod;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getStudyEffectiveDuration()
    {
        return $this->studyEffectiveDuration;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $studyEffectiveDuration
     * @return static
     */
    public function setStudyEffectiveDuration(FHIRDuration $studyEffectiveDuration = null)
    {
        $this->_trackValueSet($this->studyEffectiveDuration, $studyEffectiveDuration);
        $this->studyEffectiveDuration = $studyEffectiveDuration;
        return $this;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getStudyEffectiveTiming()
    {
        return $this->studyEffectiveTiming;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRTiming $studyEffectiveTiming
     * @return static
     */
    public function setStudyEffectiveTiming(FHIRTiming $studyEffectiveTiming = null)
    {
        $this->_trackValueSet($this->studyEffectiveTiming, $studyEffectiveTiming);
        $this->studyEffectiveTiming = $studyEffectiveTiming;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the study initiation.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getStudyEffectiveTimeFromStart()
    {
        return $this->studyEffectiveTimeFromStart;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the study initiation.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $studyEffectiveTimeFromStart
     * @return static
     */
    public function setStudyEffectiveTimeFromStart(FHIRDuration $studyEffectiveTimeFromStart = null)
    {
        $this->_trackValueSet($this->studyEffectiveTimeFromStart, $studyEffectiveTimeFromStart);
        $this->studyEffectiveTimeFromStart = $studyEffectiveTimeFromStart;
        return $this;
    }

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRGroupMeasure
     */
    public function getStudyEffectiveGroupMeasure()
    {
        return $this->studyEffectiveGroupMeasure;
    }

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRGroupMeasure $studyEffectiveGroupMeasure
     * @return static
     */
    public function setStudyEffectiveGroupMeasure(FHIRGroupMeasure $studyEffectiveGroupMeasure = null)
    {
        $this->_trackValueSet($this->studyEffectiveGroupMeasure, $studyEffectiveGroupMeasure);
        $this->studyEffectiveGroupMeasure = $studyEffectiveGroupMeasure;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getParticipantEffectiveDescription()
    {
        return $this->participantEffectiveDescription;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $participantEffectiveDescription
     * @return static
     */
    public function setParticipantEffectiveDescription($participantEffectiveDescription = null)
    {
        if (null !== $participantEffectiveDescription && !($participantEffectiveDescription instanceof FHIRString)) {
            $participantEffectiveDescription = new FHIRString($participantEffectiveDescription);
        }
        $this->_trackValueSet($this->participantEffectiveDescription, $participantEffectiveDescription);
        $this->participantEffectiveDescription = $participantEffectiveDescription;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getParticipantEffectiveDateTime()
    {
        return $this->participantEffectiveDateTime;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $participantEffectiveDateTime
     * @return static
     */
    public function setParticipantEffectiveDateTime($participantEffectiveDateTime = null)
    {
        if (null !== $participantEffectiveDateTime && !($participantEffectiveDateTime instanceof FHIRDateTime)) {
            $participantEffectiveDateTime = new FHIRDateTime($participantEffectiveDateTime);
        }
        $this->_trackValueSet($this->participantEffectiveDateTime, $participantEffectiveDateTime);
        $this->participantEffectiveDateTime = $participantEffectiveDateTime;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getParticipantEffectivePeriod()
    {
        return $this->participantEffectivePeriod;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $participantEffectivePeriod
     * @return static
     */
    public function setParticipantEffectivePeriod(FHIRPeriod $participantEffectivePeriod = null)
    {
        $this->_trackValueSet($this->participantEffectivePeriod, $participantEffectivePeriod);
        $this->participantEffectivePeriod = $participantEffectivePeriod;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getParticipantEffectiveDuration()
    {
        return $this->participantEffectiveDuration;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $participantEffectiveDuration
     * @return static
     */
    public function setParticipantEffectiveDuration(FHIRDuration $participantEffectiveDuration = null)
    {
        $this->_trackValueSet($this->participantEffectiveDuration, $participantEffectiveDuration);
        $this->participantEffectiveDuration = $participantEffectiveDuration;
        return $this;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getParticipantEffectiveTiming()
    {
        return $this->participantEffectiveTiming;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRTiming $participantEffectiveTiming
     * @return static
     */
    public function setParticipantEffectiveTiming(FHIRTiming $participantEffectiveTiming = null)
    {
        $this->_trackValueSet($this->participantEffectiveTiming, $participantEffectiveTiming);
        $this->participantEffectiveTiming = $participantEffectiveTiming;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the participant's study entry.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getParticipantEffectiveTimeFromStart()
    {
        return $this->participantEffectiveTimeFromStart;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the participant's study entry.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $participantEffectiveTimeFromStart
     * @return static
     */
    public function setParticipantEffectiveTimeFromStart(FHIRDuration $participantEffectiveTimeFromStart = null)
    {
        $this->_trackValueSet($this->participantEffectiveTimeFromStart, $participantEffectiveTimeFromStart);
        $this->participantEffectiveTimeFromStart = $participantEffectiveTimeFromStart;
        return $this;
    }

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRGroupMeasure
     */
    public function getParticipantEffectiveGroupMeasure()
    {
        return $this->participantEffectiveGroupMeasure;
    }

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRGroupMeasure $participantEffectiveGroupMeasure
     * @return static
     */
    public function setParticipantEffectiveGroupMeasure(FHIRGroupMeasure $participantEffectiveGroupMeasure = null)
    {
        $this->_trackValueSet($this->participantEffectiveGroupMeasure, $participantEffectiveGroupMeasure);
        $this->participantEffectiveGroupMeasure = $participantEffectiveGroupMeasure;
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
        if (null !== ($v = $this->getDefinitionCodeableConcept())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_DEFINITION_CODEABLE_CONCEPT] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getDefinitionCanonical())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_DEFINITION_CANONICAL] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getDefinitionExpression())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_DEFINITION_EXPRESSION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getDefinitionDataRequirement())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_DEFINITION_DATA_REQUIREMENT] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getUsageContext())) {
            foreach($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_USAGE_CONTEXT, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getExclude())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_EXCLUDE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getUnitOfMeasure())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_UNIT_OF_MEASURE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStudyEffectiveDescription())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStudyEffectiveDateTime())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STUDY_EFFECTIVE_DATE_TIME] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStudyEffectivePeriod())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STUDY_EFFECTIVE_PERIOD] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStudyEffectiveDuration())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STUDY_EFFECTIVE_DURATION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStudyEffectiveTiming())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STUDY_EFFECTIVE_TIMING] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStudyEffectiveTimeFromStart())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getStudyEffectiveGroupMeasure())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getParticipantEffectiveDescription())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getParticipantEffectiveDateTime())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getParticipantEffectivePeriod())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getParticipantEffectiveDuration())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getParticipantEffectiveTiming())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getParticipantEffectiveTimeFromStart())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getParticipantEffectiveGroupMeasure())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_DEFINITION_CODEABLE_CONCEPT])) {
            $v = $this->getDefinitionCodeableConcept();
            foreach($validationRules[self::FIELD_DEFINITION_CODEABLE_CONCEPT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_DEFINITION_CODEABLE_CONCEPT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DEFINITION_CODEABLE_CONCEPT])) {
                        $errs[self::FIELD_DEFINITION_CODEABLE_CONCEPT] = [];
                    }
                    $errs[self::FIELD_DEFINITION_CODEABLE_CONCEPT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DEFINITION_CANONICAL])) {
            $v = $this->getDefinitionCanonical();
            foreach($validationRules[self::FIELD_DEFINITION_CANONICAL] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_DEFINITION_CANONICAL, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DEFINITION_CANONICAL])) {
                        $errs[self::FIELD_DEFINITION_CANONICAL] = [];
                    }
                    $errs[self::FIELD_DEFINITION_CANONICAL][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DEFINITION_EXPRESSION])) {
            $v = $this->getDefinitionExpression();
            foreach($validationRules[self::FIELD_DEFINITION_EXPRESSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_DEFINITION_EXPRESSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DEFINITION_EXPRESSION])) {
                        $errs[self::FIELD_DEFINITION_EXPRESSION] = [];
                    }
                    $errs[self::FIELD_DEFINITION_EXPRESSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DEFINITION_DATA_REQUIREMENT])) {
            $v = $this->getDefinitionDataRequirement();
            foreach($validationRules[self::FIELD_DEFINITION_DATA_REQUIREMENT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_DEFINITION_DATA_REQUIREMENT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DEFINITION_DATA_REQUIREMENT])) {
                        $errs[self::FIELD_DEFINITION_DATA_REQUIREMENT] = [];
                    }
                    $errs[self::FIELD_DEFINITION_DATA_REQUIREMENT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_USAGE_CONTEXT])) {
            $v = $this->getUsageContext();
            foreach($validationRules[self::FIELD_USAGE_CONTEXT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_USAGE_CONTEXT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_USAGE_CONTEXT])) {
                        $errs[self::FIELD_USAGE_CONTEXT] = [];
                    }
                    $errs[self::FIELD_USAGE_CONTEXT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXCLUDE])) {
            $v = $this->getExclude();
            foreach($validationRules[self::FIELD_EXCLUDE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_EXCLUDE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXCLUDE])) {
                        $errs[self::FIELD_EXCLUDE] = [];
                    }
                    $errs[self::FIELD_EXCLUDE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_UNIT_OF_MEASURE])) {
            $v = $this->getUnitOfMeasure();
            foreach($validationRules[self::FIELD_UNIT_OF_MEASURE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_UNIT_OF_MEASURE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_UNIT_OF_MEASURE])) {
                        $errs[self::FIELD_UNIT_OF_MEASURE] = [];
                    }
                    $errs[self::FIELD_UNIT_OF_MEASURE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION])) {
            $v = $this->getStudyEffectiveDescription();
            foreach($validationRules[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_STUDY_EFFECTIVE_DESCRIPTION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION])) {
                        $errs[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION] = [];
                    }
                    $errs[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STUDY_EFFECTIVE_DATE_TIME])) {
            $v = $this->getStudyEffectiveDateTime();
            foreach($validationRules[self::FIELD_STUDY_EFFECTIVE_DATE_TIME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_STUDY_EFFECTIVE_DATE_TIME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STUDY_EFFECTIVE_DATE_TIME])) {
                        $errs[self::FIELD_STUDY_EFFECTIVE_DATE_TIME] = [];
                    }
                    $errs[self::FIELD_STUDY_EFFECTIVE_DATE_TIME][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STUDY_EFFECTIVE_PERIOD])) {
            $v = $this->getStudyEffectivePeriod();
            foreach($validationRules[self::FIELD_STUDY_EFFECTIVE_PERIOD] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_STUDY_EFFECTIVE_PERIOD, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STUDY_EFFECTIVE_PERIOD])) {
                        $errs[self::FIELD_STUDY_EFFECTIVE_PERIOD] = [];
                    }
                    $errs[self::FIELD_STUDY_EFFECTIVE_PERIOD][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STUDY_EFFECTIVE_DURATION])) {
            $v = $this->getStudyEffectiveDuration();
            foreach($validationRules[self::FIELD_STUDY_EFFECTIVE_DURATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_STUDY_EFFECTIVE_DURATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STUDY_EFFECTIVE_DURATION])) {
                        $errs[self::FIELD_STUDY_EFFECTIVE_DURATION] = [];
                    }
                    $errs[self::FIELD_STUDY_EFFECTIVE_DURATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STUDY_EFFECTIVE_TIMING])) {
            $v = $this->getStudyEffectiveTiming();
            foreach($validationRules[self::FIELD_STUDY_EFFECTIVE_TIMING] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_STUDY_EFFECTIVE_TIMING, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STUDY_EFFECTIVE_TIMING])) {
                        $errs[self::FIELD_STUDY_EFFECTIVE_TIMING] = [];
                    }
                    $errs[self::FIELD_STUDY_EFFECTIVE_TIMING][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START])) {
            $v = $this->getStudyEffectiveTimeFromStart();
            foreach($validationRules[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START])) {
                        $errs[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START] = [];
                    }
                    $errs[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE])) {
            $v = $this->getStudyEffectiveGroupMeasure();
            foreach($validationRules[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE])) {
                        $errs[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE] = [];
                    }
                    $errs[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION])) {
            $v = $this->getParticipantEffectiveDescription();
            foreach($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION])) {
                        $errs[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION] = [];
                    }
                    $errs[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME])) {
            $v = $this->getParticipantEffectiveDateTime();
            foreach($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME])) {
                        $errs[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME] = [];
                    }
                    $errs[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD])) {
            $v = $this->getParticipantEffectivePeriod();
            foreach($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD])) {
                        $errs[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD] = [];
                    }
                    $errs[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION])) {
            $v = $this->getParticipantEffectiveDuration();
            foreach($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_PARTICIPANT_EFFECTIVE_DURATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION])) {
                        $errs[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION] = [];
                    }
                    $errs[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING])) {
            $v = $this->getParticipantEffectiveTiming();
            foreach($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_PARTICIPANT_EFFECTIVE_TIMING, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING])) {
                        $errs[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING] = [];
                    }
                    $errs[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START])) {
            $v = $this->getParticipantEffectiveTimeFromStart();
            foreach($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START])) {
                        $errs[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START] = [];
                    }
                    $errs[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE])) {
            $v = $this->getParticipantEffectiveGroupMeasure();
            foreach($validationRules[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC, self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE])) {
                        $errs[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE] = [];
                    }
                    $errs[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE][$rule] = $err;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition\FHIRResearchElementDefinitionCharacteristic $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition\FHIRResearchElementDefinitionCharacteristic
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
                throw new \DomainException(sprintf('FHIRResearchElementDefinitionCharacteristic::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRResearchElementDefinitionCharacteristic::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRResearchElementDefinitionCharacteristic(null);
        } elseif (!is_object($type) || !($type instanceof FHIRResearchElementDefinitionCharacteristic)) {
            throw new \RuntimeException(sprintf(
                'FHIRResearchElementDefinitionCharacteristic::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition\FHIRResearchElementDefinitionCharacteristic or null, %s seen.',
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
            if (self::FIELD_DEFINITION_CODEABLE_CONCEPT === $n->nodeName) {
                $type->setDefinitionCodeableConcept(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_DEFINITION_CANONICAL === $n->nodeName) {
                $type->setDefinitionCanonical(FHIRCanonical::xmlUnserialize($n));
            } elseif (self::FIELD_DEFINITION_EXPRESSION === $n->nodeName) {
                $type->setDefinitionExpression(FHIRExpression::xmlUnserialize($n));
            } elseif (self::FIELD_DEFINITION_DATA_REQUIREMENT === $n->nodeName) {
                $type->setDefinitionDataRequirement(FHIRDataRequirement::xmlUnserialize($n));
            } elseif (self::FIELD_USAGE_CONTEXT === $n->nodeName) {
                $type->addUsageContext(FHIRUsageContext::xmlUnserialize($n));
            } elseif (self::FIELD_EXCLUDE === $n->nodeName) {
                $type->setExclude(FHIRBoolean::xmlUnserialize($n));
            } elseif (self::FIELD_UNIT_OF_MEASURE === $n->nodeName) {
                $type->setUnitOfMeasure(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_STUDY_EFFECTIVE_DESCRIPTION === $n->nodeName) {
                $type->setStudyEffectiveDescription(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_STUDY_EFFECTIVE_DATE_TIME === $n->nodeName) {
                $type->setStudyEffectiveDateTime(FHIRDateTime::xmlUnserialize($n));
            } elseif (self::FIELD_STUDY_EFFECTIVE_PERIOD === $n->nodeName) {
                $type->setStudyEffectivePeriod(FHIRPeriod::xmlUnserialize($n));
            } elseif (self::FIELD_STUDY_EFFECTIVE_DURATION === $n->nodeName) {
                $type->setStudyEffectiveDuration(FHIRDuration::xmlUnserialize($n));
            } elseif (self::FIELD_STUDY_EFFECTIVE_TIMING === $n->nodeName) {
                $type->setStudyEffectiveTiming(FHIRTiming::xmlUnserialize($n));
            } elseif (self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START === $n->nodeName) {
                $type->setStudyEffectiveTimeFromStart(FHIRDuration::xmlUnserialize($n));
            } elseif (self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE === $n->nodeName) {
                $type->setStudyEffectiveGroupMeasure(FHIRGroupMeasure::xmlUnserialize($n));
            } elseif (self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION === $n->nodeName) {
                $type->setParticipantEffectiveDescription(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME === $n->nodeName) {
                $type->setParticipantEffectiveDateTime(FHIRDateTime::xmlUnserialize($n));
            } elseif (self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD === $n->nodeName) {
                $type->setParticipantEffectivePeriod(FHIRPeriod::xmlUnserialize($n));
            } elseif (self::FIELD_PARTICIPANT_EFFECTIVE_DURATION === $n->nodeName) {
                $type->setParticipantEffectiveDuration(FHIRDuration::xmlUnserialize($n));
            } elseif (self::FIELD_PARTICIPANT_EFFECTIVE_TIMING === $n->nodeName) {
                $type->setParticipantEffectiveTiming(FHIRTiming::xmlUnserialize($n));
            } elseif (self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START === $n->nodeName) {
                $type->setParticipantEffectiveTimeFromStart(FHIRDuration::xmlUnserialize($n));
            } elseif (self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE === $n->nodeName) {
                $type->setParticipantEffectiveGroupMeasure(FHIRGroupMeasure::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_DEFINITION_CANONICAL);
        if (null !== $n) {
            $pt = $type->getDefinitionCanonical();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setDefinitionCanonical($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_EXCLUDE);
        if (null !== $n) {
            $pt = $type->getExclude();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setExclude($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_STUDY_EFFECTIVE_DESCRIPTION);
        if (null !== $n) {
            $pt = $type->getStudyEffectiveDescription();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setStudyEffectiveDescription($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_STUDY_EFFECTIVE_DATE_TIME);
        if (null !== $n) {
            $pt = $type->getStudyEffectiveDateTime();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setStudyEffectiveDateTime($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION);
        if (null !== $n) {
            $pt = $type->getParticipantEffectiveDescription();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setParticipantEffectiveDescription($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME);
        if (null !== $n) {
            $pt = $type->getParticipantEffectiveDateTime();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setParticipantEffectiveDateTime($n->nodeValue);
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
        if (null !== ($v = $this->getDefinitionCodeableConcept())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_DEFINITION_CODEABLE_CONCEPT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getDefinitionCanonical())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_DEFINITION_CANONICAL);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getDefinitionExpression())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_DEFINITION_EXPRESSION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getDefinitionDataRequirement())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_DEFINITION_DATA_REQUIREMENT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getUsageContext())) {
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_USAGE_CONTEXT);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getExclude())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_EXCLUDE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getUnitOfMeasure())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_UNIT_OF_MEASURE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStudyEffectiveDescription())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STUDY_EFFECTIVE_DESCRIPTION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStudyEffectiveDateTime())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STUDY_EFFECTIVE_DATE_TIME);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStudyEffectivePeriod())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STUDY_EFFECTIVE_PERIOD);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStudyEffectiveDuration())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STUDY_EFFECTIVE_DURATION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStudyEffectiveTiming())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STUDY_EFFECTIVE_TIMING);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStudyEffectiveTimeFromStart())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getStudyEffectiveGroupMeasure())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getParticipantEffectiveDescription())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getParticipantEffectiveDateTime())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getParticipantEffectivePeriod())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getParticipantEffectiveDuration())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PARTICIPANT_EFFECTIVE_DURATION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getParticipantEffectiveTiming())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PARTICIPANT_EFFECTIVE_TIMING);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getParticipantEffectiveTimeFromStart())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getParticipantEffectiveGroupMeasure())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE);
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
        if (null !== ($v = $this->getDefinitionCodeableConcept())) {
            $a[self::FIELD_DEFINITION_CODEABLE_CONCEPT] = $v;
        }
        if (null !== ($v = $this->getDefinitionCanonical())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_DEFINITION_CANONICAL] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRCanonical::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_DEFINITION_CANONICAL_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getDefinitionExpression())) {
            $a[self::FIELD_DEFINITION_EXPRESSION] = $v;
        }
        if (null !== ($v = $this->getDefinitionDataRequirement())) {
            $a[self::FIELD_DEFINITION_DATA_REQUIREMENT] = $v;
        }
        if ([] !== ($vs = $this->getUsageContext())) {
            $a[self::FIELD_USAGE_CONTEXT] = [];
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_USAGE_CONTEXT][] = $v;
            }
        }
        if (null !== ($v = $this->getExclude())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_EXCLUDE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRBoolean::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_EXCLUDE_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getUnitOfMeasure())) {
            $a[self::FIELD_UNIT_OF_MEASURE] = $v;
        }
        if (null !== ($v = $this->getStudyEffectiveDescription())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getStudyEffectiveDateTime())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_STUDY_EFFECTIVE_DATE_TIME] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDateTime::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_STUDY_EFFECTIVE_DATE_TIME_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getStudyEffectivePeriod())) {
            $a[self::FIELD_STUDY_EFFECTIVE_PERIOD] = $v;
        }
        if (null !== ($v = $this->getStudyEffectiveDuration())) {
            $a[self::FIELD_STUDY_EFFECTIVE_DURATION] = $v;
        }
        if (null !== ($v = $this->getStudyEffectiveTiming())) {
            $a[self::FIELD_STUDY_EFFECTIVE_TIMING] = $v;
        }
        if (null !== ($v = $this->getStudyEffectiveTimeFromStart())) {
            $a[self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START] = $v;
        }
        if (null !== ($v = $this->getStudyEffectiveGroupMeasure())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRGroupMeasure::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getParticipantEffectiveDescription())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getParticipantEffectiveDateTime())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRDateTime::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getParticipantEffectivePeriod())) {
            $a[self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD] = $v;
        }
        if (null !== ($v = $this->getParticipantEffectiveDuration())) {
            $a[self::FIELD_PARTICIPANT_EFFECTIVE_DURATION] = $v;
        }
        if (null !== ($v = $this->getParticipantEffectiveTiming())) {
            $a[self::FIELD_PARTICIPANT_EFFECTIVE_TIMING] = $v;
        }
        if (null !== ($v = $this->getParticipantEffectiveTimeFromStart())) {
            $a[self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START] = $v;
        }
        if (null !== ($v = $this->getParticipantEffectiveGroupMeasure())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRGroupMeasure::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE_EXT] = $ext;
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