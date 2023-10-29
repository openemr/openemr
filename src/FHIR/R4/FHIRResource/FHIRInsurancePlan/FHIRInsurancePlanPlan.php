<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan;

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
 * Details of a Health Insurance product/plan provided by an organization.
 */
class FHIRInsurancePlanPlan extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Business identifiers assigned to this health insurance plan which remain constant as the resource is updated and propagates from server to server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Type of plan. For example, "Platinum" or "High Deductable".
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * The geographic region in which a health insurance plan's benefits apply.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $coverageArea = [];

    /**
     * Reference to the network that providing the type of coverage.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $network = [];

    /**
     * Overall costs associated with the plan.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanGeneralCost[]
     */
    public $generalCost = [];

    /**
     * Costs associated with the coverage provided by the product.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanSpecificCost[]
     */
    public $specificCost = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'InsurancePlan.Plan';

    /**
     * Business identifiers assigned to this health insurance plan which remain constant as the resource is updated and propagates from server to server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifiers assigned to this health insurance plan which remain constant as the resource is updated and propagates from server to server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Type of plan. For example, "Platinum" or "High Deductable".
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type of plan. For example, "Platinum" or "High Deductable".
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The geographic region in which a health insurance plan's benefits apply.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getCoverageArea()
    {
        return $this->coverageArea;
    }

    /**
     * The geographic region in which a health insurance plan's benefits apply.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $coverageArea
     * @return $this
     */
    public function addCoverageArea($coverageArea)
    {
        $this->coverageArea[] = $coverageArea;
        return $this;
    }

    /**
     * Reference to the network that providing the type of coverage.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Reference to the network that providing the type of coverage.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $network
     * @return $this
     */
    public function addNetwork($network)
    {
        $this->network[] = $network;
        return $this;
    }

    /**
     * Overall costs associated with the plan.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanGeneralCost[]
     */
    public function getGeneralCost()
    {
        return $this->generalCost;
    }

    /**
     * Overall costs associated with the plan.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanGeneralCost $generalCost
     * @return $this
     */
    public function addGeneralCost($generalCost)
    {
        $this->generalCost[] = $generalCost;
        return $this;
    }

    /**
     * Costs associated with the coverage provided by the product.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanSpecificCost[]
     */
    public function getSpecificCost()
    {
        return $this->specificCost;
    }

    /**
     * Costs associated with the coverage provided by the product.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRInsurancePlan\FHIRInsurancePlanSpecificCost $specificCost
     * @return $this
     */
    public function addSpecificCost($specificCost)
    {
        $this->specificCost[] = $specificCost;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['coverageArea'])) {
                if (is_array($data['coverageArea'])) {
                    foreach ($data['coverageArea'] as $d) {
                        $this->addCoverageArea($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"coverageArea" must be array of objects or null, ' . gettype($data['coverageArea']) . ' seen.');
                }
            }
            if (isset($data['network'])) {
                if (is_array($data['network'])) {
                    foreach ($data['network'] as $d) {
                        $this->addNetwork($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"network" must be array of objects or null, ' . gettype($data['network']) . ' seen.');
                }
            }
            if (isset($data['generalCost'])) {
                if (is_array($data['generalCost'])) {
                    foreach ($data['generalCost'] as $d) {
                        $this->addGeneralCost($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"generalCost" must be array of objects or null, ' . gettype($data['generalCost']) . ' seen.');
                }
            }
            if (isset($data['specificCost'])) {
                if (is_array($data['specificCost'])) {
                    foreach ($data['specificCost'] as $d) {
                        $this->addSpecificCost($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specificCost" must be array of objects or null, ' . gettype($data['specificCost']) . ' seen.');
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->coverageArea)) {
            $json['coverageArea'] = [];
            foreach ($this->coverageArea as $coverageArea) {
                $json['coverageArea'][] = $coverageArea;
            }
        }
        if (0 < count($this->network)) {
            $json['network'] = [];
            foreach ($this->network as $network) {
                $json['network'][] = $network;
            }
        }
        if (0 < count($this->generalCost)) {
            $json['generalCost'] = [];
            foreach ($this->generalCost as $generalCost) {
                $json['generalCost'][] = $generalCost;
            }
        }
        if (0 < count($this->specificCost)) {
            $json['specificCost'] = [];
            foreach ($this->specificCost as $specificCost) {
                $json['specificCost'][] = $specificCost;
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
            $sxe = new \SimpleXMLElement('<InsurancePlanPlan xmlns="http://hl7.org/fhir"></InsurancePlanPlan>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->coverageArea)) {
            foreach ($this->coverageArea as $coverageArea) {
                $coverageArea->xmlSerialize(true, $sxe->addChild('coverageArea'));
            }
        }
        if (0 < count($this->network)) {
            foreach ($this->network as $network) {
                $network->xmlSerialize(true, $sxe->addChild('network'));
            }
        }
        if (0 < count($this->generalCost)) {
            foreach ($this->generalCost as $generalCost) {
                $generalCost->xmlSerialize(true, $sxe->addChild('generalCost'));
            }
        }
        if (0 < count($this->specificCost)) {
            foreach ($this->specificCost as $specificCost) {
                $specificCost->xmlSerialize(true, $sxe->addChild('specificCost'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
