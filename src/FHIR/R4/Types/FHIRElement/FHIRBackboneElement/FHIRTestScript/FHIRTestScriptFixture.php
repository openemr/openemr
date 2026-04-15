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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A structured set of tests against a FHIR server or client implementation to
 * determine compliance against the FHIR specification.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTestScriptFixture extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TEST_SCRIPT_DOT_FIXTURE;

    /* class_default.php:56 */
    public const FIELD_AUTOCREATE = 'autocreate';
    public const FIELD_AUTOCREATE_EXT = '_autocreate';
    public const FIELD_AUTODELETE = 'autodelete';
    public const FIELD_AUTODELETE_EXT = '_autodelete';
    public const FIELD_RESOURCE = 'resource';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_AUTOCREATE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_AUTODELETE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_AUTOCREATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AUTODELETE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not to implicitly create the fixture during setup. If true, the
     * fixture is automatically created on each server being tested during setup,
     * therefore no create operation is required for this fixture in the
     * TestScript.setup section.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $autocreate;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not to implicitly delete the fixture during teardown. If true, the
     * fixture is automatically deleted on each server being tested during teardown,
     * therefore no delete operation is required for this fixture in the
     * TestScript.teardown section.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $autodelete;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the resource (containing the contents of the resource needed for
     * operations).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $resource;

    /* constructor.php:61 */
    /**
     * FHIRTestScriptFixture Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $autocreate
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $autodelete
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $resource
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $autocreate = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $autodelete = null,
                                null|FHIRReference $resource = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $autocreate) {
            $this->setAutocreate($autocreate);
        }
        if (null !== $autodelete) {
            $this->setAutodelete($autodelete);
        }
        if (null !== $resource) {
            $this->setResource($resource);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not to implicitly create the fixture during setup. If true, the
     * fixture is automatically created on each server being tested during setup,
     * therefore no create operation is required for this fixture in the
     * TestScript.setup section.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getAutocreate(): null|FHIRBoolean
    {
        return $this->autocreate ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not to implicitly create the fixture during setup. If true, the
     * fixture is automatically created on each server being tested during setup,
     * therefore no create operation is required for this fixture in the
     * TestScript.setup section.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $autocreate
     * @return static
     */
    public function setAutocreate(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $autocreate): self
    {
        if (null === $autocreate) {
            unset($this->autocreate);
            return $this;
        }
        if (!($autocreate instanceof FHIRBoolean)) {
            $autocreate = new FHIRBoolean(value: $autocreate);
        }
        $this->autocreate = $autocreate;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not to implicitly delete the fixture during teardown. If true, the
     * fixture is automatically deleted on each server being tested during teardown,
     * therefore no delete operation is required for this fixture in the
     * TestScript.teardown section.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getAutodelete(): null|FHIRBoolean
    {
        return $this->autodelete ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether or not to implicitly delete the fixture during teardown. If true, the
     * fixture is automatically deleted on each server being tested during teardown,
     * therefore no delete operation is required for this fixture in the
     * TestScript.teardown section.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $autodelete
     * @return static
     */
    public function setAutodelete(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $autodelete): self
    {
        if (null === $autodelete) {
            unset($this->autodelete);
            return $this;
        }
        if (!($autodelete instanceof FHIRBoolean)) {
            $autodelete = new FHIRBoolean(value: $autodelete);
        }
        $this->autodelete = $autodelete;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the resource (containing the contents of the resource needed for
     * operations).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getResource(): null|FHIRReference
    {
        return $this->resource ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the resource (containing the contents of the resource needed for
     * operations).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $resource
     * @return static
     */
    public function setResource(null|FHIRReference $resource): self
    {
        if (null === $resource) {
            unset($this->resource);
            return $this;
        }
        $this->resource = $resource;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptFixture $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptFixture
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTestScriptFixture)) {
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
            } else if (self::FIELD_AUTOCREATE === $cen) {
                $type->setAutocreate(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTODELETE === $cen) {
                $type->setAutodelete(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESOURCE === $cen) {
                $type->setResource(FHIRReference::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AUTOCREATE])) {
            if (isset($type->autocreate)) {
                $type->autocreate->setValue((string)$attributes[self::FIELD_AUTOCREATE]);
            } else {
                $type->setAutocreate((string)$attributes[self::FIELD_AUTOCREATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AUTOCREATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AUTODELETE])) {
            if (isset($type->autodelete)) {
                $type->autodelete->setValue((string)$attributes[self::FIELD_AUTODELETE]);
            } else {
                $type->setAutodelete((string)$attributes[self::FIELD_AUTODELETE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AUTODELETE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->autocreate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AUTOCREATE]) {
            $xw->writeAttribute(self::FIELD_AUTOCREATE, $this->autocreate->_getValueAsString());
        }
        if (isset($this->autodelete) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AUTODELETE]) {
            $xw->writeAttribute(self::FIELD_AUTODELETE, $this->autodelete->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->autocreate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AUTOCREATE]
                || $this->autocreate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AUTOCREATE);
            $this->autocreate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AUTOCREATE]);
            $xw->endElement();
        }
        if (isset($this->autodelete)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AUTODELETE]
                || $this->autodelete->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AUTODELETE);
            $this->autodelete->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AUTODELETE]);
            $xw->endElement();
        }
        if (isset($this->resource)) {
            $xw->startElement(self::FIELD_RESOURCE);
            $this->resource->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptFixture $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptFixture
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
        } else if (!($type instanceof FHIRTestScriptFixture)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->autocreate)
            || isset($decoded->_autocreate)
            || property_exists($decoded, self::FIELD_AUTOCREATE)
            || property_exists($decoded, self::FIELD_AUTOCREATE_EXT)) {
            $v = $decoded->_autocreate ?? new \stdClass();
            $v->value = $decoded->autocreate ?? null;
            $type->setAutocreate(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->autodelete)
            || isset($decoded->_autodelete)
            || property_exists($decoded, self::FIELD_AUTODELETE)
            || property_exists($decoded, self::FIELD_AUTODELETE_EXT)) {
            $v = $decoded->_autodelete ?? new \stdClass();
            $v->value = $decoded->autodelete ?? null;
            $type->setAutodelete(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->resource) || property_exists($decoded, self::FIELD_RESOURCE)) {
            if (is_array($decoded->resource)) {
                $type->setResource(FHIRReference::jsonUnserialize(reset($decoded->resource), $config));
            } else {
                $type->setResource(FHIRReference::jsonUnserialize($decoded->resource, $config));
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
        if (isset($this->autocreate)) {
            if (null !== ($val = $this->autocreate->getValue())) {
                $out->autocreate = $val;
            }
            if ($this->autocreate->_nonValueFieldDefined()) {
                $ext = $this->autocreate->jsonSerialize();
                unset($ext->value);
                $out->_autocreate = $ext;
            }
        }
        if (isset($this->autodelete)) {
            if (null !== ($val = $this->autodelete->getValue())) {
                $out->autodelete = $val;
            }
            if ($this->autodelete->_nonValueFieldDefined()) {
                $ext = $this->autodelete->jsonSerialize();
                unset($ext->value);
                $out->_autodelete = $ext;
            }
        }
        if (isset($this->resource)) {
            $out->resource = $this->resource;
        }
        return $out;
    }
}
