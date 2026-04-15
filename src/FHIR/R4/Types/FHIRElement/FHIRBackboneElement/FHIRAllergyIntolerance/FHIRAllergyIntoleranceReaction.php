<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAllergyIntolerance;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAllergyIntoleranceSeverityList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAllergyIntoleranceSeverity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Risk of harmful or undesirable, physiological response which is unique to an
 * individual and associated with exposure to a substance.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRAllergyIntoleranceReaction extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_ALLERGY_INTOLERANCE_DOT_REACTION;

    /* class_default.php:56 */
    public const FIELD_SUBSTANCE = 'substance';
    public const FIELD_MANIFESTATION = 'manifestation';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_ONSET = 'onset';
    public const FIELD_ONSET_EXT = '_onset';
    public const FIELD_SEVERITY = 'severity';
    public const FIELD_SEVERITY_EXT = '_severity';
    public const FIELD_EXPOSURE_ROUTE = 'exposureRoute';
    public const FIELD_NOTE = 'note';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_MANIFESTATION => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ONSET => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SEVERITY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identification of the specific substance (or pharmaceutical product) considered
     * to be responsible for the Adverse Reaction event. Note: the substance for a
     * specific reaction may be different from the substance identified as the cause of
     * the risk, but it must be consistent with it. For instance, it may be a more
     * specific substance (e.g. a brand medication) or a composite product that
     * includes the identified substance. It must be clinically safe to only process
     * the 'code' and ignore the 'reaction.substance'. If a receiving system is unable
     * to confirm that AllergyIntolerance.reaction.substance falls within the semantic
     * scope of AllergyIntolerance.code, then the receiving system should ignore
     * AllergyIntolerance.reaction.substance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $substance;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical symptoms and/or signs that are observed or associated with the adverse
     * reaction event.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $manifestation;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Text description about the reaction as a whole, including details of the
     * manifestation if required.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Record of the date and/or time of the onset of the Reaction.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $onset;
    /**
     * Clinical assessment of the severity of a reaction event as a whole, potentially
     * considering multiple different manifestations.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Clinical assessment of the severity of the reaction event as a whole,
     * potentially considering multiple different manifestations.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAllergyIntoleranceSeverity
     */
    #[FHIRAllergyIntoleranceSeverity]
    protected FHIRAllergyIntoleranceSeverity $severity;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identification of the route by which the subject was exposed to the substance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $exposureRoute;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional text about the adverse reaction event not captured in other fields.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;

    /* constructor.php:61 */
    /**
     * FHIRAllergyIntoleranceReaction Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $substance
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $manifestation
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $onset
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAllergyIntoleranceSeverityList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAllergyIntoleranceSeverity $severity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $exposureRoute
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $substance = null,
                                null|iterable $manifestation = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $onset = null,
                                null|string|FHIRAllergyIntoleranceSeverityList|FHIRAllergyIntoleranceSeverity $severity = null,
                                null|FHIRCodeableConcept $exposureRoute = null,
                                null|iterable $note = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $substance) {
            $this->setSubstance($substance);
        }
        if (null !== $manifestation) {
            $this->setManifestation(...$manifestation);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $onset) {
            $this->setOnset($onset);
        }
        if (null !== $severity) {
            $this->setSeverity($severity);
        }
        if (null !== $exposureRoute) {
            $this->setExposureRoute($exposureRoute);
        }
        if (null !== $note) {
            $this->setNote(...$note);
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
     * Identification of the specific substance (or pharmaceutical product) considered
     * to be responsible for the Adverse Reaction event. Note: the substance for a
     * specific reaction may be different from the substance identified as the cause of
     * the risk, but it must be consistent with it. For instance, it may be a more
     * specific substance (e.g. a brand medication) or a composite product that
     * includes the identified substance. It must be clinically safe to only process
     * the 'code' and ignore the 'reaction.substance'. If a receiving system is unable
     * to confirm that AllergyIntolerance.reaction.substance falls within the semantic
     * scope of AllergyIntolerance.code, then the receiving system should ignore
     * AllergyIntolerance.reaction.substance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSubstance(): null|FHIRCodeableConcept
    {
        return $this->substance ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identification of the specific substance (or pharmaceutical product) considered
     * to be responsible for the Adverse Reaction event. Note: the substance for a
     * specific reaction may be different from the substance identified as the cause of
     * the risk, but it must be consistent with it. For instance, it may be a more
     * specific substance (e.g. a brand medication) or a composite product that
     * includes the identified substance. It must be clinically safe to only process
     * the 'code' and ignore the 'reaction.substance'. If a receiving system is unable
     * to confirm that AllergyIntolerance.reaction.substance falls within the semantic
     * scope of AllergyIntolerance.code, then the receiving system should ignore
     * AllergyIntolerance.reaction.substance.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $substance
     * @return static
     */
    public function setSubstance(null|FHIRCodeableConcept $substance): self
    {
        if (null === $substance) {
            unset($this->substance);
            return $this;
        }
        $this->substance = $substance;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical symptoms and/or signs that are observed or associated with the adverse
     * reaction event.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getManifestation(): array
    {
        return $this->manifestation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getManifestationIterator(): iterable
    {
        if (!isset($this->manifestation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->manifestation);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical symptoms and/or signs that are observed or associated with the adverse
     * reaction event.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $manifestation
     * @return static
     */
    public function addManifestation(FHIRCodeableConcept $manifestation): self
    {
        if (!isset($this->manifestation)) {
            $this->manifestation = [];
        }
        $this->manifestation[] = $manifestation;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical symptoms and/or signs that are observed or associated with the adverse
     * reaction event.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$manifestation
     * @return static
     */
    public function setManifestation(FHIRCodeableConcept ...$manifestation): self
    {
        if ([] === $manifestation) {
            unset($this->manifestation);
            return $this;
        }
        $this->manifestation = $manifestation;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Text description about the reaction as a whole, including details of the
     * manifestation if required.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDescription(): null|FHIRString
    {
        return $this->description ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Text description about the reaction as a whole, including details of the
     * manifestation if required.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @return static
     */
    public function setDescription(null|string|FHIRStringPrimitive|FHIRString $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRString)) {
            $description = new FHIRString(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Record of the date and/or time of the onset of the Reaction.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getOnset(): null|FHIRDateTime
    {
        return $this->onset ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Record of the date and/or time of the onset of the Reaction.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $onset
     * @return static
     */
    public function setOnset(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $onset): self
    {
        if (null === $onset) {
            unset($this->onset);
            return $this;
        }
        if (!($onset instanceof FHIRDateTime)) {
            $onset = new FHIRDateTime(value: $onset);
        }
        $this->onset = $onset;
        return $this;
    }

    /**
     * Clinical assessment of the severity of a reaction event as a whole, potentially
     * considering multiple different manifestations.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Clinical assessment of the severity of the reaction event as a whole,
     * potentially considering multiple different manifestations.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAllergyIntoleranceSeverity
     */
    public function getSeverity(): null|FHIRAllergyIntoleranceSeverity
    {
        return $this->severity ?? null;
    }

    /**
     * Clinical assessment of the severity of a reaction event as a whole, potentially
     * considering multiple different manifestations.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Clinical assessment of the severity of the reaction event as a whole,
     * potentially considering multiple different manifestations.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAllergyIntoleranceSeverityList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAllergyIntoleranceSeverity $severity
     * @return static
     */
    public function setSeverity(null|string|FHIRAllergyIntoleranceSeverityList|FHIRAllergyIntoleranceSeverity $severity): self
    {
        if (null === $severity) {
            unset($this->severity);
            return $this;
        }
        if (!($severity instanceof FHIRAllergyIntoleranceSeverity)) {
            $severity = new FHIRAllergyIntoleranceSeverity(value: $severity);
        }
        $this->severity = $severity;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identification of the route by which the subject was exposed to the substance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getExposureRoute(): null|FHIRCodeableConcept
    {
        return $this->exposureRoute ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identification of the route by which the subject was exposed to the substance.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $exposureRoute
     * @return static
     */
    public function setExposureRoute(null|FHIRCodeableConcept $exposureRoute): self
    {
        if (null === $exposureRoute) {
            unset($this->exposureRoute);
            return $this;
        }
        $this->exposureRoute = $exposureRoute;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional text about the adverse reaction event not captured in other fields.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNote(): array
    {
        return $this->note ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNoteIterator(): iterable
    {
        if (!isset($this->note)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->note);
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional text about the adverse reaction event not captured in other fields.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation $note
     * @return static
     */
    public function addNote(FHIRAnnotation $note): self
    {
        if (!isset($this->note)) {
            $this->note = [];
        }
        $this->note[] = $note;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional text about the adverse reaction event not captured in other fields.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation ...$note
     * @return static
     */
    public function setNote(FHIRAnnotation ...$note): self
    {
        if ([] === $note) {
            unset($this->note);
            return $this;
        }
        $this->note = $note;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAllergyIntolerance\FHIRAllergyIntoleranceReaction $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAllergyIntolerance\FHIRAllergyIntoleranceReaction
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRAllergyIntoleranceReaction)) {
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
            } else if (self::FIELD_SUBSTANCE === $cen) {
                $type->setSubstance(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANIFESTATION === $cen) {
                $type->addManifestation(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ONSET === $cen) {
                $type->setOnset(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEVERITY === $cen) {
                $type->setSeverity(FHIRAllergyIntoleranceSeverity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXPOSURE_ROUTE === $cen) {
                $type->setExposureRoute(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ONSET])) {
            if (isset($type->onset)) {
                $type->onset->setValue((string)$attributes[self::FIELD_ONSET]);
            } else {
                $type->setOnset((string)$attributes[self::FIELD_ONSET]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ONSET, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SEVERITY])) {
            if (isset($type->severity)) {
                $type->severity->setValue((string)$attributes[self::FIELD_SEVERITY]);
            } else {
                $type->setSeverity((string)$attributes[self::FIELD_SEVERITY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SEVERITY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->onset) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ONSET]) {
            $xw->writeAttribute(self::FIELD_ONSET, $this->onset->_getValueAsString());
        }
        if (isset($this->severity) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SEVERITY]) {
            $xw->writeAttribute(self::FIELD_SEVERITY, $this->severity->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->substance)) {
            $xw->startElement(self::FIELD_SUBSTANCE);
            $this->substance->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->manifestation)) {
            foreach ($this->manifestation as $v) {
                $xw->startElement(self::FIELD_MANIFESTATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->onset)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ONSET]
                || $this->onset->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ONSET);
            $this->onset->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ONSET]);
            $xw->endElement();
        }
        if (isset($this->severity)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SEVERITY]
                || $this->severity->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SEVERITY);
            $this->severity->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SEVERITY]);
            $xw->endElement();
        }
        if (isset($this->exposureRoute)) {
            $xw->startElement(self::FIELD_EXPOSURE_ROUTE);
            $this->exposureRoute->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->note)) {
            foreach ($this->note as $v) {
                $xw->startElement(self::FIELD_NOTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAllergyIntolerance\FHIRAllergyIntoleranceReaction $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAllergyIntolerance\FHIRAllergyIntoleranceReaction
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
        } else if (!($type instanceof FHIRAllergyIntoleranceReaction)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->substance) || property_exists($decoded, self::FIELD_SUBSTANCE)) {
            if (is_array($decoded->substance)) {
                $type->setSubstance(FHIRCodeableConcept::jsonUnserialize(reset($decoded->substance), $config));
            } else {
                $type->setSubstance(FHIRCodeableConcept::jsonUnserialize($decoded->substance, $config));
            }
        }
        if (isset($decoded->manifestation) || property_exists($decoded, self::FIELD_MANIFESTATION)) {
            if (is_object($decoded->manifestation)) {
                $vals = [$decoded->manifestation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MANIFESTATION, true);
            } else {
                $vals = $decoded->manifestation;
            }
            foreach($vals as $v) {
                $type->addManifestation(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->onset)
            || isset($decoded->_onset)
            || property_exists($decoded, self::FIELD_ONSET)
            || property_exists($decoded, self::FIELD_ONSET_EXT)) {
            $v = $decoded->_onset ?? new \stdClass();
            $v->value = $decoded->onset ?? null;
            $type->setOnset(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->severity)
            || isset($decoded->_severity)
            || property_exists($decoded, self::FIELD_SEVERITY)
            || property_exists($decoded, self::FIELD_SEVERITY_EXT)) {
            $v = $decoded->_severity ?? new \stdClass();
            $v->value = $decoded->severity ?? null;
            $type->setSeverity(FHIRAllergyIntoleranceSeverity::jsonUnserialize($v, $config));
        }
        if (isset($decoded->exposureRoute) || property_exists($decoded, self::FIELD_EXPOSURE_ROUTE)) {
            if (is_array($decoded->exposureRoute)) {
                $type->setExposureRoute(FHIRCodeableConcept::jsonUnserialize(reset($decoded->exposureRoute), $config));
            } else {
                $type->setExposureRoute(FHIRCodeableConcept::jsonUnserialize($decoded->exposureRoute, $config));
            }
        }
        if (isset($decoded->note) || property_exists($decoded, self::FIELD_NOTE)) {
            if (is_object($decoded->note)) {
                $vals = [$decoded->note];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NOTE, true);
            } else {
                $vals = $decoded->note;
            }
            foreach($vals as $v) {
                $type->addNote(FHIRAnnotation::jsonUnserialize($v, $config));
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
        if (isset($this->substance)) {
            $out->substance = $this->substance;
        }
        if (isset($this->manifestation) && [] !== $this->manifestation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MANIFESTATION) && 1 === count($this->manifestation)) {
                $out->manifestation = $this->manifestation[0];
            } else {
                $out->manifestation = $this->manifestation;
            }
        }
        if (isset($this->description)) {
            if (null !== ($val = $this->description->getValue())) {
                $out->description = $val;
            }
            if ($this->description->_nonValueFieldDefined()) {
                $ext = $this->description->jsonSerialize();
                unset($ext->value);
                $out->_description = $ext;
            }
        }
        if (isset($this->onset)) {
            if (null !== ($val = $this->onset->getValue())) {
                $out->onset = $val;
            }
            if ($this->onset->_nonValueFieldDefined()) {
                $ext = $this->onset->jsonSerialize();
                unset($ext->value);
                $out->_onset = $ext;
            }
        }
        if (isset($this->severity)) {
            if (null !== ($val = $this->severity->getValue())) {
                $out->severity = $val;
            }
            if ($this->severity->_nonValueFieldDefined()) {
                $ext = $this->severity->jsonSerialize();
                unset($ext->value);
                $out->_severity = $ext;
            }
        }
        if (isset($this->exposureRoute)) {
            $out->exposureRoute = $this->exposureRoute;
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        return $out;
    }
}
