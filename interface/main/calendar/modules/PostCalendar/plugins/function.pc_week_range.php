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
function smarty_function_pc_week_range($args) 
{
	// $args['date'] date to use for range building
	// $args['sep'] seperate the dates by this string
	// $args['format'] format all dates like this
	// $args['format1'] format date 1 like this
	// $args['format2'] format date 1 like this
	setlocale(LC_TIME, _PC_LOCALE);
	if(!isset($args['date'])) {
		$args['date'] = postcalendar_getDate();
	}
	
	$y = substr($args['date'],0,4);
	$m = substr($args['date'],4,2);
	$d = substr($args['date'],6,2);
	
	if(!isset($args['sep'])) {
		$args['sep'] = ' - ';
	}
	if(!isset($args['format'])) {	
		if(!isset($args['format1'])) {
			$args['format1'] = _SETTING_DATE_FORMAT;
		}
		if(!isset($args['format2']))  {
			$args['format2'] = _SETTING_DATE_FORMAT;
		}
	} else {
		$args['format1'] = $args['format'];
		$args['format2'] = $args['format'];
	}	
	
	// get the week date range for the supplied $date
	$dow = date('w',mktime(0,0,0,$m,$d,$y));
	if(_SETTING_FIRST_DAY_WEEK == 0) {
		$firstDay 	= strftime($args['format1'],mktime(0,0,0,$m,($d-$dow),$y));
		$lastDay 	= strftime($args['format2'],mktime(0,0,0,$m,($d+(6-$dow)),$y));
	} elseif(_SETTING_FIRST_DAY_WEEK == 1) {
		if($dow == 0) $sub = 6;
		else $sub = $dow-1;
		$firstDay 	= strftime($args['format1'],mktime(0,0,0,$m,($d-$sub),$y));
		$lastDay 	= strftime($args['format2'],mktime(0,0,0,$m,($d+(6-$sub)),$y));
	} elseif(_SETTING_FIRST_DAY_WEEK == 6) {
		if($dow == 6) $sub = 0;
		else $sub = $dow+1;
		$firstDay 	= strftime($args['format1'],mktime(0,0,0,$m,($d-$sub),$y));
		$lastDay 	= strftime($args['format2'],mktime(0,0,0,$m,($d+(6-$sub)),$y));
	}
	// return the formated range
	echo $firstDay.$args['sep'].$lastDay;
}
?>
