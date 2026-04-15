<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap;

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
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapContextTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapTargetListModeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapTransformList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapContextType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTargetListMode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTransform;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A Map of relationships between 2 structures that can be used to transform data.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRStructureMapTarget extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_STRUCTURE_MAP_DOT_TARGET;

    /* class_default.php:56 */
    public const FIELD_CONTEXT = 'context';
    public const FIELD_CONTEXT_EXT = '_context';
    public const FIELD_CONTEXT_TYPE = 'contextType';
    public const FIELD_CONTEXT_TYPE_EXT = '_contextType';
    public const FIELD_ELEMENT = 'element';
    public const FIELD_ELEMENT_EXT = '_element';
    public const FIELD_VARIABLE = 'variable';
    public const FIELD_VARIABLE_EXT = '_variable';
    public const FIELD_LIST_MODE = 'listMode';
    public const FIELD_LIST_MODE_EXT = '_listMode';
    public const FIELD_LIST_RULE_ID = 'listRuleId';
    public const FIELD_LIST_RULE_ID_EXT = '_listRuleId';
    public const FIELD_TRANSFORM = 'transform';
    public const FIELD_TRANSFORM_EXT = '_transform';
    public const FIELD_PARAMETER = 'parameter';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_CONTEXT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CONTEXT_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ELEMENT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VARIABLE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LIST_RULE_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TRANSFORM => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $context;
    /**
     * How to interpret the context.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How to interpret the context.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapContextType
     */
    #[FHIRStructureMapContextType]
    protected FHIRStructureMapContextType $contextType;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Field to create in the context.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $element;
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $variable;
    /**
     * If field is a list, how to manage the production.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If field is a list, how to manage the list.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTargetListMode>
     */
    #[FHIRStructureMapTargetListMode]
    protected array $listMode;
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $listRuleId;
    /**
     * How data is copied/created.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the data is copied / created.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTransform
     */
    #[FHIRStructureMapTransform]
    protected FHIRStructureMapTransform $transform;
    /**
     * A Map of relationships between 2 structures that can be used to transform data.
     *
     * Parameters to the transform.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter>
     */
    #[FHIRStructureMapParameter]
    protected array $parameter;

    /* constructor.php:61 */
    /**
     * FHIRStructureMapTarget Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $context
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapContextTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapContextType $contextType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $element
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $variable
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapTargetListModeList>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTargetListMode> $listMode
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $listRuleId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapTransformList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTransform $transform
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter> $parameter
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRIdPrimitive|FHIRId $context = null,
                                null|string|FHIRStructureMapContextTypeList|FHIRStructureMapContextType $contextType = null,
                                null|string|FHIRStringPrimitive|FHIRString $element = null,
                                null|string|FHIRIdPrimitive|FHIRId $variable = null,
                                null|iterable $listMode = null,
                                null|string|FHIRIdPrimitive|FHIRId $listRuleId = null,
                                null|string|FHIRStructureMapTransformList|FHIRStructureMapTransform $transform = null,
                                null|iterable $parameter = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $context) {
            $this->setContext($context);
        }
        if (null !== $contextType) {
            $this->setContextType($contextType);
        }
        if (null !== $element) {
            $this->setElement($element);
        }
        if (null !== $variable) {
            $this->setVariable($variable);
        }
        if (null !== $listMode) {
            $this->setListMode(...$listMode);
        }
        if (null !== $listRuleId) {
            $this->setListRuleId($listRuleId);
        }
        if (null !== $transform) {
            $this->setTransform($transform);
        }
        if (null !== $parameter) {
            $this->setParameter(...$parameter);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getContext(): null|FHIRId
    {
        return $this->context ?? null;
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
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $context
     * @return static
     */
    public function setContext(null|string|FHIRIdPrimitive|FHIRId $context): self
    {
        if (null === $context) {
            unset($this->context);
            return $this;
        }
        if (!($context instanceof FHIRId)) {
            $context = new FHIRId(value: $context);
        }
        $this->context = $context;
        return $this;
    }

    /**
     * How to interpret the context.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How to interpret the context.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapContextType
     */
    public function getContextType(): null|FHIRStructureMapContextType
    {
        return $this->contextType ?? null;
    }

    /**
     * How to interpret the context.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How to interpret the context.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapContextTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapContextType $contextType
     * @return static
     */
    public function setContextType(null|string|FHIRStructureMapContextTypeList|FHIRStructureMapContextType $contextType): self
    {
        if (null === $contextType) {
            unset($this->contextType);
            return $this;
        }
        if (!($contextType instanceof FHIRStructureMapContextType)) {
            $contextType = new FHIRStructureMapContextType(value: $contextType);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getElement(): null|FHIRString
    {
        return $this->element ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Field to create in the context.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $element
     * @return static
     */
    public function setElement(null|string|FHIRStringPrimitive|FHIRString $element): self
    {
        if (null === $element) {
            unset($this->element);
            return $this;
        }
        if (!($element instanceof FHIRString)) {
            $element = new FHIRString(value: $element);
        }
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getVariable(): null|FHIRId
    {
        return $this->variable ?? null;
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
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $variable
     * @return static
     */
    public function setVariable(null|string|FHIRIdPrimitive|FHIRId $variable): self
    {
        if (null === $variable) {
            unset($this->variable);
            return $this;
        }
        if (!($variable instanceof FHIRId)) {
            $variable = new FHIRId(value: $variable);
        }
        $this->variable = $variable;
        return $this;
    }

    /**
     * If field is a list, how to manage the production.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If field is a list, how to manage the list.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTargetListMode>
     */
    public function getListMode(): array
    {
        return $this->listMode ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTargetListMode>
     */
    public function getListModeIterator(): iterable
    {
        if (!isset($this->listMode)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->listMode);
    }

    /**
     * If field is a list, how to manage the production.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If field is a list, how to manage the list.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapTargetListModeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTargetListMode $listMode
     * @return static
     */
    public function addListMode(string|FHIRStructureMapTargetListModeList|FHIRStructureMapTargetListMode $listMode): self
    {
        if (!($listMode instanceof FHIRStructureMapTargetListMode)) {
            $listMode = new FHIRStructureMapTargetListMode(value: $listMode);
        }
        if (!isset($this->listMode)) {
            $this->listMode = [];
        }
        $this->listMode[] = $listMode;
        return $this;
    }

    /**
     * If field is a list, how to manage the production.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If field is a list, how to manage the list.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapTargetListModeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTargetListMode ...$listMode
     * @return static
     */
    public function setListMode(string|FHIRStructureMapTargetListModeList|FHIRStructureMapTargetListMode ...$listMode): self
    {
        if ([] === $listMode) {
            unset($this->listMode);
            return $this;
        }
        $this->listMode = [];
        foreach($listMode as $v) {
            if ($v instanceof FHIRStructureMapTargetListMode) {
                $this->listMode[] = $v;
            } else {
                $this->listMode[] = new FHIRStructureMapTargetListMode(value: $v);
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getListRuleId(): null|FHIRId
    {
        return $this->listRuleId ?? null;
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
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $listRuleId
     * @return static
     */
    public function setListRuleId(null|string|FHIRIdPrimitive|FHIRId $listRuleId): self
    {
        if (null === $listRuleId) {
            unset($this->listRuleId);
            return $this;
        }
        if (!($listRuleId instanceof FHIRId)) {
            $listRuleId = new FHIRId(value: $listRuleId);
        }
        $this->listRuleId = $listRuleId;
        return $this;
    }

    /**
     * How data is copied/created.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the data is copied / created.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTransform
     */
    public function getTransform(): null|FHIRStructureMapTransform
    {
        return $this->transform ?? null;
    }

    /**
     * How data is copied/created.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the data is copied / created.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapTransformList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapTransform $transform
     * @return static
     */
    public function setTransform(null|string|FHIRStructureMapTransformList|FHIRStructureMapTransform $transform): self
    {
        if (null === $transform) {
            unset($this->transform);
            return $this;
        }
        if (!($transform instanceof FHIRStructureMapTransform)) {
            $transform = new FHIRStructureMapTransform(value: $transform);
        }
        $this->transform = $transform;
        return $this;
    }

    /**
     * A Map of relationships between 2 structures that can be used to transform data.
     *
     * Parameters to the transform.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter>
     */
    public function getParameter(): array
    {
        return $this->parameter ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter>
     */
    public function getParameterIterator(): iterable
    {
        if (!isset($this->parameter)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->parameter);
    }

    /**
     * A Map of relationships between 2 structures that can be used to transform data.
     *
     * Parameters to the transform.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter $parameter
     * @return static
     */
    public function addParameter(FHIRStructureMapParameter $parameter): self
    {
        if (!isset($this->parameter)) {
            $this->parameter = [];
        }
        $this->parameter[] = $parameter;
        return $this;
    }

    /**
     * A Map of relationships between 2 structures that can be used to transform data.
     *
     * Parameters to the transform.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapParameter ...$parameter
     * @return static
     */
    public function setParameter(FHIRStructureMapParameter ...$parameter): self
    {
        if ([] === $parameter) {
            unset($this->parameter);
            return $this;
        }
        $this->parameter = $parameter;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapTarget $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapTarget
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRStructureMapTarget)) {
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
            } else if (self::FIELD_CONTEXT === $cen) {
                $type->setContext(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTEXT_TYPE === $cen) {
                $type->setContextType(FHIRStructureMapContextType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ELEMENT === $cen) {
                $type->setElement(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VARIABLE === $cen) {
                $type->setVariable(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LIST_MODE === $cen) {
                $type->addListMode(FHIRStructureMapTargetListMode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LIST_RULE_ID === $cen) {
                $type->setListRuleId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TRANSFORM === $cen) {
                $type->setTransform(FHIRStructureMapTransform::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARAMETER === $cen) {
                $type->addParameter(FHIRStructureMapParameter::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONTEXT])) {
            if (isset($type->context)) {
                $type->context->setValue((string)$attributes[self::FIELD_CONTEXT]);
            } else {
                $type->setContext((string)$attributes[self::FIELD_CONTEXT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONTEXT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONTEXT_TYPE])) {
            if (isset($type->contextType)) {
                $type->contextType->setValue((string)$attributes[self::FIELD_CONTEXT_TYPE]);
            } else {
                $type->setContextType((string)$attributes[self::FIELD_CONTEXT_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONTEXT_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ELEMENT])) {
            if (isset($type->element)) {
                $type->element->setValue((string)$attributes[self::FIELD_ELEMENT]);
            } else {
                $type->setElement((string)$attributes[self::FIELD_ELEMENT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ELEMENT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VARIABLE])) {
            if (isset($type->variable)) {
                $type->variable->setValue((string)$attributes[self::FIELD_VARIABLE]);
            } else {
                $type->setVariable((string)$attributes[self::FIELD_VARIABLE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VARIABLE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LIST_RULE_ID])) {
            if (isset($type->listRuleId)) {
                $type->listRuleId->setValue((string)$attributes[self::FIELD_LIST_RULE_ID]);
            } else {
                $type->setListRuleId((string)$attributes[self::FIELD_LIST_RULE_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LIST_RULE_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TRANSFORM])) {
            if (isset($type->transform)) {
                $type->transform->setValue((string)$attributes[self::FIELD_TRANSFORM]);
            } else {
                $type->setTransform((string)$attributes[self::FIELD_TRANSFORM]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TRANSFORM, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->context) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONTEXT]) {
            $xw->writeAttribute(self::FIELD_CONTEXT, $this->context->_getValueAsString());
        }
        if (isset($this->contextType) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONTEXT_TYPE]) {
            $xw->writeAttribute(self::FIELD_CONTEXT_TYPE, $this->contextType->_getValueAsString());
        }
        if (isset($this->element) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ELEMENT]) {
            $xw->writeAttribute(self::FIELD_ELEMENT, $this->element->_getValueAsString());
        }
        if (isset($this->variable) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VARIABLE]) {
            $xw->writeAttribute(self::FIELD_VARIABLE, $this->variable->_getValueAsString());
        }
        if (isset($this->listRuleId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LIST_RULE_ID]) {
            $xw->writeAttribute(self::FIELD_LIST_RULE_ID, $this->listRuleId->_getValueAsString());
        }
        if (isset($this->transform) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TRANSFORM]) {
            $xw->writeAttribute(self::FIELD_TRANSFORM, $this->transform->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->context)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONTEXT]
                || $this->context->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONTEXT);
            $this->context->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONTEXT]);
            $xw->endElement();
        }
        if (isset($this->contextType)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONTEXT_TYPE]
                || $this->contextType->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONTEXT_TYPE);
            $this->contextType->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONTEXT_TYPE]);
            $xw->endElement();
        }
        if (isset($this->element)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ELEMENT]
                || $this->element->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ELEMENT);
            $this->element->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ELEMENT]);
            $xw->endElement();
        }
        if (isset($this->variable)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VARIABLE]
                || $this->variable->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VARIABLE);
            $this->variable->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VARIABLE]);
            $xw->endElement();
        }
        if (isset($this->listMode) && [] !== $this->listMode) {
            foreach($this->listMode as $v) {
                $xw->startElement(self::FIELD_LIST_MODE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->listRuleId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LIST_RULE_ID]
                || $this->listRuleId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LIST_RULE_ID);
            $this->listRuleId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LIST_RULE_ID]);
            $xw->endElement();
        }
        if (isset($this->transform)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TRANSFORM]
                || $this->transform->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TRANSFORM);
            $this->transform->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TRANSFORM]);
            $xw->endElement();
        }
        if (isset($this->parameter)) {
            foreach ($this->parameter as $v) {
                $xw->startElement(self::FIELD_PARAMETER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapTarget $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapTarget
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
        } else if (!($type instanceof FHIRStructureMapTarget)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->context)
            || isset($decoded->_context)
            || property_exists($decoded, self::FIELD_CONTEXT)
            || property_exists($decoded, self::FIELD_CONTEXT_EXT)) {
            $v = $decoded->_context ?? new \stdClass();
            $v->value = $decoded->context ?? null;
            $type->setContext(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->contextType)
            || isset($decoded->_contextType)
            || property_exists($decoded, self::FIELD_CONTEXT_TYPE)
            || property_exists($decoded, self::FIELD_CONTEXT_TYPE_EXT)) {
            $v = $decoded->_contextType ?? new \stdClass();
            $v->value = $decoded->contextType ?? null;
            $type->setContextType(FHIRStructureMapContextType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->element)
            || isset($decoded->_element)
            || property_exists($decoded, self::FIELD_ELEMENT)
            || property_exists($decoded, self::FIELD_ELEMENT_EXT)) {
            $v = $decoded->_element ?? new \stdClass();
            $v->value = $decoded->element ?? null;
            $type->setElement(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->variable)
            || isset($decoded->_variable)
            || property_exists($decoded, self::FIELD_VARIABLE)
            || property_exists($decoded, self::FIELD_VARIABLE_EXT)) {
            $v = $decoded->_variable ?? new \stdClass();
            $v->value = $decoded->variable ?? null;
            $type->setVariable(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->listMode)
            || isset($decoded->_listMode)
            || property_exists($decoded, self::FIELD_LIST_MODE)
            || property_exists($decoded, self::FIELD_LIST_MODE_EXT)) {
            $vals = (array)($decoded->listMode ?? []);
            $exts = (array)($decoded->_listMode ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addListMode(FHIRStructureMapTargetListMode::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->listRuleId)
            || isset($decoded->_listRuleId)
            || property_exists($decoded, self::FIELD_LIST_RULE_ID)
            || property_exists($decoded, self::FIELD_LIST_RULE_ID_EXT)) {
            $v = $decoded->_listRuleId ?? new \stdClass();
            $v->value = $decoded->listRuleId ?? null;
            $type->setListRuleId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->transform)
            || isset($decoded->_transform)
            || property_exists($decoded, self::FIELD_TRANSFORM)
            || property_exists($decoded, self::FIELD_TRANSFORM_EXT)) {
            $v = $decoded->_transform ?? new \stdClass();
            $v->value = $decoded->transform ?? null;
            $type->setTransform(FHIRStructureMapTransform::jsonUnserialize($v, $config));
        }
        if (isset($decoded->parameter) || property_exists($decoded, self::FIELD_PARAMETER)) {
            if (is_object($decoded->parameter)) {
                $vals = [$decoded->parameter];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PARAMETER, true);
            } else {
                $vals = $decoded->parameter;
            }
            foreach($vals as $v) {
                $type->addParameter(FHIRStructureMapParameter::jsonUnserialize($v, $config));
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
        if (isset($this->context)) {
            if (null !== ($val = $this->context->getValue())) {
                $out->context = $val;
            }
            if ($this->context->_nonValueFieldDefined()) {
                $ext = $this->context->jsonSerialize();
                unset($ext->value);
                $out->_context = $ext;
            }
        }
        if (isset($this->contextType)) {
            if (null !== ($val = $this->contextType->getValue())) {
                $out->contextType = $val;
            }
            if ($this->contextType->_nonValueFieldDefined()) {
                $ext = $this->contextType->jsonSerialize();
                unset($ext->value);
                $out->_contextType = $ext;
            }
        }
        if (isset($this->element)) {
            if (null !== ($val = $this->element->getValue())) {
                $out->element = $val;
            }
            if ($this->element->_nonValueFieldDefined()) {
                $ext = $this->element->jsonSerialize();
                unset($ext->value);
                $out->_element = $ext;
            }
        }
        if (isset($this->variable)) {
            if (null !== ($val = $this->variable->getValue())) {
                $out->variable = $val;
            }
            if ($this->variable->_nonValueFieldDefined()) {
                $ext = $this->variable->jsonSerialize();
                unset($ext->value);
                $out->_variable = $ext;
            }
        }
        if (isset($this->listMode) && [] !== $this->listMode) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->listMode as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->listMode = $vals;
            }
            if ($hasExts) {
                $out->_listMode = $exts;
            }
        }
        if (isset($this->listRuleId)) {
            if (null !== ($val = $this->listRuleId->getValue())) {
                $out->listRuleId = $val;
            }
            if ($this->listRuleId->_nonValueFieldDefined()) {
                $ext = $this->listRuleId->jsonSerialize();
                unset($ext->value);
                $out->_listRuleId = $ext;
            }
        }
        if (isset($this->transform)) {
            if (null !== ($val = $this->transform->getValue())) {
                $out->transform = $val;
            }
            if ($this->transform->_nonValueFieldDefined()) {
                $ext = $this->transform->jsonSerialize();
                unset($ext->value);
                $out->_transform = $ext;
            }
        }
        if (isset($this->parameter) && [] !== $this->parameter) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PARAMETER) && 1 === count($this->parameter)) {
                $out->parameter = $this->parameter[0];
            } else {
                $out->parameter = $this->parameter;
            }
        }
        return $out;
    }
}
