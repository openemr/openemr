<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest;

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
 * An order or request for both supply of the medication and the instructions for administration of the medication to a patient. The resource is called "MedicationRequest" rather than "MedicationPrescription" or "MedicationOrder" to generalize the use across inpatient and outpatient settings, including care plans, etc., and to harmonize with workflow patterns.
 */
class FHIRMedicationRequestDispenseRequest extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Indicates the quantity or duration for the first dispense of the medication.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestInitialFill
     */
    public $initialFill = null;

    /**
     * The minimum period of time that must occur between dispenses of the medication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $dispenseInterval = null;

    /**
     * This indicates the validity period of a prescription (stale dating the Prescription).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $validityPeriod = null;

    /**
     * An integer indicating the number of times, in addition to the original dispense, (aka refills or repeats) that the patient can receive the prescribed medication. Usage Notes: This integer does not include the original order dispense. This means that if an order indicates dispense 30 tablets plus "3 repeats", then the order can be dispensed a total of 4 times and the patient can receive a total of 120 tablets.  A prescriber may explicitly say that zero refills are permitted after the initial dispense.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $numberOfRepeatsAllowed = null;

    /**
     * The amount that is to be dispensed for one fill.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * Identifies the period time over which the supplied product is expected to be used, or the length of time the dispense is expected to last.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $expectedSupplyDuration = null;

    /**
     * Indicates the intended dispensing Organization specified by the prescriber.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $performer = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationRequest.DispenseRequest';

    /**
     * Indicates the quantity or duration for the first dispense of the medication.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestInitialFill
     */
    public function getInitialFill()
    {
        return $this->initialFill;
    }

    /**
     * Indicates the quantity or duration for the first dispense of the medication.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationRequest\FHIRMedicationRequestInitialFill $initialFill
     * @return $this
     */
    public function setInitialFill($initialFill)
    {
        $this->initialFill = $initialFill;
        return $this;
    }

    /**
     * The minimum period of time that must occur between dispenses of the medication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getDispenseInterval()
    {
        return $this->dispenseInterval;
    }

    /**
     * The minimum period of time that must occur between dispenses of the medication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $dispenseInterval
     * @return $this
     */
    public function setDispenseInterval($dispenseInterval)
    {
        $this->dispenseInterval = $dispenseInterval;
        return $this;
    }

    /**
     * This indicates the validity period of a prescription (stale dating the Prescription).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getValidityPeriod()
    {
        return $this->validityPeriod;
    }

    /**
     * This indicates the validity period of a prescription (stale dating the Prescription).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $validityPeriod
     * @return $this
     */
    public function setValidityPeriod($validityPeriod)
    {
        $this->validityPeriod = $validityPeriod;
        return $this;
    }

    /**
     * An integer indicating the number of times, in addition to the original dispense, (aka refills or repeats) that the patient can receive the prescribed medication. Usage Notes: This integer does not include the original order dispense. This means that if an order indicates dispense 30 tablets plus "3 repeats", then the order can be dispensed a total of 4 times and the patient can receive a total of 120 tablets.  A prescriber may explicitly say that zero refills are permitted after the initial dispense.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getNumberOfRepeatsAllowed()
    {
        return $this->numberOfRepeatsAllowed;
    }

    /**
     * An integer indicating the number of times, in addition to the original dispense, (aka refills or repeats) that the patient can receive the prescribed medication. Usage Notes: This integer does not include the original order dispense. This means that if an order indicates dispense 30 tablets plus "3 repeats", then the order can be dispensed a total of 4 times and the patient can receive a total of 120 tablets.  A prescriber may explicitly say that zero refills are permitted after the initial dispense.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $numberOfRepeatsAllowed
     * @return $this
     */
    public function setNumberOfRepeatsAllowed($numberOfRepeatsAllowed)
    {
        $this->numberOfRepeatsAllowed = $numberOfRepeatsAllowed;
        return $this;
    }

    /**
     * The amount that is to be dispensed for one fill.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The amount that is to be dispensed for one fill.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Identifies the period time over which the supplied product is expected to be used, or the length of time the dispense is expected to last.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getExpectedSupplyDuration()
    {
        return $this->expectedSupplyDuration;
    }

    /**
     * Identifies the period time over which the supplied product is expected to be used, or the length of time the dispense is expected to last.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $expectedSupplyDuration
     * @return $this
     */
    public function setExpectedSupplyDuration($expectedSupplyDuration)
    {
        $this->expectedSupplyDuration = $expectedSupplyDuration;
        return $this;
    }

    /**
     * Indicates the intended dispensing Organization specified by the prescriber.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * Indicates the intended dispensing Organization specified by the prescriber.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $performer
     * @return $this
     */
    public function setPerformer($performer)
    {
        $this->performer = $performer;
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
            if (isset($data['initialFill'])) {
                $this->setInitialFill($data['initialFill']);
            }
            if (isset($data['dispenseInterval'])) {
                $this->setDispenseInterval($data['dispenseInterval']);
            }
            if (isset($data['validityPeriod'])) {
                $this->setValidityPeriod($data['validityPeriod']);
            }
            if (isset($data['numberOfRepeatsAllowed'])) {
                $this->setNumberOfRepeatsAllowed($data['numberOfRepeatsAllowed']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['expectedSupplyDuration'])) {
                $this->setExpectedSupplyDuration($data['expectedSupplyDuration']);
            }
            if (isset($data['performer'])) {
                $this->setPerformer($data['performer']);
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
        if (isset($this->initialFill)) {
            $json['initialFill'] = $this->initialFill;
        }
        if (isset($this->dispenseInterval)) {
            $json['dispenseInterval'] = $this->dispenseInterval;
        }
        if (isset($this->validityPeriod)) {
            $json['validityPeriod'] = $this->validityPeriod;
        }
        if (isset($this->numberOfRepeatsAllowed)) {
            $json['numberOfRepeatsAllowed'] = $this->numberOfRepeatsAllowed;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->expectedSupplyDuration)) {
            $json['expectedSupplyDuration'] = $this->expectedSupplyDuration;
        }
        if (isset($this->performer)) {
            $json['performer'] = $this->performer;
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
            $sxe = new \SimpleXMLElement('<MedicationRequestDispenseRequest xmlns="http://hl7.org/fhir"></MedicationRequestDispenseRequest>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->initialFill)) {
            $this->initialFill->xmlSerialize(true, $sxe->addChild('initialFill'));
        }
        if (isset($this->dispenseInterval)) {
            $this->dispenseInterval->xmlSerialize(true, $sxe->addChild('dispenseInterval'));
        }
        if (isset($this->validityPeriod)) {
            $this->validityPeriod->xmlSerialize(true, $sxe->addChild('validityPeriod'));
        }
        if (isset($this->numberOfRepeatsAllowed)) {
            $this->numberOfRepeatsAllowed->xmlSerialize(true, $sxe->addChild('numberOfRepeatsAllowed'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->expectedSupplyDuration)) {
            $this->expectedSupplyDuration->xmlSerialize(true, $sxe->addChild('expectedSupplyDuration'));
        }
        if (isset($this->performer)) {
            $this->performer->xmlSerialize(true, $sxe->addChild('performer'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
