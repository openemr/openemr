<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaireResponse;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A structured set of questions and their answers. The questions are ordered and grouped into coherent subsets, corresponding to the structure of the grouping of the questionnaire being responded to.
 */
class FHIRQuestionnaireResponseItem extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The item from the Questionnaire that corresponds to this item in the QuestionnaireResponse resource.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $linkId = null;

    /**
     * A reference to an [[[ElementDefinition]]] that provides the details for the item.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $definition = null;

    /**
     * Text that is displayed above the contents of the group or as the text of the question being answered.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * More specific subject this section's answers are about, details the subject given in QuestionnaireResponse.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The respondent's answer(s) to the question.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaireResponse\FHIRQuestionnaireResponseAnswer[]
     */
    public $answer = [];

    /**
     * Questions or sub-groups nested beneath a question or group.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaireResponse\FHIRQuestionnaireResponseItem[]
     */
    public $item = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'QuestionnaireResponse.Item';

    /**
     * The item from the Questionnaire that corresponds to this item in the QuestionnaireResponse resource.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * The item from the Questionnaire that corresponds to this item in the QuestionnaireResponse resource.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $linkId
     * @return $this
     */
    public function setLinkId($linkId)
    {
        $this->linkId = $linkId;
        return $this;
    }

    /**
     * A reference to an [[[ElementDefinition]]] that provides the details for the item.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * A reference to an [[[ElementDefinition]]] that provides the details for the item.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $definition
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
        return $this;
    }

    /**
     * Text that is displayed above the contents of the group or as the text of the question being answered.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Text that is displayed above the contents of the group or as the text of the question being answered.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * More specific subject this section's answers are about, details the subject given in QuestionnaireResponse.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * More specific subject this section's answers are about, details the subject given in QuestionnaireResponse.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The respondent's answer(s) to the question.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaireResponse\FHIRQuestionnaireResponseAnswer[]
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * The respondent's answer(s) to the question.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaireResponse\FHIRQuestionnaireResponseAnswer $answer
     * @return $this
     */
    public function addAnswer($answer)
    {
        $this->answer[] = $answer;
        return $this;
    }

    /**
     * Questions or sub-groups nested beneath a question or group.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaireResponse\FHIRQuestionnaireResponseItem[]
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Questions or sub-groups nested beneath a question or group.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRQuestionnaireResponse\FHIRQuestionnaireResponseItem $item
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
            if (isset($data['text'])) {
                $this->setText($data['text']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['answer'])) {
                if (is_array($data['answer'])) {
                    foreach ($data['answer'] as $d) {
                        $this->addAnswer($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"answer" must be array of objects or null, '.gettype($data['answer']).' seen.');
                }
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
        if (isset($this->text)) {
            $json['text'] = $this->text;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (0 < count($this->answer)) {
            $json['answer'] = [];
            foreach ($this->answer as $answer) {
                $json['answer'][] = $answer;
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
            $sxe = new \SimpleXMLElement('<QuestionnaireResponseItem xmlns="http://hl7.org/fhir"></QuestionnaireResponseItem>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->linkId)) {
            $this->linkId->xmlSerialize(true, $sxe->addChild('linkId'));
        }
        if (isset($this->definition)) {
            $this->definition->xmlSerialize(true, $sxe->addChild('definition'));
        }
        if (isset($this->text)) {
            $this->text->xmlSerialize(true, $sxe->addChild('text'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (0 < count($this->answer)) {
            foreach ($this->answer as $answer) {
                $answer->xmlSerialize(true, $sxe->addChild('answer'));
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
