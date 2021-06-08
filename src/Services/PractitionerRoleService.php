<?php

/**
 * PractitionerRoleService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\BaseValidator;

class PractitionerRoleService extends BaseService
{

    private const PRACTITIONER_ROLE_TABLE = "facility_user_ids";
    private $uuidRegistry;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('facility_user_ids');
        $this->uuidRegistry = new UuidRegistry([
            'table_name' => self::PRACTITIONER_ROLE_TABLE,
            'table_vertical' => ['uid', 'facility_id']
        ]);
        $this->uuidRegistry->createMissingUuids();
        (new UuidRegistry(['table_name' => 'users']))->createMissingUuids();
        (new UuidRegistry(['table_name' => 'facility']))->createMissingUuids();
    }

    public function getUuidFields(): array
    {
        // return the individual uuid fields we want converted into strings
        return ['facility_uuid', 'facility_role_uuid', 'provider_uuid', 'uuid'];
    }

    public function search($search, $isAndCondition = true)
    {
        // note we are optimizing our key indexes by specifying our list_ids for list_options
        // note because facility_user_ids is denormalized and stores its form data in a Key Value list in order to grab
        // our data in the easiest format from the database and still be able to search on it, we do several joins
        // against the same table so we can grab our provider information, provider role info, and provider specialty
        // it seems like a pretty big query but its optimized pretty heavily on the indexes.  We may need a few more
        // indexes on facility_user_ids but we'll have to test this
        $sql = "SELECT
                providers.facility_role_id AS id,
                providers.facility_role_uuid AS uuid,
                providers.user_name,
                providers.provider_id,
                providers.provider_uuid,
                
                facilities.facility_uuid,
                facilities.facility_name,
                role_codes.role_code,
                role_codes.role_title,
                specialty_codes.specialty_code,
                specialty_codes.specialty_title
                FROM (
                    select
                        facility_user_ids.uuid AS facility_role_uuid,
                        facility_user_ids.id AS facility_role_id,
                        -- field_value AS provider_id,
                        facility_user_ids.facility_id,
                        uid AS user_id,
                        -- we are treating the user_id as the provider id
                        -- TODO: @adunsulag figure out whether we should actually be using the user entered provider_id
                        uid AS provider_id,
                        users.uuid AS provider_uuid,
                        CONCAT(COALESCE(users.fname,''),
                           IF(users.mname IS NULL OR users.mname = '','',' '),COALESCE(users.mname,''),
                           IF(users.lname IS NULL OR users.lname = '','',' '),COALESCE(users.lname,'')
                        ) as user_name
                    FROM 
                        facility_user_ids
                    JOIN users ON 
                        facility_user_ids.uid = users.id
                    WHERE 
                        field_id='provider_id'
                    
                ) providers
                JOIN (
                    select 
                        field_value AS role_code,
                        field_id,
                        role.title AS role_title,
                        facility_id,
                        uid AS user_id 
                    FROM 
                        facility_user_ids
                    JOIN 
                        list_options as role ON role.option_id = field_value
                    WHERE 
                        field_value != '' AND field_value IS NOT NULL
                        AND role.list_id='us-core-provider-role'
                ) role_codes ON 
                    providers.user_id = role_codes.user_id AND providers.facility_id = role_codes.facility_id AND role_codes.field_id='role_code'
                JOIN (
                    select 
                        uuid AS facility_uuid
                        ,id AS facility_id
                        ,name AS facility_name
                    FROM
                        facility
                ) facilities 
                    ON providers.facility_id = facilities.facility_id
                LEFT JOIN (
                    select 
                        field_value AS specialty_code,
                        specialty.title AS specialty_title,
                        field_id,
                        facility_id,
                        uid AS user_id 
                     FROM 
                        facility_user_ids facilities_specialty
                    JOIN 
                        list_options as specialty ON specialty.option_id = field_value
                    WHERE 
                        field_id='specialty_code'
                        AND specialty.list_id='us-core-provider-specialty'
                ) specialty_codes ON 
                    providers.user_id = specialty_codes.user_id AND providers.facility_id = specialty_codes.facility_id AND specialty_codes.field_id='specialty_code'";
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }
        return $processingResult;
    }

    /**
     * Grabs all of the roles and groups them by practitioner.  The data result set will be a hashmap with the keys
     * being the practitioner id and the value being an array of practitioner role records.
     * @param $practitionerIds
     * @return ProcessingResult
     */
    public function getAllByPractitioners($practitionerIds)
    {

        $results = $this->search(['provider_id' => new TokenSearchField('provider_id', $practitionerIds)]);

        $data = $results->getData() ?? [];
        $providerIdMap = [];
        foreach ($data as $record) {
            $providerId = $record['provider_id'];
            if (empty($providerIdMap[$providerId])) {
                $providerIdMap[$providerId] = [];
            }
            $providerIdMap[$providerId][] = $record;
        }
        $results->setData($providerIdMap);
        return $results;
    }

    /**
     * Returns a list of practitioner-role matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true)
    {
        $sqlBindArray = array();

        $sql = "SELECT *,
                role.title as role,
                spec.title as specialty
                FROM (
                    SELECT
                    prac_role.id as id,
                    prac_role.uuid as uuid,
                    prac_role.field_id as field,
                    (if( prac_role.field_id = 'role_code', prac_role.field_value, null )) as `role_code`,
                    (if( specialty.field_id = 'specialty_code', specialty.field_value, null )) as `specialty_code`,
                    us.uuid as user_uuid,
                    CONCAT(us.fname,
                           IF(us.mname IS NULL OR us.mname = '','',' '),us.mname,
                           IF(us.lname IS NULL OR us.lname = '','',' '),us.lname
                           ) as user_name,
                    fac.uuid as facility_uuid,
                    fac.name as facility_name
                    FROM facility_user_ids as prac_role
                    LEFT JOIN users as us ON us.id = prac_role.uid
                    LEFT JOIN facility_user_ids as specialty ON specialty.uid = prac_role.uid AND specialty.field_id = 'specialty_code'
                    LEFT JOIN facility as fac ON fac.id = prac_role.facility_id) as p_role
                LEFT JOIN list_options as role ON role.option_id = p_role.role_code
                LEFT JOIN list_options as spec ON spec.option_id = p_role.specialty_code
                WHERE p_role.field = 'role_code' AND p_role.role_code != '' AND p_role.role_code IS NOT NULL";

        if (!empty($search)) {
            $sql .= " AND ";
            $whereClauses = array();
            $wildcardFields = array('user_name');
            foreach ($search as $fieldName => $fieldValue) {
                // support wildcard match on specific fields
                if (in_array($fieldName, $wildcardFields)) {
                    array_push($whereClauses, $fieldName . ' LIKE ?');
                    array_push($sqlBindArray, '%' . $fieldValue . '%');
                } else {
                    // equality match
                    array_push($whereClauses, $fieldName . ' = ?');
                    array_push($sqlBindArray, $fieldValue);
                }
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
        }
        $sql .= "
         GROUP BY p_role.uuid";
        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['user_uuid'] = UuidRegistry::uuidToString($row['user_uuid']);
            $row['facility_uuid'] = UuidRegistry::uuidToString($row['facility_uuid']);
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Returns a single practitioner-role record by id.
     * @param $uuid - The practitioner-role uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = BaseValidator::validateId("uuid", "facility_user_ids", $uuid, true);

        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $sql = "SELECT prac_role.id,
                prac_role.uuid,
                prac_role.field_value as role_code,
                specialty.field_value as specialty_code,
                us.uuid as user_uuid,
                us.fname as user_fname,
                us.mname as user_mname,
                us.lname as user_lname,
                fac.uuid as facility_uuid,
                fac.name as facility_name,
                role.title as role,
                spec.title as specialty
                FROM facility_user_ids as prac_role
                LEFT JOIN users as us ON us.id = prac_role.uid
                LEFT JOIN facility_user_ids as specialty ON
                specialty.uid = prac_role.uid AND specialty.field_id = 'specialty_code'
                LEFT JOIN facility as fac ON fac.id = prac_role.facility_id
                LEFT JOIN list_options as role ON role.option_id = prac_role.field_value
                LEFT JOIN list_options as spec ON spec.option_id = specialty.field_value
                WHERE prac_role.uuid = ? AND prac_role.field_id = 'role_code'";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['user_uuid'] = UuidRegistry::uuidToString($sqlResult['user_uuid']);
        $sqlResult['facility_uuid'] = UuidRegistry::uuidToString($sqlResult['facility_uuid']);
        $processingResult->addData($sqlResult);
        return $processingResult;
    }
}
