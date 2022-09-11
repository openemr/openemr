<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: September 10th, 2022 20:42+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2022 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeMap;

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
 *
 * Class FHIRSubstanceSourceMaterial
 * @package \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource
 */
class FHIRSubstanceSourceMaterial extends FHIRDomainResource implements PHPFHIRContainedTypeInterface
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL;
    const FIELD_SOURCE_MATERIAL_CLASS = 'sourceMaterialClass';
    const FIELD_SOURCE_MATERIAL_TYPE = 'sourceMaterialType';
    const FIELD_SOURCE_MATERIAL_STATE = 'sourceMaterialState';
    const FIELD_ORGANISM_ID = 'organismId';
    const FIELD_ORGANISM_NAME = 'organismName';
    const FIELD_ORGANISM_NAME_EXT = '_organismName';
    const FIELD_PARENT_SUBSTANCE_ID = 'parentSubstanceId';
    const FIELD_PARENT_SUBSTANCE_NAME = 'parentSubstanceName';
    const FIELD_PARENT_SUBSTANCE_NAME_EXT = '_parentSubstanceName';
    const FIELD_COUNTRY_OF_ORIGIN = 'countryOfOrigin';
    const FIELD_GEOGRAPHICAL_LOCATION = 'geographicalLocation';
    const FIELD_GEOGRAPHICAL_LOCATION_EXT = '_geographicalLocation';
    const FIELD_DEVELOPMENT_STAGE = 'developmentStage';
    const FIELD_FRACTION_DESCRIPTION = 'fractionDescription';
    const FIELD_ORGANISM = 'organism';
    const FIELD_PART_DESCRIPTION = 'partDescription';

    /** @var string */
    private $_xmlns = '';

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * General high level classification of the source material specific to the origin
     * of the material.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $sourceMaterialClass = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $sourceMaterialType = null;

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The state of the source material when extracted.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $sourceMaterialState = null;

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The unique identifier associated with the source material parent organism shall
     * be specified.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    protected $organismId = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The organism accepted Scientific name shall be provided based on the organism
     * taxonomy.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $organismName = null;

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The parent of the herbal drug Ginkgo biloba, Leaf is the substance ID of the
     * substance (fresh) of Ginkgo biloba L. or Ginkgo biloba L. (Whole plant).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    protected $parentSubstanceId = [];

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The parent substance of the Herbal Drug, or Herbal preparation.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    protected $parentSubstanceName = [];

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    protected $countryOfOrigin = [];

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The place/region where the plant is harvested or the places/regions where the
     * animal source material has its habitat.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    protected $geographicalLocation = [];

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $developmentStage = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription[]
     */
    protected $fractionDescription = [];

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism
     */
    protected $organism = null;

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
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription[]
     */
    protected $partDescription = [];

    /**
     * Validation map for fields in type SubstanceSourceMaterial
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRSubstanceSourceMaterial Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRSubstanceSourceMaterial::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_SOURCE_MATERIAL_CLASS])) {
            if ($data[self::FIELD_SOURCE_MATERIAL_CLASS] instanceof FHIRCodeableConcept) {
                $this->setSourceMaterialClass($data[self::FIELD_SOURCE_MATERIAL_CLASS]);
            } else {
                $this->setSourceMaterialClass(new FHIRCodeableConcept($data[self::FIELD_SOURCE_MATERIAL_CLASS]));
            }
        }
        if (isset($data[self::FIELD_SOURCE_MATERIAL_TYPE])) {
            if ($data[self::FIELD_SOURCE_MATERIAL_TYPE] instanceof FHIRCodeableConcept) {
                $this->setSourceMaterialType($data[self::FIELD_SOURCE_MATERIAL_TYPE]);
            } else {
                $this->setSourceMaterialType(new FHIRCodeableConcept($data[self::FIELD_SOURCE_MATERIAL_TYPE]));
            }
        }
        if (isset($data[self::FIELD_SOURCE_MATERIAL_STATE])) {
            if ($data[self::FIELD_SOURCE_MATERIAL_STATE] instanceof FHIRCodeableConcept) {
                $this->setSourceMaterialState($data[self::FIELD_SOURCE_MATERIAL_STATE]);
            } else {
                $this->setSourceMaterialState(new FHIRCodeableConcept($data[self::FIELD_SOURCE_MATERIAL_STATE]));
            }
        }
        if (isset($data[self::FIELD_ORGANISM_ID])) {
            if ($data[self::FIELD_ORGANISM_ID] instanceof FHIRIdentifier) {
                $this->setOrganismId($data[self::FIELD_ORGANISM_ID]);
            } else {
                $this->setOrganismId(new FHIRIdentifier($data[self::FIELD_ORGANISM_ID]));
            }
        }
        if (isset($data[self::FIELD_ORGANISM_NAME]) || isset($data[self::FIELD_ORGANISM_NAME_EXT])) {
            $value = isset($data[self::FIELD_ORGANISM_NAME]) ? $data[self::FIELD_ORGANISM_NAME] : null;
            $ext = (isset($data[self::FIELD_ORGANISM_NAME_EXT]) && is_array($data[self::FIELD_ORGANISM_NAME_EXT])) ? $ext = $data[self::FIELD_ORGANISM_NAME_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setOrganismName($value);
                } else if (is_array($value)) {
                    $this->setOrganismName(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setOrganismName(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setOrganismName(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_PARENT_SUBSTANCE_ID])) {
            if (is_array($data[self::FIELD_PARENT_SUBSTANCE_ID])) {
                foreach ($data[self::FIELD_PARENT_SUBSTANCE_ID] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRIdentifier) {
                        $this->addParentSubstanceId($v);
                    } else {
                        $this->addParentSubstanceId(new FHIRIdentifier($v));
                    }
                }
            } elseif ($data[self::FIELD_PARENT_SUBSTANCE_ID] instanceof FHIRIdentifier) {
                $this->addParentSubstanceId($data[self::FIELD_PARENT_SUBSTANCE_ID]);
            } else {
                $this->addParentSubstanceId(new FHIRIdentifier($data[self::FIELD_PARENT_SUBSTANCE_ID]));
            }
        }
        if (isset($data[self::FIELD_PARENT_SUBSTANCE_NAME]) || isset($data[self::FIELD_PARENT_SUBSTANCE_NAME_EXT])) {
            $value = isset($data[self::FIELD_PARENT_SUBSTANCE_NAME]) ? $data[self::FIELD_PARENT_SUBSTANCE_NAME] : null;
            $ext = (isset($data[self::FIELD_PARENT_SUBSTANCE_NAME_EXT]) && is_array($data[self::FIELD_PARENT_SUBSTANCE_NAME_EXT])) ? $ext = $data[self::FIELD_PARENT_SUBSTANCE_NAME_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->addParentSubstanceName($value);
                } else if (is_array($value)) {
                    foreach ($value as $i => $v) {
                        if ($v instanceof FHIRString) {
                            $this->addParentSubstanceName($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addParentSubstanceName(new FHIRString(array_merge($v, $iext)));
                            } else {
                                $this->addParentSubstanceName(new FHIRString([FHIRString::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addParentSubstanceName(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->addParentSubstanceName(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach ($ext as $iext) {
                    $this->addParentSubstanceName(new FHIRString($iext));
                }
            }
        }
        if (isset($data[self::FIELD_COUNTRY_OF_ORIGIN])) {
            if (is_array($data[self::FIELD_COUNTRY_OF_ORIGIN])) {
                foreach ($data[self::FIELD_COUNTRY_OF_ORIGIN] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRCodeableConcept) {
                        $this->addCountryOfOrigin($v);
                    } else {
                        $this->addCountryOfOrigin(new FHIRCodeableConcept($v));
                    }
                }
            } elseif ($data[self::FIELD_COUNTRY_OF_ORIGIN] instanceof FHIRCodeableConcept) {
                $this->addCountryOfOrigin($data[self::FIELD_COUNTRY_OF_ORIGIN]);
            } else {
                $this->addCountryOfOrigin(new FHIRCodeableConcept($data[self::FIELD_COUNTRY_OF_ORIGIN]));
            }
        }
        if (isset($data[self::FIELD_GEOGRAPHICAL_LOCATION]) || isset($data[self::FIELD_GEOGRAPHICAL_LOCATION_EXT])) {
            $value = isset($data[self::FIELD_GEOGRAPHICAL_LOCATION]) ? $data[self::FIELD_GEOGRAPHICAL_LOCATION] : null;
            $ext = (isset($data[self::FIELD_GEOGRAPHICAL_LOCATION_EXT]) && is_array($data[self::FIELD_GEOGRAPHICAL_LOCATION_EXT])) ? $ext = $data[self::FIELD_GEOGRAPHICAL_LOCATION_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->addGeographicalLocation($value);
                } else if (is_array($value)) {
                    foreach ($value as $i => $v) {
                        if ($v instanceof FHIRString) {
                            $this->addGeographicalLocation($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addGeographicalLocation(new FHIRString(array_merge($v, $iext)));
                            } else {
                                $this->addGeographicalLocation(new FHIRString([FHIRString::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addGeographicalLocation(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->addGeographicalLocation(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach ($ext as $iext) {
                    $this->addGeographicalLocation(new FHIRString($iext));
                }
            }
        }
        if (isset($data[self::FIELD_DEVELOPMENT_STAGE])) {
            if ($data[self::FIELD_DEVELOPMENT_STAGE] instanceof FHIRCodeableConcept) {
                $this->setDevelopmentStage($data[self::FIELD_DEVELOPMENT_STAGE]);
            } else {
                $this->setDevelopmentStage(new FHIRCodeableConcept($data[self::FIELD_DEVELOPMENT_STAGE]));
            }
        }
        if (isset($data[self::FIELD_FRACTION_DESCRIPTION])) {
            if (is_array($data[self::FIELD_FRACTION_DESCRIPTION])) {
                foreach ($data[self::FIELD_FRACTION_DESCRIPTION] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRSubstanceSourceMaterialFractionDescription) {
                        $this->addFractionDescription($v);
                    } else {
                        $this->addFractionDescription(new FHIRSubstanceSourceMaterialFractionDescription($v));
                    }
                }
            } elseif ($data[self::FIELD_FRACTION_DESCRIPTION] instanceof FHIRSubstanceSourceMaterialFractionDescription) {
                $this->addFractionDescription($data[self::FIELD_FRACTION_DESCRIPTION]);
            } else {
                $this->addFractionDescription(new FHIRSubstanceSourceMaterialFractionDescription($data[self::FIELD_FRACTION_DESCRIPTION]));
            }
        }
        if (isset($data[self::FIELD_ORGANISM])) {
            if ($data[self::FIELD_ORGANISM] instanceof FHIRSubstanceSourceMaterialOrganism) {
                $this->setOrganism($data[self::FIELD_ORGANISM]);
            } else {
                $this->setOrganism(new FHIRSubstanceSourceMaterialOrganism($data[self::FIELD_ORGANISM]));
            }
        }
        if (isset($data[self::FIELD_PART_DESCRIPTION])) {
            if (is_array($data[self::FIELD_PART_DESCRIPTION])) {
                foreach ($data[self::FIELD_PART_DESCRIPTION] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRSubstanceSourceMaterialPartDescription) {
                        $this->addPartDescription($v);
                    } else {
                        $this->addPartDescription(new FHIRSubstanceSourceMaterialPartDescription($v));
                    }
                }
            } elseif ($data[self::FIELD_PART_DESCRIPTION] instanceof FHIRSubstanceSourceMaterialPartDescription) {
                $this->addPartDescription($data[self::FIELD_PART_DESCRIPTION]);
            } else {
                $this->addPartDescription(new FHIRSubstanceSourceMaterialPartDescription($data[self::FIELD_PART_DESCRIPTION]));
            }
        }
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if ('' !==  $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<SubstanceSourceMaterial{$xmlns}></SubstanceSourceMaterial>";
    }
    /**
     * @return string
     */
    public function _getResourceType()
    {
        return static::FHIR_TYPE_NAME;
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSourceMaterialClass()
    {
        return $this->sourceMaterialClass;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $sourceMaterialClass
     * @return static
     */
    public function setSourceMaterialClass(FHIRCodeableConcept $sourceMaterialClass = null)
    {
        $this->_trackValueSet($this->sourceMaterialClass, $sourceMaterialClass);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSourceMaterialType()
    {
        return $this->sourceMaterialType;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $sourceMaterialType
     * @return static
     */
    public function setSourceMaterialType(FHIRCodeableConcept $sourceMaterialType = null)
    {
        $this->_trackValueSet($this->sourceMaterialType, $sourceMaterialType);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSourceMaterialState()
    {
        return $this->sourceMaterialState;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The state of the source material when extracted.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $sourceMaterialState
     * @return static
     */
    public function setSourceMaterialState(FHIRCodeableConcept $sourceMaterialState = null)
    {
        $this->_trackValueSet($this->sourceMaterialState, $sourceMaterialState);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getOrganismId()
    {
        return $this->organismId;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $organismId
     * @return static
     */
    public function setOrganismId(FHIRIdentifier $organismId = null)
    {
        $this->_trackValueSet($this->organismId, $organismId);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getOrganismName()
    {
        return $this->organismName;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The organism accepted Scientific name shall be provided based on the organism
     * taxonomy.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $organismName
     * @return static
     */
    public function setOrganismName($organismName = null)
    {
        if (null !== $organismName && !($organismName instanceof FHIRString)) {
            $organismName = new FHIRString($organismName);
        }
        $this->_trackValueSet($this->organismName, $organismName);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getParentSubstanceId()
    {
        return $this->parentSubstanceId;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $parentSubstanceId
     * @return static
     */
    public function addParentSubstanceId(FHIRIdentifier $parentSubstanceId = null)
    {
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[] $parentSubstanceId
     * @return static
     */
    public function setParentSubstanceId(array $parentSubstanceId = [])
    {
        if ([] !== $this->parentSubstanceId) {
            $this->_trackValuesRemoved(count($this->parentSubstanceId));
            $this->parentSubstanceId = [];
        }
        if ([] === $parentSubstanceId) {
            return $this;
        }
        foreach ($parentSubstanceId as $v) {
            if ($v instanceof FHIRIdentifier) {
                $this->addParentSubstanceId($v);
            } else {
                $this->addParentSubstanceId(new FHIRIdentifier($v));
            }
        }
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The parent substance of the Herbal Drug, or Herbal preparation.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getParentSubstanceName()
    {
        return $this->parentSubstanceName;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The parent substance of the Herbal Drug, or Herbal preparation.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $parentSubstanceName
     * @return static
     */
    public function addParentSubstanceName($parentSubstanceName = null)
    {
        if (null !== $parentSubstanceName && !($parentSubstanceName instanceof FHIRString)) {
            $parentSubstanceName = new FHIRString($parentSubstanceName);
        }
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString[] $parentSubstanceName
     * @return static
     */
    public function setParentSubstanceName(array $parentSubstanceName = [])
    {
        if ([] !== $this->parentSubstanceName) {
            $this->_trackValuesRemoved(count($this->parentSubstanceName));
            $this->parentSubstanceName = [];
        }
        if ([] === $parentSubstanceName) {
            return $this;
        }
        foreach ($parentSubstanceName as $v) {
            if ($v instanceof FHIRString) {
                $this->addParentSubstanceName($v);
            } else {
                $this->addParentSubstanceName(new FHIRString($v));
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCountryOfOrigin()
    {
        return $this->countryOfOrigin;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $countryOfOrigin
     * @return static
     */
    public function addCountryOfOrigin(FHIRCodeableConcept $countryOfOrigin = null)
    {
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[] $countryOfOrigin
     * @return static
     */
    public function setCountryOfOrigin(array $countryOfOrigin = [])
    {
        if ([] !== $this->countryOfOrigin) {
            $this->_trackValuesRemoved(count($this->countryOfOrigin));
            $this->countryOfOrigin = [];
        }
        if ([] === $countryOfOrigin) {
            return $this;
        }
        foreach ($countryOfOrigin as $v) {
            if ($v instanceof FHIRCodeableConcept) {
                $this->addCountryOfOrigin($v);
            } else {
                $this->addCountryOfOrigin(new FHIRCodeableConcept($v));
            }
        }
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getGeographicalLocation()
    {
        return $this->geographicalLocation;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The place/region where the plant is harvested or the places/regions where the
     * animal source material has its habitat.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $geographicalLocation
     * @return static
     */
    public function addGeographicalLocation($geographicalLocation = null)
    {
        if (null !== $geographicalLocation && !($geographicalLocation instanceof FHIRString)) {
            $geographicalLocation = new FHIRString($geographicalLocation);
        }
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString[] $geographicalLocation
     * @return static
     */
    public function setGeographicalLocation(array $geographicalLocation = [])
    {
        if ([] !== $this->geographicalLocation) {
            $this->_trackValuesRemoved(count($this->geographicalLocation));
            $this->geographicalLocation = [];
        }
        if ([] === $geographicalLocation) {
            return $this;
        }
        foreach ($geographicalLocation as $v) {
            if ($v instanceof FHIRString) {
                $this->addGeographicalLocation($v);
            } else {
                $this->addGeographicalLocation(new FHIRString($v));
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDevelopmentStage()
    {
        return $this->developmentStage;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $developmentStage
     * @return static
     */
    public function setDevelopmentStage(FHIRCodeableConcept $developmentStage = null)
    {
        $this->_trackValueSet($this->developmentStage, $developmentStage);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription[]
     */
    public function getFractionDescription()
    {
        return $this->fractionDescription;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription $fractionDescription
     * @return static
     */
    public function addFractionDescription(FHIRSubstanceSourceMaterialFractionDescription $fractionDescription = null)
    {
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription[] $fractionDescription
     * @return static
     */
    public function setFractionDescription(array $fractionDescription = [])
    {
        if ([] !== $this->fractionDescription) {
            $this->_trackValuesRemoved(count($this->fractionDescription));
            $this->fractionDescription = [];
        }
        if ([] === $fractionDescription) {
            return $this;
        }
        foreach ($fractionDescription as $v) {
            if ($v instanceof FHIRSubstanceSourceMaterialFractionDescription) {
                $this->addFractionDescription($v);
            } else {
                $this->addFractionDescription(new FHIRSubstanceSourceMaterialFractionDescription($v));
            }
        }
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism
     */
    public function getOrganism()
    {
        return $this->organism;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism $organism
     * @return static
     */
    public function setOrganism(FHIRSubstanceSourceMaterialOrganism $organism = null)
    {
        $this->_trackValueSet($this->organism, $organism);
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
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription[]
     */
    public function getPartDescription()
    {
        return $this->partDescription;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription $partDescription
     * @return static
     */
    public function addPartDescription(FHIRSubstanceSourceMaterialPartDescription $partDescription = null)
    {
        $this->_trackValueAdded();
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
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription[] $partDescription
     * @return static
     */
    public function setPartDescription(array $partDescription = [])
    {
        if ([] !== $this->partDescription) {
            $this->_trackValuesRemoved(count($this->partDescription));
            $this->partDescription = [];
        }
        if ([] === $partDescription) {
            return $this;
        }
        foreach ($partDescription as $v) {
            if ($v instanceof FHIRSubstanceSourceMaterialPartDescription) {
                $this->addPartDescription($v);
            } else {
                $this->addPartDescription(new FHIRSubstanceSourceMaterialPartDescription($v));
            }
        }
        return $this;
    }

    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = parent::_getValidationErrors();
        $validationRules = $this->_getValidationRules();
        if (null !== ($v = $this->getSourceMaterialClass())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_SOURCE_MATERIAL_CLASS] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getSourceMaterialType())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_SOURCE_MATERIAL_TYPE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getSourceMaterialState())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_SOURCE_MATERIAL_STATE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getOrganismId())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ORGANISM_ID] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getOrganismName())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ORGANISM_NAME] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getParentSubstanceId())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_PARENT_SUBSTANCE_ID, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getParentSubstanceName())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_PARENT_SUBSTANCE_NAME, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getCountryOfOrigin())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_COUNTRY_OF_ORIGIN, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getGeographicalLocation())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_GEOGRAPHICAL_LOCATION, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getDevelopmentStage())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_DEVELOPMENT_STAGE] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getFractionDescription())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_FRACTION_DESCRIPTION, $i)] = $fieldErrs;
                }
            }
        }
        if (null !== ($v = $this->getOrganism())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ORGANISM] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getPartDescription())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_PART_DESCRIPTION, $i)] = $fieldErrs;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SOURCE_MATERIAL_CLASS])) {
            $v = $this->getSourceMaterialClass();
            foreach ($validationRules[self::FIELD_SOURCE_MATERIAL_CLASS] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_SOURCE_MATERIAL_CLASS, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SOURCE_MATERIAL_CLASS])) {
                        $errs[self::FIELD_SOURCE_MATERIAL_CLASS] = [];
                    }
                    $errs[self::FIELD_SOURCE_MATERIAL_CLASS][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SOURCE_MATERIAL_TYPE])) {
            $v = $this->getSourceMaterialType();
            foreach ($validationRules[self::FIELD_SOURCE_MATERIAL_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_SOURCE_MATERIAL_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SOURCE_MATERIAL_TYPE])) {
                        $errs[self::FIELD_SOURCE_MATERIAL_TYPE] = [];
                    }
                    $errs[self::FIELD_SOURCE_MATERIAL_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SOURCE_MATERIAL_STATE])) {
            $v = $this->getSourceMaterialState();
            foreach ($validationRules[self::FIELD_SOURCE_MATERIAL_STATE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_SOURCE_MATERIAL_STATE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SOURCE_MATERIAL_STATE])) {
                        $errs[self::FIELD_SOURCE_MATERIAL_STATE] = [];
                    }
                    $errs[self::FIELD_SOURCE_MATERIAL_STATE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ORGANISM_ID])) {
            $v = $this->getOrganismId();
            foreach ($validationRules[self::FIELD_ORGANISM_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_ORGANISM_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ORGANISM_ID])) {
                        $errs[self::FIELD_ORGANISM_ID] = [];
                    }
                    $errs[self::FIELD_ORGANISM_ID][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ORGANISM_NAME])) {
            $v = $this->getOrganismName();
            foreach ($validationRules[self::FIELD_ORGANISM_NAME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_ORGANISM_NAME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ORGANISM_NAME])) {
                        $errs[self::FIELD_ORGANISM_NAME] = [];
                    }
                    $errs[self::FIELD_ORGANISM_NAME][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARENT_SUBSTANCE_ID])) {
            $v = $this->getParentSubstanceId();
            foreach ($validationRules[self::FIELD_PARENT_SUBSTANCE_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_PARENT_SUBSTANCE_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARENT_SUBSTANCE_ID])) {
                        $errs[self::FIELD_PARENT_SUBSTANCE_ID] = [];
                    }
                    $errs[self::FIELD_PARENT_SUBSTANCE_ID][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PARENT_SUBSTANCE_NAME])) {
            $v = $this->getParentSubstanceName();
            foreach ($validationRules[self::FIELD_PARENT_SUBSTANCE_NAME] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_PARENT_SUBSTANCE_NAME, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PARENT_SUBSTANCE_NAME])) {
                        $errs[self::FIELD_PARENT_SUBSTANCE_NAME] = [];
                    }
                    $errs[self::FIELD_PARENT_SUBSTANCE_NAME][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_COUNTRY_OF_ORIGIN])) {
            $v = $this->getCountryOfOrigin();
            foreach ($validationRules[self::FIELD_COUNTRY_OF_ORIGIN] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_COUNTRY_OF_ORIGIN, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_COUNTRY_OF_ORIGIN])) {
                        $errs[self::FIELD_COUNTRY_OF_ORIGIN] = [];
                    }
                    $errs[self::FIELD_COUNTRY_OF_ORIGIN][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_GEOGRAPHICAL_LOCATION])) {
            $v = $this->getGeographicalLocation();
            foreach ($validationRules[self::FIELD_GEOGRAPHICAL_LOCATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_GEOGRAPHICAL_LOCATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_GEOGRAPHICAL_LOCATION])) {
                        $errs[self::FIELD_GEOGRAPHICAL_LOCATION] = [];
                    }
                    $errs[self::FIELD_GEOGRAPHICAL_LOCATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DEVELOPMENT_STAGE])) {
            $v = $this->getDevelopmentStage();
            foreach ($validationRules[self::FIELD_DEVELOPMENT_STAGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_DEVELOPMENT_STAGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DEVELOPMENT_STAGE])) {
                        $errs[self::FIELD_DEVELOPMENT_STAGE] = [];
                    }
                    $errs[self::FIELD_DEVELOPMENT_STAGE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_FRACTION_DESCRIPTION])) {
            $v = $this->getFractionDescription();
            foreach ($validationRules[self::FIELD_FRACTION_DESCRIPTION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_FRACTION_DESCRIPTION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_FRACTION_DESCRIPTION])) {
                        $errs[self::FIELD_FRACTION_DESCRIPTION] = [];
                    }
                    $errs[self::FIELD_FRACTION_DESCRIPTION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ORGANISM])) {
            $v = $this->getOrganism();
            foreach ($validationRules[self::FIELD_ORGANISM] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_ORGANISM, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ORGANISM])) {
                        $errs[self::FIELD_ORGANISM] = [];
                    }
                    $errs[self::FIELD_ORGANISM][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_PART_DESCRIPTION])) {
            $v = $this->getPartDescription();
            foreach ($validationRules[self::FIELD_PART_DESCRIPTION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_SOURCE_MATERIAL, self::FIELD_PART_DESCRIPTION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_PART_DESCRIPTION])) {
                        $errs[self::FIELD_PART_DESCRIPTION] = [];
                    }
                    $errs[self::FIELD_PART_DESCRIPTION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TEXT])) {
            $v = $this->getText();
            foreach ($validationRules[self::FIELD_TEXT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_TEXT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TEXT])) {
                        $errs[self::FIELD_TEXT] = [];
                    }
                    $errs[self::FIELD_TEXT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTAINED])) {
            $v = $this->getContained();
            foreach ($validationRules[self::FIELD_CONTAINED] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_CONTAINED, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTAINED])) {
                        $errs[self::FIELD_CONTAINED] = [];
                    }
                    $errs[self::FIELD_CONTAINED][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach ($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MODIFIER_EXTENSION])) {
            $v = $this->getModifierExtension();
            foreach ($validationRules[self::FIELD_MODIFIER_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_MODIFIER_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MODIFIER_EXTENSION])) {
                        $errs[self::FIELD_MODIFIER_EXTENSION] = [];
                    }
                    $errs[self::FIELD_MODIFIER_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach ($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_META])) {
            $v = $this->getMeta();
            foreach ($validationRules[self::FIELD_META] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_META, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_META])) {
                        $errs[self::FIELD_META] = [];
                    }
                    $errs[self::FIELD_META][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_IMPLICIT_RULES])) {
            $v = $this->getImplicitRules();
            foreach ($validationRules[self::FIELD_IMPLICIT_RULES] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_IMPLICIT_RULES, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_IMPLICIT_RULES])) {
                        $errs[self::FIELD_IMPLICIT_RULES] = [];
                    }
                    $errs[self::FIELD_IMPLICIT_RULES][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LANGUAGE])) {
            $v = $this->getLanguage();
            foreach ($validationRules[self::FIELD_LANGUAGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_LANGUAGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LANGUAGE])) {
                        $errs[self::FIELD_LANGUAGE] = [];
                    }
                    $errs[self::FIELD_LANGUAGE][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRSubstanceSourceMaterial $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRSubstanceSourceMaterial
     */
    public static function xmlUnserialize($element = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            return null;
        }
        if (is_string($element)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($element, $libxmlOpts);
            if (false === $dom) {
                throw new \DomainException(sprintf('FHIRSubstanceSourceMaterial::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function (\libXMLError $err) {
                    return $err->message;
                }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRSubstanceSourceMaterial::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRSubstanceSourceMaterial(null);
        } elseif (!is_object($type) || !($type instanceof FHIRSubstanceSourceMaterial)) {
            throw new \RuntimeException(sprintf(
                'FHIRSubstanceSourceMaterial::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRSubstanceSourceMaterial or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for ($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_SOURCE_MATERIAL_CLASS === $n->nodeName) {
                $type->setSourceMaterialClass(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_SOURCE_MATERIAL_TYPE === $n->nodeName) {
                $type->setSourceMaterialType(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_SOURCE_MATERIAL_STATE === $n->nodeName) {
                $type->setSourceMaterialState(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_ORGANISM_ID === $n->nodeName) {
                $type->setOrganismId(FHIRIdentifier::xmlUnserialize($n));
            } elseif (self::FIELD_ORGANISM_NAME === $n->nodeName) {
                $type->setOrganismName(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_PARENT_SUBSTANCE_ID === $n->nodeName) {
                $type->addParentSubstanceId(FHIRIdentifier::xmlUnserialize($n));
            } elseif (self::FIELD_PARENT_SUBSTANCE_NAME === $n->nodeName) {
                $type->addParentSubstanceName(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_COUNTRY_OF_ORIGIN === $n->nodeName) {
                $type->addCountryOfOrigin(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_GEOGRAPHICAL_LOCATION === $n->nodeName) {
                $type->addGeographicalLocation(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_DEVELOPMENT_STAGE === $n->nodeName) {
                $type->setDevelopmentStage(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_FRACTION_DESCRIPTION === $n->nodeName) {
                $type->addFractionDescription(FHIRSubstanceSourceMaterialFractionDescription::xmlUnserialize($n));
            } elseif (self::FIELD_ORGANISM === $n->nodeName) {
                $type->setOrganism(FHIRSubstanceSourceMaterialOrganism::xmlUnserialize($n));
            } elseif (self::FIELD_PART_DESCRIPTION === $n->nodeName) {
                $type->addPartDescription(FHIRSubstanceSourceMaterialPartDescription::xmlUnserialize($n));
            } elseif (self::FIELD_TEXT === $n->nodeName) {
                $type->setText(FHIRNarrative::xmlUnserialize($n));
            } elseif (self::FIELD_CONTAINED === $n->nodeName) {
                for ($ni = 0; $ni < $n->childNodes->length; $ni++) {
                    $nn = $n->childNodes->item($ni);
                    if ($nn instanceof \DOMElement) {
                        $type->addContained(PHPFHIRTypeMap::getContainedTypeFromXML($nn));
                    }
                }
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRId::xmlUnserialize($n));
            } elseif (self::FIELD_META === $n->nodeName) {
                $type->setMeta(FHIRMeta::xmlUnserialize($n));
            } elseif (self::FIELD_IMPLICIT_RULES === $n->nodeName) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_LANGUAGE === $n->nodeName) {
                $type->setLanguage(FHIRCode::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ORGANISM_NAME);
        if (null !== $n) {
            $pt = $type->getOrganismName();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setOrganismName($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_PARENT_SUBSTANCE_NAME);
        if (null !== $n) {
            $pt = $type->getParentSubstanceName();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->addParentSubstanceName($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_GEOGRAPHICAL_LOCATION);
        if (null !== $n) {
            $pt = $type->getGeographicalLocation();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->addGeographicalLocation($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ID);
        if (null !== $n) {
            $pt = $type->getId();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setId($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_IMPLICIT_RULES);
        if (null !== $n) {
            $pt = $type->getImplicitRules();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setImplicitRules($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_LANGUAGE);
        if (null !== $n) {
            $pt = $type->getLanguage();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setLanguage($n->nodeValue);
            }
        }
        return $type;
    }

    /**
     * @param null|\DOMElement $element
     * @param null|int $libxmlOpts
     * @return \DOMElement
     */
    public function xmlSerialize(\DOMElement $element = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            $dom = new \DOMDocument();
            $dom->loadXML($this->_getFHIRXMLElementDefinition(), $libxmlOpts);
            $element = $dom->documentElement;
        } elseif (null === $element->namespaceURI && '' !== ($xmlns = $this->_getFHIRXMLNamespace())) {
            $element->setAttribute('xmlns', $xmlns);
        }
        parent::xmlSerialize($element);
        if (null !== ($v = $this->getSourceMaterialClass())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_SOURCE_MATERIAL_CLASS);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getSourceMaterialType())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_SOURCE_MATERIAL_TYPE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getSourceMaterialState())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_SOURCE_MATERIAL_STATE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getOrganismId())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ORGANISM_ID);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getOrganismName())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ORGANISM_NAME);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getParentSubstanceId())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_PARENT_SUBSTANCE_ID);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getParentSubstanceName())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_PARENT_SUBSTANCE_NAME);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getCountryOfOrigin())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_COUNTRY_OF_ORIGIN);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getGeographicalLocation())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_GEOGRAPHICAL_LOCATION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getDevelopmentStage())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_DEVELOPMENT_STAGE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getFractionDescription())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_FRACTION_DESCRIPTION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if (null !== ($v = $this->getOrganism())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ORGANISM);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getPartDescription())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_PART_DESCRIPTION);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if (null !== ($v = $this->getSourceMaterialClass())) {
            $a[self::FIELD_SOURCE_MATERIAL_CLASS] = $v;
        }
        if (null !== ($v = $this->getSourceMaterialType())) {
            $a[self::FIELD_SOURCE_MATERIAL_TYPE] = $v;
        }
        if (null !== ($v = $this->getSourceMaterialState())) {
            $a[self::FIELD_SOURCE_MATERIAL_STATE] = $v;
        }
        if (null !== ($v = $this->getOrganismId())) {
            $a[self::FIELD_ORGANISM_ID] = $v;
        }
        if (null !== ($v = $this->getOrganismName())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_ORGANISM_NAME] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_ORGANISM_NAME_EXT] = $ext;
            }
        }
        if ([] !== ($vs = $this->getParentSubstanceId())) {
            $a[self::FIELD_PARENT_SUBSTANCE_ID] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_PARENT_SUBSTANCE_ID][] = $v;
            }
        }
        if ([] !== ($vs = $this->getParentSubstanceName())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRString::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_PARENT_SUBSTANCE_NAME] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_PARENT_SUBSTANCE_NAME_EXT] = $exts;
            }
        }
        if ([] !== ($vs = $this->getCountryOfOrigin())) {
            $a[self::FIELD_COUNTRY_OF_ORIGIN] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_COUNTRY_OF_ORIGIN][] = $v;
            }
        }
        if ([] !== ($vs = $this->getGeographicalLocation())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRString::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_GEOGRAPHICAL_LOCATION] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_GEOGRAPHICAL_LOCATION_EXT] = $exts;
            }
        }
        if (null !== ($v = $this->getDevelopmentStage())) {
            $a[self::FIELD_DEVELOPMENT_STAGE] = $v;
        }
        if ([] !== ($vs = $this->getFractionDescription())) {
            $a[self::FIELD_FRACTION_DESCRIPTION] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_FRACTION_DESCRIPTION][] = $v;
            }
        }
        if (null !== ($v = $this->getOrganism())) {
            $a[self::FIELD_ORGANISM] = $v;
        }
        if ([] !== ($vs = $this->getPartDescription())) {
            $a[self::FIELD_PART_DESCRIPTION] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_PART_DESCRIPTION][] = $v;
            }
        }
        return [PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE => $this->_getResourceType()] + $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}
