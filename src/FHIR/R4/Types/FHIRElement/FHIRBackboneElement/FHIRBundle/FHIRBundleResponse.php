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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive;
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
class FHIRBundleResponse extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_BUNDLE_DOT_RESPONSE;

    /* class_default.php:56 */
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_LOCATION = 'location';
    public const FIELD_LOCATION_EXT = '_location';
    public const FIELD_ETAG = 'etag';
    public const FIELD_ETAG_EXT = '_etag';
    public const FIELD_LAST_MODIFIED = 'lastModified';
    public const FIELD_LAST_MODIFIED_EXT = '_lastModified';
    public const FIELD_OUTCOME = 'outcome';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LOCATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ETAG => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LAST_MODIFIED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status code returned by processing this entry. The status SHALL start with a
     * 3 digit HTTP code (e.g. 404) and may contain the standard HTTP description
     * associated with the status code.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $status;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The location header created by processing this operation, populated if the
     * operation returns a location.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $location;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Etag for the resource, if the operation for the entry produced a versioned
     * resource (see [Resource Metadata and Versioning](http.html#versioning) and
     * [Managing Resource Contention](http.html#concurrency)).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $etag;
    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date/time that the resource was modified on the server.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    #[FHIRInstant]
    protected FHIRInstant $lastModified;
    /**
     * (choose any one of the elements, but only one)
     *
     * An OperationOutcome containing hints and warnings produced as part of processing
     * this entry in a batch or transaction.
     *
     * @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface
     */
    #[FHIRResourceContainer]
    protected VersionContainedTypeInterface $outcome;

    /* constructor.php:61 */
    /**
     * FHIRBundleResponse Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $status
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $location
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $etag
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $lastModified
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $outcome
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $status = null,
                                null|string|FHIRUriPrimitive|FHIRUri $location = null,
                                null|string|FHIRStringPrimitive|FHIRString $etag = null,
                                null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $lastModified = null,
                                null|FHIRResourceContainer|VersionContainedTypeInterface $outcome = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $location) {
            $this->setLocation($location);
        }
        if (null !== $etag) {
            $this->setEtag($etag);
        }
        if (null !== $lastModified) {
            $this->setLastModified($lastModified);
        }
        if (null !== $outcome) {
            $this->setOutcome($outcome);
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
     * The status code returned by processing this entry. The status SHALL start with a
     * 3 digit HTTP code (e.g. 404) and may contain the standard HTTP description
     * associated with the status code.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getStatus(): null|FHIRString
    {
        return $this->status ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status code returned by processing this entry. The status SHALL start with a
     * 3 digit HTTP code (e.g. 404) and may contain the standard HTTP description
     * associated with the status code.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $status
     * @return static
     */
    public function setStatus(null|string|FHIRStringPrimitive|FHIRString $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRString)) {
            $status = new FHIRString(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The location header created by processing this operation, populated if the
     * operation returns a location.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getLocation(): null|FHIRUri
    {
        return $this->location ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The location header created by processing this operation, populated if the
     * operation returns a location.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $location
     * @return static
     */
    public function setLocation(null|string|FHIRUriPrimitive|FHIRUri $location): self
    {
        if (null === $location) {
            unset($this->location);
            return $this;
        }
        if (!($location instanceof FHIRUri)) {
            $location = new FHIRUri(value: $location);
        }
        $this->location = $location;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Etag for the resource, if the operation for the entry produced a versioned
     * resource (see [Resource Metadata and Versioning](http.html#versioning) and
     * [Managing Resource Contention](http.html#concurrency)).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getEtag(): null|FHIRString
    {
        return $this->etag ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The Etag for the resource, if the operation for the entry produced a versioned
     * resource (see [Resource Metadata and Versioning](http.html#versioning) and
     * [Managing Resource Contention](http.html#concurrency)).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $etag
     * @return static
     */
    public function setEtag(null|string|FHIRStringPrimitive|FHIRString $etag): self
    {
        if (null === $etag) {
            unset($this->etag);
            return $this;
        }
        if (!($etag instanceof FHIRString)) {
            $etag = new FHIRString(value: $etag);
        }
        $this->etag = $etag;
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
     * The date/time that the resource was modified on the server.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    public function getLastModified(): null|FHIRInstant
    {
        return $this->lastModified ?? null;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date/time that the resource was modified on the server.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $lastModified
     * @return static
     */
    public function setLastModified(null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $lastModified): self
    {
        if (null === $lastModified) {
            unset($this->lastModified);
            return $this;
        }
        if (!($lastModified instanceof FHIRInstant)) {
            $lastModified = new FHIRInstant(value: $lastModified);
        }
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * (choose any one of the elements, but only one)
     *
     * An OperationOutcome containing hints and warnings produced as part of processing
     * this entry in a batch or transaction.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface
     */
    public function getOutcome(): null|VersionContainedTypeInterface
    {
        return $this->outcome ?? null;
    }

    /**
     * (choose any one of the elements, but only one)
     *
     * An OperationOutcome containing hints and warnings produced as part of processing
     * this entry in a batch or transaction.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $outcome
     * @return static
     */
    public function setOutcome(null|FHIRResourceContainer|VersionContainedTypeInterface $outcome): self
    {
        if (null === $outcome) {
            unset($this->outcome);
            return $this;
        }
        if ($outcome instanceof FHIRResourceContainer) {
            $outcome = $outcome->getContainedType();
        }
        $this->outcome = $outcome;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleResponse $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleResponse
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRBundleResponse)) {
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
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOCATION === $cen) {
                $type->setLocation(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ETAG === $cen) {
                $type->setEtag(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LAST_MODIFIED === $cen) {
                $type->setLastModified(FHIRInstant::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OUTCOME === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->setOutcome($cn::xmlUnserialize($cen, $config));
                }
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LOCATION])) {
            if (isset($type->location)) {
                $type->location->setValue((string)$attributes[self::FIELD_LOCATION]);
            } else {
                $type->setLocation((string)$attributes[self::FIELD_LOCATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LOCATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ETAG])) {
            if (isset($type->etag)) {
                $type->etag->setValue((string)$attributes[self::FIELD_ETAG]);
            } else {
                $type->setEtag((string)$attributes[self::FIELD_ETAG]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ETAG, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LAST_MODIFIED])) {
            if (isset($type->lastModified)) {
                $type->lastModified->setValue((string)$attributes[self::FIELD_LAST_MODIFIED]);
            } else {
                $type->setLastModified((string)$attributes[self::FIELD_LAST_MODIFIED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LAST_MODIFIED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->location) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LOCATION]) {
            $xw->writeAttribute(self::FIELD_LOCATION, $this->location->_getValueAsString());
        }
        if (isset($this->etag) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ETAG]) {
            $xw->writeAttribute(self::FIELD_ETAG, $this->etag->_getValueAsString());
        }
        if (isset($this->lastModified) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LAST_MODIFIED]) {
            $xw->writeAttribute(self::FIELD_LAST_MODIFIED, $this->lastModified->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->location)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LOCATION]
                || $this->location->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LOCATION);
            $this->location->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LOCATION]);
            $xw->endElement();
        }
        if (isset($this->etag)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ETAG]
                || $this->etag->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ETAG);
            $this->etag->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ETAG]);
            $xw->endElement();
        }
        if (isset($this->lastModified)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LAST_MODIFIED]
                || $this->lastModified->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LAST_MODIFIED);
            $this->lastModified->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LAST_MODIFIED]);
            $xw->endElement();
        }
        if (isset($this->outcome)) {
            $xw->startElement(self::FIELD_OUTCOME);
            $xw->startElement($this->outcome->_getFHIRTypeName());
            $this->outcome->xmlSerialize($xw, $config);
            $xw->endElement();
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleResponse $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRBundle\FHIRBundleResponse
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
        } else if (!($type instanceof FHIRBundleResponse)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->location)
            || isset($decoded->_location)
            || property_exists($decoded, self::FIELD_LOCATION)
            || property_exists($decoded, self::FIELD_LOCATION_EXT)) {
            $v = $decoded->_location ?? new \stdClass();
            $v->value = $decoded->location ?? null;
            $type->setLocation(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->etag)
            || isset($decoded->_etag)
            || property_exists($decoded, self::FIELD_ETAG)
            || property_exists($decoded, self::FIELD_ETAG_EXT)) {
            $v = $decoded->_etag ?? new \stdClass();
            $v->value = $decoded->etag ?? null;
            $type->setEtag(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->lastModified)
            || isset($decoded->_lastModified)
            || property_exists($decoded, self::FIELD_LAST_MODIFIED)
            || property_exists($decoded, self::FIELD_LAST_MODIFIED_EXT)) {
            $v = $decoded->_lastModified ?? new \stdClass();
            $v->value = $decoded->lastModified ?? null;
            $type->setLastModified(FHIRInstant::jsonUnserialize($v, $config));
        }
        if (isset($decoded->outcome)) {
            $typeClassName = VersionTypeMap::mustGetContainedTypeClassnameFromJSON($decoded->outcome);
            $v = $decoded->outcome;
            unset($v->resourceType);
            $type->setOutcome($typeClassName::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->status)) {
            if (null !== ($val = $this->status->getValue())) {
                $out->status = $val;
            }
            if ($this->status->_nonValueFieldDefined()) {
                $ext = $this->status->jsonSerialize();
                unset($ext->value);
                $out->_status = $ext;
            }
        }
        if (isset($this->location)) {
            if (null !== ($val = $this->location->getValue())) {
                $out->location = $val;
            }
            if ($this->location->_nonValueFieldDefined()) {
                $ext = $this->location->jsonSerialize();
                unset($ext->value);
                $out->_location = $ext;
            }
        }
        if (isset($this->etag)) {
            if (null !== ($val = $this->etag->getValue())) {
                $out->etag = $val;
            }
            if ($this->etag->_nonValueFieldDefined()) {
                $ext = $this->etag->jsonSerialize();
                unset($ext->value);
                $out->_etag = $ext;
            }
        }
        if (isset($this->lastModified)) {
            if (null !== ($val = $this->lastModified->getValue())) {
                $out->lastModified = $val;
            }
            if ($this->lastModified->_nonValueFieldDefined()) {
                $ext = $this->lastModified->jsonSerialize();
                unset($ext->value);
                $out->_lastModified = $ext;
            }
        }
        if (isset($this->outcome)) {
            $out->outcome = $this->outcome;
        }
        return $out;
    }
}
