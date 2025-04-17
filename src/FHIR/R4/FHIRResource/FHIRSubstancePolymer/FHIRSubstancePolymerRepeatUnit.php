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
class FHIRSubstancePolymerRepeatUnit extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $orientationOfPolymerisation = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $repeatUnit = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceAmount
     */
    public $amount = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerDegreeOfPolymerisation[]
     */
    public $degreeOfPolymerisation = [];

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerStructuralRepresentation[]
     */
    public $structuralRepresentation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstancePolymer.RepeatUnit';

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getOrientationOfPolymerisation()
    {
        return $this->orientationOfPolymerisation;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $orientationOfPolymerisation
     * @return $this
     */
    public function setOrientationOfPolymerisation($orientationOfPolymerisation)
    {
        $this->orientationOfPolymerisation = $orientationOfPolymerisation;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getRepeatUnit()
    {
        return $this->repeatUnit;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $repeatUnit
     * @return $this
     */
    public function setRepeatUnit($repeatUnit)
    {
        $this->repeatUnit = $repeatUnit;
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
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerDegreeOfPolymerisation[]
     */
    public function getDegreeOfPolymerisation()
    {
        return $this->degreeOfPolymerisation;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerDegreeOfPolymerisation $degreeOfPolymerisation
     * @return $this
     */
    public function addDegreeOfPolymerisation($degreeOfPolymerisation)
    {
        $this->degreeOfPolymerisation[] = $degreeOfPolymerisation;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerStructuralRepresentation[]
     */
    public function getStructuralRepresentation()
    {
        return $this->structuralRepresentation;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerStructuralRepresentation $structuralRepresentation
     * @return $this
     */
    public function addStructuralRepresentation($structuralRepresentation)
    {
        $this->structuralRepresentation[] = $structuralRepresentation;
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
            if (isset($data['orientationOfPolymerisation'])) {
                $this->setOrientationOfPolymerisation($data['orientationOfPolymerisation']);
            }
            if (isset($data['repeatUnit'])) {
                $this->setRepeatUnit($data['repeatUnit']);
            }
            if (isset($data['amount'])) {
                $this->setAmount($data['amount']);
            }
            if (isset($data['degreeOfPolymerisation'])) {
                if (is_array($data['degreeOfPolymerisation'])) {
                    foreach ($data['degreeOfPolymerisation'] as $d) {
                        $this->addDegreeOfPolymerisation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"degreeOfPolymerisation" must be array of objects or null, ' . gettype($data['degreeOfPolymerisation']) . ' seen.');
                }
            }
            if (isset($data['structuralRepresentation'])) {
                if (is_array($data['structuralRepresentation'])) {
                    foreach ($data['structuralRepresentation'] as $d) {
                        $this->addStructuralRepresentation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"structuralRepresentation" must be array of objects or null, ' . gettype($data['structuralRepresentation']) . ' seen.');
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
        if (isset($this->orientationOfPolymerisation)) {
            $json['orientationOfPolymerisation'] = $this->orientationOfPolymerisation;
        }
        if (isset($this->repeatUnit)) {
            $json['repeatUnit'] = $this->repeatUnit;
        }
        if (isset($this->amount)) {
            $json['amount'] = $this->amount;
        }
        if (0 < count($this->degreeOfPolymerisation)) {
            $json['degreeOfPolymerisation'] = [];
            foreach ($this->degreeOfPolymerisation as $degreeOfPolymerisation) {
                $json['degreeOfPolymerisation'][] = $degreeOfPolymerisation;
            }
        }
        if (0 < count($this->structuralRepresentation)) {
            $json['structuralRepresentation'] = [];
            foreach ($this->structuralRepresentation as $structuralRepresentation) {
                $json['structuralRepresentation'][] = $structuralRepresentation;
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
            $sxe = new \SimpleXMLElement('<SubstancePolymerRepeatUnit xmlns="http://hl7.org/fhir"></SubstancePolymerRepeatUnit>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->orientationOfPolymerisation)) {
            $this->orientationOfPolymerisation->xmlSerialize(true, $sxe->addChild('orientationOfPolymerisation'));
        }
        if (isset($this->repeatUnit)) {
            $this->repeatUnit->xmlSerialize(true, $sxe->addChild('repeatUnit'));
        }
        if (isset($this->amount)) {
            $this->amount->xmlSerialize(true, $sxe->addChild('amount'));
        }
        if (0 < count($this->degreeOfPolymerisation)) {
            foreach ($this->degreeOfPolymerisation as $degreeOfPolymerisation) {
                $degreeOfPolymerisation->xmlSerialize(true, $sxe->addChild('degreeOfPolymerisation'));
            }
        }
        if (0 < count($this->structuralRepresentation)) {
            foreach ($this->structuralRepresentation as $structuralRepresentation) {
                $structuralRepresentation->xmlSerialize(true, $sxe->addChild('structuralRepresentation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
