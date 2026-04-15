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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRClinicalImpressionStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionFinding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionInvestigation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClinicalImpressionStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
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
 * A record of a clinical assessment performed to determine what problem(s) may
 * affect the patient and before planning the treatments or management strategies
 * that are best to manage a patient's condition. Assessments are often 1:1 with a
 * clinical consultation / encounter, but this varies greatly depending on the
 * clinical workflow. This resource is called "ClinicalImpression" rather than
 * "ClinicalAssessment" to avoid confusion with the recording of assessment tools
 * such as Apgar score.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRClinicalImpression extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CLINICAL_IMPRESSION;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_STATUS_REASON = 'statusReason';
    public const FIELD_CODE = 'code';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_ENCOUNTER = 'encounter';
    public const FIELD_EFFECTIVE_DATE_TIME = 'effectiveDateTime';
    public const FIELD_EFFECTIVE_DATE_TIME_EXT = '_effectiveDateTime';
    public const FIELD_EFFECTIVE_PERIOD = 'effectivePeriod';
    public const FIELD_DATE = 'date';
    public const FIELD_DATE_EXT = '_date';
    public const FIELD_ASSESSOR = 'assessor';
    public const FIELD_PREVIOUS = 'previous';
    public const FIELD_PROBLEM = 'problem';
    public const FIELD_INVESTIGATION = 'investigation';
    public const FIELD_PROTOCOL = 'protocol';
    public const FIELD_PROTOCOL_EXT = '_protocol';
    public const FIELD_SUMMARY = 'summary';
    public const FIELD_SUMMARY_EXT = '_summary';
    public const FIELD_FINDING = 'finding';
    public const FIELD_PROGNOSIS_CODEABLE_CONCEPT = 'prognosisCodeableConcept';
    public const FIELD_PROGNOSIS_REFERENCE = 'prognosisReference';
    public const FIELD_SUPPORTING_INFO = 'supportingInfo';
    public const FIELD_NOTE = 'note';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_SUBJECT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_EFFECTIVE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SUMMARY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this clinical impression by the performer or
     * other systems which remain constant as the resource is updated and propagates
     * from server to server.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * The workflow state of a clinical impression.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies the workflow status of the assessment.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClinicalImpressionStatus
     */
    #[FHIRClinicalImpressionStatus]
    protected FHIRClinicalImpressionStatus $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Captures the reason for the current state of the ClinicalImpression.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $statusReason;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Categorizes the type of clinical assessment performed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $code;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A summary of the context and/or cause of the assessment - why / where it was
     * performed, and what patient events/status prompted it.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient or group of individuals assessed as part of this record.
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
     * The Encounter during which this ClinicalImpression was created or to which the
     * creation of this record is tightly associated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $encounter;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The point in time or period over which the subject was assessed. (choose any one
     * of effective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $effectiveDateTime;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The point in time or period over which the subject was assessed. (choose any one
     * of effective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $effectivePeriod;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates when the documentation of the assessment was complete.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $date;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The clinician performing the assessment.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $assessor;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A reference to the last assessment that was conducted on this patient.
     * Assessments are often/usually ongoing in nature; a care provider (practitioner
     * or team) will make new assessments on an ongoing basis as new data arises or the
     * patient's conditions changes.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $previous;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A list of the relevant problems/conditions for a patient.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $problem;
    /**
     * A record of a clinical assessment performed to determine what problem(s) may
     * affect the patient and before planning the treatments or management strategies
     * that are best to manage a patient's condition. Assessments are often 1:1 with a
     * clinical consultation / encounter, but this varies greatly depending on the
     * clinical workflow. This resource is called "ClinicalImpression" rather than
     * "ClinicalAssessment" to avoid confusion with the recording of assessment tools
     * such as Apgar score.
     *
     * One or more sets of investigations (signs, symptoms, etc.). The actual grouping
     * of investigations varies greatly depending on the type and context of the
     * assessment. These investigations may include data generated during the
     * assessment process, or data previously generated and recorded that is pertinent
     * to the outcomes.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionInvestigation>
     */
    #[FHIRClinicalImpressionInvestigation]
    protected array $investigation;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference to a specific published clinical protocol that was followed during
     * this assessment, and/or that provides evidence in support of the diagnosis.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    #[FHIRUri]
    protected array $protocol;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A text summary of the investigations and the diagnosis.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $summary;
    /**
     * A record of a clinical assessment performed to determine what problem(s) may
     * affect the patient and before planning the treatments or management strategies
     * that are best to manage a patient's condition. Assessments are often 1:1 with a
     * clinical consultation / encounter, but this varies greatly depending on the
     * clinical workflow. This resource is called "ClinicalImpression" rather than
     * "ClinicalAssessment" to avoid confusion with the recording of assessment tools
     * such as Apgar score.
     *
     * Specific findings or diagnoses that were considered likely or relevant to
     * ongoing treatment.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionFinding>
     */
    #[FHIRClinicalImpressionFinding]
    protected array $finding;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimate of likely outcome.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $prognosisCodeableConcept;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * RiskAssessment expressing likely outcome.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $prognosisReference;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information supporting the clinical impression.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $supportingInfo;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Commentary about the impression, typically recorded after the impression itself
     * was made, though supplemental notes by the original author could also appear.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;

    /* constructor.php:61 */
    /**
     * FHIRClinicalImpression Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRClinicalImpressionStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClinicalImpressionStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $statusReason
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $effectiveDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $effectivePeriod
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $date
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $assessor
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $previous
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $problem
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionInvestigation> $investigation
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri> $protocol
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $summary
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionFinding> $finding
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $prognosisCodeableConcept
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $prognosisReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $supportingInfo
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
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
                                null|string|FHIRClinicalImpressionStatusList|FHIRClinicalImpressionStatus $status = null,
                                null|FHIRCodeableConcept $statusReason = null,
                                null|FHIRCodeableConcept $code = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|FHIRReference $subject = null,
                                null|FHIRReference $encounter = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $effectiveDateTime = null,
                                null|FHIRPeriod $effectivePeriod = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $date = null,
                                null|FHIRReference $assessor = null,
                                null|FHIRReference $previous = null,
                                null|iterable $problem = null,
                                null|iterable $investigation = null,
                                null|iterable $protocol = null,
                                null|string|FHIRStringPrimitive|FHIRString $summary = null,
                                null|iterable $finding = null,
                                null|iterable $prognosisCodeableConcept = null,
                                null|iterable $prognosisReference = null,
                                null|iterable $supportingInfo = null,
                                null|iterable $note = null,
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
        if (null !== $statusReason) {
            $this->setStatusReason($statusReason);
        }
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $encounter) {
            $this->setEncounter($encounter);
        }
        if (null !== $effectiveDateTime) {
            $this->setEffectiveDateTime($effectiveDateTime);
        }
        if (null !== $effectivePeriod) {
            $this->setEffectivePeriod($effectivePeriod);
        }
        if (null !== $date) {
            $this->setDate($date);
        }
        if (null !== $assessor) {
            $this->setAssessor($assessor);
        }
        if (null !== $previous) {
            $this->setPrevious($previous);
        }
        if (null !== $problem) {
            $this->setProblem(...$problem);
        }
        if (null !== $investigation) {
            $this->setInvestigation(...$investigation);
        }
        if (null !== $protocol) {
            $this->setProtocol(...$protocol);
        }
        if (null !== $summary) {
            $this->setSummary($summary);
        }
        if (null !== $finding) {
            $this->setFinding(...$finding);
        }
        if (null !== $prognosisCodeableConcept) {
            $this->setPrognosisCodeableConcept(...$prognosisCodeableConcept);
        }
        if (null !== $prognosisReference) {
            $this->setPrognosisReference(...$prognosisReference);
        }
        if (null !== $supportingInfo) {
            $this->setSupportingInfo(...$supportingInfo);
        }
        if (null !== $note) {
            $this->setNote(...$note);
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
     * Business identifiers assigned to this clinical impression by the performer or
     * other systems which remain constant as the resource is updated and propagates
     * from server to server.
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
     * Business identifiers assigned to this clinical impression by the performer or
     * other systems which remain constant as the resource is updated and propagates
     * from server to server.
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
     * Business identifiers assigned to this clinical impression by the performer or
     * other systems which remain constant as the resource is updated and propagates
     * from server to server.
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
     * The workflow state of a clinical impression.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies the workflow status of the assessment.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClinicalImpressionStatus
     */
    public function getStatus(): null|FHIRClinicalImpressionStatus
    {
        return $this->status ?? null;
    }

    /**
     * The workflow state of a clinical impression.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies the workflow status of the assessment.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRClinicalImpressionStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClinicalImpressionStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRClinicalImpressionStatusList|FHIRClinicalImpressionStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRClinicalImpressionStatus)) {
            $status = new FHIRClinicalImpressionStatus(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Captures the reason for the current state of the ClinicalImpression.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getStatusReason(): null|FHIRCodeableConcept
    {
        return $this->statusReason ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Captures the reason for the current state of the ClinicalImpression.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $statusReason
     * @return static
     */
    public function setStatusReason(null|FHIRCodeableConcept $statusReason): self
    {
        if (null === $statusReason) {
            unset($this->statusReason);
            return $this;
        }
        $this->statusReason = $statusReason;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Categorizes the type of clinical assessment performed.
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
     * Categorizes the type of clinical assessment performed.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A summary of the context and/or cause of the assessment - why / where it was
     * performed, and what patient events/status prompted it.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDescription(): null|FHIRString
    {
        return $this->description ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A summary of the context and/or cause of the assessment - why / where it was
     * performed, and what patient events/status prompted it.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @return static
     */
    public function setDescription(null|string|FHIRStringPrimitive|FHIRString $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRString)) {
            $description = new FHIRString(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient or group of individuals assessed as part of this record.
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
     * The patient or group of individuals assessed as part of this record.
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
     * The Encounter during which this ClinicalImpression was created or to which the
     * creation of this record is tightly associated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getEncounter(): null|FHIRReference
    {
        return $this->encounter ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Encounter during which this ClinicalImpression was created or to which the
     * creation of this record is tightly associated.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @return static
     */
    public function setEncounter(null|FHIRReference $encounter): self
    {
        if (null === $encounter) {
            unset($this->encounter);
            return $this;
        }
        $this->encounter = $encounter;
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
     * The point in time or period over which the subject was assessed. (choose any one
     * of effective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getEffectiveDateTime(): null|FHIRDateTime
    {
        return $this->effectiveDateTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The point in time or period over which the subject was assessed. (choose any one
     * of effective*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $effectiveDateTime
     * @return static
     */
    public function setEffectiveDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $effectiveDateTime): self
    {
        if (null === $effectiveDateTime) {
            unset($this->effectiveDateTime);
            return $this;
        }
        if (!($effectiveDateTime instanceof FHIRDateTime)) {
            $effectiveDateTime = new FHIRDateTime(value: $effectiveDateTime);
        }
        $this->effectiveDateTime = $effectiveDateTime;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The point in time or period over which the subject was assessed. (choose any one
     * of effective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getEffectivePeriod(): null|FHIRPeriod
    {
        return $this->effectivePeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The point in time or period over which the subject was assessed. (choose any one
     * of effective*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $effectivePeriod
     * @return static
     */
    public function setEffectivePeriod(null|FHIRPeriod $effectivePeriod): self
    {
        if (null === $effectivePeriod) {
            unset($this->effectivePeriod);
            return $this;
        }
        $this->effectivePeriod = $effectivePeriod;
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
     * Indicates when the documentation of the assessment was complete.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getDate(): null|FHIRDateTime
    {
        return $this->date ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates when the documentation of the assessment was complete.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $date
     * @return static
     */
    public function setDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $date): self
    {
        if (null === $date) {
            unset($this->date);
            return $this;
        }
        if (!($date instanceof FHIRDateTime)) {
            $date = new FHIRDateTime(value: $date);
        }
        $this->date = $date;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The clinician performing the assessment.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getAssessor(): null|FHIRReference
    {
        return $this->assessor ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The clinician performing the assessment.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $assessor
     * @return static
     */
    public function setAssessor(null|FHIRReference $assessor): self
    {
        if (null === $assessor) {
            unset($this->assessor);
            return $this;
        }
        $this->assessor = $assessor;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A reference to the last assessment that was conducted on this patient.
     * Assessments are often/usually ongoing in nature; a care provider (practitioner
     * or team) will make new assessments on an ongoing basis as new data arises or the
     * patient's conditions changes.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPrevious(): null|FHIRReference
    {
        return $this->previous ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A reference to the last assessment that was conducted on this patient.
     * Assessments are often/usually ongoing in nature; a care provider (practitioner
     * or team) will make new assessments on an ongoing basis as new data arises or the
     * patient's conditions changes.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $previous
     * @return static
     */
    public function setPrevious(null|FHIRReference $previous): self
    {
        if (null === $previous) {
            unset($this->previous);
            return $this;
        }
        $this->previous = $previous;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A list of the relevant problems/conditions for a patient.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getProblem(): array
    {
        return $this->problem ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getProblemIterator(): iterable
    {
        if (!isset($this->problem)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->problem);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A list of the relevant problems/conditions for a patient.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $problem
     * @return static
     */
    public function addProblem(FHIRReference $problem): self
    {
        if (!isset($this->problem)) {
            $this->problem = [];
        }
        $this->problem[] = $problem;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A list of the relevant problems/conditions for a patient.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$problem
     * @return static
     */
    public function setProblem(FHIRReference ...$problem): self
    {
        if ([] === $problem) {
            unset($this->problem);
            return $this;
        }
        $this->problem = $problem;
        return $this;
    }

    /**
     * A record of a clinical assessment performed to determine what problem(s) may
     * affect the patient and before planning the treatments or management strategies
     * that are best to manage a patient's condition. Assessments are often 1:1 with a
     * clinical consultation / encounter, but this varies greatly depending on the
     * clinical workflow. This resource is called "ClinicalImpression" rather than
     * "ClinicalAssessment" to avoid confusion with the recording of assessment tools
     * such as Apgar score.
     *
     * One or more sets of investigations (signs, symptoms, etc.). The actual grouping
     * of investigations varies greatly depending on the type and context of the
     * assessment. These investigations may include data generated during the
     * assessment process, or data previously generated and recorded that is pertinent
     * to the outcomes.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionInvestigation>
     */
    public function getInvestigation(): array
    {
        return $this->investigation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionInvestigation>
     */
    public function getInvestigationIterator(): iterable
    {
        if (!isset($this->investigation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->investigation);
    }

    /**
     * A record of a clinical assessment performed to determine what problem(s) may
     * affect the patient and before planning the treatments or management strategies
     * that are best to manage a patient's condition. Assessments are often 1:1 with a
     * clinical consultation / encounter, but this varies greatly depending on the
     * clinical workflow. This resource is called "ClinicalImpression" rather than
     * "ClinicalAssessment" to avoid confusion with the recording of assessment tools
     * such as Apgar score.
     *
     * One or more sets of investigations (signs, symptoms, etc.). The actual grouping
     * of investigations varies greatly depending on the type and context of the
     * assessment. These investigations may include data generated during the
     * assessment process, or data previously generated and recorded that is pertinent
     * to the outcomes.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionInvestigation $investigation
     * @return static
     */
    public function addInvestigation(FHIRClinicalImpressionInvestigation $investigation): self
    {
        if (!isset($this->investigation)) {
            $this->investigation = [];
        }
        $this->investigation[] = $investigation;
        return $this;
    }

    /**
     * A record of a clinical assessment performed to determine what problem(s) may
     * affect the patient and before planning the treatments or management strategies
     * that are best to manage a patient's condition. Assessments are often 1:1 with a
     * clinical consultation / encounter, but this varies greatly depending on the
     * clinical workflow. This resource is called "ClinicalImpression" rather than
     * "ClinicalAssessment" to avoid confusion with the recording of assessment tools
     * such as Apgar score.
     *
     * One or more sets of investigations (signs, symptoms, etc.). The actual grouping
     * of investigations varies greatly depending on the type and context of the
     * assessment. These investigations may include data generated during the
     * assessment process, or data previously generated and recorded that is pertinent
     * to the outcomes.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionInvestigation ...$investigation
     * @return static
     */
    public function setInvestigation(FHIRClinicalImpressionInvestigation ...$investigation): self
    {
        if ([] === $investigation) {
            unset($this->investigation);
            return $this;
        }
        $this->investigation = $investigation;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference to a specific published clinical protocol that was followed during
     * this assessment, and/or that provides evidence in support of the diagnosis.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getProtocol(): array
    {
        return $this->protocol ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getProtocolIterator(): iterable
    {
        if (!isset($this->protocol)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->protocol);
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference to a specific published clinical protocol that was followed during
     * this assessment, and/or that provides evidence in support of the diagnosis.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $protocol
     * @return static
     */
    public function addProtocol(string|FHIRUriPrimitive|FHIRUri $protocol): self
    {
        if (!($protocol instanceof FHIRUri)) {
            $protocol = new FHIRUri(value: $protocol);
        }
        if (!isset($this->protocol)) {
            $this->protocol = [];
        }
        $this->protocol[] = $protocol;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference to a specific published clinical protocol that was followed during
     * this assessment, and/or that provides evidence in support of the diagnosis.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri ...$protocol
     * @return static
     */
    public function setProtocol(string|FHIRUriPrimitive|FHIRUri ...$protocol): self
    {
        if ([] === $protocol) {
            unset($this->protocol);
            return $this;
        }
        $this->protocol = [];
        foreach($protocol as $v) {
            if ($v instanceof FHIRUri) {
                $this->protocol[] = $v;
            } else {
                $this->protocol[] = new FHIRUri(value: $v);
            }
        }
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A text summary of the investigations and the diagnosis.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getSummary(): null|FHIRString
    {
        return $this->summary ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A text summary of the investigations and the diagnosis.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $summary
     * @return static
     */
    public function setSummary(null|string|FHIRStringPrimitive|FHIRString $summary): self
    {
        if (null === $summary) {
            unset($this->summary);
            return $this;
        }
        if (!($summary instanceof FHIRString)) {
            $summary = new FHIRString(value: $summary);
        }
        $this->summary = $summary;
        return $this;
    }

    /**
     * A record of a clinical assessment performed to determine what problem(s) may
     * affect the patient and before planning the treatments or management strategies
     * that are best to manage a patient's condition. Assessments are often 1:1 with a
     * clinical consultation / encounter, but this varies greatly depending on the
     * clinical workflow. This resource is called "ClinicalImpression" rather than
     * "ClinicalAssessment" to avoid confusion with the recording of assessment tools
     * such as Apgar score.
     *
     * Specific findings or diagnoses that were considered likely or relevant to
     * ongoing treatment.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionFinding>
     */
    public function getFinding(): array
    {
        return $this->finding ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionFinding>
     */
    public function getFindingIterator(): iterable
    {
        if (!isset($this->finding)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->finding);
    }

    /**
     * A record of a clinical assessment performed to determine what problem(s) may
     * affect the patient and before planning the treatments or management strategies
     * that are best to manage a patient's condition. Assessments are often 1:1 with a
     * clinical consultation / encounter, but this varies greatly depending on the
     * clinical workflow. This resource is called "ClinicalImpression" rather than
     * "ClinicalAssessment" to avoid confusion with the recording of assessment tools
     * such as Apgar score.
     *
     * Specific findings or diagnoses that were considered likely or relevant to
     * ongoing treatment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionFinding $finding
     * @return static
     */
    public function addFinding(FHIRClinicalImpressionFinding $finding): self
    {
        if (!isset($this->finding)) {
            $this->finding = [];
        }
        $this->finding[] = $finding;
        return $this;
    }

    /**
     * A record of a clinical assessment performed to determine what problem(s) may
     * affect the patient and before planning the treatments or management strategies
     * that are best to manage a patient's condition. Assessments are often 1:1 with a
     * clinical consultation / encounter, but this varies greatly depending on the
     * clinical workflow. This resource is called "ClinicalImpression" rather than
     * "ClinicalAssessment" to avoid confusion with the recording of assessment tools
     * such as Apgar score.
     *
     * Specific findings or diagnoses that were considered likely or relevant to
     * ongoing treatment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRClinicalImpression\FHIRClinicalImpressionFinding ...$finding
     * @return static
     */
    public function setFinding(FHIRClinicalImpressionFinding ...$finding): self
    {
        if ([] === $finding) {
            unset($this->finding);
            return $this;
        }
        $this->finding = $finding;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimate of likely outcome.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPrognosisCodeableConcept(): array
    {
        return $this->prognosisCodeableConcept ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPrognosisCodeableConceptIterator(): iterable
    {
        if (!isset($this->prognosisCodeableConcept)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->prognosisCodeableConcept);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimate of likely outcome.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $prognosisCodeableConcept
     * @return static
     */
    public function addPrognosisCodeableConcept(FHIRCodeableConcept $prognosisCodeableConcept): self
    {
        if (!isset($this->prognosisCodeableConcept)) {
            $this->prognosisCodeableConcept = [];
        }
        $this->prognosisCodeableConcept[] = $prognosisCodeableConcept;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimate of likely outcome.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$prognosisCodeableConcept
     * @return static
     */
    public function setPrognosisCodeableConcept(FHIRCodeableConcept ...$prognosisCodeableConcept): self
    {
        if ([] === $prognosisCodeableConcept) {
            unset($this->prognosisCodeableConcept);
            return $this;
        }
        $this->prognosisCodeableConcept = $prognosisCodeableConcept;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * RiskAssessment expressing likely outcome.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPrognosisReference(): array
    {
        return $this->prognosisReference ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPrognosisReferenceIterator(): iterable
    {
        if (!isset($this->prognosisReference)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->prognosisReference);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * RiskAssessment expressing likely outcome.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $prognosisReference
     * @return static
     */
    public function addPrognosisReference(FHIRReference $prognosisReference): self
    {
        if (!isset($this->prognosisReference)) {
            $this->prognosisReference = [];
        }
        $this->prognosisReference[] = $prognosisReference;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * RiskAssessment expressing likely outcome.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$prognosisReference
     * @return static
     */
    public function setPrognosisReference(FHIRReference ...$prognosisReference): self
    {
        if ([] === $prognosisReference) {
            unset($this->prognosisReference);
            return $this;
        }
        $this->prognosisReference = $prognosisReference;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information supporting the clinical impression.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSupportingInfo(): array
    {
        return $this->supportingInfo ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSupportingInfoIterator(): iterable
    {
        if (!isset($this->supportingInfo)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->supportingInfo);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information supporting the clinical impression.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $supportingInfo
     * @return static
     */
    public function addSupportingInfo(FHIRReference $supportingInfo): self
    {
        if (!isset($this->supportingInfo)) {
            $this->supportingInfo = [];
        }
        $this->supportingInfo[] = $supportingInfo;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information supporting the clinical impression.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$supportingInfo
     * @return static
     */
    public function setSupportingInfo(FHIRReference ...$supportingInfo): self
    {
        if ([] === $supportingInfo) {
            unset($this->supportingInfo);
            return $this;
        }
        $this->supportingInfo = $supportingInfo;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Commentary about the impression, typically recorded after the impression itself
     * was made, though supplemental notes by the original author could also appear.
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
     * Commentary about the impression, typically recorded after the impression itself
     * was made, though supplemental notes by the original author could also appear.
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
     * Commentary about the impression, typically recorded after the impression itself
     * was made, though supplemental notes by the original author could also appear.
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

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClinicalImpression $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClinicalImpression
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRClinicalImpression)) {
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
                $type->setStatus(FHIRClinicalImpressionStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS_REASON === $cen) {
                $type->setStatusReason(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENCOUNTER === $cen) {
                $type->setEncounter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EFFECTIVE_DATE_TIME === $cen) {
                $type->setEffectiveDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EFFECTIVE_PERIOD === $cen) {
                $type->setEffectivePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATE === $cen) {
                $type->setDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ASSESSOR === $cen) {
                $type->setAssessor(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PREVIOUS === $cen) {
                $type->setPrevious(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROBLEM === $cen) {
                $type->addProblem(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INVESTIGATION === $cen) {
                $type->addInvestigation(FHIRClinicalImpressionInvestigation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROTOCOL === $cen) {
                $type->addProtocol(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUMMARY === $cen) {
                $type->setSummary(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FINDING === $cen) {
                $type->addFinding(FHIRClinicalImpressionFinding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROGNOSIS_CODEABLE_CONCEPT === $cen) {
                $type->addPrognosisCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROGNOSIS_REFERENCE === $cen) {
                $type->addPrognosisReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUPPORTING_INFO === $cen) {
                $type->addSupportingInfo(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EFFECTIVE_DATE_TIME])) {
            if (isset($type->effectiveDateTime)) {
                $type->effectiveDateTime->setValue((string)$attributes[self::FIELD_EFFECTIVE_DATE_TIME]);
            } else {
                $type->setEffectiveDateTime((string)$attributes[self::FIELD_EFFECTIVE_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EFFECTIVE_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DATE])) {
            if (isset($type->date)) {
                $type->date->setValue((string)$attributes[self::FIELD_DATE]);
            } else {
                $type->setDate((string)$attributes[self::FIELD_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SUMMARY])) {
            if (isset($type->summary)) {
                $type->summary->setValue((string)$attributes[self::FIELD_SUMMARY]);
            } else {
                $type->setSummary((string)$attributes[self::FIELD_SUMMARY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SUMMARY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('ClinicalImpression', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->effectiveDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EFFECTIVE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_EFFECTIVE_DATE_TIME, $this->effectiveDateTime->_getValueAsString());
        }
        if (isset($this->date) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DATE]) {
            $xw->writeAttribute(self::FIELD_DATE, $this->date->_getValueAsString());
        }
        if (isset($this->summary) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SUMMARY]) {
            $xw->writeAttribute(self::FIELD_SUMMARY, $this->summary->_getValueAsString());
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
        if (isset($this->statusReason)) {
            $xw->startElement(self::FIELD_STATUS_REASON);
            $this->statusReason->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->subject)) {
            $xw->startElement(self::FIELD_SUBJECT);
            $this->subject->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->encounter)) {
            $xw->startElement(self::FIELD_ENCOUNTER);
            $this->encounter->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->effectiveDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EFFECTIVE_DATE_TIME]
                || $this->effectiveDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EFFECTIVE_DATE_TIME);
            $this->effectiveDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EFFECTIVE_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->effectivePeriod)) {
            $xw->startElement(self::FIELD_EFFECTIVE_PERIOD);
            $this->effectivePeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->date)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DATE]
                || $this->date->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DATE);
            $this->date->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DATE]);
            $xw->endElement();
        }
        if (isset($this->assessor)) {
            $xw->startElement(self::FIELD_ASSESSOR);
            $this->assessor->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->previous)) {
            $xw->startElement(self::FIELD_PREVIOUS);
            $this->previous->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->problem)) {
            foreach ($this->problem as $v) {
                $xw->startElement(self::FIELD_PROBLEM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->investigation)) {
            foreach ($this->investigation as $v) {
                $xw->startElement(self::FIELD_INVESTIGATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->protocol) && [] !== $this->protocol) {
            foreach($this->protocol as $v) {
                $xw->startElement(self::FIELD_PROTOCOL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->summary)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SUMMARY]
                || $this->summary->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SUMMARY);
            $this->summary->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SUMMARY]);
            $xw->endElement();
        }
        if (isset($this->finding)) {
            foreach ($this->finding as $v) {
                $xw->startElement(self::FIELD_FINDING);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->prognosisCodeableConcept)) {
            foreach ($this->prognosisCodeableConcept as $v) {
                $xw->startElement(self::FIELD_PROGNOSIS_CODEABLE_CONCEPT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->prognosisReference)) {
            foreach ($this->prognosisReference as $v) {
                $xw->startElement(self::FIELD_PROGNOSIS_REFERENCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->supportingInfo)) {
            foreach ($this->supportingInfo as $v) {
                $xw->startElement(self::FIELD_SUPPORTING_INFO);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClinicalImpression $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRClinicalImpression
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
        } else if (!($type instanceof FHIRClinicalImpression)) {
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
            $type->setStatus(FHIRClinicalImpressionStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->statusReason) || property_exists($decoded, self::FIELD_STATUS_REASON)) {
            if (is_array($decoded->statusReason)) {
                $type->setStatusReason(FHIRCodeableConcept::jsonUnserialize(reset($decoded->statusReason), $config));
            } else {
                $type->setStatusReason(FHIRCodeableConcept::jsonUnserialize($decoded->statusReason, $config));
            }
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
            }
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_array($decoded->subject)) {
                $type->setSubject(FHIRReference::jsonUnserialize(reset($decoded->subject), $config));
            } else {
                $type->setSubject(FHIRReference::jsonUnserialize($decoded->subject, $config));
            }
        }
        if (isset($decoded->encounter) || property_exists($decoded, self::FIELD_ENCOUNTER)) {
            if (is_array($decoded->encounter)) {
                $type->setEncounter(FHIRReference::jsonUnserialize(reset($decoded->encounter), $config));
            } else {
                $type->setEncounter(FHIRReference::jsonUnserialize($decoded->encounter, $config));
            }
        }
        if (isset($decoded->effectiveDateTime)
            || isset($decoded->_effectiveDateTime)
            || property_exists($decoded, self::FIELD_EFFECTIVE_DATE_TIME)
            || property_exists($decoded, self::FIELD_EFFECTIVE_DATE_TIME_EXT)) {
            $v = $decoded->_effectiveDateTime ?? new \stdClass();
            $v->value = $decoded->effectiveDateTime ?? null;
            $type->setEffectiveDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->effectivePeriod) || property_exists($decoded, self::FIELD_EFFECTIVE_PERIOD)) {
            if (is_array($decoded->effectivePeriod)) {
                $type->setEffectivePeriod(FHIRPeriod::jsonUnserialize(reset($decoded->effectivePeriod), $config));
            } else {
                $type->setEffectivePeriod(FHIRPeriod::jsonUnserialize($decoded->effectivePeriod, $config));
            }
        }
        if (isset($decoded->date)
            || isset($decoded->_date)
            || property_exists($decoded, self::FIELD_DATE)
            || property_exists($decoded, self::FIELD_DATE_EXT)) {
            $v = $decoded->_date ?? new \stdClass();
            $v->value = $decoded->date ?? null;
            $type->setDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->assessor) || property_exists($decoded, self::FIELD_ASSESSOR)) {
            if (is_array($decoded->assessor)) {
                $type->setAssessor(FHIRReference::jsonUnserialize(reset($decoded->assessor), $config));
            } else {
                $type->setAssessor(FHIRReference::jsonUnserialize($decoded->assessor, $config));
            }
        }
        if (isset($decoded->previous) || property_exists($decoded, self::FIELD_PREVIOUS)) {
            if (is_array($decoded->previous)) {
                $type->setPrevious(FHIRReference::jsonUnserialize(reset($decoded->previous), $config));
            } else {
                $type->setPrevious(FHIRReference::jsonUnserialize($decoded->previous, $config));
            }
        }
        if (isset($decoded->problem) || property_exists($decoded, self::FIELD_PROBLEM)) {
            if (is_object($decoded->problem)) {
                $vals = [$decoded->problem];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROBLEM, true);
            } else {
                $vals = $decoded->problem;
            }
            foreach($vals as $v) {
                $type->addProblem(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->investigation) || property_exists($decoded, self::FIELD_INVESTIGATION)) {
            if (is_object($decoded->investigation)) {
                $vals = [$decoded->investigation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INVESTIGATION, true);
            } else {
                $vals = $decoded->investigation;
            }
            foreach($vals as $v) {
                $type->addInvestigation(FHIRClinicalImpressionInvestigation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->protocol)
            || isset($decoded->_protocol)
            || property_exists($decoded, self::FIELD_PROTOCOL)
            || property_exists($decoded, self::FIELD_PROTOCOL_EXT)) {
            $vals = (array)($decoded->protocol ?? []);
            $exts = (array)($decoded->_protocol ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addProtocol(FHIRUri::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->summary)
            || isset($decoded->_summary)
            || property_exists($decoded, self::FIELD_SUMMARY)
            || property_exists($decoded, self::FIELD_SUMMARY_EXT)) {
            $v = $decoded->_summary ?? new \stdClass();
            $v->value = $decoded->summary ?? null;
            $type->setSummary(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->finding) || property_exists($decoded, self::FIELD_FINDING)) {
            if (is_object($decoded->finding)) {
                $vals = [$decoded->finding];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_FINDING, true);
            } else {
                $vals = $decoded->finding;
            }
            foreach($vals as $v) {
                $type->addFinding(FHIRClinicalImpressionFinding::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->prognosisCodeableConcept) || property_exists($decoded, self::FIELD_PROGNOSIS_CODEABLE_CONCEPT)) {
            if (is_object($decoded->prognosisCodeableConcept)) {
                $vals = [$decoded->prognosisCodeableConcept];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROGNOSIS_CODEABLE_CONCEPT, true);
            } else {
                $vals = $decoded->prognosisCodeableConcept;
            }
            foreach($vals as $v) {
                $type->addPrognosisCodeableConcept(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->prognosisReference) || property_exists($decoded, self::FIELD_PROGNOSIS_REFERENCE)) {
            if (is_object($decoded->prognosisReference)) {
                $vals = [$decoded->prognosisReference];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROGNOSIS_REFERENCE, true);
            } else {
                $vals = $decoded->prognosisReference;
            }
            foreach($vals as $v) {
                $type->addPrognosisReference(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->supportingInfo) || property_exists($decoded, self::FIELD_SUPPORTING_INFO)) {
            if (is_object($decoded->supportingInfo)) {
                $vals = [$decoded->supportingInfo];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFO, true);
            } else {
                $vals = $decoded->supportingInfo;
            }
            foreach($vals as $v) {
                $type->addSupportingInfo(FHIRReference::jsonUnserialize($v, $config));
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
        if (isset($this->statusReason)) {
            $out->statusReason = $this->statusReason;
        }
        if (isset($this->code)) {
            $out->code = $this->code;
        }
        if (isset($this->description)) {
            if (null !== ($val = $this->description->getValue())) {
                $out->description = $val;
            }
            if ($this->description->_nonValueFieldDefined()) {
                $ext = $this->description->jsonSerialize();
                unset($ext->value);
                $out->_description = $ext;
            }
        }
        if (isset($this->subject)) {
            $out->subject = $this->subject;
        }
        if (isset($this->encounter)) {
            $out->encounter = $this->encounter;
        }
        if (isset($this->effectiveDateTime)) {
            if (null !== ($val = $this->effectiveDateTime->getValue())) {
                $out->effectiveDateTime = $val;
            }
            if ($this->effectiveDateTime->_nonValueFieldDefined()) {
                $ext = $this->effectiveDateTime->jsonSerialize();
                unset($ext->value);
                $out->_effectiveDateTime = $ext;
            }
        }
        if (isset($this->effectivePeriod)) {
            $out->effectivePeriod = $this->effectivePeriod;
        }
        if (isset($this->date)) {
            if (null !== ($val = $this->date->getValue())) {
                $out->date = $val;
            }
            if ($this->date->_nonValueFieldDefined()) {
                $ext = $this->date->jsonSerialize();
                unset($ext->value);
                $out->_date = $ext;
            }
        }
        if (isset($this->assessor)) {
            $out->assessor = $this->assessor;
        }
        if (isset($this->previous)) {
            $out->previous = $this->previous;
        }
        if (isset($this->problem) && [] !== $this->problem) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROBLEM) && 1 === count($this->problem)) {
                $out->problem = $this->problem[0];
            } else {
                $out->problem = $this->problem;
            }
        }
        if (isset($this->investigation) && [] !== $this->investigation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INVESTIGATION) && 1 === count($this->investigation)) {
                $out->investigation = $this->investigation[0];
            } else {
                $out->investigation = $this->investigation;
            }
        }
        if (isset($this->protocol) && [] !== $this->protocol) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->protocol as $v) {
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
                $out->protocol = $vals;
            }
            if ($hasExts) {
                $out->_protocol = $exts;
            }
        }
        if (isset($this->summary)) {
            if (null !== ($val = $this->summary->getValue())) {
                $out->summary = $val;
            }
            if ($this->summary->_nonValueFieldDefined()) {
                $ext = $this->summary->jsonSerialize();
                unset($ext->value);
                $out->_summary = $ext;
            }
        }
        if (isset($this->finding) && [] !== $this->finding) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_FINDING) && 1 === count($this->finding)) {
                $out->finding = $this->finding[0];
            } else {
                $out->finding = $this->finding;
            }
        }
        if (isset($this->prognosisCodeableConcept) && [] !== $this->prognosisCodeableConcept) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROGNOSIS_CODEABLE_CONCEPT) && 1 === count($this->prognosisCodeableConcept)) {
                $out->prognosisCodeableConcept = $this->prognosisCodeableConcept[0];
            } else {
                $out->prognosisCodeableConcept = $this->prognosisCodeableConcept;
            }
        }
        if (isset($this->prognosisReference) && [] !== $this->prognosisReference) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROGNOSIS_REFERENCE) && 1 === count($this->prognosisReference)) {
                $out->prognosisReference = $this->prognosisReference[0];
            } else {
                $out->prognosisReference = $this->prognosisReference;
            }
        }
        if (isset($this->supportingInfo) && [] !== $this->supportingInfo) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFO) && 1 === count($this->supportingInfo)) {
                $out->supportingInfo = $this->supportingInfo[0];
            } else {
                $out->supportingInfo = $this->supportingInfo;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
