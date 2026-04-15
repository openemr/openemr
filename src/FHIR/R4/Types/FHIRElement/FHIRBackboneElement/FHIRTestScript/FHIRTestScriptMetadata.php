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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A structured set of tests against a FHIR server or client implementation to
 * determine compliance against the FHIR specification.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTestScriptMetadata extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TEST_SCRIPT_DOT_METADATA;

    /* class_default.php:56 */
    public const FIELD_LINK = 'link';
    public const FIELD_CAPABILITY = 'capability';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_CAPABILITY => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * A link to the FHIR specification that this test is covering.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptLink>
     */
    #[FHIRTestScriptLink]
    protected array $link;
    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * Capabilities that must exist and are assumed to function correctly on the FHIR
     * server being tested.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability>
     */
    #[FHIRTestScriptCapability]
    protected array $capability;

    /* constructor.php:61 */
    /**
     * FHIRTestScriptMetadata Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptLink> $link
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability> $capability
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $link = null,
                                null|iterable $capability = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $link) {
            $this->setLink(...$link);
        }
        if (null !== $capability) {
            $this->setCapability(...$capability);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * A link to the FHIR specification that this test is covering.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptLink>
     */
    public function getLink(): array
    {
        return $this->link ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptLink>
     */
    public function getLinkIterator(): iterable
    {
        if (!isset($this->link)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->link);
    }

    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * A link to the FHIR specification that this test is covering.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptLink $link
     * @return static
     */
    public function addLink(FHIRTestScriptLink $link): self
    {
        if (!isset($this->link)) {
            $this->link = [];
        }
        $this->link[] = $link;
        return $this;
    }

    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * A link to the FHIR specification that this test is covering.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptLink ...$link
     * @return static
     */
    public function setLink(FHIRTestScriptLink ...$link): self
    {
        if ([] === $link) {
            unset($this->link);
            return $this;
        }
        $this->link = $link;
        return $this;
    }

    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * Capabilities that must exist and are assumed to function correctly on the FHIR
     * server being tested.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability>
     */
    public function getCapability(): array
    {
        return $this->capability ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability>
     */
    public function getCapabilityIterator(): iterable
    {
        if (!isset($this->capability)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->capability);
    }

    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * Capabilities that must exist and are assumed to function correctly on the FHIR
     * server being tested.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability $capability
     * @return static
     */
    public function addCapability(FHIRTestScriptCapability $capability): self
    {
        if (!isset($this->capability)) {
            $this->capability = [];
        }
        $this->capability[] = $capability;
        return $this;
    }

    /**
     * A structured set of tests against a FHIR server or client implementation to
     * determine compliance against the FHIR specification.
     *
     * Capabilities that must exist and are assumed to function correctly on the FHIR
     * server being tested.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptCapability ...$capability
     * @return static
     */
    public function setCapability(FHIRTestScriptCapability ...$capability): self
    {
        if ([] === $capability) {
            unset($this->capability);
            return $this;
        }
        $this->capability = $capability;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptMetadata $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptMetadata
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTestScriptMetadata)) {
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
                $type->addLink(FHIRTestScriptLink::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CAPABILITY === $cen) {
                $type->addCapability(FHIRTestScriptCapability::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        parent::xmlSerialize($xw, $config);
        if (isset($this->link)) {
            foreach ($this->link as $v) {
                $xw->startElement(self::FIELD_LINK);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->capability)) {
            foreach ($this->capability as $v) {
                $xw->startElement(self::FIELD_CAPABILITY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptMetadata $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestScript\FHIRTestScriptMetadata
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
        } else if (!($type instanceof FHIRTestScriptMetadata)) {
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
                $type->addLink(FHIRTestScriptLink::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->capability) || property_exists($decoded, self::FIELD_CAPABILITY)) {
            if (is_object($decoded->capability)) {
                $vals = [$decoded->capability];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CAPABILITY, true);
            } else {
                $vals = $decoded->capability;
            }
            foreach($vals as $v) {
                $type->addCapability(FHIRTestScriptCapability::jsonUnserialize($v, $config));
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
        if (isset($this->capability) && [] !== $this->capability) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CAPABILITY) && 1 === count($this->capability)) {
                $out->capability = $this->capability[0];
            } else {
                $out->capability = $this->capability;
            }
        }
        return $out;
    }
}
