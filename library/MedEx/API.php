<?php

/**
 * /library/MedEx/API.php
 *
 * @package MedEx
 * @author MedEx <support@MedExBank.com>
 * @link http://www.MedExBank.com
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace MedExApi;

use OpenEMR\Services\VersionService;

error_reporting(0);

class CurlRequest
{
    private $url;
    private $postData = array();
    private $cookies = array();
    private $response = '';
    private $handle;
    private $sessionFile;

    public function __construct($sessionFile)
    {
        $this->sessionFile = $sessionFile;
        $this->restoreSession();
    }

    private function restoreSession()
    {
        if (file_exists($this->sessionFile)) {
            $this->cookies = json_decode(file_get_contents($this->sessionFile), true);
        }
    }

    public function makeRequest()
    {
        $this->handle = curl_init($this->url);

        curl_setopt($this->handle, CURLOPT_VERBOSE, 0);
        curl_setopt($this->handle, CURLOPT_HEADER, true);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_POST, true);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, http_build_query($this->postData));
        if (!empty($this->cookies)) {
            curl_setopt($this->handle, CURLOPT_COOKIE, $this->getCookies());
        }

        $this->response = curl_exec($this->handle);
        $header_size = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);
        $headers = substr($this->response, 0, $header_size);
        $this->response = substr($this->response, $header_size);

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers, $matches);
        $cookies = $matches[1];
        foreach ($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = array_shift($parts);
            $value = implode('=', $parts);
            $this->cookies[$name] = $value;
        }
        curl_close($this->handle);
        $this->saveSession();
    }

    private function getCookies()
    {
        $cookies = array();
        foreach ($this->cookies as $name => $value) {
            $cookies[] = $name . '=' . $value;
        }
        return implode('; ', $cookies);
    }

    private function saveSession()
    {
        if (empty($this->sessionFile)) {
            return;
        }

        if (!file_exists(dirname($this->sessionFile))) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            mkdir(dirname($this->sessionFile, 0755, true));
        }

        file_put_contents($this->sessionFile, json_encode($this->cookies));
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }
    public function setData($postData)
    {
        $this->postData = $postData; }
    public function getResponse()
    {
        return json_decode($this->response, true);  }
    public function getRawResponse()
    {
        return $this->response; }
}

class Base
{
    protected $MedEx;
    protected $curl;

    public function __construct($MedEx)
    {
        $this->MedEx = $MedEx;
        $this->curl = $MedEx->curl;
    }
}

class Practice extends Base
{
    public function sync($token)
    {
        global $GLOBALS;
        $fields2 = array();
        $fields3 = array();
        $callback = "https://" . $GLOBALS['_SERVER']['SERVER_NAME'] . $GLOBALS['_SERVER']['PHP_SELF'];
        $callback = str_replace('ajax/execute_background_services.php', 'MedEx/MedEx.php', $callback);
        $fields2['callback_url'] = $callback;
        $sqlQuery = "SELECT * FROM medex_prefs";
        $my_status = sqlQuery($sqlQuery);
        $providers = explode('|', $my_status['ME_providers']);
        foreach ($providers as $provider) {
            $runQuery = "SELECT * FROM users WHERE id=?";
            $ures = sqlStatement($runQuery, array($provider));
            while ($urow = sqlFetchArray($ures)) {
                $fields2['providers'][] = $urow;
            }
        }
        $facilities = explode('|', $my_status['ME_facilities']);
        $runQuery = "SELECT * FROM facility WHERE service_location='1'";
        $ures = sqlStatement($runQuery);
        while ($urow = sqlFetchArray($ures)) {
            if (in_array($urow['id'], $facilities)) {
                $urow['messages_active'] = '1';
                $fields2['facilities'][] = $urow;
            }
        }
        $runQuery = "SELECT pc_catid, pc_catname, pc_catdesc, pc_catcolor, pc_seq
                     FROM openemr_postcalendar_categories WHERE pc_active = 1 AND pc_cattype='0' ORDER BY pc_catid";
        $ures = sqlStatement($runQuery);
        while ($urow = sqlFetchArray($ures)) {
            $fields2['categories'][] = $urow;
        }
        $runQuery = "SELECT * FROM `list_options` WHERE `list_id` LIKE 'apptstat' AND activity='1'";
        $ures = sqlStatement($runQuery);
        while ($urow = sqlFetchArray($ures)) {
            $fields2['apptstats'][] = $urow;
        }
        $runQuery = "SELECT option_id FROM list_options WHERE toggle_setting_2='1' AND list_id='apptstat' AND activity='1'";
        $ures = sqlStatement($runQuery);
        while ($urow = sqlFetchArray($ures)) {
            $fields2['checkedOut'][] = $urow;
        }
        $sql = "SELECT * FROM `clinical_rules`,`list_options`,`rule_action`,`rule_action_item`
                WHERE
                `clinical_rules`.`pid`=0 AND
                `clinical_rules`.`patient_reminder_flag` = 1 AND
                `clinical_rules`.id = `list_options`.option_id AND
                `clinical_rules`.id = `rule_action`.id AND
                `list_options`.option_id=`clinical_rules`.id AND
                `rule_action`.category =`rule_action_item`.category AND
                `rule_action`.item =`rule_action_item`.item ";

        $ures = sqlStatementCdrEngine($sql);
        while ($urow = sqlFetchArray($ures)) {
            $fields2['clinical_reminders'][] = $urow;
        }

        $data = array($fields2);
        if (!is_array($data)) {
         //   return false; //throw new InvalidProductException('Invalid practice information');
        }
        $this->curl->setUrl($this->MedEx->getUrl('custom/addpractice&token=' . $token));
        $this->curl->setData($fields2);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        $sql = "SELECT * FROM medex_outgoing WHERE msg_pc_eid != 'recall_%' AND msg_reply LIKE 'To Send'";
        $test = sqlStatement($sql);
        while ($result1 = sqlFetchArray($test)) {
            $query  = "SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?";
            $test2 = sqlStatement($query, array($result1['msg_pc_eid']));
            $result2 = sqlFetchArray($test2);
            //for custom installs, insert custom apptstatus here that mean appt is not happening/changed
            if (
                $result2['pc_apptstatus'] == '*' ||  //confirmed
                $result2['pc_apptstatus'] == '%' ||  //cancelled < 24hour
                $result2['pc_apptstatus'] == 'x'
            ) { //cancelled
                $sqlUPDATE = "UPDATE medex_outgoing SET msg_reply = 'DONE',msg_extra_text=? WHERE msg_uid = ?";
                sqlQuery($sqlUPDATE, array($result2['pc_apptstatus'],$result2['msg_uid']));
                $tell_MedEx['DELETE_MSG'][] = $result1['msg_pc_eid'];
            }
        }

        $sql = "SELECT * FROM medex_outgoing WHERE msg_pc_eid LIKE 'recall_%' GROUP BY msg_pc_eid";
        $result = sqlStatement($sql);
        while ($row = sqlFetchArray($result)) {
            $pid = trim($row['msg_pc_eid'], "recall_");
            $query  = "SELECT pc_eid FROM openemr_postcalendar_events WHERE (pc_eventDate > CURDATE()) AND pc_pid=?";
            $test3 = sqlStatement($query, array($pid));
            $result3 = sqlFetchArray($test3);
            if ($result3) {
                $sqlUPDATE = "UPDATE medex_outgoing SET msg_reply = 'SCHEDULED', msg_extra_text=? WHERE msg_uid = ?";
                sqlQuery($sqlUPDATE, array($result3['pc_eid'],$result2['msg_uid']));
                $tell_MedEx['DELETE_MSG'][] = $row['msg_pc_eid'];
            }
        }

        while ($urow = sqlFetchArray($my_status)) {
            $fields3['MedEx_lastupdated']   = $urow['MedEx_lastupdated'];
            $fields3['ME_providers']        = $urow['ME_providers'];
        }
        $this->curl->setUrl($this->MedEx->getUrl('custom/sync_responses&token=' . $token . '&id=' . $urow['MedEx_id']));
        $this->curl->setData($fields3);
        $this->curl->makeRequest();
        $responses = $this->curl->getResponse();

        foreach ($responses['messages'] as $data) {
            $data['msg_extra'] = $data['msg_extra'] ?: '';
            $sqlQuery = "SELECT * FROM medex_outgoing WHERE medex_uid=?";
            $checker = sqlStatement($sqlQuery, array($data['msg_uid']));
            if (sqlNumRows($checker) == '0') {
                $this->MedEx->callback->receive($data);
            }
        }
        $sqlUPDATE = "UPDATE medex_prefs SET MedEx_lastupdated=utc_timestamp()";
        sqlStatement($sqlUPDATE);
        if ($tell_MedEx['DELETE_MSG']) {
            $this->curl->setUrl($this->MedEx->getUrl('custom/remMessaging&token=' . $token . '&id=' . $urow['MedEx_id']));
            $this->curl->setData($tell_MedEx['DELETE_MSG']);
            $this->curl->makeRequest();
            $response = $this->curl->getResponse();
        }
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
}

class Campaign extends Base
{
    public function events($token)
    {
        $info = array();
        $query = "SELECT * FROM medex_prefs";
        $info = sqlFetchArray(sqlStatement($query));

        if (
            empty($info) ||
            empty($info['ME_username']) ||
            empty($info['ME_api_key']) ||
            empty($info['MedEx_id'])
        ) {
            return false;
        }
        $results = json_decode($info['status'], true);
        return $results['status']['campaigns'];
    }
}

class Events extends Base
{
    public function generate($token, $events)
    {
        global $info;
        if (empty($events)) {
            return false; //throw new InvalidDataException("You have no Campaign Events on MedEx at this time.");
        }
        $appt3 = array();
        $count_appts = 0;
        $count_recalls = 0;
        $count_recurrents = 0;
        $count_announcements = 0;
        $count_surveys = 0;
        $count_clinical_reminders = 0;
        $count_gogreen = 0;

        $sqlQuery = "SELECT * FROM medex_icons";
        $result = sqlStatement($sqlQuery);
        while ($icons = sqlFetchArray($result)) {
            $title = preg_match('/title=\"(.*)\"/', $icons['i_html']);
            $xl_title = xla($title);
            $icons['i_html'] = str_replace($title, $xl_title, $icons['i_html']);
            $icon[$icons['msg_type']][$icons['msg_status']] = $icons['i_html'];
        }
        $sql2 = "SELECT * FROM medex_prefs";
        $prefs = sqlQuery($sql2);

        foreach ($events as $event) {
            $escClause = [];
            $escapedArr = [];
            $build_langs = '';
            $target_lang = '';
            $no_dupes = '';
            if (($event['E_language'] > '') && ($event['E_language'] != "all")) {
                $langs = explode("|", $event['E_language']);
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

            if ($event['M_group'] == 'REMINDER') {
                if ($event['time_order'] > '0') {
                    $interval = "+";
                    //NOTE IF you have customized the pc_appstatus flags, you need to adjust them here too.
                    if ($event['E_instructions'] == "stop") {   // ie. don't send this if it has been confirmed.
                        $appt_status = " and pc_apptstatus='-'";//we only look at future appts w/ apptstatus == NONE ='-'
                        // OR send anyway - unless appstatus is not cancelled, then it is no longer an appointment to confirm...
                    } elseif ($event['E_instructions'] == "always") {  //send anyway
                        $appt_status = " and pc_apptstatus != '%'
                                         and pc_apptstatus != 'x' ";
                    } else { //reminders are always or stop, that's it
                        $event['E_instructions'] = 'stop';
                        $appt_status = " and pc_apptstatus='-'";//we only look at future appts w/ apptstatus == NONE ='-'
                    }
                } else {
                     $interval = '-';
                     $appt_status = " and pc_apptstatus in (SELECT option_id from list_options where toggle_setting_2='1' and list_id='apptstat')
                                     and pc_apptstatus != '%'
                                     and pc_apptstatus != 'x' ";
                }
                //T_appt_stats = list of appstat(s) to restrict event to in a '|' separated list
                //Currently GoGreen only but added this for future flexibility in refining Appt Reminders too
                if ($event['T_appt_stats'] > '') {
                    $list = implode('|', $event['T_appt_stats']);
                    $appt_status = " and pc_appstatus in (" . $list . ")";
                }

                $timing = (int)$event['E_fire_time'] - 1;
                $today = date("l");
                if (($today == "Sunday") || ($today == "Saturday")) {
                    continue;
                }
                if ($today == "Friday") {
                    $timing2 = ($timing + 3) . ":0:1";
                } else {
                    $timing2 = ($timing + 1) . ":1:1";
                }

                if ($info['ME_hipaa_default_override'] != '1') {
                    $hipaa_override = " and hipaa_notice='YES' AND ";
                }

                if (!empty($prefs['ME_facilities'])) {
                    $places = str_replace("|", ",", $prefs['ME_facilities']);
                    $query  = "SELECT * FROM openemr_postcalendar_events AS cal
                                LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                                WHERE
                                " . $target_lang . "
                                " . $hipaa_override . "
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
                    $result = sqlStatement($query, $escapedArr);
                    while ($appt = sqlFetchArray($result)) {
                        if ($appt['e_is_subEvent_of'] > '0') {
                            $query = "select * from medex_outgoing where msg_uid=?";
                            $event2 = sqlStatement($query, array($appt['e_is_subEvent_of']));
                            if (new DateTime() < new DateTime($event2["msg_date"])) {
                                // if current time is less than Parent Appt, ignore this appt
                                continue;
                            }
                        }
                        list($response,$results) = $this->MedEx->checkModality($event, $appt, $icon);
                        if ($results == false) {
                            continue;
                        }
                        if (($appt['pc_recurrtype'] != '0') && ($interval == "+")) {
                            $recurrents = $this->addRecurrent($appt, $interval, $timing, $timing2, "REMINDER");
                            $count_recurrents += $recurrents;
                            continue;
                        }
                        $count_appts++;

                        $appt2 = [];
                        $appt2['pc_pid']        = $appt['pc_pid'];
                        $appt2['pc_eventDate']  = $appt['pc_eventDate'];
                        $appt2['pc_startTime']  = $appt['pc_startTime'];
                        $appt2['pc_eid']        = $appt['pc_eid'];
                        $appt2['pc_aid']        = $appt['pc_aid'];
                        $appt2['e_reason']      = (!empty($appt['e_reason'])) ?: '';
                        $appt2['e_is_subEvent_of'] = (!empty($appt['e_is_subEvent_of'])) ?: "0";
                        $appt2['language']      = $appt['language'];
                        $appt2['pc_facility']   = $appt['pc_facility'];
                        $appt2['fname']         = $appt['fname'];
                        $appt2['lname']         = $appt['lname'];
                        $appt2['mname']         = $appt['mname'];
                        $appt2['street']        = $appt['street'];
                        $appt2['postal_code']   = $appt['postal_code'];
                        $appt2['city']          = $appt['city'];
                        $appt2['state']         = $appt['state'];
                        $appt2['country_code']  = $appt['country_code'];
                        $appt2['phone_home']    = $appt['phone_home'];
                        $appt2['phone_cell']    = $appt['phone_cell'];
                        $appt2['email']         = $appt['email'];
                        $appt2['pc_apptstatus'] = $appt['pc_apptstatus'];

                        $appt2['C_UID']         = $event['C_UID'];
                        $appt2['reply']         = "To Send";
                        $appt2['extra']         = "QUEUED";
                        $appt2['status']        = "SENT";

                        $appt2['to']            = $results;
                        $appt3[] = $appt2;
                    }
                }
            } elseif ($event['M_group'] == 'RECALL') {
                if ($event['time_order'] > '0') {
                    $interval = "+";
                } else {
                    $interval = '-';
                }
                $timing = $event['E_fire_time'];

                $query  = "SELECT * FROM medex_recalls AS recall
                            LEFT JOIN patient_data AS pat ON recall.r_pid=pat.pid
                            WHERE (recall.r_eventDate < CURDATE() " . $interval . " INTERVAL " . $timing . " DAY)
                            ORDER BY recall.r_eventDate";
                $result = sqlStatement($query);

                while ($recall = sqlFetchArray($result)) {
                    list($response,$results) = $this->MedEx->checkModality($event, $recall, $icon);
                    if ($results == false) {
                        continue;
                    }
                    $show = $this->MedEx->display->show_progress_recall($recall, $event);
                    if ($show['DONE'] == '1') {
                        $RECALLS_completed[] = $recall;
                        continue;
                    }
                    if ($show['status'] == "reddish") {
                        continue;
                    }

                    if (strtotime($recall['r_eventDate']) < mktime(0, 0, 0)) {
                        if ($this->recursive_array_search("recall_" . $recall['r_pid'], $appt3)) {
                            continue;
                        }
                    }

                    $count_recalls++;
                    $recall2 = array();
                    $recall2['pc_pid']        = $recall['r_pid'];
                    $recall2['pc_eventDate']  = $recall['r_eventDate'];
                    $recall2['pc_startTime']  = '10:42:00';
                    $recall2['pc_eid']        = "recall_" . $recall['r_pid'];
                    $recall2['pc_aid']        = $recall['r_provider'];
                    $recall2['e_is_subEvent_of'] = "0";
                    $recall2['language']      = $recall['language'];
                    $recall2['pc_facility']   = $recall['r_facility'];
                    $recall2['fname']         = $recall['fname'];
                    $recall2['lname']         = $recall['lname'];
                    $recall2['mname']         = $recall['mname'];
                    $recall2['street']        = $recall['street'];
                    $recall2['postal_code']   = $recall['postal_code'];
                    $recall2['city']          = $recall['city'];
                    $recall2['state']         = $recall['state'];
                    $recall2['country_code']  = $recall['country_code'];
                    $recall2['phone_home']    = $recall['phone_home'];
                    $recall2['phone_cell']    = $recall['phone_cell'];
                    $recall2['email']         = $recall['email'];
                    $recall2['C_UID']         = $event['C_UID'];
                    $recall2['reply']         = "To Send";
                    $recall2['extra']         = "QUEUED";
                    $recall2['status']        = "SENT";
                    $recall2['to']            = $results;

                    $appt3[] = $recall2;
                }
            } elseif ($event['M_group'] == 'ANNOUNCE') {
                if (empty($event['start_date'])) {
                    continue;
                }
                $today = strtotime(date('Y-m-d'));
                $start = strtotime($event['appts_start']);

                if ($today < $start) {
                    continue;
                }
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
                if (!empty($event['appt_stats'])) {
                    $prepare_me = '';
                    $appt_stats = explode('|', $event['appt_stats']);
                    foreach ($appt_stats as $appt_stat) {
                        $prepare_me .= "?,";
                        $escapedArr[] = $appt_stat;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $appt_status = " AND cal.pc_apptstatus in (" . $prepare_me . ") ";
                } else {
                    $appt_status = '';
                }

                if (!empty($event['providers'])) {
                    $prepare_me = '';
                    $providers = explode('|', $event['providers']);
                    foreach ($providers as $provider) {
                        $prepare_me .= "?,";
                        $escapedArr[] = $provider;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $providers = " AND cal.pc_aid in (" . $prepare_me . ") ";
                } else {
                    $providers = '';
                }

                if (!empty($event['facilities'])) {
                    $prepare_me = '';
                    $facilities = explode('|', $event['facilities']);
                    foreach ($facilities as $facility) {
                        $prepare_me .= "?,";
                        $escapedArr[] = $facility;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $places = " AND cal.pc_facility in (" . $prepare_me . ") ";
                } else {
                    $places = '';
                }

                if (!empty($event['visit_types'])) {
                    $prepare_me = '';
                    $visit_types = explode('|', $event['visit_types']);
                    foreach ($visit_types as $visit_type) {
                        $prepare_me .= "?,";
                        $escapedArr[] = $visit_type;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $visit_types = " AND cal.pc_catid in (" . $prepare_me . ") ";
                } else {
                    $visit_types = '';
                }

                $sql_ANNOUNCE = "SELECT * FROM openemr_postcalendar_events AS cal
                            LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                            WHERE " . $target_dates . "
                                " . $appt_status . "
                                " . $providers . "
                                " . $places . "
                                " . $visit_types . "
                            ORDER BY pc_eventDate,pc_startTime";
                $result = sqlStatement($sql_ANNOUNCE, $escapedArr);
                while ($appt = sqlFetchArray($result)) {
                    list($response,$results) = $this->MedEx->checkModality($event, $appt, $icon);
                    if ($results == false) {
                        continue;
                    }
                    if ($appt['pc_recurrtype'] != '0') {
                        $recurrents = $this->addRecurrent($appt, "+", $event['appts_start'], $event['appts_end'], "ANNOUNCE");
                        $count_recurrents += $recurrents;
                        continue;
                    }
                    $count_announcements++;

                    $appt2 = array();
                    $appt2['pc_pid']        = $appt['pc_pid'];
                    $appt2['pc_eventDate']  = $appt['pc_eventDate'];
                    $appt2['pc_startTime']  = $appt['pc_startTime'];
                    $appt2['pc_eid']        = $event['C_UID'] . '_' . $appt['pc_eid'];
                    $appt2['pc_aid']        = $appt['pc_aid'];
                    $appt2['e_reason']      = (!empty($appt['e_reason'])) ?: '';
                    $appt2['e_is_subEvent_of'] = (!empty($appt['e_is_subEvent_of'])) ?: "0";
                    $appt2['language']      = $appt['language'];
                    $appt2['pc_facility']   = $appt['pc_facility'];
                    $appt2['fname']         = $appt['fname'];
                    $appt2['lname']         = $appt['lname'];
                    $appt2['mname']         = $appt['mname'];
                    $appt2['street']        = $appt['street'];
                    $appt2['postal_code']   = $appt['postal_code'];
                    $appt2['city']          = $appt['city'];
                    $appt2['state']         = $appt['state'];
                    $appt2['country_code']  = $appt['country_code'];
                    $appt2['phone_home']    = $appt['phone_home'];
                    $appt2['phone_cell']    = $appt['phone_cell'];
                    $appt2['email']         = $appt['email'];
                    $appt2['e_apptstatus']  = $appt['pc_apptstatus'];
                    $appt2['C_UID']         = $event['C_UID'];

                    $appt2['reply']         = "To Send";
                    $appt2['extra']         = "QUEUED";
                    $appt2['status']        = "SENT";

                    $appt2['to']            = $results;
                    $appt3[] = $appt2;
                }
            } elseif ($event['M_group'] == 'SURVEY') {
                if (empty($event['timing'])) {
                    $event['timing'] = "180";
                }
                $escClause = [];
                // appts completed - this is defined by list_option->toggle_setting2=1 for Flow Board
                $appt_status = " and pc_apptstatus in (SELECT option_id from list_options where toggle_setting_2='1' and list_id='apptstat') ";
                if (!empty($event['appt_stats'])) {
                    foreach ($event['appt_stats'] as $stat) {
                        $escapedArr[] = $stat;
                        $escClause['Stat'] .= "?,";
                    }
                    rtrim($escClause['Stat'], ",");
                    $appt_status = " and pc_appstatus in (" . $escClause['Stat'] . ") ";
                }

                $facility_clause = '';
                if (!empty($event['facilities'])) {
                    foreach ($event['facilities'] as $fac) {
                        $escapedArr[] = $fac;
                        $escClause['Fac'] .= "?,";
                    }
                    rtrim($escClause['Fac'], ",");
                    $facility_clause = " AND cal.pc_facility in (" . $escClause['Fac'] . ") ";
                }
                $all_providers = explode('|', $prefs['ME_providers']);
                foreach ($event['survey'] as $k => $v) {
                    if (($v <= 0) || (empty($event['providers'])) || (!in_array($k, $all_providers))) {
                        continue;
                    }

                    $escapedArr[] = $k;
                    $query  = "SELECT * FROM openemr_postcalendar_events AS cal
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
                    $result = sqlStatement($query, $escapedArr);
                    while ($appt = sqlFetchArray($result)) {
                        list($response,$results) = $this->MedEx->checkModality($event, $appt, $icon);
                        if ($results == false) {
                            continue; //not happening - either not allowed or not possible
                        }
                        $appt2 = array();
                        $appt2['pc_pid']        = $appt['pc_pid'];
                        $appt2['pc_eventDate']  = $appt['pc_eventDate'];
                        $appt2['pc_startTime']  = $appt['pc_startTime'];
                        $appt2['pc_eid']        = $appt['pc_eid'];
                        $appt2['pc_aid']        = $appt['pc_aid'];
                        $appt2['e_reason']      = (!empty($appt['e_reason'])) ?: '';
                        $appt2['e_is_subEvent_of'] = (!empty($appt['e_is_subEvent_of'])) ?: "0";
                        $appt2['language']      = $appt['language'];
                        $appt2['pc_facility']   = $appt['pc_facility'];
                        $appt2['fname']         = $appt['fname'];
                        $appt2['lname']         = $appt['lname'];
                        $appt2['mname']         = $appt['mname'];
                        $appt2['street']        = $appt['street'];
                        $appt2['postal_code']   = $appt['postal_code'];
                        $appt2['city']          = $appt['city'];
                        $appt2['state']         = $appt['state'];
                        $appt2['country_code']  = $appt['country_code'];
                        $appt2['phone_home']    = $appt['phone_home'];
                        $appt2['phone_cell']    = $appt['phone_cell'];
                        $appt2['email']         = $appt['email'];
                        $appt2['pc_apptstatus'] = $appt['pc_apptstatus'];

                        $appt2['C_UID']         = $event['C_UID'];
                        $appt2['E_fire_time']   = $event['E_fire_time'];
                        $appt2['time_order']    = $event['time_order'];
                        $appt2['M_type']        = $event['M_type'];
                        $appt2['reply']         = "To Send";
                        $appt2['extra']         = "QUEUED";
                        $appt2['status']        = "SENT";

                        $appt2['to']            = $results;
                        $appt3[] = $appt2;
                        $count_surveys++;
                    }
                }
            } elseif ($event['M_group'] == 'CLINICAL_REMINDER') {
                $sql = "SELECT * FROM `patient_reminders`,`patient_data`
                              WHERE
                            `patient_reminders`.pid ='" . $event['PID'] . "' AND
                            `patient_reminders`.active='1' AND
                            `patient_reminders`.date_sent IS NULL AND
                            `patient_reminders`.pid=`patient_data`.pid
                              ORDER BY `due_status`, `date_created`";
                $ures = sqlStatementCdrEngine($sql);
                while ($urow = sqlFetchArray($ures)) {
                    list($response,$results) = $this->MedEx->checkModality($event, $urow, $icon);
                    if ($results == false) {
                        continue; //not happening - either not allowed or not possible
                    }
                    $fields2['clinical_reminders'][] = $urow;
                    $count_clinical_reminders++;
                }
            } elseif ($event['M_group'] == 'GOGREEN') {
                if (!empty($event['appt_stats'])) {
                    $prepare_me = '';
                    $no_fu = '';
                    if ($event['appt_stats'] == "?") {
                        $no_fu = $event['E_fire_time'];
                        $no_interval = "30";
                        $prepare_me .= "?,";
                        $escapedArr[] = "?";
                    } elseif ($event['appt_stats'] == "p") {
                        $no_fu = $event['E_fire_time'];
                        $no_interval = "365";
                        $prepare_me .= "?,";
                        $escapedArr[] = $event['appt_stats'];
                    } else {
                        $appt_stats = explode('|', $event['appt_stats']);
                        foreach ($appt_stats as $appt_stat) {
                            $prepare_me .= "?,";
                            $escapedArr[] = $appt_stat;
                        }
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $appt_status = " AND cal.pc_apptstatus in (" . $prepare_me . ") ";
                } else {
                    $appt_status = '';
                }

                if (!empty($event['providers'])) {
                    $prepare_me = '';
                    $providers = explode('|', $event['providers']);
                    foreach ($providers as $provider) {
                        $prepare_me .= "?,";
                        $escapedArr[] = $provider;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $providers = " AND cal.pc_aid in (" . $prepare_me . ") ";
                } else {
                    $providers = '';
                }

                if (!empty($event['facilities'])) {
                    $prepare_me = '';
                    $facilities = explode('|', $event['facilities']);
                    foreach ($facilities as $facility) {
                        $prepare_me .= "?,";
                        $escapedArr[] = $facility;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $places = " AND cal.pc_facility in (" . $prepare_me . ") ";
                } else {
                    $places = '';
                }

                if (!empty($event['visit_types'])) {
                    $prepare_me = '';
                    $visit_types = explode('|', $event['visit_types']);
                    foreach ($visit_types as $visit_type) {
                        $prepare_me .= "?,";
                        $escapedArr[] = $visit_type;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $visit_types = " AND cal.pc_catid in (" . $prepare_me . ") ";
                } else {
                    $visit_types = '';
                }

                $frequency = '';
                if ($event['E_instructions'] == 'once') {
                    $frequency = " AND cal.pc_pid NOT in (
                        SELECT msg_pid from medex_outgoing where
                            campaign_uid =?  and msg_date >= curdate() )";
                     $escapedArr[] = (int)$event['C_UID'];
                } else {
                    if ($event['E_instructions'] == 'yearly') {
                        $frequency = " AND cal.pc_pid NOT in (
                        SELECT msg_pid from medex_outgoing where
                            campaign_uid =? and
                            msg_date > curdate() - interval 1 year )";
                        $escapedArr[] =  (int)$event['C_UID'];
                    }
                }
                if ($event['E_instructions'] == 'all') {
                    $frequency = " AND cal.pc_eid NOT in (
                                SELECT DISTINCT msg_pc_eid from medex_outgoing where
                                    campaign_uid=? and
                                    msg_date > curdate() )
                                AND
                                    cal.pc_time >= NOW() - interval 6 hour ";
                     $escapedArr[] = $event['C_UID'];
                } else {
                    $no_dupes = " AND cal.pc_eid NOT IN (
                                SELECT DISTINCT msg_pc_eid from medex_outgoing where
                                campaign_uid=? and msg_date >= curdate() ) ";
                    $escapedArr[] = $event['C_UID'];
                }

                $target_dates = '';
                if ($event['E_timing'] == '5') {
                    $target_dates = " cal.pc_eventDate >= curdate()  ";
                } else {
                    if (!is_numeric($event['E_fire_time'])) { //this would be an error in building the event
                        $event['E_fire_time'] = '0';
                    }
                    $timing = (int)$event['E_fire_time'];
                    if (($event['E_timing'] == '1') || ($event['E_timing'] == '2')) {
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
                                )
                        ";

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
                    } else {
                        if (($event['E_timing'] == '3') || ($event['E_timing'] == '4')) {
                            $target_dates = "cal.pc_eventDate = curdate() - interval " . $timing . " day";
                            if ($today == "Monday") {
                                $timing2 = ($timing + 3);
                                $target_dates .= " AND cal.pc_eventDate <= curdate() - INTERVAL " . $timing . " DAY AND
                                              cal.pc_eventDate > (curdate() - INTERVAL '" . $timing2 . "' DAY) ";
                            }
                        }
                    }
                }
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
                    $result = sqlStatement($sql_GOGREEN, $escapedArr);
                } catch (\Exception $e) {
                    $this->MedEx->logging->log_this($sql_GOGREEN);
                    exit;
                }
                while ($appt = sqlFetchArray($result)) {
                    list($response,$results) = $this->MedEx->checkModality($event, $appt, $icon);
                    if ($results == false) {
                        continue; //not happening - either not allowed or not possible
                    }
                    if ($no_fu) {
                        $sql_NoFollowUp = "SELECT pc_pid FROM openemr_postcalendar_events WHERE
                            pc_pid = ? AND
                            pc_eventDate > ( ? + INTERVAL " . escape_limit($no_interval) . " DAY)";
                        $result = sqlQuery($sql_NoFollowUp, array($appt['pc_pid'], $appt['pc_eventDate']));
                        if (count($result) > '') {
                            continue;
                        }
                    }
                    if ($appt['pc_recurrtype'] != '0') {
                        $recurrents = $this->addRecurrent($appt, "+", $event['appts_start'], $event['appts_end'], "GOGREEN");
                        $count_recurrents += $recurrents;
                        continue;
                    }
                    $count_gogreen++;
                    $appt2 = array();
                    $appt2['pc_pid']        = $appt['pc_pid'];
                    $appt2['pc_eventDate']  = $appt['pc_eventDate'];
                    $appt2['pc_startTime']  = $appt['pc_startTime'];
                    $appt2['pc_eid']        = $appt['pc_eid'];
                    $appt2['pc_aid']        = $appt['pc_aid'];
                    $appt2['e_reason']      = (!empty($appt['e_reason'])) ?: '';
                    $appt2['e_is_subEvent_of'] = (!empty($appt['e_is_subEvent_of'])) ?: "0";
                    $appt2['language']      = $appt['language'];
                    $appt2['pc_facility']   = $appt['pc_facility'];
                    $appt2['fname']         = $appt['fname'];
                    $appt2['lname']         = $appt['lname'];
                    $appt2['mname']         = $appt['mname'];
                    $appt2['street']        = $appt['street'];
                    $appt2['postal_code']   = $appt['postal_code'];
                    $appt2['city']          = $appt['city'];
                    $appt2['state']         = $appt['state'];
                    $appt2['country_code']  = $appt['country_code'];
                    $appt2['phone_home']    = $appt['phone_home'];
                    $appt2['phone_cell']    = $appt['phone_cell'];
                    $appt2['email']         = $appt['email'];
                    $appt2['pc_apptstatus'] = $appt['pc_apptstatus'];

                    $appt2['C_UID']         = $event['C_UID'];
                    $appt2['reply']         = "To Send";
                    $appt2['extra']         = "QUEUED";
                    $appt2['status']        = "SENT";

                    $appt2['to']            = $results;
                    $appt3[] = $appt2;
                }
            }
        }
        if (!empty($RECALLS_completed)) {
            $deletes = $this->process_deletes($token, $RECALLS_completed);
        }

        if (!empty($appt3)) {
            $this->process($token, $appt3);
        }
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
 * This function will check recurring appt entries in calendar.
 * @param $appt
 * @param $result
 * @return array|bool
 */
    private function addRecurrent($appt, $interval, $timing, $timing2, $M_group = "REMINDER")
    {
        //get dates in this request
        if ($M_group == "REMINDER") {
            $start = explode(':', $timing);
            $end = explode(':', $timing2);
            $start_date = date('Y-m-d', strtotime($interval . $start[0] . ' day'));
            $stop_date = date('Y-m-d', strtotime($interval . $end[0] . ' day'));
        } else {
            $start_date = $timing;
            $stop_date = $timing2;
        }

        //foreach date between curdate + timing and curdate + timing2 excluding dates excluded in recurring
        $hits = $this->MedEx->events->calculateEvents($appt, $start_date, $stop_date);

        //any dates that match need to be spawned from recurrent and made to live on their own.
        $oldRecurrspec = unserialize($appt['pc_recurrspec'], ['allowed_classes' => false]);

        foreach ($hits as $selected_date) {
            $exclude = str_replace("-", "", $selected_date);

            if ($oldRecurrspec['exdate'] != "") {
                $oldRecurrspec['exdate'] .= "," . $exclude;
            } else {
                $oldRecurrspec['exdate'] .= $exclude;
            }
            // mod original event recur specs to exclude this date
            sqlStatement("UPDATE openemr_postcalendar_events SET pc_recurrspec = ? WHERE pc_eid = ?", array(serialize($oldRecurrspec),$appt['pc_eid']));
            // specify some special variables needed for the INSERT
            // no recurr specs, this is used for adding a new non-recurring event
            $noRecurrspec = array("event_repeat_freq" => "",
                        "event_repeat_freq_type" => "",
                        "event_repeat_on_num" => "1",
                        "event_repeat_on_day" => "0",
                        "event_repeat_on_freq" => "0",
                        "exdate" => ""
                    );
            // Useless garbage that we must save. Anon
            // - ok but why is it useless? RM 2018-11-05
            $locationspecs = array("event_location" => "",
                                "event_street1" => "",
                                "event_street2" => "",
                                "event_city" => "",
                                "event_state" => "",
                                "event_postal" => ""
                            );
            $locationspec = serialize($locationspecs);
            $args['duration'] = $appt['duration'];
            // this event is forced to NOT REPEAT
            $args['form_repeat'] = "0";
            $args['recurrspec'] = $noRecurrspec;
            $args['form_enddate'] = "0000-00-00";
            //$args['prefcatid'] = (int)$appt['prefcatid'];

            $sql = "INSERT INTO openemr_postcalendar_events ( " .
            "pc_catid, pc_multiple, pc_aid, pc_pid, pc_gid, pc_title, " .
            "pc_time, " .
            "pc_hometext, pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
            "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
            "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility," .
            "pc_billing_location,pc_room " .
            ") VALUES (?,?,?,?,?,?,NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?,1,1,?,?,?)";

            $pc_eid = sqlInsert($sql, array($appt['pc_catid'], $appt['pc_multiple'], $appt['pc_aid'], $appt['pc_pid'], $appt['pc_gid'], $appt['pc_title'],
            $appt['pc_hometext'], $appt['pc_informant'], $selected_date, $args['form_enddate'], $appt['pc_duration'], '0',
            serialize($noRecurrspec), $appt['pc_startTime'], $appt['pc_endTime'], $appt['pc_alldayevent'],
            $appt['pc_apptstatus'], $appt['pc_prefcatid'], $locationspec, (int)$appt['pc_facility'],
            (int)$appt['pc_billing_facility'], $appt['pc_room']));

            #Add a new tracker item for this appt.
            $datetime = date("Y-m-d H:i:s");
            sqlInsert(
                "INSERT INTO `patient_tracker` " .
                            "(`date`, `apptdate`, `appttime`, `eid`, `pid`, `original_user`, `encounter`, `lastseq`) " .
                            "VALUES (?,?,?,?,?,'MedEx','0','1')",
                array($datetime, $selected_date, $appt['pc_startTime'], $pc_eid, $appt['pc_pid'])
            );
        }
        return count($hits);
    }

    private function recursive_array_search($needle, $haystack)
    {
        foreach ($haystack as $key => $value) {
            $current_key = $key;
            if ($needle === $value or (is_array($value) && $this->recursive_array_search($needle, $value))) {
                return true; //$current_key;
            }
        }
        return false;
    }

/**
 *  This function deletes Recalls from MedEx when they are completed and no further processing is
 *   needed. They are in an array = $data.
 * @param $token
 * @param $data
 * @return bool
 */
    private function process_deletes($token, $data)
    {
        $this->curl->setUrl($this->MedEx->getUrl('custom/remRecalls&token=' . $token));
        $this->curl->setData($data);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            return $response;
        } elseif (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return false;
    }

/**
 *  This function processes appointments/recalls that meet the timimg requirements for a MedEx Campaign Event
 * @param $token
 * @param $appts
 * @return bool
 */
    private function process($token, $appts)
    {
        if (empty($appts)) {
            throw new InvalidDataException("You have no appointments that need processing at this time.");
        }
        $data = array();
        foreach ($appts as $appt) {
            $data['appts'][] = $appt;
            $sqlUPDATE = "UPDATE medex_outgoing SET msg_reply=?, msg_extra_text=?, msg_date=NOW()
                                WHERE msg_pc_eid=? AND campaign_uid=? AND msg_type=? AND msg_reply='To Send'";
            sqlQuery($sqlUPDATE, array($appt['reply'],$appt['extra'],$appt['pc_eid'],$appt['C_UID'], $appt['M_type']));
            if (count($data['appts']) > '100') {
                $this->curl->setUrl($this->MedEx->getUrl('custom/loadAppts&token=' . $token));
                $this->curl->setData($data);
                $this->curl->makeRequest();
                $this->curl->getResponse();
                $data       = array();
                sleep(1);
            }
        }
        $this->curl->setUrl($this->MedEx->getUrl('custom/loadAppts&token=' . $token));
        $this->curl->setData($data);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            return $response;
        } elseif (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return false;
    }

    public function calculateEvents($event, $start_date, $stop_date)
    {

          ///////////////////////////////////////////////////////////////////////
          // The following code is from the calculateEvents function in the    //
          // PostCalendar Module modified by epsdky and inserted here,         //
          // and modified some more for MedEx.                                 //
          ///////////////////////////////////////////////////////////////////////
        $data = array();
        switch ($event['pc_recurrtype']) {
            //not recurrent
            case '0':
                $data[] = $event;
                break;
            case '1':
            case '3':
                $event_recurrspec = @unserialize($event['pc_recurrspec'], ['allowed_classes' => false]);

                $rfreq = $event_recurrspec['event_repeat_freq'];
                $rtype = $event_recurrspec['event_repeat_freq_type'];
                $exdate = $event_recurrspec['exdate'];
                list($ny,$nm,$nd) = explode('-', $event['pc_eventDate']);
                $occurence = $event['pc_eventDate'];

                // prep work to start cooking...
                // ignore dates less than start_date
                while (strtotime($occurence) < strtotime($start_date)) {
                    // if the start date is later than the recur date start
                    // just go up a unit at a time until we hit start_date
                    $occurence =& $this->MedEx->events->__increment($nd, $nm, $ny, $rfreq, $rtype);
                    list($ny,$nm,$nd) = explode('-', $occurence);
                }
                //now we are cooking...
                while ($occurence <= $stop_date) {
                    $excluded = false;
                    if (isset($exdate)) {
                        foreach (explode(",", $exdate) as $exception) {
                            // occurrence format == yyyy-mm-dd
                            // exception format == yyyymmdd
                            if (preg_replace("/-/", "", $occurence) == $exception) {
                                $excluded = true;
                            }
                        }
                    }

                    if ($excluded == false) {
                        $data[] = $occurence;
                    }
                    $occurence =& $this->MedEx->events->__increment($nd, $nm, $ny, $rfreq, $rtype);
                    list($ny,$nm,$nd) = explode('-', $occurence);
                }
                break;

            case '2':
                $event_recurrspec = @unserialize($event['pc_recurrspec'], ['allowed_classes' => false]);

                if (checkEvent($event['pc_recurrtype'], $event_recurrspec)) {
                    break; }

                $rfreq = $event_recurrspec['event_repeat_on_freq'];
                $rnum  = $event_recurrspec['event_repeat_on_num'];
                $rday  = $event_recurrspec['event_repeat_on_day'];
                $exdate = $event_recurrspec['exdate'];

                list($ny,$nm,$nd) = explode('-', $event['pc_eventDate']);

                if (isset($event_recurrspec['rt2_pf_flag']) && $event_recurrspec['rt2_pf_flag']) {
                    $nd = 1;
                }

                $occurenceYm = "$ny-$nm"; // YYYY-mm
                $from_dateYm = substr($start_date, 0, 7); // YYYY-mm
                $stop_dateYm = substr($stop_date, 0, 7); // YYYY-mm

                // $nd will sometimes be 29, 30 or 31 and if used in the mktime functions below
                // a problem with overflow will occur so it is set to 1 to avoid this (for rt2
                // appointments set prior to fix $nd remains unchanged). This can be done since
                // $nd has no influence past the mktime functions.
                while ($occurenceYm < $from_dateYm) {
                    $occurenceYmX = date('Y-m-d', mktime(0, 0, 0, $nm + $rfreq, $nd, $ny));
                    list($ny,$nm,$nd) = explode('-', $occurenceYmX);
                    $occurenceYm = "$ny-$nm";
                }

                while ($occurenceYm <= $stop_dateYm) {
                    // (YYYY-mm)-dd
                    $dnum = $rnum;
                    do {
                        $occurence = Date_Calc::NWeekdayOfMonth($dnum--, $rday, $nm, $ny, $format = "%Y-%m-%d");
                    } while ($occurence === -1);

                    if ($occurence >= $start_date && $occurence <= $stop_date) {
                        $excluded = false;
                        if (isset($exdate)) {
                            foreach (explode(",", $exdate) as $exception) {
                                // occurrence format == yyyy-mm-dd
                                // exception format == yyyymmdd
                                if (preg_replace("/-/", "", $occurence) == $exception) {
                                    $excluded = true;
                                }
                            }
                        }

                        if ($excluded == false) {
                            $event['pc_eventDate'] = $occurence;
                            $event['pc_endDate'] = '0000-00-00';
                            $events2[] = $event;
                            $data[] = $event['pc_eventDate'];
                        }
                    }

                    $occurenceYmX = date('Y-m-d', mktime(0, 0, 0, $nm + $rfreq, $nd, $ny));
                    list($ny,$nm,$nd) = explode('-', $occurenceYmX);
                    $occurenceYm = "$ny-$nm";
                }
                break;
        }
        return $data;
    }

    private function &__increment($d, $m, $y, $f, $t)
    {
        define('REPEAT_EVERY_DAY', 0);
        define('REPEAT_EVERY_WEEK', 1);
        define('REPEAT_EVERY_MONTH', 2);
        define('REPEAT_EVERY_YEAR', 3);
        define('REPEAT_EVERY_WORK_DAY', 4);
        define('REPEAT_DAYS_EVERY_WEEK', 6);

        if ($t == REPEAT_EVERY_DAY) {
            return date('Y-m-d', mktime(0, 0, 0, $m, ($d + $f), $y));
        } elseif ($t == REPEAT_EVERY_WORK_DAY) {
            // a workday is defined as Mon,Tue,Wed,Thu,Fri
            // repeating on every or Nth work day means to not include
            // weekends (Sat/Sun) in the increment... tricky

            // ugh, a day-by-day loop seems necessary here, something where
            // we can check to see if the day is a Sat/Sun and increment
            // the frequency count so as to ignore the weekend. hmmmm....
            $orig_freq = $f;
            for ($daycount = 1; $daycount <= $orig_freq; $daycount++) {
                $nextWorkDOW = date('w', mktime(0, 0, 0, $m, ($d + $daycount), $y));
                if (is_weekend_day($nextWorkDOW)) {
                    $f++;
                }
            }

            // and finally make sure we haven't landed on a end week days
            // adjust as necessary
            $nextWorkDOW = date('w', mktime(0, 0, 0, $m, ($d + $f), $y));
            if (count($GLOBALS['weekend_days']) === 2) {
                if ($nextWorkDOW == $GLOBALS['weekend_days'][0]) {
                    $f += 2;
                } elseif ($nextWorkDOW == $GLOBALS['weekend_days'][1]) {
                     $f++;
                }
            } elseif (count($GLOBALS['weekend_days']) === 1 && $nextWorkDOW === $GLOBALS['weekend_days'][0]) {
                $f++;
            }
            return date('Y-m-d', mktime(0, 0, 0, $m, ($d + $f), $y));
        } elseif ($t == REPEAT_EVERY_WEEK) {
            return date('Y-m-d', mktime(0, 0, 0, $m, ($d + (7 * $f)), $y));
        } elseif ($t == REPEAT_EVERY_MONTH) {
            return date('Y-m-d', mktime(0, 0, 0, ($m + $f), $d, $y));
        } elseif ($t == REPEAT_EVERY_YEAR) {
            return date('Y-m-d', mktime(0, 0, 0, $m, $d, ($y + $f)));
        } elseif ($t == REPEAT_DAYS_EVERY_WEEK) {
            $old_appointment_date = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
            $next_appointment_date = getTheNextAppointment($old_appointment_date, $f);
            return $next_appointment_date;
        }
    }

    public function save_recall($saved)
    {
        $this->delete_Recall();
        $mysqldate = DateToYYYYMMDD($_REQUEST['form_recall_date']);
        $queryINS = "INSERT INTO medex_recalls (r_pid,r_reason,r_eventDate,r_provider,r_facility)
                        VALUES (?,?,?,?,?)
                        ON DUPLICATE KEY
                        UPDATE r_reason=?, r_eventDate=?, r_provider=?,r_facility=?";
        sqlStatement($queryINS, array($_REQUEST['new_pid'],$_REQUEST['new_reason'],$mysqldate,$_REQUEST['new_provider'],$_REQUEST['new_facility'],$_REQUEST['new_reason'],$mysqldate,$_REQUEST['new_provider'],$_REQUEST['new_facility']));
        $query = "UPDATE patient_data
                    SET phone_home=?,phone_cell=?,email=?,
                        hipaa_allowemail=?,hipaa_voice=?,hipaa_allowsms=?,
                        street=?,postal_code=?,city=?,state=?
                    WHERE pid=?";
        $sqlValues = array($_REQUEST['new_phone_home'],$_REQUEST['new_phone_cell'],$_REQUEST['new_email'],
                        $_REQUEST['new_email_allow'],$_REQUEST['new_voice'],$_REQUEST['new_allowsms'],
                        $_REQUEST['new_address'],$_REQUEST['new_postal_code'],$_REQUEST['new_city'],$_REQUEST['new_state'],
                        $_REQUEST['new_pid']);
        sqlStatement($query, $sqlValues);
        return;
    }

    public function delete_Recall()
    {
        $sqlQuery = "DELETE FROM medex_recalls WHERE r_pid=? OR r_ID=?";
        sqlStatement($sqlQuery, array($_POST['pid'],$_POST['r_ID']));

        $sqlDELETE = "DELETE FROM medex_outgoing WHERE msg_pc_eid = ?";
        sqlStatement($sqlDELETE, array('recall_' . $_POST['pid']));
    }

    public function getAge($dob, $asof = '')
    {
        if (empty($asof)) {
            $asof = date('Y-m-d');
        }
        $a1 = explode('-', substr($dob, 0, 10));
        $a2 = explode('-', substr($asof, 0, 10));
        $age = $a2[0] - $a1[0];
        if ($a2[1] < $a1[1] || ($a2[1] == $a1[1] && $a2[2] < $a1[2])) {
            --$age;
        }
        return $age;
    }

    private function getDatesInRecurring($appt, $interval, $start_days = '', $end_days = '')
    {
        $start = date('Y-m-d', strtotime($interval . $start_days . ' day'));
        $end = date('Y-m-d', strtotime($interval . $end_days . ' day'));
        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($start, 5, 2), substr($start, 8, 2), substr($start, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($end, 5, 2), substr($end, 8, 2), substr($end, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }
}

/**
 *  Process updates and message replies received from MedEx.
 *  Lets MedEx know if we did anything manually to a queued event.
 */
class Callback extends Base
{
    public function receive($data = '')
    {
        if ($data == '') {
            $data = $_POST;
        }
        if (empty($data['campaign_uid'])) {
          //  throw new InvalidDataException("There must be a Campaign to update...");
            $response['success'] = "No campaigns to process.";
        }
        if (!$data['patient_id']) {
            if ($data['e_pid']) {
                $data['patient_id'] = $data['e_pid'];
            } elseif ($data['pc_eid']) {
                $query = "SELECT * FROM openemr_postcalendar_events WHERE pc_eid=?";
                $patient = sqlFetchArray(sqlStatement($query, array($data['pc_eid'])));
                $data['patient_id'] = $patient['pid'];
            }
        }
        if ($data['patient_id']) {
            $sqlINSERT = "INSERT INTO medex_outgoing (msg_pc_eid, msg_pid, campaign_uid, msg_type, msg_reply, msg_extra_text, msg_date, medex_uid)
                            VALUES (?,?,?,?,?,?,utc_timestamp(),?)";
            if (!$data['M_type']) {
                $data['M_type'] = 'pending';
            }
            sqlQuery($sqlINSERT, array($data['pc_eid'],$data['patient_id'], $data['campaign_uid'], $data['M_type'],$data['msg_reply'],$data['msg_extra'],$data['msg_uid']));

            if ($data['msg_reply'] == "CONFIRMED") {
                $sqlUPDATE = "UPDATE openemr_postcalendar_events SET pc_apptstatus = ? WHERE pc_eid=?";
                sqlStatement($sqlUPDATE, array($data['msg_type'],$data['pc_eid']));
                $query = "SELECT * FROM patient_tracker WHERE eid=?";
                $tracker = sqlFetchArray(sqlStatement($query, array($data['pc_eid'])));
                if (!empty($tracker['id'])) {
                    sqlStatement(
                        "UPDATE `patient_tracker` SET  `lastseq` = ? WHERE eid=?",
                        array(($tracker['lastseq'] + 1),$data['pc_eid'])
                    );
                    $datetime = date("Y-m-d H:i:s");
                    sqlInsert(
                        "INSERT INTO `patient_tracker_element` " .
                            "(`pt_tracker_id`, `start_datetime`, `user`, `status`, `seq`) " .
                            "VALUES (?,?,?,?,?)",
                        array($tracker['id'],$datetime,'MedEx',$data['msg_type'],($tracker['lastseq'] + 1))
                    );
                }
            } elseif ($data['msg_reply'] == "CALL") {
                $sqlUPDATE = "UPDATE openemr_postcalendar_events SET pc_apptstatus = 'CALL' WHERE pc_eid=?";
                sqlQuery($sqlUPDATE, array($data['pc_eid']));
                //this requires attention.  Send up the FLAG!
                //$this->MedEx->logging->new_message($data);
            } elseif (($data['msg_type'] == "AVM") && ($data['msg_reply'] == "STOP")) {
                $sqlUPDATE = "UPDATE patient_data SET hipaa_voice = 'NO' WHERE pid=?";
                sqlQuery($sqlUPDATE, array($data['patient_id']));
            } elseif (($data['msg_type'] == "SMS") && ($data['msg_reply'] == "STOP")) {
                $sqlUPDATE = "UPDATE patient_data SET hipaa_allowsms = 'NO' WHERE pid=?";
                sqlQuery($sqlUPDATE, array($data['patient_id']));
            } elseif (($data['msg_type'] == "EMAIL") && ($data['msg_reply'] == "STOP")) {
                $sqlUPDATE = "UPDATE patient_data SET hipaa_allowemail = 'NO' WHERE pid=?";
                sqlQuery($sqlUPDATE, array($data['patient_id']));
            }
            if (($data['msg_reply'] == "SENT") || ($data['msg_reply'] == "READ")) {
                $sqlDELETE = "DELETE FROM medex_outgoing WHERE msg_pc_eid=? AND msg_reply='To Send'";
                sqlQuery($sqlDELETE, array($data['pc_eid']));
            }
            $response['comments']   = $data['pc_eid'] . " - " . $data['campaign_uid'] . " - " . $data['msg_type'] . " - " . $data['reply'] . " - " . $data['extra'];
            $response['pid']        = $data['patient_id'];
            $response['success']    = $data['msg_type'] . " reply";
        } else {
            $response['success']    = "completed";
        }
        return $response;
    }
}

class Logging extends base
{
    public function log_this($data)
    {
        //truly a debug function, that we will probably find handy to keep on end users' servers;)
        return;
        $log = "/tmp/medex.log" ;
        $std_log = fopen($log, 'a');
        $timed = date('Y-m-d H:i:s');
        fwrite($std_log, "**********************\nlibrary/MedEx/API.php fn log_this(data):  " . $timed . "\n");
        try {
            if (is_array($data)) {
                $dumper = print_r($data, true);
                foreach ($data as $key => $value) {
                    fputs($std_log, $key . ": " . $value . "\n");
                }
            } else {
                fputs($std_log, "\nDATA= " . $data . "\n");
            }
        } catch (\Exception $e) {
            fwrite($std_log, $e->getMessage() . "\n");
        }
        fclose($std_log);
        return true;
    }
}

class Display extends base
{
    public function navigation($logged_in)
    {
        global $setting_bootstrap_submenu;

        ?>
        <script>
        function toggle_menu() {
                var x = document.getElementById('hide_nav');
                if (x.style.display === 'none') {
                    $.post( "<?php echo $GLOBALS['webroot'] . "/interface/main/messages/messages.php"; ?>", {
                        'setting_bootstrap_submenu' : 'show',
                        success: function (data) {
                            x.style.display = 'block';
                        }
                        });

                } else {
                    $.post( "<?php echo $GLOBALS['webroot'] . "/interface/main/messages/messages.php"; ?>", {
                        'setting_bootstrap_submenu' : 'hide',
                        success: function (data) {
                            x.style.display = 'none';
                        }
                    });
                }
            $("#patient_caret").toggleClass('fa-caret-up').toggleClass('fa-caret-down');
            }

        function SMS_bot_list() {
            top.restoreSession();
            var myWindow = window.open('<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/messages.php?nomenu=1&go=SMS_bot&dir=back&show=new','SMS_bot', 'width=400,height=650');
            myWindow.focus();
            return false;
        }
        </script>
        <i class="fa fa-caret-<?php
        if ($setting_bootstrap_submenu == 'hide') {
            echo 'down';
        } else {
            echo 'up';
        } ?> menu_arrow" style="position:fixed;left:5px;top:10px;z-index:1200;" id="patient_caret" onclick='toggle_menu();' aria-hidden="true"></i>

        <div id="hide_nav" style="<?php if ($setting_bootstrap_submenu == 'hide') {
                echo "display:none;"; } ?>">

            <nav id="navbar_oe" class="navbar navbar-expand-sm p-0 pl-1" name="kiosk_hide" data-role="page banner navigation">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#oer-navbar-collapse-1" aria-controls="oer-navbar-collapse-1" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="oer-navbar-collapse-1">
                    <ul class="navbar-nav">
                        <?php if ($GLOBALS['medex_enable'] == '1') { ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link" data-toggle="dropdown" id="menu_dropdown_file" role="button" aria-expanded="true"><?php echo xlt("File"); ?> </a>
                                <ul class="bgcolor2 dropdown-menu" aria-labelledby="menu_dropdown_file">
                                    <?php if ($logged_in) { ?>
                                        <li id="menu_PREFERENCES"  name="menu_PREFERENCES" class=""><a class="dropdown-item" onclick="tabYourIt('prefs','main/messages/messages.php?go=Preferences');"><?php echo xlt("Preferences"); ?></a></li>
                                        <li id="icons" name="icons"><a class="dropdown-item" onclick="doRecallclick_edit('icons');"><?php echo xlt('Icon Legend'); ?></a></li>
                                        <?php
                                    } else {
                                        ?>
                                        <li id="menu_PREFERENCES"  name="menu_PREFERENCES" class="">
                                        <a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?go=setup&stage=1"><?php echo xlt("Setup MedEx"); ?></a></li>
                                    <?php } ?>
                                 </ul>
                            </li>
                        <?php } ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link" data-toggle="dropdown" id="menu_dropdown_msg" role="button" aria-expanded="true"><?php echo xlt("Messages"); ?> </a>
                            <ul class="bgcolor2 dropdown-menu" aria-labelledby="menu_dropdown_msg">
                                <li id="menu_new_msg"> <a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?showall=no&sortby=users.lname&sortorder=asc&begin=0&task=addnew&form_active=1"> <?php echo xlt("New Message"); ?></a></li>
                                <li class="dropdown-divider"></li>
                                <li id="menu_new_msg"> <a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?show_all=no&form_active=1"> <?php echo xlt("My Messages"); ?></a></li>
                                <li id="menu_all_msg"> <a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?show_all=yes&form_active=1"> <?php echo xlt("All Messages"); ?></a></li>
                                <li class="dropdown-divider"></li>
                                <li id="menu_active_msg"> <a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?show_all=yes&form_active=1"> <?php echo xlt("Active Messages"); ?></a></li>
                                <li id="menu_inactive_msg"> <a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?form_inactive=1"> <?php echo xlt("Inactive Messages"); ?></a></li>
                                <li id="menu_log_msg"> <a class="dropdown-item" onclick="openLogScreen();" > <?php echo xlt("Message Log"); ?></a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link" data-toggle="dropdown" id="menu_dropdown_recalls" role="button" aria-expanded="true"><?php echo xlt("Appt. Reminders"); ?></a>
                            <ul class="bgcolor2 dropdown-menu" aria-labelledby="menu_dropdown_recalls">
                            <?php
                            if ($GLOBALS['disable_calendar'] != '1') {  ?>
                                <li><a class="dropdown-item" id="BUTTON_ApRem_menu" onclick="tabYourIt('cal','main/main_info.php');"> <?php echo xlt("Calendar"); ?></a></li>
                                <li class="dropdown-divider"></li>
                                <?php
                            }
                            if ($GLOBALS['disable_pat_trkr'] != '1') {
                                ?>
                                <li id="menu_pend_recalls" name="menu_pend_recalls"> <a class="dropdown-item" id="BUTTON_pend_recalls_menu" onclick="tabYourIt('flb','patient_tracker/patient_tracker.php?skip_timeout_reset=1');"> <?php echo xlt("Flow Board"); ?></a></li>
                                <?php }
                            if ($logged_in) {
                                ?>
                                <li class="dropdown-divider"></li>
                                <li id="menu_pend_recalls" name="menu_pend_recalls"> <a href='https://medexbank.com/cart/upload/index.php?route=information/campaigns&g=rem' target="_medex" class='dropdown-item nowrap text-left' id="BUTTON_pend_recalls_menu"> <?php echo xlt("Reminder Campaigns"); ?></a></li>
                                    <?php
                            }
                            ?>
                            </ul>
                         </li>
                                <?php

                                if ($GLOBALS['disable_rcb'] != '1') { ?>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" data-toggle="dropdown" id="menu_dropdown_recalls" role="button" aria-expanded="true"><?php echo xlt("Patient Recalls"); ?> </a>
                                        <ul class="bgcolor2 dropdown-menu" aria-labelledby="menu_dropdown_recalls">
                                            <li id="menu_new_recall" name="menu_new_recall"> <a class="dropdown-item" id="BUTTON_new_recall_menu" onclick="tabYourIt('rcb','main/messages/messages.php?go=addRecall');"> <?php echo xlt("New Recall"); ?></a></li>
                                            <li id="menu_pend_recalls" name="menu_pend_recalls"> <a class="dropdown-item" onclick="goReminderRecall('Recalls');" id="BUTTON_pend_recalls_menu" href="#"> <?php echo xlt("Recall Board"); ?></a></li>
                                        <?php
                                        if ($logged_in) {
                                            ?>
                                            <li class="dropdown-divider"></li>
                                            <li id="menu_pend_recalls" name="menu_pend_recalls"> <a href='https://medexbank.com/cart/upload/index.php?route=information/campaigns&g=rec' target="_medex" class='dropdown-item nowrap text-left' id="BUTTON_pend_recalls_menu"> <?php echo xlt("Recall Campaigns"); ?></a></li>
                                            <?php
                                        }
                                        ?>
                                        </ul>
                                    </li>
                                    <?php
                                }

                                if ($logged_in) {
                                    if (!empty($logged_in['products']['ordered'])) {
                                        foreach ($logged_in['products']['ordered'] as $ordered) {
                                            echo $ordered['menu'];
                                        }
                                    }
                                }
                                ?>
                    </ul>
                </div>
            </nav>
        </div>
            <?php
            if ($GLOBALS['medex_enable'] == '1') {
                $error = $this->MedEx->getLastError();
                if (!empty($error['ip'])) {
                    ?>
                <div class="alert alert-danger text-center w-50" style="margin:30px auto 5px; font-size:0.9rem;">
                    <?php
                    echo $error['ip'];
                    ?>
                </div>
                    <?php
                }
            }
    }
    public function preferences($prefs = '')
    {
        global $logged_in;
        if (empty($prefs)) {
             $prefs = $this->MedEx->getPreferences();
        }
        ?>
        <div class="row">
            <div class="col-sm-12 text-center">
                <div class="showRecalls" id="show_recalls">
                    <div class="title">MedEx <?php echo xlt('Preferences'); ?></div>
                    <div name="div_response" id="div_response"><br />
                    </div>
                    <form action="#" name="save_prefs" id="save_prefs">
                            <div class="row">
                                <input type="hidden" name="go" id="go" value="Preferences" />
                                <div class="col-sm-5 div-center offset-sm-1" id="daform2">
                                    <div class="divTable2">
                                        <div class="divTableBody prefs">
                                            <div class="divTableRow">
                                                <div class="divTableCell divTableHeading">MedEx <?php echo xlt('Username'); ?></div>
                                                <div class="divTableCell indent20">
                                                    <?php echo $prefs['ME_username']; ?>
                                                </div>
                                            </div>
                                            <div class="divTableRow">
                                                <div class="divTableCell divTableHeading"><?php echo xlt('General'); ?></div>
                                                <div class="divTableCell indent20">
                                                    <input type="checkbox" class="update" name="ME_hipaa_default_override" id="ME_hipaa_default_override" value="1"
                                                    <?php
                                                    if ($prefs['ME_hipaa_default_override'] == '1') {
                                                        echo 'checked ="checked"';
                                                    }
                                                    ?> />
                                                    <label for="ME_hipaa_default_override" class="input-helper input-helper--checkbox"
                                                           data-toggle='tooltip'
                                                           data-placement='auto right'
                                                           title='<?php echo xla('Default'); ?>: "<?php echo xla('checked'); ?>".
                                                            <?php echo xla('When checked, messages are processed for patients with Patient Demographic Choice: "Hipaa Notice Received" set to "Unassigned" or "Yes". When unchecked, this choice must = "YES" to process the patient reminder. For patients with Choice ="No", Reminders will need to be processed manually.'); //or no translation... ?>'>
                                                            <?php echo xlt('Assume patients receive HIPAA policy'); ?>
                                                     </label><br />
                                                     <input type="checkbox" class="update" name="MSGS_default_yes" id="MSGS_default_yes" value="1" <?php if ($prefs['MSGS_default_yes'] == '1') {
                                                                echo "checked='checked'";} ?> /><label for="MSGS_default_yes" class="input-helper input-helper--checkbox" data-toggle="tooltip" data-placement="auto" title="<?php echo xla('Default: Checked. When checked, messages are processed for patients with Patient Demographic Choice (Phone/Text/Email) set to \'Unassigned\' or \'Yes\'. If this is unchecked, a given type of message can only be sent if its Demographic Choice = \'Yes\'.'); ?>">
                                                               <?php echo xlt('Assume patients permit Messaging'); ?></label>
                                                    </div>
                                                </div>
                                                <div class="divTableRow">
                                                    <div class="divTableCell divTableHeading"><?php echo xlt('Enable Facility'); ?></div>
                                                    <div class="divTableCell indent20">
                                                        <?php
                                                        $count = "1";
                                                        $query = "SELECT * FROM facility";
                                                        $result = sqlStatement($query);
                                                        while ($fac = sqlFetchArray($result)) {
                                                            $checked = "";
                                                            if ($prefs) {
                                                                $facs = explode('|', $prefs['ME_facilities']);
                                                                foreach ($facs as $place) {
                                                                    if ($place == $fac['id']) {
                                                                        $checked = 'checked ="checked"';
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        <input <?php echo $checked; ?> class="update" type="checkbox" name="facilities[]" id="facility_<?php echo attr($fac['id']); ?>" value="<?php echo attr($fac['id']); ?>" />
                                                        <label for="facility_<?php echo attr($fac['id']); ?>"><?php echo text($fac['name']); ?></label><br /><?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="divTableRow">
                                                    <div class="divTableCell divTableHeading"><?php echo xlt('Included Providers'); ?></div>
                                                    <div class="divTableCell indent20">
                                                    <?php
                                                    $count = "1";
                                                    $ures = sqlStatement("SELECT * FROM users WHERE authorized != 0 AND active = 1 ORDER BY lname, fname");
                                                    while ($prov = sqlFetchArray($ures)) {
                                                        $checked = "";
                                                        $suffix = "";
                                                        if ($prefs) {
                                                            $provs = explode('|', $prefs['ME_providers']);
                                                            foreach ($provs as $doc) {
                                                                if ($doc == $prov['id']) {
                                                                    $checked = 'checked ="checked"';
                                                                }
                                                            }
                                                        }
                                                        if (!empty($prov['suffix'])) {
                                                            $suffix = ', ' . $prov['suffix'];
                                                        }
                                                        ?>
                                                        <input <?php echo $checked; ?> class="update" type="checkbox" name="providers[]" id="provider_<?php echo attr($prov['id']); ?>" value="<?php echo attr($prov['id']); ?>">
                                                        <label for="provider_<?php echo attr($prov['id']); ?>"><?php echo text($prov['fname']) . " " . text($prov['lname']) . text($suffix); ?></label><br /><?php
                                                    }
                                                    ?>
                                                    </div>
                                                </div>
                                                <div class="divTableRow">
                                                    <div class="divTableCell divTableHeading"><?php echo xlt('Labels'); ?></div>
                                                    <div class="divTableCell indent20">
                                                    <input type="checkbox" class="update" name="LABELS_local" id="LABELS_local" value="1" <?php if ($prefs['LABELS_local']) {
                                                            echo "checked='checked'";} ?> />
                                                        <label for="LABELS_local" class="input-helper input-helper--checkbox" data-toggle='tooltip' data-placement='auto' title='<?php echo xla('Check if you plan to use Avery Labels for Reminders or Recalls'); ?>'>
                                                            <?php echo xlt('Use Avery Labels'); ?></label>
                                                        <select class="update form-control ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all" id="chart_label_type" name="chart_label_type">
                                                                        <option value='1' <?php if ($prefs['LABELS_choice'] == '1') {
                                                                            echo "selected";} ?>>5160</option>
                                                                        <option value='2' <?php if ($prefs['LABELS_choice'] == '2') {
                                                                            echo "selected";} ?>>5161</option>
                                                                        <option value='3' <?php if ($prefs['LABELS_choice'] == '3') {
                                                                            echo "selected";} ?>>5162</option>
                                                                        <option value='4' <?php if ($prefs['LABELS_choice'] == '4') {
                                                                            echo "selected";} ?>>5163</option>
                                                                        <option value='5' <?php if ($prefs['LABELS_choice'] == '5') {
                                                                            echo "selected";} ?>>5164</option>
                                                                        <option value='6' <?php if ($prefs['LABELS_choice'] == '6') {
                                                                            echo "selected";} ?>>8600</option>
                                                                        <option value='7' <?php if ($prefs['LABELS_choice'] == '7') {
                                                                            echo "selected";} ?>>L7163</option>
                                                                        <option value='8' <?php if ($prefs['LABELS_choice'] == '8') {
                                                                            echo "selected";} ?>>3422</option>
                                                                    </select>

                                                    </div>
                                                </div>
                                                <div class="divTableRow">
                                                    <div class="divTableCell divTableHeading"><?php echo xlt('Postcards'); ?></div>
                                                    <div class="divTableCell indent20">
                                                    <!--
                                                        <input type="checkbox" class="update" name="POSTCARDS_local" id="POSTCARDS_local" value="1" <?php if ($prefs['POSTCARDS_local']) {
                                                            echo "checked='checked'";} ?>" />
                                                        <label for="POSTCARDS_local" name="POSTCARDS_local" class="input-helper input-helper--checkbox" data-toggle='tooltip' data-placement='auto'  title='<?php echo xla('Check if you plan to print postcards locally'); ?>'><?php echo xlt('Print locally'); ?></label><br />
                                                        <input type="checkbox" class="update" name="POSTCARDS_remote" id="POSTCARDS_remote" value="1" <?php if ($prefs['POSTCARDS_remote']) {
                                                            echo "checked='checked'";} ?>" />
                                                        <label for="POSTCARDS_remote" name="POSTCARDS_remote" class="input-helper input-helper--checkbox" data-toggle='tooltip' data-placement='auto'  title='<?php echo xla('Check if you plan to send postcards via MedEx'); ?>'><?php echo xlt('Print remotely'); ?></label>
                                                    -->
                                                        <label for="postcards_top" data-toggle="tooltip" data-placement="auto" title="<?php echo xla('Custom text for Flow Board postcards. After changing text, print samples before printing mass quantities!'); ?>"><u><?php echo xlt('Custom Greeting'); ?>:</u></label><br />
                                                        <textarea rows=3 columns=70 id="postcard_top" name="postcard_top" class="update form-control" style="font-weight:400;"><?php echo nl2br(text($prefs['postcard_top'])); ?></textarea>
                                                    </div>
                                                </div>
                                            <input type="hidden" name="ME_username" id="ME_username" value="<?php echo attr($prefs['ME_username']);?>" />
                                            <input type="hidden" name="ME_api_key" id="ME_api_key" value="<?php echo attr($prefs['ME_api_key']);?>" />
                                            </div>
                                        </div>
                                </div>
                                <div class="col-sm-5 div-center" id="daform3">
                                    <div class="divTable2">
                                        <div class="divTableRow">
                                            <div class="divTableCell divTableHeading"><?php echo xlt('Sync Frequency'); ?></div>
                                            <div class="divTableCell indent20">
                                                <input name="execute_interval"
                                                       id="execute_interval"
                                                       class="form-control-range update"
                                                       min="0" max="360" step="1"
                                                       type="range"
                                                       value="<?php echo attr($prefs['execute_interval']); ?>">
                                                <span id="active_sync"><?php  echo xlt('During the work-day, syncs occurs every'); ?>
                                                    <span id="display_interval"><?php echo text($prefs['execute_interval']); ?></span> <?php echo xlt('minutes'); ?>
                                                </span>
                                                <span id="paused"><?php echo xlt("Synchronization with MedEx is paused"); ?></span>
                                            </div>
                                        </div>
                                        <?php if (count($logged_in['products']['ordered']) > '0') { ?>
                                            <div class="divTableRow">
                                            <div class="divTableCell divTableHeading"><?php echo xlt('Enabled Services'); ?></div>
                                            <div class="divTableCell">
                                                <ul>
                                                    <?php
                                                    foreach ($logged_in['products']['ordered'] as $service) {
                                                        ?><li><a href="<?php echo $service['view']; ?>" target="_medex"><?php echo $service['model']; ?> </a></li>
                                                        <?php echo $service['list'];
                                                    } ?>
                                                </ul>
                                            </div>
                                        </div>
                                            <?php
                                        }
                                        if (!empty($logged_in['products']['not_ordered'])) {
                                            ?>
                                            <div class="divTableRow">
                                                <div class="divTableCell divTableHeading"><?php echo xlt('Available Services'); ?></div>
                                                <div class="divTableCell">
                                                    <ul>
                                                    <?php
                                                    foreach ($logged_in['products']['not_ordered'] as $service) {
                                                        ?><li><a href="<?php echo $service['view']; ?>" target="_medex"><?php echo $service['model']; ?> </a></li>
                                                        <?php
                                                        if ($service['product_id'] == '54') {
                                                            ?>
                                                            <div style="margin-left: 10px;">Appointment Reminders<br />Patient Recalls<br />SMS Bot<br />Go Green Messages</div>
                                                            <?php
                                                        }
                                                    } ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            <?php
                                        } ?>

                                        </div>
                                </div>
                            </div>
                            <div class="col-sm-1"></div>
                            <div class="clearfix text-center" id="msg bottom"><br />
                            </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    public function display_recalls($logged_in)
    {
        global $MedEx;
        global $rcb_selectors;
        global $rcb_facility;
        global $rcb_provider;

        //let's get all the recalls the user requests, or if no dates set use defaults
        $from_date = (!empty($_REQUEST['form_from_date'])) ? DateToYYYYMMDD($_REQUEST['form_from_date']) : date('Y-m-d', strtotime('-6 months'));
        //limit date range for initial Board to keep us sane and not tax the server too much

        if (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'Y') {
            $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
            $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d'), date('Y') + $ptkr_time);
        } elseif (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'M') {
            $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
            $ptkr_future_time = mktime(0, 0, 0, date('m') + $ptkr_time, date('d'), date('Y'));
        } elseif (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'D') {
             $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
             $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d') + $ptkr_time, date('Y'));
        }
        $to_date = date('Y-m-d', $ptkr_future_time);
        //prevSetting to_date?

        $to_date = (!empty($_REQUEST['form_to_date'])) ? DateToYYYYMMDD($_REQUEST['form_to_date']) : $to_date;

        $recalls = $this->get_recalls($from_date, $to_date);

        $processed = $this->recall_board_process($logged_in, $recalls, $events ?? '');
        ob_start();

        ?>

    <div class="container mt-3">
        <div class="row" id="rcb_selectors" style="display: <?php echo attr($rcb_selectors); ?>">
            <div class="col-12 text-center">
                <h2><?php echo xlt('Recall Board'); ?></h2>
                <p class="text-danger"><?php echo xlt('Persons needing a recall, no appt scheduled yet.'); ?></p>
            </div>
            <div class="col-12 jumbotron p-4">
                <div class="showRFlow text-center" id="show_recalls_params">
                    <?php
                    if ($GLOBALS['medex_enable'] == '0') {
                        $last_col_width = "nodisplay";
                    }
                    ?>

                    <form name="rcb" id="rcb" method="post">
                        <input type="hidden" name="go" value="Recalls" />
                        <div class="text-center row align-items-center">
                            <div class="col-sm-4 text-center mt-3">
                                <div class="form-group row justify-content-center mx-sm-1">
                                    <select class="form-control form-control-sm" id="form_facility" name="form_facility"
                                        <?php
                                        $fac_sql = sqlStatement("SELECT * FROM facility ORDER BY id");
                                        $select_facs = '';
                                        $count_facs = 0;
                                        while ($fac = sqlFetchArray($fac_sql)) {
                                            $true = ($fac['id'] == $rcb_facility) ? "selected=true" : '';
                                            $select_facs .= "<option value=" . attr($fac['id']) . " " . $true . ">" . text($fac['name']) . "</option>\n";
                                            $count_facs++;
                                        }
                                        if ($count_facs < '1') {
                                            echo "disabled";
                                        }
                                        ?>  onchange="show_this();">
                                        <option value=""><?php echo xlt('All Facilities'); ?></option>
                                        <?php echo $select_facs; ?>
                                    </select>
                                </div>
                                <div class="form-group row mx-sm-1">
                                    <input placeholder="<?php echo xla('Patient ID'); ?>" class="form-control form-control-sm text-center" type="text" id="form_patient_id" name="form_patient_id" value="<?php echo (!empty($form_patient_id)) ? attr($form_patient_id) : ""; ?>" onKeyUp="show_this();" />
                                </div>
                            </div>

                            <div class="col-sm-4 text-center mt-3">
                                <div class="form-group row mx-sm-1 justify-content-center">
                                    <?php
                                    # Build a drop-down list of providers.
                                    $query = "SELECT id, lname, fname FROM users WHERE " .
                                    "authorized = 1  AND active = 1 ORDER BY lname, fname"; #(CHEMED) facility filter
                                    $ures = sqlStatement($query);
                                    //a year ago @matrix-amiel Adding filters to flow board and counting of statuses
                                    $count_provs = count(sqlFetchArray($ures));
                                    ?>
                                    <select class="form-control form-control-sm" id="form_provider" name="form_provider" <?php if ($count_provs < '2') {
                                        echo "disabled"; } ?> onchange="show_this();">
                                        <option value="" selected><?php echo xlt('All Providers'); ?></option>
                                        <?php
                                        // Build a drop-down list of ACTIVE providers.
                                        $query = "SELECT id, lname, fname FROM users WHERE " .
                                        "authorized = 1  AND active = 1 ORDER BY lname, fname"; #(CHEMED) facility filter
                                        $ures = sqlStatement($query);
                                        //a year ago @matrix-amiel Adding filters to flow board and counting of statuses
                                        while ($urow = sqlFetchArray($ures)) {
                                            $provid = $urow['id'];
                                            echo "    <option value='" . attr($provid) . "'";
                                            if (isset($rcb_provider) && $provid == ($_POST['form_provider'] ?? '')) {
                                                echo " selected";
                                            } elseif (!isset($_POST['form_provider']) && $_SESSION['userauthorized'] && $provid == $_SESSION['authUserID']) {
                                                echo " selected";
                                            }
                                            echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group row mx-sm-1">
                                    <input type="text" placeholder="<?php echo xla('Patient Name'); ?>" class="form-control form-control-sm text-center" id="form_patient_name" name="form_patient_name" value="<?php echo (!empty($form_patient_name)) ? attr($form_patient_name) : ""; ?>" onKeyUp="show_this();" />
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="input-append">
                                    <div class="form-group row mt-md-5">
                                        <label for="flow_from" class="col"><?php echo xlt('From'); ?>:</label>
                                        <div class="col">
                                            <input id="form_from_date" name="form_from_date" class="datepicker form-control form-control-sm text-center" value="<?php echo attr(oeFormatShortDate($from_date)); ?>" style="max-width: 140px; min-width: 85px;" />
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="flow_to" class="col">&nbsp;&nbsp;<?php echo xlt('To{{Range}}'); ?>:</label>
                                        <div class="col">
                                            <input id="form_to_date" name="form_to_date" class="datepicker form-control form-control-sm text-center" value="<?php echo attr(oeFormatShortDate($to_date)); ?>" style="max-width:140px;min-width:85px;">
                                        </div>
                                    </div>
                                    <div class="form-group row" role="group">
                                        <div class="col text-right">
                                            <button class="btn btn-primary btn-filter" type="submit" id="filter_submit" value="<?php echo xla('Filter'); ?>"><?php echo xlt('Filter'); ?></button>
                                            <button class="btn btn-primary btn-add" onclick="goReminderRecall('addRecall');return false;"><?php echo xlt('New Recall'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div name="message" id="message" class="warning">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container text-center">
            <div class="showRecalls mx-auto" id="show_recalls">
                <div name="message" id="message" class="warning">
                </div>
                <span class="text-right fa-stack fa-lg pull_right small" id="rcb_caret" onclick="toggleRcbSelectors();" data-toggle="tooltip" data-placement="auto" title="Show/Hide the Filters" style="color: <?php echo $color = (!empty($setting_selectors) && ($setting_selectors == 'none')) ? 'var(--danger)' : 'var(--black)'; ?>; position: relative; float: right; right: 0; top: 0;">
                    <i class="far fa-square fa-stack-2x"></i>
                    <i id="print_caret" class='fas fa-caret-<?php echo $caret = ($rcb_selectors === 'none') ? 'down' : 'up'; ?> fa-stack-1x'></i>
                </span>
                <ul class="nav nav-tabs" id="medex-recall-nav">
                    <li class="whitish"><a onclick="show_this();" class="nav-link"><?php echo xlt('All'); ?></a></li>
                    <li class="whitish"><a onclick="show_this('whitish');" class="nav-link" ><?php echo xlt('Events Scheduled'); ?></a></li>
                    <li class="yellowish"><a onclick="show_this('yellowish');" class="nav-link"><?php echo xlt('In-process'); ?></a></li>
                    <li class="reddish"><a onclick="show_this('reddish');" class="nav-link"><?php echo xlt('Manual Processing Required'); ?></a></li>
                    <li class="greenish"><a onclick="show_this('greenish');" class="nav-link"><?php echo xlt('Recently Completed'); ?></a></li>
                </ul>

                <div class="tab-content">
                   <div class="tab-pane active" id="tab-all">
                        <?php
                            $this->recall_board_top();
                            echo $processed['ALL'] ?? '';
                            $this->recall_board_bot();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <?php
        //we need to respect facility and provider requests if submitted.
        // 1.Retrieve everything for a given date range.
        // 2.Refine results by facility and provider using jquery on cached results
        //   ie. further requests to view facility/provider within page can be done fast through javascript, no page reload needed.
        ?>
    <script>
        function toggleRcbSelectors() {
            if ($("#rcb_selectors").css('display') === 'none') {
                $.post( "<?php echo $GLOBALS['webroot'] . "/interface/main/messages/messages.php"; ?>", {
                    'rcb_selectors' : 'block',
                    success: function (data) {
                        $("#rcb_selectors").slideToggle();
                        $("#rcb_caret").css('color','var(--black)');
                    }
                });
            } else {
                $.post( "<?php echo $GLOBALS['webroot'] . "/interface/main/messages/messages.php"; ?>", {
                    'rcb_selectors' : 'none',
                    success: function (data) {
                        $("#rcb_selectors").slideToggle();
                        $("#rcb_caret").css('color','var(--danger)');
                    }
                });
            }
            $("#print_caret").toggleClass('fa-caret-up').toggleClass('fa-caret-down');
        }

        function SMS_bot(pid) {
            top.restoreSession();
            pid = pid.replace('recall_','');
            window.open('<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/messages.php?nomenu=1&go=SMS_bot&pid=' + pid,'SMS_bot', 'width=370,height=600,resizable=0');
            return false;
        }
        $(function () {
            show_this();

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });
        </script>
        <?php
        $content = ob_get_clean();
        echo $content;
    }
    public function get_recalls($from_date = '', $to_date = '')
    {
        // Recalls are requests to schedule a future appointment.
        // Thus there is no r_appt_time (NULL) but there is a DATE set.

        $query = "SELECT * FROM medex_recalls,patient_data AS pat
                    WHERE pat.pid=medex_recalls.r_pid AND
                    r_eventDate >= ? AND
                    r_eventDate <= ? AND
                    IFNULL(pat.deceased_date,0) = 0
                    ORDER BY r_eventDate ASC";
        $result = sqlStatement($query, array($from_date,$to_date));
        while ($recall = sqlFetchArray($result)) {
            $recalls[] = $recall;
        }
        return $recalls ?? null;
    }
    private function recall_board_process($logged_in, $recalls, $events = '')
    {
        global $MedEx;
        $count_facilities = 0;
        $count_providers = 0;
        $process = array();
        if (empty($recalls)) {
            return false;
        }
        $fac_sql = sqlStatement("SELECT id, name FROM facility WHERE service_location != 0");
        while ($facrow = sqlFetchArray($fac_sql)) {
            $facility[$facrow['id']] = $facrow['name'];
            $count_facilities++;
        }
        $prov_sql = sqlStatement("SELECT * FROM users WHERE authorized != 0 AND active = 1 ORDER BY lname, fname");
        while ($prov = sqlFetchArray($prov_sql)) {
            $provider[$prov['id']] = $prov['fname'][0] . " " . $prov['lname'];
            if (!empty($prov['suffix'])) {
                $provider[$prov['id']] .= ', ' . $prov['suffix'];
            }
            $count_providers++;
        }
        foreach ($recalls as $recall) {
            $show = $this->show_progress_recall($recall, $events);
            if (!empty($show['DONE'])) {
                continue;
            }
            if (empty($show['status'])) {
                $show['status'] = 'whitish';
            }
            ob_start();
            echo '<tr class="divTableRow ALL text-center ' . attr($show['status']) . '"
                 data-status="' . attr($show['status']) . '"
                 data-plan="' . attr($show['plan']) . '"
                 data-facility="' . attr($recall['r_facility']) . '"
                 data-provider="' . attr($recall['r_provider']) . '"
                 data-pname="' . attr($recall['fname'] . " " . $recall['lname']) . '"
                 data-pid="' . attr($recall['pid']) . '"
                 id="recall_' . attr($recall['pid']) . '" style="display:none;">';

            $query = "SELECT cal.pc_eventDate,pat.DOB FROM openemr_postcalendar_events AS cal JOIN patient_data AS pat ON cal.pc_pid=pat.pid WHERE cal.pc_pid =? ORDER BY cal.pc_eventDate DESC LIMIT 1";
            $result2 = sqlQuery($query, array( $recall['pid'] ));
            $last_visit = $result2['pc_eventDate'];
            $DOB = oeFormatShortDate($result2['DOB']);
            $age = $MedEx->events->getAge($result2['DOB']);
            echo '<td class="divTableCell"><a href="#" onclick="show_patient(\'' . attr($recall['pid']) . '\');"> ' . text($recall['fname']) . ' ' . text($recall['lname']) . '</a>';
            if ($GLOBALS['ptkr_show_pid']) {
                echo '<br /><span data-toggle="tooltip" data-placement="auto" title="' . xla("Patient ID") . '" class="small">' . xlt('PID') . ': ' . text($recall['pid']) . '</span>';
            }
            echo '<br /><span data-toggle="tooltip" data-placement="auto" title="' . xla("Most recent visit") . '" class="small">' . xlt("Last Visit") . ': ' . text(oeFormatShortDate($last_visit)) . '</span>';
            echo '<br /><span class="small" data-toggle="tooltip" data-placement="auto" title="' . xla("Date of Birth and Age") . '">' . xlt('DOB') . ': ' . text($DOB) . ' (' . $age . ')</span>';
            echo '</td>';

            echo '<td class="divTableCell appt_date">' . text(oeFormatShortDate($recall['r_eventDate']));
            if ($recall['r_reason'] > '') {
                echo '<br />' . text($recall['r_reason']);
            }
            if (strlen($provider[$recall['r_provider']]) > 14) {
                $provider[$recall['r_provider']] = substr($provider[$recall['r_provider']], 0, 14) . "...";
            }
            if (strlen($facility[$recall['r_facility']]) > 20) {
                $facility[$recall['r_facility']] = substr($facility[$recall['r_facility']], 0, 17) . "...";
            }

            if ($count_providers > '1') {
                echo "<br /><span data-toggle='tooltip' data-placement='auto'  title='" . xla('Provider') . "'>" . text($provider[$recall['r_provider']]) . "</span>";
            }
            if (( $count_facilities > '1' ) && ( $_REQUEST['form_facility'] == '' )) {
                echo "<br /><span data-toggle='tooltip' data-placement='auto'  title='" . xla('Facility') . "'>" . text($facility[$recall['r_facility']]) . "</span><br />";
            }

            echo '</td>';
            echo '<td class="divTableCell phones">';
            if ($recall['phone_cell'] > '') {
                echo 'C: ' . text($recall['phone_cell']) . "<br />";
                //    echo 'C:'.substr($recall['phone_cell'], 0, 2).'-XXX-XXXX<br />';
            }
            if ($recall['phone_home'] > '') {
                echo 'H: ' . text($recall['phone_home']) . "<br />";
                //echo 'H:'.substr($recall['phone_home'], 0, 2).'-XXX-XXXX<br />';
            }
            if ($recall['email'] > '') {
                $mailto = $recall['email'];
                if (strlen($recall['email']) > 15) {
                    $recall['email'] = substr($recall['email'], 0, 12) . "...";
                }
                echo 'E: <a data-toggle="tooltip" data-placement="auto" title="' . xla('Send an email to ') . attr($mailto) . '" href="mailto:' . attr($mailto) . '">' . text($recall['email']) . '</a><br />';
            }
            if ($logged_in) {
                $pat = $this->possibleModalities($recall);
                echo $pat['SMS'] . $pat['AVM'] . $pat['EMAIL'];//escape/translation done in possibleModalities.
            }
            echo '</td>';

            if ($show['postcard'] > '') {
                echo '<td class="divTableCell text-center postcards">' . text($show['postcard']) . '</td>';
            } else {
                echo '<td class="divTableCell text-center postcards"><input type="checkbox" name="postcards" id="postcards[]" value="' . attr($recall['pid']) . '" /></td>';
            }

            if ($show['label'] > '') {
                echo '<td class="divTableCell text-center labels">' . text($show['label']) . '</td>';
            } else {
                echo '<td class="divTableCell text-center labels"><input type="checkbox" name="labels" id="labels[]" value="' . attr($recall['pid']) . '" /></td>';
            }
            echo '  <td class="divTableCell text-center msg_manual"><span class="fa fa-fw spaced_icon" >
                        <input type="checkbox" name="msg_phone" id="msg_phone_' . attr($recall['pid']) . '" onclick="process_this(\'phone\',\'' . attr($recall['pid']) . '\',\'' . attr($recall['r_ID']) . '\')" />
                </span>';
            echo '    <span data-toggle="tooltip" data-placement="auto" title="' . xla('Scheduling') . '" class="fa fa-calendar-check-o fa-fw" onclick="newEvt(\'' . attr($recall['pid']) . '\',\'\');">
                </span>';
            echo '</td>';

            echo '  <td class="divTableCell text-left msg_resp">';
            //    if phone call made show each in progress
            echo '<textarea onblur="process_this(\'notes\',\'' . attr($recall['pid']) . '\',\'' . attr($recall['r_ID']) . '\');" name="msg_notes" id="msg_notes_' . attr($recall['pid']) . '" style="width:90%;height:30px;">' . nl2br(text($recall['NOTES'])) . '</textarea>';
            echo '</td>';
            echo '  <td class="divTableCell text-left msg_resp">
            <i class="top_right_corner fa fa-times" onclick="delete_Recall(\'' . attr($recall['pid']) . '\',\'' . attr($recall['r_ID']) . '\')"></i> ';
            echo $show['progression'];

            if ($show['appt']) {
                echo "<span onclick=\"newEvt('" . attr($prog['pid']) . "','" . attr($show['pc_eid']) . "');\" class='btn btn-danger text-center' data-toggle='tooltip' data-placement='auto'  title='" . xla('Appointment made by') . " " . attr($prog['who']) . " " . xla('on') . " " . attr($prog['when']) . "'><b>" . xlt('Appt{{Abbreviation for appointment}}') . ":</b> " . text($show['appt']) . "<br />";
            }
            echo '</td>';
            echo '</tr>';
            $content = ob_get_clean();
            $process['ALL'] .= $content;
        }
        return $process;
    }

/**
 *  This function looks at a single recall and assesses its status.
 * @param $recall
 * @param string $events
 * @return mixed
 * @internal param string $possibleModalities
 */
    public function show_progress_recall($recall, $events = '')
    {
        global $logged_in;
        //Two scenarios: First, appt is made as recall asks. Second, appt is made not for recall reason - recall still needed.
        //We can either require all recalls to be manually deleted or do some automatically...  If manual only,
        //the secretary looking at the board will need to know when they were last seen at least and when next appt is
        //to know if they can/should delete the recall.  If semi-automatic, we'll use an artificial time horizon of 3 months.
        //If an appt is made through any means, and it is within 3 months of the recall date, assume it wipes out the recall.
        //If the appt was just made today, let the board show it as "green", ie. completed.  Gives us a sense of accomplishment,
        //that we got some work done today...
        //So, if appt was made more than 16 hours ago, and it is within 3 months of the recall date, auto-delete the recall from the board.
        //ie.  appts added in for problem visits won't auto-delete an official recall unless they are close in time to the recall...
        //Adjust according to your needs and work flows.  This function is run by the Recall Board and with cron MedEx calls.
        $show['EMAIL']['text'] = '';
        $show['SMS']['text'] = '';
        $show['AVM']['text'] = '';
        $show['progression'] = '';
        $show['DONE'] = '';
        $query = "SELECT * FROM openemr_postcalendar_events WHERE
                  pc_eventDate >= CURDATE() AND pc_pid =? AND pc_eventDate > (? - INTERVAL 90 DAY)  AND pc_time >  (CURDATE()- INTERVAL 16 HOUR)";
        $count = sqlFetchArray(sqlStatement($query, array($recall['r_pid'],$recall['r_eventDate'])));

        if ($count) {
            $sqlDELETE = "DELETE FROM medex_outgoing WHERE msg_pc_eid = ?";
            sqlStatement($sqlDELETE, array('recall_' . $recall['pid']));
            $sqlDELETE = "DELETE FROM medex_recalls WHERE r_pid = ?";
            sqlStatement($sqlDELETE, array($recall['pid']));
            //log this action "Recall for $pid deleted now()"?
            $show['DONE'] = '1';//tells recall board to move on.
            $show['status'] = 'greenish'; //tells MedEx to move on, don't process this recall - delete it from their servers.
            return $show;
            // Just cleaning up the Recall Board for you. Move along, nothing to see.
            // If you need to look at the track of action, look in the log?
        }

        $sql = "SELECT * FROM medex_outgoing WHERE msg_pc_eid = ?  ORDER BY msg_date ASC";
        $result = sqlStatement($sql, array('recall_' . $recall['pid']));
        $something_happened = '';

        while ($progress = sqlFetchArray($result)) {
            $i = $progress['campaign_uid'];//if this is a manual entry, this ==0.

            $phpdate = strtotime($progress['msg_date']);
            $when = oeFormatShortDate(date('Y-m-d', $phpdate)) . " @ " . date('g:iA', $phpdate);

            if (is_numeric($progress['msg_reply'])) { // it was manually added by id
                $sql2 = "SELECT * FROM users WHERE id =?";

                $who  = sqlQuery($sql2, array($progress['msg_reply']));
                $who_name = $who['fname'] . " " . $who['lname'];
                //Manually generated actions
                if ($progress['msg_type'] == 'phone') { //ie. a manual phone call, not an AVM
                    $show['progression'] .= "<span class='left' data-toggle='tooltip' data-placement='auto'  title='" . xla('Phone call made by') . " " . text($who_name) . "'><b>" . xlt('Phone') . "</b> " . text($when) . "</span></br />\n";
                } elseif ($progress['msg_type'] == 'notes') {
                    $show['progression'] .= "<span class='left' data-toggle='tooltip' data-placement='auto'  title='" . xla('Notes by') . " " . text($who_name) . " on " . text($when) . "'><b>" . xlt('Note') . ":</b> " . text($progress['msg_extra_text']) . "</span></br />\n";
                } elseif ($progress['msg_type'] == 'postcards') {
                    $show['progression'] .= "<span class='left' data-toggle='tooltip' data-placement='auto'  title='" . xla('Postcard printed by') . " " . text($who_name) . "'><b>" . xlt('Postcard') . ":</b> " . text($when) . "</span></br />\n";
                } elseif ($progress['msg_type'] == 'labels') {
                    $show['progression'] .= "<span class='left' data-toggle='tooltip' data-placement='auto'  title='" . xla('Label printed by') . " " . text($who) . "'><b>" . xlt('Label') . ":</b> " . text($when) . "</span></br />";
                }
            } else {
                $who_name = "MedEx";
                if (($progress['msg_reply'] == "READ") || ($show[$progress['msg_type']]['stage'] == "READ")) {
                    $show[$progress['msg_type']]['stage']   = "READ";
                    $icon = $this->get_icon($progress['msg_type'], "READ");
                    $show[$progress['msg_type']]['text']    = "<span class='left'>" . $icon . " " . text($when) . "</span><br />";
                    if ($progress['msg_type'] == 'AVM') {
                        $show['campaign'][$i]['status'] = "reddish";
                    }
                } elseif (($progress['msg_reply'] == "SENT") || ($show[$progress['msg_type']]['stage'] == "SENT")) {
                    if ($show[$progress['msg_type']]['stage'] != "READ") {
                        $show[$progress['msg_type']]['stage']   = "SENT";
                        $icon = $this->get_icon($progress['msg_type'], "SENT");
                        $show[$progress['msg_type']]['text']    = "<span class='left'>" . $icon . " " . text($when) . "</span><br />";
                    }
                } elseif (($progress['msg_reply'] == "To Send") || ($show[$progress['msg_type']]['stage'] == "QUEUED")) {
                    if (($show[$progress['msg_type']]['stage'] != "READ") && ($show[$progress['msg_type']]['stage'] != "SENT")) {
                        $show[$progress['msg_type']]['stage']   = "QUEUED";
                        $icon = $this->get_icon($progress['msg_type'], $progress['msg_reply']);
                    }
                }
                if ($progress['msg_reply'] == "CALL") {
                    $icon = $this->get_icon($progress['msg_type'], "CALL");
                    $show['progression'] .= "<span class='left'>" . $icon . " " . text($progress['msg_type']) . "@" . text($when) . "</span><br />";
                } elseif ($progress['msg_reply'] == "STOP") {
                    $icon = $this->get_icon($progress['msg_type'], "STOP");
                    $show['progression'] .= "<span class='left'>" . $icon . " " . text($when) . "</span><br />";
                } elseif ($progress['msg_reply'] == "EXTRA") {
                    $icon = $this->get_icon($progress['msg_type'], "EXTRA");
                    $show['progression'] .= "<span class='left'>" . $icon . " " . text($when) . "</span><br />";
                } elseif ($progress['msg_reply'] == "FAILED") {
                    $icon = $this->get_icon($progress['msg_type'], "FAILED");
                    $show['progression']  .= "<span class='left'>" . $icon . " " . text($when) . "</span><br />";
                    $show['campaign'][$i]['status'] = 1;
                }
                $show['campaign'][$i]['icon'] = $icon;
            }

            $something_happened = true;
        }
        $show['progression'] .= $show['EMAIL']['text'] . $show['SMS']['text'] . $show['AVM']['text'];

        $camps = '0';
        if (is_countable($events)) {
            foreach ($events as $event) {
                if ($event['M_group'] != "RECALL") {
                    continue;
                }
                $pat = $this->possibleModalities($recall);
                if ($pat['ALLOWED'][$event['M_type']] == 'NO') {
                    continue;    //it can't happen
                }
                if ($pat['facility']['status'] != 'ok') {
                    continue;    //it can't happen
                }
                if ($pat['provider']['status'] != 'ok') {
                    continue;    //it can't happen
                }

                if ($show['campaign'][$event['C_UID']]['status']) {
                    continue; //it is done
                }
                $camps++;                                                   //there is still work to be done
                if ($show['campaign'][$event['C_UID']]['icon']) {
                    continue;   //but something has happened since it was scheduled.
                }

                ($event['E_timing'] < '3') ? ($interval = '-') : ($interval = '+');//this is only scheduled, 3 and 4 are for past appointments...
                $show['campaign'][$event['C_UID']] = $event;
                $show['campaign'][$event['C_UID']]['icon'] = $this->get_icon($event['M_type'], "SCHEDULED");

                $recall_date = date("Y-m-d", strtotime($interval . $event['E_fire_time'] . " days", strtotime($recall['r_eventDate'])));
                $date1 = date('Y-m-d');
                $date_diff = strtotime($date1) - strtotime($recall['r_eventDate']);
                if ($date_diff >= '-1') { //if it is sched for tomorrow or earlier, queue it up
                    $show['campaign'][$event['C_UID']]['executed'] = "QUEUED";
                    $show['status'] = "whitish";
                } else {
                    $execute = oeFormatShortDate($recall_date);
                    $show['campaign'][$event['C_UID']]['executed'] = $execute;
                }
                $show['progression'] .= "<a href='https://medexbank.com/cart/upload/index.php?route=information/campaigns' class='nowrap text-left' target='_MedEx'>" .
                                    $show['campaign'][$event['C_UID']]['icon'] . " " . text($show['campaign'][$event['C_UID']]['executed']) . "</a><br />";
            }
        }

        $query  = "SELECT * FROM openemr_postcalendar_events WHERE pc_eventDate > CURDATE() AND pc_pid =? AND pc_time >  CURDATE()- INTERVAL 16 HOUR";
        $result = sqlFetchArray(sqlStatement($query, array($recall['pid'])));

        if ($something_happened || $result) {
            if ($result) {
                $show['status'] = "greenish"; //appt made, move on
                $phpdate = strtotime($result['pc_eventDate'] . " " . $result['pc_startTime']);
                $show['pc_eid'] = $result['pc_eid'];
                $show['appt'] = oeFormatShortDate(date('Y-m-d', $phpdate)) . " @ " . date('g:iA', $phpdate);
                $show['DONE'] = '1';
            } elseif ($GLOBALS['medex_enable'] == '1') {
                if ($logged_in) {
                    if ($camps == '0') {
                        $show['status'] = "reddish"; //hey, nothing automatic left to do - manual processing required.
                    } else {
                        $show['status'] = "yellowish"; //no appt yet but something happened!
                    }
                }
            } else {
                $show['status'] = "whitish";
            }
        } elseif (($GLOBALS['medex_enable'] == '1') && ($camps == '0')) {
                $show['status'] = "reddish"; //hey, nothing automatic left to do - manual processing required.
        } else {
            $show['status'] = "whitish";
        }
        if ($logged_in) {
            $show['progression'] =   '<div onclick="SMS_bot(\'recall_' . $recall['pid'] . '\');">' . $show['progression'] . '</div>';
        }
        return $show;
    }
    private function get_icon($event_type, $status = 'SCHEDULED')
    {
        $sqlQuery = "SELECT * FROM medex_icons";
        $result = sqlStatement($sqlQuery);
        while ($icons = sqlFetchArray($result)) {
            if (($icons['msg_type'] == $event_type) && ($icons['msg_status'] == $status)) {
                return $icons['i_html'];
            }
        }
        return false;
    }
    public function possibleModalities($appt)
    {
        $pat = array();
        $sqlQuery = "SELECT * FROM medex_icons";
        $result = sqlStatement($sqlQuery);
        while ($icons = sqlFetchArray($result)) {
            $icon[$icons['msg_type']][$icons['msg_status']] = $icons['i_html'];
        }
        //if the patient is dead, should we really be sending them a message?
        //Maybe we would need to customize this for a pathologist but for the rest, the answer is no...
        if (empty($appt['phone_cell']) || ($appt["hipaa_allowsms"] == "NO")) {
            $pat['SMS'] = $icon['SMS']['NotAllowed'];
            $pat['ALLOWED']['SMS'] = 'NO';
        } else {
            $phone = preg_replace("/[^0-9]/", "", $appt["phone_cell"]);
            $pat['SMS'] = $icon['SMS']['ALLOWED'];     // It is allowed and they have a cell phone
        }
        if ((empty($appt["phone_home"]) && (empty($appt["phone_cell"])) || ($appt["hipaa_voice"] == "NO"))) {
            $pat['AVM'] = $icon['AVM']['NotAllowed'];
            $pat['ALLOWED']['AVM'] = 'NO';
        } else {
            if (!empty($appt["phone_cell"])) {
                $phone = preg_replace("/[^0-9]/", "", $appt["phone_cell"]);
            } else {
                $phone = preg_replace("/[^0-9]/", "", $appt["phone_home"]);
            }
            $pat['AVM'] = $icon['AVM']['ALLOWED']; //We have a phone to call and permission!
        }
        if (($appt["email"] == "") || ($appt["hipaa_allowemail"] == "NO")) {
            $pat['EMAIL'] = $icon['EMAIL']['NotAllowed'];
            $pat['ALLOWED']['EMAIL'] = 'NO';
        } else {
            $pat['EMAIL'] = $icon['EMAIL']['ALLOWED'];
        }
        if ($GLOBALS['medex_enable'] == '1') {
            $sql = "SELECT * FROM medex_prefs";
            $prefs = sqlFetchArray(sqlStatement($sql));
            $facs = explode('|', $prefs['ME_facilities']);
            foreach ($facs as $place) {
                if (isset($appt['r_facility']) && ($appt['r_facility'] == $place)) {
                    $pat['facility']['status'] = 'ok';
                }
            }
            $providers = explode('|', $prefs['ME_providers']);
            foreach ($providers as $provider) {
                if (isset($appt['r_provider']) && ($appt['r_provider'] == $provider)) {
                    $pat['provider']['status'] = 'ok';
                }
            }
        }
        return $pat;
    }
    private function recall_board_top()
    {
        ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>
                            <?php echo xlt('Name'); ?>
                        </th>
                        <th>
                            <?php echo xlt('Recall'); ?>
                        </th>
                        <th>
                            <?php echo xlt('Contacts'); ?>
                        </th>
                        <th>
                            <?php echo xlt('Postcards'); ?>
                            <span onclick="top.restoreSession(); checkAll('postcards',true);" class="fa fa-square-o fa-lg" id="chk_postcards"></span>
                            <span onclick="process_this('postcards');" class="fa fa-print fa-lg"></span>
                        </th>
                        <th>
                            <?php echo xlt('Labels'); ?>
                            <span onclick="checkAll('labels',true);" class="fa fa-square-o fa-lg" id="chk_labels"></span>
                            <span onclick="process_this('labels');" class="fa fa-print fa-lg"></span>
                        </th>
                        <th>
                            <?php echo xlt('Office') . ": " . xlt('Phone'); ?>
                        </th>
                        <th>
                            <?php echo xlt('Notes'); ?>
                        </th>
                        <th>
                            <?php echo xlt('Progress'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
        <?php
    }
    private function recall_board_bot()
    {
        ?>      </tbody>
        </table>
    </div>
        <?php
    }
    public function display_add_recall($pid = 'new')
    {
        global $result_pat;
        ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center" id="add_recall">
                <h2><?php echo xlt('New Recall'); ?></h2>
                <p class="text-danger" name="div_response" id="div_response"><?php echo xlt('Create a reminder to schedule a future visit.'); ?></p>
            </div>

            <form class="prefs p-4 row" name="addRecall" id="addRecall">
                <input type="hidden" name="go" id="go" value="addRecall" />
                <input type="hidden" name="action" id="go" value="addRecall" />
                <div class="col-4 divTable m-2 ml-auto">
                    <div class="row divTableBody prefs">
                            <div class="divTableCell divTableHeading text-right form-group col-4 col-md-4"><label><?php echo xlt('Name'); ?></label></div>
                            <div class="divTableCell indent20 form-group col-8 col-md-8">
                                <input type="text" name="new_recall_name" id="new_recall_name" class="form-control"
                                        onclick="recall_name_click(this)"
                                        value="<?php echo attr($result_pat['fname']) . " " . attr($result_pat['lname']); ?>" />
                                <input type="hidden" name="new_pid" id="new_pid" value="<?php echo attr($result_pat['id']); ?>" />
                            </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('DOB'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8">
                            <?php
                                $DOB = oeFormatShortDate($result_pat['DOB']);
                            ?>
                            <span name="new_DOB" id="new_DOB" style="width: 90px;"><?php echo text($DOB); ?></span> -
                            <span id="new_age" name="new_age"><?php echo text($result_pat['age']); ?></span>
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('Recall When'); ?></label>
                        </div>
                        <div class="form-group col-8 col-md-8 divTableCell indent20">
                            <span class="font-weight-bold"><?php echo xlt('Last Visit'); ?>: </span>
                            <input type="text" value="" name="DOLV" id="DOLV" class="" />
                            <br />
                            <!-- Feel free to add in any dates you would like to show here...
                            <input type="radio" name="new_recall_when" id="new_recall_when_6mos" value="180">
                            <label for="new_recall_when_6mos" class="input-helper input-helper--checkbox">+ 6 <?php echo xlt('months'); ?></label><br />
                            -->
                            <div class="m-2 mb-3 ml-4">
                                <label for="new_recall_when_1yr" class="input-helper input-helper--checkbox">
                                    <input type="radio" name="new_recall_when" id="new_recall_when_1yr" value="365" /> <?php echo xlt('plus 1 year'); ?>
                                </label>
                                <br />
                                <label for="new_recall_when_2yr" class="p-15 input-helper input-helper--checkbox">
                                <input type="radio" name="new_recall_when" id="new_recall_when_2yr" value="730" /> <?php echo xlt('plus 2 years'); ?>
                                </label>
                                <br />
                                <label for="new_recall_when_3yr" class="input-helper input-helper--checkbox">
                                <input type="radio" name="new_recall_when" id="new_recall_when_3yr" value="1095" /> <?php echo xlt('plus 3 years'); ?></label>
                            </div>
                            <span class="font-weight-bold"> <?php echo xlt('Date'); ?>:</span>
                            <input class="datepicker form-control-sm text-center" type="text" id="form_recall_date" name="form_recall_date" value="" />
                        </div>

                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                                <label><?php echo xlt('Recall Reason'); ?></label>
                        </div>
                        <div class="form-group col-8 col-md-8 divTableCell indent20">
                            <input class="form-control" type="text" name="new_reason" id="new_reason" value="<?php if ($result_pat['PLAN'] > '') {
                                 echo attr(rtrim("|", trim($result_pat['PLAN']))); } ?>" />
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                            <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                                <label><?php echo xlt('Provider'); ?></label>
                            </div>
                            <div class="form-group col-8 col-md-8 divTableCell indent20">
                                    <?php
                                    $ures = sqlStatement("SELECT id, username, fname, lname FROM users WHERE authorized != 0 AND active = 1 ORDER BY lname, fname");
                                //This is an internal practice function so ignore the suffix as extraneous information.  We know who we are.
                                    $defaultProvider = $_SESSION['authUserID'];
                                // or, if we have chosen a provider in the calendar, default to them
                                // choose the first one if multiple have been selected
                                    if (is_countable($_SESSION['pc_username'])) {
                                        if (count($_SESSION['pc_username']) >= 1) {
                                            // get the numeric ID of the first provider in the array
                                            $pc_username = $_SESSION['pc_username'];
                                            $firstProvider = sqlFetchArray(sqlStatement("SELECT id FROM users WHERE username=?", array($pc_username[0])));
                                            $defaultProvider = $firstProvider['id'];
                                        }
                                    }
                                // if we clicked on a provider's schedule to add the event, use THAT.
                                    if ($userid) {
                                        $defaultProvider = $userid;
                                    }

                                    echo "<select class='form-control' name='new_provider' id='new_provider' style='width: 95%;'>";
                                    while ($urow = sqlFetchArray($ures)) {
                                        echo "    <option value='" . attr($urow['id']) . "'";
                                        if ($urow['id'] == $defaultProvider) {
                                            echo " selected";
                                        }
                                        echo ">" . text($urow['lname']);
                                        if ($urow['fname']) {
                                            echo ", " . text($urow['fname']);
                                        }
                                        echo "</option>\n";
                                    }
                                    echo "</select>";
                                    ?>
                            </div>
                    </div>
                    <div class="row divTableBody prefs">
                            <div class="text-right form-group col-4 col-md-4 divTableCell divTableHeading">
                                <label><?php echo xlt('Facility'); ?></label>
                            </div>
                            <div class="form-group col-8 col-md-8 divTableCell indent20">
                                <select class="form-control ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all" name="new_facility" id="new_facility" style="width: 95%;">
                                    <?php
                                        $qsql = sqlStatement("SELECT id, name, primary_business_entity FROM facility WHERE service_location != 0");
                                    while ($facrow = sqlFetchArray($qsql)) {
                                        if ($facrow['primary_business_entity'] == '1') {
                                            $selected = 'selected="selected"';
                                            echo "<option value='" . attr($facrow['id']) . "' $selected>" . text($facrow['name']) . "</option>";
                                        } else {
                                            $selected = '';
                                            echo "<option value='" . attr($facrow['id']) . "' $selected>" . text($facrow['name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                    </div>
                </div>
                <div class="col-4 divTable m-2 mr-auto">
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('Address'); ?></label>
                        </div>
                        <div class="divTableCell form-group col-8 col-md-8">
                            <div class="col-12 mb-12">
                                <input type="text" class="form-control" placeholder="<?php echo xla('Address'); ?>" name="new_address" id="new_address" value="<?php echo attr($result_pat['street']); ?>" />
                            </div>

                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="<?php echo xla('City'); ?>" name="new_city" id="new_city" value="<?php echo attr($result_pat['city']); ?>" />
                            </div>

                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="<?php echo xla('State'); ?>" name="new_state" id="new_state" value="<?php echo attr($result_pat['state']); ?>" />
                            </div>

                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="<?php echo xla('ZIP Code'); ?>" name="new_postal_code" id="new_postal_code" value="<?php echo attr($result_pat['postal_code']); ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('Home Phone'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8">
                            <input type="text" name="new_phone_home" id="new_phone_home" class="form-control" value="<?php echo attr($result_pat['phone_home']); ?>" />
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('Mobile Phone'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8">
                            <input type="text" name="new_phone_cell" id="new_phone_cell" class="form-control" value="<?php echo attr($result_pat['phone_cell']); ?>" />
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label data-toggle="tooltip" data-placement="top" title="<?php echo xla('Text Message permission'); ?>"><?php echo xlt('SMS OK'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8 form-check-inline">
                                    <input type="radio" class="form-check-input" name="new_allowsms" id="new_allowsms_yes" value="YES" />
                                    <label class="form-check-label" for="new_allowsms_yes"><?php echo xlt('YES'); ?></label>
                           <input class="form-check-input" type="radio" name="new_allowsms" id="new_allowsms_no" value="NO" />
                            <label class="form-check-label" for="new_allowsms_no"><?php echo xlt('NO'); ?></label>
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label data-toggle="tooltip" data-placement="top" title="<?php echo xla('Automated Voice Message permission'); ?>"><?php echo xlt('AVM OK'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8 form-check-inline">
                            <input class="form-check-input" type="radio" name="new_voice" id="new_voice_yes" value="YES" />
                            <label class="form-check-label" for="new_voice_yes"><?php echo xlt('YES'); ?></label>
                            <input class="form-check-input" type="radio" name="new_voice" id="new_voice_no" value="NO" />
                            <label class="form-check-label" for="new_voice_no"><?php echo xlt('NO'); ?></label>
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('E-Mail'); ?></label>
                            </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8 form-check-inline">
                            <input type="email" name="new_email" id="new_email" class="form-control" value="<?php echo attr($result_pat['email']); ?>" />
                        </div>
                    </div>
                    <div class="row divTableBody prefs">
                        <div class="text-right btn-group-vertical form-group col-4 col-md-4 divTableCell divTableHeading">
                            <label><?php echo xlt('E-mail OK'); ?></label>
                        </div>
                        <div class="divTableCell indent20 form-group col-8 col-md-8 form-check-inline">
                                <input class="form-check-input" type="radio" name="new_email_allow" id="new_email_yes" value="YES" />
                            <label class="form-check-label" for="new_email_yes"><?php echo xlt('YES'); ?></label>
                            <input class="form-check-input" type="radio" name="new_email_allow" id="new_email_no" value="NO" />
                            <label class="form-check-label" for="new_email_no"><?php echo xlt('NO'); ?></label>
                        </div>
                    </div>
                </div>
            </form>

            <div class="col-12 text-center">
                <button class="btn btn-primary btn-add" style="float: none;" onclick="add_this_recall();" value="<?php echo xla('Add Recall'); ?>" id="add_new" name="add_new"><?php echo xlt('Add Recall'); ?></button>
                <p>
                    <em class="small text-muted">* <?php echo xlt('N.B.{{Nota bene}}') . " " . xlt('Demographic changes made here are recorded system-wide'); ?>.</em>
                </p>
            </div>
        </div>
    </div>
        <script>
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });

            $(function () {
                $('.datepicker').datetimepicker({
                        <?php $datetimepicker_timepicker = false; ?>
                        <?php $datetimepicker_showseconds = false; ?>
                        <?php $datetimepicker_formatInput = true; ?>
                        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });
                <?php
                if ($_SESSION['pid'] > '') {
                    ?>
                setpatient('<?php echo text($_SESSION['pid']); ?>');
                    <?php
                }
                ?>
            var xljs_NOTE = '<?php echo xl("NOTE"); ?>';
            var xljs_PthsApSched = '<?php echo xl("This patient already has an appointment scheduled for"); ?>';

        </script>
            <?php
    }
    public function icon_template()
    {
        ?>
    <!-- icon rubric -->
      <div class="position-relative align-middle" style="margin:30px auto;">
            <?php
            $sqlQuery = "SELECT * FROM medex_icons ORDER BY msg_type";
            $result  = sqlStatement($sqlQuery);
            $icons = array();
            while ($urow = sqlFetchArray($result)) {
                $icons['msg_type']['description'] = $urow['i_description'];
                $icons[$urow['msg_type']][$urow['msg_status']]  = $urow['i_html'];
            } ?>
                <div class="divTable w-100" style="margin:30px auto;">
                  <div class="divTableBody">
                    <div class="divTableRow divTableHeading">
                  <div class="divTableCell text-center"><?php echo xlt('Message'); ?></div>
                  <div class="divTableCell text-center"><?php echo xlt('Possible'); ?></div>
                  <div class="divTableCell text-center"><?php echo xlt('Not Possible'); ?></div>
                  <div class="divTableCell text-center"><?php echo xlt('Scheduled'); ?></div>
                  <div class="divTableCell text-center"><?php echo xlt('Sent') . "<br />" . xlt('In-process'); ?></div>
                  <div class="divTableCell text-center"><?php echo xlt('Read') . "<br />" . xlt('Delivered');
                        ; ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('Confirmed'); ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('Callback'); ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('Failure'); ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('Replies'); ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('STOP'); ?></div>
                    </div>
                    <div class="divTableRow">
                      <div class="divTableCell text-center"><?php echo xlt('EMAIL'); ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['ALLOWED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['NotAllowed']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['SCHEDULED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['SENT']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['READ']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['CONFIRMED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['CALL']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['FAILED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['EXTRA']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['EMAIL']['STOP']; ?></div>
                    </div>
                    <div class="divTableRow">
                      <div class="divTableCell text-center"><?php echo xlt('SMS'); ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['ALLOWED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['NotAllowed']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['SCHEDULED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['SENT']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['READ']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['CONFIRMED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['CALL']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['FAILED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['EXTRA']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['SMS']['STOP']; ?></div>
                    </div>
                    <div class="divTableRow">
                      <div class="divTableCell text-center"><?php echo xlt('AVM'); ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['ALLOWED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['NotAllowed']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['SCHEDULED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['SENT']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['READ']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['CONFIRMED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['CALL']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['FAILED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['EXTRA']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['AVM']['STOP']; ?></div>
                    </div>
                </div>
          </div>

            <?php
    }

/**
 *  This function displays a bootstrap responsive pop-up window containing an image of a phone with a record of our messaging activity.
 * @param $logged_in
 * @return bool
 */

    public function SMS_bot($logged_in)
    {
        $fields = array();
        $fields = $_REQUEST;
        if (!empty($_REQUEST['pid']) && $_REQUEST['pid'] != 'find') {
            $this->syncPat($_REQUEST['pid'], $logged_in);
        } elseif ($_REQUEST['show'] == 'pat_list') {
            $responseA = $this->syncPat($_REQUEST['show'], $logged_in);
            $fields['pid_list'] = $responseA['pid_list'];
            $fields['list_hits']  = $responseA['list_hits'];
        }

        $this->curl->setUrl($this->MedEx->getUrl('custom/SMS_bot&token=' . $logged_in['token'] . "&r=" . $logged_in['display']));
        $this->curl->setData($fields);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            echo $response['success'];
        } elseif (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return false;
    }

    public function TM_bot($logged_in, $data)
    {
        $fields = array();
        $fields = $data;
        if (!empty($data['pid']) && $data['pid'] != 'find') {
            $responseA = $this->syncPat($data['pid'], $logged_in);
        } elseif ($data['show'] == 'pat_list') {
            $responseA = $this->syncPat($_REQUEST['show'], $logged_in);
            $fields['pid_list'] = $responseA['pid_list'];
            $fields['list_hits']  = $responseA['list_hits'];
        }
        $fields['providerID'] = $_SESSION['authUserID'];
        $fields['token'] = $logged_in['token'];
        $fields['pc_eid'] = $data['pc_eid'];

        $this->curl->setUrl($this->MedEx->getUrl('custom/TM_bot'));
        $this->curl->setData($fields);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return $response;
    }

/**
 * This function synchronizes a patient demographic data with MedEx
 * @param $pid
 * @param $logged_in
 * @return mixed
 */
    public function syncPat($pid, $logged_in)
    {
        if ($pid == 'pat_list') {
            global $data;
            $values = rtrim($_POST['outpatient']);
            $match = preg_split("/(?<=\w)\b\s*[!?.]*/", $values, -1, PREG_SPLIT_NO_EMPTY);
            if ((preg_match('/ /', $values)) && (!empty($match[1]))) {
                $sqlSync = "SELECT * FROM patient_data WHERE (fname LIKE ? OR fname LIKE ?) AND (lname LIKE ? OR lname LIKE ?) LIMIT 20";
                $datas = sqlStatement($sqlSync, array("%" . $match[0] . "%","%" . $match[1] . "%","%" . $match[0] . "%","%" . $match[1] . "%"));
            } else {
                $sqlSync = "SELECT * FROM patient_data WHERE fname LIKE ? OR lname LIKE ? LIMIT 20";
                $datas = sqlStatement($sqlSync, array("%" . $values . "%","%" . $values . "%"));
            }

            while ($hit = sqlFetchArray($datas)) {
                $data['list'][] = $hit;
                $pid_list[] = $hit['pid'];
            }
            $this->curl->setUrl($this->MedEx->getUrl('custom/syncPat&token=' . $logged_in['token']));
            $this->curl->setData($data);
            $this->curl->makeRequest();
            $response = $this->curl->getResponse();
            $response['pid_list'] = $pid_list;
            $response['list_hits'] = $data['list'];
        } else {
            $sqlSync = "SELECT * FROM patient_data WHERE pid=?";
            $data = sqlQuery($sqlSync, array($pid));
            $this->curl->setUrl($this->MedEx->getUrl('custom/syncPat&token=' . $logged_in['token']));
            $this->curl->setData($data);
            $this->curl->makeRequest();
            $response = $this->curl->getResponse();
        }
        if (isset($response['success'])) {
            return $response;
        } elseif (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return $this->lastError;
    }
}

class Setup extends Base
{
    public function MedExBank($stage)
    {
        if ($stage == '1') {
            ?>
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div id="setup_1">
                        <div class="row">
                            <div class="col-12 text-center">
                                <h2>MedEx</h2>
                                <p class="font-italic">
                                    <?php echo xlt('Using technology to improve productivity'); ?>.
                                </p>
                            </div>
                            <div class="col-md-6 text-center">
                                <h3 class="border-bottom"><?php echo xlt('Targets'); ?>:</h3>
                                <ul class="list-group list-group-flush text-left">
                                    <li class="list-group-item"> <?php echo xlt('Appointment Reminders'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Patient Recalls'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Office Announcements'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Patient Surveys'); ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6 text-center">
                                <h3 class="border-bottom"><?php echo xlt('Channels'); ?>:</h3>
                                <ul class="list-group list-group-flush text-right">
                                    <li class="list-group-item"> <?php echo xlt('SMS Messages'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Voice Messages'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('E-mail Messaging'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Postcards'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Address Labels'); ?></li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-center row showReminders">
                            <input value="<?php echo xla('Sign-up'); ?>" onclick="goReminderRecall('setup&stage=2');" class="btn btn-primary" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <?php
        } elseif ($stage == '2') {
            ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center"><?php echo xlt('Register'); ?>: MedEx Bank</h2>
                    <form name="medex_start" id="medex_start" class="jumbotron p-5">
                        <div id="setup_1">
                            <div id="answer" name="answer">
                                <div class="form-group mt-3">
                                    <label for="new_email"><?php echo xlt('E-mail'); ?>:</label>
                                    <i id="email_check" name="email_check" class="top_right_corner nodisplay text-success fa fa-check"></i>
                                    <input type="text" data-rule-email="true" class="form-control" id="new_email" name="new_email" value="<?php echo attr($GLOBALS['user_data']['email']); ?>" placeholder="<?php echo xla('your email address'); ?>" required />
                                    <div class="signup_help nodisplay" id="email_help" name="email_help"><?php echo xlt('Please provide a valid e-mail address to proceed'); ?>...</div>
                                </div>
                                <div class="form-group mt-3">
                                    <label for="new_password"><?php echo xlt('Password'); ?>:</label>
                                    <i id="pwd_check" name="pwd_check" class="top_right_corner nodisplay text-success fa fa-check"></i>
                                    <i class="fa top_right_corner fa-question" id="pwd_ico_help" aria-hidden="true" onclick="$('#pwd_help').toggleClass('nodisplay');"></i>
                                    <input type="password" placeholder="<?php xla('Password'); ?>" id="new_password" name="new_password" class="form-control" required />
                                    <div id="pwd_help" class="nodisplay signup_help"><?php echo xlt('Secure Password Required') . ": " . xlt('8-12 characters long, including at least one upper case letter, one lower case letter, one number, one special character and no common strings'); ?>...</div>
                                </div>
                                <div class="form-group mt-3">
                                    <label for="new_rpassword"><?php echo xlt('Repeat'); ?>:</label>
                                    <i id="pwd_rcheck" name="pwd_rcheck" class="top_right_corner nodisplay text-success fa fa-check"></i>
                                    <input type="password" placeholder="<?php echo xla('Repeat password'); ?>" id="new_rpassword" name="new_rpassword" class="form-control" required />
                                    <div id="pwd_rhelp" class="nodisplay signup_help" style=""><?php echo xlt('Passwords do not match.'); ?></div>
                                </div>
                            </div>
                            <div id="ihvread" name="ihvread" class="text-left showReminders">
                                <input type="checkbox" class="updated required" name="TERMS_yes" id="TERMS_yes" required />
                                <label for="TERMS_yes" class="input-helper input-helper--checkbox" data-toggle="tooltip" data-placement="auto" title="Terms and Conditions"><?php echo xlt('I have read and my practice agrees to the'); ?>
                                    <a href="#" onclick="cascwin('https://medexbank.com/cart/upload/index.php?route=information/information&information_id=5','TERMS',800, 600);">MedEx <?php echo xlt('Terms and Conditions'); ?></a>
                                </label>
                                <br />
                                <input type="checkbox" class="updated required" name="BusAgree_yes" id="BusAgree_yes" required />
                                <label for="BusAgree_yes" class="input-helper input-helper--checkbox" data-toggle="tooltip" data-placement="auto" title="BAA"><?php echo xlt('I have read and accept the'); ?>
                                    <a href="#" onclick="cascwin('https://medexbank.com/cart/upload/index.php?route=information/information&information_id=8','Bus Assoc Agree',800, 600);">MedEx <?php echo xlt('Business Associate Agreement'); ?></a>
                                </label>
                                <br />
                                <div class="align-center showReminders">
                                    <input id="Register" class="btn btn-primary" value="<?php echo xla('Register'); ?>" />
                                </div>

                                <div id="myModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog">
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header text-white font-weight-bold" style="background-color: #0d4867;">
                                                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:1;box-shadow:unset !important;">&times;</button>
                                                <h2 class="modal-title" style="font-weight:600;">Sign-Up Confirmation</h2>
                                            </div>
                                            <div class="modal-body" style="padding: 10px 45px;">
                                                <p>You are opening a secure connection to MedExBank.com.  During this step your EHR will synchronize with the MedEx servers.  <br />
                                                    <br />
                                                    Re-enter your username (e-mail) and password in the MedExBank.com login window to:
                                                    <ul class="text-left mx-auto" style="width: 90%;">
                                                        <li> confirm your practice and providers' information</li>
                                                        <li> choose your service options</li>
                                                        <li> update and activate your messages </li>
                                                    </ul>
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-secondary" onlick="actualSignUp();" id="actualSignUp">Proceed</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        function signUp() {
            var email = $("#new_email").val();
            if (!validateEmail(email))  return alert('<?php echo xlt('Please provide a valid e-mail address to proceed'); ?>...');
            var password = $("#new_password").val();
            var passed = check_Password(password);
            if (!passed) return alert('<?php echo xlt('Passwords must be 8-12 characters long and include one capital letter, one lower case letter and one special character'); ?> ... ');
            if ($("#new_rpassword").val() !== password) return alert('<?php echo xlt('Passwords do not match'); ?>!');
            if (!$("#TERMS_yes").is(':checked')) return alert('<?php echo xlt('You must agree to the Terms & Conditions before signing up');?>... ');
            if (!$("#BusAgree_yes").is(':checked')) return alert('<?php echo xlt('You must agree to the HIPAA Business Associate Agreement');?>... ');
            $("#myModal").modal();
            return false;
        }

        function validateEmail(email) {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }
        function check_Password(password) {
            var passed = validatePassword(password, {
                length:   [8, Infinity],
                lower:    1,
                upper:    1,
                numeric:  1,
                special:  1,
                badWords: ["password", "qwerty", "12345"],
                badSequenceLength: 4
            });
            return passed;
        }
        function validatePassword (pw, options) {
            // default options (allows any password)
            var o = {
                lower:    0,
                upper:    0,
                alpha:    0, /* lower + upper */
                numeric:  0,
                special:  0,
                length:   [0, Infinity],
                custom:   [ /* regexes and/or functions */ ],
                badWords: [],
                badSequenceLength: 0,
                noQwertySequences: false,
                noSequential:      false
            };

            for (var property in options)
                o[property] = options[property];

            var re = {
                    lower:   /[a-z]/g,
                    upper:   /[A-Z]/g,
                    alpha:   /[A-Z]/gi,
                    numeric: /[0-9]/g,
                    special: /[\W_]/g
                },
                rule, i;

            // enforce min/max length
            if (pw.length < o.length[0] || pw.length > o.length[1])
                return false;

            // enforce lower/upper/alpha/numeric/special rules
            for (rule in re) {
                if ((pw.match(re[rule]) || []).length < o[rule])
                    return false;
            }

            // enforce word ban (case insensitive)
            for (i = 0; i < o.badWords.length; i++) {
                if (pw.toLowerCase().indexOf(o.badWords[i].toLowerCase()) > -1)
                    return false;
            }

            // enforce the no sequential, identical characters rule
            if (o.noSequential && /([\S\s])\1/.test(pw))
                return false;

            // enforce alphanumeric/qwerty sequence ban rules
            if (o.badSequenceLength) {
                var lower   = "abcdefghijklmnopqrstuvwxyz",
                    upper   = lower.toUpperCase(),
                    numbers = "0123456789",
                    qwerty  = "qwertyuiopasdfghjklzxcvbnm",
                    start   = o.badSequenceLength - 1,
                    seq     = "_" + pw.slice(0, start);
                for (i = start; i < pw.length; i++) {
                    seq = seq.slice(1) + pw.charAt(i);
                    if (
                        lower.indexOf(seq)   > -1 ||
                        upper.indexOf(seq)   > -1 ||
                        numbers.indexOf(seq) > -1 ||
                        (o.noQwertySequences && qwerty.indexOf(seq) > -1)
                    ) {
                        return false;
                    }
                }
            }

            // enforce custom regex/function rules
            for (i = 0; i < o.custom.length; i++) {
                rule = o.custom[i];
                if (rule instanceof RegExp) {
                    if (!rule.test(pw))
                        return false;
                } else if (rule instanceof Function) {
                    if (!rule(pw))
                        return false;
                }
            }

            // great success!
            return true;
        }
        $(function () {
            $("#Register").click(function() {
                 signUp();
            });
            $("#actualSignUp").click(function() {
                var url = "save.php?MedEx=start";
                var email = $("#new_email").val();
                $("#actualSignUp").html('<i class="fa fa-spinner fa-pulse fa-fw"></i><span class="sr-only">Loading...</span>');
                formData = $("form#medex_start").serialize();
                top.restoreSession();
                $.ajax({
                    type   : 'POST',
                    url    : url,
                    data   : formData
                    })
                .done(function(result) {
                    obj = JSON.parse(result);
                    $("#answer").html(obj.show);
                    $("#ihvread").addClass('nodisplay');
                    $('#myModal').modal('toggle');
                    if (obj.success) {
                        url="https://www.medexbank.com/login/"+email;
                        window.open(url, 'clinical', 'resizable=1,scrollbars=1');
                        refresh_me();
                    }
                });
            });
            $("#new_email").blur(function(e) {
                                e.preventDefault();
                                var email = $("#new_email").val();
                                if (validateEmail(email))  {
                                    $("#email_help").addClass('nodisplay');
                                    $("#email_check").removeClass('nodisplay');
                                } else {
                                    $("#email_help").removeClass('nodisplay');
                                    $("#email_check").addClass('nodisplay');
                                }
                            });
            $("#new_password,#new_rpassword").keyup(function(e) {
                                e.preventDefault();
                                var pwd = $("#new_password").val();
                                if (check_Password(pwd))  {
                                    $('#pwd_help').addClass('nodisplay');
                                    $("#pwd_ico_help").addClass('nodisplay');
                                    $("#pwd_check").removeClass('nodisplay');
                                } else {
                                    $("#pwd_help").removeClass('nodisplay');
                                    $("#pwd_ico_help").removeClass('nodisplay');
                                    $("#pwd_check").addClass('nodisplay');
                                }
                                if (this.id === "new_rpassword") {
                                    var pwd1 = $("#new_password").val();
                                    var pwd2 = $("#new_rpassword").val();
                                    if (pwd1 === pwd2) {
                                        $('#pwd_rhelp').addClass('nodisplay');
                                        $("#pwd_rcheck").removeClass('nodisplay');
                                    } else {
                                        $("#pwd_rhelp").removeClass('nodisplay');
                                        $("#pwd_rcheck").addClass('nodisplay');
                                    }
                                }
                            });
        });
        </script>
            <?php
        }
    }
    public function autoReg($data)
    {
        if (empty($data)) {
            return false; //throw new InvalidDataException("We need to actually send some data...");
        }
        $this->curl->setUrl($this->MedEx->getUrl('custom/signUp'));
        $this->curl->setData($data);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();
        if (isset($response['success'])) {
            return $response;
        } elseif (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return false;
    }
}

class MedEx
{
    public $lastError = '';
    public $curl;
    public $practice;
    public $campaign;
    public $events;
    public $callback;
    public $logging;
    public $display;
    public $setup;
    private $cookie;
    private $url;

    public function __construct($url, $sessionFile = 'cookiejar_MedExAPI')
    {
        global $GLOBALS;

        if ($sessionFile == 'cookiejar_MedExAPI') {
            $sessionFile = $GLOBALS['temporary_files_dir'] . '/cookiejar_MedExAPI';
        }
        $this->url      = rtrim('https://' . preg_replace('/^https?\:\/\//', '', $url), '/') . '/cart/upload/index.php?route=api/';
        $this->curl     = new CurlRequest($sessionFile);
        $this->practice = new Practice($this);
        $this->campaign = new Campaign($this);
        $this->events   = new Events($this);
        $this->callback = new Callback($this);
        $this->logging  = new Logging($this);
        $this->display  = new Display($this);
        $this->setup    = new Setup($this);
    }

    public function getCookie()
    {
        return $this->cookie; }

    public function getLastError()
    {
        return $this->lastError; }


    private function just_login($info)
    {
        if (empty($info)) {
            return;
        }

        $versionService = new VersionService();
        $version = $versionService->fetch();
        $this->curl->setUrl($this->getUrl('login'));
        $this->curl->setData(array(
        'username'  => $info['ME_username'],
        'key'       => $info['ME_api_key'],
        'UID'       => $info['MedEx_id'],
        'MedEx'     => 'OpenEMR',
        'major'     => attr($version['v_major']),
        'minor'     => attr($version['v_minor']),
        'patch'     => attr($version['v_patch']),
        'database'  => attr($version['v_database']),
        'acl'       => attr($version['v_acl']),
        'callback_key' => $info['callback_key']
        ));

        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if ($info['force'] == '1') {
            return $response;
        }

        if (!empty($response['token'])) {
            $response['practice']   = $this->practice->sync($response['token']);
            $response['generate']   = $this->events->generate($response['token'], $response['campaigns']['events']);
            $response['success']    = "200";
        }

        $sql = "UPDATE medex_prefs set status = ?";
        sqlQuery($sql, array(json_encode($response)));
        return $response;
    }

    public function login($force = '')
    {
        $info = array();
        $info = $this->getPreferences();

        if (
            empty($info) ||
            empty($info['ME_username']) ||
            empty($info['ME_api_key']) ||
            empty($info['MedEx_id']) ||
            ($GLOBALS['medex_enable'] !== '1')
        ) {
            return false;
        }
        $info['callback_key'] = $_POST['callback_key'];

        if (empty($force)) {
            $timer = strtotime($info['MedEx_lastupdated']);
            $utc_now = date('Y-m-d H:m:s');
            $hour_ago = strtotime($utc_now . "-60 minutes");
            if ($hour_ago > $timer) {
                $expired = '1';
            }
        }
        if (($expired == '1') || ($force > '0')) {
            $info['force'] = $force;
            $info = $this->just_login($info);
        } else {
            $info['status'] = json_decode($info['status'], true);
        }

        if (isset($info['error'])) {
            $this->lastError = $info['error'];
            sqlQuery("UPDATE `background_services` SET `active`='0' WHERE `name`='MedEx'");
            return $info['status'];
        } else {
            return $info['status'];
        }
    }

    public function getPreferences()
    {
        $query = "SELECT * FROM medex_prefs";
        $info = sqlFetchArray(sqlStatement($query));
        $sql = "SELECT * from background_services where name='MedEx'";
        $back = sqlFetchArray(sqlStatement($sql));
        $info['execute_interval'] = $back['execute_interval'];
        $info['active'] = $back['active'];
        $info['running'] = $back['running'];
        return $info;
    }
    public function getUrl($method)
    {
        return $this->url . $method; }

    public function checkModality($event, $appt, $icon = '')
    {
        if ($event['M_type'] == "SMS") {
            if (empty($appt['phone_cell']) || ($appt["hipaa_allowsms"] == "NO")) {
                return array($icon['SMS']['NotAllowed'],false);
            } else {
                $phone = preg_replace("/[^0-9]/", "", $appt["phone_cell"]);
                return array($icon['SMS']['ALLOWED'],$phone);     // It is allowed and they have a cell phone
            }
        } elseif ($event['M_type'] == "AVM") {
            if ((empty($appt["phone_home"]) && (empty($appt["phone_cell"])) || ($appt["hipaa_voice"] == "NO"))) {
                return array($icon['AVM']['NotAllowed'],false);
            } else {
                if (!empty($appt["phone_cell"])) {
                    $phone = preg_replace("/[^0-9]/", "", $appt["phone_cell"]);
                } else {
                    $phone = preg_replace("/[^0-9]/", "", $appt["phone_home"]);
                }
                return array($icon['AVM']['ALLOWED'],$phone); //We have a phone to call and permission!
            }
        } elseif ($event['M_type'] == "EMAIL") {
            if (($appt["email"] == "") || ($appt["hipaa_allowemail"] == "NO")) {
                return array($icon['EMAIL']['NotAllowed'],false);
            } else {
                //need to make sure this is a valid email too eh?
                return array($icon['EMAIL']['ALLOWED'],$appt["email"]);
            }
            //need to add in check for address to send postcards? - when we add in postcards...
        } else {
            return array(false,false);
        }
    }
}

class InvalidDataException extends \Exception
{
}
