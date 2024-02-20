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
 * An interaction between a patient and healthcare provider(s) for the purpose of providing healthcare service(s) or assessing the health status of a patient.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIREncounter extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifier(s) by which this encounter is known.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * planned | arrived | triaged | in-progress | onleave | finished | cancelled +.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIREncounterStatus
     */
    public $status = null;

    /**
     * The status history permits the encounter resource to contain the status history without needing to read through the historical versions of the resource, or even have the server store them.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterStatusHistory[]
     */
    public $statusHistory = [];

    /**
     * Concepts representing classification of patient encounter such as ambulatory (outpatient), inpatient, emergency, home health or others due to local variations.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $class = null;

    /**
     * The class history permits the tracking of the encounters transitions without needing to go  through the resource history.  This would be used for a case where an admission starts of as an emergency encounter, then transitions into an inpatient scenario. Doing this and not restarting a new encounter ensures that any lab/diagnostic results can more easily follow the patient and not require re-processing and not get lost or cancelled during a kind of discharge from emergency to inpatient.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterClassHistory[]
     */
    public $classHistory = [];

    /**
     * Specific type of encounter (e.g. e-mail consultation, surgical day-care, skilled nursing, rehabilitation).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $type = [];

    /**
     * Broad categorization of the service that is to be provided (e.g. cardiology).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $serviceType = null;

    /**
     * Indicates the urgency of the encounter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $priority = null;

    /**
     * The patient or group present at the encounter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * Where a specific encounter should be classified as a part of a specific episode(s) of care this field should be used. This association can facilitate grouping of related encounters together for a specific purpose, such as government reporting, issue tracking, association via a common problem.  The association is recorded on the encounter as these are typically created after the episode of care and grouped on entry rather than editing the episode of care to append another encounter to it (the episode of care could span years).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $episodeOfCare = [];

    /**
     * The request this encounter satisfies (e.g. incoming referral or procedure request).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $basedOn = [];

    /**
     * The list of people responsible for providing the service.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterParticipant[]
     */
    public $participant = [];

    /**
     * The appointment that scheduled this encounter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $appointment = [];

    /**
     * The start and end time of the encounter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * Quantity of time the encounter lasted. This excludes the time during leaves of absence.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $length = null;

    /**
     * Reason the encounter takes place, expressed as a code. For admissions, this can be used for a coded admission diagnosis.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $reasonCode = [];

    /**
     * Reason the encounter takes place, expressed as a code. For admissions, this can be used for a coded admission diagnosis.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $reasonReference = [];

    /**
     * The list of diagnosis relevant to this encounter.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterDiagnosis[]
     */
    public $diagnosis = [];

    /**
     * The set of accounts that may be used for billing for this Encounter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $account = [];

    /**
     * Details about the admission to a healthcare service.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterHospitalization
     */
    public $hospitalization = null;

    /**
     * List of locations where  the patient has been during this encounter.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterLocation[]
     */
    public $location = [];

    /**
     * The organization that is primarily responsible for this Encounter's services. This MAY be the same as the organization on the Patient record, however it could be different, such as if the actor performing the services was from an external organization (which may be billed seperately) for an external consultation.  Refer to the example bundle showing an abbreviated set of Encounters for a colonoscopy.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $serviceProvider = null;

    /**
     * Another Encounter of which this encounter is a part of (administratively or in time).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $partOf = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Encounter';

    /**
     * Identifier(s) by which this encounter is known.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier(s) by which this encounter is known.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * planned | arrived | triaged | in-progress | onleave | finished | cancelled +.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIREncounterStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * planned | arrived | triaged | in-progress | onleave | finished | cancelled +.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIREncounterStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The status history permits the encounter resource to contain the status history without needing to read through the historical versions of the resource, or even have the server store them.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterStatusHistory[]
     */
    public function getStatusHistory()
    {
        return $this->statusHistory;
    }

    /**
     * The status history permits the encounter resource to contain the status history without needing to read through the historical versions of the resource, or even have the server store them.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterStatusHistory $statusHistory
     * @return $this
     */
    public function addStatusHistory($statusHistory)
    {
        $this->statusHistory[] = $statusHistory;
        return $this;
    }

    /**
     * Concepts representing classification of patient encounter such as ambulatory (outpatient), inpatient, emergency, home health or others due to local variations.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Concepts representing classification of patient encounter such as ambulatory (outpatient), inpatient, emergency, home health or others due to local variations.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * The class history permits the tracking of the encounters transitions without needing to go  through the resource history.  This would be used for a case where an admission starts of as an emergency encounter, then transitions into an inpatient scenario. Doing this and not restarting a new encounter ensures that any lab/diagnostic results can more easily follow the patient and not require re-processing and not get lost or cancelled during a kind of discharge from emergency to inpatient.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterClassHistory[]
     */
    public function getClassHistory()
    {
        return $this->classHistory;
    }

    /**
     * The class history permits the tracking of the encounters transitions without needing to go  through the resource history.  This would be used for a case where an admission starts of as an emergency encounter, then transitions into an inpatient scenario. Doing this and not restarting a new encounter ensures that any lab/diagnostic results can more easily follow the patient and not require re-processing and not get lost or cancelled during a kind of discharge from emergency to inpatient.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterClassHistory $classHistory
     * @return $this
     */
    public function addClassHistory($classHistory)
    {
        $this->classHistory[] = $classHistory;
        return $this;
    }

    /**
     * Specific type of encounter (e.g. e-mail consultation, surgical day-care, skilled nursing, rehabilitation).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Specific type of encounter (e.g. e-mail consultation, surgical day-care, skilled nursing, rehabilitation).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * Broad categorization of the service that is to be provided (e.g. cardiology).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * Broad categorization of the service that is to be provided (e.g. cardiology).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $serviceType
     * @return $this
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;
        return $this;
    }

    /**
     * Indicates the urgency of the encounter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Indicates the urgency of the encounter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * The patient or group present at the encounter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The patient or group present at the encounter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Where a specific encounter should be classified as a part of a specific episode(s) of care this field should be used. This association can facilitate grouping of related encounters together for a specific purpose, such as government reporting, issue tracking, association via a common problem.  The association is recorded on the encounter as these are typically created after the episode of care and grouped on entry rather than editing the episode of care to append another encounter to it (the episode of care could span years).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEpisodeOfCare()
    {
        return $this->episodeOfCare;
    }

    /**
     * Where a specific encounter should be classified as a part of a specific episode(s) of care this field should be used. This association can facilitate grouping of related encounters together for a specific purpose, such as government reporting, issue tracking, association via a common problem.  The association is recorded on the encounter as these are typically created after the episode of care and grouped on entry rather than editing the episode of care to append another encounter to it (the episode of care could span years).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $episodeOfCare
     * @return $this
     */
    public function addEpisodeOfCare($episodeOfCare)
    {
        $this->episodeOfCare[] = $episodeOfCare;
        return $this;
    }

    /**
     * The request this encounter satisfies (e.g. incoming referral or procedure request).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getBasedOn()
    {
        return $this->basedOn;
    }

    /**
     * The request this encounter satisfies (e.g. incoming referral or procedure request).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $basedOn
     * @return $this
     */
    public function addBasedOn($basedOn)
    {
        $this->basedOn[] = $basedOn;
        return $this;
    }

    /**
     * The list of people responsible for providing the service.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterParticipant[]
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * The list of people responsible for providing the service.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterParticipant $participant
     * @return $this
     */
    public function addParticipant($participant)
    {
        $this->participant[] = $participant;
        return $this;
    }

    /**
     * The appointment that scheduled this encounter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAppointment()
    {
        return $this->appointment;
    }

    /**
     * The appointment that scheduled this encounter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $appointment
     * @return $this
     */
    public function addAppointment($appointment)
    {
        $this->appointment[] = $appointment;
        return $this;
    }

    /**
     * The start and end time of the encounter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The start and end time of the encounter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * Quantity of time the encounter lasted. This excludes the time during leaves of absence.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Quantity of time the encounter lasted. This excludes the time during leaves of absence.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $length
     * @return $this
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * Reason the encounter takes place, expressed as a code. For admissions, this can be used for a coded admission diagnosis.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * Reason the encounter takes place, expressed as a code. For admissions, this can be used for a coded admission diagnosis.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return $this
     */
    public function addReasonCode($reasonCode)
    {
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * Reason the encounter takes place, expressed as a code. For admissions, this can be used for a coded admission diagnosis.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getReasonReference()
    {
        return $this->reasonReference;
    }

    /**
     * Reason the encounter takes place, expressed as a code. For admissions, this can be used for a coded admission diagnosis.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reasonReference
     * @return $this
     */
    public function addReasonReference($reasonReference)
    {
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * The list of diagnosis relevant to this encounter.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterDiagnosis[]
     */
    public function getDiagnosis()
    {
        return $this->diagnosis;
    }

    /**
     * The list of diagnosis relevant to this encounter.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterDiagnosis $diagnosis
     * @return $this
     */
    public function addDiagnosis($diagnosis)
    {
        $this->diagnosis[] = $diagnosis;
        return $this;
    }

    /**
     * The set of accounts that may be used for billing for this Encounter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * The set of accounts that may be used for billing for this Encounter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $account
     * @return $this
     */
    public function addAccount($account)
    {
        $this->account[] = $account;
        return $this;
    }

    /**
     * Details about the admission to a healthcare service.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterHospitalization
     */
    public function getHospitalization()
    {
        return $this->hospitalization;
    }

    /**
     * Details about the admission to a healthcare service.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterHospitalization $hospitalization
     * @return $this
     */
    public function setHospitalization($hospitalization)
    {
        $this->hospitalization = $hospitalization;
        return $this;
    }

    /**
     * List of locations where  the patient has been during this encounter.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterLocation[]
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * List of locations where  the patient has been during this encounter.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterLocation $location
     * @return $this
     */
    public function addLocation($location)
    {
        $this->location[] = $location;
        return $this;
    }

    /**
     * The organization that is primarily responsible for this Encounter's services. This MAY be the same as the organization on the Patient record, however it could be different, such as if the actor performing the services was from an external organization (which may be billed seperately) for an external consultation.  Refer to the example bundle showing an abbreviated set of Encounters for a colonoscopy.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getServiceProvider()
    {
        return $this->serviceProvider;
    }

    /**
     * The organization that is primarily responsible for this Encounter's services. This MAY be the same as the organization on the Patient record, however it could be different, such as if the actor performing the services was from an external organization (which may be billed seperately) for an external consultation.  Refer to the example bundle showing an abbreviated set of Encounters for a colonoscopy.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $serviceProvider
     * @return $this
     */
    public function setServiceProvider($serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
        return $this;
    }

    /**
     * Another Encounter of which this encounter is a part of (administratively or in time).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * Another Encounter of which this encounter is a part of (administratively or in time).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $partOf
     * @return $this
     */
    public function setPartOf($partOf)
    {
        $this->partOf = $partOf;
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
            if (isset($data['statusHistory'])) {
                if (is_array($data['statusHistory'])) {
                    foreach ($data['statusHistory'] as $d) {
                        $this->addStatusHistory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"statusHistory" must be array of objects or null, ' . gettype($data['statusHistory']) . ' seen.');
                }
            }
            if (isset($data['class'])) {
                $this->setClass($data['class']);
            }
            if (isset($data['classHistory'])) {
                if (is_array($data['classHistory'])) {
                    foreach ($data['classHistory'] as $d) {
                        $this->addClassHistory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"classHistory" must be array of objects or null, ' . gettype($data['classHistory']) . ' seen.');
                }
            }
            if (isset($data['type'])) {
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, ' . gettype($data['type']) . ' seen.');
                }
            }
            if (isset($data['serviceType'])) {
                $this->setServiceType($data['serviceType']);
            }
            if (isset($data['priority'])) {
                $this->setPriority($data['priority']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['episodeOfCare'])) {
                if (is_array($data['episodeOfCare'])) {
                    foreach ($data['episodeOfCare'] as $d) {
                        $this->addEpisodeOfCare($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"episodeOfCare" must be array of objects or null, ' . gettype($data['episodeOfCare']) . ' seen.');
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
            if (isset($data['participant'])) {
                if (is_array($data['participant'])) {
                    foreach ($data['participant'] as $d) {
                        $this->addParticipant($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"participant" must be array of objects or null, ' . gettype($data['participant']) . ' seen.');
                }
            }
            if (isset($data['appointment'])) {
                if (is_array($data['appointment'])) {
                    foreach ($data['appointment'] as $d) {
                        $this->addAppointment($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"appointment" must be array of objects or null, ' . gettype($data['appointment']) . ' seen.');
                }
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['length'])) {
                $this->setLength($data['length']);
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
            if (isset($data['diagnosis'])) {
                if (is_array($data['diagnosis'])) {
                    foreach ($data['diagnosis'] as $d) {
                        $this->addDiagnosis($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"diagnosis" must be array of objects or null, ' . gettype($data['diagnosis']) . ' seen.');
                }
            }
            if (isset($data['account'])) {
                if (is_array($data['account'])) {
                    foreach ($data['account'] as $d) {
                        $this->addAccount($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"account" must be array of objects or null, ' . gettype($data['account']) . ' seen.');
                }
            }
            if (isset($data['hospitalization'])) {
                $this->setHospitalization($data['hospitalization']);
            }
            if (isset($data['location'])) {
                if (is_array($data['location'])) {
                    foreach ($data['location'] as $d) {
                        $this->addLocation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"location" must be array of objects or null, ' . gettype($data['location']) . ' seen.');
                }
            }
            if (isset($data['serviceProvider'])) {
                $this->setServiceProvider($data['serviceProvider']);
            }
            if (isset($data['partOf'])) {
                $this->setPartOf($data['partOf']);
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
    public function jsonSerialize(): mixed
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
        if (0 < count($this->statusHistory)) {
            $json['statusHistory'] = [];
            foreach ($this->statusHistory as $statusHistory) {
                $json['statusHistory'][] = $statusHistory;
            }
        }
        if (isset($this->class)) {
            $json['class'] = $this->class;
        }
        if (0 < count($this->classHistory)) {
            $json['classHistory'] = [];
            foreach ($this->classHistory as $classHistory) {
                $json['classHistory'][] = $classHistory;
            }
        }
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
        }
        if (isset($this->serviceType)) {
            $json['serviceType'] = $this->serviceType;
        }
        if (isset($this->priority)) {
            $json['priority'] = $this->priority;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (0 < count($this->episodeOfCare)) {
            $json['episodeOfCare'] = [];
            foreach ($this->episodeOfCare as $episodeOfCare) {
                $json['episodeOfCare'][] = $episodeOfCare;
            }
        }
        if (0 < count($this->basedOn)) {
            $json['basedOn'] = [];
            foreach ($this->basedOn as $basedOn) {
                $json['basedOn'][] = $basedOn;
            }
        }
        if (0 < count($this->participant)) {
            $json['participant'] = [];
            foreach ($this->participant as $participant) {
                $json['participant'][] = $participant;
            }
        }
        if (0 < count($this->appointment)) {
            $json['appointment'] = [];
            foreach ($this->appointment as $appointment) {
                $json['appointment'][] = $appointment;
            }
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->length)) {
            $json['length'] = $this->length;
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
        if (0 < count($this->diagnosis)) {
            $json['diagnosis'] = [];
            foreach ($this->diagnosis as $diagnosis) {
                $json['diagnosis'][] = $diagnosis;
            }
        }
        if (0 < count($this->account)) {
            $json['account'] = [];
            foreach ($this->account as $account) {
                $json['account'][] = $account;
            }
        }
        if (isset($this->hospitalization)) {
            $json['hospitalization'] = $this->hospitalization;
        }
        if (0 < count($this->location)) {
            $json['location'] = [];
            foreach ($this->location as $location) {
                $json['location'][] = $location;
            }
        }
        if (isset($this->serviceProvider)) {
            $json['serviceProvider'] = $this->serviceProvider;
        }
        if (isset($this->partOf)) {
            $json['partOf'] = $this->partOf;
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
            $sxe = new \SimpleXMLElement('<Encounter xmlns="http://hl7.org/fhir"></Encounter>');
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
        if (0 < count($this->statusHistory)) {
            foreach ($this->statusHistory as $statusHistory) {
                $statusHistory->xmlSerialize(true, $sxe->addChild('statusHistory'));
            }
        }
        if (isset($this->class)) {
            $this->class->xmlSerialize(true, $sxe->addChild('class'));
        }
        if (0 < count($this->classHistory)) {
            foreach ($this->classHistory as $classHistory) {
                $classHistory->xmlSerialize(true, $sxe->addChild('classHistory'));
            }
        }
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (isset($this->serviceType)) {
            $this->serviceType->xmlSerialize(true, $sxe->addChild('serviceType'));
        }
        if (isset($this->priority)) {
            $this->priority->xmlSerialize(true, $sxe->addChild('priority'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (0 < count($this->episodeOfCare)) {
            foreach ($this->episodeOfCare as $episodeOfCare) {
                $episodeOfCare->xmlSerialize(true, $sxe->addChild('episodeOfCare'));
            }
        }
        if (0 < count($this->basedOn)) {
            foreach ($this->basedOn as $basedOn) {
                $basedOn->xmlSerialize(true, $sxe->addChild('basedOn'));
            }
        }
        if (0 < count($this->participant)) {
            foreach ($this->participant as $participant) {
                $participant->xmlSerialize(true, $sxe->addChild('participant'));
            }
        }
        if (0 < count($this->appointment)) {
            foreach ($this->appointment as $appointment) {
                $appointment->xmlSerialize(true, $sxe->addChild('appointment'));
            }
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->length)) {
            $this->length->xmlSerialize(true, $sxe->addChild('length'));
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
        if (0 < count($this->diagnosis)) {
            foreach ($this->diagnosis as $diagnosis) {
                $diagnosis->xmlSerialize(true, $sxe->addChild('diagnosis'));
            }
        }
        if (0 < count($this->account)) {
            foreach ($this->account as $account) {
                $account->xmlSerialize(true, $sxe->addChild('account'));
            }
        }
        if (isset($this->hospitalization)) {
            $this->hospitalization->xmlSerialize(true, $sxe->addChild('hospitalization'));
        }
        if (0 < count($this->location)) {
            foreach ($this->location as $location) {
                $location->xmlSerialize(true, $sxe->addChild('location'));
            }
        }
        if (isset($this->serviceProvider)) {
            $this->serviceProvider->xmlSerialize(true, $sxe->addChild('serviceProvider'));
        }
        if (isset($this->partOf)) {
            $this->partOf->xmlSerialize(true, $sxe->addChild('partOf'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
