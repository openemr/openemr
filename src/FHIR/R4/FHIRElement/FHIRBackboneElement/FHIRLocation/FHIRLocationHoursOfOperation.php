<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRLocation;

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
use OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDaysOfWeek;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRTime;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * Details and position information for a physical place where services are
 * provided and resources and participants may be stored, found, contained, or
 * accommodated.
 *
 * Class FHIRLocationHoursOfOperation
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRLocation
 */
class FHIRLocationHoursOfOperation extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_LOCATION_DOT_HOURS_OF_OPERATION;
    const FIELD_DAYS_OF_WEEK = 'daysOfWeek';
    const FIELD_DAYS_OF_WEEK_EXT = '_daysOfWeek';
    const FIELD_ALL_DAY = 'allDay';
    const FIELD_ALL_DAY_EXT = '_allDay';
    const FIELD_OPENING_TIME = 'openingTime';
    const FIELD_OPENING_TIME_EXT = '_openingTime';
    const FIELD_CLOSING_TIME = 'closingTime';
    const FIELD_CLOSING_TIME_EXT = '_closingTime';

    /** @var string */
    private $_xmlns = '';

    /**
     * The days of the week.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates which days of the week are available between the start and end Times.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDaysOfWeek[]
     */
    protected $daysOfWeek = [];

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Location is open all day.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    protected $allDay = null;

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location opens.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    protected $openingTime = null;

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location closes.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    protected $closingTime = null;

    /**
     * Validation map for fields in type Location.HoursOfOperation
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRLocationHoursOfOperation Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRLocationHoursOfOperation::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_DAYS_OF_WEEK]) || isset($data[self::FIELD_DAYS_OF_WEEK_EXT])) {
            $value = isset($data[self::FIELD_DAYS_OF_WEEK]) ? $data[self::FIELD_DAYS_OF_WEEK] : null;
            $ext = (isset($data[self::FIELD_DAYS_OF_WEEK_EXT]) && is_array($data[self::FIELD_DAYS_OF_WEEK_EXT])) ? $ext = $data[self::FIELD_DAYS_OF_WEEK_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRDaysOfWeek) {
                    $this->addDaysOfWeek($value);
                } else if (is_array($value)) {
                    foreach($value as $i => $v) {
                        if ($v instanceof FHIRDaysOfWeek) {
                            $this->addDaysOfWeek($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addDaysOfWeek(new FHIRDaysOfWeek(array_merge($v, $iext)));
                            } else {
                                $this->addDaysOfWeek(new FHIRDaysOfWeek([FHIRDaysOfWeek::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addDaysOfWeek(new FHIRDaysOfWeek(array_merge($ext, $value)));
                } else {
                    $this->addDaysOfWeek(new FHIRDaysOfWeek([FHIRDaysOfWeek::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach($ext as $iext) {
                    $this->addDaysOfWeek(new FHIRDaysOfWeek($iext));
                }
            }
        }
        if (isset($data[self::FIELD_ALL_DAY]) || isset($data[self::FIELD_ALL_DAY_EXT])) {
            $value = isset($data[self::FIELD_ALL_DAY]) ? $data[self::FIELD_ALL_DAY] : null;
            $ext = (isset($data[self::FIELD_ALL_DAY_EXT]) && is_array($data[self::FIELD_ALL_DAY_EXT])) ? $ext = $data[self::FIELD_ALL_DAY_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRBoolean) {
                    $this->setAllDay($value);
                } else if (is_array($value)) {
                    $this->setAllDay(new FHIRBoolean(array_merge($ext, $value)));
                } else {
                    $this->setAllDay(new FHIRBoolean([FHIRBoolean::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setAllDay(new FHIRBoolean($ext));
            }
        }
        if (isset($data[self::FIELD_OPENING_TIME]) || isset($data[self::FIELD_OPENING_TIME_EXT])) {
            $value = isset($data[self::FIELD_OPENING_TIME]) ? $data[self::FIELD_OPENING_TIME] : null;
            $ext = (isset($data[self::FIELD_OPENING_TIME_EXT]) && is_array($data[self::FIELD_OPENING_TIME_EXT])) ? $ext = $data[self::FIELD_OPENING_TIME_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRTime) {
                    $this->setOpeningTime($value);
                } else if (is_array($value)) {
                    $this->setOpeningTime(new FHIRTime(array_merge($ext, $value)));
                } else {
                    $this->setOpeningTime(new FHIRTime([FHIRTime::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setOpeningTime(new FHIRTime($ext));
            }
        }
        if (isset($data[self::FIELD_CLOSING_TIME]) || isset($data[self::FIELD_CLOSING_TIME_EXT])) {
            $value = isset($data[self::FIELD_CLOSING_TIME]) ? $data[self::FIELD_CLOSING_TIME] : null;
            $ext = (isset($data[self::FIELD_CLOSING_TIME_EXT]) && is_array($data[self::FIELD_CLOSING_TIME_EXT])) ? $ext = $data[self::FIELD_CLOSING_TIME_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRTime) {
                    $this->setClosingTime($value);
                } else if (is_array($value)) {
                    $this->setClosingTime(new FHIRTime(array_merge($ext, $value)));
                } else {
                    $this->setClosingTime(new FHIRTime([FHIRTime::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setClosingTime(new FHIRTime($ext));
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
        return "<LocationHoursOfOperation{$xmlns}></LocationHoursOfOperation>";
    }

    /**
     * The days of the week.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates which days of the week are available between the start and end Times.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDaysOfWeek[]
     */
    public function getDaysOfWeek()
    {
        return $this->daysOfWeek;
    }

    /**
     * The days of the week.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates which days of the week are available between the start and end Times.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRDaysOfWeek $daysOfWeek
     * @return static
     */
    public function addDaysOfWeek(FHIRDaysOfWeek $daysOfWeek = null)
    {
        $this->_trackValueAdded();
        $this->daysOfWeek[] = $daysOfWeek;
        return $this;
    }

    /**
     * The days of the week.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates which days of the week are available between the start and end Times.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDaysOfWeek[] $daysOfWeek
     * @return static
     */
    public function setDaysOfWeek(array $daysOfWeek = [])
    {
        if ([] !== $this->daysOfWeek) {
            $this->_trackValuesRemoved(count($this->daysOfWeek));
            $this->daysOfWeek = [];
        }
        if ([] === $daysOfWeek) {
            return $this;
        }
        foreach($daysOfWeek as $v) {
            if ($v instanceof FHIRDaysOfWeek) {
                $this->addDaysOfWeek($v);
            } else {
                $this->addDaysOfWeek(new FHIRDaysOfWeek($v));
            }
        }
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Location is open all day.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getAllDay()
    {
        return $this->allDay;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Location is open all day.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $allDay
     * @return static
     */
    public function setAllDay($allDay = null)
    {
        if (null !== $allDay && !($allDay instanceof FHIRBoolean)) {
            $allDay = new FHIRBoolean($allDay);
        }
        $this->_trackValueSet($this->allDay, $allDay);
        $this->allDay = $allDay;
        return $this;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location opens.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public function getOpeningTime()
    {
        return $this->openingTime;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location opens.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRTime $openingTime
     * @return static
     */
    public function setOpeningTime($openingTime = null)
    {
        if (null !== $openingTime && !($openingTime instanceof FHIRTime)) {
            $openingTime = new FHIRTime($openingTime);
        }
        $this->_trackValueSet($this->openingTime, $openingTime);
        $this->openingTime = $openingTime;
        return $this;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location closes.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public function getClosingTime()
    {
        return $this->closingTime;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time that the Location closes.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRTime $closingTime
     * @return static
     */
    public function setClosingTime($closingTime = null)
    {
        if (null !== $closingTime && !($closingTime instanceof FHIRTime)) {
            $closingTime = new FHIRTime($closingTime);
        }
        $this->_trackValueSet($this->closingTime, $closingTime);
        $this->closingTime = $closingTime;
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
        if ([] !== ($vs = $this->getDaysOfWeek())) {
            foreach($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_DAYS_OF_WEEK, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getAllDay())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ALL_DAY] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getOpeningTime())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_OPENING_TIME] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getClosingTime())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CLOSING_TIME] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_DAYS_OF_WEEK])) {
            $v = $this->getDaysOfWeek();
            foreach($validationRules[self::FIELD_DAYS_OF_WEEK] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_LOCATION_DOT_HOURS_OF_OPERATION, self::FIELD_DAYS_OF_WEEK, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DAYS_OF_WEEK])) {
                        $errs[self::FIELD_DAYS_OF_WEEK] = [];
                    }
                    $errs[self::FIELD_DAYS_OF_WEEK][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ALL_DAY])) {
            $v = $this->getAllDay();
            foreach($validationRules[self::FIELD_ALL_DAY] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_LOCATION_DOT_HOURS_OF_OPERATION, self::FIELD_ALL_DAY, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ALL_DAY])) {
                        $errs[self::FIELD_ALL_DAY] = [];
                    }
                    $errs[self::FIELD_ALL_DAY][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_OPENING_TIME])) {
            $v = $this->getOpeningTime();
            foreach($validationRules[self::FIELD_OPENING_TIME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_LOCATION_DOT_HOURS_OF_OPERATION, self::FIELD_OPENING_TIME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_OPENING_TIME])) {
                        $errs[self::FIELD_OPENING_TIME] = [];
                    }
                    $errs[self::FIELD_OPENING_TIME][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CLOSING_TIME])) {
            $v = $this->getClosingTime();
            foreach($validationRules[self::FIELD_CLOSING_TIME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_LOCATION_DOT_HOURS_OF_OPERATION, self::FIELD_CLOSING_TIME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CLOSING_TIME])) {
                        $errs[self::FIELD_CLOSING_TIME] = [];
                    }
                    $errs[self::FIELD_CLOSING_TIME][$rule] = $err;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationHoursOfOperation $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationHoursOfOperation
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
                throw new \DomainException(sprintf('FHIRLocationHoursOfOperation::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRLocationHoursOfOperation::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRLocationHoursOfOperation(null);
        } elseif (!is_object($type) || !($type instanceof FHIRLocationHoursOfOperation)) {
            throw new \RuntimeException(sprintf(
                'FHIRLocationHoursOfOperation::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationHoursOfOperation or null, %s seen.',
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
            if (self::FIELD_DAYS_OF_WEEK === $n->nodeName) {
                $type->addDaysOfWeek(FHIRDaysOfWeek::xmlUnserialize($n));
            } elseif (self::FIELD_ALL_DAY === $n->nodeName) {
                $type->setAllDay(FHIRBoolean::xmlUnserialize($n));
            } elseif (self::FIELD_OPENING_TIME === $n->nodeName) {
                $type->setOpeningTime(FHIRTime::xmlUnserialize($n));
            } elseif (self::FIELD_CLOSING_TIME === $n->nodeName) {
                $type->setClosingTime(FHIRTime::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ALL_DAY);
        if (null !== $n) {
            $pt = $type->getAllDay();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setAllDay($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_OPENING_TIME);
        if (null !== $n) {
            $pt = $type->getOpeningTime();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setOpeningTime($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_CLOSING_TIME);
        if (null !== $n) {
            $pt = $type->getClosingTime();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setClosingTime($n->nodeValue);
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
        if ([] !== ($vs = $this->getDaysOfWeek())) {
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_DAYS_OF_WEEK);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getAllDay())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ALL_DAY);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getOpeningTime())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_OPENING_TIME);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getClosingTime())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CLOSING_TIME);
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
        if ([] !== ($vs = $this->getDaysOfWeek())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRDaysOfWeek::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_DAYS_OF_WEEK] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_DAYS_OF_WEEK_EXT] = $exts;
            }
        }
        if (null !== ($v = $this->getAllDay())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_ALL_DAY] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRBoolean::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_ALL_DAY_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getOpeningTime())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_OPENING_TIME] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRTime::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_OPENING_TIME_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getClosingTime())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_CLOSING_TIME] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRTime::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_CLOSING_TIME_EXT] = $ext;
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