<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition;

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
 * A formal computable definition of a graph of resources - that is, a coherent set of resources that form a graph by following references. The Graph Definition resource defines a set and makes rules about the set.
 */
class FHIRGraphDefinitionTarget extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Type of resource this link refers to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $type = null;

    /**
     * A set of parameters to look up.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $params = null;

    /**
     * Profile for the target resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $profile = null;

    /**
     * Compartment Consistency Rules.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionCompartment[]
     */
    public $compartment = [];

    /**
     * Additional links from target resource.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionLink[]
     */
    public $link = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'GraphDefinition.Target';

    /**
     * Type of resource this link refers to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type of resource this link refers to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A set of parameters to look up.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * A set of parameters to look up.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Profile for the target resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Profile for the target resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Compartment Consistency Rules.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionCompartment[]
     */
    public function getCompartment()
    {
        return $this->compartment;
    }

    /**
     * Compartment Consistency Rules.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionCompartment $compartment
     * @return $this
     */
    public function addCompartment($compartment)
    {
        $this->compartment[] = $compartment;
        return $this;
    }

    /**
     * Additional links from target resource.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionLink[]
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Additional links from target resource.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRGraphDefinition\FHIRGraphDefinitionLink $link
     * @return $this
     */
    public function addLink($link)
    {
        $this->link[] = $link;
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
            if (isset($data['params'])) {
                $this->setParams($data['params']);
            }
            if (isset($data['profile'])) {
                $this->setProfile($data['profile']);
            }
            if (isset($data['compartment'])) {
                if (is_array($data['compartment'])) {
                    foreach ($data['compartment'] as $d) {
                        $this->addCompartment($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"compartment" must be array of objects or null, ' . gettype($data['compartment']) . ' seen.');
                }
            }
            if (isset($data['link'])) {
                if (is_array($data['link'])) {
                    foreach ($data['link'] as $d) {
                        $this->addLink($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"link" must be array of objects or null, ' . gettype($data['link']) . ' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->params)) {
            $json['params'] = $this->params;
        }
        if (isset($this->profile)) {
            $json['profile'] = $this->profile;
        }
        if (0 < count($this->compartment)) {
            $json['compartment'] = [];
            foreach ($this->compartment as $compartment) {
                $json['compartment'][] = $compartment;
            }
        }
        if (0 < count($this->link)) {
            $json['link'] = [];
            foreach ($this->link as $link) {
                $json['link'][] = $link;
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
            $sxe = new \SimpleXMLElement('<GraphDefinitionTarget xmlns="http://hl7.org/fhir"></GraphDefinitionTarget>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->params)) {
            $this->params->xmlSerialize(true, $sxe->addChild('params'));
        }
        if (isset($this->profile)) {
            $this->profile->xmlSerialize(true, $sxe->addChild('profile'));
        }
        if (0 < count($this->compartment)) {
            foreach ($this->compartment as $compartment) {
                $compartment->xmlSerialize(true, $sxe->addChild('compartment'));
            }
        }
        if (0 < count($this->link)) {
            foreach ($this->link as $link) {
                $link->xmlSerialize(true, $sxe->addChild('link'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
