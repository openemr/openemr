<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Describes the intention of how one or more practitioners intend to deliver care
 * for a particular patient, group or community for a period of time, possibly
 * limited to care for a specific condition or set of conditions.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCarePlanActivity extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CARE_PLAN_DOT_ACTIVITY;

    /* class_default.php:56 */
    public const FIELD_OUTCOME_CODEABLE_CONCEPT = 'outcomeCodeableConcept';
    public const FIELD_OUTCOME_REFERENCE = 'outcomeReference';
    public const FIELD_PROGRESS = 'progress';
    public const FIELD_REFERENCE = 'reference';
    public const FIELD_DETAIL = 'detail';

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
     * Identifies the outcome at the point when the status of the activity is assessed.
     * For example, the outcome of an education activity could be patient understands
     * (or not).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $outcomeCodeableConcept;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details of the outcome or action resulting from the activity. The reference to
     * an "event" resource, such as Procedure or Encounter or Observation, is the
     * result/outcome of the activity itself. The activity can be conveyed using
     * CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference
     * to a “request” resource).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $outcomeReference;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Notes about the adherence/status/progress of the activity.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $progress;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The details of the proposed activity represented in a specific resource.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $reference;
    /**
     * Describes the intention of how one or more practitioners intend to deliver care
     * for a particular patient, group or community for a period of time, possibly
     * limited to care for a specific condition or set of conditions.
     *
     * A simple summary of a planned activity suitable for a general care plan system
     * (e.g. form driven) that doesn't know about specific resources such as procedure
     * etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanDetail
     */
    #[FHIRCarePlanDetail]
    protected FHIRCarePlanDetail $detail;

    /* constructor.php:61 */
    /**
     * FHIRCarePlanActivity Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $outcomeCodeableConcept
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $outcomeReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $progress
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $reference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanDetail $detail
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $outcomeCodeableConcept = null,
                                null|iterable $outcomeReference = null,
                                null|iterable $progress = null,
                                null|FHIRReference $reference = null,
                                null|FHIRCarePlanDetail $detail = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $outcomeCodeableConcept) {
            $this->setOutcomeCodeableConcept(...$outcomeCodeableConcept);
        }
        if (null !== $outcomeReference) {
            $this->setOutcomeReference(...$outcomeReference);
        }
        if (null !== $progress) {
            $this->setProgress(...$progress);
        }
        if (null !== $reference) {
            $this->setReference($reference);
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
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the outcome at the point when the status of the activity is assessed.
     * For example, the outcome of an education activity could be patient understands
     * (or not).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getOutcomeCodeableConcept(): array
    {
        return $this->outcomeCodeableConcept ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getOutcomeCodeableConceptIterator(): iterable
    {
        if (!isset($this->outcomeCodeableConcept)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->outcomeCodeableConcept);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the outcome at the point when the status of the activity is assessed.
     * For example, the outcome of an education activity could be patient understands
     * (or not).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $outcomeCodeableConcept
     * @return static
     */
    public function addOutcomeCodeableConcept(FHIRCodeableConcept $outcomeCodeableConcept): self
    {
        if (!isset($this->outcomeCodeableConcept)) {
            $this->outcomeCodeableConcept = [];
        }
        $this->outcomeCodeableConcept[] = $outcomeCodeableConcept;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the outcome at the point when the status of the activity is assessed.
     * For example, the outcome of an education activity could be patient understands
     * (or not).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$outcomeCodeableConcept
     * @return static
     */
    public function setOutcomeCodeableConcept(FHIRCodeableConcept ...$outcomeCodeableConcept): self
    {
        if ([] === $outcomeCodeableConcept) {
            unset($this->outcomeCodeableConcept);
            return $this;
        }
        $this->outcomeCodeableConcept = $outcomeCodeableConcept;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details of the outcome or action resulting from the activity. The reference to
     * an "event" resource, such as Procedure or Encounter or Observation, is the
     * result/outcome of the activity itself. The activity can be conveyed using
     * CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference
     * to a “request” resource).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getOutcomeReference(): array
    {
        return $this->outcomeReference ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getOutcomeReferenceIterator(): iterable
    {
        if (!isset($this->outcomeReference)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->outcomeReference);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details of the outcome or action resulting from the activity. The reference to
     * an "event" resource, such as Procedure or Encounter or Observation, is the
     * result/outcome of the activity itself. The activity can be conveyed using
     * CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference
     * to a “request” resource).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $outcomeReference
     * @return static
     */
    public function addOutcomeReference(FHIRReference $outcomeReference): self
    {
        if (!isset($this->outcomeReference)) {
            $this->outcomeReference = [];
        }
        $this->outcomeReference[] = $outcomeReference;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details of the outcome or action resulting from the activity. The reference to
     * an "event" resource, such as Procedure or Encounter or Observation, is the
     * result/outcome of the activity itself. The activity can be conveyed using
     * CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference
     * to a “request” resource).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$outcomeReference
     * @return static
     */
    public function setOutcomeReference(FHIRReference ...$outcomeReference): self
    {
        if ([] === $outcomeReference) {
            unset($this->outcomeReference);
            return $this;
        }
        $this->outcomeReference = $outcomeReference;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Notes about the adherence/status/progress of the activity.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getProgress(): array
    {
        return $this->progress ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getProgressIterator(): iterable
    {
        if (!isset($this->progress)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->progress);
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Notes about the adherence/status/progress of the activity.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation $progress
     * @return static
     */
    public function addProgress(FHIRAnnotation $progress): self
    {
        if (!isset($this->progress)) {
            $this->progress = [];
        }
        $this->progress[] = $progress;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Notes about the adherence/status/progress of the activity.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation ...$progress
     * @return static
     */
    public function setProgress(FHIRAnnotation ...$progress): self
    {
        if ([] === $progress) {
            unset($this->progress);
            return $this;
        }
        $this->progress = $progress;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The details of the proposed activity represented in a specific resource.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getReference(): null|FHIRReference
    {
        return $this->reference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The details of the proposed activity represented in a specific resource.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $reference
     * @return static
     */
    public function setReference(null|FHIRReference $reference): self
    {
        if (null === $reference) {
            unset($this->reference);
            return $this;
        }
        $this->reference = $reference;
        return $this;
    }

    /**
     * Describes the intention of how one or more practitioners intend to deliver care
     * for a particular patient, group or community for a period of time, possibly
     * limited to care for a specific condition or set of conditions.
     *
     * A simple summary of a planned activity suitable for a general care plan system
     * (e.g. form driven) that doesn't know about specific resources such as procedure
     * etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanDetail
     */
    public function getDetail(): null|FHIRCarePlanDetail
    {
        return $this->detail ?? null;
    }

    /**
     * Describes the intention of how one or more practitioners intend to deliver care
     * for a particular patient, group or community for a period of time, possibly
     * limited to care for a specific condition or set of conditions.
     *
     * A simple summary of a planned activity suitable for a general care plan system
     * (e.g. form driven) that doesn't know about specific resources such as procedure
     * etc.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanDetail $detail
     * @return static
     */
    public function setDetail(null|FHIRCarePlanDetail $detail): self
    {
        if (null === $detail) {
            unset($this->detail);
            return $this;
        }
        $this->detail = $detail;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanActivity $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanActivity
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCarePlanActivity)) {
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
            } else if (self::FIELD_OUTCOME_CODEABLE_CONCEPT === $cen) {
                $type->addOutcomeCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OUTCOME_REFERENCE === $cen) {
                $type->addOutcomeReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROGRESS === $cen) {
                $type->addProgress(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE === $cen) {
                $type->setReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DETAIL === $cen) {
                $type->setDetail(FHIRCarePlanDetail::xmlUnserialize($ce, $config));
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
        if (isset($this->outcomeCodeableConcept)) {
            foreach ($this->outcomeCodeableConcept as $v) {
                $xw->startElement(self::FIELD_OUTCOME_CODEABLE_CONCEPT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->outcomeReference)) {
            foreach ($this->outcomeReference as $v) {
                $xw->startElement(self::FIELD_OUTCOME_REFERENCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->progress)) {
            foreach ($this->progress as $v) {
                $xw->startElement(self::FIELD_PROGRESS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->reference)) {
            $xw->startElement(self::FIELD_REFERENCE);
            $this->reference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->detail)) {
            $xw->startElement(self::FIELD_DETAIL);
            $this->detail->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanActivity $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanActivity
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
        } else if (!($type instanceof FHIRCarePlanActivity)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->outcomeCodeableConcept) || property_exists($decoded, self::FIELD_OUTCOME_CODEABLE_CONCEPT)) {
            if (is_object($decoded->outcomeCodeableConcept)) {
                $vals = [$decoded->outcomeCodeableConcept];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_OUTCOME_CODEABLE_CONCEPT, true);
            } else {
                $vals = $decoded->outcomeCodeableConcept;
            }
            foreach($vals as $v) {
                $type->addOutcomeCodeableConcept(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->outcomeReference) || property_exists($decoded, self::FIELD_OUTCOME_REFERENCE)) {
            if (is_object($decoded->outcomeReference)) {
                $vals = [$decoded->outcomeReference];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_OUTCOME_REFERENCE, true);
            } else {
                $vals = $decoded->outcomeReference;
            }
            foreach($vals as $v) {
                $type->addOutcomeReference(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->progress) || property_exists($decoded, self::FIELD_PROGRESS)) {
            if (is_object($decoded->progress)) {
                $vals = [$decoded->progress];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROGRESS, true);
            } else {
                $vals = $decoded->progress;
            }
            foreach($vals as $v) {
                $type->addProgress(FHIRAnnotation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->reference) || property_exists($decoded, self::FIELD_REFERENCE)) {
            if (is_array($decoded->reference)) {
                $type->setReference(FHIRReference::jsonUnserialize(reset($decoded->reference), $config));
            } else {
                $type->setReference(FHIRReference::jsonUnserialize($decoded->reference, $config));
            }
        }
        if (isset($decoded->detail) || property_exists($decoded, self::FIELD_DETAIL)) {
            if (is_array($decoded->detail)) {
                $type->setDetail(FHIRCarePlanDetail::jsonUnserialize(reset($decoded->detail), $config));
            } else {
                $type->setDetail(FHIRCarePlanDetail::jsonUnserialize($decoded->detail, $config));
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
        if (isset($this->outcomeCodeableConcept) && [] !== $this->outcomeCodeableConcept) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_OUTCOME_CODEABLE_CONCEPT) && 1 === count($this->outcomeCodeableConcept)) {
                $out->outcomeCodeableConcept = $this->outcomeCodeableConcept[0];
            } else {
                $out->outcomeCodeableConcept = $this->outcomeCodeableConcept;
            }
        }
        if (isset($this->outcomeReference) && [] !== $this->outcomeReference) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_OUTCOME_REFERENCE) && 1 === count($this->outcomeReference)) {
                $out->outcomeReference = $this->outcomeReference[0];
            } else {
                $out->outcomeReference = $this->outcomeReference;
            }
        }
        if (isset($this->progress) && [] !== $this->progress) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROGRESS) && 1 === count($this->progress)) {
                $out->progress = $this->progress[0];
            } else {
                $out->progress = $this->progress;
            }
        }
        if (isset($this->reference)) {
            $out->reference = $this->reference;
        }
        if (isset($this->detail)) {
            $out->detail = $this->detail;
        }
        return $out;
    }
}
