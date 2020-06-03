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
 * Describes a comparison of an immunization event against published recommendations to determine if the administration is "valid" in relation to those  recommendations.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRImmunizationEvaluation extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A unique identifier assigned to this immunization evaluation record.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Indicates the current status of the evaluation of the vaccination administration event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationEvaluationStatusCodes
     */
    public $status = null;

    /**
     * The individual for whom the evaluation is being done.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * The date the evaluation of the vaccine administration event was performed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * Indicates the authority who published the protocol (e.g. ACIP).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $authority = null;

    /**
     * The vaccine preventable disease the dose is being evaluated against.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $targetDisease = null;

    /**
     * The vaccine administration event being evaluated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $immunizationEvent = null;

    /**
     * Indicates if the dose is valid or not valid with respect to the published recommendations.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $doseStatus = null;

    /**
     * Provides an explanation as to why the vaccine administration event is valid or not relative to the published recommendations.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $doseStatusReason = [];

    /**
     * Additional information about the evaluation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $series = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public $doseNumberPositiveInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $doseNumberString = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public $seriesDosesPositiveInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $seriesDosesString = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ImmunizationEvaluation';

    /**
     * A unique identifier assigned to this immunization evaluation record.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A unique identifier assigned to this immunization evaluation record.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Indicates the current status of the evaluation of the vaccination administration event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationEvaluationStatusCodes
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Indicates the current status of the evaluation of the vaccination administration event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRImmunizationEvaluationStatusCodes $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The individual for whom the evaluation is being done.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * The individual for whom the evaluation is being done.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * The date the evaluation of the vaccine administration event was performed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date the evaluation of the vaccine administration event was performed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Indicates the authority who published the protocol (e.g. ACIP).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * Indicates the authority who published the protocol (e.g. ACIP).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $authority
     * @return $this
     */
    public function setAuthority($authority)
    {
        $this->authority = $authority;
        return $this;
    }

    /**
     * The vaccine preventable disease the dose is being evaluated against.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getTargetDisease()
    {
        return $this->targetDisease;
    }

    /**
     * The vaccine preventable disease the dose is being evaluated against.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $targetDisease
     * @return $this
     */
    public function setTargetDisease($targetDisease)
    {
        $this->targetDisease = $targetDisease;
        return $this;
    }

    /**
     * The vaccine administration event being evaluated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getImmunizationEvent()
    {
        return $this->immunizationEvent;
    }

    /**
     * The vaccine administration event being evaluated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $immunizationEvent
     * @return $this
     */
    public function setImmunizationEvent($immunizationEvent)
    {
        $this->immunizationEvent = $immunizationEvent;
        return $this;
    }

    /**
     * Indicates if the dose is valid or not valid with respect to the published recommendations.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDoseStatus()
    {
        return $this->doseStatus;
    }

    /**
     * Indicates if the dose is valid or not valid with respect to the published recommendations.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $doseStatus
     * @return $this
     */
    public function setDoseStatus($doseStatus)
    {
        $this->doseStatus = $doseStatus;
        return $this;
    }

    /**
     * Provides an explanation as to why the vaccine administration event is valid or not relative to the published recommendations.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getDoseStatusReason()
    {
        return $this->doseStatusReason;
    }

    /**
     * Provides an explanation as to why the vaccine administration event is valid or not relative to the published recommendations.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $doseStatusReason
     * @return $this
     */
    public function addDoseStatusReason($doseStatusReason)
    {
        $this->doseStatusReason[] = $doseStatusReason;
        return $this;
    }

    /**
     * Additional information about the evaluation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Additional information about the evaluation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $series
     * @return $this
     */
    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public function getDoseNumberPositiveInt()
    {
        return $this->doseNumberPositiveInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $doseNumberPositiveInt
     * @return $this
     */
    public function setDoseNumberPositiveInt($doseNumberPositiveInt)
    {
        $this->doseNumberPositiveInt = $doseNumberPositiveInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDoseNumberString()
    {
        return $this->doseNumberString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $doseNumberString
     * @return $this
     */
    public function setDoseNumberString($doseNumberString)
    {
        $this->doseNumberString = $doseNumberString;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public function getSeriesDosesPositiveInt()
    {
        return $this->seriesDosesPositiveInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $seriesDosesPositiveInt
     * @return $this
     */
    public function setSeriesDosesPositiveInt($seriesDosesPositiveInt)
    {
        $this->seriesDosesPositiveInt = $seriesDosesPositiveInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSeriesDosesString()
    {
        return $this->seriesDosesString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $seriesDosesString
     * @return $this
     */
    public function setSeriesDosesString($seriesDosesString)
    {
        $this->seriesDosesString = $seriesDosesString;
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
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['authority'])) {
                $this->setAuthority($data['authority']);
            }
            if (isset($data['targetDisease'])) {
                $this->setTargetDisease($data['targetDisease']);
            }
            if (isset($data['immunizationEvent'])) {
                $this->setImmunizationEvent($data['immunizationEvent']);
            }
            if (isset($data['doseStatus'])) {
                $this->setDoseStatus($data['doseStatus']);
            }
            if (isset($data['doseStatusReason'])) {
                if (is_array($data['doseStatusReason'])) {
                    foreach ($data['doseStatusReason'] as $d) {
                        $this->addDoseStatusReason($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"doseStatusReason" must be array of objects or null, ' . gettype($data['doseStatusReason']) . ' seen.');
                }
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['series'])) {
                $this->setSeries($data['series']);
            }
            if (isset($data['doseNumberPositiveInt'])) {
                $this->setDoseNumberPositiveInt($data['doseNumberPositiveInt']);
            }
            if (isset($data['doseNumberString'])) {
                $this->setDoseNumberString($data['doseNumberString']);
            }
            if (isset($data['seriesDosesPositiveInt'])) {
                $this->setSeriesDosesPositiveInt($data['seriesDosesPositiveInt']);
            }
            if (isset($data['seriesDosesString'])) {
                $this->setSeriesDosesString($data['seriesDosesString']);
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
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (isset($this->authority)) {
            $json['authority'] = $this->authority;
        }
        if (isset($this->targetDisease)) {
            $json['targetDisease'] = $this->targetDisease;
        }
        if (isset($this->immunizationEvent)) {
            $json['immunizationEvent'] = $this->immunizationEvent;
        }
        if (isset($this->doseStatus)) {
            $json['doseStatus'] = $this->doseStatus;
        }
        if (0 < count($this->doseStatusReason)) {
            $json['doseStatusReason'] = [];
            foreach ($this->doseStatusReason as $doseStatusReason) {
                $json['doseStatusReason'][] = $doseStatusReason;
            }
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->series)) {
            $json['series'] = $this->series;
        }
        if (isset($this->doseNumberPositiveInt)) {
            $json['doseNumberPositiveInt'] = $this->doseNumberPositiveInt;
        }
        if (isset($this->doseNumberString)) {
            $json['doseNumberString'] = $this->doseNumberString;
        }
        if (isset($this->seriesDosesPositiveInt)) {
            $json['seriesDosesPositiveInt'] = $this->seriesDosesPositiveInt;
        }
        if (isset($this->seriesDosesString)) {
            $json['seriesDosesString'] = $this->seriesDosesString;
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
            $sxe = new \SimpleXMLElement('<ImmunizationEvaluation xmlns="http://hl7.org/fhir"></ImmunizationEvaluation>');
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
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->authority)) {
            $this->authority->xmlSerialize(true, $sxe->addChild('authority'));
        }
        if (isset($this->targetDisease)) {
            $this->targetDisease->xmlSerialize(true, $sxe->addChild('targetDisease'));
        }
        if (isset($this->immunizationEvent)) {
            $this->immunizationEvent->xmlSerialize(true, $sxe->addChild('immunizationEvent'));
        }
        if (isset($this->doseStatus)) {
            $this->doseStatus->xmlSerialize(true, $sxe->addChild('doseStatus'));
        }
        if (0 < count($this->doseStatusReason)) {
            foreach ($this->doseStatusReason as $doseStatusReason) {
                $doseStatusReason->xmlSerialize(true, $sxe->addChild('doseStatusReason'));
            }
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->series)) {
            $this->series->xmlSerialize(true, $sxe->addChild('series'));
        }
        if (isset($this->doseNumberPositiveInt)) {
            $this->doseNumberPositiveInt->xmlSerialize(true, $sxe->addChild('doseNumberPositiveInt'));
        }
        if (isset($this->doseNumberString)) {
            $this->doseNumberString->xmlSerialize(true, $sxe->addChild('doseNumberString'));
        }
        if (isset($this->seriesDosesPositiveInt)) {
            $this->seriesDosesPositiveInt->xmlSerialize(true, $sxe->addChild('seriesDosesPositiveInt'));
        }
        if (isset($this->seriesDosesString)) {
            $this->seriesDosesString->xmlSerialize(true, $sxe->addChild('seriesDosesString'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
