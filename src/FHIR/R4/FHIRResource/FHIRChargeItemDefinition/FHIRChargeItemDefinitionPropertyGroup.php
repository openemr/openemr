<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRChargeItemDefinition;

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
 * The ChargeItemDefinition resource provides the properties that apply to the (billing) codes necessary to calculate costs and prices. The properties may differ largely depending on type and realm, therefore this resource gives only a rough structure and requires profiling for each type of billing code system.
 */
class FHIRChargeItemDefinitionPropertyGroup extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Expressions that describe applicability criteria for the priceComponent.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRChargeItemDefinition\FHIRChargeItemDefinitionApplicability[]
     */
    public $applicability = [];

    /**
     * The price for a ChargeItem may be calculated as a base price with surcharges/deductions that apply in certain conditions. A ChargeItemDefinition resource that defines the prices, factors and conditions that apply to a billing code is currently under development. The priceComponent element can be used to offer transparency to the recipient of the Invoice of how the prices have been calculated.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRChargeItemDefinition\FHIRChargeItemDefinitionPriceComponent[]
     */
    public $priceComponent = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ChargeItemDefinition.PropertyGroup';

    /**
     * Expressions that describe applicability criteria for the priceComponent.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRChargeItemDefinition\FHIRChargeItemDefinitionApplicability[]
     */
    public function getApplicability()
    {
        return $this->applicability;
    }

    /**
     * Expressions that describe applicability criteria for the priceComponent.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRChargeItemDefinition\FHIRChargeItemDefinitionApplicability $applicability
     * @return $this
     */
    public function addApplicability($applicability)
    {
        $this->applicability[] = $applicability;
        return $this;
    }

    /**
     * The price for a ChargeItem may be calculated as a base price with surcharges/deductions that apply in certain conditions. A ChargeItemDefinition resource that defines the prices, factors and conditions that apply to a billing code is currently under development. The priceComponent element can be used to offer transparency to the recipient of the Invoice of how the prices have been calculated.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRChargeItemDefinition\FHIRChargeItemDefinitionPriceComponent[]
     */
    public function getPriceComponent()
    {
        return $this->priceComponent;
    }

    /**
     * The price for a ChargeItem may be calculated as a base price with surcharges/deductions that apply in certain conditions. A ChargeItemDefinition resource that defines the prices, factors and conditions that apply to a billing code is currently under development. The priceComponent element can be used to offer transparency to the recipient of the Invoice of how the prices have been calculated.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRChargeItemDefinition\FHIRChargeItemDefinitionPriceComponent $priceComponent
     * @return $this
     */
    public function addPriceComponent($priceComponent)
    {
        $this->priceComponent[] = $priceComponent;
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
            if (isset($data['applicability'])) {
                if (is_array($data['applicability'])) {
                    foreach ($data['applicability'] as $d) {
                        $this->addApplicability($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"applicability" must be array of objects or null, ' . gettype($data['applicability']) . ' seen.');
                }
            }
            if (isset($data['priceComponent'])) {
                if (is_array($data['priceComponent'])) {
                    foreach ($data['priceComponent'] as $d) {
                        $this->addPriceComponent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"priceComponent" must be array of objects or null, ' . gettype($data['priceComponent']) . ' seen.');
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
        if (0 < count($this->applicability)) {
            $json['applicability'] = [];
            foreach ($this->applicability as $applicability) {
                $json['applicability'][] = $applicability;
            }
        }
        if (0 < count($this->priceComponent)) {
            $json['priceComponent'] = [];
            foreach ($this->priceComponent as $priceComponent) {
                $json['priceComponent'][] = $priceComponent;
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
            $sxe = new \SimpleXMLElement('<ChargeItemDefinitionPropertyGroup xmlns="http://hl7.org/fhir"></ChargeItemDefinitionPropertyGroup>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->applicability)) {
            foreach ($this->applicability as $applicability) {
                $applicability->xmlSerialize(true, $sxe->addChild('applicability'));
            }
        }
        if (0 < count($this->priceComponent)) {
            foreach ($this->priceComponent as $priceComponent) {
                $priceComponent->xmlSerialize(true, $sxe->addChild('priceComponent'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
