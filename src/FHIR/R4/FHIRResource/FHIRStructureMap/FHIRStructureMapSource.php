<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRStructureMap;

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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;

/**
 * A Map of relationships between 2 structures that can be used to transform data.
 */
class FHIRStructureMapSource extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Type or variable this rule applies to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $context = null;

    /**
     * Specified minimum cardinality for the element. This is optional; if present, it acts an implicit check on the input content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $min = null;

    /**
     * Specified maximum cardinality for the element - a number or a "*". This is optional; if present, it acts an implicit check on the input content (* just serves as documentation; it's the default value).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $max = null;

    /**
     * Specified type for the element. This works as a condition on the mapping - use for polymorphic elements.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $type = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary
     */
    public $defaultValueBase64Binary = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $defaultValueBoolean = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $defaultValueCanonical = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $defaultValueCode = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $defaultValueDate = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $defaultValueDateTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $defaultValueDecimal = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $defaultValueId = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public $defaultValueInstant = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $defaultValueInteger = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $defaultValueMarkdown = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIROid
     */
    public $defaultValueOid = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public $defaultValuePositiveInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $defaultValueString = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public $defaultValueTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $defaultValueUnsignedInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $defaultValueUri = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl
     */
    public $defaultValueUrl = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUuid
     */
    public $defaultValueUuid = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress
     */
    public $defaultValueAddress = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge
     */
    public $defaultValueAge = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation
     */
    public $defaultValueAnnotation = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public $defaultValueAttachment = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $defaultValueCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $defaultValueCoding = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint
     */
    public $defaultValueContactPoint = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRCount
     */
    public $defaultValueCount = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDistance
     */
    public $defaultValueDistance = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $defaultValueDuration = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName
     */
    public $defaultValueHumanName = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $defaultValueIdentifier = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $defaultValueMoney = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $defaultValuePeriod = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $defaultValueQuantity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $defaultValueRange = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public $defaultValueRatio = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $defaultValueReference = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSampledData
     */
    public $defaultValueSampledData = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature
     */
    public $defaultValueSignature = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public $defaultValueTiming = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail
     */
    public $defaultValueContactDetail = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContributor
     */
    public $defaultValueContributor = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement
     */
    public $defaultValueDataRequirement = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRExpression
     */
    public $defaultValueExpression = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRParameterDefinition
     */
    public $defaultValueParameterDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact
     */
    public $defaultValueRelatedArtifact = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTriggerDefinition
     */
    public $defaultValueTriggerDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext
     */
    public $defaultValueUsageContext = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage
     */
    public $defaultValueDosage = null;

    /**
     * Optional field for this source.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $element = null;

    /**
     * How to handle the list mode for this element.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapSourceListMode
     */
    public $listMode = null;

    /**
     * Named context for field, if a field is specified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $variable = null;

    /**
     * FHIRPath expression  - must be true or the rule does not apply.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $condition = null;

    /**
     * FHIRPath expression  - must be true or the mapping engine throws an error instead of completing.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $check = null;

    /**
     * A FHIRPath expression which specifies a message to put in the transform log when content matching the source rule is found.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $logMessage = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'StructureMap.Source';

    /**
     * Type or variable this rule applies to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Type or variable this rule applies to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Specified minimum cardinality for the element. This is optional; if present, it acts an implicit check on the input content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Specified minimum cardinality for the element. This is optional; if present, it acts an implicit check on the input content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $min
     * @return $this
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Specified maximum cardinality for the element - a number or a "*". This is optional; if present, it acts an implicit check on the input content (* just serves as documentation; it's the default value).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Specified maximum cardinality for the element - a number or a "*". This is optional; if present, it acts an implicit check on the input content (* just serves as documentation; it's the default value).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $max
     * @return $this
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Specified type for the element. This works as a condition on the mapping - use for polymorphic elements.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Specified type for the element. This works as a condition on the mapping - use for polymorphic elements.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary
     */
    public function getDefaultValueBase64Binary()
    {
        return $this->defaultValueBase64Binary;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary $defaultValueBase64Binary
     * @return $this
     */
    public function setDefaultValueBase64Binary($defaultValueBase64Binary)
    {
        $this->defaultValueBase64Binary = $defaultValueBase64Binary;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getDefaultValueBoolean()
    {
        return $this->defaultValueBoolean;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $defaultValueBoolean
     * @return $this
     */
    public function setDefaultValueBoolean($defaultValueBoolean)
    {
        $this->defaultValueBoolean = $defaultValueBoolean;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getDefaultValueCanonical()
    {
        return $this->defaultValueCanonical;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $defaultValueCanonical
     * @return $this
     */
    public function setDefaultValueCanonical($defaultValueCanonical)
    {
        $this->defaultValueCanonical = $defaultValueCanonical;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getDefaultValueCode()
    {
        return $this->defaultValueCode;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $defaultValueCode
     * @return $this
     */
    public function setDefaultValueCode($defaultValueCode)
    {
        $this->defaultValueCode = $defaultValueCode;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getDefaultValueDate()
    {
        return $this->defaultValueDate;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $defaultValueDate
     * @return $this
     */
    public function setDefaultValueDate($defaultValueDate)
    {
        $this->defaultValueDate = $defaultValueDate;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDefaultValueDateTime()
    {
        return $this->defaultValueDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $defaultValueDateTime
     * @return $this
     */
    public function setDefaultValueDateTime($defaultValueDateTime)
    {
        $this->defaultValueDateTime = $defaultValueDateTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getDefaultValueDecimal()
    {
        return $this->defaultValueDecimal;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $defaultValueDecimal
     * @return $this
     */
    public function setDefaultValueDecimal($defaultValueDecimal)
    {
        $this->defaultValueDecimal = $defaultValueDecimal;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getDefaultValueId()
    {
        return $this->defaultValueId;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $defaultValueId
     * @return $this
     */
    public function setDefaultValueId($defaultValueId)
    {
        $this->defaultValueId = $defaultValueId;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public function getDefaultValueInstant()
    {
        return $this->defaultValueInstant;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant $defaultValueInstant
     * @return $this
     */
    public function setDefaultValueInstant($defaultValueInstant)
    {
        $this->defaultValueInstant = $defaultValueInstant;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getDefaultValueInteger()
    {
        return $this->defaultValueInteger;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $defaultValueInteger
     * @return $this
     */
    public function setDefaultValueInteger($defaultValueInteger)
    {
        $this->defaultValueInteger = $defaultValueInteger;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDefaultValueMarkdown()
    {
        return $this->defaultValueMarkdown;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $defaultValueMarkdown
     * @return $this
     */
    public function setDefaultValueMarkdown($defaultValueMarkdown)
    {
        $this->defaultValueMarkdown = $defaultValueMarkdown;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIROid
     */
    public function getDefaultValueOid()
    {
        return $this->defaultValueOid;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIROid $defaultValueOid
     * @return $this
     */
    public function setDefaultValueOid($defaultValueOid)
    {
        $this->defaultValueOid = $defaultValueOid;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public function getDefaultValuePositiveInt()
    {
        return $this->defaultValuePositiveInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $defaultValuePositiveInt
     * @return $this
     */
    public function setDefaultValuePositiveInt($defaultValuePositiveInt)
    {
        $this->defaultValuePositiveInt = $defaultValuePositiveInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDefaultValueString()
    {
        return $this->defaultValueString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $defaultValueString
     * @return $this
     */
    public function setDefaultValueString($defaultValueString)
    {
        $this->defaultValueString = $defaultValueString;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public function getDefaultValueTime()
    {
        return $this->defaultValueTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTime $defaultValueTime
     * @return $this
     */
    public function setDefaultValueTime($defaultValueTime)
    {
        $this->defaultValueTime = $defaultValueTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getDefaultValueUnsignedInt()
    {
        return $this->defaultValueUnsignedInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $defaultValueUnsignedInt
     * @return $this
     */
    public function setDefaultValueUnsignedInt($defaultValueUnsignedInt)
    {
        $this->defaultValueUnsignedInt = $defaultValueUnsignedInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getDefaultValueUri()
    {
        return $this->defaultValueUri;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $defaultValueUri
     * @return $this
     */
    public function setDefaultValueUri($defaultValueUri)
    {
        $this->defaultValueUri = $defaultValueUri;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl
     */
    public function getDefaultValueUrl()
    {
        return $this->defaultValueUrl;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl $defaultValueUrl
     * @return $this
     */
    public function setDefaultValueUrl($defaultValueUrl)
    {
        $this->defaultValueUrl = $defaultValueUrl;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUuid
     */
    public function getDefaultValueUuid()
    {
        return $this->defaultValueUuid;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUuid $defaultValueUuid
     * @return $this
     */
    public function setDefaultValueUuid($defaultValueUuid)
    {
        $this->defaultValueUuid = $defaultValueUuid;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress
     */
    public function getDefaultValueAddress()
    {
        return $this->defaultValueAddress;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress $defaultValueAddress
     * @return $this
     */
    public function setDefaultValueAddress($defaultValueAddress)
    {
        $this->defaultValueAddress = $defaultValueAddress;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge
     */
    public function getDefaultValueAge()
    {
        return $this->defaultValueAge;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge $defaultValueAge
     * @return $this
     */
    public function setDefaultValueAge($defaultValueAge)
    {
        $this->defaultValueAge = $defaultValueAge;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation
     */
    public function getDefaultValueAnnotation()
    {
        return $this->defaultValueAnnotation;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $defaultValueAnnotation
     * @return $this
     */
    public function setDefaultValueAnnotation($defaultValueAnnotation)
    {
        $this->defaultValueAnnotation = $defaultValueAnnotation;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public function getDefaultValueAttachment()
    {
        return $this->defaultValueAttachment;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment $defaultValueAttachment
     * @return $this
     */
    public function setDefaultValueAttachment($defaultValueAttachment)
    {
        $this->defaultValueAttachment = $defaultValueAttachment;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDefaultValueCodeableConcept()
    {
        return $this->defaultValueCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $defaultValueCodeableConcept
     * @return $this
     */
    public function setDefaultValueCodeableConcept($defaultValueCodeableConcept)
    {
        $this->defaultValueCodeableConcept = $defaultValueCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getDefaultValueCoding()
    {
        return $this->defaultValueCoding;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $defaultValueCoding
     * @return $this
     */
    public function setDefaultValueCoding($defaultValueCoding)
    {
        $this->defaultValueCoding = $defaultValueCoding;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint
     */
    public function getDefaultValueContactPoint()
    {
        return $this->defaultValueContactPoint;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint $defaultValueContactPoint
     * @return $this
     */
    public function setDefaultValueContactPoint($defaultValueContactPoint)
    {
        $this->defaultValueContactPoint = $defaultValueContactPoint;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRCount
     */
    public function getDefaultValueCount()
    {
        return $this->defaultValueCount;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRCount $defaultValueCount
     * @return $this
     */
    public function setDefaultValueCount($defaultValueCount)
    {
        $this->defaultValueCount = $defaultValueCount;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDistance
     */
    public function getDefaultValueDistance()
    {
        return $this->defaultValueDistance;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDistance $defaultValueDistance
     * @return $this
     */
    public function setDefaultValueDistance($defaultValueDistance)
    {
        $this->defaultValueDistance = $defaultValueDistance;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getDefaultValueDuration()
    {
        return $this->defaultValueDuration;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $defaultValueDuration
     * @return $this
     */
    public function setDefaultValueDuration($defaultValueDuration)
    {
        $this->defaultValueDuration = $defaultValueDuration;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName
     */
    public function getDefaultValueHumanName()
    {
        return $this->defaultValueHumanName;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName $defaultValueHumanName
     * @return $this
     */
    public function setDefaultValueHumanName($defaultValueHumanName)
    {
        $this->defaultValueHumanName = $defaultValueHumanName;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getDefaultValueIdentifier()
    {
        return $this->defaultValueIdentifier;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $defaultValueIdentifier
     * @return $this
     */
    public function setDefaultValueIdentifier($defaultValueIdentifier)
    {
        $this->defaultValueIdentifier = $defaultValueIdentifier;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getDefaultValueMoney()
    {
        return $this->defaultValueMoney;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $defaultValueMoney
     * @return $this
     */
    public function setDefaultValueMoney($defaultValueMoney)
    {
        $this->defaultValueMoney = $defaultValueMoney;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getDefaultValuePeriod()
    {
        return $this->defaultValuePeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $defaultValuePeriod
     * @return $this
     */
    public function setDefaultValuePeriod($defaultValuePeriod)
    {
        $this->defaultValuePeriod = $defaultValuePeriod;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getDefaultValueQuantity()
    {
        return $this->defaultValueQuantity;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $defaultValueQuantity
     * @return $this
     */
    public function setDefaultValueQuantity($defaultValueQuantity)
    {
        $this->defaultValueQuantity = $defaultValueQuantity;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getDefaultValueRange()
    {
        return $this->defaultValueRange;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $defaultValueRange
     * @return $this
     */
    public function setDefaultValueRange($defaultValueRange)
    {
        $this->defaultValueRange = $defaultValueRange;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getDefaultValueRatio()
    {
        return $this->defaultValueRatio;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $defaultValueRatio
     * @return $this
     */
    public function setDefaultValueRatio($defaultValueRatio)
    {
        $this->defaultValueRatio = $defaultValueRatio;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getDefaultValueReference()
    {
        return $this->defaultValueReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $defaultValueReference
     * @return $this
     */
    public function setDefaultValueReference($defaultValueReference)
    {
        $this->defaultValueReference = $defaultValueReference;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSampledData
     */
    public function getDefaultValueSampledData()
    {
        return $this->defaultValueSampledData;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSampledData $defaultValueSampledData
     * @return $this
     */
    public function setDefaultValueSampledData($defaultValueSampledData)
    {
        $this->defaultValueSampledData = $defaultValueSampledData;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature
     */
    public function getDefaultValueSignature()
    {
        return $this->defaultValueSignature;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature $defaultValueSignature
     * @return $this
     */
    public function setDefaultValueSignature($defaultValueSignature)
    {
        $this->defaultValueSignature = $defaultValueSignature;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public function getDefaultValueTiming()
    {
        return $this->defaultValueTiming;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming $defaultValueTiming
     * @return $this
     */
    public function setDefaultValueTiming($defaultValueTiming)
    {
        $this->defaultValueTiming = $defaultValueTiming;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail
     */
    public function getDefaultValueContactDetail()
    {
        return $this->defaultValueContactDetail;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail $defaultValueContactDetail
     * @return $this
     */
    public function setDefaultValueContactDetail($defaultValueContactDetail)
    {
        $this->defaultValueContactDetail = $defaultValueContactDetail;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContributor
     */
    public function getDefaultValueContributor()
    {
        return $this->defaultValueContributor;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContributor $defaultValueContributor
     * @return $this
     */
    public function setDefaultValueContributor($defaultValueContributor)
    {
        $this->defaultValueContributor = $defaultValueContributor;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement
     */
    public function getDefaultValueDataRequirement()
    {
        return $this->defaultValueDataRequirement;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement $defaultValueDataRequirement
     * @return $this
     */
    public function setDefaultValueDataRequirement($defaultValueDataRequirement)
    {
        $this->defaultValueDataRequirement = $defaultValueDataRequirement;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRExpression
     */
    public function getDefaultValueExpression()
    {
        return $this->defaultValueExpression;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRExpression $defaultValueExpression
     * @return $this
     */
    public function setDefaultValueExpression($defaultValueExpression)
    {
        $this->defaultValueExpression = $defaultValueExpression;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRParameterDefinition
     */
    public function getDefaultValueParameterDefinition()
    {
        return $this->defaultValueParameterDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRParameterDefinition $defaultValueParameterDefinition
     * @return $this
     */
    public function setDefaultValueParameterDefinition($defaultValueParameterDefinition)
    {
        $this->defaultValueParameterDefinition = $defaultValueParameterDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact
     */
    public function getDefaultValueRelatedArtifact()
    {
        return $this->defaultValueRelatedArtifact;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact $defaultValueRelatedArtifact
     * @return $this
     */
    public function setDefaultValueRelatedArtifact($defaultValueRelatedArtifact)
    {
        $this->defaultValueRelatedArtifact = $defaultValueRelatedArtifact;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTriggerDefinition
     */
    public function getDefaultValueTriggerDefinition()
    {
        return $this->defaultValueTriggerDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTriggerDefinition $defaultValueTriggerDefinition
     * @return $this
     */
    public function setDefaultValueTriggerDefinition($defaultValueTriggerDefinition)
    {
        $this->defaultValueTriggerDefinition = $defaultValueTriggerDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext
     */
    public function getDefaultValueUsageContext()
    {
        return $this->defaultValueUsageContext;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext $defaultValueUsageContext
     * @return $this
     */
    public function setDefaultValueUsageContext($defaultValueUsageContext)
    {
        $this->defaultValueUsageContext = $defaultValueUsageContext;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage
     */
    public function getDefaultValueDosage()
    {
        return $this->defaultValueDosage;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage $defaultValueDosage
     * @return $this
     */
    public function setDefaultValueDosage($defaultValueDosage)
    {
        $this->defaultValueDosage = $defaultValueDosage;
        return $this;
    }

    /**
     * Optional field for this source.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Optional field for this source.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $element
     * @return $this
     */
    public function setElement($element)
    {
        $this->element = $element;
        return $this;
    }

    /**
     * How to handle the list mode for this element.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapSourceListMode
     */
    public function getListMode()
    {
        return $this->listMode;
    }

    /**
     * How to handle the list mode for this element.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapSourceListMode $listMode
     * @return $this
     */
    public function setListMode($listMode)
    {
        $this->listMode = $listMode;
        return $this;
    }

    /**
     * Named context for field, if a field is specified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * Named context for field, if a field is specified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $variable
     * @return $this
     */
    public function setVariable($variable)
    {
        $this->variable = $variable;
        return $this;
    }

    /**
     * FHIRPath expression  - must be true or the rule does not apply.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * FHIRPath expression  - must be true or the rule does not apply.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $condition
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * FHIRPath expression  - must be true or the mapping engine throws an error instead of completing.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCheck()
    {
        return $this->check;
    }

    /**
     * FHIRPath expression  - must be true or the mapping engine throws an error instead of completing.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $check
     * @return $this
     */
    public function setCheck($check)
    {
        $this->check = $check;
        return $this;
    }

    /**
     * A FHIRPath expression which specifies a message to put in the transform log when content matching the source rule is found.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getLogMessage()
    {
        return $this->logMessage;
    }

    /**
     * A FHIRPath expression which specifies a message to put in the transform log when content matching the source rule is found.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $logMessage
     * @return $this
     */
    public function setLogMessage($logMessage)
    {
        $this->logMessage = $logMessage;
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
            if (isset($data['context'])) {
                $this->setContext($data['context']);
            }
            if (isset($data['min'])) {
                $this->setMin($data['min']);
            }
            if (isset($data['max'])) {
                $this->setMax($data['max']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['defaultValueBase64Binary'])) {
                $this->setDefaultValueBase64Binary($data['defaultValueBase64Binary']);
            }
            if (isset($data['defaultValueBoolean'])) {
                $this->setDefaultValueBoolean($data['defaultValueBoolean']);
            }
            if (isset($data['defaultValueCanonical'])) {
                $this->setDefaultValueCanonical($data['defaultValueCanonical']);
            }
            if (isset($data['defaultValueCode'])) {
                $this->setDefaultValueCode($data['defaultValueCode']);
            }
            if (isset($data['defaultValueDate'])) {
                $this->setDefaultValueDate($data['defaultValueDate']);
            }
            if (isset($data['defaultValueDateTime'])) {
                $this->setDefaultValueDateTime($data['defaultValueDateTime']);
            }
            if (isset($data['defaultValueDecimal'])) {
                $this->setDefaultValueDecimal($data['defaultValueDecimal']);
            }
            if (isset($data['defaultValueId'])) {
                $this->setDefaultValueId($data['defaultValueId']);
            }
            if (isset($data['defaultValueInstant'])) {
                $this->setDefaultValueInstant($data['defaultValueInstant']);
            }
            if (isset($data['defaultValueInteger'])) {
                $this->setDefaultValueInteger($data['defaultValueInteger']);
            }
            if (isset($data['defaultValueMarkdown'])) {
                $this->setDefaultValueMarkdown($data['defaultValueMarkdown']);
            }
            if (isset($data['defaultValueOid'])) {
                $this->setDefaultValueOid($data['defaultValueOid']);
            }
            if (isset($data['defaultValuePositiveInt'])) {
                $this->setDefaultValuePositiveInt($data['defaultValuePositiveInt']);
            }
            if (isset($data['defaultValueString'])) {
                $this->setDefaultValueString($data['defaultValueString']);
            }
            if (isset($data['defaultValueTime'])) {
                $this->setDefaultValueTime($data['defaultValueTime']);
            }
            if (isset($data['defaultValueUnsignedInt'])) {
                $this->setDefaultValueUnsignedInt($data['defaultValueUnsignedInt']);
            }
            if (isset($data['defaultValueUri'])) {
                $this->setDefaultValueUri($data['defaultValueUri']);
            }
            if (isset($data['defaultValueUrl'])) {
                $this->setDefaultValueUrl($data['defaultValueUrl']);
            }
            if (isset($data['defaultValueUuid'])) {
                $this->setDefaultValueUuid($data['defaultValueUuid']);
            }
            if (isset($data['defaultValueAddress'])) {
                $this->setDefaultValueAddress($data['defaultValueAddress']);
            }
            if (isset($data['defaultValueAge'])) {
                $this->setDefaultValueAge($data['defaultValueAge']);
            }
            if (isset($data['defaultValueAnnotation'])) {
                $this->setDefaultValueAnnotation($data['defaultValueAnnotation']);
            }
            if (isset($data['defaultValueAttachment'])) {
                $this->setDefaultValueAttachment($data['defaultValueAttachment']);
            }
            if (isset($data['defaultValueCodeableConcept'])) {
                $this->setDefaultValueCodeableConcept($data['defaultValueCodeableConcept']);
            }
            if (isset($data['defaultValueCoding'])) {
                $this->setDefaultValueCoding($data['defaultValueCoding']);
            }
            if (isset($data['defaultValueContactPoint'])) {
                $this->setDefaultValueContactPoint($data['defaultValueContactPoint']);
            }
            if (isset($data['defaultValueCount'])) {
                $this->setDefaultValueCount($data['defaultValueCount']);
            }
            if (isset($data['defaultValueDistance'])) {
                $this->setDefaultValueDistance($data['defaultValueDistance']);
            }
            if (isset($data['defaultValueDuration'])) {
                $this->setDefaultValueDuration($data['defaultValueDuration']);
            }
            if (isset($data['defaultValueHumanName'])) {
                $this->setDefaultValueHumanName($data['defaultValueHumanName']);
            }
            if (isset($data['defaultValueIdentifier'])) {
                $this->setDefaultValueIdentifier($data['defaultValueIdentifier']);
            }
            if (isset($data['defaultValueMoney'])) {
                $this->setDefaultValueMoney($data['defaultValueMoney']);
            }
            if (isset($data['defaultValuePeriod'])) {
                $this->setDefaultValuePeriod($data['defaultValuePeriod']);
            }
            if (isset($data['defaultValueQuantity'])) {
                $this->setDefaultValueQuantity($data['defaultValueQuantity']);
            }
            if (isset($data['defaultValueRange'])) {
                $this->setDefaultValueRange($data['defaultValueRange']);
            }
            if (isset($data['defaultValueRatio'])) {
                $this->setDefaultValueRatio($data['defaultValueRatio']);
            }
            if (isset($data['defaultValueReference'])) {
                $this->setDefaultValueReference($data['defaultValueReference']);
            }
            if (isset($data['defaultValueSampledData'])) {
                $this->setDefaultValueSampledData($data['defaultValueSampledData']);
            }
            if (isset($data['defaultValueSignature'])) {
                $this->setDefaultValueSignature($data['defaultValueSignature']);
            }
            if (isset($data['defaultValueTiming'])) {
                $this->setDefaultValueTiming($data['defaultValueTiming']);
            }
            if (isset($data['defaultValueContactDetail'])) {
                $this->setDefaultValueContactDetail($data['defaultValueContactDetail']);
            }
            if (isset($data['defaultValueContributor'])) {
                $this->setDefaultValueContributor($data['defaultValueContributor']);
            }
            if (isset($data['defaultValueDataRequirement'])) {
                $this->setDefaultValueDataRequirement($data['defaultValueDataRequirement']);
            }
            if (isset($data['defaultValueExpression'])) {
                $this->setDefaultValueExpression($data['defaultValueExpression']);
            }
            if (isset($data['defaultValueParameterDefinition'])) {
                $this->setDefaultValueParameterDefinition($data['defaultValueParameterDefinition']);
            }
            if (isset($data['defaultValueRelatedArtifact'])) {
                $this->setDefaultValueRelatedArtifact($data['defaultValueRelatedArtifact']);
            }
            if (isset($data['defaultValueTriggerDefinition'])) {
                $this->setDefaultValueTriggerDefinition($data['defaultValueTriggerDefinition']);
            }
            if (isset($data['defaultValueUsageContext'])) {
                $this->setDefaultValueUsageContext($data['defaultValueUsageContext']);
            }
            if (isset($data['defaultValueDosage'])) {
                $this->setDefaultValueDosage($data['defaultValueDosage']);
            }
            if (isset($data['element'])) {
                $this->setElement($data['element']);
            }
            if (isset($data['listMode'])) {
                $this->setListMode($data['listMode']);
            }
            if (isset($data['variable'])) {
                $this->setVariable($data['variable']);
            }
            if (isset($data['condition'])) {
                $this->setCondition($data['condition']);
            }
            if (isset($data['check'])) {
                $this->setCheck($data['check']);
            }
            if (isset($data['logMessage'])) {
                $this->setLogMessage($data['logMessage']);
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
        if (isset($this->context)) {
            $json['context'] = $this->context;
        }
        if (isset($this->min)) {
            $json['min'] = $this->min;
        }
        if (isset($this->max)) {
            $json['max'] = $this->max;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->defaultValueBase64Binary)) {
            $json['defaultValueBase64Binary'] = $this->defaultValueBase64Binary;
        }
        if (isset($this->defaultValueBoolean)) {
            $json['defaultValueBoolean'] = $this->defaultValueBoolean;
        }
        if (isset($this->defaultValueCanonical)) {
            $json['defaultValueCanonical'] = $this->defaultValueCanonical;
        }
        if (isset($this->defaultValueCode)) {
            $json['defaultValueCode'] = $this->defaultValueCode;
        }
        if (isset($this->defaultValueDate)) {
            $json['defaultValueDate'] = $this->defaultValueDate;
        }
        if (isset($this->defaultValueDateTime)) {
            $json['defaultValueDateTime'] = $this->defaultValueDateTime;
        }
        if (isset($this->defaultValueDecimal)) {
            $json['defaultValueDecimal'] = $this->defaultValueDecimal;
        }
        if (isset($this->defaultValueId)) {
            $json['defaultValueId'] = $this->defaultValueId;
        }
        if (isset($this->defaultValueInstant)) {
            $json['defaultValueInstant'] = $this->defaultValueInstant;
        }
        if (isset($this->defaultValueInteger)) {
            $json['defaultValueInteger'] = $this->defaultValueInteger;
        }
        if (isset($this->defaultValueMarkdown)) {
            $json['defaultValueMarkdown'] = $this->defaultValueMarkdown;
        }
        if (isset($this->defaultValueOid)) {
            $json['defaultValueOid'] = $this->defaultValueOid;
        }
        if (isset($this->defaultValuePositiveInt)) {
            $json['defaultValuePositiveInt'] = $this->defaultValuePositiveInt;
        }
        if (isset($this->defaultValueString)) {
            $json['defaultValueString'] = $this->defaultValueString;
        }
        if (isset($this->defaultValueTime)) {
            $json['defaultValueTime'] = $this->defaultValueTime;
        }
        if (isset($this->defaultValueUnsignedInt)) {
            $json['defaultValueUnsignedInt'] = $this->defaultValueUnsignedInt;
        }
        if (isset($this->defaultValueUri)) {
            $json['defaultValueUri'] = $this->defaultValueUri;
        }
        if (isset($this->defaultValueUrl)) {
            $json['defaultValueUrl'] = $this->defaultValueUrl;
        }
        if (isset($this->defaultValueUuid)) {
            $json['defaultValueUuid'] = $this->defaultValueUuid;
        }
        if (isset($this->defaultValueAddress)) {
            $json['defaultValueAddress'] = $this->defaultValueAddress;
        }
        if (isset($this->defaultValueAge)) {
            $json['defaultValueAge'] = $this->defaultValueAge;
        }
        if (isset($this->defaultValueAnnotation)) {
            $json['defaultValueAnnotation'] = $this->defaultValueAnnotation;
        }
        if (isset($this->defaultValueAttachment)) {
            $json['defaultValueAttachment'] = $this->defaultValueAttachment;
        }
        if (isset($this->defaultValueCodeableConcept)) {
            $json['defaultValueCodeableConcept'] = $this->defaultValueCodeableConcept;
        }
        if (isset($this->defaultValueCoding)) {
            $json['defaultValueCoding'] = $this->defaultValueCoding;
        }
        if (isset($this->defaultValueContactPoint)) {
            $json['defaultValueContactPoint'] = $this->defaultValueContactPoint;
        }
        if (isset($this->defaultValueCount)) {
            $json['defaultValueCount'] = $this->defaultValueCount;
        }
        if (isset($this->defaultValueDistance)) {
            $json['defaultValueDistance'] = $this->defaultValueDistance;
        }
        if (isset($this->defaultValueDuration)) {
            $json['defaultValueDuration'] = $this->defaultValueDuration;
        }
        if (isset($this->defaultValueHumanName)) {
            $json['defaultValueHumanName'] = $this->defaultValueHumanName;
        }
        if (isset($this->defaultValueIdentifier)) {
            $json['defaultValueIdentifier'] = $this->defaultValueIdentifier;
        }
        if (isset($this->defaultValueMoney)) {
            $json['defaultValueMoney'] = $this->defaultValueMoney;
        }
        if (isset($this->defaultValuePeriod)) {
            $json['defaultValuePeriod'] = $this->defaultValuePeriod;
        }
        if (isset($this->defaultValueQuantity)) {
            $json['defaultValueQuantity'] = $this->defaultValueQuantity;
        }
        if (isset($this->defaultValueRange)) {
            $json['defaultValueRange'] = $this->defaultValueRange;
        }
        if (isset($this->defaultValueRatio)) {
            $json['defaultValueRatio'] = $this->defaultValueRatio;
        }
        if (isset($this->defaultValueReference)) {
            $json['defaultValueReference'] = $this->defaultValueReference;
        }
        if (isset($this->defaultValueSampledData)) {
            $json['defaultValueSampledData'] = $this->defaultValueSampledData;
        }
        if (isset($this->defaultValueSignature)) {
            $json['defaultValueSignature'] = $this->defaultValueSignature;
        }
        if (isset($this->defaultValueTiming)) {
            $json['defaultValueTiming'] = $this->defaultValueTiming;
        }
        if (isset($this->defaultValueContactDetail)) {
            $json['defaultValueContactDetail'] = $this->defaultValueContactDetail;
        }
        if (isset($this->defaultValueContributor)) {
            $json['defaultValueContributor'] = $this->defaultValueContributor;
        }
        if (isset($this->defaultValueDataRequirement)) {
            $json['defaultValueDataRequirement'] = $this->defaultValueDataRequirement;
        }
        if (isset($this->defaultValueExpression)) {
            $json['defaultValueExpression'] = $this->defaultValueExpression;
        }
        if (isset($this->defaultValueParameterDefinition)) {
            $json['defaultValueParameterDefinition'] = $this->defaultValueParameterDefinition;
        }
        if (isset($this->defaultValueRelatedArtifact)) {
            $json['defaultValueRelatedArtifact'] = $this->defaultValueRelatedArtifact;
        }
        if (isset($this->defaultValueTriggerDefinition)) {
            $json['defaultValueTriggerDefinition'] = $this->defaultValueTriggerDefinition;
        }
        if (isset($this->defaultValueUsageContext)) {
            $json['defaultValueUsageContext'] = $this->defaultValueUsageContext;
        }
        if (isset($this->defaultValueDosage)) {
            $json['defaultValueDosage'] = $this->defaultValueDosage;
        }
        if (isset($this->element)) {
            $json['element'] = $this->element;
        }
        if (isset($this->listMode)) {
            $json['listMode'] = $this->listMode;
        }
        if (isset($this->variable)) {
            $json['variable'] = $this->variable;
        }
        if (isset($this->condition)) {
            $json['condition'] = $this->condition;
        }
        if (isset($this->check)) {
            $json['check'] = $this->check;
        }
        if (isset($this->logMessage)) {
            $json['logMessage'] = $this->logMessage;
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
            $sxe = new \SimpleXMLElement('<StructureMapSource xmlns="http://hl7.org/fhir"></StructureMapSource>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->context)) {
            $this->context->xmlSerialize(true, $sxe->addChild('context'));
        }
        if (isset($this->min)) {
            $this->min->xmlSerialize(true, $sxe->addChild('min'));
        }
        if (isset($this->max)) {
            $this->max->xmlSerialize(true, $sxe->addChild('max'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->defaultValueBase64Binary)) {
            $this->defaultValueBase64Binary->xmlSerialize(true, $sxe->addChild('defaultValueBase64Binary'));
        }
        if (isset($this->defaultValueBoolean)) {
            $this->defaultValueBoolean->xmlSerialize(true, $sxe->addChild('defaultValueBoolean'));
        }
        if (isset($this->defaultValueCanonical)) {
            $this->defaultValueCanonical->xmlSerialize(true, $sxe->addChild('defaultValueCanonical'));
        }
        if (isset($this->defaultValueCode)) {
            $this->defaultValueCode->xmlSerialize(true, $sxe->addChild('defaultValueCode'));
        }
        if (isset($this->defaultValueDate)) {
            $this->defaultValueDate->xmlSerialize(true, $sxe->addChild('defaultValueDate'));
        }
        if (isset($this->defaultValueDateTime)) {
            $this->defaultValueDateTime->xmlSerialize(true, $sxe->addChild('defaultValueDateTime'));
        }
        if (isset($this->defaultValueDecimal)) {
            $this->defaultValueDecimal->xmlSerialize(true, $sxe->addChild('defaultValueDecimal'));
        }
        if (isset($this->defaultValueId)) {
            $this->defaultValueId->xmlSerialize(true, $sxe->addChild('defaultValueId'));
        }
        if (isset($this->defaultValueInstant)) {
            $this->defaultValueInstant->xmlSerialize(true, $sxe->addChild('defaultValueInstant'));
        }
        if (isset($this->defaultValueInteger)) {
            $this->defaultValueInteger->xmlSerialize(true, $sxe->addChild('defaultValueInteger'));
        }
        if (isset($this->defaultValueMarkdown)) {
            $this->defaultValueMarkdown->xmlSerialize(true, $sxe->addChild('defaultValueMarkdown'));
        }
        if (isset($this->defaultValueOid)) {
            $this->defaultValueOid->xmlSerialize(true, $sxe->addChild('defaultValueOid'));
        }
        if (isset($this->defaultValuePositiveInt)) {
            $this->defaultValuePositiveInt->xmlSerialize(true, $sxe->addChild('defaultValuePositiveInt'));
        }
        if (isset($this->defaultValueString)) {
            $this->defaultValueString->xmlSerialize(true, $sxe->addChild('defaultValueString'));
        }
        if (isset($this->defaultValueTime)) {
            $this->defaultValueTime->xmlSerialize(true, $sxe->addChild('defaultValueTime'));
        }
        if (isset($this->defaultValueUnsignedInt)) {
            $this->defaultValueUnsignedInt->xmlSerialize(true, $sxe->addChild('defaultValueUnsignedInt'));
        }
        if (isset($this->defaultValueUri)) {
            $this->defaultValueUri->xmlSerialize(true, $sxe->addChild('defaultValueUri'));
        }
        if (isset($this->defaultValueUrl)) {
            $this->defaultValueUrl->xmlSerialize(true, $sxe->addChild('defaultValueUrl'));
        }
        if (isset($this->defaultValueUuid)) {
            $this->defaultValueUuid->xmlSerialize(true, $sxe->addChild('defaultValueUuid'));
        }
        if (isset($this->defaultValueAddress)) {
            $this->defaultValueAddress->xmlSerialize(true, $sxe->addChild('defaultValueAddress'));
        }
        if (isset($this->defaultValueAge)) {
            $this->defaultValueAge->xmlSerialize(true, $sxe->addChild('defaultValueAge'));
        }
        if (isset($this->defaultValueAnnotation)) {
            $this->defaultValueAnnotation->xmlSerialize(true, $sxe->addChild('defaultValueAnnotation'));
        }
        if (isset($this->defaultValueAttachment)) {
            $this->defaultValueAttachment->xmlSerialize(true, $sxe->addChild('defaultValueAttachment'));
        }
        if (isset($this->defaultValueCodeableConcept)) {
            $this->defaultValueCodeableConcept->xmlSerialize(true, $sxe->addChild('defaultValueCodeableConcept'));
        }
        if (isset($this->defaultValueCoding)) {
            $this->defaultValueCoding->xmlSerialize(true, $sxe->addChild('defaultValueCoding'));
        }
        if (isset($this->defaultValueContactPoint)) {
            $this->defaultValueContactPoint->xmlSerialize(true, $sxe->addChild('defaultValueContactPoint'));
        }
        if (isset($this->defaultValueCount)) {
            $this->defaultValueCount->xmlSerialize(true, $sxe->addChild('defaultValueCount'));
        }
        if (isset($this->defaultValueDistance)) {
            $this->defaultValueDistance->xmlSerialize(true, $sxe->addChild('defaultValueDistance'));
        }
        if (isset($this->defaultValueDuration)) {
            $this->defaultValueDuration->xmlSerialize(true, $sxe->addChild('defaultValueDuration'));
        }
        if (isset($this->defaultValueHumanName)) {
            $this->defaultValueHumanName->xmlSerialize(true, $sxe->addChild('defaultValueHumanName'));
        }
        if (isset($this->defaultValueIdentifier)) {
            $this->defaultValueIdentifier->xmlSerialize(true, $sxe->addChild('defaultValueIdentifier'));
        }
        if (isset($this->defaultValueMoney)) {
            $this->defaultValueMoney->xmlSerialize(true, $sxe->addChild('defaultValueMoney'));
        }
        if (isset($this->defaultValuePeriod)) {
            $this->defaultValuePeriod->xmlSerialize(true, $sxe->addChild('defaultValuePeriod'));
        }
        if (isset($this->defaultValueQuantity)) {
            $this->defaultValueQuantity->xmlSerialize(true, $sxe->addChild('defaultValueQuantity'));
        }
        if (isset($this->defaultValueRange)) {
            $this->defaultValueRange->xmlSerialize(true, $sxe->addChild('defaultValueRange'));
        }
        if (isset($this->defaultValueRatio)) {
            $this->defaultValueRatio->xmlSerialize(true, $sxe->addChild('defaultValueRatio'));
        }
        if (isset($this->defaultValueReference)) {
            $this->defaultValueReference->xmlSerialize(true, $sxe->addChild('defaultValueReference'));
        }
        if (isset($this->defaultValueSampledData)) {
            $this->defaultValueSampledData->xmlSerialize(true, $sxe->addChild('defaultValueSampledData'));
        }
        if (isset($this->defaultValueSignature)) {
            $this->defaultValueSignature->xmlSerialize(true, $sxe->addChild('defaultValueSignature'));
        }
        if (isset($this->defaultValueTiming)) {
            $this->defaultValueTiming->xmlSerialize(true, $sxe->addChild('defaultValueTiming'));
        }
        if (isset($this->defaultValueContactDetail)) {
            $this->defaultValueContactDetail->xmlSerialize(true, $sxe->addChild('defaultValueContactDetail'));
        }
        if (isset($this->defaultValueContributor)) {
            $this->defaultValueContributor->xmlSerialize(true, $sxe->addChild('defaultValueContributor'));
        }
        if (isset($this->defaultValueDataRequirement)) {
            $this->defaultValueDataRequirement->xmlSerialize(true, $sxe->addChild('defaultValueDataRequirement'));
        }
        if (isset($this->defaultValueExpression)) {
            $this->defaultValueExpression->xmlSerialize(true, $sxe->addChild('defaultValueExpression'));
        }
        if (isset($this->defaultValueParameterDefinition)) {
            $this->defaultValueParameterDefinition->xmlSerialize(true, $sxe->addChild('defaultValueParameterDefinition'));
        }
        if (isset($this->defaultValueRelatedArtifact)) {
            $this->defaultValueRelatedArtifact->xmlSerialize(true, $sxe->addChild('defaultValueRelatedArtifact'));
        }
        if (isset($this->defaultValueTriggerDefinition)) {
            $this->defaultValueTriggerDefinition->xmlSerialize(true, $sxe->addChild('defaultValueTriggerDefinition'));
        }
        if (isset($this->defaultValueUsageContext)) {
            $this->defaultValueUsageContext->xmlSerialize(true, $sxe->addChild('defaultValueUsageContext'));
        }
        if (isset($this->defaultValueDosage)) {
            $this->defaultValueDosage->xmlSerialize(true, $sxe->addChild('defaultValueDosage'));
        }
        if (isset($this->element)) {
            $this->element->xmlSerialize(true, $sxe->addChild('element'));
        }
        if (isset($this->listMode)) {
            $this->listMode->xmlSerialize(true, $sxe->addChild('listMode'));
        }
        if (isset($this->variable)) {
            $this->variable->xmlSerialize(true, $sxe->addChild('variable'));
        }
        if (isset($this->condition)) {
            $this->condition->xmlSerialize(true, $sxe->addChild('condition'));
        }
        if (isset($this->check)) {
            $this->check->xmlSerialize(true, $sxe->addChild('check'));
        }
        if (isset($this->logMessage)) {
            $this->logMessage->xmlSerialize(true, $sxe->addChild('logMessage'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
