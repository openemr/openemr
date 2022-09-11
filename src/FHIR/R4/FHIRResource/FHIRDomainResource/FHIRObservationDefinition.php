<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQualifiedInterval;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRObservationDataType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeMap;

/**
 * Set of definitional characteristics for a kind of observation or measurement
 * produced or consumed by an orderable health care service.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 *
 * Class FHIRObservationDefinition
 * @package \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource
 */
class FHIRObservationDefinition extends FHIRDomainResource implements PHPFHIRContainedTypeInterface
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION;
    const FIELD_CATEGORY = 'category';
    const FIELD_CODE = 'code';
    const FIELD_IDENTIFIER = 'identifier';
    const FIELD_PERMITTED_DATA_TYPE = 'permittedDataType';
    const FIELD_PERMITTED_DATA_TYPE_EXT = '_permittedDataType';
    const FIELD_MULTIPLE_RESULTS_ALLOWED = 'multipleResultsAllowed';
    const FIELD_MULTIPLE_RESULTS_ALLOWED_EXT = '_multipleResultsAllowed';
    const FIELD_METHOD = 'method';
    const FIELD_PREFERRED_REPORT_NAME = 'preferredReportName';
    const FIELD_PREFERRED_REPORT_NAME_EXT = '_preferredReportName';
    const FIELD_QUANTITATIVE_DETAILS = 'quantitativeDetails';
    const FIELD_QUALIFIED_INTERVAL = 'qualifiedInterval';
    const FIELD_VALID_CODED_VALUE_SET = 'validCodedValueSet';
    const FIELD_NORMAL_CODED_VALUE_SET = 'normalCodedValueSet';
    const FIELD_ABNORMAL_CODED_VALUE_SET = 'abnormalCodedValueSet';
    const FIELD_CRITICAL_CODED_VALUE_SET = 'criticalCodedValueSet';

    /** @var string */
    private $_xmlns = '';

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that classifies the general type of observation.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    protected $category = [];

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes what will be observed. Sometimes this is called the observation
     * "name".
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $code = null;

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A unique identifier assigned to this ObservationDefinition artifact.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    protected $identifier = [];

    /**
     * Permitted data type for observation value.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The data types allowed for the value element of the instance observations
     * conforming to this ObservationDefinition.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRObservationDataType[]
     */
    protected $permittedDataType = [];

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Multiple results allowed for observations conforming to this
     * ObservationDefinition.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    protected $multipleResultsAllowed = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The method or technique used to perform the observation.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $method = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The preferred name to be used when reporting the results of observations
     * conforming to this ObservationDefinition.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $preferredReportName = null;

    /**
     * Set of definitional characteristics for a kind of observation or measurement
     * produced or consumed by an orderable health care service.
     *
     * Characteristics for quantitative results of this observation.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails
     */
    protected $quantitativeDetails = null;

    /**
     * Set of definitional characteristics for a kind of observation or measurement
     * produced or consumed by an orderable health care service.
     *
     * Multiple ranges of results qualified by different contexts for ordinal or
     * continuous observations conforming to this ObservationDefinition.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQualifiedInterval[]
     */
    protected $qualifiedInterval = [];

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of valid coded results for the observations conforming to this
     * ObservationDefinition.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $validCodedValueSet = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of normal coded results for the observations conforming to this
     * ObservationDefinition.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $normalCodedValueSet = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of abnormal coded results for the observation conforming to this
     * ObservationDefinition.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $abnormalCodedValueSet = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of critical coded results for the observation conforming to this
     * ObservationDefinition.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $criticalCodedValueSet = null;

    /**
     * Validation map for fields in type ObservationDefinition
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRObservationDefinition Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRObservationDefinition::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_CATEGORY])) {
            if (is_array($data[self::FIELD_CATEGORY])) {
                foreach ($data[self::FIELD_CATEGORY] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRCodeableConcept) {
                        $this->addCategory($v);
                    } else {
                        $this->addCategory(new FHIRCodeableConcept($v));
                    }
                }
            } elseif ($data[self::FIELD_CATEGORY] instanceof FHIRCodeableConcept) {
                $this->addCategory($data[self::FIELD_CATEGORY]);
            } else {
                $this->addCategory(new FHIRCodeableConcept($data[self::FIELD_CATEGORY]));
            }
        }
        if (isset($data[self::FIELD_CODE])) {
            if ($data[self::FIELD_CODE] instanceof FHIRCodeableConcept) {
                $this->setCode($data[self::FIELD_CODE]);
            } else {
                $this->setCode(new FHIRCodeableConcept($data[self::FIELD_CODE]));
            }
        }
        if (isset($data[self::FIELD_IDENTIFIER])) {
            if (is_array($data[self::FIELD_IDENTIFIER])) {
                foreach ($data[self::FIELD_IDENTIFIER] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRIdentifier) {
                        $this->addIdentifier($v);
                    } else {
                        $this->addIdentifier(new FHIRIdentifier($v));
                    }
                }
            } elseif ($data[self::FIELD_IDENTIFIER] instanceof FHIRIdentifier) {
                $this->addIdentifier($data[self::FIELD_IDENTIFIER]);
            } else {
                $this->addIdentifier(new FHIRIdentifier($data[self::FIELD_IDENTIFIER]));
            }
        }
        if (isset($data[self::FIELD_PERMITTED_DATA_TYPE]) || isset($data[self::FIELD_PERMITTED_DATA_TYPE_EXT])) {
            $value = isset($data[self::FIELD_PERMITTED_DATA_TYPE]) ? $data[self::FIELD_PERMITTED_DATA_TYPE] : null;
            $ext = (isset($data[self::FIELD_PERMITTED_DATA_TYPE_EXT]) && is_array($data[self::FIELD_PERMITTED_DATA_TYPE_EXT])) ? $ext = $data[self::FIELD_PERMITTED_DATA_TYPE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRObservationDataType) {
                    $this->addPermittedDataType($value);
                } else if (is_array($value)) {
                    foreach ($value as $i => $v) {
                        if ($v instanceof FHIRObservationDataType) {
                            $this->addPermittedDataType($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addPermittedDataType(new FHIRObservationDataType(array_merge($v, $iext)));
                            } else {
                                $this->addPermittedDataType(new FHIRObservationDataType([FHIRObservationDataType::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addPermittedDataType(new FHIRObservationDataType(array_merge($ext, $value)));
                } else {
                    $this->addPermittedDataType(new FHIRObservationDataType([FHIRObservationDataType::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach ($ext as $iext) {
                    $this->addPermittedDataType(new FHIRObservationDataType($iext));
                }
            }
        }
        if (isset($data[self::FIELD_MULTIPLE_RESULTS_ALLOWED]) || isset($data[self::FIELD_MULTIPLE_RESULTS_ALLOWED_EXT])) {
            $value = isset($data[self::FIELD_MULTIPLE_RESULTS_ALLOWED]) ? $data[self::FIELD_MULTIPLE_RESULTS_ALLOWED] : null;
            $ext = (isset($data[self::FIELD_MULTIPLE_RESULTS_ALLOWED_EXT]) && is_array($data[self::FIELD_MULTIPLE_RESULTS_ALLOWED_EXT])) ? $ext = $data[self::FIELD_MULTIPLE_RESULTS_ALLOWED_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRBoolean) {
                    $this->setMultipleResultsAllowed($value);
                } else if (is_array($value)) {
                    $this->setMultipleResultsAllowed(new FHIRBoolean(array_merge($ext, $value)));
                } else {
                    $this->setMultipleResultsAllowed(new FHIRBoolean([FHIRBoolean::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setMultipleResultsAllowed(new FHIRBoolean($ext));
            }
        }
        if (isset($data[self::FIELD_METHOD])) {
            if ($data[self::FIELD_METHOD] instanceof FHIRCodeableConcept) {
                $this->setMethod($data[self::FIELD_METHOD]);
            } else {
                $this->setMethod(new FHIRCodeableConcept($data[self::FIELD_METHOD]));
            }
        }
        if (isset($data[self::FIELD_PREFERRED_REPORT_NAME]) || isset($data[self::FIELD_PREFERRED_REPORT_NAME_EXT])) {
            $value = isset($data[self::FIELD_PREFERRED_REPORT_NAME]) ? $data[self::FIELD_PREFERRED_REPORT_NAME] : null;
            $ext = (isset($data[self::FIELD_PREFERRED_REPORT_NAME_EXT]) && is_array($data[self::FIELD_PREFERRED_REPORT_NAME_EXT])) ? $ext = $data[self::FIELD_PREFERRED_REPORT_NAME_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setPreferredReportName($value);
                } else if (is_array($value)) {
                    $this->setPreferredReportName(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setPreferredReportName(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setPreferredReportName(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_QUANTITATIVE_DETAILS])) {
            if ($data[self::FIELD_QUANTITATIVE_DETAILS] instanceof FHIRObservationDefinitionQuantitativeDetails) {
                $this->setQuantitativeDetails($data[self::FIELD_QUANTITATIVE_DETAILS]);
            } else {
                $this->setQuantitativeDetails(new FHIRObservationDefinitionQuantitativeDetails($data[self::FIELD_QUANTITATIVE_DETAILS]));
            }
        }
        if (isset($data[self::FIELD_QUALIFIED_INTERVAL])) {
            if (is_array($data[self::FIELD_QUALIFIED_INTERVAL])) {
                foreach ($data[self::FIELD_QUALIFIED_INTERVAL] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRObservationDefinitionQualifiedInterval) {
                        $this->addQualifiedInterval($v);
                    } else {
                        $this->addQualifiedInterval(new FHIRObservationDefinitionQualifiedInterval($v));
                    }
                }
            } elseif ($data[self::FIELD_QUALIFIED_INTERVAL] instanceof FHIRObservationDefinitionQualifiedInterval) {
                $this->addQualifiedInterval($data[self::FIELD_QUALIFIED_INTERVAL]);
            } else {
                $this->addQualifiedInterval(new FHIRObservationDefinitionQualifiedInterval($data[self::FIELD_QUALIFIED_INTERVAL]));
            }
        }
        if (isset($data[self::FIELD_VALID_CODED_VALUE_SET])) {
            if ($data[self::FIELD_VALID_CODED_VALUE_SET] instanceof FHIRReference) {
                $this->setValidCodedValueSet($data[self::FIELD_VALID_CODED_VALUE_SET]);
            } else {
                $this->setValidCodedValueSet(new FHIRReference($data[self::FIELD_VALID_CODED_VALUE_SET]));
            }
        }
        if (isset($data[self::FIELD_NORMAL_CODED_VALUE_SET])) {
            if ($data[self::FIELD_NORMAL_CODED_VALUE_SET] instanceof FHIRReference) {
                $this->setNormalCodedValueSet($data[self::FIELD_NORMAL_CODED_VALUE_SET]);
            } else {
                $this->setNormalCodedValueSet(new FHIRReference($data[self::FIELD_NORMAL_CODED_VALUE_SET]));
            }
        }
        if (isset($data[self::FIELD_ABNORMAL_CODED_VALUE_SET])) {
            if ($data[self::FIELD_ABNORMAL_CODED_VALUE_SET] instanceof FHIRReference) {
                $this->setAbnormalCodedValueSet($data[self::FIELD_ABNORMAL_CODED_VALUE_SET]);
            } else {
                $this->setAbnormalCodedValueSet(new FHIRReference($data[self::FIELD_ABNORMAL_CODED_VALUE_SET]));
            }
        }
        if (isset($data[self::FIELD_CRITICAL_CODED_VALUE_SET])) {
            if ($data[self::FIELD_CRITICAL_CODED_VALUE_SET] instanceof FHIRReference) {
                $this->setCriticalCodedValueSet($data[self::FIELD_CRITICAL_CODED_VALUE_SET]);
            } else {
                $this->setCriticalCodedValueSet(new FHIRReference($data[self::FIELD_CRITICAL_CODED_VALUE_SET]));
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
        return "<ObservationDefinition{$xmlns}></ObservationDefinition>";
    }
    /**
     * @return string
     */
    public function _getResourceType()
    {
        return static::FHIR_TYPE_NAME;
    }


    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that classifies the general type of observation.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that classifies the general type of observation.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return static
     */
    public function addCategory(FHIRCodeableConcept $category = null)
    {
        $this->_trackValueAdded();
        $this->category[] = $category;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that classifies the general type of observation.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[] $category
     * @return static
     */
    public function setCategory(array $category = [])
    {
        if ([] !== $this->category) {
            $this->_trackValuesRemoved(count($this->category));
            $this->category = [];
        }
        if ([] === $category) {
            return $this;
        }
        foreach ($category as $v) {
            if ($v instanceof FHIRCodeableConcept) {
                $this->addCategory($v);
            } else {
                $this->addCategory(new FHIRCodeableConcept($v));
            }
        }
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes what will be observed. Sometimes this is called the observation
     * "name".
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes what will be observed. Sometimes this is called the observation
     * "name".
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return static
     */
    public function setCode(FHIRCodeableConcept $code = null)
    {
        $this->_trackValueSet($this->code, $code);
        $this->code = $code;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A unique identifier assigned to this ObservationDefinition artifact.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A unique identifier assigned to this ObservationDefinition artifact.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function addIdentifier(FHIRIdentifier $identifier = null)
    {
        $this->_trackValueAdded();
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A unique identifier assigned to this ObservationDefinition artifact.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[] $identifier
     * @return static
     */
    public function setIdentifier(array $identifier = [])
    {
        if ([] !== $this->identifier) {
            $this->_trackValuesRemoved(count($this->identifier));
            $this->identifier = [];
        }
        if ([] === $identifier) {
            return $this;
        }
        foreach ($identifier as $v) {
            if ($v instanceof FHIRIdentifier) {
                $this->addIdentifier($v);
            } else {
                $this->addIdentifier(new FHIRIdentifier($v));
            }
        }
        return $this;
    }

    /**
     * Permitted data type for observation value.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The data types allowed for the value element of the instance observations
     * conforming to this ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRObservationDataType[]
     */
    public function getPermittedDataType()
    {
        return $this->permittedDataType;
    }

    /**
     * Permitted data type for observation value.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The data types allowed for the value element of the instance observations
     * conforming to this ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRObservationDataType $permittedDataType
     * @return static
     */
    public function addPermittedDataType(FHIRObservationDataType $permittedDataType = null)
    {
        $this->_trackValueAdded();
        $this->permittedDataType[] = $permittedDataType;
        return $this;
    }

    /**
     * Permitted data type for observation value.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The data types allowed for the value element of the instance observations
     * conforming to this ObservationDefinition.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationDataType[] $permittedDataType
     * @return static
     */
    public function setPermittedDataType(array $permittedDataType = [])
    {
        if ([] !== $this->permittedDataType) {
            $this->_trackValuesRemoved(count($this->permittedDataType));
            $this->permittedDataType = [];
        }
        if ([] === $permittedDataType) {
            return $this;
        }
        foreach ($permittedDataType as $v) {
            if ($v instanceof FHIRObservationDataType) {
                $this->addPermittedDataType($v);
            } else {
                $this->addPermittedDataType(new FHIRObservationDataType($v));
            }
        }
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Multiple results allowed for observations conforming to this
     * ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getMultipleResultsAllowed()
    {
        return $this->multipleResultsAllowed;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Multiple results allowed for observations conforming to this
     * ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $multipleResultsAllowed
     * @return static
     */
    public function setMultipleResultsAllowed($multipleResultsAllowed = null)
    {
        if (null !== $multipleResultsAllowed && !($multipleResultsAllowed instanceof FHIRBoolean)) {
            $multipleResultsAllowed = new FHIRBoolean($multipleResultsAllowed);
        }
        $this->_trackValueSet($this->multipleResultsAllowed, $multipleResultsAllowed);
        $this->multipleResultsAllowed = $multipleResultsAllowed;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The method or technique used to perform the observation.
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
     * The method or technique used to perform the observation.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The preferred name to be used when reporting the results of observations
     * conforming to this ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPreferredReportName()
    {
        return $this->preferredReportName;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The preferred name to be used when reporting the results of observations
     * conforming to this ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $preferredReportName
     * @return static
     */
    public function setPreferredReportName($preferredReportName = null)
    {
        if (null !== $preferredReportName && !($preferredReportName instanceof FHIRString)) {
            $preferredReportName = new FHIRString($preferredReportName);
        }
        $this->_trackValueSet($this->preferredReportName, $preferredReportName);
        $this->preferredReportName = $preferredReportName;
        return $this;
    }

    /**
     * Set of definitional characteristics for a kind of observation or measurement
     * produced or consumed by an orderable health care service.
     *
     * Characteristics for quantitative results of this observation.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails
     */
    public function getQuantitativeDetails()
    {
        return $this->quantitativeDetails;
    }

    /**
     * Set of definitional characteristics for a kind of observation or measurement
     * produced or consumed by an orderable health care service.
     *
     * Characteristics for quantitative results of this observation.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQuantitativeDetails $quantitativeDetails
     * @return static
     */
    public function setQuantitativeDetails(FHIRObservationDefinitionQuantitativeDetails $quantitativeDetails = null)
    {
        $this->_trackValueSet($this->quantitativeDetails, $quantitativeDetails);
        $this->quantitativeDetails = $quantitativeDetails;
        return $this;
    }

    /**
     * Set of definitional characteristics for a kind of observation or measurement
     * produced or consumed by an orderable health care service.
     *
     * Multiple ranges of results qualified by different contexts for ordinal or
     * continuous observations conforming to this ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQualifiedInterval[]
     */
    public function getQualifiedInterval()
    {
        return $this->qualifiedInterval;
    }

    /**
     * Set of definitional characteristics for a kind of observation or measurement
     * produced or consumed by an orderable health care service.
     *
     * Multiple ranges of results qualified by different contexts for ordinal or
     * continuous observations conforming to this ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQualifiedInterval $qualifiedInterval
     * @return static
     */
    public function addQualifiedInterval(FHIRObservationDefinitionQualifiedInterval $qualifiedInterval = null)
    {
        $this->_trackValueAdded();
        $this->qualifiedInterval[] = $qualifiedInterval;
        return $this;
    }

    /**
     * Set of definitional characteristics for a kind of observation or measurement
     * produced or consumed by an orderable health care service.
     *
     * Multiple ranges of results qualified by different contexts for ordinal or
     * continuous observations conforming to this ObservationDefinition.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRObservationDefinition\FHIRObservationDefinitionQualifiedInterval[] $qualifiedInterval
     * @return static
     */
    public function setQualifiedInterval(array $qualifiedInterval = [])
    {
        if ([] !== $this->qualifiedInterval) {
            $this->_trackValuesRemoved(count($this->qualifiedInterval));
            $this->qualifiedInterval = [];
        }
        if ([] === $qualifiedInterval) {
            return $this;
        }
        foreach ($qualifiedInterval as $v) {
            if ($v instanceof FHIRObservationDefinitionQualifiedInterval) {
                $this->addQualifiedInterval($v);
            } else {
                $this->addQualifiedInterval(new FHIRObservationDefinitionQualifiedInterval($v));
            }
        }
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of valid coded results for the observations conforming to this
     * ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getValidCodedValueSet()
    {
        return $this->validCodedValueSet;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of valid coded results for the observations conforming to this
     * ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $validCodedValueSet
     * @return static
     */
    public function setValidCodedValueSet(FHIRReference $validCodedValueSet = null)
    {
        $this->_trackValueSet($this->validCodedValueSet, $validCodedValueSet);
        $this->validCodedValueSet = $validCodedValueSet;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of normal coded results for the observations conforming to this
     * ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getNormalCodedValueSet()
    {
        return $this->normalCodedValueSet;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of normal coded results for the observations conforming to this
     * ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $normalCodedValueSet
     * @return static
     */
    public function setNormalCodedValueSet(FHIRReference $normalCodedValueSet = null)
    {
        $this->_trackValueSet($this->normalCodedValueSet, $normalCodedValueSet);
        $this->normalCodedValueSet = $normalCodedValueSet;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of abnormal coded results for the observation conforming to this
     * ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAbnormalCodedValueSet()
    {
        return $this->abnormalCodedValueSet;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of abnormal coded results for the observation conforming to this
     * ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $abnormalCodedValueSet
     * @return static
     */
    public function setAbnormalCodedValueSet(FHIRReference $abnormalCodedValueSet = null)
    {
        $this->_trackValueSet($this->abnormalCodedValueSet, $abnormalCodedValueSet);
        $this->abnormalCodedValueSet = $abnormalCodedValueSet;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of critical coded results for the observation conforming to this
     * ObservationDefinition.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getCriticalCodedValueSet()
    {
        return $this->criticalCodedValueSet;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of critical coded results for the observation conforming to this
     * ObservationDefinition.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $criticalCodedValueSet
     * @return static
     */
    public function setCriticalCodedValueSet(FHIRReference $criticalCodedValueSet = null)
    {
        $this->_trackValueSet($this->criticalCodedValueSet, $criticalCodedValueSet);
        $this->criticalCodedValueSet = $criticalCodedValueSet;
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
        if ([] !== ($vs = $this->getCategory())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_CATEGORY, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getCode())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CODE] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getIdentifier())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_IDENTIFIER, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getPermittedDataType())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_PERMITTED_DATA_TYPE, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getMultipleResultsAllowed())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_MULTIPLE_RESULTS_ALLOWED] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getMethod())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_METHOD] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getPreferredReportName())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PREFERRED_REPORT_NAME] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getQuantitativeDetails())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_QUANTITATIVE_DETAILS] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getQualifiedInterval())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_QUALIFIED_INTERVAL, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getValidCodedValueSet())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_VALID_CODED_VALUE_SET] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getNormalCodedValueSet())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_NORMAL_CODED_VALUE_SET] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getAbnormalCodedValueSet())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ABNORMAL_CODED_VALUE_SET] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getCriticalCodedValueSet())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CRITICAL_CODED_VALUE_SET] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_CATEGORY])) {
            $v = $this->getCategory();
            foreach ($validationRules[self::FIELD_CATEGORY] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_CATEGORY, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CATEGORY])) {
                        $errs[self::FIELD_CATEGORY] = [];
                    }
                    $errs[self::FIELD_CATEGORY][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CODE])) {
            $v = $this->getCode();
            foreach ($validationRules[self::FIELD_CODE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_CODE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CODE])) {
                        $errs[self::FIELD_CODE] = [];
                    }
                    $errs[self::FIELD_CODE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_IDENTIFIER])) {
            $v = $this->getIdentifier();
            foreach ($validationRules[self::FIELD_IDENTIFIER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_IDENTIFIER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_IDENTIFIER])) {
                        $errs[self::FIELD_IDENTIFIER] = [];
                    }
                    $errs[self::FIELD_IDENTIFIER][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PERMITTED_DATA_TYPE])) {
            $v = $this->getPermittedDataType();
            foreach ($validationRules[self::FIELD_PERMITTED_DATA_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_PERMITTED_DATA_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PERMITTED_DATA_TYPE])) {
                        $errs[self::FIELD_PERMITTED_DATA_TYPE] = [];
                    }
                    $errs[self::FIELD_PERMITTED_DATA_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MULTIPLE_RESULTS_ALLOWED])) {
            $v = $this->getMultipleResultsAllowed();
            foreach ($validationRules[self::FIELD_MULTIPLE_RESULTS_ALLOWED] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_MULTIPLE_RESULTS_ALLOWED, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MULTIPLE_RESULTS_ALLOWED])) {
                        $errs[self::FIELD_MULTIPLE_RESULTS_ALLOWED] = [];
                    }
                    $errs[self::FIELD_MULTIPLE_RESULTS_ALLOWED][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_METHOD])) {
            $v = $this->getMethod();
            foreach ($validationRules[self::FIELD_METHOD] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_METHOD, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_METHOD])) {
                        $errs[self::FIELD_METHOD] = [];
                    }
                    $errs[self::FIELD_METHOD][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PREFERRED_REPORT_NAME])) {
            $v = $this->getPreferredReportName();
            foreach ($validationRules[self::FIELD_PREFERRED_REPORT_NAME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_PREFERRED_REPORT_NAME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PREFERRED_REPORT_NAME])) {
                        $errs[self::FIELD_PREFERRED_REPORT_NAME] = [];
                    }
                    $errs[self::FIELD_PREFERRED_REPORT_NAME][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_QUANTITATIVE_DETAILS])) {
            $v = $this->getQuantitativeDetails();
            foreach ($validationRules[self::FIELD_QUANTITATIVE_DETAILS] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_QUANTITATIVE_DETAILS, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_QUANTITATIVE_DETAILS])) {
                        $errs[self::FIELD_QUANTITATIVE_DETAILS] = [];
                    }
                    $errs[self::FIELD_QUANTITATIVE_DETAILS][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_QUALIFIED_INTERVAL])) {
            $v = $this->getQualifiedInterval();
            foreach ($validationRules[self::FIELD_QUALIFIED_INTERVAL] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_QUALIFIED_INTERVAL, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_QUALIFIED_INTERVAL])) {
                        $errs[self::FIELD_QUALIFIED_INTERVAL] = [];
                    }
                    $errs[self::FIELD_QUALIFIED_INTERVAL][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_VALID_CODED_VALUE_SET])) {
            $v = $this->getValidCodedValueSet();
            foreach ($validationRules[self::FIELD_VALID_CODED_VALUE_SET] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_VALID_CODED_VALUE_SET, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_VALID_CODED_VALUE_SET])) {
                        $errs[self::FIELD_VALID_CODED_VALUE_SET] = [];
                    }
                    $errs[self::FIELD_VALID_CODED_VALUE_SET][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_NORMAL_CODED_VALUE_SET])) {
            $v = $this->getNormalCodedValueSet();
            foreach ($validationRules[self::FIELD_NORMAL_CODED_VALUE_SET] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_NORMAL_CODED_VALUE_SET, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_NORMAL_CODED_VALUE_SET])) {
                        $errs[self::FIELD_NORMAL_CODED_VALUE_SET] = [];
                    }
                    $errs[self::FIELD_NORMAL_CODED_VALUE_SET][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ABNORMAL_CODED_VALUE_SET])) {
            $v = $this->getAbnormalCodedValueSet();
            foreach ($validationRules[self::FIELD_ABNORMAL_CODED_VALUE_SET] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_ABNORMAL_CODED_VALUE_SET, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ABNORMAL_CODED_VALUE_SET])) {
                        $errs[self::FIELD_ABNORMAL_CODED_VALUE_SET] = [];
                    }
                    $errs[self::FIELD_ABNORMAL_CODED_VALUE_SET][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CRITICAL_CODED_VALUE_SET])) {
            $v = $this->getCriticalCodedValueSet();
            foreach ($validationRules[self::FIELD_CRITICAL_CODED_VALUE_SET] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_OBSERVATION_DEFINITION, self::FIELD_CRITICAL_CODED_VALUE_SET, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CRITICAL_CODED_VALUE_SET])) {
                        $errs[self::FIELD_CRITICAL_CODED_VALUE_SET] = [];
                    }
                    $errs[self::FIELD_CRITICAL_CODED_VALUE_SET][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TEXT])) {
            $v = $this->getText();
            foreach ($validationRules[self::FIELD_TEXT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_TEXT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TEXT])) {
                        $errs[self::FIELD_TEXT] = [];
                    }
                    $errs[self::FIELD_TEXT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTAINED])) {
            $v = $this->getContained();
            foreach ($validationRules[self::FIELD_CONTAINED] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_CONTAINED, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTAINED])) {
                        $errs[self::FIELD_CONTAINED] = [];
                    }
                    $errs[self::FIELD_CONTAINED][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach ($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MODIFIER_EXTENSION])) {
            $v = $this->getModifierExtension();
            foreach ($validationRules[self::FIELD_MODIFIER_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_MODIFIER_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MODIFIER_EXTENSION])) {
                        $errs[self::FIELD_MODIFIER_EXTENSION] = [];
                    }
                    $errs[self::FIELD_MODIFIER_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach ($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_META])) {
            $v = $this->getMeta();
            foreach ($validationRules[self::FIELD_META] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_META, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_META])) {
                        $errs[self::FIELD_META] = [];
                    }
                    $errs[self::FIELD_META][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_IMPLICIT_RULES])) {
            $v = $this->getImplicitRules();
            foreach ($validationRules[self::FIELD_IMPLICIT_RULES] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_IMPLICIT_RULES, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_IMPLICIT_RULES])) {
                        $errs[self::FIELD_IMPLICIT_RULES] = [];
                    }
                    $errs[self::FIELD_IMPLICIT_RULES][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LANGUAGE])) {
            $v = $this->getLanguage();
            foreach ($validationRules[self::FIELD_LANGUAGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_LANGUAGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LANGUAGE])) {
                        $errs[self::FIELD_LANGUAGE] = [];
                    }
                    $errs[self::FIELD_LANGUAGE][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRObservationDefinition $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRObservationDefinition
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
                throw new \DomainException(sprintf('FHIRObservationDefinition::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function (\libXMLError $err) {
                    return $err->message;
                }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRObservationDefinition::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRObservationDefinition(null);
        } elseif (!is_object($type) || !($type instanceof FHIRObservationDefinition)) {
            throw new \RuntimeException(sprintf(
                'FHIRObservationDefinition::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRObservationDefinition or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for ($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_CATEGORY === $n->nodeName) {
                $type->addCategory(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_CODE === $n->nodeName) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_IDENTIFIER === $n->nodeName) {
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($n));
            } elseif (self::FIELD_PERMITTED_DATA_TYPE === $n->nodeName) {
                $type->addPermittedDataType(FHIRObservationDataType::xmlUnserialize($n));
            } elseif (self::FIELD_MULTIPLE_RESULTS_ALLOWED === $n->nodeName) {
                $type->setMultipleResultsAllowed(FHIRBoolean::xmlUnserialize($n));
            } elseif (self::FIELD_METHOD === $n->nodeName) {
                $type->setMethod(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_PREFERRED_REPORT_NAME === $n->nodeName) {
                $type->setPreferredReportName(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_QUANTITATIVE_DETAILS === $n->nodeName) {
                $type->setQuantitativeDetails(FHIRObservationDefinitionQuantitativeDetails::xmlUnserialize($n));
            } elseif (self::FIELD_QUALIFIED_INTERVAL === $n->nodeName) {
                $type->addQualifiedInterval(FHIRObservationDefinitionQualifiedInterval::xmlUnserialize($n));
            } elseif (self::FIELD_VALID_CODED_VALUE_SET === $n->nodeName) {
                $type->setValidCodedValueSet(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_NORMAL_CODED_VALUE_SET === $n->nodeName) {
                $type->setNormalCodedValueSet(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_ABNORMAL_CODED_VALUE_SET === $n->nodeName) {
                $type->setAbnormalCodedValueSet(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_CRITICAL_CODED_VALUE_SET === $n->nodeName) {
                $type->setCriticalCodedValueSet(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_TEXT === $n->nodeName) {
                $type->setText(FHIRNarrative::xmlUnserialize($n));
            } elseif (self::FIELD_CONTAINED === $n->nodeName) {
                for ($ni = 0; $ni < $n->childNodes->length; $ni++) {
                    $nn = $n->childNodes->item($ni);
                    if ($nn instanceof \DOMElement) {
                        $type->addContained(PHPFHIRTypeMap::getContainedTypeFromXML($nn));
                    }
                }
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRId::xmlUnserialize($n));
            } elseif (self::FIELD_META === $n->nodeName) {
                $type->setMeta(FHIRMeta::xmlUnserialize($n));
            } elseif (self::FIELD_IMPLICIT_RULES === $n->nodeName) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_LANGUAGE === $n->nodeName) {
                $type->setLanguage(FHIRCode::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_MULTIPLE_RESULTS_ALLOWED);
        if (null !== $n) {
            $pt = $type->getMultipleResultsAllowed();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setMultipleResultsAllowed($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_PREFERRED_REPORT_NAME);
        if (null !== $n) {
            $pt = $type->getPreferredReportName();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setPreferredReportName($n->nodeValue);
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
        $n = $element->attributes->getNamedItem(self::FIELD_IMPLICIT_RULES);
        if (null !== $n) {
            $pt = $type->getImplicitRules();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setImplicitRules($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_LANGUAGE);
        if (null !== $n) {
            $pt = $type->getLanguage();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setLanguage($n->nodeValue);
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
        if ([] !== ($vs = $this->getCategory())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_CATEGORY);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getCode())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CODE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getIdentifier())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_IDENTIFIER);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getPermittedDataType())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_PERMITTED_DATA_TYPE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getMultipleResultsAllowed())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_MULTIPLE_RESULTS_ALLOWED);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getMethod())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_METHOD);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getPreferredReportName())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PREFERRED_REPORT_NAME);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getQuantitativeDetails())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_QUANTITATIVE_DETAILS);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getQualifiedInterval())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_QUALIFIED_INTERVAL);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getValidCodedValueSet())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_VALID_CODED_VALUE_SET);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getNormalCodedValueSet())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_NORMAL_CODED_VALUE_SET);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getAbnormalCodedValueSet())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ABNORMAL_CODED_VALUE_SET);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getCriticalCodedValueSet())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CRITICAL_CODED_VALUE_SET);
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
        if ([] !== ($vs = $this->getCategory())) {
            $a[self::FIELD_CATEGORY] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_CATEGORY][] = $v;
            }
        }
        if (null !== ($v = $this->getCode())) {
            $a[self::FIELD_CODE] = $v;
        }
        if ([] !== ($vs = $this->getIdentifier())) {
            $a[self::FIELD_IDENTIFIER] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_IDENTIFIER][] = $v;
            }
        }
        if ([] !== ($vs = $this->getPermittedDataType())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRObservationDataType::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_PERMITTED_DATA_TYPE] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_PERMITTED_DATA_TYPE_EXT] = $exts;
            }
        }
        if (null !== ($v = $this->getMultipleResultsAllowed())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_MULTIPLE_RESULTS_ALLOWED] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRBoolean::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_MULTIPLE_RESULTS_ALLOWED_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getMethod())) {
            $a[self::FIELD_METHOD] = $v;
        }
        if (null !== ($v = $this->getPreferredReportName())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_PREFERRED_REPORT_NAME] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_PREFERRED_REPORT_NAME_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getQuantitativeDetails())) {
            $a[self::FIELD_QUANTITATIVE_DETAILS] = $v;
        }
        if ([] !== ($vs = $this->getQualifiedInterval())) {
            $a[self::FIELD_QUALIFIED_INTERVAL] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_QUALIFIED_INTERVAL][] = $v;
            }
        }
        if (null !== ($v = $this->getValidCodedValueSet())) {
            $a[self::FIELD_VALID_CODED_VALUE_SET] = $v;
        }
        if (null !== ($v = $this->getNormalCodedValueSet())) {
            $a[self::FIELD_NORMAL_CODED_VALUE_SET] = $v;
        }
        if (null !== ($v = $this->getAbnormalCodedValueSet())) {
            $a[self::FIELD_ABNORMAL_CODED_VALUE_SET] = $v;
        }
        if (null !== ($v = $this->getCriticalCodedValueSet())) {
            $a[self::FIELD_CRITICAL_CODED_VALUE_SET] = $v;
        }
        return [PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE => $this->_getResourceType()] + $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}
