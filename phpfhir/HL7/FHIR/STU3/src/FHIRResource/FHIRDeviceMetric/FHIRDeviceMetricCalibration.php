<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRDeviceMetric;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Describes a measurement, calculation or setting capability of a medical device.
 */
class FHIRDeviceMetricCalibration extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Describes the type of the calibration method.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDeviceMetricCalibrationType
     */
    public $type = null;

    /**
     * Describes the state of the calibration.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDeviceMetricCalibrationState
     */
    public $state = null;

    /**
     * Describes the time last calibration has been performed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public $time = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'DeviceMetric.Calibration';

    /**
     * Describes the type of the calibration method.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDeviceMetricCalibrationType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Describes the type of the calibration method.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDeviceMetricCalibrationType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Describes the state of the calibration.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDeviceMetricCalibrationState
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Describes the state of the calibration.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDeviceMetricCalibrationState $state
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Describes the time last calibration has been performed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRInstant
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Describes the time last calibration has been performed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRInstant $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;
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
            if (isset($data['state'])) {
                $this->setState($data['state']);
            }
            if (isset($data['time'])) {
                $this->setTime($data['time']);
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
        if (isset($this->state)) {
            $json['state'] = $this->state;
        }
        if (isset($this->time)) {
            $json['time'] = $this->time;
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
            $sxe = new \SimpleXMLElement('<DeviceMetricCalibration xmlns="http://hl7.org/fhir"></DeviceMetricCalibration>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->state)) {
            $this->state->xmlSerialize(true, $sxe->addChild('state'));
        }
        if (isset($this->time)) {
            $this->time->xmlSerialize(true, $sxe->addChild('time'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
