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

use OpenEMR\Services\CareExperiencePreferenceService;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent;

class CareExperiencePreferenceViewCard extends CardModel
{
    private const TEMPLATE_FILE = 'patient/card/preference_card_inline.html.twig';
    private const CARD_ID_EXPAND = 'carepref_ps_expand';
    private const CARD_ID = 'card_care_experience';

    /** @var int */
    private $pid;

    /** @var CareExperiencePreferenceService */
    private $service;

    /** @var string|null */
    private $flashMessage = null;

    public function __construct($pid, array $opts = [])
    {
        $opts = $this->setupOpts($opts);
        parent::__construct($opts);
        $this->pid = $pid;
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
            'edit' => true,
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

        $preferences = $this->service->getPreferencesByPatient($this->pid) ?? [];
        $loincCodes  = $this->service->getAvailableLoincCodes();

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
            'value_boolean'         => isset($post['value_boolean']) ? (string)$post['value_boolean'] : null,
            'status'                => trim($post['status'] ?? 'final'),
            'effective_datetime'    => trim($post['effective_datetime'] ?? date('Y-m-d H:i:s')),
            'note'                  => trim($post['note'] ?? ''),
            'created_by'            => $uid,
            'updated_by'            => $uid,
        ];
    }
}
