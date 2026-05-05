<?php

/**
 * Practice Service - Handles practice data synchronization with MedEx
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

class PracticeService extends BaseService
{
    private ?string $lastError = null;

    /**
     * Sync practice data with MedEx server
     *
     * @param string $token API token
     * @return array<string,mixed>|false
     */
    public function sync(string $token): array|false
    {
        $globalsBag = OEGlobalsBag::getInstance();
        $fields2 = [];
        $fields3 = [];
        $tell_MedEx = ['DELETE_MSG' => []];

        // Build callback URL using public-facing base URL.
        // In K8s/reverse-proxy environments SERVER_NAME is an internal hostname;
        // use the 'medex_callback_base_url' global to override it.
        $serverData = $globalsBag->get('_SERVER');
        $callbackBase = $GLOBALS['medex_callback_base_url']
            ?? $GLOBALS['site_addr_oath']
            ?? ('https://' . ($serverData['HTTP_HOST'] ?? $serverData['SERVER_NAME'] ?? 'localhost'));
        $cbToken = \OpenEMR\Common\Database\QueryUtils::fetchSingleValue(
            "SELECT gl_value FROM globals WHERE gl_name = ?",
            'gl_value',
            ['medex_callback_token']
        ) ?? '';
        $cbSite = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_SESSION['site_id'] ?? 'default'));
        if ($cbSite === '') {
            $cbSite = 'default';
        }
        $callback = rtrim($callbackBase, '/')
            . '/interface/modules/custom_modules/oe-module-medex/public/callback.php'
            . '?token=' . rawurlencode($cbToken)
            . '&site=' . rawurlencode($cbSite);
        $fields2['callback_url'] = $callback;

        // Get practice preferences
        $prefsRecords = QueryUtils::fetchRecords("SELECT * FROM medex_prefs");
        $my_status = $prefsRecords[0] ?? null;

        if (!$my_status) {
            return false;
        }

        // Get providers
        $providers = explode('|', (string) $my_status['ME_providers']);
        $fields2['providers'] = [];
        foreach ($providers as $provider) {
            if (empty($provider)) {
                continue;
            }
            $providerRows = QueryUtils::fetchRecords("SELECT * FROM users WHERE id=?", [$provider]);
            foreach ($providerRows as $providerRow) {
                $fields2['providers'][] = $providerRow;
            }
        }

        // Get facilities
        $facilities = explode('|', (string) $my_status['ME_facilities']);
        $facilityRows = QueryUtils::fetchRecords("SELECT * FROM facility WHERE service_location='1'");
        $fields2['facilities'] = [];
        foreach ($facilityRows as $facility) {
            if (in_array($facility['id'], $facilities)) {
                $facility['messages_active'] = '1';
                $fields2['facilities'][] = $facility;
            }
        }

        // Get appointment categories
        $fields2['categories'] = QueryUtils::fetchRecords(
            "SELECT pc_catid, pc_catname, pc_catdesc, pc_catcolor, pc_seq
             FROM openemr_postcalendar_categories
             WHERE pc_active = 1 AND pc_cattype='0'
             ORDER BY pc_catid"
        );

        // Get appointment statuses
        $fields2['apptstats'] = QueryUtils::fetchRecords(
            "SELECT * FROM list_options WHERE list_id LIKE 'apptstat' AND activity='1'"
        );

        // Get checked out statuses
        $fields2['checkedOut'] = QueryUtils::fetchRecords(
            "SELECT option_id FROM list_options
             WHERE toggle_setting_2='1' AND list_id='apptstat' AND activity='1'"
        );

        // Get clinical reminders (legacy function call)
        $sql = "SELECT * FROM clinical_rules, list_options, rule_action, rule_action_item
                WHERE clinical_rules.pid=0
                AND clinical_rules.patient_reminder_flag = 1
                AND clinical_rules.id = list_options.option_id
                AND clinical_rules.id = rule_action.id
                AND list_options.option_id=clinical_rules.id
                AND rule_action.category = rule_action_item.category
                AND rule_action.item = rule_action_item.item";

        $fields2['clinical_reminders'] = [];
        if (function_exists('sqlStatementCdrEngine')) {
            $ures = sqlStatementCdrEngine($sql);
            // @phpstan-ignore-next-line
            while ($urow = sqlFetchArray($ures)) {
                $fields2['clinical_reminders'][] = $urow;
            }
        }

        // Send practice data to MedEx
        $this->curl->setUrl($this->medEx->getUrl('custom/addpractice&token=' . $token));
        $this->curl->setData($fields2);

        try {
            $this->curl->makeRequest();
        } catch (\Exception $e) {
            error_log("MedEx Practice sync failed: " . $e->getMessage());
            throw $e;
        }

        $response = $this->curl->getResponse();

        // Check for cancelled appointments to delete
        $outgoingRows = QueryUtils::fetchRecords(
            "SELECT * FROM medex_outgoing WHERE msg_pc_eid NOT LIKE 'recall_%' AND msg_reply = 'To Send'"
        );

        foreach ($outgoingRows as $outgoing) {
            $apptRecords = QueryUtils::fetchRecords(
                "SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?",
                [$outgoing['msg_pc_eid']]
            );
            $appt = $apptRecords[0] ?? null;

            if ($appt && in_array($appt['pc_apptstatus'], ['*', '%', 'x'])) {
                // Cancelled - mark as done
                QueryUtils::sqlStatementThrowException(
                    "UPDATE medex_outgoing SET msg_reply = 'DONE', msg_extra_text=? WHERE msg_uid = ?",
                    [$appt['pc_apptstatus'], $outgoing['msg_uid']]
                );
                $tell_MedEx['DELETE_MSG'][] = $outgoing['msg_pc_eid'];
            }
        }

        // Check for scheduled recalls
        $recallRows = QueryUtils::fetchRecords(
            "SELECT * FROM medex_outgoing WHERE msg_pc_eid LIKE 'recall_%' GROUP BY msg_pc_eid"
        );

        foreach ($recallRows as $recall) {
            $pid = trim($recall['msg_pc_eid'], "recall_");
            $futureAppts = QueryUtils::fetchRecords(
                "SELECT pc_eid FROM openemr_postcalendar_events
                 WHERE pc_eventDate > CURDATE() AND pc_pid=?",
                [$pid]
            );

            if (!empty($futureAppts)) {
                $futureAppt = $futureAppts[0];
                QueryUtils::sqlStatementThrowException(
                    "UPDATE medex_outgoing SET msg_reply = 'SCHEDULED', msg_extra_text=? WHERE msg_pc_eid = ?",
                    [$futureAppt['pc_eid'], $recall['msg_pc_eid']]
                );
                $tell_MedEx['DELETE_MSG'][] = $recall['msg_pc_eid'];
            }
        }

        // Sync responses from MedEx
        $fields3['MedEx_lastupdated'] = $my_status['MedEx_lastupdated'] ?? null;
        $fields3['ME_providers'] = $my_status['ME_providers'] ?? null;
        $medexId = $my_status['MedEx_id'] ?? null;

        $this->curl->setUrl($this->medEx->getUrl('custom/sync_responses&token=' . $token . '&id=' . $medexId));
        $this->curl->setData($fields3);

        try {
            $this->curl->makeRequest();
        } catch (\Exception $e) {
            error_log("MedEx Response sync failed: " . $e->getMessage());
            throw $e;
        }

        $responses = $this->curl->getResponse();

        // Process incoming messages
        if (!empty($responses['messages'])) {
            foreach ($responses['messages'] as $data) {
                $data['msg_extra'] = $data['msg_extra'] ?? '';

                // Check if message already exists
                $existing = QueryUtils::fetchRecords(
                    "SELECT * FROM medex_outgoing WHERE medex_uid=?",
                    [$data['msg_uid']]
                );

                if (empty($existing)) {
                    $this->medEx->callback->receive($data);
                }
            }
        }

        // Update last sync time
        QueryUtils::sqlStatementThrowException("UPDATE medex_prefs SET MedEx_lastupdated=utc_timestamp()");

        // Delete cancelled messages on MedEx
        if (!empty($tell_MedEx['DELETE_MSG'])) {
            $this->curl->setUrl($this->medEx->getUrl('custom/remMessaging&token=' . $token . '&id=' . $medexId));
            $this->curl->setData(['messages' => $tell_MedEx['DELETE_MSG']]);

            try {
                $this->curl->makeRequest();
            } catch (\Exception $e) {
                error_log("MedEx Delete messages failed: " . $e->getMessage());
                // Non-fatal - continue
            }

            $response = $this->curl->getResponse();
        }

        // Format response
        if (!empty($response['found_replies'])) {
            $response['success']['message'] = xlt("Replies retrieved") . ": " . $response['found_replies'];
        } else {
            $response['success']['message'] = xlt("No new messages on") . " MedEx.";
        }

        if (isset($response['success'])) {
            return $response;
        } elseif (isset($response['error'])) {
            $this->lastError = $response['error'];
        }

        return false;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
