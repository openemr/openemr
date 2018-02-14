<?php namespace HL7\FHIR\STU3\FHIRResource\FHIREligibilityResponse;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource provides eligibility and plan details from the processing of an Eligibility resource.
 */
class FHIREligibilityResponseFinancial extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Deductable, visits, benefit amount.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public $allowedUnsignedInt = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $allowedString = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $allowedMoney = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public $usedUnsignedInt = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $usedMoney = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'EligibilityResponse.Financial';

    /**
     * Deductable, visits, benefit amount.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Deductable, visits, benefit amount.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public function getAllowedUnsignedInt()
    {
        return $this->allowedUnsignedInt;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt $allowedUnsignedInt
     * @return $this
     */
    public function setAllowedUnsignedInt($allowedUnsignedInt)
    {
        $this->allowedUnsignedInt = $allowedUnsignedInt;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getAllowedString()
    {
        return $this->allowedString;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $allowedString
     * @return $this
     */
    public function setAllowedString($allowedString)
    {
        $this->allowedString = $allowedString;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getAllowedMoney()
    {
        return $this->allowedMoney;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $allowedMoney
     * @return $this
     */
    public function setAllowedMoney($allowedMoney)
    {
        $this->allowedMoney = $allowedMoney;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt
     */
    public function getUsedUnsignedInt()
    {
        return $this->usedUnsignedInt;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUnsignedInt $usedUnsignedInt
     * @return $this
     */
    public function setUsedUnsignedInt($usedUnsignedInt)
    {
        $this->usedUnsignedInt = $usedUnsignedInt;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getUsedMoney()
    {
        return $this->usedMoney;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $usedMoney
     * @return $this
     */
    public function setUsedMoney($usedMoney)
    {
        $this->usedMoney = $usedMoney;
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
            if (isset($data['allowedUnsignedInt'])) {
                $this->setAllowedUnsignedInt($data['allowedUnsignedInt']);
            }
            if (isset($data['allowedString'])) {
                $this->setAllowedString($data['allowedString']);
            }
            if (isset($data['allowedMoney'])) {
                $this->setAllowedMoney($data['allowedMoney']);
            }
            if (isset($data['usedUnsignedInt'])) {
                $this->setUsedUnsignedInt($data['usedUnsignedInt']);
            }
            if (isset($data['usedMoney'])) {
                $this->setUsedMoney($data['usedMoney']);
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
        if (isset($this->allowedUnsignedInt)) {
            $json['allowedUnsignedInt'] = $this->allowedUnsignedInt;
        }
        if (isset($this->allowedString)) {
            $json['allowedString'] = $this->allowedString;
        }
        if (isset($this->allowedMoney)) {
            $json['allowedMoney'] = $this->allowedMoney;
        }
        if (isset($this->usedUnsignedInt)) {
            $json['usedUnsignedInt'] = $this->usedUnsignedInt;
        }
        if (isset($this->usedMoney)) {
            $json['usedMoney'] = $this->usedMoney;
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
            $sxe = new \SimpleXMLElement('<EligibilityResponseFinancial xmlns="http://hl7.org/fhir"></EligibilityResponseFinancial>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->allowedUnsignedInt)) {
            $this->allowedUnsignedInt->xmlSerialize(true, $sxe->addChild('allowedUnsignedInt'));
        }
        if (isset($this->allowedString)) {
            $this->allowedString->xmlSerialize(true, $sxe->addChild('allowedString'));
        }
        if (isset($this->allowedMoney)) {
            $this->allowedMoney->xmlSerialize(true, $sxe->addChild('allowedMoney'));
        }
        if (isset($this->usedUnsignedInt)) {
            $this->usedUnsignedInt->xmlSerialize(true, $sxe->addChild('usedUnsignedInt'));
        }
        if (isset($this->usedMoney)) {
            $this->usedMoney->xmlSerialize(true, $sxe->addChild('usedMoney'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
