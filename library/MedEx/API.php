<?php
/**
 * /library/MedEx/API.php
 *
 * @package MedEx
 * @author MedEx <support@MedExBank.com>
 * @link http://www.MedExBank.com
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

    namespace MedExApi;

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

        curl_setopt($this->handle, CURLOPT_VERBOSE, 1);
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
            /** @noinspection PhpMethodParametersCountMismatchInspection */mkdir(dirname($this->sessionFile, 0755, true));
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
        $callback = "https://".$GLOBALS['_SERVER']['SERVER_NAME'].$GLOBALS['_SERVER']['PHP_SELF'];
        $callback = str_replace('ajax/execute_background_services.php', 'MedEx/MedEx.php', $callback);
        $fields2['callback_url'] = $callback;
        //get the providers list:
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
        //get the facilities list and flag which we do messaging for (checkboxes in Preferences)
        $facilities = explode('|', $my_status['ME_facilities']);
        $runQuery = "SELECT * FROM facility WHERE service_location='1'";
        $ures = sqlStatement($runQuery);
        while ($urow = sqlFetchArray($ures)) {
            if (in_array($urow['id'], $facilities)) {
                $urow['messages_active'] = '1';
            }
            $fields2['facilities'][] = $urow;
        }
        //get the categories list:
        $runQuery = "SELECT pc_catid, pc_catname, pc_catdesc, pc_catcolor, pc_seq
                         FROM openemr_postcalendar_categories WHERE pc_active = 1 AND pc_cattype='0' ORDER BY pc_catid";
        $ures = sqlStatement($runQuery);
        while ($urow = sqlFetchArray($ures)) {
            $fields2['categories'][] = $urow;
        }
        //get apptstats
        $runQuery = "SELECT * FROM `list_options` WHERE `list_id` LIKE 'apptstat' AND activity='1'";
        $ures = sqlStatement($runQuery);
        while ($urow = sqlFetchArray($ures)) {
            $fields2['apptstats'][] = $urow;
        }
        //get definition for "Checked Out"
        $runQuery = "SELECT option_id FROM list_options WHERE toggle_setting_2='1' AND list_id='apptstat' AND activity='1'";
        $ures = sqlStatement($runQuery);
        while ($urow = sqlFetchArray($ures)) {
            $fields2['checkedOut'][] = $urow;
        }
        //get clinical reminders for practice
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
        $this->curl->setUrl($this->MedEx->getUrl('custom/addpractice&token='.$token));
        $this->curl->setData($fields2);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        //Practice: Since the last MedEx login, who has responded to one of our messages and do we know about these responses?
        //Have we deleted, cancelled or changed an appointment that we thought we were supposed to send, and now don't want to?
        //1. Check to see if anything pending was cancelled/changed.
        //2. Make sure any recalls don't have appointments just scheduled. If so no Recall message needed.
        //3. Sync any responses received on MedEx that we didn't see already.
        //     Send and Receive everything since last timestamp/update noted in medex_prefs and check.
        //Finally we may have manually made an appointment (which deletes a Recall) or manually confirmed an appt too.
        //4. We need to send this data to MedEx so it stops processing events that are confirmed/completed.

        //1. Check to see if anything pending was cancelled/changed.
        //for appts, we are just looking for appts that are flagged as 'To Send' BUT
        // were confirmed, cancelled, moved by the staff since the appt was added to table medex_outgoing...
        $sql = "SELECT * FROM medex_outgoing WHERE msg_pc_eid != 'recall_%' AND msg_reply LIKE 'To Send'";
        $test = sqlStatement($sql);
        while ($result1 = sqlFetchArray($test)) {
            $query  = "SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?";
            $test2 = sqlStatement($query, array($result1['msg_pc_eid']));
            $result2 = sqlFetchArray($test2);
            //for custom installs, insert custom apptstatus here that mean appt is not happening/changed
            if ($result2['pc_apptstatus'] =='*' ||  //confirmed
                $result2['pc_apptstatus'] =='%' ||  //cancelled < 24hour
                $result2['pc_apptstatus'] =='x' ) { //cancelled

                $sqlUPDATE = "UPDATE medex_outgoing SET msg_reply = 'DONE',msg_extra_text=? WHERE msg_uid = ?";
                sqlQuery($sqlUPDATE, array($result2['pc_apptstatus'],$result2['msg_uid']));
                //we need to update MedEx regarding actions to try to cancel
                $tell_MedEx['DELETE_MSG'][] = $result1['msg_pc_eid'];
            }
        }

        // 2. Make sure any recalls don't have appointments, ie. just a recall is scheduled
        //   Go through each recall event in medex_outgoing and check to see if it is scheduled.  If so tell MedEx.
        //   We do this when we load the Recall Board........ and now when running a cron job/background_service
        $sql = "SELECT * FROM medex_outgoing WHERE msg_pc_eid LIKE 'recall_%' GROUP BY msg_pc_eid";
        $result = sqlStatement($sql);
        while ($row = sqlFetchArray($result)) {
            $pid = trim($row['msg_pc_eid'], "recall_");
            //if there is a future appointment now in calendar, stop this recall message from going out
            $query  = "SELECT pc_eid FROM openemr_postcalendar_events WHERE (pc_eventDate > CURDATE()) AND pc_pid=?";
            $test3 = sqlStatement($query, array($pid));
            $result3 = sqlFetchArray($test3);
            if ($result3) {
                $sqlUPDATE = "UPDATE medex_outgoing SET msg_reply = 'SCHEDULED', msg_extra_text=? WHERE msg_uid = ?";
                sqlQuery($sqlUPDATE, array($result3['pc_eid'],$result2['msg_uid']));
                //we need to update MedEx regarding actions to try to cancel
                $tell_MedEx['DELETE_MSG'][] = $row['msg_pc_eid'];
            }
        }

        //3. Sync any responses received on MedEx that we didn't see already.
        //   get last update
        $sqlQuery = "SELECT * FROM medex_prefs";
        $my_status = sqlStatement($sqlQuery);
        while ($urow = sqlFetchArray($my_status)) {
            $fields3['MedEx_lastupdated']   = $urow['MedEx_lastupdated'];
            $fields3['ME_providers']        = $urow['ME_providers'];
        }
        // send notes
        $this->curl->setUrl($this->MedEx->getUrl('custom/sync_responses&token='.$token));
        $this->curl->setData($fields3);
        $this->curl->makeRequest();
        $responses = $this->curl->getResponse();

        foreach ($responses['messages'] as $data) {
            //check to see if this response is present already
            $data['msg_extra'] = $data['msg_extra']?:'';
            $sqlQuery ="SELECT * FROM medex_outgoing WHERE medex_uid=?";
            $checker = sqlStatement($sqlQuery, array($data['msg_uid']));
            if (sqlNumRows($checker)=='0') {
                $this->MedEx->callback->receive($data);
            }
        }
        $sqlUPDATE = "UPDATE medex_prefs SET MedEx_lastupdated=utc_timestamp()";
        sqlStatement($sqlUPDATE);
        //4.  sync notes
        if ($tell_MedEx['DELETE_MSG']) {
            $this->curl->setUrl($this->MedEx->getUrl('custom/remMessaging&token='.$token));
            $this->curl->setData($tell_MedEx['DELETE_MSG']);
            $this->curl->makeRequest();
            $response = $this->curl->getResponse();
        }
        if (!empty($response['found_replies'])) {
            $response['success']['message'] = xlt("Replies retrieved").": ".$response['found_replies'];
        } else {
            $response['success']['message'] = xlt("No new messages on")." MedEx.";
        }

        if (isset($response['success'])) {
            return $response;
        } else if (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return false;
    }
}

class Campaign extends Base
{
    public function events($token)
    {
        $this->curl->setUrl($this->MedEx->getUrl('custom/showEvents&token='.$token));
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            return $response;
        } else if (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return false;
    }
}

class Events extends Base
{
    /**
         *  In this function we are generating all appts that match scheduled campaign events so MedEx can conduct the campaign events.
         *
         *  This is run via MedEx_background.php or MedEx.php to find appointments that match our Campaign events and rules.
         *  Messaging Campaigns are categorized by function:
         *      There are RECALLs, REMINDERs, CLINICAL_REMINDERs, SURVEYs and ANNOUNCEments (and later whatever comes down the pike).
         *  To meet a campaign's goals, we schedule campaign events.
         *  REMINDER and RECALL campaigns each have their own events which the user creates and personalizes.
         *      There is only one REMINDER Campaign and one RECALL Campaign, each with unlimited events.
         *
         *      SURVEYs and ANNOUNCEments can have unlimited campaigns, each with their own events...
         *          You can ANNOUNCE a weather related closure (campaign) by SMS and AVM (events) going out now/ASAP
         *      AND
         *          at the same time also ANNOUNCE that in two months Dr. X will be out of the office and patient needs to reschedule (campaign)
         *          using SMS and email and voice (events) with messages going out 2 days from now, spread out over 6 business days...
         *
         *  So SURVEY AND ANNOUNCE events have parent Campaigns they are attached to
         *  but RECALLS and REMINDERS do not (they are a RECALL or a REMINDER).
         *  Clinical Reminders can be handled locally in full, some parts local, some on MedEx or all on MedEx.
         *  ie.  some practices may want to send emails themselves,
         *       but have MedEx handle the SMS and phone arms of the campaign, etc.
         *  Whatever is decided, MedEx events are created on MedEx and we are notified it exists in $MedEx->campaign->events($token).
         *  We are taking those events to build our list of recipients, they are logged in local medex_outgoing table,
         *  and are sent to MedEx to process the event.
         * @param $token
         * @param $events
         * @return mixed
         */
    public function generate($token, $events)
    {
        if (empty($events)) {
            return false; //throw new InvalidDataException("You have no Campaign Events on MedEx at this time.");
            //You have no campaign events on MedEx!
        }
        $appt3 = array();
        $count_appts='0';
        // For future appts, we don't want to run anything on the weekend so do them Friday.
        // There is a GLOBALS value for weekend days, maybe use that LTR.
        // -->If Friday, send all appts matching campaign fire_Time + 2 days
        // For past appts, we also don't want to send messages on the weekend, so do them Monday.
        foreach ($events as $event) {
            $escClause=[];
            $escapedArr = []; //will hold prepared statement items for query.
            if ($event['M_group'] == 'REMINDER') {
                if ($event['time_order'] > '0') {
                    $interval ="+";
                    //NOTE IF you have customized the pc_appstatus flags, you need to adjust them here too.
                    if ($event['E_instructions'] == "stop") {   // ie. don't send this if it has been confirmed.
                        $appt_status = " and pc_apptstatus='-'";//we only look at future appts w/ apptstatus == NONE ='-'
                        // OR send anyway - unless appstatus is not cancelled, then it is no longer an appointment to confirm...
                    } elseif ($event['E_instructions'] == "always") {  //send anyway
                        $appt_status = " and pc_apptstatus != '%'
                                             and pc_apptstatus != 'x' ";
                    } else { //reminders are always or stop, that's it
                        $event['E_instructions'] ='stop';
                        $appt_status = " and pc_apptstatus='-'";//we only look at future appts w/ apptstatus == NONE ='-'
                    }
                } else {
                    // Past appts -> appts that are completed.
                    // Need to exclude appts that were cancelled or noshowed
                    // Granular Functionality - select out via appt_stats/Visit Types/Doc/Fac is in Go Green Messaging
                    // Use the flag in the list_options to note that the appointment is completed
                    $interval ='-';// appts completed - this is defined by list_option->toggle_setting2=1 for Flow Board
                    $appt_status = " and pc_apptstatus in (SELECT option_id from list_options where toggle_setting_2='1' and list_id='apptstat')
                                         and pc_apptstatus != '%'
                                         and pc_apptstatus != 'x' ";
                }
                $timing = (int)$event['E_fire_time']-1;//the minus one is a critical change
                // if it is Friday do stuff as scheduled + 2 days more
                // for future appts, widen the net to get Timing2 = $timing +2.":1:1";
                // eg an event/message is scheduled to go out 2 days in advance - reminder SMS.
                // It is Friday.  2 days ahead is Sunday, but Monday's would run on Saturday and Tuesday's on Sunday.
                // We should run them all on Friday...  So load them that way now.
                $today=date("l");

                if (($today =="Sunday")||($today =="Saturday")) {
                    continue;
                }
                if ($today == "Friday") {
                    $timing2 = ($timing + 3).":0:1"; //this is + 3 day, 0 hour and 1 minute...
                } else {
                    $timing2 = ($timing + 1).":1:1"; //this is + 1 day, 1 hour and 1 minute...
                }
                $sql2= "SELECT ME_facilities FROM medex_prefs";
                $pref_facilities = sqlQuery($sql2);

                if (!empty($pref_facilities['ME_facilities'])) {
                    $places = str_replace("|", ",", $pref_facilities['ME_facilities']);
                    $query  = "SELECT * FROM openemr_postcalendar_events AS cal
                                    LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                                    WHERE (pc_eventDate > CURDATE() ".$interval." INTERVAL ".$timing." DAY
                                    AND pc_eventDate < (curdate() ".$interval." INTERVAL '".$timing2."' DAY_MINUTE))
                                    AND pc_facility IN (".$places.")
                                    AND pat.pid=cal.pc_pid  ORDER BY pc_eventDate,pc_startTime";

                    $result = sqlStatement($query, $escapedArr);
                    while ($appt= sqlFetchArray($result)) {
                        list($response,$results) = $this->MedEx->checkModality($event, $appt);
                        if ($results==false) {
                            continue; //not happening - either not allowed or not possible
                        }
                        $count_appts++;

                        $appt2 = [];
                        $appt2['pc_pid']        = $appt['pc_pid'];
                        $appt2['pc_eventDate']  = $appt['pc_eventDate'];
                        $appt2['pc_startTime']  = $appt['pc_startTime'];
                        $appt2['pc_eid']        = $appt['pc_eid'];
                        $appt2['pc_aid']        = $appt['pc_aid'];
                        $appt2['e_reason']      = (!empty($appt['e_reason']))?:'';
                        $appt2['e_is_subEvent_of']= (!empty($appt['e_is_subEvent_of']))?:"0";
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
            } else if ($event['M_group'] == 'RECALL') {
                if ($event['time_order'] > '0') {
                    $interval ="+";
                } else {
                    $interval ='-';
                }
                $timing = $event['E_fire_time'];

                $count_recalls ='0';
                $query  = "SELECT * FROM medex_recalls AS recall
                                LEFT JOIN patient_data AS pat ON recall.r_pid=pat.pid
                                WHERE (recall.r_eventDate < CURDATE() ".$interval." INTERVAL ".$timing." DAY)
                                ORDER BY recall.r_eventDate";
                $result = sqlStatement($query);

                while ($recall = sqlFetchArray($result)) {
                    list($response,$results) = $this->MedEx->checkModality($event, $recall);
                    if ($results==false) {
                        continue; //not happening - either not allowed or not possible
                    }
                    $show = $this->MedEx->display->show_progress_recall($recall, $event);
                    if ($show['DONE'] == '1') {
                        // Appointment was made -the Recall about to be deleted, so don't process this RECALL
                        // MedEx needs to delete this RECALL!
                        $RECALLS_completed[] = $recall;
                        continue;
                    }

                    if ($show['status']!=="reddish") {
                        // OK there is status for this recall.  Something happened.  Maybe something was sent/postcard/phone call
                        // But despite what transpired there has been no appointment yet (yellowish) or it was just
                        // made today (greenish)?  Either way, we don't want to regenerate this - don't add it to our Appt list.
                        continue;
                    }

                    // OK the list of recalls may include "older than today" recalls, they should only be included once...
                    // Otherwise you'll be sending messages every day to recalls that have passed.  Not the intended work flow...
                    // If a recall date has passed but no MedEx messages have been sent yet, we want to do something.
                    // ie. loading recalls for the first time CAN include recalls that are already due, not just upcoming.
                    // If there is more than one campaign event that could fire for these "old recalls",
                    // we are only going to do the first one.  Generate one thing for MedEx to do for this event.
                    // Maybe the practice schedules 2 RECALL Campaign Event Messages for the same day?  We would want to run both right?
                    // Yes, and for new ones, ones just due today, we will follow that directive.
                    // However we will limit recalls that are in the past to one event!
                    // Note:  This only is an issue the first time loading a recall whose due date has already passed.
                    // If a new recall is added for this patient eg. two years from now we need a new one,
                    // the old one will be deleted from memory.
                    if (strtotime($recall['r_eventDate']) < mktime(0, 0, 0)) {
                        if ($this->recursive_array_search("recall_".$recall['r_pid'], $appt3)) {
                            continue;
                        }
                    }

                    $count_recalls++;
                    $recall2 = array();
                    $recall2['pc_pid']        = $recall['r_pid'];
                    $recall2['pc_eventDate']  = $recall['r_eventDate'];
                    $recall2['pc_startTime']  = '10:42:00';
                    $recall2['pc_eid']        = "recall_".$recall['r_pid'];
                    $recall2['pc_aid']        = $recall['r_provider'];
                    $recall2['e_is_subEvent_of']= "0";
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
            } else if ($event['M_group'] == 'ANNOUNCE') {
                if (empty($event['start_date'])) {
                    continue;
                }
                $now = strtotime('now');
                $delivery_date = strtotime($event['start_date']);

                if ($now < $delivery_date) {
                    continue;
                }
                if ((!empty($event['appts_start'])) && (empty($event['appts_end']))) {
                        $target_dates = "pc_eventDate = ?";
                        $escapedArr[]=$event['appts_start'];
                } else if ((!empty($event['appts_start'])) && (!empty($event['appts_end']))) {
                    $target_dates = "pc_eventDate >= ? and pc_eventDate <= ?";
                    $escapedArr[]=$event['appts_start'];
                    $escapedArr[]=$event['appts_end'];
                } else {
                    continue;
                    //N.B. we shouldn't get here so move to next event if we do
                }

                if (!empty($event['appt_stats'])) {
                    $prepare_me ='';
                    $appt_stats = explode('|', $event['appt_stats']);
                    foreach ($appt_stats as $appt_stat) {
                        $prepare_me .= "?,";
                        $escapedArr[]=$appt_stat;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $appt_status = " AND cal.pc_apptstatus in (".$prepare_me.") ";
                } else {
                    $appt_status ='';
                }

                if (!empty($event['providers'])) {
                    $prepare_me ='';
                    $providers = explode('|', $event['providers']);
                    foreach ($providers as $provider) {
                        $prepare_me .= "?,";
                        $escapedArr[]=$provider;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $providers = " AND cal.pc_aid in (".$prepare_me.") ";
                } else {
                    $providers ='';
                }

                if (!empty($event['facilities'])) {
                    $prepare_me ='';
                    $facilities = explode('|', $event['facilities']);
                    foreach ($facilities as $facility) {
                        $prepare_me .= "?,";
                        $escapedArr[]=$facility;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $places = " AND cal.pc_facility in (".$prepare_me.") ";
                } else {
                    $places ='';
                }

                if (!empty($event['visit_types'])) {
                    $prepare_me ='';
                    $visit_types = explode('|', $event['visit_types']);
                    foreach ($visit_types as $visit_type) {
                        $prepare_me .= "?,";
                        $escapedArr[]=$visit_type;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $visit_types = " AND cal.pc_catid in (".$prepare_me.") ";
                } else {
                    $visit_types ='';
                }

                    $sql_ANNOUNCE = "SELECT * FROM openemr_postcalendar_events AS cal
                                    LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                                    WHERE (".$target_dates.")
                                        ".$appt_status."
                                        ".$providers."
                                        ".$places."
                                        ".$visit_types."
                                    ORDER BY pc_eventDate,pc_startTime";
                    $result = sqlStatement($sql_ANNOUNCE, $escapedArr);

                while ($appt= sqlFetchArray($result)) {
                    list($response,$results) = $this->MedEx->checkModality($event, $appt);
                    if ($results==false) {
                        continue; //not happening - either not allowed or not possible
                    }
                    $req_appt = print_r($appt, true);
                    $count_announcements++;

                    $appt2 = array();
                    $appt2['pc_pid']        = $appt['pc_pid'];
                    $appt2['pc_eventDate']  = $appt['pc_eventDate'];
                    $appt2['pc_startTime']  = $appt['pc_startTime'];
                    $appt2['pc_eid']        = $event['C_UID'].'_'.$appt['pc_eid'];
                    $appt2['pc_aid']        = $appt['pc_aid'];
                    $appt2['e_reason']      = (!empty($appt['e_reason']))?:'';
                    $appt2['e_is_subEvent_of']= (!empty($appt['e_is_subEvent_of']))?:"0";
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
            } else if ($event['M_group'] == 'SURVEY') {
                // Look at appts on a per-provider basis for now...
                if (empty($event['timing'])) {
                    $event['timing'] = "180";
                }
                // appts completed - this is defined by list_option->toggle_setting2=1 for Flow Board
                $appt_status = " and pc_apptstatus in (SELECT option_id from list_options where toggle_setting_2='1' and list_id='apptstat') ";
                //if we are to refine further, to not limit by completed status but with a specific appt_stats
                // eg survey people who left without appt or
                //T_appt_stats = array -> list of appstat(s) to restrict event to in a ',' separated list
                if (!empty($event['T_appt_stats'])) {
                    foreach ($event['T_appt_stats'] as $stat) {
                        $escapedArr[] = $stat;
                        $escClause['Stat'] .= "?,";
                    }
                    rtrim($escStat, ",");
                    $appt_status = " and pc_appstatus in (".$escClause['Stat'].") ";
                }

                $sql2= "SELECT * FROM medex_prefs";
                $pref = sqlQuery($sql2);
                //if we are refining survey by facility
                $facility_clause = '';
                if (!empty($event['T_facilities'])) {
                    foreach ($event['T_facilities'] as $fac) {
                        $escapedArr[] = $fac;
                        $escClause['Fac'] .= "?,";
                    }
                    rtrim($escFac, ",");
                    $facility_clause = " AND cal.pc_facility in (".$escClause['Fac'].") ";
                }
                $all_providers = explode('|', $pref['ME_providers']);
                foreach ($event['survey'] as $k => $v) {
                    if (($v <= 0) || (empty($event['providers'])) || (!in_array($k, $all_providers))) {
                        continue;
                    }
                    $escapedArr[] = $k;
                    $query  = "SELECT * FROM openemr_postcalendar_events AS cal
                                    LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                                    WHERE (
                                        cal.pc_eventDate > CURDATE() - INTERVAL ".$event['timing']." DAY AND
                                        cal.pc_eventDate < CURDATE() - INTERVAL 3 DAY) AND
                                        pat.pid=cal.pc_pid AND
                                        pc_apptstatus !='%' AND
                                        pc_apptstatus != 'x' ".
                                    $appt_status.
                                    $facility_clause."
                                         AND cal.pc_aid IN (?)
                                         AND email > ''
                                         AND hipaa_allowemail NOT LIKE 'NO'
                                        GROUP BY pc_pid
                                        ORDER BY pc_eventDate,pc_startTime
                                        LIMIT ".$v;
                    //Survey opt-out option should feed into here also but there is no field for it in patient_data
                    //MedEx tracks opt-out requests however so unless there are a lot, it won't matter here.
                    $result = sqlStatement($query, $escapedArr);
                    while ($appt= sqlFetchArray($result)) {
                        list($response,$results) = $this->MedEx->checkModality($event, $appt);
                        if ($results==false) {
                            continue; //not happening - either not allowed or not possible
                        }
                        $appt2 = array();
                        $appt2['pc_pid']        = $appt['pc_pid'];
                        $appt2['pc_eventDate']  = $appt['pc_eventDate'];
                        $appt2['pc_startTime']  = $appt['pc_startTime'];
                        $appt2['pc_eid']        = $appt['pc_eid'];
                        $appt2['pc_aid']        = $appt['pc_aid'];
                        $appt2['e_reason']      = (!empty($appt['e_reason']))?:'';
                        $appt2['e_is_subEvent_of']= (!empty($appt['e_is_subEvent_of']))?:"0";
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
                    }
                }
            } else if ($event['M_group'] == 'CLINICAL_REMINDER') {
                continue;
                // from function fetch_reminders($patient_id = '', $type = '', $due_status = '', $select = '*')
                // we need to run the update_reminders first... Use the batch feature probably.
                // Perhaps in MedEx.php?
                // $MedEx->campaign->events($token); to get the list of things we need to do:
                //  eg.  category and item.
                // $sql = "SELECT * FROM `patient_reminders` where `active`='1' AND
                //      `date_sent` IS NULL ORDER BY `patient_reminders`.`due_status` DESC ";
                //   and category=$CR['category']
                //  MedEx needs this - we are extrapolating it's name for display and item = $CR['item'] (M_name)
                // We should already have the patient id in the $appt array.
                // We should also have the reminder info we are targeting.
                // Then we just need to the matching patient data.
                // Check if possible_modalities here to see if the event is even possible?
                // Think we should do that in $MedEx->campaign->events($token);
                // if not possible we will need to add that info into a Clinical Reminders Flow Board........ visually.
                // We will need to create this which means we need to overhaul the whole reminders code too?
                //Maybe not.

                //then this
                $sql = "SELECT * FROM `patient_reminders`,`patient_data`
                              WHERE
                            `patient_reminders`.pid ='".$event['PID']."' AND
                            `patient_reminders`.active='1' AND
                            `patient_reminders`.date_sent IS NULL AND
                            `patient_reminders`.pid=`patient_data`.pid
                              ORDER BY `due_status`, `date_created`";
                $ures = sqlStatementCdrEngine($sql);
                $returnval=array();
                while ($urow = sqlFetchArray($ures)) {
                    list($response,$results) = $this->MedEx->checkModality($event, $urow);
                    if ($results==false) {
                        continue; //not happening - either not allowed or not possible
                    }
                    $fields2['clinical_reminders'][] = $urow;
                }
            } else if ($event['M_group'] == 'GOGREEN') {
                $count=0;
                $escapedArr=[];
                if (!empty($event['appt_stats'])) {
                    $prepare_me ='';
                    $appt_stats = explode('|', $event['appt_stats']);
                    foreach ($appt_stats as $appt_stat) {
                        $prepare_me .= "?,";
                        $escapedArr[]=$appt_stat;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $appt_status = " AND cal.pc_apptstatus in (".$prepare_me.") ";
                } else {
                    $appt_status ='';
                }

                if (!empty($event['providers'])) {
                    $prepare_me ='';
                    $providers = explode('|', $event['providers']);
                    foreach ($providers as $provider) {
                        $prepare_me .= "?,";
                        $escapedArr[]=$provider;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $providers = " AND cal.pc_aid in (".$prepare_me.") ";
                } else {
                    $providers ='';
                }

                if (!empty($event['facilities'])) {
                    $prepare_me ='';
                    $facilities = explode('|', $event['facilities']);
                    foreach ($facilities as $facility) {
                        $prepare_me .= "?,";
                        $escapedArr[]=$facility;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $places = " AND cal.pc_facility in (".$prepare_me.") ";
                } else {
                    $places ='';
                }

                if (!empty($event['visit_types'])) {
                    $prepare_me ='';
                    $visit_types = explode('|', $event['visit_types']);
                    foreach ($visit_types as $visit_type) {
                        $prepare_me .= "?,";
                        $escapedArr[]=$visit_type;
                    }
                    $prepare_me = rtrim($prepare_me, ",");
                    $visit_types = " AND cal.pc_catid in (".$prepare_me.") ";
                } else {
                    $visit_types ='';
                }

                $frequency ='';
                if ($event['E_instructions'] == 'once') {
                    //once - this patient only gets one
                    $frequency = " AND cal.pc_pid NOT in (
                            SELECT msg_pid from medex_outgoing where
                                campaign_uid =? )";
                     $escapedArr[] = (int)$event['C_UID'];
                } else if ($event['E_instructions'] == 'yearly') {
                    //yearly - this patient only gets this once a year
                    $frequency = " AND cal.pc_pid NOT in (
                            SELECT msg_pid from medex_outgoing where
                                campaign_uid =? and
                                msg_date > curdate() - interval 1 year )";
                    $escapedArr[] = (int)$event['C_UID'];
                }
                //otherwise Campaign was set to send with each appointment...

                //make sure this only gets sent once for this campaign
                $no_dupes = " AND cal.pc_eid NOT IN (
                                SELECT msg_pc_eid from medex_outgoing where
                                campaign_uid =? ) ";
                $escapedArr[] = (int)$event['C_UID'];
                // don't waste our time with people w/o email addresses
                // Go Green is just EMAIL for now
                $no_dupes .= "AND pat.email >'' ";
                //now we need to look to see if this is timed around an event occurrence

                $target_dates ='';
                if ($event['E_timing'] == '5') {
                    $target_dates = " cal.pc_eventDate > curdate()  ";
                } else {
                    if (!is_numeric($event['E_fire_time'])) { //this would be an error in building the event
                        $event['E_fire_time'] ='0';
                    }
                    $timing = (int)$event['E_fire_time'];
                    if (($event['E_timing'] == '1') || ($event['E_timing'] == '2')) {
                        //then this is X days prior to appt
                        $target_dates = "cal.pc_eventDate = curdate() + interval ".$timing." day ";
                        if ($today == "Friday") {
                            $timing2 = ($timing + 3);
                            $target_dates = "cal.pc_eventDate >= curdate() + interval ".$timing." day  AND cal.pc_eventDate < (curdate() + INTERVAL '".$timing2."' DAY) ";
                        }
                    } else if (($event['E_timing'] == '3') || ($event['E_timing'] == '4')) {
                        //then this is X days post appt
                        $interval = "-";
                        $target_dates = "cal.pc_eventDate = curdate() - interval ".$timing." day";
                        if ($today == "Monday") {
                            $timing2 = ($timing + 3);
                            $target_dates .= "cal.pc_eventDate <= curdate() - interval ".$timing." day AND cal.pc_eventDate > (curdate() - INTERVAL '".$timing2."' DAY) ";
                        }
                    }
                }
                $sql_GOGREEN = "SELECT * FROM openemr_postcalendar_events AS cal
                                    LEFT JOIN patient_data AS pat ON cal.pc_pid=pat.pid
                                    WHERE
                                        ".$target_dates."
                                        ".$appt_status."
                                        ".$providers."
                                        ".$places."
                                        ".$visit_types."
                                        ".$frequency."
                                        ".$no_dupes."
                                    ORDER BY cal.pc_eventDate,cal.pc_startTime";
                $result = sqlStatement($sql_GOGREEN, $escapedArr);
                while ($appt= sqlFetchArray($result)) {
                    list($response,$results) = $this->MedEx->checkModality($event, $appt);
                    if ($results==false) {
                            continue; //not happening - either not allowed or not possible
                    }
                    $count_appts++;
                    continue;
                    $appt2 = array();
                    $appt2['pc_pid']        = $appt['pc_pid'];
                    $appt2['pc_eventDate']  = $appt['pc_eventDate'];
                    $appt2['pc_startTime']  = $appt['pc_startTime'];
                    $appt2['pc_eid']        = $appt['pc_eid'];
                    $appt2['pc_aid']        = $appt['pc_aid'];
                    $appt2['e_reason']      = (!empty($appt['e_reason']))?:'';
                    $appt2['e_is_subEvent_of']= (!empty($appt['e_is_subEvent_of']))?:"0";
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
        $responses['deletes'] = $hipaa;
        $responses['count_appts'] = $count_appts;
        $responses['count_recalls'] = $count_recalls;
        $responses['count_announcements'] = $count_announcements;
        $responses['count_surveys'] = $count_surveys;
        $responses['count_clinical_reminders'] = $count_clinical_reminders;

        return $responses;
    }

    private function recursive_array_search($needle, $haystack)
    {
        foreach ($haystack as $key => $value) {
            $current_key=$key;
            if ($needle===$value or (is_array($value) && $this->recursive_array_search($needle, $value))) {
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
        $this->curl->setUrl($this->MedEx->getUrl('custom/remRecalls&token='.$token));
        $this->curl->setData($data);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            return $response;
        } else if (isset($response['error'])) {
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
        $data= array();

        foreach ($appts as $appt) {
            $data['appts'][] = $appt;
            $sqlUPDATE = "UPDATE medex_outgoing SET msg_reply=?, msg_extra_text=?, msg_date=NOW()
                                    WHERE msg_pc_eid=? AND campaign_uid=? AND msg_type=? AND msg_reply='To Send'";
            sqlQuery($sqlUPDATE, array($appt['reply'],$appt['extra'],$appt['pc_eid'],$appt['C_UID'], $appt['M_type']));
            if (count($data['appts'])>'100') {
                $this->curl->setUrl($this->MedEx->getUrl('custom/loadAppts&token='.$token));
                $this->curl->setData($data);
                $this->curl->makeRequest();
                $response   = $this->curl->getResponse();
                $data       = array();
                sleep(1);
            }
        }
        //finish those $data < 100.
        $this->curl->setUrl($this->MedEx->getUrl('custom/loadAppts&token='.$token));
        $this->curl->setData($data);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            return $response;
        } else if (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return false;
    }

    public function save_recall($saved)
    {
        $this->delete_Recall();
        $mysqldate = DateToYYYYMMDD($_REQUEST['form_recall_date']);
        $queryINS = "INSERT INTO medex_recalls (r_pid,r_reason,r_eventDate,r_provider,r_facility)
                            VALUES (?,?,?,?,?)
                            ON DUPLICATE KEY
                            UPDATE r_reason=?, r_eventDate=?, r_provider=?,r_facility=?";
        $result = sqlStatement($queryINS, array($_REQUEST['new_pid'],$_REQUEST['new_reason'],$mysqldate,$_REQUEST['new_provider'],$_REQUEST['new_facility'],$_REQUEST['new_reason'],$mysqldate,$_REQUEST['new_provider'],$_REQUEST['new_facility']));
        $query = "UPDATE patient_data
                        SET phone_home=?,phone_cell=?,email=?,
                            hipaa_allowemail=?,hipaa_voice=?,hipaa_allowsms=?,
                            street=?,postal_code=?,city=?,state=?
                        WHERE pid=?";
        $sqlValues = array($_REQUEST['new_phone_home'],$_REQUEST['new_phone_cell'],$_REQUEST['new_email'],
                            $_REQUEST['new_email_allow'],$_REQUEST['new_voice'],$_REQUEST['new_allowsms'],
                            $_REQUEST['new_address'],$_REQUEST['new_postal_code'],$_REQUEST['new_city'],$_REQUEST['new_state'],
                            $_REQUEST['new_pid']);
        $result = sqlStatement($query, $sqlValues);
        return;
    }
    public function delete_Recall()
    {
        $sqlQuery = "DELETE FROM medex_recalls WHERE r_pid=? OR r_ID=?";
        sqlStatement($sqlQuery, array($_POST['pid'],$_POST['r_ID']));

        $sqlDELETE = "DELETE FROM medex_outgoing WHERE msg_pc_eid = ?";
        sqlStatement($sqlDELETE, array('recall_'.$_POST['pid']));
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
}

    /**
     *  Process updates and message replies received from MedEx.
     *  Lets MedEx know if we did anything manually to a queued event.
     */
class Callback extends Base
{
    public function receive($data = '')
    {
        if ($data=='') {
            $data = $_POST;
        }
        if (empty($data['campaign_uid'])) {
          //  throw new InvalidDataException("There must be a Campaign to update...");
            $response['success'] = "No campaigns to process.";
        }
         //why aren't they the same??
        if (!$data['patient_id']) {  //process AVM responses??
            if ($data['e_pid']) {
                $data['patient_id'] = $data['e_pid'];
            } else if ($data['pc_eid']) {  //process responses from callback into MedEx.php - no pid just pc_eid
                $query = "SELECT * FROM openemr_postcalendar_events WHERE pc_eid=?"; //assume one patient per appointment pc_eid/slot...
                $patient = sqlFetchArray(sqlStatement($query, array($data['pc_eid'])));  //otherwise this will need to be a loop
                $data['patient_id'] = $patient['pid'];
            }
        }
        if ($data['patient_id']) {
            //Store responses in TABLE medex_outgoing
            $sqlINSERT = "INSERT INTO medex_outgoing (msg_pc_eid, msg_pid, campaign_uid, msg_type, msg_reply, msg_extra_text, msg_date, medex_uid)
                                VALUES (?,?,?,?,?,?,utc_timestamp(),?)";

            sqlQuery($sqlINSERT, array($data['pc_eid'],$data['patient_id'], $data['campaign_uid'], $data['M_type'],$data['msg_reply'],$data['msg_extra'],$data['msg_uid']));

            if ($data['msg_reply']=="CONFIRMED") {
                $sqlUPDATE = "UPDATE openemr_postcalendar_events SET pc_apptstatus = ? WHERE pc_eid=?";
                sqlStatement($sqlUPDATE, array($data['msg_type'],$data['pc_eid']));
                //need to insert this into patient_tracker
                $query = "SELECT * FROM patient_tracker WHERE eid=?";
                $tracker = sqlFetchArray(sqlStatement($query, array($data['pc_eid'])));  //otherwise this will need to be a loop
                #Update lastseq in tracker.
                sqlStatement(
                    "UPDATE `patient_tracker` SET  `lastseq` = ? WHERE eid=?",
                    array(($tracker['lastseq']+1),$data['pc_eid'])
                );
                #Add a tracker item.
                $datetime = date("Y-m-d H:i:s");
                sqlInsert(
                    "INSERT INTO `patient_tracker_element` " .
                            "(`pt_tracker_id`, `start_datetime`, `user`, `status`, `seq`) " .
                            "VALUES (?,?,?,?,?)",
                    array($tracker['id'],$datetime,'MedEx',$data['msg_type'],($tracker['lastseq']+1))
                );
            } elseif ($data['msg_reply']=="CALL") {
                $sqlUPDATE = "UPDATE openemr_postcalendar_events SET pc_apptstatus = 'CALL' WHERE pc_eid=?";
                $test = sqlQuery($sqlUPDATE, array($data['pc_eid']));
                //this requires attention.  Send up the FLAG!
                //$this->MedEx->logging->new_message($data);
            } elseif (($data['msg_type']=="AVM") && ($data['msg_reply']=="STOP")) {
                //if reply = "STOP" update patient demographics to disallow this mode of communication
                $sqlUPDATE = "UPDATE patient_data SET hipaa_voice = 'NO' WHERE pid=?";
                sqlQuery($sqlUPDATE, array($data['patient_id']));
            } elseif (($data['msg_type']=="SMS") && ($data['msg_reply']=="STOP")) {
                $sqlUPDATE = "UPDATE patient_data SET hipaa_allowsms = 'NO' WHERE pid=?";
                sqlQuery($sqlUPDATE, array($data['patient_id']));
            } elseif (($data['msg_type']=="EMAIL") && ($data['msg_reply']=="STOP")) {
                $sqlUPDATE = "UPDATE patient_data SET hipaa_allowemail = 'NO' WHERE pid=?";
                sqlQuery($sqlUPDATE, array($data['patient_id']));
            }
            if (($data['msg_type']=="SMS")&&($data['msg_reply']=="Other")) {
                //ideally we would be incrementing the "new Message Icon", perhaps using:
                //$this->MedEx->logging->new_message($data);
            }
            if (($data['msg_reply']=="SENT")||($data['msg_reply']=="READ")) {
                $sqlDELETE = "DELETE FROM medex_outgoing WHERE msg_pc_eid=? AND msg_reply='To Send'";
                sqlQuery($sqlDELETE, array($data['pc_eid']));
            }
            $response['comments']   = $data['pc_eid']." - ".$data['campaign_uid']." - ".$data['msg_type']." - ".$data['reply']." - ".$data['extra'];
            $response['pid']        = $data['patient_id'];
            $response['success']    = $data['msg_type']." reply";
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
        $std_log = fopen($log, 'a');// or die(print_r(error_get_last(), true));
        $timed = date(DATE_RFC2822);
        fputs($std_log, "**********************\nlibrary/MedEx/API.php fn log_this(data):  ".$timed."\n");
        if (is_array($data)) {
            $dumper = print_r($data, true);
            fputs($std_log, $dumper);
            foreach ($data as $key => $value) {
                fputs($stdlog, $key.": ".$value."\n");
            }
        } else {
            fputs($std_log, "\nDATA= ".$data. "\n");
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
                    $.post( "<?php echo $GLOBALS['webroot']."/interface/main/messages/messages.php"; ?>", {
                        'setting_bootstrap_submenu' : 'show',
                        success: function (data) {
                            x.style.display = 'block';
                        }
                        });

                } else {
                    $.post( "<?php echo $GLOBALS['webroot']."/interface/main/messages/messages.php"; ?>", {
                        'setting_bootstrap_submenu' : 'hide',
                        success: function (data) {
                            x.style.display = 'none';
                        }
                    });
                }
            $("#patient_caret").toggleClass('fa-caret-up').toggleClass('fa-caret-down');
            }

            /**
             * @return {boolean}
             */
            function SMS_bot_list() {
                top.restoreSession();
                var myWindow = window.open('<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/messages.php?nomenu=1&go=SMS_bot&dir=back&show=new','SMS_bot', 'width=383,height=600,resizable=0');
                myWindow.focus();
                return false;
            }
            </script>
            <i class="fa fa-caret-<?php
            if ($setting_bootstrap_submenu == 'hide') {
                echo 'down';
            } else {
                echo 'up';
            } ?> menu_arrow" style="position:fixed;left:5px;top:5px;z-index:1099;" id="patient_caret" onclick='toggle_menu();' aria-hidden="true"></i>
            <div id="hide_nav" style="<?php if ($setting_bootstrap_submenu == 'hide') {
                echo "display:none;"; } ?>">
                <nav id="navbar_oe" class="bgcolor2 navbar-fixed-top navbar-custom navbar-bright navbar-inner" name="kiosk_hide"
                    data-role="page banner navigation">
                    <!-- Brand and toggle get grouped for better mobile display -->
                        <div class="container-fluid">
                        <div class="navbar-header brand">
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#oer-navbar-collapse-1">
                                <span class="sr-only"><?php echo xlt("Toggle navigation"); ?></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        </div>
                        <div class="navbar-collapse collapse" id="oer-navbar-collapse-1">
                            <ul class="navbar-nav">
                                <?php
                                if ($GLOBALS['medex_enable'] == '1') {
                                    ?>
                                    <li class="dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_file" role="button" aria-expanded="true"><?php echo xlt("File"); ?> </a>
                                        <ul class="bgcolor2 dropdown-menu" role="menu">
                                            <?php
                                            if ($logged_in) {
                                                ?>
                                                <li id="menu_PREFERENCES"  name="menu_PREFERENCES" class=""><a onclick="tabYourIt('prefs','main/messages/messages.php?go=Preferences');"><?php echo xlt("Preferences"); ?></a></li>
                                                <li id="icons" name="icons"><a onclick="doRecallclick_edit('icons');"><?php echo xlt('Icon Legend'); ?></a></li>
                                                <?php
                                            } else {
                                                ?>
                                                <li id="menu_PREFERENCES"  name="menu_PREFERENCES" class="">
                                                <a href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?go=setup&stage=1"><?php echo xlt("Setup MedEx"); ?></a></li>
                                                <?php
                                            }
                                            ?>
                                         </ul>
                                    </li>
                                    <?php
                                }
                                ?>

                                <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_msg" role="button" aria-expanded="true"><?php echo xlt("Messages"); ?> </a>
                                <ul class="bgcolor2 dropdown-menu" role="menu">
                                    <li id="menu_new_msg"> <a href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?showall=no&sortby=users.lname&sortorder=asc&begin=0&task=addnew&form_active=1"> <?php echo xlt("New Message"); ?></a></li>

                                    <li class="divider"><hr /></li>

                                    <li id="menu_new_msg"> <a href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?show_all=no&form_active=1"> <?php echo xlt("My Messages"); ?></a></li>
                                    <li id="menu_all_msg"> <a href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?show_all=yes&form_active=1"> <?php echo xlt("All Messages"); ?></a></li>

                                    <li class="divider"><hr /></li>

                                    <li id="menu_active_msg"> <a href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?show_all=yes&form_active=1"> <?php echo xlt("Active Messages"); ?></a></li>
                                    <li id="menu_inactive_msg"> <a href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?form_inactive=1"> <?php echo xlt("Inactive Messages"); ?></a></li>
                                    <li id="menu_log_msg"> <a onclick="openLogScreen();" > <?php echo xlt("Message Log"); ?></a></li>
                                </ul>
                                </li>
                                <li class="dropdown" > <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_recalls" role="button" aria-expanded="true"><?php echo xlt("Appt. Reminders"); ?> </a>
                                <ul class="bgcolor2 dropdown-menu" role="menu">
                                    <?php
                                    if ($GLOBALS['disable_calendar'] != '1') {  ?>
                                        <li><a id="BUTTON_ApRem_menu" onclick="tabYourIt('cal','main/main_info.php');"> <?php echo xlt("Calendar"); ?></a></li>
                                        <li class="divider"><hr /></li>
                                        <?php
                                    }
                                    if ($GLOBALS['disable_pat_trkr'] != '1') {
                                        ?>
                                        <li id="menu_pend_recalls" name="menu_pend_recalls"> <a id="BUTTON_pend_recalls_menu" onclick="tabYourIt('flb','patient_tracker/patient_tracker.php?skip_timeout_reset=1');"> <?php echo xlt("Flow Board"); ?></a></li>
                                        <?php }
                                    if ($logged_in) {
                                        ?>
                                        <li class="divider"><hr /></li>
                                        <li id="menu_pend_recalls" name="menu_pend_recalls"> <a href='https://medexbank.com/cart/upload/index.php?route=information/campaigns&g=rem' target="_medex" class='nowrap text-left' id="BUTTON_pend_recalls_menu"> <?php echo xlt("Reminder Campaigns"); ?></a></li>
                                            <?php
                                    }
                                    ?>
                                    </ul>
                                 </li>
                                    <?php

                                    if ($GLOBALS['disable_rcb'] != '1') { ?>
                                        <li class="dropdown">
                                            <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_recalls" role="button" aria-expanded="true"><?php echo xlt("Patient Recalls"); ?> </a>
                                            <ul class="bgcolor2 dropdown-menu" role="menu">
                                                <li id="menu_new_recall" name="menu_new_recall"> <a id="BUTTON_new_recall_menu" onclick="tabYourIt('rcb','main/messages/messages.php?go=addRecall');"> <?php echo xlt("New Recall"); ?></a></li>
                                                <li id="menu_pend_recalls" name="menu_pend_recalls"> <a  onclick="goReminderRecall('Recalls');" id="BUTTON_pend_recalls_menu" href="#"> <?php echo xlt("Recall Board"); ?></a></li>
                                            <?php
                                            if ($logged_in) {
                                                ?>
                                                <li class="divider"><hr /></li>
                                                <li id="menu_pend_recalls" name="menu_pend_recalls"> <a href='https://medexbank.com/cart/upload/index.php?route=information/campaigns&g=rec' target="_medex" class='nowrap text-left' id="BUTTON_pend_recalls_menu"> <?php echo xlt("Recall Campaigns"); ?></a></li>
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
                        </div><!-- /.navbar-collapse -->
                    </div>
                </nav>
            </div>
            <?php
            if ($GLOBALS['medex_enable'] == '1') {
                $error=$this->MedEx->getLastError();
                if (!empty($error['ip'])) {
                    ?>
                    <div class="alert alert-danger" style="width:50%;margin:30px auto 5px;font-size:0.9em;text-align:center;">
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
            $prefs = sqlFetchArray(sqlStatement("SELECT * FROM medex_prefs"));
        }
        ?>
        <div class="row">
            <div class="col-sm-12 text-center">
                <div class="showRecalls" id="show_recalls">
                    <div class="title">MedEx <?php echo xlt('Preferences'); ?></div>
                    <div name="div_response" id="div_response" class="form-inline"><br />
                    </div>
                    <form action="#" name="save_prefs" id="save_prefs">
                            <div class="row">
                                <input type="hidden" name="go" id="go" value="Preferences">
                                <div class="col-sm-5 div-center col-sm-offset-1" id="daform2">
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
                                                    if ($prefs['ME_hipaa_default_override']=='1') {
                                                        echo 'checked ="checked"';
                                                    }
                                                    ?> >
                                                    <label for="ME_hipaa_default_override" class="input-helper input-helper--checkbox"
                                                           data-toggle='tooltip'
                                                           data-placement='auto right'
                                                           title='<?php echo xla('Default'); ?>: "<?php echo xla('checked'); ?>".
                                                            <?php echo xla('When checked, messages are processed for patients with Patient Demographic Choice: "Hipaa Notice Received" set to "Unassigned" or "Yes". When unchecked, this choice must = "YES" to process the patient reminder. For patients with Choice ="No", Reminders will need to be processed manually.'); //or no translation... ?>'>
                                                            <?php echo xlt('Assume patients receive HIPAA policy'); ?>
                                                     </label><br />
                                                     <input type="checkbox" class="update" name="MSGS_default_yes" id="MSGS_default_yes" value="1" <?php if ($prefs['MSGS_default_yes']=='1') {
                                                                echo "checked='checked'";} ?>>
                                                        <label for="MSGS_default_yes" class="input-helper input-helper--checkbox" data-toggle="tooltip" data-placement="auto" title="<?php echo xla('Default: Checked. When checked, messages are processed for patients with Patient Demographic Choice (Phone/Text/Email) set to \'Unassigned\' or \'Yes\'. If this is unchecked, a given type of message can only be sent if its Demographic Choice = \'Yes\'.'); ?>">
                                                            <?php echo xlt('Assume patients permit Messaging'); ?></label>
                                                    </div>
                                                </div>
                                                <div class="divTableRow">
                                                    <div class="divTableCell divTableHeading"><?php echo xlt('Enable Facility'); ?></div>
                                                    <div class="divTableCell indent20">
                                                    <?php
                                                    $count="1";
                                                    $query = "SELECT * FROM facility";
                                                    $result = sqlStatement($query);
                                                    while ($fac = sqlFetchArray($result)) {
                                                        $checked ="";
                                                        if ($prefs) {
                                                            $facs = explode('|', $prefs['ME_facilities']);
                                                            foreach ($facs as $place) {
                                                                if ($place == $fac['id']) {
                                                                    $checked = 'checked ="checked"';
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <input <?php echo $checked; ?> class="update" type="checkbox" name="facilities[]" id="facility_<?php echo attr($fac['id']); ?>" value="<?php echo attr($fac['id']); ?>">
                                                        <label for="facility_<?php echo attr($fac['id']); ?>"><?php echo text($fac['name']); ?></label><br /><?php
                                                    }
                                                    ?>
                                                    </div>
                                                </div>
                                                <div class="divTableRow">
                                                    <div class="divTableCell divTableHeading"><?php echo xlt('Included Providers'); ?></div>
                                                    <div class="divTableCell indent20">
                                                    <?php
                                                    $count="1";
                                                    $ures = sqlStatement("SELECT * FROM users WHERE authorized != 0 AND active = 1 ORDER BY lname, fname");
                                                    while ($prov = sqlFetchArray($ures)) {
                                                        $checked ="";
                                                        $suffix="";
                                                        if ($prefs) {
                                                            $provs = explode('|', $prefs['ME_providers']);
                                                            foreach ($provs as $doc) {
                                                                if ($doc == $prov['id']) {
                                                                    $checked = 'checked ="checked"';
                                                                }
                                                            }
                                                        }
                                                        if (!empty($prov['suffix'])) {
                                                            $suffix = ', '.$prov['suffix'];
                                                        }
                                                        ?>
                                                        <input <?php echo $checked; ?> class="update" type="checkbox" name="providers[]" id="provider_<?php echo attr($prov['id']); ?>" value="<?php echo attr($prov['id']); ?>">
                                                        <label for="provider_<?php echo attr($prov['id']); ?>"><?php echo text($prov['fname'])." ".text($prov['lname']).text($suffix); ?></label><br /><?php
                                                    }
                                                    ?>
                                                    </div>
                                                </div>
                                                <div class="divTableRow">
                                                    <div class="divTableCell divTableHeading"><?php echo xlt('Labels'); ?></div>
                                                    <div class="divTableCell indent20">
                                                    <input type="checkbox" class="update" name="LABELS_local" id="LABELS_local" value="1" <?php if ($prefs['LABELS_local']) {
                                                            echo "checked='checked'";} ?> />
                                                        <label for="LABELS_local" class="input-helper input-helper--checkbox" data-toggle='tooltip' data-placement='auto'  title='<?php echo xla('Check if you plan to use Avery Labels for Reminders or Recalls'); ?>'>
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
                                                <?php
                                             /*      <!--
                                                These options are for future use...

                                                <div class="divTableRow">
                                                <div class="divTableCell divTableHeading"><?php echo xlt('Combine Reminders'); ?></div>
                                                <div class="divTableCell indent20">

                                                    <label for="combine_time" class="input-helper input-helper--checkbox" data-toggle='tooltip' data-placement='auto'  title='If a patient has two or more future appointments scheduled within X days, combine reminders.  eg. If you indicate "7" for this value, for a yearly physical with two appointments 3 days apart, or a surgical appointment with a follow-up 6 days post-op, these appointment reminds will be combined into one message, because they are less than "7" days apart.'>
                                                    for appts within <input type="text" class="flow_time update" name="combine_time" id="combine_time" value="<?php echo xla($prefs['combine_time']); ?>" /> <?php echo xlt('days of each other'); ?></label>
                                                </div>
                                                </div>
                                                -->
                                            */
                                            ?>
                                            <input type="hidden" name="ME_username" id="ME_username" value="<?php echo attr($prefs['ME_username']);?>" />
                                            <input type="hidden" name="ME_api_key" id="ME_api_key" value="<?php echo attr($prefs['ME_api_key']);?>" />
                                            </div>
                                        </div>
                                    </div>
                                     <div class="col-sm-5 div-center" id="daform2">
                                        <div class="divTable2">
                                            <div class="divTableBody prefs">
                                            <?php if (count($logged_in['products']['ordered']) > '0') { ?>
                                                <div class="divTableRow">
                                                    <div class="divTableCell divTableHeading"><?php echo xlt('Enabled Services'); ?></div>
                                                    <div class="divTableCell">
                                                        <ul>
                                                            <?php
                                                            foreach ($logged_in['products']['ordered'] as $service) {
                                                                ?><li><a href="<?php echo $service['view']; ?>" target="_medex"><?php echo $service['model']; ?> </a></li>
                                                                <?php
                                                                if ($service['product_id'] =='54') {
                                                                    ?>
                                                                    <div style="margin-left:10px;">Appointment Reminders<br />Patient Recalls<br />SMS Bot<br />Go Green Messages</div>
                                                                    <?php
                                                                }
                                                            } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <?php }
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
                    if ($service['product_id'] =='54') {
                        ?>
                        <div style="margin-left:10px;">Appointment Reminders<br />Patient Recalls<br />SMS Bot<br />Go Green Messages</div>
                        <?php
                    }
            } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                   <div class="col-sm-1"></div>
                                </div>
                                <div style="clear:both;text-align:center;" id="msg bottom"><br />
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
        $from_date = !is_null($_REQUEST['form_from_date']) ? DateToYYYYMMDD($_REQUEST['form_from_date']) : date('Y-m-d', strtotime('-6 months'));
        //limit date range for initial Board to keep us sane and not tax the server too much

        if (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'Y') {
            $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
            $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d'), date('Y')+$ptkr_time);
        } elseif (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'M') {
            $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
            $ptkr_future_time = mktime(0, 0, 0, date('m')+$ptkr_time, date('d'), date('Y'));
        } elseif (substr($GLOBALS['ptkr_end_date'], 0, 1) == 'D') {
             $ptkr_time = substr($GLOBALS['ptkr_end_date'], 1, 1);
             $ptkr_future_time = mktime(0, 0, 0, date('m'), date('d')+$ptkr_time, date('Y'));
        }
        $to_date = date('Y-m-d', $ptkr_future_time);
        //prevSetting to_date?

        $to_date = !is_null($_REQUEST['form_to_date']) ? DateToYYYYMMDD($_REQUEST['form_to_date']) : $to_date;

        $recalls = $this->get_recalls($from_date, $to_date);

             // if all we don't use MedEx, there is no need to display the progress tabs, all recall processing is manual.
        if (!$logged_in) {
            $reminder_bar = "nodisplay";
            $events='';
        } else {
            $results = $MedEx->campaign->events($logged_in['token']);
            $events  = $results['events'];
            $reminder_bar = "indent20";
        }
        $processed = $this->recall_board_process($logged_in, $recalls, $events);
        ob_start();

        ?>
        <div class="container-fluid">
        <div class="row-fluid" id="rcb_selectors" style="display:<?php echo attr($rcb_selectors); ?>">
            <div class="col-sm-12">
                <div class="showRFlow text-center" id="show_recalls_params" style="margin: 20px auto;">
                    <div class="title"><?php echo xlt('Recall Board'); ?></div>
                    <div id="div_response"><?php echo xlt('Persons needing a recall, no appt scheduled yet'); ?>.</div>
                    <?php
                    if ($GLOBALS['medex_enable'] == '1') {
                        $col_width="3";
                    } else {
                        $col_width="4";
                        $last_col_width="nodisplay";
                    }
                    ?>
                    <br />
                    <form name="rcb" id="rcb" method="post">
                        <input type="hidden" name="go" value="Recalls">
                        <div class=" text-center row divTable" style="width: 85%;float:unset;margin: 0 auto;">

                                <div class="col-sm-<?php echo $col_width; ?> text-center" style="margin-top:15px;">
                                    <input placeholder="<?php echo attr('Patient ID'); ?>"
                                        class="form-control input-sm"
                                        type="text" id="form_patient_id"
                                        name="form_patient_id"
                                        value="<?php echo ( $form_patient_id ) ? attr($form_patient_id) : ""; ?>"
                                        onKeyUp="show_this();">

                                    <input type="text"
                                        placeholder="<?php echo attr('Patient Name'); ?>"
                                        class="form-control input-sm" id="form_patient_name"
                                        name="form_patient_name"
                                        value="<?php echo ( $form_patient_name ) ? attr($form_patient_name) : ""; ?>"
                                        onKeyUp="show_this();">
                                </div>

                                <div class="col-sm-<?php echo $col_width; ?> text-center" style="margin-top:15px;">
                                    <select class="form-group ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all" id="form_facility" name="form_facility"
                                        <?php
                                          $fac_sql = sqlStatement("SELECT * FROM facility ORDER BY id");
                                        while ($fac = sqlFetchArray($fac_sql)) {
                                            $true = ($fac['id'] == $rcb_facility) ? "selected=true" : '';
                                            $select_facs .= "<option value=".attr($fac['id'])." ".$true.">".text($fac['name'])."</option>\n";
                                            $count_facs++;
                                        }
                                        if ($count_facs <'1') {
                                            echo "disabled";
                                        }
                                        ?>  onchange="show_this();">
                                        <option value=""><?php echo xlt('All Facilities'); ?></option>
                                            <?php  echo $select_facs;  ?>
                                        </select>
                                            <?php
                                                        # Build a drop-down list of providers.
                                            $query = "SELECT id, lname, fname FROM users WHERE ".
                                              "authorized = 1  AND active = 1 ORDER BY lname, fname"; #(CHEMED) facility filter
                                            $ures = sqlStatement($query);
                                          //a year ago @matrix-amiel Adding filters to flow board and counting of statuses
                                            $count_provs = count(sqlFetchArray($ures));
                                            ?>
                                        <select class="form-group ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all" id="form_provider" name="form_provider" <?php
                                        if ($count_provs <'2') {
                                            echo "disabled";
                                        }
                                        ?> onchange="show_this();">
                                        <option value="" selected><?php echo xlt('All Providers'); ?></option>

                                            <?php
                                          // Build a drop-down list of ACTIVE providers.
                                            $query = "SELECT id, lname, fname FROM users WHERE ".
                                              "authorized = 1  AND active = 1 ORDER BY lname, fname"; #(CHEMED) facility filter

                                            $ures = sqlStatement($query);
                                          //a year ago @matrix-amiel Adding filters to flow board and counting of statuses
                                            while ($urow = sqlFetchArray($ures)) {
                                                $provid = $urow['id'];
                                                echo "    <option value='" . attr($provid) . "'";
                                                if (isset($rcb_provider) && $provid == $_POST['form_provider']) {
                                                    echo " selected";
                                                } elseif (!isset($_POST['form_provider'])&& $_SESSION['userauthorized'] && $provid == $_SESSION['authUserID']) {
                                                    echo " selected";
                                                }
                                                echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-<?php echo $col_width; ?>">
                                      <div style="margin: 0 auto;" class="input-append">
                                        <table class="table-hover table-condensed" style="margin:0 auto;">
                                          <tr><td class="text-right" style="vertical-align:bottom;">
                                            <label for="flow_from"><?php echo xlt('From'); ?>:</label></td><td>
                                            <input id="form_from_date" name="form_from_date"
                                                class="datepicker form-control input-sm text-center"
                                                value="<?php echo attr(oeFormatShortDate($from_date)); ?>"
                                                style="max-width:140px;min-width:85px;">

                                          </td></tr>
                                          <tr><td class="text-right" style="vertical-align:bottom;">
                                            <label for="flow_to">&nbsp;&nbsp;<?php echo xlt('To'); ?>:</label></td><td>
                                            <input id="form_to_date" name="form_to_date"
                                                class="datepicker form-control input-sm text-center"
                                                value="<?php echo attr(oeFormatShortDate($to_date)); ?>"
                                                style="max-width:140px;min-width:85px;">
                                          </td></tr>

                                          <tr><td class="text-center" colspan="2">
                                            <input href="#" class="btn btn-primary" type="submit" id="filter_submit" value="<?php echo xla('Filter'); ?>">
                                            </td>
                                          </tr>
                                        </table>
                                      </div>
                                    </div>
                                    <div class="col-sm-<?php echo $col_width." ".$last_col_width; ?> text-center" >
                                        <?php
                                        if ($GLOBALS['medex_enable'] == '1') {
                                            if ($logged_in) {
                                                foreach ($results['events'] as $event) {
                                                    if ($event['M_group'] != 'RECALL') {
                                                        continue;
                                                    }
                                                    $icon = $this->get_icon($event['M_type'], 'SCHEDULED');
                                                    if ($event['E_timing'] =='1') {
                                                        $action = "before";
                                                    }
                                                    if ($event['E_timing'] =='2') {
                                                        $action = "before (PM)";
                                                    }
                                                    if ($event['E_timing'] =='3') {
                                                        $action = "after";
                                                    }
                                                    if ($event['E_timing'] =='4') {
                                                        $action = "after (PM)";
                                                    }
                                                    $current_events .=  $icon." ".$event['E_fire_time']." ".xlt('days')." ".xlt($action)."<br />";
                                                }
                                            }
                                            ?>
                                            <a class="fa fw fa-plus-square-o" data-toggle="tooltip" data-placement="auto" title="<?php echo xla('Add a New Recall'); ?>" id="BUTTON_new_recall_menu" href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?go=addRecall"></a>
                                            <b><u>MedEx <?php echo xlt('Recall Schedule'); ?></u></b><br />
                                            <a href="https://medexbank.com/cart/upload/index.php?route=information/campaigns&amp;g=rec" target="_medex">
                                                <span>
                                                    <?php echo $current_events; ?>
                                                </span>
                                            </a>
                                        </div>
                                      </div>
                                        <?php } ?>
                                    </div>
                                    <div name="message" id="message" class="warning"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row-fluid">
                <div class="col-sm-12 text-center">
                    <div class="showRecalls" id="show_recalls" style="margin:0 auto;">
                        <div name="message" id="message" class="warning"></div>
                        <span class="text-right fa-stack fa-lg pull_right small" id="rcb_caret" onclick="toggleRcbSelectors();" data-toggle="tooltip" data-placement="auto" title="Show/Hide the Filters"
                            style="color:<?php echo $color = ($setting_selectors=='none') ? 'red' : 'black'; ?>;position:relative;float:right;right:0;top:0;">
                        <i class="fa fa-square-o fa-stack-2x"></i>
                        <i id="print_caret" class='fa fa-caret-<?php echo $caret = ($rcb_selectors==='none') ? 'down' : 'up'; ?> fa-stack-1x'></i>
                    </span>
                        <ul class="nav nav-tabs <?php echo attr($reminder_bar); ?>">
                            <li class="active whitish"><a onclick="show_this();" data-toggle="tab"><?php echo xlt('All'); ?></a></li>
                            <li class="whitish"><a onclick="show_this('whitish');" data-toggle="tab"><?php echo xlt('Events Scheduled'); ?></a></li>
                            <li class="yellowish"><a onclick="show_this('yellowish');" data-toggle="tab"><?php echo xlt('In-process'); ?></a></li>
                            <li class="reddish"><a onclick="show_this('reddish');" data-toggle="tab"><?php echo xlt('Manual Processing Required'); ?></a></li>
                            <li class="greenish"><a onclick="show_this('greenish');" data-toggle="tab"><?php echo xlt('Recently Completed'); ?></a></li>
                        </ul>

                        <div class="tab-content">

                           <div class="tab-pane active" id="tab-all">
                                <?php
                                $this->recall_board_top();
                                echo $processed['ALL'];
                                $this->recall_board_bot();
                                ?>
                            </div>
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
                    $.post( "<?php echo $GLOBALS['webroot']."/interface/main/messages/messages.php"; ?>", {
                        'rcb_selectors' : 'block',
                        success: function (data) {
                            $("#rcb_selectors").slideToggle();
                            $("#rcb_caret").css('color','#000');
                        }
                    });
                } else {
                    $.post( "<?php echo $GLOBALS['webroot']."/interface/main/messages/messages.php"; ?>", {
                        'rcb_selectors' : 'none',
                        success: function (data) {
                            $("#rcb_selectors").slideToggle();
                            $("#rcb_caret").css('color','red');
                        }
                    });
                }
                $("#print_caret").toggleClass('fa-caret-up').toggleClass('fa-caret-down');
            }

            /**
             * @return {boolean}
             */
            function SMS_bot(pid) {
                top.restoreSession();
                pid = pid.replace('recall_','');
                window.open('<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/messages.php?nomenu=1&go=SMS_bot&pid=' + pid,'SMS_bot', 'width=370,height=600,resizable=0');
                return false;
            }
            $(document).ready(function() {
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
        while ($recall= sqlFetchArray($result)) {
            $recalls[]=$recall;
        }
        return $recalls;
    }
    private function recall_board_process($logged_in, $recalls, $events = '')
    {
        global $MedEx;
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
            $provider[$prov['id']] = $prov['fname'][0]." ".$prov['lname'];
            if (!empty($prov['suffix'])) {
                $provider[$prov['id']] .= ', '.$prov['suffix'];
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
            echo '<div class="divTableRow ALL '.attr($show['status']).'"
                     data-status="'.attr($show['status']).'"
                     data-plan="'.attr($show['plan']).'"
                     data-facility="'.attr($recall['r_facility']).'"
                     data-provider="'.attr($recall['r_provider']).'"
                     data-pname="'.attr($recall['fname']." ".$recall['lname']).'"
                     data-pid="'.attr($recall['pid']).'"
                     id="recall_'.attr($recall['pid']).'" style="display:none;">';

            $query = "SELECT cal.pc_eventDate,pat.DOB FROM openemr_postcalendar_events AS cal JOIN patient_data AS pat ON cal.pc_pid=pat.pid WHERE cal.pc_pid =? ORDER BY cal.pc_eventDate DESC LIMIT 1";
            $result2 = sqlQuery($query, array( $recall['pid'] ));
            $last_visit = $result2['pc_eventDate'];
            $DOB = oeFormatShortDate($result2['DOB']);
            $age = $MedEx->events->getAge($result2['DOB']);
            echo '<div class="divTableCell text-center"><a href="#" onclick="show_patient(\''.attr($recall['pid']).'\');"> '.text($recall['fname']).' '.text($recall['lname']).'</a>';
            if ($GLOBALS['ptkr_show_pid']) {
                echo '<br /><span data-toggle="tooltip" data-placement="auto" title="'.xla("Patient ID").'" class="small">'. xlt('PID').': '.text($recall['pid']).'</span>';
            }
            echo '<br /><span data-toggle="tooltip" data-placement="auto" title="'.xla("Most recent visit").'" class="small">' . xlt("Last Visit") . ': '.text(oeFormatShortDate($last_visit)).'</span>';
            echo '<br /><span class="small" data-toggle="tooltip" data-placement="auto" title="'.xla("Date of Birth and Age").'">'. xlt('DOB').': '.text($DOB).' ('.$age.')</span>';
            echo '</div>';

            echo '<div class="divTableCell appt_date">'.text(oeFormatShortDate($recall['r_eventDate']));
            if ($recall['r_reason']>'') {
                echo '<br />'.text($recall['r_reason']);
            }
            if (strlen($provider[$recall['r_provider']]) > 14) {
                $provider[$recall['r_provider']] = substr($provider[$recall['r_provider']], 0, 14)."...";
            }
            if (strlen($facility[$recall['r_facility']]) > 20) {
                $facility[$recall['r_facility']] = substr($facility[$recall['r_facility']], 0, 17)."...";
            }

            if ($count_providers > '1') {
                echo "<br /><span data-toggle='tooltip' data-placement='auto'  title='".xla('Provider')."'>".text($provider[$recall['r_provider']])."</span>";
            }
            if (( $count_facilities > '1' ) && ( $_REQUEST['form_facility'] =='' )) {
                echo "<br /><span data-toggle='tooltip' data-placement='auto'  title='".xla('Facility')."'>".text($facility[$recall['r_facility']])."</span><br />";
            }

            echo '</div>';
            echo '<div class="divTableCell phones">';
            if ($recall['phone_cell'] >'') {
                echo 'C: '.text($recall['phone_cell'])."<br />";
                //    echo 'C:'.substr($recall['phone_cell'], 0, 2).'-XXX-XXXX<br />';
            }
            if ($recall['phone_home'] >'') {
                echo 'H: '.text($recall['phone_home'])."<br />";
                //echo 'H:'.substr($recall['phone_home'], 0, 2).'-XXX-XXXX<br />';
            }
            if ($recall['email'] >'') {
                $mailto = $recall['email'];
                if (strlen($recall['email']) > 15) {
                    $recall['email'] = substr($recall['email'], 0, 12)."...";
                }
                echo 'E: <a data-toggle="tooltip" data-placement="auto" title="'.xla('Send an email to ').attr($mailto).'" href="mailto:'.attr($mailto).'">'.text($recall['email']).'</a><br />';
            }
            if ($logged_in) {
                $pat = $this->possibleModalities($recall);
                echo $pat['SMS'].$pat['AVM'].$pat['EMAIL'];//escape/translation done in possibleModalities.
            }
            echo '</div>';

            if ($show['postcard'] > '') {
                echo '<div class="divTableCell text-center postcards">'.text($show['postcard']).'</div>';
            } else {
                echo '<div class="divTableCell text-center postcards"><input type="checkbox" name="postcards" id="postcards[]" value="'.attr($recall['pid']).'"></div>';
            }

            if ($show['label'] > '') {
                echo '<div class="divTableCell text-center labels">'.text($show['label']).'</div>';
            } else {
                echo '<div class="divTableCell text-center labels"><input type="checkbox" name="labels" id="labels[]" value="'.attr($recall['pid']).'"></div>';
            }
            echo '  <div class="divTableCell text-center msg_manual"><span class="fa fa-fw spaced_icon" >
                            <input type="checkbox" name="msg_phone" id="msg_phone_'.attr($recall['pid']).'" onclick="process_this(\'phone\',\''.attr($recall['pid']).'\',\''.attr($recall['r_ID']).'\')" />
                    </span>';
            echo '    <span data-toggle="tooltip" data-placement="auto" title="'.xla('Scheduling').'" class="fa fa-calendar-check-o fa-fw" onclick="newEvt(\''.attr($recall['pid']).'\',\'\');">
                    </span>';
            echo '</div>';

            echo '  <div class="divTableCell text-left msg_resp">';
                //    if phone call made show each in progress
            echo '<textarea onblur="process_this(\'notes\',\''.attr($recall['pid']).'\',\''.attr($recall['r_ID']).'\');" name="msg_notes" id="msg_notes_'.attr($recall['pid']).'" style="width:90%;height:30px;">'.nl2br(text($recall['NOTES'])).'</textarea>';
            echo '</div>';
            echo '  <div class="divTableCell text-left msg_resp">
                <i class="top_right_corner fa fa-times" onclick="delete_Recall(\''.attr($recall['pid']).'\',\''.attr($recall['r_ID']).'\')"></i> ';
            echo $show['progression'];

            if ($show['appt']) {
                echo "<span onclick=\"newEvt('".attr($prog['pid'])."','".attr($show['pc_eid'])."');\" class='btn btn-danger text-center' data-toggle='tooltip' data-placement='auto'  title='".xla('Appointment made by')." ".attr($prog['who'])." ".xla('on')." ".attr($prog['when'])."'><b>".xlt('Appt{{Abbreviation for appointment}}').":</b> ".text($show['appt'])."<br />";
            }
            echo '</div>';
            echo '</div>';
            $content = ob_get_clean();
            $process['ALL'] .= $content;
        }
        return $process;
    }

    /**
         *   This function looks at a single recall and assesses its status.
         *   Has it been worked on yet?  Any phone calls made, labels printed or postcards printed?
         *   If they are a MedEx subscriber, do they have any scheduled Recall Campaign Events and if so when?
         *   Have any of these MedEx events happened?  Given all the variables, what is the status of this recall at this moment?
         *   We also use color coding in the Recall Board -
         *       -- whitish for pending, nothing happed yet.
         *       -- yellowish for in process, something happened but no appointment was made yet!
         *       -- reddish for manual processing needed.
         *       -- greenish for completed and an appointment was made.
         *   In the ideal workflow, the secretary would be going through the Recall Board line by line once per day/week/month (per practice needs).
         *       They can then just delete the Recall by clicking the X on the right.  Done move on to the next.
         *       However a patient may have called in randomly to make this appointment - the secretary may be unaware that the Recall even exists.
         *       In this work-flow, the secretary does not have to open the Recall Board, nor should they waste their time when we can do it for them...
         *       Let the Recall Board look to see if there is an appointment for this patient in the future.
         *       If there is, and it was made more than 16 hours ago, it deletes the Recall altogether.  It is never displayed again.
         *       If an appointment was created less than 16 hours ago, it turns the row greenish, signaling anyone who looks that it was just taken care of,
         *       and display in the "progress" column what has transpired.
         *       Thanks, move on, nothing to see here.
         *       This also allows anyone (management?) a tool to see what Recall work was performed over the last 16 hours.
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
        $show['EMAIL']['text']='';
        $show['SMS']['text']='';
        $show['AVM']['text']='';
        $show['progression']='';
        $show['DONE']='';
        $query = "SELECT * FROM openemr_postcalendar_events WHERE
                      pc_eventDate >= CURDATE() AND pc_pid =? AND pc_eventDate > (? - INTERVAL 90 DAY)  AND pc_time >  (CURDATE()- INTERVAL 16 HOUR)";
        $count = sqlFetchArray(sqlStatement($query, array($recall['r_pid'],$recall['r_eventDate'])));

        if ($count) {
            $sqlDELETE = "DELETE FROM medex_outgoing WHERE msg_pc_eid = ?";
            sqlStatement($sqlDELETE, array('recall_'.$recall['pid']));
            $sqlDELETE = "DELETE FROM medex_recalls WHERE r_pid = ?";
            sqlStatement($sqlDELETE, array($recall['pid']));
            //log this action "Recall for $pid deleted now()"?
            $show['DONE'] ='1';//tells recall board to move on.
            $show['status'] ='greenish'; //tells MedEx to move on, don't process this recall - delete it from their servers.
            return $show;
            // Just cleaning up the Recall Board for you. Move along, nothing to see.
            // If you need to look at the track of action, look in the log?
        }

        // Did anything happen yet?
        // Table medex_outgoing is our log for current activities.
        // It includes records of local manual things your office did and the MedEx reports.
        // For non-MedEx subscribers, the local functionality will still work just fine...
        // Unless the RECALL is completed (appt made) more than 16 hours ago, the RECALL's data will be present.

        // We need to output the correct text and icon to visually display the appt status

        $sql ="SELECT * FROM medex_outgoing WHERE msg_pc_eid = ?  ORDER BY msg_date ASC";
        $result = sqlStatement($sql, array('recall_'.$recall['pid']));
        $something_happened='';

        while ($progress = sqlFetchArray($result)) {
            $i = $progress['campaign_uid'];//if this is a manual entry, this ==0.

            $phpdate = strtotime($progress['msg_date']);
            $when = oeFormatShortDate(date('Y-m-d', $phpdate))." @ ".date('g:iA', $phpdate);

            if (is_numeric($progress['msg_reply'])) { // it was manually added by id
                $sql2 = "SELECT * FROM users WHERE id =?";

                $who  = sqlQuery($sql2, array($progress['msg_reply']));
                $who_name = $who['fname']." ".$who['lname'];
                //Manually generated actions
                if ($progress['msg_type'] == 'phone') { //ie. a manual phone call, not an AVM
                    $show['progression'] .= "<span class='left' data-toggle='tooltip' data-placement='auto'  title='".xla('Phone call made by')." ".text($who_name)."'><b>".xlt('Phone')."</b> ".text($when)."</span></br />\n";
                } elseif ($progress['msg_type'] == 'notes') {
                    $show['progression'] .= "<span class='left' data-toggle='tooltip' data-placement='auto'  title='".xla('Notes by')." ".text($who_name)." on ".text($when)."'><b>".xlt('Note').":</b> ".text($progress['msg_extra_text'])."</span></br />\n";
                } elseif ($progress['msg_type'] == 'postcards') {
                    $show['progression'] .= "<span class='left' data-toggle='tooltip' data-placement='auto'  title='".xla('Postcard printed by')." ".text($who_name)."'><b>".xlt('Postcard').":</b> ".text($when)."</span></br />\n";
                } elseif ($progress['msg_type'] == 'labels') {
                    $show['progression'] .= "<span class='left' data-toggle='tooltip' data-placement='auto'  title='".xla('Label printed by')." ".text($who)."'><b>".xlt('Label').":</b> ".text($when)."</span></br />";
                }
            } else {
                $who_name = "MedEx";
                // MedEx related actions
                // Recalls can't be confirmed through MedEx - we don't make appointments (yet)...
                // They disappear 16 hours after an appt is made (they glow green for those 16 hours).
                if (($progress['msg_reply'] == "READ")||($show[$progress['msg_type']]['stage']=="READ")) {
                    $show[$progress['msg_type']]['stage']   = "READ";
                    $icon = $this->get_icon($progress['msg_type'], "READ");
                    $show[$progress['msg_type']]['text']    = "<span class='left'>".$icon." ".text($when)."</span><br />";
                    if ($progress['msg_type'] == 'AVM') {
                        $show['campaign'][$i]['status']="reddish";
                    }
                } elseif (($progress['msg_reply'] == "SENT")||($show[$progress['msg_type']]['stage']=="SENT")) {
                    if ($show[$progress['msg_type']]['stage']!="READ") {
                        $show[$progress['msg_type']]['stage']   = "SENT";
                        $icon = $this->get_icon($progress['msg_type'], "SENT");
                        $show[$progress['msg_type']]['text']    = "<span class='left'>".$icon." ".text($when)."</span><br />";
                    }
                } elseif (($progress['msg_reply'] == "To Send")||($show[$progress['msg_type']]['stage']=="QUEUED")) {
                    if (($show[$progress['msg_type']]['stage']!="READ")&&($show[$progress['msg_type']]['stage']!="SENT")) {
                        $show[$progress['msg_type']]['stage']   = "QUEUED";
                        $icon = $this->get_icon($progress['msg_type'], $progress['msg_reply']);
                    }
                }
                if ($progress['msg_reply'] == "CALL") {
                    $icon = $this->get_icon($progress['msg_type'], "CALL");
                    $show['progression'] .= "<span class='left'>".$icon." ".text($progress['msg_type'])."@".text($when)."</span><br />";
                } elseif ($progress['msg_reply'] == "STOP") {
                    $icon = $this->get_icon($progress['msg_type'], "STOP");
                    $show['progression'] .= "<span class='left'>".$icon." ".text($when)."</span><br />";
                } elseif ($progress['msg_reply'] == "EXTRA") {
                    $icon = $this->get_icon($progress['msg_type'], "EXTRA");
                    $show['progression'] .= "<span class='left'>".$icon." ".text($when)."</span><br />";
                } elseif ($progress['msg_reply'] == "FAILED") {
                    $icon = $this->get_icon($progress['msg_type'], "FAILED");
                    $show['progression']  .= "<span class='left'>".$icon." ".text($when)."</span><br />";
                    $show['campaign'][$i]['status']=1;
                }
                $show['campaign'][$i]['icon'] = $icon;
            }

            $something_happened=true;
        }
        $show['progression'] .= $show['EMAIL']['text'].$show['SMS']['text'].$show['AVM']['text'];


        // Let's look at the MedEx events:
        // Show the DATE when a Campaign event will be run for a given patient
        /*
         * E_fire_tire = number of days before/after recall date that a MedEx campaign event will run
         * MedEx E_timing options:
         * 1 = days before
         * 2 = days before PM
         * 3 = days after
         * 4 = days after PM
         */
        $camps='0';
        /** @var TYPE_NAME $events */
        foreach ($events as $event) {
            if ($event['M_group'] != "RECALL") {
                continue;
            }
                $pat = $this->possibleModalities($recall);
            if ($pat['ALLOWED'][$event['M_type']] == 'NO') {
                continue;    //it can't happen
            }
            if ($pat['facility']['status']!= 'ok') {
                continue;    //it can't happen
            }
            if ($pat['provider']['status']!= 'ok') {
                continue;    //it can't happen
            }

            if ($show['campaign'][$event['C_UID']]['status']) {
                continue; //it is done
            }
                $camps++;                                                   //there is still work to be done
            if ($show['campaign'][$event['C_UID']]['icon']) {
                continue;   //but something has happened since it was scheduled.
            }

                ($event['E_timing'] < '3') ? ($interval ='-') : ($interval ='+');//this is only scheduled, 3 and 4 are for past appointments...
                $show['campaign'][$event['C_UID']] = $event;
                $show['campaign'][$event['C_UID']]['icon'] = $this->get_icon($event['M_type'], "SCHEDULED");

                $recall_date = date("Y-m-d", strtotime($interval.$event['E_fire_time']." days", strtotime($recall['r_eventDate'])));
                $date1 = date('Y-m-d');
                $date_diff=strtotime($date1) - strtotime($recall['r_eventDate']);
            if ($date_diff >= '-1') { //if it is sched for tomorrow or earlier, queue it up
                $show['campaign'][$event['C_UID']]['executed'] = "QUEUED";
                $show['status'] = "whitish";
            } else {
                $execute = oeFormatShortDate($recall_date);
                $show['campaign'][$event['C_UID']]['executed'] = $execute;
            }
                $show['progression'] .= "<a href='https://medexbank.com/cart/upload/index.php?route=information/campaigns' class='nowrap text-left' target='_MedEx'>".
                                        $show['campaign'][$event['C_UID']]['icon']." ".text($show['campaign'][$event['C_UID']]['executed'])."</a><br />";
        }



        // Show recall row status via background color.
        // If an appt was made < 16hrs ago, make it green(completed) and $show['DONE'] = 1
        //   o/w yellow(in progress) if something happened or Campaign fired
        //   o/w red (manual needed) if no more MedEx Recall Events are scheduled to be done and no appt was made yet.
        //      ie. we struck out and need to process this manually
        //      or write it off or delete it or do soemthing else?  Have to know what to do to write that. ;)
        $query  = "SELECT * FROM openemr_postcalendar_events WHERE pc_eventDate > CURDATE() AND pc_pid =? AND pc_time >  CURDATE()- INTERVAL 16 HOUR";
        $result = sqlFetchArray(sqlStatement($query, array($recall['pid'])));

        if ($something_happened||$result) {
            if ($result) {
                $show['status'] = "greenish"; //appt made, move on
                $phpdate = strtotime($result['pc_eventDate']." ".$result['pc_startTime']);
                $show['pc_eid'] = $result['pc_eid'];
                $show['appt'] = oeFormatShortDate(date('Y-m-d', $phpdate))." @ ".date('g:iA', $phpdate);
                $show['DONE'] = '1';
            } elseif ($GLOBALS['medex_enable'] == '1') {
                if ($logged_in) {
                    if ($camps =='0') {
                        $show['status'] = "reddish"; //hey, nothing automatic left to do - manual processing required.
                    } else {
                        $show['status'] = "yellowish"; //no appt yet but something happened!
                    }
                }
            } else {
                $show['status'] = "whitish";
            }
        } elseif (($GLOBALS['medex_enable'] == '1') && ($camps =='0')) {
                    $show['status'] = "reddish"; //hey, nothing automatic left to do - manual processing required.
        } else {
            $show['status'] = "whitish";
        }
        if ($logged_in) {
            $show['progression'] =   '<div onclick="SMS_bot(\'recall_'.$recall['pid'].'\');">'.$show['progression'].'</div>';
        }
        return $show;
    }
    private function get_icon($event_type, $status = 'SCHEDULED')
    {
        $sqlQuery = "SELECT * FROM medex_icons";
        $result = sqlStatement($sqlQuery);
        while ($icons = sqlFetchArray($result)) {
            if (($icons['msg_type'] == $event_type)&&($icons['msg_status'] == $status)) {
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
        //the text fields in DB are set by user and sql sscripts. Is translation/escaping needed here? How?
        while ($icons = sqlFetchArray($result)) {
            $icon[$icons['msg_type']][$icons['msg_status']] = $icons['i_html'];
        }
        //if the patient is dead, should we really be sending them a message?
        //Maybe we would need to customize this for a pathologist but for the rest, the answer is no...
        if (empty($appt['phone_cell']) || ($appt["hipaa_allowsms"]=="NO")) {
            $pat['SMS'] = $icon['SMS']['NotAllowed'];
            $pat['ALLOWED']['SMS'] = 'NO';
        } else {
            $phone = preg_replace("/[^0-9]/", "", $appt["phone_cell"]);
            $pat['SMS'] = $icon['SMS']['ALLOWED'];     // It is allowed and they have a cell phone
        }
        if ((empty($appt["phone_home"]) && (empty($appt["phone_cell"])) || ($appt["hipaa_voice"]=="NO"))) {
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
        if (($appt["email"]=="")||($appt["hipaa_allowemail"]=="NO")) {
            $pat['EMAIL'] = $icon['EMAIL']['NotAllowed'];
            $pat['ALLOWED']['EMAIL'] = 'NO';
        } else {
            $pat['EMAIL'] = $icon['EMAIL']['ALLOWED'];
        }
        // If the practice is a MedEx practice, is this facility and/or this provider signed up for MedEx?
        //  In this scenario, not all providers or locations for this practice are enrolled in MedEx.
        //  Don't report that something MedEx-related is going to happen for these folks, cause it shouldn't.
        $sql = "SELECT * FROM medex_prefs";
        $prefs = sqlFetchArray(sqlStatement($sql));
        $facs = explode('|', $prefs['ME_facilities']);
        foreach ($facs as $place) {
            if (isset($appt['r_facility']) && ($appt['r_facility']==$place)) {
                $pat['facility']['status'] = 'ok';
            }
        }
        $providers = explode('|', $prefs['ME_providers']);
        foreach ($providers as $provider) {
            if (isset($appt['r_provider']) && ($appt['r_provider']==$provider)) {
                $pat['provider']['status'] = 'ok';
            }
        }
        return $pat;
    }
    private function recall_board_top()
    {
        ?>
        <div class="divTable text-center" id="rcb_table" style="margin:0 auto 30px;width:100%;">
            <div class="sticky divTableRow divTableHeading">
                <div class="divTableCell text-center" style="width:10%;"><?php echo xlt('Name'); ?></div>
                <div class="divTableCell text-center" style="width:10%;"><?php echo xlt('Recall'); ?></div>

                <div class="divTableCell text-center phones" style="width:10%;"><?php echo xlt('Contacts'); ?></div>
                <div class="divTableCell text-center msg_resp"><?php echo xlt('Postcards'); ?><br />
                    <span onclick="top.restoreSession();checkAll('postcards',true);" class="fa fa-square-o fa-lg" id="chk_postcards"></span>
                    &nbsp;&nbsp;
                    <span onclick="process_this('postcards');" class="fa fa-print fa-lg"></span>
                </div>
                <div class="divTableCell text-center msg_resp"><?php echo xlt('Labels'); ?><br />
                    <span onclick="checkAll('labels',true);" class="fa fa-square-o fa-lg" id="chk_labels"></span>
                    &nbsp;&nbsp;
                    <span onclick="process_this('labels');" class="fa fa-print fa-lg"></span>
                </div>
                <div class="divTableCell text-center msg_resp"><?php echo xlt('Office').": ".xlt('Phone'); ?></div>
                <div class="divTableCell text-center msg_notes"><?php echo xlt('Notes'); ?></div>
                <div class="divTableCell text-center"><?php echo xlt('Progress'); ?>
                </div>

            </div>
            <div class="divTableBody">
            <?php
    }
    private function recall_board_bot()
    {
        ?>      </div>
            </div>
        </div>
        <?php
    }
    public function display_add_recall($pid = 'new')
    {
        
        ?>
        <div class="container-fluid">
            <div class="row-fluid showReminders clear text-center">
                <div id="add_recall" class="col-sm-12">
                    <div class="title"><?php echo xlt('New Recall'); ?></div>
                    <div name="div_response" id="div_response"><?php echo xlt('Create a reminder to schedule a future visit'); ?> .</div>
                </div>
            </div>
            <div class="row-fluid divTable float_center">
                <form name="addRecall" id="addRecall" class="form-inline" >
                    <input type="hidden" name="go" id="go" value="addRecall">
                    <input type="hidden" name="action" id="go" value="addRecall">
                    <div class="col-sm-6 text-right form-group form-group-sm">
                        <div class="divTableBody pull-right">
                            <div class="divTableRow">
                                <div class="divTableCell divTableHeading"><?php echo xlt('Name'); ?></div>
                                <div class="divTableCell recall_name">
                                    <input type="text" name="new_recall_name" id="new_recall_name" class="form-control"
                                        onclick="recall_name_click(this)"
                                        value="" style="width:225px;">
                                    <input type="hidden" name="new_pid" id="new_pid" value="">
                                </div>
                            </div>
                            <div class="divTableRow">
                                <div class="divTableCell divTableHeading"><?php echo xlt('Recall When'); ?></div>
                                <div class="divTableCell indent20">
                                     <span class="bold"><?php echo xlt('Last Visit'); ?>: </span><input type="text" value="" name="DOLV" id="DOLV" class="form-control">
                                    <br />
                                    <!-- Feel free to add in any dates you would like to show here...
                                    <input type="radio" name="new_recall_when" id="new_recall_when_6mos" value="180">
                                    <label for="new_recall_when_6mos" class="input-helper input-helper--checkbox">+ 6 <?php echo xlt('months'); ?></label><br />
                                    -->
                                    <label for="new_recall_when_1yr" class="indent20 input-helper input-helper--checkbox"><input type="radio" name="new_recall_when" id="new_recall_when_1yr" value="365" class="form-control">
                                    <?php echo xlt('plus 1 year'); ?></label><br />
                                    <label for="new_recall_when_2yr" class="indent20 input-helper input-helper--checkbox"><input type="radio" name="new_recall_when" id="new_recall_when_2yr" value="730" class="form-control">
                                        <?php echo xlt('plus 2 years'); ?></label><br />
                                    <label for="new_recall_when_3yr" class="indent20 input-helper input-helper--checkbox"><input type="radio" name="new_recall_when" id="new_recall_when_3yr" value="1095" class="form-control">
                                        <?php echo xlt('plus 3 years'); ?></label><br />
                                        <span class="bold"> <?php echo xlt('Date'); ?>:</span> <input class="datepicker form-control input-sm text-center" type="text" id="form_recall_date" name="form_recall_date" value="">
                                </div>
                            </div>
                            <div class="divTableRow">
                                <div class="divTableCell divTableHeading"><?php echo xlt('Recall Reason'); ?></div>
                                <div class="divTableCell">
                                    <input class="form-control" type="text"  style="width:225px;" name="new_reason" id="new_reason" value="">
                                    </div>
                                </div>
                                <div class="divTableRow">
                                    <div class="divTableCell divTableHeading"><?php echo xlt('Provider'); ?></div>
                                    <div class="divTableCell">
                                        <?php
                                        $ures = sqlStatement("SELECT id, username, fname, lname FROM users WHERE authorized != 0 AND active = 1 ORDER BY lname, fname");
                                        //This is an internal practice function so ignore the suffix as extraneous information.  We know who we are.
                                        $defaultProvider = $_SESSION['authUserID'];
                                        // or, if we have chosen a provider in the calendar, default to them
                                        // choose the first one if multiple have been selected
                                        if (count($_SESSION['pc_username']) >= 1) {
                                            // get the numeric ID of the first provider in the array
                                            $pc_username = $_SESSION['pc_username'];
                                            $firstProvider = sqlFetchArray(sqlStatement("SELECT id FROM users WHERE username=?", array($pc_username[0])));
                                            $defaultProvider = $firstProvider['id'];
                                        }
                                        // if we clicked on a provider's schedule to add the event, use THAT.
                                        if ($userid) {
                                            $defaultProvider = $userid;
                                        }

                                        echo "<select  class='form-control' name='new_provider' id='new_provider' style='width:95%;'>";
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
                                <div class="divTableRow">
                                    <div class="divTableCell divTableHeading"><?php echo xlt('Facility'); ?></div>
                                    <div class="divTableCell">
                                    <select  class="form-control ui-selectmenu-button ui-button ui-widget ui-selectmenu-button-closed ui-corner-all" name="new_facility" id="new_facility" style="width:95%;">
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
                        </div>

                        <div class="col-sm-6 text-center form-group form-group-sm">
                            <div class="divTableBody">
                                <div class="divTableRow news">
                                    <div class="divTableCell divTableHeading"><?php echo xlt('DOB'); ?></div>
                                    <div class="divTableCell">&nbsp;&nbsp;
                                    
                                    <span name="new_DOB" id="new_DOB" style="width:90px;"></span> -
                                     <span id="new_age" name="new_age"></span></div>
                                </div>
                                <div class="divTableRow news">
                                    <div class="divTableCell divTableHeading"><?php echo xlt('Address'); ?></div>
                                    <div class="divTableCell">
                                    <input type="text"  class="form-control" name="new_address" id="new_address" style="width:240px;" value=""><br />
                                    <input type="text"  class="form-control" name="new_city" id="new_city" style="width:100px;" value="">
                                    <input type="text"  class="form-control" name="new_state" id="new_state" style="width:40px;" value="">
                                    <input type="text"  class="form-control" name="new_postal_code" id="new_postal_code" style="width:65px;" value=""></div>
                                </div>
                                <div class="divTableRow news">
                                    <div class="divTableCell divTableHeading phone_home"><?php echo xlt('Home Phone'); ?></div>
                                    <div class="divTableCell"><input type="text" name="new_phone_home" id="new_phone_home" class="form-control" value=""></div>
                                </div>
                                <div class="divTableRow news">
                                    <div class="divTableCell divTableHeading phone_cell"><?php echo xlt('Mobile Phone'); ?></div>
                                    <div class="divTableCell"><input type="text" name="new_phone_cell" id="new_phone_cell" class="form-control" value=""></div>
                                </div>
                                <div class="divTableRow news">
                                    <div class="divTableCell divTableHeading msg_sent" data-placement="auto" title="<?php echo xla('Text Message permission'); ?>"><?php echo xlt('SMS OK'); ?></div>

                                    <div class="divTableCell indent20">
                                    <input type="radio" class="form-control" name="new_allowsms" id="new_allowsms_yes" value="YES"> <label for="new_allowsms_yes"><?php echo xlt('YES'); ?></label>
                                    &nbsp;&nbsp;
                                    <input type="radio" class="form-control" name="new_allowsms" id="new_allowsms_no" value="NO"> <label for="new_allowsms_no"><?php echo xlt('NO'); ?></label>
                                    </div>
                                </div>
                                <div class="divTableRow indent20">
                                    <div class="divTableCell divTableHeading msg_how" data-placement="auto" title="<?php echo xla('Automated Voice Message permission'); ?>"><?php echo xlt('AVM OK'); ?></div>
                                    <div class="divTableCell indent20">
                                    <input type="radio" class="form-control" name="new_voice" id="new_voice_yes" value="YES"> <label for="new_voice_yes"><?php echo xlt('YES'); ?></label>
                                    &nbsp;&nbsp;
                                    <input type="radio" class="form-control" name="new_voice" id="new_voice_no" value="NO"> <label for="new_voice_no"><?php echo xlt('NO'); ?></label>
                                    </div>
                                </div>
                                <div class="divTableRow news">
                                    <div class="divTableCell divTableHeading phone_cell"><?php echo xlt('E-Mail'); ?></div>
                                    <div class="divTableCell"><input type="email" name="new_email" id="new_email" class="form-control" style="width:225px;" value=""></div>
                                </div>

                                <div class="divTableRow news">
                                    <div class="divTableCell divTableHeading msg_when"><?php echo xlt('E-mail OK'); ?></div>
                                    <div class="divTableCell indent20">
                                    <input type="radio" class="form-control" name="new_email_allow" id="new_email_yes" value="YES"> <label for="new_email_yes"><?php echo xlt('YES'); ?></label>
                                    &nbsp;&nbsp;
                                    <input type="radio" class="form-control" name="new_email_allow" id="new_email_no" value="NO"> <label for="new_email_no"><?php echo xlt('NO'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row-fluid text-center">
                    <input class="btn btn-primary" onclick="add_this_recall();" value="<?php echo xla('Add Recall'); ?>" id="add_new" name="add_new">
                    <p>
                        <em class="small text-muted">* <?php echo xlt('N.B.{{Nota bene}}')." ".xlt('Demographic changes made here are recorded system-wide'); ?>.</em>
                    </p>
                </div>

            </div>
            <script>
                $(document).ready(function () {
                    $('.datepicker').datetimepicker({
                        <?php $datetimepicker_timepicker = false; ?>
                        <?php $datetimepicker_showseconds = false; ?>
                        <?php $datetimepicker_formatInput = true; ?>
                        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                    });
                    
                });
                <?php
                if ($_SESSION['pid']>'') {
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
          <div style="position:relative;margin:30px auto;vertical-align:middle;">
                <?php
                $sqlQuery = "SELECT * FROM medex_icons ORDER BY msg_type";
                $result  = sqlStatement($sqlQuery);
                $icons = array();
                while ($urow = sqlFetchArray($result)) {
                    $icons['msg_type']['description'] = $urow['i_description'];
                    $icons[$urow['msg_type']][$urow['msg_status']]  = $urow['i_html'];
                } ?>
                    <div class="divTable" style="margin:30px auto;width:100%;">
                      <div class="divTableBody">
                        <div class="divTableRow divTableHeading">
                      <div class="divTableCell text-center"><?php echo xlt('Message'); ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('Possible'); ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('Not Possible'); ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('Scheduled'); ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('Sent')."<br />".xlt('In-process'); ?></div>
                      <div class="divTableCell text-center"><?php echo xlt('Read')."<br />".xlt('Delivered');
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
                        <?php
                        //When we have added PostCards to MedEx, we can display this.
                        //Until then this would just add confusion.
                    /*
                        <div class="divTableRow">
                      <div class="divTableCell text-center"><?php echo xlt('POSTCARD'); ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['ALLOWED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['NotAllowed']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['SCHEDULED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['SENT']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['READ']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['CONFIRMED']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['CALL']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['FAILURE']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['EXTRA']; ?></div>
                      <div class="divTableCell text-center"><?php echo $icons['POSTCARD']['STOP']; ?></div>
                        </div>
                      </div>
                      */
                        ?>
                    </div>
              </div>

            <?php
    }

    /**
     *  This function displays a bootstrap responsive pop-up window containing an image of a phone with a record of our messaging activity.
     *  It is fired from the Flow board.
     *  It enables two-way SMS texting between logged_in users and patients, if desired.
     *  It may also end up on the Recall Board.
     *  It may also allow playback of AVM audio files.
     *  It may also do other things that haven't been written yet.
     *  It may open to a messaging status board on large screens.
     * @param $logged_in
     * @return bool
     */
         
    public function SMS_bot($logged_in)
    {
        $fields = array();
        $fields = $_REQUEST;
        if (!empty($_REQUEST['pid']) && $_REQUEST['pid'] != 'find') {
            $responseA = $this->syncPat($_REQUEST['pid'], $logged_in);
        } else if ($_REQUEST['show']=='pat_list') {
            $responseA = $this->syncPat($_REQUEST['show'], $logged_in);
            $fields['pid_list'] = $responseA['pid_list'];
            $fields['list_hits']  = $responseA['list_hits'];
        }
        $this->curl->setUrl($this->MedEx->getUrl('custom/SMS_bot&token='.$logged_in['token']));
        $this->curl->setData($fields);
        $this->curl->makeRequest();
        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            echo $response['success'];
        } else if (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return false;
    }
    public function syncPat($pid, $logged_in)
    {
        if ($pid == 'pat_list') {
            global $data;
            $values = $_REQUEST['outpatient'];
            $sqlSync = "SELECT * FROM patient_data WHERE fname LIKE ? OR lname LIKE ? LIMIT 20";
            $datas = sqlStatement($sqlSync, array("%".$values."%","%".$values."%"));
            while ($hit = sqlFetchArray($datas)) {
                $data['list'][] = $hit;
                $pid_list[] = $hit['pid'];
            }
            $this->curl->setUrl($this->MedEx->getUrl('custom/syncPat&token='.$logged_in['token']));
            $this->curl->setData($data);
            $this->curl->makeRequest();
            $response = $this->curl->getResponse();
            $response['pid_list'] = $pid_list;
            $response['list_hits'] = $data['list'];
        } else {
            $sqlSync = "SELECT * FROM patient_data WHERE pid=?";
            $data = sqlQuery($sqlSync, array($pid));
            $this->curl->setUrl($this->MedEx->getUrl('custom/syncPat&token='.$logged_in['token']));
            $this->curl->setData($data);
            $this->curl->makeRequest();
            $response = $this->curl->getResponse();
        }
        if (isset($response['success'])) {
            return $response;
        } else if (isset($response['error'])) {
            $this->lastError = $response['error'];
        }
        return $this->lastError;
    }
}

class Setup extends Base
{
    public function MedExBank($stage)
    {
        if ($stage =='1') {
            ?>
            <div class="row">
            <div class="col-sm-10 text-center col-xs-offset-1">
                <div id="setup_1">
                    <div class="title">MedEx</div>
                    <div class="row showReminders ">
                        <div class="col-sm-10 text-center col-xs-offset-1">
                            <em>
                                <?php echo xlt('Using technology to improve productivity'); ?>.
                            </em>
                        </div>
                    </div>
                    <div class="row showReminders ">
                        <div class="col-sm-5 col-xs-offset-1 text-center">
                            <h3 class="title"><?php echo xlt('Targets'); ?>:</h3>
                            <ul class="text-left" style="margin-left:125px;">
                                <li> <?php echo xlt('Appointment Reminders'); ?></li>
                                <li> <?php echo xlt('Patient Recalls'); ?></li>
                                <li> <?php echo xlt('Office Announcements'); ?></li>
                                <li> <?php echo xlt('Patient Surveys'); ?></li>
                            </ul>
                        </div>
                        <div class="col-sm-4 col-xs-offset-1 text-center">
                            <h3 class="title"><?php echo xlt('Channels'); ?>:</h3>
                            <ul class="text-left" style="margin-left:75px;">
                                <li> <?php echo xlt('SMS Messages'); ?></li>
                                <li> <?php echo xlt('Voice Messages'); ?></li>
                                <li> <?php echo xlt('E-mail Messaging'); ?></li>
                                <li> <?php echo xlt('Postcards'); ?></li>
                                <li> <?php echo xlt('Address Labels'); ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="text-center row showReminders">
                        <input value="<?php echo xla('Sign-up'); ?>" onclick="goReminderRecall('setup&stage=2');" class="btn btn-primary">
                    </div>

                </div>
            </div>
            </div>

            <?php
        } else if ($stage =='2') {
            ?>
            <div class="row">
            <form name="medex_start" id="medex_start">
                <div class="col-sm-10 col-sm-offset-1 text-center">
                    <div id="setup_1" class="showReminders borderShadow">
                        <div class="title row fa"><?php echo xlt('Register'); ?>: MedEx Bank</div>
                        <div class="row showReminders">
                            <div class="fa col-sm-10 col-sm-offset-1 text-center">
                                <div class="divTable4" id="answer" name="answer">
                                    <div class="divTableBody">
                                        <div class="divTableRow">
                                            <div class="divTableCell divTableHeading">
                                                <?php echo xlt('E-mail'); ?>
                                            </div>
                                            <div class="divTableCell">
                                                <i id="email_check" name="email_check" class="top_right_corner nodisplay red fa fa-check"></i>
                                                <input type="text" data-rule-email="true" class="form-control" id="new_email" name="new_email" value="<?php echo attr($GLOBALS['user_data']['email']); ?>" placeholder="<?php echo xla('your email address'); ?>" required>
                                                <div class="signup_help nodisplay" id="email_help" name="email_help"><?php echo xlt('Please provide a valid e-mail address to proceed'); ?>...</div>

                                            </div>
                                        </div>
                                        <div class="divTableRow">
                                            <div class="divTableCell divTableHeading">
                                                <?php echo xlt('Password'); ?>
                                            </div>
                                            <div class="divTableCell"><i id="pwd_check" name="pwd_check" class="top_right_corner nodisplay red fa fa-check"></i>
                                                <i class="fa top_right_corner fa-question" id="pwd_ico_help" aria-hidden="true" onclick="$('#pwd_help').toggleClass('nodisplay');"></i>
                                                <input type="password" placeholder="<?php xla('Password'); ?>" id="new_password" name="new_password" class="form-control" required>
                                                <div id="pwd_help" class="nodisplay signup_help"><?php echo xlt('Secure Password Required').": ". xlt('8-12 characters long, including at least one upper case letter, one lower case letter, one number, one special character and no common strings'); ?>...</div>
                                            </div>
                                        </div>
                                        <div class="divTableRow">
                                            <div class="divTableCell divTableHeading">
                                                <?php echo xlt('Repeat'); ?>
                                            </div>
                                            <div class="divTableCell"><i id="pwd_rcheck" name="pwd_rcheck" class="top_right_corner nodisplay red fa fa-check"></i>
                                                <input type="password" placeholder="<?php echo xla('Repeat password'); ?>" id="new_rpassword" name="new_rpassword" class="form-control" required>
                                                <div id="pwd_rhelp" class="nodisplay signup_help" style=""><?php echo xlt('Passwords do not match.'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="ihvread" name="ihvread" class="fa text-left">
                                    <input type="checkbox" class="updated required" name="TERMS_yes" id="TERMS_yes" required>
                                    <label for="TERMS_yes" class="input-helper input-helper--checkbox" data-toggle="tooltip" data-placement="auto" title="Terms and Conditions"><?php echo xlt('I have read and my practice agrees to the'); ?>
                                        <a href="#" onclick="cascwin('https://medexbank.com/cart/upload/index.php?route=information/information&information_id=5','TERMS',800, 600);">MedEx <?php echo xlt('Terms and Conditions'); ?></a></label><br />
                                    <input type="checkbox" class="updated required" name="BusAgree_yes" id="BusAgree_yes" required>
                                    <label for="BusAgree_yes" class="input-helper input-helper--checkbox" data-toggle="tooltip" data-placement="auto" title="BAA"><?php echo xlt('I have read and accept the'); ?>
                                    <a href="#" onclick="cascwin('https://medexbank.com/cart/upload/index.php?route=information/information&information_id=8','Bus Assoc Agree',800, 600);">MedEx <?php echo xlt('Business Associate Agreement'); ?></a></label>
                                    <br />
                                    <div class="align-center row showReminders">
                                        <input id="Register" class="btn btn-primary" value="<?php echo xla('Register'); ?>" />
                                    </div>

                                    <div id="myModal" class="modal fade" role="dialog">
                                      <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                          <div class="modal-header"  style="background-color: #0d4867;color: #fff;font-weight: 700;">
                                            <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;box-shadow:unset !important;">&times;</button>
                                            <h2 class="modal-title" style="font-weight:600;">Sign-Up Confirmation</h2>
                                          </div>
                                          <div class="modal-body" style="padding: 10px 45px;">
                                            <p>You are opening a secure connection to MedExBank.com.  During this step your EHR will synchronize with the MedEx servers.  <br />
                                                <br />
                                                Re-enter your username (e-mail) and password in the MedExBank.com login window to:
                                                <ul style="text-align: left;width: 90%;margin: 0 auto;">
                                                    <li> confirm your practice and providers' information</li>
                                                    <li> choose your service options</li>
                                                    <li> update and activate your messages </li>
                                                </ul>
                                            </p>
                                          </div>
                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-default" onlick="actualSignUp();" id="actualSignUp">Proceed</button>
                                          </div>
                                        </div>

                                      </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
            $(document).ready(function() {
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
        } else if (isset($response['error'])) {
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
            $sessionFile = $GLOBALS['temporary_files_dir'].'/cookiejar_MedExAPI';
        }
        $this->url      = rtrim('https://'.preg_replace('/^https?\:\/\//', '', $url), '/') . '/cart/upload/index.php?route=api/';
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

    public function login()
    {
        $response= array();
        $result = sqlStatement("SHOW TABLES LIKE 'medex_prefs'");
        $table2Exists = sqlFetchArray($result);
        if (!$table2Exists) {
            return false;
        }

        $query = "SELECT * FROM medex_prefs";
        $info = sqlFetchArray(sqlStatement($query));
        $username = $info['ME_username'];
        $key = $info['ME_api_key'];
        $UID = $info['MedEx_id'];
        $callback_key = $_POST['callback_key'];
        if (empty($username) || empty($key) || empty($UID)) {
            return false;//throw new InvalidCredentialsException("API Credentials are incomplete.");
        }
        $this->curl->setUrl($this->getUrl('login'));
        $this->curl->setData(array(
            'username' => $username,
            'key' => $key,
            'UID' => $UID,
            'MedEx' => 'openEMR',
            'callback_key' => $callback_key
        ));
        $log = "/tmp/medex.log" ;
        $stdlog = fopen($log, 'a');
        $timed = date(DATE_RFC2822);
        fputs($stdlog, "\n IN API.php::login ".$timed."\n");

        $this->curl->makeRequest();
        $response = $this->curl->getResponse();
        fputs($stdlog, "\n ".$this->curl->getRawResponse()."\n");

        if (isset($response['success']) && isset($response['token'])) {
            return $response;
        } else if (isset($response['error'])) {
            $this->lastError = $response['error'];
            sqlQuery("UPDATE `background_services` SET `active`='0' WHERE `name`='MedEx'");
        }
        return false;
    }

    public function getUrl($method)
    {
        return $this->url . $method; }

    public function checkModality($event, $appt)
    {
        $sqlQuery = "SELECT * FROM medex_icons";
        $result = sqlStatement($sqlQuery);
        while ($icons = sqlFetchArray($result)) {
            //substitute data-toggle="tooltip" data-placement="auto" title="..." with data-toggle="tooltip" data-placement="auto" title="'.xla('...').'" in $icons['i_html']
            $title = preg_match('/title=\"(.*)\"/', $icons['i_html']);
            $xl_title = xla($title);
            $icons['i_html'] = str_replace($title, $xl_title, $icons['i_html']);
            $icon[$icons['msg_type']][$icons['msg_status']] = $icons['i_html'];
        }
        if ($event['M_type'] =="SMS") {
            if (empty($appt['phone_cell']) || ($appt["hipaa_allowsms"]=="NO")) {
                return array($icon['SMS']['NotAllowed'],false);
            } else {
                $phone = preg_replace("/[^0-9]/", "", $appt["phone_cell"]);
                return array($icon['SMS']['ALLOWED'],$phone);     // It is allowed and they have a cell phone
            }
        } else if ($event['M_type'] =="AVM") {
            if ((empty($appt["phone_home"]) && (empty($appt["phone_cell"])) || ($appt["hipaa_voice"]=="NO"))) {
                return array($icon['AVM']['NotAllowed'],false);
            } else {
                if (!empty($appt["phone_cell"])) {
                    $phone = preg_replace("/[^0-9]/", "", $appt["phone_cell"]);
                } else {
                    $phone = preg_replace("/[^0-9]/", "", $appt["phone_home"]);
                }
                return array($icon['AVM']['ALLOWED'],$phone); //We have a phone to call and permission!
            }
        } else if ($event['M_type'] =="EMAIL") {
            if (($appt["email"]=="")||($appt["hipaa_allowemail"]=="NO")) {
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