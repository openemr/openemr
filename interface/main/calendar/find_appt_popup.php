<?php
 // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");

 $input_catid = $_REQUEST['catid'];

 // Record an event into the slots array for a specified day.
 function doOneDay($catid, $udate, $starttime, $duration, $prefcatid) {
  global $slots, $slotsecs, $slotstime, $slotbase, $slotcount, $input_catid;
  $udate = strtotime($starttime, $udate);
  if ($udate < $slotstime) return;
  $i = (int) ($udate / $slotsecs) - $slotbase;
  $iend = (int) (($duration + $slotsecs - 1) / $slotsecs) + $i;
  if ($iend > $slotcount) $iend = $slotcount;
  if ($iend <= $i) $iend = $i + 1;
  for (; $i < $iend; ++$i) {
   if ($catid == 2) {        // in office
    // If a category ID was specified when this popup was invoked, then select
    // only IN events with a matching preferred category or with no preferred
    // category; other IN events are to be treated as OUT events.
    if ($input_catid) {
     if ($prefcatid == $input_catid || !$prefcatid)
      $slots[$i] |= 1;
     else
      $slots[$i] |= 2;
    } else {
     $slots[$i] |= 1;
    }
    break; // ignore any positive duration for IN
   } else if ($catid == 3) { // out of office
    $slots[$i] |= 2;
    break; // ignore any positive duration for OUT
   } else { // all other events reserve time
    $slots[$i] |= 4;
   }
  }
 }

 // seconds per time slot
 $slotsecs = $GLOBALS['calendar_interval'] * 60;

 $catslots = 1;
 if ($input_catid) {
  $srow = sqlQuery("SELECT pc_duration FROM openemr_postcalendar_categories WHERE pc_catid = '$input_catid'");
  if ($srow['pc_duration']) $catslots = ceil($srow['pc_duration'] / $slotsecs);
 }

 $info_msg = "";

 $searchdays = 7; // default to a 1-week lookahead
 if ($_REQUEST['searchdays']) $searchdays = $_REQUEST['searchdays'];

 // Get a start date.
 if ($_REQUEST['startdate'] && preg_match("/(\d\d\d\d)\D*(\d\d)\D*(\d\d)/",
     $_REQUEST['startdate'], $matches))
 {
  $sdate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
 } else {
  $sdate = date("Y-m-d");
 }

 // Get an end date - actually the date after the end date.
 preg_match("/(\d\d\d\d)\D*(\d\d)\D*(\d\d)/", $sdate, $matches);
 $edate = date("Y-m-d",
  mktime(0, 0, 0, $matches[2], $matches[3] + $searchdays, $matches[1]));

 // compute starting time slot number and number of slots.
 $slotstime = strtotime("$sdate 00:00:00");
 $slotetime = strtotime("$edate 00:00:00");
 $slotbase  = (int) ($slotstime / $slotsecs);
 $slotcount = (int) ($slotetime / $slotsecs) - $slotbase;

 if ($slotcount <= 0 || $slotcount > 100000) die("Invalid date range.");

 $slotsperday = (int) (60 * 60 * 24 / $slotsecs);

 // If we have a provider, search.
 //
 if ($_REQUEST['providerid']) {
  $providerid = $_REQUEST['providerid'];

  // Create and initialize the slot array. Values are bit-mapped:
  //   bit 0 = in-office occurs here
  //   bit 1 = out-of-office occurs here
  //   bit 2 = reserved
  // So, values may range from 0 to 7.
  //
  $slots = array_pad(array(), $slotcount, 0);

  // Note there is no need to sort the query results.
  $query = "SELECT pc_eventDate, pc_endDate, pc_startTime, pc_duration, " .
   "pc_recurrtype, pc_recurrspec, pc_alldayevent, pc_catid, pc_prefcatid " .
   "FROM openemr_postcalendar_events " .
   "WHERE pc_aid = '$providerid' AND " .
   "((pc_endDate >= '$sdate' AND pc_eventDate < '$edate') OR " .
   "(pc_endDate = '0000-00-00' AND pc_eventDate >= '$sdate' AND pc_eventDate < '$edate'))";
  $res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {
   $thistime = strtotime($row['pc_eventDate'] . " 00:00:00");
   if ($row['pc_recurrtype']) {

    preg_match('/"event_repeat_freq_type";s:1:"(\d)"/', $row['pc_recurrspec'], $matches);
    $repeattype = $matches[1];

    preg_match('/"event_repeat_freq";s:1:"(\d)"/', $row['pc_recurrspec'], $matches);
    $repeatfreq = $matches[1];
    if (! $repeatfreq) $repeatfreq = 1;

    $endtime = strtotime($row['pc_endDate'] . " 00:00:00") + (24 * 60 * 60);
    if ($endtime > $slotetime) $endtime = $slotetime;

    $repeatix = 0;
    while ($thistime < $endtime) {

     // Skip the event if a repeat frequency > 1 was specified and this is
     // not the desired occurrence.
     if (! $repeatix) {
      doOneDay($row['pc_catid'], $thistime, $row['pc_startTime'],
       $row['pc_duration'], $row['pc_prefcatid']);
     }
     if (++$repeatix >= $repeatfreq) $repeatix = 0;

     $adate = getdate($thistime);
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
     $thistime = mktime(0, 0, 0, $adate['mon'], $adate['mday'], $adate['year']);
    }
   } else {
    doOneDay($row['pc_catid'], $thistime, $row['pc_startTime'],
     $row['pc_duration'], $row['pc_prefcatid']);
   }
  }

  // Mark all slots reserved where the provider is not in-office.
  // Actually we could do this in the display loop instead.
  $inoffice = false;
  for ($i = 0; $i < $slotcount; ++$i) {
   if (($i % $slotsperday) == 0) $inoffice = false;
   if ($slots[$i] & 1) $inoffice = true;
   if ($slots[$i] & 2) $inoffice = false;
   if (! $inoffice) $slots[$i] |= 4;
  }
 }
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php xl('Find Available Appointments','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<!-- for the pop up calendar -->
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>

<!-- for ajax-y stuff -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

<script language="JavaScript">

 function setappt(year,mon,mday,hours,minutes) {
  if (opener.closed || ! opener.setappt)
   alert('<?php xl('The destination form was closed; I cannot act on your selection.','e'); ?>');
  else
   opener.setappt(year,mon,mday,hours,minutes);
  window.close();
  return false;
 }

</script>


<style>
form {
    /* this eliminates the padding normally around a FORM tag */
    padding: 0px;
    margin: 0px;
}
#searchCriteria {
    text-align: center;
    width: 100%;
    font-size: 0.8em;
    background-color: #ddddff;
    font-weight: bold;
    padding: 3px;
}
#searchResultsHeader { 
    width: 100%;
    background-color: lightgrey;
}
#searchResultsHeader table { 
    width: 96%;  /* not 100% because the 'searchResults' table has a scrollbar */
    border-collapse: collapse;
}
#searchResultsHeader th {
    font-size: 0.7em;
}
#searchResults {
    width: 100%;
    height: 350px; 
    overflow: auto;
}

.srDate { width: 20%; }
.srTimes { width: 80%; }

#searchResults table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
}
#searchResults td {
    font-size: 0.7em;
    border-bottom: 1px solid gray;
    padding: 1px 5px 1px 5px;
}
.highlight { background-color: #ff9; }
.blue_highlight { background-color: #336699; color: white; }
#am {
    border-bottom: 1px solid lightgrey;
    color: #00c;
}
#pm { color: #c00; }
#pm a { color: #c00; }
</style>

</head>

<body class="body_top">

<div id="searchCriteria">
<form method='post' name='theform' action='find_appt_popup.php?providerid=<?php echo $providerid ?>&catid=<?php echo $input_catid ?>'>
   <?php xl('Start date:','e'); ?>
   <input type='text' name='startdate' size='10' value='<?php echo $sdate ?>'
    title='yyyy-mm-dd starting date for search' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   <?php xl('for','e'); ?>
   <input type='text' name='searchdays' size='3' value='<?php echo $searchdays ?>'
    title='Number of days to search from the start date' />
   <?php xl('days','e'); ?>&nbsp;
   <input type='submit' value='<?php xl('Search','e'); ?>'>
</div>

<?php if (!empty($slots)) : ?>

<div id="searchResultsHeader">
<table>
 <tr>
  <th class="srDate"><?php xl ('Day','e'); ?></th>
  <th class="srTimes"><?php xl ('Available Times','e'); ?></th>
 </tr>
</table>
</div>

<div id="searchResults">
<table> 
<?php
    $lastdate = "";
    $ampmFlag = "am"; // establish an AM-PM line break flag
    for ($i = 0; $i < $slotcount; ++$i) {

        $available = true;
        for ($j = $i; $j < $i + $catslots; ++$j) {
            if ($slots[$j] >= 4) $available = false;
        }
        if (!$available) continue; // skip reserved slots

        $utime = ($slotbase + $i) * $slotsecs;
        $thisdate = date("Y-m-d", $utime);
        if ($thisdate != $lastdate) { 
            // if a new day, start a new row
            if ($lastdate) {
                echo "</div>";
                echo "</td>\n";
                echo " </tr>\n";
            }
            $lastdate = $thisdate;
            echo " <tr class='oneresult'>\n";
            echo "  <td class='srDate'>" . date("l", $utime)."<br>".date("Y-m-d", $utime) . "</td>\n";
            echo "  <td class='srTimes'>";
            echo "<div id='am'>AM ";
            $ampmFlag = "am";  // reset the AMPM flag
        }
        
        $ampm = date('a', $utime);
        if ($ampmFlag != $ampm) { echo "</div><div id='pm'>PM "; }
        $ampmFlag = $ampm;

        $atitle = "Choose ".date("h:i a", $utime);
        $adate = getdate($utime);
        $anchor = "<a href='' onclick='return setappt(" .
            $adate['year'] . "," .
            $adate['mon'] . "," .
            $adate['mday'] . "," .
            $adate['hours'] . "," .
            $adate['minutes'] . ")'".
            " title='$atitle' alt='$atitle'".
            ">";
        echo (strlen(date('g',$utime)) < 2 ? "<span style='visibility:hidden'>0</span>" : "") .
            $anchor . date("g:i", $utime) . "</a> ";

        // If category duration is more than 1 slot, increment $i appropriately.
        // This is to avoid reporting available times on undesirable boundaries.
        $i += $catslots - 1;
    }
    if ($lastdate) {
        echo "</td>\n";
        echo " </tr>\n";
    } else {
        echo " <tr><td colspan='2'> " . xl('No openings were found for this period.','e') . "</td></tr>\n";
    }
?>
</table>
</div>
</div>
<?php endif; ?>

</form>
</body>

<!-- for the pop up calendar -->
<script language='JavaScript'>
 Calendar.setup({inputField:"startdate", ifFormat:"%Y-%m-%d", button:"img_date"});

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".oneresult a").mouseover(function () { $(this).toggleClass("blue_highlight"); $(this).children().toggleClass("blue_highlight"); });
    $(".oneresult a").mouseout(function() { $(this).toggleClass("blue_highlight"); $(this).children().toggleClass("blue_highlight"); });
    //$(".event").dblclick(function() { EditEvent(this); });
});

</script>

</html>
