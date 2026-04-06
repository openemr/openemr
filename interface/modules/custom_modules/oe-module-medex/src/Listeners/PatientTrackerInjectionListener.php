<?php

/**
 * PatientTrackerInjectionListener - Event listener for injecting MedEx content into patient tracker
 *
 * This listener responds to PatientTrackerRenderEvents and injects campaign status
 * icons, navigation, and JavaScript functionality.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Listeners;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\MedEx\Events\PatientTrackerRenderEvent;

class PatientTrackerInjectionListener
{
    private OEGlobalsBag $globalsBag;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (class_exists('OpenEMR\Core\OEGlobalsBag')) {
            $this->globalsBag = OEGlobalsBag::getInstance();
        } else {
            $this->globalsBag = null;
        }
    }

    /**
     * Main event handler - routes to appropriate method based on injection point
     */
    public function onPageRender(PatientTrackerRenderEvent $event): void
    {
        $api = new \OpenEMR\Modules\MedEx\MedExAPI();

        if (!$api->canInjectCoreBoards() || !$api->isActive() || empty($api->getEnabledServices())) {
            return;
        }

        switch ($event->getInjectionPoint()) {
            case PatientTrackerRenderEvent::INJECT_NAVIGATION:
                $this->handleNavigation($event);
                break;
            case PatientTrackerRenderEvent::INJECT_SCRIPTS:
                $this->handleScripts($event);
                break;
        }
    }

    /**
     * Handle navigation injection
     */
    private function handleNavigation(PatientTrackerRenderEvent $event): void
    {
        // Navigation will be injected via JavaScript
        // This keeps the PHP side clean
    }

    /**
     * Public method for direct injection (called from bootstrap shutdown function)
     */
    public function injectScripts(): void
    {
        // Don't inject on settings-save AJAX requests.
        // patient_tracker.php calls exit() after saving these preferences,
        // but register_shutdown_function still fires — we must not inject
        // script/style HTML into what should be an empty AJAX response.
        if (
            !empty($_POST['setting_new_window']) ||
            !empty($_POST['setting_bootstrap_submenu']) ||
            !empty($_POST['setting_selectors'])
        ) {
            return;
        }

        $api = new \OpenEMR\Modules\MedEx\MedExAPI();

        if (!$api->canInjectCoreBoards() || !$api->isActive() || empty($api->getEnabledServices())) {
            return;
        }

        echo $this->buildScriptHTML();
    }

    /**
     * Public method for TemplatePageEvent injection
     */
    public function handleScriptsFromTemplateEvent(\OpenEMR\Events\Core\TemplatePageEvent $event): void
    {
        $api = new \OpenEMR\Modules\MedEx\MedExAPI();
        if (!$api->canInjectCoreBoards()) {
            return;
        }

        $html = $this->buildScriptHTML();
        // TemplatePageEvent doesn't have output methods, so we just echo
        echo $html;
    }

    /**
     * Handle JavaScript injection
     */
    private function handleScripts(PatientTrackerRenderEvent $event): void
    {
        $html = $this->buildScriptHTML();
        echo $html;
    }

    /**
     * Build script HTML (common logic)
     */
    private function buildScriptHTML(): string
    {
        global $webroot;

        $csrfToken = \OpenEMR\Common\Csrf\CsrfUtils::collectCsrfToken();
        $isAjaxRefresh = !empty($_REQUEST['flb_table']);

        // Path to Patient Tracker injection JavaScript
        $jsPath = "{$webroot}/interface/modules/custom_modules/oe-module-medex/public/js/patient_tracker_injection.js";

        $html = '';

        // Only include CSS, config, and external JS on full page load (not AJAX table refresh)
        if (!$isAjaxRefresh) {
            // Load CSS inline (patient_tracker_injection.css only — campaign_controls.css is for recall board)
            $cssFilePath = __DIR__ . '/../../public/css/patient_tracker_injection.css';
            $cssContent = '';
            if (file_exists($cssFilePath)) {
                $cssContent = file_get_contents($cssFilePath);
            }

            // Get MedEx server URL from config via MedExAPI
            $api = new \OpenEMR\Modules\MedEx\MedExAPI();
            $medexServer = \OpenEMR\Modules\MedEx\MedExConfig::DEFAULT_BASE_URL;
            if ($api->getBaseUrl()) {
                $medexServer = $api->getBaseUrl();
            } elseif (isset($this->globalsBag)) {
                $medexServer = $this->globalsBag->get('medex_bank_url') ?? $this->globalsBag->get('medex_server_url') ?? \OpenEMR\Modules\MedEx\MedExConfig::DEFAULT_BASE_URL;
            }

            // Check if MedEx is enabled and connected
            $isEnabled = $api->isEnabled();

            // Read services directly from database cache to avoid session dependency
            $services = [];
            try {
                $records = QueryUtils::fetchRecords(
                    "SELECT JSON_EXTRACT(status, '$.enabled_services') as services FROM medex_prefs LIMIT 1"
                );
                $dbServices = $records[0]['services'] ?? '[]';
                $services = json_decode($dbServices, true) ?: [];
            } catch (\Exception $e) {
                $services = [];
            }

            $isOnline = $isEnabled && !empty($services);
            $isAdmin = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super');

            // Check for updates (admins only)
            $updateInfo = null;
            if ($isAdmin) {
                require_once(__DIR__ . '/../UpdateManager.php');
                $updateManager = new \OpenEMR\Modules\MedEx\UpdateManager();
                $updateInfo = $updateManager->checkForUpdates();
            }

            if ($cssContent) {
                $html .= '<style>' . "\n" . $cssContent . "\n" . '</style>' . "\n";
            }
            $html .= '<script>' . "\n";
            $html .= '    window.medex_csrf_token = ' . $this->jsEscape($csrfToken) . ';' . "\n";
            $html .= '    window.medex_server = ' . $this->jsEscape($medexServer) . ';' . "\n";
            $html .= '    window.medex_is_enabled = ' . ($isEnabled ? 'true' : 'false') . ';' . "\n";
            $html .= '    window.medex_online = ' . ($isOnline ? 'true' : 'false') . ';' . "\n";
            $html .= '    window.medex_is_admin = ' . ($isAdmin ? 'true' : 'false') . ';' . "\n";
            if ($updateInfo && $updateInfo['update_available']) {
                $html .= '    window.medex_update_available = true;' . "\n";
                $html .= '    window.medex_update_version = ' . $this->jsEscape($updateInfo['latest_version']) . ';' . "\n";
                $html .= '    window.medex_update_priority = ' . $this->jsEscape($updateInfo['priority']) . ';' . "\n";
            } else {
                $html .= '    window.medex_update_available = false;' . "\n";
            }
            $html .= '</script>' . "\n";
            $html .= '<script src="' . $jsPath . '"></script>' . "\n";
        }

        // Always embed appointment data inline (works for both full load and AJAX refresh)
        $appointmentData = $this->buildAppointmentDataJSON();
        $html .= '<script>' . "\n";
        $html .= '    window.medex_appointment_data = ' . $appointmentData . ';' . "\n";
        $html .= '    if (typeof window.medexPopulateIcons === "function") { window.medexPopulateIcons(); }' . "\n";
        $html .= '</script>' . "\n";

        return $html;
    }

    /**
     * Build inline JSON with all appointment campaign + modality data for instant rendering
     * Eliminates per-appointment AJAX calls — icons appear with the page, zero delay
     */
    private function buildAppointmentDataJSON(): string
    {
        try {
            // Determine date range from request (matches patient_tracker.php logic)
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');

            if (!empty($_REQUEST['form_from_date'])) {
                $from_date = function_exists('DateToYYYYMMDD')
                    ? DateToYYYYMMDD($_REQUEST['form_from_date'])
                    : $_REQUEST['form_from_date'];
            }
            if (!empty($_REQUEST['form_to_date'])) {
                $to_date = function_exists('DateToYYYYMMDD')
                    ? DateToYYYYMMDD($_REQUEST['form_to_date'])
                    : $_REQUEST['form_to_date'];
            } else {
                $to_date = $from_date;
            }

            // 1. Load medex_icons lookup table
            $icons = [];
            try {
                $iconRows = QueryUtils::fetchRecords("SELECT msg_type, msg_status, i_html FROM medex_icons");
                foreach ($iconRows as $icon) {
                    $icons[$icon['msg_type']][$icon['msg_status']]['html'] = $icon['i_html'];
                }
            } catch (\Exception $e) {
                // medex_icons table may not exist yet
            }

            // 2. Get all appointment EIDs and PIDs for the displayed date range
            $events = QueryUtils::fetchRecords(
                "SELECT pc_eid, pc_pid FROM openemr_postcalendar_events " .
                "WHERE pc_eventDate >= ? AND pc_eventDate <= ? AND pc_apptstatus != ?",
                [$from_date, $to_date, 'x']
            );

            if (empty($events)) {
                return '{"campaigns":{},"modalities":{}}';
            }

            $eids = [];
            $pidSet = [];
            foreach ($events as $evt) {
                $eids[] = (int)$evt['pc_eid'];
                $pidSet[(int)$evt['pc_pid']] = true;
            }
            $pidList = array_keys($pidSet);

            // 3. Batch-query medex_outgoing for ALL EIDs at once
            $outgoingByEid = [];
            if (!empty($eids) && !empty($icons)) {
                try {
                    $placeholders = implode(',', array_fill(0, count($eids), '?'));
                    $outgoing = QueryUtils::fetchRecords(
                        "SELECT * FROM medex_outgoing WHERE msg_pc_eid IN ($placeholders) ORDER BY medex_uid ASC",
                        $eids
                    );
                    foreach ($outgoing as $row) {
                        $outgoingByEid[$row['msg_pc_eid']][] = $row;
                    }
                } catch (\Exception $e) {
                    // medex_outgoing table may not exist yet
                }
            }

            // 4. Build campaign icon HTML per EID (only for EIDs that have outgoing messages)
            $campaignData = [];
            foreach ($outgoingByEid as $eid => $rows) {
                $html = $this->buildCampaignIconsHTML($rows, $icons);
                if ($html !== '') {
                    $campaignData[(string)$eid] = $html;
                }
            }

            // 5. Batch-query patient_data for ALL PIDs at once
            $modalityData = [];
            if (!empty($pidList) && !empty($icons)) {
                $pidPlaceholders = implode(',', array_fill(0, count($pidList), '?'));
                $patients = QueryUtils::fetchRecords(
                    "SELECT pid, phone_cell, phone_home, email, hipaa_allowsms, hipaa_voice, hipaa_allowemail " .
                    "FROM patient_data WHERE pid IN ($pidPlaceholders)",
                    $pidList
                );

                // 6. Build modality icon HTML per PID
                foreach ($patients as $pat) {
                    $html = $this->buildModalityIconsHTML($pat, $icons);
                    if ($html !== '') {
                        $modalityData[(string)$pat['pid']] = $html;
                    }
                }
            }

            return json_encode([
                'campaigns' => (object)$campaignData,
                'modalities' => (object)$modalityData
            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        } catch (\Exception $e) {
            error_log('[MedEx] buildAppointmentDataJSON error: ' . $e->getMessage());
            return '{"campaigns":{},"modalities":{}}';
        }
    }

    /**
     * Build campaign status icon HTML from medex_outgoing rows for one appointment
     * Replicates the original patient_tracker.php campaign icon logic
     */
    private function buildCampaignIconsHTML(array $rows, array $icons): string
    {
        $icon_here = [];
        $icon2_here = '';
        $icon_extra = '';
        $icon_4_CALL = '';
        $appointment = [];
        $patientMessages = [];

        foreach ($rows as $row) {
            $msgType = $row['msg_type'] ?? '';
            $msgReply = $row['msg_reply'] ?? '';

            if ($msgReply === 'Other') {
                if (!empty($row['msg_extra_text'])) {
                    $patientMessages[] = $row['msg_extra_text'];
                }
                continue;
            } elseif ($msgReply === 'CANCELLED') {
                $appointment[$msgType]['stage'] = 'CANCELLED';
                $icon_here[$msgType] = '';
            } elseif ($msgReply === 'FAILED') {
                $appointment[$msgType]['stage'] = 'FAILED';
                $icon_here[$msgType] = $icons[$msgType]['FAILED']['html'] ?? '';
            } elseif ($msgReply === 'CONFIRMED' || ($appointment[$msgType]['stage'] ?? '') === 'CONFIRMED') {
                $appointment[$msgType]['stage'] = 'CONFIRMED';
                $icon_here[$msgType] = $icons[$msgType]['CONFIRMED']['html'] ?? '';
            } elseif ($msgReply === 'READ' || ($appointment[$msgType]['stage'] ?? '') === 'READ') {
                $appointment[$msgType]['stage'] = 'READ';
                $icon_here[$msgType] = $icons[$msgType]['READ']['html'] ?? '';
            } elseif ($msgReply === 'SENT' || ($appointment[$msgType]['stage'] ?? '') === 'SENT') {
                $appointment[$msgType]['stage'] = 'SENT';
                $icon_here[$msgType] = $icons[$msgType]['SENT']['html'] ?? '';
            } elseif ($msgReply === 'To Send' || empty($appointment[$msgType]['stage'] ?? '')) {
                if (!in_array($appointment[$msgType]['stage'] ?? '', ['CONFIRMED', 'READ', 'SENT', 'FAILED'])) {
                    $appointment[$msgType]['stage'] = 'QUEUED';
                    $icon_here[$msgType] = $icons[$msgType]['SCHEDULED']['html'] ?? '';
                }
            }

            if ($msgReply === 'CALL') {
                $icon_here[$msgType] = $icons[$msgType]['CALL']['html'] ?? '';
                try {
                    $patData = QueryUtils::fetchRecords(
                        "SELECT allow_sms, phone_cell FROM patient_data WHERE pid = ?",
                        [$row['msg_pid']]
                    );
                    if (!empty($patData) && ($patData[0]['allow_sms'] ?? '') !== 'NO' && !empty($patData[0]['phone_cell'])) {
                        $icon_4_CALL = "<span class='input-group-addon' onclick='SMS_bot(" . (int)$row['msg_pid'] . ");'><i class='fas fa-sms'></i></span>";
                    }
                } catch (\Exception $e) {
                    // ignore
                }
            } elseif ($msgReply === 'STOP') {
                $icon2_here .= $icons[$msgType]['STOP']['html'] ?? '';
            }
        }

        // Speech bubble for patient messages
        if (!empty($patientMessages)) {
            $messageText = htmlspecialchars(implode("\n", $patientMessages), ENT_QUOTES, 'UTF-8');
            $icon_extra = '<span class="medex-patient-message-icon" data-tooltip="' . $messageText . '">'
                . '<i class="fas fa-comment-dots fa-2x" style="color:#9C27B0;"></i></span>';
        }

        return implode('', $icon_here) . $icon2_here . $icon_extra . $icon_4_CALL;
    }

    /**
     * Build possibleModalities icon HTML for a patient
     * Shows what contact methods (SMS/AVM/EMAIL) are available based on patient data
     */
    private function buildModalityIconsHTML(array $pat, array $icons): string
    {
        $modIcons = [];

        // SMS
        if (empty($pat['phone_cell']) || ($pat['hipaa_allowsms'] ?? '') === 'NO') {
            $modIcons[] = $icons['SMS']['NotAllowed']['html'] ?? '';
        } else {
            $modIcons[] = $icons['SMS']['ALLOWED']['html'] ?? '';
        }

        // AVM (voice)
        if ((empty($pat['phone_home']) && empty($pat['phone_cell'])) || ($pat['hipaa_voice'] ?? '') === 'NO') {
            $modIcons[] = $icons['AVM']['NotAllowed']['html'] ?? '';
        } else {
            $modIcons[] = $icons['AVM']['ALLOWED']['html'] ?? '';
        }

        // EMAIL
        if (empty($pat['email']) || ($pat['hipaa_allowemail'] ?? '') === 'NO') {
            $modIcons[] = $icons['EMAIL']['NotAllowed']['html'] ?? '';
        } else {
            $modIcons[] = $icons['EMAIL']['ALLOWED']['html'] ?? '';
        }

        return implode('', $modIcons);
    }

    /**
     * JavaScript escape helper
     */
    private function jsEscape($value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
