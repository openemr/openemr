<?php

namespace OpenEMR\FHIR\R4\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: June 14th, 2019
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *   Generated on Thu, Dec 27, 2018 22:37+1100 for FHIR v4.0.0
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/**
 * The Measure resource provides the definition of a quality measure.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMeasure extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * An absolute URI that is used to identify this measure when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this measure is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the measure is stored on different servers.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * A formal identifier that is used to identify this measure when it is represented in other formats, or referenced in a specification, model, design or an instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The identifier that is used to identify this version of the measure when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the measure author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence. To provide a version consistent with the Decision Support Service specification, use the format Major.Minor.Revision (e.g. 1.0.0). For more information on versioning knowledge assets, refer to the Decision Support Service specification. Note that a version is required for non-experimental active artifacts.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * A natural language name identifying the measure. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A short, descriptive, user-friendly title for the measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * An explanatory or alternate title for the measure giving additional information about its content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $subtitle = null;

    /**
     * The status of this measure. Enables tracking the life-cycle of the content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public $status = null;

    /**
     * A Boolean value to indicate that this measure is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $experimental = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $subjectCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subjectReference = null;

    /**
     * The date  (and optionally time) when the measure was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the measure changes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * The name of the organization or individual that published the measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $publisher = null;

    /**
     * Contact details to assist a user in finding and communicating with the publisher.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public $contact = [];

    /**
     * A free text natural language description of the measure from a consumer's perspective.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $description = null;

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate measure instances.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    public $useContext = [];

    /**
     * A legal or geographic region in which the measure is intended to be used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $jurisdiction = [];

    /**
     * Explanation of why this measure is needed and why it has been designed as it has.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $purpose = null;

    /**
     * A detailed description, from a clinical perspective, of how the measure is used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $usage = null;

    /**
     * A copyright statement relating to the measure and/or its contents. Copyright statements are generally legal restrictions on the use and publishing of the measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $copyright = null;

    /**
     * The date on which the resource content was approved by the publisher. Approval happens once when the content is officially approved for usage.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $approvalDate = null;

    /**
     * The date on which the resource content was last reviewed. Review happens periodically after approval but does not change the original approval date.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $lastReviewDate = null;

    /**
     * The period during which the measure content was or is planned to be in active use.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $effectivePeriod = null;

    /**
     * Descriptive topics related to the content of the measure. Topics provide a high-level categorization grouping types of measures that can be useful for filtering and searching.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $topic = [];

    /**
     * An individiual or organization primarily involved in the creation and maintenance of the content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public $author = [];

    /**
     * An individual or organization primarily responsible for internal coherence of the content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public $editor = [];

    /**
     * An individual or organization primarily responsible for review of some aspect of the content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public $reviewer = [];

    /**
     * An individual or organization responsible for officially endorsing the content for use in some setting.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public $endorser = [];

    /**
     * Related artifacts such as additional documentation, justification, or bibliographic references.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact[]
     */
    public $relatedArtifact = [];

    /**
     * A reference to a Library resource containing the formal logic used by the measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $library = [];

    /**
     * Notices and disclaimers regarding the use of the measure or related to intellectual property (such as code systems) referenced by the measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $disclaimer = null;

    /**
     * Indicates how the calculation is performed for the measure, including proportion, ratio, continuous-variable, and cohort. The value set is extensible, allowing additional measure scoring types to be represented.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $scoring = null;

    /**
     * If this is a composite measure, the scoring method used to combine the component measures to determine the composite score.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $compositeScoring = null;

    /**
     * Indicates whether the measure is used to examine a process, an outcome over time, a patient-reported outcome, or a structure measure such as utilization.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $type = [];

    /**
     * A description of the risk adjustment factors that may impact the resulting score for the measure and how they may be accounted for when computing and reporting measure results.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $riskAdjustment = null;

    /**
     * Describes how to combine the information calculated, based on logic in each of several populations, into one summarized result.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $rateAggregation = null;

    /**
     * Provides a succinct statement of the need for the measure. Usually includes statements pertaining to importance criterion: impact, gap in care, and evidence.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $rationale = null;

    /**
     * Provides a summary of relevant clinical guidelines or other clinical recommendations supporting the measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $clinicalRecommendationStatement = null;

    /**
     * Information on whether an increase or decrease in score is the preferred result (e.g., a higher score indicates better quality OR a lower score indicates better quality OR quality is within a range).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $improvementNotation = null;

    /**
     * Provides a description of an individual term used within the measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown[]
     */
    public $definition = [];

    /**
     * Additional guidance for the measure including how it can be used in a clinical context, and the intent of the measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $guidance = null;

    /**
     * A group of population criteria for the measure.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasure\FHIRMeasureGroup[]
     */
    public $group = [];

    /**
     * The supplemental data criteria for the measure report, specified as either the name of a valid CQL expression within a referenced library, or a valid FHIR Resource Path.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasure\FHIRMeasureSupplementalData[]
     */
    public $supplementalData = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Measure';

    /**
     * An absolute URI that is used to identify this measure when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this measure is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the measure is stored on different servers.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * An absolute URI that is used to identify this measure when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this measure is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the measure is stored on different servers.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * A formal identifier that is used to identify this measure when it is represented in other formats, or referenced in a specification, model, design or an instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A formal identifier that is used to identify this measure when it is represented in other formats, or referenced in a specification, model, design or an instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The identifier that is used to identify this version of the measure when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the measure author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence. To provide a version consistent with the Decision Support Service specification, use the format Major.Minor.Revision (e.g. 1.0.0). For more information on versioning knowledge assets, refer to the Decision Support Service specification. Note that a version is required for non-experimental active artifacts.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The identifier that is used to identify this version of the measure when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the measure author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence. To provide a version consistent with the Decision Support Service specification, use the format Major.Minor.Revision (e.g. 1.0.0). For more information on versioning knowledge assets, refer to the Decision Support Service specification. Note that a version is required for non-experimental active artifacts.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * A natural language name identifying the measure. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A natural language name identifying the measure. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A short, descriptive, user-friendly title for the measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * A short, descriptive, user-friendly title for the measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * An explanatory or alternate title for the measure giving additional information about its content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * An explanatory or alternate title for the measure giving additional information about its content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $subtitle
     * @return $this
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
     * The status of this measure. Enables tracking the life-cycle of the content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of this measure. Enables tracking the life-cycle of the content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A Boolean value to indicate that this measure is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getExperimental()
    {
        return $this->experimental;
    }

    /**
     * A Boolean value to indicate that this measure is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $experimental
     * @return $this
     */
    public function setExperimental($experimental)
    {
        $this->experimental = $experimental;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSubjectCodeableConcept()
    {
        return $this->subjectCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $subjectCodeableConcept
     * @return $this
     */
    public function setSubjectCodeableConcept($subjectCodeableConcept)
    {
        $this->subjectCodeableConcept = $subjectCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubjectReference()
    {
        return $this->subjectReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subjectReference
     * @return $this
     */
    public function setSubjectReference($subjectReference)
    {
        $this->subjectReference = $subjectReference;
        return $this;
    }

    /**
     * The date  (and optionally time) when the measure was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the measure changes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date  (and optionally time) when the measure was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the measure changes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * The name of the organization or individual that published the measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * The name of the organization or individual that published the measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $publisher
     * @return $this
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
        return $this;
    }

    /**
     * Contact details to assist a user in finding and communicating with the publisher.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Contact details to assist a user in finding and communicating with the publisher.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail $contact
     * @return $this
     */
    public function addContact($contact)
    {
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * A free text natural language description of the measure from a consumer's perspective.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A free text natural language description of the measure from a consumer's perspective.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate measure instances.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    public function getUseContext()
    {
        return $this->useContext;
    }

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate measure instances.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext $useContext
     * @return $this
     */
    public function addUseContext($useContext)
    {
        $this->useContext[] = $useContext;
        return $this;
    }

    /**
     * A legal or geographic region in which the measure is intended to be used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * A legal or geographic region in which the measure is intended to be used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $jurisdiction
     * @return $this
     */
    public function addJurisdiction($jurisdiction)
    {
        $this->jurisdiction[] = $jurisdiction;
        return $this;
    }

    /**
     * Explanation of why this measure is needed and why it has been designed as it has.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Explanation of why this measure is needed and why it has been designed as it has.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $purpose
     * @return $this
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * A detailed description, from a clinical perspective, of how the measure is used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * A detailed description, from a clinical perspective, of how the measure is used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $usage
     * @return $this
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;
        return $this;
    }

    /**
     * A copyright statement relating to the measure and/or its contents. Copyright statements are generally legal restrictions on the use and publishing of the measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * A copyright statement relating to the measure and/or its contents. Copyright statements are generally legal restrictions on the use and publishing of the measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $copyright
     * @return $this
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
        return $this;
    }

    /**
     * The date on which the resource content was approved by the publisher. Approval happens once when the content is officially approved for usage.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getApprovalDate()
    {
        return $this->approvalDate;
    }

    /**
     * The date on which the resource content was approved by the publisher. Approval happens once when the content is officially approved for usage.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $approvalDate
     * @return $this
     */
    public function setApprovalDate($approvalDate)
    {
        $this->approvalDate = $approvalDate;
        return $this;
    }

    /**
     * The date on which the resource content was last reviewed. Review happens periodically after approval but does not change the original approval date.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getLastReviewDate()
    {
        return $this->lastReviewDate;
    }

    /**
     * The date on which the resource content was last reviewed. Review happens periodically after approval but does not change the original approval date.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $lastReviewDate
     * @return $this
     */
    public function setLastReviewDate($lastReviewDate)
    {
        $this->lastReviewDate = $lastReviewDate;
        return $this;
    }

    /**
     * The period during which the measure content was or is planned to be in active use.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getEffectivePeriod()
    {
        return $this->effectivePeriod;
    }

    /**
     * The period during which the measure content was or is planned to be in active use.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $effectivePeriod
     * @return $this
     */
    public function setEffectivePeriod($effectivePeriod)
    {
        $this->effectivePeriod = $effectivePeriod;
        return $this;
    }

    /**
     * Descriptive topics related to the content of the measure. Topics provide a high-level categorization grouping types of measures that can be useful for filtering and searching.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Descriptive topics related to the content of the measure. Topics provide a high-level categorization grouping types of measures that can be useful for filtering and searching.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $topic
     * @return $this
     */
    public function addTopic($topic)
    {
        $this->topic[] = $topic;
        return $this;
    }

    /**
     * An individiual or organization primarily involved in the creation and maintenance of the content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * An individiual or organization primarily involved in the creation and maintenance of the content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail $author
     * @return $this
     */
    public function addAuthor($author)
    {
        $this->author[] = $author;
        return $this;
    }

    /**
     * An individual or organization primarily responsible for internal coherence of the content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * An individual or organization primarily responsible for internal coherence of the content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail $editor
     * @return $this
     */
    public function addEditor($editor)
    {
        $this->editor[] = $editor;
        return $this;
    }

    /**
     * An individual or organization primarily responsible for review of some aspect of the content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public function getReviewer()
    {
        return $this->reviewer;
    }

    /**
     * An individual or organization primarily responsible for review of some aspect of the content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail $reviewer
     * @return $this
     */
    public function addReviewer($reviewer)
    {
        $this->reviewer[] = $reviewer;
        return $this;
    }

    /**
     * An individual or organization responsible for officially endorsing the content for use in some setting.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public function getEndorser()
    {
        return $this->endorser;
    }

    /**
     * An individual or organization responsible for officially endorsing the content for use in some setting.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail $endorser
     * @return $this
     */
    public function addEndorser($endorser)
    {
        $this->endorser[] = $endorser;
        return $this;
    }

    /**
     * Related artifacts such as additional documentation, justification, or bibliographic references.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact[]
     */
    public function getRelatedArtifact()
    {
        return $this->relatedArtifact;
    }

    /**
     * Related artifacts such as additional documentation, justification, or bibliographic references.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact $relatedArtifact
     * @return $this
     */
    public function addRelatedArtifact($relatedArtifact)
    {
        $this->relatedArtifact[] = $relatedArtifact;
        return $this;
    }

    /**
     * A reference to a Library resource containing the formal logic used by the measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * A reference to a Library resource containing the formal logic used by the measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $library
     * @return $this
     */
    public function addLibrary($library)
    {
        $this->library[] = $library;
        return $this;
    }

    /**
     * Notices and disclaimers regarding the use of the measure or related to intellectual property (such as code systems) referenced by the measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDisclaimer()
    {
        return $this->disclaimer;
    }

    /**
     * Notices and disclaimers regarding the use of the measure or related to intellectual property (such as code systems) referenced by the measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $disclaimer
     * @return $this
     */
    public function setDisclaimer($disclaimer)
    {
        $this->disclaimer = $disclaimer;
        return $this;
    }

    /**
     * Indicates how the calculation is performed for the measure, including proportion, ratio, continuous-variable, and cohort. The value set is extensible, allowing additional measure scoring types to be represented.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getScoring()
    {
        return $this->scoring;
    }

    /**
     * Indicates how the calculation is performed for the measure, including proportion, ratio, continuous-variable, and cohort. The value set is extensible, allowing additional measure scoring types to be represented.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $scoring
     * @return $this
     */
    public function setScoring($scoring)
    {
        $this->scoring = $scoring;
        return $this;
    }

    /**
     * If this is a composite measure, the scoring method used to combine the component measures to determine the composite score.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCompositeScoring()
    {
        return $this->compositeScoring;
    }

    /**
     * If this is a composite measure, the scoring method used to combine the component measures to determine the composite score.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $compositeScoring
     * @return $this
     */
    public function setCompositeScoring($compositeScoring)
    {
        $this->compositeScoring = $compositeScoring;
        return $this;
    }

    /**
     * Indicates whether the measure is used to examine a process, an outcome over time, a patient-reported outcome, or a structure measure such as utilization.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Indicates whether the measure is used to examine a process, an outcome over time, a patient-reported outcome, or a structure measure such as utilization.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * A description of the risk adjustment factors that may impact the resulting score for the measure and how they may be accounted for when computing and reporting measure results.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getRiskAdjustment()
    {
        return $this->riskAdjustment;
    }

    /**
     * A description of the risk adjustment factors that may impact the resulting score for the measure and how they may be accounted for when computing and reporting measure results.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $riskAdjustment
     * @return $this
     */
    public function setRiskAdjustment($riskAdjustment)
    {
        $this->riskAdjustment = $riskAdjustment;
        return $this;
    }

    /**
     * Describes how to combine the information calculated, based on logic in each of several populations, into one summarized result.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getRateAggregation()
    {
        return $this->rateAggregation;
    }

    /**
     * Describes how to combine the information calculated, based on logic in each of several populations, into one summarized result.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $rateAggregation
     * @return $this
     */
    public function setRateAggregation($rateAggregation)
    {
        $this->rateAggregation = $rateAggregation;
        return $this;
    }

    /**
     * Provides a succinct statement of the need for the measure. Usually includes statements pertaining to importance criterion: impact, gap in care, and evidence.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getRationale()
    {
        return $this->rationale;
    }

    /**
     * Provides a succinct statement of the need for the measure. Usually includes statements pertaining to importance criterion: impact, gap in care, and evidence.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $rationale
     * @return $this
     */
    public function setRationale($rationale)
    {
        $this->rationale = $rationale;
        return $this;
    }

    /**
     * Provides a summary of relevant clinical guidelines or other clinical recommendations supporting the measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getClinicalRecommendationStatement()
    {
        return $this->clinicalRecommendationStatement;
    }

    /**
     * Provides a summary of relevant clinical guidelines or other clinical recommendations supporting the measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $clinicalRecommendationStatement
     * @return $this
     */
    public function setClinicalRecommendationStatement($clinicalRecommendationStatement)
    {
        $this->clinicalRecommendationStatement = $clinicalRecommendationStatement;
        return $this;
    }

    /**
     * Information on whether an increase or decrease in score is the preferred result (e.g., a higher score indicates better quality OR a lower score indicates better quality OR quality is within a range).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getImprovementNotation()
    {
        return $this->improvementNotation;
    }

    /**
     * Information on whether an increase or decrease in score is the preferred result (e.g., a higher score indicates better quality OR a lower score indicates better quality OR quality is within a range).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $improvementNotation
     * @return $this
     */
    public function setImprovementNotation($improvementNotation)
    {
        $this->improvementNotation = $improvementNotation;
        return $this;
    }

    /**
     * Provides a description of an individual term used within the measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown[]
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Provides a description of an individual term used within the measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $definition
     * @return $this
     */
    public function addDefinition($definition)
    {
        $this->definition[] = $definition;
        return $this;
    }

    /**
     * Additional guidance for the measure including how it can be used in a clinical context, and the intent of the measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getGuidance()
    {
        return $this->guidance;
    }

    /**
     * Additional guidance for the measure including how it can be used in a clinical context, and the intent of the measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $guidance
     * @return $this
     */
    public function setGuidance($guidance)
    {
        $this->guidance = $guidance;
        return $this;
    }

    /**
     * A group of population criteria for the measure.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasure\FHIRMeasureGroup[]
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * A group of population criteria for the measure.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasure\FHIRMeasureGroup $group
     * @return $this
     */
    public function addGroup($group)
    {
        $this->group[] = $group;
        return $this;
    }

    /**
     * The supplemental data criteria for the measure report, specified as either the name of a valid CQL expression within a referenced library, or a valid FHIR Resource Path.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasure\FHIRMeasureSupplementalData[]
     */
    public function getSupplementalData()
    {
        return $this->supplementalData;
    }

    /**
     * The supplemental data criteria for the measure report, specified as either the name of a valid CQL expression within a referenced library, or a valid FHIR Resource Path.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMeasure\FHIRMeasureSupplementalData $supplementalData
     * @return $this
     */
    public function addSupplementalData($supplementalData)
    {
        $this->supplementalData[] = $supplementalData;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @param mixed $data
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
            }
            if (isset($data['identifier'])) {
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, ' . gettype($data['identifier']) . ' seen.');
                }
            }
            if (isset($data['version'])) {
                $this->setVersion($data['version']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['title'])) {
                $this->setTitle($data['title']);
            }
            if (isset($data['subtitle'])) {
                $this->setSubtitle($data['subtitle']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['experimental'])) {
                $this->setExperimental($data['experimental']);
            }
            if (isset($data['subjectCodeableConcept'])) {
                $this->setSubjectCodeableConcept($data['subjectCodeableConcept']);
            }
            if (isset($data['subjectReference'])) {
                $this->setSubjectReference($data['subjectReference']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['publisher'])) {
                $this->setPublisher($data['publisher']);
            }
            if (isset($data['contact'])) {
                if (is_array($data['contact'])) {
                    foreach ($data['contact'] as $d) {
                        $this->addContact($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contact" must be array of objects or null, ' . gettype($data['contact']) . ' seen.');
                }
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['useContext'])) {
                if (is_array($data['useContext'])) {
                    foreach ($data['useContext'] as $d) {
                        $this->addUseContext($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"useContext" must be array of objects or null, ' . gettype($data['useContext']) . ' seen.');
                }
            }
            if (isset($data['jurisdiction'])) {
                if (is_array($data['jurisdiction'])) {
                    foreach ($data['jurisdiction'] as $d) {
                        $this->addJurisdiction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"jurisdiction" must be array of objects or null, ' . gettype($data['jurisdiction']) . ' seen.');
                }
            }
            if (isset($data['purpose'])) {
                $this->setPurpose($data['purpose']);
            }
            if (isset($data['usage'])) {
                $this->setUsage($data['usage']);
            }
            if (isset($data['copyright'])) {
                $this->setCopyright($data['copyright']);
            }
            if (isset($data['approvalDate'])) {
                $this->setApprovalDate($data['approvalDate']);
            }
            if (isset($data['lastReviewDate'])) {
                $this->setLastReviewDate($data['lastReviewDate']);
            }
            if (isset($data['effectivePeriod'])) {
                $this->setEffectivePeriod($data['effectivePeriod']);
            }
            if (isset($data['topic'])) {
                if (is_array($data['topic'])) {
                    foreach ($data['topic'] as $d) {
                        $this->addTopic($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"topic" must be array of objects or null, ' . gettype($data['topic']) . ' seen.');
                }
            }
            if (isset($data['author'])) {
                if (is_array($data['author'])) {
                    foreach ($data['author'] as $d) {
                        $this->addAuthor($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"author" must be array of objects or null, ' . gettype($data['author']) . ' seen.');
                }
            }
            if (isset($data['editor'])) {
                if (is_array($data['editor'])) {
                    foreach ($data['editor'] as $d) {
                        $this->addEditor($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"editor" must be array of objects or null, ' . gettype($data['editor']) . ' seen.');
                }
            }
            if (isset($data['reviewer'])) {
                if (is_array($data['reviewer'])) {
                    foreach ($data['reviewer'] as $d) {
                        $this->addReviewer($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reviewer" must be array of objects or null, ' . gettype($data['reviewer']) . ' seen.');
                }
            }
            if (isset($data['endorser'])) {
                if (is_array($data['endorser'])) {
                    foreach ($data['endorser'] as $d) {
                        $this->addEndorser($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"endorser" must be array of objects or null, ' . gettype($data['endorser']) . ' seen.');
                }
            }
            if (isset($data['relatedArtifact'])) {
                if (is_array($data['relatedArtifact'])) {
                    foreach ($data['relatedArtifact'] as $d) {
                        $this->addRelatedArtifact($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"relatedArtifact" must be array of objects or null, ' . gettype($data['relatedArtifact']) . ' seen.');
                }
            }
            if (isset($data['library'])) {
                if (is_array($data['library'])) {
                    foreach ($data['library'] as $d) {
                        $this->addLibrary($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"library" must be array of objects or null, ' . gettype($data['library']) . ' seen.');
                }
            }
            if (isset($data['disclaimer'])) {
                $this->setDisclaimer($data['disclaimer']);
            }
            if (isset($data['scoring'])) {
                $this->setScoring($data['scoring']);
            }
            if (isset($data['compositeScoring'])) {
                $this->setCompositeScoring($data['compositeScoring']);
            }
            if (isset($data['type'])) {
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, ' . gettype($data['type']) . ' seen.');
                }
            }
            if (isset($data['riskAdjustment'])) {
                $this->setRiskAdjustment($data['riskAdjustment']);
            }
            if (isset($data['rateAggregation'])) {
                $this->setRateAggregation($data['rateAggregation']);
            }
            if (isset($data['rationale'])) {
                $this->setRationale($data['rationale']);
            }
            if (isset($data['clinicalRecommendationStatement'])) {
                $this->setClinicalRecommendationStatement($data['clinicalRecommendationStatement']);
            }
            if (isset($data['improvementNotation'])) {
                $this->setImprovementNotation($data['improvementNotation']);
            }
            if (isset($data['definition'])) {
                if (is_array($data['definition'])) {
                    foreach ($data['definition'] as $d) {
                        $this->addDefinition($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"definition" must be array of objects or null, ' . gettype($data['definition']) . ' seen.');
                }
            }
            if (isset($data['guidance'])) {
                $this->setGuidance($data['guidance']);
            }
            if (isset($data['group'])) {
                if (is_array($data['group'])) {
                    foreach ($data['group'] as $d) {
                        $this->addGroup($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"group" must be array of objects or null, ' . gettype($data['group']) . ' seen.');
                }
            }
            if (isset($data['supplementalData'])) {
                if (is_array($data['supplementalData'])) {
                    foreach ($data['supplementalData'] as $d) {
                        $this->addSupplementalData($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supplementalData" must be array of objects or null, ' . gettype($data['supplementalData']) . ' seen.');
                }
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
        }
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->version)) {
            $json['version'] = $this->version;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->title)) {
            $json['title'] = $this->title;
        }
        if (isset($this->subtitle)) {
            $json['subtitle'] = $this->subtitle;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->experimental)) {
            $json['experimental'] = $this->experimental;
        }
        if (isset($this->subjectCodeableConcept)) {
            $json['subjectCodeableConcept'] = $this->subjectCodeableConcept;
        }
        if (isset($this->subjectReference)) {
            $json['subjectReference'] = $this->subjectReference;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (isset($this->publisher)) {
            $json['publisher'] = $this->publisher;
        }
        if (0 < count($this->contact)) {
            $json['contact'] = [];
            foreach ($this->contact as $contact) {
                $json['contact'][] = $contact;
            }
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->useContext)) {
            $json['useContext'] = [];
            foreach ($this->useContext as $useContext) {
                $json['useContext'][] = $useContext;
            }
        }
        if (0 < count($this->jurisdiction)) {
            $json['jurisdiction'] = [];
            foreach ($this->jurisdiction as $jurisdiction) {
                $json['jurisdiction'][] = $jurisdiction;
            }
        }
        if (isset($this->purpose)) {
            $json['purpose'] = $this->purpose;
        }
        if (isset($this->usage)) {
            $json['usage'] = $this->usage;
        }
        if (isset($this->copyright)) {
            $json['copyright'] = $this->copyright;
        }
        if (isset($this->approvalDate)) {
            $json['approvalDate'] = $this->approvalDate;
        }
        if (isset($this->lastReviewDate)) {
            $json['lastReviewDate'] = $this->lastReviewDate;
        }
        if (isset($this->effectivePeriod)) {
            $json['effectivePeriod'] = $this->effectivePeriod;
        }
        if (0 < count($this->topic)) {
            $json['topic'] = [];
            foreach ($this->topic as $topic) {
                $json['topic'][] = $topic;
            }
        }
        if (0 < count($this->author)) {
            $json['author'] = [];
            foreach ($this->author as $author) {
                $json['author'][] = $author;
            }
        }
        if (0 < count($this->editor)) {
            $json['editor'] = [];
            foreach ($this->editor as $editor) {
                $json['editor'][] = $editor;
            }
        }
        if (0 < count($this->reviewer)) {
            $json['reviewer'] = [];
            foreach ($this->reviewer as $reviewer) {
                $json['reviewer'][] = $reviewer;
            }
        }
        if (0 < count($this->endorser)) {
            $json['endorser'] = [];
            foreach ($this->endorser as $endorser) {
                $json['endorser'][] = $endorser;
            }
        }
        if (0 < count($this->relatedArtifact)) {
            $json['relatedArtifact'] = [];
            foreach ($this->relatedArtifact as $relatedArtifact) {
                $json['relatedArtifact'][] = $relatedArtifact;
            }
        }
        if (0 < count($this->library)) {
            $json['library'] = [];
            foreach ($this->library as $library) {
                $json['library'][] = $library;
            }
        }
        if (isset($this->disclaimer)) {
            $json['disclaimer'] = $this->disclaimer;
        }
        if (isset($this->scoring)) {
            $json['scoring'] = $this->scoring;
        }
        if (isset($this->compositeScoring)) {
            $json['compositeScoring'] = $this->compositeScoring;
        }
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
        }
        if (isset($this->riskAdjustment)) {
            $json['riskAdjustment'] = $this->riskAdjustment;
        }
        if (isset($this->rateAggregation)) {
            $json['rateAggregation'] = $this->rateAggregation;
        }
        if (isset($this->rationale)) {
            $json['rationale'] = $this->rationale;
        }
        if (isset($this->clinicalRecommendationStatement)) {
            $json['clinicalRecommendationStatement'] = $this->clinicalRecommendationStatement;
        }
        if (isset($this->improvementNotation)) {
            $json['improvementNotation'] = $this->improvementNotation;
        }
        if (0 < count($this->definition)) {
            $json['definition'] = [];
            foreach ($this->definition as $definition) {
                $json['definition'][] = $definition;
            }
        }
        if (isset($this->guidance)) {
            $json['guidance'] = $this->guidance;
        }
        if (0 < count($this->group)) {
            $json['group'] = [];
            foreach ($this->group as $group) {
                $json['group'][] = $group;
            }
        }
        if (0 < count($this->supplementalData)) {
            $json['supplementalData'] = [];
            foreach ($this->supplementalData as $supplementalData) {
                $json['supplementalData'][] = $supplementalData;
            }
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<Measure xmlns="http://hl7.org/fhir"></Measure>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->version)) {
            $this->version->xmlSerialize(true, $sxe->addChild('version'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->title)) {
            $this->title->xmlSerialize(true, $sxe->addChild('title'));
        }
        if (isset($this->subtitle)) {
            $this->subtitle->xmlSerialize(true, $sxe->addChild('subtitle'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->experimental)) {
            $this->experimental->xmlSerialize(true, $sxe->addChild('experimental'));
        }
        if (isset($this->subjectCodeableConcept)) {
            $this->subjectCodeableConcept->xmlSerialize(true, $sxe->addChild('subjectCodeableConcept'));
        }
        if (isset($this->subjectReference)) {
            $this->subjectReference->xmlSerialize(true, $sxe->addChild('subjectReference'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->publisher)) {
            $this->publisher->xmlSerialize(true, $sxe->addChild('publisher'));
        }
        if (0 < count($this->contact)) {
            foreach ($this->contact as $contact) {
                $contact->xmlSerialize(true, $sxe->addChild('contact'));
            }
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->useContext)) {
            foreach ($this->useContext as $useContext) {
                $useContext->xmlSerialize(true, $sxe->addChild('useContext'));
            }
        }
        if (0 < count($this->jurisdiction)) {
            foreach ($this->jurisdiction as $jurisdiction) {
                $jurisdiction->xmlSerialize(true, $sxe->addChild('jurisdiction'));
            }
        }
        if (isset($this->purpose)) {
            $this->purpose->xmlSerialize(true, $sxe->addChild('purpose'));
        }
        if (isset($this->usage)) {
            $this->usage->xmlSerialize(true, $sxe->addChild('usage'));
        }
        if (isset($this->copyright)) {
            $this->copyright->xmlSerialize(true, $sxe->addChild('copyright'));
        }
        if (isset($this->approvalDate)) {
            $this->approvalDate->xmlSerialize(true, $sxe->addChild('approvalDate'));
        }
        if (isset($this->lastReviewDate)) {
            $this->lastReviewDate->xmlSerialize(true, $sxe->addChild('lastReviewDate'));
        }
        if (isset($this->effectivePeriod)) {
            $this->effectivePeriod->xmlSerialize(true, $sxe->addChild('effectivePeriod'));
        }
        if (0 < count($this->topic)) {
            foreach ($this->topic as $topic) {
                $topic->xmlSerialize(true, $sxe->addChild('topic'));
            }
        }
        if (0 < count($this->author)) {
            foreach ($this->author as $author) {
                $author->xmlSerialize(true, $sxe->addChild('author'));
            }
        }
        if (0 < count($this->editor)) {
            foreach ($this->editor as $editor) {
                $editor->xmlSerialize(true, $sxe->addChild('editor'));
            }
        }
        if (0 < count($this->reviewer)) {
            foreach ($this->reviewer as $reviewer) {
                $reviewer->xmlSerialize(true, $sxe->addChild('reviewer'));
            }
        }
        if (0 < count($this->endorser)) {
            foreach ($this->endorser as $endorser) {
                $endorser->xmlSerialize(true, $sxe->addChild('endorser'));
            }
        }
        if (0 < count($this->relatedArtifact)) {
            foreach ($this->relatedArtifact as $relatedArtifact) {
                $relatedArtifact->xmlSerialize(true, $sxe->addChild('relatedArtifact'));
            }
        }
        if (0 < count($this->library)) {
            foreach ($this->library as $library) {
                $library->xmlSerialize(true, $sxe->addChild('library'));
            }
        }
        if (isset($this->disclaimer)) {
            $this->disclaimer->xmlSerialize(true, $sxe->addChild('disclaimer'));
        }
        if (isset($this->scoring)) {
            $this->scoring->xmlSerialize(true, $sxe->addChild('scoring'));
        }
        if (isset($this->compositeScoring)) {
            $this->compositeScoring->xmlSerialize(true, $sxe->addChild('compositeScoring'));
        }
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (isset($this->riskAdjustment)) {
            $this->riskAdjustment->xmlSerialize(true, $sxe->addChild('riskAdjustment'));
        }
        if (isset($this->rateAggregation)) {
            $this->rateAggregation->xmlSerialize(true, $sxe->addChild('rateAggregation'));
        }
        if (isset($this->rationale)) {
            $this->rationale->xmlSerialize(true, $sxe->addChild('rationale'));
        }
        if (isset($this->clinicalRecommendationStatement)) {
            $this->clinicalRecommendationStatement->xmlSerialize(true, $sxe->addChild('clinicalRecommendationStatement'));
        }
        if (isset($this->improvementNotation)) {
            $this->improvementNotation->xmlSerialize(true, $sxe->addChild('improvementNotation'));
        }
        if (0 < count($this->definition)) {
            foreach ($this->definition as $definition) {
                $definition->xmlSerialize(true, $sxe->addChild('definition'));
            }
        }
        if (isset($this->guidance)) {
            $this->guidance->xmlSerialize(true, $sxe->addChild('guidance'));
        }
        if (0 < count($this->group)) {
            foreach ($this->group as $group) {
                $group->xmlSerialize(true, $sxe->addChild('group'));
            }
        }
        if (0 < count($this->supplementalData)) {
            foreach ($this->supplementalData as $supplementalData) {
                $supplementalData->xmlSerialize(true, $sxe->addChild('supplementalData'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
