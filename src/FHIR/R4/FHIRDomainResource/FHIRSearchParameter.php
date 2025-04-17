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
 * A search parameter that defines a named search item that can be used to search/filter on a resource.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSearchParameter extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * An absolute URI that is used to identify this search parameter when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this search parameter is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the search parameter is stored on different servers.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $url = null;

    /**
     * The identifier that is used to identify this version of the search parameter when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the search parameter author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $version = null;

    /**
     * A natural language name identifying the search parameter. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * Where this search parameter is originally defined. If a derivedFrom is provided, then the details in the search parameter must be consistent with the definition from which it is defined. i.e. the parameter should have the same meaning, and (usually) the functionality should be a proper subset of the underlying search parameter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $derivedFrom = null;

    /**
     * The status of this search parameter. Enables tracking the life-cycle of the content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public $status = null;

    /**
     * A Boolean value to indicate that this search parameter is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $experimental = null;

    /**
     * The date  (and optionally time) when the search parameter was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the search parameter changes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * The name of the organization or individual that published the search parameter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $publisher = null;

    /**
     * Contact details to assist a user in finding and communicating with the publisher.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail[]
     */
    public $contact = [];

    /**
     * And how it used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $description = null;

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate search parameter instances.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    public $useContext = [];

    /**
     * A legal or geographic region in which the search parameter is intended to be used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $jurisdiction = [];

    /**
     * Explanation of why this search parameter is needed and why it has been designed as it has.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $purpose = null;

    /**
     * The code used in the URL or the parameter name in a parameters resource for this search parameter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $code = null;

    /**
     * The base resource type(s) that this search parameter can be used against.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public $base = [];

    /**
     * The type of value that a search parameter may contain, and how the content is interpreted.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSearchParamType
     */
    public $type = null;

    /**
     * A FHIRPath expression that returns a set of elements for the search parameter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $expression = null;

    /**
     * An XPath expression that returns a set of elements for the search parameter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $xpath = null;

    /**
     * How the search parameter relates to the set of elements returned by evaluating the xpath query.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRXPathUsageType
     */
    public $xpathUsage = null;

    /**
     * Types of resource (if a resource is referenced).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public $target = [];

    /**
     * Whether multiple values are allowed for each time the parameter exists. Values are separated by commas, and the parameter matches if any of the values match.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $multipleOr = null;

    /**
     * Whether multiple parameters are allowed - e.g. more than one parameter with the same name. The search matches if all the parameters match.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $multipleAnd = null;

    /**
     * Comparators supported for the search parameter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSearchComparator[]
     */
    public $comparator = [];

    /**
     * A modifier supported for the search parameter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSearchModifierCode[]
     */
    public $modifier = [];

    /**
     * Contains the names of any search parameters which may be chained to the containing search parameter. Chained parameters may be added to search parameters of type reference and specify that resources will only be returned if they contain a reference to a resource which matches the chained parameter value. Values for this field should be drawn from SearchParameter.code for a parameter on the target resource type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $chain = [];

    /**
     * Used to define the parts of a composite search parameter.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSearchParameter\FHIRSearchParameterComponent[]
     */
    public $component = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SearchParameter';

    /**
     * An absolute URI that is used to identify this search parameter when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this search parameter is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the search parameter is stored on different servers.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * An absolute URI that is used to identify this search parameter when it is referenced in a specification, model, design or an instance; also called its canonical identifier. This SHOULD be globally unique and SHOULD be a literal address at which at which an authoritative instance of this search parameter is (or will be) published. This URL can be the target of a canonical reference. It SHALL remain the same when the search parameter is stored on different servers.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * The identifier that is used to identify this version of the search parameter when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the search parameter author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The identifier that is used to identify this version of the search parameter when it is referenced in a specification, model, design or instance. This is an arbitrary value managed by the search parameter author and is not expected to be globally unique. For example, it might be a timestamp (e.g. yyyymmdd) if a managed version is not available. There is also no expectation that versions can be placed in a lexicographical sequence.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * A natural language name identifying the search parameter. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A natural language name identifying the search parameter. This name should be usable as an identifier for the module by machine processing applications such as code generation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Where this search parameter is originally defined. If a derivedFrom is provided, then the details in the search parameter must be consistent with the definition from which it is defined. i.e. the parameter should have the same meaning, and (usually) the functionality should be a proper subset of the underlying search parameter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getDerivedFrom()
    {
        return $this->derivedFrom;
    }

    /**
     * Where this search parameter is originally defined. If a derivedFrom is provided, then the details in the search parameter must be consistent with the definition from which it is defined. i.e. the parameter should have the same meaning, and (usually) the functionality should be a proper subset of the underlying search parameter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $derivedFrom
     * @return $this
     */
    public function setDerivedFrom($derivedFrom)
    {
        $this->derivedFrom = $derivedFrom;
        return $this;
    }

    /**
     * The status of this search parameter. Enables tracking the life-cycle of the content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of this search parameter. Enables tracking the life-cycle of the content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A Boolean value to indicate that this search parameter is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getExperimental()
    {
        return $this->experimental;
    }

    /**
     * A Boolean value to indicate that this search parameter is authored for testing purposes (or education/evaluation/marketing) and is not intended to be used for genuine usage.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $experimental
     * @return $this
     */
    public function setExperimental($experimental)
    {
        $this->experimental = $experimental;
        return $this;
    }

    /**
     * The date  (and optionally time) when the search parameter was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the search parameter changes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date  (and optionally time) when the search parameter was published. The date must change when the business version changes and it must change if the status code changes. In addition, it should change when the substantive content of the search parameter changes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * The name of the organization or individual that published the search parameter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * The name of the organization or individual that published the search parameter.
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
     * And how it used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * And how it used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate search parameter instances.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext[]
     */
    public function getUseContext()
    {
        return $this->useContext;
    }

    /**
     * The content was developed with a focus and intent of supporting the contexts that are listed. These contexts may be general categories (gender, age, ...) or may be references to specific programs (insurance plans, studies, ...) and may be used to assist with indexing and searching for appropriate search parameter instances.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext $useContext
     * @return $this
     */
    public function addUseContext($useContext)
    {
        $this->useContext[] = $useContext;
        return $this;
    }

    /**
     * A legal or geographic region in which the search parameter is intended to be used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * A legal or geographic region in which the search parameter is intended to be used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $jurisdiction
     * @return $this
     */
    public function addJurisdiction($jurisdiction)
    {
        $this->jurisdiction[] = $jurisdiction;
        return $this;
    }

    /**
     * Explanation of why this search parameter is needed and why it has been designed as it has.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Explanation of why this search parameter is needed and why it has been designed as it has.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $purpose
     * @return $this
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * The code used in the URL or the parameter name in a parameters resource for this search parameter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * The code used in the URL or the parameter name in a parameters resource for this search parameter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The base resource type(s) that this search parameter can be used against.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * The base resource type(s) that this search parameter can be used against.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $base
     * @return $this
     */
    public function addBase($base)
    {
        $this->base[] = $base;
        return $this;
    }

    /**
     * The type of value that a search parameter may contain, and how the content is interpreted.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSearchParamType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of value that a search parameter may contain, and how the content is interpreted.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSearchParamType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A FHIRPath expression that returns a set of elements for the search parameter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * A FHIRPath expression that returns a set of elements for the search parameter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * An XPath expression that returns a set of elements for the search parameter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getXpath()
    {
        return $this->xpath;
    }

    /**
     * An XPath expression that returns a set of elements for the search parameter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $xpath
     * @return $this
     */
    public function setXpath($xpath)
    {
        $this->xpath = $xpath;
        return $this;
    }

    /**
     * How the search parameter relates to the set of elements returned by evaluating the xpath query.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRXPathUsageType
     */
    public function getXpathUsage()
    {
        return $this->xpathUsage;
    }

    /**
     * How the search parameter relates to the set of elements returned by evaluating the xpath query.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRXPathUsageType $xpathUsage
     * @return $this
     */
    public function setXpathUsage($xpathUsage)
    {
        $this->xpathUsage = $xpathUsage;
        return $this;
    }

    /**
     * Types of resource (if a resource is referenced).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode[]
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Types of resource (if a resource is referenced).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $target
     * @return $this
     */
    public function addTarget($target)
    {
        $this->target[] = $target;
        return $this;
    }

    /**
     * Whether multiple values are allowed for each time the parameter exists. Values are separated by commas, and the parameter matches if any of the values match.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getMultipleOr()
    {
        return $this->multipleOr;
    }

    /**
     * Whether multiple values are allowed for each time the parameter exists. Values are separated by commas, and the parameter matches if any of the values match.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $multipleOr
     * @return $this
     */
    public function setMultipleOr($multipleOr)
    {
        $this->multipleOr = $multipleOr;
        return $this;
    }

    /**
     * Whether multiple parameters are allowed - e.g. more than one parameter with the same name. The search matches if all the parameters match.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getMultipleAnd()
    {
        return $this->multipleAnd;
    }

    /**
     * Whether multiple parameters are allowed - e.g. more than one parameter with the same name. The search matches if all the parameters match.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $multipleAnd
     * @return $this
     */
    public function setMultipleAnd($multipleAnd)
    {
        $this->multipleAnd = $multipleAnd;
        return $this;
    }

    /**
     * Comparators supported for the search parameter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSearchComparator[]
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * Comparators supported for the search parameter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSearchComparator $comparator
     * @return $this
     */
    public function addComparator($comparator)
    {
        $this->comparator[] = $comparator;
        return $this;
    }

    /**
     * A modifier supported for the search parameter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSearchModifierCode[]
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * A modifier supported for the search parameter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSearchModifierCode $modifier
     * @return $this
     */
    public function addModifier($modifier)
    {
        $this->modifier[] = $modifier;
        return $this;
    }

    /**
     * Contains the names of any search parameters which may be chained to the containing search parameter. Chained parameters may be added to search parameters of type reference and specify that resources will only be returned if they contain a reference to a resource which matches the chained parameter value. Values for this field should be drawn from SearchParameter.code for a parameter on the target resource type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getChain()
    {
        return $this->chain;
    }

    /**
     * Contains the names of any search parameters which may be chained to the containing search parameter. Chained parameters may be added to search parameters of type reference and specify that resources will only be returned if they contain a reference to a resource which matches the chained parameter value. Values for this field should be drawn from SearchParameter.code for a parameter on the target resource type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $chain
     * @return $this
     */
    public function addChain($chain)
    {
        $this->chain[] = $chain;
        return $this;
    }

    /**
     * Used to define the parts of a composite search parameter.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSearchParameter\FHIRSearchParameterComponent[]
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Used to define the parts of a composite search parameter.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSearchParameter\FHIRSearchParameterComponent $component
     * @return $this
     */
    public function addComponent($component)
    {
        $this->component[] = $component;
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
            if (isset($data['derivedFrom'])) {
                $this->setDerivedFrom($data['derivedFrom']);
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['base'])) {
                if (is_array($data['base'])) {
                    foreach ($data['base'] as $d) {
                        $this->addBase($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"base" must be array of objects or null, ' . gettype($data['base']) . ' seen.');
                }
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['expression'])) {
                $this->setExpression($data['expression']);
            }
            if (isset($data['xpath'])) {
                $this->setXpath($data['xpath']);
            }
            if (isset($data['xpathUsage'])) {
                $this->setXpathUsage($data['xpathUsage']);
            }
            if (isset($data['target'])) {
                if (is_array($data['target'])) {
                    foreach ($data['target'] as $d) {
                        $this->addTarget($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"target" must be array of objects or null, ' . gettype($data['target']) . ' seen.');
                }
            }
            if (isset($data['multipleOr'])) {
                $this->setMultipleOr($data['multipleOr']);
            }
            if (isset($data['multipleAnd'])) {
                $this->setMultipleAnd($data['multipleAnd']);
            }
            if (isset($data['comparator'])) {
                if (is_array($data['comparator'])) {
                    foreach ($data['comparator'] as $d) {
                        $this->addComparator($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"comparator" must be array of objects or null, ' . gettype($data['comparator']) . ' seen.');
                }
            }
            if (isset($data['modifier'])) {
                if (is_array($data['modifier'])) {
                    foreach ($data['modifier'] as $d) {
                        $this->addModifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"modifier" must be array of objects or null, ' . gettype($data['modifier']) . ' seen.');
                }
            }
            if (isset($data['chain'])) {
                if (is_array($data['chain'])) {
                    foreach ($data['chain'] as $d) {
                        $this->addChain($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"chain" must be array of objects or null, ' . gettype($data['chain']) . ' seen.');
                }
            }
            if (isset($data['component'])) {
                if (is_array($data['component'])) {
                    foreach ($data['component'] as $d) {
                        $this->addComponent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"component" must be array of objects or null, ' . gettype($data['component']) . ' seen.');
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
        if (isset($this->version)) {
            $json['version'] = $this->version;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->derivedFrom)) {
            $json['derivedFrom'] = $this->derivedFrom;
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (0 < count($this->base)) {
            $json['base'] = [];
            foreach ($this->base as $base) {
                $json['base'][] = $base;
            }
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->expression)) {
            $json['expression'] = $this->expression;
        }
        if (isset($this->xpath)) {
            $json['xpath'] = $this->xpath;
        }
        if (isset($this->xpathUsage)) {
            $json['xpathUsage'] = $this->xpathUsage;
        }
        if (0 < count($this->target)) {
            $json['target'] = [];
            foreach ($this->target as $target) {
                $json['target'][] = $target;
            }
        }
        if (isset($this->multipleOr)) {
            $json['multipleOr'] = $this->multipleOr;
        }
        if (isset($this->multipleAnd)) {
            $json['multipleAnd'] = $this->multipleAnd;
        }
        if (0 < count($this->comparator)) {
            $json['comparator'] = [];
            foreach ($this->comparator as $comparator) {
                $json['comparator'][] = $comparator;
            }
        }
        if (0 < count($this->modifier)) {
            $json['modifier'] = [];
            foreach ($this->modifier as $modifier) {
                $json['modifier'][] = $modifier;
            }
        }
        if (0 < count($this->chain)) {
            $json['chain'] = [];
            foreach ($this->chain as $chain) {
                $json['chain'][] = $chain;
            }
        }
        if (0 < count($this->component)) {
            $json['component'] = [];
            foreach ($this->component as $component) {
                $json['component'][] = $component;
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
            $sxe = new \SimpleXMLElement('<SearchParameter xmlns="http://hl7.org/fhir"></SearchParameter>');
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
        if (isset($this->derivedFrom)) {
            $this->derivedFrom->xmlSerialize(true, $sxe->addChild('derivedFrom'));
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
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (0 < count($this->base)) {
            foreach ($this->base as $base) {
                $base->xmlSerialize(true, $sxe->addChild('base'));
            }
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->expression)) {
            $this->expression->xmlSerialize(true, $sxe->addChild('expression'));
        }
        if (isset($this->xpath)) {
            $this->xpath->xmlSerialize(true, $sxe->addChild('xpath'));
        }
        if (isset($this->xpathUsage)) {
            $this->xpathUsage->xmlSerialize(true, $sxe->addChild('xpathUsage'));
        }
        if (0 < count($this->target)) {
            foreach ($this->target as $target) {
                $target->xmlSerialize(true, $sxe->addChild('target'));
            }
        }
        if (isset($this->multipleOr)) {
            $this->multipleOr->xmlSerialize(true, $sxe->addChild('multipleOr'));
        }
        if (isset($this->multipleAnd)) {
            $this->multipleAnd->xmlSerialize(true, $sxe->addChild('multipleAnd'));
        }
        if (0 < count($this->comparator)) {
            foreach ($this->comparator as $comparator) {
                $comparator->xmlSerialize(true, $sxe->addChild('comparator'));
            }
        }
        if (0 < count($this->modifier)) {
            foreach ($this->modifier as $modifier) {
                $modifier->xmlSerialize(true, $sxe->addChild('modifier'));
            }
        }
        if (0 < count($this->chain)) {
            foreach ($this->chain as $chain) {
                $chain->xmlSerialize(true, $sxe->addChild('chain'));
            }
        }
        if (0 < count($this->component)) {
            foreach ($this->component as $component) {
                $component->xmlSerialize(true, $sxe->addChild('component'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
