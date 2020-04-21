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
 * An order or request for both supply of the medication and the instructions for administration of the medication to a patient. The resource is called "MedicationRequest" rather than "MedicationPrescription" or "MedicationOrder" to generalize the use across inpatient and outpatient settings, including care plans, etc., and to harmonize with workflow patterns.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicationRequest extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifiers associated with this medication request that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate. They are business identifiers assigned to this resource by the performer or other systems and remain constant as the resource is updated and propagates from server to server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * A code specifying the current state of the order.  Generally, this will be active or completed state.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationrequestStatus
     */
    public $status = null;

    /**
     * Captures the reason for the current state of the MedicationRequest.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $statusReason = null;

    /**
     * Whether the request is a proposal, plan, or an original order.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationRequestIntent
     */
    public $intent = null;

    /**
     * Indicates the type of medication request (for example, where the medication is expected to be consumed or administered (i.e. inpatient or outpatient)).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $category = [];

    /**
     * Indicates how quickly the Medication Request should be addressed with respect to other requests.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority
     */
    public $priority = null;

    /**
     * If true indicates that the provider is asking for the medication request not to occur.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $doNotPerform = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $reportedBoolean = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $reportedReference = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $medicationCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $medicationReference = null;

    /**
     * A link to a resource representing the person or set of individuals to whom the medication will be given.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The Encounter during which this [x] was created or to which the creation of this record is tightly associated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * Include additional information (for example, patient height and weight) that supports the ordering of the medication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $supportingInformation = [];

    /**
     * The date (and perhaps time) when the prescription was initially written or authored on.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $authoredOn = null;

    /**
     * The individual, organization, or device that initiated the request and has responsibility for its activation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $requester = null;

    /**
     * The specified desired performer of the medication treatment (e.g. the performer of the medication administration).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $performer = null;

    /**
     * Indicates the type of performer of the administration of the medication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $performerType = null;

    /**
     * The person who entered the order on behalf of another individual for example in the case of a verbal or a telephone order.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $recorder = null;

    /**
     * The reason or the indication for ordering or not ordering the medication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $reasonCode = [];

    /**
     * Condition or observation that supports why the medication was ordered.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $reasonReference = [];

    /**
     * The URL pointing to a protocol, guideline, orderset, or other definition that is adhered to in whole or in part by this MedicationRequest.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $instantiatesCanonical = [];

    /**
     * The URL pointing to an externally maintained protocol, guideline, orderset or other definition that is adhered to in whole or in part by this MedicationRequest.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri[]
     */
    public $instantiatesUri = [];

    /**
     * A plan or request that is fulfilled in whole or in part by this medication request.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $basedOn = [];

    /**
     * A shared identifier common to all requests that were authorized more or less simultaneously by a single author, representing the identifier of the requisition or prescription.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $groupIdentifier = null;

    /**
     * The description of the overall patte3rn of the administration of the medication to the patient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $courseOfTherapyType = null;

    /**
     * Insurance plans, coverage extensions, pre-authorizations and/or pre-determinations that may be required for delivering the requested service.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $insurance = [];

    /**
     * Extra information about the prescription that could not be conveyed by the other attributes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * Indicates how the medication is to be used by the patient.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDosage[]
     */
    public $dosageInstruction = [];

    /**
     * Indicates the specific details for the dispense or medication supply part of a medication request (also known as a Medication Prescription or Medication Order).  Note that this information is not always sent with the order.  There may be in some settings (e.g. hospitals) institutional or system support for completing the dispense details in the pharmacy department.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestDispenseRequest
     */
    public $dispenseRequest = null;

    /**
     * Indicates whether or not substitution can or should be part of the dispense. In some cases, substitution must happen, in other cases substitution must not happen. This block explains the prescriber's intent. If nothing is specified substitution may be done.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestSubstitution
     */
    public $substitution = null;

    /**
     * A link to a resource representing an earlier order related order or prescription.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $priorPrescription = null;

    /**
     * Indicates an actual or potential clinical issue with or between one or more active or proposed clinical actions for a patient; e.g. Drug-drug interaction, duplicate therapy, dosage alert etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $detectedIssue = [];

    /**
     * Links to Provenance records for past versions of this resource or fulfilling request or event resources that identify key state transitions or updates that are likely to be relevant to a user looking at the current version of the resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $eventHistory = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationRequest';

    /**
     * Identifiers associated with this medication request that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate. They are business identifiers assigned to this resource by the performer or other systems and remain constant as the resource is updated and propagates from server to server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifiers associated with this medication request that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate. They are business identifiers assigned to this resource by the performer or other systems and remain constant as the resource is updated and propagates from server to server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * A code specifying the current state of the order.  Generally, this will be active or completed state.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationrequestStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * A code specifying the current state of the order.  Generally, this will be active or completed state.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationrequestStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Captures the reason for the current state of the MedicationRequest.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStatusReason()
    {
        return $this->statusReason;
    }

    /**
     * Captures the reason for the current state of the MedicationRequest.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $statusReason
     * @return $this
     */
    public function setStatusReason($statusReason)
    {
        $this->statusReason = $statusReason;
        return $this;
    }

    /**
     * Whether the request is a proposal, plan, or an original order.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationRequestIntent
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * Whether the request is a proposal, plan, or an original order.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMedicationRequestIntent $intent
     * @return $this
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
        return $this;
    }

    /**
     * Indicates the type of medication request (for example, where the medication is expected to be consumed or administered (i.e. inpatient or outpatient)).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Indicates the type of medication request (for example, where the medication is expected to be consumed or administered (i.e. inpatient or outpatient)).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->category[] = $category;
        return $this;
    }

    /**
     * Indicates how quickly the Medication Request should be addressed with respect to other requests.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Indicates how quickly the Medication Request should be addressed with respect to other requests.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * If true indicates that the provider is asking for the medication request not to occur.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getDoNotPerform()
    {
        return $this->doNotPerform;
    }

    /**
     * If true indicates that the provider is asking for the medication request not to occur.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $doNotPerform
     * @return $this
     */
    public function setDoNotPerform($doNotPerform)
    {
        $this->doNotPerform = $doNotPerform;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getReportedBoolean()
    {
        return $this->reportedBoolean;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $reportedBoolean
     * @return $this
     */
    public function setReportedBoolean($reportedBoolean)
    {
        $this->reportedBoolean = $reportedBoolean;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getReportedReference()
    {
        return $this->reportedReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reportedReference
     * @return $this
     */
    public function setReportedReference($reportedReference)
    {
        $this->reportedReference = $reportedReference;
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
     * A link to a resource representing the person or set of individuals to whom the medication will be given.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * A link to a resource representing the person or set of individuals to whom the medication will be given.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The Encounter during which this [x] was created or to which the creation of this record is tightly associated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * The Encounter during which this [x] was created or to which the creation of this record is tightly associated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * Include additional information (for example, patient height and weight) that supports the ordering of the medication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSupportingInformation()
    {
        return $this->supportingInformation;
    }

    /**
     * Include additional information (for example, patient height and weight) that supports the ordering of the medication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $supportingInformation
     * @return $this
     */
    public function addSupportingInformation($supportingInformation)
    {
        $this->supportingInformation[] = $supportingInformation;
        return $this;
    }

    /**
     * The date (and perhaps time) when the prescription was initially written or authored on.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getAuthoredOn()
    {
        return $this->authoredOn;
    }

    /**
     * The date (and perhaps time) when the prescription was initially written or authored on.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $authoredOn
     * @return $this
     */
    public function setAuthoredOn($authoredOn)
    {
        $this->authoredOn = $authoredOn;
        return $this;
    }

    /**
     * The individual, organization, or device that initiated the request and has responsibility for its activation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRequester()
    {
        return $this->requester;
    }

    /**
     * The individual, organization, or device that initiated the request and has responsibility for its activation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $requester
     * @return $this
     */
    public function setRequester($requester)
    {
        $this->requester = $requester;
        return $this;
    }

    /**
     * The specified desired performer of the medication treatment (e.g. the performer of the medication administration).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * The specified desired performer of the medication treatment (e.g. the performer of the medication administration).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $performer
     * @return $this
     */
    public function setPerformer($performer)
    {
        $this->performer = $performer;
        return $this;
    }

    /**
     * Indicates the type of performer of the administration of the medication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getPerformerType()
    {
        return $this->performerType;
    }

    /**
     * Indicates the type of performer of the administration of the medication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $performerType
     * @return $this
     */
    public function setPerformerType($performerType)
    {
        $this->performerType = $performerType;
        return $this;
    }

    /**
     * The person who entered the order on behalf of another individual for example in the case of a verbal or a telephone order.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRecorder()
    {
        return $this->recorder;
    }

    /**
     * The person who entered the order on behalf of another individual for example in the case of a verbal or a telephone order.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $recorder
     * @return $this
     */
    public function setRecorder($recorder)
    {
        $this->recorder = $recorder;
        return $this;
    }

    /**
     * The reason or the indication for ordering or not ordering the medication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * The reason or the indication for ordering or not ordering the medication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return $this
     */
    public function addReasonCode($reasonCode)
    {
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * Condition or observation that supports why the medication was ordered.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getReasonReference()
    {
        return $this->reasonReference;
    }

    /**
     * Condition or observation that supports why the medication was ordered.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reasonReference
     * @return $this
     */
    public function addReasonReference($reasonReference)
    {
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * The URL pointing to a protocol, guideline, orderset, or other definition that is adhered to in whole or in part by this MedicationRequest.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getInstantiatesCanonical()
    {
        return $this->instantiatesCanonical;
    }

    /**
     * The URL pointing to a protocol, guideline, orderset, or other definition that is adhered to in whole or in part by this MedicationRequest.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $instantiatesCanonical
     * @return $this
     */
    public function addInstantiatesCanonical($instantiatesCanonical)
    {
        $this->instantiatesCanonical[] = $instantiatesCanonical;
        return $this;
    }

    /**
     * The URL pointing to an externally maintained protocol, guideline, orderset or other definition that is adhered to in whole or in part by this MedicationRequest.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri[]
     */
    public function getInstantiatesUri()
    {
        return $this->instantiatesUri;
    }

    /**
     * The URL pointing to an externally maintained protocol, guideline, orderset or other definition that is adhered to in whole or in part by this MedicationRequest.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $instantiatesUri
     * @return $this
     */
    public function addInstantiatesUri($instantiatesUri)
    {
        $this->instantiatesUri[] = $instantiatesUri;
        return $this;
    }

    /**
     * A plan or request that is fulfilled in whole or in part by this medication request.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getBasedOn()
    {
        return $this->basedOn;
    }

    /**
     * A plan or request that is fulfilled in whole or in part by this medication request.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $basedOn
     * @return $this
     */
    public function addBasedOn($basedOn)
    {
        $this->basedOn[] = $basedOn;
        return $this;
    }

    /**
     * A shared identifier common to all requests that were authorized more or less simultaneously by a single author, representing the identifier of the requisition or prescription.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getGroupIdentifier()
    {
        return $this->groupIdentifier;
    }

    /**
     * A shared identifier common to all requests that were authorized more or less simultaneously by a single author, representing the identifier of the requisition or prescription.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $groupIdentifier
     * @return $this
     */
    public function setGroupIdentifier($groupIdentifier)
    {
        $this->groupIdentifier = $groupIdentifier;
        return $this;
    }

    /**
     * The description of the overall patte3rn of the administration of the medication to the patient.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCourseOfTherapyType()
    {
        return $this->courseOfTherapyType;
    }

    /**
     * The description of the overall patte3rn of the administration of the medication to the patient.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $courseOfTherapyType
     * @return $this
     */
    public function setCourseOfTherapyType($courseOfTherapyType)
    {
        $this->courseOfTherapyType = $courseOfTherapyType;
        return $this;
    }

    /**
     * Insurance plans, coverage extensions, pre-authorizations and/or pre-determinations that may be required for delivering the requested service.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getInsurance()
    {
        return $this->insurance;
    }

    /**
     * Insurance plans, coverage extensions, pre-authorizations and/or pre-determinations that may be required for delivering the requested service.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $insurance
     * @return $this
     */
    public function addInsurance($insurance)
    {
        $this->insurance[] = $insurance;
        return $this;
    }

    /**
     * Extra information about the prescription that could not be conveyed by the other attributes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Extra information about the prescription that could not be conveyed by the other attributes.
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
     * Indicates the specific details for the dispense or medication supply part of a medication request (also known as a Medication Prescription or Medication Order).  Note that this information is not always sent with the order.  There may be in some settings (e.g. hospitals) institutional or system support for completing the dispense details in the pharmacy department.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestDispenseRequest
     */
    public function getDispenseRequest()
    {
        return $this->dispenseRequest;
    }

    /**
     * Indicates the specific details for the dispense or medication supply part of a medication request (also known as a Medication Prescription or Medication Order).  Note that this information is not always sent with the order.  There may be in some settings (e.g. hospitals) institutional or system support for completing the dispense details in the pharmacy department.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestDispenseRequest $dispenseRequest
     * @return $this
     */
    public function setDispenseRequest($dispenseRequest)
    {
        $this->dispenseRequest = $dispenseRequest;
        return $this;
    }

    /**
     * Indicates whether or not substitution can or should be part of the dispense. In some cases, substitution must happen, in other cases substitution must not happen. This block explains the prescriber's intent. If nothing is specified substitution may be done.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestSubstitution
     */
    public function getSubstitution()
    {
        return $this->substitution;
    }

    /**
     * Indicates whether or not substitution can or should be part of the dispense. In some cases, substitution must happen, in other cases substitution must not happen. This block explains the prescriber's intent. If nothing is specified substitution may be done.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestSubstitution $substitution
     * @return $this
     */
    public function setSubstitution($substitution)
    {
        $this->substitution = $substitution;
        return $this;
    }

    /**
     * A link to a resource representing an earlier order related order or prescription.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPriorPrescription()
    {
        return $this->priorPrescription;
    }

    /**
     * A link to a resource representing an earlier order related order or prescription.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $priorPrescription
     * @return $this
     */
    public function setPriorPrescription($priorPrescription)
    {
        $this->priorPrescription = $priorPrescription;
        return $this;
    }

    /**
     * Indicates an actual or potential clinical issue with or between one or more active or proposed clinical actions for a patient; e.g. Drug-drug interaction, duplicate therapy, dosage alert etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getDetectedIssue()
    {
        return $this->detectedIssue;
    }

    /**
     * Indicates an actual or potential clinical issue with or between one or more active or proposed clinical actions for a patient; e.g. Drug-drug interaction, duplicate therapy, dosage alert etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $detectedIssue
     * @return $this
     */
    public function addDetectedIssue($detectedIssue)
    {
        $this->detectedIssue[] = $detectedIssue;
        return $this;
    }

    /**
     * Links to Provenance records for past versions of this resource or fulfilling request or event resources that identify key state transitions or updates that are likely to be relevant to a user looking at the current version of the resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEventHistory()
    {
        return $this->eventHistory;
    }

    /**
     * Links to Provenance records for past versions of this resource or fulfilling request or event resources that identify key state transitions or updates that are likely to be relevant to a user looking at the current version of the resource.
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
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['statusReason'])) {
                $this->setStatusReason($data['statusReason']);
            }
            if (isset($data['intent'])) {
                $this->setIntent($data['intent']);
            }
            if (isset($data['category'])) {
                if (is_array($data['category'])) {
                    foreach ($data['category'] as $d) {
                        $this->addCategory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"category" must be array of objects or null, ' . gettype($data['category']) . ' seen.');
                }
            }
            if (isset($data['priority'])) {
                $this->setPriority($data['priority']);
            }
            if (isset($data['doNotPerform'])) {
                $this->setDoNotPerform($data['doNotPerform']);
            }
            if (isset($data['reportedBoolean'])) {
                $this->setReportedBoolean($data['reportedBoolean']);
            }
            if (isset($data['reportedReference'])) {
                $this->setReportedReference($data['reportedReference']);
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
            if (isset($data['encounter'])) {
                $this->setEncounter($data['encounter']);
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
            if (isset($data['authoredOn'])) {
                $this->setAuthoredOn($data['authoredOn']);
            }
            if (isset($data['requester'])) {
                $this->setRequester($data['requester']);
            }
            if (isset($data['performer'])) {
                $this->setPerformer($data['performer']);
            }
            if (isset($data['performerType'])) {
                $this->setPerformerType($data['performerType']);
            }
            if (isset($data['recorder'])) {
                $this->setRecorder($data['recorder']);
            }
            if (isset($data['reasonCode'])) {
                if (is_array($data['reasonCode'])) {
                    foreach ($data['reasonCode'] as $d) {
                        $this->addReasonCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reasonCode" must be array of objects or null, ' . gettype($data['reasonCode']) . ' seen.');
                }
            }
            if (isset($data['reasonReference'])) {
                if (is_array($data['reasonReference'])) {
                    foreach ($data['reasonReference'] as $d) {
                        $this->addReasonReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reasonReference" must be array of objects or null, ' . gettype($data['reasonReference']) . ' seen.');
                }
            }
            if (isset($data['instantiatesCanonical'])) {
                if (is_array($data['instantiatesCanonical'])) {
                    foreach ($data['instantiatesCanonical'] as $d) {
                        $this->addInstantiatesCanonical($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"instantiatesCanonical" must be array of objects or null, ' . gettype($data['instantiatesCanonical']) . ' seen.');
                }
            }
            if (isset($data['instantiatesUri'])) {
                if (is_array($data['instantiatesUri'])) {
                    foreach ($data['instantiatesUri'] as $d) {
                        $this->addInstantiatesUri($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"instantiatesUri" must be array of objects or null, ' . gettype($data['instantiatesUri']) . ' seen.');
                }
            }
            if (isset($data['basedOn'])) {
                if (is_array($data['basedOn'])) {
                    foreach ($data['basedOn'] as $d) {
                        $this->addBasedOn($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"basedOn" must be array of objects or null, ' . gettype($data['basedOn']) . ' seen.');
                }
            }
            if (isset($data['groupIdentifier'])) {
                $this->setGroupIdentifier($data['groupIdentifier']);
            }
            if (isset($data['courseOfTherapyType'])) {
                $this->setCourseOfTherapyType($data['courseOfTherapyType']);
            }
            if (isset($data['insurance'])) {
                if (is_array($data['insurance'])) {
                    foreach ($data['insurance'] as $d) {
                        $this->addInsurance($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"insurance" must be array of objects or null, ' . gettype($data['insurance']) . ' seen.');
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
            if (isset($data['dispenseRequest'])) {
                $this->setDispenseRequest($data['dispenseRequest']);
            }
            if (isset($data['substitution'])) {
                $this->setSubstitution($data['substitution']);
            }
            if (isset($data['priorPrescription'])) {
                $this->setPriorPrescription($data['priorPrescription']);
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->statusReason)) {
            $json['statusReason'] = $this->statusReason;
        }
        if (isset($this->intent)) {
            $json['intent'] = $this->intent;
        }
        if (0 < count($this->category)) {
            $json['category'] = [];
            foreach ($this->category as $category) {
                $json['category'][] = $category;
            }
        }
        if (isset($this->priority)) {
            $json['priority'] = $this->priority;
        }
        if (isset($this->doNotPerform)) {
            $json['doNotPerform'] = $this->doNotPerform;
        }
        if (isset($this->reportedBoolean)) {
            $json['reportedBoolean'] = $this->reportedBoolean;
        }
        if (isset($this->reportedReference)) {
            $json['reportedReference'] = $this->reportedReference;
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
        if (isset($this->encounter)) {
            $json['encounter'] = $this->encounter;
        }
        if (0 < count($this->supportingInformation)) {
            $json['supportingInformation'] = [];
            foreach ($this->supportingInformation as $supportingInformation) {
                $json['supportingInformation'][] = $supportingInformation;
            }
        }
        if (isset($this->authoredOn)) {
            $json['authoredOn'] = $this->authoredOn;
        }
        if (isset($this->requester)) {
            $json['requester'] = $this->requester;
        }
        if (isset($this->performer)) {
            $json['performer'] = $this->performer;
        }
        if (isset($this->performerType)) {
            $json['performerType'] = $this->performerType;
        }
        if (isset($this->recorder)) {
            $json['recorder'] = $this->recorder;
        }
        if (0 < count($this->reasonCode)) {
            $json['reasonCode'] = [];
            foreach ($this->reasonCode as $reasonCode) {
                $json['reasonCode'][] = $reasonCode;
            }
        }
        if (0 < count($this->reasonReference)) {
            $json['reasonReference'] = [];
            foreach ($this->reasonReference as $reasonReference) {
                $json['reasonReference'][] = $reasonReference;
            }
        }
        if (0 < count($this->instantiatesCanonical)) {
            $json['instantiatesCanonical'] = [];
            foreach ($this->instantiatesCanonical as $instantiatesCanonical) {
                $json['instantiatesCanonical'][] = $instantiatesCanonical;
            }
        }
        if (0 < count($this->instantiatesUri)) {
            $json['instantiatesUri'] = [];
            foreach ($this->instantiatesUri as $instantiatesUri) {
                $json['instantiatesUri'][] = $instantiatesUri;
            }
        }
        if (0 < count($this->basedOn)) {
            $json['basedOn'] = [];
            foreach ($this->basedOn as $basedOn) {
                $json['basedOn'][] = $basedOn;
            }
        }
        if (isset($this->groupIdentifier)) {
            $json['groupIdentifier'] = $this->groupIdentifier;
        }
        if (isset($this->courseOfTherapyType)) {
            $json['courseOfTherapyType'] = $this->courseOfTherapyType;
        }
        if (0 < count($this->insurance)) {
            $json['insurance'] = [];
            foreach ($this->insurance as $insurance) {
                $json['insurance'][] = $insurance;
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
        if (isset($this->dispenseRequest)) {
            $json['dispenseRequest'] = $this->dispenseRequest;
        }
        if (isset($this->substitution)) {
            $json['substitution'] = $this->substitution;
        }
        if (isset($this->priorPrescription)) {
            $json['priorPrescription'] = $this->priorPrescription;
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
            $sxe = new \SimpleXMLElement('<MedicationRequest xmlns="http://hl7.org/fhir"></MedicationRequest>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->statusReason)) {
            $this->statusReason->xmlSerialize(true, $sxe->addChild('statusReason'));
        }
        if (isset($this->intent)) {
            $this->intent->xmlSerialize(true, $sxe->addChild('intent'));
        }
        if (0 < count($this->category)) {
            foreach ($this->category as $category) {
                $category->xmlSerialize(true, $sxe->addChild('category'));
            }
        }
        if (isset($this->priority)) {
            $this->priority->xmlSerialize(true, $sxe->addChild('priority'));
        }
        if (isset($this->doNotPerform)) {
            $this->doNotPerform->xmlSerialize(true, $sxe->addChild('doNotPerform'));
        }
        if (isset($this->reportedBoolean)) {
            $this->reportedBoolean->xmlSerialize(true, $sxe->addChild('reportedBoolean'));
        }
        if (isset($this->reportedReference)) {
            $this->reportedReference->xmlSerialize(true, $sxe->addChild('reportedReference'));
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
        if (isset($this->encounter)) {
            $this->encounter->xmlSerialize(true, $sxe->addChild('encounter'));
        }
        if (0 < count($this->supportingInformation)) {
            foreach ($this->supportingInformation as $supportingInformation) {
                $supportingInformation->xmlSerialize(true, $sxe->addChild('supportingInformation'));
            }
        }
        if (isset($this->authoredOn)) {
            $this->authoredOn->xmlSerialize(true, $sxe->addChild('authoredOn'));
        }
        if (isset($this->requester)) {
            $this->requester->xmlSerialize(true, $sxe->addChild('requester'));
        }
        if (isset($this->performer)) {
            $this->performer->xmlSerialize(true, $sxe->addChild('performer'));
        }
        if (isset($this->performerType)) {
            $this->performerType->xmlSerialize(true, $sxe->addChild('performerType'));
        }
        if (isset($this->recorder)) {
            $this->recorder->xmlSerialize(true, $sxe->addChild('recorder'));
        }
        if (0 < count($this->reasonCode)) {
            foreach ($this->reasonCode as $reasonCode) {
                $reasonCode->xmlSerialize(true, $sxe->addChild('reasonCode'));
            }
        }
        if (0 < count($this->reasonReference)) {
            foreach ($this->reasonReference as $reasonReference) {
                $reasonReference->xmlSerialize(true, $sxe->addChild('reasonReference'));
            }
        }
        if (0 < count($this->instantiatesCanonical)) {
            foreach ($this->instantiatesCanonical as $instantiatesCanonical) {
                $instantiatesCanonical->xmlSerialize(true, $sxe->addChild('instantiatesCanonical'));
            }
        }
        if (0 < count($this->instantiatesUri)) {
            foreach ($this->instantiatesUri as $instantiatesUri) {
                $instantiatesUri->xmlSerialize(true, $sxe->addChild('instantiatesUri'));
            }
        }
        if (0 < count($this->basedOn)) {
            foreach ($this->basedOn as $basedOn) {
                $basedOn->xmlSerialize(true, $sxe->addChild('basedOn'));
            }
        }
        if (isset($this->groupIdentifier)) {
            $this->groupIdentifier->xmlSerialize(true, $sxe->addChild('groupIdentifier'));
        }
        if (isset($this->courseOfTherapyType)) {
            $this->courseOfTherapyType->xmlSerialize(true, $sxe->addChild('courseOfTherapyType'));
        }
        if (0 < count($this->insurance)) {
            foreach ($this->insurance as $insurance) {
                $insurance->xmlSerialize(true, $sxe->addChild('insurance'));
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
        if (isset($this->dispenseRequest)) {
            $this->dispenseRequest->xmlSerialize(true, $sxe->addChild('dispenseRequest'));
        }
        if (isset($this->substitution)) {
            $this->substitution->xmlSerialize(true, $sxe->addChild('substitution'));
        }
        if (isset($this->priorPrescription)) {
            $this->priorPrescription->xmlSerialize(true, $sxe->addChild('priorPrescription'));
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
