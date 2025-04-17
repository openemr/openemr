<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRNutritionOrder;

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
 * A request to supply a diet, formula feeding (enteral) or oral nutritional supplement to a patient/resident.
 */
class FHIRNutritionOrderOralDiet extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The kind of diet or dietary restriction such as fiber restricted diet or diabetic diet.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $type = [];

    /**
     * The time period and frequency at which the diet should be given.  The diet should be given for the combination of all schedules if more than one schedule is present.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming[]
     */
    public $schedule = [];

    /**
     * Class that defines the quantity and type of nutrient modifications (for example carbohydrate, fiber or sodium) required for the oral diet.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRNutritionOrder\FHIRNutritionOrderNutrient[]
     */
    public $nutrient = [];

    /**
     * Class that describes any texture modifications required for the patient to safely consume various types of solid foods.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRNutritionOrder\FHIRNutritionOrderTexture[]
     */
    public $texture = [];

    /**
     * The required consistency (e.g. honey-thick, nectar-thick, thin, thickened.) of liquids or fluids served to the patient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $fluidConsistencyType = [];

    /**
     * Free text or additional instructions or information pertaining to the oral diet.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $instruction = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'NutritionOrder.OralDiet';

    /**
     * The kind of diet or dietary restriction such as fiber restricted diet or diabetic diet.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The kind of diet or dietary restriction such as fiber restricted diet or diabetic diet.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * The time period and frequency at which the diet should be given.  The diet should be given for the combination of all schedules if more than one schedule is present.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming[]
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * The time period and frequency at which the diet should be given.  The diet should be given for the combination of all schedules if more than one schedule is present.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming $schedule
     * @return $this
     */
    public function addSchedule($schedule)
    {
        $this->schedule[] = $schedule;
        return $this;
    }

    /**
     * Class that defines the quantity and type of nutrient modifications (for example carbohydrate, fiber or sodium) required for the oral diet.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRNutritionOrder\FHIRNutritionOrderNutrient[]
     */
    public function getNutrient()
    {
        return $this->nutrient;
    }

    /**
     * Class that defines the quantity and type of nutrient modifications (for example carbohydrate, fiber or sodium) required for the oral diet.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRNutritionOrder\FHIRNutritionOrderNutrient $nutrient
     * @return $this
     */
    public function addNutrient($nutrient)
    {
        $this->nutrient[] = $nutrient;
        return $this;
    }

    /**
     * Class that describes any texture modifications required for the patient to safely consume various types of solid foods.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRNutritionOrder\FHIRNutritionOrderTexture[]
     */
    public function getTexture()
    {
        return $this->texture;
    }

    /**
     * Class that describes any texture modifications required for the patient to safely consume various types of solid foods.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRNutritionOrder\FHIRNutritionOrderTexture $texture
     * @return $this
     */
    public function addTexture($texture)
    {
        $this->texture[] = $texture;
        return $this;
    }

    /**
     * The required consistency (e.g. honey-thick, nectar-thick, thin, thickened.) of liquids or fluids served to the patient.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getFluidConsistencyType()
    {
        return $this->fluidConsistencyType;
    }

    /**
     * The required consistency (e.g. honey-thick, nectar-thick, thin, thickened.) of liquids or fluids served to the patient.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $fluidConsistencyType
     * @return $this
     */
    public function addFluidConsistencyType($fluidConsistencyType)
    {
        $this->fluidConsistencyType[] = $fluidConsistencyType;
        return $this;
    }

    /**
     * Free text or additional instructions or information pertaining to the oral diet.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * Free text or additional instructions or information pertaining to the oral diet.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $instruction
     * @return $this
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
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
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, ' . gettype($data['type']) . ' seen.');
                }
            }
            if (isset($data['schedule'])) {
                if (is_array($data['schedule'])) {
                    foreach ($data['schedule'] as $d) {
                        $this->addSchedule($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"schedule" must be array of objects or null, ' . gettype($data['schedule']) . ' seen.');
                }
            }
            if (isset($data['nutrient'])) {
                if (is_array($data['nutrient'])) {
                    foreach ($data['nutrient'] as $d) {
                        $this->addNutrient($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"nutrient" must be array of objects or null, ' . gettype($data['nutrient']) . ' seen.');
                }
            }
            if (isset($data['texture'])) {
                if (is_array($data['texture'])) {
                    foreach ($data['texture'] as $d) {
                        $this->addTexture($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"texture" must be array of objects or null, ' . gettype($data['texture']) . ' seen.');
                }
            }
            if (isset($data['fluidConsistencyType'])) {
                if (is_array($data['fluidConsistencyType'])) {
                    foreach ($data['fluidConsistencyType'] as $d) {
                        $this->addFluidConsistencyType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"fluidConsistencyType" must be array of objects or null, ' . gettype($data['fluidConsistencyType']) . ' seen.');
                }
            }
            if (isset($data['instruction'])) {
                $this->setInstruction($data['instruction']);
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
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
        }
        if (0 < count($this->schedule)) {
            $json['schedule'] = [];
            foreach ($this->schedule as $schedule) {
                $json['schedule'][] = $schedule;
            }
        }
        if (0 < count($this->nutrient)) {
            $json['nutrient'] = [];
            foreach ($this->nutrient as $nutrient) {
                $json['nutrient'][] = $nutrient;
            }
        }
        if (0 < count($this->texture)) {
            $json['texture'] = [];
            foreach ($this->texture as $texture) {
                $json['texture'][] = $texture;
            }
        }
        if (0 < count($this->fluidConsistencyType)) {
            $json['fluidConsistencyType'] = [];
            foreach ($this->fluidConsistencyType as $fluidConsistencyType) {
                $json['fluidConsistencyType'][] = $fluidConsistencyType;
            }
        }
        if (isset($this->instruction)) {
            $json['instruction'] = $this->instruction;
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
            $sxe = new \SimpleXMLElement('<NutritionOrderOralDiet xmlns="http://hl7.org/fhir"></NutritionOrderOralDiet>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (0 < count($this->schedule)) {
            foreach ($this->schedule as $schedule) {
                $schedule->xmlSerialize(true, $sxe->addChild('schedule'));
            }
        }
        if (0 < count($this->nutrient)) {
            foreach ($this->nutrient as $nutrient) {
                $nutrient->xmlSerialize(true, $sxe->addChild('nutrient'));
            }
        }
        if (0 < count($this->texture)) {
            foreach ($this->texture as $texture) {
                $texture->xmlSerialize(true, $sxe->addChild('texture'));
            }
        }
        if (0 < count($this->fluidConsistencyType)) {
            foreach ($this->fluidConsistencyType as $fluidConsistencyType) {
                $fluidConsistencyType->xmlSerialize(true, $sxe->addChild('fluidConsistencyType'));
            }
        }
        if (isset($this->instruction)) {
            $this->instruction->xmlSerialize(true, $sxe->addChild('instruction'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
