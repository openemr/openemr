<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Describes the event of a patient being administered a vaccine or a record of an
 * immunization as reported by a patient, a clinician or another party.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRImmunizationEducation extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_IMMUNIZATION_DOT_EDUCATION;

    /* class_default.php:56 */
    public const FIELD_DOCUMENT_TYPE = 'documentType';
    public const FIELD_DOCUMENT_TYPE_EXT = '_documentType';
    public const FIELD_REFERENCE = 'reference';
    public const FIELD_REFERENCE_EXT = '_reference';
    public const FIELD_PUBLICATION_DATE = 'publicationDate';
    public const FIELD_PUBLICATION_DATE_EXT = '_publicationDate';
    public const FIELD_PRESENTATION_DATE = 'presentationDate';
    public const FIELD_PRESENTATION_DATE_EXT = '_presentationDate';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DOCUMENT_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_REFERENCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PUBLICATION_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PRESENTATION_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifier of the material presented to the patient.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $documentType;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference pointer to the educational material given to the patient if the
     * information was on line.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $reference;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date the educational material was published.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $publicationDate;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date the educational material was given to the patient.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $presentationDate;

    /* constructor.php:61 */
    /**
     * FHIRImmunizationEducation Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $documentType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $reference
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $publicationDate
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $presentationDate
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $documentType = null,
                                null|string|FHIRUriPrimitive|FHIRUri $reference = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $publicationDate = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $presentationDate = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $documentType) {
            $this->setDocumentType($documentType);
        }
        if (null !== $reference) {
            $this->setReference($reference);
        }
        if (null !== $publicationDate) {
            $this->setPublicationDate($publicationDate);
        }
        if (null !== $presentationDate) {
            $this->setPresentationDate($presentationDate);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifier of the material presented to the patient.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDocumentType(): null|FHIRString
    {
        return $this->documentType ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifier of the material presented to the patient.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $documentType
     * @return static
     */
    public function setDocumentType(null|string|FHIRStringPrimitive|FHIRString $documentType): self
    {
        if (null === $documentType) {
            unset($this->documentType);
            return $this;
        }
        if (!($documentType instanceof FHIRString)) {
            $documentType = new FHIRString(value: $documentType);
        }
        $this->documentType = $documentType;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference pointer to the educational material given to the patient if the
     * information was on line.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getReference(): null|FHIRUri
    {
        return $this->reference ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference pointer to the educational material given to the patient if the
     * information was on line.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $reference
     * @return static
     */
    public function setReference(null|string|FHIRUriPrimitive|FHIRUri $reference): self
    {
        if (null === $reference) {
            unset($this->reference);
            return $this;
        }
        if (!($reference instanceof FHIRUri)) {
            $reference = new FHIRUri(value: $reference);
        }
        $this->reference = $reference;
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
     * Date the educational material was published.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getPublicationDate(): null|FHIRDateTime
    {
        return $this->publicationDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date the educational material was published.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $publicationDate
     * @return static
     */
    public function setPublicationDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $publicationDate): self
    {
        if (null === $publicationDate) {
            unset($this->publicationDate);
            return $this;
        }
        if (!($publicationDate instanceof FHIRDateTime)) {
            $publicationDate = new FHIRDateTime(value: $publicationDate);
        }
        $this->publicationDate = $publicationDate;
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
     * Date the educational material was given to the patient.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getPresentationDate(): null|FHIRDateTime
    {
        return $this->presentationDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date the educational material was given to the patient.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $presentationDate
     * @return static
     */
    public function setPresentationDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $presentationDate): self
    {
        if (null === $presentationDate) {
            unset($this->presentationDate);
            return $this;
        }
        if (!($presentationDate instanceof FHIRDateTime)) {
            $presentationDate = new FHIRDateTime(value: $presentationDate);
        }
        $this->presentationDate = $presentationDate;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRImmunizationEducation)) {
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
            } else if (self::FIELD_DOCUMENT_TYPE === $cen) {
                $type->setDocumentType(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE === $cen) {
                $type->setReference(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PUBLICATION_DATE === $cen) {
                $type->setPublicationDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRESENTATION_DATE === $cen) {
                $type->setPresentationDate(FHIRDateTime::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DOCUMENT_TYPE])) {
            if (isset($type->documentType)) {
                $type->documentType->setValue((string)$attributes[self::FIELD_DOCUMENT_TYPE]);
            } else {
                $type->setDocumentType((string)$attributes[self::FIELD_DOCUMENT_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DOCUMENT_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REFERENCE])) {
            if (isset($type->reference)) {
                $type->reference->setValue((string)$attributes[self::FIELD_REFERENCE]);
            } else {
                $type->setReference((string)$attributes[self::FIELD_REFERENCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REFERENCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PUBLICATION_DATE])) {
            if (isset($type->publicationDate)) {
                $type->publicationDate->setValue((string)$attributes[self::FIELD_PUBLICATION_DATE]);
            } else {
                $type->setPublicationDate((string)$attributes[self::FIELD_PUBLICATION_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PUBLICATION_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PRESENTATION_DATE])) {
            if (isset($type->presentationDate)) {
                $type->presentationDate->setValue((string)$attributes[self::FIELD_PRESENTATION_DATE]);
            } else {
                $type->setPresentationDate((string)$attributes[self::FIELD_PRESENTATION_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PRESENTATION_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->documentType) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DOCUMENT_TYPE]) {
            $xw->writeAttribute(self::FIELD_DOCUMENT_TYPE, $this->documentType->_getValueAsString());
        }
        if (isset($this->reference) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REFERENCE]) {
            $xw->writeAttribute(self::FIELD_REFERENCE, $this->reference->_getValueAsString());
        }
        if (isset($this->publicationDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PUBLICATION_DATE]) {
            $xw->writeAttribute(self::FIELD_PUBLICATION_DATE, $this->publicationDate->_getValueAsString());
        }
        if (isset($this->presentationDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PRESENTATION_DATE]) {
            $xw->writeAttribute(self::FIELD_PRESENTATION_DATE, $this->presentationDate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->documentType)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DOCUMENT_TYPE]
                || $this->documentType->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DOCUMENT_TYPE);
            $this->documentType->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DOCUMENT_TYPE]);
            $xw->endElement();
        }
        if (isset($this->reference)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REFERENCE]
                || $this->reference->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REFERENCE);
            $this->reference->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REFERENCE]);
            $xw->endElement();
        }
        if (isset($this->publicationDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PUBLICATION_DATE]
                || $this->publicationDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PUBLICATION_DATE);
            $this->publicationDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PUBLICATION_DATE]);
            $xw->endElement();
        }
        if (isset($this->presentationDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PRESENTATION_DATE]
                || $this->presentationDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PRESENTATION_DATE);
            $this->presentationDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PRESENTATION_DATE]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation
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
        } else if (!($type instanceof FHIRImmunizationEducation)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->documentType)
            || isset($decoded->_documentType)
            || property_exists($decoded, self::FIELD_DOCUMENT_TYPE)
            || property_exists($decoded, self::FIELD_DOCUMENT_TYPE_EXT)) {
            $v = $decoded->_documentType ?? new \stdClass();
            $v->value = $decoded->documentType ?? null;
            $type->setDocumentType(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->reference)
            || isset($decoded->_reference)
            || property_exists($decoded, self::FIELD_REFERENCE)
            || property_exists($decoded, self::FIELD_REFERENCE_EXT)) {
            $v = $decoded->_reference ?? new \stdClass();
            $v->value = $decoded->reference ?? null;
            $type->setReference(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->publicationDate)
            || isset($decoded->_publicationDate)
            || property_exists($decoded, self::FIELD_PUBLICATION_DATE)
            || property_exists($decoded, self::FIELD_PUBLICATION_DATE_EXT)) {
            $v = $decoded->_publicationDate ?? new \stdClass();
            $v->value = $decoded->publicationDate ?? null;
            $type->setPublicationDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->presentationDate)
            || isset($decoded->_presentationDate)
            || property_exists($decoded, self::FIELD_PRESENTATION_DATE)
            || property_exists($decoded, self::FIELD_PRESENTATION_DATE_EXT)) {
            $v = $decoded->_presentationDate ?? new \stdClass();
            $v->value = $decoded->presentationDate ?? null;
            $type->setPresentationDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->documentType)) {
            if (null !== ($val = $this->documentType->getValue())) {
                $out->documentType = $val;
            }
            if ($this->documentType->_nonValueFieldDefined()) {
                $ext = $this->documentType->jsonSerialize();
                unset($ext->value);
                $out->_documentType = $ext;
            }
        }
        if (isset($this->reference)) {
            if (null !== ($val = $this->reference->getValue())) {
                $out->reference = $val;
            }
            if ($this->reference->_nonValueFieldDefined()) {
                $ext = $this->reference->jsonSerialize();
                unset($ext->value);
                $out->_reference = $ext;
            }
        }
        if (isset($this->publicationDate)) {
            if (null !== ($val = $this->publicationDate->getValue())) {
                $out->publicationDate = $val;
            }
            if ($this->publicationDate->_nonValueFieldDefined()) {
                $ext = $this->publicationDate->jsonSerialize();
                unset($ext->value);
                $out->_publicationDate = $ext;
            }
        }
        if (isset($this->presentationDate)) {
            if (null !== ($val = $this->presentationDate->getValue())) {
                $out->presentationDate = $val;
            }
            if ($this->presentationDate->_nonValueFieldDefined()) {
                $ext = $this->presentationDate->jsonSerialize();
                unset($ext->value);
                $out->_presentationDate = $ext;
            }
        }
        return $out;
    }
}
