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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A structured set of tests against a FHIR server or client implementation to
 * determine compliance against the FHIR specification.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTestScriptOperation extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TEST_SCRIPT_DOT_OPERATION;

    /* class_default.php:56 */
    public const FIELD_TYPE = 'type';
    public const FIELD_RESOURCE = 'resource';
    public const FIELD_RESOURCE_EXT = '_resource';
    public const FIELD_LABEL = 'label';
    public const FIELD_LABEL_EXT = '_label';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_ACCEPT = 'accept';
    public const FIELD_ACCEPT_EXT = '_accept';
    public const FIELD_CONTENT_TYPE = 'contentType';
    public const FIELD_CONTENT_TYPE_EXT = '_contentType';
    public const FIELD_DESTINATION = 'destination';
    public const FIELD_DESTINATION_EXT = '_destination';
    public const FIELD_ENCODE_REQUEST_URL = 'encodeRequestUrl';
    public const FIELD_ENCODE_REQUEST_URL_EXT = '_encodeRequestUrl';
    public const FIELD_METHOD = 'method';
    public const FIELD_METHOD_EXT = '_method';
    public const FIELD_ORIGIN = 'origin';
    public const FIELD_ORIGIN_EXT = '_origin';
    public const FIELD_PARAMS = 'params';
    public const FIELD_PARAMS_EXT = '_params';
    public const FIELD_REQUEST_HEADER = 'requestHeader';
    public const FIELD_REQUEST_ID = 'requestId';
    public const FIELD_REQUEST_ID_EXT = '_requestId';
    public const FIELD_RESPONSE_ID = 'responseId';
    public const FIELD_RESPONSE_ID_EXT = '_responseId';
    public const FIELD_SOURCE_ID = 'sourceId';
    public const FIELD_SOURCE_ID_EXT = '_sourceId';
    public const FIELD_TARGET_ID = 'targetId';
    public const FIELD_TARGET_ID_EXT = '_targetId';
    public const FIELD_URL = 'url';
    public const FIELD_URL_EXT = '_url';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_ENCODE_REQUEST_URL => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_RESOURCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LABEL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ACCEPT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CONTENT_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESTINATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ENCODE_REQUEST_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_METHOD => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ORIGIN => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PARAMS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_REQUEST_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RESPONSE_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SOURCE_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TARGET_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Server interaction or operation type.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    #[FHIRCoding]
    protected FHIRCoding $type;
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
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The mime-type to use for RESTful operation in the 'Accept' header.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $accept;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The mime-type to use for RESTful operation in the 'Content-Type' header.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $contentType;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The server where the request message is destined for. Must be one of the server
     * numbers listed in TestScript.destination section.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $destination;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not to implicitly send the request url in encoded format. The default
     * is true to match the standard RESTful client behavior. Set to false when
     * communicating with a server that does not support encoded url paths.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $encodeRequestUrl;
    /**
     * The allowable request method or HTTP operation codes.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The HTTP method the test engine MUST use for this operation regardless of any
     * other operation details.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    #[FHIRTestScriptRequestMethodCode]
    protected FHIRTestScriptRequestMethodCode $method;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The server where the request message originates from. Must be one of the server
     * numbers listed in TestScript.origin section.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $origin;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Path plus parameters after [type]. Used to set parts of the request URL
     * explicitly.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $params;
    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * Header elements would be used to set HTTP headers.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptRequestHeader>
     */
    #[FHIRTestScriptRequestHeader]
    protected array $requestHeader;
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The fixture id (maybe new) to map to the request.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $requestId;
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The fixture id (maybe new) to map to the response.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $responseId;
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The id of the fixture used as the body of a PUT or POST request.
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
     * Id of fixture used for extracting the [id], [type], and [vid] for GET requests.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $targetId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Complete request URL.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $url;

    /* constructor.php:61 */
    /**
     * FHIRTestScriptOperation Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $type
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $resource
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $label
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $accept
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $contentType
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $destination
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $encodeRequestUrl
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode $method
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $origin
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $params
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptRequestHeader> $requestHeader
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $requestId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $responseId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $sourceId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $targetId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $url
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCoding $type = null,
                                null|string|FHIRCodePrimitive|FHIRCode $resource = null,
                                null|string|FHIRStringPrimitive|FHIRString $label = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|string|FHIRCodePrimitive|FHIRCode $accept = null,
                                null|string|FHIRCodePrimitive|FHIRCode $contentType = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $destination = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $encodeRequestUrl = null,
                                null|string|FHIRTestScriptRequestMethodCodeList|FHIRTestScriptRequestMethodCode $method = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $origin = null,
                                null|string|FHIRStringPrimitive|FHIRString $params = null,
                                null|iterable $requestHeader = null,
                                null|string|FHIRIdPrimitive|FHIRId $requestId = null,
                                null|string|FHIRIdPrimitive|FHIRId $responseId = null,
                                null|string|FHIRIdPrimitive|FHIRId $sourceId = null,
                                null|string|FHIRIdPrimitive|FHIRId $targetId = null,
                                null|string|FHIRStringPrimitive|FHIRString $url = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $resource) {
            $this->setResource($resource);
        }
        if (null !== $label) {
            $this->setLabel($label);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $accept) {
            $this->setAccept($accept);
        }
        if (null !== $contentType) {
            $this->setContentType($contentType);
        }
        if (null !== $destination) {
            $this->setDestination($destination);
        }
        if (null !== $encodeRequestUrl) {
            $this->setEncodeRequestUrl($encodeRequestUrl);
        }
        if (null !== $method) {
            $this->setMethod($method);
        }
        if (null !== $origin) {
            $this->setOrigin($origin);
        }
        if (null !== $params) {
            $this->setParams($params);
        }
        if (null !== $requestHeader) {
            $this->setRequestHeader(...$requestHeader);
        }
        if (null !== $requestId) {
            $this->setRequestId($requestId);
        }
        if (null !== $responseId) {
            $this->setResponseId($responseId);
        }
        if (null !== $sourceId) {
            $this->setSourceId($sourceId);
        }
        if (null !== $targetId) {
            $this->setTargetId($targetId);
        }
        if (null !== $url) {
            $this->setUrl($url);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Server interaction or operation type.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    public function getType(): null|FHIRCoding
    {
        return $this->type ?? null;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Server interaction or operation type.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $type
     * @return static
     */
    public function setType(null|FHIRCoding $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        $this->type = $type;
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
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The mime-type to use for RESTful operation in the 'Accept' header.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getAccept(): null|FHIRCode
    {
        return $this->accept ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The mime-type to use for RESTful operation in the 'Accept' header.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $accept
     * @return static
     */
    public function setAccept(null|string|FHIRCodePrimitive|FHIRCode $accept): self
    {
        if (null === $accept) {
            unset($this->accept);
            return $this;
        }
        if (!($accept instanceof FHIRCode)) {
            $accept = new FHIRCode(value: $accept);
        }
        $this->accept = $accept;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The mime-type to use for RESTful operation in the 'Content-Type' header.
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
     * The mime-type to use for RESTful operation in the 'Content-Type' header.
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
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The server where the request message is destined for. Must be one of the server
     * numbers listed in TestScript.destination section.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getDestination(): null|FHIRInteger
    {
        return $this->destination ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The server where the request message is destined for. Must be one of the server
     * numbers listed in TestScript.destination section.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $destination
     * @return static
     */
    public function setDestination(null|string|float|FHIRIntegerPrimitive|FHIRInteger $destination): self
    {
        if (null === $destination) {
            unset($this->destination);
            return $this;
        }
        if (!($destination instanceof FHIRInteger)) {
            $destination = new FHIRInteger(value: $destination);
        }
        $this->destination = $destination;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not to implicitly send the request url in encoded format. The default
     * is true to match the standard RESTful client behavior. Set to false when
     * communicating with a server that does not support encoded url paths.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getEncodeRequestUrl(): null|FHIRBoolean
    {
        return $this->encodeRequestUrl ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not to implicitly send the request url in encoded format. The default
     * is true to match the standard RESTful client behavior. Set to false when
     * communicating with a server that does not support encoded url paths.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $encodeRequestUrl
     * @return static
     */
    public function setEncodeRequestUrl(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $encodeRequestUrl): self
    {
        if (null === $encodeRequestUrl) {
            unset($this->encodeRequestUrl);
            return $this;
        }
        if (!($encodeRequestUrl instanceof FHIRBoolean)) {
            $encodeRequestUrl = new FHIRBoolean(value: $encodeRequestUrl);
        }
        $this->encodeRequestUrl = $encodeRequestUrl;
        return $this;
    }

    /**
     * The allowable request method or HTTP operation codes.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The HTTP method the test engine MUST use for this operation regardless of any
     * other operation details.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    public function getMethod(): null|FHIRTestScriptRequestMethodCode
    {
        return $this->method ?? null;
    }

    /**
     * The allowable request method or HTTP operation codes.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The HTTP method the test engine MUST use for this operation regardless of any
     * other operation details.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestScriptRequestMethodCodeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestScriptRequestMethodCode $method
     * @return static
     */
    public function setMethod(null|string|FHIRTestScriptRequestMethodCodeList|FHIRTestScriptRequestMethodCode $method): self
    {
        if (null === $method) {
            unset($this->method);
            return $this;
        }
        if (!($method instanceof FHIRTestScriptRequestMethodCode)) {
            $method = new FHIRTestScriptRequestMethodCode(value: $method);
        }
        $this->method = $method;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The server where the request message originates from. Must be one of the server
     * numbers listed in TestScript.origin section.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getOrigin(): null|FHIRInteger
    {
        return $this->origin ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The server where the request message originates from. Must be one of the server
     * numbers listed in TestScript.origin section.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $origin
     * @return static
     */
    public function setOrigin(null|string|float|FHIRIntegerPrimitive|FHIRInteger $origin): self
    {
        if (null === $origin) {
            unset($this->origin);
            return $this;
        }
        if (!($origin instanceof FHIRInteger)) {
            $origin = new FHIRInteger(value: $origin);
        }
        $this->origin = $origin;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Path plus parameters after [type]. Used to set parts of the request URL
     * explicitly.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getParams(): null|FHIRString
    {
        return $this->params ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Path plus parameters after [type]. Used to set parts of the request URL
     * explicitly.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $params
     * @return static
     */
    public function setParams(null|string|FHIRStringPrimitive|FHIRString $params): self
    {
        if (null === $params) {
            unset($this->params);
            return $this;
        }
        if (!($params instanceof FHIRString)) {
            $params = new FHIRString(value: $params);
        }
        $this->params = $params;
        return $this;
    }

    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * Header elements would be used to set HTTP headers.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptRequestHeader>
     */
    public function getRequestHeader(): array
    {
        return $this->requestHeader ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptRequestHeader>
     */
    public function getRequestHeaderIterator(): iterable
    {
        if (!isset($this->requestHeader)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->requestHeader);
    }

    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * Header elements would be used to set HTTP headers.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptRequestHeader $requestHeader
     * @return static
     */
    public function addRequestHeader(FHIRTestScriptRequestHeader $requestHeader): self
    {
        if (!isset($this->requestHeader)) {
            $this->requestHeader = [];
        }
        $this->requestHeader[] = $requestHeader;
        return $this;
    }

    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * Header elements would be used to set HTTP headers.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptRequestHeader ...$requestHeader
     * @return static
     */
    public function setRequestHeader(FHIRTestScriptRequestHeader ...$requestHeader): self
    {
        if ([] === $requestHeader) {
            unset($this->requestHeader);
            return $this;
        }
        $this->requestHeader = $requestHeader;
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
     * The fixture id (maybe new) to map to the request.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getRequestId(): null|FHIRId
    {
        return $this->requestId ?? null;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The fixture id (maybe new) to map to the request.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $requestId
     * @return static
     */
    public function setRequestId(null|string|FHIRIdPrimitive|FHIRId $requestId): self
    {
        if (null === $requestId) {
            unset($this->requestId);
            return $this;
        }
        if (!($requestId instanceof FHIRId)) {
            $requestId = new FHIRId(value: $requestId);
        }
        $this->requestId = $requestId;
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
     * The fixture id (maybe new) to map to the response.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getResponseId(): null|FHIRId
    {
        return $this->responseId ?? null;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The fixture id (maybe new) to map to the response.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $responseId
     * @return static
     */
    public function setResponseId(null|string|FHIRIdPrimitive|FHIRId $responseId): self
    {
        if (null === $responseId) {
            unset($this->responseId);
            return $this;
        }
        if (!($responseId instanceof FHIRId)) {
            $responseId = new FHIRId(value: $responseId);
        }
        $this->responseId = $responseId;
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
     * The id of the fixture used as the body of a PUT or POST request.
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
     * The id of the fixture used as the body of a PUT or POST request.
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
     * Id of fixture used for extracting the [id], [type], and [vid] for GET requests.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getTargetId(): null|FHIRId
    {
        return $this->targetId ?? null;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Id of fixture used for extracting the [id], [type], and [vid] for GET requests.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $targetId
     * @return static
     */
    public function setTargetId(null|string|FHIRIdPrimitive|FHIRId $targetId): self
    {
        if (null === $targetId) {
            unset($this->targetId);
            return $this;
        }
        if (!($targetId instanceof FHIRId)) {
            $targetId = new FHIRId(value: $targetId);
        }
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Complete request URL.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getUrl(): null|FHIRString
    {
        return $this->url ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Complete request URL.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $url
     * @return static
     */
    public function setUrl(null|string|FHIRStringPrimitive|FHIRString $url): self
    {
        if (null === $url) {
            unset($this->url);
            return $this;
        }
        if (!($url instanceof FHIRString)) {
            $url = new FHIRString(value: $url);
        }
        $this->url = $url;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptOperation
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTestScriptOperation)) {
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
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESOURCE === $cen) {
                $type->setResource(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LABEL === $cen) {
                $type->setLabel(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACCEPT === $cen) {
                $type->setAccept(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTENT_TYPE === $cen) {
                $type->setContentType(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESTINATION === $cen) {
                $type->setDestination(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENCODE_REQUEST_URL === $cen) {
                $type->setEncodeRequestUrl(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_METHOD === $cen) {
                $type->setMethod(FHIRTestScriptRequestMethodCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORIGIN === $cen) {
                $type->setOrigin(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARAMS === $cen) {
                $type->setParams(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST_HEADER === $cen) {
                $type->addRequestHeader(FHIRTestScriptRequestHeader::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST_ID === $cen) {
                $type->setRequestId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESPONSE_ID === $cen) {
                $type->setResponseId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_ID === $cen) {
                $type->setSourceId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET_ID === $cen) {
                $type->setTargetId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_URL === $cen) {
                $type->setUrl(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RESOURCE])) {
            if (isset($type->resource)) {
                $type->resource->setValue((string)$attributes[self::FIELD_RESOURCE]);
            } else {
                $type->setResource((string)$attributes[self::FIELD_RESOURCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RESOURCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($attributes[self::FIELD_ACCEPT])) {
            if (isset($type->accept)) {
                $type->accept->setValue((string)$attributes[self::FIELD_ACCEPT]);
            } else {
                $type->setAccept((string)$attributes[self::FIELD_ACCEPT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ACCEPT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONTENT_TYPE])) {
            if (isset($type->contentType)) {
                $type->contentType->setValue((string)$attributes[self::FIELD_CONTENT_TYPE]);
            } else {
                $type->setContentType((string)$attributes[self::FIELD_CONTENT_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONTENT_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESTINATION])) {
            if (isset($type->destination)) {
                $type->destination->setValue((string)$attributes[self::FIELD_DESTINATION]);
            } else {
                $type->setDestination((string)$attributes[self::FIELD_DESTINATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESTINATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ENCODE_REQUEST_URL])) {
            if (isset($type->encodeRequestUrl)) {
                $type->encodeRequestUrl->setValue((string)$attributes[self::FIELD_ENCODE_REQUEST_URL]);
            } else {
                $type->setEncodeRequestUrl((string)$attributes[self::FIELD_ENCODE_REQUEST_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ENCODE_REQUEST_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_METHOD])) {
            if (isset($type->method)) {
                $type->method->setValue((string)$attributes[self::FIELD_METHOD]);
            } else {
                $type->setMethod((string)$attributes[self::FIELD_METHOD]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_METHOD, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ORIGIN])) {
            if (isset($type->origin)) {
                $type->origin->setValue((string)$attributes[self::FIELD_ORIGIN]);
            } else {
                $type->setOrigin((string)$attributes[self::FIELD_ORIGIN]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ORIGIN, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PARAMS])) {
            if (isset($type->params)) {
                $type->params->setValue((string)$attributes[self::FIELD_PARAMS]);
            } else {
                $type->setParams((string)$attributes[self::FIELD_PARAMS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PARAMS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REQUEST_ID])) {
            if (isset($type->requestId)) {
                $type->requestId->setValue((string)$attributes[self::FIELD_REQUEST_ID]);
            } else {
                $type->setRequestId((string)$attributes[self::FIELD_REQUEST_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REQUEST_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RESPONSE_ID])) {
            if (isset($type->responseId)) {
                $type->responseId->setValue((string)$attributes[self::FIELD_RESPONSE_ID]);
            } else {
                $type->setResponseId((string)$attributes[self::FIELD_RESPONSE_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RESPONSE_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SOURCE_ID])) {
            if (isset($type->sourceId)) {
                $type->sourceId->setValue((string)$attributes[self::FIELD_SOURCE_ID]);
            } else {
                $type->setSourceId((string)$attributes[self::FIELD_SOURCE_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SOURCE_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TARGET_ID])) {
            if (isset($type->targetId)) {
                $type->targetId->setValue((string)$attributes[self::FIELD_TARGET_ID]);
            } else {
                $type->setTargetId((string)$attributes[self::FIELD_TARGET_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TARGET_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_URL])) {
            if (isset($type->url)) {
                $type->url->setValue((string)$attributes[self::FIELD_URL]);
            } else {
                $type->setUrl((string)$attributes[self::FIELD_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->resource) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RESOURCE]) {
            $xw->writeAttribute(self::FIELD_RESOURCE, $this->resource->_getValueAsString());
        }
        if (isset($this->label) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LABEL]) {
            $xw->writeAttribute(self::FIELD_LABEL, $this->label->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->accept) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ACCEPT]) {
            $xw->writeAttribute(self::FIELD_ACCEPT, $this->accept->_getValueAsString());
        }
        if (isset($this->contentType) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONTENT_TYPE]) {
            $xw->writeAttribute(self::FIELD_CONTENT_TYPE, $this->contentType->_getValueAsString());
        }
        if (isset($this->destination) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESTINATION]) {
            $xw->writeAttribute(self::FIELD_DESTINATION, $this->destination->_getValueAsString());
        }
        if (isset($this->encodeRequestUrl) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ENCODE_REQUEST_URL]) {
            $xw->writeAttribute(self::FIELD_ENCODE_REQUEST_URL, $this->encodeRequestUrl->_getValueAsString());
        }
        if (isset($this->method) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_METHOD]) {
            $xw->writeAttribute(self::FIELD_METHOD, $this->method->_getValueAsString());
        }
        if (isset($this->origin) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ORIGIN]) {
            $xw->writeAttribute(self::FIELD_ORIGIN, $this->origin->_getValueAsString());
        }
        if (isset($this->params) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PARAMS]) {
            $xw->writeAttribute(self::FIELD_PARAMS, $this->params->_getValueAsString());
        }
        if (isset($this->requestId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REQUEST_ID]) {
            $xw->writeAttribute(self::FIELD_REQUEST_ID, $this->requestId->_getValueAsString());
        }
        if (isset($this->responseId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RESPONSE_ID]) {
            $xw->writeAttribute(self::FIELD_RESPONSE_ID, $this->responseId->_getValueAsString());
        }
        if (isset($this->sourceId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SOURCE_ID]) {
            $xw->writeAttribute(self::FIELD_SOURCE_ID, $this->sourceId->_getValueAsString());
        }
        if (isset($this->targetId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TARGET_ID]) {
            $xw->writeAttribute(self::FIELD_TARGET_ID, $this->targetId->_getValueAsString());
        }
        if (isset($this->url) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_URL]) {
            $xw->writeAttribute(self::FIELD_URL, $this->url->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->resource)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RESOURCE]
                || $this->resource->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RESOURCE);
            $this->resource->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RESOURCE]);
            $xw->endElement();
        }
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
        if (isset($this->accept)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ACCEPT]
                || $this->accept->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ACCEPT);
            $this->accept->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ACCEPT]);
            $xw->endElement();
        }
        if (isset($this->contentType)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONTENT_TYPE]
                || $this->contentType->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONTENT_TYPE);
            $this->contentType->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONTENT_TYPE]);
            $xw->endElement();
        }
        if (isset($this->destination)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESTINATION]
                || $this->destination->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESTINATION);
            $this->destination->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESTINATION]);
            $xw->endElement();
        }
        if (isset($this->encodeRequestUrl)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ENCODE_REQUEST_URL]
                || $this->encodeRequestUrl->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ENCODE_REQUEST_URL);
            $this->encodeRequestUrl->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ENCODE_REQUEST_URL]);
            $xw->endElement();
        }
        if (isset($this->method)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_METHOD]
                || $this->method->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_METHOD);
            $this->method->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_METHOD]);
            $xw->endElement();
        }
        if (isset($this->origin)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ORIGIN]
                || $this->origin->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ORIGIN);
            $this->origin->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ORIGIN]);
            $xw->endElement();
        }
        if (isset($this->params)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PARAMS]
                || $this->params->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PARAMS);
            $this->params->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PARAMS]);
            $xw->endElement();
        }
        if (isset($this->requestHeader)) {
            foreach ($this->requestHeader as $v) {
                $xw->startElement(self::FIELD_REQUEST_HEADER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->requestId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REQUEST_ID]
                || $this->requestId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REQUEST_ID);
            $this->requestId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REQUEST_ID]);
            $xw->endElement();
        }
        if (isset($this->responseId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RESPONSE_ID]
                || $this->responseId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RESPONSE_ID);
            $this->responseId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RESPONSE_ID]);
            $xw->endElement();
        }
        if (isset($this->sourceId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SOURCE_ID]
                || $this->sourceId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SOURCE_ID);
            $this->sourceId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SOURCE_ID]);
            $xw->endElement();
        }
        if (isset($this->targetId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TARGET_ID]
                || $this->targetId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TARGET_ID);
            $this->targetId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TARGET_ID]);
            $xw->endElement();
        }
        if (isset($this->url)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_URL]
                || $this->url->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_URL);
            $this->url->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_URL]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptOperation
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
        } else if (!($type instanceof FHIRTestScriptOperation)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCoding::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCoding::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->resource)
            || isset($decoded->_resource)
            || property_exists($decoded, self::FIELD_RESOURCE)
            || property_exists($decoded, self::FIELD_RESOURCE_EXT)) {
            $v = $decoded->_resource ?? new \stdClass();
            $v->value = $decoded->resource ?? null;
            $type->setResource(FHIRCode::jsonUnserialize($v, $config));
        }
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
        if (isset($decoded->accept)
            || isset($decoded->_accept)
            || property_exists($decoded, self::FIELD_ACCEPT)
            || property_exists($decoded, self::FIELD_ACCEPT_EXT)) {
            $v = $decoded->_accept ?? new \stdClass();
            $v->value = $decoded->accept ?? null;
            $type->setAccept(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->contentType)
            || isset($decoded->_contentType)
            || property_exists($decoded, self::FIELD_CONTENT_TYPE)
            || property_exists($decoded, self::FIELD_CONTENT_TYPE_EXT)) {
            $v = $decoded->_contentType ?? new \stdClass();
            $v->value = $decoded->contentType ?? null;
            $type->setContentType(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->destination)
            || isset($decoded->_destination)
            || property_exists($decoded, self::FIELD_DESTINATION)
            || property_exists($decoded, self::FIELD_DESTINATION_EXT)) {
            $v = $decoded->_destination ?? new \stdClass();
            $v->value = $decoded->destination ?? null;
            $type->setDestination(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->encodeRequestUrl)
            || isset($decoded->_encodeRequestUrl)
            || property_exists($decoded, self::FIELD_ENCODE_REQUEST_URL)
            || property_exists($decoded, self::FIELD_ENCODE_REQUEST_URL_EXT)) {
            $v = $decoded->_encodeRequestUrl ?? new \stdClass();
            $v->value = $decoded->encodeRequestUrl ?? null;
            $type->setEncodeRequestUrl(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->method)
            || isset($decoded->_method)
            || property_exists($decoded, self::FIELD_METHOD)
            || property_exists($decoded, self::FIELD_METHOD_EXT)) {
            $v = $decoded->_method ?? new \stdClass();
            $v->value = $decoded->method ?? null;
            $type->setMethod(FHIRTestScriptRequestMethodCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->origin)
            || isset($decoded->_origin)
            || property_exists($decoded, self::FIELD_ORIGIN)
            || property_exists($decoded, self::FIELD_ORIGIN_EXT)) {
            $v = $decoded->_origin ?? new \stdClass();
            $v->value = $decoded->origin ?? null;
            $type->setOrigin(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->params)
            || isset($decoded->_params)
            || property_exists($decoded, self::FIELD_PARAMS)
            || property_exists($decoded, self::FIELD_PARAMS_EXT)) {
            $v = $decoded->_params ?? new \stdClass();
            $v->value = $decoded->params ?? null;
            $type->setParams(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->requestHeader) || property_exists($decoded, self::FIELD_REQUEST_HEADER)) {
            if (is_object($decoded->requestHeader)) {
                $vals = [$decoded->requestHeader];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REQUEST_HEADER, true);
            } else {
                $vals = $decoded->requestHeader;
            }
            foreach($vals as $v) {
                $type->addRequestHeader(FHIRTestScriptRequestHeader::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->requestId)
            || isset($decoded->_requestId)
            || property_exists($decoded, self::FIELD_REQUEST_ID)
            || property_exists($decoded, self::FIELD_REQUEST_ID_EXT)) {
            $v = $decoded->_requestId ?? new \stdClass();
            $v->value = $decoded->requestId ?? null;
            $type->setRequestId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->responseId)
            || isset($decoded->_responseId)
            || property_exists($decoded, self::FIELD_RESPONSE_ID)
            || property_exists($decoded, self::FIELD_RESPONSE_ID_EXT)) {
            $v = $decoded->_responseId ?? new \stdClass();
            $v->value = $decoded->responseId ?? null;
            $type->setResponseId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->sourceId)
            || isset($decoded->_sourceId)
            || property_exists($decoded, self::FIELD_SOURCE_ID)
            || property_exists($decoded, self::FIELD_SOURCE_ID_EXT)) {
            $v = $decoded->_sourceId ?? new \stdClass();
            $v->value = $decoded->sourceId ?? null;
            $type->setSourceId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->targetId)
            || isset($decoded->_targetId)
            || property_exists($decoded, self::FIELD_TARGET_ID)
            || property_exists($decoded, self::FIELD_TARGET_ID_EXT)) {
            $v = $decoded->_targetId ?? new \stdClass();
            $v->value = $decoded->targetId ?? null;
            $type->setTargetId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->url)
            || isset($decoded->_url)
            || property_exists($decoded, self::FIELD_URL)
            || property_exists($decoded, self::FIELD_URL_EXT)) {
            $v = $decoded->_url ?? new \stdClass();
            $v->value = $decoded->url ?? null;
            $type->setUrl(FHIRString::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->type)) {
            $out->type = $this->type;
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
        if (isset($this->accept)) {
            if (null !== ($val = $this->accept->getValue())) {
                $out->accept = $val;
            }
            if ($this->accept->_nonValueFieldDefined()) {
                $ext = $this->accept->jsonSerialize();
                unset($ext->value);
                $out->_accept = $ext;
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
        if (isset($this->destination)) {
            if (null !== ($val = $this->destination->getValue())) {
                $out->destination = $val;
            }
            if ($this->destination->_nonValueFieldDefined()) {
                $ext = $this->destination->jsonSerialize();
                unset($ext->value);
                $out->_destination = $ext;
            }
        }
        if (isset($this->encodeRequestUrl)) {
            if (null !== ($val = $this->encodeRequestUrl->getValue())) {
                $out->encodeRequestUrl = $val;
            }
            if ($this->encodeRequestUrl->_nonValueFieldDefined()) {
                $ext = $this->encodeRequestUrl->jsonSerialize();
                unset($ext->value);
                $out->_encodeRequestUrl = $ext;
            }
        }
        if (isset($this->method)) {
            if (null !== ($val = $this->method->getValue())) {
                $out->method = $val;
            }
            if ($this->method->_nonValueFieldDefined()) {
                $ext = $this->method->jsonSerialize();
                unset($ext->value);
                $out->_method = $ext;
            }
        }
        if (isset($this->origin)) {
            if (null !== ($val = $this->origin->getValue())) {
                $out->origin = $val;
            }
            if ($this->origin->_nonValueFieldDefined()) {
                $ext = $this->origin->jsonSerialize();
                unset($ext->value);
                $out->_origin = $ext;
            }
        }
        if (isset($this->params)) {
            if (null !== ($val = $this->params->getValue())) {
                $out->params = $val;
            }
            if ($this->params->_nonValueFieldDefined()) {
                $ext = $this->params->jsonSerialize();
                unset($ext->value);
                $out->_params = $ext;
            }
        }
        if (isset($this->requestHeader) && [] !== $this->requestHeader) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REQUEST_HEADER) && 1 === count($this->requestHeader)) {
                $out->requestHeader = $this->requestHeader[0];
            } else {
                $out->requestHeader = $this->requestHeader;
            }
        }
        if (isset($this->requestId)) {
            if (null !== ($val = $this->requestId->getValue())) {
                $out->requestId = $val;
            }
            if ($this->requestId->_nonValueFieldDefined()) {
                $ext = $this->requestId->jsonSerialize();
                unset($ext->value);
                $out->_requestId = $ext;
            }
        }
        if (isset($this->responseId)) {
            if (null !== ($val = $this->responseId->getValue())) {
                $out->responseId = $val;
            }
            if ($this->responseId->_nonValueFieldDefined()) {
                $ext = $this->responseId->jsonSerialize();
                unset($ext->value);
                $out->_responseId = $ext;
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
        if (isset($this->targetId)) {
            if (null !== ($val = $this->targetId->getValue())) {
                $out->targetId = $val;
            }
            if ($this->targetId->_nonValueFieldDefined()) {
                $ext = $this->targetId->jsonSerialize();
                unset($ext->value);
                $out->_targetId = $ext;
            }
        }
        if (isset($this->url)) {
            if (null !== ($val = $this->url->getValue())) {
                $out->url = $val;
            }
            if ($this->url->_nonValueFieldDefined()) {
                $ext = $this->url->jsonSerialize();
                unset($ext->value);
                $out->_url = $ext;
            }
        }
        return $out;
    }
}
