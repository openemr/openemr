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
class FHIRSubstancePolymerRepeat extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $numberOfUnits = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $averageMolecularFormula = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $repeatUnitAmountType = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerRepeatUnit[]
     */
    public $repeatUnit = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstancePolymer.Repeat';

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getNumberOfUnits()
    {
        return $this->numberOfUnits;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $numberOfUnits
     * @return $this
     */
    public function setNumberOfUnits($numberOfUnits)
    {
        $this->numberOfUnits = $numberOfUnits;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getAverageMolecularFormula()
    {
        return $this->averageMolecularFormula;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $averageMolecularFormula
     * @return $this
     */
    public function setAverageMolecularFormula($averageMolecularFormula)
    {
        $this->averageMolecularFormula = $averageMolecularFormula;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getRepeatUnitAmountType()
    {
        return $this->repeatUnitAmountType;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $repeatUnitAmountType
     * @return $this
     */
    public function setRepeatUnitAmountType($repeatUnitAmountType)
    {
        $this->repeatUnitAmountType = $repeatUnitAmountType;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerRepeatUnit[]
     */
    public function getRepeatUnit()
    {
        return $this->repeatUnit;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerRepeatUnit $repeatUnit
     * @return $this
     */
    public function addRepeatUnit($repeatUnit)
    {
        $this->repeatUnit[] = $repeatUnit;
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
            if (isset($data['numberOfUnits'])) {
                $this->setNumberOfUnits($data['numberOfUnits']);
            }
            if (isset($data['averageMolecularFormula'])) {
                $this->setAverageMolecularFormula($data['averageMolecularFormula']);
            }
            if (isset($data['repeatUnitAmountType'])) {
                $this->setRepeatUnitAmountType($data['repeatUnitAmountType']);
            }
            if (isset($data['repeatUnit'])) {
                if (is_array($data['repeatUnit'])) {
                    foreach ($data['repeatUnit'] as $d) {
                        $this->addRepeatUnit($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"repeatUnit" must be array of objects or null, ' . gettype($data['repeatUnit']) . ' seen.');
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
        if (isset($this->numberOfUnits)) {
            $json['numberOfUnits'] = $this->numberOfUnits;
        }
        if (isset($this->averageMolecularFormula)) {
            $json['averageMolecularFormula'] = $this->averageMolecularFormula;
        }
        if (isset($this->repeatUnitAmountType)) {
            $json['repeatUnitAmountType'] = $this->repeatUnitAmountType;
        }
        if (0 < count($this->repeatUnit)) {
            $json['repeatUnit'] = [];
            foreach ($this->repeatUnit as $repeatUnit) {
                $json['repeatUnit'][] = $repeatUnit;
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
            $sxe = new \SimpleXMLElement('<SubstancePolymerRepeat xmlns="http://hl7.org/fhir"></SubstancePolymerRepeat>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->numberOfUnits)) {
            $this->numberOfUnits->xmlSerialize(true, $sxe->addChild('numberOfUnits'));
        }
        if (isset($this->averageMolecularFormula)) {
            $this->averageMolecularFormula->xmlSerialize(true, $sxe->addChild('averageMolecularFormula'));
        }
        if (isset($this->repeatUnitAmountType)) {
            $this->repeatUnitAmountType->xmlSerialize(true, $sxe->addChild('repeatUnitAmountType'));
        }
        if (0 < count($this->repeatUnit)) {
            foreach ($this->repeatUnit as $repeatUnit) {
                $repeatUnit->xmlSerialize(true, $sxe->addChild('repeatUnit'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
