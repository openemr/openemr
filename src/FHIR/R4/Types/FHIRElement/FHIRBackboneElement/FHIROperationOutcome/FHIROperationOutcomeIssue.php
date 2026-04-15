<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationOutcome;

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
use OpenEMR\FHIR\Types\ElementTypeInterface;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRIssueSeverityList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRIssueTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueSeverity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A collection of error, warning, or information messages that result from a
 * system action.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIROperationOutcomeIssue extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_OPERATION_OUTCOME_DOT_ISSUE;

    /* class_default.php:56 */
    public const FIELD_SEVERITY = 'severity';
    public const FIELD_SEVERITY_EXT = '_severity';
    public const FIELD_CODE = 'code';
    public const FIELD_CODE_EXT = '_code';
    public const FIELD_DETAILS = 'details';
    public const FIELD_DIAGNOSTICS = 'diagnostics';
    public const FIELD_DIAGNOSTICS_EXT = '_diagnostics';
    public const FIELD_LOCATION = 'location';
    public const FIELD_LOCATION_EXT = '_location';
    public const FIELD_EXPRESSION = 'expression';
    public const FIELD_EXPRESSION_EXT = '_expression';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_SEVERITY => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_CODE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SEVERITY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CODE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DIAGNOSTICS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * How the issue affects the success of the action.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates whether the issue indicates a variation from successful processing.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueSeverity
     */
    #[FHIRIssueSeverity]
    protected FHIRIssueSeverity $severity;
    /**
     * A code that describes the type of issue.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the type of the issue. The system that creates an OperationOutcome
     * SHALL choose the most applicable code from the IssueType value set, and may
     * additional provide its own code for the error in the details element.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueType
     */
    #[FHIRIssueType]
    protected FHIRIssueType $code;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional details about the error. This may be a text description of the error
     * or a system code that identifies the error.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $details;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional diagnostic information about the issue.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $diagnostics;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This element is deprecated because it is XML specific. It is replaced by
     * issue.expression, which is format independent, and simpler to parse. For
     * resource issues, this will be a simple XPath limited to element names,
     * repetition indicators and the default child accessor that identifies one of the
     * elements in the resource that caused this issue to be raised. For HTTP errors,
     * will be "http." + the parameter name.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $location;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A [simple subset of FHIRPath](fhirpath.html#simple) limited to element names,
     * repetition indicators and the default child accessor that identifies one of the
     * elements in the resource that caused this issue to be raised.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $expression;

    /* constructor.php:61 */
    /**
     * FHIROperationOutcomeIssue Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRIssueSeverityList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueSeverity $severity
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRIssueTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueType $code
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $details
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $diagnostics
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $location
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $expression
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRIssueSeverityList|FHIRIssueSeverity $severity = null,
                                null|string|FHIRIssueTypeList|FHIRIssueType $code = null,
                                null|FHIRCodeableConcept $details = null,
                                null|string|FHIRStringPrimitive|FHIRString $diagnostics = null,
                                null|iterable $location = null,
                                null|iterable $expression = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $severity) {
            $this->setSeverity($severity);
        }
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $details) {
            $this->setDetails($details);
        }
        if (null !== $diagnostics) {
            $this->setDiagnostics($diagnostics);
        }
        if (null !== $location) {
            $this->setLocation(...$location);
        }
        if (null !== $expression) {
            $this->setExpression(...$expression);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * How the issue affects the success of the action.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates whether the issue indicates a variation from successful processing.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueSeverity
     */
    public function getSeverity(): null|FHIRIssueSeverity
    {
        return $this->severity ?? null;
    }

    /**
     * How the issue affects the success of the action.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates whether the issue indicates a variation from successful processing.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRIssueSeverityList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueSeverity $severity
     * @return static
     */
    public function setSeverity(null|string|FHIRIssueSeverityList|FHIRIssueSeverity $severity): self
    {
        if (null === $severity) {
            unset($this->severity);
            return $this;
        }
        if (!($severity instanceof FHIRIssueSeverity)) {
            $severity = new FHIRIssueSeverity(value: $severity);
        }
        $this->severity = $severity;
        return $this;
    }

    /**
     * A code that describes the type of issue.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the type of the issue. The system that creates an OperationOutcome
     * SHALL choose the most applicable code from the IssueType value set, and may
     * additional provide its own code for the error in the details element.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueType
     */
    public function getCode(): null|FHIRIssueType
    {
        return $this->code ?? null;
    }

    /**
     * A code that describes the type of issue.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the type of the issue. The system that creates an OperationOutcome
     * SHALL choose the most applicable code from the IssueType value set, and may
     * additional provide its own code for the error in the details element.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRIssueTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIssueType $code
     * @return static
     */
    public function setCode(null|string|FHIRIssueTypeList|FHIRIssueType $code): self
    {
        if (null === $code) {
            unset($this->code);
            return $this;
        }
        if (!($code instanceof FHIRIssueType)) {
            $code = new FHIRIssueType(value: $code);
        }
        $this->code = $code;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional details about the error. This may be a text description of the error
     * or a system code that identifies the error.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDetails(): null|FHIRCodeableConcept
    {
        return $this->details ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional details about the error. This may be a text description of the error
     * or a system code that identifies the error.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $details
     * @return static
     */
    public function setDetails(null|FHIRCodeableConcept $details): self
    {
        if (null === $details) {
            unset($this->details);
            return $this;
        }
        $this->details = $details;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional diagnostic information about the issue.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDiagnostics(): null|FHIRString
    {
        return $this->diagnostics ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional diagnostic information about the issue.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $diagnostics
     * @return static
     */
    public function setDiagnostics(null|string|FHIRStringPrimitive|FHIRString $diagnostics): self
    {
        if (null === $diagnostics) {
            unset($this->diagnostics);
            return $this;
        }
        if (!($diagnostics instanceof FHIRString)) {
            $diagnostics = new FHIRString(value: $diagnostics);
        }
        $this->diagnostics = $diagnostics;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This element is deprecated because it is XML specific. It is replaced by
     * issue.expression, which is format independent, and simpler to parse. For
     * resource issues, this will be a simple XPath limited to element names,
     * repetition indicators and the default child accessor that identifies one of the
     * elements in the resource that caused this issue to be raised. For HTTP errors,
     * will be "http." + the parameter name.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getLocation(): array
    {
        return $this->location ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getLocationIterator(): iterable
    {
        if (!isset($this->location)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->location);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This element is deprecated because it is XML specific. It is replaced by
     * issue.expression, which is format independent, and simpler to parse. For
     * resource issues, this will be a simple XPath limited to element names,
     * repetition indicators and the default child accessor that identifies one of the
     * elements in the resource that caused this issue to be raised. For HTTP errors,
     * will be "http." + the parameter name.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $location
     * @return static
     */
    public function addLocation(string|FHIRStringPrimitive|FHIRString $location): self
    {
        if (!($location instanceof FHIRString)) {
            $location = new FHIRString(value: $location);
        }
        if (!isset($this->location)) {
            $this->location = [];
        }
        $this->location[] = $location;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This element is deprecated because it is XML specific. It is replaced by
     * issue.expression, which is format independent, and simpler to parse. For
     * resource issues, this will be a simple XPath limited to element names,
     * repetition indicators and the default child accessor that identifies one of the
     * elements in the resource that caused this issue to be raised. For HTTP errors,
     * will be "http." + the parameter name.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$location
     * @return static
     */
    public function setLocation(string|FHIRStringPrimitive|FHIRString ...$location): self
    {
        if ([] === $location) {
            unset($this->location);
            return $this;
        }
        $this->location = [];
        foreach($location as $v) {
            if ($v instanceof FHIRString) {
                $this->location[] = $v;
            } else {
                $this->location[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A [simple subset of FHIRPath](fhirpath.html#simple) limited to element names,
     * repetition indicators and the default child accessor that identifies one of the
     * elements in the resource that caused this issue to be raised.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getExpression(): array
    {
        return $this->expression ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getExpressionIterator(): iterable
    {
        if (!isset($this->expression)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->expression);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A [simple subset of FHIRPath](fhirpath.html#simple) limited to element names,
     * repetition indicators and the default child accessor that identifies one of the
     * elements in the resource that caused this issue to be raised.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $expression
     * @return static
     */
    public function addExpression(string|FHIRStringPrimitive|FHIRString $expression): self
    {
        if (!($expression instanceof FHIRString)) {
            $expression = new FHIRString(value: $expression);
        }
        if (!isset($this->expression)) {
            $this->expression = [];
        }
        $this->expression[] = $expression;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A [simple subset of FHIRPath](fhirpath.html#simple) limited to element names,
     * repetition indicators and the default child accessor that identifies one of the
     * elements in the resource that caused this issue to be raised.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$expression
     * @return static
     */
    public function setExpression(string|FHIRStringPrimitive|FHIRString ...$expression): self
    {
        if ([] === $expression) {
            unset($this->expression);
            return $this;
        }
        $this->expression = [];
        foreach($expression as $v) {
            if ($v instanceof FHIRString) {
                $this->expression[] = $v;
            } else {
                $this->expression[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationOutcome\FHIROperationOutcomeIssue $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationOutcome\FHIROperationOutcomeIssue
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIROperationOutcomeIssue)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ID === $cen) {
                $va = $ce->attributes()[FHIRStringPrimitive::FIELD_VALUE] ?? null;
                if (null !== $va) {
                    $type->setId((string)$va);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_ATTRIBUTE);
                } else {
                    $type->setId((string)$ce);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_VALUE);
                }
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEVERITY === $cen) {
                $type->setSeverity(FHIRIssueSeverity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRIssueType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DETAILS === $cen) {
                $type->setDetails(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DIAGNOSTICS === $cen) {
                $type->setDiagnostics(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOCATION === $cen) {
                $type->addLocation(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXPRESSION === $cen) {
                $type->addExpression(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SEVERITY])) {
            if (isset($type->severity)) {
                $type->severity->setValue((string)$attributes[self::FIELD_SEVERITY]);
            } else {
                $type->setSeverity((string)$attributes[self::FIELD_SEVERITY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SEVERITY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CODE])) {
            if (isset($type->code)) {
                $type->code->setValue((string)$attributes[self::FIELD_CODE]);
            } else {
                $type->setCode((string)$attributes[self::FIELD_CODE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CODE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DIAGNOSTICS])) {
            if (isset($type->diagnostics)) {
                $type->diagnostics->setValue((string)$attributes[self::FIELD_DIAGNOSTICS]);
            } else {
                $type->setDiagnostics((string)$attributes[self::FIELD_DIAGNOSTICS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DIAGNOSTICS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param \OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param \OpenEMR\FHIR\Encoding\SerializeConfig $config
     */
    public function xmlSerialize(XMLWriter $xw,
                                 SerializeConfig $config): void
    {
        if (isset($this->severity) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SEVERITY]) {
            $xw->writeAttribute(self::FIELD_SEVERITY, $this->severity->_getValueAsString());
        }
        if (isset($this->code) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CODE]) {
            $xw->writeAttribute(self::FIELD_CODE, $this->code->_getValueAsString());
        }
        if (isset($this->diagnostics) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DIAGNOSTICS]) {
            $xw->writeAttribute(self::FIELD_DIAGNOSTICS, $this->diagnostics->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->severity)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SEVERITY]
                || $this->severity->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SEVERITY);
            $this->severity->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SEVERITY]);
            $xw->endElement();
        }
        if (isset($this->code)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CODE]
                || $this->code->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CODE]);
            $xw->endElement();
        }
        if (isset($this->details)) {
            $xw->startElement(self::FIELD_DETAILS);
            $this->details->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->diagnostics)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DIAGNOSTICS]
                || $this->diagnostics->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DIAGNOSTICS);
            $this->diagnostics->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DIAGNOSTICS]);
            $xw->endElement();
        }
        if (isset($this->location) && [] !== $this->location) {
            foreach($this->location as $v) {
                $xw->startElement(self::FIELD_LOCATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->expression) && [] !== $this->expression) {
            foreach($this->expression as $v) {
                $xw->startElement(self::FIELD_EXPRESSION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationOutcome\FHIROperationOutcomeIssue $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIROperationOutcome\FHIROperationOutcomeIssue
     * @throws \Exception
     */
    public static function jsonUnserialize(\stdClass $decoded,
                                           UnserializeConfig $config,
                                           null|ElementTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIROperationOutcomeIssue)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->severity)
            || isset($decoded->_severity)
            || property_exists($decoded, self::FIELD_SEVERITY)
            || property_exists($decoded, self::FIELD_SEVERITY_EXT)) {
            $v = $decoded->_severity ?? new \stdClass();
            $v->value = $decoded->severity ?? null;
            $type->setSeverity(FHIRIssueSeverity::jsonUnserialize($v, $config));
        }
        if (isset($decoded->code)
            || isset($decoded->_code)
            || property_exists($decoded, self::FIELD_CODE)
            || property_exists($decoded, self::FIELD_CODE_EXT)) {
            $v = $decoded->_code ?? new \stdClass();
            $v->value = $decoded->code ?? null;
            $type->setCode(FHIRIssueType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->details) || property_exists($decoded, self::FIELD_DETAILS)) {
            if (is_array($decoded->details)) {
                $type->setDetails(FHIRCodeableConcept::jsonUnserialize(reset($decoded->details), $config));
            } else {
                $type->setDetails(FHIRCodeableConcept::jsonUnserialize($decoded->details, $config));
            }
        }
        if (isset($decoded->diagnostics)
            || isset($decoded->_diagnostics)
            || property_exists($decoded, self::FIELD_DIAGNOSTICS)
            || property_exists($decoded, self::FIELD_DIAGNOSTICS_EXT)) {
            $v = $decoded->_diagnostics ?? new \stdClass();
            $v->value = $decoded->diagnostics ?? null;
            $type->setDiagnostics(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->location)
            || isset($decoded->_location)
            || property_exists($decoded, self::FIELD_LOCATION)
            || property_exists($decoded, self::FIELD_LOCATION_EXT)) {
            $vals = (array)($decoded->location ?? []);
            $exts = (array)($decoded->_location ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addLocation(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->expression)
            || isset($decoded->_expression)
            || property_exists($decoded, self::FIELD_EXPRESSION)
            || property_exists($decoded, self::FIELD_EXPRESSION_EXT)) {
            $vals = (array)($decoded->expression ?? []);
            $exts = (array)($decoded->_expression ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addExpression(FHIRString::jsonUnserialize($v, $config));
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
        if (isset($this->severity)) {
            if (null !== ($val = $this->severity->getValue())) {
                $out->severity = $val;
            }
            if ($this->severity->_nonValueFieldDefined()) {
                $ext = $this->severity->jsonSerialize();
                unset($ext->value);
                $out->_severity = $ext;
            }
        }
        if (isset($this->code)) {
            if (null !== ($val = $this->code->getValue())) {
                $out->code = $val;
            }
            if ($this->code->_nonValueFieldDefined()) {
                $ext = $this->code->jsonSerialize();
                unset($ext->value);
                $out->_code = $ext;
            }
        }
        if (isset($this->details)) {
            $out->details = $this->details;
        }
        if (isset($this->diagnostics)) {
            if (null !== ($val = $this->diagnostics->getValue())) {
                $out->diagnostics = $val;
            }
            if ($this->diagnostics->_nonValueFieldDefined()) {
                $ext = $this->diagnostics->jsonSerialize();
                unset($ext->value);
                $out->_diagnostics = $ext;
            }
        }
        if (isset($this->location) && [] !== $this->location) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->location as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->location = $vals;
            }
            if ($hasExts) {
                $out->_location = $exts;
            }
        }
        if (isset($this->expression) && [] !== $this->expression) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->expression as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->expression = $vals;
            }
            if ($hasExts) {
                $out->_expression = $exts;
            }
        }
        return $out;
    }
}
