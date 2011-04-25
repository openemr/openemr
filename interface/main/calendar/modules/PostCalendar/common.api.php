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
define('_IS_SUNDAY',           0);
define('_IS_MONDAY',           1);
define('_IS_SATURDAY',         6);
define('_AM_VAL',              1);
define('_PM_VAL',              2);
define('_ACTION_DELETE',       4);
define('_ACTION_EDIT',         2);
define('_EVENT_TEMPLATE',      8);
define('_EVENT_TEMPORARY',     -9);
define('_EVENT_APPROVED',      1);
define('_EVENT_QUEUED',        0);
define('_EVENT_HIDDEN',       -1);
// $event_repeat
define('NO_REPEAT',            0);
define('REPEAT',               1);
define('REPEAT_ON',            2);
// $event_repeat_freq
define('REPEAT_EVERY',         1);
define('REPEAT_EVERY_OTHER',   2);
define('REPEAT_EVERY_THIRD',   3);
define('REPEAT_EVERY_FOURTH',  4);
// $event_repeat_freq_type
define('REPEAT_EVERY_DAY',     0);
define('REPEAT_EVERY_WEEK',    1);
define('REPEAT_EVERY_MONTH',   2);
define('REPEAT_EVERY_YEAR',    3);
define('REPEAT_EVERY_WORK_DAY',4);
// $event_repeat_on_num
define('REPEAT_ON_1ST',        1);
define('REPEAT_ON_2ND',        2);
define('REPEAT_ON_3RD',        3);
define('REPEAT_ON_4TH',        4);
define('REPEAT_ON_LAST',       5);
// $event_repeat_on_day
define('REPEAT_ON_SUN',        0);
define('REPEAT_ON_MON',        1);
define('REPEAT_ON_TUE',        2);
define('REPEAT_ON_WED',        3);
define('REPEAT_ON_THU',        4);
define('REPEAT_ON_FRI',        5);
define('REPEAT_ON_SAT',        6);
// $event_repeat_on_freq
define('REPEAT_ON_MONTH',      1);
define('REPEAT_ON_2MONTH',     2);
define('REPEAT_ON_3MONTH',     3);
define('REPEAT_ON_4MONTH',     4);
define('REPEAT_ON_6MONTH',     6);
define('REPEAT_ON_YEAR',       12);
// event sharing values
define('SHARING_PRIVATE',      0);
define('SHARING_PUBLIC',       1);
define('SHARING_BUSY',         2);
define('SHARING_GLOBAL',       3);
// $cat_type
define('TYPE_ON_PATIENT',        0);
define('TYPE_ON_PROVIDER',        1);
// admin defines
define('_ADMIN_ACTION_APPROVE',   0);
define('_ADMIN_ACTION_HIDE',      1);
define('_ADMIN_ACTION_EDIT',      2);
define('_ADMIN_ACTION_VIEW',      3);
define('_ADMIN_ACTION_DELETE',    4);
//=================================================================
//  Get the global PostCalendar config settings
//    This will save us a lot of time and DB queries later
//=================================================================
define('_SETTING_USE_POPUPS',       pnModGetVar(__POSTCALENDAR__,'pcUsePopups'));
define('_SETTING_USE_INT_DATES',   pnModGetVar(__POSTCALENDAR__,'pcUseInternationalDates'));
define('_SETTING_OPEN_NEW_WINDOW', pnModGetVar(__POSTCALENDAR__,'pcEventsOpenInNewWindow'));
define('_SETTING_DAY_HICOLOR',       pnModGetVar(__POSTCALENDAR__,'pcDayHighlightColor'));
define('_SETTING_FIRST_DAY_WEEK',  pnModGetVar(__POSTCALENDAR__,'pcFirstDayOfWeek'));
define('_SETTING_DATE_FORMAT',       pnModGetVar(__POSTCALENDAR__,'pcEventDateFormat'));
define('_SETTING_TIME_24HOUR',       pnModGetVar(__POSTCALENDAR__,'pcTime24Hours'));
define('_SETTING_DIRECT_SUBMIT',   pnModGetVar(__POSTCALENDAR__,'pcAllowDirectSubmit'));
define('_SETTING_DISPLAY_TOPICS',  pnModGetVar(__POSTCALENDAR__,'pcDisplayTopics'));
define('_SETTING_ALLOW_GLOBAL',       pnModGetVar(__POSTCALENDAR__,'pcAllowSiteWide'));
define('_SETTING_ALLOW_USER_CAL',  pnModGetVar(__POSTCALENDAR__,'pcAllowUserCalendar'));
define('_SETTING_TIME_INCREMENT',  pnModGetVar(__POSTCALENDAR__,'pcTimeIncrement'));
define('_SETTING_HOW_MANY_EVENTS', pnModGetVar(__POSTCALENDAR__,'pcListHowManyEvents'));
define('_SETTING_TEMPLATE',           pnModGetVar(__POSTCALENDAR__,'pcTemplate'));
define('_SETTING_EVENTS_IN_YEAR',  pnModGetVar(__POSTCALENDAR__,'pcShowEventsInYear'));
define('_SETTING_USE_CACHE',       pnModGetVar(__POSTCALENDAR__,'pcUseCache'));
define('_SETTING_CACHE_LIFETIME',  pnModGetVar(__POSTCALENDAR__,'pcCacheLifetime'));
define('_SETTING_DEFAULT_VIEW',       pnModGetVar(__POSTCALENDAR__,'pcDefaultView'));
define('_SETTING_SAFE_MODE',       pnModGetVar(__POSTCALENDAR__,'pcSafeMode'));
define('_SETTING_NOTIFY_ADMIN',       pnModGetVar(__POSTCALENDAR__,'pcNotifyAdmin'));
define('_SETTING_NOTIFY_EMAIL',       pnModGetVar(__POSTCALENDAR__,'pcNotifyEmail'));
//=================================================================
//  Make checking basic permissions easier
//=================================================================
define('PC_ACCESS_ADMIN',      pnSecAuthAction(0, 'PostCalendar::', 'null::null', ACCESS_ADMIN));
define('PC_ACCESS_DELETE',      pnSecAuthAction(0, 'PostCalendar::', 'null::null', ACCESS_DELETE));
define('PC_ACCESS_ADD',      pnSecAuthAction(0, 'PostCalendar::', 'null::null', ACCESS_ADD));
define('PC_ACCESS_EDIT',      pnSecAuthAction(0, 'PostCalendar::', 'null::null', ACCESS_EDIT));
define('PC_ACCESS_MODERATE', pnSecAuthAction(0, 'PostCalendar::', 'null::null', ACCESS_MODERATE));
define('PC_ACCESS_COMMENT',  pnSecAuthAction(0, 'PostCalendar::', 'null::null', ACCESS_COMMENT));
define('PC_ACCESS_READ',      pnSecAuthAction(0, 'PostCalendar::', 'null::null', ACCESS_READ));
define('PC_ACCESS_OVERVIEW', pnSecAuthAction(0, 'PostCalendar::', 'null::null', ACCESS_OVERVIEW));
define('PC_ACCESS_NONE',      pnSecAuthAction(0, 'PostCalendar::', 'null::null', ACCESS_NONE));
//=========================================================================
//  Require and Setup utility classes and functions
//=========================================================================
define('DATE_CALC_BEGIN_WEEKDAY', _SETTING_FIRST_DAY_WEEK);
require_once("modules/$pcDir/pnincludes/Date/Calc.php");
//=========================================================================
//  grab the global language file
//=========================================================================
$userlang = pnUserGetLang();
if(file_exists("modules/$pcDir/pnlang/$userlang/global.php")) {
    require_once("modules/$pcDir/pnlang/$userlang/global.php");
} else {
    require_once("modules/$pcDir/pnlang/eng/global.php");
}
unset($userlang);
//=========================================================================
//  Setup Smarty defines
//=========================================================================
if(!class_exists('Smarty')) {
    define('_PC_SMARTY_LOADED',true);
    define('SMARTY_DIR',"modules/$pcDir/pnincludes/Smarty/");
    require_once(SMARTY_DIR.'/Smarty.class.php');
}
require_once("modules/$pcDir/pcSmarty.class.php");
//=========================================================================
//  utility functions for postcalendar
//=========================================================================
function pcDebugVar($in)
{
    echo '<pre>';
    if(is_array($in)) print_r($in);
    else echo $in;
    echo '</pre>';
}
function &pcVarPrepForDisplay($s) {
    $s = nl2br(pnVarPrepForDisplay(postcalendar_removeScriptTags($s)));
    $s = preg_replace('/&amp;(#)?([0-9a-z]+);/i','&\\1\\2;',$s);
    return $s;
}
function &pcVarPrepHTMLDisplay($s) {
    return pnVarPrepHTMLDisplay(postcalendar_removeScriptTags($s));
}
function pcGetTopicName($topicid)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $topics_table = $pntable['topics'];
    $topics_column = &$pntable['topics_column'];
    $sql = "SELECT $topics_column[topicname]
            FROM $topics_table
            WHERE $topics_column[topicid] = '$topicid'";
    $result = $dbconn->Execute($sql);
    if($result === false) return '';
    else return $result->fields[0];
}
function &postcalendar_makeValidURL($s)
{
    if(empty($s)) return '';
    if(!preg_match('|^http[s]?:\/\/|i',$s)) {
        $s = 'http://'.$s;
    }
    return $s;
}
function postcalendar_removeScriptTags($in)
{
    return preg_replace("/<script.*?>(.*?)<\/script>/","",$in);
}

function &postcalendar_getDate($format='%Y%m%d')
{
    list($Date, $jumpday, $jumpmonth, $jumpyear, $jumpdate) =
        pnVarCleanFromInput('Date', 'jumpday', 'jumpmonth', 'jumpyear', 'jumpdate');
    if(!isset($Date)) {
        // if we still don't have a date then calculate it
        // check the jump menu, might be a 'jumpdate' input field or m/d/y select lists
        if ($jumpdate) {
            $jumpyear  = substr($jumpdate,0,4);
            $jumpmonth = substr($jumpdate,5,2);
            $jumpday   = substr($jumpdate,8,2);
        } else {
            if ($_SESSION['lastcaldate']) {
                $time = strtotime($_SESSION['lastcaldate']);
            } else {
                $time = time();
                if (pnUserLoggedIn())
                    $time += (pnUserGetVar('timezone_offset') - pnConfigGetVar('timezone_offset')) * 3600;
            }
            if(!isset($jumpday))   $jumpday   = strftime('%d',$time);
            if(!isset($jumpmonth)) $jumpmonth = strftime('%m',$time);
            if(!isset($jumpyear))  $jumpyear  = strftime('%Y',$time);
        }
        // create the correct date string
        $Date = (int) "$jumpyear$jumpmonth$jumpday";
    }
    $y = substr($Date,0,4);
    $m = substr($Date,4,2);
    $d = substr($Date,6,2);
    $_SESSION['lastcaldate'] = "$y-$m-$d"; // remember the last chosen date
    return strftime($format,mktime(0,0,0,$m,$d,$y));
}

function &postcalendar_today($format='%Y%m%d')
{
    $time = time();
    if (pnUserLoggedIn()) {
        $time += (pnUserGetVar('timezone_offset') - pnConfigGetVar('timezone_offset')) * 3600;
    }
    return strftime($format,$time);
}

/**
 * postcalendar_adminapi_pageSetup()
 *
 * sets up any necessary javascript for the page
 * @return string javascript to insert into the page
 */
function postcalendar_adminapi_pageSetup() { return postcalendar_userapi_pageSetup(); }
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
    if(_SETTING_USE_POPUPS) { $output .= postcalendar_userapi_loadPopups(); }
    // insert the js popup code into the page (find better code)
    if(_SETTING_OPEN_NEW_WINDOW) { $output .= postcalendar_userapi_jsPopup(); }
    return $output;
}
/**
 * postcalendar_userapi_jsPopup
 * Creates the necessary javascript code for a popup window
 */
function postcalendar_userapi_jsPopup()
{   if(defined('_POSTCALENDAR_JSPOPUPS_LOADED')) {
        // only put the script on the page once
        return false;
    }
    define('_POSTCALENDAR_JSPOPUPS_LOADED',true);

    // build the correct link
    $js_link = "'index.php?module=".__POSTCALENDAR__."&type=user&func=view&viewtype=details&eid='+eid+'&Date='+date+'&popup=1'";
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

<script language="javascript">
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
{   if(defined('_POSTCALENDAR_LOADPOPUPS_LOADED')) {
        // only put the script on the page once
        return false;
    }
    define('_POSTCALENDAR_LOADPOPUPS_LOADED',true);

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

<script language="JavaScript">
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
<script language="JavaScript" src="modules/$pcDir/pnincludes/overlib_mini.js">
<!-- overLIB (c) Erik Bosrup -->
</script>

EOF;
    return $output;
}

/**
 * postcalendar_adminapi_getmonthname()
 *
 * Returns the month name translated for the user's current language
 *
 * @param array $args['Date'] number of month to return
 * @return string month name in user's language
 */
function postcalendar_adminapi_getmonthname($args) { return postcalendar_userapi_getmonthname($args); }
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
    extract($args); unset($args);
    if(!isset($Date)) { return false; }
    $month_name = array('01' => _CALJAN, '02' => _CALFEB, '03' => _CALMAR,
                        '04' => _CALAPR, '05' => _CALMAY, '06' => _CALJUN,
                        '07' => _CALJUL, '08' => _CALAUG, '09' => _CALSEP,
                        '10' => _CALOCT, '11' => _CALNOV, '12' => _CALDEC);
    return $month_name[date('m',$Date)];
}
/**
 *  Returns an array of form data for FormSelectMultiple
 */
function postcalendar_adminapi_buildTimeSelect($args) { return postcalendar_userapi_buildTimeSelect($args); }
function postcalendar_userapi_buildTimeSelect($args)
{
    $inc = _SETTING_TIME_INCREMENT;
    extract($args); unset($args);
    $output = array('h'=>array(),'m'=>array());
    if((bool)_SETTING_TIME_24HOUR) {
        $start=0; $end=23;
    } else {
        $start=1; $end=12;
        $hselected = $hselected > 12 ? $hselected-=12 : $hselected;
    }
    for($c=0,$h=$start; $h<=$end; $h++,$c++) {
        $hour = sprintf('%02d',$h);
        $output['h'][$c]['id']         = pnVarPrepForStore($h);
        $output['h'][$c]['selected']   = $hselected == $hour;
        $output['h'][$c]['name']       = pnVarPrepForDisplay($hour);
    }
    for($c=0,$m=0; $m<=(60-$inc);$m+=$inc,$c++) {
        $min = sprintf('%02d',$m);
        $output['m'][$c]['id']         = pnVarPrepForStore($m);
        $output['m'][$c]['selected']   = $mselected == $min;
        $output['m'][$c]['name']       = pnVarPrepForDisplay($min);
    }
    return $output;
}
/**
 *  Returns an array of form data for FormSelectMultiple
 */
function postcalendar_adminapi_buildMonthSelect($args) { return postcalendar_userapi_buildMonthSelect($args); }
function postcalendar_userapi_buildMonthSelect($args)
{
    extract($args); unset($args);
    if(!isset($pc_month)) { $pc_month = Date_Calc::getMonth(); }
    // create the return object to be inserted into the form
    $output = array();
    if(!isset($selected)) $selected = '';
    for ($c=0,$i=1;$i<=12;$i++,$c++) {
        if ($selected)              { $sel = $selected == $i ? true : false; }
        elseif ($i == $pc_month)    { $sel = true; }
        else                        { $sel = false; }
        $output[$c]['id']       = sprintf('%02d',$i);
        $output[$c]['selected'] = $sel;
        $output[$c]['name']     = postcalendar_userapi_getmonthname(array('Date'=>mktime(0,0,0,$i,15)));
    }
    return $output;
}

/**
 *  Returns an array of form data for FormSelectMultiple
 */
function postcalendar_adminapi_buildDaySelect($args) { return postcalendar_userapi_buildDaySelect($args); }
function postcalendar_userapi_buildDaySelect($args)
{
    extract($args); unset($args);
    if(!isset($pc_day)) { $pc_day = Date_Calc::getDay(); }
    // create the return object to be inserted into the form
    $output = array();
    if(!isset($selected)) $selected = '';
    for($c=0,$i=1; $i<=31; $i++,$c++) {
        if ($selected)          { $sel = $selected == $i ? true : false; }
        elseif ($i == $pc_day)  { $sel = true; }
        else                    { $sel = false; }
        $output[$c]['id']       = sprintf('%02d',$i);
        $output[$c]['selected'] = $sel;
        $output[$c]['name']     = sprintf('%02d',$i);
    }
    return $output;
}

/**
 *  Returns an array of form data for FormSelectMultiple
 */
function postcalendar_adminapi_buildYearSelect($args) { return postcalendar_userapi_buildYearSelect($args); }
function postcalendar_userapi_buildYearSelect($args)
{
    extract($args); unset($args);
    if(!isset($pc_year)) { $pc_year = date('Y'); }
    // create the return object to be inserted into the form
    $output = array();
    // we want the list to contain 10 years before today and 30 years after
    // maybe this will eventually become a user defined value
    $pc_start_year = date('Y') - 1;
    $pc_end_year = date('Y') + 30;
    if(!isset($selected)) $selected = '';
    for($c=0,$i=$pc_start_year; $i<=$pc_end_year; $i++,$c++) {
        if ($selected)          { $sel = $selected == $i ? true : false; }
        elseif ($i == $pc_year) { $sel = true; }
        else                    { $sel = false; }
        $output[$c]['id']       = sprintf('%04d',$i);
        $output[$c]['selected'] = $sel;
        $output[$c]['name']     = sprintf('%04d',$i);
    }
    return $output;
}

function &postcalendar_adminapi_getCategories() { return postcalendar_userapi_getCategories(); }
function &postcalendar_userapi_getCategories()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $cat_table = $pntable['postcalendar_categories'];
    $sql = "SELECT pc_catid,pc_catname,pc_catcolor,pc_catdesc,
            pc_recurrtype,pc_recurrspec,pc_recurrfreq,pc_duration,
            pc_dailylimit,pc_end_date_flag,pc_end_date_type,pc_end_date_freq,
            pc_end_all_day,pc_cattype FROM $cat_table
            ORDER BY pc_catname";
    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

    $categories = array();
    for($i=0; !$result->EOF; $result->MoveNext()) {
        list($catid,$catname,$catcolor,$catdesc,
            $rtype,$rspec,$rfreq,$duration,$limit,$end_date_flag,
            $end_date_type,$end_date_freq,$end_all_day,$cattype) = $result->fields;
        // check the category's permissions
        if (!pnSecAuthAction(0,'PostCalendar::Category',"$catname::$catid",ACCESS_OVERVIEW)) {
            continue;
        }
        $categories[$i]['id']     = $catid;
        $categories[$i]['name']   = $catname;
        $categories[$i]['color']  = $catcolor;
        $categories[$i]['desc'] = $catdesc;
        $categories[$i]['value_cat_type'] = $cattype;
        $categories[$i]['event_repeat'] = $rtype;
        $rspecs = unserialize($rspec);
        $categories[$i]['event_repeat_freq'] = $rspecs['event_repeat_freq'];
        $categories[$i]['event_repeat_freq_type'] = $rspecs['event_repeat_freq_type'];
        $categories[$i]['event_repeat_on_num'] = $rspecs['event_repeat_on_num'];
        $categories[$i]['event_repeat_on_day'] = $rspecs['event_repeat_on_day'];
        $categories[$i]['event_repeat_on_freq'] = $rspecs['event_repeat_on_freq'];
        $categories[$i]['event_recurrspec'] = $rspecs;
        $categories[$i]['event_duration'] = $duration;
        $categories[$i]['event_durationh'] = (int)($duration/(60 * 60));    //seconds divided by 60 seconds * 60 minutes;
        $categories[$i]['event_durationm'] = ($duration%(60 * 60))/60;
        $categories[$i]['end_date_flag'] = $end_date_flag;
        $categories[$i]['end_date_type'] = $end_date_type;
        $categories[$i]['end_date_freq'] = $end_date_freq;
        $categories[$i]['end_all_day'] = $end_all_day;
        $categories[$i++]['dailylimit'] = $limit;

     }
    $result->Close();
    return $categories;
}

function &postcalendar_adminapi_getTopics() { return postcalendar_userapi_getTopics(); }
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
    if($dbconn->ErrorNo() != 0) {
        return false;
    }
    $data = array();
    $i=0;
    for(;!$topiclist->EOF;$topiclist->MoveNext()) {
        // check topic permissions
        if(pnSecAuthAction(0,'PostCalendar::Topic',$topiclist->fields[2].'::'.$topiclist->fields[0],ACCESS_OVERVIEW)) {
            list($data[$i]['id'], $data[$i]['text'], $data[$i++]['name']) = $topiclist->fields;
        }
    }
    $topiclist->Close();
    return $data;
}

/**
 *    postcalendar_adminapi_submitEvent()
 *    submit an event
 *    @param $args array of event data
 *    @return bool true on success : false on failure;
 */
function postcalendar_adminapi_submitEvent($args) { return postcalendar_userapi_submitEvent($args); }
/**
 *    postcalendar_userapi_submitEvent()
 *    submit an event
 *    @param $args array of event data
 *    @return bool true on success : false on failure;
 */
function postcalendar_userapi_submitEvent($args)
{
    extract($args); unset($args);

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    //    determine if the event is to be published immediately or not
    //also whether is a special system only event i.e. _EVENT_TEMPORARY
    if (empty($event_status)) {
        if( (bool) _SETTING_DIRECT_SUBMIT || (bool) PC_ACCESS_ADMIN || ($event_sharing != SHARING_GLOBAL) ) {
            $event_status = _EVENT_APPROVED;
        } else {
            $event_status = _EVENT_QUEUED;
        }
    }
    elseif ($event_status ==  _EVENT_TEMPORARY) {
        $event_status = _EVENT_TEMPORARY;
    }
    else {
        $event_status = _EVENT_QUEUED;
    }


    // set up some vars for the insert statement
    $startDate = $event_startyear.'-'.$event_startmonth.'-'.$event_startday;
    if($event_endtype == 1) {
        $endDate = $event_endyear.'-'.$event_endmonth.'-'.$event_endday;
    } else {
        $endDate = '0000-00-00';
    }

    if(!isset($event_allday)) $event_allday = 0;
    if((bool)_SETTING_TIME_24HOUR) {
        $startTime = $event_starttimeh.':'.$event_starttimem.':00';
    } else {
        if($event_startampm == _AM_VAL) {
            $event_starttimeh = $event_starttimeh == 12 ? '00' : $event_starttimeh;
        } else {
            $event_starttimeh = $event_starttimeh != 12 ? $event_starttimeh+=12 : $event_starttimeh;
        }
        $startTime = sprintf('%02d',$event_starttimeh).':'.sprintf('%02d',$event_starttimem).':00';
    }
    if ($event_allday == 1) {
        $endTime = "24:00:00";
    }
    else {
        $endTime = date("H:i:00",($event_duration + strtotime($startTime)));
    }

    // get rid of variables we no longer need to save memory
    unset($event_startyear,$event_startmonth,$event_startday,$event_endyear,$event_endmonth,
          $event_endday,$event_starttimeh,$event_starttimem);

    //pennfirm users need to be able to enter apps for different providers, this isn't aoplicable
    /*if(pnUserLoggedIn()) {
        $event_userid = pnUserGetVar('uid');
    } else {
        $event_userid = 0;
    }*/


    if($pc_html_or_text == 'html') {
        $event_desc = ':html:'.$event_desc;
    } else {
        $event_desc = ':text:'.$event_desc;
    }
    list($event_subject,$event_desc,$event_topic,$startDate,$endDate,
         $event_repeat,$startTime,$event_allday,$event_category,
         $event_location_info,$event_conttel,$event_contname,
         $event_contemail,$event_website,$event_fee,$event_status,
         $event_recurrspec,$event_duration,$event_sharing,$event_userid,$event_pid,
         $pc_event_id) = @pnVarPrepForStore($event_subject,$event_desc,$event_topic,$startDate,$endDate,
         $event_repeat,$startTime,$event_allday,$event_category,
         $event_location_info,$event_conttel,$event_contname,
         $event_contemail,$event_website,$event_fee,$event_status,
         $event_recurrspec,$event_duration,$event_sharing,$event_userid,$event_pid,
         $pc_event_id);

    if(!isset($is_update)) { $is_update = false; }
    if($is_update) {
        $sql = "UPDATE $pntable[postcalendar_events]
                SET pc_title = '$event_subject',
                    pc_hometext = '$event_desc',
                    pc_topic = '$event_topic',
                    pc_eventDate = '$startDate',
                    pc_endDate = '$endDate',
                    pc_recurrtype = '$event_repeat',
                    pc_startTime = '$startTime',
                    pc_endTime = '$endTime',
                    pc_alldayevent = '$event_allday',
                    pc_catid = '$event_category',
                    pc_location = '$event_location_info',
                    pc_conttel = '$event_conttel',
                    pc_contname = '$event_contname',
                    pc_contemail = '$event_contemail',
                    pc_website = '$event_website',
                    pc_fee = '$event_fee',
                    pc_eventstatus = '$event_status',
                    pc_recurrspec = '$event_recurrspec',
                    pc_duration = '$event_duration',
                    pc_sharing = '$event_sharing',
                    pc_aid = '$event_userid',
                    pc_pid = '$event_pid'
                WHERE pc_eid = '$pc_event_id'";
    } else {
        $pc_event_id = $dbconn->GenId($pntable['postcalendar_events']);
        $sql = "INSERT INTO $pntable[postcalendar_events] (
                    pc_eid,
                    pc_title,
                    pc_time,
                    pc_hometext,
                    pc_topic,
                    pc_informant,
                    pc_eventDate,
                    pc_endDate,
                    pc_recurrtype,
                    pc_startTime,
                     pc_endTime,
                    pc_alldayevent,
                    pc_catid,
                    pc_location,
                    pc_conttel,
                    pc_contname,
                    pc_contemail,
                    pc_website,
                    pc_fee,
                    pc_eventstatus,
                    pc_recurrspec,
                    pc_duration,
                    pc_sharing,
                    pc_aid,
                    pc_pid)
                VALUES (
                    '$pc_event_id',
                    '$event_subject',
                    NOW(),
                    '$event_desc',
                    '$event_topic',
                    " . $_SESSION['authUserID'] . ",
                    '$startDate',
                    '$endDate',
                    '$event_repeat',
                    '$startTime',
                    '$endTime',
                    '$event_allday',
                    '$event_category',
                    '$event_location_info',
                    '$event_conttel',
                    '$event_contname',
                    '$event_contemail',
                    '$event_website',
                    '$event_fee',
                    '$event_status',
                    '$event_recurrspec',
                    '$event_duration',
                    '$event_sharing',
                    '$event_userid',
                    '$event_pid'
                    )";
    }
    $result = $dbconn->Execute($sql);
    if($result === false) {
        return false;
    } else {
        if((bool)$is_update) {
            $eid = $pc_event_id;
        } else {
            $eid = $dbconn->PO_Insert_ID($pntable['postcalendar_events'],'pc_eid');
        }
        pc_notify($eid,$is_update);
        return true;
    }
}

function pc_notify($eid,$is_update)
{
    if(!(bool)_SETTING_NOTIFY_ADMIN) { return true; }

    $subject = _PC_NOTIFY_SUBJECT;

    if((bool)$is_update) {
        $message = _PC_NOTIFY_UPDATE_MSG;
    } else {
        $message = _PC_NOTIFY_NEW_MSG;
    }

    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $modversion = pnVarPrepForOS($modinfo['version']);
    unset($modinfo);

    $message .= pnModURL(__POSTCALENDAR__,'admin','adminevents',array('pc_event_id'=>$eid,'action'=>_ADMIN_ACTION_VIEW));
    $message .= "\n\n\n\n";
    $message .= "----\n";
    $message .= "PostCalendar $modversion\n";
    $message .= "http://www.postcalendar.tv";

    mail(_SETTING_NOTIFY_EMAIL,$subject,$message,
          "From: " . _SETTING_NOTIFY_EMAIL . "\r\n"
          ."X-Mailer: PHP/" . phpversion() . "\r\n"
          ."X-Mailer: PostCalendar/$modversion" );

    return true;
}


function findFirstAvailable($period) {
    //print_r($period);

    $day_date = "";
    $available_times = array();
    foreach ($period as $date => $day) {
        //echo "begin free times for $date:<br />";
        $ffid_res = findFirstInDay($day,$date);
        foreach($ffid_res as $times) {
            //echo "starting: " . date("h:i:s A",$times['startTime']) . " long: " . $times['duration'] . "<br />";
            $available_times[$date][] = $times;
            //echo "count of times is:" . count($available_times) . "<br />";
        }
        //echo "end free times for $date";
    }

    return $available_times;
}

function findFirstInDay($day,$date) {
    $stack = array();
    $lastcat = 3;
    $intime = false;
    $outtime = false;
    foreach ($day as $event) {
        //echo "event is: " . $event['title'] . " cat is: " .$event['catid'] . " event date is: " . $date . "<br />";

        if ($event['catid'] == 2) { //catid 2 is reserved to represent "In Office" events, id 3 is "Out Of Office"
            $intime = $event['startTime'];
            //echo "setting in: $intime<br />";
        }
        elseif ($event['catid'] == 3) {

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
    $intime_sec = date("U",strtotime($date . " " . $intime));
    $outtime_sec =  date("U",strtotime($date . " " . $outtime));
    $free_time = $intime_sec;

    $times = array();
    for ($i = $intime_sec; $i < $outtime_sec; $i += $inc) {
        //echo "time is now: " . date("h:i:s A",$i) . "<br />";
        $closest_start = $outtime_sec;
        $timeclear = false;
        foreach($day as $event) {
            if ($event['catid'] != 2) {

                $estart = dtSec($date,$event['startTime']) ;
                $eend = dtSecDur($date, $event['startTime'], $event['duration']);

                if    ($eend < $intime_sec or $estart > $outtime_sec) {
                    //event ends before intime or starts after outtime we don't care move on;
                    continue;
                }
                elseif ($eend < $i ) {
                    //event ended before time currently being evaluated, we don't care move on;
                    continue;
                }
                elseif ($estart < $i and $eend > $i) {
                    //event occupies part of the time we are looking at, look at another time
                    continue;
                }
                elseif ($estart >= $i) {
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
                        if ($i < ($eend - $inc))
                            $i = ($eend - $inc);
                    }
                    elseif($newfreetime <= $oldfreetime && $oldfreetime == ($outtime_sec - $i)) {
                        $free_time = $i;
                        $closest_start = $estart;

                        //echo "time is " . date("h:i:s A",$i) . " min free: " . (($closest_start - $i)/60)   . " " . date("h:i:s A",$closest_start) .  "<br />";
                        if ($i < ($eend - $inc))
                            $i = ($eend - $inc);
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
            $date_sec = strtotime ($date);
            if ($duration > 0) {
                $times[] = array ("startTime" => $free_time, "endTime" => ($date_sec+$duration));
            }
        }
    }
    return $times;

}

function dtSec ($date, $time) {
    return date("U",strtotime($date . " " . $time));
}

function dtSecDur ($date, $time, $dur) {
    $time_sec = date("U",strtotime($date . " " . $time));
    return $time_sec + $dur;
}

/**
 *    postcalendar_adminapi_buildSubmitForm()
 *    create event submit form
 */
function postcalendar_adminapi_buildSubmitForm($args) { return postcalendar_userapi_buildSubmitForm($args,true); }
/**
 *    postcalendar_userapi_buildSubmitForm()
 *    create event submit form
 */
function postcalendar_userapi_buildSubmitForm($args,$admin=false)
{
    $_SESSION['category'] = "";
    if(!PC_ACCESS_ADD) { return _POSTCALENDARNOAUTH; }
    extract($args); unset($args);
    //since we seem to clobber category
    $cat = $category;
    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    // set up Smarty
    $tpl = new pcSmarty();
    $tpl->caching = false;

    $template_name = pnModGetVar(__POSTCALENDAR__,'pcTemplate');

    if(!isset($template_name)) {
        $template_name ='default';
    }

    //=================================================================
    //  Setup the correct config file path for the templates
    //=================================================================
    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $modir = pnVarPrepForOS($modinfo['directory']);
    $modname = $modinfo['displayname'];
    $all_categories =& pnModAPIFunc(__POSTCALENDAR__,'user','getCategories');
    //print_r($all_categories);
    unset($modinfo);
    $tpl->config_dir = "modules/$modir/pntemplates/$template_name/config/";
    //=================================================================
    //  PARSE MAIN
    //=================================================================


    $tpl->assign('webroot', $GLOBALS['web_root']);
    $tpl->assign_by_ref('TPL_NAME',$template_name);
    $tpl->assign('FUNCTION',pnVarCleanFromInput('func'));
    $tpl->assign_by_ref('ModuleName', $modname);
    $tpl->assign_by_ref('ModuleDirectory', $modir);
    $tpl->assign_by_ref('category',$all_categories);
    $tpl->assign('NewEventHeader',          _PC_NEW_EVENT_HEADER);
    $tpl->assign('EventTitle',              _PC_EVENT_TITLE);
    $tpl->assign('Required',                _PC_REQUIRED);
    $tpl->assign('DateTimeTitle',           _PC_DATE_TIME);
    $tpl->assign('AlldayEventTitle',        _PC_ALLDAY_EVENT);
    $tpl->assign('TimedEventTitle',         _PC_TIMED_EVENT);
    $tpl->assign('TimedDurationTitle',      _PC_TIMED_DURATION);
    $tpl->assign('TimedDurationHoursTitle', _PC_TIMED_DURATION_HOURS);
    $tpl->assign('TimedDurationMinutesTitle',_PC_TIMED_DURATION_MINUTES);
    $tpl->assign('EventDescTitle',          _PC_EVENT_DESC);

    //the double book variable comes from the eventdata array that is
    //passed here and extracted, injection is not an issue here
    if (is_numeric($double_book)) {
        $tpl->assign('double_book',                $double_book);
    }

    //pennfirm begin patient info handling
    $ProviderID = pnVarCleanFromInput("provider_id");
    if (is_numeric($ProviderID)) {
      $tpl->assign('ProviderID',            $ProviderID);
      $tpl->assign('provider_id',            $ProviderID);
    }
    elseif(is_numeric($event_userid) && $event_userid != 0) {
       $tpl->assign('ProviderID',            $event_userid);
       $tpl->assign('provider_id',            $event_userid);
    }
    else {
        if ($_SESSION['userauthorized'] == 1)
            $tpl->assign('ProviderID',            $_SESSION['authUserID']);
        else
              $tpl->assign('ProviderID',            "");
    }
    $provinfo = getProviderInfo();
    $tpl->assign('providers',                $provinfo);
    $PatientID = pnVarCleanFromInput("patient_id");
    
    // limit the number of results returned by getPatientPID
    // this helps to prevent the server from stalling on a request with
    // no PID and thousands of PIDs in the database -- JRM
    // the function getPatientPID($pid, $given, $orderby, $limit, $start) <-- defined in library/patient.inc
    $plistlimit = 500;
    if (is_numeric($PatientID)) {
        $tpl->assign('PatientList', getPatientPID(array('pid'=>$PatientID, 'limit'=>$plistlimit)));
    }
    elseif (is_numeric($event_pid)) {
        $tpl->assign('PatientList', getPatientPID(array('pid'=>$event_pid, 'limit'=>$plistlimit)));
    }
    else {
        $tpl->assign('PatientList', getPatientPID(array('limit' =>$plistlimit)));
    }

    $tpl->assign('event_pid',            $event_pid);
    $tpl->assign('event_aid',            $event_aid);
    $tpl->assign('event_category', pnVarCleanFromInput("event_category"));

    if(empty($event_patient_name))
    {
        $patient_data = getPatientData($event_pid, $given = "lname, fname");
        $event_patient_name = $patient_data['lname'].", ".$patient_data['fname'];
    }
    $tpl->assign('patient_value', $event_patient_name);

    //=================================================================
    //  PARSE INPUT_EVENT_TITLE
    //=================================================================
    $tpl->assign('InputEventTitle', 'event_subject');
    $tpl->assign('ValueEventTitle', pnVarPrepForDisplay($event_subject));

    //=================================================================
    //  PARSE SELECT_DATE_TIME
    //=================================================================

    // It seems that with Mozilla at least, <select> fields that are disabled
    // do not get passed as form data.  Therefore we ignore $double_book so
    // that the fields will not be disabled.  -- Rod 2005-03-22

    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    if(_SETTING_USE_INT_DATES) {
        $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildDaySelect',array('pc_day'=>$day,'selected'=>$event_startday));
        $formdata = $output->FormSelectMultiple('event_startday', $sel_data,0,1,"","",false,/* $double_book*/ '');
        $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildMonthSelect',array('pc_month'=>$month,'selected'=>$event_startmonth));
        $formdata .= $output->FormSelectMultiple('event_startmonth', $sel_data,0,1,"","",false,/* $double_book*/ '');
    } else {
        $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildMonthSelect',array('pc_month'=>$month,'selected'=>$event_startmonth));
        $formdata = $output->FormSelectMultiple('event_startmonth', $sel_data,0,1,"","",false,/* $double_book*/ '');
        $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildDaySelect',array('pc_day'=>$day,'selected'=>$event_startday));
        $formdata .= $output->FormSelectMultiple('event_startday', $sel_data,0,1,"","",false,/* $double_book*/ '');
    }
    $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildYearSelect',array('pc_year'=>$year,'selected'=>$event_startyear));
    $formdata .= $output->FormSelectMultiple('event_startyear', $sel_data,0,1,"","",false,/* $double_book*/ '');
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $tpl->assign('SelectDateTime', $formdata);
    $tpl->assign('InputAllday', 'event_allday');
    $tpl->assign('ValueAllday', '1');
    $tpl->assign('SelectedAllday', $event_allday==1 ? 'checked':'');
    $tpl->assign('InputTimed', 'event_allday');
    $tpl->assign('ValueTimed', '0');
    $tpl->assign('SelectedTimed', $event_allday==0 ? 'checked':'');
    $tpl->assign('STYLE',$GLOBALS['style']);

    //=================================================================
    //  PARSE SELECT_END_DATE_TIME
    //=================================================================
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    //if there is no end date we want the box to read todays date instead of jan 01 1994 :)
    if($event_endmonth == 0 && $event_endday ==0 && $event_endyear ==0){
        $event_endmonth =$month;
        $event_endday = $day ;
        $event_endyear = $year;
    }
    if(_SETTING_USE_INT_DATES) {
        $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildDaySelect',array('pc_day'=>$day,'selected'=>$event_endday));

        $formdata = $output->FormSelectMultiple('event_endday', $sel_data,0,1,"","",false,/* $double_book*/ '');
        $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildMonthSelect',array('pc_month'=>$month,'selected'=>$event_endmonth));
        $formdata .= $output->FormSelectMultiple('event_endmonth', $sel_data,0,1,"","",false,/* $double_book*/ '');
    } else {

        $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildMonthSelect',array('pc_month'=>$month,'selected'=>$event_endmonth));
        $formdata = $output->FormSelectMultiple('event_endmonth', $sel_data,0,1,"","",false,/* $double_book*/ '');
        $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildDaySelect',array('pc_day'=>$day,'selected'=>$event_endday));
        $formdata .= $output->FormSelectMultiple('event_endday', $sel_data,0,1,"","",false,/* $double_book*/ '');
    }
    $sel_data = pnModAPIFunc(__POSTCALENDAR__,'user','buildYearSelect',array('pc_year'=>$year,'selected'=>$event_endyear));
    $formdata .= $output->FormSelectMultiple('event_endyear', $sel_data,0,1,"","",false,/* $double_book*/ '');
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $tpl->assign('SelectEndDate', $formdata);
    //=================================================================
    //  PARSE SELECT_TIMED_EVENT
    //=================================================================
    $stimes = pnModAPIFunc(__POSTCALENDAR__,'user','buildTimeSelect',array('hselected'=>$event_starttimeh,'mselected'=>$event_starttimem));
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $timed_hours = $output->FormSelectMultiple('event_starttimeh', $stimes['h'],0,1,"","",false,/* $double_book*/ '');
    $timed_minutes = $output->FormSelectMultiple('event_starttimem', $stimes['m'],0,1,"","",false,/* $double_book*/ '');
    if(!_SETTING_TIME_24HOUR) {
        $ampm = array();
        $ampm[0]['id']          = pnVarPrepForStore(_AM_VAL);
        $ampm[0]['name']        = pnVarPrepForDisplay(_PC_AM);
        $ampm[1]['id']          = pnVarPrepForStore(_PM_VAL);
        $ampm[1]['name']        = pnVarPrepForDisplay(_PC_PM);
    if($event_startampm == "AM" || $event_startampm == _AM_VAL)
    {
            $ampm[0]['selected'] = 1;
    }
    else
    {
            $ampm[1]['selected'] = 1;
    }
        $timed_ampm = $output->FormSelectMultiple('event_startampm', $ampm,0,1,"","",false,/* $double_book*/ '');
    } else {
        $timed_ampm = '';
    }
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $tpl->assign('SelectTimedHours', $timed_hours);
    $tpl->assign('SelectTimedMinutes', $timed_minutes);
    $tpl->assign('SelectTimedAMPM', $timed_ampm);
    $tpl->assign('event_startday', $event_startday);
    $tpl->assign('event_startmonth', $event_startmonth);
    $tpl->assign('event_startyear', $event_startyear);
    $tpl->assign('event_starttimeh', $event_starttimeh);
    $tpl->assign('event_starttimem', $event_starttimem);
    $tpl->assign('event_startampm', $event_startampm);
    $tpl->assign('event_dur_hours', $event_dur_hours);
    $tpl->assign('event_dur_minutes', $event_dur_minutes);

    //=================================================================
    //  PARSE SELECT_DURATION
    //=================================================================
    $event_dur_hours = (int) $event_dur_hours;

    for($i=0; $i<=24; $i+=1) {
        $TimedDurationHours[$i] = array('value'=>$i,
                                        'selected'=>($event_dur_hours==$i ? 'selected':''),
                                        'name'=>sprintf('%02d',$i));
    }

    $tpl->assign('TimedDurationHours',$TimedDurationHours);
    $tpl->assign('InputTimedDurationHours', 'event_dur_hours');
    $found_time = false;
    for($i=0; $i<60; $i+=_SETTING_TIME_INCREMENT) {
        $TimedDurationMinutes[$i] = array('value'=>$i,
                                          'selected'=>($event_dur_minutes==$i ? 'selected':''),
                                          'name'=>sprintf('%02d',$i));
         if( $TimedDurationMinutes[$i]['selected'] == 'selected' )
             $found_time = true;
    }

    if(!$found_time)
        $TimedDurationMinutes[$i] = array('value'=>$event_dur_minutes,
                                            'selected'=>'selected',
                                            'name'=>sprintf('%02d',$event_dur_minutes));
    $tpl->assign('TimedDurationMinutes',$TimedDurationMinutes);
    $tpl->assign('hidden_event_dur_minutes',$event_dur_minutes);
    $tpl->assign('InputTimedDurationMinutes', 'event_dur_minutes');
    //=================================================================
    //  PARSE INPUT_EVENT_DESC
    //=================================================================
    $tpl->assign('InputEventDesc', 'event_desc');
    if(empty($pc_html_or_text)) {
        $display_type = substr($event_desc,0,6);
        if($display_type == ':text:') {
            $pc_html_or_text = 'text';
            $event_desc = substr($event_desc,6);
        } elseif($display_type == ':html:') {
            $pc_html_or_text = 'html';
            $event_desc = substr($event_desc,6);
        } else {
            $pc_html_or_text = 'text';
        }
        unset($display_type);
    }
    $tpl->assign('ValueEventDesc', pnVarPrepForDisplay($event_desc));
    $eventHTMLorText  = "<select name=\"pc_html_or_text\">";
    if($pc_html_or_text == 'text') {
        $eventHTMLorText .= "<option value=\"text\" selected=\"selected\">"._PC_SUBMIT_TEXT."</option>";
    } else {
        $eventHTMLorText .= "<option value=\"text\">"._PC_SUBMIT_TEXT."</option>";
    }
    if($pc_html_or_text == 'html') {
        $eventHTMLorText .= "<option value=\"html\" selected=\"selected\">"._PC_SUBMIT_HTML."</option>";
    } else {
        $eventHTMLorText .= "<option value=\"html\">"._PC_SUBMIT_HTML."</option>";
    }
    $eventHTMLorText .= "</select>";
    $tpl->assign('EventHTMLorText',$eventHTMLorText);
    //=================================================================
    //  PARSE select_event_topic_block
    //=================================================================
    $tpl->assign('displayTopics',_SETTING_DISPLAY_TOPICS);
    if((bool)_SETTING_DISPLAY_TOPICS) {
        $a_topics =& postcalendar_userapi_getTopics();
        $topics = array();
        foreach($a_topics as $topic) {
            array_push($topics,array('value'=>$topic['id'],
                                     'selected'=>($topic['id']==$event_topic ? 'selected':''),
                                     'name'=>$topic['text']));
        }
        unset($a_topics);
        // only show this if we have topics to show
        if(count($topics) > 0) {
            $tpl->assign('topics',$topics);
            $tpl->assign('EventTopicTitle', _PC_EVENT_TOPIC);
            $tpl->assign('InputEventTopic', 'event_topic');
        }
    }
    //=================================================================
    //  PARSE select_event_type_block
    //=================================================================
    $categories = array();
    foreach($all_categories as $category) {
        array_push($categories,array('value'=>$category['id'],
                                     'selected'=>($category['id']==$event_category ? 'selected' : ''),
                                     'name'=>$category['name'],
                                     'color'=>$category['color'],
                                     'desc'=>$category['desc']));
    }
    // only show this if we have categories to show
    // you should ALWAYS have at least one valid category
    if(count($categories) > 0) {
        $tpl->assign('categories',$categories);
        $tpl->assign('EventCategoriesTitle', _PC_EVENT_CATEGORY);
        $tpl->assign('InputEventCategory', 'event_category');
        $tpl->assign('hidden_event_category', $event_category);
    }
    //=================================================================
    //  PARSE event_sharing_block
    //=================================================================
    $data = array();
    if(_SETTING_ALLOW_USER_CAL) {
        array_push($data,array(SHARING_PRIVATE,_PC_SHARE_PRIVATE));
        array_push($data,array(SHARING_PUBLIC,_PC_SHARE_PUBLIC));
        array_push($data,array(SHARING_BUSY,_PC_SHARE_SHOWBUSY));
    }
    if(pnSecAuthAction(0,'PostCalendar::', '::', ACCESS_ADMIN) || _SETTING_ALLOW_GLOBAL || !_SETTING_ALLOW_USER_CAL) {
        array_push($data,array(SHARING_GLOBAL,_PC_SHARE_GLOBAL));
    }
    $sharing = array();
    foreach($data as $cell) {
        array_push($sharing,array('value'=>$cell[0],
                                  'selected'=>((int) $event_sharing == $cell[0] ? 'selected' : ''),
                                  'name'=>$cell[1]));
    }

    //pennfirm get list of providers from openemr code in calendar.inc
    $tpl->assign("user",getCalendarProviderInfo());


    $tpl->assign('sharing',$sharing);
    $tpl->assign('EventSharingTitle', _PC_SHARING);
    $tpl->assign('InputEventSharing','event_sharing');
    //=================================================================
    //  location information
    //=================================================================
    $tpl->assign('EventLocationTitle',  _PC_EVENT_LOCATION);
    $tpl->assign('InputLocation',       'event_location');
    $tpl->assign('ValueLocation',       pnVarPrepForDisplay($event_location));
    $tpl->assign('EventStreetTitle',    _PC_EVENT_STREET);
    $tpl->assign('InputStreet1',        'event_street1');
    $tpl->assign('ValueStreet1',        pnVarPrepForDisplay($event_street1));
    $tpl->assign('InputStreet2',        'event_street2');
    $tpl->assign('ValueStreet2',        pnVarPrepForDisplay($event_street2));
    $tpl->assign('EventCityTitle',      _PC_EVENT_CITY);
    $tpl->assign('InputCity',           'event_city');
    $tpl->assign('ValueCity',           pnVarPrepForDisplay($event_city));
    $tpl->assign('EventStateTitle',     _PC_EVENT_STATE);
    $tpl->assign('InputState',          'event_state');
    $tpl->assign('ValueState',          pnVarPrepForDisplay($event_state));
    $tpl->assign('EventPostalTitle',    _PC_EVENT_POSTAL);
    $tpl->assign('InputPostal',         'event_postal');
    $tpl->assign('ValuePostal',         pnVarPrepForDisplay($event_postal));
    //=================================================================
    //  contact information
    //=================================================================
    $tpl->assign('EventContactTitle',   _PC_EVENT_CONTACT);
    $tpl->assign('InputContact',        'event_contname');
    $tpl->assign('ValueContact',        pnVarPrepForDisplay($event_contname));
    $tpl->assign('EventPhoneTitle',     _PC_EVENT_PHONE);
    $tpl->assign('InputPhone',          'event_conttel');
    $tpl->assign('ValuePhone',          pnVarPrepForDisplay($event_conttel));
    $tpl->assign('EventEmailTitle',     _PC_EVENT_EMAIL);
    $tpl->assign('InputEmail',          'event_contemail');
    $tpl->assign('ValueEmail',          pnVarPrepForDisplay($event_contemail));
    $tpl->assign('EventWebsiteTitle',   _PC_EVENT_WEBSITE);
    $tpl->assign('InputWebsite',        'event_website');
    $tpl->assign('ValueWebsite',        pnVarPrepForDisplay($event_website));
    $tpl->assign('EventFeeTitle',       _PC_EVENT_FEE);
    $tpl->assign('InputFee',            'event_fee');
    $tpl->assign('ValueFee',            pnVarPrepForDisplay($event_fee));
    //=================================================================
    //  Repeating Information
    //=================================================================
    $tpl->assign('RepeatingHeader',     _PC_REPEATING_HEADER);
    $tpl->assign('NoRepeatTitle',       _PC_NO_REPEAT);
    $tpl->assign('RepeatTitle',         _PC_REPEAT);
    $tpl->assign('RepeatOnTitle',       _PC_REPEAT_ON);
    $tpl->assign('OfTheMonthTitle',     _PC_OF_THE_MONTH);
    $tpl->assign('EndDateTitle',        _PC_END_DATE);
    $tpl->assign('NoEndDateTitle',      _PC_NO_END);
    $tpl->assign('InputNoRepeat', 'event_repeat');
    $tpl->assign('ValueNoRepeat', '0');
    $tpl->assign('SelectedNoRepeat', (int) $event_repeat==0 ? 'checked':'');
    $tpl->assign('InputRepeat', 'event_repeat');
    $tpl->assign('ValueRepeat', '1');
    $tpl->assign('SelectedRepeat', (int) $event_repeat==1 ? 'checked':'');

    unset($in);
    $in = array(_PC_EVERY,_PC_EVERY_OTHER,_PC_EVERY_THIRD,_PC_EVERY_FOURTH);
    $keys = array(REPEAT_EVERY,REPEAT_EVERY_OTHER,REPEAT_EVERY_THIRD,REPEAT_EVERY_FOURTH);
    $repeat_freq = array();
    foreach($in as $k=>$v) {
        array_push($repeat_freq,array('value'=>$keys[$k],
                                      'selected'=>($keys[$k]==$event_repeat_freq?'selected':''),
                                      'name'=>$v));
    }
    $tpl->assign('InputRepeatFreq','event_repeat_freq');
    if(empty($event_repeat_freq) || $event_repeat_freq < 1) $event_repeat_freq = 1;
    $tpl->assign('InputRepeatFreqVal',$event_repeat_freq);
    $tpl->assign('repeat_freq',$repeat_freq);
    unset($in);
    $in = array(_PC_EVERY_DAY,_PC_EVERY_WORKDAY,_PC_EVERY_WEEK,_PC_EVERY_MONTH,_PC_EVERY_YEAR);
    $keys = array(REPEAT_EVERY_DAY,REPEAT_EVERY_WORK_DAY,REPEAT_EVERY_WEEK,REPEAT_EVERY_MONTH,REPEAT_EVERY_YEAR);
    $repeat_freq_type = array();
    foreach($in as $k=>$v) {
        array_push($repeat_freq_type,array('value'=>$keys[$k],
                                           'selected'=>($keys[$k]==$event_repeat_freq_type?'selected':''),
                                           'name'=>$v));
    }
    $tpl->assign('InputRepeatFreqType','event_repeat_freq_type');
    $tpl->assign('repeat_freq_type',$repeat_freq_type);

    $tpl->assign('InputRepeatOn', 'event_repeat');
    $tpl->assign('ValueRepeatOn', '2');
    $tpl->assign('SelectedRepeatOn', (int) $event_repeat==2 ? 'checked':'');

    unset($in);
    $in = array(_PC_EVERY_1ST,_PC_EVERY_2ND,_PC_EVERY_3RD,_PC_EVERY_4TH,_PC_EVERY_LAST);
    $keys = array(REPEAT_ON_1ST,REPEAT_ON_2ND,REPEAT_ON_3RD,REPEAT_ON_4TH,REPEAT_ON_LAST);
    $repeat_on_num = array();
    foreach($in as $k=>$v) {
        array_push($repeat_on_num,array('value'=>$keys[$k],
                                        'selected'=>($keys[$k]==$event_repeat_on_num?'selected':''),
                                        'name'=>$v));
    }
    $tpl->assign('InputRepeatOnNum', 'event_repeat_on_num');
    $tpl->assign('repeat_on_num',$repeat_on_num);

    unset($in);
    $in = array(_PC_EVERY_SUN,_PC_EVERY_MON,_PC_EVERY_TUE,_PC_EVERY_WED,_PC_EVERY_THU,_PC_EVERY_FRI,_PC_EVERY_SAT);
    $keys = array(REPEAT_ON_SUN,REPEAT_ON_MON,REPEAT_ON_TUE,REPEAT_ON_WED,REPEAT_ON_THU,REPEAT_ON_FRI,REPEAT_ON_SAT);
    $repeat_on_day = array();
    foreach($in as $k=>$v) {
        array_push($repeat_on_day,array('value'=>$keys[$k],
                                        'selected'=>($keys[$k]==$event_repeat_on_day ? 'selected' : ''),
                                        'name'=>$v));
    }
    $tpl->assign('InputRepeatOnDay', 'event_repeat_on_day');
    $tpl->assign('repeat_on_day',$repeat_on_day);

    unset($in);
    $in = array(_PC_OF_EVERY_MONTH,_PC_OF_EVERY_2MONTH,_PC_OF_EVERY_3MONTH,_PC_OF_EVERY_4MONTH,_PC_OF_EVERY_6MONTH,_PC_OF_EVERY_YEAR);
    $keys = array(REPEAT_ON_MONTH,REPEAT_ON_2MONTH,REPEAT_ON_3MONTH,REPEAT_ON_4MONTH,REPEAT_ON_6MONTH,REPEAT_ON_YEAR);
    $repeat_on_freq = array();
    foreach($in as $k=>$v) {
        array_push($repeat_on_freq,array('value'=>$keys[$k],
                                         'selected'=>($keys[$k] == $event_repeat_on_freq ? 'selected' : ''),
                                         'name'=>$v));
    }
    $tpl->assign('InputRepeatOnFreq', 'event_repeat_on_freq');
    if(empty($event_repeat_on_freq) || $event_repeat_on_freq < 1) $event_repeat_on_freq = 1;
    $tpl->assign('InputRepeatOnFreqVal', $event_repeat_on_freq);
    $tpl->assign('repeat_on_freq',$repeat_on_freq);
    $tpl->assign('MonthsTitle',_PC_MONTHS);

    //=================================================================
    //  PARSE INPUT_END_DATE
    //=================================================================
    $tpl->assign('InputEndOn', 'event_endtype');
    $tpl->assign('ValueEndOn', '1');
    $tpl->assign('SelectedEndOn', (int) $event_endtype==1 ? 'checked':'');
    //=================================================================
    //  PARSE INPUT_NO_END
    //=================================================================
    $tpl->assign('InputNoEnd', 'event_endtype');
    $tpl->assign('ValueNoEnd', '0');
    $tpl->assign('SelectedNoEnd', (int) $event_endtype==0 ? 'checked':'');
    $qstring = preg_replace("/provider_id=[0-9]*[&]{0,1}/","",$_SERVER['QUERY_STRING']);
    $tpl->assign('qstring',        $qstring);

    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $authkey = $output->FormHidden('authid',pnSecGenAuthKey());
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $form_hidden = "<input type=\"hidden\" name=\"is_update\" value=\"$is_update\" />";
    $form_hidden .= "<input type=\"hidden\" name=\"pc_event_id\" value=\"$pc_event_id\" />";
    $form_hidden .= "<input type=\"hidden\" name=\"category\" value=\"$cat\" />";
    if(isset($data_loaded)) {
        $form_hidden .= "<input type=\"hidden\" name=\"data_loaded\" value=\"$data_loaded\" />";
        $tpl->assign('FormHidden',$form_hidden);
    }
    $form_submit = '<input type=hidden name="form_action" value="commit"/>
                   '.$authkey.'<input type="submit" name="submit" value="go">' ;
    $tpl->assign('FormSubmit',$form_submit);

    // do not cache this page
    if($admin) {
        $output->Text($tpl->fetch($template_name.'/admin/submit.html'));
    }
    //check flag no_nav, if true use much smaller submit form for find_patient.php, etc
    elseif (pnVarCleanFromInput("no_nav") == 1) {
        $output->Text($tpl->fetch($template_name.'/user/submit_no_nav.html'));
    } else {
        $output->Text($tpl->fetch($template_name.'/user/submit.html'));
    }
    $output->Text(postcalendar_footer());
    return $output->GetOutput();
}

function &postcalendar_userapi_pcGetEventDetails($eid)
{

    if(!isset($eid)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // link to the events tables
    $table      =  $pntable['postcalendar_events'];
    $cattable   =  $pntable['postcalendar_categories'];

    $sql =  "SELECT DISTINCT e.pc_eid,
                    e.pc_informant,
                    e.pc_catid,
                    e.pc_title,
                    e.pc_time,
                    e.pc_hometext,
                    e.pc_eventDate,
                    e.pc_duration,
                    e.pc_endDate,
                    e.pc_startTime,
                    e.pc_recurrtype,
                    e.pc_recurrfreq,
                    e.pc_recurrspec,
                    e.pc_topic,
                    e.pc_alldayevent,
                    e.pc_location,
                    e.pc_conttel,
                    e.pc_contname,
                    e.pc_contemail,
                    e.pc_website,
                    e.pc_fee,
                    e.pc_sharing,
                    c.pc_catcolor,
                    c.pc_catname,
                    c.pc_catdesc,
                    e.pc_pid,
                    e.pc_aid,
                    pd.pubpid
            FROM   ($table e, $cattable c)
            LEFT JOIN patient_data as pd ON (pd.pid = e.pc_pid)
            WHERE  (e.pc_eid = '$eid' AND c.pc_catid = e.pc_catid)";

    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) {
        die ($dbconn->ErrorMsg());
    }
    $event = array();
    if(!isset($result)) {
      return $event;
    }
    list($event['eid'],        $event['uname'],       $event['catid'],
         $event['title'],      $event['time'],        $event['hometext'],
         $event['eventDate'],  $event['duration'],    $event['endDate'],
         $event['startTime'],  $event['recurrtype'],  $event['recurrfreq'],
         $event['recurrspec'], $event['topic'],       $event['alldayevent'],
         $event['location'],   $event['conttel'],     $event['contname'],
         $event['contemail'],  $event['website'],     $event['fee'], $event['sharing'],
         $event['catcolor'],   $event['catname'],     $event['catdesc'], $event['pid'], $event['aid'],$event['pubpid']) = $result->fields;
    // there has to be a more intelligent way to do this
    @list($event['duration_hours'],$dmin) = @explode('.',($event['duration']/60/60));
    $event['duration_minutes'] = substr(sprintf('%.2f','.' . 60*($dmin/100)),2,2);
    //''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    $result->Close();
    //pennfirm fix to reflect openemr user/informant
    $userid = pnUserGetVar('uid');

    // get the user id of event's author
    $users = pnUserGetAll();
    foreach($users as $user) {
        if($user['uname'] == $event['uname']) {
            $cuserid = $user['uid'];
            break;
        }
    }
    unset($users);

    // is this a public event to be shown as busy?
    if($event['sharing'] == SHARING_PRIVATE && $cuserid != $userid) {
        // they are not supposed to see this
        return false;
    } elseif($event['sharing'] == SHARING_BUSY && $cuserid != $userid) {
        // make it not display any information
        $event['title']     = _USER_BUSY_TITLE;
        $event['hometext']  = _USER_BUSY_MESSAGE;
        $event['location']  = '';
        $event['conttel']   = '';
        $event['contname']  = '';
        $event['contemail'] = '';
        $event['website']   = '';
        $event['fee']       = '';
    } else {
        $event['title']     = $event['title'];
        $event['hometext']  = $event['hometext'];
        $event['location']  = $event['location'];
        $event['conttel']   = $event['conttel'];
        $event['contname']  = $event['contname'];
        $event['contemail'] = $event['contemail'];
        $event['website']   = $event['website'];
        $event['fee']       = $event['fee'];
    }
    $event['desc'] = $event['hometext'];
    $event['website'] = $event['website'];
    if (!empty($event['pid']))
      $event['patient_name'] = getPatientName($event['pid']);
    if (empty($event['aid']))
      $event['aid']= -1;

    return $event;
}

/**
 *  postcalendar_userapi_eventDetail
 *  Creates the detailed event display and outputs html.
 *  Accepts an array of key/value pairs
 *  @param int $eid the id of the event to display
 *  @return string html output
 *  @access public
 */
function postcalendar_adminapi_eventDetail($args) { return postcalendar_userapi_eventDetail($args,true); }
function postcalendar_userapi_eventDetail($args,$admin=false)
{
    if(!(bool)PC_ACCESS_READ) { return _POSTCALENDARNOAUTH; }
    // get the theme globals :: is there a better way to do this?
    pnThemeLoad(pnUserGetTheme());
    global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5;
    global $textcolor1, $textcolor2;
    $popup = pnVarCleanFromInput('popup');
    extract($args); unset($args);
    if(!isset($cacheid)) $cacheid = null;
    if(!isset($eid)) {
        return false;
    }
    if(!isset($nopop)) {
        $nopop = false;
    }

    $uid = pnUserGetVar('uid');
    //=================================================================
    //  Find out what Template we're using
    //=================================================================
    $template_name = _SETTING_TEMPLATE;
    if(!isset($template_name)) {
        $template_name = 'default';
    }
    //=================================================================
    //  Setup Smarty Template Engine
    //=================================================================
    $tpl = new pcSmarty();

    if($admin) {
        $template = $template_name.'/admin/details.html';
        $args['cacheid'] = '';
        $print=0;
        $Date =& postcalendar_getDate();
        $tpl->caching = false;
    } else {
        $template = $template_name.'/user/details.html';
    }

    if(!$tpl->is_cached($template,$cacheid)) {
        // let's get the DB information
        list($dbconn) = pnDBGetConn();
        $pntable = pnDBGetTables();
        // get the event's information
        $event =& postcalendar_userapi_pcGetEventDetails($eid);
        // if the above is false, it's a private event for another user
        // we should not diplay this - so we just exit gracefully
        if($event === false) { return false; }
        //=================================================================
        //  get event's topic information
        //=================================================================
        $topics_table = $pntable['topics'];
        $topics_column = $pntable['topics_column'];
        $topicsql = "SELECT $topics_column[topictext],$topics_column[topicimage]
                     FROM $topics_table
                     WHERE $topics_column[topicid] = $event[topic]
                     LIMIT 1";
        $topic_result = $dbconn->Execute($topicsql);
        list($event['topictext'],$event['topicimg']) = $topic_result->fields;
        $location = unserialize($event['location']);
        $event['location'] = $location['event_location'];
        $event['street1']  = $location['event_street1'];
        $event['street2']  = $location['event_street2'];
        $event['city']     = $location['event_city'];
        $event['state']    = $location['event_state'];
        $event['postal']   = $location['event_postal'];
        $event['date']     = str_replace('-','',$Date);
        //=================================================================
        //  populate the template
        //=================================================================
        if(!empty($event['location']) || !empty($event['street1']) ||
           !empty($event['street2']) || !empty($event['city']) ||
           !empty($event['state']) || !empty($event['postal'])) {
           $tpl->assign('LOCATION_INFO',true);
        } else {
            $tpl->assign('LOCATION_INFO',false);
        }
        if(!empty($event['contname']) || !empty($event['contemail']) ||
           !empty($event['conttel']) || !empty($event['website'])) {
           $tpl->assign('CONTACT_INFO',true);
        } else {
            $tpl->assign('CONTACT_INFO',false);
        }
        $display_type = substr($event['hometext'],0,6);
        if($display_type == ':text:') {
            $prepFunction = 'pcVarPrepForDisplay';
            $event['hometext'] = substr($event['hometext'],6);
        } elseif($display_type == ':html:') {
            $prepFunction = 'pcVarPrepHTMLDisplay';
            $event['hometext'] = substr($event['hometext'],6);
        } else {
            $prepFunction = 'pcVarPrepHTMLDisplay';
        }
        unset($display_type);
        // prep the vars for output
        $event['title'] =& $prepFunction($event['title']);
        $event['hometext'] =& $prepFunction($event['hometext']);
        $event['desc'] =& $event['hometext'];
        $event['conttel'] =& $prepFunction($event['conttel']);
        $event['contname'] =& $prepFunction($event['contname']);
        $event['contemail'] =& $prepFunction($event['contemail']);
        $event['website'] =& $prepFunction(postcalendar_makeValidURL($event['website']));
        $event['fee'] =& $prepFunction($event['fee']);
        $event['location'] =& $prepFunction($event['location']);
        $event['street1'] =& $prepFunction($event['street1']);
        $event['street2'] =& $prepFunction($event['street2']);
        $event['city'] =& $prepFunction($event['city']);
        $event['state'] =& $prepFunction($event['state']);
        $event['postal'] =& $prepFunction($event['postal']);

        $tpl->assign_by_ref('A_EVENT',$event);
        //=================================================================
        //  populate the template $ADMIN_OPTIONS
        //=================================================================
        $target='';
        if(_SETTING_OPEN_NEW_WINDOW) {
            $target = 'target="csCalendar"';
        }

        $admin_edit_url = $admin_delete_url = '';
        if (pnSecAuthAction(0, 'PostCalendar::', '::', ACCESS_ADMIN)) {
            $admin_edit_url     = pnModURL(__POSTCALENDAR__,'admin','submit',array('pc_event_id'=>$eid));
            $admin_delete_url   = pnModURL(__POSTCALENDAR__,'admin','adminevents',array('action'=>_ACTION_DELETE,'pc_event_id'=>$eid));
        }
        $user_edit_url = $user_delete_url = '';
        if(pnUserLoggedIn()) {
            $logged_in_uname = $_SESSION['authUser'];
        } else {
            $logged_in_uname = '';
        }


        $can_edit = false;
        if (pnSecAuthAction(0, 'PostCalendar::', '::', ACCESS_ADD) && validateGroupStatus($logged_in_uname,getUsername($event['uname']))) {

            $user_edit_url     = pnModURL(__POSTCALENDAR__,'user','submit',array('pc_event_id'=>$eid));
            $user_delete_url   = pnModURL(__POSTCALENDAR__,'user','delete',array('pc_event_id'=>$eid));
            $can_edit = true;
        }
        $tpl->assign('STYLE',$GLOBALS['style']);
        $tpl->assign_by_ref('ADMIN_TARGET',$target);
        $tpl->assign_by_ref('ADMIN_EDIT',$admin_edit_url);
        $tpl->assign_by_ref('ADMIN_DELETE',$admin_delete_url);
        $tpl->assign_by_ref('USER_TARGET',$target);
        $tpl->assign_by_ref('USER_EDIT',$user_edit_url);
        $tpl->assign_by_ref('USER_DELETE',$user_delete_url);
        $tpl->assign_by_ref('USER_CAN_EDIT',$can_edit);
    }
    //=================================================================
    //  Parse the template
    //=================================================================
    if($popup != 1 && $print != 1) {
        $output  = "\n\n<!-- START POSTCALENDAR OUTPUT [-: HTTP://POSTCALENDAR.TV :-] -->\n\n";
        $output .= $tpl->fetch($template,$cacheid);
        $output .= "\n\n<!-- END POSTCALENDAR OUTPUT [-: HTTP://POSTCALENDAR.TV :-] -->\n\n";
    } else {
        $theme = pnUserGetTheme();
        echo "<html><head>";
        echo "<LINK REL=\"StyleSheet\" HREF=\"themes/$theme/style/styleNN.css\" TYPE=\"text/css\">\n\n\n";
        echo "<style type=\"text/css\">\n";
        echo "@import url(\"themes/$theme/style/style.css\"); ";
        echo "</style>\n";
        echo "</head><body>\n";
        $tpl->display($template,$cacheid);
        echo postcalendar_footer();
        echo "\n</body></html>";
        session_write_close();
        exit;
    }

    return $output;
}

function postcalendar_footer()
{
    // lets get the module's information
    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    //$footer = "<p align=\"right\"><a href=\"http://www.postcalendar.tv\">PostCalendar v$modinfo[version]</a></p>";
    $footer = "";
    return $footer;
}

function postcalendar_smarty_pc_sort_day($params, &$smarty)
{
    extract($params);

      if (empty($var)) {
        $smarty->trigger_error("sort_array: missing 'var' parameter");
        return;
    }

    if (!in_array('value', array_keys($params))) {
        $smarty->trigger_error("sort_array: missing 'value' parameter");
        return;
    }

    if (!in_array('order', array_keys($params))) {
        $order = 'asc';
    }

    if (!in_array('inc', array_keys($params))) {
        $inc = '15';
    }

    if (!in_array('start', array_keys($params))) {
        $sh = '08';
        $sm = '00';
    } else {
        list($sh,$sm) = explode(':',$start);
    }

    if (!in_array('end', array_keys($params))) {
        $eh = '21';
        $em = '00';
    } else {
        list($eh,$em) = explode(':',$end);
    }

    if(strtolower($order) == 'asc') $function = 'sort_byTimeA';
    if(strtolower($order) == 'desc') $function = 'sort_byTimeD';

    foreach($value as $events) {
        usort($events,$function);
        $newArray = $events;
    }

    // here we want to create an intelligent array of
    // columns and rows to build a nice day view
    $ch = $sh; $cm = $sm;
    while("$ch:$cm" <= "$eh:$em") {
        $hours["$ch:$cm"] = array();
        $cm += $inc;
        if($cm >= 60) {
            $cm = '00';
            $ch = sprintf('%02d',$ch+1);
        }
    }

    $alldayevents = array();
    foreach($newArray as $event) {
        list($sh,$sm,$ss) = explode(':',$event['startTime']);
        $eh = sprintf('%02d',$sh + $event['duration_hours']);
        $em = sprintf('%02d',$sm + $event['duration_minutes']);

        if($event['alldayevent']) {
            // we need an entire column . save till later
            $alldayevents[] = $event;
        } else {
            //find open time slots - avoid overlapping
            $needed = array();
            $ch = $sh; $cm = $sm;
            //what times do we need?
            while("$ch:$cm" < "$eh:$em") {
                $needed[] = "$ch:$cm";
                $cm += $inc;
                if($cm >= 60) {
                    $cm = '00';
                    $ch = sprintf('%02d',$ch+1);
                }
            }
            $i = 0;
            foreach($needed as $time) {
                if($i==0) {
                    $hours[$time][] = $event;
                    $key = count($hours[$time])-1;
                } else {
                    $hours[$time][$key] = 'continued';
                }
                $i++;
            }
        }
    }
    //pcDebugVar($hours);
    $smarty->assign_by_ref($var,$hours);
}

function sort_byCategoryA($a,$b) {
    if($a['catname'] < $b['catname']) return -1;
    elseif($a['catname'] > $b['catname']) return 1;
}
function sort_byCategoryD($a,$b) {
    if($a['catname'] < $b['catname']) return 1;
    elseif($a['catname'] > $b['catname']) return -1;
}
function sort_byTitleA($a,$b) {
    if($a['title'] < $b['title']) return -1;
    elseif($a['title'] > $b['title']) return 1;
}
function sort_byTitleD($a,$b) {
    if($a['title'] < $b['title']) return 1;
    elseif($a['title'] > $b['title']) return -1;
}
function sort_byTimeA($a,$b) {
    if($a['startTime'] < $b['startTime']) return -1;
    elseif($a['startTime'] > $b['startTime']) return 1;
}
function sort_byTimeD($a,$b) {
    if($a['startTime'] < $b['startTime']) return 1;
    elseif($a['startTime'] > $b['startTime']) return -1;
}
/**
 *    pc_clean
 *    @param s string text to clean
 *    @return string cleaned up text
 */
function pc_clean($s)
{
    $display_type = substr($s,0,6);
    if($display_type == ':text:') {
        $s = substr($s,6);
    } elseif($display_type == ':html:') {
        $s = substr($s,6);
    }
    unset($display_type);
    $s = preg_replace('/[\r|\n]/i','',$s);
    $s = str_replace("'","\'",$s);
    $s = str_replace('"','&quot;',$s);
    // ok, now we need to break really long lines
    // we only want to break at spaces to allow for
    // correct interpretation of special characters
    $tmp = explode(' ',$s);
    return join("'+' ",$tmp);
}

function &postcalendar_adminapi_getCategoryLimits() { return postcalendar_userapi_getCategoryLimits(); }
function &postcalendar_userapi_getCategoryLimits()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $cat_table = $pntable['postcalendar_limits'];
    $sql = "SELECT pc_limitid,pc_catid,pc_starttime,pc_endtime,
            pc_limit FROM $cat_table
            ORDER BY pc_limitid";
    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

    $limits = array();
    for($i=0; !$result->EOF; $result->MoveNext()) {
        list($limitid,$catid,$startTime,$endTime,$limit) = $result->fields;
        // check the category's permissions
        if (!pnSecAuthAction(0,'PostCalendar::Category',"$catname::$catid",ACCESS_OVERVIEW)) {
            continue;
        }
        $limits[$i]['limitid']  = $limitid;
        $limits[$i]['catid']       = $catid;
        $limits[$i]['startTime']= $startTime;
        $limits[$i]['endTime']    = $endTime;
        $limits[$i++]['limit']    = $limit;
     }
    $result->Close();
    return $limits;
}

?>
