<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRStructureMap;

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
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapContextType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapTargetListMode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapTransform;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * A Map of relationships between 2 structures that can be used to transform data.
 *
 * Class FHIRStructureMapTarget
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRStructureMap
 */
class FHIRStructureMapTarget extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET;
    const FIELD_CONTEXT = 'context';
    const FIELD_CONTEXT_EXT = '_context';
    const FIELD_CONTEXT_TYPE = 'contextType';
    const FIELD_CONTEXT_TYPE_EXT = '_contextType';
    const FIELD_ELEMENT = 'element';
    const FIELD_ELEMENT_EXT = '_element';
    const FIELD_VARIABLE = 'variable';
    const FIELD_VARIABLE_EXT = '_variable';
    const FIELD_LIST_MODE = 'listMode';
    const FIELD_LIST_MODE_EXT = '_listMode';
    const FIELD_LIST_RULE_ID = 'listRuleId';
    const FIELD_LIST_RULE_ID_EXT = '_listRuleId';
    const FIELD_TRANSFORM = 'transform';
    const FIELD_TRANSFORM_EXT = '_transform';
    const FIELD_PARAMETER = 'parameter';

    /** @var string */
    private $_xmlns = '';

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Type or variable this rule applies to.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    protected $context = null;

    /**
     * How to interpret the context.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How to interpret the context.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapContextType
     */
    protected $contextType = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Field to create in the context.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $element = null;

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Named context for field, if desired, and a field is specified.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    protected $variable = null;

    /**
     * If field is a list, how to manage the production.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If field is a list, how to manage the list.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapTargetListMode[]
     */
    protected $listMode = [];

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Internal rule reference for shared list items.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    protected $listRuleId = null;

    /**
     * How data is copied/created.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the data is copied / created.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapTransform
     */
    protected $transform = null;

    /**
     * A Map of relationships between 2 structures that can be used to transform data.
     *
     * Parameters to the transform.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter[]
     */
    protected $parameter = [];

    /**
     * Validation map for fields in type StructureMap.Target
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRStructureMapTarget Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRStructureMapTarget::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_CONTEXT]) || isset($data[self::FIELD_CONTEXT_EXT])) {
            $value = isset($data[self::FIELD_CONTEXT]) ? $data[self::FIELD_CONTEXT] : null;
            $ext = (isset($data[self::FIELD_CONTEXT_EXT]) && is_array($data[self::FIELD_CONTEXT_EXT])) ? $ext = $data[self::FIELD_CONTEXT_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRId) {
                    $this->setContext($value);
                } else if (is_array($value)) {
                    $this->setContext(new FHIRId(array_merge($ext, $value)));
                } else {
                    $this->setContext(new FHIRId([FHIRId::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setContext(new FHIRId($ext));
            }
        }
        if (isset($data[self::FIELD_CONTEXT_TYPE]) || isset($data[self::FIELD_CONTEXT_TYPE_EXT])) {
            $value = isset($data[self::FIELD_CONTEXT_TYPE]) ? $data[self::FIELD_CONTEXT_TYPE] : null;
            $ext = (isset($data[self::FIELD_CONTEXT_TYPE_EXT]) && is_array($data[self::FIELD_CONTEXT_TYPE_EXT])) ? $ext = $data[self::FIELD_CONTEXT_TYPE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRStructureMapContextType) {
                    $this->setContextType($value);
                } else if (is_array($value)) {
                    $this->setContextType(new FHIRStructureMapContextType(array_merge($ext, $value)));
                } else {
                    $this->setContextType(new FHIRStructureMapContextType([FHIRStructureMapContextType::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setContextType(new FHIRStructureMapContextType($ext));
            }
        }
        if (isset($data[self::FIELD_ELEMENT]) || isset($data[self::FIELD_ELEMENT_EXT])) {
            $value = isset($data[self::FIELD_ELEMENT]) ? $data[self::FIELD_ELEMENT] : null;
            $ext = (isset($data[self::FIELD_ELEMENT_EXT]) && is_array($data[self::FIELD_ELEMENT_EXT])) ? $ext = $data[self::FIELD_ELEMENT_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setElement($value);
                } else if (is_array($value)) {
                    $this->setElement(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setElement(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setElement(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_VARIABLE]) || isset($data[self::FIELD_VARIABLE_EXT])) {
            $value = isset($data[self::FIELD_VARIABLE]) ? $data[self::FIELD_VARIABLE] : null;
            $ext = (isset($data[self::FIELD_VARIABLE_EXT]) && is_array($data[self::FIELD_VARIABLE_EXT])) ? $ext = $data[self::FIELD_VARIABLE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRId) {
                    $this->setVariable($value);
                } else if (is_array($value)) {
                    $this->setVariable(new FHIRId(array_merge($ext, $value)));
                } else {
                    $this->setVariable(new FHIRId([FHIRId::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setVariable(new FHIRId($ext));
            }
        }
        if (isset($data[self::FIELD_LIST_MODE]) || isset($data[self::FIELD_LIST_MODE_EXT])) {
            $value = isset($data[self::FIELD_LIST_MODE]) ? $data[self::FIELD_LIST_MODE] : null;
            $ext = (isset($data[self::FIELD_LIST_MODE_EXT]) && is_array($data[self::FIELD_LIST_MODE_EXT])) ? $ext = $data[self::FIELD_LIST_MODE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRStructureMapTargetListMode) {
                    $this->addListMode($value);
                } else if (is_array($value)) {
                    foreach($value as $i => $v) {
                        if ($v instanceof FHIRStructureMapTargetListMode) {
                            $this->addListMode($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addListMode(new FHIRStructureMapTargetListMode(array_merge($v, $iext)));
                            } else {
                                $this->addListMode(new FHIRStructureMapTargetListMode([FHIRStructureMapTargetListMode::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addListMode(new FHIRStructureMapTargetListMode(array_merge($ext, $value)));
                } else {
                    $this->addListMode(new FHIRStructureMapTargetListMode([FHIRStructureMapTargetListMode::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach($ext as $iext) {
                    $this->addListMode(new FHIRStructureMapTargetListMode($iext));
                }
            }
        }
        if (isset($data[self::FIELD_LIST_RULE_ID]) || isset($data[self::FIELD_LIST_RULE_ID_EXT])) {
            $value = isset($data[self::FIELD_LIST_RULE_ID]) ? $data[self::FIELD_LIST_RULE_ID] : null;
            $ext = (isset($data[self::FIELD_LIST_RULE_ID_EXT]) && is_array($data[self::FIELD_LIST_RULE_ID_EXT])) ? $ext = $data[self::FIELD_LIST_RULE_ID_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRId) {
                    $this->setListRuleId($value);
                } else if (is_array($value)) {
                    $this->setListRuleId(new FHIRId(array_merge($ext, $value)));
                } else {
                    $this->setListRuleId(new FHIRId([FHIRId::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setListRuleId(new FHIRId($ext));
            }
        }
        if (isset($data[self::FIELD_TRANSFORM]) || isset($data[self::FIELD_TRANSFORM_EXT])) {
            $value = isset($data[self::FIELD_TRANSFORM]) ? $data[self::FIELD_TRANSFORM] : null;
            $ext = (isset($data[self::FIELD_TRANSFORM_EXT]) && is_array($data[self::FIELD_TRANSFORM_EXT])) ? $ext = $data[self::FIELD_TRANSFORM_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRStructureMapTransform) {
                    $this->setTransform($value);
                } else if (is_array($value)) {
                    $this->setTransform(new FHIRStructureMapTransform(array_merge($ext, $value)));
                } else {
                    $this->setTransform(new FHIRStructureMapTransform([FHIRStructureMapTransform::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setTransform(new FHIRStructureMapTransform($ext));
            }
        }
        if (isset($data[self::FIELD_PARAMETER])) {
            if (is_array($data[self::FIELD_PARAMETER])) {
                foreach($data[self::FIELD_PARAMETER] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRStructureMapParameter) {
                        $this->addParameter($v);
                    } else {
                        $this->addParameter(new FHIRStructureMapParameter($v));
                    }
                }
            } elseif ($data[self::FIELD_PARAMETER] instanceof FHIRStructureMapParameter) {
                $this->addParameter($data[self::FIELD_PARAMETER]);
            } else {
                $this->addParameter(new FHIRStructureMapParameter($data[self::FIELD_PARAMETER]));
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
        return "<StructureMapTarget{$xmlns}></StructureMapTarget>";
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Type or variable this rule applies to.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Type or variable this rule applies to.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRId $context
     * @return static
     */
    public function setContext($context = null)
    {
        if (null !== $context && !($context instanceof FHIRId)) {
            $context = new FHIRId($context);
        }
        $this->_trackValueSet($this->context, $context);
        $this->context = $context;
        return $this;
    }

    /**
     * How to interpret the context.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How to interpret the context.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapContextType
     */
    public function getContextType()
    {
        return $this->contextType;
    }

    /**
     * How to interpret the context.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How to interpret the context.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapContextType $contextType
     * @return static
     */
    public function setContextType(FHIRStructureMapContextType $contextType = null)
    {
        $this->_trackValueSet($this->contextType, $contextType);
        $this->contextType = $contextType;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Field to create in the context.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Field to create in the context.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $element
     * @return static
     */
    public function setElement($element = null)
    {
        if (null !== $element && !($element instanceof FHIRString)) {
            $element = new FHIRString($element);
        }
        $this->_trackValueSet($this->element, $element);
        $this->element = $element;
        return $this;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Named context for field, if desired, and a field is specified.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Named context for field, if desired, and a field is specified.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRId $variable
     * @return static
     */
    public function setVariable($variable = null)
    {
        if (null !== $variable && !($variable instanceof FHIRId)) {
            $variable = new FHIRId($variable);
        }
        $this->_trackValueSet($this->variable, $variable);
        $this->variable = $variable;
        return $this;
    }

    /**
     * If field is a list, how to manage the production.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If field is a list, how to manage the list.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapTargetListMode[]
     */
    public function getListMode()
    {
        return $this->listMode;
    }

    /**
     * If field is a list, how to manage the production.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If field is a list, how to manage the list.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapTargetListMode $listMode
     * @return static
     */
    public function addListMode(FHIRStructureMapTargetListMode $listMode = null)
    {
        $this->_trackValueAdded();
        $this->listMode[] = $listMode;
        return $this;
    }

    /**
     * If field is a list, how to manage the production.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If field is a list, how to manage the list.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapTargetListMode[] $listMode
     * @return static
     */
    public function setListMode(array $listMode = [])
    {
        if ([] !== $this->listMode) {
            $this->_trackValuesRemoved(count($this->listMode));
            $this->listMode = [];
        }
        if ([] === $listMode) {
            return $this;
        }
        foreach($listMode as $v) {
            if ($v instanceof FHIRStructureMapTargetListMode) {
                $this->addListMode($v);
            } else {
                $this->addListMode(new FHIRStructureMapTargetListMode($v));
            }
        }
        return $this;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Internal rule reference for shared list items.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getListRuleId()
    {
        return $this->listRuleId;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Internal rule reference for shared list items.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRId $listRuleId
     * @return static
     */
    public function setListRuleId($listRuleId = null)
    {
        if (null !== $listRuleId && !($listRuleId instanceof FHIRId)) {
            $listRuleId = new FHIRId($listRuleId);
        }
        $this->_trackValueSet($this->listRuleId, $listRuleId);
        $this->listRuleId = $listRuleId;
        return $this;
    }

    /**
     * How data is copied/created.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the data is copied / created.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapTransform
     */
    public function getTransform()
    {
        return $this->transform;
    }

    /**
     * How data is copied/created.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the data is copied / created.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapTransform $transform
     * @return static
     */
    public function setTransform(FHIRStructureMapTransform $transform = null)
    {
        $this->_trackValueSet($this->transform, $transform);
        $this->transform = $transform;
        return $this;
    }

    /**
     * A Map of relationships between 2 structures that can be used to transform data.
     *
     * Parameters to the transform.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter[]
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * A Map of relationships between 2 structures that can be used to transform data.
     *
     * Parameters to the transform.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter $parameter
     * @return static
     */
    public function addParameter(FHIRStructureMapParameter $parameter = null)
    {
        $this->_trackValueAdded();
        $this->parameter[] = $parameter;
        return $this;
    }

    /**
     * A Map of relationships between 2 structures that can be used to transform data.
     *
     * Parameters to the transform.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter[] $parameter
     * @return static
     */
    public function setParameter(array $parameter = [])
    {
        if ([] !== $this->parameter) {
            $this->_trackValuesRemoved(count($this->parameter));
            $this->parameter = [];
        }
        if ([] === $parameter) {
            return $this;
        }
        foreach($parameter as $v) {
            if ($v instanceof FHIRStructureMapParameter) {
                $this->addParameter($v);
            } else {
                $this->addParameter(new FHIRStructureMapParameter($v));
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
        if (null !== ($v = $this->getContext())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CONTEXT] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getContextType())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CONTEXT_TYPE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getElement())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ELEMENT] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getVariable())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_VARIABLE] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getListMode())) {
            foreach($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_LIST_MODE, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getListRuleId())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_LIST_RULE_ID] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getTransform())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TRANSFORM] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getParameter())) {
            foreach($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_PARAMETER, $i)] = $fieldErrs;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTEXT])) {
            $v = $this->getContext();
            foreach($validationRules[self::FIELD_CONTEXT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET, self::FIELD_CONTEXT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTEXT])) {
                        $errs[self::FIELD_CONTEXT] = [];
                    }
                    $errs[self::FIELD_CONTEXT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTEXT_TYPE])) {
            $v = $this->getContextType();
            foreach($validationRules[self::FIELD_CONTEXT_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET, self::FIELD_CONTEXT_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTEXT_TYPE])) {
                        $errs[self::FIELD_CONTEXT_TYPE] = [];
                    }
                    $errs[self::FIELD_CONTEXT_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ELEMENT])) {
            $v = $this->getElement();
            foreach($validationRules[self::FIELD_ELEMENT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET, self::FIELD_ELEMENT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ELEMENT])) {
                        $errs[self::FIELD_ELEMENT] = [];
                    }
                    $errs[self::FIELD_ELEMENT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_VARIABLE])) {
            $v = $this->getVariable();
            foreach($validationRules[self::FIELD_VARIABLE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET, self::FIELD_VARIABLE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_VARIABLE])) {
                        $errs[self::FIELD_VARIABLE] = [];
                    }
                    $errs[self::FIELD_VARIABLE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LIST_MODE])) {
            $v = $this->getListMode();
            foreach($validationRules[self::FIELD_LIST_MODE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET, self::FIELD_LIST_MODE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LIST_MODE])) {
                        $errs[self::FIELD_LIST_MODE] = [];
                    }
                    $errs[self::FIELD_LIST_MODE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LIST_RULE_ID])) {
            $v = $this->getListRuleId();
            foreach($validationRules[self::FIELD_LIST_RULE_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET, self::FIELD_LIST_RULE_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LIST_RULE_ID])) {
                        $errs[self::FIELD_LIST_RULE_ID] = [];
                    }
                    $errs[self::FIELD_LIST_RULE_ID][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TRANSFORM])) {
            $v = $this->getTransform();
            foreach($validationRules[self::FIELD_TRANSFORM] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET, self::FIELD_TRANSFORM, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TRANSFORM])) {
                        $errs[self::FIELD_TRANSFORM] = [];
                    }
                    $errs[self::FIELD_TRANSFORM][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARAMETER])) {
            $v = $this->getParameter();
            foreach($validationRules[self::FIELD_PARAMETER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET, self::FIELD_PARAMETER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARAMETER])) {
                        $errs[self::FIELD_PARAMETER] = [];
                    }
                    $errs[self::FIELD_PARAMETER][$rule] = $err;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapTarget $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapTarget
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
                throw new \DomainException(sprintf('FHIRStructureMapTarget::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRStructureMapTarget::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRStructureMapTarget(null);
        } elseif (!is_object($type) || !($type instanceof FHIRStructureMapTarget)) {
            throw new \RuntimeException(sprintf(
                'FHIRStructureMapTarget::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapTarget or null, %s seen.',
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
            if (self::FIELD_CONTEXT === $n->nodeName) {
                $type->setContext(FHIRId::xmlUnserialize($n));
            } elseif (self::FIELD_CONTEXT_TYPE === $n->nodeName) {
                $type->setContextType(FHIRStructureMapContextType::xmlUnserialize($n));
            } elseif (self::FIELD_ELEMENT === $n->nodeName) {
                $type->setElement(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_VARIABLE === $n->nodeName) {
                $type->setVariable(FHIRId::xmlUnserialize($n));
            } elseif (self::FIELD_LIST_MODE === $n->nodeName) {
                $type->addListMode(FHIRStructureMapTargetListMode::xmlUnserialize($n));
            } elseif (self::FIELD_LIST_RULE_ID === $n->nodeName) {
                $type->setListRuleId(FHIRId::xmlUnserialize($n));
            } elseif (self::FIELD_TRANSFORM === $n->nodeName) {
                $type->setTransform(FHIRStructureMapTransform::xmlUnserialize($n));
            } elseif (self::FIELD_PARAMETER === $n->nodeName) {
                $type->addParameter(FHIRStructureMapParameter::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_CONTEXT);
        if (null !== $n) {
            $pt = $type->getContext();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setContext($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ELEMENT);
        if (null !== $n) {
            $pt = $type->getElement();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setElement($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_VARIABLE);
        if (null !== $n) {
            $pt = $type->getVariable();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setVariable($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_LIST_RULE_ID);
        if (null !== $n) {
            $pt = $type->getListRuleId();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setListRuleId($n->nodeValue);
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
        if (null !== ($v = $this->getContext())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CONTEXT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getContextType())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CONTEXT_TYPE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getElement())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ELEMENT);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getVariable())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_VARIABLE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getListMode())) {
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_LIST_MODE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getListRuleId())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_LIST_RULE_ID);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getTransform())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_TRANSFORM);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getParameter())) {
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_PARAMETER);
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
        if (null !== ($v = $this->getContext())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_CONTEXT] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRId::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_CONTEXT_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getContextType())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_CONTEXT_TYPE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRStructureMapContextType::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_CONTEXT_TYPE_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getElement())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_ELEMENT] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_ELEMENT_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getVariable())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_VARIABLE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRId::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_VARIABLE_EXT] = $ext;
            }
        }
        if ([] !== ($vs = $this->getListMode())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRStructureMapTargetListMode::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_LIST_MODE] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_LIST_MODE_EXT] = $exts;
            }
        }
        if (null !== ($v = $this->getListRuleId())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_LIST_RULE_ID] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRId::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_LIST_RULE_ID_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getTransform())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_TRANSFORM] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRStructureMapTransform::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_TRANSFORM_EXT] = $ext;
            }
        }
        if ([] !== ($vs = $this->getParameter())) {
            $a[self::FIELD_PARAMETER] = [];
            foreach($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_PARAMETER][] = $v;
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