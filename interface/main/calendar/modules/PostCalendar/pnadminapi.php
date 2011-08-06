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
$pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
$pcDir = pnVarPrepForOS($pcModInfo['directory']);
require_once("modules/$pcDir/common.api.php");
unset($pcModInfo,$pcDir);

function postcalendar_adminapi_buildHourSelect($args) 
{
    extract($args);
    $time24hours = pnModGetVar(__POSTCALENDAR__,'time24hours');
    
    if(!isset($hour)){
        $hour = $time24hours ? date('H') : date('h'); 
    }
    
    $output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    
    $options = array();
	if($time24hours) {
        for($i = 0; $i < 24; $i++) {
            $sel = false;
            if($i == $hour) {
                $sel = true;
            }
            $options[$i]['id']       = $i;
            $options[$i]['selected'] = $sel;
            $options[$i]['name']     = $i < 10 ? '0'.$i : $i;  
        }
    } else {
        for($i = 0; $i < 12; $i++) {
            $sel = false;
            if($i == $hour) {
                $sel = true;
            }
            $options[$i]['id']       = $i+1;
            $options[$i]['selected'] = $sel;
            $options[$i]['name']     = $i+1 < 10 ? '0'.$i+1 : $i+1;     
        }
    }
    
    $output->FormSelectMultiple('pc_hour',$options);
    return $output->GetOutput();
}
function postcalendar_adminapi_getAdminListEvents($args) 
{
	extract($args);
	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $events_table = $pntable['postcalendar_events'];
    
	$sql = "SELECT pc_eid,
                   pc_title, 
                   pc_time 
            FROM   $events_table
            WHERE  pc_eventstatus = $type ";
    if($sort == 'time') {
        $sql .= "ORDER BY pc_time ";
    } elseif($sort == 'title') {
        $sql .= "ORDER BY pc_title ";
    }
    if($sdir == 0) {
        $sql .= "DESC ";
    } elseif($sdir == 1) {
        $sql .= "ASC ";
    }
    $sql .= "LIMIT  $offset,$offset_increment";
    
    return $dbconn->Execute($sql);
}

function postcalendar_adminapi_buildAdminList($args) 
{
	extract($args);
	$output =& new pnHTML();
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	pnThemeLoad(pnUserGetTheme());
    // get the theme globals :: is there a better way to do this?
    global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5;
    global $textcolor1, $textcolor2;
	
	$formUrl = pnModUrl(__POSTCALENDAR__,'admin','adminevents');
    $output->FormStart($formUrl);
    $output->Text('<table border="0" cellpadding="1" cellspacing="0" width="100%" bgcolor="'.$bgcolor2.'"><tr><td>');
    $output->Text('<table border="0" cellpadding="5" cellspacing="0" width="100%" bgcolor="'.$bgcolor1.'"><tr><td>');
        $output->Text('<center><font size="4"><b>'.$title.'</b></font></center>');
    $output->Text('</td></tr></table>');    
    $output->Text('</td></tr></table>');
    
    $output->Linebreak();
    
    $output->Text('<table border="0" cellpadding="1" cellspacing="0" width="100%" bgcolor="'.$bgcolor2.'"><tr><td>');
    $output->Text('<table border="0" cellpadding="5" cellspacing="0" width="100%" bgcolor="'.$bgcolor1.'">');
        if(!$result || $result->EOF) {
            $output->Text('<tr><td width="100%" bgcolor="'.$bgcolor1.'" align="center"><b>'._PC_NO_EVENTS.'</b></td></tr>');
        } else {
            $output->Text('<tr><td bgcolor="'.$bgcolor1.'" align="center"><b>'._PC_EVENTS.'</b></td></tr>');
            $output->Text('<table border="0" cellpadding="2" cellspacing="0" width="100%" bgcolor="'.$bgcolor1.'">');
            
			// build sorting urls
            if(!isset($sdir)) { $sdir = 1; } 
			else { $sdir = $sdir ? 0 : 1; }
			
            $title_sort_url = pnModUrl(__POSTCALENDAR__,'admin',$function,array('offset'=>$offset,'sort'=>'title','sdir'=>$sdir));
            $time_sort_url = pnModUrl(__POSTCALENDAR__,'admin',$function,array('offset'=>$offset,'sort'=>'time','sdir'=>$sdir));
            $output->Text('<tr><td>select</td><td><a href="'.$title_sort_url.'">title</a></td><td><a href="'.$time_sort_url.'">timestamp</a><td></tr>');   
            // output the queued events
            $count=0;
            for(; !$result->EOF; $result->MoveNext()) {
                list($eid,$title,$timestamp) = $result->fields;
                $output->Text('<tr>');
                    $output->Text('<td align="center" valign="top">');
                        $output->FormCheckbox('pc_event_id[]', false, $eid);
                    $output->Text('</td>');
                    $output->Text('<td  align="left" valign="top" width="100%">');
                        $output->URL(pnModURL(__POSTCALENDAR__,'admin','edit',array('pc_event_id'=>$eid)),
						 			 pnVarPrepHTMLDisplay(postcalendar_removeScriptTags($title)));
                    $output->Text('</td>');
                    $output->Text('<td  align="left" valign="top" nowrap>');
                        $output->Text($timestamp);
                    $output->Text('</td>');
                $output->Text('</tr>');
                
                $count++;
            }
            $output->Text('</table>');     
        }
    $output->Text('</td></tr></table>');
    if($result->NumRows()) {
    $output->Linebreak();
    
    // action to take?
    $output->Text('<table border="0" cellpadding="1" cellspacing="0" width="100%" bgcolor="'.$bgcolor2.'"><tr><td>');
    $output->Text('<table border="0" cellpadding="5" cellspacing="0" width="100%" bgcolor="'.$bgcolor1.'"><tr>');
        $output->Text('<td align="left" valign="middle">');
            
            $seldata[0]['id'] = _ADMIN_ACTION_VIEW;
            $seldata[0]['selected'] = 1;
            $seldata[0]['name'] = _PC_ADMIN_ACTION_VIEW;
            
            $seldata[1]['id'] = _ADMIN_ACTION_APPROVE;
            $seldata[1]['selected'] = 0;
            $seldata[1]['name'] = _PC_ADMIN_ACTION_APPROVE;
            
            $seldata[2]['id'] = _ADMIN_ACTION_HIDE;
            $seldata[2]['selected'] = 0;
            $seldata[2]['name'] = _PC_ADMIN_ACTION_HIDE;
            
            $seldata[3]['id'] = _ADMIN_ACTION_DELETE;
            $seldata[3]['selected'] = 0;
            $seldata[3]['name'] = _PC_ADMIN_ACTION_DELETE;
            
            $output->FormSelectMultiple('action', $seldata);
            $output->FormHidden('thelist',$function);
            $output->FormSubmit(_PC_PERFORM_ACTION);
        $output->Text('</td>');
    $output->Text('</tr></table>');    
    $output->Text('</td></tr></table>');
    $output->Linebreak();
    
    // start previous next links
    $output->Text('<table border="0" cellpadding="1" cellspacing="0" width="100%" bgcolor="'.$bgcolor2.'"><tr><td>');
    $output->Text('<table border="0" cellpadding="5" cellspacing="0" width="100%" bgcolor="'.$bgcolor1.'"><tr>');
    if($offset > 1) {
        $output->Text('<td align="left">');
        $next_link = pnModUrl(__POSTCALENDAR__,'admin',$function,array('offset'=>$offset-$offset_increment,'sort'=>$sort,'sdir'=>$sdir));
        $output->Text('<a href="'.$next_link.'"><< '._PC_PREV.' '.$offset_increment.'</a>');
        $output->Text('</td>');
    } else {
        $output->Text('<td align="left"><< '._PC_PREV.'</td>');
    }
    if($result->NumRows() >= $offset_increment) {
        $output->Text('<td align="right">');
        $next_link = pnModUrl(__POSTCALENDAR__,'admin',$function,array('offset'=>$offset+$offset_increment,'sort'=>$sort,'sdir'=>$sdir));
        $output->Text('<a href="'.$next_link.'">'._PC_NEXT.' '.$offset_increment.' >></a>');
        $output->Text('</td>');
    } else {
        $output->Text('<td align="right">'._PC_NEXT.' >></td>');
    }
    $output->Text('</tr></table>');   
    } 
    $output->Text('</td></tr></table>');
    // end previous next links
    $output->FormEnd();
	
	return $output->GetOutput();
}

function postcalendar_adminapi_buildMinSelect($args) 
{
    extract($args);
    
    if(!isset($min)){
        $min = date('i'); 
    }
    
    $output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    
    $options = array();
    for ($i = 0; $i <= 45; $i+5) {
        $options[$i]['id']       = $i;
        $options[$i]['selected'] = false;
        $options[$i]['name']     = $i < 10 ? '0'.$i+1 : $i+1;            
    }
    
    $output->FormSelectMultiple('pc_min',$options);
    return $output->GetOutput();
}

function postcalendar_adminapi_buildAMPMSelect($args) 
{   
    extract($args);
    
    $output =& new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    
    $options = array();
    if(pnModGetVar(__POSTCALENDAR__,'time24hours')) {
        return false;
    } else {
        $options[0]['id']        = 'AM';
        $options[0]['selected']  = '';
        $options[0]['name']      = 'AM';
        $options[1]['id']        = 'PM';
        $options[1]['selected']  = '';
        $options[1]['name']      = 'PM';
    }
    
    $output->FormSelectMultiple('pc_ampm',$options);
    return $output->GetOutput();
}

function postcalendar_adminapi_waiting($args) 
{   $output =& new pnHTML();
    $output = "waiting<br />";
    return $output->GetOutput();
}

function postcalendar_adminapi_updateCategories($args) 
{
    extract($args);
    if(!isset($updates)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    foreach($updates as $update) {
        $result = $dbconn->Execute($update);
        if($result === false) {
            return false;
        }
    }
    return true;
}
function postcalendar_adminapi_deleteCategories($args) 
{
    extract($args);
    if(!isset($delete)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $result = $dbconn->Execute($delete);
    if($result === false) {
        return false;
    }
    return true;
}
function postcalendar_adminapi_addCategories($args) 
{
    extract($args);
    if(!isset($name)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    
    $name = pnVarPrepForStore($name);
    $desc = trim(pnVarPrepForStore($desc));
    $value_cat_type = pnVarPrepForStore($value_cat_type);
    $color = pnVarPrepForStore($color);
    $recurrtype = pnVarPrepForStore($repeat);
    $recurrspec = pnVarPrepForStore($spec);
    $recurrfreq = pnVarPrepForStore($recurrfreq);
    $duration = pnVarPrepForStore($duration);
    $limitid = pnVarPrepForStore($limitid);
    $end_date_flag = pnVarPrepForStore($end_date_flag);
    $end_date_type = pnVarPrepForStore($end_date_type);
    $end_date_freq = pnVarPrepForStore($end_date_freq);
    $end_all_day = pnVarPrepForStore($end_all_day);
    
    $sql = "INSERT INTO $pntable[postcalendar_categories] 
                                (pc_catid,pc_catname,pc_catdesc,pc_catcolor,
                                pc_recurrtype,pc_recurrspec,pc_recurrfreq,pc_duration,
    							pc_dailylimit,pc_end_date_flag,pc_end_date_type,
    							pc_end_date_freq,pc_end_all_day,pc_cattype)
                                VALUES ('','$name','$desc','$color',
                                '$recurrtype','$recurrspec','$recurrfreq',
                                '$duration','$limitid','$end_date_flag','$end_date_type',
                                '$end_date_freq','$end_all_day','$value_cat_type')";
                                
                                
    //print "sql is $sql \n";
    $result = $dbconn->Execute($sql);
    if($result === false) {
    	print $dbconn->ErrorMsg();
        return false;
    }
    return true;
}

function postcalendar_adminapi_updateCategoryLimit($args) 
{
    extract($args);
    if(!isset($updates)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    foreach($updates as $update) {
    	$result = $dbconn->Execute($update);
        if($result === false) {
            return false;
        }
    }
    return true;
}

function postcalendar_adminapi_deleteCategoryLimit($args) 
{
    extract($args);
    if(!isset($delete)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $result = $dbconn->Execute($delete);
    if($result === false) {
        return false;
    }
    return true;
}
function postcalendar_adminapi_addCategoryLimit($args) 
{
    extract($args);
    if(!isset($catid)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    
    $catid = pnVarPrepForStore($catid);
    $starttime = pnVarPrepForStore($starttime);
    $endtime = pnVarPrepForStore($endtime);
    $limit = pnVarPrepForStore($limit);
    
    $sql = "INSERT INTO $pntable[postcalendar_limits] 
                                (pc_limitid,pc_catid,pc_starttime,pc_endtime,
                                pc_limit)
                                VALUES ('','$catid','$starttime',
                                '$endtime','$limit')";
    
    $result = $dbconn->Execute($sql);
    if($result === false) {
    	print $dbconn->ErrorMsg();
        return false;
    }
    return true;
}
?>
