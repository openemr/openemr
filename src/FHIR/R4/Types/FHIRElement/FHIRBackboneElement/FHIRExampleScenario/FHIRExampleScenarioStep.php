<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Example of workflow instance.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRExampleScenarioStep extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_EXAMPLE_SCENARIO_DOT_STEP;

    /* class_default.php:56 */
    public const FIELD_PROCESS = 'process';
    public const FIELD_PAUSE = 'pause';
    public const FIELD_PAUSE_EXT = '_pause';
    public const FIELD_OPERATION = 'operation';
    public const FIELD_ALTERNATIVE = 'alternative';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_PAUSE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Example of workflow instance.
     *
     * Nested process.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioProcess>
     */
    #[FHIRExampleScenarioProcess]
    protected array $process;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If there is a pause in the flow.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $pause;
    /**
     * Example of workflow instance.
     *
     * Each interaction or action.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioOperation
     */
    #[FHIRExampleScenarioOperation]
    protected FHIRExampleScenarioOperation $operation;
    /**
     * Example of workflow instance.
     *
     * Indicates an alternative step that can be taken instead of the operations on the
     * base step in exceptional/atypical circumstances.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioAlternative>
     */
    #[FHIRExampleScenarioAlternative]
    protected array $alternative;

    /* constructor.php:61 */
    /**
     * FHIRExampleScenarioStep Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioProcess> $process
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $pause
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioOperation $operation
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioAlternative> $alternative
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $process = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $pause = null,
                                null|FHIRExampleScenarioOperation $operation = null,
                                null|iterable $alternative = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $process) {
            $this->setProcess(...$process);
        }
        if (null !== $pause) {
            $this->setPause($pause);
        }
        if (null !== $operation) {
            $this->setOperation($operation);
        }
        if (null !== $alternative) {
            $this->setAlternative(...$alternative);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Example of workflow instance.
     *
     * Nested process.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioProcess>
     */
    public function getProcess(): array
    {
        return $this->process ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioProcess>
     */
    public function getProcessIterator(): iterable
    {
        if (!isset($this->process)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->process);
    }

    /**
     * Example of workflow instance.
     *
     * Nested process.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioProcess $process
     * @return static
     */
    public function addProcess(FHIRExampleScenarioProcess $process): self
    {
        if (!isset($this->process)) {
            $this->process = [];
        }
        $this->process[] = $process;
        return $this;
    }

    /**
     * Example of workflow instance.
     *
     * Nested process.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioProcess ...$process
     * @return static
     */
    public function setProcess(FHIRExampleScenarioProcess ...$process): self
    {
        if ([] === $process) {
            unset($this->process);
            return $this;
        }
        $this->process = $process;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If there is a pause in the flow.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getPause(): null|FHIRBoolean
    {
        return $this->pause ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If there is a pause in the flow.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $pause
     * @return static
     */
    public function setPause(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $pause): self
    {
        if (null === $pause) {
            unset($this->pause);
            return $this;
        }
        if (!($pause instanceof FHIRBoolean)) {
            $pause = new FHIRBoolean(value: $pause);
        }
        $this->pause = $pause;
        return $this;
    }

    /**
     * Example of workflow instance.
     *
     * Each interaction or action.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioOperation
     */
    public function getOperation(): null|FHIRExampleScenarioOperation
    {
        return $this->operation ?? null;
    }

    /**
     * Example of workflow instance.
     *
     * Each interaction or action.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioOperation $operation
     * @return static
     */
    public function setOperation(null|FHIRExampleScenarioOperation $operation): self
    {
        if (null === $operation) {
            unset($this->operation);
            return $this;
        }
        $this->operation = $operation;
        return $this;
    }

    /**
     * Example of workflow instance.
     *
     * Indicates an alternative step that can be taken instead of the operations on the
     * base step in exceptional/atypical circumstances.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioAlternative>
     */
    public function getAlternative(): array
    {
        return $this->alternative ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioAlternative>
     */
    public function getAlternativeIterator(): iterable
    {
        if (!isset($this->alternative)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->alternative);
    }

    /**
     * Example of workflow instance.
     *
     * Indicates an alternative step that can be taken instead of the operations on the
     * base step in exceptional/atypical circumstances.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioAlternative $alternative
     * @return static
     */
    public function addAlternative(FHIRExampleScenarioAlternative $alternative): self
    {
        if (!isset($this->alternative)) {
            $this->alternative = [];
        }
        $this->alternative[] = $alternative;
        return $this;
    }

    /**
     * Example of workflow instance.
     *
     * Indicates an alternative step that can be taken instead of the operations on the
     * base step in exceptional/atypical circumstances.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioAlternative ...$alternative
     * @return static
     */
    public function setAlternative(FHIRExampleScenarioAlternative ...$alternative): self
    {
        if ([] === $alternative) {
            unset($this->alternative);
            return $this;
        }
        $this->alternative = $alternative;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioStep $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioStep
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRExampleScenarioStep)) {
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
            } else if (self::FIELD_PROCESS === $cen) {
                $type->addProcess(FHIRExampleScenarioProcess::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PAUSE === $cen) {
                $type->setPause(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OPERATION === $cen) {
                $type->setOperation(FHIRExampleScenarioOperation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ALTERNATIVE === $cen) {
                $type->addAlternative(FHIRExampleScenarioAlternative::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PAUSE])) {
            if (isset($type->pause)) {
                $type->pause->setValue((string)$attributes[self::FIELD_PAUSE]);
            } else {
                $type->setPause((string)$attributes[self::FIELD_PAUSE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PAUSE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->pause) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PAUSE]) {
            $xw->writeAttribute(self::FIELD_PAUSE, $this->pause->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->process)) {
            foreach ($this->process as $v) {
                $xw->startElement(self::FIELD_PROCESS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->pause)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PAUSE]
                || $this->pause->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PAUSE);
            $this->pause->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PAUSE]);
            $xw->endElement();
        }
        if (isset($this->operation)) {
            $xw->startElement(self::FIELD_OPERATION);
            $this->operation->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->alternative)) {
            foreach ($this->alternative as $v) {
                $xw->startElement(self::FIELD_ALTERNATIVE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioStep $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExampleScenario\FHIRExampleScenarioStep
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
        } else if (!($type instanceof FHIRExampleScenarioStep)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->process) || property_exists($decoded, self::FIELD_PROCESS)) {
            if (is_object($decoded->process)) {
                $vals = [$decoded->process];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROCESS, true);
            } else {
                $vals = $decoded->process;
            }
            foreach($vals as $v) {
                $type->addProcess(FHIRExampleScenarioProcess::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->pause)
            || isset($decoded->_pause)
            || property_exists($decoded, self::FIELD_PAUSE)
            || property_exists($decoded, self::FIELD_PAUSE_EXT)) {
            $v = $decoded->_pause ?? new \stdClass();
            $v->value = $decoded->pause ?? null;
            $type->setPause(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->operation) || property_exists($decoded, self::FIELD_OPERATION)) {
            if (is_array($decoded->operation)) {
                $type->setOperation(FHIRExampleScenarioOperation::jsonUnserialize(reset($decoded->operation), $config));
            } else {
                $type->setOperation(FHIRExampleScenarioOperation::jsonUnserialize($decoded->operation, $config));
            }
        }
        if (isset($decoded->alternative) || property_exists($decoded, self::FIELD_ALTERNATIVE)) {
            if (is_object($decoded->alternative)) {
                $vals = [$decoded->alternative];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ALTERNATIVE, true);
            } else {
                $vals = $decoded->alternative;
            }
            foreach($vals as $v) {
                $type->addAlternative(FHIRExampleScenarioAlternative::jsonUnserialize($v, $config));
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
        if (isset($this->process) && [] !== $this->process) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROCESS) && 1 === count($this->process)) {
                $out->process = $this->process[0];
            } else {
                $out->process = $this->process;
            }
        }
        if (isset($this->pause)) {
            if (null !== ($val = $this->pause->getValue())) {
                $out->pause = $val;
            }
            if ($this->pause->_nonValueFieldDefined()) {
                $ext = $this->pause->jsonSerialize();
                unset($ext->value);
                $out->_pause = $ext;
            }
        }
        if (isset($this->operation)) {
            $out->operation = $this->operation;
        }
        if (isset($this->alternative) && [] !== $this->alternative) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ALTERNATIVE) && 1 === count($this->alternative)) {
                $out->alternative = $this->alternative[0];
            } else {
                $out->alternative = $this->alternative;
            }
        }
        return $out;
    }
}
