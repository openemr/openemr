<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRElementDefinition;

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
 * Captures constraints on each element within the resource, profile, or extension.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRElementDefinitionExample extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Describes the purpose of this example amoung the set of examples.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $label = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary
     */
    public $valueBase64Binary = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $valueBoolean = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $valueCanonical = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $valueCode = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $valueDate = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $valueDateTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $valueDecimal = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $valueId = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public $valueInstant = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $valueInteger = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $valueMarkdown = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIROid
     */
    public $valueOid = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public $valuePositiveInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $valueString = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public $valueTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $valueUnsignedInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $valueUri = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl
     */
    public $valueUrl = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUuid
     */
    public $valueUuid = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress
     */
    public $valueAddress = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge
     */
    public $valueAge = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation
     */
    public $valueAnnotation = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public $valueAttachment = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $valueCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $valueCoding = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint
     */
    public $valueContactPoint = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRCount
     */
    public $valueCount = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDistance
     */
    public $valueDistance = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $valueDuration = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName
     */
    public $valueHumanName = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $valueIdentifier = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $valueMoney = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $valuePeriod = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $valueQuantity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $valueRange = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public $valueRatio = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $valueReference = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSampledData
     */
    public $valueSampledData = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature
     */
    public $valueSignature = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public $valueTiming = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail
     */
    public $valueContactDetail = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContributor
     */
    public $valueContributor = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement
     */
    public $valueDataRequirement = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRExpression
     */
    public $valueExpression = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRParameterDefinition
     */
    public $valueParameterDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact
     */
    public $valueRelatedArtifact = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTriggerDefinition
     */
    public $valueTriggerDefinition = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext
     */
    public $valueUsageContext = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage
     */
    public $valueDosage = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ElementDefinition.Example';

    /**
     * Describes the purpose of this example amoung the set of examples.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Describes the purpose of this example amoung the set of examples.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary
     */
    public function getValueBase64Binary()
    {
        return $this->valueBase64Binary;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary $valueBase64Binary
     * @return $this
     */
    public function setValueBase64Binary($valueBase64Binary)
    {
        $this->valueBase64Binary = $valueBase64Binary;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getValueBoolean()
    {
        return $this->valueBoolean;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $valueBoolean
     * @return $this
     */
    public function setValueBoolean($valueBoolean)
    {
        $this->valueBoolean = $valueBoolean;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getValueCanonical()
    {
        return $this->valueCanonical;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $valueCanonical
     * @return $this
     */
    public function setValueCanonical($valueCanonical)
    {
        $this->valueCanonical = $valueCanonical;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getValueCode()
    {
        return $this->valueCode;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $valueCode
     * @return $this
     */
    public function setValueCode($valueCode)
    {
        $this->valueCode = $valueCode;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getValueDate()
    {
        return $this->valueDate;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $valueDate
     * @return $this
     */
    public function setValueDate($valueDate)
    {
        $this->valueDate = $valueDate;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getValueDateTime()
    {
        return $this->valueDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $valueDateTime
     * @return $this
     */
    public function setValueDateTime($valueDateTime)
    {
        $this->valueDateTime = $valueDateTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getValueDecimal()
    {
        return $this->valueDecimal;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $valueDecimal
     * @return $this
     */
    public function setValueDecimal($valueDecimal)
    {
        $this->valueDecimal = $valueDecimal;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getValueId()
    {
        return $this->valueId;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $valueId
     * @return $this
     */
    public function setValueId($valueId)
    {
        $this->valueId = $valueId;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public function getValueInstant()
    {
        return $this->valueInstant;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant $valueInstant
     * @return $this
     */
    public function setValueInstant($valueInstant)
    {
        $this->valueInstant = $valueInstant;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getValueInteger()
    {
        return $this->valueInteger;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $valueInteger
     * @return $this
     */
    public function setValueInteger($valueInteger)
    {
        $this->valueInteger = $valueInteger;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getValueMarkdown()
    {
        return $this->valueMarkdown;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $valueMarkdown
     * @return $this
     */
    public function setValueMarkdown($valueMarkdown)
    {
        $this->valueMarkdown = $valueMarkdown;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIROid
     */
    public function getValueOid()
    {
        return $this->valueOid;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIROid $valueOid
     * @return $this
     */
    public function setValueOid($valueOid)
    {
        $this->valueOid = $valueOid;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public function getValuePositiveInt()
    {
        return $this->valuePositiveInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $valuePositiveInt
     * @return $this
     */
    public function setValuePositiveInt($valuePositiveInt)
    {
        $this->valuePositiveInt = $valuePositiveInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getValueString()
    {
        return $this->valueString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $valueString
     * @return $this
     */
    public function setValueString($valueString)
    {
        $this->valueString = $valueString;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTime
     */
    public function getValueTime()
    {
        return $this->valueTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTime $valueTime
     * @return $this
     */
    public function setValueTime($valueTime)
    {
        $this->valueTime = $valueTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getValueUnsignedInt()
    {
        return $this->valueUnsignedInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $valueUnsignedInt
     * @return $this
     */
    public function setValueUnsignedInt($valueUnsignedInt)
    {
        $this->valueUnsignedInt = $valueUnsignedInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getValueUri()
    {
        return $this->valueUri;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $valueUri
     * @return $this
     */
    public function setValueUri($valueUri)
    {
        $this->valueUri = $valueUri;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl
     */
    public function getValueUrl()
    {
        return $this->valueUrl;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl $valueUrl
     * @return $this
     */
    public function setValueUrl($valueUrl)
    {
        $this->valueUrl = $valueUrl;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUuid
     */
    public function getValueUuid()
    {
        return $this->valueUuid;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUuid $valueUuid
     * @return $this
     */
    public function setValueUuid($valueUuid)
    {
        $this->valueUuid = $valueUuid;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress
     */
    public function getValueAddress()
    {
        return $this->valueAddress;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress $valueAddress
     * @return $this
     */
    public function setValueAddress($valueAddress)
    {
        $this->valueAddress = $valueAddress;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge
     */
    public function getValueAge()
    {
        return $this->valueAge;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge $valueAge
     * @return $this
     */
    public function setValueAge($valueAge)
    {
        $this->valueAge = $valueAge;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation
     */
    public function getValueAnnotation()
    {
        return $this->valueAnnotation;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $valueAnnotation
     * @return $this
     */
    public function setValueAnnotation($valueAnnotation)
    {
        $this->valueAnnotation = $valueAnnotation;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public function getValueAttachment()
    {
        return $this->valueAttachment;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment $valueAttachment
     * @return $this
     */
    public function setValueAttachment($valueAttachment)
    {
        $this->valueAttachment = $valueAttachment;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getValueCodeableConcept()
    {
        return $this->valueCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $valueCodeableConcept
     * @return $this
     */
    public function setValueCodeableConcept($valueCodeableConcept)
    {
        $this->valueCodeableConcept = $valueCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getValueCoding()
    {
        return $this->valueCoding;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $valueCoding
     * @return $this
     */
    public function setValueCoding($valueCoding)
    {
        $this->valueCoding = $valueCoding;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint
     */
    public function getValueContactPoint()
    {
        return $this->valueContactPoint;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint $valueContactPoint
     * @return $this
     */
    public function setValueContactPoint($valueContactPoint)
    {
        $this->valueContactPoint = $valueContactPoint;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRCount
     */
    public function getValueCount()
    {
        return $this->valueCount;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRCount $valueCount
     * @return $this
     */
    public function setValueCount($valueCount)
    {
        $this->valueCount = $valueCount;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDistance
     */
    public function getValueDistance()
    {
        return $this->valueDistance;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDistance $valueDistance
     * @return $this
     */
    public function setValueDistance($valueDistance)
    {
        $this->valueDistance = $valueDistance;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getValueDuration()
    {
        return $this->valueDuration;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $valueDuration
     * @return $this
     */
    public function setValueDuration($valueDuration)
    {
        $this->valueDuration = $valueDuration;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName
     */
    public function getValueHumanName()
    {
        return $this->valueHumanName;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName $valueHumanName
     * @return $this
     */
    public function setValueHumanName($valueHumanName)
    {
        $this->valueHumanName = $valueHumanName;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getValueIdentifier()
    {
        return $this->valueIdentifier;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $valueIdentifier
     * @return $this
     */
    public function setValueIdentifier($valueIdentifier)
    {
        $this->valueIdentifier = $valueIdentifier;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getValueMoney()
    {
        return $this->valueMoney;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $valueMoney
     * @return $this
     */
    public function setValueMoney($valueMoney)
    {
        $this->valueMoney = $valueMoney;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getValuePeriod()
    {
        return $this->valuePeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $valuePeriod
     * @return $this
     */
    public function setValuePeriod($valuePeriod)
    {
        $this->valuePeriod = $valuePeriod;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getValueQuantity()
    {
        return $this->valueQuantity;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $valueQuantity
     * @return $this
     */
    public function setValueQuantity($valueQuantity)
    {
        $this->valueQuantity = $valueQuantity;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getValueRange()
    {
        return $this->valueRange;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $valueRange
     * @return $this
     */
    public function setValueRange($valueRange)
    {
        $this->valueRange = $valueRange;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getValueRatio()
    {
        return $this->valueRatio;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $valueRatio
     * @return $this
     */
    public function setValueRatio($valueRatio)
    {
        $this->valueRatio = $valueRatio;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getValueReference()
    {
        return $this->valueReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $valueReference
     * @return $this
     */
    public function setValueReference($valueReference)
    {
        $this->valueReference = $valueReference;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSampledData
     */
    public function getValueSampledData()
    {
        return $this->valueSampledData;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSampledData $valueSampledData
     * @return $this
     */
    public function setValueSampledData($valueSampledData)
    {
        $this->valueSampledData = $valueSampledData;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature
     */
    public function getValueSignature()
    {
        return $this->valueSignature;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature $valueSignature
     * @return $this
     */
    public function setValueSignature($valueSignature)
    {
        $this->valueSignature = $valueSignature;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public function getValueTiming()
    {
        return $this->valueTiming;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming $valueTiming
     * @return $this
     */
    public function setValueTiming($valueTiming)
    {
        $this->valueTiming = $valueTiming;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail
     */
    public function getValueContactDetail()
    {
        return $this->valueContactDetail;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactDetail $valueContactDetail
     * @return $this
     */
    public function setValueContactDetail($valueContactDetail)
    {
        $this->valueContactDetail = $valueContactDetail;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContributor
     */
    public function getValueContributor()
    {
        return $this->valueContributor;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContributor $valueContributor
     * @return $this
     */
    public function setValueContributor($valueContributor)
    {
        $this->valueContributor = $valueContributor;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement
     */
    public function getValueDataRequirement()
    {
        return $this->valueDataRequirement;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement $valueDataRequirement
     * @return $this
     */
    public function setValueDataRequirement($valueDataRequirement)
    {
        $this->valueDataRequirement = $valueDataRequirement;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRExpression
     */
    public function getValueExpression()
    {
        return $this->valueExpression;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRExpression $valueExpression
     * @return $this
     */
    public function setValueExpression($valueExpression)
    {
        $this->valueExpression = $valueExpression;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRParameterDefinition
     */
    public function getValueParameterDefinition()
    {
        return $this->valueParameterDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRParameterDefinition $valueParameterDefinition
     * @return $this
     */
    public function setValueParameterDefinition($valueParameterDefinition)
    {
        $this->valueParameterDefinition = $valueParameterDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact
     */
    public function getValueRelatedArtifact()
    {
        return $this->valueRelatedArtifact;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact $valueRelatedArtifact
     * @return $this
     */
    public function setValueRelatedArtifact($valueRelatedArtifact)
    {
        $this->valueRelatedArtifact = $valueRelatedArtifact;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTriggerDefinition
     */
    public function getValueTriggerDefinition()
    {
        return $this->valueTriggerDefinition;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTriggerDefinition $valueTriggerDefinition
     * @return $this
     */
    public function setValueTriggerDefinition($valueTriggerDefinition)
    {
        $this->valueTriggerDefinition = $valueTriggerDefinition;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext
     */
    public function getValueUsageContext()
    {
        return $this->valueUsageContext;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUsageContext $valueUsageContext
     * @return $this
     */
    public function setValueUsageContext($valueUsageContext)
    {
        $this->valueUsageContext = $valueUsageContext;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage
     */
    public function getValueDosage()
    {
        return $this->valueDosage;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage $valueDosage
     * @return $this
     */
    public function setValueDosage($valueDosage)
    {
        $this->valueDosage = $valueDosage;
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
            if (isset($data['label'])) {
                $this->setLabel($data['label']);
            }
            if (isset($data['valueBase64Binary'])) {
                $this->setValueBase64Binary($data['valueBase64Binary']);
            }
            if (isset($data['valueBoolean'])) {
                $this->setValueBoolean($data['valueBoolean']);
            }
            if (isset($data['valueCanonical'])) {
                $this->setValueCanonical($data['valueCanonical']);
            }
            if (isset($data['valueCode'])) {
                $this->setValueCode($data['valueCode']);
            }
            if (isset($data['valueDate'])) {
                $this->setValueDate($data['valueDate']);
            }
            if (isset($data['valueDateTime'])) {
                $this->setValueDateTime($data['valueDateTime']);
            }
            if (isset($data['valueDecimal'])) {
                $this->setValueDecimal($data['valueDecimal']);
            }
            if (isset($data['valueId'])) {
                $this->setValueId($data['valueId']);
            }
            if (isset($data['valueInstant'])) {
                $this->setValueInstant($data['valueInstant']);
            }
            if (isset($data['valueInteger'])) {
                $this->setValueInteger($data['valueInteger']);
            }
            if (isset($data['valueMarkdown'])) {
                $this->setValueMarkdown($data['valueMarkdown']);
            }
            if (isset($data['valueOid'])) {
                $this->setValueOid($data['valueOid']);
            }
            if (isset($data['valuePositiveInt'])) {
                $this->setValuePositiveInt($data['valuePositiveInt']);
            }
            if (isset($data['valueString'])) {
                $this->setValueString($data['valueString']);
            }
            if (isset($data['valueTime'])) {
                $this->setValueTime($data['valueTime']);
            }
            if (isset($data['valueUnsignedInt'])) {
                $this->setValueUnsignedInt($data['valueUnsignedInt']);
            }
            if (isset($data['valueUri'])) {
                $this->setValueUri($data['valueUri']);
            }
            if (isset($data['valueUrl'])) {
                $this->setValueUrl($data['valueUrl']);
            }
            if (isset($data['valueUuid'])) {
                $this->setValueUuid($data['valueUuid']);
            }
            if (isset($data['valueAddress'])) {
                $this->setValueAddress($data['valueAddress']);
            }
            if (isset($data['valueAge'])) {
                $this->setValueAge($data['valueAge']);
            }
            if (isset($data['valueAnnotation'])) {
                $this->setValueAnnotation($data['valueAnnotation']);
            }
            if (isset($data['valueAttachment'])) {
                $this->setValueAttachment($data['valueAttachment']);
            }
            if (isset($data['valueCodeableConcept'])) {
                $this->setValueCodeableConcept($data['valueCodeableConcept']);
            }
            if (isset($data['valueCoding'])) {
                $this->setValueCoding($data['valueCoding']);
            }
            if (isset($data['valueContactPoint'])) {
                $this->setValueContactPoint($data['valueContactPoint']);
            }
            if (isset($data['valueCount'])) {
                $this->setValueCount($data['valueCount']);
            }
            if (isset($data['valueDistance'])) {
                $this->setValueDistance($data['valueDistance']);
            }
            if (isset($data['valueDuration'])) {
                $this->setValueDuration($data['valueDuration']);
            }
            if (isset($data['valueHumanName'])) {
                $this->setValueHumanName($data['valueHumanName']);
            }
            if (isset($data['valueIdentifier'])) {
                $this->setValueIdentifier($data['valueIdentifier']);
            }
            if (isset($data['valueMoney'])) {
                $this->setValueMoney($data['valueMoney']);
            }
            if (isset($data['valuePeriod'])) {
                $this->setValuePeriod($data['valuePeriod']);
            }
            if (isset($data['valueQuantity'])) {
                $this->setValueQuantity($data['valueQuantity']);
            }
            if (isset($data['valueRange'])) {
                $this->setValueRange($data['valueRange']);
            }
            if (isset($data['valueRatio'])) {
                $this->setValueRatio($data['valueRatio']);
            }
            if (isset($data['valueReference'])) {
                $this->setValueReference($data['valueReference']);
            }
            if (isset($data['valueSampledData'])) {
                $this->setValueSampledData($data['valueSampledData']);
            }
            if (isset($data['valueSignature'])) {
                $this->setValueSignature($data['valueSignature']);
            }
            if (isset($data['valueTiming'])) {
                $this->setValueTiming($data['valueTiming']);
            }
            if (isset($data['valueContactDetail'])) {
                $this->setValueContactDetail($data['valueContactDetail']);
            }
            if (isset($data['valueContributor'])) {
                $this->setValueContributor($data['valueContributor']);
            }
            if (isset($data['valueDataRequirement'])) {
                $this->setValueDataRequirement($data['valueDataRequirement']);
            }
            if (isset($data['valueExpression'])) {
                $this->setValueExpression($data['valueExpression']);
            }
            if (isset($data['valueParameterDefinition'])) {
                $this->setValueParameterDefinition($data['valueParameterDefinition']);
            }
            if (isset($data['valueRelatedArtifact'])) {
                $this->setValueRelatedArtifact($data['valueRelatedArtifact']);
            }
            if (isset($data['valueTriggerDefinition'])) {
                $this->setValueTriggerDefinition($data['valueTriggerDefinition']);
            }
            if (isset($data['valueUsageContext'])) {
                $this->setValueUsageContext($data['valueUsageContext']);
            }
            if (isset($data['valueDosage'])) {
                $this->setValueDosage($data['valueDosage']);
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
        if (isset($this->label)) {
            $json['label'] = $this->label;
        }
        if (isset($this->valueBase64Binary)) {
            $json['valueBase64Binary'] = $this->valueBase64Binary;
        }
        if (isset($this->valueBoolean)) {
            $json['valueBoolean'] = $this->valueBoolean;
        }
        if (isset($this->valueCanonical)) {
            $json['valueCanonical'] = $this->valueCanonical;
        }
        if (isset($this->valueCode)) {
            $json['valueCode'] = $this->valueCode;
        }
        if (isset($this->valueDate)) {
            $json['valueDate'] = $this->valueDate;
        }
        if (isset($this->valueDateTime)) {
            $json['valueDateTime'] = $this->valueDateTime;
        }
        if (isset($this->valueDecimal)) {
            $json['valueDecimal'] = $this->valueDecimal;
        }
        if (isset($this->valueId)) {
            $json['valueId'] = $this->valueId;
        }
        if (isset($this->valueInstant)) {
            $json['valueInstant'] = $this->valueInstant;
        }
        if (isset($this->valueInteger)) {
            $json['valueInteger'] = $this->valueInteger;
        }
        if (isset($this->valueMarkdown)) {
            $json['valueMarkdown'] = $this->valueMarkdown;
        }
        if (isset($this->valueOid)) {
            $json['valueOid'] = $this->valueOid;
        }
        if (isset($this->valuePositiveInt)) {
            $json['valuePositiveInt'] = $this->valuePositiveInt;
        }
        if (isset($this->valueString)) {
            $json['valueString'] = $this->valueString;
        }
        if (isset($this->valueTime)) {
            $json['valueTime'] = $this->valueTime;
        }
        if (isset($this->valueUnsignedInt)) {
            $json['valueUnsignedInt'] = $this->valueUnsignedInt;
        }
        if (isset($this->valueUri)) {
            $json['valueUri'] = $this->valueUri;
        }
        if (isset($this->valueUrl)) {
            $json['valueUrl'] = $this->valueUrl;
        }
        if (isset($this->valueUuid)) {
            $json['valueUuid'] = $this->valueUuid;
        }
        if (isset($this->valueAddress)) {
            $json['valueAddress'] = $this->valueAddress;
        }
        if (isset($this->valueAge)) {
            $json['valueAge'] = $this->valueAge;
        }
        if (isset($this->valueAnnotation)) {
            $json['valueAnnotation'] = $this->valueAnnotation;
        }
        if (isset($this->valueAttachment)) {
            $json['valueAttachment'] = $this->valueAttachment;
        }
        if (isset($this->valueCodeableConcept)) {
            $json['valueCodeableConcept'] = $this->valueCodeableConcept;
        }
        if (isset($this->valueCoding)) {
            $json['valueCoding'] = $this->valueCoding;
        }
        if (isset($this->valueContactPoint)) {
            $json['valueContactPoint'] = $this->valueContactPoint;
        }
        if (isset($this->valueCount)) {
            $json['valueCount'] = $this->valueCount;
        }
        if (isset($this->valueDistance)) {
            $json['valueDistance'] = $this->valueDistance;
        }
        if (isset($this->valueDuration)) {
            $json['valueDuration'] = $this->valueDuration;
        }
        if (isset($this->valueHumanName)) {
            $json['valueHumanName'] = $this->valueHumanName;
        }
        if (isset($this->valueIdentifier)) {
            $json['valueIdentifier'] = $this->valueIdentifier;
        }
        if (isset($this->valueMoney)) {
            $json['valueMoney'] = $this->valueMoney;
        }
        if (isset($this->valuePeriod)) {
            $json['valuePeriod'] = $this->valuePeriod;
        }
        if (isset($this->valueQuantity)) {
            $json['valueQuantity'] = $this->valueQuantity;
        }
        if (isset($this->valueRange)) {
            $json['valueRange'] = $this->valueRange;
        }
        if (isset($this->valueRatio)) {
            $json['valueRatio'] = $this->valueRatio;
        }
        if (isset($this->valueReference)) {
            $json['valueReference'] = $this->valueReference;
        }
        if (isset($this->valueSampledData)) {
            $json['valueSampledData'] = $this->valueSampledData;
        }
        if (isset($this->valueSignature)) {
            $json['valueSignature'] = $this->valueSignature;
        }
        if (isset($this->valueTiming)) {
            $json['valueTiming'] = $this->valueTiming;
        }
        if (isset($this->valueContactDetail)) {
            $json['valueContactDetail'] = $this->valueContactDetail;
        }
        if (isset($this->valueContributor)) {
            $json['valueContributor'] = $this->valueContributor;
        }
        if (isset($this->valueDataRequirement)) {
            $json['valueDataRequirement'] = $this->valueDataRequirement;
        }
        if (isset($this->valueExpression)) {
            $json['valueExpression'] = $this->valueExpression;
        }
        if (isset($this->valueParameterDefinition)) {
            $json['valueParameterDefinition'] = $this->valueParameterDefinition;
        }
        if (isset($this->valueRelatedArtifact)) {
            $json['valueRelatedArtifact'] = $this->valueRelatedArtifact;
        }
        if (isset($this->valueTriggerDefinition)) {
            $json['valueTriggerDefinition'] = $this->valueTriggerDefinition;
        }
        if (isset($this->valueUsageContext)) {
            $json['valueUsageContext'] = $this->valueUsageContext;
        }
        if (isset($this->valueDosage)) {
            $json['valueDosage'] = $this->valueDosage;
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
            $sxe = new \SimpleXMLElement('<ElementDefinitionExample xmlns="http://hl7.org/fhir"></ElementDefinitionExample>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->label)) {
            $this->label->xmlSerialize(true, $sxe->addChild('label'));
        }
        if (isset($this->valueBase64Binary)) {
            $this->valueBase64Binary->xmlSerialize(true, $sxe->addChild('valueBase64Binary'));
        }
        if (isset($this->valueBoolean)) {
            $this->valueBoolean->xmlSerialize(true, $sxe->addChild('valueBoolean'));
        }
        if (isset($this->valueCanonical)) {
            $this->valueCanonical->xmlSerialize(true, $sxe->addChild('valueCanonical'));
        }
        if (isset($this->valueCode)) {
            $this->valueCode->xmlSerialize(true, $sxe->addChild('valueCode'));
        }
        if (isset($this->valueDate)) {
            $this->valueDate->xmlSerialize(true, $sxe->addChild('valueDate'));
        }
        if (isset($this->valueDateTime)) {
            $this->valueDateTime->xmlSerialize(true, $sxe->addChild('valueDateTime'));
        }
        if (isset($this->valueDecimal)) {
            $this->valueDecimal->xmlSerialize(true, $sxe->addChild('valueDecimal'));
        }
        if (isset($this->valueId)) {
            $this->valueId->xmlSerialize(true, $sxe->addChild('valueId'));
        }
        if (isset($this->valueInstant)) {
            $this->valueInstant->xmlSerialize(true, $sxe->addChild('valueInstant'));
        }
        if (isset($this->valueInteger)) {
            $this->valueInteger->xmlSerialize(true, $sxe->addChild('valueInteger'));
        }
        if (isset($this->valueMarkdown)) {
            $this->valueMarkdown->xmlSerialize(true, $sxe->addChild('valueMarkdown'));
        }
        if (isset($this->valueOid)) {
            $this->valueOid->xmlSerialize(true, $sxe->addChild('valueOid'));
        }
        if (isset($this->valuePositiveInt)) {
            $this->valuePositiveInt->xmlSerialize(true, $sxe->addChild('valuePositiveInt'));
        }
        if (isset($this->valueString)) {
            $this->valueString->xmlSerialize(true, $sxe->addChild('valueString'));
        }
        if (isset($this->valueTime)) {
            $this->valueTime->xmlSerialize(true, $sxe->addChild('valueTime'));
        }
        if (isset($this->valueUnsignedInt)) {
            $this->valueUnsignedInt->xmlSerialize(true, $sxe->addChild('valueUnsignedInt'));
        }
        if (isset($this->valueUri)) {
            $this->valueUri->xmlSerialize(true, $sxe->addChild('valueUri'));
        }
        if (isset($this->valueUrl)) {
            $this->valueUrl->xmlSerialize(true, $sxe->addChild('valueUrl'));
        }
        if (isset($this->valueUuid)) {
            $this->valueUuid->xmlSerialize(true, $sxe->addChild('valueUuid'));
        }
        if (isset($this->valueAddress)) {
            $this->valueAddress->xmlSerialize(true, $sxe->addChild('valueAddress'));
        }
        if (isset($this->valueAge)) {
            $this->valueAge->xmlSerialize(true, $sxe->addChild('valueAge'));
        }
        if (isset($this->valueAnnotation)) {
            $this->valueAnnotation->xmlSerialize(true, $sxe->addChild('valueAnnotation'));
        }
        if (isset($this->valueAttachment)) {
            $this->valueAttachment->xmlSerialize(true, $sxe->addChild('valueAttachment'));
        }
        if (isset($this->valueCodeableConcept)) {
            $this->valueCodeableConcept->xmlSerialize(true, $sxe->addChild('valueCodeableConcept'));
        }
        if (isset($this->valueCoding)) {
            $this->valueCoding->xmlSerialize(true, $sxe->addChild('valueCoding'));
        }
        if (isset($this->valueContactPoint)) {
            $this->valueContactPoint->xmlSerialize(true, $sxe->addChild('valueContactPoint'));
        }
        if (isset($this->valueCount)) {
            $this->valueCount->xmlSerialize(true, $sxe->addChild('valueCount'));
        }
        if (isset($this->valueDistance)) {
            $this->valueDistance->xmlSerialize(true, $sxe->addChild('valueDistance'));
        }
        if (isset($this->valueDuration)) {
            $this->valueDuration->xmlSerialize(true, $sxe->addChild('valueDuration'));
        }
        if (isset($this->valueHumanName)) {
            $this->valueHumanName->xmlSerialize(true, $sxe->addChild('valueHumanName'));
        }
        if (isset($this->valueIdentifier)) {
            $this->valueIdentifier->xmlSerialize(true, $sxe->addChild('valueIdentifier'));
        }
        if (isset($this->valueMoney)) {
            $this->valueMoney->xmlSerialize(true, $sxe->addChild('valueMoney'));
        }
        if (isset($this->valuePeriod)) {
            $this->valuePeriod->xmlSerialize(true, $sxe->addChild('valuePeriod'));
        }
        if (isset($this->valueQuantity)) {
            $this->valueQuantity->xmlSerialize(true, $sxe->addChild('valueQuantity'));
        }
        if (isset($this->valueRange)) {
            $this->valueRange->xmlSerialize(true, $sxe->addChild('valueRange'));
        }
        if (isset($this->valueRatio)) {
            $this->valueRatio->xmlSerialize(true, $sxe->addChild('valueRatio'));
        }
        if (isset($this->valueReference)) {
            $this->valueReference->xmlSerialize(true, $sxe->addChild('valueReference'));
        }
        if (isset($this->valueSampledData)) {
            $this->valueSampledData->xmlSerialize(true, $sxe->addChild('valueSampledData'));
        }
        if (isset($this->valueSignature)) {
            $this->valueSignature->xmlSerialize(true, $sxe->addChild('valueSignature'));
        }
        if (isset($this->valueTiming)) {
            $this->valueTiming->xmlSerialize(true, $sxe->addChild('valueTiming'));
        }
        if (isset($this->valueContactDetail)) {
            $this->valueContactDetail->xmlSerialize(true, $sxe->addChild('valueContactDetail'));
        }
        if (isset($this->valueContributor)) {
            $this->valueContributor->xmlSerialize(true, $sxe->addChild('valueContributor'));
        }
        if (isset($this->valueDataRequirement)) {
            $this->valueDataRequirement->xmlSerialize(true, $sxe->addChild('valueDataRequirement'));
        }
        if (isset($this->valueExpression)) {
            $this->valueExpression->xmlSerialize(true, $sxe->addChild('valueExpression'));
        }
        if (isset($this->valueParameterDefinition)) {
            $this->valueParameterDefinition->xmlSerialize(true, $sxe->addChild('valueParameterDefinition'));
        }
        if (isset($this->valueRelatedArtifact)) {
            $this->valueRelatedArtifact->xmlSerialize(true, $sxe->addChild('valueRelatedArtifact'));
        }
        if (isset($this->valueTriggerDefinition)) {
            $this->valueTriggerDefinition->xmlSerialize(true, $sxe->addChild('valueTriggerDefinition'));
        }
        if (isset($this->valueUsageContext)) {
            $this->valueUsageContext->xmlSerialize(true, $sxe->addChild('valueUsageContext'));
        }
        if (isset($this->valueDosage)) {
            $this->valueDosage->xmlSerialize(true, $sxe->addChild('valueDosage'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
