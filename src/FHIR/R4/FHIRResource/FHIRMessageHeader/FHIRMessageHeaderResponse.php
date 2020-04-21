<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRMessageHeader;

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
 * The header for a message exchange that is either requesting or responding to an action.  The reference(s) that are the subject of the action as well as other information related to the action are typically transmitted in a bundle in which the MessageHeader resource instance is the first resource in the bundle.
 */
class FHIRMessageHeaderResponse extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The MessageHeader.id of the message to which this message is a response.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $identifier = null;

    /**
     * Code that identifies the type of response to the message - whether it was successful or not, and whether it should be resent or not.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRResponseType
     */
    public $code = null;

    /**
     * Full details of any issues found in the message.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $details = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MessageHeader.Response';

    /**
     * The MessageHeader.id of the message to which this message is a response.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The MessageHeader.id of the message to which this message is a response.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Code that identifies the type of response to the message - whether it was successful or not, and whether it should be resent or not.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRResponseType
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Code that identifies the type of response to the message - whether it was successful or not, and whether it should be resent or not.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRResponseType $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Full details of any issues found in the message.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Full details of any issues found in the message.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['details'])) {
                $this->setDetails($data['details']);
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->details)) {
            $json['details'] = $this->details;
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
            $sxe = new \SimpleXMLElement('<MessageHeaderResponse xmlns="http://hl7.org/fhir"></MessageHeaderResponse>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->details)) {
            $this->details->xmlSerialize(true, $sxe->addChild('details'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
