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
//  Load the API Functions
//=========================================================================
pnModAPILoad(__POSTCALENDAR__,'admin');

/**
 * the main administration function
 * This function is the default function, and is called whenever the
 * module is initiated without defining arguments.  As such it can
 * be used for a number of things, but most commonly it either just
 * shows the module menu and returns or calls whatever the module
 * designer feels should be the default function (often this is the
 * view() function)
 */
function postcalendar_admin_main()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }
	return postcalendar_admin_modifyconfig();
}
function postcalendar_admin_listapproved() { return postcalendar_admin_showlist('',_EVENT_APPROVED,'listapproved',_PC_APPROVED_ADMIN); }
function postcalendar_admin_listhidden() { return postcalendar_admin_showlist('',_EVENT_HIDDEN,'listhidden',_PC_HIDDEN_ADMIN); }
function postcalendar_admin_listqueued() { return postcalendar_admin_showlist('',_EVENT_QUEUED,'listqueued',_PC_QUEUED_ADMIN); }
function postcalendar_admin_showlist($e='',$type,$function,$title,$msg='')
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }
$header = <<<EOF
	<html>
	<head></head>
	<body bgcolor=
EOF;
	$header .= '"' . $GLOBALS['style']['BGCOLOR2'] . '">';
	$output .= $header;
	$output .= postcalendar_adminmenu();

	if(!empty($e)) {
		$output .= '<div style="padding:5px; border:1px solid red; background-color: pink;">';
		$output .= '<center><b>'.$e.'</b></center>';
		$output .= '</div><br />';
	}

	if(!empty($msg)) {
		$output .= '<div style="padding:5px; border:1px solid green; background-color: lightgreen;">';
		$output .= '<center><b>'.$msg.'</b></center>';
		$output .= '</div><br />';
	}

	$offset_increment = _SETTING_HOW_MANY_EVENTS;
    if(empty($offset_increment)) $offset_increment = 15;

	pnThemeLoad(pnUserGetTheme());
    // get the theme globals :: is there a better way to do this?
    global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5;
    global $textcolor1, $textcolor2;

    $offset = pnVarCleanFromInput('offset');
    $sort = pnVarCleanFromInput('sort');
    $sdir = pnVarCleanFromInput('sdir');
    if(!isset($sort)) $sort = 'time';
    if(!isset($sdir)) $sdir = 1;
    if(!isset($offset))  $offset = 0;

	$result = pnModAPIFunc(__POSTCALENDAR__,'admin','getAdminListEvents',
	        			   array('type'=>$type,
						         'sdir'=>$sdir,
								 'sort'=>$sort,
						         'offset'=>$offset,
								 'offset_increment'=>$offset_increment));

    $output .= pnModAPIFunc(__POSTCALENDAR__,'admin','buildAdminList',
	        			      array('type'=>$type,
							  		'title'=>$title,
						         	'sdir'=>$sdir,
								 	'sort'=>$sort,
						         	'offset'=>$offset,
								 	'offset_increment'=>$offset_increment,
								 	'function'=>$function,
								 	'result'=>$result));

	$output .= "</body></html>";
    return $output;
}

function postcalendar_admin_adminevents()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	$output = '';
    list($action,$pc_event_id,$thelist) = pnVarCleanFromInput('action','pc_event_id','thelist');

    if(!isset($pc_event_id)) {
        $e  = _PC_NO_EVENT_SELECTED;

		switch($thelist) {
            case 'listqueued' :
                $output .= postcalendar_admin_showlist($e,_EVENT_QUEUED,'showlist');
                break;

            case 'listhidden' :
                $output .= postcalendar_admin_showlist($e,_EVENT_HIDDEN,'showlist');
                break;
                
            case 'listapproved' :
                $output .= postcalendar_admin_showlist($e,_EVENT_APPROVED,'showlist');
                break;
        }
        return $output;     
    }
    
    // main menu
    $output = postcalendar_adminmenu();
    $function = '';
    switch ($action) {
        case _ADMIN_ACTION_APPROVE :
            $function = 'approveevents';
			$are_you_sure_text = _PC_APPROVE_ARE_YOU_SURE;
			break;
            
        case _ADMIN_ACTION_HIDE :
            $function = 'hideevents';
			$are_you_sure_text = _PC_HIDE_ARE_YOU_SURE;
			break;
            
        case _ADMIN_ACTION_DELETE :
            $function = 'deleteevents';
			$are_you_sure_text = _PC_DELETE_ARE_YOU_SURE;
			break;
    }

	if(!empty($function)) {
		$output .= '<form action="'.pnModUrl(__POSTCALENDAR__,'admin',$function).'" method="post">';
    	$output .= $are_you_sure_text.' ';
    	$output .= '<input type="submit" name="submit" value="'._PC_ADMIN_YES.'" />';
		$output .= '<br /><br />';
	}
    if(is_array($pc_event_id)) {
		foreach($pc_event_id as $eid) {
        	$output .= pnModAPIFunc(__POSTCALENDAR__,'admin','eventDetail',array('eid'=>$eid,'nopop'=>true));
        	$output .= '<br /><br />';
        	$output .= '<input type="hidden" name="pc_eid[]" value="'.$eid.'" />';
    	}
	} else {
		$output .= pnModAPIFunc(__POSTCALENDAR__,'admin','eventDetail',array('eid'=>$pc_event_id,'nopop'=>true));
        $output .= '<br /><br />';
        $output .= '<input type="hidden" name="pc_eid[]" value="'.$pc_event_id.'" />';
	}
	if(!empty($function)) {
    	$output .= $are_you_sure_text.' ';
    	$output .= '<input type="submit" name="submit" value="'._PC_ADMIN_YES.'" />';
    	$output .= '</form>';
	}
    
	return $output;
}

function postcalendar_admin_approveevents()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }
	
	$pc_eid = pnVarCleanFromInput('pc_eid');
    $approve_list = '';
    foreach($pc_eid as $eid) {
        if(!empty($approve_list)) { $approve_list .= ','; }
        $approve_list .= $eid;
    }
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $events_table = $pntable['postcalendar_events'];
    $events_column = &$pntable['postcalendar_events_column'];
    
    $sql = "UPDATE $events_table
            SET $events_column[eventstatus] = "._EVENT_APPROVED."
            WHERE $events_column[eid] IN ($approve_list)";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) { 
		$msg = _PC_ADMIN_EVENT_ERROR; 
	} else { 
		$msg = _PC_ADMIN_EVENTS_APPROVED; 
	}

	// clear the template cache
	$tpl =& new pcSmarty();
	$tpl->clear_all_cache();
    return postcalendar_admin_showlist('',_EVENT_APPROVED,'listapproved',_PC_APPROVED_ADMIN,$msg);
}

function postcalendar_admin_hideevents()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }
	
	$pc_eid = pnVarCleanFromInput('pc_eid');
    $output = postcalendar_adminmenu();
    $event_list = '';
    foreach($pc_eid as $eid) {
        if(!empty($event_list)) { $event_list .= ','; }
        $event_list .= $eid;
    }
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $events_table = $pntable['postcalendar_events'];
    $events_column = &$pntable['postcalendar_events_column'];
    
    $sql = "UPDATE $events_table
            SET $events_column[eventstatus] = "._EVENT_HIDDEN."
            WHERE $events_column[eid] IN ($event_list)";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        $msg = _PC_ADMIN_EVENT_ERROR;
    } else {
        $msg = _PC_ADMIN_EVENTS_HIDDEN;
    }
    
	// clear the template cache
	$tpl =& new pcSmarty();
	$tpl->clear_all_cache();
    return postcalendar_admin_showlist('',_EVENT_APPROVED,'listapproved',_PC_APPROVED_ADMIN,$msg);
}

function postcalendar_admin_deleteevents()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	$pc_eid = pnVarCleanFromInput('pc_eid');
    $output = postcalendar_adminmenu();
    $event_list = '';
    foreach($pc_eid as $eid) {
        if(!empty($event_list)) { $event_list .= ','; }
        $event_list .= $eid;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $events_table = $pntable['postcalendar_events'];
    $events_column = &$pntable['postcalendar_events_column'];

    $sql = "DELETE FROM $events_table WHERE $events_column[eid] IN ($event_list)";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        $msg = _PC_ADMIN_EVENT_ERROR;
    } else {
        $msg = _PC_ADMIN_EVENTS_DELETED;
    }

	// clear the t
	$tpl =& new pcSmarty();
	$tpl->clear_all_cache();
    return postcalendar_admin_showlist('',_EVENT_APPROVED,'listapproved',_PC_APPROVED_ADMIN,$msg);
}

function postcalendar_admin_edit($args) { return postcalendar_admin_submit($args); }
function postcalendar_admin_submit($args)
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	pnModAPILoad(__POSTCALENDAR__,'user');
	$output = postcalendar_adminmenu();

	// get the theme globals :: is there a better way to do this?
    pnThemeLoad(pnUserGetTheme());
    global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5, $textcolor1, $textcolor2;
    
	extract($args);
	
	$Date = postcalendar_getDate();
	$year   = substr($Date,0,4);
    $month  = substr($Date,4,2);
    $day    = substr($Date,6,2);
    
	// basic event information
	$event_subject  	= pnVarCleanFromInput('event_subject');
	$event_desc 		= pnVarCleanFromInput('event_desc');
	$event_sharing  	= pnVarCleanFromInput('event_sharing');
	$event_category 	= pnVarCleanFromInput('event_category');
	$event_topic 		= pnVarCleanFromInput('event_topic');
	
	// event start information
	$event_startmonth 	= pnVarCleanFromInput('event_startmonth');
	$event_startday 	= pnVarCleanFromInput('event_startday');
	$event_startyear 	= pnVarCleanFromInput('event_startyear');
	$event_starttimeh	= pnVarCleanFromInput('event_starttimeh');
	$event_starttimem 	= pnVarCleanFromInput('event_starttimem');
	$event_startampm 	= pnVarCleanFromInput('event_startampm');
	
	// event end information
	$event_endmonth 	= pnVarCleanFromInput('event_endmonth');
	$event_endday 		= pnVarCleanFromInput('event_endday');
	$event_endyear  	= pnVarCleanFromInput('event_endyear');
	$event_endtype  	= pnVarCleanFromInput('event_endtype');
	$event_dur_hours 	= pnVarCleanFromInput('event_dur_hours');
	$event_dur_minutes  = pnVarCleanFromInput('event_dur_minutes');
	$event_duration 	= (60*60*$event_dur_hours) + (60*$event_dur_minutes);
	$event_allday 		= pnVarCleanFromInput('event_allday');
	
	// location data
	$event_location 	= pnVarCleanFromInput('event_location');
	$event_street1  	= pnVarCleanFromInput('event_street1');
	$event_street2  	= pnVarCleanFromInput('event_street2');
	$event_city 		= pnVarCleanFromInput('event_city');
	$event_state 		= pnVarCleanFromInput('event_state');
	$event_postal 		= pnVarCleanFromInput('event_postal');
	$event_location_info = serialize(compact('event_location', 'event_street1', 'event_street2',
                                             'event_city', 'event_state', 'event_postal'));
	// contact data
	$event_contname 	= pnVarCleanFromInput('event_contname');
	$event_conttel  	= pnVarCleanFromInput('event_conttel');
	$event_contemail 	= pnVarCleanFromInput('event_contemail');
	$event_website  	= pnVarCleanFromInput('event_website');
	$event_fee  		= pnVarCleanFromInput('event_fee');
	
	// event repeating data
	$event_repeat 		= pnVarCleanFromInput('event_repeat');
	$event_repeat_freq  = pnVarCleanFromInput('event_repeat_freq');
	$event_repeat_freq_type = pnVarCleanFromInput('event_repeat_freq_type');
	$event_repeat_on_num = pnVarCleanFromInput('event_repeat_on_num');
	$event_repeat_on_day = pnVarCleanFromInput('event_repeat_on_day');
	$event_repeat_on_freq = pnVarCleanFromInput('event_repeat_on_freq');
	$event_recurrspec = serialize(compact('event_repeat_freq', 'event_repeat_freq_type', 'event_repeat_on_num',
                                          'event_repeat_on_day', 'event_repeat_on_freq'));
	
	$pc_html_or_text = pnVarCleanFromInput('pc_html_or_text');
	$form_action = pnVarCleanFromInput('form_action');
    $pc_event_id = pnVarCleanFromInput('pc_event_id');
	$data_loaded = pnVarCleanFromInput('data_loaded');
    $is_update   = pnVarCleanFromInput('is_update');
    $authid      = pnVarCleanFromInput('authid');
	
	if(pnUserLoggedIn()) { $uname = pnUserGetVar('uname'); } 
    else { $uname = pnConfigGetVar('anonymous'); }
    if(!isset($event_repeat)) { $event_repeat = 0; }
    
	// lets wrap all the data into array for passing to submit and preview functions
	if(!isset($pc_event_id) || empty($pc_event_id) || $data_loaded) {
		$eventdata = compact('event_subject','event_desc','event_sharing','event_category','event_topic',
		'event_startmonth','event_startday','event_startyear','event_starttimeh','event_starttimem','event_startampm',
		'event_endmonth','event_endday','event_endyear','event_endtype','event_dur_hours','event_dur_minutes',
		'event_duration','event_allday','event_location','event_street1','event_street2','event_city','event_state',
		'event_postal','event_location_info','event_contname','event_conttel','event_contemail',
		'event_website','event_fee','event_repeat','event_repeat_freq','event_repeat_freq_type',
		'event_repeat_on_num','event_repeat_on_day','event_repeat_on_freq','event_recurrspec','uname',
		'Date','year','month','day','pc_html_or_text');
		$eventdata['is_update'] = $is_update;
		$eventdata['pc_event_id'] = $pc_event_id;
		$eventdata['data_loaded'] = true;
	} else {
		$event = postcalendar_userapi_pcGetEventDetails($pc_event_id);
		$eventdata['event_subject'] = $event['title'];
		$eventdata['event_desc'] = $event['hometext'];
		$eventdata['event_sharing'] = $event['sharing'];
		$eventdata['event_category'] = $event['catid'];
		$eventdata['event_topic'] = $event['topic'];
		$eventdata['event_startmonth'] = substr($event['eventDate'],5,2);
		$eventdata['event_startday'] = substr($event['eventDate'],8,2);
		$eventdata['event_startyear'] = substr($event['eventDate'],0,4);
		$eventdata['event_starttimeh'] = substr($event['startTime'],0,2);
		$eventdata['event_starttimem'] = substr($event['startTime'],3,2);
		$eventdata['event_startampm'] = $eventdata['event_starttimeh'] < 12 ? _PC_AM : _PC_PM ;
		$eventdata['event_endmonth'] = substr($event['endDate'],5,2);
		$eventdata['event_endday'] = substr($event['endDate'],8,2);
		$eventdata['event_endyear'] = substr($event['endDate'],0,4);
		$eventdata['event_endtype'] = $event['endDate'] == '0000-00-00' ? '0' : '1' ;
		$eventdata['event_dur_hours'] = $event['duration_hours'];
		$eventdata['event_dur_minutes'] = $event['duration_minutes'];
		$eventdata['event_duration'] = $event['duration'];
		$eventdata['event_allday'] = $event['alldayevent'];
		$loc_data = unserialize($event['location']);
		$eventdata['event_location'] = $loc_data['event_location'];
		$eventdata['event_street1'] = $loc_data['event_street1'];
		$eventdata['event_street2'] = $loc_data['event_street2'];
		$eventdata['event_city'] = $loc_data['event_city'];
		$eventdata['event_state'] = $loc_data['event_state'];
		$eventdata['event_postal'] = $loc_data['event_postal'];
		$eventdata['event_location_info'] = $loc_data;
		$eventdata['event_contname'] = $event['contname'];
		$eventdata['event_conttel'] = $event['conttel'];
		$eventdata['event_contemail'] = $event['contemail'];
		$eventdata['event_website'] = $event['website'];
		$eventdata['event_fee'] = $event['fee'];
		$eventdata['event_repeat'] = $event['recurrtype'];
		$eventdata['event_pid'] = $event['pid'];
		$eventdata['event_aid'] = $event['aid'];
		$rspecs = unserialize($event['recurrspec']);
		$eventdata['event_repeat_freq'] = $rspecs['event_repeat_freq'];
		$eventdata['event_repeat_freq_type'] = $rspecs['event_repeat_freq_type'];
		$eventdata['event_repeat_on_num'] = $rspecs['event_repeat_on_num'];
		$eventdata['event_repeat_on_day'] = $rspecs['event_repeat_on_day'];
		$eventdata['event_repeat_on_freq'] = $rspecs['event_repeat_on_freq'];
		$eventdata['event_recurrspec'] = $rspecs;
		$eventdata['uname'] = $uname;
		$eventdata['Date'] = $Date;
		$eventdata['year'] = $year;
		$eventdata['month'] = $month;
		$eventdata['day'] = $day;
		$eventdata['is_update'] = true;
		$eventdata['pc_event_id'] = $pc_event_id;
		$eventdata['data_loaded'] = true;
		$eventdata['pc_html_or_text'] = $pc_html_or_text;
	}
	
	// lets get the module's information
    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $categories = pnModAPIFunc(__POSTCALENDAR__,'user','getCategories');
	
	//================================================================
	//	ERROR CHECKING
	//================================================================
    $required_vars = array('event_subject','event_desc');
    $required_name = array(_PC_EVENT_TITLE,_PC_EVENT_DESC);
    $error_msg = '';
    $reqCount = count($required_vars);
    for ($r=0; $r<$reqCount; $r++) {
        if(empty($$required_vars[$r]) || !preg_match('/\S/i',$$required_vars[$r])) {
            $error_msg .= '<b>' . $required_name[$r] . '</b> ' . _PC_SUBMIT_ERROR4 . '<br />';
        }
    }
    unset($reqCount);
	// check repeating frequencies
	if($event_repeat == REPEAT) {
		if(!isset($event_repeat_freq) ||  $event_repeat_freq < 1 || empty($event_repeat_freq)) {
			$error_msg .= _PC_SUBMIT_ERROR5 . '<br />';
        } elseif(!is_numeric($event_repeat_freq)) {
			$error_msg .= _PC_SUBMIT_ERROR6 . '<br />';
        }
	} elseif($event_repeat == REPEAT_ON) {
		if(!isset($event_repeat_on_freq) || $event_repeat_on_freq < 1 || empty($event_repeat_on_freq)) {
			$error_msg .= _PC_SUBMIT_ERROR5 . '<br />';
        } elseif(!is_numeric($event_repeat_on_freq)) {
			$error_msg .= _PC_SUBMIT_ERROR6 . '<br />';
        }
	}
    // check date validity
    if(_SETTING_TIME_24HOUR) {
        $startTime = $event_starttimeh.':'.$event_starttimem;
        $endTime =   $event_endtimeh.':'.$event_endtimem;
    } else {
        if($event_startampm == _AM_VAL) {
            $event_starttimeh = $event_starttimeh == 12 ? '00' : $event_starttimeh;
        } else {
            $event_starttimeh =  $event_starttimeh != 12 ? $event_starttimeh+=12 : $event_starttimeh;
        }
        $startTime = $event_starttimeh.':'.$event_starttimem;
    }
    $sdate = strtotime($event_startyear.'-'.$event_startmonth.'-'.$event_startday);
    $edate = strtotime($event_endyear.'-'.$event_endmonth.'-'.$event_endday);
    $tdate = strtotime(date('Y-m-d'));
    if($edate < $sdate && $event_endtype == 1) {
        $error_msg .= _PC_SUBMIT_ERROR1 . '<br />';
    }
    if(!checkdate($event_startmonth,$event_startday,$event_startyear)) {
        $error_msg .= _PC_SUBMIT_ERROR2 . '<br />';
    }
    if(!checkdate($event_endmonth,$event_endday,$event_endyear)) {
        $error_msg .= _PC_SUBMIT_ERROR3 . '<br />';
    }
    
	//================================================================
	//	Preview the event
	//================================================================
    if($form_action == 'preview') {
        if(!empty($error_msg)) {
            $preview = false;
            $output .= '<table border="0" width="100%" cellpadding="1" cellspacing="0"><tr><td bgcolor="red">';
            $output .= '<table border="0" width="100%" cellpadding="1" cellspacing="0"><tr><td bgcolor="pink">';
                $output .= '<center><b>' . _PC_SUBMIT_ERROR . '</b></center>'; 
                $output .= '<br />';
                $output .= $error_msg;
            $output .= '</td></td></table>';
            $output .= '</td></td></table>';
            $output .= '<br /><br />';
        } else {
            $output .= pnModAPIFunc(__POSTCALENDAR__,'user','eventPreview',$eventdata);
			$output .= '<br />';
        }
    }
    
	//================================================================
	//	Enter the event into the DB
	//================================================================
	if($form_action == 'commit') {
		//if (!pnSecConfirmAuthKey()) { return(_NO_DIRECT_ACCESS); }
		if(!empty($error_msg)) {
            $preview = false;
            $output .= '<table border="0" width="100%" cellpadding="1" cellspacing="0"><tr><td bgcolor="red">';
            $output .= '<table border="0" width="100%" cellpadding="1" cellspacing="0"><tr><td bgcolor="pink">';
                $output .= '<center><b>'._PC_SUBMIT_ERROR.'</b></center>'; 
                $output .= '<br />';
                $output .= $error_msg;
            $output .= '</td></td></table>';
            $output .= '</td></td></table>';
            $output .= '<br /><br />';
        } else {
            if (!pnModAPIFunc(__POSTCALENDAR__,'admin','submitEvent',$eventdata)) {
        		$output .= '<center><div style="padding:5px; border:1px solid red; background-color: pink;">';		
				$output .= "<b>"._PC_EVENT_SUBMISSION_FAILED."</b>";		
				$output .= '</div></center><br />';	
				$output .= '<br />';
        	} else {
        		// clear the Smarty cache
				$tpl =& new pcSmarty();
				$tpl->clear_all_cache();
				$output .= '<center><div style="padding:5px; border:1px solid green; background-color: lightgreen;">';		
				if($is_update) {
					$output .= "<b>"._PC_EVENT_EDIT_SUCCESS."</b>";		
				} else {
					$output .= "<b>"._PC_EVENT_SUBMISSION_SUCCESS."</b>";		
				}
				$output .= '</div></center><br />';	
				$output .= '<br />';
        		// clear the form vars
        		$event_subject=$event_desc=$event_sharing=$event_category=$event_topic=
				$event_startmonth=$event_startday=$event_startyear=$event_starttimeh=$event_starttimem=$event_startampm=
				$event_endmonth=$event_endday=$event_endyear=$event_endtype=$event_dur_hours=$event_dur_minutes=
				$event_duration=$event_allday=$event_location=$event_street1=$event_street2=$event_city=$event_state=
				$event_postal=$event_location_info=$event_contname=$event_conttel=$event_contemail=
				$event_website=$event_fee=$event_repeat=$event_repeat_freq=$event_repeat_freq_type=
				$event_repeat_on_num=$event_repeat_on_day=$event_repeat_on_freq=$event_recurrspec=$uname=
				$Date=$year=$month=$day=$pc_html_or_text=null;
				$is_update = false;
				$pc_event_id = 0;
				// lets wrap all the data into array for passing to submit and preview functions
				$eventdata = compact('event_subject','event_desc','event_sharing','event_category','event_topic',
				'event_startmonth','event_startday','event_startyear','event_starttimeh','event_starttimem','event_startampm',
				'event_endmonth','event_endday','event_endyear','event_endtype','event_dur_hours','event_dur_minutes',
				'event_duration','event_allday','event_location','event_street1','event_street2','event_city','event_state',
				'event_postal','event_location_info','event_contname','event_conttel','event_contemail',
				'event_website','event_fee','event_repeat','event_repeat_freq','event_repeat_freq_type',
				'event_repeat_on_num','event_repeat_on_day','event_repeat_on_freq','event_recurrspec','uname',
				'Date','year','month','day','pc_html_or_text','is_update','pc_event_id');
			}
        }
	}

    $output .= pnModAPIFunc('PostCalendar','admin','buildSubmitForm',$eventdata);
	return $output;
}

function postcalendar_admin_modifyconfig($msg='',$showMenu=true)
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	$output =& new pnHTML();
	$pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $pcDir = pnVarPrepForOS($pcModInfo['directory']);

	$defaultsURL  = pnModURL(__POSTCALENDAR__,'admin','resetDefaults');
	$defaultsText = _EDIT_PC_CONFIG_DEFAULT;

$jsColorPicker = <<<EOF
    <script LANGUAGE="Javascript" SRC="modules/$pcDir/pnincludes/AnchorPosition.js"></SCRIPT>
    <script LANGUAGE="Javascript" SRC="modules/$pcDir/pnincludes/PopupWindow.js"></SCRIPT>
    <script LANGUAGE="Javascript" SRC="modules/$pcDir/pnincludes/ColorPicker2.js"></SCRIPT>
    <script LANGUAGE="JavaScript">
    var cp = new ColorPicker('window');
    // Runs when a color is clicked
    function pickColor(color) {
	    field.value = color;
	}

    var field;
    function pick(anchorname) {
	    field = document.forms.pcConfig.pcDayHighlightColor;
	    cp.show(anchorname);
	}
    </SCRIPT>
EOF;

    $output->SetInputMode(_PNH_VERBATIMINPUT);
	$header = <<<EOF
	<html>
	<head></head>
	<body bgcolor=
EOF;
	$header .= '"' . $GLOBALS['style']['BGCOLOR2'] . '">';
	$output->Text($header);
    $output->Text($jsColorPicker);
	if($showMenu) {
    	$output->Text(postcalendar_adminmenu());
	}

	if(!empty($msg)) {
		$output->Text('<center><div style="padding:5px; border:1px solid green; background-color: lightgreen;">');
		$output->Text("<b>$msg</b>");
		$output->Text('</div></center><br />');

	}

    $formURL = pnModUrl(__POSTCALENDAR__,'admin','updateconfig');
    /*$output->Text("<form action=\"$formURL\" method=\"post\" enctype=\"application/x-www-form-urlencoded\" name=\"pcConfig\" id=\"pcConfig\">");
    $output->Text('<table border="1" cellpadding="5" cellspacing="1">');
    $output->Text('<tr><td align="left">'._PC_ADMIN_GLOBAL_SETTINGS,'</td>');
	$output->Text("<td nowrap><a href=\"$defaultsURL\">$defaultsText</a></td></tr>");

    $settings = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $i=0;

	// global PostCalendar config options
    $settings[$i][] = $output->Text(_PC_NOTIFY_ADMIN);
    $settings[$i++][] = $output->FormCheckBox('pcNotifyAdmin', pnModGetVar(__POSTCALENDAR__,'pcNotifyAdmin'));
    $settings[$i][] = $output->Text(_PC_NOTIFY_EMAIL);
	$settings[$i++][] = $output->FormText('pcNotifyEmail', pnModGetVar(__POSTCALENDAR__,'pcNotifyEmail'));

	$settings[$i][] = $output->Text(_PC_ALLOW_DIRECT_SUBMIT);
    $settings[$i++][] = $output->FormCheckBox('pcAllowDirectSubmit', pnModGetVar(__POSTCALENDAR__,'pcAllowDirectSubmit'));
    $settings[$i][] = $output->Text(_PC_ALLOW_USER_CALENDAR);
    $settings[$i++][] = $output->FormCheckBox('pcAllowUserCalendar', pnModGetVar(__POSTCALENDAR__,'pcAllowUserCalendar'));
	$settings[$i][] = $output->Text(_PC_ALLOW_SITEWIDE_SUBMIT);
    $settings[$i++][] = $output->FormCheckBox('pcAllowSiteWide', pnModGetVar(__POSTCALENDAR__,'pcAllowSiteWide'));
    $settings[$i][] = $output->Text(_PC_LIST_HOW_MANY);
    $settings[$i++][] = $output->FormText('pcListHowManyEvents', pnModGetVar(__POSTCALENDAR__,'pcListHowManyEvents'),5);
    $settings[$i][] = $output->Text(_PC_TIME24HOURS);
    $settings[$i++][] = $output->FormCheckBox('pcTime24Hours', pnModGetVar(__POSTCALENDAR__,'pcTime24Hours'));
	$settings[$i][] = $output->Text(_PC_TIME_INCREMENT);
    $settings[$i++][] = $output->FormText('pcTimeIncrement', pnModGetVar(__POSTCALENDAR__,'pcTimeIncrement'),5);
    $settings[$i][] = $output->Text(_PC_EVENTS_IN_NEW_WINDOW);
    $settings[$i++][] = $output->FormCheckBox('pcEventsOpenInNewWindow', pnModGetVar(__POSTCALENDAR__,'pcEventsOpenInNewWindow'));
    $settings[$i][] = $output->Text(_PC_INTERNATIONAL_DATES);
    $settings[$i++][] = $output->FormCheckBox('pcUseInternationalDates', pnModGetVar(__POSTCALENDAR__,'pcUseInternationalDates'));
    $settings[$i][] = $output->Text(_PC_EVENT_DATE_FORMAT);
    $settings[$i++][] = $output->FormText('pcEventDateFormat', pnModGetVar(__POSTCALENDAR__,'pcEventDateFormat'));
    $settings[$i][] = $output->Text(_PC_DISPLAY_TOPICS);
    //$settings[$i++][] = $output->FormCheckBox('pcDisplayTopics', pnModGetVar(__POSTCALENDAR__,'pcDisplayTopics'));
    $settings[$i][] = $output->Text(_PC_FIRST_DAY_OF_WEEK);
        $options = array();
        $selected = pnModGetVar(__POSTCALENDAR__,'pcFirstDayOfWeek');
        $options[0]['id']       = '0';
        $options[0]['selected'] = ($selected == 0);
        $options[0]['name']     = _PC_SUNDAY;
        $options[1]['id']       = '1';
        $options[1]['selected'] = ($selected == 1);
        $options[1]['name']     = _PC_MONDAY;
        $options[2]['id']       = '6';
        $options[2]['selected'] = ($selected == 6);
        $options[2]['name']     = _PC_SATURDAY;
	$settings[$i++][] = $output->FormSelectMultiple('pcFirstDayOfWeek', $options);
	$settings[$i][] = $output->Text(_PC_DEFAULT_VIEW);
        $options = array();
        $selected = pnModGetVar(__POSTCALENDAR__,'pcDefaultView');
        $options[0]['id']       = 'day';
        $options[0]['selected'] = ($selected == 'day');
        $options[0]['name']     = _CAL_DAYVIEW;
        $options[1]['id']       = 'week';
        $options[1]['selected'] = ($selected == 'week');
        $options[1]['name']     = _CAL_WEEKVIEW;
        $options[2]['id']       = 'month';
        $options[2]['selected'] = ($selected == 'month');
        $options[2]['name']     = _CAL_MONTHVIEW;
		$options[3]['id']       = 'year';
        $options[3]['selected'] = ($selected == 'year');
        $options[3]['name']     = _CAL_YEARVIEW;
    $settings[$i++][] = $output->FormSelectMultiple('pcDefaultView', $options);
    $settings[$i][] = $output->Text(_PC_DAY_HIGHLIGHT_COLOR . ' [<a HREF="#" onClick="pick(\'pick\');return false;" NAME="pick" ID="pick">pick</a>]');
    $settings[$i++][] = $output->FormText('pcDayHighlightColor', pnModGetVar(__POSTCALENDAR__,'pcDayHighlightColor'));
    $settings[$i][] = $output->Text(_PC_USE_JS_POPUPS);
    $settings[$i++][] = $output->FormCheckBox('pcUsePopups', pnModGetVar(__POSTCALENDAR__,'pcUsePopups'));
	$settings[$i][] = $output->Text(_PC_USE_CACHE);
    $settings[$i++][] = $output->FormCheckBox('pcUseCache', pnModGetVar(__POSTCALENDAR__,'pcUseCache'));
	$settings[$i][] = $output->Text(_PC_CACHE_LIFETIME);
    $settings[$i++][] = $output->FormText('pcCacheLifetime', pnModGetVar(__POSTCALENDAR__,'pcCacheLifetime'));

    $templatelist = array();
    $handle = opendir('modules/'.$pcDir.'/pntemplates');
	$hide_list = array('.','..','CVS','compiled','cache');
	while($f=readdir($handle))
    {   if(!in_array($f,$hide_list) && !ereg("[.]",$f)) {
            $templatelist[] = $f;
        }
    }
    closedir($handle); unset($hide_list);
    sort($templatelist);
    $tcount = count($templatelist);
    $settings[$i][] = $output->Text(_PC_DEFAULT_TEMPLATE);
        $options = array();
        $selected = pnModGetVar(__POSTCALENDAR__,'pcTemplate');
        for($t=0;$t<$tcount;$t++) {
            $options[$t]['id']       = $templatelist[$t];
            $options[$t]['selected'] = ($selected == $templatelist[$t]);
            $options[$t]['name']     = $templatelist[$t];
        }
    $settings[$i++][] = $output->FormSelectMultiple('pcTemplate', $options);


    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    // Add row
    for($i = 0 ; $i < count($settings) ; $i++) {
        $output->TableAddRow($settings[$i], 'left');
    }

    $output->Text('</table>');
    $output->FormSubmit(_PC_ADMIN_SUBMIT);
    $output->FormEnd();*/
	$output->Text("</body></html>");

    return $output->GetOutput();
}

function postcalendar_admin_resetDefaults()
{
	if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	// remove all the PostCalendar variables from the DB
    pnModDelVar(__POSTCALENDAR__, 'pcTime24Hours');
    pnModDelVar(__POSTCALENDAR__, 'pcEventsOpenInNewWindow');
    pnModDelVar(__POSTCALENDAR__, 'pcUseInternationalDates');
    pnModDelVar(__POSTCALENDAR__, 'pcFirstDayOfWeek');
    pnModDelVar(__POSTCALENDAR__, 'pcDayHighlightColor');
    pnModDelVar(__POSTCALENDAR__, 'pcUsePopups');
	pnModDelVar(__POSTCALENDAR__, 'pcDisplayTopics');
    pnModDelVar(__POSTCALENDAR__, 'pcAllowDirectSubmit');
    pnModDelVar(__POSTCALENDAR__, 'pcListHowManyEvents');
	pnModDelVar(__POSTCALENDAR__, 'pcTimeIncrement');
	pnModDelVar(__POSTCALENDAR__, 'pcAllowSiteWide');
	pnModDelVar(__POSTCALENDAR__, 'pcAllowUserCalendar');
	pnModDelVar(__POSTCALENDAR__, 'pcEventDateFormat');
	pnModDelVar(__POSTCALENDAR__, 'pcTemplate');
	pnModDelVar(__POSTCALENDAR__, 'pcUseCache');
	pnModDelVar(__POSTCALENDAR__, 'pcCacheLifetime');
	pnModDelVar(__POSTCALENDAR__, 'pcDefaultView');
	pnModDelVar(__POSTCALENDAR__, 'pcNotifyAdmin');
	pnModDelVar(__POSTCALENDAR__, 'pcNotifyEmail');

	// PostCalendar Default Settings
    pnModSetVar(__POSTCALENDAR__, 'pcTime24Hours',              '0');
    pnModSetVar(__POSTCALENDAR__, 'pcEventsOpenInNewWindow',    '0');
    pnModSetVar(__POSTCALENDAR__, 'pcUseInternationalDates',    '0');
    pnModSetVar(__POSTCALENDAR__, 'pcFirstDayOfWeek',           '0');
    pnModSetVar(__POSTCALENDAR__, 'pcDayHighlightColor',        '#EEEEEE');
    pnModSetVar(__POSTCALENDAR__, 'pcUsePopups',                '1');
	pnModSetVar(__POSTCALENDAR__, 'pcDisplayTopics',            '0');
    pnModSetVar(__POSTCALENDAR__, 'pcAllowDirectSubmit',        '0');
    pnModSetVar(__POSTCALENDAR__, 'pcListHowManyEvents',        '15');
	pnModSetVar(__POSTCALENDAR__, 'pcTimeIncrement',        	'15');
	pnModSetVar(__POSTCALENDAR__, 'pcAllowSiteWide',        	'0');
	pnModSetVar(__POSTCALENDAR__, 'pcAllowUserCalendar',        '1');
	pnModSetVar(__POSTCALENDAR__, 'pcEventDateFormat',        	'%Y-%m-%d');
	pnModSetVar(__POSTCALENDAR__, 'pcTemplate',         		'default');
	pnModSetVar(__POSTCALENDAR__, 'pcUseCache',         		'1');
	pnModSetVar(__POSTCALENDAR__, 'pcCacheLifetime',         	'3600');
	pnModSetVar(__POSTCALENDAR__, 'pcDefaultView',         		'month');
	pnModSetVar(__POSTCALENDAR__, 'pcNotifyAdmin',         		'0');
	pnModSetVar(__POSTCALENDAR__, 'pcNotifyEmail',         		pnConfigGetVar('adminmail'));

	$tpl =& new pcSmarty();
	$tpl->clear_all_cache();

    return postcalendar_admin_modifyconfig('<center>'._PC_UPDATED_DEFAULTS.'</center>');
}

function postcalendar_admin_updateconfig()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	list($pcTime24Hours, $pcEventsOpenInNewWindow, $pcUseInternationalDates,
         $pcFirstDayOfWeek, $pcDayHighlightColor, $pcUsePopups,
         $pcAllowDirectSubmit, $pcListHowManyEvents, $pcDisplayTopics,
         $pcEventDateFormat, $pcTemplate, $pcAllowSiteWide,
		 $pcAllowUserCalendar, $pcTimeIncrement,$pcUseCache, $pcCacheLifetime,
		 $pcDefaultView, $pcNotifyAdmin, $pcNotifyEmail) = pnVarCleanFromInput('pcTime24Hours',
         'pcEventsOpenInNewWindow', 'pcUseInternationalDates', 'pcFirstDayOfWeek',
         'pcDayHighlightColor', 'pcUsePopups', 'pcAllowDirectSubmit',
         'pcListHowManyEvents', 'pcDisplayTopics', 'pcEventDateFormat',
         'pcTemplate', 'pcAllowSiteWide', 'pcAllowUserCalendar', 'pcTimeIncrement',
		 'pcUseCache', 'pcCacheLifetime', 'pcDefaultView', 'pcNotifyAdmin', 'pcNotifyEmail');

    // make sure we enter something into the DB
    if(!isset($pcTime24Hours))           { $pcTime24Hours = '0'; }
    if(!isset($pcEventsOpenInNewWindow)) { $pcEventsOpenInNewWindow='0'; }
    if(!isset($pcUseInternationalDates)) { $pcUseInternationalDates='0'; }
    if(!isset($pcFirstDayOfWeek))        { $pcFirstDayOfWeek='0'; }
    if(!isset($pcUsePopups))             { $pcUsePopups='0'; }
    if(!isset($pcAllowDirectSubmit))     { $pcAllowDirectSubmit='0'; }
    if(!isset($pcDisplayTopics))         { $pcDisplayTopics='0'; }
    if(!isset($pcTemplate))              { $pcTemplate='default'; }
    if(!isset($pcAllowSiteWide))         { $pcAllowSiteWide='0'; }
	if(!isset($pcAllowUserCalendar))     { $pcAllowUserCalendar='0'; }
	if(!isset($pcUseCache))         	 { $pcUseCache='0'; }
	if(!isset($pcDefaultView))         	 { $pcDefaultView='month'; }
	if(empty($pcCacheLifetime))          { $pcCacheLifetime='3600'; }
    if(empty($pcDayHighlightColor))      { $pcDayHighlightColor='#EEEEEE'; }
	if(empty($pcListHowManyEvents))      { $pcListHowManyEvents='15'; }
	if(empty($pcEventDateFormat))		 { $pcEventDateFormat='%Y-%m-%d'; }
	if(empty($pcTimeIncrement))          { $pcTimeIncrement='15'; }
	if(empty($pcNotifyAdmin))		 	 { $pcNotifyAdmin='0'; }
	if(empty($pcNotifyEmail))            { $pcNotifyEmail=pnConfigGetVar('adminmail'); }

	// delete the old vars - we're doing this because PostNuke variable
	// handling sometimes has old values in the $GLOBALS we need to clear
	pnModDelVar(__POSTCALENDAR__, 'pcTime24Hours');
	pnModDelVar(__POSTCALENDAR__, 'pcEventsOpenInNewWindow');
	pnModDelVar(__POSTCALENDAR__, 'pcUseInternationalDates');
	pnModDelVar(__POSTCALENDAR__, 'pcFirstDayOfWeek');
	pnModDelVar(__POSTCALENDAR__, 'pcDayHighlightColor');
	pnModDelVar(__POSTCALENDAR__, 'pcUsePopups');
	pnModDelVar(__POSTCALENDAR__, 'pcAllowDirectSubmit');
	pnModDelVar(__POSTCALENDAR__, 'pcListHowManyEvents');
	pnModDelVar(__POSTCALENDAR__, 'pcDisplayTopics');
	pnModDelVar(__POSTCALENDAR__, 'pcEventDateFormat');
	pnModDelVar(__POSTCALENDAR__, 'pcTemplate');
	pnModDelVar(__POSTCALENDAR__, 'pcAllowSiteWide');
	pnModDelVar(__POSTCALENDAR__, 'pcAllowUserCalendar');
	pnModDelVar(__POSTCALENDAR__, 'pcTimeIncrement');
	pnModDelVar(__POSTCALENDAR__, 'pcDefaultView');
	pnModDelVar(__POSTCALENDAR__, 'pcUseCache');
	pnModDelVar(__POSTCALENDAR__, 'pcCacheLifetime');
	pnModDelVar(__POSTCALENDAR__, 'pcNotifyAdmin');
	pnModDelVar(__POSTCALENDAR__, 'pcNotifyEmail');

	// set the new variables
    pnModSetVar(__POSTCALENDAR__, 'pcTime24Hours',  		 $pcTime24Hours);
    pnModSetVar(__POSTCALENDAR__, 'pcEventsOpenInNewWindow', $pcEventsOpenInNewWindow);
    pnModSetVar(__POSTCALENDAR__, 'pcUseInternationalDates', $pcUseInternationalDates);
    pnModSetVar(__POSTCALENDAR__, 'pcFirstDayOfWeek',		 $pcFirstDayOfWeek);
    pnModSetVar(__POSTCALENDAR__, 'pcDayHighlightColor',	 $pcDayHighlightColor);
    pnModSetVar(__POSTCALENDAR__, 'pcUsePopups',			 $pcUsePopups);
    pnModSetVar(__POSTCALENDAR__, 'pcAllowDirectSubmit',	 $pcAllowDirectSubmit);
    pnModSetVar(__POSTCALENDAR__, 'pcListHowManyEvents',	 $pcListHowManyEvents);
    pnModSetVar(__POSTCALENDAR__, 'pcDisplayTopics',		 $pcDisplayTopics);
    pnModSetVar(__POSTCALENDAR__, 'pcEventDateFormat',  	 $pcEventDateFormat);
    pnModSetVar(__POSTCALENDAR__, 'pcTemplate', 			 $pcTemplate);
    pnModSetVar(__POSTCALENDAR__, 'pcAllowSiteWide',		 $pcAllowSiteWide);
	pnModSetVar(__POSTCALENDAR__, 'pcAllowUserCalendar',	 $pcAllowUserCalendar);
	pnModSetVar(__POSTCALENDAR__, 'pcTimeIncrement',		 $pcTimeIncrement);
	pnModSetVar(__POSTCALENDAR__, 'pcDefaultView',		 	 $pcDefaultView);
	pnModSetVar(__POSTCALENDAR__, 'pcUseCache',		 		 $pcUseCache);
	pnModSetVar(__POSTCALENDAR__, 'pcCacheLifetime',		 $pcCacheLifetime);
	pnModSetVar(__POSTCALENDAR__, 'pcNotifyAdmin',		 	 $pcNotifyAdmin);
	pnModSetVar(__POSTCALENDAR__, 'pcNotifyEmail',		 	 $pcNotifyEmail);

	$tpl =& new pcSmarty();
	$tpl->clear_all_cache();

    return postcalendar_admin_modifyconfig('<center>'._PC_UPDATED.'</center>');
}

function postcalendar_admin_categoriesOld($msg='',$e='')
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	$output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

	$pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $pcDir = pnVarPrepForOS($pcModInfo['directory']);
$jsColorPicker = <<<EOF
    <script LANGUAGE="Javascript" SRC="modules/$pcDir/pnincludes/AnchorPosition.js"></SCRIPT>
    <script LANGUAGE="Javascript" SRC="modules/$pcDir/pnincludes/PopupWindow.js"></SCRIPT>
    <script LANGUAGE="Javascript" SRC="modules/$pcDir/pnincludes/ColorPicker2.js"></SCRIPT>
    <script LANGUAGE="JavaScript">
    var cp = new ColorPicker('window');
    // Runs when a color is clicked
    function pickColor(color) {
	    field.value = color;
	}

    var field;
    function pick(anchorname,target) {
	    field = this.document.forms.cats.elements[target];
	    cp.show(anchorname);
	}
    </SCRIPT>
EOF;
	$header = <<<EOF
		<html>
		<head></head>
		<body bgcolor=
EOF;
	$header .= '"' . $GLOBALS['style']['BGCOLOR2'] . '">';
	$output->Text($header);
	$output->Text($jsColorPicker);
    $output->Text(postcalendar_adminmenu());

	if(!empty($e)) {
		$output->Text('<div style="padding:5px; border:1px solid red; background-color: pink;">');
		$output->Text('<center><b>'.$e.'</b></center>');
		$output->Text('</div><br />');
	}

	if(!empty($msg)) {
		$output->Text('<div style="padding:5px; border:1px solid green; background-color: lightgreen;">');
		$output->Text('<center><b>'.$msg.'</b></center>');
		$output->Text('</div><br />');
	}

    $cats = pnModAPIFunc(__POSTCALENDAR__,'admin','getCategories');
    if(!is_array($cats)) {
        $output->Text($cats);
        return $output->GetOutput();
    }

    $output->Text('<form name="cats" method="post" action="'.pnModURL(__POSTCALENDAR__,'admin','categoriesConfirm').'">');
    $output->Text('<table border="1" cellpadding="5" cellspacing="0">');
    $output->Text('<tr><th>'._PC_CAT_DELETE.'</th><th>'._PC_CAT_NAME.'</th><th>'._PC_CAT_DESC.'</th><th>'._PC_CAT_COLOR.'</th></tr>');
    $i = 0;
    foreach($cats as $cat) {
        $output->Text('<tr>');
            $output->Text('<td valign="top" align="left">');
                $output->FormHidden('id[]',$cat['id']);
                $output->FormCheckbox('del[]',false,$cat['id']);
            $output->Text('</td>');
            $output->Text('<td valign="top" align="left">');
                $output->FormText('name[]',$cat['name'],20);
            $output->Text('</td>');
            $output->Text('<td valign="top" align="left">');
                $output->FormTextarea('desc[]',$cat['desc'],3,20);
            $output->Text('</td>');
            $output->Text('<td valign="top" align="left">');
                $output->FormText('color[]',$cat['color'],10);
				$output->Text('[<a href="javascript:void(0);" onClick="pick(\'pick\',\''.($i+4).'\'); return false;" NAME="pick" ID="pick">pick</a>]');
            $output->Text('</td>');
            $output->Text('</tr>');
		$i+=5;
    }
        $output->Text('<tr>');
        	$output->Text('<td><a href="'.pnModURL(__POSTCALENDAR__,'admin','categoriesNew').'">'._PC_CAT_NEW.'</a></td>');
        	/*
            $output->Text('<td valign="top" align="left">');
                $output->Text(_PC_CAT_NEW);
            $output->Text('</td>');
            $output->Text('<td valign="top" align="left">');
                $output->FormText('newname','',20);
            $output->Text('</td>');
            $output->Text('<td valign="top" align="left">');
                $output->FormTextarea('newdesc','',3,20);
            $output->Text('</td>');
            $output->Text('<td valign="top" align="left">');
                $output->FormText('newcolor','',10);
				$output->Text('[<a href="javascript:void(0);" onClick="pick(\'pick\',\'newcolor\');return false;" NAME="pick" ID="pick">pick</a>]');
            $output->Text('</td>');
            */
        $output->Text('</tr>');
    $output->Text('</table>');
    $output->FormSubmit(_PC_ADMIN_SUBMIT);
    $output->FormEnd();
	$output->Text("</body></html>");
    return $output->GetOutput();
}
function postcalendar_admin_categoriesConfirm()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	$output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
$header = <<<EOF
	<html>
	<head></head>
	<body bgcolor=
EOF;
	$header .= '"' . $GLOBALS['style']['BGCOLOR2'] . '">';
	$output->Text($header);
	$output->Text(postcalendar_adminmenu());

    list($id,$del,$name,$desc,$color,
        $event_repeat,$event_repeat_freq,
		$event_repeat_freq_type,$event_repeat_on_num,
		$event_repeat_on_day,$event_repeat_on_freq,$durationh,$durationm,
		$end_date_flag,$end_date_type,$end_date_freq,$end_all_day,
		$newname,$newdesc,$newcolor,$new_event_repeat,$new_event_repeat_freq,
		$new_event_repeat_freq_type,$new_event_repeat_on_num,$new_event_repeat_on_day,
		$new_event_repeat_on_freq,$new_durationh,$new_durationm,$new_limitid,$new_end_date_flag,
		$new_end_date_type,$new_end_date_freq,$new_end_all_day
		) = pnVarCleanFromInput('id','del','name','desc','color',
                          	'event_repeat','event_repeat_freq','event_repeat_freq_type',
                          	'event_repeat_on_num','event_repeat_on_day',
                          	'event_repeat_on_freq','durationh','durationm',
							'end_date_flag','end_date_type','end_date_freq',
							'end_all_day','newname','newdesc','newcolor',
							'newevent_repeat','newevent_repeat_freq',
							'newevent_repeat_freq_type','newevent_repeat_on_num',
							'newevent_repeat_on_day','newevent_repeat_on_freq',
							'newdurationh','newdurationm','newlimitid','newend_date_flag',
							'newend_date_type','newend_date_freq','newend_all_day'
                          	);
    //data validation
    foreach($name as $i=>$item)
   	{
   		if(empty($item))
   		{
   			$output->Text(postcalendar_admin_categories($msg,"Category Names must contain a value!"));
   			return $output->GetOutput();
   		}
   		$tmp = $color[$i];
   		if(strlen($tmp) != 7 || $tmp[0] != "#")
   		{
   			$e = $tmp." size ".strlen($tmp)." at 0 ".$tmp[0];
   			$output->Text(postcalendar_admin_categories($msg,"You entered an invalid color(USE Pick) $e!"));
   			return $output->GetOutput();
   		}
   	}
   	foreach($durationh as $i=>$val)
    {
    	if(!is_numeric($durationh[$i]) || !is_numeric($durationm[$i]) || 
    		!is_numeric($event_repeat_freq[$i]) || 
    		!is_numeric($event_repeat_on_freq[$i]) || !is_numeric($end_date_freq[$i]))
   		{	
   			$output->Text(postcalendar_admin_categories($msg," Hours, Minutes and recurrence values must be numeric!"));
   			return $output->GetOutput();
   		}
    }
      		
    if(!empty($newnam))
    {
    	if(!is_numeric($new_durationh) || !is_numeric($new_durationm) || 
	    	!is_numeric($new_event_repeat_freq) || !is_numeric($new_event_repeat_on_freq)
	    	|| !is_numeric($new_end_date_freq) )
	   	{
	   		$output->Text(postcalendar_admin_categories($msg,"Hours, Minutes and recurrence values must be numeric!"));
	   		return $output->GetOutput();
	   	}
    }			
	$new_duration = ($new_durationh * (60 * 60)) + ($new_durationm * 60);
	$event_recurrspec = serialize(compact('event_repeat_freq', 'event_repeat_freq_type', 'event_repeat_on_num',
                                          'event_repeat_on_day', 'event_repeat_on_freq'));
  	
  	$new_event_recurrspec = serialize(compact('new_event_repeat_freq', 'new_event_repeat_freq_type', 'new_event_repeat_on_num',
                                          'new_event_repeat_on_day', 'new_event_repeat_on_freq'));
  	if(is_array($del)) {
        $dels = implode(',',$del);
        $delText = _PC_DELETE_CATS . $dels .'.';
    }
    $output->FormStart(pnModURL(__POSTCALENDAR__,'admin','categoriesUpdate'));
    $output->Text(_PC_ARE_YOU_SURE);
    $output->Linebreak(2);
    // deletions
    if(isset($delText)) {
        $output->FormHidden('dels',$dels);
        $output->Text($delText);
        $output->Linebreak();
    }
    if(!empty($newname)) {
        $output->FormHidden('newname',$newname);
        $output->FormHidden('newdesc',$newdesc);
        $output->FormHidden('newcolor',$newcolor);
        $output->FormHidden('newevent_repeat',$new_event_repeat);
        $output->FormHidden('newevent_recurrfreq',$new_event_repeat_freq);
        $output->FormHidden('newevent_recurrspec',$new_event_recurrspec);
        $output->FormHidden('newduration',$new_duration);
        $output->FormHidden('newlimitid',$new_limitid);
        $output->FormHidden('newend_date_flag',$new_end_date_flag);
        $output->FormHidden('newend_date_type',$new_end_date_type);
        $output->FormHidden('newend_date_freq',$new_end_date_freq);
        $output->FormHidden('newend_all_day', $new_end_all_day);
        
        $output->Text(_PC_ADD_CAT . $newname .'.');
        $output->Linebreak();
    }
    $output->Text(_PC_MODIFY_CATS);
    $output->FormHidden('id',serialize($id));
    $output->FormHidden('del',serialize($del));
    $output->FormHidden('name',serialize($name));
    $output->FormHidden('desc',serialize($desc));
    $output->FormHidden('color',serialize($color));
    $output->FormHidden('event_repeat',serialize($event_repeat));
    $output->FormHidden('event_recurrspec',$event_recurrspec);
    $output->FormHidden('durationh',serialize($durationh));
    $output->FormHidden('durationm',serialize($durationm));
    $output->FormHidden('end_date_flag',serialize($end_date_flag));
    $output->FormHidden('end_date_type',serialize($end_date_type));
    $output->FormHidden('end_date_freq',serialize($end_date_freq));
    $output->FormHidden('end_all_day',serialize($end_all_day));
    $output->Linebreak();
    $output->FormSubmit(_PC_CATS_CONFIRM);
    $output->FormEnd();

    return $output->GetOutput();

}
function postcalendar_admin_categoriesUpdate()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	$output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    list($id,$del,$name,$desc,$color,
        $event_repeat_array,$event_recurrspec_array,$dels,$durationh,$durationm,
        $end_date_flag,$end_date_type,$end_date_freq,$end_all_day,$newname,$newdesc,$newcolor,
        $new_event_repeat,$new_event_recurrspec,$new_event_recurrfreq,
        $new_duration,$new_dailylimitid,$new_end_date_flag,$new_end_date_type,
        $new_end_date_freq,$new_end_all_day
		) = pnVarCleanFromInput('id','del','name','desc','color','event_repeat',
							'event_recurrspec','dels','durationh','durationm',
							'end_date_flag','end_date_type','end_date_freq','end_all_day',
							'newname','newdesc','newcolor',
							'newevent_repeat','newevent_recurrspec',
							'newevent_recurrfreq','newduration','newlimitid',
							'newend_date_flag','newend_date_type',
							'newend_date_freq','newend_all_day'
                          	);
	
    $id = unserialize($id);
    $del = unserialize($del);
    $name = unserialize($name);
    $desc = unserialize($desc);
    $color = unserialize($color);
	$event_repeat_array = unserialize($event_repeat_array);
	$event_recurrspec_array = unserialize($event_recurrspec_array);
    $durationh = unserialize($durationh);
    $durationm = unserialize($durationm);
    $end_date_flag = unserialize($end_date_flag);
    $end_date_type = unserialize($end_date_type);
    $end_date_freq = unserialize($end_date_freq);
    $end_all_day = unserialize($end_all_day);
    $updates = array();
    
    if(isset($id)) {
		foreach($id as $k=>$i) {
        	$found = false;
        	if(count($del)) {
            	foreach($del as $d) {
                	if($i == $d) {
                    	$found = true;
                    	break;
                	}
            	}
        	}
        	if(!$found) {
        		$event_repeat_freq = $event_recurrspec_array['event_repeat_freq'][$i];
				$event_repeat_freq_type = $event_recurrspec_array['event_repeat_freq_type'][$i];
				$event_repeat_on_num = $event_recurrspec_array['event_repeat_on_num'][$i];
                $event_repeat_on_day = $event_recurrspec_array['event_repeat_on_day'][$i];
                $event_repeat_on_freq = $event_recurrspec_array['event_repeat_on_freq'][$i];
            	
            	$recurrspec = serialize(compact('event_repeat_freq', 'event_repeat_freq_type', 'event_repeat_on_num',
                                          'event_repeat_on_day', 'event_repeat_on_freq'));
            	
            	$dur = ( ($durationh[$i]*(60 * 60)) + ($durationm[$i] * 60));
            	
            	$update_sql = "UPDATE $pntable[postcalendar_categories]
		                             SET pc_catname='".pnVarPrepForStore($name[$k])."',
		                                 pc_catdesc='".trim(pnVarPrepForStore($desc[$k]))."',
		                                 pc_catcolor='".pnVarPrepForStore($color[$k])."',
		                                 pc_recurrtype='".pnVarPrepForStore($event_repeat_array[$i])."',
		                                 pc_recurrspec='".pnVarPrepForStore($recurrspec)."',
		                                 pc_duration='".pnVarPrepForStore($dur)."',
		                                 pc_end_date_flag='".pnVarPrepForStore($end_date_flag[$i])."',
		                             	 pc_end_date_type='".pnVarPrepForStore($end_date_type[$i])."',
		                             	 pc_end_date_freq='".pnVarPrepForStore($end_date_freq[$i])."',
		                             	 pc_end_all_day='".pnVarPrepForStore($end_all_day[$i])."'
		                             WHERE pc_catid=$i";
		        array_push($updates, $update_sql);
		       	unset($recurrspec);
		       	unset($dur);            	
        	}
    	}
	}
	
	
	$delete = "DELETE FROM $pntable[postcalendar_categories] WHERE pc_catid IN ($dels)";
    $e =  $msg = '';
    if(!pnModAPIFunc(__POSTCALENDAR__,'admin','updateCategories',array('updates'=>$updates))) {
        $e .= 'UPDATE FAILED';
    }
    if(isset($dels)) {
        if(!pnModAPIFunc(__POSTCALENDAR__,'admin','deleteCategories',array('delete'=>$delete))) {
            $e .= 'DELETE FAILED';
        }
    }
    if(isset($newname)) {
    	$unpacked = unserialize($new_event_recurrspec);
    	unset($new_event_recurrspec);	
 		$new_event_recurrspec['event_repeat_freq'] = $unpacked['new_event_repeat_freq'];
    	$new_event_recurrspec['event_repeat_freq_type'] = $unpacked['new_event_repeat_freq_type'];
    	$new_event_recurrspec['event_repeat_on_num'] = $unpacked['new_event_repeat_on_num'];
    	$new_event_recurrspec['event_repeat_on_day'] = $unpacked['new_event_repeat_on_day'];
    	$new_event_recurrspec['event_repeat_on_freq'] = $unpacked['new_event_repeat_on_freq'];
    	$new_event_recurrspec = serialize($new_event_recurrspec);
    	
        if(!pnModAPIFunc(__POSTCALENDAR__,'admin','addCategories',
        	array('name'=>$newname,'desc'=>$newdesc,'color'=>$newcolor,
        	'repeat'=>$new_event_repeat,'spec'=>$new_event_recurrspec,
        	'recurrfreq'=>$new_recurrfreq,'duration'=>$new_duration,'limitid'=>$new_dailylimitid,
        	'end_date_flag'=>$new_end_date_flag,'end_date_type'=>$new_end_date_flag,
    		'end_date_freq'=>$new_end_date_freq,'end_all_day'=>$new_end_all_day))) {
            $e .= 'INSERT FAILED';
        }
    }
    
	if(empty($e)) { $msg = 'DONE'; }
    $output->Text(postcalendar_admin_categories($msg,$e));
    return $output->GetOutput();
}

/**
* Creates a new category
*/
function postcalendar_admin_categories($msg='',$e='',$args)
{
	if(!PC_ACCESS_ADD) { return _POSTCALENDARNOAUTH; }
	extract($args); unset($args);

	$output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
	// set up Smarty
    $tpl =& new pcSmarty();
	$tpl->caching = false;

	$template_name = pnModGetVar(__POSTCALENDAR__,'pcTemplate');
	
    if(!isset($template_name)) {
        $template_name ='default';
    }
	
	if(!empty($e)) {
		$output->Text('<div style="padding:5px; border:1px solid red; background-color: pink;">');
		$output->Text('<center><b>'.$e.'</b></center>');
		$output->Text('</div><br />');
	}

	if(!empty($msg)) {
		$output->Text('<div style="padding:5px; border:1px solid green; background-color: lightgreen;">');
		$output->Text('<center><b>'.$msg.'</b></center>');
		$output->Text('</div><br />');
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


	$tpl->assign_by_ref('TPL_NAME',$template_name);
    $tpl->assign('FUNCTION',pnVarCleanFromInput('func'));
    $tpl->assign_by_ref('ModuleName', $modname);
    $tpl->assign_by_ref('ModuleDirectory', $modir);
    $tpl->assign_by_ref('all_categories', $all_categories);
   	
    $tpl->assign('pcDir', $modir);
    $tpl->assign('action', pnModURL(__POSTCALENDAR__,'admin','categoriesConfirm'));
    $tpl->assign('adminmenu', postcalendar_adminmenu());
    $tpl->assign('BGCOLOR2', $GLOBALS['style']['BGCOLOR2']);
    $tpl->assign('_PC_CAT_NAME',_PC_CAT_NAME);
    $tpl->assign('_PC_CAT_DESC',_PC_CAT_DESC);
    $tpl->assign('_PC_CAT_COLOR',_PC_CAT_COLOR);
    $tpl->assign('_PC_CAT_DELETE',_PC_CAT_DELETE);
    $tpl->assign('_PC_CAT_DUR',_PC_CAT_DUR);
	
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
    $tpl->assign('REP_CAT_TITLE',	_PC_REP_CAT_TITLE);
 	$tpl->assign('NEW_CAT_TITLE',	_PC_NEW_CAT_TITLE);
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
    $tpl->assign('repeat_freq',$event_repeat_freq);
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
    $tpl->assign('InuptRepeatFreq', '' .'event_repeat_freq');
    $tpl->assign('repeat_freq_type',$repeat_freq_type);

    $tpl->assign('InputRepeatOn', 'event_repeat');
    $tpl->assign('ValueRepeatOn', '2');
    $tpl->assign('SelectedRepeatOn', (int) $event_repeat==2 ? 'checked':'');
   
 	// All Day START
 	$tpl->assign('InputAllDay', 	'end_all_day');
	$tpl->assign('ValueAllDay', 	'1');
	$tpl->assign('ValueAllDayNo', 	'0');
	$tpl->assign('ALL_DAY_CAT_TITLE',	_PC_ALL_DAY_CAT_TITLE);
 	$tpl->assign('ALL_DAY_CAT_YES',	_PC_ALL_DAY_CAT_YES);
 	$tpl->assign('ALL_DAY_CAT_NO',	_PC_ALL_DAY_CAT_NO);
 	
 //ALL Day End
// End date gather date start	    

	$tpl->assign('InputEndDateFreq','end_date_freq');
	$tpl->assign('InputEndOn', 	'end_date_flag');
	$tpl->assign('InputEndDateFreqType', 'end_date_type');
	$tpl->assign('ValueNoEnd', '0');
    $tpl->assign('ValueEnd', '1');
    
    
    foreach($in as $k=>$v) {
        array_push($end_date_type,array('value'=>$keys[$k],
                                           'selected'=>($keys[$k]==$end_date_type?'selected':''),
                                           'name'=>$v));
    }
    unset($in);


// End date gather date end   

   
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
	$tpl->assign('DurationHourTitle', _PC_DURATION_HOUR);
	$tpl->assign('DurationMinTitle', _PC_DURATION_MIN);
    $tpl->assign('InputDurationHour', "durationh");
	$tpl->assign('InputDurationMin', "durationm");
	
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $authkey = $output->FormHidden('authid',pnSecGenAuthKey());
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

	$form_hidden = "<input type=\"hidden\" name=\"is_update\" value=\"$is_update\" />";
	$form_hidden .= "<input type=\"hidden\" name=\"pc_event_id\" value=\"$pc_event_id\" />";
	if(isset($data_loaded)) {
		$form_hidden .= "<input type=\"hidden\" name=\"data_loaded\" value=\"$data_loaded\" />";
		$tpl->assign('FormHidden',$form_hidden);
	}
	$form_submit = '<input type=hidden name="form_action" value="commit"/>
				   '.$authkey.'<input type="submit" name="submit" value="go">' ;
    $tpl->assign('FormSubmit',$form_submit);

    $output->Text($tpl->fetch($template_name.'/admin/submit_category.html'));
	$output->Text(postcalendar_footer());
    return $output->GetOutput();
}

/**
 * Main administration menu
 */
function postcalendar_adminmenu($upgraded=false)
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	pnThemeLoad(pnUserGetTheme());
    // get the theme globals :: is there a better way to do this?
    global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5, $bgcolor6;
    global $textcolor1, $textcolor2;

	$pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
	$pcDir = pnVarPrepForOS($pcModInfo['directory']);

    @define('_AM_VAL',           1);
    @define('_PM_VAL',           2);

    @define('_EVENT_APPROVED',   1);
    @define('_EVENT_QUEUED',     0);
    @define('_EVENT_HIDDEN',    -1);

    $adminURL     = pnModURL(__POSTCALENDAR__,'admin','');
    $settingsURL  = pnModURL(__POSTCALENDAR__,'admin','modifyconfig');
	$categoryURL  = pnModURL(__POSTCALENDAR__,'admin','categories');
	$submitURL    = pnModURL(__POSTCALENDAR__,'admin','submit');
	$approvedURL  = pnModURL(__POSTCALENDAR__,'admin','listapproved');
	$hiddenURL    = pnModURL(__POSTCALENDAR__,'admin','listhidden');
	$queuedURL    = pnModURL(__POSTCALENDAR__,'admin','listqueued');
	$cacheURL     = pnModURL(__POSTCALENDAR__,'admin','clearCache');
	$systemURL    = pnModURL(__POSTCALENDAR__,'admin','testSystem');
	$limitsURL    = pnModURL(__POSTCALENDAR__,'admin','categoryLimits');

	$adminText    = _POSTCALENDAR;
	$settingsText = _EDIT_PC_CONFIG_GLOBAL;
	$categoryText = _EDIT_PC_CONFIG_CATEGORIES;
	$submitText   = _PC_CREATE_EVENT;
	$approvedText = "Event List";
	$hiddenText   = _PC_VIEW_HIDDEN;
	$queuedText   = "Marked for Deletion";
	$cacheText    = _PC_CLEAR_CACHE;
	$cacheText    = _PC_CLEAR_CACHE;
	$systemText   = _PC_TEST_SYSTEM;
	$limitsText	  = _PC_CAT_LIMITS;

	// check for upgrade
	$upgrade = '';
	if($upgraded === false) {
		$upgrade = pc_isNewVersion();
	}


$output = <<<EOF
<table border="0" cellpadding="1" cellspacing="0" width="100%" bgcolor="$bgcolor2"><tr><td>
<table border="0" cellpadding="5" cellspacing="0" width="100%" bgcolor="$bgcolor1">
	<tr>
		<td align="left" valign="middle"><a href="$adminURL"><img
			border="0" src="modules/$pcDir/pnimages/admin.gif"></a></td>
        <td width="100%" align="left" valign="middle">
			<table border="0" cellpadding="1" cellspacing="0"><tr><td bgcolor="$bgcolor2">
			<table border="0" cellpadding="5" cellspacing="0" width="100%" bgcolor="$bgcolor1">
				<tr>
					<td nowrap>
						<!--<a href="$settingsURL">$settingsText</a> |-->
						<!--<a href="$submitURL">$submitText</a> |-->
						<a href="$cacheURL">$cacheText</a> |
						<a href="$systemURL">$systemText</a> |
						<a href="$categoryURL">$categoryText</a> |
						<a href="$limitsURL">$limitsText</a><!-- |
						<a href="$queuedURL">$queuedText</a> |
						<a href="$approvedURL">$approvedText</a> |
						<a href="$hiddenURL">$hiddenText</a>-->
						$upgrade
				</tr>
			</table>
			</td></tr></table>
		</td>
    </tr>
</table>
</td></tr></table>
<br />
EOF;
// Return the output that has been generated by this function
return $output;
}

function postcalendar_admin_clearCache()
{
	if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

        $tpl =& new pcSmarty();
        //fmg: check that both subdirs to be cleared first exist and are writeable  
        $spec_err = '';
    if (!file_exists($tpl->compile_dir)) 
		 $spec_err .= "Error: folder '$tpl->compile_dir' doesn't exist!<br>";
    else if (!is_writeable($tpl->compile_dir))
       $spec_err .= "Error: folder '$tpl->compile_dir' not writeable!<br>";
    if (!file_exists($tpl->cache_dir))
       $spec_err .= "Error: folder '$tpl->cache_dir' doesn't exist!<br>";
    else if (!is_writeable($tpl->cache_dir))                      
	    $spec_err .= "Error: folder '$tpl->cache_dir' not writeable!<br>";             
        //note: we don't abort on error... like before.                                       
        $tpl->clear_all_cache();   
        $tpl->clear_compiled_tpl();  
		  
    return postcalendar_admin_modifyconfig('<center>'.$spec_err._PC_CACHE_CLEARED.'</center>');
}

function pc_isNewVersion()
{
	$pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
	$pcDir = pnVarPrepForOS($pcModInfo['directory']);
	@include("modules/$pcDir/pnversion.php");

	if($pcModInfo['version'] <> $modversion['version']) {
		$upgradeURL   = pnModURL(__POSTCALENDAR__,'admin','upgrade');
		$upgradeText  = "Upgrade PostCalendar $pcModInfo[version] to $modversion[version]";
		$upgrade = "<br /><br />[ <a href=\"$upgradeURL\">$upgradeText</a> ]";
	} else {
		$upgrade = '';
	}

	return $upgrade;
}

// UPGRADE WORKAROUND SCRIPT - POSTNUKE BUG
function postcalendar_admin_upgrade()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	$pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
	$pcDir = pnVarPrepForOS($pcModInfo['directory']);
	@include("modules/$pcDir/pnversion.php");
	@include("modules/$pcDir/pninit.php");

	$result = postcalendar_upgrade($pcModInfo['version']);

	if($result === false) {
		$output  = postcalendar_adminmenu(false);
		$output .= '<div style="padding:5px; border:1px solid red; background-color: pink;">';
		$output .= '<center><b>SORRY :: MODULE UPGRADE FAILED</b></center>';
		$output .= '</div><br />';
		return $output;
	}

	// if we've gotten this far, then it's time to increment the version in the db
	list($dbconn) = pnDBGetConn();
	$pntable = pnDBGetTables();
	$modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];

	// Get module ID
    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[id]
              FROM $modulestable
              WHERE $modulescolumn[name] = '". pnVarPrepForStore(__POSTCALENDAR__)."'";
    $result = $dbconn->Execute($query);

    if ($result->EOF) { die("Failed to get module ID"); }

    list($mid) = $result->fields;
    $result->Close();

	$sql = "UPDATE $modulestable
            SET $modulescolumn[version] = '".pnVarPrepForStore($modversion['version'])."',
				$modulescolumn[state] = '".pnVarPrepForStore(_PNMODULE_STATE_ACTIVE)."'
            WHERE $modulescolumn[id] = '".pnVarPrepForStore($mid)."'";

	// upgrade did not succeed
	if($dbconn->Execute($sql) === false) {
		$output  = postcalendar_adminmenu(false);
		$output .= '<div style="padding:5px; border:1px solid red; background-color: pink;">';
		$output .= '<center><b>SORRY :: MODULE UPGRADE FAILED</b><br />';
		$output .= $dbconn->ErrorMsg();
		$output .= '</center>';
		$output .= '</div><br />';
	}
    $output  = postcalendar_adminmenu(true);
	$output .= '<div style="padding:5px; border:1px solid green; background-color: lightgreen;">';
	$output .= '<center><b>CONGRATULATIONS :: MODULE UPGRADE SUCCEEDED</b></center>';
	$output .= '</div><br />';
	return $output;
}

function postcalendar_admin_testSystem()
{
    global $bgcolor1,$bgcolor2;

	if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $pcDir = pnVarPrepForOS($modinfo['directory']);
	$version = $modinfo['version'];
	unset($modinfo);

	$tpl =& new pcSmarty();
    $infos = array();

	if(phpversion() >= '4.1.0') {
		$__SERVER =& $_SERVER;
		$__ENV    =& $_ENV;
	} else {
		$__SERVER =& $HTTP_SERVER_VARS;
		$__ENV    =& $HTTP_ENV_VARS;
	}

	if(defined('_PN_VERSION_NUM')) {
		$pnVersion = _PN_VERSION_NUM;
	} else {
		$pnVersion = pnConfigGetVar('Version_Num');
	}

    array_push($infos, array('CMS Version', $pnVersion));
	array_push($infos, array('Sitename', pnConfigGetVar('sitename')));
    array_push($infos, array('url', pnGetBaseURL()));
    array_push($infos, array('PHP Version', phpversion()));
    if ((bool) ini_get('safe_mode')) {
      	$safe_mode = "On";
    } else {
      	$safe_mode = "Off";
    }
    array_push($infos, array('PHP safe_mode', $safe_mode));
	if ((bool) ini_get('safe_mode_gid')) {
      	$safe_mode_gid = "On";
    } else {
      	$safe_mode_gid = "Off";
    }
    array_push($infos, array('PHP safe_mode_gid', $safe_mode_gid));
	$base_dir = ini_get('open_basedir');
	if(!empty($base_dir)) {
		$open_basedir = "$base_dir";
	} else {
		$open_basedir = "NULL";
	}
	array_push($infos, array('PHP open_basedir', $open_basedir));
    array_push($infos, array('SAPI', php_sapi_name()));
    array_push($infos, array('OS', php_uname()));
	array_push($infos, array('WebServer', $__SERVER['SERVER_SOFTWARE']));
	array_push($infos, array('Module dir', "modules/$pcDir"));

	$modversion = array();
    include  "modules/$pcDir/pnversion.php";

	$error = '';
	if ($modversion['version'] != $version) {
      	$error  = '<br /><div style=\"color: red;\">';
      	$error .= "new version $modversion[version] installed but not updated!";
      	$error .= '</div>';
    }
    array_push($infos, array('Module version', $version . " $error"));
	array_push($infos, array('smarty version', $tpl->_version));
    array_push($infos, array('smarty location',  SMARTY_DIR));
    array_push($infos, array('smarty template dir', $tpl->template_dir));

    $info = $tpl->compile_dir;
    $error = '';
    if (!file_exists($tpl->compile_dir)) {
      	$error .= " compile dir doesn't exist! [$tpl->compile_dir]<br />";
    } else {
      	// dir exists -> check if it's writeable
      	if (!is_writeable($tpl->compile_dir)) {
         	$error .= " compile dir not writeable! [$tpl->compile_dir]<br />";
      	}
    }
	if (strlen($error) > 0) {
      	$info .= "<br /><div style=\"color: red;\">$error</div>";
    }
	array_push($infos, array('smarty compile dir',  $info));

	$info = $tpl->cache_dir;
    $error = "";
    if (!file_exists($tpl->cache_dir)) {
      	$error .= " cache dir doesn't exist! [$tpl->cache_dir]<br />";
    } else {
      	// dir exists -> check if it's writeable
      	if (!is_writeable($tpl->cache_dir)) {
         	$error .= " cache dir not writeable! [$tpl->cache_dir]<br />";
      	}
    }
	if (strlen($error) > 0) {
      	$info .= "<br /><div style=\"color: red;\">$error</div>";
    }
	array_push($infos, array('smarty cache dir',  $info));

	$header = <<<EOF
	<html>
	<head></head>
	<body bgcolor=
EOF;
	$header .= '"' . $GLOBALS['style']['BGCOLOR2'] . '">';
	$output .= $header;
	$output  = postcalendar_adminmenu();
	$output .= '<table border="1" cellpadding="3" cellspacing="1">';
    $output .= '  <tr><th align="left">Name</th><th align="left">Value</th>';
    $output .= '</tr>';
    foreach ($infos as $info) {
		$output.= '<tr><td ><b>' . pnVarPrepHTMLDisplay($info[0]) . '</b></td>';
      	$output.= '<td>' . pnVarPrepHTMLDisplay($info[1]) . '</td></tr>';
    }
    $output .= '</table>';
    $output .= '<br /><br />';
	$output .= postcalendar_admin_modifyconfig('',false);
	$output .= "</body></html>";
	return $output;
}

function postcalendar_admin_categoryDetail($args)
{
	if(!PC_ACCESS_ADD) { return _POSTCALENDARNOAUTH; }
	extract($args); unset($args);

	$output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
	// set up Smarty
    $tpl =& new pcSmarty();
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
	$all_categories = & pnModAPIFunc(__POSTCALENDAR__,'user','getCategories');
	$all_details =& pnModAPIFunc(__POSTCALENDAR__,'user','getCatDetails');
	unset($modinfo);
    $tpl->config_dir = "modules/$modir/pntemplates/$template_name/config/";
    //=================================================================
    //  PARSE MAIN
    //=================================================================


	$tpl->assign_by_ref('TPL_NAME',$template_name);
    $tpl->assign('FUNCTION',pnVarCleanFromInput('func'));
    $tpl->assign_by_ref('ModuleName', $modname);
    $tpl->assign_by_ref('ModuleDirectory', $modir);
    $tpl->assign('Category', "Category");
    $tpl->assign_by_ref('categories', $all_categories);
    
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

    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $authkey = $output->FormHidden('authid',pnSecGenAuthKey());
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

	$form_hidden = "<input type=\"hidden\" name=\"is_update\" value=\"$is_update\" />";
	$form_hidden .= "<input type=\"hidden\" name=\"pc_event_id\" value=\"$pc_event_id\" />";
	if(isset($data_loaded)) {
		$form_hidden .= "<input type=\"hidden\" name=\"data_loaded\" value=\"$data_loaded\" />";
		$tpl->assign('FormHidden',$form_hidden);
	}
	$form_submit = '<input type=hidden name="form_action" value="commit"/>
				   '.$authkey.'<input type="submit" name="submit" value="go">' ;
    $tpl->assign('FormSubmit',$form_submit);

    $output->Text($tpl->fetch($template_name.'/admin/submit_detail.html'));
	
    $output->Text(postcalendar_footer());
    return $output->GetOutput();
}

function postcalendar_admin_categoryLimits($msg='',$e='',$args)
{
	if(!PC_ACCESS_ADD) { return _POSTCALENDARNOAUTH; }
	extract($args); unset($args);

	$output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
	// set up Smarty
    $tpl =& new pcSmarty();
	$tpl->caching = false;

	$template_name = pnModGetVar(__POSTCALENDAR__,'pcTemplate');
	
    if(!isset($template_name)) {
        $template_name ='default';
    }
	
	if(!empty($e)) {
		$output->Text('<div style="padding:5px; border:1px solid red; background-color: pink;">');
		$output->Text('<center><b>'.$e.'</b></center>');
		$output->Text('</div><br />');
	}

	if(!empty($msg)) {
		$output->Text('<div style="padding:5px; border:1px solid green; background-color: lightgreen;">');
		$output->Text('<center><b>'.$msg.'</b></center>');
		$output->Text('</div><br />');
	}
	
    //=================================================================
    //  Setup the correct config file path for the templates
    //=================================================================
    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $modir = pnVarPrepForOS($modinfo['directory']);
	$modname = $modinfo['displayname'];
	
	//print_r($all_categories);
	unset($modinfo);
   	$tpl->assign('action', pnModURL(__POSTCALENDAR__,'admin','categoryLimitsUpdate'));
    //===============================================================
    //  Setup titles for smarty 
    //===============================================================
    
    $tpl->assign('_PC_LIMIT_TITLE', 	_PC_LIMIT_TITLE);
    $tpl->assign('StartTimeTitle',		_PC_LIMIT_START_TIME);
    $tpl->assign('EndTimeTile', 	_PC_LIMIT_END_TIME);
    $tpl->assign('LimitHoursTitle',_PC_TIMED_DURATION_HOURS);
    $tpl->assign('LimitMinutesTitle',_PC_TIMED_DURATION_MINUTES);

	//=============================================================
	// Setup Vars for smarty
	//============================================================
	$tpl->assign('mer_title', 'mer');    
    $mer = array('am','pm');
    $tpl->assign_by_ref('mer',		$mer);
    $tpl->assign('starttimeh',		'starttimeh');
    $tpl->assign('starttimem',		'starttimem');
    $tpl->assign('endtimeh',		'endtimeh');
    $tpl->assign('endtimem',		'endtimem');
    $tpl->assign('InputLimit',		'limit');   
    $tpl->assign('LimitTitle',		_PC_LIMIT_TITLE);
    $tpl->assign('_PC_NEW_LIMIT_TITLE',	_PC_NEW_LIMIT_TITLE);
    $tpl->assign('_PC_CAT_DELETE',		_PC_CAT_DELETE);
    $tpl->assign('EndTimeTitle',	_PC_LIMIT_END_TIME);
        
    $hour_array =array('00','01','02','03','04','05','06','07','08','09','10','11','12',
    					'13','14','15','16','17','18','19','21','21','22','23');
    $min_array = array('00','05','10','15','20','25','30','35','40','45','50','55');
    $tpl->assign_by_ref('hour_array',	$hour_array);
    $tpl->assign_by_ref('min_array',	$min_array);
    
    $categories =  & pnModAPIFunc(__POSTCALENDAR__,'user','getCategories');
    $tpl->assign_by_ref('categories', $categories);
    $limits =& pnModAPIFunc(__POSTCALENDAR__,'user','getCategoryLimits');
    $tpl->assign_by_ref('limits', $limits);
    $tpl->assign('BGCOLOR2',$GLOBALS['style']['BGCOLOR2']);
    $tpl->assign("catTitle", "Category");
    $tpl->assign("catid", "catid");
    $form_submit = '<input type=hidden name="form_action" value="commit"/>
				   '.$authkey.'<input type="submit" name="submit" value="go">' ;
	$tpl->assign('FormSubmit',$form_submit);
   
    
     $output->Text($tpl->fetch($template_name.'/admin/submit_category_limit.html'));
	$output->Text(postcalendar_footer());
    return $output->GetOutput();
    
}

function postcalendar_admin_categoryLimitsUpdate()
{
    if(!PC_ACCESS_ADMIN) { return _POSTCALENDAR_NOAUTH; }

	$output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    list($id,$del,$catId,$startTimeH,$startTimeM,$endTimeH,$endTimeM,$limit,
    	$newCatId,$newStartTimeH,$newStartTimeM,$newEndTimeH,
    	$newEndTimeM,$newLimit,
		) = pnVarCleanFromInput('id','del','catid','starttimeh','starttimem',
							'endtimeh','endtimem','limit',
							'newcatid','newstarttimeh','newstarttimem','newendtimeh',
							'newendtimem','newlimit');
	$updates = array();
	if(isset($id)) {
		foreach($id as $k=>$i) {
        	$found = false;
        	if(count($del)) {
            	foreach($del as $d) {
                	if($i == $d) {
                    	$found = true;
                    	break;
                	}
            	}
        	}
        	if(!$found) {
        		
        		$start = date("H:i:s", mktime($startTimeH[$k],$startTimeM[$k],0));
    			$end = date("H:i:s", mktime($endTimeH[$k],$endTimeM[$k],0));
        		$update_sql = "UPDATE $pntable[postcalendar_limits]
		                             SET pc_catid='".pnVarPrepForStore($catId[$k])."',
		                                 pc_starttime='".pnVarPrepForStore($start)."',
		                                 pc_endtime='".pnVarPrepForStore($end)."',
		                                 pc_limit='".pnVarPrepForStore($limit[$k])."'
		                             WHERE pc_limitid=$i";
		        array_push($updates, $update_sql);         	
        	}
    	}
	}
	
	$dels = implode(",", $del);
	$delete = "DELETE FROM $pntable[postcalendar_limits] WHERE pc_limitid IN ($dels)";
	$e =  $msg = '';
    if(!pnModAPIFunc(__POSTCALENDAR__,'admin','updateCategoryLimit',array('updates'=>$updates))) {
        $e .= 'UPDATE FAILED';
    }
    if(isset($dels)) {
        if(!pnModAPIFunc(__POSTCALENDAR__,'admin','deleteCategoryLimit',array('delete'=>$delete))) {
            $e .= 'DELETE FAILED';
        }
    }
    if(isset($newLimit) && $newLimit > 0) {
    	$start = date("H:i:s", mktime($newStartTimeH,$newStartTimeM,0));
    	$end = date("H:i:s", mktime($newEndTimeH,$newEndTimeM,0));
    	
        if(!pnModAPIFunc(__POSTCALENDAR__,'admin','addCategoryLimit',
        	array('catid'=>$newCatId,'starttime'=>$start,'endtime'=>$end,
        	'limit'=>$newLimit))) {
            $e .= 'INSERT FAILED';
        }
    }
    
	if(empty($e)) { $msg = 'DONE'; }
    $output->Text(postcalendar_admin_categoryLimits($msg,$e));
    return $output->GetOutput();
	
}
?>
