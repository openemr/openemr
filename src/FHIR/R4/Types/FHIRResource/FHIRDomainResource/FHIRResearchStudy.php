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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRResearchStudyStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyArm;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyObjective;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResearchStudyStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * A process where a researcher or organization plans and then executes a series of
 * steps intended to increase the field of healthcare-related knowledge. This
 * includes studies of safety, efficacy, comparative effectiveness and other
 * information about medications, devices, therapies and other interventional and
 * investigative techniques. A ResearchStudy involves the gathering of information
 * about human or animal subjects.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRResearchStudy extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_RESEARCH_STUDY;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_TITLE = 'title';
    public const FIELD_TITLE_EXT = '_title';
    public const FIELD_PROTOCOL = 'protocol';
    public const FIELD_PART_OF = 'partOf';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_PRIMARY_PURPOSE_TYPE = 'primaryPurposeType';
    public const FIELD_PHASE = 'phase';
    public const FIELD_CATEGORY = 'category';
    public const FIELD_FOCUS = 'focus';
    public const FIELD_CONDITION = 'condition';
    public const FIELD_CONTACT = 'contact';
    public const FIELD_RELATED_ARTIFACT = 'relatedArtifact';
    public const FIELD_KEYWORD = 'keyword';
    public const FIELD_LOCATION = 'location';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_ENROLLMENT = 'enrollment';
    public const FIELD_PERIOD = 'period';
    public const FIELD_SPONSOR = 'sponsor';
    public const FIELD_PRINCIPAL_INVESTIGATOR = 'principalInvestigator';
    public const FIELD_SITE = 'site';
    public const FIELD_REASON_STOPPED = 'reasonStopped';
    public const FIELD_NOTE = 'note';
    public const FIELD_ARM = 'arm';
    public const FIELD_OBJECTIVE = 'objective';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_TITLE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifiers assigned to this research study by the sponsor or other systems.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short, descriptive user-friendly label for the study.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $title;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of steps expected to be performed as part of the execution of the study.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $protocol;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A larger research study of which this particular study is a component or step.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $partOf;
    /**
     * Codes that convey the current status of the research study.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of the study.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResearchStudyStatus
     */
    #[FHIRResearchStudyStatus]
    protected FHIRResearchStudyStatus $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of study based upon the intent of the study's activities. A
     * classification of the intent of the study.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $primaryPurposeType;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The stage in the progression of a therapy from initial experimental use in
     * humans in clinical trials to post-market evaluation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $phase;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes categorizing the type of study such as investigational vs. observational,
     * type of blinding, type of randomization, safety vs. efficacy, etc.
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
     * The medication(s), food(s), therapy(ies), device(s) or other concerns or
     * interventions that the study is seeking to gain more information about.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $focus;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The condition that is the focus of the study. For example, In a study to examine
     * risk factors for Lupus, might have as an inclusion criterion "healthy
     * volunteer", but the target condition code would be a Lupus SNOMED code.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $condition;
    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details to assist a user in learning more about or engaging with the
     * study.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail>
     */
    #[FHIRContactDetail]
    protected array $contact;
    /**
     * Related artifacts such as additional documentation, justification, or
     * bibliographic references.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Citations, references and other related documents.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact>
     */
    #[FHIRRelatedArtifact]
    protected array $relatedArtifact;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Key terms to aid in searching for or filtering the study.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $keyword;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a country, state or other region where the study is taking place.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $location;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A full description of how the study is being conducted.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $description;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to a Group that defines the criteria for and quantity of subjects
     * participating in the study. E.g. " 200 female Europeans between the ages of 20
     * and 45 with early onset diabetes".
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $enrollment;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the start date and the expected (or actual, depending on status) end
     * date for the study.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $period;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An organization that initiates the investigation and is legally responsible for
     * the study.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $sponsor;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A researcher in a study who oversees multiple aspects of the study, such as
     * concept development, protocol writing, protocol submission for IRB approval,
     * participant recruitment, informed consent, data collection, analysis,
     * interpretation and presentation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $principalInvestigator;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A facility in which study activities are conducted.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $site;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A description and/or code explaining the premature termination of the study.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $reasonStopped;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Comments made about the study by the performer, subject or other participants.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;
    /**
     * A process where a researcher or organization plans and then executes a series of
     * steps intended to increase the field of healthcare-related knowledge. This
     * includes studies of safety, efficacy, comparative effectiveness and other
     * information about medications, devices, therapies and other interventional and
     * investigative techniques. A ResearchStudy involves the gathering of information
     * about human or animal subjects.
     *
     * Describes an expected sequence of events for one of the participants of a study.
     * E.g. Exposure to drug A, wash-out, exposure to drug B, wash-out, follow-up.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyArm>
     */
    #[FHIRResearchStudyArm]
    protected array $arm;
    /**
     * A process where a researcher or organization plans and then executes a series of
     * steps intended to increase the field of healthcare-related knowledge. This
     * includes studies of safety, efficacy, comparative effectiveness and other
     * information about medications, devices, therapies and other interventional and
     * investigative techniques. A ResearchStudy involves the gathering of information
     * about human or animal subjects.
     *
     * A goal that the study is aiming to achieve in terms of a scientific question to
     * be answered by the analysis of data collected during the study.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyObjective>
     */
    #[FHIRResearchStudyObjective]
    protected array $objective;

    /* constructor.php:61 */
    /**
     * FHIRResearchStudy Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $title
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $protocol
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $partOf
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRResearchStudyStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResearchStudyStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $primaryPurposeType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $phase
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $category
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $focus
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $condition
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail> $contact
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact> $relatedArtifact
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $keyword
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $location
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $description
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $enrollment
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $sponsor
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $principalInvestigator
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $site
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reasonStopped
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyArm> $arm
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyObjective> $objective
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
                                null|string|FHIRStringPrimitive|FHIRString $title = null,
                                null|iterable $protocol = null,
                                null|iterable $partOf = null,
                                null|string|FHIRResearchStudyStatusList|FHIRResearchStudyStatus $status = null,
                                null|FHIRCodeableConcept $primaryPurposeType = null,
                                null|FHIRCodeableConcept $phase = null,
                                null|iterable $category = null,
                                null|iterable $focus = null,
                                null|iterable $condition = null,
                                null|iterable $contact = null,
                                null|iterable $relatedArtifact = null,
                                null|iterable $keyword = null,
                                null|iterable $location = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $description = null,
                                null|iterable $enrollment = null,
                                null|FHIRPeriod $period = null,
                                null|FHIRReference $sponsor = null,
                                null|FHIRReference $principalInvestigator = null,
                                null|iterable $site = null,
                                null|FHIRCodeableConcept $reasonStopped = null,
                                null|iterable $note = null,
                                null|iterable $arm = null,
                                null|iterable $objective = null,
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
        if (null !== $title) {
            $this->setTitle($title);
        }
        if (null !== $protocol) {
            $this->setProtocol(...$protocol);
        }
        if (null !== $partOf) {
            $this->setPartOf(...$partOf);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $primaryPurposeType) {
            $this->setPrimaryPurposeType($primaryPurposeType);
        }
        if (null !== $phase) {
            $this->setPhase($phase);
        }
        if (null !== $category) {
            $this->setCategory(...$category);
        }
        if (null !== $focus) {
            $this->setFocus(...$focus);
        }
        if (null !== $condition) {
            $this->setCondition(...$condition);
        }
        if (null !== $contact) {
            $this->setContact(...$contact);
        }
        if (null !== $relatedArtifact) {
            $this->setRelatedArtifact(...$relatedArtifact);
        }
        if (null !== $keyword) {
            $this->setKeyword(...$keyword);
        }
        if (null !== $location) {
            $this->setLocation(...$location);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $enrollment) {
            $this->setEnrollment(...$enrollment);
        }
        if (null !== $period) {
            $this->setPeriod($period);
        }
        if (null !== $sponsor) {
            $this->setSponsor($sponsor);
        }
        if (null !== $principalInvestigator) {
            $this->setPrincipalInvestigator($principalInvestigator);
        }
        if (null !== $site) {
            $this->setSite(...$site);
        }
        if (null !== $reasonStopped) {
            $this->setReasonStopped($reasonStopped);
        }
        if (null !== $note) {
            $this->setNote(...$note);
        }
        if (null !== $arm) {
            $this->setArm(...$arm);
        }
        if (null !== $objective) {
            $this->setObjective(...$objective);
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
     * Identifiers assigned to this research study by the sponsor or other systems.
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
     * Identifiers assigned to this research study by the sponsor or other systems.
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
     * Identifiers assigned to this research study by the sponsor or other systems.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short, descriptive user-friendly label for the study.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getTitle(): null|FHIRString
    {
        return $this->title ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short, descriptive user-friendly label for the study.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $title
     * @return static
     */
    public function setTitle(null|string|FHIRStringPrimitive|FHIRString $title): self
    {
        if (null === $title) {
            unset($this->title);
            return $this;
        }
        if (!($title instanceof FHIRString)) {
            $title = new FHIRString(value: $title);
        }
        $this->title = $title;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of steps expected to be performed as part of the execution of the study.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getProtocol(): array
    {
        return $this->protocol ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getProtocolIterator(): iterable
    {
        if (!isset($this->protocol)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->protocol);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of steps expected to be performed as part of the execution of the study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $protocol
     * @return static
     */
    public function addProtocol(FHIRReference $protocol): self
    {
        if (!isset($this->protocol)) {
            $this->protocol = [];
        }
        $this->protocol[] = $protocol;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The set of steps expected to be performed as part of the execution of the study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$protocol
     * @return static
     */
    public function setProtocol(FHIRReference ...$protocol): self
    {
        if ([] === $protocol) {
            unset($this->protocol);
            return $this;
        }
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A larger research study of which this particular study is a component or step.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPartOf(): array
    {
        return $this->partOf ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPartOfIterator(): iterable
    {
        if (!isset($this->partOf)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->partOf);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A larger research study of which this particular study is a component or step.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $partOf
     * @return static
     */
    public function addPartOf(FHIRReference $partOf): self
    {
        if (!isset($this->partOf)) {
            $this->partOf = [];
        }
        $this->partOf[] = $partOf;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A larger research study of which this particular study is a component or step.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$partOf
     * @return static
     */
    public function setPartOf(FHIRReference ...$partOf): self
    {
        if ([] === $partOf) {
            unset($this->partOf);
            return $this;
        }
        $this->partOf = $partOf;
        return $this;
    }

    /**
     * Codes that convey the current status of the research study.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of the study.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResearchStudyStatus
     */
    public function getStatus(): null|FHIRResearchStudyStatus
    {
        return $this->status ?? null;
    }

    /**
     * Codes that convey the current status of the research study.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of the study.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRResearchStudyStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRResearchStudyStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRResearchStudyStatusList|FHIRResearchStudyStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRResearchStudyStatus)) {
            $status = new FHIRResearchStudyStatus(value: $status);
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
     * The type of study based upon the intent of the study's activities. A
     * classification of the intent of the study.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getPrimaryPurposeType(): null|FHIRCodeableConcept
    {
        return $this->primaryPurposeType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of study based upon the intent of the study's activities. A
     * classification of the intent of the study.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $primaryPurposeType
     * @return static
     */
    public function setPrimaryPurposeType(null|FHIRCodeableConcept $primaryPurposeType): self
    {
        if (null === $primaryPurposeType) {
            unset($this->primaryPurposeType);
            return $this;
        }
        $this->primaryPurposeType = $primaryPurposeType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The stage in the progression of a therapy from initial experimental use in
     * humans in clinical trials to post-market evaluation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getPhase(): null|FHIRCodeableConcept
    {
        return $this->phase ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The stage in the progression of a therapy from initial experimental use in
     * humans in clinical trials to post-market evaluation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $phase
     * @return static
     */
    public function setPhase(null|FHIRCodeableConcept $phase): self
    {
        if (null === $phase) {
            unset($this->phase);
            return $this;
        }
        $this->phase = $phase;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes categorizing the type of study such as investigational vs. observational,
     * type of blinding, type of randomization, safety vs. efficacy, etc.
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
     * Codes categorizing the type of study such as investigational vs. observational,
     * type of blinding, type of randomization, safety vs. efficacy, etc.
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
     * Codes categorizing the type of study such as investigational vs. observational,
     * type of blinding, type of randomization, safety vs. efficacy, etc.
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
     * The medication(s), food(s), therapy(ies), device(s) or other concerns or
     * interventions that the study is seeking to gain more information about.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getFocus(): array
    {
        return $this->focus ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getFocusIterator(): iterable
    {
        if (!isset($this->focus)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->focus);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The medication(s), food(s), therapy(ies), device(s) or other concerns or
     * interventions that the study is seeking to gain more information about.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $focus
     * @return static
     */
    public function addFocus(FHIRCodeableConcept $focus): self
    {
        if (!isset($this->focus)) {
            $this->focus = [];
        }
        $this->focus[] = $focus;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The medication(s), food(s), therapy(ies), device(s) or other concerns or
     * interventions that the study is seeking to gain more information about.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$focus
     * @return static
     */
    public function setFocus(FHIRCodeableConcept ...$focus): self
    {
        if ([] === $focus) {
            unset($this->focus);
            return $this;
        }
        $this->focus = $focus;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The condition that is the focus of the study. For example, In a study to examine
     * risk factors for Lupus, might have as an inclusion criterion "healthy
     * volunteer", but the target condition code would be a Lupus SNOMED code.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCondition(): array
    {
        return $this->condition ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getConditionIterator(): iterable
    {
        if (!isset($this->condition)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->condition);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The condition that is the focus of the study. For example, In a study to examine
     * risk factors for Lupus, might have as an inclusion criterion "healthy
     * volunteer", but the target condition code would be a Lupus SNOMED code.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $condition
     * @return static
     */
    public function addCondition(FHIRCodeableConcept $condition): self
    {
        if (!isset($this->condition)) {
            $this->condition = [];
        }
        $this->condition[] = $condition;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The condition that is the focus of the study. For example, In a study to examine
     * risk factors for Lupus, might have as an inclusion criterion "healthy
     * volunteer", but the target condition code would be a Lupus SNOMED code.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$condition
     * @return static
     */
    public function setCondition(FHIRCodeableConcept ...$condition): self
    {
        if ([] === $condition) {
            unset($this->condition);
            return $this;
        }
        $this->condition = $condition;
        return $this;
    }

    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details to assist a user in learning more about or engaging with the
     * study.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail>
     */
    public function getContact(): array
    {
        return $this->contact ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail>
     */
    public function getContactIterator(): iterable
    {
        if (!isset($this->contact)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->contact);
    }

    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details to assist a user in learning more about or engaging with the
     * study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail $contact
     * @return static
     */
    public function addContact(FHIRContactDetail $contact): self
    {
        if (!isset($this->contact)) {
            $this->contact = [];
        }
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details to assist a user in learning more about or engaging with the
     * study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail ...$contact
     * @return static
     */
    public function setContact(FHIRContactDetail ...$contact): self
    {
        if ([] === $contact) {
            unset($this->contact);
            return $this;
        }
        $this->contact = $contact;
        return $this;
    }

    /**
     * Related artifacts such as additional documentation, justification, or
     * bibliographic references.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Citations, references and other related documents.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact>
     */
    public function getRelatedArtifact(): array
    {
        return $this->relatedArtifact ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact>
     */
    public function getRelatedArtifactIterator(): iterable
    {
        if (!isset($this->relatedArtifact)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->relatedArtifact);
    }

    /**
     * Related artifacts such as additional documentation, justification, or
     * bibliographic references.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Citations, references and other related documents.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact $relatedArtifact
     * @return static
     */
    public function addRelatedArtifact(FHIRRelatedArtifact $relatedArtifact): self
    {
        if (!isset($this->relatedArtifact)) {
            $this->relatedArtifact = [];
        }
        $this->relatedArtifact[] = $relatedArtifact;
        return $this;
    }

    /**
     * Related artifacts such as additional documentation, justification, or
     * bibliographic references.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Citations, references and other related documents.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact ...$relatedArtifact
     * @return static
     */
    public function setRelatedArtifact(FHIRRelatedArtifact ...$relatedArtifact): self
    {
        if ([] === $relatedArtifact) {
            unset($this->relatedArtifact);
            return $this;
        }
        $this->relatedArtifact = $relatedArtifact;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Key terms to aid in searching for or filtering the study.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getKeyword(): array
    {
        return $this->keyword ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getKeywordIterator(): iterable
    {
        if (!isset($this->keyword)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->keyword);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Key terms to aid in searching for or filtering the study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $keyword
     * @return static
     */
    public function addKeyword(FHIRCodeableConcept $keyword): self
    {
        if (!isset($this->keyword)) {
            $this->keyword = [];
        }
        $this->keyword[] = $keyword;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Key terms to aid in searching for or filtering the study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$keyword
     * @return static
     */
    public function setKeyword(FHIRCodeableConcept ...$keyword): self
    {
        if ([] === $keyword) {
            unset($this->keyword);
            return $this;
        }
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a country, state or other region where the study is taking place.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getLocation(): array
    {
        return $this->location ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getLocationIterator(): iterable
    {
        if (!isset($this->location)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->location);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a country, state or other region where the study is taking place.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $location
     * @return static
     */
    public function addLocation(FHIRCodeableConcept $location): self
    {
        if (!isset($this->location)) {
            $this->location = [];
        }
        $this->location[] = $location;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a country, state or other region where the study is taking place.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$location
     * @return static
     */
    public function setLocation(FHIRCodeableConcept ...$location): self
    {
        if ([] === $location) {
            unset($this->location);
            return $this;
        }
        $this->location = $location;
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
     * A full description of how the study is being conducted.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getDescription(): null|FHIRMarkdown
    {
        return $this->description ?? null;
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
     * A full description of how the study is being conducted.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $description
     * @return static
     */
    public function setDescription(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRMarkdown)) {
            $description = new FHIRMarkdown(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to a Group that defines the criteria for and quantity of subjects
     * participating in the study. E.g. " 200 female Europeans between the ages of 20
     * and 45 with early onset diabetes".
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEnrollment(): array
    {
        return $this->enrollment ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEnrollmentIterator(): iterable
    {
        if (!isset($this->enrollment)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->enrollment);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to a Group that defines the criteria for and quantity of subjects
     * participating in the study. E.g. " 200 female Europeans between the ages of 20
     * and 45 with early onset diabetes".
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $enrollment
     * @return static
     */
    public function addEnrollment(FHIRReference $enrollment): self
    {
        if (!isset($this->enrollment)) {
            $this->enrollment = [];
        }
        $this->enrollment[] = $enrollment;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to a Group that defines the criteria for and quantity of subjects
     * participating in the study. E.g. " 200 female Europeans between the ages of 20
     * and 45 with early onset diabetes".
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$enrollment
     * @return static
     */
    public function setEnrollment(FHIRReference ...$enrollment): self
    {
        if ([] === $enrollment) {
            unset($this->enrollment);
            return $this;
        }
        $this->enrollment = $enrollment;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the start date and the expected (or actual, depending on status) end
     * date for the study.
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
     * Identifies the start date and the expected (or actual, depending on status) end
     * date for the study.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An organization that initiates the investigation and is legally responsible for
     * the study.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSponsor(): null|FHIRReference
    {
        return $this->sponsor ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An organization that initiates the investigation and is legally responsible for
     * the study.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $sponsor
     * @return static
     */
    public function setSponsor(null|FHIRReference $sponsor): self
    {
        if (null === $sponsor) {
            unset($this->sponsor);
            return $this;
        }
        $this->sponsor = $sponsor;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A researcher in a study who oversees multiple aspects of the study, such as
     * concept development, protocol writing, protocol submission for IRB approval,
     * participant recruitment, informed consent, data collection, analysis,
     * interpretation and presentation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPrincipalInvestigator(): null|FHIRReference
    {
        return $this->principalInvestigator ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A researcher in a study who oversees multiple aspects of the study, such as
     * concept development, protocol writing, protocol submission for IRB approval,
     * participant recruitment, informed consent, data collection, analysis,
     * interpretation and presentation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $principalInvestigator
     * @return static
     */
    public function setPrincipalInvestigator(null|FHIRReference $principalInvestigator): self
    {
        if (null === $principalInvestigator) {
            unset($this->principalInvestigator);
            return $this;
        }
        $this->principalInvestigator = $principalInvestigator;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A facility in which study activities are conducted.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSite(): array
    {
        return $this->site ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSiteIterator(): iterable
    {
        if (!isset($this->site)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->site);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A facility in which study activities are conducted.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $site
     * @return static
     */
    public function addSite(FHIRReference $site): self
    {
        if (!isset($this->site)) {
            $this->site = [];
        }
        $this->site[] = $site;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A facility in which study activities are conducted.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$site
     * @return static
     */
    public function setSite(FHIRReference ...$site): self
    {
        if ([] === $site) {
            unset($this->site);
            return $this;
        }
        $this->site = $site;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A description and/or code explaining the premature termination of the study.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getReasonStopped(): null|FHIRCodeableConcept
    {
        return $this->reasonStopped ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A description and/or code explaining the premature termination of the study.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reasonStopped
     * @return static
     */
    public function setReasonStopped(null|FHIRCodeableConcept $reasonStopped): self
    {
        if (null === $reasonStopped) {
            unset($this->reasonStopped);
            return $this;
        }
        $this->reasonStopped = $reasonStopped;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Comments made about the study by the performer, subject or other participants.
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
     * Comments made about the study by the performer, subject or other participants.
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
     * Comments made about the study by the performer, subject or other participants.
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
     * A process where a researcher or organization plans and then executes a series of
     * steps intended to increase the field of healthcare-related knowledge. This
     * includes studies of safety, efficacy, comparative effectiveness and other
     * information about medications, devices, therapies and other interventional and
     * investigative techniques. A ResearchStudy involves the gathering of information
     * about human or animal subjects.
     *
     * Describes an expected sequence of events for one of the participants of a study.
     * E.g. Exposure to drug A, wash-out, exposure to drug B, wash-out, follow-up.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyArm>
     */
    public function getArm(): array
    {
        return $this->arm ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyArm>
     */
    public function getArmIterator(): iterable
    {
        if (!isset($this->arm)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->arm);
    }

    /**
     * A process where a researcher or organization plans and then executes a series of
     * steps intended to increase the field of healthcare-related knowledge. This
     * includes studies of safety, efficacy, comparative effectiveness and other
     * information about medications, devices, therapies and other interventional and
     * investigative techniques. A ResearchStudy involves the gathering of information
     * about human or animal subjects.
     *
     * Describes an expected sequence of events for one of the participants of a study.
     * E.g. Exposure to drug A, wash-out, exposure to drug B, wash-out, follow-up.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyArm $arm
     * @return static
     */
    public function addArm(FHIRResearchStudyArm $arm): self
    {
        if (!isset($this->arm)) {
            $this->arm = [];
        }
        $this->arm[] = $arm;
        return $this;
    }

    /**
     * A process where a researcher or organization plans and then executes a series of
     * steps intended to increase the field of healthcare-related knowledge. This
     * includes studies of safety, efficacy, comparative effectiveness and other
     * information about medications, devices, therapies and other interventional and
     * investigative techniques. A ResearchStudy involves the gathering of information
     * about human or animal subjects.
     *
     * Describes an expected sequence of events for one of the participants of a study.
     * E.g. Exposure to drug A, wash-out, exposure to drug B, wash-out, follow-up.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyArm ...$arm
     * @return static
     */
    public function setArm(FHIRResearchStudyArm ...$arm): self
    {
        if ([] === $arm) {
            unset($this->arm);
            return $this;
        }
        $this->arm = $arm;
        return $this;
    }

    /**
     * A process where a researcher or organization plans and then executes a series of
     * steps intended to increase the field of healthcare-related knowledge. This
     * includes studies of safety, efficacy, comparative effectiveness and other
     * information about medications, devices, therapies and other interventional and
     * investigative techniques. A ResearchStudy involves the gathering of information
     * about human or animal subjects.
     *
     * A goal that the study is aiming to achieve in terms of a scientific question to
     * be answered by the analysis of data collected during the study.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyObjective>
     */
    public function getObjective(): array
    {
        return $this->objective ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyObjective>
     */
    public function getObjectiveIterator(): iterable
    {
        if (!isset($this->objective)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->objective);
    }

    /**
     * A process where a researcher or organization plans and then executes a series of
     * steps intended to increase the field of healthcare-related knowledge. This
     * includes studies of safety, efficacy, comparative effectiveness and other
     * information about medications, devices, therapies and other interventional and
     * investigative techniques. A ResearchStudy involves the gathering of information
     * about human or animal subjects.
     *
     * A goal that the study is aiming to achieve in terms of a scientific question to
     * be answered by the analysis of data collected during the study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyObjective $objective
     * @return static
     */
    public function addObjective(FHIRResearchStudyObjective $objective): self
    {
        if (!isset($this->objective)) {
            $this->objective = [];
        }
        $this->objective[] = $objective;
        return $this;
    }

    /**
     * A process where a researcher or organization plans and then executes a series of
     * steps intended to increase the field of healthcare-related knowledge. This
     * includes studies of safety, efficacy, comparative effectiveness and other
     * information about medications, devices, therapies and other interventional and
     * investigative techniques. A ResearchStudy involves the gathering of information
     * about human or animal subjects.
     *
     * A goal that the study is aiming to achieve in terms of a scientific question to
     * be answered by the analysis of data collected during the study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchStudy\FHIRResearchStudyObjective ...$objective
     * @return static
     */
    public function setObjective(FHIRResearchStudyObjective ...$objective): self
    {
        if ([] === $objective) {
            unset($this->objective);
            return $this;
        }
        $this->objective = $objective;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchStudy $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchStudy
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRResearchStudy)) {
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
            } else if (self::FIELD_TITLE === $cen) {
                $type->setTitle(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROTOCOL === $cen) {
                $type->addProtocol(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PART_OF === $cen) {
                $type->addPartOf(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRResearchStudyStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIMARY_PURPOSE_TYPE === $cen) {
                $type->setPrimaryPurposeType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PHASE === $cen) {
                $type->setPhase(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->addCategory(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FOCUS === $cen) {
                $type->addFocus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONDITION === $cen) {
                $type->addCondition(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTACT === $cen) {
                $type->addContact(FHIRContactDetail::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATED_ARTIFACT === $cen) {
                $type->addRelatedArtifact(FHIRRelatedArtifact::xmlUnserialize($ce, $config));
            } else if (self::FIELD_KEYWORD === $cen) {
                $type->addKeyword(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOCATION === $cen) {
                $type->addLocation(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENROLLMENT === $cen) {
                $type->addEnrollment(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD === $cen) {
                $type->setPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SPONSOR === $cen) {
                $type->setSponsor(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRINCIPAL_INVESTIGATOR === $cen) {
                $type->setPrincipalInvestigator(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SITE === $cen) {
                $type->addSite(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_STOPPED === $cen) {
                $type->setReasonStopped(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ARM === $cen) {
                $type->addArm(FHIRResearchStudyArm::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OBJECTIVE === $cen) {
                $type->addObjective(FHIRResearchStudyObjective::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_TITLE])) {
            if (isset($type->title)) {
                $type->title->setValue((string)$attributes[self::FIELD_TITLE]);
            } else {
                $type->setTitle((string)$attributes[self::FIELD_TITLE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TITLE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('ResearchStudy', $this->_getSourceXMLNS());
        }
        if (isset($this->title) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TITLE]) {
            $xw->writeAttribute(self::FIELD_TITLE, $this->title->_getValueAsString());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->title)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TITLE]
                || $this->title->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TITLE);
            $this->title->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TITLE]);
            $xw->endElement();
        }
        if (isset($this->protocol)) {
            foreach ($this->protocol as $v) {
                $xw->startElement(self::FIELD_PROTOCOL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->partOf)) {
            foreach ($this->partOf as $v) {
                $xw->startElement(self::FIELD_PART_OF);
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
        if (isset($this->primaryPurposeType)) {
            $xw->startElement(self::FIELD_PRIMARY_PURPOSE_TYPE);
            $this->primaryPurposeType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->phase)) {
            $xw->startElement(self::FIELD_PHASE);
            $this->phase->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->category)) {
            foreach ($this->category as $v) {
                $xw->startElement(self::FIELD_CATEGORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->focus)) {
            foreach ($this->focus as $v) {
                $xw->startElement(self::FIELD_FOCUS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->condition)) {
            foreach ($this->condition as $v) {
                $xw->startElement(self::FIELD_CONDITION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->contact)) {
            foreach ($this->contact as $v) {
                $xw->startElement(self::FIELD_CONTACT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->relatedArtifact)) {
            foreach ($this->relatedArtifact as $v) {
                $xw->startElement(self::FIELD_RELATED_ARTIFACT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->keyword)) {
            foreach ($this->keyword as $v) {
                $xw->startElement(self::FIELD_KEYWORD);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->location)) {
            foreach ($this->location as $v) {
                $xw->startElement(self::FIELD_LOCATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->enrollment)) {
            foreach ($this->enrollment as $v) {
                $xw->startElement(self::FIELD_ENROLLMENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->period)) {
            $xw->startElement(self::FIELD_PERIOD);
            $this->period->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->sponsor)) {
            $xw->startElement(self::FIELD_SPONSOR);
            $this->sponsor->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->principalInvestigator)) {
            $xw->startElement(self::FIELD_PRINCIPAL_INVESTIGATOR);
            $this->principalInvestigator->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->site)) {
            foreach ($this->site as $v) {
                $xw->startElement(self::FIELD_SITE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->reasonStopped)) {
            $xw->startElement(self::FIELD_REASON_STOPPED);
            $this->reasonStopped->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->note)) {
            foreach ($this->note as $v) {
                $xw->startElement(self::FIELD_NOTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->arm)) {
            foreach ($this->arm as $v) {
                $xw->startElement(self::FIELD_ARM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->objective)) {
            foreach ($this->objective as $v) {
                $xw->startElement(self::FIELD_OBJECTIVE);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchStudy $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRResearchStudy
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
        } else if (!($type instanceof FHIRResearchStudy)) {
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
        if (isset($decoded->title)
            || isset($decoded->_title)
            || property_exists($decoded, self::FIELD_TITLE)
            || property_exists($decoded, self::FIELD_TITLE_EXT)) {
            $v = $decoded->_title ?? new \stdClass();
            $v->value = $decoded->title ?? null;
            $type->setTitle(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->protocol) || property_exists($decoded, self::FIELD_PROTOCOL)) {
            if (is_object($decoded->protocol)) {
                $vals = [$decoded->protocol];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROTOCOL, true);
            } else {
                $vals = $decoded->protocol;
            }
            foreach($vals as $v) {
                $type->addProtocol(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->partOf) || property_exists($decoded, self::FIELD_PART_OF)) {
            if (is_object($decoded->partOf)) {
                $vals = [$decoded->partOf];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PART_OF, true);
            } else {
                $vals = $decoded->partOf;
            }
            foreach($vals as $v) {
                $type->addPartOf(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRResearchStudyStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->primaryPurposeType) || property_exists($decoded, self::FIELD_PRIMARY_PURPOSE_TYPE)) {
            if (is_array($decoded->primaryPurposeType)) {
                $type->setPrimaryPurposeType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->primaryPurposeType), $config));
            } else {
                $type->setPrimaryPurposeType(FHIRCodeableConcept::jsonUnserialize($decoded->primaryPurposeType, $config));
            }
        }
        if (isset($decoded->phase) || property_exists($decoded, self::FIELD_PHASE)) {
            if (is_array($decoded->phase)) {
                $type->setPhase(FHIRCodeableConcept::jsonUnserialize(reset($decoded->phase), $config));
            } else {
                $type->setPhase(FHIRCodeableConcept::jsonUnserialize($decoded->phase, $config));
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
        if (isset($decoded->focus) || property_exists($decoded, self::FIELD_FOCUS)) {
            if (is_object($decoded->focus)) {
                $vals = [$decoded->focus];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_FOCUS, true);
            } else {
                $vals = $decoded->focus;
            }
            foreach($vals as $v) {
                $type->addFocus(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->condition) || property_exists($decoded, self::FIELD_CONDITION)) {
            if (is_object($decoded->condition)) {
                $vals = [$decoded->condition];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONDITION, true);
            } else {
                $vals = $decoded->condition;
            }
            foreach($vals as $v) {
                $type->addCondition(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->contact) || property_exists($decoded, self::FIELD_CONTACT)) {
            if (is_object($decoded->contact)) {
                $vals = [$decoded->contact];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTACT, true);
            } else {
                $vals = $decoded->contact;
            }
            foreach($vals as $v) {
                $type->addContact(FHIRContactDetail::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->relatedArtifact) || property_exists($decoded, self::FIELD_RELATED_ARTIFACT)) {
            if (is_object($decoded->relatedArtifact)) {
                $vals = [$decoded->relatedArtifact];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RELATED_ARTIFACT, true);
            } else {
                $vals = $decoded->relatedArtifact;
            }
            foreach($vals as $v) {
                $type->addRelatedArtifact(FHIRRelatedArtifact::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->keyword) || property_exists($decoded, self::FIELD_KEYWORD)) {
            if (is_object($decoded->keyword)) {
                $vals = [$decoded->keyword];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_KEYWORD, true);
            } else {
                $vals = $decoded->keyword;
            }
            foreach($vals as $v) {
                $type->addKeyword(FHIRCodeableConcept::jsonUnserialize($v, $config));
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
                $type->addLocation(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->enrollment) || property_exists($decoded, self::FIELD_ENROLLMENT)) {
            if (is_object($decoded->enrollment)) {
                $vals = [$decoded->enrollment];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ENROLLMENT, true);
            } else {
                $vals = $decoded->enrollment;
            }
            foreach($vals as $v) {
                $type->addEnrollment(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->period) || property_exists($decoded, self::FIELD_PERIOD)) {
            if (is_array($decoded->period)) {
                $type->setPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->period), $config));
            } else {
                $type->setPeriod(FHIRPeriod::jsonUnserialize($decoded->period, $config));
            }
        }
        if (isset($decoded->sponsor) || property_exists($decoded, self::FIELD_SPONSOR)) {
            if (is_array($decoded->sponsor)) {
                $type->setSponsor(FHIRReference::jsonUnserialize(reset($decoded->sponsor), $config));
            } else {
                $type->setSponsor(FHIRReference::jsonUnserialize($decoded->sponsor, $config));
            }
        }
        if (isset($decoded->principalInvestigator) || property_exists($decoded, self::FIELD_PRINCIPAL_INVESTIGATOR)) {
            if (is_array($decoded->principalInvestigator)) {
                $type->setPrincipalInvestigator(FHIRReference::jsonUnserialize(reset($decoded->principalInvestigator), $config));
            } else {
                $type->setPrincipalInvestigator(FHIRReference::jsonUnserialize($decoded->principalInvestigator, $config));
            }
        }
        if (isset($decoded->site) || property_exists($decoded, self::FIELD_SITE)) {
            if (is_object($decoded->site)) {
                $vals = [$decoded->site];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SITE, true);
            } else {
                $vals = $decoded->site;
            }
            foreach($vals as $v) {
                $type->addSite(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->reasonStopped) || property_exists($decoded, self::FIELD_REASON_STOPPED)) {
            if (is_array($decoded->reasonStopped)) {
                $type->setReasonStopped(FHIRCodeableConcept::jsonUnserialize(reset($decoded->reasonStopped), $config));
            } else {
                $type->setReasonStopped(FHIRCodeableConcept::jsonUnserialize($decoded->reasonStopped, $config));
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
        if (isset($decoded->arm) || property_exists($decoded, self::FIELD_ARM)) {
            if (is_object($decoded->arm)) {
                $vals = [$decoded->arm];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ARM, true);
            } else {
                $vals = $decoded->arm;
            }
            foreach($vals as $v) {
                $type->addArm(FHIRResearchStudyArm::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->objective) || property_exists($decoded, self::FIELD_OBJECTIVE)) {
            if (is_object($decoded->objective)) {
                $vals = [$decoded->objective];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_OBJECTIVE, true);
            } else {
                $vals = $decoded->objective;
            }
            foreach($vals as $v) {
                $type->addObjective(FHIRResearchStudyObjective::jsonUnserialize($v, $config));
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
        if (isset($this->title)) {
            if (null !== ($val = $this->title->getValue())) {
                $out->title = $val;
            }
            if ($this->title->_nonValueFieldDefined()) {
                $ext = $this->title->jsonSerialize();
                unset($ext->value);
                $out->_title = $ext;
            }
        }
        if (isset($this->protocol) && [] !== $this->protocol) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROTOCOL) && 1 === count($this->protocol)) {
                $out->protocol = $this->protocol[0];
            } else {
                $out->protocol = $this->protocol;
            }
        }
        if (isset($this->partOf) && [] !== $this->partOf) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PART_OF) && 1 === count($this->partOf)) {
                $out->partOf = $this->partOf[0];
            } else {
                $out->partOf = $this->partOf;
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
        if (isset($this->primaryPurposeType)) {
            $out->primaryPurposeType = $this->primaryPurposeType;
        }
        if (isset($this->phase)) {
            $out->phase = $this->phase;
        }
        if (isset($this->category) && [] !== $this->category) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CATEGORY) && 1 === count($this->category)) {
                $out->category = $this->category[0];
            } else {
                $out->category = $this->category;
            }
        }
        if (isset($this->focus) && [] !== $this->focus) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_FOCUS) && 1 === count($this->focus)) {
                $out->focus = $this->focus[0];
            } else {
                $out->focus = $this->focus;
            }
        }
        if (isset($this->condition) && [] !== $this->condition) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONDITION) && 1 === count($this->condition)) {
                $out->condition = $this->condition[0];
            } else {
                $out->condition = $this->condition;
            }
        }
        if (isset($this->contact) && [] !== $this->contact) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTACT) && 1 === count($this->contact)) {
                $out->contact = $this->contact[0];
            } else {
                $out->contact = $this->contact;
            }
        }
        if (isset($this->relatedArtifact) && [] !== $this->relatedArtifact) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELATED_ARTIFACT) && 1 === count($this->relatedArtifact)) {
                $out->relatedArtifact = $this->relatedArtifact[0];
            } else {
                $out->relatedArtifact = $this->relatedArtifact;
            }
        }
        if (isset($this->keyword) && [] !== $this->keyword) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_KEYWORD) && 1 === count($this->keyword)) {
                $out->keyword = $this->keyword[0];
            } else {
                $out->keyword = $this->keyword;
            }
        }
        if (isset($this->location) && [] !== $this->location) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_LOCATION) && 1 === count($this->location)) {
                $out->location = $this->location[0];
            } else {
                $out->location = $this->location;
            }
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
        if (isset($this->enrollment) && [] !== $this->enrollment) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ENROLLMENT) && 1 === count($this->enrollment)) {
                $out->enrollment = $this->enrollment[0];
            } else {
                $out->enrollment = $this->enrollment;
            }
        }
        if (isset($this->period)) {
            $out->period = $this->period;
        }
        if (isset($this->sponsor)) {
            $out->sponsor = $this->sponsor;
        }
        if (isset($this->principalInvestigator)) {
            $out->principalInvestigator = $this->principalInvestigator;
        }
        if (isset($this->site) && [] !== $this->site) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SITE) && 1 === count($this->site)) {
                $out->site = $this->site[0];
            } else {
                $out->site = $this->site;
            }
        }
        if (isset($this->reasonStopped)) {
            $out->reasonStopped = $this->reasonStopped;
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        if (isset($this->arm) && [] !== $this->arm) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ARM) && 1 === count($this->arm)) {
                $out->arm = $this->arm[0];
            } else {
                $out->arm = $this->arm;
            }
        }
        if (isset($this->objective) && [] !== $this->objective) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_OBJECTIVE) && 1 === count($this->objective)) {
                $out->objective = $this->objective[0];
            } else {
                $out->objective = $this->objective;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
