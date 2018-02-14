<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRImmunization;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Describes the event of a patient being administered a vaccination or a record of a vaccination as reported by a patient, a clinician or another party and may include vaccine reaction information and what vaccination protocol was followed.
 */
class FHIRImmunizationVaccinationProtocol extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Nominal position in a series.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $doseSequence = null;

    /**
     * Contains the description about the protocol under which the vaccine was administered.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Indicates the authority who published the protocol.  E.g. ACIP.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $authority = null;

    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $series = null;

    /**
     * The recommended number of doses to achieve immunity.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $seriesDoses = null;

    /**
     * The targeted disease.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $targetDisease = [];

    /**
     * Indicates if the immunization event should "count" against  the protocol.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $doseStatus = null;

    /**
     * Provides an explanation as to why an immunization event should or should not count against the protocol.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $doseStatusReason = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Immunization.VaccinationProtocol';

    /**
     * Nominal position in a series.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getDoseSequence()
    {
        return $this->doseSequence;
    }

    /**
     * Nominal position in a series.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $doseSequence
     * @return $this
     */
    public function setDoseSequence($doseSequence)
    {
        $this->doseSequence = $doseSequence;
        return $this;
    }

    /**
     * Contains the description about the protocol under which the vaccine was administered.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Contains the description about the protocol under which the vaccine was administered.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Indicates the authority who published the protocol.  E.g. ACIP.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * Indicates the authority who published the protocol.  E.g. ACIP.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $authority
     * @return $this
     */
    public function setAuthority($authority)
    {
        $this->authority = $authority;
        return $this;
    }

    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * One possible path to achieve presumed immunity against a disease - within the context of an authority.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $series
     * @return $this
     */
    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    /**
     * The recommended number of doses to achieve immunity.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getSeriesDoses()
    {
        return $this->seriesDoses;
    }

    /**
     * The recommended number of doses to achieve immunity.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $seriesDoses
     * @return $this
     */
    public function setSeriesDoses($seriesDoses)
    {
        $this->seriesDoses = $seriesDoses;
        return $this;
    }

    /**
     * The targeted disease.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getTargetDisease()
    {
        return $this->targetDisease;
    }

    /**
     * The targeted disease.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $targetDisease
     * @return $this
     */
    public function addTargetDisease($targetDisease)
    {
        $this->targetDisease[] = $targetDisease;
        return $this;
    }

    /**
     * Indicates if the immunization event should "count" against  the protocol.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getDoseStatus()
    {
        return $this->doseStatus;
    }

    /**
     * Indicates if the immunization event should "count" against  the protocol.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $doseStatus
     * @return $this
     */
    public function setDoseStatus($doseStatus)
    {
        $this->doseStatus = $doseStatus;
        return $this;
    }

    /**
     * Provides an explanation as to why an immunization event should or should not count against the protocol.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getDoseStatusReason()
    {
        return $this->doseStatusReason;
    }

    /**
     * Provides an explanation as to why an immunization event should or should not count against the protocol.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $doseStatusReason
     * @return $this
     */
    public function setDoseStatusReason($doseStatusReason)
    {
        $this->doseStatusReason = $doseStatusReason;
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
            if (isset($data['doseSequence'])) {
                $this->setDoseSequence($data['doseSequence']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['authority'])) {
                $this->setAuthority($data['authority']);
            }
            if (isset($data['series'])) {
                $this->setSeries($data['series']);
            }
            if (isset($data['seriesDoses'])) {
                $this->setSeriesDoses($data['seriesDoses']);
            }
            if (isset($data['targetDisease'])) {
                if (is_array($data['targetDisease'])) {
                    foreach ($data['targetDisease'] as $d) {
                        $this->addTargetDisease($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"targetDisease" must be array of objects or null, '.gettype($data['targetDisease']).' seen.');
                }
            }
            if (isset($data['doseStatus'])) {
                $this->setDoseStatus($data['doseStatus']);
            }
            if (isset($data['doseStatusReason'])) {
                $this->setDoseStatusReason($data['doseStatusReason']);
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
        if (isset($this->doseSequence)) {
            $json['doseSequence'] = $this->doseSequence;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->authority)) {
            $json['authority'] = $this->authority;
        }
        if (isset($this->series)) {
            $json['series'] = $this->series;
        }
        if (isset($this->seriesDoses)) {
            $json['seriesDoses'] = $this->seriesDoses;
        }
        if (0 < count($this->targetDisease)) {
            $json['targetDisease'] = [];
            foreach ($this->targetDisease as $targetDisease) {
                $json['targetDisease'][] = $targetDisease;
            }
        }
        if (isset($this->doseStatus)) {
            $json['doseStatus'] = $this->doseStatus;
        }
        if (isset($this->doseStatusReason)) {
            $json['doseStatusReason'] = $this->doseStatusReason;
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
            $sxe = new \SimpleXMLElement('<ImmunizationVaccinationProtocol xmlns="http://hl7.org/fhir"></ImmunizationVaccinationProtocol>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->doseSequence)) {
            $this->doseSequence->xmlSerialize(true, $sxe->addChild('doseSequence'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->authority)) {
            $this->authority->xmlSerialize(true, $sxe->addChild('authority'));
        }
        if (isset($this->series)) {
            $this->series->xmlSerialize(true, $sxe->addChild('series'));
        }
        if (isset($this->seriesDoses)) {
            $this->seriesDoses->xmlSerialize(true, $sxe->addChild('seriesDoses'));
        }
        if (0 < count($this->targetDisease)) {
            foreach ($this->targetDisease as $targetDisease) {
                $targetDisease->xmlSerialize(true, $sxe->addChild('targetDisease'));
            }
        }
        if (isset($this->doseStatus)) {
            $this->doseStatus->xmlSerialize(true, $sxe->addChild('doseStatus'));
        }
        if (isset($this->doseStatusReason)) {
            $this->doseStatusReason->xmlSerialize(true, $sxe->addChild('doseStatusReason'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
