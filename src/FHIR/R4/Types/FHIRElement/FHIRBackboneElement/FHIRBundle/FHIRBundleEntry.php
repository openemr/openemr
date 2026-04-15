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
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * A container for a collection of resources.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRBundleEntry extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_BUNDLE_DOT_ENTRY;

    /* class_default.php:56 */
    public const FIELD_LINK = 'link';
    public const FIELD_FULL_URL = 'fullUrl';
    public const FIELD_FULL_URL_EXT = '_fullUrl';
    public const FIELD_RESOURCE = 'resource';
    public const FIELD_SEARCH = 'search';
    public const FIELD_REQUEST = 'request';
    public const FIELD_RESPONSE = 'response';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_FULL_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A container for a collection of resources.
     *
     * A series of links that provide context to this entry.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink>
     */
    #[FHIRBundleLink]
    protected array $link;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Absolute URL for the resource. The fullUrl SHALL NOT disagree with the id in
     * the resource - i.e. if the fullUrl is not a urn:uuid, the URL shall be
     * version-independent URL consistent with the Resource.id. The fullUrl is a
     * version independent reference to the resource. The fullUrl element SHALL have a
     * value except that: * fullUrl can be empty on a POST (although it does not need
     * to when specifying a temporary id for reference in the bundle) * Results from
     * operations might involve resources that are not identified.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $fullUrl;
    /**
     * (choose any one of the elements, but only one)
     *
     * The Resource for the entry. The purpose/meaning of the resource is determined by
     * the Bundle.type.
     *
     * @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface
     */
    #[FHIRResourceContainer]
    protected VersionContainedTypeInterface $resource;
    /**
     * A container for a collection of resources.
     *
     * Information about the search process that lead to the creation of this entry.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleSearch
     */
    #[FHIRBundleSearch]
    protected FHIRBundleSearch $search;
    /**
     * A container for a collection of resources.
     *
     * Additional information about how this entry should be processed as part of a
     * transaction or batch. For history, it shows how the entry was processed to
     * create the version contained in the entry.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleRequest
     */
    #[FHIRBundleRequest]
    protected FHIRBundleRequest $request;
    /**
     * A container for a collection of resources.
     *
     * Indicates the results of processing the corresponding 'request' entry in the
     * batch or transaction being responded to or what the results of an operation
     * where when returning history.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleResponse
     */
    #[FHIRBundleResponse]
    protected FHIRBundleResponse $response;

    /* constructor.php:61 */
    /**
     * FHIRBundleEntry Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink> $link
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $fullUrl
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $resource
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleSearch $search
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleRequest $request
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleResponse $response
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $link = null,
                                null|string|FHIRUriPrimitive|FHIRUri $fullUrl = null,
                                null|FHIRResourceContainer|VersionContainedTypeInterface $resource = null,
                                null|FHIRBundleSearch $search = null,
                                null|FHIRBundleRequest $request = null,
                                null|FHIRBundleResponse $response = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $link) {
            $this->setLink(...$link);
        }
        if (null !== $fullUrl) {
            $this->setFullUrl($fullUrl);
        }
        if (null !== $resource) {
            $this->setResource($resource);
        }
        if (null !== $search) {
            $this->setSearch($search);
        }
        if (null !== $request) {
            $this->setRequest($request);
        }
        if (null !== $response) {
            $this->setResponse($response);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A container for a collection of resources.
     *
     * A series of links that provide context to this entry.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink>
     */
    public function getLink(): array
    {
        return $this->link ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink>
     */
    public function getLinkIterator(): iterable
    {
        if (!isset($this->link)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->link);
    }

    /**
     * A container for a collection of resources.
     *
     * A series of links that provide context to this entry.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink $link
     * @return static
     */
    public function addLink(FHIRBundleLink $link): self
    {
        if (!isset($this->link)) {
            $this->link = [];
        }
        $this->link[] = $link;
        return $this;
    }

    /**
     * A container for a collection of resources.
     *
     * A series of links that provide context to this entry.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleLink ...$link
     * @return static
     */
    public function setLink(FHIRBundleLink ...$link): self
    {
        if ([] === $link) {
            unset($this->link);
            return $this;
        }
        $this->link = $link;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Absolute URL for the resource. The fullUrl SHALL NOT disagree with the id in
     * the resource - i.e. if the fullUrl is not a urn:uuid, the URL shall be
     * version-independent URL consistent with the Resource.id. The fullUrl is a
     * version independent reference to the resource. The fullUrl element SHALL have a
     * value except that: * fullUrl can be empty on a POST (although it does not need
     * to when specifying a temporary id for reference in the bundle) * Results from
     * operations might involve resources that are not identified.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getFullUrl(): null|FHIRUri
    {
        return $this->fullUrl ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Absolute URL for the resource. The fullUrl SHALL NOT disagree with the id in
     * the resource - i.e. if the fullUrl is not a urn:uuid, the URL shall be
     * version-independent URL consistent with the Resource.id. The fullUrl is a
     * version independent reference to the resource. The fullUrl element SHALL have a
     * value except that: * fullUrl can be empty on a POST (although it does not need
     * to when specifying a temporary id for reference in the bundle) * Results from
     * operations might involve resources that are not identified.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $fullUrl
     * @return static
     */
    public function setFullUrl(null|string|FHIRUriPrimitive|FHIRUri $fullUrl): self
    {
        if (null === $fullUrl) {
            unset($this->fullUrl);
            return $this;
        }
        if (!($fullUrl instanceof FHIRUri)) {
            $fullUrl = new FHIRUri(value: $fullUrl);
        }
        $this->fullUrl = $fullUrl;
        return $this;
    }

    /**
     * (choose any one of the elements, but only one)
     *
     * The Resource for the entry. The purpose/meaning of the resource is determined by
     * the Bundle.type.
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
     * The Resource for the entry. The purpose/meaning of the resource is determined by
     * the Bundle.type.
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
     * A container for a collection of resources.
     *
     * Information about the search process that lead to the creation of this entry.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleSearch
     */
    public function getSearch(): null|FHIRBundleSearch
    {
        return $this->search ?? null;
    }

    /**
     * A container for a collection of resources.
     *
     * Information about the search process that lead to the creation of this entry.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleSearch $search
     * @return static
     */
    public function setSearch(null|FHIRBundleSearch $search): self
    {
        if (null === $search) {
            unset($this->search);
            return $this;
        }
        $this->search = $search;
        return $this;
    }

    /**
     * A container for a collection of resources.
     *
     * Additional information about how this entry should be processed as part of a
     * transaction or batch. For history, it shows how the entry was processed to
     * create the version contained in the entry.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleRequest
     */
    public function getRequest(): null|FHIRBundleRequest
    {
        return $this->request ?? null;
    }

    /**
     * A container for a collection of resources.
     *
     * Additional information about how this entry should be processed as part of a
     * transaction or batch. For history, it shows how the entry was processed to
     * create the version contained in the entry.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleRequest $request
     * @return static
     */
    public function setRequest(null|FHIRBundleRequest $request): self
    {
        if (null === $request) {
            unset($this->request);
            return $this;
        }
        $this->request = $request;
        return $this;
    }

    /**
     * A container for a collection of resources.
     *
     * Indicates the results of processing the corresponding 'request' entry in the
     * batch or transaction being responded to or what the results of an operation
     * where when returning history.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleResponse
     */
    public function getResponse(): null|FHIRBundleResponse
    {
        return $this->response ?? null;
    }

    /**
     * A container for a collection of resources.
     *
     * Indicates the results of processing the corresponding 'request' entry in the
     * batch or transaction being responded to or what the results of an operation
     * where when returning history.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleResponse $response
     * @return static
     */
    public function setResponse(null|FHIRBundleResponse $response): self
    {
        if (null === $response) {
            unset($this->response);
            return $this;
        }
        $this->response = $response;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRBundleEntry)) {
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
            } else if (self::FIELD_LINK === $cen) {
                $type->addLink(FHIRBundleLink::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FULL_URL === $cen) {
                $type->setFullUrl(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESOURCE === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->setResource($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_SEARCH === $cen) {
                $type->setSearch(FHIRBundleSearch::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST === $cen) {
                $type->setRequest(FHIRBundleRequest::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESPONSE === $cen) {
                $type->setResponse(FHIRBundleResponse::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_FULL_URL])) {
            if (isset($type->fullUrl)) {
                $type->fullUrl->setValue((string)$attributes[self::FIELD_FULL_URL]);
            } else {
                $type->setFullUrl((string)$attributes[self::FIELD_FULL_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_FULL_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->fullUrl) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_FULL_URL]) {
            $xw->writeAttribute(self::FIELD_FULL_URL, $this->fullUrl->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->link)) {
            foreach ($this->link as $v) {
                $xw->startElement(self::FIELD_LINK);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->fullUrl)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_FULL_URL]
                || $this->fullUrl->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_FULL_URL);
            $this->fullUrl->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_FULL_URL]);
            $xw->endElement();
        }
        if (isset($this->resource)) {
            $xw->startElement(self::FIELD_RESOURCE);
            $xw->startElement($this->resource->_getFHIRTypeName());
            $this->resource->xmlSerialize($xw, $config);
            $xw->endElement();
            $xw->endElement();
        }
        if (isset($this->search)) {
            $xw->startElement(self::FIELD_SEARCH);
            $this->search->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->request)) {
            $xw->startElement(self::FIELD_REQUEST);
            $this->request->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->response)) {
            $xw->startElement(self::FIELD_RESPONSE);
            $this->response->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleEntry
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
        } else if (!($type instanceof FHIRBundleEntry)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->link) || property_exists($decoded, self::FIELD_LINK)) {
            if (is_object($decoded->link)) {
                $vals = [$decoded->link];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_LINK, true);
            } else {
                $vals = $decoded->link;
            }
            foreach($vals as $v) {
                $type->addLink(FHIRBundleLink::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->fullUrl)
            || isset($decoded->_fullUrl)
            || property_exists($decoded, self::FIELD_FULL_URL)
            || property_exists($decoded, self::FIELD_FULL_URL_EXT)) {
            $v = $decoded->_fullUrl ?? new \stdClass();
            $v->value = $decoded->fullUrl ?? null;
            $type->setFullUrl(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->resource)) {
            $typeClassName = VersionTypeMap::mustGetContainedTypeClassnameFromJSON($decoded->resource);
            $v = $decoded->resource;
            unset($v->resourceType);
            $type->setResource($typeClassName::jsonUnserialize($v, $config));
        }
        if (isset($decoded->search) || property_exists($decoded, self::FIELD_SEARCH)) {
            if (is_array($decoded->search)) {
                $type->setSearch(FHIRBundleSearch::jsonUnserialize(reset($decoded->search), $config));
            } else {
                $type->setSearch(FHIRBundleSearch::jsonUnserialize($decoded->search, $config));
            }
        }
        if (isset($decoded->request) || property_exists($decoded, self::FIELD_REQUEST)) {
            if (is_array($decoded->request)) {
                $type->setRequest(FHIRBundleRequest::jsonUnserialize(reset($decoded->request), $config));
            } else {
                $type->setRequest(FHIRBundleRequest::jsonUnserialize($decoded->request, $config));
            }
        }
        if (isset($decoded->response) || property_exists($decoded, self::FIELD_RESPONSE)) {
            if (is_array($decoded->response)) {
                $type->setResponse(FHIRBundleResponse::jsonUnserialize(reset($decoded->response), $config));
            } else {
                $type->setResponse(FHIRBundleResponse::jsonUnserialize($decoded->response, $config));
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
        if (isset($this->link) && [] !== $this->link) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_LINK) && 1 === count($this->link)) {
                $out->link = $this->link[0];
            } else {
                $out->link = $this->link;
            }
        }
        if (isset($this->fullUrl)) {
            if (null !== ($val = $this->fullUrl->getValue())) {
                $out->fullUrl = $val;
            }
            if ($this->fullUrl->_nonValueFieldDefined()) {
                $ext = $this->fullUrl->jsonSerialize();
                unset($ext->value);
                $out->_fullUrl = $ext;
            }
        }
        if (isset($this->resource)) {
            $out->resource = $this->resource;
        }
        if (isset($this->search)) {
            $out->search = $this->search;
        }
        if (isset($this->request)) {
            $out->request = $this->request;
        }
        if (isset($this->response)) {
            $out->response = $this->response;
        }
        return $out;
    }
}
