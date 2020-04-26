<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRConsent;

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
 * A record of a healthcare consumer’s  choices, which permits or denies identified recipient(s) or recipient role(s) to perform one or more actions within a given policy context, for specific purposes and periods of time.
 */
class FHIRConsentVerification extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Has the instruction been verified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $verified = null;

    /**
     * Who verified the instruction (Patient, Relative or other Authorized Person).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $verifiedWith = null;

    /**
     * Date verification was collected.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $verificationDate = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Consent.Verification';

    /**
     * Has the instruction been verified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Has the instruction been verified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $verified
     * @return $this
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;
        return $this;
    }

    /**
     * Who verified the instruction (Patient, Relative or other Authorized Person).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getVerifiedWith()
    {
        return $this->verifiedWith;
    }

    /**
     * Who verified the instruction (Patient, Relative or other Authorized Person).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $verifiedWith
     * @return $this
     */
    public function setVerifiedWith($verifiedWith)
    {
        $this->verifiedWith = $verifiedWith;
        return $this;
    }

    /**
     * Date verification was collected.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getVerificationDate()
    {
        return $this->verificationDate;
    }

    /**
     * Date verification was collected.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $verificationDate
     * @return $this
     */
    public function setVerificationDate($verificationDate)
    {
        $this->verificationDate = $verificationDate;
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
            if (isset($data['verified'])) {
                $this->setVerified($data['verified']);
            }
            if (isset($data['verifiedWith'])) {
                $this->setVerifiedWith($data['verifiedWith']);
            }
            if (isset($data['verificationDate'])) {
                $this->setVerificationDate($data['verificationDate']);
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
        if (isset($this->verified)) {
            $json['verified'] = $this->verified;
        }
        if (isset($this->verifiedWith)) {
            $json['verifiedWith'] = $this->verifiedWith;
        }
        if (isset($this->verificationDate)) {
            $json['verificationDate'] = $this->verificationDate;
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
            $sxe = new \SimpleXMLElement('<ConsentVerification xmlns="http://hl7.org/fhir"></ConsentVerification>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->verified)) {
            $this->verified->xmlSerialize(true, $sxe->addChild('verified'));
        }
        if (isset($this->verifiedWith)) {
            $this->verifiedWith->xmlSerialize(true, $sxe->addChild('verifiedWith'));
        }
        if (isset($this->verificationDate)) {
            $this->verificationDate->xmlSerialize(true, $sxe->addChild('verificationDate'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
