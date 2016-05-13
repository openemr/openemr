  <?php
/** 
* library/patient_tracker.inc.php Functions used in the Patient Flow Board. 
* 
* Functions for use in the Patient Flow Board and Patient Flow Board Reports.
* 
* 
* Copyright (C) 2015 Terry Hill <terry@lillysystems.com> 
* 
* LICENSE: This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 3 
* of the License, or (at your option) any later version. 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details. 
* You should have received a copy of the GNU General Public License 
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;. 
* 
* @package OpenEMR 
* @author Terry Hill <terry@lillysystems.com>
* @link http://www.open-emr.org 
*
* Please help the overall project by sending changes you make to the author and to the OpenEMR community.
*
*/
require_once(dirname(__FILE__) . '/appointments.inc.php');

function get_Tracker_Time_Interval ($tracker_from_time, $tracker_to_time, $allow_sec=false) {

    $tracker_time_calc = strtotime($tracker_to_time) - strtotime($tracker_from_time);

    $tracker_time = "";
    if ($tracker_time_calc > 60*60*24) {        
        $days = floor($tracker_time_calc/60/60/24);
        if($days >= 2){   
          $tracker_time .=  "$days ". xl('days'); 
        }
        else
        {
          $tracker_time .=  "$days ". xl('day');   
        }
        $tracker_time_calc = $tracker_time_calc - ($days * (60*60*24));        
    }
    if ($tracker_time_calc > 60*60) {
        $hours = floor($tracker_time_calc/60/60);
        if(strlen($days != 0)) {
          if($hours >= 2){   
             $tracker_time .=  ", $hours " . xl('hours'); 
          }
          else
          {
             $tracker_time .=  ", $hours " . xl('hour');   
          }
        }
        else
        {
          if($hours >= 2){ 
             $tracker_time .=  "$hours " . xl('hours');
          }
          else
          {
             $tracker_time .=  "$hours " . xl('hour');   
          }
        }
        $tracker_time_calc = $tracker_time_calc - ($hours * (60*60));        
    }
    if ($allow_sec) { 
     if ($tracker_time_calc > 60) {
        $minutes = floor($tracker_time_calc/60);
        if(strlen($hours != 0)) {
          if($minutes >= 2){   
            $tracker_time .=  ", $minutes " . xl('minutes');
          }
          else
          {
            $tracker_time .=  ", $minutes " . xl('minute');
          }          
         }
        else
        {  
          if($minutes >= 2){     
            $tracker_time .=  "$minutes " . xl('minutes'); 
          }
          else
          {
            $tracker_time .=  "$minutes " . xl('minute'); 
          }          
        }        
        $tracker_time_calc = $tracker_time_calc - ($minutes * 60);        
     }
    }
    else
    {
       $minutes = round($tracker_time_calc/60);
        if(strlen($hours != 0)) {
          if($minutes >= 2){   
            $tracker_time .=  ", $minutes " . xl('minutes');
          }
          else
          {
            $tracker_time .=  ", $minutes " . xl('minute');
          }          
         }
        else
        {  
          if($minutes >= 2){     
            $tracker_time .=  "$minutes " . xl('minutes'); 
          }
          else
          {
            if($minutes > 0){ 
              $tracker_time .=  "$minutes " . xl('minute');
            }            
          }          
        }        
        $tracker_time_calc = $tracker_time_calc - ($minutes * 60);  
    }
      if ($allow_sec) {   
       if ($tracker_time_calc > 0) {
        if(strlen($minutes != 0)) {
          if($tracker_time_calc >= 2){   
            $tracker_time .= ", $tracker_time_calc " . xl('seconds'); 
          }
          else
          {
            $tracker_time .= ", $tracker_time_calc " . xl('second');               
          }    
         }
        else
        {
          if($tracker_time_calc >= 2){ 
            $tracker_time .= "$tracker_time_calc " . xl('seconds'); 
          }
          else
          {
            $tracker_time .= "$tracker_time_calc " . xl('second');    
          }
        }        
      }
}  
    return $tracker_time ;
} 

function fetch_Patient_Tracker_Events($from_date, $to_date) 
{
    # used to determine which providers to display in the Patient Tracker
    $provider_id = null;
    if ($_SESSION['userauthorized'] && $GLOBALS['docs_see_entire_calendar'] !='1') {
      $provider_id = $_SESSION[authUserID];
    }
    $events = fetchAppointments( $from_date, $to_date, null, $provider_id, null, null, null, null, null, true );
    return $events;
}

#check to see if a status code exist as a check in
function  is_checkin($option) {
  
  $row = sqlQuery("SELECT toggle_setting_1 FROM list_options WHERE " .
    "list_id = 'apptstat' AND option_id = ?", array($option));
  if (empty($row['toggle_setting_1'])) return(false);
  return(true);
}

#check to see if a status code exist as a check out
function  is_checkout($option) {
  
  $row = sqlQuery("SELECT toggle_setting_2 FROM list_options WHERE " .
    "list_id = 'apptstat' AND option_id = ?", array($option));
  if (empty($row['toggle_setting_2'])) return(false);
  return(true);
}


# This function will return false for both below scenarios:
#   1. The tracker item does not exist
#   2. If the tracker item does exist, but the encounter has not been set
function  is_tracker_encounter_exist($apptdate,$appttime,$pid,$eid) {
  #Check to see if there is an encounter in the patient_tracker table.
  $enc_yn = sqlQuery("SELECT encounter from patient_tracker WHERE `apptdate` = ? AND `appttime` = ? " .
                      "AND `eid` = ? AND `pid` = ?", array($apptdate,$appttime,$eid,$pid));
if ($enc_yn['encounter'] == '0' || $enc_yn == '0') return(false);
  return(true);
}

 # this function will return the tracker id that is managed
 # or will return false if no tracker id was managed (in the case of a recurrent appointment)
function manage_tracker_status($apptdate,$appttime,$eid,$pid,$user,$status='',$room='',$enc_id='') {

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
  $tracker = sqlQuery("SELECT id, apptdate, appttime, eid, pid, original_user, encounter, lastseq,".
                       "patient_tracker_element.room AS lastroom,patient_tracker_element.status AS laststatus ".
					   "from `patient_tracker`".
					   "LEFT JOIN patient_tracker_element " .
                       "ON patient_tracker.id = patient_tracker_element.pt_tracker_id " .
                       "AND patient_tracker.lastseq = patient_tracker_element.seq " .
					   "WHERE `apptdate` = ? AND `appttime` = ? " .
                       "AND `eid` = ? AND `pid` = ?", array($apptdate,$appttime,$eid,$pid));

  if (empty($tracker)) {
    #Add a new tracker.
    $tracker_id = sqlInsert("INSERT INTO `patient_tracker` " .
                            "(`date`, `apptdate`, `appttime`, `eid`, `pid`, `original_user`, `encounter`, `lastseq`) " .
                            "VALUES (?,?,?,?,?,?,?,'1')",
                            array($datetime,$apptdate,$appttime,$eid,$pid,$user,$enc_id));
    #If there is a status or a room, then add a tracker item.
    if (!empty($status) || !empty($room)) {
    sqlInsert("INSERT INTO `patient_tracker_element` " .
              "(`pt_tracker_id`, `start_datetime`, `user`, `status`, `room`, `seq`) " .
              "VALUES (?,?,?,?,?,'1')",
              array($tracker_id,$datetime,$user,$status,$room));
    }
  }
  else {
    #Tracker already exists.
    $tracker_id = $tracker['id'];
    if (($status != $tracker['laststatus']) || ($room != $tracker['lastroom'])) {
      #Status or room has changed, so need to update tracker.
      #Update lastseq in tracker.	  
	   sqlStatement("UPDATE `patient_tracker` SET  `lastseq` = ? WHERE `id` = ?",
                   array(($tracker['lastseq']+1),$tracker_id));
      #Add a tracker item.
      sqlInsert("INSERT INTO `patient_tracker_element` " .
                "(`pt_tracker_id`, `start_datetime`, `user`, `status`, `room`, `seq`) " .
                "VALUES (?,?,?,?,?,?)",
                array($tracker_id,$datetime,$user,$status,$room,($tracker['lastseq']+1)));
    }
    if (!empty($enc_id)) {
      #enc_id (encounter number) is not blank, so update this in tracker.
      sqlStatement("UPDATE `patient_tracker` SET `encounter` = ? WHERE `id` = ?", array($enc_id,$tracker_id));
    }  
  }
  #Ensure the entry in calendar appt entry has been updated.
  $pc_appt =  sqlQuery("SELECT `pc_apptstatus`, `pc_room` FROM `openemr_postcalendar_events` WHERE `pc_eid` = ?", array($eid));
  if ($status != $pc_appt['pc_apptstatus']) {
    sqlStatement("UPDATE `openemr_postcalendar_events` SET `pc_apptstatus` = ? WHERE `pc_eid` = ?", array($status,$eid));
  }
  if ($room != $pc_appt['pc_room']) {
    sqlStatement("UPDATE `openemr_postcalendar_events` SET `pc_room` = ? WHERE `pc_eid` = ?", array($room,$eid));
  }
  if( $GLOBALS['drug_screen'] && !empty($status)  && is_checkin($status)) {
    $yearly_limit = $GLOBALS['maximum_drug_test_yearly'];
    $percentage = $GLOBALS['drug_testing_percentage'];	  
    random_drug_test($tracker_id,$percentage,$yearly_limit);
  }
  # Returning the tracker id that has been managed
return $tracker_id;
}

# This is used to break apart the information contained in the notes field of
#list_options. Currently the color and alert time are the only items stored
function collectApptStatusSettings($option) {
  $color_settings = array();
  $row = sqlQuery("SELECT notes FROM list_options WHERE " .
    "list_id = 'apptstat' AND option_id = ?", array($option));
  if (empty($row['notes'])) return $option;
  list($color_settings['color'], $color_settings['time_alert']) = explode("|", $row['notes']);
  return $color_settings;
}

# This is used to collect the tracker elements for the Patient Flow Board Report
# returns the elements in an array 
function collect_Tracker_Elements($trackerid) 
{
 $res = sqlStatement("SELECT * FROM patient_tracker_element WHERE pt_tracker_id = ? ORDER BY LENGTH(seq), seq ", array($trackerid));
 for($iter=0; $row=sqlFetchArray($res); $iter++) {
  $returnval[$iter]=$row;
 }
return $returnval;
}

#used to determine check in time 
function collect_checkin($trackerid) {
  $tracker = sqlQuery("SELECT patient_tracker_element.start_datetime " .
                                   "FROM patient_tracker_element " .
                                   "INNER JOIN list_options " .
                                   "ON patient_tracker_element.status = list_options.option_id " .
                                   "WHERE  list_options.list_id = 'apptstat' " .
                                   "AND list_options.toggle_setting_1 = '1' " .
                                   "AND patient_tracker_element.pt_tracker_id = ?",
                                   array($trackerid));
  if (empty($tracker['start_datetime'])) {
    return false;
  }
  else {
    return $tracker['start_datetime'];
  }
}

#used to determine check out time
function collect_checkout($trackerid) {
  $tracker = sqlQuery("SELECT patient_tracker_element.start_datetime " .
                                   "FROM patient_tracker_element " .
                                   "INNER JOIN list_options " .
                                   "ON patient_tracker_element.status = list_options.option_id " .
                                   "WHERE  list_options.list_id = 'apptstat' " .
                                   "AND list_options.toggle_setting_2 = '1' " .
                                   "AND patient_tracker_element.pt_tracker_id = ?",
                                   array($trackerid));
  if (empty($tracker['start_datetime'])) {
    return false;
  }
  else {
    return $tracker['start_datetime'];
  }
}

function random_drug_test($tracker_id,$percentage,$yearly_limit) {

# Check if randomization has not yet been done (is random_drug_test NULL). If already done, then exit.
      $drug_test_done = sqlQuery("SELECT `random_drug_test`, pid from patient_tracker " .
                                     "WHERE id =? ", array($tracker_id));
      $Patient_id = $drug_test_done['pid'];

  if (is_null($drug_test_done['random_drug_test'])) {
    # get a count of the number of times the patient has been screened.
    if ($yearly_limit >0) {
      # check to see if screens are within the current year.
      $lastyear = date("Y-m-d",strtotime("-1 year", strtotime(date("Y-m-d H:i:s"))));
      $drug_test_count = sqlQuery("SELECT COUNT(*) from patient_tracker " .
                                 "WHERE drug_screen_completed = '1' AND apptdate >= ? AND pid =? ", array($lastyear,$Patient_id));
    }
    # check that the patient is not at the yearly limit.
    if($drug_test_count['COUNT(*)'] >= $yearly_limit && ($yearly_limit >0)) {

       $drugtest = 0;
    }
    else
    {
    # Now do the randomization and set random_drug_test to the outcome.

       $drugtest = 0;
       $testdrug = mt_rand(0,100);
       if ($testdrug <= $percentage) {
         $drugtest = 1;
       }

    }   
   #Update the tracker file.    
   sqlStatement("UPDATE patient_tracker SET " .
                 "random_drug_test = ? " .
                 "WHERE id =? ", array($drugtest,$tracker_id)); 
  }
}
?>
