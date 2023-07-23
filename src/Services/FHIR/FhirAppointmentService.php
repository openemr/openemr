<?php

/**
 * FhirAppointmentService handles the mapping of data from the OpenEMR appointment service into FHIR resources.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAppointmentStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRParticipationStatus;
use OpenEMR\FHIR\R4\FHIRResource\FHIRAppointment\FHIRAppointmentParticipant;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirAppointmentService extends FhirServiceBase implements IPatientCompartmentResourceService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use PatientSearchTrait;

    const PARTICIPANT_TYPE_PARTICIPANT = "PART";
    const PARTICIPANT_TYPE_PRIMARY_PERFORMER = "PPRF";
    const PARTICIPANT_TYPE_PRIMARY_PERFORMER_TEXT = "Primary Performer";
    const PARTICIPANT_TYPE_PARTICIPANT_TEXT = "Participant";

    /**
     * @var AppointmentService
     */
    private $appointmentService;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->appointmentService = new AppointmentService();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('pc_uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $appt = new FHIRAppointment();

        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId("1");
        $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        $appt->setMeta($fhirMeta);

        $id = new FHIRId();
        $id->setValue($dataRecord['pc_uuid']);
        $appt->setId($id);

        // now we need to parse out our status
        $statusCode = 'pending'; // there can be a lot of different status and we will default to pending
        switch ($dataRecord['pc_apptstatus']) {
            case '-': // none
                // None of the participant(s) have finalized their acceptance of the appointment request, and the start/end time might not be set yet.
                $statusCode = 'proposed';
                break;
            case '#': // insurance / financial issue
            case '^': // pending
                // Some or all of the participant(s) have not finalized their acceptance of the appointment request.
                $statusCode = 'pending';
                break;
            case '>': // checked out
            case '$': // coding done
                $statusCode = 'fulfilled';
                break;
            case 'AVM': // AVM confirmed
            case 'SMS': // SMS confirmed
            case 'EMAIL': // Email confirmed
            case '*': // reminder done
                // All participant(s) have been considered and the appointment is confirmed to go ahead at the date/times specified.
                $statusCode = 'booked';
                break;
            case '%': // Cancelled < 24h
            case '!': // left w/o visit
            case 'x':
                // The appointment has been cancelled.
                $statusCode = 'cancelled';
                break;
            case '?':
                // Some or all of the participant(s) have not/did not appear for the appointment (usually the patient).
                $statusCode = 'noshow';
                break;
            case '~': // arrived late
            case '@':
                $statusCode = 'arrived';
                break;
            case '<': // in exam room
            case '+': // chart pulled
                // When checked in, all pre-encounter administrative work is complete, and the encounter may begin. (where multiple patients are involved, they are all present).
                $statusCode = 'checked-in';
                break;
            case 'CALL': // Callback requested
                $statusCode = 'waitlist';
                //  The appointment has been placed on a waitlist, to be scheduled/confirmed in the future when a slot/service is available. A specific time might or might not be pre-allocated.
                break;
        }
        // TODO: add an event here allowing people to update / configure the FHIR status
        $apptStatus = new FHIRAppointmentStatus();
        $apptStatus->setValue($statusCode);
        $appt->setStatus($apptStatus);
        // now parse out the participants
        // patient first
        if (!empty($dataRecord['puuid'])) {
            $patient = new FHIRAppointmentParticipant();
            $participantType = UtilsService::createCodeableConcept([
                self::PARTICIPANT_TYPE_PARTICIPANT =>
                    [
                        'code' => self::PARTICIPANT_TYPE_PARTICIPANT
                        ,'description' => self::PARTICIPANT_TYPE_PARTICIPANT_TEXT
                        ,'system' => FhirCodeSystemConstants::HL7_PARTICIPATION_TYPE
                    ]
            ]);
            $patient->addType($participantType);
            $patient->setActor(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
            $status = new FHIRParticipationStatus();
            $status->setValue('accepted'); // we don't really track any other field right now in FHIR
            $patient->setStatus($status);
            $appt->addParticipant($patient);
        }

        // now provider
        if (!empty($dataRecord['pce_aid_uuid'])) {
            $provider = new FHIRAppointmentParticipant();
            $providerType = UtilsService::createCodeableConcept([
                self::PARTICIPANT_TYPE_PRIMARY_PERFORMER =>
                    [
                        'code' => self::PARTICIPANT_TYPE_PRIMARY_PERFORMER
                        ,'description' => self::PARTICIPANT_TYPE_PRIMARY_PERFORMER_TEXT
                        ,'system' => FhirCodeSystemConstants::HL7_PARTICIPATION_TYPE
                    ]
            ]);
            $provider->addType($providerType);
            // we can only provide the provider if they have an NPI, otherwise they are a person
            if (!empty($dataRecord['pce_aid_npi'])) {
                $provider->setActor(UtilsService::createRelativeReference('Practitioner', $dataRecord['pce_aid_uuid']));
            } else {
                $provider->setActor(UtilsService::createRelativeReference('Person', $dataRecord['pce_aid_uuid']));
            }
            $status = new FHIRParticipationStatus();
            $status->setValue('accepted'); // we don't really track any other field right now in FHIR
            $provider->setStatus($status);
            $appt->addParticipant($provider);
        }

        // now let's get start and end dates

        // start time
        if (!empty($dataRecord['pc_eventDate'])) {
            $concatenatedDate = $dataRecord['pc_eventDate'] . ' ' . $dataRecord['pc_startTime'];
            $startInstant = UtilsService::getLocalDateAsUTC($concatenatedDate);
            $appt->setStart(new FHIRInstant($startInstant));
        } else if ($dataRecord['pc_endDate'] != '0000-00-00' && !empty($dataRecord['pc_startTime'])) {
            $concatenatedDate = $dataRecord['pc_endDate'] . ' ' . $dataRecord['pc_startTime'];
            $startInstant = UtilsService::getLocalDateAsUTC($concatenatedDate);
            $appt->setStart(new FHIRInstant($startInstant));
        }

        // if we have a start date and and end time we will use that
        if (!empty($dataRecord['pc_eventDate']) && !empty($dataRecord['pc_endTime'])) {
            $concatenatedDate = $dataRecord['pc_eventDate'] . ' ' . $dataRecord['pc_endTime'];
            $endInstant = UtilsService::getLocalDateAsUTC($concatenatedDate);
            $appt->setEnd(new FHIRInstant($endInstant));
        } else if (!empty($dataRecord['pc_endDate']) && !empty($dataRecord['pc_endTime'])) {
            $concatenatedDate = $dataRecord['pc_endDate'] . ' ' . $dataRecord['pc_endTime'];
            $endInstant = UtilsService::getLocalDateAsUTC($concatenatedDate);
            $appt->setEnd(new FHIRInstant($endInstant));
        }

        if (!empty($dataRecord['pc_hometext'])) {
            $appt->setComment($dataRecord['pc_hometext']);
        }

        return $appt;
    }


    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        return $this->appointmentService->search($openEMRSearchParameters, true, $puuidBind);
    }

    /**
     * Creates the Provenance resource  for the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRAppointment)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        // we don't have an individual authorship right now for appointments so we default to billing organization
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }
}
