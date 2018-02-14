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
class FHIRSequenceVariant extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Start position of the variant on the  reference sequence.If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $start = null;

    /**
     * End position of the variant on the reference sequence.If the coordinate system is 0-based then end is is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $end = null;

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).  Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the observed  sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $observedAllele = null;

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)). Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the reference sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $referenceAllele = null;

    /**
     * Extended CIGAR string for aligning the sequence with reference bases. See detailed documentation [here](http://support.illumina.com/help/SequencingAnalysisWorkflow/Content/Vault/Informatics/Sequencing_Analysis/CASAVA/swSEQ_mCA_ExtendedCIGARFormat.htm).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $cigar = null;

    /**
     * A pointer to an Observation containing variant information.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $variantPointer = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Sequence.Variant';

    /**
     * Start position of the variant on the  reference sequence.If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Start position of the variant on the  reference sequence.If the coordinate system is either 0-based or 1-based, then start position is inclusive.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $start
     * @return $this
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * End position of the variant on the reference sequence.If the coordinate system is 0-based then end is is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * End position of the variant on the reference sequence.If the coordinate system is 0-based then end is is exclusive and does not include the last position. If the coordinate system is 1-base, then end is inclusive and includes the last position.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $end
     * @return $this
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).  Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the observed  sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getObservedAllele()
    {
        return $this->observedAllele;
    }

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).  Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the observed  sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $observedAllele
     * @return $this
     */
    public function setObservedAllele($observedAllele)
    {
        $this->observedAllele = $observedAllele;
        return $this;
    }

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)). Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the reference sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getReferenceAllele()
    {
        return $this->referenceAllele;
    }

    /**
     * An allele is one of a set of coexisting sequence variants of a gene ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)). Nucleotide(s)/amino acids from start position of sequence to stop position of sequence on the positive (+) strand of the reference sequence. When the sequence  type is DNA, it should be the sequence on the positive (+) strand. This will lay in the range between variant.start and variant.end.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $referenceAllele
     * @return $this
     */
    public function setReferenceAllele($referenceAllele)
    {
        $this->referenceAllele = $referenceAllele;
        return $this;
    }

    /**
     * Extended CIGAR string for aligning the sequence with reference bases. See detailed documentation [here](http://support.illumina.com/help/SequencingAnalysisWorkflow/Content/Vault/Informatics/Sequencing_Analysis/CASAVA/swSEQ_mCA_ExtendedCIGARFormat.htm).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCigar()
    {
        return $this->cigar;
    }

    /**
     * Extended CIGAR string for aligning the sequence with reference bases. See detailed documentation [here](http://support.illumina.com/help/SequencingAnalysisWorkflow/Content/Vault/Informatics/Sequencing_Analysis/CASAVA/swSEQ_mCA_ExtendedCIGARFormat.htm).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $cigar
     * @return $this
     */
    public function setCigar($cigar)
    {
        $this->cigar = $cigar;
        return $this;
    }

    /**
     * A pointer to an Observation containing variant information.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getVariantPointer()
    {
        return $this->variantPointer;
    }

    /**
     * A pointer to an Observation containing variant information.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $variantPointer
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
            $sxe = new \SimpleXMLElement('<SequenceVariant xmlns="http://hl7.org/fhir"></SequenceVariant>');
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
