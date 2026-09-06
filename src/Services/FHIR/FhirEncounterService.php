<?php

/**
 * FhirEncounterService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson snielson@discoverandchange.com
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020, 2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2020 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterHospitalization;
use OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterLocation;
use OpenEMR\FHIR\R4\FHIRResource\FHIREncounter\FHIREncounterParticipant;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirEncounterService extends FhirServiceBase implements
    IFhirExportableResourceService,
    IPatientCompartmentResourceService,
    IResourceUSCIGProfileService
{
    use PatientSearchTrait;
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    public const ENCOUNTER_STATUS_FINISHED = "finished";

    public const ENCOUNTER_TYPE_CHECK_UP = "185349003";
    public const ENCOUNTER_TYPE_CHECK_UP_DESCRIPTION = "Encounter for check up (procedure)";

    public const ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER = "PPRF";
    public const ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER_TEXT = "Primary Performer";

    public const ENCOUNTER_PARTICIPANT_TYPE_REFERRER = "REF";
    public const ENCOUNTER_PARTICIPANT_TYPE_REFERRER_TEXT = "Referrer";
    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-encounter';


    /**
     * @var EncounterService
     */
    private $encounterService;

    public function __construct()
    {
        parent::__construct();
        $this->encounterService = new EncounterService();
    }

    /**
     * Returns an array mapping FHIR Encounter Resource search parameters to OpenEMR Encounter search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition(
                '_id',
                SearchFieldType::TOKEN,
                [
                    new ServiceField(
                        'euuid',
                        ServiceField::TYPE_UUID
                    )
                ]
            ),
            'patient' => $this->getPatientContextSearchField(),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_update']);
    }

    /**
     * Parses an OpenEMR patient record, returning the equivalent FHIR Patient Resource
     * https://build.fhir.org/ig/HL7/US-Core-R4/StructureDefinition-us-core-encounter-definitions.html
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIREncounter
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $encounterResource = new FHIREncounter();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated((new \DateTime($dataRecord['last_update']) )->format(DATE_ATOM)); // stored as utc
        $encounterResource->setMeta($meta);

        $id = new FhirId();
        $id->setValue($dataRecord['euuid']);
        $encounterResource->setId($id);

        // identifier - required
        $identifier = new FHIRIdentifier();
        $identifier->setValue($dataRecord['euuid']);
        // the system is a unique urn
        $identifier->setSystem("urn:uuid:" . strtolower((string) $dataRecord['euuid']));
        $encounterResource->addIdentifier($identifier);

        // status - required
        $status = new FHIRCode(self::ENCOUNTER_STATUS_FINISHED);
        $encounterResource->setStatus($status);

        // class
        if (!empty($dataRecord['class_code'])) {
            $class = new FHIRCoding();
            $class->setSystem(FhirCodeSystemConstants::HL7_V3_ACT_CODE);
            $class->setCode($dataRecord['class_code']);
            $class->setDisplay($dataRecord['class_title']);
            $encounterResource->setClass($class);
        } else {
            $encounterResource->setClass(UtilsService::createDataAbsentUnknownCodeableConcept());
        }

        // TODO: @adunsulag check with @brady.miller and find out if this really is the only possible encounter type
        // ...  it was here originally
        $type = UtilsService::createCodeableConcept(
            [self::ENCOUNTER_TYPE_CHECK_UP => [
                'code' => self::ENCOUNTER_TYPE_CHECK_UP
                , "description" => self::ENCOUNTER_TYPE_CHECK_UP_DESCRIPTION
                , "system" => FhirCodeSystemConstants::SNOMED_CT
            ]]
        );
        $encounterResource->addType($type);

        // subject - required
        if (!empty($dataRecord['puuid'])) {
            $encounterResource->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        } else {
            $encounterResource->setSubject(UtilsService::createDataMissingExtension());
        }

        // participant - must support
        if (!empty($dataRecord['provider_uuid'])) {
            $participant = new FHIREncounterParticipant();
            $participant->setIndividual(
                UtilsService::createRelativeReference(
                    "Practitioner",
                    $dataRecord['provider_uuid']
                )
            );
            $period = new FHIRPeriod();
            $period->setStart(UtilsService::getLocalDateAsUTC($dataRecord['date']));
            $participant->setPeriod($period);

            $participantType = UtilsService::createCodeableConcept([
                self::ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER =>
                [
                    'code' => self::ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER
                    ,'description' => self::ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER_TEXT
                    ,'system' => FhirCodeSystemConstants::HL7_PARTICIPATION_TYPE
                ]
            ]);
            $participant->addType($participantType);
            $encounterResource->addParticipant($participant);
        }

        // referring provider
        if (!empty($dataRecord['referrer_uuid'])) {
            $participant = new FHIREncounterParticipant();
            $participant->setIndividual(
                UtilsService::createRelativeReference(
                    "Practitioner",
                    $dataRecord['referrer_uuid']
                )
            );
            $period = new FHIRPeriod();
            $period->setStart(UtilsService::getLocalDateAsUTC($dataRecord['date']));
            $participant->setPeriod($period);

            $participantType = UtilsService::createCodeableConcept([
                self::ENCOUNTER_PARTICIPANT_TYPE_REFERRER =>
                [
                    'code' => self::ENCOUNTER_PARTICIPANT_TYPE_REFERRER
                    ,'description' => self::ENCOUNTER_PARTICIPANT_TYPE_REFERRER_TEXT
                    ,'system' => FhirCodeSystemConstants::HL7_PARTICIPATION_TYPE
                ]
            ]);
            $participant->addType($participantType);
            $encounterResource->addParticipant($participant);
        }

        // period - must support
        if (!empty($dataRecord['date'])) {
            $period = new FHIRPeriod();
            $period->setStart(UtilsService::getLocalDateAsUTC($dataRecord['date']));
            $encounterResource->setPeriod($period);
        }

        // reasonCode - must support OR must support reasonReference
        if (!empty($dataRecord['reason'])) {
            // Note: that we use the encounter textual representation for the reason here which is just fine as ccda
            // uses a textual representation of this.  According to HL7 chat this is just fine as epoch and
            // other systems do it this way
            // @see https://chat.fhir.org/#narrow/stream/179175-argonaut/topic/Encounter.20Reason.20For.20Visit
            // (beware of link rot)
            $reason = new FHIRCodeableConcept();
            $reasonText = $dataRecord['reason'] ?? "";
            $reason->setText(trim((string) $reasonText));
            $encounterResource->addReasonCode($reason);
        }
        // hospitalization - must support

        // hospitalization.dischargeDisposition - must support
        if (!empty($dataRecord['discharge_disposition'])) {
            $code = $dataRecord['discharge_disposition'];
            $text = $dataRecord['discharge_disposition_text'];

            $hospitalization = new FHIREncounterHospitalization();
            $hospitalization->setDischargeDisposition(UtilsService::createCodeableConcept(
                [
                    $code => [
                        'code' => $text,
                        'description' => $text,
                        'system' => FhirCodeSystemConstants::HL7_DISCHARGE_DISPOSITION
                    ]
                ]
            ));
            $encounterResource->setHospitalization($hospitalization);
        }

        // SHALL support either location.location OR serviceProvider
        // however ONC inferno requires both serviceProvider AND location.location
        // location.location - must support
        // serviceProvider - must support
        if (!empty($dataRecord['facility_uuid'])) {
            $encounterResource->setServiceProvider(
                UtilsService::createRelativeReference(
                    'Organization',
                    $dataRecord['facility_uuid']
                )
            );

            // grab the facility location address
            if (!empty($dataRecord['facility_location_uuid'])) {
                $location = new FHIREncounterLocation();
                $location->setLocation(
                    UtilsService::createRelativeReference(
                        "Location",
                        $dataRecord['facility_location_uuid']
                    )
                );
                $encounterResource->addLocation($location);
            }
        }

        if ($encode) {
            return json_encode($encounterResource);
        } else {
            return $encounterResource;
        }
    }

    /**
     * Parses a FHIR Encounter resource, returning the equivalent OpenEMR record.
     *
     * @param FHIRDomainResource $fhirResource The source FHIR resource
     * @return array a mapped OpenEMR data record
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIREncounter)) {
            throw new \InvalidArgumentException(
                'Expected FHIREncounter resource, got ' . $fhirResource::class
            );
        }

        // Use jsonSerialize() to get a normalized array representation since
        // the FHIR R4 library does not deeply hydrate nested objects
        $json = $fhirResource->jsonSerialize();
        $data = [];

        $resourceId = $json['id'] ?? null;
        if (is_string($resourceId) && $resourceId !== '') {
            $data['uuid'] = $resourceId;
        }

        // Subject -> puuid (required in US Core)
        $subjectRef = $json['subject']['reference'] ?? null;
        if (is_string($subjectRef) && $subjectRef !== '') {
            $subjectUuid = UtilsService::parseReferenceString($subjectRef, 'Patient')['uuid'] ?? null;
            if (is_string($subjectUuid) && $subjectUuid !== '') {
                $data['puuid'] = $subjectUuid;
            }
        }

        // Class -> class_code (required in FHIR R4)
        $class = $json['class'] ?? null;
        $classCode = is_array($class) ? ($class['code'] ?? null) : null;
        if (is_string($classCode) && $classCode !== '') {
            $data['class_code'] = $classCode;
        }

        // Period -> date (normalize to Y-m-d H:i:s for database). An encounter
        // names a specific point in time, so partial precision is rejected. The
        // previous fallback wrote the raw unparseable string into a DATETIME
        // column; the parser raises a 400 instead.
        $period = $json['period'] ?? null;
        $encounterDate = FhirDateTimeParser::toDbDateTime(
            is_array($period) ? ($period['start'] ?? null) : null,
            'Encounter.period.start'
        );
        if ($encounterDate !== null) {
            $data['date'] = $encounterDate;
        }

        // Participant -> provider_uuid and referrer_uuid. FHIR R4 requires that
        // unresolvable references be rejected — silently skipping creates a
        // partial Encounter and tells the caller the write succeeded. We throw
        // InvalidArgumentException so the controller emits a 400 with a clear
        // OperationOutcome.
        $participants = $json['participant'] ?? null;
        if (is_array($participants)) {
            foreach ($participants as $idx => $participant) {
                $individual = is_array($participant) ? ($participant['individual'] ?? null) : null;
                $reference = is_array($individual) ? ($individual['reference'] ?? null) : null;
                if (!is_string($reference) || $reference === '') {
                    // No reference at all on a participant entry is a malformed
                    // resource — reject rather than silently dropping the entry.
                    throw new \InvalidArgumentException(
                        'Encounter.participant[' . (int) $idx . '].individual.reference is required'
                    );
                }
                $practitionerUuid = UtilsService::parseReferenceString($reference, 'Practitioner')['uuid'] ?? null;
                if (
                    !is_string($practitionerUuid) || $practitionerUuid === ''
                    || !\OpenEMR\Common\Uuid\UuidRegistry::isValidStringUUID($practitionerUuid)
                ) {
                    throw new \InvalidArgumentException(
                        'Encounter.participant[' . (int) $idx . '].individual.reference is not a valid Practitioner reference'
                    );
                }

                // Determine participant type from type codings
                $isPrimaryPerformer = false;
                $isReferrer = false;
                // $participant is provably an array here: reaching this line
                // required $individual, and therefore $participant, to be one.
                $participantTypes = $participant['type'] ?? null;
                foreach (is_array($participantTypes) ? $participantTypes : [] as $pType) {
                    $code = $this->firstCodingCode($pType);
                    if ($code === self::ENCOUNTER_PARTICIPANT_TYPE_PRIMARY_PERFORMER) {
                        $isPrimaryPerformer = true;
                    } elseif ($code === self::ENCOUNTER_PARTICIPANT_TYPE_REFERRER) {
                        $isReferrer = true;
                    }
                }

                if ($isReferrer) {
                    $data['referrer_uuid'] = $practitionerUuid;
                } elseif ($isPrimaryPerformer || !isset($data['provider_uuid'])) {
                    $data['provider_uuid'] = $practitionerUuid;
                }
            }
        }

        // ReasonCode -> reason
        $reasonCodes = $json['reasonCode'] ?? null;
        $reason = is_array($reasonCodes) ? ($reasonCodes[0] ?? null) : null;
        if (is_array($reason)) {
            $reasonText = $reason['text'] ?? null;
            $reasonDisplay = $this->firstCodingValue($reason, 'display');
            if (is_string($reasonText) && $reasonText !== '') {
                $data['reason'] = $reasonText;
            } elseif ($reasonDisplay !== '') {
                $data['reason'] = $reasonDisplay;
            }
        }

        // ServiceProvider -> facility_id (via Organization uuid)
        $serviceProviderRef = $json['serviceProvider']['reference'] ?? null;
        if (is_string($serviceProviderRef) && $serviceProviderRef !== '') {
            $organizationUuid = UtilsService::parseReferenceString($serviceProviderRef, 'Organization')['uuid'] ?? null;
            if (
                is_string($organizationUuid) && $organizationUuid !== ''
                && \OpenEMR\Common\Uuid\UuidRegistry::isValidStringUUID($organizationUuid)
            ) {
                $facilityUuidBytes = \OpenEMR\Common\Uuid\UuidRegistry::uuidToBytes($organizationUuid);
                $facilityId = $this->encounterService->getIdByUuid($facilityUuidBytes, 'facility', 'id');
                if ($facilityId) {
                    $data['facility_id'] = $facilityId;
                }
            }
        }

        // Hospitalization -> discharge_disposition
        $hospitalization = $json['hospitalization'] ?? null;
        $dischargeCode = $this->firstCodingCode(
            is_array($hospitalization) ? ($hospitalization['dischargeDisposition'] ?? null) : null
        );
        if ($dischargeCode !== '') {
            $data['discharge_disposition'] = $dischargeCode;
        }

        // Default pc_catid for new encounters (required by EncounterValidator)
        $data['pc_catid'] = $this->getDefaultEncounterCategoryId();

        return $data;
    }

    /**
     * Returns a field of a CodeableConcept's first coding entry.
     *
     * The FHIR R4 library does not hydrate nested elements, so what reaches
     * here is whatever the request payload carried; anything that is not a
     * string is reported as absent.
     *
     * @param mixed $codeableConcept
     * @param string $field The coding field to read ('code', 'display', ...)
     * @return string The field value, or '' when absent or not a string
     */
    private function firstCodingValue($codeableConcept, string $field): string
    {
        if (!is_array($codeableConcept)) {
            return '';
        }
        $coding = $codeableConcept['coding'] ?? null;
        $firstCoding = is_array($coding) ? ($coding[0] ?? null) : null;
        $value = is_array($firstCoding) ? ($firstCoding[$field] ?? null) : null;

        return is_string($value) ? $value : '';
    }

    /**
     * Returns the `code` of a CodeableConcept's first coding entry.
     *
     * @param mixed $codeableConcept
     * @return string The code, or '' when absent or not a string
     */
    private function firstCodingCode($codeableConcept): string
    {
        return $this->firstCodingValue($codeableConcept, 'code');
    }

    /**
     * Resolves the default encounter category id from the database rather than
     * hardcoding a site-specific numeric value.
     *
     * @return int
     */
    private function getDefaultEncounterCategoryId(): int
    {
        // The 'office_visit' constant is the well-known well-defined default
        // installed by every schema (sql/database.sql). Look it up by constant
        // id only — never fall back to "pick any active row" because that
        // returns an arbitrary category and would silently misattribute the
        // encounter type (also a fresh cross-tenant write path in any future
        // multi-site scoping). If the row is genuinely missing the schema
        // default of 5 is correct.
        $category = QueryUtils::querySingleRow(
            "SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_constant_id = ? LIMIT 1",
            ['office_visit']
        );
        $categoryId = is_array($category) ? ($category['pc_catid'] ?? null) : null;
        if (is_numeric($categoryId) && (int) $categoryId > 0) {
            return (int) $categoryId;
        }
        return 5;
    }

    /**
     * Inserts an OpenEMR record into the system.
     *
     * @param array $openEmrRecord The OpenEMR record to insert
     * @return ProcessingResult
     */
    protected function insertOpenEMRRecord($openEmrRecord)
    {
        $puuid = $openEmrRecord['puuid'] ?? '';
        unset($openEmrRecord['puuid']);

        // user and group are required by EncounterService::insertEncounter for addForm()
        $session = $this->getSession();
        if ($session !== null && ($openEmrRecord['user'] ?? '') === '') {
            $openEmrRecord['user'] = $session->get('authUser') ?? '';
        }
        if ($session !== null && ($openEmrRecord['group'] ?? '') === '') {
            $openEmrRecord['group'] = $session->get('authProvider') ?? '';
        }

        $this->resolveProviderUuids($openEmrRecord);

        // EncounterService::insertEncounter unconditionally reads
        // $data['provider_id'] / $data['user']. resolveProviderUuids only sets
        // provider_id when a participant performer was supplied; when the FHIR
        // payload has no participant we fall back to the authenticated user.
        if (!isset($openEmrRecord['provider_id'])) {
            $authUserIdRaw = $session?->get('authUserID');
            $openEmrRecord['provider_id'] = is_scalar($authUserIdRaw)
                ? (int) $authUserIdRaw
                : 0;
        }

        return $this->encounterService->insertEncounter($puuid, $openEmrRecord);
    }

    /**
     * Updates an existing OpenEMR record.
     *
     * @param string $fhirResourceId The OpenEMR record's FHIR Resource ID (uuid)
     * @param array $updatedOpenEMRRecord The updated OpenEMR record
     * @return ProcessingResult
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        $puuid = $updatedOpenEMRRecord['puuid'] ?? '';
        unset($updatedOpenEMRRecord['puuid']);

        // user and group are required by EncounterValidator for updates
        $session = $this->getSession();
        if ($session !== null && ($updatedOpenEMRRecord['user'] ?? '') === '') {
            $updatedOpenEMRRecord['user'] = $session->get('authUser') ?? '';
        }
        if ($session !== null && ($updatedOpenEMRRecord['group'] ?? '') === '') {
            $updatedOpenEMRRecord['group'] = $session->get('authProvider') ?? '';
        }

        $this->resolveProviderUuids($updatedOpenEMRRecord);

        return $this->encounterService->updateEncounter($puuid, $fhirResourceId, $updatedOpenEMRRecord);
    }

    /**
     * Resolves provider and referrer UUIDs to their numeric IDs.
     *
     * Authorization: a caller may only attribute an encounter to a provider
     * other than themselves if they hold the admin/users ACL. Without it the
     * write is rejected (InvalidArgumentException → 400) rather than silently
     * dropping the attribution. Silent demotion would tell the client the
     * write succeeded while billing/audit attributes the visit to a default
     * provider — worse than failing outright.
     *
     * @param array &$record The OpenEMR record to modify in place
     */
    private function resolveProviderUuids(array &$record): void
    {
        $session = $this->getSession();
        $authUserRaw = $session?->get('authUser');
        $authUser = is_string($authUserRaw) ? $authUserRaw : '';
        $authUserIdRaw = $session?->get('authUserID');
        $authUserId = is_scalar($authUserIdRaw) ? (string) $authUserIdRaw : '';
        $canAssignAnyProvider = $authUser !== ''
            && AclMain::aclCheckCore('admin', 'users', $authUser) !== false;

        $this->resolveSingleProviderField(
            $record,
            'provider_uuid',
            'provider_id',
            'Encounter.participant performer',
            $canAssignAnyProvider,
            $authUserId
        );
        $this->resolveSingleProviderField(
            $record,
            'referrer_uuid',
            'referring_provider_id',
            'Encounter.participant referrer',
            $canAssignAnyProvider,
            $authUserId
        );
    }

    /**
     * Shared helper for the two provider-attribution paths (performer and
     * referrer). Verifies the requested user exists, then enforces the
     * admin/users-or-self policy. Throws on policy violation rather than
     * silently dropping.
     *
     * @param array<string, mixed> &$record
     */
    private function resolveSingleProviderField(
        array &$record,
        string $uuidKey,
        string $idKey,
        string $fhirLabel,
        bool $canAssignAnyProvider,
        string $authUserId
    ): void {
        $uuid = $record[$uuidKey] ?? null;
        $existingId = $record[$idKey] ?? null;
        $alreadyResolved = is_numeric($existingId) && (int) $existingId > 0;
        if (!is_string($uuid) || $uuid === '' || $alreadyResolved) {
            unset($record[$uuidKey]);
            return;
        }
        if (!\OpenEMR\Common\Uuid\UuidRegistry::isValidStringUUID($uuid)) {
            unset($record[$uuidKey]);
            throw new \InvalidArgumentException($fhirLabel . ' reference is not a valid uuid');
        }
        $providerUuidBytes = \OpenEMR\Common\Uuid\UuidRegistry::uuidToBytes($uuid);
        $providerId = $this->encounterService->getIdByUuid($providerUuidBytes, 'users', 'id');
        unset($record[$uuidKey]);
        if ($providerId === false) {
            throw new \InvalidArgumentException($fhirLabel . ' reference could not be resolved');
        }
        if (!$canAssignAnyProvider && (string) $providerId !== $authUserId) {
            throw new \InvalidArgumentException(
                $fhirLabel . ' attribution to another practitioner requires admin/users'
            );
        }
        $record[$idKey] = $providerId;
    }

    public function createProvenanceResource($dataRecord = [], $encode = false)
    {
        if (!($dataRecord instanceof FHIREncounter)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $provenanceService = new FhirProvenanceService();
        $author = null;
        if (!empty($dataRecord->getParticipant())) {
            // grab the first one for author
            $participant = reset($dataRecord->getParticipant());
            $author = $participant->getIndividual() ?? null;
        }
        $provenance = $provenanceService->createProvenanceForDomainResource($dataRecord, $author);
        return $provenance;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($searchParam): ProcessingResult
    {
        return $this->encounterService->search($searchParam, true);
    }

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }
}
