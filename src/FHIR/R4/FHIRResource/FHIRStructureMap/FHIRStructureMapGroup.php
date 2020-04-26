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
class FHIRStructureMapGroup extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A unique name for the group for the convenience of human readers.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $name = null;

    /**
     * Another group that this group adds rules to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $extends = null;

    /**
     * If this is the default rule set to apply for the source type or this combination of types.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapGroupTypeMode
     */
    public $typeMode = null;

    /**
     * Additional supporting documentation that explains the purpose of the group and the types of mappings within it.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $documentation = null;

    /**
     * A name assigned to an instance of data. The instance must be provided when the mapping is invoked.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRStructureMap\FHIRStructureMapInput[]
     */
    public $input = [];

    /**
     * Transform Rule from source to target.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRStructureMap\FHIRStructureMapRule[]
     */
    public $rule = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'StructureMap.Group';

    /**
     * A unique name for the group for the convenience of human readers.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A unique name for the group for the convenience of human readers.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Another group that this group adds rules to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * Another group that this group adds rules to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $extends
     * @return $this
     */
    public function setExtends($extends)
    {
        $this->extends = $extends;
        return $this;
    }

    /**
     * If this is the default rule set to apply for the source type or this combination of types.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapGroupTypeMode
     */
    public function getTypeMode()
    {
        return $this->typeMode;
    }

    /**
     * If this is the default rule set to apply for the source type or this combination of types.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRStructureMapGroupTypeMode $typeMode
     * @return $this
     */
    public function setTypeMode($typeMode)
    {
        $this->typeMode = $typeMode;
        return $this;
    }

    /**
     * Additional supporting documentation that explains the purpose of the group and the types of mappings within it.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Additional supporting documentation that explains the purpose of the group and the types of mappings within it.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * A name assigned to an instance of data. The instance must be provided when the mapping is invoked.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRStructureMap\FHIRStructureMapInput[]
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * A name assigned to an instance of data. The instance must be provided when the mapping is invoked.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRStructureMap\FHIRStructureMapInput $input
     * @return $this
     */
    public function addInput($input)
    {
        $this->input[] = $input;
        return $this;
    }

    /**
     * Transform Rule from source to target.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRStructureMap\FHIRStructureMapRule[]
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Transform Rule from source to target.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRStructureMap\FHIRStructureMapRule $rule
     * @return $this
     */
    public function addRule($rule)
    {
        $this->rule[] = $rule;
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
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['extends'])) {
                $this->setExtends($data['extends']);
            }
            if (isset($data['typeMode'])) {
                $this->setTypeMode($data['typeMode']);
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
            }
            if (isset($data['input'])) {
                if (is_array($data['input'])) {
                    foreach ($data['input'] as $d) {
                        $this->addInput($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"input" must be array of objects or null, ' . gettype($data['input']) . ' seen.');
                }
            }
            if (isset($data['rule'])) {
                if (is_array($data['rule'])) {
                    foreach ($data['rule'] as $d) {
                        $this->addRule($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"rule" must be array of objects or null, ' . gettype($data['rule']) . ' seen.');
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
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->extends)) {
            $json['extends'] = $this->extends;
        }
        if (isset($this->typeMode)) {
            $json['typeMode'] = $this->typeMode;
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
        }
        if (0 < count($this->input)) {
            $json['input'] = [];
            foreach ($this->input as $input) {
                $json['input'][] = $input;
            }
        }
        if (0 < count($this->rule)) {
            $json['rule'] = [];
            foreach ($this->rule as $rule) {
                $json['rule'][] = $rule;
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
            $sxe = new \SimpleXMLElement('<StructureMapGroup xmlns="http://hl7.org/fhir"></StructureMapGroup>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->extends)) {
            $this->extends->xmlSerialize(true, $sxe->addChild('extends'));
        }
        if (isset($this->typeMode)) {
            $this->typeMode->xmlSerialize(true, $sxe->addChild('typeMode'));
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if (0 < count($this->input)) {
            foreach ($this->input as $input) {
                $input->xmlSerialize(true, $sxe->addChild('input'));
            }
        }
        if (0 < count($this->rule)) {
            foreach ($this->rule as $rule) {
                $rule->xmlSerialize(true, $sxe->addChild('rule'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
