<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport;

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
 * A summary of information based on the results of executing a TestScript.
 */
class FHIRTestReportOperation extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The result of this operation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTestReportActionResult
     */
    public $result = null;

    /**
     * An explanatory message associated with the result.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $message = null;

    /**
     * A link to further details on the result.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $detail = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestReport.Operation';

    /**
     * The result of this operation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTestReportActionResult
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * The result of this operation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTestReportActionResult $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * An explanatory message associated with the result.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * An explanatory message associated with the result.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * A link to further details on the result.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * A link to further details on the result.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $detail
     * @return $this
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
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
            if (isset($data['result'])) {
                $this->setResult($data['result']);
            }
            if (isset($data['message'])) {
                $this->setMessage($data['message']);
            }
            if (isset($data['detail'])) {
                $this->setDetail($data['detail']);
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
        if (isset($this->result)) {
            $json['result'] = $this->result;
        }
        if (isset($this->message)) {
            $json['message'] = $this->message;
        }
        if (isset($this->detail)) {
            $json['detail'] = $this->detail;
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
            $sxe = new \SimpleXMLElement('<TestReportOperation xmlns="http://hl7.org/fhir"></TestReportOperation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->result)) {
            $this->result->xmlSerialize(true, $sxe->addChild('result'));
        }
        if (isset($this->message)) {
            $this->message->xmlSerialize(true, $sxe->addChild('message'));
        }
        if (isset($this->detail)) {
            $this->detail->xmlSerialize(true, $sxe->addChild('detail'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
