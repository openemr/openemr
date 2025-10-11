<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\Services\FHIR\Condition\FhirConditionEncounterDiagnosisService;
use OpenEMR\Services\FHIR\Condition\FhirConditionProblemsHealthConcernService;
use OpenEMR\Services\ConditionService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCodeTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\ReferenceSearchField;
use OpenEMR\Services\Search\ReferenceSearchValue;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Condition Service
 *
 * @package            OpenEMR
 * @link               http://www.open-emr.org
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
        $this->addMappedService(new FhirConditionProblemsHealthConcernService());
        $this->conditionService = new ConditionService();
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
            if (isset($fhirSearchParameters['_id'])) {
                $result = $this->populateSurrogateSearchFieldsForUUID($fhirSearchParameters['_id'], $fhirSearchParameters);
                if ($result instanceof ProcessingResult) { // failed to populate so return the results
                    return $result;
                }
            }

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
                    new TokenSearchField('category', $category)
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
            $systemLogger = new SystemLogger();
            $systemLogger->errorLogCaller("exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField(), 'trace' => $exception->getTraceAsString()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

//    /**
//     * Parses an OpenEMR condition record, returning the equivalent FHIR Condition Resource
//     *
//     * @param  array   $dataRecord The source OpenEMR data record
//     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
//     * @return FHIRCondition
//     */
//    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
//    {
//        $conditionResource = new FHIRCondition();
//
//        $meta = new FHIRMeta();
//        $meta->setVersionId('1');
//        if (!empty($dataRecord['last_updated_time'])) {
//            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['last_updated_time']));
//        } else {
//            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
//        }
//        foreach ($this->getProfileURIs() as $profileUri) {
//            $meta->addProfile($profileUri);
//        }
//        $conditionResource->setMeta($meta);
//
//        $id = new FHIRId();
//        $id->setValue($dataRecord['uuid']);
//        $conditionResource->setId($id);
//
//        // ONC requirements
//        $this->populateClinicalStatus($dataRecord, $conditionResource);
//        $this->populateCategory($dataRecord, $conditionResource);
//        $this->populateVerificationStatus($dataRecord, $conditionResource);
//        $this->populateCode($dataRecord, $conditionResource);
//        $this->populateSubject($dataRecord, $conditionResource);
//
//        $this->populateEncounter($dataRecord, $conditionResource);
//
//
//        if ($encode) {
//            return json_encode($conditionResource);
//        } else {
//            return $conditionResource;
//        }
//    }
//
//    private function populateEncounter($dataRecord, FHIRCondition $conditionResource)
//    {
//        if (isset($dataRecord['encounter_uuid'])) {
//            $encounter = new FHIRReference();
//            $encounter->setReference('Encounter/' . $dataRecord['encounter_uuid']);
//            $conditionResource->setEncounter($encounter);
//        }
//    }
//
//    private function populateSubject($dataRecord, FHIRCondition $conditionResource)
//    {
//        if (isset($dataRecord['puuid'])) {
//            $patient = new FHIRReference();
//            $patient->setReference('Patient/' . $dataRecord['puuid']);
//            $conditionResource->setSubject($patient);
//        }
//    }
//
//    private function populateCode($dataRecord, FHIRCondition $conditionResource)
//    {
//        if (!empty($dataRecord['diagnosis'])) {
//            $diagnosisCoding = new FHIRCoding();
//            $diagnosisCode = new FHIRCodeableConcept();
//            foreach ($dataRecord['diagnosis'] as $code => $codeValues) {
//                if (!is_string($code)) {
//                    $code = "$code"; // FHIR expects a string
//                }
//                $diagnosisCoding->setCode($code);
//                $diagnosisCoding->setDisplay($codeValues['description']);
//                $diagnosisCoding->setSystem($codeValues['system']);
//                $diagnosisCode->addCoding($diagnosisCoding);
//            }
//            $conditionResource->setCode($diagnosisCode);
//        }
//    }
//
//    private function populateVerificationStatus($dataRecord, FHIRCondition $conditionResource)
//    {
//        $system = "http://terminology.hl7.org/CodeSystem/condition-ver-status";
//        $code = $dataRecord['verification'] ?? 'unconfirmed';
//        $display = $dataRecord['verification_title'] ?? 'Unconfirmed';
//        $verificationStatus = UtilsService::createCodeableConcept([
//            $code => [
//                'system' => $system,
//                'code' => $code,
//                'display' => $display
//            ]
//        ]);
//        $conditionResource->setVerificationStatus($verificationStatus);
//    }
//
//    private function populateCategory($dataRecord, $conditionResource)
//    {
//        $conditionResource->addCategory(UtilsService::createCodeableConcept([
//            'problem-list-item' => [
//                'system' => "http://terminology.hl7.org/CodeSystem/condition-category",
//                'code' => 'problem-list-item',
//                'display' => 'Problem List Item'
//            ]
//        ]));
//    }
//
//    private function populateClinicalStatus($dataRecord, FHIRCondition $conditionResource)
//    {
//        $clinicalStatus = "inactive";
//        $clinicalSysytem = "http://terminology.hl7.org/CodeSystem/condition-clinical";
//        if (
//            (!isset($dataRecord['enddate']) && isset($dataRecord['begdate']))
//            || isset($dataRecord['enddate']) && strtotime($dataRecord['enddate']) >= strtotime("now")
//        ) {
//            // Active if Only Begin Date isset OR End Date isnot expired
//            $clinicalStatus = "active";
//            if ($dataRecord['occurrence'] == 1 || $dataRecord['outcome'] == 1) {
//                $clinicalStatus = "resolved";
//            } elseif ($dataRecord['occurrence'] > 1) {
//                $clinicalStatus = "recurrence";
//            }
//        } elseif (isset($dataRecord['enddate']) && strtotime($dataRecord['enddate']) < strtotime("now")) {
//            //Inactive if End Date is expired
//            $clinicalStatus = "inactive";
//        } else {
//            $clinicalSysytem = "http://terminology.hl7.org/CodeSystem/data-absent-reason";
//            $clinicalStatus = "unknown";
//        }
//        $clinical_Status = UtilsService::createCodeableConcept([
//            $clinicalStatus => [
//                'system' => $clinicalSysytem,
//                'code' => $clinicalStatus,
//                'display' => ucwords($clinicalStatus)
//            ]
//        ]);
//        $conditionResource->setClinicalStatus($clinical_Status);
//    }
//
//    /**
//     * Searches for OpenEMR records using OpenEMR search parameters
//     *
//     * @param  array openEMRSearchParameters OpenEMR search fields
//     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
//     * @return ProcessingResult
//     */
//    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
//    {
//        $result = $this->conditionService->getAll($openEMRSearchParameters, true, $puuidBind);
//        return $result;
//    }
//
//    public function createProvenanceResource($dataRecord = [], $encode = false)
//    {
//        if (!($dataRecord instanceof FHIRCondition)) {
//            throw new \BadMethodCallException("Data record should be correct instance class");
//        }
//        $fhirProvenanceService = new FhirProvenanceService();
//        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
//        if ($encode) {
//            return json_encode($fhirProvenance);
//        } else {
//            return $fhirProvenance;
//        }
//    }

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
        foreach ($this->getMappedServices() as $mappedService) {
            if ($mappedService instanceof IResourceUSCIGProfileService) {
                $profileSets[] = $mappedService->getProfileURIs();
            }
        }

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

    private function populateSurrogateSearchFieldsForUUID(string $_id, array $fhirSearchParameters)
    {
        $id = $search['form_id'] ?? new TokenSearchField('form_id', []);
        $encounter = $search['encounter'] ?? new ReferenceSearchField('euuid', [], true);

        // need to deparse our uuid into something else we can use
        foreach ($fieldUUID->getValues() as $value) {
            if ($value instanceof TokenSearchValue) {
                $code = $value->getCode();
                $key = $this->splitSurrogateKeyIntoParts($code);
                if (empty($key['euuid']) && empty($key['form_id'])) {
                    throw new \InvalidArgumentException("uuid '" . ($code ?? "") . "' was invalid for resource");
                }
                if (!empty($key['euuid'])) {
                    $values = $encounter->getValues();
                    array_push($values, new ReferenceSearchValue($key['euuid'], "Encounter", true));
                    $encounter->setValues($values);
                }
                if (!empty($key['form_id'])) {
                    $values = $id->getValues();
                    array_push($values, new TokenSearchValue($key['form_id'], null, false));
                    $id->setValues($values);
                }
            }
        }
        $search['form_id'] = $id;
        $search['encounter'] = $encounter;
        unset($search['uuid']);
        if (isset($search['uuid']) && $search['uuid'] instanceof ISearchField) {
            $this->populateSurrogateSearchFieldsForUUID($search['uuid'], $search);
        }
    }
}
