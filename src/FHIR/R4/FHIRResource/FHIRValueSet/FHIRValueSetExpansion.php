<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet;

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
 * A ValueSet resource instance specifies a set of codes drawn from one or more code systems, intended for use in a particular context. Value sets link between [[[CodeSystem]]] definitions and their use in [coded elements](terminologies.html).
 */
class FHIRValueSetExpansion extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * An identifier that uniquely identifies this expansion of the valueset, based on a unique combination of the provided parameters, the system default parameters, and the underlying system code system versions etc. Systems may re-use the same identifier as long as those factors remain the same, and the expansion is the same, but are not required to do so. This is a business identifier.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $identifier = null;

    /**
     * The time at which the expansion was produced by the expanding system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $timestamp = null;

    /**
     * The total number of concepts in the expansion. If the number of concept nodes in this resource is less than the stated number, then the server can return more using the offset parameter.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $total = null;

    /**
     * If paging is being used, the offset at which this resource starts.  I.e. this resource is a partial view into the expansion. If paging is not being used, this element SHALL NOT be present.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $offset = null;

    /**
     * A parameter that controlled the expansion process. These parameters may be used by users of expanded value sets to check whether the expansion is suitable for a particular purpose, or to pick the correct expansion.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetParameter[]
     */
    public $parameter = [];

    /**
     * The codes that are contained in the value set expansion.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetContains[]
     */
    public $contains = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ValueSet.Expansion';

    /**
     * An identifier that uniquely identifies this expansion of the valueset, based on a unique combination of the provided parameters, the system default parameters, and the underlying system code system versions etc. Systems may re-use the same identifier as long as those factors remain the same, and the expansion is the same, but are not required to do so. This is a business identifier.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * An identifier that uniquely identifies this expansion of the valueset, based on a unique combination of the provided parameters, the system default parameters, and the underlying system code system versions etc. Systems may re-use the same identifier as long as those factors remain the same, and the expansion is the same, but are not required to do so. This is a business identifier.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The time at which the expansion was produced by the expanding system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * The time at which the expansion was produced by the expanding system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $timestamp
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * The total number of concepts in the expansion. If the number of concept nodes in this resource is less than the stated number, then the server can return more using the offset parameter.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * The total number of concepts in the expansion. If the number of concept nodes in this resource is less than the stated number, then the server can return more using the offset parameter.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * If paging is being used, the offset at which this resource starts.  I.e. this resource is a partial view into the expansion. If paging is not being used, this element SHALL NOT be present.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * If paging is being used, the offset at which this resource starts.  I.e. this resource is a partial view into the expansion. If paging is not being used, this element SHALL NOT be present.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * A parameter that controlled the expansion process. These parameters may be used by users of expanded value sets to check whether the expansion is suitable for a particular purpose, or to pick the correct expansion.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetParameter[]
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * A parameter that controlled the expansion process. These parameters may be used by users of expanded value sets to check whether the expansion is suitable for a particular purpose, or to pick the correct expansion.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetParameter $parameter
     * @return $this
     */
    public function addParameter($parameter)
    {
        $this->parameter[] = $parameter;
        return $this;
    }

    /**
     * The codes that are contained in the value set expansion.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetContains[]
     */
    public function getContains()
    {
        return $this->contains;
    }

    /**
     * The codes that are contained in the value set expansion.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetContains $contains
     * @return $this
     */
    public function addContains($contains)
    {
        $this->contains[] = $contains;
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
            if (isset($data['timestamp'])) {
                $this->setTimestamp($data['timestamp']);
            }
            if (isset($data['total'])) {
                $this->setTotal($data['total']);
            }
            if (isset($data['offset'])) {
                $this->setOffset($data['offset']);
            }
            if (isset($data['parameter'])) {
                if (is_array($data['parameter'])) {
                    foreach ($data['parameter'] as $d) {
                        $this->addParameter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"parameter" must be array of objects or null, ' . gettype($data['parameter']) . ' seen.');
                }
            }
            if (isset($data['contains'])) {
                if (is_array($data['contains'])) {
                    foreach ($data['contains'] as $d) {
                        $this->addContains($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"contains" must be array of objects or null, ' . gettype($data['contains']) . ' seen.');
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->timestamp)) {
            $json['timestamp'] = $this->timestamp;
        }
        if (isset($this->total)) {
            $json['total'] = $this->total;
        }
        if (isset($this->offset)) {
            $json['offset'] = $this->offset;
        }
        if (0 < count($this->parameter)) {
            $json['parameter'] = [];
            foreach ($this->parameter as $parameter) {
                $json['parameter'][] = $parameter;
            }
        }
        if (0 < count($this->contains)) {
            $json['contains'] = [];
            foreach ($this->contains as $contains) {
                $json['contains'][] = $contains;
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
            $sxe = new \SimpleXMLElement('<ValueSetExpansion xmlns="http://hl7.org/fhir"></ValueSetExpansion>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->timestamp)) {
            $this->timestamp->xmlSerialize(true, $sxe->addChild('timestamp'));
        }
        if (isset($this->total)) {
            $this->total->xmlSerialize(true, $sxe->addChild('total'));
        }
        if (isset($this->offset)) {
            $this->offset->xmlSerialize(true, $sxe->addChild('offset'));
        }
        if (0 < count($this->parameter)) {
            foreach ($this->parameter as $parameter) {
                $parameter->xmlSerialize(true, $sxe->addChild('parameter'));
            }
        }
        if (0 < count($this->contains)) {
            foreach ($this->contains as $contains) {
                $contains->xmlSerialize(true, $sxe->addChild('contains'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
