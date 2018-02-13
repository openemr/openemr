<?php namespace HL7\FHIR\STU3\FHIRResource\FHIROperationDefinition;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A formal computable definition of an operation (on the RESTful interface) or a named query (using the search interaction).
 */
class FHIROperationDefinitionBinding extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Indicates the degree of conformance expectations associated with this binding - that is, the degree to which the provided value set must be adhered to in the instances.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBindingStrength
     */
    public $strength = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $valueSetUri = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $valueSetReference = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'OperationDefinition.Binding';

    /**
     * Indicates the degree of conformance expectations associated with this binding - that is, the degree to which the provided value set must be adhered to in the instances.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBindingStrength
     */
    public function getStrength()
    {
        return $this->strength;
    }

    /**
     * Indicates the degree of conformance expectations associated with this binding - that is, the degree to which the provided value set must be adhered to in the instances.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBindingStrength $strength
     * @return $this
     */
    public function setStrength($strength)
    {
        $this->strength = $strength;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getValueSetUri()
    {
        return $this->valueSetUri;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $valueSetUri
     * @return $this
     */
    public function setValueSetUri($valueSetUri)
    {
        $this->valueSetUri = $valueSetUri;
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
            if (isset($data['strength'])) {
                $this->setStrength($data['strength']);
            }
            if (isset($data['valueSetUri'])) {
                $this->setValueSetUri($data['valueSetUri']);
            }
            if (isset($data['valueSetReference'])) {
                $this->setValueSetReference($data['valueSetReference']);
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
        if (isset($this->strength)) {
            $json['strength'] = $this->strength;
        }
        if (isset($this->valueSetUri)) {
            $json['valueSetUri'] = $this->valueSetUri;
        }
        if (isset($this->valueSetReference)) {
            $json['valueSetReference'] = $this->valueSetReference;
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
            $sxe = new \SimpleXMLElement('<OperationDefinitionBinding xmlns="http://hl7.org/fhir"></OperationDefinitionBinding>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->strength)) {
            $this->strength->xmlSerialize(true, $sxe->addChild('strength'));
        }
        if (isset($this->valueSetUri)) {
            $this->valueSetUri->xmlSerialize(true, $sxe->addChild('valueSetUri'));
        }
        if (isset($this->valueSetReference)) {
            $this->valueSetReference->xmlSerialize(true, $sxe->addChild('valueSetReference'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
