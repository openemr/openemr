<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRImmunizationRecommendation;

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
 * A patient's point-in-time set of recommendations (i.e. forecasting) according to a published schedule with optional supporting justification.
 */
class FHIRImmunizationRecommendationRecommendation extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Vaccine(s) or vaccine group that pertain to the recommendation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $vaccineCode = [];

    /**
     * The targeted disease for the recommendation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $targetDisease = null;

    /**
     * Vaccine(s) which should not be used to fulfill the recommendation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $contraindicatedVaccineCode = [];

    /**
     * Indicates the patient status with respect to the path to immunity for the target disease.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $forecastStatus = null;

    /**
     * The reason for the assigned forecast status.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $forecastReason = [];

    /**
     * Vaccine date recommendations.  For example, earliest date to administer, latest date to administer, etc.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationDateCriterion[]
     */
    public $dateCriterion = [];

    /**
     * Contains the description about the protocol under which the vaccine was administered.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $series = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public $doseNumberPositiveInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $doseNumberString = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public $seriesDosesPositiveInt = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $seriesDosesString = null;

    /**
     * Immunization event history and/or evaluation that supports the status and recommendation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $supportingImmunization = [];

    /**
     * Patient Information that supports the status and recommendation.  This includes patient observations, adverse reactions and allergy/intolerance information.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $supportingPatientInformation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ImmunizationRecommendation.Recommendation';

    /**
     * Vaccine(s) or vaccine group that pertain to the recommendation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getVaccineCode()
    {
        return $this->vaccineCode;
    }

    /**
     * Vaccine(s) or vaccine group that pertain to the recommendation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $vaccineCode
     * @return $this
     */
    public function addVaccineCode($vaccineCode)
    {
        $this->vaccineCode[] = $vaccineCode;
        return $this;
    }

    /**
     * The targeted disease for the recommendation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getTargetDisease()
    {
        return $this->targetDisease;
    }

    /**
     * The targeted disease for the recommendation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $targetDisease
     * @return $this
     */
    public function setTargetDisease($targetDisease)
    {
        $this->targetDisease = $targetDisease;
        return $this;
    }

    /**
     * Vaccine(s) which should not be used to fulfill the recommendation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getContraindicatedVaccineCode()
    {
        return $this->contraindicatedVaccineCode;
    }

    /**
     * Vaccine(s) which should not be used to fulfill the recommendation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $contraindicatedVaccineCode
     * @return $this
     */
    public function addContraindicatedVaccineCode($contraindicatedVaccineCode)
    {
        $this->contraindicatedVaccineCode[] = $contraindicatedVaccineCode;
        return $this;
    }

    /**
     * Indicates the patient status with respect to the path to immunity for the target disease.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getForecastStatus()
    {
        return $this->forecastStatus;
    }

    /**
     * Indicates the patient status with respect to the path to immunity for the target disease.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $forecastStatus
     * @return $this
     */
    public function setForecastStatus($forecastStatus)
    {
        $this->forecastStatus = $forecastStatus;
        return $this;
    }

    /**
     * The reason for the assigned forecast status.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getForecastReason()
    {
        return $this->forecastReason;
    }

    /**
     * The reason for the assigned forecast status.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $forecastReason
     * @return $this
     */
    public function addForecastReason($forecastReason)
    {
        $this->forecastReason[] = $forecastReason;
        return $this;
    }

    /**
     * Vaccine date recommendations.  For example, earliest date to administer, latest date to administer, etc.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationDateCriterion[]
     */
    public function getDateCriterion()
    {
        return $this->dateCriterion;
    }

    /**
     * Vaccine date recommendations.  For example, earliest date to administer, latest date to administer, etc.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationDateCriterion $dateCriterion
     * @return $this
     */
    public function addDateCriterion($dateCriterion)
    {
        $this->dateCriterion[] = $dateCriterion;
        return $this;
    }

    /**
     * Contains the description about the protocol under which the vaccine was administered.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Contains the description about the protocol under which the vaccine was administered.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $series
     * @return $this
     */
    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public function getDoseNumberPositiveInt()
    {
        return $this->doseNumberPositiveInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $doseNumberPositiveInt
     * @return $this
     */
    public function setDoseNumberPositiveInt($doseNumberPositiveInt)
    {
        $this->doseNumberPositiveInt = $doseNumberPositiveInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDoseNumberString()
    {
        return $this->doseNumberString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $doseNumberString
     * @return $this
     */
    public function setDoseNumberString($doseNumberString)
    {
        $this->doseNumberString = $doseNumberString;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public function getSeriesDosesPositiveInt()
    {
        return $this->seriesDosesPositiveInt;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $seriesDosesPositiveInt
     * @return $this
     */
    public function setSeriesDosesPositiveInt($seriesDosesPositiveInt)
    {
        $this->seriesDosesPositiveInt = $seriesDosesPositiveInt;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getSeriesDosesString()
    {
        return $this->seriesDosesString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $seriesDosesString
     * @return $this
     */
    public function setSeriesDosesString($seriesDosesString)
    {
        $this->seriesDosesString = $seriesDosesString;
        return $this;
    }

    /**
     * Immunization event history and/or evaluation that supports the status and recommendation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSupportingImmunization()
    {
        return $this->supportingImmunization;
    }

    /**
     * Immunization event history and/or evaluation that supports the status and recommendation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $supportingImmunization
     * @return $this
     */
    public function addSupportingImmunization($supportingImmunization)
    {
        $this->supportingImmunization[] = $supportingImmunization;
        return $this;
    }

    /**
     * Patient Information that supports the status and recommendation.  This includes patient observations, adverse reactions and allergy/intolerance information.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSupportingPatientInformation()
    {
        return $this->supportingPatientInformation;
    }

    /**
     * Patient Information that supports the status and recommendation.  This includes patient observations, adverse reactions and allergy/intolerance information.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $supportingPatientInformation
     * @return $this
     */
    public function addSupportingPatientInformation($supportingPatientInformation)
    {
        $this->supportingPatientInformation[] = $supportingPatientInformation;
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
            if (isset($data['vaccineCode'])) {
                if (is_array($data['vaccineCode'])) {
                    foreach ($data['vaccineCode'] as $d) {
                        $this->addVaccineCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"vaccineCode" must be array of objects or null, ' . gettype($data['vaccineCode']) . ' seen.');
                }
            }
            if (isset($data['targetDisease'])) {
                $this->setTargetDisease($data['targetDisease']);
            }
            if (isset($data['contraindicatedVaccineCode'])) {
                if (is_array($data['contraindicatedVaccineCode'])) {
                    foreach ($data['contraindicatedVaccineCode'] as $d) {
                        $this->addContraindicatedVaccineCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contraindicatedVaccineCode" must be array of objects or null, ' . gettype($data['contraindicatedVaccineCode']) . ' seen.');
                }
            }
            if (isset($data['forecastStatus'])) {
                $this->setForecastStatus($data['forecastStatus']);
            }
            if (isset($data['forecastReason'])) {
                if (is_array($data['forecastReason'])) {
                    foreach ($data['forecastReason'] as $d) {
                        $this->addForecastReason($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"forecastReason" must be array of objects or null, ' . gettype($data['forecastReason']) . ' seen.');
                }
            }
            if (isset($data['dateCriterion'])) {
                if (is_array($data['dateCriterion'])) {
                    foreach ($data['dateCriterion'] as $d) {
                        $this->addDateCriterion($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dateCriterion" must be array of objects or null, ' . gettype($data['dateCriterion']) . ' seen.');
                }
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['series'])) {
                $this->setSeries($data['series']);
            }
            if (isset($data['doseNumberPositiveInt'])) {
                $this->setDoseNumberPositiveInt($data['doseNumberPositiveInt']);
            }
            if (isset($data['doseNumberString'])) {
                $this->setDoseNumberString($data['doseNumberString']);
            }
            if (isset($data['seriesDosesPositiveInt'])) {
                $this->setSeriesDosesPositiveInt($data['seriesDosesPositiveInt']);
            }
            if (isset($data['seriesDosesString'])) {
                $this->setSeriesDosesString($data['seriesDosesString']);
            }
            if (isset($data['supportingImmunization'])) {
                if (is_array($data['supportingImmunization'])) {
                    foreach ($data['supportingImmunization'] as $d) {
                        $this->addSupportingImmunization($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportingImmunization" must be array of objects or null, ' . gettype($data['supportingImmunization']) . ' seen.');
                }
            }
            if (isset($data['supportingPatientInformation'])) {
                if (is_array($data['supportingPatientInformation'])) {
                    foreach ($data['supportingPatientInformation'] as $d) {
                        $this->addSupportingPatientInformation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportingPatientInformation" must be array of objects or null, ' . gettype($data['supportingPatientInformation']) . ' seen.');
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
        if (0 < count($this->vaccineCode)) {
            $json['vaccineCode'] = [];
            foreach ($this->vaccineCode as $vaccineCode) {
                $json['vaccineCode'][] = $vaccineCode;
            }
        }
        if (isset($this->targetDisease)) {
            $json['targetDisease'] = $this->targetDisease;
        }
        if (0 < count($this->contraindicatedVaccineCode)) {
            $json['contraindicatedVaccineCode'] = [];
            foreach ($this->contraindicatedVaccineCode as $contraindicatedVaccineCode) {
                $json['contraindicatedVaccineCode'][] = $contraindicatedVaccineCode;
            }
        }
        if (isset($this->forecastStatus)) {
            $json['forecastStatus'] = $this->forecastStatus;
        }
        if (0 < count($this->forecastReason)) {
            $json['forecastReason'] = [];
            foreach ($this->forecastReason as $forecastReason) {
                $json['forecastReason'][] = $forecastReason;
            }
        }
        if (0 < count($this->dateCriterion)) {
            $json['dateCriterion'] = [];
            foreach ($this->dateCriterion as $dateCriterion) {
                $json['dateCriterion'][] = $dateCriterion;
            }
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->series)) {
            $json['series'] = $this->series;
        }
        if (isset($this->doseNumberPositiveInt)) {
            $json['doseNumberPositiveInt'] = $this->doseNumberPositiveInt;
        }
        if (isset($this->doseNumberString)) {
            $json['doseNumberString'] = $this->doseNumberString;
        }
        if (isset($this->seriesDosesPositiveInt)) {
            $json['seriesDosesPositiveInt'] = $this->seriesDosesPositiveInt;
        }
        if (isset($this->seriesDosesString)) {
            $json['seriesDosesString'] = $this->seriesDosesString;
        }
        if (0 < count($this->supportingImmunization)) {
            $json['supportingImmunization'] = [];
            foreach ($this->supportingImmunization as $supportingImmunization) {
                $json['supportingImmunization'][] = $supportingImmunization;
            }
        }
        if (0 < count($this->supportingPatientInformation)) {
            $json['supportingPatientInformation'] = [];
            foreach ($this->supportingPatientInformation as $supportingPatientInformation) {
                $json['supportingPatientInformation'][] = $supportingPatientInformation;
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
            $sxe = new \SimpleXMLElement('<ImmunizationRecommendationRecommendation xmlns="http://hl7.org/fhir"></ImmunizationRecommendationRecommendation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->vaccineCode)) {
            foreach ($this->vaccineCode as $vaccineCode) {
                $vaccineCode->xmlSerialize(true, $sxe->addChild('vaccineCode'));
            }
        }
        if (isset($this->targetDisease)) {
            $this->targetDisease->xmlSerialize(true, $sxe->addChild('targetDisease'));
        }
        if (0 < count($this->contraindicatedVaccineCode)) {
            foreach ($this->contraindicatedVaccineCode as $contraindicatedVaccineCode) {
                $contraindicatedVaccineCode->xmlSerialize(true, $sxe->addChild('contraindicatedVaccineCode'));
            }
        }
        if (isset($this->forecastStatus)) {
            $this->forecastStatus->xmlSerialize(true, $sxe->addChild('forecastStatus'));
        }
        if (0 < count($this->forecastReason)) {
            foreach ($this->forecastReason as $forecastReason) {
                $forecastReason->xmlSerialize(true, $sxe->addChild('forecastReason'));
            }
        }
        if (0 < count($this->dateCriterion)) {
            foreach ($this->dateCriterion as $dateCriterion) {
                $dateCriterion->xmlSerialize(true, $sxe->addChild('dateCriterion'));
            }
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->series)) {
            $this->series->xmlSerialize(true, $sxe->addChild('series'));
        }
        if (isset($this->doseNumberPositiveInt)) {
            $this->doseNumberPositiveInt->xmlSerialize(true, $sxe->addChild('doseNumberPositiveInt'));
        }
        if (isset($this->doseNumberString)) {
            $this->doseNumberString->xmlSerialize(true, $sxe->addChild('doseNumberString'));
        }
        if (isset($this->seriesDosesPositiveInt)) {
            $this->seriesDosesPositiveInt->xmlSerialize(true, $sxe->addChild('seriesDosesPositiveInt'));
        }
        if (isset($this->seriesDosesString)) {
            $this->seriesDosesString->xmlSerialize(true, $sxe->addChild('seriesDosesString'));
        }
        if (0 < count($this->supportingImmunization)) {
            foreach ($this->supportingImmunization as $supportingImmunization) {
                $supportingImmunization->xmlSerialize(true, $sxe->addChild('supportingImmunization'));
            }
        }
        if (0 < count($this->supportingPatientInformation)) {
            foreach ($this->supportingPatientInformation as $supportingPatientInformation) {
                $supportingPatientInformation->xmlSerialize(true, $sxe->addChild('supportingPatientInformation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
