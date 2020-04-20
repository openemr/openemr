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
class FHIRExampleScenarioOperation extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The sequential number of the interaction, e.g. 1.2.5.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $number = null;

    /**
     * The type of operation - CRUD.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $type = null;

    /**
     * The human-friendly name of the interaction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * Who starts the transaction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $initiator = null;

    /**
     * Who receives the transaction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $receiver = null;

    /**
     * A comment to be inserted in the diagram.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public $description = null;

    /**
     * Whether the initiator is deactivated right after the transaction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $initiatorActive = null;

    /**
     * Whether the receiver is deactivated right after the transaction.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $receiverActive = null;

    /**
     * Each resource instance used by the initiator.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioContainedInstance
     */
    public $request = null;

    /**
     * Each resource instance used by the responder.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioContainedInstance
     */
    public $response = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ExampleScenario.Operation';

    /**
     * The sequential number of the interaction, e.g. 1.2.5.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * The sequential number of the interaction, e.g. 1.2.5.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $number
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * The type of operation - CRUD.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of operation - CRUD.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The human-friendly name of the interaction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The human-friendly name of the interaction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Who starts the transaction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getInitiator()
    {
        return $this->initiator;
    }

    /**
     * Who starts the transaction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $initiator
     * @return $this
     */
    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;
        return $this;
    }

    /**
     * Who receives the transaction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Who receives the transaction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $receiver
     * @return $this
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * A comment to be inserted in the diagram.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A comment to be inserted in the diagram.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Whether the initiator is deactivated right after the transaction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getInitiatorActive()
    {
        return $this->initiatorActive;
    }

    /**
     * Whether the initiator is deactivated right after the transaction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $initiatorActive
     * @return $this
     */
    public function setInitiatorActive($initiatorActive)
    {
        $this->initiatorActive = $initiatorActive;
        return $this;
    }

    /**
     * Whether the receiver is deactivated right after the transaction.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getReceiverActive()
    {
        return $this->receiverActive;
    }

    /**
     * Whether the receiver is deactivated right after the transaction.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $receiverActive
     * @return $this
     */
    public function setReceiverActive($receiverActive)
    {
        $this->receiverActive = $receiverActive;
        return $this;
    }

    /**
     * Each resource instance used by the initiator.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioContainedInstance
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Each resource instance used by the initiator.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioContainedInstance $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Each resource instance used by the responder.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioContainedInstance
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Each resource instance used by the responder.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRExampleScenario\FHIRExampleScenarioContainedInstance $response
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
            if (isset($data['number'])) {
                $this->setNumber($data['number']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['initiator'])) {
                $this->setInitiator($data['initiator']);
            }
            if (isset($data['receiver'])) {
                $this->setReceiver($data['receiver']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['initiatorActive'])) {
                $this->setInitiatorActive($data['initiatorActive']);
            }
            if (isset($data['receiverActive'])) {
                $this->setReceiverActive($data['receiverActive']);
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
        if (isset($this->number)) {
            $json['number'] = $this->number;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->initiator)) {
            $json['initiator'] = $this->initiator;
        }
        if (isset($this->receiver)) {
            $json['receiver'] = $this->receiver;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->initiatorActive)) {
            $json['initiatorActive'] = $this->initiatorActive;
        }
        if (isset($this->receiverActive)) {
            $json['receiverActive'] = $this->receiverActive;
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
            $sxe = new \SimpleXMLElement('<ExampleScenarioOperation xmlns="http://hl7.org/fhir"></ExampleScenarioOperation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->number)) {
            $this->number->xmlSerialize(true, $sxe->addChild('number'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->initiator)) {
            $this->initiator->xmlSerialize(true, $sxe->addChild('initiator'));
        }
        if (isset($this->receiver)) {
            $this->receiver->xmlSerialize(true, $sxe->addChild('receiver'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->initiatorActive)) {
            $this->initiatorActive->xmlSerialize(true, $sxe->addChild('initiatorActive'));
        }
        if (isset($this->receiverActive)) {
            $this->receiverActive->xmlSerialize(true, $sxe->addChild('receiverActive'));
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
