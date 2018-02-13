<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRAccount;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A financial tool for tracking value accrued for a particular purpose.  In the healthcare field, used to track charges for a patient, cost centers, etc.
 */
class FHIRAccountGuarantor extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The entity who is responsible.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $party = null;

    /**
     * A guarantor may be placed on credit hold or otherwise have their role temporarily suspended.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $onHold = null;

    /**
     * The timeframe during which the guarantor accepts responsibility for the account.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Account.Guarantor';

    /**
     * The entity who is responsible.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * The entity who is responsible.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $party
     * @return $this
     */
    public function setParty($party)
    {
        $this->party = $party;
        return $this;
    }

    /**
     * A guarantor may be placed on credit hold or otherwise have their role temporarily suspended.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getOnHold()
    {
        return $this->onHold;
    }

    /**
     * A guarantor may be placed on credit hold or otherwise have their role temporarily suspended.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $onHold
     * @return $this
     */
    public function setOnHold($onHold)
    {
        $this->onHold = $onHold;
        return $this;
    }

    /**
     * The timeframe during which the guarantor accepts responsibility for the account.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The timeframe during which the guarantor accepts responsibility for the account.
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
            if (isset($data['party'])) {
                $this->setParty($data['party']);
            }
            if (isset($data['onHold'])) {
                $this->setOnHold($data['onHold']);
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
        if (isset($this->party)) {
            $json['party'] = $this->party;
        }
        if (isset($this->onHold)) {
            $json['onHold'] = $this->onHold;
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
            $sxe = new \SimpleXMLElement('<AccountGuarantor xmlns="http://hl7.org/fhir"></AccountGuarantor>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->party)) {
            $this->party->xmlSerialize(true, $sxe->addChild('party'));
        }
        if (isset($this->onHold)) {
            $this->onHold->xmlSerialize(true, $sxe->addChild('onHold'));
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
