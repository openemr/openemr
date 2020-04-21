<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence;

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
 * Raw data describing a biological sequence.
 */
class FHIRMolecularSequenceVariant extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Start position of the variant on the  reference sequence. If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $start = null;

    /**
     * End position of the variant on the reference sequence. If the coordinate system is 0-based then end is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $end = null;

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).  Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the observed  sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $observedAllele = null;

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)). Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the reference sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $referenceAllele = null;

    /**
     * Extended CIGAR string for aligning the sequence with reference bases. See detailed documentation [here](http://support.illumina.com/help/SequencingAnalysisWorkflow/Content/Vault/Informatics/Sequencing_Analysis/CASAVA/swSEQ_mCA_ExtendedCIGARFormat.htm).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $cigar = null;

    /**
     * A pointer to an Observation containing variant information.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $variantPointer = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MolecularSequence.Variant';

    /**
     * Start position of the variant on the  reference sequence. If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Start position of the variant on the  reference sequence. If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $start
     * @return $this
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * End position of the variant on the reference sequence. If the coordinate system is 0-based then end is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * End position of the variant on the reference sequence. If the coordinate system is 0-based then end is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $end
     * @return $this
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).  Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the observed  sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getObservedAllele()
    {
        return $this->observedAllele;
    }

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).  Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the observed  sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $observedAllele
     * @return $this
     */
    public function setObservedAllele($observedAllele)
    {
        $this->observedAllele = $observedAllele;
        return $this;
    }

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)). Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the reference sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getReferenceAllele()
    {
        return $this->referenceAllele;
    }

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)). Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the reference sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $referenceAllele
     * @return $this
     */
    public function setReferenceAllele($referenceAllele)
    {
        $this->referenceAllele = $referenceAllele;
        return $this;
    }

    /**
     * Extended CIGAR string for aligning the sequence with reference bases. See detailed documentation [here](http://support.illumina.com/help/SequencingAnalysisWorkflow/Content/Vault/Informatics/Sequencing_Analysis/CASAVA/swSEQ_mCA_ExtendedCIGARFormat.htm).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCigar()
    {
        return $this->cigar;
    }

    /**
     * Extended CIGAR string for aligning the sequence with reference bases. See detailed documentation [here](http://support.illumina.com/help/SequencingAnalysisWorkflow/Content/Vault/Informatics/Sequencing_Analysis/CASAVA/swSEQ_mCA_ExtendedCIGARFormat.htm).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $cigar
     * @return $this
     */
    public function setCigar($cigar)
    {
        $this->cigar = $cigar;
        return $this;
    }

    /**
     * A pointer to an Observation containing variant information.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getVariantPointer()
    {
        return $this->variantPointer;
    }

    /**
     * A pointer to an Observation containing variant information.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $variantPointer
     * @return $this
     */
    public function setVariantPointer($variantPointer)
    {
        $this->variantPointer = $variantPointer;
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
            if (isset($data['start'])) {
                $this->setStart($data['start']);
            }
            if (isset($data['end'])) {
                $this->setEnd($data['end']);
            }
            if (isset($data['observedAllele'])) {
                $this->setObservedAllele($data['observedAllele']);
            }
            if (isset($data['referenceAllele'])) {
                $this->setReferenceAllele($data['referenceAllele']);
            }
            if (isset($data['cigar'])) {
                $this->setCigar($data['cigar']);
            }
            if (isset($data['variantPointer'])) {
                $this->setVariantPointer($data['variantPointer']);
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
        if (isset($this->start)) {
            $json['start'] = $this->start;
        }
        if (isset($this->end)) {
            $json['end'] = $this->end;
        }
        if (isset($this->observedAllele)) {
            $json['observedAllele'] = $this->observedAllele;
        }
        if (isset($this->referenceAllele)) {
            $json['referenceAllele'] = $this->referenceAllele;
        }
        if (isset($this->cigar)) {
            $json['cigar'] = $this->cigar;
        }
        if (isset($this->variantPointer)) {
            $json['variantPointer'] = $this->variantPointer;
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
            $sxe = new \SimpleXMLElement('<MolecularSequenceVariant xmlns="http://hl7.org/fhir"></MolecularSequenceVariant>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->start)) {
            $this->start->xmlSerialize(true, $sxe->addChild('start'));
        }
        if (isset($this->end)) {
            $this->end->xmlSerialize(true, $sxe->addChild('end'));
        }
        if (isset($this->observedAllele)) {
            $this->observedAllele->xmlSerialize(true, $sxe->addChild('observedAllele'));
        }
        if (isset($this->referenceAllele)) {
            $this->referenceAllele->xmlSerialize(true, $sxe->addChild('referenceAllele'));
        }
        if (isset($this->cigar)) {
            $this->cigar->xmlSerialize(true, $sxe->addChild('cigar'));
        }
        if (isset($this->variantPointer)) {
            $this->variantPointer->xmlSerialize(true, $sxe->addChild('variantPointer'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
