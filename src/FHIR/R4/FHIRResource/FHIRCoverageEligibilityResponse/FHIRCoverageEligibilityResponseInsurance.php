<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse;

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
 * This resource provides eligibility and plan details from the processing of an CoverageEligibilityRequest resource.
 */
class FHIRCoverageEligibilityResponseInsurance extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Reference to the insurance card level information contained in the Coverage resource. The coverage issuing insurer will use these details to locate the patient's actual coverage within the insurer's information system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $coverage = null;

    /**
     * Flag indicating if the coverage provided is inforce currently if no service date(s) specified or for the whole duration of the service dates.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $inforce = null;

    /**
     * The term of the benefits documented in this response.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $benefitPeriod = null;

    /**
     * Benefits and optionally current balances, and authorization details by category or service.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseItem[]
     */
    public $item = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CoverageEligibilityResponse.Insurance';

    /**
     * Reference to the insurance card level information contained in the Coverage resource. The coverage issuing insurer will use these details to locate the patient's actual coverage within the insurer's information system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * Reference to the insurance card level information contained in the Coverage resource. The coverage issuing insurer will use these details to locate the patient's actual coverage within the insurer's information system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $coverage
     * @return $this
     */
    public function setCoverage($coverage)
    {
        $this->coverage = $coverage;
        return $this;
    }

    /**
     * Flag indicating if the coverage provided is inforce currently if no service date(s) specified or for the whole duration of the service dates.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getInforce()
    {
        return $this->inforce;
    }

    /**
     * Flag indicating if the coverage provided is inforce currently if no service date(s) specified or for the whole duration of the service dates.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $inforce
     * @return $this
     */
    public function setInforce($inforce)
    {
        $this->inforce = $inforce;
        return $this;
    }

    /**
     * The term of the benefits documented in this response.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getBenefitPeriod()
    {
        return $this->benefitPeriod;
    }

    /**
     * The term of the benefits documented in this response.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $benefitPeriod
     * @return $this
     */
    public function setBenefitPeriod($benefitPeriod)
    {
        $this->benefitPeriod = $benefitPeriod;
        return $this;
    }

    /**
     * Benefits and optionally current balances, and authorization details by category or service.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseItem[]
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Benefits and optionally current balances, and authorization details by category or service.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseItem $item
     * @return $this
     */
    public function addItem($item)
    {
        $this->item[] = $item;
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
            if (isset($data['coverage'])) {
                $this->setCoverage($data['coverage']);
            }
            if (isset($data['inforce'])) {
                $this->setInforce($data['inforce']);
            }
            if (isset($data['benefitPeriod'])) {
                $this->setBenefitPeriod($data['benefitPeriod']);
            }
            if (isset($data['item'])) {
                if (is_array($data['item'])) {
                    foreach ($data['item'] as $d) {
                        $this->addItem($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"item" must be array of objects or null, ' . gettype($data['item']) . ' seen.');
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
        if (isset($this->coverage)) {
            $json['coverage'] = $this->coverage;
        }
        if (isset($this->inforce)) {
            $json['inforce'] = $this->inforce;
        }
        if (isset($this->benefitPeriod)) {
            $json['benefitPeriod'] = $this->benefitPeriod;
        }
        if (0 < count($this->item)) {
            $json['item'] = [];
            foreach ($this->item as $item) {
                $json['item'][] = $item;
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
            $sxe = new \SimpleXMLElement('<CoverageEligibilityResponseInsurance xmlns="http://hl7.org/fhir"></CoverageEligibilityResponseInsurance>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->coverage)) {
            $this->coverage->xmlSerialize(true, $sxe->addChild('coverage'));
        }
        if (isset($this->inforce)) {
            $this->inforce->xmlSerialize(true, $sxe->addChild('inforce'));
        }
        if (isset($this->benefitPeriod)) {
            $this->benefitPeriod->xmlSerialize(true, $sxe->addChild('benefitPeriod'));
        }
        if (0 < count($this->item)) {
            foreach ($this->item as $item) {
                $item->xmlSerialize(true, $sxe->addChild('item'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
