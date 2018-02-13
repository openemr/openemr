<?php namespace HL7\FHIR\STU3\FHIRResource\FHIREncounter;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * An interaction between a patient and healthcare provider(s) for the purpose of providing healthcare service(s) or assessing the health status of a patient.
 */
class FHIREncounterHospitalization extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Pre-admission identifier.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $preAdmissionIdentifier = null;

    /**
     * The location from which the patient came before admission.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $origin = null;

    /**
     * From where patient was admitted (physician referral, transfer).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $admitSource = null;

    /**
     * Whether this hospitalization is a readmission and why if known.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $reAdmission = null;

    /**
     * Diet preferences reported by the patient.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $dietPreference = [];

    /**
     * Special courtesies (VIP, board member).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $specialCourtesy = [];

    /**
     * Any special requests that have been made for this hospitalization encounter, such as the provision of specific equipment or other things.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $specialArrangement = [];

    /**
     * Location to which the patient is discharged.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $destination = null;

    /**
     * Category or kind of location after discharge.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $dischargeDisposition = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Encounter.Hospitalization';

    /**
     * Pre-admission identifier.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getPreAdmissionIdentifier()
    {
        return $this->preAdmissionIdentifier;
    }

    /**
     * Pre-admission identifier.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $preAdmissionIdentifier
     * @return $this
     */
    public function setPreAdmissionIdentifier($preAdmissionIdentifier)
    {
        $this->preAdmissionIdentifier = $preAdmissionIdentifier;
        return $this;
    }

    /**
     * The location from which the patient came before admission.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * The location from which the patient came before admission.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $origin
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * From where patient was admitted (physician referral, transfer).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getAdmitSource()
    {
        return $this->admitSource;
    }

    /**
     * From where patient was admitted (physician referral, transfer).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $admitSource
     * @return $this
     */
    public function setAdmitSource($admitSource)
    {
        $this->admitSource = $admitSource;
        return $this;
    }

    /**
     * Whether this hospitalization is a readmission and why if known.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getReAdmission()
    {
        return $this->reAdmission;
    }

    /**
     * Whether this hospitalization is a readmission and why if known.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $reAdmission
     * @return $this
     */
    public function setReAdmission($reAdmission)
    {
        $this->reAdmission = $reAdmission;
        return $this;
    }

    /**
     * Diet preferences reported by the patient.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getDietPreference()
    {
        return $this->dietPreference;
    }

    /**
     * Diet preferences reported by the patient.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $dietPreference
     * @return $this
     */
    public function addDietPreference($dietPreference)
    {
        $this->dietPreference[] = $dietPreference;
        return $this;
    }

    /**
     * Special courtesies (VIP, board member).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSpecialCourtesy()
    {
        return $this->specialCourtesy;
    }

    /**
     * Special courtesies (VIP, board member).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $specialCourtesy
     * @return $this
     */
    public function addSpecialCourtesy($specialCourtesy)
    {
        $this->specialCourtesy[] = $specialCourtesy;
        return $this;
    }

    /**
     * Any special requests that have been made for this hospitalization encounter, such as the provision of specific equipment or other things.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSpecialArrangement()
    {
        return $this->specialArrangement;
    }

    /**
     * Any special requests that have been made for this hospitalization encounter, such as the provision of specific equipment or other things.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $specialArrangement
     * @return $this
     */
    public function addSpecialArrangement($specialArrangement)
    {
        $this->specialArrangement[] = $specialArrangement;
        return $this;
    }

    /**
     * Location to which the patient is discharged.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Location to which the patient is discharged.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $destination
     * @return $this
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * Category or kind of location after discharge.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getDischargeDisposition()
    {
        return $this->dischargeDisposition;
    }

    /**
     * Category or kind of location after discharge.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $dischargeDisposition
     * @return $this
     */
    public function setDischargeDisposition($dischargeDisposition)
    {
        $this->dischargeDisposition = $dischargeDisposition;
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
            if (isset($data['preAdmissionIdentifier'])) {
                $this->setPreAdmissionIdentifier($data['preAdmissionIdentifier']);
            }
            if (isset($data['origin'])) {
                $this->setOrigin($data['origin']);
            }
            if (isset($data['admitSource'])) {
                $this->setAdmitSource($data['admitSource']);
            }
            if (isset($data['reAdmission'])) {
                $this->setReAdmission($data['reAdmission']);
            }
            if (isset($data['dietPreference'])) {
                if (is_array($data['dietPreference'])) {
                    foreach ($data['dietPreference'] as $d) {
                        $this->addDietPreference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"dietPreference" must be array of objects or null, '.gettype($data['dietPreference']).' seen.');
                }
            }
            if (isset($data['specialCourtesy'])) {
                if (is_array($data['specialCourtesy'])) {
                    foreach ($data['specialCourtesy'] as $d) {
                        $this->addSpecialCourtesy($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specialCourtesy" must be array of objects or null, '.gettype($data['specialCourtesy']).' seen.');
                }
            }
            if (isset($data['specialArrangement'])) {
                if (is_array($data['specialArrangement'])) {
                    foreach ($data['specialArrangement'] as $d) {
                        $this->addSpecialArrangement($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specialArrangement" must be array of objects or null, '.gettype($data['specialArrangement']).' seen.');
                }
            }
            if (isset($data['destination'])) {
                $this->setDestination($data['destination']);
            }
            if (isset($data['dischargeDisposition'])) {
                $this->setDischargeDisposition($data['dischargeDisposition']);
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
        if (isset($this->preAdmissionIdentifier)) {
            $json['preAdmissionIdentifier'] = $this->preAdmissionIdentifier;
        }
        if (isset($this->origin)) {
            $json['origin'] = $this->origin;
        }
        if (isset($this->admitSource)) {
            $json['admitSource'] = $this->admitSource;
        }
        if (isset($this->reAdmission)) {
            $json['reAdmission'] = $this->reAdmission;
        }
        if (0 < count($this->dietPreference)) {
            $json['dietPreference'] = [];
            foreach ($this->dietPreference as $dietPreference) {
                $json['dietPreference'][] = $dietPreference;
            }
        }
        if (0 < count($this->specialCourtesy)) {
            $json['specialCourtesy'] = [];
            foreach ($this->specialCourtesy as $specialCourtesy) {
                $json['specialCourtesy'][] = $specialCourtesy;
            }
        }
        if (0 < count($this->specialArrangement)) {
            $json['specialArrangement'] = [];
            foreach ($this->specialArrangement as $specialArrangement) {
                $json['specialArrangement'][] = $specialArrangement;
            }
        }
        if (isset($this->destination)) {
            $json['destination'] = $this->destination;
        }
        if (isset($this->dischargeDisposition)) {
            $json['dischargeDisposition'] = $this->dischargeDisposition;
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
            $sxe = new \SimpleXMLElement('<EncounterHospitalization xmlns="http://hl7.org/fhir"></EncounterHospitalization>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->preAdmissionIdentifier)) {
            $this->preAdmissionIdentifier->xmlSerialize(true, $sxe->addChild('preAdmissionIdentifier'));
        }
        if (isset($this->origin)) {
            $this->origin->xmlSerialize(true, $sxe->addChild('origin'));
        }
        if (isset($this->admitSource)) {
            $this->admitSource->xmlSerialize(true, $sxe->addChild('admitSource'));
        }
        if (isset($this->reAdmission)) {
            $this->reAdmission->xmlSerialize(true, $sxe->addChild('reAdmission'));
        }
        if (0 < count($this->dietPreference)) {
            foreach ($this->dietPreference as $dietPreference) {
                $dietPreference->xmlSerialize(true, $sxe->addChild('dietPreference'));
            }
        }
        if (0 < count($this->specialCourtesy)) {
            foreach ($this->specialCourtesy as $specialCourtesy) {
                $specialCourtesy->xmlSerialize(true, $sxe->addChild('specialCourtesy'));
            }
        }
        if (0 < count($this->specialArrangement)) {
            foreach ($this->specialArrangement as $specialArrangement) {
                $specialArrangement->xmlSerialize(true, $sxe->addChild('specialArrangement'));
            }
        }
        if (isset($this->destination)) {
            $this->destination->xmlSerialize(true, $sxe->addChild('destination'));
        }
        if (isset($this->dischargeDisposition)) {
            $this->dischargeDisposition->xmlSerialize(true, $sxe->addChild('dischargeDisposition'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
