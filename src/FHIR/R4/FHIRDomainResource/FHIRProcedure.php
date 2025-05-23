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
 * An action that is or was performed on or for a patient. This can be a physical intervention like an operation, or less invasive like long term services, counseling, or hypnotherapy.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRProcedure extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Business identifiers assigned to this procedure by the performer or other systems which remain constant as the resource is updated and is propagated from server to server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The URL pointing to a FHIR-defined protocol, guideline, order set or other definition that is adhered to in whole or in part by this Procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $instantiatesCanonical = [];

    /**
     * The URL pointing to an externally maintained protocol, guideline, order set or other definition that is adhered to in whole or in part by this Procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri[]
     */
    public $instantiatesUri = [];

    /**
     * A reference to a resource that contains details of the request for this procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $basedOn = [];

    /**
     * A larger event of which this particular procedure is a component or step.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $partOf = [];

    /**
     * A code specifying the state of the procedure. Generally, this will be the in-progress or completed state.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIREventStatus
     */
    public $status = null;

    /**
     * Captures the reason for the current state of the procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $statusReason = null;

    /**
     * A code that classifies the procedure for searching, sorting and display purposes (e.g. "Surgical Procedure").
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $category = null;

    /**
     * The specific procedure that is performed. Use text if the exact nature of the procedure cannot be coded (e.g. "Laparoscopic Appendectomy").
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * The person, animal or group on which the procedure was performed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The Encounter during which this Procedure was created or performed or to which the creation of this record is tightly associated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $performedDateTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $performedPeriod = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $performedString = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge
     */
    public $performedAge = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $performedRange = null;

    /**
     * Individual who recorded the record and takes responsibility for its content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $recorder = null;

    /**
     * Individual who is making the procedure statement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $asserter = null;

    /**
     * Limited to "real" people rather than equipment.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRProcedure\FHIRProcedurePerformer[]
     */
    public $performer = [];

    /**
     * The location where the procedure actually happened.  E.g. a newborn at home, a tracheostomy at a restaurant.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $location = null;

    /**
     * The coded reason why the procedure was performed. This may be a coded entity of some type, or may simply be present as text.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $reasonCode = [];

    /**
     * The justification of why the procedure was performed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $reasonReference = [];

    /**
     * Detailed and structured anatomical location information. Multiple locations are allowed - e.g. multiple punch biopsies of a lesion.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $bodySite = [];

    /**
     * The outcome of the procedure - did it resolve the reasons for the procedure being performed?
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $outcome = null;

    /**
     * This could be a histology result, pathology report, surgical report, etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $report = [];

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period. These are generally tracked separately from the notes, which will typically describe the procedure itself rather than any 'post procedure' issues.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $complication = [];

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $complicationDetail = [];

    /**
     * If the procedure required specific follow up - e.g. removal of sutures. The follow up may be represented as a simple note or could potentially be more complex, in which case the CarePlan resource can be used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $followUp = [];

    /**
     * Any other notes and comments about the procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * A device that is implanted, removed or otherwise manipulated (calibration, battery replacement, fitting a prosthesis, attaching a wound-vac, etc.) as a focal portion of the Procedure.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRProcedure\FHIRProcedureFocalDevice[]
     */
    public $focalDevice = [];

    /**
     * Identifies medications, devices and any other substance used as part of the procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $usedReference = [];

    /**
     * Identifies coded items that were used as part of the procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $usedCode = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Procedure';

    /**
     * Business identifiers assigned to this procedure by the performer or other systems which remain constant as the resource is updated and is propagated from server to server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifiers assigned to this procedure by the performer or other systems which remain constant as the resource is updated and is propagated from server to server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The URL pointing to a FHIR-defined protocol, guideline, order set or other definition that is adhered to in whole or in part by this Procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getInstantiatesCanonical()
    {
        return $this->instantiatesCanonical;
    }

    /**
     * The URL pointing to a FHIR-defined protocol, guideline, order set or other definition that is adhered to in whole or in part by this Procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $instantiatesCanonical
     * @return $this
     */
    public function addInstantiatesCanonical($instantiatesCanonical)
    {
        $this->instantiatesCanonical[] = $instantiatesCanonical;
        return $this;
    }

    /**
     * The URL pointing to an externally maintained protocol, guideline, order set or other definition that is adhered to in whole or in part by this Procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri[]
     */
    public function getInstantiatesUri()
    {
        return $this->instantiatesUri;
    }

    /**
     * The URL pointing to an externally maintained protocol, guideline, order set or other definition that is adhered to in whole or in part by this Procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $instantiatesUri
     * @return $this
     */
    public function addInstantiatesUri($instantiatesUri)
    {
        $this->instantiatesUri[] = $instantiatesUri;
        return $this;
    }

    /**
     * A reference to a resource that contains details of the request for this procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getBasedOn()
    {
        return $this->basedOn;
    }

    /**
     * A reference to a resource that contains details of the request for this procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $basedOn
     * @return $this
     */
    public function addBasedOn($basedOn)
    {
        $this->basedOn[] = $basedOn;
        return $this;
    }

    /**
     * A larger event of which this particular procedure is a component or step.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * A larger event of which this particular procedure is a component or step.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $partOf
     * @return $this
     */
    public function addPartOf($partOf)
    {
        $this->partOf[] = $partOf;
        return $this;
    }

    /**
     * A code specifying the state of the procedure. Generally, this will be the in-progress or completed state.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIREventStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * A code specifying the state of the procedure. Generally, this will be the in-progress or completed state.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIREventStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Captures the reason for the current state of the procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStatusReason()
    {
        return $this->statusReason;
    }

    /**
     * Captures the reason for the current state of the procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $statusReason
     * @return $this
     */
    public function setStatusReason($statusReason)
    {
        $this->statusReason = $statusReason;
        return $this;
    }

    /**
     * A code that classifies the procedure for searching, sorting and display purposes (e.g. "Surgical Procedure").
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * A code that classifies the procedure for searching, sorting and display purposes (e.g. "Surgical Procedure").
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * The specific procedure that is performed. Use text if the exact nature of the procedure cannot be coded (e.g. "Laparoscopic Appendectomy").
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * The specific procedure that is performed. Use text if the exact nature of the procedure cannot be coded (e.g. "Laparoscopic Appendectomy").
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The person, animal or group on which the procedure was performed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The person, animal or group on which the procedure was performed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The Encounter during which this Procedure was created or performed or to which the creation of this record is tightly associated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * The Encounter during which this Procedure was created or performed or to which the creation of this record is tightly associated.
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
    public function getPerformedDateTime()
    {
        return $this->performedDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $performedDateTime
     * @return $this
     */
    public function setPerformedDateTime($performedDateTime)
    {
        $this->performedDateTime = $performedDateTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPerformedPeriod()
    {
        return $this->performedPeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $performedPeriod
     * @return $this
     */
    public function setPerformedPeriod($performedPeriod)
    {
        $this->performedPeriod = $performedPeriod;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPerformedString()
    {
        return $this->performedString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $performedString
     * @return $this
     */
    public function setPerformedString($performedString)
    {
        $this->performedString = $performedString;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge
     */
    public function getPerformedAge()
    {
        return $this->performedAge;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge $performedAge
     * @return $this
     */
    public function setPerformedAge($performedAge)
    {
        $this->performedAge = $performedAge;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getPerformedRange()
    {
        return $this->performedRange;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $performedRange
     * @return $this
     */
    public function setPerformedRange($performedRange)
    {
        $this->performedRange = $performedRange;
        return $this;
    }

    /**
     * Individual who recorded the record and takes responsibility for its content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRecorder()
    {
        return $this->recorder;
    }

    /**
     * Individual who recorded the record and takes responsibility for its content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $recorder
     * @return $this
     */
    public function setRecorder($recorder)
    {
        $this->recorder = $recorder;
        return $this;
    }

    /**
     * Individual who is making the procedure statement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAsserter()
    {
        return $this->asserter;
    }

    /**
     * Individual who is making the procedure statement.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $asserter
     * @return $this
     */
    public function setAsserter($asserter)
    {
        $this->asserter = $asserter;
        return $this;
    }

    /**
     * Limited to "real" people rather than equipment.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRProcedure\FHIRProcedurePerformer[]
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * Limited to "real" people rather than equipment.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRProcedure\FHIRProcedurePerformer $performer
     * @return $this
     */
    public function addPerformer($performer)
    {
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * The location where the procedure actually happened.  E.g. a newborn at home, a tracheostomy at a restaurant.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The location where the procedure actually happened.  E.g. a newborn at home, a tracheostomy at a restaurant.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * The coded reason why the procedure was performed. This may be a coded entity of some type, or may simply be present as text.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * The coded reason why the procedure was performed. This may be a coded entity of some type, or may simply be present as text.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return $this
     */
    public function addReasonCode($reasonCode)
    {
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * The justification of why the procedure was performed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getReasonReference()
    {
        return $this->reasonReference;
    }

    /**
     * The justification of why the procedure was performed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reasonReference
     * @return $this
     */
    public function addReasonReference($reasonReference)
    {
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * Detailed and structured anatomical location information. Multiple locations are allowed - e.g. multiple punch biopsies of a lesion.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getBodySite()
    {
        return $this->bodySite;
    }

    /**
     * Detailed and structured anatomical location information. Multiple locations are allowed - e.g. multiple punch biopsies of a lesion.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $bodySite
     * @return $this
     */
    public function addBodySite($bodySite)
    {
        $this->bodySite[] = $bodySite;
        return $this;
    }

    /**
     * The outcome of the procedure - did it resolve the reasons for the procedure being performed?
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * The outcome of the procedure - did it resolve the reasons for the procedure being performed?
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * This could be a histology result, pathology report, surgical report, etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * This could be a histology result, pathology report, surgical report, etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $report
     * @return $this
     */
    public function addReport($report)
    {
        $this->report[] = $report;
        return $this;
    }

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period. These are generally tracked separately from the notes, which will typically describe the procedure itself rather than any 'post procedure' issues.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getComplication()
    {
        return $this->complication;
    }

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period. These are generally tracked separately from the notes, which will typically describe the procedure itself rather than any 'post procedure' issues.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $complication
     * @return $this
     */
    public function addComplication($complication)
    {
        $this->complication[] = $complication;
        return $this;
    }

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getComplicationDetail()
    {
        return $this->complicationDetail;
    }

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $complicationDetail
     * @return $this
     */
    public function addComplicationDetail($complicationDetail)
    {
        $this->complicationDetail[] = $complicationDetail;
        return $this;
    }

    /**
     * If the procedure required specific follow up - e.g. removal of sutures. The follow up may be represented as a simple note or could potentially be more complex, in which case the CarePlan resource can be used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getFollowUp()
    {
        return $this->followUp;
    }

    /**
     * If the procedure required specific follow up - e.g. removal of sutures. The follow up may be represented as a simple note or could potentially be more complex, in which case the CarePlan resource can be used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $followUp
     * @return $this
     */
    public function addFollowUp($followUp)
    {
        $this->followUp[] = $followUp;
        return $this;
    }

    /**
     * Any other notes and comments about the procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Any other notes and comments about the procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * A device that is implanted, removed or otherwise manipulated (calibration, battery replacement, fitting a prosthesis, attaching a wound-vac, etc.) as a focal portion of the Procedure.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRProcedure\FHIRProcedureFocalDevice[]
     */
    public function getFocalDevice()
    {
        return $this->focalDevice;
    }

    /**
     * A device that is implanted, removed or otherwise manipulated (calibration, battery replacement, fitting a prosthesis, attaching a wound-vac, etc.) as a focal portion of the Procedure.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRProcedure\FHIRProcedureFocalDevice $focalDevice
     * @return $this
     */
    public function addFocalDevice($focalDevice)
    {
        $this->focalDevice[] = $focalDevice;
        return $this;
    }

    /**
     * Identifies medications, devices and any other substance used as part of the procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getUsedReference()
    {
        return $this->usedReference;
    }

    /**
     * Identifies medications, devices and any other substance used as part of the procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $usedReference
     * @return $this
     */
    public function addUsedReference($usedReference)
    {
        $this->usedReference[] = $usedReference;
        return $this;
    }

    /**
     * Identifies coded items that were used as part of the procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getUsedCode()
    {
        return $this->usedCode;
    }

    /**
     * Identifies coded items that were used as part of the procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $usedCode
     * @return $this
     */
    public function addUsedCode($usedCode)
    {
        $this->usedCode[] = $usedCode;
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
            if (isset($data['statusReason'])) {
                $this->setStatusReason($data['statusReason']);
            }
            if (isset($data['category'])) {
                $this->setCategory($data['category']);
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['encounter'])) {
                $this->setEncounter($data['encounter']);
            }
            if (isset($data['performedDateTime'])) {
                $this->setPerformedDateTime($data['performedDateTime']);
            }
            if (isset($data['performedPeriod'])) {
                $this->setPerformedPeriod($data['performedPeriod']);
            }
            if (isset($data['performedString'])) {
                $this->setPerformedString($data['performedString']);
            }
            if (isset($data['performedAge'])) {
                $this->setPerformedAge($data['performedAge']);
            }
            if (isset($data['performedRange'])) {
                $this->setPerformedRange($data['performedRange']);
            }
            if (isset($data['recorder'])) {
                $this->setRecorder($data['recorder']);
            }
            if (isset($data['asserter'])) {
                $this->setAsserter($data['asserter']);
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
            if (isset($data['bodySite'])) {
                if (is_array($data['bodySite'])) {
                    foreach ($data['bodySite'] as $d) {
                        $this->addBodySite($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"bodySite" must be array of objects or null, ' . gettype($data['bodySite']) . ' seen.');
                }
            }
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
            }
            if (isset($data['report'])) {
                if (is_array($data['report'])) {
                    foreach ($data['report'] as $d) {
                        $this->addReport($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"report" must be array of objects or null, ' . gettype($data['report']) . ' seen.');
                }
            }
            if (isset($data['complication'])) {
                if (is_array($data['complication'])) {
                    foreach ($data['complication'] as $d) {
                        $this->addComplication($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"complication" must be array of objects or null, ' . gettype($data['complication']) . ' seen.');
                }
            }
            if (isset($data['complicationDetail'])) {
                if (is_array($data['complicationDetail'])) {
                    foreach ($data['complicationDetail'] as $d) {
                        $this->addComplicationDetail($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"complicationDetail" must be array of objects or null, ' . gettype($data['complicationDetail']) . ' seen.');
                }
            }
            if (isset($data['followUp'])) {
                if (is_array($data['followUp'])) {
                    foreach ($data['followUp'] as $d) {
                        $this->addFollowUp($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"followUp" must be array of objects or null, ' . gettype($data['followUp']) . ' seen.');
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
            if (isset($data['focalDevice'])) {
                if (is_array($data['focalDevice'])) {
                    foreach ($data['focalDevice'] as $d) {
                        $this->addFocalDevice($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"focalDevice" must be array of objects or null, ' . gettype($data['focalDevice']) . ' seen.');
                }
            }
            if (isset($data['usedReference'])) {
                if (is_array($data['usedReference'])) {
                    foreach ($data['usedReference'] as $d) {
                        $this->addUsedReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"usedReference" must be array of objects or null, ' . gettype($data['usedReference']) . ' seen.');
                }
            }
            if (isset($data['usedCode'])) {
                if (is_array($data['usedCode'])) {
                    foreach ($data['usedCode'] as $d) {
                        $this->addUsedCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"usedCode" must be array of objects or null, ' . gettype($data['usedCode']) . ' seen.');
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
        if (0 < count($this->partOf)) {
            $json['partOf'] = [];
            foreach ($this->partOf as $partOf) {
                $json['partOf'][] = $partOf;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->statusReason)) {
            $json['statusReason'] = $this->statusReason;
        }
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->encounter)) {
            $json['encounter'] = $this->encounter;
        }
        if (isset($this->performedDateTime)) {
            $json['performedDateTime'] = $this->performedDateTime;
        }
        if (isset($this->performedPeriod)) {
            $json['performedPeriod'] = $this->performedPeriod;
        }
        if (isset($this->performedString)) {
            $json['performedString'] = $this->performedString;
        }
        if (isset($this->performedAge)) {
            $json['performedAge'] = $this->performedAge;
        }
        if (isset($this->performedRange)) {
            $json['performedRange'] = $this->performedRange;
        }
        if (isset($this->recorder)) {
            $json['recorder'] = $this->recorder;
        }
        if (isset($this->asserter)) {
            $json['asserter'] = $this->asserter;
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
        if (0 < count($this->bodySite)) {
            $json['bodySite'] = [];
            foreach ($this->bodySite as $bodySite) {
                $json['bodySite'][] = $bodySite;
            }
        }
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
        }
        if (0 < count($this->report)) {
            $json['report'] = [];
            foreach ($this->report as $report) {
                $json['report'][] = $report;
            }
        }
        if (0 < count($this->complication)) {
            $json['complication'] = [];
            foreach ($this->complication as $complication) {
                $json['complication'][] = $complication;
            }
        }
        if (0 < count($this->complicationDetail)) {
            $json['complicationDetail'] = [];
            foreach ($this->complicationDetail as $complicationDetail) {
                $json['complicationDetail'][] = $complicationDetail;
            }
        }
        if (0 < count($this->followUp)) {
            $json['followUp'] = [];
            foreach ($this->followUp as $followUp) {
                $json['followUp'][] = $followUp;
            }
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
        }
        if (0 < count($this->focalDevice)) {
            $json['focalDevice'] = [];
            foreach ($this->focalDevice as $focalDevice) {
                $json['focalDevice'][] = $focalDevice;
            }
        }
        if (0 < count($this->usedReference)) {
            $json['usedReference'] = [];
            foreach ($this->usedReference as $usedReference) {
                $json['usedReference'][] = $usedReference;
            }
        }
        if (0 < count($this->usedCode)) {
            $json['usedCode'] = [];
            foreach ($this->usedCode as $usedCode) {
                $json['usedCode'][] = $usedCode;
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
            $sxe = new \SimpleXMLElement('<Procedure xmlns="http://hl7.org/fhir"></Procedure>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
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
        if (0 < count($this->partOf)) {
            foreach ($this->partOf as $partOf) {
                $partOf->xmlSerialize(true, $sxe->addChild('partOf'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->statusReason)) {
            $this->statusReason->xmlSerialize(true, $sxe->addChild('statusReason'));
        }
        if (isset($this->category)) {
            $this->category->xmlSerialize(true, $sxe->addChild('category'));
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->encounter)) {
            $this->encounter->xmlSerialize(true, $sxe->addChild('encounter'));
        }
        if (isset($this->performedDateTime)) {
            $this->performedDateTime->xmlSerialize(true, $sxe->addChild('performedDateTime'));
        }
        if (isset($this->performedPeriod)) {
            $this->performedPeriod->xmlSerialize(true, $sxe->addChild('performedPeriod'));
        }
        if (isset($this->performedString)) {
            $this->performedString->xmlSerialize(true, $sxe->addChild('performedString'));
        }
        if (isset($this->performedAge)) {
            $this->performedAge->xmlSerialize(true, $sxe->addChild('performedAge'));
        }
        if (isset($this->performedRange)) {
            $this->performedRange->xmlSerialize(true, $sxe->addChild('performedRange'));
        }
        if (isset($this->recorder)) {
            $this->recorder->xmlSerialize(true, $sxe->addChild('recorder'));
        }
        if (isset($this->asserter)) {
            $this->asserter->xmlSerialize(true, $sxe->addChild('asserter'));
        }
        if (0 < count($this->performer)) {
            foreach ($this->performer as $performer) {
                $performer->xmlSerialize(true, $sxe->addChild('performer'));
            }
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
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
        if (0 < count($this->bodySite)) {
            foreach ($this->bodySite as $bodySite) {
                $bodySite->xmlSerialize(true, $sxe->addChild('bodySite'));
            }
        }
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if (0 < count($this->report)) {
            foreach ($this->report as $report) {
                $report->xmlSerialize(true, $sxe->addChild('report'));
            }
        }
        if (0 < count($this->complication)) {
            foreach ($this->complication as $complication) {
                $complication->xmlSerialize(true, $sxe->addChild('complication'));
            }
        }
        if (0 < count($this->complicationDetail)) {
            foreach ($this->complicationDetail as $complicationDetail) {
                $complicationDetail->xmlSerialize(true, $sxe->addChild('complicationDetail'));
            }
        }
        if (0 < count($this->followUp)) {
            foreach ($this->followUp as $followUp) {
                $followUp->xmlSerialize(true, $sxe->addChild('followUp'));
            }
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if (0 < count($this->focalDevice)) {
            foreach ($this->focalDevice as $focalDevice) {
                $focalDevice->xmlSerialize(true, $sxe->addChild('focalDevice'));
            }
        }
        if (0 < count($this->usedReference)) {
            foreach ($this->usedReference as $usedReference) {
                $usedReference->xmlSerialize(true, $sxe->addChild('usedReference'));
            }
        }
        if (0 < count($this->usedCode)) {
            foreach ($this->usedCode as $usedCode) {
                $usedCode->xmlSerialize(true, $sxe->addChild('usedCode'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
