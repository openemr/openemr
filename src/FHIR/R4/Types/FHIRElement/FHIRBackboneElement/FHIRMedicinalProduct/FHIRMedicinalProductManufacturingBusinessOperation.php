<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Detailed definition of a medicinal product, typically for uses other than direct
 * patient care (e.g. regulatory use).
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicinalProductManufacturingBusinessOperation extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICINAL_PRODUCT_DOT_MANUFACTURING_BUSINESS_OPERATION;

    /* class_default.php:56 */
    public const FIELD_OPERATION_TYPE = 'operationType';
    public const FIELD_AUTHORISATION_REFERENCE_NUMBER = 'authorisationReferenceNumber';
    public const FIELD_EFFECTIVE_DATE = 'effectiveDate';
    public const FIELD_EFFECTIVE_DATE_EXT = '_effectiveDate';
    public const FIELD_CONFIDENTIALITY_INDICATOR = 'confidentialityIndicator';
    public const FIELD_MANUFACTURER = 'manufacturer';
    public const FIELD_REGULATOR = 'regulator';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_EFFECTIVE_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of manufacturing operation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $operationType;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Regulatory authorization reference number.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $authorisationReferenceNumber;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Regulatory authorization date.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $effectiveDate;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * To indicate if this proces is commercially confidential.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $confidentialityIndicator;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The manufacturer or establishment associated with the process.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $manufacturer;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A regulator which oversees the operation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $regulator;

    /* constructor.php:61 */
    /**
     * FHIRMedicinalProductManufacturingBusinessOperation Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $operationType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $authorisationReferenceNumber
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $effectiveDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $confidentialityIndicator
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $manufacturer
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $regulator
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $operationType = null,
                                null|FHIRIdentifier $authorisationReferenceNumber = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $effectiveDate = null,
                                null|FHIRCodeableConcept $confidentialityIndicator = null,
                                null|iterable $manufacturer = null,
                                null|FHIRReference $regulator = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $operationType) {
            $this->setOperationType($operationType);
        }
        if (null !== $authorisationReferenceNumber) {
            $this->setAuthorisationReferenceNumber($authorisationReferenceNumber);
        }
        if (null !== $effectiveDate) {
            $this->setEffectiveDate($effectiveDate);
        }
        if (null !== $confidentialityIndicator) {
            $this->setConfidentialityIndicator($confidentialityIndicator);
        }
        if (null !== $manufacturer) {
            $this->setManufacturer(...$manufacturer);
        }
        if (null !== $regulator) {
            $this->setRegulator($regulator);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of manufacturing operation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getOperationType(): null|FHIRCodeableConcept
    {
        return $this->operationType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of manufacturing operation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $operationType
     * @return static
     */
    public function setOperationType(null|FHIRCodeableConcept $operationType): self
    {
        if (null === $operationType) {
            unset($this->operationType);
            return $this;
        }
        $this->operationType = $operationType;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Regulatory authorization reference number.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getAuthorisationReferenceNumber(): null|FHIRIdentifier
    {
        return $this->authorisationReferenceNumber ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Regulatory authorization reference number.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $authorisationReferenceNumber
     * @return static
     */
    public function setAuthorisationReferenceNumber(null|FHIRIdentifier $authorisationReferenceNumber): self
    {
        if (null === $authorisationReferenceNumber) {
            unset($this->authorisationReferenceNumber);
            return $this;
        }
        $this->authorisationReferenceNumber = $authorisationReferenceNumber;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Regulatory authorization date.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getEffectiveDate(): null|FHIRDateTime
    {
        return $this->effectiveDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Regulatory authorization date.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $effectiveDate
     * @return static
     */
    public function setEffectiveDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $effectiveDate): self
    {
        if (null === $effectiveDate) {
            unset($this->effectiveDate);
            return $this;
        }
        if (!($effectiveDate instanceof FHIRDateTime)) {
            $effectiveDate = new FHIRDateTime(value: $effectiveDate);
        }
        $this->effectiveDate = $effectiveDate;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * To indicate if this proces is commercially confidential.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getConfidentialityIndicator(): null|FHIRCodeableConcept
    {
        return $this->confidentialityIndicator ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * To indicate if this proces is commercially confidential.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $confidentialityIndicator
     * @return static
     */
    public function setConfidentialityIndicator(null|FHIRCodeableConcept $confidentialityIndicator): self
    {
        if (null === $confidentialityIndicator) {
            unset($this->confidentialityIndicator);
            return $this;
        }
        $this->confidentialityIndicator = $confidentialityIndicator;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The manufacturer or establishment associated with the process.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getManufacturer(): array
    {
        return $this->manufacturer ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getManufacturerIterator(): iterable
    {
        if (!isset($this->manufacturer)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->manufacturer);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The manufacturer or establishment associated with the process.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $manufacturer
     * @return static
     */
    public function addManufacturer(FHIRReference $manufacturer): self
    {
        if (!isset($this->manufacturer)) {
            $this->manufacturer = [];
        }
        $this->manufacturer[] = $manufacturer;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The manufacturer or establishment associated with the process.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$manufacturer
     * @return static
     */
    public function setManufacturer(FHIRReference ...$manufacturer): self
    {
        if ([] === $manufacturer) {
            unset($this->manufacturer);
            return $this;
        }
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A regulator which oversees the operation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getRegulator(): null|FHIRReference
    {
        return $this->regulator ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A regulator which oversees the operation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $regulator
     * @return static
     */
    public function setRegulator(null|FHIRReference $regulator): self
    {
        if (null === $regulator) {
            unset($this->regulator);
            return $this;
        }
        $this->regulator = $regulator;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicinalProductManufacturingBusinessOperation)) {
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
            } else if (self::FIELD_OPERATION_TYPE === $cen) {
                $type->setOperationType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHORISATION_REFERENCE_NUMBER === $cen) {
                $type->setAuthorisationReferenceNumber(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EFFECTIVE_DATE === $cen) {
                $type->setEffectiveDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONFIDENTIALITY_INDICATOR === $cen) {
                $type->setConfidentialityIndicator(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANUFACTURER === $cen) {
                $type->addManufacturer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REGULATOR === $cen) {
                $type->setRegulator(FHIRReference::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EFFECTIVE_DATE])) {
            if (isset($type->effectiveDate)) {
                $type->effectiveDate->setValue((string)$attributes[self::FIELD_EFFECTIVE_DATE]);
            } else {
                $type->setEffectiveDate((string)$attributes[self::FIELD_EFFECTIVE_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EFFECTIVE_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->effectiveDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EFFECTIVE_DATE]) {
            $xw->writeAttribute(self::FIELD_EFFECTIVE_DATE, $this->effectiveDate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->operationType)) {
            $xw->startElement(self::FIELD_OPERATION_TYPE);
            $this->operationType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->authorisationReferenceNumber)) {
            $xw->startElement(self::FIELD_AUTHORISATION_REFERENCE_NUMBER);
            $this->authorisationReferenceNumber->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->effectiveDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EFFECTIVE_DATE]
                || $this->effectiveDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EFFECTIVE_DATE);
            $this->effectiveDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EFFECTIVE_DATE]);
            $xw->endElement();
        }
        if (isset($this->confidentialityIndicator)) {
            $xw->startElement(self::FIELD_CONFIDENTIALITY_INDICATOR);
            $this->confidentialityIndicator->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->manufacturer)) {
            foreach ($this->manufacturer as $v) {
                $xw->startElement(self::FIELD_MANUFACTURER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->regulator)) {
            $xw->startElement(self::FIELD_REGULATOR);
            $this->regulator->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation
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
        } else if (!($type instanceof FHIRMedicinalProductManufacturingBusinessOperation)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->operationType) || property_exists($decoded, self::FIELD_OPERATION_TYPE)) {
            if (is_array($decoded->operationType)) {
                $type->setOperationType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->operationType), $config));
            } else {
                $type->setOperationType(FHIRCodeableConcept::jsonUnserialize($decoded->operationType, $config));
            }
        }
        if (isset($decoded->authorisationReferenceNumber) || property_exists($decoded, self::FIELD_AUTHORISATION_REFERENCE_NUMBER)) {
            if (is_array($decoded->authorisationReferenceNumber)) {
                $type->setAuthorisationReferenceNumber(FHIRIdentifier::jsonUnserialize(reset($decoded->authorisationReferenceNumber), $config));
            } else {
                $type->setAuthorisationReferenceNumber(FHIRIdentifier::jsonUnserialize($decoded->authorisationReferenceNumber, $config));
            }
        }
        if (isset($decoded->effectiveDate)
            || isset($decoded->_effectiveDate)
            || property_exists($decoded, self::FIELD_EFFECTIVE_DATE)
            || property_exists($decoded, self::FIELD_EFFECTIVE_DATE_EXT)) {
            $v = $decoded->_effectiveDate ?? new \stdClass();
            $v->value = $decoded->effectiveDate ?? null;
            $type->setEffectiveDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->confidentialityIndicator) || property_exists($decoded, self::FIELD_CONFIDENTIALITY_INDICATOR)) {
            if (is_array($decoded->confidentialityIndicator)) {
                $type->setConfidentialityIndicator(FHIRCodeableConcept::jsonUnserialize(reset($decoded->confidentialityIndicator), $config));
            } else {
                $type->setConfidentialityIndicator(FHIRCodeableConcept::jsonUnserialize($decoded->confidentialityIndicator, $config));
            }
        }
        if (isset($decoded->manufacturer) || property_exists($decoded, self::FIELD_MANUFACTURER)) {
            if (is_object($decoded->manufacturer)) {
                $vals = [$decoded->manufacturer];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MANUFACTURER, true);
            } else {
                $vals = $decoded->manufacturer;
            }
            foreach($vals as $v) {
                $type->addManufacturer(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->regulator) || property_exists($decoded, self::FIELD_REGULATOR)) {
            if (is_array($decoded->regulator)) {
                $type->setRegulator(FHIRReference::jsonUnserialize(reset($decoded->regulator), $config));
            } else {
                $type->setRegulator(FHIRReference::jsonUnserialize($decoded->regulator, $config));
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
        if (isset($this->operationType)) {
            $out->operationType = $this->operationType;
        }
        if (isset($this->authorisationReferenceNumber)) {
            $out->authorisationReferenceNumber = $this->authorisationReferenceNumber;
        }
        if (isset($this->effectiveDate)) {
            if (null !== ($val = $this->effectiveDate->getValue())) {
                $out->effectiveDate = $val;
            }
            if ($this->effectiveDate->_nonValueFieldDefined()) {
                $ext = $this->effectiveDate->jsonSerialize();
                unset($ext->value);
                $out->_effectiveDate = $ext;
            }
        }
        if (isset($this->confidentialityIndicator)) {
            $out->confidentialityIndicator = $this->confidentialityIndicator;
        }
        if (isset($this->manufacturer) && [] !== $this->manufacturer) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MANUFACTURER) && 1 === count($this->manufacturer)) {
                $out->manufacturer = $this->manufacturer[0];
            } else {
                $out->manufacturer = $this->manufacturer;
            }
        }
        if (isset($this->regulator)) {
            $out->regulator = $this->regulator;
        }
        return $out;
    }
}
