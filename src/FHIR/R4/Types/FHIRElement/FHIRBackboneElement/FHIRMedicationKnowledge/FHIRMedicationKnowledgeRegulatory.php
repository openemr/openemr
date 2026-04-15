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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Information about a medication that is used to support knowledge.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicationKnowledgeRegulatory extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICATION_KNOWLEDGE_DOT_REGULATORY;

    /* class_default.php:56 */
    public const FIELD_REGULATORY_AUTHORITY = 'regulatoryAuthority';
    public const FIELD_SUBSTITUTION = 'substitution';
    public const FIELD_SCHEDULE = 'schedule';
    public const FIELD_MAX_DISPENSE = 'maxDispense';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_REGULATORY_AUTHORITY => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The authority that is specifying the regulations.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $regulatoryAuthority;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies if changes are allowed when dispensing a medication from a regulatory
     * perspective.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSubstitution>
     */
    #[FHIRMedicationKnowledgeSubstitution]
    protected array $substitution;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies the schedule of a medication in jurisdiction.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSchedule>
     */
    #[FHIRMedicationKnowledgeSchedule]
    protected array $schedule;
    /**
     * Information about a medication that is used to support knowledge.
     *
     * The maximum number of units of the medication that can be dispensed in a period.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMaxDispense
     */
    #[FHIRMedicationKnowledgeMaxDispense]
    protected FHIRMedicationKnowledgeMaxDispense $maxDispense;

    /* constructor.php:61 */
    /**
     * FHIRMedicationKnowledgeRegulatory Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $regulatoryAuthority
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSubstitution> $substitution
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSchedule> $schedule
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMaxDispense $maxDispense
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRReference $regulatoryAuthority = null,
                                null|iterable $substitution = null,
                                null|iterable $schedule = null,
                                null|FHIRMedicationKnowledgeMaxDispense $maxDispense = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $regulatoryAuthority) {
            $this->setRegulatoryAuthority($regulatoryAuthority);
        }
        if (null !== $substitution) {
            $this->setSubstitution(...$substitution);
        }
        if (null !== $schedule) {
            $this->setSchedule(...$schedule);
        }
        if (null !== $maxDispense) {
            $this->setMaxDispense($maxDispense);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The authority that is specifying the regulations.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getRegulatoryAuthority(): null|FHIRReference
    {
        return $this->regulatoryAuthority ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The authority that is specifying the regulations.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $regulatoryAuthority
     * @return static
     */
    public function setRegulatoryAuthority(null|FHIRReference $regulatoryAuthority): self
    {
        if (null === $regulatoryAuthority) {
            unset($this->regulatoryAuthority);
            return $this;
        }
        $this->regulatoryAuthority = $regulatoryAuthority;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies if changes are allowed when dispensing a medication from a regulatory
     * perspective.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSubstitution>
     */
    public function getSubstitution(): array
    {
        return $this->substitution ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSubstitution>
     */
    public function getSubstitutionIterator(): iterable
    {
        if (!isset($this->substitution)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->substitution);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies if changes are allowed when dispensing a medication from a regulatory
     * perspective.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSubstitution $substitution
     * @return static
     */
    public function addSubstitution(FHIRMedicationKnowledgeSubstitution $substitution): self
    {
        if (!isset($this->substitution)) {
            $this->substitution = [];
        }
        $this->substitution[] = $substitution;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies if changes are allowed when dispensing a medication from a regulatory
     * perspective.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSubstitution ...$substitution
     * @return static
     */
    public function setSubstitution(FHIRMedicationKnowledgeSubstitution ...$substitution): self
    {
        if ([] === $substitution) {
            unset($this->substitution);
            return $this;
        }
        $this->substitution = $substitution;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies the schedule of a medication in jurisdiction.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSchedule>
     */
    public function getSchedule(): array
    {
        return $this->schedule ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSchedule>
     */
    public function getScheduleIterator(): iterable
    {
        if (!isset($this->schedule)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->schedule);
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies the schedule of a medication in jurisdiction.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSchedule $schedule
     * @return static
     */
    public function addSchedule(FHIRMedicationKnowledgeSchedule $schedule): self
    {
        if (!isset($this->schedule)) {
            $this->schedule = [];
        }
        $this->schedule[] = $schedule;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * Specifies the schedule of a medication in jurisdiction.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSchedule ...$schedule
     * @return static
     */
    public function setSchedule(FHIRMedicationKnowledgeSchedule ...$schedule): self
    {
        if ([] === $schedule) {
            unset($this->schedule);
            return $this;
        }
        $this->schedule = $schedule;
        return $this;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The maximum number of units of the medication that can be dispensed in a period.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMaxDispense
     */
    public function getMaxDispense(): null|FHIRMedicationKnowledgeMaxDispense
    {
        return $this->maxDispense ?? null;
    }

    /**
     * Information about a medication that is used to support knowledge.
     *
     * The maximum number of units of the medication that can be dispensed in a period.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMaxDispense $maxDispense
     * @return static
     */
    public function setMaxDispense(null|FHIRMedicationKnowledgeMaxDispense $maxDispense): self
    {
        if (null === $maxDispense) {
            unset($this->maxDispense);
            return $this;
        }
        $this->maxDispense = $maxDispense;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicationKnowledgeRegulatory)) {
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
            } else if (self::FIELD_REGULATORY_AUTHORITY === $cen) {
                $type->setRegulatoryAuthority(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBSTITUTION === $cen) {
                $type->addSubstitution(FHIRMedicationKnowledgeSubstitution::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SCHEDULE === $cen) {
                $type->addSchedule(FHIRMedicationKnowledgeSchedule::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MAX_DISPENSE === $cen) {
                $type->setMaxDispense(FHIRMedicationKnowledgeMaxDispense::xmlUnserialize($ce, $config));
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
        if (isset($this->regulatoryAuthority)) {
            $xw->startElement(self::FIELD_REGULATORY_AUTHORITY);
            $this->regulatoryAuthority->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->substitution)) {
            foreach ($this->substitution as $v) {
                $xw->startElement(self::FIELD_SUBSTITUTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->schedule)) {
            foreach ($this->schedule as $v) {
                $xw->startElement(self::FIELD_SCHEDULE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->maxDispense)) {
            $xw->startElement(self::FIELD_MAX_DISPENSE);
            $this->maxDispense->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationKnowledge\FHIRMedicationKnowledgeRegulatory
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
        } else if (!($type instanceof FHIRMedicationKnowledgeRegulatory)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->regulatoryAuthority) || property_exists($decoded, self::FIELD_REGULATORY_AUTHORITY)) {
            if (is_array($decoded->regulatoryAuthority)) {
                $type->setRegulatoryAuthority(FHIRReference::jsonUnserialize(reset($decoded->regulatoryAuthority), $config));
            } else {
                $type->setRegulatoryAuthority(FHIRReference::jsonUnserialize($decoded->regulatoryAuthority, $config));
            }
        }
        if (isset($decoded->substitution) || property_exists($decoded, self::FIELD_SUBSTITUTION)) {
            if (is_object($decoded->substitution)) {
                $vals = [$decoded->substitution];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUBSTITUTION, true);
            } else {
                $vals = $decoded->substitution;
            }
            foreach($vals as $v) {
                $type->addSubstitution(FHIRMedicationKnowledgeSubstitution::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->schedule) || property_exists($decoded, self::FIELD_SCHEDULE)) {
            if (is_object($decoded->schedule)) {
                $vals = [$decoded->schedule];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SCHEDULE, true);
            } else {
                $vals = $decoded->schedule;
            }
            foreach($vals as $v) {
                $type->addSchedule(FHIRMedicationKnowledgeSchedule::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->maxDispense) || property_exists($decoded, self::FIELD_MAX_DISPENSE)) {
            if (is_array($decoded->maxDispense)) {
                $type->setMaxDispense(FHIRMedicationKnowledgeMaxDispense::jsonUnserialize(reset($decoded->maxDispense), $config));
            } else {
                $type->setMaxDispense(FHIRMedicationKnowledgeMaxDispense::jsonUnserialize($decoded->maxDispense, $config));
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
        if (isset($this->regulatoryAuthority)) {
            $out->regulatoryAuthority = $this->regulatoryAuthority;
        }
        if (isset($this->substitution) && [] !== $this->substitution) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUBSTITUTION) && 1 === count($this->substitution)) {
                $out->substitution = $this->substitution[0];
            } else {
                $out->substitution = $this->substitution;
            }
        }
        if (isset($this->schedule) && [] !== $this->schedule) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SCHEDULE) && 1 === count($this->schedule)) {
                $out->schedule = $this->schedule[0];
            } else {
                $out->schedule = $this->schedule;
            }
        }
        if (isset($this->maxDispense)) {
            $out->maxDispense = $this->maxDispense;
        }
        return $out;
    }
}
