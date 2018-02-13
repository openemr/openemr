<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;
use HL7\FHIR\STU3\PHPFHIRHelper;

/**
 * A human-readable formatted text, including images.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRNarrative extends FHIRElement implements \JsonSerializable
{
    /**
     * The status of the narrative - whether it's entirely generated (from just the defined data or the extensions too), or whether a human authored it and it may contain additional data.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRNarrativeStatus
     */
    public $status = null;

    /**
     * The actual narrative content, a stripped down version of XHTML.
     * @var \string
     */
    public $div = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Narrative';

    /**
     * The status of the narrative - whether it's entirely generated (from just the defined data or the extensions too), or whether a human authored it and it may contain additional data.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRNarrativeStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of the narrative - whether it's entirely generated (from just the defined data or the extensions too), or whether a human authored it and it may contain additional data.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRNarrativeStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The actual narrative content, a stripped down version of XHTML.
     * @return \string
     */
    public function getDiv()
    {
        return $this->div;
    }

    /**
     * The actual narrative content, a stripped down version of XHTML.
     * @param \string $div
     * @return $this
     */
    public function setDiv($div)
    {
        $this->div = $div;
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
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['div'])) {
                $this->setDiv($data['div']);
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->div)) {
            $json['div'] = $this->div;
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
            $sxe = new \SimpleXMLElement('<Narrative xmlns="http://hl7.org/fhir"></Narrative>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->div)) {
            PHPFHIRHelper::recursiveXMLImport($sxe, $this->div);
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
