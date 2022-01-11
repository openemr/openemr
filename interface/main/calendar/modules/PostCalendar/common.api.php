<?php

/**
 *  $Id$
 *
 *  PostCalendar::PostNuke Events Calendar Module
 *  Copyright (C) 2002  The PostCalendar Team
 *  http://postcalendar.tv
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *  To read the license please read the docs/license.txt or visit
 *  http://www.gnu.org/copyleft/gpl.html
 *
 */

//=================================================================
//  define constants used to make the code more readable
//=================================================================
define('_IS_SUNDAY', 0);
define('_IS_MONDAY', 1);
define('_IS_SATURDAY', 6);
define('_AM_VAL', 1);
define('_PM_VAL', 2);
define('_ACTION_DELETE', 4);
define('_ACTION_EDIT', 2);
define('_EVENT_TEMPLATE', 8);
define('_EVENT_TEMPORARY', -9);
define('_EVENT_APPROVED', 1);
define('_EVENT_QUEUED', 0);
define('_EVENT_HIDDEN', -1);
// $event_repeat
define('NO_REPEAT', 0);
define('REPEAT', 1);
define('REPEAT_ON', 2);
define('REPEAT_DAYS', 3);
// $event_repeat_freq
define('REPEAT_EVERY', 1);
define('REPEAT_EVERY_OTHER', 2);
define('REPEAT_EVERY_THIRD', 3);
define('REPEAT_EVERY_FOURTH', 4);
// $event_repeat_freq_type
if (!defined('REPEAT_EVERY_DAY')) {
    define('REPEAT_EVERY_DAY', 0);
}
if (!defined('REPEAT_EVERY_WEEK')) {
    define('REPEAT_EVERY_WEEK', 1);
}
if (!defined('REPEAT_EVERY_MONTH')) {
    define('REPEAT_EVERY_MONTH', 2);
}
if (!defined('REPEAT_EVERY_YEAR')) {
    define('REPEAT_EVERY_YEAR', 3);
}
if (!defined('REPEAT_EVERY_WORK_DAY')) {
    define('REPEAT_EVERY_WORK_DAY', 4);
}
// $event_repeat_on_num
define('REPEAT_ON_1ST', 1);
define('REPEAT_ON_2ND', 2);
define('REPEAT_ON_3RD', 3);
define('REPEAT_ON_4TH', 4);
define('REPEAT_ON_LAST', 5);
// $event_repeat_on_day
define('REPEAT_ON_SUN', 0);
define('REPEAT_ON_MON', 1);
define('REPEAT_ON_TUE', 2);
define('REPEAT_ON_WED', 3);
define('REPEAT_ON_THU', 4);
define('REPEAT_ON_FRI', 5);
define('REPEAT_ON_SAT', 6);
// $event_repeat_on_freq
define('REPEAT_ON_MONTH', 1);
define('REPEAT_ON_2MONTH', 2);
define('REPEAT_ON_3MONTH', 3);
define('REPEAT_ON_4MONTH', 4);
define('REPEAT_ON_6MONTH', 6);
define('REPEAT_ON_YEAR', 12);
// event sharing values
define('SHARING_PRIVATE', 0);
define('SHARING_PUBLIC', 1);
define('SHARING_BUSY', 2);
define('SHARING_GLOBAL', 3);
// $cat_type
define('TYPE_ON_PATIENT', 0);
define('TYPE_ON_PROVIDER', 1);
define('TYPE_ON_CLINIC', 2);
define('TYPE_ON_THERAPY_GROUP', 3);
// admin defines
define('_ADMIN_ACTION_APPROVE', 0);
define('_ADMIN_ACTION_HIDE', 1);
define('_ADMIN_ACTION_EDIT', 2);
define('_ADMIN_ACTION_VIEW', 3);
define('_ADMIN_ACTION_DELETE', 4);
//=================================================================
//  Get the global PostCalendar config settings
//    This will save us a lot of time and DB queries later
//=================================================================
define('_SETTING_USE_POPUPS', pnModGetVar(__POSTCALENDAR__, 'pcUsePopups'));
define('_SETTING_USE_INT_DATES', pnModGetVar(__POSTCALENDAR__, 'pcUseInternationalDates'));
define('_SETTING_OPEN_NEW_WINDOW', pnModGetVar(__POSTCALENDAR__, 'pcEventsOpenInNewWindow'));
define('_SETTING_DAY_HICOLOR', pnModGetVar(__POSTCALENDAR__, 'pcDayHighlightColor'));
define('_SETTING_FIRST_DAY_WEEK', pnModGetVar(__POSTCALENDAR__, 'pcFirstDayOfWeek'));
define('_SETTING_DATE_FORMAT', pnModGetVar(__POSTCALENDAR__, 'pcEventDateFormat'));
define('_SETTING_TIME_24HOUR', pnModGetVar(__POSTCALENDAR__, 'pcTime24Hours'));
define('_SETTING_DIRECT_SUBMIT', pnModGetVar(__POSTCALENDAR__, 'pcAllowDirectSubmit'));
define('_SETTING_DISPLAY_TOPICS', pnModGetVar(__POSTCALENDAR__, 'pcDisplayTopics'));
define('_SETTING_ALLOW_GLOBAL', pnModGetVar(__POSTCALENDAR__, 'pcAllowSiteWide'));
define('_SETTING_ALLOW_USER_CAL', pnModGetVar(__POSTCALENDAR__, 'pcAllowUserCalendar'));
define('_SETTING_TIME_INCREMENT', pnModGetVar(__POSTCALENDAR__, 'pcTimeIncrement'));
define('_SETTING_HOW_MANY_EVENTS', pnModGetVar(__POSTCALENDAR__, 'pcListHowManyEvents'));
define('_SETTING_TEMPLATE', pnModGetVar(__POSTCALENDAR__, 'pcTemplate'));
define('_SETTING_EVENTS_IN_YEAR', pnModGetVar(__POSTCALENDAR__, 'pcShowEventsInYear'));
define('_SETTING_USE_CACHE', pnModGetVar(__POSTCALENDAR__, 'pcUseCache'));
define('_SETTING_CACHE_LIFETIME', pnModGetVar(__POSTCALENDAR__, 'pcCacheLifetime'));
define('_SETTING_DEFAULT_VIEW', pnModGetVar(__POSTCALENDAR__, 'pcDefaultView'));
define('_SETTING_SAFE_MODE', pnModGetVar(__POSTCALENDAR__, 'pcSafeMode'));
define('_SETTING_NOTIFY_ADMIN', pnModGetVar(__POSTCALENDAR__, 'pcNotifyAdmin'));
define('_SETTING_NOTIFY_EMAIL', pnModGetVar(__POSTCALENDAR__, 'pcNotifyEmail'));
//=========================================================================
//  Require and Setup utility classes and functions
//=========================================================================
define('DATE_CALC_BEGIN_WEEKDAY', _SETTING_FIRST_DAY_WEEK);
require_once("modules/$pcDir/pnincludes/Date/Calc.php");
//=========================================================================
//  grab the global language file
//=========================================================================
require_once("modules/$pcDir/pnlang/eng/global.php");

//=========================================================================
//  Setup Smarty defines
//=========================================================================
require_once("modules/$pcDir/pcSmarty.class.php");
//=========================================================================
//  utility functions for postcalendar
//=========================================================================
function &pcVarPrepForDisplay($s)
{
    $s = nl2br(pnVarPrepForDisplay(postcalendar_removeScriptTags($s)));
    $s = preg_replace('/&amp;(#)?([0-9a-z]+);/i', '&\\1\\2;', $s);
    return $s;
}
function &pcVarPrepHTMLDisplay($s)
{
    $postcalendarRemoveScriptTags = pnVarPrepHTMLDisplay(postcalendar_removeScriptTags($s));
    return $postcalendarRemoveScriptTags;
}
function pcGetTopicName($topicid)
{
    // not using topics in OpenEMR, so just return nothing
    return '';
}
function &postcalendar_makeValidURL($s)
{
    if (empty($s)) {
        $s = '';
        return $s;
    }

    if (!preg_match('|^http[s]?:\/\/|i', $s)) {
        $s = 'http://' . $s;
    }

    return $s;
}
function postcalendar_removeScriptTags($in)
{
    return preg_replace("/<script.*?>(.*?)<\/script>/", "", $in);
}

function postcalendar_getDate($format = 'Ymd')
{
    list($Date, $jumpday, $jumpmonth, $jumpyear, $jumpdate) =
        pnVarCleanFromInput('Date', 'jumpday', 'jumpmonth', 'jumpyear', 'jumpdate');
    if (!isset($Date)) {
        // if we still don't have a date then calculate it
        // check the jump menu, might be a 'jumpdate' input field or m/d/y select lists
        if ($jumpdate) {
            $jumpyear  = substr($jumpdate, 0, 4);
            $jumpmonth = substr($jumpdate, 5, 2);
            $jumpday   = substr($jumpdate, 8, 2);
        } else {
            if (!empty($_SESSION['lastcaldate'])) {
                $time = strtotime($_SESSION['lastcaldate']);
            } else {
                $time = time();
            }

            if (!isset($jumpday)) {
                $jumpday   = date('d', $time);
            }

            if (!isset($jumpmonth)) {
                $jumpmonth = date('m', $time);
            }

            if (!isset($jumpyear)) {
                $jumpyear  = date('Y', $time);
            }
        }

        // create the correct date string
        $Date = (int) "$jumpyear$jumpmonth$jumpday";
    }

    $y = substr($Date, 0, 4);
    $m = substr($Date, 4, 2);
    $d = substr($Date, 6, 2);
    OpenEMR\Common\Session\SessionUtil::setSession('lastcaldate', "$y-$m-$d"); // remember the last chosen date
    return date($format, mktime(0, 0, 0, $m, $d, $y));
}

function &postcalendar_today($format = 'Ymd')
{
    $time = time();
    $date = date($format, $time);

    return $date;
}

/**
 * postcalendar_userapi_pageSetup()
 *
 * sets up any necessary javascript for the page
 * @return string javascript to insert into the page
 */
function postcalendar_userapi_pageSetup()
{
    $output = '';
    // load the DHTML JavaScript code and insert it into the page
    if (_SETTING_USE_POPUPS) {
        $output .= postcalendar_userapi_loadPopups();
    }

    // insert the js popup code into the page (find better code)
    if (_SETTING_OPEN_NEW_WINDOW) {
        $output .= postcalendar_userapi_jsPopup();
    }

    return $output;
}
/**
 * postcalendar_userapi_jsPopup
 * Creates the necessary javascript code for a popup window
 */
function postcalendar_userapi_jsPopup()
{
    if (defined('_POSTCALENDAR_JSPOPUPS_LOADED')) {
        // only put the script on the page once
        return false;
    }

    define('_POSTCALENDAR_JSPOPUPS_LOADED', true);

    // build the correct link
    $js_link = "'index.php?module=" . __POSTCALENDAR__ . "&type=user&func=view&viewtype=details&eid='+eid+'&Date='+date+'&popup=1'";
    $js_window_options = 'toolbar=no,'
                       . 'location=no,'
                       . 'directories=no,'
                       . 'status=no,'
                       . 'menubar=no,'
                       . 'scrollbars=yes,'
                       . 'resizable=no,'
                       . 'width=600,'
                       . 'height=300';

    $output = <<<EOF

<script>
<!--
function opencal(eid,date) {
    window.name='csCalendar';
    w = window.open($js_link,'PostCalendarEvents','$js_window_options');
}
// -->
</script>

EOF;
    return $output;
}

/**
 * postcalendar_userapi_loadPopups
 * Creates the necessary javascript code for mouseover dHTML popups
 */
function postcalendar_userapi_loadPopups()
{
    if (defined('_POSTCALENDAR_LOADPOPUPS_LOADED')) {
        // only put the script on the page once
        return false;
    }

    define('_POSTCALENDAR_LOADPOPUPS_LOADED', true);

    // get the theme globals :: is there a better way to do this?
    global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5;
    global $textcolor1, $textcolor2;

    // lets get the module's information
    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $pcDir = pnVarPrepForOS($modinfo['directory']);
    unset($modinfo);
    $capicon = '';
    $close = _PC_OL_CLOSE;

    $output = <<<EOF

<script>
<!-- overLIB configuration -->
ol_fgcolor = "$bgcolor1";
ol_bgcolor = "$bgcolor2";
ol_textcolor = "$textcolor2";
ol_capcolor = "$textcolor2";
ol_closecolor = "$textcolor2";
ol_textfont = "Verdana,Arial,Helvetica";
ol_captionfont = "Verdana,Arial,Helvetica";
ol_captionsize = 2;
ol_textsize = 2;
ol_border = 2;
ol_width = 350;
ol_offsetx = 10;
ol_offsety = 10;
ol_sticky = 0;
ol_close = "$close";
ol_closeclick = 0;
ol_autostatus = 2;
ol_snapx = 0;
ol_snapy = 0;
ol_fixx = -1;
ol_fixy = -1;
ol_background = "";
ol_fgbackground = "";
ol_bgbackground = "";
ol_padxl = 1;
ol_padxr = 1;
ol_padyt = 1;
ol_padyb = 1;
ol_capicon = "$capicon";
ol_hauto = 1;
ol_vauto = 1;
</script>
<div id="overDiv" style="position:absolute; top:0px; left:0px; visibility:hidden; z-index:1000;"></div>
<script src="modules/$pcDir/pnincludes/overlib_mini.js">
<!-- overLIB (c) Erik Bosrup -->
</script>

EOF;
    return $output;
}

/**
 * postcalendar_userapi_getmonthname()
 *
 * Returns the month name translated for the user's current language
 *
 * @param array $args['Date'] date to return month name of
 * @return string month name in user's language
 */
function postcalendar_userapi_getmonthname($args)
{
    extract($args);
    unset($args);
    if (!isset($Date)) {
        return false;
    }

    $month_name = array('01' => _CALJAN, '02' => _CALFEB, '03' => _CALMAR,
                        '04' => _CALAPR, '05' => _CALMAY, '06' => _CALJUN,
                        '07' => _CALJUL, '08' => _CALAUG, '09' => _CALSEP,
                        '10' => _CALOCT, '11' => _CALNOV, '12' => _CALDEC);
    return $month_name[date('m', $Date)];
}

/**
 *  Returns an array of form data for FormSelectMultiple
 */
function postcalendar_userapi_buildMonthSelect($args)
{
    extract($args);
    unset($args);
    if (!isset($pc_month)) {
        $pc_month = Date_Calc::getMonth();
    }

    // create the return object to be inserted into the form
    $output = array();
    if (!isset($selected)) {
        $selected = '';
    }

    for ($c = 0,$i = 1; $i <= 12; $i++,$c++) {
        if ($selected) {
            $sel = $selected == $i ? true : false;
        } elseif ($i == $pc_month) {
            $sel = true;
        } else {
                $sel = false;
        }

            $output[$c]['id']       = sprintf('%02d', $i);
            $output[$c]['selected'] = $sel;
            $output[$c]['name']     = postcalendar_userapi_getmonthname(array('Date' => mktime(0, 0, 0, $i, 15)));
    }

    return $output;
}

/**
 *  Returns an array of form data for FormSelectMultiple
 */
function postcalendar_userapi_buildDaySelect($args)
{
    extract($args);
    unset($args);
    if (!isset($pc_day)) {
        $pc_day = Date_Calc::getDay();
    }

    // create the return object to be inserted into the form
    $output = array();
    if (!isset($selected)) {
        $selected = '';
    }

    for ($c = 0,$i = 1; $i <= 31; $i++,$c++) {
        if ($selected) {
            $sel = $selected == $i ? true : false;
        } elseif ($i == $pc_day) {
            $sel = true;
        } else {
                $sel = false;
        }

            $output[$c]['id']       = sprintf('%02d', $i);
            $output[$c]['selected'] = $sel;
            $output[$c]['name']     = sprintf('%02d', $i);
    }

    return $output;
}

/**
 *  Returns an array of form data for FormSelectMultiple
 */
function postcalendar_userapi_buildYearSelect($args)
{
    extract($args);
    unset($args);
    if (!isset($pc_year)) {
        $pc_year = date('Y');
    }

    // create the return object to be inserted into the form
    $output = array();
    // we want the list to contain 10 years before today and 30 years after
    // maybe this will eventually become a user defined value
    $pc_start_year = date('Y') - 1;
    $pc_end_year = date('Y') + 30;
    if (!isset($selected)) {
        $selected = '';
    }

    for ($c = 0,$i = $pc_start_year; $i <= $pc_end_year; $i++,$c++) {
        if ($selected) {
            $sel = $selected == $i ? true : false;
        } elseif ($i == $pc_year) {
            $sel = true;
        } else {
                $sel = false;
        }

            $output[$c]['id']       = sprintf('%04d', $i);
            $output[$c]['selected'] = $sel;
            $output[$c]['name']     = sprintf('%04d', $i);
    }

    return $output;
}

function &postcalendar_userapi_getCategories()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $cat_table = $pntable['postcalendar_categories'];
    $sql = "SELECT pc_catid,pc_catname,pc_constant_id,pc_catcolor,pc_catdesc,
            pc_recurrtype,pc_recurrspec,pc_recurrfreq,pc_duration,
            pc_dailylimit,pc_end_date_flag,pc_end_date_type,pc_end_date_freq,
            pc_end_all_day,pc_cattype,pc_active,pc_seq,aco_spec FROM $cat_table
            ORDER BY pc_catname";
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        return array();
    }

    if (!isset($result)) {
        return array();
    }

    $categories = array();
    for ($i = 0; !$result->EOF; $result->MoveNext()) {
        list($catid,$catname,$constantid,$catcolor,$catdesc,
            $rtype,$rspec,$rfreq,$duration,$limit,$end_date_flag,
            $end_date_type,$end_date_freq,$end_all_day,$cattype,$active,$seq,$aco) = $result->fields;

        $categories[$i]['id']     = $catid;
        $categories[$i]['name']   = $catname;
        $categories[$i]['constantid']   = $constantid;
        $categories[$i]['color']  = $catcolor;
        $categories[$i]['desc'] = $catdesc;
        $categories[$i]['value_cat_type'] = $cattype;
        $categories[$i]['active']   = $active;
        $categories[$i]['sequence']   = $seq;
        $categories[$i]['event_repeat'] = $rtype;
        $rspecs = unserialize($rspec, ['allowed_classes' => false]);
        $categories[$i]['event_repeat_freq'] = $rspecs['event_repeat_freq'];
        $categories[$i]['event_repeat_freq_type'] = $rspecs['event_repeat_freq_type'];
        $categories[$i]['event_repeat_on_num'] = $rspecs['event_repeat_on_num'];
        $categories[$i]['event_repeat_on_day'] = $rspecs['event_repeat_on_day'];
        $categories[$i]['event_repeat_on_freq'] = $rspecs['event_repeat_on_freq'];
        $categories[$i]['event_recurrspec'] = $rspecs;
        $categories[$i]['event_duration'] = $duration;
        $categories[$i]['event_durationh'] = (int)($duration / (60 * 60));    //seconds divided by 60 seconds * 60 minutes;
        $categories[$i]['event_durationm'] = ($duration % (60 * 60)) / 60;
        $categories[$i]['end_date_flag'] = $end_date_flag;
        $categories[$i]['end_date_type'] = $end_date_type;
        $categories[$i]['end_date_freq'] = $end_date_freq;
        $categories[$i]['end_all_day'] = $end_all_day;
        $categories[$i]['aco'] = $aco;
        $categories[$i++]['dailylimit'] = $limit;
    }

    $result->Close();
    return $categories;
}

function &postcalendar_userapi_getTopics()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $topics_table = $pntable['topics'];
    $topics_column = &$pntable['topics_column'];
    $sql = "SELECT $topics_column[topicid], $topics_column[topictext], $topics_column[topicname]
            FROM $topics_table
            ORDER BY $topics_column[topictext]";
    $topiclist = $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }

    $data = array();
    $i = 0;
    for (; !$topiclist->EOF; $topiclist->MoveNext()) {
        list($data[$i]['id'], $data[$i]['text'], $data[$i++]['name']) = $topiclist->fields;
    }

    $topiclist->Close();
    return $data;
}

function findFirstAvailable($period)
{
    //print_r($period);

    $day_date = "";
    $available_times = array();
    foreach ($period as $date => $day) {
        //echo "begin free times for $date:<br />";
        $ffid_res = findFirstInDay($day, $date);
        foreach ($ffid_res as $times) {
            //echo "starting: " . date("h:i:s A",$times['startTime']) . " long: " . $times['duration'] . "<br />";
            $available_times[$date][] = $times;
            //echo "count of times is:" . count($available_times) . "<br />";
        }

        //echo "end free times for $date";
    }

    return $available_times;
}

function findFirstInDay($day, $date)
{
    $stack = array();
    $lastcat = 3;
    $intime = false;
    $outtime = false;
    foreach ($day as $event) {
        //echo "event is: " . $event['title'] . " cat is: " .$event['catid'] . " event date is: " . $date . "<br />";

        if ($event['catid'] == 2) { //catid 2 is reserved to represent "In Office" events, id 3 is "Out Of Office"
            $intime = $event['startTime'];
            //echo "setting in: $intime<br />";
        } elseif ($event['catid'] == 3) {
            $outtime = $event['startTime'];
            //echo "setting out: $outtime<br />";
        }
    }

    if ($intime == false or $outtime == false) {
        return array();
    }

    //echo "increment is: "  . _SETTING_TIME_INCREMENT . "<br />";
    $inc = (_SETTING_TIME_INCREMENT * 60);
    //$inc = 60;
    $intime_sec = date("U", strtotime($date . " " . $intime));
    $outtime_sec =  date("U", strtotime($date . " " . $outtime));
    $free_time = $intime_sec;

    $times = array();
    for ($i = $intime_sec; $i < $outtime_sec; $i += $inc) {
        //echo "time is now: " . date("h:i:s A",$i) . "<br />";
        $closest_start = $outtime_sec;
        $timeclear = false;
        foreach ($day as $event) {
            if ($event['catid'] != 2) {
                $estart = dtSec($date, $event['startTime']) ;
                $eend = dtSecDur($date, $event['startTime'], $event['duration']);

                if ($eend < $intime_sec or $estart > $outtime_sec) {
                    //event ends before intime or starts after outtime we don't care move on;
                    continue;
                } elseif ($eend < $i) {
                    //event ended before time currently being evaluated, we don't care move on;
                    continue;
                } elseif ($estart < $i and $eend > $i) {
                    //event occupies part of the time we are looking at, look at another time
                    continue;
                } elseif ($estart >= $i) {
                    //echo "tin: " . date("h:i:s A",$i) . " estart: " . date("h:i:s A",$estart) . "<br />";

                    //echo "ev: " . $event['title'] . " s at:" . date("h:i:s A",$estart) . " e at: " . date("h:i:s A",$eend) ." <br />";
                    //some amount of time is free set closest time
                    $oldfreetime = $closest_start - $i;
                    $newfreetime = $estart - $i;

                    //echo "old free: " . $oldfreetime . "<br />";
                    //echo "new free: " . $newfreetime . "<br />";
                    //echo "duration is: " . ($estart - $i) . " cs:$estart i:$i<br />";
                    if ($newfreetime < $oldfreetime && ($estart - $i) != 0) {
                        $free_time = $i;
                        $closest_start = $estart;

                        //echo "set time is " . date("h:i:s A",$i) . " min free: " . (($closest_start - $i)/60)   . " " . date("h:i:s A",$closest_start) .  "<br />";
                        if ($i < ($eend - $inc)) {
                            $i = ($eend - $inc);
                        }
                    } elseif ($newfreetime <= $oldfreetime && $oldfreetime == ($outtime_sec - $i)) {
                        $free_time = $i;
                        $closest_start = $estart;

                        //echo "time is " . date("h:i:s A",$i) . " min free: " . (($closest_start - $i)/60)   . " " . date("h:i:s A",$closest_start) .  "<br />";
                        if ($i < ($eend - $inc)) {
                            $i = ($eend - $inc);
                        }
                    }

                    //echo "closest start: " . date("h:i:s A",$closest_start) . "<br />";
                }
            }
        }

        if ($closest_start > ($intime_sec + 60)) {
            //echo "free time is: " . date("h:i:s A",$free_time) . "<br />";
            //echo "next app is: " . date("h:i:s A",$closest_start) . "<br />";
            $duration = ($closest_start - $free_time);
            //echo "duration is: $duration<br />";
            //we allow for 0 duration events so other things such as overlap and actual times can be calculated
            //this happens because people want to be able to set 8:00 - 8:15 and 8:15 - 8:30 without a conflict
            //even though that is technially impossible, so we pretend, however here we weed out the 0
            //length blocks so that won't be seen
            $date_sec = strtotime($date);
            if ($duration > 0) {
                $times[] = array ("startTime" => $free_time, "endTime" => ($date_sec + $duration));
            }
        }
    }

    return $times;
}

function dtSec($date, $time)
{
    return date("U", strtotime($date . " " . $time));
}

function dtSecDur($date, $time, $dur)
{
    $time_sec = date("U", strtotime($date . " " . $time));
    return $time_sec + $dur;
}

function postcalendar_footer()
{
    // lets get the module's information
    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    //$footer = "<p align=\"right\"><a href=\"http://www.postcalendar.tv\">PostCalendar v$modinfo[version]</a></p>";
    $footer = "";
    return $footer;
}

function sort_byCategoryA($a, $b)
{
    if ($a['catname'] < $b['catname']) {
        return -1;
    } elseif ($a['catname'] > $b['catname']) {
        return 1;
    }
}
function sort_byCategoryD($a, $b)
{
    if ($a['catname'] < $b['catname']) {
        return 1;
    } elseif ($a['catname'] > $b['catname']) {
        return -1;
    }
}
function sort_byTitleA($a, $b)
{
    if ($a['title'] < $b['title']) {
        return -1;
    } elseif ($a['title'] > $b['title']) {
        return 1;
    }
}
function sort_byTitleD($a, $b)
{
    if ($a['title'] < $b['title']) {
        return 1;
    } elseif ($a['title'] > $b['title']) {
        return -1;
    }
}
function sort_byTimeA($a, $b)
{
    if ($a['startTime'] < ($b['startTime'] ?? null)) {
        return -1;
    } elseif ($a['startTime'] > ($b['startTime'] ?? null)) {
        return 1;
    }
}
function sort_byTimeD($a, $b)
{
    if ($a['startTime'] < $b['startTime']) {
        return 1;
    } elseif ($a['startTime'] > $b['startTime']) {
        return -1;
    }
}
/**
 *    pc_clean
 *    @param s string text to clean
 *    @return string cleaned up text
 */
function pc_clean($s)
{
    $display_type = substr($s, 0, 6);
    if ($display_type == ':text:') {
        $s = substr($s, 6);
    } elseif ($display_type == ':html:') {
        $s = substr($s, 6);
    }

    unset($display_type);
    $s = preg_replace('/[\r|\n]/i', '', $s);
    $s = str_replace("'", "\'", $s);
    $s = str_replace('"', '&quot;', $s);
    // ok, now we need to break really long lines
    // we only want to break at spaces to allow for
    // correct interpretation of special characters
    $tmp = explode(' ', $s);
    return join("'+' ", $tmp);
}
