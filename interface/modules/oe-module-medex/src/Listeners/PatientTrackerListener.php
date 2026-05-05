<?php

/**
 * PatientTrackerListener - Event listener for injecting MedEx content into patient_tracker.php
 *
 * This listener responds to PatientTrackerPageRenderEvents and injects MedEx functionality
 * including navigation, reminder icons, and communication status indicators.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Listeners;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\MedEx\Events\PatientTrackerPageRenderEvent;
use OpenEMR\Modules\MedEx\MedExAPI;

class PatientTrackerListener
{
    /**
     * @var MedExAPI|null MedEx API instance
     */
    private ?MedExAPI $medExAPI = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        // MedExAPI will be instantiated only when needed
    }

    /**
     * Main event handler - routes to appropriate method based on injection point
     */
    public function onPageRender(PatientTrackerPageRenderEvent $event): void
    {
        // We assume the bootstrap already checked if the module is officially enabled in OpenEMR.
        // Now we check if MedEx service itself is active and subscribed.

        // Verify service subscription
        $api = $this->getMedExAPI();

        // If OpenEMR version is <= 8.0, do not inject into Flow Board
        if (!$api->canInjectCoreBoards()) {
            error_log('[MedEx] OpenEMR version <= 8.0 - Skipping Flow Board injections');
            return;
        }

        if (!$api->isActive() || empty($api->getEnabledServices())) {
            return;
        }

        // Add auditing for status changes
        if ($event->getInjectionPoint() === 'audit_status_change') {
            $this->handleStatusChangeAudit($event);
            return;
        }

        // Route to appropriate handler based on injection point
        switch ($event->getInjectionPoint()) {
            case PatientTrackerPageRenderEvent::INJECT_NAVIGATION:
                $this->handleNavigation($event);
                break;

            case PatientTrackerPageRenderEvent::INJECT_STATUS_ICONS:
                $this->handleStatusIcons($event);
                break;

            case PatientTrackerPageRenderEvent::INJECT_MODALITIES:
                $this->handleModalities($event);
                break;

            case PatientTrackerPageRenderEvent::INJECT_SCRIPTS:
                $this->handleScripts($event);
                break;

            case PatientTrackerPageRenderEvent::INJECT_ONLINE_STATUS:
                $this->handleOnlineStatus($event);
                break;
        }
    }

    /**
     * Handle navigation injection
     */
    private function handleNavigation(PatientTrackerPageRenderEvent $event): void
    {
        $loggedIn = $event->getLoggedInUser();

        $html = $this->renderNavigation($loggedIn);
        $event->setContent($html);
    }

    /**
     * Handle status icons injection (reminder status for appointments)
     */
    private function handleStatusIcons(PatientTrackerPageRenderEvent $event): void
    {
        $appointment = $event->getAppointment();
        $loggedIn = $event->getLoggedInUser();
        $icons = $event->getIcons();

        // If patient has already arrived (has room assignment), don't show MedEx icons
        if (!empty($appointment['room'])) {
            return;
        }

        // Query medex_outgoing table for this appointment's communication history
        $reminderIcons = $this->generateReminderIcons($appointment, $icons);

        $event->setContent($reminderIcons);
    }

    /**
     * Handle modalities injection (show possible communication methods)
     */
    private function handleModalities(PatientTrackerPageRenderEvent $event): void
    {
        $appointment = $event->getAppointment();
        $loggedIn = $event->getLoggedInUser();

        // Show possible communication modalities for patients who haven't arrived
        if (empty($appointment['room']) && $loggedIn) {
            $modalities = $this->getMedExAPI()->getPossibleModalities($appointment);
            $html = "<span style='font-size:0.7rem;'>" .
                    $modalities['SMS'] .
                    $modalities['AVM'] .
                    $modalities['EMAIL'] .
                    "</span>";
            $event->setContent($html);
        }
    }

    /**
     * Handle JavaScript injection
     */
    private function handleScripts(PatientTrackerPageRenderEvent $event): void
    {
        global $webroot;

        $html = <<<JAVASCRIPT
<script>
    // MedEx-specific JavaScript for patient tracker
    function SMS_bot(pid) {
        top.restoreSession();
        const params = new URLSearchParams({
            nomenu: '1',
            pid: pid
        });
        const features = 'width=450,height=800,resizable=1,scrollbars=1';
        const win = window.open('{$webroot}/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php?' + params.toString(), '_blank', features);
        if (win) {
            win.focus();
        }
        return false;
    }
</script>
JAVASCRIPT;

        $event->appendContent($html);
    }

    /**
     * Handle online status injection
     */
    private function handleOnlineStatus(PatientTrackerPageRenderEvent $event): void
    {
        error_log('[MedEx] PatientTrackerListener::handleOnlineStatus CALLED');
        
        // Use same logic as PatientTrackerInjectionListener - bypass session issues
        $api = $this->getMedExAPI();
        $isEnabled = $api->isEnabled();
        error_log('[MedEx] isEnabled: ' . ($isEnabled ? 'true' : 'false'));
        
        // Read services directly from database cache to avoid session dependency
        $services = [];
        try {
            $records = \OpenEMR\Common\Database\QueryUtils::fetchRecords(
                "SELECT JSON_EXTRACT(status, '$.enabled_services') as services FROM medex_prefs LIMIT 1"
            );
            $dbServices = $records[0]['services'] ?? '[]';
            $services = json_decode($dbServices, true) ?: [];
            error_log('[MedEx] Services from DB: ' . json_encode($services));
        } catch (\Exception $e) {
            error_log('[MedEx] PatientTrackerListener - Failed to read services from DB: ' . $e->getMessage());
            $services = [];
        }
        
        $isOnline = $isEnabled && !empty($services);
        error_log('[MedEx] Final isOnline: ' . ($isOnline ? 'true' : 'false'));
        
        $status = $isOnline ? xlt("On-line") : xlt("Currently off-line");
        error_log('[MedEx] Status text: ' . $status);

        $html = <<<HTML
<b>MedEx:</b>
<a href="https://medexbank.com/cart/upload/index.php?route=information/campaigns&amp;g=rem" target="_medex">
    {$status}
</a>
HTML;

        error_log('[MedEx] Setting HTML content: ' . $html);
        $event->setContent($html);
    }

    /**
     * Handle status change auditing
     */
    private function handleStatusChangeAudit(PatientTrackerPageRenderEvent $event): void
    {
        $data = $event->getAuditData();
        $pid = $data['pid'] ?? 0;
        $status = $data['status'] ?? '';
        $room = $data['room'] ?? '';
        $encounter = $data['encounter'] ?? 0;

        $comment = sprintf('Flow board status change: pid=%d, status=%s, room=%s, encounter=%d', $pid, $status, $room, $encounter);

        if (class_exists('OpenEMR\\Common\\Logging\\EventAuditLogger')) {
            \OpenEMR\Common\Logging\EventAuditLogger::getInstance()->newEvent('flow-board', $_SESSION['authUser'], $_SESSION['authProvider'], 1, $comment, (int)$pid);
        } elseif (class_exists('EventAuditLogger')) {
            \EventAuditLogger::getInstance()->newEvent('flow-board', $_SESSION['authUser'], $_SESSION['authProvider'], 1, $comment, (int)$pid);
        }
    }

    /**
     * Render MedEx navigation bar
     */
    private function renderNavigation($loggedIn): string
    {
        ob_start();
        require __DIR__ . '/../templates/navigation.php';
        return ob_get_clean();
    }

    /**
     * Generate reminder icons for an appointment based on MedEx communication history
     *
     * Icons represent the state of communication attempts:
     * - CONFIRMED = green
     * - READ = blue
     * - FAILED = pink
     * - SENT = yellow
     * - SCHEDULED = white
     *
     * @param array $appointment Appointment data
     * @param array $icons Icon HTML from medex_icons table
     * @return string HTML for reminder icons
     */
    private function generateReminderIcons(array $appointment, array $icons): string
    {
        $eid = $appointment['eid'] ?? $appointment['pc_eid'] ?? null;
        if (!$eid) {
            return '';
        }

        // Query MedEx outgoing messages for this appointment
        $query = "SELECT * FROM medex_outgoing WHERE msg_pc_eid =? ORDER BY medex_uid asc";
        $rows = QueryUtils::fetchRecords($query, [$eid]);

        $iconHere = [];
        $icon2Here = '';
        $iconExtra = '';
        $icon4Call = '';
        $appointment_status = [];

        foreach ($rows as $row) {
            // Track progression
            $progText = attr(oeFormatShortDate($row['msg_date'])) . " :: " .
                        attr($row['msg_type']) . " : " .
                        attr($row['msg_reply']) . " |";

            // Handle different message replies
            if ($row['msg_reply'] == 'Other') {
                $iconExtra .= str_replace(
                    "EXTRA",
                    attr(oeFormatShortDate($row['msg_date'])) . "\n" .
                    xla('Patient Message') . ":\n" .
                    attr($row['msg_extra_text']) . "\n",
                    $icons[$row['msg_type']]['EXTRA']['html'] ?? ''
                );
                continue;
            } elseif ($row['msg_reply'] == 'CANCELLED') {
                $appointment_status[$row['msg_type']]['stage'] = "CANCELLED";
                $iconHere[$row['msg_type']] = '';
            } elseif ($row['msg_reply'] == "FAILED") {
                $appointment_status[$row['msg_type']]['stage'] = "FAILED";
                $iconHere[$row['msg_type']] = $icons[$row['msg_type']]['FAILED']['html'] ?? '';
            } elseif (($row['msg_reply'] == "CONFIRMED") ||
                      ($appointment_status[$row['msg_type']]['stage'] ?? '') == "CONFIRMED") {
                $appointment_status[$row['msg_type']]['stage'] = "CONFIRMED";
                $iconHere[$row['msg_type']] = $icons[$row['msg_type']]['CONFIRMED']['html'] ?? '';
            } elseif (($row['msg_reply'] == "READ") ||
                      ($appointment_status[$row['msg_type']]['stage'] ?? '') == "READ") {
                $appointment_status[$row['msg_type']]['stage'] = "READ";
                $iconHere[$row['msg_type']] = $icons[$row['msg_type']]['READ']['html'] ?? '';
            } elseif (($row['msg_reply'] == "SENT") ||
                      ($appointment_status[$row['msg_type']]['stage'] ?? '') == "SENT") {
                $appointment_status[$row['msg_type']]['stage'] = "SENT";
                $iconHere[$row['msg_type']] = $icons[$row['msg_type']]['SENT']['html'] ?? '';
            } elseif (($row['msg_reply'] == "To Send") || empty($appointment_status[$row['msg_type']]['stage'] ?? '')) {
                if (!in_array($appointment_status[$row['msg_type']]['stage'] ?? '', ["CONFIRMED", "READ", "SENT", "FAILED"])) {
                    $appointment_status[$row['msg_type']]['stage'] = "QUEUED";
                    $iconHere[$row['msg_type']] = $icons[$row['msg_type']]['SCHEDULED']['html'] ?? '';
                }
            }

            // Handle special replies
            if ($row['msg_reply'] == "CALL") {
                $iconHere[$row['msg_type']] = $icons[$row['msg_type']]['CALL']['html'] ?? '';
                if (($appointment['allow_sms'] != "NO") && ($appointment['phone_cell'] > '')) {
                    $icon4Call = "<span class='input-group-addon' onclick='SMS_bot(" . attr_js($row['msg_pid']) . ");'>
                                  <i class='fas fa-sms'></i>
                              </span>";
                }
            } elseif ($row['msg_reply'] == "STOP") {
                $icon2Here .= $icons[$row['msg_type']]['STOP']['html'] ?? '';
            }
        }

        // Combine all icons
        return implode('', $iconHere) . $icon2Here . $iconExtra . $icon4Call;
    }

    /**
     * Get or create MedExAPI instance
     */
    private function getMedExAPI(): MedExAPI
    {
        if (!$this->medExAPI) {
            $this->medExAPI = new MedExAPI();
        }
        return $this->medExAPI;
    }
}
