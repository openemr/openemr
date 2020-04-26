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
 * Describes the intention of how one or more practitioners intend to deliver care for a particular patient, group or community for a period of time, possibly limited to care for a specific condition or set of conditions.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRCarePlan extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Business identifiers assigned to this care plan by the performer or other systems which remain constant as the resource is updated and propagates from server to server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The URL pointing to a FHIR-defined protocol, guideline, questionnaire or other definition that is adhered to in whole or in part by this CarePlan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $instantiatesCanonical = [];

    /**
     * The URL pointing to an externally maintained protocol, guideline, questionnaire or other definition that is adhered to in whole or in part by this CarePlan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri[]
     */
    public $instantiatesUri = [];

    /**
     * A care plan that is fulfilled in whole or in part by this care plan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $basedOn = [];

    /**
     * Completed or terminated care plan whose function is taken by this new care plan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $replaces = [];

    /**
     * A larger care plan of which this particular care plan is a component or step.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $partOf = [];

    /**
     * Indicates whether the plan is currently being acted upon, represents future intentions or is now a historical record.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestStatus
     */
    public $status = null;

    /**
     * Indicates the level of authority/intentionality associated with the care plan and where the care plan fits into the workflow chain.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCarePlanIntent
     */
    public $intent = null;

    /**
     * Identifies what "kind" of plan this is to support differentiation between multiple co-existing plans; e.g. "Home health", "psychiatric", "asthma", "disease management", "wellness plan", etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $category = [];

    /**
     * Human-friendly name for the care plan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * A description of the scope and nature of the plan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Identifies the patient or group whose intended care is described by the plan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The Encounter during which this CarePlan was created or to which the creation of this record is tightly associated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * Indicates when the plan did (or is intended to) come into effect and end.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * Represents when this particular CarePlan record was created in the system, which is often a system-generated date.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $created = null;

    /**
     * When populated, the author is responsible for the care plan.  The care plan is attributed to the author.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $author = null;

    /**
     * Identifies the individual(s) or organization who provided the contents of the care plan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $contributor = [];

    /**
     * Identifies all people and organizations who are expected to be involved in the care envisioned by this plan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $careTeam = [];

    /**
     * Identifies the conditions/problems/concerns/diagnoses/etc. whose management and/or mitigation are handled by this plan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $addresses = [];

    /**
     * Identifies portions of the patient's record that specifically influenced the formation of the plan.  These might include comorbidities, recent procedures, limitations, recent assessments, etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $supportingInfo = [];

    /**
     * Describes the intended objective(s) of carrying out the care plan.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $goal = [];

    /**
     * Identifies a planned action to occur as part of the plan.  For example, a medication to be used, lab tests to perform, self-monitoring, education, etc.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan\FHIRCarePlanActivity[]
     */
    public $activity = [];

    /**
     * General notes about the care plan not covered elsewhere.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CarePlan';

    /**
     * Business identifiers assigned to this care plan by the performer or other systems which remain constant as the resource is updated and propagates from server to server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifiers assigned to this care plan by the performer or other systems which remain constant as the resource is updated and propagates from server to server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The URL pointing to a FHIR-defined protocol, guideline, questionnaire or other definition that is adhered to in whole or in part by this CarePlan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getInstantiatesCanonical()
    {
        return $this->instantiatesCanonical;
    }

    /**
     * The URL pointing to a FHIR-defined protocol, guideline, questionnaire or other definition that is adhered to in whole or in part by this CarePlan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $instantiatesCanonical
     * @return $this
     */
    public function addInstantiatesCanonical($instantiatesCanonical)
    {
        $this->instantiatesCanonical[] = $instantiatesCanonical;
        return $this;
    }

    /**
     * The URL pointing to an externally maintained protocol, guideline, questionnaire or other definition that is adhered to in whole or in part by this CarePlan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri[]
     */
    public function getInstantiatesUri()
    {
        return $this->instantiatesUri;
    }

    /**
     * The URL pointing to an externally maintained protocol, guideline, questionnaire or other definition that is adhered to in whole or in part by this CarePlan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $instantiatesUri
     * @return $this
     */
    public function addInstantiatesUri($instantiatesUri)
    {
        $this->instantiatesUri[] = $instantiatesUri;
        return $this;
    }

    /**
     * A care plan that is fulfilled in whole or in part by this care plan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getBasedOn()
    {
        return $this->basedOn;
    }

    /**
     * A care plan that is fulfilled in whole or in part by this care plan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $basedOn
     * @return $this
     */
    public function addBasedOn($basedOn)
    {
        $this->basedOn[] = $basedOn;
        return $this;
    }

    /**
     * Completed or terminated care plan whose function is taken by this new care plan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getReplaces()
    {
        return $this->replaces;
    }

    /**
     * Completed or terminated care plan whose function is taken by this new care plan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $replaces
     * @return $this
     */
    public function addReplaces($replaces)
    {
        $this->replaces[] = $replaces;
        return $this;
    }

    /**
     * A larger care plan of which this particular care plan is a component or step.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * A larger care plan of which this particular care plan is a component or step.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $partOf
     * @return $this
     */
    public function addPartOf($partOf)
    {
        $this->partOf[] = $partOf;
        return $this;
    }

    /**
     * Indicates whether the plan is currently being acted upon, represents future intentions or is now a historical record.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Indicates whether the plan is currently being acted upon, represents future intentions or is now a historical record.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Indicates the level of authority/intentionality associated with the care plan and where the care plan fits into the workflow chain.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCarePlanIntent
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * Indicates the level of authority/intentionality associated with the care plan and where the care plan fits into the workflow chain.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCarePlanIntent $intent
     * @return $this
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
        return $this;
    }

    /**
     * Identifies what "kind" of plan this is to support differentiation between multiple co-existing plans; e.g. "Home health", "psychiatric", "asthma", "disease management", "wellness plan", etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Identifies what "kind" of plan this is to support differentiation between multiple co-existing plans; e.g. "Home health", "psychiatric", "asthma", "disease management", "wellness plan", etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->category[] = $category;
        return $this;
    }

    /**
     * Human-friendly name for the care plan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Human-friendly name for the care plan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * A description of the scope and nature of the plan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A description of the scope and nature of the plan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Identifies the patient or group whose intended care is described by the plan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Identifies the patient or group whose intended care is described by the plan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The Encounter during which this CarePlan was created or to which the creation of this record is tightly associated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * The Encounter during which this CarePlan was created or to which the creation of this record is tightly associated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * Indicates when the plan did (or is intended to) come into effect and end.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Indicates when the plan did (or is intended to) come into effect and end.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * Represents when this particular CarePlan record was created in the system, which is often a system-generated date.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Represents when this particular CarePlan record was created in the system, which is often a system-generated date.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * When populated, the author is responsible for the care plan.  The care plan is attributed to the author.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * When populated, the author is responsible for the care plan.  The care plan is attributed to the author.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $author
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Identifies the individual(s) or organization who provided the contents of the care plan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getContributor()
    {
        return $this->contributor;
    }

    /**
     * Identifies the individual(s) or organization who provided the contents of the care plan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $contributor
     * @return $this
     */
    public function addContributor($contributor)
    {
        $this->contributor[] = $contributor;
        return $this;
    }

    /**
     * Identifies all people and organizations who are expected to be involved in the care envisioned by this plan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getCareTeam()
    {
        return $this->careTeam;
    }

    /**
     * Identifies all people and organizations who are expected to be involved in the care envisioned by this plan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $careTeam
     * @return $this
     */
    public function addCareTeam($careTeam)
    {
        $this->careTeam[] = $careTeam;
        return $this;
    }

    /**
     * Identifies the conditions/problems/concerns/diagnoses/etc. whose management and/or mitigation are handled by this plan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Identifies the conditions/problems/concerns/diagnoses/etc. whose management and/or mitigation are handled by this plan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $addresses
     * @return $this
     */
    public function addAddresses($addresses)
    {
        $this->addresses[] = $addresses;
        return $this;
    }

    /**
     * Identifies portions of the patient's record that specifically influenced the formation of the plan.  These might include comorbidities, recent procedures, limitations, recent assessments, etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSupportingInfo()
    {
        return $this->supportingInfo;
    }

    /**
     * Identifies portions of the patient's record that specifically influenced the formation of the plan.  These might include comorbidities, recent procedures, limitations, recent assessments, etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $supportingInfo
     * @return $this
     */
    public function addSupportingInfo($supportingInfo)
    {
        $this->supportingInfo[] = $supportingInfo;
        return $this;
    }

    /**
     * Describes the intended objective(s) of carrying out the care plan.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getGoal()
    {
        return $this->goal;
    }

    /**
     * Describes the intended objective(s) of carrying out the care plan.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $goal
     * @return $this
     */
    public function addGoal($goal)
    {
        $this->goal[] = $goal;
        return $this;
    }

    /**
     * Identifies a planned action to occur as part of the plan.  For example, a medication to be used, lab tests to perform, self-monitoring, education, etc.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan\FHIRCarePlanActivity[]
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Identifies a planned action to occur as part of the plan.  For example, a medication to be used, lab tests to perform, self-monitoring, education, etc.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan\FHIRCarePlanActivity $activity
     * @return $this
     */
    public function addActivity($activity)
    {
        $this->activity[] = $activity;
        return $this;
    }

    /**
     * General notes about the care plan not covered elsewhere.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * General notes about the care plan not covered elsewhere.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
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
            if (isset($data['replaces'])) {
                if (is_array($data['replaces'])) {
                    foreach ($data['replaces'] as $d) {
                        $this->addReplaces($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"replaces" must be array of objects or null, ' . gettype($data['replaces']) . ' seen.');
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
            if (isset($data['title'])) {
                $this->setTitle($data['title']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['encounter'])) {
                $this->setEncounter($data['encounter']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['created'])) {
                $this->setCreated($data['created']);
            }
            if (isset($data['author'])) {
                $this->setAuthor($data['author']);
            }
            if (isset($data['contributor'])) {
                if (is_array($data['contributor'])) {
                    foreach ($data['contributor'] as $d) {
                        $this->addContributor($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contributor" must be array of objects or null, ' . gettype($data['contributor']) . ' seen.');
                }
            }
            if (isset($data['careTeam'])) {
                if (is_array($data['careTeam'])) {
                    foreach ($data['careTeam'] as $d) {
                        $this->addCareTeam($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"careTeam" must be array of objects or null, ' . gettype($data['careTeam']) . ' seen.');
                }
            }
            if (isset($data['addresses'])) {
                if (is_array($data['addresses'])) {
                    foreach ($data['addresses'] as $d) {
                        $this->addAddresses($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"addresses" must be array of objects or null, ' . gettype($data['addresses']) . ' seen.');
                }
            }
            if (isset($data['supportingInfo'])) {
                if (is_array($data['supportingInfo'])) {
                    foreach ($data['supportingInfo'] as $d) {
                        $this->addSupportingInfo($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportingInfo" must be array of objects or null, ' . gettype($data['supportingInfo']) . ' seen.');
                }
            }
            if (isset($data['goal'])) {
                if (is_array($data['goal'])) {
                    foreach ($data['goal'] as $d) {
                        $this->addGoal($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"goal" must be array of objects or null, ' . gettype($data['goal']) . ' seen.');
                }
            }
            if (isset($data['activity'])) {
                if (is_array($data['activity'])) {
                    foreach ($data['activity'] as $d) {
                        $this->addActivity($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"activity" must be array of objects or null, ' . gettype($data['activity']) . ' seen.');
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
        if (0 < count($this->replaces)) {
            $json['replaces'] = [];
            foreach ($this->replaces as $replaces) {
                $json['replaces'][] = $replaces;
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
        if (isset($this->intent)) {
            $json['intent'] = $this->intent;
        }
        if (0 < count($this->category)) {
            $json['category'] = [];
            foreach ($this->category as $category) {
                $json['category'][] = $category;
            }
        }
        if (isset($this->title)) {
            $json['title'] = $this->title;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->encounter)) {
            $json['encounter'] = $this->encounter;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->created)) {
            $json['created'] = $this->created;
        }
        if (isset($this->author)) {
            $json['author'] = $this->author;
        }
        if (0 < count($this->contributor)) {
            $json['contributor'] = [];
            foreach ($this->contributor as $contributor) {
                $json['contributor'][] = $contributor;
            }
        }
        if (0 < count($this->careTeam)) {
            $json['careTeam'] = [];
            foreach ($this->careTeam as $careTeam) {
                $json['careTeam'][] = $careTeam;
            }
        }
        if (0 < count($this->addresses)) {
            $json['addresses'] = [];
            foreach ($this->addresses as $addresses) {
                $json['addresses'][] = $addresses;
            }
        }
        if (0 < count($this->supportingInfo)) {
            $json['supportingInfo'] = [];
            foreach ($this->supportingInfo as $supportingInfo) {
                $json['supportingInfo'][] = $supportingInfo;
            }
        }
        if (0 < count($this->goal)) {
            $json['goal'] = [];
            foreach ($this->goal as $goal) {
                $json['goal'][] = $goal;
            }
        }
        if (0 < count($this->activity)) {
            $json['activity'] = [];
            foreach ($this->activity as $activity) {
                $json['activity'][] = $activity;
            }
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
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
            $sxe = new \SimpleXMLElement('<CarePlan xmlns="http://hl7.org/fhir"></CarePlan>');
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
        if (0 < count($this->replaces)) {
            foreach ($this->replaces as $replaces) {
                $replaces->xmlSerialize(true, $sxe->addChild('replaces'));
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
        if (isset($this->intent)) {
            $this->intent->xmlSerialize(true, $sxe->addChild('intent'));
        }
        if (0 < count($this->category)) {
            foreach ($this->category as $category) {
                $category->xmlSerialize(true, $sxe->addChild('category'));
            }
        }
        if (isset($this->title)) {
            $this->title->xmlSerialize(true, $sxe->addChild('title'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->encounter)) {
            $this->encounter->xmlSerialize(true, $sxe->addChild('encounter'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->created)) {
            $this->created->xmlSerialize(true, $sxe->addChild('created'));
        }
        if (isset($this->author)) {
            $this->author->xmlSerialize(true, $sxe->addChild('author'));
        }
        if (0 < count($this->contributor)) {
            foreach ($this->contributor as $contributor) {
                $contributor->xmlSerialize(true, $sxe->addChild('contributor'));
            }
        }
        if (0 < count($this->careTeam)) {
            foreach ($this->careTeam as $careTeam) {
                $careTeam->xmlSerialize(true, $sxe->addChild('careTeam'));
            }
        }
        if (0 < count($this->addresses)) {
            foreach ($this->addresses as $addresses) {
                $addresses->xmlSerialize(true, $sxe->addChild('addresses'));
            }
        }
        if (0 < count($this->supportingInfo)) {
            foreach ($this->supportingInfo as $supportingInfo) {
                $supportingInfo->xmlSerialize(true, $sxe->addChild('supportingInfo'));
            }
        }
        if (0 < count($this->goal)) {
            foreach ($this->goal as $goal) {
                $goal->xmlSerialize(true, $sxe->addChild('goal'));
            }
        }
        if (0 < count($this->activity)) {
            foreach ($this->activity as $activity) {
                $activity->xmlSerialize(true, $sxe->addChild('activity'));
            }
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
