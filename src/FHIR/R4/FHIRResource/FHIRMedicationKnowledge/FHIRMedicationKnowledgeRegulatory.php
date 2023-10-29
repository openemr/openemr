<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge;

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
 * Information about a medication that is used to support knowledge.
 */
class FHIRMedicationKnowledgeRegulatory extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The authority that is specifying the regulations.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $regulatoryAuthority = null;

    /**
     * Specifies if changes are allowed when dispensing a medication from a regulatory perspective.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSubstitution[]
     */
    public $substitution = [];

    /**
     * Specifies the schedule of a medication in jurisdiction.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSchedule[]
     */
    public $schedule = [];

    /**
     * The maximum number of units of the medication that can be dispensed in a period.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMaxDispense
     */
    public $maxDispense = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationKnowledge.Regulatory';

    /**
     * The authority that is specifying the regulations.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRegulatoryAuthority()
    {
        return $this->regulatoryAuthority;
    }

    /**
     * The authority that is specifying the regulations.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $regulatoryAuthority
     * @return $this
     */
    public function setRegulatoryAuthority($regulatoryAuthority)
    {
        $this->regulatoryAuthority = $regulatoryAuthority;
        return $this;
    }

    /**
     * Specifies if changes are allowed when dispensing a medication from a regulatory perspective.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSubstitution[]
     */
    public function getSubstitution()
    {
        return $this->substitution;
    }

    /**
     * Specifies if changes are allowed when dispensing a medication from a regulatory perspective.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSubstitution $substitution
     * @return $this
     */
    public function addSubstitution($substitution)
    {
        $this->substitution[] = $substitution;
        return $this;
    }

    /**
     * Specifies the schedule of a medication in jurisdiction.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSchedule[]
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Specifies the schedule of a medication in jurisdiction.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeSchedule $schedule
     * @return $this
     */
    public function addSchedule($schedule)
    {
        $this->schedule[] = $schedule;
        return $this;
    }

    /**
     * The maximum number of units of the medication that can be dispensed in a period.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMaxDispense
     */
    public function getMaxDispense()
    {
        return $this->maxDispense;
    }

    /**
     * The maximum number of units of the medication that can be dispensed in a period.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge\FHIRMedicationKnowledgeMaxDispense $maxDispense
     * @return $this
     */
    public function setMaxDispense($maxDispense)
    {
        $this->maxDispense = $maxDispense;
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
            if (isset($data['regulatoryAuthority'])) {
                $this->setRegulatoryAuthority($data['regulatoryAuthority']);
            }
            if (isset($data['substitution'])) {
                if (is_array($data['substitution'])) {
                    foreach ($data['substitution'] as $d) {
                        $this->addSubstitution($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"substitution" must be array of objects or null, ' . gettype($data['substitution']) . ' seen.');
                }
            }
            if (isset($data['schedule'])) {
                if (is_array($data['schedule'])) {
                    foreach ($data['schedule'] as $d) {
                        $this->addSchedule($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"schedule" must be array of objects or null, ' . gettype($data['schedule']) . ' seen.');
                }
            }
            if (isset($data['maxDispense'])) {
                $this->setMaxDispense($data['maxDispense']);
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
        if (isset($this->regulatoryAuthority)) {
            $json['regulatoryAuthority'] = $this->regulatoryAuthority;
        }
        if (0 < count($this->substitution)) {
            $json['substitution'] = [];
            foreach ($this->substitution as $substitution) {
                $json['substitution'][] = $substitution;
            }
        }
        if (0 < count($this->schedule)) {
            $json['schedule'] = [];
            foreach ($this->schedule as $schedule) {
                $json['schedule'][] = $schedule;
            }
        }
        if (isset($this->maxDispense)) {
            $json['maxDispense'] = $this->maxDispense;
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
            $sxe = new \SimpleXMLElement('<MedicationKnowledgeRegulatory xmlns="http://hl7.org/fhir"></MedicationKnowledgeRegulatory>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->regulatoryAuthority)) {
            $this->regulatoryAuthority->xmlSerialize(true, $sxe->addChild('regulatoryAuthority'));
        }
        if (0 < count($this->substitution)) {
            foreach ($this->substitution as $substitution) {
                $substitution->xmlSerialize(true, $sxe->addChild('substitution'));
            }
        }
        if (0 < count($this->schedule)) {
            foreach ($this->schedule as $schedule) {
                $schedule->xmlSerialize(true, $sxe->addChild('schedule'));
            }
        }
        if (isset($this->maxDispense)) {
            $this->maxDispense->xmlSerialize(true, $sxe->addChild('maxDispense'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
