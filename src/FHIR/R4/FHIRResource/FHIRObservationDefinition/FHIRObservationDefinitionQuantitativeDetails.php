<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRObservationDefinition;

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
 * Set of definitional characteristics for a kind of observation or measurement produced or consumed by an orderable health care service.
 */
class FHIRObservationDefinitionQuantitativeDetails extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Customary unit used to report quantitative results of observations conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $customaryUnit = null;

    /**
     * SI unit used to report quantitative results of observations conforming to this ObservationDefinition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $unit = null;

    /**
     * Factor for converting value expressed with SI unit to value expressed with customary unit.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $conversionFactor = null;

    /**
     * Number of digits after decimal separator when the results of such observations are of type Quantity.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $decimalPrecision = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ObservationDefinition.QuantitativeDetails';

    /**
     * Customary unit used to report quantitative results of observations conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCustomaryUnit()
    {
        return $this->customaryUnit;
    }

    /**
     * Customary unit used to report quantitative results of observations conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $customaryUnit
     * @return $this
     */
    public function setCustomaryUnit($customaryUnit)
    {
        $this->customaryUnit = $customaryUnit;
        return $this;
    }

    /**
     * SI unit used to report quantitative results of observations conforming to this ObservationDefinition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * SI unit used to report quantitative results of observations conforming to this ObservationDefinition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $unit
     * @return $this
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * Factor for converting value expressed with SI unit to value expressed with customary unit.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getConversionFactor()
    {
        return $this->conversionFactor;
    }

    /**
     * Factor for converting value expressed with SI unit to value expressed with customary unit.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $conversionFactor
     * @return $this
     */
    public function setConversionFactor($conversionFactor)
    {
        $this->conversionFactor = $conversionFactor;
        return $this;
    }

    /**
     * Number of digits after decimal separator when the results of such observations are of type Quantity.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getDecimalPrecision()
    {
        return $this->decimalPrecision;
    }

    /**
     * Number of digits after decimal separator when the results of such observations are of type Quantity.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $decimalPrecision
     * @return $this
     */
    public function setDecimalPrecision($decimalPrecision)
    {
        $this->decimalPrecision = $decimalPrecision;
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
            if (isset($data['customaryUnit'])) {
                $this->setCustomaryUnit($data['customaryUnit']);
            }
            if (isset($data['unit'])) {
                $this->setUnit($data['unit']);
            }
            if (isset($data['conversionFactor'])) {
                $this->setConversionFactor($data['conversionFactor']);
            }
            if (isset($data['decimalPrecision'])) {
                $this->setDecimalPrecision($data['decimalPrecision']);
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
        if (isset($this->customaryUnit)) {
            $json['customaryUnit'] = $this->customaryUnit;
        }
        if (isset($this->unit)) {
            $json['unit'] = $this->unit;
        }
        if (isset($this->conversionFactor)) {
            $json['conversionFactor'] = $this->conversionFactor;
        }
        if (isset($this->decimalPrecision)) {
            $json['decimalPrecision'] = $this->decimalPrecision;
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
            $sxe = new \SimpleXMLElement('<ObservationDefinitionQuantitativeDetails xmlns="http://hl7.org/fhir"></ObservationDefinitionQuantitativeDetails>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->customaryUnit)) {
            $this->customaryUnit->xmlSerialize(true, $sxe->addChild('customaryUnit'));
        }
        if (isset($this->unit)) {
            $this->unit->xmlSerialize(true, $sxe->addChild('unit'));
        }
        if (isset($this->conversionFactor)) {
            $this->conversionFactor->xmlSerialize(true, $sxe->addChild('conversionFactor'));
        }
        if (isset($this->decimalPrecision)) {
            $this->decimalPrecision->xmlSerialize(true, $sxe->addChild('decimalPrecision'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
