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
 * A SubstanceProtein is defined as a single unit of a linear amino acid sequence, or a combination of subunits that are either covalently linked or have a defined invariant stoichiometric relationship. This includes all synthetic, recombinant and purified SubstanceProteins of defined sequence, whether the use is therapeutic or prophylactic. This set of elements will be used to describe albumins, coagulation factors, cytokines, growth factors, peptide/SubstanceProtein hormones, enzymes, toxins, toxoids, recombinant vaccines, and immunomodulators.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSubstanceProtein extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * The SubstanceProtein descriptive elements will only be used when a complete or partial amino acid sequence is available or derivable from a nucleic acid sequence.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $sequenceType = null;

    /**
     * Number of linear sequences of amino acids linked through peptide bonds. The number of subunits constituting the SubstanceProtein shall be described. It is possible that the number of subunits can be variable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $numberOfSubunits = null;

    /**
     * The disulphide bond between two cysteine residues either on the same subunit or on two different subunits shall be described. The position of the disulfide bonds in the SubstanceProtein shall be listed in increasing order of subunit number and position within subunit followed by the abbreviation of the amino acids involved. The disulfide linkage positions shall actually contain the amino acid Cysteine at the respective positions.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $disulfideLinkage = [];

    /**
     * This subclause refers to the description of each subunit constituting the SubstanceProtein. A subunit is a linear sequence of amino acids linked through peptide bonds. The Subunit information shall be provided when the finished SubstanceProtein is a complex of multiple sequences; subunits are not used to delineate domains within a single sequence. Subunits are listed in order of decreasing length; sequences of the same length will be ordered by decreasing molecular weight; subunits that have identical sequences will be repeated multiple times.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit[]
     */
    public $subunit = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceProtein';

    /**
     * The SubstanceProtein descriptive elements will only be used when a complete or partial amino acid sequence is available or derivable from a nucleic acid sequence.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSequenceType()
    {
        return $this->sequenceType;
    }

    /**
     * The SubstanceProtein descriptive elements will only be used when a complete or partial amino acid sequence is available or derivable from a nucleic acid sequence.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $sequenceType
     * @return $this
     */
    public function setSequenceType($sequenceType)
    {
        $this->sequenceType = $sequenceType;
        return $this;
    }

    /**
     * Number of linear sequences of amino acids linked through peptide bonds. The number of subunits constituting the SubstanceProtein shall be described. It is possible that the number of subunits can be variable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getNumberOfSubunits()
    {
        return $this->numberOfSubunits;
    }

    /**
     * Number of linear sequences of amino acids linked through peptide bonds. The number of subunits constituting the SubstanceProtein shall be described. It is possible that the number of subunits can be variable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $numberOfSubunits
     * @return $this
     */
    public function setNumberOfSubunits($numberOfSubunits)
    {
        $this->numberOfSubunits = $numberOfSubunits;
        return $this;
    }

    /**
     * The disulphide bond between two cysteine residues either on the same subunit or on two different subunits shall be described. The position of the disulfide bonds in the SubstanceProtein shall be listed in increasing order of subunit number and position within subunit followed by the abbreviation of the amino acids involved. The disulfide linkage positions shall actually contain the amino acid Cysteine at the respective positions.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getDisulfideLinkage()
    {
        return $this->disulfideLinkage;
    }

    /**
     * The disulphide bond between two cysteine residues either on the same subunit or on two different subunits shall be described. The position of the disulfide bonds in the SubstanceProtein shall be listed in increasing order of subunit number and position within subunit followed by the abbreviation of the amino acids involved. The disulfide linkage positions shall actually contain the amino acid Cysteine at the respective positions.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $disulfideLinkage
     * @return $this
     */
    public function addDisulfideLinkage($disulfideLinkage)
    {
        $this->disulfideLinkage[] = $disulfideLinkage;
        return $this;
    }

    /**
     * This subclause refers to the description of each subunit constituting the SubstanceProtein. A subunit is a linear sequence of amino acids linked through peptide bonds. The Subunit information shall be provided when the finished SubstanceProtein is a complex of multiple sequences; subunits are not used to delineate domains within a single sequence. Subunits are listed in order of decreasing length; sequences of the same length will be ordered by decreasing molecular weight; subunits that have identical sequences will be repeated multiple times.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit[]
     */
    public function getSubunit()
    {
        return $this->subunit;
    }

    /**
     * This subclause refers to the description of each subunit constituting the SubstanceProtein. A subunit is a linear sequence of amino acids linked through peptide bonds. The Subunit information shall be provided when the finished SubstanceProtein is a complex of multiple sequences; subunits are not used to delineate domains within a single sequence. Subunits are listed in order of decreasing length; sequences of the same length will be ordered by decreasing molecular weight; subunits that have identical sequences will be repeated multiple times.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit $subunit
     * @return $this
     */
    public function addSubunit($subunit)
    {
        $this->subunit[] = $subunit;
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
            if (isset($data['sequenceType'])) {
                $this->setSequenceType($data['sequenceType']);
            }
            if (isset($data['numberOfSubunits'])) {
                $this->setNumberOfSubunits($data['numberOfSubunits']);
            }
            if (isset($data['disulfideLinkage'])) {
                if (is_array($data['disulfideLinkage'])) {
                    foreach ($data['disulfideLinkage'] as $d) {
                        $this->addDisulfideLinkage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"disulfideLinkage" must be array of objects or null, ' . gettype($data['disulfideLinkage']) . ' seen.');
                }
            }
            if (isset($data['subunit'])) {
                if (is_array($data['subunit'])) {
                    foreach ($data['subunit'] as $d) {
                        $this->addSubunit($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subunit" must be array of objects or null, ' . gettype($data['subunit']) . ' seen.');
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
        if (isset($this->sequenceType)) {
            $json['sequenceType'] = $this->sequenceType;
        }
        if (isset($this->numberOfSubunits)) {
            $json['numberOfSubunits'] = $this->numberOfSubunits;
        }
        if (0 < count($this->disulfideLinkage)) {
            $json['disulfideLinkage'] = [];
            foreach ($this->disulfideLinkage as $disulfideLinkage) {
                $json['disulfideLinkage'][] = $disulfideLinkage;
            }
        }
        if (0 < count($this->subunit)) {
            $json['subunit'] = [];
            foreach ($this->subunit as $subunit) {
                $json['subunit'][] = $subunit;
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
            $sxe = new \SimpleXMLElement('<SubstanceProtein xmlns="http://hl7.org/fhir"></SubstanceProtein>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->sequenceType)) {
            $this->sequenceType->xmlSerialize(true, $sxe->addChild('sequenceType'));
        }
        if (isset($this->numberOfSubunits)) {
            $this->numberOfSubunits->xmlSerialize(true, $sxe->addChild('numberOfSubunits'));
        }
        if (0 < count($this->disulfideLinkage)) {
            foreach ($this->disulfideLinkage as $disulfideLinkage) {
                $disulfideLinkage->xmlSerialize(true, $sxe->addChild('disulfideLinkage'));
            }
        }
        if (0 < count($this->subunit)) {
            foreach ($this->subunit as $subunit) {
                $subunit->xmlSerialize(true, $sxe->addChild('subunit'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
