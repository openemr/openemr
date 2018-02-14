<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRTask;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A task to be performed.
 */
class FHIRTaskRestriction extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Indicates the number of times the requested action should occur.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $repetitions = null;

    /**
     * Over what time-period is fulfillment sought.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * For requests that are targeted to more than on potential recipient/target, for whom is fulfillment sought?
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $recipient = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Task.Restriction';

    /**
     * Indicates the number of times the requested action should occur.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getRepetitions()
    {
        return $this->repetitions;
    }

    /**
     * Indicates the number of times the requested action should occur.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $repetitions
     * @return $this
     */
    public function setRepetitions($repetitions)
    {
        $this->repetitions = $repetitions;
        return $this;
    }

    /**
     * Over what time-period is fulfillment sought.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Over what time-period is fulfillment sought.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * For requests that are targeted to more than on potential recipient/target, for whom is fulfillment sought?
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * For requests that are targeted to more than on potential recipient/target, for whom is fulfillment sought?
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $recipient
     * @return $this
     */
    public function addRecipient($recipient)
    {
        $this->recipient[] = $recipient;
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
            if (isset($data['repetitions'])) {
                $this->setRepetitions($data['repetitions']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['recipient'])) {
                if (is_array($data['recipient'])) {
                    foreach ($data['recipient'] as $d) {
                        $this->addRecipient($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"recipient" must be array of objects or null, '.gettype($data['recipient']).' seen.');
                }
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
        if (isset($this->repetitions)) {
            $json['repetitions'] = $this->repetitions;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (0 < count($this->recipient)) {
            $json['recipient'] = [];
            foreach ($this->recipient as $recipient) {
                $json['recipient'][] = $recipient;
            }
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
            $sxe = new \SimpleXMLElement('<TaskRestriction xmlns="http://hl7.org/fhir"></TaskRestriction>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->repetitions)) {
            $this->repetitions->xmlSerialize(true, $sxe->addChild('repetitions'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (0 < count($this->recipient)) {
            foreach ($this->recipient as $recipient) {
                $recipient->xmlSerialize(true, $sxe->addChild('recipient'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
