<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRList;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A set of information summarized from a list of other resources.
 */
class FHIRListEntry extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The flag allows the system constructing the list to indicate the role and significance of the item in the list.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $flag = null;

    /**
     * True if this item is marked as deleted in the list.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $deleted = null;

    /**
     * When this item was added to the list.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * A reference to the actual resource from which data was derived.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $item = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'List.Entry';

    /**
     * The flag allows the system constructing the list to indicate the role and significance of the item in the list.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * The flag allows the system constructing the list to indicate the role and significance of the item in the list.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $flag
     * @return $this
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;
        return $this;
    }

    /**
     * True if this item is marked as deleted in the list.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * True if this item is marked as deleted in the list.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * When this item was added to the list.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * When this item was added to the list.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * A reference to the actual resource from which data was derived.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * A reference to the actual resource from which data was derived.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $item
     * @return $this
     */
    public function setItem($item)
    {
        $this->item = $item;
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
            if (isset($data['flag'])) {
                $this->setFlag($data['flag']);
            }
            if (isset($data['deleted'])) {
                $this->setDeleted($data['deleted']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['item'])) {
                $this->setItem($data['item']);
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
        if (isset($this->flag)) {
            $json['flag'] = $this->flag;
        }
        if (isset($this->deleted)) {
            $json['deleted'] = $this->deleted;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (isset($this->item)) {
            $json['item'] = $this->item;
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
            $sxe = new \SimpleXMLElement('<ListEntry xmlns="http://hl7.org/fhir"></ListEntry>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->flag)) {
            $this->flag->xmlSerialize(true, $sxe->addChild('flag'));
        }
        if (isset($this->deleted)) {
            $this->deleted->xmlSerialize(true, $sxe->addChild('deleted'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->item)) {
            $this->item->xmlSerialize(true, $sxe->addChild('item'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
