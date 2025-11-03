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
    private const CARE_TEAM_TABLE = "care_team";
    private const CARE_TEAM_MEMBER_TABLE = "care_team_member";
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
            self::CARE_TEAM_TABLE
        ]);
        parent::__construct(self::CARE_TEAM_TABLE);
    }
    public function getUuidFields(): array
    {
        return ['uuid', 'puuid'];
    }

    public function search($search, $isAndCondition = true)
    {
        $processingResult = new ProcessingResult();
        // Build the base query for care teams
        $records = $this->getCareTeamRecordsForSearch($search, $isAndCondition);
        if (empty($records)) {
            return $processingResult;
        }
        $ctTeamIds = array_column($records, 'id');
        // Fetch related data for care teams
        $facilities = $this->getCareTeamFacilities($ctTeamIds);
        $users = $this->getCareTeamProviders($ctTeamIds);
        $contacts = $this->getCareTeamContacts($ctTeamIds);

        foreach ($records as &$record) {
            $teamId = $record['id'];
            $record['facilities'] = $facilities[$teamId] ?? [];
            $record['providers'] = $users[$teamId] ?? [];
            $record['contacts'] = $contacts[$teamId] ?? [];
            $processingResult->addData($record);
        }
        return $processingResult;
    }

    protected function getCareTeamRecordsForSearch(array $search, bool $isAndCondition): array {

        // now we are going to grab our related
        $sql = "SELECT
                    ct.id,
                    ct.uuid,
                    ct.team_name,
                    ct.status as care_team_status,
                    ct.date_updated as date,
                    ct.pid,
                    pd.puuid,
                    lo.title as care_team_status_title
                FROM
                    " . self::CARE_TEAM_TABLE . " ct
                JOIN (
                    select
                        uuid AS puuid
                         , pid AS patient_id
                    FROM patient_data
                ) AS pd ON ct.pid = pd.patient_id
                LEFT JOIN
                    list_options lo ON lo.option_id = ct.status AND lo.list_id = 'Care_Team_Status'";

        // Add search conditions
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
        $sql .= $whereClause->getFragment();

        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults = QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $records = [];
        while ($row = QueryUtils::fetchArrayFromResultSet($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            // Only include if we have valid data
            $records[] = $resultRecord;
        }
        return $records;
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
        $row = parent::createResultRecordFromDatabaseResult($row);
        $row['team_name'] ??= '';
        $row['care_team_status'] ??= 'active';
        $row['cate_team_status_title'] ??= '';
        $row['date'] ??= null;
        $row['providers'] = [];
        $row['facilities'] = [];
        $row['contacts'] = [];
        return $row;
    }

    private function getCareTeamProviders(array $careTeamIds): array
    {
        // physician_type_code is not stored in users; derive from list_options.
        $selectColumns = "ctm.care_team_id, ctm.user_id, ctm.role, ctm.facility_id, ctm.provider_since, ctm.status, ctm.note,
                         u.uuid as provider_uuid, u.fname, u.lname, u.physician_type,
                         lo1.title as role_title, lo2.title as physician_type_title,
                         f.uuid as facility_uuid, f.name as facility_name, lo2.codes as physician_type_codes";

        // physician_type_codes now always provided by lo2.codes

        $sql = "SELECT DISTINCT $selectColumns
                FROM " . self::CARE_TEAM_MEMBER_TABLE . " ctm
                JOIN users u ON ctm.user_id = u.id
                LEFT JOIN facility f ON ctm.facility_id = f.id
                LEFT JOIN list_options lo1 ON lo1.option_id = ctm.role AND lo1.list_id = 'care_team_roles'
                LEFT JOIN list_options lo2 ON lo2.option_id = u.physician_type AND lo2.list_id = 'physician_type'
                WHERE ctm.care_team_id IN (" . implode(',', array_fill(0, count($careTeamIds), '?')) . ")";

        $providers = [];
        $result = QueryUtils::sqlStatementThrowException($sql, $careTeamIds);
        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            if (!empty($row['physician_type_codes'])) {
                $row['physician_type_codes'] = preg_replace('/^SNOMED-CT:/', '', (string) $row['physician_type_codes']);
            }
            // Group by care team id
            $careTeamId = $row['care_team_id'] ?? null;
            if (!isset($providers[$careTeamId])) {
                $providers[$careTeamId] = [];
            }

            $providers[$careTeamId][] = [
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

        return $providers;
    }

    private function getCareTeamFacilities(array $careTeamIds): array
    {
        $sql = "SELECT DISTINCT
                    ctm.care_team_id,
                    f.id,
                    f.uuid,
                    f.name,
                    f.facility_npi,
                    f.facility_taxonomy
                FROM " . self::CARE_TEAM_MEMBER_TABLE . " ctm
                JOIN facility f ON ctm.facility_id = f.id
                WHERE ctm.care_team_id IN (" . implode(',', array_fill(0, count($careTeamIds), '?')) . ")";

        $facilities = [];
        $result = QueryUtils::sqlStatementThrowException($sql, $careTeamIds);
        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $careTeamId = $row['care_team_id'];
            if (!isset($facilities[$careTeamId])) {
                $facilities[$careTeamId] = [];
            }

            $facilities[$careTeamId][] = [
                'uuid' => UuidRegistry::uuidToString($row['uuid']),
                'name' => $row['name'],
                'facility_npi' => $row['facility_npi'],
                'facility_taxonomy' => $row['facility_taxonomy']
            ];
        }

        return $facilities;
    }

    protected function getCareTeamContacts(array $careTeamIds): array {
        // TODO: @adunsulag Implement this functionality
        return [];
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
            self::CARE_TEAM_TABLE,
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


    public function hasActiveCareTeam($pid)
    {
        $result = sqlQuery(
            "SELECT COUNT(*) as count FROM " . self::CARE_TEAM_TABLE . " WHERE pid = ? AND (status = 'active' OR status IS NULL)",
            [$pid]
        );

        return ($result['count'] ?? 0) > 0;
    }

    public function saveCareTeam($pid, $teamName, $team)
    {
        // Create UUIDs for the table if not already present
        UuidRegistry::createMissingUuidsForTables([self::CARE_TEAM_TABLE]);

        // Create or update main care team record
        $careTeamId = $this->createOrUpdateCareTeam($pid, $teamName);

        // Get existing members keyed by user_id for comparison
        $existingMembers = $this->getExistingCareTeamMembers($careTeamId);
        $existingMembersByUserId = [];
        foreach ($existingMembers as $index => $member) {
            if (!empty($member['user_id'])) {
                $existingMembersByUserId[intval($member['user_id'])] = $index;
            }
        }
        // Process submitted members
        foreach ($team as $entry) {
            if (!empty($entry['user_id'])) {
                $userId = intval($entry['user_id'] ?? 0);

                if (!$userId) {
                    continue; // Skip invalid user_id
                }

                $index = $existingMembersByUserId[$userId] ?? -1;
                if (isset($existingMembers[$index])) {
                    // Update existing member
                    $this->updateCareTeamMember($existingMembers[$index]['id'], $entry);
                    unset($existingMembers[$index]);
                } else {
                    // Insert new member
                    $this->insertCareTeamMember($careTeamId, $entry);
                }

            }
            // TODO: @adunsulag Handle contact members when contact support is added
        }

        // Mark removed members as inactive
        foreach ($existingMembers as $member) {
            $this->markMemberAsInactive($member['id']);
        }

        // Trigger care team update event for FHIR sync if needed
        $this->triggerCareTeamUpdateEvent($pid, $teamName, $team);
    }

    /**
     * Create or update main care team record
     */
    private function createOrUpdateCareTeam($pid, $teamName)
    {
        $createdBy = $_SESSION['authUserID'] ?? null;

        // Check if care team already exists for this patient
        $existingTeam = sqlQuery(
            "SELECT id FROM " . self::CARE_TEAM_TABLE . " WHERE pid = ?",
            [$pid]
        );

        if ($existingTeam) {
            // Update existing team
            QueryUtils::sqlStatementThrowException(
                "UPDATE " . self::CARE_TEAM_TABLE . "
                 SET team_name = ?, date_updated = NOW(), updated_by = ?
                 WHERE id = ?",
                [$teamName, $createdBy, $existingTeam['id']]
            );
            return $existingTeam['id'];
        } else {
            // Create new team
            $uuid = UuidRegistry::getRegistryForTable(self::CARE_TEAM_TABLE)->createUuid();
            $careTeamId = sqlInsert(
                "INSERT INTO " . self::CARE_TEAM_TABLE . "
                 (uuid, pid, team_name, status, created_by, date_created)
                 VALUES (?, ?, ?, 'active', ?, NOW())",
                [$uuid, $pid, $teamName, $createdBy]
            );
            return $careTeamId;
        }
    }

    /**
     * Insert new care team member
     */
    private function insertCareTeamMember($careTeamId, $memberData)
    {
        $createdBy = $_SESSION['authUserID'] ?? null;
        $userId = intval($memberData['user_id'] ?? 0);
        $role = trim($memberData['role'] ?? '');
        $facilityId = intval($memberData['facility_id'] ?? 0) ?: null;
        $providerSince = trim($memberData['provider_since'] ?? '') ?: null;
        $status = trim($memberData['status'] ?? 'active');
        $note = trim($memberData['note'] ?? '');

        sqlInsert(
            "INSERT INTO " . self::CARE_TEAM_MEMBER_TABLE . "
             (care_team_id, user_id, contact_id, role, facility_id, provider_since, status, note, created_by, date_created)
             VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, NOW())",
            [$careTeamId, $userId, $role, $facilityId, $providerSince, $status, $note, $createdBy]
        );
    }

    /**
     * Update existing care team member
     */
    private function updateCareTeamMember($memberId, $memberData)
    {
        $updatedBy = $_SESSION['authUserID'] ?? null;
        $role = trim($memberData['role'] ?? '');
        $facilityId = intval($memberData['facility_id'] ?? 0) ?: null;
        $providerSince = trim($memberData['provider_since'] ?? '') ?: null;
        $status = trim($memberData['status'] ?? 'active');
        $note = trim($memberData['note'] ?? '');

        QueryUtils::sqlStatementThrowException(
            "UPDATE " . self::CARE_TEAM_MEMBER_TABLE . "
             SET role = ?, facility_id = ?, provider_since = ?, status = ?, note = ?,
                 updated_by = ?, date_updated = NOW()
             WHERE id = ?",
            [$role, $facilityId, $providerSince, $status, $note, $updatedBy, $memberId]
        );
    }

    /**
     * Mark care team member as inactive
     */
    private function markMemberAsInactive($memberId)
    {
        $updatedBy = $_SESSION['authUserID'] ?? null;

        QueryUtils::sqlStatementThrowException(
            "UPDATE " . self::CARE_TEAM_MEMBER_TABLE . "
             SET status = 'inactive', updated_by = ?, date_updated = NOW()
             WHERE id = ?",
            [$updatedBy, $memberId]
        );
    }

    public function getCareTeamData($pid)
    {
        // physician_type_code comes from list_options;
        $selectColumns = "ctm.*, ct.team_name, ct.status as team_status,
                     u.fname, u.lname, u.username, u.physician_type,
                     f.name as facility_name, f.facility_npi,
                     lo1.title as role_title, lo2.title as status_title,
                     lo3.title as physician_type_title, lo3.codes as physician_type_code";
        $sql = "SELECT $selectColumns
         FROM " . self::CARE_TEAM_TABLE . " ct
         LEFT JOIN (
            select * FROM " . self::CARE_TEAM_MEMBER_TABLE . "
            WHERE status != 'inactive' AND status !='entered-in-error'
         ) ctm ON ct.id = ctm.care_team_id
         LEFT JOIN users u ON ctm.user_id = u.id
         LEFT JOIN facility f ON ctm.facility_id = f.id
         LEFT JOIN list_options lo1 ON lo1.option_id = ctm.role AND lo1.list_id = 'care_team_roles'
         LEFT JOIN list_options lo2 ON lo2.option_id = ctm.status AND lo2.list_id = 'Care_Team_Status'
         LEFT JOIN list_options lo3 ON lo3.option_id = u.physician_type AND lo3.list_id = 'physician_type'
         WHERE ct.pid = ?
         ORDER BY ct.team_name, ctm.date_created ASC";

        $careTeamResult = QueryUtils::sqlStatementThrowException($sql, [$pid]
        );

        $careTeams = [];
        $currentTeamName = null;


        while ($member = QueryUtils::fetchArrayFromResultSet($careTeamResult)) {
            $teamName = $member['team_name'] ?? 'default';

            if (!isset($careTeams[$teamName])) {
                $careTeams[$teamName] = [
                    'team_name' => $teamName,
                    'team_status' => $member['team_status'],
                    'members' => [],
                    'member_count' => 0
                ];
            }

            $careTeams[$teamName]['members'][] = [
                'id' => $member['id'],
                'care_team_id' => $member['care_team_id'],
                'user_id' => $member['user_id'],
                'user_name' => trim(($member['fname'] ?? '') . ' ' . ($member['lname'] ?? '')),
                'username' => $member['username'],
                'role' => $member['role'],
                'role_title' => $member['role_title'] ?? $member['role'],
                'physician_type' => $member['physician_type'] ?? '',
                'physician_type_code' => $member['physician_type_code'] ?? '',
                'physician_type_title' => $member['physician_type_title'] ?? '',
                'facility_id' => $member['facility_id'],
                'facility_name' => $member['facility_name'] ?? '',
                'facility_npi' => $member['facility_npi'] ?? '',
                'provider_since' => $member['provider_since'],
                'provider_since_formatted' => !empty($member['provider_since']) ? oeFormatShortDate($member['provider_since']) : '',
                'status' => $member['status'],
                'status_title' => $member['status_title'] ?? $member['status'],
                'note' => $member['note'] ?? '',
                'date_created' => $member['date_created'],
                'date_updated' => $member['date_updated']
            ];

            $careTeams[$teamName]['member_count']++;
        }

        // Return the primary team or create empty structure
        if (!empty($careTeams)) {
            return reset($careTeams); // Get first team
        }

        return [
            'team_name' => '',
            'team_status' => 'active',
            'members' => [],
            'member_count' => 0
        ];
    }

    /**
     * Get existing care team members keyed by user_id
     */
    private function getExistingCareTeamMembers($careTeamId)
    {
        $result = QueryUtils::sqlStatementThrowException(
            "SELECT ctm.* FROM " . self::CARE_TEAM_MEMBER_TABLE . " ctm WHERE ctm.care_team_id = ?",
            [$careTeamId]
        );

        $members = [];
        while ($row = QueryUtils::fetchArrayFromResultSet($result)) {
            $members[] = $row;
        }
        return $members;
    }

    private function triggerCareTeamUpdateEvent($pid, $teamName, $members)
    {
        // This would trigger an event for FHIR resource update
        // You can implement event dispatching here if needed
        // Example:
        // $this->getEventDispatcher()->dispatch(
        //     new CareTeamUpdateEvent($pid, $teamName, $members),
        //     CareTeamUpdateEvent::EVENT_HANDLE
        // );
    }

    /**
     * Create care team members
     */
    public function createCareTeamMembers($pid, $teamName, $members)
    {
        $createdBy = $_SESSION['authUserID'] ?? null;

        // First, create or get the care team
        $careTeamId = $this->createOrUpdateCareTeam($pid, $teamName);

        foreach ($members as $member) {
            $insertData = [
                'care_team_id' => $careTeamId,
                'user_id' => $member['user_id'],
                'contact_id' => null, // Skip contact support for now
                'role' => $member['role'] ?? null,
                'facility_id' => $member['facility_id'] ?? null,
                'provider_since' => $member['provider_since'] ?? null,
                'status' => $member['status'] ?? 'active',
                'note' => $member['note'] ?? null,
                'created_by' => $createdBy
            ];

            $insert = $this->buildInsertColumns($insertData);
            $sql = "INSERT INTO " . self::CARE_TEAM_MEMBER_TABLE . " SET " . $insert['set'];
            QueryUtils::sqlInsert($sql, $insert['bind']);
        }

        return true;
    }

    /**
     * Update care team status (for deactivation/reactivation)
     */
    public function updateCareTeamStatus($pid, $teamName, $status)
    {
        $updatedBy = $_SESSION['authUserID'] ?? null;

        $sql = "UPDATE " . self::CARE_TEAM_TABLE . "
                SET status = ?, date_updated = NOW(), updated_by = ?
                WHERE pid = ? AND team_name = ?";

        return QueryUtils::sqlStatementThrowException($sql, [$status, $updatedBy, $pid, $teamName]);
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
