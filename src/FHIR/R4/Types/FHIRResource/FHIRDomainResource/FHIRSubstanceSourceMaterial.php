<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;

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
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

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
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceSourceMaterial extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL;

    /* class_default.php:56 */
    public const FIELD_SOURCE_MATERIAL_CLASS = 'sourceMaterialClass';
    public const FIELD_SOURCE_MATERIAL_TYPE = 'sourceMaterialType';
    public const FIELD_SOURCE_MATERIAL_STATE = 'sourceMaterialState';
    public const FIELD_ORGANISM_ID = 'organismId';
    public const FIELD_ORGANISM_NAME = 'organismName';
    public const FIELD_ORGANISM_NAME_EXT = '_organismName';
    public const FIELD_PARENT_SUBSTANCE_ID = 'parentSubstanceId';
    public const FIELD_PARENT_SUBSTANCE_NAME = 'parentSubstanceName';
    public const FIELD_PARENT_SUBSTANCE_NAME_EXT = '_parentSubstanceName';
    public const FIELD_COUNTRY_OF_ORIGIN = 'countryOfOrigin';
    public const FIELD_GEOGRAPHICAL_LOCATION = 'geographicalLocation';
    public const FIELD_GEOGRAPHICAL_LOCATION_EXT = '_geographicalLocation';
    public const FIELD_DEVELOPMENT_STAGE = 'developmentStage';
    public const FIELD_FRACTION_DESCRIPTION = 'fractionDescription';
    public const FIELD_ORGANISM = 'organism';
    public const FIELD_PART_DESCRIPTION = 'partDescription';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ORGANISM_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * General high level classification of the source material specific to the origin
     * of the material.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $sourceMaterialClass;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of the source material shall be specified based on a controlled
     * vocabulary. For vaccines, this subclause refers to the class of infectious
     * agent.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $sourceMaterialType;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The state of the source material when extracted.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $sourceMaterialState;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The unique identifier associated with the source material parent organism shall
     * be specified.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $organismId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The organism accepted Scientific name shall be provided based on the organism
     * taxonomy.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $organismName;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The parent of the herbal drug Ginkgo biloba, Leaf is the substance ID of the
     * substance (fresh) of Ginkgo biloba L. or Ginkgo biloba L. (Whole plant).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $parentSubstanceId;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The parent substance of the Herbal Drug, or Herbal preparation.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $parentSubstanceName;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country where the plant material is harvested or the countries where the
     * plasma is sourced from as laid down in accordance with the Plasma Master File.
     * For “Plasma-derived substances” the attribute country of origin provides
     * information about the countries used for the manufacturing of the Cryopoor plama
     * or Crioprecipitate.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $countryOfOrigin;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The place/region where the plant is harvested or the places/regions where the
     * animal source material has its habitat.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $geographicalLocation;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Stage of life for animals, plants, insects and microorganisms. This information
     * shall be provided only when the substance is significantly different in these
     * stages (e.g. foetal bovine serum).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $developmentStage;
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
     * Many complex materials are fractions of parts of plants, animals, or minerals.
     * Fraction elements are often necessary to define both Substances and Specified
     * Group 1 Substances. For substances derived from Plants, fraction information
     * will be captured at the Substance information level ( . Oils, Juices and
     * Exudates). Additional information for Extracts, such as extraction solvent
     * composition, will be captured at the Specified Substance Group 1 information
     * level. For plasma-derived products fraction information will be captured at the
     * Substance and the Specified Substance Group 1 levels.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription>
     */
    #[FHIRSubstanceSourceMaterialFractionDescription]
    protected array $fractionDescription;
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
     * This subclause describes the organism which the substance is derived from. For
     * vaccines, the parent organism shall be specified based on these subclause
     * elements. As an example, full taxonomy will be described for the Substance Name:
     * ., Leaf.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism
     */
    #[FHIRSubstanceSourceMaterialOrganism]
    protected FHIRSubstanceSourceMaterialOrganism $organism;
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
     * To do.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription>
     */
    #[FHIRSubstanceSourceMaterialPartDescription]
    protected array $partDescription;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceSourceMaterial Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $sourceMaterialClass
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $sourceMaterialType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $sourceMaterialState
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $organismId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $organismName
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $parentSubstanceId
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $parentSubstanceName
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $countryOfOrigin
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $geographicalLocation
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $developmentStage
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription> $fractionDescription
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism $organism
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription> $partDescription
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRNarrative $text = null,
                                null|iterable $contained = null,
                                null|iterable $extension = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $sourceMaterialClass = null,
                                null|FHIRCodeableConcept $sourceMaterialType = null,
                                null|FHIRCodeableConcept $sourceMaterialState = null,
                                null|FHIRIdentifier $organismId = null,
                                null|string|FHIRStringPrimitive|FHIRString $organismName = null,
                                null|iterable $parentSubstanceId = null,
                                null|iterable $parentSubstanceName = null,
                                null|iterable $countryOfOrigin = null,
                                null|iterable $geographicalLocation = null,
                                null|FHIRCodeableConcept $developmentStage = null,
                                null|iterable $fractionDescription = null,
                                null|FHIRSubstanceSourceMaterialOrganism $organism = null,
                                null|iterable $partDescription = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            text: $text,
                            contained: $contained,
                            extension: $extension,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $sourceMaterialClass) {
            $this->setSourceMaterialClass($sourceMaterialClass);
        }
        if (null !== $sourceMaterialType) {
            $this->setSourceMaterialType($sourceMaterialType);
        }
        if (null !== $sourceMaterialState) {
            $this->setSourceMaterialState($sourceMaterialState);
        }
        if (null !== $organismId) {
            $this->setOrganismId($organismId);
        }
        if (null !== $organismName) {
            $this->setOrganismName($organismName);
        }
        if (null !== $parentSubstanceId) {
            $this->setParentSubstanceId(...$parentSubstanceId);
        }
        if (null !== $parentSubstanceName) {
            $this->setParentSubstanceName(...$parentSubstanceName);
        }
        if (null !== $countryOfOrigin) {
            $this->setCountryOfOrigin(...$countryOfOrigin);
        }
        if (null !== $geographicalLocation) {
            $this->setGeographicalLocation(...$geographicalLocation);
        }
        if (null !== $developmentStage) {
            $this->setDevelopmentStage($developmentStage);
        }
        if (null !== $fractionDescription) {
            $this->setFractionDescription(...$fractionDescription);
        }
        if (null !== $organism) {
            $this->setOrganism($organism);
        }
        if (null !== $partDescription) {
            $this->setPartDescription(...$partDescription);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * General high level classification of the source material specific to the origin
     * of the material.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSourceMaterialClass(): null|FHIRCodeableConcept
    {
        return $this->sourceMaterialClass ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * General high level classification of the source material specific to the origin
     * of the material.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $sourceMaterialClass
     * @return static
     */
    public function setSourceMaterialClass(null|FHIRCodeableConcept $sourceMaterialClass): self
    {
        if (null === $sourceMaterialClass) {
            unset($this->sourceMaterialClass);
            return $this;
        }
        $this->sourceMaterialClass = $sourceMaterialClass;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of the source material shall be specified based on a controlled
     * vocabulary. For vaccines, this subclause refers to the class of infectious
     * agent.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSourceMaterialType(): null|FHIRCodeableConcept
    {
        return $this->sourceMaterialType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of the source material shall be specified based on a controlled
     * vocabulary. For vaccines, this subclause refers to the class of infectious
     * agent.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $sourceMaterialType
     * @return static
     */
    public function setSourceMaterialType(null|FHIRCodeableConcept $sourceMaterialType): self
    {
        if (null === $sourceMaterialType) {
            unset($this->sourceMaterialType);
            return $this;
        }
        $this->sourceMaterialType = $sourceMaterialType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The state of the source material when extracted.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSourceMaterialState(): null|FHIRCodeableConcept
    {
        return $this->sourceMaterialState ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The state of the source material when extracted.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $sourceMaterialState
     * @return static
     */
    public function setSourceMaterialState(null|FHIRCodeableConcept $sourceMaterialState): self
    {
        if (null === $sourceMaterialState) {
            unset($this->sourceMaterialState);
            return $this;
        }
        $this->sourceMaterialState = $sourceMaterialState;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The unique identifier associated with the source material parent organism shall
     * be specified.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getOrganismId(): null|FHIRIdentifier
    {
        return $this->organismId ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The unique identifier associated with the source material parent organism shall
     * be specified.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $organismId
     * @return static
     */
    public function setOrganismId(null|FHIRIdentifier $organismId): self
    {
        if (null === $organismId) {
            unset($this->organismId);
            return $this;
        }
        $this->organismId = $organismId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The organism accepted Scientific name shall be provided based on the organism
     * taxonomy.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getOrganismName(): null|FHIRString
    {
        return $this->organismName ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The organism accepted Scientific name shall be provided based on the organism
     * taxonomy.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $organismName
     * @return static
     */
    public function setOrganismName(null|string|FHIRStringPrimitive|FHIRString $organismName): self
    {
        if (null === $organismName) {
            unset($this->organismName);
            return $this;
        }
        if (!($organismName instanceof FHIRString)) {
            $organismName = new FHIRString(value: $organismName);
        }
        $this->organismName = $organismName;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The parent of the herbal drug Ginkgo biloba, Leaf is the substance ID of the
     * substance (fresh) of Ginkgo biloba L. or Ginkgo biloba L. (Whole plant).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getParentSubstanceId(): array
    {
        return $this->parentSubstanceId ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getParentSubstanceIdIterator(): iterable
    {
        if (!isset($this->parentSubstanceId)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->parentSubstanceId);
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The parent of the herbal drug Ginkgo biloba, Leaf is the substance ID of the
     * substance (fresh) of Ginkgo biloba L. or Ginkgo biloba L. (Whole plant).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $parentSubstanceId
     * @return static
     */
    public function addParentSubstanceId(FHIRIdentifier $parentSubstanceId): self
    {
        if (!isset($this->parentSubstanceId)) {
            $this->parentSubstanceId = [];
        }
        $this->parentSubstanceId[] = $parentSubstanceId;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The parent of the herbal drug Ginkgo biloba, Leaf is the substance ID of the
     * substance (fresh) of Ginkgo biloba L. or Ginkgo biloba L. (Whole plant).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier ...$parentSubstanceId
     * @return static
     */
    public function setParentSubstanceId(FHIRIdentifier ...$parentSubstanceId): self
    {
        if ([] === $parentSubstanceId) {
            unset($this->parentSubstanceId);
            return $this;
        }
        $this->parentSubstanceId = $parentSubstanceId;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The parent substance of the Herbal Drug, or Herbal preparation.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getParentSubstanceName(): array
    {
        return $this->parentSubstanceName ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getParentSubstanceNameIterator(): iterable
    {
        if (!isset($this->parentSubstanceName)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->parentSubstanceName);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The parent substance of the Herbal Drug, or Herbal preparation.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $parentSubstanceName
     * @return static
     */
    public function addParentSubstanceName(string|FHIRStringPrimitive|FHIRString $parentSubstanceName): self
    {
        if (!($parentSubstanceName instanceof FHIRString)) {
            $parentSubstanceName = new FHIRString(value: $parentSubstanceName);
        }
        if (!isset($this->parentSubstanceName)) {
            $this->parentSubstanceName = [];
        }
        $this->parentSubstanceName[] = $parentSubstanceName;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The parent substance of the Herbal Drug, or Herbal preparation.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$parentSubstanceName
     * @return static
     */
    public function setParentSubstanceName(string|FHIRStringPrimitive|FHIRString ...$parentSubstanceName): self
    {
        if ([] === $parentSubstanceName) {
            unset($this->parentSubstanceName);
            return $this;
        }
        $this->parentSubstanceName = [];
        foreach($parentSubstanceName as $v) {
            if ($v instanceof FHIRString) {
                $this->parentSubstanceName[] = $v;
            } else {
                $this->parentSubstanceName[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country where the plant material is harvested or the countries where the
     * plasma is sourced from as laid down in accordance with the Plasma Master File.
     * For “Plasma-derived substances” the attribute country of origin provides
     * information about the countries used for the manufacturing of the Cryopoor plama
     * or Crioprecipitate.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCountryOfOrigin(): array
    {
        return $this->countryOfOrigin ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCountryOfOriginIterator(): iterable
    {
        if (!isset($this->countryOfOrigin)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->countryOfOrigin);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country where the plant material is harvested or the countries where the
     * plasma is sourced from as laid down in accordance with the Plasma Master File.
     * For “Plasma-derived substances” the attribute country of origin provides
     * information about the countries used for the manufacturing of the Cryopoor plama
     * or Crioprecipitate.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $countryOfOrigin
     * @return static
     */
    public function addCountryOfOrigin(FHIRCodeableConcept $countryOfOrigin): self
    {
        if (!isset($this->countryOfOrigin)) {
            $this->countryOfOrigin = [];
        }
        $this->countryOfOrigin[] = $countryOfOrigin;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country where the plant material is harvested or the countries where the
     * plasma is sourced from as laid down in accordance with the Plasma Master File.
     * For “Plasma-derived substances” the attribute country of origin provides
     * information about the countries used for the manufacturing of the Cryopoor plama
     * or Crioprecipitate.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$countryOfOrigin
     * @return static
     */
    public function setCountryOfOrigin(FHIRCodeableConcept ...$countryOfOrigin): self
    {
        if ([] === $countryOfOrigin) {
            unset($this->countryOfOrigin);
            return $this;
        }
        $this->countryOfOrigin = $countryOfOrigin;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The place/region where the plant is harvested or the places/regions where the
     * animal source material has its habitat.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getGeographicalLocation(): array
    {
        return $this->geographicalLocation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getGeographicalLocationIterator(): iterable
    {
        if (!isset($this->geographicalLocation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->geographicalLocation);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The place/region where the plant is harvested or the places/regions where the
     * animal source material has its habitat.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $geographicalLocation
     * @return static
     */
    public function addGeographicalLocation(string|FHIRStringPrimitive|FHIRString $geographicalLocation): self
    {
        if (!($geographicalLocation instanceof FHIRString)) {
            $geographicalLocation = new FHIRString(value: $geographicalLocation);
        }
        if (!isset($this->geographicalLocation)) {
            $this->geographicalLocation = [];
        }
        $this->geographicalLocation[] = $geographicalLocation;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The place/region where the plant is harvested or the places/regions where the
     * animal source material has its habitat.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$geographicalLocation
     * @return static
     */
    public function setGeographicalLocation(string|FHIRStringPrimitive|FHIRString ...$geographicalLocation): self
    {
        if ([] === $geographicalLocation) {
            unset($this->geographicalLocation);
            return $this;
        }
        $this->geographicalLocation = [];
        foreach($geographicalLocation as $v) {
            if ($v instanceof FHIRString) {
                $this->geographicalLocation[] = $v;
            } else {
                $this->geographicalLocation[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Stage of life for animals, plants, insects and microorganisms. This information
     * shall be provided only when the substance is significantly different in these
     * stages (e.g. foetal bovine serum).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDevelopmentStage(): null|FHIRCodeableConcept
    {
        return $this->developmentStage ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Stage of life for animals, plants, insects and microorganisms. This information
     * shall be provided only when the substance is significantly different in these
     * stages (e.g. foetal bovine serum).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $developmentStage
     * @return static
     */
    public function setDevelopmentStage(null|FHIRCodeableConcept $developmentStage): self
    {
        if (null === $developmentStage) {
            unset($this->developmentStage);
            return $this;
        }
        $this->developmentStage = $developmentStage;
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
     * Many complex materials are fractions of parts of plants, animals, or minerals.
     * Fraction elements are often necessary to define both Substances and Specified
     * Group 1 Substances. For substances derived from Plants, fraction information
     * will be captured at the Substance information level ( . Oils, Juices and
     * Exudates). Additional information for Extracts, such as extraction solvent
     * composition, will be captured at the Specified Substance Group 1 information
     * level. For plasma-derived products fraction information will be captured at the
     * Substance and the Specified Substance Group 1 levels.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription>
     */
    public function getFractionDescription(): array
    {
        return $this->fractionDescription ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription>
     */
    public function getFractionDescriptionIterator(): iterable
    {
        if (!isset($this->fractionDescription)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->fractionDescription);
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
     * Many complex materials are fractions of parts of plants, animals, or minerals.
     * Fraction elements are often necessary to define both Substances and Specified
     * Group 1 Substances. For substances derived from Plants, fraction information
     * will be captured at the Substance information level ( . Oils, Juices and
     * Exudates). Additional information for Extracts, such as extraction solvent
     * composition, will be captured at the Specified Substance Group 1 information
     * level. For plasma-derived products fraction information will be captured at the
     * Substance and the Specified Substance Group 1 levels.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription $fractionDescription
     * @return static
     */
    public function addFractionDescription(FHIRSubstanceSourceMaterialFractionDescription $fractionDescription): self
    {
        if (!isset($this->fractionDescription)) {
            $this->fractionDescription = [];
        }
        $this->fractionDescription[] = $fractionDescription;
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
     * Many complex materials are fractions of parts of plants, animals, or minerals.
     * Fraction elements are often necessary to define both Substances and Specified
     * Group 1 Substances. For substances derived from Plants, fraction information
     * will be captured at the Substance information level ( . Oils, Juices and
     * Exudates). Additional information for Extracts, such as extraction solvent
     * composition, will be captured at the Specified Substance Group 1 information
     * level. For plasma-derived products fraction information will be captured at the
     * Substance and the Specified Substance Group 1 levels.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription ...$fractionDescription
     * @return static
     */
    public function setFractionDescription(FHIRSubstanceSourceMaterialFractionDescription ...$fractionDescription): self
    {
        if ([] === $fractionDescription) {
            unset($this->fractionDescription);
            return $this;
        }
        $this->fractionDescription = $fractionDescription;
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
     * This subclause describes the organism which the substance is derived from. For
     * vaccines, the parent organism shall be specified based on these subclause
     * elements. As an example, full taxonomy will be described for the Substance Name:
     * ., Leaf.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism
     */
    public function getOrganism(): null|FHIRSubstanceSourceMaterialOrganism
    {
        return $this->organism ?? null;
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
     * This subclause describes the organism which the substance is derived from. For
     * vaccines, the parent organism shall be specified based on these subclause
     * elements. As an example, full taxonomy will be described for the Substance Name:
     * ., Leaf.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism $organism
     * @return static
     */
    public function setOrganism(null|FHIRSubstanceSourceMaterialOrganism $organism): self
    {
        if (null === $organism) {
            unset($this->organism);
            return $this;
        }
        $this->organism = $organism;
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
     * To do.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription>
     */
    public function getPartDescription(): array
    {
        return $this->partDescription ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription>
     */
    public function getPartDescriptionIterator(): iterable
    {
        if (!isset($this->partDescription)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->partDescription);
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
     * To do.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription $partDescription
     * @return static
     */
    public function addPartDescription(FHIRSubstanceSourceMaterialPartDescription $partDescription): self
    {
        if (!isset($this->partDescription)) {
            $this->partDescription = [];
        }
        $this->partDescription[] = $partDescription;
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
     * To do.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription ...$partDescription
     * @return static
     */
    public function setPartDescription(FHIRSubstanceSourceMaterialPartDescription ...$partDescription): self
    {
        if ([] === $partDescription) {
            unset($this->partDescription);
            return $this;
        }
        $this->partDescription = $partDescription;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSourceMaterial $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSourceMaterial
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceSourceMaterial)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRNarrative::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINED === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->addContained($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_MATERIAL_CLASS === $cen) {
                $type->setSourceMaterialClass(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_MATERIAL_TYPE === $cen) {
                $type->setSourceMaterialType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE_MATERIAL_STATE === $cen) {
                $type->setSourceMaterialState(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORGANISM_ID === $cen) {
                $type->setOrganismId(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORGANISM_NAME === $cen) {
                $type->setOrganismName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARENT_SUBSTANCE_ID === $cen) {
                $type->addParentSubstanceId(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARENT_SUBSTANCE_NAME === $cen) {
                $type->addParentSubstanceName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COUNTRY_OF_ORIGIN === $cen) {
                $type->addCountryOfOrigin(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GEOGRAPHICAL_LOCATION === $cen) {
                $type->addGeographicalLocation(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEVELOPMENT_STAGE === $cen) {
                $type->setDevelopmentStage(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FRACTION_DESCRIPTION === $cen) {
                $type->addFractionDescription(FHIRSubstanceSourceMaterialFractionDescription::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORGANISM === $cen) {
                $type->setOrganism(FHIRSubstanceSourceMaterialOrganism::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PART_DESCRIPTION === $cen) {
                $type->addPartDescription(FHIRSubstanceSourceMaterialPartDescription::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ORGANISM_NAME])) {
            if (isset($type->organismName)) {
                $type->organismName->setValue((string)$attributes[self::FIELD_ORGANISM_NAME]);
            } else {
                $type->setOrganismName((string)$attributes[self::FIELD_ORGANISM_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ORGANISM_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('SubstanceSourceMaterial', $this->_getSourceXMLNS());
        }
        if (isset($this->organismName) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ORGANISM_NAME]) {
            $xw->writeAttribute(self::FIELD_ORGANISM_NAME, $this->organismName->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->sourceMaterialClass)) {
            $xw->startElement(self::FIELD_SOURCE_MATERIAL_CLASS);
            $this->sourceMaterialClass->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->sourceMaterialType)) {
            $xw->startElement(self::FIELD_SOURCE_MATERIAL_TYPE);
            $this->sourceMaterialType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->sourceMaterialState)) {
            $xw->startElement(self::FIELD_SOURCE_MATERIAL_STATE);
            $this->sourceMaterialState->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->organismId)) {
            $xw->startElement(self::FIELD_ORGANISM_ID);
            $this->organismId->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->organismName)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ORGANISM_NAME]
                || $this->organismName->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ORGANISM_NAME);
            $this->organismName->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ORGANISM_NAME]);
            $xw->endElement();
        }
        if (isset($this->parentSubstanceId)) {
            foreach ($this->parentSubstanceId as $v) {
                $xw->startElement(self::FIELD_PARENT_SUBSTANCE_ID);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->parentSubstanceName) && [] !== $this->parentSubstanceName) {
            foreach($this->parentSubstanceName as $v) {
                $xw->startElement(self::FIELD_PARENT_SUBSTANCE_NAME);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->countryOfOrigin)) {
            foreach ($this->countryOfOrigin as $v) {
                $xw->startElement(self::FIELD_COUNTRY_OF_ORIGIN);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->geographicalLocation) && [] !== $this->geographicalLocation) {
            foreach($this->geographicalLocation as $v) {
                $xw->startElement(self::FIELD_GEOGRAPHICAL_LOCATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->developmentStage)) {
            $xw->startElement(self::FIELD_DEVELOPMENT_STAGE);
            $this->developmentStage->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->fractionDescription)) {
            foreach ($this->fractionDescription as $v) {
                $xw->startElement(self::FIELD_FRACTION_DESCRIPTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->organism)) {
            $xw->startElement(self::FIELD_ORGANISM);
            $this->organism->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->partDescription)) {
            foreach ($this->partDescription as $v) {
                $xw->startElement(self::FIELD_PART_DESCRIPTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSourceMaterial $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRSubstanceSourceMaterial
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIRSubstanceSourceMaterial)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->sourceMaterialClass) || property_exists($decoded, self::FIELD_SOURCE_MATERIAL_CLASS)) {
            if (is_array($decoded->sourceMaterialClass)) {
                $type->setSourceMaterialClass(FHIRCodeableConcept::jsonUnserialize(reset($decoded->sourceMaterialClass), $config));
            } else {
                $type->setSourceMaterialClass(FHIRCodeableConcept::jsonUnserialize($decoded->sourceMaterialClass, $config));
            }
        }
        if (isset($decoded->sourceMaterialType) || property_exists($decoded, self::FIELD_SOURCE_MATERIAL_TYPE)) {
            if (is_array($decoded->sourceMaterialType)) {
                $type->setSourceMaterialType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->sourceMaterialType), $config));
            } else {
                $type->setSourceMaterialType(FHIRCodeableConcept::jsonUnserialize($decoded->sourceMaterialType, $config));
            }
        }
        if (isset($decoded->sourceMaterialState) || property_exists($decoded, self::FIELD_SOURCE_MATERIAL_STATE)) {
            if (is_array($decoded->sourceMaterialState)) {
                $type->setSourceMaterialState(FHIRCodeableConcept::jsonUnserialize(reset($decoded->sourceMaterialState), $config));
            } else {
                $type->setSourceMaterialState(FHIRCodeableConcept::jsonUnserialize($decoded->sourceMaterialState, $config));
            }
        }
        if (isset($decoded->organismId) || property_exists($decoded, self::FIELD_ORGANISM_ID)) {
            if (is_array($decoded->organismId)) {
                $type->setOrganismId(FHIRIdentifier::jsonUnserialize(reset($decoded->organismId), $config));
            } else {
                $type->setOrganismId(FHIRIdentifier::jsonUnserialize($decoded->organismId, $config));
            }
        }
        if (isset($decoded->organismName)
            || isset($decoded->_organismName)
            || property_exists($decoded, self::FIELD_ORGANISM_NAME)
            || property_exists($decoded, self::FIELD_ORGANISM_NAME_EXT)) {
            $v = $decoded->_organismName ?? new \stdClass();
            $v->value = $decoded->organismName ?? null;
            $type->setOrganismName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->parentSubstanceId) || property_exists($decoded, self::FIELD_PARENT_SUBSTANCE_ID)) {
            if (is_object($decoded->parentSubstanceId)) {
                $vals = [$decoded->parentSubstanceId];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PARENT_SUBSTANCE_ID, true);
            } else {
                $vals = $decoded->parentSubstanceId;
            }
            foreach($vals as $v) {
                $type->addParentSubstanceId(FHIRIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->parentSubstanceName)
            || isset($decoded->_parentSubstanceName)
            || property_exists($decoded, self::FIELD_PARENT_SUBSTANCE_NAME)
            || property_exists($decoded, self::FIELD_PARENT_SUBSTANCE_NAME_EXT)) {
            $vals = (array)($decoded->parentSubstanceName ?? []);
            $exts = (array)($decoded->_parentSubstanceName ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addParentSubstanceName(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->countryOfOrigin) || property_exists($decoded, self::FIELD_COUNTRY_OF_ORIGIN)) {
            if (is_object($decoded->countryOfOrigin)) {
                $vals = [$decoded->countryOfOrigin];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COUNTRY_OF_ORIGIN, true);
            } else {
                $vals = $decoded->countryOfOrigin;
            }
            foreach($vals as $v) {
                $type->addCountryOfOrigin(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->geographicalLocation)
            || isset($decoded->_geographicalLocation)
            || property_exists($decoded, self::FIELD_GEOGRAPHICAL_LOCATION)
            || property_exists($decoded, self::FIELD_GEOGRAPHICAL_LOCATION_EXT)) {
            $vals = (array)($decoded->geographicalLocation ?? []);
            $exts = (array)($decoded->_geographicalLocation ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addGeographicalLocation(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->developmentStage) || property_exists($decoded, self::FIELD_DEVELOPMENT_STAGE)) {
            if (is_array($decoded->developmentStage)) {
                $type->setDevelopmentStage(FHIRCodeableConcept::jsonUnserialize(reset($decoded->developmentStage), $config));
            } else {
                $type->setDevelopmentStage(FHIRCodeableConcept::jsonUnserialize($decoded->developmentStage, $config));
            }
        }
        if (isset($decoded->fractionDescription) || property_exists($decoded, self::FIELD_FRACTION_DESCRIPTION)) {
            if (is_object($decoded->fractionDescription)) {
                $vals = [$decoded->fractionDescription];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_FRACTION_DESCRIPTION, true);
            } else {
                $vals = $decoded->fractionDescription;
            }
            foreach($vals as $v) {
                $type->addFractionDescription(FHIRSubstanceSourceMaterialFractionDescription::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->organism) || property_exists($decoded, self::FIELD_ORGANISM)) {
            if (is_array($decoded->organism)) {
                $type->setOrganism(FHIRSubstanceSourceMaterialOrganism::jsonUnserialize(reset($decoded->organism), $config));
            } else {
                $type->setOrganism(FHIRSubstanceSourceMaterialOrganism::jsonUnserialize($decoded->organism, $config));
            }
        }
        if (isset($decoded->partDescription) || property_exists($decoded, self::FIELD_PART_DESCRIPTION)) {
            if (is_object($decoded->partDescription)) {
                $vals = [$decoded->partDescription];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PART_DESCRIPTION, true);
            } else {
                $vals = $decoded->partDescription;
            }
            foreach($vals as $v) {
                $type->addPartDescription(FHIRSubstanceSourceMaterialPartDescription::jsonUnserialize($v, $config));
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
        if (isset($this->sourceMaterialClass)) {
            $out->sourceMaterialClass = $this->sourceMaterialClass;
        }
        if (isset($this->sourceMaterialType)) {
            $out->sourceMaterialType = $this->sourceMaterialType;
        }
        if (isset($this->sourceMaterialState)) {
            $out->sourceMaterialState = $this->sourceMaterialState;
        }
        if (isset($this->organismId)) {
            $out->organismId = $this->organismId;
        }
        if (isset($this->organismName)) {
            if (null !== ($val = $this->organismName->getValue())) {
                $out->organismName = $val;
            }
            if ($this->organismName->_nonValueFieldDefined()) {
                $ext = $this->organismName->jsonSerialize();
                unset($ext->value);
                $out->_organismName = $ext;
            }
        }
        if (isset($this->parentSubstanceId) && [] !== $this->parentSubstanceId) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PARENT_SUBSTANCE_ID) && 1 === count($this->parentSubstanceId)) {
                $out->parentSubstanceId = $this->parentSubstanceId[0];
            } else {
                $out->parentSubstanceId = $this->parentSubstanceId;
            }
        }
        if (isset($this->parentSubstanceName) && [] !== $this->parentSubstanceName) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->parentSubstanceName as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->parentSubstanceName = $vals;
            }
            if ($hasExts) {
                $out->_parentSubstanceName = $exts;
            }
        }
        if (isset($this->countryOfOrigin) && [] !== $this->countryOfOrigin) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COUNTRY_OF_ORIGIN) && 1 === count($this->countryOfOrigin)) {
                $out->countryOfOrigin = $this->countryOfOrigin[0];
            } else {
                $out->countryOfOrigin = $this->countryOfOrigin;
            }
        }
        if (isset($this->geographicalLocation) && [] !== $this->geographicalLocation) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->geographicalLocation as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->geographicalLocation = $vals;
            }
            if ($hasExts) {
                $out->_geographicalLocation = $exts;
            }
        }
        if (isset($this->developmentStage)) {
            $out->developmentStage = $this->developmentStage;
        }
        if (isset($this->fractionDescription) && [] !== $this->fractionDescription) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_FRACTION_DESCRIPTION) && 1 === count($this->fractionDescription)) {
                $out->fractionDescription = $this->fractionDescription[0];
            } else {
                $out->fractionDescription = $this->fractionDescription;
            }
        }
        if (isset($this->organism)) {
            $out->organism = $this->organism;
        }
        if (isset($this->partDescription) && [] !== $this->partDescription) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PART_DESCRIPTION) && 1 === count($this->partDescription)) {
                $out->partDescription = $this->partDescription[0];
            } else {
                $out->partDescription = $this->partDescription;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
