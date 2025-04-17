<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceNucleicAcid;

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
 * Nucleic acids are defined by three distinct elements: the base, sugar and linkage. Individual substance/moiety IDs will be created for each of these elements. The nucleotide sequence will be always entered in the 5’-3’ direction.
 */
class FHIRSubstanceNucleicAcidSubunit extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Index of linear sequences of nucleic acids in order of decreasing length. Sequences of the same length will be ordered by molecular weight. Subunits that have identical sequences will be repeated and have sequential subscripts.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $subunit = null;

    /**
     * Actual nucleotide sequence notation from 5' to 3' end using standard single letter codes. In addition to the base sequence, sugar and type of phosphate or non-phosphate linkage should also be captured.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $sequence = null;

    /**
     * The length of the sequence shall be captured.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $length = null;

    /**
     * (TBC).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public $sequenceAttachment = null;

    /**
     * The nucleotide present at the 5’ terminal shall be specified based on a controlled vocabulary. Since the sequence is represented from the 5' to the 3' end, the 5’ prime nucleotide is the letter at the first position in the sequence. A separate representation would be redundant.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $fivePrime = null;

    /**
     * The nucleotide present at the 3’ terminal shall be specified based on a controlled vocabulary. Since the sequence is represented from the 5' to the 3' end, the 5’ prime nucleotide is the letter at the last position in the sequence. A separate representation would be redundant.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $threePrime = null;

    /**
     * The linkages between sugar residues will also be captured.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidLinkage[]
     */
    public $linkage = [];

    /**
     * 5.3.6.8.1 Sugar ID (Mandatory).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSugar[]
     */
    public $sugar = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceNucleicAcid.Subunit';

    /**
     * Index of linear sequences of nucleic acids in order of decreasing length. Sequences of the same length will be ordered by molecular weight. Subunits that have identical sequences will be repeated and have sequential subscripts.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getSubunit()
    {
        return $this->subunit;
    }

    /**
     * Index of linear sequences of nucleic acids in order of decreasing length. Sequences of the same length will be ordered by molecular weight. Subunits that have identical sequences will be repeated and have sequential subscripts.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $subunit
     * @return $this
     */
    public function setSubunit($subunit)
    {
        $this->subunit = $subunit;
        return $this;
    }

    /**
     * Actual nucleotide sequence notation from 5' to 3' end using standard single letter codes. In addition to the base sequence, sugar and type of phosphate or non-phosphate linkage should also be captured.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Actual nucleotide sequence notation from 5' to 3' end using standard single letter codes. In addition to the base sequence, sugar and type of phosphate or non-phosphate linkage should also be captured.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $sequence
     * @return $this
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * The length of the sequence shall be captured.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * The length of the sequence shall be captured.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $length
     * @return $this
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * (TBC).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment
     */
    public function getSequenceAttachment()
    {
        return $this->sequenceAttachment;
    }

    /**
     * (TBC).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment $sequenceAttachment
     * @return $this
     */
    public function setSequenceAttachment($sequenceAttachment)
    {
        $this->sequenceAttachment = $sequenceAttachment;
        return $this;
    }

    /**
     * The nucleotide present at the 5’ terminal shall be specified based on a controlled vocabulary. Since the sequence is represented from the 5' to the 3' end, the 5’ prime nucleotide is the letter at the first position in the sequence. A separate representation would be redundant.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getFivePrime()
    {
        return $this->fivePrime;
    }

    /**
     * The nucleotide present at the 5’ terminal shall be specified based on a controlled vocabulary. Since the sequence is represented from the 5' to the 3' end, the 5’ prime nucleotide is the letter at the first position in the sequence. A separate representation would be redundant.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $fivePrime
     * @return $this
     */
    public function setFivePrime($fivePrime)
    {
        $this->fivePrime = $fivePrime;
        return $this;
    }

    /**
     * The nucleotide present at the 3’ terminal shall be specified based on a controlled vocabulary. Since the sequence is represented from the 5' to the 3' end, the 5’ prime nucleotide is the letter at the last position in the sequence. A separate representation would be redundant.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getThreePrime()
    {
        return $this->threePrime;
    }

    /**
     * The nucleotide present at the 3’ terminal shall be specified based on a controlled vocabulary. Since the sequence is represented from the 5' to the 3' end, the 5’ prime nucleotide is the letter at the last position in the sequence. A separate representation would be redundant.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $threePrime
     * @return $this
     */
    public function setThreePrime($threePrime)
    {
        $this->threePrime = $threePrime;
        return $this;
    }

    /**
     * The linkages between sugar residues will also be captured.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidLinkage[]
     */
    public function getLinkage()
    {
        return $this->linkage;
    }

    /**
     * The linkages between sugar residues will also be captured.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidLinkage $linkage
     * @return $this
     */
    public function addLinkage($linkage)
    {
        $this->linkage[] = $linkage;
        return $this;
    }

    /**
     * 5.3.6.8.1 Sugar ID (Mandatory).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSugar[]
     */
    public function getSugar()
    {
        return $this->sugar;
    }

    /**
     * 5.3.6.8.1 Sugar ID (Mandatory).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceNucleicAcid\FHIRSubstanceNucleicAcidSugar $sugar
     * @return $this
     */
    public function addSugar($sugar)
    {
        $this->sugar[] = $sugar;
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
            if (isset($data['subunit'])) {
                $this->setSubunit($data['subunit']);
            }
            if (isset($data['sequence'])) {
                $this->setSequence($data['sequence']);
            }
            if (isset($data['length'])) {
                $this->setLength($data['length']);
            }
            if (isset($data['sequenceAttachment'])) {
                $this->setSequenceAttachment($data['sequenceAttachment']);
            }
            if (isset($data['fivePrime'])) {
                $this->setFivePrime($data['fivePrime']);
            }
            if (isset($data['threePrime'])) {
                $this->setThreePrime($data['threePrime']);
            }
            if (isset($data['linkage'])) {
                if (is_array($data['linkage'])) {
                    foreach ($data['linkage'] as $d) {
                        $this->addLinkage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"linkage" must be array of objects or null, ' . gettype($data['linkage']) . ' seen.');
                }
            }
            if (isset($data['sugar'])) {
                if (is_array($data['sugar'])) {
                    foreach ($data['sugar'] as $d) {
                        $this->addSugar($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"sugar" must be array of objects or null, ' . gettype($data['sugar']) . ' seen.');
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
        if (isset($this->subunit)) {
            $json['subunit'] = $this->subunit;
        }
        if (isset($this->sequence)) {
            $json['sequence'] = $this->sequence;
        }
        if (isset($this->length)) {
            $json['length'] = $this->length;
        }
        if (isset($this->sequenceAttachment)) {
            $json['sequenceAttachment'] = $this->sequenceAttachment;
        }
        if (isset($this->fivePrime)) {
            $json['fivePrime'] = $this->fivePrime;
        }
        if (isset($this->threePrime)) {
            $json['threePrime'] = $this->threePrime;
        }
        if (0 < count($this->linkage)) {
            $json['linkage'] = [];
            foreach ($this->linkage as $linkage) {
                $json['linkage'][] = $linkage;
            }
        }
        if (0 < count($this->sugar)) {
            $json['sugar'] = [];
            foreach ($this->sugar as $sugar) {
                $json['sugar'][] = $sugar;
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
            $sxe = new \SimpleXMLElement('<SubstanceNucleicAcidSubunit xmlns="http://hl7.org/fhir"></SubstanceNucleicAcidSubunit>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->subunit)) {
            $this->subunit->xmlSerialize(true, $sxe->addChild('subunit'));
        }
        if (isset($this->sequence)) {
            $this->sequence->xmlSerialize(true, $sxe->addChild('sequence'));
        }
        if (isset($this->length)) {
            $this->length->xmlSerialize(true, $sxe->addChild('length'));
        }
        if (isset($this->sequenceAttachment)) {
            $this->sequenceAttachment->xmlSerialize(true, $sxe->addChild('sequenceAttachment'));
        }
        if (isset($this->fivePrime)) {
            $this->fivePrime->xmlSerialize(true, $sxe->addChild('fivePrime'));
        }
        if (isset($this->threePrime)) {
            $this->threePrime->xmlSerialize(true, $sxe->addChild('threePrime'));
        }
        if (0 < count($this->linkage)) {
            foreach ($this->linkage as $linkage) {
                $linkage->xmlSerialize(true, $sxe->addChild('linkage'));
            }
        }
        if (0 < count($this->sugar)) {
            foreach ($this->sugar as $sugar) {
                $sugar->xmlSerialize(true, $sxe->addChild('sugar'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
