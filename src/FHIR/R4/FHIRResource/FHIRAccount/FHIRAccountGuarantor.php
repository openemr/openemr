<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRAccount;

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
 * A financial tool for tracking value accrued for a particular purpose.  In the healthcare field, used to track charges for a patient, cost centers, etc.
 */
class FHIRAccountGuarantor extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The entity who is responsible.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $party = null;

    /**
     * A guarantor may be placed on credit hold or otherwise have their role temporarily suspended.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $onHold = null;

    /**
     * The timeframe during which the guarantor accepts responsibility for the account.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Account.Guarantor';

    /**
     * The entity who is responsible.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * The entity who is responsible.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $party
     * @return $this
     */
    public function setParty($party)
    {
        $this->party = $party;
        return $this;
    }

    /**
     * A guarantor may be placed on credit hold or otherwise have their role temporarily suspended.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getOnHold()
    {
        return $this->onHold;
    }

    /**
     * A guarantor may be placed on credit hold or otherwise have their role temporarily suspended.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $onHold
     * @return $this
     */
    public function setOnHold($onHold)
    {
        $this->onHold = $onHold;
        return $this;
    }

    /**
     * The timeframe during which the guarantor accepts responsibility for the account.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The timeframe during which the guarantor accepts responsibility for the account.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
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
            if (isset($data['party'])) {
                $this->setParty($data['party']);
            }
            if (isset($data['onHold'])) {
                $this->setOnHold($data['onHold']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
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
        if (isset($this->party)) {
            $json['party'] = $this->party;
        }
        if (isset($this->onHold)) {
            $json['onHold'] = $this->onHold;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
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
            $sxe = new \SimpleXMLElement('<AccountGuarantor xmlns="http://hl7.org/fhir"></AccountGuarantor>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->party)) {
            $this->party->xmlSerialize(true, $sxe->addChild('party'));
        }
        if (isset($this->onHold)) {
            $this->onHold->xmlSerialize(true, $sxe->addChild('onHold'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
