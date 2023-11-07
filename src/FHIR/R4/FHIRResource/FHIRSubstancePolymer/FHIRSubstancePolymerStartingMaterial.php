<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer;

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
 * Todo.
 */
class FHIRSubstancePolymerStartingMaterial extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $material = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $isDefining = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceAmount
     */
    public $amount = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstancePolymer.StartingMaterial';

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $material
     * @return $this
     */
    public function setMaterial($material)
    {
        $this->material = $material;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getIsDefining()
    {
        return $this->isDefining;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $isDefining
     * @return $this
     */
    public function setIsDefining($isDefining)
    {
        $this->isDefining = $isDefining;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceAmount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceAmount $amount
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
            if (isset($data['material'])) {
                $this->setMaterial($data['material']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['isDefining'])) {
                $this->setIsDefining($data['isDefining']);
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
        if (isset($this->material)) {
            $json['material'] = $this->material;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->isDefining)) {
            $json['isDefining'] = $this->isDefining;
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
            $sxe = new \SimpleXMLElement('<SubstancePolymerStartingMaterial xmlns="http://hl7.org/fhir"></SubstancePolymerStartingMaterial>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->material)) {
            $this->material->xmlSerialize(true, $sxe->addChild('material'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->isDefining)) {
            $this->isDefining->xmlSerialize(true, $sxe->addChild('isDefining'));
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
