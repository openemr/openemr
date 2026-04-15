<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;

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
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultAttestation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Describes validation requirements, source(s), status and dates for one or more
 * elements.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRVerificationResult extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_VERIFICATION_RESULT;

    /* class_default.php:56 */
    public const FIELD_TARGET = 'target';
    public const FIELD_TARGET_LOCATION = 'targetLocation';
    public const FIELD_TARGET_LOCATION_EXT = '_targetLocation';
    public const FIELD_NEED = 'need';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_STATUS_DATE = 'statusDate';
    public const FIELD_STATUS_DATE_EXT = '_statusDate';
    public const FIELD_VALIDATION_TYPE = 'validationType';
    public const FIELD_VALIDATION_PROCESS = 'validationProcess';
    public const FIELD_FREQUENCY = 'frequency';
    public const FIELD_LAST_PERFORMED = 'lastPerformed';
    public const FIELD_LAST_PERFORMED_EXT = '_lastPerformed';
    public const FIELD_NEXT_SCHEDULED = 'nextScheduled';
    public const FIELD_NEXT_SCHEDULED_EXT = '_nextScheduled';
    public const FIELD_FAILURE_ACTION = 'failureAction';
    public const FIELD_PRIMARY_SOURCE = 'primarySource';
    public const FIELD_ATTESTATION = 'attestation';
    public const FIELD_VALIDATOR = 'validator';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LAST_PERFORMED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NEXT_SCHEDULED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource that was validated.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $target;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The fhirpath location(s) within the resource that was validated.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $targetLocation;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The frequency with which the target must be validated (none; initial; periodic).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $need;
    /**
     * The validation status of the target.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The validation status of the target (attested; validated; in process; requires
     * revalidation; validation failed; revalidation failed).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStatus
     */
    #[FHIRStatus]
    protected FHIRStatus $status;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the validation status was updated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $statusDate;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * What the target is validated against (nothing; primary source; multiple
     * sources).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $validationType;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The primary process by which the target is validated (edit check; value set;
     * primary source; multiple sources; standalone; in context).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $validationProcess;
    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Frequency of revalidation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    #[FHIRTiming]
    protected FHIRTiming $frequency;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date/time validation was last completed (including failed validations).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $lastPerformed;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when target is next validated, if appropriate.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $nextScheduled;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The result if validation fails (fatal; warning; record only; none).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $failureAction;
    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the primary source(s) involved in validation.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource>
     */
    #[FHIRVerificationResultPrimarySource]
    protected array $primarySource;
    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the entity attesting to information.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultAttestation
     */
    #[FHIRVerificationResultAttestation]
    protected FHIRVerificationResultAttestation $attestation;
    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the entity validating information.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator>
     */
    #[FHIRVerificationResultValidator]
    protected array $validator;

    /* constructor.php:61 */
    /**
     * FHIRVerificationResult Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $target
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $targetLocation
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $need
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStatus $status
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $statusDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $validationType
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $validationProcess
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $frequency
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $lastPerformed
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $nextScheduled
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $failureAction
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource> $primarySource
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultAttestation $attestation
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator> $validator
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRNarrative $text = null,
                                null|iterable $contained = null,
                                null|iterable $extension = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $target = null,
                                null|iterable $targetLocation = null,
                                null|FHIRCodeableConcept $need = null,
                                null|string|FHIRStatusList|FHIRStatus $status = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $statusDate = null,
                                null|FHIRCodeableConcept $validationType = null,
                                null|iterable $validationProcess = null,
                                null|FHIRTiming $frequency = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $lastPerformed = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $nextScheduled = null,
                                null|FHIRCodeableConcept $failureAction = null,
                                null|iterable $primarySource = null,
                                null|FHIRVerificationResultAttestation $attestation = null,
                                null|iterable $validator = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            text: $text,
                            contained: $contained,
                            extension: $extension,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $target) {
            $this->setTarget(...$target);
        }
        if (null !== $targetLocation) {
            $this->setTargetLocation(...$targetLocation);
        }
        if (null !== $need) {
            $this->setNeed($need);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $statusDate) {
            $this->setStatusDate($statusDate);
        }
        if (null !== $validationType) {
            $this->setValidationType($validationType);
        }
        if (null !== $validationProcess) {
            $this->setValidationProcess(...$validationProcess);
        }
        if (null !== $frequency) {
            $this->setFrequency($frequency);
        }
        if (null !== $lastPerformed) {
            $this->setLastPerformed($lastPerformed);
        }
        if (null !== $nextScheduled) {
            $this->setNextScheduled($nextScheduled);
        }
        if (null !== $failureAction) {
            $this->setFailureAction($failureAction);
        }
        if (null !== $primarySource) {
            $this->setPrimarySource(...$primarySource);
        }
        if (null !== $attestation) {
            $this->setAttestation($attestation);
        }
        if (null !== $validator) {
            $this->setValidator(...$validator);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource that was validated.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getTarget(): array
    {
        return $this->target ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getTargetIterator(): iterable
    {
        if (!isset($this->target)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->target);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource that was validated.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $target
     * @return static
     */
    public function addTarget(FHIRReference $target): self
    {
        if (!isset($this->target)) {
            $this->target = [];
        }
        $this->target[] = $target;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource that was validated.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$target
     * @return static
     */
    public function setTarget(FHIRReference ...$target): self
    {
        if ([] === $target) {
            unset($this->target);
            return $this;
        }
        $this->target = $target;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The fhirpath location(s) within the resource that was validated.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getTargetLocation(): array
    {
        return $this->targetLocation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getTargetLocationIterator(): iterable
    {
        if (!isset($this->targetLocation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->targetLocation);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The fhirpath location(s) within the resource that was validated.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $targetLocation
     * @return static
     */
    public function addTargetLocation(string|FHIRStringPrimitive|FHIRString $targetLocation): self
    {
        if (!($targetLocation instanceof FHIRString)) {
            $targetLocation = new FHIRString(value: $targetLocation);
        }
        if (!isset($this->targetLocation)) {
            $this->targetLocation = [];
        }
        $this->targetLocation[] = $targetLocation;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The fhirpath location(s) within the resource that was validated.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$targetLocation
     * @return static
     */
    public function setTargetLocation(string|FHIRStringPrimitive|FHIRString ...$targetLocation): self
    {
        if ([] === $targetLocation) {
            unset($this->targetLocation);
            return $this;
        }
        $this->targetLocation = [];
        foreach($targetLocation as $v) {
            if ($v instanceof FHIRString) {
                $this->targetLocation[] = $v;
            } else {
                $this->targetLocation[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The frequency with which the target must be validated (none; initial; periodic).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getNeed(): null|FHIRCodeableConcept
    {
        return $this->need ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The frequency with which the target must be validated (none; initial; periodic).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $need
     * @return static
     */
    public function setNeed(null|FHIRCodeableConcept $need): self
    {
        if (null === $need) {
            unset($this->need);
            return $this;
        }
        $this->need = $need;
        return $this;
    }

    /**
     * The validation status of the target.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The validation status of the target (attested; validated; in process; requires
     * revalidation; validation failed; revalidation failed).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStatus
     */
    public function getStatus(): null|FHIRStatus
    {
        return $this->status ?? null;
    }

    /**
     * The validation status of the target.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The validation status of the target (attested; validated; in process; requires
     * revalidation; validation failed; revalidation failed).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRStatusList|FHIRStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRStatus)) {
            $status = new FHIRStatus(value: $status);
        }
        $this->status = $status;
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
     * When the validation status was updated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getStatusDate(): null|FHIRDateTime
    {
        return $this->statusDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the validation status was updated.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $statusDate
     * @return static
     */
    public function setStatusDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $statusDate): self
    {
        if (null === $statusDate) {
            unset($this->statusDate);
            return $this;
        }
        if (!($statusDate instanceof FHIRDateTime)) {
            $statusDate = new FHIRDateTime(value: $statusDate);
        }
        $this->statusDate = $statusDate;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * What the target is validated against (nothing; primary source; multiple
     * sources).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getValidationType(): null|FHIRCodeableConcept
    {
        return $this->validationType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * What the target is validated against (nothing; primary source; multiple
     * sources).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $validationType
     * @return static
     */
    public function setValidationType(null|FHIRCodeableConcept $validationType): self
    {
        if (null === $validationType) {
            unset($this->validationType);
            return $this;
        }
        $this->validationType = $validationType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The primary process by which the target is validated (edit check; value set;
     * primary source; multiple sources; standalone; in context).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getValidationProcess(): array
    {
        return $this->validationProcess ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getValidationProcessIterator(): iterable
    {
        if (!isset($this->validationProcess)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->validationProcess);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The primary process by which the target is validated (edit check; value set;
     * primary source; multiple sources; standalone; in context).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $validationProcess
     * @return static
     */
    public function addValidationProcess(FHIRCodeableConcept $validationProcess): self
    {
        if (!isset($this->validationProcess)) {
            $this->validationProcess = [];
        }
        $this->validationProcess[] = $validationProcess;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The primary process by which the target is validated (edit check; value set;
     * primary source; multiple sources; standalone; in context).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$validationProcess
     * @return static
     */
    public function setValidationProcess(FHIRCodeableConcept ...$validationProcess): self
    {
        if ([] === $validationProcess) {
            unset($this->validationProcess);
            return $this;
        }
        $this->validationProcess = $validationProcess;
        return $this;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Frequency of revalidation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getFrequency(): null|FHIRTiming
    {
        return $this->frequency ?? null;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Frequency of revalidation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $frequency
     * @return static
     */
    public function setFrequency(null|FHIRTiming $frequency): self
    {
        if (null === $frequency) {
            unset($this->frequency);
            return $this;
        }
        $this->frequency = $frequency;
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
     * The date/time validation was last completed (including failed validations).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getLastPerformed(): null|FHIRDateTime
    {
        return $this->lastPerformed ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date/time validation was last completed (including failed validations).
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $lastPerformed
     * @return static
     */
    public function setLastPerformed(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $lastPerformed): self
    {
        if (null === $lastPerformed) {
            unset($this->lastPerformed);
            return $this;
        }
        if (!($lastPerformed instanceof FHIRDateTime)) {
            $lastPerformed = new FHIRDateTime(value: $lastPerformed);
        }
        $this->lastPerformed = $lastPerformed;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when target is next validated, if appropriate.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getNextScheduled(): null|FHIRDate
    {
        return $this->nextScheduled ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when target is next validated, if appropriate.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $nextScheduled
     * @return static
     */
    public function setNextScheduled(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $nextScheduled): self
    {
        if (null === $nextScheduled) {
            unset($this->nextScheduled);
            return $this;
        }
        if (!($nextScheduled instanceof FHIRDate)) {
            $nextScheduled = new FHIRDate(value: $nextScheduled);
        }
        $this->nextScheduled = $nextScheduled;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The result if validation fails (fatal; warning; record only; none).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getFailureAction(): null|FHIRCodeableConcept
    {
        return $this->failureAction ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The result if validation fails (fatal; warning; record only; none).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $failureAction
     * @return static
     */
    public function setFailureAction(null|FHIRCodeableConcept $failureAction): self
    {
        if (null === $failureAction) {
            unset($this->failureAction);
            return $this;
        }
        $this->failureAction = $failureAction;
        return $this;
    }

    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the primary source(s) involved in validation.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource>
     */
    public function getPrimarySource(): array
    {
        return $this->primarySource ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource>
     */
    public function getPrimarySourceIterator(): iterable
    {
        if (!isset($this->primarySource)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->primarySource);
    }

    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the primary source(s) involved in validation.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource $primarySource
     * @return static
     */
    public function addPrimarySource(FHIRVerificationResultPrimarySource $primarySource): self
    {
        if (!isset($this->primarySource)) {
            $this->primarySource = [];
        }
        $this->primarySource[] = $primarySource;
        return $this;
    }

    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the primary source(s) involved in validation.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultPrimarySource ...$primarySource
     * @return static
     */
    public function setPrimarySource(FHIRVerificationResultPrimarySource ...$primarySource): self
    {
        if ([] === $primarySource) {
            unset($this->primarySource);
            return $this;
        }
        $this->primarySource = $primarySource;
        return $this;
    }

    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the entity attesting to information.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultAttestation
     */
    public function getAttestation(): null|FHIRVerificationResultAttestation
    {
        return $this->attestation ?? null;
    }

    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the entity attesting to information.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultAttestation $attestation
     * @return static
     */
    public function setAttestation(null|FHIRVerificationResultAttestation $attestation): self
    {
        if (null === $attestation) {
            unset($this->attestation);
            return $this;
        }
        $this->attestation = $attestation;
        return $this;
    }

    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the entity validating information.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator>
     */
    public function getValidator(): array
    {
        return $this->validator ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator>
     */
    public function getValidatorIterator(): iterable
    {
        if (!isset($this->validator)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->validator);
    }

    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the entity validating information.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator $validator
     * @return static
     */
    public function addValidator(FHIRVerificationResultValidator $validator): self
    {
        if (!isset($this->validator)) {
            $this->validator = [];
        }
        $this->validator[] = $validator;
        return $this;
    }

    /**
     * Describes validation requirements, source(s), status and dates for one or more
     * elements.
     *
     * Information about the entity validating information.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVerificationResult\FHIRVerificationResultValidator ...$validator
     * @return static
     */
    public function setValidator(FHIRVerificationResultValidator ...$validator): self
    {
        if ([] === $validator) {
            unset($this->validator);
            return $this;
        }
        $this->validator = $validator;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRVerificationResult $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRVerificationResult
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRVerificationResult)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRNarrative::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINED === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->addContained($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET === $cen) {
                $type->addTarget(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET_LOCATION === $cen) {
                $type->addTargetLocation(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NEED === $cen) {
                $type->setNeed(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS_DATE === $cen) {
                $type->setStatusDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDATION_TYPE === $cen) {
                $type->setValidationType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDATION_PROCESS === $cen) {
                $type->addValidationProcess(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FREQUENCY === $cen) {
                $type->setFrequency(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LAST_PERFORMED === $cen) {
                $type->setLastPerformed(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NEXT_SCHEDULED === $cen) {
                $type->setNextScheduled(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FAILURE_ACTION === $cen) {
                $type->setFailureAction(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIMARY_SOURCE === $cen) {
                $type->addPrimarySource(FHIRVerificationResultPrimarySource::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ATTESTATION === $cen) {
                $type->setAttestation(FHIRVerificationResultAttestation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDATOR === $cen) {
                $type->addValidator(FHIRVerificationResultValidator::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS_DATE])) {
            if (isset($type->statusDate)) {
                $type->statusDate->setValue((string)$attributes[self::FIELD_STATUS_DATE]);
            } else {
                $type->setStatusDate((string)$attributes[self::FIELD_STATUS_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LAST_PERFORMED])) {
            if (isset($type->lastPerformed)) {
                $type->lastPerformed->setValue((string)$attributes[self::FIELD_LAST_PERFORMED]);
            } else {
                $type->setLastPerformed((string)$attributes[self::FIELD_LAST_PERFORMED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LAST_PERFORMED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NEXT_SCHEDULED])) {
            if (isset($type->nextScheduled)) {
                $type->nextScheduled->setValue((string)$attributes[self::FIELD_NEXT_SCHEDULED]);
            } else {
                $type->setNextScheduled((string)$attributes[self::FIELD_NEXT_SCHEDULED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NEXT_SCHEDULED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('VerificationResult', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->statusDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS_DATE]) {
            $xw->writeAttribute(self::FIELD_STATUS_DATE, $this->statusDate->_getValueAsString());
        }
        if (isset($this->lastPerformed) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LAST_PERFORMED]) {
            $xw->writeAttribute(self::FIELD_LAST_PERFORMED, $this->lastPerformed->_getValueAsString());
        }
        if (isset($this->nextScheduled) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NEXT_SCHEDULED]) {
            $xw->writeAttribute(self::FIELD_NEXT_SCHEDULED, $this->nextScheduled->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->target)) {
            foreach ($this->target as $v) {
                $xw->startElement(self::FIELD_TARGET);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->targetLocation) && [] !== $this->targetLocation) {
            foreach($this->targetLocation as $v) {
                $xw->startElement(self::FIELD_TARGET_LOCATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->need)) {
            $xw->startElement(self::FIELD_NEED);
            $this->need->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->statusDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS_DATE]
                || $this->statusDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS_DATE);
            $this->statusDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS_DATE]);
            $xw->endElement();
        }
        if (isset($this->validationType)) {
            $xw->startElement(self::FIELD_VALIDATION_TYPE);
            $this->validationType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->validationProcess)) {
            foreach ($this->validationProcess as $v) {
                $xw->startElement(self::FIELD_VALIDATION_PROCESS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->frequency)) {
            $xw->startElement(self::FIELD_FREQUENCY);
            $this->frequency->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->lastPerformed)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LAST_PERFORMED]
                || $this->lastPerformed->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LAST_PERFORMED);
            $this->lastPerformed->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LAST_PERFORMED]);
            $xw->endElement();
        }
        if (isset($this->nextScheduled)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NEXT_SCHEDULED]
                || $this->nextScheduled->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NEXT_SCHEDULED);
            $this->nextScheduled->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NEXT_SCHEDULED]);
            $xw->endElement();
        }
        if (isset($this->failureAction)) {
            $xw->startElement(self::FIELD_FAILURE_ACTION);
            $this->failureAction->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->primarySource)) {
            foreach ($this->primarySource as $v) {
                $xw->startElement(self::FIELD_PRIMARY_SOURCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->attestation)) {
            $xw->startElement(self::FIELD_ATTESTATION);
            $this->attestation->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->validator)) {
            foreach ($this->validator as $v) {
                $xw->startElement(self::FIELD_VALIDATOR);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRVerificationResult $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRVerificationResult
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIRVerificationResult)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->target) || property_exists($decoded, self::FIELD_TARGET)) {
            if (is_object($decoded->target)) {
                $vals = [$decoded->target];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TARGET, true);
            } else {
                $vals = $decoded->target;
            }
            foreach($vals as $v) {
                $type->addTarget(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->targetLocation)
            || isset($decoded->_targetLocation)
            || property_exists($decoded, self::FIELD_TARGET_LOCATION)
            || property_exists($decoded, self::FIELD_TARGET_LOCATION_EXT)) {
            $vals = (array)($decoded->targetLocation ?? []);
            $exts = (array)($decoded->_targetLocation ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addTargetLocation(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->need) || property_exists($decoded, self::FIELD_NEED)) {
            if (is_array($decoded->need)) {
                $type->setNeed(FHIRCodeableConcept::jsonUnserialize(reset($decoded->need), $config));
            } else {
                $type->setNeed(FHIRCodeableConcept::jsonUnserialize($decoded->need, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->statusDate)
            || isset($decoded->_statusDate)
            || property_exists($decoded, self::FIELD_STATUS_DATE)
            || property_exists($decoded, self::FIELD_STATUS_DATE_EXT)) {
            $v = $decoded->_statusDate ?? new \stdClass();
            $v->value = $decoded->statusDate ?? null;
            $type->setStatusDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->validationType) || property_exists($decoded, self::FIELD_VALIDATION_TYPE)) {
            if (is_array($decoded->validationType)) {
                $type->setValidationType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->validationType), $config));
            } else {
                $type->setValidationType(FHIRCodeableConcept::jsonUnserialize($decoded->validationType, $config));
            }
        }
        if (isset($decoded->validationProcess) || property_exists($decoded, self::FIELD_VALIDATION_PROCESS)) {
            if (is_object($decoded->validationProcess)) {
                $vals = [$decoded->validationProcess];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_VALIDATION_PROCESS, true);
            } else {
                $vals = $decoded->validationProcess;
            }
            foreach($vals as $v) {
                $type->addValidationProcess(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->frequency) || property_exists($decoded, self::FIELD_FREQUENCY)) {
            if (is_array($decoded->frequency)) {
                $type->setFrequency(FHIRTiming::jsonUnserialize(reset($decoded->frequency), $config));
            } else {
                $type->setFrequency(FHIRTiming::jsonUnserialize($decoded->frequency, $config));
            }
        }
        if (isset($decoded->lastPerformed)
            || isset($decoded->_lastPerformed)
            || property_exists($decoded, self::FIELD_LAST_PERFORMED)
            || property_exists($decoded, self::FIELD_LAST_PERFORMED_EXT)) {
            $v = $decoded->_lastPerformed ?? new \stdClass();
            $v->value = $decoded->lastPerformed ?? null;
            $type->setLastPerformed(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->nextScheduled)
            || isset($decoded->_nextScheduled)
            || property_exists($decoded, self::FIELD_NEXT_SCHEDULED)
            || property_exists($decoded, self::FIELD_NEXT_SCHEDULED_EXT)) {
            $v = $decoded->_nextScheduled ?? new \stdClass();
            $v->value = $decoded->nextScheduled ?? null;
            $type->setNextScheduled(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->failureAction) || property_exists($decoded, self::FIELD_FAILURE_ACTION)) {
            if (is_array($decoded->failureAction)) {
                $type->setFailureAction(FHIRCodeableConcept::jsonUnserialize(reset($decoded->failureAction), $config));
            } else {
                $type->setFailureAction(FHIRCodeableConcept::jsonUnserialize($decoded->failureAction, $config));
            }
        }
        if (isset($decoded->primarySource) || property_exists($decoded, self::FIELD_PRIMARY_SOURCE)) {
            if (is_object($decoded->primarySource)) {
                $vals = [$decoded->primarySource];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PRIMARY_SOURCE, true);
            } else {
                $vals = $decoded->primarySource;
            }
            foreach($vals as $v) {
                $type->addPrimarySource(FHIRVerificationResultPrimarySource::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->attestation) || property_exists($decoded, self::FIELD_ATTESTATION)) {
            if (is_array($decoded->attestation)) {
                $type->setAttestation(FHIRVerificationResultAttestation::jsonUnserialize(reset($decoded->attestation), $config));
            } else {
                $type->setAttestation(FHIRVerificationResultAttestation::jsonUnserialize($decoded->attestation, $config));
            }
        }
        if (isset($decoded->validator) || property_exists($decoded, self::FIELD_VALIDATOR)) {
            if (is_object($decoded->validator)) {
                $vals = [$decoded->validator];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_VALIDATOR, true);
            } else {
                $vals = $decoded->validator;
            }
            foreach($vals as $v) {
                $type->addValidator(FHIRVerificationResultValidator::jsonUnserialize($v, $config));
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
        if (isset($this->target) && [] !== $this->target) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TARGET) && 1 === count($this->target)) {
                $out->target = $this->target[0];
            } else {
                $out->target = $this->target;
            }
        }
        if (isset($this->targetLocation) && [] !== $this->targetLocation) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->targetLocation as $v) {
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
                $out->targetLocation = $vals;
            }
            if ($hasExts) {
                $out->_targetLocation = $exts;
            }
        }
        if (isset($this->need)) {
            $out->need = $this->need;
        }
        if (isset($this->status)) {
            if (null !== ($val = $this->status->getValue())) {
                $out->status = $val;
            }
            if ($this->status->_nonValueFieldDefined()) {
                $ext = $this->status->jsonSerialize();
                unset($ext->value);
                $out->_status = $ext;
            }
        }
        if (isset($this->statusDate)) {
            if (null !== ($val = $this->statusDate->getValue())) {
                $out->statusDate = $val;
            }
            if ($this->statusDate->_nonValueFieldDefined()) {
                $ext = $this->statusDate->jsonSerialize();
                unset($ext->value);
                $out->_statusDate = $ext;
            }
        }
        if (isset($this->validationType)) {
            $out->validationType = $this->validationType;
        }
        if (isset($this->validationProcess) && [] !== $this->validationProcess) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_VALIDATION_PROCESS) && 1 === count($this->validationProcess)) {
                $out->validationProcess = $this->validationProcess[0];
            } else {
                $out->validationProcess = $this->validationProcess;
            }
        }
        if (isset($this->frequency)) {
            $out->frequency = $this->frequency;
        }
        if (isset($this->lastPerformed)) {
            if (null !== ($val = $this->lastPerformed->getValue())) {
                $out->lastPerformed = $val;
            }
            if ($this->lastPerformed->_nonValueFieldDefined()) {
                $ext = $this->lastPerformed->jsonSerialize();
                unset($ext->value);
                $out->_lastPerformed = $ext;
            }
        }
        if (isset($this->nextScheduled)) {
            if (null !== ($val = $this->nextScheduled->getValue())) {
                $out->nextScheduled = $val;
            }
            if ($this->nextScheduled->_nonValueFieldDefined()) {
                $ext = $this->nextScheduled->jsonSerialize();
                unset($ext->value);
                $out->_nextScheduled = $ext;
            }
        }
        if (isset($this->failureAction)) {
            $out->failureAction = $this->failureAction;
        }
        if (isset($this->primarySource) && [] !== $this->primarySource) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PRIMARY_SOURCE) && 1 === count($this->primarySource)) {
                $out->primarySource = $this->primarySource[0];
            } else {
                $out->primarySource = $this->primarySource;
            }
        }
        if (isset($this->attestation)) {
            $out->attestation = $this->attestation;
        }
        if (isset($this->validator) && [] !== $this->validator) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_VALIDATOR) && 1 === count($this->validator)) {
                $out->validator = $this->validator[0];
            } else {
                $out->validator = $this->validator;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
