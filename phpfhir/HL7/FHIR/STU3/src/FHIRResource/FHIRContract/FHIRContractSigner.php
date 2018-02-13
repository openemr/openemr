<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRContract;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A formal agreement between parties regarding the conduct of business, exchange of information or other matters.
 */
class FHIRContractSigner extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Role of this Contract signer, e.g. notary, grantee.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $type = null;

    /**
     * Party which is a signator to this Contract.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $party = null;

    /**
     * Legally binding Contract DSIG signature contents in Base64.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSignature[]
     */
    public $signature = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Contract.Signer';

    /**
     * Role of this Contract signer, e.g. notary, grantee.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Role of this Contract signer, e.g. notary, grantee.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Party which is a signator to this Contract.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * Party which is a signator to this Contract.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $party
     * @return $this
     */
    public function setParty($party)
    {
        $this->party = $party;
        return $this;
    }

    /**
     * Legally binding Contract DSIG signature contents in Base64.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSignature[]
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Legally binding Contract DSIG signature contents in Base64.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSignature $signature
     * @return $this
     */
    public function addSignature($signature)
    {
        $this->signature[] = $signature;
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
            if (isset($data['party'])) {
                $this->setParty($data['party']);
            }
            if (isset($data['signature'])) {
                if (is_array($data['signature'])) {
                    foreach ($data['signature'] as $d) {
                        $this->addSignature($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"signature" must be array of objects or null, '.gettype($data['signature']).' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->party)) {
            $json['party'] = $this->party;
        }
        if (0 < count($this->signature)) {
            $json['signature'] = [];
            foreach ($this->signature as $signature) {
                $json['signature'][] = $signature;
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
            $sxe = new \SimpleXMLElement('<ContractSigner xmlns="http://hl7.org/fhir"></ContractSigner>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->party)) {
            $this->party->xmlSerialize(true, $sxe->addChild('party'));
        }
        if (0 < count($this->signature)) {
            foreach ($this->signature as $signature) {
                $signature->xmlSerialize(true, $sxe->addChild('signature'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
