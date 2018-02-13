<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * A series of measurements taken by a device, with upper and lower limits. There may be more than one dimension in the data.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRSampledData extends FHIRElement implements \JsonSerializable
{
    /**
     * The base quantity that a measured value of zero represents. In addition, this provides the units of the entire measurement series.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public $origin = null;

    /**
     * The length of time between sampling times, measured in milliseconds.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $period = null;

    /**
     * A correction factor that is applied to the sampled data points before they are added to the origin.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $factor = null;

    /**
     * The lower limit of detection of the measured points. This is needed if any of the data points have the value "L" (lower than detection limit).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $lowerLimit = null;

    /**
     * The upper limit of detection of the measured points. This is needed if any of the data points have the value "U" (higher than detection limit).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $upperLimit = null;

    /**
     * The number of sample points at each time point. If this value is greater than one, then the dimensions will be interlaced - all the sample points for a point in time will be recorded at once.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $dimensions = null;

    /**
     * A series of data points which are decimal values separated by a single space (character u20). The special values "E" (error), "L" (below detection limit) and "U" (above detection limit) can also be used in place of a decimal value.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRSampledDataDataType
     */
    public $data = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SampledData';

    /**
     * The base quantity that a measured value of zero represents. In addition, this provides the units of the entire measurement series.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRQuantity
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * The base quantity that a measured value of zero represents. In addition, this provides the units of the entire measurement series.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRQuantity $origin
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * The length of time between sampling times, measured in milliseconds.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The length of time between sampling times, measured in milliseconds.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * A correction factor that is applied to the sampled data points before they are added to the origin.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * A correction factor that is applied to the sampled data points before they are added to the origin.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $factor
     * @return $this
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;
        return $this;
    }

    /**
     * The lower limit of detection of the measured points. This is needed if any of the data points have the value "L" (lower than detection limit).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getLowerLimit()
    {
        return $this->lowerLimit;
    }

    /**
     * The lower limit of detection of the measured points. This is needed if any of the data points have the value "L" (lower than detection limit).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $lowerLimit
     * @return $this
     */
    public function setLowerLimit($lowerLimit)
    {
        $this->lowerLimit = $lowerLimit;
        return $this;
    }

    /**
     * The upper limit of detection of the measured points. This is needed if any of the data points have the value "U" (higher than detection limit).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getUpperLimit()
    {
        return $this->upperLimit;
    }

    /**
     * The upper limit of detection of the measured points. This is needed if any of the data points have the value "U" (higher than detection limit).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $upperLimit
     * @return $this
     */
    public function setUpperLimit($upperLimit)
    {
        $this->upperLimit = $upperLimit;
        return $this;
    }

    /**
     * The number of sample points at each time point. If this value is greater than one, then the dimensions will be interlaced - all the sample points for a point in time will be recorded at once.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * The number of sample points at each time point. If this value is greater than one, then the dimensions will be interlaced - all the sample points for a point in time will be recorded at once.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $dimensions
     * @return $this
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    /**
     * A series of data points which are decimal values separated by a single space (character u20). The special values "E" (error), "L" (below detection limit) and "U" (above detection limit) can also be used in place of a decimal value.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRSampledDataDataType
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * A series of data points which are decimal values separated by a single space (character u20). The special values "E" (error), "L" (below detection limit) and "U" (above detection limit) can also be used in place of a decimal value.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRSampledDataDataType $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
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
            if (isset($data['origin'])) {
                $this->setOrigin($data['origin']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['factor'])) {
                $this->setFactor($data['factor']);
            }
            if (isset($data['lowerLimit'])) {
                $this->setLowerLimit($data['lowerLimit']);
            }
            if (isset($data['upperLimit'])) {
                $this->setUpperLimit($data['upperLimit']);
            }
            if (isset($data['dimensions'])) {
                $this->setDimensions($data['dimensions']);
            }
            if (isset($data['data'])) {
                $this->setData($data['data']);
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
        if (isset($this->origin)) {
            $json['origin'] = $this->origin;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->factor)) {
            $json['factor'] = $this->factor;
        }
        if (isset($this->lowerLimit)) {
            $json['lowerLimit'] = $this->lowerLimit;
        }
        if (isset($this->upperLimit)) {
            $json['upperLimit'] = $this->upperLimit;
        }
        if (isset($this->dimensions)) {
            $json['dimensions'] = $this->dimensions;
        }
        if (isset($this->data)) {
            $json['data'] = $this->data;
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
            $sxe = new \SimpleXMLElement('<SampledData xmlns="http://hl7.org/fhir"></SampledData>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->origin)) {
            $this->origin->xmlSerialize(true, $sxe->addChild('origin'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->factor)) {
            $this->factor->xmlSerialize(true, $sxe->addChild('factor'));
        }
        if (isset($this->lowerLimit)) {
            $this->lowerLimit->xmlSerialize(true, $sxe->addChild('lowerLimit'));
        }
        if (isset($this->upperLimit)) {
            $this->upperLimit->xmlSerialize(true, $sxe->addChild('upperLimit'));
        }
        if (isset($this->dimensions)) {
            $this->dimensions->xmlSerialize(true, $sxe->addChild('dimensions'));
        }
        if (isset($this->data)) {
            $this->data->xmlSerialize(true, $sxe->addChild('data'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
