<?php
@define('__POSTCALENDAR__','PostCalendar');
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

//=========================================================================
//  Require utility classes
//=========================================================================
require_once($GLOBALS['fileroot']."/library/patient.inc");
$pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
$pcDir = pnVarPrepForOS($pcModInfo['directory']);
require_once("modules/$pcDir/common.api.php");
unset($pcModInfo,$pcDir);

function postcalendar_userapi_getLongDayName($args)
{
    extract($args); unset($args);
    if(!isset($Date)) { return false; }
    $pc_long_day = array(_CALLONGFIRSTDAY,
                         _CALLONGSECONDDAY,
                         _CALLONGTHIRDDAY,
                         _CALLONGFOURTHDAY,
                         _CALLONGFIFTHDAY,
                         _CALLONGSIXTHDAY,
                         _CALLONGSEVENTHDAY);
    return $pc_long_day[Date("w",$Date)];
}

/**
 *  postcalendar_userapi_buildView
 *
 *  Builds the calendar display
 *  @param string $Date mm/dd/yyyy format (we should use timestamps)
 *  @return string generated html output
 *  @access public
 */
function postcalendar_userapi_buildView($args)
{
	$print = pnVarCleanFromInput('print');
	$show_days = pnVarCleanFromInput('show_days');
	extract($args); unset($args);
	$schedule_start = $GLOBALS[schedule_start];
	$schedule_end = $GLOBALS[schedule_end];

	// $times is an array of associative arrays, where each sub-array
	// has keys 'hour', 'minute' and 'mer'.
	//
	$times = array();

	// For each hour in the schedule...
	//
	for($blocknum = $schedule_start; $blocknum <= $schedule_end; $blocknum++){
		$mer = ($blocknum >= 12) ? 'pm' : 'am';

		// $minute is an array of time slot strings within this hour.
		$minute = array('00');

		for($minutes = $GLOBALS['calendar_interval']; $minutes <= 60; $minutes += $GLOBALS['calendar_interval']) {
			if($minutes <= '9'){
			$under_ten = "0" . $minutes;
			array_push($minute, "$under_ten");
			}
			else if($minutes >= '60') {
				break;
			}
			else {
				array_push($minute, "$minutes");
			}
		}

		foreach($minute as $m ){
			array_push($times, array("hour"=>$blocknum, "minute"=>$m, "mer"=>$mer));
		}
	}

	//=================================================================
	//  get the module's information
	//=================================================================
	$modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
	$pcDir = $modinfo['directory'];
	unset($modinfo);

	//=================================================================
	//  grab the for post variable
	//=================================================================
	// $pc_username = pnVarCleanFromInput('pc_username');
	$pc_username = $_SESSION['pc_username']; // from Michael Brinson 2006-09-19
	$category = pnVarCleanFromInput('pc_category');
	$topic    = pnVarCleanFromInput('pc_topic');

	//=================================================================
	//  set the correct date
	//=================================================================
	$Date =& postcalendar_getDate();

	//=================================================================
	//  get the current view
	//=================================================================
        if(!isset($viewtype)) { $viewtype = 'month'; }

	//=================================================================
	//  Find out what Template we're using
	//=================================================================
	$template_name = _SETTING_TEMPLATE;
	if(!isset($template_name)) { $template_name = 'default'; }

	//=================================================================
	//  Find out what Template View to use
	//=================================================================
	$template_view = pnVarCleanFromInput('tplview');
	if(!isset($template_view)) { $template_view = 'default'; }

	//=================================================================
	//  See if the template view exists
	//=================================================================
	if(!file_exists("modules/$pcDir/pntemplates/$template_name/views/$viewtype/$template_view.html")) {
		$template_view_load = 'default';
	} else {
		$template_view_load = pnVarPrepForOS($template_view);
	}

	//=================================================================
	//  Grab the current theme information
	//=================================================================
	pnThemeLoad(pnUserGetTheme());
	global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5, $bgcolor6, $textcolor1, $textcolor2;

	//=================================================================
	//  Insert necessary JavaScript into the page
	//=================================================================
	$output = pnModAPIFunc(__POSTCALENDAR__, 'user', 'pageSetup');

	//=================================================================
	//  Setup Smarty Template Engine
	//=================================================================
	$tpl =& new pcSmarty();

	//if(!$tpl->is_cached("$template_name/views/$viewtype/$template_view_load.html",$cacheid)) {
	//diable caching completely
	if (true) {
		//=================================================================
		//  Let's just finish setting things up
		//=================================================================
		$the_year   = substr($Date,0,4);
		$the_month  = substr($Date,4,2);
		$the_day    = substr($Date,6,2);
		$last_day = Date_Calc::daysInMonth($the_month,$the_year);

		//=================================================================
		//  populate the template object with information for
		//  Month Names, Long Day Names and Short Day Names
		//  as translated in the language files
		//  (may be adding more here soon - based on need)
		//=================================================================
		$pc_month_names = array(_CALJAN,_CALFEB,_CALMAR,_CALAPR,_CALMAY,_CALJUN,
			_CALJUL,_CALAUG,_CALSEP,_CALOCT,_CALNOV,_CALDEC);

		$pc_short_day_names = array(_CALSUNDAYSHORT, _CALMONDAYSHORT,
			_CALTUESDAYSHORT, _CALWEDNESDAYSHORT,
			_CALTHURSDAYSHORT, _CALFRIDAYSHORT,
			_CALSATURDAYSHORT);

		$pc_long_day_names = array(_CALSUNDAY, _CALMONDAY,
			_CALTUESDAY, _CALWEDNESDAY,
			_CALTHURSDAY, _CALFRIDAY,
			_CALSATURDAY);
		//=================================================================
		//  here we need to set up some information for later
		//  variable creation.  This helps us establish the correct
		//  date ranges for each view.  There may be a better way
		//  to handle all this, but my brain hurts, so your comments
		//  are very appreciated and welcomed.
		//=================================================================
		switch (_SETTING_FIRST_DAY_WEEK)
		{
			case _IS_MONDAY:
				$pc_array_pos = 1;
				$first_day  = date('w',mktime(0,0,0,$the_month,0,$the_year));
				$week_day   = date('w',mktime(0,0,0,$the_month,$the_day-1,$the_year));
				$end_dow    = date('w',mktime(0,0,0,$the_month,$last_day,$the_year));
				if($end_dow != 0) {
					$the_last_day = $last_day+(7-$end_dow);
				} else {
					$the_last_day = $last_day;
				}
				break;
			case _IS_SATURDAY:
				$pc_array_pos = 6;
				$first_day  = date('w',mktime(0,0,0,$the_month,2,$the_year));
				$week_day   = date('w',mktime(0,0,0,$the_month,$the_day+1,$the_year));
				$end_dow    = date('w',mktime(0,0,0,$the_month,$last_day,$the_year));
				if($end_dow == 6) {
					$the_last_day = $last_day+6;
				} elseif($end_dow != 5) {
					$the_last_day = $last_day+(5-$end_dow);
				} else {
					$the_last_day = $last_day;
				}
				break;
			case _IS_SUNDAY:
			default:
				$pc_array_pos = 0;
				$first_day  = date('w',mktime(0,0,0,$the_month,1,$the_year));
				$week_day   = date('w',mktime(0,0,0,$the_month,$the_day,$the_year));
				$end_dow    = date('w',mktime(0,0,0,$the_month,$last_day,$the_year));
				if($end_dow != 6) {
					$the_last_day = $last_day+(6-$end_dow);
				} else {
					$the_last_day = $last_day;
				}
				break;
		}

		// passing the times array to the tpl the times array is for the days schedule
		$tpl->assign_by_ref("times",$times);
		// load the table width to the template
		// $tpl->assign("day_td_width",$GLOBALS['day_view_td_width']);

		//=================================================================
		//  Week View is a bit of a pain in the ass, so we need to
		//  do some extra setup for that view.  This section will
		//  find the correct starting and ending dates for a given
		//  seven day period, based on the day of the week the
		//  calendar is setup to run under (Sunday, Saturday, Monday)
		//=================================================================
		$first_day_of_week = sprintf('%02d',$the_day-$week_day);
		$week_first_day = date('m/d/Y',mktime(0,0,0,$the_month,$first_day_of_week,$the_year));
		list($week_first_day_month, $week_first_day_date, $week_first_day_year) = explode('/',$week_first_day);
		$week_first_day_month_name = pnModAPIFunc(__POSTCALENDAR__, 'user', 'getmonthname',
			array('Date'=>mktime(0,0,0,$week_first_day_month,$week_first_day_date,$week_first_day_year)));
		$week_last_day = date('m/d/Y',mktime(0,0,0,$the_month,$first_day_of_week+6,$the_year));
		list($week_last_day_month, $week_last_day_date, $week_last_day_year) = explode('/',$week_last_day);
		$week_last_day_month_name = pnModAPIFunc(__POSTCALENDAR__, 'user', 'getmonthname',
			array('Date'=>mktime(0,0,0,$week_last_day_month,$week_last_day_date,$week_last_day_year)));

		$week_view_start = date('Y-m-d',mktime(0,0,0,$the_month,$first_day_of_week,$the_year));
		$week_view_end = date('Y-m-d',mktime(0,0,0,$the_month,$first_day_of_week+6,$the_year));

		//=================================================================
		//  Setup some information so we know the actual month's dates
		//  also get today's date for later use and highlighting
		//=================================================================
		$month_view_start = date('Y-m-d',mktime(0,0,0,$the_month,1,$the_year));
		$month_view_end   = date('Y-m-t',mktime(0,0,0,$the_month,1,$the_year));
		$today_date = postcalendar_today('%Y-%m-%d');

		//=================================================================
		//  Setup the starting and ending date ranges for pcGetEvents()
		//=================================================================
		switch($viewtype) {
			case 'day' :
				$starting_date = date('m/d/Y',mktime(0,0,0,$the_month,$the_day,$the_year));
				$ending_date   = date('m/d/Y',mktime(0,0,0,$the_month,$the_day,$the_year));
				break;
			case 'week' :
				$starting_date = "$week_first_day_month/$week_first_day_date/$week_first_day_year";
				$ending_date   = "$week_last_day_month/$week_last_day_date/$week_last_day_year";
				$calendarView  = Date_Calc::getCalendarWeek($week_first_day_date,
					$week_first_day_month,
					$week_first_day_year,
					'%Y-%m-%d');
				break;
			case 'month' :
				$starting_date = date('m/d/Y',mktime(0,0,0,$the_month,1-$first_day,$the_year));
				$ending_date   = date('m/d/Y',mktime(0,0,0,$the_month,$the_last_day,$the_year));
				$calendarView  = Date_Calc::getCalendarMonth($the_month, $the_year, '%Y-%m-%d');
				break;
			case 'year' :
				$starting_date = date('m/d/Y',mktime(0,0,0,1,1,$the_year));
				$ending_date   = date('m/d/Y',mktime(0,0,0,1,1,$the_year+1));
				$calendarView  = Date_Calc::getCalendarYear($the_year, '%Y-%m-%d');
				break;
		}


		//=================================================================
		//  Identify the Providers whose schedules we should load
		//=================================================================

                //==================================
                //FACILITY FILTERING (CHEMED)
                if ( $_SESSION['pc_facility'] ) {
       		    $provinfo = getProviderInfo('%', true, $_SESSION['pc_facility']);
                } else {
       		    $provinfo = getProviderInfo();
                }
                //EOS FACILITY FILTERING (CHEMED)
                //==================================

		$single = array();
                $provIDs = array();  // array of numeric provider IDs

		// filter the display on the requested username, the provinfo array is
		// used to build columns in the week view.

		foreach($provinfo as $provider) {
		    if(is_array($pc_username)){
		        foreach($pc_username as $uname) {
		            if (!empty($pc_username) && $provider['username'] == $uname) {
			        array_push($single,$provider);
			        array_push($provIDs,$provider['id']);
			    }
			}
	            }
	            else {
	                if (!empty($pc_username) && $provider['username'] == $pc_username) {
		            array_push($single,$provider);
			    array_push($provIDs,$provider['id']);
		        }
		    }
		}
		if ($single != null) {
		    $provinfo = $single;
		}

		//=================================================================
		//  Load the events
		//=================================================================
		if($viewtype != 'year') {
			$eventsByDate =& postcalendar_userapi_pcGetEvents(array('start'=>$starting_date,'end'=>$ending_date, 'viewtype' => $viewtype, 'provider_id' => $provIDs));
		} else {
			$eventsByDate = array();
		}


		//=================================================================
		//  Create an array with the day names in the correct order
		//=================================================================
		$daynames = array();
		$numDays = count($pc_long_day_names);
		for($i=0; $i < $numDays; $i++) {
			if($pc_array_pos >= $numDays) {
				$pc_array_pos = 0;
			}
			array_push($daynames,$pc_long_day_names[$pc_array_pos]);
			$pc_array_pos++;
		}
		unset($numDays);
		$sdaynames = array();
		$numDays = count($pc_short_day_names);
		for($i=0; $i < $numDays; $i++) {
			if($pc_array_pos >= $numDays) {
				$pc_array_pos = 0;
			}
			array_push($sdaynames,$pc_short_day_names[$pc_array_pos]);
			$pc_array_pos++;
		}
		unset($numDays);

		//=================================================================
		//  Prepare some values for the template
		//=================================================================
		$prev_month = Date_Calc::beginOfPrevMonth(1,$the_month,$the_year,'%Y%m%d');
		$next_month = Date_Calc::beginOfNextMonth(1,$the_month,$the_year,'%Y%m%d');

		$pc_prev = pnModURL(__POSTCALENDAR__,'user','view',
			array('tplview'=>$template_view,
			'viewtype'=>'month',
			'Date'=>$prev_month,
			'pc_username'=>$pc_username,
			'pc_category'=>$category,
			'pc_topic'=>$topic));

		$pc_next = pnModURL(__POSTCALENDAR__,'user','view',
			array('tplview'=>$template_view,
			'viewtype'=>'month',
			'Date'=>$next_month,
			'pc_username'=>$pc_username,
			'pc_category'=>$category,
			'pc_topic'=>$topic));

		$prev_day = Date_Calc::prevDay($the_day,$the_month,$the_year,'%Y%m%d');
		$next_day = Date_Calc::nextDay($the_day,$the_month,$the_year,'%Y%m%d');
		$pc_prev_day = pnModURL(__POSTCALENDAR__,'user','view',
			array('tplview'=>$template_view,
			'viewtype'=>'day',
			'Date'=>$prev_day,
			'pc_username'=>$pc_username,
			'pc_category'=>$category,
			'pc_topic'=>$topic));

		$pc_next_day = pnModURL(__POSTCALENDAR__,'user','view',
			array('tplview'=>$template_view,
			'viewtype'=>'day',
			'Date'=>$next_day,
			'pc_username'=>$pc_username,
			'pc_category'=>$category,
			'pc_topic'=>$topic));

		$prev_week = date('Ymd',mktime(0,0,0,$week_first_day_month,$week_first_day_date-7,$week_first_day_year));
		$next_week = date('Ymd',mktime(0,0,0,$week_last_day_month,$week_last_day_date+1,$week_last_day_year));
		$pc_prev_week = pnModURL(__POSTCALENDAR__,'user','view',
			array('viewtype'=>'week',
			'Date'=>$prev_week,
			'pc_username'=>$pc_username,
			'pc_category'=>$category,
			'pc_topic'=>$topic));
		$pc_next_week = pnModURL(__POSTCALENDAR__,'user','view',
			array('viewtype'=>'week',
			'Date'=>$next_week,
			'pc_username'=>$pc_username,
			'pc_category'=>$category,
			'pc_topic'=>$topic));

		$prev_year = date('Ymd',mktime(0,0,0,1,1,$the_year-1));
		$next_year = date('Ymd',mktime(0,0,0,1,1,$the_year+1));
		$pc_prev_year = pnModURL(__POSTCALENDAR__,'user','view',
			array('viewtype'=>'year',
			'Date'=>$prev_year,
			'pc_username'=>$pc_username,
			'pc_category'=>$category,
			'pc_topic'=>$topic));
		$pc_next_year = pnModURL(__POSTCALENDAR__,'user','view',
			array('viewtype'=>'year',
			'Date'=>$next_year,
			'pc_username'=>$pc_username,
			'pc_category'=>$category,
			'pc_topic'=>$topic));

		//=================================================================
		//  Populate the template
		//=================================================================
		$all_categories = pnModAPIFunc(__POSTCALENDAR__,'user','getCategories');

		if(isset($calendarView)) {
			$tpl->assign_by_ref('CAL_FORMAT',$calendarView);
		}
		
                if ($viewtype == "week") {
			$last_blocks = array();
			foreach($eventsByDate as $cdate => $day) {
				$tblock = array_reverse($day['blocks']);
				$last_blocks[$cdate] = count($tblock) -1;
				for ($i=0;$i<count($tblock);$i++) {
					if (!empty($tblock[$i])) {
						$last_blocks[$cdate] = count($tblock) - $i;
						break;
					}
				}
			}
			$tpl->assign("last_blocks",$last_blocks);
		}

		$tpl->assign('STYLE',$GLOBALS['style']);
		$tpl->assign('show_days',$show_days);

		//$provinfo[count($provinfo) +1] = array("id" => "","lname" => "Other");
		$tpl->assign_by_ref('providers', $provinfo);

		if (pnVarCleanFromInput("show_days") != 1) {
			$tpl->assign('showdaysurl',"index.php?" . $_SERVER['QUERY_STRING'] . "&show_days=1");
		}

		$tpl->assign('interval', $GLOBALS['calendar_interval']);
		$tpl->assign_by_ref('VIEW_TYPE',$viewtype);
		$tpl->assign_by_ref('A_MONTH_NAMES',$pc_month_names);
		$tpl->assign_by_ref('A_LONG_DAY_NAMES',$pc_long_day_names);
		$tpl->assign_by_ref('A_SHORT_DAY_NAMES',$pc_short_day_names);
		$tpl->assign_by_ref('S_LONG_DAY_NAMES',$daynames);
		$tpl->assign_by_ref('S_SHORT_DAY_NAMES',$sdaynames);
		$tpl->assign_by_ref('A_EVENTS',$eventsByDate);
		$tpl->assign_by_ref('A_CATEGORY',$all_categories);
		$tpl->assign_by_ref('PREV_MONTH_URL',$pc_prev);
		$tpl->assign_by_ref('NEXT_MONTH_URL',$pc_next);
		$tpl->assign_by_ref('PREV_DAY_URL',$pc_prev_day);
		$tpl->assign_by_ref('NEXT_DAY_URL',$pc_next_day);
		$tpl->assign_by_ref('PREV_WEEK_URL',$pc_prev_week);
		$tpl->assign_by_ref('NEXT_WEEK_URL',$pc_next_week);
		$tpl->assign_by_ref('PREV_YEAR_URL',$pc_prev_year);
		$tpl->assign_by_ref('NEXT_YEAR_URL',$pc_next_year);
		$tpl->assign_by_ref('WEEK_START_DATE',$week_view_start);
		$tpl->assign_by_ref('WEEK_END_DATE',$week_view_end);
		$tpl->assign_by_ref('MONTH_START_DATE',$month_view_start);
		$tpl->assign_by_ref('MONTH_END_DATE',$month_view_end);
		$tpl->assign_by_ref('TODAY_DATE',$today_date);
		$tpl->assign_by_ref('DATE',$Date);
		$tpl->assign_by_ref('SCHEDULE_BASE_URL', pnModURL(__POSTCALENDAR__,'user','submit'));
		$tpl->assign_by_ref('interval',$intervals);
        };

	//=================================================================
    //  Parse the template
    //=================================================================
	$template = "$template_name/views/$viewtype/$template_view_load.html";
	if(!$print) {
            $output .= "\n\n<!-- START POSTCALENDAR OUTPUT [-: HTTP://POSTCALENDAR.TV :-] -->\n\n";
    	    $output .= $tpl->fetch($template,$cacheid);    // cache id
    	    $output .= "\n\n<!-- END POSTCALENDAR OUTPUT [-: HTTP://POSTCALENDAR.TV :-] -->\n\n";
	} else {
            $theme = pnUserGetTheme();
            echo "<html><head>";
            echo "<LINK REL=\"StyleSheet\" HREF=\"themes/$theme/style/styleNN.css\" TYPE=\"text/css\">\n\n\n";
            echo "<style type=\"text/css\">\n";
            echo "@import url(\"themes/$theme/style/style.css\"); ";
            echo "</style>\n";
            echo "</head><body>\n";
            echo $output;
            $tpl->display($template,$cacheid);
            echo postcalendar_footer();
            echo "\n</body></html>";
            session_write_close();
            exit;
	}
	//=================================================================
    //  Return the output
    //=================================================================
    return $output;
}

/**
 *  postcalendar_userapi_eventPreview
 *  Creates the detailed event display and outputs html.
 *  Accepts an array of key/value pairs
 *  @param array $event array of event details from the form
 *  @return string html output
 *  @access public
 */
function postcalendar_userapi_eventPreview($args)
{
    // get the theme globals :: is there a better way to do this?
    pnThemeLoad(pnUserGetTheme());
    global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5;
    global $textcolor1, $textcolor2;

	extract($args); unset($args);
    $uid = pnUserGetVar('uid');
    //=================================================================
    //  Setup Smarty Template Engine
    //=================================================================
    $tpl =& new pcSmarty();
	$tpl->caching = false;
	// add preceding zeros
    $event_starttimeh   = sprintf('%02d',$event_starttimeh);
    $event_starttimem   = sprintf('%02d',$event_starttimem);
    $event_startday     = sprintf('%02d',$event_startday);
    $event_startmonth   = sprintf('%02d',$event_startmonth);
    $event_endday       = sprintf('%02d',$event_endday);
    $event_endmonth     = sprintf('%02d',$event_endmonth);

    if(!(bool)_SETTING_TIME_24HOUR) {
        if($event_startampm == _PM_VAL) {
            if($event_starttimeh != 12) {
				$event_starttimeh+=12;
			}
        } elseif($event_startampm == _AM_VAL) {
            if($event_starttimeh == 12) {
				$event_starttimeh = 00;
			}
        }
    }

	$event_startampm." - ";
	$startTime = $event_starttimeh.':'.$event_starttimem.' ';

	$event = array();
	$event['eid'] = '';
	$event['uname'] = $uname;
	$event['catid'] = $event_category;
	if($pc_html_or_text == 'html') {
		$prepFunction = 'pcVarPrepHTMLDisplay';
	} else {
		$prepFunction = 'pcVarPrepForDisplay';
	}
    $event['title'] = $prepFunction($event_subject);
	$event['hometext'] = $prepFunction($event_desc);
	$event['desc'] = $event['hometext'];
    $event['date'] = $event_startyear.$event_startmonth.$event_startday;
	$event['duration'] = $event_duration;
	$event['duration_hours'] = $event_dur_hours;
	$event['duration_minutes'] = $event_dur_minutes;
	$event['endDate'] = $event_endyear.'-'.$event_endmonth.'-'.$event_endday;
    $event['startTime'] = $startTime;
	$event['recurrtype'] = '';
	$event['recurrfreq'] = '';
    $event['recurrspec'] = $event_recurrspec;
	$event['topic'] = $event_topic;
	$event['alldayevent'] = $event_allday;
    $event['conttel'] = $prepFunction($event_conttel);
	$event['contname'] = $prepFunction($event_contname);
    $event['contemail'] = $prepFunction($event_contemail);
	$event['website'] = $prepFunction(postcalendar_makeValidURL($event_website));
	$event['fee'] = $prepFunction($event_fee);
    $event['location'] = $prepFunction($event_location);
	$event['street1'] = $prepFunction($event_street1);
	$event['street2'] = $prepFunction($event_street2);
	$event['city'] = $prepFunction($event_city);
	$event['state'] = $prepFunction($event_state);
	$event['postal'] = $prepFunction($event_postal);

    //=================================================================
    //  get event's topic information
	//=================================================================
    if(_SETTING_DISPLAY_TOPICS) {
		list($dbconn) = pnDBGetConn();
    	$pntable = pnDBGetTables();
		$topics_table = $pntable['topics'];
    	$topics_column = $pntable['topics_column'];
    	$topicsql = "SELECT $topics_column[topictext],$topics_column[topicimage]
            	 	 FROM $topics_table
            	 	 WHERE $topics_column[topicid] = $event[topic]
            	 	 LIMIT 1";
    	$topic_result = $dbconn->Execute($topicsql);
    	list($event['topictext'],$event['topicimg']) = $topic_result->fields;
		$topic_result->Close();
	} else {
		$event['topictext'] = $event['topicimg'] = '';
	}
	//=================================================================
    //  Find out what Template we're using
	//=================================================================
    $template_name = _SETTING_TEMPLATE;
    if(!isset($template_name)) {
    	$template_name = 'default';
    }
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
	$tpl->assign_by_ref('A_EVENT',$event);
	$tpl->assign('STYLE',$GLOBALS['style']);
	//=================================================================
    //  Parse the template
    //=================================================================
	$output  = "\n\n<!-- POSTCALENDAR HTTP://WWW.BAHRAINI.TV -->\n\n";
    $output .= "\n\n<!-- POSTCALENDAR TEMPLATE START -->\n\n";
    $output .= $tpl->fetch($template_name.'/user/preview.html');
    $output .= "\n\n<!-- POSTCALENDAR TEMPLATE END -->\n\n";

	return $output;
}

/**
 *  checkEventCollision
 *  Returns an array containing any events that collide with the specified event
 *  @params array(key=>value)
 *  @return array $events[][]
 */
 function checkEventCollision($edata) {

	extract($edata);
	$sdate = ($event_startmonth.'/'.$event_startday.'/'.$event_startyear);
    $edate = $sdate;
	//hour from forms is 12 not 24 format, convert here
	if($event_startampm == 2 && $event_starttimeh != 12)
		$event_starttimeh += 12;
 	elseif ($event_startampm == 1 && $event_starttimeh == 12)
		$event_starttimeh -= 12;

	$stime = date("H:i:00",strtotime($event_starttimeh.':'.$event_starttimem.':00'));
	$etime = date("H:i:00",$event_duration + strtotime($stime));
	//echo "stime is: $stime, etime is: $etime sdate is: $sdate edate is: $edate<br />";
	$a = array('collideFlag' => true,'start'=>$edate,'end'=>$sdate, 'provider_id' => $event_userid, 'stime' => $stime, 'etime' => $etime);
	$eventsByDate =& postcalendar_userapi_pcGetEvents($a);
	//print_r($eventsByDate);
	$collisions = array();
	foreach ($eventsByDate as $day) {
	foreach ($day as $event) {
		if ($event['duration'] == 0 && $event['alldayevent'] == 0) {
			continue;
		}
		elseif($event['alldayevent'] == 1) {
			$collisions[] = $event;
		}
		$festart = strtotime($event['startTime']);
		$feend	 = strtotime($event['startTime'] + $event['duration']);
		$estart  = strtotime($stime);
		$eend	 = strtotime($etime);

		//echo "festart = $festart feend = $feend estart = $estart eend = $eend<br />";
		if ($festart < $eend && $feend > $estart) {
			$collisions[] = $event;
		}
		elseif ($festart <= $estart && $feend <= $eend && $feend >= $estart) {
			$collisions[] = $event;
		}
		elseif ($festart >= $estart && $festart < $eend) {
				$collisions[] = $event;
		}
	}
	}
	//foreach ($collisions as $collide) {
	//	echo "collided: " . $collide['title'] . "<br />";
	//}
	return $collisions;
 }

/**
 *  postcalendar_userapi_pcQueryEventsFA
 *  Returns an array containing the event's information for first available queiries
 *  @params array(key=>value)
 *  @params string key eventstatus
 *  @params int value -1 == hidden ; 0 == queued ; 1 == approved
 *  @return array $events[][]
 */
function &postcalendar_userapi_pcQueryEventsFA($args)	{

  $end = '0000-00-00';
  extract($args);
  $eventstatus = 1;
  if (is_numeric($event_status))
    $eventstatus = $event_status;

  if(!isset($start)) { $start = Date_Calc::dateNow('%Y-%m-%d'); }
  list($sy,$sm,$sd) = explode('-',$start);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();
  // link to the events tables
  $table      =  $pntable['postcalendar_events'];
  $cattable   =  $pntable['postcalendar_categories'];

  $sql = "SELECT DISTINCT a.pc_eid,  a.pc_informant, a.pc_catid, a.pc_title, " .
    "a.pc_time, a.pc_hometext, a.pc_eventDate, a.pc_duration, a.pc_endDate, " .
    "a.pc_startTime, a.pc_recurrtype, a.pc_recurrfreq, a.pc_recurrspec, " .
    "a.pc_topic, a.pc_alldayevent, a.pc_location, a.pc_conttel, " .
    "a.pc_contname, a.pc_contemail, a.pc_website, a.pc_fee, a.pc_sharing, " .
    "a.pc_prefcatid, " .
    "b.pc_catcolor, b.pc_catname, b.pc_catdesc, a.pc_pid, a.pc_aid, " .
    "concat(u.fname,' ',u.lname) as provider_name, " .
    "concat(pd.fname,' ',pd.lname) as patient_name, " .
    "concat(u2.fname, ' ', u2.lname) as owner_name, pd.DOB as patient_dob " .
    "FROM  ( $table AS a, $cattable AS b ) " .
    "LEFT JOIN users as u ON a.pc_aid = u.id " .
    "LEFT JOIN users as u2 ON a.pc_aid = u2.id " .
    "LEFT JOIN patient_data as pd ON a.pc_pid=pd.pid " .
    "WHERE  b.pc_catid = a.pc_catid " .
    "AND a.pc_eventstatus = $eventstatus " .
    "AND (a.pc_endDate >= '$start' OR a.pc_endDate = '0000-00-00') " .
    "AND a.pc_eventDate <= '$end' " .
    "AND (a.pc_aid = '" . $provider_id . "' OR a.pc_aid = '')";

  //======================================================================
  //  START SEARCH FUNCTIONALITY
  //======================================================================
  if(!empty($s_keywords)) $sql .= "AND ($s_keywords) ";
  if(!empty($s_category)) $sql .= "AND ($s_category) ";
  if(!empty($s_topic)) 	$sql .= "AND ($s_topic) ";
  if(!empty($collide_etime) && !empty($collide_stime)) {
    $sql .= "AND NOT ((pc_endTime <= '$collide_stime') OR (pc_startTime >= '$collide_etime')) AND pc_endTime IS NOT NULL ";
  }
  if(!empty($category))	$sql .= "AND (a.pc_catid = '".pnVarPrepForStore($category)."') ";
  if(!empty($topic))		$sql .= "AND (a.pc_topic = '".pnVarPrepForStore($topic)."') ";
  //======================================================================
  //  Search sort and limitation
  //======================================================================
  if(empty($sort)) $sql .= "GROUP BY a.pc_eid ORDER BY a.pc_startTime ASC";
  else $sql .= "GROUP BY a.pc_eid ORDER BY a.$sort";
  //======================================================================
  //  END SEARCH FUNCTIONALITY
  //======================================================================
  //echo "<Br />sql: $sql<br />";
  $result = $dbconn->Execute($sql);
  if($dbconn->ErrorNo() != 0) die ($dbconn->ErrorMsg());

  // put the information into an array for easy access
  $events = array();
  // return an empty array if we don't have any results
  if(!isset($result)) { return $events; }

  for($i=0; !$result->EOF; $result->MoveNext()) {

    // get the results from the query
    if(isset($tmp)) { unset($tmp); } $tmp = array();
    list($tmp['eid'],          $tmp['uname'],         $tmp['catid'],
         $tmp['title'],        $tmp['time'],          $tmp['hometext'],
         $tmp['eventDate'],    $tmp['duration'],      $tmp['endDate'],
         $tmp['startTime'],    $tmp['recurrtype'],    $tmp['recurrfreq'],
         $tmp['recurrspec'],   $tmp['topic'],         $tmp['alldayevent'],
         $tmp['location'],     $tmp['conttel'],       $tmp['contname'],
         $tmp['contemail'],    $tmp['website'],       $tmp['fee'],
         $tmp['sharing'],      $tmp['prefcatid'],     $tmp['catcolor'],
         $tmp['catname'],      $tmp['catdesc'],       $tmp['pid'],
         $tmp['aid'],          $tmp['provider_name'], $tmp['patient_name'],
         $tmp['owner_name'],   $tmp['patient_dob'])   = $result->fields;

    // grab the name of the topic
    $topicname = pcGetTopicName($tmp['topic']);
    // get the user id of event's author
    $cuserid = @$nuke_users[strtolower($tmp['uname'])];
    // check the current event's permissions
    // the user does not have permission to view this event
    // if any of the following evaluate as false
    if(!pnSecAuthAction(0, 'PostCalendar::Event', "$tmp[title]::$tmp[eid]", ACCESS_OVERVIEW)) {
      continue;
    } elseif(!pnSecAuthAction(0, 'PostCalendar::Category', "$tmp[catname]::$tmp[catid]", ACCESS_OVERVIEW)) {
      continue;
    } elseif(!pnSecAuthAction(0, 'PostCalendar::User', "$tmp[uname]::$cuserid", ACCESS_OVERVIEW)) {
      continue;
    } elseif(!pnSecAuthAction(0, 'PostCalendar::Topic', "$topicname::$tmp[topic]", ACCESS_OVERVIEW)) {
      continue;
    } elseif($tmp['sharing'] == SHARING_PRIVATE && $cuserid != $userid) {
      continue;
    }

    // add event to the array if we passed the permissions check
    // this is the common information
    $events[$i]['eid']         = $tmp['eid'];
    $events[$i]['uname']       = $tmp['uname'];
    $events[$i]['uid']         = $cuserid;
    $events[$i]['catid']       = $tmp['catid'];
    $events[$i]['time']        = $tmp['time'];
    $events[$i]['eventDate']   = $tmp['eventDate'];
    $events[$i]['duration']    = $tmp['duration'];
    // there has to be a more intelligent way to do this
    @list($events[$i]['duration_hours'],$dmin) = @explode('.',($tmp['duration']/60/60));
    $events[$i]['duration_minutes'] = substr(sprintf('%.2f','.' . 60*($dmin/100)),2,2);
    //''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    $events[$i]['endDate']     = $tmp['endDate'];
    $events[$i]['startTime']   = $tmp['startTime'];
    $events[$i]['recurrtype']  = $tmp['recurrtype'];
    $events[$i]['recurrfreq']  = $tmp['recurrfreq'];
    $events[$i]['recurrspec']  = $tmp['recurrspec'];

    $rspecs = unserialize($tmp['recurrspec']);
    $events[$i]['event_repeat_freq'] = $rspecs['event_repeat_freq'];
    $events[$i]['event_repeat_freq_type'] = $rspecs['event_repeat_freq_type'];
    $events[$i]['event_repeat_on_num'] = $rspecs['event_repeat_on_num'];
    $events[$i]['event_repeat_on_day'] = $rspecs['event_repeat_on_day'];
    $events[$i]['event_repeat_on_freq'] = $rspecs['event_repeat_on_freq'];

    $events[$i]['topic']       = $tmp['topic'];
    $events[$i]['alldayevent'] = $tmp['alldayevent'];
    $events[$i]['catcolor']    = $tmp['catcolor'];
    $events[$i]['catname']     = $tmp['catname'];
    $events[$i]['catdesc']     = $tmp['catdesc'];
    $events[$i]['pid']         = $tmp['pid'];
    $events[$i]['patient_name']= $tmp['patient_name'];
    $events[$i]['provider_name'] = $tmp['provider_name'];
    $events[$i]['owner_name']  = $tmp['owner_name'];
    $events[$i]['patient_dob'] = $tmp['patient_dob'];
    $events[$i]['patient_age'] = date("Y") - substr(($tmp['patient_dob']),0,4);
    $events[$i]['sharing']     = $tmp['sharing'];
    $events[$i]['prefcatid']   = $tmp['prefcatid'];
    $events[$i]['aid']         = $tmp['aid'];
    $events[$i]['intervals']   = ceil(($tmp['duration']/60) / $GLOBALS['calendar_interval']);
    if($events[$i]['intervals'] == 0)
      $events[$i]['intervals'] = 1;
    // is this a public event to be shown as busy?
    if($tmp['sharing'] == SHARING_BUSY && $cuserid != $userid) {
      // make it not display any information
      $events[$i]['title']       = _USER_BUSY_TITLE;
      $events[$i]['hometext']    = _USER_BUSY_MESSAGE;
      $events[$i]['desc']        = _USER_BUSY_MESSAGE;
      $events[$i]['conttel']     = '';
      $events[$i]['contname']    = '';
      $events[$i]['contemail']   = '';
      $events[$i]['website']     = '';
      $events[$i]['fee']         = '';
      $events[$i]['location']    = '';
      $events[$i]['street1']     = '';
      $events[$i]['street2']     = '';
      $events[$i]['city']        = '';
      $events[$i]['state']       = '';
      $events[$i]['postal']      = '';
    } else {
      $display_type = substr($tmp['hometext'],0,6);
      if($display_type == ':text:') {
        $prepFunction = 'pcVarPrepForDisplay';
        $tmp['hometext'] = substr($tmp['hometext'],6);
      } elseif($display_type == ':html:') {
        $prepFunction = 'pcVarPrepHTMLDisplay';
        $tmp['hometext'] = substr($tmp['hometext'],6);
      } else {
        $prepFunction = 'pcVarPrepHTMLDisplay';
      }
      unset($display_type);
      $events[$i]['title']       = $prepFunction($tmp['title']);
      $events[$i]['hometext']    = $prepFunction($tmp['hometext']);
      $events[$i]['desc']        = $events[$i]['hometext'];
      $events[$i]['conttel']     = $prepFunction($tmp['conttel']);
      $events[$i]['contname']    = $prepFunction($tmp['contname']);
      $events[$i]['contemail']   = $prepFunction($tmp['contemail']);
      $events[$i]['website']     = $prepFunction(postcalendar_makeValidURL($tmp['website']));
      $events[$i]['fee']         = $prepFunction($tmp['fee']);
      $loc = unserialize($tmp['location']);
      $events[$i]['location']   = $prepFunction($loc['event_location']);
      $events[$i]['street1']    = $prepFunction($loc['event_street1']);
      $events[$i]['street2']    = $prepFunction($loc['event_street2']);
      $events[$i]['city']       = $prepFunction($loc['event_city']);
      $events[$i]['state']      = $prepFunction($loc['event_state']);
      $events[$i]['postal']     = $prepFunction($loc['event_postal']);
    }
    $i++;
  }
  unset($tmp);
  $result->Close();
  return $events;
}


/**
 *  postcalendar_userapi_pcQueryEvents
 *  INPUT
 *  $args = Array of values possibly containing:
 *     $provider_id = array of provider ID numbers
 *     
 *  Returns an array containing the event's information
 *  @params array(key=>value)
 *  @params string key eventstatus
 *  @params int value -1 == hidden ; 0 == queued ; 1 == approved
 *  @return array $events[][]
 */
function &postcalendar_userapi_pcQueryEvents($args)
{
  $end = '0000-00-00';
  extract($args);

  // echo "<!-- args = "; print_r($args); echo " -->\n"; // debugging

  // $pc_username = pnVarCleanFromInput('pc_username');
  $pc_username = $_SESSION['pc_username']; // from Michael Brinson 2006-09-19
  if (empty($pc_username) || is_array($pc_username)) {
    $pc_username = "__PC_ALL__";
  }

  //echo "DEBUG pc_username: $pc_username \n"; // debugging

  $topic = pnVarCleanFromInput('pc_topic');
  $category = pnVarCleanFromInput('pc_category');

  if(!empty($pc_username) && (strtolower($pc_username) != 'anonymous')) {
    if($pc_username=='__PC_ALL__' || $pc_username == -1) {
      $ruserid = -1;
    } else {
      $ruserid = getIDfromUser($pc_username);
    }
  }

  if(!isset($eventstatus)) { $eventstatus = 1; }
  // sanity check on eventstatus
  if((int)$eventstatus < -1 || (int)$eventstatus > 1) { $eventstatus = 1; }
  if(!isset($start)) { $start = Date_Calc::dateNow('%Y-%m-%d'); }
  list($sy,$sm,$sd) = explode('-',$start);

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();
  // link to the events tables
  $table      =  $pntable['postcalendar_events'];
  $cattable   =  $pntable['postcalendar_categories'];
  $topictable =  $pntable['postcalendar_topics'];

  $sql = "SELECT DISTINCT a.pc_eid,  a.pc_informant, a.pc_catid, " .
    "a.pc_title, a.pc_time, a.pc_hometext, a.pc_eventDate, a.pc_duration, " .
    "a.pc_endDate, a.pc_startTime, a.pc_recurrtype, a.pc_recurrfreq, " .
    "a.pc_recurrspec, a.pc_topic, a.pc_alldayevent, a.pc_location, " .
    "a.pc_conttel, a.pc_contname, a.pc_contemail, a.pc_website, a.pc_fee, " .
    "a.pc_sharing, a.pc_prefcatid, b.pc_catcolor, b.pc_catname, " .
    "b.pc_catdesc, a.pc_pid, a.pc_apptstatus, a.pc_aid, " .
    "concat(u.fname,' ',u.lname) as provider_name, " .
    "concat(pd.lname,', ',pd.fname) as patient_name, " .
    "concat(u2.fname, ' ', u2.lname) as owner_name, " .
    "DOB as patient_dob, pd.pubpid " .
    "FROM  ( $table AS a, $cattable AS b ) " .
    "LEFT JOIN users as u ON a.pc_aid = u.id " .
    "LEFT JOIN users as u2 ON a.pc_aid = u2.id " .
    "LEFT JOIN patient_data as pd ON a.pc_pid = pd.pid " .
    "WHERE  b.pc_catid = a.pc_catid " .
    "AND a.pc_eventstatus = $eventstatus " .
    "AND ((a.pc_endDate >= '$start' AND a.pc_eventDate <= '$end') OR " .
    "(a.pc_endDate = '0000-00-00' AND a.pc_eventDate >= '$start' AND " .
    "a.pc_eventDate <= '$end')) ";

  //==================================
  //FACILITY FILTERING (lemonsoftware)(CHEMED)
    if ( $_SESSION['pc_facility'] ) {
            $pc_facility = $_SESSION['pc_facility'];
            $sql .= " AND a.pc_facility = $pc_facility
                      AND u.facility_id = $pc_facility
                      AND u2.facility_id = $pc_facility ";
    }
    else if ($pc_facility) {
        // pc_facility could be provided in the search arguments -- JRM March 2008
        $sql .= " AND a.pc_facility = $pc_facility".
                " AND u.facility_id = $pc_facility".
                " AND u2.facility_id = $pc_facility ";
    }
  //EOS FACILITY FILTERING (lemonsoftware)
  //==================================


  // The above 3 lines replaced these:
  //   AND (a.pc_endDate >= '$start' OR a.pc_endDate = '0000-00-00')
  //   AND a.pc_eventDate <= '$end' ";

  if(!empty($providerID)) { $ruserid = $providerID; }

  // eliminate ruserid if we're trying to query by provider_id -- JRM
  if (!empty($provider_id)) { unset($ruserid); }

  if(isset($ruserid)) {
    // get all events for the specified username
    if($ruserid == -1) {
      $sql .= "AND (a.pc_sharing = '" . SHARING_BUSY . "' ";
      $sql .= "OR a.pc_sharing = '" . SHARING_PUBLIC . "') ";
    } else {
      $sql .= "AND a.pc_aid = " . $ruserid . " ";
    }
  } elseif(!pnUserLoggedIn()) {
    // get all events for anonymous users
    $sql .= "AND a.pc_sharing = '" . SHARING_GLOBAL . "' ";
  } elseif(!empty($provider_id)) {
    // get all events for a variety of provider IDs -- JRM
    $sql .= "AND a.pc_aid in (" . implode(",", $provider_id). ") ";
  } else {
    // get all events for logged in user plus global events
    $sql .= "AND (a.pc_aid = " . $_SESSION['authUserID'] . " OR a.pc_sharing = '" . SHARING_GLOBAL . "') ";
  }

  //======================================================================
  //  START SEARCH FUNCTIONALITY
  //======================================================================
  if(!empty($s_keywords)) $sql .= "AND ($s_keywords) ";
  if(!empty($s_category)) $sql .= "AND ($s_category) ";
  if(!empty($s_topic))    $sql .= "AND ($s_topic) ";
  if(!empty($category))   $sql .= "AND (a.pc_catid = '".pnVarPrepForStore($category)."') ";
  if(!empty($topic))      $sql .= "AND (a.pc_topic = '".pnVarPrepForStore($topic)."') ";

  //======================================================================
  //  Search sort and limitation
  //======================================================================
  if(empty($sort)) $sql .= "GROUP BY a.pc_eid ORDER BY a.pc_time DESC";
  else $sql .= "GROUP BY a.pc_eid ORDER BY a.$sort";

  //======================================================================
  //  END SEARCH FUNCTIONALITY
  //======================================================================
  //echo "<br>sq: $sql<br />";

  // echo "<!-- " . $sql . " -->\n"; // debugging

  $result = $dbconn->Execute($sql);
  if($dbconn->ErrorNo() != 0) die ($dbconn->ErrorMsg());

  // put the information into an array for easy access
  $events = array();
  // return an empty array if we don't have any results
  if(!isset($result)) { return $events; }

  for($i=0; !$result->EOF; $result->MoveNext()) {

    // WHY are we using an array for intermediate storage???  -- Rod

    // get the results from the query
    if(isset($tmp)) { unset($tmp); } $tmp = array();
    list($tmp['eid'],          $tmp['uname'],       $tmp['catid'],
         $tmp['title'],        $tmp['time'],        $tmp['hometext'],
         $tmp['eventDate'],    $tmp['duration'],    $tmp['endDate'],
         $tmp['startTime'],    $tmp['recurrtype'],  $tmp['recurrfreq'],
         $tmp['recurrspec'],   $tmp['topic'],       $tmp['alldayevent'],
         $tmp['location'],     $tmp['conttel'],     $tmp['contname'],
         $tmp['contemail'],    $tmp['website'],     $tmp['fee'],
         $tmp['sharing'],      $tmp['prefcatid'],   $tmp['catcolor'],
         $tmp['catname'],      $tmp['catdesc'],     $tmp['pid'],
         $tmp['apptstatus'],   $tmp['aid'],         $tmp['provider_name'],
         $tmp['patient_name'], $tmp['owner_name'],  $tmp['patient_dob'],
         $tmp['pubpid']) = $result->fields;

    // grab the name of the topic
    $topicname = pcGetTopicName($tmp['topic']);
    // get the user id of event's author
    $cuserid = @$nuke_users[strtolower($tmp['uname'])];
    // check the current event's permissions
    // the user does not have permission to view this event
    // if any of the following evaluate as false
    if(!pnSecAuthAction(0, 'PostCalendar::Event', "$tmp[title]::$tmp[eid]", ACCESS_OVERVIEW)) {
      continue;
    } elseif(!pnSecAuthAction(0, 'PostCalendar::Category', "$tmp[catname]::$tmp[catid]", ACCESS_OVERVIEW)) {
      continue;
    } elseif(!pnSecAuthAction(0, 'PostCalendar::User', "$tmp[uname]::$cuserid", ACCESS_OVERVIEW)) {
      continue;
    } elseif(!pnSecAuthAction(0, 'PostCalendar::Topic', "$topicname::$tmp[topic]", ACCESS_OVERVIEW)) {
      continue;
    } elseif($tmp['sharing'] == SHARING_PRIVATE && $cuserid != $userid) {
      continue;
    }

    // add event to the array if we passed the permissions check
    // this is the common information

    $events[$i]['intervals'] 	=($tmp['duration']/60)/	$GLOBALS['day_calandar_interval'];//sets the number of rows this event should span
    print_r($events[$i]['intervals']);

    $events[$i]['eid']         = $tmp['eid'];
    $events[$i]['uname']       = $tmp['uname'];
    $events[$i]['uid']         = $cuserid;
    $events[$i]['catid']       = $tmp['catid'];
    $events[$i]['time']        = $tmp['time'];
    $events[$i]['eventDate']   = $tmp['eventDate'];
    $events[$i]['duration']    = $tmp['duration'];
    // there has to be a more intelligent way to do this
    @list($events[$i]['duration_hours'],$dmin) = @explode('.',($tmp['duration']/60/60));
    $events[$i]['duration_minutes'] = substr(sprintf('%.2f','.' . 60*($dmin/100)),2,2);
    //''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    $events[$i]['endDate']     = $tmp['endDate'];
    $events[$i]['startTime']   = $tmp['startTime'];
    $events[$i]['recurrtype']  = $tmp['recurrtype'];
    $events[$i]['recurrfreq']  = $tmp['recurrfreq'];
    $events[$i]['recurrspec']  = $tmp['recurrspec'];
    $events[$i]['topic']       = $tmp['topic'];
    $events[$i]['alldayevent'] = $tmp['alldayevent'];
    $events[$i]['catcolor']    = $tmp['catcolor'];
    // Modified 06-2009 by BM to translate the category if applicable
    $events[$i]['catname']     = xl_appt_category($tmp['catname']);
    $events[$i]['catdesc']     = $tmp['catdesc'];
    $events[$i]['pid']         = $tmp['pid'];
    $events[$i]['apptstatus']  = $tmp['apptstatus'];
    $events[$i]['pubpid']      = $tmp['pubpid'];
    $events[$i]['patient_name']= $tmp['patient_name'];
    $events[$i]['provider_name'] = $tmp['provider_name'];
    $events[$i]['owner_name']  = $tmp['owner_name'];
    $events[$i]['patient_dob'] = $tmp['patient_dob'];
    $events[$i]['patient_age'] = getPatientAge($tmp['patient_dob']);
    $events[$i]['sharing']     = $tmp['sharing'];
    $events[$i]['prefcatid']   = $tmp['prefcatid'];
    $events[$i]['aid']         = $tmp['aid'];
    $events[$i]['topictext']   = $topicname;
    $events[$i]['intervals']   = ceil(($tmp['duration']/60) / $GLOBALS['calendar_interval']);
    if($events[$i]['intervals'] == 0)
      $events[$i]['intervals'] = 1;
    // is this a public event to be shown as busy?
    if($tmp['sharing'] == SHARING_BUSY && $cuserid != $userid) {
      // make it not display any information
      $events[$i]['title']       = _USER_BUSY_TITLE;
      $events[$i]['hometext']    = _USER_BUSY_MESSAGE;
      $events[$i]['desc']        = _USER_BUSY_MESSAGE;
      $events[$i]['conttel']     = '';
      $events[$i]['contname']    = '';
      $events[$i]['contemail']   = '';
      $events[$i]['website']     = '';
      $events[$i]['fee']         = '';
      $events[$i]['location']    = '';
      $events[$i]['street1']     = '';
      $events[$i]['street2']     = '';
      $events[$i]['city']        = '';
      $events[$i]['state']       = '';
      $events[$i]['postal']      = '';
    } else {
      $display_type = substr($tmp['hometext'],0,6);
      if($display_type == ':text:') {
        $prepFunction = 'pcVarPrepForDisplay';
        $tmp['hometext'] = substr($tmp['hometext'],6);
      } elseif($display_type == ':html:') {
        $prepFunction = 'pcVarPrepHTMLDisplay';
        $tmp['hometext'] = substr($tmp['hometext'],6);
      } else {
        $prepFunction = 'pcVarPrepHTMLDisplay';
      }
      unset($display_type);
      $events[$i]['title']       = $prepFunction($tmp['title']);
      $events[$i]['hometext']    = $prepFunction($tmp['hometext']);
      $events[$i]['desc']        = $events[$i]['hometext'];
      $events[$i]['conttel']     = $prepFunction($tmp['conttel']);
      $events[$i]['contname']    = $prepFunction($tmp['contname']);
      $events[$i]['contemail']   = $prepFunction($tmp['contemail']);
      $events[$i]['website']     = $prepFunction(postcalendar_makeValidURL($tmp['website']));
      $events[$i]['fee']         = $prepFunction($tmp['fee']);
      $loc = unserialize($tmp['location']);
      $events[$i]['location']   = $prepFunction($loc['event_location']);
      $events[$i]['street1']    = $prepFunction($loc['event_street1']);
      $events[$i]['street2']    = $prepFunction($loc['event_street2']);
      $events[$i]['city']       = $prepFunction($loc['event_city']);
      $events[$i]['state']      = $prepFunction($loc['event_state']);
      $events[$i]['postal']     = $prepFunction($loc['event_postal']);
    }
    $i++;
  }
  unset($tmp);
  $result->Close();
  return $events;
}


function getBlockTime($time) {

	if ($time == 0 || strlen($time) == 0) {

		return "all_day";
	}
	$ts = strtotime($time);
	$half = 0;
	$minutes = date("i",$ts);
	$hour = date("H",$ts);
	if ($minutes >= 30)
		$half = 1;
	$blocknum = (($hour * 2) +$half);
	return strval($blocknum);
}

/*==========================
 * Gather up all the Events matching the arguements
 * Arguements can be:
 *  start = starting date in m/d/Y format
 *  end = ending date in m/d/Y format
 *  viewtype = day|week|month|year
 *  provider_id = array of numeric IDs  <-- specified by JRM
 *========================== */
function &postcalendar_userapi_pcGetEvents($args)
{
    $s_keywords = $s_category = $s_topic = '';
    extract($args);
	
    $date =& postcalendar_getDate();
    $cy = substr($date,0,4);
    $cm = substr($date,4,2);
    $cd = substr($date,6,2);
    if(isset($start) && isset($end)) {
        // parse start date
    	list($sm,$sd,$sy) = explode('/',$start);
    	// parse end date
        list($em,$ed,$ey) = explode('/',$end);

    	$s = (int) "$sy$sm$sd";
    	if($s > $date) {
            $cy = $sy;
            $cm = $sm;
            $cd = $sd;
    	}
    	$start_date = Date_Calc::dateFormat($sd,$sm,$sy,'%Y-%m-%d');
    	$end_date = Date_Calc::dateFormat($ed,$em,$ey,'%Y-%m-%d');
    } else {
        // missing start OR end date, set them to the current date
        $sm = $em = $cm;
        $sd = $ed = $cd;
        $sy = $cy;
        $ey = $cy+2;
        $start_date = $sy.'-'.$sm.'-'.$sd;
        $end_date = $ey.'-'.$em.'-'.$ed;
    }
    
    if ($faFlag && !isset($events)) {
        $a = array('faFlag' => true,'start'=>$start_date,'end'=>$end_date,'s_keywords'=>$s_keywords,'s_category'=>$s_category,'s_topic'=>$s_topic,'viewtype'=>$viewtype, 'provider_id' => $provider_id, 'event_status' => $event_status);
        $events =& pnModAPIFunc(__POSTCALENDAR__,'user','<strong></strong>pcQueryEventsFA',$a);
    }
    elseif ($collideFlag && !isset($events)) {
        $a = array('collideFlag' => true,'start'=>$start_date,'end'=>$end_date, 'provider_id' => $provider_id, 'collide_stime' => $stime, 'collide_etime' => $etime);
        $events =& pnModAPIFunc(__POSTCALENDAR__,'user','pcQueryEventsFA',$a);
    }
    elseif ($listappsFlag && !isset($events)) {
        $a = array('listappsFlag' => true,'start'=>$start_date,'end'=>$end_date, 'patient_id' => $patient_id, 's_keywords' => $s_keywords);
        $events =& pnModAPIFunc(__POSTCALENDAR__,'user','pcQueryEvents',$a);
    }
    else if(!isset($events)) {
        if(!isset($s_keywords)) $s_keywords = '';
        $a = array('start'=>$start_date,'end'=>$end_date,'s_keywords'=>$s_keywords,'s_category'=>$s_category,'s_topic'=>$s_topic,'viewtype'=>$viewtype, "sort" => "pc_startTime ASC, a.pc_duration ASC ",'providerID' => $providerID, 'provider_id' => $provider_id);
        $events =& pnModAPIFunc(__POSTCALENDAR__,'user','pcQueryEvents',$a);
    }

    //==============================================================
    //  Here we build an array consisting of the date ranges
    //  specific to the current view.  This array is then
    //  used to build the calendar display.
    //==============================================================
    $days = array();
    $sday = Date_Calc::dateToDays($sd,$sm,$sy);
    $eday = Date_Calc::dateToDays($ed,$em,$ey);
    for($cday = $sday; $cday <= $eday; $cday++) {
        $d = Date_Calc::daysToDate($cday,'%d');
        $m = Date_Calc::daysToDate($cday,'%m');
        $y = Date_Calc::daysToDate($cday,'%Y');
        $store_date = Date_Calc::dateFormat($d,$m,$y,'%Y-%m-%d');
        $days[$store_date] = array();
    }
    $days = calculateEvents($days,$events,$viewtype);
    return $days;
}

//===========================
// Given an array of events, an array of days, and a view type
// fill days with events (recurring is the challenge)
//===========================
function calculateEvents($days,$events,$viewtype) {
  $date =& postcalendar_getDate();
  $cy = substr($date,0,4);
  $cm = substr($date,4,2);
  $cd = substr($date,6,2);

  foreach($events as $event) {
    // get the name of the topic
    $topicname = pcGetTopicName($event['topic']);

    // parse the event start date
    list($esY,$esM,$esD) = explode('-',$event['eventDate']);

    // grab the recurring specs for the event
    $event_recurrspec = @unserialize($event['recurrspec']);

    // determine the stop date for this event
    if($event['endDate'] == '0000-00-00') {
      $stop = $end_date;  // <--- this isn't previously defined !!
    } else {
      $stop = $event['endDate'];
    }
   
    // here the start_date value is set to whatever comes in
    // on postcalendar_getDate() which is not always the first
    // date of the days array -- JRM
    $start_date = "$cy-$cm-$cd";

    // here we've made the start_date equal to the first date
    // of the days array, makes sense, right? -- JRM
    $days_keys = array_keys($days);
    $start_date = $days_keys[0];

    // Optimization of the stop date to not be much later than required.
    $tmpsecs = strtotime($start_date);
    if      ($viewtype == 'day')   $tmpsecs +=  3 * 24 * 3600;
    else if ($viewtype == 'week')  $tmpsecs +=  9 * 24 * 3600;
    else if ($viewtype == 'month') $tmpsecs += 34 * 24 * 3600;
    else $tmpsecs += 367 * 24 * 3600;
    $tmp = date('Y-m-d', $tmpsecs);
    if ($stop > $tmp) $stop = $tmp;

    $eventD = $event['eventDate'];
    $eventS = $event['startTime'];

    switch($event['recurrtype']) {

      //==============================================================
      //  Events that do not repeat only have a startday
      //==============================================================
      case NO_REPEAT :

        if(isset($days[$event['eventDate']])) {
        array_push($days[$event['eventDate']],$event);
          if ($viewtype == "week") {
            //echo "non repeating date eventdate: $eventD  startime:$eventS block #: " . getBlockTime($eventS) ."<br />";
            fillBlocks($eventD,&$days);
            //echo "for $eventD loading " . getBlockTime($eventS) . "<br /><br />";
            $gbt = getBlockTime($eventS);
            $days[$eventD]['blocks'][$gbt][$eventD][] = $event;
            //echo "event is: " . print_r($days[$eventD]['blocks'][$gbt],true) . " <br />";
            //echo "begin printing blocks for $eventD<br />";
            //print_r($days[$eventD]['blocks']);
            //echo "end printing blocks<br />";
          }
        }

        break;

      //==============================================================
      //  Find events that repeat at a certain frequency
      //  Every,Every Other,Every Third,Every Fourth
      //  Day,Week,Month,Year,MWF,TR,M-F,SS
      //==============================================================
      case REPEAT :

        $rfreq = $event_recurrspec['event_repeat_freq'];
        $rtype = $event_recurrspec['event_repeat_freq_type'];
        $exdate = $event_recurrspec['exdate']; // this attribute follows the iCalendar spec http://www.ietf.org/rfc/rfc2445.txt

        // we should bring the event up to date to make this a tad bit faster
        // any ideas on how to do that, exactly??? dateToDays probably.
        $nm = $esM; $ny = $esY; $nd = $esD;
        $occurance = Date_Calc::dateFormat($nd,$nm,$ny,'%Y-%m-%d');
        while($occurance < $start_date) {
          $occurance =& __increment($nd,$nm,$ny,$rfreq,$rtype);
          list($ny,$nm,$nd) = explode('-',$occurance);
        }
        while($occurance <= $stop) {
          if(isset($days[$occurance])) {
            // check for date exceptions before pushing the event into the days array -- JRM
            $excluded = false;
            if (isset($exdate)) {
                foreach (explode(",", $exdate) as $exception) {
                    // occurrance format == yyyy-mm-dd
                    // exception format == yyyymmdd
                    if (preg_replace("/-/", "", $occurance) == $exception) {
                        $excluded = true;
                    }
                }
            }

            // push event into the days array
            if ($excluded == false) array_push($days[$occurance],$event);

            if ($viewtype == "week") {
              fillBlocks($occurance, &$days);
              //echo "for $occurance loading " . getBlockTime($eventS) . "<br /><br />";
              $gbt = getBlockTime($eventS);
              $days[$occurance]['blocks'][$gbt][$occurance][] = $event;
              //echo "begin printing blocks for $eventD<br />";
              //print_r($days[$occurance]['blocks']);
              //echo "end printing blocks<br />";
            }
          }
          $occurance =& __increment($nd,$nm,$ny,$rfreq,$rtype);
          list($ny,$nm,$nd) = explode('-',$occurance);
        }
        break;

      //==============================================================
      //  Find events that repeat on certain parameters
      //  On 1st,2nd,3rd,4th,Last
      //  Sun,Mon,Tue,Wed,Thu,Fri,Sat
      //  Every N Months
      //==============================================================
      case REPEAT_ON :

        $rfreq = $event_recurrspec['event_repeat_on_freq'];
        $rnum  = $event_recurrspec['event_repeat_on_num'];
        $rday  = $event_recurrspec['event_repeat_on_day'];
        $exdate = $event_recurrspec['exdate']; // this attribute follows the iCalendar spec http://www.ietf.org/rfc/rfc2445.txt

        //==============================================================
        //  Populate - Enter data into the event array
        //==============================================================
        $nm = $esM; $ny = $esY; $nd = $esD;
        // make us current
        while($ny < $cy) {
          $occurance = date('Y-m-d',mktime(0,0,0,$nm+$rfreq,$nd,$ny));
          list($ny,$nm,$nd) = explode('-',$occurance);
        }

        // populate the event array
        while($ny <= $cy) {
          $dnum = $rnum; // get day event repeats on
          do {
              $occurance = Date_Calc::NWeekdayOfMonth($dnum--,$rday,$nm,$ny,$format="%Y-%m-%d");
          } while($occurance === -1);

          if(isset($days[$occurance]) && $occurance <= $stop) {
            // check for date exceptions before pushing the event into the days array -- JRM
            $excluded = false;
            if (isset($exdate)) {
                foreach (explode(",", $exdate) as $exception) {
                    // occurrance format == yyyy-mm-dd
                    // exception format == yyyymmdd
                    if (preg_replace("/-/", "", $occurance) == $exception) {
                        $excluded = true;
                    }
                }
            }
            
            // push event into the days array
            if ($excluded == false) array_push($days[$occurance],$event);

            if ($viewtype == "week") {
              fillBlocks($occurance,&$days);
              //echo "for $occurance loading " . getBlockTime($eventS) . "<br /><br />";
              $gbt = getBlockTime($eventS);
              $days[$occurance]['blocks'][$gbt][$occurance][] = $event;
            }
          }
          $occurance = date('Y-m-d',mktime(0,0,0,$nm+$rfreq,$nd,$ny));
          list($ny,$nm,$nd) = explode('-',$occurance);
        }

        break;

    } // <- end of switch($event['recurrtype'])
  } // <- end of foreach($events as $event)
  return $days;
}

function fillBlocks($td,&$ar) {
	if (strlen ($td) > 0 && !isset($ar[$td]['blocks'])) {
			$ar[$td]['blocks'] = array();
			for ($j=0;$j<48;$j++)
				$ar[strval($td)]['blocks'][strval($j)] = array();
			$ar[strval($td)]['blocks']["all_day"] = array();
		}


}

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
            $nextWorkDOW = date('D',mktime(0,0,0,$m,($d+$daycount),$y));
            if ($nextWorkDOW == "Sat") { $f++; }
            else if ($nextWorkDOW == "Sun") { $f++; }
        }
        // and finally make sure we haven't landed on a Sat/Sun
        // adjust as necessary
        $nextWorkDOW = date('D',mktime(0,0,0,$m,($d+$f),$y));
        if ($nextWorkDOW == "Sat") { $f+=2; }
        else if ($nextWorkDOW == "Sun") { $f++; }

        return date('Y-m-d',mktime(0,0,0,$m,($d+$f),$y));

    } elseif($t == REPEAT_EVERY_WEEK) {
        return date('Y-m-d',mktime(0,0,0,$m,($d+(7*$f)),$y));
    } elseif($t == REPEAT_EVERY_MONTH) {
        return date('Y-m-d',mktime(0,0,0,($m+$f),$d,$y));
    } elseif($t == REPEAT_EVERY_YEAR) {
        return date('Y-m-d',mktime(0,0,0,$m,$d,($y+$f)));
    }
}

?>
