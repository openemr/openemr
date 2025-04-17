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
 * Describes the event of a patient being administered a vaccine or a record of an immunization as reported by a patient, a clinician or another party.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRImmunization extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A unique identifier assigned to this immunization record.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Indicates the current status of the immunization event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationStatusCodes
     */
    public $status = null;

    /**
     * Indicates the reason the immunization event was not performed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $statusReason = null;

    /**
     * Vaccine that was administered or was to be administered.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $vaccineCode = null;

    /**
     * The patient who either received or did not receive the immunization.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * The visit or admission or other contact between patient and health care provider the immunization was performed as part of.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $occurrenceDateTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $occurrenceString = null;

    /**
     * The date the occurrence of the immunization was first captured in the record - potentially significantly after the occurrence of the event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $recorded = null;

    /**
     * An indication that the content of the record is based on information from the person who administered the vaccine. This reflects the context under which the data was originally recorded.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $primarySource = null;

    /**
     * The source of the data when the report of the immunization event is not based on information from the person who administered the vaccine.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $reportOrigin = null;

    /**
     * The service delivery location where the vaccine administration occurred.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $location = null;

    /**
     * Name of vaccine manufacturer.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $manufacturer = null;

    /**
     * Lot number of the  vaccine product.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $lotNumber = null;

    /**
     * Date vaccine batch expires.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $expirationDate = null;

    /**
     * Body site where vaccine was administered.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $site = null;

    /**
     * The path by which the vaccine product is taken into the body.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $route = null;

    /**
     * The quantity of vaccine product that was administered.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $doseQuantity = null;

    /**
     * Indicates who performed the immunization event.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationPerformer[]
     */
    public $performer = [];

    /**
     * Extra information about the immunization that is not conveyed by the other attributes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * Reasons why the vaccine was administered.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $reasonCode = [];

    /**
     * Condition, Observation or DiagnosticReport that supports why the immunization was administered.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $reasonReference = [];

    /**
     * Indication if a dose is considered to be subpotent. By default, a dose should be considered to be potent.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $isSubpotent = null;

    /**
     * Reason why a dose is considered to be subpotent.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $subpotentReason = [];

    /**
     * Educational material presented to the patient (or guardian) at the time of vaccine administration.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationEducation[]
     */
    public $education = [];

    /**
     * Indicates a patient's eligibility for a funding program.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $programEligibility = [];

    /**
     * Indicates the source of the vaccine actually administered. This may be different than the patient eligibility (e.g. the patient may be eligible for a publically purchased vaccine but due to inventory issues, vaccine purchased with private funds was actually administered).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $fundingSource = null;

    /**
     * Categorical data indicating that an adverse event is associated in time to an immunization.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationReaction[]
     */
    public $reaction = [];

    /**
     * The protocol (set of recommendations) being followed by the provider who administered the dose.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationProtocolApplied[]
     */
    public $protocolApplied = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Immunization';

    /**
     * A unique identifier assigned to this immunization record.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A unique identifier assigned to this immunization record.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Indicates the current status of the immunization event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationStatusCodes
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Indicates the current status of the immunization event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationStatusCodes $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Indicates the reason the immunization event was not performed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStatusReason()
    {
        return $this->statusReason;
    }

    /**
     * Indicates the reason the immunization event was not performed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $statusReason
     * @return $this
     */
    public function setStatusReason($statusReason)
    {
        $this->statusReason = $statusReason;
        return $this;
    }

    /**
     * Vaccine that was administered or was to be administered.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getVaccineCode()
    {
        return $this->vaccineCode;
    }

    /**
     * Vaccine that was administered or was to be administered.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $vaccineCode
     * @return $this
     */
    public function setVaccineCode($vaccineCode)
    {
        $this->vaccineCode = $vaccineCode;
        return $this;
    }

    /**
     * The patient who either received or did not receive the immunization.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * The patient who either received or did not receive the immunization.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * The visit or admission or other contact between patient and health care provider the immunization was performed as part of.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * The visit or admission or other contact between patient and health care provider the immunization was performed as part of.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getOccurrenceDateTime()
    {
        return $this->occurrenceDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $occurrenceDateTime
     * @return $this
     */
    public function setOccurrenceDateTime($occurrenceDateTime)
    {
        $this->occurrenceDateTime = $occurrenceDateTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getOccurrenceString()
    {
        return $this->occurrenceString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $occurrenceString
     * @return $this
     */
    public function setOccurrenceString($occurrenceString)
    {
        $this->occurrenceString = $occurrenceString;
        return $this;
    }

    /**
     * The date the occurrence of the immunization was first captured in the record - potentially significantly after the occurrence of the event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getRecorded()
    {
        return $this->recorded;
    }

    /**
     * The date the occurrence of the immunization was first captured in the record - potentially significantly after the occurrence of the event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $recorded
     * @return $this
     */
    public function setRecorded($recorded)
    {
        $this->recorded = $recorded;
        return $this;
    }

    /**
     * An indication that the content of the record is based on information from the person who administered the vaccine. This reflects the context under which the data was originally recorded.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getPrimarySource()
    {
        return $this->primarySource;
    }

    /**
     * An indication that the content of the record is based on information from the person who administered the vaccine. This reflects the context under which the data was originally recorded.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $primarySource
     * @return $this
     */
    public function setPrimarySource($primarySource)
    {
        $this->primarySource = $primarySource;
        return $this;
    }

    /**
     * The source of the data when the report of the immunization event is not based on information from the person who administered the vaccine.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getReportOrigin()
    {
        return $this->reportOrigin;
    }

    /**
     * The source of the data when the report of the immunization event is not based on information from the person who administered the vaccine.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $reportOrigin
     * @return $this
     */
    public function setReportOrigin($reportOrigin)
    {
        $this->reportOrigin = $reportOrigin;
        return $this;
    }

    /**
     * The service delivery location where the vaccine administration occurred.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The service delivery location where the vaccine administration occurred.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Name of vaccine manufacturer.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Name of vaccine manufacturer.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $manufacturer
     * @return $this
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * Lot number of the  vaccine product.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getLotNumber()
    {
        return $this->lotNumber;
    }

    /**
     * Lot number of the  vaccine product.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $lotNumber
     * @return $this
     */
    public function setLotNumber($lotNumber)
    {
        $this->lotNumber = $lotNumber;
        return $this;
    }

    /**
     * Date vaccine batch expires.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Date vaccine batch expires.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $expirationDate
     * @return $this
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    /**
     * Body site where vaccine was administered.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Body site where vaccine was administered.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $site
     * @return $this
     */
    public function setSite($site)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * The path by which the vaccine product is taken into the body.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * The path by which the vaccine product is taken into the body.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $route
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * The quantity of vaccine product that was administered.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getDoseQuantity()
    {
        return $this->doseQuantity;
    }

    /**
     * The quantity of vaccine product that was administered.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $doseQuantity
     * @return $this
     */
    public function setDoseQuantity($doseQuantity)
    {
        $this->doseQuantity = $doseQuantity;
        return $this;
    }

    /**
     * Indicates who performed the immunization event.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationPerformer[]
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * Indicates who performed the immunization event.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationPerformer $performer
     * @return $this
     */
    public function addPerformer($performer)
    {
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * Extra information about the immunization that is not conveyed by the other attributes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Extra information about the immunization that is not conveyed by the other attributes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * Reasons why the vaccine was administered.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * Reasons why the vaccine was administered.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return $this
     */
    public function addReasonCode($reasonCode)
    {
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * Condition, Observation or DiagnosticReport that supports why the immunization was administered.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getReasonReference()
    {
        return $this->reasonReference;
    }

    /**
     * Condition, Observation or DiagnosticReport that supports why the immunization was administered.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reasonReference
     * @return $this
     */
    public function addReasonReference($reasonReference)
    {
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * Indication if a dose is considered to be subpotent. By default, a dose should be considered to be potent.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getIsSubpotent()
    {
        return $this->isSubpotent;
    }

    /**
     * Indication if a dose is considered to be subpotent. By default, a dose should be considered to be potent.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $isSubpotent
     * @return $this
     */
    public function setIsSubpotent($isSubpotent)
    {
        $this->isSubpotent = $isSubpotent;
        return $this;
    }

    /**
     * Reason why a dose is considered to be subpotent.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSubpotentReason()
    {
        return $this->subpotentReason;
    }

    /**
     * Reason why a dose is considered to be subpotent.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $subpotentReason
     * @return $this
     */
    public function addSubpotentReason($subpotentReason)
    {
        $this->subpotentReason[] = $subpotentReason;
        return $this;
    }

    /**
     * Educational material presented to the patient (or guardian) at the time of vaccine administration.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationEducation[]
     */
    public function getEducation()
    {
        return $this->education;
    }

    /**
     * Educational material presented to the patient (or guardian) at the time of vaccine administration.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationEducation $education
     * @return $this
     */
    public function addEducation($education)
    {
        $this->education[] = $education;
        return $this;
    }

    /**
     * Indicates a patient's eligibility for a funding program.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getProgramEligibility()
    {
        return $this->programEligibility;
    }

    /**
     * Indicates a patient's eligibility for a funding program.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $programEligibility
     * @return $this
     */
    public function addProgramEligibility($programEligibility)
    {
        $this->programEligibility[] = $programEligibility;
        return $this;
    }

    /**
     * Indicates the source of the vaccine actually administered. This may be different than the patient eligibility (e.g. the patient may be eligible for a publically purchased vaccine but due to inventory issues, vaccine purchased with private funds was actually administered).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getFundingSource()
    {
        return $this->fundingSource;
    }

    /**
     * Indicates the source of the vaccine actually administered. This may be different than the patient eligibility (e.g. the patient may be eligible for a publically purchased vaccine but due to inventory issues, vaccine purchased with private funds was actually administered).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $fundingSource
     * @return $this
     */
    public function setFundingSource($fundingSource)
    {
        $this->fundingSource = $fundingSource;
        return $this;
    }

    /**
     * Categorical data indicating that an adverse event is associated in time to an immunization.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationReaction[]
     */
    public function getReaction()
    {
        return $this->reaction;
    }

    /**
     * Categorical data indicating that an adverse event is associated in time to an immunization.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationReaction $reaction
     * @return $this
     */
    public function addReaction($reaction)
    {
        $this->reaction[] = $reaction;
        return $this;
    }

    /**
     * The protocol (set of recommendations) being followed by the provider who administered the dose.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationProtocolApplied[]
     */
    public function getProtocolApplied()
    {
        return $this->protocolApplied;
    }

    /**
     * The protocol (set of recommendations) being followed by the provider who administered the dose.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization\FHIRImmunizationProtocolApplied $protocolApplied
     * @return $this
     */
    public function addProtocolApplied($protocolApplied)
    {
        $this->protocolApplied[] = $protocolApplied;
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
            if (isset($data['vaccineCode'])) {
                $this->setVaccineCode($data['vaccineCode']);
            }
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
            }
            if (isset($data['encounter'])) {
                $this->setEncounter($data['encounter']);
            }
            if (isset($data['occurrenceDateTime'])) {
                $this->setOccurrenceDateTime($data['occurrenceDateTime']);
            }
            if (isset($data['occurrenceString'])) {
                $this->setOccurrenceString($data['occurrenceString']);
            }
            if (isset($data['recorded'])) {
                $this->setRecorded($data['recorded']);
            }
            if (isset($data['primarySource'])) {
                $this->setPrimarySource($data['primarySource']);
            }
            if (isset($data['reportOrigin'])) {
                $this->setReportOrigin($data['reportOrigin']);
            }
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['manufacturer'])) {
                $this->setManufacturer($data['manufacturer']);
            }
            if (isset($data['lotNumber'])) {
                $this->setLotNumber($data['lotNumber']);
            }
            if (isset($data['expirationDate'])) {
                $this->setExpirationDate($data['expirationDate']);
            }
            if (isset($data['site'])) {
                $this->setSite($data['site']);
            }
            if (isset($data['route'])) {
                $this->setRoute($data['route']);
            }
            if (isset($data['doseQuantity'])) {
                $this->setDoseQuantity($data['doseQuantity']);
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
            if (isset($data['note'])) {
                if (is_array($data['note'])) {
                    foreach ($data['note'] as $d) {
                        $this->addNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"note" must be array of objects or null, ' . gettype($data['note']) . ' seen.');
                }
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
            if (isset($data['isSubpotent'])) {
                $this->setIsSubpotent($data['isSubpotent']);
            }
            if (isset($data['subpotentReason'])) {
                if (is_array($data['subpotentReason'])) {
                    foreach ($data['subpotentReason'] as $d) {
                        $this->addSubpotentReason($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subpotentReason" must be array of objects or null, ' . gettype($data['subpotentReason']) . ' seen.');
                }
            }
            if (isset($data['education'])) {
                if (is_array($data['education'])) {
                    foreach ($data['education'] as $d) {
                        $this->addEducation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"education" must be array of objects or null, ' . gettype($data['education']) . ' seen.');
                }
            }
            if (isset($data['programEligibility'])) {
                if (is_array($data['programEligibility'])) {
                    foreach ($data['programEligibility'] as $d) {
                        $this->addProgramEligibility($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"programEligibility" must be array of objects or null, ' . gettype($data['programEligibility']) . ' seen.');
                }
            }
            if (isset($data['fundingSource'])) {
                $this->setFundingSource($data['fundingSource']);
            }
            if (isset($data['reaction'])) {
                if (is_array($data['reaction'])) {
                    foreach ($data['reaction'] as $d) {
                        $this->addReaction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reaction" must be array of objects or null, ' . gettype($data['reaction']) . ' seen.');
                }
            }
            if (isset($data['protocolApplied'])) {
                if (is_array($data['protocolApplied'])) {
                    foreach ($data['protocolApplied'] as $d) {
                        $this->addProtocolApplied($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"protocolApplied" must be array of objects or null, ' . gettype($data['protocolApplied']) . ' seen.');
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
        if (isset($this->statusReason)) {
            $json['statusReason'] = $this->statusReason;
        }
        if (isset($this->vaccineCode)) {
            $json['vaccineCode'] = $this->vaccineCode;
        }
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
        }
        if (isset($this->encounter)) {
            $json['encounter'] = $this->encounter;
        }
        if (isset($this->occurrenceDateTime)) {
            $json['occurrenceDateTime'] = $this->occurrenceDateTime;
        }
        if (isset($this->occurrenceString)) {
            $json['occurrenceString'] = $this->occurrenceString;
        }
        if (isset($this->recorded)) {
            $json['recorded'] = $this->recorded;
        }
        if (isset($this->primarySource)) {
            $json['primarySource'] = $this->primarySource;
        }
        if (isset($this->reportOrigin)) {
            $json['reportOrigin'] = $this->reportOrigin;
        }
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (isset($this->manufacturer)) {
            $json['manufacturer'] = $this->manufacturer;
        }
        if (isset($this->lotNumber)) {
            $json['lotNumber'] = $this->lotNumber;
        }
        if (isset($this->expirationDate)) {
            $json['expirationDate'] = $this->expirationDate;
        }
        if (isset($this->site)) {
            $json['site'] = $this->site;
        }
        if (isset($this->route)) {
            $json['route'] = $this->route;
        }
        if (isset($this->doseQuantity)) {
            $json['doseQuantity'] = $this->doseQuantity;
        }
        if (0 < count($this->performer)) {
            $json['performer'] = [];
            foreach ($this->performer as $performer) {
                $json['performer'][] = $performer;
            }
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
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
        if (isset($this->isSubpotent)) {
            $json['isSubpotent'] = $this->isSubpotent;
        }
        if (0 < count($this->subpotentReason)) {
            $json['subpotentReason'] = [];
            foreach ($this->subpotentReason as $subpotentReason) {
                $json['subpotentReason'][] = $subpotentReason;
            }
        }
        if (0 < count($this->education)) {
            $json['education'] = [];
            foreach ($this->education as $education) {
                $json['education'][] = $education;
            }
        }
        if (0 < count($this->programEligibility)) {
            $json['programEligibility'] = [];
            foreach ($this->programEligibility as $programEligibility) {
                $json['programEligibility'][] = $programEligibility;
            }
        }
        if (isset($this->fundingSource)) {
            $json['fundingSource'] = $this->fundingSource;
        }
        if (0 < count($this->reaction)) {
            $json['reaction'] = [];
            foreach ($this->reaction as $reaction) {
                $json['reaction'][] = $reaction;
            }
        }
        if (0 < count($this->protocolApplied)) {
            $json['protocolApplied'] = [];
            foreach ($this->protocolApplied as $protocolApplied) {
                $json['protocolApplied'][] = $protocolApplied;
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
            $sxe = new \SimpleXMLElement('<Immunization xmlns="http://hl7.org/fhir"></Immunization>');
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
        if (isset($this->vaccineCode)) {
            $this->vaccineCode->xmlSerialize(true, $sxe->addChild('vaccineCode'));
        }
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (isset($this->encounter)) {
            $this->encounter->xmlSerialize(true, $sxe->addChild('encounter'));
        }
        if (isset($this->occurrenceDateTime)) {
            $this->occurrenceDateTime->xmlSerialize(true, $sxe->addChild('occurrenceDateTime'));
        }
        if (isset($this->occurrenceString)) {
            $this->occurrenceString->xmlSerialize(true, $sxe->addChild('occurrenceString'));
        }
        if (isset($this->recorded)) {
            $this->recorded->xmlSerialize(true, $sxe->addChild('recorded'));
        }
        if (isset($this->primarySource)) {
            $this->primarySource->xmlSerialize(true, $sxe->addChild('primarySource'));
        }
        if (isset($this->reportOrigin)) {
            $this->reportOrigin->xmlSerialize(true, $sxe->addChild('reportOrigin'));
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (isset($this->manufacturer)) {
            $this->manufacturer->xmlSerialize(true, $sxe->addChild('manufacturer'));
        }
        if (isset($this->lotNumber)) {
            $this->lotNumber->xmlSerialize(true, $sxe->addChild('lotNumber'));
        }
        if (isset($this->expirationDate)) {
            $this->expirationDate->xmlSerialize(true, $sxe->addChild('expirationDate'));
        }
        if (isset($this->site)) {
            $this->site->xmlSerialize(true, $sxe->addChild('site'));
        }
        if (isset($this->route)) {
            $this->route->xmlSerialize(true, $sxe->addChild('route'));
        }
        if (isset($this->doseQuantity)) {
            $this->doseQuantity->xmlSerialize(true, $sxe->addChild('doseQuantity'));
        }
        if (0 < count($this->performer)) {
            foreach ($this->performer as $performer) {
                $performer->xmlSerialize(true, $sxe->addChild('performer'));
            }
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
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
        if (isset($this->isSubpotent)) {
            $this->isSubpotent->xmlSerialize(true, $sxe->addChild('isSubpotent'));
        }
        if (0 < count($this->subpotentReason)) {
            foreach ($this->subpotentReason as $subpotentReason) {
                $subpotentReason->xmlSerialize(true, $sxe->addChild('subpotentReason'));
            }
        }
        if (0 < count($this->education)) {
            foreach ($this->education as $education) {
                $education->xmlSerialize(true, $sxe->addChild('education'));
            }
        }
        if (0 < count($this->programEligibility)) {
            foreach ($this->programEligibility as $programEligibility) {
                $programEligibility->xmlSerialize(true, $sxe->addChild('programEligibility'));
            }
        }
        if (isset($this->fundingSource)) {
            $this->fundingSource->xmlSerialize(true, $sxe->addChild('fundingSource'));
        }
        if (0 < count($this->reaction)) {
            foreach ($this->reaction as $reaction) {
                $reaction->xmlSerialize(true, $sxe->addChild('reaction'));
            }
        }
        if (0 < count($this->protocolApplied)) {
            foreach ($this->protocolApplied as $protocolApplied) {
                $protocolApplied->xmlSerialize(true, $sxe->addChild('protocolApplied'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
