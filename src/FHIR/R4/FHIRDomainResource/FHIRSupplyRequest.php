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
 * A record of a request for a medication, substance or device used in the healthcare setting.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSupplyRequest extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Business identifiers assigned to this SupplyRequest by the author and/or other systems. These identifiers remain constant as the resource is updated and propagates from server to server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Status of the supply request.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSupplyRequestStatus
     */
    public $status = null;

    /**
     * Category of supply, e.g.  central, non-stock, etc. This is used to support work flows associated with the supply process.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $category = null;

    /**
     * Indicates how quickly this SupplyRequest should be addressed with respect to other requests.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority
     */
    public $priority = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $itemCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $itemReference = null;

    /**
     * The amount that is being ordered of the indicated item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * Specific parameters for the ordered item.  For example, the size of the indicated item.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSupplyRequest\FHIRSupplyRequestParameter[]
     */
    public $parameter = [];

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $occurrenceDateTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $occurrencePeriod = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public $occurrenceTiming = null;

    /**
     * When the request was made.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $authoredOn = null;

    /**
     * The device, practitioner, etc. who initiated the request.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $requester = null;

    /**
     * Who is intended to fulfill the request.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $supplier = [];

    /**
     * The reason why the supply item was requested.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $reasonCode = [];

    /**
     * The reason why the supply item was requested.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $reasonReference = [];

    /**
     * Where the supply is expected to come from.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $deliverFrom = null;

    /**
     * Where the supply is destined to go.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $deliverTo = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SupplyRequest';

    /**
     * Business identifiers assigned to this SupplyRequest by the author and/or other systems. These identifiers remain constant as the resource is updated and propagates from server to server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifiers assigned to this SupplyRequest by the author and/or other systems. These identifiers remain constant as the resource is updated and propagates from server to server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Status of the supply request.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSupplyRequestStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Status of the supply request.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSupplyRequestStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Category of supply, e.g.  central, non-stock, etc. This is used to support work flows associated with the supply process.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Category of supply, e.g.  central, non-stock, etc. This is used to support work flows associated with the supply process.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Indicates how quickly this SupplyRequest should be addressed with respect to other requests.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Indicates how quickly this SupplyRequest should be addressed with respect to other requests.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getItemCodeableConcept()
    {
        return $this->itemCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $itemCodeableConcept
     * @return $this
     */
    public function setItemCodeableConcept($itemCodeableConcept)
    {
        $this->itemCodeableConcept = $itemCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getItemReference()
    {
        return $this->itemReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $itemReference
     * @return $this
     */
    public function setItemReference($itemReference)
    {
        $this->itemReference = $itemReference;
        return $this;
    }

    /**
     * The amount that is being ordered of the indicated item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The amount that is being ordered of the indicated item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Specific parameters for the ordered item.  For example, the size of the indicated item.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSupplyRequest\FHIRSupplyRequestParameter[]
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Specific parameters for the ordered item.  For example, the size of the indicated item.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSupplyRequest\FHIRSupplyRequestParameter $parameter
     * @return $this
     */
    public function addParameter($parameter)
    {
        $this->parameter[] = $parameter;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getOccurrenceDateTime()
    {
        return $this->occurrenceDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $occurrenceDateTime
     * @return $this
     */
    public function setOccurrenceDateTime($occurrenceDateTime)
    {
        $this->occurrenceDateTime = $occurrenceDateTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getOccurrencePeriod()
    {
        return $this->occurrencePeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $occurrencePeriod
     * @return $this
     */
    public function setOccurrencePeriod($occurrencePeriod)
    {
        $this->occurrencePeriod = $occurrencePeriod;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public function getOccurrenceTiming()
    {
        return $this->occurrenceTiming;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming $occurrenceTiming
     * @return $this
     */
    public function setOccurrenceTiming($occurrenceTiming)
    {
        $this->occurrenceTiming = $occurrenceTiming;
        return $this;
    }

    /**
     * When the request was made.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getAuthoredOn()
    {
        return $this->authoredOn;
    }

    /**
     * When the request was made.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $authoredOn
     * @return $this
     */
    public function setAuthoredOn($authoredOn)
    {
        $this->authoredOn = $authoredOn;
        return $this;
    }

    /**
     * The device, practitioner, etc. who initiated the request.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRequester()
    {
        return $this->requester;
    }

    /**
     * The device, practitioner, etc. who initiated the request.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $requester
     * @return $this
     */
    public function setRequester($requester)
    {
        $this->requester = $requester;
        return $this;
    }

    /**
     * Who is intended to fulfill the request.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Who is intended to fulfill the request.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $supplier
     * @return $this
     */
    public function addSupplier($supplier)
    {
        $this->supplier[] = $supplier;
        return $this;
    }

    /**
     * The reason why the supply item was requested.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * The reason why the supply item was requested.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return $this
     */
    public function addReasonCode($reasonCode)
    {
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * The reason why the supply item was requested.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getReasonReference()
    {
        return $this->reasonReference;
    }

    /**
     * The reason why the supply item was requested.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reasonReference
     * @return $this
     */
    public function addReasonReference($reasonReference)
    {
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * Where the supply is expected to come from.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getDeliverFrom()
    {
        return $this->deliverFrom;
    }

    /**
     * Where the supply is expected to come from.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $deliverFrom
     * @return $this
     */
    public function setDeliverFrom($deliverFrom)
    {
        $this->deliverFrom = $deliverFrom;
        return $this;
    }

    /**
     * Where the supply is destined to go.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getDeliverTo()
    {
        return $this->deliverTo;
    }

    /**
     * Where the supply is destined to go.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $deliverTo
     * @return $this
     */
    public function setDeliverTo($deliverTo)
    {
        $this->deliverTo = $deliverTo;
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
            if (isset($data['category'])) {
                $this->setCategory($data['category']);
            }
            if (isset($data['priority'])) {
                $this->setPriority($data['priority']);
            }
            if (isset($data['itemCodeableConcept'])) {
                $this->setItemCodeableConcept($data['itemCodeableConcept']);
            }
            if (isset($data['itemReference'])) {
                $this->setItemReference($data['itemReference']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['parameter'])) {
                if (is_array($data['parameter'])) {
                    foreach ($data['parameter'] as $d) {
                        $this->addParameter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"parameter" must be array of objects or null, ' . gettype($data['parameter']) . ' seen.');
                }
            }
            if (isset($data['occurrenceDateTime'])) {
                $this->setOccurrenceDateTime($data['occurrenceDateTime']);
            }
            if (isset($data['occurrencePeriod'])) {
                $this->setOccurrencePeriod($data['occurrencePeriod']);
            }
            if (isset($data['occurrenceTiming'])) {
                $this->setOccurrenceTiming($data['occurrenceTiming']);
            }
            if (isset($data['authoredOn'])) {
                $this->setAuthoredOn($data['authoredOn']);
            }
            if (isset($data['requester'])) {
                $this->setRequester($data['requester']);
            }
            if (isset($data['supplier'])) {
                if (is_array($data['supplier'])) {
                    foreach ($data['supplier'] as $d) {
                        $this->addSupplier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supplier" must be array of objects or null, ' . gettype($data['supplier']) . ' seen.');
                }
            }
            if (isset($data['reasonCode'])) {
                if (is_array($data['reasonCode'])) {
                    foreach ($data['reasonCode'] as $d) {
                        $this->addReasonCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reasonCode" must be array of objects or null, ' . gettype($data['reasonCode']) . ' seen.');
                }
            }
            if (isset($data['reasonReference'])) {
                if (is_array($data['reasonReference'])) {
                    foreach ($data['reasonReference'] as $d) {
                        $this->addReasonReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reasonReference" must be array of objects or null, ' . gettype($data['reasonReference']) . ' seen.');
                }
            }
            if (isset($data['deliverFrom'])) {
                $this->setDeliverFrom($data['deliverFrom']);
            }
            if (isset($data['deliverTo'])) {
                $this->setDeliverTo($data['deliverTo']);
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
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        if (isset($this->priority)) {
            $json['priority'] = $this->priority;
        }
        if (isset($this->itemCodeableConcept)) {
            $json['itemCodeableConcept'] = $this->itemCodeableConcept;
        }
        if (isset($this->itemReference)) {
            $json['itemReference'] = $this->itemReference;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (0 < count($this->parameter)) {
            $json['parameter'] = [];
            foreach ($this->parameter as $parameter) {
                $json['parameter'][] = $parameter;
            }
        }
        if (isset($this->occurrenceDateTime)) {
            $json['occurrenceDateTime'] = $this->occurrenceDateTime;
        }
        if (isset($this->occurrencePeriod)) {
            $json['occurrencePeriod'] = $this->occurrencePeriod;
        }
        if (isset($this->occurrenceTiming)) {
            $json['occurrenceTiming'] = $this->occurrenceTiming;
        }
        if (isset($this->authoredOn)) {
            $json['authoredOn'] = $this->authoredOn;
        }
        if (isset($this->requester)) {
            $json['requester'] = $this->requester;
        }
        if (0 < count($this->supplier)) {
            $json['supplier'] = [];
            foreach ($this->supplier as $supplier) {
                $json['supplier'][] = $supplier;
            }
        }
        if (0 < count($this->reasonCode)) {
            $json['reasonCode'] = [];
            foreach ($this->reasonCode as $reasonCode) {
                $json['reasonCode'][] = $reasonCode;
            }
        }
        if (0 < count($this->reasonReference)) {
            $json['reasonReference'] = [];
            foreach ($this->reasonReference as $reasonReference) {
                $json['reasonReference'][] = $reasonReference;
            }
        }
        if (isset($this->deliverFrom)) {
            $json['deliverFrom'] = $this->deliverFrom;
        }
        if (isset($this->deliverTo)) {
            $json['deliverTo'] = $this->deliverTo;
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
            $sxe = new \SimpleXMLElement('<SupplyRequest xmlns="http://hl7.org/fhir"></SupplyRequest>');
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
        if (isset($this->category)) {
            $this->category->xmlSerialize(true, $sxe->addChild('category'));
        }
        if (isset($this->priority)) {
            $this->priority->xmlSerialize(true, $sxe->addChild('priority'));
        }
        if (isset($this->itemCodeableConcept)) {
            $this->itemCodeableConcept->xmlSerialize(true, $sxe->addChild('itemCodeableConcept'));
        }
        if (isset($this->itemReference)) {
            $this->itemReference->xmlSerialize(true, $sxe->addChild('itemReference'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (0 < count($this->parameter)) {
            foreach ($this->parameter as $parameter) {
                $parameter->xmlSerialize(true, $sxe->addChild('parameter'));
            }
        }
        if (isset($this->occurrenceDateTime)) {
            $this->occurrenceDateTime->xmlSerialize(true, $sxe->addChild('occurrenceDateTime'));
        }
        if (isset($this->occurrencePeriod)) {
            $this->occurrencePeriod->xmlSerialize(true, $sxe->addChild('occurrencePeriod'));
        }
        if (isset($this->occurrenceTiming)) {
            $this->occurrenceTiming->xmlSerialize(true, $sxe->addChild('occurrenceTiming'));
        }
        if (isset($this->authoredOn)) {
            $this->authoredOn->xmlSerialize(true, $sxe->addChild('authoredOn'));
        }
        if (isset($this->requester)) {
            $this->requester->xmlSerialize(true, $sxe->addChild('requester'));
        }
        if (0 < count($this->supplier)) {
            foreach ($this->supplier as $supplier) {
                $supplier->xmlSerialize(true, $sxe->addChild('supplier'));
            }
        }
        if (0 < count($this->reasonCode)) {
            foreach ($this->reasonCode as $reasonCode) {
                $reasonCode->xmlSerialize(true, $sxe->addChild('reasonCode'));
            }
        }
        if (0 < count($this->reasonReference)) {
            foreach ($this->reasonReference as $reasonReference) {
                $reasonReference->xmlSerialize(true, $sxe->addChild('reasonReference'));
            }
        }
        if (isset($this->deliverFrom)) {
            $this->deliverFrom->xmlSerialize(true, $sxe->addChild('deliverFrom'));
        }
        if (isset($this->deliverTo)) {
            $this->deliverTo->xmlSerialize(true, $sxe->addChild('deliverTo'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
