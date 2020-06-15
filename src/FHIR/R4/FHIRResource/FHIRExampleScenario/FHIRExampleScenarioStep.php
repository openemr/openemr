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
class FHIRExampleScenarioStep extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Nested process.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioProcess[]
     */
    public $process = [];

    /**
     * If there is a pause in the flow.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $pause = null;

    /**
     * Each interaction or action.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioOperation
     */
    public $operation = null;

    /**
     * Indicates an alternative step that can be taken instead of the operations on the base step in exceptional/atypical circumstances.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioAlternative[]
     */
    public $alternative = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ExampleScenario.Step';

    /**
     * Nested process.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioProcess[]
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Nested process.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioProcess $process
     * @return $this
     */
    public function addProcess($process)
    {
        $this->process[] = $process;
        return $this;
    }

    /**
     * If there is a pause in the flow.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getPause()
    {
        return $this->pause;
    }

    /**
     * If there is a pause in the flow.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $pause
     * @return $this
     */
    public function setPause($pause)
    {
        $this->pause = $pause;
        return $this;
    }

    /**
     * Each interaction or action.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioOperation
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Each interaction or action.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioOperation $operation
     * @return $this
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * Indicates an alternative step that can be taken instead of the operations on the base step in exceptional/atypical circumstances.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioAlternative[]
     */
    public function getAlternative()
    {
        return $this->alternative;
    }

    /**
     * Indicates an alternative step that can be taken instead of the operations on the base step in exceptional/atypical circumstances.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioAlternative $alternative
     * @return $this
     */
    public function addAlternative($alternative)
    {
        $this->alternative[] = $alternative;
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
            if (isset($data['process'])) {
                if (is_array($data['process'])) {
                    foreach ($data['process'] as $d) {
                        $this->addProcess($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"process" must be array of objects or null, ' . gettype($data['process']) . ' seen.');
                }
            }
            if (isset($data['pause'])) {
                $this->setPause($data['pause']);
            }
            if (isset($data['operation'])) {
                $this->setOperation($data['operation']);
            }
            if (isset($data['alternative'])) {
                if (is_array($data['alternative'])) {
                    foreach ($data['alternative'] as $d) {
                        $this->addAlternative($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"alternative" must be array of objects or null, ' . gettype($data['alternative']) . ' seen.');
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
        if (0 < count($this->process)) {
            $json['process'] = [];
            foreach ($this->process as $process) {
                $json['process'][] = $process;
            }
        }
        if (isset($this->pause)) {
            $json['pause'] = $this->pause;
        }
        if (isset($this->operation)) {
            $json['operation'] = $this->operation;
        }
        if (0 < count($this->alternative)) {
            $json['alternative'] = [];
            foreach ($this->alternative as $alternative) {
                $json['alternative'][] = $alternative;
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
            $sxe = new \SimpleXMLElement('<ExampleScenarioStep xmlns="http://hl7.org/fhir"></ExampleScenarioStep>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->process)) {
            foreach ($this->process as $process) {
                $process->xmlSerialize(true, $sxe->addChild('process'));
            }
        }
        if (isset($this->pause)) {
            $this->pause->xmlSerialize(true, $sxe->addChild('pause'));
        }
        if (isset($this->operation)) {
            $this->operation->xmlSerialize(true, $sxe->addChild('operation'));
        }
        if (0 < count($this->alternative)) {
            foreach ($this->alternative as $alternative) {
                $alternative->xmlSerialize(true, $sxe->addChild('alternative'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
