<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * A record of a healthcare consumer’s policy choices, which permits or denies identified recipient(s) or recipient role(s) to perform one or more actions within a given policy context, for specific purposes and periods of time.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRConsent extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Unique identifier for this copy of the Consent Statement.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * Indicates the current state of this consent.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRConsentState
     */
    public $status = null;

    /**
     * A classification of the type of consents found in the statement. This element supports indexing and retrieval of consent statements.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $category = [];

    /**
     * The patient/healthcare consumer to whom this consent applies.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * Relevant time or time-period when this Consent is applicable.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * When this  Consent was issued / created / indexed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $dateTime = null;

    /**
     * Either the Grantor, which is the entity responsible for granting the rights listed in a Consent Directive or the Grantee, which is the entity responsible for complying with the Consent Directive, including any obligations or limitations on authorizations and enforcement of prohibitions.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $consentingParty = [];

    /**
     * Who or what is controlled by this consent. Use group to identify a set of actors by some property they share (e.g. 'admitting officers').
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentActor[]
     */
    public $actor = [];

    /**
     * Actions controlled by this consent.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $action = [];

    /**
     * The organization that manages the consent, and the framework within which it is executed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $organization = [];

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public $sourceAttachment = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $sourceIdentifier = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $sourceReference = null;

    /**
     * The references to the policies that are included in this consent scope. Policies may be organizational, but are often defined jurisdictionally, or in law.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentPolicy[]
     */
    public $policy = [];

    /**
     * A referece to the specific computable policy.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $policyRule = null;

    /**
     * A set of security labels that define which resources are controlled by this consent. If more than one label is specified, all resources must have all the specified labels.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public $securityLabel = [];

    /**
     * The context of the activities a user is taking - why the user is accessing the data - that are controlled by this consent.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public $purpose = [];

    /**
     * Clinical or Operational Relevant period of time that bounds the data controlled by this consent.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $dataPeriod = null;

    /**
     * The resources controlled by this consent, if specific resources are referenced.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentData[]
     */
    public $data = [];

    /**
     * An exception to the base policy of this consent. An exception can be an addition or removal of access permissions.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentExcept[]
     */
    public $except = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Consent';

    /**
     * Unique identifier for this copy of the Consent Statement.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique identifier for this copy of the Consent Statement.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Indicates the current state of this consent.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRConsentState
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Indicates the current state of this consent.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRConsentState $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A classification of the type of consents found in the statement. This element supports indexing and retrieval of consent statements.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * A classification of the type of consents found in the statement. This element supports indexing and retrieval of consent statements.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->category[] = $category;
        return $this;
    }

    /**
     * The patient/healthcare consumer to whom this consent applies.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * The patient/healthcare consumer to whom this consent applies.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * Relevant time or time-period when this Consent is applicable.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Relevant time or time-period when this Consent is applicable.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * When this  Consent was issued / created / indexed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * When this  Consent was issued / created / indexed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $dateTime
     * @return $this
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    /**
     * Either the Grantor, which is the entity responsible for granting the rights listed in a Consent Directive or the Grantee, which is the entity responsible for complying with the Consent Directive, including any obligations or limitations on authorizations and enforcement of prohibitions.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getConsentingParty()
    {
        return $this->consentingParty;
    }

    /**
     * Either the Grantor, which is the entity responsible for granting the rights listed in a Consent Directive or the Grantee, which is the entity responsible for complying with the Consent Directive, including any obligations or limitations on authorizations and enforcement of prohibitions.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $consentingParty
     * @return $this
     */
    public function addConsentingParty($consentingParty)
    {
        $this->consentingParty[] = $consentingParty;
        return $this;
    }

    /**
     * Who or what is controlled by this consent. Use group to identify a set of actors by some property they share (e.g. 'admitting officers').
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentActor[]
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * Who or what is controlled by this consent. Use group to identify a set of actors by some property they share (e.g. 'admitting officers').
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentActor $actor
     * @return $this
     */
    public function addActor($actor)
    {
        $this->actor[] = $actor;
        return $this;
    }

    /**
     * Actions controlled by this consent.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Actions controlled by this consent.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $action
     * @return $this
     */
    public function addAction($action)
    {
        $this->action[] = $action;
        return $this;
    }

    /**
     * The organization that manages the consent, and the framework within which it is executed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * The organization that manages the consent, and the framework within which it is executed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $organization
     * @return $this
     */
    public function addOrganization($organization)
    {
        $this->organization[] = $organization;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public function getSourceAttachment()
    {
        return $this->sourceAttachment;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAttachment $sourceAttachment
     * @return $this
     */
    public function setSourceAttachment($sourceAttachment)
    {
        $this->sourceAttachment = $sourceAttachment;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getSourceIdentifier()
    {
        return $this->sourceIdentifier;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $sourceIdentifier
     * @return $this
     */
    public function setSourceIdentifier($sourceIdentifier)
    {
        $this->sourceIdentifier = $sourceIdentifier;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getSourceReference()
    {
        return $this->sourceReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $sourceReference
     * @return $this
     */
    public function setSourceReference($sourceReference)
    {
        $this->sourceReference = $sourceReference;
        return $this;
    }

    /**
     * The references to the policies that are included in this consent scope. Policies may be organizational, but are often defined jurisdictionally, or in law.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentPolicy[]
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * The references to the policies that are included in this consent scope. Policies may be organizational, but are often defined jurisdictionally, or in law.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentPolicy $policy
     * @return $this
     */
    public function addPolicy($policy)
    {
        $this->policy[] = $policy;
        return $this;
    }

    /**
     * A referece to the specific computable policy.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getPolicyRule()
    {
        return $this->policyRule;
    }

    /**
     * A referece to the specific computable policy.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $policyRule
     * @return $this
     */
    public function setPolicyRule($policyRule)
    {
        $this->policyRule = $policyRule;
        return $this;
    }

    /**
     * A set of security labels that define which resources are controlled by this consent. If more than one label is specified, all resources must have all the specified labels.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public function getSecurityLabel()
    {
        return $this->securityLabel;
    }

    /**
     * A set of security labels that define which resources are controlled by this consent. If more than one label is specified, all resources must have all the specified labels.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $securityLabel
     * @return $this
     */
    public function addSecurityLabel($securityLabel)
    {
        $this->securityLabel[] = $securityLabel;
        return $this;
    }

    /**
     * The context of the activities a user is taking - why the user is accessing the data - that are controlled by this consent.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * The context of the activities a user is taking - why the user is accessing the data - that are controlled by this consent.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $purpose
     * @return $this
     */
    public function addPurpose($purpose)
    {
        $this->purpose[] = $purpose;
        return $this;
    }

    /**
     * Clinical or Operational Relevant period of time that bounds the data controlled by this consent.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getDataPeriod()
    {
        return $this->dataPeriod;
    }

    /**
     * Clinical or Operational Relevant period of time that bounds the data controlled by this consent.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $dataPeriod
     * @return $this
     */
    public function setDataPeriod($dataPeriod)
    {
        $this->dataPeriod = $dataPeriod;
        return $this;
    }

    /**
     * The resources controlled by this consent, if specific resources are referenced.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentData[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * The resources controlled by this consent, if specific resources are referenced.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentData $data
     * @return $this
     */
    public function addData($data)
    {
        $this->data[] = $data;
        return $this;
    }

    /**
     * An exception to the base policy of this consent. An exception can be an addition or removal of access permissions.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentExcept[]
     */
    public function getExcept()
    {
        return $this->except;
    }

    /**
     * An exception to the base policy of this consent. An exception can be an addition or removal of access permissions.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRConsent\FHIRConsentExcept $except
     * @return $this
     */
    public function addExcept($except)
    {
        $this->except[] = $except;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['category'])) {
                if (is_array($data['category'])) {
                    foreach ($data['category'] as $d) {
                        $this->addCategory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"category" must be array of objects or null, '.gettype($data['category']).' seen.');
                }
            }
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['dateTime'])) {
                $this->setDateTime($data['dateTime']);
            }
            if (isset($data['consentingParty'])) {
                if (is_array($data['consentingParty'])) {
                    foreach ($data['consentingParty'] as $d) {
                        $this->addConsentingParty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"consentingParty" must be array of objects or null, '.gettype($data['consentingParty']).' seen.');
                }
            }
            if (isset($data['actor'])) {
                if (is_array($data['actor'])) {
                    foreach ($data['actor'] as $d) {
                        $this->addActor($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"actor" must be array of objects or null, '.gettype($data['actor']).' seen.');
                }
            }
            if (isset($data['action'])) {
                if (is_array($data['action'])) {
                    foreach ($data['action'] as $d) {
                        $this->addAction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"action" must be array of objects or null, '.gettype($data['action']).' seen.');
                }
            }
            if (isset($data['organization'])) {
                if (is_array($data['organization'])) {
                    foreach ($data['organization'] as $d) {
                        $this->addOrganization($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"organization" must be array of objects or null, '.gettype($data['organization']).' seen.');
                }
            }
            if (isset($data['sourceAttachment'])) {
                $this->setSourceAttachment($data['sourceAttachment']);
            }
            if (isset($data['sourceIdentifier'])) {
                $this->setSourceIdentifier($data['sourceIdentifier']);
            }
            if (isset($data['sourceReference'])) {
                $this->setSourceReference($data['sourceReference']);
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
            if (isset($data['policyRule'])) {
                $this->setPolicyRule($data['policyRule']);
            }
            if (isset($data['securityLabel'])) {
                if (is_array($data['securityLabel'])) {
                    foreach ($data['securityLabel'] as $d) {
                        $this->addSecurityLabel($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"securityLabel" must be array of objects or null, '.gettype($data['securityLabel']).' seen.');
                }
            }
            if (isset($data['purpose'])) {
                if (is_array($data['purpose'])) {
                    foreach ($data['purpose'] as $d) {
                        $this->addPurpose($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"purpose" must be array of objects or null, '.gettype($data['purpose']).' seen.');
                }
            }
            if (isset($data['dataPeriod'])) {
                $this->setDataPeriod($data['dataPeriod']);
            }
            if (isset($data['data'])) {
                if (is_array($data['data'])) {
                    foreach ($data['data'] as $d) {
                        $this->addData($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"data" must be array of objects or null, '.gettype($data['data']).' seen.');
                }
            }
            if (isset($data['except'])) {
                if (is_array($data['except'])) {
                    foreach ($data['except'] as $d) {
                        $this->addExcept($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"except" must be array of objects or null, '.gettype($data['except']).' seen.');
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
        $json['resourceType'] = $this->_fhirElementName;
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (0 < count($this->category)) {
            $json['category'] = [];
            foreach ($this->category as $category) {
                $json['category'][] = $category;
            }
        }
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->dateTime)) {
            $json['dateTime'] = $this->dateTime;
        }
        if (0 < count($this->consentingParty)) {
            $json['consentingParty'] = [];
            foreach ($this->consentingParty as $consentingParty) {
                $json['consentingParty'][] = $consentingParty;
            }
        }
        if (0 < count($this->actor)) {
            $json['actor'] = [];
            foreach ($this->actor as $actor) {
                $json['actor'][] = $actor;
            }
        }
        if (0 < count($this->action)) {
            $json['action'] = [];
            foreach ($this->action as $action) {
                $json['action'][] = $action;
            }
        }
        if (0 < count($this->organization)) {
            $json['organization'] = [];
            foreach ($this->organization as $organization) {
                $json['organization'][] = $organization;
            }
        }
        if (isset($this->sourceAttachment)) {
            $json['sourceAttachment'] = $this->sourceAttachment;
        }
        if (isset($this->sourceIdentifier)) {
            $json['sourceIdentifier'] = $this->sourceIdentifier;
        }
        if (isset($this->sourceReference)) {
            $json['sourceReference'] = $this->sourceReference;
        }
        if (0 < count($this->policy)) {
            $json['policy'] = [];
            foreach ($this->policy as $policy) {
                $json['policy'][] = $policy;
            }
        }
        if (isset($this->policyRule)) {
            $json['policyRule'] = $this->policyRule;
        }
        if (0 < count($this->securityLabel)) {
            $json['securityLabel'] = [];
            foreach ($this->securityLabel as $securityLabel) {
                $json['securityLabel'][] = $securityLabel;
            }
        }
        if (0 < count($this->purpose)) {
            $json['purpose'] = [];
            foreach ($this->purpose as $purpose) {
                $json['purpose'][] = $purpose;
            }
        }
        if (isset($this->dataPeriod)) {
            $json['dataPeriod'] = $this->dataPeriod;
        }
        if (0 < count($this->data)) {
            $json['data'] = [];
            foreach ($this->data as $data) {
                $json['data'][] = $data;
            }
        }
        if (0 < count($this->except)) {
            $json['except'] = [];
            foreach ($this->except as $except) {
                $json['except'][] = $except;
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
            $sxe = new \SimpleXMLElement('<Consent xmlns="http://hl7.org/fhir"></Consent>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (0 < count($this->category)) {
            foreach ($this->category as $category) {
                $category->xmlSerialize(true, $sxe->addChild('category'));
            }
        }
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->dateTime)) {
            $this->dateTime->xmlSerialize(true, $sxe->addChild('dateTime'));
        }
        if (0 < count($this->consentingParty)) {
            foreach ($this->consentingParty as $consentingParty) {
                $consentingParty->xmlSerialize(true, $sxe->addChild('consentingParty'));
            }
        }
        if (0 < count($this->actor)) {
            foreach ($this->actor as $actor) {
                $actor->xmlSerialize(true, $sxe->addChild('actor'));
            }
        }
        if (0 < count($this->action)) {
            foreach ($this->action as $action) {
                $action->xmlSerialize(true, $sxe->addChild('action'));
            }
        }
        if (0 < count($this->organization)) {
            foreach ($this->organization as $organization) {
                $organization->xmlSerialize(true, $sxe->addChild('organization'));
            }
        }
        if (isset($this->sourceAttachment)) {
            $this->sourceAttachment->xmlSerialize(true, $sxe->addChild('sourceAttachment'));
        }
        if (isset($this->sourceIdentifier)) {
            $this->sourceIdentifier->xmlSerialize(true, $sxe->addChild('sourceIdentifier'));
        }
        if (isset($this->sourceReference)) {
            $this->sourceReference->xmlSerialize(true, $sxe->addChild('sourceReference'));
        }
        if (0 < count($this->policy)) {
            foreach ($this->policy as $policy) {
                $policy->xmlSerialize(true, $sxe->addChild('policy'));
            }
        }
        if (isset($this->policyRule)) {
            $this->policyRule->xmlSerialize(true, $sxe->addChild('policyRule'));
        }
        if (0 < count($this->securityLabel)) {
            foreach ($this->securityLabel as $securityLabel) {
                $securityLabel->xmlSerialize(true, $sxe->addChild('securityLabel'));
            }
        }
        if (0 < count($this->purpose)) {
            foreach ($this->purpose as $purpose) {
                $purpose->xmlSerialize(true, $sxe->addChild('purpose'));
            }
        }
        if (isset($this->dataPeriod)) {
            $this->dataPeriod->xmlSerialize(true, $sxe->addChild('dataPeriod'));
        }
        if (0 < count($this->data)) {
            foreach ($this->data as $data) {
                $data->xmlSerialize(true, $sxe->addChild('data'));
            }
        }
        if (0 < count($this->except)) {
            foreach ($this->except as $except) {
                $except->xmlSerialize(true, $sxe->addChild('except'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
