<?php

 // Copyright (C) 2011 Ken Chapple
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // Holds library functions (and hashes) used by the appointment reporting module

$COMPARE_FUNCTION_HASH = array(
	'doctor' => 'compareAppointmentsByDoctorName',
	'patient' => 'compareAppointmentsByPatientName',
	'pubpid' => 'compareAppointmentsByPatientId',
	'date' => 'compareAppointmentsByDate',
	'time' => 'compareAppointmentsByTime',
	'type' => 'compareAppointmentsByType',
	'comment' => 'compareAppointmentsByComment',
	'status' => 'compareAppointmentsByStatus'
);

$ORDERHASH = array(
  	'doctor' => array( 'doctor', 'date', 'time' ),
  	'patient' => array( 'patient', 'date', 'time' ),
  	'pubpid' => array( 'pubpid', 'date', 'time' ),
  	'date' => array( 'date', 'time', 'type', 'patient' ),
  	'time' => array( 'time', 'date', 'patient' ),
  	'type' => array( 'type', 'date', 'time', 'patient' ),
  	'comment' => array( 'comment', 'date', 'time', 'patient' ),
	'status' => array( 'status', 'date', 'time', 'patient' )
);

function fetchEvents( $from_date, $to_date, $where_param = null, $orderby_param = null ) 
{
	$where =
		"( (e.pc_endDate >= '$from_date' AND e.pc_eventDate <= '$to_date' AND e.pc_recurrtype = '1') OR " .
  		  "(e.pc_eventDate >= '$from_date' AND e.pc_eventDate <= '$to_date') )";
	if ( $where_param ) $where .= $where_param;
	
	$order_by = "e.pc_eventDate, e.pc_startTime";
	if ( $orderby_param ) {
		$order_by = $orderby_param;
	}
	
	$query = "SELECT " .
  	"e.pc_eventDate, e.pc_endDate, e.pc_startTime, e.pc_endTime, e.pc_duration, e.pc_recurrtype, e.pc_recurrspec, e.pc_recurrfreq, e.pc_catid, e.pc_eid, " .
  	"e.pc_title, e.pc_hometext, e.pc_apptstatus, " .
  	"p.fname, p.mname, p.lname, p.pid, p.pubpid, p.phone_home, p.phone_cell, " .
  	"u.fname AS ufname, u.mname AS umname, u.lname AS ulname, u.id AS uprovider_id, " .
  	"c.pc_catname, c.pc_catid " .
  	"FROM openemr_postcalendar_events AS e " .
  	"LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
  	"LEFT OUTER JOIN users AS u ON u.id = e.pc_aid " .
	"LEFT OUTER JOIN openemr_postcalendar_categories AS c ON c.pc_catid = e.pc_catid " .
	"WHERE $where " . 
	"ORDER BY $order_by";

	$res = sqlStatement( $query );
	$events = array();
	if ( $res )
	{
		while ( $row = sqlFetchArray($res) ) 
		{
			// if it's a repeating appointment, fetch all occurances in date range
			if ( $row['pc_recurrtype'] ) {
				$reccuringEvents = getRecurringEvents( $row, $from_date, $to_date );
				$events = array_merge( $events, $reccuringEvents );
			} else {
				$events []= $row;
			}
		}
	}
	
	return $events;
}

function fetchAllEvents( $from_date, $to_date, $provider_id = null, $facility_id = null )
{
	$where = "";
	if ( $provider_id ) $where .= " AND e.pc_aid = '$provider_id'";

	$facility_filter = '';
	if ( $facility_id ) {
		$event_facility_filter = " AND e.pc_facility = '" . add_escape_custom($facility_id) . "'"; //escape $facility_id
		$provider_facility_filter = " AND u.facility_id = '" . add_escape_custom($facility_id) . "'"; //escape $facility_id 
		$facility_filter = $event_facility_filter . $provider_facility_filter;
	}
	
	$where .= $facility_filter;
	$appointments = fetchEvents( $from_date, $to_date, $where );
	return $appointments;
}

function fetchAppointments( $from_date, $to_date, $patient_id = null, $provider_id = null, $facility_id = null, $pc_appstatus = null, $with_out_provider = null, $with_out_facility = null, $pc_catid = null )
{
	$where = "";
	if ( $provider_id ) $where .= " AND e.pc_aid = '$provider_id'";
	if ( $patient_id ) {
		$where .= " AND e.pc_pid = '$patient_id'";
	} else {
		$where .= " AND e.pc_pid != ''";
	}		

	$facility_filter = '';
	if ( $facility_id ) {
		$event_facility_filter = " AND e.pc_facility = '" . add_escape_custom($facility_id) . "'"; // escape $facility_id
		$provider_facility_filter = " AND u.facility_id = '" . add_escape_custom($facility_id) . "'"; // escape $facility_id
		$facility_filter = $event_facility_filter . $provider_facility_filter;
	}
	
	$where .= $facility_filter;
	
	//Appointment Status Checking
	$filter_appstatus = '';
	if($pc_appstatus != ''){
		$filter_appstatus = " AND e.pc_apptstatus = '".$pc_appstatus."'";
	}
	$where .= $filter_appstatus;

        if($pc_catid !=null)
        {
            $where .= " AND e.pc_catid=".intval($pc_catid); // using intval to escape this parameter
        }
        
	//Without Provider checking
	$filter_woprovider = '';
	if($with_out_provider != ''){
		$filter_woprovider = " AND e.pc_aid = ''";
	}
	$where .= $filter_woprovider;
	
	//Without Facility checking
	$filter_wofacility = '';
	if($with_out_facility != ''){
		$filter_wofacility = " AND e.pc_facility = 0";
	}
	$where .= $filter_wofacility;
	
	$appointments = fetchEvents( $from_date, $to_date, $where );
	return $appointments;
}

function getRecurringEvents( $event, $from_date, $to_date )
{
	$repeatEvents = array();
	$from_date_time = strtotime( $from_date . " 00:00:00" );
	$thistime = strtotime( $event['pc_eventDate'] . " 00:00:00" );
	//$thistime = max( $thistime, $from_date_time );
	if ( $event['pc_recurrtype'] )
	{
		preg_match( '/"event_repeat_freq_type";s:1:"(\d)"/', $event['pc_recurrspec'], $matches );
		$repeattype = $matches[1];

		preg_match( '/"event_repeat_freq";s:1:"(\d)"/', $event['pc_recurrspec'], $matches );
		$repeatfreq = $matches[1];
    if ($event['pc_recurrtype'] == 2) {
     // Repeat type is 2 so frequency comes from event_repeat_on_freq.
     preg_match('/"event_repeat_on_freq";s:1:"(\d)"/', $event['pc_recurrspec'], $matches);
     $repeatfreq = $matches[1];
    }
		if ( !$repeatfreq ) $repeatfreq = 1;

    preg_match('/"event_repeat_on_num";s:1:"(\d)"/', $event['pc_recurrspec'], $matches);
    $my_repeat_on_num = $matches[1];

    preg_match('/"event_repeat_on_day";s:1:"(\d)"/', $event['pc_recurrspec'], $matches);
    $my_repeat_on_day = $matches[1];

		$upToDate = strtotime( $to_date." 23:59:59" ); // set the up-to-date to the last second of the "to_date"
		$endtime = strtotime( $event['pc_endDate'] . " 23:59:59" );
		if ( $endtime > $upToDate ) $endtime = $upToDate;

		$repeatix = 0;
		while ( $thistime < $endtime )
		{
			// Skip the event if a repeat frequency > 1 was specified and this is
			// not the desired occurrence.
			if ( !$repeatix ) {
			    $inRange = ( $thistime >= $from_date_time && $thistime < $upToDate );
			    if ( $inRange ) {
    				$newEvent = $event;
    				$eventDate = date( "Y-m-d", $thistime );
    				$newEvent['pc_eventDate'] = $eventDate;
    				$newEvent['pc_endDate'] = $eventDate;
    				$repeatEvents []= $newEvent;
			    }
			}
				
			if  ( ++$repeatix >= $repeatfreq ) $repeatix = 0;

			$adate = getdate($thistime);

      if ($event['pc_recurrtype'] == 2) {
        // Need to skip to nth or last weekday of the next month.
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
      } // end recurrtype 2

      else { // recurrtype 1
			  if ($repeattype == 0)        { // daily
				  $adate['mday'] += 1;
			  } else if ($repeattype == 1) { // weekly
				  $adate['mday'] += 7;
			  } else if ($repeattype == 2) { // monthly
				  $adate['mon'] += 1;
			  } else if ($repeattype == 3) { // yearly
				  $adate['year'] += 1;
			  } else if ($repeattype == 4) { // work days
				  if ($adate['wday'] == 5)      // if friday, skip to monday
				  $adate['mday'] += 3;
				  else if ($adate['wday'] == 6) // saturday should not happen
				  $adate['mday'] += 2;
				  else
				  $adate['mday'] += 1;
			  } else {
				  die("Invalid repeat type '$repeattype'");
			  }
      } // end recurrtype 1

			$thistime = mktime(0, 0, 0, $adate['mon'], $adate['mday'], $adate['year']);
		}
	}

	return $repeatEvents;
}

// get the event slot size in seconds
function getSlotSize()
{
	if ( isset( $GLOBALS['calendar_interval'] ) ) {
		return $GLOBALS['calendar_interval'] * 60;
	}
	return 15 * 60;
}

function getAvailableSlots( $from_date, $to_date, $provider_id = null, $facility_id = null )
{
	$appointments = fetchAllEvents( $from_date, $to_date, $provider_id, $facility_id );
	$appointments = sortAppointments( $appointments, "date" );
	$from_datetime = strtotime( $from_date." 00:00:00" );
	$to_datetime = strtotime( $to_date." 23:59:59" );
	$availableSlots = array();
	$start_time = 0;
	$date = 0;
	for ( $i = 0; $i < count( $appointments ); ++$i )
	{
		if ( $appointments[$i]['pc_catid'] == 2 ) { // 2 == In Office
			$start_time = $appointments[$i]['pc_startTime'];
			$date = $appointments[$i]['pc_eventDate'];
			$provider_id = $appointments[$i]['uprovider_id'];
		} else if ( $appointments[$i]['pc_catid'] == 3 ) { // 3 == Out Of Office
			continue;
		} else {
			$start_time = $appointments[$i]['pc_endTime'];
			$date = $appointments[$i]['pc_eventDate'];
			$provider_id = $appointments[$i]['uprovider_id'];
		}

		// find next appointment with the same provider
		$next_appointment_date = 0;
		$next_appointment_time = 0;
		for ( $j = $i+1; $j < count( $appointments ); ++$j ) {
			if ( $appointments[$j]['uprovider_id'] == $provider_id ) {
				$next_appointment_date = $appointments[$j]['pc_eventDate'];
				$next_appointment_time = $appointments[$j]['pc_startTime'];
				break;
			}
		}

		$same_day = ( strtotime( $next_appointment_date ) == strtotime( $date ) ) ? true : false;

		if ( $next_appointment_time && $same_day ) {
			// check the start time of the next appointment
				
			$start_datetime = strtotime( $date." ".$start_time );
			$next_appointment_datetime = strtotime( $next_appointment_date." ".$next_appointment_time );
			$curr_time = $start_datetime;
			while ( $curr_time < $next_appointment_datetime - (getSlotSize() / 2) ) {
				//create a new appointment ever 15 minutes
				$time = date( "H:i:s", $curr_time );
				$available_slot = createAvailableSlot( 
					$appointments[$i]['pc_eventDate'], 
					$time, 
					$appointments[$i]['ufname'], 
					$appointments[$i]['ulname'], 
					$appointments[$i]['umname'] );
				$availableSlots []= $available_slot;
				$curr_time += getSlotSize(); // add a 15-minute slot
			}
		}
	}

	return $availableSlots;
}

function createAvailableSlot( $event_date, $start_time, $provider_fname, $provider_lname, $provider_mname = "", $cat_name = "Available" )
{
	$newSlot = array();
	$newSlot['ulname'] = $provider_lname;
	$newSlot['ufname'] = $provider_fname;
	$newSlot['umname'] = $provider_mname;
	$newSlot['pc_eventDate'] = $event_date;
	$newSlot['pc_startTime'] = $start_time;
	$newSlot['pc_endTime'] = $start_time;
	$newSlot['pc_catname'] = $cat_name;
	return $newSlot;
}

function getCompareFunction( $code ) {
	global $COMPARE_FUNCTION_HASH;
	return $COMPARE_FUNCTION_HASH[$code];
}

function getComparisonOrder( $code ) {
	global $ORDERHASH;
	return $ORDERHASH[$code];
}

function sortAppointments( array $appointments, $orderBy = 'date' )
{
	global $appointment_sort_order;
	$appointment_sort_order = $orderBy;
	usort( $appointments, "compareAppointments" );
	return $appointments;
}

// cmp_function for usort
// The comparison function must return an integer less than, equal to,
// or greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the second.
function compareAppointments( $appointment1, $appointment2 )
{
	global $appointment_sort_order;
	$comparisonOrder = getComparisonOrder( $appointment_sort_order );
	foreach ( $comparisonOrder as $comparison )
	{
		$cmp_function = getCompareFunction( $comparison );
		$result = $cmp_function( $appointment1, $appointment2 );
		if ( 0 != $result ) {
			return $result;
		}
	}

	return 0;
}

function compareBasic( $e1, $e2 ) 
{
	if ( $e1 < $e2 ) {
		return -1;
	} else if ( $e1 > $e2 ) {
		return 1;
	}
	
	return 0;
}

function compareAppointmentsByDate( $appointment1, $appointment2 )
{
	$date1 = strtotime( $appointment1['pc_eventDate'] );
	$date2 = strtotime( $appointment2['pc_eventDate'] );

	return compareBasic( $date1, $date2 );
}

function compareAppointmentsByTime( $appointment1, $appointment2 )
{
	$time1 = strtotime( $appointment1['pc_startTime'] );
	$time2 = strtotime( $appointment2['pc_startTime'] );

	return compareBasic( $time1, $time2 );
}

function compareAppointmentsByDoctorName( $appointment1, $appointment2 )
{
	$name1 = $appointment1['ulname'];
	$name2 = $appointment2['ulname'];
	$cmp = compareBasic( $name1, $name2 );
	if ( $cmp == 0 ) {
		$name1 = $appointment1['ufname'];
		$name2 = $appointment2['ufname'];
		return compareBasic( $name1, $name2 );
	}

	return $cmp;
}

function compareAppointmentsByPatientName( $appointment1, $appointment2 )
{
	$name1 = $appointment1['lname'];
	$name2 = $appointment2['lname'];
	$cmp = compareBasic( $name1, $name2 );
	if ( $cmp == 0 ) {
		$name1 = $appointment1['fname'];
		$name2 = $appointment2['fname'];
		return compareBasic( $name1, $name2 );
	}

	return $cmp;
}

function compareAppointmentsByType( $appointment1, $appointment2 )
{
	$type1 = $appointment1['pc_catid'];
	$type2 = $appointment2['pc_catid'];
	return compareBasic( $type1, $type2 );
}

function compareAppointmentsByPatientId( $appointment1, $appointment2 )
{
	$id1 = $appointment1['pubpid'];
	$id2 = $appointment2['pubpid'];
	return compareBasic( $id1, $id2 );
}

function compareAppointmentsByComment( $appointment1, $appointment2 )
{
	$comment1 = $appointment1['pc_hometext'];
	$comment2 = $appointment2['pc_hometext'];
	return compareBasic( $comment1, $comment2 );
}

function compareAppointmentsByStatus( $appointment1, $appointment2 )
{
	$status1 = $appointment1['pc_apptstatus'];
	$status2 = $appointment2['pc_apptstatus'];
	return compareBasic( $status1, $status2 );
}

function fetchAppointmentCategories()
{
     $catSQL= " SELECT pc_catid as id, pc_catname as category " 
            . " FROM openemr_postcalendar_categories WHERE pc_recurrtype=0 and pc_cattype=0 ORDER BY category";    
     return sqlStatement($catSQL);
}
?>
