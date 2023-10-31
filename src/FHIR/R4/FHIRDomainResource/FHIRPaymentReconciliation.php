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
 * This resource provides the details including amount of a payment and allocates the payment items being paid.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRPaymentReconciliation extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A unique identifier assigned to this payment reconciliation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The status of the resource instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRFinancialResourceStatusCodes
     */
    public $status = null;

    /**
     * The period of time for which payments have been gathered into this bulk payment for settlement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * The date when the resource was created.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $created = null;

    /**
     * The party who generated the payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $paymentIssuer = null;

    /**
     * Original request resource reference.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $request = null;

    /**
     * The practitioner who is responsible for the services rendered to the patient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $requestor = null;

    /**
     * The outcome of a request for a reconciliation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRemittanceOutcome
     */
    public $outcome = null;

    /**
     * A human readable description of the status of the request for the reconciliation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $disposition = null;

    /**
     * The date of payment as indicated on the financial instrument.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $paymentDate = null;

    /**
     * Total payment amount as indicated on the financial instrument.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $paymentAmount = null;

    /**
     * Issuer's unique identifier for the payment instrument.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $paymentIdentifier = null;

    /**
     * Distribution of the payment amount for a previously acknowledged payable.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRPaymentReconciliation\FHIRPaymentReconciliationDetail[]
     */
    public $detail = [];

    /**
     * A code for the form to be used for printing the content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $formCode = null;

    /**
     * A note that describes or explains the processing in a human readable form.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRPaymentReconciliation\FHIRPaymentReconciliationProcessNote[]
     */
    public $processNote = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'PaymentReconciliation';

    /**
     * A unique identifier assigned to this payment reconciliation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A unique identifier assigned to this payment reconciliation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The status of the resource instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRFinancialResourceStatusCodes
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of the resource instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRFinancialResourceStatusCodes $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The period of time for which payments have been gathered into this bulk payment for settlement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The period of time for which payments have been gathered into this bulk payment for settlement.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * The date when the resource was created.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * The date when the resource was created.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * The party who generated the payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPaymentIssuer()
    {
        return $this->paymentIssuer;
    }

    /**
     * The party who generated the payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $paymentIssuer
     * @return $this
     */
    public function setPaymentIssuer($paymentIssuer)
    {
        $this->paymentIssuer = $paymentIssuer;
        return $this;
    }

    /**
     * Original request resource reference.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Original request resource reference.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * The practitioner who is responsible for the services rendered to the patient.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRequestor()
    {
        return $this->requestor;
    }

    /**
     * The practitioner who is responsible for the services rendered to the patient.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $requestor
     * @return $this
     */
    public function setRequestor($requestor)
    {
        $this->requestor = $requestor;
        return $this;
    }

    /**
     * The outcome of a request for a reconciliation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRemittanceOutcome
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * The outcome of a request for a reconciliation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRemittanceOutcome $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * A human readable description of the status of the request for the reconciliation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDisposition()
    {
        return $this->disposition;
    }

    /**
     * A human readable description of the status of the request for the reconciliation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $disposition
     * @return $this
     */
    public function setDisposition($disposition)
    {
        $this->disposition = $disposition;
        return $this;
    }

    /**
     * The date of payment as indicated on the financial instrument.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * The date of payment as indicated on the financial instrument.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $paymentDate
     * @return $this
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    /**
     * Total payment amount as indicated on the financial instrument.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * Total payment amount as indicated on the financial instrument.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $paymentAmount
     * @return $this
     */
    public function setPaymentAmount($paymentAmount)
    {
        $this->paymentAmount = $paymentAmount;
        return $this;
    }

    /**
     * Issuer's unique identifier for the payment instrument.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getPaymentIdentifier()
    {
        return $this->paymentIdentifier;
    }

    /**
     * Issuer's unique identifier for the payment instrument.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $paymentIdentifier
     * @return $this
     */
    public function setPaymentIdentifier($paymentIdentifier)
    {
        $this->paymentIdentifier = $paymentIdentifier;
        return $this;
    }

    /**
     * Distribution of the payment amount for a previously acknowledged payable.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRPaymentReconciliation\FHIRPaymentReconciliationDetail[]
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Distribution of the payment amount for a previously acknowledged payable.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRPaymentReconciliation\FHIRPaymentReconciliationDetail $detail
     * @return $this
     */
    public function addDetail($detail)
    {
        $this->detail[] = $detail;
        return $this;
    }

    /**
     * A code for the form to be used for printing the content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getFormCode()
    {
        return $this->formCode;
    }

    /**
     * A code for the form to be used for printing the content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $formCode
     * @return $this
     */
    public function setFormCode($formCode)
    {
        $this->formCode = $formCode;
        return $this;
    }

    /**
     * A note that describes or explains the processing in a human readable form.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRPaymentReconciliation\FHIRPaymentReconciliationProcessNote[]
     */
    public function getProcessNote()
    {
        return $this->processNote;
    }

    /**
     * A note that describes or explains the processing in a human readable form.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRPaymentReconciliation\FHIRPaymentReconciliationProcessNote $processNote
     * @return $this
     */
    public function addProcessNote($processNote)
    {
        $this->processNote[] = $processNote;
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
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['created'])) {
                $this->setCreated($data['created']);
            }
            if (isset($data['paymentIssuer'])) {
                $this->setPaymentIssuer($data['paymentIssuer']);
            }
            if (isset($data['request'])) {
                $this->setRequest($data['request']);
            }
            if (isset($data['requestor'])) {
                $this->setRequestor($data['requestor']);
            }
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
            }
            if (isset($data['disposition'])) {
                $this->setDisposition($data['disposition']);
            }
            if (isset($data['paymentDate'])) {
                $this->setPaymentDate($data['paymentDate']);
            }
            if (isset($data['paymentAmount'])) {
                $this->setPaymentAmount($data['paymentAmount']);
            }
            if (isset($data['paymentIdentifier'])) {
                $this->setPaymentIdentifier($data['paymentIdentifier']);
            }
            if (isset($data['detail'])) {
                if (is_array($data['detail'])) {
                    foreach ($data['detail'] as $d) {
                        $this->addDetail($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"detail" must be array of objects or null, ' . gettype($data['detail']) . ' seen.');
                }
            }
            if (isset($data['formCode'])) {
                $this->setFormCode($data['formCode']);
            }
            if (isset($data['processNote'])) {
                if (is_array($data['processNote'])) {
                    foreach ($data['processNote'] as $d) {
                        $this->addProcessNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"processNote" must be array of objects or null, ' . gettype($data['processNote']) . ' seen.');
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
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->created)) {
            $json['created'] = $this->created;
        }
        if (isset($this->paymentIssuer)) {
            $json['paymentIssuer'] = $this->paymentIssuer;
        }
        if (isset($this->request)) {
            $json['request'] = $this->request;
        }
        if (isset($this->requestor)) {
            $json['requestor'] = $this->requestor;
        }
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
        }
        if (isset($this->disposition)) {
            $json['disposition'] = $this->disposition;
        }
        if (isset($this->paymentDate)) {
            $json['paymentDate'] = $this->paymentDate;
        }
        if (isset($this->paymentAmount)) {
            $json['paymentAmount'] = $this->paymentAmount;
        }
        if (isset($this->paymentIdentifier)) {
            $json['paymentIdentifier'] = $this->paymentIdentifier;
        }
        if (0 < count($this->detail)) {
            $json['detail'] = [];
            foreach ($this->detail as $detail) {
                $json['detail'][] = $detail;
            }
        }
        if (isset($this->formCode)) {
            $json['formCode'] = $this->formCode;
        }
        if (0 < count($this->processNote)) {
            $json['processNote'] = [];
            foreach ($this->processNote as $processNote) {
                $json['processNote'][] = $processNote;
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
            $sxe = new \SimpleXMLElement('<PaymentReconciliation xmlns="http://hl7.org/fhir"></PaymentReconciliation>');
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
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->created)) {
            $this->created->xmlSerialize(true, $sxe->addChild('created'));
        }
        if (isset($this->paymentIssuer)) {
            $this->paymentIssuer->xmlSerialize(true, $sxe->addChild('paymentIssuer'));
        }
        if (isset($this->request)) {
            $this->request->xmlSerialize(true, $sxe->addChild('request'));
        }
        if (isset($this->requestor)) {
            $this->requestor->xmlSerialize(true, $sxe->addChild('requestor'));
        }
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if (isset($this->disposition)) {
            $this->disposition->xmlSerialize(true, $sxe->addChild('disposition'));
        }
        if (isset($this->paymentDate)) {
            $this->paymentDate->xmlSerialize(true, $sxe->addChild('paymentDate'));
        }
        if (isset($this->paymentAmount)) {
            $this->paymentAmount->xmlSerialize(true, $sxe->addChild('paymentAmount'));
        }
        if (isset($this->paymentIdentifier)) {
            $this->paymentIdentifier->xmlSerialize(true, $sxe->addChild('paymentIdentifier'));
        }
        if (0 < count($this->detail)) {
            foreach ($this->detail as $detail) {
                $detail->xmlSerialize(true, $sxe->addChild('detail'));
            }
        }
        if (isset($this->formCode)) {
            $this->formCode->xmlSerialize(true, $sxe->addChild('formCode'));
        }
        if (0 < count($this->processNote)) {
            foreach ($this->processNote as $processNote) {
                $processNote->xmlSerialize(true, $sxe->addChild('processNote'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
