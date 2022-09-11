<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient;

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
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRatio;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * An ingredient of a manufactured item or pharmaceutical product.
 *
 * Class FHIRMedicinalProductIngredientStrength
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient
 */
class FHIRMedicinalProductIngredientStrength extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT_DOT_STRENGTH;
    const FIELD_PRESENTATION = 'presentation';
    const FIELD_PRESENTATION_LOW_LIMIT = 'presentationLowLimit';
    const FIELD_CONCENTRATION = 'concentration';
    const FIELD_CONCENTRATION_LOW_LIMIT = 'concentrationLowLimit';
    const FIELD_MEASUREMENT_POINT = 'measurementPoint';
    const FIELD_MEASUREMENT_POINT_EXT = '_measurementPoint';
    const FIELD_COUNTRY = 'country';
    const FIELD_REFERENCE_STRENGTH = 'referenceStrength';

    /** @var string */
    private $_xmlns = '';

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of substance in the unit of presentation, or in the volume (or
     * mass) of the single pharmaceutical product or manufactured item.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    protected $presentation = null;

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the quantity of substance in the unit of presentation. For use
     * when there is a range of strengths, this is the lower limit, with the
     * presentation attribute becoming the upper limit.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    protected $presentationLowLimit = null;

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The strength per unitary volume (or mass).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    protected $concentration = null;

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the strength per unitary volume (or mass), for when there is a
     * range. The concentration attribute then becomes the upper limit.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    protected $concentrationLowLimit = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * For when strength is measured at a particular point or distance.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $measurementPoint = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country or countries for which the strength range applies.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    protected $country = [];

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * Strength expressed in terms of a reference substance.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength[]
     */
    protected $referenceStrength = [];

    /**
     * Validation map for fields in type MedicinalProductIngredient.Strength
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRMedicinalProductIngredientStrength Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRMedicinalProductIngredientStrength::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_PRESENTATION])) {
            if ($data[self::FIELD_PRESENTATION] instanceof FHIRRatio) {
                $this->setPresentation($data[self::FIELD_PRESENTATION]);
            } else {
                $this->setPresentation(new FHIRRatio($data[self::FIELD_PRESENTATION]));
            }
        }
        if (isset($data[self::FIELD_PRESENTATION_LOW_LIMIT])) {
            if ($data[self::FIELD_PRESENTATION_LOW_LIMIT] instanceof FHIRRatio) {
                $this->setPresentationLowLimit($data[self::FIELD_PRESENTATION_LOW_LIMIT]);
            } else {
                $this->setPresentationLowLimit(new FHIRRatio($data[self::FIELD_PRESENTATION_LOW_LIMIT]));
            }
        }
        if (isset($data[self::FIELD_CONCENTRATION])) {
            if ($data[self::FIELD_CONCENTRATION] instanceof FHIRRatio) {
                $this->setConcentration($data[self::FIELD_CONCENTRATION]);
            } else {
                $this->setConcentration(new FHIRRatio($data[self::FIELD_CONCENTRATION]));
            }
        }
        if (isset($data[self::FIELD_CONCENTRATION_LOW_LIMIT])) {
            if ($data[self::FIELD_CONCENTRATION_LOW_LIMIT] instanceof FHIRRatio) {
                $this->setConcentrationLowLimit($data[self::FIELD_CONCENTRATION_LOW_LIMIT]);
            } else {
                $this->setConcentrationLowLimit(new FHIRRatio($data[self::FIELD_CONCENTRATION_LOW_LIMIT]));
            }
        }
        if (isset($data[self::FIELD_MEASUREMENT_POINT]) || isset($data[self::FIELD_MEASUREMENT_POINT_EXT])) {
            $value = isset($data[self::FIELD_MEASUREMENT_POINT]) ? $data[self::FIELD_MEASUREMENT_POINT] : null;
            $ext = (isset($data[self::FIELD_MEASUREMENT_POINT_EXT]) && is_array($data[self::FIELD_MEASUREMENT_POINT_EXT])) ? $ext = $data[self::FIELD_MEASUREMENT_POINT_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setMeasurementPoint($value);
                } else if (is_array($value)) {
                    $this->setMeasurementPoint(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setMeasurementPoint(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setMeasurementPoint(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_COUNTRY])) {
            if (is_array($data[self::FIELD_COUNTRY])) {
                foreach($data[self::FIELD_COUNTRY] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRCodeableConcept) {
                        $this->addCountry($v);
                    } else {
                        $this->addCountry(new FHIRCodeableConcept($v));
                    }
                }
            } elseif ($data[self::FIELD_COUNTRY] instanceof FHIRCodeableConcept) {
                $this->addCountry($data[self::FIELD_COUNTRY]);
            } else {
                $this->addCountry(new FHIRCodeableConcept($data[self::FIELD_COUNTRY]));
            }
        }
        if (isset($data[self::FIELD_REFERENCE_STRENGTH])) {
            if (is_array($data[self::FIELD_REFERENCE_STRENGTH])) {
                foreach($data[self::FIELD_REFERENCE_STRENGTH] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRMedicinalProductIngredientReferenceStrength) {
                        $this->addReferenceStrength($v);
                    } else {
                        $this->addReferenceStrength(new FHIRMedicinalProductIngredientReferenceStrength($v));
                    }
                }
            } elseif ($data[self::FIELD_REFERENCE_STRENGTH] instanceof FHIRMedicinalProductIngredientReferenceStrength) {
                $this->addReferenceStrength($data[self::FIELD_REFERENCE_STRENGTH]);
            } else {
                $this->addReferenceStrength(new FHIRMedicinalProductIngredientReferenceStrength($data[self::FIELD_REFERENCE_STRENGTH]));
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
        return "<MedicinalProductIngredientStrength{$xmlns}></MedicinalProductIngredientStrength>";
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of substance in the unit of presentation, or in the volume (or
     * mass) of the single pharmaceutical product or manufactured item.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of substance in the unit of presentation, or in the volume (or
     * mass) of the single pharmaceutical product or manufactured item.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $presentation
     * @return static
     */
    public function setPresentation(FHIRRatio $presentation = null)
    {
        $this->_trackValueSet($this->presentation, $presentation);
        $this->presentation = $presentation;
        return $this;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the quantity of substance in the unit of presentation. For use
     * when there is a range of strengths, this is the lower limit, with the
     * presentation attribute becoming the upper limit.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getPresentationLowLimit()
    {
        return $this->presentationLowLimit;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the quantity of substance in the unit of presentation. For use
     * when there is a range of strengths, this is the lower limit, with the
     * presentation attribute becoming the upper limit.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $presentationLowLimit
     * @return static
     */
    public function setPresentationLowLimit(FHIRRatio $presentationLowLimit = null)
    {
        $this->_trackValueSet($this->presentationLowLimit, $presentationLowLimit);
        $this->presentationLowLimit = $presentationLowLimit;
        return $this;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The strength per unitary volume (or mass).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getConcentration()
    {
        return $this->concentration;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The strength per unitary volume (or mass).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $concentration
     * @return static
     */
    public function setConcentration(FHIRRatio $concentration = null)
    {
        $this->_trackValueSet($this->concentration, $concentration);
        $this->concentration = $concentration;
        return $this;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the strength per unitary volume (or mass), for when there is a
     * range. The concentration attribute then becomes the upper limit.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getConcentrationLowLimit()
    {
        return $this->concentrationLowLimit;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A lower limit for the strength per unitary volume (or mass), for when there is a
     * range. The concentration attribute then becomes the upper limit.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $concentrationLowLimit
     * @return static
     */
    public function setConcentrationLowLimit(FHIRRatio $concentrationLowLimit = null)
    {
        $this->_trackValueSet($this->concentrationLowLimit, $concentrationLowLimit);
        $this->concentrationLowLimit = $concentrationLowLimit;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * For when strength is measured at a particular point or distance.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMeasurementPoint()
    {
        return $this->measurementPoint;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * For when strength is measured at a particular point or distance.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $measurementPoint
     * @return static
     */
    public function setMeasurementPoint($measurementPoint = null)
    {
        if (null !== $measurementPoint && !($measurementPoint instanceof FHIRString)) {
            $measurementPoint = new FHIRString($measurementPoint);
        }
        $this->_trackValueSet($this->measurementPoint, $measurementPoint);
        $this->measurementPoint = $measurementPoint;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country or countries for which the strength range applies.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country or countries for which the strength range applies.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $country
     * @return static
     */
    public function addCountry(FHIRCodeableConcept $country = null)
    {
        $this->_trackValueAdded();
        $this->country[] = $country;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country or countries for which the strength range applies.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[] $country
     * @return static
     */
    public function setCountry(array $country = [])
    {
        if ([] !== $this->country) {
            $this->_trackValuesRemoved(count($this->country));
            $this->country = [];
        }
        if ([] === $country) {
            return $this;
        }
        foreach($country as $v) {
            if ($v instanceof FHIRCodeableConcept) {
                $this->addCountry($v);
            } else {
                $this->addCountry(new FHIRCodeableConcept($v));
            }
        }
        return $this;
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * Strength expressed in terms of a reference substance.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength[]
     */
    public function getReferenceStrength()
    {
        return $this->referenceStrength;
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * Strength expressed in terms of a reference substance.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength $referenceStrength
     * @return static
     */
    public function addReferenceStrength(FHIRMedicinalProductIngredientReferenceStrength $referenceStrength = null)
    {
        $this->_trackValueAdded();
        $this->referenceStrength[] = $referenceStrength;
        return $this;
    }

    /**
     * An ingredient of a manufactured item or pharmaceutical product.
     *
     * Strength expressed in terms of a reference substance.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientReferenceStrength[] $referenceStrength
     * @return static
     */
    public function setReferenceStrength(array $referenceStrength = [])
    {
        if ([] !== $this->referenceStrength) {
            $this->_trackValuesRemoved(count($this->referenceStrength));
            $this->referenceStrength = [];
        }
        if ([] === $referenceStrength) {
            return $this;
        }
        foreach($referenceStrength as $v) {
            if ($v instanceof FHIRMedicinalProductIngredientReferenceStrength) {
                $this->addReferenceStrength($v);
            } else {
                $this->addReferenceStrength(new FHIRMedicinalProductIngredientReferenceStrength($v));
            }
        }
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
        if (null !== ($v = $this->getPresentation())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PRESENTATION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getPresentationLowLimit())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_PRESENTATION_LOW_LIMIT] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getConcentration())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CONCENTRATION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getConcentrationLowLimit())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CONCENTRATION_LOW_LIMIT] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getMeasurementPoint())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_MEASUREMENT_POINT] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getCountry())) {
            foreach($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_COUNTRY, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getReferenceStrength())) {
            foreach($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_REFERENCE_STRENGTH, $i)] = $fieldErrs;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PRESENTATION])) {
            $v = $this->getPresentation();
            foreach($validationRules[self::FIELD_PRESENTATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT_DOT_STRENGTH, self::FIELD_PRESENTATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PRESENTATION])) {
                        $errs[self::FIELD_PRESENTATION] = [];
                    }
                    $errs[self::FIELD_PRESENTATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PRESENTATION_LOW_LIMIT])) {
            $v = $this->getPresentationLowLimit();
            foreach($validationRules[self::FIELD_PRESENTATION_LOW_LIMIT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT_DOT_STRENGTH, self::FIELD_PRESENTATION_LOW_LIMIT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PRESENTATION_LOW_LIMIT])) {
                        $errs[self::FIELD_PRESENTATION_LOW_LIMIT] = [];
                    }
                    $errs[self::FIELD_PRESENTATION_LOW_LIMIT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONCENTRATION])) {
            $v = $this->getConcentration();
            foreach($validationRules[self::FIELD_CONCENTRATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT_DOT_STRENGTH, self::FIELD_CONCENTRATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONCENTRATION])) {
                        $errs[self::FIELD_CONCENTRATION] = [];
                    }
                    $errs[self::FIELD_CONCENTRATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONCENTRATION_LOW_LIMIT])) {
            $v = $this->getConcentrationLowLimit();
            foreach($validationRules[self::FIELD_CONCENTRATION_LOW_LIMIT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT_DOT_STRENGTH, self::FIELD_CONCENTRATION_LOW_LIMIT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONCENTRATION_LOW_LIMIT])) {
                        $errs[self::FIELD_CONCENTRATION_LOW_LIMIT] = [];
                    }
                    $errs[self::FIELD_CONCENTRATION_LOW_LIMIT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MEASUREMENT_POINT])) {
            $v = $this->getMeasurementPoint();
            foreach($validationRules[self::FIELD_MEASUREMENT_POINT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT_DOT_STRENGTH, self::FIELD_MEASUREMENT_POINT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MEASUREMENT_POINT])) {
                        $errs[self::FIELD_MEASUREMENT_POINT] = [];
                    }
                    $errs[self::FIELD_MEASUREMENT_POINT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_COUNTRY])) {
            $v = $this->getCountry();
            foreach($validationRules[self::FIELD_COUNTRY] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT_DOT_STRENGTH, self::FIELD_COUNTRY, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_COUNTRY])) {
                        $errs[self::FIELD_COUNTRY] = [];
                    }
                    $errs[self::FIELD_COUNTRY][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_REFERENCE_STRENGTH])) {
            $v = $this->getReferenceStrength();
            foreach($validationRules[self::FIELD_REFERENCE_STRENGTH] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MEDICINAL_PRODUCT_INGREDIENT_DOT_STRENGTH, self::FIELD_REFERENCE_STRENGTH, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_REFERENCE_STRENGTH])) {
                        $errs[self::FIELD_REFERENCE_STRENGTH] = [];
                    }
                    $errs[self::FIELD_REFERENCE_STRENGTH][$rule] = $err;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientStrength $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientStrength
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
                throw new \DomainException(sprintf('FHIRMedicinalProductIngredientStrength::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRMedicinalProductIngredientStrength::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRMedicinalProductIngredientStrength(null);
        } elseif (!is_object($type) || !($type instanceof FHIRMedicinalProductIngredientStrength)) {
            throw new \RuntimeException(sprintf(
                'FHIRMedicinalProductIngredientStrength::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductIngredient\FHIRMedicinalProductIngredientStrength or null, %s seen.',
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
            if (self::FIELD_PRESENTATION === $n->nodeName) {
                $type->setPresentation(FHIRRatio::xmlUnserialize($n));
            } elseif (self::FIELD_PRESENTATION_LOW_LIMIT === $n->nodeName) {
                $type->setPresentationLowLimit(FHIRRatio::xmlUnserialize($n));
            } elseif (self::FIELD_CONCENTRATION === $n->nodeName) {
                $type->setConcentration(FHIRRatio::xmlUnserialize($n));
            } elseif (self::FIELD_CONCENTRATION_LOW_LIMIT === $n->nodeName) {
                $type->setConcentrationLowLimit(FHIRRatio::xmlUnserialize($n));
            } elseif (self::FIELD_MEASUREMENT_POINT === $n->nodeName) {
                $type->setMeasurementPoint(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_COUNTRY === $n->nodeName) {
                $type->addCountry(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_REFERENCE_STRENGTH === $n->nodeName) {
                $type->addReferenceStrength(FHIRMedicinalProductIngredientReferenceStrength::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_MEASUREMENT_POINT);
        if (null !== $n) {
            $pt = $type->getMeasurementPoint();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setMeasurementPoint($n->nodeValue);
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
        if (null !== ($v = $this->getPresentation())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PRESENTATION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getPresentationLowLimit())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_PRESENTATION_LOW_LIMIT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getConcentration())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CONCENTRATION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getConcentrationLowLimit())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CONCENTRATION_LOW_LIMIT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getMeasurementPoint())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_MEASUREMENT_POINT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getCountry())) {
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_COUNTRY);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getReferenceStrength())) {
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_REFERENCE_STRENGTH);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if (null !== ($v = $this->getPresentation())) {
            $a[self::FIELD_PRESENTATION] = $v;
        }
        if (null !== ($v = $this->getPresentationLowLimit())) {
            $a[self::FIELD_PRESENTATION_LOW_LIMIT] = $v;
        }
        if (null !== ($v = $this->getConcentration())) {
            $a[self::FIELD_CONCENTRATION] = $v;
        }
        if (null !== ($v = $this->getConcentrationLowLimit())) {
            $a[self::FIELD_CONCENTRATION_LOW_LIMIT] = $v;
        }
        if (null !== ($v = $this->getMeasurementPoint())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_MEASUREMENT_POINT] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_MEASUREMENT_POINT_EXT] = $ext;
            }
        }
        if ([] !== ($vs = $this->getCountry())) {
            $a[self::FIELD_COUNTRY] = [];
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_COUNTRY][] = $v;
            }
        }
        if ([] !== ($vs = $this->getReferenceStrength())) {
            $a[self::FIELD_REFERENCE_STRENGTH] = [];
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_REFERENCE_STRENGTH][] = $v;
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