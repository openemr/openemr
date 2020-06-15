<?php

namespace OpenEMR\FHIR\R4\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: June 14th, 2019
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *   Generated on Thu, Dec 27, 2018 22:37+1100 for FHIR v4.0.0
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/**
 * Indicates that a medication product is to be or has been dispensed for a named person/patient.  This includes a description of the medication product (supply) provided and the instructions for administering the medication.  The medication dispense is the result of a pharmacy system responding to a medication order.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicationDispense extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifiers associated with this Medication Dispense that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate. They are business identifiers assigned to this resource by the performer or other systems and remain constant as the resource is updated and propagates from server to server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The procedure that trigger the dispense.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $partOf = [];

    /**
     * A code specifying the state of the set of dispense events.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $status = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $statusReasonCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $statusReasonReference = null;

    /**
     * Indicates the type of medication dispense (for example, where the medication is expected to be consumed or administered (i.e. inpatient or outpatient)).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $category = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $medicationCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $medicationReference = null;

    /**
     * A link to a resource representing the person or the group to whom the medication will be given.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The encounter or episode of care that establishes the context for this event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $context = null;

    /**
     * Additional information that supports the medication being dispensed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $supportingInformation = [];

    /**
     * Indicates who or what performed the event.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationDispense\FHIRMedicationDispensePerformer[]
     */
    public $performer = [];

    /**
     * The principal physical location where the dispense was performed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $location = null;

    /**
     * Indicates the medication order that is being dispensed against.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $authorizingPrescription = [];

    /**
     * Indicates the type of dispensing event that is performed. For example, Trial Fill, Completion of Trial, Partial Fill, Emergency Fill, Samples, etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The amount of medication that has been dispensed. Includes unit of measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * The amount of medication expressed as a timing amount.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $daysSupply = null;

    /**
     * The time when the dispensed product was packaged and reviewed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $whenPrepared = null;

    /**
     * The time the dispensed product was provided to the patient or their representative.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $whenHandedOver = null;

    /**
     * Identification of the facility/location where the medication was shipped to, as part of the dispense event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $destination = null;

    /**
     * Identifies the person who picked up the medication.  This will usually be a patient or their caregiver, but some cases exist where it can be a healthcare professional.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $receiver = [];

    /**
     * Extra information about the dispense that could not be conveyed in the other attributes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * Indicates how the medication is to be used by the patient.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage[]
     */
    public $dosageInstruction = [];

    /**
     * Indicates whether or not substitution was made as part of the dispense.  In some cases, substitution will be expected but does not happen, in other cases substitution is not expected but does happen.  This block explains what substitution did or did not happen and why.  If nothing is specified, substitution was not done.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationDispense\FHIRMedicationDispenseSubstitution
     */
    public $substitution = null;

    /**
     * Indicates an actual or potential clinical issue with or between one or more active or proposed clinical actions for a patient; e.g. drug-drug interaction, duplicate therapy, dosage alert etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $detectedIssue = [];

    /**
     * A summary of the events of interest that have occurred, such as when the dispense was verified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $eventHistory = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationDispense';

    /**
     * Identifiers associated with this Medication Dispense that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate. They are business identifiers assigned to this resource by the performer or other systems and remain constant as the resource is updated and propagates from server to server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifiers associated with this Medication Dispense that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate. They are business identifiers assigned to this resource by the performer or other systems and remain constant as the resource is updated and propagates from server to server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The procedure that trigger the dispense.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * The procedure that trigger the dispense.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $partOf
     * @return $this
     */
    public function addPartOf($partOf)
    {
        $this->partOf[] = $partOf;
        return $this;
    }

    /**
     * A code specifying the state of the set of dispense events.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * A code specifying the state of the set of dispense events.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStatusReasonCodeableConcept()
    {
        return $this->statusReasonCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $statusReasonCodeableConcept
     * @return $this
     */
    public function setStatusReasonCodeableConcept($statusReasonCodeableConcept)
    {
        $this->statusReasonCodeableConcept = $statusReasonCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getStatusReasonReference()
    {
        return $this->statusReasonReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $statusReasonReference
     * @return $this
     */
    public function setStatusReasonReference($statusReasonReference)
    {
        $this->statusReasonReference = $statusReasonReference;
        return $this;
    }

    /**
     * Indicates the type of medication dispense (for example, where the medication is expected to be consumed or administered (i.e. inpatient or outpatient)).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Indicates the type of medication dispense (for example, where the medication is expected to be consumed or administered (i.e. inpatient or outpatient)).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getMedicationCodeableConcept()
    {
        return $this->medicationCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $medicationCodeableConcept
     * @return $this
     */
    public function setMedicationCodeableConcept($medicationCodeableConcept)
    {
        $this->medicationCodeableConcept = $medicationCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getMedicationReference()
    {
        return $this->medicationReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $medicationReference
     * @return $this
     */
    public function setMedicationReference($medicationReference)
    {
        $this->medicationReference = $medicationReference;
        return $this;
    }

    /**
     * A link to a resource representing the person or the group to whom the medication will be given.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * A link to a resource representing the person or the group to whom the medication will be given.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The encounter or episode of care that establishes the context for this event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * The encounter or episode of care that establishes the context for this event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Additional information that supports the medication being dispensed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSupportingInformation()
    {
        return $this->supportingInformation;
    }

    /**
     * Additional information that supports the medication being dispensed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $supportingInformation
     * @return $this
     */
    public function addSupportingInformation($supportingInformation)
    {
        $this->supportingInformation[] = $supportingInformation;
        return $this;
    }

    /**
     * Indicates who or what performed the event.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationDispense\FHIRMedicationDispensePerformer[]
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * Indicates who or what performed the event.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationDispense\FHIRMedicationDispensePerformer $performer
     * @return $this
     */
    public function addPerformer($performer)
    {
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * The principal physical location where the dispense was performed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The principal physical location where the dispense was performed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Indicates the medication order that is being dispensed against.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAuthorizingPrescription()
    {
        return $this->authorizingPrescription;
    }

    /**
     * Indicates the medication order that is being dispensed against.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $authorizingPrescription
     * @return $this
     */
    public function addAuthorizingPrescription($authorizingPrescription)
    {
        $this->authorizingPrescription[] = $authorizingPrescription;
        return $this;
    }

    /**
     * Indicates the type of dispensing event that is performed. For example, Trial Fill, Completion of Trial, Partial Fill, Emergency Fill, Samples, etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Indicates the type of dispensing event that is performed. For example, Trial Fill, Completion of Trial, Partial Fill, Emergency Fill, Samples, etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The amount of medication that has been dispensed. Includes unit of measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The amount of medication that has been dispensed. Includes unit of measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * The amount of medication expressed as a timing amount.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getDaysSupply()
    {
        return $this->daysSupply;
    }

    /**
     * The amount of medication expressed as a timing amount.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $daysSupply
     * @return $this
     */
    public function setDaysSupply($daysSupply)
    {
        $this->daysSupply = $daysSupply;
        return $this;
    }

    /**
     * The time when the dispensed product was packaged and reviewed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getWhenPrepared()
    {
        return $this->whenPrepared;
    }

    /**
     * The time when the dispensed product was packaged and reviewed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $whenPrepared
     * @return $this
     */
    public function setWhenPrepared($whenPrepared)
    {
        $this->whenPrepared = $whenPrepared;
        return $this;
    }

    /**
     * The time the dispensed product was provided to the patient or their representative.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getWhenHandedOver()
    {
        return $this->whenHandedOver;
    }

    /**
     * The time the dispensed product was provided to the patient or their representative.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $whenHandedOver
     * @return $this
     */
    public function setWhenHandedOver($whenHandedOver)
    {
        $this->whenHandedOver = $whenHandedOver;
        return $this;
    }

    /**
     * Identification of the facility/location where the medication was shipped to, as part of the dispense event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Identification of the facility/location where the medication was shipped to, as part of the dispense event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $destination
     * @return $this
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * Identifies the person who picked up the medication.  This will usually be a patient or their caregiver, but some cases exist where it can be a healthcare professional.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Identifies the person who picked up the medication.  This will usually be a patient or their caregiver, but some cases exist where it can be a healthcare professional.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $receiver
     * @return $this
     */
    public function addReceiver($receiver)
    {
        $this->receiver[] = $receiver;
        return $this;
    }

    /**
     * Extra information about the dispense that could not be conveyed in the other attributes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Extra information about the dispense that could not be conveyed in the other attributes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * Indicates how the medication is to be used by the patient.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage[]
     */
    public function getDosageInstruction()
    {
        return $this->dosageInstruction;
    }

    /**
     * Indicates how the medication is to be used by the patient.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage $dosageInstruction
     * @return $this
     */
    public function addDosageInstruction($dosageInstruction)
    {
        $this->dosageInstruction[] = $dosageInstruction;
        return $this;
    }

    /**
     * Indicates whether or not substitution was made as part of the dispense.  In some cases, substitution will be expected but does not happen, in other cases substitution is not expected but does happen.  This block explains what substitution did or did not happen and why.  If nothing is specified, substitution was not done.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationDispense\FHIRMedicationDispenseSubstitution
     */
    public function getSubstitution()
    {
        return $this->substitution;
    }

    /**
     * Indicates whether or not substitution was made as part of the dispense.  In some cases, substitution will be expected but does not happen, in other cases substitution is not expected but does happen.  This block explains what substitution did or did not happen and why.  If nothing is specified, substitution was not done.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationDispense\FHIRMedicationDispenseSubstitution $substitution
     * @return $this
     */
    public function setSubstitution($substitution)
    {
        $this->substitution = $substitution;
        return $this;
    }

    /**
     * Indicates an actual or potential clinical issue with or between one or more active or proposed clinical actions for a patient; e.g. drug-drug interaction, duplicate therapy, dosage alert etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getDetectedIssue()
    {
        return $this->detectedIssue;
    }

    /**
     * Indicates an actual or potential clinical issue with or between one or more active or proposed clinical actions for a patient; e.g. drug-drug interaction, duplicate therapy, dosage alert etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $detectedIssue
     * @return $this
     */
    public function addDetectedIssue($detectedIssue)
    {
        $this->detectedIssue[] = $detectedIssue;
        return $this;
    }

    /**
     * A summary of the events of interest that have occurred, such as when the dispense was verified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEventHistory()
    {
        return $this->eventHistory;
    }

    /**
     * A summary of the events of interest that have occurred, such as when the dispense was verified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $eventHistory
     * @return $this
     */
    public function addEventHistory($eventHistory)
    {
        $this->eventHistory[] = $eventHistory;
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
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, ' . gettype($data['identifier']) . ' seen.');
                }
            }
            if (isset($data['partOf'])) {
                if (is_array($data['partOf'])) {
                    foreach ($data['partOf'] as $d) {
                        $this->addPartOf($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"partOf" must be array of objects or null, ' . gettype($data['partOf']) . ' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['statusReasonCodeableConcept'])) {
                $this->setStatusReasonCodeableConcept($data['statusReasonCodeableConcept']);
            }
            if (isset($data['statusReasonReference'])) {
                $this->setStatusReasonReference($data['statusReasonReference']);
            }
            if (isset($data['category'])) {
                $this->setCategory($data['category']);
            }
            if (isset($data['medicationCodeableConcept'])) {
                $this->setMedicationCodeableConcept($data['medicationCodeableConcept']);
            }
            if (isset($data['medicationReference'])) {
                $this->setMedicationReference($data['medicationReference']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['context'])) {
                $this->setContext($data['context']);
            }
            if (isset($data['supportingInformation'])) {
                if (is_array($data['supportingInformation'])) {
                    foreach ($data['supportingInformation'] as $d) {
                        $this->addSupportingInformation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportingInformation" must be array of objects or null, ' . gettype($data['supportingInformation']) . ' seen.');
                }
            }
            if (isset($data['performer'])) {
                if (is_array($data['performer'])) {
                    foreach ($data['performer'] as $d) {
                        $this->addPerformer($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"performer" must be array of objects or null, ' . gettype($data['performer']) . ' seen.');
                }
            }
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['authorizingPrescription'])) {
                if (is_array($data['authorizingPrescription'])) {
                    foreach ($data['authorizingPrescription'] as $d) {
                        $this->addAuthorizingPrescription($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"authorizingPrescription" must be array of objects or null, ' . gettype($data['authorizingPrescription']) . ' seen.');
                }
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['daysSupply'])) {
                $this->setDaysSupply($data['daysSupply']);
            }
            if (isset($data['whenPrepared'])) {
                $this->setWhenPrepared($data['whenPrepared']);
            }
            if (isset($data['whenHandedOver'])) {
                $this->setWhenHandedOver($data['whenHandedOver']);
            }
            if (isset($data['destination'])) {
                $this->setDestination($data['destination']);
            }
            if (isset($data['receiver'])) {
                if (is_array($data['receiver'])) {
                    foreach ($data['receiver'] as $d) {
                        $this->addReceiver($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"receiver" must be array of objects or null, ' . gettype($data['receiver']) . ' seen.');
                }
            }
            if (isset($data['note'])) {
                if (is_array($data['note'])) {
                    foreach ($data['note'] as $d) {
                        $this->addNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"note" must be array of objects or null, ' . gettype($data['note']) . ' seen.');
                }
            }
            if (isset($data['dosageInstruction'])) {
                if (is_array($data['dosageInstruction'])) {
                    foreach ($data['dosageInstruction'] as $d) {
                        $this->addDosageInstruction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dosageInstruction" must be array of objects or null, ' . gettype($data['dosageInstruction']) . ' seen.');
                }
            }
            if (isset($data['substitution'])) {
                $this->setSubstitution($data['substitution']);
            }
            if (isset($data['detectedIssue'])) {
                if (is_array($data['detectedIssue'])) {
                    foreach ($data['detectedIssue'] as $d) {
                        $this->addDetectedIssue($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"detectedIssue" must be array of objects or null, ' . gettype($data['detectedIssue']) . ' seen.');
                }
            }
            if (isset($data['eventHistory'])) {
                if (is_array($data['eventHistory'])) {
                    foreach ($data['eventHistory'] as $d) {
                        $this->addEventHistory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"eventHistory" must be array of objects or null, ' . gettype($data['eventHistory']) . ' seen.');
                }
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (0 < count($this->partOf)) {
            $json['partOf'] = [];
            foreach ($this->partOf as $partOf) {
                $json['partOf'][] = $partOf;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->statusReasonCodeableConcept)) {
            $json['statusReasonCodeableConcept'] = $this->statusReasonCodeableConcept;
        }
        if (isset($this->statusReasonReference)) {
            $json['statusReasonReference'] = $this->statusReasonReference;
        }
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        if (isset($this->medicationCodeableConcept)) {
            $json['medicationCodeableConcept'] = $this->medicationCodeableConcept;
        }
        if (isset($this->medicationReference)) {
            $json['medicationReference'] = $this->medicationReference;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->context)) {
            $json['context'] = $this->context;
        }
        if (0 < count($this->supportingInformation)) {
            $json['supportingInformation'] = [];
            foreach ($this->supportingInformation as $supportingInformation) {
                $json['supportingInformation'][] = $supportingInformation;
            }
        }
        if (0 < count($this->performer)) {
            $json['performer'] = [];
            foreach ($this->performer as $performer) {
                $json['performer'][] = $performer;
            }
        }
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (0 < count($this->authorizingPrescription)) {
            $json['authorizingPrescription'] = [];
            foreach ($this->authorizingPrescription as $authorizingPrescription) {
                $json['authorizingPrescription'][] = $authorizingPrescription;
            }
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->daysSupply)) {
            $json['daysSupply'] = $this->daysSupply;
        }
        if (isset($this->whenPrepared)) {
            $json['whenPrepared'] = $this->whenPrepared;
        }
        if (isset($this->whenHandedOver)) {
            $json['whenHandedOver'] = $this->whenHandedOver;
        }
        if (isset($this->destination)) {
            $json['destination'] = $this->destination;
        }
        if (0 < count($this->receiver)) {
            $json['receiver'] = [];
            foreach ($this->receiver as $receiver) {
                $json['receiver'][] = $receiver;
            }
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
        }
        if (0 < count($this->dosageInstruction)) {
            $json['dosageInstruction'] = [];
            foreach ($this->dosageInstruction as $dosageInstruction) {
                $json['dosageInstruction'][] = $dosageInstruction;
            }
        }
        if (isset($this->substitution)) {
            $json['substitution'] = $this->substitution;
        }
        if (0 < count($this->detectedIssue)) {
            $json['detectedIssue'] = [];
            foreach ($this->detectedIssue as $detectedIssue) {
                $json['detectedIssue'][] = $detectedIssue;
            }
        }
        if (0 < count($this->eventHistory)) {
            $json['eventHistory'] = [];
            foreach ($this->eventHistory as $eventHistory) {
                $json['eventHistory'][] = $eventHistory;
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
            $sxe = new \SimpleXMLElement('<MedicationDispense xmlns="http://hl7.org/fhir"></MedicationDispense>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (0 < count($this->partOf)) {
            foreach ($this->partOf as $partOf) {
                $partOf->xmlSerialize(true, $sxe->addChild('partOf'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->statusReasonCodeableConcept)) {
            $this->statusReasonCodeableConcept->xmlSerialize(true, $sxe->addChild('statusReasonCodeableConcept'));
        }
        if (isset($this->statusReasonReference)) {
            $this->statusReasonReference->xmlSerialize(true, $sxe->addChild('statusReasonReference'));
        }
        if (isset($this->category)) {
            $this->category->xmlSerialize(true, $sxe->addChild('category'));
        }
        if (isset($this->medicationCodeableConcept)) {
            $this->medicationCodeableConcept->xmlSerialize(true, $sxe->addChild('medicationCodeableConcept'));
        }
        if (isset($this->medicationReference)) {
            $this->medicationReference->xmlSerialize(true, $sxe->addChild('medicationReference'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->context)) {
            $this->context->xmlSerialize(true, $sxe->addChild('context'));
        }
        if (0 < count($this->supportingInformation)) {
            foreach ($this->supportingInformation as $supportingInformation) {
                $supportingInformation->xmlSerialize(true, $sxe->addChild('supportingInformation'));
            }
        }
        if (0 < count($this->performer)) {
            foreach ($this->performer as $performer) {
                $performer->xmlSerialize(true, $sxe->addChild('performer'));
            }
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (0 < count($this->authorizingPrescription)) {
            foreach ($this->authorizingPrescription as $authorizingPrescription) {
                $authorizingPrescription->xmlSerialize(true, $sxe->addChild('authorizingPrescription'));
            }
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->daysSupply)) {
            $this->daysSupply->xmlSerialize(true, $sxe->addChild('daysSupply'));
        }
        if (isset($this->whenPrepared)) {
            $this->whenPrepared->xmlSerialize(true, $sxe->addChild('whenPrepared'));
        }
        if (isset($this->whenHandedOver)) {
            $this->whenHandedOver->xmlSerialize(true, $sxe->addChild('whenHandedOver'));
        }
        if (isset($this->destination)) {
            $this->destination->xmlSerialize(true, $sxe->addChild('destination'));
        }
        if (0 < count($this->receiver)) {
            foreach ($this->receiver as $receiver) {
                $receiver->xmlSerialize(true, $sxe->addChild('receiver'));
            }
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if (0 < count($this->dosageInstruction)) {
            foreach ($this->dosageInstruction as $dosageInstruction) {
                $dosageInstruction->xmlSerialize(true, $sxe->addChild('dosageInstruction'));
            }
        }
        if (isset($this->substitution)) {
            $this->substitution->xmlSerialize(true, $sxe->addChild('substitution'));
        }
        if (0 < count($this->detectedIssue)) {
            foreach ($this->detectedIssue as $detectedIssue) {
                $detectedIssue->xmlSerialize(true, $sxe->addChild('detectedIssue'));
            }
        }
        if (0 < count($this->eventHistory)) {
            foreach ($this->eventHistory as $eventHistory) {
                $eventHistory->xmlSerialize(true, $sxe->addChild('eventHistory'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
