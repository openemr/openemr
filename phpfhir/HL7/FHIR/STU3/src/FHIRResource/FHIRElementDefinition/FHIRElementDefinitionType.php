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
class FHIRElementDefinitionType extends FHIRElement implements \JsonSerializable
{
    /**
     * URL of Data type or Resource that is a(or the) type used for this element. References are URLs that are relative to http://hl7.org/fhir/StructureDefinition e.g. "string" is a reference to http://hl7.org/fhir/StructureDefinition/string. Absolute URLs are only allowed in logical models.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $code = null;

    /**
     * Identifies a profile structure or implementation Guide that SHALL hold for the datatype this element refers to. Can be a local reference - to a contained StructureDefinition, or a reference to another StructureDefinition or Implementation Guide by a canonical URL. When an implementation guide is specified, the resource SHALL conform to at least one profile defined in the implementation guide.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $profile = null;

    /**
     * Identifies a profile structure or implementation Guide that SHALL hold for the target of the reference this element refers to. Can be a local reference - to a contained StructureDefinition, or a reference to another StructureDefinition or Implementation Guide by a canonical URL. When an implementation guide is specified, the resource SHALL conform to at least one profile defined in the implementation guide.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public $targetProfile = null;

    /**
     * If the type is a reference to another resource, how the resource is or can be aggregated - is it a contained resource, or a reference, and if the context is a bundle, is it included in the bundle.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAggregationMode[]
     */
    public $aggregation = [];

    /**
     * Whether this reference needs to be version specific or version independent, or whether either can be used.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReferenceVersionRules
     */
    public $versioning = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ElementDefinition.Type';

    /**
     * URL of Data type or Resource that is a(or the) type used for this element. References are URLs that are relative to http://hl7.org/fhir/StructureDefinition e.g. "string" is a reference to http://hl7.org/fhir/StructureDefinition/string. Absolute URLs are only allowed in logical models.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * URL of Data type or Resource that is a(or the) type used for this element. References are URLs that are relative to http://hl7.org/fhir/StructureDefinition e.g. "string" is a reference to http://hl7.org/fhir/StructureDefinition/string. Absolute URLs are only allowed in logical models.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Identifies a profile structure or implementation Guide that SHALL hold for the datatype this element refers to. Can be a local reference - to a contained StructureDefinition, or a reference to another StructureDefinition or Implementation Guide by a canonical URL. When an implementation guide is specified, the resource SHALL conform to at least one profile defined in the implementation guide.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Identifies a profile structure or implementation Guide that SHALL hold for the datatype this element refers to. Can be a local reference - to a contained StructureDefinition, or a reference to another StructureDefinition or Implementation Guide by a canonical URL. When an implementation guide is specified, the resource SHALL conform to at least one profile defined in the implementation guide.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Identifies a profile structure or implementation Guide that SHALL hold for the target of the reference this element refers to. Can be a local reference - to a contained StructureDefinition, or a reference to another StructureDefinition or Implementation Guide by a canonical URL. When an implementation guide is specified, the resource SHALL conform to at least one profile defined in the implementation guide.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri
     */
    public function getTargetProfile()
    {
        return $this->targetProfile;
    }

    /**
     * Identifies a profile structure or implementation Guide that SHALL hold for the target of the reference this element refers to. Can be a local reference - to a contained StructureDefinition, or a reference to another StructureDefinition or Implementation Guide by a canonical URL. When an implementation guide is specified, the resource SHALL conform to at least one profile defined in the implementation guide.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $targetProfile
     * @return $this
     */
    public function setTargetProfile($targetProfile)
    {
        $this->targetProfile = $targetProfile;
        return $this;
    }

    /**
     * If the type is a reference to another resource, how the resource is or can be aggregated - is it a contained resource, or a reference, and if the context is a bundle, is it included in the bundle.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAggregationMode[]
     */
    public function getAggregation()
    {
        return $this->aggregation;
    }

    /**
     * If the type is a reference to another resource, how the resource is or can be aggregated - is it a contained resource, or a reference, and if the context is a bundle, is it included in the bundle.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAggregationMode $aggregation
     * @return $this
     */
    public function addAggregation($aggregation)
    {
        $this->aggregation[] = $aggregation;
        return $this;
    }

    /**
     * Whether this reference needs to be version specific or version independent, or whether either can be used.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReferenceVersionRules
     */
    public function getVersioning()
    {
        return $this->versioning;
    }

    /**
     * Whether this reference needs to be version specific or version independent, or whether either can be used.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReferenceVersionRules $versioning
     * @return $this
     */
    public function setVersioning($versioning)
    {
        $this->versioning = $versioning;
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
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['profile'])) {
                $this->setProfile($data['profile']);
            }
            if (isset($data['targetProfile'])) {
                $this->setTargetProfile($data['targetProfile']);
            }
            if (isset($data['aggregation'])) {
                if (is_array($data['aggregation'])) {
                    foreach ($data['aggregation'] as $d) {
                        $this->addAggregation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"aggregation" must be array of objects or null, '.gettype($data['aggregation']).' seen.');
                }
            }
            if (isset($data['versioning'])) {
                $this->setVersioning($data['versioning']);
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
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->profile)) {
            $json['profile'] = $this->profile;
        }
        if (isset($this->targetProfile)) {
            $json['targetProfile'] = $this->targetProfile;
        }
        if (0 < count($this->aggregation)) {
            $json['aggregation'] = [];
            foreach ($this->aggregation as $aggregation) {
                $json['aggregation'][] = $aggregation;
            }
        }
        if (isset($this->versioning)) {
            $json['versioning'] = $this->versioning;
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
            $sxe = new \SimpleXMLElement('<ElementDefinitionType xmlns="http://hl7.org/fhir"></ElementDefinitionType>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->profile)) {
            $this->profile->xmlSerialize(true, $sxe->addChild('profile'));
        }
        if (isset($this->targetProfile)) {
            $this->targetProfile->xmlSerialize(true, $sxe->addChild('targetProfile'));
        }
        if (0 < count($this->aggregation)) {
            foreach ($this->aggregation as $aggregation) {
                $aggregation->xmlSerialize(true, $sxe->addChild('aggregation'));
            }
        }
        if (isset($this->versioning)) {
            $this->versioning->xmlSerialize(true, $sxe->addChild('versioning'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
