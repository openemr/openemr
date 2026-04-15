<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAssertionDirectionTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAssertionOperatorTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAssertionResponseTypesList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionDirectionType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionOperatorType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionResponseTypes;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A structured set of tests against a FHIR server or client implementation to
 * determine compliance against the FHIR specification.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTestScriptAssert extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TEST_SCRIPT_DOT_ASSERT;

    /* class_default.php:56 */
    public const FIELD_LABEL = 'label';
    public const FIELD_LABEL_EXT = '_label';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_DIRECTION = 'direction';
    public const FIELD_DIRECTION_EXT = '_direction';
    public const FIELD_COMPARE_TO_SOURCE_ID = 'compareToSourceId';
    public const FIELD_COMPARE_TO_SOURCE_ID_EXT = '_compareToSourceId';
    public const FIELD_COMPARE_TO_SOURCE_EXPRESSION = 'compareToSourceExpression';
    public const FIELD_COMPARE_TO_SOURCE_EXPRESSION_EXT = '_compareToSourceExpression';
    public const FIELD_COMPARE_TO_SOURCE_PATH = 'compareToSourcePath';
    public const FIELD_COMPARE_TO_SOURCE_PATH_EXT = '_compareToSourcePath';
    public const FIELD_CONTENT_TYPE = 'contentType';
    public const FIELD_CONTENT_TYPE_EXT = '_contentType';
    public const FIELD_EXPRESSION = 'expression';
    public const FIELD_EXPRESSION_EXT = '_expression';
    public const FIELD_HEADER_FIELD = 'headerField';
    public const FIELD_HEADER_FIELD_EXT = '_headerField';
    public const FIELD_MINIMUM_ID = 'minimumId';
    public const FIELD_MINIMUM_ID_EXT = '_minimumId';
    public const FIELD_NAVIGATION_LINKS = 'navigationLinks';
    public const FIELD_NAVIGATION_LINKS_EXT = '_navigationLinks';
    public const FIELD_OPERATOR = 'operator';
    public const FIELD_OPERATOR_EXT = '_operator';
    public const FIELD_PATH = 'path';
    public const FIELD_PATH_EXT = '_path';
    public const FIELD_REQUEST_METHOD = 'requestMethod';
    public const FIELD_REQUEST_METHOD_EXT = '_requestMethod';
    public const FIELD_REQUEST_URL = 'requestURL';
    public const FIELD_REQUEST_URL_EXT = '_requestURL';
    public const FIELD_RESOURCE = 'resource';
    public const FIELD_RESOURCE_EXT = '_resource';
    public const FIELD_RESPONSE = 'response';
    public const FIELD_RESPONSE_EXT = '_response';
    public const FIELD_RESPONSE_CODE = 'responseCode';
    public const FIELD_RESPONSE_CODE_EXT = '_responseCode';
    public const FIELD_SOURCE_ID = 'sourceId';
    public const FIELD_SOURCE_ID_EXT = '_sourceId';
    public const FIELD_VALIDATE_PROFILE_ID = 'validateProfileId';
    public const FIELD_VALIDATE_PROFILE_ID_EXT = '_validateProfileId';
    public const FIELD_VALUE = 'value';
    public const FIELD_VALUE_EXT = '_value';
    public const FIELD_WARNING_ONLY = 'warningOnly';
    public const FIELD_WARNING_ONLY_EXT = '_warningOnly';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_WARNING_ONLY => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_LABEL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DIRECTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COMPARE_TO_SOURCE_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COMPARE_TO_SOURCE_EXPRESSION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COMPARE_TO_SOURCE_PATH => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CONTENT_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_EXPRESSION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_HEADER_FIELD => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MINIMUM_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NAVIGATION_LINKS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OPERATOR => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PATH => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_REQUEST_METHOD => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_REQUEST_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RESOURCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RESPONSE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RESPONSE_CODE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SOURCE_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALIDATE_PROFILE_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VALUE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_WARNING_ONLY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The label would be used for tracking/logging purposes by test engines.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $label;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The description would be used by test engines for tracking and reporting
     * purposes.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * The type of direction to use for assertion.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The direction to use for the assertion.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionDirectionType
     */
    #[FHIRAssertionDirectionType]
    protected FHIRAssertionDirectionType $direction;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Id of the source fixture used as the contents to be evaluated by either the
     * "source/expression" or "sourceId/path" definition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $compareToSourceId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The FHIRPath expression to evaluate against the source fixture. When
     * compareToSourceId is defined, either compareToSourceExpression or
     * compareToSourcePath must be defined, but not both.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $compareToSourceExpression;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * XPath or JSONPath expression to evaluate against the source fixture. When
     * compareToSourceId is defined, either compareToSourceExpression or
     * compareToSourcePath must be defined, but not both.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $compareToSourcePath;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The mime-type contents to compare against the request or response message
     * 'Content-Type' header.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $contentType;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The FHIRPath expression to be evaluated against the request or response message
     * contents - HTTP headers and payload.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $expression;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The HTTP header field name e.g. 'Location'.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $headerField;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The ID of a fixture. Asserts that the response contains at a minimum the fixture
     * specified by minimumId.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $minimumId;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution performs validation on the bundle navigation
     * links.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $navigationLinks;
    /**
     * The type of operator to use for assertion.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The operator type defines the conditional behavior of the assert. If not
     * defined, the default is equals.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionOperatorType
     */
    #[FHIRAssertionOperatorType]
    protected FHIRAssertionOperatorType $operator;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The XPath or JSONPath expression to be evaluated against the fixture
     * representing the response received from server.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $path;
    /**
     * The allowable request method or HTTP operation codes.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The request method or HTTP operation code to compare against that used by the
     * client system under test.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    #[FHIRTestScriptRequestMethodCode]
    protected FHIRTestScriptRequestMethodCode $requestMethod;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value to use in a comparison against the request URL path string.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $requestURL;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The type of the resource. See http://build.fhir.org/resourcelist.html.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $resource;
    /**
     * The type of response code to use for assertion.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * okay | created | noContent | notModified | bad | forbidden | notFound |
     * methodNotAllowed | conflict | gone | preconditionFailed | unprocessable.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionResponseTypes
     */
    #[FHIRAssertionResponseTypes]
    protected FHIRAssertionResponseTypes $response;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value of the HTTP response code to be tested.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $responseCode;
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Fixture to evaluate the XPath/JSONPath expression or the headerField against.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $sourceId;
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The ID of the Profile to validate against.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $validateProfileId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value to compare to.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $value;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution will produce a warning only on error for this
     * assert.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $warningOnly;

    /* constructor.php:61 */
    /**
     * FHIRTestScriptAssert Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $label
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAssertionDirectionTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionDirectionType $direction
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $compareToSourceId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $compareToSourceExpression
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $compareToSourcePath
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $contentType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $expression
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $headerField
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $minimumId
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $navigationLinks
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAssertionOperatorTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionOperatorType $operator
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $path
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode $requestMethod
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $requestURL
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $resource
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAssertionResponseTypesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionResponseTypes $response
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $responseCode
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $sourceId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $validateProfileId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $value
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $warningOnly
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $label = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|string|FHIRAssertionDirectionTypeList|FHIRAssertionDirectionType $direction = null,
                                null|string|FHIRStringPrimitive|FHIRString $compareToSourceId = null,
                                null|string|FHIRStringPrimitive|FHIRString $compareToSourceExpression = null,
                                null|string|FHIRStringPrimitive|FHIRString $compareToSourcePath = null,
                                null|string|FHIRCodePrimitive|FHIRCode $contentType = null,
                                null|string|FHIRStringPrimitive|FHIRString $expression = null,
                                null|string|FHIRStringPrimitive|FHIRString $headerField = null,
                                null|string|FHIRStringPrimitive|FHIRString $minimumId = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $navigationLinks = null,
                                null|string|FHIRAssertionOperatorTypeList|FHIRAssertionOperatorType $operator = null,
                                null|string|FHIRStringPrimitive|FHIRString $path = null,
                                null|string|FHIRTestScriptRequestMethodCodeList|FHIRTestScriptRequestMethodCode $requestMethod = null,
                                null|string|FHIRStringPrimitive|FHIRString $requestURL = null,
                                null|string|FHIRCodePrimitive|FHIRCode $resource = null,
                                null|string|FHIRAssertionResponseTypesList|FHIRAssertionResponseTypes $response = null,
                                null|string|FHIRStringPrimitive|FHIRString $responseCode = null,
                                null|string|FHIRIdPrimitive|FHIRId $sourceId = null,
                                null|string|FHIRIdPrimitive|FHIRId $validateProfileId = null,
                                null|string|FHIRStringPrimitive|FHIRString $value = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $warningOnly = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $label) {
            $this->setLabel($label);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $direction) {
            $this->setDirection($direction);
        }
        if (null !== $compareToSourceId) {
            $this->setCompareToSourceId($compareToSourceId);
        }
        if (null !== $compareToSourceExpression) {
            $this->setCompareToSourceExpression($compareToSourceExpression);
        }
        if (null !== $compareToSourcePath) {
            $this->setCompareToSourcePath($compareToSourcePath);
        }
        if (null !== $contentType) {
            $this->setContentType($contentType);
        }
        if (null !== $expression) {
            $this->setExpression($expression);
        }
        if (null !== $headerField) {
            $this->setHeaderField($headerField);
        }
        if (null !== $minimumId) {
            $this->setMinimumId($minimumId);
        }
        if (null !== $navigationLinks) {
            $this->setNavigationLinks($navigationLinks);
        }
        if (null !== $operator) {
            $this->setOperator($operator);
        }
        if (null !== $path) {
            $this->setPath($path);
        }
        if (null !== $requestMethod) {
            $this->setRequestMethod($requestMethod);
        }
        if (null !== $requestURL) {
            $this->setRequestURL($requestURL);
        }
        if (null !== $resource) {
            $this->setResource($resource);
        }
        if (null !== $response) {
            $this->setResponse($response);
        }
        if (null !== $responseCode) {
            $this->setResponseCode($responseCode);
        }
        if (null !== $sourceId) {
            $this->setSourceId($sourceId);
        }
        if (null !== $validateProfileId) {
            $this->setValidateProfileId($validateProfileId);
        }
        if (null !== $value) {
            $this->setValue($value);
        }
        if (null !== $warningOnly) {
            $this->setWarningOnly($warningOnly);
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
     * The label would be used for tracking/logging purposes by test engines.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getLabel(): null|FHIRString
    {
        return $this->label ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The label would be used for tracking/logging purposes by test engines.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $label
     * @return static
     */
    public function setLabel(null|string|FHIRStringPrimitive|FHIRString $label): self
    {
        if (null === $label) {
            unset($this->label);
            return $this;
        }
        if (!($label instanceof FHIRString)) {
            $label = new FHIRString(value: $label);
        }
        $this->label = $label;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The description would be used by test engines for tracking and reporting
     * purposes.
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
     * The description would be used by test engines for tracking and reporting
     * purposes.
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
     * The type of direction to use for assertion.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The direction to use for the assertion.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionDirectionType
     */
    public function getDirection(): null|FHIRAssertionDirectionType
    {
        return $this->direction ?? null;
    }

    /**
     * The type of direction to use for assertion.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The direction to use for the assertion.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAssertionDirectionTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionDirectionType $direction
     * @return static
     */
    public function setDirection(null|string|FHIRAssertionDirectionTypeList|FHIRAssertionDirectionType $direction): self
    {
        if (null === $direction) {
            unset($this->direction);
            return $this;
        }
        if (!($direction instanceof FHIRAssertionDirectionType)) {
            $direction = new FHIRAssertionDirectionType(value: $direction);
        }
        $this->direction = $direction;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Id of the source fixture used as the contents to be evaluated by either the
     * "source/expression" or "sourceId/path" definition.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getCompareToSourceId(): null|FHIRString
    {
        return $this->compareToSourceId ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Id of the source fixture used as the contents to be evaluated by either the
     * "source/expression" or "sourceId/path" definition.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $compareToSourceId
     * @return static
     */
    public function setCompareToSourceId(null|string|FHIRStringPrimitive|FHIRString $compareToSourceId): self
    {
        if (null === $compareToSourceId) {
            unset($this->compareToSourceId);
            return $this;
        }
        if (!($compareToSourceId instanceof FHIRString)) {
            $compareToSourceId = new FHIRString(value: $compareToSourceId);
        }
        $this->compareToSourceId = $compareToSourceId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The FHIRPath expression to evaluate against the source fixture. When
     * compareToSourceId is defined, either compareToSourceExpression or
     * compareToSourcePath must be defined, but not both.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getCompareToSourceExpression(): null|FHIRString
    {
        return $this->compareToSourceExpression ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The FHIRPath expression to evaluate against the source fixture. When
     * compareToSourceId is defined, either compareToSourceExpression or
     * compareToSourcePath must be defined, but not both.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $compareToSourceExpression
     * @return static
     */
    public function setCompareToSourceExpression(null|string|FHIRStringPrimitive|FHIRString $compareToSourceExpression): self
    {
        if (null === $compareToSourceExpression) {
            unset($this->compareToSourceExpression);
            return $this;
        }
        if (!($compareToSourceExpression instanceof FHIRString)) {
            $compareToSourceExpression = new FHIRString(value: $compareToSourceExpression);
        }
        $this->compareToSourceExpression = $compareToSourceExpression;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * XPath or JSONPath expression to evaluate against the source fixture. When
     * compareToSourceId is defined, either compareToSourceExpression or
     * compareToSourcePath must be defined, but not both.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getCompareToSourcePath(): null|FHIRString
    {
        return $this->compareToSourcePath ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * XPath or JSONPath expression to evaluate against the source fixture. When
     * compareToSourceId is defined, either compareToSourceExpression or
     * compareToSourcePath must be defined, but not both.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $compareToSourcePath
     * @return static
     */
    public function setCompareToSourcePath(null|string|FHIRStringPrimitive|FHIRString $compareToSourcePath): self
    {
        if (null === $compareToSourcePath) {
            unset($this->compareToSourcePath);
            return $this;
        }
        if (!($compareToSourcePath instanceof FHIRString)) {
            $compareToSourcePath = new FHIRString(value: $compareToSourcePath);
        }
        $this->compareToSourcePath = $compareToSourcePath;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The mime-type contents to compare against the request or response message
     * 'Content-Type' header.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getContentType(): null|FHIRCode
    {
        return $this->contentType ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The mime-type contents to compare against the request or response message
     * 'Content-Type' header.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $contentType
     * @return static
     */
    public function setContentType(null|string|FHIRCodePrimitive|FHIRCode $contentType): self
    {
        if (null === $contentType) {
            unset($this->contentType);
            return $this;
        }
        if (!($contentType instanceof FHIRCode)) {
            $contentType = new FHIRCode(value: $contentType);
        }
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The FHIRPath expression to be evaluated against the request or response message
     * contents - HTTP headers and payload.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getExpression(): null|FHIRString
    {
        return $this->expression ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The FHIRPath expression to be evaluated against the request or response message
     * contents - HTTP headers and payload.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $expression
     * @return static
     */
    public function setExpression(null|string|FHIRStringPrimitive|FHIRString $expression): self
    {
        if (null === $expression) {
            unset($this->expression);
            return $this;
        }
        if (!($expression instanceof FHIRString)) {
            $expression = new FHIRString(value: $expression);
        }
        $this->expression = $expression;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The HTTP header field name e.g. 'Location'.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getHeaderField(): null|FHIRString
    {
        return $this->headerField ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The HTTP header field name e.g. 'Location'.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $headerField
     * @return static
     */
    public function setHeaderField(null|string|FHIRStringPrimitive|FHIRString $headerField): self
    {
        if (null === $headerField) {
            unset($this->headerField);
            return $this;
        }
        if (!($headerField instanceof FHIRString)) {
            $headerField = new FHIRString(value: $headerField);
        }
        $this->headerField = $headerField;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The ID of a fixture. Asserts that the response contains at a minimum the fixture
     * specified by minimumId.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getMinimumId(): null|FHIRString
    {
        return $this->minimumId ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The ID of a fixture. Asserts that the response contains at a minimum the fixture
     * specified by minimumId.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $minimumId
     * @return static
     */
    public function setMinimumId(null|string|FHIRStringPrimitive|FHIRString $minimumId): self
    {
        if (null === $minimumId) {
            unset($this->minimumId);
            return $this;
        }
        if (!($minimumId instanceof FHIRString)) {
            $minimumId = new FHIRString(value: $minimumId);
        }
        $this->minimumId = $minimumId;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution performs validation on the bundle navigation
     * links.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getNavigationLinks(): null|FHIRBoolean
    {
        return $this->navigationLinks ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution performs validation on the bundle navigation
     * links.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $navigationLinks
     * @return static
     */
    public function setNavigationLinks(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $navigationLinks): self
    {
        if (null === $navigationLinks) {
            unset($this->navigationLinks);
            return $this;
        }
        if (!($navigationLinks instanceof FHIRBoolean)) {
            $navigationLinks = new FHIRBoolean(value: $navigationLinks);
        }
        $this->navigationLinks = $navigationLinks;
        return $this;
    }

    /**
     * The type of operator to use for assertion.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The operator type defines the conditional behavior of the assert. If not
     * defined, the default is equals.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionOperatorType
     */
    public function getOperator(): null|FHIRAssertionOperatorType
    {
        return $this->operator ?? null;
    }

    /**
     * The type of operator to use for assertion.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The operator type defines the conditional behavior of the assert. If not
     * defined, the default is equals.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAssertionOperatorTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionOperatorType $operator
     * @return static
     */
    public function setOperator(null|string|FHIRAssertionOperatorTypeList|FHIRAssertionOperatorType $operator): self
    {
        if (null === $operator) {
            unset($this->operator);
            return $this;
        }
        if (!($operator instanceof FHIRAssertionOperatorType)) {
            $operator = new FHIRAssertionOperatorType(value: $operator);
        }
        $this->operator = $operator;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The XPath or JSONPath expression to be evaluated against the fixture
     * representing the response received from server.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getPath(): null|FHIRString
    {
        return $this->path ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The XPath or JSONPath expression to be evaluated against the fixture
     * representing the response received from server.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $path
     * @return static
     */
    public function setPath(null|string|FHIRStringPrimitive|FHIRString $path): self
    {
        if (null === $path) {
            unset($this->path);
            return $this;
        }
        if (!($path instanceof FHIRString)) {
            $path = new FHIRString(value: $path);
        }
        $this->path = $path;
        return $this;
    }

    /**
     * The allowable request method or HTTP operation codes.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The request method or HTTP operation code to compare against that used by the
     * client system under test.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    public function getRequestMethod(): null|FHIRTestScriptRequestMethodCode
    {
        return $this->requestMethod ?? null;
    }

    /**
     * The allowable request method or HTTP operation codes.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The request method or HTTP operation code to compare against that used by the
     * client system under test.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode $requestMethod
     * @return static
     */
    public function setRequestMethod(null|string|FHIRTestScriptRequestMethodCodeList|FHIRTestScriptRequestMethodCode $requestMethod): self
    {
        if (null === $requestMethod) {
            unset($this->requestMethod);
            return $this;
        }
        if (!($requestMethod instanceof FHIRTestScriptRequestMethodCode)) {
            $requestMethod = new FHIRTestScriptRequestMethodCode(value: $requestMethod);
        }
        $this->requestMethod = $requestMethod;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value to use in a comparison against the request URL path string.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getRequestURL(): null|FHIRString
    {
        return $this->requestURL ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value to use in a comparison against the request URL path string.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $requestURL
     * @return static
     */
    public function setRequestURL(null|string|FHIRStringPrimitive|FHIRString $requestURL): self
    {
        if (null === $requestURL) {
            unset($this->requestURL);
            return $this;
        }
        if (!($requestURL instanceof FHIRString)) {
            $requestURL = new FHIRString(value: $requestURL);
        }
        $this->requestURL = $requestURL;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The type of the resource. See http://build.fhir.org/resourcelist.html.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getResource(): null|FHIRCode
    {
        return $this->resource ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The type of the resource. See http://build.fhir.org/resourcelist.html.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $resource
     * @return static
     */
    public function setResource(null|string|FHIRCodePrimitive|FHIRCode $resource): self
    {
        if (null === $resource) {
            unset($this->resource);
            return $this;
        }
        if (!($resource instanceof FHIRCode)) {
            $resource = new FHIRCode(value: $resource);
        }
        $this->resource = $resource;
        return $this;
    }

    /**
     * The type of response code to use for assertion.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * okay | created | noContent | notModified | bad | forbidden | notFound |
     * methodNotAllowed | conflict | gone | preconditionFailed | unprocessable.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionResponseTypes
     */
    public function getResponse(): null|FHIRAssertionResponseTypes
    {
        return $this->response ?? null;
    }

    /**
     * The type of response code to use for assertion.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * okay | created | noContent | notModified | bad | forbidden | notFound |
     * methodNotAllowed | conflict | gone | preconditionFailed | unprocessable.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAssertionResponseTypesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAssertionResponseTypes $response
     * @return static
     */
    public function setResponse(null|string|FHIRAssertionResponseTypesList|FHIRAssertionResponseTypes $response): self
    {
        if (null === $response) {
            unset($this->response);
            return $this;
        }
        if (!($response instanceof FHIRAssertionResponseTypes)) {
            $response = new FHIRAssertionResponseTypes(value: $response);
        }
        $this->response = $response;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value of the HTTP response code to be tested.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getResponseCode(): null|FHIRString
    {
        return $this->responseCode ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value of the HTTP response code to be tested.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $responseCode
     * @return static
     */
    public function setResponseCode(null|string|FHIRStringPrimitive|FHIRString $responseCode): self
    {
        if (null === $responseCode) {
            unset($this->responseCode);
            return $this;
        }
        if (!($responseCode instanceof FHIRString)) {
            $responseCode = new FHIRString(value: $responseCode);
        }
        $this->responseCode = $responseCode;
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
     * Fixture to evaluate the XPath/JSONPath expression or the headerField against.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getSourceId(): null|FHIRId
    {
        return $this->sourceId ?? null;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Fixture to evaluate the XPath/JSONPath expression or the headerField against.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $sourceId
     * @return static
     */
    public function setSourceId(null|string|FHIRIdPrimitive|FHIRId $sourceId): self
    {
        if (null === $sourceId) {
            unset($this->sourceId);
            return $this;
        }
        if (!($sourceId instanceof FHIRId)) {
            $sourceId = new FHIRId(value: $sourceId);
        }
        $this->sourceId = $sourceId;
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
     * The ID of the Profile to validate against.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getValidateProfileId(): null|FHIRId
    {
        return $this->validateProfileId ?? null;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The ID of the Profile to validate against.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $validateProfileId
     * @return static
     */
    public function setValidateProfileId(null|string|FHIRIdPrimitive|FHIRId $validateProfileId): self
    {
        if (null === $validateProfileId) {
            unset($this->validateProfileId);
            return $this;
        }
        if (!($validateProfileId instanceof FHIRId)) {
            $validateProfileId = new FHIRId(value: $validateProfileId);
        }
        $this->validateProfileId = $validateProfileId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value to compare to.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getValue(): null|FHIRString
    {
        return $this->value ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The value to compare to.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $value
     * @return static
     */
    public function setValue(null|string|FHIRStringPrimitive|FHIRString $value): self
    {
        if (null === $value) {
            unset($this->value);
            return $this;
        }
        if (!($value instanceof FHIRString)) {
            $value = new FHIRString(value: $value);
        }
        $this->value = $value;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution will produce a warning only on error for this
     * assert.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getWarningOnly(): null|FHIRBoolean
    {
        return $this->warningOnly ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not the test execution will produce a warning only on error for this
     * assert.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $warningOnly
     * @return static
     */
    public function setWarningOnly(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $warningOnly): self
    {
        if (null === $warningOnly) {
            unset($this->warningOnly);
            return $this;
        }
        if (!($warningOnly instanceof FHIRBoolean)) {
            $warningOnly = new FHIRBoolean(value: $warningOnly);
        }
        $this->warningOnly = $warningOnly;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptAssert $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptAssert
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTestScriptAssert)) {
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
            } else if (self::FIELD_LABEL === $cen) {
                $type->setLabel(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DIRECTION === $cen) {
                $type->setDirection(FHIRAssertionDirectionType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMPARE_TO_SOURCE_ID === $cen) {
                $type->setCompareToSourceId(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMPARE_TO_SOURCE_EXPRESSION === $cen) {
                $type->setCompareToSourceExpression(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMPARE_TO_SOURCE_PATH === $cen) {
                $type->setCompareToSourcePath(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTENT_TYPE === $cen) {
                $type->setContentType(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXPRESSION === $cen) {
                $type->setExpression(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_HEADER_FIELD === $cen) {
                $type->setHeaderField(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MINIMUM_ID === $cen) {
                $type->setMinimumId(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAVIGATION_LINKS === $cen) {
                $type->setNavigationLinks(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OPERATOR === $cen) {
                $type->setOperator(FHIRAssertionOperatorType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PATH === $cen) {
                $type->setPath(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST_METHOD === $cen) {
                $type->setRequestMethod(FHIRTestScriptRequestMethodCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST_URL === $cen) {
                $type->setRequestURL(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESOURCE === $cen) {
                $type->setResource(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESPONSE === $cen) {
                $type->setResponse(FHIRAssertionResponseTypes::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESPONSE_CODE === $cen) {
                $type->setResponseCode(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_ID === $cen) {
                $type->setSourceId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDATE_PROFILE_ID === $cen) {
                $type->setValidateProfileId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALUE === $cen) {
                $type->setValue(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_WARNING_ONLY === $cen) {
                $type->setWarningOnly(FHIRBoolean::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LABEL])) {
            if (isset($type->label)) {
                $type->label->setValue((string)$attributes[self::FIELD_LABEL]);
            } else {
                $type->setLabel((string)$attributes[self::FIELD_LABEL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LABEL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DIRECTION])) {
            if (isset($type->direction)) {
                $type->direction->setValue((string)$attributes[self::FIELD_DIRECTION]);
            } else {
                $type->setDirection((string)$attributes[self::FIELD_DIRECTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DIRECTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COMPARE_TO_SOURCE_ID])) {
            if (isset($type->compareToSourceId)) {
                $type->compareToSourceId->setValue((string)$attributes[self::FIELD_COMPARE_TO_SOURCE_ID]);
            } else {
                $type->setCompareToSourceId((string)$attributes[self::FIELD_COMPARE_TO_SOURCE_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COMPARE_TO_SOURCE_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COMPARE_TO_SOURCE_EXPRESSION])) {
            if (isset($type->compareToSourceExpression)) {
                $type->compareToSourceExpression->setValue((string)$attributes[self::FIELD_COMPARE_TO_SOURCE_EXPRESSION]);
            } else {
                $type->setCompareToSourceExpression((string)$attributes[self::FIELD_COMPARE_TO_SOURCE_EXPRESSION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COMPARE_TO_SOURCE_EXPRESSION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COMPARE_TO_SOURCE_PATH])) {
            if (isset($type->compareToSourcePath)) {
                $type->compareToSourcePath->setValue((string)$attributes[self::FIELD_COMPARE_TO_SOURCE_PATH]);
            } else {
                $type->setCompareToSourcePath((string)$attributes[self::FIELD_COMPARE_TO_SOURCE_PATH]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COMPARE_TO_SOURCE_PATH, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONTENT_TYPE])) {
            if (isset($type->contentType)) {
                $type->contentType->setValue((string)$attributes[self::FIELD_CONTENT_TYPE]);
            } else {
                $type->setContentType((string)$attributes[self::FIELD_CONTENT_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONTENT_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EXPRESSION])) {
            if (isset($type->expression)) {
                $type->expression->setValue((string)$attributes[self::FIELD_EXPRESSION]);
            } else {
                $type->setExpression((string)$attributes[self::FIELD_EXPRESSION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EXPRESSION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_HEADER_FIELD])) {
            if (isset($type->headerField)) {
                $type->headerField->setValue((string)$attributes[self::FIELD_HEADER_FIELD]);
            } else {
                $type->setHeaderField((string)$attributes[self::FIELD_HEADER_FIELD]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_HEADER_FIELD, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MINIMUM_ID])) {
            if (isset($type->minimumId)) {
                $type->minimumId->setValue((string)$attributes[self::FIELD_MINIMUM_ID]);
            } else {
                $type->setMinimumId((string)$attributes[self::FIELD_MINIMUM_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MINIMUM_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAVIGATION_LINKS])) {
            if (isset($type->navigationLinks)) {
                $type->navigationLinks->setValue((string)$attributes[self::FIELD_NAVIGATION_LINKS]);
            } else {
                $type->setNavigationLinks((string)$attributes[self::FIELD_NAVIGATION_LINKS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAVIGATION_LINKS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_OPERATOR])) {
            if (isset($type->operator)) {
                $type->operator->setValue((string)$attributes[self::FIELD_OPERATOR]);
            } else {
                $type->setOperator((string)$attributes[self::FIELD_OPERATOR]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OPERATOR, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PATH])) {
            if (isset($type->path)) {
                $type->path->setValue((string)$attributes[self::FIELD_PATH]);
            } else {
                $type->setPath((string)$attributes[self::FIELD_PATH]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PATH, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REQUEST_METHOD])) {
            if (isset($type->requestMethod)) {
                $type->requestMethod->setValue((string)$attributes[self::FIELD_REQUEST_METHOD]);
            } else {
                $type->setRequestMethod((string)$attributes[self::FIELD_REQUEST_METHOD]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REQUEST_METHOD, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REQUEST_URL])) {
            if (isset($type->requestURL)) {
                $type->requestURL->setValue((string)$attributes[self::FIELD_REQUEST_URL]);
            } else {
                $type->setRequestURL((string)$attributes[self::FIELD_REQUEST_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REQUEST_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RESOURCE])) {
            if (isset($type->resource)) {
                $type->resource->setValue((string)$attributes[self::FIELD_RESOURCE]);
            } else {
                $type->setResource((string)$attributes[self::FIELD_RESOURCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RESOURCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RESPONSE])) {
            if (isset($type->response)) {
                $type->response->setValue((string)$attributes[self::FIELD_RESPONSE]);
            } else {
                $type->setResponse((string)$attributes[self::FIELD_RESPONSE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RESPONSE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RESPONSE_CODE])) {
            if (isset($type->responseCode)) {
                $type->responseCode->setValue((string)$attributes[self::FIELD_RESPONSE_CODE]);
            } else {
                $type->setResponseCode((string)$attributes[self::FIELD_RESPONSE_CODE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RESPONSE_CODE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SOURCE_ID])) {
            if (isset($type->sourceId)) {
                $type->sourceId->setValue((string)$attributes[self::FIELD_SOURCE_ID]);
            } else {
                $type->setSourceId((string)$attributes[self::FIELD_SOURCE_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SOURCE_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALIDATE_PROFILE_ID])) {
            if (isset($type->validateProfileId)) {
                $type->validateProfileId->setValue((string)$attributes[self::FIELD_VALIDATE_PROFILE_ID]);
            } else {
                $type->setValidateProfileId((string)$attributes[self::FIELD_VALIDATE_PROFILE_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALIDATE_PROFILE_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VALUE])) {
            if (isset($type->value)) {
                $type->value->setValue((string)$attributes[self::FIELD_VALUE]);
            } else {
                $type->setValue((string)$attributes[self::FIELD_VALUE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VALUE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_WARNING_ONLY])) {
            if (isset($type->warningOnly)) {
                $type->warningOnly->setValue((string)$attributes[self::FIELD_WARNING_ONLY]);
            } else {
                $type->setWarningOnly((string)$attributes[self::FIELD_WARNING_ONLY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_WARNING_ONLY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->label) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LABEL]) {
            $xw->writeAttribute(self::FIELD_LABEL, $this->label->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->direction) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DIRECTION]) {
            $xw->writeAttribute(self::FIELD_DIRECTION, $this->direction->_getValueAsString());
        }
        if (isset($this->compareToSourceId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COMPARE_TO_SOURCE_ID]) {
            $xw->writeAttribute(self::FIELD_COMPARE_TO_SOURCE_ID, $this->compareToSourceId->_getValueAsString());
        }
        if (isset($this->compareToSourceExpression) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COMPARE_TO_SOURCE_EXPRESSION]) {
            $xw->writeAttribute(self::FIELD_COMPARE_TO_SOURCE_EXPRESSION, $this->compareToSourceExpression->_getValueAsString());
        }
        if (isset($this->compareToSourcePath) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COMPARE_TO_SOURCE_PATH]) {
            $xw->writeAttribute(self::FIELD_COMPARE_TO_SOURCE_PATH, $this->compareToSourcePath->_getValueAsString());
        }
        if (isset($this->contentType) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONTENT_TYPE]) {
            $xw->writeAttribute(self::FIELD_CONTENT_TYPE, $this->contentType->_getValueAsString());
        }
        if (isset($this->expression) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EXPRESSION]) {
            $xw->writeAttribute(self::FIELD_EXPRESSION, $this->expression->_getValueAsString());
        }
        if (isset($this->headerField) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_HEADER_FIELD]) {
            $xw->writeAttribute(self::FIELD_HEADER_FIELD, $this->headerField->_getValueAsString());
        }
        if (isset($this->minimumId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MINIMUM_ID]) {
            $xw->writeAttribute(self::FIELD_MINIMUM_ID, $this->minimumId->_getValueAsString());
        }
        if (isset($this->navigationLinks) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAVIGATION_LINKS]) {
            $xw->writeAttribute(self::FIELD_NAVIGATION_LINKS, $this->navigationLinks->_getValueAsString());
        }
        if (isset($this->operator) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OPERATOR]) {
            $xw->writeAttribute(self::FIELD_OPERATOR, $this->operator->_getValueAsString());
        }
        if (isset($this->path) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PATH]) {
            $xw->writeAttribute(self::FIELD_PATH, $this->path->_getValueAsString());
        }
        if (isset($this->requestMethod) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REQUEST_METHOD]) {
            $xw->writeAttribute(self::FIELD_REQUEST_METHOD, $this->requestMethod->_getValueAsString());
        }
        if (isset($this->requestURL) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REQUEST_URL]) {
            $xw->writeAttribute(self::FIELD_REQUEST_URL, $this->requestURL->_getValueAsString());
        }
        if (isset($this->resource) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RESOURCE]) {
            $xw->writeAttribute(self::FIELD_RESOURCE, $this->resource->_getValueAsString());
        }
        if (isset($this->response) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RESPONSE]) {
            $xw->writeAttribute(self::FIELD_RESPONSE, $this->response->_getValueAsString());
        }
        if (isset($this->responseCode) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RESPONSE_CODE]) {
            $xw->writeAttribute(self::FIELD_RESPONSE_CODE, $this->responseCode->_getValueAsString());
        }
        if (isset($this->sourceId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SOURCE_ID]) {
            $xw->writeAttribute(self::FIELD_SOURCE_ID, $this->sourceId->_getValueAsString());
        }
        if (isset($this->validateProfileId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALIDATE_PROFILE_ID]) {
            $xw->writeAttribute(self::FIELD_VALIDATE_PROFILE_ID, $this->validateProfileId->_getValueAsString());
        }
        if (isset($this->value) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VALUE]) {
            $xw->writeAttribute(self::FIELD_VALUE, $this->value->_getValueAsString());
        }
        if (isset($this->warningOnly) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_WARNING_ONLY]) {
            $xw->writeAttribute(self::FIELD_WARNING_ONLY, $this->warningOnly->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->label)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LABEL]
                || $this->label->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LABEL);
            $this->label->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LABEL]);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->direction)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DIRECTION]
                || $this->direction->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DIRECTION);
            $this->direction->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DIRECTION]);
            $xw->endElement();
        }
        if (isset($this->compareToSourceId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COMPARE_TO_SOURCE_ID]
                || $this->compareToSourceId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COMPARE_TO_SOURCE_ID);
            $this->compareToSourceId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COMPARE_TO_SOURCE_ID]);
            $xw->endElement();
        }
        if (isset($this->compareToSourceExpression)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COMPARE_TO_SOURCE_EXPRESSION]
                || $this->compareToSourceExpression->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COMPARE_TO_SOURCE_EXPRESSION);
            $this->compareToSourceExpression->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COMPARE_TO_SOURCE_EXPRESSION]);
            $xw->endElement();
        }
        if (isset($this->compareToSourcePath)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COMPARE_TO_SOURCE_PATH]
                || $this->compareToSourcePath->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COMPARE_TO_SOURCE_PATH);
            $this->compareToSourcePath->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COMPARE_TO_SOURCE_PATH]);
            $xw->endElement();
        }
        if (isset($this->contentType)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONTENT_TYPE]
                || $this->contentType->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONTENT_TYPE);
            $this->contentType->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONTENT_TYPE]);
            $xw->endElement();
        }
        if (isset($this->expression)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EXPRESSION]
                || $this->expression->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EXPRESSION);
            $this->expression->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EXPRESSION]);
            $xw->endElement();
        }
        if (isset($this->headerField)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_HEADER_FIELD]
                || $this->headerField->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_HEADER_FIELD);
            $this->headerField->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_HEADER_FIELD]);
            $xw->endElement();
        }
        if (isset($this->minimumId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MINIMUM_ID]
                || $this->minimumId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MINIMUM_ID);
            $this->minimumId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MINIMUM_ID]);
            $xw->endElement();
        }
        if (isset($this->navigationLinks)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAVIGATION_LINKS]
                || $this->navigationLinks->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAVIGATION_LINKS);
            $this->navigationLinks->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAVIGATION_LINKS]);
            $xw->endElement();
        }
        if (isset($this->operator)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OPERATOR]
                || $this->operator->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OPERATOR);
            $this->operator->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OPERATOR]);
            $xw->endElement();
        }
        if (isset($this->path)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PATH]
                || $this->path->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PATH);
            $this->path->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PATH]);
            $xw->endElement();
        }
        if (isset($this->requestMethod)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REQUEST_METHOD]
                || $this->requestMethod->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REQUEST_METHOD);
            $this->requestMethod->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REQUEST_METHOD]);
            $xw->endElement();
        }
        if (isset($this->requestURL)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REQUEST_URL]
                || $this->requestURL->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REQUEST_URL);
            $this->requestURL->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REQUEST_URL]);
            $xw->endElement();
        }
        if (isset($this->resource)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RESOURCE]
                || $this->resource->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RESOURCE);
            $this->resource->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RESOURCE]);
            $xw->endElement();
        }
        if (isset($this->response)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RESPONSE]
                || $this->response->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RESPONSE);
            $this->response->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RESPONSE]);
            $xw->endElement();
        }
        if (isset($this->responseCode)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RESPONSE_CODE]
                || $this->responseCode->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RESPONSE_CODE);
            $this->responseCode->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RESPONSE_CODE]);
            $xw->endElement();
        }
        if (isset($this->sourceId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SOURCE_ID]
                || $this->sourceId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SOURCE_ID);
            $this->sourceId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SOURCE_ID]);
            $xw->endElement();
        }
        if (isset($this->validateProfileId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALIDATE_PROFILE_ID]
                || $this->validateProfileId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALIDATE_PROFILE_ID);
            $this->validateProfileId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALIDATE_PROFILE_ID]);
            $xw->endElement();
        }
        if (isset($this->value)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VALUE]
                || $this->value->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VALUE);
            $this->value->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VALUE]);
            $xw->endElement();
        }
        if (isset($this->warningOnly)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_WARNING_ONLY]
                || $this->warningOnly->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_WARNING_ONLY);
            $this->warningOnly->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_WARNING_ONLY]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptAssert $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptAssert
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
        } else if (!($type instanceof FHIRTestScriptAssert)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->label)
            || isset($decoded->_label)
            || property_exists($decoded, self::FIELD_LABEL)
            || property_exists($decoded, self::FIELD_LABEL_EXT)) {
            $v = $decoded->_label ?? new \stdClass();
            $v->value = $decoded->label ?? null;
            $type->setLabel(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->direction)
            || isset($decoded->_direction)
            || property_exists($decoded, self::FIELD_DIRECTION)
            || property_exists($decoded, self::FIELD_DIRECTION_EXT)) {
            $v = $decoded->_direction ?? new \stdClass();
            $v->value = $decoded->direction ?? null;
            $type->setDirection(FHIRAssertionDirectionType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->compareToSourceId)
            || isset($decoded->_compareToSourceId)
            || property_exists($decoded, self::FIELD_COMPARE_TO_SOURCE_ID)
            || property_exists($decoded, self::FIELD_COMPARE_TO_SOURCE_ID_EXT)) {
            $v = $decoded->_compareToSourceId ?? new \stdClass();
            $v->value = $decoded->compareToSourceId ?? null;
            $type->setCompareToSourceId(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->compareToSourceExpression)
            || isset($decoded->_compareToSourceExpression)
            || property_exists($decoded, self::FIELD_COMPARE_TO_SOURCE_EXPRESSION)
            || property_exists($decoded, self::FIELD_COMPARE_TO_SOURCE_EXPRESSION_EXT)) {
            $v = $decoded->_compareToSourceExpression ?? new \stdClass();
            $v->value = $decoded->compareToSourceExpression ?? null;
            $type->setCompareToSourceExpression(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->compareToSourcePath)
            || isset($decoded->_compareToSourcePath)
            || property_exists($decoded, self::FIELD_COMPARE_TO_SOURCE_PATH)
            || property_exists($decoded, self::FIELD_COMPARE_TO_SOURCE_PATH_EXT)) {
            $v = $decoded->_compareToSourcePath ?? new \stdClass();
            $v->value = $decoded->compareToSourcePath ?? null;
            $type->setCompareToSourcePath(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->contentType)
            || isset($decoded->_contentType)
            || property_exists($decoded, self::FIELD_CONTENT_TYPE)
            || property_exists($decoded, self::FIELD_CONTENT_TYPE_EXT)) {
            $v = $decoded->_contentType ?? new \stdClass();
            $v->value = $decoded->contentType ?? null;
            $type->setContentType(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->expression)
            || isset($decoded->_expression)
            || property_exists($decoded, self::FIELD_EXPRESSION)
            || property_exists($decoded, self::FIELD_EXPRESSION_EXT)) {
            $v = $decoded->_expression ?? new \stdClass();
            $v->value = $decoded->expression ?? null;
            $type->setExpression(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->headerField)
            || isset($decoded->_headerField)
            || property_exists($decoded, self::FIELD_HEADER_FIELD)
            || property_exists($decoded, self::FIELD_HEADER_FIELD_EXT)) {
            $v = $decoded->_headerField ?? new \stdClass();
            $v->value = $decoded->headerField ?? null;
            $type->setHeaderField(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->minimumId)
            || isset($decoded->_minimumId)
            || property_exists($decoded, self::FIELD_MINIMUM_ID)
            || property_exists($decoded, self::FIELD_MINIMUM_ID_EXT)) {
            $v = $decoded->_minimumId ?? new \stdClass();
            $v->value = $decoded->minimumId ?? null;
            $type->setMinimumId(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->navigationLinks)
            || isset($decoded->_navigationLinks)
            || property_exists($decoded, self::FIELD_NAVIGATION_LINKS)
            || property_exists($decoded, self::FIELD_NAVIGATION_LINKS_EXT)) {
            $v = $decoded->_navigationLinks ?? new \stdClass();
            $v->value = $decoded->navigationLinks ?? null;
            $type->setNavigationLinks(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->operator)
            || isset($decoded->_operator)
            || property_exists($decoded, self::FIELD_OPERATOR)
            || property_exists($decoded, self::FIELD_OPERATOR_EXT)) {
            $v = $decoded->_operator ?? new \stdClass();
            $v->value = $decoded->operator ?? null;
            $type->setOperator(FHIRAssertionOperatorType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->path)
            || isset($decoded->_path)
            || property_exists($decoded, self::FIELD_PATH)
            || property_exists($decoded, self::FIELD_PATH_EXT)) {
            $v = $decoded->_path ?? new \stdClass();
            $v->value = $decoded->path ?? null;
            $type->setPath(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->requestMethod)
            || isset($decoded->_requestMethod)
            || property_exists($decoded, self::FIELD_REQUEST_METHOD)
            || property_exists($decoded, self::FIELD_REQUEST_METHOD_EXT)) {
            $v = $decoded->_requestMethod ?? new \stdClass();
            $v->value = $decoded->requestMethod ?? null;
            $type->setRequestMethod(FHIRTestScriptRequestMethodCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->requestURL)
            || isset($decoded->_requestURL)
            || property_exists($decoded, self::FIELD_REQUEST_URL)
            || property_exists($decoded, self::FIELD_REQUEST_URL_EXT)) {
            $v = $decoded->_requestURL ?? new \stdClass();
            $v->value = $decoded->requestURL ?? null;
            $type->setRequestURL(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->resource)
            || isset($decoded->_resource)
            || property_exists($decoded, self::FIELD_RESOURCE)
            || property_exists($decoded, self::FIELD_RESOURCE_EXT)) {
            $v = $decoded->_resource ?? new \stdClass();
            $v->value = $decoded->resource ?? null;
            $type->setResource(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->response)
            || isset($decoded->_response)
            || property_exists($decoded, self::FIELD_RESPONSE)
            || property_exists($decoded, self::FIELD_RESPONSE_EXT)) {
            $v = $decoded->_response ?? new \stdClass();
            $v->value = $decoded->response ?? null;
            $type->setResponse(FHIRAssertionResponseTypes::jsonUnserialize($v, $config));
        }
        if (isset($decoded->responseCode)
            || isset($decoded->_responseCode)
            || property_exists($decoded, self::FIELD_RESPONSE_CODE)
            || property_exists($decoded, self::FIELD_RESPONSE_CODE_EXT)) {
            $v = $decoded->_responseCode ?? new \stdClass();
            $v->value = $decoded->responseCode ?? null;
            $type->setResponseCode(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->sourceId)
            || isset($decoded->_sourceId)
            || property_exists($decoded, self::FIELD_SOURCE_ID)
            || property_exists($decoded, self::FIELD_SOURCE_ID_EXT)) {
            $v = $decoded->_sourceId ?? new \stdClass();
            $v->value = $decoded->sourceId ?? null;
            $type->setSourceId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->validateProfileId)
            || isset($decoded->_validateProfileId)
            || property_exists($decoded, self::FIELD_VALIDATE_PROFILE_ID)
            || property_exists($decoded, self::FIELD_VALIDATE_PROFILE_ID_EXT)) {
            $v = $decoded->_validateProfileId ?? new \stdClass();
            $v->value = $decoded->validateProfileId ?? null;
            $type->setValidateProfileId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->value)
            || isset($decoded->_value)
            || property_exists($decoded, self::FIELD_VALUE)
            || property_exists($decoded, self::FIELD_VALUE_EXT)) {
            $v = $decoded->_value ?? new \stdClass();
            $v->value = $decoded->value ?? null;
            $type->setValue(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->warningOnly)
            || isset($decoded->_warningOnly)
            || property_exists($decoded, self::FIELD_WARNING_ONLY)
            || property_exists($decoded, self::FIELD_WARNING_ONLY_EXT)) {
            $v = $decoded->_warningOnly ?? new \stdClass();
            $v->value = $decoded->warningOnly ?? null;
            $type->setWarningOnly(FHIRBoolean::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->label)) {
            if (null !== ($val = $this->label->getValue())) {
                $out->label = $val;
            }
            if ($this->label->_nonValueFieldDefined()) {
                $ext = $this->label->jsonSerialize();
                unset($ext->value);
                $out->_label = $ext;
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
        if (isset($this->direction)) {
            if (null !== ($val = $this->direction->getValue())) {
                $out->direction = $val;
            }
            if ($this->direction->_nonValueFieldDefined()) {
                $ext = $this->direction->jsonSerialize();
                unset($ext->value);
                $out->_direction = $ext;
            }
        }
        if (isset($this->compareToSourceId)) {
            if (null !== ($val = $this->compareToSourceId->getValue())) {
                $out->compareToSourceId = $val;
            }
            if ($this->compareToSourceId->_nonValueFieldDefined()) {
                $ext = $this->compareToSourceId->jsonSerialize();
                unset($ext->value);
                $out->_compareToSourceId = $ext;
            }
        }
        if (isset($this->compareToSourceExpression)) {
            if (null !== ($val = $this->compareToSourceExpression->getValue())) {
                $out->compareToSourceExpression = $val;
            }
            if ($this->compareToSourceExpression->_nonValueFieldDefined()) {
                $ext = $this->compareToSourceExpression->jsonSerialize();
                unset($ext->value);
                $out->_compareToSourceExpression = $ext;
            }
        }
        if (isset($this->compareToSourcePath)) {
            if (null !== ($val = $this->compareToSourcePath->getValue())) {
                $out->compareToSourcePath = $val;
            }
            if ($this->compareToSourcePath->_nonValueFieldDefined()) {
                $ext = $this->compareToSourcePath->jsonSerialize();
                unset($ext->value);
                $out->_compareToSourcePath = $ext;
            }
        }
        if (isset($this->contentType)) {
            if (null !== ($val = $this->contentType->getValue())) {
                $out->contentType = $val;
            }
            if ($this->contentType->_nonValueFieldDefined()) {
                $ext = $this->contentType->jsonSerialize();
                unset($ext->value);
                $out->_contentType = $ext;
            }
        }
        if (isset($this->expression)) {
            if (null !== ($val = $this->expression->getValue())) {
                $out->expression = $val;
            }
            if ($this->expression->_nonValueFieldDefined()) {
                $ext = $this->expression->jsonSerialize();
                unset($ext->value);
                $out->_expression = $ext;
            }
        }
        if (isset($this->headerField)) {
            if (null !== ($val = $this->headerField->getValue())) {
                $out->headerField = $val;
            }
            if ($this->headerField->_nonValueFieldDefined()) {
                $ext = $this->headerField->jsonSerialize();
                unset($ext->value);
                $out->_headerField = $ext;
            }
        }
        if (isset($this->minimumId)) {
            if (null !== ($val = $this->minimumId->getValue())) {
                $out->minimumId = $val;
            }
            if ($this->minimumId->_nonValueFieldDefined()) {
                $ext = $this->minimumId->jsonSerialize();
                unset($ext->value);
                $out->_minimumId = $ext;
            }
        }
        if (isset($this->navigationLinks)) {
            if (null !== ($val = $this->navigationLinks->getValue())) {
                $out->navigationLinks = $val;
            }
            if ($this->navigationLinks->_nonValueFieldDefined()) {
                $ext = $this->navigationLinks->jsonSerialize();
                unset($ext->value);
                $out->_navigationLinks = $ext;
            }
        }
        if (isset($this->operator)) {
            if (null !== ($val = $this->operator->getValue())) {
                $out->operator = $val;
            }
            if ($this->operator->_nonValueFieldDefined()) {
                $ext = $this->operator->jsonSerialize();
                unset($ext->value);
                $out->_operator = $ext;
            }
        }
        if (isset($this->path)) {
            if (null !== ($val = $this->path->getValue())) {
                $out->path = $val;
            }
            if ($this->path->_nonValueFieldDefined()) {
                $ext = $this->path->jsonSerialize();
                unset($ext->value);
                $out->_path = $ext;
            }
        }
        if (isset($this->requestMethod)) {
            if (null !== ($val = $this->requestMethod->getValue())) {
                $out->requestMethod = $val;
            }
            if ($this->requestMethod->_nonValueFieldDefined()) {
                $ext = $this->requestMethod->jsonSerialize();
                unset($ext->value);
                $out->_requestMethod = $ext;
            }
        }
        if (isset($this->requestURL)) {
            if (null !== ($val = $this->requestURL->getValue())) {
                $out->requestURL = $val;
            }
            if ($this->requestURL->_nonValueFieldDefined()) {
                $ext = $this->requestURL->jsonSerialize();
                unset($ext->value);
                $out->_requestURL = $ext;
            }
        }
        if (isset($this->resource)) {
            if (null !== ($val = $this->resource->getValue())) {
                $out->resource = $val;
            }
            if ($this->resource->_nonValueFieldDefined()) {
                $ext = $this->resource->jsonSerialize();
                unset($ext->value);
                $out->_resource = $ext;
            }
        }
        if (isset($this->response)) {
            if (null !== ($val = $this->response->getValue())) {
                $out->response = $val;
            }
            if ($this->response->_nonValueFieldDefined()) {
                $ext = $this->response->jsonSerialize();
                unset($ext->value);
                $out->_response = $ext;
            }
        }
        if (isset($this->responseCode)) {
            if (null !== ($val = $this->responseCode->getValue())) {
                $out->responseCode = $val;
            }
            if ($this->responseCode->_nonValueFieldDefined()) {
                $ext = $this->responseCode->jsonSerialize();
                unset($ext->value);
                $out->_responseCode = $ext;
            }
        }
        if (isset($this->sourceId)) {
            if (null !== ($val = $this->sourceId->getValue())) {
                $out->sourceId = $val;
            }
            if ($this->sourceId->_nonValueFieldDefined()) {
                $ext = $this->sourceId->jsonSerialize();
                unset($ext->value);
                $out->_sourceId = $ext;
            }
        }
        if (isset($this->validateProfileId)) {
            if (null !== ($val = $this->validateProfileId->getValue())) {
                $out->validateProfileId = $val;
            }
            if ($this->validateProfileId->_nonValueFieldDefined()) {
                $ext = $this->validateProfileId->jsonSerialize();
                unset($ext->value);
                $out->_validateProfileId = $ext;
            }
        }
        if (isset($this->value)) {
            if (null !== ($val = $this->value->getValue())) {
                $out->value = $val;
            }
            if ($this->value->_nonValueFieldDefined()) {
                $ext = $this->value->jsonSerialize();
                unset($ext->value);
                $out->_value = $ext;
            }
        }
        if (isset($this->warningOnly)) {
            if (null !== ($val = $this->warningOnly->getValue())) {
                $out->warningOnly = $val;
            }
            if ($this->warningOnly->_nonValueFieldDefined()) {
                $ext = $this->warningOnly->jsonSerialize();
                unset($ext->value);
                $out->_warningOnly = $ext;
            }
        }
        return $out;
    }
}
