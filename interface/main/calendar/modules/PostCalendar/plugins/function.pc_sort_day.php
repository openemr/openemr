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
function smarty_function_pc_sort_day($params, &$smarty)
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
?>
