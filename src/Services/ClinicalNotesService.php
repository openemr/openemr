<?php

/**
 * ClinicalNotesService.php
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
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class ClinicalNotesService extends BaseService
{
    const TABLE_NAME = "form_clinical_notes";

    const ACTIVITY_ACTIVE = 1;
    const ACTIVITY_INACTIVE = 0;

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        UuidRegistry::createMissingUuidsForTables(['form_clinical_notes']);
    }

    /**
     * Returns a list of records matching the search criteria.
     * Search criteria is conveyed by array where key = field/column name, value is an ISearchField
     * If an empty array of search criteria is provided, all records are returned.
     *
     * The search will grab the intersection of all possible values if $isAndCondition is true, otherwise it returns
     * the union (logical OR) of the search.
     *
     * More complicated searches with various sub unions / intersections can be accomplished through a CompositeSearchField
     * that allows you to combine multiple search clauses on a single search field.
     *
     * @param ISearchField[] $search Hashmap of string => ISearchField where the key is the field name of the search field
     * @param bool $isAndCondition Whether to join each search field with a logical OR or a logical AND.
     * @return ProcessingResult The results of the search.
     */
    public function search($search, $isAndCondition = true)
    {
        // because we can have two clinical note table options (one from contrib etc), we will return an empty search
        // result for now if the table does not conform to our CORE clinical_notes
        $fields = $this->getFields();
        $processingResult = new ProcessingResult();
        if (array_search('code', $fields) === false) {
            // there is no data right now for the other form so we leave it be.
            return $processingResult;
        }

        // we leave status to be current, if we ever support entered-in-error, or superseded we can do that here.
        try {
            $sql = "
            SELECT
                notes.id
                ,notes.uuid AS uuid
                ,notes.activity
                ,notes.date
                ,notes.code
                ,notes.codetext
                ,notes.description
                ,notes.external_id
                ,notes.clinical_notes_type
                ,notes.note_related_to
                ,notes.clinical_notes_category
                ,notes.last_updated
                ,forms.date_created
                ,lo_category.category_code
                ,lo_category.category_title
                ,patients.pid
                ,patients.puuid
                ,encounters.eid
                ,encounters.euuid
                ,encounters.encounter_date
                ,users.username
                ,users.user_uuid
                ,users.npi
                ,users.physician_type
            FROM
                (
                    select
                        id
                        ,uuid
                        ,activity
                        ,`date`
                        ,`code`
                        ,codetext
                        ,`description`
                        ,external_id
                        ,clinical_notes_type
                        ,note_related_to
                        ,clinical_notes_category
                        ,form_id
                        ,last_updated
                        ,user
                 FROM
                    form_clinical_notes
             ) notes
            JOIN (
                SELECT
                    id AS form_id,
                    encounter
                    ,pid AS form_pid
                    ,`date` AS date_created
                FROM
                    forms
            ) forms ON forms.form_id = notes.form_id
            LEFT JOIN (
                select
                    encounter AS eid
                    ,uuid AS euuid
                    ,`date` AS encounter_date
                FROM
                    form_encounter
            ) encounters ON encounters.eid = forms.encounter
            LEFT JOIN
            (
                SELECT
                    uuid AS puuid
                    ,pid
                    FROM patient_data
            ) patients ON forms.form_pid = patients.pid
            LEFT JOIN
            (
                SELECT
                    uuid AS user_uuid
                    ,username
                    ,id AS uid
                    ,npi
                    ,physician_type
                    FROM
                        users
            ) users ON notes.`user` = users.username
            LEFT JOIN
            (
                SELECT
                    notes AS category_code
                    ,title AS category_title
                    ,option_id
                FROM
                    list_options
                WHERE
                    list_id = 'Clinical_Note_Category'
            ) lo_category ON notes.clinical_notes_category = lo_category.option_id
        ";
            $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

            $sql .= $whereClause->getFragment();
            $sqlBindArray = $whereClause->getBoundValues();

            $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);
            $records = [];
            while ($row = QueryUtils::fetchArrayFromResultSet($statementResults)) {
                $resultRecord = $this->createResultRecordFromDatabaseResult($row);
                $records[] =  $resultRecord;
            }

            $updatedRecords = $this->populateResultsWithLinkedData($records);
            $processingResult->setData($updatedRecords);
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $processingResult;
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        // TODO: @adunsulag this is just for testing until we can figure out the right uuid schematic
        if (!empty($row['code'])) {
            $row['code'] = $this->addCoding($row['code']);
        }
        return parent::createResultRecordFromDatabaseResult($row);
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid', 'euuid', 'user_uuid'];
    }

    /**
     * Sets the activity status flag for the given form_clinical_notes record to either be active(1) or inactive(0).
     * @param $clinicalNoteId The unique record id for the form_clinical_notes table
     * @param $pid The unique patient pid from the patient_data table
     * @param $encounter The unique encounter id from the form_encounters table
     * @param $activity The activity status: active(1) or inactive(0).
     */
    public function setActivityForClinicalRecord($clinicalNoteId, $pid, $encounter, $activity)
    {
        $sql = "UPDATE `form_clinical_notes` SET activity = ? WHERE id=? AND pid = ? AND encounter = ?";
        $bindings = [$activity, $clinicalNoteId, $pid, $encounter];
        QueryUtils::sqlStatementThrowException($sql, $bindings);
    }

    /**
     * Given a form id remove all of the clinical note records connected to that form.
     * @param $formId The unique id from the forms table
     * @param $pid The unique patient pid from the patient_data table
     * @param $encounter The unique encounter id from the form_encounters table
     */
    public function clearClinicalRecordsForForm($formId, $pid, $encounter)
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `form_clinical_notes` WHERE form_id=? AND pid = ? AND encounter = ?",
            [$formId, $pid, $encounter]
        );
    }

    public function getClinicalRecordNoteById($id)
    {
        $sql = "select * from `form_clinical_notes` WHERE id = ? ";
        $records = QueryUtils::fetchRecords($sql, [$id]);
        if (!empty($records)) {
            return $records[0];
        }
        return null;
    }

    public function createClinicalNotesParentForm($pid, $encounter, $userauthorized)
    {
        $largestId = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(form_id), 0) as largestId FROM `form_clinical_notes`", 'largestId');

        $form_id = $largestId > 0 ? $largestId + 1 : 1;

        addForm($encounter, "Clinical Notes Form", $form_id, "clinical_notes", $pid, $userauthorized);
        return $form_id;
    }

    public function saveArray(array $record)
    {
        $form_id = $record['form_id'] ?? null;
        $id = $record['id'] ?? null;
        $pid = $record['pid'] ?? null;
        $encounter = $record['encounter'] ?? null;
        $userauthorized = $record['authorized'] ?? null;
        $existingRecord = [];


        if (empty($form_id) || empty($pid) || empty($encounter) || empty($record) || $userauthorized === null) {
            throw new \InvalidArgumentException("Record, form_id, pid, authorized and encounter must be populated");
        }

        unset($record['id']);
        // we grab the existing record so we can populate the uuid if necessary
        if (isset($id)) {
            $existingRecord = $this->getClinicalRecordNoteById($id);
        }

        if (empty($form_id)) {
            $largestId = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(form_id), 0) as largestId FROM `form_clinical_notes`", 'largestId');

            $record['form_id'] = $largestId > 0 ? $largestId + 1 : 1;

            addForm($encounter, "Clinical Notes Form", $record['form_id'], "clinical_notes", $pid, $userauthorized);
        }
        if (empty($existingRecord['uuid'])) {
            $record['uuid'] = (new UuidRegistry(['table_name' => 'form_clinical_notes']))->createUuid();
        }

        $keys = array_keys($record);
        $setValues = array_map(fn($val): string => $val . " = ? ", $keys);
        if (!empty($id)) {
            $sql = "UPDATE " . self::TABLE_NAME . " SET " . implode(", ", $setValues) . " WHERE id = ? ";
            $bindValues = array_values($record);
            $bindValues[] = $id;
            QueryUtils::sqlStatementThrowException($sql, $bindValues);
            $record['id'] = $id;
        } else {
            $sql = "INSERT INTO " . self::TABLE_NAME . " SET " . implode(", ", $setValues);
            $bindValues = array_values($record);
            $recordId = QueryUtils::sqlInsert($sql, $bindValues);
            $record['id'] = $recordId;
        }
        // if we want the id&uuid back we need to return the record here
        return $record;
    }

    public function getClinicalNoteIdsForPatientForm(int $formid, $pid, $encounter)
    {
        if (empty($formid) || empty($pid) || empty($encounter)) {
            throw new \InvalidArgumentException("formid, pid, and encounter must all be populated");
        }

        $sql = "SELECT id FROM `form_clinical_notes` WHERE `form_id`=? AND `pid` = ? AND `encounter` = ?";
        return QueryUtils::fetchTableColumn($sql, 'id', [$formid, $pid, $encounter]);
    }

    /**
     * Retrieve all of the clinical notes for a given patient
     * @param $pid
     * @return ProcessingResult
     */
    public function getClinicalNotesForPatient($pid): ProcessingResult
    {
        $search['pid'] = new TokenSearchField('pid', new TokenSearchValue($pid));
        return $this->search($search);
    }

    public function getClinicalNotesForPatientForm(int $formid, $pid, $encounter)
    {
        if (empty($formid) || empty($pid)) {
            throw new \InvalidArgumentException("formid, and pid must all be populated");
        }

        $sql = "SELECT fcn.*, lo_category.title AS category_title, lo_category.notes AS category_code,
                lo_type.title AS type_title, lo_type.notes AS type_code
                FROM `form_clinical_notes` fcn
                LEFT JOIN list_options lo_category ON lo_category.list_id = 'Clinical_Note_Category' AND lo_category.option_id = fcn.clinical_notes_category
                LEFT JOIN list_options lo_type ON lo_type.list_id = 'Clinical_Note_Type' AND lo_type.option_id = fcn.clinical_notes_type
                WHERE fcn.`form_id`=? AND fcn.`pid` = ? AND fcn.`encounter` = ?";
        return $this->populateResultsWithLinkedData(QueryUtils::fetchRecords($sql, [$formid, $pid, $encounter]));
    }

    public function deleteClinicalNoteRecordForPatient($recordId, $pid, $encounter)
    {
        $sql = "DELETE FROM `form_clinical_notes` WHERE id = ? AND pid= ? AND encounter = ?";
        QueryUtils::sqlStatementThrowException($sql, [$recordId, $pid, $encounter]);
    }

    /**
     * Given a code (with or without LOINC prefix) determine if its a valid clinical note code that this service can
     * respond to.
     * @param $code string
     * @return bool true if the code is valid, false otherwise
     */
    public function isValidClinicalNoteCode($code)
    {
        // make it a LOINC code
        if (!str_contains((string) $code, ":")) {
            $code = "LOINC:" . $code;
        }
        $listService = new ListService();
        $options = $listService->getOptionsByListName('Clinical_Note_Type', ['notes' => $code]);
        return !empty($options);
    }

    public function getClinicalNoteTypes($includeInactive = false)
    {
        $listService = new ListService();
        $search = ($includeInactive) ? [] :  ['activity' => '1'];
        $options = $listService->getOptionsByListName('Clinical_Note_Type', $search);
        return $this->getListAsSelectList($options);
    }

    public function getClinicalNoteCategories($includeInactive = false)
    {
        $listService = new ListService();
        $search = ($includeInactive) ? [] :  ['activity' => '1'];
        $options = $listService->getOptionsByListName('Clinical_Note_Category', $search);
        return $this->getListAsSelectList($options);
    }

    private function getListAsSelectList($optionsList)
    {
        if (empty($optionsList)) {
            return [];
        }

        $selectList = [];
        foreach ($optionsList as $option) {
            $selectList[] = ['value' => $option['option_id']
                , 'code' => $option['notes']
                , 'title' => $option['title']
                , 'xlTitle' => xl_list_label($option['title'])
                , 'selected' => $option['is_default'] === 1
            ];
        }
        return $selectList;
    }

    /**
     * AI Generated
     * @param array $clinicalNoteIds
     * @return array
     */
    protected function getLinkedDocumentsBatch(array $clinicalNoteIds): array
    {
        if (empty($clinicalNoteIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($clinicalNoteIds) - 1) . '?';
        $sql = "
        SELECT
            cnd.clinical_note_id,
            d.id as document_id,
            d.uuid as document_uuid,
            d.name as document_name,
            d.mimetype as document_type,
            d.date as document_date
        FROM clinical_notes_documents cnd
        JOIN documents d ON cnd.document_id = d.id
        WHERE cnd.clinical_note_id IN ($placeholders)
        AND d.deleted = 0
        ORDER BY cnd.clinical_note_id, d.date DESC
    ";

        $results = QueryUtils::fetchRecords($sql, $clinicalNoteIds);

        // Group by clinical note ID
        $grouped = [];
        foreach ($results as $row) {
            $noteId = $row['clinical_note_id'];
            if (!isset($grouped[$noteId])) {
                $grouped[$noteId] = [];
            }
            $grouped[$noteId][] = [
                'uuid' => UuidRegistry::uuidToString($row['document_uuid']),
                'name' => $row['document_name'],
                'type' => $row['document_type'],
                'date' => $row['document_date']
            ];
        }

        return $grouped;
    }

    /**
     * AI Generated
     * @param array $clinicalNoteIds
     * @return array
     */
    protected function getLinkedProcedureResultsBatch(array $clinicalNoteIds): array
    {
        if (empty($clinicalNoteIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($clinicalNoteIds) - 1) . '?';
        $sql = "
        SELECT
            cnpr.clinical_note_id,
            pr.procedure_result_id,
            pr.uuid as result_uuid,
            pr.result_text,
            pr.result,
            pr.date as result_date,
            poc.procedure_name
        FROM clinical_notes_procedure_results cnpr
        JOIN procedure_result pr ON cnpr.procedure_result_id = pr.procedure_result_id
        JOIN procedure_report prep ON pr.procedure_report_id = prep.procedure_report_id
        JOIN procedure_order po ON prep.procedure_order_id = po.procedure_order_id
        JOIN procedure_order_code poc ON po.procedure_order_id = poc.procedure_order_id AND prep.procedure_order_seq = poc.procedure_order_seq
        WHERE cnpr.clinical_note_id IN ($placeholders)
        ORDER BY cnpr.clinical_note_id, pr.date DESC
    ";

        $results = QueryUtils::fetchRecords($sql, $clinicalNoteIds);

        // Group by clinical note ID
        $grouped = [];
        foreach ($results as $row) {
            $noteId = $row['clinical_note_id'];
            if (!isset($grouped[$noteId])) {
                $grouped[$noteId] = [];
            }
            $grouped[$noteId][] = [
                'uuid' => UuidRegistry::uuidToString($row['result_uuid']),
                'procedure_name' => $row['procedure_name'],
                'result' => $row['result'],
                'result_text' => $row['result_text'],
                'date' => $row['result_date']
            ];
        }

        return $grouped;
    }

    /**
     * AI Generated
     * Save linked documents for a clinical note
     * @param int $clinicalNoteId The clinical note ID
     * @param array $documentsData Array of document data with uuid values
     * @param string $createdBy Username of the user creating the links
     */
    public function saveLinkedDocuments(int $clinicalNoteId, array $documentsData, string $createdBy)
    {
        // First clear existing links
        $this->clearLinkedDocuments($clinicalNoteId);

        if (empty($documentsData)) {
            return;
        }

        // Insert new links
        foreach ($documentsData as $documentData) {
            if (isset($documentData['uuid']) && !empty($documentData['uuid'])) {
                // Get document ID from UUID
                $documentId = $this->getDocumentIdFromUuid($documentData['uuid']);
                if ($documentId) {
                    $sql = "INSERT INTO clinical_notes_documents (clinical_note_id, document_id, created_by) VALUES (?, ?, ?)";
                    QueryUtils::sqlStatementThrowException($sql, [$clinicalNoteId, $documentId, $createdBy]);
                }
            }
        }
    }

    /**
     * AI Generated
     * Clear all linked documents for a clinical note
     * @param int $clinicalNoteId The clinical note ID
     */
    public function clearLinkedDocuments(int $clinicalNoteId)
    {
        $sql = "DELETE FROM clinical_notes_documents WHERE clinical_note_id = ?";
        QueryUtils::sqlStatementThrowException($sql, [$clinicalNoteId]);
    }

    /**
     * AI Generated
     * Save linked procedure results for a clinical note
     * @param int $clinicalNoteId The clinical note ID
     * @param array $resultsData Array of result data with uuid values
     * @param string $createdBy Username of the user creating the links
     */
    public function saveLinkedResults(int $clinicalNoteId, array $resultsData, string $createdBy)
    {
        // First clear existing links
        $this->clearLinkedResults($clinicalNoteId);

        if (empty($resultsData)) {
            return;
        }

        // Insert new links
        foreach ($resultsData as $resultData) {
            if (isset($resultData['uuid']) && !empty($resultData['uuid'])) {
                // Get procedure result ID from UUID
                $procedureResultId = $this->getProcedureResultIdFromUuid($resultData['uuid']);
                if ($procedureResultId) {
                    $sql = "INSERT INTO clinical_notes_procedure_results (clinical_note_id, procedure_result_id, created_by) VALUES (?, ?, ?)";
                    QueryUtils::sqlStatementThrowException($sql, [$clinicalNoteId, $procedureResultId, $createdBy]);
                }
            }
        }
    }

    /**
     * AI Generated
     * Clear all linked procedure results for a clinical note
     * @param int $clinicalNoteId The clinical note ID
     */
    public function clearLinkedResults(int $clinicalNoteId)
    {
        $sql = "DELETE FROM clinical_notes_procedure_results WHERE clinical_note_id = ?";
        QueryUtils::sqlStatementThrowException($sql, [$clinicalNoteId]);
    }

    /**
     * AI Generated
     * Get document ID from UUID
     * @param string $uuid The document UUID
     * @return int|null The document ID or null if not found
     */
    private function getDocumentIdFromUuid(string $uuid)
    {
        $sql = "SELECT id FROM documents WHERE uuid = ? AND deleted = 0";
        $binaryUuid = UuidRegistry::uuidToBytes($uuid);
        return QueryUtils::fetchSingleValue($sql, 'id', [$binaryUuid]);
    }

    /**
     * AI Generated
     * Get procedure result ID from UUID
     * @param string $uuid The procedure result UUID
     * @return int|null The procedure result ID or null if not found
     */
    private function getProcedureResultIdFromUuid(string $uuid)
    {
        $sql = "SELECT procedure_result_id FROM procedure_result WHERE uuid = ?";
        $binaryUuid = UuidRegistry::uuidToBytes($uuid);
        return QueryUtils::fetchSingleValue($sql, 'procedure_result_id', [$binaryUuid]);
    }

    private function populateResultsWithLinkedData(array $records): array
    {
        if (empty($records)) {
            return [];
        }
        $clinicalNoteIds = array_map(fn($record) => $record['id'], $records);
        // Batch fetch all linked documents
        $linkedDocuments = $this->getLinkedDocumentsBatch($clinicalNoteIds);

        // Batch fetch all linked procedure results
        $linkedResults = $this->getLinkedProcedureResultsBatch($clinicalNoteIds);

        // Attach linked data to each record
        $updatedRecords = [];
        foreach ($records as $record) {
            $noteId = $record['id'];
            $record['documents'] = $linkedDocuments[$noteId] ?? [];
            $record['linked_results'] = $linkedResults[$noteId] ?? [];
            $updatedRecords[] = $record;
        }
        return $updatedRecords;
    }
}
