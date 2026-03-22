<?php

/**
 * Display Service - Handles UI rendering for MedEx module
 *
 * This service is primarily responsible for HTML/UI output for the MedEx module.
 * Most methods output HTML directly rather than returning data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class DisplayService extends BaseService
{
    /**
     * Show progress for a recall
     *
     * @param array<string,mixed> $recall
     * @param array<string,mixed> $event
     * @return array<string,mixed>
     */
    public function show_progress_recall(array $recall, array $event): array
    {
        $pcp = null;
        $appt = null;
        $count = 0;
        $DONE = '0';
        $status = '';

        // Get provider info
        if (!empty($recall['r_provider'])) {
            $providerRecords = QueryUtils::fetchRecords(
                "SELECT * FROM users WHERE id=?",
                [$recall['r_provider']]
            );
            $pcp = $providerRecords[0] ?? null;
        }

        // Get upcoming appointment
        $apptRecords = QueryUtils::fetchRecords(
            "SELECT * FROM openemr_postcalendar_events WHERE 
             pc_pid=? AND pc_eventDate >= CURDATE() 
             ORDER BY pc_eventDate LIMIT 1",
            [$recall['r_pid'] ?? '']
        );
        $appt = $apptRecords[0] ?? null;

        // Get message history
        $msgRecords = QueryUtils::fetchRecords(
            "SELECT * FROM medex_outgoing WHERE msg_pc_eid=? AND campaign_uid=? 
             ORDER BY msg_date DESC",
            ['recall_' . ($recall['r_pid'] ?? ''), $event['C_UID'] ?? '']
        );

        // Determine status
        foreach ($msgRecords as $msg) {
            $count++;
            if (($msg['msg_reply'] ?? '') == 'CONFIRMED') {
                $status = 'greenish';
                $DONE = '1';
                break;
            } elseif (($msg['msg_reply'] ?? '') == 'CALL') {
                $status = 'reddish';
                break;
            } elseif (($msg['msg_reply'] ?? '') == 'STOP') {
                $status = 'reddish';
                $DONE = '1';
                break;
            }
        }

        // Check if appointment is scheduled
        if (!empty($appt) && $status != 'greenish') {
            $status = 'bluish';
            if (strtotime((string)($appt['pc_eventDate'] ?? '')) < strtotime((string)($recall['r_eventDate'] ?? ''))) {
                $DONE = '1';
            }
        }

        return [
            'DONE' => $DONE,
            'status' => $status,
            'count' => $count,
            'appt' => $appt,
            'pcp' => $pcp,
            'messages' => $msgRecords
        ];
    }

    /**
     * Display navigation menu
     *
     * @param array|false $logged_in The MedEx session data array from login(), or false if not logged in
     * @return void
     */
    public function navigation(array|false $logged_in): void
    {
        $globalsBag = OEGlobalsBag::getInstance();
        $webroot = $globalsBag->get('webroot');
        $web_root = $globalsBag->get('web_root');
        $medex_enable = $globalsBag->get('medex_enable');
        $disable_calendar = $globalsBag->get('disable_calendar');
        $disable_pat_trkr = $globalsBag->get('disable_pat_trkr');
        $loggedIn = $logged_in;

        global $setting_bootstrap_submenu;
        
        require(__DIR__ . '/../../templates/navigation.php');
    }

    /**
     * Display preferences panel
     *
     * @param array<string,mixed>|string $prefs
     * @return void
     */
    public function preferences(array|string $prefs = ''): void
    {
        if (empty($prefs)) {
            $prefsRecords = QueryUtils::fetchRecords("SELECT * FROM medex_prefs");
            $prefs = $prefsRecords[0] ?? [];
        }
        
        $globalsBag = OEGlobalsBag::getInstance();
        
        // Fetch facilities
        $facilities = QueryUtils::fetchRecords(
            "SELECT id, name FROM facility WHERE service_location = 1 ORDER BY name"
        );
        
        // Fetch providers
        $providers = QueryUtils::fetchRecords(
            "SELECT id, fname, lname FROM users WHERE authorized = 1 AND active = 1 ORDER BY lname, fname"
        );
        
        // Fetch appointment categories
        $categories = QueryUtils::fetchRecords(
            "SELECT pc_catid, pc_catname FROM openemr_postcalendar_categories 
             WHERE pc_active = 1 ORDER BY pc_catname"
        );
        
        // Output HTML (keeping original rendering)
        $prefView = __DIR__ . '/../../templates/preferences.php';
        if (file_exists($prefView)) {
            require($prefView);
        } else {
            echo '<p>' . xlt('Preferences view not available.') . '</p>';
        }
    }

    /**
     * Display recalls board
     *
     * @param bool $logged_in
     * @return void
     */
    public function display_recalls(bool $logged_in): void
    {
        $from_date = $_REQUEST['from_date'] ?? date('Y-m-d', strtotime('-6 months'));
        $to_date = $_REQUEST['to_date'] ?? date('Y-m-d', strtotime('+2 years'));
        
        $recalls = $this->get_recalls($from_date, $to_date);
        
        // Get events
        $eventRecords = QueryUtils::fetchRecords(
            "SELECT * FROM medex_prefs WHERE ME_username IS NOT NULL"
        );
        $prefs = $eventRecords[0] ?? null;
        $events = [];
        
        if (!empty($prefs['status'])) {
            $status = json_decode((string)$prefs['status'], true);
            $events = $status['status']['campaigns']['events'] ?? [];
        }
        
        $this->recall_board_process($logged_in, $recalls, $events);
    }

    /**
     * Get recalls from database
     *
     * @param string $from_date
     * @param string $to_date
     * @return array<int,array<string,mixed>>
     */
    public function get_recalls(string $from_date = '', string $to_date = ''): array
    {
        if (empty($from_date)) {
            $from_date = date('Y-m-d', strtotime('-6 months'));
        }
        if (empty($to_date)) {
            $to_date = date('Y-m-d', strtotime('+2 years'));
        }
        
        $recalls = QueryUtils::fetchRecords(
            "SELECT r.*, p.fname, p.lname, p.mname, p.phone_home, p.phone_cell, p.email,
                    p.street, p.city, p.state, p.postal_code,
                    u.fname as provider_fname, u.lname as provider_lname
             FROM medex_recalls r
             LEFT JOIN patient_data p ON r.r_pid = p.pid
             LEFT JOIN users u ON r.r_provider = u.id
             WHERE r.r_eventDate >= ? AND r.r_eventDate <= ?
             ORDER BY r.r_eventDate",
            [$from_date, $to_date]
        );
        
        return $recalls;
    }

    /**
     * Process recall board display
     *
     * @param bool $logged_in
     * @param array<int,array<string,mixed>> $recalls
     * @param array<string,mixed>|string $events
     * @return void
     */
    private function recall_board_process(bool $logged_in, array $recalls, array|string $events = ''): void
    {
        $this->recall_board_top();
        
        foreach ($recalls as $recall) {
            $this->display_recall_row($recall, $events);
        }
        
        $this->recall_board_bot();
    }

    /**
     * Display single recall row
     *
     * @param array<string,mixed> $recall
     * @param array<string,mixed>|string $events
     * @return void
     */
    private function display_recall_row(array $recall, array|string $events): void
    {
        // Get messages for this recall
        $msgRecords = QueryUtils::fetchRecords(
            "SELECT * FROM medex_outgoing WHERE msg_pc_eid=? ORDER BY msg_date DESC",
            ['recall_' . ($recall['r_pid'] ?? '')]
        );
        
        // Output row HTML
        echo "<tr data-pid='" . attr($recall['r_pid'] ?? '') . "'>";
        echo "<td>" . text($recall['r_eventDate'] ?? '') . "</td>";
        echo "<td>" . text(($recall['fname'] ?? '') . ' ' . ($recall['lname'] ?? '')) . "</td>";
        echo "<td>" . text($recall['r_reason'] ?? '') . "</td>";
        echo "<td>" . text(($recall['provider_fname'] ?? '') . ' ' . ($recall['provider_lname'] ?? '')) . "</td>";
        
        // Message status icons
        echo "<td>";
        foreach ($msgRecords as $msg) {
            echo $this->get_icon($msg['msg_type'] ?? '', $msg['msg_reply'] ?? '');
        }
        echo "</td>";
        
        echo "<td class='text-center'>";
        echo "<button class='btn btn-sm btn-primary' onclick='editRecall(" . attr($recall['r_ID'] ?? '') . ")'>" . xlt('Edit') . "</button>";
        echo "</td>";
        echo "</tr>";
    }

    /**
     * Display recall board header
     *
     * @return void
     */
    private function recall_board_top(): void
    {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-hover'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>" . xlt('Date') . "</th>";
        echo "<th>" . xlt('Patient') . "</th>";
        echo "<th>" . xlt('Reason') . "</th>";
        echo "<th>" . xlt('Provider') . "</th>";
        echo "<th>" . xlt('Status') . "</th>";
        echo "<th class='text-center actions-header'>" . xlt('Actions') . "</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
    }

    /**
     * Display recall board footer
     *
     * @return void
     */
    private function recall_board_bot(): void
    {
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }

    /**
     * Display add/edit recall form
     *
     * @param string|int $pid
     * @return void
     */
    public function display_add_recall(string|int $pid = 'new'): void
    {
        $recall = [];
        $patient = [];
        
        if ($pid !== 'new') {
            // Get existing recall
            $recallRecords = QueryUtils::fetchRecords(
                "SELECT * FROM medex_recalls WHERE r_pid=?",
                [$pid]
            );
            $recall = $recallRecords[0] ?? [];
            
            // Get patient data
            $patientRecords = QueryUtils::fetchRecords(
                "SELECT * FROM patient_data WHERE pid=?",
                [$pid]
            );
            $patient = $patientRecords[0] ?? [];
        }
        
        // Get providers
        $providers = QueryUtils::fetchRecords(
            "SELECT id, fname, lname FROM users WHERE authorized = 1 AND active = 1 ORDER BY lname, fname"
        );
        
        // Get facilities
        $facilities = QueryUtils::fetchRecords(
            "SELECT id, name FROM facility WHERE service_location = 1 ORDER BY name"
        );
        
        // Output form HTML
        $recallFormView = __DIR__ . '/../../templates/recall_form.php';
        if (file_exists($recallFormView)) {
            require($recallFormView);
        } else {
            echo '<p>' . xlt('Recall form view not available.') . '</p>';
        }
    }

    /**
     * Display icon template/legend
     *
     * @return void
     */
    public function icon_template(): void
    {
        $icons = QueryUtils::fetchRecords("SELECT * FROM medex_icons ORDER BY msg_type, msg_status");
        
        echo "<div class='icon-legend'>";
        echo "<h4>" . xlt('Icon Legend') . "</h4>";
        echo "<table class='table table-sm'>";
        
        $currentType = '';
        foreach ($icons as $icon) {
            if ($currentType != $icon['msg_type']) {
                if ($currentType != '') {
                    echo "</table></td></tr>";
                }
                echo "<tr><th colspan='2'>" . xlt($icon['msg_type']) . "</th></tr>";
                echo "<tr><td><table class='table table-borderless table-sm'>";
                $currentType = $icon['msg_type'];
            }
            
            echo "<tr>";
            echo "<td>" . $icon['i_html'] . "</td>";
            echo "<td>" . xlt($icon['msg_status']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table></td></tr>";
        echo "</table>";
        echo "</div>";
    }

    /**
     * Get message status icon
     *
     * @param string $event_type
     * @param string $status
     * @return string
     */
    private function get_icon(string $event_type, string $status = 'SCHEDULED'): string
    {
        $iconRecords = QueryUtils::fetchRecords(
            "SELECT i_html FROM medex_icons WHERE msg_type=? AND msg_status=?",
            [$event_type, $status]
        );
        $icon = $iconRecords[0] ?? null;
        
        return $icon['i_html'] ?? '';
    }

    /**
     * Display possible modalities for patient
     *
     * @param array<string,mixed> $appt
     * @return array<string,mixed>
     */
    public function possibleModalities(array $appt): array
    {
        $modalities = [
            'SMS' => false,
            'AVM' => false,
            'EMAIL' => false
        ];
        
        // Check SMS
        if (!empty($appt['phone_cell']) && ($appt['hipaa_allowsms'] ?? '') != 'NO') {
            $modalities['SMS'] = true;
        }
        
        // Check AVM (voice)
        if ((!empty($appt['phone_home']) || !empty($appt['phone_cell'])) && 
            ($appt['hipaa_voice'] ?? '') != 'NO') {
            $modalities['AVM'] = true;
        }
        
        // Check EMAIL
        if (!empty($appt['email']) && ($appt['hipaa_allowemail'] ?? '') != 'NO') {
            $modalities['EMAIL'] = true;
        }
        
        return $modalities;
    }

    /**
     * Display SMS bot interface
     * Redirects to the fixed interactive SMS bot interface
     *
     * @param bool $logged_in
     * @return void
     */
    public function SMS_bot(bool $logged_in): void
    {
        // Check if we're being called from the legacy messages.php
        // If so, we need to handle the session issue by providing a direct link
        if (!$logged_in) {
            // User not logged in - provide a direct link to the module
            $webroot = $GLOBALS['web_root'] ?? '/openemr';
            $redirectUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php';
            
            // Pass request parameters and add site parameter to handle session issue
            $params = $_REQUEST;
            unset($params['go']);
            if (!isset($params['site'])) {
                $params['site'] = 'default';
            }
            if (!empty($params)) {
                $redirectUrl .= '?' . http_build_query($params);
            }
            
            echo "<div class='alert alert-info'>";
            echo "<h3>" . xlt('SMS Bot Access') . "</h3>";
            echo "<p>" . xlt('Please use the direct link below to access the SMS Bot:') . "</p>";
            echo "<p><a href=\"" . htmlspecialchars($redirectUrl) . "\" class=\"btn btn-primary\">" . xlt('Open SMS Bot') . "</a></p>";
            echo "</div>";
            return;
        }
        
        // User is logged in - redirect to our fixed SMS bot entry point.
        // We cannot use header('Location: ...') here because messages.php
        // (validation_script.js.php) has already sent output, so use a
        // JavaScript redirect instead.
        $webroot = $GLOBALS['web_root'] ?? '/openemr';
        $redirectUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php';
        
        // Pass all request parameters except the 'go' parameter
        $params = $_REQUEST;
        unset($params['go']);
        if (!empty($params)) {
            $redirectUrl .= '?' . http_build_query($params);
        }
        
        $safeUrl = htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8');
        echo "<script>window.location.replace(" . json_encode($redirectUrl) . ");</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . $safeUrl . "'></noscript>";
        return;
    }

    /**
     * Display telehealth bot
     *
     * @param bool $logged_in
     * @param array<string,mixed> $data
     * @return void
     */
    public function TM_bot(bool $logged_in, array $data): void
    {
        if (!$logged_in) {
            echo "<div class='alert alert-danger'>" . xlt('Not authorized') . "</div>";
            return;
        }
        
        // Display telehealth interface
        echo "<div class='tm-bot-container'>";
        echo "<h3>" . xlt('Telehealth Messages') . "</h3>";
        echo "<p>" . xlt('Telehealth messaging interface') . "</p>";
        echo "</div>";
    }

    /**
     * Sync patient data
     *
     * @param int|string $pid
     * @param bool $logged_in
     * @return array<string,mixed>
     */
    public function syncPat(int|string $pid, bool $logged_in): array
    {
        if (!$logged_in) {
            return ['success' => false, 'error' => 'Not authorized'];
        }
        
        // Get patient data
        $patientRecords = QueryUtils::fetchRecords(
            "SELECT * FROM patient_data WHERE pid=?",
            [$pid]
        );
        $patient = $patientRecords[0] ?? null;
        
        if (empty($patient)) {
            return ['success' => false, 'error' => 'Patient not found'];
        }
        
        // Get appointments
        $appointments = QueryUtils::fetchRecords(
            "SELECT * FROM openemr_postcalendar_events 
             WHERE pc_pid=? AND pc_eventDate >= CURDATE() 
             ORDER BY pc_eventDate LIMIT 10",
            [$pid]
        );
        
        return [
            'success' => true,
            'patient' => $patient,
            'appointments' => $appointments
        ];
    }
}
