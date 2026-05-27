<?php

/**
 * PractitionerRoleService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class PractitionerRoleService extends BaseService
{
    private const PRACTITIONER_ROLE_TABLE = "facility_user_ids";
    private const PRACTITIONER_TABLE = "users";
    private const FACILITY_TABLE = "facility";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('facility_user_ids');
        UuidRegistry::createMissingUuidsForTables([self::PRACTITIONER_ROLE_TABLE, self::PRACTITIONER_TABLE, self::FACILITY_TABLE]);
    }

    public function getUuidFields(): array
    {
        // return the individual uuid fields we want converted into strings
        return ['facility_uuid', 'facility_role_uuid', 'provider_uuid', 'uuid', 'location_uuid'];
    }

    /**
     * Inserts a new PractitionerRole. The role is stored in facility_user_ids using the EAV
     * pattern: one marker row with field_id='provider_id' (which carries the FHIR uuid and
     * is what `search()`/`_id` exposes), plus a sibling row with field_id='role_code'
     * holding the actual role list_option, and optionally field_id='specialty_code' for
     * the specialty list_option. All rows share the same (uid, facility_id) pair.
     *
     * Expected $data keys:
     *   - provider_id (int, required) users.id
     *   - facility_id (int, required) facility.id
     *   - role_code (string, optional) list_options.option_id, list_id='us-core-provider-role'
     *   - specialty_code (string, optional) list_options.option_id, list_id='us-core-provider-specialty'
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): ProcessingResult
    {
        $result = new ProcessingResult();

        // Accept any resolved numeric id. We don't enforce > 0 because OpenEMR test
        // fixtures use explicit negative ids (e.g. users.id = -1) and the actual
        // sanity check is whether the FHIR uuid resolved to a row at all — that
        // happens upstream in FhirPractitionerRoleService::insertOpenEMRRecord which
        // returns null on lookup failure.
        $providerId = $data['provider_id'] ?? null;
        $facilityId = $data['facility_id'] ?? null;
        if (!is_numeric($providerId) || (int) $providerId === 0) {
            $result->setValidationMessages(['practitioner' => 'A resolvable practitioner reference is required']);
            return $result;
        }
        if (!is_numeric($facilityId) || (int) $facilityId === 0) {
            $result->setValidationMessages(['organization' => 'A resolvable organization (facility) reference is required']);
            return $result;
        }
        $providerId = (int) $providerId;
        $facilityId = (int) $facilityId;

        // Disallow duplicate role rows for the same (uid, facility_id). PractitionerRole
        // is identified externally by the marker uuid, so creating a second one would
        // shadow the existing record on read.
        $existing = QueryUtils::fetchSingleValue(
            "SELECT id FROM facility_user_ids "
            . "WHERE uid = ? AND facility_id = ? AND field_id = 'provider_id'",
            'id',
            [$providerId, $facilityId]
        );
        if ($existing !== null) {
            $result->setValidationMessages([
                'role' => 'A PractitionerRole already exists for this practitioner/facility pair',
            ]);
            return $result;
        }

        try {
            $out = QueryUtils::inTransaction(function () use ($providerId, $facilityId, $data): array {
                $uuid = (new UuidRegistry(['table_name' => self::PRACTITIONER_ROLE_TABLE]))->createUuid();

                // Marker row: this carries the FHIR uuid
                QueryUtils::sqlStatementThrowException(
                    "INSERT INTO facility_user_ids (uuid, uid, facility_id, field_id, field_value) "
                    . "VALUES (?, ?, ?, 'provider_id', ?)",
                    [$uuid, $providerId, $facilityId, (string) $providerId]
                );

                $roleCode = $data['role_code'] ?? null;
                if (is_string($roleCode) && $roleCode !== '') {
                    QueryUtils::sqlStatementThrowException(
                        "INSERT INTO facility_user_ids (uuid, uid, facility_id, field_id, field_value) "
                        . "VALUES (?, ?, ?, 'role_code', ?)",
                        [
                            (new UuidRegistry(['table_name' => self::PRACTITIONER_ROLE_TABLE]))->createUuid(),
                            $providerId,
                            $facilityId,
                            $roleCode,
                        ]
                    );
                }

                $specialtyCode = $data['specialty_code'] ?? null;
                if (is_string($specialtyCode) && $specialtyCode !== '') {
                    QueryUtils::sqlStatementThrowException(
                        "INSERT INTO facility_user_ids (uuid, uid, facility_id, field_id, field_value) "
                        . "VALUES (?, ?, ?, 'specialty_code', ?)",
                        [
                            (new UuidRegistry(['table_name' => self::PRACTITIONER_ROLE_TABLE]))->createUuid(),
                            $providerId,
                            $facilityId,
                            $specialtyCode,
                        ]
                    );
                }

                return ['uuid' => UuidRegistry::uuidToString($uuid)];
            });

            $result->addData($out);
        } catch (\RuntimeException | \OpenEMR\Common\Database\SqlQueryException $e) {
            $this->getLogger()->error('PractitionerRole insert failed', ['error' => $e->getMessage()]);
            $result->addInternalError($e->getMessage());
        }

        return $result;
    }

    /**
     * Updates a PractitionerRole identified by the marker uuid. Locates the matching
     * (uid, facility_id) from the marker row, then updates/inserts the role_code and
     * specialty_code sibling rows. FHIR PUT cannot rebind the practitioner or facility.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $uuid, array $data): ProcessingResult
    {
        $result = new ProcessingResult();

        if (!UuidRegistry::isValidStringUUID($uuid)) {
            $result->setValidationMessages(['uuid' => 'invalid uuid format']);
            return $result;
        }

        $marker = QueryUtils::querySingleRow(
            "SELECT uid, facility_id FROM facility_user_ids WHERE uuid = ? AND field_id = 'provider_id'",
            [UuidRegistry::uuidToBytes($uuid)]
        );
        if (!is_array($marker)) {
            $result->setValidationMessages(['uuid' => 'PractitionerRole not found']);
            return $result;
        }
        $uid = (int) ($marker['uid'] ?? 0);
        $facilityId = (int) ($marker['facility_id'] ?? 0);

        try {
            QueryUtils::inTransaction(function () use ($uid, $facilityId, $data): void {
                if (isset($data['role_code']) && is_string($data['role_code'])) {
                    $this->upsertEavRow($uid, $facilityId, 'role_code', $data['role_code']);
                }
                if (isset($data['specialty_code']) && is_string($data['specialty_code'])) {
                    $this->upsertEavRow($uid, $facilityId, 'specialty_code', $data['specialty_code']);
                }
            });

            $result->addData(['uuid' => $uuid]);
        } catch (\RuntimeException | \OpenEMR\Common\Database\SqlQueryException $e) {
            $this->getLogger()->error('PractitionerRole update failed', ['uuid' => $uuid, 'error' => $e->getMessage()]);
            $result->addInternalError($e->getMessage());
        }

        return $result;
    }

    /**
     * Updates the sibling EAV row in facility_user_ids for (uid, facility_id, field_id),
     * inserting a fresh row if none exists.
     */
    private function upsertEavRow(int $uid, int $facilityId, string $fieldId, string $value): void
    {
        $existing = QueryUtils::fetchSingleValue(
            "SELECT id FROM facility_user_ids WHERE uid = ? AND facility_id = ? AND field_id = ?",
            'id',
            [$uid, $facilityId, $fieldId]
        );
        if ($existing !== null) {
            QueryUtils::sqlStatementThrowException(
                "UPDATE facility_user_ids SET field_value = ? "
                . "WHERE uid = ? AND facility_id = ? AND field_id = ?",
                [$value, $uid, $facilityId, $fieldId]
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO facility_user_ids (uuid, uid, facility_id, field_id, field_value) "
                . "VALUES (?, ?, ?, ?, ?)",
                [
                    (new UuidRegistry(['table_name' => self::PRACTITIONER_ROLE_TABLE]))->createUuid(),
                    $uid,
                    $facilityId,
                    $fieldId,
                    $value,
                ]
            );
        }
    }

    public function search(array $search, $isAndCondition = true)
    {
        // note we are optimizing our key indexes by specifying our list_ids for list_options
        // note because facility_user_ids is denormalized and stores its form data in a Key Value list in order to grab
        // our data in the easiest format from the database and still be able to search on it, we do several joins
        // against the same table so we can grab our provider information, provider role info, and provider specialty
        // it seems like a pretty big query but its optimized pretty heavily on the indexes.  We may need a few more
        // indexes on facility_user_ids but we'll have to test this

        // NOTE: we do the complex joins on the telecom info (email,phone,fax, etc) so we can support searching on those fields as well
        // eventually normalizing the tables with the contact_telecom information
        $sql = "SELECT
                providers.facility_role_id AS id,
                providers.facility_role_uuid AS uuid,
                providers.user_name,
                providers.provider_id,
                providers.provider_uuid,
                providers.provider_last_updated,

                providers_location.location_uuid,
                providers_work_phone.work_phone,
                providers_work_phone.work_phone_use,
                providers_work_phone.work_phone_system,
                providers_work_fax.fax,
                providers_work_fax.fax_use,
                providers_work_fax.fax_system,
                providers_work_email.email,
                providers_work_email.email_use,
                providers_work_email.email_system,
                providers_url.url,
                providers_url.url_use,
                providers_url.url_system,

                facilities.facility_uuid,
                facilities.facility_name,
                role_codes.role_code,
                role_codes.role_title,
                role_codes.role_last_updated,

                specialty_codes.specialty_code,
                specialty_codes.specialty_title,
                specialty_codes.specialty_last_updated,

                physician_types.physician_type_codes,
                physician_types.physician_type,
                physician_types.physician_type_title
                FROM (
                    select
                        facility_user_ids.uuid AS facility_role_uuid,
                        facility_user_ids.id AS facility_role_id,
                        facility_user_ids.facility_id,
                        uid AS user_id,
                        -- we are treating the user_id as the provider id
                        -- TODO: @adunsulag figure out whether we should actually be using the user entered provider_id
                        uid AS provider_id,
                        users.uuid AS provider_uuid,
                        users.last_updated AS provider_last_updated,
                        users.physician_type,
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
                LEFT JOIN (
                    SELECT
                        uuid AS location_uuid
                        ,target_uuid AS location_provider_uuid
                    FROM uuid_mapping
                    WHERE resource='location'
                ) providers_location ON providers_location.location_provider_uuid = providers.provider_uuid
                LEFT JOIN (
                    SELECT
                        fax,
                        'work' AS fax_use,
                        'fax' AS fax_system,
                        id AS fax_user_id
                    FROM
                        users
                    WHERE
                        users.fax IS NOT NULL AND users.fax != ''
                ) providers_work_fax ON providers.user_id = providers_work_fax.fax_user_id
                LEFT JOIN (
                    SELECT
                        phonew1 AS work_phone,
                        'work' AS work_phone_use,
                        'phone' AS work_phone_system,
                        id AS work_user_id
                    FROM
                        users
                    WHERE
                        users.phonew1 IS NOT NULL AND users.phonew1 != ''
                ) providers_work_phone ON providers.user_id = providers_work_phone.work_user_id
                LEFT JOIN (
                    SELECT
                        email,
                        'work' AS email_use,
                        'email' AS email_system,
                        id AS email_user_id
                    FROM
                        users
                    WHERE
                        users.fax IS NOT NULL AND users.fax != ''
                ) providers_work_email ON providers.user_id = providers_work_email.email_user_id
                LEFT JOIN (
                    SELECT
                        url,
                        'work' AS url_use,
                        'url' AS url_system,
                        id AS url_user_id
                    FROM
                        users
                    WHERE
                        users.url IS NOT NULL AND users.url != ''
                ) providers_url ON providers.user_id = providers_url.url_user_id
                JOIN (
                    select
                        field_value AS role_code,
                        field_id,
                        role.title AS role_title,
                        facility_id,
                        uid AS user_id,
                        facility_user_ids.last_updated AS role_last_updated,
                        facility_user_ids.date_created AS role_date_created
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
                        uid AS user_id,
                        facilities_specialty.last_updated AS specialty_last_updated,
                        facilities_specialty.date_created AS specialty_date_created
                     FROM
                        facility_user_ids facilities_specialty
                    JOIN
                        list_options as specialty ON specialty.option_id = field_value
                    WHERE
                        field_id='specialty_code'
                        AND specialty.list_id='us-core-provider-specialty'
                ) specialty_codes ON
                    providers.user_id = specialty_codes.user_id AND providers.facility_id = specialty_codes.facility_id AND specialty_codes.field_id='specialty_code'
                LEFT JOIN (
                    select
                           codes AS physician_type_codes
                           ,option_id AS physician_type
                           ,title AS physician_type_title
                    FROM list_options types
                    WHERE types.list_id = 'physician_type'
                ) physician_types ON physician_types.physician_type = providers.physician_type ";
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
     * @param array<string, ISearchField|string> $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll(array $search = [], $isAndCondition = true)
    {
        $sqlBindArray = [];

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
            $whereClauses = [];
            $wildcardFields = ['user_name'];
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
