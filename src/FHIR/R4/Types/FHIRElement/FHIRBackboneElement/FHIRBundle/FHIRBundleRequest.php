<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRHTTPVerbList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHTTPVerb;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A container for a collection of resources.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRBundleRequest extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_BUNDLE_DOT_REQUEST;

    /* class_default.php:56 */
    public const FIELD_METHOD = 'method';
    public const FIELD_METHOD_EXT = '_method';
    public const FIELD_URL = 'url';
    public const FIELD_URL_EXT = '_url';
    public const FIELD_IF_NONE_MATCH = 'ifNoneMatch';
    public const FIELD_IF_NONE_MATCH_EXT = '_ifNoneMatch';
    public const FIELD_IF_MODIFIED_SINCE = 'ifModifiedSince';
    public const FIELD_IF_MODIFIED_SINCE_EXT = '_ifModifiedSince';
    public const FIELD_IF_MATCH = 'ifMatch';
    public const FIELD_IF_MATCH_EXT = '_ifMatch';
    public const FIELD_IF_NONE_EXIST = 'ifNoneExist';
    public const FIELD_IF_NONE_EXIST_EXT = '_ifNoneExist';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_METHOD => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_URL => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_METHOD => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_IF_NONE_MATCH => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_IF_MODIFIED_SINCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_IF_MATCH => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_IF_NONE_EXIST => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * HTTP verbs (in the HTTP command line). See [HTTP
     * rfc](https://tools.ietf.org/html/rfc7231) for details.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * In a transaction or batch, this is the HTTP action to be executed for this
     * entry. In a history bundle, this indicates the HTTP action that occurred.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHTTPVerb
     */
    #[FHIRHTTPVerb]
    protected FHIRHTTPVerb $method;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL for this entry, relative to the root (the address to which the request
     * is posted).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $url;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the ETag values match, return a 304 Not Modified status. See the API
     * documentation for ["Conditional Read"](http.html#cread).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $ifNoneMatch;
    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Only perform the operation if the last updated date matches. See the API
     * documentation for ["Conditional Read"](http.html#cread).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    #[FHIRInstant]
    protected FHIRInstant $ifModifiedSince;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Only perform the operation if the Etag value matches. For more information, see
     * the API section ["Managing Resource Contention"](http.html#concurrency).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $ifMatch;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Instruct the server not to perform the create if a specified resource already
     * exists. For further information, see the API documentation for ["Conditional
     * Create"](http.html#ccreate). This is just the query portion of the URL - what
     * follows the "?" (not including the "?").
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $ifNoneExist;

    /* constructor.php:61 */
    /**
     * FHIRBundleRequest Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRHTTPVerbList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHTTPVerb $method
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $url
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $ifNoneMatch
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $ifModifiedSince
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $ifMatch
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $ifNoneExist
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRHTTPVerbList|FHIRHTTPVerb $method = null,
                                null|string|FHIRUriPrimitive|FHIRUri $url = null,
                                null|string|FHIRStringPrimitive|FHIRString $ifNoneMatch = null,
                                null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $ifModifiedSince = null,
                                null|string|FHIRStringPrimitive|FHIRString $ifMatch = null,
                                null|string|FHIRStringPrimitive|FHIRString $ifNoneExist = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $method) {
            $this->setMethod($method);
        }
        if (null !== $url) {
            $this->setUrl($url);
        }
        if (null !== $ifNoneMatch) {
            $this->setIfNoneMatch($ifNoneMatch);
        }
        if (null !== $ifModifiedSince) {
            $this->setIfModifiedSince($ifModifiedSince);
        }
        if (null !== $ifMatch) {
            $this->setIfMatch($ifMatch);
        }
        if (null !== $ifNoneExist) {
            $this->setIfNoneExist($ifNoneExist);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * HTTP verbs (in the HTTP command line). See [HTTP
     * rfc](https://tools.ietf.org/html/rfc7231) for details.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * In a transaction or batch, this is the HTTP action to be executed for this
     * entry. In a history bundle, this indicates the HTTP action that occurred.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHTTPVerb
     */
    public function getMethod(): null|FHIRHTTPVerb
    {
        return $this->method ?? null;
    }

    /**
     * HTTP verbs (in the HTTP command line). See [HTTP
     * rfc](https://tools.ietf.org/html/rfc7231) for details.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * In a transaction or batch, this is the HTTP action to be executed for this
     * entry. In a history bundle, this indicates the HTTP action that occurred.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRHTTPVerbList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRHTTPVerb $method
     * @return static
     */
    public function setMethod(null|string|FHIRHTTPVerbList|FHIRHTTPVerb $method): self
    {
        if (null === $method) {
            unset($this->method);
            return $this;
        }
        if (!($method instanceof FHIRHTTPVerb)) {
            $method = new FHIRHTTPVerb(value: $method);
        }
        $this->method = $method;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL for this entry, relative to the root (the address to which the request
     * is posted).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getUrl(): null|FHIRUri
    {
        return $this->url ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL for this entry, relative to the root (the address to which the request
     * is posted).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $url
     * @return static
     */
    public function setUrl(null|string|FHIRUriPrimitive|FHIRUri $url): self
    {
        if (null === $url) {
            unset($this->url);
            return $this;
        }
        if (!($url instanceof FHIRUri)) {
            $url = new FHIRUri(value: $url);
        }
        $this->url = $url;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the ETag values match, return a 304 Not Modified status. See the API
     * documentation for ["Conditional Read"](http.html#cread).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getIfNoneMatch(): null|FHIRString
    {
        return $this->ifNoneMatch ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the ETag values match, return a 304 Not Modified status. See the API
     * documentation for ["Conditional Read"](http.html#cread).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $ifNoneMatch
     * @return static
     */
    public function setIfNoneMatch(null|string|FHIRStringPrimitive|FHIRString $ifNoneMatch): self
    {
        if (null === $ifNoneMatch) {
            unset($this->ifNoneMatch);
            return $this;
        }
        if (!($ifNoneMatch instanceof FHIRString)) {
            $ifNoneMatch = new FHIRString(value: $ifNoneMatch);
        }
        $this->ifNoneMatch = $ifNoneMatch;
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
     * Only perform the operation if the last updated date matches. See the API
     * documentation for ["Conditional Read"](http.html#cread).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    public function getIfModifiedSince(): null|FHIRInstant
    {
        return $this->ifModifiedSince ?? null;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Only perform the operation if the last updated date matches. See the API
     * documentation for ["Conditional Read"](http.html#cread).
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $ifModifiedSince
     * @return static
     */
    public function setIfModifiedSince(null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $ifModifiedSince): self
    {
        if (null === $ifModifiedSince) {
            unset($this->ifModifiedSince);
            return $this;
        }
        if (!($ifModifiedSince instanceof FHIRInstant)) {
            $ifModifiedSince = new FHIRInstant(value: $ifModifiedSince);
        }
        $this->ifModifiedSince = $ifModifiedSince;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Only perform the operation if the Etag value matches. For more information, see
     * the API section ["Managing Resource Contention"](http.html#concurrency).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getIfMatch(): null|FHIRString
    {
        return $this->ifMatch ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Only perform the operation if the Etag value matches. For more information, see
     * the API section ["Managing Resource Contention"](http.html#concurrency).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $ifMatch
     * @return static
     */
    public function setIfMatch(null|string|FHIRStringPrimitive|FHIRString $ifMatch): self
    {
        if (null === $ifMatch) {
            unset($this->ifMatch);
            return $this;
        }
        if (!($ifMatch instanceof FHIRString)) {
            $ifMatch = new FHIRString(value: $ifMatch);
        }
        $this->ifMatch = $ifMatch;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Instruct the server not to perform the create if a specified resource already
     * exists. For further information, see the API documentation for ["Conditional
     * Create"](http.html#ccreate). This is just the query portion of the URL - what
     * follows the "?" (not including the "?").
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getIfNoneExist(): null|FHIRString
    {
        return $this->ifNoneExist ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Instruct the server not to perform the create if a specified resource already
     * exists. For further information, see the API documentation for ["Conditional
     * Create"](http.html#ccreate). This is just the query portion of the URL - what
     * follows the "?" (not including the "?").
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $ifNoneExist
     * @return static
     */
    public function setIfNoneExist(null|string|FHIRStringPrimitive|FHIRString $ifNoneExist): self
    {
        if (null === $ifNoneExist) {
            unset($this->ifNoneExist);
            return $this;
        }
        if (!($ifNoneExist instanceof FHIRString)) {
            $ifNoneExist = new FHIRString(value: $ifNoneExist);
        }
        $this->ifNoneExist = $ifNoneExist;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleRequest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleRequest
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRBundleRequest)) {
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
            } else if (self::FIELD_METHOD === $cen) {
                $type->setMethod(FHIRHTTPVerb::xmlUnserialize($ce, $config));
            } else if (self::FIELD_URL === $cen) {
                $type->setUrl(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IF_NONE_MATCH === $cen) {
                $type->setIfNoneMatch(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IF_MODIFIED_SINCE === $cen) {
                $type->setIfModifiedSince(FHIRInstant::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IF_MATCH === $cen) {
                $type->setIfMatch(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IF_NONE_EXIST === $cen) {
                $type->setIfNoneExist(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_METHOD])) {
            if (isset($type->method)) {
                $type->method->setValue((string)$attributes[self::FIELD_METHOD]);
            } else {
                $type->setMethod((string)$attributes[self::FIELD_METHOD]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_METHOD, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_URL])) {
            if (isset($type->url)) {
                $type->url->setValue((string)$attributes[self::FIELD_URL]);
            } else {
                $type->setUrl((string)$attributes[self::FIELD_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IF_NONE_MATCH])) {
            if (isset($type->ifNoneMatch)) {
                $type->ifNoneMatch->setValue((string)$attributes[self::FIELD_IF_NONE_MATCH]);
            } else {
                $type->setIfNoneMatch((string)$attributes[self::FIELD_IF_NONE_MATCH]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IF_NONE_MATCH, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IF_MODIFIED_SINCE])) {
            if (isset($type->ifModifiedSince)) {
                $type->ifModifiedSince->setValue((string)$attributes[self::FIELD_IF_MODIFIED_SINCE]);
            } else {
                $type->setIfModifiedSince((string)$attributes[self::FIELD_IF_MODIFIED_SINCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IF_MODIFIED_SINCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IF_MATCH])) {
            if (isset($type->ifMatch)) {
                $type->ifMatch->setValue((string)$attributes[self::FIELD_IF_MATCH]);
            } else {
                $type->setIfMatch((string)$attributes[self::FIELD_IF_MATCH]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IF_MATCH, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IF_NONE_EXIST])) {
            if (isset($type->ifNoneExist)) {
                $type->ifNoneExist->setValue((string)$attributes[self::FIELD_IF_NONE_EXIST]);
            } else {
                $type->setIfNoneExist((string)$attributes[self::FIELD_IF_NONE_EXIST]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IF_NONE_EXIST, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->method) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_METHOD]) {
            $xw->writeAttribute(self::FIELD_METHOD, $this->method->_getValueAsString());
        }
        if (isset($this->url) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_URL]) {
            $xw->writeAttribute(self::FIELD_URL, $this->url->_getValueAsString());
        }
        if (isset($this->ifNoneMatch) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_IF_NONE_MATCH]) {
            $xw->writeAttribute(self::FIELD_IF_NONE_MATCH, $this->ifNoneMatch->_getValueAsString());
        }
        if (isset($this->ifModifiedSince) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_IF_MODIFIED_SINCE]) {
            $xw->writeAttribute(self::FIELD_IF_MODIFIED_SINCE, $this->ifModifiedSince->_getValueAsString());
        }
        if (isset($this->ifMatch) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_IF_MATCH]) {
            $xw->writeAttribute(self::FIELD_IF_MATCH, $this->ifMatch->_getValueAsString());
        }
        if (isset($this->ifNoneExist) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_IF_NONE_EXIST]) {
            $xw->writeAttribute(self::FIELD_IF_NONE_EXIST, $this->ifNoneExist->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->method)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_METHOD]
                || $this->method->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_METHOD);
            $this->method->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_METHOD]);
            $xw->endElement();
        }
        if (isset($this->url)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_URL]
                || $this->url->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_URL);
            $this->url->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_URL]);
            $xw->endElement();
        }
        if (isset($this->ifNoneMatch)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_IF_NONE_MATCH]
                || $this->ifNoneMatch->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_IF_NONE_MATCH);
            $this->ifNoneMatch->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_IF_NONE_MATCH]);
            $xw->endElement();
        }
        if (isset($this->ifModifiedSince)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_IF_MODIFIED_SINCE]
                || $this->ifModifiedSince->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_IF_MODIFIED_SINCE);
            $this->ifModifiedSince->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_IF_MODIFIED_SINCE]);
            $xw->endElement();
        }
        if (isset($this->ifMatch)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_IF_MATCH]
                || $this->ifMatch->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_IF_MATCH);
            $this->ifMatch->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_IF_MATCH]);
            $xw->endElement();
        }
        if (isset($this->ifNoneExist)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_IF_NONE_EXIST]
                || $this->ifNoneExist->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_IF_NONE_EXIST);
            $this->ifNoneExist->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_IF_NONE_EXIST]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleRequest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleRequest
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
        } else if (!($type instanceof FHIRBundleRequest)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->method)
            || isset($decoded->_method)
            || property_exists($decoded, self::FIELD_METHOD)
            || property_exists($decoded, self::FIELD_METHOD_EXT)) {
            $v = $decoded->_method ?? new \stdClass();
            $v->value = $decoded->method ?? null;
            $type->setMethod(FHIRHTTPVerb::jsonUnserialize($v, $config));
        }
        if (isset($decoded->url)
            || isset($decoded->_url)
            || property_exists($decoded, self::FIELD_URL)
            || property_exists($decoded, self::FIELD_URL_EXT)) {
            $v = $decoded->_url ?? new \stdClass();
            $v->value = $decoded->url ?? null;
            $type->setUrl(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->ifNoneMatch)
            || isset($decoded->_ifNoneMatch)
            || property_exists($decoded, self::FIELD_IF_NONE_MATCH)
            || property_exists($decoded, self::FIELD_IF_NONE_MATCH_EXT)) {
            $v = $decoded->_ifNoneMatch ?? new \stdClass();
            $v->value = $decoded->ifNoneMatch ?? null;
            $type->setIfNoneMatch(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->ifModifiedSince)
            || isset($decoded->_ifModifiedSince)
            || property_exists($decoded, self::FIELD_IF_MODIFIED_SINCE)
            || property_exists($decoded, self::FIELD_IF_MODIFIED_SINCE_EXT)) {
            $v = $decoded->_ifModifiedSince ?? new \stdClass();
            $v->value = $decoded->ifModifiedSince ?? null;
            $type->setIfModifiedSince(FHIRInstant::jsonUnserialize($v, $config));
        }
        if (isset($decoded->ifMatch)
            || isset($decoded->_ifMatch)
            || property_exists($decoded, self::FIELD_IF_MATCH)
            || property_exists($decoded, self::FIELD_IF_MATCH_EXT)) {
            $v = $decoded->_ifMatch ?? new \stdClass();
            $v->value = $decoded->ifMatch ?? null;
            $type->setIfMatch(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->ifNoneExist)
            || isset($decoded->_ifNoneExist)
            || property_exists($decoded, self::FIELD_IF_NONE_EXIST)
            || property_exists($decoded, self::FIELD_IF_NONE_EXIST_EXT)) {
            $v = $decoded->_ifNoneExist ?? new \stdClass();
            $v->value = $decoded->ifNoneExist ?? null;
            $type->setIfNoneExist(FHIRString::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
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
        if (isset($this->ifNoneMatch)) {
            if (null !== ($val = $this->ifNoneMatch->getValue())) {
                $out->ifNoneMatch = $val;
            }
            if ($this->ifNoneMatch->_nonValueFieldDefined()) {
                $ext = $this->ifNoneMatch->jsonSerialize();
                unset($ext->value);
                $out->_ifNoneMatch = $ext;
            }
        }
        if (isset($this->ifModifiedSince)) {
            if (null !== ($val = $this->ifModifiedSince->getValue())) {
                $out->ifModifiedSince = $val;
            }
            if ($this->ifModifiedSince->_nonValueFieldDefined()) {
                $ext = $this->ifModifiedSince->jsonSerialize();
                unset($ext->value);
                $out->_ifModifiedSince = $ext;
            }
        }
        if (isset($this->ifMatch)) {
            if (null !== ($val = $this->ifMatch->getValue())) {
                $out->ifMatch = $val;
            }
            if ($this->ifMatch->_nonValueFieldDefined()) {
                $ext = $this->ifMatch->jsonSerialize();
                unset($ext->value);
                $out->_ifMatch = $ext;
            }
        }
        if (isset($this->ifNoneExist)) {
            if (null !== ($val = $this->ifNoneExist->getValue())) {
                $out->ifNoneExist = $val;
            }
            if ($this->ifNoneExist->_nonValueFieldDefined()) {
                $ext = $this->ifNoneExist->jsonSerialize();
                unset($ext->value);
                $out->_ifNoneExist = $ext;
            }
        }
        return $out;
    }
}
