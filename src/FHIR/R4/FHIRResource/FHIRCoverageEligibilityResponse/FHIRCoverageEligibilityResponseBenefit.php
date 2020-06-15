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
class FHIRCoverageEligibilityResponseBenefit extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Classification of benefit being provided.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $allowedUnsignedInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $allowedString = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $allowedMoney = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $usedUnsignedInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $usedString = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public $usedMoney = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CoverageEligibilityResponse.Benefit';

    /**
     * Classification of benefit being provided.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Classification of benefit being provided.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getAllowedUnsignedInt()
    {
        return $this->allowedUnsignedInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $allowedUnsignedInt
     * @return $this
     */
    public function setAllowedUnsignedInt($allowedUnsignedInt)
    {
        $this->allowedUnsignedInt = $allowedUnsignedInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getAllowedString()
    {
        return $this->allowedString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $allowedString
     * @return $this
     */
    public function setAllowedString($allowedString)
    {
        $this->allowedString = $allowedString;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getAllowedMoney()
    {
        return $this->allowedMoney;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $allowedMoney
     * @return $this
     */
    public function setAllowedMoney($allowedMoney)
    {
        $this->allowedMoney = $allowedMoney;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getUsedUnsignedInt()
    {
        return $this->usedUnsignedInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $usedUnsignedInt
     * @return $this
     */
    public function setUsedUnsignedInt($usedUnsignedInt)
    {
        $this->usedUnsignedInt = $usedUnsignedInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getUsedString()
    {
        return $this->usedString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $usedString
     * @return $this
     */
    public function setUsedString($usedString)
    {
        $this->usedString = $usedString;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney
     */
    public function getUsedMoney()
    {
        return $this->usedMoney;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMoney $usedMoney
     * @return $this
     */
    public function setUsedMoney($usedMoney)
    {
        $this->usedMoney = $usedMoney;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['allowedUnsignedInt'])) {
                $this->setAllowedUnsignedInt($data['allowedUnsignedInt']);
            }
            if (isset($data['allowedString'])) {
                $this->setAllowedString($data['allowedString']);
            }
            if (isset($data['allowedMoney'])) {
                $this->setAllowedMoney($data['allowedMoney']);
            }
            if (isset($data['usedUnsignedInt'])) {
                $this->setUsedUnsignedInt($data['usedUnsignedInt']);
            }
            if (isset($data['usedString'])) {
                $this->setUsedString($data['usedString']);
            }
            if (isset($data['usedMoney'])) {
                $this->setUsedMoney($data['usedMoney']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->allowedUnsignedInt)) {
            $json['allowedUnsignedInt'] = $this->allowedUnsignedInt;
        }
        if (isset($this->allowedString)) {
            $json['allowedString'] = $this->allowedString;
        }
        if (isset($this->allowedMoney)) {
            $json['allowedMoney'] = $this->allowedMoney;
        }
        if (isset($this->usedUnsignedInt)) {
            $json['usedUnsignedInt'] = $this->usedUnsignedInt;
        }
        if (isset($this->usedString)) {
            $json['usedString'] = $this->usedString;
        }
        if (isset($this->usedMoney)) {
            $json['usedMoney'] = $this->usedMoney;
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
            $sxe = new \SimpleXMLElement('<CoverageEligibilityResponseBenefit xmlns="http://hl7.org/fhir"></CoverageEligibilityResponseBenefit>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->allowedUnsignedInt)) {
            $this->allowedUnsignedInt->xmlSerialize(true, $sxe->addChild('allowedUnsignedInt'));
        }
        if (isset($this->allowedString)) {
            $this->allowedString->xmlSerialize(true, $sxe->addChild('allowedString'));
        }
        if (isset($this->allowedMoney)) {
            $this->allowedMoney->xmlSerialize(true, $sxe->addChild('allowedMoney'));
        }
        if (isset($this->usedUnsignedInt)) {
            $this->usedUnsignedInt->xmlSerialize(true, $sxe->addChild('usedUnsignedInt'));
        }
        if (isset($this->usedString)) {
            $this->usedString->xmlSerialize(true, $sxe->addChild('usedString'));
        }
        if (isset($this->usedMoney)) {
            $this->usedMoney->xmlSerialize(true, $sxe->addChild('usedMoney'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
