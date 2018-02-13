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
class FHIRImagingManifestStudy extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Study instance UID of the SOP instances in the selection.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIROid
     */
    public $uid = null;

    /**
     * Reference to the Imaging Study in FHIR form.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $imagingStudy = null;

    /**
     * The network service providing access (e.g., query, view, or retrieval) for the study. See implementation notes for information about using DICOM endpoints. A study-level endpoint applies to each series in the study, unless overridden by a series-level endpoint with the same Endpoint.type.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $endpoint = [];

    /**
     * Series identity and locating information of the DICOM SOP instances in the selection.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRImagingManifest\FHIRImagingManifestSeries[]
     */
    public $series = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ImagingManifest.Study';

    /**
     * Study instance UID of the SOP instances in the selection.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIROid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Study instance UID of the SOP instances in the selection.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIROid $uid
     * @return $this
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * Reference to the Imaging Study in FHIR form.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getImagingStudy()
    {
        return $this->imagingStudy;
    }

    /**
     * Reference to the Imaging Study in FHIR form.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $imagingStudy
     * @return $this
     */
    public function setImagingStudy($imagingStudy)
    {
        $this->imagingStudy = $imagingStudy;
        return $this;
    }

    /**
     * The network service providing access (e.g., query, view, or retrieval) for the study. See implementation notes for information about using DICOM endpoints. A study-level endpoint applies to each series in the study, unless overridden by a series-level endpoint with the same Endpoint.type.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * The network service providing access (e.g., query, view, or retrieval) for the study. See implementation notes for information about using DICOM endpoints. A study-level endpoint applies to each series in the study, unless overridden by a series-level endpoint with the same Endpoint.type.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $endpoint
     * @return $this
     */
    public function addEndpoint($endpoint)
    {
        $this->endpoint[] = $endpoint;
        return $this;
    }

    /**
     * Series identity and locating information of the DICOM SOP instances in the selection.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRImagingManifest\FHIRImagingManifestSeries[]
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Series identity and locating information of the DICOM SOP instances in the selection.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRImagingManifest\FHIRImagingManifestSeries $series
     * @return $this
     */
    public function addSeries($series)
    {
        $this->series[] = $series;
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
            if (isset($data['uid'])) {
                $this->setUid($data['uid']);
            }
            if (isset($data['imagingStudy'])) {
                $this->setImagingStudy($data['imagingStudy']);
            }
            if (isset($data['endpoint'])) {
                if (is_array($data['endpoint'])) {
                    foreach ($data['endpoint'] as $d) {
                        $this->addEndpoint($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"endpoint" must be array of objects or null, '.gettype($data['endpoint']).' seen.');
                }
            }
            if (isset($data['series'])) {
                if (is_array($data['series'])) {
                    foreach ($data['series'] as $d) {
                        $this->addSeries($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"series" must be array of objects or null, '.gettype($data['series']).' seen.');
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
        if (isset($this->uid)) {
            $json['uid'] = $this->uid;
        }
        if (isset($this->imagingStudy)) {
            $json['imagingStudy'] = $this->imagingStudy;
        }
        if (0 < count($this->endpoint)) {
            $json['endpoint'] = [];
            foreach ($this->endpoint as $endpoint) {
                $json['endpoint'][] = $endpoint;
            }
        }
        if (0 < count($this->series)) {
            $json['series'] = [];
            foreach ($this->series as $series) {
                $json['series'][] = $series;
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
            $sxe = new \SimpleXMLElement('<ImagingManifestStudy xmlns="http://hl7.org/fhir"></ImagingManifestStudy>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->uid)) {
            $this->uid->xmlSerialize(true, $sxe->addChild('uid'));
        }
        if (isset($this->imagingStudy)) {
            $this->imagingStudy->xmlSerialize(true, $sxe->addChild('imagingStudy'));
        }
        if (0 < count($this->endpoint)) {
            foreach ($this->endpoint as $endpoint) {
                $endpoint->xmlSerialize(true, $sxe->addChild('endpoint'));
            }
        }
        if (0 < count($this->series)) {
            foreach ($this->series as $series) {
                $series->xmlSerialize(true, $sxe->addChild('series'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
