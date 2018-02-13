<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRPatient;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Demographics and other administrative information about an individual or animal receiving care or other health-related services.
 */
class FHIRPatientAnimal extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Identifies the high level taxonomic categorization of the kind of animal.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $species = null;

    /**
     * Identifies the detailed categorization of the kind of animal.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $breed = null;

    /**
     * Indicates the current state of the animal's reproductive organs.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $genderStatus = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Patient.Animal';

    /**
     * Identifies the high level taxonomic categorization of the kind of animal.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getSpecies()
    {
        return $this->species;
    }

    /**
     * Identifies the high level taxonomic categorization of the kind of animal.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $species
     * @return $this
     */
    public function setSpecies($species)
    {
        $this->species = $species;
        return $this;
    }

    /**
     * Identifies the detailed categorization of the kind of animal.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getBreed()
    {
        return $this->breed;
    }

    /**
     * Identifies the detailed categorization of the kind of animal.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $breed
     * @return $this
     */
    public function setBreed($breed)
    {
        $this->breed = $breed;
        return $this;
    }

    /**
     * Indicates the current state of the animal's reproductive organs.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getGenderStatus()
    {
        return $this->genderStatus;
    }

    /**
     * Indicates the current state of the animal's reproductive organs.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $genderStatus
     * @return $this
     */
    public function setGenderStatus($genderStatus)
    {
        $this->genderStatus = $genderStatus;
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
            if (isset($data['species'])) {
                $this->setSpecies($data['species']);
            }
            if (isset($data['breed'])) {
                $this->setBreed($data['breed']);
            }
            if (isset($data['genderStatus'])) {
                $this->setGenderStatus($data['genderStatus']);
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
        if (isset($this->species)) {
            $json['species'] = $this->species;
        }
        if (isset($this->breed)) {
            $json['breed'] = $this->breed;
        }
        if (isset($this->genderStatus)) {
            $json['genderStatus'] = $this->genderStatus;
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
            $sxe = new \SimpleXMLElement('<PatientAnimal xmlns="http://hl7.org/fhir"></PatientAnimal>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->species)) {
            $this->species->xmlSerialize(true, $sxe->addChild('species'));
        }
        if (isset($this->breed)) {
            $this->breed->xmlSerialize(true, $sxe->addChild('breed'));
        }
        if (isset($this->genderStatus)) {
            $this->genderStatus->xmlSerialize(true, $sxe->addChild('genderStatus'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
