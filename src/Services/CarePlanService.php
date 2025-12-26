<?php

/**
 * CarePlanService.php
 *
 * @package    openemr
 * @link       http://www.open-emr.org
 * @author     Stephen Nielson <stephen@nielson.org>
 * @author     Jerry Padgett <sjpadgett@gmail.com>
 * @copyright  Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright  Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\ReferenceSearchField;
use OpenEMR\Services\Search\ReferenceSearchValue;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;
use Twig\Token;

class CarePlanService extends BaseService
{
    // Note: FHIR 4.0.1 id columns put a constraint on ids such that:
    // Ids can be up to 64 characters long, and contain any combination of upper and lowercase ASCII letters,
    // numerals, "-" and ".".  Logical ids are opaque to the resource server and should NOT be changed once they've
    // been issued by the resource server
    // Up to OpenEMR 6.1.0 patch 0 we used underscores as our separator
    const SURROGATE_KEY_SEPARATOR_V1 = "_";
    // use the abbreviation SK for Surrogate key and hyphens.  Since Logical ids are opaque we can do this as long as
    // our UUID NEVER generates a two digit hyphenated id which none of the standards currently do.
    // the best approach would be to completely overhaul Careplan but for historical reasons we aren't doing that right now.
    const SURROGATE_KEY_SEPARATOR_V2 = "-SK-";
    const V2_TIMESTAMP = 1649476800; // strtotime("2022-04-09");
    private const PATIENT_TABLE = "patient_data";
    private const ENCOUNTER_TABLE = "form_encounter";
    private const CARE_PLAN_TABLE = "form_care_plan";

    const TYPE_PLAN_OF_CARE = 'plan_of_care';
    const TYPE_GOAL = 'goal';

    const CARE_PLAN_TYPES = [self::TYPE_PLAN_OF_CARE, self::TYPE_GOAL];

    /**
     * @var string
     */
    private $carePlanType;

    /**
     * @var CodeTypesService
     */
    private $codeTypesService;

    function getUuidFields(): array
    {
        return ['puuid', 'euuid', 'provider_uuid'];
    }

    public function __construct($carePlanType = self::TYPE_PLAN_OF_CARE)
    {
        if (in_array($carePlanType, self::CARE_PLAN_TYPES) !== false) {
            $this->carePlanType = $carePlanType;
        } else {
            throw new \InvalidArgumentException("Invalid care plan type of " . $carePlanType);
        }

        UuidRegistry::createMissingUuidsForTables([self::PATIENT_TABLE, self::ENCOUNTER_TABLE]);

        parent::__construct(self::CARE_PLAN_TABLE);
        $this->codeTypesService = new CodeTypesService();
    }

    public function getOne($uuid, $puuid = null)
    {
        $search = [
            'uuid' => new TokenSearchField('uuid', [new TokenSearchValue($uuid, null, false)])
        ];
        if (isset($puuid)) {
            $search['puuid'] = new ReferenceSearchField('puuid', [new ReferenceSearchValue($puuid, 'Patient', true)]);
        }
        return $this->search($search);
    }

    /**
     * Returns a list of all care plan resources.  Search array can be a simple key => value array which does an exact
     * match on passed in value.  For more complicated searching @param $search a key => value array
     *
     * @param bool   $isAndCondition Whether the search should be a UNION of search values or INTERSECTION of search values
     * @param string $puuidBind      - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     * @see CarePlanService::search().
     */
    public function getAll($search, $isAndCondition = true, $puuidBind = null)
    {
        if (!empty($puuidBind)) {
            // code to support patient binding
            $isValidPatient = BaseValidator::validateId(
                'uuid',
                self::PATIENT_TABLE,
                $puuidBind,
                true
            );
            if ($isValidPatient !== true) {
                return $isValidPatient;
            }
        }
        $newSearch = [];
        foreach ($search as $key => $value) {
            if (!$value instanceof ISearchField) {
                $newSearch[] = new StringSearchField($key, [$value], SearchModifier::EXACT);
            } else {
                $newSearch[$key] = $value;
            }
        }
        // override puuid, this replaces anything in search if it is already specified.
        if (isset($puuidBind)) {
            $newSearch['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }

        return $this->search($newSearch, $isAndCondition);
    }

    public function search($search, $isAndCondition = true): ProcessingResult
    {
        if (isset($search['uuid']) && $search['uuid'] instanceof ISearchField) {
            $this->populateSurrogateSearchFieldsForUUID($search['uuid'], $search);
        }

        // this value is defined in code so we don't need to db escape it.
        $carePlanType = $this->carePlanType;
        $planCategory = "assess-plan";
        if ($carePlanType === self::TYPE_GOAL) {
            $planCategory = "goal";
        }
        $sql = "SELECT
                patients.puuid
                ,patients.pid
                ,encounters.euuid
                ,encounters.eid
                ,f.id AS forms_record_id
                ,f.form_id
                ,f.date AS creation_date
                ,UNIX_TIMESTAMP(f.date) AS creation_timestamp
                ,fcp.id AS care_plan_id
                ,fcp.code
                ,fcp.codetext
                ,fcp.description
                ,fcp.date
                ,fcp.plan_status
                ,fcp.proposed_date
                ,fcp.date_end
                ,fcp.note_related_to AS note_issues
                ,fcp.reason_code
                ,fcp.reason_description
                ,fcp.reason_date_low
                ,fcp.reason_date_high
                ,fcp.reason_status
                ,l.notes AS moodCode
                ,'$planCategory' AS careplan_category
                ,provider.provider_uuid
                ,provider.provider_npi
                ,provider.provider_username
                ,provider.provider_id AS provenance_updated_by
                ,ct.ct_key AS code_type
                ,c.code_text AS code_description
                ,goals.goal_care_plan_ids
             FROM forms AS f
             INNER JOIN (
                SELECT
                    id
                    ,code
                    ,codetext
                    ,description
                    ,`date` as creation_date
                    ,`date`
                    ,plan_status
                    ,proposed_date
                    ,date_end
                    ,note_related_to
                    ,reason_code
                    ,reason_description
                    ,reason_date_low
                    ,reason_date_high
                    ,reason_status
                    ,`encounter`
                    ,`pid`
                    ,`care_plan_type`
                    ,`user` AS `care_plan_user`
                FROM form_care_plan
                WHERE `care_plan_type` = '$carePlanType'
             ) fcp ON fcp.id = f.form_id
             LEFT JOIN codes AS c ON c.code = fcp.code
             LEFT JOIN code_types AS ct ON c.code_type = ct.ct_id
             JOIN (
                SELECT
                    encounter AS eid
                    ,uuid AS euuid
                FROM form_encounter
             ) encounters ON fcp.encounter = encounters.eid
             LEFT JOIN (
                SELECT
                    pid
                    ,uuid AS puuid
                FROM patient_data
             ) patients ON f.pid = patients.pid
             LEFT JOIN (
                SELECT
                    id AS provider_id
                    ,uuid AS provider_uuid
                    ,npi AS provider_npi
                    ,username AS provider_username
                FROM users
             ) provider ON fcp.care_plan_user = provider.provider_username
             LEFT JOIN `list_options` l ON l.option_id = fcp.care_plan_type 
                AND l.list_id = 'Plan_of_Care_Type'
             LEFT JOIN (
                SELECT 
                    fcp_goal.pid,
                    fcp_goal.encounter,
                    GROUP_CONCAT(DISTINCT fcp_goal.id SEPARATOR ',') AS goal_care_plan_ids
                FROM form_care_plan fcp_goal
                WHERE fcp_goal.care_plan_type = 'goal'
                GROUP BY fcp_goal.pid, fcp_goal.encounter
             ) goals ON goals.pid = fcp.pid 
                AND (goals.encounter = fcp.encounter OR goals.encounter IS NULL)
             WHERE f.formdir = 'care_plan' AND f.deleted = 0";

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
        $whereFragment = $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();

        // The builder returns a fragment that may or may not have WHERE
        // If it has conditions, it will start with " AND " or " WHERE "
        if (!empty($whereFragment)) {
            // Replace WHERE with AND since we already have WHERE clause
            $whereFragment = preg_replace('/^\s*WHERE\s+/i', ' AND ', $whereFragment);
            $sql .= $whereFragment;
        }

        $sql .= " ORDER BY fcp.encounter DESC, f.date DESC, fcp.id ASC";

        $statementResults = QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        // since our query can eventually be sorted we want to keep things in the order that the query processed them.
        // we will have a hash map that uses our surrogate key (uuid) to track the individual detailed care_plan items.
        // since form_care_plan items are NOT unique and are replaced every time the care_plan form is saved we use the
        // encounter and the form id as a surrogate key and treat the form_care_plan items as care_plan sub-items or details.
        // we will loop through each record and aggregate the form_care_plan items into a details array using the
        // recordsByKey as our hash map to track our individual records. this lets us reach a runtime of O(2n) as we will
        // do one loop to generate our aggregated data and then another loop through our ordered records to populate the
        // processing result.
        // Aggregate by form_id - this creates ONE CarePlan per form
        $orderedRecords = [];
        $recordsByKey = [];
        $currentIndex = 0;

        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);

            // Key is encounter + form_id (NOT individual activity)
            $key = $resultRecord['uuid'];

            if (!isset($recordsByKey[$key])) {
                $orderedRecords[$currentIndex] = $resultRecord;
                $recordsByKey[$key] = $currentIndex++;
            } else {
                // Add this activity to existing CarePlan's details array
                $recordIndex = $recordsByKey[$key];

                // DON'T concatenate - just add as new array element
                array_push($orderedRecords[$recordIndex]['details'], $resultRecord['details'][0]);
            }
        }
        foreach ($orderedRecords as $record) {
            $processingResult->addData($record);
        }

        return $processingResult;
    }

    /**
     * Take our uuid surrogate key and populate the underlying data elements representing the form_care_plan id column
     * and the connected encounter uuid.
     *
     * @param TokenSearchField $fieldUUID The uuid search field with the 1..* values to search on
     * @param                  $search    Hashmap of search operators
     */
    private function populateSurrogateSearchFieldsForUUID(TokenSearchField $fieldUUID, &$search)
    {
        $formId = $search['form_id'] ?? new TokenSearchField('form_id', []);
        $encounter = $search['encounter'] ?? new ReferenceSearchField('euuid', [], true);

        // need to deparse our uuid into something else we can use
        foreach ($fieldUUID->getValues() as $value) {
            if ($value instanceof TokenSearchValue) {
                $code = $value->getCode();
                $key = $this->splitSurrogateKeyIntoParts($code);
                if (!empty($key['euuid'])) {
                    $values = $encounter->getValues();
                    $values[] = new ReferenceSearchValue($key['euuid'], "Encounter", true);
                    $encounter->setValues($values);
                }
                if (!empty($key['form_id'])) {
                    $values = $formId->getValues();
                    $values[] = new TokenSearchValue($key['form_id'], null, false);
                    $formId->setValues($values);
                }
            }
        }
        $search['form_id'] = $formId;
        $search['encounter'] = $encounter;
        unset($search['uuid']);
    }

    /**
     * Given a database record representing a form_care_plan row containing a 'form_id' and 'euuid' column generate the
     * surrogate key.  If either column is empty it uses an empty string as the value.
     *
     * @param array $record An array containing a 'form_id' and 'euuid' element.
     * @return string The surrogate key.
     */
    public function getSurrogateKeyForRecord(array $record)
    {
        // Only form_id + encounter = ONE CarePlan
        $form_id = $record['form_id'] ?? '';
        $encounter = $record['euuid'] ?? '';

        $separator = self::SURROGATE_KEY_SEPARATOR_V2;
        if (intval($record['creation_timestamp'] ?? 0) <= self::V2_TIMESTAMP) {
            $separator = self::SURROGATE_KEY_SEPARATOR_V1;
        }

        return $encounter . $separator . $form_id;
    }

    /**
     * Given the surrogate key representing a Care Plan, split the key into its component parts.
     *
     * @param $key string the key to parse
     * @return array The broken up key parts.
     */
    public function splitSurrogateKeyIntoParts($key)
    {
        $delimiter = self::SURROGATE_KEY_SEPARATOR_V2;
        if (str_contains((string)$key, self::SURROGATE_KEY_SEPARATOR_V1)) {
            $delimiter = self::SURROGATE_KEY_SEPARATOR_V1;
        }
        $parts = explode($delimiter, (string)$key);
        $key = [
            "euuid" => $parts[0] ?? "",
            "form_id" => $parts[1] ?? ""
        ];
        return $key;
    }

    protected function createResultRecordFromDatabaseResult($row): array
    {
        $formId = $row['form_id'] ?? null;
        $creationTimestamp = $row['creation_timestamp'] ?? 0;

        $record = parent::createResultRecordFromDatabaseResult($row);

        $record['form_id'] = $formId;
        $record['creation_timestamp'] = $creationTimestamp;

        // Build goal surrogate keys for related Goal resources
        // Goals are stored in form_care_plan with care_plan_type='goal'
        // Each goal (each form_care_plan row) needs a unique surrogate key
        // Format: euuid-SK-form_id-SK-care_plan_id (3 parts)
        // Build goal surrogate keys for related Goal resources
        // Goals are stored in form_care_plan with care_plan_type='goal'
        // Each goal (each form_care_plan row) needs a unique surrogate key
        // Format: euuid-SK-care_plan_id (2 parts)
        // - euuid: encounter UUID (from UuidRegistry, already converted to string by parent)
        // - care_plan_id: form_care_plan table id (unique per goal row)
        // Note: forms.form_id = form_care_plan.id, so we only need care_plan_id
        if (!empty($row['goal_care_plan_ids']) && !empty($record['euuid'])) {
            $goalIds = explode(',', (string) $row['goal_care_plan_ids']);
            $goalUuids = [];
            $separator = self::SURROGATE_KEY_SEPARATOR_V2;
            if (intval($creationTimestamp) <= self::V2_TIMESTAMP) {
                $separator = self::SURROGATE_KEY_SEPARATOR_V1;
            }

            // euuid is already a string (converted by OpenEMR's UuidRegistry in parent method)
            $euuidString = $record['euuid'];

            foreach ($goalIds as $carePlanId) {
                $carePlanId = trim($carePlanId);
                if (!empty($carePlanId)) {
                    // Build unique 2-part surrogate key: euuid-SK-care_plan_id
                    // This ensures each goal (each form_care_plan row) gets a unique UUID
                    $goalUuids[] = $euuidString . $separator . $carePlanId;
                }
            }
            $record['related_goal_uuids'] = implode(',', $goalUuids);
        }

        // Extract detail for THIS specific activity only
        $detailKeys = [
            'code',
            'codetext',
            'description',  // ONLY this row's description
            'date',
            'moodCode',
            'note_issues',
            'proposed_date',
            'reason_code',
            'reason_description',
            'reason_date_low',
            'reason_date_high',
            'reason_status'
        ];

        $details = [];
        foreach ($detailKeys as $key) {
            if (isset($record[$key])) {
                $details[$key] = $record[$key];
                unset($record[$key]);
            }
        }

        // Single detail in array (will be aggregated by search method)
        $record['details'] = [$details];

        // Generate surrogate key UUID - euuid is already a string from parent processing
        $record['uuid'] = $this->getSurrogateKeyForRecord($record);

        return $record;
    }
}
