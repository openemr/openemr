<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters;

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
use OpenEMR\FHIR\Types\ElementTypeInterface;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBase64BinaryPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDosage;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContributor;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROid;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRParameterDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRCount;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDistance;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSampledData;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUuid;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIROidPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUrlPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUuidPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * This resource is a non-persisted resource used to pass information into and back
 * from an [operation](operations.html). It has no other use, and there is no
 * RESTful endpoint associated with it.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRParametersParameter extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_PARAMETERS_DOT_PARAMETER;

    /* class_default.php:56 */
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_VALUE_BASE_64BINARY = 'valueBase64Binary';
    public const FIELD_VALUE_BASE_64BINARY_EXT = '_valueBase64Binary';
    public const FIELD_VALUE_BOOLEAN = 'valueBoolean';
    public const FIELD_VALUE_BOOLEAN_EXT = '_valueBoolean';
    public const FIELD_VALUE_CANONICAL = 'valueCanonical';
    public const FIELD_VALUE_CANONICAL_EXT = '_valueCanonical';
    public const FIELD_VALUE_CODE = 'valueCode';
    public const FIELD_VALUE_CODE_EXT = '_valueCode';
    public const FIELD_VALUE_DATE = 'valueDate';
    public const FIELD_VALUE_DATE_EXT = '_valueDate';
    public const FIELD_VALUE_DATE_TIME = 'valueDateTime';
    public const FIELD_VALUE_DATE_TIME_EXT = '_valueDateTime';
    public const FIELD_VALUE_DECIMAL = 'valueDecimal';
    public const FIELD_VALUE_DECIMAL_EXT = '_valueDecimal';
    public const FIELD_VALUE_ID = 'valueId';
    public const FIELD_VALUE_ID_EXT = '_valueId';
    public const FIELD_VALUE_INSTANT = 'valueInstant';
    public const FIELD_VALUE_INSTANT_EXT = '_valueInstant';
    public const FIELD_VALUE_INTEGER = 'valueInteger';
    public const FIELD_VALUE_INTEGER_EXT = '_valueInteger';
    public const FIELD_VALUE_MARKDOWN = 'valueMarkdown';
    public const FIELD_VALUE_MARKDOWN_EXT = '_valueMarkdown';
    public const FIELD_VALUE_OID = 'valueOid';
    public const FIELD_VALUE_OID_EXT = '_valueOid';
    public const FIELD_VALUE_POSITIVE_INT = 'valuePositiveInt';
    public const FIELD_VALUE_POSITIVE_INT_EXT = '_valuePositiveInt';
    public const FIELD_VALUE_STRING = 'valueString';
    public const FIELD_VALUE_STRING_EXT = '_valueString';
    public const FIELD_VALUE_TIME = 'valueTime';
    public const FIELD_VALUE_TIME_EXT = '_valueTime';
    public const FIELD_VALUE_UNSIGNED_INT = 'valueUnsignedInt';
    public const FIELD_VALUE_UNSIGNED_INT_EXT = '_valueUnsignedInt';
    public const FIELD_VALUE_URI = 'valueUri';
    public const FIELD_VALUE_URI_EXT = '_valueUri';
    public const FIELD_VALUE_URL = 'valueUrl';
    public const FIELD_VALUE_URL_EXT = '_valueUrl';
    public const FIELD_VALUE_UUID = 'valueUuid';
    public const FIELD_VALUE_UUID_EXT = '_valueUuid';
    public const FIELD_VALUE_ADDRESS = 'valueAddress';
    public const FIELD_VALUE_AGE = 'valueAge';
    public const FIELD_VALUE_ANNOTATION = 'valueAnnotation';
    public const FIELD_VALUE_ATTACHMENT = 'valueAttachment';
    public const FIELD_VALUE_CODEABLE_CONCEPT = 'valueCodeableConcept';
    public const FIELD_VALUE_CODING = 'valueCoding';
    public const FIELD_VALUE_CONTACT_POINT = 'valueContactPoint';
    public const FIELD_VALUE_COUNT = 'valueCount';
    public const FIELD_VALUE_DISTANCE = 'valueDistance';
    public const FIELD_VALUE_DURATION = 'valueDuration';
    public const FIELD_VALUE_HUMAN_NAME = 'valueHumanName';
    public const FIELD_VALUE_IDENTIFIER = 'valueIdentifier';
    public const FIELD_VALUE_MONEY = 'valueMoney';
    public const FIELD_VALUE_PERIOD = 'valuePeriod';
    public const FIELD_VALUE_QUANTITY = 'valueQuantity';
    public const FIELD_VALUE_RANGE = 'valueRange';
    public const FIELD_VALUE_RATIO = 'valueRatio';
    public const FIELD_VALUE_REFERENCE = 'valueReference';
    public const FIELD_VALUE_SAMPLED_DATA = 'valueSampledData';
    public const FIELD_VALUE_SIGNATURE = 'valueSignature';
    public const FIELD_VALUE_TIMING = 'valueTiming';
    public const FIELD_VALUE_CONTACT_DETAIL = 'valueContactDetail';
    public const FIELD_VALUE_CONTRIBUTOR = 'valueContributor';
    public const FIELD_VALUE_DATA_REQUIREMENT = 'valueDataRequirement';
    public const FIELD_VALUE_EXPRESSION = 'valueExpression';
    public const FIELD_VALUE_PARAMETER_DEFINITION = 'valueParameterDefinition';
    public const FIELD_VALUE_RELATED_ARTIFACT = 'valueRelatedArtifact';
    public const FIELD_VALUE_TRIGGER_DEFINITION = 'valueTriggerDefinition';
    public const FIELD_VALUE_USAGE_CONTEXT = 'valueUsageContext';
    public const FIELD_VALUE_DOSAGE = 'valueDosage';
    public const FIELD_VALUE_META = 'valueMeta';
    public const FIELD_RESOURCE = 'resource';
    public const FIELD_PART = 'part';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_NAME => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_BASE_64BINARY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_BOOLEAN => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_CANONICAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_CODE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_DECIMAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_INSTANT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_INTEGER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_MARKDOWN => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_OID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_POSITIVE_INT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_UNSIGNED_INT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_URI => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE_UUID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the parameter (reference to the operation definition).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $name;
    /**
     * A stream of bytes
     * A stream of bytes, base64 encoded
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary
     */
    #[FHIRBase64Binary]
    protected FHIRBase64Binary $valueBase64Binary;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $valueBoolean;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $valueCanonical;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $valueCode;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $valueDate;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $valueDateTime;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $valueDecimal;
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $valueId;
    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    #[FHIRInstant]
    protected FHIRInstant $valueInstant;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $valueInteger;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $valueMarkdown;
    /**
     * An OID represented as a URI
     * RFC 3001. See also ISO/IEC 8824:1990 €
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROid
     */
    #[FHIROid]
    protected FHIROid $valueOid;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $valuePositiveInt;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $valueString;
    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    #[FHIRTime]
    protected FHIRTime $valueTime;
    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    #[FHIRUnsignedInt]
    protected FHIRUnsignedInt $valueUnsignedInt;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $valueUri;
    /**
     * A URI that is a literal reference
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl
     */
    #[FHIRUrl]
    protected FHIRUrl $valueUrl;
    /**
     * A UUID, represented as a URI
     * See The Open Group, CDE 1.1 Remote Procedure Call specification, Appendix A.
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUuid
     */
    #[FHIRUuid]
    protected FHIRUuid $valueUuid;
    /**
     * An address expressed using postal conventions (as opposed to GPS or other
     * location definition formats). This data type may be used to convey addresses for
     * use in delivering mail as well as for visiting locations which might not be
     * valid for mail delivery. There are a variety of postal address formats defined
     * around the world.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress
     */
    #[FHIRAddress]
    protected FHIRAddress $valueAddress;
    /**
     * A duration of time during which an organism (or a process) has existed.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge
     */
    #[FHIRAge]
    protected FHIRAge $valueAge;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation
     */
    #[FHIRAnnotation]
    protected FHIRAnnotation $valueAnnotation;
    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    #[FHIRAttachment]
    protected FHIRAttachment $valueAttachment;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $valueCodeableConcept;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    #[FHIRCoding]
    protected FHIRCoding $valueCoding;
    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint
     */
    #[FHIRContactPoint]
    protected FHIRContactPoint $valueContactPoint;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRCount
     */
    #[FHIRCount]
    protected FHIRCount $valueCount;
    /**
     * A length - a value with a unit that is a physical distance.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDistance
     */
    #[FHIRDistance]
    protected FHIRDistance $valueDistance;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $valueDuration;
    /**
     * A human's name with the ability to identify parts and usage.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName
     */
    #[FHIRHumanName]
    protected FHIRHumanName $valueHumanName;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $valueIdentifier;
    /**
     * An amount of economic utility in some recognized currency.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney
     */
    #[FHIRMoney]
    protected FHIRMoney $valueMoney;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $valuePeriod;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $valueQuantity;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $valueRange;
    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    #[FHIRRatio]
    protected FHIRRatio $valueRatio;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $valueReference;
    /**
     * A series of measurements taken by a device, with upper and lower limits. There
     * may be more than one dimension in the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSampledData
     */
    #[FHIRSampledData]
    protected FHIRSampledData $valueSampledData;
    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    #[FHIRSignature]
    protected FHIRSignature $valueSignature;
    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    #[FHIRTiming]
    protected FHIRTiming $valueTiming;
    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail
     */
    #[FHIRContactDetail]
    protected FHIRContactDetail $valueContactDetail;
    /**
     * A contributor to the content of a knowledge asset, including authors, editors,
     * reviewers, and endorsers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContributor
     */
    #[FHIRContributor]
    protected FHIRContributor $valueContributor;
    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement
     */
    #[FHIRDataRequirement]
    protected FHIRDataRequirement $valueDataRequirement;
    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression
     */
    #[FHIRExpression]
    protected FHIRExpression $valueExpression;
    /**
     * The parameters to the module. This collection specifies both the input and
     * output parameters. Input parameters are provided by the caller as part of the
     * $evaluate operation. Output parameters are included in the GuidanceResponse.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRParameterDefinition
     */
    #[FHIRParameterDefinition]
    protected FHIRParameterDefinition $valueParameterDefinition;
    /**
     * Related artifacts such as additional documentation, justification, or
     * bibliographic references.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact
     */
    #[FHIRRelatedArtifact]
    protected FHIRRelatedArtifact $valueRelatedArtifact;
    /**
     * A description of a triggering event. Triggering events can be named events, data
     * events, or periodic, as determined by the type element.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition
     */
    #[FHIRTriggerDefinition]
    protected FHIRTriggerDefinition $valueTriggerDefinition;
    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext
     */
    #[FHIRUsageContext]
    protected FHIRUsageContext $valueUsageContext;
    /**
     * Indicates how the medication is/was taken or should be taken by the patient.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDosage
     */
    #[FHIRDosage]
    protected FHIRDosage $valueDosage;
    /**
     * The metadata about a resource. This is content in the resource that is
     * maintained by the infrastructure. Changes to the content might not always be
     * associated with version changes to the resource.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta
     */
    #[FHIRMeta]
    protected FHIRMeta $valueMeta;
    /**
     * (choose any one of the elements, but only one)
     *
     * If the parameter is a whole resource.
     *
     * @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface
     */
    #[FHIRResourceContainer]
    protected VersionContainedTypeInterface $resource;
    /**
     * This resource is a non-persisted resource used to pass information into and back
     * from an [operation](operations.html). It has no other use, and there is no
     * RESTful endpoint associated with it.
     *
     * A named part of a multi-part parameter.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter>
     */
    #[FHIRParametersParameter]
    protected array $part;

    /* constructor.php:61 */
    /**
     * FHIRParametersParameter Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRBase64BinaryPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary $valueBase64Binary
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $valueBoolean
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $valueCanonical
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $valueCode
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $valueDate
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $valueDateTime
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $valueDecimal
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $valueId
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $valueInstant
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $valueInteger
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $valueMarkdown
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIROidPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROid $valueOid
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $valuePositiveInt
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $valueString
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $valueTime
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $valueUnsignedInt
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $valueUri
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUrlPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl $valueUrl
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUuidPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUuid $valueUuid
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress $valueAddress
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge $valueAge
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation $valueAnnotation
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $valueAttachment
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $valueCodeableConcept
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $valueCoding
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint $valueContactPoint
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRCount $valueCount
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDistance $valueDistance
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $valueDuration
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName $valueHumanName
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $valueIdentifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney $valueMoney
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $valuePeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $valueQuantity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $valueRange
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $valueRatio
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $valueReference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSampledData $valueSampledData
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $valueSignature
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $valueTiming
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail $valueContactDetail
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContributor $valueContributor
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement $valueDataRequirement
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression $valueExpression
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRParameterDefinition $valueParameterDefinition
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact $valueRelatedArtifact
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition $valueTriggerDefinition
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext $valueUsageContext
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDosage $valueDosage
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $valueMeta
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $resource
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter> $part
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|string|FHIRBase64BinaryPrimitive|FHIRBase64Binary $valueBase64Binary = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $valueBoolean = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $valueCanonical = null,
                                null|string|FHIRCodePrimitive|FHIRCode $valueCode = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $valueDate = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $valueDateTime = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $valueDecimal = null,
                                null|string|FHIRIdPrimitive|FHIRId $valueId = null,
                                null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $valueInstant = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $valueInteger = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $valueMarkdown = null,
                                null|string|FHIROidPrimitive|FHIROid $valueOid = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $valuePositiveInt = null,
                                null|string|FHIRStringPrimitive|FHIRString $valueString = null,
                                null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $valueTime = null,
                                null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $valueUnsignedInt = null,
                                null|string|FHIRUriPrimitive|FHIRUri $valueUri = null,
                                null|string|FHIRUrlPrimitive|FHIRUrl $valueUrl = null,
                                null|string|FHIRUuidPrimitive|FHIRUuid $valueUuid = null,
                                null|FHIRAddress $valueAddress = null,
                                null|FHIRAge $valueAge = null,
                                null|FHIRAnnotation $valueAnnotation = null,
                                null|FHIRAttachment $valueAttachment = null,
                                null|FHIRCodeableConcept $valueCodeableConcept = null,
                                null|FHIRCoding $valueCoding = null,
                                null|FHIRContactPoint $valueContactPoint = null,
                                null|FHIRCount $valueCount = null,
                                null|FHIRDistance $valueDistance = null,
                                null|FHIRDuration $valueDuration = null,
                                null|FHIRHumanName $valueHumanName = null,
                                null|FHIRIdentifier $valueIdentifier = null,
                                null|FHIRMoney $valueMoney = null,
                                null|FHIRPeriod $valuePeriod = null,
                                null|FHIRQuantity $valueQuantity = null,
                                null|FHIRRange $valueRange = null,
                                null|FHIRRatio $valueRatio = null,
                                null|FHIRReference $valueReference = null,
                                null|FHIRSampledData $valueSampledData = null,
                                null|FHIRSignature $valueSignature = null,
                                null|FHIRTiming $valueTiming = null,
                                null|FHIRContactDetail $valueContactDetail = null,
                                null|FHIRContributor $valueContributor = null,
                                null|FHIRDataRequirement $valueDataRequirement = null,
                                null|FHIRExpression $valueExpression = null,
                                null|FHIRParameterDefinition $valueParameterDefinition = null,
                                null|FHIRRelatedArtifact $valueRelatedArtifact = null,
                                null|FHIRTriggerDefinition $valueTriggerDefinition = null,
                                null|FHIRUsageContext $valueUsageContext = null,
                                null|FHIRDosage $valueDosage = null,
                                null|FHIRMeta $valueMeta = null,
                                null|FHIRResourceContainer|VersionContainedTypeInterface $resource = null,
                                null|iterable $part = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $valueBase64Binary) {
            $this->setValueBase64Binary($valueBase64Binary);
        }
        if (null !== $valueBoolean) {
            $this->setValueBoolean($valueBoolean);
        }
        if (null !== $valueCanonical) {
            $this->setValueCanonical($valueCanonical);
        }
        if (null !== $valueCode) {
            $this->setValueCode($valueCode);
        }
        if (null !== $valueDate) {
            $this->setValueDate($valueDate);
        }
        if (null !== $valueDateTime) {
            $this->setValueDateTime($valueDateTime);
        }
        if (null !== $valueDecimal) {
            $this->setValueDecimal($valueDecimal);
        }
        if (null !== $valueId) {
            $this->setValueId($valueId);
        }
        if (null !== $valueInstant) {
            $this->setValueInstant($valueInstant);
        }
        if (null !== $valueInteger) {
            $this->setValueInteger($valueInteger);
        }
        if (null !== $valueMarkdown) {
            $this->setValueMarkdown($valueMarkdown);
        }
        if (null !== $valueOid) {
            $this->setValueOid($valueOid);
        }
        if (null !== $valuePositiveInt) {
            $this->setValuePositiveInt($valuePositiveInt);
        }
        if (null !== $valueString) {
            $this->setValueString($valueString);
        }
        if (null !== $valueTime) {
            $this->setValueTime($valueTime);
        }
        if (null !== $valueUnsignedInt) {
            $this->setValueUnsignedInt($valueUnsignedInt);
        }
        if (null !== $valueUri) {
            $this->setValueUri($valueUri);
        }
        if (null !== $valueUrl) {
            $this->setValueUrl($valueUrl);
        }
        if (null !== $valueUuid) {
            $this->setValueUuid($valueUuid);
        }
        if (null !== $valueAddress) {
            $this->setValueAddress($valueAddress);
        }
        if (null !== $valueAge) {
            $this->setValueAge($valueAge);
        }
        if (null !== $valueAnnotation) {
            $this->setValueAnnotation($valueAnnotation);
        }
        if (null !== $valueAttachment) {
            $this->setValueAttachment($valueAttachment);
        }
        if (null !== $valueCodeableConcept) {
            $this->setValueCodeableConcept($valueCodeableConcept);
        }
        if (null !== $valueCoding) {
            $this->setValueCoding($valueCoding);
        }
        if (null !== $valueContactPoint) {
            $this->setValueContactPoint($valueContactPoint);
        }
        if (null !== $valueCount) {
            $this->setValueCount($valueCount);
        }
        if (null !== $valueDistance) {
            $this->setValueDistance($valueDistance);
        }
        if (null !== $valueDuration) {
            $this->setValueDuration($valueDuration);
        }
        if (null !== $valueHumanName) {
            $this->setValueHumanName($valueHumanName);
        }
        if (null !== $valueIdentifier) {
            $this->setValueIdentifier($valueIdentifier);
        }
        if (null !== $valueMoney) {
            $this->setValueMoney($valueMoney);
        }
        if (null !== $valuePeriod) {
            $this->setValuePeriod($valuePeriod);
        }
        if (null !== $valueQuantity) {
            $this->setValueQuantity($valueQuantity);
        }
        if (null !== $valueRange) {
            $this->setValueRange($valueRange);
        }
        if (null !== $valueRatio) {
            $this->setValueRatio($valueRatio);
        }
        if (null !== $valueReference) {
            $this->setValueReference($valueReference);
        }
        if (null !== $valueSampledData) {
            $this->setValueSampledData($valueSampledData);
        }
        if (null !== $valueSignature) {
            $this->setValueSignature($valueSignature);
        }
        if (null !== $valueTiming) {
            $this->setValueTiming($valueTiming);
        }
        if (null !== $valueContactDetail) {
            $this->setValueContactDetail($valueContactDetail);
        }
        if (null !== $valueContributor) {
            $this->setValueContributor($valueContributor);
        }
        if (null !== $valueDataRequirement) {
            $this->setValueDataRequirement($valueDataRequirement);
        }
        if (null !== $valueExpression) {
            $this->setValueExpression($valueExpression);
        }
        if (null !== $valueParameterDefinition) {
            $this->setValueParameterDefinition($valueParameterDefinition);
        }
        if (null !== $valueRelatedArtifact) {
            $this->setValueRelatedArtifact($valueRelatedArtifact);
        }
        if (null !== $valueTriggerDefinition) {
            $this->setValueTriggerDefinition($valueTriggerDefinition);
        }
        if (null !== $valueUsageContext) {
            $this->setValueUsageContext($valueUsageContext);
        }
        if (null !== $valueDosage) {
            $this->setValueDosage($valueDosage);
        }
        if (null !== $valueMeta) {
            $this->setValueMeta($valueMeta);
        }
        if (null !== $resource) {
            $this->setResource($resource);
        }
        if (null !== $part) {
            $this->setPart(...$part);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the parameter (reference to the operation definition).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getName(): null|FHIRString
    {
        return $this->name ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of the parameter (reference to the operation definition).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @return static
     */
    public function setName(null|string|FHIRStringPrimitive|FHIRString $name): self
    {
        if (null === $name) {
            unset($this->name);
            return $this;
        }
        if (!($name instanceof FHIRString)) {
            $name = new FHIRString(value: $name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * A stream of bytes
     * A stream of bytes, base64 encoded
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary
     */
    public function getValueBase64Binary(): null|FHIRBase64Binary
    {
        return $this->valueBase64Binary ?? null;
    }

    /**
     * A stream of bytes
     * A stream of bytes, base64 encoded
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRBase64BinaryPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary $valueBase64Binary
     * @return static
     */
    public function setValueBase64Binary(null|string|FHIRBase64BinaryPrimitive|FHIRBase64Binary $valueBase64Binary): self
    {
        if (null === $valueBase64Binary) {
            unset($this->valueBase64Binary);
            return $this;
        }
        if (!($valueBase64Binary instanceof FHIRBase64Binary)) {
            $valueBase64Binary = new FHIRBase64Binary(value: $valueBase64Binary);
        }
        $this->valueBase64Binary = $valueBase64Binary;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getValueBoolean(): null|FHIRBoolean
    {
        return $this->valueBoolean ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $valueBoolean
     * @return static
     */
    public function setValueBoolean(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $valueBoolean): self
    {
        if (null === $valueBoolean) {
            unset($this->valueBoolean);
            return $this;
        }
        if (!($valueBoolean instanceof FHIRBoolean)) {
            $valueBoolean = new FHIRBoolean(value: $valueBoolean);
        }
        $this->valueBoolean = $valueBoolean;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getValueCanonical(): null|FHIRCanonical
    {
        return $this->valueCanonical ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $valueCanonical
     * @return static
     */
    public function setValueCanonical(null|string|FHIRCanonicalPrimitive|FHIRCanonical $valueCanonical): self
    {
        if (null === $valueCanonical) {
            unset($this->valueCanonical);
            return $this;
        }
        if (!($valueCanonical instanceof FHIRCanonical)) {
            $valueCanonical = new FHIRCanonical(value: $valueCanonical);
        }
        $this->valueCanonical = $valueCanonical;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getValueCode(): null|FHIRCode
    {
        return $this->valueCode ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $valueCode
     * @return static
     */
    public function setValueCode(null|string|FHIRCodePrimitive|FHIRCode $valueCode): self
    {
        if (null === $valueCode) {
            unset($this->valueCode);
            return $this;
        }
        if (!($valueCode instanceof FHIRCode)) {
            $valueCode = new FHIRCode(value: $valueCode);
        }
        $this->valueCode = $valueCode;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getValueDate(): null|FHIRDate
    {
        return $this->valueDate ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $valueDate
     * @return static
     */
    public function setValueDate(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $valueDate): self
    {
        if (null === $valueDate) {
            unset($this->valueDate);
            return $this;
        }
        if (!($valueDate instanceof FHIRDate)) {
            $valueDate = new FHIRDate(value: $valueDate);
        }
        $this->valueDate = $valueDate;
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
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getValueDateTime(): null|FHIRDateTime
    {
        return $this->valueDateTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $valueDateTime
     * @return static
     */
    public function setValueDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $valueDateTime): self
    {
        if (null === $valueDateTime) {
            unset($this->valueDateTime);
            return $this;
        }
        if (!($valueDateTime instanceof FHIRDateTime)) {
            $valueDateTime = new FHIRDateTime(value: $valueDateTime);
        }
        $this->valueDateTime = $valueDateTime;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getValueDecimal(): null|FHIRDecimal
    {
        return $this->valueDecimal ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $valueDecimal
     * @return static
     */
    public function setValueDecimal(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $valueDecimal): self
    {
        if (null === $valueDecimal) {
            unset($this->valueDecimal);
            return $this;
        }
        if (!($valueDecimal instanceof FHIRDecimal)) {
            $valueDecimal = new FHIRDecimal(value: $valueDecimal);
        }
        $this->valueDecimal = $valueDecimal;
        return $this;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getValueId(): null|FHIRId
    {
        return $this->valueId ?? null;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $valueId
     * @return static
     */
    public function setValueId(null|string|FHIRIdPrimitive|FHIRId $valueId): self
    {
        if (null === $valueId) {
            unset($this->valueId);
            return $this;
        }
        if (!($valueId instanceof FHIRId)) {
            $valueId = new FHIRId(value: $valueId);
        }
        $this->valueId = $valueId;
        return $this;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    public function getValueInstant(): null|FHIRInstant
    {
        return $this->valueInstant ?? null;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $valueInstant
     * @return static
     */
    public function setValueInstant(null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $valueInstant): self
    {
        if (null === $valueInstant) {
            unset($this->valueInstant);
            return $this;
        }
        if (!($valueInstant instanceof FHIRInstant)) {
            $valueInstant = new FHIRInstant(value: $valueInstant);
        }
        $this->valueInstant = $valueInstant;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getValueInteger(): null|FHIRInteger
    {
        return $this->valueInteger ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $valueInteger
     * @return static
     */
    public function setValueInteger(null|string|float|FHIRIntegerPrimitive|FHIRInteger $valueInteger): self
    {
        if (null === $valueInteger) {
            unset($this->valueInteger);
            return $this;
        }
        if (!($valueInteger instanceof FHIRInteger)) {
            $valueInteger = new FHIRInteger(value: $valueInteger);
        }
        $this->valueInteger = $valueInteger;
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
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getValueMarkdown(): null|FHIRMarkdown
    {
        return $this->valueMarkdown ?? null;
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
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $valueMarkdown
     * @return static
     */
    public function setValueMarkdown(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $valueMarkdown): self
    {
        if (null === $valueMarkdown) {
            unset($this->valueMarkdown);
            return $this;
        }
        if (!($valueMarkdown instanceof FHIRMarkdown)) {
            $valueMarkdown = new FHIRMarkdown(value: $valueMarkdown);
        }
        $this->valueMarkdown = $valueMarkdown;
        return $this;
    }

    /**
     * An OID represented as a URI
     * RFC 3001. See also ISO/IEC 8824:1990 €
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROid
     */
    public function getValueOid(): null|FHIROid
    {
        return $this->valueOid ?? null;
    }

    /**
     * An OID represented as a URI
     * RFC 3001. See also ISO/IEC 8824:1990 €
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIROidPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIROid $valueOid
     * @return static
     */
    public function setValueOid(null|string|FHIROidPrimitive|FHIROid $valueOid): self
    {
        if (null === $valueOid) {
            unset($this->valueOid);
            return $this;
        }
        if (!($valueOid instanceof FHIROid)) {
            $valueOid = new FHIROid(value: $valueOid);
        }
        $this->valueOid = $valueOid;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getValuePositiveInt(): null|FHIRPositiveInt
    {
        return $this->valuePositiveInt ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $valuePositiveInt
     * @return static
     */
    public function setValuePositiveInt(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $valuePositiveInt): self
    {
        if (null === $valuePositiveInt) {
            unset($this->valuePositiveInt);
            return $this;
        }
        if (!($valuePositiveInt instanceof FHIRPositiveInt)) {
            $valuePositiveInt = new FHIRPositiveInt(value: $valuePositiveInt);
        }
        $this->valuePositiveInt = $valuePositiveInt;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getValueString(): null|FHIRString
    {
        return $this->valueString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $valueString
     * @return static
     */
    public function setValueString(null|string|FHIRStringPrimitive|FHIRString $valueString): self
    {
        if (null === $valueString) {
            unset($this->valueString);
            return $this;
        }
        if (!($valueString instanceof FHIRString)) {
            $valueString = new FHIRString(value: $valueString);
        }
        $this->valueString = $valueString;
        return $this;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime
     */
    public function getValueTime(): null|FHIRTime
    {
        return $this->valueTime ?? null;
    }

    /**
     * A time during the day, with no date specified
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTime $valueTime
     * @return static
     */
    public function setValueTime(null|string|\DateTimeInterface|FHIRTimePrimitive|FHIRTime $valueTime): self
    {
        if (null === $valueTime) {
            unset($this->valueTime);
            return $this;
        }
        if (!($valueTime instanceof FHIRTime)) {
            $valueTime = new FHIRTime(value: $valueTime);
        }
        $this->valueTime = $valueTime;
        return $this;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    public function getValueUnsignedInt(): null|FHIRUnsignedInt
    {
        return $this->valueUnsignedInt ?? null;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $valueUnsignedInt
     * @return static
     */
    public function setValueUnsignedInt(null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $valueUnsignedInt): self
    {
        if (null === $valueUnsignedInt) {
            unset($this->valueUnsignedInt);
            return $this;
        }
        if (!($valueUnsignedInt instanceof FHIRUnsignedInt)) {
            $valueUnsignedInt = new FHIRUnsignedInt(value: $valueUnsignedInt);
        }
        $this->valueUnsignedInt = $valueUnsignedInt;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getValueUri(): null|FHIRUri
    {
        return $this->valueUri ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $valueUri
     * @return static
     */
    public function setValueUri(null|string|FHIRUriPrimitive|FHIRUri $valueUri): self
    {
        if (null === $valueUri) {
            unset($this->valueUri);
            return $this;
        }
        if (!($valueUri instanceof FHIRUri)) {
            $valueUri = new FHIRUri(value: $valueUri);
        }
        $this->valueUri = $valueUri;
        return $this;
    }

    /**
     * A URI that is a literal reference
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl
     */
    public function getValueUrl(): null|FHIRUrl
    {
        return $this->valueUrl ?? null;
    }

    /**
     * A URI that is a literal reference
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUrlPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUrl $valueUrl
     * @return static
     */
    public function setValueUrl(null|string|FHIRUrlPrimitive|FHIRUrl $valueUrl): self
    {
        if (null === $valueUrl) {
            unset($this->valueUrl);
            return $this;
        }
        if (!($valueUrl instanceof FHIRUrl)) {
            $valueUrl = new FHIRUrl(value: $valueUrl);
        }
        $this->valueUrl = $valueUrl;
        return $this;
    }

    /**
     * A UUID, represented as a URI
     * See The Open Group, CDE 1.1 Remote Procedure Call specification, Appendix A.
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUuid
     */
    public function getValueUuid(): null|FHIRUuid
    {
        return $this->valueUuid ?? null;
    }

    /**
     * A UUID, represented as a URI
     * See The Open Group, CDE 1.1 Remote Procedure Call specification, Appendix A.
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUuidPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUuid $valueUuid
     * @return static
     */
    public function setValueUuid(null|string|FHIRUuidPrimitive|FHIRUuid $valueUuid): self
    {
        if (null === $valueUuid) {
            unset($this->valueUuid);
            return $this;
        }
        if (!($valueUuid instanceof FHIRUuid)) {
            $valueUuid = new FHIRUuid(value: $valueUuid);
        }
        $this->valueUuid = $valueUuid;
        return $this;
    }

    /**
     * An address expressed using postal conventions (as opposed to GPS or other
     * location definition formats). This data type may be used to convey addresses for
     * use in delivering mail as well as for visiting locations which might not be
     * valid for mail delivery. There are a variety of postal address formats defined
     * around the world.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress
     */
    public function getValueAddress(): null|FHIRAddress
    {
        return $this->valueAddress ?? null;
    }

    /**
     * An address expressed using postal conventions (as opposed to GPS or other
     * location definition formats). This data type may be used to convey addresses for
     * use in delivering mail as well as for visiting locations which might not be
     * valid for mail delivery. There are a variety of postal address formats defined
     * around the world.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAddress $valueAddress
     * @return static
     */
    public function setValueAddress(null|FHIRAddress $valueAddress): self
    {
        if (null === $valueAddress) {
            unset($this->valueAddress);
            return $this;
        }
        $this->valueAddress = $valueAddress;
        return $this;
    }

    /**
     * A duration of time during which an organism (or a process) has existed.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge
     */
    public function getValueAge(): null|FHIRAge
    {
        return $this->valueAge ?? null;
    }

    /**
     * A duration of time during which an organism (or a process) has existed.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge $valueAge
     * @return static
     */
    public function setValueAge(null|FHIRAge $valueAge): self
    {
        if (null === $valueAge) {
            unset($this->valueAge);
            return $this;
        }
        $this->valueAge = $valueAge;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation
     */
    public function getValueAnnotation(): null|FHIRAnnotation
    {
        return $this->valueAnnotation ?? null;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation $valueAnnotation
     * @return static
     */
    public function setValueAnnotation(null|FHIRAnnotation $valueAnnotation): self
    {
        if (null === $valueAnnotation) {
            unset($this->valueAnnotation);
            return $this;
        }
        $this->valueAnnotation = $valueAnnotation;
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    public function getValueAttachment(): null|FHIRAttachment
    {
        return $this->valueAttachment ?? null;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $valueAttachment
     * @return static
     */
    public function setValueAttachment(null|FHIRAttachment $valueAttachment): self
    {
        if (null === $valueAttachment) {
            unset($this->valueAttachment);
            return $this;
        }
        $this->valueAttachment = $valueAttachment;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getValueCodeableConcept(): null|FHIRCodeableConcept
    {
        return $this->valueCodeableConcept ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $valueCodeableConcept
     * @return static
     */
    public function setValueCodeableConcept(null|FHIRCodeableConcept $valueCodeableConcept): self
    {
        if (null === $valueCodeableConcept) {
            unset($this->valueCodeableConcept);
            return $this;
        }
        $this->valueCodeableConcept = $valueCodeableConcept;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    public function getValueCoding(): null|FHIRCoding
    {
        return $this->valueCoding ?? null;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $valueCoding
     * @return static
     */
    public function setValueCoding(null|FHIRCoding $valueCoding): self
    {
        if (null === $valueCoding) {
            unset($this->valueCoding);
            return $this;
        }
        $this->valueCoding = $valueCoding;
        return $this;
    }

    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint
     */
    public function getValueContactPoint(): null|FHIRContactPoint
    {
        return $this->valueContactPoint ?? null;
    }

    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint $valueContactPoint
     * @return static
     */
    public function setValueContactPoint(null|FHIRContactPoint $valueContactPoint): self
    {
        if (null === $valueContactPoint) {
            unset($this->valueContactPoint);
            return $this;
        }
        $this->valueContactPoint = $valueContactPoint;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRCount
     */
    public function getValueCount(): null|FHIRCount
    {
        return $this->valueCount ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRCount $valueCount
     * @return static
     */
    public function setValueCount(null|FHIRCount $valueCount): self
    {
        if (null === $valueCount) {
            unset($this->valueCount);
            return $this;
        }
        $this->valueCount = $valueCount;
        return $this;
    }

    /**
     * A length - a value with a unit that is a physical distance.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDistance
     */
    public function getValueDistance(): null|FHIRDistance
    {
        return $this->valueDistance ?? null;
    }

    /**
     * A length - a value with a unit that is a physical distance.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDistance $valueDistance
     * @return static
     */
    public function setValueDistance(null|FHIRDistance $valueDistance): self
    {
        if (null === $valueDistance) {
            unset($this->valueDistance);
            return $this;
        }
        $this->valueDistance = $valueDistance;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getValueDuration(): null|FHIRDuration
    {
        return $this->valueDuration ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $valueDuration
     * @return static
     */
    public function setValueDuration(null|FHIRDuration $valueDuration): self
    {
        if (null === $valueDuration) {
            unset($this->valueDuration);
            return $this;
        }
        $this->valueDuration = $valueDuration;
        return $this;
    }

    /**
     * A human's name with the ability to identify parts and usage.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName
     */
    public function getValueHumanName(): null|FHIRHumanName
    {
        return $this->valueHumanName ?? null;
    }

    /**
     * A human's name with the ability to identify parts and usage.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHumanName $valueHumanName
     * @return static
     */
    public function setValueHumanName(null|FHIRHumanName $valueHumanName): self
    {
        if (null === $valueHumanName) {
            unset($this->valueHumanName);
            return $this;
        }
        $this->valueHumanName = $valueHumanName;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getValueIdentifier(): null|FHIRIdentifier
    {
        return $this->valueIdentifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $valueIdentifier
     * @return static
     */
    public function setValueIdentifier(null|FHIRIdentifier $valueIdentifier): self
    {
        if (null === $valueIdentifier) {
            unset($this->valueIdentifier);
            return $this;
        }
        $this->valueIdentifier = $valueIdentifier;
        return $this;
    }

    /**
     * An amount of economic utility in some recognized currency.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney
     */
    public function getValueMoney(): null|FHIRMoney
    {
        return $this->valueMoney ?? null;
    }

    /**
     * An amount of economic utility in some recognized currency.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney $valueMoney
     * @return static
     */
    public function setValueMoney(null|FHIRMoney $valueMoney): self
    {
        if (null === $valueMoney) {
            unset($this->valueMoney);
            return $this;
        }
        $this->valueMoney = $valueMoney;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getValuePeriod(): null|FHIRPeriod
    {
        return $this->valuePeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $valuePeriod
     * @return static
     */
    public function setValuePeriod(null|FHIRPeriod $valuePeriod): self
    {
        if (null === $valuePeriod) {
            unset($this->valuePeriod);
            return $this;
        }
        $this->valuePeriod = $valuePeriod;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getValueQuantity(): null|FHIRQuantity
    {
        return $this->valueQuantity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $valueQuantity
     * @return static
     */
    public function setValueQuantity(null|FHIRQuantity $valueQuantity): self
    {
        if (null === $valueQuantity) {
            unset($this->valueQuantity);
            return $this;
        }
        $this->valueQuantity = $valueQuantity;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getValueRange(): null|FHIRRange
    {
        return $this->valueRange ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $valueRange
     * @return static
     */
    public function setValueRange(null|FHIRRange $valueRange): self
    {
        if (null === $valueRange) {
            unset($this->valueRange);
            return $this;
        }
        $this->valueRange = $valueRange;
        return $this;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    public function getValueRatio(): null|FHIRRatio
    {
        return $this->valueRatio ?? null;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $valueRatio
     * @return static
     */
    public function setValueRatio(null|FHIRRatio $valueRatio): self
    {
        if (null === $valueRatio) {
            unset($this->valueRatio);
            return $this;
        }
        $this->valueRatio = $valueRatio;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getValueReference(): null|FHIRReference
    {
        return $this->valueReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $valueReference
     * @return static
     */
    public function setValueReference(null|FHIRReference $valueReference): self
    {
        if (null === $valueReference) {
            unset($this->valueReference);
            return $this;
        }
        $this->valueReference = $valueReference;
        return $this;
    }

    /**
     * A series of measurements taken by a device, with upper and lower limits. There
     * may be more than one dimension in the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSampledData
     */
    public function getValueSampledData(): null|FHIRSampledData
    {
        return $this->valueSampledData ?? null;
    }

    /**
     * A series of measurements taken by a device, with upper and lower limits. There
     * may be more than one dimension in the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSampledData $valueSampledData
     * @return static
     */
    public function setValueSampledData(null|FHIRSampledData $valueSampledData): self
    {
        if (null === $valueSampledData) {
            unset($this->valueSampledData);
            return $this;
        }
        $this->valueSampledData = $valueSampledData;
        return $this;
    }

    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature
     */
    public function getValueSignature(): null|FHIRSignature
    {
        return $this->valueSignature ?? null;
    }

    /**
     * A signature along with supporting context. The signature may be a digital
     * signature that is cryptographic in nature, or some other signature acceptable to
     * the domain. This other signature may be as simple as a graphical image
     * representing a hand-written signature, or a signature ceremony Different
     * signature approaches have different utilities.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSignature $valueSignature
     * @return static
     */
    public function setValueSignature(null|FHIRSignature $valueSignature): self
    {
        if (null === $valueSignature) {
            unset($this->valueSignature);
            return $this;
        }
        $this->valueSignature = $valueSignature;
        return $this;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getValueTiming(): null|FHIRTiming
    {
        return $this->valueTiming ?? null;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $valueTiming
     * @return static
     */
    public function setValueTiming(null|FHIRTiming $valueTiming): self
    {
        if (null === $valueTiming) {
            unset($this->valueTiming);
            return $this;
        }
        $this->valueTiming = $valueTiming;
        return $this;
    }

    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail
     */
    public function getValueContactDetail(): null|FHIRContactDetail
    {
        return $this->valueContactDetail ?? null;
    }

    /**
     * Specifies contact information for a person or organization.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactDetail $valueContactDetail
     * @return static
     */
    public function setValueContactDetail(null|FHIRContactDetail $valueContactDetail): self
    {
        if (null === $valueContactDetail) {
            unset($this->valueContactDetail);
            return $this;
        }
        $this->valueContactDetail = $valueContactDetail;
        return $this;
    }

    /**
     * A contributor to the content of a knowledge asset, including authors, editors,
     * reviewers, and endorsers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContributor
     */
    public function getValueContributor(): null|FHIRContributor
    {
        return $this->valueContributor ?? null;
    }

    /**
     * A contributor to the content of a knowledge asset, including authors, editors,
     * reviewers, and endorsers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContributor $valueContributor
     * @return static
     */
    public function setValueContributor(null|FHIRContributor $valueContributor): self
    {
        if (null === $valueContributor) {
            unset($this->valueContributor);
            return $this;
        }
        $this->valueContributor = $valueContributor;
        return $this;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement
     */
    public function getValueDataRequirement(): null|FHIRDataRequirement
    {
        return $this->valueDataRequirement ?? null;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement $valueDataRequirement
     * @return static
     */
    public function setValueDataRequirement(null|FHIRDataRequirement $valueDataRequirement): self
    {
        if (null === $valueDataRequirement) {
            unset($this->valueDataRequirement);
            return $this;
        }
        $this->valueDataRequirement = $valueDataRequirement;
        return $this;
    }

    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression
     */
    public function getValueExpression(): null|FHIRExpression
    {
        return $this->valueExpression ?? null;
    }

    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression $valueExpression
     * @return static
     */
    public function setValueExpression(null|FHIRExpression $valueExpression): self
    {
        if (null === $valueExpression) {
            unset($this->valueExpression);
            return $this;
        }
        $this->valueExpression = $valueExpression;
        return $this;
    }

    /**
     * The parameters to the module. This collection specifies both the input and
     * output parameters. Input parameters are provided by the caller as part of the
     * $evaluate operation. Output parameters are included in the GuidanceResponse.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRParameterDefinition
     */
    public function getValueParameterDefinition(): null|FHIRParameterDefinition
    {
        return $this->valueParameterDefinition ?? null;
    }

    /**
     * The parameters to the module. This collection specifies both the input and
     * output parameters. Input parameters are provided by the caller as part of the
     * $evaluate operation. Output parameters are included in the GuidanceResponse.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRParameterDefinition $valueParameterDefinition
     * @return static
     */
    public function setValueParameterDefinition(null|FHIRParameterDefinition $valueParameterDefinition): self
    {
        if (null === $valueParameterDefinition) {
            unset($this->valueParameterDefinition);
            return $this;
        }
        $this->valueParameterDefinition = $valueParameterDefinition;
        return $this;
    }

    /**
     * Related artifacts such as additional documentation, justification, or
     * bibliographic references.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact
     */
    public function getValueRelatedArtifact(): null|FHIRRelatedArtifact
    {
        return $this->valueRelatedArtifact ?? null;
    }

    /**
     * Related artifacts such as additional documentation, justification, or
     * bibliographic references.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRelatedArtifact $valueRelatedArtifact
     * @return static
     */
    public function setValueRelatedArtifact(null|FHIRRelatedArtifact $valueRelatedArtifact): self
    {
        if (null === $valueRelatedArtifact) {
            unset($this->valueRelatedArtifact);
            return $this;
        }
        $this->valueRelatedArtifact = $valueRelatedArtifact;
        return $this;
    }

    /**
     * A description of a triggering event. Triggering events can be named events, data
     * events, or periodic, as determined by the type element.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition
     */
    public function getValueTriggerDefinition(): null|FHIRTriggerDefinition
    {
        return $this->valueTriggerDefinition ?? null;
    }

    /**
     * A description of a triggering event. Triggering events can be named events, data
     * events, or periodic, as determined by the type element.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition $valueTriggerDefinition
     * @return static
     */
    public function setValueTriggerDefinition(null|FHIRTriggerDefinition $valueTriggerDefinition): self
    {
        if (null === $valueTriggerDefinition) {
            unset($this->valueTriggerDefinition);
            return $this;
        }
        $this->valueTriggerDefinition = $valueTriggerDefinition;
        return $this;
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext
     */
    public function getValueUsageContext(): null|FHIRUsageContext
    {
        return $this->valueUsageContext ?? null;
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext $valueUsageContext
     * @return static
     */
    public function setValueUsageContext(null|FHIRUsageContext $valueUsageContext): self
    {
        if (null === $valueUsageContext) {
            unset($this->valueUsageContext);
            return $this;
        }
        $this->valueUsageContext = $valueUsageContext;
        return $this;
    }

    /**
     * Indicates how the medication is/was taken or should be taken by the patient.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDosage
     */
    public function getValueDosage(): null|FHIRDosage
    {
        return $this->valueDosage ?? null;
    }

    /**
     * Indicates how the medication is/was taken or should be taken by the patient.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDosage $valueDosage
     * @return static
     */
    public function setValueDosage(null|FHIRDosage $valueDosage): self
    {
        if (null === $valueDosage) {
            unset($this->valueDosage);
            return $this;
        }
        $this->valueDosage = $valueDosage;
        return $this;
    }

    /**
     * The metadata about a resource. This is content in the resource that is
     * maintained by the infrastructure. Changes to the content might not always be
     * associated with version changes to the resource.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta
     */
    public function getValueMeta(): null|FHIRMeta
    {
        return $this->valueMeta ?? null;
    }

    /**
     * The metadata about a resource. This is content in the resource that is
     * maintained by the infrastructure. Changes to the content might not always be
     * associated with version changes to the resource.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If the parameter is a data type. (choose any one of value*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $valueMeta
     * @return static
     */
    public function setValueMeta(null|FHIRMeta $valueMeta): self
    {
        if (null === $valueMeta) {
            unset($this->valueMeta);
            return $this;
        }
        $this->valueMeta = $valueMeta;
        return $this;
    }

    /**
     * (choose any one of the elements, but only one)
     *
     * If the parameter is a whole resource.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface
     */
    public function getResource(): null|VersionContainedTypeInterface
    {
        return $this->resource ?? null;
    }

    /**
     * (choose any one of the elements, but only one)
     *
     * If the parameter is a whole resource.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $resource
     * @return static
     */
    public function setResource(null|FHIRResourceContainer|VersionContainedTypeInterface $resource): self
    {
        if (null === $resource) {
            unset($this->resource);
            return $this;
        }
        if ($resource instanceof FHIRResourceContainer) {
            $resource = $resource->getContainedType();
        }
        $this->resource = $resource;
        return $this;
    }

    /**
     * This resource is a non-persisted resource used to pass information into and back
     * from an [operation](operations.html). It has no other use, and there is no
     * RESTful endpoint associated with it.
     *
     * A named part of a multi-part parameter.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter>
     */
    public function getPart(): array
    {
        return $this->part ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter>
     */
    public function getPartIterator(): iterable
    {
        if (!isset($this->part)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->part);
    }

    /**
     * This resource is a non-persisted resource used to pass information into and back
     * from an [operation](operations.html). It has no other use, and there is no
     * RESTful endpoint associated with it.
     *
     * A named part of a multi-part parameter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter $part
     * @return static
     */
    public function addPart(FHIRParametersParameter $part): self
    {
        if (!isset($this->part)) {
            $this->part = [];
        }
        $this->part[] = $part;
        return $this;
    }

    /**
     * This resource is a non-persisted resource used to pass information into and back
     * from an [operation](operations.html). It has no other use, and there is no
     * RESTful endpoint associated with it.
     *
     * A named part of a multi-part parameter.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter ...$part
     * @return static
     */
    public function setPart(FHIRParametersParameter ...$part): self
    {
        if ([] === $part) {
            unset($this->part);
            return $this;
        }
        $this->part = $part;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRParametersParameter)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ID === $cen) {
                $va = $ce->attributes()[FHIRStringPrimitive::FIELD_VALUE] ?? null;
                if (null !== $va) {
                    $type->setId((string)$va);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_ATTRIBUTE);
                } else {
                    $type->setId((string)$ce);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_VALUE);
                }
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_BASE_64BINARY === $cen) {
                $type->setValueBase64Binary(FHIRBase64Binary::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_BOOLEAN === $cen) {
                $type->setValueBoolean(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_CANONICAL === $cen) {
                $type->setValueCanonical(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_CODE === $cen) {
                $type->setValueCode(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_DATE === $cen) {
                $type->setValueDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_DATE_TIME === $cen) {
                $type->setValueDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_DECIMAL === $cen) {
                $type->setValueDecimal(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_ID === $cen) {
                $type->setValueId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_INSTANT === $cen) {
                $type->setValueInstant(FHIRInstant::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_INTEGER === $cen) {
                $type->setValueInteger(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_MARKDOWN === $cen) {
                $type->setValueMarkdown(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_OID === $cen) {
                $type->setValueOid(FHIROid::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_POSITIVE_INT === $cen) {
                $type->setValuePositiveInt(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_STRING === $cen) {
                $type->setValueString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_TIME === $cen) {
                $type->setValueTime(FHIRTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_UNSIGNED_INT === $cen) {
                $type->setValueUnsignedInt(FHIRUnsignedInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_URI === $cen) {
                $type->setValueUri(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_URL === $cen) {
                $type->setValueUrl(FHIRUrl::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_UUID === $cen) {
                $type->setValueUuid(FHIRUuid::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_ADDRESS === $cen) {
                $type->setValueAddress(FHIRAddress::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_AGE === $cen) {
                $type->setValueAge(FHIRAge::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_ANNOTATION === $cen) {
                $type->setValueAnnotation(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_ATTACHMENT === $cen) {
                $type->setValueAttachment(FHIRAttachment::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_CODEABLE_CONCEPT === $cen) {
                $type->setValueCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_CODING === $cen) {
                $type->setValueCoding(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_CONTACT_POINT === $cen) {
                $type->setValueContactPoint(FHIRContactPoint::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_COUNT === $cen) {
                $type->setValueCount(FHIRCount::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_DISTANCE === $cen) {
                $type->setValueDistance(FHIRDistance::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_DURATION === $cen) {
                $type->setValueDuration(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_HUMAN_NAME === $cen) {
                $type->setValueHumanName(FHIRHumanName::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_IDENTIFIER === $cen) {
                $type->setValueIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_MONEY === $cen) {
                $type->setValueMoney(FHIRMoney::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_PERIOD === $cen) {
                $type->setValuePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_QUANTITY === $cen) {
                $type->setValueQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_RANGE === $cen) {
                $type->setValueRange(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_RATIO === $cen) {
                $type->setValueRatio(FHIRRatio::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_REFERENCE === $cen) {
                $type->setValueReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_SAMPLED_DATA === $cen) {
                $type->setValueSampledData(FHIRSampledData::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_SIGNATURE === $cen) {
                $type->setValueSignature(FHIRSignature::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_TIMING === $cen) {
                $type->setValueTiming(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_CONTACT_DETAIL === $cen) {
                $type->setValueContactDetail(FHIRContactDetail::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_CONTRIBUTOR === $cen) {
                $type->setValueContributor(FHIRContributor::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_DATA_REQUIREMENT === $cen) {
                $type->setValueDataRequirement(FHIRDataRequirement::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_EXPRESSION === $cen) {
                $type->setValueExpression(FHIRExpression::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_PARAMETER_DEFINITION === $cen) {
                $type->setValueParameterDefinition(FHIRParameterDefinition::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_RELATED_ARTIFACT === $cen) {
                $type->setValueRelatedArtifact(FHIRRelatedArtifact::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_TRIGGER_DEFINITION === $cen) {
                $type->setValueTriggerDefinition(FHIRTriggerDefinition::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_USAGE_CONTEXT === $cen) {
                $type->setValueUsageContext(FHIRUsageContext::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_DOSAGE === $cen) {
                $type->setValueDosage(FHIRDosage::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE_META === $cen) {
                $type->setValueMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESOURCE === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->setResource($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_PART === $cen) {
                $type->addPart(FHIRParametersParameter::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_BASE_64BINARY])) {
            if (isset($type->valueBase64Binary)) {
                $type->valueBase64Binary->setValue((string)$attributes[self::FIELD_VALUE_BASE_64BINARY]);
            } else {
                $type->setValueBase64Binary((string)$attributes[self::FIELD_VALUE_BASE_64BINARY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_BASE_64BINARY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_BOOLEAN])) {
            if (isset($type->valueBoolean)) {
                $type->valueBoolean->setValue((string)$attributes[self::FIELD_VALUE_BOOLEAN]);
            } else {
                $type->setValueBoolean((string)$attributes[self::FIELD_VALUE_BOOLEAN]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_BOOLEAN, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_CANONICAL])) {
            if (isset($type->valueCanonical)) {
                $type->valueCanonical->setValue((string)$attributes[self::FIELD_VALUE_CANONICAL]);
            } else {
                $type->setValueCanonical((string)$attributes[self::FIELD_VALUE_CANONICAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_CANONICAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_CODE])) {
            if (isset($type->valueCode)) {
                $type->valueCode->setValue((string)$attributes[self::FIELD_VALUE_CODE]);
            } else {
                $type->setValueCode((string)$attributes[self::FIELD_VALUE_CODE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_CODE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_DATE])) {
            if (isset($type->valueDate)) {
                $type->valueDate->setValue((string)$attributes[self::FIELD_VALUE_DATE]);
            } else {
                $type->setValueDate((string)$attributes[self::FIELD_VALUE_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_DATE_TIME])) {
            if (isset($type->valueDateTime)) {
                $type->valueDateTime->setValue((string)$attributes[self::FIELD_VALUE_DATE_TIME]);
            } else {
                $type->setValueDateTime((string)$attributes[self::FIELD_VALUE_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_DECIMAL])) {
            if (isset($type->valueDecimal)) {
                $type->valueDecimal->setValue((string)$attributes[self::FIELD_VALUE_DECIMAL]);
            } else {
                $type->setValueDecimal((string)$attributes[self::FIELD_VALUE_DECIMAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_DECIMAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_ID])) {
            if (isset($type->valueId)) {
                $type->valueId->setValue((string)$attributes[self::FIELD_VALUE_ID]);
            } else {
                $type->setValueId((string)$attributes[self::FIELD_VALUE_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_INSTANT])) {
            if (isset($type->valueInstant)) {
                $type->valueInstant->setValue((string)$attributes[self::FIELD_VALUE_INSTANT]);
            } else {
                $type->setValueInstant((string)$attributes[self::FIELD_VALUE_INSTANT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_INSTANT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_INTEGER])) {
            if (isset($type->valueInteger)) {
                $type->valueInteger->setValue((string)$attributes[self::FIELD_VALUE_INTEGER]);
            } else {
                $type->setValueInteger((string)$attributes[self::FIELD_VALUE_INTEGER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_INTEGER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_MARKDOWN])) {
            if (isset($type->valueMarkdown)) {
                $type->valueMarkdown->setValue((string)$attributes[self::FIELD_VALUE_MARKDOWN]);
            } else {
                $type->setValueMarkdown((string)$attributes[self::FIELD_VALUE_MARKDOWN]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_MARKDOWN, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_OID])) {
            if (isset($type->valueOid)) {
                $type->valueOid->setValue((string)$attributes[self::FIELD_VALUE_OID]);
            } else {
                $type->setValueOid((string)$attributes[self::FIELD_VALUE_OID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_OID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_POSITIVE_INT])) {
            if (isset($type->valuePositiveInt)) {
                $type->valuePositiveInt->setValue((string)$attributes[self::FIELD_VALUE_POSITIVE_INT]);
            } else {
                $type->setValuePositiveInt((string)$attributes[self::FIELD_VALUE_POSITIVE_INT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_POSITIVE_INT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_STRING])) {
            if (isset($type->valueString)) {
                $type->valueString->setValue((string)$attributes[self::FIELD_VALUE_STRING]);
            } else {
                $type->setValueString((string)$attributes[self::FIELD_VALUE_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_TIME])) {
            if (isset($type->valueTime)) {
                $type->valueTime->setValue((string)$attributes[self::FIELD_VALUE_TIME]);
            } else {
                $type->setValueTime((string)$attributes[self::FIELD_VALUE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_UNSIGNED_INT])) {
            if (isset($type->valueUnsignedInt)) {
                $type->valueUnsignedInt->setValue((string)$attributes[self::FIELD_VALUE_UNSIGNED_INT]);
            } else {
                $type->setValueUnsignedInt((string)$attributes[self::FIELD_VALUE_UNSIGNED_INT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_UNSIGNED_INT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_URI])) {
            if (isset($type->valueUri)) {
                $type->valueUri->setValue((string)$attributes[self::FIELD_VALUE_URI]);
            } else {
                $type->setValueUri((string)$attributes[self::FIELD_VALUE_URI]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_URI, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_URL])) {
            if (isset($type->valueUrl)) {
                $type->valueUrl->setValue((string)$attributes[self::FIELD_VALUE_URL]);
            } else {
                $type->setValueUrl((string)$attributes[self::FIELD_VALUE_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE_UUID])) {
            if (isset($type->valueUuid)) {
                $type->valueUuid->setValue((string)$attributes[self::FIELD_VALUE_UUID]);
            } else {
                $type->setValueUuid((string)$attributes[self::FIELD_VALUE_UUID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE_UUID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param \OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param \OpenEMR\FHIR\Encoding\SerializeConfig $config
     */
    public function xmlSerialize(XMLWriter $xw,
                                 SerializeConfig $config): void
    {
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->valueBase64Binary) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_BASE_64BINARY]) {
            $xw->writeAttribute(self::FIELD_VALUE_BASE_64BINARY, $this->valueBase64Binary->_getValueAsString());
        }
        if (isset($this->valueBoolean) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_BOOLEAN]) {
            $xw->writeAttribute(self::FIELD_VALUE_BOOLEAN, $this->valueBoolean->_getValueAsString());
        }
        if (isset($this->valueCanonical) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_CANONICAL]) {
            $xw->writeAttribute(self::FIELD_VALUE_CANONICAL, $this->valueCanonical->_getValueAsString());
        }
        if (isset($this->valueCode) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_CODE]) {
            $xw->writeAttribute(self::FIELD_VALUE_CODE, $this->valueCode->_getValueAsString());
        }
        if (isset($this->valueDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_DATE]) {
            $xw->writeAttribute(self::FIELD_VALUE_DATE, $this->valueDate->_getValueAsString());
        }
        if (isset($this->valueDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_VALUE_DATE_TIME, $this->valueDateTime->_getValueAsString());
        }
        if (isset($this->valueDecimal) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_DECIMAL]) {
            $xw->writeAttribute(self::FIELD_VALUE_DECIMAL, $this->valueDecimal->_getValueAsString());
        }
        if (isset($this->valueId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_ID]) {
            $xw->writeAttribute(self::FIELD_VALUE_ID, $this->valueId->_getValueAsString());
        }
        if (isset($this->valueInstant) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_INSTANT]) {
            $xw->writeAttribute(self::FIELD_VALUE_INSTANT, $this->valueInstant->_getValueAsString());
        }
        if (isset($this->valueInteger) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_INTEGER]) {
            $xw->writeAttribute(self::FIELD_VALUE_INTEGER, $this->valueInteger->_getValueAsString());
        }
        if (isset($this->valueMarkdown) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_MARKDOWN]) {
            $xw->writeAttribute(self::FIELD_VALUE_MARKDOWN, $this->valueMarkdown->_getValueAsString());
        }
        if (isset($this->valueOid) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_OID]) {
            $xw->writeAttribute(self::FIELD_VALUE_OID, $this->valueOid->_getValueAsString());
        }
        if (isset($this->valuePositiveInt) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_POSITIVE_INT]) {
            $xw->writeAttribute(self::FIELD_VALUE_POSITIVE_INT, $this->valuePositiveInt->_getValueAsString());
        }
        if (isset($this->valueString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_STRING]) {
            $xw->writeAttribute(self::FIELD_VALUE_STRING, $this->valueString->_getValueAsString());
        }
        if (isset($this->valueTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_TIME]) {
            $xw->writeAttribute(self::FIELD_VALUE_TIME, $this->valueTime->_getValueAsString());
        }
        if (isset($this->valueUnsignedInt) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_UNSIGNED_INT]) {
            $xw->writeAttribute(self::FIELD_VALUE_UNSIGNED_INT, $this->valueUnsignedInt->_getValueAsString());
        }
        if (isset($this->valueUri) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_URI]) {
            $xw->writeAttribute(self::FIELD_VALUE_URI, $this->valueUri->_getValueAsString());
        }
        if (isset($this->valueUrl) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_URL]) {
            $xw->writeAttribute(self::FIELD_VALUE_URL, $this->valueUrl->_getValueAsString());
        }
        if (isset($this->valueUuid) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE_UUID]) {
            $xw->writeAttribute(self::FIELD_VALUE_UUID, $this->valueUuid->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->valueBase64Binary)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_BASE_64BINARY]
                || $this->valueBase64Binary->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_BASE_64BINARY);
            $this->valueBase64Binary->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_BASE_64BINARY]);
            $xw->endElement();
        }
        if (isset($this->valueBoolean)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_BOOLEAN]
                || $this->valueBoolean->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_BOOLEAN);
            $this->valueBoolean->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_BOOLEAN]);
            $xw->endElement();
        }
        if (isset($this->valueCanonical)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_CANONICAL]
                || $this->valueCanonical->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_CANONICAL);
            $this->valueCanonical->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_CANONICAL]);
            $xw->endElement();
        }
        if (isset($this->valueCode)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_CODE]
                || $this->valueCode->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_CODE);
            $this->valueCode->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_CODE]);
            $xw->endElement();
        }
        if (isset($this->valueDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_DATE]
                || $this->valueDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_DATE);
            $this->valueDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_DATE]);
            $xw->endElement();
        }
        if (isset($this->valueDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_DATE_TIME]
                || $this->valueDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_DATE_TIME);
            $this->valueDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->valueDecimal)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_DECIMAL]
                || $this->valueDecimal->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_DECIMAL);
            $this->valueDecimal->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_DECIMAL]);
            $xw->endElement();
        }
        if (isset($this->valueId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_ID]
                || $this->valueId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_ID);
            $this->valueId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_ID]);
            $xw->endElement();
        }
        if (isset($this->valueInstant)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_INSTANT]
                || $this->valueInstant->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_INSTANT);
            $this->valueInstant->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_INSTANT]);
            $xw->endElement();
        }
        if (isset($this->valueInteger)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_INTEGER]
                || $this->valueInteger->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_INTEGER);
            $this->valueInteger->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_INTEGER]);
            $xw->endElement();
        }
        if (isset($this->valueMarkdown)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_MARKDOWN]
                || $this->valueMarkdown->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_MARKDOWN);
            $this->valueMarkdown->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_MARKDOWN]);
            $xw->endElement();
        }
        if (isset($this->valueOid)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_OID]
                || $this->valueOid->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_OID);
            $this->valueOid->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_OID]);
            $xw->endElement();
        }
        if (isset($this->valuePositiveInt)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_POSITIVE_INT]
                || $this->valuePositiveInt->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_POSITIVE_INT);
            $this->valuePositiveInt->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_POSITIVE_INT]);
            $xw->endElement();
        }
        if (isset($this->valueString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_STRING]
                || $this->valueString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_STRING);
            $this->valueString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_STRING]);
            $xw->endElement();
        }
        if (isset($this->valueTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_TIME]
                || $this->valueTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_TIME);
            $this->valueTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_TIME]);
            $xw->endElement();
        }
        if (isset($this->valueUnsignedInt)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_UNSIGNED_INT]
                || $this->valueUnsignedInt->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_UNSIGNED_INT);
            $this->valueUnsignedInt->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_UNSIGNED_INT]);
            $xw->endElement();
        }
        if (isset($this->valueUri)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_URI]
                || $this->valueUri->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_URI);
            $this->valueUri->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_URI]);
            $xw->endElement();
        }
        if (isset($this->valueUrl)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_URL]
                || $this->valueUrl->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_URL);
            $this->valueUrl->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_URL]);
            $xw->endElement();
        }
        if (isset($this->valueUuid)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE_UUID]
                || $this->valueUuid->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE_UUID);
            $this->valueUuid->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE_UUID]);
            $xw->endElement();
        }
        if (isset($this->valueAddress)) {
            $xw->startElement(self::FIELD_VALUE_ADDRESS);
            $this->valueAddress->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueAge)) {
            $xw->startElement(self::FIELD_VALUE_AGE);
            $this->valueAge->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueAnnotation)) {
            $xw->startElement(self::FIELD_VALUE_ANNOTATION);
            $this->valueAnnotation->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueAttachment)) {
            $xw->startElement(self::FIELD_VALUE_ATTACHMENT);
            $this->valueAttachment->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueCodeableConcept)) {
            $xw->startElement(self::FIELD_VALUE_CODEABLE_CONCEPT);
            $this->valueCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueCoding)) {
            $xw->startElement(self::FIELD_VALUE_CODING);
            $this->valueCoding->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueContactPoint)) {
            $xw->startElement(self::FIELD_VALUE_CONTACT_POINT);
            $this->valueContactPoint->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueCount)) {
            $xw->startElement(self::FIELD_VALUE_COUNT);
            $this->valueCount->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueDistance)) {
            $xw->startElement(self::FIELD_VALUE_DISTANCE);
            $this->valueDistance->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueDuration)) {
            $xw->startElement(self::FIELD_VALUE_DURATION);
            $this->valueDuration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueHumanName)) {
            $xw->startElement(self::FIELD_VALUE_HUMAN_NAME);
            $this->valueHumanName->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueIdentifier)) {
            $xw->startElement(self::FIELD_VALUE_IDENTIFIER);
            $this->valueIdentifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueMoney)) {
            $xw->startElement(self::FIELD_VALUE_MONEY);
            $this->valueMoney->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valuePeriod)) {
            $xw->startElement(self::FIELD_VALUE_PERIOD);
            $this->valuePeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueQuantity)) {
            $xw->startElement(self::FIELD_VALUE_QUANTITY);
            $this->valueQuantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueRange)) {
            $xw->startElement(self::FIELD_VALUE_RANGE);
            $this->valueRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueRatio)) {
            $xw->startElement(self::FIELD_VALUE_RATIO);
            $this->valueRatio->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueReference)) {
            $xw->startElement(self::FIELD_VALUE_REFERENCE);
            $this->valueReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueSampledData)) {
            $xw->startElement(self::FIELD_VALUE_SAMPLED_DATA);
            $this->valueSampledData->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueSignature)) {
            $xw->startElement(self::FIELD_VALUE_SIGNATURE);
            $this->valueSignature->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueTiming)) {
            $xw->startElement(self::FIELD_VALUE_TIMING);
            $this->valueTiming->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueContactDetail)) {
            $xw->startElement(self::FIELD_VALUE_CONTACT_DETAIL);
            $this->valueContactDetail->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueContributor)) {
            $xw->startElement(self::FIELD_VALUE_CONTRIBUTOR);
            $this->valueContributor->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueDataRequirement)) {
            $xw->startElement(self::FIELD_VALUE_DATA_REQUIREMENT);
            $this->valueDataRequirement->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueExpression)) {
            $xw->startElement(self::FIELD_VALUE_EXPRESSION);
            $this->valueExpression->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueParameterDefinition)) {
            $xw->startElement(self::FIELD_VALUE_PARAMETER_DEFINITION);
            $this->valueParameterDefinition->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueRelatedArtifact)) {
            $xw->startElement(self::FIELD_VALUE_RELATED_ARTIFACT);
            $this->valueRelatedArtifact->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueTriggerDefinition)) {
            $xw->startElement(self::FIELD_VALUE_TRIGGER_DEFINITION);
            $this->valueTriggerDefinition->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueUsageContext)) {
            $xw->startElement(self::FIELD_VALUE_USAGE_CONTEXT);
            $this->valueUsageContext->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueDosage)) {
            $xw->startElement(self::FIELD_VALUE_DOSAGE);
            $this->valueDosage->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->valueMeta)) {
            $xw->startElement(self::FIELD_VALUE_META);
            $this->valueMeta->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->resource)) {
            $xw->startElement(self::FIELD_RESOURCE);
            $xw->startElement($this->resource->_getFHIRTypeName());
            $this->resource->xmlSerialize($xw, $config);
            $xw->endElement();
            $xw->endElement();
        }
        if (isset($this->part)) {
            foreach ($this->part as $v) {
                $xw->startElement(self::FIELD_PART);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRParameters\FHIRParametersParameter
     * @throws \Exception
     */
    public static function jsonUnserialize(\stdClass $decoded,
                                           UnserializeConfig $config,
                                           null|ElementTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIRParametersParameter)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueBase64Binary)
            || isset($decoded->_valueBase64Binary)
            || property_exists($decoded, self::FIELD_VALUE_BASE_64BINARY)
            || property_exists($decoded, self::FIELD_VALUE_BASE_64BINARY_EXT)) {
            $v = $decoded->_valueBase64Binary ?? new \stdClass();
            $v->value = $decoded->valueBase64Binary ?? null;
            $type->setValueBase64Binary(FHIRBase64Binary::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueBoolean)
            || isset($decoded->_valueBoolean)
            || property_exists($decoded, self::FIELD_VALUE_BOOLEAN)
            || property_exists($decoded, self::FIELD_VALUE_BOOLEAN_EXT)) {
            $v = $decoded->_valueBoolean ?? new \stdClass();
            $v->value = $decoded->valueBoolean ?? null;
            $type->setValueBoolean(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueCanonical)
            || isset($decoded->_valueCanonical)
            || property_exists($decoded, self::FIELD_VALUE_CANONICAL)
            || property_exists($decoded, self::FIELD_VALUE_CANONICAL_EXT)) {
            $v = $decoded->_valueCanonical ?? new \stdClass();
            $v->value = $decoded->valueCanonical ?? null;
            $type->setValueCanonical(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueCode)
            || isset($decoded->_valueCode)
            || property_exists($decoded, self::FIELD_VALUE_CODE)
            || property_exists($decoded, self::FIELD_VALUE_CODE_EXT)) {
            $v = $decoded->_valueCode ?? new \stdClass();
            $v->value = $decoded->valueCode ?? null;
            $type->setValueCode(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueDate)
            || isset($decoded->_valueDate)
            || property_exists($decoded, self::FIELD_VALUE_DATE)
            || property_exists($decoded, self::FIELD_VALUE_DATE_EXT)) {
            $v = $decoded->_valueDate ?? new \stdClass();
            $v->value = $decoded->valueDate ?? null;
            $type->setValueDate(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueDateTime)
            || isset($decoded->_valueDateTime)
            || property_exists($decoded, self::FIELD_VALUE_DATE_TIME)
            || property_exists($decoded, self::FIELD_VALUE_DATE_TIME_EXT)) {
            $v = $decoded->_valueDateTime ?? new \stdClass();
            $v->value = $decoded->valueDateTime ?? null;
            $type->setValueDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueDecimal)
            || isset($decoded->_valueDecimal)
            || property_exists($decoded, self::FIELD_VALUE_DECIMAL)
            || property_exists($decoded, self::FIELD_VALUE_DECIMAL_EXT)) {
            $v = $decoded->_valueDecimal ?? new \stdClass();
            $v->value = $decoded->valueDecimal ?? null;
            $type->setValueDecimal(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueId)
            || isset($decoded->_valueId)
            || property_exists($decoded, self::FIELD_VALUE_ID)
            || property_exists($decoded, self::FIELD_VALUE_ID_EXT)) {
            $v = $decoded->_valueId ?? new \stdClass();
            $v->value = $decoded->valueId ?? null;
            $type->setValueId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueInstant)
            || isset($decoded->_valueInstant)
            || property_exists($decoded, self::FIELD_VALUE_INSTANT)
            || property_exists($decoded, self::FIELD_VALUE_INSTANT_EXT)) {
            $v = $decoded->_valueInstant ?? new \stdClass();
            $v->value = $decoded->valueInstant ?? null;
            $type->setValueInstant(FHIRInstant::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueInteger)
            || isset($decoded->_valueInteger)
            || property_exists($decoded, self::FIELD_VALUE_INTEGER)
            || property_exists($decoded, self::FIELD_VALUE_INTEGER_EXT)) {
            $v = $decoded->_valueInteger ?? new \stdClass();
            $v->value = $decoded->valueInteger ?? null;
            $type->setValueInteger(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueMarkdown)
            || isset($decoded->_valueMarkdown)
            || property_exists($decoded, self::FIELD_VALUE_MARKDOWN)
            || property_exists($decoded, self::FIELD_VALUE_MARKDOWN_EXT)) {
            $v = $decoded->_valueMarkdown ?? new \stdClass();
            $v->value = $decoded->valueMarkdown ?? null;
            $type->setValueMarkdown(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueOid)
            || isset($decoded->_valueOid)
            || property_exists($decoded, self::FIELD_VALUE_OID)
            || property_exists($decoded, self::FIELD_VALUE_OID_EXT)) {
            $v = $decoded->_valueOid ?? new \stdClass();
            $v->value = $decoded->valueOid ?? null;
            $type->setValueOid(FHIROid::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valuePositiveInt)
            || isset($decoded->_valuePositiveInt)
            || property_exists($decoded, self::FIELD_VALUE_POSITIVE_INT)
            || property_exists($decoded, self::FIELD_VALUE_POSITIVE_INT_EXT)) {
            $v = $decoded->_valuePositiveInt ?? new \stdClass();
            $v->value = $decoded->valuePositiveInt ?? null;
            $type->setValuePositiveInt(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueString)
            || isset($decoded->_valueString)
            || property_exists($decoded, self::FIELD_VALUE_STRING)
            || property_exists($decoded, self::FIELD_VALUE_STRING_EXT)) {
            $v = $decoded->_valueString ?? new \stdClass();
            $v->value = $decoded->valueString ?? null;
            $type->setValueString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueTime)
            || isset($decoded->_valueTime)
            || property_exists($decoded, self::FIELD_VALUE_TIME)
            || property_exists($decoded, self::FIELD_VALUE_TIME_EXT)) {
            $v = $decoded->_valueTime ?? new \stdClass();
            $v->value = $decoded->valueTime ?? null;
            $type->setValueTime(FHIRTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueUnsignedInt)
            || isset($decoded->_valueUnsignedInt)
            || property_exists($decoded, self::FIELD_VALUE_UNSIGNED_INT)
            || property_exists($decoded, self::FIELD_VALUE_UNSIGNED_INT_EXT)) {
            $v = $decoded->_valueUnsignedInt ?? new \stdClass();
            $v->value = $decoded->valueUnsignedInt ?? null;
            $type->setValueUnsignedInt(FHIRUnsignedInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueUri)
            || isset($decoded->_valueUri)
            || property_exists($decoded, self::FIELD_VALUE_URI)
            || property_exists($decoded, self::FIELD_VALUE_URI_EXT)) {
            $v = $decoded->_valueUri ?? new \stdClass();
            $v->value = $decoded->valueUri ?? null;
            $type->setValueUri(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueUrl)
            || isset($decoded->_valueUrl)
            || property_exists($decoded, self::FIELD_VALUE_URL)
            || property_exists($decoded, self::FIELD_VALUE_URL_EXT)) {
            $v = $decoded->_valueUrl ?? new \stdClass();
            $v->value = $decoded->valueUrl ?? null;
            $type->setValueUrl(FHIRUrl::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueUuid)
            || isset($decoded->_valueUuid)
            || property_exists($decoded, self::FIELD_VALUE_UUID)
            || property_exists($decoded, self::FIELD_VALUE_UUID_EXT)) {
            $v = $decoded->_valueUuid ?? new \stdClass();
            $v->value = $decoded->valueUuid ?? null;
            $type->setValueUuid(FHIRUuid::jsonUnserialize($v, $config));
        }
        if (isset($decoded->valueAddress) || property_exists($decoded, self::FIELD_VALUE_ADDRESS)) {
            if (is_array($decoded->valueAddress)) {
                $type->setValueAddress(FHIRAddress::jsonUnserialize(reset($decoded->valueAddress), $config));
            } else {
                $type->setValueAddress(FHIRAddress::jsonUnserialize($decoded->valueAddress, $config));
            }
        }
        if (isset($decoded->valueAge) || property_exists($decoded, self::FIELD_VALUE_AGE)) {
            if (is_array($decoded->valueAge)) {
                $type->setValueAge(FHIRAge::jsonUnserialize(reset($decoded->valueAge), $config));
            } else {
                $type->setValueAge(FHIRAge::jsonUnserialize($decoded->valueAge, $config));
            }
        }
        if (isset($decoded->valueAnnotation) || property_exists($decoded, self::FIELD_VALUE_ANNOTATION)) {
            if (is_array($decoded->valueAnnotation)) {
                $type->setValueAnnotation(FHIRAnnotation::jsonUnserialize(reset($decoded->valueAnnotation), $config));
            } else {
                $type->setValueAnnotation(FHIRAnnotation::jsonUnserialize($decoded->valueAnnotation, $config));
            }
        }
        if (isset($decoded->valueAttachment) || property_exists($decoded, self::FIELD_VALUE_ATTACHMENT)) {
            if (is_array($decoded->valueAttachment)) {
                $type->setValueAttachment(FHIRAttachment::jsonUnserialize(reset($decoded->valueAttachment), $config));
            } else {
                $type->setValueAttachment(FHIRAttachment::jsonUnserialize($decoded->valueAttachment, $config));
            }
        }
        if (isset($decoded->valueCodeableConcept) || property_exists($decoded, self::FIELD_VALUE_CODEABLE_CONCEPT)) {
            if (is_array($decoded->valueCodeableConcept)) {
                $type->setValueCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->valueCodeableConcept), $config));
            } else {
                $type->setValueCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->valueCodeableConcept, $config));
            }
        }
        if (isset($decoded->valueCoding) || property_exists($decoded, self::FIELD_VALUE_CODING)) {
            if (is_array($decoded->valueCoding)) {
                $type->setValueCoding(FHIRCoding::jsonUnserialize(reset($decoded->valueCoding), $config));
            } else {
                $type->setValueCoding(FHIRCoding::jsonUnserialize($decoded->valueCoding, $config));
            }
        }
        if (isset($decoded->valueContactPoint) || property_exists($decoded, self::FIELD_VALUE_CONTACT_POINT)) {
            if (is_array($decoded->valueContactPoint)) {
                $type->setValueContactPoint(FHIRContactPoint::jsonUnserialize(reset($decoded->valueContactPoint), $config));
            } else {
                $type->setValueContactPoint(FHIRContactPoint::jsonUnserialize($decoded->valueContactPoint, $config));
            }
        }
        if (isset($decoded->valueCount) || property_exists($decoded, self::FIELD_VALUE_COUNT)) {
            if (is_array($decoded->valueCount)) {
                $type->setValueCount(FHIRCount::jsonUnserialize(reset($decoded->valueCount), $config));
            } else {
                $type->setValueCount(FHIRCount::jsonUnserialize($decoded->valueCount, $config));
            }
        }
        if (isset($decoded->valueDistance) || property_exists($decoded, self::FIELD_VALUE_DISTANCE)) {
            if (is_array($decoded->valueDistance)) {
                $type->setValueDistance(FHIRDistance::jsonUnserialize(reset($decoded->valueDistance), $config));
            } else {
                $type->setValueDistance(FHIRDistance::jsonUnserialize($decoded->valueDistance, $config));
            }
        }
        if (isset($decoded->valueDuration) || property_exists($decoded, self::FIELD_VALUE_DURATION)) {
            if (is_array($decoded->valueDuration)) {
                $type->setValueDuration(FHIRDuration::jsonUnserialize(reset($decoded->valueDuration), $config));
            } else {
                $type->setValueDuration(FHIRDuration::jsonUnserialize($decoded->valueDuration, $config));
            }
        }
        if (isset($decoded->valueHumanName) || property_exists($decoded, self::FIELD_VALUE_HUMAN_NAME)) {
            if (is_array($decoded->valueHumanName)) {
                $type->setValueHumanName(FHIRHumanName::jsonUnserialize(reset($decoded->valueHumanName), $config));
            } else {
                $type->setValueHumanName(FHIRHumanName::jsonUnserialize($decoded->valueHumanName, $config));
            }
        }
        if (isset($decoded->valueIdentifier) || property_exists($decoded, self::FIELD_VALUE_IDENTIFIER)) {
            if (is_array($decoded->valueIdentifier)) {
                $type->setValueIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->valueIdentifier), $config));
            } else {
                $type->setValueIdentifier(FHIRIdentifier::jsonUnserialize($decoded->valueIdentifier, $config));
            }
        }
        if (isset($decoded->valueMoney) || property_exists($decoded, self::FIELD_VALUE_MONEY)) {
            if (is_array($decoded->valueMoney)) {
                $type->setValueMoney(FHIRMoney::jsonUnserialize(reset($decoded->valueMoney), $config));
            } else {
                $type->setValueMoney(FHIRMoney::jsonUnserialize($decoded->valueMoney, $config));
            }
        }
        if (isset($decoded->valuePeriod) || property_exists($decoded, self::FIELD_VALUE_PERIOD)) {
            if (is_array($decoded->valuePeriod)) {
                $type->setValuePeriod(FHIRPeriod::jsonUnserialize(reset($decoded->valuePeriod), $config));
            } else {
                $type->setValuePeriod(FHIRPeriod::jsonUnserialize($decoded->valuePeriod, $config));
            }
        }
        if (isset($decoded->valueQuantity) || property_exists($decoded, self::FIELD_VALUE_QUANTITY)) {
            if (is_array($decoded->valueQuantity)) {
                $type->setValueQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->valueQuantity), $config));
            } else {
                $type->setValueQuantity(FHIRQuantity::jsonUnserialize($decoded->valueQuantity, $config));
            }
        }
        if (isset($decoded->valueRange) || property_exists($decoded, self::FIELD_VALUE_RANGE)) {
            if (is_array($decoded->valueRange)) {
                $type->setValueRange(FHIRRange::jsonUnserialize(reset($decoded->valueRange), $config));
            } else {
                $type->setValueRange(FHIRRange::jsonUnserialize($decoded->valueRange, $config));
            }
        }
        if (isset($decoded->valueRatio) || property_exists($decoded, self::FIELD_VALUE_RATIO)) {
            if (is_array($decoded->valueRatio)) {
                $type->setValueRatio(FHIRRatio::jsonUnserialize(reset($decoded->valueRatio), $config));
            } else {
                $type->setValueRatio(FHIRRatio::jsonUnserialize($decoded->valueRatio, $config));
            }
        }
        if (isset($decoded->valueReference) || property_exists($decoded, self::FIELD_VALUE_REFERENCE)) {
            if (is_array($decoded->valueReference)) {
                $type->setValueReference(FHIRReference::jsonUnserialize(reset($decoded->valueReference), $config));
            } else {
                $type->setValueReference(FHIRReference::jsonUnserialize($decoded->valueReference, $config));
            }
        }
        if (isset($decoded->valueSampledData) || property_exists($decoded, self::FIELD_VALUE_SAMPLED_DATA)) {
            if (is_array($decoded->valueSampledData)) {
                $type->setValueSampledData(FHIRSampledData::jsonUnserialize(reset($decoded->valueSampledData), $config));
            } else {
                $type->setValueSampledData(FHIRSampledData::jsonUnserialize($decoded->valueSampledData, $config));
            }
        }
        if (isset($decoded->valueSignature) || property_exists($decoded, self::FIELD_VALUE_SIGNATURE)) {
            if (is_array($decoded->valueSignature)) {
                $type->setValueSignature(FHIRSignature::jsonUnserialize(reset($decoded->valueSignature), $config));
            } else {
                $type->setValueSignature(FHIRSignature::jsonUnserialize($decoded->valueSignature, $config));
            }
        }
        if (isset($decoded->valueTiming) || property_exists($decoded, self::FIELD_VALUE_TIMING)) {
            if (is_array($decoded->valueTiming)) {
                $type->setValueTiming(FHIRTiming::jsonUnserialize(reset($decoded->valueTiming), $config));
            } else {
                $type->setValueTiming(FHIRTiming::jsonUnserialize($decoded->valueTiming, $config));
            }
        }
        if (isset($decoded->valueContactDetail) || property_exists($decoded, self::FIELD_VALUE_CONTACT_DETAIL)) {
            if (is_array($decoded->valueContactDetail)) {
                $type->setValueContactDetail(FHIRContactDetail::jsonUnserialize(reset($decoded->valueContactDetail), $config));
            } else {
                $type->setValueContactDetail(FHIRContactDetail::jsonUnserialize($decoded->valueContactDetail, $config));
            }
        }
        if (isset($decoded->valueContributor) || property_exists($decoded, self::FIELD_VALUE_CONTRIBUTOR)) {
            if (is_array($decoded->valueContributor)) {
                $type->setValueContributor(FHIRContributor::jsonUnserialize(reset($decoded->valueContributor), $config));
            } else {
                $type->setValueContributor(FHIRContributor::jsonUnserialize($decoded->valueContributor, $config));
            }
        }
        if (isset($decoded->valueDataRequirement) || property_exists($decoded, self::FIELD_VALUE_DATA_REQUIREMENT)) {
            if (is_array($decoded->valueDataRequirement)) {
                $type->setValueDataRequirement(FHIRDataRequirement::jsonUnserialize(reset($decoded->valueDataRequirement), $config));
            } else {
                $type->setValueDataRequirement(FHIRDataRequirement::jsonUnserialize($decoded->valueDataRequirement, $config));
            }
        }
        if (isset($decoded->valueExpression) || property_exists($decoded, self::FIELD_VALUE_EXPRESSION)) {
            if (is_array($decoded->valueExpression)) {
                $type->setValueExpression(FHIRExpression::jsonUnserialize(reset($decoded->valueExpression), $config));
            } else {
                $type->setValueExpression(FHIRExpression::jsonUnserialize($decoded->valueExpression, $config));
            }
        }
        if (isset($decoded->valueParameterDefinition) || property_exists($decoded, self::FIELD_VALUE_PARAMETER_DEFINITION)) {
            if (is_array($decoded->valueParameterDefinition)) {
                $type->setValueParameterDefinition(FHIRParameterDefinition::jsonUnserialize(reset($decoded->valueParameterDefinition), $config));
            } else {
                $type->setValueParameterDefinition(FHIRParameterDefinition::jsonUnserialize($decoded->valueParameterDefinition, $config));
            }
        }
        if (isset($decoded->valueRelatedArtifact) || property_exists($decoded, self::FIELD_VALUE_RELATED_ARTIFACT)) {
            if (is_array($decoded->valueRelatedArtifact)) {
                $type->setValueRelatedArtifact(FHIRRelatedArtifact::jsonUnserialize(reset($decoded->valueRelatedArtifact), $config));
            } else {
                $type->setValueRelatedArtifact(FHIRRelatedArtifact::jsonUnserialize($decoded->valueRelatedArtifact, $config));
            }
        }
        if (isset($decoded->valueTriggerDefinition) || property_exists($decoded, self::FIELD_VALUE_TRIGGER_DEFINITION)) {
            if (is_array($decoded->valueTriggerDefinition)) {
                $type->setValueTriggerDefinition(FHIRTriggerDefinition::jsonUnserialize(reset($decoded->valueTriggerDefinition), $config));
            } else {
                $type->setValueTriggerDefinition(FHIRTriggerDefinition::jsonUnserialize($decoded->valueTriggerDefinition, $config));
            }
        }
        if (isset($decoded->valueUsageContext) || property_exists($decoded, self::FIELD_VALUE_USAGE_CONTEXT)) {
            if (is_array($decoded->valueUsageContext)) {
                $type->setValueUsageContext(FHIRUsageContext::jsonUnserialize(reset($decoded->valueUsageContext), $config));
            } else {
                $type->setValueUsageContext(FHIRUsageContext::jsonUnserialize($decoded->valueUsageContext, $config));
            }
        }
        if (isset($decoded->valueDosage) || property_exists($decoded, self::FIELD_VALUE_DOSAGE)) {
            if (is_array($decoded->valueDosage)) {
                $type->setValueDosage(FHIRDosage::jsonUnserialize(reset($decoded->valueDosage), $config));
            } else {
                $type->setValueDosage(FHIRDosage::jsonUnserialize($decoded->valueDosage, $config));
            }
        }
        if (isset($decoded->valueMeta) || property_exists($decoded, self::FIELD_VALUE_META)) {
            if (is_array($decoded->valueMeta)) {
                $type->setValueMeta(FHIRMeta::jsonUnserialize(reset($decoded->valueMeta), $config));
            } else {
                $type->setValueMeta(FHIRMeta::jsonUnserialize($decoded->valueMeta, $config));
            }
        }
        if (isset($decoded->resource)) {
            $typeClassName = VersionTypeMap::mustGetContainedTypeClassnameFromJSON($decoded->resource);
            $v = $decoded->resource;
            unset($v->resourceType);
            $type->setResource($typeClassName::jsonUnserialize($v, $config));
        }
        if (isset($decoded->part) || property_exists($decoded, self::FIELD_PART)) {
            if (is_object($decoded->part)) {
                $vals = [$decoded->part];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PART, true);
            } else {
                $vals = $decoded->part;
            }
            foreach($vals as $v) {
                $type->addPart(FHIRParametersParameter::jsonUnserialize($v, $config));
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
        if (isset($this->name)) {
            if (null !== ($val = $this->name->getValue())) {
                $out->name = $val;
            }
            if ($this->name->_nonValueFieldDefined()) {
                $ext = $this->name->jsonSerialize();
                unset($ext->value);
                $out->_name = $ext;
            }
        }
        if (isset($this->valueBase64Binary)) {
            if (null !== ($val = $this->valueBase64Binary->getValue())) {
                $out->valueBase64Binary = $val;
            }
            if ($this->valueBase64Binary->_nonValueFieldDefined()) {
                $ext = $this->valueBase64Binary->jsonSerialize();
                unset($ext->value);
                $out->_valueBase64Binary = $ext;
            }
        }
        if (isset($this->valueBoolean)) {
            if (null !== ($val = $this->valueBoolean->getValue())) {
                $out->valueBoolean = $val;
            }
            if ($this->valueBoolean->_nonValueFieldDefined()) {
                $ext = $this->valueBoolean->jsonSerialize();
                unset($ext->value);
                $out->_valueBoolean = $ext;
            }
        }
        if (isset($this->valueCanonical)) {
            if (null !== ($val = $this->valueCanonical->getValue())) {
                $out->valueCanonical = $val;
            }
            if ($this->valueCanonical->_nonValueFieldDefined()) {
                $ext = $this->valueCanonical->jsonSerialize();
                unset($ext->value);
                $out->_valueCanonical = $ext;
            }
        }
        if (isset($this->valueCode)) {
            if (null !== ($val = $this->valueCode->getValue())) {
                $out->valueCode = $val;
            }
            if ($this->valueCode->_nonValueFieldDefined()) {
                $ext = $this->valueCode->jsonSerialize();
                unset($ext->value);
                $out->_valueCode = $ext;
            }
        }
        if (isset($this->valueDate)) {
            if (null !== ($val = $this->valueDate->getValue())) {
                $out->valueDate = $val;
            }
            if ($this->valueDate->_nonValueFieldDefined()) {
                $ext = $this->valueDate->jsonSerialize();
                unset($ext->value);
                $out->_valueDate = $ext;
            }
        }
        if (isset($this->valueDateTime)) {
            if (null !== ($val = $this->valueDateTime->getValue())) {
                $out->valueDateTime = $val;
            }
            if ($this->valueDateTime->_nonValueFieldDefined()) {
                $ext = $this->valueDateTime->jsonSerialize();
                unset($ext->value);
                $out->_valueDateTime = $ext;
            }
        }
        if (isset($this->valueDecimal)) {
            if (null !== ($val = $this->valueDecimal->getValue())) {
                $out->valueDecimal = $val;
            }
            if ($this->valueDecimal->_nonValueFieldDefined()) {
                $ext = $this->valueDecimal->jsonSerialize();
                unset($ext->value);
                $out->_valueDecimal = $ext;
            }
        }
        if (isset($this->valueId)) {
            if (null !== ($val = $this->valueId->getValue())) {
                $out->valueId = $val;
            }
            if ($this->valueId->_nonValueFieldDefined()) {
                $ext = $this->valueId->jsonSerialize();
                unset($ext->value);
                $out->_valueId = $ext;
            }
        }
        if (isset($this->valueInstant)) {
            if (null !== ($val = $this->valueInstant->getValue())) {
                $out->valueInstant = $val;
            }
            if ($this->valueInstant->_nonValueFieldDefined()) {
                $ext = $this->valueInstant->jsonSerialize();
                unset($ext->value);
                $out->_valueInstant = $ext;
            }
        }
        if (isset($this->valueInteger)) {
            if (null !== ($val = $this->valueInteger->getValue())) {
                $out->valueInteger = $val;
            }
            if ($this->valueInteger->_nonValueFieldDefined()) {
                $ext = $this->valueInteger->jsonSerialize();
                unset($ext->value);
                $out->_valueInteger = $ext;
            }
        }
        if (isset($this->valueMarkdown)) {
            if (null !== ($val = $this->valueMarkdown->getValue())) {
                $out->valueMarkdown = $val;
            }
            if ($this->valueMarkdown->_nonValueFieldDefined()) {
                $ext = $this->valueMarkdown->jsonSerialize();
                unset($ext->value);
                $out->_valueMarkdown = $ext;
            }
        }
        if (isset($this->valueOid)) {
            if (null !== ($val = $this->valueOid->getValue())) {
                $out->valueOid = $val;
            }
            if ($this->valueOid->_nonValueFieldDefined()) {
                $ext = $this->valueOid->jsonSerialize();
                unset($ext->value);
                $out->_valueOid = $ext;
            }
        }
        if (isset($this->valuePositiveInt)) {
            if (null !== ($val = $this->valuePositiveInt->getValue())) {
                $out->valuePositiveInt = $val;
            }
            if ($this->valuePositiveInt->_nonValueFieldDefined()) {
                $ext = $this->valuePositiveInt->jsonSerialize();
                unset($ext->value);
                $out->_valuePositiveInt = $ext;
            }
        }
        if (isset($this->valueString)) {
            if (null !== ($val = $this->valueString->getValue())) {
                $out->valueString = $val;
            }
            if ($this->valueString->_nonValueFieldDefined()) {
                $ext = $this->valueString->jsonSerialize();
                unset($ext->value);
                $out->_valueString = $ext;
            }
        }
        if (isset($this->valueTime)) {
            if (null !== ($val = $this->valueTime->getValue())) {
                $out->valueTime = $val;
            }
            if ($this->valueTime->_nonValueFieldDefined()) {
                $ext = $this->valueTime->jsonSerialize();
                unset($ext->value);
                $out->_valueTime = $ext;
            }
        }
        if (isset($this->valueUnsignedInt)) {
            if (null !== ($val = $this->valueUnsignedInt->getValue())) {
                $out->valueUnsignedInt = $val;
            }
            if ($this->valueUnsignedInt->_nonValueFieldDefined()) {
                $ext = $this->valueUnsignedInt->jsonSerialize();
                unset($ext->value);
                $out->_valueUnsignedInt = $ext;
            }
        }
        if (isset($this->valueUri)) {
            if (null !== ($val = $this->valueUri->getValue())) {
                $out->valueUri = $val;
            }
            if ($this->valueUri->_nonValueFieldDefined()) {
                $ext = $this->valueUri->jsonSerialize();
                unset($ext->value);
                $out->_valueUri = $ext;
            }
        }
        if (isset($this->valueUrl)) {
            if (null !== ($val = $this->valueUrl->getValue())) {
                $out->valueUrl = $val;
            }
            if ($this->valueUrl->_nonValueFieldDefined()) {
                $ext = $this->valueUrl->jsonSerialize();
                unset($ext->value);
                $out->_valueUrl = $ext;
            }
        }
        if (isset($this->valueUuid)) {
            if (null !== ($val = $this->valueUuid->getValue())) {
                $out->valueUuid = $val;
            }
            if ($this->valueUuid->_nonValueFieldDefined()) {
                $ext = $this->valueUuid->jsonSerialize();
                unset($ext->value);
                $out->_valueUuid = $ext;
            }
        }
        if (isset($this->valueAddress)) {
            $out->valueAddress = $this->valueAddress;
        }
        if (isset($this->valueAge)) {
            $out->valueAge = $this->valueAge;
        }
        if (isset($this->valueAnnotation)) {
            $out->valueAnnotation = $this->valueAnnotation;
        }
        if (isset($this->valueAttachment)) {
            $out->valueAttachment = $this->valueAttachment;
        }
        if (isset($this->valueCodeableConcept)) {
            $out->valueCodeableConcept = $this->valueCodeableConcept;
        }
        if (isset($this->valueCoding)) {
            $out->valueCoding = $this->valueCoding;
        }
        if (isset($this->valueContactPoint)) {
            $out->valueContactPoint = $this->valueContactPoint;
        }
        if (isset($this->valueCount)) {
            $out->valueCount = $this->valueCount;
        }
        if (isset($this->valueDistance)) {
            $out->valueDistance = $this->valueDistance;
        }
        if (isset($this->valueDuration)) {
            $out->valueDuration = $this->valueDuration;
        }
        if (isset($this->valueHumanName)) {
            $out->valueHumanName = $this->valueHumanName;
        }
        if (isset($this->valueIdentifier)) {
            $out->valueIdentifier = $this->valueIdentifier;
        }
        if (isset($this->valueMoney)) {
            $out->valueMoney = $this->valueMoney;
        }
        if (isset($this->valuePeriod)) {
            $out->valuePeriod = $this->valuePeriod;
        }
        if (isset($this->valueQuantity)) {
            $out->valueQuantity = $this->valueQuantity;
        }
        if (isset($this->valueRange)) {
            $out->valueRange = $this->valueRange;
        }
        if (isset($this->valueRatio)) {
            $out->valueRatio = $this->valueRatio;
        }
        if (isset($this->valueReference)) {
            $out->valueReference = $this->valueReference;
        }
        if (isset($this->valueSampledData)) {
            $out->valueSampledData = $this->valueSampledData;
        }
        if (isset($this->valueSignature)) {
            $out->valueSignature = $this->valueSignature;
        }
        if (isset($this->valueTiming)) {
            $out->valueTiming = $this->valueTiming;
        }
        if (isset($this->valueContactDetail)) {
            $out->valueContactDetail = $this->valueContactDetail;
        }
        if (isset($this->valueContributor)) {
            $out->valueContributor = $this->valueContributor;
        }
        if (isset($this->valueDataRequirement)) {
            $out->valueDataRequirement = $this->valueDataRequirement;
        }
        if (isset($this->valueExpression)) {
            $out->valueExpression = $this->valueExpression;
        }
        if (isset($this->valueParameterDefinition)) {
            $out->valueParameterDefinition = $this->valueParameterDefinition;
        }
        if (isset($this->valueRelatedArtifact)) {
            $out->valueRelatedArtifact = $this->valueRelatedArtifact;
        }
        if (isset($this->valueTriggerDefinition)) {
            $out->valueTriggerDefinition = $this->valueTriggerDefinition;
        }
        if (isset($this->valueUsageContext)) {
            $out->valueUsageContext = $this->valueUsageContext;
        }
        if (isset($this->valueDosage)) {
            $out->valueDosage = $this->valueDosage;
        }
        if (isset($this->valueMeta)) {
            $out->valueMeta = $this->valueMeta;
        }
        if (isset($this->resource)) {
            $out->resource = $this->resource;
        }
        if (isset($this->part) && [] !== $this->part) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PART) && 1 === count($this->part)) {
                $out->part = $this->part[0];
            } else {
                $out->part = $this->part;
            }
        }
        return $out;
    }
}
