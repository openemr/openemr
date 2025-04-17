<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement;

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
 * A Capability Statement documents a set of capabilities (behaviors) of a FHIR Server for a particular version of FHIR that may be used as a statement of actual server functionality or a statement of required or desired server implementation.
 */
class FHIRCapabilityStatementRest extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifies whether this portion of the statement is describing the ability to initiate or receive restful operations.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRestfulCapabilityMode
     */
    public $mode = null;

    /**
     * Information about the system's restful capabilities that apply across all applications, such as security.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $documentation = null;

    /**
     * Information about security implementation from an interface perspective - what a client needs to know.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity
     */
    public $security = null;

    /**
     * A specification of the restful capabilities of the solution for a specific resource type.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementResource[]
     */
    public $resource = [];

    /**
     * A specification of restful operations supported by the system.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction1[]
     */
    public $interaction = [];

    /**
     * Search parameters that are supported for searching all resources for implementations to support and/or make use of - either references to ones defined in the specification, or additional ones defined for/by the implementation.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam[]
     */
    public $searchParam = [];

    /**
     * Definition of an operation or a named query together with its parameters and their meaning and type.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementOperation[]
     */
    public $operation = [];

    /**
     * An absolute URI which is a reference to the definition of a compartment that the system supports. The reference is to a CompartmentDefinition resource by its canonical URL .
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $compartment = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CapabilityStatement.Rest';

    /**
     * Identifies whether this portion of the statement is describing the ability to initiate or receive restful operations.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRestfulCapabilityMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Identifies whether this portion of the statement is describing the ability to initiate or receive restful operations.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRestfulCapabilityMode $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Information about the system's restful capabilities that apply across all applications, such as security.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Information about the system's restful capabilities that apply across all applications, such as security.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $documentation
     * @return $this
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
        return $this;
    }

    /**
     * Information about security implementation from an interface perspective - what a client needs to know.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * Information about security implementation from an interface perspective - what a client needs to know.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity $security
     * @return $this
     */
    public function setSecurity($security)
    {
        $this->security = $security;
        return $this;
    }

    /**
     * A specification of the restful capabilities of the solution for a specific resource type.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementResource[]
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * A specification of the restful capabilities of the solution for a specific resource type.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementResource $resource
     * @return $this
     */
    public function addResource($resource)
    {
        $this->resource[] = $resource;
        return $this;
    }

    /**
     * A specification of restful operations supported by the system.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction1[]
     */
    public function getInteraction()
    {
        return $this->interaction;
    }

    /**
     * A specification of restful operations supported by the system.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction1 $interaction
     * @return $this
     */
    public function addInteraction($interaction)
    {
        $this->interaction[] = $interaction;
        return $this;
    }

    /**
     * Search parameters that are supported for searching all resources for implementations to support and/or make use of - either references to ones defined in the specification, or additional ones defined for/by the implementation.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam[]
     */
    public function getSearchParam()
    {
        return $this->searchParam;
    }

    /**
     * Search parameters that are supported for searching all resources for implementations to support and/or make use of - either references to ones defined in the specification, or additional ones defined for/by the implementation.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam $searchParam
     * @return $this
     */
    public function addSearchParam($searchParam)
    {
        $this->searchParam[] = $searchParam;
        return $this;
    }

    /**
     * Definition of an operation or a named query together with its parameters and their meaning and type.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementOperation[]
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Definition of an operation or a named query together with its parameters and their meaning and type.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementOperation $operation
     * @return $this
     */
    public function addOperation($operation)
    {
        $this->operation[] = $operation;
        return $this;
    }

    /**
     * An absolute URI which is a reference to the definition of a compartment that the system supports. The reference is to a CompartmentDefinition resource by its canonical URL .
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getCompartment()
    {
        return $this->compartment;
    }

    /**
     * An absolute URI which is a reference to the definition of a compartment that the system supports. The reference is to a CompartmentDefinition resource by its canonical URL .
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $compartment
     * @return $this
     */
    public function addCompartment($compartment)
    {
        $this->compartment[] = $compartment;
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
            if (isset($data['mode'])) {
                $this->setMode($data['mode']);
            }
            if (isset($data['documentation'])) {
                $this->setDocumentation($data['documentation']);
            }
            if (isset($data['security'])) {
                $this->setSecurity($data['security']);
            }
            if (isset($data['resource'])) {
                if (is_array($data['resource'])) {
                    foreach ($data['resource'] as $d) {
                        $this->addResource($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"resource" must be array of objects or null, ' . gettype($data['resource']) . ' seen.');
                }
            }
            if (isset($data['interaction'])) {
                if (is_array($data['interaction'])) {
                    foreach ($data['interaction'] as $d) {
                        $this->addInteraction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"interaction" must be array of objects or null, ' . gettype($data['interaction']) . ' seen.');
                }
            }
            if (isset($data['searchParam'])) {
                if (is_array($data['searchParam'])) {
                    foreach ($data['searchParam'] as $d) {
                        $this->addSearchParam($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"searchParam" must be array of objects or null, ' . gettype($data['searchParam']) . ' seen.');
                }
            }
            if (isset($data['operation'])) {
                if (is_array($data['operation'])) {
                    foreach ($data['operation'] as $d) {
                        $this->addOperation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"operation" must be array of objects or null, ' . gettype($data['operation']) . ' seen.');
                }
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
        if (isset($this->mode)) {
            $json['mode'] = $this->mode;
        }
        if (isset($this->documentation)) {
            $json['documentation'] = $this->documentation;
        }
        if (isset($this->security)) {
            $json['security'] = $this->security;
        }
        if (0 < count($this->resource)) {
            $json['resource'] = [];
            foreach ($this->resource as $resource) {
                $json['resource'][] = $resource;
            }
        }
        if (0 < count($this->interaction)) {
            $json['interaction'] = [];
            foreach ($this->interaction as $interaction) {
                $json['interaction'][] = $interaction;
            }
        }
        if (0 < count($this->searchParam)) {
            $json['searchParam'] = [];
            foreach ($this->searchParam as $searchParam) {
                $json['searchParam'][] = $searchParam;
            }
        }
        if (0 < count($this->operation)) {
            $json['operation'] = [];
            foreach ($this->operation as $operation) {
                $json['operation'][] = $operation;
            }
        }
        if (0 < count($this->compartment)) {
            $json['compartment'] = [];
            foreach ($this->compartment as $compartment) {
                $json['compartment'][] = $compartment;
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
            $sxe = new \SimpleXMLElement('<CapabilityStatementRest xmlns="http://hl7.org/fhir"></CapabilityStatementRest>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->mode)) {
            $this->mode->xmlSerialize(true, $sxe->addChild('mode'));
        }
        if (isset($this->documentation)) {
            $this->documentation->xmlSerialize(true, $sxe->addChild('documentation'));
        }
        if (isset($this->security)) {
            $this->security->xmlSerialize(true, $sxe->addChild('security'));
        }
        if (0 < count($this->resource)) {
            foreach ($this->resource as $resource) {
                $resource->xmlSerialize(true, $sxe->addChild('resource'));
            }
        }
        if (0 < count($this->interaction)) {
            foreach ($this->interaction as $interaction) {
                $interaction->xmlSerialize(true, $sxe->addChild('interaction'));
            }
        }
        if (0 < count($this->searchParam)) {
            foreach ($this->searchParam as $searchParam) {
                $searchParam->xmlSerialize(true, $sxe->addChild('searchParam'));
            }
        }
        if (0 < count($this->operation)) {
            foreach ($this->operation as $operation) {
                $operation->xmlSerialize(true, $sxe->addChild('operation'));
            }
        }
        if (0 < count($this->compartment)) {
            foreach ($this->compartment as $compartment) {
                $compartment->xmlSerialize(true, $sxe->addChild('compartment'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
