<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRContract;

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
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a policy or agreement.
 */
class FHIRContractSigner extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Role of this Contract signer, e.g. notary, grantee.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $type = null;

    /**
     * Party which is a signator to this Contract.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $party = null;

    /**
     * Legally binding Contract DSIG signature contents in Base64.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature[]
     */
    public $signature = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Contract.Signer';

    /**
     * Role of this Contract signer, e.g. notary, grantee.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Role of this Contract signer, e.g. notary, grantee.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Party which is a signator to this Contract.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * Party which is a signator to this Contract.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $party
     * @return $this
     */
    public function setParty($party)
    {
        $this->party = $party;
        return $this;
    }

    /**
     * Legally binding Contract DSIG signature contents in Base64.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature[]
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Legally binding Contract DSIG signature contents in Base64.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature $signature
     * @return $this
     */
    public function addSignature($signature)
    {
        $this->signature[] = $signature;
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
                $this->setType($data['type']);
            }
            if (isset($data['party'])) {
                $this->setParty($data['party']);
            }
            if (isset($data['signature'])) {
                if (is_array($data['signature'])) {
                    foreach ($data['signature'] as $d) {
                        $this->addSignature($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"signature" must be array of objects or null, ' . gettype($data['signature']) . ' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->party)) {
            $json['party'] = $this->party;
        }
        if (0 < count($this->signature)) {
            $json['signature'] = [];
            foreach ($this->signature as $signature) {
                $json['signature'][] = $signature;
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
            $sxe = new \SimpleXMLElement('<ContractSigner xmlns="http://hl7.org/fhir"></ContractSigner>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->party)) {
            $this->party->xmlSerialize(true, $sxe->addChild('party'));
        }
        if (0 < count($this->signature)) {
            foreach ($this->signature as $signature) {
                $signature->xmlSerialize(true, $sxe->addChild('signature'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
