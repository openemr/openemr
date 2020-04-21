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
 * Invoice containing collected ChargeItems from an Account with calculated individual and total price for Billing purpose.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRInvoice extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifier of this Invoice, often used for reference in correspondence about this invoice or for tracking of payments.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The current state of the Invoice.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInvoiceStatus
     */
    public $status = null;

    /**
     * In case of Invoice cancellation a reason must be given (entered in error, superseded by corrected invoice etc.).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $cancelledReason = null;

    /**
     * Type of Invoice depending on domain, realm an usage (e.g. internal/external, dental, preliminary).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The individual or set of individuals receiving the goods and services billed in this invoice.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The individual or Organization responsible for balancing of this invoice.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $recipient = null;

    /**
     * Date/time(s) of when this Invoice was posted.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * Indicates who or what performed or participated in the charged service.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRInvoice\FHIRInvoiceParticipant[]
     */
    public $participant = [];

    /**
     * The organizationissuing the Invoice.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $issuer = null;

    /**
     * Account which is supposed to be balanced with this Invoice.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $account = null;

    /**
     * Each line item represents one charge for goods and services rendered. Details such as date, code and amount are found in the referenced ChargeItem resource.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRInvoice\FHIRInvoiceLineItem[]
     */
    public $lineItem = [];

    /**
     * The total amount for the Invoice may be calculated as the sum of the line items with surcharges/deductions that apply in certain conditions.  The priceComponent element can be used to offer transparency to the recipient of the Invoice of how the total price was calculated.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRInvoice\FHIRInvoicePriceComponent[]
     */
    public $totalPriceComponent = [];

    /**
     * Invoice total , taxes excluded.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $totalNet = null;

    /**
     * Invoice total, tax included.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $totalGross = null;

    /**
     * Payment details such as banking details, period of payment, deductibles, methods of payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $paymentTerms = null;

    /**
     * Comments made about the invoice by the issuer, subject, or other participants.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Invoice';

    /**
     * Identifier of this Invoice, often used for reference in correspondence about this invoice or for tracking of payments.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier of this Invoice, often used for reference in correspondence about this invoice or for tracking of payments.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The current state of the Invoice.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInvoiceStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The current state of the Invoice.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInvoiceStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * In case of Invoice cancellation a reason must be given (entered in error, superseded by corrected invoice etc.).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCancelledReason()
    {
        return $this->cancelledReason;
    }

    /**
     * In case of Invoice cancellation a reason must be given (entered in error, superseded by corrected invoice etc.).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $cancelledReason
     * @return $this
     */
    public function setCancelledReason($cancelledReason)
    {
        $this->cancelledReason = $cancelledReason;
        return $this;
    }

    /**
     * Type of Invoice depending on domain, realm an usage (e.g. internal/external, dental, preliminary).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type of Invoice depending on domain, realm an usage (e.g. internal/external, dental, preliminary).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The individual or set of individuals receiving the goods and services billed in this invoice.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The individual or set of individuals receiving the goods and services billed in this invoice.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The individual or Organization responsible for balancing of this invoice.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * The individual or Organization responsible for balancing of this invoice.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $recipient
     * @return $this
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * Date/time(s) of when this Invoice was posted.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Date/time(s) of when this Invoice was posted.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Indicates who or what performed or participated in the charged service.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRInvoice\FHIRInvoiceParticipant[]
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Indicates who or what performed or participated in the charged service.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRInvoice\FHIRInvoiceParticipant $participant
     * @return $this
     */
    public function addParticipant($participant)
    {
        $this->participant[] = $participant;
        return $this;
    }

    /**
     * The organizationissuing the Invoice.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * The organizationissuing the Invoice.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $issuer
     * @return $this
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * Account which is supposed to be balanced with this Invoice.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Account which is supposed to be balanced with this Invoice.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $account
     * @return $this
     */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * Each line item represents one charge for goods and services rendered. Details such as date, code and amount are found in the referenced ChargeItem resource.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRInvoice\FHIRInvoiceLineItem[]
     */
    public function getLineItem()
    {
        return $this->lineItem;
    }

    /**
     * Each line item represents one charge for goods and services rendered. Details such as date, code and amount are found in the referenced ChargeItem resource.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRInvoice\FHIRInvoiceLineItem $lineItem
     * @return $this
     */
    public function addLineItem($lineItem)
    {
        $this->lineItem[] = $lineItem;
        return $this;
    }

    /**
     * The total amount for the Invoice may be calculated as the sum of the line items with surcharges/deductions that apply in certain conditions.  The priceComponent element can be used to offer transparency to the recipient of the Invoice of how the total price was calculated.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRInvoice\FHIRInvoicePriceComponent[]
     */
    public function getTotalPriceComponent()
    {
        return $this->totalPriceComponent;
    }

    /**
     * The total amount for the Invoice may be calculated as the sum of the line items with surcharges/deductions that apply in certain conditions.  The priceComponent element can be used to offer transparency to the recipient of the Invoice of how the total price was calculated.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRInvoice\FHIRInvoicePriceComponent $totalPriceComponent
     * @return $this
     */
    public function addTotalPriceComponent($totalPriceComponent)
    {
        $this->totalPriceComponent[] = $totalPriceComponent;
        return $this;
    }

    /**
     * Invoice total , taxes excluded.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getTotalNet()
    {
        return $this->totalNet;
    }

    /**
     * Invoice total , taxes excluded.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $totalNet
     * @return $this
     */
    public function setTotalNet($totalNet)
    {
        $this->totalNet = $totalNet;
        return $this;
    }

    /**
     * Invoice total, tax included.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getTotalGross()
    {
        return $this->totalGross;
    }

    /**
     * Invoice total, tax included.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $totalGross
     * @return $this
     */
    public function setTotalGross($totalGross)
    {
        $this->totalGross = $totalGross;
        return $this;
    }

    /**
     * Payment details such as banking details, period of payment, deductibles, methods of payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getPaymentTerms()
    {
        return $this->paymentTerms;
    }

    /**
     * Payment details such as banking details, period of payment, deductibles, methods of payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $paymentTerms
     * @return $this
     */
    public function setPaymentTerms($paymentTerms)
    {
        $this->paymentTerms = $paymentTerms;
        return $this;
    }

    /**
     * Comments made about the invoice by the issuer, subject, or other participants.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Comments made about the invoice by the issuer, subject, or other participants.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
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
            if (isset($data['cancelledReason'])) {
                $this->setCancelledReason($data['cancelledReason']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['recipient'])) {
                $this->setRecipient($data['recipient']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['participant'])) {
                if (is_array($data['participant'])) {
                    foreach ($data['participant'] as $d) {
                        $this->addParticipant($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"participant" must be array of objects or null, ' . gettype($data['participant']) . ' seen.');
                }
            }
            if (isset($data['issuer'])) {
                $this->setIssuer($data['issuer']);
            }
            if (isset($data['account'])) {
                $this->setAccount($data['account']);
            }
            if (isset($data['lineItem'])) {
                if (is_array($data['lineItem'])) {
                    foreach ($data['lineItem'] as $d) {
                        $this->addLineItem($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"lineItem" must be array of objects or null, ' . gettype($data['lineItem']) . ' seen.');
                }
            }
            if (isset($data['totalPriceComponent'])) {
                if (is_array($data['totalPriceComponent'])) {
                    foreach ($data['totalPriceComponent'] as $d) {
                        $this->addTotalPriceComponent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"totalPriceComponent" must be array of objects or null, ' . gettype($data['totalPriceComponent']) . ' seen.');
                }
            }
            if (isset($data['totalNet'])) {
                $this->setTotalNet($data['totalNet']);
            }
            if (isset($data['totalGross'])) {
                $this->setTotalGross($data['totalGross']);
            }
            if (isset($data['paymentTerms'])) {
                $this->setPaymentTerms($data['paymentTerms']);
            }
            if (isset($data['note'])) {
                if (is_array($data['note'])) {
                    foreach ($data['note'] as $d) {
                        $this->addNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"note" must be array of objects or null, ' . gettype($data['note']) . ' seen.');
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
        if (isset($this->cancelledReason)) {
            $json['cancelledReason'] = $this->cancelledReason;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->recipient)) {
            $json['recipient'] = $this->recipient;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (0 < count($this->participant)) {
            $json['participant'] = [];
            foreach ($this->participant as $participant) {
                $json['participant'][] = $participant;
            }
        }
        if (isset($this->issuer)) {
            $json['issuer'] = $this->issuer;
        }
        if (isset($this->account)) {
            $json['account'] = $this->account;
        }
        if (0 < count($this->lineItem)) {
            $json['lineItem'] = [];
            foreach ($this->lineItem as $lineItem) {
                $json['lineItem'][] = $lineItem;
            }
        }
        if (0 < count($this->totalPriceComponent)) {
            $json['totalPriceComponent'] = [];
            foreach ($this->totalPriceComponent as $totalPriceComponent) {
                $json['totalPriceComponent'][] = $totalPriceComponent;
            }
        }
        if (isset($this->totalNet)) {
            $json['totalNet'] = $this->totalNet;
        }
        if (isset($this->totalGross)) {
            $json['totalGross'] = $this->totalGross;
        }
        if (isset($this->paymentTerms)) {
            $json['paymentTerms'] = $this->paymentTerms;
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
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
            $sxe = new \SimpleXMLElement('<Invoice xmlns="http://hl7.org/fhir"></Invoice>');
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
        if (isset($this->cancelledReason)) {
            $this->cancelledReason->xmlSerialize(true, $sxe->addChild('cancelledReason'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->recipient)) {
            $this->recipient->xmlSerialize(true, $sxe->addChild('recipient'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (0 < count($this->participant)) {
            foreach ($this->participant as $participant) {
                $participant->xmlSerialize(true, $sxe->addChild('participant'));
            }
        }
        if (isset($this->issuer)) {
            $this->issuer->xmlSerialize(true, $sxe->addChild('issuer'));
        }
        if (isset($this->account)) {
            $this->account->xmlSerialize(true, $sxe->addChild('account'));
        }
        if (0 < count($this->lineItem)) {
            foreach ($this->lineItem as $lineItem) {
                $lineItem->xmlSerialize(true, $sxe->addChild('lineItem'));
            }
        }
        if (0 < count($this->totalPriceComponent)) {
            foreach ($this->totalPriceComponent as $totalPriceComponent) {
                $totalPriceComponent->xmlSerialize(true, $sxe->addChild('totalPriceComponent'));
            }
        }
        if (isset($this->totalNet)) {
            $this->totalNet->xmlSerialize(true, $sxe->addChild('totalNet'));
        }
        if (isset($this->totalGross)) {
            $this->totalGross->xmlSerialize(true, $sxe->addChild('totalGross'));
        }
        if (isset($this->paymentTerms)) {
            $this->paymentTerms->xmlSerialize(true, $sxe->addChild('paymentTerms'));
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
