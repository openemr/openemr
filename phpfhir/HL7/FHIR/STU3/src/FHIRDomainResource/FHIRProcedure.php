<?php namespace HL7\FHIR\STU3\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: February 10th, 2018
 */

use HL7\FHIR\STU3\FHIRResource\FHIRDomainResource;

/**
 * An action that is or was performed on a patient. This can be a physical intervention like an operation, or less invasive like counseling or hypnotherapy.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRProcedure extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * This records identifiers associated with this procedure that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate (e.g. in CDA documents, or in written / printed documentation).
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * A protocol, guideline, orderset or other definition that was adhered to in whole or in part by this procedure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $definition = [];

    /**
     * A reference to a resource that contains details of the request for this procedure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $basedOn = [];

    /**
     * A larger event of which this particular procedure is a component or step.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $partOf = [];

    /**
     * A code specifying the state of the procedure. Generally this will be in-progress or completed state.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIREventStatus
     */
    public $status = null;

    /**
     * Set this to true if the record is saying that the procedure was NOT performed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public $notDone = null;

    /**
     * A code indicating why the procedure was not performed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $notDoneReason = null;

    /**
     * A code that classifies the procedure for searching, sorting and display purposes (e.g. "Surgical Procedure").
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $category = null;

    /**
     * The specific procedure that is performed. Use text if the exact nature of the procedure cannot be coded (e.g. "Laparoscopic Appendectomy").
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * The person, animal or group on which the procedure was performed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The encounter during which the procedure was performed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $context = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public $performedDateTime = null;

    /**
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public $performedPeriod = null;

    /**
     * Limited to 'real' people rather than equipment.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRProcedure\FHIRProcedurePerformer[]
     */
    public $performer = [];

    /**
     * The location where the procedure actually happened.  E.g. a newborn at home, a tracheostomy at a restaurant.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public $location = null;

    /**
     * The coded reason why the procedure was performed. This may be coded entity of some type, or may simply be present as text.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $reasonCode = [];

    /**
     * The condition that is the reason why the procedure was performed.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $reasonReference = [];

    /**
     * Detailed and structured anatomical location information. Multiple locations are allowed - e.g. multiple punch biopsies of a lesion.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $bodySite = [];

    /**
     * The outcome of the procedure - did it resolve reasons for the procedure being performed?
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public $outcome = null;

    /**
     * This could be a histology result, pathology report, surgical report, etc..
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $report = [];

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period. These are generally tracked separately from the notes, which will typically describe the procedure itself rather than any 'post procedure' issues.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $complication = [];

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $complicationDetail = [];

    /**
     * If the procedure required specific follow up - e.g. removal of sutures. The followup may be represented as a simple note, or could potentially be more complex in which case the CarePlan resource can be used.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $followUp = [];

    /**
     * Any other notes about the procedure.  E.g. the operative notes.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * A device that is implanted, removed or otherwise manipulated (calibration, battery replacement, fitting a prosthesis, attaching a wound-vac, etc.) as a focal portion of the Procedure.
     * @var \HL7\FHIR\STU3\FHIRResource\FHIRProcedure\FHIRProcedureFocalDevice[]
     */
    public $focalDevice = [];

    /**
     * Identifies medications, devices and any other substance used as part of the procedure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public $usedReference = [];

    /**
     * Identifies coded items that were used as part of the procedure.
     * @var \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public $usedCode = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Procedure';

    /**
     * This records identifiers associated with this procedure that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate (e.g. in CDA documents, or in written / printed documentation).
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * This records identifiers associated with this procedure that are defined by business processes and/or used to refer to it when a direct URL reference to the resource itself is not appropriate (e.g. in CDA documents, or in written / printed documentation).
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * A protocol, guideline, orderset or other definition that was adhered to in whole or in part by this procedure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * A protocol, guideline, orderset or other definition that was adhered to in whole or in part by this procedure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $definition
     * @return $this
     */
    public function addDefinition($definition)
    {
        $this->definition[] = $definition;
        return $this;
    }

    /**
     * A reference to a resource that contains details of the request for this procedure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getBasedOn()
    {
        return $this->basedOn;
    }

    /**
     * A reference to a resource that contains details of the request for this procedure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $basedOn
     * @return $this
     */
    public function addBasedOn($basedOn)
    {
        $this->basedOn[] = $basedOn;
        return $this;
    }

    /**
     * A larger event of which this particular procedure is a component or step.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * A larger event of which this particular procedure is a component or step.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $partOf
     * @return $this
     */
    public function addPartOf($partOf)
    {
        $this->partOf[] = $partOf;
        return $this;
    }

    /**
     * A code specifying the state of the procedure. Generally this will be in-progress or completed state.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIREventStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * A code specifying the state of the procedure. Generally this will be in-progress or completed state.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIREventStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Set this to true if the record is saying that the procedure was NOT performed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRBoolean
     */
    public function getNotDone()
    {
        return $this->notDone;
    }

    /**
     * Set this to true if the record is saying that the procedure was NOT performed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRBoolean $notDone
     * @return $this
     */
    public function setNotDone($notDone)
    {
        $this->notDone = $notDone;
        return $this;
    }

    /**
     * A code indicating why the procedure was not performed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getNotDoneReason()
    {
        return $this->notDoneReason;
    }

    /**
     * A code indicating why the procedure was not performed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $notDoneReason
     * @return $this
     */
    public function setNotDoneReason($notDoneReason)
    {
        $this->notDoneReason = $notDoneReason;
        return $this;
    }

    /**
     * A code that classifies the procedure for searching, sorting and display purposes (e.g. "Surgical Procedure").
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * A code that classifies the procedure for searching, sorting and display purposes (e.g. "Surgical Procedure").
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * The specific procedure that is performed. Use text if the exact nature of the procedure cannot be coded (e.g. "Laparoscopic Appendectomy").
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * The specific procedure that is performed. Use text if the exact nature of the procedure cannot be coded (e.g. "Laparoscopic Appendectomy").
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The person, animal or group on which the procedure was performed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The person, animal or group on which the procedure was performed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The encounter during which the procedure was performed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * The encounter during which the procedure was performed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRDateTime
     */
    public function getPerformedDateTime()
    {
        return $this->performedDateTime;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRDateTime $performedDateTime
     * @return $this
     */
    public function setPerformedDateTime($performedDateTime)
    {
        $this->performedDateTime = $performedDateTime;
        return $this;
    }

    /**
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRPeriod
     */
    public function getPerformedPeriod()
    {
        return $this->performedPeriod;
    }

    /**
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRPeriod $performedPeriod
     * @return $this
     */
    public function setPerformedPeriod($performedPeriod)
    {
        $this->performedPeriod = $performedPeriod;
        return $this;
    }

    /**
     * Limited to 'real' people rather than equipment.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRProcedure\FHIRProcedurePerformer[]
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * Limited to 'real' people rather than equipment.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRProcedure\FHIRProcedurePerformer $performer
     * @return $this
     */
    public function addPerformer($performer)
    {
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * The location where the procedure actually happened.  E.g. a newborn at home, a tracheostomy at a restaurant.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The location where the procedure actually happened.  E.g. a newborn at home, a tracheostomy at a restaurant.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * The coded reason why the procedure was performed. This may be coded entity of some type, or may simply be present as text.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * The coded reason why the procedure was performed. This may be coded entity of some type, or may simply be present as text.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return $this
     */
    public function addReasonCode($reasonCode)
    {
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * The condition that is the reason why the procedure was performed.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getReasonReference()
    {
        return $this->reasonReference;
    }

    /**
     * The condition that is the reason why the procedure was performed.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $reasonReference
     * @return $this
     */
    public function addReasonReference($reasonReference)
    {
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * Detailed and structured anatomical location information. Multiple locations are allowed - e.g. multiple punch biopsies of a lesion.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getBodySite()
    {
        return $this->bodySite;
    }

    /**
     * Detailed and structured anatomical location information. Multiple locations are allowed - e.g. multiple punch biopsies of a lesion.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $bodySite
     * @return $this
     */
    public function addBodySite($bodySite)
    {
        $this->bodySite[] = $bodySite;
        return $this;
    }

    /**
     * The outcome of the procedure - did it resolve reasons for the procedure being performed?
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * The outcome of the procedure - did it resolve reasons for the procedure being performed?
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * This could be a histology result, pathology report, surgical report, etc..
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * This could be a histology result, pathology report, surgical report, etc..
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $report
     * @return $this
     */
    public function addReport($report)
    {
        $this->report[] = $report;
        return $this;
    }

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period. These are generally tracked separately from the notes, which will typically describe the procedure itself rather than any 'post procedure' issues.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getComplication()
    {
        return $this->complication;
    }

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period. These are generally tracked separately from the notes, which will typically describe the procedure itself rather than any 'post procedure' issues.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $complication
     * @return $this
     */
    public function addComplication($complication)
    {
        $this->complication[] = $complication;
        return $this;
    }

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getComplicationDetail()
    {
        return $this->complicationDetail;
    }

    /**
     * Any complications that occurred during the procedure, or in the immediate post-performance period.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $complicationDetail
     * @return $this
     */
    public function addComplicationDetail($complicationDetail)
    {
        $this->complicationDetail[] = $complicationDetail;
        return $this;
    }

    /**
     * If the procedure required specific follow up - e.g. removal of sutures. The followup may be represented as a simple note, or could potentially be more complex in which case the CarePlan resource can be used.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getFollowUp()
    {
        return $this->followUp;
    }

    /**
     * If the procedure required specific follow up - e.g. removal of sutures. The followup may be represented as a simple note, or could potentially be more complex in which case the CarePlan resource can be used.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $followUp
     * @return $this
     */
    public function addFollowUp($followUp)
    {
        $this->followUp[] = $followUp;
        return $this;
    }

    /**
     * Any other notes about the procedure.  E.g. the operative notes.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Any other notes about the procedure.  E.g. the operative notes.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * A device that is implanted, removed or otherwise manipulated (calibration, battery replacement, fitting a prosthesis, attaching a wound-vac, etc.) as a focal portion of the Procedure.
     * @return \HL7\FHIR\STU3\FHIRResource\FHIRProcedure\FHIRProcedureFocalDevice[]
     */
    public function getFocalDevice()
    {
        return $this->focalDevice;
    }

    /**
     * A device that is implanted, removed or otherwise manipulated (calibration, battery replacement, fitting a prosthesis, attaching a wound-vac, etc.) as a focal portion of the Procedure.
     * @param \HL7\FHIR\STU3\FHIRResource\FHIRProcedure\FHIRProcedureFocalDevice $focalDevice
     * @return $this
     */
    public function addFocalDevice($focalDevice)
    {
        $this->focalDevice[] = $focalDevice;
        return $this;
    }

    /**
     * Identifies medications, devices and any other substance used as part of the procedure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRReference[]
     */
    public function getUsedReference()
    {
        return $this->usedReference;
    }

    /**
     * Identifies medications, devices and any other substance used as part of the procedure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRReference $usedReference
     * @return $this
     */
    public function addUsedReference($usedReference)
    {
        $this->usedReference[] = $usedReference;
        return $this;
    }

    /**
     * Identifies coded items that were used as part of the procedure.
     * @return \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept[]
     */
    public function getUsedCode()
    {
        return $this->usedCode;
    }

    /**
     * Identifies coded items that were used as part of the procedure.
     * @param \HL7\FHIR\STU3\FHIRElement\FHIRCodeableConcept $usedCode
     * @return $this
     */
    public function addUsedCode($usedCode)
    {
        $this->usedCode[] = $usedCode;
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
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, '.gettype($data['identifier']).' seen.');
                }
            }
            if (isset($data['definition'])) {
                if (is_array($data['definition'])) {
                    foreach ($data['definition'] as $d) {
                        $this->addDefinition($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"definition" must be array of objects or null, '.gettype($data['definition']).' seen.');
                }
            }
            if (isset($data['basedOn'])) {
                if (is_array($data['basedOn'])) {
                    foreach ($data['basedOn'] as $d) {
                        $this->addBasedOn($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"basedOn" must be array of objects or null, '.gettype($data['basedOn']).' seen.');
                }
            }
            if (isset($data['partOf'])) {
                if (is_array($data['partOf'])) {
                    foreach ($data['partOf'] as $d) {
                        $this->addPartOf($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"partOf" must be array of objects or null, '.gettype($data['partOf']).' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['notDone'])) {
                $this->setNotDone($data['notDone']);
            }
            if (isset($data['notDoneReason'])) {
                $this->setNotDoneReason($data['notDoneReason']);
            }
            if (isset($data['category'])) {
                $this->setCategory($data['category']);
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['context'])) {
                $this->setContext($data['context']);
            }
            if (isset($data['performedDateTime'])) {
                $this->setPerformedDateTime($data['performedDateTime']);
            }
            if (isset($data['performedPeriod'])) {
                $this->setPerformedPeriod($data['performedPeriod']);
            }
            if (isset($data['performer'])) {
                if (is_array($data['performer'])) {
                    foreach ($data['performer'] as $d) {
                        $this->addPerformer($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"performer" must be array of objects or null, '.gettype($data['performer']).' seen.');
                }
            }
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['reasonCode'])) {
                if (is_array($data['reasonCode'])) {
                    foreach ($data['reasonCode'] as $d) {
                        $this->addReasonCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reasonCode" must be array of objects or null, '.gettype($data['reasonCode']).' seen.');
                }
            }
            if (isset($data['reasonReference'])) {
                if (is_array($data['reasonReference'])) {
                    foreach ($data['reasonReference'] as $d) {
                        $this->addReasonReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reasonReference" must be array of objects or null, '.gettype($data['reasonReference']).' seen.');
                }
            }
            if (isset($data['bodySite'])) {
                if (is_array($data['bodySite'])) {
                    foreach ($data['bodySite'] as $d) {
                        $this->addBodySite($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"bodySite" must be array of objects or null, '.gettype($data['bodySite']).' seen.');
                }
            }
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
            }
            if (isset($data['report'])) {
                if (is_array($data['report'])) {
                    foreach ($data['report'] as $d) {
                        $this->addReport($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"report" must be array of objects or null, '.gettype($data['report']).' seen.');
                }
            }
            if (isset($data['complication'])) {
                if (is_array($data['complication'])) {
                    foreach ($data['complication'] as $d) {
                        $this->addComplication($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"complication" must be array of objects or null, '.gettype($data['complication']).' seen.');
                }
            }
            if (isset($data['complicationDetail'])) {
                if (is_array($data['complicationDetail'])) {
                    foreach ($data['complicationDetail'] as $d) {
                        $this->addComplicationDetail($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"complicationDetail" must be array of objects or null, '.gettype($data['complicationDetail']).' seen.');
                }
            }
            if (isset($data['followUp'])) {
                if (is_array($data['followUp'])) {
                    foreach ($data['followUp'] as $d) {
                        $this->addFollowUp($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"followUp" must be array of objects or null, '.gettype($data['followUp']).' seen.');
                }
            }
            if (isset($data['note'])) {
                if (is_array($data['note'])) {
                    foreach ($data['note'] as $d) {
                        $this->addNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"note" must be array of objects or null, '.gettype($data['note']).' seen.');
                }
            }
            if (isset($data['focalDevice'])) {
                if (is_array($data['focalDevice'])) {
                    foreach ($data['focalDevice'] as $d) {
                        $this->addFocalDevice($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"focalDevice" must be array of objects or null, '.gettype($data['focalDevice']).' seen.');
                }
            }
            if (isset($data['usedReference'])) {
                if (is_array($data['usedReference'])) {
                    foreach ($data['usedReference'] as $d) {
                        $this->addUsedReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"usedReference" must be array of objects or null, '.gettype($data['usedReference']).' seen.');
                }
            }
            if (isset($data['usedCode'])) {
                if (is_array($data['usedCode'])) {
                    foreach ($data['usedCode'] as $d) {
                        $this->addUsedCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"usedCode" must be array of objects or null, '.gettype($data['usedCode']).' seen.');
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (0 < count($this->definition)) {
            $json['definition'] = [];
            foreach ($this->definition as $definition) {
                $json['definition'][] = $definition;
            }
        }
        if (0 < count($this->basedOn)) {
            $json['basedOn'] = [];
            foreach ($this->basedOn as $basedOn) {
                $json['basedOn'][] = $basedOn;
            }
        }
        if (0 < count($this->partOf)) {
            $json['partOf'] = [];
            foreach ($this->partOf as $partOf) {
                $json['partOf'][] = $partOf;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->notDone)) {
            $json['notDone'] = $this->notDone;
        }
        if (isset($this->notDoneReason)) {
            $json['notDoneReason'] = $this->notDoneReason;
        }
        if (isset($this->category)) {
            $json['category'] = $this->category;
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->context)) {
            $json['context'] = $this->context;
        }
        if (isset($this->performedDateTime)) {
            $json['performedDateTime'] = $this->performedDateTime;
        }
        if (isset($this->performedPeriod)) {
            $json['performedPeriod'] = $this->performedPeriod;
        }
        if (0 < count($this->performer)) {
            $json['performer'] = [];
            foreach ($this->performer as $performer) {
                $json['performer'][] = $performer;
            }
        }
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (0 < count($this->reasonCode)) {
            $json['reasonCode'] = [];
            foreach ($this->reasonCode as $reasonCode) {
                $json['reasonCode'][] = $reasonCode;
            }
        }
        if (0 < count($this->reasonReference)) {
            $json['reasonReference'] = [];
            foreach ($this->reasonReference as $reasonReference) {
                $json['reasonReference'][] = $reasonReference;
            }
        }
        if (0 < count($this->bodySite)) {
            $json['bodySite'] = [];
            foreach ($this->bodySite as $bodySite) {
                $json['bodySite'][] = $bodySite;
            }
        }
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
        }
        if (0 < count($this->report)) {
            $json['report'] = [];
            foreach ($this->report as $report) {
                $json['report'][] = $report;
            }
        }
        if (0 < count($this->complication)) {
            $json['complication'] = [];
            foreach ($this->complication as $complication) {
                $json['complication'][] = $complication;
            }
        }
        if (0 < count($this->complicationDetail)) {
            $json['complicationDetail'] = [];
            foreach ($this->complicationDetail as $complicationDetail) {
                $json['complicationDetail'][] = $complicationDetail;
            }
        }
        if (0 < count($this->followUp)) {
            $json['followUp'] = [];
            foreach ($this->followUp as $followUp) {
                $json['followUp'][] = $followUp;
            }
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
        }
        if (0 < count($this->focalDevice)) {
            $json['focalDevice'] = [];
            foreach ($this->focalDevice as $focalDevice) {
                $json['focalDevice'][] = $focalDevice;
            }
        }
        if (0 < count($this->usedReference)) {
            $json['usedReference'] = [];
            foreach ($this->usedReference as $usedReference) {
                $json['usedReference'][] = $usedReference;
            }
        }
        if (0 < count($this->usedCode)) {
            $json['usedCode'] = [];
            foreach ($this->usedCode as $usedCode) {
                $json['usedCode'][] = $usedCode;
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
            $sxe = new \SimpleXMLElement('<Procedure xmlns="http://hl7.org/fhir"></Procedure>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (0 < count($this->definition)) {
            foreach ($this->definition as $definition) {
                $definition->xmlSerialize(true, $sxe->addChild('definition'));
            }
        }
        if (0 < count($this->basedOn)) {
            foreach ($this->basedOn as $basedOn) {
                $basedOn->xmlSerialize(true, $sxe->addChild('basedOn'));
            }
        }
        if (0 < count($this->partOf)) {
            foreach ($this->partOf as $partOf) {
                $partOf->xmlSerialize(true, $sxe->addChild('partOf'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->notDone)) {
            $this->notDone->xmlSerialize(true, $sxe->addChild('notDone'));
        }
        if (isset($this->notDoneReason)) {
            $this->notDoneReason->xmlSerialize(true, $sxe->addChild('notDoneReason'));
        }
        if (isset($this->category)) {
            $this->category->xmlSerialize(true, $sxe->addChild('category'));
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->context)) {
            $this->context->xmlSerialize(true, $sxe->addChild('context'));
        }
        if (isset($this->performedDateTime)) {
            $this->performedDateTime->xmlSerialize(true, $sxe->addChild('performedDateTime'));
        }
        if (isset($this->performedPeriod)) {
            $this->performedPeriod->xmlSerialize(true, $sxe->addChild('performedPeriod'));
        }
        if (0 < count($this->performer)) {
            foreach ($this->performer as $performer) {
                $performer->xmlSerialize(true, $sxe->addChild('performer'));
            }
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (0 < count($this->reasonCode)) {
            foreach ($this->reasonCode as $reasonCode) {
                $reasonCode->xmlSerialize(true, $sxe->addChild('reasonCode'));
            }
        }
        if (0 < count($this->reasonReference)) {
            foreach ($this->reasonReference as $reasonReference) {
                $reasonReference->xmlSerialize(true, $sxe->addChild('reasonReference'));
            }
        }
        if (0 < count($this->bodySite)) {
            foreach ($this->bodySite as $bodySite) {
                $bodySite->xmlSerialize(true, $sxe->addChild('bodySite'));
            }
        }
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if (0 < count($this->report)) {
            foreach ($this->report as $report) {
                $report->xmlSerialize(true, $sxe->addChild('report'));
            }
        }
        if (0 < count($this->complication)) {
            foreach ($this->complication as $complication) {
                $complication->xmlSerialize(true, $sxe->addChild('complication'));
            }
        }
        if (0 < count($this->complicationDetail)) {
            foreach ($this->complicationDetail as $complicationDetail) {
                $complicationDetail->xmlSerialize(true, $sxe->addChild('complicationDetail'));
            }
        }
        if (0 < count($this->followUp)) {
            foreach ($this->followUp as $followUp) {
                $followUp->xmlSerialize(true, $sxe->addChild('followUp'));
            }
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if (0 < count($this->focalDevice)) {
            foreach ($this->focalDevice as $focalDevice) {
                $focalDevice->xmlSerialize(true, $sxe->addChild('focalDevice'));
            }
        }
        if (0 < count($this->usedReference)) {
            foreach ($this->usedReference as $usedReference) {
                $usedReference->xmlSerialize(true, $sxe->addChild('usedReference'));
            }
        }
        if (0 < count($this->usedCode)) {
            foreach ($this->usedCode as $usedCode) {
                $usedCode->xmlSerialize(true, $sxe->addChild('usedCode'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
