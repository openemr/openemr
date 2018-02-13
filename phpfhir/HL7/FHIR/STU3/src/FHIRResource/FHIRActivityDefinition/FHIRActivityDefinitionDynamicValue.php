<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRActivityDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource allows for the definition of some activity to be performed, independent of a particular patient, practitioner, or other performance context.
 */
class FHIRActivityDefinitionDynamicValue extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * A brief, natural language description of the intended semantics of the dynamic value.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * The path to the element to be customized. This is the path on the resource that will hold the result of the calculation defined by the expression.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $path = null;

    /**
     * The media type of the language for the expression.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $language = null;

    /**
     * An expression specifying the value of the customized element.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $expression = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ActivityDefinition.DynamicValue';

    /**
     * A brief, natural language description of the intended semantics of the dynamic value.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A brief, natural language description of the intended semantics of the dynamic value.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The path to the element to be customized. This is the path on the resource that will hold the result of the calculation defined by the expression.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * The path to the element to be customized. This is the path on the resource that will hold the result of the calculation defined by the expression.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * The media type of the language for the expression.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * The media type of the language for the expression.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * An expression specifying the value of the customized element.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * An expression specifying the value of the customized element.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
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
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['path'])) {
                $this->setPath($data['path']);
            }
            if (isset($data['language'])) {
                $this->setLanguage($data['language']);
            }
            if (isset($data['expression'])) {
                $this->setExpression($data['expression']);
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
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->path)) {
            $json['path'] = $this->path;
        }
        if (isset($this->language)) {
            $json['language'] = $this->language;
        }
        if (isset($this->expression)) {
            $json['expression'] = $this->expression;
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
            $sxe = new \SimpleXMLElement('<ActivityDefinitionDynamicValue xmlns="http://hl7.org/fhir"></ActivityDefinitionDynamicValue>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->path)) {
            $this->path->xmlSerialize(true, $sxe->addChild('path'));
        }
        if (isset($this->language)) {
            $this->language->xmlSerialize(true, $sxe->addChild('language'));
        }
        if (isset($this->expression)) {
            $this->expression->xmlSerialize(true, $sxe->addChild('expression'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
