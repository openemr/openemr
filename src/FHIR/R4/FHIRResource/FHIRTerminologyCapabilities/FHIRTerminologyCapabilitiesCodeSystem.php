<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities;

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
 * A TerminologyCapabilities resource documents a set of capabilities (behaviors) of a FHIR Terminology Server that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRTerminologyCapabilitiesCodeSystem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * URI for the Code System.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $uri = null;

    /**
     * For the code system, a list of versions that are supported by the server.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesVersion[]
     */
    public $version = [];

    /**
     * True if subsumption is supported for this version of the code system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $subsumption = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TerminologyCapabilities.CodeSystem';

    /**
     * URI for the Code System.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * URI for the Code System.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * For the code system, a list of versions that are supported by the server.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesVersion[]
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * For the code system, a list of versions that are supported by the server.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesVersion $version
     * @return $this
     */
    public function addVersion($version)
    {
        $this->version[] = $version;
        return $this;
    }

    /**
     * True if subsumption is supported for this version of the code system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getSubsumption()
    {
        return $this->subsumption;
    }

    /**
     * True if subsumption is supported for this version of the code system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $subsumption
     * @return $this
     */
    public function setSubsumption($subsumption)
    {
        $this->subsumption = $subsumption;
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
            if (isset($data['uri'])) {
                $this->setUri($data['uri']);
            }
            if (isset($data['version'])) {
                if (is_array($data['version'])) {
                    foreach ($data['version'] as $d) {
                        $this->addVersion($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"version" must be array of objects or null, ' . gettype($data['version']) . ' seen.');
                }
            }
            if (isset($data['subsumption'])) {
                $this->setSubsumption($data['subsumption']);
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
        if (isset($this->uri)) {
            $json['uri'] = $this->uri;
        }
        if (0 < count($this->version)) {
            $json['version'] = [];
            foreach ($this->version as $version) {
                $json['version'][] = $version;
            }
        }
        if (isset($this->subsumption)) {
            $json['subsumption'] = $this->subsumption;
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
            $sxe = new \SimpleXMLElement('<TerminologyCapabilitiesCodeSystem xmlns="http://hl7.org/fhir"></TerminologyCapabilitiesCodeSystem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->uri)) {
            $this->uri->xmlSerialize(true, $sxe->addChild('uri'));
        }
        if (0 < count($this->version)) {
            foreach ($this->version as $version) {
                $version->xmlSerialize(true, $sxe->addChild('version'));
            }
        }
        if (isset($this->subsumption)) {
            $this->subsumption->xmlSerialize(true, $sxe->addChild('subsumption'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
