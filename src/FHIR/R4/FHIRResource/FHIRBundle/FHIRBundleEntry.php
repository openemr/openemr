<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;

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
 * A container for a collection of resources.
 */
class FHIRBundleEntry extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A series of links that provide context to this entry.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink[]
     */
    public $link = [];

    /**
     * The Absolute URL for the resource.  The fullUrl SHALL NOT disagree with the id in the resource - i.e. if the fullUrl is not a urn:uuid, the URL shall be version-independent URL consistent with the Resource.id. The fullUrl is a version independent reference to the resource. The fullUrl element SHALL have a value except that:
* fullUrl can be empty on a POST (although it does not need to when specifying a temporary id for reference in the bundle)
* Results from operations might involve resources that are not identified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $fullUrl = null;

    /**
     * The Resource for the entry. The purpose/meaning of the resource is determined by the Bundle.type.
     * @var \OpenEMR\FHIR\R4\FHIRResourceContainer
     */
    public $resource = null;

    /**
     * Information about the search process that lead to the creation of this entry.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleSearch
     */
    public $search = null;

    /**
     * Additional information about how this entry should be processed as part of a transaction or batch.  For history, it shows how the entry was processed to create the version contained in the entry.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleRequest
     */
    public $request = null;

    /**
     * Indicates the results of processing the corresponding 'request' entry in the batch or transaction being responded to or what the results of an operation where when returning history.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleResponse
     */
    public $response = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Bundle.Entry';

    /**
     * A series of links that provide context to this entry.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink[]
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * A series of links that provide context to this entry.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink $link
     * @return $this
     */
    public function addLink($link)
    {
        $this->link[] = $link;
        return $this;
    }

    /**
     * The Absolute URL for the resource.  The fullUrl SHALL NOT disagree with the id in the resource - i.e. if the fullUrl is not a urn:uuid, the URL shall be version-independent URL consistent with the Resource.id. The fullUrl is a version independent reference to the resource. The fullUrl element SHALL have a value except that:
* fullUrl can be empty on a POST (although it does not need to when specifying a temporary id for reference in the bundle)
* Results from operations might involve resources that are not identified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getFullUrl()
    {
        return $this->fullUrl;
    }

    /**
     * The Absolute URL for the resource.  The fullUrl SHALL NOT disagree with the id in the resource - i.e. if the fullUrl is not a urn:uuid, the URL shall be version-independent URL consistent with the Resource.id. The fullUrl is a version independent reference to the resource. The fullUrl element SHALL have a value except that:
* fullUrl can be empty on a POST (although it does not need to when specifying a temporary id for reference in the bundle)
* Results from operations might involve resources that are not identified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $fullUrl
     * @return $this
     */
    public function setFullUrl($fullUrl)
    {
        $this->fullUrl = $fullUrl;
        return $this;
    }

    /**
     * The Resource for the entry. The purpose/meaning of the resource is determined by the Bundle.type.
     * @return mixed
     */
    public function getResource()
    {
        return isset($this->resource) ? $this->resource->jsonSerialize() : null;
    }

    /**
     * The Resource for the entry. The purpose/meaning of the resource is determined by the Bundle.type.
     * @param \OpenEMR\FHIR\R4\FHIRResourceContainer $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Information about the search process that lead to the creation of this entry.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleSearch
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Information about the search process that lead to the creation of this entry.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleSearch $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Additional information about how this entry should be processed as part of a transaction or batch.  For history, it shows how the entry was processed to create the version contained in the entry.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Additional information about how this entry should be processed as part of a transaction or batch.  For history, it shows how the entry was processed to create the version contained in the entry.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleRequest $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Indicates the results of processing the corresponding 'request' entry in the batch or transaction being responded to or what the results of an operation where when returning history.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Indicates the results of processing the corresponding 'request' entry in the batch or transaction being responded to or what the results of an operation where when returning history.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleResponse $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
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
            if (isset($data['link'])) {
                if (is_array($data['link'])) {
                    foreach ($data['link'] as $d) {
                        $this->addLink($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"link" must be array of objects or null, ' . gettype($data['link']) . ' seen.');
                }
            }
            if (isset($data['fullUrl'])) {
                $this->setFullUrl($data['fullUrl']);
            }
            if (isset($data['resource'])) {
                $this->setResource($data['resource']);
            }
            if (isset($data['search'])) {
                $this->setSearch($data['search']);
            }
            if (isset($data['request'])) {
                $this->setRequest($data['request']);
            }
            if (isset($data['response'])) {
                $this->setResponse($data['response']);
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
        if (0 < count($this->link)) {
            $json['link'] = [];
            foreach ($this->link as $link) {
                $json['link'][] = $link;
            }
        }
        if (isset($this->fullUrl)) {
            $json['fullUrl'] = $this->fullUrl;
        }
        if (isset($this->resource)) {
            $json['resource'] = $this->resource;
        }
        if (isset($this->search)) {
            $json['search'] = $this->search;
        }
        if (isset($this->request)) {
            $json['request'] = $this->request;
        }
        if (isset($this->response)) {
            $json['response'] = $this->response;
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
            $sxe = new \SimpleXMLElement('<BundleEntry xmlns="http://hl7.org/fhir"></BundleEntry>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->link)) {
            foreach ($this->link as $link) {
                $link->xmlSerialize(true, $sxe->addChild('link'));
            }
        }
        if (isset($this->fullUrl)) {
            $this->fullUrl->xmlSerialize(true, $sxe->addChild('fullUrl'));
        }
        if (isset($this->resource)) {
            $this->resource->xmlSerialize(true, $sxe->addChild('resource'));
        }
        if (isset($this->search)) {
            $this->search->xmlSerialize(true, $sxe->addChild('search'));
        }
        if (isset($this->request)) {
            $this->request->xmlSerialize(true, $sxe->addChild('request'));
        }
        if (isset($this->response)) {
            $this->response->xmlSerialize(true, $sxe->addChild('response'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
