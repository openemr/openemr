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
function smarty_function_pc_filter($args, &$smarty)
{
	extract($args); unset($args);

	if(empty($type)) {
		$smarty->trigger_error("pc_filter: missing 'type' parameter");
    	return;
	}

	$Date = postcalendar_getDate();
    if(!isset($y)) $y = substr($Date,0,4);
    if(!isset($m)) $m = substr($Date,4,2);
    if(!isset($d)) $d = substr($Date,6,2);

    $tplview = pnVarCleanFromInput('tplview');
    $viewtype = pnVarCleanFromInput('viewtype');
	$pc_username = pnVarCleanFromInput('pc_username');

    if(!isset($viewtype)) { $viewtype = _SETTING_DEFAULT_VIEW; }

	$types = explode(',',$type);
	$output =& new pnHTML();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
	$modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $mdir = pnVarPrepForOS($modinfo['directory']);
	unset($modinfo);
    $pcTemplate = pnVarPrepForOS(_SETTING_TEMPLATE);
    if(empty($pcTemplate)) { $pcTemplate = 'default'; }

	list($dbconn) = pnDBGetConn();
	$pntable = pnDBGetTables();
	//================================================================
	//	build the username filter pulldown
	//================================================================
	if(in_array('user',$types)) {
		@define('_PC_FORM_USERNAME',true);
		$sql = "SELECT DISTINCT users.username, users.lname, users.fname
	 			FROM $pntable[postcalendar_events], users where users.id=pc_aid
				ORDER BY pc_aid";

		$result = $dbconn->Execute($sql);
		if($result !== false) {
			$useroptions  = "<select multiple='multiple' size='3' name=\"pc_username[]\" class=\"$class\">";
			$useroptions .= "<option value=\"\" class=\"$class\">"._PC_FILTER_USERS."</option>";
			$selected = $pc_username == '__PC_ALL__' ? 'selected="selected"' : '';
			$useroptions .= "<option value=\"__PC_ALL__\" class=\"$class\" $selected>"._PC_FILTER_USERS_ALL."</option>";
			for(;!$result->EOF;$result->MoveNext()) {
				$sel = $pc_username == $result->fields[0] ? 'selected="selected"' : '';
        		$useroptions .= "<option value=\"".$result->fields[0]."\" $sel class=\"$class\">".$result->fields[1] . ", " . $result->fields[2] ."</option>";
			}
    		$useroptions .= '</select>';
			$result->Close();
		}
	}
	//================================================================
	//	build the category filter pulldown
	//================================================================
	if(in_array('category',$types)) {
		@define('_PC_FORM_CATEGORY',true);
		$category = pnVarCleanFromInput('pc_category');
		$categories = pnModAPIFunc(__POSTCALENDAR__,'user','getCategories');
		$catoptions  = "<select name=\"pc_category\" class=\"$class\">";
		$catoptions .= "<option value=\"\" class=\"$class\">"._PC_FILTER_CATEGORY."</option>";
		foreach($categories as $c) {
			$sel = $category == $c['id'] ? 'selected="selected"' : '';
        	$catoptions .= "<option value=\"$c[id]\" $sel class=\"$class\">" . xl_appt_category($c[name]) . "</option>";
		}
		$catoptions .= '</select>';
	}
	//================================================================
	//	build the topic filter pulldown
	//================================================================
	if(in_array('topic',$types) && _SETTING_DISPLAY_TOPICS) {
		@define('_PC_FORM_TOPIC',true);
		$topic = pnVarCleanFromInput('pc_topic');
		$topics = pnModAPIFunc(__POSTCALENDAR__,'user','getTopics');
		$topoptions  = "<select name=\"pc_topic\" class=\"$class\">";
		$topoptions .= "<option value=\"\" class=\"$class\">"._PC_FILTER_TOPIC."</option>";
		foreach($topics as $t) {
			$sel = $topic == $t['id'] ? 'selected="selected"' : '';
        	$topoptions .= "<option value=\"$t[id]\" $sel class=\"$class\">$t[text]</option>";
		}
		$topoptions .= '</select>';
	} else {
		$topoptions = '';
	}
	
	//================================================================
	//	build it in the correct order
	//================================================================
	if(!isset($label)) { $label = _PC_TPL_VIEW_SUBMIT; }
    $submit = "<input type=\"submit\" valign=\"middle\" name=\"submit\" value=\"$label\" class=\"$class\" />";
    $orderArray = array('user'=>$useroptions, 'category'=>$catoptions, 'topic'=>$topoptions, 'jump'=>$submit);
	
	if(isset($order)) {
		$newOrder = array();
		$order = explode(',',$order);
		foreach($order as $tmp_order) {
			array_push($newOrder,$orderArray[$tmp_order]);
		}
		foreach($orderArray as $key=>$old_order) {
			if(!in_array($key,$newOrder)) {
				array_push($newOrder,$orderArray[$old_order]);
			}
		}
		$order = $newOrder;
	} else {
		$order = $orderArray;
	}
	
	foreach($order as $element) {
		echo $element;
	}
    if(!in_array('user',$types)) {
		echo $output->FormHidden('pc_username',$pc_username);
	}
}
?>
