<?php

namespace OpenEMR\FHIR\R4\FHIRDomainResource;

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

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/**
 * A summary of information based on the results of executing a TestScript.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRTestReport extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifier for the TestScript assigned for external purposes outside the context of FHIR.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * A free text natural language name identifying the executed TestScript.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * The current state of this test report.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTestReportStatus
     */
    public $status = null;

    /**
     * Ideally this is an absolute URL that is used to identify the version-specific TestScript that was executed, matching the `TestScript.url`.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $testScript = null;

    /**
     * The overall result from the execution of the TestScript.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTestReportResult
     */
    public $result = null;

    /**
     * The final score (percentage of tests passed) resulting from the execution of the TestScript.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public $score = null;

    /**
     * Name of the tester producing this report (Organization or individual).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $tester = null;

    /**
     * When the TestScript was executed and this TestReport was generated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $issued = null;

    /**
     * A participant in the test execution, either the execution engine, a client, or a server.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportParticipant[]
     */
    public $participant = [];

    /**
     * The results of the series of required setup operations before the tests were executed.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportSetup
     */
    public $setup = null;

    /**
     * A test executed from the test script.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportTest[]
     */
    public $test = [];

    /**
     * The results of the series of operations required to clean up after all the tests were executed (successfully or otherwise).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportTeardown
     */
    public $teardown = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TestReport';

    /**
     * Identifier for the TestScript assigned for external purposes outside the context of FHIR.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier for the TestScript assigned for external purposes outside the context of FHIR.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * A free text natural language name identifying the executed TestScript.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A free text natural language name identifying the executed TestScript.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The current state of this test report.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTestReportStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The current state of this test report.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTestReportStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Ideally this is an absolute URL that is used to identify the version-specific TestScript that was executed, matching the `TestScript.url`.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getTestScript()
    {
        return $this->testScript;
    }

    /**
     * Ideally this is an absolute URL that is used to identify the version-specific TestScript that was executed, matching the `TestScript.url`.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $testScript
     * @return $this
     */
    public function setTestScript($testScript)
    {
        $this->testScript = $testScript;
        return $this;
    }

    /**
     * The overall result from the execution of the TestScript.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTestReportResult
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * The overall result from the execution of the TestScript.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTestReportResult $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * The final score (percentage of tests passed) resulting from the execution of the TestScript.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * The final score (percentage of tests passed) resulting from the execution of the TestScript.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDecimal $score
     * @return $this
     */
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }

    /**
     * Name of the tester producing this report (Organization or individual).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTester()
    {
        return $this->tester;
    }

    /**
     * Name of the tester producing this report (Organization or individual).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $tester
     * @return $this
     */
    public function setTester($tester)
    {
        $this->tester = $tester;
        return $this;
    }

    /**
     * When the TestScript was executed and this TestReport was generated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * When the TestScript was executed and this TestReport was generated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $issued
     * @return $this
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;
        return $this;
    }

    /**
     * A participant in the test execution, either the execution engine, a client, or a server.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportParticipant[]
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * A participant in the test execution, either the execution engine, a client, or a server.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportParticipant $participant
     * @return $this
     */
    public function addParticipant($participant)
    {
        $this->participant[] = $participant;
        return $this;
    }

    /**
     * The results of the series of required setup operations before the tests were executed.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportSetup
     */
    public function getSetup()
    {
        return $this->setup;
    }

    /**
     * The results of the series of required setup operations before the tests were executed.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportSetup $setup
     * @return $this
     */
    public function setSetup($setup)
    {
        $this->setup = $setup;
        return $this;
    }

    /**
     * A test executed from the test script.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportTest[]
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * A test executed from the test script.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportTest $test
     * @return $this
     */
    public function addTest($test)
    {
        $this->test[] = $test;
        return $this;
    }

    /**
     * The results of the series of operations required to clean up after all the tests were executed (successfully or otherwise).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportTeardown
     */
    public function getTeardown()
    {
        return $this->teardown;
    }

    /**
     * The results of the series of operations required to clean up after all the tests were executed (successfully or otherwise).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTestReport\FHIRTestReportTeardown $teardown
     * @return $this
     */
    public function setTeardown($teardown)
    {
        $this->teardown = $teardown;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['testScript'])) {
                $this->setTestScript($data['testScript']);
            }
            if (isset($data['result'])) {
                $this->setResult($data['result']);
            }
            if (isset($data['score'])) {
                $this->setScore($data['score']);
            }
            if (isset($data['tester'])) {
                $this->setTester($data['tester']);
            }
            if (isset($data['issued'])) {
                $this->setIssued($data['issued']);
            }
            if (isset($data['participant'])) {
                if (is_array($data['participant'])) {
                    foreach ($data['participant'] as $d) {
                        $this->addParticipant($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"participant" must be array of objects or null, ' . gettype($data['participant']) . ' seen.');
                }
            }
            if (isset($data['setup'])) {
                $this->setSetup($data['setup']);
            }
            if (isset($data['test'])) {
                if (is_array($data['test'])) {
                    foreach ($data['test'] as $d) {
                        $this->addTest($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"test" must be array of objects or null, ' . gettype($data['test']) . ' seen.');
                }
            }
            if (isset($data['teardown'])) {
                $this->setTeardown($data['teardown']);
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
        $json['resourceType'] = $this->_fhirElementName;
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->testScript)) {
            $json['testScript'] = $this->testScript;
        }
        if (isset($this->result)) {
            $json['result'] = $this->result;
        }
        if (isset($this->score)) {
            $json['score'] = $this->score;
        }
        if (isset($this->tester)) {
            $json['tester'] = $this->tester;
        }
        if (isset($this->issued)) {
            $json['issued'] = $this->issued;
        }
        if (0 < count($this->participant)) {
            $json['participant'] = [];
            foreach ($this->participant as $participant) {
                $json['participant'][] = $participant;
            }
        }
        if (isset($this->setup)) {
            $json['setup'] = $this->setup;
        }
        if (0 < count($this->test)) {
            $json['test'] = [];
            foreach ($this->test as $test) {
                $json['test'][] = $test;
            }
        }
        if (isset($this->teardown)) {
            $json['teardown'] = $this->teardown;
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
            $sxe = new \SimpleXMLElement('<TestReport xmlns="http://hl7.org/fhir"></TestReport>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->testScript)) {
            $this->testScript->xmlSerialize(true, $sxe->addChild('testScript'));
        }
        if (isset($this->result)) {
            $this->result->xmlSerialize(true, $sxe->addChild('result'));
        }
        if (isset($this->score)) {
            $this->score->xmlSerialize(true, $sxe->addChild('score'));
        }
        if (isset($this->tester)) {
            $this->tester->xmlSerialize(true, $sxe->addChild('tester'));
        }
        if (isset($this->issued)) {
            $this->issued->xmlSerialize(true, $sxe->addChild('issued'));
        }
        if (0 < count($this->participant)) {
            foreach ($this->participant as $participant) {
                $participant->xmlSerialize(true, $sxe->addChild('participant'));
            }
        }
        if (isset($this->setup)) {
            $this->setup->xmlSerialize(true, $sxe->addChild('setup'));
        }
        if (0 < count($this->test)) {
            foreach ($this->test as $test) {
                $test->xmlSerialize(true, $sxe->addChild('test'));
            }
        }
        if (isset($this->teardown)) {
            $this->teardown->xmlSerialize(true, $sxe->addChild('teardown'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
