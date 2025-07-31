<?php

namespace OpenEMR\Patient\Cards;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent;

class CareTeamViewCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/card/manage_care_team.html.twig';
    private const CARD_ID_EXPAND = 'careteam_ps_expand';
    private const CARD_ID = 'care_team';
    private $pid;

    public function __construct($pid, array $opts = [])
    {
        $this->pid = $pid;
        $opts = $this->setupOpts($opts);
        parent::__construct($opts);

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
            'auth' => $authCheck,
            'collapse' => true,
            'templateFile' => self::TEMPLATE_FILE,
            'templateVariables' => [
                'title' => xl('Care Team'),
                'id' => self::CARD_ID_EXPAND,
                'initiallyCollapsed' => $initiallyCollapsed,
            ]
        ];

        return array_merge($opts, $newOpts);
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
        // Delete existing care team members
        sqlStatement("DELETE FROM care_teams WHERE pid = ?", [$this->pid]);

        // Insert new care team members
        foreach ($team as $entry) {
            $userId = intval($entry['user_id'] ?? 0);
            $role = trim($entry['role'] ?? '');
            $facilityId = intval($entry['facility_id'] ?? 0);
            $providerSince = trim($entry['provider_since'] ?? '');
            $status = trim($entry['status'] ?? 'active');
            $note = trim($entry['note'] ?? '');

            if ($userId) {
                sqlInsert(
                    "INSERT INTO care_teams
                    (pid, user_id, role, facility_id, provider_since, status, note, team_name)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$this->pid, $userId, $role, $facilityId, $providerSince, $status, $note, $teamName]
                );
            }
        }
    }

    private function getCareTeamData()
    {
        $careTeamResult = sqlStatement(
            "SELECT ct.*, u.fname, u.lname, f.name as facility_name,
                    lo1.title as role_title, lo2.title as status_title
             FROM care_teams ct
             LEFT JOIN users u ON ct.user_id = u.id
             LEFT JOIN facility f ON ct.facility_id = f.id
             LEFT JOIN list_options lo1 ON lo1.option_id = ct.role AND lo1.list_id = 'care_team_roles'
             LEFT JOIN list_options lo2 ON lo2.option_id = ct.status AND lo2.list_id = 'Care_Team_Status'
             WHERE ct.pid = ?
             ORDER BY ct.id ASC",
            [$this->pid]
        );

        $careTeamMembers = [];
        $teamName = '';

        while ($member = sqlFetchArray($careTeamResult)) {
            $careTeamMembers[] = [
                'id' => $member['id'],
                'user_id' => $member['user_id'],
                'user_name' => trim(($member['fname'] ?? '') . ' ' . ($member['lname'] ?? '')),
                'role' => $member['role'],
                'role_title' => $member['role_title'] ?? $member['role'],
                'facility_id' => $member['facility_id'],
                'facility_name' => $member['facility_name'] ?? '',
                'provider_since' => $member['provider_since'],
                'provider_since_formatted' => !empty($member['provider_since']) ? oeFormatShortDate($member['provider_since']) : '',
                'status' => $member['status'],
                'status_title' => $member['status_title'] ?? $member['status'],
                'note' => $member['note'] ?? '',
                'team_name' => $member['team_name'] ?? ''
            ];

            if (empty($teamName) && !empty($member['team_name'])) {
                $teamName = $member['team_name'];
            }
        }

        return [
            'team_name' => $teamName,
            'members' => $careTeamMembers,
            'member_count' => count($careTeamMembers)
        ];
    }

    private function hasActiveCareTeam()
    {
        $result = sqlQuery(
            "SELECT COUNT(*) as count FROM care_teams WHERE pid = ? AND status != 'inactive'",
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
     */
    public static function getFormManagementData($pid)
    {
        // Get users
        $usersResult = sqlStatement("SELECT id, username, fname, lname FROM users WHERE active = 1 AND username IS NOT NULL AND fname IS NOT NULL ORDER BY lname, fname");
        $templateData['users'] = [];
        while ($user = sqlFetchArray($usersResult)) {
            $templateData['users'][] = [
                'id' => $user['id'],
                'name' => $user['lname'] . ", " . $user['fname'],
                'username' => $user['username']
            ];
        }

        // Get facilities
        $facilitiesResult = sqlStatement("SELECT id, name FROM facility ORDER BY name");
        $templateData['facilities'] = [];
        while ($facility = sqlFetchArray($facilitiesResult)) {
            $templateData['facilities'][] = [
                'id' => $facility['id'],
                'name' => $facility['name']
            ];
        }
        // Get roles
        $rolesResult = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'care_team_roles' AND activity = 1 ORDER BY is_default, seq, title");
        $templateData['roles'] = [];
        while ($role = sqlFetchArray($rolesResult)) {
            $templateData['roles'][] = [
                'id' => $role['option_id'],
                'title' => $role['title']
            ];
        }
        // Get statuses
        $statusesResult = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = 'Care_Team_Status' AND activity = 1 ORDER BY is_default DESC, seq, title");
        $templateData['statuses'] = [];
        while ($status = sqlFetchArray($statusesResult)) {
            $templateData['statuses'][] = [
                'id' => $status['option_id'],
                'title' => $status['title']
            ];
        }
        // Get existing care team
        $careTeamResult = sqlStatement(
            "SELECT ct.*, u.fname, u.lname FROM care_teams ct
             LEFT JOIN users u ON ct.user_id = u.id
             WHERE ct.pid = ?
             ORDER BY ct.id ASC",
            [$pid]
        );

        $existingCareTeam = [];
        $teamName = '';
        while ($member = sqlFetchArray($careTeamResult)) {
            $existingCareTeam[] = [
                'user_id' => $member['user_id'],
                'role' => $member['role'],
                'facility_id' => $member['facility_id'],
                'provider_since' => $member['provider_since'],
                'status' => $member['status'],
                'note' => $member['note'],
                'user_name' => trim(($member['lname'] ?? '') . ", " . ($member['fname'] ?? ''))
            ];

            if (empty($teamName) && !empty($member['team_name'])) {
                $teamName = $member['team_name'];
            }
        }
        // Enhanced template data with pre-generated option strings for JavaScript
        $templateData['user_options'] = '';
        foreach ($templateData['users'] as $users) {
            $templateData['user_options'] .= "<option value='" . attr($users['id']) . "'>" . text($users['name']) . "</option>";
        }

        $templateData['facility_options'] = '';
        foreach ($templateData['facilities'] as $facilities) {
            $templateData['facility_options'] .= "<option value='" . attr($facilities['id']) . "'>" . text($facilities['name']) . "</option>";
        }

        $templateData['role_options'] = '';
        foreach ($templateData['roles'] as $roles) {
            $templateData['role_options'] .= "<option value='" . attr($roles['id']) . "'>" . text($roles['title']) . "</option>";
        }

        $templateData['status_options'] = '';
        foreach ($templateData['statuses'] as $statuses) {
            $templateData['status_options'] .= "<option value='" . attr($statuses['id']) . "'>" . text($statuses['title']) . "</option>";
        }

        return [
            'pid' => $pid,
            'team_name' => $teamName,
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
            'save_care_team_confirm' => xl('Save care team?')
        ];
    }
}
