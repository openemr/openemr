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
class FHIRMolecularSequenceQuality extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * INDEL / SNP / Undefined variant.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQualityType
     */
    public $type = null;

    /**
     * Gold standard sequence used for comparing against.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $standardSequence = null;

    /**
     * Start position of the sequence. If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $start = null;

    /**
     * End position of the sequence. If the coordinate system is 0-based then end is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $end = null;

    /**
     * The score of an experimentally derived feature such as a p-value ([SO:0001685](http://www.sequenceontology.org/browser/current_svn/term/SO:0001685)).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $score = null;

    /**
     * Which method is used to get sequence quality.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $method = null;

    /**
     * True positives, from the perspective of the truth data, i.e. the number of sites in the Truth Call Set for which there are paths through the Query Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $truthTP = null;

    /**
     * True positives, from the perspective of the query data, i.e. the number of sites in the Query Call Set for which there are paths through the Truth Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $queryTP = null;

    /**
     * False negatives, i.e. the number of sites in the Truth Call Set for which there is no path through the Query Call Set that is consistent with all of the alleles at this site, or sites for which there is an inaccurate genotype call for the event. Sites with correct variant but incorrect genotype are counted here.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $truthFN = null;

    /**
     * False positives, i.e. the number of sites in the Query Call Set for which there is no path through the Truth Call Set that is consistent with this site. Sites with correct variant but incorrect genotype are counted here.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $queryFP = null;

    /**
     * The number of false positives where the non-REF alleles in the Truth and Query Call Sets match (i.e. cases where the truth is 1/1 and the query is 0/1 or similar).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $gtFP = null;

    /**
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $precision = null;

    /**
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $recall = null;

    /**
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall / (precision + recall).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $fScore = null;

    /**
     * Receiver Operator Characteristic (ROC) Curve  to give sensitivity/specificity tradeoff.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceRoc
     */
    public $roc = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MolecularSequence.Quality';

    /**
     * INDEL / SNP / Undefined variant.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQualityType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * INDEL / SNP / Undefined variant.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQualityType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Gold standard sequence used for comparing against.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStandardSequence()
    {
        return $this->standardSequence;
    }

    /**
     * Gold standard sequence used for comparing against.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $standardSequence
     * @return $this
     */
    public function setStandardSequence($standardSequence)
    {
        $this->standardSequence = $standardSequence;
        return $this;
    }

    /**
     * Start position of the sequence. If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Start position of the sequence. If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $start
     * @return $this
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * End position of the sequence. If the coordinate system is 0-based then end is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * End position of the sequence. If the coordinate system is 0-based then end is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $end
     * @return $this
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * The score of an experimentally derived feature such as a p-value ([SO:0001685](http://www.sequenceontology.org/browser/current_svn/term/SO:0001685)).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * The score of an experimentally derived feature such as a p-value ([SO:0001685](http://www.sequenceontology.org/browser/current_svn/term/SO:0001685)).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $score
     * @return $this
     */
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }

    /**
     * Which method is used to get sequence quality.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Which method is used to get sequence quality.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * True positives, from the perspective of the truth data, i.e. the number of sites in the Truth Call Set for which there are paths through the Query Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getTruthTP()
    {
        return $this->truthTP;
    }

    /**
     * True positives, from the perspective of the truth data, i.e. the number of sites in the Truth Call Set for which there are paths through the Query Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $truthTP
     * @return $this
     */
    public function setTruthTP($truthTP)
    {
        $this->truthTP = $truthTP;
        return $this;
    }

    /**
     * True positives, from the perspective of the query data, i.e. the number of sites in the Query Call Set for which there are paths through the Truth Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getQueryTP()
    {
        return $this->queryTP;
    }

    /**
     * True positives, from the perspective of the query data, i.e. the number of sites in the Query Call Set for which there are paths through the Truth Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $queryTP
     * @return $this
     */
    public function setQueryTP($queryTP)
    {
        $this->queryTP = $queryTP;
        return $this;
    }

    /**
     * False negatives, i.e. the number of sites in the Truth Call Set for which there is no path through the Query Call Set that is consistent with all of the alleles at this site, or sites for which there is an inaccurate genotype call for the event. Sites with correct variant but incorrect genotype are counted here.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getTruthFN()
    {
        return $this->truthFN;
    }

    /**
     * False negatives, i.e. the number of sites in the Truth Call Set for which there is no path through the Query Call Set that is consistent with all of the alleles at this site, or sites for which there is an inaccurate genotype call for the event. Sites with correct variant but incorrect genotype are counted here.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $truthFN
     * @return $this
     */
    public function setTruthFN($truthFN)
    {
        $this->truthFN = $truthFN;
        return $this;
    }

    /**
     * False positives, i.e. the number of sites in the Query Call Set for which there is no path through the Truth Call Set that is consistent with this site. Sites with correct variant but incorrect genotype are counted here.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getQueryFP()
    {
        return $this->queryFP;
    }

    /**
     * False positives, i.e. the number of sites in the Query Call Set for which there is no path through the Truth Call Set that is consistent with this site. Sites with correct variant but incorrect genotype are counted here.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $queryFP
     * @return $this
     */
    public function setQueryFP($queryFP)
    {
        $this->queryFP = $queryFP;
        return $this;
    }

    /**
     * The number of false positives where the non-REF alleles in the Truth and Query Call Sets match (i.e. cases where the truth is 1/1 and the query is 0/1 or similar).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getGtFP()
    {
        return $this->gtFP;
    }

    /**
     * The number of false positives where the non-REF alleles in the Truth and Query Call Sets match (i.e. cases where the truth is 1/1 and the query is 0/1 or similar).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $gtFP
     * @return $this
     */
    public function setGtFP($gtFP)
    {
        $this->gtFP = $gtFP;
        return $this;
    }

    /**
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $precision
     * @return $this
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
        return $this;
    }

    /**
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getRecall()
    {
        return $this->recall;
    }

    /**
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $recall
     * @return $this
     */
    public function setRecall($recall)
    {
        $this->recall = $recall;
        return $this;
    }

    /**
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall / (precision + recall).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getFScore()
    {
        return $this->fScore;
    }

    /**
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall / (precision + recall).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $fScore
     * @return $this
     */
    public function setFScore($fScore)
    {
        $this->fScore = $fScore;
        return $this;
    }

    /**
     * Receiver Operator Characteristic (ROC) Curve  to give sensitivity/specificity tradeoff.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceRoc
     */
    public function getRoc()
    {
        return $this->roc;
    }

    /**
     * Receiver Operator Characteristic (ROC) Curve  to give sensitivity/specificity tradeoff.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMolecularSequence\FHIRMolecularSequenceRoc $roc
     * @return $this
     */
    public function setRoc($roc)
    {
        $this->roc = $roc;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['standardSequence'])) {
                $this->setStandardSequence($data['standardSequence']);
            }
            if (isset($data['start'])) {
                $this->setStart($data['start']);
            }
            if (isset($data['end'])) {
                $this->setEnd($data['end']);
            }
            if (isset($data['score'])) {
                $this->setScore($data['score']);
            }
            if (isset($data['method'])) {
                $this->setMethod($data['method']);
            }
            if (isset($data['truthTP'])) {
                $this->setTruthTP($data['truthTP']);
            }
            if (isset($data['queryTP'])) {
                $this->setQueryTP($data['queryTP']);
            }
            if (isset($data['truthFN'])) {
                $this->setTruthFN($data['truthFN']);
            }
            if (isset($data['queryFP'])) {
                $this->setQueryFP($data['queryFP']);
            }
            if (isset($data['gtFP'])) {
                $this->setGtFP($data['gtFP']);
            }
            if (isset($data['precision'])) {
                $this->setPrecision($data['precision']);
            }
            if (isset($data['recall'])) {
                $this->setRecall($data['recall']);
            }
            if (isset($data['fScore'])) {
                $this->setFScore($data['fScore']);
            }
            if (isset($data['roc'])) {
                $this->setRoc($data['roc']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->standardSequence)) {
            $json['standardSequence'] = $this->standardSequence;
        }
        if (isset($this->start)) {
            $json['start'] = $this->start;
        }
        if (isset($this->end)) {
            $json['end'] = $this->end;
        }
        if (isset($this->score)) {
            $json['score'] = $this->score;
        }
        if (isset($this->method)) {
            $json['method'] = $this->method;
        }
        if (isset($this->truthTP)) {
            $json['truthTP'] = $this->truthTP;
        }
        if (isset($this->queryTP)) {
            $json['queryTP'] = $this->queryTP;
        }
        if (isset($this->truthFN)) {
            $json['truthFN'] = $this->truthFN;
        }
        if (isset($this->queryFP)) {
            $json['queryFP'] = $this->queryFP;
        }
        if (isset($this->gtFP)) {
            $json['gtFP'] = $this->gtFP;
        }
        if (isset($this->precision)) {
            $json['precision'] = $this->precision;
        }
        if (isset($this->recall)) {
            $json['recall'] = $this->recall;
        }
        if (isset($this->fScore)) {
            $json['fScore'] = $this->fScore;
        }
        if (isset($this->roc)) {
            $json['roc'] = $this->roc;
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
            $sxe = new \SimpleXMLElement('<MolecularSequenceQuality xmlns="http://hl7.org/fhir"></MolecularSequenceQuality>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->standardSequence)) {
            $this->standardSequence->xmlSerialize(true, $sxe->addChild('standardSequence'));
        }
        if (isset($this->start)) {
            $this->start->xmlSerialize(true, $sxe->addChild('start'));
        }
        if (isset($this->end)) {
            $this->end->xmlSerialize(true, $sxe->addChild('end'));
        }
        if (isset($this->score)) {
            $this->score->xmlSerialize(true, $sxe->addChild('score'));
        }
        if (isset($this->method)) {
            $this->method->xmlSerialize(true, $sxe->addChild('method'));
        }
        if (isset($this->truthTP)) {
            $this->truthTP->xmlSerialize(true, $sxe->addChild('truthTP'));
        }
        if (isset($this->queryTP)) {
            $this->queryTP->xmlSerialize(true, $sxe->addChild('queryTP'));
        }
        if (isset($this->truthFN)) {
            $this->truthFN->xmlSerialize(true, $sxe->addChild('truthFN'));
        }
        if (isset($this->queryFP)) {
            $this->queryFP->xmlSerialize(true, $sxe->addChild('queryFP'));
        }
        if (isset($this->gtFP)) {
            $this->gtFP->xmlSerialize(true, $sxe->addChild('gtFP'));
        }
        if (isset($this->precision)) {
            $this->precision->xmlSerialize(true, $sxe->addChild('precision'));
        }
        if (isset($this->recall)) {
            $this->recall->xmlSerialize(true, $sxe->addChild('recall'));
        }
        if (isset($this->fScore)) {
            $this->fScore->xmlSerialize(true, $sxe->addChild('fScore'));
        }
        if (isset($this->roc)) {
            $this->roc->xmlSerialize(true, $sxe->addChild('roc'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
