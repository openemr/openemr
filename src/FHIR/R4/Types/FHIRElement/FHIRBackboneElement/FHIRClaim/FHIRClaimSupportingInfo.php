<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A provider issued list of professional services and products which have been
 * provided, or are to be provided, to a patient which is sent to an insurer for
 * reimbursement.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRClaimSupportingInfo extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CLAIM_DOT_SUPPORTING_INFO;

    /* class_default.php:56 */
    public const FIELD_SEQUENCE = 'sequence';
    public const FIELD_SEQUENCE_EXT = '_sequence';
    public const FIELD_CATEGORY = 'category';
    public const FIELD_CODE = 'code';
    public const FIELD_TIMING_DATE = 'timingDate';
    public const FIELD_TIMING_DATE_EXT = '_timingDate';
    public const FIELD_TIMING_PERIOD = 'timingPeriod';
    public const FIELD_VALUE_BOOLEAN = 'valueBoolean';
    public const FIELD_VALUE_BOOLEAN_EXT = '_valueBoolean';
    public const FIELD_VALUE_STRING = 'valueString';
    public const FIELD_VALUE_STRING_EXT = '_valueString';
    public const FIELD_VALUE_QUANTITY = 'valueQuantity';
    public const FIELD_VALUE_ATTACHMENT = 'valueAttachment';
    public const FIELD_VALUE_REFERENCE = 'valueReference';
    public const FIELD_REASON = 'reason';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_SEQUENCE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_CATEGORY => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SEQUENCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TIMING_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_BOOLEAN => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A number to uniquely identify supporting information entries.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $sequence;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The general class of the information supplied: information; exception; accident,
     * employment; onset, etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $category;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * System and code pertaining to the specific information regarding special
     * conditions relating to the setting, treatment or patient for which care is
     * sought.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $code;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when or period to which this information refers. (choose any one of
     * timing*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $timingDate;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date when or period to which this information refers. (choose any one of
     * timing*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $timingPeriod;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $valueBoolean;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $valueString;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $valueQuantity;
    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    #[FHIRAttachment]
    protected FHIRAttachment $valueAttachment;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $valueReference;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides the reason in the situation where a reason code is required in addition
     * to the content.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $reason;

    /* constructor.php:61 */
    /**
     * FHIRClaimSupportingInfo Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $sequence
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $category
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $timingDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $timingPeriod
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $valueBoolean
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $valueString
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $valueQuantity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $valueAttachment
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $valueReference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reason
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $sequence = null,
                                null|FHIRCodeableConcept $category = null,
                                null|FHIRCodeableConcept $code = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $timingDate = null,
                                null|FHIRPeriod $timingPeriod = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $valueBoolean = null,
                                null|string|FHIRStringPrimitive|FHIRString $valueString = null,
                                null|FHIRQuantity $valueQuantity = null,
                                null|FHIRAttachment $valueAttachment = null,
                                null|FHIRReference $valueReference = null,
                                null|FHIRCodeableConcept $reason = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $sequence) {
            $this->setSequence($sequence);
        }
        if (null !== $category) {
            $this->setCategory($category);
        }
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $timingDate) {
            $this->setTimingDate($timingDate);
        }
        if (null !== $timingPeriod) {
            $this->setTimingPeriod($timingPeriod);
        }
        if (null !== $valueBoolean) {
            $this->setValueBoolean($valueBoolean);
        }
        if (null !== $valueString) {
            $this->setValueString($valueString);
        }
        if (null !== $valueQuantity) {
            $this->setValueQuantity($valueQuantity);
        }
        if (null !== $valueAttachment) {
            $this->setValueAttachment($valueAttachment);
        }
        if (null !== $valueReference) {
            $this->setValueReference($valueReference);
        }
        if (null !== $reason) {
            $this->setReason($reason);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A number to uniquely identify supporting information entries.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getSequence(): null|FHIRPositiveInt
    {
        return $this->sequence ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A number to uniquely identify supporting information entries.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $sequence
     * @return static
     */
    public function setSequence(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $sequence): self
    {
        if (null === $sequence) {
            unset($this->sequence);
            return $this;
        }
        if (!($sequence instanceof FHIRPositiveInt)) {
            $sequence = new FHIRPositiveInt(value: $sequence);
        }
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The general class of the information supplied: information; exception; accident,
     * employment; onset, etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory(): null|FHIRCodeableConcept
    {
        return $this->category ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The general class of the information supplied: information; exception; accident,
     * employment; onset, etc.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $category
     * @return static
     */
    public function setCategory(null|FHIRCodeableConcept $category): self
    {
        if (null === $category) {
            unset($this->category);
            return $this;
        }
        $this->category = $category;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * System and code pertaining to the specific information regarding special
     * conditions relating to the setting, treatment or patient for which care is
     * sought.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCode(): null|FHIRCodeableConcept
    {
        return $this->code ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * System and code pertaining to the specific information regarding special
     * conditions relating to the setting, treatment or patient for which care is
     * sought.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @return static
     */
    public function setCode(null|FHIRCodeableConcept $code): self
    {
        if (null === $code) {
            unset($this->code);
            return $this;
        }
        $this->code = $code;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when or period to which this information refers. (choose any one of
     * timing*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getTimingDate(): null|FHIRDate
    {
        return $this->timingDate ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when or period to which this information refers. (choose any one of
     * timing*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $timingDate
     * @return static
     */
    public function setTimingDate(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $timingDate): self
    {
        if (null === $timingDate) {
            unset($this->timingDate);
            return $this;
        }
        if (!($timingDate instanceof FHIRDate)) {
            $timingDate = new FHIRDate(value: $timingDate);
        }
        $this->timingDate = $timingDate;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date when or period to which this information refers. (choose any one of
     * timing*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getTimingPeriod(): null|FHIRPeriod
    {
        return $this->timingPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date when or period to which this information refers. (choose any one of
     * timing*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $timingPeriod
     * @return static
     */
    public function setTimingPeriod(null|FHIRPeriod $timingPeriod): self
    {
        if (null === $timingPeriod) {
            unset($this->timingPeriod);
            return $this;
        }
        $this->timingPeriod = $timingPeriod;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getValueBoolean(): null|FHIRBoolean
    {
        return $this->valueBoolean ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $valueBoolean
     * @return static
     */
    public function setValueBoolean(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $valueBoolean): self
    {
        if (null === $valueBoolean) {
            unset($this->valueBoolean);
            return $this;
        }
        if (!($valueBoolean instanceof FHIRBoolean)) {
            $valueBoolean = new FHIRBoolean(value: $valueBoolean);
        }
        $this->valueBoolean = $valueBoolean;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getValueString(): null|FHIRString
    {
        return $this->valueString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $valueString
     * @return static
     */
    public function setValueString(null|string|FHIRStringPrimitive|FHIRString $valueString): self
    {
        if (null === $valueString) {
            unset($this->valueString);
            return $this;
        }
        if (!($valueString instanceof FHIRString)) {
            $valueString = new FHIRString(value: $valueString);
        }
        $this->valueString = $valueString;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getValueQuantity(): null|FHIRQuantity
    {
        return $this->valueQuantity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $valueQuantity
     * @return static
     */
    public function setValueQuantity(null|FHIRQuantity $valueQuantity): self
    {
        if (null === $valueQuantity) {
            unset($this->valueQuantity);
            return $this;
        }
        $this->valueQuantity = $valueQuantity;
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    public function getValueAttachment(): null|FHIRAttachment
    {
        return $this->valueAttachment ?? null;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $valueAttachment
     * @return static
     */
    public function setValueAttachment(null|FHIRAttachment $valueAttachment): self
    {
        if (null === $valueAttachment) {
            unset($this->valueAttachment);
            return $this;
        }
        $this->valueAttachment = $valueAttachment;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getValueReference(): null|FHIRReference
    {
        return $this->valueReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional data or information such as resources, documents, images etc.
     * including references to the data or the actual inclusion of the data. (choose
     * any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $valueReference
     * @return static
     */
    public function setValueReference(null|FHIRReference $valueReference): self
    {
        if (null === $valueReference) {
            unset($this->valueReference);
            return $this;
        }
        $this->valueReference = $valueReference;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides the reason in the situation where a reason code is required in addition
     * to the content.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getReason(): null|FHIRCodeableConcept
    {
        return $this->reason ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides the reason in the situation where a reason code is required in addition
     * to the content.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reason
     * @return static
     */
    public function setReason(null|FHIRCodeableConcept $reason): self
    {
        if (null === $reason) {
            unset($this->reason);
            return $this;
        }
        $this->reason = $reason;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim\FHIRClaimSupportingInfo $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim\FHIRClaimSupportingInfo
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRClaimSupportingInfo)) {
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
            } else if (self::FIELD_SEQUENCE === $cen) {
                $type->setSequence(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->setCategory(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIMING_DATE === $cen) {
                $type->setTimingDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIMING_PERIOD === $cen) {
                $type->setTimingPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_BOOLEAN === $cen) {
                $type->setValueBoolean(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_STRING === $cen) {
                $type->setValueString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_QUANTITY === $cen) {
                $type->setValueQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_ATTACHMENT === $cen) {
                $type->setValueAttachment(FHIRAttachment::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_REFERENCE === $cen) {
                $type->setValueReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON === $cen) {
                $type->setReason(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SEQUENCE])) {
            if (isset($type->sequence)) {
                $type->sequence->setValue((string)$attributes[self::FIELD_SEQUENCE]);
            } else {
                $type->setSequence((string)$attributes[self::FIELD_SEQUENCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SEQUENCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TIMING_DATE])) {
            if (isset($type->timingDate)) {
                $type->timingDate->setValue((string)$attributes[self::FIELD_TIMING_DATE]);
            } else {
                $type->setTimingDate((string)$attributes[self::FIELD_TIMING_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TIMING_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_BOOLEAN])) {
            if (isset($type->valueBoolean)) {
                $type->valueBoolean->setValue((string)$attributes[self::FIELD_VALUE_BOOLEAN]);
            } else {
                $type->setValueBoolean((string)$attributes[self::FIELD_VALUE_BOOLEAN]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_BOOLEAN, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_STRING])) {
            if (isset($type->valueString)) {
                $type->valueString->setValue((string)$attributes[self::FIELD_VALUE_STRING]);
            } else {
                $type->setValueString((string)$attributes[self::FIELD_VALUE_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->sequence) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SEQUENCE]) {
            $xw->writeAttribute(self::FIELD_SEQUENCE, $this->sequence->_getValueAsString());
        }
        if (isset($this->timingDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TIMING_DATE]) {
            $xw->writeAttribute(self::FIELD_TIMING_DATE, $this->timingDate->_getValueAsString());
        }
        if (isset($this->valueBoolean) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_BOOLEAN]) {
            $xw->writeAttribute(self::FIELD_VALUE_BOOLEAN, $this->valueBoolean->_getValueAsString());
        }
        if (isset($this->valueString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_STRING]) {
            $xw->writeAttribute(self::FIELD_VALUE_STRING, $this->valueString->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->sequence)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SEQUENCE]
                || $this->sequence->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SEQUENCE);
            $this->sequence->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SEQUENCE]);
            $xw->endElement();
        }
        if (isset($this->category)) {
            $xw->startElement(self::FIELD_CATEGORY);
            $this->category->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->timingDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TIMING_DATE]
                || $this->timingDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TIMING_DATE);
            $this->timingDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TIMING_DATE]);
            $xw->endElement();
        }
        if (isset($this->timingPeriod)) {
            $xw->startElement(self::FIELD_TIMING_PERIOD);
            $this->timingPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueBoolean)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_BOOLEAN]
                || $this->valueBoolean->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_BOOLEAN);
            $this->valueBoolean->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_BOOLEAN]);
            $xw->endElement();
        }
        if (isset($this->valueString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_STRING]
                || $this->valueString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_STRING);
            $this->valueString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_STRING]);
            $xw->endElement();
        }
        if (isset($this->valueQuantity)) {
            $xw->startElement(self::FIELD_VALUE_QUANTITY);
            $this->valueQuantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueAttachment)) {
            $xw->startElement(self::FIELD_VALUE_ATTACHMENT);
            $this->valueAttachment->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueReference)) {
            $xw->startElement(self::FIELD_VALUE_REFERENCE);
            $this->valueReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->reason)) {
            $xw->startElement(self::FIELD_REASON);
            $this->reason->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim\FHIRClaimSupportingInfo $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClaim\FHIRClaimSupportingInfo
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
        } else if (!($type instanceof FHIRClaimSupportingInfo)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->sequence)
            || isset($decoded->_sequence)
            || property_exists($decoded, self::FIELD_SEQUENCE)
            || property_exists($decoded, self::FIELD_SEQUENCE_EXT)) {
            $v = $decoded->_sequence ?? new \stdClass();
            $v->value = $decoded->sequence ?? null;
            $type->setSequence(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->category) || property_exists($decoded, self::FIELD_CATEGORY)) {
            if (is_array($decoded->category)) {
                $type->setCategory(FHIRCodeableConcept::jsonUnserialize(reset($decoded->category), $config));
            } else {
                $type->setCategory(FHIRCodeableConcept::jsonUnserialize($decoded->category, $config));
            }
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
            }
        }
        if (isset($decoded->timingDate)
            || isset($decoded->_timingDate)
            || property_exists($decoded, self::FIELD_TIMING_DATE)
            || property_exists($decoded, self::FIELD_TIMING_DATE_EXT)) {
            $v = $decoded->_timingDate ?? new \stdClass();
            $v->value = $decoded->timingDate ?? null;
            $type->setTimingDate(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->timingPeriod) || property_exists($decoded, self::FIELD_TIMING_PERIOD)) {
            if (is_array($decoded->timingPeriod)) {
                $type->setTimingPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->timingPeriod), $config));
            } else {
                $type->setTimingPeriod(FHIRPeriod::jsonUnserialize($decoded->timingPeriod, $config));
            }
        }
        if (isset($decoded->valueBoolean)
            || isset($decoded->_valueBoolean)
            || property_exists($decoded, self::FIELD_VALUE_BOOLEAN)
            || property_exists($decoded, self::FIELD_VALUE_BOOLEAN_EXT)) {
            $v = $decoded->_valueBoolean ?? new \stdClass();
            $v->value = $decoded->valueBoolean ?? null;
            $type->setValueBoolean(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueString)
            || isset($decoded->_valueString)
            || property_exists($decoded, self::FIELD_VALUE_STRING)
            || property_exists($decoded, self::FIELD_VALUE_STRING_EXT)) {
            $v = $decoded->_valueString ?? new \stdClass();
            $v->value = $decoded->valueString ?? null;
            $type->setValueString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueQuantity) || property_exists($decoded, self::FIELD_VALUE_QUANTITY)) {
            if (is_array($decoded->valueQuantity)) {
                $type->setValueQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->valueQuantity), $config));
            } else {
                $type->setValueQuantity(FHIRQuantity::jsonUnserialize($decoded->valueQuantity, $config));
            }
        }
        if (isset($decoded->valueAttachment) || property_exists($decoded, self::FIELD_VALUE_ATTACHMENT)) {
            if (is_array($decoded->valueAttachment)) {
                $type->setValueAttachment(FHIRAttachment::jsonUnserialize(reset($decoded->valueAttachment), $config));
            } else {
                $type->setValueAttachment(FHIRAttachment::jsonUnserialize($decoded->valueAttachment, $config));
            }
        }
        if (isset($decoded->valueReference) || property_exists($decoded, self::FIELD_VALUE_REFERENCE)) {
            if (is_array($decoded->valueReference)) {
                $type->setValueReference(FHIRReference::jsonUnserialize(reset($decoded->valueReference), $config));
            } else {
                $type->setValueReference(FHIRReference::jsonUnserialize($decoded->valueReference, $config));
            }
        }
        if (isset($decoded->reason) || property_exists($decoded, self::FIELD_REASON)) {
            if (is_array($decoded->reason)) {
                $type->setReason(FHIRCodeableConcept::jsonUnserialize(reset($decoded->reason), $config));
            } else {
                $type->setReason(FHIRCodeableConcept::jsonUnserialize($decoded->reason, $config));
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
        if (isset($this->sequence)) {
            if (null !== ($val = $this->sequence->getValue())) {
                $out->sequence = $val;
            }
            if ($this->sequence->_nonValueFieldDefined()) {
                $ext = $this->sequence->jsonSerialize();
                unset($ext->value);
                $out->_sequence = $ext;
            }
        }
        if (isset($this->category)) {
            $out->category = $this->category;
        }
        if (isset($this->code)) {
            $out->code = $this->code;
        }
        if (isset($this->timingDate)) {
            if (null !== ($val = $this->timingDate->getValue())) {
                $out->timingDate = $val;
            }
            if ($this->timingDate->_nonValueFieldDefined()) {
                $ext = $this->timingDate->jsonSerialize();
                unset($ext->value);
                $out->_timingDate = $ext;
            }
        }
        if (isset($this->timingPeriod)) {
            $out->timingPeriod = $this->timingPeriod;
        }
        if (isset($this->valueBoolean)) {
            if (null !== ($val = $this->valueBoolean->getValue())) {
                $out->valueBoolean = $val;
            }
            if ($this->valueBoolean->_nonValueFieldDefined()) {
                $ext = $this->valueBoolean->jsonSerialize();
                unset($ext->value);
                $out->_valueBoolean = $ext;
            }
        }
        if (isset($this->valueString)) {
            if (null !== ($val = $this->valueString->getValue())) {
                $out->valueString = $val;
            }
            if ($this->valueString->_nonValueFieldDefined()) {
                $ext = $this->valueString->jsonSerialize();
                unset($ext->value);
                $out->_valueString = $ext;
            }
        }
        if (isset($this->valueQuantity)) {
            $out->valueQuantity = $this->valueQuantity;
        }
        if (isset($this->valueAttachment)) {
            $out->valueAttachment = $this->valueAttachment;
        }
        if (isset($this->valueReference)) {
            $out->valueReference = $this->valueReference;
        }
        if (isset($this->reason)) {
            $out->reason = $this->reason;
        }
        return $out;
    }
}
