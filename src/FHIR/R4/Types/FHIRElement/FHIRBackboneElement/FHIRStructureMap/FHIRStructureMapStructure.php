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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapModelModeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapModelMode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A Map of relationships between 2 structures that can be used to transform data.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRStructureMapStructure extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_STRUCTURE_MAP_DOT_STRUCTURE;

    /* class_default.php:56 */
    public const FIELD_URL = 'url';
    public const FIELD_URL_EXT = '_url';
    public const FIELD_MODE = 'mode';
    public const FIELD_MODE_EXT = '_mode';
    public const FIELD_ALIAS = 'alias';
    public const FIELD_ALIAS_EXT = '_alias';
    public const FIELD_DOCUMENTATION = 'documentation';
    public const FIELD_DOCUMENTATION_EXT = '_documentation';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_URL => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_MODE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MODE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ALIAS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DOCUMENTATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The canonical reference to the structure.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $url;
    /**
     * How the referenced structure is used in this mapping.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the referenced structure is used in this mapping.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapModelMode
     */
    #[FHIRStructureMapModelMode]
    protected FHIRStructureMapModelMode $mode;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name used for this type in the map.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $alias;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Documentation that describes how the structure is used in the mapping.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $documentation;

    /* constructor.php:61 */
    /**
     * FHIRStructureMapStructure Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $url
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapModelModeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapModelMode $mode
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $alias
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $documentation
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $url = null,
                                null|string|FHIRStructureMapModelModeList|FHIRStructureMapModelMode $mode = null,
                                null|string|FHIRStringPrimitive|FHIRString $alias = null,
                                null|string|FHIRStringPrimitive|FHIRString $documentation = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $url) {
            $this->setUrl($url);
        }
        if (null !== $mode) {
            $this->setMode($mode);
        }
        if (null !== $alias) {
            $this->setAlias($alias);
        }
        if (null !== $documentation) {
            $this->setDocumentation($documentation);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The canonical reference to the structure.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getUrl(): null|FHIRCanonical
    {
        return $this->url ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The canonical reference to the structure.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $url
     * @return static
     */
    public function setUrl(null|string|FHIRCanonicalPrimitive|FHIRCanonical $url): self
    {
        if (null === $url) {
            unset($this->url);
            return $this;
        }
        if (!($url instanceof FHIRCanonical)) {
            $url = new FHIRCanonical(value: $url);
        }
        $this->url = $url;
        return $this;
    }

    /**
     * How the referenced structure is used in this mapping.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the referenced structure is used in this mapping.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapModelMode
     */
    public function getMode(): null|FHIRStructureMapModelMode
    {
        return $this->mode ?? null;
    }

    /**
     * How the referenced structure is used in this mapping.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * How the referenced structure is used in this mapping.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStructureMapModelModeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStructureMapModelMode $mode
     * @return static
     */
    public function setMode(null|string|FHIRStructureMapModelModeList|FHIRStructureMapModelMode $mode): self
    {
        if (null === $mode) {
            unset($this->mode);
            return $this;
        }
        if (!($mode instanceof FHIRStructureMapModelMode)) {
            $mode = new FHIRStructureMapModelMode(value: $mode);
        }
        $this->mode = $mode;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name used for this type in the map.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getAlias(): null|FHIRString
    {
        return $this->alias ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name used for this type in the map.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $alias
     * @return static
     */
    public function setAlias(null|string|FHIRStringPrimitive|FHIRString $alias): self
    {
        if (null === $alias) {
            unset($this->alias);
            return $this;
        }
        if (!($alias instanceof FHIRString)) {
            $alias = new FHIRString(value: $alias);
        }
        $this->alias = $alias;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Documentation that describes how the structure is used in the mapping.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDocumentation(): null|FHIRString
    {
        return $this->documentation ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Documentation that describes how the structure is used in the mapping.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $documentation
     * @return static
     */
    public function setDocumentation(null|string|FHIRStringPrimitive|FHIRString $documentation): self
    {
        if (null === $documentation) {
            unset($this->documentation);
            return $this;
        }
        if (!($documentation instanceof FHIRString)) {
            $documentation = new FHIRString(value: $documentation);
        }
        $this->documentation = $documentation;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapStructure $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapStructure
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRStructureMapStructure)) {
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
            } else if (self::FIELD_URL === $cen) {
                $type->setUrl(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODE === $cen) {
                $type->setMode(FHIRStructureMapModelMode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ALIAS === $cen) {
                $type->setAlias(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOCUMENTATION === $cen) {
                $type->setDocumentation(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_URL])) {
            if (isset($type->url)) {
                $type->url->setValue((string)$attributes[self::FIELD_URL]);
            } else {
                $type->setUrl((string)$attributes[self::FIELD_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MODE])) {
            if (isset($type->mode)) {
                $type->mode->setValue((string)$attributes[self::FIELD_MODE]);
            } else {
                $type->setMode((string)$attributes[self::FIELD_MODE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MODE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ALIAS])) {
            if (isset($type->alias)) {
                $type->alias->setValue((string)$attributes[self::FIELD_ALIAS]);
            } else {
                $type->setAlias((string)$attributes[self::FIELD_ALIAS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ALIAS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DOCUMENTATION])) {
            if (isset($type->documentation)) {
                $type->documentation->setValue((string)$attributes[self::FIELD_DOCUMENTATION]);
            } else {
                $type->setDocumentation((string)$attributes[self::FIELD_DOCUMENTATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DOCUMENTATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->url) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_URL]) {
            $xw->writeAttribute(self::FIELD_URL, $this->url->_getValueAsString());
        }
        if (isset($this->mode) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MODE]) {
            $xw->writeAttribute(self::FIELD_MODE, $this->mode->_getValueAsString());
        }
        if (isset($this->alias) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ALIAS]) {
            $xw->writeAttribute(self::FIELD_ALIAS, $this->alias->_getValueAsString());
        }
        if (isset($this->documentation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]) {
            $xw->writeAttribute(self::FIELD_DOCUMENTATION, $this->documentation->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->url)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_URL]
                || $this->url->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_URL);
            $this->url->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_URL]);
            $xw->endElement();
        }
        if (isset($this->mode)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MODE]
                || $this->mode->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MODE);
            $this->mode->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MODE]);
            $xw->endElement();
        }
        if (isset($this->alias)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ALIAS]
                || $this->alias->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ALIAS);
            $this->alias->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ALIAS]);
            $xw->endElement();
        }
        if (isset($this->documentation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]
                || $this->documentation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DOCUMENTATION);
            $this->documentation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DOCUMENTATION]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapStructure $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRStructureMap\FHIRStructureMapStructure
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
        } else if (!($type instanceof FHIRStructureMapStructure)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->url)
            || isset($decoded->_url)
            || property_exists($decoded, self::FIELD_URL)
            || property_exists($decoded, self::FIELD_URL_EXT)) {
            $v = $decoded->_url ?? new \stdClass();
            $v->value = $decoded->url ?? null;
            $type->setUrl(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->mode)
            || isset($decoded->_mode)
            || property_exists($decoded, self::FIELD_MODE)
            || property_exists($decoded, self::FIELD_MODE_EXT)) {
            $v = $decoded->_mode ?? new \stdClass();
            $v->value = $decoded->mode ?? null;
            $type->setMode(FHIRStructureMapModelMode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->alias)
            || isset($decoded->_alias)
            || property_exists($decoded, self::FIELD_ALIAS)
            || property_exists($decoded, self::FIELD_ALIAS_EXT)) {
            $v = $decoded->_alias ?? new \stdClass();
            $v->value = $decoded->alias ?? null;
            $type->setAlias(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->documentation)
            || isset($decoded->_documentation)
            || property_exists($decoded, self::FIELD_DOCUMENTATION)
            || property_exists($decoded, self::FIELD_DOCUMENTATION_EXT)) {
            $v = $decoded->_documentation ?? new \stdClass();
            $v->value = $decoded->documentation ?? null;
            $type->setDocumentation(FHIRString::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->url)) {
            if (null !== ($val = $this->url->getValue())) {
                $out->url = $val;
            }
            if ($this->url->_nonValueFieldDefined()) {
                $ext = $this->url->jsonSerialize();
                unset($ext->value);
                $out->_url = $ext;
            }
        }
        if (isset($this->mode)) {
            if (null !== ($val = $this->mode->getValue())) {
                $out->mode = $val;
            }
            if ($this->mode->_nonValueFieldDefined()) {
                $ext = $this->mode->jsonSerialize();
                unset($ext->value);
                $out->_mode = $ext;
            }
        }
        if (isset($this->alias)) {
            if (null !== ($val = $this->alias->getValue())) {
                $out->alias = $val;
            }
            if ($this->alias->_nonValueFieldDefined()) {
                $ext = $this->alias->jsonSerialize();
                unset($ext->value);
                $out->_alias = $ext;
            }
        }
        if (isset($this->documentation)) {
            if (null !== ($val = $this->documentation->getValue())) {
                $out->documentation = $val;
            }
            if ($this->documentation->_nonValueFieldDefined()) {
                $ext = $this->documentation->jsonSerialize();
                unset($ext->value);
                $out->_documentation = $ext;
            }
        }
        return $out;
    }
}
