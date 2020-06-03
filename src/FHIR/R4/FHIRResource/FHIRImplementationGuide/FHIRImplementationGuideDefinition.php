<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide;

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
 * A set of rules of how a particular interoperability or standards problem is solved - typically through the use of FHIR resources. This resource is used to gather all the parts of an implementation guide into a logical whole and to publish a computable definition of all the parts.
 */
class FHIRImplementationGuideDefinition extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A logical group of resources. Logical groups can be used when building pages.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideGrouping[]
     */
    public $grouping = [];

    /**
     * A resource that is part of the implementation guide. Conformance resources (value set, structure definition, capability statements etc.) are obvious candidates for inclusion, but any kind of resource can be included as an example resource.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideResource[]
     */
    public $resource = [];

    /**
     * A page / section in the implementation guide. The root page is the implementation guide home page.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuidePage
     */
    public $page = null;

    /**
     * Defines how IG is built by tools.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideParameter[]
     */
    public $parameter = [];

    /**
     * A template for building resources.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideTemplate[]
     */
    public $template = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ImplementationGuide.Definition';

    /**
     * A logical group of resources. Logical groups can be used when building pages.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideGrouping[]
     */
    public function getGrouping()
    {
        return $this->grouping;
    }

    /**
     * A logical group of resources. Logical groups can be used when building pages.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideGrouping $grouping
     * @return $this
     */
    public function addGrouping($grouping)
    {
        $this->grouping[] = $grouping;
        return $this;
    }

    /**
     * A resource that is part of the implementation guide. Conformance resources (value set, structure definition, capability statements etc.) are obvious candidates for inclusion, but any kind of resource can be included as an example resource.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideResource[]
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * A resource that is part of the implementation guide. Conformance resources (value set, structure definition, capability statements etc.) are obvious candidates for inclusion, but any kind of resource can be included as an example resource.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideResource $resource
     * @return $this
     */
    public function addResource($resource)
    {
        $this->resource[] = $resource;
        return $this;
    }

    /**
     * A page / section in the implementation guide. The root page is the implementation guide home page.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuidePage
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * A page / section in the implementation guide. The root page is the implementation guide home page.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuidePage $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Defines how IG is built by tools.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideParameter[]
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Defines how IG is built by tools.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideParameter $parameter
     * @return $this
     */
    public function addParameter($parameter)
    {
        $this->parameter[] = $parameter;
        return $this;
    }

    /**
     * A template for building resources.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideTemplate[]
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * A template for building resources.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideTemplate $template
     * @return $this
     */
    public function addTemplate($template)
    {
        $this->template[] = $template;
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
            if (isset($data['grouping'])) {
                if (is_array($data['grouping'])) {
                    foreach ($data['grouping'] as $d) {
                        $this->addGrouping($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"grouping" must be array of objects or null, ' . gettype($data['grouping']) . ' seen.');
                }
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
            if (isset($data['page'])) {
                $this->setPage($data['page']);
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
            if (isset($data['template'])) {
                if (is_array($data['template'])) {
                    foreach ($data['template'] as $d) {
                        $this->addTemplate($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"template" must be array of objects or null, ' . gettype($data['template']) . ' seen.');
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
        if (0 < count($this->grouping)) {
            $json['grouping'] = [];
            foreach ($this->grouping as $grouping) {
                $json['grouping'][] = $grouping;
            }
        }
        if (0 < count($this->resource)) {
            $json['resource'] = [];
            foreach ($this->resource as $resource) {
                $json['resource'][] = $resource;
            }
        }
        if (isset($this->page)) {
            $json['page'] = $this->page;
        }
        if (0 < count($this->parameter)) {
            $json['parameter'] = [];
            foreach ($this->parameter as $parameter) {
                $json['parameter'][] = $parameter;
            }
        }
        if (0 < count($this->template)) {
            $json['template'] = [];
            foreach ($this->template as $template) {
                $json['template'][] = $template;
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
            $sxe = new \SimpleXMLElement('<ImplementationGuideDefinition xmlns="http://hl7.org/fhir"></ImplementationGuideDefinition>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->grouping)) {
            foreach ($this->grouping as $grouping) {
                $grouping->xmlSerialize(true, $sxe->addChild('grouping'));
            }
        }
        if (0 < count($this->resource)) {
            foreach ($this->resource as $resource) {
                $resource->xmlSerialize(true, $sxe->addChild('resource'));
            }
        }
        if (isset($this->page)) {
            $this->page->xmlSerialize(true, $sxe->addChild('page'));
        }
        if (0 < count($this->parameter)) {
            foreach ($this->parameter as $parameter) {
                $parameter->xmlSerialize(true, $sxe->addChild('parameter'));
            }
        }
        if (0 < count($this->template)) {
            foreach ($this->template as $template) {
                $template->xmlSerialize(true, $sxe->addChild('template'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
