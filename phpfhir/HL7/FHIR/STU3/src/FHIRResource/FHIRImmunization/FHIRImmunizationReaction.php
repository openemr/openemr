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
class FHIRImmunizationReaction extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Date of reaction to the immunization.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * Details of the reaction.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $detail = null;

    /**
     * Self-reported indicator.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $reported = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Immunization.Reaction';

    /**
     * Date of reaction to the immunization.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Date of reaction to the immunization.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Details of the reaction.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Details of the reaction.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $detail
     * @return $this
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
        return $this;
    }

    /**
     * Self-reported indicator.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getReported()
    {
        return $this->reported;
    }

    /**
     * Self-reported indicator.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $reported
     * @return $this
     */
    public function setReported($reported)
    {
        $this->reported = $reported;
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
            if (isset($data['detail'])) {
                $this->setDetail($data['detail']);
            }
            if (isset($data['reported'])) {
                $this->setReported($data['reported']);
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
        if (isset($this->detail)) {
            $json['detail'] = $this->detail;
        }
        if (isset($this->reported)) {
            $json['reported'] = $this->reported;
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
            $sxe = new \SimpleXMLElement('<ImmunizationReaction xmlns="http://hl7.org/fhir"></ImmunizationReaction>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->detail)) {
            $this->detail->xmlSerialize(true, $sxe->addChild('detail'));
        }
        if (isset($this->reported)) {
            $this->reported->xmlSerialize(true, $sxe->addChild('reported'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
