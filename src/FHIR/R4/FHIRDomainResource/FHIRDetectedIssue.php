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
 * Indicates an actual or potential clinical issue with or between one or more active or proposed clinical actions for a patient; e.g. Drug-drug interaction, Ineffective treatment frequency, Procedure-condition conflict, etc.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRDetectedIssue extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Business identifier associated with the detected issue record.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Indicates the status of the detected issue.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationStatus
     */
    public $status = null;

    /**
     * Identifies the general type of issue identified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * Indicates the degree of importance associated with the identified issue based on the potential impact on the patient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDetectedIssueSeverity
     */
    public $severity = null;

    /**
     * Indicates the patient whose record the detected issue is associated with.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $identifiedDateTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $identifiedPeriod = null;

    /**
     * Individual or device responsible for the issue being raised.  For example, a decision support application or a pharmacist conducting a medication review.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $author = null;

    /**
     * Indicates the resource representing the current activity or proposed activity that is potentially problematic.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $implicated = [];

    /**
     * Supporting evidence or manifestations that provide the basis for identifying the detected issue such as a GuidanceResponse or MeasureReport.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDetectedIssue\FHIRDetectedIssueEvidence[]
     */
    public $evidence = [];

    /**
     * A textual explanation of the detected issue.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $detail = null;

    /**
     * The literature, knowledge-base or similar reference that describes the propensity for the detected issue identified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $reference = null;

    /**
     * Indicates an action that has been taken or is committed to reduce or eliminate the likelihood of the risk identified by the detected issue from manifesting.  Can also reflect an observation of known mitigating factors that may reduce/eliminate the need for any action.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDetectedIssue\FHIRDetectedIssueMitigation[]
     */
    public $mitigation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'DetectedIssue';

    /**
     * Business identifier associated with the detected issue record.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifier associated with the detected issue record.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Indicates the status of the detected issue.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Indicates the status of the detected issue.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRObservationStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Identifies the general type of issue identified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Identifies the general type of issue identified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Indicates the degree of importance associated with the identified issue based on the potential impact on the patient.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDetectedIssueSeverity
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Indicates the degree of importance associated with the identified issue based on the potential impact on the patient.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDetectedIssueSeverity $severity
     * @return $this
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
        return $this;
    }

    /**
     * Indicates the patient whose record the detected issue is associated with.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * Indicates the patient whose record the detected issue is associated with.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getIdentifiedDateTime()
    {
        return $this->identifiedDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $identifiedDateTime
     * @return $this
     */
    public function setIdentifiedDateTime($identifiedDateTime)
    {
        $this->identifiedDateTime = $identifiedDateTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getIdentifiedPeriod()
    {
        return $this->identifiedPeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $identifiedPeriod
     * @return $this
     */
    public function setIdentifiedPeriod($identifiedPeriod)
    {
        $this->identifiedPeriod = $identifiedPeriod;
        return $this;
    }

    /**
     * Individual or device responsible for the issue being raised.  For example, a decision support application or a pharmacist conducting a medication review.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Individual or device responsible for the issue being raised.  For example, a decision support application or a pharmacist conducting a medication review.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $author
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Indicates the resource representing the current activity or proposed activity that is potentially problematic.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getImplicated()
    {
        return $this->implicated;
    }

    /**
     * Indicates the resource representing the current activity or proposed activity that is potentially problematic.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $implicated
     * @return $this
     */
    public function addImplicated($implicated)
    {
        $this->implicated[] = $implicated;
        return $this;
    }

    /**
     * Supporting evidence or manifestations that provide the basis for identifying the detected issue such as a GuidanceResponse or MeasureReport.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDetectedIssue\FHIRDetectedIssueEvidence[]
     */
    public function getEvidence()
    {
        return $this->evidence;
    }

    /**
     * Supporting evidence or manifestations that provide the basis for identifying the detected issue such as a GuidanceResponse or MeasureReport.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDetectedIssue\FHIRDetectedIssueEvidence $evidence
     * @return $this
     */
    public function addEvidence($evidence)
    {
        $this->evidence[] = $evidence;
        return $this;
    }

    /**
     * A textual explanation of the detected issue.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * A textual explanation of the detected issue.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $detail
     * @return $this
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
        return $this;
    }

    /**
     * The literature, knowledge-base or similar reference that describes the propensity for the detected issue identified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * The literature, knowledge-base or similar reference that describes the propensity for the detected issue identified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $reference
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Indicates an action that has been taken or is committed to reduce or eliminate the likelihood of the risk identified by the detected issue from manifesting.  Can also reflect an observation of known mitigating factors that may reduce/eliminate the need for any action.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDetectedIssue\FHIRDetectedIssueMitigation[]
     */
    public function getMitigation()
    {
        return $this->mitigation;
    }

    /**
     * Indicates an action that has been taken or is committed to reduce or eliminate the likelihood of the risk identified by the detected issue from manifesting.  Can also reflect an observation of known mitigating factors that may reduce/eliminate the need for any action.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDetectedIssue\FHIRDetectedIssueMitigation $mitigation
     * @return $this
     */
    public function addMitigation($mitigation)
    {
        $this->mitigation[] = $mitigation;
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['severity'])) {
                $this->setSeverity($data['severity']);
            }
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
            }
            if (isset($data['identifiedDateTime'])) {
                $this->setIdentifiedDateTime($data['identifiedDateTime']);
            }
            if (isset($data['identifiedPeriod'])) {
                $this->setIdentifiedPeriod($data['identifiedPeriod']);
            }
            if (isset($data['author'])) {
                $this->setAuthor($data['author']);
            }
            if (isset($data['implicated'])) {
                if (is_array($data['implicated'])) {
                    foreach ($data['implicated'] as $d) {
                        $this->addImplicated($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"implicated" must be array of objects or null, ' . gettype($data['implicated']) . ' seen.');
                }
            }
            if (isset($data['evidence'])) {
                if (is_array($data['evidence'])) {
                    foreach ($data['evidence'] as $d) {
                        $this->addEvidence($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"evidence" must be array of objects or null, ' . gettype($data['evidence']) . ' seen.');
                }
            }
            if (isset($data['detail'])) {
                $this->setDetail($data['detail']);
            }
            if (isset($data['reference'])) {
                $this->setReference($data['reference']);
            }
            if (isset($data['mitigation'])) {
                if (is_array($data['mitigation'])) {
                    foreach ($data['mitigation'] as $d) {
                        $this->addMitigation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"mitigation" must be array of objects or null, ' . gettype($data['mitigation']) . ' seen.');
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->severity)) {
            $json['severity'] = $this->severity;
        }
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
        }
        if (isset($this->identifiedDateTime)) {
            $json['identifiedDateTime'] = $this->identifiedDateTime;
        }
        if (isset($this->identifiedPeriod)) {
            $json['identifiedPeriod'] = $this->identifiedPeriod;
        }
        if (isset($this->author)) {
            $json['author'] = $this->author;
        }
        if (0 < count($this->implicated)) {
            $json['implicated'] = [];
            foreach ($this->implicated as $implicated) {
                $json['implicated'][] = $implicated;
            }
        }
        if (0 < count($this->evidence)) {
            $json['evidence'] = [];
            foreach ($this->evidence as $evidence) {
                $json['evidence'][] = $evidence;
            }
        }
        if (isset($this->detail)) {
            $json['detail'] = $this->detail;
        }
        if (isset($this->reference)) {
            $json['reference'] = $this->reference;
        }
        if (0 < count($this->mitigation)) {
            $json['mitigation'] = [];
            foreach ($this->mitigation as $mitigation) {
                $json['mitigation'][] = $mitigation;
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
            $sxe = new \SimpleXMLElement('<DetectedIssue xmlns="http://hl7.org/fhir"></DetectedIssue>');
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
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->severity)) {
            $this->severity->xmlSerialize(true, $sxe->addChild('severity'));
        }
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (isset($this->identifiedDateTime)) {
            $this->identifiedDateTime->xmlSerialize(true, $sxe->addChild('identifiedDateTime'));
        }
        if (isset($this->identifiedPeriod)) {
            $this->identifiedPeriod->xmlSerialize(true, $sxe->addChild('identifiedPeriod'));
        }
        if (isset($this->author)) {
            $this->author->xmlSerialize(true, $sxe->addChild('author'));
        }
        if (0 < count($this->implicated)) {
            foreach ($this->implicated as $implicated) {
                $implicated->xmlSerialize(true, $sxe->addChild('implicated'));
            }
        }
        if (0 < count($this->evidence)) {
            foreach ($this->evidence as $evidence) {
                $evidence->xmlSerialize(true, $sxe->addChild('evidence'));
            }
        }
        if (isset($this->detail)) {
            $this->detail->xmlSerialize(true, $sxe->addChild('detail'));
        }
        if (isset($this->reference)) {
            $this->reference->xmlSerialize(true, $sxe->addChild('reference'));
        }
        if (0 < count($this->mitigation)) {
            foreach ($this->mitigation as $mitigation) {
                $mitigation->xmlSerialize(true, $sxe->addChild('mitigation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
