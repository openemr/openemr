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
 * The CodeSystem resource is used to declare the existence of and describe a code system or code system supplement and its key properties, and optionally define a part or all of its content.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRCodeSystem extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * An absolute URI that is used to identify this code system when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this code system is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the code system is stored on different servers. This is used in [Coding](datatypes.html#Coding).system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * A formal identifier that is used to identify this code system when it is represented in other formats, or referenced in a specification, model, design or an instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The identifier that is used to identify this version of the code system when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the code system author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence. This is used in [Coding](datatypes.html#Coding).version.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * A natural language name identifying the code system. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A short, descriptive, user-friendly title for the code system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * The date (and optionally time) when the code system resource was created or revised.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public $status = null;

    /**
     * A Boolean value to indicate that this code system is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $experimental = null;

    /**
     * The date  (and optionally time) when the code system was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the code system changes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * The name of the organization or individual that published the code system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $publisher = null;

    /**
     * Contact details to assist a user in finding and communicating with the publisher.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public $contact = [];

    /**
     * A free text natural language description of the code system from a consumer's perspective.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $description = null;

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate code system instances.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    public $useContext = [];

    /**
     * A legal or geographic region in which the code system is intended to be used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $jurisdiction = [];

    /**
     * Explanation of why this code system is needed and why it has been designed as it has.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $purpose = null;

    /**
     * A copyright statement relating to the code system and/or its contents. Copyright statements are generally legal restrictions on the use and publishing of the code system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $copyright = null;

    /**
     * If code comparison is case sensitive when codes within this system are compared to each other.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $caseSensitive = null;

    /**
     * Canonical reference to the value set that contains the entire code system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $valueSet = null;

    /**
     * The meaning of the hierarchy of concepts as represented in this resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeSystemHierarchyMeaning
     */
    public $hierarchyMeaning = null;

    /**
     * The code system defines a compositional (post-coordination) grammar.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $compositional = null;

    /**
     * This flag is used to signify that the code system does not commit to concept permanence across versions. If true, a version must be specified when referencing this code system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $versionNeeded = null;

    /**
     * The extent of the content of the code system (the concepts and codes it defines) are represented in this resource instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeSystemContentMode
     */
    public $content = null;

    /**
     * The canonical URL of the code system that this code system supplement is adding designations and properties to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $supplements = null;

    /**
     * The total number of concepts defined by the code system. Where the code system has a compositional grammar, the basis of this count is defined by the system steward.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $count = null;

    /**
     * A filter that can be used in a value set compose statement when selecting concepts using a filter.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemFilter[]
     */
    public $filter = [];

    /**
     * A property defines an additional slot through which additional information can be provided about a concept.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemProperty[]
     */
    public $property = [];

    /**
     * Concepts that are in the code system. The concept definitions are inherently hierarchical, but the definitions must be consulted to determine what the meanings of the hierarchical relationships are.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemConcept[]
     */
    public $concept = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CodeSystem';

    /**
     * An absolute URI that is used to identify this code system when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this code system is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the code system is stored on different servers. This is used in [Coding](datatypes.html#Coding).system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * An absolute URI that is used to identify this code system when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this code system is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the code system is stored on different servers. This is used in [Coding](datatypes.html#Coding).system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * A formal identifier that is used to identify this code system when it is represented in other formats, or referenced in a specification, model, design or an instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A formal identifier that is used to identify this code system when it is represented in other formats, or referenced in a specification, model, design or an instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The identifier that is used to identify this version of the code system when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the code system author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence. This is used in [Coding](datatypes.html#Coding).version.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The identifier that is used to identify this version of the code system when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the code system author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence. This is used in [Coding](datatypes.html#Coding).version.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * A natural language name identifying the code system. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A natural language name identifying the code system. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A short, descriptive, user-friendly title for the code system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * A short, descriptive, user-friendly title for the code system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * The date (and optionally time) when the code system resource was created or revised.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The date (and optionally time) when the code system resource was created or revised.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A Boolean value to indicate that this code system is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getExperimental()
    {
        return $this->experimental;
    }

    /**
     * A Boolean value to indicate that this code system is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $experimental
     * @return $this
     */
    public function setExperimental($experimental)
    {
        $this->experimental = $experimental;
        return $this;
    }

    /**
     * The date  (and optionally time) when the code system was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the code system changes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date  (and optionally time) when the code system was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the code system changes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * The name of the organization or individual that published the code system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * The name of the organization or individual that published the code system.
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
     * A free text natural language description of the code system from a consumer's perspective.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A free text natural language description of the code system from a consumer's perspective.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate code system instances.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    public function getUseContext()
    {
        return $this->useContext;
    }

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate code system instances.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext $useContext
     * @return $this
     */
    public function addUseContext($useContext)
    {
        $this->useContext[] = $useContext;
        return $this;
    }

    /**
     * A legal or geographic region in which the code system is intended to be used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * A legal or geographic region in which the code system is intended to be used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $jurisdiction
     * @return $this
     */
    public function addJurisdiction($jurisdiction)
    {
        $this->jurisdiction[] = $jurisdiction;
        return $this;
    }

    /**
     * Explanation of why this code system is needed and why it has been designed as it has.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Explanation of why this code system is needed and why it has been designed as it has.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $purpose
     * @return $this
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * A copyright statement relating to the code system and/or its contents. Copyright statements are generally legal restrictions on the use and publishing of the code system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * A copyright statement relating to the code system and/or its contents. Copyright statements are generally legal restrictions on the use and publishing of the code system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $copyright
     * @return $this
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
        return $this;
    }

    /**
     * If code comparison is case sensitive when codes within this system are compared to each other.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getCaseSensitive()
    {
        return $this->caseSensitive;
    }

    /**
     * If code comparison is case sensitive when codes within this system are compared to each other.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $caseSensitive
     * @return $this
     */
    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;
        return $this;
    }

    /**
     * Canonical reference to the value set that contains the entire code system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getValueSet()
    {
        return $this->valueSet;
    }

    /**
     * Canonical reference to the value set that contains the entire code system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $valueSet
     * @return $this
     */
    public function setValueSet($valueSet)
    {
        $this->valueSet = $valueSet;
        return $this;
    }

    /**
     * The meaning of the hierarchy of concepts as represented in this resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeSystemHierarchyMeaning
     */
    public function getHierarchyMeaning()
    {
        return $this->hierarchyMeaning;
    }

    /**
     * The meaning of the hierarchy of concepts as represented in this resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeSystemHierarchyMeaning $hierarchyMeaning
     * @return $this
     */
    public function setHierarchyMeaning($hierarchyMeaning)
    {
        $this->hierarchyMeaning = $hierarchyMeaning;
        return $this;
    }

    /**
     * The code system defines a compositional (post-coordination) grammar.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getCompositional()
    {
        return $this->compositional;
    }

    /**
     * The code system defines a compositional (post-coordination) grammar.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $compositional
     * @return $this
     */
    public function setCompositional($compositional)
    {
        $this->compositional = $compositional;
        return $this;
    }

    /**
     * This flag is used to signify that the code system does not commit to concept permanence across versions. If true, a version must be specified when referencing this code system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getVersionNeeded()
    {
        return $this->versionNeeded;
    }

    /**
     * This flag is used to signify that the code system does not commit to concept permanence across versions. If true, a version must be specified when referencing this code system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $versionNeeded
     * @return $this
     */
    public function setVersionNeeded($versionNeeded)
    {
        $this->versionNeeded = $versionNeeded;
        return $this;
    }

    /**
     * The extent of the content of the code system (the concepts and codes it defines) are represented in this resource instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeSystemContentMode
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * The extent of the content of the code system (the concepts and codes it defines) are represented in this resource instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeSystemContentMode $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * The canonical URL of the code system that this code system supplement is adding designations and properties to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getSupplements()
    {
        return $this->supplements;
    }

    /**
     * The canonical URL of the code system that this code system supplement is adding designations and properties to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $supplements
     * @return $this
     */
    public function setSupplements($supplements)
    {
        $this->supplements = $supplements;
        return $this;
    }

    /**
     * The total number of concepts defined by the code system. Where the code system has a compositional grammar, the basis of this count is defined by the system steward.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * The total number of concepts defined by the code system. Where the code system has a compositional grammar, the basis of this count is defined by the system steward.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $count
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * A filter that can be used in a value set compose statement when selecting concepts using a filter.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemFilter[]
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * A filter that can be used in a value set compose statement when selecting concepts using a filter.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemFilter $filter
     * @return $this
     */
    public function addFilter($filter)
    {
        $this->filter[] = $filter;
        return $this;
    }

    /**
     * A property defines an additional slot through which additional information can be provided about a concept.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemProperty[]
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * A property defines an additional slot through which additional information can be provided about a concept.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemProperty $property
     * @return $this
     */
    public function addProperty($property)
    {
        $this->property[] = $property;
        return $this;
    }

    /**
     * Concepts that are in the code system. The concept definitions are inherently hierarchical, but the definitions must be consulted to determine what the meanings of the hierarchical relationships are.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemConcept[]
     */
    public function getConcept()
    {
        return $this->concept;
    }

    /**
     * Concepts that are in the code system. The concept definitions are inherently hierarchical, but the definitions must be consulted to determine what the meanings of the hierarchical relationships are.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCodeSystem\FHIRCodeSystemConcept $concept
     * @return $this
     */
    public function addConcept($concept)
    {
        $this->concept[] = $concept;
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
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['experimental'])) {
                $this->setExperimental($data['experimental']);
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
            if (isset($data['copyright'])) {
                $this->setCopyright($data['copyright']);
            }
            if (isset($data['caseSensitive'])) {
                $this->setCaseSensitive($data['caseSensitive']);
            }
            if (isset($data['valueSet'])) {
                $this->setValueSet($data['valueSet']);
            }
            if (isset($data['hierarchyMeaning'])) {
                $this->setHierarchyMeaning($data['hierarchyMeaning']);
            }
            if (isset($data['compositional'])) {
                $this->setCompositional($data['compositional']);
            }
            if (isset($data['versionNeeded'])) {
                $this->setVersionNeeded($data['versionNeeded']);
            }
            if (isset($data['content'])) {
                $this->setContent($data['content']);
            }
            if (isset($data['supplements'])) {
                $this->setSupplements($data['supplements']);
            }
            if (isset($data['count'])) {
                $this->setCount($data['count']);
            }
            if (isset($data['filter'])) {
                if (is_array($data['filter'])) {
                    foreach ($data['filter'] as $d) {
                        $this->addFilter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"filter" must be array of objects or null, ' . gettype($data['filter']) . ' seen.');
                }
            }
            if (isset($data['property'])) {
                if (is_array($data['property'])) {
                    foreach ($data['property'] as $d) {
                        $this->addProperty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"property" must be array of objects or null, ' . gettype($data['property']) . ' seen.');
                }
            }
            if (isset($data['concept'])) {
                if (is_array($data['concept'])) {
                    foreach ($data['concept'] as $d) {
                        $this->addConcept($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"concept" must be array of objects or null, ' . gettype($data['concept']) . ' seen.');
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
    public function jsonSerialize()
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->experimental)) {
            $json['experimental'] = $this->experimental;
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
        if (isset($this->copyright)) {
            $json['copyright'] = $this->copyright;
        }
        if (isset($this->caseSensitive)) {
            $json['caseSensitive'] = $this->caseSensitive;
        }
        if (isset($this->valueSet)) {
            $json['valueSet'] = $this->valueSet;
        }
        if (isset($this->hierarchyMeaning)) {
            $json['hierarchyMeaning'] = $this->hierarchyMeaning;
        }
        if (isset($this->compositional)) {
            $json['compositional'] = $this->compositional;
        }
        if (isset($this->versionNeeded)) {
            $json['versionNeeded'] = $this->versionNeeded;
        }
        if (isset($this->content)) {
            $json['content'] = $this->content;
        }
        if (isset($this->supplements)) {
            $json['supplements'] = $this->supplements;
        }
        if (isset($this->count)) {
            $json['count'] = $this->count;
        }
        if (0 < count($this->filter)) {
            $json['filter'] = [];
            foreach ($this->filter as $filter) {
                $json['filter'][] = $filter;
            }
        }
        if (0 < count($this->property)) {
            $json['property'] = [];
            foreach ($this->property as $property) {
                $json['property'][] = $property;
            }
        }
        if (0 < count($this->concept)) {
            $json['concept'] = [];
            foreach ($this->concept as $concept) {
                $json['concept'][] = $concept;
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
            $sxe = new \SimpleXMLElement('<CodeSystem xmlns="http://hl7.org/fhir"></CodeSystem>');
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
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->experimental)) {
            $this->experimental->xmlSerialize(true, $sxe->addChild('experimental'));
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
        if (isset($this->copyright)) {
            $this->copyright->xmlSerialize(true, $sxe->addChild('copyright'));
        }
        if (isset($this->caseSensitive)) {
            $this->caseSensitive->xmlSerialize(true, $sxe->addChild('caseSensitive'));
        }
        if (isset($this->valueSet)) {
            $this->valueSet->xmlSerialize(true, $sxe->addChild('valueSet'));
        }
        if (isset($this->hierarchyMeaning)) {
            $this->hierarchyMeaning->xmlSerialize(true, $sxe->addChild('hierarchyMeaning'));
        }
        if (isset($this->compositional)) {
            $this->compositional->xmlSerialize(true, $sxe->addChild('compositional'));
        }
        if (isset($this->versionNeeded)) {
            $this->versionNeeded->xmlSerialize(true, $sxe->addChild('versionNeeded'));
        }
        if (isset($this->content)) {
            $this->content->xmlSerialize(true, $sxe->addChild('content'));
        }
        if (isset($this->supplements)) {
            $this->supplements->xmlSerialize(true, $sxe->addChild('supplements'));
        }
        if (isset($this->count)) {
            $this->count->xmlSerialize(true, $sxe->addChild('count'));
        }
        if (0 < count($this->filter)) {
            foreach ($this->filter as $filter) {
                $filter->xmlSerialize(true, $sxe->addChild('filter'));
            }
        }
        if (0 < count($this->property)) {
            foreach ($this->property as $property) {
                $property->xmlSerialize(true, $sxe->addChild('property'));
            }
        }
        if (0 < count($this->concept)) {
            foreach ($this->concept as $concept) {
                $concept->xmlSerialize(true, $sxe->addChild('concept'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
