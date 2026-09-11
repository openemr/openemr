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
use Particle\Validator\ValidationResult;

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
     * Fallback appointment category title when the FHIR Appointment carries no
     * appointmentType display. Passed through xl_appt_category() for translation.
     */
    private const DEFAULT_CATEGORY_TITLE = 'Office Visit';

    /**
     * Default openemr_postcalendar_categories.pc_catid used when the FHIR
     * Appointment carries no resolvable appointmentType. 9 is the stock
     * "Office Visit" category in the default category seed.
     */
    private const DEFAULT_CATEGORY_ID = 9;

    /**
     * Default appointment duration in seconds (15 minutes) when the FHIR
     * Appointment does not yield a start/end span. The AppointmentValidator
     * requires a non-empty pc_duration.
     */
    private const DEFAULT_DURATION_SECONDS = 900;

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
        // the FHIR R4 library does not deeply hydrate nested objects: the top
        // level is an array, but every value below it is still whatever the
        // request payload carried, so each read below narrows before using it.
        $json = $fhirResource->jsonSerialize();
        $data = [];

        // status -> pc_apptstatus (reverse the status mapping from parseOpenEMRRecord)
        $status = $json['status'] ?? null;
        $data['pc_apptstatus'] = is_string($status) && $status !== ''
            ? $this->mapFhirStatusToOpenEmr($status)
            : '-'; // default to pending/proposed

        // appointmentType[0].coding[0].code -> pc_catid (look up by pc_constant_id)
        $constantId = FhirPayloadReader::firstCodingValue($json['appointmentType'] ?? null, 'code');
        if ($constantId !== '') {
            $catId = $this->lookupCategoryByConstantId($constantId);
            if ($catId !== false) {
                $data['pc_catid'] = $catId;
            }
        }

        // Default pc_title from appointmentType display, else the translated
        // fallback category title.
        $typeDisplay = FhirPayloadReader::firstCodingValue($json['appointmentType'] ?? null, 'display');
        $data['pc_title'] = $typeDisplay !== ''
            ? $typeDisplay
            : \xl_appt_category(self::DEFAULT_CATEGORY_TITLE);

        // Authorization context for provider attribution: only callers holding admin/users may
        // attribute an appointment to a different provider. The check itself lives in
        // PractitionerAttributionPolicy, shared with Encounter, Immunization, MedicationRequest
        // and ServiceRequest. Unlike those, a rejected reference here is dropped rather than
        // thrown: pc_aid is optional and the upstream service supplies its own default, so a
        // scheduling client that names a colleague still gets its appointment.
        $attribution = new PractitionerAttributionPolicy($this->getSession());

        // Parse participants - Patient, Practitioner, Location
        $participants = $json['participant'] ?? null;
        if (is_array($participants)) {
            foreach ($participants as $participant) {
                $actor = is_array($participant) ? ($participant['actor'] ?? null) : null;
                $reference = is_array($actor) ? ($actor['reference'] ?? null) : null;
                if (!is_string($reference) || $reference === '') {
                    continue;
                }
                $parsed = UtilsService::parseReferenceString($reference);
                $referenceUuid = $parsed['uuid'] ?? null;
                $referenceType = $parsed['type'] ?? null;

                if (
                    !is_string($referenceUuid) || $referenceUuid === ''
                    || !is_string($referenceType) || $referenceType === ''
                ) {
                    continue;
                }

                // Reject malformed UUIDs before touching UuidRegistry — uuidToBytes()
                // throws on invalid input, which would surface as a 500 to the client.
                if (!UuidRegistry::isValidStringUUID($referenceUuid)) {
                    continue;
                }

                if ($referenceType === 'Patient') {
                    // An appointment stores exactly one pid, and the controller's
                    // patient-compartment check reads the first Patient reference in the
                    // payload. Letting a later participant overwrite the earlier one would
                    // mean a patient-scoped caller could list their own patient first to
                    // satisfy that check and have the appointment written for a second,
                    // unauthorized patient. Conflicting references are rejected instead.
                    $existingPuuid = $data['puuid'] ?? null;
                    if (is_string($existingPuuid) && strcasecmp($existingPuuid, $referenceUuid) !== 0) {
                        throw new \InvalidArgumentException(
                            'Appointment.participant carries more than one distinct Patient reference'
                        );
                    }
                    $data['puuid'] = $referenceUuid;
                    // Resolve patient uuid to pid
                    $puuidBytes = UuidRegistry::uuidToBytes($referenceUuid);
                    $pid = BaseService::getIdByUuid($puuidBytes, 'patient_data', 'pid');
                    if ($pid !== false) {
                        $data['pid'] = $pid;
                    }
                } elseif ($referenceType === 'Practitioner' || $referenceType === 'Person') {
                    $providerUuidBytes = UuidRegistry::uuidToBytes($referenceUuid);
                    $providerId = BaseService::getIdByUuid($providerUuidBytes, 'users', 'id');
                    // Only honour the assignment if the caller has admin/users
                    // OR is assigning the appointment to themselves.
                    if ($providerId !== false && $attribution->mayAttributeTo($providerId)) {
                        $data['pc_aid'] = $providerId;
                    }
                } elseif ($referenceType === 'Location') {
                    $facilityUuidBytes = UuidRegistry::uuidToBytes($referenceUuid);
                    $facilityId = BaseService::getIdByUuid($facilityUuidBytes, 'facility', 'id');
                    // Honour any resolvable facility — the patients/appt ACL
                    // already gates *who* can schedule, and FHIR R4 lets the
                    // serviceProvider be any Location. Admin-only restrictions
                    // here would prevent clinical staff from scheduling their
                    // own appointments at the facilities they operate in.
                    if ($facilityId !== false) {
                        $data['pc_facility'] = $facilityId;
                    }
                }
            }
        }

        // Appointment.start / .end are FHIR `instant`, so a timezone is always
        // present and partial precision is not legal. FhirDateTimeParser rejects
        // anything else with an InvalidArgumentException, which the controller
        // turns into a 400 rather than writing a fabricated date.
        $startDt = FhirDateTimeParser::toDateTimeImmutable($json['start'] ?? null, 'Appointment.start');
        $endDt = FhirDateTimeParser::toDateTimeImmutable($json['end'] ?? null, 'Appointment.end');

        // start -> pc_eventDate (Y-m-d) + pc_startTime (H:i)
        if ($startDt !== null) {
            $data['pc_eventDate'] = $startDt->format('Y-m-d');
            $data['pc_startTime'] = $startDt->format('H:i');
        }

        // end -> calculate pc_duration from start/end difference (in seconds)
        if ($startDt !== null && $endDt !== null) {
            $data['pc_duration'] = $endDt->getTimestamp() - $startDt->getTimestamp();
        }

        // comment -> pc_hometext. FHIR R4 Appointment.comment is a plain
        // string ("additional comments about the appointment") — strip any
        // markup at the write boundary so HTML never reaches storage. The
        // render sinks (printed_fee_sheet etc.) also escape, but defense in
        // depth: other render paths in the legacy UI may render raw.
        $comment = $json['comment'] ?? null;
        $commentRaw = is_string($comment) ? $comment : '';
        $data['pc_hometext'] = $commentRaw === '' ? '' : strip_tags($commentRaw);

        // pc_billing_location is not carried by FHIR Appointment; default it to the
        // facility resolved from serviceProvider so the validator's numeric requirement
        // is satisfied without inventing a location.
        if (isset($data['pc_facility'])) {
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
        $catId = is_array($result) ? ($result['pc_catid'] ?? null) : null;
        if (is_numeric($catId) && (int) $catId > 0) {
            return (int) $catId;
        }
        return false;
    }

    /**
     * Inserts an OpenEMR record into the system.
     *
     * @param mixed $openEmrRecord The parsed record from parseFhirResource()
     * @return ProcessingResult
     */
    protected function insertOpenEMRRecord($openEmrRecord)
    {
        if (!is_array($openEmrRecord)) {
            throw new \InvalidArgumentException('Expected a parsed OpenEMR Appointment record array');
        }

        $processingResult = new ProcessingResult();

        // parseFhirResource() sets pid only when a Patient participant reference resolves.
        // Falling back to 0 would create an appointment orphaned from every patient and
        // invisible to patient-scoped reads, so this is rejected the same way a missing
        // serviceProvider is below.
        $pidRaw = $openEmrRecord['pid'] ?? null;
        if (!is_numeric($pidRaw) || (int) $pidRaw <= 0) {
            $processingResult->setValidationMessages([
                'participant' => 'Appointment requires a resolvable Patient participant reference',
            ]);
            return $processingResult;
        }
        $pid = (int) $pidRaw;
        unset($openEmrRecord['pid']);
        unset($openEmrRecord['puuid']);

        // Require an explicit facility from the FHIR caller — silently
        // picking the first row would attribute the appointment to an arbitrary
        // facility (potentially the wrong tenant in a multi-site deployment).
        // Callers must supply a serviceProvider Reference to a Location.
        $facilityId = $openEmrRecord['pc_facility'] ?? null;
        if (!is_numeric($facilityId) || (int) $facilityId <= 0) {
            $processingResult->setValidationMessages([
                'serviceProvider' => 'Appointment.serviceProvider (a Location reference) is required',
            ]);
            return $processingResult;
        }
        $billingLocation = $openEmrRecord['pc_billing_location'] ?? null;
        if (!is_numeric($billingLocation) || (int) $billingLocation <= 0) {
            $openEmrRecord['pc_billing_location'] = $facilityId;
        }

        // Default pc_catid if not provided (required by validator)
        $catId = $openEmrRecord['pc_catid'] ?? null;
        if (!is_numeric($catId) || (int) $catId <= 0) {
            $openEmrRecord['pc_catid'] = self::DEFAULT_CATEGORY_ID;
        }

        // Default pc_duration if not provided (validator requires it)
        $duration = $openEmrRecord['pc_duration'] ?? null;
        if (!is_numeric($duration) || (int) $duration <= 0) {
            $openEmrRecord['pc_duration'] = self::DEFAULT_DURATION_SECONDS;
        }

        // Validate that required fields are present
        // AppointmentService::validate() is untyped; it returns Particle's ValidationResult.
        $validationResult = $this->appointmentService->validate($openEmrRecord);
        if ($validationResult instanceof ValidationResult && !$validationResult->isValid()) {
            $processingResult->setValidationMessages($validationResult->getMessages());
            return $processingResult;
        }

        $insertId = $this->appointmentService->insert($pid, $openEmrRecord);
        if ($insertId) {
            // Fetch the created appointment to return full data
            $appointment = $this->appointmentService->getAppointment($insertId);
            if (is_array($appointment) && isset($appointment[0])) {
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
