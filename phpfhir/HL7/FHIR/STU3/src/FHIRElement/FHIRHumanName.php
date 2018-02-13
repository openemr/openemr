<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * A human's name with the ability to identify parts and usage.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRHumanName extends FHIRElement implements \JsonSerializable
{
    /**
     * Identifies the purpose for this name.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRNameUse
     */
    public $use = null;

    /**
     * A full text representation of the name.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * The part of a name that links to the genealogy. In some cultures (e.g. Eritrea) the family name of a son is the first name of his father.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $family = null;

    /**
     * Given name.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $given = [];

    /**
     * Part of the name that is acquired as a title due to academic, legal, employment or nobility status, etc. and that appears at the start of the name.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $prefix = [];

    /**
     * Part of the name that is acquired as a title due to academic, legal, employment or nobility status, etc. and that appears at the end of the name.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $suffix = [];

    /**
     * Indicates the period of time when this name was valid for the named person.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'HumanName';

    /**
     * Identifies the purpose for this name.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRNameUse
     */
    public function getUse()
    {
        return $this->use;
    }

    /**
     * Identifies the purpose for this name.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRNameUse $use
     * @return $this
     */
    public function setUse($use)
    {
        $this->use = $use;
        return $this;
    }

    /**
     * A full text representation of the name.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * A full text representation of the name.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * The part of a name that links to the genealogy. In some cultures (e.g. Eritrea) the family name of a son is the first name of his father.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * The part of a name that links to the genealogy. In some cultures (e.g. Eritrea) the family name of a son is the first name of his father.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $family
     * @return $this
     */
    public function setFamily($family)
    {
        $this->family = $family;
        return $this;
    }

    /**
     * Given name.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getGiven()
    {
        return $this->given;
    }

    /**
     * Given name.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $given
     * @return $this
     */
    public function addGiven($given)
    {
        $this->given[] = $given;
        return $this;
    }

    /**
     * Part of the name that is acquired as a title due to academic, legal, employment or nobility status, etc. and that appears at the start of the name.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Part of the name that is acquired as a title due to academic, legal, employment or nobility status, etc. and that appears at the start of the name.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $prefix
     * @return $this
     */
    public function addPrefix($prefix)
    {
        $this->prefix[] = $prefix;
        return $this;
    }

    /**
     * Part of the name that is acquired as a title due to academic, legal, employment or nobility status, etc. and that appears at the end of the name.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Part of the name that is acquired as a title due to academic, legal, employment or nobility status, etc. and that appears at the end of the name.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $suffix
     * @return $this
     */
    public function addSuffix($suffix)
    {
        $this->suffix[] = $suffix;
        return $this;
    }

    /**
     * Indicates the period of time when this name was valid for the named person.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Indicates the period of time when this name was valid for the named person.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
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
            if (isset($data['use'])) {
                $this->setUse($data['use']);
            }
            if (isset($data['text'])) {
                $this->setText($data['text']);
            }
            if (isset($data['family'])) {
                $this->setFamily($data['family']);
            }
            if (isset($data['given'])) {
                if (is_array($data['given'])) {
                    foreach ($data['given'] as $d) {
                        $this->addGiven($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"given" must be array of objects or null, '.gettype($data['given']).' seen.');
                }
            }
            if (isset($data['prefix'])) {
                if (is_array($data['prefix'])) {
                    foreach ($data['prefix'] as $d) {
                        $this->addPrefix($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"prefix" must be array of objects or null, '.gettype($data['prefix']).' seen.');
                }
            }
            if (isset($data['suffix'])) {
                if (is_array($data['suffix'])) {
                    foreach ($data['suffix'] as $d) {
                        $this->addSuffix($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"suffix" must be array of objects or null, '.gettype($data['suffix']).' seen.');
                }
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
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
        if (isset($this->use)) {
            $json['use'] = $this->use;
        }
        if (isset($this->text)) {
            $json['text'] = $this->text;
        }
        if (isset($this->family)) {
            $json['family'] = $this->family;
        }
        if (0 < count($this->given)) {
            $json['given'] = [];
            foreach ($this->given as $given) {
                $json['given'][] = $given;
            }
        }
        if (0 < count($this->prefix)) {
            $json['prefix'] = [];
            foreach ($this->prefix as $prefix) {
                $json['prefix'][] = $prefix;
            }
        }
        if (0 < count($this->suffix)) {
            $json['suffix'] = [];
            foreach ($this->suffix as $suffix) {
                $json['suffix'][] = $suffix;
            }
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
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
            $sxe = new \SimpleXMLElement('<HumanName xmlns="http://hl7.org/fhir"></HumanName>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->use)) {
            $this->use->xmlSerialize(true, $sxe->addChild('use'));
        }
        if (isset($this->text)) {
            $this->text->xmlSerialize(true, $sxe->addChild('text'));
        }
        if (isset($this->family)) {
            $this->family->xmlSerialize(true, $sxe->addChild('family'));
        }
        if (0 < count($this->given)) {
            foreach ($this->given as $given) {
                $given->xmlSerialize(true, $sxe->addChild('given'));
            }
        }
        if (0 < count($this->prefix)) {
            foreach ($this->prefix as $prefix) {
                $prefix->xmlSerialize(true, $sxe->addChild('prefix'));
            }
        }
        if (0 < count($this->suffix)) {
            foreach ($this->suffix as $suffix) {
                $suffix->xmlSerialize(true, $sxe->addChild('suffix'));
            }
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
