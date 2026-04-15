<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The MeasureReport resource contains the results of the calculation of a measure;
 * and optionally a reference to the resources involved in that calculation.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMeasureReportGroup extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEASURE_REPORT_DOT_GROUP;

    /* class_default.php:56 */
    public const FIELD_CODE = 'code';
    public const FIELD_POPULATION = 'population';
    public const FIELD_MEASURE_SCORE = 'measureScore';
    public const FIELD_STRATIFIER = 'stratifier';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The meaning of the population group as defined in the measure definition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $code;
    /**
     * The MeasureReport resource contains the results of the calculation of a measure;
     * and optionally a reference to the resources involved in that calculation.
     *
     * The populations that make up the population group, one for each type of
     * population appropriate for the measure.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportPopulation>
     */
    #[FHIRMeasureReportPopulation]
    protected array $population;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The measure score for this population group, calculated as appropriate for the
     * measure type and scoring method, and based on the contents of the populations
     * defined in the group.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $measureScore;
    /**
     * The MeasureReport resource contains the results of the calculation of a measure;
     * and optionally a reference to the resources involved in that calculation.
     *
     * When a measure includes multiple stratifiers, there will be a stratifier group
     * for each stratifier defined by the measure.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportStratifier>
     */
    #[FHIRMeasureReportStratifier]
    protected array $stratifier;

    /* constructor.php:61 */
    /**
     * FHIRMeasureReportGroup Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportPopulation> $population
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $measureScore
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportStratifier> $stratifier
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $code = null,
                                null|iterable $population = null,
                                null|FHIRQuantity $measureScore = null,
                                null|iterable $stratifier = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $population) {
            $this->setPopulation(...$population);
        }
        if (null !== $measureScore) {
            $this->setMeasureScore($measureScore);
        }
        if (null !== $stratifier) {
            $this->setStratifier(...$stratifier);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The meaning of the population group as defined in the measure definition.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCode(): null|FHIRCodeableConcept
    {
        return $this->code ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The meaning of the population group as defined in the measure definition.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @return static
     */
    public function setCode(null|FHIRCodeableConcept $code): self
    {
        if (null === $code) {
            unset($this->code);
            return $this;
        }
        $this->code = $code;
        return $this;
    }

    /**
     * The MeasureReport resource contains the results of the calculation of a measure;
     * and optionally a reference to the resources involved in that calculation.
     *
     * The populations that make up the population group, one for each type of
     * population appropriate for the measure.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportPopulation>
     */
    public function getPopulation(): array
    {
        return $this->population ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportPopulation>
     */
    public function getPopulationIterator(): iterable
    {
        if (!isset($this->population)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->population);
    }

    /**
     * The MeasureReport resource contains the results of the calculation of a measure;
     * and optionally a reference to the resources involved in that calculation.
     *
     * The populations that make up the population group, one for each type of
     * population appropriate for the measure.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportPopulation $population
     * @return static
     */
    public function addPopulation(FHIRMeasureReportPopulation $population): self
    {
        if (!isset($this->population)) {
            $this->population = [];
        }
        $this->population[] = $population;
        return $this;
    }

    /**
     * The MeasureReport resource contains the results of the calculation of a measure;
     * and optionally a reference to the resources involved in that calculation.
     *
     * The populations that make up the population group, one for each type of
     * population appropriate for the measure.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportPopulation ...$population
     * @return static
     */
    public function setPopulation(FHIRMeasureReportPopulation ...$population): self
    {
        if ([] === $population) {
            unset($this->population);
            return $this;
        }
        $this->population = $population;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The measure score for this population group, calculated as appropriate for the
     * measure type and scoring method, and based on the contents of the populations
     * defined in the group.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getMeasureScore(): null|FHIRQuantity
    {
        return $this->measureScore ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The measure score for this population group, calculated as appropriate for the
     * measure type and scoring method, and based on the contents of the populations
     * defined in the group.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $measureScore
     * @return static
     */
    public function setMeasureScore(null|FHIRQuantity $measureScore): self
    {
        if (null === $measureScore) {
            unset($this->measureScore);
            return $this;
        }
        $this->measureScore = $measureScore;
        return $this;
    }

    /**
     * The MeasureReport resource contains the results of the calculation of a measure;
     * and optionally a reference to the resources involved in that calculation.
     *
     * When a measure includes multiple stratifiers, there will be a stratifier group
     * for each stratifier defined by the measure.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportStratifier>
     */
    public function getStratifier(): array
    {
        return $this->stratifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportStratifier>
     */
    public function getStratifierIterator(): iterable
    {
        if (!isset($this->stratifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->stratifier);
    }

    /**
     * The MeasureReport resource contains the results of the calculation of a measure;
     * and optionally a reference to the resources involved in that calculation.
     *
     * When a measure includes multiple stratifiers, there will be a stratifier group
     * for each stratifier defined by the measure.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportStratifier $stratifier
     * @return static
     */
    public function addStratifier(FHIRMeasureReportStratifier $stratifier): self
    {
        if (!isset($this->stratifier)) {
            $this->stratifier = [];
        }
        $this->stratifier[] = $stratifier;
        return $this;
    }

    /**
     * The MeasureReport resource contains the results of the calculation of a measure;
     * and optionally a reference to the resources involved in that calculation.
     *
     * When a measure includes multiple stratifiers, there will be a stratifier group
     * for each stratifier defined by the measure.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportStratifier ...$stratifier
     * @return static
     */
    public function setStratifier(FHIRMeasureReportStratifier ...$stratifier): self
    {
        if ([] === $stratifier) {
            unset($this->stratifier);
            return $this;
        }
        $this->stratifier = $stratifier;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportGroup $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportGroup
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMeasureReportGroup)) {
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
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_POPULATION === $cen) {
                $type->addPopulation(FHIRMeasureReportPopulation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MEASURE_SCORE === $cen) {
                $type->setMeasureScore(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STRATIFIER === $cen) {
                $type->addStratifier(FHIRMeasureReportStratifier::xmlUnserialize($ce, $config));
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
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->population)) {
            foreach ($this->population as $v) {
                $xw->startElement(self::FIELD_POPULATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->measureScore)) {
            $xw->startElement(self::FIELD_MEASURE_SCORE);
            $this->measureScore->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->stratifier)) {
            foreach ($this->stratifier as $v) {
                $xw->startElement(self::FIELD_STRATIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportGroup $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMeasureReport\FHIRMeasureReportGroup
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
        } else if (!($type instanceof FHIRMeasureReportGroup)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
            }
        }
        if (isset($decoded->population) || property_exists($decoded, self::FIELD_POPULATION)) {
            if (is_object($decoded->population)) {
                $vals = [$decoded->population];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_POPULATION, true);
            } else {
                $vals = $decoded->population;
            }
            foreach($vals as $v) {
                $type->addPopulation(FHIRMeasureReportPopulation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->measureScore) || property_exists($decoded, self::FIELD_MEASURE_SCORE)) {
            if (is_array($decoded->measureScore)) {
                $type->setMeasureScore(FHIRQuantity::jsonUnserialize(reset($decoded->measureScore), $config));
            } else {
                $type->setMeasureScore(FHIRQuantity::jsonUnserialize($decoded->measureScore, $config));
            }
        }
        if (isset($decoded->stratifier) || property_exists($decoded, self::FIELD_STRATIFIER)) {
            if (is_object($decoded->stratifier)) {
                $vals = [$decoded->stratifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_STRATIFIER, true);
            } else {
                $vals = $decoded->stratifier;
            }
            foreach($vals as $v) {
                $type->addStratifier(FHIRMeasureReportStratifier::jsonUnserialize($v, $config));
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
        if (isset($this->code)) {
            $out->code = $this->code;
        }
        if (isset($this->population) && [] !== $this->population) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_POPULATION) && 1 === count($this->population)) {
                $out->population = $this->population[0];
            } else {
                $out->population = $this->population;
            }
        }
        if (isset($this->measureScore)) {
            $out->measureScore = $this->measureScore;
        }
        if (isset($this->stratifier) && [] !== $this->stratifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_STRATIFIER) && 1 === count($this->stratifier)) {
                $out->stratifier = $this->stratifier[0];
            } else {
                $out->stratifier = $this->stratifier;
            }
        }
        return $out;
    }
}
