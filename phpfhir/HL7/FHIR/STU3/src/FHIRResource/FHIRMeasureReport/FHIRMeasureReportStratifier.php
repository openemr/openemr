<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * The MeasureReport resource contains the results of evaluating a measure.
 */
class FHIRMeasureReportStratifier extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The identifier of this stratifier, as defined in the measure definition.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * This element contains the results for a single stratum within the stratifier. For example, when stratifying on administrative gender, there will be four strata, one for each possible gender value.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportStratum[]
     */
    public $stratum = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'MeasureReport.Stratifier';

    /**
     * The identifier of this stratifier, as defined in the measure definition.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The identifier of this stratifier, as defined in the measure definition.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * This element contains the results for a single stratum within the stratifier. For example, when stratifying on administrative gender, there will be four strata, one for each possible gender value.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportStratum[]
     */
    public function getStratum()
    {
        return $this->stratum;
    }

    /**
     * This element contains the results for a single stratum within the stratifier. For example, when stratifying on administrative gender, there will be four strata, one for each possible gender value.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRMeasureReport\FHIRMeasureReportStratum $stratum
     * @return $this
     */
    public function addStratum($stratum)
    {
        $this->stratum[] = $stratum;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['stratum'])) {
                if (is_array($data['stratum'])) {
                    foreach ($data['stratum'] as $d) {
                        $this->addStratum($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"stratum" must be array of objects or null, '.gettype($data['stratum']).' seen.');
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (0 < count($this->stratum)) {
            $json['stratum'] = [];
            foreach ($this->stratum as $stratum) {
                $json['stratum'][] = $stratum;
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
            $sxe = new \SimpleXMLElement('<MeasureReportStratifier xmlns="http://hl7.org/fhir"></MeasureReportStratifier>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (0 < count($this->stratum)) {
            foreach ($this->stratum as $stratum) {
                $stratum->xmlSerialize(true, $sxe->addChild('stratum'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
