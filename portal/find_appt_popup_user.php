<?php
/**
 *
 * Modified from main codebase for the patient portal.
 *
 * Copyright (C) 2005-2006, 2013 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
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
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

// Note from Rod 2013-01-22:
// This module needs to be refactored to share the same code that is in
// interface/main/calendar/find_appt_popup.php.  It contains an old version
// of that logic and does not support exception dates for repeating events.

//continue session
session_start();
//

//landing page definition -- where to go if something goes wrong
$landingpage = "index.php?site=".$_SESSION['site_id'];
//

// kick out if patient not authenticated
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
} else {
    session_destroy();
    header('Location: '.$landingpage.'&w');
    exit();
}

//

$ignoreAuth = 1;

 include_once("../interface/globals.php");
 include_once("$srcdir/patient.inc");

 $input_catid = $_REQUEST['catid'];

 // Record an event into the slots array for a specified day.
function doOneDay($catid, $udate, $starttime, $duration, $prefcatid)
{
    global $slots, $slotsecs, $slotstime, $slotbase, $slotcount, $input_catid;
    $udate = strtotime($starttime, $udate);
    if ($udate < $slotstime) {
        return;
    }

    $i = (int) ($udate / $slotsecs) - $slotbase;
    $iend = (int) (($duration + $slotsecs - 1) / $slotsecs) + $i;
    if ($iend > $slotcount) {
        $iend = $slotcount;
    }

    if ($iend <= $i) {
        $iend = $i + 1;
    }

    for (; $i < $iend; ++$i) {
        if ($catid == 2) {        // in office
            // If a category ID was specified when this popup was invoked, then select
            // only IN events with a matching preferred category or with no preferred
            // category; other IN events are to be treated as OUT events.
            if ($input_catid) {
                if ($prefcatid == $input_catid || !$prefcatid) {
                    $slots[$i] |= 1;
                } else {
                    $slots[$i] |= 2;
                }
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
    if ($srow['pc_duration']) {
        $catslots = ceil($srow['pc_duration'] / $slotsecs);
    }
}

 $info_msg = "";

 $searchdays = 7; // default to a 1-week lookahead
if ($_REQUEST['searchdays']) {
    $searchdays = $_REQUEST['searchdays'];
}

 // Get a start date.
if ($_REQUEST['startdate'] && preg_match(
    "/(\d\d\d\d)\D*(\d\d)\D*(\d\d)/",
    $_REQUEST['startdate'],
    $matches
)) {
    $sdate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
} else {
    $sdate = date("Y-m-d");
}

    // Get an end date - actually the date after the end date.
    preg_match("/(\d\d\d\d)\D*(\d\d)\D*(\d\d)/", $sdate, $matches);
    $edate = date(
        "Y-m-d",
        mktime(0, 0, 0, $matches[2], $matches[3] + $searchdays, $matches[1])
    );

    // compute starting time slot number and number of slots.
    $slotstime = strtotime("$sdate 00:00:00");
    $slotetime = strtotime("$edate 00:00:00");
    $slotbase  = (int) ($slotstime / $slotsecs);
    $slotcount = (int) ($slotetime / $slotsecs) - $slotbase;

    if ($slotcount <= 0 || $slotcount > 100000) {
        die("Invalid date range.");
    }

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
    //  echo $sdate." -- ".$edate;
        $query = "SELECT pc_eventDate, pc_endDate, pc_startTime, pc_duration, " .
        "pc_recurrtype, pc_recurrspec, pc_alldayevent, pc_catid, pc_prefcatid, pc_title " .
        "FROM openemr_postcalendar_events " .
        "WHERE pc_aid = '$providerid' AND " .
        "((pc_endDate >= '$sdate' AND pc_eventDate < '$edate') OR " .
        "(pc_endDate = '0000-00-00' AND pc_eventDate >= '$sdate' AND pc_eventDate < '$edate'))";
        $res = sqlStatement($query);
   //  print_r($res);

        while ($row = sqlFetchArray($res)) {
            $thistime = strtotime($row['pc_eventDate'] . " 00:00:00");
            if ($row['pc_recurrtype']) {
                preg_match('/"event_repeat_freq_type";s:1:"(\d)"/', $row['pc_recurrspec'], $matches);
                $repeattype = $matches[1];

                preg_match('/"event_repeat_freq";s:1:"(\d)"/', $row['pc_recurrspec'], $matches);
                $repeatfreq = $matches[1];
                if ($row['pc_recurrtype'] == 2) {
                    // Repeat type is 2 so frequency comes from event_repeat_on_freq.
                    preg_match('/"event_repeat_on_freq";s:1:"(\d)"/', $row['pc_recurrspec'], $matches);
                    $repeatfreq = $matches[1];
                }

                if (! $repeatfreq) {
                    $repeatfreq = 1;
                }

                preg_match('/"event_repeat_on_num";s:1:"(\d)"/', $row['pc_recurrspec'], $matches);
                $my_repeat_on_num = $matches[1];

                preg_match('/"event_repeat_on_day";s:1:"(\d)"/', $row['pc_recurrspec'], $matches);
                $my_repeat_on_day = $matches[1];

                $endtime = strtotime($row['pc_endDate'] . " 00:00:00") + (24 * 60 * 60);
                if ($endtime > $slotetime) {
                    $endtime = $slotetime;
                }

                $repeatix = 0;
                while ($thistime < $endtime) {
                    // Skip the event if a repeat frequency > 1 was specified and this is
                    // not the desired occurrence.
                    if (! $repeatix) {
                        doOneDay(
                            $row['pc_catid'],
                            $thistime,
                            $row['pc_startTime'],
                            $row['pc_duration'],
                            $row['pc_prefcatid']
                        );
                    }

                    if (++$repeatix >= $repeatfreq) {
                        $repeatix = 0;
                    }

                    $adate = getdate($thistime);

                    if ($row['pc_recurrtype'] == 2) {
                        // Need to skip to nth or last weekday of the next month.
                        $adate['mon'] += 1;
                        if ($adate['mon'] > 12) {
                            $adate['year'] += 1;
                            $adate['mon'] -= 12;
                        }

                        if ($my_repeat_on_num < 5) { // not last
                            $adate['mday'] = 1;
                            $dow = jddayofweek(cal_to_jd(CAL_GREGORIAN, $adate['mon'], $adate['mday'], $adate['year']));
                            if ($dow > $my_repeat_on_day) {
                                $dow -= 7;
                            }

                            $adate['mday'] += ($my_repeat_on_num - 1) * 7 + $my_repeat_on_day - $dow;
                        } else { // last weekday of month
                            $adate['mday'] = cal_days_in_month(CAL_GREGORIAN, $adate['mon'], $adate['year']);
                            $dow = jddayofweek(cal_to_jd(CAL_GREGORIAN, $adate['mon'], $adate['mday'], $adate['year']));
                            if ($dow < $my_repeat_on_day) {
                                $dow += 7;
                            }

                            $adate['mday'] += $my_repeat_on_day - $dow;
                        }
                    } // end recurrtype 2

                    else { // recurrtype 1

                        if ($repeattype == 0) { // daily
                            $adate['mday'] += 1;
                        } else if ($repeattype == 1) { // weekly
                            $adate['mday'] += 7;
                        } else if ($repeattype == 2) { // monthly
                            $adate['mon'] += 1;
                        } else if ($repeattype == 3) { // yearly
                            $adate['year'] += 1;
                        } else if ($repeattype == 4) { // work days
                            if ($adate['wday'] == 5) {      // if friday, skip to monday
                                $adate['mday'] += 3;
                            } else if ($adate['wday'] == 6) { // saturday should not happen
                                $adate['mday'] += 2;
                            } else {
                                $adate['mday'] += 1;
                            }
                        } else if ($repeattype == 5) { // monday
                            $adate['mday'] += 7;
                        } else if ($repeattype == 6) { // tuesday
                            $adate['mday'] += 7;
                        } else if ($repeattype == 7) { // wednesday
                            $adate['mday'] += 7;
                        } else if ($repeattype == 8) { // thursday
                            $adate['mday'] += 7;
                        } else if ($repeattype == 9) { // friday
                            $adate['mday'] += 7;
                        } else {
                             die("Invalid repeat type '$repeattype'");
                        }
                    } // end recurrtype 1

                    $thistime = mktime(0, 0, 0, $adate['mon'], $adate['mday'], $adate['year']);
                }
            } else {
                doOneDay(
                    $row['pc_catid'],
                    $thistime,
                    $row['pc_startTime'],
                    $row['pc_duration'],
                    $row['pc_prefcatid']
                );
            }
        }

     // Mark all slots reserved where the provider is not in-office.
     // Actually we could do this in the display loop instead.
        $inoffice = false;
        for ($i = 0; $i < $slotcount; ++$i) {
            if (($i % $slotsperday) == 0) {
                $inoffice = false;
            }

            if ($slots[$i] & 1) {
                $inoffice = true;
            }

            if ($slots[$i] & 2) {
                $inoffice = false;
            }

            if (! $inoffice) {
                $slots[$i] |= 4;
            }
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<title><?php xl('Find Available Appointments', 'e'); ?></title>
<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<?php if ($_SESSION['language_direction'] == 'rtl') { ?>
    <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />
<?php } ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">
<!-- for the pop up calendar -->
<script src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

<script>

function setappt(year,mon,mday,hours,minutes) {
    opener.setappt(year,mon,mday,hours,minutes);
    dlgclose();
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
         background-color: #bfe6ff4d;
         font-weight: bold;
         padding: 3px;
     }

     #searchResults {
         width: 100%;
         height: 100%;
         overflow: auto;
     }

     .srDate {
         background-color: #bfe6ff4d;
     }

     #searchResults table {
         width: 100%;
         border-collapse: collapse;
         background-color: white;
     }

     #searchResults td {
         border-bottom: 1px solid gray;
         padding: 1px 5px 1px 5px;
     }

     .highlight {
         background-color: #ffff994d;
     }

     .blue_highlight {
         background-color: #BBCCDD;
         color: white;
     }

     #am {
         border-bottom: 1px solid lightgrey;
         color: #00c;
     }

     #pm {
         color: #c00;
     }

     #pm a {
         color: #c00;
     }
 </style>

</head>

<body class="body_top">

<div id="searchCriteria">
<form method='post' name='theform' action='./find_appt_popup_user.php?providerid=<?php echo $providerid ?>&catid=<?php echo $input_catid ?>'>
   <input type="hidden" name='bypatient' />

    <?php xl('Start date:', 'e'); ?>

   <input type='text' class='datepicker' name='startdate' id='startdate' size='10' value='<?php echo $sdate ?>'
    title='yyyy-mm-dd starting date for search'/>

    <?php xl('for', 'e'); ?>
   <input type='text' name='searchdays' size='3' value='<?php echo $searchdays ?>'
    title='Number of days to search from the start date' />
    <?php xl('days', 'e'); ?>&nbsp;
   <input type='submit' value='<?php xl('Search', 'e'); ?>'>
</div>

<?php if (!empty($slots)) : ?>

<div id="searchResultsHeader">
<table class='table table-bordered'>

</table>
</div>

<div id="searchResults" class="container">
<table class='table table-inversed table-bordered'>
    <thead id="searchResultsHeader">
    <tr>
        <th class="srDate"><?php xl('Day', 'e'); ?></th>
        <th class="srTimes"><?php xl('Available Times', 'e'); ?></th>
    </tr>
    </thead>
<?php
    $lastdate = "";
    $ampmFlag = "am"; // establish an AM-PM line break flag
for ($i = 0; $i < $slotcount; ++$i) {
    $available = true;
    for ($j = $i; $j < $i + $catslots; ++$j) {
        if ($slots[$j] >= 4) {
            $available = false;
        }
    }

    if (!$available) {
        continue; // skip reserved slots
    }

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
    if ($ampmFlag != $ampm) {
        echo "</div><div id='pm'>PM ";
    }

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
    echo (strlen(date('g', $utime)) < 2 ? "<span style='visibility:hidden'>0</span>" : "") .
    $anchor . date("g:i", $utime) . "</a> ";

    // If category duration is more than 1 slot, increment $i appropriately.
    // This is to avoid reporting available times on undesirable boundaries.
    $i += $catslots - 1;
}

if ($lastdate) {
    echo "</td>\n";
    echo " </tr>\n";
} else {
    echo " <tr><td colspan='2'> " . xl('No openings were found for this period.', 'e') . "</td></tr>\n";
}
?>
</table>
</div>
</div>
<?php endif; ?>

</form>
</body>

<script language='JavaScript'>

// jQuery stuff to make the page a little easier to use
$(document).ready(function(){
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".oneresult a").mouseover(function () { $(this).toggleClass("blue_highlight"); $(this).children().toggleClass("blue_highlight"); });
    $(".oneresult a").mouseout(function() { $(this).toggleClass("blue_highlight"); $(this).children().toggleClass("blue_highlight"); });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });

});

</script>

</html>
