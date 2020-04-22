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
class FHIRExampleScenarioInstance extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The id of the resource for referencing.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $resourceId = null;

    /**
     * The type of the resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRResourceType
     */
    public $resourceType = null;

    /**
     * A short name for the resource instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * Human-friendly description of the resource instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $description = null;

    /**
     * A specific version of the resource.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioVersion[]
     */
    public $version = [];

    /**
     * Resources contained in the instance (e.g. the observations contained in a bundle).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioContainedInstance[]
     */
    public $containedInstance = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ExampleScenario.Instance';

    /**
     * The id of the resource for referencing.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * The id of the resource for referencing.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $resourceId
     * @return $this
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    /**
     * The type of the resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * The type of the resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRResourceType $resourceType
     * @return $this
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
        return $this;
    }

    /**
     * A short name for the resource instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A short name for the resource instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Human-friendly description of the resource instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Human-friendly description of the resource instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * A specific version of the resource.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioVersion[]
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * A specific version of the resource.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioVersion $version
     * @return $this
     */
    public function addVersion($version)
    {
        $this->version[] = $version;
        return $this;
    }

    /**
     * Resources contained in the instance (e.g. the observations contained in a bundle).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioContainedInstance[]
     */
    public function getContainedInstance()
    {
        return $this->containedInstance;
    }

    /**
     * Resources contained in the instance (e.g. the observations contained in a bundle).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioContainedInstance $containedInstance
     * @return $this
     */
    public function addContainedInstance($containedInstance)
    {
        $this->containedInstance[] = $containedInstance;
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
            if (isset($data['resourceId'])) {
                $this->setResourceId($data['resourceId']);
            }
            if (isset($data['resourceType'])) {
                $this->setResourceType($data['resourceType']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['version'])) {
                if (is_array($data['version'])) {
                    foreach ($data['version'] as $d) {
                        $this->addVersion($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"version" must be array of objects or null, ' . gettype($data['version']) . ' seen.');
                }
            }
            if (isset($data['containedInstance'])) {
                if (is_array($data['containedInstance'])) {
                    foreach ($data['containedInstance'] as $d) {
                        $this->addContainedInstance($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"containedInstance" must be array of objects or null, ' . gettype($data['containedInstance']) . ' seen.');
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
        if (isset($this->resourceId)) {
            $json['resourceId'] = $this->resourceId;
        }
        if (isset($this->resourceType)) {
            $json['resourceType'] = $this->resourceType;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->version)) {
            $json['version'] = [];
            foreach ($this->version as $version) {
                $json['version'][] = $version;
            }
        }
        if (0 < count($this->containedInstance)) {
            $json['containedInstance'] = [];
            foreach ($this->containedInstance as $containedInstance) {
                $json['containedInstance'][] = $containedInstance;
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
            $sxe = new \SimpleXMLElement('<ExampleScenarioInstance xmlns="http://hl7.org/fhir"></ExampleScenarioInstance>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->resourceId)) {
            $this->resourceId->xmlSerialize(true, $sxe->addChild('resourceId'));
        }
        if (isset($this->resourceType)) {
            $this->resourceType->xmlSerialize(true, $sxe->addChild('resourceType'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->version)) {
            foreach ($this->version as $version) {
                $version->xmlSerialize(true, $sxe->addChild('version'));
            }
        }
        if (0 < count($this->containedInstance)) {
            foreach ($this->containedInstance as $containedInstance) {
                $containedInstance->xmlSerialize(true, $sxe->addChild('containedInstance'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
