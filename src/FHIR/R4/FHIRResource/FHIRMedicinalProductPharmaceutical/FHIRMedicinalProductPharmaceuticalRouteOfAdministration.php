<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical;

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
 * A pharmaceutical product described in terms of its composition and dose form.
 */
class FHIRMedicinalProductPharmaceuticalRouteOfAdministration extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Coded expression for the route.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * The first dose (dose quantity) administered in humans can be specified, for a product under investigation, using a numerical value and its unit of measurement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $firstDose = null;

    /**
     * The maximum single dose that can be administered as per the protocol of a clinical trial can be specified using a numerical value and its unit of measurement.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $maxSingleDose = null;

    /**
     * The maximum dose per day (maximum dose quantity to be administered in any one 24-h period) that can be administered as per the protocol referenced in the clinical trial authorisation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $maxDosePerDay = null;

    /**
     * The maximum dose per treatment period that can be administered as per the protocol referenced in the clinical trial authorisation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public $maxDosePerTreatmentPeriod = null;

    /**
     * The maximum treatment period during which an Investigational Medicinal Product can be administered as per the protocol referenced in the clinical trial authorisation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $maxTreatmentPeriod = null;

    /**
     * A species for which this route applies.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalTargetSpecies[]
     */
    public $targetSpecies = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductPharmaceutical.RouteOfAdministration';

    /**
     * Coded expression for the route.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Coded expression for the route.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The first dose (dose quantity) administered in humans can be specified, for a product under investigation, using a numerical value and its unit of measurement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getFirstDose()
    {
        return $this->firstDose;
    }

    /**
     * The first dose (dose quantity) administered in humans can be specified, for a product under investigation, using a numerical value and its unit of measurement.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $firstDose
     * @return $this
     */
    public function setFirstDose($firstDose)
    {
        $this->firstDose = $firstDose;
        return $this;
    }

    /**
     * The maximum single dose that can be administered as per the protocol of a clinical trial can be specified using a numerical value and its unit of measurement.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getMaxSingleDose()
    {
        return $this->maxSingleDose;
    }

    /**
     * The maximum single dose that can be administered as per the protocol of a clinical trial can be specified using a numerical value and its unit of measurement.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $maxSingleDose
     * @return $this
     */
    public function setMaxSingleDose($maxSingleDose)
    {
        $this->maxSingleDose = $maxSingleDose;
        return $this;
    }

    /**
     * The maximum dose per day (maximum dose quantity to be administered in any one 24-h period) that can be administered as per the protocol referenced in the clinical trial authorisation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getMaxDosePerDay()
    {
        return $this->maxDosePerDay;
    }

    /**
     * The maximum dose per day (maximum dose quantity to be administered in any one 24-h period) that can be administered as per the protocol referenced in the clinical trial authorisation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $maxDosePerDay
     * @return $this
     */
    public function setMaxDosePerDay($maxDosePerDay)
    {
        $this->maxDosePerDay = $maxDosePerDay;
        return $this;
    }

    /**
     * The maximum dose per treatment period that can be administered as per the protocol referenced in the clinical trial authorisation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio
     */
    public function getMaxDosePerTreatmentPeriod()
    {
        return $this->maxDosePerTreatmentPeriod;
    }

    /**
     * The maximum dose per treatment period that can be administered as per the protocol referenced in the clinical trial authorisation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRatio $maxDosePerTreatmentPeriod
     * @return $this
     */
    public function setMaxDosePerTreatmentPeriod($maxDosePerTreatmentPeriod)
    {
        $this->maxDosePerTreatmentPeriod = $maxDosePerTreatmentPeriod;
        return $this;
    }

    /**
     * The maximum treatment period during which an Investigational Medicinal Product can be administered as per the protocol referenced in the clinical trial authorisation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getMaxTreatmentPeriod()
    {
        return $this->maxTreatmentPeriod;
    }

    /**
     * The maximum treatment period during which an Investigational Medicinal Product can be administered as per the protocol referenced in the clinical trial authorisation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $maxTreatmentPeriod
     * @return $this
     */
    public function setMaxTreatmentPeriod($maxTreatmentPeriod)
    {
        $this->maxTreatmentPeriod = $maxTreatmentPeriod;
        return $this;
    }

    /**
     * A species for which this route applies.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalTargetSpecies[]
     */
    public function getTargetSpecies()
    {
        return $this->targetSpecies;
    }

    /**
     * A species for which this route applies.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalTargetSpecies $targetSpecies
     * @return $this
     */
    public function addTargetSpecies($targetSpecies)
    {
        $this->targetSpecies[] = $targetSpecies;
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['firstDose'])) {
                $this->setFirstDose($data['firstDose']);
            }
            if (isset($data['maxSingleDose'])) {
                $this->setMaxSingleDose($data['maxSingleDose']);
            }
            if (isset($data['maxDosePerDay'])) {
                $this->setMaxDosePerDay($data['maxDosePerDay']);
            }
            if (isset($data['maxDosePerTreatmentPeriod'])) {
                $this->setMaxDosePerTreatmentPeriod($data['maxDosePerTreatmentPeriod']);
            }
            if (isset($data['maxTreatmentPeriod'])) {
                $this->setMaxTreatmentPeriod($data['maxTreatmentPeriod']);
            }
            if (isset($data['targetSpecies'])) {
                if (is_array($data['targetSpecies'])) {
                    foreach ($data['targetSpecies'] as $d) {
                        $this->addTargetSpecies($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"targetSpecies" must be array of objects or null, ' . gettype($data['targetSpecies']) . ' seen.');
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->firstDose)) {
            $json['firstDose'] = $this->firstDose;
        }
        if (isset($this->maxSingleDose)) {
            $json['maxSingleDose'] = $this->maxSingleDose;
        }
        if (isset($this->maxDosePerDay)) {
            $json['maxDosePerDay'] = $this->maxDosePerDay;
        }
        if (isset($this->maxDosePerTreatmentPeriod)) {
            $json['maxDosePerTreatmentPeriod'] = $this->maxDosePerTreatmentPeriod;
        }
        if (isset($this->maxTreatmentPeriod)) {
            $json['maxTreatmentPeriod'] = $this->maxTreatmentPeriod;
        }
        if (0 < count($this->targetSpecies)) {
            $json['targetSpecies'] = [];
            foreach ($this->targetSpecies as $targetSpecies) {
                $json['targetSpecies'][] = $targetSpecies;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductPharmaceuticalRouteOfAdministration xmlns="http://hl7.org/fhir"></MedicinalProductPharmaceuticalRouteOfAdministration>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->firstDose)) {
            $this->firstDose->xmlSerialize(true, $sxe->addChild('firstDose'));
        }
        if (isset($this->maxSingleDose)) {
            $this->maxSingleDose->xmlSerialize(true, $sxe->addChild('maxSingleDose'));
        }
        if (isset($this->maxDosePerDay)) {
            $this->maxDosePerDay->xmlSerialize(true, $sxe->addChild('maxDosePerDay'));
        }
        if (isset($this->maxDosePerTreatmentPeriod)) {
            $this->maxDosePerTreatmentPeriod->xmlSerialize(true, $sxe->addChild('maxDosePerTreatmentPeriod'));
        }
        if (isset($this->maxTreatmentPeriod)) {
            $this->maxTreatmentPeriod->xmlSerialize(true, $sxe->addChild('maxTreatmentPeriod'));
        }
        if (0 < count($this->targetSpecies)) {
            foreach ($this->targetSpecies as $targetSpecies) {
                $targetSpecies->xmlSerialize(true, $sxe->addChild('targetSpecies'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
