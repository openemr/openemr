<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRHealthcareService;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * The details of a healthcare service available at a location.
 */
class FHIRHealthcareServiceAvailableTime extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Indicates which days of the week are available between the start and end Times.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDaysOfWeek[]
     */
    public $daysOfWeek = [];

    /**
     * Is this always available? (hence times are irrelevant) e.g. 24 hour service.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $allDay = null;

    /**
     * The opening time of day. Note: If the AllDay flag is set, then this time is ignored.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTime
     */
    public $availableStartTime = null;

    /**
     * The closing time of day. Note: If the AllDay flag is set, then this time is ignored.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRTime
     */
    public $availableEndTime = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'HealthcareService.AvailableTime';

    /**
     * Indicates which days of the week are available between the start and end Times.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDaysOfWeek[]
     */
    public function getDaysOfWeek()
    {
        return $this->daysOfWeek;
    }

    /**
     * Indicates which days of the week are available between the start and end Times.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDaysOfWeek $daysOfWeek
     * @return $this
     */
    public function addDaysOfWeek($daysOfWeek)
    {
        $this->daysOfWeek[] = $daysOfWeek;
        return $this;
    }

    /**
     * Is this always available? (hence times are irrelevant) e.g. 24 hour service.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getAllDay()
    {
        return $this->allDay;
    }

    /**
     * Is this always available? (hence times are irrelevant) e.g. 24 hour service.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $allDay
     * @return $this
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;
        return $this;
    }

    /**
     * The opening time of day. Note: If the AllDay flag is set, then this time is ignored.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTime
     */
    public function getAvailableStartTime()
    {
        return $this->availableStartTime;
    }

    /**
     * The opening time of day. Note: If the AllDay flag is set, then this time is ignored.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTime $availableStartTime
     * @return $this
     */
    public function setAvailableStartTime($availableStartTime)
    {
        $this->availableStartTime = $availableStartTime;
        return $this;
    }

    /**
     * The closing time of day. Note: If the AllDay flag is set, then this time is ignored.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRTime
     */
    public function getAvailableEndTime()
    {
        return $this->availableEndTime;
    }

    /**
     * The closing time of day. Note: If the AllDay flag is set, then this time is ignored.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRTime $availableEndTime
     * @return $this
     */
    public function setAvailableEndTime($availableEndTime)
    {
        $this->availableEndTime = $availableEndTime;
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
            if (isset($data['daysOfWeek'])) {
                if (is_array($data['daysOfWeek'])) {
                    foreach ($data['daysOfWeek'] as $d) {
                        $this->addDaysOfWeek($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"daysOfWeek" must be array of objects or null, '.gettype($data['daysOfWeek']).' seen.');
                }
            }
            if (isset($data['allDay'])) {
                $this->setAllDay($data['allDay']);
            }
            if (isset($data['availableStartTime'])) {
                $this->setAvailableStartTime($data['availableStartTime']);
            }
            if (isset($data['availableEndTime'])) {
                $this->setAvailableEndTime($data['availableEndTime']);
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
        if (0 < count($this->daysOfWeek)) {
            $json['daysOfWeek'] = [];
            foreach ($this->daysOfWeek as $daysOfWeek) {
                $json['daysOfWeek'][] = $daysOfWeek;
            }
        }
        if (isset($this->allDay)) {
            $json['allDay'] = $this->allDay;
        }
        if (isset($this->availableStartTime)) {
            $json['availableStartTime'] = $this->availableStartTime;
        }
        if (isset($this->availableEndTime)) {
            $json['availableEndTime'] = $this->availableEndTime;
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
            $sxe = new \SimpleXMLElement('<HealthcareServiceAvailableTime xmlns="http://hl7.org/fhir"></HealthcareServiceAvailableTime>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->daysOfWeek)) {
            foreach ($this->daysOfWeek as $daysOfWeek) {
                $daysOfWeek->xmlSerialize(true, $sxe->addChild('daysOfWeek'));
            }
        }
        if (isset($this->allDay)) {
            $this->allDay->xmlSerialize(true, $sxe->addChild('allDay'));
        }
        if (isset($this->availableStartTime)) {
            $this->availableStartTime->xmlSerialize(true, $sxe->addChild('availableStartTime'));
        }
        if (isset($this->availableEndTime)) {
            $this->availableEndTime->xmlSerialize(true, $sxe->addChild('availableEndTime'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
