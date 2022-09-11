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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeMap;

/**
 * A kind of specimen with associated set of requirements.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 *
 * Class FHIRSpecimenDefinition
 * @package \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource
 */
class FHIRSpecimenDefinition extends FHIRDomainResource implements PHPFHIRContainedTypeInterface
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_SPECIMEN_DEFINITION;
    const FIELD_IDENTIFIER = 'identifier';
    const FIELD_TYPE_COLLECTED = 'typeCollected';
    const FIELD_PATIENT_PREPARATION = 'patientPreparation';
    const FIELD_TIME_ASPECT = 'timeAspect';
    const FIELD_TIME_ASPECT_EXT = '_timeAspect';
    const FIELD_COLLECTION = 'collection';
    const FIELD_TYPE_TESTED = 'typeTested';

    /** @var string */
    private $_xmlns = '';

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A business identifier associated with the kind of specimen.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    protected $identifier = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of material to be collected.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $typeCollected = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Preparation of the patient for specimen collection.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    protected $patientPreparation = [];

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time aspect of specimen collection (duration or offset).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $timeAspect = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The action to be performed for collecting the specimen.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    protected $collection = [];

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Specimen conditioned in a container as expected by the testing laboratory.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested[]
     */
    protected $typeTested = [];

    /**
     * Validation map for fields in type SpecimenDefinition
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRSpecimenDefinition Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRSpecimenDefinition::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_IDENTIFIER])) {
            if ($data[self::FIELD_IDENTIFIER] instanceof FHIRIdentifier) {
                $this->setIdentifier($data[self::FIELD_IDENTIFIER]);
            } else {
                $this->setIdentifier(new FHIRIdentifier($data[self::FIELD_IDENTIFIER]));
            }
        }
        if (isset($data[self::FIELD_TYPE_COLLECTED])) {
            if ($data[self::FIELD_TYPE_COLLECTED] instanceof FHIRCodeableConcept) {
                $this->setTypeCollected($data[self::FIELD_TYPE_COLLECTED]);
            } else {
                $this->setTypeCollected(new FHIRCodeableConcept($data[self::FIELD_TYPE_COLLECTED]));
            }
        }
        if (isset($data[self::FIELD_PATIENT_PREPARATION])) {
            if (is_array($data[self::FIELD_PATIENT_PREPARATION])) {
                foreach ($data[self::FIELD_PATIENT_PREPARATION] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRCodeableConcept) {
                        $this->addPatientPreparation($v);
                    } else {
                        $this->addPatientPreparation(new FHIRCodeableConcept($v));
                    }
                }
            } elseif ($data[self::FIELD_PATIENT_PREPARATION] instanceof FHIRCodeableConcept) {
                $this->addPatientPreparation($data[self::FIELD_PATIENT_PREPARATION]);
            } else {
                $this->addPatientPreparation(new FHIRCodeableConcept($data[self::FIELD_PATIENT_PREPARATION]));
            }
        }
        if (isset($data[self::FIELD_TIME_ASPECT]) || isset($data[self::FIELD_TIME_ASPECT_EXT])) {
            $value = isset($data[self::FIELD_TIME_ASPECT]) ? $data[self::FIELD_TIME_ASPECT] : null;
            $ext = (isset($data[self::FIELD_TIME_ASPECT_EXT]) && is_array($data[self::FIELD_TIME_ASPECT_EXT])) ? $ext = $data[self::FIELD_TIME_ASPECT_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setTimeAspect($value);
                } else if (is_array($value)) {
                    $this->setTimeAspect(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setTimeAspect(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setTimeAspect(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_COLLECTION])) {
            if (is_array($data[self::FIELD_COLLECTION])) {
                foreach ($data[self::FIELD_COLLECTION] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRCodeableConcept) {
                        $this->addCollection($v);
                    } else {
                        $this->addCollection(new FHIRCodeableConcept($v));
                    }
                }
            } elseif ($data[self::FIELD_COLLECTION] instanceof FHIRCodeableConcept) {
                $this->addCollection($data[self::FIELD_COLLECTION]);
            } else {
                $this->addCollection(new FHIRCodeableConcept($data[self::FIELD_COLLECTION]));
            }
        }
        if (isset($data[self::FIELD_TYPE_TESTED])) {
            if (is_array($data[self::FIELD_TYPE_TESTED])) {
                foreach ($data[self::FIELD_TYPE_TESTED] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRSpecimenDefinitionTypeTested) {
                        $this->addTypeTested($v);
                    } else {
                        $this->addTypeTested(new FHIRSpecimenDefinitionTypeTested($v));
                    }
                }
            } elseif ($data[self::FIELD_TYPE_TESTED] instanceof FHIRSpecimenDefinitionTypeTested) {
                $this->addTypeTested($data[self::FIELD_TYPE_TESTED]);
            } else {
                $this->addTypeTested(new FHIRSpecimenDefinitionTypeTested($data[self::FIELD_TYPE_TESTED]));
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
        return "<SpecimenDefinition{$xmlns}></SpecimenDefinition>";
    }
    /**
     * @return string
     */
    public function _getResourceType()
    {
        return static::FHIR_TYPE_NAME;
    }


    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A business identifier associated with the kind of specimen.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
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
     * A business identifier associated with the kind of specimen.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function setIdentifier(FHIRIdentifier $identifier = null)
    {
        $this->_trackValueSet($this->identifier, $identifier);
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of material to be collected.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getTypeCollected()
    {
        return $this->typeCollected;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of material to be collected.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $typeCollected
     * @return static
     */
    public function setTypeCollected(FHIRCodeableConcept $typeCollected = null)
    {
        $this->_trackValueSet($this->typeCollected, $typeCollected);
        $this->typeCollected = $typeCollected;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Preparation of the patient for specimen collection.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getPatientPreparation()
    {
        return $this->patientPreparation;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Preparation of the patient for specimen collection.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $patientPreparation
     * @return static
     */
    public function addPatientPreparation(FHIRCodeableConcept $patientPreparation = null)
    {
        $this->_trackValueAdded();
        $this->patientPreparation[] = $patientPreparation;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Preparation of the patient for specimen collection.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[] $patientPreparation
     * @return static
     */
    public function setPatientPreparation(array $patientPreparation = [])
    {
        if ([] !== $this->patientPreparation) {
            $this->_trackValuesRemoved(count($this->patientPreparation));
            $this->patientPreparation = [];
        }
        if ([] === $patientPreparation) {
            return $this;
        }
        foreach ($patientPreparation as $v) {
            if ($v instanceof FHIRCodeableConcept) {
                $this->addPatientPreparation($v);
            } else {
                $this->addPatientPreparation(new FHIRCodeableConcept($v));
            }
        }
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time aspect of specimen collection (duration or offset).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTimeAspect()
    {
        return $this->timeAspect;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Time aspect of specimen collection (duration or offset).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $timeAspect
     * @return static
     */
    public function setTimeAspect($timeAspect = null)
    {
        if (null !== $timeAspect && !($timeAspect instanceof FHIRString)) {
            $timeAspect = new FHIRString($timeAspect);
        }
        $this->_trackValueSet($this->timeAspect, $timeAspect);
        $this->timeAspect = $timeAspect;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The action to be performed for collecting the specimen.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The action to be performed for collecting the specimen.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $collection
     * @return static
     */
    public function addCollection(FHIRCodeableConcept $collection = null)
    {
        $this->_trackValueAdded();
        $this->collection[] = $collection;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The action to be performed for collecting the specimen.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[] $collection
     * @return static
     */
    public function setCollection(array $collection = [])
    {
        if ([] !== $this->collection) {
            $this->_trackValuesRemoved(count($this->collection));
            $this->collection = [];
        }
        if ([] === $collection) {
            return $this;
        }
        foreach ($collection as $v) {
            if ($v instanceof FHIRCodeableConcept) {
                $this->addCollection($v);
            } else {
                $this->addCollection(new FHIRCodeableConcept($v));
            }
        }
        return $this;
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Specimen conditioned in a container as expected by the testing laboratory.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested[]
     */
    public function getTypeTested()
    {
        return $this->typeTested;
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Specimen conditioned in a container as expected by the testing laboratory.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested $typeTested
     * @return static
     */
    public function addTypeTested(FHIRSpecimenDefinitionTypeTested $typeTested = null)
    {
        $this->_trackValueAdded();
        $this->typeTested[] = $typeTested;
        return $this;
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Specimen conditioned in a container as expected by the testing laboratory.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested[] $typeTested
     * @return static
     */
    public function setTypeTested(array $typeTested = [])
    {
        if ([] !== $this->typeTested) {
            $this->_trackValuesRemoved(count($this->typeTested));
            $this->typeTested = [];
        }
        if ([] === $typeTested) {
            return $this;
        }
        foreach ($typeTested as $v) {
            if ($v instanceof FHIRSpecimenDefinitionTypeTested) {
                $this->addTypeTested($v);
            } else {
                $this->addTypeTested(new FHIRSpecimenDefinitionTypeTested($v));
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
        if (null !== ($v = $this->getIdentifier())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_IDENTIFIER] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getTypeCollected())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TYPE_COLLECTED] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getPatientPreparation())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_PATIENT_PREPARATION, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getTimeAspect())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TIME_ASPECT] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getCollection())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_COLLECTION, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getTypeTested())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_TYPE_TESTED, $i)] = $fieldErrs;
                }
            }
        }
        if (isset($validationRules[self::FIELD_IDENTIFIER])) {
            $v = $this->getIdentifier();
            foreach ($validationRules[self::FIELD_IDENTIFIER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SPECIMEN_DEFINITION, self::FIELD_IDENTIFIER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_IDENTIFIER])) {
                        $errs[self::FIELD_IDENTIFIER] = [];
                    }
                    $errs[self::FIELD_IDENTIFIER][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TYPE_COLLECTED])) {
            $v = $this->getTypeCollected();
            foreach ($validationRules[self::FIELD_TYPE_COLLECTED] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SPECIMEN_DEFINITION, self::FIELD_TYPE_COLLECTED, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TYPE_COLLECTED])) {
                        $errs[self::FIELD_TYPE_COLLECTED] = [];
                    }
                    $errs[self::FIELD_TYPE_COLLECTED][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PATIENT_PREPARATION])) {
            $v = $this->getPatientPreparation();
            foreach ($validationRules[self::FIELD_PATIENT_PREPARATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SPECIMEN_DEFINITION, self::FIELD_PATIENT_PREPARATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PATIENT_PREPARATION])) {
                        $errs[self::FIELD_PATIENT_PREPARATION] = [];
                    }
                    $errs[self::FIELD_PATIENT_PREPARATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TIME_ASPECT])) {
            $v = $this->getTimeAspect();
            foreach ($validationRules[self::FIELD_TIME_ASPECT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SPECIMEN_DEFINITION, self::FIELD_TIME_ASPECT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TIME_ASPECT])) {
                        $errs[self::FIELD_TIME_ASPECT] = [];
                    }
                    $errs[self::FIELD_TIME_ASPECT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_COLLECTION])) {
            $v = $this->getCollection();
            foreach ($validationRules[self::FIELD_COLLECTION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SPECIMEN_DEFINITION, self::FIELD_COLLECTION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_COLLECTION])) {
                        $errs[self::FIELD_COLLECTION] = [];
                    }
                    $errs[self::FIELD_COLLECTION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TYPE_TESTED])) {
            $v = $this->getTypeTested();
            foreach ($validationRules[self::FIELD_TYPE_TESTED] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SPECIMEN_DEFINITION, self::FIELD_TYPE_TESTED, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TYPE_TESTED])) {
                        $errs[self::FIELD_TYPE_TESTED] = [];
                    }
                    $errs[self::FIELD_TYPE_TESTED][$rule] = $err;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRSpecimenDefinition $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRSpecimenDefinition
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
                throw new \DomainException(sprintf('FHIRSpecimenDefinition::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function (\libXMLError $err) {
                    return $err->message;
                }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRSpecimenDefinition::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRSpecimenDefinition(null);
        } elseif (!is_object($type) || !($type instanceof FHIRSpecimenDefinition)) {
            throw new \RuntimeException(sprintf(
                'FHIRSpecimenDefinition::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRSpecimenDefinition or null, %s seen.',
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
            if (self::FIELD_IDENTIFIER === $n->nodeName) {
                $type->setIdentifier(FHIRIdentifier::xmlUnserialize($n));
            } elseif (self::FIELD_TYPE_COLLECTED === $n->nodeName) {
                $type->setTypeCollected(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_PATIENT_PREPARATION === $n->nodeName) {
                $type->addPatientPreparation(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_TIME_ASPECT === $n->nodeName) {
                $type->setTimeAspect(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_COLLECTION === $n->nodeName) {
                $type->addCollection(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_TYPE_TESTED === $n->nodeName) {
                $type->addTypeTested(FHIRSpecimenDefinitionTypeTested::xmlUnserialize($n));
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
        $n = $element->attributes->getNamedItem(self::FIELD_TIME_ASPECT);
        if (null !== $n) {
            $pt = $type->getTimeAspect();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setTimeAspect($n->nodeValue);
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
        if (null !== ($v = $this->getIdentifier())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_IDENTIFIER);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getTypeCollected())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TYPE_COLLECTED);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getPatientPreparation())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_PATIENT_PREPARATION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getTimeAspect())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TIME_ASPECT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getCollection())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_COLLECTION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getTypeTested())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_TYPE_TESTED);
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
        if (null !== ($v = $this->getIdentifier())) {
            $a[self::FIELD_IDENTIFIER] = $v;
        }
        if (null !== ($v = $this->getTypeCollected())) {
            $a[self::FIELD_TYPE_COLLECTED] = $v;
        }
        if ([] !== ($vs = $this->getPatientPreparation())) {
            $a[self::FIELD_PATIENT_PREPARATION] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_PATIENT_PREPARATION][] = $v;
            }
        }
        if (null !== ($v = $this->getTimeAspect())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_TIME_ASPECT] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_TIME_ASPECT_EXT] = $ext;
            }
        }
        if ([] !== ($vs = $this->getCollection())) {
            $a[self::FIELD_COLLECTION] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_COLLECTION][] = $v;
            }
        }
        if ([] !== ($vs = $this->getTypeTested())) {
            $a[self::FIELD_TYPE_TESTED] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_TYPE_TESTED][] = $v;
            }
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
