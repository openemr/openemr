<?php

/**
 * FhirClaimService.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Nilesh Hake <nilesh.hake@nbhhealthsoft.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRClaim;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchQueryConfig;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirClaimService extends FhirServiceBase implements IPatientCompartmentResourceService
{
    /**
     * Main Insert Method
     */
    public function insert(FHIRDomainResource $fhirResource): ProcessingResult
    {
        $result = new ProcessingResult();
        $data = $this->parseFhirResource($fhirResource);

        if (empty($data['patient_id']) || (int)$data['patient_id'] <= 0) {
            $result->setValidationMessages([
                'patient' => 'Claim.patient is required and must reference an existing patient',
            ]);
            return $result;
        }

        if (empty($data['encounter_id']) || (int)$data['encounter_id'] <= 0) {
            $result->setValidationMessages([
                'encounter' => 'Claim.item[].encounter is required and must reference an existing encounter',
            ]);
            return $result;
        }

        // Validate that the encounter belongs to the specified patient
        $encounterCheck = sqlQuery(
            "SELECT encounter FROM form_encounter WHERE pid = ? AND encounter = ?",
            [(int)$data['patient_id'], (int)$data['encounter_id']]
        );
        if (empty($encounterCheck)) {
            $result->setValidationMessages([
                'encounter' => 'The encounter does not exist or does not belong to the specified patient',
            ]);
            return $result;
        }

        try {
            $record = QueryUtils::inTransaction(function () use ($data): array {
                // Lock existing claims for this patient and encounter to avoid race conditions on version increment
                $currentVersion = sqlQuery(
                    "SELECT MAX(version) as max_v FROM claims WHERE patient_id = ? AND encounter_id = ? FOR UPDATE",
                    [$data['patient_id'], $data['encounter_id']]
                );

                $data['version'] = (!empty($currentVersion['max_v'])) ? (int)$currentVersion['max_v'] + 1 : 1;

                $success = $this->insertOpenEMRRecord($data);
                if (!$success) {
                    throw new \RuntimeException("Failed to insert claim into database.");
                }

                $record = sqlQuery(
                    "SELECT * FROM claims WHERE patient_id = ? AND encounter_id = ? AND version = ?",
                    [$data['patient_id'], $data['encounter_id'], $data['version']]
                );

                if (empty($record)) {
                    throw new \RuntimeException("Failed to fetch newly inserted claim record.");
                }

                return $record;
            });

            $this->createProvenanceResource($record);
            $result->addData($this->parseOpenEMRRecord($record));
        } catch (\Throwable $e) {
            $result->addInternalError("Failed to insert claim: " . $e->getMessage());
        }

        return $result;
    }

    public function createProvenanceResource($dataRecord, $encode = false)
    {
        return null; 
    }

    /**
     * Safely maps FHIR data to Database Columns
     */
    public function parseFhirResource($fhirResource)
    {
        $json = $fhirResource instanceof FHIRDomainResource
            ? $fhirResource->jsonSerialize()
            : (is_array($fhirResource) ? $fhirResource : []);

        $data = [];

        // 1. Patient ID resolution (from UUID reference or numeric ID)
        $patientRef = $json['patient'] ?? null;
        $data['patient_id'] = $this->resolveReferenceId($patientRef, 'Patient', 'patient_data', 'pid');

        // 2. Encounter ID resolution (from item[].encounter in FHIR R4)
        $encounterRef = null;
        if (!empty($json['item']) && is_array($json['item'])) {
            foreach ($json['item'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (!empty($item['encounter'])) {
                    if (is_array($item['encounter'])) {
                        $encounterRef = isset($item['encounter'][0]) ? $item['encounter'][0] : $item['encounter'];
                    } else {
                        $encounterRef = $item['encounter'];
                    }
                    break;
                }
            }
        }
        if ($encounterRef === null && !empty($json['encounter'])) {
            $encounterRef = $json['encounter'];
        }

        $data['encounter_id'] = $this->resolveReferenceId($encounterRef, 'Encounter', 'form_encounter', 'encounter');

        // 3. Payer ID resolution (from insurance[].coverage)
        $payerId = 0;
        if (!empty($json['insurance']) && is_array($json['insurance'])) {
            $firstIns = $json['insurance'][0] ?? null;
            if (is_array($firstIns) && !empty($firstIns['coverage'])) {
                $payerId = $this->resolveReferenceId($firstIns['coverage'], 'Coverage', 'insurance_data', 'id');
                if ($payerId === 0) {
                    $payerId = $this->resolveReferenceId($firstIns['coverage'], 'Organization', 'insurance_companies', 'id');
                }
            }
        }
        $data['payer_id'] = $payerId;

        // 4. Status mapping
        $statusStr = is_string($json['status'] ?? null) ? $json['status'] : '';
        $data['status'] = ($statusStr === 'active') ? 0 : 1;

        // 5. Defaults
        $data['payer_type'] = 0;
        $data['bill_process'] = 0;
        $data['bill_time'] = date('Y-m-d H:i:s');
        $data['process_time'] = null;
        $data['process_file'] = null;
        $data['target'] = '0';
        $data['x12_partner_id'] = 0;

        // 6. FULL JSON
        $data['submitted_claim'] = json_encode($fhirResource);

        return $data;
    }

    /**
     * Resolves a FHIR reference to an internal integer database ID.
     */
    private function resolveReferenceId(mixed $refSource, string $expectedType, string $tableName, string $idColumn): int
    {
        if (empty($refSource)) {
            return 0;
        }

        $refValue = '';
        if (is_array($refSource)) {
            $item = isset($refSource[0]) && is_array($refSource[0]) ? $refSource[0] : $refSource;
            $refValue = is_string($item['reference'] ?? null) ? $item['reference'] : '';
        } elseif (is_object($refSource)) {
            if (method_exists($refSource, 'getReference')) {
                $refObj = $refSource->getReference();
                if (is_object($refObj) && method_exists($refObj, 'getValue')) {
                    $refValue = (string) $refObj->getValue();
                } elseif (is_string($refObj)) {
                    $refValue = $refObj;
                }
            } elseif (method_exists($refSource, 'getValue')) {
                $val = $refSource->getValue();
                $refValue = is_string($val) ? $val : '';
            }
        } elseif (is_string($refSource)) {
            $refValue = $refSource;
        }

        if (empty($refValue)) {
            return 0;
        }

        $parsed = UtilsService::parseReferenceString($refValue, $expectedType);
        $uuidOrId = $parsed['uuid'] ?? str_replace($expectedType . '/', '', $refValue);

        if (empty($uuidOrId)) {
            return 0;
        }

        // If reference is a valid UUID, look up by UUID in the database
        if (UuidRegistry::isValidStringUUID($uuidOrId)) {
            $id = BaseService::getIdByUuid(UuidRegistry::uuidToBytes($uuidOrId), $tableName, $idColumn);
            return is_numeric($id) ? (int)$id : 0;
        }

        // If reference is a direct numeric ID, verify it exists
        if (is_numeric($uuidOrId)) {
            $row = sqlQuery("SELECT `{$idColumn}` FROM `{$tableName}` WHERE `{$idColumn}` = ?", [(int)$uuidOrId]);
            return !empty($row) ? (int)$row[$idColumn] : 0;
        }

        return 0;
    }

    protected function insertOpenEMRRecord($data)
    {
        $sql = "INSERT INTO claims (
                    patient_id, encounter_id, version, payer_id, 
                    status, payer_type, bill_process, bill_time, 
                    process_time, process_file, target, x12_partner_id, 
                    submitted_claim
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return sqlStatement($sql, [
            (int)$data['patient_id'],
            (int)$data['encounter_id'],
            (int)$data['version'],
            (int)$data['payer_id'],
            (int)$data['status'],
            (int)$data['payer_type'],
            (int)$data['bill_process'],
            $data['bill_time'],
            $data['process_time'],
            $data['process_file'],
            (string)$data['target'],
            (int)$data['x12_partner_id'],
            (string)$data['submitted_claim']
        ]);
    }

    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        if (empty($dataRecord) || empty($dataRecord['submitted_claim'])) {
            return null;
        }
        $fullData = json_decode($dataRecord['submitted_claim'], true);
        unset($fullData['id'], $fullData['status']);
        return $encode ? json_encode($fullData) : $fullData;
    }

    /**
     * Required Public Methods for Search functionality.
     */
    protected function loadSearchParameters()
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            'encounter' => new FhirSearchParameterDefinition(
                'encounter',
                SearchFieldType::REFERENCE,
                [new ServiceField('encounter_id', ServiceField::TYPE_NUMBER)]
            ),
        ];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition(
            'patient',
            SearchFieldType::REFERENCE,
            [new ServiceField('patient_id', ServiceField::TYPE_NUMBER)]
        );
    }

    protected function searchForOpenEMRRecordsWithConfig(array $openEMRSearchParameters, SearchQueryConfig $config): ProcessingResult
    {
        $result = new ProcessingResult();
        $whereClauses = [];
        $binds = [];

        // 1. Patient filtering
        if (isset($openEMRSearchParameters['patient'])) {
            $patientIds = $this->extractPatientIdsFromSearchField($openEMRSearchParameters['patient']);
            if (!empty($patientIds)) {
                $placeholders = implode(',', array_fill(0, count($patientIds), '?'));
                $whereClauses[] = "patient_id IN ({$placeholders})";
                foreach ($patientIds as $pid) {
                    $binds[] = (int) $pid;
                }
            } else {
                // Patient reference provided but could not be resolved -> return empty result
                return $result;
            }
        }

        // 2. Encounter filtering
        if (isset($openEMRSearchParameters['encounter'])) {
            $encounterId = $this->extractEncounterIdFromSearchField($openEMRSearchParameters['encounter']);
            if ($encounterId > 0) {
                $whereClauses[] = "encounter_id = ?";
                $binds[] = $encounterId;
            } else {
                // Encounter reference provided but could not be resolved -> return empty result
                return $result;
            }
        }

        $sql = "SELECT * FROM claims";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        $sql .= " ORDER BY bill_time DESC, version DESC";

        // 3. Database-level pagination
        $pagination = $config->getPagination();
        $limit = $pagination->getLimit();
        $offset = $pagination->getOffset();

        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $binds[] = $limit + 1;
            $binds[] = $offset;
        }

        $records = sqlStatement($sql, $binds);
        $count = 0;
        while ($row = sqlFetchArray($records)) {
            if ($limit > 0 && $count >= $limit) {
                $pagination->setHasMoreData(true);
                break;
            }
            $result->addData($row);
            $count++;
        }

        $result->setPagination($pagination);
        return $result;
    }

    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->searchForOpenEMRRecordsWithConfig($openEMRSearchParameters, new SearchQueryConfig());
    }

    private function extractPatientIdsFromSearchField(mixed $field): array
    {
        $rawValues = [];
        if ($field instanceof ISearchField) {
            $rawValues = $field->getValues();
        } elseif (is_array($field)) {
            $rawValues = $field;
        } else {
            $rawValues = [$field];
        }

        $patientIds = [];
        foreach ($rawValues as $val) {
            if ($val instanceof TokenSearchValue) {
                $val = $val->getCode();
            }
            if (is_object($val) && method_exists($val, 'getValue')) {
                $val = (string) $val->getValue();
            }
            if (!is_string($val) && !is_int($val)) {
                continue;
            }
            $val = trim((string) $val);
            if (empty($val)) {
                continue;
            }

            $parsed = UtilsService::parseReferenceString($val, 'Patient');
            $uuidOrId = $parsed['uuid'] ?? str_replace('Patient/', '', $val);

            if (UuidRegistry::isValidStringUUID($uuidOrId)) {
                $id = BaseService::getIdByUuid(UuidRegistry::uuidToBytes($uuidOrId), 'patient_data', 'pid');
                if ($id !== false && is_numeric($id)) {
                    $patientIds[] = (int) $id;
                }
            } elseif (is_numeric($uuidOrId)) {
                $patientIds[] = (int) $uuidOrId;
            }
        }

        return array_unique($patientIds);
    }

    private function extractEncounterIdFromSearchField(mixed $field): int
    {
        $rawValues = [];
        if ($field instanceof ISearchField) {
            $rawValues = $field->getValues();
        } elseif (is_array($field)) {
            $rawValues = $field;
        } else {
            $rawValues = [$field];
        }

        foreach ($rawValues as $val) {
            if ($val instanceof TokenSearchValue) {
                $val = $val->getCode();
            }
            if (!is_string($val) && !is_int($val)) {
                continue;
            }
            $val = trim((string) $val);
            if (empty($val)) {
                continue;
            }

            $parsed = UtilsService::parseReferenceString($val, 'Encounter');
            $uuidOrId = $parsed['uuid'] ?? str_replace('Encounter/', '', $val);

            if (UuidRegistry::isValidStringUUID($uuidOrId)) {
                $id = BaseService::getIdByUuid(UuidRegistry::uuidToBytes($uuidOrId), 'form_encounter', 'encounter');
                if ($id !== false && is_numeric($id)) {
                    return (int) $id;
                }
            } elseif (is_numeric($uuidOrId)) {
                return (int) $uuidOrId;
            }
        }

        return 0;
    }

    protected function updateOpenEMRRecord($data, $id)
    {
        return null;
    }
}