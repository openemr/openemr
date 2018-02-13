<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRValueSet;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A value set specifies a set of codes drawn from one or more code systems.
 */
class FHIRValueSetCompose extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * If a locked date is defined, then the Content Logical Definition must be evaluated using the current version as of the locked date for referenced code system(s) and value set instances where ValueSet.compose.include.version is not defined.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public $lockedDate = null;

    /**
     * Whether inactive codes - codes that are not approved for current use - are in the value set. If inactive = true, inactive codes are to be included in the expansion, if inactive = false, the inactive codes will not be included in the expansion. If absent, the behavior is determined by the implementation, or by the applicable ExpansionProfile (but generally, inactive codes would be expected to be included).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $inactive = null;

    /**
     * Include one or more codes from a code system or other value set(s).
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetInclude[]
     */
    public $include = [];

    /**
     * Exclude one or more codes from the value set based on code system filters and/or other value sets.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetInclude[]
     */
    public $exclude = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ValueSet.Compose';

    /**
     * If a locked date is defined, then the Content Logical Definition must be evaluated using the current version as of the locked date for referenced code system(s) and value set instances where ValueSet.compose.include.version is not defined.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDate
     */
    public function getLockedDate()
    {
        return $this->lockedDate;
    }

    /**
     * If a locked date is defined, then the Content Logical Definition must be evaluated using the current version as of the locked date for referenced code system(s) and value set instances where ValueSet.compose.include.version is not defined.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDate $lockedDate
     * @return $this
     */
    public function setLockedDate($lockedDate)
    {
        $this->lockedDate = $lockedDate;
        return $this;
    }

    /**
     * Whether inactive codes - codes that are not approved for current use - are in the value set. If inactive = true, inactive codes are to be included in the expansion, if inactive = false, the inactive codes will not be included in the expansion. If absent, the behavior is determined by the implementation, or by the applicable ExpansionProfile (but generally, inactive codes would be expected to be included).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getInactive()
    {
        return $this->inactive;
    }

    /**
     * Whether inactive codes - codes that are not approved for current use - are in the value set. If inactive = true, inactive codes are to be included in the expansion, if inactive = false, the inactive codes will not be included in the expansion. If absent, the behavior is determined by the implementation, or by the applicable ExpansionProfile (but generally, inactive codes would be expected to be included).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $inactive
     * @return $this
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
        return $this;
    }

    /**
     * Include one or more codes from a code system or other value set(s).
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetInclude[]
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * Include one or more codes from a code system or other value set(s).
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetInclude $include
     * @return $this
     */
    public function addInclude($include)
    {
        $this->include[] = $include;
        return $this;
    }

    /**
     * Exclude one or more codes from the value set based on code system filters and/or other value sets.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetInclude[]
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * Exclude one or more codes from the value set based on code system filters and/or other value sets.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRValueSet\FHIRValueSetInclude $exclude
     * @return $this
     */
    public function addExclude($exclude)
    {
        $this->exclude[] = $exclude;
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
            if (isset($data['lockedDate'])) {
                $this->setLockedDate($data['lockedDate']);
            }
            if (isset($data['inactive'])) {
                $this->setInactive($data['inactive']);
            }
            if (isset($data['include'])) {
                if (is_array($data['include'])) {
                    foreach ($data['include'] as $d) {
                        $this->addInclude($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"include" must be array of objects or null, '.gettype($data['include']).' seen.');
                }
            }
            if (isset($data['exclude'])) {
                if (is_array($data['exclude'])) {
                    foreach ($data['exclude'] as $d) {
                        $this->addExclude($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"exclude" must be array of objects or null, '.gettype($data['exclude']).' seen.');
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
        if (isset($this->lockedDate)) {
            $json['lockedDate'] = $this->lockedDate;
        }
        if (isset($this->inactive)) {
            $json['inactive'] = $this->inactive;
        }
        if (0 < count($this->include)) {
            $json['include'] = [];
            foreach ($this->include as $include) {
                $json['include'][] = $include;
            }
        }
        if (0 < count($this->exclude)) {
            $json['exclude'] = [];
            foreach ($this->exclude as $exclude) {
                $json['exclude'][] = $exclude;
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
            $sxe = new \SimpleXMLElement('<ValueSetCompose xmlns="http://hl7.org/fhir"></ValueSetCompose>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->lockedDate)) {
            $this->lockedDate->xmlSerialize(true, $sxe->addChild('lockedDate'));
        }
        if (isset($this->inactive)) {
            $this->inactive->xmlSerialize(true, $sxe->addChild('inactive'));
        }
        if (0 < count($this->include)) {
            foreach ($this->include as $include) {
                $include->xmlSerialize(true, $sxe->addChild('include'));
            }
        }
        if (0 < count($this->exclude)) {
            foreach ($this->exclude as $exclude) {
                $exclude->xmlSerialize(true, $sxe->addChild('exclude'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
