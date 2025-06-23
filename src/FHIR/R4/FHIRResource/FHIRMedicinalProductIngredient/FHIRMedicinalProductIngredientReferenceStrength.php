<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductIngredient;

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
 * An ingredient of a manufactured item or pharmaceutical product.
 */
class FHIRMedicinalProductIngredientReferenceStrength extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Relevant reference substance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $substance = null;

    /**
     * Strength expressed in terms of a reference substance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public $strength = null;

    /**
     * Strength expressed in terms of a reference substance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public $strengthLowLimit = null;

    /**
     * For when strength is measured at a particular point or distance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $measurementPoint = null;

    /**
     * The country or countries for which the strength range applies.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $country = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductIngredient.ReferenceStrength';

    /**
     * Relevant reference substance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSubstance()
    {
        return $this->substance;
    }

    /**
     * Relevant reference substance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $substance
     * @return $this
     */
    public function setSubstance($substance)
    {
        $this->substance = $substance;
        return $this;
    }

    /**
     * Strength expressed in terms of a reference substance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getStrength()
    {
        return $this->strength;
    }

    /**
     * Strength expressed in terms of a reference substance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $strength
     * @return $this
     */
    public function setStrength($strength)
    {
        $this->strength = $strength;
        return $this;
    }

    /**
     * Strength expressed in terms of a reference substance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getStrengthLowLimit()
    {
        return $this->strengthLowLimit;
    }

    /**
     * Strength expressed in terms of a reference substance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $strengthLowLimit
     * @return $this
     */
    public function setStrengthLowLimit($strengthLowLimit)
    {
        $this->strengthLowLimit = $strengthLowLimit;
        return $this;
    }

    /**
     * For when strength is measured at a particular point or distance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMeasurementPoint()
    {
        return $this->measurementPoint;
    }

    /**
     * For when strength is measured at a particular point or distance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $measurementPoint
     * @return $this
     */
    public function setMeasurementPoint($measurementPoint)
    {
        $this->measurementPoint = $measurementPoint;
        return $this;
    }

    /**
     * The country or countries for which the strength range applies.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * The country or countries for which the strength range applies.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $country
     * @return $this
     */
    public function addCountry($country)
    {
        $this->country[] = $country;
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
            if (isset($data['substance'])) {
                $this->setSubstance($data['substance']);
            }
            if (isset($data['strength'])) {
                $this->setStrength($data['strength']);
            }
            if (isset($data['strengthLowLimit'])) {
                $this->setStrengthLowLimit($data['strengthLowLimit']);
            }
            if (isset($data['measurementPoint'])) {
                $this->setMeasurementPoint($data['measurementPoint']);
            }
            if (isset($data['country'])) {
                if (is_array($data['country'])) {
                    foreach ($data['country'] as $d) {
                        $this->addCountry($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"country" must be array of objects or null, ' . gettype($data['country']) . ' seen.');
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
        if (isset($this->substance)) {
            $json['substance'] = $this->substance;
        }
        if (isset($this->strength)) {
            $json['strength'] = $this->strength;
        }
        if (isset($this->strengthLowLimit)) {
            $json['strengthLowLimit'] = $this->strengthLowLimit;
        }
        if (isset($this->measurementPoint)) {
            $json['measurementPoint'] = $this->measurementPoint;
        }
        if (0 < count($this->country)) {
            $json['country'] = [];
            foreach ($this->country as $country) {
                $json['country'][] = $country;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductIngredientReferenceStrength xmlns="http://hl7.org/fhir"></MedicinalProductIngredientReferenceStrength>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->substance)) {
            $this->substance->xmlSerialize(true, $sxe->addChild('substance'));
        }
        if (isset($this->strength)) {
            $this->strength->xmlSerialize(true, $sxe->addChild('strength'));
        }
        if (isset($this->strengthLowLimit)) {
            $this->strengthLowLimit->xmlSerialize(true, $sxe->addChild('strengthLowLimit'));
        }
        if (isset($this->measurementPoint)) {
            $this->measurementPoint->xmlSerialize(true, $sxe->addChild('measurementPoint'));
        }
        if (0 < count($this->country)) {
            foreach ($this->country as $country) {
                $country->xmlSerialize(true, $sxe->addChild('country'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
