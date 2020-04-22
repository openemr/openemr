<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRStructureMap;

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
 * A Map of relationships between 2 structures that can be used to transform data.
 */
class FHIRStructureMapStructure extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The canonical reference to the structure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $url = null;

    /**
     * How the referenced structure is used in this mapping.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapModelMode
     */
    public $mode = null;

    /**
     * The name used for this type in the map.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $alias = null;

    /**
     * Documentation that describes how the structure is used in the mapping.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $documentation = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'StructureMap.Structure';

    /**
     * The canonical reference to the structure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * The canonical reference to the structure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * How the referenced structure is used in this mapping.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapModelMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * How the referenced structure is used in this mapping.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapModelMode $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * The name used for this type in the map.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * The name used for this type in the map.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Documentation that describes how the structure is used in the mapping.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Documentation that describes how the structure is used in the mapping.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
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
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
            }
            if (isset($data['mode'])) {
                $this->setMode($data['mode']);
            }
            if (isset($data['alias'])) {
                $this->setAlias($data['alias']);
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
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
        if (isset($this->url)) {
            $json['url'] = $this->url;
        }
        if (isset($this->mode)) {
            $json['mode'] = $this->mode;
        }
        if (isset($this->alias)) {
            $json['alias'] = $this->alias;
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
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
            $sxe = new \SimpleXMLElement('<StructureMapStructure xmlns="http://hl7.org/fhir"></StructureMapStructure>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if (isset($this->mode)) {
            $this->mode->xmlSerialize(true, $sxe->addChild('mode'));
        }
        if (isset($this->alias)) {
            $this->alias->xmlSerialize(true, $sxe->addChild('alias'));
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
