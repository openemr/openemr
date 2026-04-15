<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestReportActionResultList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportActionResult;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A summary of information based on the results of executing a TestScript.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTestReportOperation extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TEST_REPORT_DOT_OPERATION;

    /* class_default.php:56 */
    public const FIELD_RESULT = 'result';
    public const FIELD_RESULT_EXT = '_result';
    public const FIELD_MESSAGE = 'message';
    public const FIELD_MESSAGE_EXT = '_message';
    public const FIELD_DETAIL = 'detail';
    public const FIELD_DETAIL_EXT = '_detail';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_RESULT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_RESULT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MESSAGE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DETAIL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * The results of executing an action.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The result of this operation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportActionResult
     */
    #[FHIRTestReportActionResult]
    protected FHIRTestReportActionResult $result;
    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An explanatory message associated with the result.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    #[FHIRMarkdown]
    protected FHIRMarkdown $message;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A link to further details on the result.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $detail;

    /* constructor.php:61 */
    /**
     * FHIRTestReportOperation Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestReportActionResultList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportActionResult $result
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $message
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $detail
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRTestReportActionResultList|FHIRTestReportActionResult $result = null,
                                null|string|FHIRMarkdownPrimitive|FHIRMarkdown $message = null,
                                null|string|FHIRUriPrimitive|FHIRUri $detail = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $result) {
            $this->setResult($result);
        }
        if (null !== $message) {
            $this->setMessage($message);
        }
        if (null !== $detail) {
            $this->setDetail($detail);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * The results of executing an action.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The result of this operation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportActionResult
     */
    public function getResult(): null|FHIRTestReportActionResult
    {
        return $this->result ?? null;
    }

    /**
     * The results of executing an action.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The result of this operation.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestReportActionResultList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportActionResult $result
     * @return static
     */
    public function setResult(null|string|FHIRTestReportActionResultList|FHIRTestReportActionResult $result): self
    {
        if (null === $result) {
            unset($this->result);
            return $this;
        }
        if (!($result instanceof FHIRTestReportActionResult)) {
            $result = new FHIRTestReportActionResult(value: $result);
        }
        $this->result = $result;
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
     * An explanatory message associated with the result.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown
     */
    public function getMessage(): null|FHIRMarkdown
    {
        return $this->message ?? null;
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
     * An explanatory message associated with the result.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRMarkdownPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMarkdown $message
     * @return static
     */
    public function setMessage(null|string|FHIRMarkdownPrimitive|FHIRMarkdown $message): self
    {
        if (null === $message) {
            unset($this->message);
            return $this;
        }
        if (!($message instanceof FHIRMarkdown)) {
            $message = new FHIRMarkdown(value: $message);
        }
        $this->message = $message;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A link to further details on the result.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getDetail(): null|FHIRUri
    {
        return $this->detail ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A link to further details on the result.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $detail
     * @return static
     */
    public function setDetail(null|string|FHIRUriPrimitive|FHIRUri $detail): self
    {
        if (null === $detail) {
            unset($this->detail);
            return $this;
        }
        if (!($detail instanceof FHIRUri)) {
            $detail = new FHIRUri(value: $detail);
        }
        $this->detail = $detail;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportOperation
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTestReportOperation)) {
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
            } else if (self::FIELD_RESULT === $cen) {
                $type->setResult(FHIRTestReportActionResult::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MESSAGE === $cen) {
                $type->setMessage(FHIRMarkdown::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DETAIL === $cen) {
                $type->setDetail(FHIRUri::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RESULT])) {
            if (isset($type->result)) {
                $type->result->setValue((string)$attributes[self::FIELD_RESULT]);
            } else {
                $type->setResult((string)$attributes[self::FIELD_RESULT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RESULT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MESSAGE])) {
            if (isset($type->message)) {
                $type->message->setValue((string)$attributes[self::FIELD_MESSAGE]);
            } else {
                $type->setMessage((string)$attributes[self::FIELD_MESSAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MESSAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DETAIL])) {
            if (isset($type->detail)) {
                $type->detail->setValue((string)$attributes[self::FIELD_DETAIL]);
            } else {
                $type->setDetail((string)$attributes[self::FIELD_DETAIL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DETAIL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->result) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RESULT]) {
            $xw->writeAttribute(self::FIELD_RESULT, $this->result->_getValueAsString());
        }
        if (isset($this->message) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MESSAGE]) {
            $xw->writeAttribute(self::FIELD_MESSAGE, $this->message->_getValueAsString());
        }
        if (isset($this->detail) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DETAIL]) {
            $xw->writeAttribute(self::FIELD_DETAIL, $this->detail->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->result)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RESULT]
                || $this->result->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RESULT);
            $this->result->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RESULT]);
            $xw->endElement();
        }
        if (isset($this->message)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MESSAGE]
                || $this->message->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MESSAGE);
            $this->message->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MESSAGE]);
            $xw->endElement();
        }
        if (isset($this->detail)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DETAIL]
                || $this->detail->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DETAIL);
            $this->detail->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DETAIL]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportOperation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportOperation
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
        } else if (!($type instanceof FHIRTestReportOperation)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->result)
            || isset($decoded->_result)
            || property_exists($decoded, self::FIELD_RESULT)
            || property_exists($decoded, self::FIELD_RESULT_EXT)) {
            $v = $decoded->_result ?? new \stdClass();
            $v->value = $decoded->result ?? null;
            $type->setResult(FHIRTestReportActionResult::jsonUnserialize($v, $config));
        }
        if (isset($decoded->message)
            || isset($decoded->_message)
            || property_exists($decoded, self::FIELD_MESSAGE)
            || property_exists($decoded, self::FIELD_MESSAGE_EXT)) {
            $v = $decoded->_message ?? new \stdClass();
            $v->value = $decoded->message ?? null;
            $type->setMessage(FHIRMarkdown::jsonUnserialize($v, $config));
        }
        if (isset($decoded->detail)
            || isset($decoded->_detail)
            || property_exists($decoded, self::FIELD_DETAIL)
            || property_exists($decoded, self::FIELD_DETAIL_EXT)) {
            $v = $decoded->_detail ?? new \stdClass();
            $v->value = $decoded->detail ?? null;
            $type->setDetail(FHIRUri::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->result)) {
            if (null !== ($val = $this->result->getValue())) {
                $out->result = $val;
            }
            if ($this->result->_nonValueFieldDefined()) {
                $ext = $this->result->jsonSerialize();
                unset($ext->value);
                $out->_result = $ext;
            }
        }
        if (isset($this->message)) {
            if (null !== ($val = $this->message->getValue())) {
                $out->message = $val;
            }
            if ($this->message->_nonValueFieldDefined()) {
                $ext = $this->message->jsonSerialize();
                unset($ext->value);
                $out->_message = $ext;
            }
        }
        if (isset($this->detail)) {
            if (null !== ($val = $this->detail->getValue())) {
                $out->detail = $val;
            }
            if ($this->detail->_nonValueFieldDefined()) {
                $ext = $this->detail->jsonSerialize();
                unset($ext->value);
                $out->_detail = $ext;
            }
        }
        return $out;
    }
}
