<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRBundle;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A container for a collection of resources.
 */
class FHIRBundleSearch extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Why this entry is in the result set - whether it's included as a match or because of an _include requirement.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSearchEntryMode
     */
    public $mode = null;

    /**
     * When searching, the server's search ranking score for the entry.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $score = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Bundle.Search';

    /**
     * Why this entry is in the result set - whether it's included as a match or because of an _include requirement.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSearchEntryMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Why this entry is in the result set - whether it's included as a match or because of an _include requirement.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSearchEntryMode $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * When searching, the server's search ranking score for the entry.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * When searching, the server's search ranking score for the entry.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $score
     * @return $this
     */
    public function setScore($score)
    {
        $this->score = $score;
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
            if (isset($data['mode'])) {
                $this->setMode($data['mode']);
            }
            if (isset($data['score'])) {
                $this->setScore($data['score']);
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
        if (isset($this->mode)) {
            $json['mode'] = $this->mode;
        }
        if (isset($this->score)) {
            $json['score'] = $this->score;
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
            $sxe = new \SimpleXMLElement('<BundleSearch xmlns="http://hl7.org/fhir"></BundleSearch>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->mode)) {
            $this->mode->xmlSerialize(true, $sxe->addChild('mode'));
        }
        if (isset($this->score)) {
            $this->score->xmlSerialize(true, $sxe->addChild('score'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
