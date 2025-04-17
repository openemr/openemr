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
class FHIRBundleResponse extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The status code returned by processing this entry. The status SHALL start with a 3 digit HTTP code (e.g. 404) and may contain the standard HTTP description associated with the status code.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $status = null;

    /**
     * The location header created by processing this operation, populated if the operation returns a location.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $location = null;

    /**
     * The Etag for the resource, if the operation for the entry produced a versioned resource (see [Resource Metadata and Versioning](http.html#versioning) and [Managing Resource Contention](http.html#concurrency)).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $etag = null;

    /**
     * The date/time that the resource was modified on the server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public $lastModified = null;

    /**
     * An OperationOutcome containing hints and warnings produced as part of processing this entry in a batch or transaction.
     * @var \OpenEMR\FHIR\R4\FHIRResourceContainer
     */
    public $outcome = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Bundle.Response';

    /**
     * The status code returned by processing this entry. The status SHALL start with a 3 digit HTTP code (e.g. 404) and may contain the standard HTTP description associated with the status code.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status code returned by processing this entry. The status SHALL start with a 3 digit HTTP code (e.g. 404) and may contain the standard HTTP description associated with the status code.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The location header created by processing this operation, populated if the operation returns a location.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The location header created by processing this operation, populated if the operation returns a location.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * The Etag for the resource, if the operation for the entry produced a versioned resource (see [Resource Metadata and Versioning](http.html#versioning) and [Managing Resource Contention](http.html#concurrency)).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getEtag()
    {
        return $this->etag;
    }

    /**
     * The Etag for the resource, if the operation for the entry produced a versioned resource (see [Resource Metadata and Versioning](http.html#versioning) and [Managing Resource Contention](http.html#concurrency)).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $etag
     * @return $this
     */
    public function setEtag($etag)
    {
        $this->etag = $etag;
        return $this;
    }

    /**
     * The date/time that the resource was modified on the server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * The date/time that the resource was modified on the server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant $lastModified
     * @return $this
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * An OperationOutcome containing hints and warnings produced as part of processing this entry in a batch or transaction.
     * @return mixed
     */
    public function getOutcome()
    {
        return isset($this->outcome) ? $this->outcome->jsonSerialize() : null;
    }

    /**
     * An OperationOutcome containing hints and warnings produced as part of processing this entry in a batch or transaction.
     * @param \OpenEMR\FHIR\R4\FHIRResourceContainer $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
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
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['etag'])) {
                $this->setEtag($data['etag']);
            }
            if (isset($data['lastModified'])) {
                $this->setLastModified($data['lastModified']);
            }
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (isset($this->etag)) {
            $json['etag'] = $this->etag;
        }
        if (isset($this->lastModified)) {
            $json['lastModified'] = $this->lastModified;
        }
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
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
            $sxe = new \SimpleXMLElement('<BundleResponse xmlns="http://hl7.org/fhir"></BundleResponse>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (isset($this->etag)) {
            $this->etag->xmlSerialize(true, $sxe->addChild('etag'));
        }
        if (isset($this->lastModified)) {
            $this->lastModified->xmlSerialize(true, $sxe->addChild('lastModified'));
        }
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
