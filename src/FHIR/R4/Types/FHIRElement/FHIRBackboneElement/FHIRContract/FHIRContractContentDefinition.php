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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContractResourcePublicationStatusCodesList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourcePublicationStatusCodes;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
 * policy or agreement.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRContractContentDefinition extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONTRACT_DOT_CONTENT_DEFINITION;

    /* class_default.php:56 */
    public const FIELD_TYPE = 'type';
    public const FIELD_SUB_TYPE = 'subType';
    public const FIELD_PUBLISHER = 'publisher';
    public const FIELD_PUBLICATION_DATE = 'publicationDate';
    public const FIELD_PUBLICATION_DATE_EXT = '_publicationDate';
    public const FIELD_PUBLICATION_STATUS = 'publicationStatus';
    public const FIELD_PUBLICATION_STATUS_EXT = '_publicationStatus';
    public const FIELD_COPYRIGHT = 'copyright';
    public const FIELD_COPYRIGHT_EXT = '_copyright';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_TYPE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_PUBLICATION_STATUS => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_PUBLICATION_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PUBLICATION_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COPYRIGHT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Precusory content structure and use, i.e., a boilerplate, template, application
     * for a contract such as an insurance policy or benefits under a program, e.g.,
     * workers compensation.
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
     * Detailed Precusory content type.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $subType;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization that published the Contract precursor content.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $publisher;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date (and optionally time) when the contract was published. The date must
     * change when the business version changes and it must change if the status code
     * changes. In addition, it should change when the substantive content of the
     * contract changes.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $publicationDate;
    /**
     * Status of the publication of contract content.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * amended | appended | cancelled | disputed | entered-in-error | executable |
     * executed | negotiable | offered | policy | rejected | renewed | revoked |
     * resolved | terminated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourcePublicationStatusCodes
     */
    #[FHIRContractResourcePublicationStatusCodes]
    protected FHIRContractResourcePublicationStatusCodes $publicationStatus;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A copyright statement relating to Contract precursor content. Copyright
     * statements are generally legal restrictions on the use and publishing of the
     * Contract precursor content.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $copyright;

    /* constructor.php:61 */
    /**
     * FHIRContractContentDefinition Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $subType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $publisher
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $publicationDate
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContractResourcePublicationStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourcePublicationStatusCodes $publicationStatus
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $copyright
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRCodeableConcept $subType = null,
                                null|FHIRReference $publisher = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $publicationDate = null,
                                null|string|FHIRContractResourcePublicationStatusCodesList|FHIRContractResourcePublicationStatusCodes $publicationStatus = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $copyright = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $subType) {
            $this->setSubType($subType);
        }
        if (null !== $publisher) {
            $this->setPublisher($publisher);
        }
        if (null !== $publicationDate) {
            $this->setPublicationDate($publicationDate);
        }
        if (null !== $publicationStatus) {
            $this->setPublicationStatus($publicationStatus);
        }
        if (null !== $copyright) {
            $this->setCopyright($copyright);
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
     * Precusory content structure and use, i.e., a boilerplate, template, application
     * for a contract such as an insurance policy or benefits under a program, e.g.,
     * workers compensation.
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
     * Precusory content structure and use, i.e., a boilerplate, template, application
     * for a contract such as an insurance policy or benefits under a program, e.g.,
     * workers compensation.
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
     * Detailed Precusory content type.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSubType(): null|FHIRCodeableConcept
    {
        return $this->subType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Detailed Precusory content type.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $subType
     * @return static
     */
    public function setSubType(null|FHIRCodeableConcept $subType): self
    {
        if (null === $subType) {
            unset($this->subType);
            return $this;
        }
        $this->subType = $subType;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization that published the Contract precursor content.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPublisher(): null|FHIRReference
    {
        return $this->publisher ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or organization that published the Contract precursor content.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $publisher
     * @return static
     */
    public function setPublisher(null|FHIRReference $publisher): self
    {
        if (null === $publisher) {
            unset($this->publisher);
            return $this;
        }
        $this->publisher = $publisher;
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
     * The date (and optionally time) when the contract was published. The date must
     * change when the business version changes and it must change if the status code
     * changes. In addition, it should change when the substantive content of the
     * contract changes.
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
     * The date (and optionally time) when the contract was published. The date must
     * change when the business version changes and it must change if the status code
     * changes. In addition, it should change when the substantive content of the
     * contract changes.
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
     * Status of the publication of contract content.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * amended | appended | cancelled | disputed | entered-in-error | executable |
     * executed | negotiable | offered | policy | rejected | renewed | revoked |
     * resolved | terminated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourcePublicationStatusCodes
     */
    public function getPublicationStatus(): null|FHIRContractResourcePublicationStatusCodes
    {
        return $this->publicationStatus ?? null;
    }

    /**
     * Status of the publication of contract content.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * amended | appended | cancelled | disputed | entered-in-error | executable |
     * executed | negotiable | offered | policy | rejected | renewed | revoked |
     * resolved | terminated.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRContractResourcePublicationStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContractResourcePublicationStatusCodes $publicationStatus
     * @return static
     */
    public function setPublicationStatus(null|string|FHIRContractResourcePublicationStatusCodesList|FHIRContractResourcePublicationStatusCodes $publicationStatus): self
    {
        if (null === $publicationStatus) {
            unset($this->publicationStatus);
            return $this;
        }
        if (!($publicationStatus instanceof FHIRContractResourcePublicationStatusCodes)) {
            $publicationStatus = new FHIRContractResourcePublicationStatusCodes(value: $publicationStatus);
        }
        $this->publicationStatus = $publicationStatus;
        return $this;
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A copyright statement relating to Contract precursor content. Copyright
     * statements are generally legal restrictions on the use and publishing of the
     * Contract precursor content.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getCopyright(): null|FHIRMarkdown
    {
        return $this->copyright ?? null;
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A copyright statement relating to Contract precursor content. Copyright
     * statements are generally legal restrictions on the use and publishing of the
     * Contract precursor content.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $copyright
     * @return static
     */
    public function setCopyright(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $copyright): self
    {
        if (null === $copyright) {
            unset($this->copyright);
            return $this;
        }
        if (!($copyright instanceof FHIRMarkdown)) {
            $copyright = new FHIRMarkdown(value: $copyright);
        }
        $this->copyright = $copyright;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRContractContentDefinition)) {
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
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUB_TYPE === $cen) {
                $type->setSubType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PUBLISHER === $cen) {
                $type->setPublisher(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PUBLICATION_DATE === $cen) {
                $type->setPublicationDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PUBLICATION_STATUS === $cen) {
                $type->setPublicationStatus(FHIRContractResourcePublicationStatusCodes::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COPYRIGHT === $cen) {
                $type->setCopyright(FHIRMarkdown::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PUBLICATION_DATE])) {
            if (isset($type->publicationDate)) {
                $type->publicationDate->setValue((string)$attributes[self::FIELD_PUBLICATION_DATE]);
            } else {
                $type->setPublicationDate((string)$attributes[self::FIELD_PUBLICATION_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PUBLICATION_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PUBLICATION_STATUS])) {
            if (isset($type->publicationStatus)) {
                $type->publicationStatus->setValue((string)$attributes[self::FIELD_PUBLICATION_STATUS]);
            } else {
                $type->setPublicationStatus((string)$attributes[self::FIELD_PUBLICATION_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PUBLICATION_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COPYRIGHT])) {
            if (isset($type->copyright)) {
                $type->copyright->setValue((string)$attributes[self::FIELD_COPYRIGHT]);
            } else {
                $type->setCopyright((string)$attributes[self::FIELD_COPYRIGHT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COPYRIGHT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->publicationDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PUBLICATION_DATE]) {
            $xw->writeAttribute(self::FIELD_PUBLICATION_DATE, $this->publicationDate->_getValueAsString());
        }
        if (isset($this->publicationStatus) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PUBLICATION_STATUS]) {
            $xw->writeAttribute(self::FIELD_PUBLICATION_STATUS, $this->publicationStatus->_getValueAsString());
        }
        if (isset($this->copyright) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COPYRIGHT]) {
            $xw->writeAttribute(self::FIELD_COPYRIGHT, $this->copyright->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subType)) {
            $xw->startElement(self::FIELD_SUB_TYPE);
            $this->subType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->publisher)) {
            $xw->startElement(self::FIELD_PUBLISHER);
            $this->publisher->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->publicationDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PUBLICATION_DATE]
                || $this->publicationDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PUBLICATION_DATE);
            $this->publicationDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PUBLICATION_DATE]);
            $xw->endElement();
        }
        if (isset($this->publicationStatus)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PUBLICATION_STATUS]
                || $this->publicationStatus->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PUBLICATION_STATUS);
            $this->publicationStatus->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PUBLICATION_STATUS]);
            $xw->endElement();
        }
        if (isset($this->copyright)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COPYRIGHT]
                || $this->copyright->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COPYRIGHT);
            $this->copyright->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COPYRIGHT]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractContentDefinition
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
        } else if (!($type instanceof FHIRContractContentDefinition)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->subType) || property_exists($decoded, self::FIELD_SUB_TYPE)) {
            if (is_array($decoded->subType)) {
                $type->setSubType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->subType), $config));
            } else {
                $type->setSubType(FHIRCodeableConcept::jsonUnserialize($decoded->subType, $config));
            }
        }
        if (isset($decoded->publisher) || property_exists($decoded, self::FIELD_PUBLISHER)) {
            if (is_array($decoded->publisher)) {
                $type->setPublisher(FHIRReference::jsonUnserialize(reset($decoded->publisher), $config));
            } else {
                $type->setPublisher(FHIRReference::jsonUnserialize($decoded->publisher, $config));
            }
        }
        if (isset($decoded->publicationDate)
            || isset($decoded->_publicationDate)
            || property_exists($decoded, self::FIELD_PUBLICATION_DATE)
            || property_exists($decoded, self::FIELD_PUBLICATION_DATE_EXT)) {
            $v = $decoded->_publicationDate ?? new \stdClass();
            $v->value = $decoded->publicationDate ?? null;
            $type->setPublicationDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->publicationStatus)
            || isset($decoded->_publicationStatus)
            || property_exists($decoded, self::FIELD_PUBLICATION_STATUS)
            || property_exists($decoded, self::FIELD_PUBLICATION_STATUS_EXT)) {
            $v = $decoded->_publicationStatus ?? new \stdClass();
            $v->value = $decoded->publicationStatus ?? null;
            $type->setPublicationStatus(FHIRContractResourcePublicationStatusCodes::jsonUnserialize($v, $config));
        }
        if (isset($decoded->copyright)
            || isset($decoded->_copyright)
            || property_exists($decoded, self::FIELD_COPYRIGHT)
            || property_exists($decoded, self::FIELD_COPYRIGHT_EXT)) {
            $v = $decoded->_copyright ?? new \stdClass();
            $v->value = $decoded->copyright ?? null;
            $type->setCopyright(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->subType)) {
            $out->subType = $this->subType;
        }
        if (isset($this->publisher)) {
            $out->publisher = $this->publisher;
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
        if (isset($this->publicationStatus)) {
            if (null !== ($val = $this->publicationStatus->getValue())) {
                $out->publicationStatus = $val;
            }
            if ($this->publicationStatus->_nonValueFieldDefined()) {
                $ext = $this->publicationStatus->jsonSerialize();
                unset($ext->value);
                $out->_publicationStatus = $ext;
            }
        }
        if (isset($this->copyright)) {
            if (null !== ($val = $this->copyright->getValue())) {
                $out->copyright = $val;
            }
            if ($this->copyright->_nonValueFieldDefined()) {
                $ext = $this->copyright->jsonSerialize();
                unset($ext->value);
                $out->_copyright = $ext;
            }
        }
        return $out;
    }
}
