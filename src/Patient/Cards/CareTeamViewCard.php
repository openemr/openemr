<?php

/**
 * @package   OpenEMR Care Team
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Patient\Cards;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent;
use OpenEMR\Services\CareTeamService;

class CareTeamViewCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/card/manage_care_team.html.twig';
    private const CARD_ID_EXPAND = 'careteam_ps_expand';
    private const CARD_ID = 'care_team';

    /**
     * @var CareTeamService
     */
    private $careTeamService;

    public function __construct(private $pid, array $opts = [])
    {
        $opts = $this->setupOpts($opts);
        parent::__construct($opts);

        // Initialize the Care Team Service
        $this->careTeamService = new CareTeamService();

        // Handle form submission if this is a POST request
        $this->handleFormSubmission();
    }

    /**
     * @return array
     */
    public function getViewArgs(): string
    {
        return self::TEMPLATE_FILE;
    }

    private function setupOpts(array $opts): array
    {
        $initiallyCollapsed = $this->getUserCardSetting(self::CARD_ID_EXPAND) == 0;
        $authCheck = AclMain::aclCheckCore('patients', 'demo', '', 'write');

        $newOpts = [
            'acl' => ['patients', 'demo'],
            'initiallyCollapsed' => $initiallyCollapsed,
            'add' => false,
            'edit' => true,
            'collapse' => true,
            'templateFile' => self::TEMPLATE_FILE,
            'identifier' => self::CARD_ID,
            'title' => xl('Care Team'),
            'templateVariables' => [
                'title' => xl("Care Team"),
                'id' => self::CARD_ID_EXPAND,
                'btnLabel' => "Edit",
                'btnLink' => "javascript:toggleEditMode(true);",
                'linkMethod' => 'html',
                'initiallyCollapsed' => $initiallyCollapsed ? true : false,
                'auth' => $authCheck
            ]
        ];

        return $newOpts;
    }

    public function getTemplateVariables(): array
    {
        $templateVars = parent::getTemplateVariables();
        $dispatchResult = $this->getEventDispatcher()->dispatch(new RenderEvent(self::CARD_ID), RenderEvent::EVENT_HANDLE);

        $templateVars['prependedInjection'] = $dispatchResult->getPrependedInjection();
        $templateVars['appendedInjection'] = $dispatchResult->getAppendedInjection();
        $templateVars['careTeamData'] = $this->getCareTeamData();
        $templateVars['hasActiveTeam'] = $this->hasActiveCareTeam();
        $templateVars = array_merge($templateVars, self::getFormManagementData($this->pid));
        return $templateVars;
    }

    private function handleFormSubmission()
    {
        if (($_POST['save_care_team'] ?? '') === 'true') {
            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
                CsrfUtils::csrfNotVerified();
            }

            $teamName = trim($_POST['team_name'] ?? '');
            $team = $_POST['team'] ?? [];

            if (!$this->pid || empty($team)) {
                die(xlt("Invalid request."));
            }

            $this->saveCareTeam($teamName, $team);
        }
    }

    private function saveCareTeam($teamName, $team)
    {
        // Create UUIDs for the table if not already present
        UuidRegistry::createMissingUuidsForTables(['care_teams']);

        // Get existing care team to compare
        $existingMembers = $this->getExistingCareTeamMembers($this->pid, $teamName);

        // Delete existing care team members for this team
        sqlStatement(
            "DELETE FROM care_teams WHERE pid = ? AND (team_name = ? OR team_name IS NULL OR team_name = '')",
            [$this->pid, $teamName]
        );

        // Insert new care team members with proper UUID handling
        $members = [];
        foreach ($team as $entry) {
            $userId = intval($entry['user_id'] ?? 0);
            $role = trim($entry['role'] ?? '');
            $facilityId = intval($entry['facility_id'] ?? 0);
            $providerSince = trim($entry['provider_since'] ?? '');
            $status = trim($entry['status'] ?? 'active');
            $note = trim($entry['note'] ?? '');

            if ($userId) {
                // Generate UUID for each care team member
                $uuid = UuidRegistry::getRegistryForTable('care_teams')->createUuid();

                sqlInsert(
                    "INSERT INTO care_teams
                    (uuid, pid, user_id, role, facility_id, provider_since, status, note, team_name, date_created)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $uuid,
                        $this->pid,
                        $userId,
                        $role,
                        $facilityId ?: null,
                        $providerSince ?: null,
                        $status,
                        $note,
                        $teamName
                    ]
                );

                $members[] = [
                    'user_id' => $userId,
                    'role' => $role,
                    'facility_id' => $facilityId,
                    'provider_since' => $providerSince,
                    'status' => $status,
                    'note' => $note
                ];
            }
        }

        // Trigger care team update event for FHIR sync if needed
        $this->triggerCareTeamUpdateEvent($this->pid, $teamName, $members);
    }

    private function getExistingCareTeamMembers($pid, $teamName)
    {
        $result = sqlStatement(
            "SELECT * FROM care_teams WHERE pid = ? AND team_name = ?",
            [$pid, $teamName]
        );

        $members = [];
        while ($row = sqlFetchArray($result)) {
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

    private function getCareTeamData()
    {
        // physician_type_code comes from list_options;
        $selectColumns = "ct.*, u.fname, u.lname, u.username, u.physician_type, 
                     f.name as facility_name, f.facility_npi,
                     lo1.title as role_title, lo2.title as status_title,
                     lo3.title as physician_type_title, lo3.codes as physician_type_code";

        $careTeamResult = sqlStatement(
            "SELECT $selectColumns
         FROM care_teams ct
         LEFT JOIN users u ON ct.user_id = u.id
         LEFT JOIN facility f ON ct.facility_id = f.id
         LEFT JOIN list_options lo1 ON lo1.option_id = ct.role AND lo1.list_id = 'care_team_roles'
         LEFT JOIN list_options lo2 ON lo2.option_id = ct.status AND lo2.list_id = 'Care_Team_Status'
         LEFT JOIN list_options lo3 ON lo3.option_id = u.physician_type AND lo3.list_id = 'physician_type'
         WHERE ct.pid = ?
         ORDER BY ct.team_name, ct.date_created ASC",
            [$this->pid]
        );

        $careTeams = [];
        $currentTeamName = null;

        while ($member = sqlFetchArray($careTeamResult)) {
            $teamName = $member['team_name'] ?? 'default';

            if (!isset($careTeams[$teamName])) {
                $careTeams[$teamName] = [
                    'team_name' => $teamName,
                    'members' => [],
                    'member_count' => 0
                ];
            }

            $careTeams[$teamName]['members'][] = [
                'id' => $member['id'],
                'uuid' => !empty($member['uuid']) ? UuidRegistry::uuidToString($member['uuid']) : '',
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
            'members' => [],
            'member_count' => 0
        ];
    }

    private function hasActiveCareTeam()
    {
        $result = sqlQuery(
            "SELECT COUNT(*) as count FROM care_teams WHERE pid = ? AND (status = 'active' OR status IS NULL)",
            [$this->pid]
        );

        return ($result['count'] ?? 0) > 0;
    }

    private function getUserCardSetting($settingName)
    {
        return \getUserSetting($settingName);
    }

    /**
     * Static method to get form management data for the care team edit page
     * Enhanced for USCDI v5 compliance
     */
    public static function getFormManagementData($pid)
    {
        // Get users with physician type information for role mapping
        $usersResult = sqlStatement(
            "SELECT u.id, u.username, u.fname, u.lname, u.physician_type, lo.codes AS physician_type_code FROM users u LEFT JOIN list_options lo ON lo.list_id='physician_type' AND lo.option_id=u.physician_type WHERE active = 1 AND username IS NOT NULL AND fname IS NOT NULL 
             ORDER BY lname, fname"
        );

        $templateData['users'] = [];
        while ($user = sqlFetchArray($usersResult)) {
            $templateData['users'][] = [
                'id' => $user['id'],
                'name' => $user['lname'] . ", " . $user['fname'],
                'username' => $user['username'],
                'physician_type' => $user['physician_type'],
                'physician_type_code' => $user['physician_type_code']
            ];
        }

        // Get facilities with NPI for organization identification
        $facilitiesResult = sqlStatement(
            "SELECT id, name, facility_npi, facility_taxonomy 
             FROM facility 
             WHERE service_location = 1 OR billing_location = 1
             ORDER BY name"
        );

        $templateData['facilities'] = [];
        while ($facility = sqlFetchArray($facilitiesResult)) {
            $templateData['facilities'][] = [
                'id' => $facility['id'],
                'name' => $facility['name'],
                'npi' => $facility['facility_npi'],
                'taxonomy' => $facility['facility_taxonomy']
            ];
        }

        // Get roles - ensure we have USCDI v5 compatible roles
        $rolesResult = sqlStatement(
            "SELECT option_id, title, codes 
             FROM list_options 
             WHERE list_id = 'care_team_roles' AND activity = 1 
             ORDER BY is_default DESC, seq, title"
        );

        $templateData['roles'] = [];
        while ($role = sqlFetchArray($rolesResult)) {
            $templateData['roles'][] = [
                'id' => $role['option_id'],
                'title' => $role['title'],
                'codes' => $role['codes'] // SNOMED CT codes for USCDI v5
            ];
        }

        // Get statuses - ensure we have FHIR-compatible statuses
        $statusesResult = sqlStatement(
            "SELECT option_id, title 
             FROM list_options 
             WHERE list_id = 'Care_Team_Status' AND activity = 1 
             ORDER BY is_default DESC, seq, title"
        );

        $templateData['statuses'] = [];
        while ($status = sqlFetchArray($statusesResult)) {
            $templateData['statuses'][] = [
                'id' => $status['option_id'],
                'title' => $status['title']
            ];
        }

        // Get existing care teams (support multiple teams)
        $careTeamResult = sqlStatement(
            "SELECT ct.*, u.fname, u.lname, u.username, u.physician_type, lo.codes AS physician_type_code
                 FROM care_teams ct
                 LEFT JOIN users u ON ct.user_id = u.id
                 LEFT JOIN list_options lo ON lo.option_id = u.physician_type AND lo.list_id = 'physician_type'
                 WHERE ct.pid = ?
                 ORDER BY ct.team_name, ct.date_created ASC",
            [$pid]
        );

        $existingCareTeam = [];
        $teamNames = [];

        while ($member = sqlFetchArray($careTeamResult)) {
            $teamName = $member['team_name'] ?? 'default';

            if (!in_array($teamName, $teamNames)) {
                $teamNames[] = $teamName;
            }

            $existingCareTeam[] = [
                'team_name' => $teamName,
                'user_id' => $member['user_id'],
                'role' => $member['role'],
                'facility_id' => $member['facility_id'],
                'provider_since' => $member['provider_since'],
                'status' => $member['status'],
                'note' => $member['note'],
                'user_name' => trim(($member['lname'] ?? '') . ", " . ($member['fname'] ?? '')),
                'physician_type' => $member['physician_type'],
                'physician_type_code' => $member['physician_type_code']
            ];
        }

        // Build option strings
        $templateData['user_options'] = "<option value=''></option>";
        foreach ($templateData['users'] as $user) {
            $extra = $user['physician_type'] ? " (" . text($user['physician_type']) . ")" : "";
            $templateData['user_options'] .= "<option value='" . attr($user['id']) . "' "
                . "data-physician-type='" . attr($user['physician_type']) . "' "
                . "data-physician-code='" . attr($user['physician_type_code']) . "'>"
                . text($user['name'] . $extra) . "</option>";
        }

        $templateData['facility_options'] = "<option value=''></option>";
        foreach ($templateData['facilities'] as $facility) {
            $extra = $facility['npi'] ? " (NPI: " . text($facility['npi']) . ")" : "";
            $templateData['facility_options'] .= "<option value='" . attr($facility['id']) . "' "
                . "data-npi='" . attr($facility['npi']) . "' "
                . "data-taxonomy='" . attr($facility['taxonomy']) . "'>"
                . text($facility['name'] . $extra) . "</option>";
        }

        $templateData['role_options'] = "<option value=''></option>";
        foreach ($templateData['roles'] as $role) {
            $templateData['role_options'] .= "<option value='" . attr($role['id']) . "' "
                . "data-codes='" . attr($role['codes']) . "'>"
                . text($role['title']) . "</option>";
        }

        $templateData['status_options'] = '';
        foreach ($templateData['statuses'] as $status) {
            $selected = ($status['id'] == 'active') ? 'selected' : '';
            $templateData['status_options'] .= "<option value='" . attr($status['id']) . "' $selected>"
                . text($status['title']) . "</option>";
        }

        return [
            'pid' => $pid,
            'team_names' => $teamNames,
            'team_name' => !empty($teamNames) ? $teamNames[0] : '',
            'user_options' => $templateData['user_options'],
            'facility_options' => $templateData['facility_options'],
            'role_options' => $templateData['role_options'],
            'status_options' => $templateData['status_options'],
            'existing_care_team' => $existingCareTeam,
            'csrf_token' => CsrfUtils::collectCsrfToken(),
            'translations' => self::getTranslations()
        ];
    }

    private static function getTranslations()
    {
        return [
            'manage_care_team' => xl("Manage Care Team"),
            'care_team_name' => xl("Care Team Name"),
            'member' => xl("Member"),
            'role' => xl("Role"),
            'facility' => xl("Facility"),
            'since' => xl("Since"),
            'status' => xl("Status"),
            'note' => xl("Note"),
            'remove' => xl("Remove"),
            'add_team_member' => xl("Add Team Member"),
            'save_care_team' => xl("Save Care Team"),
            'save_care_team_confirm' => xl('Save care team?'),
            'cancel' => xl('Cancel'),
            'physician_type' => xl('Provider Type'),
            'npi' => xl('NPI'),
            'active_members' => xl('Active Members'),
            'inactive_members' => xl('Inactive Members')
        ];
    }
}
