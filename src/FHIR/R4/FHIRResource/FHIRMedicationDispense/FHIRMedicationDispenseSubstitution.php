<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationDispense;

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
 * Indicates that a medication product is to be or has been dispensed for a named person/patient.  This includes a description of the medication product (supply) provided and the instructions for administering the medication.  The medication dispense is the result of a pharmacy system responding to a medication order.
 */
class FHIRMedicationDispenseSubstitution extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * True if the dispenser dispensed a different drug or product from what was prescribed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $wasSubstituted = null;

    /**
     * A code signifying whether a different drug was dispensed from what was prescribed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Indicates the reason for the substitution (or lack of substitution) from what was prescribed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $reason = [];

    /**
     * The person or organization that has primary responsibility for the substitution.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $responsibleParty = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationDispense.Substitution';

    /**
     * True if the dispenser dispensed a different drug or product from what was prescribed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getWasSubstituted()
    {
        return $this->wasSubstituted;
    }

    /**
     * True if the dispenser dispensed a different drug or product from what was prescribed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $wasSubstituted
     * @return $this
     */
    public function setWasSubstituted($wasSubstituted)
    {
        $this->wasSubstituted = $wasSubstituted;
        return $this;
    }

    /**
     * A code signifying whether a different drug was dispensed from what was prescribed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A code signifying whether a different drug was dispensed from what was prescribed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Indicates the reason for the substitution (or lack of substitution) from what was prescribed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Indicates the reason for the substitution (or lack of substitution) from what was prescribed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $reason
     * @return $this
     */
    public function addReason($reason)
    {
        $this->reason[] = $reason;
        return $this;
    }

    /**
     * The person or organization that has primary responsibility for the substitution.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getResponsibleParty()
    {
        return $this->responsibleParty;
    }

    /**
     * The person or organization that has primary responsibility for the substitution.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $responsibleParty
     * @return $this
     */
    public function addResponsibleParty($responsibleParty)
    {
        $this->responsibleParty[] = $responsibleParty;
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
            if (isset($data['wasSubstituted'])) {
                $this->setWasSubstituted($data['wasSubstituted']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['reason'])) {
                if (is_array($data['reason'])) {
                    foreach ($data['reason'] as $d) {
                        $this->addReason($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reason" must be array of objects or null, ' . gettype($data['reason']) . ' seen.');
                }
            }
            if (isset($data['responsibleParty'])) {
                if (is_array($data['responsibleParty'])) {
                    foreach ($data['responsibleParty'] as $d) {
                        $this->addResponsibleParty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"responsibleParty" must be array of objects or null, ' . gettype($data['responsibleParty']) . ' seen.');
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
        if (isset($this->wasSubstituted)) {
            $json['wasSubstituted'] = $this->wasSubstituted;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->reason)) {
            $json['reason'] = [];
            foreach ($this->reason as $reason) {
                $json['reason'][] = $reason;
            }
        }
        if (0 < count($this->responsibleParty)) {
            $json['responsibleParty'] = [];
            foreach ($this->responsibleParty as $responsibleParty) {
                $json['responsibleParty'][] = $responsibleParty;
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
            $sxe = new \SimpleXMLElement('<MedicationDispenseSubstitution xmlns="http://hl7.org/fhir"></MedicationDispenseSubstitution>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->wasSubstituted)) {
            $this->wasSubstituted->xmlSerialize(true, $sxe->addChild('wasSubstituted'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->reason)) {
            foreach ($this->reason as $reason) {
                $reason->xmlSerialize(true, $sxe->addChild('reason'));
            }
        }
        if (0 < count($this->responsibleParty)) {
            foreach ($this->responsibleParty as $responsibleParty) {
                $responsibleParty->xmlSerialize(true, $sxe->addChild('responsibleParty'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
