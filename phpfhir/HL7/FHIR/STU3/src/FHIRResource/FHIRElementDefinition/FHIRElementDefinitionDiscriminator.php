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
class FHIRElementDefinitionDiscriminator extends FHIRElement implements \JsonSerializable
{
    /**
     * How the element value is interpreted when discrimination is evaluated.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDiscriminatorType
     */
    public $type = null;

    /**
     * A FHIRPath expression, using a restricted subset of FHIRPath, that is used to identify the element on which discrimination is based.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $path = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ElementDefinition.Discriminator';

    /**
     * How the element value is interpreted when discrimination is evaluated.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDiscriminatorType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * How the element value is interpreted when discrimination is evaluated.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDiscriminatorType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A FHIRPath expression, using a restricted subset of FHIRPath, that is used to identify the element on which discrimination is based.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * A FHIRPath expression, using a restricted subset of FHIRPath, that is used to identify the element on which discrimination is based.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['path'])) {
                $this->setPath($data['path']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->path)) {
            $json['path'] = $this->path;
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
            $sxe = new \SimpleXMLElement('<ElementDefinitionDiscriminator xmlns="http://hl7.org/fhir"></ElementDefinitionDiscriminator>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->path)) {
            $this->path->xmlSerialize(true, $sxe->addChild('path'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
