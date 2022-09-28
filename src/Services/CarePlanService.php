<?php

/**
 * CarePlanService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
     * match on passed in value.  For more complicated searching @see CarePlanService::search().
     * @param $search a key => value array
     * @param bool $isAndCondition Whether the search should be a UNION of search values or INTERSECTION of search values
     * @param string $puuidBind- Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
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
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }

        return $this->search($search, $isAndCondition);
    }

    public function search($search, $isAndCondition = true)
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
                ,fcp_forms.form_id
                ,fcp_forms.creation_date
                ,UNIX_TIMESTAMP(fcp_forms.creation_date) AS 'creation_timestamp'
                ,fcp.code
                ,fcp.codetext
                ,fcp.description
                ,fcp.date
                ,l.`notes` AS moodCode
                ,category.careplan_category
                ,provider.provider_uuid
                ,provider.provider_npi
                ,provider.provider_username
                 FROM
                 (
                    select
                        id
                        ,code
                        ,codetext
                        ,description
                        ,`date`
                        ,`encounter`
                        ,`pid`
                        ,`care_plan_type`
                        ,`user` AS `care_plan_user`
                    FROM
                        form_care_plan
                    WHERE
                        `care_plan_type` = '$carePlanType'
                 ) fcp
                 CROSS JOIN (
                    select '$planCategory' AS careplan_category
                 ) category
                 JOIN (
                    select
                        encounter AS eid
                        ,uuid AS euuid
                    FROM
                        form_encounter
                 ) encounters ON fcp.encounter = encounters.eid
                -- we need the form date information
                 JOIN (
                      SELECT
                      id
                      ,form_id
                      ,encounter AS form_encounter
                      ,date AS creation_date
                      FROM forms
                      WHERE form_name = 'Care Plan Form'
                 ) fcp_forms ON fcp.id = fcp_forms.form_id AND encounters.eid = fcp_forms.form_encounter
                 LEFT JOIN (
                    select
                        pid
                        ,uuid AS puuid
                    FROM
                        patient_data
                 ) patients ON fcp.pid = patients.pid
                 LEFT JOIN (
                     select 
                        id AS provider_id
                        ,uuid AS provider_uuid
                        ,npi AS provider_npi
                        ,username AS provider_username
                     FROM
                        users
                 ) provider ON fcp.care_plan_user = provider.provider_username
                 LEFT JOIN `list_options` l ON l.`option_id` = fcp.`care_plan_type` AND l.`list_id`='Plan_of_Care_Type'";
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        // since our query can eventually be sorted we want to keep things in the order that the query processed them.
        // we will have a hash map that uses our surrogate key (uuid) to track the individual detailed care_plan items.
        // since form_care_plan items are NOT unique and are replaced every time the care_plan form is saved we use the
        // encounter and the form id as a surrogate key and treat the form_care_plan items as care_plan sub-items or details.
        // we will loop through each record and aggregate the form_care_plan items into a details array using the
        // recordsByKey as our hash map to track our individual records. this lets us reach a runtime of O(2n) as we will
        // do one loop to generate our aggregated data and then another loop through our ordered records to populate the
        // processing result.
        $orderedRecords = [];
        $recordsByKey = [];
        $currentIndex = 0;
        // runtime O(2n) as we create our indexed hash
        while ($row = sqlFetchArray($statementResults)) {
            // grab our key for the row
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $key = $resultRecord['uuid'];
            if (!isset($recordsByKey[$key])) {
                $orderedRecords[$currentIndex] = $resultRecord;
                $recordsByKey[$key] = $currentIndex++;
            } else {
                // now combine our child array
                $recordIndex = $recordsByKey[$key];
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
     * @param TokenSearchField $fieldUUID The uuid search field with the 1..* values to search on
     * @param $search Hashmap of search operators
     */
    private function populateSurrogateSearchFieldsForUUID(TokenSearchField $fieldUUID, &$search)
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
    }

    /**
     * Given a database record representing a form_care_plan row containing a 'form_id' and 'euuid' column generate the
     * surrogate key.  If either column is empty it uses an empty string as the value.
     * @param array $record An array containing a 'form_id' and 'euuid' element.
     * @return string The surrogate key.
     */
    public function getSurrogateKeyForRecord(array $record)
    {
        // note that logical ids are allowed to be 64 characters.  Our UUID is 32 characters so as long as
        // the form_id + separator never exceeds 64 characters we are good here.
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
     * @param $key string the key to parse
     * @return array The broken up key parts.
     */
    public function splitSurrogateKeyIntoParts($key)
    {
        $delimiter = self::SURROGATE_KEY_SEPARATOR_V2;
        if (strpos($key, self::SURROGATE_KEY_SEPARATOR_V1) !== false) {
            $delimiter = self::SURROGATE_KEY_SEPARATOR_V1;
        }
        $parts = explode($delimiter, $key);
        $key = [
            "euuid" => $parts[0] ?? ""
            ,"form_id" => $parts[1] ?? ""
        ];
        return $key;
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row);
        // now let's prep our details record
        $detailKeys = ['code', 'codetext', 'description', 'date', 'moodCode'];
        $details = [];
        foreach ($detailKeys as $key) {
            $details[$key] = $record[$key];
            unset($record[$key]);
        }
        $record['details'] = [$details];
        $record['uuid'] = $this->getSurrogateKeyForRecord($record);
        return $record;
    }
}
