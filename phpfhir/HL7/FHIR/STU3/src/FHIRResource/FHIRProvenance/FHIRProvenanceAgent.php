<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRProvenance;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: February 10th, 2018
 *
 *
 *
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Provenance of a resource is a record that describes entities and processes involved in producing and delivering or otherwise influencing that resource. Provenance provides a critical foundation for assessing authenticity, enabling trust, and allowing reproducibility. Provenance assertions are a form of contextual metadata and can themselves become important records with their own provenance. Provenance statement indicates clinical significance in terms of confidence in authenticity, reliability, and trustworthiness, integrity, and stage in lifecycle (e.g. Document Completion - has the artifact been legally authenticated), all of which may impact security, privacy, and trust policies.
 */
class FHIRProvenanceAgent extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The function of the agent with respect to the activity. The security role enabling the agent with respect to the activity.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $role = [];

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $whoUri = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $whoReference = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $onBehalfOfUri = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $onBehalfOfReference = null;

    /**
     * The type of relationship between agents.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $relatedAgentType = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Provenance.Agent';

    /**
     * The function of the agent with respect to the activity. The security role enabling the agent with respect to the activity.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * The function of the agent with respect to the activity. The security role enabling the agent with respect to the activity.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $role
     * @return $this
     */
    public function addRole($role)
    {
        $this->role[] = $role;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getWhoUri()
    {
        return $this->whoUri;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $whoUri
     * @return $this
     */
    public function setWhoUri($whoUri)
    {
        $this->whoUri = $whoUri;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getWhoReference()
    {
        return $this->whoReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $whoReference
     * @return $this
     */
    public function setWhoReference($whoReference)
    {
        $this->whoReference = $whoReference;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getOnBehalfOfUri()
    {
        return $this->onBehalfOfUri;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $onBehalfOfUri
     * @return $this
     */
    public function setOnBehalfOfUri($onBehalfOfUri)
    {
        $this->onBehalfOfUri = $onBehalfOfUri;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getOnBehalfOfReference()
    {
        return $this->onBehalfOfReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $onBehalfOfReference
     * @return $this
     */
    public function setOnBehalfOfReference($onBehalfOfReference)
    {
        $this->onBehalfOfReference = $onBehalfOfReference;
        return $this;
    }

    /**
     * The type of relationship between agents.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getRelatedAgentType()
    {
        return $this->relatedAgentType;
    }

    /**
     * The type of relationship between agents.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $relatedAgentType
     * @return $this
     */
    public function setRelatedAgentType($relatedAgentType)
    {
        $this->relatedAgentType = $relatedAgentType;
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
            if (isset($data['role'])) {
                if (is_array($data['role'])) {
                    foreach ($data['role'] as $d) {
                        $this->addRole($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"role" must be array of objects or null, '.gettype($data['role']).' seen.');
                }
            }
            if (isset($data['whoUri'])) {
                $this->setWhoUri($data['whoUri']);
            }
            if (isset($data['whoReference'])) {
                $this->setWhoReference($data['whoReference']);
            }
            if (isset($data['onBehalfOfUri'])) {
                $this->setOnBehalfOfUri($data['onBehalfOfUri']);
            }
            if (isset($data['onBehalfOfReference'])) {
                $this->setOnBehalfOfReference($data['onBehalfOfReference']);
            }
            if (isset($data['relatedAgentType'])) {
                $this->setRelatedAgentType($data['relatedAgentType']);
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
        if (0 < count($this->role)) {
            $json['role'] = [];
            foreach ($this->role as $role) {
                $json['role'][] = $role;
            }
        }
        if (isset($this->whoUri)) {
            $json['whoUri'] = $this->whoUri;
        }
        if (isset($this->whoReference)) {
            $json['whoReference'] = $this->whoReference;
        }
        if (isset($this->onBehalfOfUri)) {
            $json['onBehalfOfUri'] = $this->onBehalfOfUri;
        }
        if (isset($this->onBehalfOfReference)) {
            $json['onBehalfOfReference'] = $this->onBehalfOfReference;
        }
        if (isset($this->relatedAgentType)) {
            $json['relatedAgentType'] = $this->relatedAgentType;
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
            $sxe = new \SimpleXMLElement('<ProvenanceAgent xmlns="http://hl7.org/fhir"></ProvenanceAgent>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->role)) {
            foreach ($this->role as $role) {
                $role->xmlSerialize(true, $sxe->addChild('role'));
            }
        }
        if (isset($this->whoUri)) {
            $this->whoUri->xmlSerialize(true, $sxe->addChild('whoUri'));
        }
        if (isset($this->whoReference)) {
            $this->whoReference->xmlSerialize(true, $sxe->addChild('whoReference'));
        }
        if (isset($this->onBehalfOfUri)) {
            $this->onBehalfOfUri->xmlSerialize(true, $sxe->addChild('onBehalfOfUri'));
        }
        if (isset($this->onBehalfOfReference)) {
            $this->onBehalfOfReference->xmlSerialize(true, $sxe->addChild('onBehalfOfReference'));
        }
        if (isset($this->relatedAgentType)) {
            $this->relatedAgentType->xmlSerialize(true, $sxe->addChild('relatedAgentType'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
