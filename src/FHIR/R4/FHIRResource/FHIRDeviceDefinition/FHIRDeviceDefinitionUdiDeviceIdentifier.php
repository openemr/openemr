<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRDeviceDefinition;

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
 * The characteristics, operational status and capabilities of a medical-related component of a medical device.
 */
class FHIRDeviceDefinitionUdiDeviceIdentifier extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The identifier that is to be associated with every Device that references this DeviceDefintiion for the issuer and jurisdication porvided in the DeviceDefinition.udiDeviceIdentifier.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $deviceIdentifier = null;

    /**
     * The organization that assigns the identifier algorithm.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $issuer = null;

    /**
     * The jurisdiction to which the deviceIdentifier applies.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $jurisdiction = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'DeviceDefinition.UdiDeviceIdentifier';

    /**
     * The identifier that is to be associated with every Device that references this DeviceDefintiion for the issuer and jurisdication porvided in the DeviceDefinition.udiDeviceIdentifier.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDeviceIdentifier()
    {
        return $this->deviceIdentifier;
    }

    /**
     * The identifier that is to be associated with every Device that references this DeviceDefintiion for the issuer and jurisdication porvided in the DeviceDefinition.udiDeviceIdentifier.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $deviceIdentifier
     * @return $this
     */
    public function setDeviceIdentifier($deviceIdentifier)
    {
        $this->deviceIdentifier = $deviceIdentifier;
        return $this;
    }

    /**
     * The organization that assigns the identifier algorithm.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * The organization that assigns the identifier algorithm.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $issuer
     * @return $this
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * The jurisdiction to which the deviceIdentifier applies.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * The jurisdiction to which the deviceIdentifier applies.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $jurisdiction
     * @return $this
     */
    public function setJurisdiction($jurisdiction)
    {
        $this->jurisdiction = $jurisdiction;
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
            if (isset($data['deviceIdentifier'])) {
                $this->setDeviceIdentifier($data['deviceIdentifier']);
            }
            if (isset($data['issuer'])) {
                $this->setIssuer($data['issuer']);
            }
            if (isset($data['jurisdiction'])) {
                $this->setJurisdiction($data['jurisdiction']);
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
        if (isset($this->deviceIdentifier)) {
            $json['deviceIdentifier'] = $this->deviceIdentifier;
        }
        if (isset($this->issuer)) {
            $json['issuer'] = $this->issuer;
        }
        if (isset($this->jurisdiction)) {
            $json['jurisdiction'] = $this->jurisdiction;
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
            $sxe = new \SimpleXMLElement('<DeviceDefinitionUdiDeviceIdentifier xmlns="http://hl7.org/fhir"></DeviceDefinitionUdiDeviceIdentifier>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->deviceIdentifier)) {
            $this->deviceIdentifier->xmlSerialize(true, $sxe->addChild('deviceIdentifier'));
        }
        if (isset($this->issuer)) {
            $this->issuer->xmlSerialize(true, $sxe->addChild('issuer'));
        }
        if (isset($this->jurisdiction)) {
            $this->jurisdiction->xmlSerialize(true, $sxe->addChild('jurisdiction'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
