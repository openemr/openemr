<?php

/**
 * @package   OpenEMR Care Team
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Patient\Cards;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent;
use OpenEMR\Services\CareTeamService;
use OpenEMR\Services\ContactService; // AI-generated import
use OpenEMR\Services\ContactRelationService; // AI-generated import
use OpenEMR\Services\ListService;

class CareTeamViewCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/card/manage_care_team.html.twig';
    private const CARD_ID_EXPAND = 'careteam_ps_expand';
    private const CARD_ID = 'care_team';

    /**
     * @var CareTeamService
     */
    private CareTeamService $careTeamService;

    private ListService $listService;

    public function __construct(private $pid, array $opts = [])
    {
        $opts = $this->setupOpts($opts);
        parent::__construct($opts);

        // Handle form submission if this is a POST request
        $this->handleFormSubmission();
    }

    public function getListService(): ListService
    {
        if (!isset($this->listService)) {
            $this->listService = new ListService();
        }
        return $this->listService;
    }
    public function setListService(ListService $service): void
    {
        $this->listService = $service;
    }

    public function getCareTeamService(): CareTeamService
    {
        if (!isset($this->careTeamService)) {
            $this->careTeamService = new CareTeamService();
        }
        return $this->careTeamService;
    }

    public function setCareTeamService(CareTeamService $service): void
    {
        $this->careTeamService = $service;
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
                'btnClass' => 'btn-edit-care-team',
                'btnLink' => "javascript:void(0);",
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
        $templateVars['hasActiveTeam'] = $this->getCareTeamService()->hasActiveCareTeam($this->pid);
        $templateVars = array_merge($templateVars, $this->getFormManagementData($this->pid));
        return $templateVars;
    }

    private function handleFormSubmission()
    {
        if (($_POST['save_care_team'] ?? '') === 'true') {
            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
                CsrfUtils::csrfNotVerified();
            }

            $teamId = filter_var($_POST['team_id'], FILTER_VALIDATE_INT);
            $teamId = $teamId === false ? null : $teamId;
            $teamName = trim($_POST['team_name'] ?? '');
            $team = $_POST['team'] ?? [];
            $teamStatus = trim($_POST['team_status'] ?? 'active'); // AI-generated addition

            if (!$this->pid) {
                die(xlt("Invalid request."));
            }

            $this->getCareTeamService()->saveCareTeam($this->pid, $teamId, $teamName, $team, $teamStatus);
        }
    }

    private function getUserCardSetting($settingName)
    {
        return \getUserSetting($settingName);
    }

    /**
     * Static method to get form management data for the care team edit page
     * Enhanced for USCDI v5 compliance
     */
    public function getFormManagementData($pid)
    {

        // Get existing care teams (support multiple teams)
        $careTeamResult = $this->getCareTeamService()->getCareTeamData($pid);
        $teamName = $careTeamResult['team_name'] ?? 'default';
        $existingCareTeam = [];

        foreach ($careTeamResult['members'] as $member) {
            $existingCareTeam[] = [
                'team_name' => $teamName,
                'member_type' => $member['member_type'] ?? 'user',
                'user_id' => $member['user_id'],
                'contact_id' => $member['contact_id'],
                'role' => $member['role'],
                'facility_id' => $member['facility_id'],
                'provider_since' => $member['provider_since'],
                'status' => $member['status'],
                'note' => $member['note'],
                'user_name' => $member['user_name'],
                'contact_name' => $member['contact_name'],
                'contact_relationship' => $member['contact_relationship'],
                'physician_type' => $member['physician_type'],
                'physician_type_code' => $member['physician_type_code']
            ];
        }

        // AI-generated related person integration - Start
        // Get related persons for this patient
        $contactService = new ContactService();
        $relationService = new ContactRelationService();

        $patientContact = $contactService->getOrCreateForEntity('patient_data', $pid);
        $relatedPersons = [];
        $hasRelatedPersons = false;

        if ($patientContact && $patientContact->get_id()) {
            $relatedPersons = $relationService->getRelationshipsWithDetails($patientContact->get_id());
            $hasRelatedPersons = !empty($relatedPersons);
        }
        // AI-generated related person integration - End

        // Get users with physician type information for role mapping
        $usersResult = QueryUtils::sqlStatementThrowException(
            "SELECT u.id, u.username, u.fname, u.lname, u.physician_type, lo.codes AS physician_type_code FROM users u LEFT JOIN list_options lo ON lo.list_id='physician_type' AND lo.option_id=u.physician_type WHERE active = 1 AND username IS NOT NULL AND fname IS NOT NULL
             ORDER BY lname, fname",
            []
        );

        $templateData['users'] = [];
        while ($user = QueryUtils::fetchArrayFromResultSet($usersResult)) {
            $templateData['users'][] = [
                'id' => $user['id'],
                'name' => $user['lname'] . ", " . $user['fname'],
                'username' => $user['username'],
                'physician_type' => $user['physician_type'],
                'physician_type_code' => $user['physician_type_code']
            ];
        }

        // Get facilities with NPI for organization identification
        $facilitiesResult = QueryUtils::sqlStatementThrowException(
            "SELECT id, name, facility_npi, facility_taxonomy
             FROM facility
             WHERE service_location = 1 OR billing_location = 1
             ORDER BY name",
            []
        );

        $templateData['facilities'] = [];
        while ($facility = QueryUtils::fetchArrayFromResultSet($facilitiesResult)) {
            $templateData['facilities'][] = [
                'id' => $facility['id'],
                'name' => $facility['name'],
                'npi' => $facility['facility_npi'],
                'taxonomy' => $facility['facility_taxonomy']
            ];
        }

        // Get roles - ensure we have USCDI v5 compatible roles
        $roles = $this->getListService()->getOptionsByListName('care_team_roles');

        $templateData['roles'] = [];
        foreach ($roles as $role) {
            $templateData['roles'][] = [
                'id' => $role['option_id'],
                'title' => $role['title'],
                'codes' => $role['codes'] // SNOMED CT codes for USCDI v5
            ];
        }

        // Get statuses - ensure we have FHIR-compatible statuses
        $statusesResult = $this->getListService()->getOptionsByListName('Care_Team_Status');
        $templateData['statuses'] = [];
        foreach ($statusesResult as $status) {
            $templateData['statuses'][] = [
                'id' => $status['option_id'],
                'title' => $status['title']
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

        // AI-generated related person options - Start
        $templateData['related_person_options'] = "<option value=''></option>";
        foreach ($relatedPersons as $person) {
            $personName = trim(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));
            $relationship = $person['relationship'] ?? '';
            $displayName = $personName . ($relationship ? " ({$relationship})" : "");
            $contactId = $person['target_contact_id'] ?? '';

            if ($contactId) {
                $templateData['related_person_options'] .= "<option value='" . attr($contactId) . "' "
                    . "data-person-name='" . attr($personName) . "' "
                    . "data-relationship='" . attr($relationship) . "'>"
                    . text($displayName) . "</option>";
            }
        }
        // AI-generated related person options - End

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

        // AI-generated addition - Start
        // Get team status information
        $teamStatus = $careTeamResult['team_status'] ?? 'active';
        $teamStatusDisplay = '';
        $teamStatusBadgeClass = 'badge-secondary';

        // Get status display information
        foreach ($templateData['statuses'] as $status) {
            if ($status['id'] === $teamStatus) {
                $teamStatusDisplay = $status['title'];
                // Set badge class based on status
                $teamStatusBadgeClass = match ($teamStatus) {
                    'active' => 'badge-success',
                    'inactive' => 'badge-warning',
                    'proposed' => 'badge-info',
                    'entered-in-error' => 'badge-danger',
                    default => 'badge-secondary',
                };
                break;
            }
        }

        // Build team status options
        $templateData['team_status_options'] = '';
        foreach ($templateData['statuses'] as $status) {
            $selected = ($status['id'] === $teamStatus) ? 'selected' : '';
            $templateData['team_status_options'] .= "<option value='" . attr($status['id']) . "' $selected>"
                . text($status['title']) . "</option>";
        }
        // AI-generated addition - End

        return [
            'team_id' => $careTeamResult['team_id'] ?? null,
            'pid' => $pid,
            'team_name' => $teamName,
            'team_status' => $teamStatus, // AI-generated addition
            'team_status_display' => $teamStatusDisplay, // AI-generated addition
            'team_status_badge_class' => $teamStatusBadgeClass, // AI-generated addition
            'team_status_options' => $templateData['team_status_options'], // AI-generated addition
            'user_options' => $templateData['user_options'],
            'related_person_options' => $templateData['related_person_options'], // AI-generated addition
            'has_related_persons' => $hasRelatedPersons, // AI-generated addition
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
            'care_team_status' => xl('Care Team Status'), // AI-generated translation
            'care_team_name' => xl("Care Team Name"),
            'member_type' => xl("Type"), // AI-generated translation
            'member' => xl("Member"),
            'role' => xl("Role"),
            'facility' => xl("Facility"),
            'since' => xl("Since"),
            'status' => xl("Status"),
            'note' => xl("Note"),
            'remove' => xl("Remove"),
            'add_team_member' => xl("Add Team Member"),
            'add_related_person' => xl("Add Related Person"), // AI-generated translation
            'save_care_team' => xl("Save Care Team"),
            'save_care_team_confirm' => xl('Save care team?'),
            'cancel' => xl('Cancel'),
            'physician_type' => xl('Provider Type'),
            'npi' => xl('NPI'),
            'active_members' => xl('Active Members'),
            'inactive_members' => xl('Inactive Members'),
            'no_related_persons_alert' => xl('No related persons found for this patient. Please add related persons in the Demographics Relations screen first.'), // AI-generated translation
            'provider_member' => xl('Provider'), // AI-generated translation
            'related_person_member' => xl('Related Person') // AI-generated translation
        ];
    }
}
