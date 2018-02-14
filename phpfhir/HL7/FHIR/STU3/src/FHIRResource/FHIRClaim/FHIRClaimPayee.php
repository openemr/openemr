<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRClaim;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A provider issued list of services and products provided, or to be provided, to a patient which is provided to an insurer for payment recovery.
 */
class FHIRClaimPayee extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Type of Party to be reimbursed: Subscriber, provider, other.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * organization | patient | practitioner | relatedperson.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $resourceType = null;

    /**
     * Party to be reimbursed: Subscriber, provider, other.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $party = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Claim.Payee';

    /**
     * Type of Party to be reimbursed: Subscriber, provider, other.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type of Party to be reimbursed: Subscriber, provider, other.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * organization | patient | practitioner | relatedperson.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * organization | patient | practitioner | relatedperson.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $resourceType
     * @return $this
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
        return $this;
    }

    /**
     * Party to be reimbursed: Subscriber, provider, other.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * Party to be reimbursed: Subscriber, provider, other.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $party
     * @return $this
     */
    public function setParty($party)
    {
        $this->party = $party;
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
            if (isset($data['resourceType'])) {
                $this->setResourceType($data['resourceType']);
            }
            if (isset($data['party'])) {
                $this->setParty($data['party']);
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
        if (isset($this->resourceType)) {
            $json['resourceType'] = $this->resourceType;
        }
        if (isset($this->party)) {
            $json['party'] = $this->party;
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
            $sxe = new \SimpleXMLElement('<ClaimPayee xmlns="http://hl7.org/fhir"></ClaimPayee>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->resourceType)) {
            $this->resourceType->xmlSerialize(true, $sxe->addChild('resourceType'));
        }
        if (isset($this->party)) {
            $this->party->xmlSerialize(true, $sxe->addChild('party'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
