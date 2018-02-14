<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRLocation;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * Details and position information for a physical place where services are provided  and resources and participants may be stored, found, contained or accommodated.
 */
class FHIRLocationPosition extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Longitude. The value domain and the interpretation are the same as for the text of the longitude element in KML (see notes below).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $longitude = null;

    /**
     * Latitude. The value domain and the interpretation are the same as for the text of the latitude element in KML (see notes below).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $latitude = null;

    /**
     * Altitude. The value domain and the interpretation are the same as for the text of the altitude element in KML (see notes below).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public $altitude = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Location.Position';

    /**
     * Longitude. The value domain and the interpretation are the same as for the text of the longitude element in KML (see notes below).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Longitude. The value domain and the interpretation are the same as for the text of the longitude element in KML (see notes below).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $longitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * Latitude. The value domain and the interpretation are the same as for the text of the latitude element in KML (see notes below).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Latitude. The value domain and the interpretation are the same as for the text of the latitude element in KML (see notes below).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * Altitude. The value domain and the interpretation are the same as for the text of the altitude element in KML (see notes below).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDecimal
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * Altitude. The value domain and the interpretation are the same as for the text of the altitude element in KML (see notes below).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDecimal $altitude
     * @return $this
     */
    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;
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
            if (isset($data['longitude'])) {
                $this->setLongitude($data['longitude']);
            }
            if (isset($data['latitude'])) {
                $this->setLatitude($data['latitude']);
            }
            if (isset($data['altitude'])) {
                $this->setAltitude($data['altitude']);
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
        if (isset($this->longitude)) {
            $json['longitude'] = $this->longitude;
        }
        if (isset($this->latitude)) {
            $json['latitude'] = $this->latitude;
        }
        if (isset($this->altitude)) {
            $json['altitude'] = $this->altitude;
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
            $sxe = new \SimpleXMLElement('<LocationPosition xmlns="http://hl7.org/fhir"></LocationPosition>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->longitude)) {
            $this->longitude->xmlSerialize(true, $sxe->addChild('longitude'));
        }
        if (isset($this->latitude)) {
            $this->latitude->xmlSerialize(true, $sxe->addChild('latitude'));
        }
        if (isset($this->altitude)) {
            $this->altitude->xmlSerialize(true, $sxe->addChild('altitude'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
