<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRMedication;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource is primarily used for the identification and definition of a medication. It covers the ingredients and the packaging for a medication.
 */
class FHIRMedicationBatch extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The assigned lot number of a batch of the specified product.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $lotNumber = null;

    /**
     * When this specific batch of product will expire.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $expirationDate = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Medication.Batch';

    /**
     * The assigned lot number of a batch of the specified product.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getLotNumber()
    {
        return $this->lotNumber;
    }

    /**
     * The assigned lot number of a batch of the specified product.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $lotNumber
     * @return $this
     */
    public function setLotNumber($lotNumber)
    {
        $this->lotNumber = $lotNumber;
        return $this;
    }

    /**
     * When this specific batch of product will expire.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * When this specific batch of product will expire.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $expirationDate
     * @return $this
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
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
            if (isset($data['lotNumber'])) {
                $this->setLotNumber($data['lotNumber']);
            }
            if (isset($data['expirationDate'])) {
                $this->setExpirationDate($data['expirationDate']);
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
        if (isset($this->lotNumber)) {
            $json['lotNumber'] = $this->lotNumber;
        }
        if (isset($this->expirationDate)) {
            $json['expirationDate'] = $this->expirationDate;
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
            $sxe = new \SimpleXMLElement('<MedicationBatch xmlns="http://hl7.org/fhir"></MedicationBatch>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->lotNumber)) {
            $this->lotNumber->xmlSerialize(true, $sxe->addChild('lotNumber'));
        }
        if (isset($this->expirationDate)) {
            $this->expirationDate->xmlSerialize(true, $sxe->addChild('expirationDate'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
