<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * The resource ChargeItem describes the provision of healthcare provider products for a certain patient, therefore referring not only to the product, but containing in addition details of the provision, like date, time, amounts and participating organizations and persons. Main Usage of the ChargeItem is to enable the billing process and internal cost allocation.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRChargeItem extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifiers assigned to this event performer or other systems.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * References the source of pricing information, rules of application for the code this ChargeItem uses.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public $definition = [];

    /**
     * The current state of the ChargeItem.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRChargeItemStatus
     */
    public $status = null;

    /**
     * ChargeItems can be grouped to larger ChargeItems covering the whole set.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $partOf = [];

    /**
     * A code that identifies the charge, like a billing code.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * The individual or set of individuals the action is being or was performed on.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The encounter or episode of care that establishes the context for this event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $context = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $occurrenceDateTime = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $occurrencePeriod = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTiming
     */
    public $occurrenceTiming = null;

    /**
     * Indicates who or what performed or participated in the charged service.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRChargeItem\FHIRChargeItemParticipant[]
     */
    public $participant = [];

    /**
     * The organization requesting the service.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $performingOrganization = null;

    /**
     * The organization performing the service.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $requestingOrganization = null;

    /**
     * Quantity of which the charge item has been serviced.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * The anatomical location where the related service has been applied.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $bodysite = [];

    /**
     * Factor overriding the factor determined by the rules associated with the code.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $factorOverride = null;

    /**
     * Total price of the charge overriding the list price associated with the code.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public $priceOverride = null;

    /**
     * If the list price or the rule based factor associated with the code is overridden, this attribute can capture a text to indicate the  reason for this action.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $overrideReason = null;

    /**
     * The device, practitioner, etc. who entered the charge item.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $enterer = null;

    /**
     * Date the charge item was entered.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $enteredDate = null;

    /**
     * Describes why the event occurred in coded or textual form.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $reason = [];

    /**
     * Indicated the rendered service that caused this charge.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $service = [];

    /**
     * Account into which this ChargeItems belongs.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $account = [];

    /**
     * Comments made about the event by the performer, subject or other participants.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * Further information supporting the this charge.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $supportingInformation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ChargeItem';

    /**
     * Identifiers assigned to this event performer or other systems.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifiers assigned to this event performer or other systems.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * References the source of pricing information, rules of application for the code this ChargeItem uses.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * References the source of pricing information, rules of application for the code this ChargeItem uses.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $definition
     * @return $this
     */
    public function addDefinition($definition)
    {
        $this->definition[] = $definition;
        return $this;
    }

    /**
     * The current state of the ChargeItem.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRChargeItemStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The current state of the ChargeItem.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRChargeItemStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * ChargeItems can be grouped to larger ChargeItems covering the whole set.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * ChargeItems can be grouped to larger ChargeItems covering the whole set.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $partOf
     * @return $this
     */
    public function addPartOf($partOf)
    {
        $this->partOf[] = $partOf;
        return $this;
    }

    /**
     * A code that identifies the charge, like a billing code.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A code that identifies the charge, like a billing code.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The individual or set of individuals the action is being or was performed on.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The individual or set of individuals the action is being or was performed on.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The encounter or episode of care that establishes the context for this event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * The encounter or episode of care that establishes the context for this event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getOccurrenceDateTime()
    {
        return $this->occurrenceDateTime;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $occurrenceDateTime
     * @return $this
     */
    public function setOccurrenceDateTime($occurrenceDateTime)
    {
        $this->occurrenceDateTime = $occurrenceDateTime;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getOccurrencePeriod()
    {
        return $this->occurrencePeriod;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $occurrencePeriod
     * @return $this
     */
    public function setOccurrencePeriod($occurrencePeriod)
    {
        $this->occurrencePeriod = $occurrencePeriod;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTiming
     */
    public function getOccurrenceTiming()
    {
        return $this->occurrenceTiming;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTiming $occurrenceTiming
     * @return $this
     */
    public function setOccurrenceTiming($occurrenceTiming)
    {
        $this->occurrenceTiming = $occurrenceTiming;
        return $this;
    }

    /**
     * Indicates who or what performed or participated in the charged service.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRChargeItem\FHIRChargeItemParticipant[]
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Indicates who or what performed or participated in the charged service.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRChargeItem\FHIRChargeItemParticipant $participant
     * @return $this
     */
    public function addParticipant($participant)
    {
        $this->participant[] = $participant;
        return $this;
    }

    /**
     * The organization requesting the service.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getPerformingOrganization()
    {
        return $this->performingOrganization;
    }

    /**
     * The organization requesting the service.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $performingOrganization
     * @return $this
     */
    public function setPerformingOrganization($performingOrganization)
    {
        $this->performingOrganization = $performingOrganization;
        return $this;
    }

    /**
     * The organization performing the service.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getRequestingOrganization()
    {
        return $this->requestingOrganization;
    }

    /**
     * The organization performing the service.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $requestingOrganization
     * @return $this
     */
    public function setRequestingOrganization($requestingOrganization)
    {
        $this->requestingOrganization = $requestingOrganization;
        return $this;
    }

    /**
     * Quantity of which the charge item has been serviced.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Quantity of which the charge item has been serviced.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * The anatomical location where the related service has been applied.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getBodysite()
    {
        return $this->bodysite;
    }

    /**
     * The anatomical location where the related service has been applied.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $bodysite
     * @return $this
     */
    public function addBodysite($bodysite)
    {
        $this->bodysite[] = $bodysite;
        return $this;
    }

    /**
     * Factor overriding the factor determined by the rules associated with the code.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getFactorOverride()
    {
        return $this->factorOverride;
    }

    /**
     * Factor overriding the factor determined by the rules associated with the code.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $factorOverride
     * @return $this
     */
    public function setFactorOverride($factorOverride)
    {
        $this->factorOverride = $factorOverride;
        return $this;
    }

    /**
     * Total price of the charge overriding the list price associated with the code.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney
     */
    public function getPriceOverride()
    {
        return $this->priceOverride;
    }

    /**
     * Total price of the charge overriding the list price associated with the code.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity\FHIRMoney $priceOverride
     * @return $this
     */
    public function setPriceOverride($priceOverride)
    {
        $this->priceOverride = $priceOverride;
        return $this;
    }

    /**
     * If the list price or the rule based factor associated with the code is overridden, this attribute can capture a text to indicate the  reason for this action.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getOverrideReason()
    {
        return $this->overrideReason;
    }

    /**
     * If the list price or the rule based factor associated with the code is overridden, this attribute can capture a text to indicate the  reason for this action.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $overrideReason
     * @return $this
     */
    public function setOverrideReason($overrideReason)
    {
        $this->overrideReason = $overrideReason;
        return $this;
    }

    /**
     * The device, practitioner, etc. who entered the charge item.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getEnterer()
    {
        return $this->enterer;
    }

    /**
     * The device, practitioner, etc. who entered the charge item.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $enterer
     * @return $this
     */
    public function setEnterer($enterer)
    {
        $this->enterer = $enterer;
        return $this;
    }

    /**
     * Date the charge item was entered.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getEnteredDate()
    {
        return $this->enteredDate;
    }

    /**
     * Date the charge item was entered.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $enteredDate
     * @return $this
     */
    public function setEnteredDate($enteredDate)
    {
        $this->enteredDate = $enteredDate;
        return $this;
    }

    /**
     * Describes why the event occurred in coded or textual form.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Describes why the event occurred in coded or textual form.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $reason
     * @return $this
     */
    public function addReason($reason)
    {
        $this->reason[] = $reason;
        return $this;
    }

    /**
     * Indicated the rendered service that caused this charge.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Indicated the rendered service that caused this charge.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $service
     * @return $this
     */
    public function addService($service)
    {
        $this->service[] = $service;
        return $this;
    }

    /**
     * Account into which this ChargeItems belongs.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Account into which this ChargeItems belongs.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $account
     * @return $this
     */
    public function addAccount($account)
    {
        $this->account[] = $account;
        return $this;
    }

    /**
     * Comments made about the event by the performer, subject or other participants.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Comments made about the event by the performer, subject or other participants.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * Further information supporting the this charge.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getSupportingInformation()
    {
        return $this->supportingInformation;
    }

    /**
     * Further information supporting the this charge.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $supportingInformation
     * @return $this
     */
    public function addSupportingInformation($supportingInformation)
    {
        $this->supportingInformation[] = $supportingInformation;
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
            if (isset($data['definition'])) {
                if (is_array($data['definition'])) {
                    foreach ($data['definition'] as $d) {
                        $this->addDefinition($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"definition" must be array of objects or null, '.gettype($data['definition']).' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['partOf'])) {
                if (is_array($data['partOf'])) {
                    foreach ($data['partOf'] as $d) {
                        $this->addPartOf($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"partOf" must be array of objects or null, '.gettype($data['partOf']).' seen.');
                }
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['context'])) {
                $this->setContext($data['context']);
            }
            if (isset($data['occurrenceDateTime'])) {
                $this->setOccurrenceDateTime($data['occurrenceDateTime']);
            }
            if (isset($data['occurrencePeriod'])) {
                $this->setOccurrencePeriod($data['occurrencePeriod']);
            }
            if (isset($data['occurrenceTiming'])) {
                $this->setOccurrenceTiming($data['occurrenceTiming']);
            }
            if (isset($data['participant'])) {
                if (is_array($data['participant'])) {
                    foreach ($data['participant'] as $d) {
                        $this->addParticipant($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"participant" must be array of objects or null, '.gettype($data['participant']).' seen.');
                }
            }
            if (isset($data['performingOrganization'])) {
                $this->setPerformingOrganization($data['performingOrganization']);
            }
            if (isset($data['requestingOrganization'])) {
                $this->setRequestingOrganization($data['requestingOrganization']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['bodysite'])) {
                if (is_array($data['bodysite'])) {
                    foreach ($data['bodysite'] as $d) {
                        $this->addBodysite($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"bodysite" must be array of objects or null, '.gettype($data['bodysite']).' seen.');
                }
            }
            if (isset($data['factorOverride'])) {
                $this->setFactorOverride($data['factorOverride']);
            }
            if (isset($data['priceOverride'])) {
                $this->setPriceOverride($data['priceOverride']);
            }
            if (isset($data['overrideReason'])) {
                $this->setOverrideReason($data['overrideReason']);
            }
            if (isset($data['enterer'])) {
                $this->setEnterer($data['enterer']);
            }
            if (isset($data['enteredDate'])) {
                $this->setEnteredDate($data['enteredDate']);
            }
            if (isset($data['reason'])) {
                if (is_array($data['reason'])) {
                    foreach ($data['reason'] as $d) {
                        $this->addReason($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reason" must be array of objects or null, '.gettype($data['reason']).' seen.');
                }
            }
            if (isset($data['service'])) {
                if (is_array($data['service'])) {
                    foreach ($data['service'] as $d) {
                        $this->addService($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"service" must be array of objects or null, '.gettype($data['service']).' seen.');
                }
            }
            if (isset($data['account'])) {
                if (is_array($data['account'])) {
                    foreach ($data['account'] as $d) {
                        $this->addAccount($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"account" must be array of objects or null, '.gettype($data['account']).' seen.');
                }
            }
            if (isset($data['note'])) {
                if (is_array($data['note'])) {
                    foreach ($data['note'] as $d) {
                        $this->addNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"note" must be array of objects or null, '.gettype($data['note']).' seen.');
                }
            }
            if (isset($data['supportingInformation'])) {
                if (is_array($data['supportingInformation'])) {
                    foreach ($data['supportingInformation'] as $d) {
                        $this->addSupportingInformation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportingInformation" must be array of objects or null, '.gettype($data['supportingInformation']).' seen.');
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
        if (0 < count($this->definition)) {
            $json['definition'] = [];
            foreach ($this->definition as $definition) {
                $json['definition'][] = $definition;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (0 < count($this->partOf)) {
            $json['partOf'] = [];
            foreach ($this->partOf as $partOf) {
                $json['partOf'][] = $partOf;
            }
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->context)) {
            $json['context'] = $this->context;
        }
        if (isset($this->occurrenceDateTime)) {
            $json['occurrenceDateTime'] = $this->occurrenceDateTime;
        }
        if (isset($this->occurrencePeriod)) {
            $json['occurrencePeriod'] = $this->occurrencePeriod;
        }
        if (isset($this->occurrenceTiming)) {
            $json['occurrenceTiming'] = $this->occurrenceTiming;
        }
        if (0 < count($this->participant)) {
            $json['participant'] = [];
            foreach ($this->participant as $participant) {
                $json['participant'][] = $participant;
            }
        }
        if (isset($this->performingOrganization)) {
            $json['performingOrganization'] = $this->performingOrganization;
        }
        if (isset($this->requestingOrganization)) {
            $json['requestingOrganization'] = $this->requestingOrganization;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (0 < count($this->bodysite)) {
            $json['bodysite'] = [];
            foreach ($this->bodysite as $bodysite) {
                $json['bodysite'][] = $bodysite;
            }
        }
        if (isset($this->factorOverride)) {
            $json['factorOverride'] = $this->factorOverride;
        }
        if (isset($this->priceOverride)) {
            $json['priceOverride'] = $this->priceOverride;
        }
        if (isset($this->overrideReason)) {
            $json['overrideReason'] = $this->overrideReason;
        }
        if (isset($this->enterer)) {
            $json['enterer'] = $this->enterer;
        }
        if (isset($this->enteredDate)) {
            $json['enteredDate'] = $this->enteredDate;
        }
        if (0 < count($this->reason)) {
            $json['reason'] = [];
            foreach ($this->reason as $reason) {
                $json['reason'][] = $reason;
            }
        }
        if (0 < count($this->service)) {
            $json['service'] = [];
            foreach ($this->service as $service) {
                $json['service'][] = $service;
            }
        }
        if (0 < count($this->account)) {
            $json['account'] = [];
            foreach ($this->account as $account) {
                $json['account'][] = $account;
            }
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
        }
        if (0 < count($this->supportingInformation)) {
            $json['supportingInformation'] = [];
            foreach ($this->supportingInformation as $supportingInformation) {
                $json['supportingInformation'][] = $supportingInformation;
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
            $sxe = new \SimpleXMLElement('<ChargeItem xmlns="http://hl7.org/fhir"></ChargeItem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (0 < count($this->definition)) {
            foreach ($this->definition as $definition) {
                $definition->xmlSerialize(true, $sxe->addChild('definition'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (0 < count($this->partOf)) {
            foreach ($this->partOf as $partOf) {
                $partOf->xmlSerialize(true, $sxe->addChild('partOf'));
            }
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->context)) {
            $this->context->xmlSerialize(true, $sxe->addChild('context'));
        }
        if (isset($this->occurrenceDateTime)) {
            $this->occurrenceDateTime->xmlSerialize(true, $sxe->addChild('occurrenceDateTime'));
        }
        if (isset($this->occurrencePeriod)) {
            $this->occurrencePeriod->xmlSerialize(true, $sxe->addChild('occurrencePeriod'));
        }
        if (isset($this->occurrenceTiming)) {
            $this->occurrenceTiming->xmlSerialize(true, $sxe->addChild('occurrenceTiming'));
        }
        if (0 < count($this->participant)) {
            foreach ($this->participant as $participant) {
                $participant->xmlSerialize(true, $sxe->addChild('participant'));
            }
        }
        if (isset($this->performingOrganization)) {
            $this->performingOrganization->xmlSerialize(true, $sxe->addChild('performingOrganization'));
        }
        if (isset($this->requestingOrganization)) {
            $this->requestingOrganization->xmlSerialize(true, $sxe->addChild('requestingOrganization'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (0 < count($this->bodysite)) {
            foreach ($this->bodysite as $bodysite) {
                $bodysite->xmlSerialize(true, $sxe->addChild('bodysite'));
            }
        }
        if (isset($this->factorOverride)) {
            $this->factorOverride->xmlSerialize(true, $sxe->addChild('factorOverride'));
        }
        if (isset($this->priceOverride)) {
            $this->priceOverride->xmlSerialize(true, $sxe->addChild('priceOverride'));
        }
        if (isset($this->overrideReason)) {
            $this->overrideReason->xmlSerialize(true, $sxe->addChild('overrideReason'));
        }
        if (isset($this->enterer)) {
            $this->enterer->xmlSerialize(true, $sxe->addChild('enterer'));
        }
        if (isset($this->enteredDate)) {
            $this->enteredDate->xmlSerialize(true, $sxe->addChild('enteredDate'));
        }
        if (0 < count($this->reason)) {
            foreach ($this->reason as $reason) {
                $reason->xmlSerialize(true, $sxe->addChild('reason'));
            }
        }
        if (0 < count($this->service)) {
            foreach ($this->service as $service) {
                $service->xmlSerialize(true, $sxe->addChild('service'));
            }
        }
        if (0 < count($this->account)) {
            foreach ($this->account as $account) {
                $account->xmlSerialize(true, $sxe->addChild('account'));
            }
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if (0 < count($this->supportingInformation)) {
            foreach ($this->supportingInformation as $supportingInformation) {
                $supportingInformation->xmlSerialize(true, $sxe->addChild('supportingInformation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
