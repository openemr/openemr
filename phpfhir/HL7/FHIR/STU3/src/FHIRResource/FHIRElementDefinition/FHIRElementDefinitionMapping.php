<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRElementDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * Captures constraints on each element within the resource, profile, or extension.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRElementDefinitionMapping extends FHIRElement implements \JsonSerializable
{
    /**
     * An internal reference to the definition of a mapping.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public $identity = null;

    /**
     * Identifies the computable language in which mapping.map is expressed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $language = null;

    /**
     * Expresses what part of the target specification corresponds to this element.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $map = null;

    /**
     * Comments that provide information about the mapping or its use.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $comment = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ElementDefinition.Mapping';

    /**
     * An internal reference to the definition of a mapping.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRId
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * An internal reference to the definition of a mapping.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRId $identity
     * @return $this
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
        return $this;
    }

    /**
     * Identifies the computable language in which mapping.map is expressed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Identifies the computable language in which mapping.map is expressed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Expresses what part of the target specification corresponds to this element.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Expresses what part of the target specification corresponds to this element.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $map
     * @return $this
     */
    public function setMap($map)
    {
        $this->map = $map;
        return $this;
    }

    /**
     * Comments that provide information about the mapping or its use.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Comments that provide information about the mapping or its use.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
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
            if (isset($data['identity'])) {
                $this->setIdentity($data['identity']);
            }
            if (isset($data['language'])) {
                $this->setLanguage($data['language']);
            }
            if (isset($data['map'])) {
                $this->setMap($data['map']);
            }
            if (isset($data['comment'])) {
                $this->setComment($data['comment']);
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
        if (isset($this->identity)) {
            $json['identity'] = $this->identity;
        }
        if (isset($this->language)) {
            $json['language'] = $this->language;
        }
        if (isset($this->map)) {
            $json['map'] = $this->map;
        }
        if (isset($this->comment)) {
            $json['comment'] = $this->comment;
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
            $sxe = new \SimpleXMLElement('<ElementDefinitionMapping xmlns="http://hl7.org/fhir"></ElementDefinitionMapping>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identity)) {
            $this->identity->xmlSerialize(true, $sxe->addChild('identity'));
        }
        if (isset($this->language)) {
            $this->language->xmlSerialize(true, $sxe->addChild('language'));
        }
        if (isset($this->map)) {
            $this->map->xmlSerialize(true, $sxe->addChild('map'));
        }
        if (isset($this->comment)) {
            $this->comment->xmlSerialize(true, $sxe->addChild('comment'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
