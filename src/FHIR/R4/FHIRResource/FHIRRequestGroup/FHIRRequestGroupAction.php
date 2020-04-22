<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup;

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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;

/**
 * A group of related requests that can be used to capture intended activities that have inter-dependencies such as "give this medication after that one".
 */
class FHIRRequestGroupAction extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A user-visible prefix for the action.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $prefix = null;

    /**
     * The title of the action displayed to a user.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * A short description of the action used to provide a summary to display to the user.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * A text equivalent of the action to be performed. This provides a human-interpretable description of the action when the definition is consumed by a system that might not be capable of interpreting it dynamically.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $textEquivalent = null;

    /**
     * Indicates how quickly the action should be addressed with respect to other actions.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority
     */
    public $priority = null;

    /**
     * A code that provides meaning for the action or action group. For example, a section may have a LOINC code for a section of a documentation template.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $code = [];

    /**
     * Didactic or other informational resources associated with the action that can be provided to the CDS recipient. Information resources can include inline text commentary and links to web resources.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact[]
     */
    public $documentation = [];

    /**
     * An expression that describes applicability criteria, or start/stop conditions for the action.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup\FHIRRequestGroupCondition[]
     */
    public $condition = [];

    /**
     * A relationship to another action such as "before" or "30-60 minutes after start of".
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup\FHIRRequestGroupRelatedAction[]
     */
    public $relatedAction = [];

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $timingDateTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge
     */
    public $timingAge = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $timingPeriod = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $timingDuration = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $timingRange = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public $timingTiming = null;

    /**
     * The participant that should perform or be responsible for this action.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $participant = [];

    /**
     * The type of action to perform (create, update, remove).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Defines the grouping behavior for the action and its children.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRActionGroupingBehavior
     */
    public $groupingBehavior = null;

    /**
     * Defines the selection behavior for the action and its children.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRActionSelectionBehavior
     */
    public $selectionBehavior = null;

    /**
     * Defines expectations around whether an action is required.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRActionRequiredBehavior
     */
    public $requiredBehavior = null;

    /**
     * Defines whether the action should usually be preselected.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRActionPrecheckBehavior
     */
    public $precheckBehavior = null;

    /**
     * Defines whether the action can be selected multiple times.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRActionCardinalityBehavior
     */
    public $cardinalityBehavior = null;

    /**
     * The resource that is the target of the action (e.g. CommunicationRequest).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $resource = null;

    /**
     * Sub actions.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup\FHIRRequestGroupAction[]
     */
    public $action = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'RequestGroup.Action';

    /**
     * A user-visible prefix for the action.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * A user-visible prefix for the action.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * The title of the action displayed to a user.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * The title of the action displayed to a user.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * A short description of the action used to provide a summary to display to the user.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A short description of the action used to provide a summary to display to the user.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * A text equivalent of the action to be performed. This provides a human-interpretable description of the action when the definition is consumed by a system that might not be capable of interpreting it dynamically.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTextEquivalent()
    {
        return $this->textEquivalent;
    }

    /**
     * A text equivalent of the action to be performed. This provides a human-interpretable description of the action when the definition is consumed by a system that might not be capable of interpreting it dynamically.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $textEquivalent
     * @return $this
     */
    public function setTextEquivalent($textEquivalent)
    {
        $this->textEquivalent = $textEquivalent;
        return $this;
    }

    /**
     * Indicates how quickly the action should be addressed with respect to other actions.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Indicates how quickly the action should be addressed with respect to other actions.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * A code that provides meaning for the action or action group. For example, a section may have a LOINC code for a section of a documentation template.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A code that provides meaning for the action or action group. For example, a section may have a LOINC code for a section of a documentation template.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function addCode($code)
    {
        $this->code[] = $code;
        return $this;
    }

    /**
     * Didactic or other informational resources associated with the action that can be provided to the CDS recipient. Information resources can include inline text commentary and links to web resources.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact[]
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Didactic or other informational resources associated with the action that can be provided to the CDS recipient. Information resources can include inline text commentary and links to web resources.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRelatedArtifact $documentation
     * @return $this
     */
    public function addDocumentation($documentation)
    {
        $this->documentation[] = $documentation;
        return $this;
    }

    /**
     * An expression that describes applicability criteria, or start/stop conditions for the action.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup\FHIRRequestGroupCondition[]
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * An expression that describes applicability criteria, or start/stop conditions for the action.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup\FHIRRequestGroupCondition $condition
     * @return $this
     */
    public function addCondition($condition)
    {
        $this->condition[] = $condition;
        return $this;
    }

    /**
     * A relationship to another action such as "before" or "30-60 minutes after start of".
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup\FHIRRequestGroupRelatedAction[]
     */
    public function getRelatedAction()
    {
        return $this->relatedAction;
    }

    /**
     * A relationship to another action such as "before" or "30-60 minutes after start of".
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup\FHIRRequestGroupRelatedAction $relatedAction
     * @return $this
     */
    public function addRelatedAction($relatedAction)
    {
        $this->relatedAction[] = $relatedAction;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getTimingDateTime()
    {
        return $this->timingDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $timingDateTime
     * @return $this
     */
    public function setTimingDateTime($timingDateTime)
    {
        $this->timingDateTime = $timingDateTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge
     */
    public function getTimingAge()
    {
        return $this->timingAge;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRAge $timingAge
     * @return $this
     */
    public function setTimingAge($timingAge)
    {
        $this->timingAge = $timingAge;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getTimingPeriod()
    {
        return $this->timingPeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $timingPeriod
     * @return $this
     */
    public function setTimingPeriod($timingPeriod)
    {
        $this->timingPeriod = $timingPeriod;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getTimingDuration()
    {
        return $this->timingDuration;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $timingDuration
     * @return $this
     */
    public function setTimingDuration($timingDuration)
    {
        $this->timingDuration = $timingDuration;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getTimingRange()
    {
        return $this->timingRange;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $timingRange
     * @return $this
     */
    public function setTimingRange($timingRange)
    {
        $this->timingRange = $timingRange;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public function getTimingTiming()
    {
        return $this->timingTiming;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming $timingTiming
     * @return $this
     */
    public function setTimingTiming($timingTiming)
    {
        $this->timingTiming = $timingTiming;
        return $this;
    }

    /**
     * The participant that should perform or be responsible for this action.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * The participant that should perform or be responsible for this action.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $participant
     * @return $this
     */
    public function addParticipant($participant)
    {
        $this->participant[] = $participant;
        return $this;
    }

    /**
     * The type of action to perform (create, update, remove).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of action to perform (create, update, remove).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Defines the grouping behavior for the action and its children.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRActionGroupingBehavior
     */
    public function getGroupingBehavior()
    {
        return $this->groupingBehavior;
    }

    /**
     * Defines the grouping behavior for the action and its children.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRActionGroupingBehavior $groupingBehavior
     * @return $this
     */
    public function setGroupingBehavior($groupingBehavior)
    {
        $this->groupingBehavior = $groupingBehavior;
        return $this;
    }

    /**
     * Defines the selection behavior for the action and its children.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRActionSelectionBehavior
     */
    public function getSelectionBehavior()
    {
        return $this->selectionBehavior;
    }

    /**
     * Defines the selection behavior for the action and its children.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRActionSelectionBehavior $selectionBehavior
     * @return $this
     */
    public function setSelectionBehavior($selectionBehavior)
    {
        $this->selectionBehavior = $selectionBehavior;
        return $this;
    }

    /**
     * Defines expectations around whether an action is required.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRActionRequiredBehavior
     */
    public function getRequiredBehavior()
    {
        return $this->requiredBehavior;
    }

    /**
     * Defines expectations around whether an action is required.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRActionRequiredBehavior $requiredBehavior
     * @return $this
     */
    public function setRequiredBehavior($requiredBehavior)
    {
        $this->requiredBehavior = $requiredBehavior;
        return $this;
    }

    /**
     * Defines whether the action should usually be preselected.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRActionPrecheckBehavior
     */
    public function getPrecheckBehavior()
    {
        return $this->precheckBehavior;
    }

    /**
     * Defines whether the action should usually be preselected.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRActionPrecheckBehavior $precheckBehavior
     * @return $this
     */
    public function setPrecheckBehavior($precheckBehavior)
    {
        $this->precheckBehavior = $precheckBehavior;
        return $this;
    }

    /**
     * Defines whether the action can be selected multiple times.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRActionCardinalityBehavior
     */
    public function getCardinalityBehavior()
    {
        return $this->cardinalityBehavior;
    }

    /**
     * Defines whether the action can be selected multiple times.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRActionCardinalityBehavior $cardinalityBehavior
     * @return $this
     */
    public function setCardinalityBehavior($cardinalityBehavior)
    {
        $this->cardinalityBehavior = $cardinalityBehavior;
        return $this;
    }

    /**
     * The resource that is the target of the action (e.g. CommunicationRequest).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * The resource that is the target of the action (e.g. CommunicationRequest).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Sub actions.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup\FHIRRequestGroupAction[]
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Sub actions.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRRequestGroup\FHIRRequestGroupAction $action
     * @return $this
     */
    public function addAction($action)
    {
        $this->action[] = $action;
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
            if (isset($data['prefix'])) {
                $this->setPrefix($data['prefix']);
            }
            if (isset($data['title'])) {
                $this->setTitle($data['title']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['textEquivalent'])) {
                $this->setTextEquivalent($data['textEquivalent']);
            }
            if (isset($data['priority'])) {
                $this->setPriority($data['priority']);
            }
            if (isset($data['code'])) {
                if (is_array($data['code'])) {
                    foreach ($data['code'] as $d) {
                        $this->addCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"code" must be array of objects or null, ' . gettype($data['code']) . ' seen.');
                }
            }
            if (isset($data['documentation'])) {
                if (is_array($data['documentation'])) {
                    foreach ($data['documentation'] as $d) {
                        $this->addDocumentation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"documentation" must be array of objects or null, ' . gettype($data['documentation']) . ' seen.');
                }
            }
            if (isset($data['condition'])) {
                if (is_array($data['condition'])) {
                    foreach ($data['condition'] as $d) {
                        $this->addCondition($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"condition" must be array of objects or null, ' . gettype($data['condition']) . ' seen.');
                }
            }
            if (isset($data['relatedAction'])) {
                if (is_array($data['relatedAction'])) {
                    foreach ($data['relatedAction'] as $d) {
                        $this->addRelatedAction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"relatedAction" must be array of objects or null, ' . gettype($data['relatedAction']) . ' seen.');
                }
            }
            if (isset($data['timingDateTime'])) {
                $this->setTimingDateTime($data['timingDateTime']);
            }
            if (isset($data['timingAge'])) {
                $this->setTimingAge($data['timingAge']);
            }
            if (isset($data['timingPeriod'])) {
                $this->setTimingPeriod($data['timingPeriod']);
            }
            if (isset($data['timingDuration'])) {
                $this->setTimingDuration($data['timingDuration']);
            }
            if (isset($data['timingRange'])) {
                $this->setTimingRange($data['timingRange']);
            }
            if (isset($data['timingTiming'])) {
                $this->setTimingTiming($data['timingTiming']);
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['groupingBehavior'])) {
                $this->setGroupingBehavior($data['groupingBehavior']);
            }
            if (isset($data['selectionBehavior'])) {
                $this->setSelectionBehavior($data['selectionBehavior']);
            }
            if (isset($data['requiredBehavior'])) {
                $this->setRequiredBehavior($data['requiredBehavior']);
            }
            if (isset($data['precheckBehavior'])) {
                $this->setPrecheckBehavior($data['precheckBehavior']);
            }
            if (isset($data['cardinalityBehavior'])) {
                $this->setCardinalityBehavior($data['cardinalityBehavior']);
            }
            if (isset($data['resource'])) {
                $this->setResource($data['resource']);
            }
            if (isset($data['action'])) {
                if (is_array($data['action'])) {
                    foreach ($data['action'] as $d) {
                        $this->addAction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"action" must be array of objects or null, ' . gettype($data['action']) . ' seen.');
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
        if (isset($this->prefix)) {
            $json['prefix'] = $this->prefix;
        }
        if (isset($this->title)) {
            $json['title'] = $this->title;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->textEquivalent)) {
            $json['textEquivalent'] = $this->textEquivalent;
        }
        if (isset($this->priority)) {
            $json['priority'] = $this->priority;
        }
        if (0 < count($this->code)) {
            $json['code'] = [];
            foreach ($this->code as $code) {
                $json['code'][] = $code;
            }
        }
        if (0 < count($this->documentation)) {
            $json['documentation'] = [];
            foreach ($this->documentation as $documentation) {
                $json['documentation'][] = $documentation;
            }
        }
        if (0 < count($this->condition)) {
            $json['condition'] = [];
            foreach ($this->condition as $condition) {
                $json['condition'][] = $condition;
            }
        }
        if (0 < count($this->relatedAction)) {
            $json['relatedAction'] = [];
            foreach ($this->relatedAction as $relatedAction) {
                $json['relatedAction'][] = $relatedAction;
            }
        }
        if (isset($this->timingDateTime)) {
            $json['timingDateTime'] = $this->timingDateTime;
        }
        if (isset($this->timingAge)) {
            $json['timingAge'] = $this->timingAge;
        }
        if (isset($this->timingPeriod)) {
            $json['timingPeriod'] = $this->timingPeriod;
        }
        if (isset($this->timingDuration)) {
            $json['timingDuration'] = $this->timingDuration;
        }
        if (isset($this->timingRange)) {
            $json['timingRange'] = $this->timingRange;
        }
        if (isset($this->timingTiming)) {
            $json['timingTiming'] = $this->timingTiming;
        }
        if (0 < count($this->participant)) {
            $json['participant'] = [];
            foreach ($this->participant as $participant) {
                $json['participant'][] = $participant;
            }
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->groupingBehavior)) {
            $json['groupingBehavior'] = $this->groupingBehavior;
        }
        if (isset($this->selectionBehavior)) {
            $json['selectionBehavior'] = $this->selectionBehavior;
        }
        if (isset($this->requiredBehavior)) {
            $json['requiredBehavior'] = $this->requiredBehavior;
        }
        if (isset($this->precheckBehavior)) {
            $json['precheckBehavior'] = $this->precheckBehavior;
        }
        if (isset($this->cardinalityBehavior)) {
            $json['cardinalityBehavior'] = $this->cardinalityBehavior;
        }
        if (isset($this->resource)) {
            $json['resource'] = $this->resource;
        }
        if (0 < count($this->action)) {
            $json['action'] = [];
            foreach ($this->action as $action) {
                $json['action'][] = $action;
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
            $sxe = new \SimpleXMLElement('<RequestGroupAction xmlns="http://hl7.org/fhir"></RequestGroupAction>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->prefix)) {
            $this->prefix->xmlSerialize(true, $sxe->addChild('prefix'));
        }
        if (isset($this->title)) {
            $this->title->xmlSerialize(true, $sxe->addChild('title'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->textEquivalent)) {
            $this->textEquivalent->xmlSerialize(true, $sxe->addChild('textEquivalent'));
        }
        if (isset($this->priority)) {
            $this->priority->xmlSerialize(true, $sxe->addChild('priority'));
        }
        if (0 < count($this->code)) {
            foreach ($this->code as $code) {
                $code->xmlSerialize(true, $sxe->addChild('code'));
            }
        }
        if (0 < count($this->documentation)) {
            foreach ($this->documentation as $documentation) {
                $documentation->xmlSerialize(true, $sxe->addChild('documentation'));
            }
        }
        if (0 < count($this->condition)) {
            foreach ($this->condition as $condition) {
                $condition->xmlSerialize(true, $sxe->addChild('condition'));
            }
        }
        if (0 < count($this->relatedAction)) {
            foreach ($this->relatedAction as $relatedAction) {
                $relatedAction->xmlSerialize(true, $sxe->addChild('relatedAction'));
            }
        }
        if (isset($this->timingDateTime)) {
            $this->timingDateTime->xmlSerialize(true, $sxe->addChild('timingDateTime'));
        }
        if (isset($this->timingAge)) {
            $this->timingAge->xmlSerialize(true, $sxe->addChild('timingAge'));
        }
        if (isset($this->timingPeriod)) {
            $this->timingPeriod->xmlSerialize(true, $sxe->addChild('timingPeriod'));
        }
        if (isset($this->timingDuration)) {
            $this->timingDuration->xmlSerialize(true, $sxe->addChild('timingDuration'));
        }
        if (isset($this->timingRange)) {
            $this->timingRange->xmlSerialize(true, $sxe->addChild('timingRange'));
        }
        if (isset($this->timingTiming)) {
            $this->timingTiming->xmlSerialize(true, $sxe->addChild('timingTiming'));
        }
        if (0 < count($this->participant)) {
            foreach ($this->participant as $participant) {
                $participant->xmlSerialize(true, $sxe->addChild('participant'));
            }
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->groupingBehavior)) {
            $this->groupingBehavior->xmlSerialize(true, $sxe->addChild('groupingBehavior'));
        }
        if (isset($this->selectionBehavior)) {
            $this->selectionBehavior->xmlSerialize(true, $sxe->addChild('selectionBehavior'));
        }
        if (isset($this->requiredBehavior)) {
            $this->requiredBehavior->xmlSerialize(true, $sxe->addChild('requiredBehavior'));
        }
        if (isset($this->precheckBehavior)) {
            $this->precheckBehavior->xmlSerialize(true, $sxe->addChild('precheckBehavior'));
        }
        if (isset($this->cardinalityBehavior)) {
            $this->cardinalityBehavior->xmlSerialize(true, $sxe->addChild('cardinalityBehavior'));
        }
        if (isset($this->resource)) {
            $this->resource->xmlSerialize(true, $sxe->addChild('resource'));
        }
        if (0 < count($this->action)) {
            foreach ($this->action as $action) {
                $action->xmlSerialize(true, $sxe->addChild('action'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
