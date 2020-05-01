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
class FHIRTerminologyCapabilitiesExpansion extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Whether the server can return nested value sets.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $hierarchical = null;

    /**
     * Whether the server supports paging on expansion.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $paging = null;

    /**
     * Allow request for incomplete expansions?
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $incomplete = null;

    /**
     * Supported expansion parameter.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesParameter[]
     */
    public $parameter = [];

    /**
     * Documentation about text searching works.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $textFilter = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TerminologyCapabilities.Expansion';

    /**
     * Whether the server can return nested value sets.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getHierarchical()
    {
        return $this->hierarchical;
    }

    /**
     * Whether the server can return nested value sets.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $hierarchical
     * @return $this
     */
    public function setHierarchical($hierarchical)
    {
        $this->hierarchical = $hierarchical;
        return $this;
    }

    /**
     * Whether the server supports paging on expansion.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getPaging()
    {
        return $this->paging;
    }

    /**
     * Whether the server supports paging on expansion.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $paging
     * @return $this
     */
    public function setPaging($paging)
    {
        $this->paging = $paging;
        return $this;
    }

    /**
     * Allow request for incomplete expansions?
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getIncomplete()
    {
        return $this->incomplete;
    }

    /**
     * Allow request for incomplete expansions?
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $incomplete
     * @return $this
     */
    public function setIncomplete($incomplete)
    {
        $this->incomplete = $incomplete;
        return $this;
    }

    /**
     * Supported expansion parameter.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesParameter[]
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Supported expansion parameter.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTerminologyCapabilities\FHIRTerminologyCapabilitiesParameter $parameter
     * @return $this
     */
    public function addParameter($parameter)
    {
        $this->parameter[] = $parameter;
        return $this;
    }

    /**
     * Documentation about text searching works.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getTextFilter()
    {
        return $this->textFilter;
    }

    /**
     * Documentation about text searching works.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $textFilter
     * @return $this
     */
    public function setTextFilter($textFilter)
    {
        $this->textFilter = $textFilter;
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
            if (isset($data['hierarchical'])) {
                $this->setHierarchical($data['hierarchical']);
            }
            if (isset($data['paging'])) {
                $this->setPaging($data['paging']);
            }
            if (isset($data['incomplete'])) {
                $this->setIncomplete($data['incomplete']);
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
            if (isset($data['textFilter'])) {
                $this->setTextFilter($data['textFilter']);
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
        if (isset($this->hierarchical)) {
            $json['hierarchical'] = $this->hierarchical;
        }
        if (isset($this->paging)) {
            $json['paging'] = $this->paging;
        }
        if (isset($this->incomplete)) {
            $json['incomplete'] = $this->incomplete;
        }
        if (0 < count($this->parameter)) {
            $json['parameter'] = [];
            foreach ($this->parameter as $parameter) {
                $json['parameter'][] = $parameter;
            }
        }
        if (isset($this->textFilter)) {
            $json['textFilter'] = $this->textFilter;
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
            $sxe = new \SimpleXMLElement('<TerminologyCapabilitiesExpansion xmlns="http://hl7.org/fhir"></TerminologyCapabilitiesExpansion>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->hierarchical)) {
            $this->hierarchical->xmlSerialize(true, $sxe->addChild('hierarchical'));
        }
        if (isset($this->paging)) {
            $this->paging->xmlSerialize(true, $sxe->addChild('paging'));
        }
        if (isset($this->incomplete)) {
            $this->incomplete->xmlSerialize(true, $sxe->addChild('incomplete'));
        }
        if (0 < count($this->parameter)) {
            foreach ($this->parameter as $parameter) {
                $parameter->xmlSerialize(true, $sxe->addChild('parameter'));
            }
        }
        if (isset($this->textFilter)) {
            $this->textFilter->xmlSerialize(true, $sxe->addChild('textFilter'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
