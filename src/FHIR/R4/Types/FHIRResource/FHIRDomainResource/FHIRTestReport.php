<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\ValueXMLLocationEnum;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLWriter;
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestReportResultList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestReportStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportParticipant;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportSetup;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTeardown;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTest;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportResult;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * A summary of information based on the results of executing a TestScript.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTestReport extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TEST_REPORT;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_TEST_SCRIPT = 'testScript';
    public const FIELD_RESULT = 'result';
    public const FIELD_RESULT_EXT = '_result';
    public const FIELD_SCORE = 'score';
    public const FIELD_SCORE_EXT = '_score';
    public const FIELD_TESTER = 'tester';
    public const FIELD_TESTER_EXT = '_tester';
    public const FIELD_ISSUED = 'issued';
    public const FIELD_ISSUED_EXT = '_issued';
    public const FIELD_PARTICIPANT = 'participant';
    public const FIELD_SETUP = 'setup';
    public const FIELD_TEST = 'test';
    public const FIELD_TEARDOWN = 'teardown';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_TEST_SCRIPT => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_RESULT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RESULT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SCORE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TESTER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ISSUED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier for the TestScript assigned for external purposes outside the context
     * of FHIR.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $identifier;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A free text natural language name identifying the executed TestScript.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $name;
    /**
     * The current status of the test report.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of this test report.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportStatus
     */
    #[FHIRTestReportStatus]
    protected FHIRTestReportStatus $status;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Ideally this is an absolute URL that is used to identify the version-specific
     * TestScript that was executed, matching the `TestScript.url`.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $testScript;
    /**
     * The reported execution result.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The overall result from the execution of the TestScript.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportResult
     */
    #[FHIRTestReportResult]
    protected FHIRTestReportResult $result;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The final score (percentage of tests passed) resulting from the execution of the
     * TestScript.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $score;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Name of the tester producing this report (Organization or individual).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $tester;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the TestScript was executed and this TestReport was generated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $issued;
    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * A participant in the test execution, either the execution engine, a client, or a
     * server.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportParticipant>
     */
    #[FHIRTestReportParticipant]
    protected array $participant;
    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * The results of the series of required setup operations before the tests were
     * executed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportSetup
     */
    #[FHIRTestReportSetup]
    protected FHIRTestReportSetup $setup;
    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * A test executed from the test script.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTest>
     */
    #[FHIRTestReportTest]
    protected array $test;
    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * The results of the series of operations required to clean up after all the tests
     * were executed (successfully or otherwise).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTeardown
     */
    #[FHIRTestReportTeardown]
    protected FHIRTestReportTeardown $teardown;

    /* constructor.php:61 */
    /**
     * FHIRTestReport Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestReportStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $testScript
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestReportResultList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportResult $result
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $score
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $tester
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $issued
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportParticipant> $participant
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportSetup $setup
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTest> $test
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTeardown $teardown
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRNarrative $text = null,
                                null|iterable $contained = null,
                                null|iterable $extension = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRIdentifier $identifier = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|string|FHIRTestReportStatusList|FHIRTestReportStatus $status = null,
                                null|FHIRReference $testScript = null,
                                null|string|FHIRTestReportResultList|FHIRTestReportResult $result = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $score = null,
                                null|string|FHIRStringPrimitive|FHIRString $tester = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $issued = null,
                                null|iterable $participant = null,
                                null|FHIRTestReportSetup $setup = null,
                                null|iterable $test = null,
                                null|FHIRTestReportTeardown $teardown = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            text: $text,
                            contained: $contained,
                            extension: $extension,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $identifier) {
            $this->setIdentifier($identifier);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $testScript) {
            $this->setTestScript($testScript);
        }
        if (null !== $result) {
            $this->setResult($result);
        }
        if (null !== $score) {
            $this->setScore($score);
        }
        if (null !== $tester) {
            $this->setTester($tester);
        }
        if (null !== $issued) {
            $this->setIssued($issued);
        }
        if (null !== $participant) {
            $this->setParticipant(...$participant);
        }
        if (null !== $setup) {
            $this->setSetup($setup);
        }
        if (null !== $test) {
            $this->setTest(...$test);
        }
        if (null !== $teardown) {
            $this->setTeardown($teardown);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier for the TestScript assigned for external purposes outside the context
     * of FHIR.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier(): null|FHIRIdentifier
    {
        return $this->identifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifier for the TestScript assigned for external purposes outside the context
     * of FHIR.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function setIdentifier(null|FHIRIdentifier $identifier): self
    {
        if (null === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A free text natural language name identifying the executed TestScript.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getName(): null|FHIRString
    {
        return $this->name ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A free text natural language name identifying the executed TestScript.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @return static
     */
    public function setName(null|string|FHIRStringPrimitive|FHIRString $name): self
    {
        if (null === $name) {
            unset($this->name);
            return $this;
        }
        if (!($name instanceof FHIRString)) {
            $name = new FHIRString(value: $name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * The current status of the test report.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of this test report.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportStatus
     */
    public function getStatus(): null|FHIRTestReportStatus
    {
        return $this->status ?? null;
    }

    /**
     * The current status of the test report.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of this test report.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestReportStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRTestReportStatusList|FHIRTestReportStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRTestReportStatus)) {
            $status = new FHIRTestReportStatus(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Ideally this is an absolute URL that is used to identify the version-specific
     * TestScript that was executed, matching the `TestScript.url`.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getTestScript(): null|FHIRReference
    {
        return $this->testScript ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Ideally this is an absolute URL that is used to identify the version-specific
     * TestScript that was executed, matching the `TestScript.url`.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $testScript
     * @return static
     */
    public function setTestScript(null|FHIRReference $testScript): self
    {
        if (null === $testScript) {
            unset($this->testScript);
            return $this;
        }
        $this->testScript = $testScript;
        return $this;
    }

    /**
     * The reported execution result.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The overall result from the execution of the TestScript.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportResult
     */
    public function getResult(): null|FHIRTestReportResult
    {
        return $this->result ?? null;
    }

    /**
     * The reported execution result.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The overall result from the execution of the TestScript.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTestReportResultList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTestReportResult $result
     * @return static
     */
    public function setResult(null|string|FHIRTestReportResultList|FHIRTestReportResult $result): self
    {
        if (null === $result) {
            unset($this->result);
            return $this;
        }
        if (!($result instanceof FHIRTestReportResult)) {
            $result = new FHIRTestReportResult(value: $result);
        }
        $this->result = $result;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The final score (percentage of tests passed) resulting from the execution of the
     * TestScript.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getScore(): null|FHIRDecimal
    {
        return $this->score ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The final score (percentage of tests passed) resulting from the execution of the
     * TestScript.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $score
     * @return static
     */
    public function setScore(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $score): self
    {
        if (null === $score) {
            unset($this->score);
            return $this;
        }
        if (!($score instanceof FHIRDecimal)) {
            $score = new FHIRDecimal(value: $score);
        }
        $this->score = $score;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Name of the tester producing this report (Organization or individual).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getTester(): null|FHIRString
    {
        return $this->tester ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Name of the tester producing this report (Organization or individual).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $tester
     * @return static
     */
    public function setTester(null|string|FHIRStringPrimitive|FHIRString $tester): self
    {
        if (null === $tester) {
            unset($this->tester);
            return $this;
        }
        if (!($tester instanceof FHIRString)) {
            $tester = new FHIRString(value: $tester);
        }
        $this->tester = $tester;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the TestScript was executed and this TestReport was generated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getIssued(): null|FHIRDateTime
    {
        return $this->issued ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the TestScript was executed and this TestReport was generated.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $issued
     * @return static
     */
    public function setIssued(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $issued): self
    {
        if (null === $issued) {
            unset($this->issued);
            return $this;
        }
        if (!($issued instanceof FHIRDateTime)) {
            $issued = new FHIRDateTime(value: $issued);
        }
        $this->issued = $issued;
        return $this;
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * A participant in the test execution, either the execution engine, a client, or a
     * server.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportParticipant>
     */
    public function getParticipant(): array
    {
        return $this->participant ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportParticipant>
     */
    public function getParticipantIterator(): iterable
    {
        if (!isset($this->participant)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->participant);
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * A participant in the test execution, either the execution engine, a client, or a
     * server.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportParticipant $participant
     * @return static
     */
    public function addParticipant(FHIRTestReportParticipant $participant): self
    {
        if (!isset($this->participant)) {
            $this->participant = [];
        }
        $this->participant[] = $participant;
        return $this;
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * A participant in the test execution, either the execution engine, a client, or a
     * server.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportParticipant ...$participant
     * @return static
     */
    public function setParticipant(FHIRTestReportParticipant ...$participant): self
    {
        if ([] === $participant) {
            unset($this->participant);
            return $this;
        }
        $this->participant = $participant;
        return $this;
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * The results of the series of required setup operations before the tests were
     * executed.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportSetup
     */
    public function getSetup(): null|FHIRTestReportSetup
    {
        return $this->setup ?? null;
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * The results of the series of required setup operations before the tests were
     * executed.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportSetup $setup
     * @return static
     */
    public function setSetup(null|FHIRTestReportSetup $setup): self
    {
        if (null === $setup) {
            unset($this->setup);
            return $this;
        }
        $this->setup = $setup;
        return $this;
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * A test executed from the test script.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTest>
     */
    public function getTest(): array
    {
        return $this->test ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTest>
     */
    public function getTestIterator(): iterable
    {
        if (!isset($this->test)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->test);
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * A test executed from the test script.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTest $test
     * @return static
     */
    public function addTest(FHIRTestReportTest $test): self
    {
        if (!isset($this->test)) {
            $this->test = [];
        }
        $this->test[] = $test;
        return $this;
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * A test executed from the test script.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTest ...$test
     * @return static
     */
    public function setTest(FHIRTestReportTest ...$test): self
    {
        if ([] === $test) {
            unset($this->test);
            return $this;
        }
        $this->test = $test;
        return $this;
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * The results of the series of operations required to clean up after all the tests
     * were executed (successfully or otherwise).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTeardown
     */
    public function getTeardown(): null|FHIRTestReportTeardown
    {
        return $this->teardown ?? null;
    }

    /**
     * A summary of information based on the results of executing a TestScript.
     *
     * The results of the series of operations required to clean up after all the tests
     * were executed (successfully or otherwise).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTestReport\FHIRTestReportTeardown $teardown
     * @return static
     */
    public function setTeardown(null|FHIRTestReportTeardown $teardown): self
    {
        if (null === $teardown) {
            unset($this->teardown);
            return $this;
        }
        $this->teardown = $teardown;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTestReport $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTestReport
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTestReport)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRNarrative::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINED === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->addContained($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->setIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRTestReportStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEST_SCRIPT === $cen) {
                $type->setTestScript(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESULT === $cen) {
                $type->setResult(FHIRTestReportResult::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SCORE === $cen) {
                $type->setScore(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TESTER === $cen) {
                $type->setTester(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ISSUED === $cen) {
                $type->setIssued(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT === $cen) {
                $type->addParticipant(FHIRTestReportParticipant::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SETUP === $cen) {
                $type->setSetup(FHIRTestReportSetup::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEST === $cen) {
                $type->addTest(FHIRTestReportTest::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEARDOWN === $cen) {
                $type->setTeardown(FHIRTestReportTeardown::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RESULT])) {
            if (isset($type->result)) {
                $type->result->setValue((string)$attributes[self::FIELD_RESULT]);
            } else {
                $type->setResult((string)$attributes[self::FIELD_RESULT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RESULT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SCORE])) {
            if (isset($type->score)) {
                $type->score->setValue((string)$attributes[self::FIELD_SCORE]);
            } else {
                $type->setScore((string)$attributes[self::FIELD_SCORE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SCORE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TESTER])) {
            if (isset($type->tester)) {
                $type->tester->setValue((string)$attributes[self::FIELD_TESTER]);
            } else {
                $type->setTester((string)$attributes[self::FIELD_TESTER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TESTER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ISSUED])) {
            if (isset($type->issued)) {
                $type->issued->setValue((string)$attributes[self::FIELD_ISSUED]);
            } else {
                $type->setIssued((string)$attributes[self::FIELD_ISSUED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ISSUED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('TestReport', $this->_getSourceXMLNS());
        }
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->result) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RESULT]) {
            $xw->writeAttribute(self::FIELD_RESULT, $this->result->_getValueAsString());
        }
        if (isset($this->score) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SCORE]) {
            $xw->writeAttribute(self::FIELD_SCORE, $this->score->_getValueAsString());
        }
        if (isset($this->tester) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TESTER]) {
            $xw->writeAttribute(self::FIELD_TESTER, $this->tester->_getValueAsString());
        }
        if (isset($this->issued) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ISSUED]) {
            $xw->writeAttribute(self::FIELD_ISSUED, $this->issued->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            $xw->startElement(self::FIELD_IDENTIFIER);
            $this->identifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->testScript)) {
            $xw->startElement(self::FIELD_TEST_SCRIPT);
            $this->testScript->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->result)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RESULT]
                || $this->result->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RESULT);
            $this->result->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RESULT]);
            $xw->endElement();
        }
        if (isset($this->score)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SCORE]
                || $this->score->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SCORE);
            $this->score->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SCORE]);
            $xw->endElement();
        }
        if (isset($this->tester)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TESTER]
                || $this->tester->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TESTER);
            $this->tester->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TESTER]);
            $xw->endElement();
        }
        if (isset($this->issued)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ISSUED]
                || $this->issued->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ISSUED);
            $this->issued->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ISSUED]);
            $xw->endElement();
        }
        if (isset($this->participant)) {
            foreach ($this->participant as $v) {
                $xw->startElement(self::FIELD_PARTICIPANT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->setup)) {
            $xw->startElement(self::FIELD_SETUP);
            $this->setup->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->test)) {
            foreach ($this->test as $v) {
                $xw->startElement(self::FIELD_TEST);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->teardown)) {
            $xw->startElement(self::FIELD_TEARDOWN);
            $this->teardown->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTestReport $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTestReport
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            if (isset($decoded->resourceType) && $decoded->resourceType !== static::FHIR_TYPE_NAME) {
                throw new \DomainException(sprintf(
                    '%s::jsonUnserialize - Cannot unmarshal data for resource type "%s" into this type.',
                    ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                    $decoded->resourceType,
                ));
            }
            $type = new static();
        } else if (!($type instanceof FHIRTestReport)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_array($decoded->identifier)) {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->identifier), $config));
            } else {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize($decoded->identifier, $config));
            }
        }
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRTestReportStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->testScript) || property_exists($decoded, self::FIELD_TEST_SCRIPT)) {
            if (is_array($decoded->testScript)) {
                $type->setTestScript(FHIRReference::jsonUnserialize(reset($decoded->testScript), $config));
            } else {
                $type->setTestScript(FHIRReference::jsonUnserialize($decoded->testScript, $config));
            }
        }
        if (isset($decoded->result)
            || isset($decoded->_result)
            || property_exists($decoded, self::FIELD_RESULT)
            || property_exists($decoded, self::FIELD_RESULT_EXT)) {
            $v = $decoded->_result ?? new \stdClass();
            $v->value = $decoded->result ?? null;
            $type->setResult(FHIRTestReportResult::jsonUnserialize($v, $config));
        }
        if (isset($decoded->score)
            || isset($decoded->_score)
            || property_exists($decoded, self::FIELD_SCORE)
            || property_exists($decoded, self::FIELD_SCORE_EXT)) {
            $v = $decoded->_score ?? new \stdClass();
            $v->value = $decoded->score ?? null;
            $type->setScore(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->tester)
            || isset($decoded->_tester)
            || property_exists($decoded, self::FIELD_TESTER)
            || property_exists($decoded, self::FIELD_TESTER_EXT)) {
            $v = $decoded->_tester ?? new \stdClass();
            $v->value = $decoded->tester ?? null;
            $type->setTester(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->issued)
            || isset($decoded->_issued)
            || property_exists($decoded, self::FIELD_ISSUED)
            || property_exists($decoded, self::FIELD_ISSUED_EXT)) {
            $v = $decoded->_issued ?? new \stdClass();
            $v->value = $decoded->issued ?? null;
            $type->setIssued(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->participant) || property_exists($decoded, self::FIELD_PARTICIPANT)) {
            if (is_object($decoded->participant)) {
                $vals = [$decoded->participant];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PARTICIPANT, true);
            } else {
                $vals = $decoded->participant;
            }
            foreach($vals as $v) {
                $type->addParticipant(FHIRTestReportParticipant::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->setup) || property_exists($decoded, self::FIELD_SETUP)) {
            if (is_array($decoded->setup)) {
                $type->setSetup(FHIRTestReportSetup::jsonUnserialize(reset($decoded->setup), $config));
            } else {
                $type->setSetup(FHIRTestReportSetup::jsonUnserialize($decoded->setup, $config));
            }
        }
        if (isset($decoded->test) || property_exists($decoded, self::FIELD_TEST)) {
            if (is_object($decoded->test)) {
                $vals = [$decoded->test];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TEST, true);
            } else {
                $vals = $decoded->test;
            }
            foreach($vals as $v) {
                $type->addTest(FHIRTestReportTest::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->teardown) || property_exists($decoded, self::FIELD_TEARDOWN)) {
            if (is_array($decoded->teardown)) {
                $type->setTeardown(FHIRTestReportTeardown::jsonUnserialize(reset($decoded->teardown), $config));
            } else {
                $type->setTeardown(FHIRTestReportTeardown::jsonUnserialize($decoded->teardown, $config));
            }
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->identifier)) {
            $out->identifier = $this->identifier;
        }
        if (isset($this->name)) {
            if (null !== ($val = $this->name->getValue())) {
                $out->name = $val;
            }
            if ($this->name->_nonValueFieldDefined()) {
                $ext = $this->name->jsonSerialize();
                unset($ext->value);
                $out->_name = $ext;
            }
        }
        if (isset($this->status)) {
            if (null !== ($val = $this->status->getValue())) {
                $out->status = $val;
            }
            if ($this->status->_nonValueFieldDefined()) {
                $ext = $this->status->jsonSerialize();
                unset($ext->value);
                $out->_status = $ext;
            }
        }
        if (isset($this->testScript)) {
            $out->testScript = $this->testScript;
        }
        if (isset($this->result)) {
            if (null !== ($val = $this->result->getValue())) {
                $out->result = $val;
            }
            if ($this->result->_nonValueFieldDefined()) {
                $ext = $this->result->jsonSerialize();
                unset($ext->value);
                $out->_result = $ext;
            }
        }
        if (isset($this->score)) {
            if (null !== ($val = $this->score->getValue())) {
                $out->score = $val;
            }
            if ($this->score->_nonValueFieldDefined()) {
                $ext = $this->score->jsonSerialize();
                unset($ext->value);
                $out->_score = $ext;
            }
        }
        if (isset($this->tester)) {
            if (null !== ($val = $this->tester->getValue())) {
                $out->tester = $val;
            }
            if ($this->tester->_nonValueFieldDefined()) {
                $ext = $this->tester->jsonSerialize();
                unset($ext->value);
                $out->_tester = $ext;
            }
        }
        if (isset($this->issued)) {
            if (null !== ($val = $this->issued->getValue())) {
                $out->issued = $val;
            }
            if ($this->issued->_nonValueFieldDefined()) {
                $ext = $this->issued->jsonSerialize();
                unset($ext->value);
                $out->_issued = $ext;
            }
        }
        if (isset($this->participant) && [] !== $this->participant) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PARTICIPANT) && 1 === count($this->participant)) {
                $out->participant = $this->participant[0];
            } else {
                $out->participant = $this->participant;
            }
        }
        if (isset($this->setup)) {
            $out->setup = $this->setup;
        }
        if (isset($this->test) && [] !== $this->test) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TEST) && 1 === count($this->test)) {
                $out->test = $this->test[0];
            } else {
                $out->test = $this->test;
            }
        }
        if (isset($this->teardown)) {
            $out->teardown = $this->teardown;
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
