<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification;

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
 * The detailed description of a substance, typically at a level beyond what is used for prescribing.
 */
class FHIRSubstanceSpecificationStructure extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Stereochemistry type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $stereochemistry = null;

    /**
     * Optical activity type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $opticalActivity = null;

    /**
     * Molecular formula.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $molecularFormula = null;

    /**
     * Specified per moiety according to the Hill system, i.e. first C, then H, then alphabetical, each moiety separated by a dot.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $molecularFormulaByMoiety = null;

    /**
     * Applicable for single substances that contain a radionuclide or a non-natural isotopic ratio.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationIsotope[]
     */
    public $isotope = [];

    /**
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight
     */
    public $molecularWeight = null;

    /**
     * Supporting literature.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $source = [];

    /**
     * Molecular structural representation.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRepresentation[]
     */
    public $representation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceSpecification.Structure';

    /**
     * Stereochemistry type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStereochemistry()
    {
        return $this->stereochemistry;
    }

    /**
     * Stereochemistry type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $stereochemistry
     * @return $this
     */
    public function setStereochemistry($stereochemistry)
    {
        $this->stereochemistry = $stereochemistry;
        return $this;
    }

    /**
     * Optical activity type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getOpticalActivity()
    {
        return $this->opticalActivity;
    }

    /**
     * Optical activity type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $opticalActivity
     * @return $this
     */
    public function setOpticalActivity($opticalActivity)
    {
        $this->opticalActivity = $opticalActivity;
        return $this;
    }

    /**
     * Molecular formula.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMolecularFormula()
    {
        return $this->molecularFormula;
    }

    /**
     * Molecular formula.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $molecularFormula
     * @return $this
     */
    public function setMolecularFormula($molecularFormula)
    {
        $this->molecularFormula = $molecularFormula;
        return $this;
    }

    /**
     * Specified per moiety according to the Hill system, i.e. first C, then H, then alphabetical, each moiety separated by a dot.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMolecularFormulaByMoiety()
    {
        return $this->molecularFormulaByMoiety;
    }

    /**
     * Specified per moiety according to the Hill system, i.e. first C, then H, then alphabetical, each moiety separated by a dot.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $molecularFormulaByMoiety
     * @return $this
     */
    public function setMolecularFormulaByMoiety($molecularFormulaByMoiety)
    {
        $this->molecularFormulaByMoiety = $molecularFormulaByMoiety;
        return $this;
    }

    /**
     * Applicable for single substances that contain a radionuclide or a non-natural isotopic ratio.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationIsotope[]
     */
    public function getIsotope()
    {
        return $this->isotope;
    }

    /**
     * Applicable for single substances that contain a radionuclide or a non-natural isotopic ratio.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationIsotope $isotope
     * @return $this
     */
    public function addIsotope($isotope)
    {
        $this->isotope[] = $isotope;
        return $this;
    }

    /**
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight
     */
    public function getMolecularWeight()
    {
        return $this->molecularWeight;
    }

    /**
     * The molecular weight or weight range (for proteins, polymers or nucleic acids).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationMolecularWeight $molecularWeight
     * @return $this
     */
    public function setMolecularWeight($molecularWeight)
    {
        $this->molecularWeight = $molecularWeight;
        return $this;
    }

    /**
     * Supporting literature.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Supporting literature.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $source
     * @return $this
     */
    public function addSource($source)
    {
        $this->source[] = $source;
        return $this;
    }

    /**
     * Molecular structural representation.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRepresentation[]
     */
    public function getRepresentation()
    {
        return $this->representation;
    }

    /**
     * Molecular structural representation.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification\FHIRSubstanceSpecificationRepresentation $representation
     * @return $this
     */
    public function addRepresentation($representation)
    {
        $this->representation[] = $representation;
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
            if (isset($data['stereochemistry'])) {
                $this->setStereochemistry($data['stereochemistry']);
            }
            if (isset($data['opticalActivity'])) {
                $this->setOpticalActivity($data['opticalActivity']);
            }
            if (isset($data['molecularFormula'])) {
                $this->setMolecularFormula($data['molecularFormula']);
            }
            if (isset($data['molecularFormulaByMoiety'])) {
                $this->setMolecularFormulaByMoiety($data['molecularFormulaByMoiety']);
            }
            if (isset($data['isotope'])) {
                if (is_array($data['isotope'])) {
                    foreach ($data['isotope'] as $d) {
                        $this->addIsotope($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"isotope" must be array of objects or null, ' . gettype($data['isotope']) . ' seen.');
                }
            }
            if (isset($data['molecularWeight'])) {
                $this->setMolecularWeight($data['molecularWeight']);
            }
            if (isset($data['source'])) {
                if (is_array($data['source'])) {
                    foreach ($data['source'] as $d) {
                        $this->addSource($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"source" must be array of objects or null, ' . gettype($data['source']) . ' seen.');
                }
            }
            if (isset($data['representation'])) {
                if (is_array($data['representation'])) {
                    foreach ($data['representation'] as $d) {
                        $this->addRepresentation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"representation" must be array of objects or null, ' . gettype($data['representation']) . ' seen.');
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
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        if (isset($this->stereochemistry)) {
            $json['stereochemistry'] = $this->stereochemistry;
        }
        if (isset($this->opticalActivity)) {
            $json['opticalActivity'] = $this->opticalActivity;
        }
        if (isset($this->molecularFormula)) {
            $json['molecularFormula'] = $this->molecularFormula;
        }
        if (isset($this->molecularFormulaByMoiety)) {
            $json['molecularFormulaByMoiety'] = $this->molecularFormulaByMoiety;
        }
        if (0 < count($this->isotope)) {
            $json['isotope'] = [];
            foreach ($this->isotope as $isotope) {
                $json['isotope'][] = $isotope;
            }
        }
        if (isset($this->molecularWeight)) {
            $json['molecularWeight'] = $this->molecularWeight;
        }
        if (0 < count($this->source)) {
            $json['source'] = [];
            foreach ($this->source as $source) {
                $json['source'][] = $source;
            }
        }
        if (0 < count($this->representation)) {
            $json['representation'] = [];
            foreach ($this->representation as $representation) {
                $json['representation'][] = $representation;
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
            $sxe = new \SimpleXMLElement('<SubstanceSpecificationStructure xmlns="http://hl7.org/fhir"></SubstanceSpecificationStructure>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->stereochemistry)) {
            $this->stereochemistry->xmlSerialize(true, $sxe->addChild('stereochemistry'));
        }
        if (isset($this->opticalActivity)) {
            $this->opticalActivity->xmlSerialize(true, $sxe->addChild('opticalActivity'));
        }
        if (isset($this->molecularFormula)) {
            $this->molecularFormula->xmlSerialize(true, $sxe->addChild('molecularFormula'));
        }
        if (isset($this->molecularFormulaByMoiety)) {
            $this->molecularFormulaByMoiety->xmlSerialize(true, $sxe->addChild('molecularFormulaByMoiety'));
        }
        if (0 < count($this->isotope)) {
            foreach ($this->isotope as $isotope) {
                $isotope->xmlSerialize(true, $sxe->addChild('isotope'));
            }
        }
        if (isset($this->molecularWeight)) {
            $this->molecularWeight->xmlSerialize(true, $sxe->addChild('molecularWeight'));
        }
        if (0 < count($this->source)) {
            foreach ($this->source as $source) {
                $source->xmlSerialize(true, $sxe->addChild('source'));
            }
        }
        if (0 < count($this->representation)) {
            foreach ($this->representation as $representation) {
                $representation->xmlSerialize(true, $sxe->addChild('representation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
