<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRExplanationOfBenefit;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * This resource provides: the claim details; adjudication details from the processing of a Claim; and optionally account balance information, for informing the subscriber of the benefits provided.
 */
class FHIRExplanationOfBenefitDiagnosis extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Sequence of diagnosis which serves to provide a link.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public $sequence = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $diagnosisCodeableConcept = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $diagnosisReference = null;

    /**
     * The type of the Diagnosis, for example: admitting, primary, secondary, discharge.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $type = [];

    /**
     * The package billing code, for example DRG, based on the assigned grouping code system.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $packageCode = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ExplanationOfBenefit.Diagnosis';

    /**
     * Sequence of diagnosis which serves to provide a link.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Sequence of diagnosis which serves to provide a link.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPositiveInt $sequence
     * @return $this
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getDiagnosisCodeableConcept()
    {
        return $this->diagnosisCodeableConcept;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $diagnosisCodeableConcept
     * @return $this
     */
    public function setDiagnosisCodeableConcept($diagnosisCodeableConcept)
    {
        $this->diagnosisCodeableConcept = $diagnosisCodeableConcept;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getDiagnosisReference()
    {
        return $this->diagnosisReference;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $diagnosisReference
     * @return $this
     */
    public function setDiagnosisReference($diagnosisReference)
    {
        $this->diagnosisReference = $diagnosisReference;
        return $this;
    }

    /**
     * The type of the Diagnosis, for example: admitting, primary, secondary, discharge.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of the Diagnosis, for example: admitting, primary, secondary, discharge.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * The package billing code, for example DRG, based on the assigned grouping code system.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getPackageCode()
    {
        return $this->packageCode;
    }

    /**
     * The package billing code, for example DRG, based on the assigned grouping code system.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $packageCode
     * @return $this
     */
    public function setPackageCode($packageCode)
    {
        $this->packageCode = $packageCode;
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
            if (isset($data['sequence'])) {
                $this->setSequence($data['sequence']);
            }
            if (isset($data['diagnosisCodeableConcept'])) {
                $this->setDiagnosisCodeableConcept($data['diagnosisCodeableConcept']);
            }
            if (isset($data['diagnosisReference'])) {
                $this->setDiagnosisReference($data['diagnosisReference']);
            }
            if (isset($data['type'])) {
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, '.gettype($data['type']).' seen.');
                }
            }
            if (isset($data['packageCode'])) {
                $this->setPackageCode($data['packageCode']);
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
        if (isset($this->sequence)) {
            $json['sequence'] = $this->sequence;
        }
        if (isset($this->diagnosisCodeableConcept)) {
            $json['diagnosisCodeableConcept'] = $this->diagnosisCodeableConcept;
        }
        if (isset($this->diagnosisReference)) {
            $json['diagnosisReference'] = $this->diagnosisReference;
        }
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
        }
        if (isset($this->packageCode)) {
            $json['packageCode'] = $this->packageCode;
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
            $sxe = new \SimpleXMLElement('<ExplanationOfBenefitDiagnosis xmlns="http://hl7.org/fhir"></ExplanationOfBenefitDiagnosis>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->sequence)) {
            $this->sequence->xmlSerialize(true, $sxe->addChild('sequence'));
        }
        if (isset($this->diagnosisCodeableConcept)) {
            $this->diagnosisCodeableConcept->xmlSerialize(true, $sxe->addChild('diagnosisCodeableConcept'));
        }
        if (isset($this->diagnosisReference)) {
            $this->diagnosisReference->xmlSerialize(true, $sxe->addChild('diagnosisReference'));
        }
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (isset($this->packageCode)) {
            $this->packageCode->xmlSerialize(true, $sxe->addChild('packageCode'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
