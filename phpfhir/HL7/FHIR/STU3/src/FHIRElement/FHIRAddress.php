<?php namespace HL7\FHIR\STU3\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement;

/**
 * An address expressed using postal conventions (as opposed to GPS or other location definition formats).  This data type may be used to convey addresses for use in delivering mail as well as for visiting locations which might not be valid for mail delivery.  There are a variety of postal address formats defined around the world.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRAddress extends FHIRElement implements \JsonSerializable
{
    /**
     * The purpose of this address.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAddressUse
     */
    public $use = null;

    /**
     * Distinguishes between physical addresses (those you can visit) and mailing addresses (e.g. PO Boxes and care-of addresses). Most addresses are both.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAddressType
     */
    public $type = null;

    /**
     * A full text representation of the address.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * This component contains the house number, apartment number, street name, street direction,  P.O. Box number, delivery hints, and similar address information.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public $line = [];

    /**
     * The name of the city, town, village or other community or delivery center.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $city = null;

    /**
     * The name of the administrative area (county).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $district = null;

    /**
     * Sub-unit of a country with limited sovereignty in a federally organized country. A code may be used if codes are in common use (i.e. US 2 letter state codes).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $state = null;

    /**
     * A postal code designating a region defined by the postal service.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $postalCode = null;

    /**
     * Country - a nation as commonly understood or generally accepted.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $country = null;

    /**
     * Time period when address was/is in use.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Address';

    /**
     * The purpose of this address.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAddressUse
     */
    public function getUse()
    {
        return $this->use;
    }

    /**
     * The purpose of this address.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAddressUse $use
     * @return $this
     */
    public function setUse($use)
    {
        $this->use = $use;
        return $this;
    }

    /**
     * Distinguishes between physical addresses (those you can visit) and mailing addresses (e.g. PO Boxes and care-of addresses). Most addresses are both.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAddressType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Distinguishes between physical addresses (those you can visit) and mailing addresses (e.g. PO Boxes and care-of addresses). Most addresses are both.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAddressType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A full text representation of the address.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * A full text representation of the address.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * This component contains the house number, apartment number, street name, street direction,  P.O. Box number, delivery hints, and similar address information.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString[]
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * This component contains the house number, apartment number, street name, street direction,  P.O. Box number, delivery hints, and similar address information.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $line
     * @return $this
     */
    public function addLine($line)
    {
        $this->line[] = $line;
        return $this;
    }

    /**
     * The name of the city, town, village or other community or delivery center.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * The name of the city, town, village or other community or delivery center.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * The name of the administrative area (county).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * The name of the administrative area (county).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $district
     * @return $this
     */
    public function setDistrict($district)
    {
        $this->district = $district;
        return $this;
    }

    /**
     * Sub-unit of a country with limited sovereignty in a federally organized country. A code may be used if codes are in common use (i.e. US 2 letter state codes).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sub-unit of a country with limited sovereignty in a federally organized country. A code may be used if codes are in common use (i.e. US 2 letter state codes).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $state
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * A postal code designating a region defined by the postal service.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * A postal code designating a region defined by the postal service.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $postalCode
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * Country - a nation as commonly understood or generally accepted.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Country - a nation as commonly understood or generally accepted.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Time period when address was/is in use.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Time period when address was/is in use.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
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
            if (isset($data['use'])) {
                $this->setUse($data['use']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['text'])) {
                $this->setText($data['text']);
            }
            if (isset($data['line'])) {
                if (is_array($data['line'])) {
                    foreach ($data['line'] as $d) {
                        $this->addLine($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"line" must be array of objects or null, '.gettype($data['line']).' seen.');
                }
            }
            if (isset($data['city'])) {
                $this->setCity($data['city']);
            }
            if (isset($data['district'])) {
                $this->setDistrict($data['district']);
            }
            if (isset($data['state'])) {
                $this->setState($data['state']);
            }
            if (isset($data['postalCode'])) {
                $this->setPostalCode($data['postalCode']);
            }
            if (isset($data['country'])) {
                $this->setCountry($data['country']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
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
        if (isset($this->use)) {
            $json['use'] = $this->use;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->text)) {
            $json['text'] = $this->text;
        }
        if (0 < count($this->line)) {
            $json['line'] = [];
            foreach ($this->line as $line) {
                $json['line'][] = $line;
            }
        }
        if (isset($this->city)) {
            $json['city'] = $this->city;
        }
        if (isset($this->district)) {
            $json['district'] = $this->district;
        }
        if (isset($this->state)) {
            $json['state'] = $this->state;
        }
        if (isset($this->postalCode)) {
            $json['postalCode'] = $this->postalCode;
        }
        if (isset($this->country)) {
            $json['country'] = $this->country;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
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
            $sxe = new \SimpleXMLElement('<Address xmlns="http://hl7.org/fhir"></Address>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->use)) {
            $this->use->xmlSerialize(true, $sxe->addChild('use'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->text)) {
            $this->text->xmlSerialize(true, $sxe->addChild('text'));
        }
        if (0 < count($this->line)) {
            foreach ($this->line as $line) {
                $line->xmlSerialize(true, $sxe->addChild('line'));
            }
        }
        if (isset($this->city)) {
            $this->city->xmlSerialize(true, $sxe->addChild('city'));
        }
        if (isset($this->district)) {
            $this->district->xmlSerialize(true, $sxe->addChild('district'));
        }
        if (isset($this->state)) {
            $this->state->xmlSerialize(true, $sxe->addChild('state'));
        }
        if (isset($this->postalCode)) {
            $this->postalCode->xmlSerialize(true, $sxe->addChild('postalCode'));
        }
        if (isset($this->country)) {
            $this->country->xmlSerialize(true, $sxe->addChild('country'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
