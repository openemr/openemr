<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Source material shall capture information on the taxonomic and anatomical
 * origins as well as the fraction of a material that can result in or can be
 * modified to form a substance. This set of data elements shall be used to define
 * polymer substances isolated from biological matrices. Taxonomic and anatomical
 * origins shall be described using a controlled vocabulary as required. This
 * information is captured for naturally derived polymers ( . starch) and
 * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
 * the Substance level defines the fresh material of a single species or
 * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
 * preparations, the fraction information will be captured at the Substance
 * information level and additional information for herbal extracts will be
 * captured at the Specified Substance Group 1 information level. See for further
 * explanation the Substance Class: Structurally Diverse and the herbal annex.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceSourceMaterialOrganism extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL_DOT_ORGANISM;

    /* class_default.php:56 */
    public const FIELD_FAMILY = 'family';
    public const FIELD_GENUS = 'genus';
    public const FIELD_SPECIES = 'species';
    public const FIELD_INTRASPECIFIC_TYPE = 'intraspecificType';
    public const FIELD_INTRASPECIFIC_DESCRIPTION = 'intraspecificDescription';
    public const FIELD_INTRASPECIFIC_DESCRIPTION_EXT = '_intraspecificDescription';
    public const FIELD_AUTHOR = 'author';
    public const FIELD_HYBRID = 'hybrid';
    public const FIELD_ORGANISM_GENERAL = 'organismGeneral';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_INTRASPECIFIC_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The family of an organism shall be specified.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $family;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The genus of an organism shall be specified; refers to the Latin epithet of the
     * genus element of the plant/animal scientific name; it is present in names for
     * genera, species and infraspecies.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $genus;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The species of an organism shall be specified; refers to the Latin epithet of
     * the species of the plant/animal; it is present in names for species and
     * infraspecies.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $species;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Intraspecific type of an organism shall be specified.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $intraspecificType;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The intraspecific description of an organism shall be specified based on a
     * controlled vocabulary. For Influenza Vaccine, the intraspecific description
     * shall contain the syntax of the antigen in line with the WHO convention.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $intraspecificDescription;
    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.6.1 Author type (Conditional).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialAuthor>
     */
    #[FHIRSubstanceSourceMaterialAuthor]
    protected array $author;
    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.8.1 Hybrid species maternal organism ID (Optional).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid
     */
    #[FHIRSubstanceSourceMaterialHybrid]
    protected FHIRSubstanceSourceMaterialHybrid $hybrid;
    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.7.1 Kingdom (Conditional).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganismGeneral
     */
    #[FHIRSubstanceSourceMaterialOrganismGeneral]
    protected FHIRSubstanceSourceMaterialOrganismGeneral $organismGeneral;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceSourceMaterialOrganism Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $family
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $genus
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $species
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $intraspecificType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $intraspecificDescription
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialAuthor> $author
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid $hybrid
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganismGeneral $organismGeneral
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $family = null,
                                null|FHIRCodeableConcept $genus = null,
                                null|FHIRCodeableConcept $species = null,
                                null|FHIRCodeableConcept $intraspecificType = null,
                                null|string|FHIRStringPrimitive|FHIRString $intraspecificDescription = null,
                                null|iterable $author = null,
                                null|FHIRSubstanceSourceMaterialHybrid $hybrid = null,
                                null|FHIRSubstanceSourceMaterialOrganismGeneral $organismGeneral = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $family) {
            $this->setFamily($family);
        }
        if (null !== $genus) {
            $this->setGenus($genus);
        }
        if (null !== $species) {
            $this->setSpecies($species);
        }
        if (null !== $intraspecificType) {
            $this->setIntraspecificType($intraspecificType);
        }
        if (null !== $intraspecificDescription) {
            $this->setIntraspecificDescription($intraspecificDescription);
        }
        if (null !== $author) {
            $this->setAuthor(...$author);
        }
        if (null !== $hybrid) {
            $this->setHybrid($hybrid);
        }
        if (null !== $organismGeneral) {
            $this->setOrganismGeneral($organismGeneral);
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
     * The family of an organism shall be specified.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getFamily(): null|FHIRCodeableConcept
    {
        return $this->family ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The family of an organism shall be specified.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $family
     * @return static
     */
    public function setFamily(null|FHIRCodeableConcept $family): self
    {
        if (null === $family) {
            unset($this->family);
            return $this;
        }
        $this->family = $family;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The genus of an organism shall be specified; refers to the Latin epithet of the
     * genus element of the plant/animal scientific name; it is present in names for
     * genera, species and infraspecies.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getGenus(): null|FHIRCodeableConcept
    {
        return $this->genus ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The genus of an organism shall be specified; refers to the Latin epithet of the
     * genus element of the plant/animal scientific name; it is present in names for
     * genera, species and infraspecies.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $genus
     * @return static
     */
    public function setGenus(null|FHIRCodeableConcept $genus): self
    {
        if (null === $genus) {
            unset($this->genus);
            return $this;
        }
        $this->genus = $genus;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The species of an organism shall be specified; refers to the Latin epithet of
     * the species of the plant/animal; it is present in names for species and
     * infraspecies.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSpecies(): null|FHIRCodeableConcept
    {
        return $this->species ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The species of an organism shall be specified; refers to the Latin epithet of
     * the species of the plant/animal; it is present in names for species and
     * infraspecies.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $species
     * @return static
     */
    public function setSpecies(null|FHIRCodeableConcept $species): self
    {
        if (null === $species) {
            unset($this->species);
            return $this;
        }
        $this->species = $species;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Intraspecific type of an organism shall be specified.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getIntraspecificType(): null|FHIRCodeableConcept
    {
        return $this->intraspecificType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Intraspecific type of an organism shall be specified.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $intraspecificType
     * @return static
     */
    public function setIntraspecificType(null|FHIRCodeableConcept $intraspecificType): self
    {
        if (null === $intraspecificType) {
            unset($this->intraspecificType);
            return $this;
        }
        $this->intraspecificType = $intraspecificType;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The intraspecific description of an organism shall be specified based on a
     * controlled vocabulary. For Influenza Vaccine, the intraspecific description
     * shall contain the syntax of the antigen in line with the WHO convention.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getIntraspecificDescription(): null|FHIRString
    {
        return $this->intraspecificDescription ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The intraspecific description of an organism shall be specified based on a
     * controlled vocabulary. For Influenza Vaccine, the intraspecific description
     * shall contain the syntax of the antigen in line with the WHO convention.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $intraspecificDescription
     * @return static
     */
    public function setIntraspecificDescription(null|string|FHIRStringPrimitive|FHIRString $intraspecificDescription): self
    {
        if (null === $intraspecificDescription) {
            unset($this->intraspecificDescription);
            return $this;
        }
        if (!($intraspecificDescription instanceof FHIRString)) {
            $intraspecificDescription = new FHIRString(value: $intraspecificDescription);
        }
        $this->intraspecificDescription = $intraspecificDescription;
        return $this;
    }

    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.6.1 Author type (Conditional).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialAuthor>
     */
    public function getAuthor(): array
    {
        return $this->author ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialAuthor>
     */
    public function getAuthorIterator(): iterable
    {
        if (!isset($this->author)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->author);
    }

    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.6.1 Author type (Conditional).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialAuthor $author
     * @return static
     */
    public function addAuthor(FHIRSubstanceSourceMaterialAuthor $author): self
    {
        if (!isset($this->author)) {
            $this->author = [];
        }
        $this->author[] = $author;
        return $this;
    }

    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.6.1 Author type (Conditional).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialAuthor ...$author
     * @return static
     */
    public function setAuthor(FHIRSubstanceSourceMaterialAuthor ...$author): self
    {
        if ([] === $author) {
            unset($this->author);
            return $this;
        }
        $this->author = $author;
        return $this;
    }

    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.8.1 Hybrid species maternal organism ID (Optional).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid
     */
    public function getHybrid(): null|FHIRSubstanceSourceMaterialHybrid
    {
        return $this->hybrid ?? null;
    }

    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.8.1 Hybrid species maternal organism ID (Optional).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid $hybrid
     * @return static
     */
    public function setHybrid(null|FHIRSubstanceSourceMaterialHybrid $hybrid): self
    {
        if (null === $hybrid) {
            unset($this->hybrid);
            return $this;
        }
        $this->hybrid = $hybrid;
        return $this;
    }

    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.7.1 Kingdom (Conditional).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganismGeneral
     */
    public function getOrganismGeneral(): null|FHIRSubstanceSourceMaterialOrganismGeneral
    {
        return $this->organismGeneral ?? null;
    }

    /**
     * Source material shall capture information on the taxonomic and anatomical
     * origins as well as the fraction of a material that can result in or can be
     * modified to form a substance. This set of data elements shall be used to define
     * polymer substances isolated from biological matrices. Taxonomic and anatomical
     * origins shall be described using a controlled vocabulary as required. This
     * information is captured for naturally derived polymers ( . starch) and
     * structurally diverse substances. For Organisms belonging to the Kingdom Plantae
     * the Substance level defines the fresh material of a single species or
     * infraspecies, the Herbal Drug and the Herbal preparation. For Herbal
     * preparations, the fraction information will be captured at the Substance
     * information level and additional information for herbal extracts will be
     * captured at the Specified Substance Group 1 information level. See for further
     * explanation the Substance Class: Structurally Diverse and the herbal annex.
     *
     * 4.9.13.7.1 Kingdom (Conditional).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganismGeneral $organismGeneral
     * @return static
     */
    public function setOrganismGeneral(null|FHIRSubstanceSourceMaterialOrganismGeneral $organismGeneral): self
    {
        if (null === $organismGeneral) {
            unset($this->organismGeneral);
            return $this;
        }
        $this->organismGeneral = $organismGeneral;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceSourceMaterialOrganism)) {
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
            } else if (self::FIELD_FAMILY === $cen) {
                $type->setFamily(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GENUS === $cen) {
                $type->setGenus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SPECIES === $cen) {
                $type->setSpecies(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INTRASPECIFIC_TYPE === $cen) {
                $type->setIntraspecificType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INTRASPECIFIC_DESCRIPTION === $cen) {
                $type->setIntraspecificDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHOR === $cen) {
                $type->addAuthor(FHIRSubstanceSourceMaterialAuthor::xmlUnserialize($ce, $config));
            } else if (self::FIELD_HYBRID === $cen) {
                $type->setHybrid(FHIRSubstanceSourceMaterialHybrid::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORGANISM_GENERAL === $cen) {
                $type->setOrganismGeneral(FHIRSubstanceSourceMaterialOrganismGeneral::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_INTRASPECIFIC_DESCRIPTION])) {
            if (isset($type->intraspecificDescription)) {
                $type->intraspecificDescription->setValue((string)$attributes[self::FIELD_INTRASPECIFIC_DESCRIPTION]);
            } else {
                $type->setIntraspecificDescription((string)$attributes[self::FIELD_INTRASPECIFIC_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_INTRASPECIFIC_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->intraspecificDescription) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_INTRASPECIFIC_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_INTRASPECIFIC_DESCRIPTION, $this->intraspecificDescription->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->family)) {
            $xw->startElement(self::FIELD_FAMILY);
            $this->family->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->genus)) {
            $xw->startElement(self::FIELD_GENUS);
            $this->genus->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->species)) {
            $xw->startElement(self::FIELD_SPECIES);
            $this->species->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->intraspecificType)) {
            $xw->startElement(self::FIELD_INTRASPECIFIC_TYPE);
            $this->intraspecificType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->intraspecificDescription)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_INTRASPECIFIC_DESCRIPTION]
                || $this->intraspecificDescription->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_INTRASPECIFIC_DESCRIPTION);
            $this->intraspecificDescription->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_INTRASPECIFIC_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->author)) {
            foreach ($this->author as $v) {
                $xw->startElement(self::FIELD_AUTHOR);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->hybrid)) {
            $xw->startElement(self::FIELD_HYBRID);
            $this->hybrid->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->organismGeneral)) {
            $xw->startElement(self::FIELD_ORGANISM_GENERAL);
            $this->organismGeneral->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism
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
        } else if (!($type instanceof FHIRSubstanceSourceMaterialOrganism)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->family) || property_exists($decoded, self::FIELD_FAMILY)) {
            if (is_array($decoded->family)) {
                $type->setFamily(FHIRCodeableConcept::jsonUnserialize(reset($decoded->family), $config));
            } else {
                $type->setFamily(FHIRCodeableConcept::jsonUnserialize($decoded->family, $config));
            }
        }
        if (isset($decoded->genus) || property_exists($decoded, self::FIELD_GENUS)) {
            if (is_array($decoded->genus)) {
                $type->setGenus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->genus), $config));
            } else {
                $type->setGenus(FHIRCodeableConcept::jsonUnserialize($decoded->genus, $config));
            }
        }
        if (isset($decoded->species) || property_exists($decoded, self::FIELD_SPECIES)) {
            if (is_array($decoded->species)) {
                $type->setSpecies(FHIRCodeableConcept::jsonUnserialize(reset($decoded->species), $config));
            } else {
                $type->setSpecies(FHIRCodeableConcept::jsonUnserialize($decoded->species, $config));
            }
        }
        if (isset($decoded->intraspecificType) || property_exists($decoded, self::FIELD_INTRASPECIFIC_TYPE)) {
            if (is_array($decoded->intraspecificType)) {
                $type->setIntraspecificType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->intraspecificType), $config));
            } else {
                $type->setIntraspecificType(FHIRCodeableConcept::jsonUnserialize($decoded->intraspecificType, $config));
            }
        }
        if (isset($decoded->intraspecificDescription)
            || isset($decoded->_intraspecificDescription)
            || property_exists($decoded, self::FIELD_INTRASPECIFIC_DESCRIPTION)
            || property_exists($decoded, self::FIELD_INTRASPECIFIC_DESCRIPTION_EXT)) {
            $v = $decoded->_intraspecificDescription ?? new \stdClass();
            $v->value = $decoded->intraspecificDescription ?? null;
            $type->setIntraspecificDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->author) || property_exists($decoded, self::FIELD_AUTHOR)) {
            if (is_object($decoded->author)) {
                $vals = [$decoded->author];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_AUTHOR, true);
            } else {
                $vals = $decoded->author;
            }
            foreach($vals as $v) {
                $type->addAuthor(FHIRSubstanceSourceMaterialAuthor::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->hybrid) || property_exists($decoded, self::FIELD_HYBRID)) {
            if (is_array($decoded->hybrid)) {
                $type->setHybrid(FHIRSubstanceSourceMaterialHybrid::jsonUnserialize(reset($decoded->hybrid), $config));
            } else {
                $type->setHybrid(FHIRSubstanceSourceMaterialHybrid::jsonUnserialize($decoded->hybrid, $config));
            }
        }
        if (isset($decoded->organismGeneral) || property_exists($decoded, self::FIELD_ORGANISM_GENERAL)) {
            if (is_array($decoded->organismGeneral)) {
                $type->setOrganismGeneral(FHIRSubstanceSourceMaterialOrganismGeneral::jsonUnserialize(reset($decoded->organismGeneral), $config));
            } else {
                $type->setOrganismGeneral(FHIRSubstanceSourceMaterialOrganismGeneral::jsonUnserialize($decoded->organismGeneral, $config));
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
        if (isset($this->family)) {
            $out->family = $this->family;
        }
        if (isset($this->genus)) {
            $out->genus = $this->genus;
        }
        if (isset($this->species)) {
            $out->species = $this->species;
        }
        if (isset($this->intraspecificType)) {
            $out->intraspecificType = $this->intraspecificType;
        }
        if (isset($this->intraspecificDescription)) {
            if (null !== ($val = $this->intraspecificDescription->getValue())) {
                $out->intraspecificDescription = $val;
            }
            if ($this->intraspecificDescription->_nonValueFieldDefined()) {
                $ext = $this->intraspecificDescription->jsonSerialize();
                unset($ext->value);
                $out->_intraspecificDescription = $ext;
            }
        }
        if (isset($this->author) && [] !== $this->author) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_AUTHOR) && 1 === count($this->author)) {
                $out->author = $this->author[0];
            } else {
                $out->author = $this->author;
            }
        }
        if (isset($this->hybrid)) {
            $out->hybrid = $this->hybrid;
        }
        if (isset($this->organismGeneral)) {
            $out->organismGeneral = $this->organismGeneral;
        }
        return $out;
    }
}
