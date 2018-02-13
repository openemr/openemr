<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRDataRequirement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * Describes a required data item for evaluation in terms of the type of data, and optional code or date-based filters of the data.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRDataRequirementCodeFilter extends FHIRElement implements \JsonSerializable
{
    /**
     * The code-valued attribute of the filter. The specified path must be resolvable from the type of the required data. The path is allowed to contain qualifiers (.) to traverse sub-elements, as well as indexers ([x]) to traverse multiple-cardinality sub-elements. Note that the index must be an integer constant. The path must resolve to an element of type code, Coding, or CodeableConcept.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $path = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $valueSetString = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $valueSetReference = null;

    /**
     * The codes for the code filter. Only one of valueSet, valueCode, valueCoding, or valueCodeableConcept may be specified. If values are given, the filter will return only those data items for which the code-valued attribute specified by the path has a value that is one of the specified codes.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode[]
     */
    public $valueCode = [];

    /**
     * The Codings for the code filter. Only one of valueSet, valueCode, valueConding, or valueCodeableConcept may be specified. If values are given, the filter will return only those data items for which the code-valued attribute specified by the path has a value that is one of the specified Codings.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public $valueCoding = [];

    /**
     * The CodeableConcepts for the code filter. Only one of valueSet, valueCode, valueConding, or valueCodeableConcept may be specified. If values are given, the filter will return only those data items for which the code-valued attribute specified by the path has a value that is one of the specified CodeableConcepts.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $valueCodeableConcept = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'DataRequirement.CodeFilter';

    /**
     * The code-valued attribute of the filter. The specified path must be resolvable from the type of the required data. The path is allowed to contain qualifiers (.) to traverse sub-elements, as well as indexers ([x]) to traverse multiple-cardinality sub-elements. Note that the index must be an integer constant. The path must resolve to an element of type code, Coding, or CodeableConcept.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * The code-valued attribute of the filter. The specified path must be resolvable from the type of the required data. The path is allowed to contain qualifiers (.) to traverse sub-elements, as well as indexers ([x]) to traverse multiple-cardinality sub-elements. Note that the index must be an integer constant. The path must resolve to an element of type code, Coding, or CodeableConcept.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getValueSetString()
    {
        return $this->valueSetString;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $valueSetString
     * @return $this
     */
    public function setValueSetString($valueSetString)
    {
        $this->valueSetString = $valueSetString;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getValueSetReference()
    {
        return $this->valueSetReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $valueSetReference
     * @return $this
     */
    public function setValueSetReference($valueSetReference)
    {
        $this->valueSetReference = $valueSetReference;
        return $this;
    }

    /**
     * The codes for the code filter. Only one of valueSet, valueCode, valueCoding, or valueCodeableConcept may be specified. If values are given, the filter will return only those data items for which the code-valued attribute specified by the path has a value that is one of the specified codes.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode[]
     */
    public function getValueCode()
    {
        return $this->valueCode;
    }

    /**
     * The codes for the code filter. Only one of valueSet, valueCode, valueCoding, or valueCodeableConcept may be specified. If values are given, the filter will return only those data items for which the code-valued attribute specified by the path has a value that is one of the specified codes.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $valueCode
     * @return $this
     */
    public function addValueCode($valueCode)
    {
        $this->valueCode[] = $valueCode;
        return $this;
    }

    /**
     * The Codings for the code filter. Only one of valueSet, valueCode, valueConding, or valueCodeableConcept may be specified. If values are given, the filter will return only those data items for which the code-valued attribute specified by the path has a value that is one of the specified Codings.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCoding[]
     */
    public function getValueCoding()
    {
        return $this->valueCoding;
    }

    /**
     * The Codings for the code filter. Only one of valueSet, valueCode, valueConding, or valueCodeableConcept may be specified. If values are given, the filter will return only those data items for which the code-valued attribute specified by the path has a value that is one of the specified Codings.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCoding $valueCoding
     * @return $this
     */
    public function addValueCoding($valueCoding)
    {
        $this->valueCoding[] = $valueCoding;
        return $this;
    }

    /**
     * The CodeableConcepts for the code filter. Only one of valueSet, valueCode, valueConding, or valueCodeableConcept may be specified. If values are given, the filter will return only those data items for which the code-valued attribute specified by the path has a value that is one of the specified CodeableConcepts.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getValueCodeableConcept()
    {
        return $this->valueCodeableConcept;
    }

    /**
     * The CodeableConcepts for the code filter. Only one of valueSet, valueCode, valueConding, or valueCodeableConcept may be specified. If values are given, the filter will return only those data items for which the code-valued attribute specified by the path has a value that is one of the specified CodeableConcepts.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $valueCodeableConcept
     * @return $this
     */
    public function addValueCodeableConcept($valueCodeableConcept)
    {
        $this->valueCodeableConcept[] = $valueCodeableConcept;
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
            if (isset($data['path'])) {
                $this->setPath($data['path']);
            }
            if (isset($data['valueSetString'])) {
                $this->setValueSetString($data['valueSetString']);
            }
            if (isset($data['valueSetReference'])) {
                $this->setValueSetReference($data['valueSetReference']);
            }
            if (isset($data['valueCode'])) {
                if (is_array($data['valueCode'])) {
                    foreach ($data['valueCode'] as $d) {
                        $this->addValueCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"valueCode" must be array of objects or null, '.gettype($data['valueCode']).' seen.');
                }
            }
            if (isset($data['valueCoding'])) {
                if (is_array($data['valueCoding'])) {
                    foreach ($data['valueCoding'] as $d) {
                        $this->addValueCoding($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"valueCoding" must be array of objects or null, '.gettype($data['valueCoding']).' seen.');
                }
            }
            if (isset($data['valueCodeableConcept'])) {
                if (is_array($data['valueCodeableConcept'])) {
                    foreach ($data['valueCodeableConcept'] as $d) {
                        $this->addValueCodeableConcept($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"valueCodeableConcept" must be array of objects or null, '.gettype($data['valueCodeableConcept']).' seen.');
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
        if (isset($this->path)) {
            $json['path'] = $this->path;
        }
        if (isset($this->valueSetString)) {
            $json['valueSetString'] = $this->valueSetString;
        }
        if (isset($this->valueSetReference)) {
            $json['valueSetReference'] = $this->valueSetReference;
        }
        if (0 < count($this->valueCode)) {
            $json['valueCode'] = [];
            foreach ($this->valueCode as $valueCode) {
                $json['valueCode'][] = $valueCode;
            }
        }
        if (0 < count($this->valueCoding)) {
            $json['valueCoding'] = [];
            foreach ($this->valueCoding as $valueCoding) {
                $json['valueCoding'][] = $valueCoding;
            }
        }
        if (0 < count($this->valueCodeableConcept)) {
            $json['valueCodeableConcept'] = [];
            foreach ($this->valueCodeableConcept as $valueCodeableConcept) {
                $json['valueCodeableConcept'][] = $valueCodeableConcept;
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
            $sxe = new \SimpleXMLElement('<DataRequirementCodeFilter xmlns="http://hl7.org/fhir"></DataRequirementCodeFilter>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->path)) {
            $this->path->xmlSerialize(true, $sxe->addChild('path'));
        }
        if (isset($this->valueSetString)) {
            $this->valueSetString->xmlSerialize(true, $sxe->addChild('valueSetString'));
        }
        if (isset($this->valueSetReference)) {
            $this->valueSetReference->xmlSerialize(true, $sxe->addChild('valueSetReference'));
        }
        if (0 < count($this->valueCode)) {
            foreach ($this->valueCode as $valueCode) {
                $valueCode->xmlSerialize(true, $sxe->addChild('valueCode'));
            }
        }
        if (0 < count($this->valueCoding)) {
            foreach ($this->valueCoding as $valueCoding) {
                $valueCoding->xmlSerialize(true, $sxe->addChild('valueCoding'));
            }
        }
        if (0 < count($this->valueCodeableConcept)) {
            foreach ($this->valueCodeableConcept as $valueCodeableConcept) {
                $valueCodeableConcept->xmlSerialize(true, $sxe->addChild('valueCodeableConcept'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
