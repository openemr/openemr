<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * A  text note which also  contains information about who made the statement and when.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRAnnotation extends FHIRElement implements \JsonSerializable
{
    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $authorReference = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $authorString = null;

    /**
     * Indicates when this particular annotation was made.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $time = null;

    /**
     * The text of the annotation.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Annotation';

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getAuthorReference()
    {
        return $this->authorReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $authorReference
     * @return $this
     */
    public function setAuthorReference($authorReference)
    {
        $this->authorReference = $authorReference;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getAuthorString()
    {
        return $this->authorString;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $authorString
     * @return $this
     */
    public function setAuthorString($authorString)
    {
        $this->authorString = $authorString;
        return $this;
    }

    /**
     * Indicates when this particular annotation was made.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Indicates when this particular annotation was made.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * The text of the annotation.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * The text of the annotation.
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
            if (isset($data['authorReference'])) {
                $this->setAuthorReference($data['authorReference']);
            }
            if (isset($data['authorString'])) {
                $this->setAuthorString($data['authorString']);
            }
            if (isset($data['time'])) {
                $this->setTime($data['time']);
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
        if (isset($this->authorReference)) {
            $json['authorReference'] = $this->authorReference;
        }
        if (isset($this->authorString)) {
            $json['authorString'] = $this->authorString;
        }
        if (isset($this->time)) {
            $json['time'] = $this->time;
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
            $sxe = new \SimpleXMLElement('<Annotation xmlns="http://hl7.org/fhir"></Annotation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->authorReference)) {
            $this->authorReference->xmlSerialize(true, $sxe->addChild('authorReference'));
        }
        if (isset($this->authorString)) {
            $this->authorString->xmlSerialize(true, $sxe->addChild('authorString'));
        }
        if (isset($this->time)) {
            $this->time->xmlSerialize(true, $sxe->addChild('time'));
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
