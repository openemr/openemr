<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentReference;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A reference to a document of any kind for any purpose. Provides metadata about
 * the document so that the document can be discovered and managed. The scope of a
 * document is any seralized object with a mime-type, so includes formal patient
 * centric documents (CDA), cliical notes, scanned paper, and non-patient specific
 * documents like policy text.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRDocumentReferenceContext extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_DOCUMENT_REFERENCE_DOT_CONTEXT;

    /* class_default.php:56 */
    public const FIELD_ENCOUNTER = 'encounter';
    public const FIELD_EVENT = 'event';
    public const FIELD_PERIOD = 'period';
    public const FIELD_FACILITY_TYPE = 'facilityType';
    public const FIELD_PRACTICE_SETTING = 'practiceSetting';
    public const FIELD_SOURCE_PATIENT_INFO = 'sourcePatientInfo';
    public const FIELD_RELATED = 'related';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
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
     * Describes the clinical encounter or type of care that the document content is
     * associated with.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $encounter;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This list of codes represents the main clinical acts, such as a colonoscopy or
     * an appendectomy, being documented. In some cases, the event is inherent in the
     * type Code, such as a "History and Physical Report" in which the procedure being
     * documented is necessarily a "History and Physical" act.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $event;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The time period over which the service that is described by the document was
     * provided.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $period;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of facility where the patient was seen.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $facilityType;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This property may convey specifics about the practice setting where the content
     * was created, often reflecting the clinical specialty.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $practiceSetting;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Patient Information as known when the document was published. May be a
     * reference to a version specific, or contained.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $sourcePatientInfo;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Related identifiers or resources associated with the DocumentReference.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $related;

    /* constructor.php:61 */
    /**
     * FHIRDocumentReferenceContext Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $encounter
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $event
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $facilityType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $practiceSetting
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $sourcePatientInfo
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $related
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $encounter = null,
                                null|iterable $event = null,
                                null|FHIRPeriod $period = null,
                                null|FHIRCodeableConcept $facilityType = null,
                                null|FHIRCodeableConcept $practiceSetting = null,
                                null|FHIRReference $sourcePatientInfo = null,
                                null|iterable $related = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $encounter) {
            $this->setEncounter(...$encounter);
        }
        if (null !== $event) {
            $this->setEvent(...$event);
        }
        if (null !== $period) {
            $this->setPeriod($period);
        }
        if (null !== $facilityType) {
            $this->setFacilityType($facilityType);
        }
        if (null !== $practiceSetting) {
            $this->setPracticeSetting($practiceSetting);
        }
        if (null !== $sourcePatientInfo) {
            $this->setSourcePatientInfo($sourcePatientInfo);
        }
        if (null !== $related) {
            $this->setRelated(...$related);
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
     * Describes the clinical encounter or type of care that the document content is
     * associated with.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEncounter(): array
    {
        return $this->encounter ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEncounterIterator(): iterable
    {
        if (!isset($this->encounter)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->encounter);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the clinical encounter or type of care that the document content is
     * associated with.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @return static
     */
    public function addEncounter(FHIRReference $encounter): self
    {
        if (!isset($this->encounter)) {
            $this->encounter = [];
        }
        $this->encounter[] = $encounter;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the clinical encounter or type of care that the document content is
     * associated with.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$encounter
     * @return static
     */
    public function setEncounter(FHIRReference ...$encounter): self
    {
        if ([] === $encounter) {
            unset($this->encounter);
            return $this;
        }
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This list of codes represents the main clinical acts, such as a colonoscopy or
     * an appendectomy, being documented. In some cases, the event is inherent in the
     * type Code, such as a "History and Physical Report" in which the procedure being
     * documented is necessarily a "History and Physical" act.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getEvent(): array
    {
        return $this->event ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getEventIterator(): iterable
    {
        if (!isset($this->event)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->event);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This list of codes represents the main clinical acts, such as a colonoscopy or
     * an appendectomy, being documented. In some cases, the event is inherent in the
     * type Code, such as a "History and Physical Report" in which the procedure being
     * documented is necessarily a "History and Physical" act.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $event
     * @return static
     */
    public function addEvent(FHIRCodeableConcept $event): self
    {
        if (!isset($this->event)) {
            $this->event = [];
        }
        $this->event[] = $event;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This list of codes represents the main clinical acts, such as a colonoscopy or
     * an appendectomy, being documented. In some cases, the event is inherent in the
     * type Code, such as a "History and Physical Report" in which the procedure being
     * documented is necessarily a "History and Physical" act.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$event
     * @return static
     */
    public function setEvent(FHIRCodeableConcept ...$event): self
    {
        if ([] === $event) {
            unset($this->event);
            return $this;
        }
        $this->event = $event;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The time period over which the service that is described by the document was
     * provided.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getPeriod(): null|FHIRPeriod
    {
        return $this->period ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The time period over which the service that is described by the document was
     * provided.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @return static
     */
    public function setPeriod(null|FHIRPeriod $period): self
    {
        if (null === $period) {
            unset($this->period);
            return $this;
        }
        $this->period = $period;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of facility where the patient was seen.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getFacilityType(): null|FHIRCodeableConcept
    {
        return $this->facilityType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of facility where the patient was seen.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $facilityType
     * @return static
     */
    public function setFacilityType(null|FHIRCodeableConcept $facilityType): self
    {
        if (null === $facilityType) {
            unset($this->facilityType);
            return $this;
        }
        $this->facilityType = $facilityType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This property may convey specifics about the practice setting where the content
     * was created, often reflecting the clinical specialty.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getPracticeSetting(): null|FHIRCodeableConcept
    {
        return $this->practiceSetting ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This property may convey specifics about the practice setting where the content
     * was created, often reflecting the clinical specialty.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $practiceSetting
     * @return static
     */
    public function setPracticeSetting(null|FHIRCodeableConcept $practiceSetting): self
    {
        if (null === $practiceSetting) {
            unset($this->practiceSetting);
            return $this;
        }
        $this->practiceSetting = $practiceSetting;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Patient Information as known when the document was published. May be a
     * reference to a version specific, or contained.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSourcePatientInfo(): null|FHIRReference
    {
        return $this->sourcePatientInfo ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Patient Information as known when the document was published. May be a
     * reference to a version specific, or contained.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $sourcePatientInfo
     * @return static
     */
    public function setSourcePatientInfo(null|FHIRReference $sourcePatientInfo): self
    {
        if (null === $sourcePatientInfo) {
            unset($this->sourcePatientInfo);
            return $this;
        }
        $this->sourcePatientInfo = $sourcePatientInfo;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Related identifiers or resources associated with the DocumentReference.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRelated(): array
    {
        return $this->related ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRelatedIterator(): iterable
    {
        if (!isset($this->related)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->related);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Related identifiers or resources associated with the DocumentReference.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $related
     * @return static
     */
    public function addRelated(FHIRReference $related): self
    {
        if (!isset($this->related)) {
            $this->related = [];
        }
        $this->related[] = $related;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Related identifiers or resources associated with the DocumentReference.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$related
     * @return static
     */
    public function setRelated(FHIRReference ...$related): self
    {
        if ([] === $related) {
            unset($this->related);
            return $this;
        }
        $this->related = $related;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentReference\FHIRDocumentReferenceContext $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentReference\FHIRDocumentReferenceContext
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRDocumentReferenceContext)) {
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
            } else if (self::FIELD_ENCOUNTER === $cen) {
                $type->addEncounter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EVENT === $cen) {
                $type->addEvent(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD === $cen) {
                $type->setPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FACILITY_TYPE === $cen) {
                $type->setFacilityType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRACTICE_SETTING === $cen) {
                $type->setPracticeSetting(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_PATIENT_INFO === $cen) {
                $type->setSourcePatientInfo(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATED === $cen) {
                $type->addRelated(FHIRReference::xmlUnserialize($ce, $config));
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
        if (isset($this->encounter)) {
            foreach ($this->encounter as $v) {
                $xw->startElement(self::FIELD_ENCOUNTER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->event)) {
            foreach ($this->event as $v) {
                $xw->startElement(self::FIELD_EVENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->period)) {
            $xw->startElement(self::FIELD_PERIOD);
            $this->period->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->facilityType)) {
            $xw->startElement(self::FIELD_FACILITY_TYPE);
            $this->facilityType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->practiceSetting)) {
            $xw->startElement(self::FIELD_PRACTICE_SETTING);
            $this->practiceSetting->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->sourcePatientInfo)) {
            $xw->startElement(self::FIELD_SOURCE_PATIENT_INFO);
            $this->sourcePatientInfo->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->related)) {
            foreach ($this->related as $v) {
                $xw->startElement(self::FIELD_RELATED);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentReference\FHIRDocumentReferenceContext $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDocumentReference\FHIRDocumentReferenceContext
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
        } else if (!($type instanceof FHIRDocumentReferenceContext)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->encounter) || property_exists($decoded, self::FIELD_ENCOUNTER)) {
            if (is_object($decoded->encounter)) {
                $vals = [$decoded->encounter];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ENCOUNTER, true);
            } else {
                $vals = $decoded->encounter;
            }
            foreach($vals as $v) {
                $type->addEncounter(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->event) || property_exists($decoded, self::FIELD_EVENT)) {
            if (is_object($decoded->event)) {
                $vals = [$decoded->event];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_EVENT, true);
            } else {
                $vals = $decoded->event;
            }
            foreach($vals as $v) {
                $type->addEvent(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->period) || property_exists($decoded, self::FIELD_PERIOD)) {
            if (is_array($decoded->period)) {
                $type->setPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->period), $config));
            } else {
                $type->setPeriod(FHIRPeriod::jsonUnserialize($decoded->period, $config));
            }
        }
        if (isset($decoded->facilityType) || property_exists($decoded, self::FIELD_FACILITY_TYPE)) {
            if (is_array($decoded->facilityType)) {
                $type->setFacilityType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->facilityType), $config));
            } else {
                $type->setFacilityType(FHIRCodeableConcept::jsonUnserialize($decoded->facilityType, $config));
            }
        }
        if (isset($decoded->practiceSetting) || property_exists($decoded, self::FIELD_PRACTICE_SETTING)) {
            if (is_array($decoded->practiceSetting)) {
                $type->setPracticeSetting(FHIRCodeableConcept::jsonUnserialize(reset($decoded->practiceSetting), $config));
            } else {
                $type->setPracticeSetting(FHIRCodeableConcept::jsonUnserialize($decoded->practiceSetting, $config));
            }
        }
        if (isset($decoded->sourcePatientInfo) || property_exists($decoded, self::FIELD_SOURCE_PATIENT_INFO)) {
            if (is_array($decoded->sourcePatientInfo)) {
                $type->setSourcePatientInfo(FHIRReference::jsonUnserialize(reset($decoded->sourcePatientInfo), $config));
            } else {
                $type->setSourcePatientInfo(FHIRReference::jsonUnserialize($decoded->sourcePatientInfo, $config));
            }
        }
        if (isset($decoded->related) || property_exists($decoded, self::FIELD_RELATED)) {
            if (is_object($decoded->related)) {
                $vals = [$decoded->related];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RELATED, true);
            } else {
                $vals = $decoded->related;
            }
            foreach($vals as $v) {
                $type->addRelated(FHIRReference::jsonUnserialize($v, $config));
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
        if (isset($this->encounter) && [] !== $this->encounter) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ENCOUNTER) && 1 === count($this->encounter)) {
                $out->encounter = $this->encounter[0];
            } else {
                $out->encounter = $this->encounter;
            }
        }
        if (isset($this->event) && [] !== $this->event) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_EVENT) && 1 === count($this->event)) {
                $out->event = $this->event[0];
            } else {
                $out->event = $this->event;
            }
        }
        if (isset($this->period)) {
            $out->period = $this->period;
        }
        if (isset($this->facilityType)) {
            $out->facilityType = $this->facilityType;
        }
        if (isset($this->practiceSetting)) {
            $out->practiceSetting = $this->practiceSetting;
        }
        if (isset($this->sourcePatientInfo)) {
            $out->sourcePatientInfo = $this->sourcePatientInfo;
        }
        if (isset($this->related) && [] !== $this->related) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELATED) && 1 === count($this->related)) {
                $out->related = $this->related[0];
            } else {
                $out->related = $this->related;
            }
        }
        return $out;
    }
}
