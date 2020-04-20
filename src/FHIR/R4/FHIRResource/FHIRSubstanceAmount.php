<?php

namespace OpenEMR\FHIR\R4\FHIRResource;

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
 * Chemical substances are a single substance type whose primary defining element is the molecular structure. Chemical substances shall be defined on the basis of their complete covalent molecular structure; the presence of a salt (counter-ion) and/or solvates (water, alcohols) is also captured. Purity, grade, physical form or particle size are not taken into account in the definition of a chemical substance or in the assignment of a Substance ID.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRSubstanceAmount extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $amountQuantity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $amountRange = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $amountString = null;

    /**
     * Most elements that require a quantitative value will also have a field called amount type. Amount type should always be specified because the actual value of the amount is often dependent on it. EXAMPLE: In capturing the actual relative amounts of substances or molecular fragments it is essential to indicate whether the amount refers to a mole ratio or weight ratio. For any given element an effort should be made to use same the amount type for all related definitional elements.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $amountType = null;

    /**
     * A textual comment on a numeric value.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $amountText = null;

    /**
     * Reference range of possible or expected values.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceAmount\FHIRSubstanceAmountReferenceRange
     */
    public $referenceRange = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceAmount';

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getAmountQuantity()
    {
        return $this->amountQuantity;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $amountQuantity
     * @return $this
     */
    public function setAmountQuantity($amountQuantity)
    {
        $this->amountQuantity = $amountQuantity;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getAmountRange()
    {
        return $this->amountRange;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $amountRange
     * @return $this
     */
    public function setAmountRange($amountRange)
    {
        $this->amountRange = $amountRange;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getAmountString()
    {
        return $this->amountString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $amountString
     * @return $this
     */
    public function setAmountString($amountString)
    {
        $this->amountString = $amountString;
        return $this;
    }

    /**
     * Most elements that require a quantitative value will also have a field called amount type. Amount type should always be specified because the actual value of the amount is often dependent on it. EXAMPLE: In capturing the actual relative amounts of substances or molecular fragments it is essential to indicate whether the amount refers to a mole ratio or weight ratio. For any given element an effort should be made to use same the amount type for all related definitional elements.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getAmountType()
    {
        return $this->amountType;
    }

    /**
     * Most elements that require a quantitative value will also have a field called amount type. Amount type should always be specified because the actual value of the amount is often dependent on it. EXAMPLE: In capturing the actual relative amounts of substances or molecular fragments it is essential to indicate whether the amount refers to a mole ratio or weight ratio. For any given element an effort should be made to use same the amount type for all related definitional elements.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $amountType
     * @return $this
     */
    public function setAmountType($amountType)
    {
        $this->amountType = $amountType;
        return $this;
    }

    /**
     * A textual comment on a numeric value.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getAmountText()
    {
        return $this->amountText;
    }

    /**
     * A textual comment on a numeric value.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $amountText
     * @return $this
     */
    public function setAmountText($amountText)
    {
        $this->amountText = $amountText;
        return $this;
    }

    /**
     * Reference range of possible or expected values.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceAmount\FHIRSubstanceAmountReferenceRange
     */
    public function getReferenceRange()
    {
        return $this->referenceRange;
    }

    /**
     * Reference range of possible or expected values.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceAmount\FHIRSubstanceAmountReferenceRange $referenceRange
     * @return $this
     */
    public function setReferenceRange($referenceRange)
    {
        $this->referenceRange = $referenceRange;
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
            if (isset($data['amountQuantity'])) {
                $this->setAmountQuantity($data['amountQuantity']);
            }
            if (isset($data['amountRange'])) {
                $this->setAmountRange($data['amountRange']);
            }
            if (isset($data['amountString'])) {
                $this->setAmountString($data['amountString']);
            }
            if (isset($data['amountType'])) {
                $this->setAmountType($data['amountType']);
            }
            if (isset($data['amountText'])) {
                $this->setAmountText($data['amountText']);
            }
            if (isset($data['referenceRange'])) {
                $this->setReferenceRange($data['referenceRange']);
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
        if (isset($this->amountQuantity)) {
            $json['amountQuantity'] = $this->amountQuantity;
        }
        if (isset($this->amountRange)) {
            $json['amountRange'] = $this->amountRange;
        }
        if (isset($this->amountString)) {
            $json['amountString'] = $this->amountString;
        }
        if (isset($this->amountType)) {
            $json['amountType'] = $this->amountType;
        }
        if (isset($this->amountText)) {
            $json['amountText'] = $this->amountText;
        }
        if (isset($this->referenceRange)) {
            $json['referenceRange'] = $this->referenceRange;
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
            $sxe = new \SimpleXMLElement('<SubstanceAmount xmlns="http://hl7.org/fhir"></SubstanceAmount>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->amountQuantity)) {
            $this->amountQuantity->xmlSerialize(true, $sxe->addChild('amountQuantity'));
        }
        if (isset($this->amountRange)) {
            $this->amountRange->xmlSerialize(true, $sxe->addChild('amountRange'));
        }
        if (isset($this->amountString)) {
            $this->amountString->xmlSerialize(true, $sxe->addChild('amountString'));
        }
        if (isset($this->amountType)) {
            $this->amountType->xmlSerialize(true, $sxe->addChild('amountType'));
        }
        if (isset($this->amountText)) {
            $this->amountText->xmlSerialize(true, $sxe->addChild('amountText'));
        }
        if (isset($this->referenceRange)) {
            $this->referenceRange->xmlSerialize(true, $sxe->addChild('referenceRange'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
