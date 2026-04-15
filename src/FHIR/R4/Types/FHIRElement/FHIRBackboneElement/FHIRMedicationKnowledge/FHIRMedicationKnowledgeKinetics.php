<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Information about a medication that is used to support knowledge.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicationKnowledgeKinetics extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICATION_KNOWLEDGE_DOT_KINETICS;

    /* class_default.php:56 */
    public const FIELD_AREA_UNDER_CURVE = 'areaUnderCurve';
    public const FIELD_LETHAL_DOSE_50 = 'lethalDose50';
    public const FIELD_HALF_LIFE_PERIOD = 'halfLifePeriod';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The drug concentration measured at certain discrete points in time.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity>
     */
    #[FHIRQuantity]
    protected array $areaUnderCurve;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The median lethal dose of a drug.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity>
     */
    #[FHIRQuantity]
    protected array $lethalDose50;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The time required for any specified property (e.g., the concentration of a
     * substance in the body) to decrease by half.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $halfLifePeriod;

    /* constructor.php:61 */
    /**
     * FHIRMedicationKnowledgeKinetics Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity> $areaUnderCurve
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity> $lethalDose50
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $halfLifePeriod
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $areaUnderCurve = null,
                                null|iterable $lethalDose50 = null,
                                null|FHIRDuration $halfLifePeriod = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $areaUnderCurve) {
            $this->setAreaUnderCurve(...$areaUnderCurve);
        }
        if (null !== $lethalDose50) {
            $this->setLethalDose50(...$lethalDose50);
        }
        if (null !== $halfLifePeriod) {
            $this->setHalfLifePeriod($halfLifePeriod);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The drug concentration measured at certain discrete points in time.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity>
     */
    public function getAreaUnderCurve(): array
    {
        return $this->areaUnderCurve ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity>
     */
    public function getAreaUnderCurveIterator(): iterable
    {
        if (!isset($this->areaUnderCurve)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->areaUnderCurve);
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The drug concentration measured at certain discrete points in time.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $areaUnderCurve
     * @return static
     */
    public function addAreaUnderCurve(FHIRQuantity $areaUnderCurve): self
    {
        if (!isset($this->areaUnderCurve)) {
            $this->areaUnderCurve = [];
        }
        $this->areaUnderCurve[] = $areaUnderCurve;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The drug concentration measured at certain discrete points in time.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity ...$areaUnderCurve
     * @return static
     */
    public function setAreaUnderCurve(FHIRQuantity ...$areaUnderCurve): self
    {
        if ([] === $areaUnderCurve) {
            unset($this->areaUnderCurve);
            return $this;
        }
        $this->areaUnderCurve = $areaUnderCurve;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The median lethal dose of a drug.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity>
     */
    public function getLethalDose50(): array
    {
        return $this->lethalDose50 ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity>
     */
    public function getLethalDose50Iterator(): iterable
    {
        if (!isset($this->lethalDose50)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->lethalDose50);
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The median lethal dose of a drug.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $lethalDose50
     * @return static
     */
    public function addLethalDose50(FHIRQuantity $lethalDose50): self
    {
        if (!isset($this->lethalDose50)) {
            $this->lethalDose50 = [];
        }
        $this->lethalDose50[] = $lethalDose50;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The median lethal dose of a drug.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity ...$lethalDose50
     * @return static
     */
    public function setLethalDose50(FHIRQuantity ...$lethalDose50): self
    {
        if ([] === $lethalDose50) {
            unset($this->lethalDose50);
            return $this;
        }
        $this->lethalDose50 = $lethalDose50;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The time required for any specified property (e.g., the concentration of a
     * substance in the body) to decrease by half.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getHalfLifePeriod(): null|FHIRDuration
    {
        return $this->halfLifePeriod ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The time required for any specified property (e.g., the concentration of a
     * substance in the body) to decrease by half.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $halfLifePeriod
     * @return static
     */
    public function setHalfLifePeriod(null|FHIRDuration $halfLifePeriod): self
    {
        if (null === $halfLifePeriod) {
            unset($this->halfLifePeriod);
            return $this;
        }
        $this->halfLifePeriod = $halfLifePeriod;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicationKnowledgeKinetics)) {
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
            } else if (self::FIELD_AREA_UNDER_CURVE === $cen) {
                $type->addAreaUnderCurve(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LETHAL_DOSE_50 === $cen) {
                $type->addLethalDose50(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_HALF_LIFE_PERIOD === $cen) {
                $type->setHalfLifePeriod(FHIRDuration::xmlUnserialize($ce, $config));
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
        if (isset($this->areaUnderCurve)) {
            foreach ($this->areaUnderCurve as $v) {
                $xw->startElement(self::FIELD_AREA_UNDER_CURVE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->lethalDose50)) {
            foreach ($this->lethalDose50 as $v) {
                $xw->startElement(self::FIELD_LETHAL_DOSE_50);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->halfLifePeriod)) {
            $xw->startElement(self::FIELD_HALF_LIFE_PERIOD);
            $this->halfLifePeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeKinetics
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
        } else if (!($type instanceof FHIRMedicationKnowledgeKinetics)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->areaUnderCurve) || property_exists($decoded, self::FIELD_AREA_UNDER_CURVE)) {
            if (is_object($decoded->areaUnderCurve)) {
                $vals = [$decoded->areaUnderCurve];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_AREA_UNDER_CURVE, true);
            } else {
                $vals = $decoded->areaUnderCurve;
            }
            foreach($vals as $v) {
                $type->addAreaUnderCurve(FHIRQuantity::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->lethalDose50) || property_exists($decoded, self::FIELD_LETHAL_DOSE_50)) {
            if (is_object($decoded->lethalDose50)) {
                $vals = [$decoded->lethalDose50];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_LETHAL_DOSE_50, true);
            } else {
                $vals = $decoded->lethalDose50;
            }
            foreach($vals as $v) {
                $type->addLethalDose50(FHIRQuantity::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->halfLifePeriod) || property_exists($decoded, self::FIELD_HALF_LIFE_PERIOD)) {
            if (is_array($decoded->halfLifePeriod)) {
                $type->setHalfLifePeriod(FHIRDuration::jsonUnserialize(reset($decoded->halfLifePeriod), $config));
            } else {
                $type->setHalfLifePeriod(FHIRDuration::jsonUnserialize($decoded->halfLifePeriod, $config));
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
        if (isset($this->areaUnderCurve) && [] !== $this->areaUnderCurve) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_AREA_UNDER_CURVE) && 1 === count($this->areaUnderCurve)) {
                $out->areaUnderCurve = $this->areaUnderCurve[0];
            } else {
                $out->areaUnderCurve = $this->areaUnderCurve;
            }
        }
        if (isset($this->lethalDose50) && [] !== $this->lethalDose50) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_LETHAL_DOSE_50) && 1 === count($this->lethalDose50)) {
                $out->lethalDose50 = $this->lethalDose50[0];
            } else {
                $out->lethalDose50 = $this->lethalDose50;
            }
        }
        if (isset($this->halfLifePeriod)) {
            $out->halfLifePeriod = $this->halfLifePeriod;
        }
        return $out;
    }
}
