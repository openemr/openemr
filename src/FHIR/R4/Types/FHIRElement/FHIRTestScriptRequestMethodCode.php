<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement;

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
use OpenEMR\FHIR\Types\PrimitiveContainerTypeInterface;
use OpenEMR\FHIR\Types\ValueContainerTrait;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The allowable request method or HTTP operation codes.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTestScriptRequestMethodCode extends FHIRElement implements PrimitiveContainerTypeInterface, \Stringable
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait,
        ValueContainerTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TEST_SCRIPT_REQUEST_METHOD_CODE;

    /* class_default.php:56 */
    public const FIELD_VALUE = 'value';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_VALUE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /** @var \OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList */
    #[FHIRTestScriptRequestMethodCodeList]
    protected FHIRTestScriptRequestMethodCodeList $value;

    /* constructor.php:61 */
    /**
     * FHIRTestScriptRequestMethodCode Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList $value
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|string|FHIRTestScriptRequestMethodCodeList $value = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            fhirComments: $fhirComments);
        if (null !== $value) {
            $this->setValue($value);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList
     */
    public function getValue(): null|FHIRTestScriptRequestMethodCodeList
    {
        return $this->value ?? null;
    }

    /**
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList $value
     * @return static
     */
    public function setValue(null|string|FHIRTestScriptRequestMethodCodeList $value): self
    {
        if (null === $value) {
            unset($this->value);
            return $this;
        }
        if (!($value instanceof FHIRTestScriptRequestMethodCodeList)) {
            $value = new FHIRTestScriptRequestMethodCodeList(value: $value);
        }
        $this->value = $value;
        return $this;
    }

    /* class_default.php:190 */
    public function _nonValueFieldDefined(): bool
    {
        return isset($this->extension)
            || isset($this->id);
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTestScriptRequestMethodCode)) {
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
            } else if (self::FIELD_VALUE === $cen) {
                $va = $ce->attributes()[FHIRTestScriptRequestMethodCodeList::FIELD_VALUE] ?? null;
                if (null !== $va) {
                    $type->setValue((string)$va);
                    $type->_setXMLFieldValueLocation(self::FIELD_VALUE, ValueXMLLocationEnum::ELEMENT_ATTRIBUTE);
                } else {
                    $type->setValue((string)$ce);
                    $type->_setXMLFieldValueLocation(self::FIELD_VALUE, ValueXMLLocationEnum::ELEMENT_VALUE);
                }
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE])) {
            $type->setValue((string)$attributes[self::FIELD_VALUE]);
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE, ValueXMLLocationEnum::CONTAINER_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param \OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param \OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @param null|\OpenEMR\FHIR\Encoding\ValueXMLLocationEnum $valueLocation
     */
    public function xmlSerialize(XMLWriter $xw,
                                 SerializeConfig $config,
                                 null|ValueXMLLocationEnum $valueLocation = null): void
    {
        $valueLocation ??= $this->_valueXMLLocations[self::FIELD_VALUE];
        if (isset($this->value) && ValueXMLLocationEnum::CONTAINER_ATTRIBUTE === $valueLocation) {
            $xw->writeAttribute(self::FIELD_VALUE, $this->value->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->value)) {
            if (ValueXMLLocationEnum::CONTAINER_VALUE === $valueLocation) {
                $xw->text($this->value->_getValueAsString());
            } else if (ValueXMLLocationEnum::ELEMENT_ATTRIBUTE === $valueLocation) {
                $xw->startElement(self::FIELD_VALUE);
                $xw->writeAttribute(FHIRTestScriptRequestMethodCodeList::FIELD_VALUE, $this->value->_getValueAsString());
                $xw->endElement();
            } else if (ValueXMLLocationEnum::ELEMENT_VALUE === $valueLocation) {
                $xw->writeElement(self::FIELD_VALUE, $this->value->_getValueAsString());
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode
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
        } else if (!($type instanceof FHIRTestScriptRequestMethodCode)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->value) || property_exists($decoded, self::FIELD_VALUE)) {
            $type->setValue($decoded->value);
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->value)) {
            $out->value = $this->value;
        }
        return $out;
    }

    /* class_default.php:227 */
    public function __toString(): string
    {
        return $this->_getValueAsString();
    }
}
