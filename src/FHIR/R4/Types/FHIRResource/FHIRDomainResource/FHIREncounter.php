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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREncounterStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterClassHistory;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterDiagnosis;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterHospitalization;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterLocation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterParticipant;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterStatusHistory;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREncounterStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * An interaction between a patient and healthcare provider(s) for the purpose of
 * providing healthcare service(s) or assessing the health status of a patient.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIREncounter extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_ENCOUNTER;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_STATUS_HISTORY = 'statusHistory';
    public const FIELD_CLASS = 'class';
    public const FIELD_CLASS_HISTORY = 'classHistory';
    public const FIELD_TYPE = 'type';
    public const FIELD_SERVICE_TYPE = 'serviceType';
    public const FIELD_PRIORITY = 'priority';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_EPISODE_OF_CARE = 'episodeOfCare';
    public const FIELD_BASED_ON = 'basedOn';
    public const FIELD_PARTICIPANT = 'participant';
    public const FIELD_APPOINTMENT = 'appointment';
    public const FIELD_PERIOD = 'period';
    public const FIELD_LENGTH = 'length';
    public const FIELD_REASON_CODE = 'reasonCode';
    public const FIELD_REASON_REFERENCE = 'reasonReference';
    public const FIELD_DIAGNOSIS = 'diagnosis';
    public const FIELD_ACCOUNT = 'account';
    public const FIELD_HOSPITALIZATION = 'hospitalization';
    public const FIELD_LOCATION = 'location';
    public const FIELD_SERVICE_PROVIDER = 'serviceProvider';
    public const FIELD_PART_OF = 'partOf';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_CLASS => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier(s) by which this encounter is known.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * Current state of the encounter.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * planned | arrived | triaged | in-progress | onleave | finished | cancelled +.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREncounterStatus
     */
    #[FHIREncounterStatus]
    protected FHIREncounterStatus $status;
    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The status history permits the encounter resource to contain the status history
     * without needing to read through the historical versions of the resource, or even
     * have the server store them.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterStatusHistory>
     */
    #[FHIREncounterStatusHistory]
    protected array $statusHistory;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Concepts representing classification of patient encounter such as ambulatory
     * (outpatient), inpatient, emergency, home health or others due to local
     * variations.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    #[FHIRCoding]
    protected FHIRCoding $class;
    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The class history permits the tracking of the encounters transitions without
     * needing to go through the resource history. This would be used for a case where
     * an admission starts of as an emergency encounter, then transitions into an
     * inpatient scenario. Doing this and not restarting a new encounter ensures that
     * any lab/diagnostic results can more easily follow the patient and not require
     * re-processing and not get lost or cancelled during a kind of discharge from
     * emergency to inpatient.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterClassHistory>
     */
    #[FHIREncounterClassHistory]
    protected array $classHistory;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specific type of encounter (e.g. e-mail consultation, surgical day-care, skilled
     * nursing, rehabilitation).
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
     * Broad categorization of the service that is to be provided (e.g. cardiology).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $serviceType;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the urgency of the encounter.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $priority;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient or group present at the encounter.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $subject;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where a specific encounter should be classified as a part of a specific
     * episode(s) of care this field should be used. This association can facilitate
     * grouping of related encounters together for a specific purpose, such as
     * government reporting, issue tracking, association via a common problem. The
     * association is recorded on the encounter as these are typically created after
     * the episode of care and grouped on entry rather than editing the episode of care
     * to append another encounter to it (the episode of care could span years).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $episodeOfCare;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request this encounter satisfies (e.g. incoming referral or procedure
     * request).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $basedOn;
    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The list of people responsible for providing the service.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterParticipant>
     */
    #[FHIREncounterParticipant]
    protected array $participant;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The appointment that scheduled this encounter.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $appointment;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The start and end time of the encounter.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $period;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Quantity of time the encounter lasted. This excludes the time during leaves of
     * absence.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $length;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason the encounter takes place, expressed as a code. For admissions, this can
     * be used for a coded admission diagnosis.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $reasonCode;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason the encounter takes place, expressed as a code. For admissions, this can
     * be used for a coded admission diagnosis.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $reasonReference;
    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The list of diagnosis relevant to this encounter.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterDiagnosis>
     */
    #[FHIREncounterDiagnosis]
    protected array $diagnosis;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of accounts that may be used for billing for this Encounter.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $account;
    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * Details about the admission to a healthcare service.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterHospitalization
     */
    #[FHIREncounterHospitalization]
    protected FHIREncounterHospitalization $hospitalization;
    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * List of locations where the patient has been during this encounter.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterLocation>
     */
    #[FHIREncounterLocation]
    protected array $location;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The organization that is primarily responsible for this Encounter's services.
     * This MAY be the same as the organization on the Patient record, however it could
     * be different, such as if the actor performing the services was from an external
     * organization (which may be billed seperately) for an external consultation.
     * Refer to the example bundle showing an abbreviated set of Encounters for a
     * colonoscopy.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $serviceProvider;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Another Encounter of which this encounter is a part of (administratively or in
     * time).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $partOf;

    /* constructor.php:61 */
    /**
     * FHIREncounter Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREncounterStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREncounterStatus $status
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterStatusHistory> $statusHistory
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $class
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterClassHistory> $classHistory
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $serviceType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $priority
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $episodeOfCare
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $basedOn
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterParticipant> $participant
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $appointment
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $length
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $reasonCode
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $reasonReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterDiagnosis> $diagnosis
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $account
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterHospitalization $hospitalization
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterLocation> $location
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $serviceProvider
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $partOf
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
                                null|string|FHIREncounterStatusList|FHIREncounterStatus $status = null,
                                null|iterable $statusHistory = null,
                                null|FHIRCoding $class = null,
                                null|iterable $classHistory = null,
                                null|iterable $type = null,
                                null|FHIRCodeableConcept $serviceType = null,
                                null|FHIRCodeableConcept $priority = null,
                                null|FHIRReference $subject = null,
                                null|iterable $episodeOfCare = null,
                                null|iterable $basedOn = null,
                                null|iterable $participant = null,
                                null|iterable $appointment = null,
                                null|FHIRPeriod $period = null,
                                null|FHIRDuration $length = null,
                                null|iterable $reasonCode = null,
                                null|iterable $reasonReference = null,
                                null|iterable $diagnosis = null,
                                null|iterable $account = null,
                                null|FHIREncounterHospitalization $hospitalization = null,
                                null|iterable $location = null,
                                null|FHIRReference $serviceProvider = null,
                                null|FHIRReference $partOf = null,
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
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $statusHistory) {
            $this->setStatusHistory(...$statusHistory);
        }
        if (null !== $class) {
            $this->setClass($class);
        }
        if (null !== $classHistory) {
            $this->setClassHistory(...$classHistory);
        }
        if (null !== $type) {
            $this->setType(...$type);
        }
        if (null !== $serviceType) {
            $this->setServiceType($serviceType);
        }
        if (null !== $priority) {
            $this->setPriority($priority);
        }
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $episodeOfCare) {
            $this->setEpisodeOfCare(...$episodeOfCare);
        }
        if (null !== $basedOn) {
            $this->setBasedOn(...$basedOn);
        }
        if (null !== $participant) {
            $this->setParticipant(...$participant);
        }
        if (null !== $appointment) {
            $this->setAppointment(...$appointment);
        }
        if (null !== $period) {
            $this->setPeriod($period);
        }
        if (null !== $length) {
            $this->setLength($length);
        }
        if (null !== $reasonCode) {
            $this->setReasonCode(...$reasonCode);
        }
        if (null !== $reasonReference) {
            $this->setReasonReference(...$reasonReference);
        }
        if (null !== $diagnosis) {
            $this->setDiagnosis(...$diagnosis);
        }
        if (null !== $account) {
            $this->setAccount(...$account);
        }
        if (null !== $hospitalization) {
            $this->setHospitalization($hospitalization);
        }
        if (null !== $location) {
            $this->setLocation(...$location);
        }
        if (null !== $serviceProvider) {
            $this->setServiceProvider($serviceProvider);
        }
        if (null !== $partOf) {
            $this->setPartOf($partOf);
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
     * Identifier(s) by which this encounter is known.
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
     * Identifier(s) by which this encounter is known.
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
     * Identifier(s) by which this encounter is known.
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
     * Current state of the encounter.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * planned | arrived | triaged | in-progress | onleave | finished | cancelled +.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREncounterStatus
     */
    public function getStatus(): null|FHIREncounterStatus
    {
        return $this->status ?? null;
    }

    /**
     * Current state of the encounter.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * planned | arrived | triaged | in-progress | onleave | finished | cancelled +.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREncounterStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREncounterStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIREncounterStatusList|FHIREncounterStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIREncounterStatus)) {
            $status = new FHIREncounterStatus(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The status history permits the encounter resource to contain the status history
     * without needing to read through the historical versions of the resource, or even
     * have the server store them.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterStatusHistory>
     */
    public function getStatusHistory(): array
    {
        return $this->statusHistory ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterStatusHistory>
     */
    public function getStatusHistoryIterator(): iterable
    {
        if (!isset($this->statusHistory)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->statusHistory);
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The status history permits the encounter resource to contain the status history
     * without needing to read through the historical versions of the resource, or even
     * have the server store them.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterStatusHistory $statusHistory
     * @return static
     */
    public function addStatusHistory(FHIREncounterStatusHistory $statusHistory): self
    {
        if (!isset($this->statusHistory)) {
            $this->statusHistory = [];
        }
        $this->statusHistory[] = $statusHistory;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The status history permits the encounter resource to contain the status history
     * without needing to read through the historical versions of the resource, or even
     * have the server store them.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterStatusHistory ...$statusHistory
     * @return static
     */
    public function setStatusHistory(FHIREncounterStatusHistory ...$statusHistory): self
    {
        if ([] === $statusHistory) {
            unset($this->statusHistory);
            return $this;
        }
        $this->statusHistory = $statusHistory;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Concepts representing classification of patient encounter such as ambulatory
     * (outpatient), inpatient, emergency, home health or others due to local
     * variations.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    public function getClass(): null|FHIRCoding
    {
        return $this->class ?? null;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Concepts representing classification of patient encounter such as ambulatory
     * (outpatient), inpatient, emergency, home health or others due to local
     * variations.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $class
     * @return static
     */
    public function setClass(null|FHIRCoding $class): self
    {
        if (null === $class) {
            unset($this->class);
            return $this;
        }
        $this->class = $class;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The class history permits the tracking of the encounters transitions without
     * needing to go through the resource history. This would be used for a case where
     * an admission starts of as an emergency encounter, then transitions into an
     * inpatient scenario. Doing this and not restarting a new encounter ensures that
     * any lab/diagnostic results can more easily follow the patient and not require
     * re-processing and not get lost or cancelled during a kind of discharge from
     * emergency to inpatient.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterClassHistory>
     */
    public function getClassHistory(): array
    {
        return $this->classHistory ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterClassHistory>
     */
    public function getClassHistoryIterator(): iterable
    {
        if (!isset($this->classHistory)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->classHistory);
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The class history permits the tracking of the encounters transitions without
     * needing to go through the resource history. This would be used for a case where
     * an admission starts of as an emergency encounter, then transitions into an
     * inpatient scenario. Doing this and not restarting a new encounter ensures that
     * any lab/diagnostic results can more easily follow the patient and not require
     * re-processing and not get lost or cancelled during a kind of discharge from
     * emergency to inpatient.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterClassHistory $classHistory
     * @return static
     */
    public function addClassHistory(FHIREncounterClassHistory $classHistory): self
    {
        if (!isset($this->classHistory)) {
            $this->classHistory = [];
        }
        $this->classHistory[] = $classHistory;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The class history permits the tracking of the encounters transitions without
     * needing to go through the resource history. This would be used for a case where
     * an admission starts of as an emergency encounter, then transitions into an
     * inpatient scenario. Doing this and not restarting a new encounter ensures that
     * any lab/diagnostic results can more easily follow the patient and not require
     * re-processing and not get lost or cancelled during a kind of discharge from
     * emergency to inpatient.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterClassHistory ...$classHistory
     * @return static
     */
    public function setClassHistory(FHIREncounterClassHistory ...$classHistory): self
    {
        if ([] === $classHistory) {
            unset($this->classHistory);
            return $this;
        }
        $this->classHistory = $classHistory;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specific type of encounter (e.g. e-mail consultation, surgical day-care, skilled
     * nursing, rehabilitation).
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
     * Specific type of encounter (e.g. e-mail consultation, surgical day-care, skilled
     * nursing, rehabilitation).
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
     * Specific type of encounter (e.g. e-mail consultation, surgical day-care, skilled
     * nursing, rehabilitation).
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
     * Broad categorization of the service that is to be provided (e.g. cardiology).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getServiceType(): null|FHIRCodeableConcept
    {
        return $this->serviceType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Broad categorization of the service that is to be provided (e.g. cardiology).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $serviceType
     * @return static
     */
    public function setServiceType(null|FHIRCodeableConcept $serviceType): self
    {
        if (null === $serviceType) {
            unset($this->serviceType);
            return $this;
        }
        $this->serviceType = $serviceType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the urgency of the encounter.
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
     * Indicates the urgency of the encounter.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient or group present at the encounter.
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
     * The patient or group present at the encounter.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where a specific encounter should be classified as a part of a specific
     * episode(s) of care this field should be used. This association can facilitate
     * grouping of related encounters together for a specific purpose, such as
     * government reporting, issue tracking, association via a common problem. The
     * association is recorded on the encounter as these are typically created after
     * the episode of care and grouped on entry rather than editing the episode of care
     * to append another encounter to it (the episode of care could span years).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEpisodeOfCare(): array
    {
        return $this->episodeOfCare ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEpisodeOfCareIterator(): iterable
    {
        if (!isset($this->episodeOfCare)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->episodeOfCare);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where a specific encounter should be classified as a part of a specific
     * episode(s) of care this field should be used. This association can facilitate
     * grouping of related encounters together for a specific purpose, such as
     * government reporting, issue tracking, association via a common problem. The
     * association is recorded on the encounter as these are typically created after
     * the episode of care and grouped on entry rather than editing the episode of care
     * to append another encounter to it (the episode of care could span years).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $episodeOfCare
     * @return static
     */
    public function addEpisodeOfCare(FHIRReference $episodeOfCare): self
    {
        if (!isset($this->episodeOfCare)) {
            $this->episodeOfCare = [];
        }
        $this->episodeOfCare[] = $episodeOfCare;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where a specific encounter should be classified as a part of a specific
     * episode(s) of care this field should be used. This association can facilitate
     * grouping of related encounters together for a specific purpose, such as
     * government reporting, issue tracking, association via a common problem. The
     * association is recorded on the encounter as these are typically created after
     * the episode of care and grouped on entry rather than editing the episode of care
     * to append another encounter to it (the episode of care could span years).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$episodeOfCare
     * @return static
     */
    public function setEpisodeOfCare(FHIRReference ...$episodeOfCare): self
    {
        if ([] === $episodeOfCare) {
            unset($this->episodeOfCare);
            return $this;
        }
        $this->episodeOfCare = $episodeOfCare;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request this encounter satisfies (e.g. incoming referral or procedure
     * request).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getBasedOn(): array
    {
        return $this->basedOn ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getBasedOnIterator(): iterable
    {
        if (!isset($this->basedOn)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->basedOn);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request this encounter satisfies (e.g. incoming referral or procedure
     * request).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $basedOn
     * @return static
     */
    public function addBasedOn(FHIRReference $basedOn): self
    {
        if (!isset($this->basedOn)) {
            $this->basedOn = [];
        }
        $this->basedOn[] = $basedOn;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request this encounter satisfies (e.g. incoming referral or procedure
     * request).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$basedOn
     * @return static
     */
    public function setBasedOn(FHIRReference ...$basedOn): self
    {
        if ([] === $basedOn) {
            unset($this->basedOn);
            return $this;
        }
        $this->basedOn = $basedOn;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The list of people responsible for providing the service.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterParticipant>
     */
    public function getParticipant(): array
    {
        return $this->participant ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterParticipant>
     */
    public function getParticipantIterator(): iterable
    {
        if (!isset($this->participant)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->participant);
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The list of people responsible for providing the service.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterParticipant $participant
     * @return static
     */
    public function addParticipant(FHIREncounterParticipant $participant): self
    {
        if (!isset($this->participant)) {
            $this->participant = [];
        }
        $this->participant[] = $participant;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The list of people responsible for providing the service.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterParticipant ...$participant
     * @return static
     */
    public function setParticipant(FHIREncounterParticipant ...$participant): self
    {
        if ([] === $participant) {
            unset($this->participant);
            return $this;
        }
        $this->participant = $participant;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The appointment that scheduled this encounter.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAppointment(): array
    {
        return $this->appointment ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAppointmentIterator(): iterable
    {
        if (!isset($this->appointment)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->appointment);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The appointment that scheduled this encounter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $appointment
     * @return static
     */
    public function addAppointment(FHIRReference $appointment): self
    {
        if (!isset($this->appointment)) {
            $this->appointment = [];
        }
        $this->appointment[] = $appointment;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The appointment that scheduled this encounter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$appointment
     * @return static
     */
    public function setAppointment(FHIRReference ...$appointment): self
    {
        if ([] === $appointment) {
            unset($this->appointment);
            return $this;
        }
        $this->appointment = $appointment;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The start and end time of the encounter.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getPeriod(): null|FHIRPeriod
    {
        return $this->period ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The start and end time of the encounter.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @return static
     */
    public function setPeriod(null|FHIRPeriod $period): self
    {
        if (null === $period) {
            unset($this->period);
            return $this;
        }
        $this->period = $period;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Quantity of time the encounter lasted. This excludes the time during leaves of
     * absence.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getLength(): null|FHIRDuration
    {
        return $this->length ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Quantity of time the encounter lasted. This excludes the time during leaves of
     * absence.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $length
     * @return static
     */
    public function setLength(null|FHIRDuration $length): self
    {
        if (null === $length) {
            unset($this->length);
            return $this;
        }
        $this->length = $length;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason the encounter takes place, expressed as a code. For admissions, this can
     * be used for a coded admission diagnosis.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getReasonCode(): array
    {
        return $this->reasonCode ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getReasonCodeIterator(): iterable
    {
        if (!isset($this->reasonCode)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->reasonCode);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason the encounter takes place, expressed as a code. For admissions, this can
     * be used for a coded admission diagnosis.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return static
     */
    public function addReasonCode(FHIRCodeableConcept $reasonCode): self
    {
        if (!isset($this->reasonCode)) {
            $this->reasonCode = [];
        }
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason the encounter takes place, expressed as a code. For admissions, this can
     * be used for a coded admission diagnosis.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$reasonCode
     * @return static
     */
    public function setReasonCode(FHIRCodeableConcept ...$reasonCode): self
    {
        if ([] === $reasonCode) {
            unset($this->reasonCode);
            return $this;
        }
        $this->reasonCode = $reasonCode;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason the encounter takes place, expressed as a code. For admissions, this can
     * be used for a coded admission diagnosis.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getReasonReference(): array
    {
        return $this->reasonReference ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getReasonReferenceIterator(): iterable
    {
        if (!isset($this->reasonReference)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->reasonReference);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason the encounter takes place, expressed as a code. For admissions, this can
     * be used for a coded admission diagnosis.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $reasonReference
     * @return static
     */
    public function addReasonReference(FHIRReference $reasonReference): self
    {
        if (!isset($this->reasonReference)) {
            $this->reasonReference = [];
        }
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason the encounter takes place, expressed as a code. For admissions, this can
     * be used for a coded admission diagnosis.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$reasonReference
     * @return static
     */
    public function setReasonReference(FHIRReference ...$reasonReference): self
    {
        if ([] === $reasonReference) {
            unset($this->reasonReference);
            return $this;
        }
        $this->reasonReference = $reasonReference;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The list of diagnosis relevant to this encounter.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterDiagnosis>
     */
    public function getDiagnosis(): array
    {
        return $this->diagnosis ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterDiagnosis>
     */
    public function getDiagnosisIterator(): iterable
    {
        if (!isset($this->diagnosis)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->diagnosis);
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The list of diagnosis relevant to this encounter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterDiagnosis $diagnosis
     * @return static
     */
    public function addDiagnosis(FHIREncounterDiagnosis $diagnosis): self
    {
        if (!isset($this->diagnosis)) {
            $this->diagnosis = [];
        }
        $this->diagnosis[] = $diagnosis;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * The list of diagnosis relevant to this encounter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterDiagnosis ...$diagnosis
     * @return static
     */
    public function setDiagnosis(FHIREncounterDiagnosis ...$diagnosis): self
    {
        if ([] === $diagnosis) {
            unset($this->diagnosis);
            return $this;
        }
        $this->diagnosis = $diagnosis;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of accounts that may be used for billing for this Encounter.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAccount(): array
    {
        return $this->account ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAccountIterator(): iterable
    {
        if (!isset($this->account)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->account);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of accounts that may be used for billing for this Encounter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $account
     * @return static
     */
    public function addAccount(FHIRReference $account): self
    {
        if (!isset($this->account)) {
            $this->account = [];
        }
        $this->account[] = $account;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of accounts that may be used for billing for this Encounter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$account
     * @return static
     */
    public function setAccount(FHIRReference ...$account): self
    {
        if ([] === $account) {
            unset($this->account);
            return $this;
        }
        $this->account = $account;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * Details about the admission to a healthcare service.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterHospitalization
     */
    public function getHospitalization(): null|FHIREncounterHospitalization
    {
        return $this->hospitalization ?? null;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * Details about the admission to a healthcare service.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterHospitalization $hospitalization
     * @return static
     */
    public function setHospitalization(null|FHIREncounterHospitalization $hospitalization): self
    {
        if (null === $hospitalization) {
            unset($this->hospitalization);
            return $this;
        }
        $this->hospitalization = $hospitalization;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * List of locations where the patient has been during this encounter.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterLocation>
     */
    public function getLocation(): array
    {
        return $this->location ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterLocation>
     */
    public function getLocationIterator(): iterable
    {
        if (!isset($this->location)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->location);
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * List of locations where the patient has been during this encounter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterLocation $location
     * @return static
     */
    public function addLocation(FHIREncounterLocation $location): self
    {
        if (!isset($this->location)) {
            $this->location = [];
        }
        $this->location[] = $location;
        return $this;
    }

    /**
     * An interaction between a patient and healthcare provider(s) for the purpose of
     * providing healthcare service(s) or assessing the health status of a patient.
     *
     * List of locations where the patient has been during this encounter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREncounter\FHIREncounterLocation ...$location
     * @return static
     */
    public function setLocation(FHIREncounterLocation ...$location): self
    {
        if ([] === $location) {
            unset($this->location);
            return $this;
        }
        $this->location = $location;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The organization that is primarily responsible for this Encounter's services.
     * This MAY be the same as the organization on the Patient record, however it could
     * be different, such as if the actor performing the services was from an external
     * organization (which may be billed seperately) for an external consultation.
     * Refer to the example bundle showing an abbreviated set of Encounters for a
     * colonoscopy.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getServiceProvider(): null|FHIRReference
    {
        return $this->serviceProvider ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The organization that is primarily responsible for this Encounter's services.
     * This MAY be the same as the organization on the Patient record, however it could
     * be different, such as if the actor performing the services was from an external
     * organization (which may be billed seperately) for an external consultation.
     * Refer to the example bundle showing an abbreviated set of Encounters for a
     * colonoscopy.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $serviceProvider
     * @return static
     */
    public function setServiceProvider(null|FHIRReference $serviceProvider): self
    {
        if (null === $serviceProvider) {
            unset($this->serviceProvider);
            return $this;
        }
        $this->serviceProvider = $serviceProvider;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Another Encounter of which this encounter is a part of (administratively or in
     * time).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPartOf(): null|FHIRReference
    {
        return $this->partOf ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Another Encounter of which this encounter is a part of (administratively or in
     * time).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $partOf
     * @return static
     */
    public function setPartOf(null|FHIRReference $partOf): self
    {
        if (null === $partOf) {
            unset($this->partOf);
            return $this;
        }
        $this->partOf = $partOf;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREncounter $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREncounter
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIREncounter)) {
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
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIREncounterStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS_HISTORY === $cen) {
                $type->addStatusHistory(FHIREncounterStatusHistory::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLASS === $cen) {
                $type->setClass(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLASS_HISTORY === $cen) {
                $type->addClassHistory(FHIREncounterClassHistory::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->addType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERVICE_TYPE === $cen) {
                $type->setServiceType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIORITY === $cen) {
                $type->setPriority(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EPISODE_OF_CARE === $cen) {
                $type->addEpisodeOfCare(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BASED_ON === $cen) {
                $type->addBasedOn(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT === $cen) {
                $type->addParticipant(FHIREncounterParticipant::xmlUnserialize($ce, $config));
            } else if (self::FIELD_APPOINTMENT === $cen) {
                $type->addAppointment(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD === $cen) {
                $type->setPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LENGTH === $cen) {
                $type->setLength(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_CODE === $cen) {
                $type->addReasonCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_REFERENCE === $cen) {
                $type->addReasonReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DIAGNOSIS === $cen) {
                $type->addDiagnosis(FHIREncounterDiagnosis::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACCOUNT === $cen) {
                $type->addAccount(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_HOSPITALIZATION === $cen) {
                $type->setHospitalization(FHIREncounterHospitalization::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOCATION === $cen) {
                $type->addLocation(FHIREncounterLocation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERVICE_PROVIDER === $cen) {
                $type->setServiceProvider(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PART_OF === $cen) {
                $type->setPartOf(FHIRReference::xmlUnserialize($ce, $config));
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
            $xw->openRootNode('Encounter', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->statusHistory)) {
            foreach ($this->statusHistory as $v) {
                $xw->startElement(self::FIELD_STATUS_HISTORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->class)) {
            $xw->startElement(self::FIELD_CLASS);
            $this->class->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->classHistory)) {
            foreach ($this->classHistory as $v) {
                $xw->startElement(self::FIELD_CLASS_HISTORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->type)) {
            foreach ($this->type as $v) {
                $xw->startElement(self::FIELD_TYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->serviceType)) {
            $xw->startElement(self::FIELD_SERVICE_TYPE);
            $this->serviceType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->priority)) {
            $xw->startElement(self::FIELD_PRIORITY);
            $this->priority->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subject)) {
            $xw->startElement(self::FIELD_SUBJECT);
            $this->subject->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->episodeOfCare)) {
            foreach ($this->episodeOfCare as $v) {
                $xw->startElement(self::FIELD_EPISODE_OF_CARE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->basedOn)) {
            foreach ($this->basedOn as $v) {
                $xw->startElement(self::FIELD_BASED_ON);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->participant)) {
            foreach ($this->participant as $v) {
                $xw->startElement(self::FIELD_PARTICIPANT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->appointment)) {
            foreach ($this->appointment as $v) {
                $xw->startElement(self::FIELD_APPOINTMENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->period)) {
            $xw->startElement(self::FIELD_PERIOD);
            $this->period->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->length)) {
            $xw->startElement(self::FIELD_LENGTH);
            $this->length->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->reasonCode)) {
            foreach ($this->reasonCode as $v) {
                $xw->startElement(self::FIELD_REASON_CODE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->reasonReference)) {
            foreach ($this->reasonReference as $v) {
                $xw->startElement(self::FIELD_REASON_REFERENCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->diagnosis)) {
            foreach ($this->diagnosis as $v) {
                $xw->startElement(self::FIELD_DIAGNOSIS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->account)) {
            foreach ($this->account as $v) {
                $xw->startElement(self::FIELD_ACCOUNT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->hospitalization)) {
            $xw->startElement(self::FIELD_HOSPITALIZATION);
            $this->hospitalization->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->location)) {
            foreach ($this->location as $v) {
                $xw->startElement(self::FIELD_LOCATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->serviceProvider)) {
            $xw->startElement(self::FIELD_SERVICE_PROVIDER);
            $this->serviceProvider->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->partOf)) {
            $xw->startElement(self::FIELD_PART_OF);
            $this->partOf->xmlSerialize($xw, $config);
            $xw->endElement();
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREncounter $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIREncounter
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
        } else if (!($type instanceof FHIREncounter)) {
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
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIREncounterStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->statusHistory) || property_exists($decoded, self::FIELD_STATUS_HISTORY)) {
            if (is_object($decoded->statusHistory)) {
                $vals = [$decoded->statusHistory];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_STATUS_HISTORY, true);
            } else {
                $vals = $decoded->statusHistory;
            }
            foreach($vals as $v) {
                $type->addStatusHistory(FHIREncounterStatusHistory::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->class) || property_exists($decoded, self::FIELD_CLASS)) {
            if (is_array($decoded->class)) {
                $type->setClass(FHIRCoding::jsonUnserialize(reset($decoded->class), $config));
            } else {
                $type->setClass(FHIRCoding::jsonUnserialize($decoded->class, $config));
            }
        }
        if (isset($decoded->classHistory) || property_exists($decoded, self::FIELD_CLASS_HISTORY)) {
            if (is_object($decoded->classHistory)) {
                $vals = [$decoded->classHistory];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CLASS_HISTORY, true);
            } else {
                $vals = $decoded->classHistory;
            }
            foreach($vals as $v) {
                $type->addClassHistory(FHIREncounterClassHistory::jsonUnserialize($v, $config));
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
        if (isset($decoded->serviceType) || property_exists($decoded, self::FIELD_SERVICE_TYPE)) {
            if (is_array($decoded->serviceType)) {
                $type->setServiceType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->serviceType), $config));
            } else {
                $type->setServiceType(FHIRCodeableConcept::jsonUnserialize($decoded->serviceType, $config));
            }
        }
        if (isset($decoded->priority) || property_exists($decoded, self::FIELD_PRIORITY)) {
            if (is_array($decoded->priority)) {
                $type->setPriority(FHIRCodeableConcept::jsonUnserialize(reset($decoded->priority), $config));
            } else {
                $type->setPriority(FHIRCodeableConcept::jsonUnserialize($decoded->priority, $config));
            }
        }
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_array($decoded->subject)) {
                $type->setSubject(FHIRReference::jsonUnserialize(reset($decoded->subject), $config));
            } else {
                $type->setSubject(FHIRReference::jsonUnserialize($decoded->subject, $config));
            }
        }
        if (isset($decoded->episodeOfCare) || property_exists($decoded, self::FIELD_EPISODE_OF_CARE)) {
            if (is_object($decoded->episodeOfCare)) {
                $vals = [$decoded->episodeOfCare];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_EPISODE_OF_CARE, true);
            } else {
                $vals = $decoded->episodeOfCare;
            }
            foreach($vals as $v) {
                $type->addEpisodeOfCare(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->basedOn) || property_exists($decoded, self::FIELD_BASED_ON)) {
            if (is_object($decoded->basedOn)) {
                $vals = [$decoded->basedOn];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_BASED_ON, true);
            } else {
                $vals = $decoded->basedOn;
            }
            foreach($vals as $v) {
                $type->addBasedOn(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->participant) || property_exists($decoded, self::FIELD_PARTICIPANT)) {
            if (is_object($decoded->participant)) {
                $vals = [$decoded->participant];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PARTICIPANT, true);
            } else {
                $vals = $decoded->participant;
            }
            foreach($vals as $v) {
                $type->addParticipant(FHIREncounterParticipant::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->appointment) || property_exists($decoded, self::FIELD_APPOINTMENT)) {
            if (is_object($decoded->appointment)) {
                $vals = [$decoded->appointment];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_APPOINTMENT, true);
            } else {
                $vals = $decoded->appointment;
            }
            foreach($vals as $v) {
                $type->addAppointment(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->period) || property_exists($decoded, self::FIELD_PERIOD)) {
            if (is_array($decoded->period)) {
                $type->setPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->period), $config));
            } else {
                $type->setPeriod(FHIRPeriod::jsonUnserialize($decoded->period, $config));
            }
        }
        if (isset($decoded->length) || property_exists($decoded, self::FIELD_LENGTH)) {
            if (is_array($decoded->length)) {
                $type->setLength(FHIRDuration::jsonUnserialize(reset($decoded->length), $config));
            } else {
                $type->setLength(FHIRDuration::jsonUnserialize($decoded->length, $config));
            }
        }
        if (isset($decoded->reasonCode) || property_exists($decoded, self::FIELD_REASON_CODE)) {
            if (is_object($decoded->reasonCode)) {
                $vals = [$decoded->reasonCode];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REASON_CODE, true);
            } else {
                $vals = $decoded->reasonCode;
            }
            foreach($vals as $v) {
                $type->addReasonCode(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->reasonReference) || property_exists($decoded, self::FIELD_REASON_REFERENCE)) {
            if (is_object($decoded->reasonReference)) {
                $vals = [$decoded->reasonReference];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REASON_REFERENCE, true);
            } else {
                $vals = $decoded->reasonReference;
            }
            foreach($vals as $v) {
                $type->addReasonReference(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->diagnosis) || property_exists($decoded, self::FIELD_DIAGNOSIS)) {
            if (is_object($decoded->diagnosis)) {
                $vals = [$decoded->diagnosis];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DIAGNOSIS, true);
            } else {
                $vals = $decoded->diagnosis;
            }
            foreach($vals as $v) {
                $type->addDiagnosis(FHIREncounterDiagnosis::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->account) || property_exists($decoded, self::FIELD_ACCOUNT)) {
            if (is_object($decoded->account)) {
                $vals = [$decoded->account];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ACCOUNT, true);
            } else {
                $vals = $decoded->account;
            }
            foreach($vals as $v) {
                $type->addAccount(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->hospitalization) || property_exists($decoded, self::FIELD_HOSPITALIZATION)) {
            if (is_array($decoded->hospitalization)) {
                $type->setHospitalization(FHIREncounterHospitalization::jsonUnserialize(reset($decoded->hospitalization), $config));
            } else {
                $type->setHospitalization(FHIREncounterHospitalization::jsonUnserialize($decoded->hospitalization, $config));
            }
        }
        if (isset($decoded->location) || property_exists($decoded, self::FIELD_LOCATION)) {
            if (is_object($decoded->location)) {
                $vals = [$decoded->location];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_LOCATION, true);
            } else {
                $vals = $decoded->location;
            }
            foreach($vals as $v) {
                $type->addLocation(FHIREncounterLocation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->serviceProvider) || property_exists($decoded, self::FIELD_SERVICE_PROVIDER)) {
            if (is_array($decoded->serviceProvider)) {
                $type->setServiceProvider(FHIRReference::jsonUnserialize(reset($decoded->serviceProvider), $config));
            } else {
                $type->setServiceProvider(FHIRReference::jsonUnserialize($decoded->serviceProvider, $config));
            }
        }
        if (isset($decoded->partOf) || property_exists($decoded, self::FIELD_PART_OF)) {
            if (is_array($decoded->partOf)) {
                $type->setPartOf(FHIRReference::jsonUnserialize(reset($decoded->partOf), $config));
            } else {
                $type->setPartOf(FHIRReference::jsonUnserialize($decoded->partOf, $config));
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
        if (isset($this->statusHistory) && [] !== $this->statusHistory) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_STATUS_HISTORY) && 1 === count($this->statusHistory)) {
                $out->statusHistory = $this->statusHistory[0];
            } else {
                $out->statusHistory = $this->statusHistory;
            }
        }
        if (isset($this->class)) {
            $out->class = $this->class;
        }
        if (isset($this->classHistory) && [] !== $this->classHistory) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CLASS_HISTORY) && 1 === count($this->classHistory)) {
                $out->classHistory = $this->classHistory[0];
            } else {
                $out->classHistory = $this->classHistory;
            }
        }
        if (isset($this->type) && [] !== $this->type) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TYPE) && 1 === count($this->type)) {
                $out->type = $this->type[0];
            } else {
                $out->type = $this->type;
            }
        }
        if (isset($this->serviceType)) {
            $out->serviceType = $this->serviceType;
        }
        if (isset($this->priority)) {
            $out->priority = $this->priority;
        }
        if (isset($this->subject)) {
            $out->subject = $this->subject;
        }
        if (isset($this->episodeOfCare) && [] !== $this->episodeOfCare) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_EPISODE_OF_CARE) && 1 === count($this->episodeOfCare)) {
                $out->episodeOfCare = $this->episodeOfCare[0];
            } else {
                $out->episodeOfCare = $this->episodeOfCare;
            }
        }
        if (isset($this->basedOn) && [] !== $this->basedOn) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_BASED_ON) && 1 === count($this->basedOn)) {
                $out->basedOn = $this->basedOn[0];
            } else {
                $out->basedOn = $this->basedOn;
            }
        }
        if (isset($this->participant) && [] !== $this->participant) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PARTICIPANT) && 1 === count($this->participant)) {
                $out->participant = $this->participant[0];
            } else {
                $out->participant = $this->participant;
            }
        }
        if (isset($this->appointment) && [] !== $this->appointment) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_APPOINTMENT) && 1 === count($this->appointment)) {
                $out->appointment = $this->appointment[0];
            } else {
                $out->appointment = $this->appointment;
            }
        }
        if (isset($this->period)) {
            $out->period = $this->period;
        }
        if (isset($this->length)) {
            $out->length = $this->length;
        }
        if (isset($this->reasonCode) && [] !== $this->reasonCode) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REASON_CODE) && 1 === count($this->reasonCode)) {
                $out->reasonCode = $this->reasonCode[0];
            } else {
                $out->reasonCode = $this->reasonCode;
            }
        }
        if (isset($this->reasonReference) && [] !== $this->reasonReference) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REASON_REFERENCE) && 1 === count($this->reasonReference)) {
                $out->reasonReference = $this->reasonReference[0];
            } else {
                $out->reasonReference = $this->reasonReference;
            }
        }
        if (isset($this->diagnosis) && [] !== $this->diagnosis) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DIAGNOSIS) && 1 === count($this->diagnosis)) {
                $out->diagnosis = $this->diagnosis[0];
            } else {
                $out->diagnosis = $this->diagnosis;
            }
        }
        if (isset($this->account) && [] !== $this->account) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ACCOUNT) && 1 === count($this->account)) {
                $out->account = $this->account[0];
            } else {
                $out->account = $this->account;
            }
        }
        if (isset($this->hospitalization)) {
            $out->hospitalization = $this->hospitalization;
        }
        if (isset($this->location) && [] !== $this->location) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_LOCATION) && 1 === count($this->location)) {
                $out->location = $this->location[0];
            } else {
                $out->location = $this->location;
            }
        }
        if (isset($this->serviceProvider)) {
            $out->serviceProvider = $this->serviceProvider;
        }
        if (isset($this->partOf)) {
            $out->partOf = $this->partOf;
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
