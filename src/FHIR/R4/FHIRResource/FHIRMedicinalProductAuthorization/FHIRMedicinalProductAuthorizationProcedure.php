<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization;

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
 * The regulatory authorization of a medicinal product.
 */
class FHIRMedicinalProductAuthorizationProcedure extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifier for this procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * Type of procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $datePeriod = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $dateDateTime = null;

    /**
     * Applcations submitted to obtain a marketing authorization.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure[]
     */
    public $application = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductAuthorization.Procedure';

    /**
     * Identifier for this procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier for this procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Type of procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type of procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getDatePeriod()
    {
        return $this->datePeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $datePeriod
     * @return $this
     */
    public function setDatePeriod($datePeriod)
    {
        $this->datePeriod = $datePeriod;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDateDateTime()
    {
        return $this->dateDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $dateDateTime
     * @return $this
     */
    public function setDateDateTime($dateDateTime)
    {
        $this->dateDateTime = $dateDateTime;
        return $this;
    }

    /**
     * Applcations submitted to obtain a marketing authorization.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure[]
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Applcations submitted to obtain a marketing authorization.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure $application
     * @return $this
     */
    public function addApplication($application)
    {
        $this->application[] = $application;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['datePeriod'])) {
                $this->setDatePeriod($data['datePeriod']);
            }
            if (isset($data['dateDateTime'])) {
                $this->setDateDateTime($data['dateDateTime']);
            }
            if (isset($data['application'])) {
                if (is_array($data['application'])) {
                    foreach ($data['application'] as $d) {
                        $this->addApplication($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"application" must be array of objects or null, ' . gettype($data['application']) . ' seen.');
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->datePeriod)) {
            $json['datePeriod'] = $this->datePeriod;
        }
        if (isset($this->dateDateTime)) {
            $json['dateDateTime'] = $this->dateDateTime;
        }
        if (0 < count($this->application)) {
            $json['application'] = [];
            foreach ($this->application as $application) {
                $json['application'][] = $application;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductAuthorizationProcedure xmlns="http://hl7.org/fhir"></MedicinalProductAuthorizationProcedure>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->datePeriod)) {
            $this->datePeriod->xmlSerialize(true, $sxe->addChild('datePeriod'));
        }
        if (isset($this->dateDateTime)) {
            $this->dateDateTime->xmlSerialize(true, $sxe->addChild('dateDateTime'));
        }
        if (0 < count($this->application)) {
            foreach ($this->application as $application) {
                $application->xmlSerialize(true, $sxe->addChild('application'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
