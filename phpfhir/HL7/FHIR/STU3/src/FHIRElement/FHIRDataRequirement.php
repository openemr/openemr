<?php namespace HL7\FHIR\STU3\FHIRElement;

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
class FHIRDataRequirement extends FHIRElement implements \JsonSerializable
{
    /**
     * The type of the required data, specified as the type name of a resource. For profiles, this value is set to the type of the base resource of the profile.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public $type = null;

    /**
     * The profile of the required data, specified as the uri of the profile definition.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public $profile = [];

    /**
     * Indicates that specific elements of the type are referenced by the knowledge module and must be supported by the consumer in order to obtain an effective evaluation. This does not mean that a value is required for this element, only that the consuming system must understand the element and be able to provide values for it if they are available. Note that the value for this element can be a path to allow references to nested elements. In that case, all the elements along the path must be supported.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $mustSupport = [];

    /**
     * Code filters specify additional constraints on the data, specifying the value set of interest for a particular element of the data.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRDataRequirement\FHIRDataRequirementCodeFilter[]
     */
    public $codeFilter = [];

    /**
     * Date filters specify additional constraints on the data in terms of the applicable date range for specific elements.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRDataRequirement\FHIRDataRequirementDateFilter[]
     */
    public $dateFilter = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'DataRequirement';

    /**
     * The type of the required data, specified as the type name of a resource. For profiles, this value is set to the type of the base resource of the profile.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCode
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of the required data, specified as the type name of a resource. For profiles, this value is set to the type of the base resource of the profile.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCode $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The profile of the required data, specified as the uri of the profile definition.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRUri[]
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * The profile of the required data, specified as the uri of the profile definition.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRUri $profile
     * @return $this
     */
    public function addProfile($profile)
    {
        $this->profile[] = $profile;
        return $this;
    }

    /**
     * Indicates that specific elements of the type are referenced by the knowledge module and must be supported by the consumer in order to obtain an effective evaluation. This does not mean that a value is required for this element, only that the consuming system must understand the element and be able to provide values for it if they are available. Note that the value for this element can be a path to allow references to nested elements. In that case, all the elements along the path must be supported.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getMustSupport()
    {
        return $this->mustSupport;
    }

    /**
     * Indicates that specific elements of the type are referenced by the knowledge module and must be supported by the consumer in order to obtain an effective evaluation. This does not mean that a value is required for this element, only that the consuming system must understand the element and be able to provide values for it if they are available. Note that the value for this element can be a path to allow references to nested elements. In that case, all the elements along the path must be supported.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $mustSupport
     * @return $this
     */
    public function addMustSupport($mustSupport)
    {
        $this->mustSupport[] = $mustSupport;
        return $this;
    }

    /**
     * Code filters specify additional constraints on the data, specifying the value set of interest for a particular element of the data.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRDataRequirement\FHIRDataRequirementCodeFilter[]
     */
    public function getCodeFilter()
    {
        return $this->codeFilter;
    }

    /**
     * Code filters specify additional constraints on the data, specifying the value set of interest for a particular element of the data.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRDataRequirement\FHIRDataRequirementCodeFilter $codeFilter
     * @return $this
     */
    public function addCodeFilter($codeFilter)
    {
        $this->codeFilter[] = $codeFilter;
        return $this;
    }

    /**
     * Date filters specify additional constraints on the data in terms of the applicable date range for specific elements.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRDataRequirement\FHIRDataRequirementDateFilter[]
     */
    public function getDateFilter()
    {
        return $this->dateFilter;
    }

    /**
     * Date filters specify additional constraints on the data in terms of the applicable date range for specific elements.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRDataRequirement\FHIRDataRequirementDateFilter $dateFilter
     * @return $this
     */
    public function addDateFilter($dateFilter)
    {
        $this->dateFilter[] = $dateFilter;
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
            if (isset($data['profile'])) {
                if (is_array($data['profile'])) {
                    foreach ($data['profile'] as $d) {
                        $this->addProfile($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"profile" must be array of objects or null, '.gettype($data['profile']).' seen.');
                }
            }
            if (isset($data['mustSupport'])) {
                if (is_array($data['mustSupport'])) {
                    foreach ($data['mustSupport'] as $d) {
                        $this->addMustSupport($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"mustSupport" must be array of objects or null, '.gettype($data['mustSupport']).' seen.');
                }
            }
            if (isset($data['codeFilter'])) {
                if (is_array($data['codeFilter'])) {
                    foreach ($data['codeFilter'] as $d) {
                        $this->addCodeFilter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"codeFilter" must be array of objects or null, '.gettype($data['codeFilter']).' seen.');
                }
            }
            if (isset($data['dateFilter'])) {
                if (is_array($data['dateFilter'])) {
                    foreach ($data['dateFilter'] as $d) {
                        $this->addDateFilter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dateFilter" must be array of objects or null, '.gettype($data['dateFilter']).' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->profile)) {
            $json['profile'] = [];
            foreach ($this->profile as $profile) {
                $json['profile'][] = $profile;
            }
        }
        if (0 < count($this->mustSupport)) {
            $json['mustSupport'] = [];
            foreach ($this->mustSupport as $mustSupport) {
                $json['mustSupport'][] = $mustSupport;
            }
        }
        if (0 < count($this->codeFilter)) {
            $json['codeFilter'] = [];
            foreach ($this->codeFilter as $codeFilter) {
                $json['codeFilter'][] = $codeFilter;
            }
        }
        if (0 < count($this->dateFilter)) {
            $json['dateFilter'] = [];
            foreach ($this->dateFilter as $dateFilter) {
                $json['dateFilter'][] = $dateFilter;
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
            $sxe = new \SimpleXMLElement('<DataRequirement xmlns="http://hl7.org/fhir"></DataRequirement>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->profile)) {
            foreach ($this->profile as $profile) {
                $profile->xmlSerialize(true, $sxe->addChild('profile'));
            }
        }
        if (0 < count($this->mustSupport)) {
            foreach ($this->mustSupport as $mustSupport) {
                $mustSupport->xmlSerialize(true, $sxe->addChild('mustSupport'));
            }
        }
        if (0 < count($this->codeFilter)) {
            foreach ($this->codeFilter as $codeFilter) {
                $codeFilter->xmlSerialize(true, $sxe->addChild('codeFilter'));
            }
        }
        if (0 < count($this->dateFilter)) {
            foreach ($this->dateFilter as $dateFilter) {
                $dateFilter->xmlSerialize(true, $sxe->addChild('dateFilter'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
