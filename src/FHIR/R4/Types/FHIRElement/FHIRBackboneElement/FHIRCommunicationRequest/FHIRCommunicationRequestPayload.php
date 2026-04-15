<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCommunicationRequest;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A request to convey information; e.g. the CDS system proposes that an alert be
 * sent to a responsible provider, the CDS system proposes that the public health
 * agency be notified about a reportable condition.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCommunicationRequestPayload extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_COMMUNICATION_REQUEST_DOT_PAYLOAD;

    /* class_default.php:56 */
    public const FIELD_CONTENT_STRING = 'contentString';
    public const FIELD_CONTENT_STRING_EXT = '_contentString';
    public const FIELD_CONTENT_ATTACHMENT = 'contentAttachment';
    public const FIELD_CONTENT_REFERENCE = 'contentReference';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_CONTENT_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The communicated content (or for multi-part communications, one portion of the
     * communication). (choose any one of content*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $contentString;
    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The communicated content (or for multi-part communications, one portion of the
     * communication). (choose any one of content*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    #[FHIRAttachment]
    protected FHIRAttachment $contentAttachment;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The communicated content (or for multi-part communications, one portion of the
     * communication). (choose any one of content*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $contentReference;

    /* constructor.php:61 */
    /**
     * FHIRCommunicationRequestPayload Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $contentString
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $contentAttachment
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $contentReference
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $contentString = null,
                                null|FHIRAttachment $contentAttachment = null,
                                null|FHIRReference $contentReference = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $contentString) {
            $this->setContentString($contentString);
        }
        if (null !== $contentAttachment) {
            $this->setContentAttachment($contentAttachment);
        }
        if (null !== $contentReference) {
            $this->setContentReference($contentReference);
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
     * The communicated content (or for multi-part communications, one portion of the
     * communication). (choose any one of content*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getContentString(): null|FHIRString
    {
        return $this->contentString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The communicated content (or for multi-part communications, one portion of the
     * communication). (choose any one of content*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $contentString
     * @return static
     */
    public function setContentString(null|string|FHIRStringPrimitive|FHIRString $contentString): self
    {
        if (null === $contentString) {
            unset($this->contentString);
            return $this;
        }
        if (!($contentString instanceof FHIRString)) {
            $contentString = new FHIRString(value: $contentString);
        }
        $this->contentString = $contentString;
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The communicated content (or for multi-part communications, one portion of the
     * communication). (choose any one of content*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    public function getContentAttachment(): null|FHIRAttachment
    {
        return $this->contentAttachment ?? null;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The communicated content (or for multi-part communications, one portion of the
     * communication). (choose any one of content*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $contentAttachment
     * @return static
     */
    public function setContentAttachment(null|FHIRAttachment $contentAttachment): self
    {
        if (null === $contentAttachment) {
            unset($this->contentAttachment);
            return $this;
        }
        $this->contentAttachment = $contentAttachment;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The communicated content (or for multi-part communications, one portion of the
     * communication). (choose any one of content*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getContentReference(): null|FHIRReference
    {
        return $this->contentReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The communicated content (or for multi-part communications, one portion of the
     * communication). (choose any one of content*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $contentReference
     * @return static
     */
    public function setContentReference(null|FHIRReference $contentReference): self
    {
        if (null === $contentReference) {
            unset($this->contentReference);
            return $this;
        }
        $this->contentReference = $contentReference;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCommunicationRequest\FHIRCommunicationRequestPayload $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCommunicationRequest\FHIRCommunicationRequestPayload
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCommunicationRequestPayload)) {
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
            } else if (self::FIELD_CONTENT_STRING === $cen) {
                $type->setContentString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTENT_ATTACHMENT === $cen) {
                $type->setContentAttachment(FHIRAttachment::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTENT_REFERENCE === $cen) {
                $type->setContentReference(FHIRReference::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CONTENT_STRING])) {
            if (isset($type->contentString)) {
                $type->contentString->setValue((string)$attributes[self::FIELD_CONTENT_STRING]);
            } else {
                $type->setContentString((string)$attributes[self::FIELD_CONTENT_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CONTENT_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->contentString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CONTENT_STRING]) {
            $xw->writeAttribute(self::FIELD_CONTENT_STRING, $this->contentString->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->contentString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CONTENT_STRING]
                || $this->contentString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CONTENT_STRING);
            $this->contentString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CONTENT_STRING]);
            $xw->endElement();
        }
        if (isset($this->contentAttachment)) {
            $xw->startElement(self::FIELD_CONTENT_ATTACHMENT);
            $this->contentAttachment->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->contentReference)) {
            $xw->startElement(self::FIELD_CONTENT_REFERENCE);
            $this->contentReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCommunicationRequest\FHIRCommunicationRequestPayload $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCommunicationRequest\FHIRCommunicationRequestPayload
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
        } else if (!($type instanceof FHIRCommunicationRequestPayload)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->contentString)
            || isset($decoded->_contentString)
            || property_exists($decoded, self::FIELD_CONTENT_STRING)
            || property_exists($decoded, self::FIELD_CONTENT_STRING_EXT)) {
            $v = $decoded->_contentString ?? new \stdClass();
            $v->value = $decoded->contentString ?? null;
            $type->setContentString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->contentAttachment) || property_exists($decoded, self::FIELD_CONTENT_ATTACHMENT)) {
            if (is_array($decoded->contentAttachment)) {
                $type->setContentAttachment(FHIRAttachment::jsonUnserialize(reset($decoded->contentAttachment), $config));
            } else {
                $type->setContentAttachment(FHIRAttachment::jsonUnserialize($decoded->contentAttachment, $config));
            }
        }
        if (isset($decoded->contentReference) || property_exists($decoded, self::FIELD_CONTENT_REFERENCE)) {
            if (is_array($decoded->contentReference)) {
                $type->setContentReference(FHIRReference::jsonUnserialize(reset($decoded->contentReference), $config));
            } else {
                $type->setContentReference(FHIRReference::jsonUnserialize($decoded->contentReference, $config));
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
        if (isset($this->contentString)) {
            if (null !== ($val = $this->contentString->getValue())) {
                $out->contentString = $val;
            }
            if ($this->contentString->_nonValueFieldDefined()) {
                $ext = $this->contentString->jsonSerialize();
                unset($ext->value);
                $out->_contentString = $ext;
            }
        }
        if (isset($this->contentAttachment)) {
            $out->contentAttachment = $this->contentAttachment;
        }
        if (isset($this->contentReference)) {
            $out->contentReference = $this->contentReference;
        }
        return $out;
    }
}
