<?php namespace HL7\FHIR\STU3;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 *
 */

/**
 * Base definition for all elements in a resource.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRElement implements \JsonSerializable
{
    /**
     * May be used to represent additional information that is not part of the basic definition of the element. In order to make the use of extensions safe and manageable, there is a strict set of governance  applied to the definition and use of extensions. Though any implementer is allowed to define an extension, there is a set of requirements that SHALL be met as part of the definition of the extension.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRExtension[]
     */
    public $extension = [];

    /**
     * @var string
     */
    public $id = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Element';

    /**
     * May be used to represent additional information that is not part of the basic definition of the element. In order to make the use of extensions safe and manageable, there is a strict set of governance  applied to the definition and use of extensions. Though any implementer is allowed to define an extension, there is a set of requirements that SHALL be met as part of the definition of the extension.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRExtension[]
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * May be used to represent additional information that is not part of the basic definition of the element. In order to make the use of extensions safe and manageable, there is a strict set of governance  applied to the definition and use of extensions. Though any implementer is allowed to define an extension, there is a set of requirements that SHALL be met as part of the definition of the extension.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRExtension $extension
     * @return $this
     */
    public function addExtension($extension)
    {
        $this->extension[] = $extension;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
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
            if (isset($data['extension'])) {
                if (is_array($data['extension'])) {
                    foreach ($data['extension'] as $d) {
                        $this->addExtension($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"extension" must be array of objects or null, '.gettype($data['extension']).' seen.');
                }
            }
            if (isset($data['id'])) {
                $this->setId($data['id']);
            }
        } else if (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "'.gettype($data).'"');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = [];
        if (0 < count($this->extension)) {
            $json['extension'] = [];
            foreach ($this->extension as $extension) {
                $json['extension'][] = $extension;
            }
        }
        if (isset($this->id)) {
            $json['id'] = $this->id;
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
            $sxe = new \SimpleXMLElement('<Element xmlns="http://hl7.org/fhir"></Element>');
        }
        if (0 < count($this->extension)) {
            foreach ($this->extension as $extension) {
                $extension->xmlSerialize(true, $sxe->addChild('extension'));
            }
        }
        if (isset($this->id)) {
            $idElement = $sxe->addChild('id');
            $idElement->addAttribute('value', (string)$this->id);
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
