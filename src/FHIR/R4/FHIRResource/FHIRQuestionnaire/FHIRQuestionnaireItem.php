<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire;

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
 * A structured set of questions intended to guide the collection of answers from end-users. Questionnaires provide detailed control over order, presentation, phraseology and grouping to allow coherent, consistent data collection.
 */
class FHIRQuestionnaireItem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * An identifier that is unique within the Questionnaire allowing linkage to the equivalent item in a QuestionnaireResponse resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $linkId = null;

    /**
     * This element is a URI that refers to an [[[ElementDefinition]]] that provides information about this item, including information that might otherwise be included in the instance of the Questionnaire resource. A detailed description of the construction of the URI is shown in Comments, below. If this element is present then the following element values MAY be derived from the Element Definition if the corresponding elements of this Questionnaire resource instance have no value:

* code (ElementDefinition.code)
* type (ElementDefinition.type)
* required (ElementDefinition.min)
* repeats (ElementDefinition.max)
* maxLength (ElementDefinition.maxLength)
* answerValueSet (ElementDefinition.binding)
* options (ElementDefinition.binding).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $definition = null;

    /**
     * A terminology code that corresponds to this group or question (e.g. a code from LOINC, which defines many questions and answers).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public $code = [];

    /**
     * A short label for a particular group, question or set of display text within the questionnaire used for reference by the individual completing the questionnaire.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $prefix = null;

    /**
     * The name of a section, the text of a question or text content for a display item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * The type of questionnaire item this is - whether text for display, a grouping of other items or a particular type of data to be captured (string, integer, coded choice, etc.).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuestionnaireItemType
     */
    public $type = null;

    /**
     * A constraint indicating that this item should only be enabled (displayed/allow answers to be captured) when the specified condition is true.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen[]
     */
    public $enableWhen = [];

    /**
     * Controls how multiple enableWhen values are interpreted -  whether all or any must be true.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIREnableWhenBehavior
     */
    public $enableBehavior = null;

    /**
     * An indication, if true, that the item must be present in a "completed" QuestionnaireResponse.  If false, the item may be skipped when answering the questionnaire.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $required = null;

    /**
     * An indication, if true, that the item may occur multiple times in the response, collecting multiple answers for questions or multiple sets of answers for groups.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $repeats = null;

    /**
     * An indication, when true, that the value cannot be changed by a human respondent to the Questionnaire.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $readOnly = null;

    /**
     * The maximum number of characters that are permitted in the answer to be considered a "valid" QuestionnaireResponse.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public $maxLength = null;

    /**
     * A reference to a value set containing a list of codes representing permitted answers for a "choice" or "open-choice" question.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $answerValueSet = null;

    /**
     * One of the permitted answers for a "choice" or "open-choice" question.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireAnswerOption[]
     */
    public $answerOption = [];

    /**
     * One or more values that should be pre-populated in the answer when initially rendering the questionnaire for user input.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireInitial[]
     */
    public $initial = [];

    /**
     * Text, questions and other groups to be nested beneath a question or group.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireItem[]
     */
    public $item = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Questionnaire.Item';

    /**
     * An identifier that is unique within the Questionnaire allowing linkage to the equivalent item in a QuestionnaireResponse resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * An identifier that is unique within the Questionnaire allowing linkage to the equivalent item in a QuestionnaireResponse resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $linkId
     * @return $this
     */
    public function setLinkId($linkId)
    {
        $this->linkId = $linkId;
        return $this;
    }

    /**
     * This element is a URI that refers to an [[[ElementDefinition]]] that provides information about this item, including information that might otherwise be included in the instance of the Questionnaire resource. A detailed description of the construction of the URI is shown in Comments, below. If this element is present then the following element values MAY be derived from the Element Definition if the corresponding elements of this Questionnaire resource instance have no value:

* code (ElementDefinition.code)
* type (ElementDefinition.type)
* required (ElementDefinition.min)
* repeats (ElementDefinition.max)
* maxLength (ElementDefinition.maxLength)
* answerValueSet (ElementDefinition.binding)
* options (ElementDefinition.binding).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * This element is a URI that refers to an [[[ElementDefinition]]] that provides information about this item, including information that might otherwise be included in the instance of the Questionnaire resource. A detailed description of the construction of the URI is shown in Comments, below. If this element is present then the following element values MAY be derived from the Element Definition if the corresponding elements of this Questionnaire resource instance have no value:

* code (ElementDefinition.code)
* type (ElementDefinition.type)
* required (ElementDefinition.min)
* repeats (ElementDefinition.max)
* maxLength (ElementDefinition.maxLength)
* answerValueSet (ElementDefinition.binding)
* options (ElementDefinition.binding).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $definition
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
        return $this;
    }

    /**
     * A terminology code that corresponds to this group or question (e.g. a code from LOINC, which defines many questions and answers).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A terminology code that corresponds to this group or question (e.g. a code from LOINC, which defines many questions and answers).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $code
     * @return $this
     */
    public function addCode($code)
    {
        $this->code[] = $code;
        return $this;
    }

    /**
     * A short label for a particular group, question or set of display text within the questionnaire used for reference by the individual completing the questionnaire.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * A short label for a particular group, question or set of display text within the questionnaire used for reference by the individual completing the questionnaire.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * The name of a section, the text of a question or text content for a display item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * The name of a section, the text of a question or text content for a display item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * The type of questionnaire item this is - whether text for display, a grouping of other items or a particular type of data to be captured (string, integer, coded choice, etc.).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuestionnaireItemType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of questionnaire item this is - whether text for display, a grouping of other items or a particular type of data to be captured (string, integer, coded choice, etc.).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuestionnaireItemType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A constraint indicating that this item should only be enabled (displayed/allow answers to be captured) when the specified condition is true.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen[]
     */
    public function getEnableWhen()
    {
        return $this->enableWhen;
    }

    /**
     * A constraint indicating that this item should only be enabled (displayed/allow answers to be captured) when the specified condition is true.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen $enableWhen
     * @return $this
     */
    public function addEnableWhen($enableWhen)
    {
        $this->enableWhen[] = $enableWhen;
        return $this;
    }

    /**
     * Controls how multiple enableWhen values are interpreted -  whether all or any must be true.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIREnableWhenBehavior
     */
    public function getEnableBehavior()
    {
        return $this->enableBehavior;
    }

    /**
     * Controls how multiple enableWhen values are interpreted -  whether all or any must be true.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIREnableWhenBehavior $enableBehavior
     * @return $this
     */
    public function setEnableBehavior($enableBehavior)
    {
        $this->enableBehavior = $enableBehavior;
        return $this;
    }

    /**
     * An indication, if true, that the item must be present in a "completed" QuestionnaireResponse.  If false, the item may be skipped when answering the questionnaire.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * An indication, if true, that the item must be present in a "completed" QuestionnaireResponse.  If false, the item may be skipped when answering the questionnaire.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * An indication, if true, that the item may occur multiple times in the response, collecting multiple answers for questions or multiple sets of answers for groups.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getRepeats()
    {
        return $this->repeats;
    }

    /**
     * An indication, if true, that the item may occur multiple times in the response, collecting multiple answers for questions or multiple sets of answers for groups.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $repeats
     * @return $this
     */
    public function setRepeats($repeats)
    {
        $this->repeats = $repeats;
        return $this;
    }

    /**
     * An indication, when true, that the value cannot be changed by a human respondent to the Questionnaire.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * An indication, when true, that the value cannot be changed by a human respondent to the Questionnaire.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $readOnly
     * @return $this
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * The maximum number of characters that are permitted in the answer to be considered a "valid" QuestionnaireResponse.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * The maximum number of characters that are permitted in the answer to be considered a "valid" QuestionnaireResponse.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $maxLength
     * @return $this
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * A reference to a value set containing a list of codes representing permitted answers for a "choice" or "open-choice" question.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getAnswerValueSet()
    {
        return $this->answerValueSet;
    }

    /**
     * A reference to a value set containing a list of codes representing permitted answers for a "choice" or "open-choice" question.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $answerValueSet
     * @return $this
     */
    public function setAnswerValueSet($answerValueSet)
    {
        $this->answerValueSet = $answerValueSet;
        return $this;
    }

    /**
     * One of the permitted answers for a "choice" or "open-choice" question.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireAnswerOption[]
     */
    public function getAnswerOption()
    {
        return $this->answerOption;
    }

    /**
     * One of the permitted answers for a "choice" or "open-choice" question.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireAnswerOption $answerOption
     * @return $this
     */
    public function addAnswerOption($answerOption)
    {
        $this->answerOption[] = $answerOption;
        return $this;
    }

    /**
     * One or more values that should be pre-populated in the answer when initially rendering the questionnaire for user input.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireInitial[]
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * One or more values that should be pre-populated in the answer when initially rendering the questionnaire for user input.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireInitial $initial
     * @return $this
     */
    public function addInitial($initial)
    {
        $this->initial[] = $initial;
        return $this;
    }

    /**
     * Text, questions and other groups to be nested beneath a question or group.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireItem[]
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Text, questions and other groups to be nested beneath a question or group.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireItem $item
     * @return $this
     */
    public function addItem($item)
    {
        $this->item[] = $item;
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
            if (isset($data['linkId'])) {
                $this->setLinkId($data['linkId']);
            }
            if (isset($data['definition'])) {
                $this->setDefinition($data['definition']);
            }
            if (isset($data['code'])) {
                if (is_array($data['code'])) {
                    foreach ($data['code'] as $d) {
                        $this->addCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"code" must be array of objects or null, ' . gettype($data['code']) . ' seen.');
                }
            }
            if (isset($data['prefix'])) {
                $this->setPrefix($data['prefix']);
            }
            if (isset($data['text'])) {
                $this->setText($data['text']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['enableWhen'])) {
                if (is_array($data['enableWhen'])) {
                    foreach ($data['enableWhen'] as $d) {
                        $this->addEnableWhen($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"enableWhen" must be array of objects or null, ' . gettype($data['enableWhen']) . ' seen.');
                }
            }
            if (isset($data['enableBehavior'])) {
                $this->setEnableBehavior($data['enableBehavior']);
            }
            if (isset($data['required'])) {
                $this->setRequired($data['required']);
            }
            if (isset($data['repeats'])) {
                $this->setRepeats($data['repeats']);
            }
            if (isset($data['readOnly'])) {
                $this->setReadOnly($data['readOnly']);
            }
            if (isset($data['maxLength'])) {
                $this->setMaxLength($data['maxLength']);
            }
            if (isset($data['answerValueSet'])) {
                $this->setAnswerValueSet($data['answerValueSet']);
            }
            if (isset($data['answerOption'])) {
                if (is_array($data['answerOption'])) {
                    foreach ($data['answerOption'] as $d) {
                        $this->addAnswerOption($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"answerOption" must be array of objects or null, ' . gettype($data['answerOption']) . ' seen.');
                }
            }
            if (isset($data['initial'])) {
                if (is_array($data['initial'])) {
                    foreach ($data['initial'] as $d) {
                        $this->addInitial($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"initial" must be array of objects or null, ' . gettype($data['initial']) . ' seen.');
                }
            }
            if (isset($data['item'])) {
                if (is_array($data['item'])) {
                    foreach ($data['item'] as $d) {
                        $this->addItem($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"item" must be array of objects or null, ' . gettype($data['item']) . ' seen.');
                }
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
        if (isset($this->linkId)) {
            $json['linkId'] = $this->linkId;
        }
        if (isset($this->definition)) {
            $json['definition'] = $this->definition;
        }
        if (0 < count($this->code)) {
            $json['code'] = [];
            foreach ($this->code as $code) {
                $json['code'][] = $code;
            }
        }
        if (isset($this->prefix)) {
            $json['prefix'] = $this->prefix;
        }
        if (isset($this->text)) {
            $json['text'] = $this->text;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->enableWhen)) {
            $json['enableWhen'] = [];
            foreach ($this->enableWhen as $enableWhen) {
                $json['enableWhen'][] = $enableWhen;
            }
        }
        if (isset($this->enableBehavior)) {
            $json['enableBehavior'] = $this->enableBehavior;
        }
        if (isset($this->required)) {
            $json['required'] = $this->required;
        }
        if (isset($this->repeats)) {
            $json['repeats'] = $this->repeats;
        }
        if (isset($this->readOnly)) {
            $json['readOnly'] = $this->readOnly;
        }
        if (isset($this->maxLength)) {
            $json['maxLength'] = $this->maxLength;
        }
        if (isset($this->answerValueSet)) {
            $json['answerValueSet'] = $this->answerValueSet;
        }
        if (0 < count($this->answerOption)) {
            $json['answerOption'] = [];
            foreach ($this->answerOption as $answerOption) {
                $json['answerOption'][] = $answerOption;
            }
        }
        if (0 < count($this->initial)) {
            $json['initial'] = [];
            foreach ($this->initial as $initial) {
                $json['initial'][] = $initial;
            }
        }
        if (0 < count($this->item)) {
            $json['item'] = [];
            foreach ($this->item as $item) {
                $json['item'][] = $item;
            }
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
            $sxe = new \SimpleXMLElement('<QuestionnaireItem xmlns="http://hl7.org/fhir"></QuestionnaireItem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->linkId)) {
            $this->linkId->xmlSerialize(true, $sxe->addChild('linkId'));
        }
        if (isset($this->definition)) {
            $this->definition->xmlSerialize(true, $sxe->addChild('definition'));
        }
        if (0 < count($this->code)) {
            foreach ($this->code as $code) {
                $code->xmlSerialize(true, $sxe->addChild('code'));
            }
        }
        if (isset($this->prefix)) {
            $this->prefix->xmlSerialize(true, $sxe->addChild('prefix'));
        }
        if (isset($this->text)) {
            $this->text->xmlSerialize(true, $sxe->addChild('text'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->enableWhen)) {
            foreach ($this->enableWhen as $enableWhen) {
                $enableWhen->xmlSerialize(true, $sxe->addChild('enableWhen'));
            }
        }
        if (isset($this->enableBehavior)) {
            $this->enableBehavior->xmlSerialize(true, $sxe->addChild('enableBehavior'));
        }
        if (isset($this->required)) {
            $this->required->xmlSerialize(true, $sxe->addChild('required'));
        }
        if (isset($this->repeats)) {
            $this->repeats->xmlSerialize(true, $sxe->addChild('repeats'));
        }
        if (isset($this->readOnly)) {
            $this->readOnly->xmlSerialize(true, $sxe->addChild('readOnly'));
        }
        if (isset($this->maxLength)) {
            $this->maxLength->xmlSerialize(true, $sxe->addChild('maxLength'));
        }
        if (isset($this->answerValueSet)) {
            $this->answerValueSet->xmlSerialize(true, $sxe->addChild('answerValueSet'));
        }
        if (0 < count($this->answerOption)) {
            foreach ($this->answerOption as $answerOption) {
                $answerOption->xmlSerialize(true, $sxe->addChild('answerOption'));
            }
        }
        if (0 < count($this->initial)) {
            foreach ($this->initial as $initial) {
                $initial->xmlSerialize(true, $sxe->addChild('initial'));
            }
        }
        if (0 < count($this->item)) {
            foreach ($this->item as $item) {
                $item->xmlSerialize(true, $sxe->addChild('item'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
