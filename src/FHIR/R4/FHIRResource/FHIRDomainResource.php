<?php

namespace OpenEMR\FHIR\R4\FHIRResource;

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

use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeMap;

/**
 * A resource that includes narrative, extensions, and contained resources.
 *
 * Class FHIRDomainResource
 * @package \OpenEMR\FHIR\R4\FHIRResource
 */
class FHIRDomainResource extends FHIRResource
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE;
    const FIELD_TEXT = 'text';
    const FIELD_CONTAINED = 'contained';
    const FIELD_EXTENSION = 'extension';
    const FIELD_MODIFIER_EXTENSION = 'modifierExtension';

    /** @var string */
    private $_xmlns = '';

    /**
     * A human-readable summary of the resource conveying the essential clinical and
     * business information for the resource.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A human-readable narrative that contains a summary of the resource and can be
     * used to represent the content of the resource to a human. The narrative need not
     * encode all the structured data, but is required to contain sufficient detail to
     * make it "clinically safe" for a human to just read the narrative. Resource
     * definitions may define what content should be represented in the narrative to
     * ensure clinical safety.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative
     */
    protected $text = null;

    /**
     * These resources do not have an independent existence apart from the resource
     * that contains them - they cannot be identified independently, and nor can they
     * have their own independent transaction scope.
     *
     * @var null|\OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface[]
     */
    protected $contained = [];

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the resource. To make the use of extensions safe and manageable,
     * there is a strict set of governance applied to the definition and use of
     * extensions. Though any implementer can define an extension, there is a set of
     * requirements that SHALL be met as part of the definition of the extension.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRExtension[]
     */
    protected $extension = [];

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the resource and that modifies the understanding of the element
     * that contains it and/or the understanding of the containing element's
     * descendants. Usually modifier elements provide negation or qualification. To
     * make the use of extensions safe and manageable, there is a strict set of
     * governance applied to the definition and use of extensions. Though any
     * implementer is allowed to define an extension, there is a set of requirements
     * that SHALL be met as part of the definition of the extension. Applications
     * processing a resource are required to check for modifier extensions. Modifier
     * extensions SHALL NOT change the meaning of any elements on Resource or
     * DomainResource (including cannot change the meaning of modifierExtension
     * itself).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRExtension[]
     */
    protected $modifierExtension = [];

    /**
     * Validation map for fields in type DomainResource
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRDomainResource Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRDomainResource::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_TEXT])) {
            if ($data[self::FIELD_TEXT] instanceof FHIRNarrative) {
                $this->setText($data[self::FIELD_TEXT]);
            } else {
                $this->setText(new FHIRNarrative($data[self::FIELD_TEXT]));
            }
        }
        if (isset($data[self::FIELD_CONTAINED])) {
            if (is_array($data[self::FIELD_CONTAINED])) {
                if (is_int(key($data[self::FIELD_CONTAINED]))) {
                    $this->setContained($data[self::FIELD_CONTAINED]);
                } else {
                    $typeClass = PHPFHIRTypeMap::getContainedTypeFromArray($data[self::FIELD_CONTAINED]);
                    if (null === $typeClass) {
                        throw new \InvalidArgumentException(sprintf(
                            'FHIRDomainResource - Unable to determine class for field "contained" from value: %s',
                            json_encode($data[self::FIELD_CONTAINED])
                        ));
                    }
                    $this->addContained(new $typeClass($data[self::FIELD_CONTAINED]));
                }
            } elseif ($data[self::FIELD_CONTAINED] instanceof PHPFHIRContainedTypeInterface) {
                $this->addContained($data[self::FIELD_CONTAINED]);
            }
        }
        if (isset($data[self::FIELD_EXTENSION])) {
            if (is_array($data[self::FIELD_EXTENSION])) {
                foreach ($data[self::FIELD_EXTENSION] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRExtension) {
                        $this->addExtension($v);
                    } else {
                        $this->addExtension(new FHIRExtension($v));
                    }
                }
            } elseif ($data[self::FIELD_EXTENSION] instanceof FHIRExtension) {
                $this->addExtension($data[self::FIELD_EXTENSION]);
            } else {
                $this->addExtension(new FHIRExtension($data[self::FIELD_EXTENSION]));
            }
        }
        if (isset($data[self::FIELD_MODIFIER_EXTENSION])) {
            if (is_array($data[self::FIELD_MODIFIER_EXTENSION])) {
                foreach ($data[self::FIELD_MODIFIER_EXTENSION] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRExtension) {
                        $this->addModifierExtension($v);
                    } else {
                        $this->addModifierExtension(new FHIRExtension($v));
                    }
                }
            } elseif ($data[self::FIELD_MODIFIER_EXTENSION] instanceof FHIRExtension) {
                $this->addModifierExtension($data[self::FIELD_MODIFIER_EXTENSION]);
            } else {
                $this->addModifierExtension(new FHIRExtension($data[self::FIELD_MODIFIER_EXTENSION]));
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
        return "<DomainResource{$xmlns}></DomainResource>";
    }

    /**
     * A human-readable summary of the resource conveying the essential clinical and
     * business information for the resource.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A human-readable narrative that contains a summary of the resource and can be
     * used to represent the content of the resource to a human. The narrative need not
     * encode all the structured data, but is required to contain sufficient detail to
     * make it "clinically safe" for a human to just read the narrative. Resource
     * definitions may define what content should be represented in the narrative to
     * ensure clinical safety.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * A human-readable summary of the resource conveying the essential clinical and
     * business information for the resource.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A human-readable narrative that contains a summary of the resource and can be
     * used to represent the content of the resource to a human. The narrative need not
     * encode all the structured data, but is required to contain sufficient detail to
     * make it "clinically safe" for a human to just read the narrative. Resource
     * definitions may define what content should be represented in the narrative to
     * ensure clinical safety.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative $text
     * @return static
     */
    public function setText(FHIRNarrative $text = null)
    {
        $this->_trackValueSet($this->text, $text);
        $this->text = $text;
        return $this;
    }

    /**
     * These resources do not have an independent existence apart from the resource
     * that contains them - they cannot be identified independently, and nor can they
     * have their own independent transaction scope.
     *
     * @return null|\OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface[]
     */
    public function getContained()
    {
        return $this->contained;
    }

    /**
     * These resources do not have an independent existence apart from the resource
     * that contains them - they cannot be identified independently, and nor can they
     * have their own independent transaction scope.
     *
     * @param null|\OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface $contained
     * @return static
     */
    public function addContained(PHPFHIRContainedTypeInterface $contained = null)
    {
        $this->_trackValueAdded();
        $this->contained[] = $contained;
        return $this;
    }

    /**
     * These resources do not have an independent existence apart from the resource
     * that contains them - they cannot be identified independently, and nor can they
     * have their own independent transaction scope.
     *
     * @param \OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface[] $contained
     * @return static
     */
    public function setContained(array $contained = [])
    {
        if ([] !== $this->contained) {
            $this->_trackValuesRemoved(count($this->contained));
            $this->contained = [];
        }
        if ([] === $contained) {
            return $this;
        }
        foreach ($contained as $v) {
            if (null === $v) {
                continue;
            }
            if (is_object($v)) {
                if ($v instanceof PHPFHIRContainedTypeInterface) {
                    $this->addContained($v);
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'FHIRDomainResource - Field "contained" must be an array of objects implementing PHPFHIRContainedTypeInterface, object of type %s seen',
                        get_class($v)
                    ));
                }
            } elseif (is_array($v)) {
                $typeClass = PHPFHIRTypeMap::getContainedTypeFromArray($v);
                if (null === $typeClass) {
                    throw new \InvalidArgumentException(sprintf(
                        'FHIRDomainResource - Unable to determine class for field "contained" from value: %s',
                        json_encode($v)
                    ));
                }
                $this->addContained(new $typeClass($v));
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'FHIRDomainResource - Unable to determine class for field "contained" from value: %s',
                    json_encode($v)
                ));
            }
        }
        return $this;
    }

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the resource. To make the use of extensions safe and manageable,
     * there is a strict set of governance applied to the definition and use of
     * extensions. Though any implementer can define an extension, there is a set of
     * requirements that SHALL be met as part of the definition of the extension.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRExtension[]
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the resource. To make the use of extensions safe and manageable,
     * there is a strict set of governance applied to the definition and use of
     * extensions. Though any implementer can define an extension, there is a set of
     * requirements that SHALL be met as part of the definition of the extension.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRExtension $extension
     * @return static
     */
    public function addExtension(FHIRExtension $extension = null)
    {
        $this->_trackValueAdded();
        $this->extension[] = $extension;
        return $this;
    }

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the resource. To make the use of extensions safe and manageable,
     * there is a strict set of governance applied to the definition and use of
     * extensions. Though any implementer can define an extension, there is a set of
     * requirements that SHALL be met as part of the definition of the extension.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRExtension[] $extension
     * @return static
     */
    public function setExtension(array $extension = [])
    {
        if ([] !== $this->extension) {
            $this->_trackValuesRemoved(count($this->extension));
            $this->extension = [];
        }
        if ([] === $extension) {
            return $this;
        }
        foreach ($extension as $v) {
            if ($v instanceof FHIRExtension) {
                $this->addExtension($v);
            } else {
                $this->addExtension(new FHIRExtension($v));
            }
        }
        return $this;
    }

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the resource and that modifies the understanding of the element
     * that contains it and/or the understanding of the containing element's
     * descendants. Usually modifier elements provide negation or qualification. To
     * make the use of extensions safe and manageable, there is a strict set of
     * governance applied to the definition and use of extensions. Though any
     * implementer is allowed to define an extension, there is a set of requirements
     * that SHALL be met as part of the definition of the extension. Applications
     * processing a resource are required to check for modifier extensions. Modifier
     * extensions SHALL NOT change the meaning of any elements on Resource or
     * DomainResource (including cannot change the meaning of modifierExtension
     * itself).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRExtension[]
     */
    public function getModifierExtension()
    {
        return $this->modifierExtension;
    }

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the resource and that modifies the understanding of the element
     * that contains it and/or the understanding of the containing element's
     * descendants. Usually modifier elements provide negation or qualification. To
     * make the use of extensions safe and manageable, there is a strict set of
     * governance applied to the definition and use of extensions. Though any
     * implementer is allowed to define an extension, there is a set of requirements
     * that SHALL be met as part of the definition of the extension. Applications
     * processing a resource are required to check for modifier extensions. Modifier
     * extensions SHALL NOT change the meaning of any elements on Resource or
     * DomainResource (including cannot change the meaning of modifierExtension
     * itself).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRExtension $modifierExtension
     * @return static
     */
    public function addModifierExtension(FHIRExtension $modifierExtension = null)
    {
        $this->_trackValueAdded();
        $this->modifierExtension[] = $modifierExtension;
        return $this;
    }

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the resource and that modifies the understanding of the element
     * that contains it and/or the understanding of the containing element's
     * descendants. Usually modifier elements provide negation or qualification. To
     * make the use of extensions safe and manageable, there is a strict set of
     * governance applied to the definition and use of extensions. Though any
     * implementer is allowed to define an extension, there is a set of requirements
     * that SHALL be met as part of the definition of the extension. Applications
     * processing a resource are required to check for modifier extensions. Modifier
     * extensions SHALL NOT change the meaning of any elements on Resource or
     * DomainResource (including cannot change the meaning of modifierExtension
     * itself).
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRExtension[] $modifierExtension
     * @return static
     */
    public function setModifierExtension(array $modifierExtension = [])
    {
        if ([] !== $this->modifierExtension) {
            $this->_trackValuesRemoved(count($this->modifierExtension));
            $this->modifierExtension = [];
        }
        if ([] === $modifierExtension) {
            return $this;
        }
        foreach ($modifierExtension as $v) {
            if ($v instanceof FHIRExtension) {
                $this->addModifierExtension($v);
            } else {
                $this->addModifierExtension(new FHIRExtension($v));
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
        if (null !== ($v = $this->getText())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TEXT] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getContained())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_CONTAINED, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getExtension())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_EXTENSION, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getModifierExtension())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_MODIFIER_EXTENSION, $i)] = $fieldErrs;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource
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
                throw new \DomainException(sprintf('FHIRDomainResource::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function (\libXMLError $err) {
                    return $err->message;
                }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRDomainResource::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRDomainResource(null);
        } elseif (!is_object($type) || !($type instanceof FHIRDomainResource)) {
            throw new \RuntimeException(sprintf(
                'FHIRDomainResource::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource or null, %s seen.',
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
            if (self::FIELD_TEXT === $n->nodeName) {
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
        if (null !== ($v = $this->getText())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TEXT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getContained())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $e2 = $element->ownerDocument->createElement(self::FIELD_CONTAINED);
                $element->appendChild($e2);
                $e3 = $element->ownerDocument->createElement($v->_getFHIRTypeName());
                $e2->appendChild($e3);
                $v->xmlSerialize($e3);
            }
        }        if ([] !== ($vs = $this->getExtension())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_EXTENSION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getModifierExtension())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_MODIFIER_EXTENSION);
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
        if (null !== ($v = $this->getText())) {
            $a[self::FIELD_TEXT] = $v;
        }
        if ([] !== ($vs = $this->getContained())) {
            $a[self::FIELD_CONTAINED] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_CONTAINED][] = $v;
            }
        }
        if ([] !== ($vs = $this->getExtension())) {
            $a[self::FIELD_EXTENSION] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_EXTENSION][] = $v;
            }
        }
        if ([] !== ($vs = $this->getModifierExtension())) {
            $a[self::FIELD_MODIFIER_EXTENSION] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_MODIFIER_EXTENSION][] = $v;
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
