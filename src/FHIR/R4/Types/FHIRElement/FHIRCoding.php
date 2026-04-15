<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A reference to a code defined by a terminology system.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCoding extends FHIRElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CODING;

    /* class_default.php:56 */
    public const FIELD_SYSTEM = 'system';
    public const FIELD_SYSTEM_EXT = '_system';
    public const FIELD_VERSION = 'version';
    public const FIELD_VERSION_EXT = '_version';
    public const FIELD_CODE = 'code';
    public const FIELD_CODE_EXT = '_code';
    public const FIELD_DISPLAY = 'display';
    public const FIELD_DISPLAY_EXT = '_display';
    public const FIELD_USER_SELECTED = 'userSelected';
    public const FIELD_USER_SELECTED_EXT = '_userSelected';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SYSTEM => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VERSION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CODE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DISPLAY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_USER_SELECTED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identification of the code system that defines the meaning of the symbol in
     * the code.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $system;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version of the code system which was used when choosing this code. Note that
     * a well-maintained code system does not need the version reported, because the
     * meaning of codes is consistent across versions. However this cannot consistently
     * be assured, and when the meaning is not guaranteed to be consistent, the version
     * SHOULD be exchanged.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $version;
    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A symbol in syntax defined by the system. The symbol may be a predefined code or
     * an expression in a syntax defined by the coding system (e.g. post-coordination).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    #[FHIRCode]
    protected FHIRCode $code;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A representation of the meaning of the code in the system, following the rules
     * of the system.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $display;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates that this coding was chosen by a user directly - e.g. off a pick list
     * of available items (codes or displays).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $userSelected;

    /* constructor.php:61 */
    /**
     * FHIRCoding Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $system
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $version
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $code
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $display
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $userSelected
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|string|FHIRUriPrimitive|FHIRUri $system = null,
                                null|string|FHIRStringPrimitive|FHIRString $version = null,
                                null|string|FHIRCodePrimitive|FHIRCode $code = null,
                                null|string|FHIRStringPrimitive|FHIRString $display = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $userSelected = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            fhirComments: $fhirComments);
        if (null !== $system) {
            $this->setSystem($system);
        }
        if (null !== $version) {
            $this->setVersion($version);
        }
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $display) {
            $this->setDisplay($display);
        }
        if (null !== $userSelected) {
            $this->setUserSelected($userSelected);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identification of the code system that defines the meaning of the symbol in
     * the code.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getSystem(): null|FHIRUri
    {
        return $this->system ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identification of the code system that defines the meaning of the symbol in
     * the code.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $system
     * @return static
     */
    public function setSystem(null|string|FHIRUriPrimitive|FHIRUri $system): self
    {
        if (null === $system) {
            unset($this->system);
            return $this;
        }
        if (!($system instanceof FHIRUri)) {
            $system = new FHIRUri(value: $system);
        }
        $this->system = $system;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version of the code system which was used when choosing this code. Note that
     * a well-maintained code system does not need the version reported, because the
     * meaning of codes is consistent across versions. However this cannot consistently
     * be assured, and when the meaning is not guaranteed to be consistent, the version
     * SHOULD be exchanged.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getVersion(): null|FHIRString
    {
        return $this->version ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The version of the code system which was used when choosing this code. Note that
     * a well-maintained code system does not need the version reported, because the
     * meaning of codes is consistent across versions. However this cannot consistently
     * be assured, and when the meaning is not guaranteed to be consistent, the version
     * SHOULD be exchanged.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $version
     * @return static
     */
    public function setVersion(null|string|FHIRStringPrimitive|FHIRString $version): self
    {
        if (null === $version) {
            unset($this->version);
            return $this;
        }
        if (!($version instanceof FHIRString)) {
            $version = new FHIRString(value: $version);
        }
        $this->version = $version;
        return $this;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A symbol in syntax defined by the system. The symbol may be a predefined code or
     * an expression in a syntax defined by the coding system (e.g. post-coordination).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode
     */
    public function getCode(): null|FHIRCode
    {
        return $this->code ?? null;
    }

    /**
     * A string which has at least one character and no leading or trailing whitespace
     * and where there is no whitespace other than single spaces in the contents
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A symbol in syntax defined by the system. The symbol may be a predefined code or
     * an expression in a syntax defined by the coding system (e.g. post-coordination).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $code
     * @return static
     */
    public function setCode(null|string|FHIRCodePrimitive|FHIRCode $code): self
    {
        if (null === $code) {
            unset($this->code);
            return $this;
        }
        if (!($code instanceof FHIRCode)) {
            $code = new FHIRCode(value: $code);
        }
        $this->code = $code;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A representation of the meaning of the code in the system, following the rules
     * of the system.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDisplay(): null|FHIRString
    {
        return $this->display ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A representation of the meaning of the code in the system, following the rules
     * of the system.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $display
     * @return static
     */
    public function setDisplay(null|string|FHIRStringPrimitive|FHIRString $display): self
    {
        if (null === $display) {
            unset($this->display);
            return $this;
        }
        if (!($display instanceof FHIRString)) {
            $display = new FHIRString(value: $display);
        }
        $this->display = $display;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates that this coding was chosen by a user directly - e.g. off a pick list
     * of available items (codes or displays).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getUserSelected(): null|FHIRBoolean
    {
        return $this->userSelected ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates that this coding was chosen by a user directly - e.g. off a pick list
     * of available items (codes or displays).
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $userSelected
     * @return static
     */
    public function setUserSelected(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $userSelected): self
    {
        if (null === $userSelected) {
            unset($this->userSelected);
            return $this;
        }
        if (!($userSelected instanceof FHIRBoolean)) {
            $userSelected = new FHIRBoolean(value: $userSelected);
        }
        $this->userSelected = $userSelected;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCoding)) {
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
            } else if (self::FIELD_SYSTEM === $cen) {
                $type->setSystem(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VERSION === $cen) {
                $type->setVersion(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DISPLAY === $cen) {
                $type->setDisplay(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_USER_SELECTED === $cen) {
                $type->setUserSelected(FHIRBoolean::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SYSTEM])) {
            if (isset($type->system)) {
                $type->system->setValue((string)$attributes[self::FIELD_SYSTEM]);
            } else {
                $type->setSystem((string)$attributes[self::FIELD_SYSTEM]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SYSTEM, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VERSION])) {
            if (isset($type->version)) {
                $type->version->setValue((string)$attributes[self::FIELD_VERSION]);
            } else {
                $type->setVersion((string)$attributes[self::FIELD_VERSION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VERSION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CODE])) {
            if (isset($type->code)) {
                $type->code->setValue((string)$attributes[self::FIELD_CODE]);
            } else {
                $type->setCode((string)$attributes[self::FIELD_CODE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CODE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DISPLAY])) {
            if (isset($type->display)) {
                $type->display->setValue((string)$attributes[self::FIELD_DISPLAY]);
            } else {
                $type->setDisplay((string)$attributes[self::FIELD_DISPLAY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DISPLAY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_USER_SELECTED])) {
            if (isset($type->userSelected)) {
                $type->userSelected->setValue((string)$attributes[self::FIELD_USER_SELECTED]);
            } else {
                $type->setUserSelected((string)$attributes[self::FIELD_USER_SELECTED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_USER_SELECTED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->system) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SYSTEM]) {
            $xw->writeAttribute(self::FIELD_SYSTEM, $this->system->_getValueAsString());
        }
        if (isset($this->version) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VERSION]) {
            $xw->writeAttribute(self::FIELD_VERSION, $this->version->_getValueAsString());
        }
        if (isset($this->code) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CODE]) {
            $xw->writeAttribute(self::FIELD_CODE, $this->code->_getValueAsString());
        }
        if (isset($this->display) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DISPLAY]) {
            $xw->writeAttribute(self::FIELD_DISPLAY, $this->display->_getValueAsString());
        }
        if (isset($this->userSelected) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_USER_SELECTED]) {
            $xw->writeAttribute(self::FIELD_USER_SELECTED, $this->userSelected->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->system)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SYSTEM]
                || $this->system->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SYSTEM);
            $this->system->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SYSTEM]);
            $xw->endElement();
        }
        if (isset($this->version)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VERSION]
                || $this->version->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VERSION);
            $this->version->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VERSION]);
            $xw->endElement();
        }
        if (isset($this->code)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CODE]
                || $this->code->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CODE]);
            $xw->endElement();
        }
        if (isset($this->display)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DISPLAY]
                || $this->display->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DISPLAY);
            $this->display->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DISPLAY]);
            $xw->endElement();
        }
        if (isset($this->userSelected)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_USER_SELECTED]
                || $this->userSelected->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_USER_SELECTED);
            $this->userSelected->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_USER_SELECTED]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
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
        } else if (!($type instanceof FHIRCoding)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->system)
            || isset($decoded->_system)
            || property_exists($decoded, self::FIELD_SYSTEM)
            || property_exists($decoded, self::FIELD_SYSTEM_EXT)) {
            $v = $decoded->_system ?? new \stdClass();
            $v->value = $decoded->system ?? null;
            $type->setSystem(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->version)
            || isset($decoded->_version)
            || property_exists($decoded, self::FIELD_VERSION)
            || property_exists($decoded, self::FIELD_VERSION_EXT)) {
            $v = $decoded->_version ?? new \stdClass();
            $v->value = $decoded->version ?? null;
            $type->setVersion(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->code)
            || isset($decoded->_code)
            || property_exists($decoded, self::FIELD_CODE)
            || property_exists($decoded, self::FIELD_CODE_EXT)) {
            $v = $decoded->_code ?? new \stdClass();
            $v->value = $decoded->code ?? null;
            $type->setCode(FHIRCode::jsonUnserialize($v, $config));
        }
        if (isset($decoded->display)
            || isset($decoded->_display)
            || property_exists($decoded, self::FIELD_DISPLAY)
            || property_exists($decoded, self::FIELD_DISPLAY_EXT)) {
            $v = $decoded->_display ?? new \stdClass();
            $v->value = $decoded->display ?? null;
            $type->setDisplay(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->userSelected)
            || isset($decoded->_userSelected)
            || property_exists($decoded, self::FIELD_USER_SELECTED)
            || property_exists($decoded, self::FIELD_USER_SELECTED_EXT)) {
            $v = $decoded->_userSelected ?? new \stdClass();
            $v->value = $decoded->userSelected ?? null;
            $type->setUserSelected(FHIRBoolean::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->system)) {
            if (null !== ($val = $this->system->getValue())) {
                $out->system = $val;
            }
            if ($this->system->_nonValueFieldDefined()) {
                $ext = $this->system->jsonSerialize();
                unset($ext->value);
                $out->_system = $ext;
            }
        }
        if (isset($this->version)) {
            if (null !== ($val = $this->version->getValue())) {
                $out->version = $val;
            }
            if ($this->version->_nonValueFieldDefined()) {
                $ext = $this->version->jsonSerialize();
                unset($ext->value);
                $out->_version = $ext;
            }
        }
        if (isset($this->code)) {
            if (null !== ($val = $this->code->getValue())) {
                $out->code = $val;
            }
            if ($this->code->_nonValueFieldDefined()) {
                $ext = $this->code->jsonSerialize();
                unset($ext->value);
                $out->_code = $ext;
            }
        }
        if (isset($this->display)) {
            if (null !== ($val = $this->display->getValue())) {
                $out->display = $val;
            }
            if ($this->display->_nonValueFieldDefined()) {
                $ext = $this->display->jsonSerialize();
                unset($ext->value);
                $out->_display = $ext;
            }
        }
        if (isset($this->userSelected)) {
            if (null !== ($val = $this->userSelected->getValue())) {
                $out->userSelected = $val;
            }
            if ($this->userSelected->_nonValueFieldDefined()) {
                $ext = $this->userSelected->jsonSerialize();
                unset($ext->value);
                $out->_userSelected = $ext;
            }
        }
        return $out;
    }
}
