<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRPaymentReconciliation;

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
 * This resource provides the details including amount of a payment and allocates the payment items being paid.
 */
class FHIRPaymentReconciliationDetail extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Unique identifier for the current payment item for the referenced payable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * Unique identifier for the prior payment item for the referenced payable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $predecessor = null;

    /**
     * Code to indicate the nature of the payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * A resource, such as a Claim, the evaluation of which could lead to payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $request = null;

    /**
     * The party which submitted the claim or financial transaction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $submitter = null;

    /**
     * A resource, such as a ClaimResponse, which contains a commitment to payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $response = null;

    /**
     * The date from the response resource containing a commitment to pay.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $date = null;

    /**
     * A reference to the individual who is responsible for inquiries regarding the response and its payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $responsible = null;

    /**
     * The party which is receiving the payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $payee = null;

    /**
     * The monetary amount allocated from the total payment to the payable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $amount = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'PaymentReconciliation.Detail';

    /**
     * Unique identifier for the current payment item for the referenced payable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique identifier for the current payment item for the referenced payable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Unique identifier for the prior payment item for the referenced payable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getPredecessor()
    {
        return $this->predecessor;
    }

    /**
     * Unique identifier for the prior payment item for the referenced payable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $predecessor
     * @return $this
     */
    public function setPredecessor($predecessor)
    {
        $this->predecessor = $predecessor;
        return $this;
    }

    /**
     * Code to indicate the nature of the payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Code to indicate the nature of the payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A resource, such as a Claim, the evaluation of which could lead to payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * A resource, such as a Claim, the evaluation of which could lead to payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * The party which submitted the claim or financial transaction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubmitter()
    {
        return $this->submitter;
    }

    /**
     * The party which submitted the claim or financial transaction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $submitter
     * @return $this
     */
    public function setSubmitter($submitter)
    {
        $this->submitter = $submitter;
        return $this;
    }

    /**
     * A resource, such as a ClaimResponse, which contains a commitment to payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * A resource, such as a ClaimResponse, which contains a commitment to payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * The date from the response resource containing a commitment to pay.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date from the response resource containing a commitment to pay.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * A reference to the individual who is responsible for inquiries regarding the response and its payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getResponsible()
    {
        return $this->responsible;
    }

    /**
     * A reference to the individual who is responsible for inquiries regarding the response and its payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $responsible
     * @return $this
     */
    public function setResponsible($responsible)
    {
        $this->responsible = $responsible;
        return $this;
    }

    /**
     * The party which is receiving the payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPayee()
    {
        return $this->payee;
    }

    /**
     * The party which is receiving the payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $payee
     * @return $this
     */
    public function setPayee($payee)
    {
        $this->payee = $payee;
        return $this;
    }

    /**
     * The monetary amount allocated from the total payment to the payable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * The monetary amount allocated from the total payment to the payable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['predecessor'])) {
                $this->setPredecessor($data['predecessor']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['request'])) {
                $this->setRequest($data['request']);
            }
            if (isset($data['submitter'])) {
                $this->setSubmitter($data['submitter']);
            }
            if (isset($data['response'])) {
                $this->setResponse($data['response']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['responsible'])) {
                $this->setResponsible($data['responsible']);
            }
            if (isset($data['payee'])) {
                $this->setPayee($data['payee']);
            }
            if (isset($data['amount'])) {
                $this->setAmount($data['amount']);
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->predecessor)) {
            $json['predecessor'] = $this->predecessor;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->request)) {
            $json['request'] = $this->request;
        }
        if (isset($this->submitter)) {
            $json['submitter'] = $this->submitter;
        }
        if (isset($this->response)) {
            $json['response'] = $this->response;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (isset($this->responsible)) {
            $json['responsible'] = $this->responsible;
        }
        if (isset($this->payee)) {
            $json['payee'] = $this->payee;
        }
        if (isset($this->amount)) {
            $json['amount'] = $this->amount;
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
            $sxe = new \SimpleXMLElement('<PaymentReconciliationDetail xmlns="http://hl7.org/fhir"></PaymentReconciliationDetail>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->predecessor)) {
            $this->predecessor->xmlSerialize(true, $sxe->addChild('predecessor'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->request)) {
            $this->request->xmlSerialize(true, $sxe->addChild('request'));
        }
        if (isset($this->submitter)) {
            $this->submitter->xmlSerialize(true, $sxe->addChild('submitter'));
        }
        if (isset($this->response)) {
            $this->response->xmlSerialize(true, $sxe->addChild('response'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->responsible)) {
            $this->responsible->xmlSerialize(true, $sxe->addChild('responsible'));
        }
        if (isset($this->payee)) {
            $this->payee->xmlSerialize(true, $sxe->addChild('payee'));
        }
        if (isset($this->amount)) {
            $this->amount->xmlSerialize(true, $sxe->addChild('amount'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
