<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRClaimResponse;

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
 * This resource provides the adjudication details from the processing of a Claim resource.
 */
class FHIRClaimResponseDetail extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A number to uniquely reference the claim detail entry.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public $detailSequence = null;

    /**
     * The numbers associated with notes below which apply to the adjudication of this item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public $noteNumber = [];

    /**
     * The adjudication results.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRClaimResponse\FHIRClaimResponseAdjudication[]
     */
    public $adjudication = [];

    /**
     * A sub-detail adjudication of a simple product or service.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRClaimResponse\FHIRClaimResponseSubDetail[]
     */
    public $subDetail = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ClaimResponse.Detail';

    /**
     * A number to uniquely reference the claim detail entry.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt
     */
    public function getDetailSequence()
    {
        return $this->detailSequence;
    }

    /**
     * A number to uniquely reference the claim detail entry.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $detailSequence
     * @return $this
     */
    public function setDetailSequence($detailSequence)
    {
        $this->detailSequence = $detailSequence;
        return $this;
    }

    /**
     * The numbers associated with notes below which apply to the adjudication of this item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt[]
     */
    public function getNoteNumber()
    {
        return $this->noteNumber;
    }

    /**
     * The numbers associated with notes below which apply to the adjudication of this item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPositiveInt $noteNumber
     * @return $this
     */
    public function addNoteNumber($noteNumber)
    {
        $this->noteNumber[] = $noteNumber;
        return $this;
    }

    /**
     * The adjudication results.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRClaimResponse\FHIRClaimResponseAdjudication[]
     */
    public function getAdjudication()
    {
        return $this->adjudication;
    }

    /**
     * The adjudication results.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRClaimResponse\FHIRClaimResponseAdjudication $adjudication
     * @return $this
     */
    public function addAdjudication($adjudication)
    {
        $this->adjudication[] = $adjudication;
        return $this;
    }

    /**
     * A sub-detail adjudication of a simple product or service.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRClaimResponse\FHIRClaimResponseSubDetail[]
     */
    public function getSubDetail()
    {
        return $this->subDetail;
    }

    /**
     * A sub-detail adjudication of a simple product or service.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRClaimResponse\FHIRClaimResponseSubDetail $subDetail
     * @return $this
     */
    public function addSubDetail($subDetail)
    {
        $this->subDetail[] = $subDetail;
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
            if (isset($data['detailSequence'])) {
                $this->setDetailSequence($data['detailSequence']);
            }
            if (isset($data['noteNumber'])) {
                if (is_array($data['noteNumber'])) {
                    foreach ($data['noteNumber'] as $d) {
                        $this->addNoteNumber($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"noteNumber" must be array of objects or null, ' . gettype($data['noteNumber']) . ' seen.');
                }
            }
            if (isset($data['adjudication'])) {
                if (is_array($data['adjudication'])) {
                    foreach ($data['adjudication'] as $d) {
                        $this->addAdjudication($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"adjudication" must be array of objects or null, ' . gettype($data['adjudication']) . ' seen.');
                }
            }
            if (isset($data['subDetail'])) {
                if (is_array($data['subDetail'])) {
                    foreach ($data['subDetail'] as $d) {
                        $this->addSubDetail($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subDetail" must be array of objects or null, ' . gettype($data['subDetail']) . ' seen.');
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
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->detailSequence)) {
            $json['detailSequence'] = $this->detailSequence;
        }
        if (0 < count($this->noteNumber)) {
            $json['noteNumber'] = [];
            foreach ($this->noteNumber as $noteNumber) {
                $json['noteNumber'][] = $noteNumber;
            }
        }
        if (0 < count($this->adjudication)) {
            $json['adjudication'] = [];
            foreach ($this->adjudication as $adjudication) {
                $json['adjudication'][] = $adjudication;
            }
        }
        if (0 < count($this->subDetail)) {
            $json['subDetail'] = [];
            foreach ($this->subDetail as $subDetail) {
                $json['subDetail'][] = $subDetail;
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
            $sxe = new \SimpleXMLElement('<ClaimResponseDetail xmlns="http://hl7.org/fhir"></ClaimResponseDetail>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->detailSequence)) {
            $this->detailSequence->xmlSerialize(true, $sxe->addChild('detailSequence'));
        }
        if (0 < count($this->noteNumber)) {
            foreach ($this->noteNumber as $noteNumber) {
                $noteNumber->xmlSerialize(true, $sxe->addChild('noteNumber'));
            }
        }
        if (0 < count($this->adjudication)) {
            foreach ($this->adjudication as $adjudication) {
                $adjudication->xmlSerialize(true, $sxe->addChild('adjudication'));
            }
        }
        if (0 < count($this->subDetail)) {
            foreach ($this->subDetail as $subDetail) {
                $subDetail->xmlSerialize(true, $sxe->addChild('subDetail'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
