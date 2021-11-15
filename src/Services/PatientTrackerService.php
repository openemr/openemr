<?php

/**
 * PatientTrackerService Handles the select, create, and update of patient events that we are track.  This is used typically
 * in the patient flow board and for patient reporting.
 *
 * Much of this code was refactored from the patient_tracker.inc.php file.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (C) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Events\Services\ServiceSaveEvent;

class PatientTrackerService extends BaseService
{
    const TABLE_NAME = "patient_tracker";
    const TABLE_NAME_TRACKER_ELEMENT = "patient_tracker_element";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * @param $tracker_from_time
     * @param $tracker_to_time
     * @param bool $allow_sec
     * @return string
     */
    public function get_Tracker_Time_Interval($tracker_from_time, $tracker_to_time, $allow_sec = false)
    {

        $tracker_time_calc = strtotime($tracker_to_time) - strtotime($tracker_from_time);

        $tracker_time = "";
        if ($tracker_time_calc > 60 * 60 * 24) {
            $days = floor($tracker_time_calc / 60 / 60 / 24);
            if ($days >= 2) {
                $tracker_time .=  "$days " . xl('days');
            } else {
                $tracker_time .=  "$days " . xl('day');
            }

            $tracker_time_calc = $tracker_time_calc - ($days * (60 * 60 * 24));
        }

        if ($tracker_time_calc > 60 * 60) {
            $hours = floor($tracker_time_calc / 60 / 60);
            if (strlen($days != 0)) {
                if ($hours >= 2) {
                    $tracker_time .=  ", $hours " . xl('hours');
                } else {
                    $tracker_time .=  ", $hours " . xl('hour');
                }
            } else {
                if ($hours >= 2) {
                    $tracker_time .=  "$hours " . xl('hours');
                } else {
                    $tracker_time .=  "$hours " . xl('hour');
                }
            }

            $tracker_time_calc = $tracker_time_calc - ($hours * (60 * 60));
        }

        if ($allow_sec) {
            if ($tracker_time_calc > 60) {
                $minutes = floor($tracker_time_calc / 60);
                if (strlen($hours != 0)) {
                    if ($minutes >= 2) {
                        $tracker_time .=  ", $minutes " . xl('minutes');
                    } else {
                        $tracker_time .=  ", $minutes " . xl('minute');
                    }
                } else {
                    if ($minutes >= 2) {
                        $tracker_time .=  "$minutes " . xl('minutes');
                    } else {
                        $tracker_time .=  "$minutes " . xl('minute');
                    }
                }

                $tracker_time_calc = $tracker_time_calc - ($minutes * 60);
            }
        } else {
            $minutes = round($tracker_time_calc / 60);
            if (!empty($hours) && strlen($hours != 0)) {
                if ($minutes >= 2) {
                    $tracker_time .=  ", $minutes " . xl('minutes');
                } else {
                    $tracker_time .=  ", $minutes " . xl('minute');
                }
            } else {
                if ($minutes >= 2) {
                    $tracker_time .=  "$minutes " . xl('minutes');
                } else {
                    if ($minutes > 0) {
                        $tracker_time .=  "$minutes " . xl('minute');
                    }
                }
            }

            $tracker_time_calc = $tracker_time_calc - ($minutes * 60);
        }

        if ($allow_sec) {
            if ($tracker_time_calc > 0) {
                if (strlen($minutes != 0)) {
                    if ($tracker_time_calc >= 2) {
                        $tracker_time .= ", $tracker_time_calc " . xl('seconds');
                    } else {
                        $tracker_time .= ", $tracker_time_calc " . xl('second');
                    }
                } else {
                    if ($tracker_time_calc >= 2) {
                        $tracker_time .= "$tracker_time_calc " . xl('seconds');
                    } else {
                        $tracker_time .= "$tracker_time_calc " . xl('second');
                    }
                }
            }
        }

        return $tracker_time ;
    }

    /**
     * This function will return false for both below scenarios:
     * 1. The tracker item does not exist
     * 2. If the tracker item does exist, but the encounter has not been set
     * @param $apptdate
     * @param $appttime
     * @param $pid
     * @param $eid
     * @return int
     */
    public function is_tracker_encounter_exist($apptdate, $appttime, $pid, $eid)
    {
        #Check to see if there is an encounter in the patient_tracker table.
        $enc_yn = sqlQuery("SELECT encounter from patient_tracker WHERE `apptdate` = ? AND encounter > 0 " .
            "AND `eid` = ? AND `pid` = ?", array($apptdate, $eid, $pid));
        if (empty($enc_yn['encounter']) || $enc_yn === false) {
            return (0);
        }

        return ($enc_yn['encounter']);
    }

    /**
     * this function will return the tracker id that is managed
     * or will return false if no tracker id was managed (in the case of a recurrent appointment)
     * @param $apptdate
     * @param $appttime
     * @param $eid
     * @param $pid
     * @param $user
     * @param string $status
     * @param string $room
     * @param string $enc_id
     * @return bool|int
     */
    public function manage_tracker_status($apptdate, $appttime, $eid, $pid, $user, $status = '', $room = '', $enc_id = '')
    {
        #First ensure the eid is not a recurrent appointment. If it is, then do not do anything and return false.
        $pc_appt =  sqlQuery("SELECT `pc_recurrtype` FROM `openemr_postcalendar_events` WHERE `pc_eid` = ?", array($eid));
        if ($pc_appt['pc_recurrtype'] != 0) {
            return false;
        }

        $datetime = date("Y-m-d H:i:s");
        if (is_null($room)) {
            $room = '';
        }

        #Check to see if there is an entry in the patient_tracker table.
        $tracker = sqlQuery("SELECT id, apptdate, appttime, eid, pid, original_user, encounter, lastseq," .
            "patient_tracker_element.room AS lastroom,patient_tracker_element.status AS laststatus " .
            "from `patient_tracker`" .
            "LEFT JOIN patient_tracker_element " .
            "ON patient_tracker.id = patient_tracker_element.pt_tracker_id " .
            "AND patient_tracker.lastseq = patient_tracker_element.seq " .
            "WHERE `apptdate` = ? AND `appttime` = ? " .
            "AND `eid` = ? AND `pid` = ?", array($apptdate,$appttime,$eid,$pid));

        if (empty($tracker)) {
            #Add a new tracker.
            $tracker_id = sqlInsert(
                "INSERT INTO `patient_tracker` " .
                "(`date`, `apptdate`, `appttime`, `eid`, `pid`, `original_user`, `encounter`, `lastseq`) " .
                "VALUES (?,?,?,?,?,?,?,'1')",
                array($datetime,$apptdate,$appttime,$eid,$pid,$user,$enc_id)
            );
            #If there is a status or a room, then add a tracker item.
            if (!empty($status) || !empty($room)) {
                sqlStatement(
                    "INSERT INTO `patient_tracker_element` " .
                    "(`pt_tracker_id`, `start_datetime`, `user`, `status`, `room`, `seq`) " .
                    "VALUES (?,?,?,?,?,'1')",
                    array($tracker_id,$datetime,$user,$status,$room)
                );
            }
            $tracker = [
                'id' => $tracker_id
                ,'date' => $datetime
                ,'apptdate' => $apptdate
                ,'appttime' => $appttime
                ,'eid' => $eid
                ,'pid' => $pid
                ,'original_user' => $user
                ,'encounter' => $enc_id
                ,'lastseq' => '1'
                ,'element' => [
                    'pt_tracker_id' => $tracker_id
                    ,'start_datetime' => $datetime
                    ,'user' => $user
                    ,'status' => $status
                    ,'room' => $room
                    ,'seq' => '1'
                ]
            ];
        } else {
            #Tracker already exists.
            $tracker_id = $tracker['id'];
            if (($status != $tracker['laststatus']) || ($room != $tracker['lastroom'])) {
                #Status or room has changed, so need to update tracker.
                #Update lastseq in tracker.
                sqlStatement(
                    "UPDATE `patient_tracker` SET  `lastseq` = ? WHERE `id` = ?",
                    array(($tracker['lastseq'] + 1),$tracker_id)
                );
                #Add a tracker item.
                sqlStatement(
                    "INSERT INTO `patient_tracker_element` " .
                    "(`pt_tracker_id`, `start_datetime`, `user`, `status`, `room`, `seq`) " .
                    "VALUES (?,?,?,?,?,?)",
                    array($tracker_id,$datetime,$user,$status,$room,($tracker['lastseq'] + 1))
                );
            }

            if (!empty($enc_id)) {
                #enc_id (encounter number) is not blank, so update this in tracker.
                sqlStatement("UPDATE `patient_tracker` SET `encounter` = ? WHERE `id` = ?", array($enc_id,$tracker_id));
            }
            $tracker['lastseq'] = $tracker['lastseq'] + 1;
            $tracker['element'] = [
                    'pt_tracker_id' => $tracker_id
                    ,'start_datetime' => $datetime
                    ,'user' => $user
                    ,'status' => $status
                    ,'room' => $room
                    ,'seq' => $tracker['lastseq']
            ];
        }

        #Ensure the entry in calendar appt entry has been updated.
        $pc_appt =  sqlQuery("SELECT `pc_apptstatus`, `pc_room` FROM `openemr_postcalendar_events` WHERE `pc_eid` = ?", array($eid));
        if ($status != $pc_appt['pc_apptstatus']) {
            sqlStatement("UPDATE `openemr_postcalendar_events` SET `pc_apptstatus` = ? WHERE `pc_eid` = ?", array($status,$eid));
        }

        if ($room != $pc_appt['pc_room']) {
            sqlStatement("UPDATE `openemr_postcalendar_events` SET `pc_room` = ? WHERE `pc_eid` = ?", array($room,$eid));
        }

        $GLOBALS['kernel']->getEventDispatcher()->dispatch(new ServiceSaveEvent($this, $tracker), ServiceSaveEvent::EVENT_POST_SAVE);

        # Returning the tracker id that has been managed
        return $tracker_id;
    }

    /**
     * This is used to break apart the information contained in the notes field of
     * list_options. Currently the color and alert time are the only items stored
     * @param $option
     * @return array
     */
    public function collectApptStatusSettings($option)
    {
        $color_settings = array();
        $row = sqlQuery("SELECT notes FROM list_options WHERE " .
            "list_id = 'apptstat' AND option_id = ? AND activity = 1", array($option));
        if (empty($row['notes'])) {
            return $option;
        }

        list($color_settings['color'], $color_settings['time_alert']) = explode("|", $row['notes']);
        return $color_settings;
    }

    /**
     * This is used to collect the tracker elements for the Patient Flow Board Report
     * returns the elements in an array
     * @param $trackerid
     * @return mixed
     */
    public function collect_Tracker_Elements($trackerid)
    {
        $res = sqlStatement("SELECT * FROM patient_tracker_element WHERE pt_tracker_id = ? ORDER BY LENGTH(seq), seq ", array($trackerid));
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $returnval[$iter] = $row;
        }

        return $returnval;
    }

    /**
     * used to determine check in time
     * @param $trackerid
     * @return bool
     */
    public function collect_checkin($trackerid)
    {
        $tracker = sqlQuery(
            "SELECT patient_tracker_element.start_datetime " .
            "FROM patient_tracker_element " .
            "INNER JOIN list_options " .
            "ON patient_tracker_element.status = list_options.option_id " .
            "WHERE  list_options.list_id = 'apptstat' " .
            "AND list_options.toggle_setting_1 = '1' AND list_options.activity = 1 " .
            "AND patient_tracker_element.pt_tracker_id = ?",
            array($trackerid)
        );
        if (empty($tracker['start_datetime'])) {
            return false;
        } else {
            return $tracker['start_datetime'];
        }
    }

    /**
     * used to determine check out time
     * @param $trackerid
     * @return bool
     */
    public function collect_checkout($trackerid)
    {
        $tracker = sqlQuery(
            "SELECT patient_tracker_element.start_datetime " .
            "FROM patient_tracker_element " .
            "INNER JOIN list_options " .
            "ON patient_tracker_element.status = list_options.option_id " .
            "WHERE  list_options.list_id = 'apptstat' " .
            "AND list_options.toggle_setting_2 = '1' AND list_options.activity = 1 " .
            "AND patient_tracker_element.pt_tracker_id = ?",
            array($trackerid)
        );
        if (empty($tracker['start_datetime'])) {
            return false;
        } else {
            return $tracker['start_datetime'];
        }
    }

    public function getApptStatus($appointments)
    {
        $astat = array();
        $astat['count_all'] = count($appointments);
        //group the appointment by status
        foreach ($appointments as $appointment) {
            $astat[$appointment['pc_apptstatus']] += 1;
        }

        return $astat;
    }
}
