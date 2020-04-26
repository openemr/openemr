<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRContract;

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
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a policy or agreement.
 */
class FHIRContractValuedItem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $entityCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $entityReference = null;

    /**
     * Identifies a Contract Valued Item instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * Indicates the time during which this Contract ValuedItem information is effective.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $effectiveTime = null;

    /**
     * Specifies the units by which the Contract Valued Item is measured or counted, and quantifies the countable or measurable Contract Valued Item instances.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * A Contract Valued Item unit valuation measure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $unitPrice = null;

    /**
     * A real number that represents a multiplier used in determining the overall value of the Contract Valued Item delivered. The concept of a Factor allows for a discount or surcharge multiplier to be applied to a monetary amount.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $factor = null;

    /**
     * An amount that expresses the weighting (based on difficulty, cost and/or resource intensiveness) associated with the Contract Valued Item delivered. The concept of Points allows for assignment of point values for a Contract Valued Item, such that a monetary amount can be assigned to each point.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $points = null;

    /**
     * Expresses the product of the Contract Valued Item unitQuantity and the unitPriceAmt. For example, the formula: unit Quantity * unit Price (Cost per Point) * factor Number  * points = net Amount. Quantity, factor and points are assumed to be 1 if not supplied.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $net = null;

    /**
     * Terms of valuation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $payment = null;

    /**
     * When payment is due.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $paymentDate = null;

    /**
     * Who will make payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $responsible = null;

    /**
     * Who will receive payment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $recipient = null;

    /**
     * Id  of the clause or question text related to the context of this valuedItem in the referenced form or QuestionnaireResponse.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $linkId = [];

    /**
     * A set of security labels that define which terms are controlled by this condition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt[]
     */
    public $securityLabelNumber = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Contract.ValuedItem';

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getEntityCodeableConcept()
    {
        return $this->entityCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $entityCodeableConcept
     * @return $this
     */
    public function setEntityCodeableConcept($entityCodeableConcept)
    {
        $this->entityCodeableConcept = $entityCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getEntityReference()
    {
        return $this->entityReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $entityReference
     * @return $this
     */
    public function setEntityReference($entityReference)
    {
        $this->entityReference = $entityReference;
        return $this;
    }

    /**
     * Identifies a Contract Valued Item instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifies a Contract Valued Item instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Indicates the time during which this Contract ValuedItem information is effective.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getEffectiveTime()
    {
        return $this->effectiveTime;
    }

    /**
     * Indicates the time during which this Contract ValuedItem information is effective.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $effectiveTime
     * @return $this
     */
    public function setEffectiveTime($effectiveTime)
    {
        $this->effectiveTime = $effectiveTime;
        return $this;
    }

    /**
     * Specifies the units by which the Contract Valued Item is measured or counted, and quantifies the countable or measurable Contract Valued Item instances.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Specifies the units by which the Contract Valued Item is measured or counted, and quantifies the countable or measurable Contract Valued Item instances.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * A Contract Valued Item unit valuation measure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * A Contract Valued Item unit valuation measure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $unitPrice
     * @return $this
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    /**
     * A real number that represents a multiplier used in determining the overall value of the Contract Valued Item delivered. The concept of a Factor allows for a discount or surcharge multiplier to be applied to a monetary amount.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * A real number that represents a multiplier used in determining the overall value of the Contract Valued Item delivered. The concept of a Factor allows for a discount or surcharge multiplier to be applied to a monetary amount.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $factor
     * @return $this
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;
        return $this;
    }

    /**
     * An amount that expresses the weighting (based on difficulty, cost and/or resource intensiveness) associated with the Contract Valued Item delivered. The concept of Points allows for assignment of point values for a Contract Valued Item, such that a monetary amount can be assigned to each point.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * An amount that expresses the weighting (based on difficulty, cost and/or resource intensiveness) associated with the Contract Valued Item delivered. The concept of Points allows for assignment of point values for a Contract Valued Item, such that a monetary amount can be assigned to each point.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $points
     * @return $this
     */
    public function setPoints($points)
    {
        $this->points = $points;
        return $this;
    }

    /**
     * Expresses the product of the Contract Valued Item unitQuantity and the unitPriceAmt. For example, the formula: unit Quantity * unit Price (Cost per Point) * factor Number  * points = net Amount. Quantity, factor and points are assumed to be 1 if not supplied.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getNet()
    {
        return $this->net;
    }

    /**
     * Expresses the product of the Contract Valued Item unitQuantity and the unitPriceAmt. For example, the formula: unit Quantity * unit Price (Cost per Point) * factor Number  * points = net Amount. Quantity, factor and points are assumed to be 1 if not supplied.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $net
     * @return $this
     */
    public function setNet($net)
    {
        $this->net = $net;
        return $this;
    }

    /**
     * Terms of valuation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Terms of valuation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $payment
     * @return $this
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * When payment is due.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * When payment is due.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $paymentDate
     * @return $this
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    /**
     * Who will make payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getResponsible()
    {
        return $this->responsible;
    }

    /**
     * Who will make payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $responsible
     * @return $this
     */
    public function setResponsible($responsible)
    {
        $this->responsible = $responsible;
        return $this;
    }

    /**
     * Who will receive payment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Who will receive payment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $recipient
     * @return $this
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * Id  of the clause or question text related to the context of this valuedItem in the referenced form or QuestionnaireResponse.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * Id  of the clause or question text related to the context of this valuedItem in the referenced form or QuestionnaireResponse.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $linkId
     * @return $this
     */
    public function addLinkId($linkId)
    {
        $this->linkId[] = $linkId;
        return $this;
    }

    /**
     * A set of security labels that define which terms are controlled by this condition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt[]
     */
    public function getSecurityLabelNumber()
    {
        return $this->securityLabelNumber;
    }

    /**
     * A set of security labels that define which terms are controlled by this condition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $securityLabelNumber
     * @return $this
     */
    public function addSecurityLabelNumber($securityLabelNumber)
    {
        $this->securityLabelNumber[] = $securityLabelNumber;
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
            if (isset($data['entityCodeableConcept'])) {
                $this->setEntityCodeableConcept($data['entityCodeableConcept']);
            }
            if (isset($data['entityReference'])) {
                $this->setEntityReference($data['entityReference']);
            }
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['effectiveTime'])) {
                $this->setEffectiveTime($data['effectiveTime']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['unitPrice'])) {
                $this->setUnitPrice($data['unitPrice']);
            }
            if (isset($data['factor'])) {
                $this->setFactor($data['factor']);
            }
            if (isset($data['points'])) {
                $this->setPoints($data['points']);
            }
            if (isset($data['net'])) {
                $this->setNet($data['net']);
            }
            if (isset($data['payment'])) {
                $this->setPayment($data['payment']);
            }
            if (isset($data['paymentDate'])) {
                $this->setPaymentDate($data['paymentDate']);
            }
            if (isset($data['responsible'])) {
                $this->setResponsible($data['responsible']);
            }
            if (isset($data['recipient'])) {
                $this->setRecipient($data['recipient']);
            }
            if (isset($data['linkId'])) {
                if (is_array($data['linkId'])) {
                    foreach ($data['linkId'] as $d) {
                        $this->addLinkId($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"linkId" must be array of objects or null, ' . gettype($data['linkId']) . ' seen.');
                }
            }
            if (isset($data['securityLabelNumber'])) {
                if (is_array($data['securityLabelNumber'])) {
                    foreach ($data['securityLabelNumber'] as $d) {
                        $this->addSecurityLabelNumber($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"securityLabelNumber" must be array of objects or null, ' . gettype($data['securityLabelNumber']) . ' seen.');
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
        if (isset($this->entityCodeableConcept)) {
            $json['entityCodeableConcept'] = $this->entityCodeableConcept;
        }
        if (isset($this->entityReference)) {
            $json['entityReference'] = $this->entityReference;
        }
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->effectiveTime)) {
            $json['effectiveTime'] = $this->effectiveTime;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->unitPrice)) {
            $json['unitPrice'] = $this->unitPrice;
        }
        if (isset($this->factor)) {
            $json['factor'] = $this->factor;
        }
        if (isset($this->points)) {
            $json['points'] = $this->points;
        }
        if (isset($this->net)) {
            $json['net'] = $this->net;
        }
        if (isset($this->payment)) {
            $json['payment'] = $this->payment;
        }
        if (isset($this->paymentDate)) {
            $json['paymentDate'] = $this->paymentDate;
        }
        if (isset($this->responsible)) {
            $json['responsible'] = $this->responsible;
        }
        if (isset($this->recipient)) {
            $json['recipient'] = $this->recipient;
        }
        if (0 < count($this->linkId)) {
            $json['linkId'] = [];
            foreach ($this->linkId as $linkId) {
                $json['linkId'][] = $linkId;
            }
        }
        if (0 < count($this->securityLabelNumber)) {
            $json['securityLabelNumber'] = [];
            foreach ($this->securityLabelNumber as $securityLabelNumber) {
                $json['securityLabelNumber'][] = $securityLabelNumber;
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
            $sxe = new \SimpleXMLElement('<ContractValuedItem xmlns="http://hl7.org/fhir"></ContractValuedItem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->entityCodeableConcept)) {
            $this->entityCodeableConcept->xmlSerialize(true, $sxe->addChild('entityCodeableConcept'));
        }
        if (isset($this->entityReference)) {
            $this->entityReference->xmlSerialize(true, $sxe->addChild('entityReference'));
        }
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->effectiveTime)) {
            $this->effectiveTime->xmlSerialize(true, $sxe->addChild('effectiveTime'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->unitPrice)) {
            $this->unitPrice->xmlSerialize(true, $sxe->addChild('unitPrice'));
        }
        if (isset($this->factor)) {
            $this->factor->xmlSerialize(true, $sxe->addChild('factor'));
        }
        if (isset($this->points)) {
            $this->points->xmlSerialize(true, $sxe->addChild('points'));
        }
        if (isset($this->net)) {
            $this->net->xmlSerialize(true, $sxe->addChild('net'));
        }
        if (isset($this->payment)) {
            $this->payment->xmlSerialize(true, $sxe->addChild('payment'));
        }
        if (isset($this->paymentDate)) {
            $this->paymentDate->xmlSerialize(true, $sxe->addChild('paymentDate'));
        }
        if (isset($this->responsible)) {
            $this->responsible->xmlSerialize(true, $sxe->addChild('responsible'));
        }
        if (isset($this->recipient)) {
            $this->recipient->xmlSerialize(true, $sxe->addChild('recipient'));
        }
        if (0 < count($this->linkId)) {
            foreach ($this->linkId as $linkId) {
                $linkId->xmlSerialize(true, $sxe->addChild('linkId'));
            }
        }
        if (0 < count($this->securityLabelNumber)) {
            foreach ($this->securityLabelNumber as $securityLabelNumber) {
                $securityLabelNumber->xmlSerialize(true, $sxe->addChild('securityLabelNumber'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
