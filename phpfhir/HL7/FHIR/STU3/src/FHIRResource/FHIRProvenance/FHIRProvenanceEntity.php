<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRProvenance;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Provenance of a resource is a record that describes entities and processes involved in producing and delivering or otherwise influencing that resource. Provenance provides a critical foundation for assessing authenticity, enabling trust, and allowing reproducibility. Provenance assertions are a form of contextual metadata and can themselves become important records with their own provenance. Provenance statement indicates clinical significance in terms of confidence in authenticity, reliability, and trustworthiness, integrity, and stage in lifecycle (e.g. Document Completion - has the artifact been legally authenticated), all of which may impact security, privacy, and trust policies.
 */
class FHIRProvenanceEntity extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * How the entity was used during the activity.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRProvenanceEntityRole
     */
    public $role = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $whatUri = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $whatReference = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $whatIdentifier = null;

    /**
     * The entity is attributed to an agent to express the agent's responsibility for that entity, possibly along with other agents. This description can be understood as shorthand for saying that the agent was responsible for the activity which generated the entity.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRProvenance\FHIRProvenanceAgent[]
     */
    public $agent = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Provenance.Entity';

    /**
     * How the entity was used during the activity.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRProvenanceEntityRole
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * How the entity was used during the activity.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRProvenanceEntityRole $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getWhatUri()
    {
        return $this->whatUri;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $whatUri
     * @return $this
     */
    public function setWhatUri($whatUri)
    {
        $this->whatUri = $whatUri;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getWhatReference()
    {
        return $this->whatReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $whatReference
     * @return $this
     */
    public function setWhatReference($whatReference)
    {
        $this->whatReference = $whatReference;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getWhatIdentifier()
    {
        return $this->whatIdentifier;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $whatIdentifier
     * @return $this
     */
    public function setWhatIdentifier($whatIdentifier)
    {
        $this->whatIdentifier = $whatIdentifier;
        return $this;
    }

    /**
     * The entity is attributed to an agent to express the agent's responsibility for that entity, possibly along with other agents. This description can be understood as shorthand for saying that the agent was responsible for the activity which generated the entity.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRProvenance\FHIRProvenanceAgent[]
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * The entity is attributed to an agent to express the agent's responsibility for that entity, possibly along with other agents. This description can be understood as shorthand for saying that the agent was responsible for the activity which generated the entity.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRProvenance\FHIRProvenanceAgent $agent
     * @return $this
     */
    public function addAgent($agent)
    {
        $this->agent[] = $agent;
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
                $this->setRole($data['role']);
            }
            if (isset($data['whatUri'])) {
                $this->setWhatUri($data['whatUri']);
            }
            if (isset($data['whatReference'])) {
                $this->setWhatReference($data['whatReference']);
            }
            if (isset($data['whatIdentifier'])) {
                $this->setWhatIdentifier($data['whatIdentifier']);
            }
            if (isset($data['agent'])) {
                if (is_array($data['agent'])) {
                    foreach ($data['agent'] as $d) {
                        $this->addAgent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"agent" must be array of objects or null, '.gettype($data['agent']).' seen.');
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
        if (isset($this->role)) {
            $json['role'] = $this->role;
        }
        if (isset($this->whatUri)) {
            $json['whatUri'] = $this->whatUri;
        }
        if (isset($this->whatReference)) {
            $json['whatReference'] = $this->whatReference;
        }
        if (isset($this->whatIdentifier)) {
            $json['whatIdentifier'] = $this->whatIdentifier;
        }
        if (0 < count($this->agent)) {
            $json['agent'] = [];
            foreach ($this->agent as $agent) {
                $json['agent'][] = $agent;
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
            $sxe = new \SimpleXMLElement('<ProvenanceEntity xmlns="http://hl7.org/fhir"></ProvenanceEntity>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->role)) {
            $this->role->xmlSerialize(true, $sxe->addChild('role'));
        }
        if (isset($this->whatUri)) {
            $this->whatUri->xmlSerialize(true, $sxe->addChild('whatUri'));
        }
        if (isset($this->whatReference)) {
            $this->whatReference->xmlSerialize(true, $sxe->addChild('whatReference'));
        }
        if (isset($this->whatIdentifier)) {
            $this->whatIdentifier->xmlSerialize(true, $sxe->addChild('whatIdentifier'));
        }
        if (0 < count($this->agent)) {
            foreach ($this->agent as $agent) {
                $agent->xmlSerialize(true, $sxe->addChild('agent'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
