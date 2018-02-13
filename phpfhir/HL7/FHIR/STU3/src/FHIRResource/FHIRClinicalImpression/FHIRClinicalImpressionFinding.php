<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRClinicalImpression;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A record of a clinical assessment performed to determine what problem(s) may affect the patient and before planning the treatments or management strategies that are best to manage a patient's condition. Assessments are often 1:1 with a clinical consultation / encounter,  but this varies greatly depending on the clinical workflow. This resource is called "ClinicalImpression" rather than "ClinicalAssessment" to avoid confusion with the recording of assessment tools such as Apgar score.
 */
class FHIRClinicalImpressionFinding extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $itemCodeableConcept = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $itemReference = null;

    /**
     * Which investigations support finding or diagnosis.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $basis = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ClinicalImpression.Finding';

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getItemCodeableConcept()
    {
        return $this->itemCodeableConcept;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $itemCodeableConcept
     * @return $this
     */
    public function setItemCodeableConcept($itemCodeableConcept)
    {
        $this->itemCodeableConcept = $itemCodeableConcept;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getItemReference()
    {
        return $this->itemReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $itemReference
     * @return $this
     */
    public function setItemReference($itemReference)
    {
        $this->itemReference = $itemReference;
        return $this;
    }

    /**
     * Which investigations support finding or diagnosis.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getBasis()
    {
        return $this->basis;
    }

    /**
     * Which investigations support finding or diagnosis.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $basis
     * @return $this
     */
    public function setBasis($basis)
    {
        $this->basis = $basis;
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
            if (isset($data['itemCodeableConcept'])) {
                $this->setItemCodeableConcept($data['itemCodeableConcept']);
            }
            if (isset($data['itemReference'])) {
                $this->setItemReference($data['itemReference']);
            }
            if (isset($data['basis'])) {
                $this->setBasis($data['basis']);
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
        if (isset($this->itemCodeableConcept)) {
            $json['itemCodeableConcept'] = $this->itemCodeableConcept;
        }
        if (isset($this->itemReference)) {
            $json['itemReference'] = $this->itemReference;
        }
        if (isset($this->basis)) {
            $json['basis'] = $this->basis;
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
            $sxe = new \SimpleXMLElement('<ClinicalImpressionFinding xmlns="http://hl7.org/fhir"></ClinicalImpressionFinding>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->itemCodeableConcept)) {
            $this->itemCodeableConcept->xmlSerialize(true, $sxe->addChild('itemCodeableConcept'));
        }
        if (isset($this->itemReference)) {
            $this->itemReference->xmlSerialize(true, $sxe->addChild('itemReference'));
        }
        if (isset($this->basis)) {
            $this->basis->xmlSerialize(true, $sxe->addChild('basis'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
