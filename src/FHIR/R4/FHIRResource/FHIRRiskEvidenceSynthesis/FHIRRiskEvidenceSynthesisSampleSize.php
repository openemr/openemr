<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRRiskEvidenceSynthesis;

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
 * The RiskEvidenceSynthesis resource describes the likelihood of an outcome in a population plus exposure state where the risk estimate is derived from a combination of research studies.
 */
class FHIRRiskEvidenceSynthesisSampleSize extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Human-readable summary of sample size.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Number of studies included in this evidence synthesis.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $numberOfStudies = null;

    /**
     * Number of participants included in this evidence synthesis.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $numberOfParticipants = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'RiskEvidenceSynthesis.SampleSize';

    /**
     * Human-readable summary of sample size.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Human-readable summary of sample size.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Number of studies included in this evidence synthesis.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getNumberOfStudies()
    {
        return $this->numberOfStudies;
    }

    /**
     * Number of studies included in this evidence synthesis.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $numberOfStudies
     * @return $this
     */
    public function setNumberOfStudies($numberOfStudies)
    {
        $this->numberOfStudies = $numberOfStudies;
        return $this;
    }

    /**
     * Number of participants included in this evidence synthesis.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getNumberOfParticipants()
    {
        return $this->numberOfParticipants;
    }

    /**
     * Number of participants included in this evidence synthesis.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $numberOfParticipants
     * @return $this
     */
    public function setNumberOfParticipants($numberOfParticipants)
    {
        $this->numberOfParticipants = $numberOfParticipants;
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
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['numberOfStudies'])) {
                $this->setNumberOfStudies($data['numberOfStudies']);
            }
            if (isset($data['numberOfParticipants'])) {
                $this->setNumberOfParticipants($data['numberOfParticipants']);
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
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->numberOfStudies)) {
            $json['numberOfStudies'] = $this->numberOfStudies;
        }
        if (isset($this->numberOfParticipants)) {
            $json['numberOfParticipants'] = $this->numberOfParticipants;
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
            $sxe = new \SimpleXMLElement('<RiskEvidenceSynthesisSampleSize xmlns="http://hl7.org/fhir"></RiskEvidenceSynthesisSampleSize>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->numberOfStudies)) {
            $this->numberOfStudies->xmlSerialize(true, $sxe->addChild('numberOfStudies'));
        }
        if (isset($this->numberOfParticipants)) {
            $this->numberOfParticipants->xmlSerialize(true, $sxe->addChild('numberOfParticipants'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
