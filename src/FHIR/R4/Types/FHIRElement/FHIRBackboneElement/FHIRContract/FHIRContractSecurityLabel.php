<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
 * policy or agreement.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRContractSecurityLabel extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONTRACT_DOT_SECURITY_LABEL;

    /* class_default.php:56 */
    public const FIELD_NUMBER = 'number';
    public const FIELD_NUMBER_EXT = '_number';
    public const FIELD_CLASSIFICATION = 'classification';
    public const FIELD_CATEGORY = 'category';
    public const FIELD_CONTROL = 'control';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_CLASSIFICATION => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Number used to link this term or term element to the applicable Security Label.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt>
     */
    #[FHIRUnsignedInt]
    protected array $number;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the level of confidentiality protection
     * required for this term and/or term elements.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    #[FHIRCoding]
    protected FHIRCoding $classification;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the applicable privacy and security
     * policies governing this term and/or term elements.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    #[FHIRCoding]
    protected array $category;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the manner in which term and/or term
     * elements are to be protected.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    #[FHIRCoding]
    protected array $control;

    /* constructor.php:61 */
    /**
     * FHIRContractSecurityLabel Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<string>|iterable<int>|iterable<float>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt> $number
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $classification
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding> $category
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding> $control
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $number = null,
                                null|FHIRCoding $classification = null,
                                null|iterable $category = null,
                                null|iterable $control = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $number) {
            $this->setNumber(...$number);
        }
        if (null !== $classification) {
            $this->setClassification($classification);
        }
        if (null !== $category) {
            $this->setCategory(...$category);
        }
        if (null !== $control) {
            $this->setControl(...$control);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Number used to link this term or term element to the applicable Security Label.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt>
     */
    public function getNumber(): array
    {
        return $this->number ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt>
     */
    public function getNumberIterator(): iterable
    {
        if (!isset($this->number)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->number);
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Number used to link this term or term element to the applicable Security Label.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $number
     * @return static
     */
    public function addNumber(string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $number): self
    {
        if (!($number instanceof FHIRUnsignedInt)) {
            $number = new FHIRUnsignedInt(value: $number);
        }
        if (!isset($this->number)) {
            $this->number = [];
        }
        $this->number[] = $number;
        return $this;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Number used to link this term or term element to the applicable Security Label.
     *
     * @param string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt ...$number
     * @return static
     */
    public function setNumber(string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt ...$number): self
    {
        if ([] === $number) {
            unset($this->number);
            return $this;
        }
        $this->number = [];
        foreach($number as $v) {
            if ($v instanceof FHIRUnsignedInt) {
                $this->number[] = $v;
            } else {
                $this->number[] = new FHIRUnsignedInt(value: $v);
            }
        }
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the level of confidentiality protection
     * required for this term and/or term elements.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    public function getClassification(): null|FHIRCoding
    {
        return $this->classification ?? null;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the level of confidentiality protection
     * required for this term and/or term elements.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $classification
     * @return static
     */
    public function setClassification(null|FHIRCoding $classification): self
    {
        if (null === $classification) {
            unset($this->classification);
            return $this;
        }
        $this->classification = $classification;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the applicable privacy and security
     * policies governing this term and/or term elements.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getCategory(): array
    {
        return $this->category ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getCategoryIterator(): iterable
    {
        if (!isset($this->category)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->category);
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the applicable privacy and security
     * policies governing this term and/or term elements.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $category
     * @return static
     */
    public function addCategory(FHIRCoding $category): self
    {
        if (!isset($this->category)) {
            $this->category = [];
        }
        $this->category[] = $category;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the applicable privacy and security
     * policies governing this term and/or term elements.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding ...$category
     * @return static
     */
    public function setCategory(FHIRCoding ...$category): self
    {
        if ([] === $category) {
            unset($this->category);
            return $this;
        }
        $this->category = $category;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the manner in which term and/or term
     * elements are to be protected.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getControl(): array
    {
        return $this->control ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getControlIterator(): iterable
    {
        if (!isset($this->control)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->control);
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the manner in which term and/or term
     * elements are to be protected.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $control
     * @return static
     */
    public function addControl(FHIRCoding $control): self
    {
        if (!isset($this->control)) {
            $this->control = [];
        }
        $this->control[] = $control;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Security label privacy tag that species the manner in which term and/or term
     * elements are to be protected.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding ...$control
     * @return static
     */
    public function setControl(FHIRCoding ...$control): self
    {
        if ([] === $control) {
            unset($this->control);
            return $this;
        }
        $this->control = $control;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRContractSecurityLabel)) {
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
            } else if (self::FIELD_NUMBER === $cen) {
                $type->addNumber(FHIRUnsignedInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLASSIFICATION === $cen) {
                $type->setClassification(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->addCategory(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTROL === $cen) {
                $type->addControl(FHIRCoding::xmlUnserialize($ce, $config));
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
        parent::xmlSerialize($xw, $config);
        if (isset($this->number) && [] !== $this->number) {
            foreach($this->number as $v) {
                $xw->startElement(self::FIELD_NUMBER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->classification)) {
            $xw->startElement(self::FIELD_CLASSIFICATION);
            $this->classification->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->category)) {
            foreach ($this->category as $v) {
                $xw->startElement(self::FIELD_CATEGORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->control)) {
            foreach ($this->control as $v) {
                $xw->startElement(self::FIELD_CONTROL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel
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
        } else if (!($type instanceof FHIRContractSecurityLabel)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->number)
            || isset($decoded->_number)
            || property_exists($decoded, self::FIELD_NUMBER)
            || property_exists($decoded, self::FIELD_NUMBER_EXT)) {
            $vals = (array)($decoded->number ?? []);
            $exts = (array)($decoded->_number ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addNumber(FHIRUnsignedInt::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->classification) || property_exists($decoded, self::FIELD_CLASSIFICATION)) {
            if (is_array($decoded->classification)) {
                $type->setClassification(FHIRCoding::jsonUnserialize(reset($decoded->classification), $config));
            } else {
                $type->setClassification(FHIRCoding::jsonUnserialize($decoded->classification, $config));
            }
        }
        if (isset($decoded->category) || property_exists($decoded, self::FIELD_CATEGORY)) {
            if (is_object($decoded->category)) {
                $vals = [$decoded->category];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CATEGORY, true);
            } else {
                $vals = $decoded->category;
            }
            foreach($vals as $v) {
                $type->addCategory(FHIRCoding::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->control) || property_exists($decoded, self::FIELD_CONTROL)) {
            if (is_object($decoded->control)) {
                $vals = [$decoded->control];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTROL, true);
            } else {
                $vals = $decoded->control;
            }
            foreach($vals as $v) {
                $type->addControl(FHIRCoding::jsonUnserialize($v, $config));
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
        if (isset($this->number) && [] !== $this->number) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->number as $v) {
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
                $out->number = $vals;
            }
            if ($hasExts) {
                $out->_number = $exts;
            }
        }
        if (isset($this->classification)) {
            $out->classification = $this->classification;
        }
        if (isset($this->category) && [] !== $this->category) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CATEGORY) && 1 === count($this->category)) {
                $out->category = $this->category[0];
            } else {
                $out->category = $this->category;
            }
        }
        if (isset($this->control) && [] !== $this->control) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTROL) && 1 === count($this->control)) {
                $out->control = $this->control[0];
            } else {
                $out->control = $this->control;
            }
        }
        return $out;
    }
}
