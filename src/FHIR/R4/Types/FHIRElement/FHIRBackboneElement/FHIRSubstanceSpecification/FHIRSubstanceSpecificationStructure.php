<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The detailed description of a substance, typically at a level beyond what is
 * used for prescribing.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceSpecificationStructure extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_SPECIFICATION_DOT_STRUCTURE;

    /* class_default.php:56 */
    public const FIELD_STEREOCHEMISTRY = 'stereochemistry';
    public const FIELD_OPTICAL_ACTIVITY = 'opticalActivity';
    public const FIELD_MOLECULAR_FORMULA = 'molecularFormula';
    public const FIELD_MOLECULAR_FORMULA_EXT = '_molecularFormula';
    public const FIELD_MOLECULAR_FORMULA_BY_MOIETY = 'molecularFormulaByMoiety';
    public const FIELD_MOLECULAR_FORMULA_BY_MOIETY_EXT = '_molecularFormulaByMoiety';
    public const FIELD_ISOTOPE = 'isotope';
    public const FIELD_MOLECULAR_WEIGHT = 'molecularWeight';
    public const FIELD_SOURCE = 'source';
    public const FIELD_REPRESENTATION = 'representation';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_MOLECULAR_FORMULA => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MOLECULAR_FORMULA_BY_MOIETY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Stereochemistry type.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $stereochemistry;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Optical activity type.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $opticalActivity;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Molecular formula.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $molecularFormula;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Specified per moiety according to the Hill system, i.e. first C, then H, then
     * alphabetical, each moiety separated by a dot.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $molecularFormulaByMoiety;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Applicable for single substances that contain a radionuclide or a non-natural
     * isotopic ratio.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationIsotope>
     */
    #[FHIRSubstanceSpecificationIsotope]
    protected array $isotope;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight
     */
    #[FHIRSubstanceSpecificationMolecularWeight]
    protected FHIRSubstanceSpecificationMolecularWeight $molecularWeight;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting literature.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $source;
    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Molecular structural representation.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRepresentation>
     */
    #[FHIRSubstanceSpecificationRepresentation]
    protected array $representation;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceSpecificationStructure Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $stereochemistry
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $opticalActivity
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $molecularFormula
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $molecularFormulaByMoiety
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationIsotope> $isotope
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight $molecularWeight
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $source
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRepresentation> $representation
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $stereochemistry = null,
                                null|FHIRCodeableConcept $opticalActivity = null,
                                null|string|FHIRStringPrimitive|FHIRString $molecularFormula = null,
                                null|string|FHIRStringPrimitive|FHIRString $molecularFormulaByMoiety = null,
                                null|iterable $isotope = null,
                                null|FHIRSubstanceSpecificationMolecularWeight $molecularWeight = null,
                                null|iterable $source = null,
                                null|iterable $representation = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $stereochemistry) {
            $this->setStereochemistry($stereochemistry);
        }
        if (null !== $opticalActivity) {
            $this->setOpticalActivity($opticalActivity);
        }
        if (null !== $molecularFormula) {
            $this->setMolecularFormula($molecularFormula);
        }
        if (null !== $molecularFormulaByMoiety) {
            $this->setMolecularFormulaByMoiety($molecularFormulaByMoiety);
        }
        if (null !== $isotope) {
            $this->setIsotope(...$isotope);
        }
        if (null !== $molecularWeight) {
            $this->setMolecularWeight($molecularWeight);
        }
        if (null !== $source) {
            $this->setSource(...$source);
        }
        if (null !== $representation) {
            $this->setRepresentation(...$representation);
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
     * Stereochemistry type.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getStereochemistry(): null|FHIRCodeableConcept
    {
        return $this->stereochemistry ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Stereochemistry type.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $stereochemistry
     * @return static
     */
    public function setStereochemistry(null|FHIRCodeableConcept $stereochemistry): self
    {
        if (null === $stereochemistry) {
            unset($this->stereochemistry);
            return $this;
        }
        $this->stereochemistry = $stereochemistry;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Optical activity type.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getOpticalActivity(): null|FHIRCodeableConcept
    {
        return $this->opticalActivity ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Optical activity type.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $opticalActivity
     * @return static
     */
    public function setOpticalActivity(null|FHIRCodeableConcept $opticalActivity): self
    {
        if (null === $opticalActivity) {
            unset($this->opticalActivity);
            return $this;
        }
        $this->opticalActivity = $opticalActivity;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Molecular formula.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getMolecularFormula(): null|FHIRString
    {
        return $this->molecularFormula ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Molecular formula.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $molecularFormula
     * @return static
     */
    public function setMolecularFormula(null|string|FHIRStringPrimitive|FHIRString $molecularFormula): self
    {
        if (null === $molecularFormula) {
            unset($this->molecularFormula);
            return $this;
        }
        if (!($molecularFormula instanceof FHIRString)) {
            $molecularFormula = new FHIRString(value: $molecularFormula);
        }
        $this->molecularFormula = $molecularFormula;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Specified per moiety according to the Hill system, i.e. first C, then H, then
     * alphabetical, each moiety separated by a dot.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getMolecularFormulaByMoiety(): null|FHIRString
    {
        return $this->molecularFormulaByMoiety ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Specified per moiety according to the Hill system, i.e. first C, then H, then
     * alphabetical, each moiety separated by a dot.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $molecularFormulaByMoiety
     * @return static
     */
    public function setMolecularFormulaByMoiety(null|string|FHIRStringPrimitive|FHIRString $molecularFormulaByMoiety): self
    {
        if (null === $molecularFormulaByMoiety) {
            unset($this->molecularFormulaByMoiety);
            return $this;
        }
        if (!($molecularFormulaByMoiety instanceof FHIRString)) {
            $molecularFormulaByMoiety = new FHIRString(value: $molecularFormulaByMoiety);
        }
        $this->molecularFormulaByMoiety = $molecularFormulaByMoiety;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Applicable for single substances that contain a radionuclide or a non-natural
     * isotopic ratio.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationIsotope>
     */
    public function getIsotope(): array
    {
        return $this->isotope ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationIsotope>
     */
    public function getIsotopeIterator(): iterable
    {
        if (!isset($this->isotope)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->isotope);
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Applicable for single substances that contain a radionuclide or a non-natural
     * isotopic ratio.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationIsotope $isotope
     * @return static
     */
    public function addIsotope(FHIRSubstanceSpecificationIsotope $isotope): self
    {
        if (!isset($this->isotope)) {
            $this->isotope = [];
        }
        $this->isotope[] = $isotope;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Applicable for single substances that contain a radionuclide or a non-natural
     * isotopic ratio.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationIsotope ...$isotope
     * @return static
     */
    public function setIsotope(FHIRSubstanceSpecificationIsotope ...$isotope): self
    {
        if ([] === $isotope) {
            unset($this->isotope);
            return $this;
        }
        $this->isotope = $isotope;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight
     */
    public function getMolecularWeight(): null|FHIRSubstanceSpecificationMolecularWeight
    {
        return $this->molecularWeight ?? null;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight $molecularWeight
     * @return static
     */
    public function setMolecularWeight(null|FHIRSubstanceSpecificationMolecularWeight $molecularWeight): self
    {
        if (null === $molecularWeight) {
            unset($this->molecularWeight);
            return $this;
        }
        $this->molecularWeight = $molecularWeight;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting literature.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSource(): array
    {
        return $this->source ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSourceIterator(): iterable
    {
        if (!isset($this->source)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->source);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting literature.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $source
     * @return static
     */
    public function addSource(FHIRReference $source): self
    {
        if (!isset($this->source)) {
            $this->source = [];
        }
        $this->source[] = $source;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting literature.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$source
     * @return static
     */
    public function setSource(FHIRReference ...$source): self
    {
        if ([] === $source) {
            unset($this->source);
            return $this;
        }
        $this->source = $source;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Molecular structural representation.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRepresentation>
     */
    public function getRepresentation(): array
    {
        return $this->representation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRepresentation>
     */
    public function getRepresentationIterator(): iterable
    {
        if (!isset($this->representation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->representation);
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Molecular structural representation.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRepresentation $representation
     * @return static
     */
    public function addRepresentation(FHIRSubstanceSpecificationRepresentation $representation): self
    {
        if (!isset($this->representation)) {
            $this->representation = [];
        }
        $this->representation[] = $representation;
        return $this;
    }

    /**
     * The detailed description of a substance, typically at a level beyond what is
     * used for prescribing.
     *
     * Molecular structural representation.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRepresentation ...$representation
     * @return static
     */
    public function setRepresentation(FHIRSubstanceSpecificationRepresentation ...$representation): self
    {
        if ([] === $representation) {
            unset($this->representation);
            return $this;
        }
        $this->representation = $representation;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceSpecificationStructure)) {
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
            } else if (self::FIELD_STEREOCHEMISTRY === $cen) {
                $type->setStereochemistry(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OPTICAL_ACTIVITY === $cen) {
                $type->setOpticalActivity(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MOLECULAR_FORMULA === $cen) {
                $type->setMolecularFormula(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MOLECULAR_FORMULA_BY_MOIETY === $cen) {
                $type->setMolecularFormulaByMoiety(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ISOTOPE === $cen) {
                $type->addIsotope(FHIRSubstanceSpecificationIsotope::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MOLECULAR_WEIGHT === $cen) {
                $type->setMolecularWeight(FHIRSubstanceSpecificationMolecularWeight::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE === $cen) {
                $type->addSource(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REPRESENTATION === $cen) {
                $type->addRepresentation(FHIRSubstanceSpecificationRepresentation::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MOLECULAR_FORMULA])) {
            if (isset($type->molecularFormula)) {
                $type->molecularFormula->setValue((string)$attributes[self::FIELD_MOLECULAR_FORMULA]);
            } else {
                $type->setMolecularFormula((string)$attributes[self::FIELD_MOLECULAR_FORMULA]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MOLECULAR_FORMULA, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MOLECULAR_FORMULA_BY_MOIETY])) {
            if (isset($type->molecularFormulaByMoiety)) {
                $type->molecularFormulaByMoiety->setValue((string)$attributes[self::FIELD_MOLECULAR_FORMULA_BY_MOIETY]);
            } else {
                $type->setMolecularFormulaByMoiety((string)$attributes[self::FIELD_MOLECULAR_FORMULA_BY_MOIETY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MOLECULAR_FORMULA_BY_MOIETY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->molecularFormula) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MOLECULAR_FORMULA]) {
            $xw->writeAttribute(self::FIELD_MOLECULAR_FORMULA, $this->molecularFormula->_getValueAsString());
        }
        if (isset($this->molecularFormulaByMoiety) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MOLECULAR_FORMULA_BY_MOIETY]) {
            $xw->writeAttribute(self::FIELD_MOLECULAR_FORMULA_BY_MOIETY, $this->molecularFormulaByMoiety->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->stereochemistry)) {
            $xw->startElement(self::FIELD_STEREOCHEMISTRY);
            $this->stereochemistry->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->opticalActivity)) {
            $xw->startElement(self::FIELD_OPTICAL_ACTIVITY);
            $this->opticalActivity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->molecularFormula)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MOLECULAR_FORMULA]
                || $this->molecularFormula->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MOLECULAR_FORMULA);
            $this->molecularFormula->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MOLECULAR_FORMULA]);
            $xw->endElement();
        }
        if (isset($this->molecularFormulaByMoiety)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MOLECULAR_FORMULA_BY_MOIETY]
                || $this->molecularFormulaByMoiety->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MOLECULAR_FORMULA_BY_MOIETY);
            $this->molecularFormulaByMoiety->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MOLECULAR_FORMULA_BY_MOIETY]);
            $xw->endElement();
        }
        if (isset($this->isotope)) {
            foreach ($this->isotope as $v) {
                $xw->startElement(self::FIELD_ISOTOPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->molecularWeight)) {
            $xw->startElement(self::FIELD_MOLECULAR_WEIGHT);
            $this->molecularWeight->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->source)) {
            foreach ($this->source as $v) {
                $xw->startElement(self::FIELD_SOURCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->representation)) {
            foreach ($this->representation as $v) {
                $xw->startElement(self::FIELD_REPRESENTATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSpecification\FHIRSubstanceSpecificationStructure
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
        } else if (!($type instanceof FHIRSubstanceSpecificationStructure)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->stereochemistry) || property_exists($decoded, self::FIELD_STEREOCHEMISTRY)) {
            if (is_array($decoded->stereochemistry)) {
                $type->setStereochemistry(FHIRCodeableConcept::jsonUnserialize(reset($decoded->stereochemistry), $config));
            } else {
                $type->setStereochemistry(FHIRCodeableConcept::jsonUnserialize($decoded->stereochemistry, $config));
            }
        }
        if (isset($decoded->opticalActivity) || property_exists($decoded, self::FIELD_OPTICAL_ACTIVITY)) {
            if (is_array($decoded->opticalActivity)) {
                $type->setOpticalActivity(FHIRCodeableConcept::jsonUnserialize(reset($decoded->opticalActivity), $config));
            } else {
                $type->setOpticalActivity(FHIRCodeableConcept::jsonUnserialize($decoded->opticalActivity, $config));
            }
        }
        if (isset($decoded->molecularFormula)
            || isset($decoded->_molecularFormula)
            || property_exists($decoded, self::FIELD_MOLECULAR_FORMULA)
            || property_exists($decoded, self::FIELD_MOLECULAR_FORMULA_EXT)) {
            $v = $decoded->_molecularFormula ?? new \stdClass();
            $v->value = $decoded->molecularFormula ?? null;
            $type->setMolecularFormula(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->molecularFormulaByMoiety)
            || isset($decoded->_molecularFormulaByMoiety)
            || property_exists($decoded, self::FIELD_MOLECULAR_FORMULA_BY_MOIETY)
            || property_exists($decoded, self::FIELD_MOLECULAR_FORMULA_BY_MOIETY_EXT)) {
            $v = $decoded->_molecularFormulaByMoiety ?? new \stdClass();
            $v->value = $decoded->molecularFormulaByMoiety ?? null;
            $type->setMolecularFormulaByMoiety(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->isotope) || property_exists($decoded, self::FIELD_ISOTOPE)) {
            if (is_object($decoded->isotope)) {
                $vals = [$decoded->isotope];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ISOTOPE, true);
            } else {
                $vals = $decoded->isotope;
            }
            foreach($vals as $v) {
                $type->addIsotope(FHIRSubstanceSpecificationIsotope::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->molecularWeight) || property_exists($decoded, self::FIELD_MOLECULAR_WEIGHT)) {
            if (is_array($decoded->molecularWeight)) {
                $type->setMolecularWeight(FHIRSubstanceSpecificationMolecularWeight::jsonUnserialize(reset($decoded->molecularWeight), $config));
            } else {
                $type->setMolecularWeight(FHIRSubstanceSpecificationMolecularWeight::jsonUnserialize($decoded->molecularWeight, $config));
            }
        }
        if (isset($decoded->source) || property_exists($decoded, self::FIELD_SOURCE)) {
            if (is_object($decoded->source)) {
                $vals = [$decoded->source];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SOURCE, true);
            } else {
                $vals = $decoded->source;
            }
            foreach($vals as $v) {
                $type->addSource(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->representation) || property_exists($decoded, self::FIELD_REPRESENTATION)) {
            if (is_object($decoded->representation)) {
                $vals = [$decoded->representation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REPRESENTATION, true);
            } else {
                $vals = $decoded->representation;
            }
            foreach($vals as $v) {
                $type->addRepresentation(FHIRSubstanceSpecificationRepresentation::jsonUnserialize($v, $config));
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
        if (isset($this->stereochemistry)) {
            $out->stereochemistry = $this->stereochemistry;
        }
        if (isset($this->opticalActivity)) {
            $out->opticalActivity = $this->opticalActivity;
        }
        if (isset($this->molecularFormula)) {
            if (null !== ($val = $this->molecularFormula->getValue())) {
                $out->molecularFormula = $val;
            }
            if ($this->molecularFormula->_nonValueFieldDefined()) {
                $ext = $this->molecularFormula->jsonSerialize();
                unset($ext->value);
                $out->_molecularFormula = $ext;
            }
        }
        if (isset($this->molecularFormulaByMoiety)) {
            if (null !== ($val = $this->molecularFormulaByMoiety->getValue())) {
                $out->molecularFormulaByMoiety = $val;
            }
            if ($this->molecularFormulaByMoiety->_nonValueFieldDefined()) {
                $ext = $this->molecularFormulaByMoiety->jsonSerialize();
                unset($ext->value);
                $out->_molecularFormulaByMoiety = $ext;
            }
        }
        if (isset($this->isotope) && [] !== $this->isotope) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ISOTOPE) && 1 === count($this->isotope)) {
                $out->isotope = $this->isotope[0];
            } else {
                $out->isotope = $this->isotope;
            }
        }
        if (isset($this->molecularWeight)) {
            $out->molecularWeight = $this->molecularWeight;
        }
        if (isset($this->source) && [] !== $this->source) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SOURCE) && 1 === count($this->source)) {
                $out->source = $this->source[0];
            } else {
                $out->source = $this->source;
            }
        }
        if (isset($this->representation) && [] !== $this->representation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REPRESENTATION) && 1 === count($this->representation)) {
                $out->representation = $this->representation[0];
            } else {
                $out->representation = $this->representation;
            }
        }
        return $out;
    }
}
