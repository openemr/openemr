<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREnableWhenBehaviorList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRQuestionnaireItemTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREnableWhenBehavior;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuestionnaireItemType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A structured set of questions intended to guide the collection of answers from
 * end-users. Questionnaires provide detailed control over order, presentation,
 * phraseology and grouping to allow coherent, consistent data collection.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRQuestionnaireItem extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_QUESTIONNAIRE_DOT_ITEM;

    /* class_default.php:56 */
    public const FIELD_LINK_ID = 'linkId';
    public const FIELD_LINK_ID_EXT = '_linkId';
    public const FIELD_DEFINITION = 'definition';
    public const FIELD_DEFINITION_EXT = '_definition';
    public const FIELD_CODE = 'code';
    public const FIELD_PREFIX = 'prefix';
    public const FIELD_PREFIX_EXT = '_prefix';
    public const FIELD_TEXT = 'text';
    public const FIELD_TEXT_EXT = '_text';
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_EXT = '_type';
    public const FIELD_ENABLE_WHEN = 'enableWhen';
    public const FIELD_ENABLE_BEHAVIOR = 'enableBehavior';
    public const FIELD_ENABLE_BEHAVIOR_EXT = '_enableBehavior';
    public const FIELD_REQUIRED = 'required';
    public const FIELD_REQUIRED_EXT = '_required';
    public const FIELD_REPEATS = 'repeats';
    public const FIELD_REPEATS_EXT = '_repeats';
    public const FIELD_READ_ONLY = 'readOnly';
    public const FIELD_READ_ONLY_EXT = '_readOnly';
    public const FIELD_MAX_LENGTH = 'maxLength';
    public const FIELD_MAX_LENGTH_EXT = '_maxLength';
    public const FIELD_ANSWER_VALUE_SET = 'answerValueSet';
    public const FIELD_ANSWER_VALUE_SET_EXT = '_answerValueSet';
    public const FIELD_ANSWER_OPTION = 'answerOption';
    public const FIELD_INITIAL = 'initial';
    public const FIELD_ITEM = 'item';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_LINK_ID => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_TYPE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_LINK_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DEFINITION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PREFIX => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TEXT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ENABLE_BEHAVIOR => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_REQUIRED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_REPEATS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_READ_ONLY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MAX_LENGTH => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ANSWER_VALUE_SET => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An identifier that is unique within the Questionnaire allowing linkage to the
     * equivalent item in a QuestionnaireResponse resource.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $linkId;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This element is a URI that refers to an [[[ElementDefinition]]] that provides
     * information about this item, including information that might otherwise be
     * included in the instance of the Questionnaire resource. A detailed description
     * of the construction of the URI is shown in Comments, below. If this element is
     * present then the following element values MAY be derived from the Element
     * Definition if the corresponding elements of this Questionnaire resource instance
     * have no value: * code (ElementDefinition.code) * type (ElementDefinition.type) *
     * required (ElementDefinition.min) * repeats (ElementDefinition.max) * maxLength
     * (ElementDefinition.maxLength) * answerValueSet (ElementDefinition.binding) *
     * options (ElementDefinition.binding).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $definition;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A terminology code that corresponds to this group or question (e.g. a code from
     * LOINC, which defines many questions and answers).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    #[FHIRCoding]
    protected array $code;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short label for a particular group, question or set of display text within the
     * questionnaire used for reference by the individual completing the questionnaire.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $prefix;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of a section, the text of a question or text content for a display
     * item.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $text;
    /**
     * Distinguishes groups from questions and display text and indicates data type for
     * questions.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of questionnaire item this is - whether text for display, a grouping of
     * other items or a particular type of data to be captured (string, integer, coded
     * choice, etc.).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuestionnaireItemType
     */
    #[FHIRQuestionnaireItemType]
    protected FHIRQuestionnaireItemType $type;
    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * A constraint indicating that this item should only be enabled (displayed/allow
     * answers to be captured) when the specified condition is true.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen>
     */
    #[FHIRQuestionnaireEnableWhen]
    protected array $enableWhen;
    /**
     * Controls how multiple enableWhen values are interpreted - whether all or any
     * must be true.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Controls how multiple enableWhen values are interpreted - whether all or any
     * must be true.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREnableWhenBehavior
     */
    #[FHIREnableWhenBehavior]
    protected FHIREnableWhenBehavior $enableBehavior;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication, if true, that the item must be present in a "completed"
     * QuestionnaireResponse. If false, the item may be skipped when answering the
     * questionnaire.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $required;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication, if true, that the item may occur multiple times in the response,
     * collecting multiple answers for questions or multiple sets of answers for
     * groups.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $repeats;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication, when true, that the value cannot be changed by a human respondent
     * to the Questionnaire.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $readOnly;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The maximum number of characters that are permitted in the answer to be
     * considered a "valid" QuestionnaireResponse.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $maxLength;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A reference to a value set containing a list of codes representing permitted
     * answers for a "choice" or "open-choice" question.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $answerValueSet;
    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * One of the permitted answers for a "choice" or "open-choice" question.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireAnswerOption>
     */
    #[FHIRQuestionnaireAnswerOption]
    protected array $answerOption;
    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * One or more values that should be pre-populated in the answer when initially
     * rendering the questionnaire for user input.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireInitial>
     */
    #[FHIRQuestionnaireInitial]
    protected array $initial;
    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * Text, questions and other groups to be nested beneath a question or group.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem>
     */
    #[FHIRQuestionnaireItem]
    protected array $item;

    /* constructor.php:61 */
    /**
     * FHIRQuestionnaireItem Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $linkId
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $definition
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding> $code
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $prefix
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $text
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRQuestionnaireItemTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuestionnaireItemType $type
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen> $enableWhen
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREnableWhenBehaviorList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREnableWhenBehavior $enableBehavior
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $required
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $repeats
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $readOnly
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $maxLength
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $answerValueSet
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireAnswerOption> $answerOption
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireInitial> $initial
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem> $item
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $linkId = null,
                                null|string|FHIRUriPrimitive|FHIRUri $definition = null,
                                null|iterable $code = null,
                                null|string|FHIRStringPrimitive|FHIRString $prefix = null,
                                null|string|FHIRStringPrimitive|FHIRString $text = null,
                                null|string|FHIRQuestionnaireItemTypeList|FHIRQuestionnaireItemType $type = null,
                                null|iterable $enableWhen = null,
                                null|string|FHIREnableWhenBehaviorList|FHIREnableWhenBehavior $enableBehavior = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $required = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $repeats = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $readOnly = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $maxLength = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $answerValueSet = null,
                                null|iterable $answerOption = null,
                                null|iterable $initial = null,
                                null|iterable $item = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $linkId) {
            $this->setLinkId($linkId);
        }
        if (null !== $definition) {
            $this->setDefinition($definition);
        }
        if (null !== $code) {
            $this->setCode(...$code);
        }
        if (null !== $prefix) {
            $this->setPrefix($prefix);
        }
        if (null !== $text) {
            $this->setText($text);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $enableWhen) {
            $this->setEnableWhen(...$enableWhen);
        }
        if (null !== $enableBehavior) {
            $this->setEnableBehavior($enableBehavior);
        }
        if (null !== $required) {
            $this->setRequired($required);
        }
        if (null !== $repeats) {
            $this->setRepeats($repeats);
        }
        if (null !== $readOnly) {
            $this->setReadOnly($readOnly);
        }
        if (null !== $maxLength) {
            $this->setMaxLength($maxLength);
        }
        if (null !== $answerValueSet) {
            $this->setAnswerValueSet($answerValueSet);
        }
        if (null !== $answerOption) {
            $this->setAnswerOption(...$answerOption);
        }
        if (null !== $initial) {
            $this->setInitial(...$initial);
        }
        if (null !== $item) {
            $this->setItem(...$item);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An identifier that is unique within the Questionnaire allowing linkage to the
     * equivalent item in a QuestionnaireResponse resource.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getLinkId(): null|FHIRString
    {
        return $this->linkId ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An identifier that is unique within the Questionnaire allowing linkage to the
     * equivalent item in a QuestionnaireResponse resource.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $linkId
     * @return static
     */
    public function setLinkId(null|string|FHIRStringPrimitive|FHIRString $linkId): self
    {
        if (null === $linkId) {
            unset($this->linkId);
            return $this;
        }
        if (!($linkId instanceof FHIRString)) {
            $linkId = new FHIRString(value: $linkId);
        }
        $this->linkId = $linkId;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This element is a URI that refers to an [[[ElementDefinition]]] that provides
     * information about this item, including information that might otherwise be
     * included in the instance of the Questionnaire resource. A detailed description
     * of the construction of the URI is shown in Comments, below. If this element is
     * present then the following element values MAY be derived from the Element
     * Definition if the corresponding elements of this Questionnaire resource instance
     * have no value: * code (ElementDefinition.code) * type (ElementDefinition.type) *
     * required (ElementDefinition.min) * repeats (ElementDefinition.max) * maxLength
     * (ElementDefinition.maxLength) * answerValueSet (ElementDefinition.binding) *
     * options (ElementDefinition.binding).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getDefinition(): null|FHIRUri
    {
        return $this->definition ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This element is a URI that refers to an [[[ElementDefinition]]] that provides
     * information about this item, including information that might otherwise be
     * included in the instance of the Questionnaire resource. A detailed description
     * of the construction of the URI is shown in Comments, below. If this element is
     * present then the following element values MAY be derived from the Element
     * Definition if the corresponding elements of this Questionnaire resource instance
     * have no value: * code (ElementDefinition.code) * type (ElementDefinition.type) *
     * required (ElementDefinition.min) * repeats (ElementDefinition.max) * maxLength
     * (ElementDefinition.maxLength) * answerValueSet (ElementDefinition.binding) *
     * options (ElementDefinition.binding).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $definition
     * @return static
     */
    public function setDefinition(null|string|FHIRUriPrimitive|FHIRUri $definition): self
    {
        if (null === $definition) {
            unset($this->definition);
            return $this;
        }
        if (!($definition instanceof FHIRUri)) {
            $definition = new FHIRUri(value: $definition);
        }
        $this->definition = $definition;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A terminology code that corresponds to this group or question (e.g. a code from
     * LOINC, which defines many questions and answers).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getCode(): array
    {
        return $this->code ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getCodeIterator(): iterable
    {
        if (!isset($this->code)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->code);
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A terminology code that corresponds to this group or question (e.g. a code from
     * LOINC, which defines many questions and answers).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $code
     * @return static
     */
    public function addCode(FHIRCoding $code): self
    {
        if (!isset($this->code)) {
            $this->code = [];
        }
        $this->code[] = $code;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A terminology code that corresponds to this group or question (e.g. a code from
     * LOINC, which defines many questions and answers).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding ...$code
     * @return static
     */
    public function setCode(FHIRCoding ...$code): self
    {
        if ([] === $code) {
            unset($this->code);
            return $this;
        }
        $this->code = $code;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short label for a particular group, question or set of display text within the
     * questionnaire used for reference by the individual completing the questionnaire.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getPrefix(): null|FHIRString
    {
        return $this->prefix ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short label for a particular group, question or set of display text within the
     * questionnaire used for reference by the individual completing the questionnaire.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $prefix
     * @return static
     */
    public function setPrefix(null|string|FHIRStringPrimitive|FHIRString $prefix): self
    {
        if (null === $prefix) {
            unset($this->prefix);
            return $this;
        }
        if (!($prefix instanceof FHIRString)) {
            $prefix = new FHIRString(value: $prefix);
        }
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of a section, the text of a question or text content for a display
     * item.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getText(): null|FHIRString
    {
        return $this->text ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The name of a section, the text of a question or text content for a display
     * item.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $text
     * @return static
     */
    public function setText(null|string|FHIRStringPrimitive|FHIRString $text): self
    {
        if (null === $text) {
            unset($this->text);
            return $this;
        }
        if (!($text instanceof FHIRString)) {
            $text = new FHIRString(value: $text);
        }
        $this->text = $text;
        return $this;
    }

    /**
     * Distinguishes groups from questions and display text and indicates data type for
     * questions.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of questionnaire item this is - whether text for display, a grouping of
     * other items or a particular type of data to be captured (string, integer, coded
     * choice, etc.).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuestionnaireItemType
     */
    public function getType(): null|FHIRQuestionnaireItemType
    {
        return $this->type ?? null;
    }

    /**
     * Distinguishes groups from questions and display text and indicates data type for
     * questions.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of questionnaire item this is - whether text for display, a grouping of
     * other items or a particular type of data to be captured (string, integer, coded
     * choice, etc.).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRQuestionnaireItemTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuestionnaireItemType $type
     * @return static
     */
    public function setType(null|string|FHIRQuestionnaireItemTypeList|FHIRQuestionnaireItemType $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        if (!($type instanceof FHIRQuestionnaireItemType)) {
            $type = new FHIRQuestionnaireItemType(value: $type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * A constraint indicating that this item should only be enabled (displayed/allow
     * answers to be captured) when the specified condition is true.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen>
     */
    public function getEnableWhen(): array
    {
        return $this->enableWhen ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen>
     */
    public function getEnableWhenIterator(): iterable
    {
        if (!isset($this->enableWhen)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->enableWhen);
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * A constraint indicating that this item should only be enabled (displayed/allow
     * answers to be captured) when the specified condition is true.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen $enableWhen
     * @return static
     */
    public function addEnableWhen(FHIRQuestionnaireEnableWhen $enableWhen): self
    {
        if (!isset($this->enableWhen)) {
            $this->enableWhen = [];
        }
        $this->enableWhen[] = $enableWhen;
        return $this;
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * A constraint indicating that this item should only be enabled (displayed/allow
     * answers to be captured) when the specified condition is true.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen ...$enableWhen
     * @return static
     */
    public function setEnableWhen(FHIRQuestionnaireEnableWhen ...$enableWhen): self
    {
        if ([] === $enableWhen) {
            unset($this->enableWhen);
            return $this;
        }
        $this->enableWhen = $enableWhen;
        return $this;
    }

    /**
     * Controls how multiple enableWhen values are interpreted - whether all or any
     * must be true.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Controls how multiple enableWhen values are interpreted - whether all or any
     * must be true.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREnableWhenBehavior
     */
    public function getEnableBehavior(): null|FHIREnableWhenBehavior
    {
        return $this->enableBehavior ?? null;
    }

    /**
     * Controls how multiple enableWhen values are interpreted - whether all or any
     * must be true.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Controls how multiple enableWhen values are interpreted - whether all or any
     * must be true.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREnableWhenBehaviorList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREnableWhenBehavior $enableBehavior
     * @return static
     */
    public function setEnableBehavior(null|string|FHIREnableWhenBehaviorList|FHIREnableWhenBehavior $enableBehavior): self
    {
        if (null === $enableBehavior) {
            unset($this->enableBehavior);
            return $this;
        }
        if (!($enableBehavior instanceof FHIREnableWhenBehavior)) {
            $enableBehavior = new FHIREnableWhenBehavior(value: $enableBehavior);
        }
        $this->enableBehavior = $enableBehavior;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication, if true, that the item must be present in a "completed"
     * QuestionnaireResponse. If false, the item may be skipped when answering the
     * questionnaire.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getRequired(): null|FHIRBoolean
    {
        return $this->required ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication, if true, that the item must be present in a "completed"
     * QuestionnaireResponse. If false, the item may be skipped when answering the
     * questionnaire.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $required
     * @return static
     */
    public function setRequired(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $required): self
    {
        if (null === $required) {
            unset($this->required);
            return $this;
        }
        if (!($required instanceof FHIRBoolean)) {
            $required = new FHIRBoolean(value: $required);
        }
        $this->required = $required;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication, if true, that the item may occur multiple times in the response,
     * collecting multiple answers for questions or multiple sets of answers for
     * groups.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getRepeats(): null|FHIRBoolean
    {
        return $this->repeats ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication, if true, that the item may occur multiple times in the response,
     * collecting multiple answers for questions or multiple sets of answers for
     * groups.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $repeats
     * @return static
     */
    public function setRepeats(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $repeats): self
    {
        if (null === $repeats) {
            unset($this->repeats);
            return $this;
        }
        if (!($repeats instanceof FHIRBoolean)) {
            $repeats = new FHIRBoolean(value: $repeats);
        }
        $this->repeats = $repeats;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication, when true, that the value cannot be changed by a human respondent
     * to the Questionnaire.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getReadOnly(): null|FHIRBoolean
    {
        return $this->readOnly ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication, when true, that the value cannot be changed by a human respondent
     * to the Questionnaire.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $readOnly
     * @return static
     */
    public function setReadOnly(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $readOnly): self
    {
        if (null === $readOnly) {
            unset($this->readOnly);
            return $this;
        }
        if (!($readOnly instanceof FHIRBoolean)) {
            $readOnly = new FHIRBoolean(value: $readOnly);
        }
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The maximum number of characters that are permitted in the answer to be
     * considered a "valid" QuestionnaireResponse.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getMaxLength(): null|FHIRInteger
    {
        return $this->maxLength ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The maximum number of characters that are permitted in the answer to be
     * considered a "valid" QuestionnaireResponse.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $maxLength
     * @return static
     */
    public function setMaxLength(null|string|float|FHIRIntegerPrimitive|FHIRInteger $maxLength): self
    {
        if (null === $maxLength) {
            unset($this->maxLength);
            return $this;
        }
        if (!($maxLength instanceof FHIRInteger)) {
            $maxLength = new FHIRInteger(value: $maxLength);
        }
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A reference to a value set containing a list of codes representing permitted
     * answers for a "choice" or "open-choice" question.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getAnswerValueSet(): null|FHIRCanonical
    {
        return $this->answerValueSet ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A reference to a value set containing a list of codes representing permitted
     * answers for a "choice" or "open-choice" question.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $answerValueSet
     * @return static
     */
    public function setAnswerValueSet(null|string|FHIRCanonicalPrimitive|FHIRCanonical $answerValueSet): self
    {
        if (null === $answerValueSet) {
            unset($this->answerValueSet);
            return $this;
        }
        if (!($answerValueSet instanceof FHIRCanonical)) {
            $answerValueSet = new FHIRCanonical(value: $answerValueSet);
        }
        $this->answerValueSet = $answerValueSet;
        return $this;
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * One of the permitted answers for a "choice" or "open-choice" question.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireAnswerOption>
     */
    public function getAnswerOption(): array
    {
        return $this->answerOption ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireAnswerOption>
     */
    public function getAnswerOptionIterator(): iterable
    {
        if (!isset($this->answerOption)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->answerOption);
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * One of the permitted answers for a "choice" or "open-choice" question.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireAnswerOption $answerOption
     * @return static
     */
    public function addAnswerOption(FHIRQuestionnaireAnswerOption $answerOption): self
    {
        if (!isset($this->answerOption)) {
            $this->answerOption = [];
        }
        $this->answerOption[] = $answerOption;
        return $this;
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * One of the permitted answers for a "choice" or "open-choice" question.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireAnswerOption ...$answerOption
     * @return static
     */
    public function setAnswerOption(FHIRQuestionnaireAnswerOption ...$answerOption): self
    {
        if ([] === $answerOption) {
            unset($this->answerOption);
            return $this;
        }
        $this->answerOption = $answerOption;
        return $this;
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * One or more values that should be pre-populated in the answer when initially
     * rendering the questionnaire for user input.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireInitial>
     */
    public function getInitial(): array
    {
        return $this->initial ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireInitial>
     */
    public function getInitialIterator(): iterable
    {
        if (!isset($this->initial)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->initial);
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * One or more values that should be pre-populated in the answer when initially
     * rendering the questionnaire for user input.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireInitial $initial
     * @return static
     */
    public function addInitial(FHIRQuestionnaireInitial $initial): self
    {
        if (!isset($this->initial)) {
            $this->initial = [];
        }
        $this->initial[] = $initial;
        return $this;
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * One or more values that should be pre-populated in the answer when initially
     * rendering the questionnaire for user input.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireInitial ...$initial
     * @return static
     */
    public function setInitial(FHIRQuestionnaireInitial ...$initial): self
    {
        if ([] === $initial) {
            unset($this->initial);
            return $this;
        }
        $this->initial = $initial;
        return $this;
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * Text, questions and other groups to be nested beneath a question or group.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem>
     */
    public function getItem(): array
    {
        return $this->item ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem>
     */
    public function getItemIterator(): iterable
    {
        if (!isset($this->item)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->item);
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * Text, questions and other groups to be nested beneath a question or group.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem $item
     * @return static
     */
    public function addItem(FHIRQuestionnaireItem $item): self
    {
        if (!isset($this->item)) {
            $this->item = [];
        }
        $this->item[] = $item;
        return $this;
    }

    /**
     * A structured set of questions intended to guide the collection of answers from
     * end-users. Questionnaires provide detailed control over order, presentation,
     * phraseology and grouping to allow coherent, consistent data collection.
     *
     * Text, questions and other groups to be nested beneath a question or group.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem ...$item
     * @return static
     */
    public function setItem(FHIRQuestionnaireItem ...$item): self
    {
        if ([] === $item) {
            unset($this->item);
            return $this;
        }
        $this->item = $item;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRQuestionnaireItem)) {
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
            } else if (self::FIELD_LINK_ID === $cen) {
                $type->setLinkId(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION === $cen) {
                $type->setDefinition(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->addCode(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PREFIX === $cen) {
                $type->setPrefix(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRQuestionnaireItemType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENABLE_WHEN === $cen) {
                $type->addEnableWhen(FHIRQuestionnaireEnableWhen::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENABLE_BEHAVIOR === $cen) {
                $type->setEnableBehavior(FHIREnableWhenBehavior::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUIRED === $cen) {
                $type->setRequired(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REPEATS === $cen) {
                $type->setRepeats(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_READ_ONLY === $cen) {
                $type->setReadOnly(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MAX_LENGTH === $cen) {
                $type->setMaxLength(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ANSWER_VALUE_SET === $cen) {
                $type->setAnswerValueSet(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ANSWER_OPTION === $cen) {
                $type->addAnswerOption(FHIRQuestionnaireAnswerOption::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INITIAL === $cen) {
                $type->addInitial(FHIRQuestionnaireInitial::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ITEM === $cen) {
                $type->addItem(FHIRQuestionnaireItem::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LINK_ID])) {
            if (isset($type->linkId)) {
                $type->linkId->setValue((string)$attributes[self::FIELD_LINK_ID]);
            } else {
                $type->setLinkId((string)$attributes[self::FIELD_LINK_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LINK_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DEFINITION])) {
            if (isset($type->definition)) {
                $type->definition->setValue((string)$attributes[self::FIELD_DEFINITION]);
            } else {
                $type->setDefinition((string)$attributes[self::FIELD_DEFINITION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DEFINITION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PREFIX])) {
            if (isset($type->prefix)) {
                $type->prefix->setValue((string)$attributes[self::FIELD_PREFIX]);
            } else {
                $type->setPrefix((string)$attributes[self::FIELD_PREFIX]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PREFIX, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TEXT])) {
            if (isset($type->text)) {
                $type->text->setValue((string)$attributes[self::FIELD_TEXT]);
            } else {
                $type->setText((string)$attributes[self::FIELD_TEXT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TEXT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TYPE])) {
            if (isset($type->type)) {
                $type->type->setValue((string)$attributes[self::FIELD_TYPE]);
            } else {
                $type->setType((string)$attributes[self::FIELD_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ENABLE_BEHAVIOR])) {
            if (isset($type->enableBehavior)) {
                $type->enableBehavior->setValue((string)$attributes[self::FIELD_ENABLE_BEHAVIOR]);
            } else {
                $type->setEnableBehavior((string)$attributes[self::FIELD_ENABLE_BEHAVIOR]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ENABLE_BEHAVIOR, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REQUIRED])) {
            if (isset($type->required)) {
                $type->required->setValue((string)$attributes[self::FIELD_REQUIRED]);
            } else {
                $type->setRequired((string)$attributes[self::FIELD_REQUIRED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REQUIRED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REPEATS])) {
            if (isset($type->repeats)) {
                $type->repeats->setValue((string)$attributes[self::FIELD_REPEATS]);
            } else {
                $type->setRepeats((string)$attributes[self::FIELD_REPEATS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REPEATS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_READ_ONLY])) {
            if (isset($type->readOnly)) {
                $type->readOnly->setValue((string)$attributes[self::FIELD_READ_ONLY]);
            } else {
                $type->setReadOnly((string)$attributes[self::FIELD_READ_ONLY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_READ_ONLY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MAX_LENGTH])) {
            if (isset($type->maxLength)) {
                $type->maxLength->setValue((string)$attributes[self::FIELD_MAX_LENGTH]);
            } else {
                $type->setMaxLength((string)$attributes[self::FIELD_MAX_LENGTH]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MAX_LENGTH, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ANSWER_VALUE_SET])) {
            if (isset($type->answerValueSet)) {
                $type->answerValueSet->setValue((string)$attributes[self::FIELD_ANSWER_VALUE_SET]);
            } else {
                $type->setAnswerValueSet((string)$attributes[self::FIELD_ANSWER_VALUE_SET]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ANSWER_VALUE_SET, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->linkId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LINK_ID]) {
            $xw->writeAttribute(self::FIELD_LINK_ID, $this->linkId->_getValueAsString());
        }
        if (isset($this->definition) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DEFINITION]) {
            $xw->writeAttribute(self::FIELD_DEFINITION, $this->definition->_getValueAsString());
        }
        if (isset($this->prefix) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PREFIX]) {
            $xw->writeAttribute(self::FIELD_PREFIX, $this->prefix->_getValueAsString());
        }
        if (isset($this->text) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TEXT]) {
            $xw->writeAttribute(self::FIELD_TEXT, $this->text->_getValueAsString());
        }
        if (isset($this->type) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TYPE]) {
            $xw->writeAttribute(self::FIELD_TYPE, $this->type->_getValueAsString());
        }
        if (isset($this->enableBehavior) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ENABLE_BEHAVIOR]) {
            $xw->writeAttribute(self::FIELD_ENABLE_BEHAVIOR, $this->enableBehavior->_getValueAsString());
        }
        if (isset($this->required) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REQUIRED]) {
            $xw->writeAttribute(self::FIELD_REQUIRED, $this->required->_getValueAsString());
        }
        if (isset($this->repeats) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REPEATS]) {
            $xw->writeAttribute(self::FIELD_REPEATS, $this->repeats->_getValueAsString());
        }
        if (isset($this->readOnly) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_READ_ONLY]) {
            $xw->writeAttribute(self::FIELD_READ_ONLY, $this->readOnly->_getValueAsString());
        }
        if (isset($this->maxLength) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MAX_LENGTH]) {
            $xw->writeAttribute(self::FIELD_MAX_LENGTH, $this->maxLength->_getValueAsString());
        }
        if (isset($this->answerValueSet) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ANSWER_VALUE_SET]) {
            $xw->writeAttribute(self::FIELD_ANSWER_VALUE_SET, $this->answerValueSet->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->linkId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LINK_ID]
                || $this->linkId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LINK_ID);
            $this->linkId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LINK_ID]);
            $xw->endElement();
        }
        if (isset($this->definition)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DEFINITION]
                || $this->definition->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DEFINITION);
            $this->definition->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DEFINITION]);
            $xw->endElement();
        }
        if (isset($this->code)) {
            foreach ($this->code as $v) {
                $xw->startElement(self::FIELD_CODE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->prefix)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PREFIX]
                || $this->prefix->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PREFIX);
            $this->prefix->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PREFIX]);
            $xw->endElement();
        }
        if (isset($this->text)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TEXT]
                || $this->text->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TEXT);
            $this->text->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TEXT]);
            $xw->endElement();
        }
        if (isset($this->type)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TYPE]
                || $this->type->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TYPE]);
            $xw->endElement();
        }
        if (isset($this->enableWhen)) {
            foreach ($this->enableWhen as $v) {
                $xw->startElement(self::FIELD_ENABLE_WHEN);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->enableBehavior)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ENABLE_BEHAVIOR]
                || $this->enableBehavior->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ENABLE_BEHAVIOR);
            $this->enableBehavior->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ENABLE_BEHAVIOR]);
            $xw->endElement();
        }
        if (isset($this->required)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REQUIRED]
                || $this->required->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REQUIRED);
            $this->required->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REQUIRED]);
            $xw->endElement();
        }
        if (isset($this->repeats)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REPEATS]
                || $this->repeats->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REPEATS);
            $this->repeats->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REPEATS]);
            $xw->endElement();
        }
        if (isset($this->readOnly)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_READ_ONLY]
                || $this->readOnly->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_READ_ONLY);
            $this->readOnly->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_READ_ONLY]);
            $xw->endElement();
        }
        if (isset($this->maxLength)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MAX_LENGTH]
                || $this->maxLength->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MAX_LENGTH);
            $this->maxLength->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MAX_LENGTH]);
            $xw->endElement();
        }
        if (isset($this->answerValueSet)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ANSWER_VALUE_SET]
                || $this->answerValueSet->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ANSWER_VALUE_SET);
            $this->answerValueSet->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ANSWER_VALUE_SET]);
            $xw->endElement();
        }
        if (isset($this->answerOption)) {
            foreach ($this->answerOption as $v) {
                $xw->startElement(self::FIELD_ANSWER_OPTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->initial)) {
            foreach ($this->initial as $v) {
                $xw->startElement(self::FIELD_INITIAL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->item)) {
            foreach ($this->item as $v) {
                $xw->startElement(self::FIELD_ITEM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRQuestionnaire\FHIRQuestionnaireItem
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
        } else if (!($type instanceof FHIRQuestionnaireItem)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->linkId)
            || isset($decoded->_linkId)
            || property_exists($decoded, self::FIELD_LINK_ID)
            || property_exists($decoded, self::FIELD_LINK_ID_EXT)) {
            $v = $decoded->_linkId ?? new \stdClass();
            $v->value = $decoded->linkId ?? null;
            $type->setLinkId(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->definition)
            || isset($decoded->_definition)
            || property_exists($decoded, self::FIELD_DEFINITION)
            || property_exists($decoded, self::FIELD_DEFINITION_EXT)) {
            $v = $decoded->_definition ?? new \stdClass();
            $v->value = $decoded->definition ?? null;
            $type->setDefinition(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_object($decoded->code)) {
                $vals = [$decoded->code];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CODE, true);
            } else {
                $vals = $decoded->code;
            }
            foreach($vals as $v) {
                $type->addCode(FHIRCoding::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->prefix)
            || isset($decoded->_prefix)
            || property_exists($decoded, self::FIELD_PREFIX)
            || property_exists($decoded, self::FIELD_PREFIX_EXT)) {
            $v = $decoded->_prefix ?? new \stdClass();
            $v->value = $decoded->prefix ?? null;
            $type->setPrefix(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->text)
            || isset($decoded->_text)
            || property_exists($decoded, self::FIELD_TEXT)
            || property_exists($decoded, self::FIELD_TEXT_EXT)) {
            $v = $decoded->_text ?? new \stdClass();
            $v->value = $decoded->text ?? null;
            $type->setText(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->type)
            || isset($decoded->_type)
            || property_exists($decoded, self::FIELD_TYPE)
            || property_exists($decoded, self::FIELD_TYPE_EXT)) {
            $v = $decoded->_type ?? new \stdClass();
            $v->value = $decoded->type ?? null;
            $type->setType(FHIRQuestionnaireItemType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->enableWhen) || property_exists($decoded, self::FIELD_ENABLE_WHEN)) {
            if (is_object($decoded->enableWhen)) {
                $vals = [$decoded->enableWhen];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ENABLE_WHEN, true);
            } else {
                $vals = $decoded->enableWhen;
            }
            foreach($vals as $v) {
                $type->addEnableWhen(FHIRQuestionnaireEnableWhen::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->enableBehavior)
            || isset($decoded->_enableBehavior)
            || property_exists($decoded, self::FIELD_ENABLE_BEHAVIOR)
            || property_exists($decoded, self::FIELD_ENABLE_BEHAVIOR_EXT)) {
            $v = $decoded->_enableBehavior ?? new \stdClass();
            $v->value = $decoded->enableBehavior ?? null;
            $type->setEnableBehavior(FHIREnableWhenBehavior::jsonUnserialize($v, $config));
        }
        if (isset($decoded->required)
            || isset($decoded->_required)
            || property_exists($decoded, self::FIELD_REQUIRED)
            || property_exists($decoded, self::FIELD_REQUIRED_EXT)) {
            $v = $decoded->_required ?? new \stdClass();
            $v->value = $decoded->required ?? null;
            $type->setRequired(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->repeats)
            || isset($decoded->_repeats)
            || property_exists($decoded, self::FIELD_REPEATS)
            || property_exists($decoded, self::FIELD_REPEATS_EXT)) {
            $v = $decoded->_repeats ?? new \stdClass();
            $v->value = $decoded->repeats ?? null;
            $type->setRepeats(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->readOnly)
            || isset($decoded->_readOnly)
            || property_exists($decoded, self::FIELD_READ_ONLY)
            || property_exists($decoded, self::FIELD_READ_ONLY_EXT)) {
            $v = $decoded->_readOnly ?? new \stdClass();
            $v->value = $decoded->readOnly ?? null;
            $type->setReadOnly(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->maxLength)
            || isset($decoded->_maxLength)
            || property_exists($decoded, self::FIELD_MAX_LENGTH)
            || property_exists($decoded, self::FIELD_MAX_LENGTH_EXT)) {
            $v = $decoded->_maxLength ?? new \stdClass();
            $v->value = $decoded->maxLength ?? null;
            $type->setMaxLength(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->answerValueSet)
            || isset($decoded->_answerValueSet)
            || property_exists($decoded, self::FIELD_ANSWER_VALUE_SET)
            || property_exists($decoded, self::FIELD_ANSWER_VALUE_SET_EXT)) {
            $v = $decoded->_answerValueSet ?? new \stdClass();
            $v->value = $decoded->answerValueSet ?? null;
            $type->setAnswerValueSet(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->answerOption) || property_exists($decoded, self::FIELD_ANSWER_OPTION)) {
            if (is_object($decoded->answerOption)) {
                $vals = [$decoded->answerOption];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ANSWER_OPTION, true);
            } else {
                $vals = $decoded->answerOption;
            }
            foreach($vals as $v) {
                $type->addAnswerOption(FHIRQuestionnaireAnswerOption::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->initial) || property_exists($decoded, self::FIELD_INITIAL)) {
            if (is_object($decoded->initial)) {
                $vals = [$decoded->initial];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INITIAL, true);
            } else {
                $vals = $decoded->initial;
            }
            foreach($vals as $v) {
                $type->addInitial(FHIRQuestionnaireInitial::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->item) || property_exists($decoded, self::FIELD_ITEM)) {
            if (is_object($decoded->item)) {
                $vals = [$decoded->item];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ITEM, true);
            } else {
                $vals = $decoded->item;
            }
            foreach($vals as $v) {
                $type->addItem(FHIRQuestionnaireItem::jsonUnserialize($v, $config));
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
        if (isset($this->linkId)) {
            if (null !== ($val = $this->linkId->getValue())) {
                $out->linkId = $val;
            }
            if ($this->linkId->_nonValueFieldDefined()) {
                $ext = $this->linkId->jsonSerialize();
                unset($ext->value);
                $out->_linkId = $ext;
            }
        }
        if (isset($this->definition)) {
            if (null !== ($val = $this->definition->getValue())) {
                $out->definition = $val;
            }
            if ($this->definition->_nonValueFieldDefined()) {
                $ext = $this->definition->jsonSerialize();
                unset($ext->value);
                $out->_definition = $ext;
            }
        }
        if (isset($this->code) && [] !== $this->code) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CODE) && 1 === count($this->code)) {
                $out->code = $this->code[0];
            } else {
                $out->code = $this->code;
            }
        }
        if (isset($this->prefix)) {
            if (null !== ($val = $this->prefix->getValue())) {
                $out->prefix = $val;
            }
            if ($this->prefix->_nonValueFieldDefined()) {
                $ext = $this->prefix->jsonSerialize();
                unset($ext->value);
                $out->_prefix = $ext;
            }
        }
        if (isset($this->text)) {
            if (null !== ($val = $this->text->getValue())) {
                $out->text = $val;
            }
            if ($this->text->_nonValueFieldDefined()) {
                $ext = $this->text->jsonSerialize();
                unset($ext->value);
                $out->_text = $ext;
            }
        }
        if (isset($this->type)) {
            if (null !== ($val = $this->type->getValue())) {
                $out->type = $val;
            }
            if ($this->type->_nonValueFieldDefined()) {
                $ext = $this->type->jsonSerialize();
                unset($ext->value);
                $out->_type = $ext;
            }
        }
        if (isset($this->enableWhen) && [] !== $this->enableWhen) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ENABLE_WHEN) && 1 === count($this->enableWhen)) {
                $out->enableWhen = $this->enableWhen[0];
            } else {
                $out->enableWhen = $this->enableWhen;
            }
        }
        if (isset($this->enableBehavior)) {
            if (null !== ($val = $this->enableBehavior->getValue())) {
                $out->enableBehavior = $val;
            }
            if ($this->enableBehavior->_nonValueFieldDefined()) {
                $ext = $this->enableBehavior->jsonSerialize();
                unset($ext->value);
                $out->_enableBehavior = $ext;
            }
        }
        if (isset($this->required)) {
            if (null !== ($val = $this->required->getValue())) {
                $out->required = $val;
            }
            if ($this->required->_nonValueFieldDefined()) {
                $ext = $this->required->jsonSerialize();
                unset($ext->value);
                $out->_required = $ext;
            }
        }
        if (isset($this->repeats)) {
            if (null !== ($val = $this->repeats->getValue())) {
                $out->repeats = $val;
            }
            if ($this->repeats->_nonValueFieldDefined()) {
                $ext = $this->repeats->jsonSerialize();
                unset($ext->value);
                $out->_repeats = $ext;
            }
        }
        if (isset($this->readOnly)) {
            if (null !== ($val = $this->readOnly->getValue())) {
                $out->readOnly = $val;
            }
            if ($this->readOnly->_nonValueFieldDefined()) {
                $ext = $this->readOnly->jsonSerialize();
                unset($ext->value);
                $out->_readOnly = $ext;
            }
        }
        if (isset($this->maxLength)) {
            if (null !== ($val = $this->maxLength->getValue())) {
                $out->maxLength = $val;
            }
            if ($this->maxLength->_nonValueFieldDefined()) {
                $ext = $this->maxLength->jsonSerialize();
                unset($ext->value);
                $out->_maxLength = $ext;
            }
        }
        if (isset($this->answerValueSet)) {
            if (null !== ($val = $this->answerValueSet->getValue())) {
                $out->answerValueSet = $val;
            }
            if ($this->answerValueSet->_nonValueFieldDefined()) {
                $ext = $this->answerValueSet->jsonSerialize();
                unset($ext->value);
                $out->_answerValueSet = $ext;
            }
        }
        if (isset($this->answerOption) && [] !== $this->answerOption) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ANSWER_OPTION) && 1 === count($this->answerOption)) {
                $out->answerOption = $this->answerOption[0];
            } else {
                $out->answerOption = $this->answerOption;
            }
        }
        if (isset($this->initial) && [] !== $this->initial) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INITIAL) && 1 === count($this->initial)) {
                $out->initial = $this->initial[0];
            } else {
                $out->initial = $this->initial;
            }
        }
        if (isset($this->item) && [] !== $this->item) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ITEM) && 1 === count($this->item)) {
                $out->item = $this->item[0];
            } else {
                $out->item = $this->item;
            }
        }
        return $out;
    }
}
