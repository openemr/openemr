<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRImagingManifest;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A text description of the DICOM SOP instances selected in the ImagingManifest; or the reason for, or significance of, the selection.
 */
class FHIRImagingManifestInstance extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * SOP class UID of the selected instance.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIROid
     */
    public $sopClass = null;

    /**
     * SOP Instance UID of the selected instance.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIROid
     */
    public $uid = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ImagingManifest.Instance';

    /**
     * SOP class UID of the selected instance.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIROid
     */
    public function getSopClass()
    {
        return $this->sopClass;
    }

    /**
     * SOP class UID of the selected instance.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIROid $sopClass
     * @return $this
     */
    public function setSopClass($sopClass)
    {
        $this->sopClass = $sopClass;
        return $this;
    }

    /**
     * SOP Instance UID of the selected instance.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIROid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * SOP Instance UID of the selected instance.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIROid $uid
     * @return $this
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
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
            if (isset($data['sopClass'])) {
                $this->setSopClass($data['sopClass']);
            }
            if (isset($data['uid'])) {
                $this->setUid($data['uid']);
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
        if (isset($this->sopClass)) {
            $json['sopClass'] = $this->sopClass;
        }
        if (isset($this->uid)) {
            $json['uid'] = $this->uid;
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
            $sxe = new \SimpleXMLElement('<ImagingManifestInstance xmlns="http://hl7.org/fhir"></ImagingManifestInstance>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->sopClass)) {
            $this->sopClass->xmlSerialize(true, $sxe->addChild('sopClass'));
        }
        if (isset($this->uid)) {
            $this->uid->xmlSerialize(true, $sxe->addChild('uid'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
