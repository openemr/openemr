<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicationKnowledge;

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
 * Information about a medication that is used to support knowledge.
 */
class FHIRMedicationKnowledgeKinetics extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The drug concentration measured at certain discrete points in time.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity[]
     */
    public $areaUnderCurve = [];

    /**
     * The median lethal dose of a drug.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity[]
     */
    public $lethalDose50 = [];

    /**
     * The time required for any specified property (e.g., the concentration of a substance in the body) to decrease by half.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $halfLifePeriod = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationKnowledge.Kinetics';

    /**
     * The drug concentration measured at certain discrete points in time.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity[]
     */
    public function getAreaUnderCurve()
    {
        return $this->areaUnderCurve;
    }

    /**
     * The drug concentration measured at certain discrete points in time.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $areaUnderCurve
     * @return $this
     */
    public function addAreaUnderCurve($areaUnderCurve)
    {
        $this->areaUnderCurve[] = $areaUnderCurve;
        return $this;
    }

    /**
     * The median lethal dose of a drug.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity[]
     */
    public function getLethalDose50()
    {
        return $this->lethalDose50;
    }

    /**
     * The median lethal dose of a drug.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $lethalDose50
     * @return $this
     */
    public function addLethalDose50($lethalDose50)
    {
        $this->lethalDose50[] = $lethalDose50;
        return $this;
    }

    /**
     * The time required for any specified property (e.g., the concentration of a substance in the body) to decrease by half.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getHalfLifePeriod()
    {
        return $this->halfLifePeriod;
    }

    /**
     * The time required for any specified property (e.g., the concentration of a substance in the body) to decrease by half.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $halfLifePeriod
     * @return $this
     */
    public function setHalfLifePeriod($halfLifePeriod)
    {
        $this->halfLifePeriod = $halfLifePeriod;
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
            if (isset($data['areaUnderCurve'])) {
                if (is_array($data['areaUnderCurve'])) {
                    foreach ($data['areaUnderCurve'] as $d) {
                        $this->addAreaUnderCurve($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"areaUnderCurve" must be array of objects or null, ' . gettype($data['areaUnderCurve']) . ' seen.');
                }
            }
            if (isset($data['lethalDose50'])) {
                if (is_array($data['lethalDose50'])) {
                    foreach ($data['lethalDose50'] as $d) {
                        $this->addLethalDose50($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"lethalDose50" must be array of objects or null, ' . gettype($data['lethalDose50']) . ' seen.');
                }
            }
            if (isset($data['halfLifePeriod'])) {
                $this->setHalfLifePeriod($data['halfLifePeriod']);
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
        if (0 < count($this->areaUnderCurve)) {
            $json['areaUnderCurve'] = [];
            foreach ($this->areaUnderCurve as $areaUnderCurve) {
                $json['areaUnderCurve'][] = $areaUnderCurve;
            }
        }
        if (0 < count($this->lethalDose50)) {
            $json['lethalDose50'] = [];
            foreach ($this->lethalDose50 as $lethalDose50) {
                $json['lethalDose50'][] = $lethalDose50;
            }
        }
        if (isset($this->halfLifePeriod)) {
            $json['halfLifePeriod'] = $this->halfLifePeriod;
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
            $sxe = new \SimpleXMLElement('<MedicationKnowledgeKinetics xmlns="http://hl7.org/fhir"></MedicationKnowledgeKinetics>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->areaUnderCurve)) {
            foreach ($this->areaUnderCurve as $areaUnderCurve) {
                $areaUnderCurve->xmlSerialize(true, $sxe->addChild('areaUnderCurve'));
            }
        }
        if (0 < count($this->lethalDose50)) {
            foreach ($this->lethalDose50 as $lethalDose50) {
                $lethalDose50->xmlSerialize(true, $sxe->addChild('lethalDose50'));
            }
        }
        if (isset($this->halfLifePeriod)) {
            $this->halfLifePeriod->xmlSerialize(true, $sxe->addChild('halfLifePeriod'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
