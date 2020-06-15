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
class FHIRImplementationGuideManifest extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A pointer to official web page, PDF or other rendering of the implementation guide.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl
     */
    public $rendering = null;

    /**
     * A resource that is part of the implementation guide. Conformance resources (value set, structure definition, capability statements etc.) are obvious candidates for inclusion, but any kind of resource can be included as an example resource.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideResource1[]
     */
    public $resource = [];

    /**
     * Information about a page within the IG.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuidePage1[]
     */
    public $page = [];

    /**
     * Indicates a relative path to an image that exists within the IG.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $image = [];

    /**
     * Indicates the relative path of an additional non-page, non-image file that is part of the IG - e.g. zip, jar and similar files that could be the target of a hyperlink in a derived IG.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $other = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ImplementationGuide.Manifest';

    /**
     * A pointer to official web page, PDF or other rendering of the implementation guide.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl
     */
    public function getRendering()
    {
        return $this->rendering;
    }

    /**
     * A pointer to official web page, PDF or other rendering of the implementation guide.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUrl $rendering
     * @return $this
     */
    public function setRendering($rendering)
    {
        $this->rendering = $rendering;
        return $this;
    }

    /**
     * A resource that is part of the implementation guide. Conformance resources (value set, structure definition, capability statements etc.) are obvious candidates for inclusion, but any kind of resource can be included as an example resource.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideResource1[]
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * A resource that is part of the implementation guide. Conformance resources (value set, structure definition, capability statements etc.) are obvious candidates for inclusion, but any kind of resource can be included as an example resource.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuideResource1 $resource
     * @return $this
     */
    public function addResource($resource)
    {
        $this->resource[] = $resource;
        return $this;
    }

    /**
     * Information about a page within the IG.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuidePage1[]
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Information about a page within the IG.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImplementationGuide\FHIRImplementationGuidePage1 $page
     * @return $this
     */
    public function addPage($page)
    {
        $this->page[] = $page;
        return $this;
    }

    /**
     * Indicates a relative path to an image that exists within the IG.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Indicates a relative path to an image that exists within the IG.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $image
     * @return $this
     */
    public function addImage($image)
    {
        $this->image[] = $image;
        return $this;
    }

    /**
     * Indicates the relative path of an additional non-page, non-image file that is part of the IG - e.g. zip, jar and similar files that could be the target of a hyperlink in a derived IG.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * Indicates the relative path of an additional non-page, non-image file that is part of the IG - e.g. zip, jar and similar files that could be the target of a hyperlink in a derived IG.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $other
     * @return $this
     */
    public function addOther($other)
    {
        $this->other[] = $other;
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
            if (isset($data['rendering'])) {
                $this->setRendering($data['rendering']);
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
                if (is_array($data['page'])) {
                    foreach ($data['page'] as $d) {
                        $this->addPage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"page" must be array of objects or null, ' . gettype($data['page']) . ' seen.');
                }
            }
            if (isset($data['image'])) {
                if (is_array($data['image'])) {
                    foreach ($data['image'] as $d) {
                        $this->addImage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"image" must be array of objects or null, ' . gettype($data['image']) . ' seen.');
                }
            }
            if (isset($data['other'])) {
                if (is_array($data['other'])) {
                    foreach ($data['other'] as $d) {
                        $this->addOther($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"other" must be array of objects or null, ' . gettype($data['other']) . ' seen.');
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
        if (isset($this->rendering)) {
            $json['rendering'] = $this->rendering;
        }
        if (0 < count($this->resource)) {
            $json['resource'] = [];
            foreach ($this->resource as $resource) {
                $json['resource'][] = $resource;
            }
        }
        if (0 < count($this->page)) {
            $json['page'] = [];
            foreach ($this->page as $page) {
                $json['page'][] = $page;
            }
        }
        if (0 < count($this->image)) {
            $json['image'] = [];
            foreach ($this->image as $image) {
                $json['image'][] = $image;
            }
        }
        if (0 < count($this->other)) {
            $json['other'] = [];
            foreach ($this->other as $other) {
                $json['other'][] = $other;
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
            $sxe = new \SimpleXMLElement('<ImplementationGuideManifest xmlns="http://hl7.org/fhir"></ImplementationGuideManifest>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->rendering)) {
            $this->rendering->xmlSerialize(true, $sxe->addChild('rendering'));
        }
        if (0 < count($this->resource)) {
            foreach ($this->resource as $resource) {
                $resource->xmlSerialize(true, $sxe->addChild('resource'));
            }
        }
        if (0 < count($this->page)) {
            foreach ($this->page as $page) {
                $page->xmlSerialize(true, $sxe->addChild('page'));
            }
        }
        if (0 < count($this->image)) {
            foreach ($this->image as $image) {
                $image->xmlSerialize(true, $sxe->addChild('image'));
            }
        }
        if (0 < count($this->other)) {
            foreach ($this->other as $other) {
                $other->xmlSerialize(true, $sxe->addChild('other'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
