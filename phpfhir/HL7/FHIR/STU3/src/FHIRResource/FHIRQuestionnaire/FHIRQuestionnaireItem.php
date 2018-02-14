<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A structured set of questions intended to guide the collection of answers from end-users. Questionnaires provide detailed control over order, presentation, phraseology and grouping to allow coherent, consistent data collection.
 */
class FHIRQuestionnaireItem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * An identifier that is unique within the Questionnaire allowing linkage to the equivalent item in a QuestionnaireResponse resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $linkId = null;

    /**
     * A reference to an [[[ElementDefinition]]] that provides the details for the item. If a definition is provided, then the following element values can be inferred from the definition:

* code (ElementDefinition.code)
* type (ElementDefinition.type)
* required (ElementDefinition.min)
* repeats (ElementDefinition.max)
* maxLength (ElementDefinition.maxLength)
* options (ElementDefinition.binding)

Any information provided in these elements on a Questionnaire Item overrides the information from the definition.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $definition = null;

    /**
     * A terminology code that corresponds to this group or question (e.g. a code from LOINC, which defines many questions and answers).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public $code = [];

    /**
     * A short label for a particular group, question or set of display text within the questionnaire used for reference by the individual completing the questionnaire.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $prefix = null;

    /**
     * The name of a section, the text of a question or text content for a display item.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * The type of questionnaire item this is - whether text for display, a grouping of other items or a particular type of data to be captured (string, integer, coded choice, etc.).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuestionnaireItemType
     */
    public $type = null;

    /**
     * A constraint indicating that this item should only be enabled (displayed/allow answers to be captured) when the specified condition is true.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen[]
     */
    public $enableWhen = [];

    /**
     * An indication, if true, that the item must be present in a "completed" QuestionnaireResponse.  If false, the item may be skipped when answering the questionnaire.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $required = null;

    /**
     * An indication, if true, that the item may occur multiple times in the response, collecting multiple answers answers for questions or multiple sets of answers for groups.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $repeats = null;

    /**
     * An indication, when true, that the value cannot be changed by a human respondent to the Questionnaire.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $readOnly = null;

    /**
     * The maximum number of characters that are permitted in the answer to be considered a "valid" QuestionnaireResponse.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $maxLength = null;

    /**
     * A reference to a value set containing a list of codes representing permitted answers for a "choice" or "open-choice" question.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $options = null;

    /**
     * One of the permitted answers for a "choice" or "open-choice" question.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireOption[]
     */
    public $option = [];

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $initialBoolean = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $initialDecimal = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public $initialInteger = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public $initialDate = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $initialDateTime = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTime
     */
    public $initialTime = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $initialString = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $initialUri = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public $initialAttachment = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public $initialCoding = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $initialQuantity = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $initialReference = null;

    /**
     * Text, questions and other groups to be nested beneath a question or group.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireItem[]
     */
    public $item = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Questionnaire.Item';

    /**
     * An identifier that is unique within the Questionnaire allowing linkage to the equivalent item in a QuestionnaireResponse resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * An identifier that is unique within the Questionnaire allowing linkage to the equivalent item in a QuestionnaireResponse resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $linkId
     * @return $this
     */
    public function setLinkId($linkId)
    {
        $this->linkId = $linkId;
        return $this;
    }

    /**
     * A reference to an [[[ElementDefinition]]] that provides the details for the item. If a definition is provided, then the following element values can be inferred from the definition:

* code (ElementDefinition.code)
* type (ElementDefinition.type)
* required (ElementDefinition.min)
* repeats (ElementDefinition.max)
* maxLength (ElementDefinition.maxLength)
* options (ElementDefinition.binding)

Any information provided in these elements on a Questionnaire Item overrides the information from the definition.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * A reference to an [[[ElementDefinition]]] that provides the details for the item. If a definition is provided, then the following element values can be inferred from the definition:

* code (ElementDefinition.code)
* type (ElementDefinition.type)
* required (ElementDefinition.min)
* repeats (ElementDefinition.max)
* maxLength (ElementDefinition.maxLength)
* options (ElementDefinition.binding)

Any information provided in these elements on a Questionnaire Item overrides the information from the definition.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $definition
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
        return $this;
    }

    /**
     * A terminology code that corresponds to this group or question (e.g. a code from LOINC, which defines many questions and answers).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A terminology code that corresponds to this group or question (e.g. a code from LOINC, which defines many questions and answers).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $code
     * @return $this
     */
    public function addCode($code)
    {
        $this->code[] = $code;
        return $this;
    }

    /**
     * A short label for a particular group, question or set of display text within the questionnaire used for reference by the individual completing the questionnaire.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * A short label for a particular group, question or set of display text within the questionnaire used for reference by the individual completing the questionnaire.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * The name of a section, the text of a question or text content for a display item.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * The name of a section, the text of a question or text content for a display item.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * The type of questionnaire item this is - whether text for display, a grouping of other items or a particular type of data to be captured (string, integer, coded choice, etc.).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuestionnaireItemType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of questionnaire item this is - whether text for display, a grouping of other items or a particular type of data to be captured (string, integer, coded choice, etc.).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuestionnaireItemType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A constraint indicating that this item should only be enabled (displayed/allow answers to be captured) when the specified condition is true.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen[]
     */
    public function getEnableWhen()
    {
        return $this->enableWhen;
    }

    /**
     * A constraint indicating that this item should only be enabled (displayed/allow answers to be captured) when the specified condition is true.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireEnableWhen $enableWhen
     * @return $this
     */
    public function addEnableWhen($enableWhen)
    {
        $this->enableWhen[] = $enableWhen;
        return $this;
    }

    /**
     * An indication, if true, that the item must be present in a "completed" QuestionnaireResponse.  If false, the item may be skipped when answering the questionnaire.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * An indication, if true, that the item must be present in a "completed" QuestionnaireResponse.  If false, the item may be skipped when answering the questionnaire.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * An indication, if true, that the item may occur multiple times in the response, collecting multiple answers answers for questions or multiple sets of answers for groups.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getRepeats()
    {
        return $this->repeats;
    }

    /**
     * An indication, if true, that the item may occur multiple times in the response, collecting multiple answers answers for questions or multiple sets of answers for groups.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $repeats
     * @return $this
     */
    public function setRepeats($repeats)
    {
        $this->repeats = $repeats;
        return $this;
    }

    /**
     * An indication, when true, that the value cannot be changed by a human respondent to the Questionnaire.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * An indication, when true, that the value cannot be changed by a human respondent to the Questionnaire.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $readOnly
     * @return $this
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * The maximum number of characters that are permitted in the answer to be considered a "valid" QuestionnaireResponse.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * The maximum number of characters that are permitted in the answer to be considered a "valid" QuestionnaireResponse.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $maxLength
     * @return $this
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * A reference to a value set containing a list of codes representing permitted answers for a "choice" or "open-choice" question.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * A reference to a value set containing a list of codes representing permitted answers for a "choice" or "open-choice" question.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * One of the permitted answers for a "choice" or "open-choice" question.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireOption[]
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * One of the permitted answers for a "choice" or "open-choice" question.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireOption $option
     * @return $this
     */
    public function addOption($option)
    {
        $this->option[] = $option;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getInitialBoolean()
    {
        return $this->initialBoolean;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $initialBoolean
     * @return $this
     */
    public function setInitialBoolean($initialBoolean)
    {
        $this->initialBoolean = $initialBoolean;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getInitialDecimal()
    {
        return $this->initialDecimal;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $initialDecimal
     * @return $this
     */
    public function setInitialDecimal($initialDecimal)
    {
        $this->initialDecimal = $initialDecimal;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInteger
     */
    public function getInitialInteger()
    {
        return $this->initialInteger;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInteger $initialInteger
     * @return $this
     */
    public function setInitialInteger($initialInteger)
    {
        $this->initialInteger = $initialInteger;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public function getInitialDate()
    {
        return $this->initialDate;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDate $initialDate
     * @return $this
     */
    public function setInitialDate($initialDate)
    {
        $this->initialDate = $initialDate;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getInitialDateTime()
    {
        return $this->initialDateTime;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $initialDateTime
     * @return $this
     */
    public function setInitialDateTime($initialDateTime)
    {
        $this->initialDateTime = $initialDateTime;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTime
     */
    public function getInitialTime()
    {
        return $this->initialTime;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTime $initialTime
     * @return $this
     */
    public function setInitialTime($initialTime)
    {
        $this->initialTime = $initialTime;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getInitialString()
    {
        return $this->initialString;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $initialString
     * @return $this
     */
    public function setInitialString($initialString)
    {
        $this->initialString = $initialString;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getInitialUri()
    {
        return $this->initialUri;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $initialUri
     * @return $this
     */
    public function setInitialUri($initialUri)
    {
        $this->initialUri = $initialUri;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAttachment
     */
    public function getInitialAttachment()
    {
        return $this->initialAttachment;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAttachment $initialAttachment
     * @return $this
     */
    public function setInitialAttachment($initialAttachment)
    {
        $this->initialAttachment = $initialAttachment;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding
     */
    public function getInitialCoding()
    {
        return $this->initialCoding;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $initialCoding
     * @return $this
     */
    public function setInitialCoding($initialCoding)
    {
        $this->initialCoding = $initialCoding;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getInitialQuantity()
    {
        return $this->initialQuantity;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $initialQuantity
     * @return $this
     */
    public function setInitialQuantity($initialQuantity)
    {
        $this->initialQuantity = $initialQuantity;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getInitialReference()
    {
        return $this->initialReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $initialReference
     * @return $this
     */
    public function setInitialReference($initialReference)
    {
        $this->initialReference = $initialReference;
        return $this;
    }

    /**
     * Text, questions and other groups to be nested beneath a question or group.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireItem[]
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Text, questions and other groups to be nested beneath a question or group.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireItem $item
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
                    throw new \InvalidArgumentException('"code" must be array of objects or null, '.gettype($data['code']).' seen.');
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
                    throw new \InvalidArgumentException('"enableWhen" must be array of objects or null, '.gettype($data['enableWhen']).' seen.');
                }
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
            if (isset($data['options'])) {
                $this->setOptions($data['options']);
            }
            if (isset($data['option'])) {
                if (is_array($data['option'])) {
                    foreach ($data['option'] as $d) {
                        $this->addOption($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"option" must be array of objects or null, '.gettype($data['option']).' seen.');
                }
            }
            if (isset($data['initialBoolean'])) {
                $this->setInitialBoolean($data['initialBoolean']);
            }
            if (isset($data['initialDecimal'])) {
                $this->setInitialDecimal($data['initialDecimal']);
            }
            if (isset($data['initialInteger'])) {
                $this->setInitialInteger($data['initialInteger']);
            }
            if (isset($data['initialDate'])) {
                $this->setInitialDate($data['initialDate']);
            }
            if (isset($data['initialDateTime'])) {
                $this->setInitialDateTime($data['initialDateTime']);
            }
            if (isset($data['initialTime'])) {
                $this->setInitialTime($data['initialTime']);
            }
            if (isset($data['initialString'])) {
                $this->setInitialString($data['initialString']);
            }
            if (isset($data['initialUri'])) {
                $this->setInitialUri($data['initialUri']);
            }
            if (isset($data['initialAttachment'])) {
                $this->setInitialAttachment($data['initialAttachment']);
            }
            if (isset($data['initialCoding'])) {
                $this->setInitialCoding($data['initialCoding']);
            }
            if (isset($data['initialQuantity'])) {
                $this->setInitialQuantity($data['initialQuantity']);
            }
            if (isset($data['initialReference'])) {
                $this->setInitialReference($data['initialReference']);
            }
            if (isset($data['item'])) {
                if (is_array($data['item'])) {
                    foreach ($data['item'] as $d) {
                        $this->addItem($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"item" must be array of objects or null, '.gettype($data['item']).' seen.');
                }
            }
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
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
        if (isset($this->options)) {
            $json['options'] = $this->options;
        }
        if (0 < count($this->option)) {
            $json['option'] = [];
            foreach ($this->option as $option) {
                $json['option'][] = $option;
            }
        }
        if (isset($this->initialBoolean)) {
            $json['initialBoolean'] = $this->initialBoolean;
        }
        if (isset($this->initialDecimal)) {
            $json['initialDecimal'] = $this->initialDecimal;
        }
        if (isset($this->initialInteger)) {
            $json['initialInteger'] = $this->initialInteger;
        }
        if (isset($this->initialDate)) {
            $json['initialDate'] = $this->initialDate;
        }
        if (isset($this->initialDateTime)) {
            $json['initialDateTime'] = $this->initialDateTime;
        }
        if (isset($this->initialTime)) {
            $json['initialTime'] = $this->initialTime;
        }
        if (isset($this->initialString)) {
            $json['initialString'] = $this->initialString;
        }
        if (isset($this->initialUri)) {
            $json['initialUri'] = $this->initialUri;
        }
        if (isset($this->initialAttachment)) {
            $json['initialAttachment'] = $this->initialAttachment;
        }
        if (isset($this->initialCoding)) {
            $json['initialCoding'] = $this->initialCoding;
        }
        if (isset($this->initialQuantity)) {
            $json['initialQuantity'] = $this->initialQuantity;
        }
        if (isset($this->initialReference)) {
            $json['initialReference'] = $this->initialReference;
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
        if (isset($this->options)) {
            $this->options->xmlSerialize(true, $sxe->addChild('options'));
        }
        if (0 < count($this->option)) {
            foreach ($this->option as $option) {
                $option->xmlSerialize(true, $sxe->addChild('option'));
            }
        }
        if (isset($this->initialBoolean)) {
            $this->initialBoolean->xmlSerialize(true, $sxe->addChild('initialBoolean'));
        }
        if (isset($this->initialDecimal)) {
            $this->initialDecimal->xmlSerialize(true, $sxe->addChild('initialDecimal'));
        }
        if (isset($this->initialInteger)) {
            $this->initialInteger->xmlSerialize(true, $sxe->addChild('initialInteger'));
        }
        if (isset($this->initialDate)) {
            $this->initialDate->xmlSerialize(true, $sxe->addChild('initialDate'));
        }
        if (isset($this->initialDateTime)) {
            $this->initialDateTime->xmlSerialize(true, $sxe->addChild('initialDateTime'));
        }
        if (isset($this->initialTime)) {
            $this->initialTime->xmlSerialize(true, $sxe->addChild('initialTime'));
        }
        if (isset($this->initialString)) {
            $this->initialString->xmlSerialize(true, $sxe->addChild('initialString'));
        }
        if (isset($this->initialUri)) {
            $this->initialUri->xmlSerialize(true, $sxe->addChild('initialUri'));
        }
        if (isset($this->initialAttachment)) {
            $this->initialAttachment->xmlSerialize(true, $sxe->addChild('initialAttachment'));
        }
        if (isset($this->initialCoding)) {
            $this->initialCoding->xmlSerialize(true, $sxe->addChild('initialCoding'));
        }
        if (isset($this->initialQuantity)) {
            $this->initialQuantity->xmlSerialize(true, $sxe->addChild('initialQuantity'));
        }
        if (isset($this->initialReference)) {
            $this->initialReference->xmlSerialize(true, $sxe->addChild('initialReference'));
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
