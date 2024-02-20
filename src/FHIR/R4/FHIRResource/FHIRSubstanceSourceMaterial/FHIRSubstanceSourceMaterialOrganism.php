<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial;

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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;

/**
 * Source material shall capture information on the taxonomic and anatomical origins as well as the fraction of a material that can result in or can be modified to form a substance. This set of data elements shall be used to define polymer substances isolated from biological matrices. Taxonomic and anatomical origins shall be described using a controlled vocabulary as required. This information is captured for naturally derived polymers ( . starch) and structurally diverse substances. For Organisms belonging to the Kingdom Plantae the Substance level defines the fresh material of a single species or infraspecies, the Herbal Drug and the Herbal preparation. For Herbal preparations, the fraction information will be captured at the Substance information level and additional information for herbal extracts will be captured at the Specified Substance Group 1 information level. See for further explanation the Substance Class: Structurally Diverse and the herbal annex.
 */
class FHIRSubstanceSourceMaterialOrganism extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The family of an organism shall be specified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $family = null;

    /**
     * The genus of an organism shall be specified; refers to the Latin epithet of the genus element of the plant/animal scientific name; it is present in names for genera, species and infraspecies.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $genus = null;

    /**
     * The species of an organism shall be specified; refers to the Latin epithet of the species of the plant/animal; it is present in names for species and infraspecies.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $species = null;

    /**
     * The Intraspecific type of an organism shall be specified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $intraspecificType = null;

    /**
     * The intraspecific description of an organism shall be specified based on a controlled vocabulary. For Influenza Vaccine, the intraspecific description shall contain the syntax of the antigen in line with the WHO convention.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $intraspecificDescription = null;

    /**
     * 4.9.13.6.1 Author type (Conditional).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialAuthor[]
     */
    public $author = [];

    /**
     * 4.9.13.8.1 Hybrid species maternal organism ID (Optional).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid
     */
    public $hybrid = null;

    /**
     * 4.9.13.7.1 Kingdom (Conditional).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganismGeneral
     */
    public $organismGeneral = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceSourceMaterial.Organism';

    /**
     * The family of an organism shall be specified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * The family of an organism shall be specified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $family
     * @return $this
     */
    public function setFamily($family)
    {
        $this->family = $family;
        return $this;
    }

    /**
     * The genus of an organism shall be specified; refers to the Latin epithet of the genus element of the plant/animal scientific name; it is present in names for genera, species and infraspecies.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getGenus()
    {
        return $this->genus;
    }

    /**
     * The genus of an organism shall be specified; refers to the Latin epithet of the genus element of the plant/animal scientific name; it is present in names for genera, species and infraspecies.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $genus
     * @return $this
     */
    public function setGenus($genus)
    {
        $this->genus = $genus;
        return $this;
    }

    /**
     * The species of an organism shall be specified; refers to the Latin epithet of the species of the plant/animal; it is present in names for species and infraspecies.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSpecies()
    {
        return $this->species;
    }

    /**
     * The species of an organism shall be specified; refers to the Latin epithet of the species of the plant/animal; it is present in names for species and infraspecies.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $species
     * @return $this
     */
    public function setSpecies($species)
    {
        $this->species = $species;
        return $this;
    }

    /**
     * The Intraspecific type of an organism shall be specified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getIntraspecificType()
    {
        return $this->intraspecificType;
    }

    /**
     * The Intraspecific type of an organism shall be specified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $intraspecificType
     * @return $this
     */
    public function setIntraspecificType($intraspecificType)
    {
        $this->intraspecificType = $intraspecificType;
        return $this;
    }

    /**
     * The intraspecific description of an organism shall be specified based on a controlled vocabulary. For Influenza Vaccine, the intraspecific description shall contain the syntax of the antigen in line with the WHO convention.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getIntraspecificDescription()
    {
        return $this->intraspecificDescription;
    }

    /**
     * The intraspecific description of an organism shall be specified based on a controlled vocabulary. For Influenza Vaccine, the intraspecific description shall contain the syntax of the antigen in line with the WHO convention.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $intraspecificDescription
     * @return $this
     */
    public function setIntraspecificDescription($intraspecificDescription)
    {
        $this->intraspecificDescription = $intraspecificDescription;
        return $this;
    }

    /**
     * 4.9.13.6.1 Author type (Conditional).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialAuthor[]
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * 4.9.13.6.1 Author type (Conditional).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialAuthor $author
     * @return $this
     */
    public function addAuthor($author)
    {
        $this->author[] = $author;
        return $this;
    }

    /**
     * 4.9.13.8.1 Hybrid species maternal organism ID (Optional).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid
     */
    public function getHybrid()
    {
        return $this->hybrid;
    }

    /**
     * 4.9.13.8.1 Hybrid species maternal organism ID (Optional).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialHybrid $hybrid
     * @return $this
     */
    public function setHybrid($hybrid)
    {
        $this->hybrid = $hybrid;
        return $this;
    }

    /**
     * 4.9.13.7.1 Kingdom (Conditional).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganismGeneral
     */
    public function getOrganismGeneral()
    {
        return $this->organismGeneral;
    }

    /**
     * 4.9.13.7.1 Kingdom (Conditional).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSourceMaterial\FHIRSubstanceSourceMaterialOrganismGeneral $organismGeneral
     * @return $this
     */
    public function setOrganismGeneral($organismGeneral)
    {
        $this->organismGeneral = $organismGeneral;
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
            if (isset($data['family'])) {
                $this->setFamily($data['family']);
            }
            if (isset($data['genus'])) {
                $this->setGenus($data['genus']);
            }
            if (isset($data['species'])) {
                $this->setSpecies($data['species']);
            }
            if (isset($data['intraspecificType'])) {
                $this->setIntraspecificType($data['intraspecificType']);
            }
            if (isset($data['intraspecificDescription'])) {
                $this->setIntraspecificDescription($data['intraspecificDescription']);
            }
            if (isset($data['author'])) {
                if (is_array($data['author'])) {
                    foreach ($data['author'] as $d) {
                        $this->addAuthor($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"author" must be array of objects or null, ' . gettype($data['author']) . ' seen.');
                }
            }
            if (isset($data['hybrid'])) {
                $this->setHybrid($data['hybrid']);
            }
            if (isset($data['organismGeneral'])) {
                $this->setOrganismGeneral($data['organismGeneral']);
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
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        if (isset($this->family)) {
            $json['family'] = $this->family;
        }
        if (isset($this->genus)) {
            $json['genus'] = $this->genus;
        }
        if (isset($this->species)) {
            $json['species'] = $this->species;
        }
        if (isset($this->intraspecificType)) {
            $json['intraspecificType'] = $this->intraspecificType;
        }
        if (isset($this->intraspecificDescription)) {
            $json['intraspecificDescription'] = $this->intraspecificDescription;
        }
        if (0 < count($this->author)) {
            $json['author'] = [];
            foreach ($this->author as $author) {
                $json['author'][] = $author;
            }
        }
        if (isset($this->hybrid)) {
            $json['hybrid'] = $this->hybrid;
        }
        if (isset($this->organismGeneral)) {
            $json['organismGeneral'] = $this->organismGeneral;
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
            $sxe = new \SimpleXMLElement('<SubstanceSourceMaterialOrganism xmlns="http://hl7.org/fhir"></SubstanceSourceMaterialOrganism>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->family)) {
            $this->family->xmlSerialize(true, $sxe->addChild('family'));
        }
        if (isset($this->genus)) {
            $this->genus->xmlSerialize(true, $sxe->addChild('genus'));
        }
        if (isset($this->species)) {
            $this->species->xmlSerialize(true, $sxe->addChild('species'));
        }
        if (isset($this->intraspecificType)) {
            $this->intraspecificType->xmlSerialize(true, $sxe->addChild('intraspecificType'));
        }
        if (isset($this->intraspecificDescription)) {
            $this->intraspecificDescription->xmlSerialize(true, $sxe->addChild('intraspecificDescription'));
        }
        if (0 < count($this->author)) {
            foreach ($this->author as $author) {
                $author->xmlSerialize(true, $sxe->addChild('author'));
            }
        }
        if (isset($this->hybrid)) {
            $this->hybrid->xmlSerialize(true, $sxe->addChild('hybrid'));
        }
        if (isset($this->organismGeneral)) {
            $this->organismGeneral->xmlSerialize(true, $sxe->addChild('organismGeneral'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
