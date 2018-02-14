<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
* Class creation date: February 10th, 2018 *
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * A concept that may be defined by a formal reference to a terminology or ontology or may be provided by text.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRCodeableConcept extends FHIRElement implements \JsonSerializable
{
    /**
     * A reference to a code defined by a terminology system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public $coding = [];

    /**
     * A human language representation of the concept as seen/selected/uttered by the user who entered the data and/or which represents the intended meaning of the user.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CodeableConcept';

    /**
     * A reference to a code defined by a terminology system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public function getCoding()
    {
        return $this->coding;
    }

    /**
     * A reference to a code defined by a terminology system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $coding
     * @return $this
     */
    public function addCoding($coding)
    {
        $this->coding[] = $coding;
        return $this;
    }

    /**
     * A human language representation of the concept as seen/selected/uttered by the user who entered the data and/or which represents the intended meaning of the user.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * A human language representation of the concept as seen/selected/uttered by the user who entered the data and/or which represents the intended meaning of the user.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
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
            if (isset($data['coding'])) {
                if (is_array($data['coding'])) {
                    foreach ($data['coding'] as $d) {
                        $this->addCoding($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"coding" must be array of objects or null, '.gettype($data['coding']).' seen.');
                }
            }
            if (isset($data['text'])) {
                $this->setText($data['text']);
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
        if (0 < count($this->coding)) {
            $json['coding'] = [];
            foreach ($this->coding as $coding) {
                $json['coding'][] = $coding;
            }
        }
        if (isset($this->text)) {
            $json['text'] = $this->text;
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
            $sxe = new \SimpleXMLElement('<CodeableConcept xmlns="http://hl7.org/fhir"></CodeableConcept>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->coding)) {
            foreach ($this->coding as $coding) {
                $coding->xmlSerialize(true, $sxe->addChild('coding'));
            }
        }
        if (isset($this->text)) {
            $this->text->xmlSerialize(true, $sxe->addChild('text'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
