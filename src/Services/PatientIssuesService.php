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
use OpenEMR\Events\Services\ServiceSaveEvent;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Services\Traits\ServiceEventTrait;
use OpenEMR\Validators\ProcessingResult;

class PatientIssuesService extends BaseService
{
    use ServiceEventTrait;

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
        $results = $this->search([]);
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
        $whiteListDict = $this->dispatchSaveEvent(ServiceSaveEvent::EVENT_PRE_SAVE, $whiteListDict);
        $insert = $this->buildInsertColumns($whiteListDict, ['null_value' => null]);

        $sql = "INSERT INTO lists SET " . $insert['set'];
        $whiteListDict['id'] = QueryUtils::sqlInsert($sql, $insert['bind']);
        if ($issueRecord['type'] == "medication" && !empty($issueRecord['medication'])) {
            $medication = $issueRecord['medication'] ?? [];
            $medication['list_id'] = $whiteListDict['id'];
            $medicationIssueService = new MedicationPatientIssueService();
            $medication['id'] = $medicationIssueService->createIssue($medication);
            $whiteListDict['medication'] = $medication;
            $whiteListDict = $this->dispatchSaveEvent(ServiceSaveEvent::EVENT_POST_SAVE, $whiteListDict);
        }

        return $whiteListDict;
    }

    public function getActiveIssues(int $pid): ProcessingResult
    {
        $notEnded = new TokenSearchField('enddate', [new TokenSearchValue(null)]);
        $notEnded->setModifier(SearchModifier::MISSING);
        $futureEndDate = new DateSearchField('enddate', ['gt' . date(DATE_ATOM)]);
        $isActive = new CompositeSearchField('active_issues', [], false);
        $isActive->addChild($notEnded);
        $isActive->addChild($futureEndDate);
        // values are string for TokenSearchField
        $patientByPid = new TokenSearchField('pid', [(string)$pid]);
        return $this->search(['active_issues' => $isActive, 'pid' => $patientByPid]);
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
        $whiteListDict = $this->dispatchSaveEvent(ServiceSaveEvent::EVENT_PRE_SAVE, $whiteListDict);
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
                    $medication['id'] = $medicationIssueService->createIssue($medication);
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
                    [$issueRecord['pid'], strtoupper((string) $title)]
                );
            }
            $whiteListDict['medication'] = $medication;
            $whiteListDict = $this->dispatchSaveEvent(ServiceSaveEvent::EVENT_POST_SAVE, $whiteListDict);
        }
        return $whiteListDict;
    }

    private function validateIssueType($type)
    {
        $value = QueryUtils::fetchSingleValue("select type FROM issue_types WHERE type = ? ", 'type', $type);
        if (empty($value)) {
            throw new \InvalidArgumentException("Invalid issue type sent");
        }
    }

    // TODO: @adunsulag can this be merged with the search in ConditionService or move things to a common base class?
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
                ,medications.medication_adherence_information_source
                ,medications.medication_adherence
                ,medications.medication_adherence_date_asserted
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
                        ,medication_adherence_information_source
                        ,medication_adherence
                        ,medication_adherence_date_asserted
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
            $extractKeys = ['usage_category', 'usage_category_title', 'request_intent', 'request_intent_title'
                , 'drug_dosage_instructions', 'medication_adherence_information_source', 'medication_adherence'
                , 'medication_adherence_date_asserted'];
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

    public function replaceIssuesForEncounter(string $pid, string $encounter, array $issues, ?int $userCreatorId = null): void
    {
        $issues = array_combine($issues, $issues);
        $records = QueryUtils::fetchRecords("SELECT * FROM issue_encounter WHERE "
            . "pid = ? AND encounter = ?", [$pid, $encounter]);
        foreach ($records as $record) {
            if (!isset($issues[$record['list_id']])) {
                // issue no longer linked to this encounter, so remove it
                $this->unlinkIssueFromEncounter($pid, $encounter, $record['list_id']);
            }
            unset($issues[$record['list_id']]);
        }
        // now add any remaining issues
        foreach ($issues as $issue) {
            $this->linkIssueToEncounter($pid, $encounter, $issue, $userCreatorId);
        }
    }

    /**
     * Link an issue to an encounter.  If the linkage already exists, do nothing but return the linkage uuid.
     * @param string $pid The patient pid from patient_data.pid
     * @param string $encounter The encounter id from form_encounter.encounter
     * @param string $issueId The issue id from lists.id
     * @param int|null $userCreatorId The user id of the user creating the linkage, defaults to the current user if null
     * @param int $resolved Optional resolved flag, defaults to 0 (whether the issue was resolved during this encounter)
     * @return string The UUID of the created linkage, or the uuid of the linkage if the linkage already exists
     */
    public function linkIssueToEncounter(string $pid, string $encounter, string $issueId, ?int $userCreatorId = null, int $resolved = 0): string
    {
        $uuid = QueryUtils::fetchSingleValue("SELECT uuid AS count FROM issue_encounter WHERE " .
            "pid = ? AND encounter = ? AND list_id = ?", 'uuid', [$pid, $encounter, $issueId]);
        if (!empty($uuid)) {
            return $uuid; // already connected
        }
        $userCreatorId ??= $_SESSION['authUserID'];
        $uuid = UuidRegistry::getRegistryForTable("issue_encounter")->createUuid();
        $query = "INSERT INTO issue_encounter ( `uuid`, pid, list_id, encounter, resolved, created_by, updated_by) VALUES (?,?,?,?,?,?,?)";
        QueryUtils::sqlInsert($query, [$uuid, $pid, $issueId, $encounter, $resolved,$userCreatorId,$userCreatorId]);
        return $uuid;
    }

    public function unlinkIssueFromEncounter(string $pid, string $encounter, string $issueId)
    {
        // TODO: @adunsulag should we actually have a status flag on the linkage and set it to inactive instead of deleting the linkage?
        // this would preserve historical data, and we could have a purge job to remove old inactive linkages after some time period if needed
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM issue_encounter WHERE " .
            "pid = ? AND encounter = ? AND list_id = ?)", [$pid, $encounter, $issueId]);
        QueryUtils::sqlStatementThrowException("DELETE FROM issue_encounter WHERE " .
            "pid = ? AND encounter = ? AND list_id = ?", [$pid, $encounter, $issueId]);
    }

    /**
     * Replace all the patient encounter issues for a patient.
     * @param int $pid The patient pid from patient_data.pid
     * @param array $encountersByListId An associative array where the key is the issue id and the value is an array of encounter ids that need to be linked to the issue
     * @param int|null $userCreatorId The user id of the user creating the linkages, defaults to the current user if null
     * @return void
     */
    public function replacePatientEncounterIssues(int $pid, array $encountersByListId, ?int $userCreatorId = null): void
    {
        // get all the issues
        // loop through each issue, if it has encounters in encountersByListId then call replaceIssuesForEncounter
        // otherwise call replaceIssuesForEncounter with empty array to clear out any existing linkages
        // existing linkages not in encountersByListId will be removed
        $encounterIssues = QueryUtils::fetchRecords("SELECT list_id,encounter,pid FROM issue_encounter WHERE pid = ?", [$pid]);
        foreach ($encounterIssues as $issue) {
            $issueId = $issue['list_id'];
            if (!isset($encountersByListId[$issueId])) {
                $this->unlinkIssueFromEncounter($pid, $issue['encounter'], $issue['list_id']);
            } else {
                $index = array_search($issue['encounter'], $encountersByListId[$issueId]);
                if ($index !== false) {
                    $encountersByListId[$issueId][$index] = null; // empty it out
                } else {
                    $this->unlinkIssueFromEncounter($pid, $issue['encounter'], $issue['list_id']);
                }
            }
        }
        // add any remaining encounters for each issue
        foreach ($encountersByListId as $listId => $encounters) {
            foreach ($encounters as $encounter) {
                if (!empty($encounter)) {
                    $this->linkIssueToEncounter($pid, $encounter, $listId, $userCreatorId);
                }
            }
        }
    }
}
