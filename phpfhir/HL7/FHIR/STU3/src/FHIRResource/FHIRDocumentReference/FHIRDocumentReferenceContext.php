<?php namespace HL7\FHIR\STU3\FHIRResource\FHIRDocumentReference;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRElement\FHIRBackboneElement;

/**
 * A reference to a document.
 */
class FHIRDocumentReferenceContext extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Describes the clinical encounter or type of care that the document content is associated with.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * This list of codes represents the main clinical acts, such as a colonoscopy or an appendectomy, being documented. In some cases, the event is inherent in the typeCode, such as a "History and Physical Report" in which the procedure being documented is necessarily a "History and Physical" act.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $event = [];

    /**
     * The time period over which the service that is described by the document was provided.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * The kind of facility where the patient was seen.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $facilityType = null;

    /**
     * This property may convey specifics about the practice setting where the content was created, often reflecting the clinical specialty.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $practiceSetting = null;

    /**
     * The Patient Information as known when the document was published. May be a reference to a version specific, or contained.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $sourcePatientInfo = null;

    /**
     * Related identifiers or resources associated with the DocumentReference.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceRelated[]
     */
    public $related = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'DocumentReference.Context';

    /**
     * Describes the clinical encounter or type of care that the document content is associated with.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * Describes the clinical encounter or type of care that the document content is associated with.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * This list of codes represents the main clinical acts, such as a colonoscopy or an appendectomy, being documented. In some cases, the event is inherent in the typeCode, such as a "History and Physical Report" in which the procedure being documented is necessarily a "History and Physical" act.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * This list of codes represents the main clinical acts, such as a colonoscopy or an appendectomy, being documented. In some cases, the event is inherent in the typeCode, such as a "History and Physical Report" in which the procedure being documented is necessarily a "History and Physical" act.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $event
     * @return $this
     */
    public function addEvent($event)
    {
        $this->event[] = $event;
        return $this;
    }

    /**
     * The time period over which the service that is described by the document was provided.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The time period over which the service that is described by the document was provided.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * The kind of facility where the patient was seen.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getFacilityType()
    {
        return $this->facilityType;
    }

    /**
     * The kind of facility where the patient was seen.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $facilityType
     * @return $this
     */
    public function setFacilityType($facilityType)
    {
        $this->facilityType = $facilityType;
        return $this;
    }

    /**
     * This property may convey specifics about the practice setting where the content was created, often reflecting the clinical specialty.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getPracticeSetting()
    {
        return $this->practiceSetting;
    }

    /**
     * This property may convey specifics about the practice setting where the content was created, often reflecting the clinical specialty.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $practiceSetting
     * @return $this
     */
    public function setPracticeSetting($practiceSetting)
    {
        $this->practiceSetting = $practiceSetting;
        return $this;
    }

    /**
     * The Patient Information as known when the document was published. May be a reference to a version specific, or contained.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getSourcePatientInfo()
    {
        return $this->sourcePatientInfo;
    }

    /**
     * The Patient Information as known when the document was published. May be a reference to a version specific, or contained.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $sourcePatientInfo
     * @return $this
     */
    public function setSourcePatientInfo($sourcePatientInfo)
    {
        $this->sourcePatientInfo = $sourcePatientInfo;
        return $this;
    }

    /**
     * Related identifiers or resources associated with the DocumentReference.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceRelated[]
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Related identifiers or resources associated with the DocumentReference.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceRelated $related
     * @return $this
     */
    public function addRelated($related)
    {
        $this->related[] = $related;
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
            if (isset($data['encounter'])) {
                $this->setEncounter($data['encounter']);
            }
            if (isset($data['event'])) {
                if (is_array($data['event'])) {
                    foreach ($data['event'] as $d) {
                        $this->addEvent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"event" must be array of objects or null, '.gettype($data['event']).' seen.');
                }
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['facilityType'])) {
                $this->setFacilityType($data['facilityType']);
            }
            if (isset($data['practiceSetting'])) {
                $this->setPracticeSetting($data['practiceSetting']);
            }
            if (isset($data['sourcePatientInfo'])) {
                $this->setSourcePatientInfo($data['sourcePatientInfo']);
            }
            if (isset($data['related'])) {
                if (is_array($data['related'])) {
                    foreach ($data['related'] as $d) {
                        $this->addRelated($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"related" must be array of objects or null, '.gettype($data['related']).' seen.');
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
        if (isset($this->encounter)) {
            $json['encounter'] = $this->encounter;
        }
        if (0 < count($this->event)) {
            $json['event'] = [];
            foreach ($this->event as $event) {
                $json['event'][] = $event;
            }
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->facilityType)) {
            $json['facilityType'] = $this->facilityType;
        }
        if (isset($this->practiceSetting)) {
            $json['practiceSetting'] = $this->practiceSetting;
        }
        if (isset($this->sourcePatientInfo)) {
            $json['sourcePatientInfo'] = $this->sourcePatientInfo;
        }
        if (0 < count($this->related)) {
            $json['related'] = [];
            foreach ($this->related as $related) {
                $json['related'][] = $related;
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
            $sxe = new \SimpleXMLElement('<DocumentReferenceContext xmlns="http://hl7.org/fhir"></DocumentReferenceContext>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->encounter)) {
            $this->encounter->xmlSerialize(true, $sxe->addChild('encounter'));
        }
        if (0 < count($this->event)) {
            foreach ($this->event as $event) {
                $event->xmlSerialize(true, $sxe->addChild('event'));
            }
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->facilityType)) {
            $this->facilityType->xmlSerialize(true, $sxe->addChild('facilityType'));
        }
        if (isset($this->practiceSetting)) {
            $this->practiceSetting->xmlSerialize(true, $sxe->addChild('practiceSetting'));
        }
        if (isset($this->sourcePatientInfo)) {
            $this->sourcePatientInfo->xmlSerialize(true, $sxe->addChild('sourcePatientInfo'));
        }
        if (0 < count($this->related)) {
            foreach ($this->related as $related) {
                $related->xmlSerialize(true, $sxe->addChild('related'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
