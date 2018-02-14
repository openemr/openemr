<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource provides: the claim details; adjudication details from the processing of a Claim; and optionally account balance information, for informing the subscriber of the benefits provided.
 */
class FHIRExplanationOfBenefitPayment extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Whether this represents partial or complete payment of the claim.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Adjustment to the payment of this transaction which is not related to adjudication of this transaction.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $adjustment = null;

    /**
     * Reason for the payment adjustment.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $adjustmentReason = null;

    /**
     * Estimated payment date.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public $date = null;

    /**
     * Payable less any payment adjustment.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $amount = null;

    /**
     * Payment identifer.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ExplanationOfBenefit.Payment';

    /**
     * Whether this represents partial or complete payment of the claim.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Whether this represents partial or complete payment of the claim.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Adjustment to the payment of this transaction which is not related to adjudication of this transaction.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getAdjustment()
    {
        return $this->adjustment;
    }

    /**
     * Adjustment to the payment of this transaction which is not related to adjudication of this transaction.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $adjustment
     * @return $this
     */
    public function setAdjustment($adjustment)
    {
        $this->adjustment = $adjustment;
        return $this;
    }

    /**
     * Reason for the payment adjustment.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getAdjustmentReason()
    {
        return $this->adjustmentReason;
    }

    /**
     * Reason for the payment adjustment.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $adjustmentReason
     * @return $this
     */
    public function setAdjustmentReason($adjustmentReason)
    {
        $this->adjustmentReason = $adjustmentReason;
        return $this;
    }

    /**
     * Estimated payment date.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Estimated payment date.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDate $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Payable less any payment adjustment.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Payable less any payment adjustment.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Payment identifer.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Payment identifer.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
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
            if (isset($data['adjustment'])) {
                $this->setAdjustment($data['adjustment']);
            }
            if (isset($data['adjustmentReason'])) {
                $this->setAdjustmentReason($data['adjustmentReason']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['amount'])) {
                $this->setAmount($data['amount']);
            }
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
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
        if (isset($this->adjustment)) {
            $json['adjustment'] = $this->adjustment;
        }
        if (isset($this->adjustmentReason)) {
            $json['adjustmentReason'] = $this->adjustmentReason;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (isset($this->amount)) {
            $json['amount'] = $this->amount;
        }
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
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
            $sxe = new \SimpleXMLElement('<ExplanationOfBenefitPayment xmlns="http://hl7.org/fhir"></ExplanationOfBenefitPayment>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->adjustment)) {
            $this->adjustment->xmlSerialize(true, $sxe->addChild('adjustment'));
        }
        if (isset($this->adjustmentReason)) {
            $this->adjustmentReason->xmlSerialize(true, $sxe->addChild('adjustmentReason'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->amount)) {
            $this->amount->xmlSerialize(true, $sxe->addChild('amount'));
        }
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
