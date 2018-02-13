<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * A patient's point-in-time immunization and recommendation (i.e. forecasting a patient's immunization eligibility according to a published schedule) with optional supporting justification.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRImmunizationRecommendation extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A unique identifier assigned to this particular recommendation record.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The patient the recommendations are for.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * Vaccine administration recommendations.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationRecommendation[]
     */
    public $recommendation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ImmunizationRecommendation';

    /**
     * A unique identifier assigned to this particular recommendation record.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A unique identifier assigned to this particular recommendation record.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The patient the recommendations are for.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * The patient the recommendations are for.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * Vaccine administration recommendations.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationRecommendation[]
     */
    public function getRecommendation()
    {
        return $this->recommendation;
    }

    /**
     * Vaccine administration recommendations.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationRecommendation $recommendation
     * @return $this
     */
    public function addRecommendation($recommendation)
    {
        $this->recommendation[] = $recommendation;
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
            if (isset($data['identifier'])) {
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, '.gettype($data['identifier']).' seen.');
                }
            }
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
            }
            if (isset($data['recommendation'])) {
                if (is_array($data['recommendation'])) {
                    foreach ($data['recommendation'] as $d) {
                        $this->addRecommendation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"recommendation" must be array of objects or null, '.gettype($data['recommendation']).' seen.');
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
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
        }
        if (0 < count($this->recommendation)) {
            $json['recommendation'] = [];
            foreach ($this->recommendation as $recommendation) {
                $json['recommendation'][] = $recommendation;
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
            $sxe = new \SimpleXMLElement('<ImmunizationRecommendation xmlns="http://hl7.org/fhir"></ImmunizationRecommendation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (0 < count($this->recommendation)) {
            foreach ($this->recommendation as $recommendation) {
                $recommendation->xmlSerialize(true, $sxe->addChild('recommendation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
