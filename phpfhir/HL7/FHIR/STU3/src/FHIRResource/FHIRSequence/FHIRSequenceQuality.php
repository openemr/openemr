<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRSequence;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Raw data describing a biological sequence.
 */
class FHIRSequenceQuality extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * INDEL / SNP / Undefined variant.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQualityType
     */
    public $type = null;

    /**
     * Gold standard sequence used for comparing against.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $standardSequence = null;

    /**
     * Start position of the sequence. If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $start = null;

    /**
     * End position of the sequence.If the coordinate system is 0-based then end is is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $end = null;

    /**
     * The score of an experimentally derived feature such as a p-value ([SO:0001685](http://www.sequenceontology.org/browser/current_svn/term/SO:0001685)).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $score = null;

    /**
     * Which method is used to get sequence quality.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $method = null;

    /**
     * True positives, from the perspective of the truth data, i.e. the number of sites in the Truth Call Set for which there are paths through the Query Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $truthTP = null;

    /**
     * True positives, from the perspective of the query data, i.e. the number of sites in the Query Call Set for which there are paths through the Truth Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $queryTP = null;

    /**
     * False negatives, i.e. the number of sites in the Truth Call Set for which there is no path through the Query Call Set that is consistent with all of the alleles at this site, or sites for which there is an inaccurate genotype call for the event. Sites with correct variant but incorrect genotype are counted here.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $truthFN = null;

    /**
     * False positives, i.e. the number of sites in the Query Call Set for which there is no path through the Truth Call Set that is consistent with this site. Sites with correct variant but incorrect genotype are counted here.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $queryFP = null;

    /**
     * The number of false positives where the non-REF alleles in the Truth and Query Call Sets match (i.e. cases where the truth is 1/1 and the query is 0/1 or similar).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $gtFP = null;

    /**
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $precision = null;

    /**
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $recall = null;

    /**
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall / (precision + recall).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $fScore = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Sequence.Quality';

    /**
     * INDEL / SNP / Undefined variant.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQualityType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * INDEL / SNP / Undefined variant.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQualityType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Gold standard sequence used for comparing against.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getStandardSequence()
    {
        return $this->standardSequence;
    }

    /**
     * Gold standard sequence used for comparing against.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $standardSequence
     * @return $this
     */
    public function setStandardSequence($standardSequence)
    {
        $this->standardSequence = $standardSequence;
        return $this;
    }

    /**
     * Start position of the sequence. If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Start position of the sequence. If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $start
     * @return $this
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * End position of the sequence.If the coordinate system is 0-based then end is is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * End position of the sequence.If the coordinate system is 0-based then end is is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $end
     * @return $this
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * The score of an experimentally derived feature such as a p-value ([SO:0001685](http://www.sequenceontology.org/browser/current_svn/term/SO:0001685)).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * The score of an experimentally derived feature such as a p-value ([SO:0001685](http://www.sequenceontology.org/browser/current_svn/term/SO:0001685)).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $score
     * @return $this
     */
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }

    /**
     * Which method is used to get sequence quality.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Which method is used to get sequence quality.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * True positives, from the perspective of the truth data, i.e. the number of sites in the Truth Call Set for which there are paths through the Query Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getTruthTP()
    {
        return $this->truthTP;
    }

    /**
     * True positives, from the perspective of the truth data, i.e. the number of sites in the Truth Call Set for which there are paths through the Query Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $truthTP
     * @return $this
     */
    public function setTruthTP($truthTP)
    {
        $this->truthTP = $truthTP;
        return $this;
    }

    /**
     * True positives, from the perspective of the query data, i.e. the number of sites in the Query Call Set for which there are paths through the Truth Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getQueryTP()
    {
        return $this->queryTP;
    }

    /**
     * True positives, from the perspective of the query data, i.e. the number of sites in the Query Call Set for which there are paths through the Truth Call Set that are consistent with all of the alleles at this site, and for which there is an accurate genotype call for the event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $queryTP
     * @return $this
     */
    public function setQueryTP($queryTP)
    {
        $this->queryTP = $queryTP;
        return $this;
    }

    /**
     * False negatives, i.e. the number of sites in the Truth Call Set for which there is no path through the Query Call Set that is consistent with all of the alleles at this site, or sites for which there is an inaccurate genotype call for the event. Sites with correct variant but incorrect genotype are counted here.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getTruthFN()
    {
        return $this->truthFN;
    }

    /**
     * False negatives, i.e. the number of sites in the Truth Call Set for which there is no path through the Query Call Set that is consistent with all of the alleles at this site, or sites for which there is an inaccurate genotype call for the event. Sites with correct variant but incorrect genotype are counted here.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $truthFN
     * @return $this
     */
    public function setTruthFN($truthFN)
    {
        $this->truthFN = $truthFN;
        return $this;
    }

    /**
     * False positives, i.e. the number of sites in the Query Call Set for which there is no path through the Truth Call Set that is consistent with this site. Sites with correct variant but incorrect genotype are counted here.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getQueryFP()
    {
        return $this->queryFP;
    }

    /**
     * False positives, i.e. the number of sites in the Query Call Set for which there is no path through the Truth Call Set that is consistent with this site. Sites with correct variant but incorrect genotype are counted here.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $queryFP
     * @return $this
     */
    public function setQueryFP($queryFP)
    {
        $this->queryFP = $queryFP;
        return $this;
    }

    /**
     * The number of false positives where the non-REF alleles in the Truth and Query Call Sets match (i.e. cases where the truth is 1/1 and the query is 0/1 or similar).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getGtFP()
    {
        return $this->gtFP;
    }

    /**
     * The number of false positives where the non-REF alleles in the Truth and Query Call Sets match (i.e. cases where the truth is 1/1 and the query is 0/1 or similar).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $gtFP
     * @return $this
     */
    public function setGtFP($gtFP)
    {
        $this->gtFP = $gtFP;
        return $this;
    }

    /**
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * QUERY.TP / (QUERY.TP + QUERY.FP).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $precision
     * @return $this
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
        return $this;
    }

    /**
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getRecall()
    {
        return $this->recall;
    }

    /**
     * TRUTH.TP / (TRUTH.TP + TRUTH.FN).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $recall
     * @return $this
     */
    public function setRecall($recall)
    {
        $this->recall = $recall;
        return $this;
    }

    /**
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall / (precision + recall).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getFScore()
    {
        return $this->fScore;
    }

    /**
     * Harmonic mean of Recall and Precision, computed as: 2 * precision * recall / (precision + recall).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $fScore
     * @return $this
     */
    public function setFScore($fScore)
    {
        $this->fScore = $fScore;
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
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
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
            $sxe = new \SimpleXMLElement('<SequenceQuality xmlns="http://hl7.org/fhir"></SequenceQuality>');
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
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
