<?php

namespace OpenEMR\FHIR\R4\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: June 14th, 2019
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *   Generated on Thu, Dec 27, 2018 22:37+1100 for FHIR v4.0.0
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/**
 * Source material shall capture information on the taxonomic and anatomical origins as well as the fraction of a material that can result in or can be modified to form a substance. This set of data elements shall be used to define polymer substances isolated from biological matrices. Taxonomic and anatomical origins shall be described using a controlled vocabulary as required. This information is captured for naturally derived polymers ( . starch) and structurally diverse substances. For Organisms belonging to the Kingdom Plantae the Substance level defines the fresh material of a single species or infraspecies, the Herbal Drug and the Herbal preparation. For Herbal preparations, the fraction information will be captured at the Substance information level and additional information for herbal extracts will be captured at the Specified Substance Group 1 information level. See for further explanation the Substance Class: Structurally Diverse and the herbal annex.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSubstanceSourceMaterial extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * General high level classification of the source material specific to the origin of the material.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $sourceMaterialClass = null;

    /**
     * The type of the source material shall be specified based on a controlled vocabulary. For vaccines, this subclause refers to the class of infectious agent.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $sourceMaterialType = null;

    /**
     * The state of the source material when extracted.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $sourceMaterialState = null;

    /**
     * The unique identifier associated with the source material parent organism shall be specified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $organismId = null;

    /**
     * The organism accepted Scientific name shall be provided based on the organism taxonomy.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $organismName = null;

    /**
     * The parent of the herbal drug Ginkgo biloba, Leaf is the substance ID of the substance (fresh) of Ginkgo biloba L. or Ginkgo biloba L. (Whole plant).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $parentSubstanceId = [];

    /**
     * The parent substance of the Herbal Drug, or Herbal preparation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $parentSubstanceName = [];

    /**
     * The country where the plant material is harvested or the countries where the plasma is sourced from as laid down in accordance with the Plasma Master File. For “Plasma-derived substances” the attribute country of origin provides information about the countries used for the manufacturing of the Cryopoor plama or Crioprecipitate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $countryOfOrigin = [];

    /**
     * The place/region where the plant is harvested or the places/regions where the animal source material has its habitat.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $geographicalLocation = [];

    /**
     * Stage of life for animals, plants, insects and microorganisms. This information shall be provided only when the substance is significantly different in these stages (e.g. foetal bovine serum).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $developmentStage = null;

    /**
     * Many complex materials are fractions of parts of plants, animals, or minerals. Fraction elements are often necessary to define both Substances and Specified Group 1 Substances. For substances derived from Plants, fraction information will be captured at the Substance information level ( . Oils, Juices and Exudates). Additional information for Extracts, such as extraction solvent composition, will be captured at the Specified Substance Group 1 information level. For plasma-derived products fraction information will be captured at the Substance and the Specified Substance Group 1 levels.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription[]
     */
    public $fractionDescription = [];

    /**
     * This subclause describes the organism which the substance is derived from. For vaccines, the parent organism shall be specified based on these subclause elements. As an example, full taxonomy will be described for the Substance Name: ., Leaf.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism
     */
    public $organism = null;

    /**
     * To do.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription[]
     */
    public $partDescription = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceSourceMaterial';

    /**
     * General high level classification of the source material specific to the origin of the material.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSourceMaterialClass()
    {
        return $this->sourceMaterialClass;
    }

    /**
     * General high level classification of the source material specific to the origin of the material.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $sourceMaterialClass
     * @return $this
     */
    public function setSourceMaterialClass($sourceMaterialClass)
    {
        $this->sourceMaterialClass = $sourceMaterialClass;
        return $this;
    }

    /**
     * The type of the source material shall be specified based on a controlled vocabulary. For vaccines, this subclause refers to the class of infectious agent.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSourceMaterialType()
    {
        return $this->sourceMaterialType;
    }

    /**
     * The type of the source material shall be specified based on a controlled vocabulary. For vaccines, this subclause refers to the class of infectious agent.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $sourceMaterialType
     * @return $this
     */
    public function setSourceMaterialType($sourceMaterialType)
    {
        $this->sourceMaterialType = $sourceMaterialType;
        return $this;
    }

    /**
     * The state of the source material when extracted.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSourceMaterialState()
    {
        return $this->sourceMaterialState;
    }

    /**
     * The state of the source material when extracted.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $sourceMaterialState
     * @return $this
     */
    public function setSourceMaterialState($sourceMaterialState)
    {
        $this->sourceMaterialState = $sourceMaterialState;
        return $this;
    }

    /**
     * The unique identifier associated with the source material parent organism shall be specified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getOrganismId()
    {
        return $this->organismId;
    }

    /**
     * The unique identifier associated with the source material parent organism shall be specified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $organismId
     * @return $this
     */
    public function setOrganismId($organismId)
    {
        $this->organismId = $organismId;
        return $this;
    }

    /**
     * The organism accepted Scientific name shall be provided based on the organism taxonomy.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getOrganismName()
    {
        return $this->organismName;
    }

    /**
     * The organism accepted Scientific name shall be provided based on the organism taxonomy.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $organismName
     * @return $this
     */
    public function setOrganismName($organismName)
    {
        $this->organismName = $organismName;
        return $this;
    }

    /**
     * The parent of the herbal drug Ginkgo biloba, Leaf is the substance ID of the substance (fresh) of Ginkgo biloba L. or Ginkgo biloba L. (Whole plant).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getParentSubstanceId()
    {
        return $this->parentSubstanceId;
    }

    /**
     * The parent of the herbal drug Ginkgo biloba, Leaf is the substance ID of the substance (fresh) of Ginkgo biloba L. or Ginkgo biloba L. (Whole plant).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $parentSubstanceId
     * @return $this
     */
    public function addParentSubstanceId($parentSubstanceId)
    {
        $this->parentSubstanceId[] = $parentSubstanceId;
        return $this;
    }

    /**
     * The parent substance of the Herbal Drug, or Herbal preparation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getParentSubstanceName()
    {
        return $this->parentSubstanceName;
    }

    /**
     * The parent substance of the Herbal Drug, or Herbal preparation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $parentSubstanceName
     * @return $this
     */
    public function addParentSubstanceName($parentSubstanceName)
    {
        $this->parentSubstanceName[] = $parentSubstanceName;
        return $this;
    }

    /**
     * The country where the plant material is harvested or the countries where the plasma is sourced from as laid down in accordance with the Plasma Master File. For “Plasma-derived substances” the attribute country of origin provides information about the countries used for the manufacturing of the Cryopoor plama or Crioprecipitate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCountryOfOrigin()
    {
        return $this->countryOfOrigin;
    }

    /**
     * The country where the plant material is harvested or the countries where the plasma is sourced from as laid down in accordance with the Plasma Master File. For “Plasma-derived substances” the attribute country of origin provides information about the countries used for the manufacturing of the Cryopoor plama or Crioprecipitate.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $countryOfOrigin
     * @return $this
     */
    public function addCountryOfOrigin($countryOfOrigin)
    {
        $this->countryOfOrigin[] = $countryOfOrigin;
        return $this;
    }

    /**
     * The place/region where the plant is harvested or the places/regions where the animal source material has its habitat.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getGeographicalLocation()
    {
        return $this->geographicalLocation;
    }

    /**
     * The place/region where the plant is harvested or the places/regions where the animal source material has its habitat.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $geographicalLocation
     * @return $this
     */
    public function addGeographicalLocation($geographicalLocation)
    {
        $this->geographicalLocation[] = $geographicalLocation;
        return $this;
    }

    /**
     * Stage of life for animals, plants, insects and microorganisms. This information shall be provided only when the substance is significantly different in these stages (e.g. foetal bovine serum).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDevelopmentStage()
    {
        return $this->developmentStage;
    }

    /**
     * Stage of life for animals, plants, insects and microorganisms. This information shall be provided only when the substance is significantly different in these stages (e.g. foetal bovine serum).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $developmentStage
     * @return $this
     */
    public function setDevelopmentStage($developmentStage)
    {
        $this->developmentStage = $developmentStage;
        return $this;
    }

    /**
     * Many complex materials are fractions of parts of plants, animals, or minerals. Fraction elements are often necessary to define both Substances and Specified Group 1 Substances. For substances derived from Plants, fraction information will be captured at the Substance information level ( . Oils, Juices and Exudates). Additional information for Extracts, such as extraction solvent composition, will be captured at the Specified Substance Group 1 information level. For plasma-derived products fraction information will be captured at the Substance and the Specified Substance Group 1 levels.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription[]
     */
    public function getFractionDescription()
    {
        return $this->fractionDescription;
    }

    /**
     * Many complex materials are fractions of parts of plants, animals, or minerals. Fraction elements are often necessary to define both Substances and Specified Group 1 Substances. For substances derived from Plants, fraction information will be captured at the Substance information level ( . Oils, Juices and Exudates). Additional information for Extracts, such as extraction solvent composition, will be captured at the Specified Substance Group 1 information level. For plasma-derived products fraction information will be captured at the Substance and the Specified Substance Group 1 levels.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialFractionDescription $fractionDescription
     * @return $this
     */
    public function addFractionDescription($fractionDescription)
    {
        $this->fractionDescription[] = $fractionDescription;
        return $this;
    }

    /**
     * This subclause describes the organism which the substance is derived from. For vaccines, the parent organism shall be specified based on these subclause elements. As an example, full taxonomy will be described for the Substance Name: ., Leaf.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism
     */
    public function getOrganism()
    {
        return $this->organism;
    }

    /**
     * This subclause describes the organism which the substance is derived from. For vaccines, the parent organism shall be specified based on these subclause elements. As an example, full taxonomy will be described for the Substance Name: ., Leaf.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganism $organism
     * @return $this
     */
    public function setOrganism($organism)
    {
        $this->organism = $organism;
        return $this;
    }

    /**
     * To do.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription[]
     */
    public function getPartDescription()
    {
        return $this->partDescription;
    }

    /**
     * To do.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialPartDescription $partDescription
     * @return $this
     */
    public function addPartDescription($partDescription)
    {
        $this->partDescription[] = $partDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @param mixed $data
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            if (isset($data['sourceMaterialClass'])) {
                $this->setSourceMaterialClass($data['sourceMaterialClass']);
            }
            if (isset($data['sourceMaterialType'])) {
                $this->setSourceMaterialType($data['sourceMaterialType']);
            }
            if (isset($data['sourceMaterialState'])) {
                $this->setSourceMaterialState($data['sourceMaterialState']);
            }
            if (isset($data['organismId'])) {
                $this->setOrganismId($data['organismId']);
            }
            if (isset($data['organismName'])) {
                $this->setOrganismName($data['organismName']);
            }
            if (isset($data['parentSubstanceId'])) {
                if (is_array($data['parentSubstanceId'])) {
                    foreach ($data['parentSubstanceId'] as $d) {
                        $this->addParentSubstanceId($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"parentSubstanceId" must be array of objects or null, ' . gettype($data['parentSubstanceId']) . ' seen.');
                }
            }
            if (isset($data['parentSubstanceName'])) {
                if (is_array($data['parentSubstanceName'])) {
                    foreach ($data['parentSubstanceName'] as $d) {
                        $this->addParentSubstanceName($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"parentSubstanceName" must be array of objects or null, ' . gettype($data['parentSubstanceName']) . ' seen.');
                }
            }
            if (isset($data['countryOfOrigin'])) {
                if (is_array($data['countryOfOrigin'])) {
                    foreach ($data['countryOfOrigin'] as $d) {
                        $this->addCountryOfOrigin($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"countryOfOrigin" must be array of objects or null, ' . gettype($data['countryOfOrigin']) . ' seen.');
                }
            }
            if (isset($data['geographicalLocation'])) {
                if (is_array($data['geographicalLocation'])) {
                    foreach ($data['geographicalLocation'] as $d) {
                        $this->addGeographicalLocation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"geographicalLocation" must be array of objects or null, ' . gettype($data['geographicalLocation']) . ' seen.');
                }
            }
            if (isset($data['developmentStage'])) {
                $this->setDevelopmentStage($data['developmentStage']);
            }
            if (isset($data['fractionDescription'])) {
                if (is_array($data['fractionDescription'])) {
                    foreach ($data['fractionDescription'] as $d) {
                        $this->addFractionDescription($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"fractionDescription" must be array of objects or null, ' . gettype($data['fractionDescription']) . ' seen.');
                }
            }
            if (isset($data['organism'])) {
                $this->setOrganism($data['organism']);
            }
            if (isset($data['partDescription'])) {
                if (is_array($data['partDescription'])) {
                    foreach ($data['partDescription'] as $d) {
                        $this->addPartDescription($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"partDescription" must be array of objects or null, ' . gettype($data['partDescription']) . ' seen.');
                }
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
        }
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (isset($this->sourceMaterialClass)) {
            $json['sourceMaterialClass'] = $this->sourceMaterialClass;
        }
        if (isset($this->sourceMaterialType)) {
            $json['sourceMaterialType'] = $this->sourceMaterialType;
        }
        if (isset($this->sourceMaterialState)) {
            $json['sourceMaterialState'] = $this->sourceMaterialState;
        }
        if (isset($this->organismId)) {
            $json['organismId'] = $this->organismId;
        }
        if (isset($this->organismName)) {
            $json['organismName'] = $this->organismName;
        }
        if (0 < count($this->parentSubstanceId)) {
            $json['parentSubstanceId'] = [];
            foreach ($this->parentSubstanceId as $parentSubstanceId) {
                $json['parentSubstanceId'][] = $parentSubstanceId;
            }
        }
        if (0 < count($this->parentSubstanceName)) {
            $json['parentSubstanceName'] = [];
            foreach ($this->parentSubstanceName as $parentSubstanceName) {
                $json['parentSubstanceName'][] = $parentSubstanceName;
            }
        }
        if (0 < count($this->countryOfOrigin)) {
            $json['countryOfOrigin'] = [];
            foreach ($this->countryOfOrigin as $countryOfOrigin) {
                $json['countryOfOrigin'][] = $countryOfOrigin;
            }
        }
        if (0 < count($this->geographicalLocation)) {
            $json['geographicalLocation'] = [];
            foreach ($this->geographicalLocation as $geographicalLocation) {
                $json['geographicalLocation'][] = $geographicalLocation;
            }
        }
        if (isset($this->developmentStage)) {
            $json['developmentStage'] = $this->developmentStage;
        }
        if (0 < count($this->fractionDescription)) {
            $json['fractionDescription'] = [];
            foreach ($this->fractionDescription as $fractionDescription) {
                $json['fractionDescription'][] = $fractionDescription;
            }
        }
        if (isset($this->organism)) {
            $json['organism'] = $this->organism;
        }
        if (0 < count($this->partDescription)) {
            $json['partDescription'] = [];
            foreach ($this->partDescription as $partDescription) {
                $json['partDescription'][] = $partDescription;
            }
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<SubstanceSourceMaterial xmlns="http://hl7.org/fhir"></SubstanceSourceMaterial>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->sourceMaterialClass)) {
            $this->sourceMaterialClass->xmlSerialize(true, $sxe->addChild('sourceMaterialClass'));
        }
        if (isset($this->sourceMaterialType)) {
            $this->sourceMaterialType->xmlSerialize(true, $sxe->addChild('sourceMaterialType'));
        }
        if (isset($this->sourceMaterialState)) {
            $this->sourceMaterialState->xmlSerialize(true, $sxe->addChild('sourceMaterialState'));
        }
        if (isset($this->organismId)) {
            $this->organismId->xmlSerialize(true, $sxe->addChild('organismId'));
        }
        if (isset($this->organismName)) {
            $this->organismName->xmlSerialize(true, $sxe->addChild('organismName'));
        }
        if (0 < count($this->parentSubstanceId)) {
            foreach ($this->parentSubstanceId as $parentSubstanceId) {
                $parentSubstanceId->xmlSerialize(true, $sxe->addChild('parentSubstanceId'));
            }
        }
        if (0 < count($this->parentSubstanceName)) {
            foreach ($this->parentSubstanceName as $parentSubstanceName) {
                $parentSubstanceName->xmlSerialize(true, $sxe->addChild('parentSubstanceName'));
            }
        }
        if (0 < count($this->countryOfOrigin)) {
            foreach ($this->countryOfOrigin as $countryOfOrigin) {
                $countryOfOrigin->xmlSerialize(true, $sxe->addChild('countryOfOrigin'));
            }
        }
        if (0 < count($this->geographicalLocation)) {
            foreach ($this->geographicalLocation as $geographicalLocation) {
                $geographicalLocation->xmlSerialize(true, $sxe->addChild('geographicalLocation'));
            }
        }
        if (isset($this->developmentStage)) {
            $this->developmentStage->xmlSerialize(true, $sxe->addChild('developmentStage'));
        }
        if (0 < count($this->fractionDescription)) {
            foreach ($this->fractionDescription as $fractionDescription) {
                $fractionDescription->xmlSerialize(true, $sxe->addChild('fractionDescription'));
            }
        }
        if (isset($this->organism)) {
            $this->organism->xmlSerialize(true, $sxe->addChild('organism'));
        }
        if (0 < count($this->partDescription)) {
            foreach ($this->partDescription as $partDescription) {
                $partDescription->xmlSerialize(true, $sxe->addChild('partDescription'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
