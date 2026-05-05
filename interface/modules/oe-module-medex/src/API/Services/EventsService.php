<?php

/**
 * Events Service - Handles MedEx event generation and processing
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
use MedExApi\Exceptions\InvalidDataException;

class EventsService extends BaseService
{
    /** @phpstan-ignore-next-line property.onlyWritten */
    private ?string $lastError = null;

    /**
     * Generate events for campaigns
     *
     * @param string $token
     * @param array<string,mixed> $events
     * @return array<string,mixed>|false
     */
    public function generate(string $token, array $events): array|false
    {
        if (empty($events)) {
            return false;
        }

        $appt3 = [];
        $RECALLS_completed = [];
        $count_appts = 0;
        $count_recalls = 0;
        $count_recurrents = 0;
        $count_announcements = 0;
        $count_surveys = 0;
        $count_clinical_reminders = 0;
        $count_gogreen = 0;

        // Load icons
        $icon = [];
        $iconRows = QueryUtils::fetchRecords("SELECT * FROM medex_icons");
        foreach ($iconRows as $icons) {
            $icon[$icons['msg_type']][$icons['msg_status']] = $icons['i_html'];
        }

        // Get preferences
        $prefsRecords = QueryUtils::fetchRecords("SELECT * FROM medex_prefs");
        $prefs = $prefsRecords[0] ?? null;

        foreach ($events as $event) {
            $escapedArr = [];
            $target_lang = '';
            
            // Build language filter
            if (!empty($event['E_language']) && $event['E_language'] != "all") {
                $build_langs = '';
                $langs = explode("|", (string)$event['E_language']);
                foreach ($langs as $lang) {
                    if ($lang == 'No preference') {
                        $build_langs .= "pat.language = '' OR ";
                    } else {
                        $build_langs .= "pat.language=? OR ";
                        $escapedArr[] = $lang;
                    }
                }
                $build_langs = rtrim($build_langs, "OR ");
                $target_lang = "(" . $build_langs . ") AND ";
            }

            $M_group = $event['M_group'] ?? '';

            if ($M_group == 'REMINDER') {
                $this->processReminders($event, $prefs, $icon, $target_lang, $escapedArr, $appt3, $count_appts, $count_recurrents);
            } elseif ($M_group == 'RECALL') {
                $this->processRecalls($event, $events, $icon, $appt3, $RECALLS_completed, $count_recalls);
            } elseif ($M_group == 'ANNOUNCE') {
                $this->processAnnouncements($event, $icon, $escapedArr, $appt3, $count_announcements, $count_recurrents);
            } elseif ($M_group == 'SURVEY') {
                $this->processSurveys($event, $prefs, $icon, $escapedArr, $appt3, $count_surveys);
            } elseif ($M_group == 'CLINICAL_REMINDER') {
                $this->processClinicalReminders($event, $icon, $count_clinical_reminders);
            } elseif ($M_group == 'GOGREEN') {
                $this->processGoGreen($event, $prefs, $icon, $target_lang, $escapedArr, $appt3, $count_gogreen, $count_recurrents);
            }
        }

        $deletes = null;
        if (!empty($RECALLS_completed)) {
            $deletes = $this->process_deletes($token, $RECALLS_completed);
        }

        if (!empty($appt3)) {
            $this->process($token, $appt3);
        }

        $responses = [];
        $responses['deletes'] = $deletes;
        $responses['count_appts'] = $count_appts;
        $responses['count_recalls'] = $count_recalls;
        $responses['count_recurrents'] = $count_recurrents;
        $responses['count_announcements'] = $count_announcements;
        $responses['count_surveys'] = $count_surveys;
        $responses['count_clinical_reminders'] = $count_clinical_reminders;
        $responses['count_gogreen'] = $count_gogreen;

        return $responses;
    }

    /**
     * Process REMINDER events
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed>|null $prefs
     * @param array<string,mixed> $icon
     * @param string $target_lang
     * @param array<mixed> $escapedArr
     * @param array<mixed> &$appt3
     * @param int &$count_appts
     * @param int &$count_recurrents
     */
    private function processReminders(
        array $event,
        ?array $prefs,
        array $icon,
        string $target_lang,
        array $escapedArr,
        array &$appt3,
        int &$count_appts,
        int &$count_recurrents
    ): void {
        $interval = ($event['time_order'] ?? '0') > '0' ? "+" : '-';
        
        if ($interval == "+") {
            if (($event['E_instructions'] ?? '') == "stop") {
                $appt_status = " and pc_apptstatus='-'";
            } elseif (($event['E_instructions'] ?? '') == "always") {
                $appt_status = " and pc_apptstatus != '%' and pc_apptstatus != 'x' ";
            } else {
                $appt_status = " and pc_apptstatus='-'";
            }
        } else {
            $appt_status = " and pc_apptstatus in (SELECT option_id from list_options where toggle_setting_2='1' and list_id='apptstat')
                            and pc_apptstatus != '%'
                            and pc_apptstatus != 'x' ";
        }

        $timing = ((int)($event['E_fire_time'] ?? 0)) - 1;
        $today = date("l");
        if ($today == "Sunday" || $today == "Saturday") {
            return;
        }
        $timing2 = $today == "Friday" ? ($timing + 3) . ":0:1" : ($timing + 1) . ":1:1";

        if (empty($prefs['ME_facilities'])) {
            return;
        }

        $places = str_replace("|", ",", (string)$prefs['ME_facilities']);
        $query = "SELECT * FROM openemr_postcalendar_events AS cal
                    LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                    WHERE
                    " . $target_lang . "
                    (
                      (
                        pc_eventDate > CURDATE() " . $interval . " INTERVAL " . $timing . " DAY AND
                        pc_eventDate < CURDATE() " . $interval . " INTERVAL '" . $timing2 . "' DAY_MINUTE
                       )
                      OR
                      (
                        pc_eventDate <= CURDATE() " . $interval . " INTERVAL '" . $timing2 . "' DAY_MINUTE AND
                        pc_endDate >= curdate() " . $interval . " INTERVAL " . $timing . " DAY AND
                        pc_recurrtype >'0'
                      )
                    )
                    " . $appt_status . "
                     and pat.pid > ''
                    AND pc_facility IN (" . $places . ")
                    AND pat.pid=cal.pc_pid  ORDER BY pc_eventDate,pc_startTime";

        $rows = QueryUtils::fetchRecords($query, $escapedArr);
        foreach ($rows as $appt) {
            if (($appt['e_is_subEvent_of'] ?? '0') > '0') {
                $event2Records = QueryUtils::fetchRecords(
                    "SELECT msg_date FROM medex_outgoing WHERE msg_uid=?",
                    [$appt['e_is_subEvent_of']]
                );
                $event2 = $event2Records[0] ?? null;
                if ($event2 && new \DateTime() < new \DateTime($event2["msg_date"])) {
                    continue;
                }
            }

            [$response, $results] = $this->medEx->checkModality($event, $appt, $icon);
            if ($results == false) {
                continue;
            }

            if (($appt['pc_recurrtype'] ?? '0') != '0' && $interval == "+") {
                $recurrents = $this->addRecurrent($appt, $interval, $timing, $timing2, "REMINDER");
                $count_recurrents += $recurrents;
                continue;
            }

            $count_appts++;
            $appt3[] = $this->buildApptArray($appt, $event, $results);
        }
    }

    /**
     * Process RECALL events
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $icon
     * @param array<mixed> &$appt3
     * @param array<mixed> &$RECALLS_completed
     * @param int &$count_recalls
     */
    private function processRecalls(
        array $event,
        array $events,
        array $icon,
        array &$appt3,
        array &$RECALLS_completed,
        int &$count_recalls
    ): void {
        $interval = ($event['time_order'] ?? '0') > '0' ? "+" : '-';
        $timing = $event['E_fire_time'] ?? 0;

        $query = "SELECT * FROM medex_recalls AS recall
                    LEFT JOIN patient_data AS pat ON recall.r_pid=pat.pid
                    WHERE (recall.r_eventDate < CURDATE() " . $interval . " INTERVAL " . $timing . " DAY)
                    ORDER BY recall.r_eventDate";

        $rows = QueryUtils::fetchRecords($query);
        foreach ($rows as $recall) {
            [$response, $results] = $this->medEx->checkModality($event, $recall, $icon);
            if ($results == false) {
                continue;
            }

            $show = $this->medEx->display->show_progress_recall($recall, $events);
            if (($show['DONE'] ?? '0') == '1') {
                $RECALLS_completed[] = $recall;
                continue;
            }
            if (($show['status'] ?? '') == "reddish") {
                continue;
            }

            if (strtotime((string)($recall['r_eventDate'] ?? '')) < mktime(0, 0, 0)) {
                if ($this->recursive_array_search("recall_" . ($recall['r_pid'] ?? ''), $appt3)) {
                    continue;
                }
            }

            $count_recalls++;
            $recall2 = [
                'pc_pid' => $recall['r_pid'] ?? '',
                'pc_eventDate' => $recall['r_eventDate'] ?? '',
                'pc_startTime' => '10:42:00',
                'pc_eid' => "recall_" . ($recall['r_pid'] ?? ''),
                'pc_aid' => $recall['r_provider'] ?? '',
                'e_is_subEvent_of' => "0",
                'language' => $recall['language'] ?? '',
                'pc_facility' => $recall['r_facility'] ?? '',
                'fname' => $recall['fname'] ?? '',
                'lname' => $recall['lname'] ?? '',
                'mname' => $recall['mname'] ?? '',
                'street' => $recall['street'] ?? '',
                'postal_code' => $recall['postal_code'] ?? '',
                'city' => $recall['city'] ?? '',
                'state' => $recall['state'] ?? '',
                'country_code' => $recall['country_code'] ?? '',
                'phone_home' => $recall['phone_home'] ?? '',
                'phone_cell' => $recall['phone_cell'] ?? '',
                'email' => $recall['email'] ?? '',
                'C_UID' => $event['C_UID'] ?? '',
                // FIX 2026-03-10: M_type was missing — without it, process() SQL UPDATE
                // WHERE msg_type=? matched nothing, leaving records stuck as "To Send"
                'M_type' => $event['M_type'] ?? '',
                'reply' => "To Send",
                'extra' => "QUEUED",
                'status' => "SENT",
                'to' => $results
            ];
            $appt3[] = $recall2;
        }
    }

    /**
     * Process ANNOUNCE events
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $icon
     * @param array<mixed> $escapedArr
     * @param array<mixed> &$appt3
     * @param int &$count_announcements
     * @param int &$count_recurrents
     */
    private function processAnnouncements(
        array $event,
        array $icon,
        array $escapedArr,
        array &$appt3,
        int &$count_announcements,
        int &$count_recurrents
    ): void {
        if (empty($event['start_date'])) {
            return;
        }

        $today = strtotime(date('Y-m-d'));
        $start = strtotime((string)($event['appts_start'] ?? ''));

        if ($today < $start) {
            return;
        }

        // Build date filter
        if ($start >= $today) {
            if (empty($event['appts_end'])) {
                $event['appts_end'] = $event['appts_start'];
            }
            $target_dates = "(
                          (
                            cal.pc_eventDate >= ? AND
                            cal.pc_eventDate <= ?
                           )
                          OR
                          (
                            cal.pc_eventDate <= ? AND
                            cal.pc_endDate >= ? AND
                            pc_recurrtype >'0'
                          )
                        ) ";
            $escapedArr[] = $event['appts_start'];
            $escapedArr[] = $event['appts_end'];
            $escapedArr[] = $event['appts_end'];
            $escapedArr[] = $event['appts_start'];
        } else {
            if (empty($event['appts_end'])) {
                $target_dates = "pc_eventDate = ?";
                $escapedArr[] = $event['appts_start'];
            } else {
                $target_dates = "(pc_eventDate >= ? and pc_eventDate <= ?)";
                $escapedArr[] = $event['appts_start'];
                $escapedArr[] = $event['appts_end'];
            }
        }

        // Build status filter
        $appt_status = $this->buildInFilter('appt_stats', 'cal.pc_apptstatus', $event, $escapedArr);
        $providers = $this->buildInFilter('providers', 'cal.pc_aid', $event, $escapedArr);
        $places = $this->buildInFilter('facilities', 'cal.pc_facility', $event, $escapedArr);
        $visit_types = $this->buildInFilter('visit_types', 'cal.pc_catid', $event, $escapedArr);

        $sql_ANNOUNCE = "SELECT * FROM openemr_postcalendar_events AS cal
                    LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                    WHERE " . $target_dates . "
                        " . $appt_status . "
                        " . $providers . "
                        " . $places . "
                        " . $visit_types . "
                    ORDER BY pc_eventDate,pc_startTime";

        $rows = QueryUtils::fetchRecords($sql_ANNOUNCE, $escapedArr);
        foreach ($rows as $appt) {
            [$response, $results] = $this->medEx->checkModality($event, $appt, $icon);
            if ($results == false) {
                continue;
            }

            if (($appt['pc_recurrtype'] ?? '0') != '0') {
                $recurrents = $this->addRecurrent($appt, "+", $event['appts_start'] ?? '', $event['appts_end'] ?? '', "ANNOUNCE");
                $count_recurrents += $recurrents;
                continue;
            }

            $count_announcements++;
            $appt2 = $this->buildApptArray($appt, $event, $results);
            $appt2['pc_eid'] = ($event['C_UID'] ?? '') . '_' . ($appt['pc_eid'] ?? '');
            $appt2['e_apptstatus'] = $appt['pc_apptstatus'] ?? '';
            $appt3[] = $appt2;
        }
    }

    /**
     * Process SURVEY events
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed>|null $prefs
     * @param array<string,mixed> $icon
     * @param array<mixed> $escapedArr
     * @param array<mixed> &$appt3
     * @param int &$count_surveys
     */
    private function processSurveys(
        array $event,
        ?array $prefs,
        array $icon,
        array $escapedArr,
        array &$appt3,
        int &$count_surveys
    ): void {
        if (empty($event['timing'])) {
            $event['timing'] = "180";
        }

        $appt_status = " and pc_apptstatus in (SELECT option_id from list_options where toggle_setting_2='1' and list_id='apptstat') ";
        $facility_clause = $this->buildInFilter('facilities', 'cal.pc_facility', $event, $escapedArr);

        $all_providers = explode('|', (string)($prefs['ME_providers'] ?? ''));
        foreach (($event['survey'] ?? []) as $k => $v) {
            if (($v <= 0) || (empty($event['providers'])) || (!in_array($k, $all_providers))) {
                continue;
            }

            $escapedArr[] = $k;
            $query = "SELECT * FROM openemr_postcalendar_events AS cal
                            LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                            WHERE (
                                cal.pc_eventDate > CURDATE() - INTERVAL " . $event['timing'] . " DAY AND
                                cal.pc_eventDate < CURDATE() - INTERVAL 3 DAY) AND
                                pat.pid=cal.pc_pid AND
                                pc_apptstatus !='%' AND
                                pc_apptstatus != 'x' " .
                                $appt_status .
                                $facility_clause . "
                                AND cal.pc_aid IN (?)
                            GROUP BY pc_pid
                            ORDER BY pc_eventDate,pc_startTime
                            LIMIT " . $v;

            $rows = QueryUtils::fetchRecords($query, $escapedArr);
            foreach ($rows as $appt) {
                [$response, $results] = $this->medEx->checkModality($event, $appt, $icon);
                if ($results == false) {
                    continue;
                }

                $appt2 = $this->buildApptArray($appt, $event, $results);
                $appt2['E_fire_time'] = $event['E_fire_time'] ?? '';
                $appt2['time_order'] = $event['time_order'] ?? '';
                $appt2['M_type'] = $event['M_type'] ?? '';
                $appt3[] = $appt2;
                $count_surveys++;
            }
        }
    }

    /**
     * Process CLINICAL_REMINDER events
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed> $icon
     * @param int &$count_clinical_reminders
     */
    private function processClinicalReminders(
        array $event,
        array $icon,
        int &$count_clinical_reminders
    ): void {
        $sql = "SELECT * FROM `patient_reminders`,`patient_data`
                      WHERE
                    `patient_reminders`.pid ='" . ($event['PID'] ?? '') . "' AND
                    `patient_reminders`.active='1' AND
                    `patient_reminders`.date_sent IS NULL AND
                    `patient_reminders`.pid=`patient_data`.pid
                      ORDER BY `due_status`, `date_created`";

        if (function_exists('sqlStatementCdrEngine')) {
            $ures = sqlStatementCdrEngine($sql);
            // @phpstan-ignore-next-line openemr.deprecatedSqlFunction
            while ($urow = sqlFetchArray($ures)) {
                [$response, $results] = $this->medEx->checkModality($event, $urow, $icon);
                if ($results == false) {
                    continue;
                }
                $count_clinical_reminders++;
            }
        }
    }

    /**
     * Process GOGREEN events
     *
     * @param array<string,mixed> $event
     * @param array<string,mixed>|null $prefs
     * @param array<string,mixed> $icon
     * @param string $target_lang
     * @param array<mixed> $escapedArr
     * @param array<mixed> &$appt3
     * @param int &$count_gogreen
     * @param int &$count_recurrents
     */
    private function processGoGreen(
        array $event,
        ?array $prefs,
        array $icon,
        string $target_lang,
        array $escapedArr,
        array &$appt3,
        int &$count_gogreen,
        int &$count_recurrents
    ): void {
        $appt_status = $this->buildInFilter('appt_stats', 'cal.pc_apptstatus', $event, $escapedArr);
        $providers = $this->buildInFilter('providers', 'cal.pc_aid', $event, $escapedArr);
        $places = $this->buildInFilter('facilities', 'cal.pc_facility', $event, $escapedArr);
        $visit_types = $this->buildInFilter('visit_types', 'cal.pc_catid', $event, $escapedArr);

        $frequency = '';
        $E_instructions = $event['E_instructions'] ?? '';
        if ($E_instructions == 'once') {
            $frequency = " AND cal.pc_pid NOT in (
                SELECT msg_pid from medex_outgoing where
                    campaign_uid =?  and msg_date >= curdate() )";
            $escapedArr[] = (int)($event['C_UID'] ?? 0);
        } elseif ($E_instructions == 'yearly') {
            $frequency = " AND cal.pc_pid NOT in (
                SELECT msg_pid from medex_outgoing where
                    campaign_uid =? and
                    msg_date > curdate() - interval 1 year )";
            $escapedArr[] = (int)($event['C_UID'] ?? 0);
        } elseif ($E_instructions == 'all') {
            $frequency = " AND cal.pc_eid NOT in (
                        SELECT DISTINCT msg_pc_eid from medex_outgoing where
                            campaign_uid=? and
                            msg_date > curdate() )
                        AND
                            cal.pc_time >= NOW() - interval 6 hour ";
            $escapedArr[] = $event['C_UID'] ?? '';
        }

        $no_dupes = " AND cal.pc_eid NOT IN (
                        SELECT DISTINCT msg_pc_eid from medex_outgoing where
                        campaign_uid=? and msg_date >= curdate() ) ";
        $escapedArr[] = $event['C_UID'] ?? '';

        $target_dates = $this->buildGoGreenDateFilter($event);

        $sql_GOGREEN = "SELECT * FROM openemr_postcalendar_events AS cal
                        LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                        WHERE
                            " . $target_lang . "
                            " . $target_dates . "
                            " . $appt_status . "
                            " . $providers . "
                            " . $places . "
                            " . $visit_types . "
                            " . $frequency . "
                            " . $no_dupes . "
                        ORDER BY cal.pc_eventDate,cal.pc_startTime";

        try {
            $result = QueryUtils::sqlStatementThrowException($sql_GOGREEN, $escapedArr);
        } catch (\Exception $e) {
            $this->medEx->logging->log_this($sql_GOGREEN);
            throw $e;
        }

        // @phpstan-ignore-next-line
        while ($appt = sqlFetchArray($result)) {
            [$response, $results] = $this->medEx->checkModality($event, $appt, $icon);
            if ($results == false) {
                continue;
            }

            if (($appt['pc_recurrtype'] ?? '0') != '0') {
                $recurrents = $this->addRecurrent($appt, "+", $event['appts_start'] ?? '', $event['appts_end'] ?? '', "GOGREEN");
                $count_recurrents += $recurrents;
                continue;
            }

            $count_gogreen++;
            $appt3[] = $this->buildApptArray($appt, $event, $results);
        }
    }

    /**
     * Build SQL IN filter clause
     *
     * @param string $eventKey
     * @param string $columnName
     * @param array<string,mixed> $event
     * @param array<mixed> &$escapedArr
     * @return string
     */
    private function buildInFilter(string $eventKey, string $columnName, array $event, array &$escapedArr): string
    {
        if (empty($event[$eventKey])) {
            return '';
        }

        $prepare_me = '';
        $items = explode('|', (string)$event[$eventKey]);
        foreach ($items as $item) {
            $prepare_me .= "?,";
            $escapedArr[] = $item;
        }
        $prepare_me = rtrim($prepare_me, ",");
        return " AND " . $columnName . " in (" . $prepare_me . ") ";
    }

    /**
     * Build date filter for GOGREEN events
     *
     * @param array<string,mixed> $event
     * @return string
     */
    private function buildGoGreenDateFilter(array $event): string
    {
        $today = date("l");
        $E_timing = $event['E_timing'] ?? '';
        
        if ($E_timing == '5') {
            return " cal.pc_eventDate >= curdate()  ";
        }

        $timing = (int)($event['E_fire_time'] ?? 0);
        
        if ($E_timing == '1' || $E_timing == '2') {
            $target_dates = "(
                      (
                        cal.pc_eventDate = CURDATE() + INTERVAL " . $timing . " DAY
                      )
                      OR
                      (
                        cal.pc_eventDate <= CURDATE() + INTERVAL " . $timing . " DAY  AND
                        cal.pc_endDate >= CURDATE() + INTERVAL " . $timing . " DAY AND
                        cal.pc_recurrtype >'0'
                      )
                    )";

            if ($today == "Friday") {
                $timing2 = ($timing + 2);
                $target_dates = "(
                      (
                        cal.pc_eventDate >= (CURDATE() + INTERVAL " . $timing . " DAY)  AND
                        cal.pc_eventDate <= (CURDATE() + INTERVAL " . $timing2 . " DAY)
                      )
                      OR
                      (
                        cal.pc_eventDate <= CURDATE() + INTERVAL " . $timing2 . " DAY  AND
                        cal.pc_endDate >= CURDATE() + INTERVAL " . $timing . " DAY AND
                        cal.pc_recurrtype >'0'
                      )
                    )";
            }
            return $target_dates;
        }

        if ($E_timing == '3' || $E_timing == '4') {
            $target_dates = "cal.pc_eventDate = curdate() - interval " . $timing . " day";
            if ($today == "Monday") {
                $timing2 = ($timing + 3);
                $target_dates .= " AND cal.pc_eventDate <= curdate() - INTERVAL " . $timing . " DAY AND
                              cal.pc_eventDate > (curdate() - INTERVAL '" . $timing2 . "' DAY) ";
            }
            return $target_dates;
        }

        return "";
    }

    /**
     * Build standard appointment array
     *
     * @param array<string,mixed> $appt
     * @param array<string,mixed> $event
     * @param string|false $to
     * @return array<string,mixed>
     */
    private function buildApptArray(array $appt, array $event, string|false $to): array
    {
        return [
            'pc_pid' => $appt['pc_pid'] ?? '',
            'pc_eventDate' => $appt['pc_eventDate'] ?? '',
            'pc_startTime' => $appt['pc_startTime'] ?? '',
            'pc_eid' => $appt['pc_eid'] ?? '',
            'pc_aid' => $appt['pc_aid'] ?? '',
            'e_reason' => $appt['e_reason'] ?? '',
            'e_is_subEvent_of' => $appt['e_is_subEvent_of'] ?? "0",
            'language' => $appt['language'] ?? '',
            'pc_facility' => $appt['pc_facility'] ?? '',
            'fname' => $appt['fname'] ?? '',
            'lname' => $appt['lname'] ?? '',
            'mname' => $appt['mname'] ?? '',
            'street' => $appt['street'] ?? '',
            'postal_code' => $appt['postal_code'] ?? '',
            'city' => $appt['city'] ?? '',
            'state' => $appt['state'] ?? '',
            'country_code' => $appt['country_code'] ?? '',
            'phone_home' => $appt['phone_home'] ?? '',
            'phone_cell' => $appt['phone_cell'] ?? '',
            'email' => $appt['email'] ?? '',
            'pc_apptstatus' => $appt['pc_apptstatus'] ?? '',
            'C_UID' => $event['C_UID'] ?? '',
            'reply' => "To Send",
            'extra' => "QUEUED",
            'status' => "SENT",
            'to' => $to
        ];
    }

    /**
     * Add recurrent appointment events
     *
     * @param array<string,mixed> $appt
     * @param string $interval
     * @param int|string $timing
     * @param int|string $timing2
     * @param string $M_group
     * @return int
     */
    private function addRecurrent(array $appt, string $interval, int|string $timing, int|string $timing2, string $M_group = "REMINDER"): int
    {
        // Get date range
        if ($M_group == "REMINDER") {
            $start = explode(':', (string)$timing);
            $end = explode(':', (string)$timing2);
            $start_date = date('Y-m-d', strtotime($interval . $start[0] . ' day'));
            $stop_date = date('Y-m-d', strtotime($interval . $end[0] . ' day'));
        } else {
            $start_date = (string)$timing;
            $stop_date = (string)$timing2;
        }

        // Calculate occurrences
        $hits = $this->calculateEvents($appt, $start_date, $stop_date);

        // Update recurrence spec
        $oldRecurrspec = unserialize($appt['pc_recurrspec'] ?? '', ['allowed_classes' => false]);

        foreach ($hits as $selected_date) {
            $exclude = str_replace("-", "", $selected_date);
            
            if (($oldRecurrspec['exdate'] ?? '') != "") {
                $oldRecurrspec['exdate'] .= "," . $exclude;
            } else {
                $oldRecurrspec['exdate'] = $exclude;
            }

            QueryUtils::sqlStatementThrowException(
                "UPDATE openemr_postcalendar_events SET pc_recurrspec = ? WHERE pc_eid = ?",
                [serialize($oldRecurrspec), $appt['pc_eid'] ?? '']
            );

            $noRecurrspec = [
                "event_repeat_freq" => "",
                "event_repeat_freq_type" => "",
                "event_repeat_on_num" => "1",
                "event_repeat_on_day" => "0",
                "event_repeat_on_freq" => "0",
                "exdate" => ""
            ];

            $locationspecs = [
                "event_location" => "",
                "event_street1" => "",
                "event_street2" => "",
                "event_city" => "",
                "event_state" => "",
                "event_postal" => ""
            ];
            $locationspec = serialize($locationspecs);

            $sql = "INSERT INTO openemr_postcalendar_events ( " .
            "pc_catid, pc_multiple, pc_aid, pc_pid, pc_gid, pc_title, " .
            "pc_time, " .
            "pc_hometext, pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
            "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
            "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility," .
            "pc_billing_location,pc_room " .
            ") VALUES (?,?,?,?,?,?,NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?,1,1,?,?,?)";

            // @phpstan-ignore-next-line
            $pc_eid = sqlInsert($sql, [
                $appt['pc_catid'] ?? '', $appt['pc_multiple'] ?? '', $appt['pc_aid'] ?? '', 
                $appt['pc_pid'] ?? '', $appt['pc_gid'] ?? '', $appt['pc_title'] ?? '',
                $appt['pc_hometext'] ?? '', $appt['pc_informant'] ?? '', $selected_date, 
                "0000-00-00", $appt['pc_duration'] ?? '', '0',
                serialize($noRecurrspec), $appt['pc_startTime'] ?? '', 
                $appt['pc_endTime'] ?? '', $appt['pc_alldayevent'] ?? '',
                $appt['pc_apptstatus'] ?? '', $appt['pc_prefcatid'] ?? '', 
                $locationspec, (int)($appt['pc_facility'] ?? 0),
                (int)($appt['pc_billing_facility'] ?? 0), $appt['pc_room'] ?? ''
            ]);

            $datetime = date("Y-m-d H:i:s");
            // @phpstan-ignore-next-line
            sqlInsert(
                "INSERT INTO `patient_tracker` " .
                "`date`, `apptdate`, `appttime`, `eid`, `pid`, `original_user`, `encounter`, `lastseq`) " .
                "VALUES (?,?,?,?,?,'MedEx','0','1')",
                [$datetime, $selected_date, $appt['pc_startTime'] ?? '', $pc_eid, $appt['pc_pid'] ?? '']
            );
        }

        return count($hits);
    }

    /**
     * Calculate recurring event dates
     *
     * @param array<string,mixed> $event
     * @param string $start_date
     * @param string $stop_date
     * @return array<int,string>
     */
    public function calculateEvents(array $event, string $start_date, string $stop_date): array
    {
        $data = [];
        $pc_recurrtype = (int)($event['pc_recurrtype'] ?? 0);

        switch ($pc_recurrtype) {
            case 0:
                $data[] = $event['pc_eventDate'] ?? '';
                break;

            case 1:
            case 3:
                $event_recurrspec = @unserialize($event['pc_recurrspec'] ?? '', ['allowed_classes' => false]);
                $rfreq = $event_recurrspec['event_repeat_freq'] ?? 1;
                $rtype = $event_recurrspec['event_repeat_freq_type'] ?? 0;
                $exdate = $event_recurrspec['exdate'] ?? '';
                
                [$ny, $nm, $nd] = explode('-', (string)($event['pc_eventDate'] ?? ''));
                $occurence = $event['pc_eventDate'] ?? '';

                while (strtotime((string)$occurence) < strtotime((string)$start_date)) {
                    $occurence = $this->__increment($nd, $nm, $ny, $rfreq, $rtype);
                    [$ny, $nm, $nd] = explode('-', (string)$occurence);
                }

                while ($occurence <= $stop_date) {
                    $excluded = false;
                    if (!empty($exdate)) {
                        foreach (explode(",", (string)$exdate) as $exception) {
                            if (preg_replace("/-/", "", (string)$occurence) == $exception) {
                                $excluded = true;
                            }
                        }
                    }

                    if ($excluded == false) {
                        $data[] = $occurence;
                    }
                    $occurence = $this->__increment($nd, $nm, $ny, $rfreq, $rtype);
                    [$ny, $nm, $nd] = explode('-', (string)$occurence);
                }
                break;
        }

        return $data;
    }

    /**
     * Increment date for recurring calculations
     *
     * @param int|string $d
     * @param int|string $m
     * @param int|string $y
     * @param int $f
     * @param int $t
     * @return string
     */
    private function __increment(int|string $d, int|string $m, int|string $y, int $f, int $t): string
    {
        define('REPEAT_EVERY_DAY', 0);
        define('REPEAT_EVERY_WEEK', 1);
        define('REPEAT_EVERY_MONTH', 2);
        define('REPEAT_EVERY_YEAR', 3);
        define('REPEAT_EVERY_WORK_DAY', 4);

        if ($t == REPEAT_EVERY_DAY) {
            return date('Y-m-d', mktime(0, 0, 0, (int)$m, ((int)$d + $f), (int)$y));
        } elseif ($t == REPEAT_EVERY_WEEK) {
            return date('Y-m-d', mktime(0, 0, 0, (int)$m, ((int)$d + (7 * $f)), (int)$y));
        } elseif ($t == REPEAT_EVERY_MONTH) {
            return date('Y-m-d', mktime(0, 0, 0, ((int)$m + $f), (int)$d, (int)$y));
        } elseif ($t == REPEAT_EVERY_YEAR) {
            return date('Y-m-d', mktime(0, 0, 0, (int)$m, (int)$d, ((int)$y + $f)));
        }
        
        return date('Y-m-d');
    }

    /**
     * Recursive array search
     *
     * @param string $needle
     * @param array<mixed> $haystack
     * @return bool
     */
    private function recursive_array_search(string $needle, array $haystack): bool
    {
        foreach ($haystack as $key => $value) {
            if (
                $needle === $value ||
                (is_array($value) && $this->recursive_array_search($needle, $value) !== false)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Delete completed recalls from MedEx
     *
     * @param string $token
     * @param array<mixed> $data
     * @return array<string,mixed>|false
     */
    private function process_deletes(string $token, array $data): array|false
    {
        $this->curl->setUrl($this->medEx->getUrl('custom/remRecalls&token=' . $token));
        $this->curl->setData(['recalls' => $data]);

        try {
            $this->curl->makeRequest();
        } catch (\Exception $e) {
            error_log("MedEx process_deletes failed: " . $e->getMessage());
            throw $e;
        }

        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            return $response;
        } elseif (isset($response['error'])) {
            $this->lastError = $response['error'];
        }

        return false;
    }

    /**
     * Process appointments/recalls for MedEx
     *
     * @param string $token
     * @param array<mixed> $appts
     * @return array<string,mixed>|false
     */
    private function process(string $token, array $appts): array|false
    {
        if (empty($appts)) {
            throw new InvalidDataException("You have no appointments that need processing at this time.");
        }

        $data = [];
        foreach ($appts as $appt) {
            $data['appts'][] = $appt;
            
            QueryUtils::sqlStatementThrowException(
                "UPDATE medex_outgoing SET msg_reply=?, msg_extra_text=?, msg_date=NOW()
                 WHERE msg_pc_eid=? AND campaign_uid=? AND msg_type=? AND msg_reply='To Send'",
                [
                    $appt['reply'] ?? '',
                    $appt['extra'] ?? '',
                    $appt['pc_eid'] ?? '',
                    $appt['C_UID'] ?? '',
                    $appt['M_type'] ?? ''
                ]
            );

            if (count($data['appts']) > 100) {
                $this->sendBatch($token, $data);
                $data = [];
                sleep(1);
            }
        }

        if (!empty($data['appts'])) {
            return $this->sendBatch($token, $data);
        }

        return false;
    }

    /**
     * Send batch to MedEx
     *
     * @param string $token
     * @param array<string,mixed> $data
     * @return array<string,mixed>|false
     */
    private function sendBatch(string $token, array $data): array|false
    {
        $this->curl->setUrl($this->medEx->getUrl('custom/loadAppts&token=' . $token));
        $this->curl->setData($data);

        try {
            $this->curl->makeRequest();
        } catch (\Exception $e) {
            error_log("MedEx sendBatch failed: " . $e->getMessage());
            throw $e;
        }

        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            return $response;
        } elseif (isset($response['error'])) {
            $this->lastError = $response['error'];
        }

        return false;
    }

    /**
     * Save recall
     *
     * @param array<string,mixed> $saved
     */
    public function save_recall(array $saved): void
    {
        $this->delete_Recall();
        $mysqldate = \DateToYYYYMMDD($_REQUEST['form_recall_date'] ?? '');
        
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO medex_recalls (r_pid,r_reason,r_eventDate,r_provider,r_facility)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY
             UPDATE r_reason=?, r_eventDate=?, r_provider=?,r_facility=?",
            [
                $_REQUEST['new_pid'] ?? '', $_REQUEST['new_reason'] ?? '', $mysqldate,
                $_REQUEST['new_provider'] ?? '', $_REQUEST['new_facility'] ?? '',
                $_REQUEST['new_reason'] ?? '', $mysqldate,
                $_REQUEST['new_provider'] ?? '', $_REQUEST['new_facility'] ?? ''
            ]
        );

        QueryUtils::sqlStatementThrowException(
            "UPDATE patient_data
             SET phone_home=?,phone_cell=?,email=?,
                 hipaa_allowemail=?,hipaa_voice=?,hipaa_allowsms=?,
                 street=?,postal_code=?,city=?,state=?
             WHERE pid=?",
            [
                $_REQUEST['new_phone_home'] ?? '', $_REQUEST['new_phone_cell'] ?? '', $_REQUEST['new_email'] ?? '',
                $_REQUEST['new_email_allow'] ?? '', $_REQUEST['new_voice'] ?? '', $_REQUEST['new_allowsms'] ?? '',
                $_REQUEST['new_address'] ?? '', $_REQUEST['new_postal_code'] ?? '', $_REQUEST['new_city'] ?? '', 
                $_REQUEST['new_state'] ?? '', $_REQUEST['new_pid'] ?? ''
            ]
        );
    }

    /**
     * Delete recall
     */
    public function delete_Recall(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM medex_recalls WHERE r_pid=? OR r_ID=?",
            [$_POST['pid'] ?? '', $_POST['r_ID'] ?? '']
        );

        QueryUtils::sqlStatementThrowException(
            "DELETE FROM medex_outgoing WHERE msg_pc_eid = ?",
            ['recall_' . ($_POST['pid'] ?? '')]
        );
    }

    /**
     * Get patient age
     *
     * @param string $dob
     * @param string $asof
     * @return int
     */
    public function getAge(string $dob, string $asof = ''): int
    {
        if (empty($asof)) {
            $asof = date('Y-m-d');
        }
        $a1 = explode('-', substr((string)$dob, 0, 10));
        $a2 = explode('-', substr((string)$asof, 0, 10));
        $age = (int)$a2[0] - (int)$a1[0];
        if ((int)$a2[1] < (int)$a1[1] || ((int)$a2[1] == (int)$a1[1] && (int)$a2[2] < (int)$a1[2])) {
            --$age;
        }
        return $age;
    }
}
