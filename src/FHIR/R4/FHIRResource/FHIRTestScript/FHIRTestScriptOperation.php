<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRTestScript;

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
 * A structured set of tests against a FHIR server or client implementation to determine compliance against the FHIR specification.
 */
class FHIRTestScriptOperation extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Server interaction or operation type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $type = null;

    /**
     * The type of the resource.  See http://build.fhir.org/resourcelist.html.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $resource = null;

    /**
     * The label would be used for tracking/logging purposes by test engines.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $label = null;

    /**
     * The description would be used by test engines for tracking and reporting purposes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * The mime-type to use for RESTful operation in the 'Accept' header.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $accept = null;

    /**
     * The mime-type to use for RESTful operation in the 'Content-Type' header.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $contentType = null;

    /**
     * The server where the request message is destined for.  Must be one of the server numbers listed in TestScript.destination section.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $destination = null;

    /**
     * Whether or not to implicitly send the request url in encoded format. The default is true to match the standard RESTful client behavior. Set to false when communicating with a server that does not support encoded url paths.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $encodeRequestUrl = null;

    /**
     * The HTTP method the test engine MUST use for this operation regardless of any other operation details.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    public $method = null;

    /**
     * The server where the request message originates from.  Must be one of the server numbers listed in TestScript.origin section.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $origin = null;

    /**
     * Path plus parameters after [type].  Used to set parts of the request URL explicitly.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $params = null;

    /**
     * Header elements would be used to set HTTP headers.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTestScript\FHIRTestScriptRequestHeader[]
     */
    public $requestHeader = [];

    /**
     * The fixture id (maybe new) to map to the request.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $requestId = null;

    /**
     * The fixture id (maybe new) to map to the response.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $responseId = null;

    /**
     * The id of the fixture used as the body of a PUT or POST request.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $sourceId = null;

    /**
     * Id of fixture used for extracting the [id],  [type], and [vid] for GET requests.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $targetId = null;

    /**
     * Complete request URL.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $url = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestScript.Operation';

    /**
     * Server interaction or operation type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Server interaction or operation type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The type of the resource.  See http://build.fhir.org/resourcelist.html.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * The type of the resource.  See http://build.fhir.org/resourcelist.html.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * The label would be used for tracking/logging purposes by test engines.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * The label would be used for tracking/logging purposes by test engines.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * The description would be used by test engines for tracking and reporting purposes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * The description would be used by test engines for tracking and reporting purposes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The mime-type to use for RESTful operation in the 'Accept' header.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getAccept()
    {
        return $this->accept;
    }

    /**
     * The mime-type to use for RESTful operation in the 'Accept' header.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $accept
     * @return $this
     */
    public function setAccept($accept)
    {
        $this->accept = $accept;
        return $this;
    }

    /**
     * The mime-type to use for RESTful operation in the 'Content-Type' header.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * The mime-type to use for RESTful operation in the 'Content-Type' header.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * The server where the request message is destined for.  Must be one of the server numbers listed in TestScript.destination section.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * The server where the request message is destined for.  Must be one of the server numbers listed in TestScript.destination section.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $destination
     * @return $this
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * Whether or not to implicitly send the request url in encoded format. The default is true to match the standard RESTful client behavior. Set to false when communicating with a server that does not support encoded url paths.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getEncodeRequestUrl()
    {
        return $this->encodeRequestUrl;
    }

    /**
     * Whether or not to implicitly send the request url in encoded format. The default is true to match the standard RESTful client behavior. Set to false when communicating with a server that does not support encoded url paths.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $encodeRequestUrl
     * @return $this
     */
    public function setEncodeRequestUrl($encodeRequestUrl)
    {
        $this->encodeRequestUrl = $encodeRequestUrl;
        return $this;
    }

    /**
     * The HTTP method the test engine MUST use for this operation regardless of any other operation details.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTestScriptRequestMethodCode
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * The HTTP method the test engine MUST use for this operation regardless of any other operation details.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTestScriptRequestMethodCode $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * The server where the request message originates from.  Must be one of the server numbers listed in TestScript.origin section.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * The server where the request message originates from.  Must be one of the server numbers listed in TestScript.origin section.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $origin
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * Path plus parameters after [type].  Used to set parts of the request URL explicitly.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Path plus parameters after [type].  Used to set parts of the request URL explicitly.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Header elements would be used to set HTTP headers.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTestScript\FHIRTestScriptRequestHeader[]
     */
    public function getRequestHeader()
    {
        return $this->requestHeader;
    }

    /**
     * Header elements would be used to set HTTP headers.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTestScript\FHIRTestScriptRequestHeader $requestHeader
     * @return $this
     */
    public function addRequestHeader($requestHeader)
    {
        $this->requestHeader[] = $requestHeader;
        return $this;
    }

    /**
     * The fixture id (maybe new) to map to the request.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * The fixture id (maybe new) to map to the request.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $requestId
     * @return $this
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }

    /**
     * The fixture id (maybe new) to map to the response.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getResponseId()
    {
        return $this->responseId;
    }

    /**
     * The fixture id (maybe new) to map to the response.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $responseId
     * @return $this
     */
    public function setResponseId($responseId)
    {
        $this->responseId = $responseId;
        return $this;
    }

    /**
     * The id of the fixture used as the body of a PUT or POST request.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * The id of the fixture used as the body of a PUT or POST request.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $sourceId
     * @return $this
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;
        return $this;
    }

    /**
     * Id of fixture used for extracting the [id],  [type], and [vid] for GET requests.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * Id of fixture used for extracting the [id],  [type], and [vid] for GET requests.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $targetId
     * @return $this
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * Complete request URL.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Complete request URL.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['resource'])) {
                $this->setResource($data['resource']);
            }
            if (isset($data['label'])) {
                $this->setLabel($data['label']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['accept'])) {
                $this->setAccept($data['accept']);
            }
            if (isset($data['contentType'])) {
                $this->setContentType($data['contentType']);
            }
            if (isset($data['destination'])) {
                $this->setDestination($data['destination']);
            }
            if (isset($data['encodeRequestUrl'])) {
                $this->setEncodeRequestUrl($data['encodeRequestUrl']);
            }
            if (isset($data['method'])) {
                $this->setMethod($data['method']);
            }
            if (isset($data['origin'])) {
                $this->setOrigin($data['origin']);
            }
            if (isset($data['params'])) {
                $this->setParams($data['params']);
            }
            if (isset($data['requestHeader'])) {
                if (is_array($data['requestHeader'])) {
                    foreach ($data['requestHeader'] as $d) {
                        $this->addRequestHeader($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"requestHeader" must be array of objects or null, ' . gettype($data['requestHeader']) . ' seen.');
                }
            }
            if (isset($data['requestId'])) {
                $this->setRequestId($data['requestId']);
            }
            if (isset($data['responseId'])) {
                $this->setResponseId($data['responseId']);
            }
            if (isset($data['sourceId'])) {
                $this->setSourceId($data['sourceId']);
            }
            if (isset($data['targetId'])) {
                $this->setTargetId($data['targetId']);
            }
            if (isset($data['url'])) {
                $this->setUrl($data['url']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->resource)) {
            $json['resource'] = $this->resource;
        }
        if (isset($this->label)) {
            $json['label'] = $this->label;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->accept)) {
            $json['accept'] = $this->accept;
        }
        if (isset($this->contentType)) {
            $json['contentType'] = $this->contentType;
        }
        if (isset($this->destination)) {
            $json['destination'] = $this->destination;
        }
        if (isset($this->encodeRequestUrl)) {
            $json['encodeRequestUrl'] = $this->encodeRequestUrl;
        }
        if (isset($this->method)) {
            $json['method'] = $this->method;
        }
        if (isset($this->origin)) {
            $json['origin'] = $this->origin;
        }
        if (isset($this->params)) {
            $json['params'] = $this->params;
        }
        if (0 < count($this->requestHeader)) {
            $json['requestHeader'] = [];
            foreach ($this->requestHeader as $requestHeader) {
                $json['requestHeader'][] = $requestHeader;
            }
        }
        if (isset($this->requestId)) {
            $json['requestId'] = $this->requestId;
        }
        if (isset($this->responseId)) {
            $json['responseId'] = $this->responseId;
        }
        if (isset($this->sourceId)) {
            $json['sourceId'] = $this->sourceId;
        }
        if (isset($this->targetId)) {
            $json['targetId'] = $this->targetId;
        }
        if (isset($this->url)) {
            $json['url'] = $this->url;
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
            $sxe = new \SimpleXMLElement('<TestScriptOperation xmlns="http://hl7.org/fhir"></TestScriptOperation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->resource)) {
            $this->resource->xmlSerialize(true, $sxe->addChild('resource'));
        }
        if (isset($this->label)) {
            $this->label->xmlSerialize(true, $sxe->addChild('label'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->accept)) {
            $this->accept->xmlSerialize(true, $sxe->addChild('accept'));
        }
        if (isset($this->contentType)) {
            $this->contentType->xmlSerialize(true, $sxe->addChild('contentType'));
        }
        if (isset($this->destination)) {
            $this->destination->xmlSerialize(true, $sxe->addChild('destination'));
        }
        if (isset($this->encodeRequestUrl)) {
            $this->encodeRequestUrl->xmlSerialize(true, $sxe->addChild('encodeRequestUrl'));
        }
        if (isset($this->method)) {
            $this->method->xmlSerialize(true, $sxe->addChild('method'));
        }
        if (isset($this->origin)) {
            $this->origin->xmlSerialize(true, $sxe->addChild('origin'));
        }
        if (isset($this->params)) {
            $this->params->xmlSerialize(true, $sxe->addChild('params'));
        }
        if (0 < count($this->requestHeader)) {
            foreach ($this->requestHeader as $requestHeader) {
                $requestHeader->xmlSerialize(true, $sxe->addChild('requestHeader'));
            }
        }
        if (isset($this->requestId)) {
            $this->requestId->xmlSerialize(true, $sxe->addChild('requestId'));
        }
        if (isset($this->responseId)) {
            $this->responseId->xmlSerialize(true, $sxe->addChild('responseId'));
        }
        if (isset($this->sourceId)) {
            $this->sourceId->xmlSerialize(true, $sxe->addChild('sourceId'));
        }
        if (isset($this->targetId)) {
            $this->targetId->xmlSerialize(true, $sxe->addChild('targetId'));
        }
        if (isset($this->url)) {
            $this->url->xmlSerialize(true, $sxe->addChild('url'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
