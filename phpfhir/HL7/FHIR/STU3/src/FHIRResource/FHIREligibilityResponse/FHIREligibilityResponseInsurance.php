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
class FHIREligibilityResponseInsurance extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A suite of updated or additional Coverages from the Insurer.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $coverage = null;

    /**
     * The contract resource which may provide more detailed information.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $contract = null;

    /**
     * Benefits and optionally current balances by Category.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIREligibilityResponse\FHIREligibilityResponseBenefitBalance[]
     */
    public $benefitBalance = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'EligibilityResponse.Insurance';

    /**
     * A suite of updated or additional Coverages from the Insurer.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * A suite of updated or additional Coverages from the Insurer.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $coverage
     * @return $this
     */
    public function setCoverage($coverage)
    {
        $this->coverage = $coverage;
        return $this;
    }

    /**
     * The contract resource which may provide more detailed information.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * The contract resource which may provide more detailed information.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $contract
     * @return $this
     */
    public function setContract($contract)
    {
        $this->contract = $contract;
        return $this;
    }

    /**
     * Benefits and optionally current balances by Category.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIREligibilityResponse\FHIREligibilityResponseBenefitBalance[]
     */
    public function getBenefitBalance()
    {
        return $this->benefitBalance;
    }

    /**
     * Benefits and optionally current balances by Category.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIREligibilityResponse\FHIREligibilityResponseBenefitBalance $benefitBalance
     * @return $this
     */
    public function addBenefitBalance($benefitBalance)
    {
        $this->benefitBalance[] = $benefitBalance;
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
            if (isset($data['coverage'])) {
                $this->setCoverage($data['coverage']);
            }
            if (isset($data['contract'])) {
                $this->setContract($data['contract']);
            }
            if (isset($data['benefitBalance'])) {
                if (is_array($data['benefitBalance'])) {
                    foreach ($data['benefitBalance'] as $d) {
                        $this->addBenefitBalance($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"benefitBalance" must be array of objects or null, '.gettype($data['benefitBalance']).' seen.');
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
        if (isset($this->coverage)) {
            $json['coverage'] = $this->coverage;
        }
        if (isset($this->contract)) {
            $json['contract'] = $this->contract;
        }
        if (0 < count($this->benefitBalance)) {
            $json['benefitBalance'] = [];
            foreach ($this->benefitBalance as $benefitBalance) {
                $json['benefitBalance'][] = $benefitBalance;
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
            $sxe = new \SimpleXMLElement('<EligibilityResponseInsurance xmlns="http://hl7.org/fhir"></EligibilityResponseInsurance>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->coverage)) {
            $this->coverage->xmlSerialize(true, $sxe->addChild('coverage'));
        }
        if (isset($this->contract)) {
            $this->contract->xmlSerialize(true, $sxe->addChild('contract'));
        }
        if (0 < count($this->benefitBalance)) {
            foreach ($this->benefitBalance as $benefitBalance) {
                $benefitBalance->xmlSerialize(true, $sxe->addChild('benefitBalance'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
