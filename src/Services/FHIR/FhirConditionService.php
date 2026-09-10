<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\ConditionService;
use OpenEMR\Services\FHIR\Condition\FhirConditionEncounterDiagnosisService;
use OpenEMR\Services\FHIR\Condition\FhirConditionHealthConcernService;
use OpenEMR\Services\FHIR\Condition\FhirConditionProblemListItemService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCodeTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;
use Psr\Log\LoggerInterface;

/**
 * FHIR Condition Service
 *
 * @package            OpenEMR
 * @link               https://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirConditionService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;
    use MappedServiceCodeTrait;
    use SystemLoggerAwareTrait;

    /**
     * @var ConditionService
     */
    private $conditionService;

    private FhirConditionProblemListItemService $problemListItemService;

    public function __construct()
    {
        parent::__construct();
        $this->problemListItemService = new FhirConditionProblemListItemService();
        $this->addMappedService(new FhirConditionEncounterDiagnosisService());
        $this->addMappedService($this->problemListItemService);
        $this->addMappedService(new FhirConditionHealthConcernService());
        $this->conditionService = new ConditionService();
    }

    /**
     * Re-emits a stored row as a FHIR resource. FhirServiceBase::update() calls this to build
     * the body of a PUT response, so leaving it on the empty trait answers a successful update
     * with a null body. The write path runs through ConditionService, which only ever writes
     * `lists` rows of type 'medical_problem', so the problem-list-item mapping is the one that
     * re-emits them.
     *
     * @param array<array-key, mixed> $dataRecord
     * @param bool $encode
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        return $this->problemListItemService->parseOpenEMRRecord($dataRecord, $encode);
    }

    public function setSystemLogger(LoggerInterface $systemLogger): void
    {
        $this->logger = $systemLogger;
        foreach ($this->getMappedServices() as $service) {
            $service->setSystemLogger($systemLogger);
        }
    }

    /**
     * Returns an array mapping FHIR Condition Resource search parameters to OpenEMR Condition search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('condition_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated_time']);
    }

    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        try {
            if (isset($puuidBind)) {
                $field = $this->getPatientContextSearchField();
                $fhirSearchParameters[$field->getName()] = $puuidBind;
            }

            $servicesMap = [];
            $services = [];
            if (isset($fhirSearchParameters['category'])) {
                /**
                 * @var TokenSearchField
                 */
                $category = $fhirSearchParameters['category'];

                $catServices = $this->getServiceListForCategory(
                    // TODO: @adunsulag should we put inside TokenSearchValue the exploding of the comma separated values?
                    new TokenSearchField('category', explode(",", $category))
                );
                foreach ($catServices as $service) {
                    $servicesMap[$service::class] = $service;
                }
                $services = $servicesMap;
            }
            if (empty($services)) {
                $services = $this->getMappedServices();
            }
            $fhirSearchResult = $this->searchServices($services, $fhirSearchParameters, $puuidBind);
        } catch (SearchFieldException $exception) {
            $systemLogger = $this->getSystemLogger();
            $systemLogger->error("exception thrown", ['exception' => $exception,
                'field' => $exception->getField()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    /**
     * Parses a FHIR Condition resource, returning the equivalent OpenEMR record.
     *
     * @param FHIRDomainResource $fhirResource The source FHIR resource
     * @return array a mapped OpenEMR data record
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRCondition)) {
            throw new \InvalidArgumentException(
                'Expected FHIRCondition resource, got ' . $fhirResource::class
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

        // Category -> subtype
        $categories = $json['category'] ?? null;
        $subtype = FhirPayloadReader::firstConceptCode($categories);
        if ($subtype !== '') {
            $data['subtype'] = $subtype;
        }

        // Subject -> puuid
        $subjectRef = FhirPayloadReader::reference($json['subject'] ?? null);
        if ($subjectRef !== null) {
            $subjectUuid = UtilsService::parseReferenceString($subjectRef, 'Patient')['uuid'] ?? null;
            if (
                is_string($subjectUuid) && $subjectUuid !== ''
                && \OpenEMR\Common\Uuid\UuidRegistry::isValidStringUUID($subjectUuid)
            ) {
                $data['puuid'] = $subjectUuid;
            }
        }

        // Code -> title and diagnosis
        $code = $json['code'] ?? null;
        $codeCodings = FhirPayloadReader::codings($code);
        if ($codeCodings !== []) {
            $codeTypesService = new CodeTypesService();
            $diagnosisParts = [];
            foreach ($codeCodings as $coding) {
                $systemValue = $coding['system'] ?? null;
                $system = is_string($systemValue) ? $systemValue : '';
                $codeValue = $coding['code'] ?? null;
                if (is_scalar($codeValue) && $codeValue !== '' && $codeValue !== false) {
                    $diagnosisParts[] = $codeTypesService->getOpenEMRCodeForSystemAndCode($system, $codeValue);
                }
                $display = $coding['display'] ?? null;
                if (is_string($display) && $display !== '' && !isset($data['title'])) {
                    $data['title'] = $display;
                }
            }
            if ($diagnosisParts !== []) {
                $data['diagnosis'] = implode(';', $diagnosisParts);
            }
        }
        $codeText = is_array($code) ? ($code['text'] ?? null) : null;
        if (!isset($data['title']) && is_string($codeText) && $codeText !== '') {
            $data['title'] = $codeText;
        }

        // ClinicalStatus -> outcome and occurrence
        $clinicalStatus = FhirPayloadReader::firstCodingCode($json['clinicalStatus'] ?? null);
        if ($clinicalStatus !== '') {
            $data['outcome'] = match ($clinicalStatus) {
                'resolved' => '1',
                'recurrence' => '0',
                default => '0',
            };
            if ($clinicalStatus === 'recurrence') {
                $data['occurrence'] = '2';
            }
        }

        // VerificationStatus -> verification
        $verification = FhirPayloadReader::firstCodingCode($json['verificationStatus'] ?? null);
        if ($verification !== '') {
            $data['verification'] = $verification;
        }

        // onsetDateTime -> begdate (ConditionValidator expects Y-m-d).
        // Partial precision (YYYY, YYYY-MM) is rejected rather than widened:
        // lists.begdate is a DATE column and a year-only onset cannot be stored
        // faithfully, so the caller gets a 400 instead of a fabricated day.
        $begdate = FhirDateTimeParser::toDbDate($json['onsetDateTime'] ?? null, 'Condition.onsetDateTime');
        if ($begdate !== null) {
            $data['begdate'] = $begdate;
        }

        // abatementDateTime -> enddate
        $enddate = FhirDateTimeParser::toDbDate($json['abatementDateTime'] ?? null, 'Condition.abatementDateTime');
        if ($enddate !== null) {
            $data['enddate'] = $enddate;
        }

        // Note -> comments
        $notes = $json['note'] ?? null;
        $firstNote = is_array($notes) ? ($notes[0] ?? null) : null;
        $noteText = is_array($firstNote) ? ($firstNote['text'] ?? null) : null;
        if (is_string($noteText) && $noteText !== '') {
            $data['comments'] = $noteText;
        }

        return $data;
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
            throw new \InvalidArgumentException('Expected a parsed OpenEMR Condition record array');
        }

        return $this->conditionService->insert($openEmrRecord);
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
        return $this->conditionService->update($fhirResourceId, $updatedOpenEMRRecord);
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
        $profileSets = [];
        $profileSets[] = $this->getProfileForVersions(FhirConditionProblemListItemService::USCGI_PROFILE_URI_3_1_1, ['', '3.1.1']);
        $profileSets[] = $this->getProfileForVersions(FhirConditionEncounterDiagnosisService::USCGI_PROFILE_ENCOUNTER_DIAGNOSIS_URI, $this->getSupportedVersions());
        $profileSets[] = $this->getProfileForVersions(FhirConditionProblemListItemService::USCGI_PROFILE_PROBLEMS_HEALTH_CONCERNS_URI, $this->getSupportedVersions());
        $profiles = array_merge(...$profileSets);
        return $profiles;
    }

    protected function getSupportedVersions(): array
    {
        return ['', '7.0.0', '8.0.0'];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}
