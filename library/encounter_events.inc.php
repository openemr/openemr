<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Paul Simon K <paul@zhservices.com> 
//
// +------------------------------------------------------------------------------+

require_once(dirname(__FILE__) . '/calendar.inc');
require_once(dirname(__FILE__) . '/forms.inc');
require_once(dirname(__FILE__) . '/patient_tracker.inc.php');


//===============================================================================
//This section handles the events of payment screen.
//===============================================================================
define('REPEAT_EVERY_DAY',     0);
define('REPEAT_EVERY_WEEK',    1);
define('REPEAT_EVERY_MONTH',   2);
define('REPEAT_EVERY_YEAR',    3);
define('REPEAT_EVERY_WORK_DAY',4);
	define('REPEAT_DAYS_EVERY_WEEK', 6);
//===============================================================================
//Create event in calender as arrived
function calendar_arrived($form_pid) {
	$today=date('Y-m-d');
	//Take all recurring events relevent for today.
	$result_event=sqlStatement("SELECT * FROM openemr_postcalendar_events WHERE pc_recurrtype != '0' and pc_pid = ? and pc_endDate != '0000-00-00'
		and pc_eventDate < ? and pc_endDate >= ? ",
		array($form_pid,$today,$today));
	if(sqlNumRows($result_event)==0)//no repeating appointment
	 {
	 	$result_event=sqlStatement("SELECT * FROM openemr_postcalendar_events WHERE pc_pid =?	and pc_eventDate = ?",
			array($form_pid,$today));
		if(sqlNumRows($result_event)==0)//no appointment
		 {
			echo "<br><br><br>".htmlspecialchars( xl('Sorry No Appointment is Fixed'), ENT_QUOTES ).". ".htmlspecialchars( xl('No Encounter could be created'), ENT_QUOTES ).".";
			die;
		 }
		else//one appointment
		 {
		 	sqlStatement("UPDATE openemr_postcalendar_events SET pc_apptstatus ='@' WHERE pc_pid =? and pc_eventDate = ?",
		 			array($form_pid,$today));
		 	$enc = todaysEncounterCheck($form_pid);//create encounter
			 $zero_enc=0;
		 }
	 }
	else//repeating appointment set
	 {
	 	while($row_event=sqlFetchArray($result_event))
		 {
			$pc_eid = $row_event['pc_eid'];
			$pc_eventDate = $row_event['pc_eventDate'];
			$pc_recurrspec_array = unserialize($row_event['pc_recurrspec']);
			while(1)
			 {
			 	if($pc_eventDate==$today)//Matches so insert.
				 {
				 if(!$exist_eid=check_event_exist($pc_eid))
					{
					 update_event($pc_eid);
					}
				 else
					{
					 sqlStatement("UPDATE openemr_postcalendar_events SET pc_apptstatus = '@' WHERE pc_eid = ?",
						 array($exist_eid));
					}
					 $enc = todaysEncounterCheck($form_pid);//create encounter
					 $zero_enc=0;
				 break;
				 }
				elseif($pc_eventDate>$today)//the frequency does not match today,no need to increment furthur.
				 {
					echo "<br><br><br>".htmlspecialchars( xl('Sorry No Appointment is Fixed'), ENT_QUOTES ).". ".htmlspecialchars( xl('No Encounter could be created'), ENT_QUOTES ).".";
					die;
				 break;
				 }
				 
        // Added by Rod to handle repeats on nth or last given weekday of a month:
        if ($row_event['pc_recurrtype'] == 2) {
          $my_repeat_on_day = $pc_recurrspec_array['event_repeat_on_day'];
          $my_repeat_on_num = $pc_recurrspec_array['event_repeat_on_num'];
          $adate = getdate(strtotime($pc_eventDate));
          $adate['mon'] += 1;
          if ($adate['mon'] > 12) {
            $adate['year'] += 1;
            $adate['mon'] -= 12;
          }
          if ($my_repeat_on_num < 5) { // not last
            $adate['mday'] = 1;
            $dow = jddayofweek(cal_to_jd(CAL_GREGORIAN, $adate['mon'], $adate['mday'], $adate['year']));
            if ($dow > $my_repeat_on_day) $dow -= 7;
            $adate['mday'] += ($my_repeat_on_num - 1) * 7 + $my_repeat_on_day - $dow;
          }
          else { // last weekday of month
            $adate['mday'] = cal_days_in_month(CAL_GREGORIAN, $adate['mon'], $adate['year']);
            $dow = jddayofweek(cal_to_jd(CAL_GREGORIAN, $adate['mon'], $adate['mday'], $adate['year']));
            if ($dow < $my_repeat_on_day) $dow += 7;
            $adate['mday'] += $my_repeat_on_day - $dow;
          }
          $pc_eventDate = date('Y-m-d', mktime(0, 0, 0, $adate['mon'], $adate['mday'], $adate['year']));
        } // end recurrtype 2

        else { // pc_recurrtype is 1
        	$pc_eventDate_array = explode('-', $pc_eventDate);
				  // Find the next day as per the frequency definition.
				  $pc_eventDate =& __increment($pc_eventDate_array[2], $pc_eventDate_array[1], $pc_eventDate_array[0],
            $pc_recurrspec_array['event_repeat_freq'], $pc_recurrspec_array['event_repeat_freq_type']);
        }
        
			 }
		 }
	 }
	 return $enc;
}
//===============================================================================
// Checks for the patient's encounter ID for today, creating it if there is none.
//
function todaysEncounterCheck($patient_id, $enc_date = '', $reason = '', $fac_id = '', $billing_fac = '', $provider = '', $cat = '', $return_existing = true){
  global $today;
	$encounter = todaysEncounterIf($patient_id);
	if($encounter){
		if($return_existing){
			return $encounter;
		}else{
			return 0;
		}
	}
    if(is_array($provider)){
        $visit_provider = (int)$provider[0];
    } elseif($provider){
        $visit_provider = (int)$provider;
    } else {
        $visit_provider = '(NULL)';
    }
    // Validate date format
    $chk_dt = date_create_from_format('Y-m-d', $enc_date);
    $dos = ($chk_dt ? $enc_date : $today);
	$visit_reason = $reason ? $reason : xl('Please indicate visit reason');
  $tmprow = sqlQuery("SELECT username, facility, facility_id FROM users WHERE id = ?", array($_SESSION["authUserID"]) );
  $username = $tmprow['username'];
  $facility = $tmprow['facility'];
  $facility_id = $fac_id ? (int)$fac_id : $tmprow['facility_id'];
	$billing_facility = $billing_fac ? (int)$billing_fac : $tmprow['facility_id'];
	$visit_cat = $cat ? $cat : '(NULL)';
  $conn = $GLOBALS['adodb']['db'];
  $encounter = $conn->GenID("sequences");
  addForm($encounter, "New Patient Encounter",
    sqlInsert("INSERT INTO form_encounter SET " .
      "date = ?, " .
      "reason = ?, " .
      "facility = ?, " .
      "facility_id = ?, " .
      "billing_facility = ?, " .
			"provider_id = ?, " .
      "pid = ?, " .
      "encounter = ?," .
			"pc_catid = ?",
			array($dos,$visit_reason,$facility,$facility_id,$billing_facility,$visit_provider,$patient_id,$encounter,$visit_cat)
    ),
    "newpatient", $patient_id, "1", "NOW()", $username
  );
  return $encounter;
}

    //===============================================================================
    // Checks for the group's encounter ID for today, creating it if there is none.
    //
    function todaysTherapyGroupEncounterCheck($group_id, $enc_date = '', $reason = '', $fac_id = '', $billing_fac = '', $provider = '', $cat = '', $return_existing = true, $eid = null){
        global $today;
        $encounter = todaysTherapyGroupEncounterIf($group_id);
        if($encounter){
            if($return_existing){
                return $encounter;
            }else{
                return 0;
            }
        }
        if(is_array($provider)){
            $visit_provider = (int)$provider[0];
            $counselors = implode(',', $provider);
		} elseif($provider){
            $visit_provider = $counselors = (int)$provider;
		} else {
            $visit_provider = $counselors = NULL;
		}
        $dos = $enc_date ? $enc_date : $today;
        $visit_reason = $reason ? $reason : 'Please indicate visit reason';
        $tmprow = sqlQuery("SELECT username, facility, facility_id FROM users WHERE id = ?", array($_SESSION["authUserID"]) );
        $username = $tmprow['username'];
        $facility = $tmprow['facility'];
        $facility_id = $fac_id ? (int)$fac_id : $tmprow['facility_id'];
        $billing_facility = $billing_fac ? (int)$billing_fac : $tmprow['facility_id'];
        $visit_cat = $cat ? $cat : '(NULL)';
        $conn = $GLOBALS['adodb']['db'];
        $encounter = $conn->GenID("sequences");
        addForm($encounter, "New Therapy Group Encounter",
            sqlInsert("INSERT INTO form_groups_encounter SET " .
                "date = ?, " .
                "reason = ?, " .
                "facility = ?, " .
                "facility_id = ?, " .
                "billing_facility = ?, " .
                "provider_id = ?, " .
                "group_id = ?, " .
                "encounter = ?," .
                "pc_catid = ? ," .
                "appt_id = ? ," .
				"counselors = ? ",
                array($dos,$visit_reason,$facility,$facility_id,$billing_facility,$visit_provider,$group_id,$encounter,$visit_cat, $eid, $counselors)
            ),
            "newGroupEncounter", NULL, "1", "NOW()", $username, "", $group_id
        );
        return $encounter;
    }
//===============================================================================
// Get the patient's encounter ID for today, if it exists.
// In the case of more than one encounter today, pick the last one.
//
function todaysEncounterIf($patient_id) {
  global $today;
  $tmprow = sqlQuery("SELECT encounter FROM form_encounter WHERE " .
    "pid = ? AND date = ? " .
    "ORDER BY encounter DESC LIMIT 1",array($patient_id,"$today 00:00:00"));
  return empty($tmprow['encounter']) ? 0 : $tmprow['encounter'];
}
//===============================================================================
// Get the group's encounter ID for today, if it exists.
// In the case of more than one encounter today, pick the last one.
//
function todaysTherapyGroupEncounterIf($group_id) {
	global $today;
	$tmprow = sqlQuery("SELECT encounter FROM form_groups_encounter WHERE " .
		"group_id = ? AND date = ? " .
		"ORDER BY encounter DESC LIMIT 1",array($group_id,"$today 00:00:00"));
	return empty($tmprow['encounter']) ? 0 : $tmprow['encounter'];
}
//===============================================================================

// Get the patient's encounter ID for today, creating it if there is none.
//
function todaysEncounter($patient_id, $reason='') {
  global $today, $userauthorized;

  if (empty($reason)) $reason = xl('Please indicate visit reason');

  // Was going to use the existing encounter for today if there is one, but
  // decided it's right to always create a new one.  Leaving the code here
  // (and corresponding function above) in case it is ever wanted later.
  /*******************************************************************
  $encounter = todaysEncounterIf($patient_id);
  if ($encounter) return $encounter;
  *******************************************************************/

  $tmprow = sqlQuery("SELECT username, facility, facility_id FROM users " .
    "WHERE id = ?", array($_SESSION["authUserID"]));
  $username = $tmprow['username'];
  $facility = $tmprow['facility'];
  $facility_id = $tmprow['facility_id'];
  $conn = $GLOBALS['adodb']['db'];
  $encounter = $conn->GenID("sequences");
  $provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;
  addForm($encounter, "New Patient Encounter",
    sqlInsert("INSERT INTO form_encounter SET date = ?, onset_date = ?, "  .
      "reason = ?, facility = ?, facility_id = ?, pid = ?, encounter = ?, " .
      "provider_id = ?",
      array($today, $today, $reason, $facility, $facility_id, $patient_id,
        $encounter, $provider_id)
    ),
    "newpatient", $patient_id, $userauthorized, "NOW()", $username
  );
  return $encounter;
}
//===============================================================================
// get the original event's repeat specs
function update_event($eid)
 {
	$origEventRes = sqlStatement("SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?",array($eid));
	$origEvent=sqlFetchArray($origEventRes);
	$oldRecurrspec = unserialize($origEvent['pc_recurrspec']);
	$duration=$origEvent['pc_duration'];
	$starttime=$origEvent['pc_startTime'];
	$endtime=$origEvent['pc_endTime'];
	$selected_date = date("Ymd");
	if ($oldRecurrspec['exdate'] != "") { $oldRecurrspec['exdate'] .= ",".$selected_date; }
	else { $oldRecurrspec['exdate'] .= $selected_date; }
	// mod original event recur specs to exclude this date
	sqlStatement("UPDATE openemr_postcalendar_events SET pc_recurrspec = ? WHERE pc_eid = ?",array(serialize($oldRecurrspec),$eid));
	// specify some special variables needed for the INSERT
  // no recurr specs, this is used for adding a new non-recurring event
    $noRecurrspec = array("event_repeat_freq" => "",
                        "event_repeat_freq_type" => "",
                        "event_repeat_on_num" => "1",
                        "event_repeat_on_day" => "0",
                        "event_repeat_on_freq" => "0",
                        "exdate" => ""
                    );
    // Useless garbage that we must save.
    $locationspecs = array("event_location" => "",
                            "event_street1" => "",
                            "event_street2" => "",
                            "event_city" => "",
                            "event_state" => "",
                            "event_postal" => ""
                        );
    $locationspec = serialize($locationspecs);
	$args['event_date'] = date('Y-m-d');
	$args['duration'] = $duration;
	// this event is forced to NOT REPEAT
	$args['form_repeat'] = "0";
	$args['recurrspec'] = $noRecurrspec;
	$args['form_enddate'] = "0000-00-00";
	$args['starttime'] = $starttime;
	$args['endtime'] = $endtime;
	$args['locationspec'] = $locationspec;
	$args['form_category']=$origEvent['pc_catid'];
	$args['new_multiple_value']=$origEvent['pc_multiple'];
	$args['form_provider']=$origEvent['pc_aid'];
	$args['form_pid']=$origEvent['pc_pid'];
	$args['form_title']=$origEvent['pc_title'];
	$args['form_allday']=$origEvent['pc_alldayevent'];
	$args['form_apptstatus']='@';
	$args['form_prefcat']=$origEvent['pc_prefcatid'];
	$args['facility']=$origEvent['pc_facility'];
	$args['billing_facility']=$origEvent['pc_billing_location'];
	InsertEvent($args,'payment');
 }
//===============================================================================
// check if event exists
function check_event_exist($eid)
 {
	$origEventRes = sqlStatement("SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?",array($eid));
	$origEvent=sqlFetchArray($origEventRes);
	$pc_catid=$origEvent['pc_catid'];
	$pc_aid=$origEvent['pc_aid'];
	$pc_pid=$origEvent['pc_pid'];
	$pc_eventDate=date('Y-m-d');
	$pc_startTime=$origEvent['pc_startTime'];
	$pc_endTime=$origEvent['pc_endTime'];
	$pc_facility=$origEvent['pc_facility'];
	$pc_billing_location=$origEvent['pc_billing_location'];
	$pc_recurrspec_array = unserialize($origEvent['pc_recurrspec']);
	$origEvent = sqlStatement("SELECT * FROM openemr_postcalendar_events WHERE pc_eid != ? and pc_catid=? and pc_aid=? ".
		"and pc_pid=? and pc_eventDate=? and pc_startTime=? and pc_endTime=? and pc_facility=? and pc_billing_location=?",
		array($eid,$pc_catid,$pc_aid,$pc_pid,$pc_eventDate,$pc_startTime,$pc_endTime,$pc_facility,$pc_billing_location));
	if(sqlNumRows($origEvent)>0)
	 {
	  $origEventRow=sqlFetchArray($origEvent);
	  return $origEventRow['pc_eid'];
	 }
	else
	 {
		if(strpos($pc_recurrspec_array['exdate'],date('Ymd')) === false)//;'20110228'
		 {
		  return false;
		 }
		else
		 {//this happens in delete case
		  return true;
		 }
	 }
 }
//===============================================================================
// insert an event
// $args is mainly filled with content from the POST http var
function InsertEvent($args,$from = 'general') {
	$pc_recurrtype = '0';
	if ($args['form_repeat'] || $args['days_every_week']) {
		if($args['recurrspec']['event_repeat_freq_type'] == "6"){
			$pc_recurrtype = 3;
		}
		else {
			$pc_recurrtype = $args['recurrspec']['event_repeat_on_freq'] ? '2' : '1';
		}
	}
	$evt_cols = array(
			"pc_catid" => $args['form_category'],
			"pc_multiple" => (isset($args['new_multiple_value'])?$args['new_multiple_value']:''),
			"pc_aid" => $args['form_provider'],
			"pc_pid" => (empty($args['form_pid']) ? '' : $args['form_pid']),
			"pc_gid" => (empty($args['form_gid']) ? '' : $args['form_gid']),
			"pc_title" => $args['form_title'],
			"pc_time" => NOW(),
			"pc_hometext" => $args['form_comments'],
			"pc_informant" => $_SESSION['authUserID'],
			"pc_eventDate" => $args['event_date'],
			"pc_endDate" => fixDate($args['form_enddate']),
			"pc_duration" => $args['duration'],
			"pc_recurrtype" => $pc_recurrtype,
			"pc_recurrspec" => serialize($args['recurrspec']),
			"pc_startTime" => $args['starttime'],
			"pc_endTime" => $args['endtime'],
			"pc_alldayevent" => $args['form_allday'],
			"pc_apptstatus" => $args['form_apptstatus'],
			"pc_prefcatid" => $args['form_prefcat'],
			"pc_location" => $args['locationspec'],
			"pc_eventstatus" => 1,
			"pc_sharing" => 1,
			"pc_facility" => (int)$args['facility'],
			"pc_billing_location" => (int)$args['billing_facility'],
			"pc_room" => (empty($args['form_room']) ? '' : $args['form_room']),
	);
	$evt_sql = '';
	$evt_pin = '';
	$evt_vals = array();
	foreach ($evt_cols as $col => $val) {
		$evt_sql .= $col.',';
		$evt_pin .= "?,";
		array_push($evt_vals, $val);
	}
	$evt_sql = sprintf('INSERT INTO openemr_postcalendar_events (%s) VALUES (%s)',
			rtrim($evt_sql,","), rtrim($evt_pin,","));
	$pc_eid = sqlInsert($evt_sql, $evt_vals);
	
	// mdsupport - Decide tracker action based on apptstatus instead of $from == 'general' or 'payment'
	if (is_checkin($evt_cols["pc_apptstatus"])) {
		manage_tracker_status($args['event_date'],$args['starttime'],$pc_eid,$form_pid,
				$_SESSION['authUser'],$args['form_apptstatus'],$args['form_room']);
		// Set event id for use by manage tracker module to set correct encounter in tracker during check in
		$GLOBALS['temporary-eid-for-manage-tracker'] = $pc_eid;
	}
	return $pc_eid;
}
//================================================================================================================
/**
 *	__increment()
 *	returns the next valid date for an event based on the
 *	current day,month,year,freq and type
 *  @private
 *	@returns string YYYY-MM-DD
 */
function &__increment($d,$m,$y,$f,$t)
{

	if($t == REPEAT_EVERY_DAY) {
        return date('Y-m-d',mktime(0,0,0,$m,($d+$f),$y));
    } elseif($t == REPEAT_EVERY_WORK_DAY) {
        // a workday is defined as Mon,Tue,Wed,Thu,Fri
        // repeating on every or Nth work day means to not include
        // weekends (Sat/Sun) in the increment... tricky

        // ugh, a day-by-day loop seems necessary here, something where
        // we can check to see if the day is a Sat/Sun and increment
        // the frequency count so as to ignore the weekend. hmmmm....
        $orig_freq = $f;
		for ($daycount=1; $daycount<=$orig_freq; $daycount++) {
			$nextWorkDOW = date('w',mktime(0,0,0,$m,($d+$daycount),$y));
			if (is_weekend_day($nextWorkDOW)) { $f++; }
		}

        // and finally make sure we haven't landed on a end week days
        // adjust as necessary
        $nextWorkDOW = date('w',mktime(0,0,0,$m,($d+$f),$y));
        if (count($GLOBALS['weekend_days']) === 2){
			if ($nextWorkDOW == $GLOBALS['weekend_days'][0]) {
				$f+=2;
			}elseif($nextWorkDOW == $GLOBALS['weekend_days'][1]){
				 $f++;
			}
		} elseif(count($GLOBALS['weekend_days']) === 1 && $nextWorkDOW === $GLOBALS['weekend_days'][0]) {
			$f++;
		}

		return date('Y-m-d',mktime(0,0,0,$m,($d+$f),$y));

    } elseif($t == REPEAT_EVERY_WEEK) {
        return date('Y-m-d',mktime(0,0,0,$m,($d+(7*$f)),$y));
    } elseif($t == REPEAT_EVERY_MONTH) {
        return date('Y-m-d',mktime(0,0,0,($m+$f),$d,$y));
    } elseif($t == REPEAT_EVERY_YEAR) {
        return date('Y-m-d',mktime(0,0,0,$m,$d,($y+$f)));
    }elseif($t == REPEAT_DAYS_EVERY_WEEK) {
		$old_appointment_date = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
		$next_appointment_date = getTheNextAppointment($old_appointment_date, $f);
		return $next_appointment_date;
	}
}

	function getTheNextAppointment($appointment_date, $freq){
		$day_arr = explode(",", $freq);
		$date_arr = array();
		foreach ($day_arr as $day){
			$day = getDayName($day);
			$date = date('Y-m-d', strtotime("next " . $day, strtotime($appointment_date)));
			array_push($date_arr, $date);
		}
		$next_appointment = getEarliestDate($date_arr);
		return $next_appointment;


	}

	function getDayName($day_num){
		if($day_num == "1"){return "sunday";}
		if($day_num == "2"){return "monday";}
		if($day_num == "3"){return "tuesday";}
		if($day_num == "4"){return "wednesday";}
		if($day_num == "5"){return "thursday";}
		if($day_num == "6"){return "friday";}
		if($day_num == "7"){return "saturday";}
	}


	function getEarliestDate($date_arr){
		$earliest = ($date_arr[0]);
		foreach ($date_arr as $date){
			if(strtotime($date) < strtotime($earliest)){
				$earliest = $date;
			}
		}
		return $earliest;
	}
//================================================================================================================
?>