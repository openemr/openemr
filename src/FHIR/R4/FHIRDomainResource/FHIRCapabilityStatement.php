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
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR Server for a particular version of FHIR that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRCapabilityStatement extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * An absolute URI that is used to identify this capability statement when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this capability statement is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the capability statement is stored on different servers.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * The identifier that is used to identify this version of the capability statement when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the capability statement author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * A natural language name identifying the capability statement. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A short, descriptive, user-friendly title for the capability statement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * The status of this capability statement. Enables tracking the life-cycle of the content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public $status = null;

    /**
     * A Boolean value to indicate that this capability statement is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $experimental = null;

    /**
     * The date  (and optionally time) when the capability statement was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the capability statement changes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * The name of the organization or individual that published the capability statement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $publisher = null;

    /**
     * Contact details to assist a user in finding and communicating with the publisher.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public $contact = [];

    /**
     * A free text natural language description of the capability statement from a consumer's perspective. Typically, this is used when the capability statement describes a desired rather than an actual solution, for example as a formal expression of requirements as part of an RFP.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $description = null;

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate capability statement instances.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    public $useContext = [];

    /**
     * A legal or geographic region in which the capability statement is intended to be used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $jurisdiction = [];

    /**
     * Explanation of why this capability statement is needed and why it has been designed as it has.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $purpose = null;

    /**
     * A copyright statement relating to the capability statement and/or its contents. Copyright statements are generally legal restrictions on the use and publishing of the capability statement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $copyright = null;

    /**
     * The way that this statement is intended to be used, to describe an actual running instance of software, a particular product (kind, not instance of software) or a class of implementation (e.g. a desired purchase).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCapabilityStatementKind
     */
    public $kind = null;

    /**
     * Reference to a canonical URL of another CapabilityStatement that this software implements. This capability statement is a published API description that corresponds to a business service. The server may actually implement a subset of the capability statement it claims to implement, so the capability statement must specify the full capability details.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $instantiates = [];

    /**
     * Reference to a canonical URL of another CapabilityStatement that this software adds to. The capability statement automatically includes everything in the other statement, and it is not duplicated, though the server may repeat the same resources, interactions and operations to add additional details to them.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $imports = [];

    /**
     * Software that is covered by this capability statement.  It is used when the capability statement describes the capabilities of a particular software version, independent of an installation.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSoftware
     */
    public $software = null;

    /**
     * Identifies a specific implementation instance that is described by the capability statement - i.e. a particular installation, rather than the capabilities of a software program.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementImplementation
     */
    public $implementation = null;

    /**
     * The version of the FHIR specification that this CapabilityStatement describes (which SHALL be the same as the FHIR version of the CapabilityStatement itself). There is no default value.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRVersion
     */
    public $fhirVersion = null;

    /**
     * A list of the formats supported by this implementation using their content types.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public $format = [];

    /**
     * A list of the patch formats supported by this implementation using their content types.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public $patchFormat = [];

    /**
     * A list of implementation guides that the server does (or should) support in their entirety.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $implementationGuide = [];

    /**
     * A definition of the restful capabilities of the solution, if any.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementRest[]
     */
    public $rest = [];

    /**
     * A description of the messaging capabilities of the solution.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementMessaging[]
     */
    public $messaging = [];

    /**
     * A document definition.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementDocument[]
     */
    public $document = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement';

    /**
     * An absolute URI that is used to identify this capability statement when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this capability statement is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the capability statement is stored on different servers.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * An absolute URI that is used to identify this capability statement when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this capability statement is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the capability statement is stored on different servers.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * The identifier that is used to identify this version of the capability statement when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the capability statement author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The identifier that is used to identify this version of the capability statement when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the capability statement author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * A natural language name identifying the capability statement. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A natural language name identifying the capability statement. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A short, descriptive, user-friendly title for the capability statement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * A short, descriptive, user-friendly title for the capability statement.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * The status of this capability statement. Enables tracking the life-cycle of the content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of this capability statement. Enables tracking the life-cycle of the content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A Boolean value to indicate that this capability statement is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getExperimental()
    {
        return $this->experimental;
    }

    /**
     * A Boolean value to indicate that this capability statement is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $experimental
     * @return $this
     */
    public function setExperimental($experimental)
    {
        $this->experimental = $experimental;
        return $this;
    }

    /**
     * The date  (and optionally time) when the capability statement was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the capability statement changes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date  (and optionally time) when the capability statement was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the capability statement changes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * The name of the organization or individual that published the capability statement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * The name of the organization or individual that published the capability statement.
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
     * A free text natural language description of the capability statement from a consumer's perspective. Typically, this is used when the capability statement describes a desired rather than an actual solution, for example as a formal expression of requirements as part of an RFP.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A free text natural language description of the capability statement from a consumer's perspective. Typically, this is used when the capability statement describes a desired rather than an actual solution, for example as a formal expression of requirements as part of an RFP.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate capability statement instances.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    public function getUseContext()
    {
        return $this->useContext;
    }

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate capability statement instances.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext $useContext
     * @return $this
     */
    public function addUseContext($useContext)
    {
        $this->useContext[] = $useContext;
        return $this;
    }

    /**
     * A legal or geographic region in which the capability statement is intended to be used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * A legal or geographic region in which the capability statement is intended to be used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $jurisdiction
     * @return $this
     */
    public function addJurisdiction($jurisdiction)
    {
        $this->jurisdiction[] = $jurisdiction;
        return $this;
    }

    /**
     * Explanation of why this capability statement is needed and why it has been designed as it has.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Explanation of why this capability statement is needed and why it has been designed as it has.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $purpose
     * @return $this
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * A copyright statement relating to the capability statement and/or its contents. Copyright statements are generally legal restrictions on the use and publishing of the capability statement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * A copyright statement relating to the capability statement and/or its contents. Copyright statements are generally legal restrictions on the use and publishing of the capability statement.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $copyright
     * @return $this
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
        return $this;
    }

    /**
     * The way that this statement is intended to be used, to describe an actual running instance of software, a particular product (kind, not instance of software) or a class of implementation (e.g. a desired purchase).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCapabilityStatementKind
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * The way that this statement is intended to be used, to describe an actual running instance of software, a particular product (kind, not instance of software) or a class of implementation (e.g. a desired purchase).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCapabilityStatementKind $kind
     * @return $this
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
        return $this;
    }

    /**
     * Reference to a canonical URL of another CapabilityStatement that this software implements. This capability statement is a published API description that corresponds to a business service. The server may actually implement a subset of the capability statement it claims to implement, so the capability statement must specify the full capability details.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getInstantiates()
    {
        return $this->instantiates;
    }

    /**
     * Reference to a canonical URL of another CapabilityStatement that this software implements. This capability statement is a published API description that corresponds to a business service. The server may actually implement a subset of the capability statement it claims to implement, so the capability statement must specify the full capability details.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $instantiates
     * @return $this
     */
    public function addInstantiates($instantiates)
    {
        $this->instantiates[] = $instantiates;
        return $this;
    }

    /**
     * Reference to a canonical URL of another CapabilityStatement that this software adds to. The capability statement automatically includes everything in the other statement, and it is not duplicated, though the server may repeat the same resources, interactions and operations to add additional details to them.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getImports()
    {
        return $this->imports;
    }

    /**
     * Reference to a canonical URL of another CapabilityStatement that this software adds to. The capability statement automatically includes everything in the other statement, and it is not duplicated, though the server may repeat the same resources, interactions and operations to add additional details to them.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $imports
     * @return $this
     */
    public function addImports($imports)
    {
        $this->imports[] = $imports;
        return $this;
    }

    /**
     * Software that is covered by this capability statement.  It is used when the capability statement describes the capabilities of a particular software version, independent of an installation.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSoftware
     */
    public function getSoftware()
    {
        return $this->software;
    }

    /**
     * Software that is covered by this capability statement.  It is used when the capability statement describes the capabilities of a particular software version, independent of an installation.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSoftware $software
     * @return $this
     */
    public function setSoftware($software)
    {
        $this->software = $software;
        return $this;
    }

    /**
     * Identifies a specific implementation instance that is described by the capability statement - i.e. a particular installation, rather than the capabilities of a software program.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementImplementation
     */
    public function getImplementation()
    {
        return $this->implementation;
    }

    /**
     * Identifies a specific implementation instance that is described by the capability statement - i.e. a particular installation, rather than the capabilities of a software program.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementImplementation $implementation
     * @return $this
     */
    public function setImplementation($implementation)
    {
        $this->implementation = $implementation;
        return $this;
    }

    /**
     * The version of the FHIR specification that this CapabilityStatement describes (which SHALL be the same as the FHIR version of the CapabilityStatement itself). There is no default value.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRVersion
     */
    public function getFhirVersion()
    {
        return $this->fhirVersion;
    }

    /**
     * The version of the FHIR specification that this CapabilityStatement describes (which SHALL be the same as the FHIR version of the CapabilityStatement itself). There is no default value.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRVersion $fhirVersion
     * @return $this
     */
    public function setFhirVersion($fhirVersion)
    {
        $this->fhirVersion = $fhirVersion;
        return $this;
    }

    /**
     * A list of the formats supported by this implementation using their content types.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * A list of the formats supported by this implementation using their content types.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $format
     * @return $this
     */
    public function addFormat($format)
    {
        $this->format[] = $format;
        return $this;
    }

    /**
     * A list of the patch formats supported by this implementation using their content types.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public function getPatchFormat()
    {
        return $this->patchFormat;
    }

    /**
     * A list of the patch formats supported by this implementation using their content types.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $patchFormat
     * @return $this
     */
    public function addPatchFormat($patchFormat)
    {
        $this->patchFormat[] = $patchFormat;
        return $this;
    }

    /**
     * A list of implementation guides that the server does (or should) support in their entirety.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getImplementationGuide()
    {
        return $this->implementationGuide;
    }

    /**
     * A list of implementation guides that the server does (or should) support in their entirety.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $implementationGuide
     * @return $this
     */
    public function addImplementationGuide($implementationGuide)
    {
        $this->implementationGuide[] = $implementationGuide;
        return $this;
    }

    /**
     * A definition of the restful capabilities of the solution, if any.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementRest[]
     */
    public function getRest()
    {
        return $this->rest;
    }

    /**
     * A definition of the restful capabilities of the solution, if any.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementRest $rest
     * @return $this
     */
    public function addRest($rest)
    {
        $this->rest[] = $rest;
        return $this;
    }

    /**
     * A description of the messaging capabilities of the solution.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementMessaging[]
     */
    public function getMessaging()
    {
        return $this->messaging;
    }

    /**
     * A description of the messaging capabilities of the solution.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementMessaging $messaging
     * @return $this
     */
    public function addMessaging($messaging)
    {
        $this->messaging[] = $messaging;
        return $this;
    }

    /**
     * A document definition.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementDocument[]
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * A document definition.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementDocument $document
     * @return $this
     */
    public function addDocument($document)
    {
        $this->document[] = $document;
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
            if (isset($data['kind'])) {
                $this->setKind($data['kind']);
            }
            if (isset($data['instantiates'])) {
                if (is_array($data['instantiates'])) {
                    foreach ($data['instantiates'] as $d) {
                        $this->addInstantiates($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"instantiates" must be array of objects or null, ' . gettype($data['instantiates']) . ' seen.');
                }
            }
            if (isset($data['imports'])) {
                if (is_array($data['imports'])) {
                    foreach ($data['imports'] as $d) {
                        $this->addImports($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"imports" must be array of objects or null, ' . gettype($data['imports']) . ' seen.');
                }
            }
            if (isset($data['software'])) {
                $this->setSoftware($data['software']);
            }
            if (isset($data['implementation'])) {
                $this->setImplementation($data['implementation']);
            }
            if (isset($data['fhirVersion'])) {
                $this->setFhirVersion($data['fhirVersion']);
            }
            if (isset($data['format'])) {
                if (is_array($data['format'])) {
                    foreach ($data['format'] as $d) {
                        $this->addFormat($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"format" must be array of objects or null, ' . gettype($data['format']) . ' seen.');
                }
            }
            if (isset($data['patchFormat'])) {
                if (is_array($data['patchFormat'])) {
                    foreach ($data['patchFormat'] as $d) {
                        $this->addPatchFormat($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"patchFormat" must be array of objects or null, ' . gettype($data['patchFormat']) . ' seen.');
                }
            }
            if (isset($data['implementationGuide'])) {
                if (is_array($data['implementationGuide'])) {
                    foreach ($data['implementationGuide'] as $d) {
                        $this->addImplementationGuide($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"implementationGuide" must be array of objects or null, ' . gettype($data['implementationGuide']) . ' seen.');
                }
            }
            if (isset($data['rest'])) {
                if (is_array($data['rest'])) {
                    foreach ($data['rest'] as $d) {
                        $this->addRest($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"rest" must be array of objects or null, ' . gettype($data['rest']) . ' seen.');
                }
            }
            if (isset($data['messaging'])) {
                if (is_array($data['messaging'])) {
                    foreach ($data['messaging'] as $d) {
                        $this->addMessaging($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"messaging" must be array of objects or null, ' . gettype($data['messaging']) . ' seen.');
                }
            }
            if (isset($data['document'])) {
                if (is_array($data['document'])) {
                    foreach ($data['document'] as $d) {
                        $this->addDocument($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"document" must be array of objects or null, ' . gettype($data['document']) . ' seen.');
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
        if (isset($this->kind)) {
            $json['kind'] = $this->kind;
        }
        if (0 < count($this->instantiates)) {
            $json['instantiates'] = [];
            foreach ($this->instantiates as $instantiates) {
                $json['instantiates'][] = $instantiates;
            }
        }
        if (0 < count($this->imports)) {
            $json['imports'] = [];
            foreach ($this->imports as $imports) {
                $json['imports'][] = $imports;
            }
        }
        if (isset($this->software)) {
            $json['software'] = $this->software;
        }
        if (isset($this->implementation)) {
            $json['implementation'] = $this->implementation;
        }
        if (isset($this->fhirVersion)) {
            $json['fhirVersion'] = $this->fhirVersion;
        }
        if (0 < count($this->format)) {
            $json['format'] = [];
            foreach ($this->format as $format) {
                $json['format'][] = $format;
            }
        }
        if (0 < count($this->patchFormat)) {
            $json['patchFormat'] = [];
            foreach ($this->patchFormat as $patchFormat) {
                $json['patchFormat'][] = $patchFormat;
            }
        }
        if (0 < count($this->implementationGuide)) {
            $json['implementationGuide'] = [];
            foreach ($this->implementationGuide as $implementationGuide) {
                $json['implementationGuide'][] = $implementationGuide;
            }
        }
        if (0 < count($this->rest)) {
            $json['rest'] = [];
            foreach ($this->rest as $rest) {
                $json['rest'][] = $rest;
            }
        }
        if (0 < count($this->messaging)) {
            $json['messaging'] = [];
            foreach ($this->messaging as $messaging) {
                $json['messaging'][] = $messaging;
            }
        }
        if (0 < count($this->document)) {
            $json['document'] = [];
            foreach ($this->document as $document) {
                $json['document'][] = $document;
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
            $sxe = new \SimpleXMLElement('<CapabilityStatement xmlns="http://hl7.org/fhir"></CapabilityStatement>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
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
        if (isset($this->kind)) {
            $this->kind->xmlSerialize(true, $sxe->addChild('kind'));
        }
        if (0 < count($this->instantiates)) {
            foreach ($this->instantiates as $instantiates) {
                $instantiates->xmlSerialize(true, $sxe->addChild('instantiates'));
            }
        }
        if (0 < count($this->imports)) {
            foreach ($this->imports as $imports) {
                $imports->xmlSerialize(true, $sxe->addChild('imports'));
            }
        }
        if (isset($this->software)) {
            $this->software->xmlSerialize(true, $sxe->addChild('software'));
        }
        if (isset($this->implementation)) {
            $this->implementation->xmlSerialize(true, $sxe->addChild('implementation'));
        }
        if (isset($this->fhirVersion)) {
            $this->fhirVersion->xmlSerialize(true, $sxe->addChild('fhirVersion'));
        }
        if (0 < count($this->format)) {
            foreach ($this->format as $format) {
                $format->xmlSerialize(true, $sxe->addChild('format'));
            }
        }
        if (0 < count($this->patchFormat)) {
            foreach ($this->patchFormat as $patchFormat) {
                $patchFormat->xmlSerialize(true, $sxe->addChild('patchFormat'));
            }
        }
        if (0 < count($this->implementationGuide)) {
            foreach ($this->implementationGuide as $implementationGuide) {
                $implementationGuide->xmlSerialize(true, $sxe->addChild('implementationGuide'));
            }
        }
        if (0 < count($this->rest)) {
            foreach ($this->rest as $rest) {
                $rest->xmlSerialize(true, $sxe->addChild('rest'));
            }
        }
        if (0 < count($this->messaging)) {
            foreach ($this->messaging as $messaging) {
                $messaging->xmlSerialize(true, $sxe->addChild('messaging'));
            }
        }
        if (0 < count($this->document)) {
            foreach ($this->document as $document) {
                $document->xmlSerialize(true, $sxe->addChild('document'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
