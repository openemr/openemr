<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * Actual or  potential/avoided event causing unintended physical injury resulting from or contributed to by medical care, a research study or other healthcare setting factors that requires additional monitoring, treatment, or hospitalization, or that results in death.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRAdverseEvent extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * The identifier(s) of this adverse event that are assigned by business processes and/or used to refer to it when a direct URL reference to the resource itsefl is not appropriate.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * The type of event which is important to characterize what occurred and caused harm to the subject, or had the potential to cause harm to the subject.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAdverseEventCategory
     */
    public $category = null;

    /**
     * This element defines the specific type of event that occurred or that was prevented from occurring.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * This subject or group impacted by the event.  With a prospective adverse event, there will be no subject as the adverse event was prevented.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The date (and perhaps time) when the adverse event occurred.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * Includes information about the reaction that occurred as a result of exposure to a substance (for example, a drug or a chemical).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $reaction = [];

    /**
     * The information about where the adverse event occurred.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $location = null;

    /**
     * Describes the seriousness or severity of the adverse event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $seriousness = null;

    /**
     * Describes the type of outcome from the adverse event.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $outcome = null;

    /**
     * Information on who recorded the adverse event.  May be the patient or a practitioner.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $recorder = null;

    /**
     * Parties that may or should contribute or have contributed information to the Act. Such information includes information leading to the decision to perform the Act and how to perform the Act (e.g. consultant), information that the Act itself seeks to reveal (e.g. informant of clinical history), or information about what Act was performed (e.g. informant witness).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $eventParticipant = null;

    /**
     * Describes the adverse event in text.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Describes the entity that is suspected to have caused the adverse event.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity[]
     */
    public $suspectEntity = [];

    /**
     * AdverseEvent.subjectMedicalHistory.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $subjectMedicalHistory = [];

    /**
     * AdverseEvent.referenceDocument.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $referenceDocument = [];

    /**
     * AdverseEvent.study.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $study = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'AdverseEvent';

    /**
     * The identifier(s) of this adverse event that are assigned by business processes and/or used to refer to it when a direct URL reference to the resource itsefl is not appropriate.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The identifier(s) of this adverse event that are assigned by business processes and/or used to refer to it when a direct URL reference to the resource itsefl is not appropriate.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The type of event which is important to characterize what occurred and caused harm to the subject, or had the potential to cause harm to the subject.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAdverseEventCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * The type of event which is important to characterize what occurred and caused harm to the subject, or had the potential to cause harm to the subject.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAdverseEventCategory $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * This element defines the specific type of event that occurred or that was prevented from occurring.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * This element defines the specific type of event that occurred or that was prevented from occurring.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * This subject or group impacted by the event.  With a prospective adverse event, there will be no subject as the adverse event was prevented.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * This subject or group impacted by the event.  With a prospective adverse event, there will be no subject as the adverse event was prevented.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The date (and perhaps time) when the adverse event occurred.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date (and perhaps time) when the adverse event occurred.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Includes information about the reaction that occurred as a result of exposure to a substance (for example, a drug or a chemical).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getReaction()
    {
        return $this->reaction;
    }

    /**
     * Includes information about the reaction that occurred as a result of exposure to a substance (for example, a drug or a chemical).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $reaction
     * @return $this
     */
    public function addReaction($reaction)
    {
        $this->reaction[] = $reaction;
        return $this;
    }

    /**
     * The information about where the adverse event occurred.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The information about where the adverse event occurred.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Describes the seriousness or severity of the adverse event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getSeriousness()
    {
        return $this->seriousness;
    }

    /**
     * Describes the seriousness or severity of the adverse event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $seriousness
     * @return $this
     */
    public function setSeriousness($seriousness)
    {
        $this->seriousness = $seriousness;
        return $this;
    }

    /**
     * Describes the type of outcome from the adverse event.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * Describes the type of outcome from the adverse event.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * Information on who recorded the adverse event.  May be the patient or a practitioner.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getRecorder()
    {
        return $this->recorder;
    }

    /**
     * Information on who recorded the adverse event.  May be the patient or a practitioner.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $recorder
     * @return $this
     */
    public function setRecorder($recorder)
    {
        $this->recorder = $recorder;
        return $this;
    }

    /**
     * Parties that may or should contribute or have contributed information to the Act. Such information includes information leading to the decision to perform the Act and how to perform the Act (e.g. consultant), information that the Act itself seeks to reveal (e.g. informant of clinical history), or information about what Act was performed (e.g. informant witness).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getEventParticipant()
    {
        return $this->eventParticipant;
    }

    /**
     * Parties that may or should contribute or have contributed information to the Act. Such information includes information leading to the decision to perform the Act and how to perform the Act (e.g. consultant), information that the Act itself seeks to reveal (e.g. informant of clinical history), or information about what Act was performed (e.g. informant witness).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $eventParticipant
     * @return $this
     */
    public function setEventParticipant($eventParticipant)
    {
        $this->eventParticipant = $eventParticipant;
        return $this;
    }

    /**
     * Describes the adverse event in text.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Describes the adverse event in text.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Describes the entity that is suspected to have caused the adverse event.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity[]
     */
    public function getSuspectEntity()
    {
        return $this->suspectEntity;
    }

    /**
     * Describes the entity that is suspected to have caused the adverse event.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity $suspectEntity
     * @return $this
     */
    public function addSuspectEntity($suspectEntity)
    {
        $this->suspectEntity[] = $suspectEntity;
        return $this;
    }

    /**
     * AdverseEvent.subjectMedicalHistory.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getSubjectMedicalHistory()
    {
        return $this->subjectMedicalHistory;
    }

    /**
     * AdverseEvent.subjectMedicalHistory.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $subjectMedicalHistory
     * @return $this
     */
    public function addSubjectMedicalHistory($subjectMedicalHistory)
    {
        $this->subjectMedicalHistory[] = $subjectMedicalHistory;
        return $this;
    }

    /**
     * AdverseEvent.referenceDocument.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getReferenceDocument()
    {
        return $this->referenceDocument;
    }

    /**
     * AdverseEvent.referenceDocument.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $referenceDocument
     * @return $this
     */
    public function addReferenceDocument($referenceDocument)
    {
        $this->referenceDocument[] = $referenceDocument;
        return $this;
    }

    /**
     * AdverseEvent.study.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * AdverseEvent.study.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $study
     * @return $this
     */
    public function addStudy($study)
    {
        $this->study[] = $study;
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
            if (isset($data['category'])) {
                $this->setCategory($data['category']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['reaction'])) {
                if (is_array($data['reaction'])) {
                    foreach ($data['reaction'] as $d) {
                        $this->addReaction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reaction" must be array of objects or null, '.gettype($data['reaction']).' seen.');
                }
            }
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['seriousness'])) {
                $this->setSeriousness($data['seriousness']);
            }
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
            }
            if (isset($data['recorder'])) {
                $this->setRecorder($data['recorder']);
            }
            if (isset($data['eventParticipant'])) {
                $this->setEventParticipant($data['eventParticipant']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['suspectEntity'])) {
                if (is_array($data['suspectEntity'])) {
                    foreach ($data['suspectEntity'] as $d) {
                        $this->addSuspectEntity($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"suspectEntity" must be array of objects or null, '.gettype($data['suspectEntity']).' seen.');
                }
            }
            if (isset($data['subjectMedicalHistory'])) {
                if (is_array($data['subjectMedicalHistory'])) {
                    foreach ($data['subjectMedicalHistory'] as $d) {
                        $this->addSubjectMedicalHistory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subjectMedicalHistory" must be array of objects or null, '.gettype($data['subjectMedicalHistory']).' seen.');
                }
            }
            if (isset($data['referenceDocument'])) {
                if (is_array($data['referenceDocument'])) {
                    foreach ($data['referenceDocument'] as $d) {
                        $this->addReferenceDocument($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"referenceDocument" must be array of objects or null, '.gettype($data['referenceDocument']).' seen.');
                }
            }
            if (isset($data['study'])) {
                if (is_array($data['study'])) {
                    foreach ($data['study'] as $d) {
                        $this->addStudy($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"study" must be array of objects or null, '.gettype($data['study']).' seen.');
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
        $json['resourceType'] = $this->_fhirElementName;
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (0 < count($this->reaction)) {
            $json['reaction'] = [];
            foreach ($this->reaction as $reaction) {
                $json['reaction'][] = $reaction;
            }
        }
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (isset($this->seriousness)) {
            $json['seriousness'] = $this->seriousness;
        }
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
        }
        if (isset($this->recorder)) {
            $json['recorder'] = $this->recorder;
        }
        if (isset($this->eventParticipant)) {
            $json['eventParticipant'] = $this->eventParticipant;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->suspectEntity)) {
            $json['suspectEntity'] = [];
            foreach ($this->suspectEntity as $suspectEntity) {
                $json['suspectEntity'][] = $suspectEntity;
            }
        }
        if (0 < count($this->subjectMedicalHistory)) {
            $json['subjectMedicalHistory'] = [];
            foreach ($this->subjectMedicalHistory as $subjectMedicalHistory) {
                $json['subjectMedicalHistory'][] = $subjectMedicalHistory;
            }
        }
        if (0 < count($this->referenceDocument)) {
            $json['referenceDocument'] = [];
            foreach ($this->referenceDocument as $referenceDocument) {
                $json['referenceDocument'][] = $referenceDocument;
            }
        }
        if (0 < count($this->study)) {
            $json['study'] = [];
            foreach ($this->study as $study) {
                $json['study'][] = $study;
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
            $sxe = new \SimpleXMLElement('<AdverseEvent xmlns="http://hl7.org/fhir"></AdverseEvent>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->category)) {
            $this->category->xmlSerialize(true, $sxe->addChild('category'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (0 < count($this->reaction)) {
            foreach ($this->reaction as $reaction) {
                $reaction->xmlSerialize(true, $sxe->addChild('reaction'));
            }
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (isset($this->seriousness)) {
            $this->seriousness->xmlSerialize(true, $sxe->addChild('seriousness'));
        }
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if (isset($this->recorder)) {
            $this->recorder->xmlSerialize(true, $sxe->addChild('recorder'));
        }
        if (isset($this->eventParticipant)) {
            $this->eventParticipant->xmlSerialize(true, $sxe->addChild('eventParticipant'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->suspectEntity)) {
            foreach ($this->suspectEntity as $suspectEntity) {
                $suspectEntity->xmlSerialize(true, $sxe->addChild('suspectEntity'));
            }
        }
        if (0 < count($this->subjectMedicalHistory)) {
            foreach ($this->subjectMedicalHistory as $subjectMedicalHistory) {
                $subjectMedicalHistory->xmlSerialize(true, $sxe->addChild('subjectMedicalHistory'));
            }
        }
        if (0 < count($this->referenceDocument)) {
            foreach ($this->referenceDocument as $referenceDocument) {
                $referenceDocument->xmlSerialize(true, $sxe->addChild('referenceDocument'));
            }
        }
        if (0 < count($this->study)) {
            foreach ($this->study as $study) {
                $study->xmlSerialize(true, $sxe->addChild('study'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
