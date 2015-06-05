<?php
// Copyright (C) 2010-2014 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

function oeFormatMoney($amount, $symbol=false) {
  $s = number_format($amount,
    $GLOBALS['currency_decimals'],
    $GLOBALS['currency_dec_point'],
    $GLOBALS['currency_thousands_sep']);
  // If the currency symbol exists and is requested, prepend it.
  if ($symbol && !empty($GLOBALS['gbl_currency_symbol']))
    $s = $GLOBALS['gbl_currency_symbol'] . " $s";
  return $s;
}

function oeFormatShortDate($date='today') {
  if ($date === 'today') $date = date('Y-m-d');
  if (strlen($date) == 10) {
    // assume input is yyyy-mm-dd
    if ($GLOBALS['date_display_format'] == 1)      // mm/dd/yyyy
      $date = substr($date, 5, 2) . '/' . substr($date, 8, 2) . '/' . substr($date, 0, 4);
    else if ($GLOBALS['date_display_format'] == 2) // dd/mm/yyyy
      $date = substr($date, 8, 2) . '/' . substr($date, 5, 2) . '/' . substr($date, 0, 4);
  }
  return $date;
}

// 0 - Time format 24 hr
// 1 - Time format 12 hr
function oeFormatTime( $time, $format = "" ) 
{
	$formatted = $time;
	if ( $format == "" ) {
		$format = $GLOBALS['time_display_format'];
	}
	
	if ( $format == 0 ) {
		$formatted = date( "H:i", strtotime( $time ) );	
	} else if ( $format == 1 ) {
		$formatted = date( "g:i a", strtotime( $time ) );		
	}
	
	return $formatted;
}

// Format short date from time.
function oeFormatSDFT($time) {
  return oeFormatShortDate(date('Y-m-d', $time));
}

// Format the body of a patient note.
function oeFormatPatientNote($note) {
  $i = 0;
  while ($i !== false) {
    if (preg_match('/^\d\d\d\d-\d\d-\d\d/', substr($note, $i))) {
      $note = substr($note, 0, $i) . oeFormatShortDate(substr($note, $i, 10)) . substr($note, $i + 10);
    }
    $i = strpos("\n", $note, $i);
    if ($i !== false) ++$i;
  }
  return $note;
}

function oeFormatClientID($id) {

  // TBD

  return $id;
}
//----------------------------------------------------
function DateFormatRead()
 {//For the 3 supported date format,the javascript code also should be twicked to display the date as per it.
  //Output of this function is given to 'ifFormat' parameter of the 'Calendar.setup'.
  //This will show the date as per the global settings.
	if($GLOBALS['date_display_format']==0)
	 {
	  return "%Y-%m-%d";
	 }
	else if($GLOBALS['date_display_format']==1)
	 {
	  return "%m/%d/%Y";
	 }
	else if($GLOBALS['date_display_format']==2)
	 {
	  return "%d/%m/%Y";
	 }
 }
function DateToYYYYMMDD($DateValue)
 {//With the help of function DateFormatRead() now the user can enter date is any of the 3 formats depending upon the global setting.
 //But in database the date can be stored only in the yyyy-mm-dd format.
 //This function accepts a date in any of the 3 formats, and as per the global setting, converts it to the yyyy-mm-dd format.
	if(trim($DateValue)=='')
	 {
	  return '';
	 }
	 
	if($GLOBALS['date_display_format']==0)
	 {
	  return $DateValue;
	 }
	else if($GLOBALS['date_display_format']==1 || $GLOBALS['date_display_format']==2)
	 {
	  $DateValueArray=split('/',$DateValue);
	  if($GLOBALS['date_display_format']==1)
	   {
		  return $DateValueArray[2].'-'.$DateValueArray[0].'-'.$DateValueArray[1];
	   }
	  if($GLOBALS['date_display_format']==2)
	   {
		  return $DateValueArray[2].'-'.$DateValueArray[1].'-'.$DateValueArray[0];
	   }
	 }
 }

// Returns age in a desired format:
//   0 = "xx month(s)" if < 2 years, else years
//   1 = Years      : just a number
//   2 = Months     : just a number
//   3 = Gestational: "xx week(s) y day(s)"
// $dobYMD is YYYYMMDD or YYYY-MM-DD
// $nowYMD is same format but optional
//
function oeFormatAge($dobYMD, $nowYMD='', $format=0) {
  // Strip any dashes from the dates.
  $dobYMD = preg_replace('/-/', '', $dobYMD);
  $nowYMD = preg_replace('/-/', '', $nowYMD);
  $dobDay   = substr($dobYMD,6,2);
  $dobMonth = substr($dobYMD,4,2);
  $dobYear  = substr($dobYMD,0,4);

  if ($nowYMD) {
    $nowDay   = substr($nowYMD,6,2);
    $nowMonth = substr($nowYMD,4,2);
    $nowYear  = substr($nowYMD,0,4);
  }
  else {
    $nowDay   = date("d");
    $nowMonth = date("m");
    $nowYear  = date("Y");
  }

  if ($format == 3) {
    // Gestational age as weeks and days.
    $secs = mktime(0, 0, 0, $nowMonth, $nowDay, $nowYear) -
            mktime(0, 0, 0, $dobMonth, $dobDay, $dobYear);
    $days  = intval($secs / (24 * 60 * 60));
    $weeks = intval($days / 7);
    $days  = $days % 7;
    $age   = "$weeks " . ($weeks == 1 ? xl('week') : xl('weeks')) .
             " $days " . ($days  == 1 ? xl('day' ) : xl('days' ));
  }
  else {
    // Years or months.
    $dayDiff   = $nowDay   - $dobDay;
    $monthDiff = $nowMonth - $dobMonth;
    $yearDiff  = $nowYear  - $dobYear;
    $ageInMonths = $yearDiff * 12 + $monthDiff;
    if ($dayDiff < 0) --$ageInMonths;
    if ($format == 1 || ($format == 0 && $ageInMonths >= 24)) {
      $age = $yearDiff;
      if ($monthDiff < 0 || ($monthDiff == 0 && $dayDiff < 0)) --$age;
    }
    else {
      $age = $ageInMonths;
      if ($format == 0) {
        $age .= ' ' . $ageInMonths == 1 ? xl('month') : xl('months'); 
      }
    }
  }

  return $age;
}
?>
