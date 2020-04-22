<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit;

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
 * This resource provides: the claim details; adjudication details from the processing of a Claim; and optionally account balance information, for informing the subscriber of the benefits provided.
 */
class FHIRExplanationOfBenefitPayment extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Whether this represents partial or complete payment of the benefits payable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Total amount of all adjustments to this payment included in this transaction which are not related to this claim's adjudication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $adjustment = null;

    /**
     * Reason for the payment adjustment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $adjustmentReason = null;

    /**
     * Estimated date the payment will be issued or the actual issue date of payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $date = null;

    /**
     * Benefits payable less any payment adjustment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $amount = null;

    /**
     * Issuer's unique identifier for the payment instrument.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ExplanationOfBenefit.Payment';

    /**
     * Whether this represents partial or complete payment of the benefits payable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Whether this represents partial or complete payment of the benefits payable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Total amount of all adjustments to this payment included in this transaction which are not related to this claim's adjudication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getAdjustment()
    {
        return $this->adjustment;
    }

    /**
     * Total amount of all adjustments to this payment included in this transaction which are not related to this claim's adjudication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $adjustment
     * @return $this
     */
    public function setAdjustment($adjustment)
    {
        $this->adjustment = $adjustment;
        return $this;
    }

    /**
     * Reason for the payment adjustment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getAdjustmentReason()
    {
        return $this->adjustmentReason;
    }

    /**
     * Reason for the payment adjustment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $adjustmentReason
     * @return $this
     */
    public function setAdjustmentReason($adjustmentReason)
    {
        $this->adjustmentReason = $adjustmentReason;
        return $this;
    }

    /**
     * Estimated date the payment will be issued or the actual issue date of payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Estimated date the payment will be issued or the actual issue date of payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Benefits payable less any payment adjustment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Benefits payable less any payment adjustment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Issuer's unique identifier for the payment instrument.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Issuer's unique identifier for the payment instrument.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
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
                $this->setType($data['type']);
            }
            if (isset($data['adjustment'])) {
                $this->setAdjustment($data['adjustment']);
            }
            if (isset($data['adjustmentReason'])) {
                $this->setAdjustmentReason($data['adjustmentReason']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['amount'])) {
                $this->setAmount($data['amount']);
            }
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->adjustment)) {
            $json['adjustment'] = $this->adjustment;
        }
        if (isset($this->adjustmentReason)) {
            $json['adjustmentReason'] = $this->adjustmentReason;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (isset($this->amount)) {
            $json['amount'] = $this->amount;
        }
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
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
            $sxe = new \SimpleXMLElement('<ExplanationOfBenefitPayment xmlns="http://hl7.org/fhir"></ExplanationOfBenefitPayment>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->adjustment)) {
            $this->adjustment->xmlSerialize(true, $sxe->addChild('adjustment'));
        }
        if (isset($this->adjustmentReason)) {
            $this->adjustmentReason->xmlSerialize(true, $sxe->addChild('adjustmentReason'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->amount)) {
            $this->amount->xmlSerialize(true, $sxe->addChild('amount'));
        }
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
