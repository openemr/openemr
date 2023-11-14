<?php

namespace OpenEMR\FHIR\R4\FHIRElement;

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

use OpenEMR\FHIR\R4\FHIRElement;

/**
 * A signature along with supporting context. The signature may be a digital signature that is cryptographic in nature, or some other signature acceptable to the domain. This other signature may be as simple as a graphical image representing a hand-written signature, or a signature ceremony Different signature approaches have different utilities.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRSignature extends FHIRElement implements \JsonSerializable
{
    /**
     * An indication of the reason that the entity signed this document. This may be explicitly included as part of the signature information and can be used when determining accountability for various actions concerning the document.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public $type = [];

    /**
     * When the digital signature was signed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public $when = null;

    /**
     * A reference to an application-usable description of the identity that signed  (e.g. the signature used their private key).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $who = null;

    /**
     * A reference to an application-usable description of the identity that is represented by the signature.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $onBehalfOf = null;

    /**
     * A mime type that indicates the technical format of the target resources signed by the signature.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $targetFormat = null;

    /**
     * A mime type that indicates the technical format of the signature. Important mime types are application/signature+xml for X ML DigSig, application/jose for JWS, and image/* for a graphical image of a signature, etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $sigFormat = null;

    /**
     * The base64 encoding of the Signature content. When signature is not recorded electronically this element would be empty.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary
     */
    public $data = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Signature';

    /**
     * An indication of the reason that the entity signed this document. This may be explicitly included as part of the signature information and can be used when determining accountability for various actions concerning the document.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * An indication of the reason that the entity signed this document. This may be explicitly included as part of the signature information and can be used when determining accountability for various actions concerning the document.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * When the digital signature was signed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * When the digital signature was signed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant $when
     * @return $this
     */
    public function setWhen($when)
    {
        $this->when = $when;
        return $this;
    }

    /**
     * A reference to an application-usable description of the identity that signed  (e.g. the signature used their private key).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * A reference to an application-usable description of the identity that signed  (e.g. the signature used their private key).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $who
     * @return $this
     */
    public function setWho($who)
    {
        $this->who = $who;
        return $this;
    }

    /**
     * A reference to an application-usable description of the identity that is represented by the signature.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getOnBehalfOf()
    {
        return $this->onBehalfOf;
    }

    /**
     * A reference to an application-usable description of the identity that is represented by the signature.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $onBehalfOf
     * @return $this
     */
    public function setOnBehalfOf($onBehalfOf)
    {
        $this->onBehalfOf = $onBehalfOf;
        return $this;
    }

    /**
     * A mime type that indicates the technical format of the target resources signed by the signature.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getTargetFormat()
    {
        return $this->targetFormat;
    }

    /**
     * A mime type that indicates the technical format of the target resources signed by the signature.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $targetFormat
     * @return $this
     */
    public function setTargetFormat($targetFormat)
    {
        $this->targetFormat = $targetFormat;
        return $this;
    }

    /**
     * A mime type that indicates the technical format of the signature. Important mime types are application/signature+xml for X ML DigSig, application/jose for JWS, and image/* for a graphical image of a signature, etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getSigFormat()
    {
        return $this->sigFormat;
    }

    /**
     * A mime type that indicates the technical format of the signature. Important mime types are application/signature+xml for X ML DigSig, application/jose for JWS, and image/* for a graphical image of a signature, etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $sigFormat
     * @return $this
     */
    public function setSigFormat($sigFormat)
    {
        $this->sigFormat = $sigFormat;
        return $this;
    }

    /**
     * The base64 encoding of the Signature content. When signature is not recorded electronically this element would be empty.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * The base64 encoding of the Signature content. When signature is not recorded electronically this element would be empty.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
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
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, ' . gettype($data['type']) . ' seen.');
                }
            }
            if (isset($data['when'])) {
                $this->setWhen($data['when']);
            }
            if (isset($data['who'])) {
                $this->setWho($data['who']);
            }
            if (isset($data['onBehalfOf'])) {
                $this->setOnBehalfOf($data['onBehalfOf']);
            }
            if (isset($data['targetFormat'])) {
                $this->setTargetFormat($data['targetFormat']);
            }
            if (isset($data['sigFormat'])) {
                $this->setSigFormat($data['sigFormat']);
            }
            if (isset($data['data'])) {
                $this->setData($data['data']);
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
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
        }
        if (isset($this->when)) {
            $json['when'] = $this->when;
        }
        if (isset($this->who)) {
            $json['who'] = $this->who;
        }
        if (isset($this->onBehalfOf)) {
            $json['onBehalfOf'] = $this->onBehalfOf;
        }
        if (isset($this->targetFormat)) {
            $json['targetFormat'] = $this->targetFormat;
        }
        if (isset($this->sigFormat)) {
            $json['sigFormat'] = $this->sigFormat;
        }
        if (isset($this->data)) {
            $json['data'] = $this->data;
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
            $sxe = new \SimpleXMLElement('<Signature xmlns="http://hl7.org/fhir"></Signature>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (isset($this->when)) {
            $this->when->xmlSerialize(true, $sxe->addChild('when'));
        }
        if (isset($this->who)) {
            $this->who->xmlSerialize(true, $sxe->addChild('who'));
        }
        if (isset($this->onBehalfOf)) {
            $this->onBehalfOf->xmlSerialize(true, $sxe->addChild('onBehalfOf'));
        }
        if (isset($this->targetFormat)) {
            $this->targetFormat->xmlSerialize(true, $sxe->addChild('targetFormat'));
        }
        if (isset($this->sigFormat)) {
            $this->sigFormat->xmlSerialize(true, $sxe->addChild('sigFormat'));
        }
        if (isset($this->data)) {
            $this->data->xmlSerialize(true, $sxe->addChild('data'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
