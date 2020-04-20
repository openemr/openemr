<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRNutritionOrder;

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
 * A request to supply a diet, formula feeding (enteral) or oral nutritional supplement to a patient/resident.
 */
class FHIRNutritionOrderAdministration extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The time period and frequency at which the enteral formula should be delivered to the patient.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public $schedule = null;

    /**
     * The volume of formula to provide to the patient per the specified administration schedule.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $rateQuantity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public $rateRatio = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'NutritionOrder.Administration';

    /**
     * The time period and frequency at which the enteral formula should be delivered to the patient.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * The time period and frequency at which the enteral formula should be delivered to the patient.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming $schedule
     * @return $this
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;
        return $this;
    }

    /**
     * The volume of formula to provide to the patient per the specified administration schedule.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The volume of formula to provide to the patient per the specified administration schedule.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getRateQuantity()
    {
        return $this->rateQuantity;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $rateQuantity
     * @return $this
     */
    public function setRateQuantity($rateQuantity)
    {
        $this->rateQuantity = $rateQuantity;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getRateRatio()
    {
        return $this->rateRatio;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $rateRatio
     * @return $this
     */
    public function setRateRatio($rateRatio)
    {
        $this->rateRatio = $rateRatio;
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
            if (isset($data['schedule'])) {
                $this->setSchedule($data['schedule']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['rateQuantity'])) {
                $this->setRateQuantity($data['rateQuantity']);
            }
            if (isset($data['rateRatio'])) {
                $this->setRateRatio($data['rateRatio']);
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
        if (isset($this->schedule)) {
            $json['schedule'] = $this->schedule;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->rateQuantity)) {
            $json['rateQuantity'] = $this->rateQuantity;
        }
        if (isset($this->rateRatio)) {
            $json['rateRatio'] = $this->rateRatio;
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
            $sxe = new \SimpleXMLElement('<NutritionOrderAdministration xmlns="http://hl7.org/fhir"></NutritionOrderAdministration>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->schedule)) {
            $this->schedule->xmlSerialize(true, $sxe->addChild('schedule'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->rateQuantity)) {
            $this->rateQuantity->xmlSerialize(true, $sxe->addChild('rateQuantity'));
        }
        if (isset($this->rateRatio)) {
            $this->rateRatio->xmlSerialize(true, $sxe->addChild('rateRatio'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
