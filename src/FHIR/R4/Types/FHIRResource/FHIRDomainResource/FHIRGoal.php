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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGoalLifecycleStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRGoal\FHIRGoalTarget;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGoalLifecycleStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
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
 * Describes the intended objective(s) for a patient, group or organization care,
 * for example, weight loss, restoring an activity of daily living, obtaining herd
 * immunity via immunization, meeting a process improvement objective, etc.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRGoal extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_GOAL;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_LIFECYCLE_STATUS = 'lifecycleStatus';
    public const FIELD_LIFECYCLE_STATUS_EXT = '_lifecycleStatus';
    public const FIELD_ACHIEVEMENT_STATUS = 'achievementStatus';
    public const FIELD_CATEGORY = 'category';
    public const FIELD_PRIORITY = 'priority';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_START_DATE = 'startDate';
    public const FIELD_START_DATE_EXT = '_startDate';
    public const FIELD_START_CODEABLE_CONCEPT = 'startCodeableConcept';
    public const FIELD_TARGET = 'target';
    public const FIELD_STATUS_DATE = 'statusDate';
    public const FIELD_STATUS_DATE_EXT = '_statusDate';
    public const FIELD_STATUS_REASON = 'statusReason';
    public const FIELD_STATUS_REASON_EXT = '_statusReason';
    public const FIELD_EXPRESSED_BY = 'expressedBy';
    public const FIELD_ADDRESSES = 'addresses';
    public const FIELD_NOTE = 'note';
    public const FIELD_OUTCOME_CODE = 'outcomeCode';
    public const FIELD_OUTCOME_REFERENCE = 'outcomeReference';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_LIFECYCLE_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_DESCRIPTION => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_SUBJECT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_LIFECYCLE_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_START_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS_REASON => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this goal by the performer or other systems
     * which remain constant as the resource is updated and propagates from server to
     * server.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * Codes that reflect the current state of a goal and whether the goal is still
     * being targeted.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The state of the goal throughout its lifecycle.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGoalLifecycleStatus
     */
    #[FHIRGoalLifecycleStatus]
    protected FHIRGoalLifecycleStatus $lifecycleStatus;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the progression, or lack thereof, towards the goal against the target.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $achievementStatus;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a category the goal falls within.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $category;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the mutually agreed level of importance associated with
     * reaching/sustaining the goal.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $priority;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Human-readable and/or coded description of a specific desired objective of care,
     * such as "control blood pressure" or "negotiate an obstacle course" or "dance
     * with child at wedding".
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $description;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the patient, group or organization for whom the goal is being
     * established.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $subject;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or event after which the goal should begin being pursued. (choose any
     * one of start*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $startDate;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or event after which the goal should begin being pursued. (choose any
     * one of start*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $startCodeableConcept;
    /**
     * Describes the intended objective(s) for a patient, group or organization care,
     * for example, weight loss, restoring an activity of daily living, obtaining herd
     * immunity via immunization, meeting a process improvement objective, etc.
     *
     * Indicates what should be done by when.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRGoal\FHIRGoalTarget>
     */
    #[FHIRGoalTarget]
    protected array $target;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies when the current status. I.e. When initially created, when achieved,
     * when cancelled, etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $statusDate;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Captures the reason for the current status.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $statusReason;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates whose goal this is - patient goal, practitioner goal, etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $expressedBy;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identified conditions and other health record elements that are intended to
     * be addressed by the goal.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $addresses;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Any comments related to the goal.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the change (or lack of change) at the point when the status of the
     * goal is assessed.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $outcomeCode;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details of what's changed (or not changed).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $outcomeReference;

    /* constructor.php:61 */
    /**
     * FHIRGoal Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGoalLifecycleStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGoalLifecycleStatus $lifecycleStatus
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $achievementStatus
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $category
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $priority
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $description
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $startDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $startCodeableConcept
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRGoal\FHIRGoalTarget> $target
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $statusDate
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $statusReason
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $expressedBy
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $addresses
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $outcomeCode
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $outcomeReference
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
                                null|iterable $identifier = null,
                                null|string|FHIRGoalLifecycleStatusList|FHIRGoalLifecycleStatus $lifecycleStatus = null,
                                null|FHIRCodeableConcept $achievementStatus = null,
                                null|iterable $category = null,
                                null|FHIRCodeableConcept $priority = null,
                                null|FHIRCodeableConcept $description = null,
                                null|FHIRReference $subject = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $startDate = null,
                                null|FHIRCodeableConcept $startCodeableConcept = null,
                                null|iterable $target = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $statusDate = null,
                                null|string|FHIRStringPrimitive|FHIRString $statusReason = null,
                                null|FHIRReference $expressedBy = null,
                                null|iterable $addresses = null,
                                null|iterable $note = null,
                                null|iterable $outcomeCode = null,
                                null|iterable $outcomeReference = null,
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
        if (null !== $identifier) {
            $this->setIdentifier(...$identifier);
        }
        if (null !== $lifecycleStatus) {
            $this->setLifecycleStatus($lifecycleStatus);
        }
        if (null !== $achievementStatus) {
            $this->setAchievementStatus($achievementStatus);
        }
        if (null !== $category) {
            $this->setCategory(...$category);
        }
        if (null !== $priority) {
            $this->setPriority($priority);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $startDate) {
            $this->setStartDate($startDate);
        }
        if (null !== $startCodeableConcept) {
            $this->setStartCodeableConcept($startCodeableConcept);
        }
        if (null !== $target) {
            $this->setTarget(...$target);
        }
        if (null !== $statusDate) {
            $this->setStatusDate($statusDate);
        }
        if (null !== $statusReason) {
            $this->setStatusReason($statusReason);
        }
        if (null !== $expressedBy) {
            $this->setExpressedBy($expressedBy);
        }
        if (null !== $addresses) {
            $this->setAddresses(...$addresses);
        }
        if (null !== $note) {
            $this->setNote(...$note);
        }
        if (null !== $outcomeCode) {
            $this->setOutcomeCode(...$outcomeCode);
        }
        if (null !== $outcomeReference) {
            $this->setOutcomeReference(...$outcomeReference);
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
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this goal by the performer or other systems
     * which remain constant as the resource is updated and propagates from server to
     * server.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifier(): array
    {
        return $this->identifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifierIterator(): iterable
    {
        if (!isset($this->identifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->identifier);
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this goal by the performer or other systems
     * which remain constant as the resource is updated and propagates from server to
     * server.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function addIdentifier(FHIRIdentifier $identifier): self
    {
        if (!isset($this->identifier)) {
            $this->identifier = [];
        }
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this goal by the performer or other systems
     * which remain constant as the resource is updated and propagates from server to
     * server.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier ...$identifier
     * @return static
     */
    public function setIdentifier(FHIRIdentifier ...$identifier): self
    {
        if ([] === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Codes that reflect the current state of a goal and whether the goal is still
     * being targeted.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The state of the goal throughout its lifecycle.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGoalLifecycleStatus
     */
    public function getLifecycleStatus(): null|FHIRGoalLifecycleStatus
    {
        return $this->lifecycleStatus ?? null;
    }

    /**
     * Codes that reflect the current state of a goal and whether the goal is still
     * being targeted.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The state of the goal throughout its lifecycle.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGoalLifecycleStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGoalLifecycleStatus $lifecycleStatus
     * @return static
     */
    public function setLifecycleStatus(null|string|FHIRGoalLifecycleStatusList|FHIRGoalLifecycleStatus $lifecycleStatus): self
    {
        if (null === $lifecycleStatus) {
            unset($this->lifecycleStatus);
            return $this;
        }
        if (!($lifecycleStatus instanceof FHIRGoalLifecycleStatus)) {
            $lifecycleStatus = new FHIRGoalLifecycleStatus(value: $lifecycleStatus);
        }
        $this->lifecycleStatus = $lifecycleStatus;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the progression, or lack thereof, towards the goal against the target.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getAchievementStatus(): null|FHIRCodeableConcept
    {
        return $this->achievementStatus ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the progression, or lack thereof, towards the goal against the target.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $achievementStatus
     * @return static
     */
    public function setAchievementStatus(null|FHIRCodeableConcept $achievementStatus): self
    {
        if (null === $achievementStatus) {
            unset($this->achievementStatus);
            return $this;
        }
        $this->achievementStatus = $achievementStatus;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a category the goal falls within.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCategory(): array
    {
        return $this->category ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCategoryIterator(): iterable
    {
        if (!isset($this->category)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->category);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a category the goal falls within.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $category
     * @return static
     */
    public function addCategory(FHIRCodeableConcept $category): self
    {
        if (!isset($this->category)) {
            $this->category = [];
        }
        $this->category[] = $category;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a category the goal falls within.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$category
     * @return static
     */
    public function setCategory(FHIRCodeableConcept ...$category): self
    {
        if ([] === $category) {
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
     * Identifies the mutually agreed level of importance associated with
     * reaching/sustaining the goal.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getPriority(): null|FHIRCodeableConcept
    {
        return $this->priority ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the mutually agreed level of importance associated with
     * reaching/sustaining the goal.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $priority
     * @return static
     */
    public function setPriority(null|FHIRCodeableConcept $priority): self
    {
        if (null === $priority) {
            unset($this->priority);
            return $this;
        }
        $this->priority = $priority;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Human-readable and/or coded description of a specific desired objective of care,
     * such as "control blood pressure" or "negotiate an obstacle course" or "dance
     * with child at wedding".
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDescription(): null|FHIRCodeableConcept
    {
        return $this->description ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Human-readable and/or coded description of a specific desired objective of care,
     * such as "control blood pressure" or "negotiate an obstacle course" or "dance
     * with child at wedding".
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $description
     * @return static
     */
    public function setDescription(null|FHIRCodeableConcept $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        $this->description = $description;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the patient, group or organization for whom the goal is being
     * established.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSubject(): null|FHIRReference
    {
        return $this->subject ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the patient, group or organization for whom the goal is being
     * established.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @return static
     */
    public function setSubject(null|FHIRReference $subject): self
    {
        if (null === $subject) {
            unset($this->subject);
            return $this;
        }
        $this->subject = $subject;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or event after which the goal should begin being pursued. (choose any
     * one of start*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getStartDate(): null|FHIRDate
    {
        return $this->startDate ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or event after which the goal should begin being pursued. (choose any
     * one of start*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $startDate
     * @return static
     */
    public function setStartDate(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $startDate): self
    {
        if (null === $startDate) {
            unset($this->startDate);
            return $this;
        }
        if (!($startDate instanceof FHIRDate)) {
            $startDate = new FHIRDate(value: $startDate);
        }
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or event after which the goal should begin being pursued. (choose any
     * one of start*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getStartCodeableConcept(): null|FHIRCodeableConcept
    {
        return $this->startCodeableConcept ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or event after which the goal should begin being pursued. (choose any
     * one of start*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $startCodeableConcept
     * @return static
     */
    public function setStartCodeableConcept(null|FHIRCodeableConcept $startCodeableConcept): self
    {
        if (null === $startCodeableConcept) {
            unset($this->startCodeableConcept);
            return $this;
        }
        $this->startCodeableConcept = $startCodeableConcept;
        return $this;
    }

    /**
     * Describes the intended objective(s) for a patient, group or organization care,
     * for example, weight loss, restoring an activity of daily living, obtaining herd
     * immunity via immunization, meeting a process improvement objective, etc.
     *
     * Indicates what should be done by when.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRGoal\FHIRGoalTarget>
     */
    public function getTarget(): array
    {
        return $this->target ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRGoal\FHIRGoalTarget>
     */
    public function getTargetIterator(): iterable
    {
        if (!isset($this->target)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->target);
    }

    /**
     * Describes the intended objective(s) for a patient, group or organization care,
     * for example, weight loss, restoring an activity of daily living, obtaining herd
     * immunity via immunization, meeting a process improvement objective, etc.
     *
     * Indicates what should be done by when.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRGoal\FHIRGoalTarget $target
     * @return static
     */
    public function addTarget(FHIRGoalTarget $target): self
    {
        if (!isset($this->target)) {
            $this->target = [];
        }
        $this->target[] = $target;
        return $this;
    }

    /**
     * Describes the intended objective(s) for a patient, group or organization care,
     * for example, weight loss, restoring an activity of daily living, obtaining herd
     * immunity via immunization, meeting a process improvement objective, etc.
     *
     * Indicates what should be done by when.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRGoal\FHIRGoalTarget ...$target
     * @return static
     */
    public function setTarget(FHIRGoalTarget ...$target): self
    {
        if ([] === $target) {
            unset($this->target);
            return $this;
        }
        $this->target = $target;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies when the current status. I.e. When initially created, when achieved,
     * when cancelled, etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getStatusDate(): null|FHIRDate
    {
        return $this->statusDate ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies when the current status. I.e. When initially created, when achieved,
     * when cancelled, etc.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $statusDate
     * @return static
     */
    public function setStatusDate(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $statusDate): self
    {
        if (null === $statusDate) {
            unset($this->statusDate);
            return $this;
        }
        if (!($statusDate instanceof FHIRDate)) {
            $statusDate = new FHIRDate(value: $statusDate);
        }
        $this->statusDate = $statusDate;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Captures the reason for the current status.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getStatusReason(): null|FHIRString
    {
        return $this->statusReason ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Captures the reason for the current status.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $statusReason
     * @return static
     */
    public function setStatusReason(null|string|FHIRStringPrimitive|FHIRString $statusReason): self
    {
        if (null === $statusReason) {
            unset($this->statusReason);
            return $this;
        }
        if (!($statusReason instanceof FHIRString)) {
            $statusReason = new FHIRString(value: $statusReason);
        }
        $this->statusReason = $statusReason;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates whose goal this is - patient goal, practitioner goal, etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getExpressedBy(): null|FHIRReference
    {
        return $this->expressedBy ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates whose goal this is - patient goal, practitioner goal, etc.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $expressedBy
     * @return static
     */
    public function setExpressedBy(null|FHIRReference $expressedBy): self
    {
        if (null === $expressedBy) {
            unset($this->expressedBy);
            return $this;
        }
        $this->expressedBy = $expressedBy;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identified conditions and other health record elements that are intended to
     * be addressed by the goal.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAddresses(): array
    {
        return $this->addresses ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAddressesIterator(): iterable
    {
        if (!isset($this->addresses)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->addresses);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identified conditions and other health record elements that are intended to
     * be addressed by the goal.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $addresses
     * @return static
     */
    public function addAddresses(FHIRReference $addresses): self
    {
        if (!isset($this->addresses)) {
            $this->addresses = [];
        }
        $this->addresses[] = $addresses;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The identified conditions and other health record elements that are intended to
     * be addressed by the goal.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$addresses
     * @return static
     */
    public function setAddresses(FHIRReference ...$addresses): self
    {
        if ([] === $addresses) {
            unset($this->addresses);
            return $this;
        }
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Any comments related to the goal.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNote(): array
    {
        return $this->note ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNoteIterator(): iterable
    {
        if (!isset($this->note)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->note);
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Any comments related to the goal.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation $note
     * @return static
     */
    public function addNote(FHIRAnnotation $note): self
    {
        if (!isset($this->note)) {
            $this->note = [];
        }
        $this->note[] = $note;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Any comments related to the goal.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation ...$note
     * @return static
     */
    public function setNote(FHIRAnnotation ...$note): self
    {
        if ([] === $note) {
            unset($this->note);
            return $this;
        }
        $this->note = $note;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the change (or lack of change) at the point when the status of the
     * goal is assessed.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getOutcomeCode(): array
    {
        return $this->outcomeCode ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getOutcomeCodeIterator(): iterable
    {
        if (!isset($this->outcomeCode)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->outcomeCode);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the change (or lack of change) at the point when the status of the
     * goal is assessed.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $outcomeCode
     * @return static
     */
    public function addOutcomeCode(FHIRCodeableConcept $outcomeCode): self
    {
        if (!isset($this->outcomeCode)) {
            $this->outcomeCode = [];
        }
        $this->outcomeCode[] = $outcomeCode;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the change (or lack of change) at the point when the status of the
     * goal is assessed.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$outcomeCode
     * @return static
     */
    public function setOutcomeCode(FHIRCodeableConcept ...$outcomeCode): self
    {
        if ([] === $outcomeCode) {
            unset($this->outcomeCode);
            return $this;
        }
        $this->outcomeCode = $outcomeCode;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details of what's changed (or not changed).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getOutcomeReference(): array
    {
        return $this->outcomeReference ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getOutcomeReferenceIterator(): iterable
    {
        if (!isset($this->outcomeReference)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->outcomeReference);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details of what's changed (or not changed).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $outcomeReference
     * @return static
     */
    public function addOutcomeReference(FHIRReference $outcomeReference): self
    {
        if (!isset($this->outcomeReference)) {
            $this->outcomeReference = [];
        }
        $this->outcomeReference[] = $outcomeReference;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details of what's changed (or not changed).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$outcomeReference
     * @return static
     */
    public function setOutcomeReference(FHIRReference ...$outcomeReference): self
    {
        if ([] === $outcomeReference) {
            unset($this->outcomeReference);
            return $this;
        }
        $this->outcomeReference = $outcomeReference;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGoal $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGoal
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRGoal)) {
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
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LIFECYCLE_STATUS === $cen) {
                $type->setLifecycleStatus(FHIRGoalLifecycleStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACHIEVEMENT_STATUS === $cen) {
                $type->setAchievementStatus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->addCategory(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIORITY === $cen) {
                $type->setPriority(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_START_DATE === $cen) {
                $type->setStartDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_START_CODEABLE_CONCEPT === $cen) {
                $type->setStartCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET === $cen) {
                $type->addTarget(FHIRGoalTarget::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS_DATE === $cen) {
                $type->setStatusDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS_REASON === $cen) {
                $type->setStatusReason(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXPRESSED_BY === $cen) {
                $type->setExpressedBy(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADDRESSES === $cen) {
                $type->addAddresses(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OUTCOME_CODE === $cen) {
                $type->addOutcomeCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OUTCOME_REFERENCE === $cen) {
                $type->addOutcomeReference(FHIRReference::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_LIFECYCLE_STATUS])) {
            if (isset($type->lifecycleStatus)) {
                $type->lifecycleStatus->setValue((string)$attributes[self::FIELD_LIFECYCLE_STATUS]);
            } else {
                $type->setLifecycleStatus((string)$attributes[self::FIELD_LIFECYCLE_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LIFECYCLE_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_START_DATE])) {
            if (isset($type->startDate)) {
                $type->startDate->setValue((string)$attributes[self::FIELD_START_DATE]);
            } else {
                $type->setStartDate((string)$attributes[self::FIELD_START_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_START_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS_DATE])) {
            if (isset($type->statusDate)) {
                $type->statusDate->setValue((string)$attributes[self::FIELD_STATUS_DATE]);
            } else {
                $type->setStatusDate((string)$attributes[self::FIELD_STATUS_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS_REASON])) {
            if (isset($type->statusReason)) {
                $type->statusReason->setValue((string)$attributes[self::FIELD_STATUS_REASON]);
            } else {
                $type->setStatusReason((string)$attributes[self::FIELD_STATUS_REASON]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS_REASON, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('Goal', $this->_getSourceXMLNS());
        }
        if (isset($this->lifecycleStatus) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LIFECYCLE_STATUS]) {
            $xw->writeAttribute(self::FIELD_LIFECYCLE_STATUS, $this->lifecycleStatus->_getValueAsString());
        }
        if (isset($this->startDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_START_DATE]) {
            $xw->writeAttribute(self::FIELD_START_DATE, $this->startDate->_getValueAsString());
        }
        if (isset($this->statusDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS_DATE]) {
            $xw->writeAttribute(self::FIELD_STATUS_DATE, $this->statusDate->_getValueAsString());
        }
        if (isset($this->statusReason) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS_REASON]) {
            $xw->writeAttribute(self::FIELD_STATUS_REASON, $this->statusReason->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->lifecycleStatus)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LIFECYCLE_STATUS]
                || $this->lifecycleStatus->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LIFECYCLE_STATUS);
            $this->lifecycleStatus->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LIFECYCLE_STATUS]);
            $xw->endElement();
        }
        if (isset($this->achievementStatus)) {
            $xw->startElement(self::FIELD_ACHIEVEMENT_STATUS);
            $this->achievementStatus->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->category)) {
            foreach ($this->category as $v) {
                $xw->startElement(self::FIELD_CATEGORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->priority)) {
            $xw->startElement(self::FIELD_PRIORITY);
            $this->priority->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->description)) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subject)) {
            $xw->startElement(self::FIELD_SUBJECT);
            $this->subject->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->startDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_START_DATE]
                || $this->startDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_START_DATE);
            $this->startDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_START_DATE]);
            $xw->endElement();
        }
        if (isset($this->startCodeableConcept)) {
            $xw->startElement(self::FIELD_START_CODEABLE_CONCEPT);
            $this->startCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->target)) {
            foreach ($this->target as $v) {
                $xw->startElement(self::FIELD_TARGET);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->statusDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS_DATE]
                || $this->statusDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS_DATE);
            $this->statusDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS_DATE]);
            $xw->endElement();
        }
        if (isset($this->statusReason)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS_REASON]
                || $this->statusReason->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS_REASON);
            $this->statusReason->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS_REASON]);
            $xw->endElement();
        }
        if (isset($this->expressedBy)) {
            $xw->startElement(self::FIELD_EXPRESSED_BY);
            $this->expressedBy->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->addresses)) {
            foreach ($this->addresses as $v) {
                $xw->startElement(self::FIELD_ADDRESSES);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->note)) {
            foreach ($this->note as $v) {
                $xw->startElement(self::FIELD_NOTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->outcomeCode)) {
            foreach ($this->outcomeCode as $v) {
                $xw->startElement(self::FIELD_OUTCOME_CODE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->outcomeReference)) {
            foreach ($this->outcomeReference as $v) {
                $xw->startElement(self::FIELD_OUTCOME_REFERENCE);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGoal $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRGoal
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
        } else if (!($type instanceof FHIRGoal)) {
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
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_object($decoded->identifier)) {
                $vals = [$decoded->identifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER, true);
            } else {
                $vals = $decoded->identifier;
            }
            foreach($vals as $v) {
                $type->addIdentifier(FHIRIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->lifecycleStatus)
            || isset($decoded->_lifecycleStatus)
            || property_exists($decoded, self::FIELD_LIFECYCLE_STATUS)
            || property_exists($decoded, self::FIELD_LIFECYCLE_STATUS_EXT)) {
            $v = $decoded->_lifecycleStatus ?? new \stdClass();
            $v->value = $decoded->lifecycleStatus ?? null;
            $type->setLifecycleStatus(FHIRGoalLifecycleStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->achievementStatus) || property_exists($decoded, self::FIELD_ACHIEVEMENT_STATUS)) {
            if (is_array($decoded->achievementStatus)) {
                $type->setAchievementStatus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->achievementStatus), $config));
            } else {
                $type->setAchievementStatus(FHIRCodeableConcept::jsonUnserialize($decoded->achievementStatus, $config));
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
                $type->addCategory(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->priority) || property_exists($decoded, self::FIELD_PRIORITY)) {
            if (is_array($decoded->priority)) {
                $type->setPriority(FHIRCodeableConcept::jsonUnserialize(reset($decoded->priority), $config));
            } else {
                $type->setPriority(FHIRCodeableConcept::jsonUnserialize($decoded->priority, $config));
            }
        }
        if (isset($decoded->description) || property_exists($decoded, self::FIELD_DESCRIPTION)) {
            if (is_array($decoded->description)) {
                $type->setDescription(FHIRCodeableConcept::jsonUnserialize(reset($decoded->description), $config));
            } else {
                $type->setDescription(FHIRCodeableConcept::jsonUnserialize($decoded->description, $config));
            }
        }
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_array($decoded->subject)) {
                $type->setSubject(FHIRReference::jsonUnserialize(reset($decoded->subject), $config));
            } else {
                $type->setSubject(FHIRReference::jsonUnserialize($decoded->subject, $config));
            }
        }
        if (isset($decoded->startDate)
            || isset($decoded->_startDate)
            || property_exists($decoded, self::FIELD_START_DATE)
            || property_exists($decoded, self::FIELD_START_DATE_EXT)) {
            $v = $decoded->_startDate ?? new \stdClass();
            $v->value = $decoded->startDate ?? null;
            $type->setStartDate(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->startCodeableConcept) || property_exists($decoded, self::FIELD_START_CODEABLE_CONCEPT)) {
            if (is_array($decoded->startCodeableConcept)) {
                $type->setStartCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->startCodeableConcept), $config));
            } else {
                $type->setStartCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->startCodeableConcept, $config));
            }
        }
        if (isset($decoded->target) || property_exists($decoded, self::FIELD_TARGET)) {
            if (is_object($decoded->target)) {
                $vals = [$decoded->target];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TARGET, true);
            } else {
                $vals = $decoded->target;
            }
            foreach($vals as $v) {
                $type->addTarget(FHIRGoalTarget::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->statusDate)
            || isset($decoded->_statusDate)
            || property_exists($decoded, self::FIELD_STATUS_DATE)
            || property_exists($decoded, self::FIELD_STATUS_DATE_EXT)) {
            $v = $decoded->_statusDate ?? new \stdClass();
            $v->value = $decoded->statusDate ?? null;
            $type->setStatusDate(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->statusReason)
            || isset($decoded->_statusReason)
            || property_exists($decoded, self::FIELD_STATUS_REASON)
            || property_exists($decoded, self::FIELD_STATUS_REASON_EXT)) {
            $v = $decoded->_statusReason ?? new \stdClass();
            $v->value = $decoded->statusReason ?? null;
            $type->setStatusReason(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->expressedBy) || property_exists($decoded, self::FIELD_EXPRESSED_BY)) {
            if (is_array($decoded->expressedBy)) {
                $type->setExpressedBy(FHIRReference::jsonUnserialize(reset($decoded->expressedBy), $config));
            } else {
                $type->setExpressedBy(FHIRReference::jsonUnserialize($decoded->expressedBy, $config));
            }
        }
        if (isset($decoded->addresses) || property_exists($decoded, self::FIELD_ADDRESSES)) {
            if (is_object($decoded->addresses)) {
                $vals = [$decoded->addresses];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADDRESSES, true);
            } else {
                $vals = $decoded->addresses;
            }
            foreach($vals as $v) {
                $type->addAddresses(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->note) || property_exists($decoded, self::FIELD_NOTE)) {
            if (is_object($decoded->note)) {
                $vals = [$decoded->note];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NOTE, true);
            } else {
                $vals = $decoded->note;
            }
            foreach($vals as $v) {
                $type->addNote(FHIRAnnotation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->outcomeCode) || property_exists($decoded, self::FIELD_OUTCOME_CODE)) {
            if (is_object($decoded->outcomeCode)) {
                $vals = [$decoded->outcomeCode];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_OUTCOME_CODE, true);
            } else {
                $vals = $decoded->outcomeCode;
            }
            foreach($vals as $v) {
                $type->addOutcomeCode(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->outcomeReference) || property_exists($decoded, self::FIELD_OUTCOME_REFERENCE)) {
            if (is_object($decoded->outcomeReference)) {
                $vals = [$decoded->outcomeReference];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_OUTCOME_REFERENCE, true);
            } else {
                $vals = $decoded->outcomeReference;
            }
            foreach($vals as $v) {
                $type->addOutcomeReference(FHIRReference::jsonUnserialize($v, $config));
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
        if (isset($this->identifier) && [] !== $this->identifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER) && 1 === count($this->identifier)) {
                $out->identifier = $this->identifier[0];
            } else {
                $out->identifier = $this->identifier;
            }
        }
        if (isset($this->lifecycleStatus)) {
            if (null !== ($val = $this->lifecycleStatus->getValue())) {
                $out->lifecycleStatus = $val;
            }
            if ($this->lifecycleStatus->_nonValueFieldDefined()) {
                $ext = $this->lifecycleStatus->jsonSerialize();
                unset($ext->value);
                $out->_lifecycleStatus = $ext;
            }
        }
        if (isset($this->achievementStatus)) {
            $out->achievementStatus = $this->achievementStatus;
        }
        if (isset($this->category) && [] !== $this->category) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CATEGORY) && 1 === count($this->category)) {
                $out->category = $this->category[0];
            } else {
                $out->category = $this->category;
            }
        }
        if (isset($this->priority)) {
            $out->priority = $this->priority;
        }
        if (isset($this->description)) {
            $out->description = $this->description;
        }
        if (isset($this->subject)) {
            $out->subject = $this->subject;
        }
        if (isset($this->startDate)) {
            if (null !== ($val = $this->startDate->getValue())) {
                $out->startDate = $val;
            }
            if ($this->startDate->_nonValueFieldDefined()) {
                $ext = $this->startDate->jsonSerialize();
                unset($ext->value);
                $out->_startDate = $ext;
            }
        }
        if (isset($this->startCodeableConcept)) {
            $out->startCodeableConcept = $this->startCodeableConcept;
        }
        if (isset($this->target) && [] !== $this->target) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TARGET) && 1 === count($this->target)) {
                $out->target = $this->target[0];
            } else {
                $out->target = $this->target;
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
        if (isset($this->statusReason)) {
            if (null !== ($val = $this->statusReason->getValue())) {
                $out->statusReason = $val;
            }
            if ($this->statusReason->_nonValueFieldDefined()) {
                $ext = $this->statusReason->jsonSerialize();
                unset($ext->value);
                $out->_statusReason = $ext;
            }
        }
        if (isset($this->expressedBy)) {
            $out->expressedBy = $this->expressedBy;
        }
        if (isset($this->addresses) && [] !== $this->addresses) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADDRESSES) && 1 === count($this->addresses)) {
                $out->addresses = $this->addresses[0];
            } else {
                $out->addresses = $this->addresses;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        if (isset($this->outcomeCode) && [] !== $this->outcomeCode) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_OUTCOME_CODE) && 1 === count($this->outcomeCode)) {
                $out->outcomeCode = $this->outcomeCode[0];
            } else {
                $out->outcomeCode = $this->outcomeCode;
            }
        }
        if (isset($this->outcomeReference) && [] !== $this->outcomeReference) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_OUTCOME_REFERENCE) && 1 === count($this->outcomeReference)) {
                $out->outcomeReference = $this->outcomeReference[0];
            } else {
                $out->outcomeReference = $this->outcomeReference;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
