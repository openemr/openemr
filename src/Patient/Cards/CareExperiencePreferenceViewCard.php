<?php

/**
 * Care Experience Preference Dashboard Card
 *
 * @package   OpenEMR
 * @link      https://www.openemr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Patient\Cards;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\CareExperiencePreferenceService;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent;

class CareExperiencePreferenceViewCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/card/preference_card_inline.html.twig';
    private const CARD_ID_EXPAND = 'carepref_ps_expand';
    private const CARD_ID = 'card_care_experience';

    /** @var CareExperiencePreferenceService */
    private $service;

    /** @var string|null */
    private $flashMessage = null;

    /**
     * @param int $pid
     */
    public function __construct(private $pid, array $opts = [])
    {
        $opts = $this->setupOpts($opts);
        parent::__construct($opts);
        $this->service = new CareExperiencePreferenceService();
    }

    private function setupOpts(array $opts): array
    {
        $initiallyCollapsed = $this->getUserCardSetting(self::CARD_ID_EXPAND) == 0;
        $authCheck = AclMain::aclCheckCore('patients', 'demo', '', 'write');

        $newOpts = [
            'auth' => ['patients', 'demo'],
            'initiallyCollapsed' => $initiallyCollapsed,
            'add' => true,
            'edit' => false,
            'collapse' => true,
            'templateFile' => self::TEMPLATE_FILE,
            'identifier' => self::CARD_ID,
            'title' => xl('Care Experience Preferences'),
            'templateVariables' => [
                'title' => xl('Care Experience Preferences'),
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

    private function getUserCardSetting($settingName)
    {
        return \getUserSetting($settingName);
    }

    public function getTemplateFile(): string
    {
        // Unified card (view + edit)
        return 'patient/card/preference_card_inline.html.twig';
    }

    public function getTemplateVariables(): array
    {

        $templateVars = parent::getTemplateVariables();
        $dispatchResult = $this->getEventDispatcher()->dispatch(new RenderEvent(self::CARD_ID), RenderEvent::EVENT_HANDLE);
        $this->handlePost();

        //$preferences = $this->service->getPreferencesByPatient($this->pid) ?? [];
        $loincCodes  = $this->service->getAvailableLoincCodes();
        $result = $this->service->getPreferencesByPatient($this->pid);

        $preferences = [];
        if (!empty($result)) {
            foreach ($result as $row) {
                $preferences[] = [
                    // IDs
                    'id' => $row['id'],
                    'patient_id' => $row['patient_id'],

                    'effective_datetime' => $row['effective_datetime'] ?? $row['created_at'],
                    'recorded_date' => $row['effective_datetime'] ?? $row['created_at'],
                    'updated_at' => $row['updated_at'],

                    'observation_code' => $row['observation_code'],
                    'observation_code_text' => $row['observation_code_text'],
                    'observation_code_system' => $row['observation_code_system'] ?? 'http://loinc.org',

                    'code_display' => $row['observation_code_text'],

                    'value_type' => $row['value_type'] ?? 'coded',
                    'value_code' => $row['value_code'] ?? null,
                    'value_display' => $row['value_display'] ?? null,
                    'value_code_system' => $row['value_code_system'] ?? null,
                    'value_boolean' => $row['value_boolean'] ?? null,
                    'value_text' => $row['value_text'] ?? null,

                    // Metadata
                    'status' => $row['status'] ?? 'final',
                    'note' => $row['note'] ?? '',

                    // User info
                    'user_id' => $row['created_by'] ?? $row['user_id'] ?? null,
                    'user_display' => $this->getUserDisplay($row['created_by'] ?? $row['user_id'] ?? null),
                ];
            }
        }
        $pref = [
            'title'            => xl('Care Experience Preferences'),
            'type'             => 'care_experience',
            'pid'              => $this->pid,
            'auth'             => true,  // TODO ACL
            'can_write'        => true,  // TODO ACL
            'webroot'          => $GLOBALS['webroot'] ?? '',
            'csrf_token'       => CsrfUtils::collectCsrfToken(),
            'preferences'      => $preferences,
            'loinc_codes'      => $loincCodes,
            'current_datetime' => date('Y-m-d\TH:i'),
            'message'          => $this->flashMessage,
        ];
        $templateVars = array_merge($templateVars, $pref);

        return $templateVars;
    }

    /**
     * Get display name for user
     *
     * @param int|null $userId
     * @return string
     */
    private function getUserDisplay($userId)
    {
        if (empty($userId)) {
            return '';
        }

        $sql = "SELECT CONCAT(fname, ' ', lname) as name FROM users WHERE id = ?";
        $result = QueryUtils::querySingleRow($sql, [$userId]);
        return $result['name'] ?? '';
    }

    public function canAdd(): bool
    {
        return true; }
    public function canEdit(): bool
    {
        return true; }
    //public function canCollapse(): bool { return true; }
    public function getTitle(): string
    {
        return xl('Care Experience Preferences'); }
    public function getIdentifier(): string
    {
        return 'card_care_experience'; }
    public function isInitiallyCollapsed(): bool
    {
        return (getUserSetting('card_care_experience') == 0); }
    public function getBackgroundColorClass(): string
    {
        return ''; }
    public function getTextColorClass(): string
    {
        return ''; }

    private function handlePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }
        if (($_POST['pref_type'] ?? '') !== 'care_experience') {
            return;
        }
        if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            CsrfUtils::csrfNotVerified();
        }

        $action = $_POST['action'] ?? '';
        if ($action === 'save') {
            $id   = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
            $data = $this->collectPost($_POST);  // note: returns patient_id
            if ($id) {
                $this->service->update($id, $data);
                $this->flashMessage = xl('Preference updated');
            } else {
                $this->service->insert($data);   // ← was create()
                $this->flashMessage = xl('Preference saved');
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $this->service->delete($id);     // method exists
                $this->flashMessage = xl('Preference deleted');
            }
        }
    }

    private function collectPost(array $post): array
    {
        $uid = $_SESSION['authUserID'] ?? null;

        return [
            'patient_id'            => $this->pid,  // ← was 'pid'
            'observation_code'      => trim($post['observation_code'] ?? ''),
            'observation_code_text' => trim($post['observation_code_text'] ?? ''),
            'value_type'            => trim($post['value_type'] ?? 'coded'),
            'value_code'            => trim($post['value_code'] ?? ''),
            'value_code_system'     => trim($post['value_code_system'] ?? ''),
            'value_display'         => trim($post['value_display'] ?? ''),
            'value_text'            => trim($post['value_text'] ?? ''),
            'value_boolean'         => $post['value_boolean'] ?? null,
            'status'                => trim($post['status'] ?? 'final'),
            'effective_datetime'    => trim($post['effective_datetime'] ?? date('Y-m-d H:i:s')),
            'note'                  => trim($post['note'] ?? ''),
            'created_by'            => $uid,
            'updated_by'            => $uid,
        ];
    }
}
