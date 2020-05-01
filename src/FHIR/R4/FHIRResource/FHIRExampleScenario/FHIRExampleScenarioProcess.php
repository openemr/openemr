<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario;

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
 * Example of workflow instance.
 */
class FHIRExampleScenarioProcess extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The diagram title of the group of operations.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * A longer description of the group of operations.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $description = null;

    /**
     * Description of initial status before the process starts.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $preConditions = null;

    /**
     * Description of final status after the process ends.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $postConditions = null;

    /**
     * Each step of the process.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioStep[]
     */
    public $step = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ExampleScenario.Process';

    /**
     * The diagram title of the group of operations.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * The diagram title of the group of operations.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * A longer description of the group of operations.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A longer description of the group of operations.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Description of initial status before the process starts.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getPreConditions()
    {
        return $this->preConditions;
    }

    /**
     * Description of initial status before the process starts.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $preConditions
     * @return $this
     */
    public function setPreConditions($preConditions)
    {
        $this->preConditions = $preConditions;
        return $this;
    }

    /**
     * Description of final status after the process ends.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getPostConditions()
    {
        return $this->postConditions;
    }

    /**
     * Description of final status after the process ends.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $postConditions
     * @return $this
     */
    public function setPostConditions($postConditions)
    {
        $this->postConditions = $postConditions;
        return $this;
    }

    /**
     * Each step of the process.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioStep[]
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Each step of the process.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioStep $step
     * @return $this
     */
    public function addStep($step)
    {
        $this->step[] = $step;
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
            if (isset($data['title'])) {
                $this->setTitle($data['title']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['preConditions'])) {
                $this->setPreConditions($data['preConditions']);
            }
            if (isset($data['postConditions'])) {
                $this->setPostConditions($data['postConditions']);
            }
            if (isset($data['step'])) {
                if (is_array($data['step'])) {
                    foreach ($data['step'] as $d) {
                        $this->addStep($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"step" must be array of objects or null, ' . gettype($data['step']) . ' seen.');
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
        if (isset($this->title)) {
            $json['title'] = $this->title;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->preConditions)) {
            $json['preConditions'] = $this->preConditions;
        }
        if (isset($this->postConditions)) {
            $json['postConditions'] = $this->postConditions;
        }
        if (0 < count($this->step)) {
            $json['step'] = [];
            foreach ($this->step as $step) {
                $json['step'][] = $step;
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
            $sxe = new \SimpleXMLElement('<ExampleScenarioProcess xmlns="http://hl7.org/fhir"></ExampleScenarioProcess>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->title)) {
            $this->title->xmlSerialize(true, $sxe->addChild('title'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->preConditions)) {
            $this->preConditions->xmlSerialize(true, $sxe->addChild('preConditions'));
        }
        if (isset($this->postConditions)) {
            $this->postConditions->xmlSerialize(true, $sxe->addChild('postConditions'));
        }
        if (0 < count($this->step)) {
            foreach ($this->step as $step) {
                $step->xmlSerialize(true, $sxe->addChild('step'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
