<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRAuditEvent;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A record of an event made for purposes of maintaining a security log. Typical uses include detection of intrusion attempts and monitoring for inappropriate usage.
 */
class FHIRAuditEventAgent extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The security role that the user was acting under, that come from local codes defined by the access control security system (e.g. RBAC, ABAC) used in the local context.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $role = [];

    /**
     * Direct reference to a resource that identifies the agent.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $reference = null;

    /**
     * Unique identifier for the user actively participating in the event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $userId = null;

    /**
     * Alternative agent Identifier. For a human, this should be a user identifier text string from authentication system. This identifier would be one known to a common authentication system (e.g. single sign-on), if available.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $altId = null;

    /**
     * Human-meaningful name for the agent.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * Indicator that the user is or is not the requestor, or initiator, for the event being audited.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $requestor = null;

    /**
     * Where the event occurred.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $location = null;

    /**
     * The policy or plan that authorized the activity being recorded. Typically, a single activity may have multiple applicable policies, such as patient consent, guarantor funding, etc. The policy would also indicate the security token used.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public $policy = [];

    /**
     * Type of media involved. Used when the event is about exporting/importing onto media.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $media = null;

    /**
     * Logical network location for application activity, if the activity has a network location.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRAuditEvent\FHIRAuditEventNetwork
     */
    public $network = null;

    /**
     * The reason (purpose of use), specific to this agent, that was used during the event being recorded.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $purposeOfUse = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'AuditEvent.Agent';

    /**
     * The security role that the user was acting under, that come from local codes defined by the access control security system (e.g. RBAC, ABAC) used in the local context.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * The security role that the user was acting under, that come from local codes defined by the access control security system (e.g. RBAC, ABAC) used in the local context.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $role
     * @return $this
     */
    public function addRole($role)
    {
        $this->role[] = $role;
        return $this;
    }

    /**
     * Direct reference to a resource that identifies the agent.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Direct reference to a resource that identifies the agent.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $reference
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Unique identifier for the user actively participating in the event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Unique identifier for the user actively participating in the event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Alternative agent Identifier. For a human, this should be a user identifier text string from authentication system. This identifier would be one known to a common authentication system (e.g. single sign-on), if available.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getAltId()
    {
        return $this->altId;
    }

    /**
     * Alternative agent Identifier. For a human, this should be a user identifier text string from authentication system. This identifier would be one known to a common authentication system (e.g. single sign-on), if available.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $altId
     * @return $this
     */
    public function setAltId($altId)
    {
        $this->altId = $altId;
        return $this;
    }

    /**
     * Human-meaningful name for the agent.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Human-meaningful name for the agent.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Indicator that the user is or is not the requestor, or initiator, for the event being audited.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getRequestor()
    {
        return $this->requestor;
    }

    /**
     * Indicator that the user is or is not the requestor, or initiator, for the event being audited.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $requestor
     * @return $this
     */
    public function setRequestor($requestor)
    {
        $this->requestor = $requestor;
        return $this;
    }

    /**
     * Where the event occurred.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Where the event occurred.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * The policy or plan that authorized the activity being recorded. Typically, a single activity may have multiple applicable policies, such as patient consent, guarantor funding, etc. The policy would also indicate the security token used.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * The policy or plan that authorized the activity being recorded. Typically, a single activity may have multiple applicable policies, such as patient consent, guarantor funding, etc. The policy would also indicate the security token used.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $policy
     * @return $this
     */
    public function addPolicy($policy)
    {
        $this->policy[] = $policy;
        return $this;
    }

    /**
     * Type of media involved. Used when the event is about exporting/importing onto media.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Type of media involved. Used when the event is about exporting/importing onto media.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $media
     * @return $this
     */
    public function setMedia($media)
    {
        $this->media = $media;
        return $this;
    }

    /**
     * Logical network location for application activity, if the activity has a network location.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRAuditEvent\FHIRAuditEventNetwork
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Logical network location for application activity, if the activity has a network location.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRAuditEvent\FHIRAuditEventNetwork $network
     * @return $this
     */
    public function setNetwork($network)
    {
        $this->network = $network;
        return $this;
    }

    /**
     * The reason (purpose of use), specific to this agent, that was used during the event being recorded.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getPurposeOfUse()
    {
        return $this->purposeOfUse;
    }

    /**
     * The reason (purpose of use), specific to this agent, that was used during the event being recorded.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $purposeOfUse
     * @return $this
     */
    public function addPurposeOfUse($purposeOfUse)
    {
        $this->purposeOfUse[] = $purposeOfUse;
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
            if (isset($data['reference'])) {
                $this->setReference($data['reference']);
            }
            if (isset($data['userId'])) {
                $this->setUserId($data['userId']);
            }
            if (isset($data['altId'])) {
                $this->setAltId($data['altId']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['requestor'])) {
                $this->setRequestor($data['requestor']);
            }
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['policy'])) {
                if (is_array($data['policy'])) {
                    foreach ($data['policy'] as $d) {
                        $this->addPolicy($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"policy" must be array of objects or null, '.gettype($data['policy']).' seen.');
                }
            }
            if (isset($data['media'])) {
                $this->setMedia($data['media']);
            }
            if (isset($data['network'])) {
                $this->setNetwork($data['network']);
            }
            if (isset($data['purposeOfUse'])) {
                if (is_array($data['purposeOfUse'])) {
                    foreach ($data['purposeOfUse'] as $d) {
                        $this->addPurposeOfUse($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"purposeOfUse" must be array of objects or null, '.gettype($data['purposeOfUse']).' seen.');
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
        if (0 < count($this->role)) {
            $json['role'] = [];
            foreach ($this->role as $role) {
                $json['role'][] = $role;
            }
        }
        if (isset($this->reference)) {
            $json['reference'] = $this->reference;
        }
        if (isset($this->userId)) {
            $json['userId'] = $this->userId;
        }
        if (isset($this->altId)) {
            $json['altId'] = $this->altId;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->requestor)) {
            $json['requestor'] = $this->requestor;
        }
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (0 < count($this->policy)) {
            $json['policy'] = [];
            foreach ($this->policy as $policy) {
                $json['policy'][] = $policy;
            }
        }
        if (isset($this->media)) {
            $json['media'] = $this->media;
        }
        if (isset($this->network)) {
            $json['network'] = $this->network;
        }
        if (0 < count($this->purposeOfUse)) {
            $json['purposeOfUse'] = [];
            foreach ($this->purposeOfUse as $purposeOfUse) {
                $json['purposeOfUse'][] = $purposeOfUse;
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
            $sxe = new \SimpleXMLElement('<AuditEventAgent xmlns="http://hl7.org/fhir"></AuditEventAgent>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->role)) {
            foreach ($this->role as $role) {
                $role->xmlSerialize(true, $sxe->addChild('role'));
            }
        }
        if (isset($this->reference)) {
            $this->reference->xmlSerialize(true, $sxe->addChild('reference'));
        }
        if (isset($this->userId)) {
            $this->userId->xmlSerialize(true, $sxe->addChild('userId'));
        }
        if (isset($this->altId)) {
            $this->altId->xmlSerialize(true, $sxe->addChild('altId'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->requestor)) {
            $this->requestor->xmlSerialize(true, $sxe->addChild('requestor'));
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (0 < count($this->policy)) {
            foreach ($this->policy as $policy) {
                $policy->xmlSerialize(true, $sxe->addChild('policy'));
            }
        }
        if (isset($this->media)) {
            $this->media->xmlSerialize(true, $sxe->addChild('media'));
        }
        if (isset($this->network)) {
            $this->network->xmlSerialize(true, $sxe->addChild('network'));
        }
        if (0 < count($this->purposeOfUse)) {
            foreach ($this->purposeOfUse as $purposeOfUse) {
                $purposeOfUse->xmlSerialize(true, $sxe->addChild('purposeOfUse'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
