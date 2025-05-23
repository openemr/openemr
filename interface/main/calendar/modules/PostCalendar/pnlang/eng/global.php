<?php

/*
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
//  The following define is necessary for the date and time functions
//  set it to the locale for this language
//=========================================================================
define('_PC_LOCALE', xl('_PC_LOCALE'));
//=========================================================================
//  Defines used in all files
//=========================================================================
// new in 3.9.9

define('_PC_FILTER_USERS', xl('Default/Global'));
define('_PC_FILTER_USERS_ALL', xl('All Users'));
define('_PC_FILTER_CATEGORY', xl('All Categories'));
define('_PC_FILTER_TOPIC', xl('All Topics'));
define('_USER_BUSY_TITLE', xl('Busy'));
define('_USER_BUSY_MESSAGE', xl('I am busy during this time.'));
define('_PC_JUMP_MENU_SUBMIT', xl('Save'));
define('_PC_TPL_VIEW_SUBMIT', xl('change'));
define('_CALJAN', xl('January'));
define('_CALFEB', xl('February'));
define('_CALMAR', xl('March'));
define('_CALAPR', xl('April'));
define('_CALMAY', xl('May'));
define('_CALJUN', xl('June'));
define('_CALJUL', xl('July'));
define('_CALAUG', xl('August'));
define('_CALSEP', xl('September'));
define('_CALOCT', xl('October'));
define('_CALNOV', xl('November'));
define('_CALDEC', xl('December'));
define('_CALMONDAYSHORT', xl('M{{Monday}}'));
define('_CALTUESDAYSHORT', xl('T{{Tuesday}}'));
define('_CALWEDNESDAYSHORT', xl('W{{Wednesday}}'));
define('_CALTHURSDAYSHORT', xl('T{{Thursday}}'));
define('_CALFRIDAYSHORT', xl('F{{Friday}}'));
define('_CALSATURDAYSHORT', xl('S{{Saturday}}'));
define('_CALSUNDAYSHORT', xl('S{{Sunday}}'));
define('_CALSUNDAY', xl('Sunday'));
define('_CALMONDAY', xl('Monday'));
define('_CALTUESDAY', xl('Tuesday'));
define('_CALWEDNESDAY', xl('Wednesday'));
define('_CALTHURSDAY', xl('Thursday'));
define('_CALFRIDAY', xl('Friday'));
define('_CALSATURDAY', xl('Saturday'));
define('_CAL_DAYVIEW', xl('Day'));
define('_CAL_WEEKVIEW', xl('Week'));
define('_CAL_MONTHVIEW', xl('Month'));
define('_CAL_YEARVIEW', xl('Year'));
define('_PC_REPEATING_HEADER', xl('Repeating Info:'));
define('_PC_NO_REPEAT', xl('Event does not repeat'));
define('_PC_REPEAT', xl('Event repeats every'));
define('_PC_REPEAT_ON', xl('Event repeats on'));
define('_PC_OF_THE_MONTH', xl('of the month every'));
define('_PC_END_DATE', xl('End date'));
define('_PC_NO_END', xl('No end date'));
define('_PC_EVERY', xl('Every'));
define('_PC_EVERY_OTHER', xl('Every Other'));
define('_PC_EVERY_THIRD', xl('Every Third'));
define('_PC_EVERY_FOURTH', xl('Every Fourth'));
define('_PC_EVERY_1ST', xl('First'));
define('_PC_EVERY_2ND', xl('Second'));
define('_PC_EVERY_3RD', xl('Third'));
define('_PC_EVERY_4TH', xl('Fourth'));
define('_PC_EVERY_LAST', xl('Last'));
define('_PC_EVERY_SUN', xl('Sun'));
define('_PC_EVERY_MON', xl('Mon'));
define('_PC_EVERY_TUE', xl('Tue'));
define('_PC_EVERY_WED', xl('Wed'));
define('_PC_EVERY_THU', xl('Thu'));
define('_PC_EVERY_FRI', xl('Fri'));
define('_PC_EVERY_SAT', xl('Sat'));
define('_PC_OF_EVERY_MONTH', xl('month'));
define('_PC_OF_EVERY_2MONTH', xl('other month'));
define('_PC_OF_EVERY_3MONTH', xl('3 months'));
define('_PC_OF_EVERY_4MONTH', xl('4 months'));
define('_PC_OF_EVERY_6MONTH', xl('6 months'));
define('_PC_OF_EVERY_YEAR', xl('year'));
define('_PC_EVERY_DAY', xl('Day(s)'));
define('_PC_EVERY_WORKDAY', xl('Day(s) M-F'));
define('_PC_EVERY_WEEK', xl('Week(s)'));
define('_PC_EVERY_MONTH', xl('Month(s)'));
define('_PC_MONTHS', xl('Month(s)'));
define('_PC_EVERY_YEAR', xl('Year(s)'));
define('_PC_CAT_PATIENT', xl('Patient'));
define('_PC_CAT_PROVIDER', xl('Provider'));
define('_PC_CAT_CLINIC', xl('Clinic'));
define('_PC_CAT_THERAPY_GROUP', xl('Therapy group'));
