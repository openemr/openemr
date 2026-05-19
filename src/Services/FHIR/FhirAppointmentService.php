<?php

/**
 * FhirAppointmentService handles the mapping of data from the OpenEMR appointment service into FHIR resources.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\BC\Utilities;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAppointmentStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRParticipationStatus;
use OpenEMR\FHIR\R4\FHIRResource\FHIRAppointment\FHIRAppointmentParticipant;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirAppointmentService extends FhirServiceBase implements IPatientCompartmentResourceService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use PatientSearchTrait;

    const APPOINTMENT_TYPE_LOCATION = "LOC";
    const APPOINTMENT_TYPE_LOCATION_TEXT = "Location";
    const PARTICIPANT_TYPE_LOCATION = "LOC";
    const PARTICIPANT_TYPE_LOCATION_TEXT = "Location";
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
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATE, ['pc_eventDate']),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['pc_time']);
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $appt = new FHIRAppointment();

        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId("1");
        $fhirMeta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['pc_time']));
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

        // now add appointmentType coding
        if (!empty($dataRecord['pc_catid'])) {
            $category = $this->appointmentService->getOneCalendarCategory($dataRecord['pc_catid']);
            $appointmentType = new FHIRCodeableConcept();
            $code = new FHIRCoding();
            $code->setCode($category[ 0 ][ 'pc_constant_id' ]);
            $code->setDisplay($category[ 0 ][ 'pc_catname' ]);
            // var_dump( $_SERVER );
            $system = str_replace('/Appointment', '/ValueSet/appointment-type', OEGlobalsBag::getInstance()->get('site_addr_oath') . ($_SERVER['REDIRECT_URL'] ?? ''));
            $code->setSystem($system);
            $appointmentType->addCoding($code);
            $appt->setAppointmentType($appointmentType);
        }


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

        // now location
        if (!empty($dataRecord['facility_uuid'])) {
            $location = new FHIRAppointmentParticipant();
            $participantType = UtilsService::createCodeableConcept([
                self::PARTICIPANT_TYPE_LOCATION =>
                    [
                        'code' => self::PARTICIPANT_TYPE_LOCATION
                        ,'description' => self::PARTICIPANT_TYPE_LOCATION_TEXT
                        ,'system' => FhirCodeSystemConstants::HL7_PARTICIPATION_TYPE
                    ]
            ]);
            $location->addType($participantType);
            $location->setActor(UtilsService::createRelativeReference('Location', $dataRecord['facility_uuid']));
            $status = new FHIRParticipationStatus();
            $status->setValue('accepted'); // we don't really track any other field right now in FHIR
            $location->setStatus($status);
            $appt->addParticipant($location);
        }

        // now let's get start and end dates

        // start time
        if (!empty($dataRecord['pc_eventDate'])) {
            $concatenatedDate = $dataRecord['pc_eventDate'] . ' ' . $dataRecord['pc_startTime'];
            $startInstant = UtilsService::getLocalDateAsUTC($concatenatedDate);
            $appt->setStart(new FHIRInstant($startInstant));
        } elseif (!Utilities::isDateEmpty($dataRecord['pc_endDate']) && !empty($dataRecord['pc_startTime'])) {
            $concatenatedDate = $dataRecord['pc_endDate'] . ' ' . $dataRecord['pc_startTime'];
            $startInstant = UtilsService::getLocalDateAsUTC($concatenatedDate);
            $appt->setStart(new FHIRInstant($startInstant));
        }

        // if we have a start date and and end time we will use that
        if (!empty($dataRecord['pc_eventDate']) && !empty($dataRecord['pc_endTime'])) {
            $concatenatedDate = $dataRecord['pc_eventDate'] . ' ' . $dataRecord['pc_endTime'];
            $endInstant = UtilsService::getLocalDateAsUTC($concatenatedDate);
            $appt->setEnd(new FHIRInstant($endInstant));
        } elseif (!empty($dataRecord['pc_endDate']) && !empty($dataRecord['pc_endTime'])) {
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
     * Parses a FHIR Appointment resource, returning the equivalent OpenEMR record.
     *
     * @param FHIRDomainResource $fhirResource The source FHIR resource
     * @return array a mapped OpenEMR data record
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRAppointment)) {
            throw new \InvalidArgumentException(
                'Expected FHIRAppointment resource, got ' . $fhirResource::class
            );
        }

        // Use jsonSerialize() to get a normalized array representation since
        // the FHIR R4 library does not deeply hydrate nested objects
        $json = $fhirResource->jsonSerialize();
        $data = [];

        // status -> pc_apptstatus (reverse the status mapping from parseOpenEMRRecord)
        if (!empty($json['status'])) {
            $data['pc_apptstatus'] = $this->mapFhirStatusToOpenEmr($json['status']);
        } else {
            $data['pc_apptstatus'] = '-'; // default to pending/proposed
        }

        // appointmentType[0].coding[0].code -> pc_catid (look up by pc_constant_id)
        if (!empty($json['appointmentType']['coding'][0]['code'])) {
            $constantId = $json['appointmentType']['coding'][0]['code'];
            $catId = $this->lookupCategoryByConstantId($constantId);
            if ($catId !== false) {
                $data['pc_catid'] = $catId;
            }
        }

        // Default pc_title from appointmentType display or "Office Visit"
        if (!empty($json['appointmentType']['coding'][0]['display'])) {
            $data['pc_title'] = $json['appointmentType']['coding'][0]['display'];
        } else {
            $data['pc_title'] = 'Office Visit';
        }

        // Parse participants - Patient, Practitioner, Location
        if (!empty($json['participant']) && is_array($json['participant'])) {
            foreach ($json['participant'] as $participant) {
                if (empty($participant['actor']['reference'])) {
                    continue;
                }
                $reference = (string) $participant['actor']['reference'];
                $parsed = UtilsService::parseReferenceString($reference);

                if (empty($parsed['uuid']) || empty($parsed['type'])) {
                    continue;
                }

                // Reject malformed UUIDs before touching UuidRegistry — uuidToBytes()
                // throws on invalid input, which would surface as a 500 to the client.
                if (!UuidRegistry::isValidStringUUID($parsed['uuid'])) {
                    continue;
                }

                if ($parsed['type'] === 'Patient') {
                    $data['puuid'] = $parsed['uuid'];
                    // Resolve patient uuid to pid
                    $puuidBytes = UuidRegistry::uuidToBytes($parsed['uuid']);
                    $pid = BaseService::getIdByUuid($puuidBytes, 'patient_data', 'pid');
                    if ($pid !== false) {
                        $data['pid'] = $pid;
                    }
                } elseif ($parsed['type'] === 'Practitioner' || $parsed['type'] === 'Person') {
                    $providerUuidBytes = UuidRegistry::uuidToBytes($parsed['uuid']);
                    $providerId = BaseService::getIdByUuid($providerUuidBytes, 'users', 'id');
                    if ($providerId !== false) {
                        $data['pc_aid'] = $providerId;
                    }
                } elseif ($parsed['type'] === 'Location') {
                    $facilityUuidBytes = UuidRegistry::uuidToBytes($parsed['uuid']);
                    $facilityId = BaseService::getIdByUuid($facilityUuidBytes, 'facility', 'id');
                    if ($facilityId !== false) {
                        $data['pc_facility'] = $facilityId;
                    }
                }
            }
        }

        // start -> pc_eventDate (Y-m-d) + pc_startTime (H:i)
        if (!empty($json['start']) && is_string($json['start'])) {
            $startDt = date_create_immutable($json['start']);
            if ($startDt !== false) {
                $data['pc_eventDate'] = $startDt->format('Y-m-d');
                $data['pc_startTime'] = $startDt->format('H:i');
            }
        }

        // end -> calculate pc_duration from start/end difference (in seconds)
        if (
            !empty($json['start']) && is_string($json['start'])
            && !empty($json['end']) && is_string($json['end'])
        ) {
            $startDt = date_create_immutable($json['start']);
            $endDt = date_create_immutable($json['end']);
            if ($startDt !== false && $endDt !== false) {
                $data['pc_duration'] = $endDt->getTimestamp() - $startDt->getTimestamp();
            }
        }

        // comment -> pc_hometext
        $data['pc_hometext'] = !empty($json['comment']) ? $json['comment'] : '';

        // Default pc_billing_location to pc_facility if not set
        if (!isset($data['pc_billing_location']) && isset($data['pc_facility'])) {
            $data['pc_billing_location'] = $data['pc_facility'];
        }

        return $data;
    }

    /**
     * Maps a FHIR Appointment status code to an OpenEMR appointment status code.
     *
     * @param string $fhirStatus The FHIR status code
     * @return string The OpenEMR appointment status code
     */
    private function mapFhirStatusToOpenEmr(string $fhirStatus): string
    {
        return match ($fhirStatus) {
            'proposed' => '-',
            'pending' => '^',
            'booked' => '*',
            'arrived' => '@',
            'fulfilled' => '>',
            'cancelled' => 'x',
            'noshow' => '?',
            'checked-in' => '<',
            'waitlist' => 'CALL',
            default => '-',
        };
    }

    /**
     * Looks up a calendar category ID by its constant_id value.
     *
     * @param string $constantId The pc_constant_id to look up
     * @return int|false The pc_catid or false if not found
     */
    private function lookupCategoryByConstantId(string $constantId)
    {
        $result = QueryUtils::querySingleRow(
            "SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_constant_id = ? AND pc_active = 1",
            [$constantId]
        );
        if (!empty($result['pc_catid'])) {
            return (int) $result['pc_catid'];
        }
        return false;
    }

    /**
     * Inserts an OpenEMR record into the system.
     *
     * @param array $openEmrRecord The OpenEMR record to insert
     * @return ProcessingResult
     */
    protected function insertOpenEMRRecord($openEmrRecord)
    {
        $processingResult = new ProcessingResult();

        $pid = $openEmrRecord['pid'] ?? 0;
        unset($openEmrRecord['pid']);
        unset($openEmrRecord['puuid']);

        // Fall back to the first active facility if none was provided via FHIR.
        // Appointments require pc_facility and pc_billing_location, but FHIR
        // Appointment resources don't always include a Location participant.
        if (empty($openEmrRecord['pc_facility'])) {
            $defaultFacilityId = $this->getDefaultFacilityId();
            if ($defaultFacilityId !== null) {
                $openEmrRecord['pc_facility'] = $defaultFacilityId;
            }
        }
        if (empty($openEmrRecord['pc_billing_location']) && !empty($openEmrRecord['pc_facility'])) {
            $openEmrRecord['pc_billing_location'] = $openEmrRecord['pc_facility'];
        }

        // Default pc_catid if not provided (required by validator)
        if (empty($openEmrRecord['pc_catid'])) {
            $openEmrRecord['pc_catid'] = 9; // Office Visit is typically 9
        }

        // Default pc_duration if not provided (validator requires it)
        if (empty($openEmrRecord['pc_duration'])) {
            $openEmrRecord['pc_duration'] = 900; // 15 minutes default
        }

        // Validate that required fields are present
        $validationResult = $this->appointmentService->validate($openEmrRecord);
        if (!$validationResult->isValid()) {
            $processingResult->setValidationMessages($validationResult->getMessages());
            return $processingResult;
        }

        $insertId = $this->appointmentService->insert($pid, $openEmrRecord);
        if ($insertId) {
            // Fetch the created appointment to return full data
            $appointment = $this->appointmentService->getAppointment($insertId);
            if (!empty($appointment)) {
                $processingResult->addData($appointment[0]);
            } else {
                $processingResult->addData(['pc_eid' => $insertId]);
            }
        } else {
            $processingResult->addInternalError("Failed to insert appointment record");
        }

        return $processingResult;
    }

    /**
     * Returns the first active facility id, or null if none exist.
     * Used as a fallback when a FHIR Appointment doesn't include a Location
     * participant but the underlying service requires pc_facility.
     *
     * @return int|null
     */
    private function getDefaultFacilityId(): ?int
    {
        $facility = QueryUtils::querySingleRow(
            "SELECT id FROM facility WHERE inactive = 0 ORDER BY id ASC LIMIT 1",
            []
        );
        if (!empty($facility['id'])) {
            return (int) $facility['id'];
        }
        // Fall back to any facility
        $facility = QueryUtils::querySingleRow(
            "SELECT id FROM facility ORDER BY id ASC LIMIT 1",
            []
        );
        if (!empty($facility['id'])) {
            return (int) $facility['id'];
        }
        return null;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
    * @return ProcessingResult OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->appointmentService->search($openEMRSearchParameters, true);
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
