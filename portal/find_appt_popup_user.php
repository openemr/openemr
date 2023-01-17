<?php

/**
 *
 * Modified from main codebase for the patient portal.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ian Jardine ( github.com/epsdky )
 * @copyright Copyright (C) 2005-2013 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2019 Ian Jardine ( github.com/epsdky )
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Note from Rod 2013-01-22:
// This module needs to be refactored to share the same code that is in
// interface/main/calendar/find_appt_popup.php.  It contains an old version
// of that logic and does not support exception dates for repeating events.

// Rod mentioned in the previous comment that the code "does not support exception dates for repeating events".
// This issue no longer exists - epsdky 2019

//continue session
// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();
//

//landing page definition -- where to go if something goes wrong
$landingpage = "index.php?site=" . urlencode($_SESSION['site_id']);
//

// kick out if patient not authenticated
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}

//

$ignoreAuth_onsite_portal = true;

require_once("../interface/globals.php");
require_once("$srcdir/patient.inc.php");
require_once(dirname(__FILE__) . "/../library/appointments.inc.php");

use OpenEMR\Core\Header;

$input_catid = $_REQUEST['catid'];

// Record an event into the slots array for a specified day.
function doOneDay($catid, $udate, $starttime, $duration, $prefcatid)
{
    global $slots, $slotsecs, $slotstime, $slotbase, $slotcount, $input_catid;
    $udate = strtotime($starttime, $udate);
    if ($udate < $slotstime) {
        return;
    }

    $i = (int)($udate / $slotsecs) - $slotbase;
    $iend = (int)(($duration + $slotsecs - 1) / $slotsecs) + $i;
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
                if (!empty($slots[$i])) {
                    if ($prefcatid == $input_catid || !$prefcatid) {
                        $slots[$i] |= 1;
                    } else {
                        $slots[$i] |= 2;
                    }
                } else {
                    $slots[$i] |= 1;
                }
            } else {
                $slots[$i] |= 1;
            }

            break; // ignore any positive duration for IN
        } elseif ($catid == 3) { // out of office
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
    $srow = sqlQuery("SELECT pc_duration FROM openemr_postcalendar_categories WHERE pc_catid = ?", array($input_catid));
    if ($srow['pc_duration']) {
        $catslots = ceil($srow['pc_duration'] / $slotsecs);
    }
}

$info_msg = "";

$searchdays = 7; // default to a 1-week lookahead
if ($_REQUEST['searchdays'] ?? null) {
    $searchdays = $_REQUEST['searchdays'];
}

// Get a start date.
if (
    $_REQUEST['startdate'] && preg_match(
        "/(\d\d\d\d)\D*(\d\d)\D*(\d\d)/",
        $_REQUEST['startdate'],
        $matches
    )
) {
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
$slotbase = (int)($slotstime / $slotsecs);
$slotcount = (int)($slotetime / $slotsecs) - $slotbase;

if ($slotcount <= 0 || $slotcount > 100000) {
    die("Invalid date range.");
}

$slotsperday = (int)(60 * 60 * 24 / $slotsecs);

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
        "WHERE pc_aid = ? AND " .
        "((pc_endDate >= ? AND pc_eventDate < ?) OR " .
        "(pc_endDate = '0000-00-00' AND pc_eventDate >= ? AND pc_eventDate < ?))";

    $sqlBindArray = array();
    array_push($sqlBindArray, $providerid, $sdate, $edate, $sdate, $edate);
    //////
    $events2 = fetchEvents($sdate, $edate, null, null, false, 0, $sqlBindArray, $query);
    foreach ($events2 as $row) {
        $thistime = strtotime($row['pc_eventDate'] . " 00:00:00");
        doOneDay(
            $row['pc_catid'],
            $thistime,
            $row['pc_startTime'],
            $row['pc_duration'],
            $row['pc_prefcatid']
        );
    }
    //////

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

        if (!$inoffice) {
            $slots[$i] |= 4;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Find Available Appointments'); ?></title>
    <?php Header::setupHeader(['no_main-theme', 'datetime-picker', 'opener']); ?>
    <script>

        function setappt(year, mon, mday, hours, minutes) {
            opener.setappt(year, mon, mday, hours, minutes);
            dlgclose();
            return false;
        }

    </script>

    <style>
      form {
        /* this eliminates the padding normally around a FORM tag */
        padding: 0;
        margin: 0;
      }

      #searchCriteria {
        text-align: center;
        width: 100%;
        font-weight: bold;
        padding: 0.1875rem;
      }

      #searchResults {
        width: 100%;
        height: 100%;
        overflow: auto;
      }

      #searchResults table {
        width: 100%;
        border-collapse: collapse;
        background-color: var(--white);
      }

      #searchResults td {
        border-bottom: 1px solid var(--gray600);
        padding: 1px 5px;
      }

      .blue_highlight {
        background-color: #BBCCDD;
        color: var(--white);
      }

      #am a, #am a:hover {
        padding: 4px;
        text-decoration: none;
      }

      #pm a, #pm a:hover {
        color: var(--danger);
        padding: 4px;
        text-decoration: none;
      }
    </style>

</head>

<body class="body_top">

    <div class="table-primary" id="searchCriteria">
        <form method='post' name='theform' action='./find_appt_popup_user.php?providerid=<?php echo attr_url($providerid); ?>&catid=<?php echo attr_url($input_catid); ?>'>
            <input type="hidden" name='bypatient' />
            <div class="form-row mx-0 align-items-center">
                <label for="startdate" class="col-1 mx-2 col-form-label"><?php echo xlt('Start date:'); ?></label>
                <div class="col-auto">
                    <input type='text' class='datepicker form-control' name='startdate' id='startdate' size='10' value='<?php echo attr($sdate); ?>' title='yyyy-mm-dd starting date for search' />
                </div>
                <label for="searchdays" class="col-auto col-form-label"><?php echo xlt('for'); ?></label>
                <div class="col-auto">
                    <input type='text' class="form-control" name='searchdays' id='searchdays' size='3' value='<?php echo attr($searchdays); ?>' title='Number of days to search from the start date' />
                </div>
                <label for="searchdays" class="col-auto col-form-label"><?php echo xlt('days'); ?></label>
                <div class="col-auto">
                    <input type='submit' class="btn btn-primary btn-sm btn-block" value='<?php echo xla('Search'); ?>' />
                </div>
            </div>
    </div>

    <?php if (!empty($slots)) : ?>
        <div id="searchResultsHeader">
            <table class='table table-bordered'>

            </table>
        </div>

        <div id="searchResults" class="container-fluid">
            <div class="table-responsive">
                <table class='table table-sm table-striped table-bordered'>
                    <thead id="searchResultsHeader">
                    <tr>
                        <th class="table-dark text-light"><?php echo xlt('Day'); ?></th>
                        <th class="srTimes"><?php echo xlt('Available Times'); ?></th>
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
                            echo "  <td class='table-dark text-light'>" . date("l", $utime) . "<br />" . date("Y-m-d", $utime) . "</td>\n";
                            echo "  <td class='srTimes'>";
                            echo "<div id='am'>AM<hr class='m-0 p-0 mb-n3'/><br/>";
                            $ampmFlag = "am";  // reset the AMPM flag
                        }

                        $ampm = date('a', $utime);
                        if ($ampmFlag != $ampm) {
                            echo "</div><div id='pm'><hr class='m-0 p-0' />PM<hr class='m-0 p-0 mb-n3' /><br/>";
                        }

                        $ampmFlag = $ampm;

                        $atitle = "Choose " . date("h:i a", $utime);
                        $adate = getdate($utime);

                        $anchor = "<a href='' onclick='return setappt(" .
                            attr_js(date("Y", $utime)) . "," .
                            attr_js($adate['mon']) . "," .
                            attr_js($adate['mday']) . "," .
                            attr_js(date("G", $utime)) . "," .
                            attr_js(date("i", $utime)) . "," .
                            attr_js(date('a', $utime)) . ")'" .
                            " title='" . attr($atitle) . "' alt='" . attr($atitle) . "'" .
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
                        echo " <tr><td colspan='2'> " . xlt('No openings were found for this period.') . "</td></tr>\n";
                    }
                    ?>
                </table>
            </div>
        </div>
        </div>
    <?php endif; ?>

    </form>

    <script>

        // jQuery stuff to make the page a little easier to use
        $(function () {
            $(".oneresult").hover(function () {
                $(this).toggleClass("highlight");
            }, function () {
                $(this).toggleClass("highlight");
            });
            $(".oneresult a").hover(function () {
                $(this).toggleClass("blue_highlight");
                $(this).children().toggleClass("blue_highlight");
            }, function () {
                $(this).toggleClass("blue_highlight");
                $(this).children().toggleClass("blue_highlight");
            });

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });

        });

    </script>
</body>
</html>
