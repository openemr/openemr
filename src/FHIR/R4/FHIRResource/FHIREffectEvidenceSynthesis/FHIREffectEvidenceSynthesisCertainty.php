<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIREffectEvidenceSynthesis;

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
 * The EffectEvidenceSynthesis resource describes the difference in an outcome between exposures states in a population where the effect estimate is derived from a combination of research studies.
 */
class FHIREffectEvidenceSynthesisCertainty extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A rating of the certainty of the effect estimate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $rating = [];

    /**
     * A human-readable string to clarify or explain concepts about the resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * A description of a component of the overall certainty.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisCertaintySubcomponent[]
     */
    public $certaintySubcomponent = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'EffectEvidenceSynthesis.Certainty';

    /**
     * A rating of the certainty of the effect estimate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * A rating of the certainty of the effect estimate.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $rating
     * @return $this
     */
    public function addRating($rating)
    {
        $this->rating[] = $rating;
        return $this;
    }

    /**
     * A human-readable string to clarify or explain concepts about the resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * A human-readable string to clarify or explain concepts about the resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * A description of a component of the overall certainty.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisCertaintySubcomponent[]
     */
    public function getCertaintySubcomponent()
    {
        return $this->certaintySubcomponent;
    }

    /**
     * A description of a component of the overall certainty.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIREffectEvidenceSynthesis\FHIREffectEvidenceSynthesisCertaintySubcomponent $certaintySubcomponent
     * @return $this
     */
    public function addCertaintySubcomponent($certaintySubcomponent)
    {
        $this->certaintySubcomponent[] = $certaintySubcomponent;
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
            if (isset($data['rating'])) {
                if (is_array($data['rating'])) {
                    foreach ($data['rating'] as $d) {
                        $this->addRating($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"rating" must be array of objects or null, ' . gettype($data['rating']) . ' seen.');
                }
            }
            if (isset($data['note'])) {
                if (is_array($data['note'])) {
                    foreach ($data['note'] as $d) {
                        $this->addNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"note" must be array of objects or null, ' . gettype($data['note']) . ' seen.');
                }
            }
            if (isset($data['certaintySubcomponent'])) {
                if (is_array($data['certaintySubcomponent'])) {
                    foreach ($data['certaintySubcomponent'] as $d) {
                        $this->addCertaintySubcomponent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"certaintySubcomponent" must be array of objects or null, ' . gettype($data['certaintySubcomponent']) . ' seen.');
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
        if (0 < count($this->rating)) {
            $json['rating'] = [];
            foreach ($this->rating as $rating) {
                $json['rating'][] = $rating;
            }
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
        }
        if (0 < count($this->certaintySubcomponent)) {
            $json['certaintySubcomponent'] = [];
            foreach ($this->certaintySubcomponent as $certaintySubcomponent) {
                $json['certaintySubcomponent'][] = $certaintySubcomponent;
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
            $sxe = new \SimpleXMLElement('<EffectEvidenceSynthesisCertainty xmlns="http://hl7.org/fhir"></EffectEvidenceSynthesisCertainty>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->rating)) {
            foreach ($this->rating as $rating) {
                $rating->xmlSerialize(true, $sxe->addChild('rating'));
            }
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if (0 < count($this->certaintySubcomponent)) {
            foreach ($this->certaintySubcomponent as $certaintySubcomponent) {
                $certaintySubcomponent->xmlSerialize(true, $sxe->addChild('certaintySubcomponent'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
