<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types;

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
use OpenEMR\FHIR\Constants;
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\ValueXMLLocationEnum;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLWriter;
use OpenEMR\FHIR\FHIRVersion;
use OpenEMR\FHIR\Types\CommentContainerInterface;
use OpenEMR\FHIR\Types\CommentContainerTrait;
use OpenEMR\FHIR\Types\ElementTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Base definition for all elements in a resource.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 */
class FHIRElement implements ElementTypeInterface, CommentContainerInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait,
        CommentContainerTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_ELEMENT;

    /* class_default.php:56 */
    public const FIELD_EXTENSION = 'extension';
    public const FIELD_ID = 'id';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ID => ValueXMLLocationEnum::PARENT_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the element. To make the use of extensions safe and manageable,
     * there is a strict set of governance applied to the definition and use of
     * extensions. Though any implementer can define an extension, there is a set of
     * requirements that SHALL be met as part of the definition of the extension.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension>
     */
    #[FHIRExtension]
    protected array $extension;
    /** @var \OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive */
    #[FHIRStringPrimitive]
    protected FHIRStringPrimitive $id;

    /* constructor.php:61 */
    /**
     * FHIRElement Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $fhirComments = null)
    {
        if (null !== $fhirComments && [] !== $fhirComments) {
            $this->_setFHIRComments($fhirComments);
        }
        if (null !== $extension) {
            $this->setExtension(...$extension);
        }
        if (null !== $id) {
            $this->setId($id);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:154 */
    public function _getFHIRVersion(): FHIRVersion
    {
        return Version::getFHIRVersion();
    }

    /* class_default.php:174 */
    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the element. To make the use of extensions safe and manageable,
     * there is a strict set of governance applied to the definition and use of
     * extensions. Though any implementer can define an extension, there is a set of
     * requirements that SHALL be met as part of the definition of the extension.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension>
     */
    public function getExtension(): array
    {
        return $this->extension ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension>
     */
    public function getExtensionIterator(): iterable
    {
        if (!isset($this->extension)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->extension);
    }

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the element. To make the use of extensions safe and manageable,
     * there is a strict set of governance applied to the definition and use of
     * extensions. Though any implementer can define an extension, there is a set of
     * requirements that SHALL be met as part of the definition of the extension.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension $extension
     * @return static
     */
    public function addExtension(FHIRExtension $extension): self
    {
        if (!isset($this->extension)) {
            $this->extension = [];
        }
        $this->extension[] = $extension;
        return $this;
    }

    /**
     * Optional Extension Element - found in all resources.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * May be used to represent additional information that is not part of the basic
     * definition of the element. To make the use of extensions safe and manageable,
     * there is a strict set of governance applied to the definition and use of
     * extensions. Though any implementer can define an extension, there is a set of
     * requirements that SHALL be met as part of the definition of the extension.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension ...$extension
     * @return static
     */
    public function setExtension(FHIRExtension ...$extension): self
    {
        if ([] === $extension) {
            unset($this->extension);
            return $this;
        }
        $this->extension = $extension;
        return $this;
    }

    /**
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive
     */
    public function getId(): null|FHIRStringPrimitive
    {
        return $this->id ?? null;
    }

    /**
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @return static
     */
    public function setId(null|string|FHIRStringPrimitive $id): self
    {
        if (null === $id) {
            unset($this->id);
            return $this;
        }
        if (!($id instanceof FHIRStringPrimitive)) {
            $id = new FHIRStringPrimitive(value: $id);
        }
        $this->id = $id;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRElement)) {
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
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->id) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ID]) {
            $xw->writeAttribute(self::FIELD_ID, $this->id->_getValueAsString());
        }
        if (isset($this->extension)) {
            foreach ($this->extension as $v) {
                $xw->startElement(self::FIELD_EXTENSION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->id)) {
            if (ValueXMLLocationEnum::ELEMENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ID]) {
                $xw->startElement(self::FIELD_ID);
                $xw->writeAttribute(FHIRStringPrimitive::FIELD_VALUE, $this->id->_getValueAsString());
                $xw->endElement();
            } else if (ValueXMLLocationEnum::ELEMENT_VALUE === $this->_valueXMLLocations[self::FIELD_ID]) {
                $xw->writeElement(self::FIELD_ID, $this->id->_getValueAsString());
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement
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
        } else if (!($type instanceof FHIRElement)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (isset($decoded->{Constants::JSON_FIELD_FHIR_COMMENTS})) {
            $type->_setFHIRComments((array)$decoded->{Constants::JSON_FIELD_FHIR_COMMENTS});
        }
        if (isset($decoded->extension) || property_exists($decoded, self::FIELD_EXTENSION)) {
            if (is_object($decoded->extension)) {
                $vals = [$decoded->extension];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_EXTENSION, true);
            } else {
                $vals = $decoded->extension;
            }
            foreach($vals as $v) {
                $type->addExtension(FHIRExtension::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->id) || property_exists($decoded, self::FIELD_ID)) {
            $type->setId($decoded->id);
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = new \stdClass();
        if ([] !== ($vs = $this->_getFHIRComments())) {
            $out->fhir_comments = $vs;
        }
        if (isset($this->extension) && [] !== $this->extension) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_EXTENSION) && 1 === count($this->extension)) {
                $out->extension = $this->extension[0];
            } else {
                $out->extension = $this->extension;
            }
        }
        if (isset($this->id)) {
            $out->id = $this->id;
        }
        if ([] !== ($vs = $this->_getFHIRComments())) {
            $out->fhir_comments = $vs;
        }
        return $out;
    }

    /* class_default.php:227 */
    public function __toString(): string
    {
        return (string) self::FHIR_TYPE_NAME;
    }
}
