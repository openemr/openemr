<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRParameters;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This special resource type is used to represent an operation request and response (operations.html). It has no other use, and there is no RESTful endpoint associated with it.
 */
class FHIRParametersParameter extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The name of the parameter (reference to the operation definition).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public $valueBase64Binary = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $valueBoolean = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $valueCode = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public $valueDate = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $valueDateTime = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $valueDecimal = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $valueId = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public $valueInstant = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $valueInteger = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown
     */
    public $valueMarkdown = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIROid
     */
    public $valueOid = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $valuePositiveInt = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $valueString = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTime
     */
    public $valueTime = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public $valueUnsignedInt = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $valueUri = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAddress
     */
    public $valueAddress = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRAge
     */
    public $valueAge = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation
     */
    public $valueAnnotation = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public $valueAttachment = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $valueCodeableConcept = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $valueCoding = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint
     */
    public $valueContactPoint = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRCount
     */
    public $valueCount = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDistance
     */
    public $valueDistance = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $valueDuration = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRHumanName
     */
    public $valueHumanName = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $valueIdentifier = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $valueMoney = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $valuePeriod = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $valueQuantity = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public $valueRange = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRRatio
     */
    public $valueRatio = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $valueReference = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSampledData
     */
    public $valueSampledData = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSignature
     */
    public $valueSignature = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTiming
     */
    public $valueTiming = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRMeta
     */
    public $valueMeta = null;

    /**
     * If the parameter is a whole resource.
     * @var \HL7\FHIR\STU3\FHIRResourceContainer
     */
    public $resource = null;

    /**
     * A named part of a multi-part parameter.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRParameters\FHIRParametersParameter[]
     */
    public $part = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Parameters.Parameter';

    /**
     * The name of the parameter (reference to the operation definition).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The name of the parameter (reference to the operation definition).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary
     */
    public function getValueBase64Binary()
    {
        return $this->valueBase64Binary;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBase64Binary $valueBase64Binary
     * @return $this
     */
    public function setValueBase64Binary($valueBase64Binary)
    {
        $this->valueBase64Binary = $valueBase64Binary;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getValueBoolean()
    {
        return $this->valueBoolean;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $valueBoolean
     * @return $this
     */
    public function setValueBoolean($valueBoolean)
    {
        $this->valueBoolean = $valueBoolean;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getValueCode()
    {
        return $this->valueCode;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $valueCode
     * @return $this
     */
    public function setValueCode($valueCode)
    {
        $this->valueCode = $valueCode;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public function getValueDate()
    {
        return $this->valueDate;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDate $valueDate
     * @return $this
     */
    public function setValueDate($valueDate)
    {
        $this->valueDate = $valueDate;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getValueDateTime()
    {
        return $this->valueDateTime;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $valueDateTime
     * @return $this
     */
    public function setValueDateTime($valueDateTime)
    {
        $this->valueDateTime = $valueDateTime;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getValueDecimal()
    {
        return $this->valueDecimal;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $valueDecimal
     * @return $this
     */
    public function setValueDecimal($valueDecimal)
    {
        $this->valueDecimal = $valueDecimal;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getValueId()
    {
        return $this->valueId;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $valueId
     * @return $this
     */
    public function setValueId($valueId)
    {
        $this->valueId = $valueId;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public function getValueInstant()
    {
        return $this->valueInstant;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInstant $valueInstant
     * @return $this
     */
    public function setValueInstant($valueInstant)
    {
        $this->valueInstant = $valueInstant;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getValueInteger()
    {
        return $this->valueInteger;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $valueInteger
     * @return $this
     */
    public function setValueInteger($valueInteger)
    {
        $this->valueInteger = $valueInteger;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown
     */
    public function getValueMarkdown()
    {
        return $this->valueMarkdown;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRMarkdown $valueMarkdown
     * @return $this
     */
    public function setValueMarkdown($valueMarkdown)
    {
        $this->valueMarkdown = $valueMarkdown;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIROid
     */
    public function getValueOid()
    {
        return $this->valueOid;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIROid $valueOid
     * @return $this
     */
    public function setValueOid($valueOid)
    {
        $this->valueOid = $valueOid;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getValuePositiveInt()
    {
        return $this->valuePositiveInt;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $valuePositiveInt
     * @return $this
     */
    public function setValuePositiveInt($valuePositiveInt)
    {
        $this->valuePositiveInt = $valuePositiveInt;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getValueString()
    {
        return $this->valueString;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $valueString
     * @return $this
     */
    public function setValueString($valueString)
    {
        $this->valueString = $valueString;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTime
     */
    public function getValueTime()
    {
        return $this->valueTime;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTime $valueTime
     * @return $this
     */
    public function setValueTime($valueTime)
    {
        $this->valueTime = $valueTime;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public function getValueUnsignedInt()
    {
        return $this->valueUnsignedInt;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt $valueUnsignedInt
     * @return $this
     */
    public function setValueUnsignedInt($valueUnsignedInt)
    {
        $this->valueUnsignedInt = $valueUnsignedInt;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getValueUri()
    {
        return $this->valueUri;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $valueUri
     * @return $this
     */
    public function setValueUri($valueUri)
    {
        $this->valueUri = $valueUri;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAddress
     */
    public function getValueAddress()
    {
        return $this->valueAddress;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAddress $valueAddress
     * @return $this
     */
    public function setValueAddress($valueAddress)
    {
        $this->valueAddress = $valueAddress;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRAge
     */
    public function getValueAge()
    {
        return $this->valueAge;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRAge $valueAge
     * @return $this
     */
    public function setValueAge($valueAge)
    {
        $this->valueAge = $valueAge;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation
     */
    public function getValueAnnotation()
    {
        return $this->valueAnnotation;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation $valueAnnotation
     * @return $this
     */
    public function setValueAnnotation($valueAnnotation)
    {
        $this->valueAnnotation = $valueAnnotation;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public function getValueAttachment()
    {
        return $this->valueAttachment;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAttachment $valueAttachment
     * @return $this
     */
    public function setValueAttachment($valueAttachment)
    {
        $this->valueAttachment = $valueAttachment;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getValueCodeableConcept()
    {
        return $this->valueCodeableConcept;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $valueCodeableConcept
     * @return $this
     */
    public function setValueCodeableConcept($valueCodeableConcept)
    {
        $this->valueCodeableConcept = $valueCodeableConcept;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getValueCoding()
    {
        return $this->valueCoding;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $valueCoding
     * @return $this
     */
    public function setValueCoding($valueCoding)
    {
        $this->valueCoding = $valueCoding;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint
     */
    public function getValueContactPoint()
    {
        return $this->valueContactPoint;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRContactPoint $valueContactPoint
     * @return $this
     */
    public function setValueContactPoint($valueContactPoint)
    {
        $this->valueContactPoint = $valueContactPoint;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRCount
     */
    public function getValueCount()
    {
        return $this->valueCount;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRCount $valueCount
     * @return $this
     */
    public function setValueCount($valueCount)
    {
        $this->valueCount = $valueCount;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDistance
     */
    public function getValueDistance()
    {
        return $this->valueDistance;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDistance $valueDistance
     * @return $this
     */
    public function setValueDistance($valueDistance)
    {
        $this->valueDistance = $valueDistance;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getValueDuration()
    {
        return $this->valueDuration;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRDuration $valueDuration
     * @return $this
     */
    public function setValueDuration($valueDuration)
    {
        $this->valueDuration = $valueDuration;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRHumanName
     */
    public function getValueHumanName()
    {
        return $this->valueHumanName;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRHumanName $valueHumanName
     * @return $this
     */
    public function setValueHumanName($valueHumanName)
    {
        $this->valueHumanName = $valueHumanName;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getValueIdentifier()
    {
        return $this->valueIdentifier;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $valueIdentifier
     * @return $this
     */
    public function setValueIdentifier($valueIdentifier)
    {
        $this->valueIdentifier = $valueIdentifier;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getValueMoney()
    {
        return $this->valueMoney;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $valueMoney
     * @return $this
     */
    public function setValueMoney($valueMoney)
    {
        $this->valueMoney = $valueMoney;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getValuePeriod()
    {
        return $this->valuePeriod;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $valuePeriod
     * @return $this
     */
    public function setValuePeriod($valuePeriod)
    {
        $this->valuePeriod = $valuePeriod;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getValueQuantity()
    {
        return $this->valueQuantity;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $valueQuantity
     * @return $this
     */
    public function setValueQuantity($valueQuantity)
    {
        $this->valueQuantity = $valueQuantity;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRange
     */
    public function getValueRange()
    {
        return $this->valueRange;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRange $valueRange
     * @return $this
     */
    public function setValueRange($valueRange)
    {
        $this->valueRange = $valueRange;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRRatio
     */
    public function getValueRatio()
    {
        return $this->valueRatio;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRRatio $valueRatio
     * @return $this
     */
    public function setValueRatio($valueRatio)
    {
        $this->valueRatio = $valueRatio;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getValueReference()
    {
        return $this->valueReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $valueReference
     * @return $this
     */
    public function setValueReference($valueReference)
    {
        $this->valueReference = $valueReference;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSampledData
     */
    public function getValueSampledData()
    {
        return $this->valueSampledData;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSampledData $valueSampledData
     * @return $this
     */
    public function setValueSampledData($valueSampledData)
    {
        $this->valueSampledData = $valueSampledData;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSignature
     */
    public function getValueSignature()
    {
        return $this->valueSignature;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSignature $valueSignature
     * @return $this
     */
    public function setValueSignature($valueSignature)
    {
        $this->valueSignature = $valueSignature;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTiming
     */
    public function getValueTiming()
    {
        return $this->valueTiming;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTiming $valueTiming
     * @return $this
     */
    public function setValueTiming($valueTiming)
    {
        $this->valueTiming = $valueTiming;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRMeta
     */
    public function getValueMeta()
    {
        return $this->valueMeta;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRMeta $valueMeta
     * @return $this
     */
    public function setValueMeta($valueMeta)
    {
        $this->valueMeta = $valueMeta;
        return $this;
    }

    /**
     * If the parameter is a whole resource.
     * @return mixed
     */
    public function getResource()
    {
        return isset($this->resource) ? $this->resource->jsonSerialize() : null;
    }

    /**
     * If the parameter is a whole resource.
     * @param \HL7\FHIR\STU3\FHIRResourceContainer $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * A named part of a multi-part parameter.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRParameters\FHIRParametersParameter[]
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * A named part of a multi-part parameter.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRParameters\FHIRParametersParameter $part
     * @return $this
     */
    public function addPart($part)
    {
        $this->part[] = $part;
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
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['valueBase64Binary'])) {
                $this->setValueBase64Binary($data['valueBase64Binary']);
            }
            if (isset($data['valueBoolean'])) {
                $this->setValueBoolean($data['valueBoolean']);
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
            if (isset($data['valueMeta'])) {
                $this->setValueMeta($data['valueMeta']);
            }
            if (isset($data['resource'])) {
                $this->setResource($data['resource']);
            }
            if (isset($data['part'])) {
                if (is_array($data['part'])) {
                    foreach ($data['part'] as $d) {
                        $this->addPart($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"part" must be array of objects or null, '.gettype($data['part']).' seen.');
                }
            }
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
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
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->valueBase64Binary)) {
            $json['valueBase64Binary'] = $this->valueBase64Binary;
        }
        if (isset($this->valueBoolean)) {
            $json['valueBoolean'] = $this->valueBoolean;
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
        if (isset($this->valueMeta)) {
            $json['valueMeta'] = $this->valueMeta;
        }
        if (isset($this->resource)) {
            $json['resource'] = $this->resource;
        }
        if (0 < count($this->part)) {
            $json['part'] = [];
            foreach ($this->part as $part) {
                $json['part'][] = $part;
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
            $sxe = new \SimpleXMLElement('<ParametersParameter xmlns="http://hl7.org/fhir"></ParametersParameter>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->valueBase64Binary)) {
            $this->valueBase64Binary->xmlSerialize(true, $sxe->addChild('valueBase64Binary'));
        }
        if (isset($this->valueBoolean)) {
            $this->valueBoolean->xmlSerialize(true, $sxe->addChild('valueBoolean'));
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
        if (isset($this->valueMeta)) {
            $this->valueMeta->xmlSerialize(true, $sxe->addChild('valueMeta'));
        }
        if (isset($this->resource)) {
            $this->resource->xmlSerialize(true, $sxe->addChild('resource'));
        }
        if (0 < count($this->part)) {
            foreach ($this->part as $part) {
                $part->xmlSerialize(true, $sxe->addChild('part'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
