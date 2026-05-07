<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
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

    public function __construct()
    {
        parent::__construct();
        $this->addMappedService(new FhirConditionEncounterDiagnosisService());
        $this->addMappedService(new FhirConditionProblemListItemService());
        $this->addMappedService(new FhirConditionHealthConcernService());
        $this->conditionService = new ConditionService();
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

        if (!empty($json['id'])) {
            $data['uuid'] = $json['id'];
        }

        // Category -> subtype
        if (!empty($json['category'][0]['coding'][0]['code'])) {
            $data['subtype'] = $json['category'][0]['coding'][0]['code'];
        }

        // Subject -> puuid
        $subjectRef = $json['subject']['reference'] ?? null;
        if (is_string($subjectRef) && $subjectRef !== '') {
            $parsed = UtilsService::parseReferenceString($subjectRef, 'Patient');
            if (!empty($parsed['uuid']) && \OpenEMR\Common\Uuid\UuidRegistry::isValidStringUUID($parsed['uuid'])) {
                $data['puuid'] = $parsed['uuid'];
            }
        }

        // Code -> title and diagnosis
        if (!empty($json['code']['coding'])) {
            $diagnosisParts = [];
            foreach ($json['code']['coding'] as $coding) {
                $system = $coding['system'] ?? '';
                $codeValue = $coding['code'] ?? '';
                $display = $coding['display'] ?? '';
                if (!empty($codeValue)) {
                    $prefix = match ($system) {
                        'http://snomed.info/sct' => 'SNOMED-CT',
                        'http://hl7.org/fhir/sid/icd-10-cm' => 'ICD10',
                        'http://hl7.org/fhir/sid/icd-9-cm' => 'ICD9',
                        default => $system,
                    };
                    $diagnosisParts[] = $prefix . ':' . $codeValue;
                }
                if (!empty($display) && empty($data['title'])) {
                    $data['title'] = $display;
                }
            }
            if (!empty($diagnosisParts)) {
                $data['diagnosis'] = implode(';', $diagnosisParts);
            }
        }
        if (empty($data['title']) && !empty($json['code']['text'])) {
            $data['title'] = $json['code']['text'];
        }

        // ClinicalStatus -> outcome and occurrence
        if (!empty($json['clinicalStatus']['coding'][0]['code'])) {
            $statusCode = $json['clinicalStatus']['coding'][0]['code'];
            $data['outcome'] = match ($statusCode) {
                'resolved' => '1',
                'recurrence' => '0',
                default => '0',
            };
            if ($statusCode === 'recurrence') {
                $data['occurrence'] = '2';
            }
        }

        // VerificationStatus -> verification
        if (!empty($json['verificationStatus']['coding'][0]['code'])) {
            $data['verification'] = $json['verificationStatus']['coding'][0]['code'];
        }

        // OnsetDateTime -> begdate (ConditionValidator expects Y-m-d)
        if (!empty($json['onsetDateTime'])) {
            $dateValue = $json['onsetDateTime'];
            if (strlen((string) $dateValue) > 10) {
                $dateValue = substr((string) $dateValue, 0, 10);
            }
            $data['begdate'] = $dateValue;
        }

        // AbatementDateTime -> enddate
        if (!empty($json['abatementDateTime'])) {
            $dateValue = $json['abatementDateTime'];
            if (strlen((string) $dateValue) > 10) {
                $dateValue = substr((string) $dateValue, 0, 10);
            }
            $data['enddate'] = $dateValue;
        }

        // Note -> comments
        if (!empty($json['note'][0]['text'])) {
            $data['comments'] = $json['note'][0]['text'];
        }

        return $data;
    }

    /**
     * Inserts an OpenEMR record into the system.
     *
     * @param array $openEmrRecord The OpenEMR record to insert
     * @return ProcessingResult
     */
    protected function insertOpenEMRRecord($openEmrRecord)
    {
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
