<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIREncounter;

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
 * An interaction between a patient and healthcare provider(s) for the purpose of providing healthcare service(s) or assessing the health status of a patient.
 */
class FHIREncounterParticipant extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Role of participant in encounter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $type = [];

    /**
     * The period of time that the specified participant participated in the encounter. These can overlap or be sub-sets of the overall encounter's period.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * Persons involved in the encounter other than the patient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $individual = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Encounter.Participant';

    /**
     * Role of participant in encounter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Role of participant in encounter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * The period of time that the specified participant participated in the encounter. These can overlap or be sub-sets of the overall encounter's period.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The period of time that the specified participant participated in the encounter. These can overlap or be sub-sets of the overall encounter's period.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * Persons involved in the encounter other than the patient.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getIndividual()
    {
        return $this->individual;
    }

    /**
     * Persons involved in the encounter other than the patient.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $individual
     * @return $this
     */
    public function setIndividual($individual)
    {
        $this->individual = $individual;
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
            if (isset($data['type'])) {
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, ' . gettype($data['type']) . ' seen.');
                }
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['individual'])) {
                $this->setIndividual($data['individual']);
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
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->individual)) {
            $json['individual'] = $this->individual;
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
            $sxe = new \SimpleXMLElement('<EncounterParticipant xmlns="http://hl7.org/fhir"></EncounterParticipant>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->individual)) {
            $this->individual->xmlSerialize(true, $sxe->addChild('individual'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
