<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Describes validation requirements, source(s), status and dates for one or more
 * elements.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRVerificationResultPrimarySource extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_VERIFICATION_RESULT_DOT_PRIMARY_SOURCE;

    /* class_default.php:56 */
    public const FIELD_WHO = 'who';
    public const FIELD_TYPE = 'type';
    public const FIELD_COMMUNICATION_METHOD = 'communicationMethod';
    public const FIELD_VALIDATION_STATUS = 'validationStatus';
    public const FIELD_VALIDATION_DATE = 'validationDate';
    public const FIELD_VALIDATION_DATE_EXT = '_validationDate';
    public const FIELD_CAN_PUSH_UPDATES = 'canPushUpdates';
    public const FIELD_PUSH_TYPE_AVAILABLE = 'pushTypeAvailable';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_VALIDATION_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the primary source.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $who;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of primary source (License Board; Primary Education; Continuing Education;
     * Postal Service; Relationship owner; Registration Authority; legal source;
     * issuing source; authoritative source).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $type;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Method for communicating with the primary source (manual; API; Push).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $communicationMethod;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Status of the validation of the target against the primary source (successful;
     * failed; unknown).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $validationStatus;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the target was validated against the primary source.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $validationDate;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Ability of the primary source to push updates/alerts (yes; no; undetermined).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $canPushUpdates;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of alerts/updates the primary source can send (specific requested changes;
     * any changes; as defined by source).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $pushTypeAvailable;

    /* constructor.php:61 */
    /**
     * FHIRVerificationResultPrimarySource Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $who
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $type
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $communicationMethod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $validationStatus
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $validationDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $canPushUpdates
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $pushTypeAvailable
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRReference $who = null,
                                null|iterable $type = null,
                                null|iterable $communicationMethod = null,
                                null|FHIRCodeableConcept $validationStatus = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $validationDate = null,
                                null|FHIRCodeableConcept $canPushUpdates = null,
                                null|iterable $pushTypeAvailable = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $who) {
            $this->setWho($who);
        }
        if (null !== $type) {
            $this->setType(...$type);
        }
        if (null !== $communicationMethod) {
            $this->setCommunicationMethod(...$communicationMethod);
        }
        if (null !== $validationStatus) {
            $this->setValidationStatus($validationStatus);
        }
        if (null !== $validationDate) {
            $this->setValidationDate($validationDate);
        }
        if (null !== $canPushUpdates) {
            $this->setCanPushUpdates($canPushUpdates);
        }
        if (null !== $pushTypeAvailable) {
            $this->setPushTypeAvailable(...$pushTypeAvailable);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the primary source.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getWho(): null|FHIRReference
    {
        return $this->who ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the primary source.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $who
     * @return static
     */
    public function setWho(null|FHIRReference $who): self
    {
        if (null === $who) {
            unset($this->who);
            return $this;
        }
        $this->who = $who;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of primary source (License Board; Primary Education; Continuing Education;
     * Postal Service; Relationship owner; Registration Authority; legal source;
     * issuing source; authoritative source).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getType(): array
    {
        return $this->type ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getTypeIterator(): iterable
    {
        if (!isset($this->type)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->type);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of primary source (License Board; Primary Education; Continuing Education;
     * Postal Service; Relationship owner; Registration Authority; legal source;
     * issuing source; authoritative source).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function addType(FHIRCodeableConcept $type): self
    {
        if (!isset($this->type)) {
            $this->type = [];
        }
        $this->type[] = $type;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of primary source (License Board; Primary Education; Continuing Education;
     * Postal Service; Relationship owner; Registration Authority; legal source;
     * issuing source; authoritative source).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$type
     * @return static
     */
    public function setType(FHIRCodeableConcept ...$type): self
    {
        if ([] === $type) {
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
     * Method for communicating with the primary source (manual; API; Push).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCommunicationMethod(): array
    {
        return $this->communicationMethod ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCommunicationMethodIterator(): iterable
    {
        if (!isset($this->communicationMethod)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->communicationMethod);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Method for communicating with the primary source (manual; API; Push).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $communicationMethod
     * @return static
     */
    public function addCommunicationMethod(FHIRCodeableConcept $communicationMethod): self
    {
        if (!isset($this->communicationMethod)) {
            $this->communicationMethod = [];
        }
        $this->communicationMethod[] = $communicationMethod;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Method for communicating with the primary source (manual; API; Push).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$communicationMethod
     * @return static
     */
    public function setCommunicationMethod(FHIRCodeableConcept ...$communicationMethod): self
    {
        if ([] === $communicationMethod) {
            unset($this->communicationMethod);
            return $this;
        }
        $this->communicationMethod = $communicationMethod;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Status of the validation of the target against the primary source (successful;
     * failed; unknown).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getValidationStatus(): null|FHIRCodeableConcept
    {
        return $this->validationStatus ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Status of the validation of the target against the primary source (successful;
     * failed; unknown).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $validationStatus
     * @return static
     */
    public function setValidationStatus(null|FHIRCodeableConcept $validationStatus): self
    {
        if (null === $validationStatus) {
            unset($this->validationStatus);
            return $this;
        }
        $this->validationStatus = $validationStatus;
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
     * When the target was validated against the primary source.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getValidationDate(): null|FHIRDateTime
    {
        return $this->validationDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the target was validated against the primary source.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $validationDate
     * @return static
     */
    public function setValidationDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $validationDate): self
    {
        if (null === $validationDate) {
            unset($this->validationDate);
            return $this;
        }
        if (!($validationDate instanceof FHIRDateTime)) {
            $validationDate = new FHIRDateTime(value: $validationDate);
        }
        $this->validationDate = $validationDate;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Ability of the primary source to push updates/alerts (yes; no; undetermined).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCanPushUpdates(): null|FHIRCodeableConcept
    {
        return $this->canPushUpdates ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Ability of the primary source to push updates/alerts (yes; no; undetermined).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $canPushUpdates
     * @return static
     */
    public function setCanPushUpdates(null|FHIRCodeableConcept $canPushUpdates): self
    {
        if (null === $canPushUpdates) {
            unset($this->canPushUpdates);
            return $this;
        }
        $this->canPushUpdates = $canPushUpdates;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of alerts/updates the primary source can send (specific requested changes;
     * any changes; as defined by source).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPushTypeAvailable(): array
    {
        return $this->pushTypeAvailable ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPushTypeAvailableIterator(): iterable
    {
        if (!isset($this->pushTypeAvailable)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->pushTypeAvailable);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of alerts/updates the primary source can send (specific requested changes;
     * any changes; as defined by source).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $pushTypeAvailable
     * @return static
     */
    public function addPushTypeAvailable(FHIRCodeableConcept $pushTypeAvailable): self
    {
        if (!isset($this->pushTypeAvailable)) {
            $this->pushTypeAvailable = [];
        }
        $this->pushTypeAvailable[] = $pushTypeAvailable;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of alerts/updates the primary source can send (specific requested changes;
     * any changes; as defined by source).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$pushTypeAvailable
     * @return static
     */
    public function setPushTypeAvailable(FHIRCodeableConcept ...$pushTypeAvailable): self
    {
        if ([] === $pushTypeAvailable) {
            unset($this->pushTypeAvailable);
            return $this;
        }
        $this->pushTypeAvailable = $pushTypeAvailable;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRVerificationResultPrimarySource)) {
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
            } else if (self::FIELD_WHO === $cen) {
                $type->setWho(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->addType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMMUNICATION_METHOD === $cen) {
                $type->addCommunicationMethod(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDATION_STATUS === $cen) {
                $type->setValidationStatus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDATION_DATE === $cen) {
                $type->setValidationDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CAN_PUSH_UPDATES === $cen) {
                $type->setCanPushUpdates(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PUSH_TYPE_AVAILABLE === $cen) {
                $type->addPushTypeAvailable(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALIDATION_DATE])) {
            if (isset($type->validationDate)) {
                $type->validationDate->setValue((string)$attributes[self::FIELD_VALIDATION_DATE]);
            } else {
                $type->setValidationDate((string)$attributes[self::FIELD_VALIDATION_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALIDATION_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->validationDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALIDATION_DATE]) {
            $xw->writeAttribute(self::FIELD_VALIDATION_DATE, $this->validationDate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->who)) {
            $xw->startElement(self::FIELD_WHO);
            $this->who->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)) {
            foreach ($this->type as $v) {
                $xw->startElement(self::FIELD_TYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->communicationMethod)) {
            foreach ($this->communicationMethod as $v) {
                $xw->startElement(self::FIELD_COMMUNICATION_METHOD);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->validationStatus)) {
            $xw->startElement(self::FIELD_VALIDATION_STATUS);
            $this->validationStatus->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->validationDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALIDATION_DATE]
                || $this->validationDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALIDATION_DATE);
            $this->validationDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALIDATION_DATE]);
            $xw->endElement();
        }
        if (isset($this->canPushUpdates)) {
            $xw->startElement(self::FIELD_CAN_PUSH_UPDATES);
            $this->canPushUpdates->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->pushTypeAvailable)) {
            foreach ($this->pushTypeAvailable as $v) {
                $xw->startElement(self::FIELD_PUSH_TYPE_AVAILABLE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource
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
        } else if (!($type instanceof FHIRVerificationResultPrimarySource)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->who) || property_exists($decoded, self::FIELD_WHO)) {
            if (is_array($decoded->who)) {
                $type->setWho(FHIRReference::jsonUnserialize(reset($decoded->who), $config));
            } else {
                $type->setWho(FHIRReference::jsonUnserialize($decoded->who, $config));
            }
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_object($decoded->type)) {
                $vals = [$decoded->type];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TYPE, true);
            } else {
                $vals = $decoded->type;
            }
            foreach($vals as $v) {
                $type->addType(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->communicationMethod) || property_exists($decoded, self::FIELD_COMMUNICATION_METHOD)) {
            if (is_object($decoded->communicationMethod)) {
                $vals = [$decoded->communicationMethod];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COMMUNICATION_METHOD, true);
            } else {
                $vals = $decoded->communicationMethod;
            }
            foreach($vals as $v) {
                $type->addCommunicationMethod(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->validationStatus) || property_exists($decoded, self::FIELD_VALIDATION_STATUS)) {
            if (is_array($decoded->validationStatus)) {
                $type->setValidationStatus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->validationStatus), $config));
            } else {
                $type->setValidationStatus(FHIRCodeableConcept::jsonUnserialize($decoded->validationStatus, $config));
            }
        }
        if (isset($decoded->validationDate)
            || isset($decoded->_validationDate)
            || property_exists($decoded, self::FIELD_VALIDATION_DATE)
            || property_exists($decoded, self::FIELD_VALIDATION_DATE_EXT)) {
            $v = $decoded->_validationDate ?? new \stdClass();
            $v->value = $decoded->validationDate ?? null;
            $type->setValidationDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->canPushUpdates) || property_exists($decoded, self::FIELD_CAN_PUSH_UPDATES)) {
            if (is_array($decoded->canPushUpdates)) {
                $type->setCanPushUpdates(FHIRCodeableConcept::jsonUnserialize(reset($decoded->canPushUpdates), $config));
            } else {
                $type->setCanPushUpdates(FHIRCodeableConcept::jsonUnserialize($decoded->canPushUpdates, $config));
            }
        }
        if (isset($decoded->pushTypeAvailable) || property_exists($decoded, self::FIELD_PUSH_TYPE_AVAILABLE)) {
            if (is_object($decoded->pushTypeAvailable)) {
                $vals = [$decoded->pushTypeAvailable];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PUSH_TYPE_AVAILABLE, true);
            } else {
                $vals = $decoded->pushTypeAvailable;
            }
            foreach($vals as $v) {
                $type->addPushTypeAvailable(FHIRCodeableConcept::jsonUnserialize($v, $config));
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
        if (isset($this->who)) {
            $out->who = $this->who;
        }
        if (isset($this->type) && [] !== $this->type) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TYPE) && 1 === count($this->type)) {
                $out->type = $this->type[0];
            } else {
                $out->type = $this->type;
            }
        }
        if (isset($this->communicationMethod) && [] !== $this->communicationMethod) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COMMUNICATION_METHOD) && 1 === count($this->communicationMethod)) {
                $out->communicationMethod = $this->communicationMethod[0];
            } else {
                $out->communicationMethod = $this->communicationMethod;
            }
        }
        if (isset($this->validationStatus)) {
            $out->validationStatus = $this->validationStatus;
        }
        if (isset($this->validationDate)) {
            if (null !== ($val = $this->validationDate->getValue())) {
                $out->validationDate = $val;
            }
            if ($this->validationDate->_nonValueFieldDefined()) {
                $ext = $this->validationDate->jsonSerialize();
                unset($ext->value);
                $out->_validationDate = $ext;
            }
        }
        if (isset($this->canPushUpdates)) {
            $out->canPushUpdates = $this->canPushUpdates;
        }
        if (isset($this->pushTypeAvailable) && [] !== $this->pushTypeAvailable) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PUSH_TYPE_AVAILABLE) && 1 === count($this->pushTypeAvailable)) {
                $out->pushTypeAvailable = $this->pushTypeAvailable[0];
            } else {
                $out->pushTypeAvailable = $this->pushTypeAvailable;
            }
        }
        return $out;
    }
}
