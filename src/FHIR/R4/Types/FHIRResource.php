<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types;

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
use OpenEMR\FHIR\Constants;
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\ValueXMLLocationEnum;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLWriter;
use OpenEMR\FHIR\FHIRVersion;
use OpenEMR\FHIR\Types\CommentContainerInterface;
use OpenEMR\FHIR\Types\CommentContainerTrait;
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Types\SourceXMLNamespaceTrait;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionResourceTypeInterface;

/**
 * This is the base resource type for everything.
 */
class FHIRResource implements VersionResourceTypeInterface, CommentContainerInterface, \Stringable
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait,
        CommentContainerTrait,
        SourceXMLNamespaceTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_RESOURCE;

    /* class_default.php:56 */
    public const FIELD_ID = 'id';
    public const FIELD_ID_EXT = '_id';
    public const FIELD_META = 'meta';
    public const FIELD_IMPLICIT_RULES = 'implicitRules';
    public const FIELD_IMPLICIT_RULES_EXT = '_implicitRules';
    public const FIELD_LANGUAGE = 'language';
    public const FIELD_LANGUAGE_EXT = '_language';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_IMPLICIT_RULES => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LANGUAGE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The logical id of the resource, as used in the URL for the resource. Once
     * assigned, this value never changes.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $id;
    /**
     * The metadata about a resource. This is content in the resource that is
     * maintained by the infrastructure. Changes to the content might not always be
     * associated with version changes to the resource.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The metadata about the resource. This is content that is maintained by the
     * infrastructure. Changes to the content might not always be associated with
     * version changes to the resource.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta
     */
    #[FHIRMeta]
    protected FHIRMeta $meta;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A reference to a set of rules that were followed when the resource was
     * constructed, and which must be understood when processing the content. Often,
     * this is a reference to an implementation guide that defines the special rules
     * along with other profiles etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $implicitRules;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The base language in which the resource is written.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $language;

    /* constructor.php:61 */
    /**
     * FHIRResource Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|iterable $fhirComments = null)
    {
        if (null !== $fhirComments && [] !== $fhirComments) {
            $this->_setFHIRComments($fhirComments);
        }
        if (null !== $id) {
            $this->setId($id);
        }
        if (null !== $meta) {
            $this->setMeta($meta);
        }
        if (null !== $implicitRules) {
            $this->setImplicitRules($implicitRules);
        }
        if (null !== $language) {
            $this->setLanguage($language);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:154 */
    public function _getFHIRVersion(): FHIRVersion
    {
        return Version::getFHIRVersion();
    }

    /* class_default.php:174 */
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The logical id of the resource, as used in the URL for the resource. Once
     * assigned, this value never changes.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getId(): null|FHIRId
    {
        return $this->id ?? null;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The logical id of the resource, as used in the URL for the resource. Once
     * assigned, this value never changes.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @return static
     */
    public function setId(null|string|FHIRIdPrimitive|FHIRId $id): self
    {
        if (null === $id) {
            unset($this->id);
            return $this;
        }
        if (!($id instanceof FHIRId)) {
            $id = new FHIRId(value: $id);
        }
        $this->id = $id;
        return $this;
    }

    /**
     * The metadata about a resource. This is content in the resource that is
     * maintained by the infrastructure. Changes to the content might not always be
     * associated with version changes to the resource.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The metadata about the resource. This is content that is maintained by the
     * infrastructure. Changes to the content might not always be associated with
     * version changes to the resource.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta
     */
    public function getMeta(): null|FHIRMeta
    {
        return $this->meta ?? null;
    }

    /**
     * The metadata about a resource. This is content in the resource that is
     * maintained by the infrastructure. Changes to the content might not always be
     * associated with version changes to the resource.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The metadata about the resource. This is content that is maintained by the
     * infrastructure. Changes to the content might not always be associated with
     * version changes to the resource.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @return static
     */
    public function setMeta(null|FHIRMeta $meta): self
    {
        if (null === $meta) {
            unset($this->meta);
            return $this;
        }
        $this->meta = $meta;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A reference to a set of rules that were followed when the resource was
     * constructed, and which must be understood when processing the content. Often,
     * this is a reference to an implementation guide that defines the special rules
     * along with other profiles etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getImplicitRules(): null|FHIRUri
    {
        return $this->implicitRules ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A reference to a set of rules that were followed when the resource was
     * constructed, and which must be understood when processing the content. Often,
     * this is a reference to an implementation guide that defines the special rules
     * along with other profiles etc.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @return static
     */
    public function setImplicitRules(null|string|FHIRUriPrimitive|FHIRUri $implicitRules): self
    {
        if (null === $implicitRules) {
            unset($this->implicitRules);
            return $this;
        }
        if (!($implicitRules instanceof FHIRUri)) {
            $implicitRules = new FHIRUri(value: $implicitRules);
        }
        $this->implicitRules = $implicitRules;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The base language in which the resource is written.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getLanguage(): null|FHIRCode
    {
        return $this->language ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The base language in which the resource is written.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @return static
     */
    public function setLanguage(null|string|FHIRCodePrimitive|FHIRCode $language): self
    {
        if (null === $language) {
            unset($this->language);
            return $this;
        }
        if (!($language instanceof FHIRCode)) {
            $language = new FHIRCode(value: $language);
        }
        $this->language = $language;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRResource)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('Resource', $this->_getSourceXMLNS());
        }
        if (isset($this->id) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ID]) {
            $xw->writeAttribute(self::FIELD_ID, $this->id->_getValueAsString());
        }
        if (isset($this->implicitRules) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_IMPLICIT_RULES]) {
            $xw->writeAttribute(self::FIELD_IMPLICIT_RULES, $this->implicitRules->_getValueAsString());
        }
        if (isset($this->language) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LANGUAGE]) {
            $xw->writeAttribute(self::FIELD_LANGUAGE, $this->language->_getValueAsString());
        }
        if (isset($this->id)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ID]
                || $this->id->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ID);
            $this->id->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ID]);
            $xw->endElement();
        }
        if (isset($this->meta)) {
            $xw->startElement(self::FIELD_META);
            $this->meta->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->implicitRules)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_IMPLICIT_RULES]
                || $this->implicitRules->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_IMPLICIT_RULES);
            $this->implicitRules->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_IMPLICIT_RULES]);
            $xw->endElement();
        }
        if (isset($this->language)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LANGUAGE]
                || $this->language->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LANGUAGE);
            $this->language->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LANGUAGE]);
            $xw->endElement();
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIRResource)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        if (isset($decoded->{Constants::JSON_FIELD_FHIR_COMMENTS})) {
            $type->_setFHIRComments((array)$decoded->{Constants::JSON_FIELD_FHIR_COMMENTS});
        }
        if (isset($decoded->id)
            || isset($decoded->_id)
            || property_exists($decoded, self::FIELD_ID)
            || property_exists($decoded, self::FIELD_ID_EXT)) {
            $v = $decoded->_id ?? new \stdClass();
            $v->value = $decoded->id ?? null;
            $type->setId(FHIRId::jsonUnserialize($v, $config));
        }
        if (isset($decoded->meta) || property_exists($decoded, self::FIELD_META)) {
            if (is_array($decoded->meta)) {
                $type->setMeta(FHIRMeta::jsonUnserialize(reset($decoded->meta), $config));
            } else {
                $type->setMeta(FHIRMeta::jsonUnserialize($decoded->meta, $config));
            }
        }
        if (isset($decoded->implicitRules)
            || isset($decoded->_implicitRules)
            || property_exists($decoded, self::FIELD_IMPLICIT_RULES)
            || property_exists($decoded, self::FIELD_IMPLICIT_RULES_EXT)) {
            $v = $decoded->_implicitRules ?? new \stdClass();
            $v->value = $decoded->implicitRules ?? null;
            $type->setImplicitRules(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->language)
            || isset($decoded->_language)
            || property_exists($decoded, self::FIELD_LANGUAGE)
            || property_exists($decoded, self::FIELD_LANGUAGE_EXT)) {
            $v = $decoded->_language ?? new \stdClass();
            $v->value = $decoded->language ?? null;
            $type->setLanguage(FHIRCode::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = new \stdClass();
        if ([] !== ($vs = $this->_getFHIRComments())) {
            $out->fhir_comments = $vs;
        }
        if (isset($this->id)) {
            if (null !== ($val = $this->id->getValue())) {
                $out->id = $val;
            }
            if ($this->id->_nonValueFieldDefined()) {
                $ext = $this->id->jsonSerialize();
                unset($ext->value);
                $out->_id = $ext;
            }
        }
        if (isset($this->meta)) {
            $out->meta = $this->meta;
        }
        if (isset($this->implicitRules)) {
            if (null !== ($val = $this->implicitRules->getValue())) {
                $out->implicitRules = $val;
            }
            if ($this->implicitRules->_nonValueFieldDefined()) {
                $ext = $this->implicitRules->jsonSerialize();
                unset($ext->value);
                $out->_implicitRules = $ext;
            }
        }
        if (isset($this->language)) {
            if (null !== ($val = $this->language->getValue())) {
                $out->language = $val;
            }
            if ($this->language->_nonValueFieldDefined()) {
                $ext = $this->language->jsonSerialize();
                unset($ext->value);
                $out->_language = $ext;
            }
        }
        if ([] !== ($vs = $this->_getFHIRComments())) {
            $out->fhir_comments = $vs;
        }
        return $out;
    }

    /* class_default.php:227 */
    public function __toString(): string
    {
        return (string) self::FHIR_TYPE_NAME;
    }
}
