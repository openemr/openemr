<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A patient's point-in-time immunization and recommendation (i.e. forecasting a patient's immunization eligibility according to a published schedule) with optional supporting justification.
 */
class FHIRImmunizationRecommendationRecommendation extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The date the immunization recommendation was created.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * Vaccine that pertains to the recommendation.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $vaccineCode = null;

    /**
     * The targeted disease for the recommendation.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $targetDisease = null;

    /**
     * The next recommended dose number (e.g. dose 2 is the next recommended dose).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $doseNumber = null;

    /**
     * Vaccine administration status.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $forecastStatus = null;

    /**
     * Vaccine date recommendations.  For example, earliest date to administer, latest date to administer, etc.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationDateCriterion[]
     */
    public $dateCriterion = [];

    /**
     * Contains information about the protocol under which the vaccine was administered.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationProtocol
     */
    public $protocol = null;

    /**
     * Immunization event history that supports the status and recommendation.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $supportingImmunization = [];

    /**
     * Patient Information that supports the status and recommendation.  This includes patient observations, adverse reactions and allergy/intolerance information.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $supportingPatientInformation = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ImmunizationRecommendation.Recommendation';

    /**
     * The date the immunization recommendation was created.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date the immunization recommendation was created.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Vaccine that pertains to the recommendation.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getVaccineCode()
    {
        return $this->vaccineCode;
    }

    /**
     * Vaccine that pertains to the recommendation.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $vaccineCode
     * @return $this
     */
    public function setVaccineCode($vaccineCode)
    {
        $this->vaccineCode = $vaccineCode;
        return $this;
    }

    /**
     * The targeted disease for the recommendation.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getTargetDisease()
    {
        return $this->targetDisease;
    }

    /**
     * The targeted disease for the recommendation.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $targetDisease
     * @return $this
     */
    public function setTargetDisease($targetDisease)
    {
        $this->targetDisease = $targetDisease;
        return $this;
    }

    /**
     * The next recommended dose number (e.g. dose 2 is the next recommended dose).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getDoseNumber()
    {
        return $this->doseNumber;
    }

    /**
     * The next recommended dose number (e.g. dose 2 is the next recommended dose).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $doseNumber
     * @return $this
     */
    public function setDoseNumber($doseNumber)
    {
        $this->doseNumber = $doseNumber;
        return $this;
    }

    /**
     * Vaccine administration status.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getForecastStatus()
    {
        return $this->forecastStatus;
    }

    /**
     * Vaccine administration status.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $forecastStatus
     * @return $this
     */
    public function setForecastStatus($forecastStatus)
    {
        $this->forecastStatus = $forecastStatus;
        return $this;
    }

    /**
     * Vaccine date recommendations.  For example, earliest date to administer, latest date to administer, etc.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationDateCriterion[]
     */
    public function getDateCriterion()
    {
        return $this->dateCriterion;
    }

    /**
     * Vaccine date recommendations.  For example, earliest date to administer, latest date to administer, etc.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationDateCriterion $dateCriterion
     * @return $this
     */
    public function addDateCriterion($dateCriterion)
    {
        $this->dateCriterion[] = $dateCriterion;
        return $this;
    }

    /**
     * Contains information about the protocol under which the vaccine was administered.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationProtocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Contains information about the protocol under which the vaccine was administered.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRImmunizationRecommendation\FHIRImmunizationRecommendationProtocol $protocol
     * @return $this
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * Immunization event history that supports the status and recommendation.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getSupportingImmunization()
    {
        return $this->supportingImmunization;
    }

    /**
     * Immunization event history that supports the status and recommendation.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $supportingImmunization
     * @return $this
     */
    public function addSupportingImmunization($supportingImmunization)
    {
        $this->supportingImmunization[] = $supportingImmunization;
        return $this;
    }

    /**
     * Patient Information that supports the status and recommendation.  This includes patient observations, adverse reactions and allergy/intolerance information.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getSupportingPatientInformation()
    {
        return $this->supportingPatientInformation;
    }

    /**
     * Patient Information that supports the status and recommendation.  This includes patient observations, adverse reactions and allergy/intolerance information.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $supportingPatientInformation
     * @return $this
     */
    public function addSupportingPatientInformation($supportingPatientInformation)
    {
        $this->supportingPatientInformation[] = $supportingPatientInformation;
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
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['vaccineCode'])) {
                $this->setVaccineCode($data['vaccineCode']);
            }
            if (isset($data['targetDisease'])) {
                $this->setTargetDisease($data['targetDisease']);
            }
            if (isset($data['doseNumber'])) {
                $this->setDoseNumber($data['doseNumber']);
            }
            if (isset($data['forecastStatus'])) {
                $this->setForecastStatus($data['forecastStatus']);
            }
            if (isset($data['dateCriterion'])) {
                if (is_array($data['dateCriterion'])) {
                    foreach ($data['dateCriterion'] as $d) {
                        $this->addDateCriterion($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dateCriterion" must be array of objects or null, '.gettype($data['dateCriterion']).' seen.');
                }
            }
            if (isset($data['protocol'])) {
                $this->setProtocol($data['protocol']);
            }
            if (isset($data['supportingImmunization'])) {
                if (is_array($data['supportingImmunization'])) {
                    foreach ($data['supportingImmunization'] as $d) {
                        $this->addSupportingImmunization($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportingImmunization" must be array of objects or null, '.gettype($data['supportingImmunization']).' seen.');
                }
            }
            if (isset($data['supportingPatientInformation'])) {
                if (is_array($data['supportingPatientInformation'])) {
                    foreach ($data['supportingPatientInformation'] as $d) {
                        $this->addSupportingPatientInformation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"supportingPatientInformation" must be array of objects or null, '.gettype($data['supportingPatientInformation']).' seen.');
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
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (isset($this->vaccineCode)) {
            $json['vaccineCode'] = $this->vaccineCode;
        }
        if (isset($this->targetDisease)) {
            $json['targetDisease'] = $this->targetDisease;
        }
        if (isset($this->doseNumber)) {
            $json['doseNumber'] = $this->doseNumber;
        }
        if (isset($this->forecastStatus)) {
            $json['forecastStatus'] = $this->forecastStatus;
        }
        if (0 < count($this->dateCriterion)) {
            $json['dateCriterion'] = [];
            foreach ($this->dateCriterion as $dateCriterion) {
                $json['dateCriterion'][] = $dateCriterion;
            }
        }
        if (isset($this->protocol)) {
            $json['protocol'] = $this->protocol;
        }
        if (0 < count($this->supportingImmunization)) {
            $json['supportingImmunization'] = [];
            foreach ($this->supportingImmunization as $supportingImmunization) {
                $json['supportingImmunization'][] = $supportingImmunization;
            }
        }
        if (0 < count($this->supportingPatientInformation)) {
            $json['supportingPatientInformation'] = [];
            foreach ($this->supportingPatientInformation as $supportingPatientInformation) {
                $json['supportingPatientInformation'][] = $supportingPatientInformation;
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
            $sxe = new \SimpleXMLElement('<ImmunizationRecommendationRecommendation xmlns="http://hl7.org/fhir"></ImmunizationRecommendationRecommendation>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->vaccineCode)) {
            $this->vaccineCode->xmlSerialize(true, $sxe->addChild('vaccineCode'));
        }
        if (isset($this->targetDisease)) {
            $this->targetDisease->xmlSerialize(true, $sxe->addChild('targetDisease'));
        }
        if (isset($this->doseNumber)) {
            $this->doseNumber->xmlSerialize(true, $sxe->addChild('doseNumber'));
        }
        if (isset($this->forecastStatus)) {
            $this->forecastStatus->xmlSerialize(true, $sxe->addChild('forecastStatus'));
        }
        if (0 < count($this->dateCriterion)) {
            foreach ($this->dateCriterion as $dateCriterion) {
                $dateCriterion->xmlSerialize(true, $sxe->addChild('dateCriterion'));
            }
        }
        if (isset($this->protocol)) {
            $this->protocol->xmlSerialize(true, $sxe->addChild('protocol'));
        }
        if (0 < count($this->supportingImmunization)) {
            foreach ($this->supportingImmunization as $supportingImmunization) {
                $supportingImmunization->xmlSerialize(true, $sxe->addChild('supportingImmunization'));
            }
        }
        if (0 < count($this->supportingPatientInformation)) {
            foreach ($this->supportingPatientInformation as $supportingPatientInformation) {
                $supportingPatientInformation->xmlSerialize(true, $sxe->addChild('supportingPatientInformation'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
