<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan;

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
 * Describes the intention of how one or more practitioners intend to deliver care for a particular patient, group or community for a period of time, possibly limited to care for a specific condition or set of conditions.
 */
class FHIRCarePlanActivity extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifies the outcome at the point when the status of the activity is assessed.  For example, the outcome of an education activity could be patient understands (or not).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $outcomeCodeableConcept = [];

    /**
     * Details of the outcome or action resulting from the activity.  The reference to an "event" resource, such as Procedure or Encounter or Observation, is the result/outcome of the activity itself.  The activity can be conveyed using CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference to a “request” resource).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $outcomeReference = [];

    /**
     * Notes about the adherence/status/progress of the activity.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $progress = [];

    /**
     * The details of the proposed activity represented in a specific resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $reference = null;

    /**
     * A simple summary of a planned activity suitable for a general care plan system (e.g. form driven) that doesn't know about specific resources such as procedure etc.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan\FHIRCarePlanDetail
     */
    public $detail = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CarePlan.Activity';

    /**
     * Identifies the outcome at the point when the status of the activity is assessed.  For example, the outcome of an education activity could be patient understands (or not).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getOutcomeCodeableConcept()
    {
        return $this->outcomeCodeableConcept;
    }

    /**
     * Identifies the outcome at the point when the status of the activity is assessed.  For example, the outcome of an education activity could be patient understands (or not).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $outcomeCodeableConcept
     * @return $this
     */
    public function addOutcomeCodeableConcept($outcomeCodeableConcept)
    {
        $this->outcomeCodeableConcept[] = $outcomeCodeableConcept;
        return $this;
    }

    /**
     * Details of the outcome or action resulting from the activity.  The reference to an "event" resource, such as Procedure or Encounter or Observation, is the result/outcome of the activity itself.  The activity can be conveyed using CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference to a “request” resource).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getOutcomeReference()
    {
        return $this->outcomeReference;
    }

    /**
     * Details of the outcome or action resulting from the activity.  The reference to an "event" resource, such as Procedure or Encounter or Observation, is the result/outcome of the activity itself.  The activity can be conveyed using CarePlan.activity.detail OR using the CarePlan.activity.reference (a reference to a “request” resource).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $outcomeReference
     * @return $this
     */
    public function addOutcomeReference($outcomeReference)
    {
        $this->outcomeReference[] = $outcomeReference;
        return $this;
    }

    /**
     * Notes about the adherence/status/progress of the activity.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Notes about the adherence/status/progress of the activity.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $progress
     * @return $this
     */
    public function addProgress($progress)
    {
        $this->progress[] = $progress;
        return $this;
    }

    /**
     * The details of the proposed activity represented in a specific resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * The details of the proposed activity represented in a specific resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reference
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * A simple summary of a planned activity suitable for a general care plan system (e.g. form driven) that doesn't know about specific resources such as procedure etc.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan\FHIRCarePlanDetail
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * A simple summary of a planned activity suitable for a general care plan system (e.g. form driven) that doesn't know about specific resources such as procedure etc.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCarePlan\FHIRCarePlanDetail $detail
     * @return $this
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
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
            if (isset($data['outcomeCodeableConcept'])) {
                if (is_array($data['outcomeCodeableConcept'])) {
                    foreach ($data['outcomeCodeableConcept'] as $d) {
                        $this->addOutcomeCodeableConcept($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"outcomeCodeableConcept" must be array of objects or null, ' . gettype($data['outcomeCodeableConcept']) . ' seen.');
                }
            }
            if (isset($data['outcomeReference'])) {
                if (is_array($data['outcomeReference'])) {
                    foreach ($data['outcomeReference'] as $d) {
                        $this->addOutcomeReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"outcomeReference" must be array of objects or null, ' . gettype($data['outcomeReference']) . ' seen.');
                }
            }
            if (isset($data['progress'])) {
                if (is_array($data['progress'])) {
                    foreach ($data['progress'] as $d) {
                        $this->addProgress($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"progress" must be array of objects or null, ' . gettype($data['progress']) . ' seen.');
                }
            }
            if (isset($data['reference'])) {
                $this->setReference($data['reference']);
            }
            if (isset($data['detail'])) {
                $this->setDetail($data['detail']);
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
        if (0 < count($this->outcomeCodeableConcept)) {
            $json['outcomeCodeableConcept'] = [];
            foreach ($this->outcomeCodeableConcept as $outcomeCodeableConcept) {
                $json['outcomeCodeableConcept'][] = $outcomeCodeableConcept;
            }
        }
        if (0 < count($this->outcomeReference)) {
            $json['outcomeReference'] = [];
            foreach ($this->outcomeReference as $outcomeReference) {
                $json['outcomeReference'][] = $outcomeReference;
            }
        }
        if (0 < count($this->progress)) {
            $json['progress'] = [];
            foreach ($this->progress as $progress) {
                $json['progress'][] = $progress;
            }
        }
        if (isset($this->reference)) {
            $json['reference'] = $this->reference;
        }
        if (isset($this->detail)) {
            $json['detail'] = $this->detail;
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
            $sxe = new \SimpleXMLElement('<CarePlanActivity xmlns="http://hl7.org/fhir"></CarePlanActivity>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->outcomeCodeableConcept)) {
            foreach ($this->outcomeCodeableConcept as $outcomeCodeableConcept) {
                $outcomeCodeableConcept->xmlSerialize(true, $sxe->addChild('outcomeCodeableConcept'));
            }
        }
        if (0 < count($this->outcomeReference)) {
            foreach ($this->outcomeReference as $outcomeReference) {
                $outcomeReference->xmlSerialize(true, $sxe->addChild('outcomeReference'));
            }
        }
        if (0 < count($this->progress)) {
            foreach ($this->progress as $progress) {
                $progress->xmlSerialize(true, $sxe->addChild('progress'));
            }
        }
        if (isset($this->reference)) {
            $this->reference->xmlSerialize(true, $sxe->addChild('reference'));
        }
        if (isset($this->detail)) {
            $this->detail->xmlSerialize(true, $sxe->addChild('detail'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
