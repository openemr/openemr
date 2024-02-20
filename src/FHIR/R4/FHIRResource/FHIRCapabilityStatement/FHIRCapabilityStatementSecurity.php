<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement;

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
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR Server for a particular version of FHIR that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRCapabilityStatementSecurity extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Server adds CORS headers when responding to requests - this enables Javascript applications to use the server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $cors = null;

    /**
     * Types of security services that are supported/required by the system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $service = [];

    /**
     * General description of how security works.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $description = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.Security';

    /**
     * Server adds CORS headers when responding to requests - this enables Javascript applications to use the server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getCors()
    {
        return $this->cors;
    }

    /**
     * Server adds CORS headers when responding to requests - this enables Javascript applications to use the server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $cors
     * @return $this
     */
    public function setCors($cors)
    {
        $this->cors = $cors;
        return $this;
    }

    /**
     * Types of security services that are supported/required by the system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Types of security services that are supported/required by the system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $service
     * @return $this
     */
    public function addService($service)
    {
        $this->service[] = $service;
        return $this;
    }

    /**
     * General description of how security works.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * General description of how security works.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
            if (isset($data['cors'])) {
                $this->setCors($data['cors']);
            }
            if (isset($data['service'])) {
                if (is_array($data['service'])) {
                    foreach ($data['service'] as $d) {
                        $this->addService($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"service" must be array of objects or null, ' . gettype($data['service']) . ' seen.');
                }
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
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
        if (isset($this->cors)) {
            $json['cors'] = $this->cors;
        }
        if (0 < count($this->service)) {
            $json['service'] = [];
            foreach ($this->service as $service) {
                $json['service'][] = $service;
            }
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
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
            $sxe = new \SimpleXMLElement('<CapabilityStatementSecurity xmlns="http://hl7.org/fhir"></CapabilityStatementSecurity>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->cors)) {
            $this->cors->xmlSerialize(true, $sxe->addChild('cors'));
        }
        if (0 < count($this->service)) {
            foreach ($this->service as $service) {
                $service->xmlSerialize(true, $sxe->addChild('service'));
            }
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
