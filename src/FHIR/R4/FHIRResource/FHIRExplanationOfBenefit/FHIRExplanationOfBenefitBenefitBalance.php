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
class FHIRExplanationOfBenefitBenefitBalance extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Code to identify the general type of benefits under which products and services are provided.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $category = null;

    /**
     * True if the indicated class of service is excluded from the plan, missing or False indicates the product or service is included in the coverage.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $excluded = null;

    /**
     * A short name or tag for the benefit.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * A richer description of the benefit or services covered.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Is a flag to indicate whether the benefits refer to in-network providers or out-of-network providers.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $network = null;

    /**
     * Indicates if the benefits apply to an individual or to the family.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $unit = null;

    /**
     * The term or period of the values such as 'maximum lifetime benefit' or 'maximum annual visits'.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $term = null;

    /**
     * Benefits Used to date.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitFinancial[]
     */
    public $financial = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ExplanationOfBenefit.BenefitBalance';

    /**
     * Code to identify the general type of benefits under which products and services are provided.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Code to identify the general type of benefits under which products and services are provided.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * True if the indicated class of service is excluded from the plan, missing or False indicates the product or service is included in the coverage.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getExcluded()
    {
        return $this->excluded;
    }

    /**
     * True if the indicated class of service is excluded from the plan, missing or False indicates the product or service is included in the coverage.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $excluded
     * @return $this
     */
    public function setExcluded($excluded)
    {
        $this->excluded = $excluded;
        return $this;
    }

    /**
     * A short name or tag for the benefit.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A short name or tag for the benefit.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * A richer description of the benefit or services covered.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A richer description of the benefit or services covered.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Is a flag to indicate whether the benefits refer to in-network providers or out-of-network providers.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Is a flag to indicate whether the benefits refer to in-network providers or out-of-network providers.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $network
     * @return $this
     */
    public function setNetwork($network)
    {
        $this->network = $network;
        return $this;
    }

    /**
     * Indicates if the benefits apply to an individual or to the family.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Indicates if the benefits apply to an individual or to the family.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $unit
     * @return $this
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * The term or period of the values such as 'maximum lifetime benefit' or 'maximum annual visits'.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * The term or period of the values such as 'maximum lifetime benefit' or 'maximum annual visits'.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $term
     * @return $this
     */
    public function setTerm($term)
    {
        $this->term = $term;
        return $this;
    }

    /**
     * Benefits Used to date.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitFinancial[]
     */
    public function getFinancial()
    {
        return $this->financial;
    }

    /**
     * Benefits Used to date.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitFinancial $financial
     * @return $this
     */
    public function addFinancial($financial)
    {
        $this->financial[] = $financial;
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
            if (isset($data['category'])) {
                $this->setCategory($data['category']);
            }
            if (isset($data['excluded'])) {
                $this->setExcluded($data['excluded']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['network'])) {
                $this->setNetwork($data['network']);
            }
            if (isset($data['unit'])) {
                $this->setUnit($data['unit']);
            }
            if (isset($data['term'])) {
                $this->setTerm($data['term']);
            }
            if (isset($data['financial'])) {
                if (is_array($data['financial'])) {
                    foreach ($data['financial'] as $d) {
                        $this->addFinancial($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"financial" must be array of objects or null, ' . gettype($data['financial']) . ' seen.');
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
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        if (isset($this->excluded)) {
            $json['excluded'] = $this->excluded;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->network)) {
            $json['network'] = $this->network;
        }
        if (isset($this->unit)) {
            $json['unit'] = $this->unit;
        }
        if (isset($this->term)) {
            $json['term'] = $this->term;
        }
        if (0 < count($this->financial)) {
            $json['financial'] = [];
            foreach ($this->financial as $financial) {
                $json['financial'][] = $financial;
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
            $sxe = new \SimpleXMLElement('<ExplanationOfBenefitBenefitBalance xmlns="http://hl7.org/fhir"></ExplanationOfBenefitBenefitBalance>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->category)) {
            $this->category->xmlSerialize(true, $sxe->addChild('category'));
        }
        if (isset($this->excluded)) {
            $this->excluded->xmlSerialize(true, $sxe->addChild('excluded'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->network)) {
            $this->network->xmlSerialize(true, $sxe->addChild('network'));
        }
        if (isset($this->unit)) {
            $this->unit->xmlSerialize(true, $sxe->addChild('unit'));
        }
        if (isset($this->term)) {
            $this->term->xmlSerialize(true, $sxe->addChild('term'));
        }
        if (0 < count($this->financial)) {
            foreach ($this->financial as $financial) {
                $financial->xmlSerialize(true, $sxe->addChild('financial'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
