<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRImmunization;

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
 * Describes the event of a patient being administered a vaccine or a record of an immunization as reported by a patient, a clinician or another party.
 */
class FHIRImmunizationProtocolApplied extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $series = null;

    /**
     * Indicates the authority who published the protocol (e.g. ACIP) that is being followed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $authority = null;

    /**
     * The vaccine preventable disease the dose is being administered against.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $targetDisease = [];

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
     * @var string
     */
    private $_fhirElementName = 'Immunization.ProtocolApplied';

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
     * Indicates the authority who published the protocol (e.g. ACIP) that is being followed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * Indicates the authority who published the protocol (e.g. ACIP) that is being followed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $authority
     * @return $this
     */
    public function setAuthority($authority)
    {
        $this->authority = $authority;
        return $this;
    }

    /**
     * The vaccine preventable disease the dose is being administered against.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getTargetDisease()
    {
        return $this->targetDisease;
    }

    /**
     * The vaccine preventable disease the dose is being administered against.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $targetDisease
     * @return $this
     */
    public function addTargetDisease($targetDisease)
    {
        $this->targetDisease[] = $targetDisease;
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
            if (isset($data['series'])) {
                $this->setSeries($data['series']);
            }
            if (isset($data['authority'])) {
                $this->setAuthority($data['authority']);
            }
            if (isset($data['targetDisease'])) {
                if (is_array($data['targetDisease'])) {
                    foreach ($data['targetDisease'] as $d) {
                        $this->addTargetDisease($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"targetDisease" must be array of objects or null, ' . gettype($data['targetDisease']) . ' seen.');
                }
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
        if (isset($this->series)) {
            $json['series'] = $this->series;
        }
        if (isset($this->authority)) {
            $json['authority'] = $this->authority;
        }
        if (0 < count($this->targetDisease)) {
            $json['targetDisease'] = [];
            foreach ($this->targetDisease as $targetDisease) {
                $json['targetDisease'][] = $targetDisease;
            }
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
            $sxe = new \SimpleXMLElement('<ImmunizationProtocolApplied xmlns="http://hl7.org/fhir"></ImmunizationProtocolApplied>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->series)) {
            $this->series->xmlSerialize(true, $sxe->addChild('series'));
        }
        if (isset($this->authority)) {
            $this->authority->xmlSerialize(true, $sxe->addChild('authority'));
        }
        if (0 < count($this->targetDisease)) {
            foreach ($this->targetDisease as $targetDisease) {
                $targetDisease->xmlSerialize(true, $sxe->addChild('targetDisease'));
            }
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
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
