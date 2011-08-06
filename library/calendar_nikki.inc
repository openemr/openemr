<?php

/*
nikkipost.php - Control panel for O-Kiraku Nikki
Copyright (C) 2003 Javi Lavandeira (http://www.ag0ny.com)

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

//require 'nikki.conf.php';
$week_start = 0;
$default_language = 'en';

if (isset($_GET['lang']))
{
	$lang = $_GET['lang'];
} else {
	if (isset($_SESSION['lang']))
	{
		$lang = $_SESSION['lang'];
	} else {
		$lang = $GLOBALS['default_language'];
	}
}

#$lang = isset($_GET['lang'])?$_GET['lang']:isset($_SESSION['lang'])?$_SESSION['lang']:$GLOBALS['default_language'];

switch($lang)
{
	case 'de':
		$language_dir = 'de_DE';
		$_SESSION['lang'] = 'de';
		break;
	case 'es':
		$language_dir = 'es_ES';
		$_SESSION['lang'] = 'es';
		break;
	case 'it':
		$language_dir = 'it_IT';
		$_SESSION['lang'] = 'it';
		break;
	case 'ja':
		$language_dir = 'ja_JP';
		$_SESSION['lang'] = 'ja';
		break;
	case 'nl':
		$language_dir = 'nl_NL';
		$_SESSION['lang'] = 'nl';
		break;
	case 'sv':
		$language_dir = 'sv_SE';
		$_SESSION['lang'] = 'sv';
		break;
	case 'en':
	default:
		$language_dir = 'en_US';
		$_SESSION['lang'] = 'en';
		break;
}

//putenv("LANG=$language_dir");
//setlocale(LC_ALL, "$language_dir");
//bindtextdomain("nikki", $GLOBALS['prefix']."/locale");
//textdomain("nikki");

$program_version = "program_name";

//header('Content-Type: text/html; charset=UTF-8');

$month[0] = "January";
$month[1] = "February";
$month[2] = "March";
$month[3] = "April";
$month[4] = "May";
$month[5] = "June";
$month[6] = "July";
$month[7] = "August";
$month[8] = "September";
$month[9] = "October";
$month[10] = "November";
$month[11] = "December";
$week_day[0] = "Sunday";
$week_day[1] = "Monday";
$week_day[2] = "Tuesday";
$week_day[3] = "Wednesday";
$week_day[4] = "Thursday";
$week_day[5] = "Friday";
$week_day[6] = "Saturday";
$week_day_abb[0] = "Sun";
$week_day_abb[1] = "Mon";
$week_day_abb[2] = "Tue";
$week_day_abb[3] = "Wed";
$week_day_abb[4] = "Thu";
$week_day_abb[5] = "Fri";
$week_day_abb[6] = "Sat";
$month_days[0] = 31;
$month_days[1] = 28;
$month_days[2] = 31;
$month_days[3] = 30;
$month_days[4] = 31;
$month_days[5] = 30;
$month_days[6] = 31;
$month_days[7] = 31;
$month_days[8] = 30;
$month_days[9] = 31;
$month_days[10] = 30;
$month_days[11] = 31;

$border_color = '#000000';

//connect_to_database();

function connect_to_database()
{
	if ($descriptor = mysql_connect($GLOBALS['mysql_host'], $GLOBALS['mysql_user'], $GLOBALS['mysql_password']))
	{
		mysql_query('USE '.$GLOBALS['mysql_database']);
		return $descriptor;
	} else {
		return 0;
	}
}

# display a month
# Input:	$year	Year in 4 digits format (ie: 2000, 1999...)
#		$month	Month (1-12)
function calendar_display_month($bg_line)
{
	$day_id = calendar_get_date();
	$year = abs(substr($day_id,0,4));
	$month = abs(substr($day_id,4,2));

	$attributes = localtime(mktime(0,0,1, $month, 1, $year), 1);
	$start_day = $attributes['tm_wday'];
	$month_name = date("F",strtotime($_SESSION["caltime"]));
	//$GLOBALS['month'][$month-1];
	$days_this_month = $GLOBALS['month_days'][$month-1];

	# is this a leap year?	
	if ($month == 2)
	{
		$days_this_month = ($year/4 == (int)($year/4) && ($year/100 != (int)($year/100) || $year/400 == (int)($year/400)))?29:28;
	}

	$today = localtime(time(), 1);

	echo '<table border="0" cellspacing="1" cellpadding="1" '.$bg_line.'>';
	echo '<tr>';
	echo '<td colspan="1">';
	echo calendar_link_previous_month('&lt;');
	echo '</td>';
	echo '<td colspan="5" align=center class="text">';
	echo $month_name.'&nbsp;('.$year.')&nbsp;';
	echo '</td>';
	echo '<td colspan="1">';
	echo calendar_link_next_month('&gt;');
	echo '</td>';
	echo '</tr>';

	# Print weekday abbreviations
	echo '<tr>';
	for ($column = 0; $column < 7; $column++)
	{
		echo '<td class="small">';
		#echo $GLOBALS['week_day_abb'][$column];
		echo $GLOBALS['week_day_abb'][($column + $GLOBALS['week_start']) % 7];
		echo '</td>';
	}
	echo '</tr>';

	$column = 0;
	echo '<tr>';
	while ($column < ($start_day - $GLOBALS['week_start']))
	{
		echo '<td class="text">&nbsp;</td>';
		$column++;
	}


	for ($day = 1; $day <= $days_this_month; $day++)
	{
		$day_index = date("Y-m",strtotime($_SESSION["caltime"]))."-".$time.substr('00'.abs($day), -2, 2);
		if (($column>0) && ($column%7 == 0)) echo '</tr><tr>';

		$check_wend = getdate(mktime(0,0,0,$month, $day, $year));	
		$weekend = (($check_wend['wday'] == 0) || ($check_wend['wday'] == 6))?1:0;

		$its_today = ($today['tm_mday'] == $day && $today['tm_mon'] == ($month-1) && ($today['tm_year'] + 1900) == $year)?1:0;
		$theres_text = isset($calendar_id[$day_index])?1:0;
		if (isset($_GET['day_id']))
		{
			$is_selected = abs(substr($_GET['day_id'],-2,2) == $day)?1:0;
		} else {
			$is_selected = ($today['tm_mday'] == $day)?1:0;
		}

		echo '<td>';
		//if (isset($calendar_id[$day_index]))
		//{
		//	echo '<a href="'.$GLOBALS['PHP_SELF'].'?lang='.$GLOBALS['lang'].'&day_id='.$day_index.'">'.$day.'</a>';
		//} else {
			echo '<a class=small href="'.$GLOBALS['PHP_SELF'].'?viewmode=daily&mode=settime&caltime='.$day_index.'">'.$day.'</a>';
		//}
		echo '</td>';
		$column++;
	}

	while ($column % 7 > 0)
	{
		echo '<td class="text">&nbsp;</td>';
		$column++;
	}
	echo '</tr>';
	echo '</table>';
}

function calendar_display_entries()
{
	$day_id = calendar_get_date();
	$entries = mysql_query('SELECT * FROM nikki WHERE timestamp=\''.$day_id.'\' ORDER BY id');
	if (mysql_num_rows($entries))
	{
		$year = abs(substr($day_id, 0, 4));
		$month = abs(substr($day_id, 4, 2));
		$day = abs(substr($day_id, 6, 2));
		printf("today_date", $year, $month, $day);
		echo '<br><br>';
		$calendar_id = array();
		while ($object = mysql_fetch_object($entries))
		{
			echo $object->text.'<br><br>';
		}
	}
}

function calendar_link_previous_month($text)
{
	$day_id = calendar_get_date();
	$year = abs(substr($day_id, 0, 4));
	$month = abs(substr($day_id, 4, 2));
	$day = abs(substr($day_id, 6, 2));

	$month = ($month == 1)?12:$month-1;
	$year = ($month == 12)?$year-1:$year;

	$day_index = substr('0000'.$year,-4,4)."-".substr('00'.$month,-2,2)."-".substr('00'.$day,-2,2);
	echo '<a class=bold href="'.$GLOBALS['PHP_SELF'].'?mode=settime&caltime='.$day_index.'">'.$text.'</a>';
}

function calendar_link_next_month($text)
{
	$day_id = calendar_get_date();
	$year = abs(substr($day_id, 0, 4));
	$month = abs(substr($day_id, 4, 2));
	$day = abs(substr($day_id, 6, 2));

	$month = ($month == 12)?1:$month+1;
	$year = ($month == 1)?$year+1:$year;

	$day_index = substr('0000'.$year,-4,4)."-".substr('00'.$month,-2,2)."-".substr('00'.$day,-2,2);
	echo '<a class=bold href="'.$GLOBALS['PHP_SELF'].'?mode=settime&caltime='.$day_index.'">'.$text.'</a>';
}

function calendar_get_date()
{
	/*
	if (isset($GLOBALS['day_id']))
	{
		return $GLOBALS['day_id'];
	} else {
		return isset($_GET['day_id'])?addslashes($_GET['day_id']):date('Ymd');
	}
	*/
	
	return date("Ymd",strtotime($_SESSION["caltime"]));
	
}
?>
