<?php

/**
 * CareTeamService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class CareTeamService extends BaseService
{
    private const PATIENT_TABLE = "patient_data";
    private const PRACTITIONER_TABLE = "users";
    private const FACILITY_TABLE = "facility";
    private const PATIENT_HISTORY_TABLE = "patient_history"; //Legacy
    private const CARE_TEAMS_TABLE = "care_teams";
    public const MAPPING_RESOURCE_NAME = "CareTeam";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        UuidRegistry::createMissingUuidsForTables([
            self::PATIENT_TABLE,
            self::PRACTITIONER_TABLE,
            self::FACILITY_TABLE,
            self::CARE_TEAMS_TABLE
        ]);
        parent::__construct(self::CARE_TEAMS_TABLE);
    }

    public function search($search, $isAndCondition = true)
    {
        // Build the base query for care teams
        $sql = "SELECT 
                    ct.uuid,
                    ct.team_name,
                    ct.status as care_team_status,
                    ct.date_updated as date,
                    pd.uuid as puuid,
                    pd.pid,
                    GROUP_CONCAT(DISTINCT ct.id) as member_ids,
                    lo.title as care_team_status_title
                FROM
                    care_teams ct
                JOIN
                    patient_data pd ON ct.pid = pd.pid
                LEFT JOIN
                    list_options lo ON lo.option_id = ct.status AND lo.list_id = 'Care_Team_Status'";

        // Add search conditions
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
        $sql .= $whereClause->getFragment();

        // Group by team to get unique care teams
        $sql .= " GROUP BY ct.pid, ct.team_name, ct.status";

        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults = QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            // Only include if we have valid data
            if (!empty($resultRecord['providers']) || !empty($resultRecord['facilities'])) {
                $processingResult->addData($resultRecord);
            }
        }
        return $processingResult;
    }

    /**
     * Returns a list of careTeams matching optional search criteria.
     *
     * @param  $search         search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @param  $puuidBind      - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data payload.
     */
    public function getAll($search = [], $isAndCondition = true, $puuidBind = null)
    {
        if (!empty($puuidBind)) {
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

        if (isset($puuidBind)) {
            $newSearch['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }

        return $this->search($newSearch, $isAndCondition);
    }

    public function createResultRecordFromDatabaseResult($row)
    {
        // Fetch detailed information for all team members
        $providers = $this->getCareTeamProviders($row['pid'], $row['team_name'] ?? '');
        $facilities = $this->getCareTeamFacilities($row['pid'], $row['team_name'] ?? '');

        // Generate a unique UUID for this care team instance
        // If team_name is present, use it; otherwise use the first member's UUID
        $careTeamUuid = $row['uuid'] ?? null;
        if (empty($careTeamUuid) && !empty($row['team_name']) && !empty($row['pid'])) {
            // Generate a deterministic UUID based on patient and team name
            $careTeamUuid = UuidRegistry::uuidToBytes(
                (new UuidRegistry())->createUuid()
            );
        }

        return [
            'uuid' => UuidRegistry::uuidToString($careTeamUuid),
            'puuid' => UuidRegistry::uuidToString($row['puuid']),
            'team_name' => $row['team_name'] ?? '',
            'care_team_status' => $row['care_team_status'] ?? 'active',
            'care_team_status_title' => $row['care_team_status_title'] ?? '',
            'date' => $row['date'] ?? null,
            'providers' => $providers,
            'facilities' => $facilities
        ];
    }

    private function getCareTeamProviders($pid, $teamName = '')
    {
        // physician_type_code is not stored in users; derive from list_options.


        $selectColumns = "ct.user_id, ct.role, ct.facility_id, ct.provider_since, ct.status, ct.note,
                         u.uuid as provider_uuid, u.fname, u.lname, u.physician_type,
                         lo1.title as role_title, lo2.title as physician_type_title,
                         f.uuid as facility_uuid, f.name as facility_name, lo2.codes as physician_type_codes";

        // physician_type_codes now always provided by lo2.codes

        $sql = "SELECT DISTINCT $selectColumns
                FROM care_teams ct
                JOIN users u ON ct.user_id = u.id
                LEFT JOIN facility f ON ct.facility_id = f.id
                LEFT JOIN list_options lo1 ON lo1.option_id = ct.role AND lo1.list_id = 'care_team_roles'
                LEFT JOIN list_options lo2 ON lo2.option_id = u.physician_type AND lo2.list_id = 'physician_type'
                WHERE ct.pid = ?";

        $params = [$pid];

        if (!empty($teamName)) {
            $sql .= " AND ct.team_name = ?";
            $params[] = $teamName;
        }

        $result = sqlStatement($sql, $params);
        $providers = [];

        while ($row = sqlFetchArray($result)) {
            if (!empty($row['physician_type_codes'])) {
                $row['physician_type_codes'] = preg_replace('/^SNOMED-CT:/', '', (string) $row['physician_type_codes']);
            }
            // Group by provider to handle multiple facilities
            $providerId = $row['user_id'];
            if (!isset($providers[$providerId])) {
                $providers[$providerId] = [];
            }

            $providers[$providerId][] = [
                'provider_uuid' => UuidRegistry::uuidToString($row['provider_uuid']),
                'provider_name' => $row['fname'] . ' ' . $row['lname'],
                'role' => $row['role'],
                'role_title' => $row['role_title'] ?? $row['role'],
                'physician_type' => $row['physician_type'] ?? '',
                'physician_type_codes' => $row['physician_type_codes'] ?? '',
                'physician_type_title' => $row['physician_type_title'] ?? '',
                'facility_uuid' => $row['facility_uuid'] ? UuidRegistry::uuidToString($row['facility_uuid']) : null,
                'facility_name' => $row['facility_name'] ?? '',
                'provider_since' => $row['provider_since'],
                'status' => $row['status'],
                'note' => $row['note']
            ];
        }

        return array_values($providers);
    }

    private function getCareTeamFacilities($pid, $teamName = '')
    {
        $sql = "SELECT DISTINCT
                    f.id,
                    f.uuid,
                    f.name,
                    f.facility_npi,
                    f.facility_taxonomy
                FROM care_teams ct
                JOIN facility f ON ct.facility_id = f.id
                WHERE ct.pid = ? AND ct.facility_id IS NOT NULL AND ct.facility_id != 0";

        $params = [$pid];

        if (!empty($teamName)) {
            $sql .= " AND ct.team_name = ?";
            $params[] = $teamName;
        }

        $result = sqlStatement($sql, $params);
        $facilities = [];

        while ($row = sqlFetchArray($result)) {
            $facilities[] = [
                'uuid' => UuidRegistry::uuidToString($row['uuid']),
                'name' => $row['name'],
                'facility_npi' => $row['facility_npi'],
                'facility_taxonomy' => $row['facility_taxonomy']
            ];
        }

        return $facilities;
    }

    /**
     * Returns a single careTeam record by id.
     *
     * @param $uuid      - The careTeam uuid identifier in string format.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data payload.
     */
    public function getOne($uuid, $puuidBind = null)
    {
        $processingResult = new ProcessingResult();

        $isValid = BaseValidator::validateId(
            "uuid",
            self::CARE_TEAMS_TABLE,
            $uuid,
            true
        );
        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        if (!empty($puuidBind)) {
            $isValid = BaseValidator::validateId(
                "uuid",
                self::PATIENT_TABLE,
                $puuidBind,
                true
            );
            if ($isValid !== true) {
                $validationMessages = [
                    'puuid' => ["invalid or nonexisting value" => " value " . $puuidBind,]
                ];
                $processingResult->setValidationMessages($validationMessages);
                return $processingResult;
            }
        }

        $search = [];
        if (isset($puuidBind)) {
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }
        $search['uuid'] = new TokenSearchField('uuid', $uuid, true);

        return $this->search($search);
    }

    /**
     * Create care team members
     */
    public function createCareTeamMembers($pid, $teamName, $members)
    {
        $createdBy = $_SESSION['authUserID'] ?? null;

        foreach ($members as $member) {
            $insertData = [
                'uuid' => UuidRegistry::getRegistryForTable(self::CARE_TEAMS_TABLE)->createUuid(),
                'pid' => $pid,
                'team_name' => $teamName,
                'user_id' => $member['user_id'],
                'role' => $member['role'] ?? null,
                'facility_id' => $member['facility_id'] ?? null,
                'provider_since' => $member['provider_since'] ?? null,
                'status' => $member['status'] ?? 'active',
                'note' => $member['note'] ?? null
            ];

            $insert = $this->buildInsertColumns($insertData);
            $sql = "INSERT INTO " . self::CARE_TEAMS_TABLE . " SET " . $insert['set'];
            QueryUtils::sqlInsert($sql, $insert['bind']);
        }

        return true;
    }

    /**
     * Update care team status (for deactivation/reactivation)
     */
    public function updateCareTeamStatus($pid, $teamName, $status)
    {
        $sql = "UPDATE " . self::CARE_TEAMS_TABLE . " 
                SET status = ?, date_updated = NOW() 
                WHERE pid = ? AND team_name = ?";

        return sqlStatement($sql, [$status, $pid, $teamName]);
    }

    /**
     * Legacy
     * Create care team history record
     */
    public function createCareTeamHistory($pid, $oldProviders, $oldFacilities)
    {
        // we should never be null here but for legacy reasons we are going to default to this
        $createdBy = $_SESSION['authUserID'] ?? null; // we don't let anyone else but the current user be the createdBy

        $insertData = [
            'pid' => $pid, 'care_team_provider' => $oldProviders, 'care_team_facility' => $oldFacilities,
            'history_type_key' => 'care_team_history',
            'created_by' => $createdBy,
            'uuid' => UuidRegistry::getRegistryForTable(self::PATIENT_HISTORY_TABLE)->createUuid()
        ];
        $insert = $this->buildInsertColumns($insertData);

        $sql = "INSERT INTO " . self::PATIENT_HISTORY_TABLE . " SET " . $insert['set'];
        return QueryUtils::sqlInsert($sql, $insert['bind']);
    }
}
