<?php

/**
 * PatientIssuesService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class PatientIssuesService extends BaseService
{
    const ISSUES_TABLE_NAME = "lists";

    public function __construct()
    {
        parent::__construct(self::ISSUES_TABLE_NAME);
        UuidRegistry::createMissingUuidsForTables([self::ISSUES_TABLE_NAME]);
    }

    public function getUuidFields(): array
    {
        return ['uuid'];
    }

    public function getOneById($issueId)
    {
        $results = $this->search(['id' => new TokenSearchField('id', [$issueId])]);
        if (!empty($results->getData())) {
            $data_results = $results->getData();
            return array_pop($data_results);
        }
        return null;
    }

    public function getAllIssues()
    {
        $results = $this->search();
        return $results->getData();
    }

    public function createIssue($issueRecord)
    {
        $this->validateIssueType($issueRecord['type']);

        if (empty($issueRecord['pid'])) {
            throw new \InvalidArgumentException("issue pid cannot be empty for create");
        }
        if (!empty($issueRecord['id'])) {
            throw new \InvalidArgumentException("Cannot insert record with existing id");
        }

        $whiteListDict = $this->filterData($issueRecord);
        $insert = $this->buildInsertColumns($whiteListDict, ['null_value' => null]);

        $sql = "INSERT INTO lists SET " . $insert['set'];
        $list_id = QueryUtils::sqlInsert($sql, $insert['bind']);
        if ($issueRecord['type'] == "medication" && !empty($issueRecord['medication'])) {
            $medication = $issueRecord['medication'] ?? [];
            $medication['list_id'] = $list_id;
            $medicationIssueService = new MedicationPatientIssueService();
            $medicationIssueService->createIssue($medication);
        }
    }

    public function updateIssue($issueRecord)
    {
        $this->validateIssueType($issueRecord['type']);

        if (empty($issueRecord['id'])) {
            throw new \InvalidArgumentException("issue id cannot be empty for update");
        }
        if (empty($issueRecord['pid'])) {
            throw new \InvalidArgumentException("issue pid cannot be empty for update");
        }

        $whiteListDict = $this->filterData($issueRecord);
        $update = $this->buildUpdateColumns($whiteListDict, ['null_value' => null]);
        $values = $update['bind'];
        $sql = "UPDATE lists SET " . $update['set'] . " WHERE id = ? AND pid = ? ";
        $values[] = $issueRecord['id'];
        $values[] = $issueRecord['pid'];
        QueryUtils::sqlStatementThrowException($sql, $values);

        // now do any specific type updates here...
        // TODO: @adunsulag we should trigger a dispatch event here so people can do any post processing on the list insertion
        $endDate = $issueRecord['enddate'] ?? null;
        $title = $issueRecord['title'] ?? null;


        if ($issueRecord['type'] == "medication") {
            $medication = $issueRecord['medication'] ?? [];


            if (!empty($medication)) {
                $medicationIssueService = new MedicationPatientIssueService();
                $medication['list_id'] = $issueRecord['id'];
                $existingMedication = $medicationIssueService->getRecordByIssueListId($issueRecord['id']);
                if (!empty($existingMedication)) {
                    foreach ($medication as $key => $value) {
                        $existingMedication[$key] = $value;
                    }
                    $medicationIssueService->updateIssue($existingMedication);
                } else {
                    $medicationIssueService->createIssue($medication);
                }
            }
            // sync the prescriptions
            // TODO: is this prescription update even used anymore?  Is this something for WENO or NewCrop or is this just legacy
            if ($endDate != null && $title != null) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE prescriptions SET '
                    . 'medication = 0 where patient_id = ? '
                    . " and upper(trim(drug)) = ? "
                    . ' and medication = 1',
                    array($issueRecord['pid'], strtoupper($title))
                );
            }
        }
    }

    private function validateIssueType($type)
    {
        $value = QueryUtils::fetchSingleValue("select type FROM issue_types WHERE type = ? ", 'type', $type);
        if (empty($value)) {
            throw new \InvalidArgumentException("Invalid issue type sent");
        }
    }

    public function search($search, $isAndCondition = true)
    {
        $sql = "SELECT lists.*
                ,medications.lists_medication_id
                ,medications.list_id
                ,medications.usage_category
                ,medications.usage_category_title
                ,medications.drug_dosage_instructions
                ,medications.request_intent
                ,medications.request_intent_title
                FROM lists
                LEFT JOIN (
                    SELECT 
                       id AS lists_medication_id
                       ,list_id
                        ,usage_category
                        ,usage_category_title
                        ,drug_dosage_instructions
                        ,request_intent
                        ,request_intent_title
                    FROM lists_medication
                ) medications ON medications.list_id = lists.id";

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment() . " ORDER BY lists.begdate ";
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);


        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }
        return $processingResult;
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row);
        if (!empty($record['lists_medication_id'])) {
            $extractKeys = ['usage_category', 'usage_category_title', 'request_intent', 'request_intent_title', 'drug_dosage_instructions'];
            $record['medication'] = [
                'id' => $row['lists_medication_id']
                ,'erx_source' => $row['erx_source']
                ,'erx_uploaded' => $row['erx_uploaded']
            ];
            foreach ($extractKeys as $key) {
                $record['medication'][$key] = $row[$key];
                unset($row[$key]);
            }
            unset($record['list_medication_id']);
        }
        return $record;
    }
}
