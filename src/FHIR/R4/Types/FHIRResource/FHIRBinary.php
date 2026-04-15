<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource;

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
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBase64BinaryPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;

/**
 * A resource that represents the data of a single raw artifact as digital content
 * accessible in its native format. A Binary resource can contain any content,
 * whether text, image, pdf, zip archive, etc.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRBinary extends FHIRResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_BINARY;

    /* class_default.php:56 */
    public const FIELD_CONTENT_TYPE = 'contentType';
    public const FIELD_CONTENT_TYPE_EXT = '_contentType';
    public const FIELD_SECURITY_CONTEXT = 'securityContext';
    public const FIELD_DATA = 'data';
    public const FIELD_DATA_EXT = '_data';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_CONTENT_TYPE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_CONTENT_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DATA => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * MimeType of the binary content represented as a standard MimeType (BCP 13).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $contentType;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This element identifies another resource that can be used as a proxy of the
     * security sensitivity to use when deciding and enforcing access control rules for
     * the Binary resource. Given that the Binary resource contains very few elements
     * that can be used to determine the sensitivity of the data and relationships to
     * individuals, the referenced resource stands in as a proxy equivalent for this
     * purpose. This referenced resource may be related to the Binary (e.g. Media,
     * DocumentReference), or may be some non-related Resource purely as a security
     * proxy. E.g. to identify that the binary resource relates to a patient, and
     * access should only be granted to applications that have access to the patient.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $securityContext;
    /**
     * A stream of bytes
     * A stream of bytes, base64 encoded
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The actual content, base64 encoded.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary
     */
    #[FHIRBase64Binary]
    protected FHIRBase64Binary $data;

    /* constructor.php:61 */
    /**
     * FHIRBinary Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $contentType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $securityContext
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRBase64BinaryPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary $data
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|string|FHIRCodePrimitive|FHIRCode $contentType = null,
                                null|FHIRReference $securityContext = null,
                                null|string|FHIRBase64BinaryPrimitive|FHIRBase64Binary $data = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            fhirComments: $fhirComments);
        if (null !== $contentType) {
            $this->setContentType($contentType);
        }
        if (null !== $securityContext) {
            $this->setSecurityContext($securityContext);
        }
        if (null !== $data) {
            $this->setData($data);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * MimeType of the binary content represented as a standard MimeType (BCP 13).
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
     * MimeType of the binary content represented as a standard MimeType (BCP 13).
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This element identifies another resource that can be used as a proxy of the
     * security sensitivity to use when deciding and enforcing access control rules for
     * the Binary resource. Given that the Binary resource contains very few elements
     * that can be used to determine the sensitivity of the data and relationships to
     * individuals, the referenced resource stands in as a proxy equivalent for this
     * purpose. This referenced resource may be related to the Binary (e.g. Media,
     * DocumentReference), or may be some non-related Resource purely as a security
     * proxy. E.g. to identify that the binary resource relates to a patient, and
     * access should only be granted to applications that have access to the patient.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSecurityContext(): null|FHIRReference
    {
        return $this->securityContext ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This element identifies another resource that can be used as a proxy of the
     * security sensitivity to use when deciding and enforcing access control rules for
     * the Binary resource. Given that the Binary resource contains very few elements
     * that can be used to determine the sensitivity of the data and relationships to
     * individuals, the referenced resource stands in as a proxy equivalent for this
     * purpose. This referenced resource may be related to the Binary (e.g. Media,
     * DocumentReference), or may be some non-related Resource purely as a security
     * proxy. E.g. to identify that the binary resource relates to a patient, and
     * access should only be granted to applications that have access to the patient.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $securityContext
     * @return static
     */
    public function setSecurityContext(null|FHIRReference $securityContext): self
    {
        if (null === $securityContext) {
            unset($this->securityContext);
            return $this;
        }
        $this->securityContext = $securityContext;
        return $this;
    }

    /**
     * A stream of bytes
     * A stream of bytes, base64 encoded
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The actual content, base64 encoded.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary
     */
    public function getData(): null|FHIRBase64Binary
    {
        return $this->data ?? null;
    }

    /**
     * A stream of bytes
     * A stream of bytes, base64 encoded
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The actual content, base64 encoded.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRBase64BinaryPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBase64Binary $data
     * @return static
     */
    public function setData(null|string|FHIRBase64BinaryPrimitive|FHIRBase64Binary $data): self
    {
        if (null === $data) {
            unset($this->data);
            return $this;
        }
        if (!($data instanceof FHIRBase64Binary)) {
            $data = new FHIRBase64Binary(value: $data);
        }
        $this->data = $data;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBinary $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBinary
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRBinary)) {
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
            } else if (self::FIELD_CONTENT_TYPE === $cen) {
                $type->setContentType(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SECURITY_CONTEXT === $cen) {
                $type->setSecurityContext(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATA === $cen) {
                $type->setData(FHIRBase64Binary::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_CONTENT_TYPE])) {
            if (isset($type->contentType)) {
                $type->contentType->setValue((string)$attributes[self::FIELD_CONTENT_TYPE]);
            } else {
                $type->setContentType((string)$attributes[self::FIELD_CONTENT_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONTENT_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DATA])) {
            if (isset($type->data)) {
                $type->data->setValue((string)$attributes[self::FIELD_DATA]);
            } else {
                $type->setData((string)$attributes[self::FIELD_DATA]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DATA, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('Binary', $this->_getSourceXMLNS());
        }
        if (isset($this->contentType) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONTENT_TYPE]) {
            $xw->writeAttribute(self::FIELD_CONTENT_TYPE, $this->contentType->_getValueAsString());
        }
        if (isset($this->data) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DATA]) {
            $xw->writeAttribute(self::FIELD_DATA, $this->data->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->contentType)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONTENT_TYPE]
                || $this->contentType->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONTENT_TYPE);
            $this->contentType->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONTENT_TYPE]);
            $xw->endElement();
        }
        if (isset($this->securityContext)) {
            $xw->startElement(self::FIELD_SECURITY_CONTEXT);
            $this->securityContext->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->data)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DATA]
                || $this->data->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DATA);
            $this->data->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DATA]);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBinary $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRBinary
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
        } else if (!($type instanceof FHIRBinary)) {
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
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->contentType)
            || isset($decoded->_contentType)
            || property_exists($decoded, self::FIELD_CONTENT_TYPE)
            || property_exists($decoded, self::FIELD_CONTENT_TYPE_EXT)) {
            $v = $decoded->_contentType ?? new \stdClass();
            $v->value = $decoded->contentType ?? null;
            $type->setContentType(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->securityContext) || property_exists($decoded, self::FIELD_SECURITY_CONTEXT)) {
            if (is_array($decoded->securityContext)) {
                $type->setSecurityContext(FHIRReference::jsonUnserialize(reset($decoded->securityContext), $config));
            } else {
                $type->setSecurityContext(FHIRReference::jsonUnserialize($decoded->securityContext, $config));
            }
        }
        if (isset($decoded->data)
            || isset($decoded->_data)
            || property_exists($decoded, self::FIELD_DATA)
            || property_exists($decoded, self::FIELD_DATA_EXT)) {
            $v = $decoded->_data ?? new \stdClass();
            $v->value = $decoded->data ?? null;
            $type->setData(FHIRBase64Binary::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
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
        if (isset($this->securityContext)) {
            $out->securityContext = $this->securityContext;
        }
        if (isset($this->data)) {
            if (null !== ($val = $this->data->getValue())) {
                $out->data = $val;
            }
            if ($this->data->_nonValueFieldDefined()) {
                $ext = $this->data->jsonSerialize();
                unset($ext->value);
                $out->_data = $ext;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
