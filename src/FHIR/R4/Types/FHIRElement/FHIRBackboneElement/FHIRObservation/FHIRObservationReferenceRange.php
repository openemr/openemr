<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservation;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Measurements and simple assertions made about a patient, device or other
 * subject.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRObservationReferenceRange extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_OBSERVATION_DOT_REFERENCE_RANGE;

    /* class_default.php:56 */
    public const FIELD_LOW = 'low';
    public const FIELD_HIGH = 'high';
    public const FIELD_TYPE = 'type';
    public const FIELD_APPLIES_TO = 'appliesTo';
    public const FIELD_AGE = 'age';
    public const FIELD_TEXT = 'text';
    public const FIELD_TEXT_EXT = '_text';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_TEXT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the low bound of the reference range. The low bound of the
     * reference range endpoint is inclusive of the value (e.g. reference range is >=5
     * - <=9). If the low bound is omitted, it is assumed to be meaningless (e.g.
     * reference range is <=2.3).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $low;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the high bound of the reference range. The high bound of the
     * reference range endpoint is inclusive of the value (e.g. reference range is >=5
     * - <=9). If the high bound is omitted, it is assumed to be meaningless (e.g.
     * reference range is >= 2.3).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $high;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes to indicate the what part of the targeted reference population it applies
     * to. For example, the normal or therapeutic range.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes to indicate the target population this reference range applies to. For
     * example, a reference range may be based on the normal population or a particular
     * sex or race. Multiple `appliesTo` are interpreted as an "AND" of the target
     * populations. For example, to represent a target population of African American
     * females, both a code of female and a code for African American would be used.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $appliesTo;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The age at which this reference range is applicable. This is a neonatal age
     * (e.g. number of weeks at term) if the meaning says so.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $age;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Text based reference range in an observation which may be used when a
     * quantitative range is not appropriate for an observation. An example would be a
     * reference value of "Negative" or a list or table of "normals".
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $text;

    /* constructor.php:61 */
    /**
     * FHIRObservationReferenceRange Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $low
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $high
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $appliesTo
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $age
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $text
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRQuantity $low = null,
                                null|FHIRQuantity $high = null,
                                null|FHIRCodeableConcept $type = null,
                                null|iterable $appliesTo = null,
                                null|FHIRRange $age = null,
                                null|string|FHIRStringPrimitive|FHIRString $text = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $low) {
            $this->setLow($low);
        }
        if (null !== $high) {
            $this->setHigh($high);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $appliesTo) {
            $this->setAppliesTo(...$appliesTo);
        }
        if (null !== $age) {
            $this->setAge($age);
        }
        if (null !== $text) {
            $this->setText($text);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the low bound of the reference range. The low bound of the
     * reference range endpoint is inclusive of the value (e.g. reference range is >=5
     * - <=9). If the low bound is omitted, it is assumed to be meaningless (e.g.
     * reference range is <=2.3).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getLow(): null|FHIRQuantity
    {
        return $this->low ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the low bound of the reference range. The low bound of the
     * reference range endpoint is inclusive of the value (e.g. reference range is >=5
     * - <=9). If the low bound is omitted, it is assumed to be meaningless (e.g.
     * reference range is <=2.3).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $low
     * @return static
     */
    public function setLow(null|FHIRQuantity $low): self
    {
        if (null === $low) {
            unset($this->low);
            return $this;
        }
        $this->low = $low;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the high bound of the reference range. The high bound of the
     * reference range endpoint is inclusive of the value (e.g. reference range is >=5
     * - <=9). If the high bound is omitted, it is assumed to be meaningless (e.g.
     * reference range is >= 2.3).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getHigh(): null|FHIRQuantity
    {
        return $this->high ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The value of the high bound of the reference range. The high bound of the
     * reference range endpoint is inclusive of the value (e.g. reference range is >=5
     * - <=9). If the high bound is omitted, it is assumed to be meaningless (e.g.
     * reference range is >= 2.3).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $high
     * @return static
     */
    public function setHigh(null|FHIRQuantity $high): self
    {
        if (null === $high) {
            unset($this->high);
            return $this;
        }
        $this->high = $high;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes to indicate the what part of the targeted reference population it applies
     * to. For example, the normal or therapeutic range.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getType(): null|FHIRCodeableConcept
    {
        return $this->type ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes to indicate the what part of the targeted reference population it applies
     * to. For example, the normal or therapeutic range.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function setType(null|FHIRCodeableConcept $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes to indicate the target population this reference range applies to. For
     * example, a reference range may be based on the normal population or a particular
     * sex or race. Multiple `appliesTo` are interpreted as an "AND" of the target
     * populations. For example, to represent a target population of African American
     * females, both a code of female and a code for African American would be used.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getAppliesTo(): array
    {
        return $this->appliesTo ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getAppliesToIterator(): iterable
    {
        if (!isset($this->appliesTo)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->appliesTo);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes to indicate the target population this reference range applies to. For
     * example, a reference range may be based on the normal population or a particular
     * sex or race. Multiple `appliesTo` are interpreted as an "AND" of the target
     * populations. For example, to represent a target population of African American
     * females, both a code of female and a code for African American would be used.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $appliesTo
     * @return static
     */
    public function addAppliesTo(FHIRCodeableConcept $appliesTo): self
    {
        if (!isset($this->appliesTo)) {
            $this->appliesTo = [];
        }
        $this->appliesTo[] = $appliesTo;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes to indicate the target population this reference range applies to. For
     * example, a reference range may be based on the normal population or a particular
     * sex or race. Multiple `appliesTo` are interpreted as an "AND" of the target
     * populations. For example, to represent a target population of African American
     * females, both a code of female and a code for African American would be used.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$appliesTo
     * @return static
     */
    public function setAppliesTo(FHIRCodeableConcept ...$appliesTo): self
    {
        if ([] === $appliesTo) {
            unset($this->appliesTo);
            return $this;
        }
        $this->appliesTo = $appliesTo;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The age at which this reference range is applicable. This is a neonatal age
     * (e.g. number of weeks at term) if the meaning says so.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getAge(): null|FHIRRange
    {
        return $this->age ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The age at which this reference range is applicable. This is a neonatal age
     * (e.g. number of weeks at term) if the meaning says so.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $age
     * @return static
     */
    public function setAge(null|FHIRRange $age): self
    {
        if (null === $age) {
            unset($this->age);
            return $this;
        }
        $this->age = $age;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Text based reference range in an observation which may be used when a
     * quantitative range is not appropriate for an observation. An example would be a
     * reference value of "Negative" or a list or table of "normals".
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getText(): null|FHIRString
    {
        return $this->text ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Text based reference range in an observation which may be used when a
     * quantitative range is not appropriate for an observation. An example would be a
     * reference value of "Negative" or a list or table of "normals".
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $text
     * @return static
     */
    public function setText(null|string|FHIRStringPrimitive|FHIRString $text): self
    {
        if (null === $text) {
            unset($this->text);
            return $this;
        }
        if (!($text instanceof FHIRString)) {
            $text = new FHIRString(value: $text);
        }
        $this->text = $text;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservation\FHIRObservationReferenceRange $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservation\FHIRObservationReferenceRange
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRObservationReferenceRange)) {
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
            } else if (self::FIELD_LOW === $cen) {
                $type->setLow(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_HIGH === $cen) {
                $type->setHigh(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_APPLIES_TO === $cen) {
                $type->addAppliesTo(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AGE === $cen) {
                $type->setAge(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TEXT])) {
            if (isset($type->text)) {
                $type->text->setValue((string)$attributes[self::FIELD_TEXT]);
            } else {
                $type->setText((string)$attributes[self::FIELD_TEXT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TEXT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->text) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TEXT]) {
            $xw->writeAttribute(self::FIELD_TEXT, $this->text->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->low)) {
            $xw->startElement(self::FIELD_LOW);
            $this->low->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->high)) {
            $xw->startElement(self::FIELD_HIGH);
            $this->high->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->appliesTo)) {
            foreach ($this->appliesTo as $v) {
                $xw->startElement(self::FIELD_APPLIES_TO);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->age)) {
            $xw->startElement(self::FIELD_AGE);
            $this->age->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->text)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TEXT]
                || $this->text->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TEXT);
            $this->text->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TEXT]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservation\FHIRObservationReferenceRange $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRObservation\FHIRObservationReferenceRange
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
        } else if (!($type instanceof FHIRObservationReferenceRange)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->low) || property_exists($decoded, self::FIELD_LOW)) {
            if (is_array($decoded->low)) {
                $type->setLow(FHIRQuantity::jsonUnserialize(reset($decoded->low), $config));
            } else {
                $type->setLow(FHIRQuantity::jsonUnserialize($decoded->low, $config));
            }
        }
        if (isset($decoded->high) || property_exists($decoded, self::FIELD_HIGH)) {
            if (is_array($decoded->high)) {
                $type->setHigh(FHIRQuantity::jsonUnserialize(reset($decoded->high), $config));
            } else {
                $type->setHigh(FHIRQuantity::jsonUnserialize($decoded->high, $config));
            }
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->appliesTo) || property_exists($decoded, self::FIELD_APPLIES_TO)) {
            if (is_object($decoded->appliesTo)) {
                $vals = [$decoded->appliesTo];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_APPLIES_TO, true);
            } else {
                $vals = $decoded->appliesTo;
            }
            foreach($vals as $v) {
                $type->addAppliesTo(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->age) || property_exists($decoded, self::FIELD_AGE)) {
            if (is_array($decoded->age)) {
                $type->setAge(FHIRRange::jsonUnserialize(reset($decoded->age), $config));
            } else {
                $type->setAge(FHIRRange::jsonUnserialize($decoded->age, $config));
            }
        }
        if (isset($decoded->text)
            || isset($decoded->_text)
            || property_exists($decoded, self::FIELD_TEXT)
            || property_exists($decoded, self::FIELD_TEXT_EXT)) {
            $v = $decoded->_text ?? new \stdClass();
            $v->value = $decoded->text ?? null;
            $type->setText(FHIRString::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->low)) {
            $out->low = $this->low;
        }
        if (isset($this->high)) {
            $out->high = $this->high;
        }
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->appliesTo) && [] !== $this->appliesTo) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_APPLIES_TO) && 1 === count($this->appliesTo)) {
                $out->appliesTo = $this->appliesTo[0];
            } else {
                $out->appliesTo = $this->appliesTo;
            }
        }
        if (isset($this->age)) {
            $out->age = $this->age;
        }
        if (isset($this->text)) {
            if (null !== ($val = $this->text->getValue())) {
                $out->text = $val;
            }
            if ($this->text->_nonValueFieldDefined()) {
                $ext = $this->text->jsonSerialize();
                unset($ext->value);
                $out->_text = $ext;
            }
        }
        return $out;
    }
}
